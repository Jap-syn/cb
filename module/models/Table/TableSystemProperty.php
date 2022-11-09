<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * システムプロパティのモデルクラス
 */
class TableSystemProperty
{
    /** モジュール定数：システム共通設定 @var string */
    const DEFAULT_MODULE = '[DEFAULT]';

    /** カテゴリ定数：仮想カテゴリ - カテゴリなし @var string */
    const EMPTY_CATEGORY = '__default__';

    /** カテゴリ定数：認証 @var string */
    const FIX_CATEGORY_AUTH = 'auth';

    /** カテゴリ定数：税設定 @var string */
    const FIX_CATEGORY_TAXCONF = 'taxconf';

    /** カテゴリ定数：カスタムCSS @var string */
    const FIX_CATEGORY_CUSTOM_CSS = 'css';

	/** カテゴリ定数：JNB関連システム設定 @var string */
	const FIX_CATEGORY_JNBCONF = 'jnbconf';

    /** カテゴリ定数：SMBCバーチャル口座関連システム設定 @var string */
    const FIX_CATEGORY_SMBCPACONF = 'smbcpaconf';

	/** カテゴリ定数：パスワード有効期限制約設定 @var string */
	const FIX_CATEGORY_PSW_VALIDITY = 'psw-validity-days';

	/** カテゴリ定数：パスワード期限切れアラート設定 @var string */
	const FIX_CATEGORY_PSW_LIMIT_ALERT = 'psw-limit-alert-days';

	/** カテゴリ定数：クライアントレベル認証試行制限設定 @var string */
	const FIX_CATEGORY_ATM_JUDGE_CLIENT = 'attempt-judge-by-client';

	/** カテゴリ定数：IDレベル認証試行制限設定 @var string */
	const FIX_CATEGORY_ATM_JUDGE_ID = 'attempt-judge-by-id';

	/** カテゴリプレフィックス定数：追加イメージ @var string */
	const CATEGORY_PREFIX_IMAGE_EX = 'imageex';

    /** 定義済みプロパティ名（カテゴリ：認証）：認証用ハッシュSALT @var string */
    const FIX_NAME_HASH_SALT = 'hash_salt';

    /** 定義済みプロパティ名（カテゴリ：税設定）：デフォルト設定 @var string */
    const TAXCONF_DEFAULT_KEY = '[DEFAULT]';


    protected $_name = 'T_SystemProperty';
    protected $_primary = array('PropId');
    protected $_adapter = null;

    protected $_filterCategory = null;

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
     * 現在このインスタンスに適用されているフィルタカテゴリを取得する
     *
     * @return string
     */
    public function getFilterCategory() {
        return $this->_filterCategory;
    }

    /**
     * 指定のカテゴリを現在のインスタンスにフィルタとして適用する。
     * フィルタが適用されているインスタンスは検索時に無条件にフィルタに一致する
     * カテゴリのデータのみが取得される
     *
     * @param string $category フィルタとして適用するカテゴリ
     * @return TableSystemProperty このインスタンス
     */
    public function setFilterCategory($category) {
        $category = trim((string)$category);
        if($this->categoryExists($category) || $category == self::EMPTY_CATEGORY) {
            $this->_filterCategory = $category;
        } else {
            $this->_filterCategory = null;
        }
        return $this;
    }

    /**
     * このインスタンスが現在フィルタ適用されているかを判断する
     *
     * @return boolean フィルタが適用されている場合はtrue、それ以外はfalse
     */
    public function isFiltered() {
        return $this->_filterCategory !== null;
    }

