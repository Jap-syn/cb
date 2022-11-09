<?php
namespace Coral\Base;

use Zend\Config\Config;
use Zend\Log\Logger;
use Zend\Log\Writer\SharedNull as ZendNull;
use Zend\Log\Writer\Stream;
use Zend\Log\Writer\Db;
use Zend\Log\Writer\AbstractWriter;
use Zend\Log\Filter\Priority;
use Zend\Db\Adapter\Adapter;
use Zend\XmlRpc\Value\SharedString;

/**
 * @class
 *
 * 簡単なコンフィグレーションでテキストやSQLiteDB、コンソール向けの
 * LogWriterを初期化できる、Zend\log\Logger派生クラス
 */
class BaseLog extends Logger {
	/**
	 * @static
	 *
	 * デフォルトのログ名を取得する
	 *
	 * @return string
	 */
	public static function getDefaultLogName() {
		return '%dir%_%timestamp:Ymd%';
	}

	/**
	 * @static
	 *
	 * BaseLogの新しいインスタンスを作成する。
	 * この静的メソッドはコンストラクタのエイリアスとして動作するファクトリメソッドである。
	 *
	 * @param AbstractWriter|null $writer 初期追加するログライター
	 * @param string|null $logDir ログディレクトリのパス
	 * @param string|null $logNmae ログファイルのベース名
	 */
   public static function create($writer = null, $logDir = null, $logName = null) {
       return new self( $writer, $logDir, $logName );
	}

// ----------------------------------------------未使用のため削除-----------------------------------------------
// 	/**
// 	 * @static
// 	 *
// 	 * 初期化用のZend_Configインスタンスを指定して、NetB_Logの新しいインスタンスを作成する。
// 	 * このメソッドは以下のコードと等価である。
// 	 *
// 	 * $log = new NetB_Log();
// 	 * $log->initFromConfig( $config );
// 	 *
// 	 * @param Zend_Config $config 初期化用のZend_Config
// 	 * @return NetB_Log
// 	 */
// 	public static function createFromConfig(Zend_Config $config) {
// 		return self::create()->initFromConfig( $config );
// 	}
// -------------------------------------------------------------------------------------------------------------

	/**
	 * @static
	 *
	 * 初期化用の連想配列を指定して、BaseLogの新しいインスタンスを作成する。
	 * このメソッドは以下のコードと等価である。
	 *
	 * $log = new BaseLog();
	 * $log->initFromArray( $config );
	 *
	 * @param array $config 初期化用の連想配列
	 * @return BaseLog
	 */
	public static function createFromArray(array $config = array()) {
		return self::create()->initFromArray( $config );
	}

	/**
	 * @access protected
	 *
	 * デフォルトのテキストファイル用LogWriter
	 *
	 * @var Zend\Log\Writer\Stream
	 */
	protected $_textWriter = null;

	/**
	 * @access protected
	 *
	 * デフォルトのデータベースLogWriter。SQLiteを使用する。
	 *
	 * @var Zend\Log\Writer\Db
	 */
	protected $_sqliteWriter = null;

	/**
	 * @access protected
	 *
	 * デフォルトのコンソール出力用LogWriter
	 *
	 * @var Zend\Log\Writer\Stream
	 */
	protected $_consoleWriter = null;

	/**
	 * @access protected
	 *
	 * ログディレクトリのパス
	 *
	 * @var string
	 */
	protected $_dir = '.';

	/**
	 * @access protected
	 *
	 * ログファイルのベース名
	 *
	 * @var string
	 */
	protected $_logName;

	/**
	 * @access protected
	 *
	 * 内部エラーを記録するエラーログ
	 *
	 * @var Logger
	 */
	protected $_internalErrorLog;

	/**
	 * @access protected
	 *
	 * 内部エラーを記録するテキストログのパス
	 *
	 * @var string
	 */
	protected $_internalErrorPath = '';

	/**
	 * @access protected
	 *
	 * プライオリティによるメッセージフィルタの閾値となるプライオリティ値
	 *
	 * @var int
	 */
	protected $_priorityThreshold = Logger::ERR;

	/**
	 * @access private
	 *
	 * インスタンスが生成されてからログ出力を行ったかのフラグ
	 *
	 * @var bool
	 */
	private $_log_writed = false;

