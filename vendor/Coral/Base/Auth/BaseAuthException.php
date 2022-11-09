<?php
namespace Coral\Base\Auth;

/**
 * BaseAuth名前空間向けの例外クラス
 */
class BaseAuthException extends \Exception {
    public function __construct($message = null, $code = null) {
        parent::__construct($message, $code, /* $previous = */null);
    }
}
