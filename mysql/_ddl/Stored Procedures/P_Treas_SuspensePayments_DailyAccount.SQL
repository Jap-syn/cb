DROP PROCEDURE IF EXISTS P_Treas_SuspensePayments_DailyAccount;

DELIMITER $$

CREATE PROCEDURE P_Treas_SuspensePayments_DailyAccount( IN pi_user_id INT )
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名       ：  P_Treas_SuspensePayments_DailyAccount
 *
 * 概要             ：  仮払金日計
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
    DECLARE v_BusinessDate                          DATE;                                       -- 業務日付
    DECLARE v_AccountingMonth                       DATE;                                       -- 会計月
    DECLARE v_PaymentTarget_UseAmount               VARCHAR(160);                               -- 入金対象：OEM仮払金
    DECLARE v_PaymentTarget_DamageInterestAmount    VARCHAR(160);                               -- 入金対象：延滞利息
    DECLARE v_PaymentTarget_ClaimFee                VARCHAR(160);                               -- 入金対象：再発行手数料
    DECLARE v_PaymentTarget_ReceiptAmountDue        VARCHAR(160);                               -- 入金対象：OEM未収金
    DECLARE v_PaymentTarget_ReceiptAmountRece       VARCHAR(160);                               -- 入金対象：OEM売掛金
    DECLARE v_PaymentTarget_RepayAmount             VARCHAR(160);                               -- 入金対象：返金（OEM仮払金）
    DECLARE v_PaymentTarget_RepayAmountPayable      VARCHAR(160);                               -- 入金先：OEM未払金

    -- ------------------------------
    -- 変数初期化
    -- ------------------------------
    SET v_BusinessDate    = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'BusinessDate');     -- 業務日付
    SET v_AccountingMonth = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'AccountingMonth');  -- 会計月
    SET v_PaymentTarget_UseAmount            = F_GetCode(161, 1, 1);                            -- 入金対象：OEM仮払金
    SET v_PaymentTarget_DamageInterestAmount = F_GetCode(161, 2, 1);                            -- 入金対象：延滞利息
    SET v_PaymentTarget_ClaimFee             = F_GetCode(161, 3, 1);                            -- 入金対象：再発行手数料
    SET v_PaymentTarget_ReceiptAmountDue     = F_GetCode(161, 4, 1);                            -- 入金対象：OEM未収金
    SET v_PaymentTarget_ReceiptAmountRece    = F_GetCode(161, 6, 1);                            -- 入金対象：OEM売掛金
    SET v_PaymentTarget_RepayAmount          = F_GetCode(161, 8, 1);                            -- 入金対象：返金（OEM仮払金）
    SET v_PaymentTarget_RepayAmountPayable   = F_GetCode(161, 9, 1);                            -- 入金先：OEM未払金

    -- ------------------------------
    -- ﾜｰｸﾃｰﾌﾞﾙの初期化
    -- ------------------------------
    -- ①-0 仮払金日計ﾜｰｸをﾄﾗﾝｹｰﾄする
    TRUNCATE AW_SuspensePayments_DailyAccount;

    -- ------------------------------
    -- 消込用ワークテーブルにデータを追加
    -- ------------------------------
    TRUNCATE AW_SuspensePayments_DailyAccount2;
    
    INSERT INTO AW_SuspensePayments_DailyAccount2
    (
         ReceiptDate
        ,DepositDate
        ,ReceiptMonth
        ,CheckingUseAmount
        ,CheckingDamageInterestAmount
        ,CheckingClaimFee
        ,ReceiptAgentId
        ,BranchBankId
        ,AccountNumber
        ,ClassDetails
        ,ReceiptClass
        ,DailySummaryFlg
        ,ValidFlg
        ,OemId
        ,EnterpriseId
        ,OrderSeq
        ,OrderId
        ,ReceiptSeq
        ,ReceiptSeq2
        ,Rct_CancelFlg
        ,TargetFlg
    )
    SELECT
        rc.ReceiptDate                          -- 顧客入金日
       ,rc.DepositDate                          -- 入金予定日
       ,CASE WHEN DATE_FORMAT(rc.ReceiptDate, '%Y-%m-01') < v_AccountingMonth THEN v_AccountingMonth 
        ELSE DATE_FORMAT(rc.ReceiptDate, '%Y-%m-01')
        END ReceiptMonth                        -- 顧客入金月
       ,rc.CheckingUseAmount                    -- 消込情報-利用額
       ,rc.CheckingDamageInterestAmount         -- 消込情報-遅延損害金
       ,rc.CheckingClaimFee                     -- 消込情報-請求手数料
       ,rc.ReceiptAgentId                       -- 収納代行会社ID
       ,rc.BranchBankId                         -- 銀行支店ID
       ,arc.AccountNumber                       -- 口座番号(郵便)
       ,arc.ClassDetails                        -- 入金方法詳細
       ,rc.ReceiptClass                         -- 入金方法
       ,rc.DailySummaryFlg                      -- 日次更新ﾌﾗｸﾞ
       ,rc.ValidFlg                             -- 有効ﾌﾗｸﾞ
       ,IFNULL(e.OemId,0) AS OemId              -- OEMID
       ,o.EnterpriseId                          -- 加盟店ID
       ,o.OrderSeq                              -- 注文Seq
       ,o.OrderId                               -- 注文ID
       ,rc.ReceiptSeq                           -- 入金管理Seq
       ,rc.ReceiptSeq as ReceiptSeq2            -- 入金管理Seq
       ,arc.Rct_CancelFlg                       -- 入金取消フラグ
       ,1                                       -- 処理対象フラグ
    FROM  T_ReceiptControl rc
    INNER JOIN AT_ReceiptControl arc
    ON    rc.ReceiptSeq     = arc.ReceiptSeq
    INNER JOIN T_Order o
    ON    rc.OrderSeq       = o.OrderSeq
    AND   ( o.OemClaimTransDate IS NULL OR
            ( o.OemClaimTransDate > DATE(rc.ReceiptDate)      -- 債権移管日 <= 入金日の入金データは、CB分。入金日 < 債権移管日の入金データは、OEM分。
              AND   NOT ( DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01')  -- 月を跨いで入金処理がされた
                          AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01')) -- 既に会計締めデータが存在する
                        )
            )
          )
    INNER JOIN T_Enterprise e
    ON    o.EnterpriseId    = e.EnterpriseId
    INNER JOIN M_Code c
    ON    c.CodeId          = 160
    AND   c.KeyCode         = IFNULL(e.OemId, 0)
    WHERE c.Class1         <> 0
    AND   DATE_FORMAT( rc.ReceiptProcessDate, '%Y-%m-%d' ) <= v_BusinessDate 
    -- ﾃﾝﾎﾟﾗﾘｰﾃｰﾌﾞﾙの作成に時間がかかるようなので、日次更新ﾌﾗｸﾞの条件を追加 ← これにより全件から1日分へ絞られる！！！
    AND   rc.DailySummaryFlg = 0
    AND   arc.Rct_CancelFlg = 0
    
    UNION ALL
    
    SELECT
        rc2.ReceiptDate                          -- 顧客入金日
       ,rc2.DepositDate                          -- 入金予定日
       ,CASE WHEN DATE_FORMAT(rc2.ReceiptDate, '%Y-%m-01') < v_AccountingMonth THEN v_AccountingMonth 
        ELSE DATE_FORMAT(rc2.ReceiptDate, '%Y-%m-01')
        END ReceiptMonth                        -- 顧客入金月
       ,rc2.CheckingUseAmount * -1               -- 消込情報-利用額
       ,rc2.CheckingDamageInterestAmount * -1    -- 消込情報-遅延損害金
       ,rc2.CheckingClaimFee * -1                -- 消込情報-請求手数料
       ,rc2.ReceiptAgentId                       -- 収納代行会社ID
       ,rc2.BranchBankId                         -- 銀行支店ID
       ,arc2.AccountNumber                       -- 口座番号(郵便)
       ,arc2.ClassDetails                        -- 入金方法詳細
       ,rc2.ReceiptClass                         -- 入金方法
       ,rc.DailySummaryFlg                      -- 日次更新ﾌﾗｸﾞ
       ,rc2.ValidFlg                             -- 有効ﾌﾗｸﾞ
       ,IFNULL(e.OemId,0) AS OemId              -- OEMID
       ,o.EnterpriseId                          -- 加盟店ID
       ,o.OrderSeq                              -- 注文Seq
       ,o.OrderId                               -- 注文ID
       ,rc.ReceiptSeq                           -- 入金管理Seq
       ,rc2.ReceiptSeq as ReceiptSeq2           -- 入金管理Seq
       ,arc.Rct_CancelFlg                       -- 入金取消フラグ
       ,1                                       -- 処理対象フラグ
    FROM  T_ReceiptControl rc
    INNER JOIN AT_ReceiptControl arc
    ON    rc.ReceiptSeq     = arc.ReceiptSeq
    INNER JOIN T_ReceiptControl rc2
    ON rc2.ReceiptSeq < rc.ReceiptSeq
    AND rc2.OrderSeq = rc.OrderSeq
    INNER JOIN AT_ReceiptControl arc2
    ON rc2.ReceiptSeq = arc2.ReceiptSeq
    INNER JOIN T_Order o
    ON    rc.OrderSeq       = o.OrderSeq
    AND   ( o.OemClaimTransDate IS NULL OR
            ( o.OemClaimTransDate > DATE(rc.ReceiptDate)      -- 債権移管日 <= 入金日の入金データは、CB分。入金日 < 債権移管日の入金データは、OEM分。
              AND   NOT ( DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01')  -- 月を跨いで入金処理がされた
                          AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01')) -- 既に会計締めデータが存在する
                        )
            )
          )
    INNER JOIN T_Enterprise e
    ON    o.EnterpriseId    = e.EnterpriseId
    INNER JOIN M_Code c
    ON    c.CodeId          = 160
    AND   c.KeyCode         = IFNULL(e.OemId, 0)
    WHERE c.Class1         <> 0
    AND   DATE_FORMAT( rc.ReceiptProcessDate, '%Y-%m-%d' ) <= v_BusinessDate 
    -- ﾃﾝﾎﾟﾗﾘｰﾃｰﾌﾞﾙの作成に時間がかかるようなので、日次更新ﾌﾗｸﾞの条件を追加 ← これにより全件から1日分へ絞られる！！！
    AND   rc.DailySummaryFlg = 0
    AND   arc.Rct_CancelFlg = 1
    AND   rc2.ReceiptSeq BETWEEN (SELECT IFNULL(MAX(tmp.ReceiptSeq), 0) FROM T_ReceiptControl tmp, AT_ReceiptControl tmp2 WHERE tmp.ReceiptSeq = tmp2.ReceiptSeq AND tmp.ReceiptSeq < rc.ReceiptSeq AND tmp.OrderSeq = rc.OrderSeq AND tmp2.Rct_CancelFlg = 1) + 1 AND rc.ReceiptSeq - 1
    ORDER BY ReceiptSeq, ReceiptSeq2
    ;
    
    BEGIN
        
        -- AW_SuspensePayments_DailyAccount2
        DECLARE v_AW2_Seq                               BIGINT;                                     -- シーケンス
        DECLARE v_AW2_ReceiptDate                       DATE;                                       -- 入金日
        DECLARE v_AW2_CheckingUseAmount                 BIGINT;                                     -- 消込情報－利用額
        DECLARE v_AW2_CheckingDamageInterestAmount      BIGINT;                                     -- 消込情報－遅延損害金
        DECLARE v_AW2_CheckingClaimFee                  BIGINT;                                     -- 消込情報－請求手数料
        DECLARE v_AW2_ReceiptAgentId                    BIGINT;                                     -- 収納代行会社ID
        DECLARE v_AW2_OrderSeq                          BIGINT;                                     -- 注文SEQ
        
        DECLARE v_AW2_HitSeq                            BIGINT;                                     -- シーケンス
        
        -- NO_DATA_FOUND ｲﾍﾞﾝﾄﾊﾝﾄﾞﾗ用変数
        DECLARE no_data_found INT DEFAULT 1;
        
        -- ｶｰｿﾙ宣言
        DECLARE l_cur CURSOR FOR
            SELECT Seq
                  ,ReceiptDate
                  ,CheckingUseAmount
                  ,CheckingDamageInterestAmount
                  ,CheckingClaimFee
                  ,IFNULL(ReceiptAgentId, -1)
                  ,OrderSeq
            FROM AW_SuspensePayments_DailyAccount2
            WHERE Rct_CancelFlg = 1
            ORDER BY Seq
        ;
        
        -- NO_DATA_FOUND 用ｲﾍﾞﾝﾄﾊﾝﾄﾞﾗ宣言
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_data_found = 0;
        
        -- ｶｰｿﾙｵｰﾌﾟﾝ
        OPEN    l_cur;
            -- 最初のｶｰｿﾙをFetch
            FETCH l_cur INTO v_AW2_Seq, v_AW2_ReceiptDate, v_AW2_CheckingUseAmount, v_AW2_CheckingDamageInterestAmount, v_AW2_CheckingClaimFee, v_AW2_ReceiptAgentId, v_AW2_OrderSeq;
            
            -- whileﾙｰﾌﾟで処理する
            WHILE no_data_found != 0  DO
                
                SELECT IFNULL(MIN(Seq), -1)
                INTO   v_AW2_HitSeq
                FROM   AW_SuspensePayments_DailyAccount2
                WHERE  1 = 1
                AND    Rct_CancelFlg = 0
                AND    ReceiptDate = v_AW2_ReceiptDate
                AND    CheckingUseAmount = (v_AW2_CheckingUseAmount * -1)
                AND    CheckingDamageInterestAmount = (v_AW2_CheckingDamageInterestAmount * -1)
                AND    CheckingClaimFee = (v_AW2_CheckingClaimFee * -1)
                AND    IFNULL(ReceiptAgentId, -1) = v_AW2_ReceiptAgentId
                AND    OrderSeq = v_AW2_OrderSeq
                AND    TargetFlg = 1
                ;
                
                IF v_AW2_HitSeq > 0 THEN
                    -- 該当するデータが存在する場合、消し込みを行う
                    UPDATE AW_SuspensePayments_DailyAccount2 SET TargetFlg = 0 WHERE Seq = v_AW2_HitSeq;
                    UPDATE AW_SuspensePayments_DailyAccount2 SET TargetFlg = 0 WHERE Seq = v_AW2_Seq;
                END IF;
                
                -- 次のｶｰｿﾙをFetch
                FETCH l_cur INTO v_AW2_Seq, v_AW2_ReceiptDate, v_AW2_CheckingUseAmount, v_AW2_CheckingDamageInterestAmount, v_AW2_CheckingClaimFee, v_AW2_ReceiptAgentId, v_AW2_OrderSeq;
            END WHILE;
        
        -- ｶｰｿﾙｸﾛｰｽﾞ
        CLOSE   l_cur;
    END;
    
    -- ------------------------------
    -- 入金管理より作成
    -- ------------------------------
    -- 入金管理.注文Seq=注文.注文Seqで取得した注文.加盟店ID=加盟店.加盟店IDで取得した加盟店.OEMIDでｺｰﾄﾞﾏｽﾀ(160).区分1=0(直営)
    CREATE TEMPORARY TABLE TMP_ReceiptControl
    SELECT 
         ReceiptDate
        ,DepositDate
        ,ReceiptMonth
        ,CheckingUseAmount
        ,CheckingDamageInterestAmount
        ,CheckingClaimFee
        ,ReceiptAgentId
        ,BranchBankId
        ,AccountNumber
        ,ClassDetails
        ,ReceiptClass
        ,DailySummaryFlg
        ,ValidFlg
        ,OemId
        ,EnterpriseId
        ,OrderSeq
        ,OrderId
        ,ReceiptSeq
    FROM AW_SuspensePayments_DailyAccount2
    WHERE TargetFlg = 1
    ;

    -- ①-1 OEM分&収納代行入金(預金-銀行)&消費者未収金(利用額の入金)&会計未集計 の入金管理より仮払金日計ﾜｰｸを出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,rc.ReceiptMonth                         -- 会計月
       ,rc.ReceiptDate                          -- 支払日
       ,rc.DepositDate                          -- 入金日
       ,c.Class3                                -- 入金先
       ,v_PaymentTarget_UseAmount               -- 入金対象
       ,1                                       -- 対象件数
       ,rc.CheckingUseAmount                    -- 金額
       ,rc.OemId                                -- OEMID
       ,rc.EnterpriseId                         -- 加盟店ID
       ,rc.OrderSeq                             -- 注文Seq
       ,rc.OrderId                              -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,rc.ReceiptSeq                           -- 入金管理Seq
    FROM  TMP_ReceiptControl rc
    LEFT  JOIN M_Code c
    ON    c.CodeId              = 101
    AND   c.KeyCode             = rc.ReceiptAgentId
    WHERE
          rc.DailySummaryFlg    = 0
    AND   rc.ValidFlg           = 1
    AND   rc.ReceiptAgentId     IS NOT NULL 
    AND   rc.CheckingUseAmount <> 0
    ;

    -- ①-2 OEM分&収納代行入金(預金-銀行)&消延滞利息&会計未集計 の入金管理より仮払金日計ﾜｰｸを出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,rc.ReceiptMonth                         -- 会計月
       ,rc.ReceiptDate                          -- 支払日
       ,rc.DepositDate                          -- 入金日
       ,c.Class3                                -- 入金先
       ,v_PaymentTarget_DamageInterestAmount    -- 入金対象
       ,1                                       -- 対象件数
       ,rc.CheckingDamageInterestAmount         -- 金額
       ,rc.OemId                                -- OEMID
       ,rc.EnterpriseId                         -- 加盟店ID
       ,rc.OrderSeq                             -- 注文Seq
       ,rc.OrderId                              -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,rc.ReceiptSeq                           -- 入金管理Seq
    FROM  TMP_ReceiptControl rc
    LEFT  JOIN M_Code c
    ON    c.CodeId              = 101
    AND   c.KeyCode             = rc.ReceiptAgentId
    WHERE
          rc.DailySummaryFlg    = 0
    AND   rc.ValidFlg           = 1
    AND   rc.ReceiptAgentId     IS NOT NULL
    AND   rc.CheckingDamageInterestAmount  <> 0
    ;

    -- ①-3 OEM分&収納代行入金(預金-銀行)&再発行手数料&会計未集計 の入金管理より仮払金日計ﾜｰｸを出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,rc.ReceiptMonth                         -- 会計月
       ,rc.ReceiptDate                          -- 支払日
       ,rc.DepositDate                          -- 入金日
       ,c.Class3                                -- 入金先
       ,v_PaymentTarget_ClaimFee                -- 入金対象
       ,1                                       -- 対象件数
       ,rc.CheckingClaimFee                     -- 金額
       ,rc.OemId                                -- OEMID
       ,rc.EnterpriseId                         -- 加盟店ID
       ,rc.OrderSeq                             -- 注文Seq
       ,rc.OrderId                              -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,rc.ReceiptSeq                           -- 入金管理Seq
    FROM  TMP_ReceiptControl rc
    LEFT  JOIN M_Code c
    ON    c.CodeId              = 101
    AND   c.KeyCode             = rc.ReceiptAgentId
    WHERE
          rc.DailySummaryFlg    = 0
    AND   rc.ValidFlg           = 1
    AND   rc.ReceiptAgentId     IS NOT NULL
    AND   rc.CheckingClaimFee  <> 0
    ;

    -- ②-1 OEM分&銀行(預金-銀行)&消費者未収金(利用額の入金)&会計未集計 の入金管理より仮払金日計ﾜｰｸを出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,rc.ReceiptMonth                         -- 会計月
       ,rc.ReceiptDate                          -- 支払日
       ,rc.ReceiptDate                          -- 入金日
       ,c.Class3                                -- 入金先
       ,v_PaymentTarget_UseAmount               -- 入金対象
       ,1                                       -- 対象件数
       ,rc.CheckingUseAmount                    -- 金額
       ,rc.OemId                                -- OEMID
       ,rc.EnterpriseId                         -- 加盟店ID
       ,rc.OrderSeq                             -- 注文Seq
       ,rc.OrderId                              -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,rc.ReceiptSeq                           -- 入金管理Seq
    FROM  TMP_ReceiptControl rc
    LEFT  JOIN M_Code c
    ON    c.CodeId              = 151
    AND   c.KeyCode             = rc.BranchBankId
    WHERE
          rc.DailySummaryFlg    = 0
    AND   rc.ValidFlg           = 1
    AND   rc.ReceiptAgentId     IS NULL
    AND   rc.ReceiptClass       = 3
    AND   rc.CheckingUseAmount <> 0
    ;

    -- ②-2 OEM分&銀行(預金-銀行)&消延滞利息&会計未集計 の入金管理より仮払金日計ﾜｰｸを出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,rc.ReceiptMonth                         -- 会計月
       ,rc.ReceiptDate                          -- 支払日
       ,rc.ReceiptDate                          -- 入金日
       ,c.Class3                                -- 入金先
       ,v_PaymentTarget_DamageInterestAmount    -- 入金対象
       ,1                                       -- 対象件数
       ,rc.CheckingDamageInterestAmount         -- 金額
       ,rc.OemId                                -- OEMID
       ,rc.EnterpriseId                         -- 加盟店ID
       ,rc.OrderSeq                             -- 注文Seq
       ,rc.OrderId                              -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,rc.ReceiptSeq                           -- 入金管理Seq
    FROM  TMP_ReceiptControl rc
    LEFT  JOIN M_Code c
    ON    c.CodeId              = 151
    AND   c.KeyCode             = rc.BranchBankId
    WHERE
          rc.DailySummaryFlg    = 0
    AND   rc.ValidFlg           = 1
    AND   rc.ReceiptAgentId     IS NULL
    AND   rc.ReceiptClass       = 3
    AND   rc.CheckingDamageInterestAmount  <> 0
    ;

    -- ②-3 OEM分&銀行(預金-銀行)&再発行手数料&会計未集計 の入金管理より仮払金日計ﾜｰｸを出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,rc.ReceiptMonth                         -- 会計月
       ,rc.ReceiptDate                          -- 支払日
       ,rc.ReceiptDate                          -- 入金日
       ,c.Class3                                -- 入金先
       ,v_PaymentTarget_ClaimFee                -- 入金対象
       ,1                                       -- 対象件数
       ,rc.CheckingClaimFee                     -- 金額
       ,rc.OemId                                -- OEMID
       ,rc.EnterpriseId                         -- 加盟店ID
       ,rc.OrderSeq                             -- 注文Seq
       ,rc.OrderId                              -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,rc.ReceiptSeq                           -- 入金管理Seq
    FROM  TMP_ReceiptControl rc
    LEFT  JOIN M_Code c
    ON    c.CodeId              = 151
    AND   c.KeyCode             = rc.BranchBankId
    WHERE
          rc.DailySummaryFlg    = 0
    AND   rc.ValidFlg           = 1
    AND   rc.ReceiptAgentId     IS NULL
    AND   rc.ReceiptClass       = 3
    AND   rc.CheckingClaimFee  <> 0
    ;

    -- ③-1 OEM分&郵便&消費者未収金(利用額の入金)&会計未集計 の入金管理より仮払金日計ﾜｰｸを出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,rc.ReceiptMonth                         -- 会計月
       ,rc.ReceiptDate                          -- 支払日
       ,rc.ReceiptDate                          -- 入金日
       ,c.Class3                                -- 入金先
       ,v_PaymentTarget_UseAmount               -- 入金対象
       ,1                                       -- 対象件数
       ,rc.CheckingUseAmount                    -- 金額
       ,rc.OemId                                -- OEMID
       ,rc.EnterpriseId                         -- 加盟店ID
       ,rc.OrderSeq                             -- 注文Seq
       ,rc.OrderId                              -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,rc.ReceiptSeq                           -- 入金管理Seq
    FROM  TMP_ReceiptControl rc
    LEFT  JOIN M_Code c
    ON    c.CodeId              = 166
    AND   c.Class1              = rc.AccountNumber
    WHERE
          rc.DailySummaryFlg    = 0
    AND   rc.ValidFlg           = 1
    AND   rc.ReceiptAgentId     IS NULL
    AND   rc.ReceiptClass       = 2
    AND   rc.CheckingUseAmount <> 0
    ;

    -- ③-2 OEM分&郵便&消延滞利息&会計未集計 の入金管理より仮払金日計ﾜｰｸを出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,rc.ReceiptMonth                         -- 会計月
       ,rc.ReceiptDate                          -- 支払日
       ,rc.ReceiptDate                          -- 入金日
       ,c.Class3                                -- 入金先
       ,v_PaymentTarget_DamageInterestAmount    -- 入金対象
       ,1                                       -- 対象件数
       ,rc.CheckingDamageInterestAmount         -- 金額
       ,rc.OemId                                -- OEMID
       ,rc.EnterpriseId                         -- 加盟店ID
       ,rc.OrderSeq                             -- 注文Seq
       ,rc.OrderId                              -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,rc.ReceiptSeq                           -- 入金管理Seq
    FROM  TMP_ReceiptControl rc
    LEFT  JOIN M_Code c
    ON    c.CodeId              = 166
    AND   c.Class1              = rc.AccountNumber
    WHERE
          rc.DailySummaryFlg    = 0
    AND   rc.ValidFlg           = 1
    AND   rc.ReceiptAgentId     IS NULL
    AND   rc.ReceiptClass       = 2
    AND   rc.CheckingDamageInterestAmount  <> 0
    ;

    -- ③-3 OEM分&郵便&再発行手数料&会計未集計 の入金管理より仮払金日計ﾜｰｸを出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,rc.ReceiptMonth                         -- 会計月
       ,rc.ReceiptDate                          -- 支払日
       ,rc.ReceiptDate                          -- 入金日
       ,c.Class3                                -- 入金先
       ,v_PaymentTarget_ClaimFee                -- 入金対象
       ,1                                       -- 対象件数
       ,rc.CheckingClaimFee                     -- 金額
       ,rc.OemId                                -- OEMID
       ,rc.EnterpriseId                         -- 加盟店ID
       ,rc.OrderSeq                             -- 注文Seq
       ,rc.OrderId                              -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,rc.ReceiptSeq                           -- 入金管理Seq
    FROM  TMP_ReceiptControl rc
    LEFT  JOIN M_Code c
    ON    c.CodeId              = 166
    AND   c.Class1              = rc.AccountNumber
    WHERE
          rc.DailySummaryFlg    = 0
    AND   rc.ValidFlg           = 1
    AND   rc.ReceiptAgentId     IS NULL
    AND   rc.ReceiptClass       = 2
    AND   rc.CheckingClaimFee  <> 0
    ;

    -- ④-1 OEM分&その他&消費者未収金(利用額の入金)&会計未集計 の入金管理より仮払金日計ﾜｰｸを出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,rc.ReceiptMonth                         -- 会計月
       ,rc.ReceiptDate                          -- 支払日
       ,rc.ReceiptDate                          -- 入金日
       ,c.KeyContent                            -- 入金先
       ,v_PaymentTarget_UseAmount               -- 入金対象
       ,1                                       -- 対象件数
       ,rc.CheckingUseAmount                    -- 金額
       ,rc.OemId                                -- OEMID
       ,rc.EnterpriseId                         -- 加盟店ID
       ,rc.OrderSeq                             -- 注文Seq
       ,rc.OrderId                              -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,rc.ReceiptSeq                           -- 入金管理Seq
    FROM  TMP_ReceiptControl rc
    LEFT  JOIN M_Code c
    ON    c.CodeId              = 155
    AND   c.KeyCode             = rc.ClassDetails
    WHERE
          rc.DailySummaryFlg    = 0
    AND   rc.ValidFlg           = 1
    AND   rc.ReceiptAgentId     IS NULL
    AND   rc.ReceiptClass       = 9
    AND   rc.CheckingUseAmount <> 0
    ;

    -- ④-2 OEM分&その他&消延滞利息&会計未集計 の入金管理より仮払金日計ﾜｰｸを出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,rc.ReceiptMonth                         -- 会計月
       ,rc.ReceiptDate                          -- 支払日
       ,rc.ReceiptDate                          -- 入金日
       ,c.KeyContent                            -- 入金先
       ,v_PaymentTarget_DamageInterestAmount    -- 入金対象
       ,1                                       -- 対象件数
       ,rc.CheckingDamageInterestAmount         -- 金額
       ,rc.OemId                                -- OEMID
       ,rc.EnterpriseId                         -- 加盟店ID
       ,rc.OrderSeq                             -- 注文Seq
       ,rc.OrderId                              -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,rc.ReceiptSeq                           -- 入金管理Seq
    FROM  TMP_ReceiptControl rc
    LEFT  JOIN M_Code c
    ON    c.CodeId              = 155
    AND   c.KeyCode             = rc.ClassDetails
    WHERE
          rc.DailySummaryFlg    = 0
    AND   rc.ValidFlg           = 1
    AND   rc.ReceiptAgentId     IS NULL
    AND   rc.ReceiptClass       = 9
    AND   rc.CheckingDamageInterestAmount  <> 0
    ;

    -- ④-3 OEM分&その他&再発行手数料&会計未集計 の入金管理より仮払金日計ﾜｰｸを出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,rc.ReceiptMonth                         -- 会計月
       ,rc.ReceiptDate                          -- 支払日
       ,rc.ReceiptDate                          -- 入金日
       ,c.KeyContent                            -- 入金先
       ,v_PaymentTarget_ClaimFee                -- 入金対象
       ,1                                       -- 対象件数
       ,rc.CheckingClaimFee                     -- 金額
       ,rc.OemId                                -- OEMID
       ,rc.EnterpriseId                         -- 加盟店ID
       ,rc.OrderSeq                             -- 注文Seq
       ,rc.OrderId                              -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,rc.ReceiptSeq                           -- 入金管理Seq
    FROM  TMP_ReceiptControl rc
    LEFT  JOIN M_Code c
    ON    c.CodeId              = 155
    AND   c.KeyCode             = rc.ClassDetails
    WHERE
          rc.DailySummaryFlg    = 0
    AND   rc.ValidFlg           = 1
    AND   rc.ReceiptAgentId     IS NULL
    AND   rc.ReceiptClass       = 9
    AND   rc.CheckingClaimFee  <> 0
    ;

    DROP TABLE TMP_ReceiptControl;

    -- ------------------------------
    -- 雑収入･雑損失管理より作成
    -- ------------------------------
    -- 雑収入･雑損失管理.雑収入･雑損失科目より取得したｺｰﾄﾞﾏｽﾀ(96).区分2=1(会計対象)
    -- 雑収入･雑損失管理.雑収入･注文SEQ=注文.注文Seq で取得した注文.加盟店ID=加盟店.加盟店IDで取得した加盟店.OEMIDでｺｰﾄﾞﾏｽﾀ(160).区分1=0(直営)
    -- 雑収入･雑損失管理.雑収入･雑損失科目より取得したｺｰﾄﾞﾏｽﾀ(165).区分1より入金先(勘定科目)を取得
    CREATE TEMPORARY TABLE TMP_SundryControl
    SELECT
        sc.ProcessDate                          -- 発生日
       ,CASE WHEN DATE_FORMAT(sc.ProcessDate, '%Y-%m-01') < v_AccountingMonth THEN v_AccountingMonth
        ELSE DATE_FORMAT(sc.ProcessDate, '%Y-%m-01')
        END ProcessMonth                        -- 発生月
       ,sc.SundryType                           -- 種類(雑収入/雑損失)
       ,sc.CheckingUseAmount                    -- 消込情報-利用額
       ,sc.CheckingDamageInterestAmount         -- 消込情報-遅延損害金
       ,sc.CheckingClaimFee                     -- 消込情報-請求手数料
       ,sc.DailySummaryFlg                      -- 日次更新ﾌﾗｸﾞ
       ,sc.ValidFlg                             -- 有効ﾌﾗｸﾞ
       ,c3.Class1 PaymentAccountTitle           -- 入金先(勘定科目)
       ,IFNULL(e.OemId,0) AS OemId              -- OEMID
       ,o.EnterpriseId                          -- 加盟店ID
       ,o.OrderSeq                              -- 注文Seq
       ,o.OrderId                               -- 注文ID
    FROM  T_SundryControl sc
    INNER JOIN M_Code c1
    ON    c1.CodeId         = 96
    AND   c1.KeyCode        = sc.SundryClass
    INNER JOIN T_Order o
    ON    sc.OrderSeq       = o.OrderSeq
    AND   ( o.OemClaimTransDate IS NULL OR 
            o.OemClaimTransDate > sc.ProcessDate
          )
    INNER JOIN T_Enterprise e
    ON    o.EnterpriseId    = e.EnterpriseId
    INNER JOIN M_Code c2
    ON    c2.CodeId         = 160
    AND   c2.KeyCode        = IFNULL(e.OemId,0)
    LEFT  JOIN M_Code c3
    ON    c3.CodeId         = 165
    AND   c3.KeyCode        = sc.SundryClass
    WHERE c1.Class2         = 1
    AND   c2.Class1        <> 0
    AND   sc.ProcessDate   <= v_BusinessDate
    -- ﾃﾝﾎﾟﾗﾘｰﾃｰﾌﾞﾙの作成に時間がかかるようなので、日次更新ﾌﾗｸﾞの条件を追加 ← これにより全件から1日分へ絞られる！！！
    AND   sc.DailySummaryFlg = 0
    ;

    -- ⑤-1 OEM分の雑収入･雑損失管理より雑収入を仮払金日計ﾜｰｸを出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,sc.ProcessMonth                         -- 会計月
       ,sc.ProcessDate                          -- 支払日
       ,sc.ProcessDate                          -- 入金日
       ,sc.PaymentAccountTitle                  -- 入金先
       ,v_PaymentTarget_UseAmount               -- 入金対象
       ,1                                       -- 対象件数
       ,sc.CheckingUseAmount * -1               -- 金額
       ,sc.OemId                                -- OEMID
       ,sc.EnterpriseId                         -- 加盟店ID
       ,sc.OrderSeq                             -- 注文Seq
       ,sc.OrderId                              -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,NULL                                    -- 入金管理Seq
    FROM  TMP_SundryControl sc
    WHERE
          sc.DailySummaryFlg    = 0
    AND   sc.ValidFlg           = 1
    AND   sc.SundryType         = 0
    AND   sc.CheckingUseAmount <> 0
    ;

    -- ⑤-2 OEM分の雑収入･雑損失管理より雑収入を仮払金日計ﾜｰｸを出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,sc.ProcessMonth                         -- 会計月
       ,sc.ProcessDate                          -- 支払日
       ,sc.ProcessDate                          -- 入金日
       ,sc.PaymentAccountTitle                  -- 入金先
       ,v_PaymentTarget_DamageInterestAmount    -- 入金対象
       ,1                                       -- 対象件数
       ,sc.CheckingDamageInterestAmount * -1    -- 金額
       ,sc.OemId                                -- OEMID
       ,sc.EnterpriseId                         -- 加盟店ID
       ,sc.OrderSeq                             -- 注文Seq
       ,sc.OrderId                              -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,NULL                                    -- 入金管理Seq
    FROM  TMP_SundryControl sc
    WHERE
          sc.DailySummaryFlg    = 0
    AND   sc.ValidFlg           = 1
    AND   sc.SundryType         = 0
    AND   sc.CheckingDamageInterestAmount <> 0
    ;

    -- ⑤-3 OEM分の雑収入･雑損失管理より雑収入を仮払金日計ﾜｰｸを出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,sc.ProcessMonth                         -- 会計月
       ,sc.ProcessDate                          -- 支払日
       ,sc.ProcessDate                          -- 入金日
       ,sc.PaymentAccountTitle                  -- 入金先
       ,v_PaymentTarget_ClaimFee                -- 入金対象
       ,1                                       -- 対象件数
       ,sc.CheckingClaimFee * -1                -- 金額
       ,sc.OemId                                -- OEMID
       ,sc.EnterpriseId                         -- 加盟店ID
       ,sc.OrderSeq                             -- 注文Seq
       ,sc.OrderId                              -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,NULL                                    -- 入金管理Seq
    FROM  TMP_SundryControl sc
    WHERE
          sc.DailySummaryFlg    = 0
    AND   sc.ValidFlg           = 1
    AND   sc.SundryType         = 0
    AND   sc.CheckingClaimFee  <> 0
    ;

    -- ⑤-4 雑収入･雑損失管理より雑損失･貸倒を仮払金日計ﾜｰｸを出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,sc.ProcessMonth                         -- 会計月
       ,sc.ProcessDate                          -- 支払日
       ,sc.ProcessDate                          -- 入金日
       ,sc.PaymentAccountTitle                  -- 入金先
       ,v_PaymentTarget_UseAmount               -- 入金対象
       ,1                                       -- 対象件数
       ,sc.CheckingUseAmount                    -- 金額
       ,sc.OemId                                -- OEMID
       ,sc.EnterpriseId                         -- 加盟店ID
       ,sc.OrderSeq                             -- 注文Seq
       ,sc.OrderId                              -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,NULL                                    -- 入金管理Seq
    FROM  TMP_SundryControl sc
    WHERE
          sc.DailySummaryFlg    = 0
    AND   sc.ValidFlg           = 1
    AND   sc.SundryType         = 1
    AND   sc.CheckingUseAmount <> 0
    ;

    -- ⑤-5 雑収入･雑損失管理より雑損失･貸倒を仮払金日計ﾜｰｸを出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,sc.ProcessMonth                         -- 会計月
       ,sc.ProcessDate                          -- 支払日
       ,sc.ProcessDate                          -- 入金日
       ,sc.PaymentAccountTitle                  -- 入金先
       ,v_PaymentTarget_DamageInterestAmount    -- 入金対象
       ,1                                       -- 対象件数
       ,sc.CheckingDamageInterestAmount         -- 金額
       ,sc.OemId                                -- OEMID
       ,sc.EnterpriseId                         -- 加盟店ID
       ,sc.OrderSeq                             -- 注文Seq
       ,sc.OrderId                              -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,NULL                                    -- 入金管理Seq
    FROM  TMP_SundryControl sc
    WHERE
          sc.DailySummaryFlg    = 0
    AND   sc.ValidFlg           = 1
    AND   sc.SundryType         = 1
    AND   sc.CheckingDamageInterestAmount <> 0
    ;

    -- ⑤-6 雑収入･雑損失管理より雑損失･貸倒を仮払金日計ﾜｰｸを出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,sc.ProcessMonth                         -- 会計月
       ,sc.ProcessDate                          -- 支払日
       ,sc.ProcessDate                          -- 入金日
       ,sc.PaymentAccountTitle                  -- 入金先
       ,v_PaymentTarget_ClaimFee                -- 入金対象
       ,1                                       -- 対象件数
       ,sc.CheckingClaimFee                     -- 金額
       ,sc.OemId                                -- OEMID
       ,sc.EnterpriseId                         -- 加盟店ID
       ,sc.OrderSeq                             -- 注文Seq
       ,sc.OrderId                              -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,NULL                                    -- 入金管理Seq
    FROM  TMP_SundryControl sc
    WHERE
          sc.DailySummaryFlg    = 0
    AND   sc.ValidFlg           = 1
    AND   sc.SundryType         = 1
    AND   sc.CheckingClaimFee  <> 0
    ;

    DROP TABLE TMP_SundryControl;

    -- ------------------------------
    -- 返金管理より作成
    -- ------------------------------
    -- ⑥-1 OEM分の返金管理より出金(返金)を出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,CASE WHEN DATE_FORMAT(rc.DecisionDate, '%Y-%m-01') < v_AccountingMonth THEN v_AccountingMonth
        ELSE DATE_FORMAT(rc.DecisionDate, '%Y-%m-01')
        END                                     -- 会計月
       ,rc.DecisionDate                         -- 支払日
       ,rc.DecisionDate                         -- 入金日
       ,c2.Class2                               -- 入金先
       ,v_PaymentTarget_RepayAmount             -- 入金対象
       ,1                                       -- 対象件数
       ,rc.RepayAmount * -1                     -- 金額
       ,IFNULL(e.OemId, 0)                      -- OEMID
       ,o.EnterpriseId                          -- 加盟店ID
       ,o.OrderSeq                              -- 注文Seq
       ,o.OrderId                               -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,NULL                                    -- 入金管理Seq
    FROM  T_RepaymentControl rc
    INNER JOIN AT_RepaymentControl arc
    ON    arc.RepaySeq          = rc.RepaySeq
    INNER JOIN T_ClaimControl cc
    ON    rc.ClaimId            = cc.ClaimId
    INNER JOIN T_Order o
    ON    cc.OrderSeq           = o.OrderSeq
    AND   ( o.OemClaimTransDate IS NULL OR 
            o.OemClaimTransDate > DATE_FORMAT( rc.DecisionDate, '%Y-%m-%d' )
          )
    INNER JOIN T_Enterprise e
    ON    o.EnterpriseId        = e.EnterpriseId
    INNER JOIN M_Code c1
    ON    c1.CodeId             = 160
    AND   c1.KeyCode            = IFNULL(e.OemId, 0)
    LEFT  JOIN M_Code c2
    ON    c2.CodeId             = 151
    AND   c2.KeyCode            = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'RepayBankId')
    WHERE
          arc.DailySummaryFlg   = 0
    AND   rc.ValidFlg           = 1
    AND   rc.RepayStatus        = 1
    AND   rc.RepayAmount       <> 0
    AND   c1.Class1            <> 0
    AND   DATE_FORMAT( rc.DecisionDate, '%Y-%m-%d' ) <= v_BusinessDate
    ;

    -- ⑥-2 精算日計より直営の「加盟店への返金加算」分を出力する。
    INSERT INTO AW_AccountsDue_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,CASE WHEN DATE_FORMAT(ProcessingDate, '%Y-%m-01') < v_AccountingMonth THEN v_AccountingMonth
        ELSE DATE_FORMAT(ProcessingDate, '%Y-%m-01')
        END                                     -- 会計月
       ,AdvancesFixedDate                       -- 支払日
       ,AdvancesDate                            -- 入金日
       ,v_PaymentTarget_RepayAmountPayable      -- 入金先
       ,v_PaymentTarget_RepayAmount             -- 入金対象
       ,EnterpriseRefundCount                   -- 対象件数
       ,EnterpriseRefund * -1                   -- 金額
       ,OemId                                   -- OEMID
       ,EnterpriseId                            -- 加盟店ID
       ,NULL                                    -- 注文Seq
       ,NULL                                    -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,NULL                                    -- 入金管理Seq
    FROM  AT_PayOff_DailyAccount
    WHERE ProcessingDate   = v_BusinessDate
    AND   EnterpriseRefund <> 0
    AND   OemId <> 0
    ;

    -- ------------------------------
    -- 加盟店入金履歴より作成
    -- ------------------------------
    -- 加盟店入金履歴.加盟店ID=加盟店.加盟店ID で加盟店を取得できること
    -- (加盟店.OEMID=ｺｰﾄﾞﾏｽﾀ.KEYｺｰﾄﾞ) AND (ｺｰﾄﾞﾏｽﾀ.ｺｰﾄﾞ識別ID=160) で取得したｺｰﾄﾞﾏｽﾀ.区分1=0をであること
    CREATE TEMPORARY TABLE TMP_EnterpriseReceiptHistory
    SELECT
        erh.ReceiptDate                         -- 入金日付
       ,CASE WHEN DATE_FORMAT(erh.ReceiptDate, '%Y-%m-01') < v_AccountingMonth THEN v_AccountingMonth
        ELSE DATE_FORMAT(erh.ReceiptDate, '%Y-%m-01')
        END ReceiptMonth                        -- 入金月
       ,erh.ReceiptAmount                       -- 入金額
       ,aerh.ReceiptAmountDue                   -- 入金額(未収金)
       ,aerh.ReceiptAmountRece                  -- 入金額(売掛金)
       ,erh.ReceiptClass                        -- 入金科目
       ,aerh.ReceiptAmountSource                -- 入金元
       ,aerh.DailySummaryFlg                    -- 日別集計ﾌﾗｸﾞ
       ,erh.ValidFlg                            -- 有効ﾌﾗｸﾞ
       ,IFNULL(e.OemId, 0) AS OemId             -- OEMID
       ,e.EnterpriseId                          -- 加盟店ID
    FROM  T_EnterpriseReceiptHistory erh
    INNER JOIN AT_EnterpriseReceiptHistory aerh
    ON    erh.EntRcptSeq        = aerh.EntRcptSeq
    INNER JOIN T_Enterprise e
    ON    erh.EnterpriseId      = e.EnterpriseId
    INNER JOIN M_Code c
    ON    c.CodeId              = 160
    AND   c.KeyCode             = IFNULL(e.OemId, 0)
    WHERE c.Class1             <> 0
    AND   DATE_FORMAT( erh.ReceiptProcessDate, '%Y-%m-%d' ) <= v_BusinessDate
    -- ﾃﾝﾎﾟﾗﾘｰﾃｰﾌﾞﾙの作成に時間がかかるようなので、日次更新ﾌﾗｸﾞの条件を追加 ← これにより全件から1日分へ絞られる！！！
    AND   aerh.DailySummaryFlg = 0
    ;

    -- ⑦-1 OEM分&加盟店未収金の入金より加盟店未収金入金を仮払金日計ﾜｰｸを出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,erh.ReceiptMonth                        -- 会計月
       ,erh.ReceiptDate                         -- 支払日
       ,erh.ReceiptDate                         -- 入金日
       ,c2.Class1                               -- 入金先
       ,v_PaymentTarget_ReceiptAmountDue        -- 入金対象
       ,1                                       -- 対象件数
       ,erh.ReceiptAmountDue                    -- 金額
       ,erh.OemId                               -- OEMID
       ,erh.EnterpriseId                        -- 加盟店ID
       ,NULL                                    -- 注文Seq
       ,NULL                                    -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,erh.ReceiptAmountDue                    -- 入金額(未収金)
       ,NULL                                    -- 入金管理Seq
    FROM  TMP_EnterpriseReceiptHistory erh
    INNER JOIN M_Code c1
    ON    c1.CodeId             = 95
    AND   c1.KeyCode            = erh.ReceiptClass
    LEFT  JOIN M_Code c2
    ON    c2.CodeId             = 167
    AND   c2.KeyCode            = erh.ReceiptAmountSource
    WHERE
          c1.Class1             = 0
    AND   erh.DailySummaryFlg   = 0
    AND   erh.ValidFlg          = 1
    AND   erh.ReceiptAmountDue <> 0
    ;

    -- ⑦-2 OEM分&加盟店売掛金の入金より加盟店売掛金入金を仮払金日計ﾜｰｸを出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,erh.ReceiptMonth                        -- 会計月
       ,erh.ReceiptDate                         -- 支払日
       ,erh.ReceiptDate                         -- 入金日
       ,c2.Class1                               -- 入金先
       ,v_PaymentTarget_ReceiptAmountRece       -- 入金対象
       ,1                                       -- 対象件数
       ,erh.ReceiptAmountRece                   -- 金額
       ,erh.OemId                               -- OEMID
       ,erh.EnterpriseId                        -- 加盟店ID
       ,NULL                                    -- 注文Seq
       ,NULL                                    -- 注文ID
       ,erh.ReceiptAmountRece                   -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,NULL                                    -- 入金管理Seq
    FROM  TMP_EnterpriseReceiptHistory erh
    INNER JOIN M_Code c1
    ON    c1.CodeId             = 95
    AND   c1.KeyCode            = erh.ReceiptClass
    LEFT  JOIN M_Code c2
    ON    c2.CodeId             = 167
    AND   c2.KeyCode            = erh.ReceiptAmountSource
    WHERE
          c1.Class1             = 0
    AND   erh.DailySummaryFlg   = 0
    AND   erh.ValidFlg          = 1
    AND   erh.ReceiptAmountRece <> 0
    ;

    -- ⑦-3 OEM分&貸倒の入金より加盟店貸倒を仮払金日計ﾜｰｸを出力する
    INSERT INTO AW_SuspensePayments_DailyAccount(
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,OemId
       ,EnterpriseId
       ,OrderSeq
       ,OrderId
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,ReceiptSeq
       )
    SELECT
        v_BusinessDate                          -- 処理日付
       ,erh.ReceiptMonth                        -- 会計月
       ,erh.ReceiptDate                         -- 支払日
       ,erh.ReceiptDate                         -- 入金日
       ,c2.Class1                               -- 入金先
       ,v_PaymentTarget_ReceiptAmountDue        -- 入金対象
       ,1                                       -- 対象件数
       ,erh.ReceiptAmount                       -- 金額
       ,erh.OemId                               -- OEMID
       ,erh.EnterpriseId                        -- 加盟店ID
       ,NULL                                    -- 注文Seq
       ,NULL                                    -- 注文ID
       ,NULL                                    -- 入金額(売掛金)
       ,NULL                                    -- 入金額(未収金)
       ,NULL                                    -- 入金管理Seq
    FROM  TMP_EnterpriseReceiptHistory erh
    INNER JOIN M_Code c1
    ON    c1.CodeId             = 95
    AND   c1.KeyCode            = erh.ReceiptClass
    LEFT  JOIN M_Code c2
    ON    c2.CodeId             = 165
    AND   c2.KeyCode            = erh.ReceiptClass
    WHERE
          c1.Class1             = 1
    AND   erh.DailySummaryFlg   = 0
    AND   erh.ValidFlg          = 1
    AND   erh.ReceiptAmount    <> 0
    ;

    DROP TABLE TMP_EnterpriseReceiptHistory;

    -- ------------------------------
    -- 仮払金日計の作成
    -- ------------------------------
    -- ⑧ 仮払金日計ﾜｰｸを集計し仮払金日計を出力する
    INSERT INTO AT_SuspensePayments_DailyAccount(
        DailyMonthlyFlg
       ,ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        1                                       -- 日次･月次区分
       ,ProcessingDate                          -- 処理日付
       ,AccountDate                             -- 会計月
       ,PaymentDate                             -- 支払日
       ,ReceiptProcessDate                      -- 入金日
       ,PaymentAccountTitle                     -- 入金先(勘定科目)
       ,PaymentTargetAccountTitle               -- 入金対象(勘定科目)
       ,SUM(PaymentNumber)                      -- 件数
       ,SUM(IFNULL(Amount, 0))                  -- 金額
       ,NOW()                                   -- 登録日時
       ,pi_user_id                              -- 登録者
       ,NOW()                                   -- 更新日時
       ,pi_user_id                              -- 更新者
       ,1                                       -- 有効ﾌﾗｸﾞ
    FROM  AW_SuspensePayments_DailyAccount
    WHERE ProcessingDate = v_BusinessDate
    GROUP BY
        ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
    ;

    -- ------------------------------
    -- 仮払金日計明細の作成
    -- ------------------------------
    -- ⑨ 仮払金日計ﾜｰｸを集計し仮払金日計明細を出力する
    INSERT INTO AT_SuspensePayments_DailyAccountDetails(
        DailyMonthlyFlg
       ,ProcessingDate
       ,AccountDate
       ,PaymentDate
       ,ReceiptProcessDate
       ,PaymentAccountTitle
       ,PaymentTargetAccountTitle
       ,PaymentNumber
       ,Amount
       ,ReceiptAmountRece
       ,ReceiptAmountDue
       ,OemId
       ,OemNameKj
       ,EnterpriseId
       ,EnterpriseNameKj
       ,OrderSeq
       ,OrderId
       ,ReceiptSeq
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        0                                       -- 日次･月次区分
       ,asd.ProcessingDate                      -- 処理日付
       ,asd.AccountDate                         -- 会計月
       ,asd.PaymentDate                         -- 支払日
       ,asd.ReceiptProcessDate                  -- 入金日
       ,asd.PaymentAccountTitle                 -- 入金先(勘定科目)
       ,asd.PaymentTargetAccountTitle           -- 入金対象(勘定科目)
       ,asd.PaymentNumber                       -- 対象件数
       ,asd.Amount                              -- 金額
       ,asd.ReceiptAmountRece                   -- 入金額(売掛金)
       ,asd.ReceiptAmountDue                    -- 入金額(未収金)
       ,asd.OemId                               -- OEMID
       ,c.Class2                                -- OEM先名
       ,asd.EnterpriseId                        -- 加盟店ID
       ,e.EnterpriseNameKj                      -- 加盟店名
       ,asd.OrderSeq                            -- 注文Seq
       ,asd.OrderId                             -- 注文ID (旧取引ID)
       ,asd.ReceiptSeq                          -- 入金管理Seq
       ,NOW()                                   -- 登録日時
       ,pi_user_id                              -- 登録者
       ,NOW()                                   -- 更新日時
       ,pi_user_id                              -- 更新者
       ,1                                       -- 有効ﾌﾗｸﾞ
    FROM  AW_SuspensePayments_DailyAccount asd
    INNER JOIN T_Enterprise e
    ON    asd.EnterpriseId      = e.EnterpriseId
    LEFT  JOIN M_Code c
    ON    c.CodeId              = 160
    AND   c.KeyCode             = IFNULL(asd.OemId, 0)
    WHERE ProcessingDate        = v_BusinessDate
    ;

END
$$

DELIMITER ;
