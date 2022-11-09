<?php
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

setlocale( LC_ALL, 'ja_JP.UTF-8' );
ini_set( 'default_charset', 'UTF-8' );
mb_internal_encoding( 'UTF-8' );
mb_http_output('UTF-8');
mb_regex_encoding( 'UTF-8' );

ini_set( 'max_execution_time', 0 );

/**
 * アプリケーションクラスです。
 *
 */
use Coral\Base\Application\BaseApplicationAbstract;
use Coral\Base\BaseLog;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use models\Table\TableUser;
use models\Table\TableSystemProperty;
use models\Table\TableClaimControl;
use models\Table\TableEnterprise;
use Coral\Base\Auth\BaseAuthUtility;
use Coral\Base\BaseGeneralUtils;
use models\Logic\Exception\LogicClaimException;
use models\Table\TableOrder;
use Zend\Db\ResultSet\ResultSet;
use models\Logic\SelfBilling\LogicSelfBillingSelfBillingApi;
use Zend\Json\Json;


class Application extends BaseApplicationAbstract {
    protected $_application_id = 'tools';

    private $checkcsv;

    /**
     * Application の唯一のインスタンスを取得します。
     *
     * @static
     * @access public
     * @return Application
     */
    public static function getInstance() {
        if( self::$_instance === null ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Application の新しいインスタンスを初期化します。
     *
     * @ignore
     * @access private
     */
    private function __construct() {
        parent::init();
    }

    /**
     * @var Adapter
     */
    public $dbAdapter;

    /**
     * @var Log
     */
    public $logger;

    /**
     * @var メール環境
     */
    public $mail;

    /**
     * アプリケーションを実行します。
     *
     * @access public
     */
    public function run() {

        $exitCode = 1;
        $isBeginTran = false;

        try {

            $configPath = __DIR__ . '/../module/cbadmin/config/config.ini';
            // データベースアダプタをiniファイルから初期化します
            $data = array();
            if (file_exists($configPath))
            {
                $reader = new Ini();
                $data = $reader->fromFile($configPath);
            }

            $this->dbAdapter = new Adapter($data['database']);

            // ログ設定の読み込み
            $logConfig = $data['log'];
            // 標準ログクラス初期化
            $this->logger = BaseLog::createFromArray( $logConfig );
$this->logger->info('self_billing_printed_auto_update.php start');

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

            // 設定をシステムプロパティテーブルから読み込み
            $apinfo = $this->getApplicationiInfo($this->dbAdapter, 'cbadmin');
            // iniファイルの内容をマージ
            $data = array_merge($data, $apinfo);

            // メールに絡む属性
            $this->mail = $data['mail'];

            // ユーザーID取得
            $mdlu = new TableUser($this->dbAdapter);
            $userId = $mdlu->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

            // 初回請求書再発行指示
            $this->runMain();

$this->logger->info('self_billing_printed_auto_update.php end');
            $exitCode = 0; // 正常終了

        } catch( \Exception $e ) {
            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err($e->getMessage());
$this->logger->err($e->getTraceAsString());
            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }

    /**
     * 初回請求書再発行指示
     */
    protected function runMain() {
        $loader = new \Composer\Autoload\ClassLoader();
        $classMap = require __DIR__ . '/../module/api/autoload_classmap.php';
        $loader->addClassMap($classMap);
        $loader->register(true);

        $mdlsys = new TableSystemProperty($this->dbAdapter);
        $mdlcc  = new TableClaimControl($this->dbAdapter);
        $mdle = new TableEnterprise($this->dbAdapter);
        $sysProps = new \models\Table\TableSystemProperty($this->dbAdapter);

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->dbAdapter);
        $userId = $obj->getUserId(TableUser::USERCLASS_SYSTEM, TableUser::SEQ_BATCH_USER);

        // 督促支払期限有効日数を取得
        $enterprises = $this->getTargetEneprises();
        foreach ($enterprises as $enterprise) {
            $eid = $enterprise['EnterpriseId'];
            $limit = $enterprise['TargetListLimit'];
            $logic = $this->getBizLogic($enterprise, $userId);

            $this->logger->info('  EnterpriseId['.$eid.']:start');

            $orders = $this->getTargetClaim($eid);
            $params = array();
            foreach ($orders as $order) {
                $params[] = $order['OrderId'];
                if ($limit <= sizeof($params)) {
                    $sendData = $this->getSendData($eid, $params);
                    $this->logger->info('    Call Logic ['.Json::encode($params).']:start');
                    $logic->dispatch($sendData);
                    $params = array();
                }
            }
            if (sizeof($params) > 0) {
                $sendData = $this->getSendData($eid, $params);
                $logic->dispatch($sendData);
            }
            $this->logger->info('  EnterpriseId['.$eid.']:end');
        }
        return;
    }

    /**
     * 自動印刷済み更新加盟店取得.
     * @return array
     */
    private function getTargetEneprises() {
        $mdle = new TableEnterprise($this->dbAdapter);
        $result = array();
        $ri = $mdle->fetchAll('ValidFlg = 1 and SelfBillingPrintedAutoUpdateFlg = 1', 'EnterpriseId DESC');
        $rs = new ResultSet();
        $array = $rs->initialize($ri)->toArray();
        foreach( $array as $row) {
            $result[] = $row;
        }
        return $result;
    }

    /**
     * 請求対象リスト取得.
     * @param $eid 加盟店ID
     */
    private function getTargetClaim($eid) {
        $sql = <<<EOQ
SELECT  DISTINCT O.OrderId
FROM    T_Order O
        INNER JOIN T_ClaimHistory H ON (O.OrderSeq = H.OrderSeq)
        INNER JOIN T_Enterprise E ON (O.EnterpriseId = E.EnterpriseId)
        INNER JOIN T_Site S ON (O.SiteId = S.SiteId )
        INNER JOIN T_OrderItems OI ON (O.OrderSeq = OI.OrderSeq)
WHERE   O.EnterpriseId = :EnterpriseId
AND     (O.CombinedClaimTargetStatus IN (91, 92) OR IFNULL(O.CombinedClaimTargetStatus, 0) = 0)
AND     H.Seq = (SELECT Seq FROM T_ClaimHistory WHERE OrderSeq = O.OrderSeq AND ClaimPattern = 1 AND PrintedFlg = 0 AND ValidFlg = 1 AND EnterpriseBillingCode IS NOT NULL)
AND     O.ClaimSendingClass <> 12
AND     S.SelfBillingFlg = 1
AND     O.ConfirmWaitingFlg = 1
AND     OI.Deli_JournalNumber NOT LIKE 'TMP%'
EOQ;
        $ri = $this->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $eid));
        return ResultInterfaceToArray($ri);
    }

