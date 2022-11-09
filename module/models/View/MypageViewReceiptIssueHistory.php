<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewReceiptIssueHistory
{
	protected $_name = 'MPV_ReceiptIssueHistory';
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
	 * 注文SEQの領収書発行履歴を取得する。
	 *
	 * @param int $orderSeq 注文SEQ
	 * @return ResultInterface
	 */
	public function findOrderSeq($orderSeq)
	{
        $sql  = " SELECT *";
        $sql .= " FROM MPV_ReceiptIssueHistory";
        $sql .= " WHERE OrderSeq = :OrderSeq ";
        $sql .= " ORDER BY Seq desc ";
        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
        );

        return $stm->execute($prm);
	}

	/**
	 * 注文SEQの領収書発行履歴を取得する。
	 *
	 * @param int $orderSeq 注文SEQ
	 * @return ResultInterface
	 */
	public function findOrderSeqSeqAsc($orderSeq)
	{
        $sql  = " SELECT *";
        $sql .= " FROM MPV_ReceiptIssueHistory";
        $sql .= " WHERE OrderSeq = :OrderSeq ";
        $sql .= " ORDER BY Seq";
        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
        );

        return $stm->execute($prm);
	}

}