	/**
	 * BaseLogクラスの新しいインスタンスを初期化する
	 *
	 * @param AbstractWriter|null $writer 初期追加するログライター
	 * @param string|null $logDir ログディレクトリのパス
	 * @param string|null $logNmae ログファイルのベース名
	 */
	public function __construct($writer = null, $logDir = null, $logName = null) {
	    // 親クラスのコンストラクタ実行
		parent::__construct( $writer );

		// デフォルトでNullライターを必ず追加しておく
		if( $writer == null || ! ( $writer instanceof ZendNull ) ) {
			$this->addWriter( new ZendNull() );
		}

		$logDir = trim( "$logDir" );
		if( empty($logDir) ) $logDir = '.';

		$logName = trim( "$logName" );
		if( empty($logName) ) $logName = self::getDefaultLogName();

		$this
			->setLogDir( $logDir )
			->setLogName( $logName );
	}

	/**
	 * BaseLogクラスのインスタンスを廃棄する
	 */
	public function __destruct() {
		parent::__destruct();
	}

	/**
	 * Zend\Log\Logger::log($priority, $message, $extra = array())メソッドのオーバーライド
	 *
	 * @param integer $priority メッセージプライオリティ
	 * @param string $message ログメッセージ
	 */
	public function log($priority, $message, $extra = array()) {

		try {
		    parent::log( $priority, $message );
		}
		catch(\Exception $err) {

		    try {
				// エラーログ作成
				if( $this->_internalErrorLog == null && ! empty( $this->_internalErrorPath ) ) {

				    $this->_internalErrorLog = new Logger();
				    $writer = new Stream($this->_internalErrorPath);
				    $this->_internalErrorLog->addWriter($writer);
				}
				if (!is_null($this->_internalErrorLog)) {
			        $this->_internalErrorLog->err( $err->getMessage() );
				}
            }
			catch(\Exception $err2) {
				// 内部記録のエラーは無視
			}
		}
	}

	/**
	 * 初期化用の連想配列からBaseLogのインスタンスを初期設定する。
	 * 連想配列は以下のキーを指定できる
	 * - 'log_name' .......... ログファイルのベース名。setLogName()を呼び出す。
	 * - 'log_dir' ........... ログファイルの保存ディレクトリ。setLogDir()を呼び出す。
	 * - 'error_log' ......... 内部エラーログの保存先パス。setInternalErrorLogPath()を呼び出す。
	 * - 'use_text_log' ...... trueが指定された場合、標準のテキストログを使用する。enableTextLog()を呼び出す。
	 * - 'use_sqlite_log' .... trueが指定された場合、標準のSQLiteログを使用する。enableSqliteLog()を呼び出す。
	 * - 'use_console_log' ... trueが指定された場合、標準の出力バッファログを使用する。enableConsoleLog()を呼び出す。
	 * - 'priority_threshold'. フィルタに適用されるプライオリティの閾値。setPriorityThreshold()を呼び出す。
	 *
	 * @param array $config 初期化用連想配列
	 * @return BaseLog
	 */
	public function initFromArray(array $config = array()) {
	    if( ! is_array( $config ) ) $config = array();
		foreach( $config as $key => $value ) {
			switch( $key ) {
			case 'log_name':
				$this->setLogName( $value );
				break;
			case 'log_dir':
				$this->setLogDir( $value );
				break;
			case 'error_log':
				$this->setInternalErrorLogPath( $value );
				break;
			case 'use_text_log':
				if( $value ) $this->enableTextLog();
				break;
			case 'use_sqlite_log':
				if( $value ) $this->enableSqliteLog();
				break;
			case 'use_console_log':
				if( $value ) $this->enableConsoleLog();
				break;
			case 'priority_threshold':
				$this->setPriorityThreshold( $value );
				break;
			}
		}
		return $this;
	}

