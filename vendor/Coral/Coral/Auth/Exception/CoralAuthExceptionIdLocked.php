<?php
namespace Coral\Coral\Auth\Exception;

use Coral\Coral\Auth\CoralAuthException;

/**
 * 認証処理時に、指定のアカウントがIDレベルでロックされていることを通知する例外クラス
 */
class CoralAuthExceptionIdLocked extends CoralAuthException {
}
