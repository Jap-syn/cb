<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_StampFeeテーブルへのアダプタ
 */
class TableStampFee
{
	protected $_name = 'T_StampFee';
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
	 * 指定条件（AND）の印紙代管理データを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @param boolean $isAsc プライマリキーのオーダー
	 * @return ResultInterface
	 */
	public function findStampFee($conditionArray, $isAsc = false)
	{
        $prm = array();
        $sql  = " SELECT * FROM T_StampFee WHERE 1 = 1 ";
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
	 * @param int $orderSeq インサートする注文ID
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO T_StampFee (OrderSeq, DecisionDate, StampFee, ClearFlg, ClearDate, CancelFlg, PayingControlSeq, PayingControlStatus, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :DecisionDate ";
        $sql .= " , :StampFee ";
        $sql .= " , :ClearFlg ";
        $sql .= " , :ClearDate ";
        $sql .= " , :CancelFlg ";
        $sql .= " , :PayingControlSeq ";
        $sql .= " , :PayingControlStatus ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':DecisionDate' => $data['DecisionDate'],
                ':StampFee' => $data['StampFee'],
                ':ClearFlg' => $data['ClearFlg'],
                ':ClearDate' => $data['ClearDate'],
                ':CancelFlg' => $data['CancelFlg'],
                ':PayingControlSeq' => $data['PayingControlSeq'],
                ':PayingControlStatus' => isset($data['PayingControlStatus']) ? $data['PayingControlStatus'] : 0,
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
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
        $sql = " SELECT * FROM T_StampFee WHERE Seq = :Seq ";

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

        $sql  = " UPDATE T_StampFee ";
        $sql .= " SET ";
        $sql .= "     OrderSeq = :OrderSeq ";
        $sql .= " ,   DecisionDate = :DecisionDate ";
        $sql .= " ,   StampFee = :StampFee ";
        $sql .= " ,   ClearFlg = :ClearFlg ";
        $sql .= " ,   ClearDate = :ClearDate ";
        $sql .= " ,   CancelFlg = :CancelFlg ";
        $sql .= " ,   PayingControlSeq = :PayingControlSeq ";
        $sql .= " ,   PayingControlStatus = :PayingControlStatus ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':OrderSeq' => $row['OrderSeq'],
                ':DecisionDate' => $row['DecisionDate'],
                ':StampFee' => $row['StampFee'],
                ':ClearFlg' => $row['ClearFlg'],
                ':ClearDate' => $row['ClearDate'],
                ':CancelFlg' => $row['CancelFlg'],
                ':PayingControlSeq' => $row['PayingControlSeq'],
                ':PayingControlStatus' => $row['PayingControlStatus'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定された注文SEQの精算済みの印紙代を取得する。
	 *
	 * @param int $oseq 注文SEQ
	 * @return int 印紙代
	 */
	public function getClearStampFee($oseq)
	{
	    $ri = $this->findStampFee(array('OrderSeq' => $oseq, 'ClearFlg' => 1));
        return ($ri->count() > 0) ? $ri->current()['StampFee'] : 0;
	}

	/**
	 * 指定された注文SEQの印紙代を取得する。
	 *
	 * @param int $oseq 注文SEQ
	 * @return int 印紙代
	 */
	public function getStampFee($oseq)
	{
	    $ri = $this->findStampFee(array('OrderSeq' => $oseq));
        return ($ri->count() > 0) ? $ri->current()['StampFee'] : 0;
	}

	/**
	 * 指定注文SEQの印紙代データをキャンセル中にする。
	 *
	 * @param int $oseq 注文SEQ
	 * @param $opId 担当者
	 */
	public function toCanceling($oseq, $opId)
	{
		$this->setCancelFlg($oseq, 1, $opId);
	}

	/**
	 * 指定注文SEQの印紙代データをキャンセルにする。
	 *
	 * @param int $oseq 注文SEQ
	 * @param $opId 担当者
	 */
	public function toCanceled($oseq, $opId)
	{
		$this->setCancelFlg($oseq, 2, $opId);
	}

	/**
	 * 指定注文SEQの印紙代データのキャンセルフラッグを設定する。
	 *
	 * @param int $oseq 注文SEQ
	 * @param int $flg キャンセルフラッグ
	 * @param $opId 担当者
	 */
	public function setCancelFlg($oseq, $flg, $opId)
	{
        $sql = " UPDATE T_StampFee SET CancelFlg = :CancelFlg, UpdateDate = :UpdateDate, UpdateId = :UpdateId WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CancelFlg' => $flg,
                ':OrderSeq' => $oseq,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $opId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 精算済みにする
	 *
	 * @param int $seq 管理Seq
	 * @param string $date 精算日 'yyyy-MM-dd'書式で通知
	 * @param int $payingControlSeq 立替振込管理Seq
	 * @param $opId 担当者
	 */
	public function settleUp($seq, $date, $payingControlSeq, $opId)
	{
	    $data = array(
            'ClearFlg' => 1,
            'ClearDate' => $date,
            'PayingControlSeq' => $payingControlSeq,
            'UpdateId' => $opId,
	    );
        return $this->saveUpdate($data, $seq);
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
	    $sql  = " SELECT * FROM T_StampFee WHERE 1 = 1 ";
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