    /**
     * 指定のカテゴリがDB上に存在するかを確認する
     *
     * @param string $category 存在確認をするカテゴリ
     * @return 指定のカテゴリがDBに登録済みの場合はtrue、それ以外はfalse
     */
    public function categoryExists($category)
    {
        $category = trim((string)$category);

        $prm = array();
        $sql = " SELECT COUNT(1) AS cnt FROM T_SystemProperty ";
        if($category == self::EMPTY_CATEGORY) {
            $sql .= " WHERE Category IS NULL ";
        } else {
            $sql .= " WHERE Category = :Category ";
            $prm += array(':Category' => $category);
        }

        $stm = $this->_adapter->query($sql);

        $row = $stm->execute($prm)->current();
        return (intval($row['cnt']) > 0) ? true : false;
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
     * 指定のプロパティ名で登録されているデータが存在するかを確認する
     *
     * @param string $name プロパティ名
     * @param null | string $category カテゴリ。フィルタ適用中の場合はこの指定は無効となる。
     *                                省略時は、フィルタ未適用なら「カテゴリなし」と見なされる
     * @return 指定のプロパティ名・カテゴリで登録されているデータがある場合はtrue、それ以外はfalse
     */
    public function propNameExists($name, $category = null) {
        return (!is_null($this->getValue(self::DEFAULT_MODULE, $category, $name))) ? true : false;
    }

// Del By Takemasa(NDC) 20141209 Stt 不必要化につきコメントアウト化
//     /**
//      * プロパティ名による値取得向けの検索条件を構築する
//      *
//      * @access protected
//      * @param string $name プロパティ名
//      * @param null | string $category カテゴリ。フィルタ適用中の場合はこの指定は無効となる。
//      *                                省略時は、フィルタ未適用なら「カテゴリなし」と見なされる
//      * @return string
//      */
//     protected function buildWheres($name, $category = null) {
//         $db = $this->_db;
//
//         $name = trim((string)$name);
//         if(!strlen($name)) {
//             // プロパティ名の指定は必須
//             throw new Exception('name must specify');
//         }
//
//         // カテゴリを整形する
//         $category = $this->fixCategoryForSpecify($category);
//         // 整形後にnullならフィルタなし＋カテゴリ指定なし状態なので「カテゴリなし」指定と見なす
//         if($category === null) $category = self::EMPTY_CATEGORY;
//
//         $wheres = array($db->quoteInto('Name = ?', $name));
//         if($category == self::EMPTY_CATEGORY) {
//             $wheres[] = 'Category IS NULL';
//         } else {
//             $wheres[] = $db->quoteInto('Category = ?', $category);
//         }
//
//         // AND結合して構築
//         return join(' AND ', $wheres);
//     }
// Del By Takemasa(NDC) 20141209 End 不必要化につきコメントアウト化

    /**
     * 検索処理向けにカテゴリを整形する
     *
     * @param null | string $category カテゴリ。フィルタ適用中の場合はこの指定は無効となる
     * @return string | null $categoryを整形したカテゴリ
     */
    protected function fixCategoryForSpecify($category = null) {
        if($this->isFiltered()) {
            // フィルタ適用中ならそのフィルタに置き換え
            $category = $this->getFilterCategory();
        } else if($category !== null) {
            // null以外は空文字に整備
            $category = trim((string)$category);
        }
        return $category;
    }

// Del By Takemasa(NDC) 20141209 Stt 未使用故コメントアウト化
//     /**
//      * 現在登録済みのデータで使用されているすべてのカテゴリを取得する
//      *
//      * @return array
//      */
//     public function getAllCategories() {
//         $q = sprintf(
//                      'SELECT DISTINCT Category FROM %s',
//                      $this->_db->quoteIdentifier($this->_name) );
//         $results = array(self::EMPTY_CATEGORY);
//         foreach($this->_db->fetchAll($q) as $row) {
//             $results[] = $row->Category;
//         }
//         return $results;
//     }
//
//     /**
//      * 現在テーブルに登録されているすべてのデータをカテゴリ別に集約した
//      * 連想配列としてエクスポートする
//      *
//      * @param null | string $category エクスポート対象のカテゴリを絞り込む場合に指定する
//      * @return array
//      */
//     public function export($category = null) {
//         $category = $this->fixCategoryForSpecify($category);
//
//         if($category == self::EMPTY_CATEGORY) {
//             $rows = $this->fetchAll('Category IS NULL');
//         } else {
//             $rows = $category === null ?
//                 // カテゴリ未指定時は全件fetch
//                 $this->fetchAll() :
//                 // カテゴリによる絞り込み
//                 $this->fetchAll($this->_db->quoteInto('Category = ?', $category));
//         }
//         $results = array();
//         foreach($rows as $row) {
//             $cat = nvl($row->Category);
//             if(!strlen($cat)) {
//                 $cat = self::EMPTY_CATEGORY;
//             }
//             if(!isset($results[$cat]) || !is_array($results[$cat])) {
//                 $results[$cat] = array();
//             }
//             $results[$cat][] = $row->toArray();
//         }
//         return $results;
//     }
// Del By Takemasa(NDC) 20141209 End 未使用故コメントアウト化

    // ---------------------------------------------------------------- カテゴリ「認証」固有メソッド
    /**
     * パスワード向けのハッシュSALTを取得する
     *
     * @return string
     */
    public function getHashSalt() {
        return $this->getValue(self::DEFAULT_MODULE, self::FIX_CATEGORY_AUTH, self::FIX_NAME_HASH_SALT );
    }

    // ---------------------------------------------------------------- カテゴリ「税設定」固有メソッド
	/**
	 * 印紙代適用金額関連の設定データを取得する。
	 * データは日付をキーとし、その日付以降の印紙代適用金額を値に持つ。
	 * ただし2014年3月31日以前に適用するデフォルト値のキーは'[DEFAULT]'となる
	 *
	 * @return array
	 */
	public function getStampFeeSettings() {
		$result = array();
		foreach($this->getTaxConfs() as $name => $data) {
			$result[$name] = $data['th_price'];
		}
		return $result;
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
        $this->initTaxConfs();
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
	 * カテゴリ'taxconf'に、2014年9月11日時点で確定済みの初期値を投入する
	 * ただしカテゴリにデータが登録済みの場合にはなにもしない
	 *
	 * @access protected
	 */
	protected function initTaxConfs() {
// zzz ↓システムDB設定は初期投入すべきものであるハズ(20150202_1440)
// 		if($this->categoryExists(self::FIX_CATEGORY_TAXCONF)) {
// 			return;
// 		}
// 		$defaults = array(
// 			self::TAXCONF_DEFAULT_KEY => array(
// 								 'value' => '5:31500',
// 								 'desc' => '2014年3月31日以前の消費税率と印紙代適用金額'
// 								),
// 			'2014-04-01' => array(
// 								 'value' => '8:54000',
// 								 'desc' => '2014年4月1日以降の消費税率と印紙代適用金額'
// 								)
// 		);
// 		foreach($defaults as $name => $value) {
// 			$data = array(
// 				'Category' => self::FIX_CATEGORY_TAXCONF,
// 				'Name' => $name,
// 				'PropValue' => $value['value'],
// 				'Description' => $value['desc']
// 			);
// 			$this->createRow($data)->save();
// 		}
// zzz ↑システムDB設定は初期投入すべきものであるハズ
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

    /**
     * 指定日付における税込率を取得する
     *
     * @param string $date 判断する日付
     * @param float $rate 税抜き率
     * @param int $digits 小数点以下有効桁数
     * @param int $tax_type 端数処理区分（1:切捨て、2:切り上げ、3:四捨五入）
     * @return float $date時点の税込率
     */
    public function getIncludeTaxRate($date, $rate, $digits = 5, $tax_type = 1) {
        // 税率取得
        $tax_rate = $this->getTaxRateAt($date);

        // 税率は整数で設定されているため倍率に変換
        $tax_rate = 1 + ($tax_rate / 100);

        // 有効桁数計算用
        $calcDigits = pow(10, $digits);

        // 端数処理の判定
        $includeTaxRate = 0;
        switch ($tax_type) {
            case 1:
                // 切捨て
                $includeTaxRate = floor($rate * $tax_rate * $calcDigits) / $calcDigits;
                break;
            case 2:
                // 切り上げ
                $includeTaxRate = ceil($rate * $tax_rate * $calcDigits) / $calcDigits;
                break;
            case 3:
                // 四捨五入
                $includeTaxRate = round($rate * $tax_rate * $calcDigits) / $calcDigits;
                break;
            default:
                // その他→切捨て
                $includeTaxRate = floor($rate * $tax_rate * $calcDigits) / $calcDigits;
                break;
        }

        return $includeTaxRate;
    }

    // ---------------------------------------------------------------- カテゴリ「カスタムCSS」固有メソッド
    /**
     * OEM先事業者向けのカスタムCSSルールを取得する。
     * 指定OEM ID用のルールが未定義の場合は長さ0の文字列を返す
     *
     * @param int $oemId OEM ID
     * @return string CSSルール
     */
    public function getMemberRulesByOemId($oemId) {
        $oemId = (int)$oemId;
        $name = sprintf('OemId%d', $oemId);
        return nvl($this->getValue( self::DEFAULT_MODULE, self::FIX_CATEGORY_CUSTOM_CSS , $name));
    }

    // ---------------------------------------------------------------- カテゴリ「追加イメージ」固有メソッド

    /**
     * 指定OEM先向けに設定されている、指定タイプの追加イメージを取得する
     * 指定OEM ID用に指定タイプの画像が設定されていない場合はnullを返す
     *
     * @param int $oemId OEM ID
     * @param string $imageType 追加イメージタイプ
     * @return mixed 追加イメージデータまたはnull
     */
    public function getExtraImageByOemId($oemId, $imageType) {
        $oemId = (int)$oemId;
        $name = sprintf('OemId%d', $oemId);
        $cat = sprintf('%s:%s', self::CATEGORY_PREFIX_IMAGE_EX, strtolower($imageType));
        $result = $this->getValue(self::DEFAULT_MODULE, $cat, $name);

        return strlen($result) ? base64_decode($result) : null;
    }

    /**
     * システムプロパティデータを取得する
     *
     * @param int $propId プロパティID
     * @return ResultInterface
     */
    public function find($propId)
    {
        $sql = " SELECT * FROM T_SystemProperty WHERE PropId = :PropId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PropId' => $propId,
        );

        return $stm->execute($prm);
    }

    /**
     * 新しいレコードをインサートする。
     *
     * @param array $data インサートする連想配列
     * @return プライマリキーのバリュー
     */
    public function saveNew($data)
    {
        $sql  = " INSERT INTO T_SystemProperty (Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :Module ";
        $sql .= " , :Category ";
        $sql .= " , :Name ";
        $sql .= " , :PropValue ";
        $sql .= " , :Description ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Module' => $data['Module'],
                ':Category' => $data['Category'],
                ':Name' => $data['Name'],
                ':PropValue' => $data['PropValue'],
                ':Description' => $data['Description'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param @param int $propId プロパティID
     * @return ResultInterface
     */
    public function saveUpdate($data, $propId)
    {
        $row = $this->find($propId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_SystemProperty ";
        $sql .= " SET ";
        $sql .= "     Module = :Module ";
        $sql .= " ,   Category = :Category ";
        $sql .= " ,   Name = :Name ";
        $sql .= " ,   PropValue = :PropValue ";
        $sql .= " ,   Description = :Description ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE PropId = :PropId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PropId' => $propId,
                ':Module' => $row['Module'],
                ':Category' => $row['Category'],
                ':Name' => $row['Name'],
                ':PropValue' => $row['PropValue'],
                ':Description' => $row['Description'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 同じ税率(同じテーブル行値)か？
     *
     * @param string $stt 'yyyy-MM-dd'書式で通知
     * @param string $end 'yyyy-MM-dd'書式で通知
     * @param int $taxRate 税率 ※trueが戻るとき有効
     * @return boolean true:同じ税率(同じテーブル行値)／false:異なる税率(異なるテーブル行値)
     */
    public function isSameTaxRate($stt, $end, &$taxRate) {
        $taxRate = 0;   // 戻り引数初期化

        $sql = " SELECT (CASE WHEN Name = '[DEFAULT]' THEN '1970-01-01' ELSE Name END) AS Name, PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'taxconf' ORDER BY Name DESC ";
        $aryTax = ResultInterfaceToArray($this->_adapter->query($sql)->execute(null));

        $aryTaxCount = 0;
        if(!empty($aryTax)) {
            $aryTaxCount = count($aryTax);
        }
        // $sttのインデックス取得
        $idxStt = 0;
        for ($i=0; $i<$aryTaxCount; $i++) {
            if ($stt >= $aryTax[$i]['Name']) {
                $idxStt = $i;
                break;
            }
        }

        // $endのインデックス取得
        $idxEnd = 0;
        for ($i=0; $i<$aryTaxCount; $i++) {
            if ($end >= $aryTax[$i]['Name']) {
                $idxEnd = $i;
                break;
            }
        }

        // 異なる税率時はfalseを戻す
        if ($idxStt != $idxEnd) {
            return false;
        }

        // 消費税率を戻り引数$taxRateへセット＆trueを戻す
        $propValue = explode(':', $aryTax[$idxStt]['PropValue']);
        $taxRate = (int)$propValue[0];
        return true;
    }
}
