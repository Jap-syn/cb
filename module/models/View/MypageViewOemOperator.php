<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewOemOperator
{
	protected $_name = 'MV_OemOperator';
	protected $_primary = 'OemOpId';
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
	 * OEMオペレーターデータを取得する
	 *
	 * @param int $oemOpId
	 * @return ResultInterface
	 */
	public function find($oemOpId)
	{
	    $sql  = " SELECT * FROM MV_OemOperator WHERE OemOpId = :OemOpId ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':OemOpId' => $oemOpId,
	    );

	    return $stm->execute($prm);
	}

}
