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

$this->logger->info('CB_B2C_DEV-396.php start');

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
$this->logger->info('CB_B2C_DEV-396.php end');

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

//        $sql = <<<EOQ
//SELECT s.*,IFNULL(e.OemId, 0) OemId,e.CreditTransferFlg,e.BillingAgentFlg FROM T_Site s INNER JOIN T_Enterprise e ON s.EnterpriseId=e.EnterpriseId ORDER BY SiteId
//EOQ;
        $sql = <<<EOQ
SELECT s.*,IFNULL(e.OemId, 0) OemId,e.CreditTransferFlg,e.BillingAgentFlg 
FROM T_Site s INNER JOIN T_Enterprise e ON s.EnterpriseId=e.EnterpriseId 
WHERE s.RegistDate >= '2021-05-01 00:00:00'
ORDER BY SiteId
EOQ;
        $works = $this->dbAdapter->query($sql)->execute(null);
        foreach ($works as $work) {
            $sid = $work['SiteId'];
            if (!isset($idx[$sid])) {
                $idx[$sid] = 1;
                $sites[] = $work;
            }
        }

        $sql = <<<EOQ
SELECT s.*,IFNULL(e.OemId, 0) OemId,e.CreditTransferFlg,e.BillingAgentFlg 
FROM T_Site s INNER JOIN T_Enterprise e ON s.EnterpriseId=e.EnterpriseId 
INNER JOIN T_Order o ON s.SiteId=o.SiteId AND o.RegistDate >= '2021-05-01 00:00:00' AND o.RegistDate < '2021-06-01 00:00:00'
WHERE s.RegistDate < '2021-05-01 00:00:00'
ORDER BY SiteId
EOQ;
        $works = $this->dbAdapter->query($sql)->execute(null);
        foreach ($works as $work) {
            $sid = $work['SiteId'];
            if (!isset($idx[$sid])) {
                $idx[$sid] = 1;
                $sites[] = $work;
            }
        }

        $sql = <<<EOQ
SELECT s.*,IFNULL(e.OemId, 0) OemId,e.CreditTransferFlg,e.BillingAgentFlg 
FROM T_Site s INNER JOIN T_Enterprise e ON s.EnterpriseId=e.EnterpriseId 
INNER JOIN T_Order o ON s.SiteId=o.SiteId AND o.RegistDate >= '2021-06-01 00:00:00' AND o.RegistDate < '2021-07-01 00:00:00'
WHERE s.RegistDate < '2021-05-01 00:00:00'
ORDER BY SiteId
EOQ;
        $works = $this->dbAdapter->query($sql)->execute(null);
        foreach ($works as $work) {
            $sid = $work['SiteId'];
            if (!isset($idx[$sid])) {
                $idx[$sid] = 1;
                $sites[] = $work;
            }
        }

        $sql = <<<EOQ
SELECT s.*,IFNULL(e.OemId, 0) OemId,e.CreditTransferFlg,e.BillingAgentFlg 
FROM T_Site s INNER JOIN T_Enterprise e ON s.EnterpriseId=e.EnterpriseId 
INNER JOIN T_Order o ON s.SiteId=o.SiteId AND o.RegistDate >= '2021-07-01 00:00:00' AND o.RegistDate < '2021-08-01 00:00:00'
WHERE s.RegistDate < '2021-05-01 00:00:00'
ORDER BY SiteId
EOQ;
        $works = $this->dbAdapter->query($sql)->execute(null);
        foreach ($works as $work) {
            $sid = $work['SiteId'];
            if (!isset($idx[$sid])) {
                $idx[$sid] = 1;
                $sites[] = $work;
            }
        }

        $sql = <<<EOQ
SELECT s.*,IFNULL(e.OemId, 0) OemId,e.CreditTransferFlg,e.BillingAgentFlg 
FROM T_Site s INNER JOIN T_Enterprise e ON s.EnterpriseId=e.EnterpriseId 
INNER JOIN T_Order o ON s.SiteId=o.SiteId AND o.RegistDate >= '2021-08-01 00:00:00' AND o.RegistDate < '2021-09-01 00:00:00'
WHERE s.RegistDate < '2021-05-01 00:00:00'
ORDER BY SiteId
EOQ;
        $works = $this->dbAdapter->query($sql)->execute(null);
        foreach ($works as $work) {
            $sid = $work['SiteId'];
            if (!isset($idx[$sid])) {
                $idx[$sid] = 1;
                $sites[] = $work;
            }
        }

        $sql = <<<EOQ
