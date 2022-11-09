<?php
namespace Coral\Base\Reflection;

/**
 * BaseReflection名前空間向けの例外クラス
 */
class BaseReflectionException extends \Exception {
	protected $_innerException = null;

	public function __construct($message = null, $code = 0, $innerException = null) {
		parent::__construct($message, $code, /* $previous = */null);
		$this->setInnerException( $innerException );
	}

	public function getInnerException() {
		return $this->_innerException;
	}
	public function setInnerException($innerException) {
		$this->_innerException = $innerException;
		if($this->_innerException) {
		    $this->message = "Reflection Exception: {$this->message} by '" . $this->_innerException->getMessage() . "'.";
		}
	}
}
