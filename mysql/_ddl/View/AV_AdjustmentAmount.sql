DROP VIEW IF EXISTS AV_AdjustmentAmount;

CREATE VIEW AV_AdjustmentAmount
AS
SELECT 
     a.PayingControlSeq
    ,a.SerialNumber
    ,a.OrderId
    ,a.OrderSeq
    ,a.ItemCode
    ,a.AdjustmentAmount
    ,a.RegistDate
    ,a.RegistId
    ,a.UpdateDate
    ,a.UpdateId
    ,a.ValidFlg
    ,aa.DailySummaryFlg
FROM T_AdjustmentAmount a
    ,AT_AdjustmentAmount aa
WHERE a.PayingControlSeq = aa.PayingControlSeq
AND   a.SerialNumber = aa.SerialNumber
;
