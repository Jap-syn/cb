<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_CjResultテーブルへのアダプタ
 */
class TableCjResult
{
	protected $_name = 'T_CjResult';
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
	 * 指定条件（AND）でデータを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @param boolean $isAsc プライマリキーのオーダー
	 * @return ResultInterface
	 */
	public function findCjResult($conditionArray, $isAsc = false)
	{
        $prm = array();
        $sql  = " SELECT * FROM T_CjResult WHERE 1 = 1 ";
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
        $sql  = " INSERT INTO T_CjResult (OrderSeq, OrderId, SendDate, ReceiveDate, TotalScore, Status, Result, TotalScoreWeighting, AggregationLevel0Cnt, AggregationLevel1Cnt, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :OrderId ";
        $sql .= " , :SendDate ";
        $sql .= " , :ReceiveDate ";
        $sql .= " , :TotalScore ";
        $sql .= " , :Status ";
        $sql .= " , :Result ";
        $sql .= " , :TotalScoreWeighting ";
        $sql .= " , :AggregationLevel0Cnt ";
        $sql .= " , :AggregationLevel1Cnt ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':OrderId' => $data['OrderId'],
                ':SendDate' => $data['SendDate'],
                ':ReceiveDate' => $data['ReceiveDate'],
                ':TotalScore' => $data['TotalScore'],
                ':Status' => $data['Status'],
                ':Result' => isset($data['Result']) ? $data['Result'] : 0,
                ':TotalScoreWeighting' => $data['TotalScoreWeighting'],
                ':AggregationLevel0Cnt' => $data['AggregationLevel0Cnt'],
                ':AggregationLevel1Cnt' => $data['AggregationLevel1Cnt'],
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
        $sql = " SELECT * FROM T_CjResult WHERE Seq = :Seq ";

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

        $sql  = " UPDATE T_CjResult ";
        $sql .= " SET ";
        $sql .= "     OrderSeq = :OrderSeq ";
        $sql .= " ,   OrderId = :OrderId ";
        $sql .= " ,   SendDate = :SendDate ";
        $sql .= " ,   ReceiveDate = :ReceiveDate ";
        $sql .= " ,   TotalScore = :TotalScore ";
        $sql .= " ,   Status = :Status ";
        $sql .= " ,   Result = :Result ";
        $sql .= " ,   TotalScoreWeighting = :TotalScoreWeighting ";
        $sql .= " ,   AggregationLevel0Cnt = :AggregationLevel0Cnt ";
        $sql .= " ,   AggregationLevel1Cnt = :AggregationLevel1Cnt ";
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
                ':OrderId' => $row['OrderId'],
                ':SendDate' => $row['SendDate'],
                ':ReceiveDate' => $row['ReceiveDate'],
                ':TotalScore' => $row['TotalScore'],
                ':Status' => $row['Status'],
                ':Result' => $row['Result'],
                ':TotalScoreWeighting' => $row['TotalScoreWeighting'],
                ':AggregationLevel0Cnt' => $row['AggregationLevel0Cnt'],
                ':AggregationLevel1Cnt' => $row['AggregationLevel1Cnt'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
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
        $sql  = " SELECT * FROM T_CjResult WHERE 1 = 1 ";
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

            $sql  = " UPDATE T_CjResult ";
            $sql .= " SET ";
            $sql .= "     OrderSeq    = :OrderSeq ";
            $sql .= " ,   OrderId     = :OrderId ";
            $sql .= " ,   SendDate    = :SendDate ";
            $sql .= " ,   ReceiveDate = :ReceiveDate ";
            $sql .= " ,   TotalScore  = :TotalScore ";
            $sql .= " ,   Status      = :Status ";
            $sql .= " ,   Result      = :Result ";
            $sql .= " ,   TotalScoreWeighting    = :TotalScoreWeighting ";
            $sql .= " ,   AggregationLevel0Cnt   = :AggregationLevel0Cnt ";
            $sql .= " ,   AggregationLevel1Cnt   = :AggregationLevel1Cnt ";
            $sql .= " ,   RegistDate  = :RegistDate ";
            $sql .= " ,   RegistId    = :RegistId ";
            $sql .= " ,   UpdateDate  = :UpdateDate ";
            $sql .= " ,   UpdateId    = :UpdateId ";
            $sql .= " ,   ValidFlg    = :ValidFlg ";
            $sql .= " WHERE Seq       = :Seq ";

            $stm = $this->_adapter->query($sql);

            $prm = array(
                    ':OrderSeq' => $row['OrderSeq'],
                    ':OrderId' => $row['OrderId'],
                    ':SendDate' => $row['SendDate'],
                    ':ReceiveDate' => $row['ReceiveDate'],
                    ':TotalScore' => $row['TotalScore'],
                    ':Status' => $row['Status'],
                    ':Result' => $row['Result'],
                    ':TotalScoreWeighting' => $row['TotalScoreWeighting'],
                    ':AggregationLevel0Cnt' => $row['AggregationLevel0Cnt'],
                    ':AggregationLevel1Cnt' => $row['AggregationLevel1Cnt'],
                    ':Seq' => $row['Seq'],
                    ':RegistDate' => $row['RegistDate'],
                    ':RegistId' => $row['RegistId'],
                    ':UpdateDate' => date('Y-m-d H:i:s'),
                    ':UpdateId' => $row['UpdateId'],
                    ':ValidFlg' => $row['ValidFlg'],
            );

            $ri = $stm->execute($prm);
        }
	}

