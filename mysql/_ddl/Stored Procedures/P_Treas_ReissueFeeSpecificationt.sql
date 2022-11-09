DROP PROCEDURE IF EXISTS P_Treas_ReissueFeeSpecificationt;

DELIMITER $$

CREATE PROCEDURE P_Treas_ReissueFeeSpecificationt ( IN pi_user_id INT )
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名       ：  P_Treas_ReissueFeeSpecificationt
 *
 * 概要             ：  再発行手数料明細
 *
 * 引数             ：  [I/ ]pi_user_id        ﾕｰｻﾞｰID
 *
 * 履歴             ：  2015/10/01  NDC 新規作成
 *
 * 備考             ：  税込金額､消費税額､税抜金額の計算方法(消費税率は8 → つまり､0.08は消費税率ではない!!!)
 *                      ★税込金額から求める場合
 *                      ・税抜金額 = 税込金額 * 100 / (100 + 消費税率)
 *                      ・消費税額 = 税込金額 * 消費税率 / (100 + 消費税率)
 *                      ★税抜金額から求める場合
 *                      ・税込金額 = 税抜金額 * (100 + 消費税率) / 100
 *                      ・消費税額 = 税抜金額 * 消費税率 / 100
 *                      ★消費税額から求める場合
 *                      ・税込金額 = 消費税額 * (100 + 消費税率) / 消費税率
 *                      ・税抜金額 = 消費税額 * 100 / 消費税率
 *                      各金額を算出したい基準値がﾏｲﾅｽの場合、上記計算式ではｽﾞﾚが生じる
 *                        → ﾏｲﾅｽの場合には一旦ﾌﾟﾗｽに変換して金額を算出後、ﾏｲﾅｽ値に戻す
 *
 *****************************************************************************/
