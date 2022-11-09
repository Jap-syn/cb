<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * AT_Oem_DailyStatisticsTable(OEM日次統計表)テーブルへのアダプタ
 */
class ATableOemDailyStatisticsTable
{
    protected $_name = 'AT_Oem_DailyStatisticsTable';
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
     * OEM日次統計表データを取得する
     *
     * @param int $seq シーケンス
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM AT_Oem_DailyStatisticsTable WHERE Seq = :Seq ";

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
        $sql  = " INSERT INTO AT_Oem_DailyStatisticsTable (DailyMonthlyFlg, ProcessingDate, AccountDate, OemId, OemNameKj, EnterpriseId, EnterpriseNameKj, DB__AccountsReceivableBalance, D_ChargeCount, D_ChargeAmount, D_CancelCount, D_CancelAmount, D_SettlementBackCount, D_SettlementBackAmount, D_OemTransferCount, D_OemTransferAmount, D_ReClaimFeeCount, D_ReClaimFeeAmount, D_DamageCount, D_DamageAmount, D_ReceiptCount, D_ReceiptAmount, D_RepayCount, D_RepayAmount, D_BadDebtCount, D_BadDebtAmount, D_OtherPaymentCount, D_OtherPaymentAmount, D_AccountsReceivableBalance, D_SettlementFee, D_SettlementFeeTax, D_ClaimFee, D_ClaimFeeTax, D_MonthlyFee, D_MonthlyFeeTax, D_OemIncludeMonthlyFee, D_OemIncludeMonthlyFeeTax, D_OemApiMonthlyFee, D_OemApiMonthlyFeeTax, D_OemCreditNoticeMonthlyFee, D_OemCreditNoticeMonthlyFeeTax, D_OemNCreditNoticeMonthlyFee, D_OemNCreditNoticeMonthlyFeeTax, D_OemReserveMonthlyFee, D_OemReserveMonthlyFeeTax, D_AddClaimFee, D_AddClaimFeeTax, D_DamageInterestAmount, D_CanSettlementFee, D_CanSettlementFeeTax, D_CanClaimFee, D_CanClaimFeeTax, D_SettlementFeeTotal, D_SettlementFeeTaxTotal, D_ClaimFeeTotal, D_ClaimFeeTaxTotal, D_MonthlyFeeTotal, D_MonthlyFeeTaxTotal, D_OemIncludeMonthlyFeeTotal, D_OemIncludeMonthlyFeeTaxTotal, D_OemApiMonthlyFeeTotal, D_OemApiMonthlyFeeTaxTotal, D_OemCreditNoticeMonthlyFeeTotal, D_OemCreditNoticeMonthlyFeeTaxTotal, D_OemNCreditNoticeMonthlyFeeTotal, D_OemNCreditNoticeMonthlyFeeTaxTotal, D_OemReserveMonthlyFeeTotal, D_OemReserveMonthlyFeeTaxTotal, D_AddClaimFeeTotal, D_AddClaimFeeTaxTotal, D_DamageInterestAmountTotal, D_AllTotal, D_SettlementFeeOther, D_SettlementFeeTaxOther, D_ClaimFeeOther, D_ClaimFeeTaxOther, D_MonthlyFeeOther, D_MonthlyFeeTaxOther, D_OemIncludeMonthlyFeeOther, D_OemIncludeMonthlyFeeTaxOther, D_OemApiMonthlyFeeOther, D_OemApiMonthlyFeeTaxOther, D_OemCreditNoticeMonthlyFeeOther, D_OemCreditNoticeMonthlyFeeTaxOther, D_OemNCreditNoticeMonthlyFeeOther, D_OemNCreditNoticeMonthlyFeeTaxOther, D_OemReserveMonthlyFeeOther, D_OemReserveMonthlyFeeTaxOther, D_AddClaimFeeOther, D_AddClaimFeeTaxOther, D_DamageInterestAmountOther, D_SettlementFeeDiff, D_SettlementFeeTaxDiff, D_ClaimFeeDiff, D_ClaimFeeTaxDiff, D_MonthlyFeeDiff, D_MonthlyFeeTaxDiff, D_OemIncludeMonthlyFeeDiff, D_OemIncludeMonthlyFeeTaxDiff, D_OemApiMonthlyFeeDiff, D_OemApiMonthlyFeeTaxDiff, D_OemCreditNoticeMonthlyFeeDiff, D_OemCreditNoticeMonthlyFeeTaxDiff, D_OemNCreditNoticeMonthlyFeeDiff, D_OemNCreditNoticeMonthlyFeeTaxDiff, D_OemReserveMonthlyFeeDiff, D_OemReserveMonthlyFeeTaxDiff, D_AddClaimFeeDiff, D_AddClaimFeeTaxDiff, D_DamageInterestAmountDiff, MB__AccountsReceivableBalance, M_ChargeCount, M_ChargeAmount, M_CancelCount, M_CancelAmount, M_SettlementBackCount, M_SettlementBackAmount, M_OemTransferCount, M_OemTransferAmount, M_ReClaimFeeCount, M_ReClaimFeeAmount, M_DamageCount, M_DamageAmount, M_ReceiptCount, M_ReceiptAmount, M_RepayCount, M_RepayAmount, M_BadDebtCount, M_BadDebtAmount, M_OtherPaymentCount, M_OtherPaymentAmount, M_AccountsReceivableBalance, M_SuspensePaymentsAmount, M_AccountsReceivableBalanceDiff, M_SettlementFee, M_SettlementFeeTax, M_ClaimFee, M_ClaimFeeTax, M_MonthlyFee, M_MonthlyFeeTax, M_OemIncludeMonthlyFee, M_OemIncludeMonthlyFeeTax, M_OemApiMonthlyFee, M_OemApiMonthlyFeeTax, M_OemCreditNoticeMonthlyFee, M_OemCreditNoticeMonthlyFeeTax, M_OemNCreditNoticeMonthlyFee, M_OemNCreditNoticeMonthlyFeeTax, M_OemReserveMonthlyFee, M_OemReserveMonthlyFeeTax, M_AddClaimFee, M_AddClaimFeeTax, M_DamageInterestAmount, M_CanSettlementFee, M_CanSettlementFeeTax, M_CanClaimFee, M_CanClaimFeeTax, M_SettlementFeeTotal, M_SettlementFeeTaxTotal, M_ClaimFeeTotal, M_ClaimFeeTaxTotal, M_MonthlyFeeTotal, M_MonthlyFeeTaxTotal, M_OemIncludeMonthlyFeeTotal, M_OemIncludeMonthlyFeeTaxTotal, M_OemApiMonthlyFeeTotal, M_OemApiMonthlyFeeTaxTotal, M_OemCreditNoticeMonthlyFeeTotal, M_OemCreditNoticeMonthlyFeeTaxTotal, M_OemNCreditNoticeMonthlyFeeTotal, M_OemNCreditNoticeMonthlyFeeTaxTotal, M_OemReserveMonthlyFeeTotal, M_OemReserveMonthlyFeeTaxTotal, M_AddClaimFeeTotal, M_AddClaimFeeTaxTotal, M_DamageInterestAmountTotal, M_AllTotal, M_SettlementFeeOther, M_SettlementFeeTaxOther, M_ClaimFeeOther, M_ClaimFeeTaxOther, M_MonthlyFeeOther, M_MonthlyFeeTaxOther, M_OemIncludeMonthlyFeeOther, M_OemIncludeMonthlyFeeTaxOther, M_OemApiMonthlyFeeOther, M_OemApiMonthlyFeeTaxOther, M_OemCreditNoticeMonthlyFeeOther, M_OemCreditNoticeMonthlyFeeTaxOther, M_OemNCreditNoticeMonthlyFeeOther, M_OemNCreditNoticeMonthlyFeeTaxOther, M_OemReserveMonthlyFeeOther, M_OemReserveMonthlyFeeTaxOther, M_AddClaimFeeOther, M_AddClaimFeeTaxOther, M_DamageInterestAmountOther, M_SettlementFeeDiff, M_SettlementFeeTaxDiff, M_ClaimFeeDiff, M_ClaimFeeTaxDiff, M_MonthlyFeeDiff, M_MonthlyFeeTaxDiff, M_OemIncludeMonthlyFeeDiff, M_OemIncludeMonthlyFeeTaxDiff, M_OemApiMonthlyFeeDiff, M_OemApiMonthlyFeeTaxDiff, M_OemCreditNoticeMonthlyFeeDiff, M_OemCreditNoticeMonthlyFeeTaxDiff, M_OemNCreditNoticeMonthlyFeeDiff, M_OemNCreditNoticeMonthlyFeeTaxDiff, M_OemReserveMonthlyFeeDiff, M_OemReserveMonthlyFeeTaxDiff, M_AddClaimFeeDiff, M_AddClaimFeeTaxDiff, M_DamageInterestAmountDiff, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :DailyMonthlyFlg ";
        $sql .= " , :ProcessingDate ";
        $sql .= " , :AccountDate ";
        $sql .= " , :OemId ";
        $sql .= " , :OemNameKj ";
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
        $sql .= " , :D_OemIncludeMonthlyFee ";
        $sql .= " , :D_OemIncludeMonthlyFeeTax ";
        $sql .= " , :D_OemApiMonthlyFee ";
        $sql .= " , :D_OemApiMonthlyFeeTax ";
        $sql .= " , :D_OemCreditNoticeMonthlyFee ";
        $sql .= " , :D_OemCreditNoticeMonthlyFeeTax ";
        $sql .= " , :D_OemNCreditNoticeMonthlyFee ";
        $sql .= " , :D_OemNCreditNoticeMonthlyFeeTax ";
        $sql .= " , :D_OemReserveMonthlyFee ";
        $sql .= " , :D_OemReserveMonthlyFeeTax ";
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
        $sql .= " , :D_OemIncludeMonthlyFeeTotal ";
        $sql .= " , :D_OemIncludeMonthlyFeeTaxTotal ";
        $sql .= " , :D_OemApiMonthlyFeeTotal ";
        $sql .= " , :D_OemApiMonthlyFeeTaxTotal ";
        $sql .= " , :D_OemCreditNoticeMonthlyFeeTotal ";
        $sql .= " , :D_OemCreditNoticeMonthlyFeeTaxTotal ";
        $sql .= " , :D_OemNCreditNoticeMonthlyFeeTotal ";
        $sql .= " , :D_OemNCreditNoticeMonthlyFeeTaxTotal ";
        $sql .= " , :D_OemReserveMonthlyFeeTotal ";
        $sql .= " , :D_OemReserveMonthlyFeeTaxTotal ";
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
        $sql .= " , :D_OemIncludeMonthlyFeeOther ";
        $sql .= " , :D_OemIncludeMonthlyFeeTaxOther ";
        $sql .= " , :D_OemApiMonthlyFeeOther ";
        $sql .= " , :D_OemApiMonthlyFeeTaxOther ";
        $sql .= " , :D_OemCreditNoticeMonthlyFeeOther ";
        $sql .= " , :D_OemCreditNoticeMonthlyFeeTaxOther ";
        $sql .= " , :D_OemNCreditNoticeMonthlyFeeOther ";
        $sql .= " , :D_OemNCreditNoticeMonthlyFeeTaxOther ";
        $sql .= " , :D_OemReserveMonthlyFeeOther ";
        $sql .= " , :D_OemReserveMonthlyFeeTaxOther ";
        $sql .= " , :D_AddClaimFeeOther ";
        $sql .= " , :D_AddClaimFeeTaxOther ";
        $sql .= " , :D_DamageInterestAmountOther ";
        $sql .= " , :D_SettlementFeeDiff ";
        $sql .= " , :D_SettlementFeeTaxDiff ";
        $sql .= " , :D_ClaimFeeDiff ";
        $sql .= " , :D_ClaimFeeTaxDiff ";
        $sql .= " , :D_MonthlyFeeDiff ";
        $sql .= " , :D_MonthlyFeeTaxDiff ";
        $sql .= " , :D_OemIncludeMonthlyFeeDiff ";
        $sql .= " , :D_OemIncludeMonthlyFeeTaxDiff ";
        $sql .= " , :D_OemApiMonthlyFeeDiff ";
        $sql .= " , :D_OemApiMonthlyFeeTaxDiff ";
        $sql .= " , :D_OemCreditNoticeMonthlyFeeDiff ";
        $sql .= " , :D_OemCreditNoticeMonthlyFeeTaxDiff ";
        $sql .= " , :D_OemNCreditNoticeMonthlyFeeDiff ";
        $sql .= " , :D_OemNCreditNoticeMonthlyFeeTaxDiff ";
        $sql .= " , :D_OemReserveMonthlyFeeDiff ";
        $sql .= " , :D_OemReserveMonthlyFeeTaxDiff ";
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
        $sql .= " , :M_OemTransferCount ";
        $sql .= " , :M_OemTransferAmount ";
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
        $sql .= " , :M_SettlementFeeTax ";
        $sql .= " , :M_ClaimFee ";
        $sql .= " , :M_ClaimFeeTax ";
        $sql .= " , :M_MonthlyFee ";
        $sql .= " , :M_MonthlyFeeTax ";
        $sql .= " , :M_OemIncludeMonthlyFee ";
        $sql .= " , :M_OemIncludeMonthlyFeeTax ";
        $sql .= " , :M_OemApiMonthlyFee ";
        $sql .= " , :M_OemApiMonthlyFeeTax ";
        $sql .= " , :M_OemCreditNoticeMonthlyFee ";
        $sql .= " , :M_OemCreditNoticeMonthlyFeeTax ";
        $sql .= " , :M_OemNCreditNoticeMonthlyFee ";
        $sql .= " , :M_OemNCreditNoticeMonthlyFeeTax ";
        $sql .= " , :M_OemReserveMonthlyFee ";
        $sql .= " , :M_OemReserveMonthlyFeeTax ";
        $sql .= " , :M_AddClaimFee ";
        $sql .= " , :M_AddClaimFeeTax ";
        $sql .= " , :M_DamageInterestAmount ";
        $sql .= " , :M_CanSettlementFee ";
        $sql .= " , :M_CanSettlementFeeTax ";
        $sql .= " , :M_CanClaimFee ";
        $sql .= " , :M_CanClaimFeeTax ";
        $sql .= " , :M_SettlementFeeTotal ";
        $sql .= " , :M_SettlementFeeTaxTotal ";
        $sql .= " , :M_ClaimFeeTotal ";
        $sql .= " , :M_ClaimFeeTaxTotal ";
        $sql .= " , :M_MonthlyFeeTotal ";
        $sql .= " , :M_MonthlyFeeTaxTotal ";
        $sql .= " , :M_OemIncludeMonthlyFeeTotal ";
        $sql .= " , :M_OemIncludeMonthlyFeeTaxTotal ";
        $sql .= " , :M_OemApiMonthlyFeeTotal ";
        $sql .= " , :M_OemApiMonthlyFeeTaxTotal ";
        $sql .= " , :M_OemCreditNoticeMonthlyFeeTotal ";
        $sql .= " , :M_OemCreditNoticeMonthlyFeeTaxTotal ";
        $sql .= " , :M_OemNCreditNoticeMonthlyFeeTotal ";
        $sql .= " , :M_OemNCreditNoticeMonthlyFeeTaxTotal ";
        $sql .= " , :M_OemReserveMonthlyFeeTotal ";
        $sql .= " , :M_OemReserveMonthlyFeeTaxTotal ";
        $sql .= " , :M_AddClaimFeeTotal ";
        $sql .= " , :M_AddClaimFeeTaxTotal ";
        $sql .= " , :M_DamageInterestAmountTotal ";
        $sql .= " , :M_AllTotal ";
        $sql .= " , :M_SettlementFeeOther ";
        $sql .= " , :M_SettlementFeeTaxOther ";
        $sql .= " , :M_ClaimFeeOther ";
        $sql .= " , :M_ClaimFeeTaxOther ";
        $sql .= " , :M_MonthlyFeeOther ";
        $sql .= " , :M_MonthlyFeeTaxOther ";
        $sql .= " , :M_OemIncludeMonthlyFeeOther ";
        $sql .= " , :M_OemIncludeMonthlyFeeTaxOther ";
        $sql .= " , :M_OemApiMonthlyFeeOther ";
        $sql .= " , :M_OemApiMonthlyFeeTaxOther ";
        $sql .= " , :M_OemCreditNoticeMonthlyFeeOther ";
        $sql .= " , :M_OemCreditNoticeMonthlyFeeTaxOther ";
        $sql .= " , :M_OemNCreditNoticeMonthlyFeeOther ";
        $sql .= " , :M_OemNCreditNoticeMonthlyFeeTaxOther ";
        $sql .= " , :M_OemReserveMonthlyFeeOther ";
        $sql .= " , :M_OemReserveMonthlyFeeTaxOther ";
        $sql .= " , :M_AddClaimFeeOther ";
        $sql .= " , :M_AddClaimFeeTaxOther ";
        $sql .= " , :M_DamageInterestAmountOther ";
        $sql .= " , :M_SettlementFeeDiff ";
        $sql .= " , :M_SettlementFeeTaxDiff ";
        $sql .= " , :M_ClaimFeeDiff ";
        $sql .= " , :M_ClaimFeeTaxDiff ";
        $sql .= " , :M_MonthlyFeeDiff ";
        $sql .= " , :M_MonthlyFeeTaxDiff ";
        $sql .= " , :M_OemIncludeMonthlyFeeDiff ";
        $sql .= " , :M_OemIncludeMonthlyFeeTaxDiff ";
        $sql .= " , :M_OemApiMonthlyFeeDiff ";
        $sql .= " , :M_OemApiMonthlyFeeTaxDiff ";
        $sql .= " , :M_OemCreditNoticeMonthlyFeeDiff ";
        $sql .= " , :M_OemCreditNoticeMonthlyFeeTaxDiff ";
        $sql .= " , :M_OemNCreditNoticeMonthlyFeeDiff ";
        $sql .= " , :M_OemNCreditNoticeMonthlyFeeTaxDiff ";
        $sql .= " , :M_OemReserveMonthlyFeeDiff ";
        $sql .= " , :M_OemReserveMonthlyFeeTaxDiff ";
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
                ':OemId' => $data['OemId'],
                ':OemNameKj' => $data['OemNameKj'],
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
                ':D_OemIncludeMonthlyFee' => $data['D_OemIncludeMonthlyFee'],
                ':D_OemIncludeMonthlyFeeTax' => $data['D_OemIncludeMonthlyFeeTax'],
                ':D_OemApiMonthlyFee' => $data['D_OemApiMonthlyFee'],
                ':D_OemApiMonthlyFeeTax' => $data['D_OemApiMonthlyFeeTax'],
                ':D_OemCreditNoticeMonthlyFee' => $data['D_OemCreditNoticeMonthlyFee'],
                ':D_OemCreditNoticeMonthlyFeeTax' => $data['D_OemCreditNoticeMonthlyFeeTax'],
                ':D_OemNCreditNoticeMonthlyFee' => $data['D_OemNCreditNoticeMonthlyFee'],
                ':D_OemNCreditNoticeMonthlyFeeTax' => $data['D_OemNCreditNoticeMonthlyFeeTax'],
                ':D_OemReserveMonthlyFee' => $data['D_OemReserveMonthlyFee'],
                ':D_OemReserveMonthlyFeeTax' => $data['D_OemReserveMonthlyFeeTax'],
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
                ':D_OemIncludeMonthlyFeeTotal' => $data['D_OemIncludeMonthlyFeeTotal'],
                ':D_OemIncludeMonthlyFeeTaxTotal' => $data['D_OemIncludeMonthlyFeeTaxTotal'],
                ':D_OemApiMonthlyFeeTotal' => $data['D_OemApiMonthlyFeeTotal'],
                ':D_OemApiMonthlyFeeTaxTotal' => $data['D_OemApiMonthlyFeeTaxTotal'],
                ':D_OemCreditNoticeMonthlyFeeTotal' => $data['D_OemCreditNoticeMonthlyFeeTotal'],
                ':D_OemCreditNoticeMonthlyFeeTaxTotal' => $data['D_OemCreditNoticeMonthlyFeeTaxTotal'],
                ':D_OemNCreditNoticeMonthlyFeeTotal' => $data['D_OemNCreditNoticeMonthlyFeeTotal'],
                ':D_OemNCreditNoticeMonthlyFeeTaxTotal' => $data['D_OemNCreditNoticeMonthlyFeeTaxTotal'],
                ':D_OemReserveMonthlyFeeTotal' => $data['D_OemReserveMonthlyFeeTotal'],
                ':D_OemReserveMonthlyFeeTaxTotal' => $data['D_OemReserveMonthlyFeeTaxTotal'],
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
                ':D_OemIncludeMonthlyFeeOther' => $data['D_OemIncludeMonthlyFeeOther'],
                ':D_OemIncludeMonthlyFeeTaxOther' => $data['D_OemIncludeMonthlyFeeTaxOther'],
                ':D_OemApiMonthlyFeeOther' => $data['D_OemApiMonthlyFeeOther'],
                ':D_OemApiMonthlyFeeTaxOther' => $data['D_OemApiMonthlyFeeTaxOther'],
                ':D_OemCreditNoticeMonthlyFeeOther' => $data['D_OemCreditNoticeMonthlyFeeOther'],
                ':D_OemCreditNoticeMonthlyFeeTaxOther' => $data['D_OemCreditNoticeMonthlyFeeTaxOther'],
                ':D_OemNCreditNoticeMonthlyFeeOther' => $data['D_OemNCreditNoticeMonthlyFeeOther'],
                ':D_OemNCreditNoticeMonthlyFeeTaxOther' => $data['D_OemNCreditNoticeMonthlyFeeTaxOther'],
                ':D_OemReserveMonthlyFeeOther' => $data['D_OemReserveMonthlyFeeOther'],
                ':D_OemReserveMonthlyFeeTaxOther' => $data['D_OemReserveMonthlyFeeTaxOther'],
                ':D_AddClaimFeeOther' => $data['D_AddClaimFeeOther'],
                ':D_AddClaimFeeTaxOther' => $data['D_AddClaimFeeTaxOther'],
                ':D_DamageInterestAmountOther' => $data['D_DamageInterestAmountOther'],
                ':D_SettlementFeeDiff' => $data['D_SettlementFeeDiff'],
                ':D_SettlementFeeTaxDiff' => $data['D_SettlementFeeTaxDiff'],
                ':D_ClaimFeeDiff' => $data['D_ClaimFeeDiff'],
                ':D_ClaimFeeTaxDiff' => $data['D_ClaimFeeTaxDiff'],
                ':D_MonthlyFeeDiff' => $data['D_MonthlyFeeDiff'],
                ':D_MonthlyFeeTaxDiff' => $data['D_MonthlyFeeTaxDiff'],
                ':D_OemIncludeMonthlyFeeDiff' => $data['D_OemIncludeMonthlyFeeDiff'],
                ':D_OemIncludeMonthlyFeeTaxDiff' => $data['D_OemIncludeMonthlyFeeTaxDiff'],
                ':D_OemApiMonthlyFeeDiff' => $data['D_OemApiMonthlyFeeDiff'],
                ':D_OemApiMonthlyFeeTaxDiff' => $data['D_OemApiMonthlyFeeTaxDiff'],
                ':D_OemCreditNoticeMonthlyFeeDiff' => $data['D_OemCreditNoticeMonthlyFeeDiff'],
                ':D_OemCreditNoticeMonthlyFeeTaxDiff' => $data['D_OemCreditNoticeMonthlyFeeTaxDiff'],
                ':D_OemNCreditNoticeMonthlyFeeDiff' => $data['D_OemNCreditNoticeMonthlyFeeDiff'],
                ':D_OemNCreditNoticeMonthlyFeeTaxDiff' => $data['D_OemNCreditNoticeMonthlyFeeTaxDiff'],
                ':D_OemReserveMonthlyFeeDiff' => $data['D_OemReserveMonthlyFeeDiff'],
                ':D_OemReserveMonthlyFeeTaxDiff' => $data['D_OemReserveMonthlyFeeTaxDiff'],
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
                ':M_OemTransferCount' => $data['M_OemTransferCount'],
                ':M_OemTransferAmount' => $data['M_OemTransferAmount'],
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
                ':M_SettlementFeeTax' => $data['M_SettlementFeeTax'],
                ':M_ClaimFee' => $data['M_ClaimFee'],
                ':M_ClaimFeeTax' => $data['M_ClaimFeeTax'],
                ':M_MonthlyFee' => $data['M_MonthlyFee'],
                ':M_MonthlyFeeTax' => $data['M_MonthlyFeeTax'],
                ':M_OemIncludeMonthlyFee' => $data['M_OemIncludeMonthlyFee'],
                ':M_OemIncludeMonthlyFeeTax' => $data['M_OemIncludeMonthlyFeeTax'],
                ':M_OemApiMonthlyFee' => $data['M_OemApiMonthlyFee'],
                ':M_OemApiMonthlyFeeTax' => $data['M_OemApiMonthlyFeeTax'],
                ':M_OemCreditNoticeMonthlyFee' => $data['M_OemCreditNoticeMonthlyFee'],
                ':M_OemCreditNoticeMonthlyFeeTax' => $data['M_OemCreditNoticeMonthlyFeeTax'],
                ':M_OemNCreditNoticeMonthlyFee' => $data['M_OemNCreditNoticeMonthlyFee'],
                ':M_OemNCreditNoticeMonthlyFeeTax' => $data['M_OemNCreditNoticeMonthlyFeeTax'],
                ':M_OemReserveMonthlyFee' => $data['M_OemReserveMonthlyFee'],
                ':M_OemReserveMonthlyFeeTax' => $data['M_OemReserveMonthlyFeeTax'],
                ':M_AddClaimFee' => $data['M_AddClaimFee'],
                ':M_AddClaimFeeTax' => $data['M_AddClaimFeeTax'],
                ':M_DamageInterestAmount' => $data['M_DamageInterestAmount'],
                ':M_CanSettlementFee' => $data['M_CanSettlementFee'],
                ':M_CanSettlementFeeTax' => $data['M_CanSettlementFeeTax'],
                ':M_CanClaimFee' => $data['M_CanClaimFee'],
                ':M_CanClaimFeeTax' => $data['M_CanClaimFeeTax'],
                ':M_SettlementFeeTotal' => $data['M_SettlementFeeTotal'],
                ':M_SettlementFeeTaxTotal' => $data['M_SettlementFeeTaxTotal'],
                ':M_ClaimFeeTotal' => $data['M_ClaimFeeTotal'],
                ':M_ClaimFeeTaxTotal' => $data['M_ClaimFeeTaxTotal'],
                ':M_MonthlyFeeTotal' => $data['M_MonthlyFeeTotal'],
                ':M_MonthlyFeeTaxTotal' => $data['M_MonthlyFeeTaxTotal'],
                ':M_OemIncludeMonthlyFeeTotal' => $data['M_OemIncludeMonthlyFeeTotal'],
                ':M_OemIncludeMonthlyFeeTaxTotal' => $data['M_OemIncludeMonthlyFeeTaxTotal'],
                ':M_OemApiMonthlyFeeTotal' => $data['M_OemApiMonthlyFeeTotal'],
                ':M_OemApiMonthlyFeeTaxTotal' => $data['M_OemApiMonthlyFeeTaxTotal'],
                ':M_OemCreditNoticeMonthlyFeeTotal' => $data['M_OemCreditNoticeMonthlyFeeTotal'],
                ':M_OemCreditNoticeMonthlyFeeTaxTotal' => $data['M_OemCreditNoticeMonthlyFeeTaxTotal'],
                ':M_OemNCreditNoticeMonthlyFeeTotal' => $data['M_OemNCreditNoticeMonthlyFeeTotal'],
                ':M_OemNCreditNoticeMonthlyFeeTaxTotal' => $data['M_OemNCreditNoticeMonthlyFeeTaxTotal'],
                ':M_OemReserveMonthlyFeeTotal' => $data['M_OemReserveMonthlyFeeTotal'],
                ':M_OemReserveMonthlyFeeTaxTotal' => $data['M_OemReserveMonthlyFeeTaxTotal'],
                ':M_AddClaimFeeTotal' => $data['M_AddClaimFeeTotal'],
                ':M_AddClaimFeeTaxTotal' => $data['M_AddClaimFeeTaxTotal'],
                ':M_DamageInterestAmountTotal' => $data['M_DamageInterestAmountTotal'],
                ':M_AllTotal' => $data['M_AllTotal'],
                ':M_SettlementFeeOther' => $data['M_SettlementFeeOther'],
                ':M_SettlementFeeTaxOther' => $data['M_SettlementFeeTaxOther'],
                ':M_ClaimFeeOther' => $data['M_ClaimFeeOther'],
                ':M_ClaimFeeTaxOther' => $data['M_ClaimFeeTaxOther'],
                ':M_MonthlyFeeOther' => $data['M_MonthlyFeeOther'],
                ':M_MonthlyFeeTaxOther' => $data['M_MonthlyFeeTaxOther'],
                ':M_OemIncludeMonthlyFeeOther' => $data['M_OemIncludeMonthlyFeeOther'],
                ':M_OemIncludeMonthlyFeeTaxOther' => $data['M_OemIncludeMonthlyFeeTaxOther'],
                ':M_OemApiMonthlyFeeOther' => $data['M_OemApiMonthlyFeeOther'],
                ':M_OemApiMonthlyFeeTaxOther' => $data['M_OemApiMonthlyFeeTaxOther'],
                ':M_OemCreditNoticeMonthlyFeeOther' => $data['M_OemCreditNoticeMonthlyFeeOther'],
                ':M_OemCreditNoticeMonthlyFeeTaxOther' => $data['M_OemCreditNoticeMonthlyFeeTaxOther'],
                ':M_OemNCreditNoticeMonthlyFeeOther' => $data['M_OemNCreditNoticeMonthlyFeeOther'],
                ':M_OemNCreditNoticeMonthlyFeeTaxOther' => $data['M_OemNCreditNoticeMonthlyFeeTaxOther'],
                ':M_OemReserveMonthlyFeeOther' => $data['M_OemReserveMonthlyFeeOther'],
                ':M_OemReserveMonthlyFeeTaxOther' => $data['M_OemReserveMonthlyFeeTaxOther'],
                ':M_AddClaimFeeOther' => $data['M_AddClaimFeeOther'],
                ':M_AddClaimFeeTaxOther' => $data['M_AddClaimFeeTaxOther'],
                ':M_DamageInterestAmountOther' => $data['M_DamageInterestAmountOther'],
                ':M_SettlementFeeDiff' => $data['M_SettlementFeeDiff'],
                ':M_SettlementFeeTaxDiff' => $data['M_SettlementFeeTaxDiff'],
                ':M_ClaimFeeDiff' => $data['M_ClaimFeeDiff'],
                ':M_ClaimFeeTaxDiff' => $data['M_ClaimFeeTaxDiff'],
                ':M_MonthlyFeeDiff' => $data['M_MonthlyFeeDiff'],
                ':M_MonthlyFeeTaxDiff' => $data['M_MonthlyFeeTaxDiff'],
                ':M_OemIncludeMonthlyFeeDiff' => $data['M_OemIncludeMonthlyFeeDiff'],
                ':M_OemIncludeMonthlyFeeTaxDiff' => $data['M_OemIncludeMonthlyFeeTaxDiff'],
                ':M_OemApiMonthlyFeeDiff' => $data['M_OemApiMonthlyFeeDiff'],
                ':M_OemApiMonthlyFeeTaxDiff' => $data['M_OemApiMonthlyFeeTaxDiff'],
                ':M_OemCreditNoticeMonthlyFeeDiff' => $data['M_OemCreditNoticeMonthlyFeeDiff'],
                ':M_OemCreditNoticeMonthlyFeeTaxDiff' => $data['M_OemCreditNoticeMonthlyFeeTaxDiff'],
                ':M_OemNCreditNoticeMonthlyFeeDiff' => $data['M_OemNCreditNoticeMonthlyFeeDiff'],
                ':M_OemNCreditNoticeMonthlyFeeTaxDiff' => $data['M_OemNCreditNoticeMonthlyFeeTaxDiff'],
                ':M_OemReserveMonthlyFeeDiff' => $data['M_OemReserveMonthlyFeeDiff'],
                ':M_OemReserveMonthlyFeeTaxDiff' => $data['M_OemReserveMonthlyFeeTaxDiff'],
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

