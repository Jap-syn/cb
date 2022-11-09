<?php
namespace Coral\Base\Drawing;

require_once 'NetB/Delegate.php';
require_once 'NetB/Drawing/Image/Types.php';

/**
 * 画像イメージクラス。GD関数群のラッパーとして機能し、1つの「イメージ」を1つのインスタンスで完結させる。
 */
final class BaseDrawingImage {
	/**
	 * @private
	 * @static
	 *
	 * デフォルトのイメージタイプ
	 *
	 * @var NetB_Drawing_Image_Types
	 */
	private static $__defaultType;

	/**
	 * @private
	 * @static
	 *
	 * サムネイルのデフォルトイメージタイプ
	 *
	 * @var NetB_Drawing_Image_Types
	 */
	private static $__defaultThumbType;

	/**
	 * @private
	 * @static
	 * イメージタイプと読み込み/保存用の関数を関連付けて管理する配列。
	 * 各要素はキー'type'にNetB_Drawing_Image_Types、キー'load'/'save'にNetB_Delegateが格納された
	 * 連想配列になる
	 *
	 * @var array
	 */
	private static $__handlers;

	/**
	 * @private
	 * @static
	 * 
	 * イメージを設定済みのインスタンスに対して_setImageSize()メソッドを実行した
	 * 場合にエラーを無視するかのフラグ。
	 * setIgnoreResizeError()スタティックメソッドで設定した値が各インスタンスに
	 * 利用される
	 *
	 * @var bool
	 */
	private static $__ignore_resize_error = false;

	/**
	 * @private
	 * @static
	 *
	 * GDライブラリが有効であるかを検査する。GDが無効な環境下でこの
	 * メソッドを実行すると例外がスローされる
	 */
	private static function __checkGD() {
		if( ! function_exists( 'gd_info' ) ) {
			throw new NetB_Drawing_Exception( 'GD library not supported.' );
		}
	}

	/**
	 * @static
	 *
	 * 新規インスタンス作成時のデフォルトのイメージタイプを取得する
	 *
	 * @return NetB_Drawing_Image_Types
	 */
	public static function getDefaultType() {
		if( ! ( self::$__defaultType instanceof NetB_Drawing_Image_Types ) ) {
			self::$__defaultType = NetB_Drawing_Image_Types::get( IMG_JPG );
		}
		return self::$__defaultType;
	}

	/**
	 * @static
	 *
	 * 新規インスタンス作成時のデフォルトのイメージタイプを設定する
	 *
	 * @param NetB_Drawing_ImageTypes $type 設定するイメージタイプ
	 */
	public static function setDefaultType(NetB_Drawing_Image_Types $type) {
		self::$__defaultType = $type;
	}

	/**
	 * @static
	 *
	 * サムネイル作成時のデフォルトのイメージタイプを取得する
	 *
	 * @return NetB_Drawing_Image_Types
	 */
	public static function getDefaultThumbnailType() {
		if( ! ( self::$__defaultThumbType instanceof NetB_Drawing_Image_Types ) ) {
			self::$__defaultThumbType = NetB_Drawing_Image_Types::get( IMG_GIF );
		}
		return self::$__defaultThumbType;
	}

	/**
	 * @static
	 *
	 * サムネイル作成時のデフォルトのイメージタイプを設定する
	 *
	 * @param NetB_Drawing_ImageTypes $type 設定するイメージタイプ
	 */
	public static function setDefaultThumbnailType(NetB_Drawing_ImageTypes $type) {
		self::$__defaultThumbType = $type;
	}

	/**
	 * @static
	 *
	 * インスタンスをリサイズする際に発生するエラーを無視するかの
	 * フラグを取得する。
	 * このメソッドで取得できる値は、createThumbnail()メソッドなどの
	 * 内部でのみ使用される。
	 *
	 * @return bool リサイズ時に発生するエラーを無視する場合はtrue、それ以外はfalse
	 */
	public static function getIgnoreResizeError() {
		return self::$__ignore_resize_error ? true : false;
	}

	/**
	 * @static
	 *
	 * インスタンスをリサイズする際に発生するエラーを無視するかの
	 * フラグを設定する。
	 * このメソッドで取得できる値は、createThumbnail()メソッドなどの
	 * 内部でのみ使用される。
	 *
	 * @param bool $ignore リサイズ時に発生するエラーを無視する場合はtrue、それ以外はfalse
	 */
	public static function setIgnoreResizeError($ignore = true) {
		self::$__ignore_resize_error = $ignore ? true : false;
	}

	/**
	 * @static
	 *
	 * 現在の環境でサポートされるイメージタイプを配列で取得する。
	 * 戻り値の各要素はNetB_Drawing_Image_Types。
	 *
	 * @return array
	 */
	public static function availableTypes() {
		$result = array();
		foreach( NetB_Drawing_Image_Types::getMembers() as $type ) {
			if( imagetypes() | $type->getValue() ) $result[] = $type;
		}
		return $result;
	}

