<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_MypageToBackIFテーブルへのアダプタ
 */
class TableMypageToBackIF
{
	protected $_name = 'T_MypageToBackIF';
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
	    $sql  = " SELECT * FROM T_MypageToBackIF WHERE Seq = :Seq ";

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
	public function findMypageToBackIF($conditionArray, $isAsc = false)
	{
        $prm = array();
        $sql  = " SELECT * FROM T_MypageToBackIF WHERE 1 = 1 AND ValidFlg = 1 ";
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
        $sql  = " INSERT INTO T_MypageToBackIF (Status, Reason, IFClass, IFData, OrderSeq, ManCustId, CustomerId, RegistDate, UpdateDate, ValidFlg) VALUES (";
        $sql .= "   :Status ";
        $sql .= " , :Reason ";
        $sql .= " , :IFClass ";
        $sql .= " , :IFData ";
        $sql .= " , :OrderSeq ";
        $sql .= " , :ManCustId ";
        $sql .= " , :CustomerId ";
        $sql .= " , :RegistDate ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Status' => isset($data['Status']) ? $data['Status'] : 0,
                ':Reason' => $data['Reason'],
                ':IFClass' => $data['IFClass'],
                ':IFData' => $data['IFData'],
                ':OrderSeq' => $data['OrderSeq'],
                ':ManCustId' => $data['ManCustId'],
                ':CustomerId' => $data['CustomerId'],
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
        $sql = " SELECT * FROM T_MypageToBackIF WHERE Seq = :Seq ";

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

        $sql  = " UPDATE T_MypageToBackIF ";
        $sql .= " SET ";
        $sql .= "     Seq = :Seq ";
        $sql .= " ,   Status = :Status ";
        $sql .= " ,   Reason = :Reason ";
        $sql .= " ,   IFClass = :IFClass ";
        $sql .= " ,   IFData = :IFData ";
        $sql .= " ,   OrderSeq = :OrderSeq ";
        $sql .= " ,   ManCustId = :ManCustId ";
        $sql .= " ,   CustomerId = :CustomerId ";
        $sql .= " ,   MailFlg = :MailFlg ";
        $sql .= " ,   MailRetryCount = :MailRetryCount ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':Status' => $row['Status'],
                ':Reason' => $row['Reason'],
                ':IFClass' => $row['IFClass'],
                ':IFData' => $row['IFData'],
                ':OrderSeq' => $row['OrderSeq'],
                ':ManCustId' => $row['ManCustId'],
                ':CustomerId' => $row['CustomerId'],
                ':MailFlg' => $row['MailFlg'],
                ':MailRetryCount' => $row['MailRetryCount'],
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
	    $sql  = " SELECT * FROM T_MypageToBackIF WHERE 1 = 1 ";
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

	/**
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param int $seq 更新するSeq
	 */
	public function saveUpdateOrCreate($data, $orderSeq)
	{
        $sql = " SELECT * FROM T_MypageToBackIF WHERE IFClass=4 AND OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':OrderSeq' => $orderSeq,
        );

        $row = $stm->execute($prm)->current();
        if ($row) {
            foreach ($data as $key => $value)
            {
                if (array_key_exists($key, $row))
                {
                    $row[$key] = $value;
                }
            }
    
            $sql  = " UPDATE T_MypageToBackIF ";
            $sql .= " SET ";
            $sql .= "     Seq = :Seq ";
            $sql .= " ,   Status = :Status ";
            $sql .= " ,   Reason = :Reason ";
            $sql .= " ,   IFClass = :IFClass ";
            $sql .= " ,   IFData = :IFData ";
            $sql .= " ,   OrderSeq = :OrderSeq ";
            $sql .= " ,   ManCustId = :ManCustId ";
            $sql .= " ,   CustomerId = :CustomerId ";
            $sql .= " ,   MailFlg = :MailFlg ";
            $sql .= " ,   MailRetryCount = :MailRetryCount ";
            $sql .= " ,   RegistDate = :RegistDate ";
            $sql .= " ,   UpdateDate = :UpdateDate ";
            $sql .= " ,   ValidFlg = :ValidFlg ";
            $sql .= " WHERE Seq = :Seq ";
    
            $stm = $this->_adapter->query($sql);
    
            $prm = array(
                    ':Seq' => $row['Seq'],
                    ':Status' => $row['Status'],
                    ':Reason' => $row['Reason'],
                    ':IFClass' => $row['IFClass'],
                    ':IFData' => $row['IFData'],
                    ':OrderSeq' => $row['OrderSeq'],
                    ':ManCustId' => $row['ManCustId'],
                    ':CustomerId' => $row['CustomerId'],
                    ':MailFlg' => $row['MailFlg'],
                    ':MailRetryCount' => $row['MailRetryCount'],
                    ':RegistDate' => $row['RegistDate'],
                    ':UpdateDate' => date('Y-m-d H:i:s'),
                    ':ValidFlg' => $row['ValidFlg'],
            );
    
            return $stm->execute($prm);
        } else {
            $this->saveNew($data);
        }
    }

}
