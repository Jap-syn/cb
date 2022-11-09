<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Coral\Base\BaseGeneralUtils;

class TableOrderItems
{
	/**
	 * カラム'DataClass'に指定する、商品を示す定数
	 *
	 * @var int
	 */
	const DATA_CLASS_ITEM = 1;

	/**
	 * カラム'DataClass'に指定する、送料を示す定数
	 *
	 * @var int
	 */
	const DATA_CLASS_CARRIAGE = 2;

	/**
	 * カラム'DataClass'に指定する、手数料を示す定数
	 *
	 * @var int
	 */
	const DATA_CLASS_CHARGE = 3;

	/**
	 * カラム'DataClass'に指定する、外税を示す定数
	 *
	 * @var int
	 */
	const DATA_CLASS_TAXCLASS = 4;

	/**
	 * カラム'DeliDestId'に指定する、配送先未指定（＝商品以外）を示す定数
	 *
	 * @var int
	 */
	const DELI_DEST_ID_NA = -1;

	/**
	 * テーブル名
	 *
	 * @var string
	 */
	protected $_name = 'T_OrderItems';

	/**
	 * プライマリキーのカラム名
	 *
	 * @var string
	 */
	protected $_primary = array('OrderItemId');

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
	 * 新しいOrderItemsのレコード挿入する
	 *
	 * @param int $orderSeq 親のT_Orderのシーケンス番号
	 * @param int $delivId 関連するT_DeliveryDesitinationの配送先ID
	 * @param int $dataClass この行のデータクラス。省略時は商品（＝{@link Table_OrderItems::DATA_CLASS_ITEM}）になる
	 * @param array|null $data 新しい行に設定する初期データの連想配列。この配列に設定されたカラム'OrderSeq'、'DeliDestId'および'DataClass'のデータは採用されない
	 * @return プライマリキーのバリュー
	 */
	public function newRow($orderSeq, $delivId, $dataClass = 1, $data = array()) {
	    if( ! is_array($data) ) $data = array();

		$dataClass = ( $dataClass != self::DATA_CLASS_CARRIAGE && $dataClass != self::DATA_CLASS_CHARGE && $dataClass != self::DATA_CLASS_TAXCLASS ) ?
			self::DATA_CLASS_ITEM : $dataClass;

		// データクラスが商品以外の場合、配送先IDは-1にする
		if( $dataClass != self::DATA_CLASS_ITEM ) $delivId = -1;

		// 注文シーケンスと配送先IDが不正の場合は例外をスロー
		if( ((int)$orderSeq) < 1 ) {
			throw new \Exception('invalid order sequence');
		}
		if( ((int)$delivId) < 1 && ((int)$delivId) != -1 ) {
			throw new \Exception('invalid delivery id');
		}

		// 連想配列を整形
		// $data以外のパラメータを適用
		$data = array_merge( $data, array(
			'OrderSeq' => $orderSeq,
			'DeliDestId' => $delivId,
			'DataClass' => $dataClass,
			'Deli_ConfirmArrivalFlg' => 0,
		) );

		// Del By Takemasa(NDC) 20150727 Stt 呼出し元でSumMoneyを設定する
		//// 単価と数量が設定されていたら金額を設定
		//if( isset( $data['UnitPrice'] ) && isset( $data['ItemNum'] ) ) {
		//	$data['SumMoney'] = ((int)$data['UnitPrice']) * ((int)$data['ItemNum']);
		//}
		// Del By Takemasa(NDC) 20150727 End 呼出し元でSumMoneyを設定する

		// Mod By Takemasa(NDC) 20150105 Stt 関数saveNewを呼出すよう変更
		//// 新しいZend_Db_Table_Rowを作成
		//$result = $this->createRow( $data );
		$result = $this->saveNew( $data );
		// Mod By Takemasa(NDC) 20150105 End 関数saveNewを呼出すよう変更

		return $result;
	}

