<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewCustomer
{
	protected $_name = 'MV_Customer';
	protected $_primary = 'CustomerId';
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
	 * 指定条件（AND）の購入者データを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @param boolean $isAsc プライマリキーのオーダー
	 * @return ResultInterface
	 */
	public function findCustomer($conditionArray, $isAsc = false)
	{
	    $prm = array();
	    $sql  = " SELECT * FROM MV_Customer WHERE 1 = 1 ";
	    foreach ($conditionArray as $key => $value) {
	        $sql .= (" AND " . $key . " = :" . $key);
	        $prm += array(':' . $key => $value);
	    }
	    $sql .= " ORDER BY CustomerId " . ($isAsc ? "asc" : "desc");

	    $stm = $this->_adapter->query($sql);

	    return $stm->execute($prm);
	}
}
