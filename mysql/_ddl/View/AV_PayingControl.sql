DROP VIEW IF EXISTS AV_PayingControl;

CREATE VIEW AV_PayingControl
AS
SELECT 
     a.Seq
    ,a.EnterpriseId
    ,a.FixedDate
    ,a.DecisionDate
    ,a.ExecDate
    ,a.ExecFlg
    ,a.ExecCpId
    ,a.ChargeCount
    ,a.ChargeAmount
    ,a.CancelCount
    ,a.CalcelAmount
    ,a.StampFeeCount
    ,a.StampFeeTotal
    ,a.MonthlyFee
    ,a.DecisionPayment
    ,a.AddUpFlg
    ,a.AddUpFixedMonth
    ,a.SettlementFee
    ,a.ClaimFee
    ,a.CarryOver
    ,a.TransferCommission
    ,a.ExecScheduleDate
    ,a.AdjustmentAmount
    ,a.PayBackTC
    ,a.CarryOverTC
    ,a.OemId
    ,a.OemClaimedSeq
    ,a.OemClaimedAddUpFlg
    ,a.ChargeMonthlyFeeFlg
    ,a.PayBackCount
    ,a.PayBackAmount
    ,a.PayingControlStatus
    ,a.SpecialPayingFlg
    ,a.PayingDataDownloadFlg
    ,a.PayingDataFilePath
    ,a.ClaimPdfFilePath
    ,a.AdjustmentDecisionFlg
    ,a.AdjustmentDecisionDate
    ,a.AdjustmentCount
    ,a.RegistDate
    ,a.RegistId
    ,a.UpdateDate
    ,a.UpdateId
    ,a.ValidFlg
    ,b.MonthlyFeeWithoutTax
    ,b.MonthlyFeeTax
    ,b.IncludeMonthlyFee
    ,b.IncludeMonthlyFeeTax
    ,b.ApiMonthlyFee
    ,b.ApiMonthlyFeeTax
    ,b.CreditNoticeMonthlyFee
    ,b.CreditNoticeMonthlyFeeTax
    ,b.NCreditNoticeMonthlyFee
    ,b.NCreditNoticeMonthlyFeeTax
    ,b.ReserveMonthlyFee
    ,b.ReserveMonthlyFeeTax
    ,b.OemMonthlyFeeWithoutTax
    ,b.OemMonthlyFeeTax
    ,b.OemIncludeMonthlyFee
    ,b.OemIncludeMonthlyFeeTax
    ,b.OemApiMonthlyFee
    ,b.OemApiMonthlyFeeTax
    ,b.OemCreditNoticeMonthlyFee
    ,b.OemCreditNoticeMonthlyFeeTax
    ,b.OemNCreditNoticeMonthlyFee
    ,b.OemNCreditNoticeMonthlyFeeTax
    ,b.OemReserveMonthlyFee
    ,b.OemReserveMonthlyFeeTax
    ,b.DailySummaryFlg

FROM T_PayingControl a
    ,AT_PayingControl b
WHERE a.Seq = b.Seq
;
