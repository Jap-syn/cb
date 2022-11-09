<?php
namespace models\Logic\RwarvlData;

/**
 * 着荷確認データ連携ロジック用例外クラス
 */
class LogicRwarvlDataException extends \Exception {
    /**
     * LogicRwarvlDataExceptionの新しいインスタンスを初期化する
     *
     * @param null | string $message
     * @param null | int $code
     */
    public function __construct($message = null, $code = 0) {
        parent::__construct($message, $code);
    }
}
