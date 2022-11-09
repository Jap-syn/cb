<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_RepaymentControl(返金管理)テーブルへのアダプタ
 */
class TableRepaymentControl
{
    protected $_name = 'T_RepaymentControl';
    protected $_primary = array('RepaySeq');
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
     * 返金管理データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $repaySeq 返金SEQ
     * @return ResultInterface
     */
    public function find($repaySeq)
    {
        $sql = " SELECT * FROM T_RepaymentControl WHERE ValidFlg = 1 AND RepaySeq = :RepaySeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':RepaySeq' => $repaySeq,
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
        $sql  = " INSERT INTO T_RepaymentControl (RepayStatus, IndicationDate, DecisionDate, ProcessClass, BankName, FfCode, BranchName, FfBranchCode, FfAccountClass, AccountNumber, AccountHolder, TransferCommission, TransferAmount, RepayAmount, RepayExpectedDate, ClaimId, CheckingUseAmount, CheckingClaimFee, CheckingDamageInterestAmount, CheckingAdditionalClaimFee, OutputFileSeq, NetStatus, CoRecvNum, CoYoyakuNum, CoTranLimit, CoWcosId, CoWcosPassword, CoWcosUrl, CoTranReqDate, CoTranProcDate, MailFlg, MailRetryCount, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :RepayStatus ";
        $sql .= " , :IndicationDate ";
        $sql .= " , :DecisionDate ";
        $sql .= " , :ProcessClass ";
        $sql .= " , :BankName ";
        $sql .= " , :FfCode ";
        $sql .= " , :BranchName ";
        $sql .= " , :FfBranchCode ";
        $sql .= " , :FfAccountClass ";
        $sql .= " , :AccountNumber ";
        $sql .= " , :AccountHolder ";
        $sql .= " , :TransferCommission ";
        $sql .= " , :TransferAmount ";
        $sql .= " , :RepayAmount ";
        $sql .= " , :RepayExpectedDate ";
        $sql .= " , :ClaimId ";
        $sql .= " , :CheckingUseAmount ";
        $sql .= " , :CheckingClaimFee ";
        $sql .= " , :CheckingDamageInterestAmount ";
        $sql .= " , :CheckingAdditionalClaimFee ";
        $sql .= " , :OutputFileSeq ";
        $sql .= " , :NetStatus ";
        $sql .= " , :CoRecvNum ";
        $sql .= " , :CoYoyakuNum ";
        $sql .= " , :CoTranLimit ";
        $sql .= " , :CoWcosId ";
        $sql .= " , :CoWcosPassword ";
        $sql .= " , :CoWcosUrl ";
        $sql .= " , :CoTranReqDate ";
        $sql .= " , :CoTranProcDate ";
        $sql .= " , :MailFlg ";
        $sql .= " , :MailRetryCount ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':RepayStatus' => isset($data['RepayStatus']) ? $data['RepayStatus'] : 0,
                ':IndicationDate' => $data['IndicationDate'],
                ':DecisionDate' => $data['DecisionDate'],
                ':ProcessClass' => isset($data['ProcessClass']) ? $data['ProcessClass'] : 0,
                ':BankName' => $data['BankName'],
                ':FfCode' => $data['FfCode'],
                ':BranchName' => $data['BranchName'],
                ':FfBranchCode' => $data['FfBranchCode'],
                ':FfAccountClass' => $data['FfAccountClass'],
                ':AccountNumber' => $data['AccountNumber'],
                ':AccountHolder' => $data['AccountHolder'],
                ':TransferCommission' => $data['TransferCommission'],
                ':TransferAmount' => $data['TransferAmount'],
                ':RepayAmount' => $data['RepayAmount'],
                ':RepayExpectedDate' => $data['RepayExpectedDate'],
                ':ClaimId' => $data['ClaimId'],
                ':CheckingUseAmount' => isset($data['CheckingUseAmount']) ? $data['CheckingUseAmount'] : 0,
                ':CheckingClaimFee' => isset($data['CheckingClaimFee']) ? $data['CheckingClaimFee'] : 0,
                ':CheckingDamageInterestAmount' => isset($data['CheckingDamageInterestAmount']) ? $data['CheckingDamageInterestAmount'] : 0,
                ':CheckingAdditionalClaimFee' => isset($data['CheckingAdditionalClaimFee']) ? $data['CheckingAdditionalClaimFee'] : 0,
                ':OutputFileSeq' => $data['OutputFileSeq'],
                ':NetStatus' => isset($data['NetStatus']) ? $data['NetStatus'] : 0,
                ':CoRecvNum' => $data['CoRecvNum'],
                ':CoYoyakuNum' => $data['CoYoyakuNum'],
                ':CoTranLimit' => $data['CoTranLimit'],
                ':CoWcosId' => $data['CoWcosId'],
                ':CoWcosPassword' => $data['CoWcosPassword'],
                ':CoWcosUrl' => $data['CoWcosUrl'],
                ':CoTranReqDate' => $data['CoTranReqDate'],
                ':CoTranProcDate' => $data['CoTranProcDate'],
                ':MailFlg' => isset($data['MailFlg']) ? $data['MailFlg'] : 9,
                ':MailRetryCount' => isset($data['MailRetryCount']) ? $data['MailRetryCount'] : 0,
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
     * @param int $repaySeq 返金SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $repaySeq)
    {
        $row = $this->find($repaySeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_RepaymentControl ";
        $sql .= " SET ";
        $sql .= "     RepayStatus = :RepayStatus ";
        $sql .= " ,   IndicationDate = :IndicationDate ";
        $sql .= " ,   DecisionDate = :DecisionDate ";
        $sql .= " ,   ProcessClass = :ProcessClass ";
        $sql .= " ,   BankName = :BankName ";
        $sql .= " ,   FfCode = :FfCode ";
        $sql .= " ,   BranchName = :BranchName ";
        $sql .= " ,   FfBranchCode = :FfBranchCode ";
        $sql .= " ,   FfAccountClass = :FfAccountClass ";
        $sql .= " ,   AccountNumber = :AccountNumber ";
        $sql .= " ,   AccountHolder = :AccountHolder ";
        $sql .= " ,   TransferCommission = :TransferCommission ";
        $sql .= " ,   TransferAmount = :TransferAmount ";
        $sql .= " ,   RepayAmount = :RepayAmount ";
        $sql .= " ,   RepayExpectedDate = :RepayExpectedDate ";
        $sql .= " ,   ClaimId = :ClaimId ";
        $sql .= " ,   CheckingUseAmount = :CheckingUseAmount ";
        $sql .= " ,   CheckingClaimFee = :CheckingClaimFee ";
        $sql .= " ,   CheckingDamageInterestAmount = :CheckingDamageInterestAmount ";
        $sql .= " ,   CheckingAdditionalClaimFee = :CheckingAdditionalClaimFee ";
        $sql .= " ,   OutputFileSeq = :OutputFileSeq ";
        $sql .= " ,   NetStatus = :NetStatus ";
        $sql .= " ,   CoRecvNum = :CoRecvNum ";
        $sql .= " ,   CoYoyakuNum = :CoYoyakuNum ";
        $sql .= " ,   CoTranLimit = :CoTranLimit ";
        $sql .= " ,   CoWcosId = :CoWcosId ";
        $sql .= " ,   CoWcosPassword = :CoWcosPassword ";
        $sql .= " ,   CoWcosUrl = :CoWcosUrl ";
        $sql .= " ,   CoTranReqDate = :CoTranReqDate ";
        $sql .= " ,   CoTranProcDate = :CoTranProcDate ";
        $sql .= " ,   MailFlg = :MailFlg ";
        $sql .= " ,   MailRetryCount = :MailRetryCount ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE RepaySeq = :RepaySeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':RepaySeq' => $repaySeq,
                ':RepayStatus' => $row['RepayStatus'],
                ':IndicationDate' => $row['IndicationDate'],
                ':DecisionDate' => $row['DecisionDate'],
                ':ProcessClass' => $row['ProcessClass'],
                ':BankName' => $row['BankName'],
                ':FfCode' => $row['FfCode'],
                ':BranchName' => $row['BranchName'],
                ':FfBranchCode' => $row['FfBranchCode'],
                ':FfAccountClass' => $row['FfAccountClass'],
                ':AccountNumber' => $row['AccountNumber'],
                ':AccountHolder' => $row['AccountHolder'],
                ':TransferCommission' => $row['TransferCommission'],
                ':TransferAmount' => $row['TransferAmount'],
                ':RepayAmount' => $row['RepayAmount'],
                ':RepayExpectedDate' => $row['RepayExpectedDate'],
                ':ClaimId' => $row['ClaimId'],
                ':CheckingUseAmount' => $row['CheckingUseAmount'],
                ':CheckingClaimFee' => $row['CheckingClaimFee'],
                ':CheckingDamageInterestAmount' => $row['CheckingDamageInterestAmount'],
                ':CheckingAdditionalClaimFee' => $row['CheckingAdditionalClaimFee'],
                ':OutputFileSeq' => $row['OutputFileSeq'],
                ':NetStatus' => $row['NetStatus'],
                ':CoRecvNum' => $row['CoRecvNum'],
                ':CoYoyakuNum' => $row['CoYoyakuNum'],
                ':CoTranLimit' => $row['CoTranLimit'],
                ':CoWcosId' => $row['CoWcosId'],
                ':CoWcosPassword' => $row['CoWcosPassword'],
                ':CoWcosUrl' => $row['CoWcosUrl'],
                ':CoTranReqDate' => $row['CoTranReqDate'],
                ':CoTranProcDate' => $row['CoTranProcDate'],
                ':MailFlg' => $row['MailFlg'],
                ':MailRetryCount' => $row['MailRetryCount'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }
}
