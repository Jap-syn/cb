DROP PROCEDURE IF EXISTS P_Treas_AdvancesSettlementBack;

DELIMITER $$

CREATE PROCEDURE P_Treas_AdvancesSettlementBack ( IN pi_user_id INT )
proc:
/******************************************************************************
 *
 * プロシージャ名   ：  P_Treas_AdvancesSettlementBack
 *
 * 概要             ：  無保証立替金戻し明細
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

    -- ------------------------------
    -- 変数初期化
    -- ------------------------------
    SET v_BusinessDate      = F_GetSystemProperty( '[DEFAULT]', 'systeminfo', 'BusinessDate' );     -- 業務日付
    SET v_AccountingMonth   = F_GetSystemProperty( '[DEFAULT]', 'systeminfo', 'AccountingMonth' );  -- 会計月

    INSERT INTO AT_AdvancesSettlementBack (
         DailyMonthlyFlg                                                        -- 日次・月次区分
        ,ProcessingDate                                                         -- 処理日付
        ,AccountDate                                                            -- 会計月
        ,OemId                                                                  -- OEMID
        ,OemNameKj                                                              -- OEM先名
        ,SettlementBackDate                                                     -- 無保証立替金戻し確定日
        ,SettlementBackOffsetDate                                               -- 無保証立替金戻し精算日
        ,EnterpriseId                                                           -- 加盟店ID
        ,EnterpriseNameKj                                                       -- 加盟店名
        ,OrderSeq                                                               -- 注文Seq
        ,OrderId                                                                -- 注文ID
        ,SalesDefiniteConditions                                                -- 売上確定条件
        ,SalesDefiniteDate                                                      -- 売上確定日付
        ,FirstBillingDate                                                       -- 初回請求日
        ,FirstCaimAfterTheNumberOfDays                                          -- 初回請求後経過日数
        ,JournalNumber                                                          -- 伝票番号
        ,ManCustId                                                              -- 管理顧客番号
        ,ManCusNameKj                                                           -- 顧客名
        ,ClaimAmount                                                            -- 未収金残高
        ,F_ClaimAmount                                                          -- 初期債権金額
        ,RegistDate                                                             -- 登録日時
        ,RegistId                                                               -- 登録者
        ,UpdateDate                                                             -- 更新日時
        ,UpdateId                                                               -- 更新者
        ,ValidFlg )                                                             -- 有効フラグ
    SELECT
         2                                                                      -- 日次・月次区分
        ,v_BusinessDate                                                         -- 業務日付
        ,( CASE
            WHEN DATE_FORMAT( pbc.PayDecisionDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' ) THEN v_AccountingMonth
            ELSE DATE_FORMAT( pbc.PayDecisionDate, '%Y-%m-01' )
         END )                                                                  -- 会計月
        ,c4.Class1                                                              -- 会計OEMID
        ,c4.Class2                                                              -- 会計OEM名称
        ,pbc.PayDecisionDate                                                    -- 立替確定日
        ,( CASE
            WHEN c4.Class1 <> 0 THEN oc.SettlePlanDate
            ELSE pc.ExecScheduleDate
         END )                                                                  -- 精算予定日・立替実行予定日
        ,o.EnterpriseId                                                         -- 加盟店ID
        ,e.EnterpriseNameKj                                                     -- 加盟店名
        ,o.OrderSeq                                                             -- 注文Seq
        ,o.OrderId                                                              -- 注文ID
        ,F_GetSalesDefiniteConditions(o.ServiceTargetClass, o.Deli_ConfirmArrivalDate, cc.LastProcessDate)    -- 売上確定条件                                                                 -- 売上確定条件
        ,DATE(apas.ATUriDay)                                                    -- 立替条件クリア日
        ,cc.F_ClaimDate                                                         -- 初回－請求日
        ,DATEDIFF( v_BusinessDate, cc.F_ClaimDate )                             -- 業務日付 - 請求管理．初回－請求日
        ,os.Deli_JournalNumber                                                  -- 配送－伝票番号
        ,mc.ManCustId                                                           -- 管理顧客番号
        ,mc.NameKj                                                              -- 顧客名
        ,cc.BalanceUseAmount                                                    -- 残高情報－利用額
        ,cc.F_ClaimAmount                                                       -- 初回請求金額
        ,NOW()                                                                  -- システム日時
        ,pi_user_id                                                             -- ﾕｰｻﾞｰID
        ,NOW()                                                                  -- システム日時
        ,pi_user_id                                                             -- ﾕｰｻﾞｰID
        ,1                                                                      -- 有効フラグ：有効
    FROM        T_PayingControl pc
    INNER JOIN  AT_PayingControl at_pc
    ON      pc.Seq                  = at_pc.Seq
    INNER JOIN  T_PayingBackControl pbc
    ON      pbc.PayingControlSeq    = pc.Seq
    AND     pbc.ValidFlg            = 1
    INNER JOIN  T_Order o
    ON      o.OrderSeq              = pbc.OrderSeq
    INNER JOIN  T_Customer c
    ON      c.OrderSeq              = o.OrderSeq
    INNER JOIN  T_PayingAndSales pas 
    ON      pas.OrderSeq            = o.OrderSeq
    INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
    INNER JOIN  T_ClaimControl cc 
    ON      cc.OrderSeq             = o.OrderSeq
    LEFT JOIN   T_Enterprise e
    ON      o.EnterpriseId          = e.EnterpriseId
    LEFT JOIN   T_EnterpriseCustomer ec
    ON      ec.EntCustSeq           = c.EntCustSeq
    LEFT JOIN   T_ManagementCustomer mc 
    ON      mc.ManCustId            = ec.ManCustId
    LEFT JOIN   T_OrderSummary os
    ON      os.OrderSeq             = o.OrderSeq
    LEFT JOIN   T_OemClaimed oc
    ON      oc.OemClaimedSeq        = pc.OemClaimedSeq
    INNER JOIN  M_Code c4
    ON      c4.CodeId               = 160
    AND     c4.KeyCode              = IFNULL( o.OemId, 0 )
    WHERE   pc.PayingControlStatus  = 1
    AND     at_pc.DailySummaryFlg   = 0
    AND     pc.ValidFlg = 1;

END
