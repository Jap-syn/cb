<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewDeliveryDestination
{
	protected $_name = 'MV_DeliveryDestination';
	protected $_primary = 'DeliDestId';
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
	 * 配送先データを取得する
	 *
	 * @param int $deliDestId
	 * @return ResultInterface
	 */
	public function find($deliDestId)
	{
	    $sql  = " SELECT * FROM MV_DeliveryDestination WHERE DeliDestId = :DeliDestId ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':DeliDestId' => $deliDestId,
	    );

	    return $stm->execute($prm);
	}


}
