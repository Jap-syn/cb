DROP VIEW IF EXISTS `V_OrderSearch`;

CREATE VIEW V_OrderSearch AS
    SELECT
        O.OrderSeq AS OrderSeq,
        O.ReceiptOrderDate AS ReceiptOrderDate,
        O.DataStatus AS DataStatus,
        O.EnterpriseId AS EnterpriseId,
        O.SiteId AS SiteId,
        O.OrderId AS OrderId,
        O.Ent_OrderId AS Ent_OrderId,
        O.Ent_Note AS Ent_Note,
        O.UseAmount AS UseAmount,
        O.RegistDate AS RegistDate,
        -- O.OutOfAmends AS OutOfAmends,
        IFNULL(O.OutOfAmends, 0) AS OutOfAmends,
        (CASE
            WHEN O.Incre_Status = 1 THEN 1
            WHEN O.Incre_Status = - 1 THEN - 1
            ELSE 0
        END) AS IncreStatus,
        S.CarriageFee AS CarriageFee,
        S.ChargeFee AS ChargeFee,
        O.Chg_ExecDate AS Chg_ExecDate,
        O.Cnl_CantCancelFlg AS Cnl_CantCancelFlg,
        O.Cnl_Status AS Cnl_Status,
        O.AnotherDeliFlg AS AnotherDeliFlg,
        O.CombinedClaimTargetStatus AS CombinedClaimTargetStatus,
        O.P_OrderSeq,
        O.CombinedClaimParentFlg AS CombinedClaimParentFlg,
        O.ClaimSendingClass AS ClaimSendingClass,
        O.ServiceExpectedDate AS ServiceExpectedDate,
        C.CustomerId AS CustomerId,
        C.NameKj AS NameKj,
        C.NameKn AS NameKn,
        C.PostalCode AS PostalCode,
        C.UnitingAddress AS UnitingAddress,
        C.Phone AS Phone,
        C.MailAddress AS MailAddress,
        C.EntCustId AS EntCustId,
        S.DestNameKj AS DestNameKj,
        S.DestNameKn AS DestNameKn,
        S.DestPostalCode AS DestPostalCode,
        S.DestUnitingAddress AS DestUnitingAddress,
        S.DestPhone AS DestPhone,
        S.OrderItemId AS OrderItemId,
        S.OrderItemNames AS OrderItemNames,
        S.ItemNameKj AS ItemNameKj,
        S.ItemCount AS ItemCount,
        S.Deli_JournalIncDate AS Deli_JournalIncDate,
        S.Deli_DeliveryMethod AS Deli_DeliveryMethod,
        S.Deli_DeliveryMethodName AS Deli_DeliveryMethodName,
        S.Deli_JournalNumber AS Deli_JournalNumber,
        L.CancelDate AS CancelDate,
        L.CancelReason AS CancelReason,
        L.ApprovalDate AS ApprovalDate,
        L.CancelReasonCode AS CancelReasonCode,
        P.ExecScheduleDate AS ExecScheduleDate,
        CL.ClaimDate AS ClaimDate,
        (CASE
            WHEN ISNULL(O.Cnl_ReturnSaikenCancelFlg) THEN 0
            ELSE O.Cnl_ReturnSaikenCancelFlg
        END) AS Cnl_ReturnSaikenCancelFlg,
        (CASE
            WHEN (O.Cnl_Status = 0) THEN 0
            WHEN
                ((O.Cnl_Status = 1)
                    AND (IFNULL(O.Cnl_ReturnSaikenCancelFlg, 0) = 0))
            THEN
                1
            WHEN
                ((O.Cnl_Status = 2)
                    AND (IFNULL(O.Cnl_ReturnSaikenCancelFlg, 0) = 0))
            THEN
                2
            WHEN
                ((O.Cnl_Status = 1)
                    AND (O.Cnl_ReturnSaikenCancelFlg = 1))
            THEN
                11
            WHEN
                ((O.Cnl_Status = 2)
                    AND (O.Cnl_ReturnSaikenCancelFlg = 1))
            THEN
                12
        END) AS RealCancelStatus,
/*
        (CASE
            WHEN
                ((SA.AlertClass = 0)
                    AND (SA.AlertSign = 1))
            THEN
                1
            ELSE 0
        END) AS Deli_JournalNumberAlert,
        (CASE
            WHEN
                ((SA.AlertClass = 1)
                    AND (SA.AlertSign = 1))
            THEN
                1
            ELSE 0
        END) AS ArrivalConfirmAlert
*/
        (CASE
            WHEN
                (SELECT MAX(AlertSign) FROM T_StagnationAlert WHERE AlertClass = 0 AND OrderSeq = O.OrderSeq)
            THEN
                1
            ELSE 0
        END) AS Deli_JournalNumberAlert,
        (CASE
            WHEN
                (SELECT MAX(AlertSign) FROM T_StagnationAlert WHERE AlertClass = 1 AND OrderSeq = O.OrderSeq)
            THEN
                1
            ELSE 0
        END) AS ArrivalConfirmAlert

    FROM
        T_Order O
        INNER JOIN T_Customer C
                ON C.OrderSeq = O.OrderSeq
        INNER JOIN T_OrderSummary S
                ON S.OrderSeq = O.OrderSeq
        LEFT  JOIN T_Cancel L
                ON L.OrderSeq = O.OrderSeq
               AND L.ValidFlg = 1
        LEFT  JOIN T_PayingControl P
                ON P.Seq = O.Chg_Seq
        LEFT  JOIN T_ClaimControl CL
                ON CL.OrderSeq = O.P_OrderSeq
 /*
        LEFT  JOIN T_StagnationAlert SA
                ON SA.OrderSeq = O.OrderSeq
 */
;