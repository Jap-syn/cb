<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * ATablePayOffDailyAccount2(直営日次統計表)テーブルへのアダプタ
 */
class ATablePayOffDailyAccount2
{
    protected $_name = 'AT_PayOff_DailyAccount2';
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
     * 直営日次統計表データを取得する
     *
     * @param int $seq シーケンス
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM AT_PayOff_DailyAccount2 WHERE Seq = :Seq ";

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
        $sql  = " INSERT INTO AT_PayOff_DailyAccount2 (DailyMonthlyFlg, ProcessingDate, OemId, OemNameKj, EnterpriseId, EnterpriseNameKj, MerchantNumber, AdvancesDate, AdvancesAmount, EnterpriseAccountsDue, AccountsPayablePending, ClaimAndObligationsDecision, UseAmount, CancelAmount, UseSettlementBackOffse, AccountsPayableTotal, SettlementFee, ClaimFee, MonthlyFee, IncludeMonthlyFee, ApiMonthlyFee, CreditNoticeMonthlyFee, NextClaimCreditNoticeMonthlyFee, AccountsReceivableTotal, StampFeeCount, StampFee, TransferCommissionCount, TransferCommission, AdjustmentAmountCount, AdjustmentAmount, EnterpriseRefundCount, EnterpriseRefund, AccountsDueOffsetAmount, AccountsPayablePendingAmount, AdvancesFixedDate, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :DailyMonthlyFlg ";
        $sql .= " , :ProcessingDate ";
        $sql .= " , :OemId ";
        $sql .= " , :OemNameKj ";
        $sql .= " , :EnterpriseId ";
        $sql .= " , :EnterpriseNameKj ";
        $sql .= " , :MerchantNumber ";
        $sql .= " , :AdvancesDate ";
        $sql .= " , :AdvancesAmount ";
        $sql .= " , :EnterpriseAccountsDue ";
        $sql .= " , :AccountsPayablePending ";
        $sql .= " , :ClaimAndObligationsDecision ";
        $sql .= " , :UseAmount ";
        $sql .= " , :CancelAmount ";
        $sql .= " , :UseSettlementBackOffse ";
        $sql .= " , :AccountsPayableTotal ";
        $sql .= " , :SettlementFee ";
        $sql .= " , :ClaimFee ";
        $sql .= " , :MonthlyFee ";
        $sql .= " , :IncludeMonthlyFee ";
        $sql .= " , :ApiMonthlyFee ";
        $sql .= " , :CreditNoticeMonthlyFee ";
        $sql .= " , :NextClaimCreditNoticeMonthlyFee ";
        $sql .= " , :AccountsReceivableTotal ";
        $sql .= " , :StampFeeCount ";
        $sql .= " , :StampFee ";
        $sql .= " , :TransferCommissionCount ";
        $sql .= " , :TransferCommission ";
        $sql .= " , :AdjustmentAmountCount ";
        $sql .= " , :AdjustmentAmount ";
        $sql .= " , :EnterpriseRefundCount ";
        $sql .= " , :EnterpriseRefund ";
        $sql .= " , :AccountsDueOffsetAmount ";
        $sql .= " , :AccountsPayablePendingAmount ";
        $sql .= " , :AdvancesFixedDate ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':DailyMonthlyFlg' => $data['DailyMonthlyFlg'],
                ':ProcessingDate' => $data['ProcessingDate'],
                ':OemId' => $data['OemId'],
                ':OemNameKj' => $data['OemNameKj'],
                ':EnterpriseId' => $data['EnterpriseId'],
                ':EnterpriseNameKj' => $data['EnterpriseNameKj'],
                ':MerchantNumber' => $data['MerchantNumber'],
                ':AdvancesDate' => $data['AdvancesDate'],
                ':AdvancesAmount' => $data['AdvancesAmount'],
                ':EnterpriseAccountsDue' => $data['EnterpriseAccountsDue'],
                ':AccountsPayablePending' => $data['AccountsPayablePending'],
                ':ClaimAndObligationsDecision' => $data['ClaimAndObligationsDecision'],
                ':UseAmount' => $data['UseAmount'],
                ':CancelAmount' => $data['CancelAmount'],
                ':UseSettlementBackOffse' => $data['UseSettlementBackOffse'],
                ':AccountsPayableTotal' => $data['AccountsPayableTotal'],
                ':SettlementFee' => $data['SettlementFee'],
                ':ClaimFee' => $data['ClaimFee'],
                ':MonthlyFee' => $data['MonthlyFee'],
                ':IncludeMonthlyFee' => $data['IncludeMonthlyFee'],
                ':ApiMonthlyFee' => $data['ApiMonthlyFee'],
                ':CreditNoticeMonthlyFee' => $data['CreditNoticeMonthlyFee'],
                ':NextClaimCreditNoticeMonthlyFee' => $data['NextClaimCreditNoticeMonthlyFee'],
                ':AccountsReceivableTotal' => $data['AccountsReceivableTotal'],
                ':StampFeeCount' => $data['StampFeeCount'],
                ':StampFee' => $data['StampFee'],
                ':TransferCommissionCount' => $data['TransferCommissionCount'],
                ':TransferCommission' => $data['TransferCommission'],
                ':AdjustmentAmountCount' => $data['AdjustmentAmountCount'],
                ':AdjustmentAmount' => $data['AdjustmentAmount'],
                ':EnterpriseRefundCount' => $data['EnterpriseRefundCount'],
                ':EnterpriseRefund' => $data['EnterpriseRefund'],
                ':AccountsDueOffsetAmount' => $data['AccountsDueOffsetAmount'],
                ':AccountsPayablePendingAmount' => $data['AccountsPayablePendingAmount'],
                ':AdvancesFixedDate' => $data['AdvancesFixedDate'],
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

