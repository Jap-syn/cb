<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_ReceiptIssueHistory(領収書発行履歴)テーブルへのアダプタ
 */
class TableReceiptIssueHistory
{
    protected $_name = 'T_ReceiptIssueHistory';
    protected $_primary = array ('Seq');
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
     * 領収書発行履歴データを取得する
     *
     * @param int $seq 管理SEQ
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM T_ReceiptIssueHistory WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
        );

        return $stm->execute($prm);
    }

    /**
     * 領収書発行履歴データを取得する（注文SEQ単位プライマリ昇順）
     *
     * @param int $seq 管理SEQ
     * @return ResultInterface
     */
    public function findOrderSeq($OrderSeq)
    {
        $sql = " SELECT *";
        $sql = " FROM T_ReceiptIssueHistory";
        $sql = " WHERE OrderSeq = :OrderSeq";
        $sql = " ORDER BY Seq ASC";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $OrderSeq,
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
        $sql  = " INSERT INTO T_ReceiptIssueHistory (OrderSeq, ReceiptIssueDate, RegistDate, RegistId)";
        $sql .= " VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :ReceiptIssueDate ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':ReceiptIssueDate' => $data['ReceiptIssueDate'],
                ':RegistDate' => $data['RegistDate'],
                ':RegistId' => $data['RegistId'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * updateValidFlg
     *
     * @param int $OrderSeq ユーザーID
     * @param int $ValidFlg
     * @return ResultInterface
     */
    public function updateValidFlg($OrderSeq, $ValidFlg)
    {
        $sql  = " UPDATE T_ReceiptIssueHistory ";
        $sql .= " SET ";
        $sql .= "     ValidFlg = :ValidFlg ";
        $sql .= " WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $OrderSeq,
                ':ValidFlg' => $ValidFlg,
        );

        return $stm->execute($prm);
    }
}