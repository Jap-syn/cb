<?php
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

use Coral\Base\Application\BaseApplicationAbstract;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use models\Logic\LogicNgAccessIp;
use Coral\Base\BaseLog;

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
     * @var Adapter
     */
    public $dbAdapterMypage;

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

            // マイページ用
            $configPathMypage = __DIR__ . '/../module/mypage/config/config.ini';
            // データベースアダプタをiniファイルから初期化します
            $dataMypage = array();
            if (file_exists($configPathMypage))
            {
                $readerMypage = new Ini();
                $dataMypage = $readerMypage->fromFile($configPathMypage);
            }

            $this->dbAdapterMypage = new Adapter($dataMypage['database']);

            // ログ設定の読み込み
            $logConfig = $data['log'];
            // 標準ログクラス初期化
            $this->logger = BaseLog::createFromArray( $logConfig );

            $this->logger->info('ngaccessclear.php start');

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
                $this->dbAdapterMypage->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

            // プログラム引数の件数チェック
            if ($_SERVER['argc'] != 2) {
                $this->logger->warn('(ngaccessclear.php)It does not match the number of arguments. argc=' . $_SERVER['argc']);
                exit(0);
            }

            // プログラム引数の型チェック
            if (!is_numeric($_SERVER['argv'][1])) {
                $this->logger->warn('(ngaccessclear.php)The argument is not a number. argv=' . $_SERVER['argv'][1]);
                exit(0);
            }
            $serverNo = (int)$_SERVER['argv'][1];

            // 不正アクセス解除
            $obj = new LogicNgAccessIp($this->dbAdapter);
            $obj->clearNgAccess($this->dbAdapter, $this->dbAdapterMypage, $serverNo);

            $this->logger->info('ngaccessclear.php end');
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
}

Application::getInstance()->run();
