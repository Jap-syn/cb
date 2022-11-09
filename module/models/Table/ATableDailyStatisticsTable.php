<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * AT_DailyStatisticsTable(直営日次統計表)テーブルへのアダプタ
 */
class ATableDailyStatisticsTable
{
    protected $_name = 'AT_DailyStatisticsTable';
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
        $sql = " SELECT * FROM AT_DailyStatisticsTable WHERE Seq = :Seq ";

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
        $sql  = " INSERT INTO AT_DailyStatisticsTable (DailyMonthlyFlg, ProcessingDate, AccountDate, EnterpriseId, EnterpriseNameKj, DB__AccountsReceivableBalance, D_ChargeCount, D_ChargeAmount, D_CancelCount, D_CancelAmount, D_SettlementBackCount, D_SettlementBackAmount, D_OemTransferCount, D_OemTransferAmount, D_ReClaimFeeCount, D_ReClaimFeeAmount, D_DamageCount, D_DamageAmount, D_ReceiptCount, D_ReceiptAmount, D_RepayCount, D_RepayAmount, D_BadDebtCount, D_BadDebtAmount, D_OtherPaymentCount, D_OtherPaymentAmount, D_AccountsReceivableBalance, D_SettlementFee, D_SettlementFeeTax, D_ClaimFee, D_ClaimFeeTax, D_MonthlyFee, D_MonthlyFeeTax, D_IncludeMonthlyFee, D_IncludeMonthlyFeeTax, D_ApiMonthlyFee, D_ApiMonthlyFeeTax, D_CreditNoticeMonthlyFee, D_CreditNoticeMonthlyFeeTax, D_NCreditNoticeMonthlyFee, D_NCreditNoticeMonthlyFeeTax, D_ReserveMonthlyFee, D_ReserveMonthlyFeeTax, D_AddClaimFee, D_AddClaimFeeTax, D_DamageInterestAmount, D_CanSettlementFee, D_CanSettlementFeeTax, D_CanClaimFee, D_CanClaimFeeTax, D_SettlementFeeTotal, D_SettlementFeeTaxTotal, D_ClaimFeeTotal, D_ClaimFeeTaxTotal, D_MonthlyFeeTotal, D_MonthlyFeeTaxTotal, D_IncludeMonthlyFeeTotal, D_IncludeMonthlyFeeTaxTotal, D_ApiMonthlyFeeTotal, D_ApiMonthlyFeeTaxTotal, D_CreditNoticeMonthlyFeeTotal, D_CreditNoticeMonthlyFeeTaxTotal, D_NCreditNoticeMonthlyFeeTotal, D_NCreditNoticeMonthlyFeeTaxTotal, D_ReserveMonthlyFeeTotal, D_ReserveMonthlyFeeTaxTotal, D_AddClaimFeeTotal, D_AddClaimFeeTaxTotal, D_DamageInterestAmountTotal, D_AllTotal, D_SettlementFeeOther, D_SettlementFeeTaxOther, D_ClaimFeeOther, D_ClaimFeeTaxOther, D_MonthlyFeeOther, D_MonthlyFeeTaxOther, D_IncludeMonthlyFeeOther, D_IncludeMonthlyFeeTaxOther, D_ApiMonthlyFeeOther, D_ApiMonthlyFeeTaxOther, D_CreditNoticeMonthlyFeeOther, D_CreditNoticeMonthlyFeeTaxOther, D_NCreditNoticeMonthlyFeeOther, D_NCreditNoticeMonthlyFeeTaxOther, D_ReserveMonthlyFeeOther, D_ReserveMonthlyFeeTaxOther, D_AddClaimFeeOther, D_AddClaimFeeTaxOther, D_DamageInterestAmountOther, D_SettlementFeeDiff, D_SettlementFeeTaxDiff, D_ClaimFeeDiff, D_ClaimFeeTaxDiff, D_MonthlyFeeDiff, D_MonthlyFeeTaxDiff, D_IncludeMonthlyFeeDiff, D_IncludeMonthlyFeeTaxDiff, D_ApiMonthlyFeeDiff, D_ApiMonthlyFeeTaxDiff, D_CreditNoticeMonthlyFeeDiff, D_CreditNoticeMonthlyFeeTaxDiff, D_NCreditNoticeMonthlyFeeDiff, D_NCreditNoticeMonthlyFeeTaxDiff, D_ReserveMonthlyFeeDiff, D_ReserveMonthlyFeeTaxDiff, D_AddClaimFeeDiff, D_AddClaimFeeTaxDiff, D_DamageInterestAmountDiff, MB__AccountsReceivableBalance, M_ChargeCount, M_ChargeAmount, M_CancelCount, M_CancelAmount, M_SettlementBackCount, M_SettlementBackAmount, M_TransferCount, M_TransferAmount, M_ReClaimFeeCount, M_ReClaimFeeAmount, M_DamageCount, M_DamageAmount, M_ReceiptCount, M_ReceiptAmount, M_RepayCount, M_RepayAmount, M_BadDebtCount, M_BadDebtAmount, M_OtherPaymentCount, M_OtherPaymentAmount, M_AccountsReceivableBalance, M_SuspensePaymentsAmount, M_AccountsReceivableBalanceDiff, M_SettlementFee, M_ClaimFee, M_ClaimFeeTax, M_MonthlyFee, M_MonthlyFeeTax, M_IncludeMonthlyFee, M_IncludeMonthlyFeeTax, M_ApiMonthlyFee, M_ApiMonthlyFeeTax, M_CreditNoticeMonthlyFee, M_CreditNoticeMonthlyFeeTax, M_NCreditNoticeMonthlyFee, M_NCreditNoticeMonthlyFeeTax, M_ReserveMonthlyFee, M_ReserveMonthlyFeeTax, M_AddClaimFee, M_AddClaimFeeTax, M_DamageInterestAmount, M_CanSettlementFee, M_CanSettlementFeeTax, M_CanClaimFee, M_CanClaimFeeTax, M_SettlementFeeTotal, M_ClaimFeeTotal, M_ClaimFeeTaxTotal, M_MonthlyFeeTotal, M_MonthlyFeeTaxTotal, M_IncludeMonthlyFeeTotal, M_IncludeMonthlyFeeTaxTotal, M_ApiMonthlyFeeTotal, M_ApiMonthlyFeeTaxTotal, M_CreditNoticeMonthlyFeeTotal, M_CreditNoticeMonthlyFeeTaxTotal, M_NCreditNoticeMonthlyFeeTotal, M_NCreditNoticeMonthlyFeeTaxTotal, M_ReserveMonthlyFeeTotal, M_ReserveMonthlyFeeTaxTotal, M_AddClaimFeeTotal, M_AddClaimFeeTaxTotal, M_DamageInterestAmountTotal, M_AllTotal, M_SettlementFeeOther, M_ClaimFeeOther, M_ClaimFeeTaxOther, M_MonthlyFeeOther, M_MonthlyFeeTaxOther, M_IncludeMonthlyFeeOther, M_IncludeMonthlyFeeTaxOther, M_ApiMonthlyFeeOther, M_ApiMonthlyFeeTaxOther, M_CreditNoticeMonthlyFeeOther, M_CreditNoticeMonthlyFeeTaxOther, M_NCreditNoticeMonthlyFeeOther, M_NCreditNoticeMonthlyFeeTaxOther, M_ReserveMonthlyFeeOther, M_ReserveMonthlyFeeTaxOther, M_AddClaimFeeOther, M_AddClaimFeeTaxOther, M_DamageInterestAmountOther, M_SettlementFeeDiff, M_ClaimFeeDiff, M_ClaimFeeTaxDiff, M_MonthlyFeeDiff, M_MonthlyFeeTaxDiff, M_IncludeMonthlyFeeDiff, M_IncludeMonthlyFeeTaxDiff, M_ApiMonthlyFeeDiff, M_ApiMonthlyFeeTaxDiff, M_CreditNoticeMonthlyFeeDiff, M_CreditNoticeMonthlyFeeTaxDiff, M_NCreditNoticeMonthlyFeeDiff, M_NCreditNoticeMonthlyFeeTaxDiff, M_ReserveMonthlyFeeDiff, M_ReserveMonthlyFeeTaxDiff, M_AddClaimFeeDiff, M_AddClaimFeeTaxDiff, M_DamageInterestAmountDiff, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :DailyMonthlyFlg ";
        $sql .= " , :ProcessingDate ";
        $sql .= " , :AccountDate ";
        $sql .= " , :EnterpriseId ";
        $sql .= " , :EnterpriseNameKj ";
        $sql .= " , :DB__AccountsReceivableBalance ";
        $sql .= " , :D_ChargeCount ";
        $sql .= " , :D_ChargeAmount ";
        $sql .= " , :D_CancelCount ";
        $sql .= " , :D_CancelAmount ";
        $sql .= " , :D_SettlementBackCount ";
        $sql .= " , :D_SettlementBackAmount ";
        $sql .= " , :D_OemTransferCount ";
        $sql .= " , :D_OemTransferAmount ";
        $sql .= " , :D_ReClaimFeeCount ";
        $sql .= " , :D_ReClaimFeeAmount ";
        $sql .= " , :D_DamageCount ";
        $sql .= " , :D_DamageAmount ";
        $sql .= " , :D_ReceiptCount ";
        $sql .= " , :D_ReceiptAmount ";
        $sql .= " , :D_RepayCount ";
        $sql .= " , :D_RepayAmount ";
        $sql .= " , :D_BadDebtCount ";
        $sql .= " , :D_BadDebtAmount ";
        $sql .= " , :D_OtherPaymentCount ";
        $sql .= " , :D_OtherPaymentAmount ";
        $sql .= " , :D_AccountsReceivableBalance ";
        $sql .= " , :D_SettlementFee ";
        $sql .= " , :D_SettlementFeeTax ";
        $sql .= " , :D_ClaimFee ";
        $sql .= " , :D_ClaimFeeTax ";
        $sql .= " , :D_MonthlyFee ";
        $sql .= " , :D_MonthlyFeeTax ";
        $sql .= " , :D_IncludeMonthlyFee ";
        $sql .= " , :D_IncludeMonthlyFeeTax ";
        $sql .= " , :D_ApiMonthlyFee ";
        $sql .= " , :D_ApiMonthlyFeeTax ";
        $sql .= " , :D_CreditNoticeMonthlyFee ";
        $sql .= " , :D_CreditNoticeMonthlyFeeTax ";
        $sql .= " , :D_NCreditNoticeMonthlyFee ";
        $sql .= " , :D_NCreditNoticeMonthlyFeeTax ";
        $sql .= " , :D_ReserveMonthlyFee ";
        $sql .= " , :D_ReserveMonthlyFeeTax ";
        $sql .= " , :D_AddClaimFee ";
        $sql .= " , :D_AddClaimFeeTax ";
        $sql .= " , :D_DamageInterestAmount ";
        $sql .= " , :D_CanSettlementFee ";
        $sql .= " , :D_CanSettlementFeeTax ";
        $sql .= " , :D_CanClaimFee ";
        $sql .= " , :D_CanClaimFeeTax ";
        $sql .= " , :D_SettlementFeeTotal ";
        $sql .= " , :D_SettlementFeeTaxTotal ";
        $sql .= " , :D_ClaimFeeTotal ";
        $sql .= " , :D_ClaimFeeTaxTotal ";
        $sql .= " , :D_MonthlyFeeTotal ";
        $sql .= " , :D_MonthlyFeeTaxTotal ";
        $sql .= " , :D_IncludeMonthlyFeeTotal ";
        $sql .= " , :D_IncludeMonthlyFeeTaxTotal ";
        $sql .= " , :D_ApiMonthlyFeeTotal ";
        $sql .= " , :D_ApiMonthlyFeeTaxTotal ";
        $sql .= " , :D_CreditNoticeMonthlyFeeTotal ";
        $sql .= " , :D_CreditNoticeMonthlyFeeTaxTotal ";
        $sql .= " , :D_NCreditNoticeMonthlyFeeTotal ";
        $sql .= " , :D_NCreditNoticeMonthlyFeeTaxTotal ";
        $sql .= " , :D_ReserveMonthlyFeeTotal ";
        $sql .= " , :D_ReserveMonthlyFeeTaxTotal ";
        $sql .= " , :D_AddClaimFeeTotal ";
        $sql .= " , :D_AddClaimFeeTaxTotal ";
        $sql .= " , :D_DamageInterestAmountTotal ";
        $sql .= " , :D_AllTotal ";
        $sql .= " , :D_SettlementFeeOther ";
        $sql .= " , :D_SettlementFeeTaxOther ";
        $sql .= " , :D_ClaimFeeOther ";
        $sql .= " , :D_ClaimFeeTaxOther ";
        $sql .= " , :D_MonthlyFeeOther ";
        $sql .= " , :D_MonthlyFeeTaxOther ";
        $sql .= " , :D_IncludeMonthlyFeeOther ";
        $sql .= " , :D_IncludeMonthlyFeeTaxOther ";
        $sql .= " , :D_ApiMonthlyFeeOther ";
        $sql .= " , :D_ApiMonthlyFeeTaxOther ";
        $sql .= " , :D_CreditNoticeMonthlyFeeOther ";
        $sql .= " , :D_CreditNoticeMonthlyFeeTaxOther ";
        $sql .= " , :D_NCreditNoticeMonthlyFeeOther ";
        $sql .= " , :D_NCreditNoticeMonthlyFeeTaxOther ";
        $sql .= " , :D_ReserveMonthlyFeeOther ";
        $sql .= " , :D_ReserveMonthlyFeeTaxOther ";
        $sql .= " , :D_AddClaimFeeOther ";
        $sql .= " , :D_AddClaimFeeTaxOther ";
        $sql .= " , :D_DamageInterestAmountOther ";
        $sql .= " , :D_SettlementFeeDiff ";
        $sql .= " , :D_SettlementFeeTaxDiff ";
        $sql .= " , :D_ClaimFeeDiff ";
        $sql .= " , :D_ClaimFeeTaxDiff ";
        $sql .= " , :D_MonthlyFeeDiff ";
        $sql .= " , :D_MonthlyFeeTaxDiff ";
        $sql .= " , :D_IncludeMonthlyFeeDiff ";
        $sql .= " , :D_IncludeMonthlyFeeTaxDiff ";
        $sql .= " , :D_ApiMonthlyFeeDiff ";
        $sql .= " , :D_ApiMonthlyFeeTaxDiff ";
        $sql .= " , :D_CreditNoticeMonthlyFeeDiff ";
        $sql .= " , :D_CreditNoticeMonthlyFeeTaxDiff ";
        $sql .= " , :D_NCreditNoticeMonthlyFeeDiff ";
        $sql .= " , :D_NCreditNoticeMonthlyFeeTaxDiff ";
        $sql .= " , :D_ReserveMonthlyFeeDiff ";
        $sql .= " , :D_ReserveMonthlyFeeTaxDiff ";
        $sql .= " , :D_AddClaimFeeDiff ";
        $sql .= " , :D_AddClaimFeeTaxDiff ";
        $sql .= " , :D_DamageInterestAmountDiff ";
        $sql .= " , :MB__AccountsReceivableBalance ";
        $sql .= " , :M_ChargeCount ";
        $sql .= " , :M_ChargeAmount ";
        $sql .= " , :M_CancelCount ";
        $sql .= " , :M_CancelAmount ";
        $sql .= " , :M_SettlementBackCount ";
        $sql .= " , :M_SettlementBackAmount ";
        $sql .= " , :M_TransferCount ";
        $sql .= " , :M_TransferAmount ";
        $sql .= " , :M_ReClaimFeeCount ";
        $sql .= " , :M_ReClaimFeeAmount ";
        $sql .= " , :M_DamageCount ";
        $sql .= " , :M_DamageAmount ";
        $sql .= " , :M_ReceiptCount ";
        $sql .= " , :M_ReceiptAmount ";
        $sql .= " , :M_RepayCount ";
        $sql .= " , :M_RepayAmount ";
        $sql .= " , :M_BadDebtCount ";
        $sql .= " , :M_BadDebtAmount ";
        $sql .= " , :M_OtherPaymentCount ";
        $sql .= " , :M_OtherPaymentAmount ";
        $sql .= " , :M_AccountsReceivableBalance ";
        $sql .= " , :M_SuspensePaymentsAmount ";
        $sql .= " , :M_AccountsReceivableBalanceDiff ";
        $sql .= " , :M_SettlementFee ";
        $sql .= " , :M_ClaimFee ";
        $sql .= " , :M_ClaimFeeTax ";
        $sql .= " , :M_MonthlyFee ";
        $sql .= " , :M_MonthlyFeeTax ";
        $sql .= " , :M_IncludeMonthlyFee ";
        $sql .= " , :M_IncludeMonthlyFeeTax ";
        $sql .= " , :M_ApiMonthlyFee ";
        $sql .= " , :M_ApiMonthlyFeeTax ";
        $sql .= " , :M_CreditNoticeMonthlyFee ";
        $sql .= " , :M_CreditNoticeMonthlyFeeTax ";
        $sql .= " , :M_NCreditNoticeMonthlyFee ";
        $sql .= " , :M_NCreditNoticeMonthlyFeeTax ";
        $sql .= " , :M_ReserveMonthlyFee ";
        $sql .= " , :M_ReserveMonthlyFeeTax ";
        $sql .= " , :M_AddClaimFee ";
        $sql .= " , :M_AddClaimFeeTax ";
        $sql .= " , :M_DamageInterestAmount ";
        $sql .= " , :M_CanSettlementFee ";
        $sql .= " , :M_CanSettlementFeeTax ";
        $sql .= " , :M_CanClaimFee ";
        $sql .= " , :M_CanClaimFeeTax ";
        $sql .= " , :M_SettlementFeeTotal ";
        $sql .= " , :M_ClaimFeeTotal ";
        $sql .= " , :M_ClaimFeeTaxTotal ";
        $sql .= " , :M_MonthlyFeeTotal ";
        $sql .= " , :M_MonthlyFeeTaxTotal ";
        $sql .= " , :M_IncludeMonthlyFeeTotal ";
        $sql .= " , :M_IncludeMonthlyFeeTaxTotal ";
        $sql .= " , :M_ApiMonthlyFeeTotal ";
        $sql .= " , :M_ApiMonthlyFeeTaxTotal ";
        $sql .= " , :M_CreditNoticeMonthlyFeeTotal ";
        $sql .= " , :M_CreditNoticeMonthlyFeeTaxTotal ";
        $sql .= " , :M_NCreditNoticeMonthlyFeeTotal ";
        $sql .= " , :M_NCreditNoticeMonthlyFeeTaxTotal ";
        $sql .= " , :M_ReserveMonthlyFeeTotal ";
        $sql .= " , :M_ReserveMonthlyFeeTaxTotal ";
        $sql .= " , :M_AddClaimFeeTotal ";
        $sql .= " , :M_AddClaimFeeTaxTotal ";
        $sql .= " , :M_DamageInterestAmountTotal ";
        $sql .= " , :M_AllTotal ";
        $sql .= " , :M_SettlementFeeOther ";
        $sql .= " , :M_ClaimFeeOther ";
        $sql .= " , :M_ClaimFeeTaxOther ";
        $sql .= " , :M_MonthlyFeeOther ";
        $sql .= " , :M_MonthlyFeeTaxOther ";
        $sql .= " , :M_IncludeMonthlyFeeOther ";
        $sql .= " , :M_IncludeMonthlyFeeTaxOther ";
        $sql .= " , :M_ApiMonthlyFeeOther ";
        $sql .= " , :M_ApiMonthlyFeeTaxOther ";
        $sql .= " , :M_CreditNoticeMonthlyFeeOther ";
        $sql .= " , :M_CreditNoticeMonthlyFeeTaxOther ";
        $sql .= " , :M_NCreditNoticeMonthlyFeeOther ";
        $sql .= " , :M_NCreditNoticeMonthlyFeeTaxOther ";
        $sql .= " , :M_ReserveMonthlyFeeOther ";
        $sql .= " , :M_ReserveMonthlyFeeTaxOther ";
        $sql .= " , :M_AddClaimFeeOther ";
        $sql .= " , :M_AddClaimFeeTaxOther ";
        $sql .= " , :M_DamageInterestAmountOther ";
        $sql .= " , :M_SettlementFeeDiff ";
        $sql .= " , :M_ClaimFeeDiff ";
        $sql .= " , :M_ClaimFeeTaxDiff ";
        $sql .= " , :M_MonthlyFeeDiff ";
        $sql .= " , :M_MonthlyFeeTaxDiff ";
        $sql .= " , :M_IncludeMonthlyFeeDiff ";
        $sql .= " , :M_IncludeMonthlyFeeTaxDiff ";
        $sql .= " , :M_ApiMonthlyFeeDiff ";
        $sql .= " , :M_ApiMonthlyFeeTaxDiff ";
        $sql .= " , :M_CreditNoticeMonthlyFeeDiff ";
        $sql .= " , :M_CreditNoticeMonthlyFeeTaxDiff ";
        $sql .= " , :M_NCreditNoticeMonthlyFeeDiff ";
        $sql .= " , :M_NCreditNoticeMonthlyFeeTaxDiff ";
        $sql .= " , :M_ReserveMonthlyFeeDiff ";
        $sql .= " , :M_ReserveMonthlyFeeTaxDiff ";
        $sql .= " , :M_AddClaimFeeDiff ";
        $sql .= " , :M_AddClaimFeeTaxDiff ";
        $sql .= " , :M_DamageInterestAmountDiff ";
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
                ':AccountDate' => $data['AccountDate'],
                ':EnterpriseId' => $data['EnterpriseId'],
                ':EnterpriseNameKj' => $data['EnterpriseNameKj'],
                ':DB__AccountsReceivableBalance' => $data['DB__AccountsReceivableBalance'],
                ':D_ChargeCount' => $data['D_ChargeCount'],
                ':D_ChargeAmount' => $data['D_ChargeAmount'],
                ':D_CancelCount' => $data['D_CancelCount'],
                ':D_CancelAmount' => $data['D_CancelAmount'],
                ':D_SettlementBackCount' => $data['D_SettlementBackCount'],
                ':D_SettlementBackAmount' => $data['D_SettlementBackAmount'],
                ':D_OemTransferCount' => $data['D_OemTransferCount'],
                ':D_OemTransferAmount' => $data['D_OemTransferAmount'],
                ':D_ReClaimFeeCount' => $data['D_ReClaimFeeCount'],
                ':D_ReClaimFeeAmount' => $data['D_ReClaimFeeAmount'],
                ':D_DamageCount' => $data['D_DamageCount'],
                ':D_DamageAmount' => $data['D_DamageAmount'],
                ':D_ReceiptCount' => $data['D_ReceiptCount'],
                ':D_ReceiptAmount' => $data['D_ReceiptAmount'],
                ':D_RepayCount' => $data['D_RepayCount'],
                ':D_RepayAmount' => $data['D_RepayAmount'],
                ':D_BadDebtCount' => $data['D_BadDebtCount'],
                ':D_BadDebtAmount' => $data['D_BadDebtAmount'],
                ':D_OtherPaymentCount' => $data['D_OtherPaymentCount'],
                ':D_OtherPaymentAmount' => $data['D_OtherPaymentAmount'],
                ':D_AccountsReceivableBalance' => $data['D_AccountsReceivableBalance'],
                ':D_SettlementFee' => $data['D_SettlementFee'],
                ':D_SettlementFeeTax' => $data['D_SettlementFeeTax'],
                ':D_ClaimFee' => $data['D_ClaimFee'],
                ':D_ClaimFeeTax' => $data['D_ClaimFeeTax'],
                ':D_MonthlyFee' => $data['D_MonthlyFee'],
                ':D_MonthlyFeeTax' => $data['D_MonthlyFeeTax'],
                ':D_IncludeMonthlyFee' => $data['D_IncludeMonthlyFee'],
                ':D_IncludeMonthlyFeeTax' => $data['D_IncludeMonthlyFeeTax'],
                ':D_ApiMonthlyFee' => $data['D_ApiMonthlyFee'],
                ':D_ApiMonthlyFeeTax' => $data['D_ApiMonthlyFeeTax'],
                ':D_CreditNoticeMonthlyFee' => $data['D_CreditNoticeMonthlyFee'],
                ':D_CreditNoticeMonthlyFeeTax' => $data['D_CreditNoticeMonthlyFeeTax'],
                ':D_NCreditNoticeMonthlyFee' => $data['D_NCreditNoticeMonthlyFee'],
                ':D_NCreditNoticeMonthlyFeeTax' => $data['D_NCreditNoticeMonthlyFeeTax'],
                ':D_ReserveMonthlyFee' => $data['D_ReserveMonthlyFee'],
                ':D_ReserveMonthlyFeeTax' => $data['D_ReserveMonthlyFeeTax'],
                ':D_AddClaimFee' => $data['D_AddClaimFee'],
                ':D_AddClaimFeeTax' => $data['D_AddClaimFeeTax'],
                ':D_DamageInterestAmount' => $data['D_DamageInterestAmount'],
                ':D_CanSettlementFee' => $data['D_CanSettlementFee'],
                ':D_CanSettlementFeeTax' => $data['D_CanSettlementFeeTax'],
                ':D_CanClaimFee' => $data['D_CanClaimFee'],
                ':D_CanClaimFeeTax' => $data['D_CanClaimFeeTax'],
                ':D_SettlementFeeTotal' => $data['D_SettlementFeeTotal'],
                ':D_SettlementFeeTaxTotal' => $data['D_SettlementFeeTaxTotal'],
                ':D_ClaimFeeTotal' => $data['D_ClaimFeeTotal'],
                ':D_ClaimFeeTaxTotal' => $data['D_ClaimFeeTaxTotal'],
                ':D_MonthlyFeeTotal' => $data['D_MonthlyFeeTotal'],
                ':D_MonthlyFeeTaxTotal' => $data['D_MonthlyFeeTaxTotal'],
                ':D_IncludeMonthlyFeeTotal' => $data['D_IncludeMonthlyFeeTotal'],
                ':D_IncludeMonthlyFeeTaxTotal' => $data['D_IncludeMonthlyFeeTaxTotal'],
                ':D_ApiMonthlyFeeTotal' => $data['D_ApiMonthlyFeeTotal'],
                ':D_ApiMonthlyFeeTaxTotal' => $data['D_ApiMonthlyFeeTaxTotal'],
                ':D_CreditNoticeMonthlyFeeTotal' => $data['D_CreditNoticeMonthlyFeeTotal'],
                ':D_CreditNoticeMonthlyFeeTaxTotal' => $data['D_CreditNoticeMonthlyFeeTaxTotal'],
                ':D_NCreditNoticeMonthlyFeeTotal' => $data['D_NCreditNoticeMonthlyFeeTotal'],
                ':D_NCreditNoticeMonthlyFeeTaxTotal' => $data['D_NCreditNoticeMonthlyFeeTaxTotal'],
                ':D_ReserveMonthlyFeeTotal' => $data['D_ReserveMonthlyFeeTotal'],
                ':D_ReserveMonthlyFeeTaxTotal' => $data['D_ReserveMonthlyFeeTaxTotal'],
                ':D_AddClaimFeeTotal' => $data['D_AddClaimFeeTotal'],
                ':D_AddClaimFeeTaxTotal' => $data['D_AddClaimFeeTaxTotal'],
                ':D_DamageInterestAmountTotal' => $data['D_DamageInterestAmountTotal'],
                ':D_AllTotal' => $data['D_AllTotal'],
                ':D_SettlementFeeOther' => $data['D_SettlementFeeOther'],
                ':D_SettlementFeeTaxOther' => $data['D_SettlementFeeTaxOther'],
                ':D_ClaimFeeOther' => $data['D_ClaimFeeOther'],
                ':D_ClaimFeeTaxOther' => $data['D_ClaimFeeTaxOther'],
                ':D_MonthlyFeeOther' => $data['D_MonthlyFeeOther'],
                ':D_MonthlyFeeTaxOther' => $data['D_MonthlyFeeTaxOther'],
                ':D_IncludeMonthlyFeeOther' => $data['D_IncludeMonthlyFeeOther'],
                ':D_IncludeMonthlyFeeTaxOther' => $data['D_IncludeMonthlyFeeTaxOther'],
                ':D_ApiMonthlyFeeOther' => $data['D_ApiMonthlyFeeOther'],
                ':D_ApiMonthlyFeeTaxOther' => $data['D_ApiMonthlyFeeTaxOther'],
                ':D_CreditNoticeMonthlyFeeOther' => $data['D_CreditNoticeMonthlyFeeOther'],
                ':D_CreditNoticeMonthlyFeeTaxOther' => $data['D_CreditNoticeMonthlyFeeTaxOther'],
                ':D_NCreditNoticeMonthlyFeeOther' => $data['D_NCreditNoticeMonthlyFeeOther'],
                ':D_NCreditNoticeMonthlyFeeTaxOther' => $data['D_NCreditNoticeMonthlyFeeTaxOther'],
                ':D_ReserveMonthlyFeeOther' => $data['D_ReserveMonthlyFeeOther'],
                ':D_ReserveMonthlyFeeTaxOther' => $data['D_ReserveMonthlyFeeTaxOther'],
                ':D_AddClaimFeeOther' => $data['D_AddClaimFeeOther'],
                ':D_AddClaimFeeTaxOther' => $data['D_AddClaimFeeTaxOther'],
                ':D_DamageInterestAmountOther' => $data['D_DamageInterestAmountOther'],
                ':D_SettlementFeeDiff' => $data['D_SettlementFeeDiff'],
                ':D_SettlementFeeTaxDiff' => $data['D_SettlementFeeTaxDiff'],
                ':D_ClaimFeeDiff' => $data['D_ClaimFeeDiff'],
                ':D_ClaimFeeTaxDiff' => $data['D_ClaimFeeTaxDiff'],
                ':D_MonthlyFeeDiff' => $data['D_MonthlyFeeDiff'],
                ':D_MonthlyFeeTaxDiff' => $data['D_MonthlyFeeTaxDiff'],
                ':D_IncludeMonthlyFeeDiff' => $data['D_IncludeMonthlyFeeDiff'],
                ':D_IncludeMonthlyFeeTaxDiff' => $data['D_IncludeMonthlyFeeTaxDiff'],
                ':D_ApiMonthlyFeeDiff' => $data['D_ApiMonthlyFeeDiff'],
                ':D_ApiMonthlyFeeTaxDiff' => $data['D_ApiMonthlyFeeTaxDiff'],
                ':D_CreditNoticeMonthlyFeeDiff' => $data['D_CreditNoticeMonthlyFeeDiff'],
                ':D_CreditNoticeMonthlyFeeTaxDiff' => $data['D_CreditNoticeMonthlyFeeTaxDiff'],
                ':D_NCreditNoticeMonthlyFeeDiff' => $data['D_NCreditNoticeMonthlyFeeDiff'],
                ':D_NCreditNoticeMonthlyFeeTaxDiff' => $data['D_NCreditNoticeMonthlyFeeTaxDiff'],
                ':D_ReserveMonthlyFeeDiff' => $data['D_ReserveMonthlyFeeDiff'],
                ':D_ReserveMonthlyFeeTaxDiff' => $data['D_ReserveMonthlyFeeTaxDiff'],
                ':D_AddClaimFeeDiff' => $data['D_AddClaimFeeDiff'],
                ':D_AddClaimFeeTaxDiff' => $data['D_AddClaimFeeTaxDiff'],
                ':D_DamageInterestAmountDiff' => $data['D_DamageInterestAmountDiff'],
                ':MB__AccountsReceivableBalance' => $data['MB__AccountsReceivableBalance'],
                ':M_ChargeCount' => $data['M_ChargeCount'],
                ':M_ChargeAmount' => $data['M_ChargeAmount'],
                ':M_CancelCount' => $data['M_CancelCount'],
                ':M_CancelAmount' => $data['M_CancelAmount'],
                ':M_SettlementBackCount' => $data['M_SettlementBackCount'],
                ':M_SettlementBackAmount' => $data['M_SettlementBackAmount'],
                ':M_TransferCount' => $data['M_TransferCount'],
                ':M_TransferAmount' => $data['M_TransferAmount'],
                ':M_ReClaimFeeCount' => $data['M_ReClaimFeeCount'],
                ':M_ReClaimFeeAmount' => $data['M_ReClaimFeeAmount'],
                ':M_DamageCount' => $data['M_DamageCount'],
                ':M_DamageAmount' => $data['M_DamageAmount'],
                ':M_ReceiptCount' => $data['M_ReceiptCount'],
                ':M_ReceiptAmount' => $data['M_ReceiptAmount'],
                ':M_RepayCount' => $data['M_RepayCount'],
                ':M_RepayAmount' => $data['M_RepayAmount'],
                ':M_BadDebtCount' => $data['M_BadDebtCount'],
                ':M_BadDebtAmount' => $data['M_BadDebtAmount'],
                ':M_OtherPaymentCount' => $data['M_OtherPaymentCount'],
                ':M_OtherPaymentAmount' => $data['M_OtherPaymentAmount'],
                ':M_AccountsReceivableBalance' => $data['M_AccountsReceivableBalance'],
                ':M_SuspensePaymentsAmount' => $data['M_SuspensePaymentsAmount'],
                ':M_AccountsReceivableBalanceDiff' => $data['M_AccountsReceivableBalanceDiff'],
                ':M_SettlementFee' => $data['M_SettlementFee'],
                ':M_ClaimFee' => $data['M_ClaimFee'],
                ':M_ClaimFeeTax' => $data['M_ClaimFeeTax'],
                ':M_MonthlyFee' => $data['M_MonthlyFee'],
                ':M_MonthlyFeeTax' => $data['M_MonthlyFeeTax'],
                ':M_IncludeMonthlyFee' => $data['M_IncludeMonthlyFee'],
                ':M_IncludeMonthlyFeeTax' => $data['M_IncludeMonthlyFeeTax'],
                ':M_ApiMonthlyFee' => $data['M_ApiMonthlyFee'],
                ':M_ApiMonthlyFeeTax' => $data['M_ApiMonthlyFeeTax'],
                ':M_CreditNoticeMonthlyFee' => $data['M_CreditNoticeMonthlyFee'],
                ':M_CreditNoticeMonthlyFeeTax' => $data['M_CreditNoticeMonthlyFeeTax'],
                ':M_NCreditNoticeMonthlyFee' => $data['M_NCreditNoticeMonthlyFee'],
                ':M_NCreditNoticeMonthlyFeeTax' => $data['M_NCreditNoticeMonthlyFeeTax'],
                ':M_ReserveMonthlyFee' => $data['M_ReserveMonthlyFee'],
                ':M_ReserveMonthlyFeeTax' => $data['M_ReserveMonthlyFeeTax'],
                ':M_AddClaimFee' => $data['M_AddClaimFee'],
                ':M_AddClaimFeeTax' => $data['M_AddClaimFeeTax'],
                ':M_DamageInterestAmount' => $data['M_DamageInterestAmount'],
                ':M_CanSettlementFee' => $data['M_CanSettlementFee'],
                ':M_CanSettlementFeeTax' => $data['M_CanSettlementFeeTax'],
                ':M_CanClaimFee' => $data['M_CanClaimFee'],
                ':M_CanClaimFeeTax' => $data['M_CanClaimFeeTax'],
                ':M_SettlementFeeTotal' => $data['M_SettlementFeeTotal'],
                ':M_ClaimFeeTotal' => $data['M_ClaimFeeTotal'],
                ':M_ClaimFeeTaxTotal' => $data['M_ClaimFeeTaxTotal'],
                ':M_MonthlyFeeTotal' => $data['M_MonthlyFeeTotal'],
                ':M_MonthlyFeeTaxTotal' => $data['M_MonthlyFeeTaxTotal'],
                ':M_IncludeMonthlyFeeTotal' => $data['M_IncludeMonthlyFeeTotal'],
                ':M_IncludeMonthlyFeeTaxTotal' => $data['M_IncludeMonthlyFeeTaxTotal'],
                ':M_ApiMonthlyFeeTotal' => $data['M_ApiMonthlyFeeTotal'],
                ':M_ApiMonthlyFeeTaxTotal' => $data['M_ApiMonthlyFeeTaxTotal'],
                ':M_CreditNoticeMonthlyFeeTotal' => $data['M_CreditNoticeMonthlyFeeTotal'],
                ':M_CreditNoticeMonthlyFeeTaxTotal' => $data['M_CreditNoticeMonthlyFeeTaxTotal'],
                ':M_NCreditNoticeMonthlyFeeTotal' => $data['M_NCreditNoticeMonthlyFeeTotal'],
                ':M_NCreditNoticeMonthlyFeeTaxTotal' => $data['M_NCreditNoticeMonthlyFeeTaxTotal'],
                ':M_ReserveMonthlyFeeTotal' => $data['M_ReserveMonthlyFeeTotal'],
                ':M_ReserveMonthlyFeeTaxTotal' => $data['M_ReserveMonthlyFeeTaxTotal'],
                ':M_AddClaimFeeTotal' => $data['M_AddClaimFeeTotal'],
                ':M_AddClaimFeeTaxTotal' => $data['M_AddClaimFeeTaxTotal'],
                ':M_DamageInterestAmountTotal' => $data['M_DamageInterestAmountTotal'],
                ':M_AllTotal' => $data['M_AllTotal'],
                ':M_SettlementFeeOther' => $data['M_SettlementFeeOther'],
                ':M_ClaimFeeOther' => $data['M_ClaimFeeOther'],
                ':M_ClaimFeeTaxOther' => $data['M_ClaimFeeTaxOther'],
                ':M_MonthlyFeeOther' => $data['M_MonthlyFeeOther'],
                ':M_MonthlyFeeTaxOther' => $data['M_MonthlyFeeTaxOther'],
                ':M_IncludeMonthlyFeeOther' => $data['M_IncludeMonthlyFeeOther'],
                ':M_IncludeMonthlyFeeTaxOther' => $data['M_IncludeMonthlyFeeTaxOther'],
                ':M_ApiMonthlyFeeOther' => $data['M_ApiMonthlyFeeOther'],
                ':M_ApiMonthlyFeeTaxOther' => $data['M_ApiMonthlyFeeTaxOther'],
                ':M_CreditNoticeMonthlyFeeOther' => $data['M_CreditNoticeMonthlyFeeOther'],
                ':M_CreditNoticeMonthlyFeeTaxOther' => $data['M_CreditNoticeMonthlyFeeTaxOther'],
                ':M_NCreditNoticeMonthlyFeeOther' => $data['M_NCreditNoticeMonthlyFeeOther'],
                ':M_NCreditNoticeMonthlyFeeTaxOther' => $data['M_NCreditNoticeMonthlyFeeTaxOther'],
                ':M_ReserveMonthlyFeeOther' => $data['M_ReserveMonthlyFeeOther'],
                ':M_ReserveMonthlyFeeTaxOther' => $data['M_ReserveMonthlyFeeTaxOther'],
                ':M_AddClaimFeeOther' => $data['M_AddClaimFeeOther'],
                ':M_AddClaimFeeTaxOther' => $data['M_AddClaimFeeTaxOther'],
                ':M_DamageInterestAmountOther' => $data['M_DamageInterestAmountOther'],
                ':M_SettlementFeeDiff' => $data['M_SettlementFeeDiff'],
                ':M_ClaimFeeDiff' => $data['M_ClaimFeeDiff'],
                ':M_ClaimFeeTaxDiff' => $data['M_ClaimFeeTaxDiff'],
                ':M_MonthlyFeeDiff' => $data['M_MonthlyFeeDiff'],
                ':M_MonthlyFeeTaxDiff' => $data['M_MonthlyFeeTaxDiff'],
                ':M_IncludeMonthlyFeeDiff' => $data['M_IncludeMonthlyFeeDiff'],
                ':M_IncludeMonthlyFeeTaxDiff' => $data['M_IncludeMonthlyFeeTaxDiff'],
                ':M_ApiMonthlyFeeDiff' => $data['M_ApiMonthlyFeeDiff'],
                ':M_ApiMonthlyFeeTaxDiff' => $data['M_ApiMonthlyFeeTaxDiff'],
                ':M_CreditNoticeMonthlyFeeDiff' => $data['M_CreditNoticeMonthlyFeeDiff'],
                ':M_CreditNoticeMonthlyFeeTaxDiff' => $data['M_CreditNoticeMonthlyFeeTaxDiff'],
                ':M_NCreditNoticeMonthlyFeeDiff' => $data['M_NCreditNoticeMonthlyFeeDiff'],
                ':M_NCreditNoticeMonthlyFeeTaxDiff' => $data['M_NCreditNoticeMonthlyFeeTaxDiff'],
                ':M_ReserveMonthlyFeeDiff' => $data['M_ReserveMonthlyFeeDiff'],
                ':M_ReserveMonthlyFeeTaxDiff' => $data['M_ReserveMonthlyFeeTaxDiff'],
                ':M_AddClaimFeeDiff' => $data['M_AddClaimFeeDiff'],
                ':M_AddClaimFeeTaxDiff' => $data['M_AddClaimFeeTaxDiff'],
                ':M_DamageInterestAmountDiff' => $data['M_DamageInterestAmountDiff'],
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

