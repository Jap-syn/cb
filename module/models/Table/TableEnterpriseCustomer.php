<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_EnterpriseCustomer(加盟店顧客)テーブルへのアダプタ
 */
class TableEnterpriseCustomer
{
    protected $_name = 'T_EnterpriseCustomer';
    protected $_primary = array('EntCustSeq');
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
     * 加盟店請求履歴データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $entCustSeq 加盟店顧客SEQ
     * @return ResultInterface
     */
    public function find($entCustSeq)
    {
        $sql = " SELECT * FROM T_EnterpriseCustomer WHERE ValidFlg = 1 AND EntCustSeq = :EntCustSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EntCustSeq' => $entCustSeq,
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
        $sql  = " INSERT INTO T_EnterpriseCustomer (EnterpriseId, ManCustId, NameKj, NameKn, PostalCode, PrefectureCode, PrefectureName, City, Town, Building, UnitingAddress, Phone, MailAddress, Note, RegNameKj, RegUnitingAddress, RegPhone, SearchNameKj, SearchNameKn, SearchPhone, SearchUnitingAddress, BtoBCreditLimitAmountFlg, BtoBCreditLimitAmount, FfName, FfCode, FfBranchName, FfBranchCode, FfAccountClass, FfAccountNumber, FfAccountName, RequestCompScheduleDate, RequestStatus, RequestSubStatus, FfNote, ClaimFeeFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :EnterpriseId ";
        $sql .= " , :ManCustId ";
        $sql .= " , :NameKj ";
        $sql .= " , :NameKn ";
        $sql .= " , :PostalCode ";
        $sql .= " , :PrefectureCode ";
        $sql .= " , :PrefectureName ";
        $sql .= " , :City ";
        $sql .= " , :Town ";
        $sql .= " , :Building ";
        $sql .= " , :UnitingAddress ";
        $sql .= " , :Phone ";
        $sql .= " , :MailAddress ";
        $sql .= " , :Note ";
        $sql .= " , :RegNameKj ";
        $sql .= " , :RegUnitingAddress ";
        $sql .= " , :RegPhone ";
        $sql .= " , :SearchNameKj ";
        $sql .= " , :SearchNameKn ";
        $sql .= " , :SearchPhone ";
        $sql .= " , :SearchUnitingAddress ";
        $sql .= " , :BtoBCreditLimitAmountFlg ";
        $sql .= " , :BtoBCreditLimitAmount ";
        $sql .= " , :FfName ";
        $sql .= " , :FfCode ";
        $sql .= " , :FfBranchName ";
        $sql .= " , :FfBranchCode ";
        $sql .= " , :FfAccountClass ";
        $sql .= " , :FfAccountNumber ";
        $sql .= " , :FfAccountName ";
        $sql .= " , :RequestCompScheduleDate ";
        $sql .= " , :RequestStatus ";
        $sql .= " , :RequestSubStatus ";
        $sql .= " , :FfNote ";
        $sql .= " , :ClaimFeeFlg ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $data['EnterpriseId'],
                ':ManCustId' => $data['ManCustId'],
                ':NameKj' => $data['NameKj'],
                ':NameKn' => $data['NameKn'],
                ':PostalCode' => $data['PostalCode'],
                ':PrefectureCode' => $data['PrefectureCode'],
                ':PrefectureName' => $data['PrefectureName'],
                ':City' => $data['City'],
                ':Town' => $data['Town'],
                ':Building' => $data['Building'],
                ':UnitingAddress' => $data['UnitingAddress'],
                ':Phone' => $data['Phone'],
                ':MailAddress' => $data['MailAddress'],
                ':Note' => $data['Note'],
                ':RegNameKj' => $data['RegNameKj'],
                ':RegUnitingAddress' => $data['RegUnitingAddress'],
                ':RegPhone' => $data['RegPhone'],
                ':SearchNameKj' => $data['SearchNameKj'],
                ':SearchNameKn' => $data['SearchNameKn'],
                ':SearchPhone' => $data['SearchPhone'],
                ':SearchUnitingAddress' => $data['SearchUnitingAddress'],
                ':BtoBCreditLimitAmountFlg' => $data['BtoBCreditLimitAmountFlg'],
                ':BtoBCreditLimitAmount' => $data['BtoBCreditLimitAmount'],
                ':FfName' => $data['FfName'],
                ':FfCode' => $data['FfCode'],
                ':FfBranchName' => $data['FfBranchName'],
                ':FfBranchCode' => $data['FfBranchCode'],
                ':FfAccountClass' => $data['FfAccountClass'],
                ':FfAccountNumber' => $data['FfAccountNumber'],
                ':FfAccountName' => $data['FfAccountName'],
                ':RequestCompScheduleDate' => $data['RequestCompScheduleDate'],
                ':RequestStatus' => $data['RequestStatus'],
                ':RequestSubStatus' => $data['RequestSubStatus'],
                ':FfNote' => $data['FfNote'],
                ':ClaimFeeFlg' => $data['ClaimFeeFlg'],
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
     * @param int $entCustSeq 加盟店顧客SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $entCustSeq)
    {
        $row = $this->find($entCustSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_EnterpriseCustomer ";
        $sql .= " SET ";
        $sql .= "     EnterpriseId = :EnterpriseId ";
        $sql .= " ,   ManCustId = :ManCustId ";
        $sql .= " ,   NameKj = :NameKj ";
        $sql .= " ,   NameKn = :NameKn ";
        $sql .= " ,   PostalCode = :PostalCode ";
        $sql .= " ,   PrefectureCode = :PrefectureCode ";
        $sql .= " ,   PrefectureName = :PrefectureName ";
        $sql .= " ,   City = :City ";
        $sql .= " ,   Town = :Town ";
        $sql .= " ,   Building = :Building ";
        $sql .= " ,   UnitingAddress = :UnitingAddress ";
        $sql .= " ,   Phone = :Phone ";
        $sql .= " ,   MailAddress = :MailAddress ";
        $sql .= " ,   Note = :Note ";
        $sql .= " ,   RegNameKj = :RegNameKj ";
        $sql .= " ,   RegUnitingAddress = :RegUnitingAddress ";
        $sql .= " ,   RegPhone = :RegPhone ";
        $sql .= " ,   SearchNameKj = :SearchNameKj ";
        $sql .= " ,   SearchNameKn = :SearchNameKn ";
        $sql .= " ,   SearchPhone = :SearchPhone ";
        $sql .= " ,   SearchUnitingAddress = :SearchUnitingAddress ";
        $sql .= " ,   BtoBCreditLimitAmountFlg = :BtoBCreditLimitAmountFlg ";
        $sql .= " ,   BtoBCreditLimitAmount = :BtoBCreditLimitAmount ";
        $sql .= " ,   FfName = :FfName ";
        $sql .= " ,   FfCode = :FfCode ";
        $sql .= " ,   FfBranchName = :FfBranchName ";
        $sql .= " ,   FfBranchCode = :FfBranchCode ";
        $sql .= " ,   FfAccountClass = :FfAccountClass ";
        $sql .= " ,   FfAccountNumber = :FfAccountNumber ";
        $sql .= " ,   FfAccountName = :FfAccountName ";
        $sql .= " ,   RequestCompScheduleDate = :RequestCompScheduleDate ";
        $sql .= " ,   RequestCompDate = :RequestCompDate ";
        $sql .= " ,   RequestStatus = :RequestStatus ";
        $sql .= " ,   RequestSubStatus = :RequestSubStatus ";
        $sql .= " ,   FfNote = :FfNote ";
        $sql .= " ,   ClaimFeeFlg = :ClaimFeeFlg ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE EntCustSeq = :EntCustSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EntCustSeq' => $entCustSeq,
                ':EnterpriseId' => $row['EnterpriseId'],
                ':ManCustId' => $row['ManCustId'],
                ':NameKj' => $row['NameKj'],
                ':NameKn' => $row['NameKn'],
                ':PostalCode' => $row['PostalCode'],
                ':PrefectureCode' => $row['PrefectureCode'],
                ':PrefectureName' => $row['PrefectureName'],
                ':City' => $row['City'],
                ':Town' => $row['Town'],
                ':Building' => $row['Building'],
                ':UnitingAddress' => $row['UnitingAddress'],
                ':Phone' => $row['Phone'],
                ':MailAddress' => $row['MailAddress'],
                ':Note' => $row['Note'],
                ':RegNameKj' => $row['RegNameKj'],
                ':RegUnitingAddress' => $row['RegUnitingAddress'],
                ':RegPhone' => $row['RegPhone'],
                ':SearchNameKj' => $row['SearchNameKj'],
                ':SearchNameKn' => $row['SearchNameKn'],
                ':SearchPhone' => $row['SearchPhone'],
                ':SearchUnitingAddress' => $row['SearchUnitingAddress'],
                ':BtoBCreditLimitAmountFlg' => $row['BtoBCreditLimitAmountFlg'],
                ':BtoBCreditLimitAmount' => $row['BtoBCreditLimitAmount'],
                ':FfName' => $row['FfName'],
                ':FfCode' => $row['FfCode'],
                ':FfBranchName' => $row['FfBranchName'],
                ':FfBranchCode' => $row['FfBranchCode'],
                ':FfAccountClass' => $row['FfAccountClass'],
                ':FfAccountNumber' => $row['FfAccountNumber'],
                ':FfAccountName' => $row['FfAccountName'],
                ':RequestCompScheduleDate' => $row['RequestCompScheduleDate'],
                ':RequestCompDate' => isset($row['RequestCompDate']) ? $row['RequestCompDate'] : null,
                ':RequestStatus' => $row['RequestStatus'],
                ':RequestSubStatus' => $row['RequestSubStatus'],
                ':FfNote' => $row['FfNote'],
                ':ClaimFeeFlg' => $row['ClaimFeeFlg'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 統合指示された顧客のうち、統合元に紐づいた加盟店顧客の管理顧客番号を更新する
     * @param int $manCustId
     * @param array $combinedListId
     * @param int $usrId
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function saveManCustId($manCustId, $combinedListId, $usrId)
    {
        $sqls = <<<EOQ
SELECT      ec.EntCustSeq
FROM        T_EnterpriseCustomer ec
INNER JOIN  T_CombinedList cl
ON          cl.ManCustId = ec.ManCustId
WHERE       cl.CombinedListId = :CombinedListId
AND 		cl.CombinedDictateFlg = 1
AND 		ec.ValidFlg = 1
AND 		cl.ValidFlg = 1
AND 		ec.ManCustId <> :MinManCustId
EOQ;
        $stms = $this->_adapter->query($sqls);

        $prms = array(
                ':CombinedListId' => $combinedListId,
                ':MinManCustId'   => $manCustId,
                );
        $entCustSeqList = array();
        $ri = $stms->execute($prms);
        foreach ($ri as $row) {
            $entCustSeqList[] = $row['EntCustSeq'];
        }

        if (!empty($entCustSeqList))
        {
            $entCustSeqIn = implode(' ,', $entCustSeqList);

            $sqlu = <<<EOQ
UPDATE  T_EnterpriseCustomer
SET     ManCustId   = :ManCustId,
        UpdateDate  = :UpdateDate,
        UpdateId    = :UpdateId
EOQ;
            $where = ' WHERE EntCustSeq IN ( ' . $entCustSeqIn . ' )';
            $sql = $sqlu . $where;
            $stmu = $this->_adapter->query($sql);

            $prmu = array(
                ':ManCustId' => $manCustId,
                ':UpdateDate'   => date('Y-m-d H:i:s'),
                ':UpdateId'   => $usrId,
            );

            return $stmu->execute($prmu);
        }
    }

    /**
     * 加盟店顧客の存在チェック
     * (存在する時はMAX値を、存在しない時は-1を戻す)
     *
     * @param int $orderSeq 注文SEQ
     * @param int $enterpriseId 加盟店ID
     * @return -1 | MAX値
     */
    public function getMaxManEntCustSeq($orderSeq, $enterpriseId, $regNameKj, $regPhone, $regUnitingAddress)
    {
        $sql = <<<EOQ
SELECT IFNULL(MAX(ec.EntCustSeq), -1) AS MaxEntCustSeq
FROM   T_EnterpriseCustomer ec
       INNER JOIN T_Customer c
               ON c.EntCustSeq = ec.EntCustSeq
              AND c.OrderSeq  != :OrderSeq
WHERE  ec.EnterpriseId      = :EnterpriseId
AND    ec.RegNameKj         = :RegNameKj
AND    ec.RegPhone          = :RegPhone
AND    ec.RegUnitingAddress = :RegUnitingAddress
EOQ;

        $stm = $this->_adapter->query($sql);

        return $stm->execute(array(':OrderSeq' => $orderSeq, ':EnterpriseId' => $enterpriseId, ':RegNameKj' => $regNameKj, ':RegPhone' => $regPhone, ':RegUnitingAddress' => $regUnitingAddress))->current()['MaxEntCustSeq'];
    }

    public function getMaxManEntCustSeq2($orderSeq, $enterpriseId, $entCustId, $regNameKj)
    {
        $sql = <<<EOQ
SELECT IFNULL(MAX(ec.EntCustSeq), -1) AS MaxEntCustSeq
FROM   T_EnterpriseCustomer ec
       INNER JOIN T_Customer c
               ON c.EntCustSeq = ec.EntCustSeq
              AND c.OrderSeq  != :OrderSeq
              AND c.EntCustId  = :EntCustId
              AND c.OrderSeq NOT IN (SELECT OrderSeq FROM T_CreditTransferAlert WHERE ValidFlg=1)
WHERE  ec.EnterpriseId = :EnterpriseId
AND    ec.RegNameKj    = :RegNameKj
EOQ;

        $stm = $this->_adapter->query($sql);

        return $stm->execute(array(':OrderSeq' => $orderSeq, ':EnterpriseId' => $enterpriseId, ':EntCustId' => $entCustId, ':RegNameKj' => $regNameKj))->current()['MaxEntCustSeq'];
    }

    public function getMaxManEntCustSeq3($orderSeq, $enterpriseId, $entCustId)
    {
        $sql = <<<EOQ
SELECT IFNULL(MAX(ec.EntCustSeq), -1) AS MaxEntCustSeq
FROM   T_EnterpriseCustomer ec
       INNER JOIN T_Customer c
               ON c.EntCustSeq = ec.EntCustSeq
              AND c.OrderSeq  != :OrderSeq
              AND c.EntCustId  = :EntCustId
              AND c.OrderSeq NOT IN (SELECT OrderSeq FROM T_CreditTransferAlert WHERE ValidFlg=1)
WHERE  ec.EnterpriseId = :EnterpriseId
EOQ;

        $stm = $this->_adapter->query($sql);

        return $stm->execute(array(':OrderSeq' => $orderSeq, ':EnterpriseId' => $enterpriseId, ':EntCustId' => $entCustId))->current()['MaxEntCustSeq'];
    }
}
