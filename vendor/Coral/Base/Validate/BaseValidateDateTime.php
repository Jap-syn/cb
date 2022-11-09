<?php
namespace Coral\Base\Validate;

require_once 'Zend/Validate/Abstract.php';
require_once 'Zend/Date.php';

/**
 * 日付時刻型の妥当性を検証するバリデータ
 */
class BaseValidateDateTime extends Zend_Validate_Abstract {
	/**
	 * エラー定数：データ不正
	 * @var string
	 */
	const INVALID = 'dateTimeInvalid';
	
	/**
	 * 文字列としてのマッチングパターン
	 * @var string
	 */
	const PATTERN = '^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}(:\d{2})?)?$';
	
	/**
	 * エラー定数とメッセージテンプレートのマッピング
	 * @var array
	 */
	protected $_messageTemplates = array(
		self::INVALID => "'%value%' は日付時刻として正しくありません"
	);
	
	/**
	 * オーバーライド。指定の値に対して検証を行う
	 * @param mixed $value 検証対象のデータ
	 * @return boolean 検証に成功した場合はtrue、それ以外はfalse
	 */
	public function isValid($value) {
		$this->_setValue($value);
		
		try {
			// 日付インスタンスの生成を試みる
			$d = new Zend_Date((string)$value, 'yyyy-MM-dd HH:mm:ss');
		} catch(Exception $err) {
			$this->_error(self::INVALID);
			return false;
		}
		
		// Zend_Dateが割とあいまいに解釈するのでパターンマッチングを適用
		if( !mb_ereg(self::PATTERN, (string)$value) ) {
			$this->_error(self::INVALID);
			return false;
		}
		
		return true;
	}
}
