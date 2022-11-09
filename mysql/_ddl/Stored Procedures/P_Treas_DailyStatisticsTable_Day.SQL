DROP PROCEDURE IF EXISTS P_Treas_DailyStatisticsTable_Day;

DELIMITER $$

CREATE PROCEDURE P_Treas_DailyStatisticsTable_Day ( IN pi_user_id INT )
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名       ：  P_Treas_DailyStatisticsTable_Day
 *
 * 概要             ：  直営日次統計表（日次）
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
    DECLARE v_BusinessDate      DATE;                               -- 業務日付
    DECLARE v_AccountingMonth   DATE;                               -- 会計月
    DECLARE v_EnterpriseId      BIGINT(20);                         -- OEM移管分の加盟店ID

    -- ------------------------------
    -- 変数初期化
    -- ------------------------------
    SET v_BusinessDate      = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'BusinessDate');       -- 業務日付
    SET v_AccountingMonth   = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'AccountingMonth');    -- 会計月
    SET v_EnterpriseId      = 99999999;                                                             -- OEM移管分の加盟店ID

    -- ------------------------------
    -- ﾜｰｸﾃｰﾌﾞﾙの初期化
    -- ------------------------------
    -- 日別直営日次統計表ﾜｰｸをﾄﾗﾝｹｰﾄする
    TRUNCATE AW_Daily2StatisticsTable;

    -- ------------------------------
    -- 1.1.詳細設計（日次売上明細の「直営売上」より）
    -- ------------------------------
    INSERT INTO AW_Daily2StatisticsTable (
         ProcessingDate                     -- 集計日
        ,AccountDate                        -- 会計月
        ,EnterpriseId                       -- 加盟店ID
        ,D_ChargeCount                      -- 当日立替件数
        ,D_ChargeAmount                     -- 当日立替金額
        ,D_CancelCount                      -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                     -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount              -- 当日立替金戻し件数
        ,D_SettlementBackAmount             -- 当日立替金戻し金額
        ,D_OemTransferCount                 -- 当日OEM移管件数
        ,D_OemTransferAmount                -- 当日OEM移管金額
        ,D_ReClaimFeeCount                  -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                 -- 当日再発行手数料額
        ,D_DamageCount                      -- 当日遅延損害金件数
        ,D_DamageAmount                     -- 当日遅延損害金額
        ,D_ReceiptCount                     -- 当日入金件数
        ,D_ReceiptAmount                    -- 当日入金額
        ,D_RepayCount                       -- 当日返金件数
        ,D_RepayAmount                      -- 当日返金額
        ,D_BadDebtCount                     -- 当日貸倒件数
        ,D_BadDebtAmount                    -- 当日貸倒金額
        ,D_OtherPaymentCount                -- 当日その他件数
        ,D_OtherPaymentAmount               -- 当日その他金額
        ,D_SettlementFee                    -- 当日決済手数料
        ,D_ClaimFee                         -- 当日請求手数料
        ,D_ClaimFeeTax                      -- 当日請求手数料消費税
        ,D_MonthlyFee                       -- 当日月額固定費
        ,D_MonthlyFeeTax                    -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax             -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                    -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                 -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax             -- 当日月額固定費予備消費税
        ,D_AddClaimFee                      -- 当日再発行手数料
        ,D_AddClaimFeeTax                   -- 当日再発行手数料消費税
        ,D_DamageInterestAmount             -- 当日遅延損害金
        ,D_CanSettlementFee                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        )
    SELECT
         v_BusinessDate                     -- 集計日
        ,CASE
            WHEN DATE_FORMAT( at_pas.ATUriDay, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            THEN v_AccountingMonth
            ELSE DATE_FORMAT( at_pas.ATUriDay, '%Y-%m-01' )
         END                                -- 会計月
        ,o.EnterpriseId                     -- 加盟店ID
        ,1                                  -- 当日立替件数
        ,pas.UseAmount                      -- 当日立替金額
        ,null                               -- 当日ｷｬﾝｾﾙ件数
        ,null                               -- 当日ｷｬﾝｾﾙ立替金額
        ,null                               -- 当日立替金戻し件数
        ,null                               -- 当日立替金戻し金額
        ,null                               -- 当日OEM移管件数
        ,null                               -- 当日OEM移管金額
        ,null                               -- 当日再発行手数料件数
        ,null                               -- 当日再発行手数料額
        ,null                               -- 当日遅延損害金件数
        ,null                               -- 当日遅延損害金額
        ,null                               -- 当日入金件数
        ,null                               -- 当日入金額
        ,null                               -- 当日返金件数
        ,null                               -- 当日返金額
        ,null                               -- 当日貸倒件数
        ,null                               -- 当日貸倒金額
        ,null                               -- 当日その他件数
        ,null                               -- 当日その他金額
        ,pas.SettlementFee                  -- 当日決済手数料
        -- ありえないとは思うけど念のため対応･･･請求手数料等がﾏｲﾅｽの場合にはﾌﾟﾗｽ変換して計算 → ﾏｲﾅｽへ戻す。
        ,CASE WHEN pas.ClaimFee < 0
            THEN ((pas.ClaimFee * -1) - FLOOR((pas.ClaimFee * -1) * F_GetTaxRate(DATE(at_pas.ATUriDay)) / (100 + F_GetTaxRate(DATE(at_pas.ATUriDay))))) * -1
            ELSE pas.ClaimFee - FLOOR(pas.ClaimFee * F_GetTaxRate(DATE(at_pas.ATUriDay)) / (100 + F_GetTaxRate(DATE(at_pas.ATUriDay))))
         END                                -- 当日請求手数料
        ,CASE WHEN pas.ClaimFee < 0
            THEN (FLOOR((pas.ClaimFee * -1) * F_GetTaxRate(DATE(at_pas.ATUriDay)) / (100 + F_GetTaxRate(DATE(at_pas.ATUriDay))))) * -1
            ELSE FLOOR(pas.ClaimFee * F_GetTaxRate(DATE(at_pas.ATUriDay)) / (100 + F_GetTaxRate(DATE(at_pas.ATUriDay))))
         END                                -- 当日請求手数料消費税
        ,null                               -- 当日月額固定費
        ,null                               -- 当日月額固定費消費税
        ,null                               -- 当日同梱月額固定費
        ,null                               -- 当日同梱月額固定費消費税
        ,null                               -- 当日API月額固定費
        ,null                               -- 当日API月額固定費消費税
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日月額固定費予備
        ,null                               -- 当日月額固定費予備消費税
        ,null                               -- 当日再発行手数料
        ,null                               -- 当日再発行手数料消費税
        ,null                               -- 当日遅延損害金
        ,null                               -- 当日ｷｬﾝｾﾙ決済手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料消費税
    FROM        T_PayingAndSales pas
    INNER JOIN  AT_PayingAndSales at_pas
    ON      at_pas.Seq  = pas.Seq
    INNER JOIN  T_Order o
    ON      o.OrderSeq  = pas.OrderSeq
    LEFT OUTER JOIN  T_ClaimControl cc
    ON      cc.OrderSeq = o.P_OrderSeq
    INNER JOIN  M_Code c1
    ON      c1.CodeId   = 160
    AND     c1.KeyCode  = IFNULL( o.OemId, 0 )
    LEFT OUTER JOIN T_Cancel cncl
    ON      cncl.OrderSeq = o.OrderSeq
    WHERE   pas.ClearConditionForCharge = 1
    AND     DATE_FORMAT(at_pas.ATUriDay, '%Y-%m-%d') <= v_BusinessDate
    AND     at_pas.DailySummaryFlg      = 0
    AND     pas.ValidFlg                = 1
    AND     c1.Class1                   = 0
    AND     (cncl.ApprovalDate IS NULL OR DATE(at_pas.ATUriDay) <> DATE(cncl.ApprovalDate));

    -- ------------------------------
    -- 1.2.詳細設計（日次売上明細の「直営ｷｬﾝｾﾙ」より）
    -- ------------------------------
    INSERT INTO AW_Daily2StatisticsTable (
         ProcessingDate                     -- 集計日
        ,AccountDate                        -- 会計月
        ,EnterpriseId                       -- 加盟店ID
        ,D_ChargeCount                      -- 当日立替件数
        ,D_ChargeAmount                     -- 当日立替金額
        ,D_CancelCount                      -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                     -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount              -- 当日立替金戻し件数
        ,D_SettlementBackAmount             -- 当日立替金戻し金額
        ,D_OemTransferCount                 -- 当日OEM移管件数
        ,D_OemTransferAmount                -- 当日OEM移管金額
        ,D_ReClaimFeeCount                  -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                 -- 当日再発行手数料額
        ,D_DamageCount                      -- 当日遅延損害金件数
        ,D_DamageAmount                     -- 当日遅延損害金額
        ,D_ReceiptCount                     -- 当日入金件数
        ,D_ReceiptAmount                    -- 当日入金額
        ,D_RepayCount                       -- 当日返金件数
        ,D_RepayAmount                      -- 当日返金額
        ,D_BadDebtCount                     -- 当日貸倒件数
        ,D_BadDebtAmount                    -- 当日貸倒金額
        ,D_OtherPaymentCount                -- 当日その他件数
        ,D_OtherPaymentAmount               -- 当日その他金額
        ,D_SettlementFee                    -- 当日決済手数料
        ,D_ClaimFee                         -- 当日請求手数料
        ,D_ClaimFeeTax                      -- 当日請求手数料消費税
        ,D_MonthlyFee                       -- 当日月額固定費
        ,D_MonthlyFeeTax                    -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax             -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                    -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                 -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax             -- 当日月額固定費予備消費税
        ,D_AddClaimFee                      -- 当日再発行手数料
        ,D_AddClaimFeeTax                   -- 当日再発行手数料消費税
        ,D_DamageInterestAmount             -- 当日遅延損害金
        ,D_CanSettlementFee                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        )
    SELECT
         v_BusinessDate                     -- 集計日
        ,CASE
            WHEN DATE_FORMAT( cnl.ApprovalDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            THEN v_AccountingMonth
            ELSE DATE_FORMAT( cnl.ApprovalDate, '%Y-%m-01' )
         END                                -- 会計月
        ,o.EnterpriseId                     -- 加盟店ID
        ,null                               -- 当日立替件数
        ,null                               -- 当日立替金額
        ,1                                  -- 当日ｷｬﾝｾﾙ件数
        ,pas.UseAmount * -1                 -- 当日ｷｬﾝｾﾙ立替金額
        ,null                               -- 当日立替金戻し件数
        ,null                               -- 当日立替金戻し金額
        ,null                               -- 当日OEM移管件数
        ,null                               -- 当日OEM移管金額
        ,null                               -- 当日再発行手数料件数
        ,null                               -- 当日再発行手数料額
        ,null                               -- 当日遅延損害金件数
        ,null                               -- 当日遅延損害金額
        ,null                               -- 当日入金件数
        ,null                               -- 当日入金額
        ,null                               -- 当日返金件数
        ,null                               -- 当日返金額
        ,null                               -- 当日貸倒件数
        ,null                               -- 当日貸倒金額
        ,null                               -- 当日その他件数
        ,null                               -- 当日その他金額
        ,null                               -- 当日決済手数料
        ,null                               -- 当日請求手数料
        ,null                               -- 当日請求手数料消費税
        ,null                               -- 当日月額固定費
        ,null                               -- 当日月額固定費消費税
        ,null                               -- 当日同梱月額固定費
        ,null                               -- 当日同梱月額固定費消費税
        ,null                               -- 当日API月額固定費
        ,null                               -- 当日API月額固定費消費税
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日月額固定費予備
        ,null                               -- 当日月額固定費予備消費税
        ,null                               -- 当日再発行手数料
        ,null                               -- 当日再発行手数料消費税
        ,null                               -- 当日遅延損害金
        ,pas.SettlementFee * -1             -- 当日ｷｬﾝｾﾙ決済手数料
        -- ありえないとは思うけど念のため対応･･･請求手数料がﾏｲﾅｽの場合にはﾌﾟﾗｽ変換して計算 → ﾏｲﾅｽへ戻す。
        ,CASE WHEN pas.ClaimFee < 0
            THEN (((pas.ClaimFee * -1) - FLOOR((pas.ClaimFee * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1) * -1
            ELSE (pas.ClaimFee - FLOOR(pas.ClaimFee * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
         END                                -- 当日ｷｬﾝｾﾙ請求手数料
        ,CASE WHEN pas.ClaimFee < 0
            THEN FLOOR(((pas.ClaimFee * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))) * -1) * -1
            ELSE FLOOR(pas.ClaimFee * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))) * -1
         END                                -- 当日ｷｬﾝｾﾙ請求手数料消費税
    FROM        T_Cancel cnl
    INNER JOIN  T_Order o
    ON      o.OrderSeq      = cnl.OrderSeq
    INNER JOIN  T_PayingAndSales pas
    ON      pas.OrderSeq    = cnl.OrderSeq
    INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
    LEFT OUTER JOIN  T_ClaimControl cc
    ON      cc.OrderSeq     = o.P_OrderSeq
    INNER JOIN  M_Code c1
    ON      c1.CodeId       = 160
    AND     c1.KeyCode      = IFNULL( o.OemId, 0 )
    WHERE   cnl.ApproveFlg              = 1
    AND     DATE_FORMAT( cnl.ApprovalDate, '%Y-%m-%d' ) <= v_BusinessDate
    AND     cnl.DailySummaryFlg         = 0
    AND     cnl.ValidFlg                = 1
    AND     pas.ClearConditionForCharge = 1
    AND     c1.Class1                   = 0
    AND     (cnl.ApprovalDate IS NULL OR DATE(apas.ATUriDay) <> DATE(cnl.ApprovalDate));

    -- ------------------------------
    -- 1.3.詳細設計書:日次売上明細の「直営月額固定費」より各月額固定費を日別直営日次統計表ﾜｰｸに出力する
    -- ------------------------------
    INSERT INTO AW_Daily2StatisticsTable (
         ProcessingDate                     -- 集計日
        ,AccountDate                        -- 会計月
        ,EnterpriseId                       -- 加盟店ID
        ,D_ChargeCount                      -- 当日立替件数
        ,D_ChargeAmount                     -- 当日立替金額
        ,D_CancelCount                      -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                     -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount              -- 当日立替金戻し件数
        ,D_SettlementBackAmount             -- 当日立替金戻し金額
        ,D_OemTransferCount                 -- 当日OEM移管件数
        ,D_OemTransferAmount                -- 当日OEM移管金額
        ,D_ReClaimFeeCount                  -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                 -- 当日再発行手数料額
        ,D_DamageCount                      -- 当日遅延損害金件数
        ,D_DamageAmount                     -- 当日遅延損害金額
        ,D_ReceiptCount                     -- 当日入金件数
        ,D_ReceiptAmount                    -- 当日入金額
        ,D_RepayCount                       -- 当日返金件数
        ,D_RepayAmount                      -- 当日返金額
        ,D_BadDebtCount                     -- 当日貸倒件数
        ,D_BadDebtAmount                    -- 当日貸倒金額
        ,D_OtherPaymentCount                -- 当日その他件数
        ,D_OtherPaymentAmount               -- 当日その他金額
        ,D_SettlementFee                    -- 当日決済手数料
        ,D_ClaimFee                         -- 当日請求手数料
        ,D_ClaimFeeTax                      -- 当日請求手数料消費税
        ,D_MonthlyFee                       -- 当日月額固定費
        ,D_MonthlyFeeTax                    -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax             -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                    -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                 -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax             -- 当日月額固定費予備消費税
        ,D_AddClaimFee                      -- 当日再発行手数料
        ,D_AddClaimFeeTax                   -- 当日再発行手数料消費税
        ,D_DamageInterestAmount             -- 当日遅延損害金
        ,D_CanSettlementFee                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        )
    SELECT
         v_BusinessDate                     -- 集計日
        ,emci.ClosingMonthly                -- 会計月
        ,emci.EnterpriseId                  -- 加盟店ID
        ,null                               -- 当日立替件数
        ,null                               -- 当日立替金額
        ,null                               -- 当日ｷｬﾝｾﾙ件数
        ,null                               -- 当日ｷｬﾝｾﾙ立替金額
        ,null                               -- 当日立替金戻し件数
        ,null                               -- 当日立替金戻し金額
        ,null                               -- 当日OEM移管件数
        ,null                               -- 当日OEM移管金額
        ,null                               -- 当日再発行手数料件数
        ,null                               -- 当日再発行手数料額
        ,null                               -- 当日遅延損害金件数
        ,null                               -- 当日遅延損害金額
        ,null                               -- 当日入金件数
        ,null                               -- 当日入金額
        ,null                               -- 当日返金件数
        ,null                               -- 当日返金額
        ,null                               -- 当日貸倒件数
        ,null                               -- 当日貸倒金額
        ,null                               -- 当日その他件数
        ,null                               -- 当日その他金額
        ,null                               -- 当日決済手数料
        ,null                               -- 当日請求手数料
        ,null                               -- 当日請求手数料消費税
        ,emci.MonthlyFee                    -- 当日月額固定費
        ,emci.MonthlyFeeTax                 -- 当日月額固定費消費税
        ,emci.IncludeMonthlyFee             -- 当日同梱月額固定費
        ,emci.IncludeMonthlyFeeTax          -- 当日同梱月額固定費消費税
        ,emci.ApiMonthlyFee                 -- 当日API月額固定費
        ,emci.ApiMonthlyFeeTax              -- 当日API月額固定費消費税
        ,emci.CreditNoticeMonthlyFee        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,emci.CreditNoticeMonthlyFeeTax     -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,emci.NCreditNoticeMonthlyFee       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,emci.NCreditNoticeMonthlyFeeTax    -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,emci.ReserveMonthlyFee             -- 当日月額固定費予備
        ,emci.ReserveMonthlyFeeTax          -- 当日月額固定費予備消費税
        ,null                               -- 当日再発行手数料
        ,null                               -- 当日再発行手数料消費税
        ,null                               -- 当日遅延損害金
        ,null                               -- 当日ｷｬﾝｾﾙ決済手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料消費税
    FROM    AT_EnterpriseMonthlyClosingInfo emci
    INNER JOIN  M_Code c1
    ON      c1.CodeId       = 160
    AND     c1.KeyCode      = IFNULL( emci.OemId, 0 )
    WHERE   emci.ValidFlg             = 1
    AND     emci.DailySummaryFlg      = 0
    AND     emci.ClosingMonthly <= v_BusinessDate
    AND     emci.MonthlyFee + emci.MonthlyFeeTax +
            emci.IncludeMonthlyFee + emci.IncludeMonthlyFeeTax +
            emci.ApiMonthlyFee + emci.ApiMonthlyFeeTax +
            emci.CreditNoticeMonthlyFee + emci.CreditNoticeMonthlyFeeTax +
            emci.NCreditNoticeMonthlyFee + emci.NCreditNoticeMonthlyFeeTax +
            emci.ReserveMonthlyFee + emci.ReserveMonthlyFeeTax > 0
    AND     c1.Class1                 = 0;

    -- ------------------------------
    -- 1.5.詳細設計書:OEM移管明細より債権移管情報を日別直営日次統計表ﾜｰｸに出力する
    -- ------------------------------
    INSERT INTO AW_Daily2StatisticsTable (
         ProcessingDate                     -- 集計日
        ,AccountDate                        -- 会計月
        ,EnterpriseId                       -- 加盟店ID
        ,D_ChargeCount                      -- 当日立替件数
        ,D_ChargeAmount                     -- 当日立替金額
        ,D_CancelCount                      -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                     -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount              -- 当日立替金戻し件数
        ,D_SettlementBackAmount             -- 当日立替金戻し金額
        ,D_OemTransferCount                 -- 当日OEM移管件数
        ,D_OemTransferAmount                -- 当日OEM移管金額
        ,D_ReClaimFeeCount                  -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                 -- 当日再発行手数料額
        ,D_DamageCount                      -- 当日遅延損害金件数
        ,D_DamageAmount                     -- 当日遅延損害金額
        ,D_ReceiptCount                     -- 当日入金件数
        ,D_ReceiptAmount                    -- 当日入金額
        ,D_RepayCount                       -- 当日返金件数
        ,D_RepayAmount                      -- 当日返金額
        ,D_BadDebtCount                     -- 当日貸倒件数
        ,D_BadDebtAmount                    -- 当日貸倒金額
        ,D_OtherPaymentCount                -- 当日その他件数
        ,D_OtherPaymentAmount               -- 当日その他金額
        ,D_SettlementFee                    -- 当日決済手数料
        ,D_ClaimFee                         -- 当日請求手数料
        ,D_ClaimFeeTax                      -- 当日請求手数料消費税
        ,D_MonthlyFee                       -- 当日月額固定費
        ,D_MonthlyFeeTax                    -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax             -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                    -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                 -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax             -- 当日月額固定費予備消費税
        ,D_AddClaimFee                      -- 当日再発行手数料
        ,D_AddClaimFeeTax                   -- 当日再発行手数料消費税
        ,D_DamageInterestAmount             -- 当日遅延損害金
        ,D_CanSettlementFee                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        )
    SELECT
         v_BusinessDate                     -- 集計日
        ,CASE
            WHEN DATE_FORMAT( o.OemClaimTransDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            THEN v_AccountingMonth
            ELSE DATE_FORMAT( o.OemClaimTransDate, '%Y-%m-01' )
         END                                -- 会計月
        ,v_EnterpriseId                     -- 加盟店ID
        ,null                               -- 当日立替件数
        ,null                               -- 当日立替金額
        ,null                               -- 当日ｷｬﾝｾﾙ件数
        ,null                               -- 当日ｷｬﾝｾﾙ立替金額
        ,null                               -- 当日立替金戻し件数
        ,null                               -- 当日立替金戻し金額
        ,1                                  -- 当日OEM移管件数
        ,cc.BalanceUseAmount                -- 当日OEM移管金額
        ,null                               -- 当日再発行手数料件数
        ,null                               -- 当日再発行手数料額
        ,null                               -- 当日遅延損害金件数
        ,null                               -- 当日遅延損害金額
        ,null                               -- 当日入金件数
        ,null                               -- 当日入金額
        ,null                               -- 当日返金件数
        ,null                               -- 当日返金額
        ,null                               -- 当日貸倒件数
        ,null                               -- 当日貸倒金額
        ,null                               -- 当日その他件数
        ,null                               -- 当日その他金額
        ,null                               -- 当日決済手数料
        ,null                               -- 当日請求手数料
        ,null                               -- 当日請求手数料消費税
        ,null                               -- 当日月額固定費
        ,null                               -- 当日月額固定費消費税
        ,null                               -- 当日同梱月額固定費
        ,null                               -- 当日同梱月額固定費消費税
        ,null                               -- 当日API月額固定費
        ,null                               -- 当日API月額固定費消費税
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日月額固定費予備
        ,null                               -- 当日月額固定費予備消費税
        ,null                               -- 当日再発行手数料
        ,null                               -- 当日再発行手数料消費税
        ,null                               -- 当日遅延損害金
        ,null                               -- 当日ｷｬﾝｾﾙ決済手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料消費税
    FROM        T_Order o
    INNER JOIN  T_ClaimControl cc
    ON      cc.OrderSeq = o.OrderSeq
    INNER JOIN  M_Code c1
    ON      c1.CodeId   = 160
    AND     c1.KeyCode  = IFNULL( o.OemId, 0 )
    WHERE   o.OemClaimTransDate IS NOT NULL
    AND     o.OemClaimTransDate <= v_BusinessDate
    AND     o.OemClaimTransFlg  =  0
    AND     o.ValidFlg          =  1
    AND     cc.BalanceUseAmount >  0
    AND     c1.Class1           <> 0;

    -- ------------------------------
    -- 1.5.1.詳細設計書:OEM移管明細より債権移管情報を日別直営日次統計表ﾜｰｸに出力する（ｷｬﾝｾﾙ分の対応）
    -- ------------------------------
    INSERT INTO AW_Daily2StatisticsTable (
            ProcessingDate                     -- 集計日
        ,   AccountDate                        -- 会計月
        ,   EnterpriseId                       -- 加盟店ID
        ,   D_ChargeCount                      -- 当日立替件数
        ,   D_ChargeAmount                     -- 当日立替金額
        ,   D_CancelCount                      -- 当日ｷｬﾝｾﾙ件数
        ,   D_CancelAmount                     -- 当日ｷｬﾝｾﾙ立替金額
        ,   D_SettlementBackCount              -- 当日立替金戻し件数
        ,   D_SettlementBackAmount             -- 当日立替金戻し金額
        ,   D_OemTransferCount                 -- 当日OEM移管件数
        ,   D_OemTransferAmount                -- 当日OEM移管金額
        ,   D_ReClaimFeeCount                  -- 当日再発行手数料件数
        ,   D_ReClaimFeeAmount                 -- 当日再発行手数料額
        ,   D_DamageCount                      -- 当日遅延損害金件数
        ,   D_DamageAmount                     -- 当日遅延損害金額
        ,   D_ReceiptCount                     -- 当日入金件数
        ,   D_ReceiptAmount                    -- 当日入金額
        ,   D_RepayCount                       -- 当日返金件数
        ,   D_RepayAmount                      -- 当日返金額
        ,   D_BadDebtCount                     -- 当日貸倒件数
        ,   D_BadDebtAmount                    -- 当日貸倒金額
        ,   D_OtherPaymentCount                -- 当日その他件数
        ,   D_OtherPaymentAmount               -- 当日その他金額
        ,   D_SettlementFee                    -- 当日決済手数料
        ,   D_ClaimFee                         -- 当日請求手数料
        ,   D_ClaimFeeTax                      -- 当日請求手数料消費税
        ,   D_MonthlyFee                       -- 当日月額固定費
        ,   D_MonthlyFeeTax                    -- 当日月額固定費消費税
        ,   D_IncludeMonthlyFee                -- 当日同梱月額固定費
        ,   D_IncludeMonthlyFeeTax             -- 当日同梱月額固定費消費税
        ,   D_ApiMonthlyFee                    -- 当日API月額固定費
        ,   D_ApiMonthlyFeeTax                 -- 当日API月額固定費消費税
        ,   D_CreditNoticeMonthlyFee           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,   D_CreditNoticeMonthlyFeeTax        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,   D_NCreditNoticeMonthlyFee          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,   D_NCreditNoticeMonthlyFeeTax       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,   D_ReserveMonthlyFee                -- 当日月額固定費予備
        ,   D_ReserveMonthlyFeeTax             -- 当日月額固定費予備消費税
        ,   D_AddClaimFee                      -- 当日再発行手数料
        ,   D_AddClaimFeeTax                   -- 当日再発行手数料消費税
        ,   D_DamageInterestAmount             -- 当日遅延損害金
        ,   D_CanSettlementFee                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,   D_CanClaimFee                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,   D_CanClaimFeeTax                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        )
    SELECT  v_BusinessDate                     -- 集計日
        ,   CASE
                WHEN DATE_FORMAT(can.ApprovalDate, '%Y-%m') < DATE_FORMAT(v_AccountingMonth, '%Y-%m') THEN v_AccountingMonth
                ELSE DATE_FORMAT(can.ApprovalDate, '%Y-%m-01')
            END                                -- 会計月
        ,   v_EnterpriseId                     -- 加盟店ID
        ,   null                               -- 当日立替件数
        ,   null                               -- 当日立替金額
        ,   null                               -- 当日ｷｬﾝｾﾙ件数
        ,   null                               -- 当日ｷｬﾝｾﾙ立替金額
        ,   null                               -- 当日立替金戻し件数
        ,   null                               -- 当日立替金戻し金額
        ,   1                                  -- 当日OEM移管件数
        ,   cc.BalanceUseAmount * -1           -- 当日OEM移管金額
        ,   null                               -- 当日再発行手数料件数
        ,   null                               -- 当日再発行手数料額
        ,   null                               -- 当日遅延損害金件数
        ,   null                               -- 当日遅延損害金額
        ,   null                               -- 当日入金件数
        ,   null                               -- 当日入金額
        ,   null                               -- 当日返金件数
        ,   null                               -- 当日返金額
        ,   null                               -- 当日貸倒件数
        ,   null                               -- 当日貸倒金額
        ,   null                               -- 当日その他件数
        ,   null                               -- 当日その他金額
        ,   null                               -- 当日決済手数料
        ,   null                               -- 当日請求手数料
        ,   null                               -- 当日請求手数料消費税
        ,   null                               -- 当日月額固定費
        ,   null                               -- 当日月額固定費消費税
        ,   null                               -- 当日同梱月額固定費
        ,   null                               -- 当日同梱月額固定費消費税
        ,   null                               -- 当日API月額固定費
        ,   null                               -- 当日API月額固定費消費税
        ,   null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,   null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,   null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,   null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,   null                               -- 当日月額固定費予備
        ,   null                               -- 当日月額固定費予備消費税
        ,   null                               -- 当日再発行手数料
        ,   null                               -- 当日再発行手数料消費税
        ,   null                               -- 当日遅延損害金
        ,   null                               -- 当日ｷｬﾝｾﾙ決済手数料
        ,   null                               -- 当日ｷｬﾝｾﾙ請求手数料
        ,   null                               -- 当日ｷｬﾝｾﾙ請求手数料消費税
    FROM    T_Order o
            INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq)
            INNER JOIN T_Cancel can ON (can.OrderSeq = o.OrderSeq)
            INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode  = IFNULL( o.OemId, 0 ))
    WHERE   o.OemClaimTransDate IS NOT NULL
    AND     o.OemClaimTransDate <= v_BusinessDate
    AND     o.ValidFlg          =  1
    AND     cc.BalanceUseAmount >  0
    AND     c1.Class1           <> 0
    AND     can.ApprovalDate >= v_BusinessDate AND can.ApprovalDate < (v_BusinessDate + INTERVAL 1 DAY)
    AND     can.ApproveFlg       = 1
    AND     can.ValidFlg         = 1;

    -- ------------------------------
    -- 顧客入金日<債権移管日<=入金処理日による債権移管戻し
    -- ------------------------------
    INSERT INTO AW_Daily2StatisticsTable (
            ProcessingDate                     -- 集計日
        ,   AccountDate                        -- 会計月
        ,   EnterpriseId                       -- 加盟店ID
        ,   D_ChargeCount                      -- 当日立替件数
        ,   D_ChargeAmount                     -- 当日立替金額
        ,   D_CancelCount                      -- 当日ｷｬﾝｾﾙ件数
        ,   D_CancelAmount                     -- 当日ｷｬﾝｾﾙ立替金額
        ,   D_SettlementBackCount              -- 当日立替金戻し件数
        ,   D_SettlementBackAmount             -- 当日立替金戻し金額
        ,   D_OemTransferCount                 -- 当日OEM移管件数
        ,   D_OemTransferAmount                -- 当日OEM移管金額
        ,   D_ReClaimFeeCount                  -- 当日再発行手数料件数
        ,   D_ReClaimFeeAmount                 -- 当日再発行手数料額
        ,   D_DamageCount                      -- 当日遅延損害金件数
        ,   D_DamageAmount                     -- 当日遅延損害金額
        ,   D_ReceiptCount                     -- 当日入金件数
        ,   D_ReceiptAmount                    -- 当日入金額
        ,   D_RepayCount                       -- 当日返金件数
        ,   D_RepayAmount                      -- 当日返金額
        ,   D_BadDebtCount                     -- 当日貸倒件数
        ,   D_BadDebtAmount                    -- 当日貸倒金額
        ,   D_OtherPaymentCount                -- 当日その他件数
        ,   D_OtherPaymentAmount               -- 当日その他金額
        ,   D_SettlementFee                    -- 当日決済手数料
        ,   D_ClaimFee                         -- 当日請求手数料
        ,   D_ClaimFeeTax                      -- 当日請求手数料消費税
        ,   D_MonthlyFee                       -- 当日月額固定費
        ,   D_MonthlyFeeTax                    -- 当日月額固定費消費税
        ,   D_IncludeMonthlyFee                -- 当日同梱月額固定費
        ,   D_IncludeMonthlyFeeTax             -- 当日同梱月額固定費消費税
        ,   D_ApiMonthlyFee                    -- 当日API月額固定費
        ,   D_ApiMonthlyFeeTax                 -- 当日API月額固定費消費税
        ,   D_CreditNoticeMonthlyFee           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,   D_CreditNoticeMonthlyFeeTax        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,   D_NCreditNoticeMonthlyFee          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,   D_NCreditNoticeMonthlyFeeTax       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,   D_ReserveMonthlyFee                -- 当日月額固定費予備
        ,   D_ReserveMonthlyFeeTax             -- 当日月額固定費予備消費税
        ,   D_AddClaimFee                      -- 当日再発行手数料
        ,   D_AddClaimFeeTax                   -- 当日再発行手数料消費税
        ,   D_DamageInterestAmount             -- 当日遅延損害金
        ,   D_CanSettlementFee                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,   D_CanClaimFee                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,   D_CanClaimFeeTax                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        )
    SELECT  v_BusinessDate                     -- 集計日
        ,   CASE
                WHEN DATE_FORMAT(o.OemClaimTransDate, '%Y-%m') < DATE_FORMAT(v_AccountingMonth, '%Y-%m') THEN v_AccountingMonth
                ELSE DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01')
            END                                -- 会計月
        ,   v_EnterpriseId                     -- 加盟店ID
        ,   null                               -- 当日立替件数
        ,   null                               -- 当日立替金額
        ,   null                               -- 当日ｷｬﾝｾﾙ件数
        ,   null                               -- 当日ｷｬﾝｾﾙ立替金額
        ,   null                               -- 当日立替金戻し件数
        ,   null                               -- 当日立替金戻し金額
        ,   1                                  -- 当日OEM移管件数
        ,   SUM(rc.CheckingUseAmount) * -1     -- 当日OEM移管金額
        ,   null                               -- 当日再発行手数料件数
        ,   null                               -- 当日再発行手数料額
        ,   null                               -- 当日遅延損害金件数
        ,   null                               -- 当日遅延損害金額
        ,   null                               -- 当日入金件数
        ,   null                               -- 当日入金額
        ,   null                               -- 当日返金件数
        ,   null                               -- 当日返金額
        ,   null                               -- 当日貸倒件数
        ,   null                               -- 当日貸倒金額
        ,   null                               -- 当日その他件数
        ,   null                               -- 当日その他金額
        ,   null                               -- 当日決済手数料
        ,   null                               -- 当日請求手数料
        ,   null                               -- 当日請求手数料消費税
        ,   null                               -- 当日月額固定費
        ,   null                               -- 当日月額固定費消費税
        ,   null                               -- 当日同梱月額固定費
        ,   null                               -- 当日同梱月額固定費消費税
        ,   null                               -- 当日API月額固定費
        ,   null                               -- 当日API月額固定費消費税
        ,   null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,   null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,   null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,   null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,   null                               -- 当日月額固定費予備
        ,   null                               -- 当日月額固定費予備消費税
        ,   null                               -- 当日再発行手数料
        ,   null                               -- 当日再発行手数料消費税
        ,   null                               -- 当日遅延損害金
        ,   null                               -- 当日ｷｬﾝｾﾙ決済手数料
        ,   null                               -- 当日ｷｬﾝｾﾙ請求手数料
        ,   null                               -- 当日ｷｬﾝｾﾙ請求手数料消費税
    FROM    T_Order o
            INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq)
            INNER JOIN T_ReceiptControl rc ON (rc.OrderSeq = o.OrderSeq AND rc.ValidFlg = 1)
            INNER JOIN M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode  = IFNULL( o.OemId, 0 ))
    WHERE   o.OemClaimTransDate IS NOT NULL
    AND     o.OemClaimTransDate <= v_BusinessDate
    AND     o.ValidFlg          =  1
    AND     o.OemClaimTransFlg  =  1
    AND     c1.Class1           <> 0
    AND     rc.ReceiptDate < o.OemClaimTransDate 
    AND     o.OemClaimTransDate <= DATE(rc.ReceiptProcessDate)
    AND     rc.DailySummaryFlg = 0
    AND     NOT (   DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01') < DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01')  -- 月を跨いで入金処理がされた
                AND EXISTS(SELECT * FROM AT_ReportFileMonthly WHERE DATE_FORMAT(CreateDate, '%Y-%m-01') = DATE_FORMAT(rc.ReceiptProcessDate, '%Y-%m-01')) -- 既に会計締めデータが存在する
                )
    GROUP BY o.OrderSeq
    HAVING SUM(rc.CheckingUseAmount) > 0;

    -- ------------------------------
    -- 1.6.詳細設計書:貸倒債権一覧の「顧客」より貸倒情報を日別直営日次統計表ﾜｰｸに出力する
    -- ------------------------------
    INSERT INTO AW_Daily2StatisticsTable (
         ProcessingDate                     -- 集計日
        ,AccountDate                        -- 会計月
        ,EnterpriseId                       -- 加盟店ID
        ,D_ChargeCount                      -- 当日立替件数
        ,D_ChargeAmount                     -- 当日立替金額
        ,D_CancelCount                      -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                     -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount              -- 当日立替金戻し件数
        ,D_SettlementBackAmount             -- 当日立替金戻し金額
        ,D_OemTransferCount                 -- 当日OEM移管件数
        ,D_OemTransferAmount                -- 当日OEM移管金額
        ,D_ReClaimFeeCount                  -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                 -- 当日再発行手数料額
        ,D_DamageCount                      -- 当日遅延損害金件数
        ,D_DamageAmount                     -- 当日遅延損害金額
        ,D_ReceiptCount                     -- 当日入金件数
        ,D_ReceiptAmount                    -- 当日入金額
        ,D_RepayCount                       -- 当日返金件数
        ,D_RepayAmount                      -- 当日返金額
        ,D_BadDebtCount                     -- 当日貸倒件数
        ,D_BadDebtAmount                    -- 当日貸倒金額
        ,D_OtherPaymentCount                -- 当日その他件数
        ,D_OtherPaymentAmount               -- 当日その他金額
        ,D_SettlementFee                    -- 当日決済手数料
        ,D_ClaimFee                         -- 当日請求手数料
        ,D_ClaimFeeTax                      -- 当日請求手数料消費税
        ,D_MonthlyFee                       -- 当日月額固定費
        ,D_MonthlyFeeTax                    -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax             -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                    -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                 -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax             -- 当日月額固定費予備消費税
        ,D_AddClaimFee                      -- 当日再発行手数料
        ,D_AddClaimFeeTax                   -- 当日再発行手数料消費税
        ,D_DamageInterestAmount             -- 当日遅延損害金
        ,D_CanSettlementFee                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        )
    SELECT
         v_BusinessDate                     -- 集計日
        ,CASE
            WHEN DATE_FORMAT( sc.ProcessDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            THEN v_AccountingMonth
            ELSE DATE_FORMAT( sc.ProcessDate, '%Y-%m-01' )
         END                                -- 会計月
        ,o.EnterpriseId                     -- 加盟店ID
        ,null                               -- 当日立替件数
        ,null                               -- 当日立替金額
        ,null                               -- 当日ｷｬﾝｾﾙ件数
        ,null                               -- 当日ｷｬﾝｾﾙ立替金額
        ,null                               -- 当日立替金戻し件数
        ,null                               -- 当日立替金戻し金額
        ,null                               -- 当日OEM移管件数
        ,null                               -- 当日OEM移管金額
        ,null                               -- 当日再発行手数料件数
        ,null                               -- 当日再発行手数料額
        ,null                               -- 当日遅延損害金件数
        ,null                               -- 当日遅延損害金額
        ,null                               -- 当日入金件数
        ,null                               -- 当日入金額
        ,null                               -- 当日返金件数
        ,null                               -- 当日返金額
        ,1                                  -- 当日貸倒件数
        ,CASE WHEN sc.SundryType = 0
            THEN sc.SundryAmount
            ELSE sc.SundryAmount * -1
         END                                -- 当日貸倒金額
        ,null                               -- 当日その他件数
        ,null                               -- 当日その他金額
        ,null                               -- 当日決済手数料
        ,null                               -- 当日請求手数料
        ,null                               -- 当日請求手数料消費税
        ,null                               -- 当日月額固定費
        ,null                               -- 当日月額固定費消費税
        ,null                               -- 当日同梱月額固定費
        ,null                               -- 当日同梱月額固定費消費税
        ,null                               -- 当日API月額固定費
        ,null                               -- 当日API月額固定費消費税
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日月額固定費予備
        ,null                               -- 当日月額固定費予備消費税
        ,null                               -- 当日再発行手数料
        ,null                               -- 当日再発行手数料消費税
        ,null                               -- 当日遅延損害金
        ,null                               -- 当日ｷｬﾝｾﾙ決済手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料消費税
    FROM        T_SundryControl sc
    INNER JOIN  T_Order o
    ON     o.OrderSeq   = sc.OrderSeq
    INNER JOIN  M_Code c1
    ON     c1.CodeId    = 160
    AND    c1.KeyCode   = IFNULL( o.OemId, 0 )
    INNER JOIN  M_Code c2
    ON     c2.CodeId    = 96
    AND    c2.KeyCode   = sc.SundryClass
    AND    c2.Class3    = 1
    WHERE  sc.DailySummaryFlg =  0
    AND    sc.ValidFlg        =  1
    AND    sc.SundryAmount    <> 0
    AND    sc.ProcessDate <= v_BusinessDate
    AND    c1.Class1          =  0
    AND    c2.Class2          =  1;

    INSERT INTO AW_Daily2StatisticsTable (
         ProcessingDate                     -- 集計日
        ,AccountDate                        -- 会計月
        ,EnterpriseId                       -- 加盟店ID
        ,D_ChargeCount                      -- 当日立替件数
        ,D_ChargeAmount                     -- 当日立替金額
        ,D_CancelCount                      -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                     -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount              -- 当日立替金戻し件数
        ,D_SettlementBackAmount             -- 当日立替金戻し金額
        ,D_OemTransferCount                 -- 当日OEM移管件数
        ,D_OemTransferAmount                -- 当日OEM移管金額
        ,D_ReClaimFeeCount                  -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                 -- 当日再発行手数料額
        ,D_DamageCount                      -- 当日遅延損害金件数
        ,D_DamageAmount                     -- 当日遅延損害金額
        ,D_ReceiptCount                     -- 当日入金件数
        ,D_ReceiptAmount                    -- 当日入金額
        ,D_RepayCount                       -- 当日返金件数
        ,D_RepayAmount                      -- 当日返金額
        ,D_BadDebtCount                     -- 当日貸倒件数
        ,D_BadDebtAmount                    -- 当日貸倒金額
        ,D_OtherPaymentCount                -- 当日その他件数
        ,D_OtherPaymentAmount               -- 当日その他金額
        ,D_SettlementFee                    -- 当日決済手数料
        ,D_ClaimFee                         -- 当日請求手数料
        ,D_ClaimFeeTax                      -- 当日請求手数料消費税
        ,D_MonthlyFee                       -- 当日月額固定費
        ,D_MonthlyFeeTax                    -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax             -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                    -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                 -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax             -- 当日月額固定費予備消費税
        ,D_AddClaimFee                      -- 当日再発行手数料
        ,D_AddClaimFeeTax                   -- 当日再発行手数料消費税
        ,D_DamageInterestAmount             -- 当日遅延損害金
        ,D_CanSettlementFee                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        )
    SELECT
         v_BusinessDate                     -- 集計日
        ,CASE
            WHEN DATE_FORMAT( sc.ProcessDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            THEN v_AccountingMonth
            ELSE DATE_FORMAT( sc.ProcessDate, '%Y-%m-01' )
         END                                -- 会計月
        ,v_EnterpriseId                     -- 加盟店ID
        ,null                               -- 当日立替件数
        ,null                               -- 当日立替金額
        ,null                               -- 当日ｷｬﾝｾﾙ件数
        ,null                               -- 当日ｷｬﾝｾﾙ立替金額
        ,null                               -- 当日立替金戻し件数
        ,null                               -- 当日立替金戻し金額
        ,null                               -- 当日OEM移管件数
        ,null                               -- 当日OEM移管金額
        ,null                               -- 当日再発行手数料件数
        ,null                               -- 当日再発行手数料額
        ,null                               -- 当日遅延損害金件数
        ,null                               -- 当日遅延損害金額
        ,null                               -- 当日入金件数
        ,null                               -- 当日入金額
        ,null                               -- 当日返金件数
        ,null                               -- 当日返金額
        ,1                                  -- 当日貸倒件数
        ,CASE WHEN sc.SundryType = 0
            THEN sc.SundryAmount
            ELSE sc.SundryAmount * -1
         END                                -- 当日貸倒金額
        ,null                               -- 当日その他件数
        ,null                               -- 当日その他金額
        ,null                               -- 当日決済手数料
        ,null                               -- 当日請求手数料
        ,null                               -- 当日請求手数料消費税
        ,null                               -- 当日月額固定費
        ,null                               -- 当日月額固定費消費税
        ,null                               -- 当日同梱月額固定費
        ,null                               -- 当日同梱月額固定費消費税
        ,null                               -- 当日API月額固定費
        ,null                               -- 当日API月額固定費消費税
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日月額固定費予備
        ,null                               -- 当日月額固定費予備消費税
        ,null                               -- 当日再発行手数料
        ,null                               -- 当日再発行手数料消費税
        ,null                               -- 当日遅延損害金
        ,null                               -- 当日ｷｬﾝｾﾙ決済手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料消費税
    FROm        T_SundryControl sc
    INNER JOIN  T_Order o
    ON     o.OrderSeq   = sc.OrderSeq
    INNER JOIN  M_Code c1
    ON     c1.CodeId    = 160
    AND    c1.KeyCode   = IFNULL( o.OemId, 0 )
    INNER JOIN  M_Code c2
    ON     c2.CodeId    = 96
    AND    c2.KeyCode   = sc.SundryClass
    AND    c2.Class3    = 1
    WHERE  sc.DailySummaryFlg  =  0
    AND    sc.ValidFlg         =  1
    AND    sc.SundryAmount     <> 0
    AND    sc.ProcessDate <= v_BusinessDate
    AND    o.OemClaimTransDate IS NOT NULL
    AND    o.OemClaimTransDate <=  sc.ProcessDate
    AND    c1.Class1           <> 0
    AND    c2.Class2           =  1;

    -- ------------------------------
    -- 1.7.詳細設計書:再発行手数料明細より再発行手数料情報を日別直営日次統計表ﾜｰｸに出力する
    -- ------------------------------
    -- 1) 再発行手数料件数を取得
    INSERT INTO AW_Daily2StatisticsTable (
         ProcessingDate                     -- 集計日
        ,AccountDate                        -- 会計月
        ,EnterpriseId                       -- 加盟店ID
        ,D_ChargeCount                      -- 当日立替件数
        ,D_ChargeAmount                     -- 当日立替金額
        ,D_CancelCount                      -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                     -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount              -- 当日立替金戻し件数
        ,D_SettlementBackAmount             -- 当日立替金戻し金額
        ,D_OemTransferCount                 -- 当日OEM移管件数
        ,D_OemTransferAmount                -- 当日OEM移管金額
        ,D_ReClaimFeeCount                  -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                 -- 当日再発行手数料額
        ,D_DamageCount                      -- 当日遅延損害金件数
        ,D_DamageAmount                     -- 当日遅延損害金額
        ,D_ReceiptCount                     -- 当日入金件数
        ,D_ReceiptAmount                    -- 当日入金額
        ,D_RepayCount                       -- 当日返金件数
        ,D_RepayAmount                      -- 当日返金額
        ,D_BadDebtCount                     -- 当日貸倒件数
        ,D_BadDebtAmount                    -- 当日貸倒金額
        ,D_OtherPaymentCount                -- 当日その他件数
        ,D_OtherPaymentAmount               -- 当日その他金額
        ,D_SettlementFee                    -- 当日決済手数料
        ,D_ClaimFee                         -- 当日請求手数料
        ,D_ClaimFeeTax                      -- 当日請求手数料消費税
        ,D_MonthlyFee                       -- 当日月額固定費
        ,D_MonthlyFeeTax                    -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax             -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                    -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                 -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax             -- 当日月額固定費予備消費税
        ,D_AddClaimFee                      -- 当日再発行手数料
        ,D_AddClaimFeeTax                   -- 当日再発行手数料消費税
        ,D_DamageInterestAmount             -- 当日遅延損害金
        ,D_CanSettlementFee                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        )
    SELECT
         v_BusinessDate                     -- 集計日
        ,CASE
            WHEN DATE_FORMAT( rc.ReceiptDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            THEN v_AccountingMonth
            ELSE DATE_FORMAT( rc.ReceiptDate, '%Y-%m-01' )
         END                                -- 会計月
        ,o.EnterpriseId                     -- 加盟店ID
        ,null                               -- 当日立替件数
        ,null                               -- 当日立替金額
        ,null                               -- 当日ｷｬﾝｾﾙ件数
        ,null                               -- 当日ｷｬﾝｾﾙ立替金額
        ,null                               -- 当日立替金戻し件数
        ,null                               -- 当日立替金戻し金額
        ,null                               -- 当日OEM移管件数
        ,null                               -- 当日OEM移管金額
        ,1                                  -- 当日再発行手数料件数
        ,rc.CheckingClaimFee                -- 当日再発行手数料額
        ,null                               -- 当日遅延損害金件数
        ,null                               -- 当日遅延損害金額
        ,null                               -- 当日入金件数
        ,null                               -- 当日入金額
        ,null                               -- 当日返金件数
        ,null                               -- 当日返金額
        ,null                               -- 当日貸倒件数
        ,null                               -- 当日貸倒金額
        ,null                               -- 当日その他件数
        ,null                               -- 当日その他金額
        ,null                               -- 当日決済手数料
        ,null                               -- 当日請求手数料
        ,null                               -- 当日請求手数料消費税
        ,null                               -- 当日月額固定費
        ,null                               -- 当日月額固定費消費税
        ,null                               -- 当日同梱月額固定費
        ,null                               -- 当日同梱月額固定費消費税
        ,null                               -- 当日API月額固定費
        ,null                               -- 当日API月額固定費消費税
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日月額固定費予備
        ,null                               -- 当日月額固定費予備消費税
        -- 入金管理から取得している場合、ﾏｲﾅｽがありえる → 入金取消された場合、ﾏｲﾅｽのﾃﾞｰﾀが発生 → 計算する際にはﾌﾟﾗｽにし、その後ﾏｲﾅｽに戻す
        ,CASE WHEN rc.CheckingClaimFee < 0
            THEN ((rc.CheckingClaimFee * -1) - FLOOR((rc.CheckingClaimFee * -1) * F_GetTaxRate(rc.ReceiptDate ) / (100 + F_GetTaxRate(rc.ReceiptDate)))) * -1
            ELSE rc.CheckingClaimFee - FLOOR (rc.CheckingClaimFee * F_GetTaxRate(rc.ReceiptDate ) / (100 + F_GetTaxRate(rc.ReceiptDate)))
         END                                -- 当日再発行手数料
        ,CASE WHEN rc.CheckingClaimFee < 0
            THEN (FLOOR((rc.CheckingClaimFee * -1) * F_GetTaxRate(rc.ReceiptDate ) / (100 + F_GetTaxRate(rc.ReceiptDate)))) * -1
            ELSE FLOOR (rc.CheckingClaimFee * F_GetTaxRate(rc.ReceiptDate ) / (100 + F_GetTaxRate(rc.ReceiptDate)))
         END                                -- 当日再発行手数料消費税
        ,null                               -- 当日遅延損害金
        ,null                               -- 当日ｷｬﾝｾﾙ決済手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料消費税
    FROM        T_ReceiptControl rc
    INNER JOIN  T_Order o
    ON      o.OrderSeq  = rc.OrderSeq
    INNER JOIN  M_Code c1
    ON      c1.CodeId   = 160
    AND     c1.KeyCode  = IFNULL( o.OemId, 0 )
    WHERE   rc.DailySummaryFlg  = 0
    AND     DATE_FORMAT( rc.ReceiptProcessDate, '%Y-%m-%d' ) <= v_BusinessDate
    AND     IFNULL(rc.CheckingClaimFee, 0) <> 0
    AND     rc.ValidFlg         = 1
    AND     (c1.Class1 = 0 OR (c1.Class1 = 1 AND o.OemClaimTransDate IS NOT NULL AND o.OemClaimTransDate <= rc.ReceiptDate));

    -- 2) 遅延損害金件数を取得
    INSERT INTO AW_Daily2StatisticsTable (
         ProcessingDate                     -- 集計日
        ,AccountDate                        -- 会計月
        ,EnterpriseId                       -- 加盟店ID
        ,D_ChargeCount                      -- 当日立替件数
        ,D_ChargeAmount                     -- 当日立替金額
        ,D_CancelCount                      -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                     -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount              -- 当日立替金戻し件数
        ,D_SettlementBackAmount             -- 当日立替金戻し金額
        ,D_OemTransferCount                 -- 当日OEM移管件数
        ,D_OemTransferAmount                -- 当日OEM移管金額
        ,D_ReClaimFeeCount                  -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                 -- 当日再発行手数料額
        ,D_DamageCount                      -- 当日遅延損害金件数
        ,D_DamageAmount                     -- 当日遅延損害金額
        ,D_ReceiptCount                     -- 当日入金件数
        ,D_ReceiptAmount                    -- 当日入金額
        ,D_RepayCount                       -- 当日返金件数
        ,D_RepayAmount                      -- 当日返金額
        ,D_BadDebtCount                     -- 当日貸倒件数
        ,D_BadDebtAmount                    -- 当日貸倒金額
        ,D_OtherPaymentCount                -- 当日その他件数
        ,D_OtherPaymentAmount               -- 当日その他金額
        ,D_SettlementFee                    -- 当日決済手数料
        ,D_ClaimFee                         -- 当日請求手数料
        ,D_ClaimFeeTax                      -- 当日請求手数料消費税
        ,D_MonthlyFee                       -- 当日月額固定費
        ,D_MonthlyFeeTax                    -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax             -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                    -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                 -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax             -- 当日月額固定費予備消費税
        ,D_AddClaimFee                      -- 当日再発行手数料
        ,D_AddClaimFeeTax                   -- 当日再発行手数料消費税
        ,D_DamageInterestAmount             -- 当日遅延損害金
        ,D_CanSettlementFee                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        )
    SELECT
         v_BusinessDate                     -- 集計日
        ,CASE
            WHEN DATE_FORMAT( rc.ReceiptDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            THEN v_AccountingMonth
            ELSE DATE_FORMAT( rc.ReceiptDate, '%Y-%m-01' )
         END                                -- 会計月
        ,o.EnterpriseId                     -- 加盟店ID
        ,null                               -- 当日立替件数
        ,null                               -- 当日立替金額
        ,null                               -- 当日ｷｬﾝｾﾙ件数
        ,null                               -- 当日ｷｬﾝｾﾙ立替金額
        ,null                               -- 当日立替金戻し件数
        ,null                               -- 当日立替金戻し金額
        ,null                               -- 当日OEM移管件数
        ,null                               -- 当日OEM移管金額
        ,null                               -- 当日再発行手数料件数
        ,null                               -- 当日再発行手数料額
        ,1                                  -- 当日遅延損害金件数
        ,rc.CheckingDamageInterestAmount    -- 当日遅延損害金額
        ,null                               -- 当日入金件数
        ,null                               -- 当日入金額
        ,null                               -- 当日返金件数
        ,null                               -- 当日返金額
        ,null                               -- 当日貸倒件数
        ,null                               -- 当日貸倒金額
        ,null                               -- 当日その他件数
        ,null                               -- 当日その他金額
        ,null                               -- 当日決済手数料
        ,null                               -- 当日請求手数料
        ,null                               -- 当日請求手数料消費税
        ,null                               -- 当日月額固定費
        ,null                               -- 当日月額固定費消費税
        ,null                               -- 当日同梱月額固定費
        ,null                               -- 当日同梱月額固定費消費税
        ,null                               -- 当日API月額固定費
        ,null                               -- 当日API月額固定費消費税
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日月額固定費予備
        ,null                               -- 当日月額固定費予備消費税
        ,null                               -- 当日再発行手数料
        ,null                               -- 当日再発行手数料消費税
        ,rc.CheckingDamageInterestAmount    -- 当日遅延損害金
        ,null                               -- 当日ｷｬﾝｾﾙ決済手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料消費税
    FROM        T_ReceiptControl rc
    INNER JOIN  T_Order o
    ON      o.OrderSeq  = rc.OrderSeq
    INNER JOIN  M_Code c1
    ON      c1.CodeId   = 160
    AND     c1.KeyCode  = IFNULL( o.OemId, 0 )
    WHERE   rc.DailySummaryFlg  = 0
    AND     DATE_FORMAT( rc.ReceiptProcessDate, '%Y-%m-%d' ) <= v_BusinessDate
    AND     IFNULL(rc.CheckingDamageInterestAmount, 0) <> 0
    AND     rc.ValidFlg         = 1
    AND     (c1.Class1 = 0 OR (c1.Class1 = 1 AND o.OemClaimTransDate IS NOT NULL AND o.OemClaimTransDate <= rc.ReceiptDate));

    -- ------------------------------
    -- 1.8.詳細設計書:未収金日計の「■入金管理より作成」①より入金情報を日別直営日次統計表ﾜｰｸに出力する
    -- ------------------------------
    INSERT INTO AW_Daily2StatisticsTable (
         ProcessingDate                     -- 集計日
        ,AccountDate                        -- 会計月
        ,EnterpriseId                       -- 加盟店ID
        ,D_ChargeCount                      -- 当日立替件数
        ,D_ChargeAmount                     -- 当日立替金額
        ,D_CancelCount                      -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                     -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount              -- 当日立替金戻し件数
        ,D_SettlementBackAmount             -- 当日立替金戻し金額
        ,D_OemTransferCount                 -- 当日OEM移管件数
        ,D_OemTransferAmount                -- 当日OEM移管金額
        ,D_ReClaimFeeCount                  -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                 -- 当日再発行手数料額
        ,D_DamageCount                      -- 当日遅延損害金件数
        ,D_DamageAmount                     -- 当日遅延損害金額
        ,D_ReceiptCount                     -- 当日入金件数
        ,D_ReceiptAmount                    -- 当日入金額
        ,D_RepayCount                       -- 当日返金件数
        ,D_RepayAmount                      -- 当日返金額
        ,D_BadDebtCount                     -- 当日貸倒件数
        ,D_BadDebtAmount                    -- 当日貸倒金額
        ,D_OtherPaymentCount                -- 当日その他件数
        ,D_OtherPaymentAmount               -- 当日その他金額
        ,D_SettlementFee                    -- 当日決済手数料
        ,D_ClaimFee                         -- 当日請求手数料
        ,D_ClaimFeeTax                      -- 当日請求手数料消費税
        ,D_MonthlyFee                       -- 当日月額固定費
        ,D_MonthlyFeeTax                    -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax             -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                    -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                 -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax             -- 当日月額固定費予備消費税
        ,D_AddClaimFee                      -- 当日再発行手数料
        ,D_AddClaimFeeTax                   -- 当日再発行手数料消費税
        ,D_DamageInterestAmount             -- 当日遅延損害金
        ,D_CanSettlementFee                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        )
    SELECT
         v_BusinessDate                     -- 集計日
        ,CASE
            WHEN DATE_FORMAT( ad.PaymentDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            THEN v_AccountingMonth
            ELSE DATE_FORMAT( ad.PaymentDate, '%Y-%m-01' )
         END                                -- 会計月
        ,CASE
            WHEN c1.Class1 = 0 THEN o.EnterpriseId
            ELSE v_EnterpriseId
         END                                -- 加盟店ID
        ,null                               -- 当日立替件数
        ,null                               -- 当日立替金額
        ,null                               -- 当日ｷｬﾝｾﾙ件数
        ,null                               -- 当日ｷｬﾝｾﾙ立替金額
        ,null                               -- 当日立替金戻し件数
        ,null                               -- 当日立替金戻し金額
        ,null                               -- 当日OEM移管件数
        ,null                               -- 当日OEM移管金額
        ,null                               -- 当日再発行手数料件数
        ,null                               -- 当日再発行手数料額
        ,null                               -- 当日遅延損害金件数
        ,null                               -- 当日遅延損害金額
        ,ad.PaymentNumber                   -- 当日入金件数
        ,ad.Amount * -1                     -- 当日入金額(消込情報-利用額 + 消込情報-請求手数料 + 消込情報-遅延損害金)
        ,null                               -- 当日返金件数
        ,null                               -- 当日返金額
        ,null                               -- 当日貸倒件数
        ,null                               -- 当日貸倒金額
        ,null                               -- 当日その他件数
        ,null                               -- 当日その他金額
        ,null                               -- 当日決済手数料
        ,null                               -- 当日請求手数料
        ,null                               -- 当日請求手数料消費税
        ,null                               -- 当日月額固定費
        ,null                               -- 当日月額固定費消費税
        ,null                               -- 当日同梱月額固定費
        ,null                               -- 当日同梱月額固定費消費税
        ,null                               -- 当日API月額固定費
        ,null                               -- 当日API月額固定費消費税
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日月額固定費予備
        ,null                               -- 当日月額固定費予備消費税
        ,null                               -- 当日再発行手数料
        ,null                               -- 当日再発行手数料消費税
        ,null                               -- 当日遅延損害金
        ,null                               -- 当日ｷｬﾝｾﾙ決済手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料消費税
    FROM       AW_AccountsDue_DailyAccount ad
    INNER JOIN  T_Order o
    ON      o.OrderSeq = ad.OrderSeq
    INNER JOIN  M_Code c1
    ON      c1.CodeId     = 160
    AND     c1.KeyCode    = IFNULL( o.OemId, 0 )
    WHERE   ad.ReceiptSeq > 0;

    -- ------------------------------
    -- 1.10.詳細設計書:未収金日計の「■返金管理より作成」①より
    -- 入金情報を日別直営日次統計表ﾜｰｸに出力する
    -- ------------------------------
    INSERT INTO AW_Daily2StatisticsTable (
         ProcessingDate                     -- 集計日
        ,AccountDate                        -- 会計月
        ,EnterpriseId                       -- 加盟店ID
        ,D_ChargeCount                      -- 当日立替件数
        ,D_ChargeAmount                     -- 当日立替金額
        ,D_CancelCount                      -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                     -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount              -- 当日立替金戻し件数
        ,D_SettlementBackAmount             -- 当日立替金戻し金額
        ,D_OemTransferCount                 -- 当日OEM移管件数
        ,D_OemTransferAmount                -- 当日OEM移管金額
        ,D_ReClaimFeeCount                  -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                 -- 当日再発行手数料額
        ,D_DamageCount                      -- 当日遅延損害金件数
        ,D_DamageAmount                     -- 当日遅延損害金額
        ,D_ReceiptCount                     -- 当日入金件数
        ,D_ReceiptAmount                    -- 当日入金額
        ,D_RepayCount                       -- 当日返金件数
        ,D_RepayAmount                      -- 当日返金額
        ,D_BadDebtCount                     -- 当日貸倒件数
        ,D_BadDebtAmount                    -- 当日貸倒金額
        ,D_OtherPaymentCount                -- 当日その他件数
        ,D_OtherPaymentAmount               -- 当日その他金額
        ,D_SettlementFee                    -- 当日決済手数料
        ,D_ClaimFee                         -- 当日請求手数料
        ,D_ClaimFeeTax                      -- 当日請求手数料消費税
        ,D_MonthlyFee                       -- 当日月額固定費
        ,D_MonthlyFeeTax                    -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax             -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                    -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                 -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax             -- 当日月額固定費予備消費税
        ,D_AddClaimFee                      -- 当日再発行手数料
        ,D_AddClaimFeeTax                   -- 当日再発行手数料消費税
        ,D_DamageInterestAmount             -- 当日遅延損害金
        ,D_CanSettlementFee                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        )
    SELECT
         v_BusinessDate                     -- 集計日
        ,CASE
            WHEN DATE_FORMAT( rc.DecisionDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            THEN v_AccountingMonth
            ELSE DATE_FORMAT( rc.DecisionDate, '%Y-%m-01' )
         END                                -- 会計月
        ,o.EnterpriseId                     -- 加盟店ID
        ,null                               -- 当日立替件数
        ,null                               -- 当日立替金額
        ,null                               -- 当日ｷｬﾝｾﾙ件数
        ,null                               -- 当日ｷｬﾝｾﾙ立替金額
        ,null                               -- 当日立替金戻し件数
        ,null                               -- 当日立替金戻し金額
        ,null                               -- 当日OEM移管件数
        ,null                               -- 当日OEM移管金額
        ,null                               -- 当日再発行手数料件数
        ,null                               -- 当日再発行手数料額
        ,null                               -- 当日遅延損害金件数
        ,null                               -- 当日遅延損害金額
        ,null                               -- 当日入金件数
        ,null                               -- 当日入金額
        ,1                                  -- 当日返金件数
        ,rc.RepayAmount                     -- 当日返金額
        ,null                               -- 当日貸倒件数
        ,null                               -- 当日貸倒金額
        ,null                               -- 当日その他件数
        ,null                               -- 当日その他金額
        ,null                               -- 当日決済手数料
        ,null                               -- 当日請求手数料
        ,null                               -- 当日請求手数料消費税
        ,null                               -- 当日月額固定費
        ,null                               -- 当日月額固定費消費税
        ,null                               -- 当日同梱月額固定費
        ,null                               -- 当日同梱月額固定費消費税
        ,null                               -- 当日API月額固定費
        ,null                               -- 当日API月額固定費消費税
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日月額固定費予備
        ,null                               -- 当日月額固定費予備消費税
        ,null                               -- 当日再発行手数料
        ,null                               -- 当日再発行手数料消費税
        ,null                               -- 当日遅延損害金
        ,null                               -- 当日ｷｬﾝｾﾙ決済手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料消費税
    FROM        T_RepaymentControl rc
    INNER JOIN  AT_RepaymentControl at_rc
    ON     at_rc.RepaySeq = rc.RepaySeq
    INNER JOIN  T_ClaimControl cc
    ON     cc.ClaimId     = rc.ClaimId
    INNER JOIN  T_Order o
    ON     o.OrderSeq     = cc.OrderSeq
    INNER JOIN  M_Code c1
    ON     c1.CodeId      = 160
    AND    c1.KeyCode     = IFNULL( o.OemId, 0 )
    WHERE  at_rc.DailySummaryFlg    =  0
    AND    rc.ValidFlg              =  1
    AND    rc.RepayStatus           =  1
    AND    rc.RepayAmount           <> 0
    AND    DATE_FORMAT( rc.DecisionDate, '%Y-%m-%d' ) <= v_BusinessDate
    AND    c1.Class1                =  0;

    -- ------------------------------
    -- 1.11.詳細設計書:未収金日計の「■返金管理より作成」② 債権移管後はCBの返金 より
    -- 入金情報を日別直営日次統計表ﾜｰｸに出力する
    -- ------------------------------
    INSERT INTO AW_Daily2StatisticsTable (
         ProcessingDate                     -- 集計日
        ,AccountDate                        -- 会計月
        ,EnterpriseId                       -- 加盟店ID
        ,D_ChargeCount                      -- 当日立替件数
        ,D_ChargeAmount                     -- 当日立替金額
        ,D_CancelCount                      -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                     -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount              -- 当日立替金戻し件数
        ,D_SettlementBackAmount             -- 当日立替金戻し金額
        ,D_OemTransferCount                 -- 当日OEM移管件数
        ,D_OemTransferAmount                -- 当日OEM移管金額
        ,D_ReClaimFeeCount                  -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                 -- 当日再発行手数料額
        ,D_DamageCount                      -- 当日遅延損害金件数
        ,D_DamageAmount                     -- 当日遅延損害金額
        ,D_ReceiptCount                     -- 当日入金件数
        ,D_ReceiptAmount                    -- 当日入金額
        ,D_RepayCount                       -- 当日返金件数
        ,D_RepayAmount                      -- 当日返金額
        ,D_BadDebtCount                     -- 当日貸倒件数
        ,D_BadDebtAmount                    -- 当日貸倒金額
        ,D_OtherPaymentCount                -- 当日その他件数
        ,D_OtherPaymentAmount               -- 当日その他金額
        ,D_SettlementFee                    -- 当日決済手数料
        ,D_ClaimFee                         -- 当日請求手数料
        ,D_ClaimFeeTax                      -- 当日請求手数料消費税
        ,D_MonthlyFee                       -- 当日月額固定費
        ,D_MonthlyFeeTax                    -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax             -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                    -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                 -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax             -- 当日月額固定費予備消費税
        ,D_AddClaimFee                      -- 当日再発行手数料
        ,D_AddClaimFeeTax                   -- 当日再発行手数料消費税
        ,D_DamageInterestAmount             -- 当日遅延損害金
        ,D_CanSettlementFee                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        )
    SELECT
         v_BusinessDate                     -- 集計日
        ,CASE
            WHEN DATE_FORMAT( rc.DecisionDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            THEN v_AccountingMonth
            ELSE DATE_FORMAT( rc.DecisionDate, '%Y-%m-01' )
         END                                -- 会計月
        ,v_EnterpriseId                     -- 加盟店ID
        ,null                               -- 当日立替件数
        ,null                               -- 当日立替金額
        ,null                               -- 当日ｷｬﾝｾﾙ件数
        ,null                               -- 当日ｷｬﾝｾﾙ立替金額
        ,null                               -- 当日立替金戻し件数
        ,null                               -- 当日立替金戻し金額
        ,null                               -- 当日OEM移管件数
        ,null                               -- 当日OEM移管金額
        ,null                               -- 当日再発行手数料件数
        ,null                               -- 当日再発行手数料額
        ,null                               -- 当日遅延損害金件数
        ,null                               -- 当日遅延損害金額
        ,null                               -- 当日入金件数
        ,null                               -- 当日入金額
        ,1                                  -- 当日返金件数
        ,rc.RepayAmount                     -- 当日返金額
        ,null                               -- 当日貸倒件数
        ,null                               -- 当日貸倒金額
        ,null                               -- 当日その他件数
        ,null                               -- 当日その他金額
        ,null                               -- 当日決済手数料
        ,null                               -- 当日請求手数料
        ,null                               -- 当日請求手数料消費税
        ,null                               -- 当日月額固定費
        ,null                               -- 当日月額固定費消費税
        ,null                               -- 当日同梱月額固定費
        ,null                               -- 当日同梱月額固定費消費税
        ,null                               -- 当日API月額固定費
        ,null                               -- 当日API月額固定費消費税
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日月額固定費予備
        ,null                               -- 当日月額固定費予備消費税
        ,null                               -- 当日再発行手数料
        ,null                               -- 当日再発行手数料消費税
        ,null                               -- 当日遅延損害金
        ,null                               -- 当日ｷｬﾝｾﾙ決済手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料消費税
    FROM        T_RepaymentControl rc
    INNER JOIN  AT_RepaymentControl at_rc
    ON     at_rc.RepaySeq = rc.RepaySeq
    INNER JOIN  T_ClaimControl cc
    ON     cc.ClaimId     = rc.ClaimId
    INNER JOIN  T_Order o
    ON     o.OrderSeq     = cc.OrderSeq
    INNER JOIN  M_Code c1
    ON     c1.CodeId      = 160
    AND    c1.KeyCode     = IFNULL( o.OemId, 0 )
    WHERE  at_rc.DailySummaryFlg    =  0
    AND    rc.ValidFlg              =  1
    AND    rc.RepayStatus           =  1
    AND    rc.RepayAmount           <> 0
    AND    DATE_FORMAT( rc.DecisionDate, '%Y-%m-%d' ) <= v_BusinessDate
    AND    o.OemClaimTransDate IS NOT NULL
    AND    o.OemClaimTransDate      <=  DATE_FORMAT( rc.DecisionDate, '%Y-%m-%d' )
    AND    c1.Class1                <> 0;

    -- ------------------------------
    -- 1.11.1.詳細設計書:未収金日計の「■精算日計より返金作成」より
    -- 加盟店への返金加算情報を日別直営日次統計表ﾜｰｸに出力する
    -- ------------------------------
        INSERT INTO AW_Daily2StatisticsTable (
         ProcessingDate                     -- 集計日
        ,AccountDate                        -- 会計月
        ,EnterpriseId                       -- 加盟店ID
        ,D_ChargeCount                      -- 当日立替件数
        ,D_ChargeAmount                     -- 当日立替金額
        ,D_CancelCount                      -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                     -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount              -- 当日立替金戻し件数
        ,D_SettlementBackAmount             -- 当日立替金戻し金額
        ,D_OemTransferCount                 -- 当日OEM移管件数
        ,D_OemTransferAmount                -- 当日OEM移管金額
        ,D_ReClaimFeeCount                  -- 当日件数
        ,D_ReClaimFeeAmount                 -- 当日再発行手数料額
        ,D_DamageCount                      -- 当日遅延損害金件数
        ,D_DamageAmount                     -- 当日遅延損害金額
        ,D_ReceiptCount                     -- 当日入金件数
        ,D_ReceiptAmount                    -- 当日入金額
        ,D_RepayCount                       -- 当日返金件数
        ,D_RepayAmount                      -- 当日返金額
        ,D_BadDebtCount                     -- 当日貸倒件数
        ,D_BadDebtAmount                    -- 当日貸倒金額
        ,D_OtherPaymentCount                -- 当日その他件数
        ,D_OtherPaymentAmount               -- 当日その他金額
        ,D_SettlementFee                    -- 当日決済手数料
        ,D_ClaimFee                         -- 当日請求手数料
        ,D_ClaimFeeTax                      -- 当日請求手数料消費税
        ,D_MonthlyFee                       -- 当日月額固定費
        ,D_MonthlyFeeTax                    -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax             -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                    -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                 -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax             -- 当日月額固定費予備消費税
        ,D_AddClaimFee                      -- 当日再発行手数料
        ,D_AddClaimFeeTax                   -- 当日再発行手数料消費税
        ,D_DamageInterestAmount             -- 当日遅延損害金
        ,D_CanSettlementFee                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        )
    SELECT
         v_BusinessDate                     -- 集計日
        ,CASE
            WHEN DATE_FORMAT(ProcessingDate, '%Y-%m') < DATE_FORMAT(v_AccountingMonth, '%Y-%m') THEN v_AccountingMonth
            ELSE DATE_FORMAT(ProcessingDate, '%Y-%m-01')
         END                                -- 会計月
        ,EnterpriseId                       -- 加盟店ID
        ,null                               -- 当日立替件数
        ,null                               -- 当日立替金額
        ,null                               -- 当日ｷｬﾝｾﾙ件数
        ,null                               -- 当日ｷｬﾝｾﾙ立替金額
        ,null                               -- 当日立替金戻し件数
        ,null                               -- 当日立替金戻し金額
        ,null                               -- 当日OEM移管件数
        ,null                               -- 当日OEM移管金額
        ,null                               -- 当日再発行手数料件数
        ,null                               -- 当日再発行手数料額
        ,null                               -- 当日遅延損害金件数
        ,null                               -- 当日遅延損害金額
        ,null                               -- 当日入金件数
        ,null                               -- 当日入金額
        ,EnterpriseRefundCount              -- 当日返金件数
        ,EnterpriseRefund                   -- 当日返金額
        ,null                               -- 当日貸倒件数
        ,null                               -- 当日貸倒金額
        ,null                               -- 当日その他件数
        ,null                               -- 当日その他金額
        ,null                               -- 当日決済手数料
        ,null                               -- 当日請求手数料
        ,null                               -- 当日請求手数料消費税
        ,null                               -- 当日月額固定費
        ,null                               -- 当日月額固定費消費税
        ,null                               -- 当日同梱月額固定費
        ,null                               -- 当日同梱月額固定費消費税
        ,null                               -- 当日API月額固定費
        ,null                               -- 当日API月額固定費消費税
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日月額固定費予備
        ,null                               -- 当日月額固定費予備消費税
        ,null                               -- 当日再発行手数料
        ,null                               -- 当日再発行手数料消費税
        ,null                               -- 当日遅延損害金
        ,null                               -- 当日ｷｬﾝｾﾙ決済手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料消費税
    FROM   AT_PayOff_DailyAccount
    WHERE  ValidFlg         =  1
    AND    ProcessingDate   = v_BusinessDate
    AND    OemId            = 0
    AND    EnterpriseRefund <> 0
    ;

    -- ------------------------------
    -- 1.12.詳細設計書:未収金日計の「■雑収入･雑損失管理より作成」①より
    -- その他入金情報を日別直営日次統計表ﾜｰｸに出力する
    -- ------------------------------
    INSERT INTO AW_Daily2StatisticsTable (
         ProcessingDate                     -- 集計日
        ,AccountDate                        -- 会計月
        ,EnterpriseId                       -- 加盟店ID
        ,D_ChargeCount                      -- 当日立替件数
        ,D_ChargeAmount                     -- 当日立替金額
        ,D_CancelCount                      -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                     -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount              -- 当日立替金戻し件数
        ,D_SettlementBackAmount             -- 当日立替金戻し金額
        ,D_OemTransferCount                 -- 当日OEM移管件数
        ,D_OemTransferAmount                -- 当日OEM移管金額
        ,D_ReClaimFeeCount                  -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                 -- 当日再発行手数料額
        ,D_DamageCount                      -- 当日遅延損害金件数
        ,D_DamageAmount                     -- 当日遅延損害金額
        ,D_ReceiptCount                     -- 当日入金件数
        ,D_ReceiptAmount                    -- 当日入金額
        ,D_RepayCount                       -- 当日返金件数
        ,D_RepayAmount                      -- 当日返金額
        ,D_BadDebtCount                     -- 当日貸倒件数
        ,D_BadDebtAmount                    -- 当日貸倒金額
        ,D_OtherPaymentCount                -- 当日その他件数
        ,D_OtherPaymentAmount               -- 当日その他金額
        ,D_SettlementFee                    -- 当日決済手数料
        ,D_ClaimFee                         -- 当日請求手数料
        ,D_ClaimFeeTax                      -- 当日請求手数料消費税
        ,D_MonthlyFee                       -- 当日月額固定費
        ,D_MonthlyFeeTax                    -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax             -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                    -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                 -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax             -- 当日月額固定費予備消費税
        ,D_AddClaimFee                      -- 当日再発行手数料
        ,D_AddClaimFeeTax                   -- 当日再発行手数料消費税
        ,D_DamageInterestAmount             -- 当日遅延損害金
        ,D_CanSettlementFee                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        )
    SELECT
         v_BusinessDate                     -- 集計日
        ,CASE
            WHEN DATE_FORMAT( sc.ProcessDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            THEN v_AccountingMonth
            ELSE DATE_FORMAT( sc.ProcessDate, '%Y-%m-01' )
         END                                -- 会計月
        ,o.EnterpriseId                     -- 加盟店ID
        ,null                               -- 当日立替件数
        ,null                               -- 当日立替金額
        ,null                               -- 当日ｷｬﾝｾﾙ件数
        ,null                               -- 当日ｷｬﾝｾﾙ立替金額
        ,null                               -- 当日立替金戻し件数
        ,null                               -- 当日立替金戻し金額
        ,null                               -- 当日OEM移管件数
        ,null                               -- 当日OEM移管金額
        ,null                               -- 当日再発行手数料件数
        ,null                               -- 当日再発行手数料額
        ,null                               -- 当日遅延損害金件数
        ,null                               -- 当日遅延損害金額
        ,null                               -- 当日入金件数
        ,null                               -- 当日入金額
        ,null                               -- 当日返金件数
        ,null                               -- 当日返金額
        ,null                               -- 当日貸倒件数
        ,null                               -- 当日貸倒金額
        ,1                                  -- 当日その他件数
        ,CASE WHEN sc.SundryType = 0
            THEN sc.SundryAmount
            ELSE sc.SundryAmount * -1
         END                                -- 当日その他金額
        ,null                               -- 当日決済手数料
        ,null                               -- 当日請求手数料
        ,null                               -- 当日請求手数料消費税
        ,null                               -- 当日月額固定費
        ,null                               -- 当日月額固定費消費税
        ,null                               -- 当日同梱月額固定費
        ,null                               -- 当日同梱月額固定費消費税
        ,null                               -- 当日API月額固定費
        ,null                               -- 当日API月額固定費消費税
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日月額固定費予備
        ,null                               -- 当日月額固定費予備消費税
        ,null                               -- 当日再発行手数料
        ,null                               -- 当日再発行手数料消費税
        ,null                               -- 当日遅延損害金
        ,null                               -- 当日ｷｬﾝｾﾙ決済手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料消費税
    FROm        T_SundryControl sc
    INNER JOIN  T_Order o
    ON     o.OrderSeq   = sc.OrderSeq
    INNER JOIN  M_Code c1
    ON     c1.CodeId    = 160
    AND    c1.KeyCode   = IFNULL( o.OemId, 0 )
    INNER JOIN  M_Code c2
    ON     c2.CodeId    = 96
    AND    c2.KeyCode   = sc.SundryClass
    AND    c2.Class3    <> 1
    WHERE  sc.DailySummaryFlg =  0
    AND    sc.ValidFlg        =  1
    AND    sc.SundryAmount    <> 0
    AND    sc.ProcessDate <= v_BusinessDate
    AND    c1.Class1          =  0
    AND    c2.Class2          =  1;

    -- ------------------------------
    -- 1.13.詳細設計書:未収金日計の「■雑収入･雑損失管理より作成」②(債権移管後)より
    -- その他入金情報を日別直営日次統計表ﾜｰｸに出力す
    -- ------------------------------
    INSERT INTO AW_Daily2StatisticsTable (
         ProcessingDate                     -- 集計日
        ,AccountDate                        -- 会計月
        ,EnterpriseId                       -- 加盟店ID
        ,D_ChargeCount                      -- 当日立替件数
        ,D_ChargeAmount                     -- 当日立替金額
        ,D_CancelCount                      -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                     -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount              -- 当日立替金戻し件数
        ,D_SettlementBackAmount             -- 当日立替金戻し金額
        ,D_OemTransferCount                 -- 当日OEM移管件数
        ,D_OemTransferAmount                -- 当日OEM移管金額
        ,D_ReClaimFeeCount                  -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                 -- 当日再発行手数料額
        ,D_DamageCount                      -- 当日遅延損害金件数
        ,D_DamageAmount                     -- 当日遅延損害金額
        ,D_ReceiptCount                     -- 当日入金件数
        ,D_ReceiptAmount                    -- 当日入金額
        ,D_RepayCount                       -- 当日返金件数
        ,D_RepayAmount                      -- 当日返金額
        ,D_BadDebtCount                     -- 当日貸倒件数
        ,D_BadDebtAmount                    -- 当日貸倒金額
        ,D_OtherPaymentCount                -- 当日その他件数
        ,D_OtherPaymentAmount               -- 当日その他金額
        ,D_SettlementFee                    -- 当日決済手数料
        ,D_ClaimFee                         -- 当日請求手数料
        ,D_ClaimFeeTax                      -- 当日請求手数料消費税
        ,D_MonthlyFee                       -- 当日月額固定費
        ,D_MonthlyFeeTax                    -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax             -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                    -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                 -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax             -- 当日月額固定費予備消費税
        ,D_AddClaimFee                      -- 当日再発行手数料
        ,D_AddClaimFeeTax                   -- 当日再発行手数料消費税
        ,D_DamageInterestAmount             -- 当日遅延損害金
        ,D_CanSettlementFee                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        )
    SELECT
         v_BusinessDate                     -- 集計日
        ,CASE
            WHEN DATE_FORMAT( sc.ProcessDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            THEN v_AccountingMonth
            ELSE DATE_FORMAT( sc.ProcessDate, '%Y-%m-01' )
         END                                -- 会計月
        ,v_EnterpriseId                     -- 加盟店ID
        ,null                               -- 当日立替件数
        ,null                               -- 当日立替金額
        ,null                               -- 当日ｷｬﾝｾﾙ件数
        ,null                               -- 当日ｷｬﾝｾﾙ立替金額
        ,null                               -- 当日立替金戻し件数
        ,null                               -- 当日立替金戻し金額
        ,null                               -- 当日OEM移管件数
        ,null                               -- 当日OEM移管金額
        ,null                               -- 当日再発行手数料件数
        ,null                               -- 当日再発行手数料額
        ,null                               -- 当日遅延損害金件数
        ,null                               -- 当日遅延損害金額
        ,null                               -- 当日入金件数
        ,null                               -- 当日入金額
        ,null                               -- 当日返金件数
        ,null                               -- 当日返金額
        ,null                               -- 当日貸倒件数
        ,null                               -- 当日貸倒金額
        ,1                                  -- 当日その他件数
        ,CASE WHEN sc.SundryType = 0
            THEN sc.SundryAmount
            ELSE sc.SundryAmount * -1
         END                                -- 当日その他金額
        ,null                               -- 当日決済手数料
        ,null                               -- 当日請求手数料
        ,null                               -- 当日請求手数料消費税
        ,null                               -- 当日月額固定費
        ,null                               -- 当日月額固定費消費税
        ,null                               -- 当日同梱月額固定費
        ,null                               -- 当日同梱月額固定費消費税
        ,null                               -- 当日API月額固定費
        ,null                               -- 当日API月額固定費消費税
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                               -- 当日月額固定費予備
        ,null                               -- 当日月額固定費予備消費税
        ,null                               -- 当日再発行手数料
        ,null                               -- 当日再発行手数料消費税
        ,null                               -- 当日遅延損害金
        ,null                               -- 当日ｷｬﾝｾﾙ決済手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料
        ,null                               -- 当日ｷｬﾝｾﾙ請求手数料消費税
    FROm        T_SundryControl sc
    INNER JOIN  T_Order o
    ON     o.OrderSeq   = sc.OrderSeq
    INNER JOIN  M_Code c1
    ON     c1.CodeId    = 160
    AND    c1.KeyCode   = IFNULL( o.OemId, 0 )
    INNER JOIN  M_Code c2
    ON     c2.CodeId    = 96
    AND    c2.KeyCode   = sc.SundryClass
    AND    c2.Class3    <> 1
    WHERE  sc.DailySummaryFlg  =  0
    AND    sc.ValidFlg         =  1
    AND    sc.SundryAmount     <> 0
    AND    sc.ProcessDate <= v_BusinessDate
    AND    o.OemClaimTransDate IS NOT NULL
    AND    o.OemClaimTransDate <=  sc.ProcessDate
    AND    c1.Class1           <> 0
    AND    c2.Class2           =  1;

    -- ------------------------------
    -- 1.14.詳細設計書:日別直営日次統計表ﾜｰｸを集約し日別直営日次統計表に出力する
    -- ------------------------------
    INSERT INTO AT_Daily2StatisticsTable (
         ProcessingDate                     -- 集計日
        ,AccountDate                        -- 会計月
        ,EnterpriseId                       -- 加盟店ID
        ,D_ChargeCount                      -- 当日立替件数
        ,D_ChargeAmount                     -- 当日立替金額
        ,D_CancelCount                      -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                     -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount              -- 当日立替金戻し件数
        ,D_SettlementBackAmount             -- 当日立替金戻し金額
        ,D_OemTransferCount                 -- 当日OEM移管件数
        ,D_OemTransferAmount                -- 当日OEM移管金額
        ,D_ReClaimFeeCount                  -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                 -- 当日再発行手数料額
        ,D_DamageCount                      -- 当日遅延損害金件数
        ,D_DamageAmount                     -- 当日遅延損害金額
        ,D_ReceiptCount                     -- 当日入金件数
        ,D_ReceiptAmount                    -- 当日入金額
        ,D_RepayCount                       -- 当日返金件数
        ,D_RepayAmount                      -- 当日返金額
        ,D_BadDebtCount                     -- 当日貸倒件数
        ,D_BadDebtAmount                    -- 当日貸倒金額
        ,D_OtherPaymentCount                -- 当日その他件数
        ,D_OtherPaymentAmount               -- 当日その他金額
        ,D_SettlementFee                    -- 当日決済手数料
        ,D_ClaimFee                         -- 当日請求手数料
        ,D_ClaimFeeTax                      -- 当日請求手数料消費税
        ,D_MonthlyFee                       -- 当日月額固定費
        ,D_MonthlyFeeTax                    -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax             -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                    -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                 -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax             -- 当日月額固定費予備消費税
        ,D_AddClaimFee                      -- 当日再発行手数料
        ,D_AddClaimFeeTax                   -- 当日再発行手数料消費税
        ,D_DamageInterestAmount             -- 当日遅延損害金
        ,D_CanSettlementFee                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        ,RegistDate                         -- 登録日時
        ,RegistId                           -- 登録者
        ,UpdateDate                         -- 更新日時
        ,UpdateId                           -- 更新者
        ,ValidFlg                           -- 有効ﾌﾗｸﾞ
        )
    SELECT
         ProcessingDate                                         -- 集計日
        ,AccountDate                                            -- 会計月
        ,EnterpriseId                                           -- 加盟店ID
        ,SUM(IFNULL(D_ChargeCount, 0))                          -- 当日立替件数
        ,SUM(IFNULL(D_ChargeAmount, 0))                         -- 当日立替金額
        ,SUM(IFNULL(D_CancelCount, 0))                          -- 当日ｷｬﾝｾﾙ件数
        ,SUM(IFNULL(D_CancelAmount, 0))                         -- 当日ｷｬﾝｾﾙ立替金額
        ,SUM(IFNULL(D_SettlementBackCount, 0))                  -- 当日立替金戻し件数
        ,SUM(IFNULL(D_SettlementBackAmount, 0))                 -- 当日立替金戻し金額
        ,SUM(IFNULL(D_OemTransferCount, 0))                     -- 当日OEM移管件数
        ,SUM(IFNULL(D_OemTransferAmount, 0))                    -- 当日OEM移管金額
        ,SUM(IFNULL(D_ReClaimFeeCount, 0))                      -- 当日再発行手数料件数
        ,SUM(IFNULL(D_ReClaimFeeAmount, 0))                     -- 当日再発行手数料額
        ,SUM(IFNULL(D_DamageCount, 0))                          -- 当日遅延損害金件数
        ,SUM(IFNULL(D_DamageAmount, 0))                         -- 当日遅延損害金額
        ,SUM(IFNULL(D_ReceiptCount, 0))                         -- 当日入金件数
        ,SUM(IFNULL(D_ReceiptAmount, 0))                        -- 当日入金額
        ,SUM(IFNULL(D_RepayCount, 0))                           -- 当日返金件数
        ,SUM(IFNULL(D_RepayAmount, 0))                          -- 当日返金額
        ,SUM(IFNULL(D_BadDebtCount, 0))                         -- 当日貸倒件数
        ,SUM(IFNULL(D_BadDebtAmount, 0))                        -- 当日貸倒金額
        ,SUM(IFNULL(D_OtherPaymentCount, 0))                    -- 当日その他件数
        ,SUM(IFNULL(D_OtherPaymentAmount, 0))                   -- 当日その他金額
        ,SUM(IFNULL(D_SettlementFee, 0))                        -- 当日決済手数料
        ,SUM(IFNULL(D_ClaimFee, 0 ))                             -- 当日請求手数料
        ,SUM(IFNULL(D_ClaimFeeTax, 0))                          -- 当日請求手数料消費税
        ,SUM(IFNULL(D_MonthlyFee, 0))                           -- 当日月額固定費
        ,SUM(IFNULL(D_MonthlyFeeTax, 0))                        -- 当日月額固定費消費税
        ,SUM(IFNULL(D_IncludeMonthlyFee, 0))                    -- 当日同梱月額固定費
        ,SUM(IFNULL(D_IncludeMonthlyFeeTax, 0))                 -- 当日同梱月額固定費消費税
        ,SUM(IFNULL(D_ApiMonthlyFee, 0))                        -- 当日API月額固定費
        ,SUM(IFNULL(D_ApiMonthlyFeeTax, 0))                     -- 当日API月額固定費消費税
        ,SUM(IFNULL(D_CreditNoticeMonthlyFee, 0))               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM(IFNULL(D_CreditNoticeMonthlyFeeTax, 0))            -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM(IFNULL(D_NCreditNoticeMonthlyFee, 0))              -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM(IFNULL(D_NCreditNoticeMonthlyFeeTax, 0))           -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM(IFNULL(D_ReserveMonthlyFee, 0))                    -- 当日月額固定費予備
        ,SUM(IFNULL(D_ReserveMonthlyFeeTax, 0))                 -- 当日月額固定費予備消費税
        ,SUM(IFNULL(D_AddClaimFee, 0))                          -- 当日再発行手数料
        ,SUM(IFNULL(D_AddClaimFeeTax, 0))                       -- 当日再発行手数料消費税
        ,SUM(IFNULL(D_DamageInterestAmount, 0))                 -- 当日遅延損害金
        ,SUM(IFNULL(D_CanSettlementFee, 0))                     -- 当日ｷｬﾝｾﾙ決済手数料
        ,SUM(IFNULL(D_CanClaimFee, 0))                          -- 当日ｷｬﾝｾﾙ請求手数料
        ,SUM(IFNULL(D_CanClaimFeeTax, 0))                       -- 当日ｷｬﾝｾﾙ請求手数料消費税
        ,NOW()                                                  -- 登録日時
        ,pi_user_id                                             -- 登録者
        ,NOW()                                                  -- 更新日時
        ,pi_user_id                                             -- 更新者
        ,1                                                      -- 有効ﾌﾗｸﾞ
    FROM  AW_Daily2StatisticsTable
    GROUP BY  ProcessingDate                                    -- 集計日
             ,AccountDate                                       -- 会計月
             ,EnterpriseId;                                     -- 加盟店ID

    -- ------------------------------
    -- 2.1.直営日次統計表ﾜｰｸをﾄﾗﾝｹｰﾄする
    -- ------------------------------
    TRUNCATE AW_DailyStatisticsTable;

    -- ------------------------------
    -- 2.2.業務日付=日別直営日次統計表.集計日のﾃﾞｰﾀを集計出力し日次用の直営日次統計表ﾜｰｸを出力する
    -- ------------------------------
    INSERT INTO AW_DailyStatisticsTable (
         ProcessingDate                                     -- 集計日
        ,AccountDate                                        -- 会計月
        ,EnterpriseId                                       -- 加盟店ID
        ,DB__AccountsReceivableBalance                      -- 前日時点未収金残高
        ,D_ChargeCount                                      -- 当日立替件数
        ,D_ChargeAmount                                     -- 当日立替金額
        ,D_CancelCount                                      -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                                     -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount                              -- 当日立替金戻し件数
        ,D_SettlementBackAmount                             -- 当日立替金戻し金額
        ,D_OemTransferCount                                 -- 当日OEM移管件数
        ,D_OemTransferAmount                                -- 当日OEM移管金額
        ,D_ReClaimFeeCount                                  -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                                 -- 当日再発行手数料額
        ,D_DamageCount                                      -- 当日遅延損害金件数
        ,D_DamageAmount                                     -- 当日遅延損害金額
        ,D_ReceiptCount                                     -- 当日入金件数
        ,D_ReceiptAmount                                    -- 当日入金額
        ,D_RepayCount                                       -- 当日返金件数
        ,D_RepayAmount                                      -- 当日返金額
        ,D_BadDebtCount                                     -- 当日貸倒件数
        ,D_BadDebtAmount                                    -- 当日貸倒金額
        ,D_OtherPaymentCount                                -- 当日その他件数
        ,D_OtherPaymentAmount                               -- 当日その他金額
        ,D_AccountsReceivableBalance                        -- 当日時点未収金残高
        ,D_SettlementFee                                    -- 当日決済手数料
        ,D_ClaimFee                                         -- 当日請求手数料
        ,D_ClaimFeeTax                                      -- 当日請求手数料消費税
        ,D_MonthlyFee                                       -- 当日月額固定費
        ,D_MonthlyFeeTax                                    -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                                -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax                             -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                                    -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                                 -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee                           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax                        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee                          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax                       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                                -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax                             -- 当日月額固定費予備消費税
        ,D_AddClaimFee                                      -- 当日再発行手数料
        ,D_AddClaimFeeTax                                   -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                             -- 当日遅延損害金
        ,D_CanSettlementFee                                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        ,D_SettlementFeeOther                               -- 当日売上明細決済手数料
        ,D_ClaimFeeOther                                    -- 当日売上明細請求手数料
        ,D_ClaimFeeTaxOther                                 -- 当日売上明細請求手数料消費税
        ,D_MonthlyFeeOther                                  -- 当日売上明細月額固定費
        ,D_MonthlyFeeTaxOther                               -- 当日売上明細月額固定費消費税
        ,D_IncludeMonthlyFeeOther                           -- 当日売上明細同梱月額固定費
        ,D_IncludeMonthlyFeeTaxOther                        -- 当日売上明細同梱月額固定費消費税
        ,D_ApiMonthlyFeeOther                               -- 当日売上明細API月額固定費
        ,D_ApiMonthlyFeeTaxOther                            -- 当日売上明細API月額固定費消費税
        ,D_CreditNoticeMonthlyFeeOther                      -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTaxOther                   -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFeeOther                     -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTaxOther                  -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFeeOther                           -- 当日売上明細月額固定費予備
        ,D_ReserveMonthlyFeeTaxOther                        -- 当日売上明細月額固定費予備消費税
        ,D_AddClaimFeeOther                                 -- 当日手数料明細再発行手数料
        ,D_AddClaimFeeTaxOther                              -- 当日手数料明細再発行手数料消費税
        ,D_DamageInterestAmountOther                        -- 当日手数料明細遅延損害金
        ,MB__AccountsReceivableBalance                      -- 月初未収金残高
        ,M_ChargeCount                                      -- 当月立替件数
        ,M_ChargeAmount                                     -- 当月立替金額
        ,M_CancelCount                                      -- 当月ｷｬﾝｾﾙ件数
        ,M_CancelAmount                                     -- 当月ｷｬﾝｾﾙ立替金額
        ,M_SettlementBackCount                              -- 当月立替金戻し件数
        ,M_SettlementBackAmount                             -- 当月立替金戻し金額
        ,M_TransferCount                                    -- 当月OEM移管件数
        ,M_TransferAmount                                   -- 当月OEM移管金額
        ,M_ReClaimFeeCount                                  -- 当月再発行手数料件数
        ,M_ReClaimFeeAmount                                 -- 当月再発行手数料額
        ,M_DamageCount                                      -- 当月遅延損害金件数
        ,M_DamageAmount                                     -- 当月遅延損害金額
        ,M_ReceiptCount                                     -- 当月入金件数
        ,M_ReceiptAmount                                    -- 当月入金額
        ,M_RepayCount                                       -- 当月返金件数
        ,M_RepayAmount                                      -- 当月返金額
        ,M_BadDebtCount                                     -- 当月貸倒件数
        ,M_BadDebtAmount                                    -- 当月貸倒金額
        ,M_OtherPaymentCount                                -- 当月その他件数
        ,M_OtherPaymentAmount                               -- 当月その他金額
        ,M_AccountsReceivableBalance                        -- 当月時点未収金残高
        ,M_SuspensePaymentsAmount                           -- 消費者未収金明細残高
        ,M_AccountsReceivableBalanceDiff                    -- 当月差額
        ,M_SettlementFee                                    -- 当月決済手数料
        ,M_ClaimFee                                         -- 当月請求手数料
        ,M_ClaimFeeTax                                      -- 当月請求手数料消費税
        ,M_MonthlyFee                                       -- 当月月額固定費
        ,M_MonthlyFeeTax                                    -- 当月月額固定費消費税
        ,M_IncludeMonthlyFee                                -- 当月同梱月額固定費
        ,M_IncludeMonthlyFeeTax                             -- 当月同梱月額固定費消費税
        ,M_ApiMonthlyFee                                    -- 当月API月額固定費
        ,M_ApiMonthlyFeeTax                                 -- 当月API月額固定費消費税
        ,M_CreditNoticeMonthlyFee                           -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTax                        -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFee                          -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTax                       -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFee                                -- 当月月額固定費予備
        ,M_ReserveMonthlyFeeTax                             -- 当月月額固定費予備消費税
        ,M_AddClaimFee                                      -- 当月再発行手数料
        ,M_AddClaimFeeTax                                   -- 当月再発行手数料消費税
        ,M_DamageInterestAmount                             -- 当月遅延損害金
        ,M_CanSettlementFee                                 -- 当月ｷｬﾝｾﾙ決済手数料
        ,M_CanClaimFee                                      -- 当月ｷｬﾝｾﾙ請求手数料
        ,M_CanClaimFeeTax                                   -- 当月ｷｬﾝｾﾙ請求手数料消費税
        ,M_SettlementFeeOther                               -- 当月売上明細決済手数料
        ,M_ClaimFeeOther                                    -- 当月売上明細請求手数料
        ,M_ClaimFeeTaxOther                                 -- 当月売上明細請求手数料消費税
        ,M_MonthlyFeeOther                                  -- 当月売上明細月額固定費
        ,M_MonthlyFeeTaxOther                               -- 当月売上明細月額固定費消費税
        ,M_IncludeMonthlyFeeOther                           -- 当月売上明細同梱月額固定費
        ,M_IncludeMonthlyFeeTaxOther                        -- 当月売上明細同梱月額固定費消費税
        ,M_ApiMonthlyFeeOther                               -- 当月売上明細API月額固定費
        ,M_ApiMonthlyFeeTaxOther                            -- 当月売上明細API月額固定費消費税
        ,M_CreditNoticeMonthlyFeeOther                      -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTaxOther                   -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFeeOther                     -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTaxOther                  -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFeeOther                           -- 当月売上明細月額固定費予備
        ,M_ReserveMonthlyFeeTaxOther                        -- 当月売上明細月額固定費予備消費税
        ,M_AddClaimFeeOther                                 -- 当月手数料明細再発行手数料
        ,M_AddClaimFeeTaxOther                              -- 当月手数料明細再発行手数料消費税
        ,M_DamageInterestAmountOther                        -- 当月手数料明細遅延損害金
        ,RegistDate                                         -- 登録日時
        ,RegistId                                           -- 登録者
        ,UpdateDate                                         -- 更新日時
        ,UpdateId                                           -- 更新者
        ,ValidFlg                                           -- 有効ﾌﾗｸﾞ
        )
    SELECT
         null                                               -- 集計日
        ,null                                               -- 会計月
        ,EnterpriseId                                       -- 加盟店ID
        ,null                                               -- 前日時点未収金残高
        ,SUM(IFNULL(D_ChargeCount, 0))                      -- 当日立替件数
        ,SUM(IFNULL(D_ChargeAmount, 0))                     -- 当日立替金額
        ,SUM(IFNULL(D_CancelCount, 0))                      -- 当日ｷｬﾝｾﾙ件数
        ,SUM(IFNULL(D_CancelAmount, 0))                     -- 当日ｷｬﾝｾﾙ立替金額
        ,SUM(IFNULL(D_SettlementBackCount, 0))              -- 当日立替金戻し件数
        ,SUM(IFNULL(D_SettlementBackAmount, 0))             -- 当日立替金戻し金額
        ,SUM(IFNULL(D_OemTransferCount, 0))                 -- 当日OEM移管件数
        ,SUM(IFNULL(D_OemTransferAmount, 0))                -- 当日OEM移管金額
        ,SUM(IFNULL(D_ReClaimFeeCount, 0))                  -- 当日再発行手数料件数
        ,SUM(IFNULL(D_ReClaimFeeAmount, 0))                 -- 当日再発行手数料額
        ,SUM(IFNULL(D_DamageCount, 0))                      -- 当日遅延損害金件数
        ,SUM(IFNULL(D_DamageAmount, 0))                     -- 当日遅延損害金額
        ,SUM(IFNULL(D_ReceiptCount,0))                      -- 当日入金件数
        ,SUM(IFNULL(D_ReceiptAmount,0))                     -- 当日入金額
        ,SUM(IFNULL(D_RepayCount,0))                        -- 当日返金件数
        ,SUM(IFNULL(D_RepayAmount,0))                       -- 当日返金額
        ,SUM(IFNULL(D_BadDebtCount,0))                      -- 当日貸倒件数
        ,SUM(IFNULL(D_BadDebtAmount,0))                     -- 当日貸倒金額
        ,SUM(IFNULL(D_OtherPaymentCount,0))                 -- 当日その他件数
        ,SUM(IFNULL(D_OtherPaymentAmount,0))                -- 当日その他金額
        ,null                                               -- 当日時点未収金残高
        ,SUM(IFNULL(D_SettlementFee, 0))                    -- 当日決済手数料
        ,SUM(IFNULL(D_ClaimFee, 0))                         -- 当日請求手数料
        ,SUM(IFNULL(D_ClaimFeeTax, 0))                      -- 当日請求手数料消費税
        ,SUM(IFNULL(D_MonthlyFee, 0))                       -- 当日月額固定費
        ,SUM(IFNULL(D_MonthlyFeeTax, 0))                    -- 当日月額固定費消費税
        ,SUM(IFNULL(D_IncludeMonthlyFee, 0))                -- 当日同梱月額固定費
        ,SUM(IFNULL(D_IncludeMonthlyFeeTax, 0))             -- 当日同梱月額固定費消費税
        ,SUM(IFNULL(D_ApiMonthlyFee, 0))                    -- 当日API月額固定費
        ,SUM(IFNULL(D_ApiMonthlyFeeTax, 0))                 -- 当日API月額固定費消費税
        ,SUM(IFNULL(D_CreditNoticeMonthlyFee, 0))           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM(IFNULL(D_CreditNoticeMonthlyFeeTax, 0))        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM(IFNULL(D_NCreditNoticeMonthlyFee, 0))          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM(IFNULL(D_NCreditNoticeMonthlyFeeTax, 0))       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM(IFNULL(D_ReserveMonthlyFee, 0))                -- 当日月額固定費予備
        ,SUM(IFNULL(D_ReserveMonthlyFeeTax, 0))             -- 当日月額固定費予備消費税
        ,SUM(IFNULL(D_AddClaimFee, 0))                      -- 当日再発行手数料
        ,SUM(IFNULL(D_AddClaimFeeTax, 0))                   -- 当日再発行手数料消費税
        ,SUM(IFNULL(D_DamageInterestAmount, 0))             -- 当日遅延損害金
        ,SUM(IFNULL(D_CanSettlementFee, 0))                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,SUM(IFNULL(D_CanClaimFee, 0))                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,SUM(IFNULL(D_CanClaimFeeTax, 0))                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        ,null                                               -- 当日売上明細決済手数料
        ,null                                               -- 当日売上明細請求手数料
        ,null                                               -- 当日売上明細請求手数料消費税
        ,null                                               -- 当日売上明細月額固定費
        ,null                                               -- 当日売上明細月額固定費消費税
        ,null                                               -- 当日売上明細同梱月額固定費
        ,null                                               -- 当日売上明細同梱月額固定費消費税
        ,null                                               -- 当日売上明細API月額固定費
        ,null                                               -- 当日売上明細API月額固定費消費税
        ,null                                               -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当日売上明細月額固定費予備
        ,null                                               -- 当日売上明細月額固定費予備消費税
        ,null                                               -- 当日手数料明細再発行手数料
        ,null                                               -- 当日手数料明細再発行手数料消費税
        ,null                                               -- 当日手数料明細遅延損害金
        ,null                                               -- 月初未収金残高
        ,null                                               -- 当月立替件数
        ,null                                               -- 当月立替金額
        ,null                                               -- 当月ｷｬﾝｾﾙ件数
        ,null                                               -- 当月ｷｬﾝｾﾙ立替金額
        ,null                                               -- 当月立替金戻し件数
        ,null                                               -- 当月立替金戻し金額
        ,null                                               -- 当月OEM移管件数
        ,null                                               -- 当月OEM移管金額
        ,null                                               -- 当月再発行手数料件数
        ,null                                               -- 当月再発行手数料額
        ,null                                               -- 当月遅延損害金件数
        ,null                                               -- 当月遅延損害金額
        ,null                                               -- 当月入金件数
        ,null                                               -- 当月入金額
        ,null                                               -- 当月返金件数
        ,null                                               -- 当月返金額
        ,null                                               -- 当月貸倒件数
        ,null                                               -- 当月貸倒金額
        ,null                                               -- 当月その他件数
        ,null                                               -- 当月その他金額
        ,null                                               -- 当月時点未収金残高
        ,null                                               -- 消費者未収金明細残高
        ,null                                               -- 当月差額
        ,null                                               -- 当月決済手数料
        ,null                                               -- 当月請求手数料
        ,null                                               -- 当月請求手数料消費税
        ,null                                               -- 当月月額固定費
        ,null                                               -- 当月月額固定費消費税
        ,null                                               -- 当月同梱月額固定費
        ,null                                               -- 当月同梱月額固定費消費税
        ,null                                               -- 当月API月額固定費
        ,null                                               -- 当月API月額固定費消費税
        ,null                                               -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当月月額固定費予備
        ,null                                               -- 当月月額固定費予備消費税
        ,null                                               -- 当月再発行手数料
        ,null                                               -- 当月再発行手数料消費税
        ,null                                               -- 当月遅延損害金
        ,null                                               -- 当月ｷｬﾝｾﾙ決済手数料
        ,null                                               -- 当月ｷｬﾝｾﾙ請求手数料
        ,null                                               -- 当月ｷｬﾝｾﾙ請求手数料消費税
        ,null                                               -- 当月売上明細決済手数料
        ,null                                               -- 当月売上明細請求手数料
        ,null                                               -- 当月売上明細請求手数料消費税
        ,null                                               -- 当月売上明細月額固定費
        ,null                                               -- 当月売上明細月額固定費消費税
        ,null                                               -- 当月売上明細同梱月額固定費
        ,null                                               -- 当月売上明細同梱月額固定費消費税
        ,null                                               -- 当月売上明細API月額固定費
        ,null                                               -- 当月売上明細API月額固定費消費税
        ,null                                               -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当月売上明細月額固定費予備
        ,null                                               -- 当月売上明細月額固定費予備消費税
        ,null                                               -- 当月手数料明細再発行手数料
        ,null                                               -- 当月手数料明細再発行手数料消費税
        ,null                                               -- 当月手数料明細遅延損害金
        ,NOW()                                              -- 登録日時
        ,pi_user_id                                         -- 登録者
        ,NOW()                                              -- 更新日時
        ,pi_user_id                                         -- 更新者
        ,1                                                  -- 有効ﾌﾗｸﾞ
    FROM  AT_Daily2StatisticsTable
    WHERE ProcessingDate = v_BusinessDate
    AND   AccountDate    = v_AccountingMonth
    GROUP BY  AccountDate                                   -- 会計月
             ,EnterpriseId;                                 -- 加盟店ID


    -- ------------------------------
    -- 2.3.業務日付=日別直営日次統計表.集計日のﾃﾞｰﾀを集計出力し日次用の直営日次統計表ﾜｰｸを出力する
    -- ------------------------------
    INSERT INTO AW_DailyStatisticsTable (
         ProcessingDate                                     -- 集計日
        ,AccountDate                                        -- 会計月
        ,EnterpriseId                                       -- 加盟店ID
        ,DB__AccountsReceivableBalance                      -- 前日時点未収金残高
        ,D_ChargeCount                                      -- 当日立替件数
        ,D_ChargeAmount                                     -- 当日立替金額
        ,D_CancelCount                                      -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                                     -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount                              -- 当日立替金戻し件数
        ,D_SettlementBackAmount                             -- 当日立替金戻し金額
        ,D_OemTransferCount                                 -- 当日OEM移管件数
        ,D_OemTransferAmount                                -- 当日OEM移管金額
        ,D_ReClaimFeeCount                                  -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                                 -- 当日再発行手数料額
        ,D_DamageCount                                      -- 当日遅延損害金件数
        ,D_DamageAmount                                     -- 当日遅延損害金額
        ,D_ReceiptCount                                     -- 当日入金件数
        ,D_ReceiptAmount                                    -- 当日入金額
        ,D_RepayCount                                       -- 当日返金件数
        ,D_RepayAmount                                      -- 当日返金額
        ,D_BadDebtCount                                     -- 当日貸倒件数
        ,D_BadDebtAmount                                    -- 当日貸倒金額
        ,D_OtherPaymentCount                                -- 当日その他件数
        ,D_OtherPaymentAmount                               -- 当日その他金額
        ,D_AccountsReceivableBalance                        -- 当日時点未収金残高
        ,D_SettlementFee                                    -- 当日決済手数料
        ,D_ClaimFee                                         -- 当日請求手数料
        ,D_ClaimFeeTax                                      -- 当日請求手数料消費税
        ,D_MonthlyFee                                       -- 当日月額固定費
        ,D_MonthlyFeeTax                                    -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                                -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax                             -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                                    -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                                 -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee                           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax                        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee                          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax                       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                                -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax                             -- 当日月額固定費予備消費税
        ,D_AddClaimFee                                      -- 当日再発行手数料
        ,D_AddClaimFeeTax                                   -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                             -- 当日遅延損害金
        ,D_CanSettlementFee                                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        ,D_SettlementFeeOther                               -- 当日売上明細決済手数料
        ,D_ClaimFeeOther                                    -- 当日売上明細請求手数料
        ,D_ClaimFeeTaxOther                                 -- 当日売上明細請求手数料消費税
        ,D_MonthlyFeeOther                                  -- 当日売上明細月額固定費
        ,D_MonthlyFeeTaxOther                               -- 当日売上明細月額固定費消費税
        ,D_IncludeMonthlyFeeOther                           -- 当日売上明細同梱月額固定費
        ,D_IncludeMonthlyFeeTaxOther                        -- 当日売上明細同梱月額固定費消費税
        ,D_ApiMonthlyFeeOther                               -- 当日売上明細API月額固定費
        ,D_ApiMonthlyFeeTaxOther                            -- 当日売上明細API月額固定費消費税
        ,D_CreditNoticeMonthlyFeeOther                      -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTaxOther                   -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFeeOther                     -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTaxOther                  -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFeeOther                           -- 当日売上明細月額固定費予備
        ,D_ReserveMonthlyFeeTaxOther                        -- 当日売上明細月額固定費予備消費税
        ,D_AddClaimFeeOther                                 -- 当日手数料明細再発行手数料
        ,D_AddClaimFeeTaxOther                              -- 当日手数料明細再発行手数料消費税
        ,D_DamageInterestAmountOther                        -- 当日手数料明細遅延損害金
        ,MB__AccountsReceivableBalance                      -- 月初未収金残高
        ,M_ChargeCount                                      -- 当月立替件数
        ,M_ChargeAmount                                     -- 当月立替金額
        ,M_CancelCount                                      -- 当月ｷｬﾝｾﾙ件数
        ,M_CancelAmount                                     -- 当月ｷｬﾝｾﾙ立替金額
        ,M_SettlementBackCount                              -- 当月立替金戻し件数
        ,M_SettlementBackAmount                             -- 当月立替金戻し金額
        ,M_TransferCount                                    -- 当月OEM移管件数
        ,M_TransferAmount                                   -- 当月OEM移管金額
        ,M_ReClaimFeeCount                                  -- 当月再発行手数料件数
        ,M_ReClaimFeeAmount                                 -- 当月再発行手数料額
        ,M_DamageCount                                      -- 当月遅延損害金件数
        ,M_DamageAmount                                     -- 当月遅延損害金額
        ,M_ReceiptCount                                     -- 当月入金件数
        ,M_ReceiptAmount                                    -- 当月入金額
        ,M_RepayCount                                       -- 当月返金件数
        ,M_RepayAmount                                      -- 当月返金額
        ,M_BadDebtCount                                     -- 当月貸倒件数
        ,M_BadDebtAmount                                    -- 当月貸倒金額
        ,M_OtherPaymentCount                                -- 当月その他件数
        ,M_OtherPaymentAmount                               -- 当月その他金額
        ,M_AccountsReceivableBalance                        -- 当月時点未収金残高
        ,M_SuspensePaymentsAmount                           -- 消費者未収金明細残高
        ,M_AccountsReceivableBalanceDiff                    -- 当月差額
        ,M_SettlementFee                                    -- 当月決済手数料
        ,M_ClaimFee                                         -- 当月請求手数料
        ,M_ClaimFeeTax                                      -- 当月請求手数料消費税
        ,M_MonthlyFee                                       -- 当月月額固定費
        ,M_MonthlyFeeTax                                    -- 当月月額固定費消費税
        ,M_IncludeMonthlyFee                                -- 当月同梱月額固定費
        ,M_IncludeMonthlyFeeTax                             -- 当月同梱月額固定費消費税
        ,M_ApiMonthlyFee                                    -- 当月API月額固定費
        ,M_ApiMonthlyFeeTax                                 -- 当月API月額固定費消費税
        ,M_CreditNoticeMonthlyFee                           -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTax                        -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFee                          -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTax                       -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFee                                -- 当月月額固定費予備
        ,M_ReserveMonthlyFeeTax                             -- 当月月額固定費予備消費税
        ,M_AddClaimFee                                      -- 当月再発行手数料
        ,M_AddClaimFeeTax                                   -- 当月再発行手数料消費税
        ,M_DamageInterestAmount                             -- 当月遅延損害金
        ,M_CanSettlementFee                                 -- 当月ｷｬﾝｾﾙ決済手数料
        ,M_CanClaimFee                                      -- 当月ｷｬﾝｾﾙ請求手数料
        ,M_CanClaimFeeTax                                   -- 当月ｷｬﾝｾﾙ請求手数料消費税
        ,M_SettlementFeeOther                               -- 当月売上明細決済手数料
        ,M_ClaimFeeOther                                    -- 当月売上明細請求手数料
        ,M_ClaimFeeTaxOther                                 -- 当月売上明細請求手数料消費税
        ,M_MonthlyFeeOther                                  -- 当月売上明細月額固定費
        ,M_MonthlyFeeTaxOther                               -- 当月売上明細月額固定費消費税
        ,M_IncludeMonthlyFeeOther                           -- 当月売上明細同梱月額固定費
        ,M_IncludeMonthlyFeeTaxOther                        -- 当月売上明細同梱月額固定費消費税
        ,M_ApiMonthlyFeeOther                               -- 当月売上明細API月額固定費
        ,M_ApiMonthlyFeeTaxOther                            -- 当月売上明細API月額固定費消費税
        ,M_CreditNoticeMonthlyFeeOther                      -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTaxOther                   -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFeeOther                     -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTaxOther                  -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFeeOther                           -- 当月売上明細月額固定費予備
        ,M_ReserveMonthlyFeeTaxOther                        -- 当月売上明細月額固定費予備消費税
        ,M_AddClaimFeeOther                                 -- 当月手数料明細再発行手数料
        ,M_AddClaimFeeTaxOther                              -- 当月手数料明細再発行手数料消費税
        ,M_DamageInterestAmountOther                        -- 当月手数料明細遅延損害金
        ,RegistDate                                         -- 登録日時
        ,RegistId                                           -- 登録者
        ,UpdateDate                                         -- 更新日時
        ,UpdateId                                           -- 更新者
        ,ValidFlg                                           -- 有効ﾌﾗｸﾞ
        )
    SELECT
         null                                               -- 集計日
        ,null                                               -- 会計月
        ,EnterpriseId                                       -- 加盟店ID
        ,null                                               -- 前日時点未収金残高
        ,null                                               -- 当日立替件数
        ,null                                               -- 当日立替金額
        ,null                                               -- 当日ｷｬﾝｾﾙ件数
        ,null                                               -- 当日ｷｬﾝｾﾙ立替金額
        ,null                                               -- 当日立替金戻し件数
        ,null                                               -- 当日立替金戻し金額
        ,null                                               -- 当日OEM移管件数
        ,null                                               -- 当日OEM移管金額
        ,null                                               -- 当日再発行手数料件数
        ,null                                               -- 当日再発行手数料額
        ,null                                               -- 当日遅延損害金件数
        ,null                                               -- 当日遅延損害金額
        ,null                                               -- 当日入金件数
        ,null                                               -- 当日入金額
        ,null                                               -- 当日返金件数
        ,null                                               -- 当日返金額
        ,null                                               -- 当日貸倒件数
        ,null                                               -- 当日貸倒金額
        ,null                                               -- 当日その他件数
        ,null                                               -- 当日その他金額
        ,null                                               -- 当日時点未収金残高
        ,null                                               -- 当日決済手数料
        ,null                                               -- 当日請求手数料
        ,null                                               -- 当日請求手数料消費税
        ,null                                               -- 当日月額固定費
        ,null                                               -- 当日月額固定費消費税
        ,null                                               -- 当日同梱月額固定費
        ,null                                               -- 当日同梱月額固定費消費税
        ,null                                               -- 当日API月額固定費
        ,null                                               -- 当日API月額固定費消費税
        ,null                                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当日月額固定費予備
        ,null                                               -- 当日月額固定費予備消費税
        ,null                                               -- 当日再発行手数料
        ,null                                               -- 当日再発行手数料消費税
        ,null                                               -- 当日遅延損害金
        ,null                                               -- 当日ｷｬﾝｾﾙ決済手数料
        ,null                                               -- 当日ｷｬﾝｾﾙ請求手数料
        ,null                                               -- 当日ｷｬﾝｾﾙ請求手数料消費税
        ,null                                               -- 当日売上明細決済手数料
        ,null                                               -- 当日売上明細請求手数料
        ,null                                               -- 当日売上明細請求手数料消費税
        ,null                                               -- 当日売上明細月額固定費
        ,null                                               -- 当日売上明細月額固定費消費税
        ,null                                               -- 当日売上明細同梱月額固定費
        ,null                                               -- 当日売上明細同梱月額固定費消費税
        ,null                                               -- 当日売上明細API月額固定費
        ,null                                               -- 当日売上明細API月額固定費消費税
        ,null                                               -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当日売上明細月額固定費予備
        ,null                                               -- 当日売上明細月額固定費予備消費税
        ,null                                               -- 当日手数料明細再発行手数料
        ,null                                               -- 当日手数料明細再発行手数料消費税
        ,null                                               -- 当日手数料明細遅延損害金
        ,null                                               -- 月初未収金残高
        ,SUM(IFNULL(D_ChargeCount, 0))                      -- 当月立替件数
        ,SUM(IFNULL(D_ChargeAmount, 0))                     -- 当月立替金額
        ,SUM(IFNULL(D_CancelCount, 0))                      -- 当月ｷｬﾝｾﾙ件数
        ,SUM(IFNULL(D_CancelAmount, 0))                     -- 当月ｷｬﾝｾﾙ立替金額
        ,SUM(IFNULL(D_SettlementBackCount, 0))              -- 当月立替金戻し件数
        ,SUM(IFNULL(D_SettlementBackAmount, 0))             -- 当月立替金戻し金額
        ,SUM(IFNULL(D_OemTransferCount, 0))                 -- 当月OEM移管件数
        ,SUM(IFNULL(D_OemTransferAmount, 0))                -- 当月OEM移管金額
        ,SUM(IFNULL(D_ReClaimFeeCount, 0))                  -- 当月再発行手数料件数
        ,SUM(IFNULL(D_ReClaimFeeAmount, 0))                 -- 当月再発行手数料額
        ,SUM(IFNULL(D_DamageCount, 0))                      -- 当月遅延損害金件数
        ,SUM(IFNULL(D_DamageAmount, 0))                     -- 当月遅延損害金額
        ,SUM(IFNULL(D_ReceiptCount, 0))                     -- 当月入金件数
        ,SUM(IFNULL(D_ReceiptAmount, 0))                    -- 当月入金額
        ,SUM(IFNULL(D_RepayCount, 0))                       -- 当月返金件数
        ,SUM(IFNULL(D_RepayAmount, 0))                      -- 当月返金額
        ,SUM(IFNULL(D_BadDebtCount, 0))                     -- 当月貸倒件数
        ,SUM(IFNULL(D_BadDebtAmount, 0))                    -- 当月貸倒金額
        ,SUM(IFNULL(D_OtherPaymentCount, 0))                -- 当月その他件数
        ,SUM(IFNULL(D_OtherPaymentAmount, 0))               -- 当月その他金額
        ,null                                               -- 当月時点未収金残高
        ,null                                               -- 消費者未収金明細残高
        ,null                                               -- 当月差額
        ,SUM(IFNULL(D_SettlementFee, 0))                    -- 当月決済手数料
        ,SUM(IFNULL(D_ClaimFee, 0))                         -- 当月請求手数料
        ,SUM(IFNULL(D_ClaimFeeTax, 0))                      -- 当月請求手数料消費税
        ,SUM(IFNULL(D_MonthlyFee, 0))                       -- 当月月額固定費
        ,SUM(IFNULL(D_MonthlyFeeTax, 0))                    -- 当月月額固定費消費税
        ,SUM(IFNULL(D_IncludeMonthlyFee, 0))                -- 当月同梱月額固定費
        ,SUM(IFNULL(D_IncludeMonthlyFeeTax, 0))             -- 当月同梱月額固定費消費税
        ,SUM(IFNULL(D_ApiMonthlyFee, 0))                    -- 当月API月額固定費
        ,SUM(IFNULL(D_ApiMonthlyFeeTax, 0))                 -- 当月API月額固定費消費税
        ,SUM(IFNULL(D_CreditNoticeMonthlyFee, 0))           -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM(IFNULL(D_CreditNoticeMonthlyFeeTax, 0))        -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM(IFNULL(D_NCreditNoticeMonthlyFee, 0))          -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM(IFNULL(D_NCreditNoticeMonthlyFeeTax, 0))       -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM(IFNULL(D_ReserveMonthlyFee, 0))                -- 当月月額固定費予備
        ,SUM(IFNULL(D_ReserveMonthlyFeeTax, 0))             -- 当月月額固定費予備消費税
        ,SUM(IFNULL(D_AddClaimFee, 0))                      -- 当月再発行手数料
        ,SUM(IFNULL(D_AddClaimFeeTax, 0))                   -- 当月再発行手数料消費税
        ,SUM(IFNULL(D_DamageInterestAmount, 0))             -- 当月遅延損害金
        ,SUM(IFNULL(D_CanSettlementFee, 0))                 -- 当月ｷｬﾝｾﾙ決済手数料
        ,SUM(IFNULL(D_CanClaimFee, 0))                      -- 当月ｷｬﾝｾﾙ請求手数料
        ,SUM(IFNULL(D_CanClaimFeeTax, 0))                   -- 当月ｷｬﾝｾﾙ請求手数料消費税
        ,null                                               -- 当月売上明細決済手数料
        ,null                                               -- 当月売上明細請求手数料
        ,null                                               -- 当月売上明細請求手数料消費税
        ,null                                               -- 当月売上明細月額固定費
        ,null                                               -- 当月売上明細月額固定費消費税
        ,null                                               -- 当月売上明細同梱月額固定費
        ,null                                               -- 当月売上明細同梱月額固定費消費税
        ,null                                               -- 当月売上明細API月額固定費
        ,null                                               -- 当月売上明細API月額固定費消費税
        ,null                                               -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当月売上明細月額固定費予備
        ,null                                               -- 当月売上明細月額固定費予備消費税
        ,null                                               -- 当月手数料明細再発行手数料
        ,null                                               -- 当月手数料明細再発行手数料消費税
        ,null                                               -- 当月手数料明細遅延損害金
        ,NOW()                                              -- 登録日時
        ,pi_user_id                                         -- 登録者
        ,NOW()                                              -- 更新日時
        ,pi_user_id                                         -- 更新者
        ,1                                                  -- 有効ﾌﾗｸﾞ
    FROM  AT_Daily2StatisticsTable
    WHERE AccountDate    = v_AccountingMonth
    GROUP BY AccountDate                                    -- 会計月
            ,EnterpriseId;                                  -- 加盟店ID

    -- ------------------------------
    -- 2.4.加盟店別に業務日付 > 直営日次統計表.集計日、且つ、MAX（直営日次統計表.集計日）【前日】
    --     のﾃﾞｰﾀを出力し日次用の直営日次統計表ﾜｰｸを出力する
    -- ------------------------------
    INSERT INTO AW_DailyStatisticsTable (
         ProcessingDate                                     -- 集計日
        ,AccountDate                                        -- 会計月
        ,EnterpriseId                                       -- 加盟店ID
        ,DB__AccountsReceivableBalance                      -- 前日時点未収金残高
        ,D_ChargeCount                                      -- 当日立替件数
        ,D_ChargeAmount                                     -- 当日立替金額
        ,D_CancelCount                                      -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                                     -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount                              -- 当日立替金戻し件数
        ,D_SettlementBackAmount                             -- 当日立替金戻し金額
        ,D_OemTransferCount                                 -- 当日OEM移管件数
        ,D_OemTransferAmount                                -- 当日OEM移管金額
        ,D_ReClaimFeeCount                                  -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                                 -- 当日再発行手数料額
        ,D_DamageCount                                      -- 当日遅延損害金件数
        ,D_DamageAmount                                     -- 当日遅延損害金額
        ,D_ReceiptCount                                     -- 当日入金件数
        ,D_ReceiptAmount                                    -- 当日入金額
        ,D_RepayCount                                       -- 当日返金件数
        ,D_RepayAmount                                      -- 当日返金額
        ,D_BadDebtCount                                     -- 当日貸倒件数
        ,D_BadDebtAmount                                    -- 当日貸倒金額
        ,D_OtherPaymentCount                                -- 当日その他件数
        ,D_OtherPaymentAmount                               -- 当日その他金額
        ,D_AccountsReceivableBalance                        -- 当日時点未収金残高
        ,D_SettlementFee                                    -- 当日決済手数料
        ,D_ClaimFee                                         -- 当日請求手数料
        ,D_ClaimFeeTax                                      -- 当日請求手数料消費税
        ,D_MonthlyFee                                       -- 当日月額固定費
        ,D_MonthlyFeeTax                                    -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                                -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax                             -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                                    -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                                 -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee                           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax                        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee                          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax                       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                                -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax                             -- 当日月額固定費予備消費税
        ,D_AddClaimFee                                      -- 当日再発行手数料
        ,D_AddClaimFeeTax                                   -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                             -- 当日遅延損害金
        ,D_CanSettlementFee                                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        ,D_SettlementFeeOther                               -- 当日売上明細決済手数料
        ,D_ClaimFeeOther                                    -- 当日売上明細請求手数料
        ,D_ClaimFeeTaxOther                                 -- 当日売上明細請求手数料消費税
        ,D_MonthlyFeeOther                                  -- 当日売上明細月額固定費
        ,D_MonthlyFeeTaxOther                               -- 当日売上明細月額固定費消費税
        ,D_IncludeMonthlyFeeOther                           -- 当日売上明細同梱月額固定費
        ,D_IncludeMonthlyFeeTaxOther                        -- 当日売上明細同梱月額固定費消費税
        ,D_ApiMonthlyFeeOther                               -- 当日売上明細API月額固定費
        ,D_ApiMonthlyFeeTaxOther                            -- 当日売上明細API月額固定費消費税
        ,D_CreditNoticeMonthlyFeeOther                      -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTaxOther                   -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFeeOther                     -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTaxOther                  -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFeeOther                           -- 当日売上明細月額固定費予備
        ,D_ReserveMonthlyFeeTaxOther                        -- 当日売上明細月額固定費予備消費税
        ,D_AddClaimFeeOther                                 -- 当日手数料明細再発行手数料
        ,D_AddClaimFeeTaxOther                              -- 当日手数料明細再発行手数料消費税
        ,D_DamageInterestAmountOther                        -- 当日手数料明細遅延損害金
        ,MB__AccountsReceivableBalance                      -- 月初未収金残高
        ,M_ChargeCount                                      -- 当月立替件数
        ,M_ChargeAmount                                     -- 当月立替金額
        ,M_CancelCount                                      -- 当月ｷｬﾝｾﾙ件数
        ,M_CancelAmount                                     -- 当月ｷｬﾝｾﾙ立替金額
        ,M_SettlementBackCount                              -- 当月立替金戻し件数
        ,M_SettlementBackAmount                             -- 当月立替金戻し金額
        ,M_TransferCount                                    -- 当月OEM移管件数
        ,M_TransferAmount                                   -- 当月OEM移管金額
        ,M_ReClaimFeeCount                                  -- 当月再発行手数料件数
        ,M_ReClaimFeeAmount                                 -- 当月再発行手数料額
        ,M_DamageCount                                      -- 当月遅延損害金件数
        ,M_DamageAmount                                     -- 当月遅延損害金額
        ,M_ReceiptCount                                     -- 当月入金件数
        ,M_ReceiptAmount                                    -- 当月入金額
        ,M_RepayCount                                       -- 当月返金件数
        ,M_RepayAmount                                      -- 当月返金額
        ,M_BadDebtCount                                     -- 当月貸倒件数
        ,M_BadDebtAmount                                    -- 当月貸倒金額
        ,M_OtherPaymentCount                                -- 当月その他件数
        ,M_OtherPaymentAmount                               -- 当月その他金額
        ,M_AccountsReceivableBalance                        -- 当月時点未収金残高
        ,M_SuspensePaymentsAmount                           -- 消費者未収金明細残高
        ,M_AccountsReceivableBalanceDiff                    -- 当月差額
        ,M_SettlementFee                                    -- 当月決済手数料
        ,M_ClaimFee                                         -- 当月請求手数料
        ,M_ClaimFeeTax                                      -- 当月請求手数料消費税
        ,M_MonthlyFee                                       -- 当月月額固定費
        ,M_MonthlyFeeTax                                    -- 当月月額固定費消費税
        ,M_IncludeMonthlyFee                                -- 当月同梱月額固定費
        ,M_IncludeMonthlyFeeTax                             -- 当月同梱月額固定費消費税
        ,M_ApiMonthlyFee                                    -- 当月API月額固定費
        ,M_ApiMonthlyFeeTax                                 -- 当月API月額固定費消費税
        ,M_CreditNoticeMonthlyFee                           -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTax                        -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFee                          -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTax                       -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFee                                -- 当月月額固定費予備
        ,M_ReserveMonthlyFeeTax                             -- 当月月額固定費予備消費税
        ,M_AddClaimFee                                      -- 当月再発行手数料
        ,M_AddClaimFeeTax                                   -- 当月再発行手数料消費税
        ,M_DamageInterestAmount                             -- 当月遅延損害金
        ,M_CanSettlementFee                                 -- 当月ｷｬﾝｾﾙ決済手数料
        ,M_CanClaimFee                                      -- 当月ｷｬﾝｾﾙ請求手数料
        ,M_CanClaimFeeTax                                   -- 当月ｷｬﾝｾﾙ請求手数料消費税
        ,M_SettlementFeeOther                               -- 当月売上明細決済手数料
        ,M_ClaimFeeOther                                    -- 当月売上明細請求手数料
        ,M_ClaimFeeTaxOther                                 -- 当月売上明細請求手数料消費税
        ,M_MonthlyFeeOther                                  -- 当月売上明細月額固定費
        ,M_MonthlyFeeTaxOther                               -- 当月売上明細月額固定費消費税
        ,M_IncludeMonthlyFeeOther                           -- 当月売上明細同梱月額固定費
        ,M_IncludeMonthlyFeeTaxOther                        -- 当月売上明細同梱月額固定費消費税
        ,M_ApiMonthlyFeeOther                               -- 当月売上明細API月額固定費
        ,M_ApiMonthlyFeeTaxOther                            -- 当月売上明細API月額固定費消費税
        ,M_CreditNoticeMonthlyFeeOther                      -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTaxOther                   -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFeeOther                     -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTaxOther                  -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFeeOther                           -- 当月売上明細月額固定費予備
        ,M_ReserveMonthlyFeeTaxOther                        -- 当月売上明細月額固定費予備消費税
        ,M_AddClaimFeeOther                                 -- 当月手数料明細再発行手数料
        ,M_AddClaimFeeTaxOther                              -- 当月手数料明細再発行手数料消費税
        ,M_DamageInterestAmountOther                        -- 当月手数料明細遅延損害金
        ,RegistDate                                         -- 登録日時
        ,RegistId                                           -- 登録者
        ,UpdateDate                                         -- 更新日時
        ,UpdateId                                           -- 更新者
        ,ValidFlg                                           -- 有効ﾌﾗｸﾞ
        )
    SELECT
         null                                               -- 集計日
        ,null                                               -- 会計月
        ,dst.EnterpriseId                                   -- 加盟店ID
        ,dst.D_AccountsReceivableBalance                    -- 前日時点未収金残高
        ,null                                               -- 当日立替件数
        ,null                                               -- 当日立替金額
        ,null                                               -- 当日ｷｬﾝｾﾙ件数
        ,null                                               -- 当日ｷｬﾝｾﾙ立替金額
        ,null                                               -- 当日立替金戻し件数
        ,null                                               -- 当日立替金戻し金額
        ,null                                               -- 当日OEM移管件数
        ,null                                               -- 当日OEM移管金額
        ,null                                               -- 当日再発行手数料件数
        ,null                                               -- 当日再発行手数料額
        ,null                                               -- 当日遅延損害金件数
        ,null                                               -- 当日遅延損害金額
        ,null                                               -- 当日入金件数
        ,null                                               -- 当日入金額
        ,null                                               -- 当日返金件数
        ,null                                               -- 当日返金額
        ,null                                               -- 当日貸倒件数
        ,null                                               -- 当日貸倒金額
        ,null                                               -- 当日その他件数
        ,null                                               -- 当日その他金額
        ,null                                               -- 当日時点未収金残高
        ,null                                               -- 当日決済手数料
        ,null                                               -- 当日請求手数料
        ,null                                               -- 当日請求手数料消費税
        ,null                                               -- 当日月額固定費
        ,null                                               -- 当日月額固定費消費税
        ,null                                               -- 当日同梱月額固定費
        ,null                                               -- 当日同梱月額固定費消費税
        ,null                                               -- 当日API月額固定費
        ,null                                               -- 当日API月額固定費消費税
        ,null                                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当日月額固定費予備
        ,null                                               -- 当日月額固定費予備消費税
        ,null                                               -- 当日再発行手数料
        ,null                                               -- 当日再発行手数料消費税
        ,null                                               -- 当日遅延損害金
        ,null                                               -- 当日ｷｬﾝｾﾙ決済手数料
        ,null                                               -- 当日ｷｬﾝｾﾙ請求手数料
        ,null                                               -- 当日ｷｬﾝｾﾙ請求手数料消費税
        ,null                                               -- 当日売上明細決済手数料
        ,null                                               -- 当日売上明細請求手数料
        ,null                                               -- 当日売上明細請求手数料消費税
        ,null                                               -- 当日売上明細月額固定費
        ,null                                               -- 当日売上明細月額固定費消費税
        ,null                                               -- 当日売上明細同梱月額固定費
        ,null                                               -- 当日売上明細同梱月額固定費消費税
        ,null                                               -- 当日売上明細API月額固定費
        ,null                                               -- 当日売上明細API月額固定費消費税
        ,null                                               -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当日売上明細月額固定費予備
        ,null                                               -- 当日売上明細月額固定費予備消費税
        ,null                                               -- 当日手数料明細再発行手数料
        ,null                                               -- 当日手数料明細再発行手数料消費税
        ,null                                               -- 当日手数料明細遅延損害金
        ,null                                               -- 月初未収金残高
        ,null                                               -- 当月立替件数
        ,null                                               -- 当月立替金額
        ,null                                               -- 当月ｷｬﾝｾﾙ件数
        ,null                                               -- 当月ｷｬﾝｾﾙ立替金額
        ,null                                               -- 当月立替金戻し件数
        ,null                                               -- 当月立替金戻し金額
        ,null                                               -- 当月OEM移管件数
        ,null                                               -- 当月OEM移管金額
        ,null                                               -- 当月再発行手数料件数
        ,null                                               -- 当月再発行手数料額
        ,null                                               -- 当月遅延損害金件数
        ,null                                               -- 当月遅延損害金額
        ,null                                               -- 当月入金件数
        ,null                                               -- 当月入金額
        ,null                                               -- 当月返金件数
        ,null                                               -- 当月返金額
        ,null                                               -- 当月貸倒件数
        ,null                                               -- 当月貸倒金額
        ,null                                               -- 当月その他件数
        ,null                                               -- 当月その他金額
        ,null                                               -- 当月時点未収金残高
        ,null                                               -- 消費者未収金明細残高
        ,null                                               -- 当月差額
        ,null                                               -- 当月決済手数料
        ,null                                               -- 当月請求手数料
        ,null                                               -- 当月請求手数料消費税
        ,null                                               -- 当月月額固定費
        ,null                                               -- 当月月額固定費消費税
        ,null                                               -- 当月同梱月額固定費
        ,null                                               -- 当月同梱月額固定費消費税
        ,null                                               -- 当月API月額固定費
        ,null                                               -- 当月API月額固定費消費税
        ,null                                               -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当月月額固定費予備
        ,null                                               -- 当月月額固定費予備消費税
        ,null                                               -- 当月再発行手数料
        ,null                                               -- 当月再発行手数料消費税
        ,null                                               -- 当月遅延損害金
        ,null                                               -- 当月ｷｬﾝｾﾙ決済手数料
        ,null                                               -- 当月ｷｬﾝｾﾙ請求手数料
        ,null                                               -- 当月ｷｬﾝｾﾙ請求手数料消費税
        ,null                                               -- 当月売上明細決済手数料
        ,null                                               -- 当月売上明細請求手数料
        ,null                                               -- 当月売上明細請求手数料消費税
        ,null                                               -- 当月売上明細月額固定費
        ,null                                               -- 当月売上明細月額固定費消費税
        ,null                                               -- 当月売上明細同梱月額固定費
        ,null                                               -- 当月売上明細同梱月額固定費消費税
        ,null                                               -- 当月売上明細API月額固定費
        ,null                                               -- 当月売上明細API月額固定費消費税
        ,null                                               -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当月売上明細月額固定費予備
        ,null                                               -- 当月売上明細月額固定費予備消費税
        ,null                                               -- 当月手数料明細再発行手数料
        ,null                                               -- 当月手数料明細再発行手数料消費税
        ,null                                               -- 当月手数料明細遅延損害金
        ,NOW()                                              -- 登録日時
        ,pi_user_id                                         -- 登録者
        ,NOW()                                              -- 更新日時
        ,pi_user_id                                         -- 更新者
        ,1                                                  -- 有効ﾌﾗｸﾞ
    FROM  AT_DailyStatisticsTable dst
    INNER JOIN ( SELECT EnterpriseId, MAX( ProcessingDate ) ProcessingDate FROM AT_DailyStatisticsTable
                 WHERE v_BusinessDate      > ProcessingDate
                 AND   v_AccountingMonth   = AccountDate
                 AND   DailyMonthlyFlg     = 0
                 GROUP BY EnterpriseId ) dstw
    ON    dst.EnterpriseId = dstw.EnterpriseId
    AND   dst.ProcessingDate = dstw.ProcessingDate
    AND   DailyMonthlyFlg = 0
    AND   dst.AccountDate = v_AccountingMonth;

    -- ------------------------------
    -- 2.5.加盟店別に会計月の前月 > 直営日次統計表.会計月、且つ、MAX（直営日次統計表.集計日）【前月末】の
    --     ﾃﾞｰﾀを出力し日次用の直営日次統計表ﾜｰｸを出力する
    -- ------------------------------
    INSERT INTO AW_DailyStatisticsTable (
         ProcessingDate                                     -- 集計日
        ,AccountDate                                        -- 会計月
        ,EnterpriseId                                       -- 加盟店ID
        ,DB__AccountsReceivableBalance                      -- 前日時点未収金残高
        ,D_ChargeCount                                      -- 当日立替件数
        ,D_ChargeAmount                                     -- 当日立替金額
        ,D_CancelCount                                      -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                                     -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount                              -- 当日立替金戻し件数
        ,D_SettlementBackAmount                             -- 当日立替金戻し金額
        ,D_OemTransferCount                                 -- 当日OEM移管件数
        ,D_OemTransferAmount                                -- 当日OEM移管金額
        ,D_ReClaimFeeCount                                  -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                                 -- 当日再発行手数料額
        ,D_DamageCount                                      -- 当日遅延損害金件数
        ,D_DamageAmount                                     -- 当日遅延損害金額
        ,D_ReceiptCount                                     -- 当日入金件数
        ,D_ReceiptAmount                                    -- 当日入金額
        ,D_RepayCount                                       -- 当日返金件数
        ,D_RepayAmount                                      -- 当日返金額
        ,D_BadDebtCount                                     -- 当日貸倒件数
        ,D_BadDebtAmount                                    -- 当日貸倒金額
        ,D_OtherPaymentCount                                -- 当日その他件数
        ,D_OtherPaymentAmount                               -- 当日その他金額
        ,D_AccountsReceivableBalance                        -- 当日時点未収金残高
        ,D_SettlementFee                                    -- 当日決済手数料
        ,D_ClaimFee                                         -- 当日請求手数料
        ,D_ClaimFeeTax                                      -- 当日請求手数料消費税
        ,D_MonthlyFee                                       -- 当日月額固定費
        ,D_MonthlyFeeTax                                    -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                                -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax                             -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                                    -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                                 -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee                           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax                        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee                          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax                       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                                -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax                             -- 当日月額固定費予備消費税
        ,D_AddClaimFee                                      -- 当日再発行手数料
        ,D_AddClaimFeeTax                                   -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                             -- 当日遅延損害金
        ,D_CanSettlementFee                                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        ,D_SettlementFeeOther                               -- 当日売上明細決済手数料
        ,D_ClaimFeeOther                                    -- 当日売上明細請求手数料
        ,D_ClaimFeeTaxOther                                 -- 当日売上明細請求手数料消費税
        ,D_MonthlyFeeOther                                  -- 当日売上明細月額固定費
        ,D_MonthlyFeeTaxOther                               -- 当日売上明細月額固定費消費税
        ,D_IncludeMonthlyFeeOther                           -- 当日売上明細同梱月額固定費
        ,D_IncludeMonthlyFeeTaxOther                        -- 当日売上明細同梱月額固定費消費税
        ,D_ApiMonthlyFeeOther                               -- 当日売上明細API月額固定費
        ,D_ApiMonthlyFeeTaxOther                            -- 当日売上明細API月額固定費消費税
        ,D_CreditNoticeMonthlyFeeOther                      -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTaxOther                   -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFeeOther                     -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTaxOther                  -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFeeOther                           -- 当日売上明細月額固定費予備
        ,D_ReserveMonthlyFeeTaxOther                        -- 当日売上明細月額固定費予備消費税
        ,D_AddClaimFeeOther                                 -- 当日手数料明細再発行手数料
        ,D_AddClaimFeeTaxOther                              -- 当日手数料明細再発行手数料消費税
        ,D_DamageInterestAmountOther                        -- 当日手数料明細遅延損害金
        ,MB__AccountsReceivableBalance                      -- 月初未収金残高
        ,M_ChargeCount                                      -- 当月立替件数
        ,M_ChargeAmount                                     -- 当月立替金額
        ,M_CancelCount                                      -- 当月ｷｬﾝｾﾙ件数
        ,M_CancelAmount                                     -- 当月ｷｬﾝｾﾙ立替金額
        ,M_SettlementBackCount                              -- 当月立替金戻し件数
        ,M_SettlementBackAmount                             -- 当月立替金戻し金額
        ,M_TransferCount                                    -- 当月OEM移管件数
        ,M_TransferAmount                                   -- 当月OEM移管金額
        ,M_ReClaimFeeCount                                  -- 当月再発行手数料件数
        ,M_ReClaimFeeAmount                                 -- 当月再発行手数料額
        ,M_DamageCount                                      -- 当月遅延損害金件数
        ,M_DamageAmount                                     -- 当月遅延損害金額
        ,M_ReceiptCount                                     -- 当月入金件数
        ,M_ReceiptAmount                                    -- 当月入金額
        ,M_RepayCount                                       -- 当月返金件数
        ,M_RepayAmount                                      -- 当月返金額
        ,M_BadDebtCount                                     -- 当月貸倒件数
        ,M_BadDebtAmount                                    -- 当月貸倒金額
        ,M_OtherPaymentCount                                -- 当月その他件数
        ,M_OtherPaymentAmount                               -- 当月その他金額
        ,M_AccountsReceivableBalance                        -- 当月時点未収金残高
        ,M_SuspensePaymentsAmount                           -- 消費者未収金明細残高
        ,M_AccountsReceivableBalanceDiff                    -- 当月差額
        ,M_SettlementFee                                    -- 当月決済手数料
        ,M_ClaimFee                                         -- 当月請求手数料
        ,M_ClaimFeeTax                                      -- 当月請求手数料消費税
        ,M_MonthlyFee                                       -- 当月月額固定費
        ,M_MonthlyFeeTax                                    -- 当月月額固定費消費税
        ,M_IncludeMonthlyFee                                -- 当月同梱月額固定費
        ,M_IncludeMonthlyFeeTax                             -- 当月同梱月額固定費消費税
        ,M_ApiMonthlyFee                                    -- 当月API月額固定費
        ,M_ApiMonthlyFeeTax                                 -- 当月API月額固定費消費税
        ,M_CreditNoticeMonthlyFee                           -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTax                        -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFee                          -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTax                       -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFee                                -- 当月月額固定費予備
        ,M_ReserveMonthlyFeeTax                             -- 当月月額固定費予備消費税
        ,M_AddClaimFee                                      -- 当月再発行手数料
        ,M_AddClaimFeeTax                                   -- 当月再発行手数料消費税
        ,M_DamageInterestAmount                             -- 当月遅延損害金
        ,M_CanSettlementFee                                 -- 当月ｷｬﾝｾﾙ決済手数料
        ,M_CanClaimFee                                      -- 当月ｷｬﾝｾﾙ請求手数料
        ,M_CanClaimFeeTax                                   -- 当月ｷｬﾝｾﾙ請求手数料消費税
        ,M_SettlementFeeOther                               -- 当月売上明細決済手数料
        ,M_ClaimFeeOther                                    -- 当月売上明細請求手数料
        ,M_ClaimFeeTaxOther                                 -- 当月売上明細請求手数料消費税
        ,M_MonthlyFeeOther                                  -- 当月売上明細月額固定費
        ,M_MonthlyFeeTaxOther                               -- 当月売上明細月額固定費消費税
        ,M_IncludeMonthlyFeeOther                           -- 当月売上明細同梱月額固定費
        ,M_IncludeMonthlyFeeTaxOther                        -- 当月売上明細同梱月額固定費消費税
        ,M_ApiMonthlyFeeOther                               -- 当月売上明細API月額固定費
        ,M_ApiMonthlyFeeTaxOther                            -- 当月売上明細API月額固定費消費税
        ,M_CreditNoticeMonthlyFeeOther                      -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTaxOther                   -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFeeOther                     -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTaxOther                  -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFeeOther                           -- 当月売上明細月額固定費予備
        ,M_ReserveMonthlyFeeTaxOther                        -- 当月売上明細月額固定費予備消費税
        ,M_AddClaimFeeOther                                 -- 当月手数料明細再発行手数料
        ,M_AddClaimFeeTaxOther                              -- 当月手数料明細再発行手数料消費税
        ,M_DamageInterestAmountOther                        -- 当月手数料明細遅延損害金
        ,RegistDate                                         -- 登録日時
        ,RegistId                                           -- 登録者
        ,UpdateDate                                         -- 更新日時
        ,UpdateId                                           -- 更新者
        ,ValidFlg                                           -- 有効ﾌﾗｸﾞ
        )
    SELECT
         null                                               -- 集計日
        ,null                                               -- 会計月
        ,dst.EnterpriseId                                   -- 加盟店ID
        ,null                                               -- 前日時点未収金残高
        ,null                                               -- 当日立替件数
        ,null                                               -- 当日立替金額
        ,null                                               -- 当日ｷｬﾝｾﾙ件数
        ,null                                               -- 当日ｷｬﾝｾﾙ立替金額
        ,null                                               -- 当日立替金戻し件数
        ,null                                               -- 当日立替金戻し金額
        ,null                                               -- 当日OEM移管件数
        ,null                                               -- 当日OEM移管金額
        ,null                                               -- 当日再発行手数料件数
        ,null                                               -- 当日再発行手数料額
        ,null                                               -- 当日遅延損害金件数
        ,null                                               -- 当日遅延損害金額
        ,null                                               -- 当日入金件数
        ,null                                               -- 当日入金額
        ,null                                               -- 当日返金件数
        ,null                                               -- 当日返金額
        ,null                                               -- 当日貸倒件数
        ,null                                               -- 当日貸倒金額
        ,null                                               -- 当日その他件数
        ,null                                               -- 当日その他金額
        ,null                                               -- 当日時点未収金残高
        ,null                                               -- 当日決済手数料
        ,null                                               -- 当日請求手数料
        ,null                                               -- 当日請求手数料消費税
        ,null                                               -- 当日月額固定費
        ,null                                               -- 当日月額固定費消費税
        ,null                                               -- 当日同梱月額固定費
        ,null                                               -- 当日同梱月額固定費消費税
        ,null                                               -- 当日API月額固定費
        ,null                                               -- 当日API月額固定費消費税
        ,null                                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当日月額固定費予備
        ,null                                               -- 当日月額固定費予備消費税
        ,null                                               -- 当日再発行手数料
        ,null                                               -- 当日再発行手数料消費税
        ,null                                               -- 当日遅延損害金
        ,null                                               -- 当日ｷｬﾝｾﾙ決済手数料
        ,null                                               -- 当日ｷｬﾝｾﾙ請求手数料
        ,null                                               -- 当日ｷｬﾝｾﾙ請求手数料消費税
        ,null                                               -- 当日売上明細決済手数料
        ,null                                               -- 当日売上明細請求手数料
        ,null                                               -- 当日売上明細請求手数料消費税
        ,null                                               -- 当日売上明細月額固定費
        ,null                                               -- 当日売上明細月額固定費消費税
        ,null                                               -- 当日売上明細同梱月額固定費
        ,null                                               -- 当日売上明細同梱月額固定費消費税
        ,null                                               -- 当日売上明細API月額固定費
        ,null                                               -- 当日売上明細API月額固定費消費税
        ,null                                               -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当日売上明細月額固定費予備
        ,null                                               -- 当日売上明細月額固定費予備消費税
        ,null                                               -- 当日手数料明細再発行手数料
        ,null                                               -- 当日手数料明細再発行手数料消費税
        ,null                                               -- 当日手数料明細遅延損害金
        ,dst.M_AccountsReceivableBalance                    -- 月初未収金残高
        ,null                                               -- 当月立替件数
        ,null                                               -- 当月立替金額
        ,null                                               -- 当月ｷｬﾝｾﾙ件数
        ,null                                               -- 当月ｷｬﾝｾﾙ立替金額
        ,null                                               -- 当月立替金戻し件数
        ,null                                               -- 当月立替金戻し金額
        ,null                                               -- 当月OEM移管件数
        ,null                                               -- 当月OEM移管金額
        ,null                                               -- 当月再発行手数料件数
        ,null                                               -- 当月再発行手数料額
        ,null                                               -- 当月遅延損害金件数
        ,null                                               -- 当月遅延損害金額
        ,null                                               -- 当月入金件数
        ,null                                               -- 当月入金額
        ,null                                               -- 当月返金件数
        ,null                                               -- 当月返金額
        ,null                                               -- 当月貸倒件数
        ,null                                               -- 当月貸倒金額
        ,null                                               -- 当月その他件数
        ,null                                               -- 当月その他金額
        ,null                                               -- 当月時点未収金残高
        ,null                                               -- 消費者未収金明細残高
        ,null                                               -- 当月差額
        ,null                                               -- 当月決済手数料
        ,null                                               -- 当月請求手数料
        ,null                                               -- 当月請求手数料消費税
        ,null                                               -- 当月月額固定費
        ,null                                               -- 当月月額固定費消費税
        ,null                                               -- 当月同梱月額固定費
        ,null                                               -- 当月同梱月額固定費消費税
        ,null                                               -- 当月API月額固定費
        ,null                                               -- 当月API月額固定費消費税
        ,null                                               -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当月月額固定費予備
        ,null                                               -- 当月月額固定費予備消費税
        ,null                                               -- 当月再発行手数料
        ,null                                               -- 当月再発行手数料消費税
        ,null                                               -- 当月遅延損害金
        ,null                                               -- 当月ｷｬﾝｾﾙ決済手数料
        ,null                                               -- 当月ｷｬﾝｾﾙ請求手数料
        ,null                                               -- 当月ｷｬﾝｾﾙ請求手数料消費税
        ,null                                               -- 当月売上明細決済手数料
        ,null                                               -- 当月売上明細請求手数料
        ,null                                               -- 当月売上明細請求手数料消費税
        ,null                                               -- 当月売上明細月額固定費
        ,null                                               -- 当月売上明細月額固定費消費税
        ,null                                               -- 当月売上明細同梱月額固定費
        ,null                                               -- 当月売上明細同梱月額固定費消費税
        ,null                                               -- 当月売上明細API月額固定費
        ,null                                               -- 当月売上明細API月額固定費消費税
        ,null                                               -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                               -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                               -- 当月売上明細月額固定費予備
        ,null                                               -- 当月売上明細月額固定費予備消費税
        ,null                                               -- 当月手数料明細再発行手数料
        ,null                                               -- 当月手数料明細再発行手数料消費税
        ,null                                               -- 当月手数料明細遅延損害金
        ,NOW()                                              -- 登録日時
        ,pi_user_id                                         -- 登録者
        ,NOW()                                              -- 更新日時
        ,pi_user_id                                         -- 更新者
        ,1                                                  -- 有効ﾌﾗｸﾞ
    FROM  AT_DailyStatisticsTable dst
    INNER JOIN ( SELECT EnterpriseId, MAX( ProcessingDate ) ProcessingDate FROM AT_DailyStatisticsTable
                 WHERE DATE_SUB(v_AccountingMonth, INTERVAL 1 MONTH) = AccountDate
                 AND   DailyMonthlyFlg     = 1
                 GROUP BY EnterpriseId ) dstw
    ON    dst.EnterpriseId = dstw.EnterpriseId
    AND   dst.ProcessingDate = dstw.ProcessingDate
    AND   dst.DailyMonthlyFlg = 1
    AND   dst.AccountDate = DATE_SUB(v_AccountingMonth, INTERVAL 1 MONTH);

    -- ------------------------------
    -- 2.6.直営日次統計表ﾜｰｸのﾃﾞｰﾀを加盟店別に集計出力し日次用の直営日次統計表を出力する
    -- ------------------------------
    INSERT INTO AT_DailyStatisticsTable (
         DailyMonthlyFlg                                    -- 日次･月次区分
        ,ProcessingDate                                     -- 集計日
        ,AccountDate                                        -- 会計月
        ,EnterpriseId                                       -- 加盟店ID
        ,EnterpriseNameKj                                   -- 加盟店名
        ,DB__AccountsReceivableBalance                      -- 前日時点未収金残高
        ,D_ChargeCount                                      -- 当日立替件数
        ,D_ChargeAmount                                     -- 当日立替金額
        ,D_CancelCount                                      -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                                     -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount                              -- 当日立替金戻し件数
        ,D_SettlementBackAmount                             -- 当日立替金戻し金額
        ,D_OemTransferCount                                 -- 当日OEM移管件数
        ,D_OemTransferAmount                                -- 当日OEM移管金額
        ,D_ReClaimFeeCount                                  -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                                 -- 当日再発行手数料額
        ,D_DamageCount                                      -- 当日遅延損害金件数
        ,D_DamageAmount                                     -- 当日遅延損害金額
        ,D_ReceiptCount                                     -- 当日入金件数
        ,D_ReceiptAmount                                    -- 当日入金額
        ,D_RepayCount                                       -- 当日返金件数
        ,D_RepayAmount                                      -- 当日返金額
        ,D_BadDebtCount                                     -- 当日貸倒件数
        ,D_BadDebtAmount                                    -- 当日貸倒金額
        ,D_OtherPaymentCount                                -- 当日その他件数
        ,D_OtherPaymentAmount                               -- 当日その他金額
        ,D_AccountsReceivableBalance                        -- 当日時点未収金残高
        ,D_SettlementFee                                    -- 当日決済手数料
        ,D_ClaimFee                                         -- 当日請求手数料
        ,D_ClaimFeeTax                                      -- 当日請求手数料消費税
        ,D_MonthlyFee                                       -- 当日月額固定費
        ,D_MonthlyFeeTax                                    -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                                -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax                             -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                                    -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                                 -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee                           -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax                        -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee                          -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax                       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                                -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax                             -- 当日月額固定費予備消費税
        ,D_AddClaimFee                                      -- 当日再発行手数料
        ,D_AddClaimFeeTax                                   -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                             -- 当日遅延損害金
        ,D_CanSettlementFee                                 -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                                      -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                                   -- 当日ｷｬﾝｾﾙ請求手数料消費税
        ,D_SettlementFeeTotal                               -- 当日合計決済手数料
        ,D_ClaimFeeTotal                                    -- 当日合計請求手数料
        ,D_ClaimFeeTaxTotal                                 -- 当日合計請求手数料消費税
        ,D_MonthlyFeeTotal                                  -- 当日合計月額固定費
        ,D_MonthlyFeeTaxTotal                               -- 当日合計月額固定費消費税
        ,D_IncludeMonthlyFeeTotal                           -- 当日合計同梱月額固定費
        ,D_IncludeMonthlyFeeTaxTotal                        -- 当日合計同梱月額固定費消費税
        ,D_ApiMonthlyFeeTotal                               -- 当日合計API月額固定費
        ,D_ApiMonthlyFeeTaxTotal                            -- 当日合計API月額固定費消費税
        ,D_CreditNoticeMonthlyFeeTotal                      -- 当日合計与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTaxTotal                   -- 当日合計与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFeeTotal                     -- 当日合計次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTaxTotal                  -- 当日合計次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFeeTotal                           -- 当日合計月額固定費予備
        ,D_ReserveMonthlyFeeTaxTotal                        -- 当日合計月額固定費予備消費税
        ,D_AddClaimFeeTotal                                 -- 当日合計再発行手数料
        ,D_AddClaimFeeTaxTotal                              -- 当日合計再発行手数料消費税
        ,D_DamageInterestAmountTotal                        -- 当日合計遅延損害金
        ,D_AllTotal                                         -- 当日総合計
        ,D_SettlementFeeOther                               -- 当日売上明細決済手数料
        ,D_ClaimFeeOther                                    -- 当日売上明細請求手数料
        ,D_ClaimFeeTaxOther                                 -- 当日売上明細請求手数料消費税
        ,D_MonthlyFeeOther                                  -- 当日売上明細月額固定費
        ,D_MonthlyFeeTaxOther                               -- 当日売上明細月額固定費消費税
        ,D_IncludeMonthlyFeeOther                           -- 当日売上明細同梱月額固定費
        ,D_IncludeMonthlyFeeTaxOther                        -- 当日売上明細同梱月額固定費消費税
        ,D_ApiMonthlyFeeOther                               -- 当日売上明細API月額固定費
        ,D_ApiMonthlyFeeTaxOther                            -- 当日売上明細API月額固定費消費税
        ,D_CreditNoticeMonthlyFeeOther                      -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTaxOther                   -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFeeOther                     -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTaxOther                  -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFeeOther                           -- 当日売上明細月額固定費予備
        ,D_ReserveMonthlyFeeTaxOther                        -- 当日売上明細月額固定費予備消費税
        ,D_AddClaimFeeOther                                 -- 当日手数料明細再発行手数料
        ,D_AddClaimFeeTaxOther                              -- 当日手数料明細再発行手数料消費税
        ,D_DamageInterestAmountOther                        -- 当日手数料明細遅延損害金
        ,D_SettlementFeeDiff                                -- 当日差額決済手数料
        ,D_ClaimFeeDiff                                     -- 当日差額請求手数料
        ,D_ClaimFeeTaxDiff                                  -- 当日差額請求手数料消費税
        ,D_MonthlyFeeDiff                                   -- 当日差額月額固定費
        ,D_MonthlyFeeTaxDiff                                -- 当日差額月額固定費消費税
        ,D_IncludeMonthlyFeeDiff                            -- 当日差額同梱月額固定費
        ,D_IncludeMonthlyFeeTaxDiff                         -- 当日差額同梱月額固定費消費税
        ,D_ApiMonthlyFeeDiff                                -- 当日差額API月額固定費
        ,D_ApiMonthlyFeeTaxDiff                             -- 当日差額API月額固定費消費税
        ,D_CreditNoticeMonthlyFeeDiff                       -- 当日差額与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTaxDiff                    -- 当日差額与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFeeDiff                      -- 当日差額次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTaxDiff                   -- 当日差額次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFeeDiff                            -- 当日差額月額固定費予備
        ,D_ReserveMonthlyFeeTaxDiff                         -- 当日差額月額固定費予備消費税
        ,D_AddClaimFeeDiff                                  -- 当日差額再発行手数料
        ,D_AddClaimFeeTaxDiff                               -- 当日差額再発行手数料消費税
        ,D_DamageInterestAmountDiff                         -- 当日差額遅延損害金
        ,MB__AccountsReceivableBalance                      -- 月初未収金残高
        ,M_ChargeCount                                      -- 当月立替件数
        ,M_ChargeAmount                                     -- 当月立替金額
        ,M_CancelCount                                      -- 当月ｷｬﾝｾﾙ件数
        ,M_CancelAmount                                     -- 当月ｷｬﾝｾﾙ立替金額
        ,M_SettlementBackCount                              -- 当月立替金戻し件数
        ,M_SettlementBackAmount                             -- 当月立替金戻し金額
        ,M_TransferCount                                    -- 当月OEM移管件数
        ,M_TransferAmount                                   -- 当月OEM移管金額
        ,M_ReClaimFeeCount                                  -- 当月再発行手数料件数
        ,M_ReClaimFeeAmount                                 -- 当月再発行手数料額
        ,M_DamageCount                                      -- 当月遅延損害金件数
        ,M_DamageAmount                                     -- 当月遅延損害金額
        ,M_ReceiptCount                                     -- 当月入金件数
        ,M_ReceiptAmount                                    -- 当月入金額
        ,M_RepayCount                                       -- 当月返金件数
        ,M_RepayAmount                                      -- 当月返金額
        ,M_BadDebtCount                                     -- 当月貸倒件数
        ,M_BadDebtAmount                                    -- 当月貸倒金額
        ,M_OtherPaymentCount                                -- 当月その他件数
        ,M_OtherPaymentAmount                               -- 当月その他金額
        ,M_AccountsReceivableBalance                        -- 当月時点未収金残高
        ,M_SuspensePaymentsAmount                           -- 消費者未収金明細残高
        ,M_AccountsReceivableBalanceDiff                    -- 当月差額
        ,M_SettlementFee                                    -- 当月決済手数料
        ,M_ClaimFee                                         -- 当月請求手数料
        ,M_ClaimFeeTax                                      -- 当月請求手数料消費税
        ,M_MonthlyFee                                       -- 当月月額固定費
        ,M_MonthlyFeeTax                                    -- 当月月額固定費消費税
        ,M_IncludeMonthlyFee                                -- 当月同梱月額固定費
        ,M_IncludeMonthlyFeeTax                             -- 当月同梱月額固定費消費税
        ,M_ApiMonthlyFee                                    -- 当月API月額固定費
        ,M_ApiMonthlyFeeTax                                 -- 当月API月額固定費消費税
        ,M_CreditNoticeMonthlyFee                           -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTax                        -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFee                          -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTax                       -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFee                                -- 当月月額固定費予備
        ,M_ReserveMonthlyFeeTax                             -- 当月月額固定費予備消費税
        ,M_AddClaimFee                                      -- 当月再発行手数料
        ,M_AddClaimFeeTax                                   -- 当月再発行手数料消費税
        ,M_DamageInterestAmount                             -- 当月遅延損害金
        ,M_CanSettlementFee                                 -- 当月ｷｬﾝｾﾙ決済手数料
        ,M_CanClaimFee                                      -- 当月ｷｬﾝｾﾙ請求手数料
        ,M_CanClaimFeeTax                                   -- 当月ｷｬﾝｾﾙ請求手数料消費税
        ,M_SettlementFeeTotal                               -- 当月合計決済手数料
        ,M_ClaimFeeTotal                                    -- 当月合計請求手数料
        ,M_ClaimFeeTaxTotal                                 -- 当月合計請求手数料消費税
        ,M_MonthlyFeeTotal                                  -- 当月合計月額固定費
        ,M_MonthlyFeeTaxTotal                               -- 当月合計月額固定費消費税
        ,M_IncludeMonthlyFeeTotal                           -- 当月合計同梱月額固定費
        ,M_IncludeMonthlyFeeTaxTotal                        -- 当月合計同梱月額固定費消費税
        ,M_ApiMonthlyFeeTotal                               -- 当月合計API月額固定費
        ,M_ApiMonthlyFeeTaxTotal                            -- 当月合計API月額固定費消費税
        ,M_CreditNoticeMonthlyFeeTotal                      -- 当月合計与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTaxTotal                   -- 当月合計与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFeeTotal                     -- 当月合計次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTaxTotal                  -- 当月合計次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFeeTotal                           -- 当月合計月額固定費予備
        ,M_ReserveMonthlyFeeTaxTotal                        -- 当月合計月額固定費予備消費税
        ,M_AddClaimFeeTotal                                 -- 当月合計再発行手数料
        ,M_AddClaimFeeTaxTotal                              -- 当月合計再発行手数料消費税
        ,M_DamageInterestAmountTotal                        -- 当月合計遅延損害金
        ,M_AllTotal                                         -- 当月総合計
        ,M_SettlementFeeOther                               -- 当月売上明細決済手数料
        ,M_ClaimFeeOther                                    -- 当月売上明細請求手数料
        ,M_ClaimFeeTaxOther                                 -- 当月売上明細請求手数料消費税
        ,M_MonthlyFeeOther                                  -- 当月売上明細月額固定費
        ,M_MonthlyFeeTaxOther                               -- 当月売上明細月額固定費消費税
        ,M_IncludeMonthlyFeeOther                           -- 当月売上明細同梱月額固定費
        ,M_IncludeMonthlyFeeTaxOther                        -- 当月売上明細同梱月額固定費消費税
        ,M_ApiMonthlyFeeOther                               -- 当月売上明細API月額固定費
        ,M_ApiMonthlyFeeTaxOther                            -- 当月売上明細API月額固定費消費税
        ,M_CreditNoticeMonthlyFeeOther                      -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTaxOther                   -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFeeOther                     -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTaxOther                  -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFeeOther                           -- 当月売上明細月額固定費予備
        ,M_ReserveMonthlyFeeTaxOther                        -- 当月売上明細月額固定費予備消費税
        ,M_AddClaimFeeOther                                 -- 当月手数料明細再発行手数料
        ,M_AddClaimFeeTaxOther                              -- 当月手数料明細再発行手数料消費税
        ,M_DamageInterestAmountOther                        -- 当月手数料明細遅延損害金
        ,M_SettlementFeeDiff                                -- 当月差額決済手数料
        ,M_ClaimFeeDiff                                     -- 当月差額請求手数料
        ,M_ClaimFeeTaxDiff                                  -- 当月差額請求手数料消費税
        ,M_MonthlyFeeDiff                                   -- 当月差額月額固定費
        ,M_MonthlyFeeTaxDiff                                -- 当月差額月額固定費消費税
        ,M_IncludeMonthlyFeeDiff                            -- 当月差額同梱月額固定費
        ,M_IncludeMonthlyFeeTaxDiff                         -- 当月差額同梱月額固定費消費税
        ,M_ApiMonthlyFeeDiff                                -- 当月差額API月額固定費
        ,M_ApiMonthlyFeeTaxDiff                             -- 当月差額API月額固定費消費税
        ,M_CreditNoticeMonthlyFeeDiff                       -- 当月差額与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTaxDiff                    -- 当月差額与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFeeDiff                      -- 当月差額次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTaxDiff                   -- 当月差額次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFeeDiff                            -- 当月差額月額固定費予備
        ,M_ReserveMonthlyFeeTaxDiff                         -- 当月差額月額固定費予備消費税
        ,M_AddClaimFeeDiff                                  -- 当月差額再発行手数料
        ,M_AddClaimFeeTaxDiff                               -- 当月差額再発行手数料消費税
        ,M_DamageInterestAmountDiff                         -- 当月差額遅延損害金
        ,RegistDate                                         -- 登録日時
        ,RegistId                                           -- 登録者
        ,UpdateDate                                         -- 更新日時
        ,UpdateId                                           -- 更新者
        ,ValidFlg                                           -- 有効ﾌﾗｸﾞ
        )
    SELECT
         0                                                          -- 日次･月次区分
        ,v_BusinessDate                                             -- 集計日
        ,v_AccountingMonth                                          -- 会計月
        ,aw_dst.EnterpriseId                                        -- 加盟店ID
        ,CASE WHEN aw_dst.EnterpriseId = v_EnterpriseId
            THEN 'OEM移管分'
            ELSE ent.EnterpriseNameKj
         END                                                        -- 加盟店名
        ,aw_dst.DB__AccountsReceivableBalance                       -- 前日時点未収金残高
        ,aw_dst.D_ChargeCount                                       -- 当日立替件数
        ,aw_dst.D_ChargeAmount                                      -- 当日立替金額
        ,aw_dst.D_CancelCount                                       -- 当日ｷｬﾝｾﾙ件数
        ,aw_dst.D_CancelAmount                                      -- 当日ｷｬﾝｾﾙ立替金額
        ,aw_dst.D_SettlementBackCount                               -- 当日立替金戻し件数
        ,aw_dst.D_SettlementBackAmount                              -- 当日立替金戻し金額
        ,aw_dst.D_OemTransferCount                                  -- 当日OEM移管件数
        ,aw_dst.D_OemTransferAmount                                 -- 当日OEM移管金額
        ,aw_dst.D_ReClaimFeeCount                                   -- 当日再発行手数料件数
        ,aw_dst.D_ReClaimFeeAmount                                  -- 当日再発行手数料額
        ,aw_dst.D_DamageCount                                       -- 当日遅延損害金件数
        ,aw_dst.D_DamageAmount                                      -- 当日遅延損害金額
        ,aw_dst.D_ReceiptCount                                      -- 当日入金件数
        ,aw_dst.D_ReceiptAmount                                     -- 当日入金額
        ,aw_dst.D_RepayCount                                        -- 当日返金件数
        ,aw_dst.D_RepayAmount                                       -- 当日返金額
        ,aw_dst.D_BadDebtCount                                      -- 当日貸倒件数
        ,aw_dst.D_BadDebtAmount                                     -- 当日貸倒金額
        ,aw_dst.D_OtherPaymentCount                                 -- 当日その他件数
        ,aw_dst.D_OtherPaymentAmount                                -- 当日その他金額
        ,aw_dst.DB__AccountsReceivableBalance +
         aw_dst.D_ChargeAmount + aw_dst.D_CancelAmount +
         aw_dst.D_SettlementBackAmount + aw_dst.D_OemTransferAmount +
         aw_dst.D_ReClaimFeeAmount + aw_dst.D_DamageAmount +
         aw_dst.D_ReceiptAmount + aw_dst.D_RepayAmount +
         aw_dst.D_BadDebtAmount + aw_dst.D_OtherPaymentAmount       -- 当日時点未収金残高
        ,aw_dst.D_SettlementFee                                     -- 当日決済手数料
        ,aw_dst.D_ClaimFee                                          -- 当日請求手数料
        ,aw_dst.D_ClaimFeeTax                                       -- 当日請求手数料消費税
        ,aw_dst.D_MonthlyFee                                        -- 当日月額固定費
        ,aw_dst.D_MonthlyFeeTax                                     -- 当日月額固定費消費税
        ,aw_dst.D_IncludeMonthlyFee                                 -- 当日同梱月額固定費
        ,aw_dst.D_IncludeMonthlyFeeTax                              -- 当日同梱月額固定費消費税
        ,aw_dst.D_ApiMonthlyFee                                     -- 当日API月額固定費
        ,aw_dst.D_ApiMonthlyFeeTax                                  -- 当日API月額固定費消費税
        ,aw_dst.D_CreditNoticeMonthlyFee                            -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_CreditNoticeMonthlyFeeTax                         -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_NCreditNoticeMonthlyFee                           -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_NCreditNoticeMonthlyFeeTax                        -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_ReserveMonthlyFee                                 -- 当日月額固定費予備
        ,aw_dst.D_ReserveMonthlyFeeTax                              -- 当日月額固定費予備消費税
        ,aw_dst.D_AddClaimFee                                       -- 当日再発行手数料
        ,aw_dst.D_AddClaimFeeTax                                    -- 当日再発行手数料消費税
        ,aw_dst.D_DamageInterestAmount                              -- 当日遅延損害金
        ,aw_dst.D_CanSettlementFee                                  -- 当日ｷｬﾝｾﾙ決済手数料
        ,aw_dst.D_CanClaimFee                                       -- 当日ｷｬﾝｾﾙ請求手数料
        ,aw_dst.D_CanClaimFeeTax                                    -- 当日ｷｬﾝｾﾙ請求手数料消費税
        ,aw_dst.D_SettlementFee + aw_dst.D_CanSettlementFee         -- 当日合計決済手数料
        ,aw_dst.D_ClaimFee + aw_dst.D_CanClaimFee                   -- 当日合計請求手数料
        ,aw_dst.D_ClaimFeeTax + aw_dst.D_CanClaimFeeTax             -- 当日合計請求手数料消費税
        ,aw_dst.D_MonthlyFee                                        -- 当日合計月額固定費
        ,aw_dst.D_MonthlyFeeTax                                     -- 当日合計月額固定費消費税
        ,aw_dst.D_IncludeMonthlyFee                                 -- 当日合計同梱月額固定費
        ,aw_dst.D_IncludeMonthlyFeeTax                              -- 当日合計同梱月額固定費消費税
        ,aw_dst.D_ApiMonthlyFee                                     -- 当日合計API月額固定費
        ,aw_dst.D_ApiMonthlyFeeTax                                  -- 当日合計API月額固定費消費税
        ,aw_dst.D_CreditNoticeMonthlyFee                            -- 当日合計与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_CreditNoticeMonthlyFeeTax                         -- 当日合計与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_NCreditNoticeMonthlyFee                           -- 当日合計次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_NCreditNoticeMonthlyFeeTax                        -- 当日合計次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_ReserveMonthlyFee                                 -- 当日合計月額固定費予備
        ,aw_dst.D_ReserveMonthlyFeeTax                              -- 当日合計月額固定費予備消費税
        ,aw_dst.D_AddClaimFee                                       -- 当日合計再発行手数料
        ,aw_dst.D_AddClaimFeeTax                                    -- 当日合計再発行手数料消費税
        ,aw_dst.D_DamageInterestAmount                              -- 当日合計遅延損害金
        ,aw_dst.D_SettlementFee + aw_dst.D_CanSettlementFee +
         aw_dst.D_ClaimFee + aw_dst.D_CanClaimFee +
         aw_dst.D_ClaimFeeTax + aw_dst.D_CanClaimFeeTax +
         aw_dst.D_MonthlyFee + aw_dst.D_MonthlyFeeTax +
         aw_dst.D_IncludeMonthlyFee + aw_dst.D_IncludeMonthlyFeeTax +
         aw_dst.D_ApiMonthlyFee + aw_dst.D_ApiMonthlyFeeTax +
         aw_dst.D_CreditNoticeMonthlyFee+ aw_dst.D_CreditNoticeMonthlyFeeTax +
         aw_dst.D_NCreditNoticeMonthlyFee + aw_dst.D_NCreditNoticeMonthlyFeeTax +
         aw_dst.D_ReserveMonthlyFee + aw_dst.D_ReserveMonthlyFeeTax +
         aw_dst.D_AddClaimFee + aw_dst.D_AddClaimFeeTax +
         aw_dst.D_DamageInterestAmount                              -- 当日総合計
        ,IFNULL(at_dsd.SettlementFee, 0)                            -- 当日売上明細決済手数料
        ,IFNULL(at_dsd.ClaimFee, 0)                                 -- 当日売上明細請求手数料
        ,IFNULL(at_dsd.ClaimFeeTax, 0)                              -- 当日売上明細請求手数料消費税
        ,IFNULL(at_dsd.MonthlyFee, 0)                               -- 当日売上明細月額固定費
        ,IFNULL(at_dsd.MonthlyFeeTax, 0)                            -- 当日売上明細月額固定費消費税
        ,IFNULL(at_dsd.IncludeMonthlyFee, 0)                        -- 当日売上明細同梱月額固定費
        ,IFNULL(at_dsd.IncludeMonthlyFeeTax, 0)                     -- 当日売上明細同梱月額固定費消費税
        ,IFNULL(at_dsd.ApiMonthlyFee, 0)                            -- 当日売上明細API月額固定費
        ,IFNULL(at_dsd.ApiMonthlyFeeTax, 0)                         -- 当日売上明細API月額固定費消費税
        ,IFNULL(at_dsd.CreditNoticeMonthlyFee, 0)                   -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,IFNULL(at_dsd.CreditNoticeMonthlyFeeTax, 0)                -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,IFNULL(at_dsd.NCreditNoticeMonthlyFee, 0)                  -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,IFNULL(at_dsd.NCreditNoticeMonthlyFeeTax, 0)               -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,IFNULL(at_dsd.ReserveMonthlyFee, 0)                        -- 当日売上明細月額固定費予備
        ,IFNULL(at_dsd.ReserveMonthlyFeeTax, 0)                     -- 当日売上明細月額固定費予備消費税
        ,IFNULL(at_rfs.Clm_L_ClaimFee, 0)                           -- 当日手数料明細再発行手数料
        ,IFNULL(at_rfs.Clm_L_ClaimFeeTax, 0)                        -- 当日手数料明細再発行手数料消費税
        ,IFNULL(at_rfs.Clm_L_DamageInterestAmount, 0)               -- 当日手数料明細遅延損害金
        ,aw_dst.D_SettlementFee + aw_dst.D_CanSettlementFee - IFNULL(at_dsd.SettlementFee, 0)       -- 当日差額決済手数料
        ,aw_dst.D_ClaimFee + aw_dst.D_CanClaimFee - IFNULL(at_dsd.ClaimFee, 0)                      -- 当日差額請求手数料
        ,aw_dst.D_ClaimFeeTax + aw_dst.D_CanClaimFeeTax - IFNULL(at_dsd.ClaimFeeTax, 0)             -- 当日差額請求手数料消費税
        ,aw_dst.D_MonthlyFee - IFNULL(at_dsd.MonthlyFee, 0)                                         -- 当日差額月額固定費
        ,aw_dst.D_MonthlyFeeTax - IFNULL(at_dsd.MonthlyFeeTax, 0)                                   -- 当日差額月額固定費消費税
        ,aw_dst.D_IncludeMonthlyFee - IFNULL(at_dsd.IncludeMonthlyFee, 0)                           -- 当日差額同梱月額固定費
        ,aw_dst.D_IncludeMonthlyFeeTax - IFNULL(at_dsd.IncludeMonthlyFeeTax, 0)                     -- 当日差額同梱月額固定費消費税
        ,aw_dst.D_ApiMonthlyFee - IFNULL(at_dsd.ApiMonthlyFee, 0)                                   -- 当日差額API月額固定費
        ,aw_dst.D_ApiMonthlyFeeTax - IFNULL(at_dsd.ApiMonthlyFeeTax, 0)                             -- 当日差額API月額固定費消費税
        ,aw_dst.D_CreditNoticeMonthlyFee - IFNULL(at_dsd.CreditNoticeMonthlyFee, 0)                 -- 当日差額与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_CreditNoticeMonthlyFeeTax - IFNULL(at_dsd.CreditNoticeMonthlyFeeTax, 0)           -- 当日差額与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_NCreditNoticeMonthlyFee - IFNULL(at_dsd.NCreditNoticeMonthlyFee, 0)               -- 当日差額次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_NCreditNoticeMonthlyFeeTax - IFNULL(at_dsd.NCreditNoticeMonthlyFeeTax, 0)         -- 当日差額次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_ReserveMonthlyFee - IFNULL(at_dsd.ReserveMonthlyFee, 0)                           -- 当日差額月額固定費予備
        ,aw_dst.D_ReserveMonthlyFeeTax -  IFNULL(at_dsd.ReserveMonthlyFeeTax, 0)                    -- 当日差額月額固定費予備消費税
        ,aw_dst.D_AddClaimFee - IFNULL(at_rfs.Clm_L_ClaimFee, 0)                                    -- 当日差額再発行手数料
        ,aw_dst.D_AddClaimFeeTax - IFNULL(at_rfs.Clm_L_ClaimFeeTax, 0)                              -- 当日差額再発行手数料消費税
        ,aw_dst.D_DamageInterestAmount - IFNULL(at_rfs.Clm_L_DamageInterestAmount, 0)               -- 当日差額遅延損害金
        ,aw_dst.MB__AccountsReceivableBalance                       -- 月初未収金残高
        ,aw_dst.M_ChargeCount                                       -- 当月立替件数
        ,aw_dst.M_ChargeAmount                                      -- 当月立替金額
        ,aw_dst.M_CancelCount                                       -- 当月ｷｬﾝｾﾙ件数
        ,aw_dst.M_CancelAmount                                      -- 当月ｷｬﾝｾﾙ立替金額
        ,aw_dst.M_SettlementBackCount                               -- 当月立替金戻し件数
        ,aw_dst.M_SettlementBackAmount                              -- 当月立替金戻し金額
        ,aw_dst.M_TransferCount                                     -- 当月OEM移管件数
        ,aw_dst.M_TransferAmount                                    -- 当月OEM移管金額
        ,aw_dst.M_ReClaimFeeCount                                   -- 当月再発行手数料件数
        ,aw_dst.M_ReClaimFeeAmount                                  -- 当月再発行手数料額
        ,aw_dst.M_DamageCount                                       -- 当月遅延損害金件数
        ,aw_dst.M_DamageAmount                                      -- 当月遅延損害金額
        ,aw_dst.M_ReceiptCount                                      -- 当月入金件数
        ,aw_dst.M_ReceiptAmount                                     -- 当月入金額
        ,aw_dst.M_RepayCount                                        -- 当月返金件数
        ,aw_dst.M_RepayAmount                                       -- 当月返金額
        ,aw_dst.M_BadDebtCount                                      -- 当月貸倒件数
        ,aw_dst.M_BadDebtAmount                                     -- 当月貸倒金額
        ,aw_dst.M_OtherPaymentCount                                 -- 当月その他件数
        ,aw_dst.M_OtherPaymentAmount                                -- 当月その他金額
        ,aw_dst.MB__AccountsReceivableBalance +
         aw_dst.M_ChargeAmount + aw_dst.M_CancelAmount +
         aw_dst.M_SettlementBackAmount + aw_dst.M_TransferAmount +
         aw_dst.M_ReClaimFeeAmount + aw_dst.M_DamageAmount +
         aw_dst.M_ReceiptAmount + aw_dst.M_RepayAmount +
         aw_dst.M_BadDebtAmount + aw_dst.M_OtherPaymentAmount       -- 当月時点未収金残高
        ,null                                                       -- 消費者未収金明細残高
        ,null                                                       -- 当月差額
        ,aw_dst.M_SettlementFee                                     -- 当月決済手数料
        ,aw_dst.M_ClaimFee                                          -- 当月請求手数料
        ,aw_dst.M_ClaimFeeTax                                       -- 当月請求手数料消費税
        ,aw_dst.M_MonthlyFee                                        -- 当月月額固定費
        ,aw_dst.M_MonthlyFeeTax                                     -- 当月月額固定費消費税
        ,aw_dst.M_IncludeMonthlyFee                                 -- 当月同梱月額固定費
        ,aw_dst.M_IncludeMonthlyFeeTax                              -- 当月同梱月額固定費消費税
        ,aw_dst.M_ApiMonthlyFee                                     -- 当月API月額固定費
        ,aw_dst.M_ApiMonthlyFeeTax                                  -- 当月API月額固定費消費税
        ,aw_dst.M_CreditNoticeMonthlyFee                            -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.M_CreditNoticeMonthlyFeeTax                         -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.M_NCreditNoticeMonthlyFee                           -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.M_NCreditNoticeMonthlyFeeTax                        -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.M_ReserveMonthlyFee                                 -- 当月月額固定費予備
        ,aw_dst.M_ReserveMonthlyFeeTax                              -- 当月月額固定費予備消費税
        ,aw_dst.M_AddClaimFee                                       -- 当月再発行手数料
        ,aw_dst.M_AddClaimFeeTax                                    -- 当月再発行手数料消費税
        ,aw_dst.M_DamageInterestAmount                              -- 当月遅延損害金
        ,aw_dst.M_CanSettlementFee                                  -- 当月ｷｬﾝｾﾙ決済手数料
        ,aw_dst.M_CanClaimFee                                       -- 当月ｷｬﾝｾﾙ請求手数料
        ,aw_dst.M_CanClaimFeeTax                                    -- 当月ｷｬﾝｾﾙ請求手数料消費税
        ,aw_dst.M_SettlementFee + aw_dst.M_CanSettlementFee         -- 当月合計決済手数料
        ,aw_dst.M_ClaimFee + aw_dst.M_CanClaimFee                   -- 当月合計請求手数料
        ,aw_dst.M_ClaimFeeTax + aw_dst.M_CanClaimFeeTax             -- 当月合計請求手数料消費税
        ,aw_dst.M_MonthlyFee                                        -- 当月合計月額固定費
        ,aw_dst.M_MonthlyFeeTax                                     -- 当月合計月額固定費消費税
        ,aw_dst.M_IncludeMonthlyFee                                 -- 当月合計同梱月額固定費
        ,aw_dst.M_IncludeMonthlyFeeTax                              -- 当月合計同梱月額固定費消費税
        ,aw_dst.M_ApiMonthlyFee                                     -- 当月合計API月額固定費
        ,aw_dst.M_ApiMonthlyFeeTax                                  -- 当月合計API月額固定費消費税
        ,aw_dst.M_CreditNoticeMonthlyFee                            -- 当月合計与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.M_CreditNoticeMonthlyFeeTax                         -- 当月合計与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.M_NCreditNoticeMonthlyFee                           -- 当月合計次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.M_NCreditNoticeMonthlyFeeTax                        -- 当月合計次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.M_ReserveMonthlyFee                                 -- 当月合計月額固定費予備
        ,aw_dst.M_ReserveMonthlyFeeTax                              -- 当月合計月額固定費予備消費税
        ,aw_dst.M_AddClaimFee                                       -- 当月合計再発行手数料
        ,aw_dst.M_AddClaimFeeTax                                    -- 当月合計再発行手数料消費税
        ,aw_dst.M_DamageInterestAmount                              -- 当月合計遅延損害金
        ,aw_dst.M_SettlementFee + aw_dst.M_CanSettlementFee +
         aw_dst.M_ClaimFee + aw_dst.M_CanClaimFee +
         aw_dst.M_ClaimFeeTax + aw_dst.M_CanClaimFeeTax +
         aw_dst.M_MonthlyFee + aw_dst.M_MonthlyFeeTax +
         aw_dst.M_IncludeMonthlyFee + aw_dst.M_IncludeMonthlyFeeTax +
         aw_dst.M_ApiMonthlyFee + aw_dst.M_ApiMonthlyFeeTax +
         aw_dst.M_CreditNoticeMonthlyFee + aw_dst.M_CreditNoticeMonthlyFeeTax +
         aw_dst.M_NCreditNoticeMonthlyFee + aw_dst.M_NCreditNoticeMonthlyFeeTax +
         aw_dst.M_ReserveMonthlyFee + aw_dst.M_ReserveMonthlyFeeTax +
         aw_dst.M_AddClaimFee + aw_dst.M_AddClaimFeeTax +
         aw_dst.M_DamageInterestAmount                              -- 当月総合計
        ,null                                                       -- 当月売上明細決済手数料
        ,null                                                       -- 当月売上明細請求手数料
        ,null                                                       -- 当月売上明細請求手数料消費税
        ,null                                                       -- 当月売上明細月額固定費
        ,null                                                       -- 当月売上明細月額固定費消費税
        ,null                                                       -- 当月売上明細同梱月額固定費
        ,null                                                       -- 当月売上明細同梱月額固定費消費税
        ,null                                                       -- 当月売上明細API月額固定費
        ,null                                                       -- 当月売上明細API月額固定費消費税
        ,null                                                       -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細月額固定費予備
        ,null                                                       -- 当月売上明細月額固定費予備消費税
        ,null                                                       -- 当月手数料明細再発行手数料
        ,null                                                       -- 当月手数料明細再発行手数料消費税
        ,null                                                       -- 当月手数料明細遅延損害金
        ,null                                                       -- 当月差額決済手数料
        ,null                                                       -- 当月差額請求手数料
        ,null                                                       -- 当月差額請求手数料消費税
        ,null                                                       -- 当月差額月額固定費
        ,null                                                       -- 当月差額月額固定費消費税
        ,null                                                       -- 当月差額同梱月額固定費
        ,null                                                       -- 当月差額同梱月額固定費消費税
        ,null                                                       -- 当月差額API月額固定費
        ,null                                                       -- 当月差額API月額固定費消費税
        ,null                                                       -- 当月差額与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月差額与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月差額次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月差額次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月差額月額固定費予備
        ,null                                                       -- 当月差額月額固定費予備消費税
        ,null                                                       -- 当月差額再発行手数料
        ,null                                                       -- 当月差額再発行手数料消費税
        ,null                                                       -- 当月差額遅延損害金
        ,NOW()                                                      -- 登録日時
        ,pi_user_id                                                 -- 登録者
        ,NOW()                                                      -- 更新日時
        ,pi_user_id                                                 -- 更新者
        ,1                                                          -- 有効ﾌﾗｸﾞ
    FROM (      SELECT
                     EnterpriseId                                           AS EnterpriseId
                    ,SUM( IFNULL( DB__AccountsReceivableBalance, 0 ) )      AS DB__AccountsReceivableBalance
                    ,SUM( IFNULL( D_ChargeCount, 0 ) )                      AS D_ChargeCount
                    ,SUM( IFNULL( D_ChargeAmount, 0 ) )                     AS D_ChargeAmount
                    ,SUM( IFNULL( D_CancelCount, 0 ) )                      AS D_CancelCount
                    ,SUM( IFNULL( D_CancelAmount, 0 ) )                     AS D_CancelAmount
                    ,SUM( IFNULL( D_SettlementBackCount, 0 ) )              AS D_SettlementBackCount
                    ,SUM( IFNULL( D_SettlementBackAmount, 0 ) )             AS D_SettlementBackAmount
                    ,SUM( IFNULL( D_OemTransferCount, 0 ) )                 AS D_OemTransferCount
                    ,SUM( IFNULL( D_OemTransferAmount, 0 ) )                AS D_OemTransferAmount
                    ,SUM( IFNULL( D_ReClaimFeeCount, 0 ) )                  AS D_ReClaimFeeCount
                    ,SUM( IFNULL( D_ReClaimFeeAmount, 0 ) )                 AS D_ReClaimFeeAmount
                    ,SUM( IFNULL( D_DamageCount, 0 ) )                      AS D_DamageCount
                    ,SUM( IFNULL( D_DamageAmount, 0 ) )                     AS D_DamageAmount
                    ,SUM( IFNULL( D_ReceiptCount, 0 ) )                     AS D_ReceiptCount
                    ,SUM( IFNULL( D_ReceiptAmount, 0 ) )                    AS D_ReceiptAmount
                    ,SUM( IFNULL( D_RepayCount, 0 ) )                       AS D_RepayCount
                    ,SUM( IFNULL( D_RepayAmount, 0 ) )                      AS D_RepayAmount
                    ,SUM( IFNULL( D_BadDebtCount, 0 ) )                     AS D_BadDebtCount
                    ,SUM( IFNULL( D_BadDebtAmount, 0 ) )                    AS D_BadDebtAmount
                    ,SUM( IFNULL( D_OtherPaymentCount, 0 ) )                AS D_OtherPaymentCount
                    ,SUM( IFNULL( D_OtherPaymentAmount, 0 ) )               AS D_OtherPaymentAmount
                    ,SUM( IFNULL( D_AccountsReceivableBalance, 0 ) )        AS D_AccountsReceivableBalance
                    ,SUM( IFNULL( D_SettlementFee, 0 ) )                    AS D_SettlementFee
                    ,SUM( IFNULL( D_ClaimFee, 0 ) )                         AS D_ClaimFee
                    ,SUM( IFNULL( D_ClaimFeeTax, 0 ) )                      AS D_ClaimFeeTax
                    ,SUM( IFNULL( D_MonthlyFee, 0 ) )                       AS D_MonthlyFee
                    ,SUM( IFNULL( D_MonthlyFeeTax, 0 ) )                    AS D_MonthlyFeeTax
                    ,SUM( IFNULL( D_IncludeMonthlyFee, 0 ) )                AS D_IncludeMonthlyFee
                    ,SUM( IFNULL( D_IncludeMonthlyFeeTax, 0 ) )             AS D_IncludeMonthlyFeeTax
                    ,SUM( IFNULL( D_ApiMonthlyFee, 0 ) )                    AS D_ApiMonthlyFee
                    ,SUM( IFNULL( D_ApiMonthlyFeeTax, 0 ) )                 AS D_ApiMonthlyFeeTax
                    ,SUM( IFNULL( D_CreditNoticeMonthlyFee, 0 ) )           AS D_CreditNoticeMonthlyFee
                    ,SUM( IFNULL( D_CreditNoticeMonthlyFeeTax, 0 ) )        AS D_CreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( D_NCreditNoticeMonthlyFee, 0 ) )          AS D_NCreditNoticeMonthlyFee
                    ,SUM( IFNULL( D_NCreditNoticeMonthlyFeeTax, 0 ) )       AS D_NCreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( D_ReserveMonthlyFee, 0 ) )                AS D_ReserveMonthlyFee
                    ,SUM( IFNULL( D_ReserveMonthlyFeeTax, 0 ) )             AS D_ReserveMonthlyFeeTax
                    ,SUM( IFNULL( D_AddClaimFee, 0 ) )                      AS D_AddClaimFee
                    ,SUM( IFNULL( D_AddClaimFeeTax, 0 ) )                   AS D_AddClaimFeeTax
                    ,SUM( IFNULL( D_DamageInterestAmount, 0 ) )             AS D_DamageInterestAmount
                    ,SUM( IFNULL( D_CanSettlementFee, 0 ) )                 AS D_CanSettlementFee
                    ,SUM( IFNULL( D_CanClaimFee, 0 ) )                      AS D_CanClaimFee
                    ,SUM( IFNULL( D_CanClaimFeeTax, 0 ) )                   AS D_CanClaimFeeTax
                    ,SUM( IFNULL( D_SettlementFeeOther, 0 ) )               AS D_SettlementFeeOther
                    ,SUM( IFNULL( D_ClaimFeeOther, 0 ) )                    AS D_ClaimFeeOther
                    ,SUM( IFNULL( D_ClaimFeeTaxOther, 0 ) )                 AS D_ClaimFeeTaxOther
                    ,SUM( IFNULL( D_MonthlyFeeOther, 0 ) )                  AS D_MonthlyFeeOther
                    ,SUM( IFNULL( D_MonthlyFeeTaxOther, 0 ) )               AS D_MonthlyFeeTaxOther
                    ,SUM( IFNULL( D_IncludeMonthlyFeeOther, 0 ) )           AS D_IncludeMonthlyFeeOther
                    ,SUM( IFNULL( D_IncludeMonthlyFeeTaxOther, 0 ) )        AS D_IncludeMonthlyFeeTaxOther
                    ,SUM( IFNULL( D_ApiMonthlyFeeOther, 0 ) )               AS D_ApiMonthlyFeeOther
                    ,SUM( IFNULL( D_ApiMonthlyFeeTaxOther, 0 ) )            AS D_ApiMonthlyFeeTaxOther
                    ,SUM( IFNULL( D_CreditNoticeMonthlyFeeOther, 0 ) )      AS D_CreditNoticeMonthlyFeeOther
                    ,SUM( IFNULL( D_CreditNoticeMonthlyFeeTaxOther, 0 ) )   AS D_CreditNoticeMonthlyFeeTaxOther
                    ,SUM( IFNULL( D_NCreditNoticeMonthlyFeeOther, 0 ) )     AS D_NCreditNoticeMonthlyFeeOther
                    ,SUM( IFNULL( D_NCreditNoticeMonthlyFeeTaxOther, 0 ) )  AS D_NCreditNoticeMonthlyFeeTaxOther
                    ,SUM( IFNULL( D_ReserveMonthlyFeeOther, 0 ) )           AS D_ReserveMonthlyFeeOther
                    ,SUM( IFNULL( D_ReserveMonthlyFeeTaxOther, 0 ) )        AS D_ReserveMonthlyFeeTaxOther
                    ,SUM( IFNULL( D_AddClaimFeeOther, 0 ) )                 AS D_AddClaimFeeOther
                    ,SUM( IFNULL( D_AddClaimFeeTaxOther, 0 ) )              AS D_AddClaimFeeTaxOther
                    ,SUM( IFNULL( D_DamageInterestAmountOther, 0 ) )        AS D_DamageInterestAmountOther
                    ,SUM( IFNULL( MB__AccountsReceivableBalance, 0 ) )      AS MB__AccountsReceivableBalance
                    ,SUM( IFNULL( M_ChargeCount, 0 ) )                      AS M_ChargeCount
                    ,SUM( IFNULL( M_ChargeAmount, 0 ) )                     AS M_ChargeAmount
                    ,SUM( IFNULL( M_CancelCount, 0 ) )                      AS M_CancelCount
                    ,SUM( IFNULL( M_CancelAmount, 0 ) )                     AS M_CancelAmount
                    ,SUM( IFNULL( M_SettlementBackCount, 0 ) )              AS M_SettlementBackCount
                    ,SUM( IFNULL( M_SettlementBackAmount, 0 ) )             AS M_SettlementBackAmount
                    ,SUM( IFNULL( M_TransferCount, 0 ) )                    AS M_TransferCount
                    ,SUM( IFNULL( M_TransferAmount, 0 ) )                   AS M_TransferAmount
                    ,SUM( IFNULL( M_ReClaimFeeCount, 0 ) )                  AS M_ReClaimFeeCount
                    ,SUM( IFNULL( M_ReClaimFeeAmount, 0 ) )                 AS M_ReClaimFeeAmount
                    ,SUM( IFNULL( M_DamageCount, 0 ) )                      AS M_DamageCount
                    ,SUM( IFNULL( M_DamageAmount, 0 ) )                     AS M_DamageAmount
                    ,SUM( IFNULL( M_ReceiptCount, 0 ) )                     AS M_ReceiptCount
                    ,SUM( IFNULL( M_ReceiptAmount, 0 ) )                    AS M_ReceiptAmount
                    ,SUM( IFNULL( M_RepayCount, 0 ) )                       AS M_RepayCount
                    ,SUM( IFNULL( M_RepayAmount, 0 ) )                      AS M_RepayAmount
                    ,SUM( IFNULL( M_BadDebtCount, 0 ) )                     AS M_BadDebtCount
                    ,SUM( IFNULL( M_BadDebtAmount, 0 ) )                    AS M_BadDebtAmount
                    ,SUM( IFNULL( M_OtherPaymentCount, 0 ) )                AS M_OtherPaymentCount
                    ,SUM( IFNULL( M_OtherPaymentAmount, 0 ) )               AS M_OtherPaymentAmount
                    ,SUM( IFNULL( M_AccountsReceivableBalance, 0 ) )        AS M_AccountsReceivableBalance
                    ,SUM( IFNULL( M_SuspensePaymentsAmount, 0 ) )           AS M_SuspensePaymentsAmount
                    ,SUM( IFNULL( M_AccountsReceivableBalanceDiff, 0 ) )    AS M_AccountsReceivableBalanceDiff
                    ,SUM( IFNULL( M_SettlementFee, 0 ) )                    AS M_SettlementFee
                    ,SUM( IFNULL( M_ClaimFee, 0 ) )                         AS M_ClaimFee
                    ,SUM( IFNULL( M_ClaimFeeTax, 0 ) )                      AS M_ClaimFeeTax
                    ,SUM( IFNULL( M_MonthlyFee, 0 ) )                       AS M_MonthlyFee
                    ,SUM( IFNULL( M_MonthlyFeeTax, 0 ) )                    AS M_MonthlyFeeTax
                    ,SUM( IFNULL( M_IncludeMonthlyFee, 0 ) )                AS M_IncludeMonthlyFee
                    ,SUM( IFNULL( M_IncludeMonthlyFeeTax, 0 ) )             AS M_IncludeMonthlyFeeTax
                    ,SUM( IFNULL( M_ApiMonthlyFee, 0 ) )                    AS M_ApiMonthlyFee
                    ,SUM( IFNULL( M_ApiMonthlyFeeTax, 0 ) )                 AS M_ApiMonthlyFeeTax
                    ,SUM( IFNULL( M_CreditNoticeMonthlyFee, 0 ) )           AS M_CreditNoticeMonthlyFee
                    ,SUM( IFNULL( M_CreditNoticeMonthlyFeeTax, 0 ) )        AS M_CreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( M_NCreditNoticeMonthlyFee, 0 ) )          AS M_NCreditNoticeMonthlyFee
                    ,SUM( IFNULL( M_NCreditNoticeMonthlyFeeTax, 0 ) )       AS M_NCreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( M_ReserveMonthlyFee, 0 ) )                AS M_ReserveMonthlyFee
                    ,SUM( IFNULL( M_ReserveMonthlyFeeTax, 0 ) )             AS M_ReserveMonthlyFeeTax
                    ,SUM( IFNULL( M_AddClaimFee, 0 ) )                      AS M_AddClaimFee
                    ,SUM( IFNULL( M_AddClaimFeeTax, 0 ) )                   AS M_AddClaimFeeTax
                    ,SUM( IFNULL( M_DamageInterestAmount, 0 ) )             AS M_DamageInterestAmount
                    ,SUM( IFNULL( M_CanSettlementFee, 0 ) )                 AS M_CanSettlementFee
                    ,SUM( IFNULL( M_CanClaimFee, 0 ) )                      AS M_CanClaimFee
                    ,SUM( IFNULL( M_CanClaimFeeTax, 0 ) )                   AS M_CanClaimFeeTax
                    ,SUM( IFNULL( M_SettlementFeeOther, 0 ) )               AS M_SettlementFeeOther
                    ,SUM( IFNULL( M_ClaimFeeOther, 0 ) )                    AS M_ClaimFeeOther
                    ,SUM( IFNULL( M_ClaimFeeTaxOther, 0 ) )                 AS M_ClaimFeeTaxOther
                    ,SUM( IFNULL( M_MonthlyFeeOther, 0 ) )                  AS M_MonthlyFeeOther
                    ,SUM( IFNULL( M_MonthlyFeeTaxOther, 0 ) )               AS M_MonthlyFeeTaxOther
                    ,SUM( IFNULL( M_IncludeMonthlyFeeOther, 0 ) )           AS M_IncludeMonthlyFeeOther
                    ,SUM( IFNULL( M_IncludeMonthlyFeeTaxOther, 0 ) )        AS M_IncludeMonthlyFeeTaxOther
                    ,SUM( IFNULL( M_ApiMonthlyFeeOther, 0 ) )               AS M_ApiMonthlyFeeOther
                    ,SUM( IFNULL( M_ApiMonthlyFeeTaxOther, 0 ) )            AS M_ApiMonthlyFeeTaxOther
                    ,SUM( IFNULL( M_CreditNoticeMonthlyFeeOther, 0 ) )      AS M_CreditNoticeMonthlyFeeOther
                    ,SUM( IFNULL( M_CreditNoticeMonthlyFeeTaxOther, 0 ) )   AS M_CreditNoticeMonthlyFeeTaxOther
                    ,SUM( IFNULL( M_NCreditNoticeMonthlyFeeOther, 0 ) )     AS M_NCreditNoticeMonthlyFeeOther
                    ,SUM( IFNULL( M_NCreditNoticeMonthlyFeeTaxOther, 0 ) )  AS M_NCreditNoticeMonthlyFeeTaxOther
                    ,SUM( IFNULL( M_ReserveMonthlyFeeOther, 0 ) )           AS M_ReserveMonthlyFeeOther
                    ,SUM( IFNULL( M_ReserveMonthlyFeeTaxOther, 0 ) )        AS M_ReserveMonthlyFeeTaxOther
                    ,SUM( IFNULL( M_AddClaimFeeOther, 0 ) )                 AS M_AddClaimFeeOther
                    ,SUM( IFNULL( M_AddClaimFeeTaxOther, 0 ) )              AS M_AddClaimFeeTaxOther
                    ,SUM( IFNULL( M_DamageInterestAmountOther, 0 ) )        AS M_DamageInterestAmountOther
                FROM  AW_DailyStatisticsTable
                GROUP BY EnterpriseId ) AS aw_dst
    LEFT JOIN( SELECT
                     EnterpriseId                                           AS AT_DSD_EnterpriseId
                    ,SUM( IFNULL( SettlementFee, 0 ) )                      AS SettlementFee
                    ,SUM( IFNULL( ClaimFee, 0 ) )                           AS ClaimFee
                    ,SUM( IFNULL( ClaimFeeTax, 0 ) )                        AS ClaimFeeTax
                    ,SUM( IFNULL( MonthlyFee, 0 ) )                         AS MonthlyFee
                    ,SUM( IFNULL( MonthlyFeeTax, 0 ) )                      AS MonthlyFeeTax
                    ,SUM( IFNULL( IncludeMonthlyFee, 0 ) )                  AS IncludeMonthlyFee
                    ,SUM( IFNULL( IncludeMonthlyFeeTax, 0 ) )               AS IncludeMonthlyFeeTax
                    ,SUM( IFNULL( ApiMonthlyFee, 0 ) )                      AS ApiMonthlyFee
                    ,SUM( IFNULL( ApiMonthlyFeeTax, 0 ) )                   AS ApiMonthlyFeeTax
                    ,SUM( IFNULL( CreditNoticeMonthlyFee, 0 ) )             AS CreditNoticeMonthlyFee
                    ,SUM( IFNULL( CreditNoticeMonthlyFeeTax, 0 ) )          AS CreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( NCreditNoticeMonthlyFee, 0 ) )            AS NCreditNoticeMonthlyFee
                    ,SUM( IFNULL( NCreditNoticeMonthlyFeeTax, 0 ) )         AS NCreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( ReserveMonthlyFee, 0 ) )                  AS ReserveMonthlyFee
                    ,SUM( IFNULL( ReserveMonthlyFeeTax, 0 ) )               AS ReserveMonthlyFeeTax
                FROM  AT_Daily_SalesDetails
                WHERE ProcessingDate = v_BusinessDate
                AND   AccountDate    = v_AccountingMonth
                AND   SalesDefiniteConditions in ('着荷確認日', '入金日', '役務提供日', 'キャンセル日', 'キャンセル確定日', '月額固定費')
                AND      ValidFlg    = 1
                AND      OemId       = 0
                GROUP BY EnterpriseId ) AS at_dsd
    ON  at_dsd.AT_DSD_EnterpriseId = aw_dst.EnterpriseId
    LEFT JOIN( SELECT
                     EnterpriseId                                           AS AT_RFS_EnterpriseId
                    ,SUM( IFNULL( Clm_L_ClaimFee, 0 ) )                     AS Clm_L_ClaimFee
                    ,SUM( IFNULL( Clm_L_ClaimFeeTax, 0 ) )                  AS Clm_L_ClaimFeeTax
                    ,SUM( IFNULL( Clm_L_DamageInterestAmount, 0 ) )         AS Clm_L_DamageInterestAmount
                FROM AT_ReissueFeeSpecification
                WHERE ProcessingDate = v_BusinessDate
                AND   AccountDate    = v_AccountingMonth
                AND   ValidFlg       = 1
                -- 直営 + 債権移管されたもの（OemId が 0 以外で、業務日付より前に債権移管がなされているもの）が抽出対象 ↓↓↓
                AND   (OemId = 0 OR (OemId <> 0 AND OemTransferDate IS NOT NULL AND OemTransferDate < v_BusinessDate))
                GROUP BY EnterpriseId ) AS at_rfs
    ON  at_rfs.AT_RFS_EnterpriseId = aw_dst.EnterpriseId
    LEFT JOIN  T_Enterprise ent
    ON  ent.EnterpriseId           = aw_dst.EnterpriseId;

    -- ---------------------------------------------------------------------------------
    -- 2016/01/05_memo
    -- 会計の月次処理が実行されるまでは、業務日付の年月 <> 会計月の年月 になる。
    --   → つまり･･･会計月の翌月の年月 = 業務日付（集計日）の年月分を作成する必要がある
    -- 以下は 業務日付（集計日）の年月 <> 会計月の年月の場合に処理する
    -- ---------------------------------------------------------------------------------
    -- ------------------------------
    -- 3.1.直営日次統計表ﾜｰｸをﾄﾗﾝｹｰﾄする
    -- ------------------------------
    TRUNCATE AW_DailyStatisticsTable;

    -- ------------------------------
    -- 3.2.業務日付=日別直営日次統計表.集計日のﾃﾞｰﾀを集計出力し日次用の直営日次統計表ﾜｰｸを出力する
    -- ------------------------------
    INSERT INTO AW_DailyStatisticsTable (
         ProcessingDate                                             -- 集計日
        ,AccountDate                                                -- 会計月
        ,EnterpriseId                                               -- 加盟店ID
        ,DB__AccountsReceivableBalance                              -- 前日時点未収金残高
        ,D_ChargeCount                                              -- 当日立替件数
        ,D_ChargeAmount                                             -- 当日立替金額
        ,D_CancelCount                                              -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                                             -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount                                      -- 当日立替金戻し件数
        ,D_SettlementBackAmount                                     -- 当日立替金戻し金額
        ,D_OemTransferCount                                         -- 当日OEM移管件数
        ,D_OemTransferAmount                                        -- 当日OEM移管金額
        ,D_ReClaimFeeCount                                          -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                                         -- 当日再発行手数料額
        ,D_DamageCount                                              -- 当日遅延損害金件数
        ,D_DamageAmount                                             -- 当日遅延損害金額
        ,D_ReceiptCount                                             -- 当日入金件数
        ,D_ReceiptAmount                                            -- 当日入金額
        ,D_RepayCount                                               -- 当日返金件数
        ,D_RepayAmount                                              -- 当日返金額
        ,D_BadDebtCount                                             -- 当日貸倒件数
        ,D_BadDebtAmount                                            -- 当日貸倒金額
        ,D_OtherPaymentCount                                        -- 当日その他件数
        ,D_OtherPaymentAmount                                       -- 当日その他金額
        ,D_AccountsReceivableBalance                                -- 当日時点未収金残高
        ,D_SettlementFee                                            -- 当日決済手数料
        ,D_ClaimFee                                                 -- 当日請求手数料
        ,D_ClaimFeeTax                                              -- 当日請求手数料消費税
        ,D_MonthlyFee                                               -- 当日月額固定費
        ,D_MonthlyFeeTax                                            -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                                        -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax                                     -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                                            -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                                         -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee                                   -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax                                -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee                                  -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                                        -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax                                     -- 当日月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙ請求手数料消費税
        ,D_SettlementFeeOther                                       -- 当日売上明細決済手数料
        ,D_ClaimFeeOther                                            -- 当日売上明細請求手数料
        ,D_ClaimFeeTaxOther                                         -- 当日売上明細請求手数料消費税
        ,D_MonthlyFeeOther                                          -- 当日売上明細月額固定費
        ,D_MonthlyFeeTaxOther                                       -- 当日売上明細月額固定費消費税
        ,D_IncludeMonthlyFeeOther                                   -- 当日売上明細同梱月額固定費
        ,D_IncludeMonthlyFeeTaxOther                                -- 当日売上明細同梱月額固定費消費税
        ,D_ApiMonthlyFeeOther                                       -- 当日売上明細API月額固定費
        ,D_ApiMonthlyFeeTaxOther                                    -- 当日売上明細API月額固定費消費税
        ,D_CreditNoticeMonthlyFeeOther                              -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTaxOther                           -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFeeOther                             -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTaxOther                          -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFeeOther                                   -- 当日売上明細月額固定費予備
        ,D_ReserveMonthlyFeeTaxOther                                -- 当日売上明細月額固定費予備消費税
        ,D_AddClaimFeeOther                                         -- 当日手数料明細再発行手数料
        ,D_AddClaimFeeTaxOther                                      -- 当日手数料明細再発行手数料消費税
        ,D_DamageInterestAmountOther                                -- 当日手数料明細遅延損害金
        ,MB__AccountsReceivableBalance                              -- 月初未収金残高
        ,M_ChargeCount                                              -- 当月立替件数
        ,M_ChargeAmount                                             -- 当月立替金額
        ,M_CancelCount                                              -- 当月ｷｬﾝｾﾙ件数
        ,M_CancelAmount                                             -- 当月ｷｬﾝｾﾙ立替金額
        ,M_SettlementBackCount                                      -- 当月立替金戻し件数
        ,M_SettlementBackAmount                                     -- 当月立替金戻し金額
        ,M_TransferCount                                            -- 当月OEM移管件数
        ,M_TransferAmount                                           -- 当月OEM移管金額
        ,M_ReClaimFeeCount                                          -- 当月再発行手数料件数
        ,M_ReClaimFeeAmount                                         -- 当月再発行手数料額
        ,M_DamageCount                                              -- 当月遅延損害金件数
        ,M_DamageAmount                                             -- 当月遅延損害金額
        ,M_ReceiptCount                                             -- 当月入金件数
        ,M_ReceiptAmount                                            -- 当月入金額
        ,M_RepayCount                                               -- 当月返金件数
        ,M_RepayAmount                                              -- 当月返金額
        ,M_BadDebtCount                                             -- 当月貸倒件数
        ,M_BadDebtAmount                                            -- 当月貸倒金額
        ,M_OtherPaymentCount                                        -- 当月その他件数
        ,M_OtherPaymentAmount                                       -- 当月その他金額
        ,M_AccountsReceivableBalance                                -- 当月時点未収金残高
        ,M_SuspensePaymentsAmount                                   -- 消費者未収金明細残高
        ,M_AccountsReceivableBalanceDiff                            -- 当月差額
        ,M_SettlementFee                                            -- 当月決済手数料
        ,M_ClaimFee                                                 -- 当月請求手数料
        ,M_ClaimFeeTax                                              -- 当月請求手数料消費税
        ,M_MonthlyFee                                               -- 当月月額固定費
        ,M_MonthlyFeeTax                                            -- 当月月額固定費消費税
        ,M_IncludeMonthlyFee                                        -- 当月同梱月額固定費
        ,M_IncludeMonthlyFeeTax                                     -- 当月同梱月額固定費消費税
        ,M_ApiMonthlyFee                                            -- 当月API月額固定費
        ,M_ApiMonthlyFeeTax                                         -- 当月API月額固定費消費税
        ,M_CreditNoticeMonthlyFee                                   -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTax                                -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFee                                  -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTax                               -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFee                                        -- 当月月額固定費予備
        ,M_ReserveMonthlyFeeTax                                     -- 当月月額固定費予備消費税
        ,M_AddClaimFee                                              -- 当月再発行手数料
        ,M_AddClaimFeeTax                                           -- 当月再発行手数料消費税
        ,M_DamageInterestAmount                                     -- 当月遅延損害金
        ,M_CanSettlementFee                                         -- 当月ｷｬﾝｾﾙ決済手数料
        ,M_CanClaimFee                                              -- 当月ｷｬﾝｾﾙ請求手数料
        ,M_CanClaimFeeTax                                           -- 当月ｷｬﾝｾﾙ請求手数料消費税
        ,M_SettlementFeeOther                                       -- 当月売上明細決済手数料
        ,M_ClaimFeeOther                                            -- 当月売上明細請求手数料
        ,M_ClaimFeeTaxOther                                         -- 当月売上明細請求手数料消費税
        ,M_MonthlyFeeOther                                          -- 当月売上明細月額固定費
        ,M_MonthlyFeeTaxOther                                       -- 当月売上明細月額固定費消費税
        ,M_IncludeMonthlyFeeOther                                   -- 当月売上明細同梱月額固定費
        ,M_IncludeMonthlyFeeTaxOther                                -- 当月売上明細同梱月額固定費消費税
        ,M_ApiMonthlyFeeOther                                       -- 当月売上明細API月額固定費
        ,M_ApiMonthlyFeeTaxOther                                    -- 当月売上明細API月額固定費消費税
        ,M_CreditNoticeMonthlyFeeOther                              -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTaxOther                           -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFeeOther                             -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTaxOther                          -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFeeOther                                   -- 当月売上明細月額固定費予備
        ,M_ReserveMonthlyFeeTaxOther                                -- 当月売上明細月額固定費予備消費税
        ,M_AddClaimFeeOther                                         -- 当月手数料明細再発行手数料
        ,M_AddClaimFeeTaxOther                                      -- 当月手数料明細再発行手数料消費税
        ,M_DamageInterestAmountOther                                -- 当月手数料明細遅延損害金
        ,RegistDate                                                 -- 登録日時
        ,RegistId                                                   -- 登録者
        ,UpdateDate                                                 -- 更新日時
        ,UpdateId                                                   -- 更新者
        ,ValidFlg                                                   -- 有効ﾌﾗｸﾞ
        )
    SELECT
         null                                                       -- 集計日
        ,null                                                       -- 会計月
        ,EnterpriseId                                               -- 加盟店ID
        ,null                                                       -- 前日時点未収金残高
        ,SUM(IFNULL(D_ChargeCount, 0))                              -- 当日立替件数
        ,SUM(IFNULL(D_ChargeAmount, 0))                             -- 当日立替金額
        ,SUM(IFNULL(D_CancelCount, 0))                              -- 当日ｷｬﾝｾﾙ件数
        ,SUM(IFNULL(D_CancelAmount, 0))                             -- 当日ｷｬﾝｾﾙ立替金額
        ,SUM(IFNULL(D_SettlementBackCount, 0))                      -- 当日立替金戻し件数
        ,SUM(IFNULL(D_SettlementBackAmount, 0))                     -- 当日立替金戻し金額
        ,SUM(IFNULL(D_OemTransferCount, 0))                         -- 当日OEM移管件数
        ,SUM(IFNULL(D_OemTransferAmount, 0))                        -- 当日OEM移管金額
        ,SUM(IFNULL(D_ReClaimFeeCount, 0))                          -- 当日再発行手数料件数
        ,SUM(IFNULL(D_ReClaimFeeAmount, 0))                         -- 当日再発行手数料額
        ,SUM(IFNULL(D_DamageCount, 0))                              -- 当日遅延損害金件数
        ,SUM(IFNULL(D_DamageAmount, 0))                             -- 当日遅延損害金額
        ,SUM(IFNULL(D_ReceiptCount, 0))                             -- 当日入金件数
        ,SUM(IFNULL(D_ReceiptAmount, 0))                            -- 当日入金額
        ,SUM(IFNULL(D_RepayCount, 0))                               -- 当日返金件数
        ,SUM(IFNULL(D_RepayAmount, 0))                              -- 当日返金額
        ,SUM(IFNULL(D_BadDebtCount, 0))                             -- 当日貸倒件数
        ,SUM(IFNULL(D_BadDebtAmount, 0))                            -- 当日貸倒金額
        ,SUM(IFNULL(D_OtherPaymentCount, 0))                        -- 当日その他件数
        ,SUM(IFNULL(D_OtherPaymentAmount, 0))                       -- 当日その他金額
        ,null                                                       -- 当日時点未収金残高
        ,SUM(IFNULL(D_SettlementFee, 0))                            -- 当日決済手数料
        ,SUM(IFNULL(D_ClaimFee, 0))                                 -- 当日請求手数料
        ,SUM(IFNULL(D_ClaimFeeTax, 0))                              -- 当日請求手数料消費税
        ,SUM(IFNULL(D_MonthlyFee, 0))                               -- 当日月額固定費
        ,SUM(IFNULL(D_MonthlyFeeTax, 0))                            -- 当日月額固定費消費税
        ,SUM(IFNULL(D_IncludeMonthlyFee, 0))                        -- 当日同梱月額固定費
        ,SUM(IFNULL(D_IncludeMonthlyFeeTax, 0))                     -- 当日同梱月額固定費消費税
        ,SUM(IFNULL(D_ApiMonthlyFee, 0))                            -- 当日API月額固定費
        ,SUM(IFNULL(D_ApiMonthlyFeeTax, 0))                         -- 当日API月額固定費消費税
        ,SUM(IFNULL(D_CreditNoticeMonthlyFee, 0))                   -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM(IFNULL(D_CreditNoticeMonthlyFeeTax, 0))                -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM(IFNULL(D_NCreditNoticeMonthlyFee, 0))                  -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM(IFNULL(D_NCreditNoticeMonthlyFeeTax, 0))               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM(IFNULL(D_ReserveMonthlyFee, 0))                        -- 当日月額固定費予備
        ,SUM(IFNULL(D_ReserveMonthlyFeeTax, 0))                     -- 当日月額固定費予備消費税
        ,SUM(IFNULL(D_AddClaimFee, 0))                              -- 当日再発行手数料
        ,SUM(IFNULL(D_AddClaimFeeTax, 0))                           -- 当日再発行手数料消費税
        ,SUM(IFNULL(D_DamageInterestAmount, 0))                     -- 当日遅延損害金
        ,SUM(IFNULL(D_CanSettlementFee, 0))                         -- 当日ｷｬﾝｾﾙ決済手数料
        ,SUM(IFNULL(D_CanClaimFee, 0))                              -- 当日ｷｬﾝｾﾙ請求手数料
        ,SUM(IFNULL(D_CanClaimFeeTax, 0))                           -- 当日ｷｬﾝｾﾙ請求手数料消費税
        ,null                                                       -- 当日売上明細決済手数料
        ,null                                                       -- 当日売上明細請求手数料
        ,null                                                       -- 当日売上明細請求手数料消費税
        ,null                                                       -- 当日売上明細月額固定費
        ,null                                                       -- 当日売上明細月額固定費消費税
        ,null                                                       -- 当日売上明細同梱月額固定費
        ,null                                                       -- 当日売上明細同梱月額固定費消費税
        ,null                                                       -- 当日売上明細API月額固定費
        ,null                                                       -- 当日売上明細API月額固定費消費税
        ,null                                                       -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日売上明細月額固定費予備
        ,null                                                       -- 当日売上明細月額固定費予備消費税
        ,null                                                       -- 当日手数料明細再発行手数料
        ,null                                                       -- 当日手数料明細再発行手数料消費税
        ,null                                                       -- 当日手数料明細遅延損害金
        ,null                                                       -- 月初未収金残高
        ,null                                                       -- 当月立替件数
        ,null                                                       -- 当月立替金額
        ,null                                                       -- 当月ｷｬﾝｾﾙ件数
        ,null                                                       -- 当月ｷｬﾝｾﾙ立替金額
        ,null                                                       -- 当月立替金戻し件数
        ,null                                                       -- 当月立替金戻し金額
        ,null                                                       -- 当月OEM移管件数
        ,null                                                       -- 当月OEM移管金額
        ,null                                                       -- 当月再発行手数料件数
        ,null                                                       -- 当月再発行手数料額
        ,null                                                       -- 当月遅延損害金件数
        ,null                                                       -- 当月遅延損害金額
        ,null                                                       -- 当月入金件数
        ,null                                                       -- 当月入金額
        ,null                                                       -- 当月返金件数
        ,null                                                       -- 当月返金額
        ,null                                                       -- 当月貸倒件数
        ,null                                                       -- 当月貸倒金額
        ,null                                                       -- 当月その他件数
        ,null                                                       -- 当月その他金額
        ,null                                                       -- 当月時点未収金残高
        ,null                                                       -- 消費者未収金明細残高
        ,null                                                       -- 当月差額
        ,null                                                       -- 当月決済手数料
        ,null                                                       -- 当月請求手数料
        ,null                                                       -- 当月請求手数料消費税
        ,null                                                       -- 当月月額固定費
        ,null                                                       -- 当月月額固定費消費税
        ,null                                                       -- 当月同梱月額固定費
        ,null                                                       -- 当月同梱月額固定費消費税
        ,null                                                       -- 当月API月額固定費
        ,null                                                       -- 当月API月額固定費消費税
        ,null                                                       -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月月額固定費予備
        ,null                                                       -- 当月月額固定費予備消費税
        ,null                                                       -- 当月再発行手数料
        ,null                                                       -- 当月再発行手数料消費税
        ,null                                                       -- 当月遅延損害金
        ,null                                                       -- 当月ｷｬﾝｾﾙ決済手数料
        ,null                                                       -- 当月ｷｬﾝｾﾙ請求手数料
        ,null                                                       -- 当月ｷｬﾝｾﾙ請求手数料消費税
        ,null                                                       -- 当月売上明細決済手数料
        ,null                                                       -- 当月売上明細請求手数料
        ,null                                                       -- 当月売上明細請求手数料消費税
        ,null                                                       -- 当月売上明細月額固定費
        ,null                                                       -- 当月売上明細月額固定費消費税
        ,null                                                       -- 当月売上明細同梱月額固定費
        ,null                                                       -- 当月売上明細同梱月額固定費消費税
        ,null                                                       -- 当月売上明細API月額固定費
        ,null                                                       -- 当月売上明細API月額固定費消費税
        ,null                                                       -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細月額固定費予備
        ,null                                                       -- 当月売上明細月額固定費予備消費税
        ,null                                                       -- 当月手数料明細再発行手数料
        ,null                                                       -- 当月手数料明細再発行手数料消費税
        ,null                                                       -- 当月手数料明細遅延損害金
        ,NOW()                                                      -- 登録日時
        ,pi_user_id                                                 -- 登録者
        ,NOW()                                                      -- 更新日時
        ,pi_user_id                                                 -- 更新者
        ,1                                                          -- 有効ﾌﾗｸﾞ
    FROM    AT_Daily2StatisticsTable
    WHERE   DATE_ADD(v_AccountingMonth, INTERVAL 1 MONTH) = AccountDate
    AND     v_BusinessDate = ProcessingDate
    GROUP BY AccountDate
            ,EnterpriseId;

    -- ------------------------------
    -- 3.3.会計月の翌月=日別直営日次統計表.会計月のﾃﾞｰﾀを集計出力し日次用の直営日次統計表ﾜｰｸを出力する
    -- ------------------------------
    INSERT INTO AW_DailyStatisticsTable (
         ProcessingDate                                             -- 集計日
        ,AccountDate                                                -- 会計月
        ,EnterpriseId                                               -- 加盟店ID
        ,DB__AccountsReceivableBalance                              -- 前日時点未収金残高
        ,D_ChargeCount                                              -- 当日立替件数
        ,D_ChargeAmount                                             -- 当日立替金額
        ,D_CancelCount                                              -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                                             -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount                                      -- 当日立替金戻し件数
        ,D_SettlementBackAmount                                     -- 当日立替金戻し金額
        ,D_OemTransferCount                                         -- 当日OEM移管件数
        ,D_OemTransferAmount                                        -- 当日OEM移管金額
        ,D_ReClaimFeeCount                                          -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                                         -- 当日再発行手数料額
        ,D_DamageCount                                              -- 当日遅延損害金件数
        ,D_DamageAmount                                             -- 当日遅延損害金額
        ,D_ReceiptCount                                             -- 当日入金件数
        ,D_ReceiptAmount                                            -- 当日入金額
        ,D_RepayCount                                               -- 当日返金件数
        ,D_RepayAmount                                              -- 当日返金額
        ,D_BadDebtCount                                             -- 当日貸倒件数
        ,D_BadDebtAmount                                            -- 当日貸倒金額
        ,D_OtherPaymentCount                                        -- 当日その他件数
        ,D_OtherPaymentAmount                                       -- 当日その他金額
        ,D_AccountsReceivableBalance                                -- 当日時点未収金残高
        ,D_SettlementFee                                            -- 当日決済手数料
        ,D_ClaimFee                                                 -- 当日請求手数料
        ,D_ClaimFeeTax                                              -- 当日請求手数料消費税
        ,D_MonthlyFee                                               -- 当日月額固定費
        ,D_MonthlyFeeTax                                            -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                                        -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax                                     -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                                            -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                                         -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee                                   -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax                                -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee                                  -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                                        -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax                                     -- 当日月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙ請求手数料消費税
        ,D_SettlementFeeOther                                       -- 当日売上明細決済手数料
        ,D_ClaimFeeOther                                            -- 当日売上明細請求手数料
        ,D_ClaimFeeTaxOther                                         -- 当日売上明細請求手数料消費税
        ,D_MonthlyFeeOther                                          -- 当日売上明細月額固定費
        ,D_MonthlyFeeTaxOther                                       -- 当日売上明細月額固定費消費税
        ,D_IncludeMonthlyFeeOther                                   -- 当日売上明細同梱月額固定費
        ,D_IncludeMonthlyFeeTaxOther                                -- 当日売上明細同梱月額固定費消費税
        ,D_ApiMonthlyFeeOther                                       -- 当日売上明細API月額固定費
        ,D_ApiMonthlyFeeTaxOther                                    -- 当日売上明細API月額固定費消費税
        ,D_CreditNoticeMonthlyFeeOther                              -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTaxOther                           -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFeeOther                             -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTaxOther                          -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFeeOther                                   -- 当日売上明細月額固定費予備
        ,D_ReserveMonthlyFeeTaxOther                                -- 当日売上明細月額固定費予備消費税
        ,D_AddClaimFeeOther                                         -- 当日手数料明細再発行手数料
        ,D_AddClaimFeeTaxOther                                      -- 当日手数料明細再発行手数料消費税
        ,D_DamageInterestAmountOther                                -- 当日手数料明細遅延損害金
        ,MB__AccountsReceivableBalance                              -- 月初未収金残高
        ,M_ChargeCount                                              -- 当月立替件数
        ,M_ChargeAmount                                             -- 当月立替金額
        ,M_CancelCount                                              -- 当月ｷｬﾝｾﾙ件数
        ,M_CancelAmount                                             -- 当月ｷｬﾝｾﾙ立替金額
        ,M_SettlementBackCount                                      -- 当月立替金戻し件数
        ,M_SettlementBackAmount                                     -- 当月立替金戻し金額
        ,M_TransferCount                                            -- 当月OEM移管件数
        ,M_TransferAmount                                           -- 当月OEM移管金額
        ,M_ReClaimFeeCount                                          -- 当月再発行手数料件数
        ,M_ReClaimFeeAmount                                         -- 当月再発行手数料額
        ,M_DamageCount                                              -- 当月遅延損害金件数
        ,M_DamageAmount                                             -- 当月遅延損害金額
        ,M_ReceiptCount                                             -- 当月入金件数
        ,M_ReceiptAmount                                            -- 当月入金額
        ,M_RepayCount                                               -- 当月返金件数
        ,M_RepayAmount                                              -- 当月返金額
        ,M_BadDebtCount                                             -- 当月貸倒件数
        ,M_BadDebtAmount                                            -- 当月貸倒金額
        ,M_OtherPaymentCount                                        -- 当月その他件数
        ,M_OtherPaymentAmount                                       -- 当月その他金額
        ,M_AccountsReceivableBalance                                -- 当月時点未収金残高
        ,M_SuspensePaymentsAmount                                   -- 消費者未収金明細残高
        ,M_AccountsReceivableBalanceDiff                            -- 当月差額
        ,M_SettlementFee                                            -- 当月決済手数料
        ,M_ClaimFee                                                 -- 当月請求手数料
        ,M_ClaimFeeTax                                              -- 当月請求手数料消費税
        ,M_MonthlyFee                                               -- 当月月額固定費
        ,M_MonthlyFeeTax                                            -- 当月月額固定費消費税
        ,M_IncludeMonthlyFee                                        -- 当月同梱月額固定費
        ,M_IncludeMonthlyFeeTax                                     -- 当月同梱月額固定費消費税
        ,M_ApiMonthlyFee                                            -- 当月API月額固定費
        ,M_ApiMonthlyFeeTax                                         -- 当月API月額固定費消費税
        ,M_CreditNoticeMonthlyFee                                   -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTax                                -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFee                                  -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTax                               -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFee                                        -- 当月月額固定費予備
        ,M_ReserveMonthlyFeeTax                                     -- 当月月額固定費予備消費税
        ,M_AddClaimFee                                              -- 当月再発行手数料
        ,M_AddClaimFeeTax                                           -- 当月再発行手数料消費税
        ,M_DamageInterestAmount                                     -- 当月遅延損害金
        ,M_CanSettlementFee                                         -- 当月ｷｬﾝｾﾙ決済手数料
        ,M_CanClaimFee                                              -- 当月ｷｬﾝｾﾙ請求手数料
        ,M_CanClaimFeeTax                                           -- 当月ｷｬﾝｾﾙ請求手数料消費税
        ,M_SettlementFeeOther                                       -- 当月売上明細決済手数料
        ,M_ClaimFeeOther                                            -- 当月売上明細請求手数料
        ,M_ClaimFeeTaxOther                                         -- 当月売上明細請求手数料消費税
        ,M_MonthlyFeeOther                                          -- 当月売上明細月額固定費
        ,M_MonthlyFeeTaxOther                                       -- 当月売上明細月額固定費消費税
        ,M_IncludeMonthlyFeeOther                                   -- 当月売上明細同梱月額固定費
        ,M_IncludeMonthlyFeeTaxOther                                -- 当月売上明細同梱月額固定費消費税
        ,M_ApiMonthlyFeeOther                                       -- 当月売上明細API月額固定費
        ,M_ApiMonthlyFeeTaxOther                                    -- 当月売上明細API月額固定費消費税
        ,M_CreditNoticeMonthlyFeeOther                              -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTaxOther                           -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFeeOther                             -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTaxOther                          -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFeeOther                                   -- 当月売上明細月額固定費予備
        ,M_ReserveMonthlyFeeTaxOther                                -- 当月売上明細月額固定費予備消費税
        ,M_AddClaimFeeOther                                         -- 当月手数料明細再発行手数料
        ,M_AddClaimFeeTaxOther                                      -- 当月手数料明細再発行手数料消費税
        ,M_DamageInterestAmountOther                                -- 当月手数料明細遅延損害金
        ,RegistDate                                                 -- 登録日時
        ,RegistId                                                   -- 登録者
        ,UpdateDate                                                 -- 更新日時
        ,UpdateId                                                   -- 更新者
        ,ValidFlg                                                   -- 有効ﾌﾗｸﾞ
        )
    SELECT
         null                                                       -- 集計日
        ,null                                                       -- 会計月
        ,EnterpriseId                                               -- 加盟店ID
        ,null                                                       -- 前日時点未収金残高
        ,null                                                       -- 当日立替件数
        ,null                                                       -- 当日立替金額
        ,null                                                       -- 当日ｷｬﾝｾﾙ件数
        ,null                                                       -- 当日ｷｬﾝｾﾙ立替金額
        ,null                                                       -- 当日立替金戻し件数
        ,null                                                       -- 当日立替金戻し金額
        ,null                                                       -- 当日OEM移管件数
        ,null                                                       -- 当日OEM移管金額
        ,null                                                       -- 当日再発行手数料件数
        ,null                                                       -- 当日再発行手数料額
        ,null                                                       -- 当日遅延損害金件数
        ,null                                                       -- 当日遅延損害金額
        ,null                                                       -- 当日入金件数
        ,null                                                       -- 当日入金額
        ,null                                                       -- 当日返金件数
        ,null                                                       -- 当日返金額
        ,null                                                       -- 当日貸倒件数
        ,null                                                       -- 当日貸倒金額
        ,null                                                       -- 当日その他件数
        ,null                                                       -- 当日その他金額
        ,null                                                       -- 当日時点未収金残高
        ,null                                                       -- 当日決済手数料
        ,null                                                       -- 当日請求手数料
        ,null                                                       -- 当日請求手数料消費税
        ,null                                                       -- 当日月額固定費
        ,null                                                       -- 当日月額固定費消費税
        ,null                                                       -- 当日同梱月額固定費
        ,null                                                       -- 当日同梱月額固定費消費税
        ,null                                                       -- 当日API月額固定費
        ,null                                                       -- 当日API月額固定費消費税
        ,null                                                       -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日月額固定費予備
        ,null                                                       -- 当日月額固定費予備消費税
        ,null                                                       -- 当日再発行手数料
        ,null                                                       -- 当日再発行手数料消費税
        ,null                                                       -- 当日遅延損害金
        ,null                                                       -- 当日ｷｬﾝｾﾙ決済手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙ請求手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙ請求手数料消費税
        ,null                                                       -- 当日売上明細決済手数料
        ,null                                                       -- 当日売上明細請求手数料
        ,null                                                       -- 当日売上明細請求手数料消費税
        ,null                                                       -- 当日売上明細月額固定費
        ,null                                                       -- 当日売上明細月額固定費消費税
        ,null                                                       -- 当日売上明細同梱月額固定費
        ,null                                                       -- 当日売上明細同梱月額固定費消費税
        ,null                                                       -- 当日売上明細API月額固定費
        ,null                                                       -- 当日売上明細API月額固定費消費税
        ,null                                                       -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日売上明細月額固定費予備
        ,null                                                       -- 当日売上明細月額固定費予備消費税
        ,null                                                       -- 当日手数料明細再発行手数料
        ,null                                                       -- 当日手数料明細再発行手数料消費税
        ,null                                                       -- 当日手数料明細遅延損害金
        ,null                                                       -- 月初未収金残高
        ,SUM(IFNULL(D_ChargeCount, 0))                              -- 当月立替件数
        ,SUM(IFNULL(D_ChargeAmount, 0))                             -- 当月立替金額
        ,SUM(IFNULL(D_CancelCount, 0))                              -- 当月ｷｬﾝｾﾙ件数
        ,SUM(IFNULL(D_CancelAmount, 0))                             -- 当月ｷｬﾝｾﾙ立替金額
        ,SUM(IFNULL(D_SettlementBackCount, 0))                      -- 当月立替金戻し件数
        ,SUM(IFNULL(D_SettlementBackAmount, 0))                     -- 当月立替金戻し金額
        ,SUM(IFNULL(D_OemTransferCount, 0))                         -- 当月OEM移管件数
        ,SUM(IFNULL(D_OemTransferAmount, 0))                        -- 当月OEM移管金額
        ,SUM(IFNULL(D_ReClaimFeeCount, 0))                          -- 当月再発行手数料件数
        ,SUM(IFNULL(D_ReClaimFeeAmount, 0))                         -- 当月再発行手数料額
        ,SUM(IFNULL(D_DamageCount, 0))                              -- 当月遅延損害金件数
        ,SUM(IFNULL(D_DamageAmount, 0))                             -- 当月遅延損害金額
        ,SUM(IFNULL(D_ReceiptCount, 0))                             -- 当月入金件数
        ,SUM(IFNULL(D_ReceiptAmount, 0))                            -- 当月入金額
        ,SUM(IFNULL(D_RepayCount, 0))                               -- 当月返金件数
        ,SUM(IFNULL(D_RepayAmount, 0))                              -- 当月返金額
        ,SUM(IFNULL(D_BadDebtCount, 0))                             -- 当月貸倒件数
        ,SUM(IFNULL(D_BadDebtAmount, 0))                            -- 当月貸倒金額
        ,SUM(IFNULL(D_OtherPaymentCount, 0))                        -- 当月その他件数
        ,SUM(IFNULL(D_OtherPaymentAmount, 0))                       -- 当月その他金額
        ,null                                                       -- 当月時点未収金残高
        ,null                                                       -- 消費者未収金明細残高
        ,null                                                       -- 当月差額
        ,SUM(IFNULL(D_SettlementFee, 0))                            -- 当月決済手数料
        ,SUM(IFNULL(D_ClaimFee, 0))                                 -- 当月請求手数料
        ,SUM(IFNULL(D_ClaimFeeTax, 0))                              -- 当月請求手数料消費税
        ,SUM(IFNULL(D_MonthlyFee, 0))                               -- 当月月額固定費
        ,SUM(IFNULL(D_MonthlyFeeTax, 0))                            -- 当月月額固定費消費税
        ,SUM(IFNULL(D_IncludeMonthlyFee, 0))                        -- 当月同梱月額固定費
        ,SUM(IFNULL(D_IncludeMonthlyFeeTax, 0))                     -- 当月同梱月額固定費消費税
        ,SUM(IFNULL(D_ApiMonthlyFee, 0))                            -- 当月API月額固定費
        ,SUM(IFNULL(D_ApiMonthlyFeeTax, 0))                         -- 当月API月額固定費消費税
        ,SUM(IFNULL(D_CreditNoticeMonthlyFee, 0))                   -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM(IFNULL(D_CreditNoticeMonthlyFeeTax, 0))                -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM(IFNULL(D_NCreditNoticeMonthlyFee, 0))                  -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM(IFNULL(D_NCreditNoticeMonthlyFeeTax, 0))               -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM(IFNULL(D_ReserveMonthlyFee, 0))                        -- 当月月額固定費予備
        ,SUM(IFNULL(D_ReserveMonthlyFeeTax, 0))                     -- 当月月額固定費予備消費税
        ,SUM(IFNULL(D_AddClaimFee, 0))                              -- 当月再発行手数料
        ,SUM(IFNULL(D_AddClaimFeeTax, 0))                           -- 当月再発行手数料消費税
        ,SUM(IFNULL(D_DamageInterestAmount, 0))                     -- 当月遅延損害金
        ,SUM(IFNULL(D_CanSettlementFee, 0))                         -- 当月ｷｬﾝｾﾙ決済手数料
        ,SUM(IFNULL(D_CanClaimFee, 0))                              -- 当月ｷｬﾝｾﾙ請求手数料
        ,SUM(IFNULL(D_CanClaimFeeTax, 0))                           -- 当月ｷｬﾝｾﾙ請求手数料消費税
        ,null                                                       -- 当月売上明細決済手数料
        ,null                                                       -- 当月売上明細請求手数料
        ,null                                                       -- 当月売上明細請求手数料消費税
        ,null                                                       -- 当月売上明細月額固定費
        ,null                                                       -- 当月売上明細月額固定費消費税
        ,null                                                       -- 当月売上明細同梱月額固定費
        ,null                                                       -- 当月売上明細同梱月額固定費消費税
        ,null                                                       -- 当月売上明細API月額固定費
        ,null                                                       -- 当月売上明細API月額固定費消費税
        ,null                                                       -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細月額固定費予備
        ,null                                                       -- 当月売上明細月額固定費予備消費税
        ,null                                                       -- 当月手数料明細再発行手数料
        ,null                                                       -- 当月手数料明細再発行手数料消費税
        ,null                                                       -- 当月手数料明細遅延損害金
        ,NOW()                                                      -- 登録日時
        ,pi_user_id                                                 -- 登録者
        ,NOW()                                                      -- 更新日時
        ,pi_user_id                                                 -- 更新者
        ,1                                                          -- 有効ﾌﾗｸﾞ
    FROM    AT_Daily2StatisticsTable
    WHERE   DATE_ADD( v_AccountingMonth, INTERVAL 1 MONTH ) = AccountDate
    AND     v_BusinessDate = ProcessingDate
    GROUP BY ProcessingDate
            ,AccountDate
            ,EnterpriseId;

    -- ------------------------------
    -- 3.4.加盟店別に業務日付>直営日次統計表.集計日、且つ、MAX（直営日次統計表.集計日）【前日】の
    --     ﾃﾞｰﾀを出力し日次用の直営日次統計表ﾜｰｸを出力する
    -- ------------------------------
    INSERT INTO AW_DailyStatisticsTable (
         ProcessingDate                                             -- 集計日
        ,AccountDate                                                -- 会計月
        ,EnterpriseId                                               -- 加盟店ID
        ,DB__AccountsReceivableBalance                              -- 前日時点未収金残高
        ,D_ChargeCount                                              -- 当日立替件数
        ,D_ChargeAmount                                             -- 当日立替金額
        ,D_CancelCount                                              -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                                             -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount                                      -- 当日立替金戻し件数
        ,D_SettlementBackAmount                                     -- 当日立替金戻し金額
        ,D_OemTransferCount                                         -- 当日OEM移管件数
        ,D_OemTransferAmount                                        -- 当日OEM移管金額
        ,D_ReClaimFeeCount                                          -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                                         -- 当日再発行手数料額
        ,D_DamageCount                                              -- 当日遅延損害金件数
        ,D_DamageAmount                                             -- 当日遅延損害金額
        ,D_ReceiptCount                                             -- 当日入金件数
        ,D_ReceiptAmount                                            -- 当日入金額
        ,D_RepayCount                                               -- 当日返金件数
        ,D_RepayAmount                                              -- 当日返金額
        ,D_BadDebtCount                                             -- 当日貸倒件数
        ,D_BadDebtAmount                                            -- 当日貸倒金額
        ,D_OtherPaymentCount                                        -- 当日その他件数
        ,D_OtherPaymentAmount                                       -- 当日その他金額
        ,D_AccountsReceivableBalance                                -- 当日時点未収金残高
        ,D_SettlementFee                                            -- 当日決済手数料
        ,D_ClaimFee                                                 -- 当日請求手数料
        ,D_ClaimFeeTax                                              -- 当日請求手数料消費税
        ,D_MonthlyFee                                               -- 当日月額固定費
        ,D_MonthlyFeeTax                                            -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                                        -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax                                     -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                                            -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                                         -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee                                   -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax                                -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee                                  -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                                        -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax                                     -- 当日月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙ請求手数料消費税
        ,D_SettlementFeeOther                                       -- 当日売上明細決済手数料
        ,D_ClaimFeeOther                                            -- 当日売上明細請求手数料
        ,D_ClaimFeeTaxOther                                         -- 当日売上明細請求手数料消費税
        ,D_MonthlyFeeOther                                          -- 当日売上明細月額固定費
        ,D_MonthlyFeeTaxOther                                       -- 当日売上明細月額固定費消費税
        ,D_IncludeMonthlyFeeOther                                   -- 当日売上明細同梱月額固定費
        ,D_IncludeMonthlyFeeTaxOther                                -- 当日売上明細同梱月額固定費消費税
        ,D_ApiMonthlyFeeOther                                       -- 当日売上明細API月額固定費
        ,D_ApiMonthlyFeeTaxOther                                    -- 当日売上明細API月額固定費消費税
        ,D_CreditNoticeMonthlyFeeOther                              -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTaxOther                           -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFeeOther                             -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTaxOther                          -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFeeOther                                   -- 当日売上明細月額固定費予備
        ,D_ReserveMonthlyFeeTaxOther                                -- 当日売上明細月額固定費予備消費税
        ,D_AddClaimFeeOther                                         -- 当日手数料明細再発行手数料
        ,D_AddClaimFeeTaxOther                                      -- 当日手数料明細再発行手数料消費税
        ,D_DamageInterestAmountOther                                -- 当日手数料明細遅延損害金
        ,MB__AccountsReceivableBalance                              -- 月初未収金残高
        ,M_ChargeCount                                              -- 当月立替件数
        ,M_ChargeAmount                                             -- 当月立替金額
        ,M_CancelCount                                              -- 当月ｷｬﾝｾﾙ件数
        ,M_CancelAmount                                             -- 当月ｷｬﾝｾﾙ立替金額
        ,M_SettlementBackCount                                      -- 当月立替金戻し件数
        ,M_SettlementBackAmount                                     -- 当月立替金戻し金額
        ,M_TransferCount                                            -- 当月OEM移管件数
        ,M_TransferAmount                                           -- 当月OEM移管金額
        ,M_ReClaimFeeCount                                          -- 当月再発行手数料件数
        ,M_ReClaimFeeAmount                                         -- 当月再発行手数料額
        ,M_DamageCount                                              -- 当月遅延損害金件数
        ,M_DamageAmount                                             -- 当月遅延損害金額
        ,M_ReceiptCount                                             -- 当月入金件数
        ,M_ReceiptAmount                                            -- 当月入金額
        ,M_RepayCount                                               -- 当月返金件数
        ,M_RepayAmount                                              -- 当月返金額
        ,M_BadDebtCount                                             -- 当月貸倒件数
        ,M_BadDebtAmount                                            -- 当月貸倒金額
        ,M_OtherPaymentCount                                        -- 当月その他件数
        ,M_OtherPaymentAmount                                       -- 当月その他金額
        ,M_AccountsReceivableBalance                                -- 当月時点未収金残高
        ,M_SuspensePaymentsAmount                                   -- 消費者未収金明細残高
        ,M_AccountsReceivableBalanceDiff                            -- 当月差額
        ,M_SettlementFee                                            -- 当月決済手数料
        ,M_ClaimFee                                                 -- 当月請求手数料
        ,M_ClaimFeeTax                                              -- 当月請求手数料消費税
        ,M_MonthlyFee                                               -- 当月月額固定費
        ,M_MonthlyFeeTax                                            -- 当月月額固定費消費税
        ,M_IncludeMonthlyFee                                        -- 当月同梱月額固定費
        ,M_IncludeMonthlyFeeTax                                     -- 当月同梱月額固定費消費税
        ,M_ApiMonthlyFee                                            -- 当月API月額固定費
        ,M_ApiMonthlyFeeTax                                         -- 当月API月額固定費消費税
        ,M_CreditNoticeMonthlyFee                                   -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTax                                -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFee                                  -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTax                               -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFee                                        -- 当月月額固定費予備
        ,M_ReserveMonthlyFeeTax                                     -- 当月月額固定費予備消費税
        ,M_AddClaimFee                                              -- 当月再発行手数料
        ,M_AddClaimFeeTax                                           -- 当月再発行手数料消費税
        ,M_DamageInterestAmount                                     -- 当月遅延損害金
        ,M_CanSettlementFee                                         -- 当月ｷｬﾝｾﾙ決済手数料
        ,M_CanClaimFee                                              -- 当月ｷｬﾝｾﾙ請求手数料
        ,M_CanClaimFeeTax                                           -- 当月ｷｬﾝｾﾙ請求手数料消費税
        ,M_SettlementFeeOther                                       -- 当月売上明細決済手数料
        ,M_ClaimFeeOther                                            -- 当月売上明細請求手数料
        ,M_ClaimFeeTaxOther                                         -- 当月売上明細請求手数料消費税
        ,M_MonthlyFeeOther                                          -- 当月売上明細月額固定費
        ,M_MonthlyFeeTaxOther                                       -- 当月売上明細月額固定費消費税
        ,M_IncludeMonthlyFeeOther                                   -- 当月売上明細同梱月額固定費
        ,M_IncludeMonthlyFeeTaxOther                                -- 当月売上明細同梱月額固定費消費税
        ,M_ApiMonthlyFeeOther                                       -- 当月売上明細API月額固定費
        ,M_ApiMonthlyFeeTaxOther                                    -- 当月売上明細API月額固定費消費税
        ,M_CreditNoticeMonthlyFeeOther                              -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTaxOther                           -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFeeOther                             -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTaxOther                          -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFeeOther                                   -- 当月売上明細月額固定費予備
        ,M_ReserveMonthlyFeeTaxOther                                -- 当月売上明細月額固定費予備消費税
        ,M_AddClaimFeeOther                                         -- 当月手数料明細再発行手数料
        ,M_AddClaimFeeTaxOther                                      -- 当月手数料明細再発行手数料消費税
        ,M_DamageInterestAmountOther                                -- 当月手数料明細遅延損害金
        ,RegistDate                                                 -- 登録日時
        ,RegistId                                                   -- 登録者
        ,UpdateDate                                                 -- 更新日時
        ,UpdateId                                                   -- 更新者
        ,ValidFlg                                                   -- 有効ﾌﾗｸﾞ
        )
    SELECT
         null                                                       -- 集計日
        ,null                                                       -- 会計月
        ,dst.EnterpriseId                                           -- 加盟店ID
        ,dst.D_AccountsReceivableBalance                            -- 前日時点未収金残高
        ,null                                                       -- 当日立替件数
        ,null                                                       -- 当日立替金額
        ,null                                                       -- 当日ｷｬﾝｾﾙ件数
        ,null                                                       -- 当日ｷｬﾝｾﾙ立替金額
        ,null                                                       -- 当日立替金戻し件数
        ,null                                                       -- 当日立替金戻し金額
        ,null                                                       -- 当日OEM移管件数
        ,null                                                       -- 当日OEM移管金額
        ,null                                                       -- 当日再発行手数料件数
        ,null                                                       -- 当日再発行手数料額
        ,null                                                       -- 当日遅延損害金件数
        ,null                                                       -- 当日遅延損害金額
        ,null                                                       -- 当日入金件数
        ,null                                                       -- 当日入金額
        ,null                                                       -- 当日返金件数
        ,null                                                       -- 当日返金額
        ,null                                                       -- 当日貸倒件数
        ,null                                                       -- 当日貸倒金額
        ,null                                                       -- 当日その他件数
        ,null                                                       -- 当日その他金額
        ,null                                                       -- 当日時点未収金残高
        ,null                                                       -- 当日決済手数料
        ,null                                                       -- 当日請求手数料
        ,null                                                       -- 当日請求手数料消費税
        ,null                                                       -- 当日月額固定費
        ,null                                                       -- 当日月額固定費消費税
        ,null                                                       -- 当日同梱月額固定費
        ,null                                                       -- 当日同梱月額固定費消費税
        ,null                                                       -- 当日API月額固定費
        ,null                                                       -- 当日API月額固定費消費税
        ,null                                                       -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日月額固定費予備
        ,null                                                       -- 当日月額固定費予備消費税
        ,null                                                       -- 当日再発行手数料
        ,null                                                       -- 当日再発行手数料消費税
        ,null                                                       -- 当日遅延損害金
        ,null                                                       -- 当日ｷｬﾝｾﾙ決済手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙ請求手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙ請求手数料消費税
        ,null                                                       -- 当日売上明細決済手数料
        ,null                                                       -- 当日売上明細請求手数料
        ,null                                                       -- 当日売上明細請求手数料消費税
        ,null                                                       -- 当日売上明細月額固定費
        ,null                                                       -- 当日売上明細月額固定費消費税
        ,null                                                       -- 当日売上明細同梱月額固定費
        ,null                                                       -- 当日売上明細同梱月額固定費消費税
        ,null                                                       -- 当日売上明細API月額固定費
        ,null                                                       -- 当日売上明細API月額固定費消費税
        ,null                                                       -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日売上明細月額固定費予備
        ,null                                                       -- 当日売上明細月額固定費予備消費税
        ,null                                                       -- 当日手数料明細再発行手数料
        ,null                                                       -- 当日手数料明細再発行手数料消費税
        ,null                                                       -- 当日手数料明細遅延損害金
        ,null                                                       -- 月初未収金残高
        ,null                                                       -- 当月立替件数
        ,null                                                       -- 当月立替金額
        ,null                                                       -- 当月ｷｬﾝｾﾙ件数
        ,null                                                       -- 当月ｷｬﾝｾﾙ立替金額
        ,null                                                       -- 当月立替金戻し件数
        ,null                                                       -- 当月立替金戻し金額
        ,null                                                       -- 当月OEM移管件数
        ,null                                                       -- 当月OEM移管金額
        ,null                                                       -- 当月再発行手数料件数
        ,null                                                       -- 当月再発行手数料額
        ,null                                                       -- 当月遅延損害金件数
        ,null                                                       -- 当月遅延損害金額
        ,null                                                       -- 当月入金件数
        ,null                                                       -- 当月入金額
        ,null                                                       -- 当月返金件数
        ,null                                                       -- 当月返金額
        ,null                                                       -- 当月貸倒件数
        ,null                                                       -- 当月貸倒金額
        ,null                                                       -- 当月その他件数
        ,null                                                       -- 当月その他金額
        ,null                                                       -- 当月時点未収金残高
        ,null                                                       -- 消費者未収金明細残高
        ,null                                                       -- 当月差額
        ,null                                                       -- 当月決済手数料
        ,null                                                       -- 当月請求手数料
        ,null                                                       -- 当月請求手数料消費税
        ,null                                                       -- 当月月額固定費
        ,null                                                       -- 当月月額固定費消費税
        ,null                                                       -- 当月同梱月額固定費
        ,null                                                       -- 当月同梱月額固定費消費税
        ,null                                                       -- 当月API月額固定費
        ,null                                                       -- 当月API月額固定費消費税
        ,null                                                       -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月月額固定費予備
        ,null                                                       -- 当月月額固定費予備消費税
        ,null                                                       -- 当月再発行手数料
        ,null                                                       -- 当月再発行手数料消費税
        ,null                                                       -- 当月遅延損害金
        ,null                                                       -- 当月ｷｬﾝｾﾙ決済手数料
        ,null                                                       -- 当月ｷｬﾝｾﾙ請求手数料
        ,null                                                       -- 当月ｷｬﾝｾﾙ請求手数料消費税
        ,null                                                       -- 当月売上明細決済手数料
        ,null                                                       -- 当月売上明細請求手数料
        ,null                                                       -- 当月売上明細請求手数料消費税
        ,null                                                       -- 当月売上明細月額固定費
        ,null                                                       -- 当月売上明細月額固定費消費税
        ,null                                                       -- 当月売上明細同梱月額固定費
        ,null                                                       -- 当月売上明細同梱月額固定費消費税
        ,null                                                       -- 当月売上明細API月額固定費
        ,null                                                       -- 当月売上明細API月額固定費消費税
        ,null                                                       -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細月額固定費予備
        ,null                                                       -- 当月売上明細月額固定費予備消費税
        ,null                                                       -- 当月手数料明細再発行手数料
        ,null                                                       -- 当月手数料明細再発行手数料消費税
        ,null                                                       -- 当月手数料明細遅延損害金
        ,NOW()                                                      -- 登録日時
        ,pi_user_id                                                 -- 登録者
        ,NOW()                                                      -- 更新日時
        ,pi_user_id                                                 -- 更新者
        ,1                                                          -- 有効ﾌﾗｸﾞ
    FROM  AT_DailyStatisticsTable dst
    INNER JOIN ( SELECT EnterpriseId, MAX( ProcessingDate ) ProcessingDate FROM AT_DailyStatisticsTable
                 WHERE v_BusinessDate      > ProcessingDate
                 AND   DATE_ADD( v_AccountingMonth, INTERVAL 1 MONTH )   = AccountDate
                 AND   DailyMonthlyFlg     = 0
                 GROUP BY EnterpriseId ) AS dstw
    ON    dst.EnterpriseId = dstw.EnterpriseId
    AND   dst.ProcessingDate = dstw.ProcessingDate
    AND   dst.DailyMonthlyFlg = 0
    AND   dst.AccountDate = DATE_ADD( v_AccountingMonth, INTERVAL 1 MONTH );

    -- ------------------------------
    -- 3.6.直営日次統計表ﾜｰｸのﾃﾞｰﾀを加盟店別に集計出力し日次用の直営日次統計表を出力する
    -- ------------------------------
    INSERT INTO AT_DailyStatisticsTable (
         DailyMonthlyFlg                                            -- 日次･月次区分
        ,ProcessingDate                                             -- 集計日
        ,AccountDate                                                -- 会計月
        ,EnterpriseId                                               -- 加盟店ID
        ,EnterpriseNameKj                                           -- 加盟店名
        ,DB__AccountsReceivableBalance                              -- 前日時点未収金残高
        ,D_ChargeCount                                              -- 当日立替件数
        ,D_ChargeAmount                                             -- 当日立替金額
        ,D_CancelCount                                              -- 当日ｷｬﾝｾﾙ件数
        ,D_CancelAmount                                             -- 当日ｷｬﾝｾﾙ立替金額
        ,D_SettlementBackCount                                      -- 当日立替金戻し件数
        ,D_SettlementBackAmount                                     -- 当日立替金戻し金額
        ,D_OemTransferCount                                         -- 当日OEM移管件数
        ,D_OemTransferAmount                                        -- 当日OEM移管金額
        ,D_ReClaimFeeCount                                          -- 当日再発行手数料件数
        ,D_ReClaimFeeAmount                                         -- 当日再発行手数料額
        ,D_DamageCount                                              -- 当日遅延損害金件数
        ,D_DamageAmount                                             -- 当日遅延損害金額
        ,D_ReceiptCount                                             -- 当日入金件数
        ,D_ReceiptAmount                                            -- 当日入金額
        ,D_RepayCount                                               -- 当日返金件数
        ,D_RepayAmount                                              -- 当日返金額
        ,D_BadDebtCount                                             -- 当日貸倒件数
        ,D_BadDebtAmount                                            -- 当日貸倒金額
        ,D_OtherPaymentCount                                        -- 当日その他件数
        ,D_OtherPaymentAmount                                       -- 当日その他金額
        ,D_AccountsReceivableBalance                                -- 当日時点未収金残高
        ,D_SettlementFee                                            -- 当日決済手数料
        ,D_ClaimFee                                                 -- 当日請求手数料
        ,D_ClaimFeeTax                                              -- 当日請求手数料消費税
        ,D_MonthlyFee                                               -- 当日月額固定費
        ,D_MonthlyFeeTax                                            -- 当日月額固定費消費税
        ,D_IncludeMonthlyFee                                        -- 当日同梱月額固定費
        ,D_IncludeMonthlyFeeTax                                     -- 当日同梱月額固定費消費税
        ,D_ApiMonthlyFee                                            -- 当日API月額固定費
        ,D_ApiMonthlyFeeTax                                         -- 当日API月額固定費消費税
        ,D_CreditNoticeMonthlyFee                                   -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTax                                -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFee                                  -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTax                               -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFee                                        -- 当日月額固定費予備
        ,D_ReserveMonthlyFeeTax                                     -- 当日月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙ決済手数料
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙ請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙ請求手数料消費税
        ,D_SettlementFeeTotal                                       -- 当日合計決済手数料
        ,D_ClaimFeeTotal                                            -- 当日合計請求手数料
        ,D_ClaimFeeTaxTotal                                         -- 当日合計請求手数料消費税
        ,D_MonthlyFeeTotal                                          -- 当日合計月額固定費
        ,D_MonthlyFeeTaxTotal                                       -- 当日合計月額固定費消費税
        ,D_IncludeMonthlyFeeTotal                                   -- 当日合計同梱月額固定費
        ,D_IncludeMonthlyFeeTaxTotal                                -- 当日合計同梱月額固定費消費税
        ,D_ApiMonthlyFeeTotal                                       -- 当日合計API月額固定費
        ,D_ApiMonthlyFeeTaxTotal                                    -- 当日合計API月額固定費消費税
        ,D_CreditNoticeMonthlyFeeTotal                              -- 当日合計与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTaxTotal                           -- 当日合計与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFeeTotal                             -- 当日合計次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTaxTotal                          -- 当日合計次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFeeTotal                                   -- 当日合計月額固定費予備
        ,D_ReserveMonthlyFeeTaxTotal                                -- 当日合計月額固定費予備消費税
        ,D_AddClaimFeeTotal                                         -- 当日合計再発行手数料
        ,D_AddClaimFeeTaxTotal                                      -- 当日合計再発行手数料消費税
        ,D_DamageInterestAmountTotal                                -- 当日合計遅延損害金
        ,D_AllTotal                                                 -- 当日総合計
        ,D_SettlementFeeOther                                       -- 当日売上明細決済手数料
        ,D_ClaimFeeOther                                            -- 当日売上明細請求手数料
        ,D_ClaimFeeTaxOther                                         -- 当日売上明細請求手数料消費税
        ,D_MonthlyFeeOther                                          -- 当日売上明細月額固定費
        ,D_MonthlyFeeTaxOther                                       -- 当日売上明細月額固定費消費税
        ,D_IncludeMonthlyFeeOther                                   -- 当日売上明細同梱月額固定費
        ,D_IncludeMonthlyFeeTaxOther                                -- 当日売上明細同梱月額固定費消費税
        ,D_ApiMonthlyFeeOther                                       -- 当日売上明細API月額固定費
        ,D_ApiMonthlyFeeTaxOther                                    -- 当日売上明細API月額固定費消費税
        ,D_CreditNoticeMonthlyFeeOther                              -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTaxOther                           -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFeeOther                             -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTaxOther                          -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFeeOther                                   -- 当日売上明細月額固定費予備
        ,D_ReserveMonthlyFeeTaxOther                                -- 当日売上明細月額固定費予備消費税
        ,D_AddClaimFeeOther                                         -- 当日手数料明細再発行手数料
        ,D_AddClaimFeeTaxOther                                      -- 当日手数料明細再発行手数料消費税
        ,D_DamageInterestAmountOther                                -- 当日手数料明細遅延損害金
        ,D_SettlementFeeDiff                                        -- 当日差額決済手数料
        ,D_ClaimFeeDiff                                             -- 当日差額請求手数料
        ,D_ClaimFeeTaxDiff                                          -- 当日差額請求手数料消費税
        ,D_MonthlyFeeDiff                                           -- 当日差額月額固定費
        ,D_MonthlyFeeTaxDiff                                        -- 当日差額月額固定費消費税
        ,D_IncludeMonthlyFeeDiff                                    -- 当日差額同梱月額固定費
        ,D_IncludeMonthlyFeeTaxDiff                                 -- 当日差額同梱月額固定費消費税
        ,D_ApiMonthlyFeeDiff                                        -- 当日差額API月額固定費
        ,D_ApiMonthlyFeeTaxDiff                                     -- 当日差額API月額固定費消費税
        ,D_CreditNoticeMonthlyFeeDiff                               -- 当日差額与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_CreditNoticeMonthlyFeeTaxDiff                            -- 当日差額与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_NCreditNoticeMonthlyFeeDiff                              -- 当日差額次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_NCreditNoticeMonthlyFeeTaxDiff                           -- 当日差額次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_ReserveMonthlyFeeDiff                                    -- 当日差額月額固定費予備
        ,D_ReserveMonthlyFeeTaxDiff                                 -- 当日差額月額固定費予備消費税
        ,D_AddClaimFeeDiff                                          -- 当日差額再発行手数料
        ,D_AddClaimFeeTaxDiff                                       -- 当日差額再発行手数料消費税
        ,D_DamageInterestAmountDiff                                 -- 当日差額遅延損害金
        ,MB__AccountsReceivableBalance                              -- 月初未収金残高
        ,M_ChargeCount                                              -- 当月立替件数
        ,M_ChargeAmount                                             -- 当月立替金額
        ,M_CancelCount                                              -- 当月ｷｬﾝｾﾙ件数
        ,M_CancelAmount                                             -- 当月ｷｬﾝｾﾙ立替金額
        ,M_SettlementBackCount                                      -- 当月立替金戻し件数
        ,M_SettlementBackAmount                                     -- 当月立替金戻し金額
        ,M_TransferCount                                            -- 当月OEM移管件数
        ,M_TransferAmount                                           -- 当月OEM移管金額
        ,M_ReClaimFeeCount                                          -- 当月再発行手数料件数
        ,M_ReClaimFeeAmount                                         -- 当月再発行手数料額
        ,M_DamageCount                                              -- 当月遅延損害金件数
        ,M_DamageAmount                                             -- 当月遅延損害金額
        ,M_ReceiptCount                                             -- 当月入金件数
        ,M_ReceiptAmount                                            -- 当月入金額
        ,M_RepayCount                                               -- 当月返金件数
        ,M_RepayAmount                                              -- 当月返金額
        ,M_BadDebtCount                                             -- 当月貸倒件数
        ,M_BadDebtAmount                                            -- 当月貸倒金額
        ,M_OtherPaymentCount                                        -- 当月その他件数
        ,M_OtherPaymentAmount                                       -- 当月その他金額
        ,M_AccountsReceivableBalance                                -- 当月時点未収金残高
        ,M_SuspensePaymentsAmount                                   -- 消費者未収金明細残高
        ,M_AccountsReceivableBalanceDiff                            -- 当月差額
        ,M_SettlementFee                                            -- 当月決済手数料
        ,M_ClaimFee                                                 -- 当月請求手数料
        ,M_ClaimFeeTax                                              -- 当月請求手数料消費税
        ,M_MonthlyFee                                               -- 当月月額固定費
        ,M_MonthlyFeeTax                                            -- 当月月額固定費消費税
        ,M_IncludeMonthlyFee                                        -- 当月同梱月額固定費
        ,M_IncludeMonthlyFeeTax                                     -- 当月同梱月額固定費消費税
        ,M_ApiMonthlyFee                                            -- 当月API月額固定費
        ,M_ApiMonthlyFeeTax                                         -- 当月API月額固定費消費税
        ,M_CreditNoticeMonthlyFee                                   -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTax                                -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFee                                  -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTax                               -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFee                                        -- 当月月額固定費予備
        ,M_ReserveMonthlyFeeTax                                     -- 当月月額固定費予備消費税
        ,M_AddClaimFee                                              -- 当月再発行手数料
        ,M_AddClaimFeeTax                                           -- 当月再発行手数料消費税
        ,M_DamageInterestAmount                                     -- 当月遅延損害金
        ,M_CanSettlementFee                                         -- 当月ｷｬﾝｾﾙ決済手数料
        ,M_CanClaimFee                                              -- 当月ｷｬﾝｾﾙ請求手数料
        ,M_CanClaimFeeTax                                           -- 当月ｷｬﾝｾﾙ請求手数料消費税
        ,M_SettlementFeeTotal                                       -- 当月合計決済手数料
        ,M_ClaimFeeTotal                                            -- 当月合計請求手数料
        ,M_ClaimFeeTaxTotal                                         -- 当月合計請求手数料消費税
        ,M_MonthlyFeeTotal                                          -- 当月合計月額固定費
        ,M_MonthlyFeeTaxTotal                                       -- 当月合計月額固定費消費税
        ,M_IncludeMonthlyFeeTotal                                   -- 当月合計同梱月額固定費
        ,M_IncludeMonthlyFeeTaxTotal                                -- 当月合計同梱月額固定費消費税
        ,M_ApiMonthlyFeeTotal                                       -- 当月合計API月額固定費
        ,M_ApiMonthlyFeeTaxTotal                                    -- 当月合計API月額固定費消費税
        ,M_CreditNoticeMonthlyFeeTotal                              -- 当月合計与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTaxTotal                           -- 当月合計与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFeeTotal                             -- 当月合計次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTaxTotal                          -- 当月合計次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFeeTotal                                   -- 当月合計月額固定費予備
        ,M_ReserveMonthlyFeeTaxTotal                                -- 当月合計月額固定費予備消費税
        ,M_AddClaimFeeTotal                                         -- 当月合計再発行手数料
        ,M_AddClaimFeeTaxTotal                                      -- 当月合計再発行手数料消費税
        ,M_DamageInterestAmountTotal                                -- 当月合計遅延損害金
        ,M_AllTotal                                                 -- 当月総合計
        ,M_SettlementFeeOther                                       -- 当月売上明細決済手数料
        ,M_ClaimFeeOther                                            -- 当月売上明細請求手数料
        ,M_ClaimFeeTaxOther                                         -- 当月売上明細請求手数料消費税
        ,M_MonthlyFeeOther                                          -- 当月売上明細月額固定費
        ,M_MonthlyFeeTaxOther                                       -- 当月売上明細月額固定費消費税
        ,M_IncludeMonthlyFeeOther                                   -- 当月売上明細同梱月額固定費
        ,M_IncludeMonthlyFeeTaxOther                                -- 当月売上明細同梱月額固定費消費税
        ,M_ApiMonthlyFeeOther                                       -- 当月売上明細API月額固定費
        ,M_ApiMonthlyFeeTaxOther                                    -- 当月売上明細API月額固定費消費税
        ,M_CreditNoticeMonthlyFeeOther                              -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTaxOther                           -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFeeOther                             -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTaxOther                          -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFeeOther                                   -- 当月売上明細月額固定費予備
        ,M_ReserveMonthlyFeeTaxOther                                -- 当月売上明細月額固定費予備消費税
        ,M_AddClaimFeeOther                                         -- 当月手数料明細再発行手数料
        ,M_AddClaimFeeTaxOther                                      -- 当月手数料明細再発行手数料消費税
        ,M_DamageInterestAmountOther                                -- 当月手数料明細遅延損害金
        ,M_SettlementFeeDiff                                        -- 当月差額決済手数料
        ,M_ClaimFeeDiff                                             -- 当月差額請求手数料
        ,M_ClaimFeeTaxDiff                                          -- 当月差額請求手数料消費税
        ,M_MonthlyFeeDiff                                           -- 当月差額月額固定費
        ,M_MonthlyFeeTaxDiff                                        -- 当月差額月額固定費消費税
        ,M_IncludeMonthlyFeeDiff                                    -- 当月差額同梱月額固定費
        ,M_IncludeMonthlyFeeTaxDiff                                 -- 当月差額同梱月額固定費消費税
        ,M_ApiMonthlyFeeDiff                                        -- 当月差額API月額固定費
        ,M_ApiMonthlyFeeTaxDiff                                     -- 当月差額API月額固定費消費税
        ,M_CreditNoticeMonthlyFeeDiff                               -- 当月差額与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_CreditNoticeMonthlyFeeTaxDiff                            -- 当月差額与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_NCreditNoticeMonthlyFeeDiff                              -- 当月差額次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_NCreditNoticeMonthlyFeeTaxDiff                           -- 当月差額次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_ReserveMonthlyFeeDiff                                    -- 当月差額月額固定費予備
        ,M_ReserveMonthlyFeeTaxDiff                                 -- 当月差額月額固定費予備消費税
        ,M_AddClaimFeeDiff                                          -- 当月差額再発行手数料
        ,M_AddClaimFeeTaxDiff                                       -- 当月差額再発行手数料消費税
        ,M_DamageInterestAmountDiff                                 -- 当月差額遅延損害金
        ,RegistDate                                                 -- 登録日時
        ,RegistId                                                   -- 登録者
        ,UpdateDate                                                 -- 更新日時
        ,UpdateId                                                   -- 更新者
        ,ValidFlg                                                   -- 有効ﾌﾗｸﾞ
        )
    SELECT
         0                                                          -- 日次･月次区分
        ,v_BusinessDate                                             -- 集計日
        ,DATE_ADD( v_AccountingMonth, INTERVAL 1 MONTH )            -- 会計月
        ,aw_dst.EnterpriseId                                        -- 加盟店ID
        ,CASE WHEN aw_dst.EnterpriseId = v_EnterpriseId
            THEN 'OEM移管分'
            ELSE ent.EnterpriseNameKj
         END                                                        -- 加盟店名
        ,aw_dst.DB__AccountsReceivableBalance                       -- 前日時点未収金残高
        ,aw_dst.D_ChargeCount                                       -- 当日立替件数
        ,aw_dst.D_ChargeAmount                                      -- 当日立替金額
        ,aw_dst.D_CancelCount                                       -- 当日ｷｬﾝｾﾙ件数
        ,aw_dst.D_CancelAmount                                      -- 当日ｷｬﾝｾﾙ立替金額
        ,aw_dst.D_SettlementBackCount                               -- 当日立替金戻し件数
        ,aw_dst.D_SettlementBackAmount                              -- 当日立替金戻し金額
        ,aw_dst.D_OemTransferCount                                  -- 当日OEM移管件数
        ,aw_dst.D_OemTransferAmount                                 -- 当日OEM移管金額
        ,aw_dst.D_ReClaimFeeCount                                   -- 当月再発行手数料件数
        ,aw_dst.D_ReClaimFeeAmount                                  -- 当月再発行手数料額
        ,aw_dst.D_DamageCount                                       -- 当月遅延損害金件数
        ,aw_dst.D_DamageAmount                                      -- 当月遅延損害金額
        ,aw_dst.D_ReceiptCount                                      -- 当日入金件数
        ,aw_dst.D_ReceiptAmount                                     -- 当日入金額
        ,aw_dst.D_RepayCount                                        -- 当日返金件数
        ,aw_dst.D_RepayAmount                                       -- 当日返金額
        ,aw_dst.D_BadDebtCount                                      -- 当日貸倒件数
        ,aw_dst.D_BadDebtAmount                                     -- 当日貸倒金額
        ,aw_dst.D_OtherPaymentCount                                 -- 当日その他件数
        ,aw_dst.D_OtherPaymentAmount                                -- 当日その他金額
        ,aw_dst.DB__AccountsReceivableBalance +
         aw_dst.D_ChargeAmount + aw_dst.D_CancelAmount +
         aw_dst.D_SettlementBackAmount + aw_dst.D_OemTransferAmount +
         aw_dst.D_ReClaimFeeAmount + aw_dst.D_DamageAmount +
         aw_dst.D_ReceiptAmount + aw_dst.D_RepayAmount +
         aw_dst.D_BadDebtAmount + aw_dst.D_OtherPaymentAmount       -- 当日時点未収金残高
        ,aw_dst.D_SettlementFee                                     -- 当日決済手数料
        ,aw_dst.D_ClaimFee                                          -- 当日請求手数料
        ,aw_dst.D_ClaimFeeTax                                       -- 当日請求手数料消費税
        ,aw_dst.D_MonthlyFee                                        -- 当日月額固定費
        ,aw_dst.D_MonthlyFeeTax                                     -- 当日月額固定費消費税
        ,aw_dst.D_IncludeMonthlyFee                                 -- 当日同梱月額固定費
        ,aw_dst.D_IncludeMonthlyFeeTax                              -- 当日同梱月額固定費消費税
        ,aw_dst.D_ApiMonthlyFee                                     -- 当日API月額固定費
        ,aw_dst.D_ApiMonthlyFeeTax                                  -- 当日API月額固定費消費税
        ,aw_dst.D_CreditNoticeMonthlyFee                            -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_CreditNoticeMonthlyFeeTax                         -- 当日与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_NCreditNoticeMonthlyFee                           -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_NCreditNoticeMonthlyFeeTax                        -- 当日次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_ReserveMonthlyFee                                 -- 当日月額固定費予備
        ,aw_dst.D_ReserveMonthlyFeeTax                              -- 当日月額固定費予備消費税
        ,aw_dst.D_AddClaimFee                                       -- 当日再発行手数料
        ,aw_dst.D_AddClaimFeeTax                                    -- 当日再発行手数料消費税
        ,aw_dst.D_DamageInterestAmount                              -- 当日遅延損害金
        ,aw_dst.D_CanSettlementFee                                  -- 当日ｷｬﾝｾﾙ決済手数料
        ,aw_dst.D_CanClaimFee                                       -- 当日ｷｬﾝｾﾙ請求手数料
        ,aw_dst.D_CanClaimFeeTax                                    -- 当日ｷｬﾝｾﾙ請求手数料消費税
        ,aw_dst.D_SettlementFee + aw_dst.D_CanSettlementFee         -- 当日合計決済手数料
        ,aw_dst.D_ClaimFee + aw_dst.D_CanClaimFee                   -- 当日合計請求手数料
        ,aw_dst.D_ClaimFeeTax + aw_dst.D_CanClaimFeeTax             -- 当日合計請求手数料消費税
        ,aw_dst.D_MonthlyFee                                        -- 当日合計月額固定費
        ,aw_dst.D_MonthlyFeeTax                                     -- 当日合計月額固定費消費税
        ,aw_dst.D_IncludeMonthlyFee                                 -- 当日合計同梱月額固定費
        ,aw_dst.D_IncludeMonthlyFeeTax                              -- 当日合計同梱月額固定費消費税
        ,aw_dst.D_ApiMonthlyFee                                     -- 当日合計API月額固定費
        ,aw_dst.D_ApiMonthlyFeeTax                                  -- 当日合計API月額固定費消費税
        ,aw_dst.D_CreditNoticeMonthlyFee                            -- 当日合計与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_CreditNoticeMonthlyFeeTax                         -- 当日合計与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_NCreditNoticeMonthlyFee                           -- 当日合計次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_NCreditNoticeMonthlyFeeTax                        -- 当日合計次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_ReserveMonthlyFee                                 -- 当日合計月額固定費予備
        ,aw_dst.D_ReserveMonthlyFeeTax                              -- 当日合計月額固定費予備消費税
        ,aw_dst.D_AddClaimFee                                       -- 当日合計再発行手数料
        ,aw_dst.D_AddClaimFeeTax                                    -- 当日合計再発行手数料消費税
        ,aw_dst.D_DamageInterestAmount                              -- 当日合計遅延損害金
        ,aw_dst.D_SettlementFee + aw_dst.D_CanSettlementFee +
         aw_dst.D_ClaimFee + aw_dst.D_CanClaimFee +
         aw_dst.D_ClaimFeeTax + aw_dst.D_CanClaimFeeTax +
         aw_dst.D_MonthlyFee + aw_dst.D_MonthlyFeeTax +
         aw_dst.D_IncludeMonthlyFee + aw_dst.D_IncludeMonthlyFeeTax +
         aw_dst.D_ApiMonthlyFee + aw_dst.D_ApiMonthlyFeeTax +
         aw_dst.D_CreditNoticeMonthlyFee+ aw_dst.D_CreditNoticeMonthlyFeeTax +
         aw_dst.D_NCreditNoticeMonthlyFee + aw_dst.D_NCreditNoticeMonthlyFeeTax +
         aw_dst.D_ReserveMonthlyFee + aw_dst.D_ReserveMonthlyFeeTax +
         aw_dst.D_AddClaimFee + aw_dst.D_AddClaimFeeTax +
         aw_dst.D_DamageInterestAmount                              -- 当日総合計
        ,IFNULL(at_dsd.SettlementFee, 0)                            -- 当日売上明細決済手数料
        ,IFNULL(at_dsd.ClaimFee, 0)                                 -- 当日売上明細請求手数料
        ,IFNULL(at_dsd.ClaimFeeTax, 0)                              -- 当日売上明細請求手数料消費税
        ,IFNULL(at_dsd.MonthlyFee, 0)                               -- 当日売上明細月額固定費
        ,IFNULL(at_dsd.MonthlyFeeTax, 0)                            -- 当日売上明細月額固定費消費税
        ,IFNULL(at_dsd.IncludeMonthlyFee, 0)                        -- 当日売上明細同梱月額固定費
        ,IFNULL(at_dsd.IncludeMonthlyFeeTax, 0)                     -- 当日売上明細同梱月額固定費消費税
        ,IFNULL(at_dsd.ApiMonthlyFee, 0)                            -- 当日売上明細API月額固定費
        ,IFNULL(at_dsd.ApiMonthlyFeeTax, 0)                         -- 当日売上明細API月額固定費消費税
        ,IFNULL(at_dsd.CreditNoticeMonthlyFee, 0)                   -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,IFNULL(at_dsd.CreditNoticeMonthlyFeeTax, 0)                -- 当日売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,IFNULL(at_dsd.NCreditNoticeMonthlyFee, 0)                  -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,IFNULL(at_dsd.NCreditNoticeMonthlyFeeTax, 0)               -- 当日売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,IFNULL(at_dsd.ReserveMonthlyFee, 0)                        -- 当日売上明細月額固定費予備
        ,IFNULL(at_dsd.ReserveMonthlyFeeTax, 0)                     -- 当日売上明細月額固定費予備消費税
        ,IFNULL(at_rfs.Clm_L_ClaimFee, 0)                           -- 当日手数料明細再発行手数料
        ,IFNULL(at_rfs.Clm_L_ClaimFeeTax, 0)                        -- 当日手数料明細再発行手数料消費税
        ,IFNULL(at_rfs.Clm_L_DamageInterestAmount, 0)               -- 当日手数料明細遅延損害金
        ,aw_dst.D_SettlementFee + aw_dst.D_CanSettlementFee - IFNULL(at_dsd.SettlementFee, 0)       -- 当日差額決済手数料
        ,aw_dst.D_ClaimFee + aw_dst.D_CanClaimFee - IFNULL(at_dsd.ClaimFee, 0)                      -- 当日差額請求手数料
        ,aw_dst.D_ClaimFeeTax + aw_dst.D_CanClaimFeeTax - IFNULL(at_dsd.ClaimFeeTax, 0)             -- 当日差額請求手数料消費税
        ,aw_dst.D_MonthlyFee - IFNULL(at_dsd.MonthlyFee , 0)                                        -- 当日差額月額固定費
        ,aw_dst.D_MonthlyFeeTax - IFNULL(at_dsd.MonthlyFeeTax, 0)                                   -- 当日差額月額固定費消費税
        ,aw_dst.D_IncludeMonthlyFee - IFNULL(at_dsd.IncludeMonthlyFee, 0)                           -- 当日差額同梱月額固定費
        ,aw_dst.D_IncludeMonthlyFeeTax - IFNULL(at_dsd.IncludeMonthlyFeeTax, 0)                     -- 当日差額同梱月額固定費消費税
        ,aw_dst.D_ApiMonthlyFee - IFNULL(at_dsd.ApiMonthlyFee, 0)                                   -- 当日差額API月額固定費
        ,aw_dst.D_ApiMonthlyFeeTax - IFNULL(at_dsd.ApiMonthlyFeeTax, 0)                             -- 当日差額API月額固定費消費税
        ,aw_dst.D_CreditNoticeMonthlyFee - IFNULL(at_dsd.CreditNoticeMonthlyFee, 0)                 -- 当日差額与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_CreditNoticeMonthlyFeeTax - IFNULL(at_dsd.CreditNoticeMonthlyFeeTax, 0)           -- 当日差額与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_NCreditNoticeMonthlyFee - IFNULL(at_dsd.NCreditNoticeMonthlyFee, 0)               -- 当日差額次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_NCreditNoticeMonthlyFeeTax - IFNULL(at_dsd.NCreditNoticeMonthlyFeeTax, 0)         -- 当日差額次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_ReserveMonthlyFee - IFNULL(at_dsd.ReserveMonthlyFee, 0)                           -- 当日差額月額固定費予備
        ,aw_dst.D_ReserveMonthlyFeeTax - IFNULL(at_dsd.ReserveMonthlyFeeTax, 0)                     -- 当日差額月額固定費予備消費税
        ,aw_dst.D_AddClaimFee - IFNULL(at_rfs.Clm_L_ClaimFee, 0)                                    -- 当日差額再発行手数料
        ,aw_dst.D_AddClaimFeeTax - IFNULL(at_rfs.Clm_L_ClaimFeeTax, 0)                              -- 当日差額再発行手数料消費税
        ,aw_dst.D_DamageInterestAmount - IFNULL(at_rfs.Clm_L_DamageInterestAmount, 0)               -- 当日差額遅延損害金
        ,aw_dst.MB__AccountsReceivableBalance                       -- 月初未収金残高
        ,aw_dst.M_ChargeCount                                       -- 当月立替件数
        ,aw_dst.M_ChargeAmount                                      -- 当月立替金額
        ,aw_dst.M_CancelCount                                       -- 当月ｷｬﾝｾﾙ件数
        ,aw_dst.M_CancelAmount                                      -- 当月ｷｬﾝｾﾙ立替金額
        ,aw_dst.M_SettlementBackCount                               -- 当月立替金戻し件数
        ,aw_dst.M_SettlementBackAmount                              -- 当月立替金戻し金額
        ,aw_dst.M_TransferCount                                     -- 当月OEM移管件数
        ,aw_dst.M_TransferAmount                                    -- 当月OEM移管金額
        ,aw_dst.M_ReClaimFeeCount                                   -- 当月再発行手数料件数
        ,aw_dst.M_ReClaimFeeAmount                                  -- 当月再発行手数料額
        ,aw_dst.M_DamageCount                                       -- 当月遅延損害金件数
        ,aw_dst.M_DamageAmount                                      -- 当月遅延損害金額
        ,aw_dst.M_ReceiptCount                                      -- 当月入金件数
        ,aw_dst.M_ReceiptAmount                                     -- 当月入金額
        ,aw_dst.M_RepayCount                                        -- 当月返金件数
        ,aw_dst.M_RepayAmount                                       -- 当月返金額
        ,aw_dst.M_BadDebtCount                                      -- 当月貸倒件数
        ,aw_dst.M_BadDebtAmount                                     -- 当月貸倒金額
        ,aw_dst.M_OtherPaymentCount                                 -- 当月その他件数
        ,aw_dst.M_OtherPaymentAmount                                -- 当月その他金額
        ,aw_dst.MB__AccountsReceivableBalance +
         aw_dst.M_ChargeAmount + aw_dst.M_CancelAmount +
         aw_dst.M_SettlementBackAmount + aw_dst.M_TransferAmount +
         aw_dst.M_ReClaimFeeAmount + aw_dst.M_DamageAmount +
         aw_dst.M_ReceiptAmount + aw_dst.M_RepayAmount +
         aw_dst.M_BadDebtAmount + aw_dst.M_OtherPaymentAmount       -- 当月時点未収金残高
        ,null
        ,null
        ,aw_dst.M_SettlementFee                                     -- 当月決済手数料
        ,aw_dst.M_ClaimFee                                          -- 当月請求手数料
        ,aw_dst.M_ClaimFeeTax                                       -- 当月請求手数料消費税
        ,aw_dst.M_MonthlyFee                                        -- 当月月額固定費
        ,aw_dst.M_MonthlyFeeTax                                     -- 当月月額固定費消費税
        ,aw_dst.M_IncludeMonthlyFee                                 -- 当月同梱月額固定費
        ,aw_dst.M_IncludeMonthlyFeeTax                              -- 当月同梱月額固定費消費税
        ,aw_dst.M_ApiMonthlyFee                                     -- 当月API月額固定費
        ,aw_dst.M_ApiMonthlyFeeTax                                  -- 当月API月額固定費消費税
        ,aw_dst.M_CreditNoticeMonthlyFee                            -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.M_CreditNoticeMonthlyFeeTax                         -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.M_NCreditNoticeMonthlyFee                           -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.M_NCreditNoticeMonthlyFeeTax                        -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.M_ReserveMonthlyFee                                 -- 当月月額固定費予備
        ,aw_dst.M_ReserveMonthlyFeeTax                              -- 当月月額固定費予備消費税
        ,aw_dst.M_AddClaimFee                                       -- 当月再発行手数料
        ,aw_dst.M_AddClaimFeeTax                                    -- 当月再発行手数料消費税
        ,aw_dst.M_DamageInterestAmount                              -- 当月遅延損害金
        ,aw_dst.M_CanSettlementFee                                  -- 当月ｷｬﾝｾﾙ決済手数料
        ,aw_dst.M_CanClaimFee                                       -- 当月ｷｬﾝｾﾙ請求手数料
        ,aw_dst.M_CanClaimFeeTax                                    -- 当月ｷｬﾝｾﾙ請求手数料消費税
        ,aw_dst.M_SettlementFee + aw_dst.M_CanSettlementFee         -- 当月合計決済手数料
        ,aw_dst.M_ClaimFee + aw_dst.M_CanClaimFee                   -- 当月合計請求手数料
        ,aw_dst.M_ClaimFeeTax + aw_dst.M_CanClaimFeeTax             -- 当月合計請求手数料消費税
        ,aw_dst.M_MonthlyFee                                        -- 当月合計月額固定費
        ,aw_dst.M_MonthlyFeeTax                                     -- 当月合計月額固定費消費税
        ,aw_dst.M_IncludeMonthlyFee                                 -- 当月合計同梱月額固定費
        ,aw_dst.M_IncludeMonthlyFeeTax                              -- 当月合計同梱月額固定費消費税
        ,aw_dst.M_ApiMonthlyFee                                     -- 当月合計API月額固定費
        ,aw_dst.M_ApiMonthlyFeeTax                                  -- 当月合計API月額固定費消費税
        ,aw_dst.M_CreditNoticeMonthlyFee                            -- 当月合計与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.M_CreditNoticeMonthlyFeeTax                         -- 当月合計与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.M_NCreditNoticeMonthlyFee                           -- 当月合計次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.M_NCreditNoticeMonthlyFeeTax                        -- 当月合計次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.M_ReserveMonthlyFee                                 -- 当月合計月額固定費予備
        ,aw_dst.M_ReserveMonthlyFeeTax                              -- 当月合計月額固定費予備消費税
        ,aw_dst.M_AddClaimFee                                       -- 当月合計再発行手数料
        ,aw_dst.M_AddClaimFeeTax                                    -- 当月合計再発行手数料消費税
        ,aw_dst.M_DamageInterestAmount                              -- 当月合計遅延損害金
        ,aw_dst.M_SettlementFee + aw_dst.M_CanSettlementFee +
         aw_dst.M_ClaimFee + aw_dst.M_CanClaimFee +
         aw_dst.M_ClaimFeeTax + aw_dst.M_CanClaimFeeTax +
         aw_dst.M_MonthlyFee + aw_dst.M_MonthlyFeeTax +
         aw_dst.M_IncludeMonthlyFee + aw_dst.M_IncludeMonthlyFeeTax +
         aw_dst.M_ApiMonthlyFee + aw_dst.M_ApiMonthlyFeeTax +
         aw_dst.M_CreditNoticeMonthlyFee + aw_dst.M_CreditNoticeMonthlyFeeTax +
         aw_dst.M_NCreditNoticeMonthlyFee + aw_dst.M_NCreditNoticeMonthlyFeeTax +
         aw_dst.M_ReserveMonthlyFee + aw_dst.M_ReserveMonthlyFeeTax +
         aw_dst.M_AddClaimFee + aw_dst.M_AddClaimFeeTax +
         aw_dst.M_DamageInterestAmount                              -- 当月総合計
        ,null                                                       -- 当月売上明細決済手数料
        ,null                                                       -- 当月売上明細請求手数料
        ,null                                                       -- 当月売上明細請求手数料消費税
        ,null                                                       -- 当月売上明細月額固定費
        ,null                                                       -- 当月売上明細月額固定費消費税
        ,null                                                       -- 当月売上明細同梱月額固定費
        ,null                                                       -- 当月売上明細同梱月額固定費消費税
        ,null                                                       -- 当月売上明細API月額固定費
        ,null                                                       -- 当月売上明細API月額固定費消費税
        ,null                                                       -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細月額固定費予備
        ,null                                                       -- 当月売上明細月額固定費予備消費税
        ,null                                                       -- 当月手数料明細再発行手数料
        ,null                                                       -- 当月手数料明細再発行手数料消費税
        ,null                                                       -- 当月手数料明細遅延損害金
        ,null                                                       -- 当月差額決済手数料
        ,null                                                       -- 当月差額請求手数料
        ,null                                                       -- 当月差額請求手数料消費税
        ,null                                                       -- 当月差額月額固定費
        ,null                                                       -- 当月差額月額固定費消費税
        ,null                                                       -- 当月差額同梱月額固定費
        ,null                                                       -- 当月差額同梱月額固定費消費税
        ,null                                                       -- 当月差額API月額固定費
        ,null                                                       -- 当月差額API月額固定費消費税
        ,null                                                       -- 当月差額与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月差額与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月差額次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月差額次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月差額月額固定費予備
        ,null                                                       -- 当月差額月額固定費予備消費税
        ,null                                                       -- 当月差額再発行手数料
        ,null                                                       -- 当月差額再発行手数料消費税
        ,null                                                       -- 当月差額遅延損害金
        ,NOW()                                                      -- 登録日時
        ,pi_user_id                                                 -- 登録者
        ,NOW()                                                      -- 更新日時
        ,pi_user_id                                                 -- 更新者
        ,1                                                          -- 有効ﾌﾗｸﾞ
    FROM (      SELECT
                     EnterpriseId                                           AS EnterpriseId
                    ,SUM( IFNULL( DB__AccountsReceivableBalance, 0 ) )      AS DB__AccountsReceivableBalance
                    ,SUM( IFNULL( D_ChargeCount, 0 ) )                      AS D_ChargeCount
                    ,SUM( IFNULL( D_ChargeAmount, 0 ) )                     AS D_ChargeAmount
                    ,SUM( IFNULL( D_CancelCount, 0 ) )                      AS D_CancelCount
                    ,SUM( IFNULL( D_CancelAmount, 0 ) )                     AS D_CancelAmount
                    ,SUM( IFNULL( D_SettlementBackCount, 0 ) )              AS D_SettlementBackCount
                    ,SUM( IFNULL( D_SettlementBackAmount, 0 ) )             AS D_SettlementBackAmount
                    ,SUM( IFNULL( D_OemTransferCount, 0 ) )                 AS D_OemTransferCount
                    ,SUM( IFNULL( D_OemTransferAmount, 0 ) )                AS D_OemTransferAmount
                    ,SUM( IFNULL( D_ReClaimFeeCount, 0 ) )                  AS D_ReClaimFeeCount
                    ,SUM( IFNULL( D_ReClaimFeeAmount, 0 ) )                 AS D_ReClaimFeeAmount
                    ,SUM( IFNULL( D_DamageCount, 0 ) )                      AS D_DamageCount
                    ,SUM( IFNULL( D_DamageAmount, 0 ) )                     AS D_DamageAmount
                    ,SUM( IFNULL( D_ReceiptCount, 0 ) )                     AS D_ReceiptCount
                    ,SUM( IFNULL( D_ReceiptAmount, 0 ) )                    AS D_ReceiptAmount
                    ,SUM( IFNULL( D_RepayCount, 0 ) )                       AS D_RepayCount
                    ,SUM( IFNULL( D_RepayAmount, 0 ) )                      AS D_RepayAmount
                    ,SUM( IFNULL( D_BadDebtCount, 0 ) )                     AS D_BadDebtCount
                    ,SUM( IFNULL( D_BadDebtAmount, 0 ) )                    AS D_BadDebtAmount
                    ,SUM( IFNULL( D_OtherPaymentCount, 0 ) )                AS D_OtherPaymentCount
                    ,SUM( IFNULL( D_OtherPaymentAmount, 0 ) )               AS D_OtherPaymentAmount
                    ,SUM( IFNULL( D_AccountsReceivableBalance, 0 ) )        AS D_AccountsReceivableBalance
                    ,SUM( IFNULL( D_SettlementFee, 0 ) )                    AS D_SettlementFee
                    ,SUM( IFNULL( D_ClaimFee, 0 ) )                         AS D_ClaimFee
                    ,SUM( IFNULL( D_ClaimFeeTax, 0 ) )                      AS D_ClaimFeeTax
                    ,SUM( IFNULL( D_MonthlyFee, 0 ) )                       AS D_MonthlyFee
                    ,SUM( IFNULL( D_MonthlyFeeTax, 0 ) )                    AS D_MonthlyFeeTax
                    ,SUM( IFNULL( D_IncludeMonthlyFee, 0 ) )                AS D_IncludeMonthlyFee
                    ,SUM( IFNULL( D_IncludeMonthlyFeeTax, 0 ) )             AS D_IncludeMonthlyFeeTax
                    ,SUM( IFNULL( D_ApiMonthlyFee, 0 ) )                    AS D_ApiMonthlyFee
                    ,SUM( IFNULL( D_ApiMonthlyFeeTax, 0 ) )                 AS D_ApiMonthlyFeeTax
                    ,SUM( IFNULL( D_CreditNoticeMonthlyFee, 0 ) )           AS D_CreditNoticeMonthlyFee
                    ,SUM( IFNULL( D_CreditNoticeMonthlyFeeTax, 0 ) )        AS D_CreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( D_NCreditNoticeMonthlyFee, 0 ) )          AS D_NCreditNoticeMonthlyFee
                    ,SUM( IFNULL( D_NCreditNoticeMonthlyFeeTax, 0 ) )       AS D_NCreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( D_ReserveMonthlyFee, 0 ) )                AS D_ReserveMonthlyFee
                    ,SUM( IFNULL( D_ReserveMonthlyFeeTax, 0 ) )             AS D_ReserveMonthlyFeeTax
                    ,SUM( IFNULL( D_AddClaimFee, 0 ) )                      AS D_AddClaimFee
                    ,SUM( IFNULL( D_AddClaimFeeTax, 0 ) )                   AS D_AddClaimFeeTax
                    ,SUM( IFNULL( D_DamageInterestAmount, 0 ) )             AS D_DamageInterestAmount
                    ,SUM( IFNULL( D_CanSettlementFee, 0 ) )                 AS D_CanSettlementFee
                    ,SUM( IFNULL( D_CanClaimFee, 0 ) )                      AS D_CanClaimFee
                    ,SUM( IFNULL( D_CanClaimFeeTax, 0 ) )                   AS D_CanClaimFeeTax
                    ,SUM( IFNULL( D_SettlementFeeOther, 0 ) )               AS D_SettlementFeeOther
                    ,SUM( IFNULL( D_ClaimFeeOther, 0 ) )                    AS D_ClaimFeeOther
                    ,SUM( IFNULL( D_ClaimFeeTaxOther, 0 ) )                 AS D_ClaimFeeTaxOther
                    ,SUM( IFNULL( D_MonthlyFeeOther, 0 ) )                  AS D_MonthlyFeeOther
                    ,SUM( IFNULL( D_MonthlyFeeTaxOther, 0 ) )               AS D_MonthlyFeeTaxOther
                    ,SUM( IFNULL( D_IncludeMonthlyFeeOther, 0 ) )           AS D_IncludeMonthlyFeeOther
                    ,SUM( IFNULL( D_IncludeMonthlyFeeTaxOther, 0 ) )        AS D_IncludeMonthlyFeeTaxOther
                    ,SUM( IFNULL( D_ApiMonthlyFeeOther, 0 ) )               AS D_ApiMonthlyFeeOther
                    ,SUM( IFNULL( D_ApiMonthlyFeeTaxOther, 0 ) )            AS D_ApiMonthlyFeeTaxOther
                    ,SUM( IFNULL( D_CreditNoticeMonthlyFeeOther, 0 ) )      AS D_CreditNoticeMonthlyFeeOther
                    ,SUM( IFNULL( D_CreditNoticeMonthlyFeeTaxOther, 0 ) )   AS D_CreditNoticeMonthlyFeeTaxOther
                    ,SUM( IFNULL( D_NCreditNoticeMonthlyFeeOther, 0 ) )     AS D_NCreditNoticeMonthlyFeeOther
                    ,SUM( IFNULL( D_NCreditNoticeMonthlyFeeTaxOther, 0 ) )  AS D_NCreditNoticeMonthlyFeeTaxOther
                    ,SUM( IFNULL( D_ReserveMonthlyFeeOther, 0 ) )           AS D_ReserveMonthlyFeeOther
                    ,SUM( IFNULL( D_ReserveMonthlyFeeTaxOther, 0 ) )        AS D_ReserveMonthlyFeeTaxOther
                    ,SUM( IFNULL( D_AddClaimFeeOther, 0 ) )                 AS D_AddClaimFeeOther
                    ,SUM( IFNULL( D_AddClaimFeeTaxOther, 0 ) )              AS D_AddClaimFeeTaxOther
                    ,SUM( IFNULL( D_DamageInterestAmountOther, 0 ) )        AS D_DamageInterestAmountOther
                    ,SUM( IFNULL( MB__AccountsReceivableBalance, 0 ) )      AS MB__AccountsReceivableBalance
                    ,SUM( IFNULL( M_ChargeCount, 0 ) )                      AS M_ChargeCount
                    ,SUM( IFNULL( M_ChargeAmount, 0 ) )                     AS M_ChargeAmount
                    ,SUM( IFNULL( M_CancelCount, 0 ) )                      AS M_CancelCount
                    ,SUM( IFNULL( M_CancelAmount, 0 ) )                     AS M_CancelAmount
                    ,SUM( IFNULL( M_SettlementBackCount, 0 ) )              AS M_SettlementBackCount
                    ,SUM( IFNULL( M_SettlementBackAmount, 0 ) )             AS M_SettlementBackAmount
                    ,SUM( IFNULL( M_TransferCount, 0 ) )                    AS M_TransferCount
                    ,SUM( IFNULL( M_TransferAmount, 0 ) )                   AS M_TransferAmount
                    ,SUM( IFNULL( M_ReClaimFeeCount, 0 ) )                  AS M_ReClaimFeeCount
                    ,SUM( IFNULL( M_ReClaimFeeAmount, 0 ) )                 AS M_ReClaimFeeAmount
                    ,SUM( IFNULL( M_DamageCount, 0 ) )                      AS M_DamageCount
                    ,SUM( IFNULL( M_DamageAmount, 0 ) )                     AS M_DamageAmount
                    ,SUM( IFNULL( M_ReceiptCount, 0 ) )                     AS M_ReceiptCount
                    ,SUM( IFNULL( M_ReceiptAmount, 0 ) )                    AS M_ReceiptAmount
                    ,SUM( IFNULL( M_RepayCount, 0 ) )                       AS M_RepayCount
                    ,SUM( IFNULL( M_RepayAmount, 0 ) )                      AS M_RepayAmount
                    ,SUM( IFNULL( M_BadDebtCount, 0 ) )                     AS M_BadDebtCount
                    ,SUM( IFNULL( M_BadDebtAmount, 0 ) )                    AS M_BadDebtAmount
                    ,SUM( IFNULL( M_OtherPaymentCount, 0 ) )                AS M_OtherPaymentCount
                    ,SUM( IFNULL( M_OtherPaymentAmount, 0 ) )               AS M_OtherPaymentAmount
                    ,SUM( IFNULL( M_AccountsReceivableBalance, 0 ) )        AS M_AccountsReceivableBalance
                    ,SUM( IFNULL( M_SuspensePaymentsAmount, 0 ) )           AS M_SuspensePaymentsAmount
                    ,SUM( IFNULL( M_AccountsReceivableBalanceDiff, 0 ) )    AS M_AccountsReceivableBalanceDiff
                    ,SUM( IFNULL( M_SettlementFee, 0 ) )                    AS M_SettlementFee
                    ,SUM( IFNULL( M_ClaimFee, 0 ) )                         AS M_ClaimFee
                    ,SUM( IFNULL( M_ClaimFeeTax, 0 ) )                      AS M_ClaimFeeTax
                    ,SUM( IFNULL( M_MonthlyFee, 0 ) )                       AS M_MonthlyFee
                    ,SUM( IFNULL( M_MonthlyFeeTax, 0 ) )                    AS M_MonthlyFeeTax
                    ,SUM( IFNULL( M_IncludeMonthlyFee, 0 ) )                AS M_IncludeMonthlyFee
                    ,SUM( IFNULL( M_IncludeMonthlyFeeTax, 0 ) )             AS M_IncludeMonthlyFeeTax
                    ,SUM( IFNULL( M_ApiMonthlyFee, 0 ) )                    AS M_ApiMonthlyFee
                    ,SUM( IFNULL( M_ApiMonthlyFeeTax, 0 ) )                 AS M_ApiMonthlyFeeTax
                    ,SUM( IFNULL( M_CreditNoticeMonthlyFee, 0 ) )           AS M_CreditNoticeMonthlyFee
                    ,SUM( IFNULL( M_CreditNoticeMonthlyFeeTax, 0 ) )        AS M_CreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( M_NCreditNoticeMonthlyFee, 0 ) )          AS M_NCreditNoticeMonthlyFee
                    ,SUM( IFNULL( M_NCreditNoticeMonthlyFeeTax, 0 ) )       AS M_NCreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( M_ReserveMonthlyFee, 0 ) )                AS M_ReserveMonthlyFee
                    ,SUM( IFNULL( M_ReserveMonthlyFeeTax, 0 ) )             AS M_ReserveMonthlyFeeTax
                    ,SUM( IFNULL( M_AddClaimFee, 0 ) )                      AS M_AddClaimFee
                    ,SUM( IFNULL( M_AddClaimFeeTax, 0 ) )                   AS M_AddClaimFeeTax
                    ,SUM( IFNULL( M_DamageInterestAmount, 0 ) )             AS M_DamageInterestAmount
                    ,SUM( IFNULL( M_CanSettlementFee, 0 ) )                 AS M_CanSettlementFee
                    ,SUM( IFNULL( M_CanClaimFee, 0 ) )                      AS M_CanClaimFee
                    ,SUM( IFNULL( M_CanClaimFeeTax, 0 ) )                   AS M_CanClaimFeeTax
                    ,SUM( IFNULL( M_SettlementFeeOther, 0 ) )               AS M_SettlementFeeOther
                    ,SUM( IFNULL( M_ClaimFeeOther, 0 ) )                    AS M_ClaimFeeOther
                    ,SUM( IFNULL( M_ClaimFeeTaxOther, 0 ) )                 AS M_ClaimFeeTaxOther
                    ,SUM( IFNULL( M_MonthlyFeeOther, 0 ) )                  AS M_MonthlyFeeOther
                    ,SUM( IFNULL( M_MonthlyFeeTaxOther, 0 ) )               AS M_MonthlyFeeTaxOther
                    ,SUM( IFNULL( M_IncludeMonthlyFeeOther, 0 ) )           AS M_IncludeMonthlyFeeOther
                    ,SUM( IFNULL( M_IncludeMonthlyFeeTaxOther, 0 ) )        AS M_IncludeMonthlyFeeTaxOther
                    ,SUM( IFNULL( M_ApiMonthlyFeeOther, 0 ) )               AS M_ApiMonthlyFeeOther
                    ,SUM( IFNULL( M_ApiMonthlyFeeTaxOther, 0 ) )            AS M_ApiMonthlyFeeTaxOther
                    ,SUM( IFNULL( M_CreditNoticeMonthlyFeeOther, 0 ) )      AS M_CreditNoticeMonthlyFeeOther
                    ,SUM( IFNULL( M_CreditNoticeMonthlyFeeTaxOther, 0 ) )   AS M_CreditNoticeMonthlyFeeTaxOther
                    ,SUM( IFNULL( M_NCreditNoticeMonthlyFeeOther, 0 ) )     AS M_NCreditNoticeMonthlyFeeOther
                    ,SUM( IFNULL( M_NCreditNoticeMonthlyFeeTaxOther, 0 ) )  AS M_NCreditNoticeMonthlyFeeTaxOther
                    ,SUM( IFNULL( M_ReserveMonthlyFeeOther, 0 ) )           AS M_ReserveMonthlyFeeOther
                    ,SUM( IFNULL( M_ReserveMonthlyFeeTaxOther, 0 ) )        AS M_ReserveMonthlyFeeTaxOther
                    ,SUM( IFNULL( M_AddClaimFeeOther, 0 ) )                 AS M_AddClaimFeeOther
                    ,SUM( IFNULL( M_AddClaimFeeTaxOther, 0 ) )              AS M_AddClaimFeeTaxOther
                    ,SUM( IFNULL( M_DamageInterestAmountOther, 0 ) )        AS M_DamageInterestAmountOther
                FROM  AW_DailyStatisticsTable
                GROUP BY EnterpriseId ) AS aw_dst
    LEFT JOIN( SELECT
                     EnterpriseId                                           AS AT_DSD_EnterpriseId
                    ,SUM( IFNULL( SettlementFee, 0 ) )                      AS SettlementFee
                    ,SUM( IFNULL( ClaimFee, 0 ) )                           AS ClaimFee
                    ,SUM( IFNULL( ClaimFeeTax, 0 ) )                        AS ClaimFeeTax
                    ,SUM( IFNULL( MonthlyFee, 0 ) )                         AS MonthlyFee
                    ,SUM( IFNULL( MonthlyFeeTax, 0 ) )                      AS MonthlyFeeTax
                    ,SUM( IFNULL( IncludeMonthlyFee, 0 ) )                  AS IncludeMonthlyFee
                    ,SUM( IFNULL( IncludeMonthlyFeeTax, 0 ) )               AS IncludeMonthlyFeeTax
                    ,SUM( IFNULL( ApiMonthlyFee, 0 ) )                      AS ApiMonthlyFee
                    ,SUM( IFNULL( ApiMonthlyFeeTax, 0 ) )                   AS ApiMonthlyFeeTax
                    ,SUM( IFNULL( CreditNoticeMonthlyFee, 0 ) )             AS CreditNoticeMonthlyFee
                    ,SUM( IFNULL( CreditNoticeMonthlyFeeTax, 0 ) )          AS CreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( NCreditNoticeMonthlyFee, 0 ) )            AS NCreditNoticeMonthlyFee
                    ,SUM( IFNULL( NCreditNoticeMonthlyFeeTax, 0 ) )         AS NCreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( ReserveMonthlyFee, 0 ) )                  AS ReserveMonthlyFee
                    ,SUM( IFNULL( ReserveMonthlyFeeTax, 0 ) )               AS ReserveMonthlyFeeTax
                FROM  AT_Daily_SalesDetails
                WHERE ProcessingDate = v_BusinessDate
                AND   AccountDate    = DATE_ADD( v_AccountingMonth, INTERVAL 1 MONTH )
                AND   SalesDefiniteConditions in ('着荷確認日', '入金日', '役務提供日', 'キャンセル日', 'キャンセル確定日', '月額固定費')
                AND      ValidFlg    = 1
                AND      OemId       = 0
                GROUP BY EnterpriseId ) AS at_dsd
    ON  at_dsd.AT_DSD_EnterpriseId = aw_dst.EnterpriseId
    LEFT JOIN( SELECT
                     EnterpriseId                                           AS AT_RFS_EnterpriseId
                    ,SUM( IFNULL( Clm_L_ClaimFee, 0 ) )                     AS Clm_L_ClaimFee
                    ,SUM( IFNULL( Clm_L_ClaimFeeTax, 0 ) )                  AS Clm_L_ClaimFeeTax
                    ,SUM( IFNULL( Clm_L_DamageInterestAmount, 0 ) )         AS Clm_L_DamageInterestAmount
                FROM AT_ReissueFeeSpecification
                WHERE ProcessingDate = v_BusinessDate
                AND   AccountDate    = DATE_ADD( v_AccountingMonth, INTERVAL 1 MONTH )
                AND   ValidFlg       = 1
                -- 直営 + 債権移管されたもの（OemId が 0 以外で、業務日付より前に債権移管がなされているもの）が抽出対象 ↓↓↓
                AND   (OemId = 0 OR (OemId <> 0 AND OemTransferDate IS NOT NULL AND OemTransferDate < v_BusinessDate))
                GROUP BY EnterpriseId ) AS at_rfs
    ON  at_rfs.AT_RFS_EnterpriseId = aw_dst.EnterpriseId
    LEFT JOIN  T_Enterprise ent
    ON  ent.EnterpriseId           = aw_dst.EnterpriseId;

END
$$

DELIMITER ;

