<?php
namespace Coral\Base\Validate;

use Zend\Validator\AbstractValidator;

/**
 * 郵便番号の妥当性を検証するバリデータ
 * 7桁の数字または3桁-4桁の数字の形式のみ許容する
 */
class BaseValidateZipcode extends AbstractValidator {
	/**
	 * エラー定数：データ不正
	 */
	const INVALID = 'zipcodeInvalid';

	/**
	 * エラー定数とメッセージテンプレートのマッピング
	 * @var array
	 */
	protected $_messageTemplates = array(
		self::INVALID => "'%value%' は郵便番号の形式として正しくありません"
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

		if( ! mb_ereg_match('^\d{3}\-?\d{4}$', (string)$value) ) {
			$this->error(self::INVALID);
			return false;
		}

		return true;
	}
}