    private function getBizLogic($enterprise, $userId) {
        $mdle = new TableEnterprise($this->dbAdapter);
        $mdlsp = new TableSystemProperty($this->dbAdapter);

        $selfBillingConfig = $this->getSelfBillingConfig();

        // 事業者情報の設定
//        $ent = $mdle->findEnterprise2($eid)->current();
        // リスト取得上限件数の設定
        if ($enterprise['TargetListLimit'] == NULL) {
            $target_list_limit = $selfBillingConfig['target_list_limit'];
        } else {
            $target_list_limit = $enterprise['TargetListLimit'];
        }

        // SelfBillLogicのインスタンス生成
        $logic = new LogicSelfBillingSelfBillingApi($this->dbAdapter, $enterprise['EnterpriseId'], $userId, new BaseAuthUtility($mdlsp->getHashSalt()));

        $logic->setSystemSelfBillingEnabled($selfBillingConfig['use_selfbilling']);
        $logic->setPaymentLimitDays($selfBillingConfig['payment_limit_days']);
        $logic->setThresholdClientVersion($selfBillingConfig['threshold_version']);
        $logic->setTargetListLimit($target_list_limit);
        $logic->importStampFeeLogicSettings($mdlsp->getStampFeeSettings());
        $logic->setLogger($this->logger);

        return $logic;
    }

    private function getSelfBillingConfig() {
        $configPath = __DIR__ . '/../module/member/config/config.ini';
        // データベースアダプタをiniファイルから初期化します
        $mdata = array();
        if (file_exists($configPath))
        {
            $reader = new Ini();
            $mdata = $reader->fromFile($configPath);
        }
        $mdata = array_merge($mdata, $this->getApplicationiInfo($this->dbAdapter, 'member'));

        // 請求書同梱ツール設定の構築
        $default_sbconfig = array(		// デフォルト設定
            'use_selfbilling' => false,
            'payment_limit_days' => 14,
            'threshold_version' => null,
            'target_list_limit' => 250,
            'shipping_sp_count' => 30
        );

        $runtime_sbconfig = array();	// ランタイム設定
        try {
            // ランタイム設定をiniから読み込んで上書き
            $selfBillingConfig = $mdata['selfbilling'];
            $runtime_sbconfig = $selfBillingConfig;
        } catch(Exception $err) {
            // nop
        }
        // デフォルト設定をランタイム設定で上書きして初期化
        if (is_array($runtime_sbconfig)) {
            return array_merge($default_sbconfig, $runtime_sbconfig);
        }

        return array();
    }

    private function getSendData($eid, $orders) {
        // Logic用送信用データ
        $senddata = array();
        $senddata['Command'] = 'SetPrinted';

        // パラメータ設定
        $data = array();
        $selfBillingConfig = $this->getSelfBillingConfig();
        $data['Version'] =  $selfBillingConfig['threshold_version'];
        //{"EnterpriseId":"26381","ApiUserId":"66","AccessToken":null,"Action":"Processed","Param":[{"OrderId":"AK46972986"}]}
        $row = array();
        foreach ($orders as $order) {
            $row[] = array('OrderId' => $order);
        }
        $senddata['Parameters'] = array('Param' => $row);

        // 加盟店設定
        $senddata['EnterpriseId'] = $eid;

        return $senddata;
    }
}

Application::getInstance()->run();
