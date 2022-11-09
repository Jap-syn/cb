DROP PROCEDURE IF EXISTS P_Treas_Consumer_AccountsDue;

DELIMITER $$

CREATE PROCEDURE P_Treas_Consumer_AccountsDue( IN pi_user_id INT )
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名       ：  P_Treas_Consumer_AccountsDue
 *
 * 概要             ：  消費者未収金明細（月次）
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
    SET v_BusinessDate = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'BusinessDate');         -- 業務日付
    SET v_AccountingMonth = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'AccountingMonth');   -- 会計月

    -- --------------------
    -- 消費者未収金①
    -- --------------------
    BEGIN

        DECLARE v_DailyMonthlyFlg               INT;
        DECLARE v_ProcessingDate                DATE;
        DECLARE v_AccountDate                   DATE;
        DECLARE v_ManCustId                     BIGINT;
        DECLARE v_ManCusNameKj                  VARCHAR(160);
        DECLARE v_OrderSeq                      BIGINT;
        DECLARE v_OrderId                       VARCHAR(50);
        DECLARE v_OutOfAmends                   VARCHAR(50);
        DECLARE v_SalesDefiniteConditions       VARCHAR(50);
        DECLARE v_SalesDefiniteDate             DATE;
        DECLARE v_OemTransferDate               DATE;
        DECLARE v_ReceivablesRemainingAmount    BIGINT;
        DECLARE v_OemId                         BIGINT;
        DECLARE v_OemNameKj                     VARCHAR(160);
        DECLARE v_EnterpriseId                  BIGINT;
        DECLARE v_EnterpriseNameKj              VARCHAR(160);
        DECLARE v_F_LimitDate                   DATE;
        DECLARE v_F_ClaimAmount                 BIGINT;
        DECLARE v_FinalReceiptDate              DATE;
        DECLARE v_AfterTheFinalPaymentDays      BIGINT;
        DECLARE v_OverdueClassification         VARCHAR(50);
        DECLARE v_RegistDate                    DATETIME;
        DECLARE v_RegistId                      INT;
        DECLARE v_UpdateDate                    DATETIME;
        DECLARE v_UpdateId                      INT;
        DECLARE v_ValidFlg                      INT;

        -- NO_DATA_FOUND ｲﾍﾞﾝﾄﾊﾝﾄﾞﾗ用変数
        DECLARE no_data_found INT DEFAULT 1;

        -- ｶｰｿﾙ宣言
        DECLARE l_cur CURSOR FOR
            SELECT
                 '1'                                            -- 日次･月次区分
                ,v_BusinessDate                                 -- 処理日付
                ,v_AccountingMonth                              -- 会計月
                ,mc.ManCustId                                   -- 管理顧客番号
                ,mc.NameKj                                      -- 顧客名
                ,o.OrderSeq                                     -- 注文Seq
                ,o.OrderId                                      -- 注文ID
                ,c2.Class1                                      -- 補償有無
                ,F_GetSalesDefiniteConditions(o.OrderSeq)       -- 売上確定条件
                ,DATE(apas.ATUriDay)                            -- 売上確定日付
                ,o.OemClaimTransDate                            -- OEM移管日
                ,(cc.BalanceUseAmount
                  + IFNULL( ( SELECT SUM(CheckingUseAmount) FROM T_ReceiptControl WHERE cc.OrderSeq = OrderSeq AND DATE_FORMAT(ReceiptDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
                  - IFNULL( ( SELECT SUM(CheckingUseAmount) FROM T_RepaymentControl WHERE cc.ClaimId = ClaimId AND DATE_FORMAT(DecisionDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
                  + IFNULL( ( SELECT SUM(CheckingUseAmount) FROM T_SundryControl WHERE cc.OrderSeq = OrderSeq AND SundryType = 1 AND DATE_FORMAT(ProcessDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
                  - IFNULL( ( SELECT SUM(CheckingUseAmount) FROM T_SundryControl WHERE cc.OrderSeq = OrderSeq AND SundryType = 0 AND DATE_FORMAT(ProcessDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)) -- 債権残金額
                ,c1.Class3                                      -- OEMID
                ,c1.Class2                                      -- OEM先名
                ,o.EnterpriseId                                 -- 加盟店ID
                ,e.EnterpriseNameKj                             -- 加盟店名
                ,cc.F_LimitDate                                 -- 支払期日
                ,cc.F_ClaimAmount                               -- 初期債権金額
                ,(SELECT MAX(ReceiptDate)
                  FROM   T_ReceiptControl
                  WHERE  OrderSeq = cc.OrderSeq
                  AND    (DATE_FORMAT(ReceiptDate, '%Y-%m') <= DATE_FORMAT(v_AccountingMonth, '%Y-%m'))) -- 最終入金日
                ,DATEDIFF(v_BusinessDate, (SELECT MAX(ReceiptDate)
                                           FROM   T_ReceiptControl
                                           WHERE  OrderSeq = cc.OrderSeq
                                           AND    (DATE_FORMAT(ReceiptDate, '%Y-%m') <= DATE_FORMAT(v_AccountingMonth, '%Y-%m')))) -- 最終入金後経過日数
                ,c3.Class3                                      -- 延滞区分
                ,NOW()                                          -- 登録日時
                ,pi_user_id                                     -- 登録者
                ,NOW()                                          -- 更新日時
                ,pi_user_id                                     -- 更新者
                ,1                                              -- 有効ﾌﾗｸﾞ
            FROM  T_PayingAndSales pas
                  INNER JOIN AT_PayingAndSales apas ON (pas.Seq = apas.Seq)
                  INNER JOIN T_Order o ON (pas.OrderSeq = o.OrderSeq)
                  INNER JOIN T_Customer c ON (o.OrderSeq = c.OrderSeq)
                  INNER JOIN T_EnterpriseCustomer ec ON (c.EntCustSeq = ec.EntCustSeq)
                  INNER JOIN T_ManagementCustomer mc ON (ec.ManCustId = mc.ManCustId)
                  INNER JOIN T_Enterprise e ON (o.EnterpriseId = e.EnterpriseId)
                  INNER JOIN M_Code c1 ON c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0) AND (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m') <= DATE_FORMAT(v_AccountingMonth , '%Y-%m')))
                  INNER JOIN T_ClaimControl cc ON (pas.OrderSeq = cc.OrderSeq)
                  LEFT OUTER JOIN T_Cancel can ON (pas.OrderSeq = can.OrderSeq AND can.ValidFlg = 1)
                  LEFT OUTER JOIN M_Code c2 ON (c2.CodeId = 159 AND c2.KeyCode = IFNULL(o.OutOfAmends, 0))
                  LEFT OUTER JOIN M_Code c3 ON (c3.CodeId = 12 AND c3.KeyCode = cc.ClaimPattern)
            WHERE apas.ATUriDay < DATE_FORMAT((v_AccountingMonth + INTERVAL 1 MONTH), '%Y%m%d')
            AND   DATE_FORMAT(IFNULL(can.ApprovalDate, '2100-01-01'), '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')
            AND   DATE_FORMAT(IFNULL(o.Dmg_DecisionDate, '2100-01-01'), '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')
            AND   cc.BalanceUseAmount
                  + IFNULL( ( SELECT SUM(CheckingUseAmount) FROM T_ReceiptControl WHERE cc.OrderSeq = OrderSeq AND DATE_FORMAT(ReceiptDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
                  - IFNULL( ( SELECT SUM(CheckingUseAmount) FROM T_RepaymentControl WHERE cc.ClaimId = ClaimId AND DATE_FORMAT(DecisionDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
                  + IFNULL( ( SELECT SUM(CheckingUseAmount) FROM T_SundryControl WHERE cc.OrderSeq = OrderSeq AND SundryType = 1 AND DATE_FORMAT(ProcessDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
                  - IFNULL( ( SELECT SUM(CheckingUseAmount) FROM T_SundryControl WHERE cc.OrderSeq = OrderSeq AND SundryType = 0 AND DATE_FORMAT(ProcessDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
                  <> 0
            ;    

        -- NO_DATA_FOUND 用ｲﾍﾞﾝﾄﾊﾝﾄﾞﾗ宣言
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_data_found = 0;

        -- ｶｰｿﾙｵｰﾌﾟﾝ
        OPEN    l_cur;
            -- 最初のｶｰｿﾙをFetch
            FETCH l_cur INTO v_DailyMonthlyFlg, v_ProcessingDate, v_AccountDate, v_ManCustId, v_ManCusNameKj, v_OrderSeq, v_OrderId, v_OutOfAmends, v_SalesDefiniteConditions, v_SalesDefiniteDate, v_OemTransferDate, v_ReceivablesRemainingAmount, v_OemId, v_OemNameKj, v_EnterpriseId, v_EnterpriseNameKj, v_F_LimitDate, v_F_ClaimAmount, v_FinalReceiptDate, v_AfterTheFinalPaymentDays, v_OverdueClassification, v_RegistDate, v_RegistId, v_UpdateDate, v_UpdateId, v_ValidFlg;

            -- whileﾙｰﾌﾟで処理する
            WHILE no_data_found != 0  DO

                INSERT INTO AT_Consumer_AccountsDue (
                     DailyMonthlyFlg                                -- 日次･月次区分
                    ,ProcessingDate                                 -- 処理日付
                    ,AccountDate                                    -- 会計月
                    ,ManCustId                                      -- 管理顧客番号
                    ,ManCusNameKj                                   -- 顧客名
                    ,OrderSeq                                       -- 注文Seq
                    ,OrderId                                        -- 注文ID
                    ,OutOfAmends                                    -- 補償有無
                    ,SalesDefiniteConditions                        -- 売上確定条件
                    ,SalesDefiniteDate                              -- 売上確定日付
                    ,OemTransferDate                                -- OEM移管日
                    ,ReceivablesRemainingAmount                     -- 債権残金額
                    ,OemId                                          -- OEMID
                    ,OemNameKj                                      -- OEM先名
                    ,EnterpriseId                                   -- 加盟店ID
                    ,EnterpriseNameKj                               -- 加盟店名
                    ,F_LimitDate                                    -- 支払期日
                    ,F_ClaimAmount                                  -- 初期債権金額
                    ,FinalReceiptDate                               -- 最終入金日
                    ,AfterTheFinalPaymentDays                       -- 最終入金後経過日数
                    ,OverdueClassification                          -- 延滞区分
                    ,RegistDate                                     -- 登録日時
                    ,RegistId                                       -- 登録者
                    ,UpdateDate                                     -- 更新日時
                    ,UpdateId                                       -- 更新者
                    ,ValidFlg                                       -- 有効ﾌﾗｸﾞ
                ) VALUES (
                     v_DailyMonthlyFlg
                    ,v_ProcessingDate
                    ,v_AccountDate
                    ,v_ManCustId
                    ,v_ManCusNameKj
                    ,v_OrderSeq
                    ,v_OrderId
                    ,v_OutOfAmends
                    ,v_SalesDefiniteConditions
                    ,v_SalesDefiniteDate
                    ,v_OemTransferDate
                    ,v_ReceivablesRemainingAmount
                    ,v_OemId
                    ,v_OemNameKj
                    ,v_EnterpriseId
                    ,v_EnterpriseNameKj
                    ,v_F_LimitDate
                    ,v_F_ClaimAmount
                    ,v_FinalReceiptDate
                    ,v_AfterTheFinalPaymentDays
                    ,v_OverdueClassification
                    ,v_RegistDate
                    ,v_RegistId
                    ,v_UpdateDate
                    ,v_UpdateId
                    ,v_ValidFlg
                );

                -- 次のｶｰｿﾙをFetch
                FETCH l_cur INTO v_DailyMonthlyFlg, v_ProcessingDate, v_AccountDate, v_ManCustId, v_ManCusNameKj, v_OrderSeq, v_OrderId, v_OutOfAmends, v_SalesDefiniteConditions, v_SalesDefiniteDate, v_OemTransferDate, v_ReceivablesRemainingAmount, v_OemId, v_OemNameKj, v_EnterpriseId, v_EnterpriseNameKj, v_F_LimitDate, v_F_ClaimAmount, v_FinalReceiptDate, v_AfterTheFinalPaymentDays, v_OverdueClassification, v_RegistDate, v_RegistId, v_UpdateDate, v_UpdateId, v_ValidFlg;
            END WHILE;
        -- ｶｰｿﾙｸﾛｰｽﾞ
        CLOSE   l_cur;

    END;

    -- --------------------
    -- 消費者未収金②
    -- --------------------
    INSERT INTO AT_Consumer_AccountsDue (
         DailyMonthlyFlg                                -- 日次･月次区分
        ,ProcessingDate                                 -- 処理日付
        ,AccountDate                                    -- 会計月
        ,ManCustId                                      -- 管理顧客番号
        ,ManCusNameKj                                   -- 顧客名
        ,OrderSeq                                       -- 注文Seq
        ,OrderId                                        -- 注文ID
        ,OutOfAmends                                    -- 補償有無
        ,SalesDefiniteConditions                        -- 売上確定条件
        ,SalesDefiniteDate                              -- 売上確定日付
        ,OemTransferDate                                -- OEM移管日
        ,ReceivablesRemainingAmount                     -- 債権残金額
        ,OemId                                          -- OEMID
        ,OemNameKj                                      -- OEM先名
        ,EnterpriseId                                   -- 加盟店ID
        ,EnterpriseNameKj                               -- 加盟店名
        ,F_LimitDate                                    -- 支払期日
        ,F_ClaimAmount                                  -- 初期債権金額
        ,FinalReceiptDate                               -- 最終入金日
        ,AfterTheFinalPaymentDays                       -- 最終入金後経過日数
        ,OverdueClassification                          -- 延滞区分
        ,RegistDate                                     -- 登録日時
        ,RegistId                                       -- 登録者
        ,UpdateDate                                     -- 更新日時
        ,UpdateId                                       -- 更新者
        ,ValidFlg                                       -- 有効ﾌﾗｸﾞ
    )
    SELECT
         '1'                                            -- 日次･月次区分
        ,v_BusinessDate                                 -- 処理日付
        ,v_AccountingMonth                              -- 会計月
        ,mc.ManCustId                                   -- 管理顧客番号
        ,mc.NameKj                                      -- 顧客名
        ,o.OrderSeq                                     -- 注文Seq
        ,o.OrderId                                      -- 注文ID
        ,c2.Class1                                      -- 補償有無
        ,NULL                                           -- 売上確定条件
        ,DATE(apas.ATUriDay)                            -- 売上確定日付
        ,o.OemClaimTransDate                            -- OEM移管日
        ,(cc.CheckingUseAmount
          - IFNULL( (SELECT SUM(CheckingUseAmount) FROM T_ReceiptControl WHERE cc.OrderSeq = OrderSeq AND DATE_FORMAT(ReceiptDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
          + IFNULL( (SELECT SUM(CheckingUseAmount) FROM T_RepaymentControl WHERE cc.ClaimId = ClaimId AND DATE_FORMAT(DecisionDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
          - IFNULL( (SELECT SUM(CheckingUseAmount) FROM T_SundryControl WHERE cc.OrderSeq = OrderSeq AND SundryType = 1 AND DATE_FORMAT(ProcessDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
          + IFNULL( (SELECT SUM(CheckingUseAmount) FROM T_SundryControl WHERE cc.OrderSeq = OrderSeq AND SundryType = 0 AND DATE_FORMAT(ProcessDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
          - IFNULL( (SELECT SUM(CheckingUseAmount) FROM T_ReceiptControl WHERE cc.OrderSeq = OrderSeq AND DATE_FORMAT(ReceiptDate, '%Y-%m') <= DATE_FORMAT(v_AccountingMonth, '%Y-%m') AND DATE_FORMAT(ReceiptProcessDate, '%Y-%m-%d') > DATE_FORMAT(v_BusinessDate, '%Y-%m-%d')), 0)) * -1 -- 債権残金額
        ,c1.Class3                                      -- OEMID
        ,c1.Class2                                      -- OEM先名
        ,o.EnterpriseId                                 -- 加盟店ID
        ,e.EnterpriseNameKj                             -- 加盟店名
        ,cc.F_LimitDate                                 -- 支払期日
        ,cc.F_ClaimAmount                               -- 初期債権金額
        ,(SELECT MAX(ReceiptDate)
          FROM   T_ReceiptControl
          WHERE  OrderSeq = cc.OrderSeq
          AND    (DATE_FORMAT(ReceiptDate, '%Y-%m') <= DATE_FORMAT(v_AccountingMonth, '%Y-%m'))) -- 最終入金日
        ,DATEDIFF(v_BusinessDate, (SELECT MAX(ReceiptDate)
                                   FROM   T_ReceiptControl
                                   WHERE  OrderSeq = cc.OrderSeq
                                   AND    (DATE_FORMAT(ReceiptDate, '%Y-%m') <= DATE_FORMAT(v_AccountingMonth, '%Y-%m')))) -- 最終入金後経過日数
        ,c3.Class3                                      -- 延滞区分
        ,NOW()                                          -- 登録日時
        ,pi_user_id                                     -- 登録者
        ,NOW()                                          -- 更新日時
        ,pi_user_id                                     -- 更新者
        ,1                                              -- 有効ﾌﾗｸﾞ
    FROM  T_PayingAndSales pas
          INNER JOIN AT_PayingAndSales apas ON (pas.Seq = apas.Seq)
          INNER JOIN T_Order o ON (pas.OrderSeq = o.OrderSeq)
          INNER JOIN T_Customer c ON (o.OrderSeq = c.OrderSeq)
          INNER JOIN T_EnterpriseCustomer ec ON (c.EntCustSeq = ec.EntCustSeq)
          INNER JOIN T_ManagementCustomer mc ON (ec.ManCustId = mc.ManCustId)
          INNER JOIN T_Enterprise e ON (o.EnterpriseId = e.EnterpriseId)
          INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0) AND (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m') <= DATE_FORMAT(v_AccountingMonth , '%Y-%m'))))
          INNER JOIN T_ClaimControl cc ON (pas.OrderSeq = cc.OrderSeq)
          LEFT OUTER JOIN T_Cancel can ON (pas.OrderSeq = can.OrderSeq AND can.ValidFlg = 1)
          LEFT OUTER JOIN M_Code c2 ON (c2.CodeId = 159 AND c2.KeyCode = IFNULL(o.OutOfAmends, 0))
          LEFT OUTER JOIN M_Code c3 ON (c3.CodeId = 12 AND c3.KeyCode = cc.ClaimPattern)
    WHERE apas.ATUriDay >= DATE_FORMAT((v_AccountingMonth + INTERVAL 1 MONTH), '%Y%m%d')
    AND   DATE_FORMAT(IFNULL(can.ApprovalDate, '2100-01-01'), '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')
    AND   DATE_FORMAT(IFNULL(o.Dmg_DecisionDate, '2100-01-01'), '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')
    AND   ( SELECT SUM(ReceiptAmount) FROM T_ReceiptControl x WHERE x.OrderSeq = cc.OrderSeq AND DATE_FORMAT(x.ReceiptDate, '%Y-%m') <= DATE_FORMAT(v_AccountingMonth, '%Y-%m') AND DATE_FORMAT(x.ReceiptProcessDate, '%Y-%m-%d') <= DATE_FORMAT(v_BusinessDate, '%Y-%m-%d')) > 0
    AND   cc.CheckingUseAmount
          - IFNULL( (SELECT SUM(CheckingUseAmount) FROM T_ReceiptControl WHERE cc.OrderSeq = OrderSeq AND DATE_FORMAT(ReceiptDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
          + IFNULL( (SELECT SUM(CheckingUseAmount) FROM T_RepaymentControl WHERE cc.ClaimId = ClaimId AND DATE_FORMAT(DecisionDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
          - IFNULL( (SELECT SUM(CheckingUseAmount) FROM T_SundryControl WHERE cc.OrderSeq = OrderSeq AND SundryType = 1 AND DATE_FORMAT(ProcessDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
          + IFNULL( (SELECT SUM(CheckingUseAmount) FROM T_SundryControl WHERE cc.OrderSeq = OrderSeq AND SundryType = 0 AND DATE_FORMAT(ProcessDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
          - IFNULL( (SELECT SUM(CheckingUseAmount) FROM T_ReceiptControl WHERE cc.OrderSeq = OrderSeq AND DATE_FORMAT(ReceiptDate, '%Y-%m') <= DATE_FORMAT(v_AccountingMonth, '%Y-%m') AND DATE_FORMAT(ReceiptProcessDate, '%Y-%m-%d') > DATE_FORMAT(v_BusinessDate, '%Y-%m-%d')), 0)
          <> 0
    ;

    -- --------------------
    -- 消費者未収金③
    -- --------------------
    INSERT INTO AT_Consumer_AccountsDue (
         DailyMonthlyFlg                                -- 日次･月次区分
        ,ProcessingDate                                 -- 処理日付
        ,AccountDate                                    -- 会計月
        ,ManCustId                                      -- 管理顧客番号
        ,ManCusNameKj                                   -- 顧客名
        ,OrderSeq                                       -- 注文Seq
        ,OrderId                                        -- 注文ID
        ,OutOfAmends                                    -- 補償有無
        ,SalesDefiniteConditions                        -- 売上確定条件
        ,SalesDefiniteDate                              -- 売上確定日付
        ,OemTransferDate                                -- OEM移管日
        ,ReceivablesRemainingAmount                     -- 債権残金額
        ,OemId                                          -- OEMID
        ,OemNameKj                                      -- OEM先名
        ,EnterpriseId                                   -- 加盟店ID
        ,EnterpriseNameKj                               -- 加盟店名
        ,F_LimitDate                                    -- 支払期日
        ,F_ClaimAmount                                  -- 初期債権金額
        ,FinalReceiptDate                               -- 最終入金日
        ,AfterTheFinalPaymentDays                       -- 最終入金後経過日数
        ,OverdueClassification                          -- 延滞区分
        ,RegistDate                                     -- 登録日時
        ,RegistId                                       -- 登録者
        ,UpdateDate                                     -- 更新日時
        ,UpdateId                                       -- 更新者
        ,ValidFlg                                       -- 有効ﾌﾗｸﾞ
    )
    SELECT
         '1'                                            -- 日次･月次区分
        ,v_BusinessDate                                 -- 処理日付
        ,v_AccountingMonth                              -- 会計月
        ,mc.ManCustId                                   -- 管理顧客番号
        ,mc.NameKj                                      -- 顧客名
        ,o.OrderSeq                                     -- 注文Seq
        ,o.OrderId                                      -- 注文ID
        ,c2.Class1                                      -- 補償有無
        ,'キャンセル日'                                 -- 売上確定条件(固定文字)
        ,DATE(can.ApprovalDate)                         -- 売上確定日付(キャンセル承認日)
        ,o.OemClaimTransDate                            -- OEM移管日
        ,(cc.CheckingUseAmount
          - IFNULL( (SELECT SUM(CheckingUseAmount) FROM T_ReceiptControl WHERE cc.OrderSeq = OrderSeq AND DATE_FORMAT(ReceiptDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
          + IFNULL( (SELECT SUM(CheckingUseAmount) FROM T_RepaymentControl WHERE cc.ClaimId = ClaimId AND DATE_FORMAT(DecisionDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
          - IFNULL( (SELECT SUM(CheckingUseAmount) FROM T_SundryControl WHERE cc.OrderSeq = OrderSeq AND SundryType = 1 AND DATE_FORMAT(ProcessDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
          + IFNULL( (SELECT SUM(CheckingUseAmount) FROM T_SundryControl WHERE cc.OrderSeq = OrderSeq AND SundryType = 0 AND DATE_FORMAT(ProcessDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
          - IFNULL( (SELECT SUM(CheckingUseAmount) FROM T_ReceiptControl WHERE cc.OrderSeq = OrderSeq AND DATE_FORMAT(ReceiptDate, '%Y-%m') <= DATE_FORMAT(v_AccountingMonth, '%Y-%m') AND DATE_FORMAT(ReceiptProcessDate, '%Y-%m-%d') > DATE_FORMAT(v_BusinessDate, '%Y-%m-%d')), 0)) * -1 -- 債権残金額
        ,c1.Class3                                      -- OEMID
        ,c1.Class2                                      -- OEM先名
        ,o.EnterpriseId                                 -- 加盟店ID
        ,e.EnterpriseNameKj                             -- 加盟店名
        ,cc.F_LimitDate                                 -- 支払期日
        ,cc.F_ClaimAmount                               -- 初期債権金額
        ,(SELECT MAX(ReceiptDate)
          FROM   T_ReceiptControl
          WHERE  OrderSeq = cc.OrderSeq
          AND    (DATE_FORMAT(ReceiptDate, '%Y-%m') <= DATE_FORMAT(v_AccountingMonth, '%Y-%m'))) -- 最終入金日
        ,DATEDIFF(v_BusinessDate, (SELECT MAX(ReceiptDate)
                                   FROM   T_ReceiptControl
                                   WHERE  OrderSeq = cc.OrderSeq
                                   AND    (DATE_FORMAT(ReceiptDate, '%Y-%m') <= DATE_FORMAT(v_AccountingMonth, '%Y-%m')))) -- 最終入金後経過日数
        ,c3.Class3                                      -- 延滞区分
        ,NOW()                                          -- 登録日時
        ,pi_user_id                                     -- 登録者
        ,NOW()                                          -- 更新日時
        ,pi_user_id                                     -- 更新者
        ,1                                              -- 有効ﾌﾗｸﾞ
    FROM  T_PayingAndSales pas
          INNER JOIN AT_PayingAndSales apas ON (pas.Seq = apas.Seq)
          INNER JOIN T_Order o ON (pas.OrderSeq = o.OrderSeq)
          INNER JOIN T_Customer c ON (o.OrderSeq = c.OrderSeq)
          INNER JOIN T_EnterpriseCustomer ec ON (c.EntCustSeq = ec.EntCustSeq)
          INNER JOIN T_ManagementCustomer mc ON (ec.ManCustId = mc.ManCustId)
          INNER JOIN T_Enterprise e ON (o.EnterpriseId = e.EnterpriseId)
          INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL(o.OemId, 0) AND (c1.Class1 = 0 OR (c1.Class1 <> 0 AND DATE_FORMAT(o.OemClaimTransDate, '%Y-%m') <= DATE_FORMAT(v_AccountingMonth , '%Y-%m'))))
          INNER JOIN T_ClaimControl cc ON (pas.OrderSeq = cc.OrderSeq)
          INNER JOIN T_Cancel can ON (pas.OrderSeq = can.OrderSeq AND can.ValidFlg = 1)
          LEFT OUTER JOIN T_PayingControl pc ON (can.PayingControlSeq = pc.Seq)
          LEFT OUTER JOIN M_Code c2 ON (c2.CodeId = 159 AND c2.KeyCode = IFNULL(o.OutOfAmends, 0))
          LEFT OUTER JOIN M_Code c3 ON (c3.CodeId = 12 AND c3.KeyCode = cc.ClaimPattern)
    WHERE apas.ATUriDay < DATE_FORMAT((v_AccountingMonth + INTERVAL 1 MONTH), '%Y%m%d')
    AND   DATE_FORMAT(IFNULL(can.ApprovalDate, '2100-01-01'), '%Y-%m') <= DATE_FORMAT(v_AccountingMonth, '%Y-%m')
    AND   DATE_FORMAT(IFNULL(pc.ExecScheduleDate, '2100-01-01'), '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')
    AND   cc.CheckingUseAmount
          - IFNULL( (SELECT SUM(CheckingUseAmount) FROM T_ReceiptControl WHERE cc.OrderSeq = OrderSeq AND DATE_FORMAT(ReceiptDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
          + IFNULL( (SELECT SUM(CheckingUseAmount) FROM T_RepaymentControl WHERE cc.ClaimId = ClaimId AND DATE_FORMAT(DecisionDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
          - IFNULL( (SELECT SUM(CheckingUseAmount) FROM T_SundryControl WHERE cc.OrderSeq = OrderSeq AND SundryType = 1 AND DATE_FORMAT(ProcessDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
          + IFNULL( (SELECT SUM(CheckingUseAmount) FROM T_SundryControl WHERE cc.OrderSeq = OrderSeq AND SundryType = 0 AND DATE_FORMAT(ProcessDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')), 0)
          - IFNULL( (SELECT SUM(CheckingUseAmount) FROM T_ReceiptControl WHERE cc.OrderSeq = OrderSeq AND DATE_FORMAT(ReceiptDate, '%Y-%m') <= DATE_FORMAT(v_AccountingMonth, '%Y-%m') AND DATE_FORMAT(ReceiptProcessDate, '%Y-%m-%d') > DATE_FORMAT(v_BusinessDate, '%Y-%m-%d')), 0)
          <> 0
    ;

END
$$

DELIMITER ;
