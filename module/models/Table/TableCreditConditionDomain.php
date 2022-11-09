<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use \models\Logic\LogicNormalizer;
use models\Logic\Validation\LogicValidationCreditCondition;
use models\Logic\Validation\LogicValidationConfigBuilder;

/**
 * T_CreditConditionテーブルへのアダプタ
 */
class TableCreditConditionDomain
{
    const DUPLICATED_ERROR_MESSAGE = '重複する与信条件がすでに登録されています。条件文字列、コメント、ポイントのいずれかを変更してください';

	protected $_name = 'T_CreditConditionDomain';
	protected $_primary = array('Seq');
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
	 * すべての与信条件データを取得する
	 *
	 * @param bool $asc 昇順ソートの場合はtrueを指定。デフォルトはfalse（＝降順ソート）
	 * @return ResultInterface
	 */
	public function getAll($asc = false)
	{
        $sql = " SELECT * FROM T_CreditConditionDomain ORDER BY Seq " . ($asc ? "asc" : "desc");
        return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 指定条件（AND）の与信条件データを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @return ResultInterface
	 */
	public function findCreditCondition($conditionArray)
	{
        $prm = array();
        $sql  = " SELECT * FROM T_CreditConditionDomain WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }
        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
	}

	/**
	 * 指定条件（AND）の与信条件データの件数を取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @return int 件数
	 */
	public function findCreditConditionCnt($conditionArray)
	{
        $prm = array();
        $sql  = " SELECT COUNT(*) AS Cnt FROM T_CreditConditionDomain WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            if ($value == null){
                $sql .= (" AND " . $key . " IS NULL");
            } else {
                $sql .= (" AND " . $key . " = :" . $key);
                $prm += array(':' . $key => $value);
            }
        }

        $stm = $this->_adapter->query($sql);

