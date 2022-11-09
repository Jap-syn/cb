<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewOemClaimAccountInfo
{
	protected $_name = 'MV_OemClaimAccountInfo';
	protected $_primary = 'ClaimAccountSeq';
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
	 * OEM請求口座データを取得する
	 *
	 * @param int $claimAccountSeq
	 * @return ResultInterface
	 */
	public function find($claimAccountSeq)
	{
	    $sql  = " SELECT * FROM MV_OemClaimAccountInfo WHERE ClaimAccountSeq = :ClaimAccountSeq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':ClaimAccountSeq' => $claimAccountSeq,
	    );

	    return $stm->execute($prm);
	}

	/**
	 * 指定の注文に関連付けられているすべてのOEM請求口座データを取得する
	 *
	 * @param int $oseq 注文SEQ
	 * @param null | string $order ソート順指定。'asc'または'desc'を指定可能。省略時は'desc'
	 * @return ResultInterface
	 */
	public function findByOrderSeq($oseq, $order = 'desc')
	{
	    $order = strtolower((string)$order);
	    if(!in_array($order, array('asc', 'desc'))) $order = 'desc';
	    $sql = " SELECT * FROM MV_OemClaimAccountInfo WHERE OrderSeq = :OrderSeq ORDER BY ClaimAccountSeq " . $order;

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':OrderSeq' => $oseq,
	    );

	    return $stm->execute($prm);
	}

}
