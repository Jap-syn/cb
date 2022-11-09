<?php
namespace Coral\Coral\Validate;

use Zend\Validator\AbstractValidator;
use Coral\Coral\CoralValidate;

/**
 * Coralシステムのルールで郵便番号の検証を行う{@link AbstractValidator}派生バリデータ
 */
class CoralValidatePostalCode extends AbstractValidator {
	const INVALID = 'postalCodeInvalid';

	protected $_messageTemplates = array(
		self::INVALID => "'%value%'は無効な郵便番号です"
	);

	/**
	 * コンストラクタ
	 * @param array|Traversable $options
	 */
	public function __construct($options = null) {

	    parent::__construct($options);

	    $this->abstractOptions['messageTemplates'] = $this->_messageTemplates;
	}

	public function isValid($value) {
	    $this->setValue($value);

	    $obj = new CoralValidate();
	    if( ! $obj->isPostCode($value) ) {
			$this->error(self::INVALID);
			return false;
		}
		return true;
	}
}