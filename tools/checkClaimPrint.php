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
use models\Table\TableClaimPrintCheck;
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

$this->logger->info('checkClaimPrint.php start');

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
$this->logger->info('checkClaimPrint.php end');

        // 終了コードを指定して処理終了
        exit($exitCode);
    }

    protected function main($userId)
    {
        $mdlCpp = new TableClaimPrintPattern($this->dbAdapter);
        $mdlCpc = new TableClaimPrintCheck($this->dbAdapter);
        $logic = new LogicClaimPrint($this->dbAdapter);

        $error1 = array();
        $error1_wk = array();
        $error2 = array();
        $error2_wk = array();

        $sql = <<<EOQ
SELECT cpp.*
     , s.*
    , IFNULL(e.OemId, 0) OemId
    , e.CreditTransferFlg
    , e.LoginId
    , e.EnterpriseNameKj
    , e.BillingAgentFlg
 FROM T_ClaimPrintPattern cpp
 INNER JOIN T_Site s ON cpp.SiteId=s.SiteId AND s.ValidFlg = 1 AND cpp.ValidFlg=s.ValidFlg
 INNER JOIN T_Enterprise e ON cpp.EnterpriseId=e.EnterpriseId AND e.ValidFlg = 1 AND e.ServiceInDate IS NOT NULL
 ORDER BY cpp.EnterpriseId,cpp.SiteId
EOQ;

        $targets = $this->dbAdapter->query($sql)->execute(null);

        foreach ($targets as $target) {
            $data_cpc = $mdlCpc->find($target['PrintFormCd'], $target['PrintTypeCd'], $target['PrintIssueCd'], $target['PrintIssueCountCd']);
            if ($data_cpc->count() == 0) {
                if (!isset($error1_wk[$target['LoginId'].'-'.$target['SiteId']])) {
                    $error1_wk[$target['LoginId'].'-'.$target['SiteId']] = 1;
                    $error1[] = '印刷パターンマスタ　事業者ID:'.$target['LoginId'].'　事業者名：'.$target['EnterpriseNameKj'].'　サイトID：'.$target['SiteId'];
                }
            }
            $check = $logic->paymentCheck($target['PrintPatternCd'], $target['SpPaymentCd']);
            if ($check === false) {
                if (!isset($error2_wk[$target['LoginId'].'-'.$target['SiteId']])) {
                    $error2_wk[$target['LoginId'].'-'.$target['SiteId']] = 1;
                    $error2[] = '支払方法マスタ　　　事業者ID:'.$target['LoginId'].'　事業者名：'.$target['EnterpriseNameKj'].'　サイトID：'.$target['SiteId'];
                }
            }
//            // test用
//            if ($target['SiteId'] > 100) {
//                break;
//            }
        }

        if ((sizeof($error1) > 0) || (sizeof($error2) > 0)) {
            $mail = new CoralMail($this->dbAdapter, $this->mail['smtp']);
            $error = array_merge($error1, $error2);
            $mail->ClaimPrintErrorMailToCb($error, $userId);
        }
    }
}

Application::getInstance()->run();
