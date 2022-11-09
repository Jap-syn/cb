<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_JtcResultテーブルへのアダプタ
 */
class TableJtcResult
{
    protected $_name = 'T_JtcResult';
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
     * ジンテック結果詳細データを取得する
     *
     * @param int $seq SEQ
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM T_JtcResult WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
        );

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
        $sql  = " INSERT INTO T_JtcResult (OrderSeq, SendDate, ReceiveDate, Status, Result, JintecManualJudgeFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :SendDate ";
        $sql .= " , :ReceiveDate ";
        $sql .= " , :Status ";
        $sql .= " , :Result ";
        $sql .= " , :JintecManualJudgeFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':OrderSeq' => $data['OrderSeq'],
            ':SendDate' => $data['SendDate'],
            ':ReceiveDate' => $data['ReceiveDate'],
            ':Status' => $data['Status'],
            ':Result' => $data['Result'],
            ':JintecManualJudgeFlg' => $data['JintecManualJudgeFlg'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $seq SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $seq)
    {
        $row = $this->find($seq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_JtcResult ";
        $sql .= " SET ";
        $sql .= "     OrderSeq = :OrderSeq ";
        $sql .= " ,   SendDate = :SendDate ";
        $sql .= " ,   ReceiveDate = :ReceiveDate ";
        $sql .= " ,   Status = :Status ";
        $sql .= " ,   Result = :Result ";
        $sql .= " ,   JintecManualJudgeFlg = :JintecManualJudgeFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':OrderSeq' => $row['OrderSeq'],
            ':SendDate' => $row['SendDate'],
            ':ReceiveDate' => $row['ReceiveDate'],
            ':Status' => $row['Status'],
            ':Result' => $row['Result'],
            ':JintecManualJudgeFlg' => $row['JintecManualJudgeFlg'],
            ':Seq' => $row['Seq'],
        );

        return $stm->execute($prm);
    }

    /**
     * 指定注文SEQに関連付けられたT_JtcResultの最新行を取得する
     *
     * @param int $order_seq 注文SEQ
     * @return ResultInterface
     */
    public function findByOrderSeq($order_seq)
    {
        $sql  = " SELECT * FROM T_JtcResult WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC LIMIT 1 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $order_seq,
        );

        return $stm->execute($prm);
    }

}
