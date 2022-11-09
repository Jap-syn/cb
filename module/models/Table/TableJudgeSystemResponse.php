<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_JudgeSystemResponse(審査システム応答)テーブルへのアダプタ
 */
class TableJudgeSystemResponse
{
    protected $_name = 'T_JudgeSystemResponse';
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
     * 審査システム応答データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $seq シーケンス
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM T_JudgeSystemResponse WHERE ValidFlg = 1 AND Seq = :Seq ";

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
        $sql  = " INSERT INTO T_JudgeSystemResponse (SendDate, ReceiveDate, Status, AcceptNumber, ConfirmStatus, JudgeClass, SentRawData, ReceivedRawData, Reserve, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :SendDate ";
        $sql .= " , :ReceiveDate ";
        $sql .= " , :Status ";
        $sql .= " , :AcceptNumber ";
        $sql .= " , :ConfirmStatus ";
        $sql .= " , :JudgeClass ";
        $sql .= " , :SentRawData ";
        $sql .= " , :ReceivedRawData ";
        $sql .= " , :Reserve ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SendDate' => $data['SendDate'],
                ':ReceiveDate' => $data['ReceiveDate'],
                ':Status' => $data['Status'],
                ':AcceptNumber' => $data['AcceptNumber'],
                ':ConfirmStatus' => $data['ConfirmStatus'],
                ':JudgeClass' => $data['JudgeClass'],
                ':SentRawData' => $data['SentRawData'],
                ':ReceivedRawData' => $data['ReceivedRawData'],
                ':Reserve' => $data['Reserve'],
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

        $sql  = " UPDATE T_JudgeSystemResponse ";
        $sql .= " SET ";
        $sql .= "     SendDate = :SendDate ";
        $sql .= " ,   ReceiveDate = :ReceiveDate ";
        $sql .= " ,   Status = :Status ";
        $sql .= " ,   AcceptNumber = :AcceptNumber ";
        $sql .= " ,   ConfirmStatus = :ConfirmStatus ";
        $sql .= " ,   JudgeClass = :JudgeClass ";
        $sql .= " ,   SentRawData = :SentRawData ";
        $sql .= " ,   ReceivedRawData = :ReceivedRawData ";
        $sql .= " ,   Reserve = :Reserve ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':SendDate' => $row['SendDate'],
                ':ReceiveDate' => $row['ReceiveDate'],
                ':Status' => $row['Status'],
                ':AcceptNumber' => $row['AcceptNumber'],
                ':ConfirmStatus' => $row['ConfirmStatus'],
                ':JudgeClass' => $row['JudgeClass'],
                ':SentRawData' => $row['SentRawData'],
                ':ReceivedRawData' => $row['ReceivedRawData'],
                ':Reserve' => $row['Reserve'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 与信審査結果の顧客情報編集結果を未確認のデータを取得
     *
     * @return ResultInterface
     */
    public function getUnconfirmedData() {
        $sql = " SELECT * FROM T_JudgeSystemResponse WHERE ValidFlg = 1 AND ConfirmStatus = 1 AND JudgeClass = 1 ";

        $stm = $this->_adapter->query($sql);

        return $stm->execute(null);
    }
}
