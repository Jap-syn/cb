<?php
namespace Coral\Base\Validate;

require_once 'Zend/Validate/Abstract.php';

/**
 * ID/パスワードを検証するバリデータ
 * 使用可能な文字を英数字・ハイフン・ピリオド・アンダースコアおよびアットマークに制限し、
 * コンストラクタで最小/最大文字列長を設定できる。
 */
class BaseValidateIdPassword extends Zend_Validate_Abstract {
	/**
	 * エラー定数：文字不正
	 * @var string
	 */
	const INVALID = 'idPasswordInvalid';
	
	/**
	 * エラー定数：最小文字数以下
	 * @var string
	 */
	const TOO_SHORT = 'idPasswordTooShort';
	
	/**
	 * エラー定数：最大文字数超過
	 * @var string
	 */
	const TOO_LONG = 'idPasswordTooLong';
	
	/**
	 * 検証用正規表現
	 * @var string
	 */
	const PATTERN = '/^[a-zA-Z\d\-\._@]+$/';
	
	/**
	 * エラー定数とメッセージテンプレートのマッピング
	 * @var array
	 */
	protected $_messageTemplates = array(
		self::INVALID => "'%fields%' は正しい形式ではありません。英数字とピリオド、ハイフン、アットマーク、アンダースコアのみ使用できます",
		self::TOO_SHORT => "'%fields%' は %min% 文字以上で入力してください",
		self::TOO_LONG => "'%fields%' は %max% 文字以下で入力してください"
	);
	
	/**
	 * 追加のメッセージ変数
	 * @var array
	 */
	protected $_messageVariables = array(
		'min' => '_min',
		'max' => '_max'
	);
	
	/**
	 * 最小文字数
	 * @var int
	 */
	protected $_min;
	
	/**
	 * 最大文字数
	 * @var int
	 */
	protected $_max;
	
	/**
	 * 文字数の下限・上限を指定して、NetB_Validate_IdPasswordの
	 * 新しいインスタンスを初期化する
	 *
	 * @param null|int $min 最小文字数。省略時は0
	 * @param null|int $max 最大文字数
	 */
	public function __construct($min = 0, $max = null) {
		$this->setMin($min)->setMax($max);
	}
	
	/**
	 * 最小文字数を取得する
	 * @return int
	 */
    public function getMin() {
        return $this->_min;
    }
	
	/**
	 * 最小文字数を設定する
	 * @param int $min 設定する最小文字数。0以下を指定した場合は0と見なされる
	 * @return NetB_Validate_IdPassword このインスタンス自身
	 */
    public function setMin($min) {
        $this->_min = max(0, (integer) $min);
        return $this;
    }
	
	/**
	 * 最大文字数を取得する
	 * @return null|int
	 */
    public function getMax() {
        return $this->_max;
    }

	/**
	 * 最大文字数を設定する
	 * @param null|int $max 設定する最大文字数。null指定可能
	 * @return NetB_Validate_IdPassword このインスタンス自身
	 */
    public function setMax($max) {
        if (null === $max) {
            $this->_max = null;
        } else {
            $this->_max = (integer) $max;
        }

        return $this;
    }

	/**
	 * オーバーライド。指定の値に対して検証を行う
	 * @param mixed $value 検証対象のデータ
	 * @return boolean 検証に成功した場合はtrue、それ以外はfalse
	 */
	public function isValid($value) {
		$value = (string)$value;
		$this->_setValue($value);
		
		$len = strlen($value);
		if( $len < $this->_min ) {
			// 最小値以下
			$this->_error(self::TOO_SHORT);
			return false;
		}
		
		if( $this->_max !== null && $len > $this->_max ) {
			// 最大値以上
			$this->_error(self::TOO_LONG);
			return false;
		}
		
		if( ! preg_match(self::PATTERN, $value) ) {
			// 文字校正不正
			$this->_error(self::INVALID);
			return false;
		}
		
		return true;
	}
}