        $sql  = " UPDATE AT_Oem_DailyStatisticsTable ";
        $sql .= " SET ";
        $sql .= "     DailyMonthlyFlg = :DailyMonthlyFlg ";
        $sql .= " ,   ProcessingDate = :ProcessingDate ";
        $sql .= " ,   AccountDate = :AccountDate ";
        $sql .= " ,   OemId = :OemId ";
        $sql .= " ,   OemNameKj = :OemNameKj ";
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
        $sql .= " ,   D_OemIncludeMonthlyFee = :D_OemIncludeMonthlyFee ";
        $sql .= " ,   D_OemIncludeMonthlyFeeTax = :D_OemIncludeMonthlyFeeTax ";
        $sql .= " ,   D_OemApiMonthlyFee = :D_OemApiMonthlyFee ";
        $sql .= " ,   D_OemApiMonthlyFeeTax = :D_OemApiMonthlyFeeTax ";
        $sql .= " ,   D_OemCreditNoticeMonthlyFee = :D_OemCreditNoticeMonthlyFee ";
        $sql .= " ,   D_OemCreditNoticeMonthlyFeeTax = :D_OemCreditNoticeMonthlyFeeTax ";
        $sql .= " ,   D_OemNCreditNoticeMonthlyFee = :D_OemNCreditNoticeMonthlyFee ";
        $sql .= " ,   D_OemNCreditNoticeMonthlyFeeTax = :D_OemNCreditNoticeMonthlyFeeTax ";
        $sql .= " ,   D_OemReserveMonthlyFee = :D_OemReserveMonthlyFee ";
        $sql .= " ,   D_OemReserveMonthlyFeeTax = :D_OemReserveMonthlyFeeTax ";
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
        $sql .= " ,   D_OemIncludeMonthlyFeeTotal = :D_OemIncludeMonthlyFeeTotal ";
        $sql .= " ,   D_OemIncludeMonthlyFeeTaxTotal = :D_OemIncludeMonthlyFeeTaxTotal ";
        $sql .= " ,   D_OemApiMonthlyFeeTotal = :D_OemApiMonthlyFeeTotal ";
        $sql .= " ,   D_OemApiMonthlyFeeTaxTotal = :D_OemApiMonthlyFeeTaxTotal ";
        $sql .= " ,   D_OemCreditNoticeMonthlyFeeTotal = :D_OemCreditNoticeMonthlyFeeTotal ";
        $sql .= " ,   D_OemCreditNoticeMonthlyFeeTaxTotal = :D_OemCreditNoticeMonthlyFeeTaxTotal ";
        $sql .= " ,   D_OemNCreditNoticeMonthlyFeeTotal = :D_OemNCreditNoticeMonthlyFeeTotal ";
        $sql .= " ,   D_OemNCreditNoticeMonthlyFeeTaxTotal = :D_OemNCreditNoticeMonthlyFeeTaxTotal ";
        $sql .= " ,   D_OemReserveMonthlyFeeTotal = :D_OemReserveMonthlyFeeTotal ";
        $sql .= " ,   D_OemReserveMonthlyFeeTaxTotal = :D_OemReserveMonthlyFeeTaxTotal ";
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
        $sql .= " ,   D_OemIncludeMonthlyFeeOther = :D_OemIncludeMonthlyFeeOther ";
        $sql .= " ,   D_OemIncludeMonthlyFeeTaxOther = :D_OemIncludeMonthlyFeeTaxOther ";
        $sql .= " ,   D_OemApiMonthlyFeeOther = :D_OemApiMonthlyFeeOther ";
        $sql .= " ,   D_OemApiMonthlyFeeTaxOther = :D_OemApiMonthlyFeeTaxOther ";
        $sql .= " ,   D_OemCreditNoticeMonthlyFeeOther = :D_OemCreditNoticeMonthlyFeeOther ";
        $sql .= " ,   D_OemCreditNoticeMonthlyFeeTaxOther = :D_OemCreditNoticeMonthlyFeeTaxOther ";
        $sql .= " ,   D_OemNCreditNoticeMonthlyFeeOther = :D_OemNCreditNoticeMonthlyFeeOther ";
        $sql .= " ,   D_OemNCreditNoticeMonthlyFeeTaxOther = :D_OemNCreditNoticeMonthlyFeeTaxOther ";
        $sql .= " ,   D_OemReserveMonthlyFeeOther = :D_OemReserveMonthlyFeeOther ";
        $sql .= " ,   D_OemReserveMonthlyFeeTaxOther = :D_OemReserveMonthlyFeeTaxOther ";
        $sql .= " ,   D_AddClaimFeeOther = :D_AddClaimFeeOther ";
        $sql .= " ,   D_AddClaimFeeTaxOther = :D_AddClaimFeeTaxOther ";
        $sql .= " ,   D_DamageInterestAmountOther = :D_DamageInterestAmountOther ";
        $sql .= " ,   D_SettlementFeeDiff = :D_SettlementFeeDiff ";
        $sql .= " ,   D_SettlementFeeTaxDiff = :D_SettlementFeeTaxDiff ";
        $sql .= " ,   D_ClaimFeeDiff = :D_ClaimFeeDiff ";
        $sql .= " ,   D_ClaimFeeTaxDiff = :D_ClaimFeeTaxDiff ";
        $sql .= " ,   D_MonthlyFeeDiff = :D_MonthlyFeeDiff ";
        $sql .= " ,   D_MonthlyFeeTaxDiff = :D_MonthlyFeeTaxDiff ";
        $sql .= " ,   D_OemIncludeMonthlyFeeDiff = :D_OemIncludeMonthlyFeeDiff ";
        $sql .= " ,   D_OemIncludeMonthlyFeeTaxDiff = :D_OemIncludeMonthlyFeeTaxDiff ";
        $sql .= " ,   D_OemApiMonthlyFeeDiff = :D_OemApiMonthlyFeeDiff ";
        $sql .= " ,   D_OemApiMonthlyFeeTaxDiff = :D_OemApiMonthlyFeeTaxDiff ";
        $sql .= " ,   D_OemCreditNoticeMonthlyFeeDiff = :D_OemCreditNoticeMonthlyFeeDiff ";
        $sql .= " ,   D_OemCreditNoticeMonthlyFeeTaxDiff = :D_OemCreditNoticeMonthlyFeeTaxDiff ";
        $sql .= " ,   D_OemNCreditNoticeMonthlyFeeDiff = :D_OemNCreditNoticeMonthlyFeeDiff ";
        $sql .= " ,   D_OemNCreditNoticeMonthlyFeeTaxDiff = :D_OemNCreditNoticeMonthlyFeeTaxDiff ";
        $sql .= " ,   D_OemReserveMonthlyFeeDiff = :D_OemReserveMonthlyFeeDiff ";
        $sql .= " ,   D_OemReserveMonthlyFeeTaxDiff = :D_OemReserveMonthlyFeeTaxDiff ";
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
        $sql .= " ,   M_OemTransferCount = :M_OemTransferCount ";
        $sql .= " ,   M_OemTransferAmount = :M_OemTransferAmount ";
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
        $sql .= " ,   M_SettlementFeeTax = :M_SettlementFeeTax ";
        $sql .= " ,   M_ClaimFee = :M_ClaimFee ";
        $sql .= " ,   M_ClaimFeeTax = :M_ClaimFeeTax ";
        $sql .= " ,   M_MonthlyFee = :M_MonthlyFee ";
        $sql .= " ,   M_MonthlyFeeTax = :M_MonthlyFeeTax ";
        $sql .= " ,   M_OemIncludeMonthlyFee = :M_OemIncludeMonthlyFee ";
        $sql .= " ,   M_OemIncludeMonthlyFeeTax = :M_OemIncludeMonthlyFeeTax ";
        $sql .= " ,   M_OemApiMonthlyFee = :M_OemApiMonthlyFee ";
        $sql .= " ,   M_OemApiMonthlyFeeTax = :M_OemApiMonthlyFeeTax ";
        $sql .= " ,   M_OemCreditNoticeMonthlyFee = :M_OemCreditNoticeMonthlyFee ";
        $sql .= " ,   M_OemCreditNoticeMonthlyFeeTax = :M_OemCreditNoticeMonthlyFeeTax ";
        $sql .= " ,   M_OemNCreditNoticeMonthlyFee = :M_OemNCreditNoticeMonthlyFee ";
        $sql .= " ,   M_OemNCreditNoticeMonthlyFeeTax = :M_OemNCreditNoticeMonthlyFeeTax ";
        $sql .= " ,   M_OemReserveMonthlyFee = :M_OemReserveMonthlyFee ";
        $sql .= " ,   M_OemReserveMonthlyFeeTax = :M_OemReserveMonthlyFeeTax ";
        $sql .= " ,   M_AddClaimFee = :M_AddClaimFee ";
        $sql .= " ,   M_AddClaimFeeTax = :M_AddClaimFeeTax ";
        $sql .= " ,   M_DamageInterestAmount = :M_DamageInterestAmount ";
        $sql .= " ,   M_CanSettlementFee = :M_CanSettlementFee ";
        $sql .= " ,   M_CanSettlementFeeTax = :M_CanSettlementFeeTax ";
        $sql .= " ,   M_CanClaimFee = :M_CanClaimFee ";
        $sql .= " ,   M_CanClaimFeeTax = :M_CanClaimFeeTax ";
        $sql .= " ,   M_SettlementFeeTotal = :M_SettlementFeeTotal ";
        $sql .= " ,   M_SettlementFeeTaxTotal = :M_SettlementFeeTaxTotal ";
        $sql .= " ,   M_ClaimFeeTotal = :M_ClaimFeeTotal ";
        $sql .= " ,   M_ClaimFeeTaxTotal = :M_ClaimFeeTaxTotal ";
        $sql .= " ,   M_MonthlyFeeTotal = :M_MonthlyFeeTotal ";
        $sql .= " ,   M_MonthlyFeeTaxTotal = :M_MonthlyFeeTaxTotal ";
        $sql .= " ,   M_OemIncludeMonthlyFeeTotal = :M_OemIncludeMonthlyFeeTotal ";
        $sql .= " ,   M_OemIncludeMonthlyFeeTaxTotal = :M_OemIncludeMonthlyFeeTaxTotal ";
        $sql .= " ,   M_OemApiMonthlyFeeTotal = :M_OemApiMonthlyFeeTotal ";
        $sql .= " ,   M_OemApiMonthlyFeeTaxTotal = :M_OemApiMonthlyFeeTaxTotal ";
        $sql .= " ,   M_OemCreditNoticeMonthlyFeeTotal = :M_OemCreditNoticeMonthlyFeeTotal ";
        $sql .= " ,   M_OemCreditNoticeMonthlyFeeTaxTotal = :M_OemCreditNoticeMonthlyFeeTaxTotal ";
        $sql .= " ,   M_OemNCreditNoticeMonthlyFeeTotal = :M_OemNCreditNoticeMonthlyFeeTotal ";
        $sql .= " ,   M_OemNCreditNoticeMonthlyFeeTaxTotal = :M_OemNCreditNoticeMonthlyFeeTaxTotal ";
        $sql .= " ,   M_OemReserveMonthlyFeeTotal = :M_OemReserveMonthlyFeeTotal ";
        $sql .= " ,   M_OemReserveMonthlyFeeTaxTotal = :M_OemReserveMonthlyFeeTaxTotal ";
        $sql .= " ,   M_AddClaimFeeTotal = :M_AddClaimFeeTotal ";
        $sql .= " ,   M_AddClaimFeeTaxTotal = :M_AddClaimFeeTaxTotal ";
        $sql .= " ,   M_DamageInterestAmountTotal = :M_DamageInterestAmountTotal ";
        $sql .= " ,   M_AllTotal = :M_AllTotal ";
        $sql .= " ,   M_SettlementFeeOther = :M_SettlementFeeOther ";
        $sql .= " ,   M_SettlementFeeTaxOther = :M_SettlementFeeTaxOther ";
        $sql .= " ,   M_ClaimFeeOther = :M_ClaimFeeOther ";
        $sql .= " ,   M_ClaimFeeTaxOther = :M_ClaimFeeTaxOther ";
        $sql .= " ,   M_MonthlyFeeOther = :M_MonthlyFeeOther ";
        $sql .= " ,   M_MonthlyFeeTaxOther = :M_MonthlyFeeTaxOther ";
        $sql .= " ,   M_OemIncludeMonthlyFeeOther = :M_OemIncludeMonthlyFeeOther ";
        $sql .= " ,   M_OemIncludeMonthlyFeeTaxOther = :M_OemIncludeMonthlyFeeTaxOther ";
        $sql .= " ,   M_OemApiMonthlyFeeOther = :M_OemApiMonthlyFeeOther ";
        $sql .= " ,   M_OemApiMonthlyFeeTaxOther = :M_OemApiMonthlyFeeTaxOther ";
        $sql .= " ,   M_OemCreditNoticeMonthlyFeeOther = :M_OemCreditNoticeMonthlyFeeOther ";
        $sql .= " ,   M_OemCreditNoticeMonthlyFeeTaxOther = :M_OemCreditNoticeMonthlyFeeTaxOther ";
        $sql .= " ,   M_OemNCreditNoticeMonthlyFeeOther = :M_OemNCreditNoticeMonthlyFeeOther ";
        $sql .= " ,   M_OemNCreditNoticeMonthlyFeeTaxOther = :M_OemNCreditNoticeMonthlyFeeTaxOther ";
        $sql .= " ,   M_OemReserveMonthlyFeeOther = :M_OemReserveMonthlyFeeOther ";
        $sql .= " ,   M_OemReserveMonthlyFeeTaxOther = :M_OemReserveMonthlyFeeTaxOther ";
        $sql .= " ,   M_AddClaimFeeOther = :M_AddClaimFeeOther ";
        $sql .= " ,   M_AddClaimFeeTaxOther = :M_AddClaimFeeTaxOther ";
        $sql .= " ,   M_DamageInterestAmountOther = :M_DamageInterestAmountOther ";
        $sql .= " ,   M_SettlementFeeDiff = :M_SettlementFeeDiff ";
        $sql .= " ,   M_SettlementFeeTaxDiff = :M_SettlementFeeTaxDiff ";
        $sql .= " ,   M_ClaimFeeDiff = :M_ClaimFeeDiff ";
        $sql .= " ,   M_ClaimFeeTaxDiff = :M_ClaimFeeTaxDiff ";
        $sql .= " ,   M_MonthlyFeeDiff = :M_MonthlyFeeDiff ";
        $sql .= " ,   M_MonthlyFeeTaxDiff = :M_MonthlyFeeTaxDiff ";
        $sql .= " ,   M_OemIncludeMonthlyFeeDiff = :M_OemIncludeMonthlyFeeDiff ";
        $sql .= " ,   M_OemIncludeMonthlyFeeTaxDiff = :M_OemIncludeMonthlyFeeTaxDiff ";
        $sql .= " ,   M_OemApiMonthlyFeeDiff = :M_OemApiMonthlyFeeDiff ";
        $sql .= " ,   M_OemApiMonthlyFeeTaxDiff = :M_OemApiMonthlyFeeTaxDiff ";
        $sql .= " ,   M_OemCreditNoticeMonthlyFeeDiff = :M_OemCreditNoticeMonthlyFeeDiff ";
        $sql .= " ,   M_OemCreditNoticeMonthlyFeeTaxDiff = :M_OemCreditNoticeMonthlyFeeTaxDiff ";
        $sql .= " ,   M_OemNCreditNoticeMonthlyFeeDiff = :M_OemNCreditNoticeMonthlyFeeDiff ";
        $sql .= " ,   M_OemNCreditNoticeMonthlyFeeTaxDiff = :M_OemNCreditNoticeMonthlyFeeTaxDiff ";
        $sql .= " ,   M_OemReserveMonthlyFeeDiff = :M_OemReserveMonthlyFeeDiff ";
        $sql .= " ,   M_OemReserveMonthlyFeeTaxDiff = :M_OemReserveMonthlyFeeTaxDiff ";
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
                ':OemId' => $row['OemId'],
                ':OemNameKj' => $row['OemNameKj'],
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
                ':D_OemIncludeMonthlyFee' => $row['D_OemIncludeMonthlyFee'],
                ':D_OemIncludeMonthlyFeeTax' => $row['D_OemIncludeMonthlyFeeTax'],
                ':D_OemApiMonthlyFee' => $row['D_OemApiMonthlyFee'],
                ':D_OemApiMonthlyFeeTax' => $row['D_OemApiMonthlyFeeTax'],
                ':D_OemCreditNoticeMonthlyFee' => $row['D_OemCreditNoticeMonthlyFee'],
                ':D_OemCreditNoticeMonthlyFeeTax' => $row['D_OemCreditNoticeMonthlyFeeTax'],
                ':D_OemNCreditNoticeMonthlyFee' => $row['D_OemNCreditNoticeMonthlyFee'],
                ':D_OemNCreditNoticeMonthlyFeeTax' => $row['D_OemNCreditNoticeMonthlyFeeTax'],
                ':D_OemReserveMonthlyFee' => $row['D_OemReserveMonthlyFee'],
                ':D_OemReserveMonthlyFeeTax' => $row['D_OemReserveMonthlyFeeTax'],
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
                ':D_OemIncludeMonthlyFeeTotal' => $row['D_OemIncludeMonthlyFeeTotal'],
                ':D_OemIncludeMonthlyFeeTaxTotal' => $row['D_OemIncludeMonthlyFeeTaxTotal'],
                ':D_OemApiMonthlyFeeTotal' => $row['D_OemApiMonthlyFeeTotal'],
                ':D_OemApiMonthlyFeeTaxTotal' => $row['D_OemApiMonthlyFeeTaxTotal'],
                ':D_OemCreditNoticeMonthlyFeeTotal' => $row['D_OemCreditNoticeMonthlyFeeTotal'],
                ':D_OemCreditNoticeMonthlyFeeTaxTotal' => $row['D_OemCreditNoticeMonthlyFeeTaxTotal'],
                ':D_OemNCreditNoticeMonthlyFeeTotal' => $row['D_OemNCreditNoticeMonthlyFeeTotal'],
                ':D_OemNCreditNoticeMonthlyFeeTaxTotal' => $row['D_OemNCreditNoticeMonthlyFeeTaxTotal'],
                ':D_OemReserveMonthlyFeeTotal' => $row['D_OemReserveMonthlyFeeTotal'],
                ':D_OemReserveMonthlyFeeTaxTotal' => $row['D_OemReserveMonthlyFeeTaxTotal'],
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
                ':D_OemIncludeMonthlyFeeOther' => $row['D_OemIncludeMonthlyFeeOther'],
                ':D_OemIncludeMonthlyFeeTaxOther' => $row['D_OemIncludeMonthlyFeeTaxOther'],
                ':D_OemApiMonthlyFeeOther' => $row['D_OemApiMonthlyFeeOther'],
                ':D_OemApiMonthlyFeeTaxOther' => $row['D_OemApiMonthlyFeeTaxOther'],
                ':D_OemCreditNoticeMonthlyFeeOther' => $row['D_OemCreditNoticeMonthlyFeeOther'],
                ':D_OemCreditNoticeMonthlyFeeTaxOther' => $row['D_OemCreditNoticeMonthlyFeeTaxOther'],
                ':D_OemNCreditNoticeMonthlyFeeOther' => $row['D_OemNCreditNoticeMonthlyFeeOther'],
                ':D_OemNCreditNoticeMonthlyFeeTaxOther' => $row['D_OemNCreditNoticeMonthlyFeeTaxOther'],
                ':D_OemReserveMonthlyFeeOther' => $row['D_OemReserveMonthlyFeeOther'],
                ':D_OemReserveMonthlyFeeTaxOther' => $row['D_OemReserveMonthlyFeeTaxOther'],
                ':D_AddClaimFeeOther' => $row['D_AddClaimFeeOther'],
                ':D_AddClaimFeeTaxOther' => $row['D_AddClaimFeeTaxOther'],
                ':D_DamageInterestAmountOther' => $row['D_DamageInterestAmountOther'],
                ':D_SettlementFeeDiff' => $row['D_SettlementFeeDiff'],
                ':D_SettlementFeeTaxDiff' => $row['D_SettlementFeeTaxDiff'],
                ':D_ClaimFeeDiff' => $row['D_ClaimFeeDiff'],
                ':D_ClaimFeeTaxDiff' => $row['D_ClaimFeeTaxDiff'],
                ':D_MonthlyFeeDiff' => $row['D_MonthlyFeeDiff'],
                ':D_MonthlyFeeTaxDiff' => $row['D_MonthlyFeeTaxDiff'],
                ':D_OemIncludeMonthlyFeeDiff' => $row['D_OemIncludeMonthlyFeeDiff'],
                ':D_OemIncludeMonthlyFeeTaxDiff' => $row['D_OemIncludeMonthlyFeeTaxDiff'],
                ':D_OemApiMonthlyFeeDiff' => $row['D_OemApiMonthlyFeeDiff'],
                ':D_OemApiMonthlyFeeTaxDiff' => $row['D_OemApiMonthlyFeeTaxDiff'],
                ':D_OemCreditNoticeMonthlyFeeDiff' => $row['D_OemCreditNoticeMonthlyFeeDiff'],
                ':D_OemCreditNoticeMonthlyFeeTaxDiff' => $row['D_OemCreditNoticeMonthlyFeeTaxDiff'],
                ':D_OemNCreditNoticeMonthlyFeeDiff' => $row['D_OemNCreditNoticeMonthlyFeeDiff'],
                ':D_OemNCreditNoticeMonthlyFeeTaxDiff' => $row['D_OemNCreditNoticeMonthlyFeeTaxDiff'],
                ':D_OemReserveMonthlyFeeDiff' => $row['D_OemReserveMonthlyFeeDiff'],
                ':D_OemReserveMonthlyFeeTaxDiff' => $row['D_OemReserveMonthlyFeeTaxDiff'],
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
                ':M_OemTransferCount' => $row['M_OemTransferCount'],
                ':M_OemTransferAmount' => $row['M_OemTransferAmount'],
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
                ':M_SettlementFeeTax' => $row['M_SettlementFeeTax'],
                ':M_ClaimFee' => $row['M_ClaimFee'],
                ':M_ClaimFeeTax' => $row['M_ClaimFeeTax'],
                ':M_MonthlyFee' => $row['M_MonthlyFee'],
                ':M_MonthlyFeeTax' => $row['M_MonthlyFeeTax'],
                ':M_OemIncludeMonthlyFee' => $row['M_OemIncludeMonthlyFee'],
                ':M_OemIncludeMonthlyFeeTax' => $row['M_OemIncludeMonthlyFeeTax'],
                ':M_OemApiMonthlyFee' => $row['M_OemApiMonthlyFee'],
                ':M_OemApiMonthlyFeeTax' => $row['M_OemApiMonthlyFeeTax'],
                ':M_OemCreditNoticeMonthlyFee' => $row['M_OemCreditNoticeMonthlyFee'],
                ':M_OemCreditNoticeMonthlyFeeTax' => $row['M_OemCreditNoticeMonthlyFeeTax'],
                ':M_OemNCreditNoticeMonthlyFee' => $row['M_OemNCreditNoticeMonthlyFee'],
                ':M_OemNCreditNoticeMonthlyFeeTax' => $row['M_OemNCreditNoticeMonthlyFeeTax'],
                ':M_OemReserveMonthlyFee' => $row['M_OemReserveMonthlyFee'],
                ':M_OemReserveMonthlyFeeTax' => $row['M_OemReserveMonthlyFeeTax'],
                ':M_AddClaimFee' => $row['M_AddClaimFee'],
                ':M_AddClaimFeeTax' => $row['M_AddClaimFeeTax'],
                ':M_DamageInterestAmount' => $row['M_DamageInterestAmount'],
                ':M_CanSettlementFee' => $row['M_CanSettlementFee'],
                ':M_CanSettlementFeeTax' => $row['M_CanSettlementFeeTax'],
                ':M_CanClaimFee' => $row['M_CanClaimFee'],
                ':M_CanClaimFeeTax' => $row['M_CanClaimFeeTax'],
                ':M_SettlementFeeTotal' => $row['M_SettlementFeeTotal'],
                ':M_SettlementFeeTaxTotal' => $row['M_SettlementFeeTaxTotal'],
                ':M_ClaimFeeTotal' => $row['M_ClaimFeeTotal'],
                ':M_ClaimFeeTaxTotal' => $row['M_ClaimFeeTaxTotal'],
                ':M_MonthlyFeeTotal' => $row['M_MonthlyFeeTotal'],
                ':M_MonthlyFeeTaxTotal' => $row['M_MonthlyFeeTaxTotal'],
                ':M_OemIncludeMonthlyFeeTotal' => $row['M_OemIncludeMonthlyFeeTotal'],
                ':M_OemIncludeMonthlyFeeTaxTotal' => $row['M_OemIncludeMonthlyFeeTaxTotal'],
                ':M_OemApiMonthlyFeeTotal' => $row['M_OemApiMonthlyFeeTotal'],
                ':M_OemApiMonthlyFeeTaxTotal' => $row['M_OemApiMonthlyFeeTaxTotal'],
                ':M_OemCreditNoticeMonthlyFeeTotal' => $row['M_OemCreditNoticeMonthlyFeeTotal'],
                ':M_OemCreditNoticeMonthlyFeeTaxTotal' => $row['M_OemCreditNoticeMonthlyFeeTaxTotal'],
                ':M_OemNCreditNoticeMonthlyFeeTotal' => $row['M_OemNCreditNoticeMonthlyFeeTotal'],
                ':M_OemNCreditNoticeMonthlyFeeTaxTotal' => $row['M_OemNCreditNoticeMonthlyFeeTaxTotal'],
                ':M_OemReserveMonthlyFeeTotal' => $row['M_OemReserveMonthlyFeeTotal'],
                ':M_OemReserveMonthlyFeeTaxTotal' => $row['M_OemReserveMonthlyFeeTaxTotal'],
                ':M_AddClaimFeeTotal' => $row['M_AddClaimFeeTotal'],
                ':M_AddClaimFeeTaxTotal' => $row['M_AddClaimFeeTaxTotal'],
                ':M_DamageInterestAmountTotal' => $row['M_DamageInterestAmountTotal'],
                ':M_AllTotal' => $row['M_AllTotal'],
                ':M_SettlementFeeOther' => $row['M_SettlementFeeOther'],
                ':M_SettlementFeeTaxOther' => $row['M_SettlementFeeTaxOther'],
                ':M_ClaimFeeOther' => $row['M_ClaimFeeOther'],
                ':M_ClaimFeeTaxOther' => $row['M_ClaimFeeTaxOther'],
                ':M_MonthlyFeeOther' => $row['M_MonthlyFeeOther'],
                ':M_MonthlyFeeTaxOther' => $row['M_MonthlyFeeTaxOther'],
                ':M_OemIncludeMonthlyFeeOther' => $row['M_OemIncludeMonthlyFeeOther'],
                ':M_OemIncludeMonthlyFeeTaxOther' => $row['M_OemIncludeMonthlyFeeTaxOther'],
                ':M_OemApiMonthlyFeeOther' => $row['M_OemApiMonthlyFeeOther'],
                ':M_OemApiMonthlyFeeTaxOther' => $row['M_OemApiMonthlyFeeTaxOther'],
                ':M_OemCreditNoticeMonthlyFeeOther' => $row['M_OemCreditNoticeMonthlyFeeOther'],
                ':M_OemCreditNoticeMonthlyFeeTaxOther' => $row['M_OemCreditNoticeMonthlyFeeTaxOther'],
                ':M_OemNCreditNoticeMonthlyFeeOther' => $row['M_OemNCreditNoticeMonthlyFeeOther'],
                ':M_OemNCreditNoticeMonthlyFeeTaxOther' => $row['M_OemNCreditNoticeMonthlyFeeTaxOther'],
                ':M_OemReserveMonthlyFeeOther' => $row['M_OemReserveMonthlyFeeOther'],
                ':M_OemReserveMonthlyFeeTaxOther' => $row['M_OemReserveMonthlyFeeTaxOther'],
                ':M_AddClaimFeeOther' => $row['M_AddClaimFeeOther'],
                ':M_AddClaimFeeTaxOther' => $row['M_AddClaimFeeTaxOther'],
                ':M_DamageInterestAmountOther' => $row['M_DamageInterestAmountOther'],
                ':M_SettlementFeeDiff' => $row['M_SettlementFeeDiff'],
                ':M_SettlementFeeTaxDiff' => $row['M_SettlementFeeTaxDiff'],
                ':M_ClaimFeeDiff' => $row['M_ClaimFeeDiff'],
                ':M_ClaimFeeTaxDiff' => $row['M_ClaimFeeTaxDiff'],
                ':M_MonthlyFeeDiff' => $row['M_MonthlyFeeDiff'],
                ':M_MonthlyFeeTaxDiff' => $row['M_MonthlyFeeTaxDiff'],
                ':M_OemIncludeMonthlyFeeDiff' => $row['M_OemIncludeMonthlyFeeDiff'],
                ':M_OemIncludeMonthlyFeeTaxDiff' => $row['M_OemIncludeMonthlyFeeTaxDiff'],
                ':M_OemApiMonthlyFeeDiff' => $row['M_OemApiMonthlyFeeDiff'],
                ':M_OemApiMonthlyFeeTaxDiff' => $row['M_OemApiMonthlyFeeTaxDiff'],
                ':M_OemCreditNoticeMonthlyFeeDiff' => $row['M_OemCreditNoticeMonthlyFeeDiff'],
                ':M_OemCreditNoticeMonthlyFeeTaxDiff' => $row['M_OemCreditNoticeMonthlyFeeTaxDiff'],
                ':M_OemNCreditNoticeMonthlyFeeDiff' => $row['M_OemNCreditNoticeMonthlyFeeDiff'],
                ':M_OemNCreditNoticeMonthlyFeeTaxDiff' => $row['M_OemNCreditNoticeMonthlyFeeTaxDiff'],
                ':M_OemReserveMonthlyFeeDiff' => $row['M_OemReserveMonthlyFeeDiff'],
                ':M_OemReserveMonthlyFeeTaxDiff' => $row['M_OemReserveMonthlyFeeTaxDiff'],
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
