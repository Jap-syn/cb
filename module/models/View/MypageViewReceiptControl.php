<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewReceiptControl
{
	protected $_name = 'MV_ReceiptControl';
	protected $_primary = 'ReceiptSeq';
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
	 * 入金管理データを取得する
	 *
	 * @param int $receiptSeq
	 * @return ResultInterface
	 */
	public function find($receiptSeq)
	{
	    $sql  = " SELECT * FROM MV_ReceiptControl WHERE ReceiptSeq = :ReceiptSeq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':ReceiptSeq' => $receiptSeq,
	    );

	    return $stm->execute($prm);
	}

}
