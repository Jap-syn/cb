DROP PROCEDURE IF EXISTS P_Treas_PaymentPlaceTotal;

DELIMITER $$

CREATE PROCEDURE P_Treas_PaymentPlaceTotal( IN pi_user_id INT )
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名       ：  P_Treas_PaymentPlaceTotal
 *
 * 概要             ：  入金先ﾄｰﾀﾙ表
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
    DECLARE v_Seq               INT;                    -- ｼｰｹﾝｽ
    DECLARE v_DepositDate       DATE;                   -- 入金予定日
    DECLARE v_done              INT DEFAULT 0;

    -- ﾊﾝﾄﾞﾗｰ
    DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET v_done = 1;   -- cursor読み取り終了

    -- ------------------------------
    -- 変数初期化
    -- ------------------------------
    SET v_BusinessDate = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'BusinessDate');        -- 業務日付

    -- ------------------------------
    -- ① 収納代行会社の入金先ﾄｰﾀﾙ表明細を作成する
    -- ------------------------------
    INSERT INTO AT_PaymentPlaceDetails(
        ReceiptSeq
       ,DepositDate
       ,ReceiptFlg
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,PaymentDestinationName
       ,PaymentDestinationDetails
       ,PaymentAmount
       ,PaymentPerson
       ,CheckingUseAmount
       ,CheckingClaimFee
       ,CheckingDamageInterestAmount
       ,CheckingAdditionalClaimFee
       ,ProcessingDate
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        rc.ReceiptSeq                           -- 入金Seq
       ,rc.DepositDate                          -- 入返金日
       ,0                                       -- 入返金ﾌﾗｸﾞ
       ,c1.Class3                               -- OEMID
       ,o.EnterpriseId                          -- 加盟店ID
       ,o.OrderSeq                              -- 注文Seq
       ,o.OrderId                               -- 注文ID (旧取引ID)
       ,c4.Class1                               -- 入返金先
       ,c2.Class1                               -- 入返金先詳細
       ,rc.ReceiptAmount                        -- 入返金額
       ,c3.Class1                               -- 入金者
       ,rc.CheckingUseAmount                    -- 消込情報-利用額
       ,rc.CheckingClaimFee                     -- 消込情報-請求手数料
       ,rc.CheckingDamageInterestAmount         -- 消込情報-遅延損害金
       ,rc.CheckingAdditionalClaimFee           -- 消込情報-請求追加手数料
       ,CASE WHEN rc.DepositDate < v_BusinessDate THEN v_BusinessDate
        ELSE rc.DepositDate
        END                                     -- 集計日
       ,NOW()                                   -- 登録日時
       ,pi_user_id                              -- 登録者
       ,NOW()                                   -- 更新日時
       ,pi_user_id                              -- 更新者
       ,1                                       -- 有効ﾌﾗｸﾞ
    FROM
          T_ReceiptControl rc
    INNER JOIN AT_ReceiptControl arc
    ON    rc.ReceiptSeq       = arc.ReceiptSeq
    INNER JOIN T_Order o
    ON    o.OrderSeq        = rc.OrderSeq
    LEFT JOIN M_Code c1
    ON    c1.CodeId           = 160
    AND   c1.KeyCode          = IFNULL(o.OemId, 0)
    LEFT JOIN M_Code c2
    ON    c2.CodeId           = 156
    AND   c2.KeyCode          = arc.BankFlg
    LEFT JOIN M_Code c3
    ON    c3.CodeId           = 157
    AND   c3.KeyCode          = 1
    LEFT JOIN M_Code c4
    ON    c4.CodeId           = 151
    AND   c4.KeyCode          = rc.BranchBankId
    WHERE rc.DailySummaryFlg  = 0
    AND   rc.ValidFlg         = 1
    AND   rc.ReceiptAgentId   IS NOT NULL
    AND   rc.ReceiptClass    <> 9
    AND   DATE_FORMAT( rc.ReceiptProcessDate, '%Y-%m-%d' ) <= v_BusinessDate
    ;

    -- ------------------------------
    -- ② 収納代行会社以外の弁護士入金の入金先ﾄｰﾀﾙ表明細を作成する
    -- ------------------------------
    INSERT INTO AT_PaymentPlaceDetails(
        ReceiptSeq
       ,DepositDate
       ,ReceiptFlg
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,PaymentDestinationName
       ,PaymentDestinationDetails
       ,PaymentAmount
       ,PaymentPerson
       ,CheckingUseAmount
       ,CheckingClaimFee
       ,CheckingDamageInterestAmount
       ,CheckingAdditionalClaimFee
       ,ProcessingDate
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        rc.ReceiptSeq                           -- 入金Seq
       ,rc.ReceiptDate                          -- 入返金日
       ,0                                       -- 入返金ﾌﾗｸﾞ
       ,c1.Class3                               -- OEMID
       ,o.EnterpriseId                          -- 加盟店ID
       ,o.OrderSeq                              -- 注文Seq
       ,o.OrderId                               -- 注文ID (旧取引ID)
       ,c4.Class1                               -- 入返金先
       ,c2.Class1                               -- 入返金先詳細
       ,rc.ReceiptAmount                        -- 入返金額
       ,c3.Class1                               -- 入金者
       ,rc.CheckingUseAmount                    -- 消込情報-利用額
       ,rc.CheckingClaimFee                     -- 消込情報-請求手数料
       ,rc.CheckingDamageInterestAmount         -- 消込情報-遅延損害金
       ,rc.CheckingAdditionalClaimFee           -- 消込情報-請求追加手数料
       ,CASE WHEN rc.ReceiptDate < v_BusinessDate THEN v_BusinessDate
        ELSE rc.ReceiptDate
        END                                     -- 集計日
       ,NOW()                                   -- 登録日時
       ,pi_user_id                              -- 登録者
       ,NOW()                                   -- 更新日時
       ,pi_user_id                              -- 更新者
       ,1                                       -- 有効ﾌﾗｸﾞ
    FROM
          T_ReceiptControl rc
    INNER JOIN AT_ReceiptControl arc
    ON    rc.ReceiptSeq       = arc.ReceiptSeq
    INNER JOIN T_Order o
    ON    o.OrderSeq        = rc.OrderSeq
    LEFT JOIN M_Code c1
    ON    c1.CodeId           = 160
    AND   c1.KeyCode          = IFNULL(o.OemId, 0)
    LEFT JOIN M_Code c2
    ON    c2.CodeId           = 156
    AND   c2.KeyCode          = arc.BankFlg
    LEFT JOIN M_Code c3
    ON    c3.CodeId           = 157
    AND   c3.KeyCode          = 4
    LEFT JOIN M_Code c4
    ON    c4.CodeId           = 151
    AND   c4.KeyCode          = rc.BranchBankId
    WHERE rc.DailySummaryFlg  = 0
    AND   rc.ValidFlg         = 1
    AND   rc.ReceiptAgentId   IS NULL
    AND   rc.ReceiptClass     = 9
    AND   arc.ClassDetails    = 4
    AND   DATE_FORMAT( rc.ReceiptProcessDate, '%Y-%m-%d' ) <= v_BusinessDate
    ;

    -- ------------------------------
    -- ③ 収納代行会社以外の郵便局､銀行の入金先ﾄｰﾀﾙ表明細を作成する
    -- ------------------------------
    INSERT INTO AT_PaymentPlaceDetails(
        ReceiptSeq
       ,DepositDate
       ,ReceiptFlg
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,PaymentDestinationName
       ,PaymentDestinationDetails
       ,PaymentAmount
       ,PaymentPerson
       ,CheckingUseAmount
       ,CheckingClaimFee
       ,CheckingDamageInterestAmount
       ,CheckingAdditionalClaimFee
       ,ProcessingDate
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        rc.ReceiptSeq                           -- 入金Seq
       ,rc.ReceiptDate                          -- 入返金日
       ,0                                       -- 入返金ﾌﾗｸﾞ
       ,c1.Class3                               -- OEMID
       ,o.EnterpriseId                          -- 加盟店ID
       ,o.OrderSeq                              -- 注文Seq
       ,o.OrderId                               -- 注文ID (旧取引ID)
       ,CASE  rc.ReceiptClass
        WHEN 3 THEN c4.Class1
        WHEN 2 THEN (SELECT c.Class3
                     FROM   M_Code c
                     WHERE  c.CodeId  = 152
                     AND    c.Class1  = arc.AccountNumber
                     limit 1
                    )
        END                                     -- 入返金先
       ,c2.Class1                               -- 入返金先詳細
       ,rc.ReceiptAmount                        -- 入返金額
       ,c3.Class1                               -- 入金者
       ,rc.CheckingUseAmount                    -- 消込情報-利用額
       ,rc.CheckingClaimFee                     -- 消込情報-請求手数料
       ,rc.CheckingDamageInterestAmount         -- 消込情報-遅延損害金
       ,rc.CheckingAdditionalClaimFee           -- 消込情報-請求追加手数料
       ,CASE WHEN rc.ReceiptDate < v_BusinessDate THEN v_BusinessDate
        ELSE rc.ReceiptDate
        END                                     -- 集計日
       ,NOW()                                   -- 登録日時
       ,pi_user_id                              -- 登録者
       ,NOW()                                   -- 更新日時
       ,pi_user_id                              -- 更新者
       ,1                                       -- 有効ﾌﾗｸﾞ
    FROM
          T_ReceiptControl rc
    INNER JOIN AT_ReceiptControl arc
    ON    rc.ReceiptSeq       = arc.ReceiptSeq
    INNER JOIN T_Order o
    ON    o.OrderSeq        = rc.OrderSeq
    LEFT JOIN M_Code c1
    ON    c1.CodeId           = 160
    AND   c1.KeyCode          = IFNULL(o.OemId, 0)
    LEFT JOIN M_Code c2
    ON    c2.CodeId           = 156
    AND   c2.KeyCode          = arc.BankFlg
    LEFT JOIN M_Code c3
    ON    c3.CodeId           = 157
    AND   c3.KeyCode          = 1
    LEFT JOIN M_Code c4
    ON    c4.CodeId           = 151
    AND   c4.KeyCode          = rc.BranchBankId
    WHERE rc.DailySummaryFlg  = 0
    AND   rc.ValidFlg         = 1
    AND   rc.ReceiptAgentId   IS NULL
    AND   rc.ReceiptClass     IN (2, 3)
    AND   DATE_FORMAT( rc.ReceiptProcessDate, '%Y-%m-%d' ) <= v_BusinessDate
    ;

    -- ------------------------------
    -- ④ 加盟店入金履歴の入金先ﾄｰﾀﾙ表明細を作成する
    -- ------------------------------
    INSERT INTO AT_PaymentPlaceDetails(
        ReceiptSeq
       ,DepositDate
       ,ReceiptFlg
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,PaymentDestinationName
       ,PaymentDestinationDetails
       ,PaymentAmount
       ,PaymentPerson
       ,CheckingUseAmount
       ,CheckingClaimFee
       ,CheckingDamageInterestAmount
       ,CheckingAdditionalClaimFee
       ,ProcessingDate
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        erh.EntRcptSeq                          -- 入金Seq
       ,erh.ReceiptDate                         -- 入返金日
       ,0                                       -- 入返金ﾌﾗｸﾞ
       ,c1.Class3                               -- OEMID
       ,erh.EnterpriseId                        -- 加盟店ID
       ,NULL                                    -- 注文Seq
       ,NULL                                    -- 注文ID (旧取引ID)
       ,c4.Class1                               -- 入返金先
       ,c2.Class1                               -- 入返金先詳細
       ,erh.ReceiptAmount                       -- 入返金額
       ,c3.Class1                               -- 入金者
       ,NULL                                    -- 消込情報-利用額
       ,NULL                                    -- 消込情報-請求手数料
       ,NULL                                    -- 消込情報-遅延損害金
       ,NULL                                    -- 消込情報-請求追加手数料
       ,CASE WHEN erh.ReceiptDate < v_BusinessDate THEN v_BusinessDate
        ELSE erh.ReceiptDate
        END                                     -- 集計日
       ,NOW()                                   -- 登録日時
       ,pi_user_id                              -- 登録者
       ,NOW()                                   -- 更新日時
       ,pi_user_id                              -- 更新者
       ,1                                       -- 有効ﾌﾗｸﾞ
    FROM
          T_EnterpriseReceiptHistory erh
    INNER JOIN AT_EnterpriseReceiptHistory aerh
    ON    aerh.EntRcptSeq      = erh.EntRcptSeq
    INNER JOIN T_Enterprise e
    ON    e.EnterpriseId       = erh.EnterpriseId
    LEFT JOIN M_Code c1
    ON    c1.CodeId            = 160
    AND   c1.KeyCode           = IFNULL(e.OemId, 0)
    LEFT JOIN M_Code c2
    ON    c2.CodeId            = 156
    AND   c2.KeyCode           = 2
    LEFT JOIN M_Code c3
    ON    c3.CodeId            = 157
    AND   c3.KeyCode           = 2
    LEFT JOIN M_Code c4
    ON    c4.CodeId            = 167
    AND   c4.KeyCode           = aerh.ReceiptAmountSource
    WHERE aerh.DailySummaryFlg = 0
    AND   erh.ValidFlg         = 1
    AND   erh.ReceiptClass     = 1
    AND   DATE_FORMAT( erh.ReceiptProcessDate, '%Y-%m-%d' ) <= v_BusinessDate
    ;

    -- ------------------------------
    -- ⑤ 返金管理の入金先ﾄｰﾀﾙ表明細を作成する
    -- ------------------------------
    INSERT INTO AT_PaymentPlaceDetails(
        ReceiptSeq
       ,DepositDate
       ,ReceiptFlg
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,PaymentDestinationName
       ,PaymentDestinationDetails
       ,PaymentAmount
       ,PaymentPerson
       ,CheckingUseAmount
       ,CheckingClaimFee
       ,CheckingDamageInterestAmount
       ,CheckingAdditionalClaimFee
       ,ProcessingDate
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        rc.RepaySeq                             -- 入金Seq
       ,rc.RepayExpectedDate                    -- 入返金日
       ,1                                       -- 入返金ﾌﾗｸﾞ
       ,c1.Class3                               -- OEMID
       ,o.EnterpriseId                          -- 加盟店ID
       ,o.OrderSeq                              -- 注文Seq
       ,o.OrderId                               -- 注文ID (旧取引ID)
       ,c2.Class1                               -- 入返金先
       ,NULL                                    -- 入返金先詳細
       ,rc.RepayAmount * (-1)                   -- 入返金額
       ,c3.Class1                               -- 入金者
       ,NULL                                    -- 消込情報-利用額
       ,NULL                                    -- 消込情報-請求手数料
       ,NULL                                    -- 消込情報-遅延損害金
       ,NULL                                    -- 消込情報-請求追加手数料
       ,CASE WHEN rc.DecisionDate < v_BusinessDate THEN v_BusinessDate
        ELSE rc.DecisionDate
        END                                     -- 集計日
       ,NOW()                                   -- 登録日時
       ,pi_user_id                              -- 登録者
       ,NOW()                                   -- 更新日時
       ,pi_user_id                              -- 更新者
       ,1                                       -- 有効ﾌﾗｸﾞ
    FROM
          T_RepaymentControl rc
    INNER JOIN AT_RepaymentControl arc
    ON    arc.RepaySeq         = rc.RepaySeq
    LEFT JOIN T_ClaimControl cc
    ON    cc.ClaimId           = rc.ClaimId
    LEFT JOIN T_Order o
    ON    o.OrderSeq         = cc.OrderSeq
    LEFT JOIN M_Code c1
    ON    c1.CodeId            = 160
    AND   c1.KeyCode           = IFNULL(o.OemId, 0)
    LEFT JOIN M_Code c2
    ON    c2.CodeId            = 168
    AND   c2.KeyCode           = 0
    LEFT JOIN M_Code c3
    ON    c3.CodeId            = 157
    AND   c3.KeyCode           = 1
    WHERE arc.DailySummaryFlg  = 0
    AND   rc.ValidFlg          = 1
    AND   rc.RepayStatus       = 1
    AND   DATE_FORMAT( rc.DecisionDate, '%Y-%m-%d' ) <= v_BusinessDate
    ;

    -- ------------------------------
    -- ⑥ 入金先ﾄｰﾀﾙ表明細を更新する
    -- ------------------------------
BEGIN

    DECLARE cur1 CURSOR FOR
         SELECT ppd.Seq                     -- ｼｰｹﾝｽ
               ,rc.DepositDate              -- 入金予定日
         FROM
               AT_PaymentPlaceDetails ppd
         INNER JOIN T_ReceiptControl rc
         ON    rc.ReceiptSeq     = ppd.ReceiptSeq
         WHERE ppd.ValidFlg      = 1
         AND   ppd.ReceiptFlg    = 0
         AND   ppd.DepositDate   IS NULL;

    OPEN cur1;

    read_loop1: LOOP

        -- ｶｰｿﾙﾌｪｯﾁ
        FETCH cur1 INTO
            v_Seq
           ,v_DepositDate;

        IF v_done = 1 THEN leave read_loop1;
        END IF;

        IF v_DepositDate IS NOT NULL THEN
            UPDATE AT_PaymentPlaceDetails 
            SET  DepositDate    = v_DepositDate
                ,ProcessingDate = CASE WHEN v_DepositDate < v_BusinessDate THEN v_BusinessDate ELSE v_DepositDate END
                ,UpdateDate     = NOW()
                ,UpdateId       = pi_user_id
            WHERE Seq           = v_Seq;
        END IF;

    END LOOP;

    -- ｶｰｿﾙｸﾛｰｽﾞ
    CLOSE cur1;
END;

    -- ------------------------------
    -- ⑦ 入金先ﾄｰﾀﾙ表を作成する
    -- ------------------------------
    INSERT INTO AT_PaymentPlaceTotal(
        DailyMonthlyFlg
       ,ProcessingDate
       ,ReceiptDate
       ,ReceiptName
       ,PaymentDestinationName
       ,PaymentDestinationDetails
       ,PaymentAmount
       ,PaymentPerson
       ,ProcessDate
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        0                                       -- 日次･月次区分
       ,v_BusinessDate                          -- 処理日付
       ,ppd.DepositDate                         -- 入返金日
       ,c.Class1                                -- 入返金
       ,ppd.PaymentDestinationName              -- 入返金先
       ,ppd.PaymentDestinationDetails           -- 入返金先詳細
       ,SUM(IFNULL(ppd.PaymentAmount, 0))       -- 入返金額
       ,ppd.PaymentPerson                       -- 入金者
       ,ppd.ProcessingDate                      -- 集計日
       ,NOW()                                   -- 登録日時
       ,pi_user_id                              -- 登録者
       ,NOW()                                   -- 更新日時
       ,pi_user_id                              -- 更新者
       ,1                                       -- 有効ﾌﾗｸﾞ
    FROM
          AT_PaymentPlaceDetails ppd
    LEFT JOIN M_Code c
    ON    c.CodeId            = 150
    AND   c.KeyCode           = ppd.ReceiptFlg
    WHERE ppd.ValidFlg        = 1
    AND   ppd.ProcessingDate  = v_BusinessDate
    GROUP BY
          ppd.ProcessingDate
         ,ppd.DepositDate
         ,ppd.ReceiptFlg
         ,ppd.PaymentDestinationName
         ,ppd.PaymentDestinationDetails
         ,ppd.PaymentPerson
    ;

END
$$

DELIMITER ;
