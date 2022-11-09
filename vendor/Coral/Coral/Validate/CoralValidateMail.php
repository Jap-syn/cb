<?php
namespace Coral\Coral\Validate;

use Zend\Validator\AbstractValidator;
use Coral\Coral\CoralValidate;

/**
 * Coralシステムのルールでメールアドレスの検証を行う{@link AbstractValidator}派生バリデータ
 */
class CoralValidateMail extends AbstractValidator {
	const INVALID = 'mailInvalid';

	protected $_messageTemplates = array(
		self::INVALID => "'%value%'は無効なメールアドレスです"
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
	    if( ! $obj->isMail($value) ) {
			$this->error(self::INVALID);
			return false;
		}
		return true;
	}
}
