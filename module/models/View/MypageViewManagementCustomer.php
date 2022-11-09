<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewManagementCustomer
{
	protected $_name = 'MV_ManagementCustomer';
	protected $_primary = 'ManCustId';
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
	 * 管理顧客データを取得する
	 *
	 * @param int $manCustId
	 * @return ResultInterface
	 */
	public function find($manCustId)
	{
	    $sql  = " SELECT * FROM MV_ManagementCustomer WHERE ManCustId = :ManCustId ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':ManCustId' => $manCustId,
	    );

	    return $stm->execute($prm);
	}


}
