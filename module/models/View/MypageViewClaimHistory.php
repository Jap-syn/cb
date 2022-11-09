<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewClaimHistory
{
	protected $_name = 'MV_ClaimHistory';
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
	 * 請求履歴データを取得する
	 *
	 * @param int $seq
	 * @return ResultInterface
	 */
	public function find($seq)
	{
	    $sql  = " SELECT * FROM MV_ClaimHistory WHERE Seq = :Seq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':Seq' => $seq,
	    );

	    return $stm->execute($prm);
	}

}
