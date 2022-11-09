<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_PushSms(PushSMS結果)テーブルへのアダプタ
 */
class TablePushSms
{
    protected $_name = 'T_PushSms';
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
     * PushSMS結果データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $seq SEQ
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM T_PushSms WHERE ValidFlg = 1 AND Seq = :Seq ";

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
        $sql  = " INSERT INTO T_PushSms (OrderSeq, PhoneNumber, CaririerId, MessageNumber, Message, ReferenceDate, SendDateTime, UseDateTime, CallPhoneNumber, Caririer, IncMessageNumber, IncMessage, State, ErrorCode, DeliveryState, DeliveryErrorCode, Status, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :PhoneNumber ";
        $sql .= " , :CaririerId ";
        $sql .= " , :MessageNumber ";
        $sql .= " , :Message ";
        $sql .= " , :ReferenceDate ";
        $sql .= " , :SendDateTime ";
        $sql .= " , :UseDateTime ";
        $sql .= " , :CallPhoneNumber ";
        $sql .= " , :Caririer ";
        $sql .= " , :IncMessageNumber ";
        $sql .= " , :IncMessage ";
        $sql .= " , :State ";
        $sql .= " , :ErrorCode ";
        $sql .= " , :DeliveryState ";
        $sql .= " , :DeliveryErrorCode ";
        $sql .= " , :Status ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':PhoneNumber' => $data['PhoneNumber'],
                ':CaririerId' => $data['CaririerId'],
                ':MessageNumber' => $data['MessageNumber'],
                ':Message' => $data['Message'],
                ':ReferenceDate' => $data['ReferenceDate'],
                ':SendDateTime' => $data['SendDateTime'],
                ':UseDateTime' => $data['UseDateTime'],
                ':CallPhoneNumber' => $data['CallPhoneNumber'],
                ':Caririer' => $data['Caririer'],
                ':IncMessageNumber' => $data['IncMessageNumber'],
                ':IncMessage' => $data['IncMessage'],
                ':State' => $data['State'],
                ':ErrorCode' => $data['ErrorCode'],
                ':DeliveryState' => $data['DeliveryState'],
                ':DeliveryErrorCode' => $data['DeliveryErrorCode'],
                ':Status' => isset($data['Status']) ? $data['Status'] : 0,
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

        $sql  = " UPDATE T_PushSms ";
        $sql .= " SET ";
        $sql .= "     OrderSeq = :OrderSeq ";
        $sql .= " ,   PhoneNumber = :PhoneNumber ";
        $sql .= " ,   CaririerId = :CaririerId ";
        $sql .= " ,   MessageNumber = :MessageNumber ";
        $sql .= " ,   Message = :Message ";
        $sql .= " ,   ReferenceDate = :ReferenceDate ";
        $sql .= " ,   SendDateTime = :SendDateTime ";
        $sql .= " ,   UseDateTime = :UseDateTime ";
        $sql .= " ,   CallPhoneNumber = :CallPhoneNumber ";
        $sql .= " ,   Caririer = :Caririer ";
        $sql .= " ,   IncMessageNumber = :IncMessageNumber ";
        $sql .= " ,   IncMessage = :IncMessage ";
        $sql .= " ,   State = :State ";
        $sql .= " ,   ErrorCode = :ErrorCode ";
        $sql .= " ,   DeliveryState = :DeliveryState ";
        $sql .= " ,   DeliveryErrorCode = :DeliveryErrorCode ";
        $sql .= " ,   Status = :Status ";
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
                ':PhoneNumber' => $row['PhoneNumber'],
                ':CaririerId' => $row['CaririerId'],
                ':MessageNumber' => $row['MessageNumber'],
                ':Message' => $row['Message'],
                ':ReferenceDate' => $row['ReferenceDate'],
                ':SendDateTime' => $row['SendDateTime'],
                ':UseDateTime' => $row['UseDateTime'],
                ':CallPhoneNumber' => $row['CallPhoneNumber'],
                ':Caririer' => $row['Caririer'],
                ':IncMessageNumber' => $row['IncMessageNumber'],
                ':IncMessage' => $row['IncMessage'],
                ':State' => $row['State'],
                ':ErrorCode' => $row['ErrorCode'],
                ':DeliveryState' => $row['DeliveryState'],
                ':DeliveryErrorCode' => $row['DeliveryErrorCode'],
                ':Status' => $row['Status'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }
}
