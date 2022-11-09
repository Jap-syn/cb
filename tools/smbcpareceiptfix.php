<?php
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

use Coral\Base\Application\BaseApplicationAbstract;
use Coral\Base\BaseLog;
use Zend\Config\Reader\Ini;
use models\Logic\Smbcpa\LogicSmbcpaCommon;
use models\Logic\Smbcpa\LogicSmbcpaAccount;
use Zend\Db\Adapter\Adapter;

setlocale( LC_ALL, 'ja_JP.UTF-8' );
ini_set( 'default_charset', 'UTF-8' );
mb_internal_encoding( 'UTF-8' );
mb_http_output('UTF-8');
mb_regex_encoding( 'UTF-8' );

ini_set( 'max_execution_time', 0 );

/**
 * SMBCバーチャル口座開放バッチ
 */
class Application extends BaseApplicationAbstract {
    /** 入金処理ループの実行インターバル（秒） @var int */
    const BATCH_LOOP_INTERVAL = 30;

    /**
     * アプリケーション固有ID
     *
     * @access protected
     * @var string
     */
    protected $_application_id = 'smbcpa-rcpt-fix-batch';

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
     * DBアダプタ
     *
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
        $configPath = __DIR__ . '/../module/cbadmin/config/config.ini';

        // データベースアダプタをiniファイルから初期化します
        $data = array();
        if (file_exists($configPath))
        {
            $reader = new Ini();
            $data = $reader->fromFile($configPath);
        }

        $this->dbAdapter = new Adapter($data['database']);

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

        // ログ設定の読み込み
        $logConfig = $data['log'];
        // 標準ログクラス初期化
        $this->logger = BaseLog::createFromArray( $logConfig );
        LogicSmbcpaCommon::setDefaultLogger($this->logger);

        $db = $this->dbAdapter;
        $logic = new LogicSmbcpaAccount($db);
        $this->logger->debug('batch process start.');
        while(($target_count = $logic->countContradictedAccounts()) > 0) {
            $this->logger->debug(sprintf('target count: %s accounts.', $target_count));
            try {
                $logic->sweepContradictedAccounts();
                $this->logger->debug(sprintf('%s accounts sweeped.', $target_count));
                break;
            } catch(\Exception $err) {
                $this->logger->info(sprintf('an error has occred. error = %s', $err->getMessage()));
            }
            sleep(5);
        }
        $this->logger->debug('batch process completed.');
    }
}

Application::getInstance()->run();
