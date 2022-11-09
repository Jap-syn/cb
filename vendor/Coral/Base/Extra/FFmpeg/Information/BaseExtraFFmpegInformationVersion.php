<?php
namespace Coral\Base\Extra\FFmpeg\Information;

require_once 'NetB/Extra/FFmpeg/Information/Interface.php';

/**
 * @class
 *
 * バージョン情報を解析するNetB_Extra_FFmpeg_Information_Interface実装クラス
 */
class BaseExtraFFmpegInformationVersion implements BaseExtraFFmpegInformationInterface {
	/**
	 * @static
	 * @protected
	 *
	 * バージョン情報の開始行に一致するPerl互換正規表現のパターン
	 *
	 * @var string
	 */
	protected static $__firstLine = '/^FFmpeg version ([^,]+), (.+)$/';

	/**
	 * @static
	 * @protected
	 *
	 * バージョン情報の各行を解析する正規表現パターンの配列
	 *
	 * @var array
	 */
	protected static $__expressions;

	/**
	 * @static
	 *
	 * 指定の行文字列がバージョン情報開始行に一致するかを判断する
	 *
	 * @param string $line 検査する行文字列
	 * @return bool $lineがバージョン情報の開始行の場合はtrue、それ以外はfalse
	 */
	public static function canHandle($line) {
		return preg_match( self::$__firstLine, $line ) ? true : false;
	}

	/**
	 * @static
	 * @protected
	 *
	 * バージョン情報の各行を解析する正規表現の配列を取得する
	 *
	 * @return array
	 */
	protected static function _getExpressions() {
		if( self::$__expressions == null ) {
			self::$__expressions = array(
				'config' => '/  configuration: (.+)$/',
				'library' => '/  ([^\s]+) version: (.+)$/',
				'build_info' => '/  built on ([^,]+), ([^:]+: .+)$/'
			);
		}
		return self::$__expressions;
	}

	/**
	 * @protected
	 *
	 * FFmpegのバージョン文字列
	 *
	 * @var string
	 */
	protected $_version;

	/**
	 * @protected
	 *
	 * FFmpegの著作権情報文字列
	 *
	 * @var string
	 */
	protected $_copyright;

	/**
	 * @protected
	 *
	 * 主要ライブラリの情報を格納する配列。
	 * 各要素はキーにライブラリ名称、値にバージョン文字列が格納される
	 *
	 * @var array
	 */
	protected $_libraries = array();

	/**
	 * @protected
	 *
	 * FFmpegのconfigure情報を格納する配列
	 *
	 * @var array
	 */
	protected $_configuration;

	/**
	 * @protected
	 *
	 * FFmpegのビルド情報を格納する連想配列。
	 * ビルド日付を示す'build date'と使用されたコンパイラ情報を示す'compiler'のキーを持つ。
	 *
	 * @var array
	 */
	protected $_build_info;

	/**
	 * @constructor
	 *
	 * バージョン情報開始行を指定してNetB_Extra_FFmpeg_Information_Versionの
	 * 新しいインスタンスを初期化する
	 *
	 * @param string $line バージョン情報開始行。canHandle()静的メソッドがtrueを返した文字列のみ処理可能
	 */
	public function __construct($line) {
		if( ! self::canHandle( $line ) ) {
			$msg = "line '" . ( substr( $line, 0, 30 ) ) . "' is unknown format.";
			throw new NetB_Extra_FFmpeg_Exception( $msg );
		}

		preg_match( self::$__firstLine, $line, $matches );
		
		$this->_version = $matches[1];
		$this->_copyright = $matches[2];
	}

	/**
	 * 指定の行文字列をバージョン情報として解析を試みる。
	 * $lineがバージョン情報に含まれる要素の場合、このインスタンスの
	 * 内部情報が更新されtrueを返すが、処理できない文字列の場合は
	 * falseを返し、このインスタンスの内容はなにも変化しない。
	 *
	 * @param string $line 解析する行文字列
	 * @return bool $lineがバージョン情報として解析できた場合はtrue、それ以外はfalse
	 */
	public function parseLine($line) {
		foreach( self::_getExpressions() as $key => $exp ) {
			$hit = preg_match( $exp, $line, $matches );
			if( ! $hit ) continue;

			// ヒットしたパース条件ごとに処理を分岐
			switch( $key ) {
			case 'config':
				// configure情報
				$this->_configuration = split( ' ', $matches[1] );
				break;
			case 'library':
				// ライブラリ情報
				$this->_libraries[ $matches[1] ] = $matches[2];
				break;
			case 'build_info':
				// ビルド情報
				$this->_build_info = array(
					'build date' => $matches[1],
					'compiler' => $matches[2]
				);
				break;
			}
			// 行を処理できたのでtrueで終了
			return true;
		}
		// 一致するパターンがないのでfalseで終了
		return false;
	}

	/**
	 * FFmpegのバージョン文字列を取得する
	 *
	 * @return string
	 */
	public function getVersion() {
		return $this->_version;
	}

	/**
	 * FFmpegの著作権文字列を取得する
	 *
	 * @return string
	 */
	public function getCopyright() {
		return $this->_copyright;
	}

	/**
	 * configure情報を取得する
	 *
	 * @return array
	 */
	public function getConfigOptions() {
		return $this->_configuration;
	}

	/**
	 * 主要ライブラリのバージョン情報を取得する。
	 * 戻り値の配列はキーがライブラリ名、値がそのライブラリのバージョンになる。
	 *
	 * @return array
	 */
	public function getLibraryVersion() {
		return $this->_libraries;
	}

	/**
	 * ビルド情報を取得する。
	 * 戻り値は連想配列で、ビルド日付を示す'build date'と
	 * ビルドに使用されたコンパイラ情報を示す'compiler'をキーに持つ。
	 *
	 * @return array
	 */
	public function getBuildInfo() {
		return $this->_build_info;
	}

	/**
	 * このインスタンスの解析内容を連想配列で取得する
	 *
	 * @return array
	 */
	public function toArray() {
		return array(
			'version' => $this->_version,
			'copyright' => $this->_copyright,
			'library version' => $this->_libraries,
			'configuration options' => $this->_configuration,
			'build info' => $this->_build_info
		);
	}
}

