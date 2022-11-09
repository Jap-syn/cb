DROP PROCEDURE IF EXISTS P_Treas_Accounts_PayableReceivable_Day;

DELIMITER $$

CREATE PROCEDURE P_Treas_Accounts_PayableReceivable_Day( IN pi_user_id INT )
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名       ：  P_Treas_Accounts_PayableReceivable_Day
 *
 * 概要             ：  直営未払金兼売掛金明細（日次）
 *
 * 引数             ：  [I/ ]pi_user_id        ﾕｰｻﾞｰID
 *
 * 履歴             ：  2015/10/01  NDC 新規作成
 *
 *****************************************************************************/
BEGIN
    -- ------------------------------
    -- 変数宣言
    -- ------------------------------
    DECLARE v_BusinessDate              DATE;       -- 業務日付
    DECLARE v_AccountingMonth           DATE;       -- 会計月

    -- ｶｰｿﾙ用
    DECLARE v_EnterpriseId              INT;        -- 加盟店ID
    DECLARE v_OemId                     INT;        -- OEMID
    DECLARE v_CheckingAmount            INT;        -- 消込金額

    DECLARE v_Seq                       INT;        -- ｼｰｹﾝｽ
    DECLARE v_AccountsDue               INT;        -- 在残加盟店未収金
    DECLARE v_DebtFixedDate             INT;        -- 売上確定日

    -- その他
    DECLARE v_done                      INT DEFAULT 0;

    -- ｶｰｿﾙ宣言
    DECLARE cur1 CURSOR FOR
        SELECT pc.EnterpriseId
              ,pc.OemId
              ,IFNULL(pc.DecisionPayment, 0) + IFNULL(pc.TransferCommission, 0) - IFNULL(pc.CarryOver, 0)
        FROM  T_PayingControl pc
        INNER JOIN AT_PayingControl apc
        ON    pc.Seq = apc.Seq
        LEFT  JOIN T_Enterprise e
        ON    pc.EnterpriseId = e.EnterpriseId
        LEFT  JOIN M_Code c1
        ON    c1.CodeId         = 160
        AND   c1.KeyCode        = IFNULL(pc.OemId, 0)
        WHERE pc.PayingControlStatus = 1                -- 1:本締め
        AND   apc.DailySummaryFlg = 0                   -- 0:日別集計前
        AND   IFNULL(pc.CarryOver, 0) < 0
        AND   IFNULL(pc.CarryOver, 0) < IFNULL(pc.DecisionPayment, 0) + IFNULL(pc.TransferCommission, 0)
        AND   e.ServiceInDate IS NOT NULL
        AND   c1.Class1 = 0;

    -- ﾊﾝﾄﾞﾗｰ
    DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET v_done = 1;   -- cursor読み取り終了

    -- ------------------------------
    -- 変数初期化
    -- ------------------------------
    SET v_BusinessDate = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'BusinessDate');         -- 業務日付
    SET v_AccountingMonth = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'AccountingMonth');   -- 会計月

    -- ------------------------------
    -- 1.加盟店未収金
    -- ------------------------------
    INSERT INTO AT_Cb_Accounts_PayableReceivable(
         DailyMonthlyFlg                            -- 日次･月次区分
        ,ProcessingDate                             -- 処理日付
        ,AccountDate                                -- 会計月
        ,EnterpriseId                               -- 加盟店ID
        ,EnterpriseNameKj                           -- 加盟店名
        ,OrderSeq                                   -- 注文Seq
        ,OrderId                                    -- 注文ID
        ,ManCustId                                  -- 管理顧客番号 
        ,ManCusNameKj                               -- 顧客名
        ,OutOfAmends                                -- 補償有無
        ,DebtDefiniteConditions                     -- 債務確定条件
        ,DebtFixedDate                              -- 売上確定日
        ,SettlementCosingDate                       -- 立替締日
        ,SettlementExpectedDate                     -- 立替予定日
        ,AccountsPayablePending                     -- 精算日到来未払金保留
        ,UseAmount                                  -- 商品等代金合計(未払金)
        ,SettlementFeeRate                          -- 決済手数料率
        ,SettlementFee                              -- 決済手数料
        ,ClaimFee                                   -- 請求手数料(税込)
        ,MonthlyFee                                 -- 月額固定費(税込)
        ,IncludeMonthlyFee                          -- 同梱月額固定費
        ,ApiMonthlyFee                              -- API月額固定費
        ,CreditNoticeMonthlyFee                     -- 与信結果通知ｻｰﾋﾞｽ月額固定費
        ,NCreditNoticeMonthlyFee                    -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,ReserveMonthlyFee                          -- 月額固定費予備
        ,AccountsReceivableTotal                    -- 売掛金合計
        ,AccountsDue                                -- 現在残加盟店未収金
        ,CarryOverAmount                            -- 振替時未精算繰越
        ,InitiallyRemainAccountsPayable             -- 当初残(未払金)
        ,InitiallyRemainAccountsReceivable          -- 当初残(売掛金)
        ,InitiallyRemainStampFee                    -- 当初残印紙代
        ,InitiallyRemainAdjustmentAmount            -- 当初残精算調整額
        ,InitiallyRemainRefund                      -- 当初残返金
        ,AccountsDueFlg                             -- 現在残未収金消込ﾌﾗｸﾞ
        ,RegistDate                                 -- 登録日時
        ,RegistId                                   -- 登録者
        ,UpdateDate                                 -- 更新日時
        ,UpdateId                                   -- 更新者
        ,ValidFlg                                   -- 有効ﾌﾗｸﾞ
       )
    SELECT
         0                                          -- 日次･月次区分
        ,v_BusinessDate                             -- 処理日付
        ,v_AccountingMonth                          -- 会計月
        ,pc.EnterpriseId                            -- 加盟店ID
        ,e.EnterpriseNameKj                         -- 加盟店名
        ,NULL                                       -- 注文Seq
        ,NULL                                       -- 注文ID
        ,NULL                                       -- 管理顧客番号 
        ,NULL                                       -- 顧客名
        ,NULL                                       -- 補償有無
        ,c2.Class1                                  -- 売上確定条件
        ,pc.DecisionDate                            -- 売上確定日
        ,pc.FixedDate                               -- 立替締日
        ,pc.ExecScheduleDate                        -- 立替予定日
        ,NULL                                       -- 精算日到来未払金保留
        ,NULL                                       -- 商品等代金合計(未払金)
        ,NULL                                       -- 決済手数料率
        ,NULL                                       -- 決済手数料
        ,NULL                                       -- 請求手数料(税込)
        ,NULL                                       -- 月額固定費(税込)
        ,NULL                                       -- 同梱月額固定費
        ,NULL                                       -- API月額固定費
        ,NULL                                       -- 与信結果通知ｻｰﾋﾞｽ月額固定費
        ,NULL                                       -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,NULL                                       -- 月額固定費予備
        ,NULL                                       -- 売掛金合計
        ,( IFNULL(pc.DecisionPayment, 0) + IFNULL(pc.TransferCommission, 0) - IFNULL(pc.CarryOver, 0) ) * -1    -- 現在残加盟店未収金
        ,IFNULL(pc.DecisionPayment, 0) + IFNULL(pc.TransferCommission, 0) - IFNULL(pc.CarryOver, 0)             -- 振替時未精算繰越
        ,pa1.Sum_UseAmount                          -- 当初残(未払金)
        ,IFNULL(pa1.Sum_SettlementFee, 0) + IFNULL(pa1.Sum_ClaimFee, 0)   -- 当初残(売掛金)
        ,pc.StampFeeTotal                           -- 当初残印紙代
        ,pc.AdjustmentAmount                        -- 当初残精算調整額
        ,(IFNULL(c1.Sum_RepayTotal1, 0) + IFNULL(c2.Sum_RepayTotal2, 0) - IFNULL(c3.Sum_ChargeAmount, 0)) * -1  -- 当初残返金
        ,2                                          -- 現在残未収金消込ﾌﾗｸﾞ
        ,NOW()                                      -- 登録日時
        ,pi_user_id                                 -- 登録者
        ,NOW()                                      -- 更新日時
        ,pi_user_id                                 -- 更新者
        ,1                                          -- 有効ﾌﾗｸﾞ
    FROM  T_PayingControl pc
    INNER JOIN AT_PayingControl apc
    ON    pc.Seq = apc.Seq
    LEFT  JOIN T_Enterprise e
    ON    pc.EnterpriseId = e.EnterpriseId
    LEFT  JOIN M_Code c1
    ON    c1.CodeId         = 160
    AND   c1.KeyCode        = IFNULL(pc.OemId, 0)
    LEFT  JOIN M_Code c2
    ON    c2.CodeId         = 158
    AND   c2.KeyCode        = 10                    -- 10:加盟店未収金
    INNER JOIN ( SELECT pa.PayingControlSeq
                       ,SUM(IFNULL(pa.UseAmount, 0)) AS Sum_UseAmount
                       ,SUM(IFNULL(pa.SettlementFee, 0)) AS Sum_SettlementFee
                       ,SUM(IFNULL(pa.ClaimFee, 0)) AS Sum_ClaimFee
                 FROM T_PayingAndSales pa
                 WHERE pa.ValidFlg = 1
                 GROUP BY pa.PayingControlSeq ) pa1
    ON pc.seq = pa1.PayingControlSeq
    LEFT JOIN ( SELECT  c.PayingControlSeq
                       ,SUM(IFNULL(c.RepayTotal, 0)) as Sum_RepayTotal1
                FROM T_Cancel c
                WHERE c.CancelPhase = 4             -- 4:立替実行前､顧客入金後
                AND   c.ValidFlg = 1
                GROUP BY c.PayingControlSeq ) c1
    ON pc.seq = c1.PayingControlSeq
    LEFT JOIN ( SELECT  c.PayingControlSeq
                       ,SUM(IFNULL(c.RepayTotal, 0)) as Sum_RepayTotal2
                FROM T_Cancel c
                WHERE c.CancelPhase = 3             -- 3:立替実行後､顧客入金後
                AND   c.ValidFlg = 1
                GROUP BY c.PayingControlSeq ) c2
    ON pc.seq = c2.PayingControlSeq
    LEFT JOIN ( SELECT  c.PayingControlSeq
                       ,SUM(IFNULL(pa.ChargeAmount, 0)) as Sum_ChargeAmount
                FROM T_Cancel c
                INNER JOIN T_PayingAndSales pa
                ON pa.OrderSeq = c.OrderSeq
                WHERE c.CancelPhase = 3             -- 3:立替実行後､顧客入金後
                AND   c.ValidFlg = 1
                GROUP BY c.PayingControlSeq ) c3
    ON pc.seq = c3.PayingControlSeq
    WHERE pc.PayingControlStatus = 1                -- 1:本締め
    AND   apc.DailySummaryFlg = 0                   -- 0:日別集計前
    AND   IFNULL(pc.DecisionPayment, 0) + IFNULL(pc.TransferCommission, 0) < 0
    AND   IFNULL(pc.CarryOver, 0) > IFNULL(pc.DecisionPayment, 0) + IFNULL(pc.TransferCommission, 0)
    AND   e.ServiceInDate IS NOT NULL
    AND   c1.Class1 = 0
    ORDER BY pc.EnterpriseId, pc.DecisionDate;

    -- ------------------------------
    -- 2.未精算残
    -- ------------------------------
    INSERT INTO AT_Cb_Accounts_PayableReceivable(
         DailyMonthlyFlg                            -- 日次･月次区分
        ,ProcessingDate                             -- 処理日付
        ,AccountDate                                -- 会計月
        ,EnterpriseId                               -- 加盟店ID
        ,EnterpriseNameKj                           -- 加盟店名
        ,OrderSeq                                   -- 注文Seq
        ,OrderId                                    -- 注文ID
        ,ManCustId                                  -- 管理顧客番号 
        ,ManCusNameKj                               -- 顧客名
        ,OutOfAmends                                -- 補償有無
        ,DebtDefiniteConditions                     -- 債務確定条件
        ,DebtFixedDate                              -- 売上確定日
        ,SettlementCosingDate                       -- 立替締日
        ,SettlementExpectedDate                     -- 立替予定日
        ,AccountsPayablePending                     -- 精算日到来未払金保留
        ,UseAmount                                  -- 商品等代金合計(未払金)
        ,SettlementFeeRate                          -- 決済手数料率
        ,SettlementFee                              -- 決済手数料
        ,ClaimFee                                   -- 請求手数料(税込)
        ,MonthlyFee                                 -- 月額固定費(税込)
        ,IncludeMonthlyFee                          -- 同梱月額固定費
        ,ApiMonthlyFee                              -- API月額固定費
        ,CreditNoticeMonthlyFee                     -- 与信結果通知ｻｰﾋﾞｽ月額固定費
        ,NCreditNoticeMonthlyFee                    -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,ReserveMonthlyFee                          -- 月額固定費予備
        ,AccountsReceivableTotal                    -- 売掛金合計
        ,AccountsDue                                -- 現在残加盟店未収金
        ,CarryOverAmount                            -- 振替時未精算繰越
        ,InitiallyRemainAccountsPayable             -- 当初残(未払金)
        ,InitiallyRemainAccountsReceivable          -- 当初残(売掛金)
        ,InitiallyRemainStampFee                    -- 当初残印紙代
        ,InitiallyRemainAdjustmentAmount            -- 当初残精算調整額
        ,InitiallyRemainRefund                      -- 当初残返金
        ,AccountsDueFlg                             -- 現在残未収金消込ﾌﾗｸﾞ
        ,RegistDate                                 -- 登録日時
        ,RegistId                                   -- 登録者
        ,UpdateDate                                 -- 更新日時
        ,UpdateId                                   -- 更新者
        ,ValidFlg                                   -- 有効ﾌﾗｸﾞ
       )
    SELECT
         0                                          -- 日次･月次区分
        ,v_BusinessDate                             -- 処理日付
        ,v_AccountingMonth                          -- 会計月
        ,pc.EnterpriseId                            -- 加盟店ID
        ,e.EnterpriseNameKj                         -- 加盟店名
        ,NULL                                       -- 注文Seq
        ,NULL                                       -- 注文ID
        ,NULL                                       -- 管理顧客番号 
        ,NULL                                       -- 顧客名
        ,NULL                                       -- 補償有無
        ,c2.Class1                                  -- 売上確定条件
        ,pc.DecisionDate                            -- 売上確定日
        ,pc.FixedDate                               -- 立替締日
        ,pc.ExecScheduleDate                        -- 立替予定日
        ,IFNULL(pc.DecisionPayment, 0) + IFNULL(pc.TransferCommission, 0) - IFNULL(pc.CarryOver, 0)             -- 精算日到来未払金保留
        ,NULL                                       -- 商品等代金合計(未払金)
        ,NULL                                       -- 決済手数料率
        ,NULL                                       -- 決済手数料
        ,NULL                                       -- 請求手数料(税込)
        ,NULL                                       -- 月額固定費(税込)
        ,NULL                                       -- 同梱月額固定費
        ,NULL                                       -- API月額固定費
        ,NULL                                       -- 与信結果通知ｻｰﾋﾞｽ月額固定費
        ,NULL                                       -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,NULL                                       -- 月額固定費予備
        ,NULL                                       -- 売掛金合計
        ,NULL                                       -- 現在残加盟店未収金
        ,IFNULL(pc.DecisionPayment, 0) + IFNULL(pc.TransferCommission, 0) - IFNULL(pc.CarryOver, 0)             -- 振替時未精算繰越
        ,pa1.Sum_UseAmount                          -- 当初残(未払金)
        ,IFNULL(pa1.Sum_SettlementFee, 0) + IFNULL(pa1.Sum_ClaimFee, 0)   -- 当初残(売掛金)
        ,pc.StampFeeTotal                           -- 当初残印紙代
        ,pc.AdjustmentAmount                        -- 当初残精算調整額
        ,(IFNULL(c1.Sum_RepayTotal1, 0) + IFNULL(c2.Sum_RepayTotal2, 0) - IFNULL(c3.Sum_ChargeAmount, 0)) * -1  -- 当初残返金
        ,0                                          -- 現在残未収金消込ﾌﾗｸﾞ
        ,NOW()                                      -- 登録日時
        ,pi_user_id                                 -- 登録者
        ,NOW()                                      -- 更新日時
        ,pi_user_id                                 -- 更新者
        ,1                                          -- 有効ﾌﾗｸﾞ
    FROM  T_PayingControl pc
    INNER JOIN AT_PayingControl apc
    ON    pc.Seq = apc.Seq
    LEFT  JOIN T_Enterprise e
    ON    pc.EnterpriseId = e.EnterpriseId
    LEFT  JOIN M_Code c1
    ON    c1.CodeId         = 160
    AND   c1.KeyCode        = IFNULL(pc.OemId, 0)
    LEFT  JOIN M_Code c2
    ON    c2.CodeId         = 158
    AND   c2.KeyCode        = 11                    -- 11:未精算残
    INNER JOIN ( SELECT pa.PayingControlSeq
                       ,SUM(IFNULL(pa.UseAmount, 0)) AS Sum_UseAmount
                       ,SUM(IFNULL(pa.SettlementFee, 0)) AS Sum_SettlementFee
                       ,SUM(IFNULL(pa.ClaimFee, 0)) AS Sum_ClaimFee
                 FROM T_PayingAndSales pa
                 WHERE pa.ValidFlg = 1
                 GROUP BY pa.PayingControlSeq ) pa1
    ON pc.seq = pa1.PayingControlSeq
    LEFT JOIN ( SELECT  c.PayingControlSeq
                       ,SUM(IFNULL(c.RepayTotal, 0)) as Sum_RepayTotal1
                FROM T_Cancel c
                WHERE c.CancelPhase = 4             -- 4:立替実行前､顧客入金後
                AND   c.ValidFlg = 1
                GROUP BY c.PayingControlSeq ) c1
    ON pc.seq = c1.PayingControlSeq
    LEFT JOIN ( SELECT  c.PayingControlSeq
                       ,SUM(IFNULL(c.RepayTotal, 0)) as Sum_RepayTotal2
                FROM T_Cancel c
                WHERE c.CancelPhase = 3             -- 3:立替実行後､顧客入金後
                AND   c.ValidFlg = 1
                GROUP BY c.PayingControlSeq ) c2
    ON pc.seq = c2.PayingControlSeq
    LEFT JOIN ( SELECT  c.PayingControlSeq
                       ,SUM(IFNULL(pa.ChargeAmount, 0)) as Sum_ChargeAmount
                FROM T_Cancel c
                INNER JOIN T_PayingAndSales pa
                ON pa.OrderSeq = c.OrderSeq
                WHERE c.CancelPhase = 3             -- 3:立替実行後､顧客入金後
                AND   c.ValidFlg = 1
                GROUP BY c.PayingControlSeq ) c3
    ON pc.seq = c3.PayingControlSeq
    WHERE pc.PayingControlStatus = 1                -- 1:本締め
    AND   apc.DailySummaryFlg = 0                   -- 0:日別集計前
    AND   IFNULL(pc.DecisionPayment, 0) < 0
    AND   IFNULL(pc.DecisionPayment, 0) + IFNULL(pc.TransferCommission, 0) >= 0
    AND   IFNULL(pc.CarryOver, 0) > IFNULL(pc.DecisionPayment, 0) + IFNULL(pc.TransferCommission, 0)
    AND   e.ServiceInDate IS NOT NULL
    AND   c1.Class1 = 0
    ;

    -- ------------------------------
    -- 3.店舗未収金の消込
    -- ------------------------------