        return (int)$stm->execute($prm)->current()['Cnt'];
	}

	/**
	 * 指定条件（AND）の与信条件データの件数を取得する。　自動与信用
	 *
	 * @param string $address 与信を行う住所
	 * @return ResultInterface
	 */
	public function findCreditConditionCntForJudge($address)
	{
        $sql = "SELECT Class, COUNT(*) AS Cnt FROM T_CreditConditionDomain WHERE Category = 1 AND ValidFlg = 1 AND Cstring = :Cstring GROUP BY Class ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Cstring' => $address,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定条件（AND）の与信条件データ（一行目）を取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @return ResultInterface
	 */
	public function findCreditCondition2($conditionArray)
	{
        return $this->findCreditCondition($conditionArray);
	}

	/**
	 * 指定ドメインの後方一致検索
	 *
	 * @param string $domain ドメイン名
	 * @return boolean true:一致 false:不一致
	 */
	public function findMailDomainRearAgree($domain)
	{
        $query = " SELECT COUNT(Seq) AS Cnt FROM T_CreditConditionDomain WHERE ValidFlg = 1 AND Category = 4 AND Cstring like :domain ";
        return ((int)$this->_adapter->query($query)->execute(array(':domain' => '%' . $domain))->current()['Cnt'] > 0) ? true : false;
	}

	/**
	 * 指定ドメインの配列を取得する。
	 *
	 * @return ResultInterface
	 */
	public function getMailDomainNames()
	{
        $query = " SELECT Cstring FROM T_CreditConditionDomain WHERE ValidFlg = 1 AND Category = 4 ";
        return $this->_adapter->query($query)->execute(null);
	}

	/**
	 * 住所配列を取得する。
	 *
	 * @return ResultInterface
	 */
	public function getUnitingAddressCate1()
	{
        $query = " SELECT Cstring FROM T_CreditConditionDomain WHERE ValidFlg = 1 AND Category = 1 AND OrderSeq = -1 ";
        return $this->_adapter->query($query)->execute(null);
	}

	/**
	 * 住所配列を取得する。（与信ポイント付き）
	 *
	 * @return ResultInterface
	 */
	public function getUnitingAddressCate1WithPoint()
	{
        $query = " SELECT Cstring, Point FROM T_CreditConditionDomain WHERE ValidFlg = 1 AND Category = 1 AND OrderSeq = -1 ";
        return $this->_adapter->query($query)->execute(null);
	}

	/**
	 * 優良住所配列を取得する。
	 *
	 * @return ResultInterface
	 */
	public function getUnitingAddressCate6()
	{
        $query = " SELECT Cstring FROM T_CreditConditionDomain WHERE ValidFlg = 1 AND Category = 6 AND OrderSeq = -1 ";
        return $this->_adapter->query($query)->execute(null);
	}

	/**
	 * 指定商品名の部分一致検索
	 *
	 * @param string $itemName 商品名
	 * @return boolean true:一致 false:不一致
	 */
	public function findItemNamePartialAgree($itemName)
	{
        $query = " SELECT COUNT(Seq) AS Cnt FROM T_CreditConditionDomain WHERE ValidFlg = 1 AND Category = 3 AND Cstring like :itemName ";
        return ((int)$this->_adapter->query($query)->execute(array(':itemName' => '%' . $itemName . '%'))->current()['Cnt'] > 0) ? true : false;
	}

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO T_CreditConditionDomain (OrderSeq, Category, Class, Cstring, CstringHash, RegistDate, ValidFlg, Point, RegCstring, Comment, RegCstringHash, ComboHash, CreditCriterionId, JintecManualReqFlg, EnterpriseId, SearchPattern, AddConditionCount, RegistId, UpdateDate, UpdateId) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :Category ";
        $sql .= " , :Class ";
        $sql .= " , :Cstring ";
        $sql .= " , :CstringHash ";
        $sql .= " , :RegistDate ";
        $sql .= " , :ValidFlg ";
        $sql .= " , :Point ";
        $sql .= " , :RegCstring ";
        $sql .= " , :Comment ";
        $sql .= " , :RegCstringHash ";
        $sql .= " , :ComboHash ";
        $sql .= " , :CreditCriterionId ";
        $sql .= " , :JintecManualReqFlg ";
        $sql .= " , :EnterpriseId";
        $sql .= " , :SearchPattern";
        $sql .= " , :AddConditionCount";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':Category' => $data['Category'],
                ':Class' => $data['Class'],
                ':Cstring' => $data['Cstring'],
                ':CstringHash' => $data['CstringHash'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
                ':Point' => $data['Point'],
                ':RegCstring' => $data['RegCstring'],
                ':Comment' => $data['Comment'],
                ':RegCstringHash' => $data['RegCstringHash'],
                ':ComboHash' => $data['ComboHash'],
                ':CreditCriterionId' => $data['CreditCriterionId'],
                ':JintecManualReqFlg' => isset($data['JintecManualReqFlg']) ? $data['JintecManualReqFlg'] : 0,
                ':EnterpriseId' => intval($data['EnterpriseId']) > 0 ? $data['EnterpriseId'] : null,
                ':SearchPattern' => isset($data['SearchPattern']) ? $data['SearchPattern'] : 0,
                ':AddConditionCount' => isset($data['AddConditionCount']) ? $data['AddConditionCount'] : 0,
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

	/**
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param int $seq 更新するSeq
	 */
	public function saveUpdate($data, $seq)
	{
        $sql = " SELECT * FROM T_CreditConditionDomain WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_CreditConditionDomain ";
        $sql .= " SET ";
        $sql .= "     Seq = :Seq ";
        $sql .= " ,   OrderSeq = :OrderSeq ";
        $sql .= " ,   Category = :Category ";
        $sql .= " ,   Class = :Class ";
        $sql .= " ,   Cstring = :Cstring ";
        $sql .= " ,   CstringHash = :CstringHash ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " ,   Point = :Point ";
        $sql .= " ,   RegCstring = :RegCstring ";
        $sql .= " ,   Comment = :Comment ";
        $sql .= " ,   RegCstringHash = :RegCstringHash ";
        $sql .= " ,   ComboHash = :ComboHash ";
        $sql .= " ,   CreditCriterionId = :CreditCriterionId ";
        $sql .= " ,   JintecManualReqFlg = :JintecManualReqFlg ";
        $sql .= " ,   EnterpriseId = :EnterpriseId ";
        $sql .= " ,   SearchPattern = :SearchPattern ";
        $sql .= " ,   AddConditionCount = :AddConditionCount ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";

        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':OrderSeq' => $row['OrderSeq'],
                ':Category' => $row['Category'],
                ':Class' => $row['Class'],
                ':Cstring' => $row['Cstring'],
                ':CstringHash' => $row['CstringHash'],
                ':RegistDate' => $row['RegistDate'],
                ':ValidFlg' => $row['ValidFlg'],
                ':Point' => $row['Point'],
                ':RegCstring' => $row['RegCstring'],
                ':Comment' => $row['Comment'],
                ':RegCstringHash' => $row['RegCstringHash'],
                ':ComboHash' => $row['ComboHash'],
                ':CreditCriterionId' => $row['CreditCriterionId'],
                ':JintecManualReqFlg' => $row['JintecManualReqFlg'],
                ':EnterpriseId' => intval($row['EnterpriseId']) > 0 ? $row['EnterpriseId'] : null,
                ':SearchPattern' => intval($row['SearchPattern']) > 0 ? $row['SearchPattern'] : 0,
                ':AddConditionCount' => intval($row['AddConditionCount']) > 0 ? $row['AddConditionCount'] : 0,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId']
        );
        return $stm->execute($prm);
	}

	 /**
     * 登録用連想配列の内容から結合ハッシュを生成する
     *
     * @param array $data 登録用連想配列
     * @return string 結合条件ハッシュ
     */
    public function createComboHash($data) {
        $fields = array($data['RegCstring'], $data['Comment'], $data['Point']);

        return md5(join('|', $fields));
    }

	 /**
     * 条件結合ハッシュを指定して、指定カテゴリ内での重複条件を取得する
     *
     * @param int $category 対象カテゴリ。有効な値を指定しなかった場合は例外が発生する
     * @param string $combo_hash 正規化条件文字列・コメント・スコアの結合から取得した、条件結合ハッシュ
     * @return ResultInterface $combo_hash $combo_hashと同一の結合ハッシュを持つすべてのデータ
     */
    public function getDuplicatedConditionsByComboHash($category, $combo_hash, $enterpriseId, $seq = null) {

        $prm = array();
        if ($seq === null) {
            $where = " ValidFlg = 1 AND Category = :Category AND ComboHash = :ComboHash ";
            $prm = array(
                    ':Category' => $category,
                    ':ComboHash' => $combo_hash,
            );
        }
        else {
            $where = " ValidFlg = 1 AND Category = :Category AND ComboHash = :ComboHash AND Seq <> :Seq ";
            $prm = array(
                    ':Category' => $category,
                    ':ComboHash' => $combo_hash,
                    ':Seq' => $seq,
            );
        }

        if ($enterpriseId == -1 || $enterpriseId == null) {
            $where .= " AND EnterpriseId IS NULL ";
        } else {
            $where .= " AND EnterpriseId = :EnterpriseId ";
            $prm[':EnterpriseId'] = $enterpriseId;
        }

        $sql = " SELECT * FROM T_CreditConditionDomain WHERE " . $where . " ORDER BY Seq DESC ";
        return $this->_adapter->query($sql)->execute($prm);
    }

    /**
     * 指定の登録向け連想配列と、カテゴリ・条件文字列・コメント・スコアが一致するデータを
     * 検索する。
     * 連想配列内でキー'Seq'に有効な値が格納されている場合は指定シーケンスを除外する動作となる
     *
     * @param array $data 登録向けの連想配列。重複検出に向けて、内部でfixDataArray()メソッドを経由する
     * @return ResultInterface $combo_hash $combo_hashと同一の結合ハッシュを持つすべてのデータ
     */
    public function findDuplicatedConditions($data, $enterpriseId) {
        $data = $this->fixDataArray($data);
        $cat = (int)$data['Category'];
        $hash = $data['ComboHash'];

        return isset($data['Seq']) ?
            $this->getDuplicatedConditionsByComboHash($cat, $hash, $enterpriseId, (int)$data['Seq']) :
            $this->getDuplicatedConditionsByComboHash($cat, $hash, $enterpriseId);
    }

	/*
	 * RegCstringがNULLの情報を取得する
	 *
	 * @param string $address 与信を行う住所
	 */
	public function getRegCstringIsNull()
	{
		throw new \Exception('this method call is invalid');
	}

	/**
	 * 指定条件にしたがって与信情報を取得する
	 *
	 * @return ResultInterface
	 */
	public function getCreditCondtionList($expressions, $enterpriseId)
	{
	    $prm = array();
	    $sql = " SELECT * FROM T_CreditConditionDomain WHERE ( OrderSeq = -1 ";

	    $sql2 = null;
	    $prm2 = array();

        // 加盟店ID
        if ($enterpriseId != -1) {
        $sql .= (" AND EnterpriseId = :EnterpriseId ");
        $sql2 .= (" AND EnterpriseId = :EnterpriseId ");
        $prm += array(':EnterpriseId' => $enterpriseId);
        $prm2 += array(':EnterpriseId' => $enterpriseId);
        } else {
        $sql .= (" AND EnterpriseId IS NULL ");
        $sql2 .= (" AND EnterpriseId IS NULL ");
        }

        foreach($expressions as $key => $value) {

            if(is_array($value)) {
                if(! count($value)) continue;
            } else {
                $value = trim(nvl($value, ''));
                if(! strlen($value)) continue;
            }

            switch($key) {
                case 'ValidFlg':    // 有効フラグ(0:無効/1:有効、のときのみ条件に加える)
                    if ($value == 0 || $value == 1) {
                        $sql .= (" AND ValidFlg = :ValidFlg ");
                        $sql2 .= (" AND ValidFlg = :ValidFlg ");
                        $prm += array(':ValidFlg' => $value);
                        $prm2 += array(':ValidFlg' => $value);
                    }
                    break;
                case 'Category':    // カテゴリ('0' はカテゴリ未指定)    if(! strlen($value)) continue;
                    if($value != 0) {
                        $sql .= (" AND Category = :Category ");
                        $sql2 .= (" AND Category = :Category ");
                        $prm += array(':Category' => $value);
                        $prm2 += array(':Category' => $value);
                    }
                    break;
                case 'Cstring':     // 条件文字列の部分一致検索
                    // 選択されたカテゴリに応じて正規化パターンを決定する
                    $map = array(
                            '1' => LogicNormalizer::FILTER_FOR_ADDRESS,
                            '2' => LogicNormalizer::FILTER_FOR_NAME,
                            '3' => LogicNormalizer::FILTER_FOR_ITEM_NAME,
                            '4' => LogicNormalizer::FILTER_FOR_MAIL,
                            '5' => LogicNormalizer::FILTER_FOR_ID,
                          //'6' => LogicNormalizer::FILTER_FOR_ADDRESS,
                          //'7' => LogicNormalizer::FILTER_FOR_ID,
                            '8' => LogicNormalizer::FILTER_FOR_TEL,
                            '9' => LogicNormalizer::FILTER_FOR_MONEY,
                    );
                    if($map[$expressions['Category']]) {
                        $normalizer = LogicNormalizer::create($map[$expressions['Category']]);
                        $sql .= (" AND " . $this->makeLikeExpression('RegCstring', $normalizer->normalize($value)));
                        $sql2 .= (" AND " . $this->makeLikeExpression('RegCstring', $normalizer->normalize($value)));
                    }
                    else {
                        // カテゴリ未指定時はすべてのパターンの正規化を適用してOR検索
                        $w = array();
                        $v_list = array();
                        foreach($map as $key => $const) {
                            $normalizer = LogicNormalizer::create($const);
                            $v = $normalizer->normalize($value);
                            if(strlen($v) && ! in_array(sprintf('[%s]', $v), $v_list)) {
                                $v_list[] = sprintf('[%s]', $v);
                            }
                        }
                        foreach($v_list as $v) {
                            $w[] = sprintf('(%s)', $this->makeLikeExpression('RegCstring', preg_replace('/[\[\]]/u', '', $v)));
                        }
                        $sql .= " AND ( " . join(" OR ", $w) . " ) ";
                        $sql2 .= " AND ( " . join(" OR ", $w) . " ) ";
                    }
                    break;
                case 'Comment':     // コメントの部分一致検索
                    $sql .= (" AND Comment LIKE :Comment ");
                    $prm += array(':Comment' => '%' . $value . '%');
                    break;
                case 'Point':       // スコア
                    $sql .= (" AND Point = :Point ");
                    $prm += array(':Point' => $value);
                    break;
            }
        }

        // 追加社内与信条件を検索
        $addsql = " SELECT IFNULL(GROUP_CONCAT(P_ConditionSeq), 0) AS AddSeq FROM T_AddCreditCondition WHERE 1 = 1 " . $sql2;
        $addseq = $this->_adapter->query($addsql)->execute($prm2)->current()['AddSeq'];

         $sql .=  " ) OR Seq IN ( $addseq ) ";

        $sql .= " ORDER BY Seq ";

        return $this->_adapter->query($sql)->execute($prm);
	}

 /**
     * 指定された注文・商品について部分一致あるいは完全一致により与信条件の検索を行う。
     *
     * @param int $orderCustomerSeq
     * @param int $category
     * @param boolean $isMatchesIn
     * @param int $orderItemId
     * @param string $conditionSeq
     * @return ResultInterface
     */
    public function judge($category, $isMatchesIn, $regtarget, $conditionSeq)
    {
        $matchWhere = "";

    	if ($isMatchesIn)
    	{
            // 部分一致
            // RegCstringに対してリテラルエスケープ＋ワイルドカードエスケープの下処理を追加
            $matchWhere = ":regtarget LIKE CONCAT('%%', REPLACE(REPLACE(TRIM(BOTH '\\'' FROM QUOTE(RegCstring)), '_', '\\_'), '%%', '\\%%'), '%%')";
    	}
    	else
    	{
    		// 完全一致の場合
            // Hashと比較
    	    $matchWhere = " RegCstringHash = MD5(:regtarget) ";
    	}

    	if ($conditionSeq != null) {
    	    $matchWhere .= " and Seq NOT IN ($conditionSeq) ";
    	}

    	$addWhere = sprintf("ValidFlg = 1 and Category = %s and OrderSeq = -1 and %s", $category, $matchWhere);

    	$query = "";
    		// 与信条件の取得
	    	$query = sprintf("
				SELECT
				    *
				FROM
				    T_CreditConditionDomain
				Where
				 %s
				",
		    	$addWhere
	    	);

        return $this->_adapter->query($query)->execute(array(':regtarget' => $regtarget));
    }


    /**
     * 指定された注文・商品について部分一致あるいは完全一致により与信条件の検索を行う。
     *
     * @param string $regtarget
     * @param date $date
     * @return int
     */
    public function judgeskip($regtarget, $date = NULL)
    {
        $matchWhere = "";

        // RegCstringに対してリテラルエスケープ＋ワイルドカードエスケープの下処理を追加
        $matchWhere = ":regtarget LIKE CONCAT('%%', REPLACE(REPLACE(TRIM(BOTH '\\'' FROM QUOTE(RegCstring)), '_', '\\_'), '%%', '\\%%'), '%%')";

        $addWhere = sprintf("ValidFlg = 1 and Category = 4 and OrderSeq = -1 and Point <= -2000 and %s", $matchWhere);

        $data = array(
                'regtarget'    => $regtarget,
        );

        if ($date != NULL){
            $addWhere .= " and UpdateDate >= :UpdateDate and AddConditionCount = 0 ";
            $data = array(
                    ':regtarget'    => $regtarget,
                    ':UpdateDate'   => $date,
            );
        }

        $query = "";
        // 与信条件の取得
        $query = sprintf("
				SELECT
				    count(*) as cnt
				FROM
				    T_CreditConditionDomain
				Where
				 %s
				",
        $addWhere
        );

        return $this->_adapter->query($query)->execute($data)->current()['cnt'];
    }
     /**
     * 永続化向けの連想配列に対し、新規インサート向けの初期値補完を適用する
     *
     * @param array $data 補完する連想配列
     * @param int $ent_id 事業者ID
     * @return array
     */
    public function fixDataArrayForNew(array $data) {
        return array_merge($data, array(
            'Seq' => -1,                    // dummy
            'OrderSeq' => -1,               // 手動設定データ
            'RegistDate' => date('Y-m-d'),  // 登録日
            'Class' => -1,                  // 手動設定データ
            'ValidFlg' => 1                 // 有効フラッグ
        ));
    }


	  /**
     * 永続化向けの連想配列に対し、正規化関連のカラムの値を補完更新する
     *
     * @param array $data 処理対象の連想配列
     * @return array
     */
    public function fixDataArray(array $data) {
        return $this->fixDataArrayOrg($data, true);
    }

	  /**
     * 永続化向けの連想配列に対し、正規化関連のカラムの値を補完更新する
     *
     * @param array $data 処理対象の連想配列
     * @return array
     */
    public function fixDataArrayOrg(array $data, $flg = true) {
        // 選択されたカテゴリに応じて正規化パターンを決定する
        $map = array(
                '1' => LogicNormalizer::FILTER_FOR_ADDRESS,
                '2' => LogicNormalizer::FILTER_FOR_NAME,
                '3' => LogicNormalizer::FILTER_FOR_ITEM_NAME,
                '4' => LogicNormalizer::FILTER_FOR_MAIL,
                '5' => LogicNormalizer::FILTER_FOR_ID,
                //'6' => Logic_Normalizer::FILTER_FOR_ADDRESS,
                //'7' => Logic_Normalizer::FILTER_FOR_ID,
                '8' => LogicNormalizer::FILTER_FOR_TEL,
                '9' => LogicNormalizer::FILTER_FOR_MONEY,
       );
        $key = $map[$data['Category']];

        // カテゴリが不正な場合はエラーとする
        if(empty($key)) throw new \Exception('invalid category specified');

        // 正規化実行
        $data['RegCstring'] =
            LogicNormalizer::create($key)->normalize($data['Cstring']);

		if($flg) {
	        // さらにRegCstringのハッシュ値を作成
	        $data['RegCstringHash'] = md5($data['RegCstring']);

	        // さらに、さらにRegCstring＋Comment＋Scoreのハッシュ値を作成
	        $data['ComboHash'] = $this->createComboHash($data);
		}
        return $data;
    }

    /**
     * カラムに一致するキーを持つ連想配列のデータを検証し
     * 結果を返す
     *
     * @param array $data 検証するデータを格納した連想配列
     * @return LogicValidationResult 検証結果
     */
    public function validate(array $data) {
        $validator = new LogicValidationCreditCondition();

        // 選択されたカテゴリに応じてCstringの検証ルールを構築
        $builder = LogicValidationConfigBuilder::create("Cstring", true, "条件文字列")
            ->addRule(array('StringLength', 1, 4000));  // requiredで4000文字MAXは共通

        switch($data['Category']) {
            case "4":       // メアド
                $builder
                    ->addRule('MailPart', "'%NAME%' はメールアドレスとして正しくありません");
                break;
            case "8":       // 電話番号
                $builder
                    ->addRule('Phone', "'%NAME%' は電話番号として正しくありません");
                break;
        }

        return $validator
            ->addConfig($builder)
            ->validate($data);
    }

	// 以下からcreekの各Abstractクラスを代用

	/**
     * カラム名に一致するキーを持つ連想配列のデータを
     * テーブルへ永続化する
     *
     * @param array $data 永続化するデータを格納した連想配列
     * @return ResultInterface 保存された行データ
     */
	public function saveFromArray(array $data) {

        // プライマリキー設定を初期化
        $pkeys = $this->_primary;
        if(! is_array($pkeys)) $primaries = array($pkeys);

        // 入力値からプライマリキー情報を抽出
        $primaries = array();
        foreach((array)$pkeys as $key) {
            if(isset($data[$key])) $primaries[] = $data[$key];
        }

        // プライマリキーが不完全なのでエラー
        $primariesCount = 0;
        if(!empty($primaries)) {
            $primariesCount = count($primaries);
        }
	    $pkeysCount = 0;
        if(!empty($pkeys)) {
            $pkeysCount = count($pkeys);
        }
        if($primariesCount != $pkeysCount) {
            throw new \Exception('invalid primary key(s)');
        }

        // プライマリキーに一致するデータの取得を試みる
        $pkval = $data['Seq'];
        $sql = " SELECT * FROM T_CreditConditionDomain WHERE Seq = :Seq ";
        $ri = $this->_adapter->query($sql)->execute(array(':Seq' => $pkval));

        if ($data['Category'] == 5) {
            $data['SearchPattern'] = 3;
        }

        if ($ri->count() > 0) {
            // UPDATE
            $this->saveUpdate($data, $pkval);
        }
        else {
            // INSERT
            // ユーザーIDの取得
            $pkval = $this->saveNew($data);
        }

        return $this->_adapter->query(" SELECT * FROM T_CreditConditionDomain WHERE Seq = :Seq ")->execute(array(':Seq' => $pkval));
	}

    /**
     * 部分一致検索用のLIKE句を構築する
     *
     * @param string $field カラム名
     * @param mixed $value 検索値
     * @return string
     */
    public function makeLikeExpression($field, $value) {
        return $field . " LIKE '%" . self::escapeWildcard($value) . "%' ";
    }

	/**
	 * MySQLでLIKEを発行できるよう入力文字列をエスケープする
	 * エスケープする内容は通常のZend_Db_Adapter_Abstract::quote()とは以下の点が異なる。
	 * ・ワイルドカード文字（%および_）もバックスラッシュエスケープする
	 * ・バックスラッシュ自体は通常の2重バックスラッシュではなく4重バックスラッシュにエスケープする
	 * ・（quoteではないので）前後に引用符は付加しない
	 * @param string $s
	 * @return string
	 */
    public static function escapeWildcard($s) {
 		// 事前にバックスラッシュを2重化してからaddcslashesを行う
 		return addcslashes(str_replace("\\", "\\\\", $s), "\000\r\n\\'\"\032%_");
    }
}