	/**
	 * @static
	 *
	 * 指定のパスまたはURLの画像ファイルを読み込み、NetB_Drawing_Imageの
	 * 新しいインスタンスを初期化する。
	 * $filenameにURLを指定できるのは、fopen_wrappersが有効な場合に限られる。
	 *
	 * @param string $filename 読み込む画像ファイルのパスまたはURL
	 * @return NetB_Drawing_Image
	 */
	public static function loadFrom($filename) {
		$result = null;
		foreach( self::__getHandlers() as $handlers ) {
			$type = $handlers['type'];
			$handle = @$handlers['load']->invoke( $filename );
			if( $handle ) {
				$result = array(
					'image_handle' => $handle,
					'type' => $type
				);
				break;
			}
		}
		if( ! $result ) {
			throw new NetB_Drawing_Exception( "file '$filename' is not a valid format." );
		}

		return new self( $result['type'], $result['image_handle'] );
	}

	/**
	 * @static
	 * @private
	 *
	 * 有効なイメージタイプとそれを読み込み/保存するためのデリゲートを
	 * 関連付けて管理する配列を取得する。
	 * 戻り値の配列の各要素はキー'type'にNetB_Drawing_Image_Types、
	 * キー'load'および'save'にimagecreateXXX/imageXXXを格納したNetB_Delegateが
	 * 格納された連想配列になる。
	 *
	 * @return array
	 */
	private static function __getHandlers() {
		if( ! is_array( self::$__handlers ) ) {
			self::$__handlers = array(
				array(
					'type' => NetB_Drawing_Image_Types::get(IMG_GIF),
					'load' => new NetB_Delegate( 'imagecreatefromgif' ),
					'save' => new NetB_Delegate( 'imagegif' )
				),
				array(
					'type' => NetB_Drawing_Image_Types::get(IMG_JPG),
					'load' => new NetB_Delegate( 'imagecreatefromjpeg' ),
					'save' => new NetB_Delegate( 'imagejpeg' )
				),
				array(
					'type' => NetB_Drawing_Image_Types::get(IMG_PNG),
					'load' => new NetB_Delegate( 'imagecreatefrompng' ),
					'save' => new NetB_Delegate( 'imagepng' )
				)
			);
		}

		return self::$__handlers;
	}

	/**
	 * @static
	 * @private
	 *
	 * 指定のイメージタイプを処理するための処理ハンドラ連想配列を取得する。
	 * 戻り値の配列の各要素はキー'type'にNetB_Drawing_Image_Types、
	 * キー'load'および'save'にimagecreateXXX/imageXXXを格納したNetB_Delegateが
	 * 格納されている。
	 *
	 * @param NetB_Drawing_Image_Types $type イメージタイプ
	 * @return array ハンドラ連想配列
	 */
	private static function __getHandler(NetB_Drawing_Image_Types $type) {
		foreach( self::__getHandlers() as $handlers ) {
			$key = $handlers['type'];
			if( $type == $key ) return $handlers;
		}
		return null;
	}

	/**
	 * @private
	 *
	 * イメージリソースID
	 * @var resource
	 */
	private $_id;

	/**
	 * @private
	 *
	 * 廃棄済みフラグ
	 * @var bool
	 */
	private $_disposed = true;

	/**
	 * @private
	 *
	 * イメージタイプ
	 * @var NetB_Drawing_Image_Types
	 */
	private $_type;

	/**
	 * @private
	 *
	 * デフォルトのサムネイルサイズ
	 * @var array
	 */
	private $_thumnail_size;

	/**
	 * @constructor
	 *
	 * NetB_Drawing_Imageの新しいインスタンスを初期化する
	 *
	 * @param NetB_Drawing_Image_Types|null $type イメージタイプ
	 * @param resource|null $image_id イメージリソースID
	 */
	public function __construct($type = null, $image_id = null) {
		self::__checkGD();

		$this->_type = ( ! ( $type instanceof NetB_Drawing_Image_Types ) ) ?
			self::getDefaultType() : $type;

		$this->_id = $image_id === null ? null : $image_id;

		$this->setDefaultThumbnailSize();

		$this->_disposed = false;
	}

	/**
	 * @destructor
	 *
	 * NetB_Drawing_Imageのインスタンスの廃棄処理
	 */
	public function __destruct() {
		$this->dispose();
	}

	/**
	 * このイメージオブジェクトを廃棄する。
	 * このメソッドを実行するとイメージリソースは廃棄され、各メソッドにアクセスすると例外がスローされる
	 */
	public function dispose() {
		if( $this->_disposed ) return;
		
		if( $this->_id !== null ) {
			imagedestroy( $this->_id );
		}

		$this->_id = null;
		$this->_type = null;

		$this->_disposed = true;
	}

	/**
	 * @private
	 *
	 * このインスタンスが廃棄されているかをチェックする。
	 * 廃棄後にこのメソッドを呼び出すと例外がスローされる
	 */
	private function _checkDispose() {
		if( $this->_disposed ) {
			throw new NetB_Drawing_Exception( 'access vioration !! this instance already disposed.' );
		}
	}

