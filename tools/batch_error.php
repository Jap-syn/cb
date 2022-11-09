<?php
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

use Coral\Base\Application\BaseApplicationAbstract;
use Coral\Base\BaseLog;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use Zend\Db\Adapter\Driver\ResultInterface;
use models\Logic\LogicMail;

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

$this->logger->info('batch_error.php start');

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

            // 本処理
            $this->_exec();

$this->logger->info('batch_error.php end');
            $exitCode = 0; // 正常終了

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
     * 本処理
     */
    private function _exec() {

        // 1. プログラム引数のチェック
        if ($_SERVER['argc'] != 2) {
            return;
        }

        // 2. データベース登録
        $now = date('Y-m-d H:i:s');
        $sql = " INSERT INTO T_BatchLog (OccDate, ProgramName, ValidFlg) VALUES (:OccDate, :ProgramName, :ValidFlg) ";
        $prm = array(
            ':OccDate'      => $now,
            ':ProgramName'  => $_SERVER['argv'][1],
            ':ValidFlg'     => 1,
        );
        $this->dbAdapter->query($sql)->execute($prm);

        // 3. メール送信
        $lcmail = new LogicMail($this->dbAdapter, $this->mail['smtp']);
        $lcmail->SendBatchError($this->mail['system_mail_address'], $now, $_SERVER['argv'][1]);

        return;
    }
}

Application::getInstance()->run();
