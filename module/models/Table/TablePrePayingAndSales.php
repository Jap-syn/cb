<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_PrePayingAndSales(退避_立替売上管理)テーブルへのアダプタ
 */
class TablePrePayingAndSales
{
	/**
	 * 請求手数料を示す定数
	 *
	 */
	const CLAIM_FEE_DEFAULT = 200;

	/**
	 * テーブル名
	 *
	 * @var string
	 */
	protected $_name = 'T_PrePayingAndSales ';

	/**
	 * プライマリキー
	 *
	 * @var string
	 */
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
   * レコードを一件取得する
   * @param int $seq
   * @return array
   */
	public function find($seq){
	    $sql = " SELECT * FROM T_PrePayingAndSales WHERE Seq = :Seq ";
	    return $this->_adapter->query($sql)->execute( array(':Seq'=>$seq) )->current();
	}
	/**
	 * 新しいPayingAndSalesEvaの行クラスを作成する。作成された行はまだテーブルに属さないため、
	 * 値を適切に設定して{@link Zend_Db_Table_Row_Abstract::save}を実行する必要がある
	 *
	 * @param int $orderSeq 注文シーケンス
	 * @param int $useAmount 注文金額
	 * @param int $settlementFeeRate 立替手数料率。パーセント値の数値
	 * @param int $claimFee 請求手数料
	 * @param array|null $data 初期データの連想配列
	 * @return array
	 */
	public function newRow($orderSeq, $useAmount, $settlementFeeRate, $claimFee, $data = array()) {
	    if( ! is_array( $data ) ) $data = array();

		// 注文シーケンスが不正の場合は例外をスロー
		if( ((int)$orderSeq < 1 ) ) {
			throw new \Exception( 'invalid order sequence' );
		}

		// 立替額と手数料を計算して連想配列を整形
		$data = array_merge( $data, self::calcFeeAndAmount( (int)$useAmount, $settlementFeeRate, (int)$claimFee ) );
		// 注文IDとデータ発生日、およびフラグフィールドの初期値を連想配列に適用
		$data = array_merge( $data, array(
			'OrderSeq' => $orderSeq,
			'OccDate' => date('Y-m-d'),
			'ClearConditionForCharge' => 0,
			'ChargeDecisionFlg' => 0,
			'CancelFlg' => 0
		) );

		// Mod By Takemasa(NDC) 20141226 Stt 関数戻り値の変更(row作成は行わない)
		//// 行を返す
		//return $this->createRow( $data );
		// arrayを返す
		return $data;
		// Mod By Takemasa(NDC) 20141226 End 関数戻り値の変更(row作成は行わない)
	}

/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO T_PrePayingAndSales (OrderSeq, OccDate, UseAmount, AppSettlementFeeRate, SettlementFee, ClaimFee, ChargeAmount, ClearConditionForCharge, ClearConditionDate, ChargeDecisionFlg, ChargeDecisionDate, CancelFlg, PayingControlSeq, RegistFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :OccDate ";
        $sql .= " , :UseAmount ";
        $sql .= " , :AppSettlementFeeRate ";
        $sql .= " , :SettlementFee ";
        $sql .= " , :ClaimFee ";
        $sql .= " , :ChargeAmount ";
        $sql .= " , :ClearConditionForCharge ";
        $sql .= " , :ClearConditionDate ";
        $sql .= " , :ChargeDecisionFlg ";
        $sql .= " , :ChargeDecisionDate ";
        $sql .= " , :CancelFlg ";
        $sql .= " , :PayingControlSeq ";
        $sql .= " , 0 ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':OccDate' => $data['OccDate'],
                ':UseAmount' => $data['UseAmount'],
                ':AppSettlementFeeRate' => $data['AppSettlementFeeRate'],
                ':SettlementFee' => $data['SettlementFee'],
                ':ClaimFee' => $data['ClaimFee'],
                ':ChargeAmount' => $data['ChargeAmount'],
                ':ClearConditionForCharge' => $data['ClearConditionForCharge'],
                ':ClearConditionDate' => $data['ClearConditionDate'],
                ':ChargeDecisionFlg' => $data['ChargeDecisionFlg'],
                ':ChargeDecisionDate' => $data['ChargeDecisionDate'],
                ':CancelFlg' => $data['CancelFlg'],
                ':PayingControlSeq' => $data['PayingControlSeq'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}
  /**
   * 指定したレコードを登録済みに更新する
   */
	public function updateRegisted($seq)
	{
	    $sql  = " UPDATE T_PrePayingAndSales ";
	    $sql .= " SET RegistFlg = 1 WHERE Seq = :Seq ";
	    $this->_adapter->query($sql)->execute(array(':Seq' => $seq));
	}
	/**
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param int $seq 更新するSeq
	 */
	public function saveUpdate($data, $seq)
	{
	    $sql = " SELECT * FROM T_PrePayingAndSales WHERE Seq = :Seq ";

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

	    $sql  = " UPDATE T_PrePayingAndSales ";
	    $sql .= " SET ";
	    $sql .= "     OrderSeq = :OrderSeq ";
	    $sql .= " ,   OccDate = :OccDate ";
	    $sql .= " ,   UseAmount = :UseAmount ";
	    $sql .= " ,   AppSettlementFeeRate = :AppSettlementFeeRate ";
	    $sql .= " ,   SettlementFee = :SettlementFee ";
	    $sql .= " ,   ClaimFee = :ClaimFee ";
	    $sql .= " ,   ChargeAmount = :ChargeAmount ";
	    $sql .= " ,   ClearConditionForCharge = :ClearConditionForCharge ";
	    $sql .= " ,   ClearConditionDate = :ClearConditionDate ";
	    $sql .= " ,   ChargeDecisionFlg = :ChargeDecisionFlg ";
	    $sql .= " ,   ChargeDecisionDate = :ChargeDecisionDate ";
	    $sql .= " ,   CancelFlg = :CancelFlg ";
	    $sql .= " ,   PayingControlSeq = :PayingControlSeq ";
	    $sql .= " ,   RegistFlg = :RegistFlg ";
	    $sql .= " WHERE Seq = :Seq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':Seq' => $seq,
	            ':OrderSeq' => $row['OrderSeq'],
	            ':OccDate' => $row['OccDate'],
	            ':UseAmount' => $row['UseAmount'],
	            ':AppSettlementFeeRate' => $row['AppSettlementFeeRate'],
	            ':SettlementFee' => $row['SettlementFee'],
	            ':ClaimFee' => $row['ClaimFee'],
	            ':ChargeAmount' => $row['ChargeAmount'],
	            ':ClearConditionForCharge' => $row['ClearConditionForCharge'],
	            ':ClearConditionDate' => $row['ClearConditionDate'],
	            ':ChargeDecisionFlg' => $row['ChargeDecisionFlg'],
	            ':ChargeDecisionDate' => $row['ChargeDecisionDate'],
	            ':CancelFlg' => $row['CancelFlg'],
	            ':PayingControlSeq' => $row['PayingControlSeq'],
	            ':RegistFlg' => $row['RegistFlg'],
	    );

	    return $stm->execute($prm);
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
	    $sql  = " SELECT * FROM T_PrePayingAndSales WHERE 1 = 1 ";
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
	        $this->saveUpdate($row, $row['Seq']);
	    }
	}
	/**
	 * 本登録対象注文を取得する
	 */
	public function getRegistOrders()
	{
	    $sql = <<<EOQ
 SELECT
     Seq,
     OrderSeq ,
     OccDate,
     UseAmount,
     AppSettlementFeeRate,
     SettlementFee,
     ClaimFee,
     ChargeAmount,
     ClearConditionForCharge,
     ClearConditionDate,
     ChargeDecisionFlg,
     CancelFlg
 FROM T_PrePayingAndSales
 WHERE RegistFlg = 0
EOQ;
    return $this->_adapter->query($sql)->execute();
	}
}
?>