<?php
namespace Coral\Base\IO;

/**
 * BaseIOException名前空間向けの例外クラス
 */
class BaseIOException extends \Exception {
	public function __construct($message = null, $code = 0) {
		parent::__construct($message, $code, /* $previous = */null);
	}
}
