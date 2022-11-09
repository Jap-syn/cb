<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_CreditOkTicketテーブルへのアダプタ
 */
class TableCreditOkTicket
{
    protected $_name = 'T_CreditOkTicket';
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
     * OKチケットデータを取得する
     *
     * @param int $seq シーケンス
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM T_CreditOkTicket WHERE Seq = :Seq ";

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
        $sql  = " INSERT INTO T_CreditOkTicket (Seq, Status, EnterpriseId, OrderSeq, RegistDate, RegistOpId, ValidToDate, ReleaseDate, ReleaseOpId, UseOrderSeq, UseDate) VALUES (";
        $sql .= "   :Seq ";
        $sql .= " , :Status ";
        $sql .= " , :EnterpriseId ";
        $sql .= " , :OrderSeq ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistOpId ";
        $sql .= " , :ValidToDate ";
        $sql .= " , :ReleaseDate ";
        $sql .= " , :ReleaseOpId ";
        $sql .= " , :UseOrderSeq ";
        $sql .= " , :UseDate ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $data['Seq'],
                ':Status' => $data['Status'],
                ':EnterpriseId' => $data['EnterpriseId'],
                ':OrderSeq' => $data['OrderSeq'],
                ':RegistDate' => $data['RegistDate'],
                ':RegistOpId' => $data['RegistOpId'],
                ':ValidToDate' => $data['ValidToDate'],
                ':ReleaseDate' => $data['ReleaseDate'],
                ':ReleaseOpId' => $data['ReleaseOpId'],
                ':UseOrderSeq' => $data['UseOrderSeq'],
                ':UseDate' => $data['UseDate'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $seq シーケンス
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

        $sql  = " UPDATE T_CreditOkTicket ";
        $sql .= " SET ";
        $sql .= "     Status = :Status ";
        $sql .= " ,   EnterpriseId = :EnterpriseId ";
        $sql .= " ,   OrderSeq = :OrderSeq ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistOpId = :RegistOpId ";
        $sql .= " ,   ValidToDate = :ValidToDate ";
        $sql .= " ,   ReleaseDate = :ReleaseDate ";
        $sql .= " ,   ReleaseOpId = :ReleaseOpId ";
        $sql .= " ,   UseOrderSeq = :UseOrderSeq ";
        $sql .= " ,   UseDate = :UseDate ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $row['Seq'],
                ':Status' => $row['Status'],
                ':EnterpriseId' => $row['EnterpriseId'],
                ':OrderSeq' => $row['OrderSeq'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistOpId' => $row['RegistOpId'],
                ':ValidToDate' => $row['ValidToDate'],
                ':ReleaseDate' => $row['ReleaseDate'],
                ':ReleaseOpId' => $row['ReleaseOpId'],
                ':UseOrderSeq' => $row['UseOrderSeq'],
                ':UseDate' => $row['UseDate'],
        );

        return $stm->execute($prm);
    }

}
