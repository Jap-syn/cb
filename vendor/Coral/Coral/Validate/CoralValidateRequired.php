<?php
namespace Coral\Coral\Validate;

use Zend\Validator\AbstractValidator;

/**
 * データが null または長さ0の文字列ではないかを検証する検証クラス。
 * 尚、0（または0に変換可能な文字列）は正常データとみなす
 */
class CoralValidateRequired extends AbstractValidator {
	const NO_VALUE = 'noValue';

	protected $_messageTemplates = array(
		self::NO_VALUE => "null は許容されません。値を設定してください"
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
		$valueString = (string)$value;

		$this->setValue($valueString);

		if( $valueString === null || ! strlen($valueString) ) {
			$this->error(self::NO_VALUE);
			return false;
		}

		return true;
	}

	/**
	 * 数値チェック（整数）
	 *
	 * @param string $data　チェックするデータ
	 * @return boolean
	 */
	public static function isInt($data)
	{
	    // ZF1のソースから抽出
	    $value = $data;

	    $valueString = (string) $value;

	    $locale = localeconv();

	    $valueFiltered = str_replace($locale['decimal_point'], '.', $valueString);
	    $valueFiltered = str_replace($locale['thousands_sep'], '', $valueFiltered);

	    if (strval(intval($valueFiltered)) != $valueFiltered) {
	        return false;
	    }

	    return true;

	}
}

