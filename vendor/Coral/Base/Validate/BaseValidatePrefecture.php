<?php
namespace Coral\Base\Validate;

require_once 'Zend/Validate/Abstract.php';

/**
 * 都道府県名称の妥当性を検証するバリデータ
 */
class BaseValidatePrefecture extends Zend_Validate_Abstract {
	/**
	 * エラー定数：データ不正
	 */
	const INVALID = 'prefectureInvalid';
	
	/**
	 * エラー定数とメッセージテンプレートのマッピング
	 * @var array
	 */
	protected $_messageTemplates = array(
		self::INVALID => "'%value%' は都道府県の名称ではありません"
	);
	
	/**
	 * 妥当な都道府県名のリスト
	 * @var array
	 */
	protected $_prefectureNames = array(
		'北海道',
		'青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
		'茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
		'新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県',
		'三重県', '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県',
		'鳥取県', '島根県', '岡山県', '広島県', '山口県',
		'徳島県', '香川県', '愛媛県', '高知県',
		'福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県',
		'沖縄県'
	);
	
	/**
	 * オーバーライド。指定の値に対して検証を行う
	 * @param mixed $value 検証対象のデータ
	 * @return boolean 検証に成功した場合はtrue、それ以外はfalse
	 */
	public function isValid($value) {
		$this->_setValue($value);
		
		if( ! in_array( (string)$value, $this->_prefectureNames ) ) {
			// 妥当性リストに含まれない場合は一律INVALID
			$this->_error(self::INVALID);
			return false;
		}
		
		return true;
	}
}
