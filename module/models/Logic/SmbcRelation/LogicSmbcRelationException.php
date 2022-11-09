<?php
namespace models\Logic\SmbcRelation;

use models\Logic\SmbcRelation\LogicSmbcRelationException;

/**
 * SMBC決済ステーション連携例外
 */
class LogicSmbcRelationException extends \Exception {
    /**
     * 任意のアプリケーションデータ
     *
     * @access protected
     * @var mixed
     */
    protected $app_data = null;

    /**
     * エラーメッセージとアプリケーションデータ、エラーコードを指定して
     * LogicSmbcRelationExceptionの新しいインスタンスを初期化する
     *
     * @param string $message エラーメッセージ
     * @param mixed $app_data 任意のアプリケーションデータ
     * @param int $code エラーコード
     */
    public function __construct($message = null, $app_data = null, $code = 0) {
        parent::__construct($message, $code);
        $this->app_data = $app_data;
    }

    /**
     * この例外のアプリケーションデータを取得する
     *
     * @return mixed アプリケーションデータ
     */
    public function getApplicationData() {
        return $this->app_data;
    }
}
