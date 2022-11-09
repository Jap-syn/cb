<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewSite
{
	protected $_name = 'MV_Site';
	protected $_primary = 'SiteId';
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
	 * サイトデータを取得する
	 *
	 * @param int $siteId
	 * @return ResultInterface
	 */
	public function find($siteId)
	{
	    $sql  = " SELECT * FROM MV_Site WHERE SiteId = :SiteId ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':SiteId' => $siteId,
	    );

	    return $stm->execute($prm);
	}


}
