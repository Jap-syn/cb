<?php
namespace Coral\Coral\Auth;

use Coral\Coral\Auth\Exception\CoralAuthExceptionClientLocked;
use Coral\Coral\Auth\Exception\CoralAuthExceptionIdLocked;

/**
 * CoralAuth ライブラリで発生する例外を規定する抽象例外クラス。
 * 型の起点としての役割のほか、具象例外クラスをスローするための静的メソッドも実装する
 */
class CoralAuthException extends \Exception {
    /**
     * クライアントレベルでロックされていることを通知する例外をスローする
     *
     * @static
     * @param null | string $message
     * @param null | int $code
     */
    public static function throwClientLockedException($message = null, $code = 0) {
        throw new CoralAuthExceptionClientLocked($message, $code);
    }

    /**
     * IDレベルでロックされていることを通知する例外をスローする
     *
     * @static
     * @param null | string $message
     * @param null | int $code
     */
    public static function throwIdLockedException($message = null, $code = 0) {
        throw new CoralAuthExceptionIdLocked($message, $code);
    }
}
