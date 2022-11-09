DROP procedure IF EXISTS `procMigrateAT_Cb_Accounts_PayableReceivable`;

DELIMITER $$
CREATE PROCEDURE `procMigrateAT_Cb_Accounts_PayableReceivable` ()
BEGIN
    /* 移行処理：直営未払金兼売掛金明細 */
    
    DECLARE
        updDttm    datetime;
    
    SET updDttm = now();
    /* 20151124 当データは移行不要
    -- 加盟店を抽出
    INSERT INTO AT_Cb_Accounts_PayableReceivable (
         -- Seq
         DailyMonthlyFlg
        ,ProcessingDate
        ,AccountDate
        ,EnterpriseId
        ,EnterpriseNameKj
        ,OrderSeq
        ,OrderId
        ,ManCustId
        ,ManCusNameKj
        ,OutOfAmends
        ,DebtDefiniteConditions
        ,DebtFixedDate
        ,SettlementCosingDate
        ,SettlementExpectedDate
        ,AccountsPayablePending
        ,UseAmount
        ,SettlementFeeRate
        ,SettlementFee
        ,ClaimFee
        ,MonthlyFee
        ,IncludeMonthlyFee
        ,ApiMonthlyFee
        ,CreditNoticeMonthlyFee
        ,NCreditNoticeMonthlyFee
        ,ReserveMonthlyFee
        ,AccountsReceivableTotal
        ,AccountsDue
        ,CarryOverAmount
        ,InitiallyRemainAccountsPayable
        ,InitiallyRemainAccountsReceivable
        ,InitiallyRemainStampFee
        ,InitiallyRemainAdjustmentAmount
        ,InitiallyRemainRefund
        ,AccountsDueFlg
        ,RegistDate
        ,RegistId
        ,UpdateDate
        ,UpdateId
        ,ValidFlg
    )
    SELECT
        -- Seq
         1                      -- DailyMonthlyFlg
        ,'2015-11-30'           -- ProcessingDate
        ,'2015-12-01'           -- AccountDate
        ,pc.EnterpriseId        -- EnterpriseId
        ,e.EnterpriseNameKj     -- EnterpriseNameKj
        ,NULL                   -- OrderSeq
        ,NULL                   -- OrderId
        ,NULL                   -- ManCustId
        ,NULL                   -- ManCusNameKj
        ,NULL                   -- OutOfAmends
        ,'加盟店未収金'         -- DebtDefiniteConditions
        ,pc.DecisionDate        -- DebtFixedDate
        ,pc.FixedDate           -- SettlementCosingDate
        ,pc.ExecScheduleDate    -- SettlementExpectedDate
        ,NULL                   -- AccountsPayablePending
        ,NULL                   -- UseAmount
        ,NULL                   -- SettlementFeeRate
        ,NULL                   -- SettlementFee
        ,NULL                   -- ClaimFee
        ,NULL                   -- MonthlyFee
        ,NULL                   -- IncludeMonthlyFee
        ,NULL                   -- ApiMonthlyFee
        ,NULL                   -- CreditNoticeMonthlyFee
        ,NULL                   -- NCreditNoticeMonthlyFee
        ,NULL                   -- ReserveMonthlyFee
        ,NULL                   -- AccountsReceivableTotal
        ,(DecisionPayment + TransferCommission) * -1 -- AccountsDue （振込確定金額＋振込手数料×-1）
        ,(DecisionPayment + TransferCommission)      -- CarryOverAmount
        ,NULL                   -- InitiallyRemainAccountsPayable
        ,NULL                   -- InitiallyRemainAccountsReceivable
        ,NULL                   -- InitiallyRemainStampFee
        ,NULL                   -- InitiallyRemainAdjustmentAmount
        ,NULL                   -- InitiallyRemainRefund
        ,2                      -- AccountsDueFlg
        ,NOW()                  -- RegistDate
        ,9                      -- RegistId
        ,NOW()                  -- UpdateDate
        ,9                      -- UpdateId
        ,1                      -- ValidFlg
    FROM T_Enterprise e
         INNER JOIN T_PayingControl pc
                 ON e.EnterpriseId = pc.EnterpriseId
                AND pc.Seq = ( SELECT MAX(Seq) FROM T_PayingControl WHERE EnterpriseId = pc.EnterpriseId)
    WHERE IFNULL(e.OemId , 0) IN (0, 2)      -- 直営、SMBCの加盟店のみ
    AND   (pc.DecisionPayment + pc.TransferCommission) < 0
    ;
    */
    
END
$$

DELIMITER ;


