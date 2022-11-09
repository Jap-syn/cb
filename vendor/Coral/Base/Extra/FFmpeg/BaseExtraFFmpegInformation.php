<?php
namespace Coral\Base\Extra\FFmpeg;

require_once 'NetB/Delegate.php';
require_once 'NetB/Extra/FFmpeg/Information/Interface.php';
require_once 'NetB/Extra/FFmpeg/Information/Version.php';
require_once 'NetB/Extra/FFmpeg/Information/Input.php';
require_once 'NetB/Extra/FFmpeg/Information/Output.php';

/**
 * @class
 *
 * FFmpegのコマンド実行結果を解析し各種情報を取得するクラス。
 *
 */
class BaseExtraFFmpegInformation {
	/**
	 * @const
	 *
	 * このクラスが属する名前空間プレフィックス
	 *
	 * @var string
	 */
	const NAMESPACE = 'NetB_Extra_FFmpeg_Information';

	/**
	 * @const
	 *
	 * カテゴリがバージョン情報であることを示す定数
	 *
	 * @var string
	 */
	const CATEGORY_VERSION = 'Version';

	/**
	 * @const
	 *
	 * カテゴリが入力ファイル情報であることを示す定数
	 *
	 * @var string
	 */
	const CATEGORY_INPUT = 'Input';

	/**
	 * @const
	 *
	 * カテゴリが出力ファイル情報であることを示す定数
	 *
	 * @var string
	 */
	const CATEGORY_OUTPUT = 'Output';

	/**
	 * @static
	 * @protected
	 *
	 * パーサとなるNetB_Extra_FFmpeg_Information_Interface実装クラスの
	 * canHandleスタティックメソッドを呼び出すためのデリゲートコレクション
	 *
	 * @var array
	 */
	protected static $__parsers = null;

	/**
	 * @protected
	 *
	 * 解析結果を格納する連想配列
	 *
	 * @var array
	 */
	protected $_results = array();

	/**
	 * @protected
	 *
	 * FFmpegの実行メッセージ
	 *
	 * @string
	 */
	protected $_raw_message;

	/**
	 * @protected
	 *
	 * 現在のコンテキストを示すNetB_Extra_FFmpeg_Information_Interface実装インスタンス
	 *
	 * @var NetB_Extra_FFmpeg_Information
	 */
	protected $_context = null;

	/**
	 * @constructor
	 *
	 * FFmpegの実行結果出力を指定して、NetB_Extra_FFmpeg_Informationの新しいインスタンスを初期化する
	 *
	 * @param string|array FFmpegの実行結果出力
	 */
	public function __construct($lines) {
		if( empty( self::$__parsers ) ) {
			$ns = self::NAMESPACE;
			self::$__parsers = array();

			$categories = array(
				self::CATEGORY_VERSION,
				self::CATEGORY_INPUT,
				self::CATEGORY_OUTPUT
			);

			// カテゴリ判別を行うためのデリゲートリストを構築
			foreach( $categories as $className ) {
				self::$__parsers[ $className ] =
					new NetB_Delegate( "{$ns}_{$className}", "canHandle" );
			}
		}
		// 解析処理開始
		$this->_parseLines( $lines );
	}

	/**
	 * 解析結果にバージョン情報が含まれているかを判断する。
	 * このメソッドがfalseを返す場合、解析対象のログはコマンドに失敗している。
	 *
	 * @return bool 解析結果にバージョン情報が含まれている場合はtrue、それ以外はfalse
	 */
	public function hasVersionInfo() {
		return $this->getVersionInfo() ? true : false;
	}
	/**
	 * バージョン情報を取得する
	 *
	 * @return NetB_Extra_FFmpeg_Information_Version
	 */
	public function getVersionInfo() {
		return $this->_results[ self::CATEGORY_VERSION ];
	}

	/**
	 * 解析結果に入力ファイル情報が含まれているかを判断する。
	 *
	 * @return bool 解析結果に入力ファイル情報が含まれている場合はtrue、それ以外はfalse
	 */
	public function hasInputInfo() {
		return $this->getInputInfo() ? true : false;
	}
	/**
	 * 入力ファイル情報を取得する
	 *
	 * @return NetB_Extra_FFmpeg_Information_Input
	 */
	public function getInputInfo() {
		return $this->_results[ self::CATEGORY_INPUT ];
	}

	/**
	 * 解析結果に出力ファイル情報が含まれているかを判断する。
	 *
	 * @return bool 解析結果に出力ファイル情報が含まれている場合はtrue、それ以外はfalse
	 */
	public function hasOutputInfo() {
		return count( $this->getOutputInfo() ) > 0;
	}
	/**
	 * 出力ファイル情報を取得する。
	 * このメソッドの戻り値は配列であり、各要素が出力ファイル情報を示す
	 * NetB_Extra_FFmpeg_Information_Outputのインスタンスとなる。
	 *
	 * @return array 出力ファイル情報の配列。出力ファイル情報が含まれない場合は長さ0の配列になる。
	 */
	public function getOutputInfo() {
		$result = $this->_results[ self::CATEGORY_OUTPUT ];
		if( $result === null ) $result = array();
		return $result;
	}

	/**
	 * @protected
	 *
	 * FFmpeg実行結果出力を解析し、各種情報を連想配列として構築する
	 *
	 * @param string|array $lines FFmpegの実行結果出力。出力テキストをそのまま渡すか、行単位で区切った配列を指定する。
	 */
	protected function _parseLines($lines) {
		// 内部処理は行単位で行うので分割する
		if( ! is_array( $lines ) ) {
			$lines = preg_split( '/((\r\n)|\r|\n)/', "$lines" );
		}
		// $_raw_messageは出力テキストを保持する
		$this->_raw_message = join( "\n", $lines );

		// 解析コンテキストの初期化
		$this->_context = null;

		// コンテキストを確定するためのデリゲートリストをクラス変数から取得
		$parsers = self::$__parsers;
		// 行単位で処理
		foreach( $lines as $line ) {
			$newContext = null;
			foreach( $parsers as $name => $method ) {
				if( $method->invoke( $line ) ) {
					// 新しいコンテキスト確定
					$newContext = $name;
					break;
				}
			}

			// コンテキストの変更があった
			if( $newContext ) {
				$ns = self::NAMESPACE;
				// コンテキストとなるNetB_Extra_FFmpeg_Information_Interface実装クラスを初期化
				$this->_context =
					NetB_Reflection_Utility::createClass( "{$ns}_{$newContext}", array($line) );

				if( $newContext == 'Output' ) {
					// 'Output'コンテキストのみ複数回出現する
					$this->_results[ $newContext ][] = $this->_context;
				} else {
					$this->_results[ $newContext ] = $this->_context;
				}
				// コンテキスト変更の開始行はコンテキスト初期化時に完了しているので次の行へ
				continue;
			}
			// 有効なコンテキストに行を処理させる
			if( $this->_context ) $this->_context->parseLine( $line );
		}
	}

	/**
	 * 解析結果をネストした連想配列として取得する。
	 * このメソッドは解析内容をダンプするのに利用できる。
	 *
	 * @return array
	 */
	public function toArray() {
		$result = array();
		foreach( $this->_results as $key => $info ) {
			switch( $key ) {
			case 'Output':
				$sub_info = array();
				foreach( $info as $item ) $sub_info[] = $item->toArray();
				$result[$key] = $sub_info;
				break;
			default:
				$result[$key] = $info->toArray();
				break;
			}
		}
		return $result;
	}

}