SELECT s.*,IFNULL(e.OemId, 0) OemId,e.CreditTransferFlg,e.BillingAgentFlg 
FROM T_Site s INNER JOIN T_Enterprise e ON s.EnterpriseId=e.EnterpriseId 
INNER JOIN T_Order o ON s.SiteId=o.SiteId AND o.RegistDate >= '2021-09-01 00:00:00' AND o.RegistDate < '2021-10-01 00:00:00'
WHERE s.RegistDate < '2021-05-01 00:00:00'
ORDER BY SiteId
EOQ;
        $works = $this->dbAdapter->query($sql)->execute(null);
        foreach ($works as $work) {
            $sid = $work['SiteId'];
            if (!isset($idx[$sid])) {
                $idx[$sid] = 1;
                $sites[] = $work;
            }
        }

        $sql = <<<EOQ
SELECT s.*,IFNULL(e.OemId, 0) OemId,e.CreditTransferFlg,e.BillingAgentFlg 
FROM T_Site s INNER JOIN T_Enterprise e ON s.EnterpriseId=e.EnterpriseId 
INNER JOIN T_Order o ON s.SiteId=o.SiteId AND o.RegistDate >= '2021-10-01 00:00:00' AND o.RegistDate < '2021-11-01 00:00:00'
WHERE s.RegistDate < '2021-05-01 00:00:00'
ORDER BY SiteId
EOQ;
        $works = $this->dbAdapter->query($sql)->execute(null);
        foreach ($works as $work) {
            $sid = $work['SiteId'];
            if (!isset($idx[$sid])) {
                $idx[$sid] = 1;
                $sites[] = $work;
            }
        }

        $sql = <<<EOQ
SELECT s.*,IFNULL(e.OemId, 0) OemId,e.CreditTransferFlg,e.BillingAgentFlg 
FROM T_Site s INNER JOIN T_Enterprise e ON s.EnterpriseId=e.EnterpriseId 
INNER JOIN T_Order o ON s.SiteId=o.SiteId AND o.RegistDate >= '2021-11-01 00:00:00' AND o.RegistDate < '2021-12-01 00:00:00'
WHERE s.RegistDate < '2021-05-01 00:00:00'
ORDER BY SiteId
EOQ;
        $works = $this->dbAdapter->query($sql)->execute(null);
        foreach ($works as $work) {
            $sid = $work['SiteId'];
            if (!isset($idx[$sid])) {
                $idx[$sid] = 1;
                $sites[] = $work;
            }
        }

        $sql = <<<EOQ
SELECT s.*,IFNULL(e.OemId, 0) OemId,e.CreditTransferFlg,e.BillingAgentFlg 
FROM T_Site s INNER JOIN T_Enterprise e ON s.EnterpriseId=e.EnterpriseId 
INNER JOIN T_Order o ON s.SiteId=o.SiteId AND o.RegistDate >= '2021-12-01 00:00:00' AND o.RegistDate < '2022-01-01 00:00:00'
WHERE s.RegistDate < '2021-05-01 00:00:00'
ORDER BY SiteId
EOQ;
        $works = $this->dbAdapter->query($sql)->execute(null);
        foreach ($works as $work) {
            $sid = $work['SiteId'];
            if (!isset($idx[$sid])) {
                $idx[$sid] = 1;
                $sites[] = $work;
            }
        }

        $sql = <<<EOQ
SELECT s.*,IFNULL(e.OemId, 0) OemId,e.CreditTransferFlg,e.BillingAgentFlg 
FROM T_Site s INNER JOIN T_Enterprise e ON s.EnterpriseId=e.EnterpriseId 
INNER JOIN T_Order o ON s.SiteId=o.SiteId AND o.RegistDate >= '2022-01-01 00:00:00' AND o.RegistDate < '2022-02-01 00:00:00'
WHERE s.RegistDate < '2021-05-01 00:00:00'
ORDER BY SiteId
EOQ;
        $works = $this->dbAdapter->query($sql)->execute(null);
        foreach ($works as $work) {
            $sid = $work['SiteId'];
            if (!isset($idx[$sid])) {
                $idx[$sid] = 1;
                $sites[] = $work;
            }
        }

        $sql = <<<EOQ
