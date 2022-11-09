<?php
namespace Coral\Base\Extra\FFmpeg;

/**
 * BaseExtraFFmpeg名前空間向けの例外クラス
 */
class BaseExtraFFmpegException extends \Exception {
	public function __construct($message = null, $code = 0) {
		parent::__construct($message, $code, /* $previous = */null);
	}
}