        $sql  = " UPDATE AT_PayOff_DailyAccount2 ";
        $sql .= " SET ";
        $sql .= "     DailyMonthlyFlg = :DailyMonthlyFlg ";
        $sql .= " ,   ProcessingDate = :ProcessingDate ";
        $sql .= " ,   OemId = :OemId ";
        $sql .= " ,   OemNameKj = :OemNameKj ";
        $sql .= " ,   EnterpriseId = :EnterpriseId ";
        $sql .= " ,   EnterpriseNameKj = :EnterpriseNameKj ";
        $sql .= " ,   MerchantNumber = :MerchantNumber ";
        $sql .= " ,   AdvancesDate = :AdvancesDate ";
        $sql .= " ,   AdvancesAmount = :AdvancesAmount ";
        $sql .= " ,   EnterpriseAccountsDue = :EnterpriseAccountsDue ";
        $sql .= " ,   AccountsPayablePending = :AccountsPayablePending ";
        $sql .= " ,   ClaimAndObligationsDecision = :ClaimAndObligationsDecision ";
        $sql .= " ,   UseAmount = :UseAmount ";
        $sql .= " ,   CancelAmount = :CancelAmount ";
        $sql .= " ,   UseSettlementBackOffse = :UseSettlementBackOffse ";
        $sql .= " ,   AccountsPayableTotal = :AccountsPayableTotal ";
        $sql .= " ,   SettlementFee = :SettlementFee ";
        $sql .= " ,   ClaimFee = :ClaimFee ";
        $sql .= " ,   MonthlyFee = :MonthlyFee ";
        $sql .= " ,   IncludeMonthlyFee = :IncludeMonthlyFee ";
        $sql .= " ,   ApiMonthlyFee = :ApiMonthlyFee ";
        $sql .= " ,   CreditNoticeMonthlyFee = :CreditNoticeMonthlyFee ";
        $sql .= " ,   NextClaimCreditNoticeMonthlyFee = :NextClaimCreditNoticeMonthlyFee ";
        $sql .= " ,   AccountsReceivableTotal = :AccountsReceivableTotal ";
        $sql .= " ,   StampFeeCount = :StampFeeCount ";
        $sql .= " ,   StampFee = :StampFee ";
        $sql .= " ,   TransferCommissionCount = :TransferCommissionCount ";
        $sql .= " ,   TransferCommission = :TransferCommission ";
        $sql .= " ,   AdjustmentAmountCount = :AdjustmentAmountCount ";
        $sql .= " ,   AdjustmentAmount = :AdjustmentAmount ";
        $sql .= " ,   EnterpriseRefundCount = :EnterpriseRefundCount ";
        $sql .= " ,   EnterpriseRefund = :EnterpriseRefund ";
        $sql .= " ,   AccountsDueOffsetAmount = :AccountsDueOffsetAmount ";
        $sql .= " ,   AccountsPayablePendingAmount = :AccountsPayablePendingAmount ";
        $sql .= " ,   AdvancesFixedDate = :AdvancesFixedDate ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':DailyMonthlyFlg' => $row['DailyMonthlyFlg'],
                ':ProcessingDate' => $row['ProcessingDate'],
                ':OemId' => $row['OemId'],
                ':OemNameKj' => $row['OemNameKj'],
                ':EnterpriseId' => $row['EnterpriseId'],
                ':EnterpriseNameKj' => $row['EnterpriseNameKj'],
                ':MerchantNumber' => $row['MerchantNumber'],
                ':AdvancesDate' => $row['AdvancesDate'],
                ':AdvancesAmount' => $row['AdvancesAmount'],
                ':EnterpriseAccountsDue' => $row['EnterpriseAccountsDue'],
                ':AccountsPayablePending' => $row['AccountsPayablePending'],
                ':ClaimAndObligationsDecision' => $row['ClaimAndObligationsDecision'],
                ':UseAmount' => $row['UseAmount'],
                ':CancelAmount' => $row['CancelAmount'],
                ':UseSettlementBackOffse' => $row['UseSettlementBackOffse'],
                ':AccountsPayableTotal' => $row['AccountsPayableTotal'],
                ':SettlementFee' => $row['SettlementFee'],
                ':ClaimFee' => $row['ClaimFee'],
                ':MonthlyFee' => $row['MonthlyFee'],
                ':IncludeMonthlyFee' => $row['IncludeMonthlyFee'],
                ':ApiMonthlyFee' => $row['ApiMonthlyFee'],
                ':CreditNoticeMonthlyFee' => $row['CreditNoticeMonthlyFee'],
                ':NextClaimCreditNoticeMonthlyFee' => $row['NextClaimCreditNoticeMonthlyFee'],
                ':AccountsReceivableTotal' => $row['AccountsReceivableTotal'],
                ':StampFeeCount' => $row['StampFeeCount'],
                ':StampFee' => $row['StampFee'],
                ':TransferCommissionCount' => $row['TransferCommissionCount'],
                ':TransferCommission' => $row['TransferCommission'],
                ':AdjustmentAmountCount' => $row['AdjustmentAmountCount'],
                ':AdjustmentAmount' => $row['AdjustmentAmount'],
                ':EnterpriseRefundCount' => $row['EnterpriseRefundCount'],
                ':EnterpriseRefund' => $row['EnterpriseRefund'],
                ':AccountsDueOffsetAmount' => $row['AccountsDueOffsetAmount'],
                ':AccountsPayablePendingAmount' => $row['AccountsPayablePendingAmount'],
                ':AdvancesFixedDate' => $row['AdvancesFixedDate'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }
}
