DROP VIEW IF EXISTS AV_PayingAndSales;

CREATE VIEW AV_PayingAndSales
AS
SELECT 
     a.Seq
    ,a.OrderSeq
    ,a.OccDate
    ,a.UseAmount
    ,a.AppSettlementFeeRate
    ,a.SettlementFee
    ,a.ClaimFee
    ,a.ChargeAmount
    ,a.ClearConditionForCharge
    ,a.ClearConditionDate
    ,a.ChargeDecisionFlg
    ,a.ChargeDecisionDate
    ,a.CancelFlg
    ,a.PayingControlSeq
    ,a.SpecialPayingDate
    ,a.PayingControlStatus
    ,a.AgencyFeeAddUpFlg
    ,a.RegistDate
    ,a.RegistId
    ,a.UpdateDate
    ,a.UpdateId
    ,a.ValidFlg
    ,b.DailySummaryFlg
FROM T_PayingAndSales a
    ,AT_PayingAndSales b
WHERE a.Seq = b.Seq
;
