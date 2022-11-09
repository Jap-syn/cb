<?php
namespace Coral\Coral\Auth\Exception;

use Coral\Coral\Auth\CoralAuthException;

/**
 * 認証処理時に、指定のアカウントがクライアントレベルでロックされていることを通知する例外クラス
 */
class CoralAuthExceptionClientLocked extends CoralAuthException {
}
