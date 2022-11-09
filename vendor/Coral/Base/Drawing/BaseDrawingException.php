<?php
namespace Coral\Base\Drawing;

/**
 * BaseDrawing名前空間向けの例外クラス
 */
class BaseDrawingException extends \Exception {
	public function __construct($message = null, $code = 0) {
		parent::__construct($message, $code, /* $previous = */null);
	}
}
