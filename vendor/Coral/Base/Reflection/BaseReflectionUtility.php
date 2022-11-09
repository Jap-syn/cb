<?php
namespace Coral\Base\Reflection;

use Coral\Base\Reflection\BaseReflectionException;

/**
 * リフレクション関連のユーティリティクラス
 *
 */
class BaseReflectionUtility {
	/**
	 * @static
	 *
	 * コールバックとして適切かを検証する。
	 * 具体的には$callbackの形式がそのままcall_user_func関数に使用できるかを検証する
	 *
	 * @param mixed $callback コールバック情報
	 * @return bool
	 */
	public static function isCallback($callback) {
		// stringまたはarrayのみ許容
		if( is_string( $callback ) || is_array( $callback ) ) {
			if( is_array( $callback ) ) {
				// メソッドコールバックの検証
				if(
					// 配列の長さが不正
					count( $callback ) != 2 ||
					// 第一要素がobjectまたはstringではない
					( ! is_object( $callback[0] ) && ! is_string( $callback[0] ) ) ||
					// 第二要素がstringではない
					! is_string( $callback[1] )
				) {
					// arrayの構成が不正
					return false;
				}
			}
			// コールバックとして適切
			return true;
		} else {
			// コールバックとして適切ではない
			return false;
		}
	}

	/**
	 * @static
	 *
	 * コールバック関数を示す文字列を取得する。
	 * $callbackが文字列の場合は関数名、call_user_funcに使用できるarrayの場合は
	 * クラス名::メソッド名（クラスメソッド）またはクラス名->メソッド名（インスタンスメソッド）の形式の
	 * 文字列が生成される。コールバックとして適切な形式でない場合はnullが返る
	 *
	 * @param mixed $callback コールバック情報
	 * @return $callbackの内容を示す文字列またはnull
	 */
	public static function getCallbackString($callback) {
		// コールバックとして適切ではないのでnullを返す
		if( ! self::isCallback($callback) ) return null;

		// 関数コールバックはそのまま返す
		if( is_string( $callback ) ) return $callback;

		// クラス名::メソッド名の形式で返す
		return ( is_string( $callback[0] ) ) ?
			"{$callback[0]}::{$callback[1]}" :
			get_class($callback[0]) . "->{$callback[1]}";
	}

// Del By Takemasa(NDC) 20150114 Stt 未使用故コメントアウト化(NetB_Extra_FFmpeg_Informationからしか呼ばれない上、移植対象外ゆえ)
// 	/**
// 	 * @static
// 	 *
// 	 * 指定の名前のクラスを、指定のパラメータ配列をパラメータにして
// 	 * コンストラクトする
// 	 *
// 	 * @param string コンストラクトするクラスのクラス名
// 	 * @param array $params コンストラクタパラメータ
// 	 * @return object $classNameの新しいインスタンス
// 	 */
// 	public static function createClass($className, $params) {
// 		$className = "$className";
//
// 		if( ! class_exists( $className ) ) {
// 			require_once 'Zend/Loader.php';
// 			Zend_Loader::loadClass( $className );
// 		}
//
// 		$call_params = array();
// 		for($i = 0; $i < count($params); $i++) {
// 			$call_params[] = '$params[' . $i . ']';
// 		}
// 		$obj = null;
//
// 		eval( '$obj = new ' . $className . '(' . join(',', $call_params) . ');' );
//
// 		return $obj;
// 	}
// Del By Takemasa(NDC) 20150114 End 未使用故コメントアウト化

	/**
	 * @static
	 *
	 * var_export()で出力したPHPソースを復元し、その値を返す。
	 * $sourceに構文エラーがあった場合はFALSEを返すが、$sourceをeval()した際に発生する
	 * ランタイムエラーはそのままスローされる。
	 *
	 * @param string $source 復元するソース文字列またはソースファイルのパス
	 * @param null|bool $ignore_path $sourceをファイルとして見なさない場合はtrueを指定する
	 * @return $sourceから復元された値。$sourceの構文エラー時はfalse。
	 */
	public static function varImport($source, $ignore_path = false) {
		if( is_file( $source ) && ! $ignore_path ) {
			$source = file_get_contents( $source );
		}
		$_r = '$result';
		$_e = '$err';
		// evalするコードを構築。内部で例外を捕捉しておく
		$source = <<<SOURCE_END
try {
	$_r = $source;
} catch(\Exception $_e) {
	$_r = $_e;
}
return $_r;
SOURCE_END;
		if( ! eval( $source ) ) return false;
		if( $result instanceof \Exception ) throw new BaseReflectionException( "Runtime error on eval().", null, $result );
		return $result;
	}

	/**
	 * @static
	 *
	 * 指定の値がnull、空文字または空の配列であるかを検査する。
	 * 組み込み関数のempty()と違い、数値の0はfalseを返す。
	 *
	 * @param mixed $value 検査する値
	 * @return $valueがnull、空文字、空の配列である場合はtrue、それ以外はfalse
	 */
	public static function isEmpty($value) {
		return ( empty( $value ) && ! preg_match( '/^0$/', trim("$value") ) ) ? true : false;
	}

