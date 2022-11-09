<?php
namespace models\Logic\Exception;
/**
 * 請求エラー用Exception
 */
class LogicClaimException extends \Exception {

    const ERR_CODE_SMBC = 1;        // SMBC連携エラー
    const ERR_CODE_0YEN = 2;        // 0円請求エラー
    const ERR_CODE_LIMIT_DAY = 3;   // 支払期限エラー
    const ERR_CODE_PAYEASY = 4;     // ペイジー連携エラー
    const ERR_CODE_FORCE_CANCEL_DATE = 5;   // 強制解約日エラー
    const ERR_CODE_PRINT_PATTERN = 6;   // 印刷パターンマスタ未存在エラー
    const ERR_CODE_PAYMENT_CHECK = 7;   // 支払方法チェックマスタ未存在エラー

}
?>