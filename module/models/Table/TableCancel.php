<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_Cancelテーブルへのアダプタ
 */
class TableCancel
{
	protected $_name = 'T_Cancel';
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
	 * 指定条件（AND）のキャンセル管理データを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @param boolean $isAsc プライマリキーのオーダー
	 * @return ResultInterface
	 */
	public function findCancel($conditionArray, $isAsc = false)
	{
        $prm = array();
        $sql  = " SELECT * FROM T_Cancel WHERE 1 = 1 AND ValidFlg = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }
        $sql .= " ORDER BY Seq " . ($isAsc ? "asc" : "desc");

        $stm = $this->_adapter->query($sql);

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
        $sql  = " INSERT INTO T_Cancel (OrderSeq, CancelDate, CancelPhase, CancelReason, RepayChargeAmount, RepaySettlementFee, RepayClaimFee, RepayStampFee, RepayDamageInterest, RepayReClaimFee, RepayDifferentialAmount, RepayDepositAmount, RepayReceiptAmount, RepayTotal, ApproveFlg, ApprovalDate, ApproveOpId, KeepAnAccurateFlg, KeepAnAccurateDate, PayingControlSeq, CancelReasonCode, CancelRequestDate, PayingControlStatus, DailySummaryFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :CancelDate ";
        $sql .= " , :CancelPhase ";
        $sql .= " , :CancelReason ";
        $sql .= " , :RepayChargeAmount ";
        $sql .= " , :RepaySettlementFee ";
        $sql .= " , :RepayClaimFee ";
        $sql .= " , :RepayStampFee ";
        $sql .= " , :RepayDamageInterest ";
        $sql .= " , :RepayReClaimFee ";
        $sql .= " , :RepayDifferentialAmount ";
        $sql .= " , :RepayDepositAmount ";
        $sql .= " , :RepayReceiptAmount ";
        $sql .= " , :RepayTotal ";
        $sql .= " , :ApproveFlg ";
        $sql .= " , :ApprovalDate ";
        $sql .= " , :ApproveOpId ";
        $sql .= " , :KeepAnAccurateFlg ";
        $sql .= " , :KeepAnAccurateDate ";
        $sql .= " , :PayingControlSeq ";
        $sql .= " , :CancelReasonCode ";
        $sql .= " , :CancelRequestDate ";
        $sql .= " , :PayingControlStatus ";
        $sql .= " , :DailySummaryFlg ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':CancelDate' => $data['CancelDate'],
                ':CancelPhase' => $data['CancelPhase'],
                ':CancelReason' => $data['CancelReason'],
                ':RepayChargeAmount' => $data['RepayChargeAmount'],
                ':RepaySettlementFee' => $data['RepaySettlementFee'],
                ':RepayClaimFee' => $data['RepayClaimFee'],
                ':RepayStampFee' => $data['RepayStampFee'],
                ':RepayDamageInterest' => $data['RepayDamageInterest'],
                ':RepayReClaimFee' => $data['RepayReClaimFee'],
                ':RepayDifferentialAmount' => $data['RepayDifferentialAmount'],
                ':RepayDepositAmount' => $data['RepayDepositAmount'],
                ':RepayReceiptAmount' => $data['RepayReceiptAmount'],
                ':RepayTotal' => $data['RepayTotal'],
                ':ApproveFlg' => $data['ApproveFlg'],
                ':ApprovalDate' => $data['ApprovalDate'],
                ':ApproveOpId' => $data['ApproveOpId'],
                ':KeepAnAccurateFlg' => $data['KeepAnAccurateFlg'],
                ':KeepAnAccurateDate' => $data['KeepAnAccurateDate'],
                ':PayingControlSeq' => $data['PayingControlSeq'],
                ':CancelReasonCode' => $data['CancelReasonCode'],
                ':CancelRequestDate' => $data['CancelRequestDate'],
                ':PayingControlStatus' => isset($data['PayingControlStatus']) ? $data['PayingControlStatus'] : 0,
                ':DailySummaryFlg' => isset($data['DailySummaryFlg']) ? $data['DailySummaryFlg'] : 0,
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
	 * @param int $seq 更新するSeq
	 */
	public function saveUpdate($data, $seq)
	{
        $sql = " SELECT * FROM T_Cancel WHERE Seq = :Seq ";

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

        $sql  = " UPDATE T_Cancel ";
        $sql .= " SET ";
        $sql .= "     OrderSeq = :OrderSeq ";
        $sql .= " ,   CancelDate = :CancelDate ";
        $sql .= " ,   CancelPhase = :CancelPhase ";
        $sql .= " ,   CancelReason = :CancelReason ";
        $sql .= " ,   RepayChargeAmount = :RepayChargeAmount ";
        $sql .= " ,   RepaySettlementFee = :RepaySettlementFee ";
        $sql .= " ,   RepayClaimFee = :RepayClaimFee ";
        $sql .= " ,   RepayStampFee = :RepayStampFee ";
        $sql .= " ,   RepayDamageInterest = :RepayDamageInterest ";
        $sql .= " ,   RepayReClaimFee = :RepayReClaimFee ";
        $sql .= " ,   RepayDifferentialAmount = :RepayDifferentialAmount ";
        $sql .= " ,   RepayDepositAmount = :RepayDepositAmount ";
        $sql .= " ,   RepayReceiptAmount = :RepayReceiptAmount ";
        $sql .= " ,   RepayTotal = :RepayTotal ";
        $sql .= " ,   ApproveFlg = :ApproveFlg ";
        $sql .= " ,   ApprovalDate = :ApprovalDate ";
        $sql .= " ,   ApproveOpId = :ApproveOpId ";
        $sql .= " ,   KeepAnAccurateFlg = :KeepAnAccurateFlg ";
        $sql .= " ,   KeepAnAccurateDate = :KeepAnAccurateDate ";
        $sql .= " ,   PayingControlSeq = :PayingControlSeq ";
        $sql .= " ,   CancelReasonCode = :CancelReasonCode ";
        $sql .= " ,   CancelRequestDate = :CancelRequestDate ";
        $sql .= " ,   PayingControlStatus = :PayingControlStatus ";
        $sql .= " ,   DailySummaryFlg = :DailySummaryFlg ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':OrderSeq' => $row['OrderSeq'],
                ':CancelDate' => $row['CancelDate'],
                ':CancelPhase' => $row['CancelPhase'],
                ':CancelReason' => $row['CancelReason'],
                ':RepayChargeAmount' => $row['RepayChargeAmount'],
                ':RepaySettlementFee' => $row['RepaySettlementFee'],
                ':RepayClaimFee' => $row['RepayClaimFee'],
                ':RepayStampFee' => $row['RepayStampFee'],
                ':RepayDamageInterest' => $row['RepayDamageInterest'],
                ':RepayReClaimFee' => $row['RepayReClaimFee'],
                ':RepayDifferentialAmount' => $row['RepayDifferentialAmount'],
                ':RepayDepositAmount' => $row['RepayDepositAmount'],
                ':RepayReceiptAmount' => $row['RepayReceiptAmount'],
                ':RepayTotal' => $row['RepayTotal'],
                ':ApproveFlg' => $row['ApproveFlg'],
                ':ApprovalDate' => $row['ApprovalDate'],
                ':ApproveOpId' => $row['ApproveOpId'],
                ':KeepAnAccurateFlg' => $row['KeepAnAccurateFlg'],
                ':KeepAnAccurateDate' => $row['KeepAnAccurateDate'],
                ':PayingControlSeq' => $row['PayingControlSeq'],
                ':CancelReasonCode' => $row['CancelReasonCode'],
                ':CancelRequestDate' => $row['CancelRequestDate'],
                ':PayingControlStatus' => $row['PayingControlStatus'],
                ':DailySummaryFlg' => $row['DailySummaryFlg'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
	}

	/**
	 * キャンセル承認
	 *
	 * @param $oseq 注文Seq
	 * @param $opId 担当者
	 * @param $useId ユーザーID
	 */
	public function approve($oseq, $opId, $userId)
	{
        $sql  = " UPDATE T_Cancel ";
        $sql .= " SET ";
        $sql .= "     ApproveFlg              = 1";
        $sql .= " ,   ApprovalDate            = :ApprovalDate ";
        $sql .= " ,   ApproveOpId             = :ApproveOpId ";
        $sql .= " ,   UpdateDate              = :UpdateDate ";
        $sql .= " ,   UpdateId                = :UpdateId ";
        $sql .= " WHERE OrderSeq              = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ApprovalDate' => date('Y-m-d H:i:s'),
                ':ApproveOpId' => $opId,
                ':OrderSeq' => $oseq,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $userId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 精算済みにする
	 *
	 * @param int $seq 管理Seq
	 * @param string $date 精算日 'yyyy-MM-dd'書式で通知
	 * @param int $payingControlSeq 立替振込管理Seq
	 * @param $opId 担当者
	 */
	public function settleUp($seq, $date, $payingControlSeq, $opId)
	{
        $sql  = " UPDATE T_Cancel ";
        $sql .= " SET ";
        $sql .= "     KeepAnAccurateFlg       = 1";
        $sql .= " ,   KeepAnAccurateDate      = :KeepAnAccurateDate ";
        $sql .= " ,   PayingControlSeq        = :PayingControlSeq ";
        $sql .= " ,   UpdateDate              = :UpdateDate ";
        $sql .= " ,   UpdateId                = :UpdateId ";
        $sql .= " WHERE Seq                   = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':KeepAnAccurateDate' => $date,
                ':PayingControlSeq' => $payingControlSeq,
                ':Seq' => $seq,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $opId,
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
	    $sql  = " SELECT * FROM T_Cancel WHERE 1 = 1 ";
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
     * 過去二年間の注文でいたずらキャンセルの件数を取得する
     *
     * @param $pastOrders 過去2年間の対象注文以外の注文配列
     * @return int 件数
     */
    public function findMischiefCancel($pastOrders) {
        $query  = " SELECT COUNT(*) AS Cnt ";
        $query .= " FROM T_Cancel ";
        $query .= " WHERE ";
        $query .= sprintf(" CancelReasonCode = 7 AND OrderSeq IN (%s) ", $pastOrders);

        return (int)$this->_adapter->query($query)->execute(null)->current()['Cnt'];
    }
}
