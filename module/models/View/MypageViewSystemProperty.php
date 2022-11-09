<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewSystemProperty
{
    /** モジュール定数：システム共通設定 @var string */
    const DEFAULT_MODULE = '[DEFAULT]';

    /** カテゴリ定数：認証 @var string */
    const FIX_CATEGORY_AUTH = 'auth';

    /** 定義済みプロパティ名（カテゴリ：認証）：認証用ハッシュSALT @var string */
    const FIX_NAME_HASH_SALT = 'hash_salt';

    /** 定義済みプロパティ名（カテゴリ：税設定）：デフォルト設定 @var string */
    const TAXCONF_DEFAULT_KEY = '[DEFAULT]';

	protected $_name = 'T_SystemProperty';
	protected $_primary = 'PropId';
	protected $_adapter = null;

	/**
	 * コンストラクタ
	 *
	 * @param Adapter $adapter アダプタ
	 */
	public function __construct(Adapter $adapter)
	{
	    $this->_adapter = $adapter;
	}

	/**
	 * システムプロパティデータを取得する
	 *
	 * @param int $propId
	 * @return ResultInterface
	 */
	public function find($propId)
	{
	    $sql  = " SELECT * FROM T_SystemProperty WHERE PropId = :PropId ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':PropId' => $propId,
	    );

	    return $stm->execute($prm);
	}

	/**
	 * パスワード向けのハッシュSALTを取得する
	 *
	 * @return string
	 */
	public function getHashSalt() {
	    return $this->getValue(self::DEFAULT_MODULE, self::FIX_CATEGORY_AUTH, self::FIX_NAME_HASH_SALT );
	}

	/**
	 * 指定のプロパティ名で設定されているプロパティ値を取得する
	 * @param string $module モジュールy名
	 * @param string $category プロパティ名をグルーピングするための任意カテゴリー名
	 * @param string $name プロパティ名
	 * @return mixed 指定のプロパティ名・カテゴリで登録されている値。プロパティ名が存在しない場合はnullを返すが、
	 *               DB上の値がNULLの場合は空の文字列が返る
	 */
	public function getValue($module, $category, $name)
	{
	    $prm = array();
	    $sql = " SELECT PropValue FROM T_SystemProperty WHERE 1 = 1 ";
	    $sql .= " AND Module    = :Module ";
	    $sql .= " AND Category  = :Category ";
	    $sql .= " AND Name      = :Name ";

	    // パラメーターの設定
	    $prm += array(':Module'   => $module);
	    $prm += array(':Category' => $category);
	    $prm += array(':Name'     => $name);

	    $stm = $this->_adapter->query($sql);

	    $ri = $stm->execute($prm);

	    if (!($ri->count() > 0)) return null;

	    return nvl($ri->current()['PropValue']);
	}

	/**
	 * 消費税率関連の設定データを取得する。
	 * データは日付をキーとし、その日付以降に適用される税率（パーセント）を値に持つ。
	 * ただし2014年3月31日以前に適用するデフォルト値のキーは'[DEFAULT]'となる
	 *
	 * @return array
	 */
	public function getTaxRateSettings() {
	    $result = array();
	    foreach($this->getTaxConfs() as $name => $data) {
	        $result[$name] = $data['tax_rate'];
	    }
	    return $result;
	}

	/**
	 * カテゴリ'taxconf'の設定からしきい日付に関連付けた印紙代適用金額と消費税率のデータを展開する
	 *
	 * @access protected
	 * @return array
	 */
	protected function getTaxConfs() {
//	    $this->initTaxConfs();
	    $result = array(
	            self::TAXCONF_DEFAULT_KEY => null
	    );

	    $sql = " SELECT Name, PropValue FROM T_SystemProperty WHERE Category = 'taxconf' ORDER BY Name ASC ";
	    $ri = $this->_adapter->query($sql)->execute(null);
	    foreach($ri as $row) {
	        list($tax_rate, $th_price) = explode(':', $row['PropValue']);
	        $result[$row['Name']] = array(
	                'tax_rate' => (float)$tax_rate,
	                'th_price' => (int)$th_price
	        );
	    }

	    return $result;
	}

	/**
	 * 指定日付における消費税率を取得する
	 *
	 * @param string $date 判断する日付
	 * @return float $date時点の消費税率
	 */
	public function getTaxRateAt($date) {
	    if (!IsDate($date)) {
	        throw new \Exception('invalid date format specified');
	    }
	    $dateValue = new \DateTime($date);
	    $date = $dateValue->format('Y-m-d');

	    $list = $this->getTaxRateSettings();
	    $new_list = array();
	    $default = null;

	    $keys = array_keys($list);
	    sort($keys);
	    $keys = array_reverse($keys);

	    foreach($keys as $key) {
	        $value = $list[$key];
	        if($key == self::TAXCONF_DEFAULT_KEY) {
	            $default = $value;
	        } else
	            if(IsDate($key)) {
	                $dt =new \DateTime($key);
	                $key = $dt->format('Y-m-d');
	                if($date >= $key) return $value;
	            }
	    }
	    return $default;
	}

	/**
	 * 指定日付における税込金額を取得する
	 *
	 * @param string $date 判断する日付
	 * @param int $amount 税抜き金額
	 * @param int $tax_type 端数処理区分（1:切捨て、2:切り上げ、3:四捨五入）
	 * @return int $date時点の税込金額
	 */
	public function getIncludeTaxAmount($date, $amount, $tax_type = 1) {
	    // 税率取得
	    $tax_rate = $this->getTaxRateAt($date);

	    // 税率は整数で設定されているため倍率に変換
	    $tax_rate = 1 + ($tax_rate / 100);

	    // 端数処理の判定
	    $includeTaxAmount = 0;
	    switch ($tax_type) {
	        case 1:
	            // 切捨て
	            $includeTaxAmount = floor($amount * $tax_rate);
	            break;
	        case 2:
	            // 切り上げ
	            $includeTaxAmount = ceil($amount * $tax_rate);
	            break;
	        case 3:
	            // 四捨五入
	            $includeTaxAmount = round($amount * $tax_rate);
	            break;
	        default:
	            // その他→切捨て
	            $includeTaxAmount = floor($amount * $tax_rate);
	            break;
	    }

	    return $includeTaxAmount;
	}
}