SELECT s.*,IFNULL(e.OemId, 0) OemId,e.CreditTransferFlg,e.BillingAgentFlg 
FROM T_Site s INNER JOIN T_Enterprise e ON s.EnterpriseId=e.EnterpriseId 
INNER JOIN T_Order o ON s.SiteId=o.SiteId AND o.RegistDate >= '2022-02-01 00:00:00' AND o.RegistDate < '2022-03-01 00:00:00'
WHERE s.RegistDate < '2021-05-01 00:00:00'
ORDER BY SiteId
EOQ;
        $works = $this->dbAdapter->query($sql)->execute(null);
        foreach ($works as $work) {
            $sid = $work['SiteId'];
            if (!isset($idx[$sid])) {
                $idx[$sid] = 1;
                $sites[] = $work;
            }
        }

        $sql = <<<EOQ
SELECT s.*,IFNULL(e.OemId, 0) OemId,e.CreditTransferFlg,e.BillingAgentFlg 
FROM T_Site s INNER JOIN T_Enterprise e ON s.EnterpriseId=e.EnterpriseId 
INNER JOIN T_Order o ON s.SiteId=o.SiteId AND o.RegistDate >= '2022-03-01 00:00:00' AND o.RegistDate < '2022-04-01 00:00:00'
WHERE s.RegistDate < '2021-05-01 00:00:00'
ORDER BY SiteId
EOQ;
        $works = $this->dbAdapter->query($sql)->execute(null);
        foreach ($works as $work) {
            $sid = $work['SiteId'];
            if (!isset($idx[$sid])) {
                $idx[$sid] = 1;
                $sites[] = $work;
            }
        }

        $sql = <<<EOQ
SELECT s.*,IFNULL(e.OemId, 0) OemId,e.CreditTransferFlg,e.BillingAgentFlg 
FROM T_Site s INNER JOIN T_Enterprise e ON s.EnterpriseId=e.EnterpriseId 
WHERE s.SiteId IN (SELECT DISTINCT SiteId FROM T_Order WHERE RegistDate >= '2022-04-01 00:00:00' AND RegistDate < '2022-05-01 00:00:00')
ORDER BY SiteId
EOQ;
        $works = $this->dbAdapter->query($sql)->execute(null);
        foreach ($works as $work) {
            $sid = $work['SiteId'];
            if (!isset($idx[$sid])) {
                $idx[$sid] = 1;
                $sites[] = $work;
            }
        }

        $sql = <<<EOQ
SELECT s.*,IFNULL(e.OemId, 0) OemId,e.CreditTransferFlg,e.BillingAgentFlg 
FROM T_Site s INNER JOIN T_Enterprise e ON s.EnterpriseId=e.EnterpriseId 
WHERE s.SiteId IN (SELECT DISTINCT SiteId FROM T_Order WHERE RegistDate >= '2022-05-01 00:00:00' AND RegistDate < '2022-06-01 00:00:00')
ORDER BY SiteId
EOQ;
        $works = $this->dbAdapter->query($sql)->execute(null);
        foreach ($works as $work) {
            $sid = $work['SiteId'];
            if (!isset($idx[$sid])) {
                $idx[$sid] = 1;
                $sites[] = $work;
            }
        }

        $this->submain($sites);
    }

    private function submain($sites)
    {
        $claimPrintPattern = new TableClaimPrintPattern($this->dbAdapter);
        $claimPrint = new LogicClaimPrint($this->dbAdapter);

        foreach ($sites as $site) {
            $claimPatterns = array(0,1,2,4,6,7,8,9);
            if ($site['CreditTransferFlg'] == 0) {
                $claimPatterns = array(1,2,4,6,7,8,9);
            }
            if ($site['BillingAgentFlg'] == 1) {
                $claimPatterns = array(1);
            }
            foreach ($claimPatterns as $claimPattern) {
                $data = $claimPrint->create($site['OemId'], $site['EnterpriseId'], $site['SiteId'], $claimPattern, $site['PaymentAfterArrivalFlg'], $site['FirstClaimLayoutMode'], $site['MufjBarcodeUsedFlg'], $site['ClaimMypagePrint']);

                $work = $claimPrintPattern->find($site['EnterpriseId'], $site['SiteId'], $data['PrintIssueCountCd']);
                if ($work->count() > 0) {
                    $pkey = $work->current()['ClaimPrintPatternSeq'];
                    $data['EnterpriseId'] = $site['EnterpriseId'];
                    $data['SiteId'] = $site['SiteId'];
                    $data['UpdateId'] = $userId;
                    $claimPrintPattern->saveUpdate($data, $pkey);
                } else {
                    $data['EnterpriseId'] = $site['EnterpriseId'];
                    $data['SiteId'] = $site['SiteId'];
                    $data['RegistId'] = $userId;
                    $claimPrintPattern->saveNew($data);
                }
            }
        }
    }
}

Application::getInstance()->run();

