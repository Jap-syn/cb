<?php
chdir(dirname(__DIR__));

require 'init_autoloader.php';

use Coral\Base\Application\BaseApplicationAbstract;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Reader\Ini;
use Coral\Base\BaseLog;

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

$this->logger->info('logcompress.php start');

            $globalConfig = include __DIR__ . '/../config/autoload/global.php';
            // 接続時間を設定する
            $rds_session_timezone = $globalConfig['RDS_SESSION_TIMEZONE'];
            if (isset($rds_session_timezone)) {
                $this->dbAdapter->query('SET SESSION time_zone = :time_zone')->execute(array(':time_zone'=>$rds_session_timezone));
            }

            // 指定フォルダ下の[.txt]ファイルをZIP保管する
            $this->_execute($data);

$this->logger->info('logcompress.php end');
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
     * 指定フォルダ下の[.txt]ファイルをZIP保管する
     *
     * @param array $config 設定
     */
	protected function _execute($data) {

        // ファイル列挙
        $fpath_array = array();
        $pattern = $data['log']['log_dir'] . '/*.txt';
        foreach (glob($pattern) as $file) {
            if (is_file($file) && !strstr($file, date('Ymd'))) {
                $fpath_array[] = $file;
            }
        }

        // ZIPファイル作成
        $zip = new \ZipArchive();

        // 出力時刻
        $formatNowStr = date('Ymd', strtotime(' -1 day '));

        // 出力ファイル名
        $outFileName = $data['log']['log_dir'] . '/' . ('logs_' . $formatNowStr . '.zip');

        // ZIPファイルオープン
        $zip->open( $outFileName, \ZipArchive::CREATE );

        $unlinkList = array();
        foreach ($fpath_array as $filepath) {
            $filename = basename($filepath);
            $zip->addFromString($filename, file_get_contents($filepath));
            $unlinkList[] = $filepath;
        }

        // ZIPファイルクローズ
        $zip->close();

        // 削除
        // count関数対策
			$unLinkListCount = 0;
			if (!empty($unlinkList)) {
			$unLinkListCount = count($unlinkList);
			}
       for ($i=0; $i<$unLinkListCount; $i++) {
           unlink( $unlinkList[$i] );
       }
	}
}
Application::getInstance()->run();