<?php
namespace Coral\Coral\Validate;

use Zend\Validator\AbstractValidator;
use Coral\Coral\CoralValidate;

/**
 * Coralシステムのルールでメールアドレス（複数記述対応）の検証を行う{@link AbstractValidator}派生バリデータ
 */
class CoralValidateMultiMail extends AbstractValidator {
	const INVALID = 'mailInvalid';

	const DEFAULT_DELIMITER = ',';

	protected $_delimiter;

	public function __construct($delimiter = null) {

	    parent::__construct();

	    $this->abstractOptions['messageTemplates'] = $this->_messageTemplates;

	    $this->setDelimiter($delimiter);
	}

	public function getDelimiter() {
		return $this->_delimiter;
	}

	public function setDelimiter($delimiter = null) {
		$delimiter = trim((string)$delimiter);
		if(empty($delimiter)) $delimiter = self::DEFAULT_DELIMITER;
		$this->_delimiter = $delimiter;
		return $this;
	}

	protected $_messageTemplates = array(
		self::INVALID => "'%value%'は無効なメールアドレスです"
	);

	public function isValid($value) {
		$value = (string)$value;

		$values = explode($this->getDelimiter(), $value);
		$obj = new CoralValidate();
		foreach($values as $v) {
			if( ! $obj->isMail(trim($v)) ) {
                $this->setValue($v);
			    // エラーが見つかったら即エラー
				$this->error(self::INVALID);
				return false;
			}
		}
		return true;
	}
}
