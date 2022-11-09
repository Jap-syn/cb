<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_OperationLog(操作ログ)テーブルへのアダプタ
 */
class TableOperationLog
{
    protected $_name = 'T_OperationLog';
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
     * 料金プランマスターデータを取得する
     *
     * @param int $seq シーケンス
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM T_OperationLog WHERE Seq = :Seq ";

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
        $sql  = " INSERT INTO T_OperationLog (Module, OperationTime, OperationContent, Url, Paramter, UserId, UserName, IPAddress, Note, RegistDate, RegistId) VALUES (";
        $sql .= "   :Module ";
        $sql .= " , :OperationTime ";
        $sql .= " , :OperationContent ";
        $sql .= " , :Url ";
        $sql .= " , :Paramter ";
        $sql .= " , :UserId ";
        $sql .= " , :UserName ";
        $sql .= " , :IPAddress ";
        $sql .= " , :Note ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Module' => $data['Module'],
                ':OperationTime' => $data['OperationTime'],
                ':OperationContent' => $data['OperationContent'],
                ':Url' => $data['Url'],
                ':Paramter' => $data['Paramter'],
                ':UserId' => $data['UserId'],
                ':UserName' => $data['UserName'],
                ':IPAddress' => $data['IPAddress'],
                ':Note' => $data['Note'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
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

        $sql  = " UPDATE T_OperationLog ";
        $sql .= " SET ";
        $sql .= "     Module = :Module ";
        $sql .= " ,   OperationTime = :OperationTime ";
        $sql .= " ,   OperationContent = :OperationContent ";
        $sql .= " ,   Url = :Url ";
        $sql .= " ,   Paramter = :Paramter ";
        $sql .= " ,   UserId = :UserId ";
        $sql .= " ,   UserName = :UserName ";
        $sql .= " ,   IPAddress = :IPAddress ";
        $sql .= " ,   Note = :Note ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':Module' => $row['Module'],
                ':OperationTime' => $row['OperationTime'],
                ':OperationContent' => $row['OperationContent'],
                ':Url' => $row['Url'],
                ':Paramter' => $row['Paramter'],
                ':UserId' => $row['UserId'],
                ':UserName' => $row['UserName'],
                ':IPAddress' => $row['IPAddress'],
                ':Note' => $row['Note'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
        );

        return $stm->execute($prm);
    }
}
