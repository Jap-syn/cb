<?php
namespace Coral\Base\Validate;

use Zend\Validator\AbstractValidator;

/**
 * 電話番号の妥当性を検証するバリデータ
 * 1～5桁の3つの数字ブロックを半角のマイナス記号で区切った形式許容され、
 * さらに合計15桁以下である必要がある
 */
class BaseValidatePhoneNumber extends AbstractValidator {
	/**
	 * エラー定数：データ不正
	 */
	const INVALID = 'phoneNumberInvalid';

	/**
	 * エラー定数：データ長オーバー
	 */
	const TOO_LONG = 'phoneNumberTooLong';

	/**
	 * エラー定数：データ長不足
	 */
	const TOO_SHORT = 'phoneNumberTooShort';

	/**
	 * エラー定数とメッセージテンプレートのマッピング
	 * @var array
	 */
	protected $_messageTemplates = array(
		self::INVALID => "'%value%' は電話番号の形式として正しくありません",
		self::TOO_SHORT => "'%value%' は短すぎます",
		self::TOO_LONG => "'%value%' は長すぎます"
	);

    /**
     * コンストラクタ
     * @param array|Traversable $options
     */
	public function __construct($options = null) {

	    parent::__construct($options);

	    $this->abstractOptions['messageTemplates'] = $this->_messageTemplates;
	}

	/**
	 * オーバーライド。指定の値に対して検証を行う
	 * @param mixed $value 検証対象のデータ
	 * @return boolean 検証に成功した場合はtrue、それ以外はfalse
	 */
	public function isValid($value) {
        $this->setValue($value);

        $value = (string)$value;
        if( ! mb_ereg_match('^\d{1,5}\-?\d{1,5}\-\d{1,5}$', $value) ) {
            $this->error(self::INVALID);
            return false;
        }
        else if( strlen($value) < 3 ) {
            $this->error(self::TOO_SHORT);
            return false;
        }
        else if( strlen($value) > 15 ) {
            $this->error(self::TOO_LONG);
            return false;
        }
        return true;
	}
}