<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewOperator
{
	protected $_name = 'MV_Operator';
	protected $_primary = 'OpId';
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
	 * オペレーターデータを取得する
	 *
	 * @param int $opId
	 * @return ResultInterface
	 */
	public function find($opId)
	{
	    $sql  = " SELECT * FROM MV_Operator WHERE OpId = :OpId ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':OpId' => $opId,
	    );

	    return $stm->execute($prm);
	}

}
