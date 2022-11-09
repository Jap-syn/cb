<?php
namespace Coral\Base\Validate;

require_once 'Zend/Validate/StringLength.php';

/**
 * マルチバイト環境で正しく長さを検出できる、{@link Zend_Validate_StringLength}の修正版
 */
class BaseValidateStringLength extends Zend_Validate_StringLength {
	public function isValid($value) {
        $valueString = (string) $value;
        $this->_setValue($valueString);
		$length = mb_strlen($valueString);
        if ($length < $this->_min) {
            $this->_error(self::TOO_SHORT);
        }
        if (null !== $this->_max && $this->_max < $length) {
            $this->_error(self::TOO_LONG);
        }
        if (count($this->_messages)) {
            return false;
        } else {
            return true;
        }
	}
}
