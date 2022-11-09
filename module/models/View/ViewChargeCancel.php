<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * V_ChargeCancelビュー
 *
--
-- V_ChargeCancel
-- 立替時キャンセル精算対象ビュー
--
DROP VIEW V_ChargeCancel;

CREATE VIEW V_ChargeCancel AS
SELECT
    CNL.Seq,
    CNL.OrderSeq,
    CNL.CancelDate,
    CNL.CancelPhase,
    CNL.CancelReason,
    CNL.RepayChargeAmount,
    CNL.RepaySettlementFee,
    CNL.RepayClaimFee,
    CNL.RepayStampFee,
    CNL.RepayDamageInterest,
    CNL.RepayReClaimFee,
    CNL.RepayDifferentialAmount,
    CNL.RepayDepositAmount,
    CNL.RepayReceiptAmount,
    CNL.RepayTotal,
    CNL.ApprovalDate,
    CNL.ApproveOpId,
    ODR.EnterpriseId,
    ODR.RegistDate,
    ODR.ReceiptOrderDate
FROM
    T_Cancel CNL,
    T_Order ODR
WHERE
    CNL.OrderSeq = ODR.OrderSeq AND
    CNL.ApproveFlg = 1 AND
    CNL.KeepAnAccurateFlg = 0
;
 *
 */
class ViewChargeCancel
{
	protected $_name = 'V_ChargeCancel';
	protected $_primary = 'Seq';
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
	 * 指定条件（AND）の立替時精算対象キャンセルデータを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @param string $order オーダー
	 * @return ResultInterface
	 */
	public function findChargeCancel($conditionArray, $order)
	{
        $prm = array();
        $sql  = " SELECT * FROM V_ChargeCancel WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }
        $sql .= " ORDER BY " . $order;

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
	}

	/**
	 * 指定事業者・指定締め日の立替時精算対象キャンセルデータを取得する。
	 *
	 * @param int $enterpriseId 事業者ID
	 * @param string $fixedDate 対象締め日 'yyyy-MM-dd'書式で通知
	 * @return ResultInterface
	 */
	public function getChargeCancel($enterpriseId, $fixedDate)
	{
        $sql = " SELECT * FROM V_ChargeCancel WHERE EnterpriseId = :EnterpriseId AND ApprovalDate <= :ApprovalDate ORDER BY Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
                ':ApprovalDate' => $fixedDate,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定された立替振込管理Seqをもつキャンセルデータを取得する。
	 *
	 * @param int $pcseq 立替振込管理Seq
	 * @return ResultInterface
	 */
	public function getCancelData($pcseq, $oemId = null)
	{
	    $prm = array();

        $query = <<<EOQ
SELECT
    CNL.Seq,
    CNL.OrderSeq,
    DATE_FORMAT(CNL.CancelDate, '%Y-%m-%d') AS CancelDate,
    CNL.CancelPhase,
    CNL.CancelReason,
    CNL.RepayChargeAmount,
    CNL.RepaySettlementFee,
    CNL.RepayClaimFee,
    CNL.RepayStampFee,
    CNL.RepayDamageInterest,
    CNL.RepayReClaimFee,
    CNL.RepayDifferentialAmount,
    CNL.RepayDepositAmount,
    CNL.RepayReceiptAmount,
    CNL.RepayTotal,
    CNL.ApprovalDate,
    CNL.ApproveOpId,
    ODR.EnterpriseId,
    ODR.RegistDate,
    ODR.ReceiptOrderDate,
	ODR.OrderId,
	ODR.UseAmount,
	CUS.NameKj,
	CUS.CustomerId,
    CNL.RegistDate AS CancelRegistDate, /* (キャンセル)登録日時 */
    F_GetLoginUserName(CNL.RegistId) AS CancelRegistName /* (キャンセル)登録者 */
FROM
    T_Cancel CNL,
    T_Order ODR,
	T_Customer CUS
WHERE
	CNL.ValidFlg = 1 AND
	CNL.OrderSeq = CUS.OrderSeq AND
    CNL.OrderSeq = ODR.OrderSeq AND
	CNL.PayingControlSeq = :PayingControlSeq
EOQ;
        $prm += array(':PayingControlSeq' => $pcseq);

        if(nvl($oemId, 0)) {
            $query .= " AND ODR.OemId = :OemId ";
            $prm += array(':OemId' => $oemId);
        }

        $query .= " ORDER BY ODR.OrderSeq";

        return $this->_adapter->query($query)->execute($prm);
	}
}
