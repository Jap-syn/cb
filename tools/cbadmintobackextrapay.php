<?php
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

use Coral\Base\Application\BaseApplicationAbstract;
use Coral\Base\BaseLog;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use models\Logic\LogicMypage;
use models\Table\TableBatchLock;

/**
 * アプリケーションクラスです。
 *
 */
class Application extends BaseApplicationAbstract {
    protected $_application_id = 'tools';

    /**
     * バッチID
     *
     * @var unknown
     */
    const EXECUTE_BATCH_ID = 6;

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
     * メール環境
     */
    public $mail;

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

            // 設定をシステムプロパティテーブルから読み込み
            $apinfo = $this->getApplicationiInfo($this->dbAdapter, 'cbadmin');

            // iniファイルの内容をマージ
            $data = array_merge($data, $apinfo);

            // メールに絡む属性
            $this->mail = $data['mail'];

            // ログ設定の読み込み
            $logConfig = $data['log'];

            // 標準ログクラス初期化
            $this->logger = BaseLog::createFromArray( $logConfig );

$this->logger->info('fromCbadminToMypage.php start');

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
                $this->dbAdapterMypage->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

            // システムステータス
            $mdlbl = new TableBatchLock ( $this->dbAdapter );
            $BatchLock = $mdlbl->getLock( $this::EXECUTE_BATCH_ID );

            if ($BatchLock > 0) {
                // マイページ連携バッチ
                $mypage = new LogicMypage($this->dbAdapter, $this->dbAdapterMypage, $this->mail['smtp'], $this->logger);
                $mypage->fromCbadminToMypage();

                // ロック解除
                $mdlbl->releaseLock( $this::EXECUTE_BATCH_ID );

            } else {
$this->logger->alert("Can't execute by Locking.\r\n");

            }

$this->logger->info('fromCbadminToMypage.php end');
$exitCode = 0;
        } catch(\Exception $e) {
            // エラーログを出力
            if ( isset($this->logger) ) {
$this->logger->err($e->getMessage());
            }
            if (! empty ( $BatchLock )) {
                if ($BatchLock > 0) {
                    // ロック解除
                    $mdlbl->releaseLock( $this::EXECUTE_BATCH_ID );
                }
            }
        }

        // 終了コードを指定して処理終了
        exit($exitCode);

    }
}

Application::getInstance()->run();
