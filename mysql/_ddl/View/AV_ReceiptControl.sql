DROP VIEW IF EXISTS AV_ReceiptControl;

CREATE VIEW AV_ReceiptControl
AS
SELECT 
     a.ReceiptSeq
    ,a.ReceiptProcessDate
    ,a.ReceiptDate
    ,a.ReceiptClass
    ,a.ReceiptAmount
    ,a.ClaimId
    ,a.OrderSeq
    ,a.CheckingUseAmount
    ,a.CheckingClaimFee
    ,a.CheckingDamageInterestAmount
    ,a.CheckingAdditionalClaimFee
    ,a.DailySummaryFlg
    ,a.BranchBankId
    ,a.DepositDate
    ,a.ReceiptAgentId
    ,a.RegistDate
    ,a.RegistId
    ,a.UpdateDate
    ,a.UpdateId
    ,a.ValidFlg
    ,b.AccountNumber
    ,b.ClassDetails
    ,b.BankFlg
    ,b.Rct_CancelFlg
    ,b.Before_ClearConditionForCharge
    ,b.Before_ClearConditionDate
    ,b.Before_Cnl_Status
    ,b.Before_Deli_ConfirmArrivalFlg

FROM T_ReceiptControl a
    ,AT_ReceiptControl b
WHERE a.ReceiptSeq = b.ReceiptSeq
;
