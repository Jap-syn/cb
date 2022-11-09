<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewEnterprise
{
	protected $_name = 'MV_Enterprise';
	protected $_primary = 'EnterpriseId';
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
	 * 加盟店データを取得する
	 *
	 * @param int $enterpriseId
	 * @return ResultInterface
	 */
	public function find($enterpriseId)
	{
	    $sql  = " SELECT * FROM MV_Enterprise WHERE EnterpriseId = :EnterpriseId ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':EnterpriseId' => $enterpriseId,
	    );

	    return $stm->execute($prm);
	}

	/**
	 * 指定事業者IDの事業者データを取得する。
	 *
	 * @param string $enterpriseId 事業者ID
	 * @return ResultInterface
	 */
	public function findEnterprise($enterpriseId)
	{
        $sql = " SELECT * FROM MV_Enterprise WHERE EnterpriseId = :EnterpriseId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定事業者IDの事業者データを取得する。
	 *
	 * @param string $enterpriseId 事業者ID
	 * @return ResultInterface
	 */
	public function findEnterprise2($enterpriseId)
	{
	    return $this->findEnterprise($enterpriseId);
	}

}