	/**
	 * @static
	 *
	 * 指定の値が数値と見なせるかを判断する。
	 * このメソッドはis_numeric()組み込み関数のエイリアスである。
	 *
	 * @param mixed $value 検査する値
	 * @return bool $valueを数値と見なせる場合はtrue、それ以外はfalse
	 */
	public static function isNumeric($value) {
		return is_numeric( $value ) ? true : false;
	}

	/**
	 * @static
	 *
	 * 指定の値が0より大きい数値と見なせるかを判断する。0は含まれない
	 *
	 * @param mixed $value 検査する値
	 * @return bool $valueが0より大きい数値と見なせる場合はtrue、それ以外はfalse
	 */
	public static function isPositiveNumeric($value) {
		$value = trim( "$value" );
		if( ! self::isNumeric( $value ) ) return false;
		return ( ((float)$value) > 0 ) ? true : false;
	}

	/**
	 * @static
	 *
	 * 指定の値が0より小さい数値と見なせるかを判断する。0は含まれない。
	 *
	 * @param mixed $value 検査する値
	 * @return bool $valueが0より小さい数値と見なせる場合はtrue、それ以外はfalse
	 */
	public static function isNegativeNumeric($value) {
		$value = trim( "$value" );
		if( ! self::isNumeric( $value ) ) return false;
		return ( ((float)$value) < 0 ) ? true : false;
	}

	/**
	 * @static
	 *
	 * 指定の値が0以上の数値と見なせるかを判断する。
	 *
	 * @param mixed $value 検査する値
	 * @return bool $valueが0以上の数値と見なせる場合はtrue、それ以外はfalse
	 */
	public static function isNonNegativeNumeric($value) {
		$value = trim( "$value" );
		if( ! self::isNumeric( $value ) ) return false;
		return ( ((float)$value) >= 0 ) ? true : false;
	}

	/**
	 * @static
	 *
	 * 指定の値が0以下の数値と見なせるかを判断する。
	 *
	 * @param mixed $value 検査する値
	 * @return bool $valueが0以下の数値と見なせる場合はtrue、それ以外はfalse
	 */
	public static function isNonPositiveNumeric($value) {
		$value = trim( "$value" );
		if( ! self::isNumeric( $value ) ) return false;
		return ( ((float)$value) <= 0 ) ? true : false;
	}

	/**
	 * @static
	 *
	 * 指定の値が整数値と見なせるかを判断する。
	 * 組み込みのis_int()とは異なり、文字列'123'もtrueを返す。
	 *
	 * @param mixed $value 検査する値
	 * @return bool $valueを整数と見なせる場合はtrue、それ以外はfalse
	 */
	public static function isInteger($value) {
		if( ! self::isNumeric( $value ) ) return false;
		$i = (int)$value;
		return ( "$value" === "$i" ) ? true : false;
	}

	/**
	 * @static
	 *
	 * 指定の値が正の整数値と見なせるかを判断する。0は含まれない。
	 *
	 * @param mixed $value 検査する値
	 * @return bool $valueを正の整数と見なせる場合はtrue、それ以外はfalse
	 */
	public static function isPositiveInteger($value) {
		return ( self::isInteger($value) && self::isPositiveNumeric($value) ) ?
			true : false;
	}

	/**
	 * @static
	 *
	 * 指定の値が負の整数値と見なせるかを判断する。0は含まれない。
	 *
	 * @param mixed $value 検査する整数
	 * @return bool $valueを負の整数と見なせる場合はtrue、それ以外はfalse
	 */
	public static function isNegativeInteger($value) {
		$value = trim( "$value" );
		return ( self::isInteger( $value ) && ((int)$value) < 0 ) ? true : false;
	}

	/**
	 * @static
	 *
	 * 指定の値が0以下の整数と見なせるかを判断する。
	 *
	 * @param mixed $value 検査する値
	 * @return bool $valueを0以下の整数と見なせる場合はtrue、それ以外はfalse
	 */
	public static function isNonPositiveInteger($value) {
		$value = trim( "$value" );
		return ( self::isInteger( $value ) && ((int)$value) <= 0 ) ? true : false;
	}

	/**
	 * @static
	 *
	 * 指定の値が0以上の整数と見なせるかを判断する
	 *
	 * @param mixed $value 検査する値
	 * @return bool $valueが0以上の整数と見なせる場合はtrue、それ以外はfalse
	 */
	public static function isNonNegativeInteger($value) {
		$value = trim( "$value" );
		return ( self::isInteger( $value ) && ((int)$value) >= 0 ) ? true : false;
	}

	/**
	 * @static
	 *
	 * 指定の値が実数と見なせるかを判断する。
	 * 組み込みのis_float()とは異なり、文字列'3.14'もtrueを返す
	 *
	 * @param mixed $value 検査する値
	 * @return bool $valueを実数と見なせる場合はtrue、それ以外はfalse
	 */
	public static function isFloat($value) {
		if( ! self::isNumeric( $value ) ) return false;
		if( preg_match( '/^-?\d+(\.\d+)$/', trim( "$value" ) ) ) return true;
		$f = (float)$value;
		if( self::isInteger( $f ) ) return false;
		return is_float( $f ) ? true : false;
	}
}