	/**
	 * @private
	 *
	 * イメージサイズを設定する。
	 * このメソッドは、有効なイメージリソースIDを保持しないインスタンスでのみ
	 * 有効で、loadFrom()スタティックメソッドで生成されたインスタンスなどで
	 * 実行すると例外がスローされる。
	 *
	 * @param int $width 変更する幅
	 * @param int $height 変更する高さ
	 * @return NetB_Drawing_Image このインスタンス自身
	 */
	private function _setImageSize($width, $height) {
		$this->_checkDispose();

		if( $this->_id !== null ) {
			if( ! self::getIgnoreResizeError() )
				throw new NetB_Drawing_Exception( 'invalid operation !! this image already initalized.' );
			return $this;
		}

		//$this->_id = imagecreate( (int)$width, (int)$height );
		$this->_id = imagecreatetruecolor( (int)$width, (int)$height );

		return $this;
	}

	/**
	 * @private
	 *
	 * このインスタンスが保持するイメージリソースIDを取得する
	 *
	 * @return resource|null
	 */
	private function _getImageId() {
		$this->_checkDispose();

		return $this->_id;
	}

	/**
	 * このインスタンスのイメージタイプを取得する
	 *
	 * @return NetB_Drawing_Image_Types
	 */
	public function getType() {
		$this->_checkDispose();

		return $this->_type;
	}

	/**
	 * このインスタンスの色数を取得する
	 *
	 * @return int
	 */
	public function getColors() {
		$this->_checkDispose();

		return $this->isTrueColor() ?
			pow( 2, 24 ) : imagecolorstotal( $this->_id );
	}

	/**
	 * このインスタンスのイメージサイズを連想配列で取得する。
	 * 連想配列はキー'width'にイメージの幅、キー'height'にイメージの高さが格納される
	 *
	 * @return array イメージの大きさを示す連想配列
	 */
	public function getImageSize() {
		$this->_checkDispose();

		return array(
			'width' => imagesx( $this->_id ),
			'height' => imagesy( $this->_id )
		);
	}

	/**
	 * このインスタンスのイメージがTrue Color(24bit color)であるかを取得する
	 *
	 * @return bool
	 */
	public function isTrueColor() {
		$this->_checkDispose();

		return imageistruecolor( $this->_id );
	}

	public function getDefaultThumbnailSize() {
		return $this->_thumbnail_size;
	}

	public function setDefaultThumbnailSize($size = array()) {
		if( ! is_array( $size ) ) $size = array();
		$size = array_merge( $size, array(
			'width' => 160,
			'height' => 160
		) );

		$this->_thumbnail_size = $size;

		return $this;
	}

	/**
	 * このインスタンスのイメージを指定ファイルに保存する。
	 *
	 * @param string $fileName 保存先のパス
	 * @param NetB_Drawing_Image_Types|null $type 保存時のイメージタイプ
	 * @param bool|null $addExtension 保存ファイル名に拡張子を付加するかのフラグ。省略時はtrue
	 * @return NetB_Drawing_Image このインスタンス自身
	 */
	public function saveAs($fileName, $type = null, $addExtension = true) {
		$addExtension = $addExtension ? true : false;
		
		$this->_checkDispose();

		$type = ( $type instanceof NetB_Drawing_Image_Types ) ?
			$type : $this->getType();

		$path = $addExtension ?
			"$fileName." . $type->getExtension() : "$fileName";

		$handlers = self::__getHandler( $type );
		$handlers['save']->invoke( $this->_id, $path );

		return $this;
	}

	/**
	 * このイメージのサムネイルイメージを作成する。
	 *
	 * @param int|null $width サムネイルの幅
	 * @param int|null $height サムネイルの高さ
	 * @return NetB_Drawing_Image サムネイルイメージオブジェクト
	 */
	public function createThumbnail($width = -1, $height = -1, $type = null) {
		$type = ( $type instanceof NetB_Drawing_Image_Types ) ?
			$type : self::getDefaultThumbnailType();

		$width = (int)$width;
		$height = (int)$height;

		$size = $this->getImageSize();

		if( $width <= 0 && $height <= 0 ) {
			$def_size = $this->getDefaultThumbnailSize();
			list( $width, $height ) = array( $def_size['width'], $def_size['height'] );
		} else if( $width <= 0 ) {
			list( $width, $height ) = array( $height, $height );
		} else if( $height <= 0 ) {
			list( $width, $height ) = array( $width, $width );
		}

		if( $size['width'] > $size['height'] ) {
			// 横長
			$height = round( $width * $size['height'] / $size['width'] );
		} else {
			// 縦長
			$width = round( $height * $size['width'] / $size['height'] );
		}

		$thumb = new self();
		$thumb->_setImageSize( $width, $height );

		$result = imagecopyresampled(
			$thumb->_getImageId(),	// destination image handle
			$this->_getImageId(),	// source image handle ( = $this )
			0, 0,					// start point of destination image
			0, 0,					// start point of source image
			$width, $height,		// destination image size
			$size['width'], $size['height']	// source image size
		);

		if( ! $result ) {
			$thumb->dispose();
			throw new NetB_Drawing_Exception( 'sorry, cannot create thumbnail.' );
		}

		return $thumb;
	}
}