	/**
	 * 指定の注文シーケンスに関連付けられた注文商品を検索する
	 *
	 * @param int $orderSeq 注文シーケンス
	 * @param boolean $isOnlyValid 有効なものに限定か？(初期値true)
	 * @return ResultInterface
	 */
	public function findByOrderSeq($orderSeq, $isOnlyValid = true)
	{
        $sql  = " SELECT * FROM T_OrderItems WHERE OrderSeq = :OrderSeq ";
        if ($isOnlyValid) {
            // 有効なものに限る
            $sql .= " AND ValidFlg = 1 ";
        }
        $sql .= " ORDER BY DataClass, OrderItemId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定の親注文シーケンスに関連付けられた注文商品を検索する
	 * （取りまとめ注文対応）
	 *
	 * @param int $p_orderSeq 親注文シーケンス
	 * @param boolean $isOnlyValid 有効なものに限定か？(初期値true)
	 * @return ResultInterface
	 */
	public function findByP_OrderSeq($p_orderSeq, $isOnlyValid = true)
	{
	    $sql  = " SELECT ITM.* FROM T_OrderItems ITM INNER JOIN T_Order ORD ON ORD.OrderSeq = ITM.OrderSeq WHERE ORD.P_OrderSeq = :P_OrderSeq ";
	    if ($isOnlyValid) {
	        // 有効なものに限る
	        $sql .= " AND ITM.ValidFlg = 1 AND ORD.Cnl_Status = 0 ";
	    }
	    $sql .= " ORDER BY ITM.DataClass, ITM.OrderItemId ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':P_OrderSeq' => $p_orderSeq,
	    );

	    return $stm->execute($prm);
	}

	/**
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param int $orderItemId 更新するOrderItemId
	 */
	public function saveUpdate($data, $orderItemId)
	{
        $sql = " SELECT * FROM T_OrderItems WHERE OrderItemId = :OrderItemId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderItemId' => $orderItemId,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_OrderItems ";
        $sql .= " SET ";
        $sql .= "     OrderSeq = :OrderSeq ";
        $sql .= " ,   DeliDestId = :DeliDestId ";
        $sql .= " ,   ItemNameKj = :ItemNameKj ";
        $sql .= " ,   ItemNameKn = :ItemNameKn ";
        $sql .= " ,   UnitPrice = :UnitPrice ";
        $sql .= " ,   ItemNum = :ItemNum ";
        $sql .= " ,   SumMoney = :SumMoney ";
        $sql .= " ,   TaxRate = :TaxRate ";
        $sql .= " ,   TaxrateNotsetFlg = :TaxrateNotsetFlg ";
        $sql .= " ,   DataClass = :DataClass ";
        $sql .= " ,   Incre_Score = :Incre_Score ";
        $sql .= " ,   Incre_Note = :Incre_Note ";
        $sql .= " ,   Deli_JournalIncDate = :Deli_JournalIncDate ";
        $sql .= " ,   Deli_DeliveryMethod = :Deli_DeliveryMethod ";
        $sql .= " ,   Deli_JournalNumber = :Deli_JournalNumber ";
        $sql .= " ,   Deli_ShipDate = :Deli_ShipDate ";
        $sql .= " ,   Deli_ConfirmArrivalFlg = :Deli_ConfirmArrivalFlg ";
        $sql .= " ,   Deli_ConfirmArrivalDate = :Deli_ConfirmArrivalDate ";
        $sql .= " ,   Deli_ConfirmArrivalOpId = :Deli_ConfirmArrivalOpId ";
        $sql .= " ,   Deli_ConfirmNoArrivalReason = :Deli_ConfirmNoArrivalReason ";
        $sql .= " ,   CombinedTargetFlg = :CombinedTargetFlg ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE OrderItemId = :OrderItemId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderItemId' => $orderItemId,
                ':OrderSeq' => $row['OrderSeq'],
                ':DeliDestId' => $row['DeliDestId'],
                ':ItemNameKj' => $row['ItemNameKj'],
                ':ItemNameKn' => $row['ItemNameKn'],
                ':UnitPrice' => $row['UnitPrice'],
                ':ItemNum' => $row['ItemNum'],
                ':SumMoney' => $row['SumMoney'],
                ':TaxRate' => $row['TaxRate'],
                ':TaxrateNotsetFlg' => $row['TaxrateNotsetFlg'],
                ':DataClass' => $row['DataClass'],
                ':Incre_Score' => $row['Incre_Score'],
                ':Incre_Note' => $row['Incre_Note'],
                ':Deli_JournalIncDate' => $row['Deli_JournalIncDate'],
                ':Deli_DeliveryMethod' => $row['Deli_DeliveryMethod'],
                ':Deli_JournalNumber' => $row['Deli_JournalNumber'],
                ':Deli_ShipDate' => $row['Deli_ShipDate'],
                ':Deli_ConfirmArrivalFlg' => $row['Deli_ConfirmArrivalFlg'],
                ':Deli_ConfirmArrivalDate' => $row['Deli_ConfirmArrivalDate'],
                ':Deli_ConfirmArrivalOpId' => $row['Deli_ConfirmArrivalOpId'],
                ':Deli_ConfirmNoArrivalReason' => $row['Deli_ConfirmNoArrivalReason'],
                ':CombinedTargetFlg' => $row['CombinedTargetFlg'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
	}

	/**
	 * 配送方法・伝票番号の更新
	 *
	 * @param int $orderSeq 注文Seq
	 * @param int $deliveryMethod 配送方法
	 * @param string $journalNumber 伝票番号
	 * @param $userId 担当者
	 * @param $IncDateFlg 伝票入力日時の更新フラグ true:更新する
	 */
	public function updateJournal($orderSeq, $deliveryMethod, $journalNumber, $userId, $incDateUpdateFlg = false)
	{
        $sql  = " UPDATE T_OrderItems ";
        $sql .= " SET ";
        $sql .= "     Deli_DeliveryMethod = :Deli_DeliveryMethod ";
        $sql .= " ,   Deli_JournalNumber  = :Deli_JournalNumber ";
        $sql .= " ,   UpdateDate  = :UpdateDate ";
        $sql .= " ,   UpdateId    = :UpdateId ";
        if($incDateUpdateFlg) $sql .= ",     Deli_JournalIncDate = :Deli_JournalIncDate ";
        $sql .= " WHERE OrderSeq          = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Deli_DeliveryMethod' => $deliveryMethod,
                ':Deli_JournalNumber' => $journalNumber,
                ':OrderSeq' => $orderSeq,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $userId,
        );
        if($incDateUpdateFlg) $prm[':Deli_JournalIncDate'] = date('Y-m-d H:i:s');

        return $stm->execute($prm);
	}

