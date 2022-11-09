DROP PROCEDURE IF EXISTS P_Treas_BadDebt_List;

DELIMITER $$

CREATE PROCEDURE P_Treas_BadDebt_List( IN pi_user_id INT )
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名       ：  P_Treas_BadDebt_List
 *
 * 概要             ：  貸倒債権一覧
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
    SET v_BusinessDate = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'BusinessDate');        -- 業務日付
    SET v_AccountingMonth = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'AccountingMonth');  -- 会計月

    -- ------------------------------
    -- 1.注文の貸倒対象作成
    -- ------------------------------
    INSERT INTO AT_BadDebt_List(
        DailyMonthlyFlg
       ,ProcessingDate
       ,AccountDate
       ,CrediTarget
       ,CreditProcessingDate
       ,CrediAmount
       ,CrediAmountRece
       ,CrediAmountDue
       ,OrderSeq
       ,OrderId
       ,ManCustId
       ,ManCusNameKj
       ,OemId
       ,OemNameKj
       ,EnterpriseId
       ,EnterpriseNameKj
       ,OutOfAmends
       ,SalesDefiniteConditions
       ,SalesDefiniteDate
       ,OemTransferDate
       ,F_LimitDate
       ,F_ClaimAmount
       ,FinalReceiptDate
       ,AfterTheFinalPaymentDays
       ,OverdueClassification
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        2                                           -- 日次･月次区分
       ,v_BusinessDate                              -- 処理日付
       ,v_AccountingMonth                           -- 会計月
       ,c1.Class1                                   -- 貸倒対象
       ,o.Dmg_DecisionDate                          -- 貸倒処理日
       ,o.Dmg_DecisionAmount                        -- 貸倒金額
       ,0                                           -- 貸倒金額(売掛金)
       ,0                                           -- 貸倒金額(未収金)
       ,o.OrderSeq                                  -- 注文Seq
       ,o.OrderId                                   -- 注文ID
       ,mc.ManCustId                                -- 管理顧客番号
       ,mc.NameKj                                   -- 顧客名
       ,c2.Class3                                   -- OEMID
       ,c2.Class2                                   -- OEM先名
       ,o.EnterpriseId                              -- 加盟店ID
       ,e.EnterpriseNameKj                          -- 加盟店名
       ,c3.Class1                                   -- 補償有無
       ,F_GetSalesDefiniteConditions(o.OrderSeq)    -- 売上確定条件
       ,DATE(apas.ATUriDay)                         -- 売上確定日付
       ,o.OemClaimTransDate                         -- OEM移管日
       ,cc.F_LimitDate                              -- 支払期日
       ,cc.F_ClaimAmount                            -- 初期債権金額
       ,rc.ReceiptDate                              -- 最終入金日
       ,DATEDIFF(v_BusinessDate, rc.ReceiptDate)    -- 最終入金後経過日数
       ,c5.Class3                                   -- 延滞区分
       ,NOW()                                       -- 登録日時
       ,pi_user_id                                  -- 登録者
       ,NOW()                                       -- 更新日時
       ,pi_user_id                                  -- 更新者
       ,1                                           -- 有効ﾌﾗｸﾞ
    FROM  T_Order o
    INNER JOIN AT_Order ao
    ON    o.OrderSeq        = ao.OrderSeq
    INNER JOIN T_ClaimControl cc
    ON    o.OrderSeq      = cc.OrderSeq
    LEFT  JOIN T_ReceiptControl rc
    ON    cc.LastReceiptSeq = rc.ReceiptSeq
    AND   rc.ValidFlg       = 1
    LEFT  JOIN T_Enterprise e
    ON    o.EnterpriseId    = e.EnterpriseId
    INNER JOIN T_Customer c
    ON    o.OrderSeq        = c.OrderSeq
    LEFT  JOIN T_EnterpriseCustomer ec
    ON    c.EntCustSeq      = ec.EntCustSeq
    LEFT  JOIN T_ManagementCustomer mc
    ON    ec.ManCustId      = mc.ManCustId
    INNER JOIN T_PayingAndSales ps
    ON    o.OrderSeq        = ps.OrderSeq
    INNER JOIN AT_PayingAndSales apas ON (apas.Seq = ps.Seq)
    LEFT  JOIN M_Code c1
    ON    c1.CodeId         = 164
    AND   c1.KeyCode        = 1
    INNER JOIN M_Code c2
    ON    c2.CodeId         = 160
    AND   c2.KeyCode        = IFNULL(e.OemId, 0)
    LEFT  JOIN M_Code c3
    ON    c3.CodeId         = 159
    AND   c3.KeyCode        = IFNULL(o.OutOfAmends, 0)
    LEFT  JOIN M_Code c5
    ON    c5.CodeId         = 12
    AND   c5.KeyCode        = cc.ClaimPattern
    WHERE
          o.Dmg_DecisionDate    IS NOT NULL
    AND   o.Dmg_DecisionFlg      = 1
    AND   ao.Dmg_DailySummaryFlg = 0
    AND   o.ValidFlg             = 1
    AND   o.Dmg_DecisionDate <= v_BusinessDate
    ;

    -- ------------------------------
    -- 2.加盟店入金履歴の貸倒対象作成
    -- ------------------------------
    INSERT INTO AT_BadDebt_List(
        DailyMonthlyFlg
       ,ProcessingDate
       ,AccountDate
       ,CrediTarget
       ,CreditProcessingDate
       ,CrediAmount
       ,CrediAmountRece
       ,CrediAmountDue
       ,OrderSeq
       ,OrderId
       ,ManCustId
       ,ManCusNameKj
       ,OemId
       ,OemNameKj
       ,EnterpriseId
       ,EnterpriseNameKj
       ,OutOfAmends
       ,SalesDefiniteConditions
       ,SalesDefiniteDate
       ,OemTransferDate
       ,F_LimitDate
       ,F_ClaimAmount
       ,FinalReceiptDate
       ,AfterTheFinalPaymentDays
       ,OverdueClassification
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        2                                       -- 日次･月次区分
       ,v_BusinessDate                          -- 処理日付
       ,v_AccountingMonth                       -- 会計月
       ,c2.Class1                               -- 貸倒対象
       ,erh.ReceiptDate                         -- 貸倒処理日
       ,erh.ReceiptAmount                       -- 貸倒金額
       ,0                                       -- 貸倒金額(売掛金)
       ,0                                       -- 貸倒金額(未収金)
       ,NULL                                    -- 注文Seq
       ,NULL                                    -- 注文ID
       ,NULL                                    -- 管理顧客番号
       ,NULL                                    -- 顧客名
       ,c3.Class3                               -- OEMID
       ,c3.Class2                               -- OEM先名
       ,erh.EnterpriseId                        -- 加盟店ID
       ,e.EnterpriseNameKj                      -- 加盟店名
       ,NULL                                    -- 補償有無
       ,NULL                                    -- 売上確定条件
       ,NULL                                    -- 売上確定日付
       ,NULL                                    -- OEM移管日
       ,NULL                                    -- 支払期日
       ,NULL                                    -- 初期債権金額
       ,NULL                                    -- 最終入金日
       ,NULL                                    -- 最終入金後経過日数
       ,NULL                                    -- 延滞区分
       ,NOW()                                   -- 登録日時
       ,pi_user_id                              -- 登録者
       ,NOW()                                   -- 更新日時
       ,pi_user_id                              -- 更新者
       ,1                                       -- 有効ﾌﾗｸﾞ
    FROM  T_EnterpriseReceiptHistory erh
    LEFT  JOIN AT_EnterpriseReceiptHistory aerh
    ON    erh.EntRcptSeq        = aerh.EntRcptSeq
    LEFT  JOIN T_Enterprise e
    ON    erh.EnterpriseId      = e.EnterpriseId
    INNER JOIN M_Code c1
    ON    c1.CodeId             = 95
    AND   c1.KeyCode            = erh.ReceiptClass
    AND   c1.Class1             = 1
    LEFT JOIN M_Code c2
    ON    c2.CodeId             = 164
    AND   c2.KeyCode            = 2
    INNER JOIN M_Code c3
    ON    c3.CodeId             = 160
    AND   c3.KeyCode            = IFNULL(e.OemId, 0)
    WHERE
          aerh.DailySummaryFlg  = 0
    AND   erh.ValidFlg          = 1
    AND   DATE_FORMAT( erh.ReceiptProcessDate, '%Y-%m-%d' ) <= v_BusinessDate
    ;

END
$$

DELIMITER ;
