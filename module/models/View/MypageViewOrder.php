<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewOrder
{
	protected $_name = 'MV_Order';
	protected $_primary = 'OrderSeq';
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
	 * 注文データを取得する
	 *
	 * @param int $orderSeq
	 * @return ResultInterface
	 */
	public function find($orderSeq)
	{
	    $sql  = " SELECT * FROM MV_Order WHERE OrderSeq = :OrderSeq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':OrderSeq' => $orderSeq,
	    );

	    return $stm->execute($prm);
	}


}
