<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewOem
{
	protected $_name = 'MV_Oem';
	protected $_primary = 'OemId';
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
	 * Oemデータを取得する
	 *
	 * @param int $oemId
	 * @return ResultInterface
	 */
	public function find($oemId)
	{
	    $sql  = " SELECT * FROM MV_Oem WHERE OemId = :OemId ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':OemId' => $oemId,
	    );

	    return $stm->execute($prm);
	}

	/**
	 * AccessIdからT_Oem行を逆引きする
	 *
	 * @param string $accessId AccessId
	 * @return ResultInterface 行オブジェクト
	 */
	public function findByAccessId($accessId)
	{
	    $sql = " SELECT * FROM MV_Oem WHERE ValidFlg = 1 AND AccessId = :AccessId ORDER BY OemId ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':AccessId' => $accessId,
	    );

	    return $stm->execute($prm);
	}

}
