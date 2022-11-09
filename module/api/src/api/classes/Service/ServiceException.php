<?php
namespace api\classes\Service;

/**
 * APIサービス向け例外クラス
 */
class ServiceException extends \Exception {
    const SYS_ERR_CODE_901 = '901';

    const SYS_ERR_CODE_902 = '902';

    const SYS_ERR_MSG_901 = 'システムで障害が発生しました。後払い.comのサポートセンター（0120-667-690）までお問い合わせください';

    const SYS_ERR_MSG_902 = 'システムメンテナンス中です。詳しくは後払い.comのサポートセンター（0120-667-690）までお問い合わせください';

    /**
     * APIサービスID
     * @var string
     */
    protected $_serviceId;

    /**
     * サービスエラーコード
     * @var string
     */
    protected $_errorCode;

    /**
     * エラーメッセージとAPIサービスID、サービスエラーコードを指定して
     * ServiceExceptionの新しいインスタンスを初期化する
     *
     * @param string $message エラーメッセージ
     * @param string $serviceId APIサービスID。2桁の数字を指定する（例：注文登録 → 00）
     * @param string $errorCode サービスエラーコード。3桁の数字を指定する（例：IP認証エラー → 101）
     */
    public function __construct($message, $serviceId, $errorCode) {
        $this
            ->setServiceId($serviceId)
            ->setErrorCode($errorCode);
        parent::__construct($message, 0);
    }

    /**
     * APIサービスIDを取得する
     *
     * @return string APIサービスID。2桁の数字（例：注文登録 → 00）
     */
    public function getServiceId() {
        return $this->_serviceId;
    }
    /**
     * APIサービスIDを設定する
     *
     * @param string $serviceId APIサービスID。2桁の数字を指定する（例：注文登録 → 00）
     * @return ServiceException このインスタンス
     */
    public function setServiceId($serviceId) {
        $this->_serviceId = $serviceId;
        return $this;
    }

    /**
     * サービスエラーコードを取得する
     *
     * @return string サービスエラーコード。3桁の数字（例：IP認証エラー → 101）
     */
    public function getErrorCode() {
        return $this->_errorCode;
    }
    /**
     * サービスエラーコードを設定する
     *
     * @param string $errorCode サービスエラーコード。3桁の数字を指定する（例：IP認証エラー → 101）
     * @return ServiceException このインスタンス
     */
    public function setErrorCode($errorCode) {
        $this->_errorCode = $errorCode;
        return $this;
    }

    /**
     * 'EXXYYY'形式のフォーマット済みサービスエラーコードを取得する。
     * フォーマット済みエラーコードは固定の'E'で始まり、
     * 'XX'は{@link getServiceId}で得られる2桁のAPIサービスID、
     * 'YYY'は{@link getErrorCode}で得られる3桁のサービスエラーコードから形成される
     *
     * @return string フォーマット済みサービスエラーコード
     */
    public function getFormattedErrorCode() {
        return sprintf('E%s%s', $this->getServiceId(), $this->getErrorCode());
    }
}