BEGIN
    -- ------------------------------
    -- 変数宣言
    -- ------------------------------
    DECLARE v_BusinessDate      DATE;                   -- 業務日付
    DECLARE v_AccountingMonth   DATE;                   -- 会計月
    DECLARE v_TaxRate           INT;                    -- 消費税率
    -- ------------------------------
    -- 変数初期化
    -- ------------------------------
    SET v_BusinessDate      = F_GetSystemProperty( '[DEFAULT]', 'systeminfo', 'BusinessDate' );       -- 業務日付
    SET v_AccountingMonth   = F_GetSystemProperty( '[DEFAULT]', 'systeminfo', 'AccountingMonth' );    -- 会計月
    SET v_TaxRate           = F_GetTaxRate( v_BusinessDate ) ;                                        -- 消費税率

    -- ------------------------------
    -- 再発行手数料明細
    -- ------------------------------
    INSERT INTO AT_ReissueFeeSpecification (
         DailyMonthlyFlg                                                        -- 日次･月次区分
        ,ProcessingDate                                                         -- 処理日付
        ,AccountDate                                                            -- 会計月
        ,OemId                                                                  -- OEMID
        ,OemNameKj                                                              -- OEM先名
        ,ManCustId                                                              -- 管理顧客番号
        ,ManCusNameKj                                                           -- 顧客名
        ,OrderSeq                                                               -- 注文Seq
        ,OrderId                                                                -- 注文ID
        ,OverdueClassification                                                  -- 督促延滞区分
        ,Clm_L_ClaimFeeTotal                                                    -- 再発行手数料合計
        ,Clm_L_ClaimFee                                                         -- 再発行手数料(税抜)
        ,Clm_L_ClaimFeeTax                                                      -- 再発行手数料(消費税)
        ,Clm_L_DamageInterestAmount                                             -- 延滞利息金額
        ,OutOfAmends                                                            -- 補償有無
        ,SalesDefiniteConditions                                                -- 売上確定条件
        ,SalesDefiniteDate                                                      -- 売上確定日付
        ,OemTransferDate                                                        -- OEM移管日
        ,EnterpriseId                                                           -- 加盟店ID
        ,EnterpriseNameKj                                                       -- 加盟店名
        ,F_LimitDate                                                            -- 支払期日
        ,F_ClaimAmount                                                          -- 初期債権金額
        ,ClaimAmount                                                            -- 入金後債権残金額
        ,FinalReceiptDate                                                       -- 最終入金日
        ,AfterTheFinalPaymentDays                                               -- 最終入金後経過日数
        ,RegistDate                                                             -- 登録日時
        ,RegistId                                                               -- 登録者
        ,UpdateDate                                                             -- 更新日時
        ,UpdateId                                                               -- 更新者
        ,ValidFlg )                                                             -- 有効ﾌﾗｸﾞ
    SELECT
         2                                                                      -- 日次･月次区分
        ,v_BusinessDate                                                         -- 業務日付
        ,( CASE
            WHEN DATE_FORMAT( rc.ReceiptDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' ) THEN v_AccountingMonth
            ELSE DATE_FORMAT( rc.ReceiptDate, '%Y-%m-01' )
         END )                                                                  -- 会計月
        ,c6.Class3                                                              -- 会計OEMID
        ,c6.Class2                                                              -- 会計OEM名称
        ,mc.ManCustId                                                           -- 管理顧客番号
        ,mc.NameKj                                                              -- 顧客名
        ,o.OrderSeq                                                             -- 注文Seq
        ,o.OrderId                                                              -- 注文ID
        ,c1.Class3                                                              -- 請求ﾊﾟﾀｰﾝ
        ,rc.CheckingClaimFee                                                    -- 消込情報-請求手数料
        -- 入金管理から取得している場合、ﾏｲﾅｽがありえる → 入金取消された場合、ﾏｲﾅｽのﾃﾞｰﾀが発生 → 計算する際にはﾌﾟﾗｽにし、その後ﾏｲﾅｽに戻す
        ,CASE WHEN rc.CheckingClaimFee < 0
            THEN ((rc.CheckingClaimFee * -1) - FLOOR((rc.CheckingClaimFee * -1) * v_TaxRate / (100 + v_TaxRate))) * -1
            ELSE rc.CheckingClaimFee - FLOOR(rc.CheckingClaimFee * v_TaxRate / (100 + v_TaxRate))
         END                                                                    -- 税抜金額
        ,CASE WHEN rc.CheckingClaimFee < 0
            THEN (FLOOR((rc.CheckingClaimFee * -1) * v_TaxRate / (100 + v_TaxRate))) * -1
            ELSE FLOOR(rc.CheckingClaimFee * v_TaxRate / (100 + v_TaxRate))
         END                                                                    -- 消費税
        ,rc.CheckingDamageInterestAmount                                        -- 延滞利息金額
        ,c5.Class1                                                              -- 補償対象有無
        ,F_GetSalesDefiniteConditions(o.OrderSeq)                               -- 売上確定条件
        ,DATE(apas.ATUriDay)                                                    -- 立替条件ｸﾘｱ日
        ,o.OemClaimTransDate                                                    -- OEM移管確定日
        ,o.EnterpriseId                                                         -- 加盟店ID
        ,e.EnterpriseNameKj                                                     -- 加盟店名
        ,cc.F_LimitDate                                                         -- 初回-支払期限
        ,cc.F_ClaimAmount                                                       -- 初回請求金額
        ,cc.BalanceUseAmount                                                    -- 残高情報-利用額
        ,rc.ReceiptDate                                                         -- 顧客入金日
        ,DATEDIFF( v_BusinessDate, rc.ReceiptDate )                             -- 業務日付 - 入金管理.顧客入金日
        ,NOW()                                                                  -- ｼｽﾃﾑ日時
        ,pi_user_id                                                             -- ﾕｰｻﾞｰID
        ,NOW()                                                                  -- ｼｽﾃﾑ日時
        ,pi_user_id                                                             -- ﾕｰｻﾞｰID
        ,1                                                                      -- 有効ﾌﾗｸﾞ:有効
    FROM        T_ReceiptControl rc
    INNER JOIN  T_Order o
    ON      o.OrderSeq          = rc.OrderSeq
    INNER JOIN  T_Customer c
    ON      c.OrderSeq          = o.OrderSeq
    INNER JOIN  T_PayingAndSales pas
    ON      pas.OrderSeq        = o.OrderSeq
    INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
    INNER JOIN  T_ClaimControl cc
    ON      cc.OrderSeq         = o.OrderSeq
    LEFT JOIN   T_Enterprise e
    ON      o.EnterpriseId      = e.EnterpriseId
    LEFT JOIN   T_EnterpriseCustomer ec
    ON      ec.EntCustSeq       = c.EntCustSeq
    LEFT JOIN   T_ManagementCustomer mc
    ON      mc.ManCustId        = ec.ManCustId
    LEFT JOIN   M_Code c1
    ON      c1.CodeId           = 12
    AND     c1.KeyCode          = cc.ClaimPattern
    LEFT JOIN   M_Code c5
    ON      c5.CodeId           = 159
    AND     c5.KeyCode          = IFNULL( o.OutOfAmends, 0 )
    INNER JOIN  M_Code c6
    ON      c6.CodeId           = 160
    AND     c6.KeyCode          = IFNULL( o.OemId, 0 )
    WHERE   rc.DailySummaryFlg = 0
    AND     ( rc.CheckingDamageInterestAmount <> 0
    OR      rc.CheckingClaimFee <> 0 )
    AND     DATE_FORMAT( rc.ReceiptProcessDate, '%Y-%m-%d' ) <= v_BusinessDate
    AND     rc.ValidFlg        = 1
;

END
$$

DELIMITER ;
