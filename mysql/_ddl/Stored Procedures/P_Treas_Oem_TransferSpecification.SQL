DROP PROCEDURE IF EXISTS P_Treas_Oem_TransferSpecification;

DELIMITER $$

CREATE PROCEDURE P_Treas_Oem_TransferSpecification ( IN pi_user_id INT )
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名       ：  P_Treas_Oem_TransferSpecification
 *
 * 概要             ：  OEM債権移管明細
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
    SET v_BusinessDate      = F_GetSystemProperty( '[DEFAULT]', 'systeminfo', 'BusinessDate' );       -- 業務日付
    SET v_AccountingMonth   = F_GetSystemProperty( '[DEFAULT]', 'systeminfo', 'AccountingMonth' );    -- 会計月

    INSERT INTO AT_Oem_TransferSpecification (
         DailyMonthlyFlg
        ,ProcessingDate
        ,AccountDate
        ,OemId
        ,OemNameKj
        ,OemTransferDate
        ,ReceivablesTransferredAmount
        ,OrderSeq
        ,OrderId
        ,OutOfAmends
        ,SalesDefiniteConditions
        ,SalesDefiniteDate
        ,EnterpriseId
        ,EnterpriseNameKj
        ,ManCustId
        ,ManCusNameKj
        ,F_LimitDate
        ,F_ClaimAmount
        ,FinalReceiptDate
        ,AfterTheFinalPaymentDays
        ,OverdueClassification
        ,RegistDate
        ,RegistId
        ,UpdateDate
        ,UpdateId
        ,ValidFlg )
    SELECT
         2                                                                      -- 日次･月次区分
        ,v_BusinessDate                                                         -- 処理日付
        ,( CASE
            WHEN DATE_FORMAT( o.OemClaimTransDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' ) THEN v_AccountingMonth
            ELSE DATE_FORMAT( o.OemClaimTransDate, '%Y-%m-01' )
         END )                                                                  -- 会計月
        ,c3.Class3                                                              -- OEMID
        ,c3.Class2                                                              -- OEM先名
        ,o.OemClaimTransDate                                                    -- OEM移管日
        ,o.UseAmount                                                            -- CB移管金額(債権残金額)
        ,o.OrderSeq                                                             -- 注文Seq
        ,o.OrderId                                                              -- 注文ID
        ,c2.Class1                                                              -- 補償有無
        ,F_GetSalesDefiniteConditions(o.OrderSeq)                               -- 売上確定条件
        ,DATE(apas.ATUriDay)                                                    -- 売上確定日付
        ,o.EnterpriseId                                                         -- 加盟店ID
        ,e.EnterpriseNameKj                                                     -- 加盟店名
        ,mc.ManCustId                                                           -- 管理顧客番号
        ,mc.NameKj                                                              -- 顧客名
        ,cc.F_LimitDate                                                         -- 支払期日
        ,cc.F_ClaimAmount                                                       -- 当初OEM消費者未収金(初期債権金額)
        ,rc.ReceiptDate                                                         -- 最終入金日
        ,DATEDIFF( v_BusinessDate, rc.ReceiptDate )                             -- 最終入金後経過日数
        ,c1.Class3                                                              -- 延滞区分
        ,NOW()                                                                  -- 登録日時
        ,pi_user_id                                                             -- 登録者
        ,NOW()                                                                  -- 更新日時
        ,pi_user_id                                                             -- 更新者
        ,1                                                                      -- 有効ﾌﾗｸﾞ:有効
    FROM        T_Order o
    INNER JOIN  T_PayingAndSales pas
    ON      pas.OrderSeq                = o.OrderSeq
    INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
    INNER JOIN  T_Customer c
    ON      c.OrderSeq          = o.OrderSeq
    INNER JOIN  T_ClaimControl cc
    ON      cc.OrderSeq         = o.P_OrderSeq
    LEFT JOIN   T_Enterprise e
    ON      e.EnterpriseId      = o.EnterpriseId
    LEFT JOIN   T_EnterpriseCustomer ec
    ON      ec.EntCustSeq       = c.EntCustSeq
    LEFT JOIN   T_ManagementCustomer mc
    ON      mc.ManCustId        = ec.ManCustId
    LEFT  JOIN T_ReceiptControl rc
    ON      rc.ReceiptSeq       = cc.LastReceiptSeq
    AND     rc.ValidFlg         = 1
    LEFT JOIN   M_Code c1
    ON      c1.CodeId           = 12
    AND     c1.KeyCode          = cc.ClaimPattern
    LEFT JOIN   M_Code c2
    ON      c2.CodeId           = 159
    AND     c2.KeyCode          = IFNULL( o.OutOfAmends, 0 )
    INNER JOIN  M_Code c3
    ON      c3.CodeId           = 160
    AND     c3.KeyCode          = IFNULL( o.OemId, 0 )
    WHERE   o.OemClaimTransDate IS NOT NULL
    AND     o.OemClaimTransDate <=  v_BusinessDate
    AND     o.OemClaimTransFlg  =   0
    AND     o.ValidFlg          =   1
    AND     c3.Class1           <>  0;

    -- -----------------------------
    -- 取消分の移管明細を出力
    -- -----------------------------
    INSERT
    INTO    AT_Oem_TransferSpecification(   DailyMonthlyFlg
                                        ,   ProcessingDate
                                        ,   AccountDate
                                        ,   OemId
                                        ,   OemNameKj
                                        ,   OemTransferDate
                                        ,   ReceivablesTransferredAmount
                                        ,   OrderSeq
                                        ,   OrderId
                                        ,   OutOfAmends
                                        ,   SalesDefiniteConditions
                                        ,   SalesDefiniteDate
                                        ,   EnterpriseId
                                        ,   EnterpriseNameKj
                                        ,   ManCustId
                                        ,   ManCusNameKj
                                        ,   F_LimitDate
                                        ,   F_ClaimAmount
                                        ,   FinalReceiptDate
                                        ,   AfterTheFinalPaymentDays
                                        ,   OverdueClassification
                                        ,   RegistDate
                                        ,   RegistId
                                        ,   UpdateDate
                                        ,   UpdateId
                                        ,   ValidFlg
                                        )
                                    SELECT  2                                                                      -- 日次･月次区分
                                        ,   v_BusinessDate                                                         -- 処理日付
                                        ,   ( CASE
                                               WHEN DATE_FORMAT( can.ApprovalDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' ) THEN v_AccountingMonth
                                               ELSE DATE_FORMAT( can.ApprovalDate, '%Y-%m-01' )
                                            END )                                                                  -- 会計月
                                        ,   c3.Class3                                                              -- OEMID
                                        ,   c3.Class2                                                              -- OEM先名
                                        ,   DATE(can.ApprovalDate)                                                 -- OEM移管日
                                        ,   o.UseAmount * -1                                                       -- CB移管金額(債権残金額)
                                        ,   o.OrderSeq                                                             -- 注文Seq
                                        ,   o.OrderId                                                              -- 注文ID
                                        ,   c2.Class1                                                              -- 補償有無
                                        ,   'キャンセル日'                                                         -- 売上確定条件(固定文字)
                                        ,   DATE(can.ApprovalDate)                                                 -- 売上確定日付(キャンセル承認日)
                                        ,   o.EnterpriseId                                                         -- 加盟店ID
                                        ,   e.EnterpriseNameKj                                                     -- 加盟店名
                                        ,   mc.ManCustId                                                           -- 管理顧客番号
                                        ,   mc.NameKj                                                              -- 顧客名
                                        ,   cc.F_LimitDate                                                         -- 支払期日
                                        ,   cc.F_ClaimAmount                                                       -- 当初OEM消費者未収金(初期債権金額)
                                        ,   rc.ReceiptDate                                                         -- 最終入金日
                                        ,   DATEDIFF( v_BusinessDate, rc.ReceiptDate )                             -- 最終入金後経過日数
                                        ,   c1.Class3                                                              -- 延滞区分
                                        ,   NOW()                                                                  -- 登録日時
                                        ,   pi_user_id                                                             -- 登録者
                                        ,   NOW()                                                                  -- 更新日時
                                        ,   pi_user_id                                                             -- 更新者
                                        ,   1                                                                      -- 有効ﾌﾗｸﾞ:有効
                                    FROM    T_Order o
                                            INNER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq AND pas.ClearConditionForCharge = 1)
                                            INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
                                            INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
                                            INNER JOIN T_Cancel can ON (can.OrderSeq = o.OrderSeq)
                                            INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
                                            INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
                                            INNER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
                                            LEFT  JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq AND rc.ValidFlg = 1)
                                            LEFT  JOIN M_Code c1 ON (c1.CodeId = 12 AND c1.KeyCode = cc.ClaimPattern)
                                            LEFT  JOIN M_Code c2 ON (c2.CodeId = 159 AND c2.KeyCode = IFNULL( o.OutOfAmends, 0 ))
                                            INNER JOIN M_Code c3 ON (c3.CodeId = 160 AND c3.KeyCode = IFNULL( o.OemId, 0 ))
                                    WHERE   o.OemClaimTransDate IS NOT NULL
                                    AND     o.OemClaimTransDate <=  v_BusinessDate
                                    AND     o.ValidFlg          =   1
                                    AND     cc.BalanceUseAmount > 0
                                    AND     c3.Class1           <>  0
                                    AND     can.ApprovalDate >= v_BusinessDate AND can.ApprovalDate < (v_BusinessDate + INTERVAL 1 DAY)
                                    AND     can.ApproveFlg       = 1
                                    AND     can.ValidFlg         = 1;


    -- -----------------------------
    -- 顧客入金日<債権移管日<=入金処理日による債権移管戻し
    -- -----------------------------
    INSERT
    INTO    AT_Oem_TransferSpecification(   DailyMonthlyFlg
                                        ,   ProcessingDate
                                        ,   AccountDate
                                        ,   OemId
                                        ,   OemNameKj
                                        ,   OemTransferDate
                                        ,   ReceivablesTransferredAmount
                                        ,   OrderSeq
                                        ,   OrderId
                                        ,   OutOfAmends
                                        ,   SalesDefiniteConditions
                                        ,   SalesDefiniteDate
                                        ,   EnterpriseId
                                        ,   EnterpriseNameKj
                                        ,   ManCustId
                                        ,   ManCusNameKj
                                        ,   F_LimitDate
                                        ,   F_ClaimAmount
                                        ,   FinalReceiptDate
                                        ,   AfterTheFinalPaymentDays
                                        ,   OverdueClassification
                                        ,   RegistDate
                                        ,   RegistId
                                        ,   UpdateDate
                                        ,   UpdateId
                                        ,   ValidFlg
                                        )
                                        SELECT 
                                             2                                                                      -- 日次･月次区分
                                            ,v_BusinessDate                                                         -- 処理日付
                                            ,( CASE
                                                WHEN DATE_FORMAT( o.OemClaimTransDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' ) THEN v_AccountingMonth
                                                ELSE DATE_FORMAT( o.OemClaimTransDate, '%Y-%m-01' )
                                                END )                                                               -- 会計月
                                            ,c3.Class3                                                              -- OEMID
                                            ,c3.Class2                                                              -- OEM先名
                                            ,o.OemClaimTransDate                                                    -- OEM移管日
                                            ,SUM(rc.CheckingUseAmount) * -1                                         -- CB移管金額(債権残金額)
                                            ,o.OrderSeq                                                             -- 注文Seq
                                            ,o.OrderId                                                              -- 注文ID
                                            ,c2.Class1                                                              -- 補償有無
                                            ,F_GetSalesDefiniteConditions(o.OrderSeq)                               -- 売上確定条件
                                            ,DATE(apas.ATUriDay)                                                    -- 売上確定日付
                                            ,o.EnterpriseId                                                         -- 加盟店ID
                                            ,e.EnterpriseNameKj                                                     -- 加盟店名
                                            ,mc.ManCustId                                                           -- 管理顧客番号
                                            ,mc.NameKj                                                              -- 顧客名
                                            ,cc.F_LimitDate                                                         -- 支払期日
                                            ,cc.F_ClaimAmount                                                       -- 当初OEM消費者未収金(初期債権金額)
                                            ,MAX(rc.ReceiptDate)                                                    -- 最終入金日
                                            ,DATEDIFF( v_BusinessDate, MAX(rc.ReceiptDate) )                          -- 最終入金後経過日数
                                            ,c1.Class3                                                              -- 延滞区分
                                            ,NOW()                                                                  -- 登録日時
                                            ,pi_user_id                                                             -- 登録者
                                            ,NOW()                                                                  -- 更新日時
                                            ,pi_user_id                                                             -- 更新者
                                            ,1                                                                      -- 有効ﾌﾗｸﾞ:有効
                                        FROM T_Order o
                                             INNER JOIN T_PayingAndSales pas
                                                     ON pas.OrderSeq = o.OrderSeq
                                                    AND pas.ClearConditionForCharge = 1
                                             INNER JOIN AT_PayingAndSales apas
                                                     ON apas.Seq = pas.Seq
                                             INNER JOIN T_Customer c
                                                     ON c.OrderSeq = o.OrderSeq
                                             INNER JOIN T_ClaimControl cc
                                                     ON cc.OrderSeq = o.P_OrderSeq
                                             INNER JOIN T_ReceiptControl rc
                                                     ON rc.OrderSeq = o.OrderSeq
                                                    AND rc.ValidFlg = 1
                                              LEFT JOIN T_Enterprise e
                                                     ON e.EnterpriseId = o.EnterpriseId
                                              LEFT JOIN T_EnterpriseCustomer ec
                                                     ON ec.EntCustSeq = c.EntCustSeq
                                              LEFT JOIN T_ManagementCustomer mc
                                                     ON mc.ManCustId = ec.ManCustId
                                              LEFT JOIN M_Code c1
                                                     ON c1.CodeId = 12
                                                    AND c1.KeyCode = cc.ClaimPattern
                                              LEFT JOIN M_Code c2
                                                     ON c2.CodeId = 159
                                                    AND c2.KeyCode = IFNULL( o.OutOfAmends, 0 )
                                             INNER JOIN M_Code c3
                                                     ON c3.CodeId = 160
                                                    AND c3.KeyCode = IFNULL( o.OemId, 0 )
                                        WHERE o.OemClaimTransDate IS NOT NULL
                                          AND o.OemClaimTransDate <=  v_BusinessDate
                                          AND o.OemClaimTransFlg  =   1
                                          AND o.ValidFlg          =   1
                                          AND c3.Class1           <>  0
                                          AND rc.ReceiptDate < o.OemClaimTransDate 
                                          AND o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate)
                                          AND rc.DailySummaryFlg = 0
                                          AND NOT (   DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01')  -- 月を跨いで入金処理がされた
                                                  AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01')) -- 既に会計締めデータが存在する
                                                  )
                                        GROUP BY o.OrderSeq
                                        HAVING SUM(rc.CheckingUseAmount) > 0;

END
$$

DELIMITER ;