	/**
	 * 送料の更新
	 *
	 * @param int $orderSeq 注文Seq
	 * @param int $deliveryFee 送料
	 * @param $userId 担当者
	 */
	public function updateDeliveryFee($orderSeq, $deliveryFee, $userId)
	{
        $sql  = " UPDATE T_OrderItems ";
        $sql .= " SET ";
        $sql .= "     UnitPrice   = :UnitPrice ";
        $sql .= " ,   SumMoney    = :SumMoney ";
        $sql .= " ,   UpdateDate  = :UpdateDate ";
        $sql .= " ,   UpdateId    = :UpdateId ";
        $sql .= " WHERE DataClass = 2 ";
        $sql .= " AND   OrderSeq  = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':UnitPrice' => $deliveryFee,
                ':SumMoney' => $deliveryFee,
                ':OrderSeq' => $orderSeq,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $userId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 手数料の更新
	 *
	 * @param int $orderSeq 注文Seq
	 * @param int $settlementFee 手数料
	 * @param $userId 担当者
	 */
	public function updateSettlementFee($orderSeq, $settlementFee, $userId)
	{
        $sql  = " UPDATE T_OrderItems ";
        $sql .= " SET ";
        $sql .= "     UnitPrice   = :UnitPrice ";
        $sql .= " ,   SumMoney    = :SumMoney ";
        $sql .= " ,   UpdateDate  = :UpdateDate ";
        $sql .= " ,   UpdateId    = :UpdateId ";
        $sql .= " WHERE DataClass = 3 ";
        $sql .= " AND   OrderSeq  = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':UnitPrice' => $settlementFee,
                ':SumMoney' => $settlementFee,
                ':OrderSeq' => $orderSeq,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $userId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 外税の更新
	 *
	 * @param int $orderSeq 注文Seq
	 * @param int $tax 外税
	 * @param $userId 担当者
	 */
	public function updateTax($orderSeq, $tax, $userId)
	{
        $sql  = " UPDATE T_OrderItems ";
        $sql .= " SET ";
        $sql .= "     UnitPrice   = :UnitPrice ";
        $sql .= " ,   SumMoney    = :SumMoney ";
        $sql .= " ,   UpdateDate  = :UpdateDate ";
        $sql .= " ,   UpdateId    = :UpdateId ";
        $sql .= " WHERE DataClass = 4 ";
        $sql .= " AND   OrderSeq  = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':UnitPrice' => $tax,
                ':SumMoney' => $tax,
                ':OrderSeq' => $orderSeq,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $userId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 商品名を一つだけ取得する。
	 *
	 * @param int 注文Seq
	 * @return string 商品名
	 */
	public function getOneItemName($orderSeq)
	{
        $sql = " SELECT ItemNameKj FROM T_OrderItems WHERE ValidFlg = 1 AND DataClass = 1 AND OrderSeq = :OrderSeq LIMIT 1 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
        );

        $ri = $stm->execute($prm);

        if (!($ri->count() > 0)) { return null; }

        return $ri->current()['ItemNameKj'];
	}

	/**
	 * 商品名を一つだけ取得する(以下の考慮あり版 20150909_1920)
	 * ・未キャンセルの注文に限る
	 * ・請求取りまとめに対応する
	 *
	 * @param int 注文Seq
	 * @return string 商品名
	 */
	public function getOneItemName2($orderSeq)
	{
        $sql  = " SELECT oi.ItemNameKj ";
        $sql .= " FROM   T_Order o ";
        $sql .= "        INNER JOIN T_OrderItems oi ON (oi.OrderSeq = o.OrderSeq) ";
        $sql .= " WHERE  o.Cnl_Status = 0 ";
        $sql .= " AND    o.ValidFlg = 1 ";
        $sql .= " AND    oi.ValidFlg = 1 ";
        $sql .= " AND    oi.DataClass = 1 ";
        $sql .= " AND    o.P_OrderSeq = :OrderSeq ";
        $sql .= " ORDER BY oi.OrderItemId ";
        $sql .= " LIMIT 1 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
        );

        $ri = $stm->execute($prm);

        if (!($ri->count() > 0)) { return null; }

        return $ri->current()['ItemNameKj'];
	}

	/**
	 * 伝票番号を検索する。
	 *
	 * @param mixed $journalNumber 伝票番号
	 * @param mixed $deliMethod 配送方法ID
	 * @param int $entId 事業者ID
	 * @return string 伝票番号
	 */
	public function getJournalNumber($journalNumber, $deliMethod, $entId)
	{
        $sql  = " SELECT s.Deli_JournalNumber ";
        $sql .= " FROM   T_OrderSummary s INNER JOIN T_Order o ON o.OrderSeq = s.OrderSeq ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    o.EnterpriseId = :EnterpriseId ";
        $sql .= " AND    s.Deli_JournalNumber = :Deli_JournalNumber ";
        $sql .= " AND    s.Deli_DeliveryMethod = :Deli_DeliveryMethod ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $entId,
                ':Deli_JournalNumber' => $journalNumber,
                ':Deli_DeliveryMethod' => $deliMethod,
        );

        $ri = $stm->execute($prm);

        if (!($ri->count() > 0)) { return null; }

        return $ri->current()['Deli_JournalNumber'];
	}

