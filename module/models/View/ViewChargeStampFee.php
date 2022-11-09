<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultInterface\ResultInterface;

/**
 * V_ChargeStampFeeビュー
 *
--
-- V_ChargeStampFee
-- 立替時印紙代精算対象ビュー
--
DROP VIEW V_ChargeStampFee;

CREATE VIEW V_ChargeStampFee AS
SELECT
    STF.Seq,
    STF.OrderSeq,
    STF.DecisionDate,
    STF.StampFee,
    ODR.EnterpriseId,
    ODR.RegistDate,
    ODR.ReceiptOrderDate
FROM
    T_StampFee STF,
    T_Order ODR
WHERE
    STF.OrderSeq = ODR.OrderSeq AND
    STF.ClearFlg = 0 AND
    STF.CancelFlg = 0
;
 *
 */
class ViewChargeStampFee
{
	protected $_name = 'V_ChargeStampFee';
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
	 * 指定条件（AND）の立替時精算対象印紙代データを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @param string $order オーダー
	 * @return ResultInterface
	 */
	public function findChargeStampFee($conditionArray, $order)
	{
        $prm = array();
        $sql  = " SELECT * FROM V_ChargeStampFee WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }
        $sql .= " ORDER BY " . $order;

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
	}

	/**
	 * 指定事業者・指定締め日の立替時精算対象印紙代データを取得する。
	 *
	 * @param int $enterpriseId 事業者ID
	 * @param string $fixedDate 対象締め日 'yyyy-MM-dd'書式で通知
	 * @return ResultInterface
	 */
	public function getChargeStampFee($enterpriseId, $fixedDate)
	{
        $sql = " SELECT * FROM V_ChargeStampFee WHERE EnterpriseId = :EnterpriseId AND DecisionDate <= :DecisionDate ORDER BY Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
                ':DecisionDate' => $fixedDate,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定された立替振込管理Seqをもつ、印紙代データを取得する。
	 *
	 * @param int $pcseq 立替振込管理Seq
	 * @return ResultInterface
	 */
	public function getStampFeeData($pcseq, $oemId = null)
	{
        $prm = array();

        $query = <<<EOQ
SELECT
    STF.Seq,
    STF.OrderSeq,
    STF.DecisionDate,
    STF.StampFee,
	ODR.OrderSeq,
	ODR.OrderId,
    ODR.EnterpriseId,
    ODR.RegistDate,
    ODR.ReceiptOrderDate,
	ODR.UseAmount,
	CUS.NameKj,
    CUS.CustomerId
FROM
    T_StampFee STF,
    T_Order ODR,
	T_Customer CUS
WHERE
	STF.OrderSeq = CUS.OrderSeq AND
    STF.OrderSeq = ODR.OrderSeq AND
	STF.PayingControlSeq = :PayingControlSeq
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
