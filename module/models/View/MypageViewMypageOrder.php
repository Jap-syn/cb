<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewMypageOrder
{
	protected $_name = 'MV_MypageOrder';
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
	 * 注文マイページデータを取得する
	 *
	 * @param int $seq
	 * @return ResultInterface
	 */
	public function find($seq)
	{
	    $sql  = " SELECT * FROM MV_MypageOrder WHERE Seq = :Seq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':Seq' => $seq,
	    );

	    return $stm->execute($prm);
	}

	/**
	 * 注文マイページデータを取得する
	 *
	 * @param string $accesskey
	 * @return ResultInterface
	 */
	public function findByAccessKey($accesskey)
	{
	    $sql  = " SELECT * FROM MV_MypageOrder WHERE AccessKey = :AccessKey AND ValidFlg = 1 ORDER BY Seq DESC Limit 1 ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':AccessKey' => $accesskey,
	    );

	    return $stm->execute($prm);
	}

}
