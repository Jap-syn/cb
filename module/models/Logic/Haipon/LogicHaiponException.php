<?php
namespace models\Logic\Haipon;
/**
 * 配送でポン連携ロジック用例外クラス
 */
class LogicHaiponException extends \Exception {
    /**
     * LogicHaiponExceptionの新しいインスタンスを初期化する
     *
     * @param null | string $message
     * @param null | int $code
     */
    public function __construct($message = null, $code = 0) {
        parent::__construct($message, $code);
    }
}
