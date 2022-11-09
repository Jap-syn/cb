<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewOrderItems
{
	protected $_name = 'MV_OrderItems';
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
	 * 注文商品データを取得する
	 *
	 * @param int $seq
	 * @return ResultInterface
	 */
	public function find($orderItemId)
	{
	    $sql  = " SELECT * FROM MV_OrderItems WHERE OrderItemId = :OrderItemId ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':OrderItemId' => $orderItemId,
	    );

	    return $stm->execute($prm);
	}


}
