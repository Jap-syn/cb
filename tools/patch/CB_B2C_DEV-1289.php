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
use models\Table\TableSite;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use Zend\Json\Json;
use models\Table\TableUser;
use models\Table\TableClaimBatchControl;
use models\Table\TableOem;
use models\Table\TableSystemProperty;
use models\Table\TableCode;
use models\Table\TableClaimThreadManage;
use models\Table\TableOrderAddInfo;
use models\Logic\LogicTemplate;
use models\Table\TableSiteFreeItems;
use models\Table\TableEnterprise;
use models\Logic\LogicPayeasy;
use models\Table\TableTemplateField;

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

$this->logger->info('CB_B2C_DEV-1289.php start');

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
$this->logger->info('CB_B2C_DEV-1289.php end');

        // 終了コードを指定して処理終了
        exit($exitCode);
    }

    /**
     * 主処理
     * (CSV＆ZIP化)
     *
     * @return boolean true:成功／false:失敗
     */
    protected function main($userId)
    {
        $idx = array();
        $sites = array();

        $esql = <<<EOQ
SELECT e.* 
FROM T_Enterprise e 
ORDER BY EnterpriseId
EOQ;

        $t1sql = <<<EOQ
SELECT F.TemplateSeq
,      F.ListNumber
FROM   M_TemplateHeader H
       INNER JOIN M_TemplateField F ON F.TemplateSeq = H.TemplateSeq
WHERE  H.TemplateId    = 'CKA01005_1'
AND    H.TemplateClass = 2
AND    H.Seq           = :EnterpriseId
AND    (F.PhysicalName = 'IsWaitForReceipt' or
        F.PhysicalName = 'ReceiptDate' or
        F.PhysicalName = 'ReceiptProcessDate' or
        F.PhysicalName = 'ReceiptClass')
AND    F.ValidFlg      = 1
EOQ;

        $t2sql = <<<EOQ
SELECT F.TemplateSeq
,      F.ListNumber
FROM   M_TemplateHeader H
       INNER JOIN M_TemplateField F ON F.TemplateSeq = H.TemplateSeq
WHERE  H.TemplateId    = 'CKA01005_1'
AND    H.TemplateClass = 2
AND    H.Seq           = :EnterpriseId
AND    (F.PhysicalName = 'CreditTransferRequestFlg' or
        F.PhysicalName = 'RequestStatus' or
        F.PhysicalName = 'RequestSubStatus' or
        F.PhysicalName = 'RequestCompDate' or
        F.PhysicalName = 'CreditTransferMethod1' or
        F.PhysicalName = 'CreditTransferMethod2')
AND    F.ValidFlg      = 1
EOQ;

        $t3sql = <<<EOQ
SELECT F.TemplateSeq
,      F.ListNumber
FROM   M_TemplateHeader H
       INNER JOIN M_TemplateField F ON F.TemplateSeq = H.TemplateSeq
WHERE  H.TemplateId    = 'CKA01005_1'
AND    H.TemplateClass = 2
AND    H.Seq           = :EnterpriseId
AND    H.TemplatePattern = :SiteId
AND    (F.PhysicalName = 'ExtraPayKey')
AND    F.ValidFlg      = 1
EOQ;

        $t4sql = <<<EOQ
SELECT F.TemplateSeq
,      F.ListNumber
FROM   M_TemplateHeader H
       INNER JOIN M_TemplateField F ON F.TemplateSeq = H.TemplateSeq
WHERE  H.TemplateId    = 'CKA01005_1'
AND    H.TemplateClass = 2
AND    H.Seq           = :EnterpriseId
AND    H.TemplatePattern = :SiteId
ORDER BY F.ListNumber
EOQ;

        $t5sql = <<<EOQ
SELECT F.TemplateSeq
,      F.ListNumber
FROM   M_TemplateHeader H
       INNER JOIN M_TemplateField F ON F.TemplateSeq = H.TemplateSeq
WHERE  H.TemplateId    = 'CKA04016_1'
AND    H.TemplateClass = 2
AND    H.Seq           = :EnterpriseId
ORDER BY F.ListNumber
EOQ;

        $t6sql = <<<EOQ
SELECT COUNT(DISTINCT(PhysicalName)) KENSU
FROM M_TemplateField
WHERE TemplateSeq=:TemplateSeq
EOQ;

        $updsql = <<<EOQ
UPDATE M_TemplateField
SET ListNumber=:UpdListNumber, UpdateId=:UpdateId, UpdateDate = :UpdateDate
WHERE TemplateSeq=:TemplateSeq AND ListNumber=:ListNumber 
EOQ;

        $mdltf = new TableTemplateField($this->dbAdapter);
        $tblSite = new TableSite($this->dbAdapter);
        $edatas = $this->dbAdapter->query($esql)->execute(null);
        foreach ($edatas as $edata) {
            if ($edata['ReceiptStatusSearchClass'] == 0) {
                $ri = $this->dbAdapter->query($t1sql)->execute(array( 'EnterpriseId' => $edata['EnterpriseId'] ));
                $fields = ResultInterfaceToArray($ri);
                foreach($fields as $field) {
                    $mdltf->saveUpdate(array(
                                           'ValidFlg' => 0,
                                           'UpdateId' => $userId,
                                       ), $field['TemplateSeq'], $field['ListNumber']);
                }
            }
            if ($edata['CreditTransferFlg'] == 0) {
                $ri = $this->dbAdapter->query($t2sql)->execute(array( 'EnterpriseId' => $edata['EnterpriseId'] ));
                $fields = ResultInterfaceToArray($ri);
                foreach($fields as $field) {
                    $mdltf->saveUpdate(array(
                                           'ValidFlg' => 0,
                                           'UpdateId' => $userId,
                                       ), $field['TemplateSeq'], $field['ListNumber']);
                }
            }
            $ri = $this->dbAdapter->query($t5sql)->execute(array( 'EnterpriseId' => $edata['EnterpriseId'] ));
            $fields = ResultInterfaceToArray($ri);
            $i = 1;
            $sw = false;
            foreach($fields as $field) {
                if ($field['ListNumber'] != $i) {
                    $this->dbAdapter->query($updsql)->execute(array( ':TemplateSeq' => $field['TemplateSeq'], ':ListNumber' => $field['ListNumber'], ':UpdListNumber' => $i, ':UpdateId' => $userId, ':UpdateDate' => date('Y-m-d H:i:s') ));
                    if (!$sw) {
                        $this->logger->info('['.$edata['EnterpriseId'].']CKA04016_1 Update TemplateSeq='.$field['TemplateSeq']);
                        $sw = true;
                    }
                }
                $i++;
            }
            if ((sizeof($fields) > 0) && (sizeof($fields) != 62)) {
                $this->logger->info('['.$edata['EnterpriseId'].']CKA04016_1 Count Error TemplateSeq='.$field['TemplateSeq']);
            }
            if (sizeof($fields) > 0) {
                $kensu = $this->dbAdapter->query($t6sql)->execute(array( ':TemplateSeq' => $fields[0]['TemplateSeq'] ))->current()['kensu'];
                if ($kensu != sizeof($fields)) {
                    $this->logger->info('['.$edata['EnterpriseId'].']CKA04016_1 dup Error TemplateSeq='.$field['TemplateSeq']);
                }
            }

            $sites = ResultInterfaceToArray($tblSite->getAll($edata['EnterpriseId']));
            foreach ($sites as $site) {
                if ($site['PaymentAfterArrivalFlg'] == 1) {
                    $ri = $this->dbAdapter->query($t3sql)->execute(array( 'EnterpriseId' => $edata['EnterpriseId'], 'SiteId' => $site['SiteId'] ));
                    $fields = ResultInterfaceToArray($ri);
                    foreach($fields as $field) {
                        $mdltf->saveUpdate(array(
                                               'ValidFlg' => 0,
                                               'UpdateId' => $userId,
                                           ), $field['TemplateSeq'], $field['ListNumber']);
                    }
                }
                $ri = $this->dbAdapter->query($t4sql)->execute(array( 'EnterpriseId' => $edata['EnterpriseId'], 'SiteId' => $site['SiteId'] ));
                $fields = ResultInterfaceToArray($ri);
                $i = 1;
                $sw = false;
                foreach($fields as $field) {
                    if ($field['ListNumber'] != $i) {
                        $this->dbAdapter->query($updsql)->execute(array( ':TemplateSeq' => $field['TemplateSeq'], ':ListNumber' => $field['ListNumber'], ':UpdListNumber' => $i, ':UpdateId' => $userId, ':UpdateDate' => date('Y-m-d H:i:s') ));
                        if (!$sw) {
                            $this->logger->info('['.$edata['EnterpriseId'].']CKA01005_1 Update TemplateSeq='.$field['TemplateSeq']);
                            $sw = true;
                        }
                    }
                    $i++;
                }
                if ((sizeof($fields) > 0) && (sizeof($fields) != 47)) {
                    $this->logger->info('['.$edata['EnterpriseId'].']CKA01005_1 Count Error TemplateSeq='.$field['TemplateSeq']);
                }
                if (sizeof($fields) > 0) {
                    $kensu = $this->dbAdapter->query($t6sql)->execute(array( ':TemplateSeq' => $fields[0]['TemplateSeq'] ))->current()['kensu'];
                    if ($kensu != sizeof($fields)) {
                        $this->logger->info('['.$edata['EnterpriseId'].']CKA01005_1 dup Error TemplateSeq='.$field['TemplateSeq']);
                    }
                }
            }
        }
    }
}

Application::getInstance()->run();

