<?php
namespace Coral\Coral\Mail;

/**
 * CoralMailのメソッドで発生する例外クラス
 */
class CoralMailException extends \Exception {
	/**
	 * 原因となった例外
	 *
	 * @var Exception
	 */
	protected $_innerException;

	/**
	 * CoralMailExceptionの新しいインスタンスを初期化する
	 *
	 * @param string|null $message エラーメッセージ
	 * @param int|null $code エラーコード
	 * @param Exception|null 原因の例外
	 */
	public function __construct($message = null, $code = 0, $innerException = null) {
		parent::__construct($message, $code, /* $previous = */null);

		$this->_innerException = ( $innerException instanceof \Exception ) ? $innerException : null;
	}

	/**
	 * この例外の原因となった基の例外を取得する
	 *
	 * @return Exception|null
	 */
	public function getInnerException() {
		return $this->_innerException;
	}
}
