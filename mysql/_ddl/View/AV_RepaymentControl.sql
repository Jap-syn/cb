DROP VIEW IF EXISTS AV_RepaymentControl;

CREATE VIEW AV_RepaymentControl
AS
SELECT 
     a.RepaySeq
    ,a.RepayStatus
    ,a.IndicationDate
    ,a.DecisionDate
    ,a.ProcessClass
    ,a.BankName
    ,a.FfCode
    ,a.BranchName
    ,a.FfBranchCode
    ,a.FfAccountClass
    ,a.AccountNumber
    ,a.AccountHolder
    ,a.TransferCommission
    ,a.TransferAmount
    ,a.RepayAmount
    ,a.RepayExpectedDate
    ,a.ClaimId
    ,a.CheckingUseAmount
    ,a.CheckingClaimFee
    ,a.CheckingDamageInterestAmount
    ,a.CheckingAdditionalClaimFee
    ,a.RegistDate
    ,a.RegistId
    ,a.UpdateDate
    ,a.UpdateId
    ,a.ValidFlg
    ,b.DailySummaryFlg
FROM T_RepaymentControl a
    ,AT_RepaymentControl b
WHERE a.RepaySeq = b.RepaySeq
;
