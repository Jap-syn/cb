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
use models\Logic\LogicClaimPrint;
use models\Table\TableClaimPrintPattern;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use models\Table\TableUser;
use Coral\Coral\Mail\CoralMail;

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

$this->logger->info('allUpdateClaimPrint.php start');

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

            $this->main($userId);

            $exitCode = 0;

        } catch( \Exception $e ) {
            // エラーログを出力
            if ( isset($this->logger) ) {
                $this->logger->err($e->getMessage());
            }
        }
$this->logger->info('allUpdateClaimPrint.php end');

        // 終了コードを指定して処理終了
        exit($exitCode);
    }

    protected function main($userId)
    {
        $mdlCpp = new TableClaimPrintPattern($this->dbAdapter);
        $logic = new LogicClaimPrint($this->dbAdapter);

//        $error1 = array();
//        $error1_wk = array();
//        $error2 = array();
//        $error2_wk = array();

        $sql = <<<EOQ
SELECT s.*,IFNULL(e.OemId, 0) OemId,e.CreditTransferFlg,e.LoginId,e.EnterpriseNameKj,e.BillingAgentFlg FROM T_Site s INNER JOIN T_Enterprise e ON s.EnterpriseId=e.EnterpriseId ORDER BY s.EnterpriseId,SiteId
EOQ;
        $sites = $this->dbAdapter->query($sql)->execute(null);

        foreach ($sites as $site) {
            $claimPatterns = array(0,1,2,4,6,7,8,9);
            if ($site['CreditTransferFlg'] == 0) {
                $claimPatterns = array(1,2,4,6,7,8,9);
            }
            if ($site['BillingAgentFlg'] == 1) {
                $claimPatterns = array(1);
            }
            foreach ($claimPatterns as $claimPattern) {
                $data = $logic->create($site['OemId'],$site['EnterpriseId'],$site['SiteId'],$claimPattern,$site['PaymentAfterArrivalFlg'],$site['FirstClaimLayoutMode'],$site['MufjBarcodeUsedFlg'],$site['ClaimMypagePrint']);
//                // 印刷パターンマスタに存在しないデータ
//                if ($data['ErrorCd'] == 1) {
//                    if (!isset($error1_wk[$site['LoginId'].'-'.$site['SiteId']])) {
//                        $error1_wk[$site['LoginId'].'-'.$site['SiteId']] = 1;
//                        $error1[] = '印刷パターンマスタ　事業者ID:'.$site['LoginId'].'　事業者名：'.$site['EnterpriseNameKj'].'　サイトID：'.$site['SiteId'];
//                    }
//                }
//                // 支払方法チェックマスタに存在しないデータ
//                if ($data['ErrorCd'] == 2) {
//                    if (!isset($error2_wk[$site['LoginId'].'-'.$site['SiteId']])) {
//                        $error2_wk[$site['LoginId'].'-'.$site['SiteId']] = 1;
//                        $error2[] = '支払方法マスタ　　　事業者ID:'.$site['LoginId'].'　事業者名：'.$site['EnterpriseNameKj'].'　サイトID：'.$site['SiteId'];
//                    }
//                }

                $work = $mdlCpp->find($site['EnterpriseId'], $site['SiteId'], $data['PrintIssueCountCd']);
                if ($work->count() > 0) {
                    $pkey = $work->current()['ClaimPrintPatternSeq'];
                    $data['EnterpriseId'] = $site['EnterpriseId'];
                    $data['SiteId'] = $site['SiteId'];
                    $data['UpdateId'] = $userId;
                    $mdlCpp->saveUpdate($data, $pkey);
                } else {
                    $data['EnterpriseId'] = $site['EnterpriseId'];
                    $data['SiteId'] = $site['SiteId'];
                    $data['RegistId'] = $userId;
                    $mdlCpp->saveNew($data);
                }
            }
//            // test用
//            if ($site['SiteId'] > 100) {
//                break;
//            }
        }

//        if ((sizeof($error1) > 0) || (sizeof($error2) > 0)) {
//            $mail = new CoralMail($this->dbAdapter, $this->mail['smtp']);
//            $error = array_merge($error1, $error2);
//            $mail->ClaimPrintErrorMailToCb($error, $userId);
//        }
    }
}

Application::getInstance()->run();
