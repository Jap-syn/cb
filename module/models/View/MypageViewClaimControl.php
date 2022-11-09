<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewClaimControl
{
	protected $_name = 'MV_ClaimControl';
	protected $_primary = 'ClaimId';
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

}
