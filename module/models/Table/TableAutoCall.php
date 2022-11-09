<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_AutoCall(オートコール結果)テーブルへのアダプタ
 */
class TableAutoCall
{
    protected $_name = 'T_AutoCall';
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
     * オートコール結果データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $seq SEQ
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM T_AutoCall WHERE ValidFlg = 1 AND Seq = :Seq ";

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
        $sql  = " INSERT INTO T_AutoCall (OrderSeq, AddInfo, Phone1, Phone2, Phone3, CallNumber, CallCount, CallPhone, CallStartDate, CallEndDate, ResponseDate, CuttingDate, TalkTime, CallResult, CuttingLevel, RepeatCount, Message1, Message2, Message3, Message4, Message5, Message6, Message7, Message8, Message9, Status, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :AddInfo ";
        $sql .= " , :Phone1 ";
        $sql .= " , :Phone2 ";
        $sql .= " , :Phone3 ";
        $sql .= " , :CallNumber ";
        $sql .= " , :CallCount ";
        $sql .= " , :CallPhone ";
        $sql .= " , :CallStartDate ";
        $sql .= " , :CallEndDate ";
        $sql .= " , :ResponseDate ";
        $sql .= " , :CuttingDate ";
        $sql .= " , :TalkTime ";
        $sql .= " , :CallResult ";
        $sql .= " , :CuttingLevel ";
        $sql .= " , :RepeatCount ";
        $sql .= " , :Message1 ";
        $sql .= " , :Message2 ";
        $sql .= " , :Message3 ";
        $sql .= " , :Message4 ";
        $sql .= " , :Message5 ";
        $sql .= " , :Message6 ";
        $sql .= " , :Message7 ";
        $sql .= " , :Message8 ";
        $sql .= " , :Message9 ";
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
                ':AddInfo' => $data['AddInfo'],
                ':Phone1' => $data['Phone1'],
                ':Phone2' => $data['Phone2'],
                ':Phone3' => $data['Phone3'],
                ':CallNumber' => $data['CallNumber'],
                ':CallCount' => $data['CallCount'],
                ':CallPhone' => $data['CallPhone'],
                ':CallStartDate' => $data['CallStartDate'],
                ':CallEndDate' => $data['CallEndDate'],
                ':ResponseDate' => $data['ResponseDate'],
                ':CuttingDate' => $data['CuttingDate'],
                ':TalkTime' => $data['TalkTime'],
                ':CallResult' => $data['CallResult'],
                ':CuttingLevel' => $data['CuttingLevel'],
                ':RepeatCount' => $data['RepeatCount'],
                ':Message1' => $data['Message1'],
                ':Message2' => $data['Message2'],
                ':Message3' => $data['Message3'],
                ':Message4' => $data['Message4'],
                ':Message5' => $data['Message5'],
                ':Message6' => $data['Message6'],
                ':Message7' => $data['Message7'],
                ':Message8' => $data['Message8'],
                ':Message9' => $data['Message9'],
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

        $sql  = " UPDATE T_AutoCall ";
        $sql .= " SET ";
        $sql .= "     OrderSeq = :OrderSeq ";
        $sql .= " ,   AddInfo = :AddInfo ";
        $sql .= " ,   Phone1 = :Phone1 ";
        $sql .= " ,   Phone2 = :Phone2 ";
        $sql .= " ,   Phone3 = :Phone3 ";
        $sql .= " ,   CallNumber = :CallNumber ";
        $sql .= " ,   CallCount = :CallCount ";
        $sql .= " ,   CallPhone = :CallPhone ";
        $sql .= " ,   CallStartDate = :CallStartDate ";
        $sql .= " ,   CallEndDate = :CallEndDate ";
        $sql .= " ,   ResponseDate = :ResponseDate ";
        $sql .= " ,   CuttingDate = :CuttingDate ";
        $sql .= " ,   TalkTime = :TalkTime ";
        $sql .= " ,   CallResult = :CallResult ";
        $sql .= " ,   CuttingLevel = :CuttingLevel ";
        $sql .= " ,   RepeatCount = :RepeatCount ";
        $sql .= " ,   Message1 = :Message1 ";
        $sql .= " ,   Message2 = :Message2 ";
        $sql .= " ,   Message3 = :Message3 ";
        $sql .= " ,   Message4 = :Message4 ";
        $sql .= " ,   Message5 = :Message5 ";
        $sql .= " ,   Message6 = :Message6 ";
        $sql .= " ,   Message7 = :Message7 ";
        $sql .= " ,   Message8 = :Message8 ";
        $sql .= " ,   Message9 = :Message9 ";
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
                ':AddInfo' => $row['AddInfo'],
                ':Phone1' => $row['Phone1'],
                ':Phone2' => $row['Phone2'],
                ':Phone3' => $row['Phone3'],
                ':CallNumber' => $row['CallNumber'],
                ':CallCount' => $row['CallCount'],
                ':CallPhone' => $row['CallPhone'],
                ':CallStartDate' => $row['CallStartDate'],
                ':CallEndDate' => $row['CallEndDate'],
                ':ResponseDate' => $row['ResponseDate'],
                ':CuttingDate' => $row['CuttingDate'],
                ':TalkTime' => $row['TalkTime'],
                ':CallResult' => $row['CallResult'],
                ':CuttingLevel' => $row['CuttingLevel'],
                ':RepeatCount' => $row['RepeatCount'],
                ':Message1' => $row['Message1'],
                ':Message2' => $row['Message2'],
                ':Message3' => $row['Message3'],
                ':Message4' => $row['Message4'],
                ':Message5' => $row['Message5'],
                ':Message6' => $row['Message6'],
                ':Message7' => $row['Message7'],
                ':Message8' => $row['Message8'],
                ':Message9' => $row['Message9'],
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
