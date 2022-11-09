DROP PROCEDURE IF EXISTS P_Treas_Excess_List_Day;

DELIMITER $$

CREATE PROCEDURE P_Treas_Excess_List_Day ( IN pi_user_id INT )
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名       ：  P_Treas_Excess_List_Day
 *
 * 概要             ：  過剰金一覧（日次）
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
    DECLARE v_BusinessDate  DATE;                   -- 業務日付

    -- ------------------------------
    -- 変数初期化
    -- ------------------------------
    SET v_BusinessDate  =   F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'BusinessDate');       -- 業務日付

    -- ------------------------------
    -- 過剰金一覧（日次）
    -- ------------------------------
    -- CB直営 & OEM
    -- ①入金ｸﾛｰｽﾞしている過剰金
    INSERT
    INTO    AT_Excess_List( DailyMonthlyFlg                                                        -- 日次･月次区分
                        ,   ProcessingDate                                                         -- 処理日付
                        ,   AccountDate                                                            -- 会計月
                        ,   OemId                                                                  -- OEMID
                        ,   OemNameKj                                                              -- OEM先名
                        ,   ManCustId                                                              -- 管理顧客番号
                        ,   ManCusNameKj                                                           -- 顧客名
                        ,   ExcessAmount                                                           -- 過剰金額
                        ,   OrderSeq                                                               -- 注文Seq
                        ,   OrderId                                                                -- 注文ID
                        ,   OutOfAmends                                                            -- 補償有無
                        ,   SalesDefiniteConditions                                                -- 売上確定条件
                        ,   SalesDefiniteDate                                                      -- 売上確定日付
                        ,   OemTransferDate                                                        -- OEM移管日
                        ,   SettlementBackDate                                                     -- 無保証立替金戻し実施日
                        ,   EnterpriseId                                                           -- 加盟店ID
                        ,   EnterpriseNameKj                                                       -- 加盟店名
                        ,   F_LimitDate                                                            -- 支払期日
                        ,   F_ClaimAmount                                                          -- 初期債権金額
                        ,   FinalReceiptDate                                                       -- 最最終入金日
                        ,   AfterTheFinalPaymentDays                                               -- 最終入金後経過日数
                        ,   OverdueClassification                                                  -- 延滞区分
                        ,   RegistDate                                                             -- 登録日時
                        ,   RegistId                                                               -- 登録者
                        ,   UpdateDate                                                             -- 更新日時
                        ,   UpdateId                                                               -- 更新者
                        ,   ValidFlg                                                               -- 有効ﾌﾗｸﾞ
                          )
                    SELECT  0                                                                      -- 日次･月次区分
                        ,   v_BusinessDate                                                         -- 処理日付
                        ,   NULL                                                                   -- 会計月
                        ,   c6.Class3                                                              -- OEMID
                        ,   c6.Class2                                                              -- OEM先名
                        ,   mc.ManCustId                                                           -- 管理顧客番号
                        ,   mc.NameKj                                                              -- 顧客名
                        ,   cc.BalanceClaimAmount * -1                                             -- 過剰金額
                        ,   o.OrderSeq                                                             -- 注文Seq
                        ,   o.OrderId                                                              -- 注文ID
                        ,   c5.Class1                                                              -- 補償有無
                        ,   F_GetSalesDefiniteConditions(o.OrderSeq)                               -- 売上確定条件
                        ,   DATE(apas.ATUriDay)                                                    -- 売上確定日付
                        ,   o.OemClaimTransDate                                                    -- OEM移管日
                        ,   NULL                                                                   -- 無保証立替金戻し実施日
                        ,   o.EnterpriseId                                                         -- 加盟店ID
                        ,   e.EnterpriseNameKj                                                     -- 加盟店名
                        ,   cc.F_LimitDate                                                         -- 支払期日
                        ,   cc.F_ClaimAmount                                                       -- 初期債権金額
                        ,   rc.ReceiptDate                                                         -- 最最終入金日
                        ,   DATEDIFF( v_BusinessDate, rc.ReceiptDate )                             -- 最終入金後経過日数
                        ,   c1.Class3                                                              -- 延滞区分
                        ,   NOW()                                                                  -- 登録日時
                        ,   pi_user_id                                                             -- 登録者
                        ,   NOW()                                                                  -- 更新日時
                        ,   pi_user_id                                                             -- 更新者
                        ,   1                                                                      -- 有効ﾌﾗｸﾞ
                    FROM    T_ClaimControl cc
                            INNER JOIN T_Order o ON (o.OrderSeq = cc.OrderSeq)
                            INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
                            INNER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
                            INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
                            INNER JOIN T_Enterprise e ON (o.EnterpriseId = e.EnterpriseId)
                            INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
                            INNER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
                            INNER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq AND rc.ValidFlg = 1)
                            INNER JOIN M_Code c1 ON (c1.CodeId = 12 AND c1.KeyCode = cc.ClaimPattern)
                            INNER JOIN M_Code c5 ON (c5.CodeId = 159 AND c5.KeyCode = IFNULL(o.OutOfAmends,0))
                            INNER JOIN M_Code c6 ON (c6.CodeId = 160 AND c6.KeyCode = IFNULL(o.OemId, 0))
                            LEFT OUTER JOIN T_Cancel can ON (can.OrderSeq = o.OrderSeq)
                    WHERE   cc.BalanceClaimAmount < 0
                    AND     cc.ValidFlg = 1
                    AND     o.ValidFlg = 1
                    AND     can.ApprovalDate IS NULL
                    ;

    -- ③着荷前の一部入金
    INSERT
    INTO    AT_Excess_List( DailyMonthlyFlg                                                        -- 日次･月次区分
                        ,   ProcessingDate                                                         -- 処理日付
                        ,   AccountDate                                                            -- 会計月
                        ,   OemId                                                                  -- OEMID
                        ,   OemNameKj                                                              -- OEM先名
                        ,   ManCustId                                                              -- 管理顧客番号
                        ,   ManCusNameKj                                                           -- 顧客名
                        ,   ExcessAmount                                                           -- 過剰金額
                        ,   OrderSeq                                                               -- 注文Seq
                        ,   OrderId                                                                -- 注文ID
                        ,   OutOfAmends                                                            -- 補償有無
                        ,   SalesDefiniteConditions                                                -- 売上確定条件
                        ,   SalesDefiniteDate                                                      -- 売上確定日付
                        ,   OemTransferDate                                                        -- OEM移管日
                        ,   SettlementBackDate                                                     -- 無保証立替金戻し実施日
                        ,   EnterpriseId                                                           -- 加盟店ID
                        ,   EnterpriseNameKj                                                       -- 加盟店名
                        ,   F_LimitDate                                                            -- 支払期日
                        ,   F_ClaimAmount                                                          -- 初期債権金額
                        ,   FinalReceiptDate                                                       -- 最最終入金日
                        ,   AfterTheFinalPaymentDays                                               -- 最終入金後経過日数
                        ,   OverdueClassification                                                  -- 延滞区分
                        ,   RegistDate                                                             -- 登録日時
                        ,   RegistId                                                               -- 登録者
                        ,   UpdateDate                                                             -- 更新日時
                        ,   UpdateId                                                               -- 更新者
                        ,   ValidFlg                                                               -- 有効ﾌﾗｸﾞ
                          )
                    SELECT  0                                                                      -- 日次･月次区分
                        ,   v_BusinessDate                                                         -- 処理日付
                        ,   NULL                                                                   -- 会計月
                        ,   c6.Class3                                                              -- OEMID
                        ,   c6.Class2                                                              -- OEM先名
                        ,   mc.ManCustId                                                           -- 管理顧客番号
                        ,   mc.NameKj                                                              -- 顧客名
                        ,   cc.ReceiptAmountTotal                                                  -- 過剰金額
                        ,   o.OrderSeq                                                             -- 注文Seq
                        ,   o.OrderId                                                              -- 注文ID
                        ,   c5.Class1                                                              -- 補償有無
                        ,   CASE
                                WHEN pas.ClearConditionForCharge = 0 THEN NULL
                                ELSE F_GetSalesDefiniteConditions(o.OrderSeq)
                            END                                                                    -- 売上確定条件
                        ,   DATE(apas.ATUriDay)                                                    -- 売上確定日付
                        ,   o.OemClaimTransDate                                                    -- OEM移管日
                        ,   NULL                                                                   -- 無保証立替金戻し実施日
                        ,   o.EnterpriseId                                                         -- 加盟店ID
                        ,   e.EnterpriseNameKj                                                     -- 加盟店名
                        ,   cc.F_LimitDate                                                         -- 支払期日
                        ,   cc.F_ClaimAmount                                                       -- 初期債権金額
                        ,   rc.ReceiptDate                                                         -- 最最終入金日
                        ,   DATEDIFF( v_BusinessDate, rc.ReceiptDate )                             -- 最終入金後経過日数
                        ,   c1.Class3                                                              -- 延滞区分
                        ,   NOW()                                                                  -- 登録日時
                        ,   pi_user_id                                                             -- 登録者
                        ,   NOW()                                                                  -- 更新日時
                        ,   pi_user_id                                                             -- 更新者
                        ,   1                                                                      -- 有効ﾌﾗｸﾞ
                    FROM    T_ClaimControl cc
                            INNER JOIN T_Order o ON (o.OrderSeq = cc.OrderSeq)
                            INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
                            INNER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
                            INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
                            INNER JOIN T_Enterprise e ON (o.EnterpriseId = e.EnterpriseId)
                            INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
                            INNER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
                            INNER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq AND rc.ValidFlg = 1)
                            INNER JOIN M_Code c1 ON (c1.CodeId = 12 AND c1.KeyCode = cc.ClaimPattern)
                            INNER JOIN M_Code c5 ON (c5.CodeId = 159 AND c5.KeyCode = IFNULL(o.OutOfAmends,0))
                            INNER JOIN M_Code c6 ON (c6.CodeId = 160 AND c6.KeyCode = IFNULL(o.OemId, 0))
                            LEFT OUTER JOIN T_Cancel can ON (can.OrderSeq = o.OrderSeq)
                    WHERE   cc.BalanceClaimAmount > 0
                    AND     (o.Deli_ConfirmArrivalDate IS NULL OR (o.Deli_ConfirmArrivalDate IS NOT NULL AND o.Deli_ConfirmArrivalFlg <> 1))
                    AND     cc.ValidFlg = 1
                    AND     o.ValidFlg = 1
                    AND     can.ApprovalDate IS NULL
                    ;

    -- CB直営分
    -- ④入金後ｷｬﾝｾﾙで顧客入金済でｷｬﾝｾﾙ分の返金分が立替前及び立替済（本締）＆加盟店への振込日が未来日
    INSERT
    INTO    AT_Excess_List( DailyMonthlyFlg                                                        -- 日次･月次区分
                        ,   ProcessingDate                                                         -- 処理日付
                        ,   AccountDate                                                            -- 会計月
                        ,   OemId                                                                  -- OEMID
                        ,   OemNameKj                                                              -- OEM先名
                        ,   ManCustId                                                              -- 管理顧客番号
                        ,   ManCusNameKj                                                           -- 顧客名
                        ,   ExcessAmount                                                           -- 過剰金額
                        ,   OrderSeq                                                               -- 注文Seq
                        ,   OrderId                                                                -- 注文ID
                        ,   OutOfAmends                                                            -- 補償有無
                        ,   SalesDefiniteConditions                                                -- 売上確定条件
                        ,   SalesDefiniteDate                                                      -- 売上確定日付
                        ,   OemTransferDate                                                        -- OEM移管日
                        ,   SettlementBackDate                                                     -- 無保証立替金戻し実施日
                        ,   EnterpriseId                                                           -- 加盟店ID
                        ,   EnterpriseNameKj                                                       -- 加盟店名
                        ,   F_LimitDate                                                            -- 支払期日
                        ,   F_ClaimAmount                                                          -- 初期債権金額
                        ,   FinalReceiptDate                                                       -- 最最終入金日
                        ,   AfterTheFinalPaymentDays                                               -- 最終入金後経過日数
                        ,   OverdueClassification                                                  -- 延滞区分
                        ,   RegistDate                                                             -- 登録日時
                        ,   RegistId                                                               -- 登録者
                        ,   UpdateDate                                                             -- 更新日時
                        ,   UpdateId                                                               -- 更新者
                        ,   ValidFlg                                                               -- 有効ﾌﾗｸﾞ
                          )
                    SELECT  0                                                                      -- 日次･月次区分
                        ,   v_BusinessDate                                                         -- 処理日付
                        ,   NULL                                                                   -- 会計月
                        ,   c6.Class3                                                              -- OEMID
                        ,   c6.Class2                                                              -- OEM先名
                        ,   mc.ManCustId                                                           -- 管理顧客番号
                        ,   mc.NameKj                                                              -- 顧客名
                        ,   cc.ReceiptAmountTotal                                                  -- 過剰金額
                        ,   o.OrderSeq                                                             -- 注文Seq
                        ,   o.OrderId                                                              -- 注文ID
                        ,   c5.Class1                                                              -- 補償有無
                        ,   F_GetSalesDefiniteConditions(o.OrderSeq)                               -- 売上確定条件
                        ,   DATE(apas.ATUriDay)                                                    -- 売上確定日付
                        ,   o.OemClaimTransDate                                                    -- OEM移管日
                        ,   NULL                                                                   -- 無保証立替金戻し実施日
                        ,   o.EnterpriseId                                                         -- 加盟店ID
                        ,   e.EnterpriseNameKj                                                     -- 加盟店名
                        ,   cc.F_LimitDate                                                         -- 支払期日
                        ,   cc.F_ClaimAmount                                                       -- 初期債権金額
                        ,   rc.ReceiptDate                                                         -- 最最終入金日
                        ,   DATEDIFF( v_BusinessDate, rc.ReceiptDate )                             -- 最終入金後経過日数
                        ,   c1.Class3                                                              -- 延滞区分
                        ,   NOW()                                                                  -- 登録日時
                        ,   pi_user_id                                                             -- 登録者
                        ,   NOW()                                                                  -- 更新日時
                        ,   pi_user_id                                                             -- 更新者
                        ,   1                                                                      -- 有効ﾌﾗｸﾞ
                    FROM    T_ClaimControl cc
                            INNER JOIN T_Order o ON (o.OrderSeq = cc.OrderSeq)
                            INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
                            INNER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
                            INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
                            INNER JOIN T_Cancel can ON (can.OrderSeq = o.OrderSeq AND CancelPhase IN (3, 4))
                            LEFT OUTER JOIN T_PayingControl pc ON (pc.Seq = can.PayingControlSeq)
                            INNER JOIN T_Enterprise e ON (o.EnterpriseId = e.EnterpriseId)
                            INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
                            INNER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
                            INNER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq AND rc.ValidFlg = 1)
                            INNER JOIN M_Code c1 ON (c1.CodeId = 12 AND c1.KeyCode = cc.ClaimPattern)
                            INNER JOIN M_Code c5 ON (c5.CodeId = 159 AND c5.KeyCode = IFNULL(o.OutOfAmends,0))
                            INNER JOIN M_Code c6 ON (c6.CodeId = 160 AND c6.KeyCode = IFNULL(o.OemId, 0))
                    WHERE   cc.ReceiptAmountTotal > 0
                    AND     pc.ExecScheduleDate > v_BusinessDate       -- 振込日（立替予定日）が未来日のﾃﾞｰﾀが対象
                    AND     cc.ValidFlg = 1
                    AND     o.ValidFlg = 1
                    AND     can.ApprovalDate IS NOT NULL
                    AND     (c6.Class1 = 0 OR (c6.Class1 = 1 AND o.OemClaimTransDate IS NOT NULL AND o.OemClaimTransDate <= rc.ReceiptDate))
                    ;

    -- OEM
    -- ④入金後ｷｬﾝｾﾙで顧客入金済でｷｬﾝｾﾙ分の返金分が立替前及び立替済（本締）＆加盟店への振込日が未来日
    INSERT
    INTO    AT_Excess_List( DailyMonthlyFlg                                                        -- 日次･月次区分
                        ,   ProcessingDate                                                         -- 処理日付
                        ,   AccountDate                                                            -- 会計月
                        ,   OemId                                                                  -- OEMID
                        ,   OemNameKj                                                              -- OEM先名
                        ,   ManCustId                                                              -- 管理顧客番号
                        ,   ManCusNameKj                                                           -- 顧客名
                        ,   ExcessAmount                                                           -- 過剰金額
                        ,   OrderSeq                                                               -- 注文Seq
                        ,   OrderId                                                                -- 注文ID
                        ,   OutOfAmends                                                            -- 補償有無
                        ,   SalesDefiniteConditions                                                -- 売上確定条件
                        ,   SalesDefiniteDate                                                      -- 売上確定日付
                        ,   OemTransferDate                                                        -- OEM移管日
                        ,   SettlementBackDate                                                     -- 無保証立替金戻し実施日
                        ,   EnterpriseId                                                           -- 加盟店ID
                        ,   EnterpriseNameKj                                                       -- 加盟店名
                        ,   F_LimitDate                                                            -- 支払期日
                        ,   F_ClaimAmount                                                          -- 初期債権金額
                        ,   FinalReceiptDate                                                       -- 最最終入金日
                        ,   AfterTheFinalPaymentDays                                               -- 最終入金後経過日数
                        ,   OverdueClassification                                                  -- 延滞区分
                        ,   RegistDate                                                             -- 登録日時
                        ,   RegistId                                                               -- 登録者
                        ,   UpdateDate                                                             -- 更新日時
                        ,   UpdateId                                                               -- 更新者
                        ,   ValidFlg                                                               -- 有効ﾌﾗｸﾞ
                          )
                    SELECT  0                                                                      -- 日次･月次区分
                        ,   v_BusinessDate                                                         -- 処理日付
                        ,   NULL                                                                   -- 会計月
                        ,   c6.Class3                                                              -- OEMID
                        ,   c6.Class2                                                              -- OEM先名
                        ,   mc.ManCustId                                                           -- 管理顧客番号
                        ,   mc.NameKj                                                              -- 顧客名
                        ,   cc.ReceiptAmountTotal                                                  -- 過剰金額
                        ,   o.OrderSeq                                                             -- 注文Seq
                        ,   o.OrderId                                                              -- 注文ID
                        ,   c5.Class1                                                              -- 補償有無
                        ,   F_GetSalesDefiniteConditions(o.OrderSeq)                               -- 売上確定条件
                        ,   DATE(apas.ATUriDay)                                                    -- 売上確定日付
                        ,   o.OemClaimTransDate                                                    -- OEM移管日
                        ,   NULL                                                                   -- 無保証立替金戻し実施日
                        ,   o.EnterpriseId                                                         -- 加盟店ID
                        ,   e.EnterpriseNameKj                                                     -- 加盟店名
                        ,   cc.F_LimitDate                                                         -- 支払期日
                        ,   cc.F_ClaimAmount                                                       -- 初期債権金額
                        ,   rc.ReceiptDate                                                         -- 最最終入金日
                        ,   DATEDIFF( v_BusinessDate, rc.ReceiptDate )                             -- 最終入金後経過日数
                        ,   c1.Class3                                                              -- 延滞区分
                        ,   NOW()                                                                  -- 登録日時
                        ,   pi_user_id                                                             -- 登録者
                        ,   NOW()                                                                  -- 更新日時
                        ,   pi_user_id                                                             -- 更新者
                        ,   1                                                                      -- 有効ﾌﾗｸﾞ
                    FROM    T_ClaimControl cc
                            INNER JOIN T_Order o ON (o.OrderSeq = cc.OrderSeq)
                            INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
                            INNER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
                            INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
                            INNER JOIN T_Cancel can ON (can.OrderSeq = o.OrderSeq AND CancelPhase IN (3, 4))
                            LEFT OUTER JOIN T_PayingControl pc ON (pc.Seq = can.PayingControlSeq)
                            INNER JOIN T_Enterprise e ON (o.EnterpriseId = e.EnterpriseId)
                            INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
                            INNER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
                            INNER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq AND rc.ValidFlg = 1)
                            INNER JOIN M_Code c1 ON (c1.CodeId = 12 AND c1.KeyCode = cc.ClaimPattern)
                            INNER JOIN M_Code c5 ON (c5.CodeId = 159 AND c5.KeyCode = IFNULL(o.OutOfAmends,0))
                            INNER JOIN M_Code c6 ON (c6.CodeId = 160 AND c6.KeyCode = IFNULL(o.OemId, 0))
                            LEFT OUTER JOIN T_OemClaimed oc ON (oc.OemClaimedSeq = pc.OemClaimedSeq)
                    WHERE   cc.ReceiptAmountTotal > 0
                    AND     oc.SettlePlanDate > v_BusinessDate     -- 振込日（精算日）が未来日のﾃﾞｰﾀが対象
                    AND     cc.ValidFlg = 1
                    AND     o.ValidFlg = 1
                    AND     can.ApprovalDate IS NOT NULL
                    AND     ( o.OemClaimTransDate IS NULL OR ( o.OemClaimTransDate IS NOT NULL AND o.OemClaimTransDate > rc.ReceiptDate ) )
                    AND     c6.Class1 <> 0
                    ;

END
$$

DELIMITER ;
