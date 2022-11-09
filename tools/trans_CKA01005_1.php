<?php
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

use Coral\Base\Application\BaseApplicationAbstract;
use Coral\Base\BaseLog;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Json\Json;
use models\Table\TableTemplateHeader;
use models\Table\TableTemplateField;

/**
 * アプリケーションクラスです。
 *
 */
class Application extends BaseApplicationAbstract {
    protected $_application_id = 'tools';

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
     * ログクラス
     *
     * @var BaseLog
     */
    public $logger;

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

$this->logger->info('trans_CKA01005_1.php start');

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

            // 本処理
            $this->_exec();

$this->logger->info('trans_CKA01005_1.php end');
            $exitCode = 0; // 正常終了

        } catch(\Exception $e) {
            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err($e->getMessage());
            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }

    /**
     * 本処理
     */
    private function _exec() {

        // 1. (処理対象となる)加盟店リスト取得
        $ri = $this->_getTargetEnterpriseList();

        // 2. 加盟店分処理
        foreach ($ri as $row) {
            // 調整登録処理
            $this->_adjustmentCsvSchema($row['EnterpriseId']);
        }

        return;
    }

    /**
     * (処理対象となる)加盟店リスト取得
     */
    private function _getTargetEnterpriseList() {
        $sql=<<<EOQ
SELECT DISTINCT e.EnterpriseId, th.Seq
FROM   T_Enterprise e
       INNER JOIN T_CsvSchema cs ON (cs.EnterpriseId = e.EnterpriseId)
       LEFT OUTER JOIN M_TemplateHeader th ON (th.Seq = e.EnterpriseId AND th.TemplateId = 'CKA01005_1')
WHERE  1 = 1
AND    cs.CsvClass = 3
AND    e.ValidFlg = 1
AND    th.Seq IS NULL
EOQ;
        return $this->dbAdapter->query($sql)->execute(null);
    }

    /**
     * 調整登録処理
     *
     * @param int $enterpriseId 加盟店ID
     */
    private function _adjustmentCsvSchema($enterpriseId) {

        // T_CsvSchemaより現在の設定情報の取得(基本)
        $ri_cs =  $this->dbAdapter->query(" SELECT * FROM T_CsvSchema WHERE EnterpriseId = :EnterpriseId AND CsvClass = 3 ORDER BY Ordinal "
            )->execute(array(':EnterpriseId' => $enterpriseId));

        // CSVスキーマ上の表示項目(昇順)取得
        $cs_show = array(); // 表示項目リスト
        foreach ($ri_cs as $row_cs) {
            $appdt = Json::decode($row_cs['ApplicationData'], Json::TYPE_ARRAY);
            if ($appdt['hidden'] == false) {
                $cs_show[] = $row_cs['ColumnName'];
            }
        }

        // 非表示一覧の生成(基本項目より表示項目を除外する)
        $cs_hide = array(
             'SiteId'
            ,'NameKj'
            ,'NameKn'
            ,'UnitingAddress'
            ,'Phone'
            ,'MailAddress'
            ,'EntCustId'
            ,'ReceiptOrderDate'
            ,'ServiceExpectedDate'
            ,'OrderId'
            ,'Ent_OrderId'
            ,'OrderItemNames'
            ,'DestNameKj'
            ,'DestNameKn'
            ,'DestUnitingAddress'
            ,'DestPhone'
            ,'IncreStatus'
            ,'UseAmount'
            ,'Ent_Note'
            ,'ClaimSendingClass'
            ,'Deli_JournalIncDate'
            ,'Deli_DeliveryMethod'
            ,'Deli_JournalNumber'
            ,'Deli_JournalNumberAlert'
            ,'ExecScheduleDate'
            ,'ApprovalDate'
            ,'RegistDate'
            ,'OutOfAmends'
            ,'RealCancelStatus'
            ,'CancelReasonCode'
            ,'ArrivalConfirmAlert'
            ,'RegistName'
            ,'UpdateDate'
            ,'UpdateName'
            ,'IsWaitForReceipt'
        );
        foreach ($cs_show as $row) {
            $index = array_search($row, $cs_hide);
            // NOTE : ここで$indexがfalse(検出できない)になることは考慮不要
            unset($cs_hide[$index]);
        }

        // 基準となるTemplateSeqの取得
        $templateSeq = $this->dbAdapter->query(" SELECT TemplateSeq FROM M_TemplateHeader where TemplateId = 'CKA01005_1' AND Seq = 0 "
            )->execute(null)->current()['TemplateSeq'];

        $mdlth = new TableTemplateHeader($this->dbAdapter);
        $mdltf = new TableTemplateField($this->dbAdapter);
        $userId = 9;// 移行ユーザ

        // サイト情報分登録処理
        $ri_site = $this->dbAdapter->query(" SELECT SiteId FROM T_Site WHERE EnterpriseId = :EnterpriseId "
            )->execute(array(':EnterpriseId' => $enterpriseId));
        foreach ($ri_site as $row_site) {

            // M_TemplateHeader登録
            $ary_th = array (
                    'TemplateId'        => 'CKA01005_1',
                    'TemplateClass'     => 2,
                    'Seq'               => $enterpriseId,
                    'TemplatePattern'   => $row_site['SiteId'],
                    'TemplateName'      => '取引履歴検索結果CSV',
                    'TitleClass'        => 1,
                    'DelimiterValue'    => ',',
                    'EncloseValue'      => '"',
                    'CharacterCode'     => '*',
                    'RegistId'          => $userId,
                    'UpdateId'          => $userId,
            );
            $newTemplateSeq = $mdlth->saveNew($ary_th);

            // M_TemplateField登録
            $listNumber = 0;
            // (表示項目)
            foreach ($cs_show as $row) {
                $listNumber += 1;   // インクリメント

                $row_tf = $this->dbAdapter->query(" SELECT * FROM M_TemplateField where TemplateSeq = :TemplateSeq AND PhysicalName = :PhysicalName "
                    )->execute(array(':TemplateSeq' => $templateSeq, ':PhysicalName' => $row))->current();

                $row_tf['TemplateSeq']  = $newTemplateSeq;
                $row_tf['ListNumber']   = $listNumber;
                $row_tf['RegistId']     = $userId;
                $row_tf['UpdateId']     = $userId;
                $row_tf['ValidFlg']     = 1;// 有効

                $mdltf->saveNew($row_tf);// INSERT
            }
            // (非表示項目)
            foreach ($cs_hide as $row) {
                $listNumber += 1;   // インクリメント

                $row_tf = $this->dbAdapter->query(" SELECT * FROM M_TemplateField where TemplateSeq = :TemplateSeq AND PhysicalName = :PhysicalName "
                )->execute(array(':TemplateSeq' => $templateSeq, ':PhysicalName' => $row))->current();

                $row_tf['TemplateSeq']  = $newTemplateSeq;
                $row_tf['ListNumber']   = $listNumber;
                $row_tf['RegistId']     = $userId;
                $row_tf['UpdateId']     = $userId;
                $row_tf['ValidFlg']     = 0;// 無効

                $mdltf->saveNew($row_tf);// INSERT
            }
        }
    }
}

Application::getInstance()->run();
