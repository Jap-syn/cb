<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * V_Deliveryビュー
 */
class ViewDelivery
{
	protected $_name = 'V_Delivery';
	protected $_primary = 'OrderItemId';
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
	 * 指定条件（AND）の配送データを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @return ResultInterface
	 */
	public function findDelivery($conditionArray)
	{
        $prm = array();
        $sql  = " SELECT * FROM V_Delivery WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }
        $sql .= " ORDER BY OrderItemId ";

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
	}

	/**
	 * 指定注文Seqの商品配送データを取得する。
	 *
	 * @param int $orderSeq 注文Seq
	 * @return ResultInterface
	 */
	public function findByOrderSeq($orderSeq)
	{
        $sql = " SELECT * FROM V_Delivery WHERE OrderSeq = :OrderSeq ORDER BY OrderItemId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定注文Seqの立替確定条件が顧客入金であるか否か。
	 * @param int $orderSeq 注文Seq
	 * @return boolean true:立替条件が顧客入金である false:立替条件が顧客入金ではない
	 */
	public function isPayChgConditionEq2($orderSeq)
	{
        $sql = " SELECT DISTINCT Deli_PayChgCondition FROM V_Delivery WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
        );

        $ri = $stm->execute($prm);

        if (!($ri->count() > 0)) { return false; }

        return ((int)$ri->current()['Deli_PayChgCondition'] == 2) ? true : false;
	}
}
