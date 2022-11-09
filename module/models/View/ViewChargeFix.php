<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * V_ChargeFixビュー
 */
class ViewChargeFix
{
	protected $_name = 'V_ChargeFix';
	protected $_primary = 'OrderSeq';
	protected $_adapter = null;

	/**
	 * ベースSQL
	 */
	const BASE_QUERY = "
SELECT ORD.OrderSeq AS OrderSeq
    ,ORD.Chg_Seq AS Chg_Seq
    ,ORD.EnterpriseId AS EnterpriseId
    ,ORD.OrderId AS OrderId
    ,ORD.ReceiptOrderDate AS ReceiptOrderDate
    ,CUS.NameKj AS NameKj
    ,CUS.CustomerId AS CustomerId
    ,ORD.SiteId AS SiteId
    ,ORD.UseAmount AS UseAmount
    ,PAS.SettlementFee AS SettlementFee
    ,PAS.ClaimFee AS ClaimFee
    ,(
        SELECT sum(T_StampFee.StampFee)
        FROM T_StampFee
        WHERE (
                (T_StampFee.OrderSeq = ORD.OrderSeq)
                AND (T_StampFee.ClearFlg = 1)
                )
        ) AS StampFee
    ,RCT.ReceiptClass AS ReceiptClass
    ,ORD.Chg_ChargeAmount AS Chg_ChargeAmount
	,ORD.Rct_Status AS Rct_Status
FROM T_Order ORD
INNER JOIN T_Customer CUS ON ORD.OrderSeq = CUS.OrderSeq
INNER JOIN T_PayingAndSales PAS ON ORD.OrderSeq = PAS.OrderSeq
LEFT  JOIN T_ClaimControl CLM ON ORD.P_OrderSeq = CLM.OrderSeq
LEFT  JOIN T_ReceiptControl RCT ON CLM.LastReceiptSeq = RCT.ReceiptSeq
WHERE 1 = 1
";

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
	 * 立替確定データを取得する
	 *
	 * @param int $payingControlSeq 立替振込管理Seq
	 * @return ResultInterface
	 */
	public function getFixList($payingControlSeq, $oemId = null)
	{
        $prm = array();

        $sql  = self::BASE_QUERY;
        $sql .= " AND ORD.Chg_Seq = :Chg_Seq ";
        $prm[':Chg_Seq'] = $payingControlSeq;
        if (nvl($oemId, 0)) {
            $sql .= " AND ORD.OemId = :OemId ";
            $prm[':OemId'] = $oemId;
        }

        return $this->_adapter->query($sql)->execute($prm);
	}
}
