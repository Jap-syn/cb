<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_CjMailHistoryテーブルへのアダプタ
 */
class TableCjMailHistory
{
	protected $_name = 'T_CjMailHistory';
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
	public function findCjMailHistory($conditionArray, $isAsc = false)
	{
        $prm = array();
        $sql  = " SELECT * FROM T_CjMailHistory WHERE 1 = 1 ";
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
        $sql  = " INSERT INTO T_CjMailHistory (OrderSeq, RegistDate, OccReason, SendMailFlg, ProcessingDate, MailTo, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :RegistDate ";
        $sql .= " , :OccReason ";
        $sql .= " , :SendMailFlg ";
        $sql .= " , :ProcessingDate ";
        $sql .= " , :MailTo ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':OccReason' => $data['OccReason'],
                ':SendMailFlg' => $data['SendMailFlg'],
                ':ProcessingDate' => $data['ProcessingDate'],
                ':MailTo' => $data['MailTo'],
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
        $sql = " SELECT * FROM T_CjMailHistory WHERE Seq = :Seq ";

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

        $sql  = " UPDATE T_CjMailHistory ";
        $sql .= " SET ";
        $sql .= "     OrderSeq = :OrderSeq ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   OccReason = :OccReason ";
        $sql .= " ,   SendMailFlg = :SendMailFlg ";
        $sql .= " ,   ProcessingDate = :ProcessingDate ";
        $sql .= " ,   MailTo = :MailTo ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':OrderSeq' => $row['OrderSeq'],
                ':RegistDate' => $row['RegistDate'],
                ':OccReason' => $row['OccReason'],
                ':SendMailFlg' => $row['SendMailFlg'],
                ':ProcessingDate' => $row['ProcessingDate'],
                ':MailTo' => $row['MailTo'],
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
        $sql  = " SELECT * FROM T_CjMailHistory WHERE 1 = 1 ";
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

            $sql  = " UPDATE T_CjMailHistory ";
            $sql .= " SET ";
            $sql .= "     OrderSeq       = :OrderSeq ";
            $sql .= " ,   RegistDate     = :RegistDate ";
            $sql .= " ,   OccReason      = :OccReason ";
            $sql .= " ,   SendMailFlg    = :SendMailFlg ";
            $sql .= " ,   ProcessingDate = :ProcessingDate ";
            $sql .= " ,   MailTo         = :MailTo ";
            $sql .= " ,   UpdateDate     = :UpdateDate ";
            $sql .= " ,   UpdateId       = :UpdateId ";
            $sql .= " WHERE Seq          = :Seq ";

            $stm = $this->_adapter->query($sql);

            $prm = array(
                    ':OrderSeq' => $row['OrderSeq'],
                    ':RegistDate' => $row['RegistDate'],
                    ':OccReason' => $row['OccReason'],
                    ':SendMailFlg' => $row['SendMailFlg'],
                    ':ProcessingDate' => $row['ProcessingDate'],
                    ':MailTo' => $row['MailTo'],
                    ':Seq' => $row['Seq'],
                    ':UpdateDate' => date('Y-m-d H:i:s'),
                    ':UpdateId' => $row['UpdateId'],
            );

            $ri = $stm->execute($prm);
        }
	}

	/**
	 * メール送信予約
	 *
	 * @param int $orderSeq 注文ID
	 * @param int $reason 発生理由
	 * @param $opId 承認担当者
	 */
	public function rsvCjMail($orderSeq, $reason, $opId)
	{
	    // すでに送信予約があればメール送信フラグ=9でクローズ
        $this->doneCjMail($orderSeq, 9, 'N/A', $opId);

        $data["OrderSeq"] = $orderSeq;
        $data["RegistDate"] = date("Y-m-d H:i:s");
        $data["OccReason"] = $reason;
        $data["SendMailFlg"] = 0;
        $data['RegistId'] = $opId;
        $data['UpdateId'] = $opId;

        $this->saveNew($data);
	}

	/**
	 * メール送信完了
	 *
	 * @param int $orderSeq 注文ID
	 * @param int $sendMailFlg 送信結果フラグ
	 * @param string $mailTo 送信先メールアドレス
	 * @param $opId 承認担当者
	 */
	public function doneCjMail($orderSeq, $sendMailFlg, $mailTo, $opId)
	{
        $seqs = $this->_adapter->query(" SELECT IFNULL(GROUP_CONCAT(Seq),'0') AS Seqs FROM T_CjMailHistory WHERE OrderSeq = :OrderSeq AND SendMailFlg = 0 "
            )->execute(array(':OrderSeq' => $orderSeq))->current()['Seqs'];

        $sql = <<<EOQ
UPDATE T_CjMailHistory
SET
    SendMailFlg    = :SendMailFlg
,   ProcessingDate = :ProcessingDate
,   MailTo         = :MailTo
,   UpdateDate     = :UpdateDate
,   UpdateId       = :UpdateId
WHERE  Seq IN ($seqs)
EOQ;

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SendMailFlg' => $sendMailFlg,
                ':ProcessingDate' => date("Y-m-d H:i:s"),
                ':MailTo' => $mailTo,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $opId,
        );

        return $stm->execute($prm);
	}

	/**
	 * メール送信対象取得
     *
     * @return ResultInterface
	 */
	public function getMailTargets()
	{
	    $sql = " SELECT * FROM T_CjMailHistory WHERE SendMailFlg = 0 ";
        return $this->_adapter->query($sql)->execute(null);
	}

}