	/**
	 * 初期化用のZend\Config\ConfigインスタンスからBaseLogを初期化する。
	 * 初期化用のZend\Config\Configは以下のプロパティを使用できる
	 *
	 * - 'log_name' .......... ログファイルのベース名。setLogName()を呼び出す。
	 * - 'log_dir' ........... ログファイルの保存ディレクトリ。setLogDir()を呼び出す。
	 * - 'error_log' ......... 内部エラーログの保存先パス。setInternalErrorLogPath()を呼び出す。
	 * - 'use_text_log' ...... trueが指定された場合、標準のテキストログを使用する。enableTextLog()を呼び出す。
	 * - 'use_sqlite_log' .... trueが指定された場合、標準のSQLiteログを使用する。enableSqliteLog()を呼び出す。
	 * - 'use_console_log' ... trueが指定された場合、標準の出力バッファログを使用する。enableConsoleLog()を呼び出す。
	 * - 'priority_threshold'. フィルタに適用されるプライオリティの閾値。setPriorityThreshold()を呼び出す。
	 *
	 * @param Zend\Config\Config $config 初期化用インスタンス
	 * @return BaseLog
	 */
	public function initFromConfig(Config $config) {
	    $this->initFromArray( $config->toArray() );
		return $this;
	}

	/**
	 * ログ出力を制御するプライオリティフィルタを取得する
	 *
	 * @return int ログ出力を制御するプライオリティ値
	 */
	public function getPriorityThreshold() {
		return $this->_priorityThreshold;
	}

	/**
	 * ログ出力を制御するプライオリティフィルタにプライオリティ値を設定する。
	 * 例えば、このメソッドでLogger::ERRを設定した場合、debug()メソッドによる
	 * メッセージはフィルタリングによりブロックされる。
	 *
	 * このメソッドでプライオリティを変更できるのは、最初のログメッセージを出力する前までで、
	 * なにか1つでもログメッセージを出力した後にこのメソッドを実行しても
	 * プライオリティの閾値は変更されない。
	 *
	 * @param in $priority メッセージフィルタの閾値となるプライオリティ値
	 * @return BaseLog
	 */
	public function setPriorityThreshold($priority = Logger::ERR) {
		if( ! $this->_log_writed ) {
			$is_nonNegInt = preg_match( '/^\+?([0-9]|([1-9]\d+))$/', "$priority" );
			if( ! $is_nonNegInt ) $priority = Logger::ERR;
			$this->_priorityThreshold = (int)$priority;
		}
		return $this;
	}

	/**
	 * ログファイルのベース名を取得する
	 *
	 * @return string
	 */
	public function getLogName() {
		return $this->_logName;
	}

	/**
	 * ログファイルのベース名を指定する。
	 * ログファイル名には書式指定変数を埋め込むことができる。
	 * 書式指定変数は、
	 *  - %dir% ........ ログファイルの保存先ディレクトリ名
	 *  - %timestam% ... タイムスタンプ
	 * を指定することができ、%timestamp%は、「%timestamp:Ymd%」のように、
	 * 日付書式指定をコロン（:）以降に指定することもできる。
	 *
	 * @param string $logName ログファイルのベース名
	 * @return BaseLog
	 */
	public function setLogName($logName) {
		if( $this->_textWriter == null || $this->_sqliteLogWriter == null ) {
			$logName = trim( "$logName" );
			if( $logName ) $this->_logName = $logName;
		}
		return $this;
	}

	/**
	 * ログファイルを保存するディレクトリパスを取得する
	 *
	 * @return string
	 */
	public function getLogDir() {
		return $this->_dir;
	}

	/**
	 * ログファイルを保存するディレクトリパスを設定する
	 *
	 * @param string $dir ディレクトリパス
	 * @return BaseLog
	 */
	public function setLogDir($dir) {
		$dir = trim( "$dir" );
		if( file_exists( realpath( $dir ) ) ) $this->_dir = $dir;
		return $this;
	}

	/**
	 * 内部エラーを記録するログのパスを取得する
	 *
	 * @return string
	 */
	public function getInternalErrorLogPath() {
		return $this->_internalErrorPath;
	}

	/**
	 * 内部エラーを記録するログのパスを設定する。
	 * $pathにnullまたは空の文字列を指定した場合内部エラーログは作成されない
	 *
	 * @param string|null $path 内部エラーを記録するテキストログのパス
	 * @return BaseLog
	 */
	public function setInternalErrorLogPath($path) {
		$this->_internalErrorPath = trim( "$path" );
		return $this;
	}