	/**
	 * 最も最後に伝票番号入力された注文商品を取得する
	 *
	 * @param array $arrOrderSeqs 注文Seq配列
     * @return ResultInterface
	 */
	public function getLatestJournalIncItem($arrOrderSeqs)
	{
	    $query = sprintf("SELECT * FROM T_OrderItems WHERE OrderSeq IN (%s) AND DataClass = 1 ORDER BY Deli_JournalIncDate DESC, OrderSeq DESC", BaseGeneralUtils::ArrayToCsv($arrOrderSeqs));
        return $this->_adapter->query($query)->execute(null);
	}

	/**
	 * 複数注文の全ての注文商品を取得する
	 *
	 * @param array $arrOrderSeqs 注文Seq配列
     * @return ResultInterface
	 */
	public function getMultiOrderItems($arrOrderSeqs)
	{
	    $query = sprintf("SELECT * FROM T_OrderItems WHERE OrderSeq IN (%s) ORDER BY OrderSeq, DataClass, OrderItemId", BaseGeneralUtils::ArrayToCsv($arrOrderSeqs));
        return $this->_adapter->query($query)->execute(null);
	}

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO T_OrderItems (OrderSeq, DeliDestId, ItemNameKj, ItemNameKn, UnitPrice, ItemNum, SumMoney, TaxRate, TaxrateNotsetFlg, DataClass, Incre_Score, Incre_Note, Deli_JournalIncDate, Deli_DeliveryMethod, Deli_JournalNumber, Deli_ShipDate, Deli_ConfirmArrivalFlg, Deli_ConfirmArrivalDate, Deli_ConfirmArrivalOpId, Deli_ConfirmNoArrivalReason, CombinedTargetFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :DeliDestId ";
        $sql .= " , :ItemNameKj ";
        $sql .= " , :ItemNameKn ";
        $sql .= " , :UnitPrice ";
        $sql .= " , :ItemNum ";
        $sql .= " , :SumMoney ";
        $sql .= " , :TaxRate ";
        $sql .= " , :TaxrateNotsetFlg ";
        $sql .= " , :DataClass ";
        $sql .= " , :Incre_Score ";
        $sql .= " , :Incre_Note ";
        $sql .= " , :Deli_JournalIncDate ";
        $sql .= " , :Deli_DeliveryMethod ";
        $sql .= " , :Deli_JournalNumber ";
        $sql .= " , :Deli_ShipDate ";
        $sql .= " , :Deli_ConfirmArrivalFlg ";
        $sql .= " , :Deli_ConfirmArrivalDate ";
        $sql .= " , :Deli_ConfirmArrivalOpId ";
        $sql .= " , :Deli_ConfirmNoArrivalReason ";
        $sql .= " , :CombinedTargetFlg ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':DeliDestId' => $data['DeliDestId'],
                ':ItemNameKj' => $data['ItemNameKj'],
                ':ItemNameKn' => $data['ItemNameKn'],
                ':UnitPrice' => $data['UnitPrice'],
                ':ItemNum' => $data['ItemNum'],
                ':SumMoney' => $data['SumMoney'],
                ':TaxRate' => $data['TaxRate'],
                ':TaxrateNotsetFlg' => isset($data['TaxrateNotsetFlg']) ? $data['TaxrateNotsetFlg'] : 1,
                ':DataClass' => $data['DataClass'],
                ':Incre_Score' => $data['Incre_Score'],
                ':Incre_Note' => $data['Incre_Note'],
                ':Deli_JournalIncDate' => $data['Deli_JournalIncDate'],
                ':Deli_DeliveryMethod' => $data['Deli_DeliveryMethod'],
                ':Deli_JournalNumber' => $data['Deli_JournalNumber'],
                ':Deli_ShipDate' => $data['Deli_ShipDate'],
                ':Deli_ConfirmArrivalFlg' => $data['Deli_ConfirmArrivalFlg'],
                ':Deli_ConfirmArrivalDate' => $data['Deli_ConfirmArrivalDate'],
                ':Deli_ConfirmArrivalOpId' => $data['Deli_ConfirmArrivalOpId'],
                ':Deli_ConfirmNoArrivalReason' => $data['Deli_ConfirmNoArrivalReason'],
                ':CombinedTargetFlg' => isset($data['CombinedTargetFlg']) ? $data['CombinedTargetFlg'] : 1,
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
	 * 指定された条件でレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param array $conditionArray
	 */
	public function saveUpdateWhere($data, $conditionArray)
	{
	    $prm = array();
	    $sql  = " SELECT * FROM T_OrderItems WHERE 1 = 1 ";
	    foreach ($conditionArray as $key => $value) {
	        $sql .= (" AND " . $key . " = :" . $key);
	        $prm += array(':' . $key => $value);
	    }

	    $stm = $this->_adapter->query($sql);

	    $ri = $stm->execute($prm);

	    foreach ($ri AS $row) {
	        foreach ($data as $key => $value) {
	            if (array_key_exists($key, $row)) {
	                $row[$key] = $value;
	            }
	        }

	        // 指定されたレコードを更新する
	        $this->saveUpdate($row, $row['OrderItemId']);
	    }
	}

	/**
	 * 消費税率の更新
	 *
	 * @param array $taxrateData 消費税率、ログインID
	 */
	public function updateTaxrate($taxrateData,$oseq)
	{
	    $sql  = " UPDATE T_OrderItems ";
	    $sql .= " SET ";
	    $sql .= "     TaxRate = :TaxRate ";
	    $sql .= " ,   UpdateId = :UpdateId ";
	    $sql .= " ,   UpdateDate  = :UpdateDate ";
	    $sql .= " WHERE OrderSeq = :OrderSeq ";
	    $sql .= " AND  DataClass = 1";
	    $sql .= " AND  TaxrateNotsetFlg = 1";
	    $sql .= " AND  TaxRate IS NULL";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':TaxRate' => $taxrateData['TaxRate'],
	            ':OrderSeq' => $oseq,
	            ':UpdateId' => $taxrateData['UpdateId'],
	            ':UpdateDate' => date('Y-m-d H:i:s'),

	    );

	    return $stm->execute($prm);
	}

	/**
	 * 消費税率の更新（2019-09-30 以前の場合）
	 *
	 * @param array $taxrateData 消費税率、ログインID
	 */
	public function updateTaxrateBefore($taxrateData,$oseq)
	{
	    $sql  = " UPDATE T_OrderItems ";
	    $sql .= " SET ";
	    $sql .= "     TaxRate = :TaxRate ";
	    $sql .= " ,   UpdateId = :UpdateId ";
	    $sql .= " ,   UpdateDate  = :UpdateDate ";
	    $sql .= " WHERE OrderSeq = :OrderSeq ";
	    $sql .= " AND  DataClass = 1";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':TaxRate' => $taxrateData['TaxRate'],
	            ':OrderSeq' => $oseq,
	            ':UpdateId' => $taxrateData['UpdateId'],
	            ':UpdateDate' => date('Y-m-d H:i:s'),

	    );

	    return $stm->execute($prm);
	}


}