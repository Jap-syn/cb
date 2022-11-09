<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * AT_ReceiptControl(入金管理_会計)テーブルへのアダプタ
 */
class ATableReceiptControl
{
    protected $_name = 'AT_ReceiptControl';
    protected $_primary = array('ReceiptSeq');
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
     * 入金管理_会計データを取得する
     *
     * @param int $receiptSeq 入金SEQ
     * @return ResultInterface
     */
    public function find($receiptSeq)
    {
        $sql = " SELECT * FROM AT_ReceiptControl WHERE ReceiptSeq = :ReceiptSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ReceiptSeq' => $receiptSeq,
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
        $sql  = " INSERT INTO AT_ReceiptControl (ReceiptSeq, AccountNumber, ClassDetails, BankFlg, Rct_CancelFlg, Before_ClearConditionForCharge, Before_ClearConditionDate, Before_Cnl_Status, Before_Deli_ConfirmArrivalFlg, KeyInfo, SokuhoRegistDate, KakuhoRegistDate) VALUES (";
        $sql .= "   :ReceiptSeq ";
        $sql .= " , :AccountNumber ";
        $sql .= " , :ClassDetails ";
        $sql .= " , :BankFlg ";
        $sql .= " , :Rct_CancelFlg ";
        $sql .= " , :Before_ClearConditionForCharge ";
        $sql .= " , :Before_ClearConditionDate ";
        $sql .= " , :Before_Cnl_Status ";
        $sql .= " , :Before_Deli_ConfirmArrivalFlg ";
        $sql .= " , :KeyInfo ";
        $sql .= " , :SokuhoRegistDate ";
        $sql .= " , :KakuhoRegistDate ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ReceiptSeq' => $data['ReceiptSeq'],
                ':AccountNumber' => $data['AccountNumber'],
                ':ClassDetails' => $data['ClassDetails'],
                ':BankFlg' => $data['BankFlg'],
                ':Rct_CancelFlg' => isset($data['Rct_CancelFlg']) ? $data['Rct_CancelFlg'] : 0,
                ':Before_ClearConditionForCharge' => $data['Before_ClearConditionForCharge'],
                ':Before_ClearConditionDate' => $data['Before_ClearConditionDate'],
                ':Before_Cnl_Status' => $data['Before_Cnl_Status'],
                ':Before_Deli_ConfirmArrivalFlg' => $data['Before_Deli_ConfirmArrivalFlg'],
                ':KeyInfo' => $data['KeyInfo'],
                ':SokuhoRegistDate' => $data['SokuhoRegistDate'],
                ':KakuhoRegistDate' => $data['KakuhoRegistDate'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $receiptSeq 入金SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $receiptSeq)
    {
        $row = $this->find($receiptSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE AT_ReceiptControl ";
        $sql .= " SET ";
        $sql .= "     AccountNumber = :AccountNumber ";
        $sql .= " ,   ClassDetails = :ClassDetails ";
        $sql .= " ,   BankFlg = :BankFlg ";
        $sql .= " ,   Rct_CancelFlg = :Rct_CancelFlg ";
        $sql .= " ,   Before_ClearConditionForCharge = :Before_ClearConditionForCharge ";
        $sql .= " ,   Before_ClearConditionDate = :Before_ClearConditionDate ";
        $sql .= " ,   Before_Cnl_Status = :Before_Cnl_Status ";
        $sql .= " ,   Before_Deli_ConfirmArrivalFlg = :Before_Deli_ConfirmArrivalFlg ";
        $sql .= " ,   KeyInfo = :KeyInfo ";
        $sql .= " ,   SokuhoRegistDate = :SokuhoRegistDate ";
        $sql .= " ,   KakuhoRegistDate = :KakuhoRegistDate ";
        $sql .= " WHERE ReceiptSeq = :ReceiptSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ReceiptSeq' => $receiptSeq,
                ':AccountNumber' => $row['AccountNumber'],
                ':ClassDetails' => $row['ClassDetails'],
                ':BankFlg' => $row['BankFlg'],
                ':Rct_CancelFlg' => $row['Rct_CancelFlg'],
                ':Before_ClearConditionForCharge' => $row['Before_ClearConditionForCharge'],
                ':Before_ClearConditionDate' => $row['Before_ClearConditionDate'],
                ':Before_Cnl_Status' => $row['Before_Cnl_Status'],
                ':Before_Deli_ConfirmArrivalFlg' => $row['Before_Deli_ConfirmArrivalFlg'],
                ':KeyInfo' => $row['KeyInfo'],
                ':SokuhoRegistDate' => $row['SokuhoRegistDate'],
                ':KakuhoRegistDate' => $row['KakuhoRegistDate'],
        );

        return $stm->execute($prm);
    }
}
