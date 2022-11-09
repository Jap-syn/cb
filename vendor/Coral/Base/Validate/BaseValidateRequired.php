<?php
namespace Coral\Base\Validate;

require_once 'Zend/Validate/Abstract.php';

/**
 * データが null または長さ0の文字列ではないかを検証する検証クラス。
 * {@link Zend_Validate_NotEmpty} と違い、0（または0に変換可能な文字列）は正常データとみなす
 */
class BaseValidateRequired extends Zend_Validate_Abstract {
	const NO_VALUE = 'noValue';
	
	protected $_messageTemplates = array(
		self::NO_VALUE => "null は許容されません。値を設定してください"
	);
	
	public function isValid($value) {
		$valueString = (string)$value;
		
		$this->_setValue($valueString);
		
		if( $valueString === null || ! strlen($valueString) ) {
			$this->_error();
			return false;
		}
		
		return true;
	}
}

