DROP VIEW IF EXISTS AV_EnterpriseReceiptHistory;

CREATE VIEW AV_EnterpriseReceiptHistory
AS
SELECT 
     e.EntRcptSeq
    ,e.EnterpriseId
    ,e.ReceiptDate
    ,e.ReceiptAmount
    ,e.ReceiptClass
    ,e.Note
    ,e.ReceiptProcessDate
    ,e.RegistDate
    ,e.RegistId
    ,e.UpdateDate
    ,e.UpdateId
    ,e.ValidFlg
    ,ae.ReceiptAmountRece
    ,ae.ReceiptAmountDue
    ,ae.ReceiptAmountSource
    ,ae.DailySummaryFlg
FROM T_EnterpriseReceiptHistory e
    ,AT_EnterpriseReceiptHistory ae
WHERE e.EntRcptSeq = ae.EntRcptSeq
;
