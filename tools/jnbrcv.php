<?php
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

use Coral\Base\Application\BaseApplicationAbstract;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use Coral\Base\BaseLog;
use models\Logic\Jnb\LogicJnbCommon;
use models\Logic\Jnb\LogicJnbNotificationHandler;

setlocale( LC_ALL, 'ja_JP.UTF-8' );
ini_set( 'default_charset', 'UTF-8' );
mb_internal_encoding( 'UTF-8' );
mb_http_output('UTF-8');
mb_regex_encoding( 'UTF-8' );

ini_set( 'max_execution_time', 600 );

/**
 * JNB入金通知受信アプリケーション
 *
 */
class Application extends BaseApplicationAbstract {
	protected $_application_id = 'jnb-notification-receiver';

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
	    // iniファイルから設定を取得
	    $configPath = __DIR__ . '/../module/cbadmin/config/config.ini';

	    // データベースアダプタをiniファイルから初期化します
	    $data = array();
	    if (file_exists($configPath))
	    {
	        $reader = new Ini();
	        $data = $reader->fromFile($configPath);
	    }
		// データベースアダプタをiniファイルから初期化します
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

        // JNB共通ロジッククラスにロガーを割り当てる
        LogicJnbCommon::setDefaultLogger($this->logger);

        // 通知処理ロジック初期化
        $logic = new LogicJnbNotificationHandler($this->dbAdapter);

        try {
            $result = $logic->process($_POST);
            echo http_build_query($result);
        } catch(\Exception $err) {
            $this->logger->err(sprintf('CRITICAL ERROR !!!! error = %s', $err->getMessage()));
            $this->return500Error();
        }
	}

	/**
	 * 500エラーレスポンスを返す
	 */
	public function return500Error() {
			header('HTTP/1.1 500 Internal Server Error');
			header('Content-Type: text/html; charset=utf-8');
			$src = <<<EOH
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>500 Internal Server Error</title>
</head><body>
<h1>Internal Server Error</h1>
<p>
The server encountered an internal error and was
unable to complete your request. Either the server is
overloaded or there was an error in a CGI script.
</p>
</body></html>
EOH;
			die($src);
	}
}

Application::getInstance()->run();