        $sql  = " UPDATE AT_DailyStatisticsTable ";
        $sql .= " SET ";
        $sql .= "     DailyMonthlyFlg = :DailyMonthlyFlg ";
        $sql .= " ,   ProcessingDate = :ProcessingDate ";
        $sql .= " ,   AccountDate = :AccountDate ";
        $sql .= " ,   EnterpriseId = :EnterpriseId ";
        $sql .= " ,   EnterpriseNameKj = :EnterpriseNameKj ";
        $sql .= " ,   DB__AccountsReceivableBalance = :DB__AccountsReceivableBalance ";
        $sql .= " ,   D_ChargeCount = :D_ChargeCount ";
        $sql .= " ,   D_ChargeAmount = :D_ChargeAmount ";
        $sql .= " ,   D_CancelCount = :D_CancelCount ";
        $sql .= " ,   D_CancelAmount = :D_CancelAmount ";
        $sql .= " ,   D_SettlementBackCount = :D_SettlementBackCount ";
        $sql .= " ,   D_SettlementBackAmount = :D_SettlementBackAmount ";
        $sql .= " ,   D_OemTransferCount = :D_OemTransferCount ";
        $sql .= " ,   D_OemTransferAmount = :D_OemTransferAmount ";
        $sql .= " ,   D_ReClaimFeeCount = :D_ReClaimFeeCount ";
        $sql .= " ,   D_ReClaimFeeAmount = :D_ReClaimFeeAmount ";
        $sql .= " ,   D_DamageCount = :D_DamageCount ";
        $sql .= " ,   D_DamageAmount = :D_DamageAmount ";
        $sql .= " ,   D_ReceiptCount = :D_ReceiptCount ";
        $sql .= " ,   D_ReceiptAmount = :D_ReceiptAmount ";
        $sql .= " ,   D_RepayCount = :D_RepayCount ";
        $sql .= " ,   D_RepayAmount = :D_RepayAmount ";
        $sql .= " ,   D_BadDebtCount = :D_BadDebtCount ";
        $sql .= " ,   D_BadDebtAmount = :D_BadDebtAmount ";
        $sql .= " ,   D_OtherPaymentCount = :D_OtherPaymentCount ";
        $sql .= " ,   D_OtherPaymentAmount = :D_OtherPaymentAmount ";
        $sql .= " ,   D_AccountsReceivableBalance = :D_AccountsReceivableBalance ";
        $sql .= " ,   D_SettlementFee = :D_SettlementFee ";
        $sql .= " ,   D_SettlementFeeTax = :D_SettlementFeeTax ";
        $sql .= " ,   D_ClaimFee = :D_ClaimFee ";
        $sql .= " ,   D_ClaimFeeTax = :D_ClaimFeeTax ";
        $sql .= " ,   D_MonthlyFee = :D_MonthlyFee ";
        $sql .= " ,   D_MonthlyFeeTax = :D_MonthlyFeeTax ";
        $sql .= " ,   D_IncludeMonthlyFee = :D_IncludeMonthlyFee ";
        $sql .= " ,   D_IncludeMonthlyFeeTax = :D_IncludeMonthlyFeeTax ";
        $sql .= " ,   D_ApiMonthlyFee = :D_ApiMonthlyFee ";
        $sql .= " ,   D_ApiMonthlyFeeTax = :D_ApiMonthlyFeeTax ";
        $sql .= " ,   D_CreditNoticeMonthlyFee = :D_CreditNoticeMonthlyFee ";
        $sql .= " ,   D_CreditNoticeMonthlyFeeTax = :D_CreditNoticeMonthlyFeeTax ";
        $sql .= " ,   D_NCreditNoticeMonthlyFee = :D_NCreditNoticeMonthlyFee ";
        $sql .= " ,   D_NCreditNoticeMonthlyFeeTax = :D_NCreditNoticeMonthlyFeeTax ";
        $sql .= " ,   D_ReserveMonthlyFee = :D_ReserveMonthlyFee ";
        $sql .= " ,   D_ReserveMonthlyFeeTax = :D_ReserveMonthlyFeeTax ";
        $sql .= " ,   D_AddClaimFee = :D_AddClaimFee ";
        $sql .= " ,   D_AddClaimFeeTax = :D_AddClaimFeeTax ";
        $sql .= " ,   D_DamageInterestAmount = :D_DamageInterestAmount ";
        $sql .= " ,   D_CanSettlementFee = :D_CanSettlementFee ";
        $sql .= " ,   D_CanSettlementFeeTax = :D_CanSettlementFeeTax ";
        $sql .= " ,   D_CanClaimFee = :D_CanClaimFee ";
        $sql .= " ,   D_CanClaimFeeTax = :D_CanClaimFeeTax ";
        $sql .= " ,   D_SettlementFeeTotal = :D_SettlementFeeTotal ";
        $sql .= " ,   D_SettlementFeeTaxTotal = :D_SettlementFeeTaxTotal ";
        $sql .= " ,   D_ClaimFeeTotal = :D_ClaimFeeTotal ";
        $sql .= " ,   D_ClaimFeeTaxTotal = :D_ClaimFeeTaxTotal ";
        $sql .= " ,   D_MonthlyFeeTotal = :D_MonthlyFeeTotal ";
        $sql .= " ,   D_MonthlyFeeTaxTotal = :D_MonthlyFeeTaxTotal ";
        $sql .= " ,   D_IncludeMonthlyFeeTotal = :D_IncludeMonthlyFeeTotal ";
        $sql .= " ,   D_IncludeMonthlyFeeTaxTotal = :D_IncludeMonthlyFeeTaxTotal ";
        $sql .= " ,   D_ApiMonthlyFeeTotal = :D_ApiMonthlyFeeTotal ";
        $sql .= " ,   D_ApiMonthlyFeeTaxTotal = :D_ApiMonthlyFeeTaxTotal ";
        $sql .= " ,   D_CreditNoticeMonthlyFeeTotal = :D_CreditNoticeMonthlyFeeTotal ";
        $sql .= " ,   D_CreditNoticeMonthlyFeeTaxTotal = :D_CreditNoticeMonthlyFeeTaxTotal ";
        $sql .= " ,   D_NCreditNoticeMonthlyFeeTotal = :D_NCreditNoticeMonthlyFeeTotal ";
        $sql .= " ,   D_NCreditNoticeMonthlyFeeTaxTotal = :D_NCreditNoticeMonthlyFeeTaxTotal ";
        $sql .= " ,   D_ReserveMonthlyFeeTotal = :D_ReserveMonthlyFeeTotal ";
        $sql .= " ,   D_ReserveMonthlyFeeTaxTotal = :D_ReserveMonthlyFeeTaxTotal ";
        $sql .= " ,   D_AddClaimFeeTotal = :D_AddClaimFeeTotal ";
        $sql .= " ,   D_AddClaimFeeTaxTotal = :D_AddClaimFeeTaxTotal ";
        $sql .= " ,   D_DamageInterestAmountTotal = :D_DamageInterestAmountTotal ";
        $sql .= " ,   D_AllTotal = :D_AllTotal ";
        $sql .= " ,   D_SettlementFeeOther = :D_SettlementFeeOther ";
        $sql .= " ,   D_SettlementFeeTaxOther = :D_SettlementFeeTaxOther ";
        $sql .= " ,   D_ClaimFeeOther = :D_ClaimFeeOther ";
        $sql .= " ,   D_ClaimFeeTaxOther = :D_ClaimFeeTaxOther ";
        $sql .= " ,   D_MonthlyFeeOther = :D_MonthlyFeeOther ";
        $sql .= " ,   D_MonthlyFeeTaxOther = :D_MonthlyFeeTaxOther ";
        $sql .= " ,   D_IncludeMonthlyFeeOther = :D_IncludeMonthlyFeeOther ";
        $sql .= " ,   D_IncludeMonthlyFeeTaxOther = :D_IncludeMonthlyFeeTaxOther ";
        $sql .= " ,   D_ApiMonthlyFeeOther = :D_ApiMonthlyFeeOther ";
        $sql .= " ,   D_ApiMonthlyFeeTaxOther = :D_ApiMonthlyFeeTaxOther ";
        $sql .= " ,   D_CreditNoticeMonthlyFeeOther = :D_CreditNoticeMonthlyFeeOther ";
        $sql .= " ,   D_CreditNoticeMonthlyFeeTaxOther = :D_CreditNoticeMonthlyFeeTaxOther ";
        $sql .= " ,   D_NCreditNoticeMonthlyFeeOther = :D_NCreditNoticeMonthlyFeeOther ";
        $sql .= " ,   D_NCreditNoticeMonthlyFeeTaxOther = :D_NCreditNoticeMonthlyFeeTaxOther ";
        $sql .= " ,   D_ReserveMonthlyFeeOther = :D_ReserveMonthlyFeeOther ";
        $sql .= " ,   D_ReserveMonthlyFeeTaxOther = :D_ReserveMonthlyFeeTaxOther ";
        $sql .= " ,   D_AddClaimFeeOther = :D_AddClaimFeeOther ";
        $sql .= " ,   D_AddClaimFeeTaxOther = :D_AddClaimFeeTaxOther ";
        $sql .= " ,   D_DamageInterestAmountOther = :D_DamageInterestAmountOther ";
        $sql .= " ,   D_SettlementFeeDiff = :D_SettlementFeeDiff ";
        $sql .= " ,   D_SettlementFeeTaxDiff = :D_SettlementFeeTaxDiff ";
        $sql .= " ,   D_ClaimFeeDiff = :D_ClaimFeeDiff ";
        $sql .= " ,   D_ClaimFeeTaxDiff = :D_ClaimFeeTaxDiff ";
        $sql .= " ,   D_MonthlyFeeDiff = :D_MonthlyFeeDiff ";
        $sql .= " ,   D_MonthlyFeeTaxDiff = :D_MonthlyFeeTaxDiff ";
        $sql .= " ,   D_IncludeMonthlyFeeDiff = :D_IncludeMonthlyFeeDiff ";
        $sql .= " ,   D_IncludeMonthlyFeeTaxDiff = :D_IncludeMonthlyFeeTaxDiff ";
        $sql .= " ,   D_ApiMonthlyFeeDiff = :D_ApiMonthlyFeeDiff ";
        $sql .= " ,   D_ApiMonthlyFeeTaxDiff = :D_ApiMonthlyFeeTaxDiff ";
        $sql .= " ,   D_CreditNoticeMonthlyFeeDiff = :D_CreditNoticeMonthlyFeeDiff ";
        $sql .= " ,   D_CreditNoticeMonthlyFeeTaxDiff = :D_CreditNoticeMonthlyFeeTaxDiff ";
        $sql .= " ,   D_NCreditNoticeMonthlyFeeDiff = :D_NCreditNoticeMonthlyFeeDiff ";
        $sql .= " ,   D_NCreditNoticeMonthlyFeeTaxDiff = :D_NCreditNoticeMonthlyFeeTaxDiff ";
        $sql .= " ,   D_ReserveMonthlyFeeDiff = :D_ReserveMonthlyFeeDiff ";
        $sql .= " ,   D_ReserveMonthlyFeeTaxDiff = :D_ReserveMonthlyFeeTaxDiff ";
        $sql .= " ,   D_AddClaimFeeDiff = :D_AddClaimFeeDiff ";
        $sql .= " ,   D_AddClaimFeeTaxDiff = :D_AddClaimFeeTaxDiff ";
        $sql .= " ,   D_DamageInterestAmountDiff = :D_DamageInterestAmountDiff ";
        $sql .= " ,   MB__AccountsReceivableBalance = :MB__AccountsReceivableBalance ";
        $sql .= " ,   M_ChargeCount = :M_ChargeCount ";
        $sql .= " ,   M_ChargeAmount = :M_ChargeAmount ";
        $sql .= " ,   M_CancelCount = :M_CancelCount ";
        $sql .= " ,   M_CancelAmount = :M_CancelAmount ";
        $sql .= " ,   M_SettlementBackCount = :M_SettlementBackCount ";
        $sql .= " ,   M_SettlementBackAmount = :M_SettlementBackAmount ";
        $sql .= " ,   M_TransferCount = :M_TransferCount ";
        $sql .= " ,   M_TransferAmount = :M_TransferAmount ";
        $sql .= " ,   M_ReClaimFeeCount = :M_ReClaimFeeCount ";
        $sql .= " ,   M_ReClaimFeeAmount = :M_ReClaimFeeAmount ";
        $sql .= " ,   M_DamageCount = :M_DamageCount ";
        $sql .= " ,   M_DamageAmount = :M_DamageAmount ";
        $sql .= " ,   M_ReceiptCount = :M_ReceiptCount ";
        $sql .= " ,   M_ReceiptAmount = :M_ReceiptAmount ";
        $sql .= " ,   M_RepayCount = :M_RepayCount ";
        $sql .= " ,   M_RepayAmount = :M_RepayAmount ";
        $sql .= " ,   M_BadDebtCount = :M_BadDebtCount ";
        $sql .= " ,   M_BadDebtAmount = :M_BadDebtAmount ";
        $sql .= " ,   M_OtherPaymentCount = :M_OtherPaymentCount ";
        $sql .= " ,   M_OtherPaymentAmount = :M_OtherPaymentAmount ";
        $sql .= " ,   M_AccountsReceivableBalance = :M_AccountsReceivableBalance ";
        $sql .= " ,   M_SuspensePaymentsAmount = :M_SuspensePaymentsAmount ";
        $sql .= " ,   M_AccountsReceivableBalanceDiff = :M_AccountsReceivableBalanceDiff ";
        $sql .= " ,   M_SettlementFee = :M_SettlementFee ";
        $sql .= " ,   M_ClaimFee = :M_ClaimFee ";
        $sql .= " ,   M_ClaimFeeTax = :M_ClaimFeeTax ";
        $sql .= " ,   M_MonthlyFee = :M_MonthlyFee ";
        $sql .= " ,   M_MonthlyFeeTax = :M_MonthlyFeeTax ";
        $sql .= " ,   M_IncludeMonthlyFee = :M_IncludeMonthlyFee ";
        $sql .= " ,   M_IncludeMonthlyFeeTax = :M_IncludeMonthlyFeeTax ";
        $sql .= " ,   M_ApiMonthlyFee = :M_ApiMonthlyFee ";
        $sql .= " ,   M_ApiMonthlyFeeTax = :M_ApiMonthlyFeeTax ";
        $sql .= " ,   M_CreditNoticeMonthlyFee = :M_CreditNoticeMonthlyFee ";
        $sql .= " ,   M_CreditNoticeMonthlyFeeTax = :M_CreditNoticeMonthlyFeeTax ";
        $sql .= " ,   M_NCreditNoticeMonthlyFee = :M_NCreditNoticeMonthlyFee ";
        $sql .= " ,   M_NCreditNoticeMonthlyFeeTax = :M_NCreditNoticeMonthlyFeeTax ";
        $sql .= " ,   M_ReserveMonthlyFee = :M_ReserveMonthlyFee ";
        $sql .= " ,   M_ReserveMonthlyFeeTax = :M_ReserveMonthlyFeeTax ";
        $sql .= " ,   M_AddClaimFee = :M_AddClaimFee ";
        $sql .= " ,   M_AddClaimFeeTax = :M_AddClaimFeeTax ";
        $sql .= " ,   M_DamageInterestAmount = :M_DamageInterestAmount ";
        $sql .= " ,   M_CanSettlementFee = :M_CanSettlementFee ";
        $sql .= " ,   M_CanSettlementFeeTax = :M_CanSettlementFeeTax ";
        $sql .= " ,   M_CanClaimFee = :M_CanClaimFee ";
        $sql .= " ,   M_CanClaimFeeTax = :M_CanClaimFeeTax ";
        $sql .= " ,   M_SettlementFeeTotal = :M_SettlementFeeTotal ";
        $sql .= " ,   M_ClaimFeeTotal = :M_ClaimFeeTotal ";
        $sql .= " ,   M_ClaimFeeTaxTotal = :M_ClaimFeeTaxTotal ";
        $sql .= " ,   M_MonthlyFeeTotal = :M_MonthlyFeeTotal ";
        $sql .= " ,   M_MonthlyFeeTaxTotal = :M_MonthlyFeeTaxTotal ";
        $sql .= " ,   M_IncludeMonthlyFeeTotal = :M_IncludeMonthlyFeeTotal ";
        $sql .= " ,   M_IncludeMonthlyFeeTaxTotal = :M_IncludeMonthlyFeeTaxTotal ";
        $sql .= " ,   M_ApiMonthlyFeeTotal = :M_ApiMonthlyFeeTotal ";
        $sql .= " ,   M_ApiMonthlyFeeTaxTotal = :M_ApiMonthlyFeeTaxTotal ";
        $sql .= " ,   M_CreditNoticeMonthlyFeeTotal = :M_CreditNoticeMonthlyFeeTotal ";
        $sql .= " ,   M_CreditNoticeMonthlyFeeTaxTotal = :M_CreditNoticeMonthlyFeeTaxTotal ";
        $sql .= " ,   M_NCreditNoticeMonthlyFeeTotal = :M_NCreditNoticeMonthlyFeeTotal ";
        $sql .= " ,   M_NCreditNoticeMonthlyFeeTaxTotal = :M_NCreditNoticeMonthlyFeeTaxTotal ";
        $sql .= " ,   M_ReserveMonthlyFeeTotal = :M_ReserveMonthlyFeeTotal ";
        $sql .= " ,   M_ReserveMonthlyFeeTaxTotal = :M_ReserveMonthlyFeeTaxTotal ";
        $sql .= " ,   M_AddClaimFeeTotal = :M_AddClaimFeeTotal ";
        $sql .= " ,   M_AddClaimFeeTaxTotal = :M_AddClaimFeeTaxTotal ";
        $sql .= " ,   M_DamageInterestAmountTotal = :M_DamageInterestAmountTotal ";
        $sql .= " ,   M_AllTotal = :M_AllTotal ";
        $sql .= " ,   M_SettlementFeeOther = :M_SettlementFeeOther ";
        $sql .= " ,   M_ClaimFeeOther = :M_ClaimFeeOther ";
        $sql .= " ,   M_ClaimFeeTaxOther = :M_ClaimFeeTaxOther ";
        $sql .= " ,   M_MonthlyFeeOther = :M_MonthlyFeeOther ";
        $sql .= " ,   M_MonthlyFeeTaxOther = :M_MonthlyFeeTaxOther ";
        $sql .= " ,   M_IncludeMonthlyFeeOther = :M_IncludeMonthlyFeeOther ";
        $sql .= " ,   M_IncludeMonthlyFeeTaxOther = :M_IncludeMonthlyFeeTaxOther ";
        $sql .= " ,   M_ApiMonthlyFeeOther = :M_ApiMonthlyFeeOther ";
        $sql .= " ,   M_ApiMonthlyFeeTaxOther = :M_ApiMonthlyFeeTaxOther ";
        $sql .= " ,   M_CreditNoticeMonthlyFeeOther = :M_CreditNoticeMonthlyFeeOther ";
        $sql .= " ,   M_CreditNoticeMonthlyFeeTaxOther = :M_CreditNoticeMonthlyFeeTaxOther ";
        $sql .= " ,   M_NCreditNoticeMonthlyFeeOther = :M_NCreditNoticeMonthlyFeeOther ";
        $sql .= " ,   M_NCreditNoticeMonthlyFeeTaxOther = :M_NCreditNoticeMonthlyFeeTaxOther ";
        $sql .= " ,   M_ReserveMonthlyFeeOther = :M_ReserveMonthlyFeeOther ";
        $sql .= " ,   M_ReserveMonthlyFeeTaxOther = :M_ReserveMonthlyFeeTaxOther ";
        $sql .= " ,   M_AddClaimFeeOther = :M_AddClaimFeeOther ";
        $sql .= " ,   M_AddClaimFeeTaxOther = :M_AddClaimFeeTaxOther ";
        $sql .= " ,   M_DamageInterestAmountOther = :M_DamageInterestAmountOther ";
        $sql .= " ,   M_SettlementFeeDiff = :M_SettlementFeeDiff ";
        $sql .= " ,   M_ClaimFeeDiff = :M_ClaimFeeDiff ";
        $sql .= " ,   M_ClaimFeeTaxDiff = :M_ClaimFeeTaxDiff ";
        $sql .= " ,   M_MonthlyFeeDiff = :M_MonthlyFeeDiff ";
        $sql .= " ,   M_MonthlyFeeTaxDiff = :M_MonthlyFeeTaxDiff ";
        $sql .= " ,   M_IncludeMonthlyFeeDiff = :M_IncludeMonthlyFeeDiff ";
        $sql .= " ,   M_IncludeMonthlyFeeTaxDiff = :M_IncludeMonthlyFeeTaxDiff ";
        $sql .= " ,   M_ApiMonthlyFeeDiff = :M_ApiMonthlyFeeDiff ";
        $sql .= " ,   M_ApiMonthlyFeeTaxDiff = :M_ApiMonthlyFeeTaxDiff ";
        $sql .= " ,   M_CreditNoticeMonthlyFeeDiff = :M_CreditNoticeMonthlyFeeDiff ";
        $sql .= " ,   M_CreditNoticeMonthlyFeeTaxDiff = :M_CreditNoticeMonthlyFeeTaxDiff ";
        $sql .= " ,   M_NCreditNoticeMonthlyFeeDiff = :M_NCreditNoticeMonthlyFeeDiff ";
        $sql .= " ,   M_NCreditNoticeMonthlyFeeTaxDiff = :M_NCreditNoticeMonthlyFeeTaxDiff ";
        $sql .= " ,   M_ReserveMonthlyFeeDiff = :M_ReserveMonthlyFeeDiff ";
        $sql .= " ,   M_ReserveMonthlyFeeTaxDiff = :M_ReserveMonthlyFeeTaxDiff ";
        $sql .= " ,   M_AddClaimFeeDiff = :M_AddClaimFeeDiff ";
        $sql .= " ,   M_AddClaimFeeTaxDiff = :M_AddClaimFeeTaxDiff ";
        $sql .= " ,   M_DamageInterestAmountDiff = :M_DamageInterestAmountDiff ";
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
                ':AccountDate' => $row['AccountDate'],
                ':EnterpriseId' => $row['EnterpriseId'],
                ':EnterpriseNameKj' => $row['EnterpriseNameKj'],
                ':DB__AccountsReceivableBalance' => $row['DB__AccountsReceivableBalance'],
                ':D_ChargeCount' => $row['D_ChargeCount'],
                ':D_ChargeAmount' => $row['D_ChargeAmount'],
                ':D_CancelCount' => $row['D_CancelCount'],
                ':D_CancelAmount' => $row['D_CancelAmount'],
                ':D_SettlementBackCount' => $row['D_SettlementBackCount'],
                ':D_SettlementBackAmount' => $row['D_SettlementBackAmount'],
                ':D_OemTransferCount' => $row['D_OemTransferCount'],
                ':D_OemTransferAmount' => $row['D_OemTransferAmount'],
                ':D_ReClaimFeeCount' => $row['D_ReClaimFeeCount'],
                ':D_ReClaimFeeAmount' => $row['D_ReClaimFeeAmount'],
                ':D_DamageCount' => $row['D_DamageCount'],
                ':D_DamageAmount' => $row['D_DamageAmount'],
                ':D_ReceiptCount' => $row['D_ReceiptCount'],
                ':D_ReceiptAmount' => $row['D_ReceiptAmount'],
                ':D_RepayCount' => $row['D_RepayCount'],
                ':D_RepayAmount' => $row['D_RepayAmount'],
                ':D_BadDebtCount' => $row['D_BadDebtCount'],
                ':D_BadDebtAmount' => $row['D_BadDebtAmount'],
                ':D_OtherPaymentCount' => $row['D_OtherPaymentCount'],
                ':D_OtherPaymentAmount' => $row['D_OtherPaymentAmount'],
                ':D_AccountsReceivableBalance' => $row['D_AccountsReceivableBalance'],
                ':D_SettlementFee' => $row['D_SettlementFee'],
                ':D_SettlementFeeTax' => $row['D_SettlementFeeTax'],
                ':D_ClaimFee' => $row['D_ClaimFee'],
                ':D_ClaimFeeTax' => $row['D_ClaimFeeTax'],
                ':D_MonthlyFee' => $row['D_MonthlyFee'],
                ':D_MonthlyFeeTax' => $row['D_MonthlyFeeTax'],
                ':D_IncludeMonthlyFee' => $row['D_IncludeMonthlyFee'],
                ':D_IncludeMonthlyFeeTax' => $row['D_IncludeMonthlyFeeTax'],
                ':D_ApiMonthlyFee' => $row['D_ApiMonthlyFee'],
                ':D_ApiMonthlyFeeTax' => $row['D_ApiMonthlyFeeTax'],
                ':D_CreditNoticeMonthlyFee' => $row['D_CreditNoticeMonthlyFee'],
                ':D_CreditNoticeMonthlyFeeTax' => $row['D_CreditNoticeMonthlyFeeTax'],
                ':D_NCreditNoticeMonthlyFee' => $row['D_NCreditNoticeMonthlyFee'],
                ':D_NCreditNoticeMonthlyFeeTax' => $row['D_NCreditNoticeMonthlyFeeTax'],
                ':D_ReserveMonthlyFee' => $row['D_ReserveMonthlyFee'],
                ':D_ReserveMonthlyFeeTax' => $row['D_ReserveMonthlyFeeTax'],
                ':D_AddClaimFee' => $row['D_AddClaimFee'],
                ':D_AddClaimFeeTax' => $row['D_AddClaimFeeTax'],
                ':D_DamageInterestAmount' => $row['D_DamageInterestAmount'],
                ':D_CanSettlementFee' => $row['D_CanSettlementFee'],
                ':D_CanSettlementFeeTax' => $row['D_CanSettlementFeeTax'],
                ':D_CanClaimFee' => $row['D_CanClaimFee'],
                ':D_CanClaimFeeTax' => $row['D_CanClaimFeeTax'],
                ':D_SettlementFeeTotal' => $row['D_SettlementFeeTotal'],
                ':D_SettlementFeeTaxTotal' => $row['D_SettlementFeeTaxTotal'],
                ':D_ClaimFeeTotal' => $row['D_ClaimFeeTotal'],
                ':D_ClaimFeeTaxTotal' => $row['D_ClaimFeeTaxTotal'],
                ':D_MonthlyFeeTotal' => $row['D_MonthlyFeeTotal'],
                ':D_MonthlyFeeTaxTotal' => $row['D_MonthlyFeeTaxTotal'],
                ':D_IncludeMonthlyFeeTotal' => $row['D_IncludeMonthlyFeeTotal'],
                ':D_IncludeMonthlyFeeTaxTotal' => $row['D_IncludeMonthlyFeeTaxTotal'],
                ':D_ApiMonthlyFeeTotal' => $row['D_ApiMonthlyFeeTotal'],
                ':D_ApiMonthlyFeeTaxTotal' => $row['D_ApiMonthlyFeeTaxTotal'],
                ':D_CreditNoticeMonthlyFeeTotal' => $row['D_CreditNoticeMonthlyFeeTotal'],
                ':D_CreditNoticeMonthlyFeeTaxTotal' => $row['D_CreditNoticeMonthlyFeeTaxTotal'],
                ':D_NCreditNoticeMonthlyFeeTotal' => $row['D_NCreditNoticeMonthlyFeeTotal'],
                ':D_NCreditNoticeMonthlyFeeTaxTotal' => $row['D_NCreditNoticeMonthlyFeeTaxTotal'],
                ':D_ReserveMonthlyFeeTotal' => $row['D_ReserveMonthlyFeeTotal'],
                ':D_ReserveMonthlyFeeTaxTotal' => $row['D_ReserveMonthlyFeeTaxTotal'],
                ':D_AddClaimFeeTotal' => $row['D_AddClaimFeeTotal'],
                ':D_AddClaimFeeTaxTotal' => $row['D_AddClaimFeeTaxTotal'],
                ':D_DamageInterestAmountTotal' => $row['D_DamageInterestAmountTotal'],
                ':D_AllTotal' => $row['D_AllTotal'],
                ':D_SettlementFeeOther' => $row['D_SettlementFeeOther'],
                ':D_SettlementFeeTaxOther' => $row['D_SettlementFeeTaxOther'],
                ':D_ClaimFeeOther' => $row['D_ClaimFeeOther'],
                ':D_ClaimFeeTaxOther' => $row['D_ClaimFeeTaxOther'],
                ':D_MonthlyFeeOther' => $row['D_MonthlyFeeOther'],
                ':D_MonthlyFeeTaxOther' => $row['D_MonthlyFeeTaxOther'],
                ':D_IncludeMonthlyFeeOther' => $row['D_IncludeMonthlyFeeOther'],
                ':D_IncludeMonthlyFeeTaxOther' => $row['D_IncludeMonthlyFeeTaxOther'],
                ':D_ApiMonthlyFeeOther' => $row['D_ApiMonthlyFeeOther'],
                ':D_ApiMonthlyFeeTaxOther' => $row['D_ApiMonthlyFeeTaxOther'],
                ':D_CreditNoticeMonthlyFeeOther' => $row['D_CreditNoticeMonthlyFeeOther'],
                ':D_CreditNoticeMonthlyFeeTaxOther' => $row['D_CreditNoticeMonthlyFeeTaxOther'],
                ':D_NCreditNoticeMonthlyFeeOther' => $row['D_NCreditNoticeMonthlyFeeOther'],
                ':D_NCreditNoticeMonthlyFeeTaxOther' => $row['D_NCreditNoticeMonthlyFeeTaxOther'],
                ':D_ReserveMonthlyFeeOther' => $row['D_ReserveMonthlyFeeOther'],
                ':D_ReserveMonthlyFeeTaxOther' => $row['D_ReserveMonthlyFeeTaxOther'],
                ':D_AddClaimFeeOther' => $row['D_AddClaimFeeOther'],
                ':D_AddClaimFeeTaxOther' => $row['D_AddClaimFeeTaxOther'],
                ':D_DamageInterestAmountOther' => $row['D_DamageInterestAmountOther'],
                ':D_SettlementFeeDiff' => $row['D_SettlementFeeDiff'],
                ':D_SettlementFeeTaxDiff' => $row['D_SettlementFeeTaxDiff'],
                ':D_ClaimFeeDiff' => $row['D_ClaimFeeDiff'],
                ':D_ClaimFeeTaxDiff' => $row['D_ClaimFeeTaxDiff'],
                ':D_MonthlyFeeDiff' => $row['D_MonthlyFeeDiff'],
                ':D_MonthlyFeeTaxDiff' => $row['D_MonthlyFeeTaxDiff'],
                ':D_IncludeMonthlyFeeDiff' => $row['D_IncludeMonthlyFeeDiff'],
                ':D_IncludeMonthlyFeeTaxDiff' => $row['D_IncludeMonthlyFeeTaxDiff'],
                ':D_ApiMonthlyFeeDiff' => $row['D_ApiMonthlyFeeDiff'],
                ':D_ApiMonthlyFeeTaxDiff' => $row['D_ApiMonthlyFeeTaxDiff'],
                ':D_CreditNoticeMonthlyFeeDiff' => $row['D_CreditNoticeMonthlyFeeDiff'],
                ':D_CreditNoticeMonthlyFeeTaxDiff' => $row['D_CreditNoticeMonthlyFeeTaxDiff'],
                ':D_NCreditNoticeMonthlyFeeDiff' => $row['D_NCreditNoticeMonthlyFeeDiff'],
                ':D_NCreditNoticeMonthlyFeeTaxDiff' => $row['D_NCreditNoticeMonthlyFeeTaxDiff'],
                ':D_ReserveMonthlyFeeDiff' => $row['D_ReserveMonthlyFeeDiff'],
                ':D_ReserveMonthlyFeeTaxDiff' => $row['D_ReserveMonthlyFeeTaxDiff'],
                ':D_AddClaimFeeDiff' => $row['D_AddClaimFeeDiff'],
                ':D_AddClaimFeeTaxDiff' => $row['D_AddClaimFeeTaxDiff'],
                ':D_DamageInterestAmountDiff' => $row['D_DamageInterestAmountDiff'],
                ':MB__AccountsReceivableBalance' => $row['MB__AccountsReceivableBalance'],
                ':M_ChargeCount' => $row['M_ChargeCount'],
                ':M_ChargeAmount' => $row['M_ChargeAmount'],
                ':M_CancelCount' => $row['M_CancelCount'],
                ':M_CancelAmount' => $row['M_CancelAmount'],
                ':M_SettlementBackCount' => $row['M_SettlementBackCount'],
                ':M_SettlementBackAmount' => $row['M_SettlementBackAmount'],
                ':M_TransferCount' => $row['M_TransferCount'],
                ':M_TransferAmount' => $row['M_TransferAmount'],
                ':M_ReClaimFeeCount' => $row['M_ReClaimFeeCount'],
                ':M_ReClaimFeeAmount' => $row['M_ReClaimFeeAmount'],
                ':M_DamageCount' => $row['M_DamageCount'],
                ':M_DamageAmount' => $row['M_DamageAmount'],
                ':M_ReceiptCount' => $row['M_ReceiptCount'],
                ':M_ReceiptAmount' => $row['M_ReceiptAmount'],
                ':M_RepayCount' => $row['M_RepayCount'],
                ':M_RepayAmount' => $row['M_RepayAmount'],
                ':M_BadDebtCount' => $row['M_BadDebtCount'],
                ':M_BadDebtAmount' => $row['M_BadDebtAmount'],
                ':M_OtherPaymentCount' => $row['M_OtherPaymentCount'],
                ':M_OtherPaymentAmount' => $row['M_OtherPaymentAmount'],
                ':M_AccountsReceivableBalance' => $row['M_AccountsReceivableBalance'],
                ':M_SuspensePaymentsAmount' => $row['M_SuspensePaymentsAmount'],
                ':M_AccountsReceivableBalanceDiff' => $row['M_AccountsReceivableBalanceDiff'],
                ':M_SettlementFee' => $row['M_SettlementFee'],
                ':M_ClaimFee' => $row['M_ClaimFee'],
                ':M_ClaimFeeTax' => $row['M_ClaimFeeTax'],
                ':M_MonthlyFee' => $row['M_MonthlyFee'],
                ':M_MonthlyFeeTax' => $row['M_MonthlyFeeTax'],
                ':M_IncludeMonthlyFee' => $row['M_IncludeMonthlyFee'],
                ':M_IncludeMonthlyFeeTax' => $row['M_IncludeMonthlyFeeTax'],
                ':M_ApiMonthlyFee' => $row['M_ApiMonthlyFee'],
                ':M_ApiMonthlyFeeTax' => $row['M_ApiMonthlyFeeTax'],
                ':M_CreditNoticeMonthlyFee' => $row['M_CreditNoticeMonthlyFee'],
                ':M_CreditNoticeMonthlyFeeTax' => $row['M_CreditNoticeMonthlyFeeTax'],
                ':M_NCreditNoticeMonthlyFee' => $row['M_NCreditNoticeMonthlyFee'],
                ':M_NCreditNoticeMonthlyFeeTax' => $row['M_NCreditNoticeMonthlyFeeTax'],
                ':M_ReserveMonthlyFee' => $row['M_ReserveMonthlyFee'],
                ':M_ReserveMonthlyFeeTax' => $row['M_ReserveMonthlyFeeTax'],
                ':M_AddClaimFee' => $row['M_AddClaimFee'],
                ':M_AddClaimFeeTax' => $row['M_AddClaimFeeTax'],
                ':M_DamageInterestAmount' => $row['M_DamageInterestAmount'],
                ':M_CanSettlementFee' => $row['M_CanSettlementFee'],
                ':M_CanSettlementFeeTax' => $row['M_CanSettlementFeeTax'],
                ':M_CanClaimFee' => $row['M_CanClaimFee'],
                ':M_CanClaimFeeTax' => $row['M_CanClaimFeeTax'],
                ':M_SettlementFeeTotal' => $row['M_SettlementFeeTotal'],
                ':M_ClaimFeeTotal' => $row['M_ClaimFeeTotal'],
                ':M_ClaimFeeTaxTotal' => $row['M_ClaimFeeTaxTotal'],
                ':M_MonthlyFeeTotal' => $row['M_MonthlyFeeTotal'],
                ':M_MonthlyFeeTaxTotal' => $row['M_MonthlyFeeTaxTotal'],
                ':M_IncludeMonthlyFeeTotal' => $row['M_IncludeMonthlyFeeTotal'],
                ':M_IncludeMonthlyFeeTaxTotal' => $row['M_IncludeMonthlyFeeTaxTotal'],
                ':M_ApiMonthlyFeeTotal' => $row['M_ApiMonthlyFeeTotal'],
                ':M_ApiMonthlyFeeTaxTotal' => $row['M_ApiMonthlyFeeTaxTotal'],
                ':M_CreditNoticeMonthlyFeeTotal' => $row['M_CreditNoticeMonthlyFeeTotal'],
                ':M_CreditNoticeMonthlyFeeTaxTotal' => $row['M_CreditNoticeMonthlyFeeTaxTotal'],
                ':M_NCreditNoticeMonthlyFeeTotal' => $row['M_NCreditNoticeMonthlyFeeTotal'],
                ':M_NCreditNoticeMonthlyFeeTaxTotal' => $row['M_NCreditNoticeMonthlyFeeTaxTotal'],
                ':M_ReserveMonthlyFeeTotal' => $row['M_ReserveMonthlyFeeTotal'],
                ':M_ReserveMonthlyFeeTaxTotal' => $row['M_ReserveMonthlyFeeTaxTotal'],
                ':M_AddClaimFeeTotal' => $row['M_AddClaimFeeTotal'],
                ':M_AddClaimFeeTaxTotal' => $row['M_AddClaimFeeTaxTotal'],
                ':M_DamageInterestAmountTotal' => $row['M_DamageInterestAmountTotal'],
                ':M_AllTotal' => $row['M_AllTotal'],
                ':M_SettlementFeeOther' => $row['M_SettlementFeeOther'],
                ':M_ClaimFeeOther' => $row['M_ClaimFeeOther'],
                ':M_ClaimFeeTaxOther' => $row['M_ClaimFeeTaxOther'],
                ':M_MonthlyFeeOther' => $row['M_MonthlyFeeOther'],
                ':M_MonthlyFeeTaxOther' => $row['M_MonthlyFeeTaxOther'],
                ':M_IncludeMonthlyFeeOther' => $row['M_IncludeMonthlyFeeOther'],
                ':M_IncludeMonthlyFeeTaxOther' => $row['M_IncludeMonthlyFeeTaxOther'],
                ':M_ApiMonthlyFeeOther' => $row['M_ApiMonthlyFeeOther'],
                ':M_ApiMonthlyFeeTaxOther' => $row['M_ApiMonthlyFeeTaxOther'],
                ':M_CreditNoticeMonthlyFeeOther' => $row['M_CreditNoticeMonthlyFeeOther'],
                ':M_CreditNoticeMonthlyFeeTaxOther' => $row['M_CreditNoticeMonthlyFeeTaxOther'],
                ':M_NCreditNoticeMonthlyFeeOther' => $row['M_NCreditNoticeMonthlyFeeOther'],
                ':M_NCreditNoticeMonthlyFeeTaxOther' => $row['M_NCreditNoticeMonthlyFeeTaxOther'],
                ':M_ReserveMonthlyFeeOther' => $row['M_ReserveMonthlyFeeOther'],
                ':M_ReserveMonthlyFeeTaxOther' => $row['M_ReserveMonthlyFeeTaxOther'],
                ':M_AddClaimFeeOther' => $row['M_AddClaimFeeOther'],
                ':M_AddClaimFeeTaxOther' => $row['M_AddClaimFeeTaxOther'],
                ':M_DamageInterestAmountOther' => $row['M_DamageInterestAmountOther'],
                ':M_SettlementFeeDiff' => $row['M_SettlementFeeDiff'],
                ':M_ClaimFeeDiff' => $row['M_ClaimFeeDiff'],
                ':M_ClaimFeeTaxDiff' => $row['M_ClaimFeeTaxDiff'],
                ':M_MonthlyFeeDiff' => $row['M_MonthlyFeeDiff'],
                ':M_MonthlyFeeTaxDiff' => $row['M_MonthlyFeeTaxDiff'],
                ':M_IncludeMonthlyFeeDiff' => $row['M_IncludeMonthlyFeeDiff'],
                ':M_IncludeMonthlyFeeTaxDiff' => $row['M_IncludeMonthlyFeeTaxDiff'],
                ':M_ApiMonthlyFeeDiff' => $row['M_ApiMonthlyFeeDiff'],
                ':M_ApiMonthlyFeeTaxDiff' => $row['M_ApiMonthlyFeeTaxDiff'],
                ':M_CreditNoticeMonthlyFeeDiff' => $row['M_CreditNoticeMonthlyFeeDiff'],
                ':M_CreditNoticeMonthlyFeeTaxDiff' => $row['M_CreditNoticeMonthlyFeeTaxDiff'],
                ':M_NCreditNoticeMonthlyFeeDiff' => $row['M_NCreditNoticeMonthlyFeeDiff'],
                ':M_NCreditNoticeMonthlyFeeTaxDiff' => $row['M_NCreditNoticeMonthlyFeeTaxDiff'],
                ':M_ReserveMonthlyFeeDiff' => $row['M_ReserveMonthlyFeeDiff'],
                ':M_ReserveMonthlyFeeTaxDiff' => $row['M_ReserveMonthlyFeeTaxDiff'],
                ':M_AddClaimFeeDiff' => $row['M_AddClaimFeeDiff'],
                ':M_AddClaimFeeTaxDiff' => $row['M_AddClaimFeeTaxDiff'],
                ':M_DamageInterestAmountDiff' => $row['M_DamageInterestAmountDiff'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }
}
