<?php
namespace api\classes\Service\Cancel;

/**
 * 注文キャンセルAPIの個別エラーを管理するクラス
 */
class ServiceCancelRequestError {
    /**
     * 個別エラーコード
     *
     * @var string
     */
    public $reqErrorCode;

    /**
     * 個別エラーコードを指定してService_Cancel_RequestErrorの
     * 新しいインスタンスを初期化する
     *
     * @param string $reqErrorCode 個別エラーコード
     */
    public function __construct($reqErrorCode) {
        $this->reqErrorCode = $reqErrorCode;
    }
}
