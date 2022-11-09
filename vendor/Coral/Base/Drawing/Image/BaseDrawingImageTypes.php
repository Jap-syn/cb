<?php
namespace Coral\Base\Drawing\Image;

require_once 'NetB/Drawing/Exception.php';

/**
 * NetB_Drawing_Imageクラスで取り扱い可能な画像種別を示す列挙クラス
 */
final class BaseDrawingImageTypes {
	/**
	 * @private
	 * @static
	 *
	 * すべてのインスタンスを管理する連想配列
	 *
	 * @var array
	 */
	private static $__members;

	/**
	 * @private
	 * @static
	 *
	 * クラスが初期化されているかのフラグ
	 *
	 * @var bool
	 */
	private static $__initialized = false;

	/**
	 * @static
	 *
	 * NetB_Drawing_Image_Typesクラスを初期化する
	 */
	public static function initialize() {
		// すでの初期化済みの場合は処理しない
		if( self::$__initialized ) return;

		// 生成するインスタンスのパラメータ配列
		$pairs = array(
			array( 'key' => 'IMG_GIF', 'value' => IMG_GIF, 'extension' => 'gif' ),
			array( 'key' => 'IMG_JPG', 'value' => IMG_JPG, 'extension' => 'jpg' ),
			array( 'key' => 'IMG_PNG', 'value' => IMG_PNG, 'extension' => 'png' )
		);

		// 必要なインスタンスをすべて生成
		foreach( $pairs as $pair ) {
			new self( $pair['key'], $pair['value'], $pair['extension'] );
		}
		self::$__initialized = true;
	}

	/**
	 * @static
	 *
	 * NetB_Drawing_Image_Typesのすべてのインスタンスを取得する
	 *
	 * @return array メンバとして初期化されているすべてのNetB_Drawing_Image_Typesの配列
	 */
	public static function getMembers() {
		if( ! self::$__initialized ) self::initialize();
		return array_merge( array(), self::$__members );
	}

	/**
	 * @static
	 *
	 * 指定のキーまたはIMG_*値に対応するNetB_Drawing_Image_Typesのインスタンスを取得する
	 *
	 * @param string|int $key 検索に使用するキーまたはIMG_*値
	 * @return NetB_Drawing_Image_Types
	 */
	public static function get($key) {
		foreach( self::getMembers() as $member ) {
			if( strtolower($member->getName()) == strtolower("$key") || $member->getValue() == (int)$key ) {
				return $member;
			}
		}

		return null;
	}

	/**
	 * @private
	 * @static
	 *
	 * 生成されたNetB_Drawing_Image_Typesのインスタンスを管理用の連想配列に追加する。
	 * このメソッドはNetB_Drawing_Image_Typesのコンストラクタからのみ呼び出される
	 *
	 * @param NetB_Drawing_Image_Types $instance 管理下に追加するインスタンス
	 */
	private static function __setInstance(NetB_Drawing_Image_Types $instance) {
		if( ! is_array( self::$__members ) ) self::$__members = array();
		self::$__members[] = $instance;
	}

	/**
	 * @private
	 *
	 * このインスタンスの名前。静的メソッドで問い合わせる際のキーになる。
	 * GDライブラリで定義済みのIMG_*の定数表記に一致する文字列。
	 *
	 * @var string
	 */
	private $_name;

	/**
	 * @private
	 *
	 * このインスタンスの値。IMG_*定数の値に一致する
	 *
	 * @var int
	 */
	private $_value;

	/**
	 * @private
	 *
	 * このインスタンスの拡張子。ピリオドは含まれない
	 *
	 * @var string
	 */
	private $_extension;

	/**
	 * @private
	 *
	 * このインスタンスの値に対応する、IMAGETYPE_*定数の値
	 *
	 * @var int
	 */
	private $_imageType;

	/**
	 * @private
	 *
	 * このインスタンスの画像種別に対応するMIMEタイプ
	 *
	 * @var string
	 */
	private $_mimeType;

	/**
	 * @private
	 *
	 * NetB_Drawing_Image_Typesの新しいインスタンスを初期化する。
	 *
	 * @param string $name インスタンスの名前
	 * @param int $value インスタンスの値
	 * @param string $extension 拡張子
	 */
	private function __construct($name, $value, $extension) {
		$this->_name = $name;

		$this->_value = $value;

		$this->_extension = $extension;
		
		// IMG_*に対応するIMAGETYPE_*をマッピングする
		switch( $this->_value ) {
		case IMG_GIF:
			$this->_imageType = IMAGETYPE_GIF;
			break;
		case IMG_JPG:
			$this->_imageType = IMAGETYPE_JPEG;
			break;
		case IMG_PNG:
			$this->_imageType = IMAGETYPE_PNG;
			break;
		case IMG_WBMP:
			$this->_imageType = IMAGETYPE_WBMP;
			break;
		default:
			// GIF/JPEG/PNG/Windows BMP以外のタイプが指定された場合は例外
			throw new NetB_Drawing_Exception( "'$value' is invalid type." );
		}

		// マッピングしたIMAGETYPE_*値からMIMEタイプを取得
		$this->_mimeType = image_type_to_mime_type( $this->_imageType );

		// クラスメソッドを呼び出してインスタンスを管理下に加える
		self::__setInstance( $this );
	}

	/**
	 * インスタンス名を取得する
	 *
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * インスタンスの値を取得する。この値はGDライブラリのIMG_*定数値に一致する
	 *
	 * @return int
	 */
	public function getValue() {
		return $this->_value;
	}

	/**
	 * インスタンスの画像種別のファイル拡張子を取得する。
	 * このメソッドの戻り値にピリオドは含まれない
	 *
	 * @return string
	 */
	public function getExtension() {
		return $this->_extension;
	}

	/**
	 * インスタンスの画像種別を取得する。
	 * このメソッドの戻り値はgetValue()メソッドと異なり、GDライブラリの
	 * IMAGETYPE_*定数の値を返す
	 *
	 * @return int
	 */
	public function getImageType() {
		return $this->_imageType;
	}

	/**
	 * このインスタンスの画像種別のMIMEタイプを取得する
	 *
	 * @return string
	 */
	public function getMimeType() {
		return $this->_mimeType;
	}

	/**
	 * __toStringをオーバーライド
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->getName() . " => '" . $this->getMimeType() . "'";
	}
}

// includeまたはrequireされた時点でNetB_Drawing_Image_Typesクラスを初期化する
NetB_Drawing_Image_Types::initialize();

