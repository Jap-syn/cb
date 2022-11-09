<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_ToBackMypageIFテーブルへのアダプタ
 */
class TableToBackMypageIF
{
	protected $_name = 'T_ToBackMypageIF';
	protected $_primary = array('Seq');
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
	 * 基幹反映指示インタフェースデータを取得する
	 *
	 * @param int $seq
	 * @return ResultInterface
	 */
	public function find($seq)
	{
	    $sql  = " SELECT * FROM T_ToBackMypageIF WHERE Seq = :Seq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':Seq' => $seq,
	    );

	    return $stm->execute($prm);
	}

	/**
	 * 指定条件（AND）の基幹反映指示インタフェースデータを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @param boolean $isAsc プライマリキーのオーダー
	 * @return ResultInterface
	 */
	public function findToBackMypageIF($conditionArray, $isAsc = false)
	{
        $prm = array();
        $sql  = " SELECT * FROM T_ToBackMypageIF WHERE 1 = 1 AND ValidFlg = 1 AND IFClass IN (1, 2) ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }
        $sql .= " ORDER BY Seq " . ($isAsc ? "asc" : "desc");

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
	}

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO T_ToBackMypageIF (Status, IFClass, IFData, OrderSeq, RegistDate, UpdateDate, ValidFlg) VALUES (";
        $sql .= "   :Status ";
        $sql .= " , :IFClass ";
        $sql .= " , :IFData ";
        $sql .= " , :OrderSeq ";
        $sql .= " , :RegistDate ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Status' => isset($data['Status']) ? $data['Status'] : 0,
                ':IFClass' => $data['IFClass'],
                ':IFData' => $data['IFData'],
                ':OrderSeq' => $data['OrderSeq'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

	/**
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param int $seq 更新するSeq
	 */
	public function saveUpdate($data, $seq)
	{
        $sql = " SELECT * FROM T_ToBackMypageIF WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_ToBackMypageIF ";
        $sql .= " SET ";
        $sql .= "     Seq = :Seq ";
        $sql .= " ,   Status = :Status ";
        $sql .= " ,   IFClass = :IFClass ";
        $sql .= " ,   IFData = :IFData ";
        $sql .= " ,   OrderSeq = :OrderSeq ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':Status' => $row['Status'],
                ':IFClass' => $row['IFClass'],
                ':IFData' => $row['IFData'],
                ':OrderSeq' => $row['OrderSeq'],
                ':RegistDate' => $row['RegistDate'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定された条件でレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param array $conditionArray
	 */
	public function saveUpdateWhere($data, $conditionArray)
	{
	    $prm = array();
	    $sql  = " SELECT * FROM T_ToBackMypageIF WHERE 1 = 1 ";
	    foreach ($conditionArray as $key => $value) {
	        $sql .= (" AND " . $key . " = :" . $key);
	        $prm += array(':' . $key => $value);
	    }

	    $stm = $this->_adapter->query($sql);

	    $ri = $stm->execute($prm);

	    foreach ($ri AS $row) {
	        foreach ($data as $key => $value) {
	            if (array_key_exists($key, $row)) {
	                $row[$key] = $value;
	            }
	        }

	        // 指定されたレコードを更新する
	        $this->saveUpdate($row, $row['Seq']);
	    }
	}

}
