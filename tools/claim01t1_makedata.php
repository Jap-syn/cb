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
use models\Table\TableBusinessCalendar;
use models\Table\TableClaimBatchControl;

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

$this->logger->info('claim01_makedata.php start');
$this->logger->info('claim01t1_makedata.php start');

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

            // メイン処理([1.処理開始の処理]⇒[2.各スレッドスタート]、までを実施) ※東洋紙業稼働日非稼働日を問わない
            $this->main();

$this->logger->info('claim01t1_makedata.php end');
            $exitCode = 0;

        } catch( \Exception $e ) {
            // エラーログを出力
            if ( isset($this->logger) ) {
                $this->logger->err($e->getMessage());
            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);
    }

    /**
     * メイン処理
     * ([1.処理開始の処理]⇒[2.各スレッドスタート(非同期にて)]、までを実施)
     *
     * @return null
     * @see [初回SMBC][再請求]はﾏﾙﾁｽﾚｯﾄﾞ化しない
     */
    protected function main()
    {
        // 1. 処理開始の処理
        $mdlctm = new \models\Table\TableClaimThreadManage($this->dbAdapter);
        $mdlctm->updateForStart();

        // 2. 各スレッドスタート
        if (\Coral\Base\BaseProcessInfo::isWin()) {
            $fp = popen('start php ' . __DIR__ . '/claim01t2_makedata.php Rw1', 'r');
            pclose($fp);
            $fp = popen('start php ' . __DIR__ . '/claim01t2_makedata.php Rw2', 'r');
            pclose($fp);
            $fp = popen('start php ' . __DIR__ . '/claim01t2_makedata.php Rw3', 'r');
            pclose($fp);
            $fp = popen('start php ' . __DIR__ . '/claim01t2_makedata.php Rw4', 'r');
            pclose($fp);
            $fp = popen('start php ' . __DIR__ . '/claim01t2_makedata.php Rw5', 'r');
            pclose($fp);
            $fp = popen('start php ' . __DIR__ . '/claim01t2_makedata.php Rw6', 'r');
            pclose($fp);
            $fp = popen('start php ' . __DIR__ . '/claim01t2_makedata.php Rw7', 'r');
            pclose($fp);
            $fp = popen('start php ' . __DIR__ . '/claim01t2_makedata.php Rw8', 'r');
            pclose($fp);
            $fp = popen('start php ' . __DIR__ . '/claim01t2_makedata.php Rw9', 'r');
            pclose($fp);
            $fp = popen('start php ' . __DIR__ . '/claim01t2_makedata.php Rw0', 'r');
            pclose($fp);
        }
        else {
            exec('php ' . __DIR__ . '/claim01t2_makedata.php Rw1 > /dev/null &');
            exec('php ' . __DIR__ . '/claim01t2_makedata.php Rw2 > /dev/null &');
            exec('php ' . __DIR__ . '/claim01t2_makedata.php Rw3 > /dev/null &');
            exec('php ' . __DIR__ . '/claim01t2_makedata.php Rw4 > /dev/null &');
            exec('php ' . __DIR__ . '/claim01t2_makedata.php Rw5 > /dev/null &');
            exec('php ' . __DIR__ . '/claim01t2_makedata.php Rw6 > /dev/null &');
            exec('php ' . __DIR__ . '/claim01t2_makedata.php Rw7 > /dev/null &');
            exec('php ' . __DIR__ . '/claim01t2_makedata.php Rw8 > /dev/null &');
            exec('php ' . __DIR__ . '/claim01t2_makedata.php Rw9 > /dev/null &');
            exec('php ' . __DIR__ . '/claim01t2_makedata.php Rw0 > /dev/null &');
        }

        return null;
    }
}

Application::getInstance()->run();
