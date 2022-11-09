<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewEnterpriseCustomer
{
	protected $_name = 'MV_EnterpriseCustomer';
	protected $_primary = 'EntCustSeq';
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
	 * 加盟店顧客データを取得する
	 *
	 * @param int $entCustSeq
	 * @return ResultInterface
	 */
	public function find($entCustSeq)
	{
	    $sql  = " SELECT * FROM MV_EnterpriseCustomer WHERE EntCustSeq = :EntCustSeq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':EntCustSeq' => $entCustSeq,
	    );

	    return $stm->execute($prm);
	}

}