BEGIN
    -- ｶｰｿﾙｵｰﾌﾟﾝ
    OPEN cur1;

    read_loop1: LOOP
        -- ｶｰｿﾙﾌｪｯﾁ
        FETCH cur1 INTO
            v_EnterpriseId
           ,v_OemId
           ,v_CheckingAmount;

        IF v_done = 1 THEN leave read_loop1;
        END IF;

        BEGIN

            DECLARE cur2 CURSOR FOR
                SELECT Seq
                      ,AccountsDue                 -- 現在残加盟店未収金
                      ,DebtFixedDate               -- 売上確認日
                FROM   AT_Cb_Accounts_PayableReceivable
                WHERE  EnterpriseId = v_EnterpriseId
                AND    AccountsDueFlg = 2
                ORDER BY DebtFixedDate;

            OPEN cur2;

            read_loop2: LOOP

                -- ｶｰｿﾙﾌｪｯﾁ
                FETCH cur2 INTO
                    v_Seq
                   ,v_AccountsDue
                   ,v_DebtFixedDate;

                IF v_done = 1 OR v_CheckingAmount <=0 THEN leave read_loop2;
                END IF;

                IF v_CheckingAmount - v_AccountsDue >= 0 THEN
                    UPDATE AT_Cb_Accounts_PayableReceivable
                    SET  AccountsDue = 0
                        ,AccountsDueFlg = 1
                        ,UpdateDate = now()
                        ,UpdateId = 1
                    WHERE Seq = v_Seq;
                ELSE
                    IF v_CheckingAmount > 0 THEN
                        UPDATE AT_Cb_Accounts_PayableReceivable
                        SET  AccountsDue = v_AccountsDue - v_CheckingAmount
                            ,UpdateDate = now()
                            ,UpdateId = 1
                        WHERE Seq = v_Seq;
                    END IF;
                END IF;

                SET v_CheckingAmount = v_CheckingAmount - v_AccountsDue;

            END LOOP;

            -- ｶｰｿﾙｸﾛｰｽﾞ
            CLOSE cur2;

            SET v_done = 0;
        END;

    END LOOP;

    -- ｶｰｿﾙｸﾛｰｽﾞ
    CLOSE cur1;
END;

END
$$

DELIMITER ;
