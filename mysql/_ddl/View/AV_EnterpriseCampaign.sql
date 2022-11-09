DROP VIEW IF EXISTS AV_EnterpriseCampaign;

CREATE VIEW AV_EnterpriseCampaign
AS
SELECT 
     e.Seq
    ,e.EnterpriseId
    ,e.SiteId
    ,e.DateFrom
    ,e.DateTo
    ,e.MonthlyFee
    ,e.AppPlan
    ,e.OemMonthlyFee
    ,e.OemSettlementFeeRate
    ,e.OemClaimFee
    ,e.PayingCycleId
    ,e.LimitDatePattern
    ,e.LimitDay
    ,e.Salesman
    ,e.SettlementAmountLimit
    ,e.SettlementFeeRate
    ,e.ClaimFeeDK
    ,e.ClaimFeeBS
    ,e.ReClaimFee
    ,e.SystemFee
    ,e.RegistDate
    ,e.RegistId
    ,e.UpdateDate
    ,e.UpdateId
    ,e.ValidFlg
    ,ae.OemClaimFeeDK
    ,ae.IncludeMonthlyFee
    ,ae.ApiMonthlyFee
    ,ae.CreditNoticeMonthlyFee
    ,ae.NCreditNoticeMonthlyFee
    ,ae.ReserveMonthlyFee
    ,ae.OemIncludeMonthlyFee
    ,ae.OemApiMonthlyFee
    ,ae.OemCreditNoticeMonthlyFee
    ,ae.OemNCreditNoticeMonthlyFee
    ,ae.OemReserveMonthlyFee

FROM T_EnterpriseCampaign e
    ,AT_EnterpriseCampaign ae
WHERE e.Seq = ae.Seq;
