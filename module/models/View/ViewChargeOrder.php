<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * V_ChargeOrderビュー
 *
--
-- V_ChargeOrder
-- 立替対象ビュー
--
DROP VIEW V_ChargeOrder;

CREATE VIEW V_ChargeOrder AS
SELECT
    PAS.Seq,
    PAS.OrderSeq,
    PAS.OccDate,
    PAS.UseAmount,
    PAS.AppSettlementFeeRate,
    PAS.SettlementFee,
    PAS.ClaimFee,
    PAS.ChargeAmount,
    PAS.ClearConditionDate,
    ODR.EnterpriseId,
    ODR.RegistDate,
    ODR.ReceiptOrderDate
FROM
    T_PayingAndSales PAS,
    T_Order ODR
WHERE
    PAS.OrderSeq = ODR.OrderSeq AND
    PAS.ClearConditionForCharge = 1 AND
    PAS.ChargeDecisionFlg = 0 AND
    PAS.CancelFlg = 0
;
 *
 */
class ViewChargeOrder
{
	protected $_name = 'V_ChargeOrder';
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
	 * 指定条件（AND）の立替対象データを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @param string $order オーダー
	 * @return ResultInterface
	 */
	public function findChargeOrder($conditionArray, $order)
	{
        $prm = array();
        $sql  = " SELECT * FROM V_ChargeOrder WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }
        if (strlen($order) > 0) {
            $sql .= " ORDER BY " . $order;
        }

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
	}

	/**
	 * 指定事業者・指定締め日の立替対象データを取得する。
	 *
	 * @param int $enterpriseId 事業者ID
	 * @param string $fixedDate 対象締め日 'yyyy-MM-dd'書式で通知
	 *
	 * @return ResultInterface
	 */
	public function getChargeOrder($enterpriseId, $fixedDate)
	{
        $sql = " SELECT * FROM V_ChargeOrder WHERE EnterpriseId = :EnterpriseId AND ClearConditionDate <= :ClearConditionDate ORDER BY Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
                ':ClearConditionDate' => $fixedDate,
        );

        return $stm->execute($prm);
	}
}
