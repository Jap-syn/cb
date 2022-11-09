DROP PROCEDURE IF EXISTS P_Treas_Oem_Accounts_PayableReceivable;

DELIMITER $$

CREATE PROCEDURE P_Treas_Oem_Accounts_PayableReceivable ( IN pi_user_id INT )
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名       ：  P_Treas_Oem_Accounts_PayableReceivable
 *
 * 概要             ：  OEM未払金兼売掛金明細
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

    -- ------------------------------
    -- 1.月額固定費
    -- ------------------------------
    INSERT INTO AT_Oem_Accounts_PayableReceivable (
         DailyMonthlyFlg
        ,ProcessingDate
        ,AccountDate
        ,OemId
        ,OemNameKj
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
        ,LAST_DAY(v_AccountingMonth)                                                -- 処理日付
        ,v_AccountingMonth                                                          -- 会計月
        ,c1.Class3                                                                  -- OEMID
        ,c1.Class2                                                                  -- OEM先名
        ,emci.EnterpriseId                                                          -- 加盟店ID
        ,e.EnterpriseNameKj                                                         -- 加盟店名
        ,NULL                                                                       -- 注文Seq
        ,NULL                                                                       -- 注文ID
        ,NULL                                                                       -- 管理顧客番号 
        ,NULL                                                                       -- 顧客名
        ,NULL                                                                       -- 補償有無
        ,c2.Class1                                                                  -- 債務確定条件
        ,v_BusinessDate                                                             -- 債務確定日
        ,NULL                                                                       -- OEM精算締日
        ,NULL                                                                       -- OEM精算予定日
        ,NULL                                                                       -- 精算日到来未払金保留
        ,NULL                                                                       -- 商品等代金合計(未払金)
        ,NULL                                                                       -- OEM決済手数料率(税込)
        ,NULL                                                                       -- OEM決済手数料(税込)
        ,NULL                                                                       -- OEM請求手数料(税込)
        ,IFNULL( emci.OemMonthlyFee, 0 ) +
            IFNULL( emci.OemMonthlyFeeTax, 0 )                                      -- OEM月額固定費(税込)
        ,IFNULL( emci.OemIncludeMonthlyFee, 0 ) +
            IFNULL( emci.OemIncludeMonthlyFeeTax, 0 )                               -- OEM同梱月額固定費
        ,IFNULL( emci.OemApiMonthlyFee, 0 ) +
            IFNULL( emci.OemApiMonthlyFeeTax, 0 )                                   -- OEMAPI月額固定費
        ,IFNULL( emci.OemCreditNoticeMonthlyFee, 0 ) +
            IFNULL( emci.OemCreditNoticeMonthlyFeeTax, 0 )                          -- OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,IFNULL( emci.OemNCreditNoticeMonthlyFee, 0 ) +
            IFNULL( emci.OemNCreditNoticeMonthlyFeeTax, 0 )                         -- OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,IFNULL( emci.OemReserveMonthlyFee, 0 ) +
            IFNULL( emci.OemReserveMonthlyFeeTax, 0 )                               -- OEM月額固定費予備
        ,IFNULL( emci.OemMonthlyFee, 0 ) +
            IFNULL( emci.OemMonthlyFeeTax, 0 ) +
            IFNULL( emci.OemIncludeMonthlyFee, 0 ) +
            IFNULL( emci.OemIncludeMonthlyFeeTax, 0 ) +
            IFNULL( emci.OemApiMonthlyFee, 0 ) +
            IFNULL( emci.OemApiMonthlyFeeTax, 0 ) +
            IFNULL( emci.OemCreditNoticeMonthlyFee, 0 ) +
            IFNULL( emci.OemCreditNoticeMonthlyFeeTax, 0 ) +
            IFNULL( emci.OemNCreditNoticeMonthlyFee, 0 ) +
            IFNULL( emci.OemNCreditNoticeMonthlyFeeTax, 0 ) +
            IFNULL( emci.OemReserveMonthlyFee, 0 ) +
            IFNULL( emci.OemReserveMonthlyFeeTax, 0 )                               -- 売掛金合計
        ,NULL                                                                       -- 現在残OEM未収金
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
    ON      c1.CodeId         = 160
    AND     c1.KeyCode        = IFNULL( emci.OemId, 0 )
    AND     c1.Class1         <> 0
    LEFT JOIN   M_Code c2
    ON      c2.CodeId         = 158
    AND     c2.KeyCode        = 8
    WHERE emci.ValidFlg                   = 1
    AND   emci.ClosingMonthly             = v_AccountingMonth
    AND   IFNULL( emci.OemMonthlyFee, 0 ) +
          IFNULL( emci.OemMonthlyFeeTax, 0 ) +
          IFNULL( emci.OemIncludeMonthlyFee, 0 ) +
          IFNULL( emci.OemIncludeMonthlyFeeTax, 0 ) +
          IFNULL( emci.OemApiMonthlyFee, 0 ) +
          IFNULL( emci.OemApiMonthlyFeeTax, 0 ) +
          IFNULL( emci.OemCreditNoticeMonthlyFee, 0 ) +
          IFNULL( emci.OemCreditNoticeMonthlyFeeTax, 0 ) +
          IFNULL( emci.OemNCreditNoticeMonthlyFee, 0 ) +
          IFNULL( emci.OemNCreditNoticeMonthlyFeeTax, 0 ) +
          IFNULL( emci.OemReserveMonthlyFee, 0 ) +
          IFNULL( emci.OemReserveMonthlyFeeTax, 0 ) > 0;

    -- ---------------------------------------------------------
    -- 月末までに売上済かつ、OEM精算予定日が会計月後
    -- ---------------------------------------------------------
    INSERT INTO AT_Oem_Accounts_PayableReceivable (
         DailyMonthlyFlg
        ,ProcessingDate
        ,AccountDate
        ,OemId
        ,OemNameKj
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
        ,c1.Class3                                                                  -- OEMID
        ,c1.Class2                                                                  -- OEM先名
        ,o.EnterpriseId                                                             -- 加盟店ID
        ,e.EnterpriseNameKj                                                         -- 加盟店名
        ,o.OrderSeq                                                                 -- 注文Seq
        ,o.OrderId                                                                  -- 注文ID
        ,mc.ManCustId                                                               -- 管理顧客番号 
        ,mc.NameKj                                                                  -- 顧客名
        ,c2.Class1                                                                  -- 補償有無
        ,F_GetSalesDefiniteConditions(o.OrderSeq)                                   -- 債務確定条件
        ,DATE(apas.ATUriDay)                                                        -- 債務確定日
        ,oc.SpanTo                                                                  -- OEM精算締日
        ,oc.SettlePlanDate                                                          -- OEM精算予定日
        ,NULL                                                                       -- 精算日到来未払金保留
        ,pas.UseAmount                                                              -- 商品等代金合計(未払金)
        ,osf.AppSettlementFeeRate                                                   -- OEM決済手数料率(税込)
        ,IFNULL( osf.SettlementFee, 0 )                                             -- OEM決済手数料(税込)
        ,IFNULL( ocf.ClaimFee, 0 )                                                  -- OEM請求手数料(税込)
        ,NULL                                                                       -- OEM月額固定費(税込)
        ,NULL                                                                       -- OEM同梱月額固定費
        ,NULL                                                                       -- OEMAPI月額固定費
        ,NULL                                                                       -- OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,NULL                                                                       -- OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,NULL                                                                       -- OEM月額固定費予備
        ,IFNULL( osf.SettlementFee, 0 ) + IFNULL( ocf.ClaimFee, 0 )                 -- 売掛金合計
        ,NULL                                                                       -- 現在残OEM未収金
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
    FROM        T_PayingAndSales pas
    INNER JOIN AT_PayingAndSales apas
    ON      pas.Seq = apas.Seq
    LEFT JOIN  T_PayingControl pc
    ON      pc.Seq              = pas.PayingControlSeq
    LEFT JOIN  T_OemClaimed oc
    ON      oc.OemClaimedSeq    = pc.OemClaimedSeq
    INNER JOIN  T_Order o
    ON      o.OrderSeq          = pas.OrderSeq
    INNER JOIN  T_Enterprise e
    ON      e.EnterpriseId      = o.EnterpriseId
    AND     e.ServiceInDate IS NOT NULL
    INNER JOIN  T_Customer c
    ON      c.OrderSeq          = o.OrderSeq
    LEFT JOIN   T_EnterpriseCustomer ec
    ON      ec.EntCustSeq       = c.EntCustSeq
    LEFT JOIN   T_ManagementCustomer mc
    ON      mc.ManCustId        = ec.ManCustId
    INNER JOIN  T_OemClaimFee ocf
    ON      ocf.OrderSeq        = o.OrderSeq
    INNER JOIN  T_OemSettlementFee osf
    ON      osf.OrderSeq        = o.OrderSeq
    INNER JOIN  M_Code c1
    ON      c1.CodeId           = 160
    AND     c1.KeyCode          = IFNULL( o.OemId, 0 )
    AND     c1.Class1           <> 0
    LEFT JOIN   M_Code c2
    ON      c2.CodeId           = 159
    AND     c2.KeyCode          = IFNULL( o.OutOfAmends, 0)
    LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
    LEFT OUTER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq)
    LEFT OUTER JOIN T_Cancel ca ON (ca.OrderSeq = o.OrderSeq AND ca.ValidFlg = 1)
    WHERE apas.ATUriDay <= DATE_FORMAT( LAST_DAY(v_AccountingMonth), '%Y%m%d' )
    AND   IFNULL(oc.ExecDate, '2100-01-01') > LAST_DAY( v_AccountingMonth )
    AND   DATE_FORMAT( IFNULL( ca.ApprovalDate , '2100-01-01' ), '%Y-%m' ) > DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
    ;

    -- ---------------------------------------------------
    -- 月末までにｷｬﾝｾﾙ済かつ、OEM精算予定日が会計月後
    -- ---------------------------------------------------
    INSERT INTO AT_Oem_Accounts_PayableReceivable (
         DailyMonthlyFlg
        ,ProcessingDate
        ,AccountDate
        ,OemId
        ,OemNameKj
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
        ,c1.Class3                                                                  -- OEMID
        ,c1.Class2                                                                  -- OEM先名
        ,o.EnterpriseId                                                             -- 加盟店ID
        ,e.EnterpriseNameKj                                                         -- 加盟店名
        ,o.OrderSeq                                                                 -- 注文Seq
        ,o.OrderId                                                                  -- 注文ID
        ,mc.ManCustId                                                               -- 管理顧客番号 
        ,mc.NameKj                                                                  -- 顧客名
        ,c2.Class1                                                                  -- 補償有無
        ,c3.Class1                                                                  -- 債務確定条件
        ,ca.ApprovalDate                                                            -- 債務確定日
        ,oc.SpanTo                                                                  -- OEM精算締日
        ,oc.SettlePlanDate                                                          -- OEM精算予定日
        ,NULL                                                                       -- 精算日到来未払金保留
        ,pas.UseAmount * -1                                                         -- 商品等代金合計(未払金)
        ,osf.AppSettlementFeeRate                                                   -- OEM決済手数料率(税込)
        ,IFNULL( osf.SettlementFee * -1, 0 )                                        -- OEM決済手数料(税込)
        ,IFNULL( ocf.ClaimFee * -1, 0 )                                             -- OEM請求手数料(税込)
        ,NULL                                                                       -- OEM月額固定費(税込)
        ,NULL                                                                       -- OEM同梱月額固定費
        ,NULL                                                                       -- OEMAPI月額固定費
        ,NULL                                                                       -- OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,NULL                                                                       -- OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,NULL                                                                       -- OEM月額固定費予備
        ,( IFNULL( osf.SettlementFee, 0 ) + IFNULL( ocf.ClaimFee, 0 ) ) * -1        -- 売掛金合計
        ,NULL                                                                       -- 現在残OEM未収金
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
    FROM        T_Cancel ca
    LEFT JOIN  T_PayingControl pc
    ON      pc.Seq              = ca.PayingControlSeq
    LEFT JOIN  T_OemClaimed oc
    ON      oc.OemClaimedSeq    = pc.OemClaimedSeq
    INNER JOIN  T_PayingAndSales pas
    ON      ca.OrderSeq         = pas.OrderSeq
    INNER JOIN  T_Order o
    ON      o.OrderSeq          = ca.OrderSeq
    INNER JOIN  T_Enterprise e
    ON      e.EnterpriseId      = o.EnterpriseId
    AND     e.ServiceInDate IS NOT NULL
    INNER JOIN  T_Customer c
    ON      c.OrderSeq          = o.OrderSeq
    LEFT JOIN   T_EnterpriseCustomer ec
    ON      ec.EntCustSeq       = c.EntCustSeq
    LEFT JOIN   T_ManagementCustomer mc
    ON      mc.ManCustId        = ec.ManCustId
    INNER JOIN  T_OemClaimFee ocf
    ON      ocf.OrderSeq        = ca.OrderSeq
    INNER JOIN  T_OemSettlementFee osf
    ON      osf.OrderSeq        = ca.OrderSeq
    INNER JOIN  M_Code c1
    ON      c1.CodeId           = 160
    AND     c1.KeyCode          = IFNULL( o.OemId, 0 )
    AND     c1.Class1           <> 0
    LEFT JOIN   M_Code c2
    ON      c2.CodeId           = 159
    AND     c2.KeyCode          = IFNULL( o.OutOfAmends, 0 )
    LEFT JOIN   M_Code c3
    ON      c3.CodeId           = 158
    AND     c3.KeyCode          = 6
    LEFT JOIN   T_PayingControl pc2
    ON      pc2.Seq = pas.PayingControlSeq
    LEFT JOIN   T_OemClaimed oc2
    ON      oc2.OemClaimedSeq = pc2.OemClaimedSeq
    WHERE   ca.ValidFlg = 1
    AND     ca.CancelPhase IN ( 2, 3 )     -- 立替済ｷｬﾝｾﾙのみ対象
    AND     DATE_FORMAT( ca.ApprovalDate, '%Y-%m' ) <= DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
    AND     IFNULL(oc.ExecDate, '2100-01-01') > LAST_DAY( v_AccountingMonth )
    AND     IFNULL(oc2.ExecDate, '2100-01-01') <= LAST_DAY( v_AccountingMonth )
    ;
    -- -------------------------------------
    -- 6. 無保証立替金戻し確定日
    -- -------------------------------------
    INSERT INTO AT_Oem_Accounts_PayableReceivable (
         DailyMonthlyFlg
        ,ProcessingDate
        ,AccountDate
        ,OemId
        ,OemNameKj
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
        ,c1.Class3                                                                  -- OEMID
        ,c1.Class2                                                                  -- OEM先名
        ,o.EnterpriseId                                                             -- 加盟店ID
        ,e.EnterpriseNameKj                                                         -- 加盟店名
        ,o.OrderSeq                                                                 -- 注文Seq
        ,o.OrderId                                                                  -- 注文ID
        ,mc.ManCustId                                                               -- 管理顧客番号 
        ,mc.NameKj                                                                  -- 顧客名
        ,c2.Class1                                                                  -- 補償有無
        ,c3.Class1                                                                  -- 債務確定条件
        ,pbc.PayDecisionDate                                                        -- 債務確定日
        ,oc.SpanTo                                                                  -- OEM精算締日
        ,oc.SettlePlanDate                                                          -- OEM精算予定日
        ,NULL                                                                       -- 精算日到来未払金保留
        ,pbc.PayBackAmount                                                          -- 商品等代金合計(未払金)
        ,NULL                                                                       -- OEM決済手数料率(税込)
        ,NULL                                                                       -- OEM決済手数料(税込)
        ,NULL                                                                       -- OEM請求手数料(税込)
        ,NULL                                                                       -- OEM月額固定費(税込)
        ,NULL                                                                       -- OEM同梱月額固定費
        ,NULL                                                                       -- OEMAPI月額固定費
        ,NULL                                                                       -- OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,NULL                                                                       -- OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,NULL                                                                       -- OEM月額固定費予備
        ,NULL                                                                       -- 売掛金合計
        ,NULL                                                                       -- 現在残OEM未収金
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
    FROM        T_OemClaimed oc
    INNER JOIN  T_PayingControl pc
    ON      pc.OemClaimedSeq    = oc.OemClaimedSeq
    AND     pc.ValidFlg         = 1
    INNER JOIN  T_PayingBackControl pbc
    ON      pbc.PayingControlSeq = pc.Seq
    AND     pbc.ValidFlg        = 1
    INNER JOIN  T_PayingAndSales pas
    ON      pas.OrderSeq        = pbc.OrderSeq
    INNER JOIN  T_Order o
    ON      o.OrderSeq          = pbc.OrderSeq
    INNER JOIN  T_Enterprise e
    ON      e.EnterpriseId      = o.EnterpriseId
    AND     e.ServiceInDate IS NOT NULL
    INNER JOIN  T_Customer c
    ON      c.OrderSeq          = o.OrderSeq
    LEFT JOIN   T_EnterpriseCustomer ec
    ON      ec.EntCustSeq       = c.EntCustSeq
    LEFT JOIN   T_ManagementCustomer mc
    ON      mc.ManCustId        = ec.ManCustId
    INNER JOIN  M_Code c1
    ON      c1.CodeId           = 160
    AND     c1.KeyCode          = IFNULL( o.OemId, 0 )
    AND     c1.Class1           <> 0
    LEFT JOIN   M_Code c2
    ON      c2.CodeId           = 159
    AND     c2.KeyCode          = IFNULL( o.OutOfAmends, 0 )
    LEFT JOIN   M_Code c3
    ON      c3.CodeId           = 158
    AND     c3.KeyCode          = 7
    WHERE   oc.ValidFlg = 1
    AND     DATE_FORMAT( oc.ExecDate, '%Y-%m' ) > DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
    AND     oc.PayingControlStatus = 1;

END
$$

DELIMITER ;
