<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

/**
 * M_CreditSystemInfoテーブルへのアダプタ
 */
class TableCreditSystemInfo
{
	protected $_name = 'M_CreditSystemInfo';
	protected $_primary = array('AutoCreditLimitAmount1');		// 暫定対応
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
	 * 社内与信システム情報を取得する
	 *
	 * @return ResultInterface
	 */
	public function find()
	{
	    $sql = " SELECT * FROM M_CreditSystemInfo ";

	    $stm = $this->_adapter->query($sql);

	    $prm = null;

	    return $stm->execute($prm);
	}

	/**
	 * レコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @return ResultInterface
	 */
	public function saveUpdate($data)
	{
	    $row = $this->find()->current();

	    foreach ($data as $key => $value)
	    {
	        if (array_key_exists($key, $row))
	        {
	            $row[$key] = $value;
	        }
	    }

	    $sql  = " UPDATE M_CreditSystemInfo ";
	    $sql .= " SET ";
	    $sql .= "     AutoCreditLimitAmount1 = :AutoCreditLimitAmount1 ";
	    $sql .= " ,   AutoCreditLimitAmount2 = :AutoCreditLimitAmount2 ";
	    $sql .= " ,   AutoCreditLimitAmount3 = :AutoCreditLimitAmount3 ";
	    $sql .= " ,   AutoCreditLimitAmount4 = :AutoCreditLimitAmount4 ";
	    $sql .= " ,   ClaimPastDays = :ClaimPastDays ";
	    $sql .= " ,   DeliveryPastDays = :DeliveryPastDays ";
	    $sql .= " ,   JintecManualJudgeSns = :JintecManualJudgeSns ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':AutoCreditLimitAmount1' => $row['AutoCreditLimitAmount1'],
	            ':AutoCreditLimitAmount2' => $row['AutoCreditLimitAmount2'],
	            ':AutoCreditLimitAmount3' => $row['AutoCreditLimitAmount3'],
	            ':AutoCreditLimitAmount4' => $row['AutoCreditLimitAmount4'],
	            ':ClaimPastDays' => $row['ClaimPastDays'],
	            ':DeliveryPastDays' => $row['DeliveryPastDays'],
	            ':JintecManualJudgeSns' => $row['JintecManualJudgeSns'],
	    );

	    return $stm->execute($prm);
	}

}
