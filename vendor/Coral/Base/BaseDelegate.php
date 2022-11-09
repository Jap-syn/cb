<?php
namespace Coral\Base;

use Coral\Base\Reflection\BaseReflectionException;
use Coral\Base\Reflection\BaseReflectionUtility;

/**
 * コールバック関数をラッピングするデリゲートクラス
 */
class BaseDelegate {
	/**
	 * コールバックオブジェクト
	 *
	 * @var string|array
	 */
	private $_callback;

	/**
	 * BaseDelegateの新しいインスタンスを初期化する
	 *
	 * @param string|mixed $thisObj コールバック関数名、クラス名、またはオブジェクトインスタンスのいずれか
	 * @param string|null $methodName $thisObjがクラス名またはオブジェクトインスタンスの場合にのみ使用される、メソッド名
	 * @throws BaseReflectionException $callbackが適切な形式でない場合にスローされる
	 */
	public function __construct($thisObj, $methodName = null) {
		$callback = ( is_string( $thisObj ) && empty( $methodName ) ) ?
			// $thisObjが文字列で$methodNameがnullの場合は関数指定
			$thisObj :
			// それ以外はメソッド指定
			array( $thisObj, "$methodName" );

		if( ! BaseReflectionUtility::isCallback( $callback ) ) {
			throw new BaseReflectionException( 'コールバックが適切な形式ではありません' );
		}

		$this->_callback = $callback;
	}

	/**
	 * コールバックを実行する
	 *
	 * @param [mixed parameter [, mixed ...]] 可変長パラメータ
	 * @return mixed コールバックの実行結果
	 */
	public function invoke() {
		return $this->invokeByArray( func_get_args() );
	}

	/**
	 * コールバック関数へのパラメータを配列で指定して、コールバックを実行する
	 *
	 * @param array $params コールバック関数の呼び出しに渡す引数のリスト
	 * @return mixed コールバックの実行結果
	 */
	public function invokeByArray(array $params) {
		return call_user_func_array( $this->_callback, $params );
	}

	/**
	 * このコールバックデリゲートの内容を表す文字列を返す
	 *
	 * @return string
	 */
	public function __toString() {
		return BaseReflectionUtility::getCallbackString( $this->_callback );
	}
}
