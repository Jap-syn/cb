<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_ReceiptControl(入金管理)テーブルへのアダプタ
 */
class TableReceiptControl
{
    protected $_name = 'T_ReceiptControl';
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
     * 入金管理データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $receiptSeq 入金SEQ
     * @return ResultInterface
     */
    public function find($receiptSeq)
    {
        $sql = " SELECT * FROM T_ReceiptControl WHERE ValidFlg = 1 AND ReceiptSeq = :ReceiptSeq ";

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
        $sql  = " INSERT INTO T_ReceiptControl (ReceiptProcessDate, ReceiptDate, ReceiptClass, ReceiptAmount, ClaimId, OrderSeq, CheckingUseAmount, CheckingClaimFee, CheckingDamageInterestAmount, CheckingAdditionalClaimFee, BranchBankId, DepositDate, ReceiptAgentId, MailFlg, MailRetryCount, Receipt_Note, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :ReceiptProcessDate ";
        $sql .= " , :ReceiptDate ";
        $sql .= " , :ReceiptClass ";
        $sql .= " , :ReceiptAmount ";
        $sql .= " , :ClaimId ";
        $sql .= " , :OrderSeq ";
        $sql .= " , :CheckingUseAmount ";
        $sql .= " , :CheckingClaimFee ";
        $sql .= " , :CheckingDamageInterestAmount ";
        $sql .= " , :CheckingAdditionalClaimFee ";
        $sql .= " , :BranchBankId ";
        $sql .= " , :DepositDate ";
        $sql .= " , :ReceiptAgentId ";
        $sql .= " , :MailFlg ";
        $sql .= " , :MailRetryCount ";
        $sql .= " , :Receipt_Note ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ReceiptProcessDate' => $data['ReceiptProcessDate'],
                ':ReceiptDate' => $data['ReceiptDate'],
                ':ReceiptClass' => $data['ReceiptClass'],
                ':ReceiptAmount' => $data['ReceiptAmount'],
                ':ClaimId' => $data['ClaimId'],
                ':OrderSeq' => $data['OrderSeq'],
                ':CheckingUseAmount' => isset($data['CheckingUseAmount']) ? $data['CheckingUseAmount'] : 0,
                ':CheckingClaimFee' => isset($data['CheckingClaimFee']) ? $data['CheckingClaimFee'] : 0,
                ':CheckingDamageInterestAmount' => isset($data['CheckingDamageInterestAmount']) ? $data['CheckingDamageInterestAmount'] : 0,
                ':CheckingAdditionalClaimFee' => isset($data['CheckingAdditionalClaimFee']) ? $data['CheckingAdditionalClaimFee'] : 0,
                ':BranchBankId' => $data['BranchBankId'],
                ':DepositDate' => $data['DepositDate'],
                ':ReceiptAgentId' => $data['ReceiptAgentId'],
                ':MailFlg' => isset($data['MailFlg']) ? $data['MailFlg'] : 9,
                ':MailRetryCount' => isset($data['MailRetryCount']) ? $data['MailRetryCount'] : 0,
                ':Receipt_Note' => isset($data['Receipt_Note']) ? $data['Receipt_Note'] : null,
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

        $sql  = " UPDATE T_ReceiptControl ";
        $sql .= " SET ";
        $sql .= "     ReceiptProcessDate = :ReceiptProcessDate ";
        $sql .= " ,   ReceiptDate = :ReceiptDate ";
        $sql .= " ,   ReceiptClass = :ReceiptClass ";
        $sql .= " ,   ReceiptAmount = :ReceiptAmount ";
        $sql .= " ,   ClaimId = :ClaimId ";
        $sql .= " ,   OrderSeq = :OrderSeq ";
        $sql .= " ,   CheckingUseAmount = :CheckingUseAmount ";
        $sql .= " ,   CheckingClaimFee = :CheckingClaimFee ";
        $sql .= " ,   CheckingDamageInterestAmount = :CheckingDamageInterestAmount ";
        $sql .= " ,   CheckingAdditionalClaimFee = :CheckingAdditionalClaimFee ";
        $sql .= " ,   BranchBankId = :BranchBankId ";
        $sql .= " ,   DepositDate = :DepositDate ";
        $sql .= " ,   ReceiptAgentId = :ReceiptAgentId ";
        $sql .= " ,   MailFlg = :MailFlg ";
        $sql .= " ,   MailRetryCount = :MailRetryCount ";
        $sql .= " ,   Receipt_Note = :Receipt_Note ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE ReceiptSeq = :ReceiptSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ReceiptSeq' => $receiptSeq,
                ':ReceiptProcessDate' => $row['ReceiptProcessDate'],
                ':ReceiptDate' => $row['ReceiptDate'],
                ':ReceiptClass' => $row['ReceiptClass'],
                ':ReceiptAmount' => $row['ReceiptAmount'],
                ':ClaimId' => $row['ClaimId'],
                ':OrderSeq' => $row['OrderSeq'],
                ':CheckingUseAmount' => $row['CheckingUseAmount'],
                ':CheckingClaimFee' => $row['CheckingClaimFee'],
                ':CheckingDamageInterestAmount' => $row['CheckingDamageInterestAmount'],
                ':CheckingAdditionalClaimFee' => $row['CheckingAdditionalClaimFee'],
                ':BranchBankId' => $row['BranchBankId'],
                ':DepositDate' => $row['DepositDate'],
                ':ReceiptAgentId' => $row['ReceiptAgentId'],
                ':MailFlg' => $row['MailFlg'],
                ':MailRetryCount' => $row['MailRetryCount'],
                ':Receipt_Note' => $row['Receipt_Note'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 入金管理データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $orderSeq 入金SEQ
     * @return ResultInterface
     */
    public function findByOrderSeq($orderSeq)
    {
        $sql = " SELECT ReceiptClass, ReceiptProcessDate FROM T_ReceiptControl WHERE ValidFlg = 1 AND OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
        );

        return $stm->execute($prm);
    }


    public function findByOrderSeqAndReceiptClass($orderSeq, $receiptClass, $receiptAmount)
    {
        $receiptSeq = null;
        $sql = " SELECT ReceiptSeq FROM T_ReceiptControl WHERE ValidFlg = 1 AND OrderSeq = :OrderSeq AND ReceiptClass = :ReceiptClass AND ReceiptAmount = :ReceiptAmount ORDER BY ReceiptProcessDate DESC LIMIT 1";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':OrderSeq' => $orderSeq,
            ':ReceiptClass' => $receiptClass,
            ':ReceiptAmount' => $receiptAmount,
        );

        $receiptSeq =  $stm->execute($prm)->current()['ReceiptSeq'];

        return $receiptSeq;
    }
}
