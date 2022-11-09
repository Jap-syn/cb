<?php
//require_once 'functions.php';
namespace models\Logic\SelfBilling;



/**
 * 請求書同梱ツールロジック用例外
 * クライアントへ返す拡張エラーコードを設定可能
 */
class LogicSelfBillingException extends \Exception {
	/**
	 * 追加エラーコード：ランタイム例外などの一般的なエラー
	 * @var string
	 */
	const ERR_GENERAL_EXCEPTION = 'E001';

	/**
	 * 追加エラーコード：アクセスキー不正
	 * @var string
	 */
	const ERR_PERMISSION_INVALID_ACCESS_KEY = 'E101';

	/**
	 * 追加エラーコード：バージョン不正
	 * @var string
	 */
	const ERR_PERMISSION_INVALID_VERSION = 'E109';

	/**
	 * 追加エラーコード：コマンド指定不正
	 * @var string
	 */
	const ERR_DISPATCH_INVALID_COMMAND = 'E201';

	/**
	 * 追加エラーコード：パラメータ指定不正
	 * @var string
	 */
	const ERR_DISPATCH_INVALID_PARAMETER = 'E202';


	/**
	 * 追加エラーコード
	 *
	 * @access protected
	 * @var string
	 */
	protected $_additional_code;

    /**
     * Logic_SelfBilling_Exceptionの新しいインスタンスを初期化する
     *
     * @param null|string 例外メッセージ
     * @param null|int|string エラーコード。追加エラーコードを指定可能
     */
    public function __construct($message = '', $code = 0) {
        if(is_string($code)) {
            // 追加エラーコード指定あり
            $this->_additional_code = $code;
            $code = 0;
        } else {
            // 通常のエラーコード指定時は一般エラー扱い
            $this->_additional_code = self::ERR_GENERAL_EXCEPTION;
        }
        parent::__construct($message, $code);
    }

	/**
	 * 'Exxx'形式の追加エラーコードを取得する
	 *
	 * @return string
	 */
	final public function getAdditionalCode() {
		return $this->_additional_code;
	}
}