	/**
	 * ステータス1に更新する
	 * @param $opId 承認担当者
	 */
	public function setStatusGetdata($opId)
	{
        $sql = " UPDATE T_CjResult SET Status = 1, UpdateDate = :UpdateDate, UpdateId = :UpdateId WHERE Status = 0 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $opId,
        );

        return $stm->execute($prm);

	}

	/**
	 * 指定のデータをステータスxxに更新する
	 *
	 * @param int $order_seq 注文SEQ
	 * @param int $status ステータス
	 * @param $opId 承認担当者
	 */
	public function setStatusUpdateSend($order_seq, $status, $opId)
	{
        $ri = $this->findByOrderSeq($order_seq);
        if ($ri == null) { return; }
        if (!($ri->count() > 0)) { return; }

        $data["Status"] = $status;
        $data["SendDate"] = date('Y-m-d H:i:s');

        $sql  = " UPDATE T_CjResult ";
        $sql .= " SET ";
        $sql .= "     SendDate    = :SendDate ";
        $sql .= " ,   Status      = :Status ";
        $sql .= " ,   UpdateDate  = :UpdateDate ";
        $sql .= " ,   UpdateId    = :UpdateId ";
        $sql .= " WHERE Seq       = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SendDate' => $data["SendDate"],
                ':Status' => $data["Status"],
                ':Seq' =>  $ri->current()['Seq'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $opId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定のデータをステータス9に更新し、スコア、Resultも更新する
	 *
	 * @param $_db
	 * @param int $order_seq 対象の注文SEQ
	 * @param int result 結果値
	 * @param $opId 承認担当者
	 * @param $aggregationLevel0Cnt 同一顧客数
	 * @param $aggregationLevel1Cnt 関連顧客数
	 * @param null | int $score スコア
	 */
	public function setStatusUpdateReception($order_seq, $result, $opId, $aggregationLevel0Cnt, $aggregationLevel1Cnt, $score = null)
	{
        $ri = $this->findByOrderSeq($order_seq);
        if ($ri == null) { return; }
        if (!($ri->count() > 0)) { return; }

        $data["Status"] = 9;
        $data["Result"] = $result;
        $data["ReceiveDate"] = date('Y-m-d H:i:s');
        if($score !== null) {
            $data['TotalScore'] = (int)$score;
        }

        $sql  = " UPDATE T_CjResult ";
        $sql .= " SET ";
        $sql .= "     ReceiveDate = :ReceiveDate ";
        $sql .= " ,   TotalScore  = :TotalScore ";
        $sql .= " ,   Status      = :Status ";
        $sql .= " ,   Result      = :Result ";
        $sql .= " ,   AggregationLevel0Cnt = :AggregationLevel0Cnt ";
        $sql .= " ,   AggregationLevel1Cnt = :AggregationLevel1Cnt ";
        $sql .= " ,   UpdateDate  = :UpdateDate ";
        $sql .= " ,   UpdateId    = :UpdateId ";
        $sql .= " WHERE Seq       = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ReceiveDate' => $data["ReceiveDate"],
                ':TotalScore' => $data['TotalScore'],
                ':Status' => $data["Status"],
                ':Result' => $data["Result"],
                ':AggregationLevel0Cnt' => $aggregationLevel0Cnt,
                ':AggregationLevel1Cnt' => $aggregationLevel1Cnt,
                ':Seq' => $ri->current()['Seq'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $opId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定注文SEQに関連付けられたT_CjResultの最新行を取得する
	 *
	 * @param int $order_seq 注文SEQ
	 * @return ResultInterface
	 */
	public function findByOrderSeq($order_seq)
	{
        $sql  = " SELECT * FROM T_CjResult WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $order_seq,
        );

        return $stm->execute($prm);
	}

	/**
	 * 複数条件のOrderSeqを検索する
	 *
	 * @param string $orderSeq ,で区切りられた条件
	 * @return ResultInterface
	 */
	public function orderSeqSearch($orderSeq)
	{
	    $seqs = array();
        foreach(explode(',', $orderSeq) as $seq) {
            if (!is_numeric(trim($seq))) continue;
            $seqs[] = trim($seq);
        }
        if (empty($seqs)) $seqs[] = 0;

        $phraseIn = MakeQueryValStrPhraseIn($seqs);

        $sql  = " SELECT DISTINCT OrderSeq, OrderId, TotalScore, TotalScoreWeighting FROM T_CjResult WHERE OrderSeq IN (" . $phraseIn . ") ORDER BY Seq DESC ";
        return $this->_adapter->query($sql)->execute(null);
	}
}
