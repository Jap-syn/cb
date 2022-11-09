DROP PROCEDURE IF EXISTS P_Treas_Accounts_PayableReceivable_Month;

DELIMITER $$

CREATE PROCEDURE P_Treas_Accounts_PayableReceivable_Month( IN pi_user_id INT )
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名       ：  P_Treas_Accounts_PayableReceivable_Month
 *
 * 概要             ：  直営未払金兼売掛金明細（月次）
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
    DECLARE v_BusinessDate      DATE;                   -- 業務日付
    DECLARE v_AccountingMonth   DATE;                   -- 会計月

    DECLARE v_EnterpriseId                  BIGINT;
    DECLARE v_EnterpriseNameKj              VARCHAR(160);
    DECLARE v_OrderSeq                      BIGINT;
    DECLARE v_OrderId                       VARCHAR(50);
    DECLARE v_ManCustId                     BIGINT;
    DECLARE v_ManCusNameKj                  VARCHAR(160);
    DECLARE v_OutOfAmends                   VARCHAR(50);
    DECLARE v_SalesDefiniteConditions       VARCHAR(50);
    DECLARE v_SalesDefiniteDate             DATE;
    DECLARE v_FixedDate                     DATE;
    DECLARE v_ExecScheduleDate              DATE;
    DECLARE v_UseAmount                     BIGINT;
    DECLARE v_AppSettlementFeeRate          DECIMAL(16,5);
    DECLARE v_SettlementFee                 BIGINT;
    DECLARE v_ClaimFee                      BIGINT;

    -- ------------------------------
    -- 変数初期化
    -- ------------------------------
    SET v_BusinessDate      = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'BusinessDate');       -- 業務日付
    SET v_AccountingMonth   = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'AccountingMonth');    -- 会計月


    -- ------------------------------------------------------------
    -- 4.直営未払金兼売掛金明細（月次）作成：月額固定費
    -- ------------------------------------------------------------
    INSERT INTO AT_Cb_Accounts_PayableReceivable (
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
        ,ValidFlg )
    SELECT
         1                                                                          -- 日次･月次区分 1：月次
        ,v_BusinessDate                                                             -- 処理日付
        ,v_AccountingMonth                                                          -- 会計月
        ,emci.EnterpriseId                                                          -- 加盟店ID
        ,e.EnterpriseNameKj                                                         -- 加盟店名
        ,NULL                                                                       -- 注文Seq
        ,NULL                                                                       -- 注文ID
        ,NULL                                                                       -- 管理顧客番号 
        ,NULL                                                                       -- 顧客名
        ,NULL                                                                       -- 補償有無
        ,c2.Class1                                                                  -- 債務確定条件
        ,LAST_DAY(v_AccountingMonth)                                                -- 売上確定日
        ,LAST_DAY(v_AccountingMonth)                                                -- 立替締日
        ,LAST_DAY(v_AccountingMonth)                                                -- 立替予定日
        ,NULL                                                                       -- 精算日到来未払金保留
        ,NULL                                                                       -- 商品等代金合計(未払金)
        ,NULL                                                                       -- 決済手数料率
        ,NULL                                                                       -- 決済手数料
        ,NULL                                                                       -- 請求手数料(税込)
        ,IFNULL( emci.MonthlyFee, 0 ) + IFNULL( emci.MonthlyFeeTax, 0 )                             -- 月額固定費(税込)
        ,IFNULL( emci.IncludeMonthlyFee, 0 ) + IFNULL( emci.IncludeMonthlyFeeTax, 0 )               -- 同梱月額固定費
        ,IFNULL( emci.ApiMonthlyFee, 0 ) + IFNULL( emci.ApiMonthlyFeeTax, 0 )                       -- 月額固定費
        ,IFNULL( emci.CreditNoticeMonthlyFee, 0 ) + IFNULL( emci.CreditNoticeMonthlyFeeTax, 0 )     -- 与信結果通知ｻｰﾋﾞｽ月額固定費
        ,IFNULL( emci.NCreditNoticeMonthlyFee, 0 ) + IFNULL( emci.NCreditNoticeMonthlyFeeTax, 0 )   -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,IFNULL( emci.ReserveMonthlyFee, 0 ) + IFNULL( emci.ReserveMonthlyFeeTax, 0 )               -- 月額固定費予備
        ,IFNULL( emci.MonthlyFee, 0 ) + IFNULL( emci.MonthlyFeeTax, 0 ) +
         IFNULL( emci.IncludeMonthlyFee, 0 ) + IFNULL( emci.IncludeMonthlyFeeTax, 0 ) +
         IFNULL( emci.ApiMonthlyFee, 0 ) + IFNULL( emci.ApiMonthlyFeeTax, 0 ) +
         IFNULL( emci.CreditNoticeMonthlyFee, 0 ) + IFNULL( emci.CreditNoticeMonthlyFeeTax, 0 ) +
         IFNULL( emci.NCreditNoticeMonthlyFee, 0 ) + IFNULL( emci.NCreditNoticeMonthlyFeeTax, 0 ) +
         IFNULL( emci.ReserveMonthlyFee, 0 ) + IFNULL( emci.ReserveMonthlyFeeTax, 0 )               -- 売掛金合計
        ,NULL                                                                       -- 現在残加盟店未収金
        ,NULL                                                                       -- 振替時未精算繰越
        ,NULL                                                                       -- 当初残(未払金)
        ,NULL                                                                       -- 当初残(売掛金)
        ,NULL                                                                       -- 当初残印紙代
        ,NULL                                                                       -- 当初残精算調整額
        ,NULL                                                                       -- 当初残返金
        ,0                                                                          -- 現在残未収金消込ﾌﾗｸﾞ 0:消込対象外
        ,NOW()                                                                      -- 登録日時
        ,pi_user_id                                                                 -- 登録者
        ,NOW()                                                                      -- 更新日時
        ,pi_user_id                                                                 -- 更新者
        ,1                                                                          -- 有効ﾌﾗｸﾞ 1:有効
    FROM        AT_EnterpriseMonthlyClosingInfo emci
    INNER JOIN  T_Enterprise e
    ON      e.EnterpriseId = emci.EnterpriseId
    INNER JOIN  M_Code c1
    ON      c1.CodeId           = 160
    AND     c1.KeyCode          = IFNULL(emci.OemId, 0)
    AND     c1.Class1           = 0
    LEFT JOIN   M_Code c2
    ON      c2.CodeId           = 158
    AND     c2.KeyCode          = 8
    WHERE emci.ValidFlg                   = 1
    AND   emci.ClosingMonthly             = v_AccountingMonth
    AND   IFNULL( emci.MonthlyFee, 0 ) +
          IFNULL( emci.MonthlyFeeTax, 0 ) +
          IFNULL( emci.IncludeMonthlyFee, 0 ) +
          IFNULL( emci.IncludeMonthlyFeeTax, 0 ) +
          IFNULL( emci.ApiMonthlyFee, 0 ) +
          IFNULL( emci.ApiMonthlyFeeTax, 0 ) +
          IFNULL( emci.CreditNoticeMonthlyFee, 0 ) +
          IFNULL( emci.CreditNoticeMonthlyFeeTax, 0 ) +
          IFNULL( emci.NCreditNoticeMonthlyFee, 0 ) +
          IFNULL( emci.NCreditNoticeMonthlyFeeTax, 0 ) +
          IFNULL( emci.ReserveMonthlyFee, 0 ) +
          IFNULL( emci.ReserveMonthlyFeeTax, 0 ) > 0;

    -- ----------------------------------------------------------------------------------------
    -- 直営未払金兼売掛金明細（月次）作成：未払い金①
    -- ----------------------------------------------------------------------------------------
    BEGIN
        -- NO_DATA_FOUND ｲﾍﾞﾝﾄﾊﾝﾄﾞﾗ用変数
        DECLARE no_data_found INT DEFAULT 1;

        -- ｶｰｿﾙ宣言
        DECLARE l_cur CURSOR FOR
            SELECT
                 o.EnterpriseId                                                             -- 加盟店ID
                ,e.EnterpriseNameKj                                                         -- 加盟店名
                ,o.OrderSeq                                                                 -- 注文Seq
                ,o.OrderId                                                                  -- 注文ID
                ,mc.ManCustId                                                               -- 管理顧客番号 
                ,mc.NameKj                                                                  -- 顧客名
                ,c2.Class1                                                                  -- 補償有無
                ,F_GetSalesDefiniteConditions(o.OrderSeq)                                   -- 債務確定条件
                ,DATE(apas.ATUriDay)                                                        -- 売上確定日
                ,pc.FixedDate                                                               -- 立替締日
                ,pc.ExecScheduleDate                                                        -- 立替予定日
                ,IFNULL( pas.UseAmount, 0 )                                                 -- 商品等代金合計(未払金)
                ,pas.AppSettlementFeeRate                                                   -- 決済手数料率
                ,IFNULL( pas.SettlementFee, 0 )                                             -- 決済手数料
                ,IFNULL( pas.ClaimFee, 0 )                                                  -- 請求手数料(税込)
            FROM T_PayingAndSales pas
                 INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
                 INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
                 INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId AND e.ServiceInDate IS NOT NULL)
                 INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
                 LEFT OUTER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
                 LEFT OUTER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
                 INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0) AND c1.Class1 = 0)
                 LEFT OUTER JOIN M_Code c2 ON (c2.CodeId = 159 AND c2.KeyCode = IFNULL(o.OutOfAmends, 0))
                 LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
                 LEFT OUTER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq)
                 LEFT OUTER JOIN T_Cancel ca ON (ca.OrderSeq = o.OrderSeq AND ca.ValidFlg = 1)
                 LEFT OUTER JOIN T_PayingControl pc ON (pas.PayingControlSeq = pc.Seq)
            WHERE DATE_FORMAT(apas.ATUriDay, '%Y-%m') <= DATE_FORMAT(v_AccountingMonth, '%Y-%m')
            AND   DATE_FORMAT( IFNULL( pc.ExecDate , '2100-01-01' ), '%Y-%m' ) > DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            AND   DATE_FORMAT( IFNULL( ca.ApprovalDate , '2100-01-01' ), '%Y-%m' ) > DATE_FORMAT( v_AccountingMonth, '%Y-%m' );

        -- NO_DATA_FOUND 用ｲﾍﾞﾝﾄﾊﾝﾄﾞﾗ宣言
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_data_found = 0;

        -- ｶｰｿﾙｵｰﾌﾟﾝ
        OPEN    l_cur;
            -- 最初のｶｰｿﾙをFetch
            FETCH l_cur INTO v_EnterpriseId, v_EnterpriseNameKj, v_OrderSeq, v_OrderId, v_ManCustId, v_ManCusNameKj, v_OutOfAmends, v_SalesDefiniteConditions, v_SalesDefiniteDate, v_FixedDate, v_ExecScheduleDate, v_UseAmount, v_AppSettlementFeeRate, v_SettlementFee, v_ClaimFee;

            -- whileﾙｰﾌﾟで処理する
            WHILE no_data_found != 0  DO

                INSERT INTO AT_Cb_Accounts_PayableReceivable (
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
                ) VALUES (
                     1                                                                          -- 日次･月次区分 1:月次
                    ,v_BusinessDate                                                             -- 処理日付
                    ,v_AccountingMonth                                                          -- 会計月
                    ,v_EnterpriseId                                                             -- 加盟店ID
                    ,v_EnterpriseNameKj                                                         -- 加盟店名
                    ,v_OrderSeq                                                                 -- 注文Seq
                    ,v_OrderId                                                                  -- 注文ID
                    ,v_ManCustId                                                                -- 管理顧客番号
                    ,v_ManCusNameKj                                                             -- 顧客名
                    ,v_OutOfAmends                                                              -- 補償有無
                    ,v_SalesDefiniteConditions                                                  -- 債務確定条件
                    ,v_SalesDefiniteDate                                                        -- 売上確定日
                    ,v_FixedDate                                                                -- 立替締日
                    ,v_ExecScheduleDate                                                         -- 立替予定日
                    ,NULL                                                                       -- 精算日到来未払金保留
                    ,v_UseAmount                                                                -- 商品等代金合計(未払金)
                    ,v_AppSettlementFeeRate                                                     -- 決済手数料率
                    ,v_SettlementFee                                                            -- 決済手数料
                    ,v_ClaimFee                                                                 -- 請求手数料(税込)
                    ,NULL                                                                       -- 月額固定費(税込)
                    ,NULL                                                                       -- 同梱月額固定費
                    ,NULL                                                                       -- API月額固定費
                    ,NULL                                                                       -- 与信結果通知ｻｰﾋﾞｽ月額固定費
                    ,NULL                                                                       -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
                    ,NULL                                                                       -- 月額固定費予備
                    ,(v_SettlementFee + v_ClaimFee)                                             -- 売掛金合計
                    ,NULL                                                                       -- 現在残加盟店未収金
                    ,NULL                                                                       -- 振替時未精算繰越
                    ,NULL                                                                       -- 当初残(未払金)
                    ,NULL                                                                       -- 当初残(売掛金)
                    ,NULL                                                                       -- 当初残印紙代
                    ,NULL                                                                       -- 当初残精算調整額
                    ,NULL                                                                       -- 当初残返金
                    ,0                                                                          -- 現在残未収金消込ﾌﾗｸﾞ
                    ,NOW()                                                                      -- 登録日時
                    ,pi_user_id                                                                 -- 登録者
                    ,NOW()                                                                      -- 更新日時
                    ,pi_user_id                                                                 -- 更新者
                    ,1                                                                          -- 有効ﾌﾗｸﾞ 1:有効
                );

                -- 次のｶｰｿﾙをFetch
                FETCH l_cur INTO v_EnterpriseId, v_EnterpriseNameKj, v_OrderSeq, v_OrderId, v_ManCustId, v_ManCusNameKj, v_OutOfAmends, v_SalesDefiniteConditions, v_SalesDefiniteDate, v_FixedDate, v_ExecScheduleDate, v_UseAmount, v_AppSettlementFeeRate, v_SettlementFee, v_ClaimFee;
            END WHILE;
        -- ｶｰｿﾙｸﾛｰｽﾞ
        CLOSE   l_cur;

    END;

    -- ----------------------------------------------------------------------------------------
    -- 直営未払金兼売掛金明細（月次）作成：未払い金②
    -- ----------------------------------------------------------------------------------------
    BEGIN
        -- NO_DATA_FOUND ｲﾍﾞﾝﾄﾊﾝﾄﾞﾗ用変数
        DECLARE no_data_found INT DEFAULT 1;

        -- ｶｰｿﾙ宣言
        DECLARE l_cur CURSOR FOR
            SELECT
                 o.EnterpriseId                                                             -- 加盟店ID
                ,e.EnterpriseNameKj                                                         -- 加盟店名
                ,o.OrderSeq                                                                 -- 注文Seq
                ,o.OrderId                                                                  -- 注文ID
                ,mc.ManCustId                                                               -- 管理顧客番号 
                ,mc.NameKj                                                                  -- 顧客名
                ,c2.Class1                                                                  -- 補償有無
                ,c3.Class1                                                                  -- 債務確定条件
                ,ca.ApprovalDate                                                            -- 売上確定日
                ,pc.FixedDate                                                               -- 立替締日
                ,pc.ExecScheduleDate                                                        -- 立替予定日
                ,( IFNULL( pas.UseAmount, 0 ) ) * -1                                        -- 商品等代金合計(未払金)
                ,pas.AppSettlementFeeRate                                                   -- 決済手数料率
                ,( IFNULL( pas.SettlementFee, 0 ) ) * -1                                    -- 決済手数料
                ,IFNULL( pas.ClaimFee * -1, 0 )                                             -- 請求手数料(税込)
            FROM T_PayingAndSales pas
                 INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
                 INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId AND e.ServiceInDate IS NOT NULL)
                 INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
                 INNER JOIN T_Cancel ca ON (ca.OrderSeq = o.OrderSeq AND ca.ValidFlg = 1)
                 LEFT OUTER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
                 LEFT OUTER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
                 INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0) AND c1.Class1 = 0)
                 LEFT OUTER JOIN M_Code c2 ON (c2.CodeId = 159 AND c2.KeyCode = IFNULL(o.OutOfAmends, 0))
                 LEFT JOIN M_Code c3 ON (c3.CodeId = 158 AND c3.KeyCode = 6)
                 LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
                 LEFT OUTER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq)
                 LEFT OUTER JOIN T_PayingControl pc ON (ca.PayingControlSeq = pc.Seq)
                 LEFT OUTER JOIN T_PayingControl pc2 ON (pas.PayingControlSeq = pc2.Seq)
            WHERE DATE_FORMAT( IFNULL( pc.ExecDate , '2100-01-01' ), '%Y-%m' ) > DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            AND   DATE_FORMAT( IFNULL( pc2.ExecDate , '2100-01-01' ), '%Y-%m' ) <= DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            AND   DATE_FORMAT( ca.ApprovalDate, '%Y-%m' ) <= DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            AND   ca.CancelPhase IN (2,3);

        -- NO_DATA_FOUND 用ｲﾍﾞﾝﾄﾊﾝﾄﾞﾗ宣言
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_data_found = 0;

        -- ｶｰｿﾙｵｰﾌﾟﾝ
        OPEN    l_cur;
            -- 最初のｶｰｿﾙをFetch
            FETCH l_cur INTO v_EnterpriseId, v_EnterpriseNameKj, v_OrderSeq, v_OrderId, v_ManCustId, v_ManCusNameKj, v_OutOfAmends, v_SalesDefiniteConditions, v_SalesDefiniteDate, v_FixedDate, v_ExecScheduleDate, v_UseAmount, v_AppSettlementFeeRate, v_SettlementFee, v_ClaimFee;

            -- whileﾙｰﾌﾟで処理する
            WHILE no_data_found != 0  DO

                INSERT INTO AT_Cb_Accounts_PayableReceivable (
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
                ) VALUES (
                     1                                                                          -- 日次･月次区分 1:月次
                    ,v_BusinessDate                                                             -- 処理日付
                    ,v_AccountingMonth                                                          -- 会計月
                    ,v_EnterpriseId                                                             -- 加盟店ID
                    ,v_EnterpriseNameKj                                                         -- 加盟店名
                    ,v_OrderSeq                                                                 -- 注文Seq
                    ,v_OrderId                                                                  -- 注文ID
                    ,v_ManCustId                                                                -- 管理顧客番号
                    ,v_ManCusNameKj                                                             -- 顧客名
                    ,v_OutOfAmends                                                              -- 補償有無
                    ,v_SalesDefiniteConditions                                                  -- 債務確定条件
                    ,v_SalesDefiniteDate                                                        -- 売上確定日
                    ,v_FixedDate                                                                -- 立替締日
                    ,v_ExecScheduleDate                                                         -- 立替予定日
                    ,NULL                                                                       -- 精算日到来未払金保留
                    ,v_UseAmount                                                                -- 商品等代金合計(未払金)
                    ,v_AppSettlementFeeRate                                                     -- 決済手数料率
                    ,v_SettlementFee                                                            -- 決済手数料
                    ,v_ClaimFee                                                                 -- 請求手数料(税込)
                    ,NULL                                                                       -- 月額固定費(税込)
                    ,NULL                                                                       -- 同梱月額固定費
                    ,NULL                                                                       -- API月額固定費
                    ,NULL                                                                       -- 与信結果通知ｻｰﾋﾞｽ月額固定費
                    ,NULL                                                                       -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
                    ,NULL                                                                       -- 月額固定費予備
                    ,(v_SettlementFee + v_ClaimFee)                                             -- 売掛金合計
                    ,NULL                                                                       -- 現在残加盟店未収金
                    ,NULL                                                                       -- 振替時未精算繰越
                    ,NULL                                                                       -- 当初残(未払金)
                    ,NULL                                                                       -- 当初残(売掛金)
                    ,NULL                                                                       -- 当初残印紙代
                    ,NULL                                                                       -- 当初残精算調整額
                    ,NULL                                                                       -- 当初残返金
                    ,0                                                                          -- 現在残未収金消込ﾌﾗｸﾞ
                    ,NOW()                                                                      -- 登録日時
                    ,pi_user_id                                                                 -- 登録者
                    ,NOW()                                                                      -- 更新日時
                    ,pi_user_id                                                                 -- 更新者
                    ,1                                                                          -- 有効ﾌﾗｸﾞ 1:有効
                );

                -- 次のｶｰｿﾙをFetch
                FETCH l_cur INTO v_EnterpriseId, v_EnterpriseNameKj, v_OrderSeq, v_OrderId, v_ManCustId, v_ManCusNameKj, v_OutOfAmends, v_SalesDefiniteConditions, v_SalesDefiniteDate, v_FixedDate, v_ExecScheduleDate, v_UseAmount, v_AppSettlementFeeRate, v_SettlementFee, v_ClaimFee;
            END WHILE;
        -- ｶｰｿﾙｸﾛｰｽﾞ
        CLOSE   l_cur;

    END;

    -- -----------------------------------------------------------------------
    -- 10.直営未払金兼売掛金明細（月次）作成：無保証立替金戻し確定日
    -- -----------------------------------------------------------------------
    INSERT INTO AT_Cb_Accounts_PayableReceivable (
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
        ,ValidFlg )
    SELECT
         1                                                                          -- 日次･月次区分 1:月次
        ,v_BusinessDate                                                             -- 処理日付
        ,v_AccountingMonth                                                          -- 会計月
        ,o.EnterpriseId                                                             -- 加盟店ID
        ,e.EnterpriseNameKj                                                         -- 加盟店名
        ,o.OrderSeq                                                                 -- 注文Seq
        ,o.OrderId                                                                  -- 注文ID
        ,mc.ManCustId                                                               -- 管理顧客番号 
        ,mc.NameKj                                                                  -- 顧客名
        ,c2.Class1                                                                  -- 補償有無
        ,c3.Class1                                                                  -- 債務確定条件
        ,pbc.PayDecisionDate                                                        -- 売上確定日
        ,pc.FixedDate                                                               -- 立替締日
        ,pc.ExecScheduleDate                                                        -- 立替予定日
        ,NULL                                                                       -- 精算日到来未払金保留
        ,pbc.PayBackAmount                                                          -- 商品等代金合計(未払金)
        ,NULL                                                                       -- 決済手数料率
        ,NULL                                                                       -- 決済手数料
        ,NULL                                                                       -- 請求手数料(税込)
        ,NULL                                                                       -- 月額固定費(税込)
        ,NULL                                                                       -- 同梱月額固定費
        ,NULL                                                                       -- API月額固定費
        ,NULL                                                                       -- 与信結果通知ｻｰﾋﾞｽ月額固定費
        ,NULL                                                                       -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,NULL                                                                       -- 月額固定費予備
        ,NULL                                                                       -- 売掛金合計
        ,NULL                                                                       -- 現在残加盟店未収金
        ,NULL                                                                       -- 振替時未精算繰越
        ,NULL                                                                       -- 当初残(未払金)
        ,NULL                                                                       -- 当初残(売掛金)
        ,NULL                                                                       -- 当初残印紙代
        ,NULL                                                                       -- 当初残精算調整額
        ,NULL                                                                       -- 当初残返金
        ,0                                                                          -- 現在残未収金消込ﾌﾗｸﾞ
        ,NOW()                                                                      -- 登録日時
        ,pi_user_id                                                                 -- 登録者
        ,NOW()                                                                      -- 更新日時
        ,pi_user_id                                                                 -- 更新者
        ,1                                                                          -- 有効ﾌﾗｸﾞ 1:有効
    FROM        T_PayingControl pc
    INNER JOIN  T_PayingBackControl pbc
    ON      pbc.PayingControlSeq = pc.Seq
    INNER JOIN  T_PayingAndSales pas
    ON      pas.OrderSeq         = pbc.OrderSeq
    INNER JOIN  T_Order o
    ON      o.OrderSeq           = pbc.OrderSeq
    INNER JOIN  T_Enterprise e
    ON      e.EnterpriseId       = o.EnterpriseId
    AND     e.ServiceInDate IS NOT NULL
    INNER JOIN  T_Customer c
    ON      c.OrderSeq           = o.OrderSeq
    LEFT JOIN   T_EnterpriseCustomer ec
    ON      ec.EntCustSeq        = c.EntCustSeq
    LEFT JOIN   T_ManagementCustomer mc
    ON      mc.ManCustId         = ec.ManCustId
    INNER JOIN  M_Code c1
    ON      c1.CodeId            = 160
    AND     c1.KeyCode           = IFNULL( o.OemId, 0 )
    AND     c1.Class1            = 0
    LEFT JOIN   M_Code c2
    ON      c2.CodeId            = 159
    AND     c2.KeyCode           = IFNULL( o.OutOfAmends, 0 )
    LEFT JOIN   M_Code c3
    ON      c3.CodeId            = 158
    AND     c3.KeyCode           = 7
    WHERE   pc.PayingControlStatus = 1
    AND     pc.ValidFlg            = 1
    AND     pbc.ValidFlg           = 1
    AND     DATE_FORMAT(pc.ExecDate, '%Y-%m-%d') <= v_BusinessDate      -- 業務日付以前のものが対象
    AND     DATE_FORMAT( pc.ExecDate, '%Y-%m' ) > DATE_FORMAT( v_AccountingMonth, '%Y-%m' );

    -- -----------------------------------------------------------------------
    -- 11.直営未払金兼売掛金明細（月次）作成：加盟店未収金/未精算残
    -- -----------------------------------------------------------------------
    INSERT INTO AT_Cb_Accounts_PayableReceivable (
            DailyMonthlyFlg
        ,   ProcessingDate
        ,   AccountDate
        ,   EnterpriseId
        ,   EnterpriseNameKj
        ,   OrderSeq
        ,   OrderId
        ,   ManCustId
        ,   ManCusNameKj
        ,   OutOfAmends
        ,   DebtDefiniteConditions
        ,   DebtFixedDate
        ,   SettlementCosingDate
        ,   SettlementExpectedDate
        ,   AccountsPayablePending
        ,   UseAmount
        ,   SettlementFeeRate
        ,   SettlementFee
        ,   ClaimFee
        ,   MonthlyFee
        ,   IncludeMonthlyFee
        ,   ApiMonthlyFee
        ,   CreditNoticeMonthlyFee
        ,   NCreditNoticeMonthlyFee
        ,   ReserveMonthlyFee
        ,   AccountsReceivableTotal
        ,   AccountsDue
        ,   CarryOverAmount
        ,   InitiallyRemainAccountsPayable
        ,   InitiallyRemainAccountsReceivable
        ,   InitiallyRemainStampFee
        ,   InitiallyRemainAdjustmentAmount
        ,   InitiallyRemainRefund
        ,   AccountsDueFlg
        ,   RegistDate
        ,   RegistId
        ,   UpdateDate
        ,   UpdateId
        ,   ValidFlg )
    SELECT  1                                                                          -- 日次･月次区分 1：月次
        ,   v_BusinessDate                                                             -- 処理日付
        ,   v_AccountingMonth                                                          -- 会計月
        ,   pc.EnterpriseId                                                            -- 加盟店ID
        ,   e.EnterpriseNameKj                                                         -- 加盟店名
        ,   NULL                                                                       -- 注文Seq
        ,   NULL                                                                       -- 注文ID
        ,   NULL                                                                       -- 管理顧客番号 
        ,   NULL                                                                       -- 顧客名
        ,   NULL                                                                       -- 補償有無
        ,   CASE
                WHEN pc.DecisionPayment + pc.TransferCommission >= 0 THEN F_GetCode(158, 11, 1)
                ELSE F_GetCode(158, 10, 1)
            END                                                                        -- 債務確定条件
        ,   pc.DecisionDate                                                            -- 売上確定日
        ,   pc.FixedDate                                                               -- 立替締日
        ,   pc.ExecScheduleDate                                                        -- 立替予定日
        ,   CASE
                WHEN pc.DecisionPayment + pc.TransferCommission >= 0 THEN pc.DecisionPayment + pc.TransferCommission
                ELSE NULL
            END                                                                        -- 精算日到来未払金保留
        ,   NULL                                                                       -- 商品等代金合計(未払金)
        ,   NULL                                                                       -- 決済手数料率
        ,   NULL                                                                       -- 決済手数料
        ,   NULL                                                                       -- 請求手数料(税込)
        ,   NULL                                                                       -- 月額固定費(税込)
        ,   NULL                                                                       -- 同梱月額固定費
        ,   NULL                                                                       -- API月額固定費
        ,   NULL                                                                       -- 与信結果通知ｻｰﾋﾞｽ月額固定費
        ,   NULL                                                                       -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,   NULL                                                                       -- 月額固定費予備
        ,   NULL                                                                       -- 売掛金合計
        ,   CASE
                WHEN pc.DecisionPayment + pc.TransferCommission < 0 THEN pc.DecisionPayment + pc.TransferCommission
                ELSE NULL
            END                                                                        -- 現在残加盟店未収金
        ,   NULL                                                                       -- 振替時未精算繰越
        ,   NULL                                                                       -- 当初残(未払金)
        ,   NULL                                                                       -- 当初残(売掛金)
        ,   NULL                                                                       -- 当初残印紙代
        ,   NULL                                                                       -- 当初残精算調整額
        ,   NULL                                                                       -- 当初残返金
        ,   0                                                                          -- 現在残未収金消込ﾌﾗｸﾞ
        ,   NOW()                                                                      -- 登録日時
        ,   pi_user_id                                                                 -- 登録者
        ,   NOW()                                                                      -- 更新日時
        ,   pi_user_id                                                                 -- 更新者
        ,   1                                                                          -- 有効ﾌﾗｸﾞ 1：有効
    FROM    T_PayingControl pc
            INNER JOIN (SELECT EnterpriseId, MAX(ExecDate) AS ExecDate FROM T_PayingControl 
                WHERE   PayingControlStatus = 1
                AND     ValidFlg            = 1
                AND     DATE_FORMAT(ExecDate, '%Y-%m') = DATE_FORMAT(v_AccountingMonth, '%Y-%m')
            GROUP BY EnterpriseId) pc2 ON (pc2.EnterpriseId = pc.EnterpriseId AND pc2.ExecDate = pc.ExecDate)
            INNER JOIN T_Enterprise e ON (e.EnterpriseId = pc.EnterpriseId AND e.ServiceInDate IS NOT NULL)
            INNER JOIN M_Code mc ON (mc.CodeId = 160 AND mc.KeyCode = IFNULL(e.OemId, 0) AND mc.Class1 = 0)
    WHERE   pc.PayingControlStatus = 1
    AND     pc.ValidFlg            = 1
    AND     DATE_FORMAT(pc.ExecDate, '%Y-%m') = DATE_FORMAT(v_AccountingMonth, '%Y-%m')
    ;

END
$$

DELIMITER ;
