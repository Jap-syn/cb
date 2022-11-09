<?php
namespace Coral\Base\Extra\FFmpeg\Information;

require_once 'NetB/Extra/FFmpeg/Information/Interface.php';

/**
 * @class
 *
 * 出力ファイル情報を解析するNetB_Extra_FFmpeg_Information_Interface実装クラス
 */
class BaseExtraFFmpegInformationOutput implements BaseExtraFFmpegInformationInterface {
	/**
	 * @static
	 * @protected
	 *
	 * 出力ファイル情報の開始行に一致するPerl互換正規表現のパターン
	 *
	 * @var string
	 */
	protected static $__firstLine = '/^Output #(\d+), ([^,]+), to ([^:]+):$/';

	/**
	 * @static
	 * @protected
	 *
	 * 出力ファイル情報の各行を解析する正規表現パターンの配列
	 *
	 * @var array
	 */
	protected static $__expressions;

	/**
	 * @static
	 *
	 * 指定の行文字列がファイル入力情報の開始行に一致するかを判断する
	 *
	 * @param string $line 検査する行文字列
	 * @return bool $lineが出力ファイル情報の開始行の場合はtrue、それ以外はfalse
	 */
	public static function canHandle($line) {
		return preg_match( self::$__firstLine, $line ) ? true : false;
	}

	/**
	 * @static
	 * @protected
	 *
	 * 出力ファイル情報の各行を解析する正規表現の配列を取得する
	 *
	 * @return array
	 */
	protected static function _getExpressions() {
		if( self::$__expressions == null ) {
			self::$__expressions = array(
				'stream' => '/    Stream #([^:]+): ([^:]+): (.+)$/'
			);
		}
		return self::$__expressions;
	}

	/**
	 * @protected
	 *
	 * 入力ファイルのメディアフォーマット
	 *
	 * @var string
	 */
	protected $_format;

	/**
	 * @protected
	 *
	 * 入力ファイルのパス
	 *
	 * @var string
	 */
	protected $_path;

	/**
	 * @protected
	 *
	 * ファイルに含まれる各ストリームの情報を保持する連想配列。
	 * キーがストリームの種別、各項目がそのストリームの情報を示す連想配列になる。
	 *
	 * @var array
	 */
	protected $_streams = array();

	/**
	 * @constructor
	 *
	 * 出力ファイル情報開始行を指定して、NetB_Extra_FFmpeg_Information_Outputの
	 * 新しいインスタンスを初期化する
	 *
	 * @param string $line 入力ファイル情報開始行。canHandle()静的メソッドがtrueを返した文字列のみ処理可能
	 */
	public function __construct($line) {
		if( ! self::canHandle( $line ) ) {
			$msg = "line '" . ( substr( $line, 0, 30 ) ) . "' is unknown format.";
			throw new NetB_Extra_FFmpeg_Exception( $msg );
		}

		preg_match( self::$__firstLine, $line, $matches );

		$this->_format = $matches[2];
		$this->_path = str_replace( "'", '', "$matches[3]" );
	}

	/**
	 * 指定の行文字列を入力ファイル情報として解析を試みる。
	 * $lineが入力ファイル情報に含まれる要素の場合、このインスタンスの
	 * 内部情報が更新されtrueを返すが、処理できない文字列の場合は
	 * falseを返し、このインスタンスの内容はなにも変化しない。
	 *
	 * @param string $line 解析する文字列
	 * @return bool $lineが入力ファイル情報として解析できた場合はtrue、それ以外はfalse
	 */
	public function parseLine($line) {
		foreach( self::_getExpressions() as $key => $exp ) {
			$hit = preg_match( $exp, $line, $matches );
			if( ! $hit ) continue;

			// ヒットしたパース条件ごとに処理を分岐
			switch( $key ) {
			case 'stream':
				// ストリーム情報
				$kind = strtolower( $matches[2] );
				$map = null;
				$props = split( ', ', $matches[3] );
				
				// 解析したストリームのプロパティ情報と位置が一致するプロパティ名を
				// ストリームタイプごとに構築
				switch( $kind ) {
				case 'video':
					$map = split( '/', 'format/pixel format/size/quality/bitrate/frame rate' );
					break;
				case 'audio':
					$map = split( '/', 'encoder/sampling rate/channels/bitrate' );
					break;
				}
				if( $map ) {
					$info = array();
					foreach( split( ', ', $matches[3] ) as $i => $prop ) {
						$name = $map[$i];
						switch( $name ) {
						case 'size':
							// ビデオサイズの場合はアスペクト比などの情報が[]で囲まれてるので
							// 切り落とす
							$info[$name] = preg_replace( '/ \[[^\]]*\]$/', '', "$prop" );
							// 可能ならサイズ情報は幅と高さの数値に分離
							if( preg_match( '/^(\d+)x(\d+)$/', $info[$name], $size_info ) ) {
								$info[$name] = array( 'width' => (int)$size_info[1], 'height' => (int)$size_info[2] );
							}
							break;
						case 'frame rate':
							// フレームレートも末尾に不要な文字列があるので切り落とす
							$info[$name] = preg_replace( '/ t[bc]\(.\)$/', '', "$prop" );
							break;
						default:
							$info[$name] = "$prop";
							break;
						}
					}
					$this->_streams[$kind] = $info;
				}
				break;
			}
			return true;
		}
		return false;
	}

	/**
	 * 出力ファイルのフォーマットを取得する。
	 * これはffmpegが出力エンコードに用いたライブラリの名前を示す場合がある。
	 *
	 * @return string
	 */
	public function getFormat() {
		return $this->_format;
	}

	/**
	 * 出力ファイルのパスを取得する。
	 *
	 * @return string
	 */
	public function getFilePath() {
		return $this->_path;
	}

	/**
	 * メディアストリームの情報を連想配列で取得する。
	 * 戻り値はそれぞれ動画ストリームを示す'video'、音声ストリームを示す'audio'を
	 * キーとし、対応する値は各ストリームのプロパティ（ビットレート・サイズなど）を
	 * 保持する連想配列になる。
	 *
	 * @return array
	 */
	public function getStreamInfo() {
		return $this->_streams;
	}

	/**
	 * このインスタンスの解析内容を連想配列で取得する
	 *
	 * @return array
	 */
	public function toArray() {
		return array(
			'format' => $this->_format,
			'file path' => $this->_path,
			'stream info' => $this->_streams
		);
	}
}