	/**
	 * テキスト出力する組み込みログライターを有効にする。
	 * すでにテキストログライターが有効になっている場合はなにも処理されない
	 *
	 * @return BaseLog
	 */
	public function enableTextLog() {
		if( $this->_textWriter == null ) {
			$this->_textWriter = new Stream( $this->_getLogPath( '.txt' ) );
			$this->addWriter( $this->_textWriter );
		}
		return $this;
	}

	/**
	 * SQLiteデータベースへ出力する組み込みログライターを有効にする
	 * すでにSQLiteログライターが有効になっている場合はなにも処理されない
	 *
	 * @return BaseLog
	 */
	public function enableSqliteLog() {
	    if( $this->_sqliteLogWriter == null ) {
	        $path = $this->_getLogPath( '.sqlite' );
	        if( ! file_exists( $path ) ) $this->_createSqliteDb( $path );

	        $array_con = array();
	        $array_con['driver'] = 'Sqlite';
	        $array_con['database'] =$path;
            $adapter = new Adapter($array_con);

	        $map = array(
	                'priority' => 'priority',
	                'priority_name' => 'priorityName',
	                'message' => 'message',
	                'timestamp' => 'timestamp'
	        );
	        $this->_sqliteLogWriter = new Db( $adapter, 'logs', $map );
	        $this->addWriter( $this->_sqliteLogWriter );
	    }
	    return $this;
	}

	/**
	 * 出力バッファへ出力する組み込みログライターを有効にする
	 * すでにバッファログライターが有効になっている場合は何も処理されない
	 *
	 * @return BaseLog
	 */
	public function enableConsoleLog() {
	    if( $this->_consoleWriter == null ) {
			$this->_consoleWriter = new Stream( 'php://output' );
			$this->addWriter( $this->_consoleWriter );
		}
		return $this;
	}

	/**
	 * 現在のログファイルベース名を元に書式指定変数を展開した
	 * 名前を返す
	 *
	 * @return string
	 */
	public function parseLogName() {
		$s = $this->getLogName();

		// $dirのパース
		$pattern = '%dir%';
		if( mb_ereg_match( $pattern, $s ) ) {
			$s = mb_ereg_replace( $pattern, basename(realpath( $this->getLogDir() )), $s );
		}

		// $timestampのパース
		$pattern = '%timestamp(:.+)?%';
		if( mb_ereg( $pattern, $s, $captures ) ) {
			$replacement = $captures[1] ? substr( $captures[1], 1 ) : 'Ymd';
			$s = mb_ereg_replace( $pattern, date( $replacement ), $s );
		}

		return $s;
	}

	/**
	 * 現在のログディレクトリとログファイル名の設定からログのパスを作成する。
	 *
	 * @param string|null $ext ログファイルに設定する拡張子。省略時は'.txt'が設定される
	 * @return string $extの拡張子が設定されたログファイル用のパス文字列
	 */
	protected function _getLogPath($ext = null) {
		$ext = trim( "$ext" );
		if( strlen( $ext ) == 0 ) $ext = '.txt';
		if( substr( $ext, 0, 1 ) != '.' ) $ext = ".$ext";

		return
			realpath( $this->getLogDir() ) .
			DIRECTORY_SEPARATOR .
			$this->parseLogName() .
			$ext;
	}

	/**
	 * SQLiteログライター向けのSQLiteデータベースを初期化する
	 *
	 * @param string $path ログデータベースのパス
	 */
	protected function _createSqliteDb($path) {

	    $array_con = array();
	    $array_con['driver'] = 'Sqlite';
	    $array_con['database'] =$path;
	    $adapter = new Adapter($array_con);

	    $queries = array(
            // create table
            (
	            'CREATE TABLE logs ( ' .
	            'id INTEGER PRIMARY KEY, ' .
	            'priority INTEGER, ' .
	            'priority_name VARCHAR(15), ' .
	            'message TEXT, '.
	            'timestamp DATETIME ' .
	            ')'
            ),
            // create indices
            'CREATE INDEX idx_logs_01 ON logs ( priority )',
            'CREATE INDEX idx_logs_02 ON logs ( message )',
            'CREATE INDEX idx_logs_03 ON logs ( timestamp )'
	    );

	    foreach( $queries as $query ) $adapter->query($query)->execute(null);
	}
}

