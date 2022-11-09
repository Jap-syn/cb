<?php
namespace Coral\Coral\Validate;

require_once 'Zend/Validate/Abstract.php';
require_once 'Coral/Validate.php';

/**
 * Creekシステムのルールでメールアドレスの部分一致検証を行う{@link Zend_Validate_Abstract}派生バリデータ
 */
class CoralValidateMailPart extends Zend_Validate_Abstract {
	const INVALID = 'mailInvalid';
	
	protected $_messageTemplates = array(
		self::INVALID => "'%value%'にメールアドレスに使用できない文字が含まれています"
	);
	
	public function isValid($value) {
		if( ! Coral_Validate::isMailPart($value) ) {
			$this->_error(self::INVALID);
			return false;
		}
		return true;
	}
}
