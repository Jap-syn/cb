DROP PROCEDURE IF EXISTS P_Treas_Oem_DailyStatisticsTable_Day;

DELIMITER $$

CREATE PROCEDURE P_Treas_Oem_DailyStatisticsTable_Day ( IN pi_user_id INT )
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名       ：  P_Treas_Oem_DailyStatisticsTable_Day
 *
 * 概要             ：  OEM日次統計表（日次）
 *
 * 引数             ：  [I/ ]pi_user_id        ﾕｰｻﾞｰID
 *
 * 履歴             ：  2015/10/01  NDC 新規作成
 *
 * 備考             ：  税込金額､消費税額､税抜金額の計算方法（消費税率は8 → つまり､0.08は消費税率ではない！！！）
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

    -- ------------------------------
    -- 変数初期化
    -- ------------------------------
    SET v_BusinessDate      = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'BusinessDate');       -- 業務日付
    SET v_AccountingMonth   = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'AccountingMonth');    -- 会計月

    -- ------------------------------
    -- ﾜｰｸﾃｰﾌﾞﾙの初期化
    -- ------------------------------
    -- 日別OEM日次統計表ﾜｰｸをﾄﾗﾝｹｰﾄする
    TRUNCATE AW_Oem_Daily2StatisticsTable;

    -- ------------------------------
    -- 1.1.詳細設計（日次売上明細の「OEM売上」より）
    -- ------------------------------
    INSERT INTO AW_Oem_Daily2StatisticsTable (
         ProcessingDate                                             -- 算出日
        ,AccountDate                                                -- 会計月
        ,OemId                                                      -- OEMID
        ,EnterpriseId                                               -- 加盟店ID
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
        ,D_SettlementFee                                            -- 当日OEM決済手数料
        ,D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,D_ClaimFee                                                 -- 当日OEM請求手数料
        ,D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,D_MonthlyFee                                               -- 当日OEM月額固定費
        ,D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        )
    SELECT
         v_BusinessDate                                             -- 算出日
        ,CASE
            WHEN DATE_FORMAT( at_pas.ATUriDay, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            THEN v_AccountingMonth
            ELSE DATE_FORMAT( at_pas.ATUriDay, '%Y-%m-01' )
         END                                                        -- 会計月
        ,c1.Class3                                                  -- OEMID
        ,o.EnterpriseId                                             -- 加盟店ID
        ,1                                                          -- 当日立替件数
        ,pas.UseAmount                                              -- 当日立替金額
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
        -- ありえないとは思うけど念のため対応･･･請求手数料がﾏｲﾅｽの場合にはﾌﾟﾗｽ変換して計算 → ﾏｲﾅｽへ戻す。
        ,CASE WHEN IFNULL(osf.SettlementFee, 0) < 0
            THEN ((osf.SettlementFee * -1) - FLOOR((osf.SettlementFee * -1) * F_GetTaxRate(DATE(at_pas.ATUriDay)) / (100 + F_GetTaxRate(DATE(at_pas.ATUriDay))))) * -1
            ELSE osf.SettlementFee - FLOOR(osf.SettlementFee * F_GetTaxRate(DATE(at_pas.ATUriDay)) / (100 + F_GetTaxRate(DATE(at_pas.ATUriDay))))
         END                                                        -- 当日OEM決済手数料
        ,CASE WHEN IFNULL(osf.SettlementFee, 0) < 0
            THEN (FLOOR((osf.SettlementFee * -1) * F_GetTaxRate(DATE(at_pas.ATUriDay)) / (100 + F_GetTaxRate(DATE(at_pas.ATUriDay))))) * -1
            ELSE FLOOR(osf.SettlementFee * F_GetTaxRate(DATE(at_pas.ATUriDay)) / (100 + F_GetTaxRate(DATE(at_pas.ATUriDay))))
         END                                                        -- 当日OEM決済手数料消費税
        ,CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0
            THEN ((ocf.ClaimFee * -1) - FLOOR((ocf.ClaimFee * -1) * F_GetTaxRate(DATE(at_pas.ATUriDay)) / (100 + F_GetTaxRate(DATE(at_pas.ATUriDay))))) * -1
            ELSE ocf.ClaimFee - FLOOR(ocf.ClaimFee * F_GetTaxRate(DATE(at_pas.ATUriDay)) / (100 + F_GetTaxRate(DATE(at_pas.ATUriDay))))
         END                                                        -- 当日OEM請求手数料
        ,CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0
            THEN (FLOOR((ocf.ClaimFee * -1) * F_GetTaxRate(DATE(at_pas.ATUriDay)) / (100 + F_GetTaxRate(DATE(at_pas.ATUriDay))))) * -1
            ELSE FLOOR(ocf.ClaimFee * F_GetTaxRate(DATE(at_pas.ATUriDay)) / (100 + F_GetTaxRate(DATE(at_pas.ATUriDay))))
         END                                                        -- 当日OEM請求手数料消費税
        ,null                                                       -- 当日OEM月額固定費
        ,null                                                       -- 当日OEM月額固定費消費税
        ,null                                                       -- 当日OEM同梱月額固定費
        ,null                                                       -- 当日OEM同梱月額固定費消費税
        ,null                                                       -- 当日OEMAPI月額固定費
        ,null                                                       -- 当日OEMAPI月額固定費消費税
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM月額固定費予備
        ,null                                                       -- 当日OEM月額固定費予備消費税
        ,null                                                       -- 当日再発行手数料
        ,null                                                       -- 当日再発行手数料消費税
        ,null                                                       -- 当日遅延損害金
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
    FROM        T_PayingAndSales pas
    INNER JOIN  AT_PayingAndSales at_pas
    ON      at_pas.Seq  = pas.Seq
    INNER JOIN  T_Order o
    ON      o.OrderSeq  = pas.OrderSeq
    LEFT OUTER JOIN  T_ClaimControl cc
    ON      cc.OrderSeq = o.P_OrderSeq
    LEFT JOIN  T_OemSettlementFee osf
    ON      osf.OrderSeq = o.OrderSeq
    LEFT JOIN  T_OemClaimFee ocf
    ON      ocf.OrderSeq = o.OrderSeq
    INNER JOIN  M_Code c1
    ON      c1.CodeId   = 160
    AND     c1.KeyCode  = IFNULL( o.OemId, 0 )
    LEFT OUTER JOIN T_Cancel cncl
    ON      cncl.OrderSeq = o.OrderSeq
    WHERE   pas.ClearConditionForCharge = 1
    AND     DATE_FORMAT(at_pas.ATUriDay, '%Y-%m-%d') <= v_BusinessDate
    AND     at_pas.DailySummaryFlg      = 0
    AND     pas.ValidFlg                = 1
    AND     c1.Class1                   <> 0
    AND     (cncl.ApprovalDate IS NULL OR DATE(at_pas.ATUriDay) <> DATE(cncl.ApprovalDate));

    -- ------------------------------
    -- 1.2.詳細設計（日次売上明細の「OEMｷｬﾝｾﾙ」より）
    -- ------------------------------
    INSERT INTO AW_Oem_Daily2StatisticsTable (
         ProcessingDate                                             -- 算出日
        ,AccountDate                                                -- 会計月
        ,OemId                                                      -- OEMID
        ,EnterpriseId                                               -- 加盟店ID
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
        ,D_SettlementFee                                            -- 当日OEM決済手数料
        ,D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,D_ClaimFee                                                 -- 当日OEM請求手数料
        ,D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,D_MonthlyFee                                               -- 当日OEM月額固定費
        ,D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        )
    SELECT
         v_BusinessDate                                             -- 算出日
        ,CASE
            WHEN DATE_FORMAT( cnl.ApprovalDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            THEN v_AccountingMonth
            ELSE DATE_FORMAT( cnl.ApprovalDate, '%Y-%m-01' )
         END                                                        -- 会計月
        ,c1.Class3                                                  -- OEMID
        ,o.EnterpriseId                                             -- 加盟店ID
        ,null                                                       -- 当日立替件数
        ,null                                                       -- 当日立替金額
        ,1                                                          -- 当日ｷｬﾝｾﾙ件数
        ,pas.UseAmount * -1                                         -- 当日ｷｬﾝｾﾙ立替金額
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
        ,null                                                       -- 当日OEM決済手数料
        ,null                                                       -- 当日OEM決済手数料消費税
        ,null                                                       -- 当日OEM請求手数料
        ,null                                                       -- 当日OEM請求手数料消費税
        ,null                                                       -- 当日OEM月額固定費
        ,null                                                       -- 当日OEM月額固定費消費税
        ,null                                                       -- 当日OEM同梱月額固定費
        ,null                                                       -- 当日OEM同梱月額固定費消費税
        ,null                                                       -- 当日OEMAPI月額固定費
        ,null                                                       -- 当日OEMAPI月額固定費消費税
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM月額固定費予備
        ,null                                                       -- 当日OEM月額固定費予備消費税
        ,null                                                       -- 当日再発行手数料
        ,null                                                       -- 当日再発行手数料消費税
        ,null                                                       -- 当日遅延損害金
        -- ありえないとは思うけど念のため対応･･･請求手数料がﾏｲﾅｽの場合にはﾌﾟﾗｽ変換して計算 → ﾏｲﾅｽへ戻す。
        ,CASE WHEN IFNULL(osf.SettlementFee, 0) < 0
            THEN (((osf.SettlementFee * -1) - FLOOR((osf.SettlementFee * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1) * -1
            ELSE (osf.SettlementFee - FLOOR(osf.SettlementFee * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
         END                                                        -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,CASE WHEN IFNULL(osf.SettlementFee, 0) < 0
            THEN (FLOOR((osf.SettlementFee * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))) * -1) * -1
            ELSE FLOOR(osf.SettlementFee * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))) * -1
         END                                                        -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0
            THEN (((ocf.ClaimFee * -1) - FLOOR((ocf.ClaimFee * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1) * -1
            ELSE (ocf.ClaimFee - FLOOR(ocf.ClaimFee * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
         END                                                        -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0
            THEN (FLOOR((ocf.ClaimFee * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))) * -1) * -1
            ELSE FLOOR(ocf.ClaimFee * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))) * -1
         END                                                        -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
    FROM        T_Cancel cnl
    INNER JOIN  T_Order o
    ON      o.OrderSeq      = cnl.OrderSeq
    INNER JOIN  T_PayingAndSales pas
    ON      pas.OrderSeq    = cnl.OrderSeq
    INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
    LEFT OUTER JOIN  T_ClaimControl cc
    ON      cc.OrderSeq     = o.P_OrderSeq
    LEFT JOIN  T_OemSettlementFee osf
    ON      osf.OrderSeq = o.OrderSeq
    LEFT JOIN  T_OemClaimFee ocf
    ON      ocf.OrderSeq = o.OrderSeq
    INNER JOIN  M_Code c1
    ON      c1.CodeId       = 160
    AND     c1.KeyCode      = IFNULL( o.OemId, 0 )
    WHERE   cnl.ApproveFlg              = 1                                 -- ｷｬﾝｾﾙ承認ﾌﾗｸﾞ
    AND     DATE_FORMAT( cnl.ApprovalDate, '%Y-%m-%d' ) <= v_BusinessDate   -- ｷｬﾝｾﾙ承認日 =< 業務日付が対象
    AND     cnl.DailySummaryFlg         = 0                                 -- 日別更新ﾌﾗｸﾞ=0 
    AND     cnl.ValidFlg                = 1
    AND     pas.ClearConditionForCharge = 1
    AND     c1.Class1                   <> 0
    AND     (cnl.ApprovalDate IS NULL OR DATE(apas.ATUriDay) <> DATE(cnl.ApprovalDate));

    -- ------------------------------
    -- 1.3.詳細設計（日次売上明細の「OEM月額固定費」より）
    -- ------------------------------
    INSERT INTO AW_Oem_Daily2StatisticsTable (
         ProcessingDate                                             -- 算出日
        ,AccountDate                                                -- 会計月
        ,OemId                                                      -- OEMID
        ,EnterpriseId                                               -- 加盟店ID
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
        ,D_SettlementFee                                            -- 当日OEM決済手数料
        ,D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,D_ClaimFee                                                 -- 当日OEM請求手数料
        ,D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,D_MonthlyFee                                               -- 当日OEM月額固定費
        ,D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        )
    SELECT
         v_BusinessDate                                             -- 算出日
        ,emci.ClosingMonthly                                        -- 会計月
        ,c1.Class3                                                  -- OEMID
        ,emci.EnterpriseId                                          -- 加盟店ID
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
        ,null                                                       -- 当日OEM決済手数料
        ,null                                                       -- 当日OEM決済手数料消費税
        ,null                                                       -- 当日OEM請求手数料
        ,null                                                       -- 当日OEM請求手数料消費税
        ,emci.OemMonthlyFee                                         -- 当日OEM月額固定費
        ,emci.OemMonthlyFeeTax                                      -- 当日OEM月額固定費消費税
        ,emci.OemIncludeMonthlyFee                                  -- 当日OEM同梱月額固定費
        ,emci.OemIncludeMonthlyFeeTax                               -- 当日OEM同梱月額固定費消費税
        ,emci.OemApiMonthlyFee                                      -- 当日OEMAPI月額固定費
        ,emci.OemApiMonthlyFeeTax                                   -- 当日OEMAPI月額固定費消費税
        ,emci.OemCreditNoticeMonthlyFee                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,emci.OemCreditNoticeMonthlyFeeTax                          -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,emci.OemNCreditNoticeMonthlyFee                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,emci.OemNCreditNoticeMonthlyFeeTax                         -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,emci.OemReserveMonthlyFee                                  -- 当日OEM月額固定費予備
        ,emci.OemReserveMonthlyFeeTax                               -- 当日OEM月額固定費予備消費税
        ,null                                                       -- 当日再発行手数料
        ,null                                                       -- 当日再発行手数料消費税
        ,null                                                       -- 当日遅延損害金
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
    FROM    AT_EnterpriseMonthlyClosingInfo emci
    INNER JOIN  M_Code c1
    ON      c1.CodeId       = 160
    AND     c1.KeyCode      = IFNULL( emci.OemId, 0 )
    WHERE   emci.ValidFlg             = 1
    AND     emci.DailySummaryFlg      = 0
    AND     emci.ClosingMonthly <= v_BusinessDate
    AND     emci.OemMonthlyFee + emci.OemMonthlyFeeTax +
            emci.OemIncludeMonthlyFee + emci.OemIncludeMonthlyFeeTax +
            emci.OemApiMonthlyFee + emci.OemApiMonthlyFeeTax +
            emci.OemCreditNoticeMonthlyFee + emci.OemCreditNoticeMonthlyFeeTax +
            emci.OemNCreditNoticeMonthlyFee + emci.OemNCreditNoticeMonthlyFeeTax +
            emci.OemReserveMonthlyFee + emci.OemReserveMonthlyFeeTax > 0
    AND     c1.Class1                 <> 0;

    -- ------------------------------
    -- 1.5.詳細設計（OEM移管明細より）
    -- ------------------------------
    INSERT INTO AW_Oem_Daily2StatisticsTable (
         ProcessingDate                                             -- 算出日
        ,AccountDate                                                -- 会計月
        ,OemId                                                      -- OEMID
        ,EnterpriseId                                               -- 加盟店ID
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
        ,D_SettlementFee                                            -- 当日OEM決済手数料
        ,D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,D_ClaimFee                                                 -- 当日OEM請求手数料
        ,D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,D_MonthlyFee                                               -- 当日OEM月額固定費
        ,D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        )
    SELECT
         v_BusinessDate                                             -- 算出日
        ,CASE
            WHEN DATE_FORMAT( o.OemClaimTransDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            THEN v_AccountingMonth
            ELSE DATE_FORMAT( o.OemClaimTransDate, '%Y-%m-01' )
         END                                                        -- 会計月
        ,c1.Class3                                                  -- OEMID
        ,o.EnterpriseId                                             -- 加盟店ID
        ,null                                                       -- 当日立替件数
        ,null                                                       -- 当日立替金額
        ,null                                                       -- 当日ｷｬﾝｾﾙ件数
        ,null                                                       -- 当日ｷｬﾝｾﾙ立替金額
        ,null                                                       -- 当日立替金戻し件数
        ,null                                                       -- 当日立替金戻し金額
        ,1                                                          -- 当日OEM移管件数
        ,cc.BalanceUseAmount * -1                                   -- 当日OEM移管金額
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
        ,null                                                       -- 当日OEM決済手数料
        ,null                                                       -- 当日OEM決済手数料消費税
        ,null                                                       -- 当日OEM請求手数料
        ,null                                                       -- 当日OEM請求手数料消費税
        ,null                                                       -- 当日OEM月額固定費
        ,null                                                       -- 当日OEM月額固定費消費税
        ,null                                                       -- 当日OEM同梱月額固定費
        ,null                                                       -- 当日OEM同梱月額固定費消費税
        ,null                                                       -- 当日OEMAPI月額固定費
        ,null                                                       -- 当日OEMAPI月額固定費消費税
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM月額固定費予備
        ,null                                                       -- 当日OEM月額固定費予備消費税
        ,null                                                       -- 当日再発行手数料
        ,null                                                       -- 当日再発行手数料消費税
        ,null                                                       -- 当日遅延損害金
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
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
    -- 1.5.詳細設計（OEM移管明細より_ｷｬﾝｾﾙ分の出力）
    -- ------------------------------
    INSERT INTO AW_Oem_Daily2StatisticsTable (
            ProcessingDate                                             -- 算出日
        ,   AccountDate                                                -- 会計月
        ,   OemId                                                      -- OEMID
        ,   EnterpriseId                                               -- 加盟店ID
        ,   D_ChargeCount                                              -- 当日立替件数
        ,   D_ChargeAmount                                             -- 当日立替金額
        ,   D_CancelCount                                              -- 当日ｷｬﾝｾﾙ件数
        ,   D_CancelAmount                                             -- 当日ｷｬﾝｾﾙ立替金額
        ,   D_SettlementBackCount                                      -- 当日立替金戻し件数
        ,   D_SettlementBackAmount                                     -- 当日立替金戻し金額
        ,   D_OemTransferCount                                         -- 当日OEM移管件数
        ,   D_OemTransferAmount                                        -- 当日OEM移管金額
        ,   D_ReClaimFeeCount                                          -- 当日再発行手数料件数
        ,   D_ReClaimFeeAmount                                         -- 当日再発行手数料額
        ,   D_DamageCount                                              -- 当日遅延損害金件数
        ,   D_DamageAmount                                             -- 当日遅延損害金額
        ,   D_ReceiptCount                                             -- 当日入金件数
        ,   D_ReceiptAmount                                            -- 当日入金額
        ,   D_RepayCount                                               -- 当日返金件数
        ,   D_RepayAmount                                              -- 当日返金額
        ,   D_BadDebtCount                                             -- 当日貸倒件数
        ,   D_BadDebtAmount                                            -- 当日貸倒金額
        ,   D_OtherPaymentCount                                        -- 当日その他件数
        ,   D_OtherPaymentAmount                                       -- 当日その他金額
        ,   D_SettlementFee                                            -- 当日OEM決済手数料
        ,   D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,   D_ClaimFee                                                 -- 当日OEM請求手数料
        ,   D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,   D_MonthlyFee                                               -- 当日OEM月額固定費
        ,   D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,   D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,   D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,   D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,   D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,   D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,   D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,   D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,   D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,   D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,   D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,   D_AddClaimFee                                              -- 当日再発行手数料
        ,   D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,   D_DamageInterestAmount                                     -- 当日遅延損害金
        ,   D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,   D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,   D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,   D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        )
    SELECT  v_BusinessDate                                             -- 算出日
        ,   CASE
               WHEN DATE_FORMAT(can.ApprovalDate, '%Y-%m') < DATE_FORMAT(v_AccountingMonth, '%Y-%m') THEN v_AccountingMonth
               ELSE DATE_FORMAT(can.ApprovalDate, '%Y-%m-01')
            END                                                        -- 会計月
        ,   c1.Class3                                                  -- OEMID
        ,   o.EnterpriseId                                             -- 加盟店ID
        ,   null                                                       -- 当日立替件数
        ,   null                                                       -- 当日立替金額
        ,   null                                                       -- 当日ｷｬﾝｾﾙ件数
        ,   null                                                       -- 当日ｷｬﾝｾﾙ立替金額
        ,   null                                                       -- 当日立替金戻し件数
        ,   null                                                       -- 当日立替金戻し金額
        ,   1                                                          -- 当日OEM移管件数
        ,   cc.BalanceUseAmount                                        -- 当日OEM移管金額
        ,   null                                                       -- 当日再発行手数料件数
        ,   null                                                       -- 当日再発行手数料額
        ,   null                                                       -- 当日遅延損害金件数
        ,   null                                                       -- 当日遅延損害金額
        ,   null                                                       -- 当日入金件数
        ,   null                                                       -- 当日入金額
        ,   null                                                       -- 当日返金件数
        ,   null                                                       -- 当日返金額
        ,   null                                                       -- 当日貸倒件数
        ,   null                                                       -- 当日貸倒金額
        ,   null                                                       -- 当日その他件数
        ,   null                                                       -- 当日その他金額
        ,   null                                                       -- 当日OEM決済手数料
        ,   null                                                       -- 当日OEM決済手数料消費税
        ,   null                                                       -- 当日OEM請求手数料
        ,   null                                                       -- 当日OEM請求手数料消費税
        ,   null                                                       -- 当日OEM月額固定費
        ,   null                                                       -- 当日OEM月額固定費消費税
        ,   null                                                       -- 当日OEM同梱月額固定費
        ,   null                                                       -- 当日OEM同梱月額固定費消費税
        ,   null                                                       -- 当日OEMAPI月額固定費
        ,   null                                                       -- 当日OEMAPI月額固定費消費税
        ,   null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,   null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,   null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,   null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,   null                                                       -- 当日OEM月額固定費予備
        ,   null                                                       -- 当日OEM月額固定費予備消費税
        ,   null                                                       -- 当日再発行手数料
        ,   null                                                       -- 当日再発行手数料消費税
        ,   null                                                       -- 当日遅延損害金
        ,   null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,   null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,   null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,   null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
    FROM    T_Order o
            INNER JOIN  T_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq)
            INNER JOIN T_Cancel can ON (can.OrderSeq = o.OrderSeq)
            INNER JOIN  M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL( o.OemId, 0 ))
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
    INSERT INTO AW_Oem_Daily2StatisticsTable (
            ProcessingDate                                             -- 算出日
        ,   AccountDate                                                -- 会計月
        ,   OemId                                                      -- OEMID
        ,   EnterpriseId                                               -- 加盟店ID
        ,   D_ChargeCount                                              -- 当日立替件数
        ,   D_ChargeAmount                                             -- 当日立替金額
        ,   D_CancelCount                                              -- 当日ｷｬﾝｾﾙ件数
        ,   D_CancelAmount                                             -- 当日ｷｬﾝｾﾙ立替金額
        ,   D_SettlementBackCount                                      -- 当日立替金戻し件数
        ,   D_SettlementBackAmount                                     -- 当日立替金戻し金額
        ,   D_OemTransferCount                                         -- 当日OEM移管件数
        ,   D_OemTransferAmount                                        -- 当日OEM移管金額
        ,   D_ReClaimFeeCount                                          -- 当日再発行手数料件数
        ,   D_ReClaimFeeAmount                                         -- 当日再発行手数料額
        ,   D_DamageCount                                              -- 当日遅延損害金件数
        ,   D_DamageAmount                                             -- 当日遅延損害金額
        ,   D_ReceiptCount                                             -- 当日入金件数
        ,   D_ReceiptAmount                                            -- 当日入金額
        ,   D_RepayCount                                               -- 当日返金件数
        ,   D_RepayAmount                                              -- 当日返金額
        ,   D_BadDebtCount                                             -- 当日貸倒件数
        ,   D_BadDebtAmount                                            -- 当日貸倒金額
        ,   D_OtherPaymentCount                                        -- 当日その他件数
        ,   D_OtherPaymentAmount                                       -- 当日その他金額
        ,   D_SettlementFee                                            -- 当日OEM決済手数料
        ,   D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,   D_ClaimFee                                                 -- 当日OEM請求手数料
        ,   D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,   D_MonthlyFee                                               -- 当日OEM月額固定費
        ,   D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,   D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,   D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,   D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,   D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,   D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,   D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,   D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,   D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,   D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,   D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,   D_AddClaimFee                                              -- 当日再発行手数料
        ,   D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,   D_DamageInterestAmount                                     -- 当日遅延損害金
        ,   D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,   D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,   D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,   D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        )
    SELECT  v_BusinessDate                                             -- 算出日
        ,   CASE
               WHEN DATE_FORMAT(o.OemClaimTransDate, '%Y-%m') < DATE_FORMAT(v_AccountingMonth, '%Y-%m') THEN v_AccountingMonth
               ELSE DATE_FORMAT(o.OemClaimTransDate, '%Y-%m-01')
            END                                                        -- 会計月
        ,   c1.Class3                                                  -- OEMID
        ,   o.EnterpriseId                                             -- 加盟店ID
        ,   null                                                       -- 当日立替件数
        ,   null                                                       -- 当日立替金額
        ,   null                                                       -- 当日ｷｬﾝｾﾙ件数
        ,   null                                                       -- 当日ｷｬﾝｾﾙ立替金額
        ,   null                                                       -- 当日立替金戻し件数
        ,   null                                                       -- 当日立替金戻し金額
        ,   1                                                          -- 当日OEM移管件数
        ,   SUM(rc.CheckingUseAmount)                                  -- 当日OEM移管金額
        ,   null                                                       -- 当日再発行手数料件数
        ,   null                                                       -- 当日再発行手数料額
        ,   null                                                       -- 当日遅延損害金件数
        ,   null                                                       -- 当日遅延損害金額
        ,   null                                                       -- 当日入金件数
        ,   null                                                       -- 当日入金額
        ,   null                                                       -- 当日返金件数
        ,   null                                                       -- 当日返金額
        ,   null                                                       -- 当日貸倒件数
        ,   null                                                       -- 当日貸倒金額
        ,   null                                                       -- 当日その他件数
        ,   null                                                       -- 当日その他金額
        ,   null                                                       -- 当日OEM決済手数料
        ,   null                                                       -- 当日OEM決済手数料消費税
        ,   null                                                       -- 当日OEM請求手数料
        ,   null                                                       -- 当日OEM請求手数料消費税
        ,   null                                                       -- 当日OEM月額固定費
        ,   null                                                       -- 当日OEM月額固定費消費税
        ,   null                                                       -- 当日OEM同梱月額固定費
        ,   null                                                       -- 当日OEM同梱月額固定費消費税
        ,   null                                                       -- 当日OEMAPI月額固定費
        ,   null                                                       -- 当日OEMAPI月額固定費消費税
        ,   null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,   null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,   null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,   null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,   null                                                       -- 当日OEM月額固定費予備
        ,   null                                                       -- 当日OEM月額固定費予備消費税
        ,   null                                                       -- 当日再発行手数料
        ,   null                                                       -- 当日再発行手数料消費税
        ,   null                                                       -- 当日遅延損害金
        ,   null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,   null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,   null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,   null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
    FROM    T_Order o
            INNER JOIN  T_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq)
            INNER JOIN T_ReceiptControl rc ON (rc.OrderSeq = o.OrderSeq AND rc.ValidFlg = 1)
            INNER JOIN  M_Code c1 ON (c1.CodeId = 160 AND c1.KeyCode = IFNULL( o.OemId, 0 ))
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
    -- 1.7.①詳細設計（再発行手数料明細より）
    -- ------------------------------
    -- 1) 再発行手数料件数を取得
    INSERT INTO AW_Oem_Daily2StatisticsTable (
         ProcessingDate                                             -- 算出日
        ,AccountDate                                                -- 会計月
        ,OemId                                                      -- OEMID
        ,EnterpriseId                                               -- 加盟店ID
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
        ,D_SettlementFee                                            -- 当日OEM決済手数料
        ,D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,D_ClaimFee                                                 -- 当日OEM請求手数料
        ,D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,D_MonthlyFee                                               -- 当日OEM月額固定費
        ,D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        )
    SELECT
         v_BusinessDate                                             -- 算出日
        ,CASE
            WHEN DATE_FORMAT( rc.ReceiptDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            THEN v_AccountingMonth
            ELSE DATE_FORMAT( rc.ReceiptDate, '%Y-%m-01' )
         END                                                        -- 会計月
        ,c1.Class3                                                  -- OEMID
        ,o.EnterpriseId                                             -- 加盟店ID
        ,null                                                       -- 当日立替件数
        ,null                                                       -- 当日立替金額
        ,null                                                       -- 当日ｷｬﾝｾﾙ件数
        ,null                                                       -- 当日ｷｬﾝｾﾙ立替金額
        ,null                                                       -- 当日立替金戻し件数
        ,null                                                       -- 当日立替金戻し金額
        ,null                                                       -- 当日OEM移管件数
        ,null                                                       -- 当日OEM移管金額
        ,1                                                          -- 当日再発行手数料件数
        ,rc.CheckingClaimFee                                        -- 当日再発行手数料額
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
        ,null                                                       -- 当日OEM決済手数料
        ,null                                                       -- 当日OEM決済手数料消費税
        ,null                                                       -- 当日OEM請求手数料
        ,null                                                       -- 当日OEM請求手数料消費税
        ,null                                                       -- 当日OEM月額固定費
        ,null                                                       -- 当日OEM月額固定費消費税
        ,null                                                       -- 当日OEM同梱月額固定費
        ,null                                                       -- 当日OEM同梱月額固定費消費税
        ,null                                                       -- 当日OEMAPI月額固定費
        ,null                                                       -- 当日OEMAPI月額固定費消費税
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM月額固定費予備
        ,null                                                       -- 当日OEM月額固定費予備消費税
        -- 入金管理から取得している場合、ﾏｲﾅｽがありえる → 入金取消された場合、ﾏｲﾅｽのﾃﾞｰﾀが発生 → 計算する際にはﾌﾟﾗｽにし、その後ﾏｲﾅｽに戻す
        ,CASE WHEN rc.CheckingClaimFee < 0
            THEN ((rc.CheckingClaimFee * -1) - FLOOR((rc.CheckingClaimFee * -1) * F_GetTaxRate(rc.ReceiptDate) / (100 + F_GetTaxRate(rc.ReceiptDate)))) * -1
            ELSE rc.CheckingClaimFee - FLOOR(rc.CheckingClaimFee * F_GetTaxRate(rc.ReceiptDate) / (100 + F_GetTaxRate(rc.ReceiptDate)))
         END                                                        -- 当日再発行手数料
        ,CASE WHEN rc.CheckingClaimFee < 0
            THEN (FLOOR((rc.CheckingClaimFee * -1) * F_GetTaxRate(rc.ReceiptDate) / (100 + F_GetTaxRate(rc.ReceiptDate)))) * -1
            ELSE FLOOR(rc.CheckingClaimFee * F_GetTaxRate(rc.ReceiptDate) / (100 + F_GetTaxRate(rc.ReceiptDate)))
         END                                                        -- 当日再発行手数料消費税
        ,null                                                       -- 当日遅延損害金
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
    FROM        T_ReceiptControl rc
    INNER JOIN  T_Order o
    ON      o.OrderSeq  = rc.OrderSeq
    AND     (o.OemClaimTransDate IS NULL OR ( o.OemClaimTransDate IS NOT NULL AND o.OemClaimTransDate > rc.ReceiptDate ) )
    INNER JOIN  M_Code c1
    ON      c1.CodeId   = 160
    AND     c1.KeyCode  = IFNULL( o.OemId, 0 )
    WHERE   rc.DailySummaryFlg  = 0                                 -- 日次更新ﾌﾗｸﾞ=0 
    AND     DATE_FORMAT( rc.ReceiptProcessDate, '%Y-%m-%d' ) <= v_BusinessDate                 -- 入金管理.入金処理日=<業務日付
    AND     IFNULL(rc.CheckingClaimFee, 0) <> 0						-- 入金管理.消込情報-請求手数料 <> 0
    AND     rc.ValidFlg        =  1
    AND     c1.Class1          <> 0;

    -- 2) 遅延損害金件数を取得
    INSERT INTO AW_Oem_Daily2StatisticsTable (
         ProcessingDate                                             -- 算出日
        ,AccountDate                                                -- 会計月
        ,OemId                                                      -- OEMID
        ,EnterpriseId                                               -- 加盟店ID
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
        ,D_SettlementFee                                            -- 当日OEM決済手数料
        ,D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,D_ClaimFee                                                 -- 当日OEM請求手数料
        ,D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,D_MonthlyFee                                               -- 当日OEM月額固定費
        ,D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        )
    SELECT
         v_BusinessDate                                             -- 算出日
        ,CASE
            WHEN DATE_FORMAT( rc.ReceiptDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            THEN v_AccountingMonth
            ELSE DATE_FORMAT( rc.ReceiptDate, '%Y-%m-01' )
         END                                                        -- 会計月
        ,c1.Class3                                                  -- OEMID
        ,o.EnterpriseId                                             -- 加盟店ID
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
        ,1                                                          -- 当日遅延損害金件数
        ,rc.CheckingDamageInterestAmount                            -- 当日遅延損害金額
        ,null                                                       -- 当日入金件数
        ,null                                                       -- 当日入金額
        ,null                                                       -- 当日返金件数
        ,null                                                       -- 当日返金額
        ,null                                                       -- 当日貸倒件数
        ,null                                                       -- 当日貸倒金額
        ,null                                                       -- 当日その他件数
        ,null                                                       -- 当日その他金額
        ,null                                                       -- 当日OEM決済手数料
        ,null                                                       -- 当日OEM決済手数料消費税
        ,null                                                       -- 当日OEM請求手数料
        ,null                                                       -- 当日OEM請求手数料消費税
        ,null                                                       -- 当日OEM月額固定費
        ,null                                                       -- 当日OEM月額固定費消費税
        ,null                                                       -- 当日OEM同梱月額固定費
        ,null                                                       -- 当日OEM同梱月額固定費消費税
        ,null                                                       -- 当日OEMAPI月額固定費
        ,null                                                       -- 当日OEMAPI月額固定費消費税
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM月額固定費予備
        ,null                                                       -- 当日OEM月額固定費予備消費税
        ,null                                                       -- 当日再発行手数料
        ,null                                                       -- 当日再発行手数料消費税
        ,rc.CheckingDamageInterestAmount                            -- 当日遅延損害金
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
    FROM        T_ReceiptControl rc
    INNER JOIN  T_Order o
    ON      o.OrderSeq  = rc.OrderSeq
    AND     (o.OemClaimTransDate IS NULL OR ( o.OemClaimTransDate IS NOT NULL AND o.OemClaimTransDate > rc.ReceiptDate ) )
    INNER JOIN  M_Code c1
    ON      c1.CodeId   = 160
    AND     c1.KeyCode  = IFNULL( o.OemId, 0 )
    WHERE   rc.DailySummaryFlg  = 0                                 -- 日次更新ﾌﾗｸﾞ=0 
    AND     DATE_FORMAT( rc.ReceiptProcessDate, '%Y-%m-%d' ) <= v_BusinessDate                 -- 入金管理.入金処理日=<業務日付
    AND     IFNULL(rc.CheckingDamageInterestAmount, 0) <> 0         -- 入金管理.消込情報-遅延損害金 <> 0
    AND     rc.ValidFlg        =  1
    AND     c1.Class1          <> 0;

    -- ------------------------------
    -- 1.8. 詳細設計（未収金日計の「■入金管理より作成」①より）
    -- ------------------------------
    INSERT INTO AW_Oem_Daily2StatisticsTable (
         ProcessingDate                                             -- 算出日
        ,AccountDate                                                -- 会計月
        ,OemId                                                      -- OEMID
        ,EnterpriseId                                               -- 加盟店ID
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
        ,D_SettlementFee                                            -- 当日OEM決済手数料
        ,D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,D_ClaimFee                                                 -- 当日OEM請求手数料
        ,D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,D_MonthlyFee                                               -- 当日OEM月額固定費
        ,D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        )
    SELECT
         v_BusinessDate                                             -- 算出日
        ,CASE
            WHEN DATE_FORMAT( spd.PaymentDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            THEN v_AccountingMonth
            ELSE DATE_FORMAT( spd.PaymentDate, '%Y-%m-01' )
         END                                                        -- 会計月
        ,c1.Class3                                                  -- OEMID
        ,o.EnterpriseId                                             -- 加盟店ID
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
        ,spd.PaymentNumber                                          -- 当日入金件数
        ,spd.Amount * -1                                            -- 当日入金額(消込情報-利用額 + 消込情報-請求手数料 + 消込情報-遅延損害金)
        ,null                                                       -- 当日返金件数
        ,null                                                       -- 当日返金額
        ,null                                                       -- 当日貸倒件数
        ,null                                                       -- 当日貸倒金額
        ,null                                                       -- 当日その他件数
        ,null                                                       -- 当日その他金額
        ,null                                                       -- 当日OEM決済手数料
        ,null                                                       -- 当日OEM決済手数料消費税
        ,null                                                       -- 当日OEM請求手数料
        ,null                                                       -- 当日OEM請求手数料消費税
        ,null                                                       -- 当日OEM月額固定費
        ,null                                                       -- 当日OEM月額固定費消費税
        ,null                                                       -- 当日OEM同梱月額固定費
        ,null                                                       -- 当日OEM同梱月額固定費消費税
        ,null                                                       -- 当日OEMAPI月額固定費
        ,null                                                       -- 当日OEMAPI月額固定費消費税
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM月額固定費予備
        ,null                                                       -- 当日OEM月額固定費予備消費税
        ,null                                                       -- 当日再発行手数料
        ,null                                                       -- 当日再発行手数料消費税
        ,null                                                       -- 当日遅延損害金
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
    FROM       AW_SuspensePayments_DailyAccount spd
    INNER JOIN  T_Order o
    ON      o.OrderSeq = spd.OrderSeq
    INNER JOIN  M_Code c1
    ON      c1.CodeId     = 160
    AND     c1.KeyCode    = IFNULL( o.OemId, 0 )
    ;

    -- ------------------------------
    -- 1.10.詳細設計（仮払金日計の「■返金管理より作成」①より）
    -- ------------------------------
    INSERT INTO AW_Oem_Daily2StatisticsTable (
         ProcessingDate                                             -- 算出日
        ,AccountDate                                                -- 会計月
        ,OemId                                                      -- OEMID
        ,EnterpriseId                                               -- 加盟店ID
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
        ,D_SettlementFee                                            -- 当日OEM決済手数料
        ,D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,D_ClaimFee                                                 -- 当日OEM請求手数料
        ,D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,D_MonthlyFee                                               -- 当日OEM月額固定費
        ,D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        )
    SELECT
         v_BusinessDate                                             -- 算出日
        ,CASE
            WHEN DATE_FORMAT( rc.DecisionDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            THEN v_AccountingMonth
            ELSE DATE_FORMAT( rc.DecisionDate, '%Y-%m-01' )
         END                                                        -- 会計月
        ,c1.Class3                                                  -- OEMID
        ,o.EnterpriseId                                             -- 加盟店ID
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
        ,1                                                          -- 当日返金件数
        ,rc.RepayAmount                                             -- 当日返金額
        ,null                                                       -- 当日貸倒件数
        ,null                                                       -- 当日貸倒金額
        ,null                                                       -- 当日その他件数
        ,null                                                       -- 当日その他金額
        ,null                                                       -- 当日OEM決済手数料
        ,null                                                       -- 当日OEM決済手数料消費税
        ,null                                                       -- 当日OEM請求手数料
        ,null                                                       -- 当日OEM請求手数料消費税
        ,null                                                       -- 当日OEM月額固定費
        ,null                                                       -- 当日OEM月額固定費消費税
        ,null                                                       -- 当日OEM同梱月額固定費
        ,null                                                       -- 当日OEM同梱月額固定費消費税
        ,null                                                       -- 当日OEMAPI月額固定費
        ,null                                                       -- 当日OEMAPI月額固定費消費税
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM月額固定費予備
        ,null                                                       -- 当日OEM月額固定費予備消費税
        ,null                                                       -- 当日再発行手数料
        ,null                                                       -- 当日再発行手数料消費税
        ,null                                                       -- 当日遅延損害金
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
    FROM        T_RepaymentControl rc
    INNER JOIN  AT_RepaymentControl at_rc
    ON     at_rc.RepaySeq = rc.RepaySeq
    INNER JOIN  T_ClaimControl cc
    ON     cc.ClaimId     = rc.ClaimId
    INNER JOIN  T_Order o
    ON     o.OrderSeq     = cc.OrderSeq
    AND   ( o.OemClaimTransDate IS NULL OR ( o.OemClaimTransDate IS NOT NULL AND o.OemClaimTransDate > DATE_FORMAT( rc.DecisionDate, '%Y-%m-%d' ) ) )
    INNER JOIN  M_Code c1
    ON     c1.CodeId      = 160
    AND    c1.KeyCode     = IFNULL( o.OemId, 0 )
    WHERE  at_rc.DailySummaryFlg    =  0
    AND    rc.ValidFlg              =  1
    AND    rc.RepayStatus           =  1
    AND    rc.RepayAmount           <> 0
    AND     DATE_FORMAT( rc.DecisionDate, '%Y-%m-%d' ) <= v_BusinessDate
    AND    c1.Class1                <>  0;

    -- ------------------------------
    -- 1.10.1 加盟店への返金加算情報の出力
    -- ------------------------------
    INSERT INTO AW_Oem_Daily2StatisticsTable (
         ProcessingDate                                             -- 算出日
        ,AccountDate                                                -- 会計月
        ,OemId                                                      -- OEMID
        ,EnterpriseId                                               -- 加盟店ID
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
        ,D_SettlementFee                                            -- 当日OEM決済手数料
        ,D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,D_ClaimFee                                                 -- 当日OEM請求手数料
        ,D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,D_MonthlyFee                                               -- 当日OEM月額固定費
        ,D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        )
    SELECT
         v_BusinessDate                                             -- 算出日
        ,CASE
            WHEN DATE_FORMAT(ProcessingDate, '%Y-%m') < DATE_FORMAT(v_AccountingMonth, '%Y-%m') THEN v_AccountingMonth
            ELSE DATE_FORMAT(ProcessingDate, '%Y-%m-01')
         END                                                        -- 会計月
        ,OemId                                                      -- OEMID
        ,EnterpriseId                                               -- 加盟店ID
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
        ,EnterpriseRefundCount                                      -- 当日返金件数
        ,EnterpriseRefund                                           -- 当日返金額
        ,null                                                       -- 当日貸倒件数
        ,null                                                       -- 当日貸倒金額
        ,null                                                       -- 当日その他件数
        ,null                                                       -- 当日その他金額
        ,null                                                       -- 当日OEM決済手数料
        ,null                                                       -- 当日OEM決済手数料消費税
        ,null                                                       -- 当日OEM請求手数料
        ,null                                                       -- 当日OEM請求手数料消費税
        ,null                                                       -- 当日OEM月額固定費
        ,null                                                       -- 当日OEM月額固定費消費税
        ,null                                                       -- 当日OEM同梱月額固定費
        ,null                                                       -- 当日OEM同梱月額固定費消費税
        ,null                                                       -- 当日OEMAPI月額固定費
        ,null                                                       -- 当日OEMAPI月額固定費消費税
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM月額固定費予備
        ,null                                                       -- 当日OEM月額固定費予備消費税
        ,null                                                       -- 当日再発行手数料
        ,null                                                       -- 当日再発行手数料消費税
        ,null                                                       -- 当日遅延損害金
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
    FROM   AT_PayOff_DailyAccount
    WHERE  ValidFlg         =  1
    AND    ProcessingDate   = v_BusinessDate
    AND    OemId            <> 0
    AND    EnterpriseRefund <> 0
    ;

    -- ------------------------------
    -- 1.12.詳細設計（仮払金日計の「■雑収入損失作成」①より）
    -- ------------------------------
    INSERT INTO AW_Oem_Daily2StatisticsTable (
         ProcessingDate                                             -- 算出日
        ,AccountDate                                                -- 会計月
        ,OemId                                                      -- OEMID
        ,EnterpriseId                                               -- 加盟店ID
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
        ,D_SettlementFee                                            -- 当日OEM決済手数料
        ,D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,D_ClaimFee                                                 -- 当日OEM請求手数料
        ,D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,D_MonthlyFee                                               -- 当日OEM月額固定費
        ,D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        )
    SELECT
         v_BusinessDate                                             -- 算出日
        ,CASE
            WHEN DATE_FORMAT( sc.ProcessDate, '%Y-%m' ) < DATE_FORMAT( v_AccountingMonth, '%Y-%m' )
            THEN v_AccountingMonth
            ELSE DATE_FORMAT( sc.ProcessDate, '%Y-%m-01' )
         END                                                        -- 会計月
        ,c2.Class3                                                  -- OEMID
        ,o.EnterpriseId                                             -- 加盟店ID
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
        ,1                                                          -- 当日その他件数
        ,CASE WHEN sc.SundryType = 0
            THEN sc.SundryAmount
            ELSE sc.SundryAmount * -1
         END                                                        -- 当日その他金額
        ,null                                                       -- 当日OEM決済手数料
        ,null                                                       -- 当日OEM決済手数料消費税
        ,null                                                       -- 当日OEM請求手数料
        ,null                                                       -- 当日OEM請求手数料消費税
        ,null                                                       -- 当日OEM月額固定費
        ,null                                                       -- 当日OEM月額固定費消費税
        ,null                                                       -- 当日OEM同梱月額固定費
        ,null                                                       -- 当日OEM同梱月額固定費消費税
        ,null                                                       -- 当日OEMAPI月額固定費
        ,null                                                       -- 当日OEMAPI月額固定費消費税
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM月額固定費予備
        ,null                                                       -- 当日OEM月額固定費予備消費税
        ,null                                                       -- 当日再発行手数料
        ,null                                                       -- 当日再発行手数料消費税
        ,null                                                       -- 当日遅延損害金
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
    FROm        T_SundryControl sc
    INNER JOIN  T_Order o
    ON     o.OrderSeq   = sc.OrderSeq
    AND  ( o.OemClaimTransDate IS NULL OR ( o.OemClaimTransDate IS NOT NULL AND o.OemClaimTransDate > sc.ProcessDate ) )
    INNER JOIN  M_Code c1
    ON     c1.CodeId    = 160
    AND    c1.KeyCode   = IFNULL( o.OemId, 0 )
    INNER JOIN  M_Code c2
    ON     c2.CodeId    = 96
    AND    c2.KeyCode   = sc.SundryClass
    WHERE  sc.DailySummaryFlg =  0
    AND    sc.ValidFlg        =  1
    AND    sc.SundryAmount    <> 0
    AND    sc.ProcessDate <= v_BusinessDate
    AND    c1.Class1          <> 0
    AND    c2.Class2          =  1;

    -- ------------------------------
    -- 1.14.詳細設計（日別OEM日次統計表の作成）
    -- ------------------------------
    INSERT INTO AT_Oem_Daily2StatisticsTable (
         ProcessingDate                                             -- 算出日
        ,AccountDate                                                -- 会計月
        ,OemId                                                      -- OEMID
        ,EnterpriseId                                               -- 加盟店ID
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
        ,D_SettlementFee                                            -- 当日OEM決済手数料
        ,D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,D_ClaimFee                                                 -- 当日OEM請求手数料
        ,D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,D_MonthlyFee                                               -- 当日OEM月額固定費
        ,D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        ,RegistDate                                                 -- 登録日時
        ,RegistId                                                   -- 登録者
        ,UpdateDate                                                 -- 更新日時
        ,UpdateId                                                   -- 更新者
        ,ValidFlg                                                   -- 有効ﾌﾗｸﾞ
        )
    SELECT
         ProcessingDate                                             -- 算出日
        ,AccountDate                                                -- 会計月
        ,OemId                                                      -- OEMID
        ,EnterpriseId                                               -- 加盟店ID
        ,SUM( IFNULL( D_ChargeCount, 0 ) )                          -- 当日立替件数
        ,SUM( IFNULL( D_ChargeAmount, 0 ) )                         -- 当日立替金額
        ,SUM( IFNULL( D_CancelCount, 0 ) )                          -- 当日ｷｬﾝｾﾙ件数
        ,SUM( IFNULL( D_CancelAmount, 0 ) )                         -- 当日ｷｬﾝｾﾙ立替金額
        ,SUM( IFNULL( D_SettlementBackCount, 0 ) )                  -- 当日立替金戻し件数
        ,SUM( IFNULL( D_SettlementBackAmount, 0 ) )                 -- 当日立替金戻し金額
        ,SUM( IFNULL( D_OemTransferCount, 0 ) )                     -- 当日OEM移管件数
        ,SUM( IFNULL( D_OemTransferAmount, 0 ) )                    -- 当日OEM移管金額
        ,SUM( IFNULL( D_ReClaimFeeCount, 0 ) )                      -- 当日再発行手数料件数
        ,SUM( IFNULL( D_ReClaimFeeAmount, 0 ) )                     -- 当日再発行手数料額
        ,SUM( IFNULL( D_DamageCount, 0 ) )                          -- 当日遅延損害金件数
        ,SUM( IFNULL( D_DamageAmount, 0 ) )                         -- 当日遅延損害金額
        ,SUM( IFNULL( D_ReceiptCount, 0 ) )                         -- 当日入金件数
        ,SUM( IFNULL( D_ReceiptAmount, 0 ) )                        -- 当日入金額
        ,SUM( IFNULL( D_RepayCount, 0 ) )                           -- 当日返金件数
        ,SUM( IFNULL( D_RepayAmount, 0 ) )                          -- 当日返金額
        ,SUM( IFNULL( D_BadDebtCount, 0 ) )                         -- 当日貸倒件数
        ,SUM( IFNULL( D_BadDebtAmount, 0 ) )                        -- 当日貸倒金額
        ,SUM( IFNULL( D_OtherPaymentCount, 0 ) )                    -- 当日その他件数
        ,SUM( IFNULL( D_OtherPaymentAmount, 0 ) )                   -- 当日その他金額
        ,SUM( IFNULL( D_SettlementFee, 0 ) )                        -- 当日OEM決済手数料
        ,SUM( IFNULL( D_SettlementFeeTax, 0 ) )                     -- 当日OEM決済手数料消費税
        ,SUM( IFNULL( D_ClaimFee, 0 ) )                             -- 当日OEM請求手数料
        ,SUM( IFNULL( D_ClaimFeeTax, 0 ) )                          -- 当日OEM請求手数料消費税
        ,SUM( IFNULL( D_MonthlyFee, 0 ) )                           -- 当日OEM月額固定費
        ,SUM( IFNULL( D_MonthlyFeeTax, 0 ) )                        -- 当日OEM月額固定費消費税
        ,SUM( IFNULL( D_OemIncludeMonthlyFee, 0 ) )                 -- 当日OEM同梱月額固定費
        ,SUM( IFNULL( D_OemIncludeMonthlyFeeTax, 0 ) )              -- 当日OEM同梱月額固定費消費税
        ,SUM( IFNULL( D_OemApiMonthlyFee, 0 ) )                     -- 当日OEMAPI月額固定費
        ,SUM( IFNULL( D_OemApiMonthlyFeeTax, 0 ) )                  -- 当日OEMAPI月額固定費消費税
        ,SUM( IFNULL( D_OemCreditNoticeMonthlyFee, 0 ) )            -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM( IFNULL( D_OemCreditNoticeMonthlyFeeTax, 0 ) )         -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM( IFNULL( D_OemNCreditNoticeMonthlyFee, 0 ) )           -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM( IFNULL( D_OemNCreditNoticeMonthlyFeeTax, 0 ) )        -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM( IFNULL( D_OemReserveMonthlyFee, 0 ) )                 -- 当日OEM月額固定費予備
        ,SUM( IFNULL( D_OemReserveMonthlyFeeTax, 0 ) )              -- 当日OEM月額固定費予備消費税
        ,SUM( IFNULL( D_AddClaimFee, 0 ) )                          -- 当日再発行手数料
        ,SUM( IFNULL( D_AddClaimFeeTax, 0 ) )                       -- 当日再発行手数料消費税
        ,SUM( IFNULL( D_DamageInterestAmount, 0 ) )                 -- 当日遅延損害金
        ,SUM( IFNULL( D_CanSettlementFee, 0 ) )                     -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,SUM( IFNULL( D_CanSettlementFeeTax, 0 ) )                  -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,SUM( IFNULL( D_CanClaimFee, 0 ) )                          -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,SUM( IFNULL( D_CanClaimFeeTax, 0 ) )                       -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        ,NOW()                                                      -- 登録日時
        ,pi_user_id                                                 -- 登録者
        ,NOW()                                                      -- 更新日時
        ,pi_user_id                                                 -- 更新者
        ,1                                                          -- 有効ﾌﾗｸﾞ
    FROM  AW_Oem_Daily2StatisticsTable
    GROUP BY  ProcessingDate                                        -- 集計日
             ,AccountDate                                           -- 会計月
             ,OemId                                                 -- OemId
             ,EnterpriseId;                                         -- 加盟店ID

    -- ------------------------------
    -- 2.1.OEM日次統計表ﾜｰｸをﾄﾗﾝｹｰﾄする
    -- ------------------------------
    TRUNCATE AW_Oem_DailyStatisticsTable;

    -- ------------------------------
    -- 2.2.業務日付=日別OEM日次統計表.集計日のﾃﾞｰﾀを集計出力し日次用のOEM日次統計表ﾜｰｸを出力する
    -- ------------------------------
    INSERT INTO AW_Oem_DailyStatisticsTable (
         ProcessingDate                                             -- 集計日
        ,AccountDate                                                -- 会計月
        ,OemId                                                      -- OEMID
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
        ,D_SettlementFee                                            -- 当日OEM決済手数料
        ,D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,D_ClaimFee                                                 -- 当日OEM請求手数料
        ,D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,D_MonthlyFee                                               -- 当日OEM月額固定費
        ,D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        ,D_SettlementFeeOther                                       -- 当日売上明細OEM決済手数料
        ,D_SettlementFeeTaxOther                                    -- 当日売上明細OEM決済手数料消費税
        ,D_ClaimFeeOther                                            -- 当日売上明細OEM請求手数料
        ,D_ClaimFeeTaxOther                                         -- 当日売上明細OEM請求手数料消費税
        ,D_MonthlyFeeOther                                          -- 当日売上明細OEM月額固定費
        ,D_MonthlyFeeTaxOther                                       -- 当日売上明細OEM月額固定費消費税
        ,D_OemIncludeMonthlyFeeOther                                -- 当日売上明細OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTaxOther                             -- 当日売上明細OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFeeOther                                    -- 当日売上明細OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTaxOther                                 -- 当日売上明細OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFeeOther                           -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTaxOther                        -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFeeOther                          -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTaxOther                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFeeOther                                -- 当日売上明細OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTaxOther                             -- 当日売上明細OEM月額固定費予備消費税
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
        ,M_OemTransferCount                                         -- 当月OEM移管件数
        ,M_OemTransferAmount                                        -- 当月OEM移管金額
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
        ,M_SuspensePaymentsAmount                                   -- OEM仮払金明細残高
        ,M_AccountsReceivableBalanceDiff                            -- 当月差額
        ,M_SettlementFee                                            -- 当月OEM決済手数料
        ,M_SettlementFeeTax                                         -- 当月OEM決済手数料消費税
        ,M_ClaimFee                                                 -- 当月OEM請求手数料
        ,M_ClaimFeeTax                                              -- 当月OEM請求手数料消費税
        ,M_MonthlyFee                                               -- 当月OEM月額固定費
        ,M_MonthlyFeeTax                                            -- 当月OEM月額固定費消費税
        ,M_OemIncludeMonthlyFee                                     -- 当月OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTax                                  -- 当月OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFee                                         -- 当月OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTax                                      -- 当月OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFee                                -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTax                             -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFee                               -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTax                            -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFee                                     -- 当月OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTax                                  -- 当月OEM月額固定費予備消費税
        ,M_AddClaimFee                                              -- 当月再発行手数料
        ,M_AddClaimFeeTax                                           -- 当月再発行手数料消費税
        ,M_DamageInterestAmount                                     -- 当月遅延損害金
        ,M_CanSettlementFee                                         -- 当月ｷｬﾝｾﾙOEM決済手数料
        ,M_CanSettlementFeeTax                                      -- 当月ｷｬﾝｾﾙOEM決済手数料消費税
        ,M_CanClaimFee                                              -- 当月ｷｬﾝｾﾙOEM請求手数料
        ,M_CanClaimFeeTax                                           -- 当月ｷｬﾝｾﾙOEM請求手数料消費税
        ,M_SettlementFeeOther                                       -- 当月売上明細OEM決済手数料
        ,M_SettlementFeeTaxOther                                    -- 当月売上明細OEM決済手数料消費税
        ,M_ClaimFeeOther                                            -- 当月売上明細OEM請求手数料
        ,M_ClaimFeeTaxOther                                         -- 当月売上明細OEM請求手数料消費税
        ,M_MonthlyFeeOther                                          -- 当月売上明細OEM月額固定費
        ,M_MonthlyFeeTaxOther                                       -- 当月売上明細OEM月額固定費消費税
        ,M_OemIncludeMonthlyFeeOther                                -- 当月売上明細OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTaxOther                             -- 当月売上明細OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFeeOther                                    -- 当月売上明細OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTaxOther                                 -- 当月売上明細OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFeeOther                           -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTaxOther                        -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFeeOther                          -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTaxOther                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFeeOther                                -- 当月売上明細OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTaxOther                             -- 当月売上明細OEM月額固定費予備消費税
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
        ,OemId                                                      -- OEMID
        ,EnterpriseId                                               -- 加盟店ID
        ,null                                                       -- 前日時点未収金残高
        ,SUM( IFNULL( D_ChargeCount,0 ) )                           -- 当日立替件数
        ,SUM( IFNULL( D_ChargeAmount,0 ) )                          -- 当日立替金額
        ,SUM( IFNULL( D_CancelCount,0 ) )                           -- 当日ｷｬﾝｾﾙ件数
        ,SUM( IFNULL( D_CancelAmount,0 ) )                          -- 当日ｷｬﾝｾﾙ立替金額
        ,SUM( IFNULL( D_SettlementBackCount,0 ) )                   -- 当日立替金戻し件数
        ,SUM( IFNULL( D_SettlementBackAmount,0 ) )                  -- 当日立替金戻し金額
        ,SUM( IFNULL( D_OemTransferCount,0 ) )                      -- 当日OEM移管件数
        ,SUM( IFNULL( D_OemTransferAmount,0 ) )                     -- 当日OEM移管金額
        ,SUM( IFNULL( D_ReClaimFeeCount,0 ) )                       -- 当日再発行手数料件数
        ,SUM( IFNULL( D_ReClaimFeeAmount,0 ) )                      -- 当日再発行手数料額
        ,SUM( IFNULL( D_DamageCount,0 ) )                           -- 当日遅延損害金件数
        ,SUM( IFNULL( D_DamageAmount,0 ) )                          -- 当日遅延損害金額
        ,SUM( IFNULL( D_ReceiptCount,0 ) )                          -- 当日入金件数
        ,SUM( IFNULL( D_ReceiptAmount,0 ) )                         -- 当日入金額
        ,SUM( IFNULL( D_RepayCount,0 ) )                            -- 当日返金件数
        ,SUM( IFNULL( D_RepayAmount,0 ) )                           -- 当日返金額
        ,SUM( IFNULL( D_BadDebtCount,0 ) )                          -- 当日貸倒件数
        ,SUM( IFNULL( D_BadDebtAmount,0 ) )                         -- 当日貸倒金額
        ,SUM( IFNULL( D_OtherPaymentCount,0 ) )                     -- 当日その他件数
        ,SUM( IFNULL( D_OtherPaymentAmount,0 ) )                    -- 当日その他金額
        ,null                                                       -- 当日時点未収金残高
        ,SUM( IFNULL( D_SettlementFee, 0 ) )                        -- 当日OEM決済手数料
        ,SUM( IFNULL( D_SettlementFeeTax, 0 ) )                     -- 当日OEM決済手数料消費税
        ,SUM( IFNULL( D_ClaimFee, 0 ) )                             -- 当日OEM請求手数料
        ,SUM( IFNULL( D_ClaimFeeTax, 0 ) )                          -- 当日OEM請求手数料消費税
        ,SUM( IFNULL( D_MonthlyFee, 0 ) )                           -- 当日OEM月額固定費
        ,SUM( IFNULL( D_MonthlyFeeTax, 0 ) )                        -- 当日OEM月額固定費消費税
        ,SUM( IFNULL( D_OemIncludeMonthlyFee, 0 ) )                 -- 当日OEM同梱月額固定費
        ,SUM( IFNULL( D_OemIncludeMonthlyFeeTax, 0 ) )              -- 当日OEM同梱月額固定費消費税
        ,SUM( IFNULL( D_OemApiMonthlyFee, 0 ) )                     -- 当日OEMAPI月額固定費
        ,SUM( IFNULL( D_OemApiMonthlyFeeTax, 0 ) )                  -- 当日OEMAPI月額固定費消費税
        ,SUM( IFNULL( D_OemCreditNoticeMonthlyFee, 0 ) )            -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM( IFNULL( D_OemCreditNoticeMonthlyFeeTax, 0 ) )         -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM( IFNULL( D_OemNCreditNoticeMonthlyFee, 0 ) )           -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM( IFNULL( D_OemNCreditNoticeMonthlyFeeTax, 0 ) )        -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM( IFNULL( D_OemReserveMonthlyFee, 0 ) )                 -- 当日OEM月額固定費予備
        ,SUM( IFNULL( D_OemReserveMonthlyFeeTax, 0 ) )              -- 当日OEM月額固定費予備消費税
        ,SUM( IFNULL( D_AddClaimFee, 0 ) )                          -- 当日再発行手数料
        ,SUM( IFNULL( D_AddClaimFeeTax, 0 ) )                       -- 当日再発行手数料消費税
        ,SUM( IFNULL( D_DamageInterestAmount, 0 ) )                 -- 当日遅延損害金
        ,SUM( IFNULL( D_CanSettlementFee, 0 ) )                     -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,SUM( IFNULL( D_CanSettlementFeeTax, 0 ) )                  -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,SUM( IFNULL( D_CanClaimFee, 0 ) )                          -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,SUM( IFNULL( D_CanClaimFeeTax, 0 ) )                       -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        ,null                                                       -- 当日売上明細OEM決済手数料
        ,null                                                       -- 当日売上明細OEM決済手数料消費税
        ,null                                                       -- 当日売上明細OEM請求手数料
        ,null                                                       -- 当日売上明細OEM請求手数料消費税
        ,null                                                       -- 当日売上明細OEM月額固定費
        ,null                                                       -- 当日売上明細OEM月額固定費消費税
        ,null                                                       -- 当日売上明細OEM同梱月額固定費
        ,null                                                       -- 当日売上明細OEM同梱月額固定費消費税
        ,null                                                       -- 当日売上明細OEMAPI月額固定費
        ,null                                                       -- 当日売上明細OEMAPI月額固定費消費税
        ,null                                                       -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日売上明細OEM月額固定費予備
        ,null                                                       -- 当日売上明細OEM月額固定費予備消費税
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
        ,null                                                       -- 当日再発行手数料件数
        ,null                                                       -- 当日再発行手数料額
        ,null                                                       -- 当日遅延損害金件数
        ,null                                                       -- 当日遅延損害金額
        ,null                                                       -- 当月入金件数
        ,null                                                       -- 当月入金額
        ,null                                                       -- 当月返金件数
        ,null                                                       -- 当月返金額
        ,null                                                       -- 当月貸倒件数
        ,null                                                       -- 当月貸倒金額
        ,null                                                       -- 当月その他件数
        ,null                                                       -- 当月その他金額
        ,null                                                       -- 当月時点未収金残高
        ,null                                                       -- OEM仮払金明細残高
        ,null                                                       -- 当月差額
        ,null                                                       -- 当月OEM決済手数料
        ,null                                                       -- 当月OEM決済手数料消費税
        ,null                                                       -- 当月OEM請求手数料
        ,null                                                       -- 当月OEM請求手数料消費税
        ,null                                                       -- 当月OEM月額固定費
        ,null                                                       -- 当月OEM月額固定費消費税
        ,null                                                       -- 当月OEM同梱月額固定費
        ,null                                                       -- 当月OEM同梱月額固定費消費税
        ,null                                                       -- 当月OEMAPI月額固定費
        ,null                                                       -- 当月OEMAPI月額固定費消費税
        ,null                                                       -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月OEM月額固定費予備
        ,null                                                       -- 当月OEM月額固定費予備消費税
        ,null                                                       -- 当月再発行手数料
        ,null                                                       -- 当月再発行手数料消費税
        ,null                                                       -- 当月遅延損害金
        ,null                                                       -- 当月ｷｬﾝｾﾙOEM決済手数料
        ,null                                                       -- 当月ｷｬﾝｾﾙOEM決済手数料消費税
        ,null                                                       -- 当月ｷｬﾝｾﾙOEM請求手数料
        ,null                                                       -- 当月ｷｬﾝｾﾙOEM請求手数料消費税
        ,null                                                       -- 当月売上明細OEM決済手数料
        ,null                                                       -- 当月売上明細OEM決済手数料消費税
        ,null                                                       -- 当月売上明細OEM請求手数料
        ,null                                                       -- 当月売上明細OEM請求手数料消費税
        ,null                                                       -- 当月売上明細OEM月額固定費
        ,null                                                       -- 当月売上明細OEM月額固定費消費税
        ,null                                                       -- 当月売上明細OEM同梱月額固定費
        ,null                                                       -- 当月売上明細OEM同梱月額固定費消費税
        ,null                                                       -- 当月売上明細OEMAPI月額固定費
        ,null                                                       -- 当月売上明細OEMAPI月額固定費消費税
        ,null                                                       -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細OEM月額固定費予備
        ,null                                                       -- 当月売上明細OEM月額固定費予備消費税
        ,null                                                       -- 当月手数料明細再発行手数料
        ,null                                                       -- 当月手数料明細再発行手数料消費税
        ,null                                                       -- 当月手数料明細遅延損害金
        ,NOW()                                                      -- 登録日時
        ,pi_user_id                                                 -- 登録者
        ,NOW()                                                      -- 更新日時
        ,pi_user_id                                                 -- 更新者
        ,1                                                          -- 有効ﾌﾗｸﾞ
    FROM  AT_Oem_Daily2StatisticsTable
    WHERE ProcessingDate = v_BusinessDate
    AND   AccountDate    = v_AccountingMonth
    GROUP BY  AccountDate                                           -- 会計月
             ,OemId                                                 -- OEMID
             ,EnterpriseId;                                         -- 加盟店ID


    -- ------------------------------
    -- 2.3.業務日付=日別OEM日次統計表.集計日のﾃﾞｰﾀを集計出力し日次用のOEM日次統計表ﾜｰｸを出力する
    -- ------------------------------
    INSERT INTO AW_Oem_DailyStatisticsTable (
         ProcessingDate                                             -- 集計日
        ,AccountDate                                                -- 会計月
        ,OemId                                                      -- OEMID
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
        ,D_SettlementFee                                            -- 当日OEM決済手数料
        ,D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,D_ClaimFee                                                 -- 当日OEM請求手数料
        ,D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,D_MonthlyFee                                               -- 当日OEM月額固定費
        ,D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        ,D_SettlementFeeOther                                       -- 当日売上明細OEM決済手数料
        ,D_SettlementFeeTaxOther                                    -- 当日売上明細OEM決済手数料消費税
        ,D_ClaimFeeOther                                            -- 当日売上明細OEM請求手数料
        ,D_ClaimFeeTaxOther                                         -- 当日売上明細OEM請求手数料消費税
        ,D_MonthlyFeeOther                                          -- 当日売上明細OEM月額固定費
        ,D_MonthlyFeeTaxOther                                       -- 当日売上明細OEM月額固定費消費税
        ,D_OemIncludeMonthlyFeeOther                                -- 当日売上明細OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTaxOther                             -- 当日売上明細OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFeeOther                                    -- 当日売上明細OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTaxOther                                 -- 当日売上明細OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFeeOther                           -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTaxOther                        -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFeeOther                          -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTaxOther                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFeeOther                                -- 当日売上明細OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTaxOther                             -- 当日売上明細OEM月額固定費予備消費税
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
        ,M_OemTransferCount                                         -- 当月OEM移管件数
        ,M_OemTransferAmount                                        -- 当月OEM移管金額
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
        ,M_SuspensePaymentsAmount                                   -- OEM仮払金明細残高
        ,M_AccountsReceivableBalanceDiff                            -- 当月差額
        ,M_SettlementFee                                            -- 当月OEM決済手数料
        ,M_SettlementFeeTax                                         -- 当月OEM決済手数料消費税
        ,M_ClaimFee                                                 -- 当月OEM請求手数料
        ,M_ClaimFeeTax                                              -- 当月OEM請求手数料消費税
        ,M_MonthlyFee                                               -- 当月OEM月額固定費
        ,M_MonthlyFeeTax                                            -- 当月OEM月額固定費消費税
        ,M_OemIncludeMonthlyFee                                     -- 当月OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTax                                  -- 当月OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFee                                         -- 当月OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTax                                      -- 当月OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFee                                -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTax                             -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFee                               -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTax                            -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFee                                     -- 当月OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTax                                  -- 当月OEM月額固定費予備消費税
        ,M_AddClaimFee                                              -- 当月再発行手数料
        ,M_AddClaimFeeTax                                           -- 当月再発行手数料消費税
        ,M_DamageInterestAmount                                     -- 当月遅延損害金
        ,M_CanSettlementFee                                         -- 当月ｷｬﾝｾﾙOEM決済手数料
        ,M_CanSettlementFeeTax                                      -- 当月ｷｬﾝｾﾙOEM決済手数料消費税
        ,M_CanClaimFee                                              -- 当月ｷｬﾝｾﾙOEM請求手数料
        ,M_CanClaimFeeTax                                           -- 当月ｷｬﾝｾﾙOEM請求手数料消費税
        ,M_SettlementFeeOther                                       -- 当月売上明細OEM決済手数料
        ,M_SettlementFeeTaxOther                                    -- 当月売上明細OEM決済手数料消費税
        ,M_ClaimFeeOther                                            -- 当月売上明細OEM請求手数料
        ,M_ClaimFeeTaxOther                                         -- 当月売上明細OEM請求手数料消費税
        ,M_MonthlyFeeOther                                          -- 当月売上明細OEM月額固定費
        ,M_MonthlyFeeTaxOther                                       -- 当月売上明細OEM月額固定費消費税
        ,M_OemIncludeMonthlyFeeOther                                -- 当月売上明細OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTaxOther                             -- 当月売上明細OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFeeOther                                    -- 当月売上明細OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTaxOther                                 -- 当月売上明細OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFeeOther                           -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTaxOther                        -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFeeOther                          -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTaxOther                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFeeOther                                -- 当月売上明細OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTaxOther                             -- 当月売上明細OEM月額固定費予備消費税
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
        ,OemId                                                      -- OEMID
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
        ,null                                                       -- 当日OEM決済手数料
        ,null                                                       -- 当日OEM決済手数料消費税
        ,null                                                       -- 当日OEM請求手数料
        ,null                                                       -- 当日OEM請求手数料消費税
        ,null                                                       -- 当日OEM月額固定費
        ,null                                                       -- 当日OEM月額固定費消費税
        ,null                                                       -- 当日OEM同梱月額固定費
        ,null                                                       -- 当日OEM同梱月額固定費消費税
        ,null                                                       -- 当日OEMAPI月額固定費
        ,null                                                       -- 当日OEMAPI月額固定費消費税
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM月額固定費予備
        ,null                                                       -- 当日OEM月額固定費予備消費税
        ,null                                                       -- 当日再発行手数料
        ,null                                                       -- 当日再発行手数料消費税
        ,null                                                       -- 当日遅延損害金
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        ,null                                                       -- 当日売上明細OEM決済手数料
        ,null                                                       -- 当日売上明細OEM決済手数料消費税
        ,null                                                       -- 当日売上明細OEM請求手数料
        ,null                                                       -- 当日売上明細OEM請求手数料消費税
        ,null                                                       -- 当日売上明細OEM月額固定費
        ,null                                                       -- 当日売上明細OEM月額固定費消費税
        ,null                                                       -- 当日売上明細OEM同梱月額固定費
        ,null                                                       -- 当日売上明細OEM同梱月額固定費消費税
        ,null                                                       -- 当日売上明細OEMAPI月額固定費
        ,null                                                       -- 当日売上明細OEMAPI月額固定費消費税
        ,null                                                       -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日売上明細OEM月額固定費予備
        ,null                                                       -- 当日売上明細OEM月額固定費予備消費税
        ,null                                                       -- 当日手数料明細再発行手数料
        ,null                                                       -- 当日手数料明細再発行手数料消費税
        ,null                                                       -- 当日手数料明細遅延損害金
        ,null                                                       -- 月初未収金残高
        ,SUM( IFNULL( D_ChargeCount,0 ) )                           -- 当月立替件数
        ,SUM( IFNULL( D_ChargeAmount,0 ) )                          -- 当月立替金額
        ,SUM( IFNULL( D_CancelCount,0 ) )                           -- 当月ｷｬﾝｾﾙ件数
        ,SUM( IFNULL( D_CancelAmount,0 ) )                          -- 当月ｷｬﾝｾﾙ立替金額
        ,SUM( IFNULL( D_SettlementBackCount,0 ) )                   -- 当月立替金戻し件数
        ,SUM( IFNULL( D_SettlementBackAmount,0 ) )                  -- 当月立替金戻し金額
        ,SUM( IFNULL( D_OemTransferCount,0 ) )                      -- 当月OEM移管件数
        ,SUM( IFNULL( D_OemTransferAmount,0 ) )                     -- 当月OEM移管金額
        ,SUM( IFNULL( D_ReClaimFeeCount,0 ) )                       -- 当月再発行手数料件数
        ,SUM( IFNULL( D_ReClaimFeeAmount,0 ) )                      -- 当月再発行手数料額
        ,SUM( IFNULL( D_DamageCount,0 ) )                           -- 当月遅延損害金件数
        ,SUM( IFNULL( D_DamageAmount,0 ) )                          -- 当月遅延損害金額
        ,SUM( IFNULL( D_ReceiptCount,0 ) )                          -- 当月入金件数
        ,SUM( IFNULL( D_ReceiptAmount,0 ) )                         -- 当月入金額
        ,SUM( IFNULL( D_RepayCount,0 ) )                            -- 当月返金件数
        ,SUM( IFNULL( D_RepayAmount,0 ) )                           -- 当月返金額
        ,SUM( IFNULL( D_BadDebtCount, 0) )                          -- 当月貸倒件数
        ,SUM( IFNULL( D_BadDebtAmount, 0) )                         -- 当月貸倒金額
        ,SUM( IFNULL( D_OtherPaymentCount, 0) )                     -- 当月その他件数
        ,SUM( IFNULL( D_OtherPaymentAmount, 0) )                    -- 当月その他金額
        ,null                                                       -- 当月時点未収金残高
        ,null                                                       -- OEM仮払金明細残高
        ,null                                                       -- 当月差額
        ,SUM( IFNULL( D_SettlementFee, 0 ) )                        -- 当月OEM決済手数料
        ,SUM( IFNULL( D_SettlementFeeTax, 0 ) )                     -- 当月OEM決済手数料消費税
        ,SUM( IFNULL( D_ClaimFee, 0 ) )                             -- 当月OEM請求手数料
        ,SUM( IFNULL( D_ClaimFeeTax, 0 ) )                          -- 当月OEM請求手数料消費税
        ,SUM( IFNULL( D_MonthlyFee, 0 ) )                           -- 当月OEM月額固定費
        ,SUM( IFNULL( D_MonthlyFeeTax, 0 ) )                        -- 当月OEM月額固定費消費税
        ,SUM( IFNULL( D_OemIncludeMonthlyFee, 0 ) )                 -- 当月OEM同梱月額固定費
        ,SUM( IFNULL( D_OemIncludeMonthlyFeeTax, 0 ) )              -- 当月OEM同梱月額固定費消費税
        ,SUM( IFNULL( D_OemApiMonthlyFee, 0 ) )                     -- 当月OEMAPI月額固定費
        ,SUM( IFNULL( D_OemApiMonthlyFeeTax, 0 ) )                  -- 当月OEMAPI月額固定費消費税
        ,SUM( IFNULL( D_OemCreditNoticeMonthlyFee, 0 ) )            -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM( IFNULL( D_OemCreditNoticeMonthlyFeeTax, 0 ) )         -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM( IFNULL( D_OemNCreditNoticeMonthlyFee, 0 ) )           -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM( IFNULL( D_OemNCreditNoticeMonthlyFeeTax, 0 ) )        -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM( IFNULL( D_OemReserveMonthlyFee, 0 ) )                 -- 当月OEM月額固定費予備
        ,SUM( IFNULL( D_OemReserveMonthlyFeeTax, 0 ) )              -- 当月OEM月額固定費予備消費税
        ,SUM( IFNULL( D_AddClaimFee, 0 ) )                          -- 当月再発行手数料
        ,SUM( IFNULL( D_AddClaimFeeTax, 0 ) )                       -- 当月再発行手数料消費税
        ,SUM( IFNULL( D_DamageInterestAmount, 0 ) )                 -- 当月遅延損害金
        ,SUM( IFNULL( D_CanSettlementFee, 0 ) )                     -- 当月ｷｬﾝｾﾙOEM決済手数料
        ,SUM( IFNULL( D_CanSettlementFeeTax, 0 ) )                  -- 当月ｷｬﾝｾﾙOEM決済手数料消費税
        ,SUM( IFNULL( D_CanClaimFee, 0 ) )                          -- 当月ｷｬﾝｾﾙOEM請求手数料
        ,SUM( IFNULL( D_CanClaimFeeTax, 0 ) )                       -- 当月ｷｬﾝｾﾙOEM請求手数料消費税
        ,null                                                       -- 当月売上明細OEM決済手数料
        ,null                                                       -- 当月売上明細OEM決済手数料消費税
        ,null                                                       -- 当月売上明細OEM請求手数料
        ,null                                                       -- 当月売上明細OEM請求手数料消費税
        ,null                                                       -- 当月売上明細OEM月額固定費
        ,null                                                       -- 当月売上明細OEM月額固定費消費税
        ,null                                                       -- 当月売上明細OEM同梱月額固定費
        ,null                                                       -- 当月売上明細OEM同梱月額固定費消費税
        ,null                                                       -- 当月売上明細OEMAPI月額固定費
        ,null                                                       -- 当月売上明細OEMAPI月額固定費消費税
        ,null                                                       -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細OEM月額固定費予備
        ,null                                                       -- 当月売上明細OEM月額固定費予備消費税
        ,null                                                       -- 当月手数料明細再発行手数料
        ,null                                                       -- 当月手数料明細再発行手数料消費税
        ,null                                                       -- 当月手数料明細遅延損害金
        ,NOW()                                                      -- 登録日時
        ,pi_user_id                                                 -- 登録者
        ,NOW()                                                      -- 更新日時
        ,pi_user_id                                                 -- 更新者
        ,1                                                          -- 有効ﾌﾗｸﾞ
    FROM  AT_Oem_Daily2StatisticsTable
    WHERE AccountDate    = v_AccountingMonth
    GROUP BY AccountDate                                    -- 会計月
            ,OemId                                          -- OEMID
            ,EnterpriseId;                                  -- 加盟店ID

    -- ------------------------------
    -- 2.4.加盟店別に業務日付 > OEM日次統計表.集計日、且つ、MAX（OEM日次統計表.集計日）【前日】
    --     のﾃﾞｰﾀを出力し日次用のOEM日次統計表ﾜｰｸを出力する
    -- ------------------------------
    INSERT INTO AW_Oem_DailyStatisticsTable (
         ProcessingDate                                             -- 集計日
        ,AccountDate                                                -- 会計月
        ,OemId                                                      -- OEMID
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
        ,D_SettlementFee                                            -- 当日OEM決済手数料
        ,D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,D_ClaimFee                                                 -- 当日OEM請求手数料
        ,D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,D_MonthlyFee                                               -- 当日OEM月額固定費
        ,D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        ,D_SettlementFeeOther                                       -- 当日売上明細OEM決済手数料
        ,D_SettlementFeeTaxOther                                    -- 当日売上明細OEM決済手数料消費税
        ,D_ClaimFeeOther                                            -- 当日売上明細OEM請求手数料
        ,D_ClaimFeeTaxOther                                         -- 当日売上明細OEM請求手数料消費税
        ,D_MonthlyFeeOther                                          -- 当日売上明細OEM月額固定費
        ,D_MonthlyFeeTaxOther                                       -- 当日売上明細OEM月額固定費消費税
        ,D_OemIncludeMonthlyFeeOther                                -- 当日売上明細OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTaxOther                             -- 当日売上明細OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFeeOther                                    -- 当日売上明細OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTaxOther                                 -- 当日売上明細OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFeeOther                           -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTaxOther                        -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFeeOther                          -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTaxOther                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFeeOther                                -- 当日売上明細OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTaxOther                             -- 当日売上明細OEM月額固定費予備消費税
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
        ,M_OemTransferCount                                         -- 当月OEM移管件数
        ,M_OemTransferAmount                                        -- 当月OEM移管金額
        ,M_ReClaimFeeCount                                          -- 当日再発行手数料件数
        ,M_ReClaimFeeAmount                                         -- 当日再発行手数料額
        ,M_DamageCount                                              -- 当日遅延損害金件数
        ,M_DamageAmount                                             -- 当日遅延損害金額
        ,M_ReceiptCount                                             -- 当月入金件数
        ,M_ReceiptAmount                                            -- 当月入金額
        ,M_RepayCount                                               -- 当月返金件数
        ,M_RepayAmount                                              -- 当月返金額
        ,M_BadDebtCount                                             -- 当月貸倒件数
        ,M_BadDebtAmount                                            -- 当月貸倒金額
        ,M_OtherPaymentCount                                        -- 当月その他件数
        ,M_OtherPaymentAmount                                       -- 当月その他金額
        ,M_AccountsReceivableBalance                                -- 当月時点未収金残高
        ,M_SuspensePaymentsAmount                                   -- OEM仮払金明細残高
        ,M_AccountsReceivableBalanceDiff                            -- 当月差額
        ,M_SettlementFee                                            -- 当月OEM決済手数料
        ,M_SettlementFeeTax                                         -- 当月OEM決済手数料消費税
        ,M_ClaimFee                                                 -- 当月OEM請求手数料
        ,M_ClaimFeeTax                                              -- 当月OEM請求手数料消費税
        ,M_MonthlyFee                                               -- 当月OEM月額固定費
        ,M_MonthlyFeeTax                                            -- 当月OEM月額固定費消費税
        ,M_OemIncludeMonthlyFee                                     -- 当月OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTax                                  -- 当月OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFee                                         -- 当月OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTax                                      -- 当月OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFee                                -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTax                             -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFee                               -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTax                            -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFee                                     -- 当月OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTax                                  -- 当月OEM月額固定費予備消費税
        ,M_AddClaimFee                                              -- 当月再発行手数料
        ,M_AddClaimFeeTax                                           -- 当月再発行手数料消費税
        ,M_DamageInterestAmount                                     -- 当月遅延損害金
        ,M_CanSettlementFee                                         -- 当月ｷｬﾝｾﾙOEM決済手数料
        ,M_CanSettlementFeeTax                                      -- 当月ｷｬﾝｾﾙOEM決済手数料消費税
        ,M_CanClaimFee                                              -- 当月ｷｬﾝｾﾙOEM請求手数料
        ,M_CanClaimFeeTax                                           -- 当月ｷｬﾝｾﾙOEM請求手数料消費税
        ,M_SettlementFeeOther                                       -- 当月売上明細OEM決済手数料
        ,M_SettlementFeeTaxOther                                    -- 当月売上明細OEM決済手数料消費税
        ,M_ClaimFeeOther                                            -- 当月売上明細OEM請求手数料
        ,M_ClaimFeeTaxOther                                         -- 当月売上明細OEM請求手数料消費税
        ,M_MonthlyFeeOther                                          -- 当月売上明細OEM月額固定費
        ,M_MonthlyFeeTaxOther                                       -- 当月売上明細OEM月額固定費消費税
        ,M_OemIncludeMonthlyFeeOther                                -- 当月売上明細OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTaxOther                             -- 当月売上明細OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFeeOther                                    -- 当月売上明細OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTaxOther                                 -- 当月売上明細OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFeeOther                           -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTaxOther                        -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFeeOther                          -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTaxOther                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFeeOther                                -- 当月売上明細OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTaxOther                             -- 当月売上明細OEM月額固定費予備消費税
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
        ,dst.OemId                                                  -- OEMID
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
        ,null                                                       -- 当日OEM決済手数料
        ,null                                                       -- 当日OEM決済手数料消費税
        ,null                                                       -- 当日OEM請求手数料
        ,null                                                       -- 当日OEM請求手数料消費税
        ,null                                                       -- 当日OEM月額固定費
        ,null                                                       -- 当日OEM月額固定費消費税
        ,null                                                       -- 当日OEM同梱月額固定費
        ,null                                                       -- 当日OEM同梱月額固定費消費税
        ,null                                                       -- 当日OEMAPI月額固定費
        ,null                                                       -- 当日OEMAPI月額固定費消費税
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM月額固定費予備
        ,null                                                       -- 当日OEM月額固定費予備消費税
        ,null                                                       -- 当日再発行手数料
        ,null                                                       -- 当日再発行手数料消費税
        ,null                                                       -- 当日遅延損害金
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        ,null                                                       -- 当日売上明細OEM決済手数料
        ,null                                                       -- 当日売上明細OEM決済手数料消費税
        ,null                                                       -- 当日売上明細OEM請求手数料
        ,null                                                       -- 当日売上明細OEM請求手数料消費税
        ,null                                                       -- 当日売上明細OEM月額固定費
        ,null                                                       -- 当日売上明細OEM月額固定費消費税
        ,null                                                       -- 当日売上明細OEM同梱月額固定費
        ,null                                                       -- 当日売上明細OEM同梱月額固定費消費税
        ,null                                                       -- 当日売上明細OEMAPI月額固定費
        ,null                                                       -- 当日売上明細OEMAPI月額固定費消費税
        ,null                                                       -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日売上明細OEM月額固定費予備
        ,null                                                       -- 当日売上明細OEM月額固定費予備消費税
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
        ,null                                                       -- OEM仮払金明細残高
        ,null                                                       -- 当月差額
        ,null                                                       -- 当月OEM決済手数料
        ,null                                                       -- 当月OEM決済手数料消費税
        ,null                                                       -- 当月OEM請求手数料
        ,null                                                       -- 当月OEM請求手数料消費税
        ,null                                                       -- 当月OEM月額固定費
        ,null                                                       -- 当月OEM月額固定費消費税
        ,null                                                       -- 当月OEM同梱月額固定費
        ,null                                                       -- 当月OEM同梱月額固定費消費税
        ,null                                                       -- 当月OEMAPI月額固定費
        ,null                                                       -- 当月OEMAPI月額固定費消費税
        ,null                                                       -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月OEM月額固定費予備
        ,null                                                       -- 当月OEM月額固定費予備消費税
        ,null                                                       -- 当月再発行手数料
        ,null                                                       -- 当月再発行手数料消費税
        ,null                                                       -- 当月遅延損害金
        ,null                                                       -- 当月ｷｬﾝｾﾙOEM決済手数料
        ,null                                                       -- 当月ｷｬﾝｾﾙOEM決済手数料消費税
        ,null                                                       -- 当月ｷｬﾝｾﾙOEM請求手数料
        ,null                                                       -- 当月ｷｬﾝｾﾙOEM請求手数料消費税
        ,null                                                       -- 当月売上明細OEM決済手数料
        ,null                                                       -- 当月売上明細OEM決済手数料消費税
        ,null                                                       -- 当月売上明細OEM請求手数料
        ,null                                                       -- 当月売上明細OEM請求手数料消費税
        ,null                                                       -- 当月売上明細OEM月額固定費
        ,null                                                       -- 当月売上明細OEM月額固定費消費税
        ,null                                                       -- 当月売上明細OEM同梱月額固定費
        ,null                                                       -- 当月売上明細OEM同梱月額固定費消費税
        ,null                                                       -- 当月売上明細OEMAPI月額固定費
        ,null                                                       -- 当月売上明細OEMAPI月額固定費消費税
        ,null                                                       -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細OEM月額固定費予備
        ,null                                                       -- 当月売上明細OEM月額固定費予備消費税
        ,null                                                       -- 当月手数料明細再発行手数料
        ,null                                                       -- 当月手数料明細再発行手数料消費税
        ,null                                                       -- 当月手数料明細遅延損害金
        ,NOW()                                                      -- 登録日時
        ,pi_user_id                                                 -- 登録者
        ,NOW()                                                      -- 更新日時
        ,pi_user_id                                                 -- 更新者
        ,1                                                          -- 有効ﾌﾗｸﾞ
    FROM  AT_Oem_DailyStatisticsTable dst
    INNER JOIN ( SELECT OemId, EnterpriseId, MAX( ProcessingDate ) ProcessingDate FROM AT_Oem_DailyStatisticsTable
                 WHERE v_BusinessDate      > ProcessingDate
                 AND   v_AccountingMonth   = AccountDate
                 AND   DailyMonthlyFlg     = 0
                 GROUP BY OemId, EnterpriseId ) dstw
    ON    dst.OemId = dstw.OemId
    AND   dst.EnterpriseId = dstw.EnterpriseId
    AND   dst.ProcessingDate = dstw.ProcessingDate
    AND   dst.DailyMonthlyFlg = 0
    AND   dst.AccountDate = v_AccountingMonth;

    -- ------------------------------
    -- 2.5.加盟店別に会計月の前月 = OEM日次統計表.会計月、且つ、MAX（OEM日次統計表.集計日）【前月末】のﾃﾞｰﾀを出力し日次用のOEM日次統計表ﾜｰｸを出力
    --     のﾃﾞｰﾀを出力し日次用のOEM日次統計表ﾜｰｸを出力する
    -- ------------------------------
    INSERT INTO AW_Oem_DailyStatisticsTable (
         ProcessingDate                                             -- 集計日
        ,AccountDate                                                -- 会計月
        ,OemId                                                      -- OEMID
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
        ,D_SettlementFee                                            -- 当日OEM決済手数料
        ,D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,D_ClaimFee                                                 -- 当日OEM請求手数料
        ,D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,D_MonthlyFee                                               -- 当日OEM月額固定費
        ,D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        ,D_SettlementFeeOther                                       -- 当日売上明細OEM決済手数料
        ,D_SettlementFeeTaxOther                                    -- 当日売上明細OEM決済手数料消費税
        ,D_ClaimFeeOther                                            -- 当日売上明細OEM請求手数料
        ,D_ClaimFeeTaxOther                                         -- 当日売上明細OEM請求手数料消費税
        ,D_MonthlyFeeOther                                          -- 当日売上明細OEM月額固定費
        ,D_MonthlyFeeTaxOther                                       -- 当日売上明細OEM月額固定費消費税
        ,D_OemIncludeMonthlyFeeOther                                -- 当日売上明細OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTaxOther                             -- 当日売上明細OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFeeOther                                    -- 当日売上明細OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTaxOther                                 -- 当日売上明細OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFeeOther                           -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTaxOther                        -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFeeOther                          -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTaxOther                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFeeOther                                -- 当日売上明細OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTaxOther                             -- 当日売上明細OEM月額固定費予備消費税
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
        ,M_OemTransferCount                                         -- 当月OEM移管件数
        ,M_OemTransferAmount                                        -- 当月OEM移管金額
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
        ,M_SuspensePaymentsAmount                                   -- OEM仮払金明細残高
        ,M_AccountsReceivableBalanceDiff                            -- 当月差額
        ,M_SettlementFee                                            -- 当月OEM決済手数料
        ,M_SettlementFeeTax                                         -- 当月OEM決済手数料消費税
        ,M_ClaimFee                                                 -- 当月OEM請求手数料
        ,M_ClaimFeeTax                                              -- 当月OEM請求手数料消費税
        ,M_MonthlyFee                                               -- 当月OEM月額固定費
        ,M_MonthlyFeeTax                                            -- 当月OEM月額固定費消費税
        ,M_OemIncludeMonthlyFee                                     -- 当月OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTax                                  -- 当月OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFee                                         -- 当月OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTax                                      -- 当月OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFee                                -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTax                             -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFee                               -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTax                            -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFee                                     -- 当月OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTax                                  -- 当月OEM月額固定費予備消費税
        ,M_AddClaimFee                                              -- 当月再発行手数料
        ,M_AddClaimFeeTax                                           -- 当月再発行手数料消費税
        ,M_DamageInterestAmount                                     -- 当月遅延損害金
        ,M_CanSettlementFee                                         -- 当月ｷｬﾝｾﾙOEM決済手数料
        ,M_CanSettlementFeeTax                                      -- 当月ｷｬﾝｾﾙOEM決済手数料消費税
        ,M_CanClaimFee                                              -- 当月ｷｬﾝｾﾙOEM請求手数料
        ,M_CanClaimFeeTax                                           -- 当月ｷｬﾝｾﾙOEM請求手数料消費税
        ,M_SettlementFeeOther                                       -- 当月売上明細OEM決済手数料
        ,M_SettlementFeeTaxOther                                    -- 当月売上明細OEM決済手数料消費税
        ,M_ClaimFeeOther                                            -- 当月売上明細OEM請求手数料
        ,M_ClaimFeeTaxOther                                         -- 当月売上明細OEM請求手数料消費税
        ,M_MonthlyFeeOther                                          -- 当月売上明細OEM月額固定費
        ,M_MonthlyFeeTaxOther                                       -- 当月売上明細OEM月額固定費消費税
        ,M_OemIncludeMonthlyFeeOther                                -- 当月売上明細OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTaxOther                             -- 当月売上明細OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFeeOther                                    -- 当月売上明細OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTaxOther                                 -- 当月売上明細OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFeeOther                           -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTaxOther                        -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFeeOther                          -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTaxOther                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFeeOther                                -- 当月売上明細OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTaxOther                             -- 当月売上明細OEM月額固定費予備消費税
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
        ,dst.OemId                                                  -- OEMID
        ,dst.EnterpriseId                                           -- 加盟店ID
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
        ,null                                                       -- 当日OEM決済手数料
        ,null                                                       -- 当日OEM決済手数料消費税
        ,null                                                       -- 当日OEM請求手数料
        ,null                                                       -- 当日OEM請求手数料消費税
        ,null                                                       -- 当日OEM月額固定費
        ,null                                                       -- 当日OEM月額固定費消費税
        ,null                                                       -- 当日OEM同梱月額固定費
        ,null                                                       -- 当日OEM同梱月額固定費消費税
        ,null                                                       -- 当日OEMAPI月額固定費
        ,null                                                       -- 当日OEMAPI月額固定費消費税
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM月額固定費予備
        ,null                                                       -- 当日OEM月額固定費予備消費税
        ,null                                                       -- 当日再発行手数料
        ,null                                                       -- 当日再発行手数料消費税
        ,null                                                       -- 当日遅延損害金
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        ,null                                                       -- 当日売上明細OEM決済手数料
        ,null                                                       -- 当日売上明細OEM決済手数料消費税
        ,null                                                       -- 当日売上明細OEM請求手数料
        ,null                                                       -- 当日売上明細OEM請求手数料消費税
        ,null                                                       -- 当日売上明細OEM月額固定費
        ,null                                                       -- 当日売上明細OEM月額固定費消費税
        ,null                                                       -- 当日売上明細OEM同梱月額固定費
        ,null                                                       -- 当日売上明細OEM同梱月額固定費消費税
        ,null                                                       -- 当日売上明細OEMAPI月額固定費
        ,null                                                       -- 当日売上明細OEMAPI月額固定費消費税
        ,null                                                       -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日売上明細OEM月額固定費予備
        ,null                                                       -- 当日売上明細OEM月額固定費予備消費税
        ,null                                                       -- 当日手数料明細再発行手数料
        ,null                                                       -- 当日手数料明細再発行手数料消費税
        ,null                                                       -- 当日手数料明細遅延損害金
        ,dst.M_AccountsReceivableBalance                            -- 月初未収金残高
        ,null                                                       -- 当月立替件数
        ,null                                                       -- 当月立替金額
        ,null                                                       -- 当月ｷｬﾝｾﾙ件数
        ,null                                                       -- 当月ｷｬﾝｾﾙ立替金額
        ,null                                                       -- 当月立替金戻し件数
        ,null                                                       -- 当月立替金戻し金額
        ,null                                                       -- 当月OEM移管件数
        ,null                                                       -- 当月OEM移管金額
        ,null                                                       -- 当日再発行手数料件数
        ,null                                                       -- 当日再発行手数料額
        ,null                                                       -- 当日遅延損害金件数
        ,null                                                       -- 当日遅延損害金額
        ,null                                                       -- 当月入金件数
        ,null                                                       -- 当月入金額
        ,null                                                       -- 当月返金件数
        ,null                                                       -- 当月返金額
        ,null                                                       -- 当月貸倒件数
        ,null                                                       -- 当月貸倒金額
        ,null                                                       -- 当月その他件数
        ,null                                                       -- 当月その他金額
        ,null                                                       -- 当月時点未収金残高
        ,null                                                       -- OEM仮払金明細残高
        ,null                                                       -- 当月差額
        ,null                                                       -- 当月OEM決済手数料
        ,null                                                       -- 当月OEM決済手数料消費税
        ,null                                                       -- 当月OEM請求手数料
        ,null                                                       -- 当月OEM請求手数料消費税
        ,null                                                       -- 当月OEM月額固定費
        ,null                                                       -- 当月OEM月額固定費消費税
        ,null                                                       -- 当月OEM同梱月額固定費
        ,null                                                       -- 当月OEM同梱月額固定費消費税
        ,null                                                       -- 当月OEMAPI月額固定費
        ,null                                                       -- 当月OEMAPI月額固定費消費税
        ,null                                                       -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月OEM月額固定費予備
        ,null                                                       -- 当月OEM月額固定費予備消費税
        ,null                                                       -- 当月再発行手数料
        ,null                                                       -- 当月再発行手数料消費税
        ,null                                                       -- 当月遅延損害金
        ,null                                                       -- 当月ｷｬﾝｾﾙOEM決済手数料
        ,null                                                       -- 当月ｷｬﾝｾﾙOEM決済手数料消費税
        ,null                                                       -- 当月ｷｬﾝｾﾙOEM請求手数料
        ,null                                                       -- 当月ｷｬﾝｾﾙOEM請求手数料消費税
        ,null                                                       -- 当月売上明細OEM決済手数料
        ,null                                                       -- 当月売上明細OEM決済手数料消費税
        ,null                                                       -- 当月売上明細OEM請求手数料
        ,null                                                       -- 当月売上明細OEM請求手数料消費税
        ,null                                                       -- 当月売上明細OEM月額固定費
        ,null                                                       -- 当月売上明細OEM月額固定費消費税
        ,null                                                       -- 当月売上明細OEM同梱月額固定費
        ,null                                                       -- 当月売上明細OEM同梱月額固定費消費税
        ,null                                                       -- 当月売上明細OEMAPI月額固定費
        ,null                                                       -- 当月売上明細OEMAPI月額固定費消費税
        ,null                                                       -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細OEM月額固定費予備
        ,null                                                       -- 当月売上明細OEM月額固定費予備消費税
        ,null                                                       -- 当月手数料明細再発行手数料
        ,null                                                       -- 当月手数料明細再発行手数料消費税
        ,null                                                       -- 当月手数料明細遅延損害金
        ,NOW()                                                      -- 登録日時-- 登録日時
        ,pi_user_id                                                 -- 登録者-- 登録者
        ,NOW()                                                      -- 更新日時-- 更新日時
        ,pi_user_id                                                 -- 更新者-- 更新者
        ,1                                                          -- 有効ﾌﾗｸﾞ-- 有効ﾌﾗｸﾞ
    FROM  AT_Oem_DailyStatisticsTable dst
    INNER JOIN ( SELECT OemId, EnterpriseId, MAX( ProcessingDate ) ProcessingDate FROM AT_Oem_DailyStatisticsTable
                 WHERE DATE_SUB(v_AccountingMonth, INTERVAL 1 MONTH) = AccountDate
                 AND   DailyMonthlyFlg     = 1
                 GROUP BY OemId, EnterpriseId ) dstw
    ON    dst.OemId = dstw.OemId
    AND   dst.EnterpriseId = dstw.EnterpriseId
    AND   dst.ProcessingDate = dstw.ProcessingDate
    AND   dst.DailyMonthlyFlg = 1
    AND   dst.AccountDate = DATE_SUB(v_AccountingMonth, INTERVAL 1 MONTH);


    -- ------------------------------
    -- 2.6.OEM日次統計表ﾜｰｸのﾃﾞｰﾀを加盟店別に集計出力し日次用のOEM日次統計表を出力する
    -- ------------------------------
    INSERT INTO AT_Oem_DailyStatisticsTable (
         DailyMonthlyFlg                                            -- 日次･月次区分
        ,ProcessingDate                                             -- 集計日
        ,AccountDate                                                -- 会計月
        ,OemId                                                      -- OEMID
        ,OemNameKj                                                  -- OEM先名
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
        ,D_SettlementFee                                            -- 当日OEM決済手数料
        ,D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,D_ClaimFee                                                 -- 当日OEM請求手数料
        ,D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,D_MonthlyFee                                               -- 当日OEM月額固定費
        ,D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        ,D_SettlementFeeTotal                                       -- 当日合計OEM決済手数料
        ,D_SettlementFeeTaxTotal                                    -- 当日合計OEM決済手数料消費税
        ,D_ClaimFeeTotal                                            -- 当日合計OEM請求手数料
        ,D_ClaimFeeTaxTotal                                         -- 当日合計OEM請求手数料消費税
        ,D_MonthlyFeeTotal                                          -- 当日合計OEM月額固定費
        ,D_MonthlyFeeTaxTotal                                       -- 当日合計OEM月額固定費消費税
        ,D_OemIncludeMonthlyFeeTotal                                -- 当日合計OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTaxTotal                             -- 当日合計OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFeeTotal                                    -- 当日合計OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTaxTotal                                 -- 当日合計OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFeeTotal                           -- 当日合計OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTaxTotal                        -- 当日合計OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFeeTotal                          -- 当日合計OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTaxTotal                       -- 当日合計OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFeeTotal                                -- 当日合計OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTaxTotal                             -- 当日合計OEM月額固定費予備消費税
        ,D_AddClaimFeeTotal                                         -- 当日合計再発行手数料
        ,D_AddClaimFeeTaxTotal                                      -- 当日合計再発行手数料消費税
        ,D_DamageInterestAmountTotal                                -- 当日合計遅延損害金
        ,D_AllTotal                                                 -- 当日総合計
        ,D_SettlementFeeOther                                       -- 当日売上明細OEM決済手数料
        ,D_SettlementFeeTaxOther                                    -- 当日売上明細OEM決済手数料消費税
        ,D_ClaimFeeOther                                            -- 当日売上明細OEM請求手数料
        ,D_ClaimFeeTaxOther                                         -- 当日売上明細OEM請求手数料消費税
        ,D_MonthlyFeeOther                                          -- 当日売上明細OEM月額固定費
        ,D_MonthlyFeeTaxOther                                       -- 当日売上明細OEM月額固定費消費税
        ,D_OemIncludeMonthlyFeeOther                                -- 当日売上明細OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTaxOther                             -- 当日売上明細OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFeeOther                                    -- 当日売上明細OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTaxOther                                 -- 当日売上明細OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFeeOther                           -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTaxOther                        -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFeeOther                          -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTaxOther                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFeeOther                                -- 当日売上明細OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTaxOther                             -- 当日売上明細OEM月額固定費予備消費税
        ,D_AddClaimFeeOther                                         -- 当日手数料明細再発行手数料
        ,D_AddClaimFeeTaxOther                                      -- 当日手数料明細再発行手数料消費税
        ,D_DamageInterestAmountOther                                -- 当日手数料明細遅延損害金
        ,D_SettlementFeeDiff                                        -- 当日差額OEM決済手数料
        ,D_SettlementFeeTaxDiff                                     -- 当日差額OEM決済手数料消費税
        ,D_ClaimFeeDiff                                             -- 当日差額OEM請求手数料
        ,D_ClaimFeeTaxDiff                                          -- 当日差額OEM請求手数料消費税
        ,D_MonthlyFeeDiff                                           -- 当日差額OEM月額固定費
        ,D_MonthlyFeeTaxDiff                                        -- 当日差額OEM月額固定費消費税
        ,D_OemIncludeMonthlyFeeDiff                                 -- 当日差額OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTaxDiff                              -- 当日差額OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFeeDiff                                     -- 当日差額OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTaxDiff                                  -- 当日差額OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFeeDiff                            -- 当日差額OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTaxDiff                         -- 当日差額OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFeeDiff                           -- 当日差額OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTaxDiff                        -- 当日差額OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFeeDiff                                 -- 当日差額OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTaxDiff                              -- 当日差額OEM月額固定費予備消費税
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
        ,M_OemTransferCount                                         -- 当月OEM移管件数
        ,M_OemTransferAmount                                        -- 当月OEM移管金額
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
        ,M_SuspensePaymentsAmount                                   -- OEM仮払金明細残高
        ,M_AccountsReceivableBalanceDiff                            -- 当月差額
        ,M_SettlementFee                                            -- 当月OEM決済手数料
        ,M_SettlementFeeTax                                         -- 当月OEM決済手数料消費税
        ,M_ClaimFee                                                 -- 当月OEM請求手数料
        ,M_ClaimFeeTax                                              -- 当月OEM請求手数料消費税
        ,M_MonthlyFee                                               -- 当月OEM月額固定費
        ,M_MonthlyFeeTax                                            -- 当月OEM月額固定費消費税
        ,M_OemIncludeMonthlyFee                                     -- 当月OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTax                                  -- 当月OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFee                                         -- 当月OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTax                                      -- 当月OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFee                                -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTax                             -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFee                               -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTax                            -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFee                                     -- 当月OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTax                                  -- 当月OEM月額固定費予備消費税
        ,M_AddClaimFee                                              -- 当月再発行手数料
        ,M_AddClaimFeeTax                                           -- 当月再発行手数料消費税
        ,M_DamageInterestAmount                                     -- 当月遅延損害金
        ,M_CanSettlementFee                                         -- 当月ｷｬﾝｾﾙOEM決済手数料
        ,M_CanSettlementFeeTax                                      -- 当月ｷｬﾝｾﾙOEM決済手数料消費税
        ,M_CanClaimFee                                              -- 当月ｷｬﾝｾﾙOEM請求手数料
        ,M_CanClaimFeeTax                                           -- 当月ｷｬﾝｾﾙOEM請求手数料消費税
        ,M_SettlementFeeTotal                                       -- 当月合計OEM決済手数料
        ,M_SettlementFeeTaxTotal                                    -- 当月合計OEM決済手数料消費税
        ,M_ClaimFeeTotal                                            -- 当月合計OEM請求手数料
        ,M_ClaimFeeTaxTotal                                         -- 当月合計OEM請求手数料消費税
        ,M_MonthlyFeeTotal                                          -- 当月合計OEM月額固定費
        ,M_MonthlyFeeTaxTotal                                       -- 当月合計OEM月額固定費消費税
        ,M_OemIncludeMonthlyFeeTotal                                -- 当月合計OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTaxTotal                             -- 当月合計OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFeeTotal                                    -- 当月合計OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTaxTotal                                 -- 当月合計OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFeeTotal                           -- 当月合計OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTaxTotal                        -- 当月合計OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFeeTotal                          -- 当月合計OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTaxTotal                       -- 当月合計OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFeeTotal                                -- 当月合計OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTaxTotal                             -- 当月合計OEM月額固定費予備消費税
        ,M_AddClaimFeeTotal                                         -- 当月合計再発行手数料
        ,M_AddClaimFeeTaxTotal                                      -- 当月合計再発行手数料消費税
        ,M_DamageInterestAmountTotal                                -- 当月合計遅延損害金
        ,M_AllTotal                                                 -- 当月総合計
        ,M_SettlementFeeOther                                       -- 当月売上明細OEM決済手数料
        ,M_SettlementFeeTaxOther                                    -- 当月売上明細OEM決済手数料消費税
        ,M_ClaimFeeOther                                            -- 当月売上明細OEM請求手数料
        ,M_ClaimFeeTaxOther                                         -- 当月売上明細OEM請求手数料消費税
        ,M_MonthlyFeeOther                                          -- 当月売上明細OEM月額固定費
        ,M_MonthlyFeeTaxOther                                       -- 当月売上明細OEM月額固定費消費税
        ,M_OemIncludeMonthlyFeeOther                                -- 当月売上明細OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTaxOther                             -- 当月売上明細OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFeeOther                                    -- 当月売上明細OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTaxOther                                 -- 当月売上明細OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFeeOther                           -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTaxOther                        -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFeeOther                          -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTaxOther                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFeeOther                                -- 当月売上明細OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTaxOther                             -- 当月売上明細OEM月額固定費予備消費税
        ,M_AddClaimFeeOther                                         -- 当月手数料明細再発行手数料
        ,M_AddClaimFeeTaxOther                                      -- 当月手数料明細再発行手数料消費税
        ,M_DamageInterestAmountOther                                -- 当月手数料明細遅延損害金
        ,M_SettlementFeeDiff                                        -- 当月差額OEM決済手数料
        ,M_SettlementFeeTaxDiff                                     -- 当月差額OEM決済手数料消費税
        ,M_ClaimFeeDiff                                             -- 当月差額OEM請求手数料
        ,M_ClaimFeeTaxDiff                                          -- 当月差額OEM請求手数料消費税
        ,M_MonthlyFeeDiff                                           -- 当月差額OEM月額固定費
        ,M_MonthlyFeeTaxDiff                                        -- 当月差額OEM月額固定費消費税
        ,M_OemIncludeMonthlyFeeDiff                                 -- 当月差額OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTaxDiff                              -- 当月差額OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFeeDiff                                     -- 当月差額OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTaxDiff                                  -- 当月差額OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFeeDiff                            -- 当月差額OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTaxDiff                         -- 当月差額OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFeeDiff                           -- 当月差額OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTaxDiff                        -- 当月差額OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFeeDiff                                 -- 当月差額OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTaxDiff                              -- 当月差額OEM月額固定費予備消費税
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
        ,v_AccountingMonth                                          -- 会計月
        ,aw_dst.OemId                                               -- OemId
        ,c1.Class2                                                  -- Oem名
        ,aw_dst.EnterpriseId                                        -- 加盟店ID
        ,ent.EnterpriseNameKj                                       -- 加盟店名
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
        ,aw_dst.D_SettlementFee                                     -- 当日OEM決済手数料
        ,aw_dst.D_SettlementFeeTax                                  -- 当日OEM決済手数料消費税
        ,aw_dst.D_ClaimFee                                          -- 当日OEM請求手数料
        ,aw_dst.D_ClaimFeeTax                                       -- 当日OEM請求手数料消費税
        ,aw_dst.D_MonthlyFee                                        -- 当日OEM月額固定費
        ,aw_dst.D_MonthlyFeeTax                                     -- 当日OEM月額固定費消費税
        ,aw_dst.D_OemIncludeMonthlyFee                              -- 当日OEM同梱月額固定費
        ,aw_dst.D_OemIncludeMonthlyFeeTax                           -- 当日OEM同梱月額固定費消費税
        ,aw_dst.D_OemApiMonthlyFee                                  -- 当日OEMAPI月額固定費
        ,aw_dst.D_OemApiMonthlyFeeTax                               -- 当日OEMAPI月額固定費消費税
        ,aw_dst.D_OemCreditNoticeMonthlyFee                         -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_OemCreditNoticeMonthlyFeeTax                      -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_OemNCreditNoticeMonthlyFee                        -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_OemNCreditNoticeMonthlyFeeTax                     -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_OemReserveMonthlyFee                              -- 当日OEM月額固定費予備
        ,aw_dst.D_OemReserveMonthlyFeeTax                           -- 当日OEM月額固定費予備消費税
        ,aw_dst.D_AddClaimFee                                       -- 当日再発行手数料
        ,aw_dst.D_AddClaimFeeTax                                    -- 当日再発行手数料消費税
        ,aw_dst.D_DamageInterestAmount                              -- 当日遅延損害金
        ,aw_dst.D_CanSettlementFee                                  -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,aw_dst.D_CanSettlementFeeTax                               -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,aw_dst.D_CanClaimFee                                       -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,aw_dst.D_CanClaimFeeTax                                    -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        ,aw_dst.D_SettlementFee + aw_dst.D_CanSettlementFee         -- 当日合計OEM決済手数料
        ,aw_dst.D_SettlementFeeTax + aw_dst.D_CanSettlementFeeTax   -- 当日合計OEM決済手数料消費税
        ,aw_dst.D_ClaimFee + aw_dst.D_CanClaimFee                   -- 当日合計OEM請求手数料
        ,aw_dst.D_ClaimFeeTax + aw_dst.D_CanClaimFeeTax             -- 当日合計OEM請求手数料消費税
        ,aw_dst.D_MonthlyFee                                        -- 当日合計OEM月額固定費
        ,aw_dst.D_MonthlyFeeTax                                     -- 当日合計OEM月額固定費消費税
        ,aw_dst.D_OemIncludeMonthlyFee                              -- 当日合計OEM同梱月額固定費
        ,aw_dst.D_OemIncludeMonthlyFeeTax                           -- 当日合計OEM同梱月額固定費消費税
        ,aw_dst.D_OemApiMonthlyFee                                  -- 当日合計OEMAPI月額固定費
        ,aw_dst.D_OemApiMonthlyFeeTax                               -- 当日合計OEMAPI月額固定費消費税
        ,aw_dst.D_OemCreditNoticeMonthlyFee                         -- 当日合計OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_OemCreditNoticeMonthlyFeeTax                      -- 当日合計OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_OemNCreditNoticeMonthlyFee                        -- 当日合計OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_OemNCreditNoticeMonthlyFeeTax                     -- 当日合計OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_OemReserveMonthlyFee                              -- 当日合計OEM月額固定費予備
        ,aw_dst.D_OemReserveMonthlyFeeTax                           -- 当日合計OEM月額固定費予備消費税
        ,aw_dst.D_AddClaimFee                                       -- 当日合計再発行手数料
        ,aw_dst.D_AddClaimFeeTax                                    -- 当日合計再発行手数料消費税
        ,aw_dst.D_DamageInterestAmount                              -- 当日合計遅延損害金
        ,aw_dst.D_SettlementFee + aw_dst.D_CanSettlementFee +
         aw_dst.D_SettlementFeeTax + aw_dst.D_CanSettlementFeeTax +
         aw_dst.D_ClaimFee + aw_dst.D_CanClaimFee +
         aw_dst.D_ClaimFeeTax + aw_dst.D_CanClaimFeeTax +
         aw_dst.D_MonthlyFee + aw_dst.D_MonthlyFeeTax +
         aw_dst.D_OemIncludeMonthlyFee + aw_dst.D_OemIncludeMonthlyFeeTax +
         aw_dst.D_OemApiMonthlyFee + aw_dst.D_OemApiMonthlyFeeTax +
         aw_dst.D_OemCreditNoticeMonthlyFee+ aw_dst.D_OemCreditNoticeMonthlyFeeTax +
         aw_dst.D_OemNCreditNoticeMonthlyFee + aw_dst.D_OemNCreditNoticeMonthlyFeeTax +
         aw_dst.D_OemReserveMonthlyFee + aw_dst.D_OemReserveMonthlyFeeTax +
         aw_dst.D_AddClaimFee + aw_dst.D_AddClaimFeeTax +
         aw_dst.D_DamageInterestAmount                              -- 当日総合計
        ,IFNULL(at_dsd.OemSettlementFee , 0)                        -- 当日売上明細OEM決済手数料
        ,IFNULL(at_dsd.OemSettlementFeeTax , 0)                     -- 当日売上明細OEM決済手数料消費税
        ,IFNULL(at_dsd.OemClaimFee , 0)                             -- 当日売上明細OEM請求手数料
        ,IFNULL(at_dsd.OemClaimFeeTax , 0)                          -- 当日売上明細OEM請求手数料消費税
        ,IFNULL(at_dsd.OemMonthlyFee , 0)                           -- 当日売上明細OEM月額固定費
        ,IFNULL(at_dsd.OemMonthlyFeeTax , 0)                        -- 当日売上明細OEM月額固定費消費税
        ,IFNULL(at_dsd.OemIncludeMonthlyFee , 0)                    -- 当日売上明細OEM同梱月額固定費
        ,IFNULL(at_dsd.OemIncludeMonthlyFeeTax , 0)                 -- 当日売上明細OEM同梱月額固定費消費税
        ,IFNULL(at_dsd.OemApiMonthlyFee , 0)                        -- 当日売上明細OEMAPI月額固定費
        ,IFNULL(at_dsd.OemApiMonthlyFeeTax , 0)                     -- 当日売上明細OEMAPI月額固定費消費税
        ,IFNULL(at_dsd.OemCreditNoticeMonthlyFee , 0)               -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,IFNULL(at_dsd.OemCreditNoticeMonthlyFeeTax , 0)            -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,IFNULL(at_dsd.OemNCreditNoticeMonthlyFee , 0)              -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,IFNULL(at_dsd.OemNCreditNoticeMonthlyFeeTax , 0)           -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,IFNULL(at_dsd.OemReserveMonthlyFee , 0)                    -- 当日売上明細OEM月額固定費予備
        ,IFNULL(at_dsd.OemReserveMonthlyFeeTax , 0)                 -- 当日売上明細OEM月額固定費予備消費税
        ,IFNULL(at_rfs.Clm_L_ClaimFee , 0)                          -- 当日手数料明細再発行手数料
        ,IFNULL(at_rfs.Clm_L_ClaimFeeTax , 0)                       -- 当日手数料明細再発行手数料消費税
        ,IFNULL(at_rfs.Clm_L_DamageInterestAmount , 0)              -- 当日手数料明細遅延損害金
        ,aw_dst.D_SettlementFee + aw_dst.D_CanSettlementFee - IFNULL( at_dsd.OemSettlementFee, 0)               -- 当日差額OEM決済手数料
        ,aw_dst.D_SettlementFeeTax + aw_dst.D_CanSettlementFeeTax - IFNULL( at_dsd.OemSettlementFeeTax, 0)      -- 当日差額OEM決済手数料消費税
        ,aw_dst.D_ClaimFee + aw_dst.D_CanClaimFee - IFNULL( at_dsd.OemClaimFee, 0)                              -- 当日差額OEM請求手数料
        ,aw_dst.D_ClaimFeeTax + aw_dst.D_CanClaimFeeTax - IFNULL( at_dsd.OemClaimFeeTax, 0)                     -- 当日差額OEM請求手数料消費税
        ,aw_dst.D_MonthlyFee - IFNULL( at_dsd.OemMonthlyFee, 0)                                                 -- 当日差額OEM月額固定費
        ,aw_dst.D_MonthlyFeeTax - IFNULL( at_dsd.OemMonthlyFeeTax, 0)                                           -- 当日差額OEM月額固定費消費税
        ,aw_dst.D_OemIncludeMonthlyFee - IFNULL( at_dsd.OemIncludeMonthlyFee, 0)                                -- 当日差額OEM同梱月額固定費
        ,aw_dst.D_OemIncludeMonthlyFeeTax - IFNULL( at_dsd.OemIncludeMonthlyFeeTax, 0)                          -- 当日差額OEM同梱月額固定費消費税
        ,aw_dst.D_OemApiMonthlyFee - IFNULL( at_dsd.OemApiMonthlyFee  , 0)                                      -- 当日差額OEMAPI月額固定費
        ,aw_dst.D_OemApiMonthlyFeeTax - IFNULL( at_dsd.OemApiMonthlyFeeTax, 0)                                  -- 当日差額OEMAPI月額固定費消費税
        ,aw_dst.D_OemCreditNoticeMonthlyFee - IFNULL( at_dsd.OemCreditNoticeMonthlyFee, 0)                      -- 当日差額OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_OemCreditNoticeMonthlyFeeTax - IFNULL( at_dsd.OemCreditNoticeMonthlyFeeTax, 0)                -- 当日差額OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_OemNCreditNoticeMonthlyFee - IFNULL( at_dsd.OemNCreditNoticeMonthlyFee, 0)                    -- 当日差額OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_OemNCreditNoticeMonthlyFeeTax - IFNULL( at_dsd.OemNCreditNoticeMonthlyFeeTax, 0)              -- 当日差額OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_OemReserveMonthlyFee - IFNULL( at_dsd.OemReserveMonthlyFee, 0)                                -- 当日差額OEM月額固定費予備
        ,aw_dst.D_OemReserveMonthlyFeeTax - IFNULL( at_dsd.OemReserveMonthlyFeeTax, 0)                          -- 当日差額OEM月額固定費予備消費税
        ,aw_dst.D_AddClaimFee - IFNULL(at_rfs.Clm_L_ClaimFee, 0)                                                -- 当日差額再発行手数料
        ,aw_dst.D_AddClaimFeeTax - IFNULL(at_rfs.Clm_L_ClaimFeeTax, 0)                                          -- 当日差額再発行手数料消費税
        ,aw_dst.D_DamageInterestAmount - IFNULL(at_rfs.Clm_L_DamageInterestAmount, 0)                           -- 当日差額遅延損害金
        ,aw_dst.MB__AccountsReceivableBalance                       -- 月初未収金残高
        ,aw_dst.M_ChargeCount                                       -- 当月立替件数
        ,aw_dst.M_ChargeAmount                                      -- 当月立替金額
        ,aw_dst.M_CancelCount                                       -- 当月ｷｬﾝｾﾙ件数
        ,aw_dst.M_CancelAmount                                      -- 当月ｷｬﾝｾﾙ立替金額
        ,aw_dst.M_SettlementBackCount                               -- 当月立替金戻し件数
        ,aw_dst.M_SettlementBackAmount                              -- 当月立替金戻し金額
        ,aw_dst.M_OemTransferCount                                  -- 当月OEM移管件数
        ,aw_dst.M_OemTransferAmount                                 -- 当月OEM移管金額
        ,aw_dst.M_ReClaimFeeCount                                   -- 当日再発行手数料件数
        ,aw_dst.M_ReClaimFeeAmount                                  -- 当日再発行手数料額
        ,aw_dst.M_DamageCount                                       -- 当日遅延損害金件数
        ,aw_dst.M_DamageAmount                                      -- 当日遅延損害金額
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
         aw_dst.M_SettlementBackAmount + aw_dst.M_OemTransferAmount +
         aw_dst.M_ReClaimFeeAmount + aw_dst.M_DamageAmount +
         aw_dst.M_ReceiptAmount + aw_dst.M_RepayAmount +
         aw_dst.M_BadDebtAmount + aw_dst.M_OtherPaymentAmount       -- 当月時点未収金残高
        ,null                                                       -- OEM仮払金明細残高
        ,null                                                       -- 当月差額
        ,aw_dst.M_SettlementFee                                     -- 当月OEM決済手数料
        ,aw_dst.M_SettlementFeeTax                                  -- 当月OEM決済手数料消費税
        ,aw_dst.M_ClaimFee                                          -- 当月OEM請求手数料
        ,aw_dst.M_ClaimFeeTax                                       -- 当月OEM請求手数料消費税
        ,aw_dst.M_MonthlyFee                                        -- 当月OEM月額固定費
        ,aw_dst.M_MonthlyFeeTax                                     -- 当月OEM月額固定費消費税
        ,aw_dst.M_OemIncludeMonthlyFee                              -- 当月OEM同梱月額固定費
        ,aw_dst.M_OemIncludeMonthlyFeeTax                           -- 当月OEM同梱月額固定費消費税
        ,aw_dst.M_OemApiMonthlyFee                                  -- 当月OEMAPI月額固定費
        ,aw_dst.M_OemApiMonthlyFeeTax                               -- 当月OEMAPI月額固定費消費税
        ,aw_dst.M_OemCreditNoticeMonthlyFee                         -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.M_OemCreditNoticeMonthlyFeeTax                      -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.M_OemNCreditNoticeMonthlyFee                        -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.M_OemNCreditNoticeMonthlyFeeTax                     -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.M_OemReserveMonthlyFee                              -- 当月OEM月額固定費予備
        ,aw_dst.M_OemReserveMonthlyFeeTax                           -- 当月OEM月額固定費予備消費税
        ,aw_dst.M_AddClaimFee                                       -- 当月再発行手数料
        ,aw_dst.M_AddClaimFeeTax                                    -- 当月再発行手数料消費税
        ,aw_dst.M_DamageInterestAmount                              -- 当月遅延損害金
        ,aw_dst.M_CanSettlementFee                                  -- 当月ｷｬﾝｾﾙOEM決済手数料
        ,aw_dst.M_CanSettlementFeeTax                               -- 当月ｷｬﾝｾﾙOEM決済手数料消費税
        ,aw_dst.M_CanClaimFee                                       -- 当月ｷｬﾝｾﾙOEM請求手数料
        ,aw_dst.M_CanClaimFeeTax                                    -- 当月ｷｬﾝｾﾙOEM請求手数料消費税
        ,aw_dst.M_SettlementFee + aw_dst.M_CanSettlementFee         -- 当月合計OEM決済手数料
        ,aw_dst.M_SettlementFeeTax + aw_dst.M_CanSettlementFeeTax   -- 当月合計OEM決済手数料消費税
        ,aw_dst.M_ClaimFee + aw_dst.M_CanClaimFee                   -- 当月合計OEM請求手数料
        ,aw_dst.M_ClaimFeeTax + aw_dst.M_CanClaimFeeTax             -- 当月合計OEM請求手数料消費税
        ,aw_dst.M_MonthlyFee                                        -- 当月合計OEM月額固定費
        ,aw_dst.M_MonthlyFeeTax                                     -- 当月合計OEM月額固定費消費税
        ,aw_dst.M_OemIncludeMonthlyFee                              -- 当月合計OEM同梱月額固定費
        ,aw_dst.M_OemIncludeMonthlyFeeTax                           -- 当月合計OEM同梱月額固定費消費税
        ,aw_dst.M_OemApiMonthlyFee                                  -- 当月合計OEMAPI月額固定費
        ,aw_dst.M_OemApiMonthlyFeeTax                               -- 当月合計OEMAPI月額固定費消費税
        ,aw_dst.M_OemCreditNoticeMonthlyFee                         -- 当月合計OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.M_OemCreditNoticeMonthlyFeeTax                      -- 当月合計OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.M_OemNCreditNoticeMonthlyFee                        -- 当月合計OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.M_OemNCreditNoticeMonthlyFeeTax                     -- 当月合計OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.M_OemReserveMonthlyFee                              -- 当月合計OEM月額固定費予備
        ,aw_dst.M_OemReserveMonthlyFeeTax                           -- 当月合計OEM月額固定費予備消費税
        ,aw_dst.M_AddClaimFee                                       -- 当月合計再発行手数料
        ,aw_dst.M_AddClaimFeeTax                                    -- 当月合計再発行手数料消費税
        ,aw_dst.M_DamageInterestAmount                              -- 当月合計遅延損害金
        ,aw_dst.M_SettlementFee + aw_dst.M_CanSettlementFee +
         aw_dst.M_SettlementFeeTax + aw_dst.M_CanSettlementFeeTax +
         aw_dst.M_ClaimFee + aw_dst.M_CanClaimFee +
         aw_dst.M_ClaimFeeTax + aw_dst.M_CanClaimFeeTax +
         aw_dst.M_MonthlyFee + aw_dst.M_MonthlyFeeTax +
         aw_dst.M_OemIncludeMonthlyFee + aw_dst.M_OemIncludeMonthlyFeeTax +
         aw_dst.M_OemApiMonthlyFee + aw_dst.M_OemApiMonthlyFeeTax +
         aw_dst.M_OemCreditNoticeMonthlyFee + aw_dst.M_OemCreditNoticeMonthlyFeeTax +
         aw_dst.M_OemNCreditNoticeMonthlyFee + aw_dst.M_OemNCreditNoticeMonthlyFeeTax +
         aw_dst.M_OemReserveMonthlyFee + aw_dst.M_OemReserveMonthlyFeeTax +
         aw_dst.M_AddClaimFee + aw_dst.M_AddClaimFeeTax +
         aw_dst.M_DamageInterestAmount                              -- 当月総合計
        ,null                                                       -- 当月売上明細OEM決済手数料
        ,null                                                       -- 当月売上明細OEM決済手数料消費税
        ,null                                                       -- 当月売上明細OEM請求手数料
        ,null                                                       -- 当月売上明細OEM請求手数料消費税
        ,null                                                       -- 当月売上明細OEM月額固定費
        ,null                                                       -- 当月売上明細OEM月額固定費消費税
        ,null                                                       -- 当月売上明細OEM同梱月額固定費
        ,null                                                       -- 当月売上明細OEM同梱月額固定費消費税
        ,null                                                       -- 当月売上明細OEMAPI月額固定費
        ,null                                                       -- 当月売上明細OEMAPI月額固定費消費税
        ,null                                                       -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細OEM月額固定費予備
        ,null                                                       -- 当月売上明細OEM月額固定費予備消費税
        ,null                                                       -- 当月手数料明細再発行手数料
        ,null                                                       -- 当月手数料明細再発行手数料消費税
        ,null                                                       -- 当月手数料明細遅延損害金
        ,null                                                       -- 当月差額OEM決済手数料
        ,null                                                       -- 当月差額OEM決済手数料消費税
        ,null                                                       -- 当月差額OEM請求手数料
        ,null                                                       -- 当月差額OEM請求手数料消費税
        ,null                                                       -- 当月差額OEM月額固定費
        ,null                                                       -- 当月差額OEM月額固定費消費税
        ,null                                                       -- 当月差額OEM同梱月額固定費
        ,null                                                       -- 当月差額OEM同梱月額固定費消費税
        ,null                                                       -- 当月差額OEMAPI月額固定費
        ,null                                                       -- 当月差額OEMAPI月額固定費消費税
        ,null                                                       -- 当月差額OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月差額OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月差額OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月差額OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月差額OEM月額固定費予備
        ,null                                                       -- 当月差額OEM月額固定費予備消費税
        ,null                                                       -- 当月差額再発行手数料
        ,null                                                       -- 当月差額再発行手数料消費税
        ,null                                                       -- 当月差額遅延損害金
        ,NOW()                                                      -- 登録日時
        ,pi_user_id                                                 -- 登録者
        ,NOW()                                                      -- 更新日時
        ,pi_user_id                                                 -- 更新者
        ,1                                                          -- 有効ﾌﾗｸﾞ
    FROM (      SELECT
                     OemId                                                    AS OemId
                    ,EnterpriseId                                             AS EnterpriseId
                    ,SUM( IFNULL( DB__AccountsReceivableBalance, 0 ) )        AS DB__AccountsReceivableBalance
                    ,SUM( IFNULL( D_ChargeCount, 0 ) )                        AS D_ChargeCount
                    ,SUM( IFNULL( D_ChargeAmount, 0 ) )                       AS D_ChargeAmount
                    ,SUM( IFNULL( D_CancelCount, 0 ) )                        AS D_CancelCount
                    ,SUM( IFNULL( D_CancelAmount, 0 ) )                       AS D_CancelAmount
                    ,SUM( IFNULL( D_SettlementBackCount, 0 ) )                AS D_SettlementBackCount
                    ,SUM( IFNULL( D_SettlementBackAmount, 0 ) )               AS D_SettlementBackAmount
                    ,SUM( IFNULL( D_OemTransferCount, 0 ) )                   AS D_OemTransferCount
                    ,SUM( IFNULL( D_OemTransferAmount, 0 ) )                  AS D_OemTransferAmount
                    ,SUM( IFNULL( D_ReClaimFeeCount, 0 ) )                    AS D_ReClaimFeeCount
                    ,SUM( IFNULL( D_ReClaimFeeAmount, 0 ) )                   AS D_ReClaimFeeAmount
                    ,SUM( IFNULL( D_DamageCount, 0 ) )                        AS D_DamageCount
                    ,SUM( IFNULL( D_DamageAmount, 0 ) )                       AS D_DamageAmount
                    ,SUM( IFNULL( D_ReceiptCount, 0 ) )                       AS D_ReceiptCount
                    ,SUM( IFNULL( D_ReceiptAmount, 0 ) )                      AS D_ReceiptAmount
                    ,SUM( IFNULL( D_RepayCount, 0 ) )                         AS D_RepayCount
                    ,SUM( IFNULL( D_RepayAmount, 0 ) )                        AS D_RepayAmount
                    ,SUM( IFNULL( D_BadDebtCount, 0 ) )                       AS D_BadDebtCount
                    ,SUM( IFNULL( D_BadDebtAmount, 0 ) )                      AS D_BadDebtAmount
                    ,SUM( IFNULL( D_OtherPaymentCount, 0 ) )                  AS D_OtherPaymentCount
                    ,SUM( IFNULL( D_OtherPaymentAmount, 0 ) )                 AS D_OtherPaymentAmount
                    ,SUM( IFNULL( D_AccountsReceivableBalance, 0 ) )          AS D_AccountsReceivableBalance
                    ,SUM( IFNULL( D_SettlementFee, 0 ) )                      AS D_SettlementFee
                    ,SUM( IFNULL( D_SettlementFeeTax, 0 ) )                   AS D_SettlementFeeTax
                    ,SUM( IFNULL( D_ClaimFee, 0 ) )                           AS D_ClaimFee
                    ,SUM( IFNULL( D_ClaimFeeTax, 0 ) )                        AS D_ClaimFeeTax
                    ,SUM( IFNULL( D_MonthlyFee, 0 ) )                         AS D_MonthlyFee
                    ,SUM( IFNULL( D_MonthlyFeeTax, 0 ) )                      AS D_MonthlyFeeTax
                    ,SUM( IFNULL( D_OemIncludeMonthlyFee, 0 ) )               AS D_OemIncludeMonthlyFee
                    ,SUM( IFNULL( D_OemIncludeMonthlyFeeTax, 0 ) )            AS D_OemIncludeMonthlyFeeTax
                    ,SUM( IFNULL( D_OemApiMonthlyFee, 0 ) )                   AS D_OemApiMonthlyFee
                    ,SUM( IFNULL( D_OemApiMonthlyFeeTax, 0 ) )                AS D_OemApiMonthlyFeeTax
                    ,SUM( IFNULL( D_OemCreditNoticeMonthlyFee, 0 ) )          AS D_OemCreditNoticeMonthlyFee
                    ,SUM( IFNULL( D_OemCreditNoticeMonthlyFeeTax, 0 ) )       AS D_OemCreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( D_OemNCreditNoticeMonthlyFee, 0 ) )         AS D_OemNCreditNoticeMonthlyFee
                    ,SUM( IFNULL( D_OemNCreditNoticeMonthlyFeeTax, 0 ) )      AS D_OemNCreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( D_OemReserveMonthlyFee, 0 ) )               AS D_OemReserveMonthlyFee
                    ,SUM( IFNULL( D_OemReserveMonthlyFeeTax, 0 ) )            AS D_OemReserveMonthlyFeeTax
                    ,SUM( IFNULL( D_AddClaimFee, 0 ) )                        AS D_AddClaimFee
                    ,SUM( IFNULL( D_AddClaimFeeTax, 0 ) )                     AS D_AddClaimFeeTax
                    ,SUM( IFNULL( D_DamageInterestAmount, 0 ) )               AS D_DamageInterestAmount
                    ,SUM( IFNULL( D_CanSettlementFee, 0 ) )                   AS D_CanSettlementFee
                    ,SUM( IFNULL( D_CanSettlementFeeTax, 0 ) )                AS D_CanSettlementFeeTax
                    ,SUM( IFNULL( D_CanClaimFee, 0 ) )                        AS D_CanClaimFee
                    ,SUM( IFNULL( D_CanClaimFeeTax, 0 ) )                     AS D_CanClaimFeeTax
                    ,SUM( IFNULL( D_SettlementFeeOther, 0 ) )                 AS D_SettlementFeeOther
                    ,SUM( IFNULL( D_SettlementFeeTaxOther, 0 ) )              AS D_SettlementFeeTaxOther
                    ,SUM( IFNULL( D_ClaimFeeOther, 0 ) )                      AS D_ClaimFeeOther
                    ,SUM( IFNULL( D_ClaimFeeTaxOther, 0 ) )                   AS D_ClaimFeeTaxOther
                    ,SUM( IFNULL( D_MonthlyFeeOther, 0 ) )                    AS D_MonthlyFeeOther
                    ,SUM( IFNULL( D_MonthlyFeeTaxOther, 0 ) )                 AS D_MonthlyFeeTaxOther
                    ,SUM( IFNULL( D_OemIncludeMonthlyFeeOther, 0 ) )          AS D_OemIncludeMonthlyFeeOther
                    ,SUM( IFNULL( D_OemIncludeMonthlyFeeTaxOther, 0 ) )       AS D_OemIncludeMonthlyFeeTaxOther
                    ,SUM( IFNULL( D_OemApiMonthlyFeeOther, 0 ) )              AS D_OemApiMonthlyFeeOther
                    ,SUM( IFNULL( D_OemApiMonthlyFeeTaxOther, 0 ) )           AS D_OemApiMonthlyFeeTaxOther
                    ,SUM( IFNULL( D_OemCreditNoticeMonthlyFeeOther, 0 ) )     AS D_OemCreditNoticeMonthlyFeeOther
                    ,SUM( IFNULL( D_OemCreditNoticeMonthlyFeeTaxOther, 0 ) )  AS D_OemCreditNoticeMonthlyFeeTaxOther
                    ,SUM( IFNULL( D_OemNCreditNoticeMonthlyFeeOther, 0 ) )    AS D_OemNCreditNoticeMonthlyFeeOther
                    ,SUM( IFNULL( D_OemNCreditNoticeMonthlyFeeTaxOther, 0 ) ) AS D_OemNCreditNoticeMonthlyFeeTaxOther
                    ,SUM( IFNULL( D_OemReserveMonthlyFeeOther, 0 ) )          AS D_OemReserveMonthlyFeeOther
                    ,SUM( IFNULL( D_OemReserveMonthlyFeeTaxOther, 0 ) )       AS D_OemReserveMonthlyFeeTaxOther
                    ,SUM( IFNULL( D_AddClaimFeeOther, 0 ) )                   AS D_AddClaimFeeOther
                    ,SUM( IFNULL( D_AddClaimFeeTaxOther, 0 ) )                AS D_AddClaimFeeTaxOther
                    ,SUM( IFNULL( D_DamageInterestAmountOther, 0 ) )          AS D_DamageInterestAmountOther
                    ,SUM( IFNULL( MB__AccountsReceivableBalance, 0 ) )        AS MB__AccountsReceivableBalance
                    ,SUM( IFNULL( M_ChargeCount, 0 ) )                        AS M_ChargeCount
                    ,SUM( IFNULL( M_ChargeAmount, 0 ) )                       AS M_ChargeAmount
                    ,SUM( IFNULL( M_CancelCount, 0 ) )                        AS M_CancelCount
                    ,SUM( IFNULL( M_CancelAmount, 0 ) )                       AS M_CancelAmount
                    ,SUM( IFNULL( M_SettlementBackCount, 0 ) )                AS M_SettlementBackCount
                    ,SUM( IFNULL( M_SettlementBackAmount, 0 ) )               AS M_SettlementBackAmount
                    ,SUM( IFNULL( M_OemTransferCount, 0 ) )                   AS M_OemTransferCount
                    ,SUM( IFNULL( M_OemTransferAmount, 0 ) )                  AS M_OemTransferAmount
                    ,SUM( IFNULL( M_ReClaimFeeCount, 0 ) )                    AS M_ReClaimFeeCount
                    ,SUM( IFNULL( M_ReClaimFeeAmount, 0 ) )                   AS M_ReClaimFeeAmount
                    ,SUM( IFNULL( M_DamageCount, 0 ) )                        AS M_DamageCount
                    ,SUM( IFNULL( M_DamageAmount, 0 ) )                       AS M_DamageAmount
                    ,SUM( IFNULL( M_ReceiptCount, 0 ) )                       AS M_ReceiptCount
                    ,SUM( IFNULL( M_ReceiptAmount, 0 ) )                      AS M_ReceiptAmount
                    ,SUM( IFNULL( M_RepayCount, 0 ) )                         AS M_RepayCount
                    ,SUM( IFNULL( M_RepayAmount, 0 ) )                        AS M_RepayAmount
                    ,SUM( IFNULL( M_BadDebtCount, 0 ) )                       AS M_BadDebtCount
                    ,SUM( IFNULL( M_BadDebtAmount, 0 ) )                      AS M_BadDebtAmount
                    ,SUM( IFNULL( M_OtherPaymentCount, 0 ) )                  AS M_OtherPaymentCount
                    ,SUM( IFNULL( M_OtherPaymentAmount, 0 ) )                 AS M_OtherPaymentAmount
                    ,SUM( IFNULL( M_AccountsReceivableBalance, 0 ) )          AS M_AccountsReceivableBalance
                    ,SUM( IFNULL( M_SuspensePaymentsAmount, 0 ) )             AS M_SuspensePaymentsAmount
                    ,SUM( IFNULL( M_AccountsReceivableBalanceDiff, 0 ) )      AS M_AccountsReceivableBalanceDiff
                    ,SUM( IFNULL( M_SettlementFee, 0 ) )                      AS M_SettlementFee
                    ,SUM( IFNULL( M_SettlementFeeTax, 0 ) )                   AS M_SettlementFeeTax
                    ,SUM( IFNULL( M_ClaimFee, 0 ) )                           AS M_ClaimFee
                    ,SUM( IFNULL( M_ClaimFeeTax, 0 ) )                        AS M_ClaimFeeTax
                    ,SUM( IFNULL( M_MonthlyFee, 0 ) )                         AS M_MonthlyFee
                    ,SUM( IFNULL( M_MonthlyFeeTax, 0 ) )                      AS M_MonthlyFeeTax
                    ,SUM( IFNULL( M_OemIncludeMonthlyFee, 0 ) )               AS M_OemIncludeMonthlyFee
                    ,SUM( IFNULL( M_OemIncludeMonthlyFeeTax, 0 ) )            AS M_OemIncludeMonthlyFeeTax
                    ,SUM( IFNULL( M_OemApiMonthlyFee, 0 ) )                   AS M_OemApiMonthlyFee
                    ,SUM( IFNULL( M_OemApiMonthlyFeeTax, 0 ) )                AS M_OemApiMonthlyFeeTax
                    ,SUM( IFNULL( M_OemCreditNoticeMonthlyFee, 0 ) )          AS M_OemCreditNoticeMonthlyFee
                    ,SUM( IFNULL( M_OemCreditNoticeMonthlyFeeTax, 0 ) )       AS M_OemCreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( M_OemNCreditNoticeMonthlyFee, 0 ) )         AS M_OemNCreditNoticeMonthlyFee
                    ,SUM( IFNULL( M_OemNCreditNoticeMonthlyFeeTax, 0 ) )      AS M_OemNCreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( M_OemReserveMonthlyFee, 0 ) )               AS M_OemReserveMonthlyFee
                    ,SUM( IFNULL( M_OemReserveMonthlyFeeTax, 0 ) )            AS M_OemReserveMonthlyFeeTax
                    ,SUM( IFNULL( M_AddClaimFee, 0 ) )                        AS M_AddClaimFee
                    ,SUM( IFNULL( M_AddClaimFeeTax, 0 ) )                     AS M_AddClaimFeeTax
                    ,SUM( IFNULL( M_DamageInterestAmount, 0 ) )               AS M_DamageInterestAmount
                    ,SUM( IFNULL( M_CanSettlementFee, 0 ) )                   AS M_CanSettlementFee
                    ,SUM( IFNULL( M_CanSettlementFeeTax, 0 ) )                AS M_CanSettlementFeeTax
                    ,SUM( IFNULL( M_CanClaimFee, 0 ) )                        AS M_CanClaimFee
                    ,SUM( IFNULL( M_CanClaimFeeTax, 0 ) )                     AS M_CanClaimFeeTax
                    ,SUM( IFNULL( M_SettlementFeeOther, 0 ) )                 AS M_SettlementFeeOther
                    ,SUM( IFNULL( M_SettlementFeeTaxOther, 0 ) )              AS M_SettlementFeeTaxOther
                    ,SUM( IFNULL( M_ClaimFeeOther, 0 ) )                      AS M_ClaimFeeOther
                    ,SUM( IFNULL( M_ClaimFeeTaxOther, 0 ) )                   AS M_ClaimFeeTaxOther
                    ,SUM( IFNULL( M_MonthlyFeeOther, 0 ) )                    AS M_MonthlyFeeOther
                    ,SUM( IFNULL( M_MonthlyFeeTaxOther, 0 ) )                 AS M_MonthlyFeeTaxOther
                    ,SUM( IFNULL( M_OemIncludeMonthlyFeeOther, 0 ) )          AS M_OemIncludeMonthlyFeeOther
                    ,SUM( IFNULL( M_OemIncludeMonthlyFeeTaxOther, 0 ) )       AS M_OemIncludeMonthlyFeeTaxOther
                    ,SUM( IFNULL( M_OemApiMonthlyFeeOther, 0 ) )              AS M_OemApiMonthlyFeeOther
                    ,SUM( IFNULL( M_OemApiMonthlyFeeTaxOther, 0 ) )           AS M_OemApiMonthlyFeeTaxOther
                    ,SUM( IFNULL( M_OemCreditNoticeMonthlyFeeOther, 0 ) )     AS M_OemCreditNoticeMonthlyFeeOther
                    ,SUM( IFNULL( M_OemCreditNoticeMonthlyFeeTaxOther, 0 ) )  AS M_OemCreditNoticeMonthlyFeeTaxOther
                    ,SUM( IFNULL( M_OemNCreditNoticeMonthlyFeeOther, 0 ) )    AS M_OemNCreditNoticeMonthlyFeeOther
                    ,SUM( IFNULL( M_OemNCreditNoticeMonthlyFeeTaxOther, 0 ) ) AS M_OemNCreditNoticeMonthlyFeeTaxOther
                    ,SUM( IFNULL( M_OemReserveMonthlyFeeOther, 0 ) )          AS M_OemReserveMonthlyFeeOther
                    ,SUM( IFNULL( M_OemReserveMonthlyFeeTaxOther, 0 ) )       AS M_OemReserveMonthlyFeeTaxOther
                    ,SUM( IFNULL( M_AddClaimFeeOther, 0 ) )                   AS M_AddClaimFeeOther
                    ,SUM( IFNULL( M_AddClaimFeeTaxOther, 0 ) )                AS M_AddClaimFeeTaxOther
                    ,SUM( IFNULL( M_DamageInterestAmountOther, 0 ) )          AS M_DamageInterestAmountOther
                FROM  AW_Oem_DailyStatisticsTable
                GROUP BY OemId, EnterpriseId ) AS aw_dst
    LEFT JOIN( SELECT
                     OemId                                                    AS AT_DSD_OemId
                    ,EnterpriseId                                             AS AT_DSD_EnterpriseId
                    ,SUM( IFNULL( OemSettlementFee, 0 ) )                     AS OemSettlementFee
                    ,SUM( IFNULL( OemSettlementFeeTax, 0 ) )                  AS OemSettlementFeeTax
                    ,SUM( IFNULL( OemClaimFee, 0 ) )                          AS OemClaimFee
                    ,SUM( IFNULL( OemClaimFeeTax, 0 ) )                       AS OemClaimFeeTax
                    ,SUM( IFNULL( OemMonthlyFee, 0 ) )                        AS OemMonthlyFee
                    ,SUM( IFNULL( OemMonthlyFeeTax, 0 ) )                     AS OemMonthlyFeeTax
                    ,SUM( IFNULL( OemIncludeMonthlyFee, 0 ) )                 AS OemIncludeMonthlyFee
                    ,SUM( IFNULL( OemIncludeMonthlyFeeTax, 0 ) )              AS OemIncludeMonthlyFeeTax
                    ,SUM( IFNULL( OemApiMonthlyFee, 0 ) )                     AS OemApiMonthlyFee
                    ,SUM( IFNULL( OemApiMonthlyFeeTax, 0 ) )                  AS OemApiMonthlyFeeTax
                    ,SUM( IFNULL( OemCreditNoticeMonthlyFee, 0 ) )            AS OemCreditNoticeMonthlyFee
                    ,SUM( IFNULL( OemCreditNoticeMonthlyFeeTax, 0 ) )         AS OemCreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( OemNCreditNoticeMonthlyFee, 0 ) )           AS OemNCreditNoticeMonthlyFee
                    ,SUM( IFNULL( OemNCreditNoticeMonthlyFeeTax, 0 ) )        AS OemNCreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( OemReserveMonthlyFee, 0 ) )                 AS OemReserveMonthlyFee
                    ,SUM( IFNULL( OemReserveMonthlyFeeTax, 0 ) )              AS OemReserveMonthlyFeeTax
                FROM  AT_Daily_SalesDetails
                WHERE ProcessingDate = v_BusinessDate
                AND   AccountDate    = v_AccountingMonth
                AND   SalesDefiniteConditions in ('着荷確認日', '入金日', '役務提供日', 'キャンセル日', 'キャンセル確定日', '月額固定費')
                AND      ValidFlg    =  1
                AND      OemId       <> 0
                GROUP BY OemId, EnterpriseId ) AS at_dsd
    ON  at_dsd.AT_DSD_OemId = aw_dst.OemId
    AND at_dsd.AT_DSD_EnterpriseId = aw_dst.EnterpriseId
    LEFT JOIN( SELECT
                     OemId                                                    AS AT_RFS_OemId
                    ,EnterpriseId                                             AS AT_RFS_EnterpriseId
                    ,SUM( IFNULL( Clm_L_ClaimFee, 0 ) )                       AS Clm_L_ClaimFee
                    ,SUM( IFNULL( Clm_L_ClaimFeeTax, 0 ) )                    AS Clm_L_ClaimFeeTax
                    ,SUM( IFNULL( Clm_L_DamageInterestAmount, 0 ) )           AS Clm_L_DamageInterestAmount
                FROM AT_ReissueFeeSpecification
                WHERE ProcessingDate = v_BusinessDate
                AND   AccountDate    = v_AccountingMonth
                AND   ValidFlg       = 1
                AND   OemId          <> 0
                -- 債権移管されていないもの（債権移管日が未来日含む）が抽出対象 ↓↓↓
                AND   (OemTransferDate IS NULL OR (OemTransferDate IS NOT NULL AND OemTransferDate >= v_BusinessDate))
                GROUP BY OemId, EnterpriseId ) AS at_rfs
    ON  at_rfs.AT_RFS_OemId = aw_dst.OemId
    AND at_rfs.AT_RFS_EnterpriseId = aw_dst.EnterpriseId
    INNER JOIN  M_Code c1
    ON      c1.CodeId   = 160
    AND     c1.KeyCode  = IFNULL( aw_dst.OemId, 0 )
    AND     c1.Class1   <> 0
    LEFT JOIN  T_Enterprise ent
    ON  ent.EnterpriseId           = aw_dst.EnterpriseId;

    -- ---------------------------------------------------------------------------------
    -- 2016/01/05_memo
    -- 会計の月次処理が実行されるまでは、業務日付の年月 <> 会計月の年月 になる。
    --   → つまり･･･会計月の翌月の年月 = 業務日付（集計日）の年月分を作成する必要がある
    -- 以下は 業務日付（集計日）の年月 <> 会計月の年月の場合に処理する
    -- ---------------------------------------------------------------------------------
    -- ------------------------------
    -- 3.1.OEM日次統計表ﾜｰｸをﾄﾗﾝｹｰﾄする
    -- ------------------------------
    TRUNCATE AW_Oem_DailyStatisticsTable;

    -- ------------------------------
    -- 3.2.業務日付=日別OEM日次統計表.集計日のﾃﾞｰﾀを集計出力し日次用のOEM日次統計表ﾜｰｸを出力する
    -- ------------------------------
    INSERT INTO AW_Oem_DailyStatisticsTable (
         ProcessingDate                                             -- 集計日
        ,AccountDate                                                -- 会計月
        ,OemId                                                      -- OEMID
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
        ,D_SettlementFee                                            -- 当日OEM決済手数料
        ,D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,D_ClaimFee                                                 -- 当日OEM請求手数料
        ,D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,D_MonthlyFee                                               -- 当日OEM月額固定費
        ,D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        ,D_SettlementFeeOther                                       -- 当日売上明細OEM決済手数料
        ,D_SettlementFeeTaxOther                                    -- 当日売上明細OEM決済手数料消費税
        ,D_ClaimFeeOther                                            -- 当日売上明細OEM請求手数料
        ,D_ClaimFeeTaxOther                                         -- 当日売上明細OEM請求手数料消費税
        ,D_MonthlyFeeOther                                          -- 当日売上明細OEM月額固定費
        ,D_MonthlyFeeTaxOther                                       -- 当日売上明細OEM月額固定費消費税
        ,D_OemIncludeMonthlyFeeOther                                -- 当日売上明細OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTaxOther                             -- 当日売上明細OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFeeOther                                    -- 当日売上明細OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTaxOther                                 -- 当日売上明細OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFeeOther                           -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTaxOther                        -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFeeOther                          -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTaxOther                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFeeOther                                -- 当日売上明細OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTaxOther                             -- 当日売上明細OEM月額固定費予備消費税
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
        ,M_OemTransferCount                                         -- 当月OEM移管件数
        ,M_OemTransferAmount                                        -- 当月OEM移管金額
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
        ,M_SuspensePaymentsAmount                                   -- OEM仮払金明細残高
        ,M_AccountsReceivableBalanceDiff                            -- 当月差額
        ,M_SettlementFee                                            -- 当月OEM決済手数料
        ,M_SettlementFeeTax                                         -- 当月OEM決済手数料消費税
        ,M_ClaimFee                                                 -- 当月OEM請求手数料
        ,M_ClaimFeeTax                                              -- 当月OEM請求手数料消費税
        ,M_MonthlyFee                                               -- 当月OEM月額固定費
        ,M_MonthlyFeeTax                                            -- 当月OEM月額固定費消費税
        ,M_OemIncludeMonthlyFee                                     -- 当月OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTax                                  -- 当月OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFee                                         -- 当月OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTax                                      -- 当月OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFee                                -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTax                             -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFee                               -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTax                            -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFee                                     -- 当月OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTax                                  -- 当月OEM月額固定費予備消費税
        ,M_AddClaimFee                                              -- 当月再発行手数料
        ,M_AddClaimFeeTax                                           -- 当月再発行手数料消費税
        ,M_DamageInterestAmount                                     -- 当月遅延損害金
        ,M_CanSettlementFee                                         -- 当月ｷｬﾝｾﾙOEM決済手数料
        ,M_CanSettlementFeeTax                                      -- 当月ｷｬﾝｾﾙOEM決済手数料消費税
        ,M_CanClaimFee                                              -- 当月ｷｬﾝｾﾙOEM請求手数料
        ,M_CanClaimFeeTax                                           -- 当月ｷｬﾝｾﾙOEM請求手数料消費税
        ,M_SettlementFeeOther                                       -- 当月売上明細OEM決済手数料
        ,M_SettlementFeeTaxOther                                    -- 当月売上明細OEM決済手数料消費税
        ,M_ClaimFeeOther                                            -- 当月売上明細OEM請求手数料
        ,M_ClaimFeeTaxOther                                         -- 当月売上明細OEM請求手数料消費税
        ,M_MonthlyFeeOther                                          -- 当月売上明細OEM月額固定費
        ,M_MonthlyFeeTaxOther                                       -- 当月売上明細OEM月額固定費消費税
        ,M_OemIncludeMonthlyFeeOther                                -- 当月売上明細OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTaxOther                             -- 当月売上明細OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFeeOther                                    -- 当月売上明細OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTaxOther                                 -- 当月売上明細OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFeeOther                           -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTaxOther                        -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFeeOther                          -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTaxOther                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFeeOther                                -- 当月売上明細OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTaxOther                             -- 当月売上明細OEM月額固定費予備消費税
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
        ,OemId                                                      -- OEMID
        ,EnterpriseId                                               -- 加盟店ID
        ,null                                                       -- 前日時点未収金残高
        ,SUM( IFNULL ( D_ChargeCount, 0 ) )                         -- 当日立替件数
        ,SUM( IFNULL ( D_ChargeAmount, 0 ) )                        -- 当日立替金額
        ,SUM( IFNULL ( D_CancelCount, 0 ) )                         -- 当日ｷｬﾝｾﾙ件数
        ,SUM( IFNULL ( D_CancelAmount, 0 ) )                        -- 当日ｷｬﾝｾﾙ立替金額
        ,SUM( IFNULL ( D_SettlementBackCount, 0 ) )                 -- 当日立替金戻し件数
        ,SUM( IFNULL ( D_SettlementBackAmount, 0 ) )                -- 当日立替金戻し金額
        ,SUM( IFNULL ( D_OemTransferCount, 0 ) )                    -- 当日OEM移管件数
        ,SUM( IFNULL ( D_OemTransferAmount, 0 ) )                   -- 当日OEM移管金額
        ,SUM( IFNULL ( D_ReClaimFeeCount, 0 ) )                     -- 当日再発行手数料件数
        ,SUM( IFNULL ( D_ReClaimFeeAmount, 0 ) )                    -- 当日再発行手数料額
        ,SUM( IFNULL ( D_DamageCount, 0 ) )                         -- 当日遅延損害金件数
        ,SUM( IFNULL ( D_DamageAmount, 0 ) )                        -- 当日遅延損害金額
        ,SUM( IFNULL ( D_ReceiptCount, 0 ) )                        -- 当日入金件数
        ,SUM( IFNULL ( D_ReceiptAmount, 0 ) )                       -- 当日入金額
        ,SUM( IFNULL ( D_RepayCount, 0 ) )                          -- 当日返金件数
        ,SUM( IFNULL ( D_RepayAmount, 0 ) )                         -- 当日返金額
        ,SUM( IFNULL ( D_BadDebtCount, 0 ) )                        -- 当日貸倒件数
        ,SUM( IFNULL ( D_BadDebtAmount, 0 ) )                       -- 当日貸倒金額
        ,SUM( IFNULL ( D_OtherPaymentCount, 0 ) )                   -- 当日その他件数
        ,SUM( IFNULL ( D_OtherPaymentAmount, 0 ) )                  -- 当日その他金額
        ,null                                                       -- 当日時点未収金残高
        ,SUM( IFNULL ( D_SettlementFee, 0 ) )                       -- 当日OEM決済手数料
        ,SUM( IFNULL ( D_SettlementFeeTax, 0 ) )                    -- 当日OEM決済手数料消費税
        ,SUM( IFNULL ( D_ClaimFee, 0 ) )                            -- 当日OEM請求手数料
        ,SUM( IFNULL ( D_ClaimFeeTax, 0 ) )                         -- 当日OEM請求手数料消費税
        ,SUM( IFNULL ( D_MonthlyFee, 0 ) )                          -- 当日OEM月額固定費
        ,SUM( IFNULL ( D_MonthlyFeeTax, 0 ) )                       -- 当日OEM月額固定費消費税
        ,SUM( IFNULL ( D_OemIncludeMonthlyFee, 0 ) )                -- 当日OEM同梱月額固定費
        ,SUM( IFNULL ( D_OemIncludeMonthlyFeeTax, 0 ) )             -- 当日OEM同梱月額固定費消費税
        ,SUM( IFNULL ( D_OemApiMonthlyFee, 0 ) )                    -- 当日OEMAPI月額固定費
        ,SUM( IFNULL ( D_OemApiMonthlyFeeTax, 0 ) )                 -- 当日OEMAPI月額固定費消費税
        ,SUM( IFNULL ( D_OemCreditNoticeMonthlyFee, 0 ) )           -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM( IFNULL ( D_OemCreditNoticeMonthlyFeeTax, 0 ) )        -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM( IFNULL ( D_OemNCreditNoticeMonthlyFee, 0 ) )          -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM( IFNULL ( D_OemNCreditNoticeMonthlyFeeTax, 0 ) )       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM( IFNULL ( D_OemReserveMonthlyFee, 0 ) )                -- 当日OEM月額固定費予備
        ,SUM( IFNULL ( D_OemReserveMonthlyFeeTax, 0 ) )             -- 当日OEM月額固定費予備消費税
        ,SUM( IFNULL ( D_AddClaimFee, 0 ) )                         -- 当日再発行手数料
        ,SUM( IFNULL ( D_AddClaimFeeTax, 0 ) )                      -- 当日再発行手数料消費税
        ,SUM( IFNULL ( D_DamageInterestAmount, 0 ) )                -- 当日遅延損害金
        ,SUM( IFNULL ( D_CanSettlementFee, 0 ) )                    -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,SUM( IFNULL ( D_CanSettlementFeeTax, 0 ) )                 -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,SUM( IFNULL ( D_CanClaimFee, 0 ) )                         -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,SUM( IFNULL ( D_CanClaimFeeTax, 0 ) )                      -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        ,null                                                       -- 当日売上明細OEM決済手数料
        ,null                                                       -- 当日売上明細OEM決済手数料消費税
        ,null                                                       -- 当日売上明細OEM請求手数料
        ,null                                                       -- 当日売上明細OEM請求手数料消費税
        ,null                                                       -- 当日売上明細OEM月額固定費
        ,null                                                       -- 当日売上明細OEM月額固定費消費税
        ,null                                                       -- 当日売上明細OEM同梱月額固定費
        ,null                                                       -- 当日売上明細OEM同梱月額固定費消費税
        ,null                                                       -- 当日売上明細OEMAPI月額固定費
        ,null                                                       -- 当日売上明細OEMAPI月額固定費消費税
        ,null                                                       -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日売上明細OEM月額固定費予備
        ,null                                                       -- 当日売上明細OEM月額固定費予備消費税
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
        ,null                                                       -- OEM仮払金明細残高
        ,null                                                       -- 当月差額
        ,null                                                       -- 当月OEM決済手数料
        ,null                                                       -- 当月OEM決済手数料消費税
        ,null                                                       -- 当月OEM請求手数料
        ,null                                                       -- 当月OEM請求手数料消費税
        ,null                                                       -- 当月OEM月額固定費
        ,null                                                       -- 当月OEM月額固定費消費税
        ,null                                                       -- 当月OEM同梱月額固定費
        ,null                                                       -- 当月OEM同梱月額固定費消費税
        ,null                                                       -- 当月OEMAPI月額固定費
        ,null                                                       -- 当月OEMAPI月額固定費消費税
        ,null                                                       -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月OEM月額固定費予備
        ,null                                                       -- 当月OEM月額固定費予備消費税
        ,null                                                       -- 当月再発行手数料
        ,null                                                       -- 当月再発行手数料消費税
        ,null                                                       -- 当月遅延損害金
        ,null                                                       -- 当月ｷｬﾝｾﾙOEM決済手数料
        ,null                                                       -- 当月ｷｬﾝｾﾙOEM決済手数料消費税
        ,null                                                       -- 当月ｷｬﾝｾﾙOEM請求手数料
        ,null                                                       -- 当月ｷｬﾝｾﾙOEM請求手数料消費税
        ,null                                                       -- 当月売上明細OEM決済手数料
        ,null                                                       -- 当月売上明細OEM決済手数料消費税
        ,null                                                       -- 当月売上明細OEM請求手数料
        ,null                                                       -- 当月売上明細OEM請求手数料消費税
        ,null                                                       -- 当月売上明細OEM月額固定費
        ,null                                                       -- 当月売上明細OEM月額固定費消費税
        ,null                                                       -- 当月売上明細OEM同梱月額固定費
        ,null                                                       -- 当月売上明細OEM同梱月額固定費消費税
        ,null                                                       -- 当月売上明細OEMAPI月額固定費
        ,null                                                       -- 当月売上明細OEMAPI月額固定費消費税
        ,null                                                       -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細OEM月額固定費予備
        ,null                                                       -- 当月売上明細OEM月額固定費予備消費税
        ,null                                                       -- 当月手数料明細再発行手数料
        ,null                                                       -- 当月手数料明細再発行手数料消費税
        ,null                                                       -- 当月手数料明細遅延損害金
        ,NOW()                                                      -- 登録日時
        ,pi_user_id                                                 -- 登録者
        ,NOW()                                                      -- 更新日時
        ,pi_user_id                                                 -- 更新者
        ,1                                                          -- 有効ﾌﾗｸﾞ
    FROM    AT_Oem_Daily2StatisticsTable
    WHERE   DATE_ADD(v_AccountingMonth, INTERVAL 1 MONTH) = AccountDate
    AND     v_BusinessDate = ProcessingDate
    GROUP BY AccountDate
            ,OemId
            ,EnterpriseId;

    -- ------------------------------
    -- 3.3.業務日付=日別OEM日次統計表.集計日のﾃﾞｰﾀを集計出力し日次用のOEM日次統計表ﾜｰｸを出力する
    -- ------------------------------
    INSERT INTO AW_Oem_DailyStatisticsTable (
         ProcessingDate                                             -- 集計日
        ,AccountDate                                                -- 会計月
        ,OemId                                                      -- OEMID
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
        ,D_SettlementFee                                            -- 当日OEM決済手数料
        ,D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,D_ClaimFee                                                 -- 当日OEM請求手数料
        ,D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,D_MonthlyFee                                               -- 当日OEM月額固定費
        ,D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        ,D_SettlementFeeOther                                       -- 当日売上明細OEM決済手数料
        ,D_SettlementFeeTaxOther                                    -- 当日売上明細OEM決済手数料消費税
        ,D_ClaimFeeOther                                            -- 当日売上明細OEM請求手数料
        ,D_ClaimFeeTaxOther                                         -- 当日売上明細OEM請求手数料消費税
        ,D_MonthlyFeeOther                                          -- 当日売上明細OEM月額固定費
        ,D_MonthlyFeeTaxOther                                       -- 当日売上明細OEM月額固定費消費税
        ,D_OemIncludeMonthlyFeeOther                                -- 当日売上明細OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTaxOther                             -- 当日売上明細OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFeeOther                                    -- 当日売上明細OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTaxOther                                 -- 当日売上明細OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFeeOther                           -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTaxOther                        -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFeeOther                          -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTaxOther                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFeeOther                                -- 当日売上明細OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTaxOther                             -- 当日売上明細OEM月額固定費予備消費税
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
        ,M_OemTransferCount                                         -- 当月OEM移管件数
        ,M_OemTransferAmount                                        -- 当月OEM移管金額
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
        ,M_SuspensePaymentsAmount                                   -- OEM仮払金明細残高
        ,M_AccountsReceivableBalanceDiff                            -- 当月差額
        ,M_SettlementFee                                            -- 当月OEM決済手数料
        ,M_SettlementFeeTax                                         -- 当月OEM決済手数料消費税
        ,M_ClaimFee                                                 -- 当月OEM請求手数料
        ,M_ClaimFeeTax                                              -- 当月OEM請求手数料消費税
        ,M_MonthlyFee                                               -- 当月OEM月額固定費
        ,M_MonthlyFeeTax                                            -- 当月OEM月額固定費消費税
        ,M_OemIncludeMonthlyFee                                     -- 当月OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTax                                  -- 当月OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFee                                         -- 当月OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTax                                      -- 当月OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFee                                -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTax                             -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFee                               -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTax                            -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFee                                     -- 当月OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTax                                  -- 当月OEM月額固定費予備消費税
        ,M_AddClaimFee                                              -- 当月再発行手数料
        ,M_AddClaimFeeTax                                           -- 当月再発行手数料消費税
        ,M_DamageInterestAmount                                     -- 当月遅延損害金
        ,M_CanSettlementFee                                         -- 当月ｷｬﾝｾﾙOEM決済手数料
        ,M_CanSettlementFeeTax                                      -- 当月ｷｬﾝｾﾙOEM決済手数料消費税
        ,M_CanClaimFee                                              -- 当月ｷｬﾝｾﾙOEM請求手数料
        ,M_CanClaimFeeTax                                           -- 当月ｷｬﾝｾﾙOEM請求手数料消費税
        ,M_SettlementFeeOther                                       -- 当月売上明細OEM決済手数料
        ,M_SettlementFeeTaxOther                                    -- 当月売上明細OEM決済手数料消費税
        ,M_ClaimFeeOther                                            -- 当月売上明細OEM請求手数料
        ,M_ClaimFeeTaxOther                                         -- 当月売上明細OEM請求手数料消費税
        ,M_MonthlyFeeOther                                          -- 当月売上明細OEM月額固定費
        ,M_MonthlyFeeTaxOther                                       -- 当月売上明細OEM月額固定費消費税
        ,M_OemIncludeMonthlyFeeOther                                -- 当月売上明細OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTaxOther                             -- 当月売上明細OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFeeOther                                    -- 当月売上明細OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTaxOther                                 -- 当月売上明細OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFeeOther                           -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTaxOther                        -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFeeOther                          -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTaxOther                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFeeOther                                -- 当月売上明細OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTaxOther                             -- 当月売上明細OEM月額固定費予備消費税
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
        ,OemId                                                      -- OEMID
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
        ,null                                                       -- 当日OEM決済手数料
        ,null                                                       -- 当日OEM決済手数料消費税
        ,null                                                       -- 当日OEM請求手数料
        ,null                                                       -- 当日OEM請求手数料消費税
        ,null                                                       -- 当日OEM月額固定費
        ,null                                                       -- 当日OEM月額固定費消費税
        ,null                                                       -- 当日OEM同梱月額固定費
        ,null                                                       -- 当日OEM同梱月額固定費消費税
        ,null                                                       -- 当日OEMAPI月額固定費
        ,null                                                       -- 当日OEMAPI月額固定費消費税
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM月額固定費予備
        ,null                                                       -- 当日OEM月額固定費予備消費税
        ,null                                                       -- 当日再発行手数料
        ,null                                                       -- 当日再発行手数料消費税
        ,null                                                       -- 当日遅延損害金
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        ,null                                                       -- 当日売上明細OEM決済手数料
        ,null                                                       -- 当日売上明細OEM決済手数料消費税
        ,null                                                       -- 当日売上明細OEM請求手数料
        ,null                                                       -- 当日売上明細OEM請求手数料消費税
        ,null                                                       -- 当日売上明細OEM月額固定費
        ,null                                                       -- 当日売上明細OEM月額固定費消費税
        ,null                                                       -- 当日売上明細OEM同梱月額固定費
        ,null                                                       -- 当日売上明細OEM同梱月額固定費消費税
        ,null                                                       -- 当日売上明細OEMAPI月額固定費
        ,null                                                       -- 当日売上明細OEMAPI月額固定費消費税
        ,null                                                       -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日売上明細OEM月額固定費予備
        ,null                                                       -- 当日売上明細OEM月額固定費予備消費税
        ,null                                                       -- 当日手数料明細再発行手数料
        ,null                                                       -- 当日手数料明細再発行手数料消費税
        ,null                                                       -- 当日手数料明細遅延損害金
        ,null                                                       -- 月初未収金残高
        ,SUM( IFNULL ( D_ChargeCount, 0 ) )                         -- 当月立替件数
        ,SUM( IFNULL ( D_ChargeAmount, 0 ) )                        -- 当月立替金額
        ,SUM( IFNULL ( D_CancelCount, 0 ) )                         -- 当月ｷｬﾝｾﾙ件数
        ,SUM( IFNULL ( D_CancelAmount, 0 ) )                        -- 当月ｷｬﾝｾﾙ立替金額
        ,SUM( IFNULL ( D_SettlementBackCount, 0 ) )                 -- 当月立替金戻し件数
        ,SUM( IFNULL ( D_SettlementBackAmount, 0 ) )                -- 当月立替金戻し金額
        ,SUM( IFNULL ( D_OemTransferCount, 0 ) )                    -- 当月OEM移管件数
        ,SUM( IFNULL ( D_OemTransferAmount, 0 ) )                   -- 当月OEM移管金額
        ,SUM( IFNULL ( D_ReClaimFeeCount, 0 ) )                     -- 当日再発行手数料件数
        ,SUM( IFNULL ( D_ReClaimFeeAmount, 0 ) )                    -- 当日再発行手数料額
        ,SUM( IFNULL ( D_DamageCount, 0 ) )                         -- 当日遅延損害金件数
        ,SUM( IFNULL ( D_DamageAmount, 0 ) )                        -- 当日遅延損害金額
        ,SUM( IFNULL ( D_ReceiptCount, 0 ) )                        -- 当月入金件数
        ,SUM( IFNULL ( D_ReceiptAmount, 0 ) )                       -- 当月入金額
        ,SUM( IFNULL ( D_RepayCount, 0 ) )                          -- 当月返金件数
        ,SUM( IFNULL ( D_RepayAmount, 0 ) )                         -- 当月返金額
        ,SUM( IFNULL( D_BadDebtCount, 0 ) )                         -- 当月貸倒件数
        ,SUM( IFNULL( D_BadDebtAmount, 0 ) )                        -- 当月貸倒金額
        ,SUM( IFNULL( D_OtherPaymentCount, 0 ) )                    -- 当月その他件数
        ,SUM( IFNULL( D_OtherPaymentAmount, 0 ) )                   -- 当月その他金額
        ,null                                                       -- 当月時点未収金残高
        ,null                                                       -- OEM仮払金明細残高
        ,null                                                       -- 当月差額
        ,SUM( IFNULL ( D_SettlementFee, 0 ) )                       -- 当月OEM決済手数料
        ,SUM( IFNULL ( D_SettlementFeeTax, 0 ) )                    -- 当月OEM決済手数料消費税
        ,SUM( IFNULL ( D_ClaimFee, 0 ) )                            -- 当月OEM請求手数料
        ,SUM( IFNULL ( D_ClaimFeeTax, 0 ) )                         -- 当月OEM請求手数料消費税
        ,SUM( IFNULL ( D_MonthlyFee, 0 ) )                          -- 当月OEM月額固定費
        ,SUM( IFNULL ( D_MonthlyFeeTax, 0 ) )                       -- 当月OEM月額固定費消費税
        ,SUM( IFNULL ( D_OemIncludeMonthlyFee, 0 ) )                -- 当月OEM同梱月額固定費
        ,SUM( IFNULL ( D_OemIncludeMonthlyFeeTax, 0 ) )             -- 当月OEM同梱月額固定費消費税
        ,SUM( IFNULL ( D_OemApiMonthlyFee, 0 ) )                    -- 当月OEMAPI月額固定費
        ,SUM( IFNULL ( D_OemApiMonthlyFeeTax, 0 ) )                 -- 当月OEMAPI月額固定費消費税
        ,SUM( IFNULL ( D_OemCreditNoticeMonthlyFee, 0 ) )           -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM( IFNULL ( D_OemCreditNoticeMonthlyFeeTax, 0 ) )        -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM( IFNULL ( D_OemNCreditNoticeMonthlyFee, 0 ) )          -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM( IFNULL ( D_OemNCreditNoticeMonthlyFeeTax, 0 ) )       -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM( IFNULL ( D_OemReserveMonthlyFee, 0 ) )                -- 当月OEM月額固定費予備
        ,SUM( IFNULL ( D_OemReserveMonthlyFeeTax, 0 ) )             -- 当月OEM月額固定費予備消費税
        ,SUM( IFNULL ( D_AddClaimFee, 0 ) )                         -- 当月再発行手数料
        ,SUM( IFNULL ( D_AddClaimFeeTax, 0 ) )                      -- 当月再発行手数料消費税
        ,SUM( IFNULL ( D_DamageInterestAmount, 0 ) )                -- 当月遅延損害金
        ,SUM( IFNULL ( D_CanSettlementFee, 0 ) )                    -- 当月ｷｬﾝｾﾙOEM決済手数料
        ,SUM( IFNULL ( D_CanSettlementFeeTax, 0 ) )                 -- 当月ｷｬﾝｾﾙOEM決済手数料消費税
        ,SUM( IFNULL ( D_CanClaimFee, 0 ) )                         -- 当月ｷｬﾝｾﾙOEM請求手数料
        ,SUM( IFNULL ( D_CanClaimFeeTax, 0 ) )                      -- 当月ｷｬﾝｾﾙOEM請求手数料消費税
        ,null                                                       -- 当月売上明細OEM決済手数料
        ,null                                                       -- 当月売上明細OEM決済手数料消費税
        ,null                                                       -- 当月売上明細OEM請求手数料
        ,null                                                       -- 当月売上明細OEM請求手数料消費税
        ,null                                                       -- 当月売上明細OEM月額固定費
        ,null                                                       -- 当月売上明細OEM月額固定費消費税
        ,null                                                       -- 当月売上明細OEM同梱月額固定費
        ,null                                                       -- 当月売上明細OEM同梱月額固定費消費税
        ,null                                                       -- 当月売上明細OEMAPI月額固定費
        ,null                                                       -- 当月売上明細OEMAPI月額固定費消費税
        ,null                                                       -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細OEM月額固定費予備
        ,null                                                       -- 当月売上明細OEM月額固定費予備消費税
        ,null                                                       -- 当月手数料明細再発行手数料
        ,null                                                       -- 当月手数料明細再発行手数料消費税
        ,null                                                       -- 当月手数料明細遅延損害金
        ,NOW()                                                      -- 登録日時
        ,pi_user_id                                                 -- 登録者
        ,NOW()                                                      -- 更新日時
        ,pi_user_id                                                 -- 更新者
        ,1                                                          -- 有効ﾌﾗｸﾞ
    FROM    AT_Oem_Daily2StatisticsTable
    WHERE   DATE_ADD( v_AccountingMonth, INTERVAL 1 MONTH ) = AccountDate
    AND     v_BusinessDate = ProcessingDate
    GROUP BY ProcessingDate
            ,AccountDate
            ,OemId
            ,EnterpriseId;

    -- ------------------------------
    -- 3.4.加盟店別に業務日付>OEM日次統計表.集計日、且つ、MAX（OEM日次統計表.集計日）【前日】
    --     のﾃﾞｰﾀを出力し日次用のOEM日次統計表ﾜｰｸを出力する
    -- ------------------------------
    INSERT INTO AW_Oem_DailyStatisticsTable (
         ProcessingDate                                             -- 集計日
        ,AccountDate                                                -- 会計月
        ,OemId                                                      -- OEMID
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
        ,D_SettlementFee                                            -- 当日OEM決済手数料
        ,D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,D_ClaimFee                                                 -- 当日OEM請求手数料
        ,D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,D_MonthlyFee                                               -- 当日OEM月額固定費
        ,D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        ,D_SettlementFeeOther                                       -- 当日売上明細OEM決済手数料
        ,D_SettlementFeeTaxOther                                    -- 当日売上明細OEM決済手数料消費税
        ,D_ClaimFeeOther                                            -- 当日売上明細OEM請求手数料
        ,D_ClaimFeeTaxOther                                         -- 当日売上明細OEM請求手数料消費税
        ,D_MonthlyFeeOther                                          -- 当日売上明細OEM月額固定費
        ,D_MonthlyFeeTaxOther                                       -- 当日売上明細OEM月額固定費消費税
        ,D_OemIncludeMonthlyFeeOther                                -- 当日売上明細OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTaxOther                             -- 当日売上明細OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFeeOther                                    -- 当日売上明細OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTaxOther                                 -- 当日売上明細OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFeeOther                           -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTaxOther                        -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFeeOther                          -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTaxOther                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFeeOther                                -- 当日売上明細OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTaxOther                             -- 当日売上明細OEM月額固定費予備消費税
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
        ,M_OemTransferCount                                         -- 当月OEM移管件数
        ,M_OemTransferAmount                                        -- 当月OEM移管金額
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
        ,M_SuspensePaymentsAmount                                   -- OEM仮払金明細残高
        ,M_AccountsReceivableBalanceDiff                            -- 当月差額
        ,M_SettlementFee                                            -- 当月OEM決済手数料
        ,M_SettlementFeeTax                                         -- 当月OEM決済手数料消費税
        ,M_ClaimFee                                                 -- 当月OEM請求手数料
        ,M_ClaimFeeTax                                              -- 当月OEM請求手数料消費税
        ,M_MonthlyFee                                               -- 当月OEM月額固定費
        ,M_MonthlyFeeTax                                            -- 当月OEM月額固定費消費税
        ,M_OemIncludeMonthlyFee                                     -- 当月OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTax                                  -- 当月OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFee                                         -- 当月OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTax                                      -- 当月OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFee                                -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTax                             -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFee                               -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTax                            -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFee                                     -- 当月OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTax                                  -- 当月OEM月額固定費予備消費税
        ,M_AddClaimFee                                              -- 当月再発行手数料
        ,M_AddClaimFeeTax                                           -- 当月再発行手数料消費税
        ,M_DamageInterestAmount                                     -- 当月遅延損害金
        ,M_CanSettlementFee                                         -- 当月ｷｬﾝｾﾙOEM決済手数料
        ,M_CanSettlementFeeTax                                      -- 当月ｷｬﾝｾﾙOEM決済手数料消費税
        ,M_CanClaimFee                                              -- 当月ｷｬﾝｾﾙOEM請求手数料
        ,M_CanClaimFeeTax                                           -- 当月ｷｬﾝｾﾙOEM請求手数料消費税
        ,M_SettlementFeeOther                                       -- 当月売上明細OEM決済手数料
        ,M_SettlementFeeTaxOther                                    -- 当月売上明細OEM決済手数料消費税
        ,M_ClaimFeeOther                                            -- 当月売上明細OEM請求手数料
        ,M_ClaimFeeTaxOther                                         -- 当月売上明細OEM請求手数料消費税
        ,M_MonthlyFeeOther                                          -- 当月売上明細OEM月額固定費
        ,M_MonthlyFeeTaxOther                                       -- 当月売上明細OEM月額固定費消費税
        ,M_OemIncludeMonthlyFeeOther                                -- 当月売上明細OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTaxOther                             -- 当月売上明細OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFeeOther                                    -- 当月売上明細OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTaxOther                                 -- 当月売上明細OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFeeOther                           -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTaxOther                        -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFeeOther                          -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTaxOther                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFeeOther                                -- 当月売上明細OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTaxOther                             -- 当月売上明細OEM月額固定費予備消費税
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
        ,dst.OemId                                                  -- OEMID
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
        ,null                                                       -- 当日OEM決済手数料
        ,null                                                       -- 当日OEM決済手数料消費税
        ,null                                                       -- 当日OEM請求手数料
        ,null                                                       -- 当日OEM請求手数料消費税
        ,null                                                       -- 当日OEM月額固定費
        ,null                                                       -- 当日OEM月額固定費消費税
        ,null                                                       -- 当日OEM同梱月額固定費
        ,null                                                       -- 当日OEM同梱月額固定費消費税
        ,null                                                       -- 当日OEMAPI月額固定費
        ,null                                                       -- 当日OEMAPI月額固定費消費税
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日OEM月額固定費予備
        ,null                                                       -- 当日OEM月額固定費予備消費税
        ,null                                                       -- 当日再発行手数料
        ,null                                                       -- 当日再発行手数料消費税
        ,null                                                       -- 当日遅延損害金
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        ,null                                                       -- 当日売上明細OEM決済手数料
        ,null                                                       -- 当日売上明細OEM決済手数料消費税
        ,null                                                       -- 当日売上明細OEM請求手数料
        ,null                                                       -- 当日売上明細OEM請求手数料消費税
        ,null                                                       -- 当日売上明細OEM月額固定費
        ,null                                                       -- 当日売上明細OEM月額固定費消費税
        ,null                                                       -- 当日売上明細OEM同梱月額固定費
        ,null                                                       -- 当日売上明細OEM同梱月額固定費消費税
        ,null                                                       -- 当日売上明細OEMAPI月額固定費
        ,null                                                       -- 当日売上明細OEMAPI月額固定費消費税
        ,null                                                       -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日売上明細OEM月額固定費予備
        ,null                                                       -- 当日売上明細OEM月額固定費予備消費税
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
        ,null                                                       -- 当日再発行手数料件数
        ,null                                                       -- 当日再発行手数料額
        ,null                                                       -- 当日遅延損害金件数
        ,null                                                       -- 当日遅延損害金額
        ,null                                                       -- 当月入金件数
        ,null                                                       -- 当月入金額
        ,null                                                       -- 当月返金件数
        ,null                                                       -- 当月返金額
        ,null                                                       -- 当月貸倒件数
        ,null                                                       -- 当月貸倒金額
        ,null                                                       -- 当月その他件数
        ,null                                                       -- 当月その他金額
        ,null                                                       -- 当月時点未収金残高
        ,null                                                       -- OEM仮払金明細残高
        ,null                                                       -- 当月差額
        ,null                                                       -- 当月OEM決済手数料
        ,null                                                       -- 当月OEM決済手数料消費税
        ,null                                                       -- 当月OEM請求手数料
        ,null                                                       -- 当月OEM請求手数料消費税
        ,null                                                       -- 当月OEM月額固定費
        ,null                                                       -- 当月OEM月額固定費消費税
        ,null                                                       -- 当月OEM同梱月額固定費
        ,null                                                       -- 当月OEM同梱月額固定費消費税
        ,null                                                       -- 当月OEMAPI月額固定費
        ,null                                                       -- 当月OEMAPI月額固定費消費税
        ,null                                                       -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月OEM月額固定費予備
        ,null                                                       -- 当月OEM月額固定費予備消費税
        ,null                                                       -- 当月再発行手数料
        ,null                                                       -- 当月再発行手数料消費税
        ,null                                                       -- 当月遅延損害金
        ,null                                                       -- 当月ｷｬﾝｾﾙOEM決済手数料
        ,null                                                       -- 当月ｷｬﾝｾﾙOEM決済手数料消費税
        ,null                                                       -- 当月ｷｬﾝｾﾙOEM請求手数料
        ,null                                                       -- 当月ｷｬﾝｾﾙOEM請求手数料消費税
        ,null                                                       -- 当月売上明細OEM決済手数料
        ,null                                                       -- 当月売上明細OEM決済手数料消費税
        ,null                                                       -- 当月売上明細OEM請求手数料
        ,null                                                       -- 当月売上明細OEM請求手数料消費税
        ,null                                                       -- 当月売上明細OEM月額固定費
        ,null                                                       -- 当月売上明細OEM月額固定費消費税
        ,null                                                       -- 当月売上明細OEM同梱月額固定費
        ,null                                                       -- 当月売上明細OEM同梱月額固定費消費税
        ,null                                                       -- 当月売上明細OEMAPI月額固定費
        ,null                                                       -- 当月売上明細OEMAPI月額固定費消費税
        ,null                                                       -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細OEM月額固定費予備
        ,null                                                       -- 当月売上明細OEM月額固定費予備消費税
        ,null                                                       -- 当月手数料明細再発行手数料
        ,null                                                       -- 当月手数料明細再発行手数料消費税
        ,null                                                       -- 当月手数料明細遅延損害金
        ,NOW()                                                      -- 登録日時
        ,pi_user_id                                                 -- 登録者
        ,NOW()                                                      -- 更新日時
        ,pi_user_id                                                 -- 更新者
        ,1                                                          -- 有効ﾌﾗｸﾞ
    FROM  AT_Oem_DailyStatisticsTable dst
    INNER JOIN ( SELECT OemId, EnterpriseId, MAX( ProcessingDate ) ProcessingDate FROM AT_Oem_DailyStatisticsTable
                 WHERE v_BusinessDate      > ProcessingDate
                 AND   DATE_ADD( v_AccountingMonth, INTERVAL 1 MONTH )   = AccountDate
                 AND   DailyMonthlyFlg     = 0
                 GROUP BY OemId, EnterpriseId ) AS dstw
    ON    dst.OemId = dstw.OemId
    AND   dst.EnterpriseId = dstw.EnterpriseId
    AND   dst.ProcessingDate = dstw.ProcessingDate
    AND   dst.DailyMonthlyFlg = 0
    AND   dst.AccountDate = DATE_ADD( v_AccountingMonth, INTERVAL 1 MONTH );

    -- ------------------------------
    -- 3.6.OEM日次統計表ﾜｰｸのﾃﾞｰﾀを加盟店別に集計出力し日次用のOEM日次統計表を出力する
    -- ------------------------------
    INSERT INTO AT_Oem_DailyStatisticsTable (
         DailyMonthlyFlg                                            -- 日次･月次区分
        ,ProcessingDate                                             -- 集計日
        ,AccountDate                                                -- 会計月
        ,OemId                                                      -- OEMID
        ,OemNameKj                                                  -- OEM先名
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
        ,D_SettlementFee                                            -- 当日OEM決済手数料
        ,D_SettlementFeeTax                                         -- 当日OEM決済手数料消費税
        ,D_ClaimFee                                                 -- 当日OEM請求手数料
        ,D_ClaimFeeTax                                              -- 当日OEM請求手数料消費税
        ,D_MonthlyFee                                               -- 当日OEM月額固定費
        ,D_MonthlyFeeTax                                            -- 当日OEM月額固定費消費税
        ,D_OemIncludeMonthlyFee                                     -- 当日OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTax                                  -- 当日OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFee                                         -- 当日OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTax                                      -- 当日OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFee                                -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTax                             -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFee                               -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTax                            -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFee                                     -- 当日OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTax                                  -- 当日OEM月額固定費予備消費税
        ,D_AddClaimFee                                              -- 当日再発行手数料
        ,D_AddClaimFeeTax                                           -- 当日再発行手数料消費税
        ,D_DamageInterestAmount                                     -- 当日遅延損害金
        ,D_CanSettlementFee                                         -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,D_CanSettlementFeeTax                                      -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,D_CanClaimFee                                              -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,D_CanClaimFeeTax                                           -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        ,D_SettlementFeeTotal                                       -- 当日合計OEM決済手数料
        ,D_SettlementFeeTaxTotal                                    -- 当日合計OEM決済手数料消費税
        ,D_ClaimFeeTotal                                            -- 当日合計OEM請求手数料
        ,D_ClaimFeeTaxTotal                                         -- 当日合計OEM請求手数料消費税
        ,D_MonthlyFeeTotal                                          -- 当日合計OEM月額固定費
        ,D_MonthlyFeeTaxTotal                                       -- 当日合計OEM月額固定費消費税
        ,D_OemIncludeMonthlyFeeTotal                                -- 当日合計OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTaxTotal                             -- 当日合計OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFeeTotal                                    -- 当日合計OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTaxTotal                                 -- 当日合計OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFeeTotal                           -- 当日合計OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTaxTotal                        -- 当日合計OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFeeTotal                          -- 当日合計OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTaxTotal                       -- 当日合計OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFeeTotal                                -- 当日合計OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTaxTotal                             -- 当日合計OEM月額固定費予備消費税
        ,D_AddClaimFeeTotal                                         -- 当日合計再発行手数料
        ,D_AddClaimFeeTaxTotal                                      -- 当日合計再発行手数料消費税
        ,D_DamageInterestAmountTotal                                -- 当日合計遅延損害金
        ,D_AllTotal                                                 -- 当日総合計
        ,D_SettlementFeeOther                                       -- 当日売上明細OEM決済手数料
        ,D_SettlementFeeTaxOther                                    -- 当日売上明細OEM決済手数料消費税
        ,D_ClaimFeeOther                                            -- 当日売上明細OEM請求手数料
        ,D_ClaimFeeTaxOther                                         -- 当日売上明細OEM請求手数料消費税
        ,D_MonthlyFeeOther                                          -- 当日売上明細OEM月額固定費
        ,D_MonthlyFeeTaxOther                                       -- 当日売上明細OEM月額固定費消費税
        ,D_OemIncludeMonthlyFeeOther                                -- 当日売上明細OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTaxOther                             -- 当日売上明細OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFeeOther                                    -- 当日売上明細OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTaxOther                                 -- 当日売上明細OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFeeOther                           -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTaxOther                        -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFeeOther                          -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTaxOther                       -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFeeOther                                -- 当日売上明細OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTaxOther                             -- 当日売上明細OEM月額固定費予備消費税
        ,D_AddClaimFeeOther                                         -- 当日手数料明細再発行手数料
        ,D_AddClaimFeeTaxOther                                      -- 当日手数料明細再発行手数料消費税
        ,D_DamageInterestAmountOther                                -- 当日手数料明細遅延損害金
        ,D_SettlementFeeDiff                                        -- 当日差額OEM決済手数料
        ,D_SettlementFeeTaxDiff                                     -- 当日差額OEM決済手数料消費税
        ,D_ClaimFeeDiff                                             -- 当日差額OEM請求手数料
        ,D_ClaimFeeTaxDiff                                          -- 当日差額OEM請求手数料消費税
        ,D_MonthlyFeeDiff                                           -- 当日差額OEM月額固定費
        ,D_MonthlyFeeTaxDiff                                        -- 当日差額OEM月額固定費消費税
        ,D_OemIncludeMonthlyFeeDiff                                 -- 当日差額OEM同梱月額固定費
        ,D_OemIncludeMonthlyFeeTaxDiff                              -- 当日差額OEM同梱月額固定費消費税
        ,D_OemApiMonthlyFeeDiff                                     -- 当日差額OEMAPI月額固定費
        ,D_OemApiMonthlyFeeTaxDiff                                  -- 当日差額OEMAPI月額固定費消費税
        ,D_OemCreditNoticeMonthlyFeeDiff                            -- 当日差額OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemCreditNoticeMonthlyFeeTaxDiff                         -- 当日差額OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemNCreditNoticeMonthlyFeeDiff                           -- 当日差額OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,D_OemNCreditNoticeMonthlyFeeTaxDiff                        -- 当日差額OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,D_OemReserveMonthlyFeeDiff                                 -- 当日差額OEM月額固定費予備
        ,D_OemReserveMonthlyFeeTaxDiff                              -- 当日差額OEM月額固定費予備消費税
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
        ,M_OemTransferCount                                         -- 当月OEM移管件数
        ,M_OemTransferAmount                                        -- 当月OEM移管金額
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
        ,M_SuspensePaymentsAmount                                   -- OEM仮払金明細残高
        ,M_AccountsReceivableBalanceDiff                            -- 当月差額
        ,M_SettlementFee                                            -- 当月OEM決済手数料
        ,M_SettlementFeeTax                                         -- 当月OEM決済手数料消費税
        ,M_ClaimFee                                                 -- 当月OEM請求手数料
        ,M_ClaimFeeTax                                              -- 当月OEM請求手数料消費税
        ,M_MonthlyFee                                               -- 当月OEM月額固定費
        ,M_MonthlyFeeTax                                            -- 当月OEM月額固定費消費税
        ,M_OemIncludeMonthlyFee                                     -- 当月OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTax                                  -- 当月OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFee                                         -- 当月OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTax                                      -- 当月OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFee                                -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTax                             -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFee                               -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTax                            -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFee                                     -- 当月OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTax                                  -- 当月OEM月額固定費予備消費税
        ,M_AddClaimFee                                              -- 当月再発行手数料
        ,M_AddClaimFeeTax                                           -- 当月再発行手数料消費税
        ,M_DamageInterestAmount                                     -- 当月遅延損害金
        ,M_CanSettlementFee                                         -- 当月ｷｬﾝｾﾙOEM決済手数料
        ,M_CanSettlementFeeTax                                      -- 当月ｷｬﾝｾﾙOEM決済手数料消費税
        ,M_CanClaimFee                                              -- 当月ｷｬﾝｾﾙOEM請求手数料
        ,M_CanClaimFeeTax                                           -- 当月ｷｬﾝｾﾙOEM請求手数料消費税
        ,M_SettlementFeeTotal                                       -- 当月合計OEM決済手数料
        ,M_SettlementFeeTaxTotal                                    -- 当月合計OEM決済手数料消費税
        ,M_ClaimFeeTotal                                            -- 当月合計OEM請求手数料
        ,M_ClaimFeeTaxTotal                                         -- 当月合計OEM請求手数料消費税
        ,M_MonthlyFeeTotal                                          -- 当月合計OEM月額固定費
        ,M_MonthlyFeeTaxTotal                                       -- 当月合計OEM月額固定費消費税
        ,M_OemIncludeMonthlyFeeTotal                                -- 当月合計OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTaxTotal                             -- 当月合計OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFeeTotal                                    -- 当月合計OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTaxTotal                                 -- 当月合計OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFeeTotal                           -- 当月合計OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTaxTotal                        -- 当月合計OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFeeTotal                          -- 当月合計OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTaxTotal                       -- 当月合計OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFeeTotal                                -- 当月合計OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTaxTotal                             -- 当月合計OEM月額固定費予備消費税
        ,M_AddClaimFeeTotal                                         -- 当月合計再発行手数料
        ,M_AddClaimFeeTaxTotal                                      -- 当月合計再発行手数料消費税
        ,M_DamageInterestAmountTotal                                -- 当月合計遅延損害金
        ,M_AllTotal                                                 -- 当月総合計
        ,M_SettlementFeeOther                                       -- 当月売上明細OEM決済手数料
        ,M_SettlementFeeTaxOther                                    -- 当月売上明細OEM決済手数料消費税
        ,M_ClaimFeeOther                                            -- 当月売上明細OEM請求手数料
        ,M_ClaimFeeTaxOther                                         -- 当月売上明細OEM請求手数料消費税
        ,M_MonthlyFeeOther                                          -- 当月売上明細OEM月額固定費
        ,M_MonthlyFeeTaxOther                                       -- 当月売上明細OEM月額固定費消費税
        ,M_OemIncludeMonthlyFeeOther                                -- 当月売上明細OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTaxOther                             -- 当月売上明細OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFeeOther                                    -- 当月売上明細OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTaxOther                                 -- 当月売上明細OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFeeOther                           -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTaxOther                        -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFeeOther                          -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTaxOther                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFeeOther                                -- 当月売上明細OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTaxOther                             -- 当月売上明細OEM月額固定費予備消費税
        ,M_AddClaimFeeOther                                         -- 当月手数料明細再発行手数料
        ,M_AddClaimFeeTaxOther                                      -- 当月手数料明細再発行手数料消費税
        ,M_DamageInterestAmountOther                                -- 当月手数料明細遅延損害金
        ,M_SettlementFeeDiff                                        -- 当月差額OEM決済手数料
        ,M_SettlementFeeTaxDiff                                     -- 当月差額OEM決済手数料消費税
        ,M_ClaimFeeDiff                                             -- 当月差額OEM請求手数料
        ,M_ClaimFeeTaxDiff                                          -- 当月差額OEM請求手数料消費税
        ,M_MonthlyFeeDiff                                           -- 当月差額OEM月額固定費
        ,M_MonthlyFeeTaxDiff                                        -- 当月差額OEM月額固定費消費税
        ,M_OemIncludeMonthlyFeeDiff                                 -- 当月差額OEM同梱月額固定費
        ,M_OemIncludeMonthlyFeeTaxDiff                              -- 当月差額OEM同梱月額固定費消費税
        ,M_OemApiMonthlyFeeDiff                                     -- 当月差額OEMAPI月額固定費
        ,M_OemApiMonthlyFeeTaxDiff                                  -- 当月差額OEMAPI月額固定費消費税
        ,M_OemCreditNoticeMonthlyFeeDiff                            -- 当月差額OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemCreditNoticeMonthlyFeeTaxDiff                         -- 当月差額OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemNCreditNoticeMonthlyFeeDiff                           -- 当月差額OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,M_OemNCreditNoticeMonthlyFeeTaxDiff                        -- 当月差額OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,M_OemReserveMonthlyFeeDiff                                 -- 当月差額OEM月額固定費予備
        ,M_OemReserveMonthlyFeeTaxDiff                              -- 当月差額OEM月額固定費予備消費税
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
        ,aw_dst.OemId                                               -- OEMID
        ,c1.Class2                                                  -- OEM先名
        ,aw_dst.EnterpriseId                                        -- 加盟店ID
        ,ent.EnterpriseNameKj                                       -- 加盟店名
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
        ,aw_dst.D_SettlementFee                                     -- 当日OEM決済手数料
        ,aw_dst.D_SettlementFeeTax                                  -- 当日OEM決済手数料消費税
        ,aw_dst.D_ClaimFee                                          -- 当日OEM請求手数料
        ,aw_dst.D_ClaimFeeTax                                       -- 当日OEM請求手数料消費税
        ,aw_dst.D_MonthlyFee                                        -- 当日OEM月額固定費
        ,aw_dst.D_MonthlyFeeTax                                     -- 当日OEM月額固定費消費税
        ,aw_dst.D_OemIncludeMonthlyFee                              -- 当日OEM同梱月額固定費
        ,aw_dst.D_OemIncludeMonthlyFeeTax                           -- 当日OEM同梱月額固定費消費税
        ,aw_dst.D_OemApiMonthlyFee                                  -- 当日OEMAPI月額固定費
        ,aw_dst.D_OemApiMonthlyFeeTax                               -- 当日OEMAPI月額固定費消費税
        ,aw_dst.D_OemCreditNoticeMonthlyFee                         -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_OemCreditNoticeMonthlyFeeTax                      -- 当日OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_OemNCreditNoticeMonthlyFee                        -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_OemNCreditNoticeMonthlyFeeTax                     -- 当日OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_OemReserveMonthlyFee                              -- 当日OEM月額固定費予備
        ,aw_dst.D_OemReserveMonthlyFeeTax                           -- 当日OEM月額固定費予備消費税
        ,aw_dst.D_AddClaimFee                                       -- 当日再発行手数料
        ,aw_dst.D_AddClaimFeeTax                                    -- 当日再発行手数料消費税
        ,aw_dst.D_DamageInterestAmount                              -- 当日遅延損害金
        ,aw_dst.D_CanSettlementFee                                  -- 当日ｷｬﾝｾﾙOEM決済手数料
        ,aw_dst.D_CanSettlementFeeTax                               -- 当日ｷｬﾝｾﾙOEM決済手数料消費税
        ,aw_dst.D_CanClaimFee                                       -- 当日ｷｬﾝｾﾙOEM請求手数料
        ,aw_dst.D_CanClaimFeeTax                                    -- 当日ｷｬﾝｾﾙOEM請求手数料消費税
        ,aw_dst.D_SettlementFee + aw_dst.D_CanSettlementFee         -- 当日合計OEM決済手数料
        ,aw_dst.D_SettlementFeeTax + aw_dst.D_CanSettlementFeeTax   -- 当日合計OEM決済手数料消費税
        ,aw_dst.D_ClaimFee + aw_dst.D_CanClaimFee                   -- 当日合計OEM請求手数料
        ,aw_dst.D_ClaimFeeTax + aw_dst.D_CanClaimFeeTax             -- 当日合計OEM請求手数料消費税
        ,aw_dst.D_MonthlyFee                                        -- 当日合計OEM月額固定費
        ,aw_dst.D_MonthlyFeeTax                                     -- 当日合計OEM月額固定費消費税
        ,aw_dst.D_OemIncludeMonthlyFee                              -- 当日合計OEM同梱月額固定費
        ,aw_dst.D_OemIncludeMonthlyFeeTax                           -- 当日合計OEM同梱月額固定費消費税
        ,aw_dst.D_OemApiMonthlyFee                                  -- 当日合計OEMAPI月額固定費
        ,aw_dst.D_OemApiMonthlyFeeTax                               -- 当日合計OEMAPI月額固定費消費税
        ,aw_dst.D_OemCreditNoticeMonthlyFee                         -- 当日合計OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_OemCreditNoticeMonthlyFeeTax                      -- 当日合計OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_OemNCreditNoticeMonthlyFee                        -- 当日合計OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_OemNCreditNoticeMonthlyFeeTax                     -- 当日合計OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_OemReserveMonthlyFee                              -- 当日合計OEM月額固定費予備
        ,aw_dst.D_OemReserveMonthlyFeeTax                           -- 当日合計OEM月額固定費予備消費税
        ,aw_dst.D_AddClaimFee                                       -- 当日合計再発行手数料
        ,aw_dst.D_AddClaimFeeTax                                    -- 当日合計再発行手数料消費税
        ,aw_dst.D_DamageInterestAmount                              -- 当日合計遅延損害金
        ,aw_dst.D_SettlementFee + aw_dst.D_CanSettlementFee +
         aw_dst.D_SettlementFeeTax + aw_dst.D_CanSettlementFeeTax +
         aw_dst.D_ClaimFee + aw_dst.D_CanClaimFee +
         aw_dst.D_ClaimFeeTax + aw_dst.D_CanClaimFeeTax +
         aw_dst.D_MonthlyFee + aw_dst.D_MonthlyFeeTax +
         aw_dst.D_OemIncludeMonthlyFee + aw_dst.D_OemIncludeMonthlyFeeTax +
         aw_dst.D_OemApiMonthlyFee + aw_dst.D_OemApiMonthlyFeeTax +
         aw_dst.D_OemCreditNoticeMonthlyFee+ aw_dst.D_OemCreditNoticeMonthlyFeeTax +
         aw_dst.D_OemNCreditNoticeMonthlyFee + aw_dst.D_OemNCreditNoticeMonthlyFeeTax +
         aw_dst.D_OemReserveMonthlyFee + aw_dst.D_OemReserveMonthlyFeeTax +
         aw_dst.D_AddClaimFee + aw_dst.D_AddClaimFeeTax +
         aw_dst.D_DamageInterestAmount                              -- 当日総合計
        ,IFNULL(at_dsd.OemSettlementFee, 0)                         -- 当日売上明細OEM決済手数料
        ,IFNULL(at_dsd.OemSettlementFeeTax, 0)                      -- 当日売上明細OEM決済手数料消費税
        ,IFNULL(at_dsd.OemClaimFee, 0)                              -- 当日売上明細OEM請求手数料
        ,IFNULL(at_dsd.OemClaimFeeTax, 0)                           -- 当日売上明細OEM請求手数料消費税
        ,IFNULL(at_dsd.OemMonthlyFee, 0)                            -- 当日売上明細OEM月額固定費
        ,IFNULL(at_dsd.OemMonthlyFeeTax, 0)                         -- 当日売上明細OEM月額固定費消費税
        ,IFNULL(at_dsd.OemIncludeMonthlyFee, 0)                     -- 当日売上明細OEM同梱月額固定費
        ,IFNULL(at_dsd.OemIncludeMonthlyFeeTax, 0)                  -- 当日売上明細OEM同梱月額固定費消費税
        ,IFNULL(at_dsd.OemApiMonthlyFee, 0)                         -- 当日売上明細OEMAPI月額固定費
        ,IFNULL(at_dsd.OemApiMonthlyFeeTax, 0)                      -- 当日売上明細OEMAPI月額固定費消費税
        ,IFNULL(at_dsd.OemCreditNoticeMonthlyFee, 0)                -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,IFNULL(at_dsd.OemCreditNoticeMonthlyFeeTax, 0)             -- 当日売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,IFNULL(at_dsd.OemNCreditNoticeMonthlyFee, 0)               -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,IFNULL(at_dsd.OemNCreditNoticeMonthlyFeeTax, 0)            -- 当日売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,IFNULL(at_dsd.OemReserveMonthlyFee, 0)                     -- 当日売上明細OEM月額固定費予備
        ,IFNULL(at_dsd.OemReserveMonthlyFeeTax, 0)                  -- 当日売上明細OEM月額固定費予備消費税
        ,IFNULL(at_rfs.Clm_L_ClaimFee, 0)                           -- 当日手数料明細再発行手数料
        ,IFNULL(at_rfs.Clm_L_ClaimFeeTax, 0)                        -- 当日手数料明細再発行手数料消費税
        ,IFNULL(at_rfs.Clm_L_DamageInterestAmount, 0)               -- 当日手数料明細遅延損害金
        ,aw_dst.D_SettlementFee + aw_dst.D_CanSettlementFee - IFNULL( at_dsd.OemSettlementFee, 0)               -- 当日差額OEM決済手数料
        ,aw_dst.D_SettlementFeeTax + aw_dst.D_CanSettlementFeeTax - IFNULL( at_dsd.OemSettlementFeeTax, 0)      -- 当日差額OEM決済手数料消費税
        ,aw_dst.D_ClaimFee + aw_dst.D_CanClaimFee - IFNULL( at_dsd.OemClaimFee, 0)                              -- 当日差額OEM請求手数料
        ,aw_dst.D_ClaimFeeTax + aw_dst.D_CanClaimFeeTax - IFNULL( at_dsd.OemClaimFeeTax, 0)                     -- 当日差額OEM請求手数料消費税
        ,aw_dst.D_MonthlyFee - IFNULL( at_dsd.OemMonthlyFee , 0)                                                -- 当日差額OEM月額固定費
        ,aw_dst.D_MonthlyFeeTax - IFNULL( at_dsd.OemMonthlyFeeTax, 0)                                           -- 当日差額OEM月額固定費消費税
        ,aw_dst.D_OemIncludeMonthlyFee - IFNULL( at_dsd.OemIncludeMonthlyFee, 0)                                -- 当日差額OEM同梱月額固定費
        ,aw_dst.D_OemIncludeMonthlyFeeTax - IFNULL( at_dsd.OemIncludeMonthlyFeeTax, 0)                          -- 当日差額OEM同梱月額固定費消費税
        ,aw_dst.D_OemApiMonthlyFee - IFNULL( at_dsd.OemApiMonthlyFee, 0)                                        -- 当日差額OEMAPI月額固定費
        ,aw_dst.D_OemApiMonthlyFeeTax - IFNULL( at_dsd.OemApiMonthlyFeeTax, 0)                                  -- 当日差額OEMAPI月額固定費消費税
        ,aw_dst.D_OemCreditNoticeMonthlyFee - IFNULL( at_dsd.OemCreditNoticeMonthlyFee, 0)                      -- 当日差額OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_OemCreditNoticeMonthlyFeeTax - IFNULL( at_dsd.OemCreditNoticeMonthlyFeeTax, 0)                -- 当日差額OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_OemNCreditNoticeMonthlyFee - IFNULL( at_dsd.OemNCreditNoticeMonthlyFee, 0)                    -- 当日差額OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.D_OemNCreditNoticeMonthlyFeeTax - IFNULL( at_dsd.OemNCreditNoticeMonthlyFeeTax, 0)              -- 当日差額OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.D_OemReserveMonthlyFee - IFNULL( at_dsd.OemReserveMonthlyFee, 0)                                -- 当日差額OEM月額固定費予備
        ,aw_dst.D_OemReserveMonthlyFeeTax - IFNULL( at_dsd.OemReserveMonthlyFeeTax, 0)                          -- 当日差額OEM月額固定費予備消費税
        ,aw_dst.D_AddClaimFee - IFNULL( at_rfs.Clm_L_ClaimFee, 0)                                               -- 当日差額OEM再発行手数料
        ,aw_dst.D_AddClaimFeeTax - IFNULL( at_rfs.Clm_L_ClaimFeeTax, 0)                                         -- 当日差額OEM再発行手数料消費税
        ,aw_dst.D_DamageInterestAmount - IFNULL( at_rfs.Clm_L_DamageInterestAmount, 0)                          -- 当日差額遅延損害金
        ,aw_dst.MB__AccountsReceivableBalance                       -- 月初未収金残高
        ,aw_dst.M_ChargeCount                                       -- 当月立替件数
        ,aw_dst.M_ChargeAmount                                      -- 当月立替金額
        ,aw_dst.M_CancelCount                                       -- 当月ｷｬﾝｾﾙ件数
        ,aw_dst.M_CancelAmount                                      -- 当月ｷｬﾝｾﾙ立替金額
        ,aw_dst.M_SettlementBackCount                               -- 当月立替金戻し件数
        ,aw_dst.M_SettlementBackAmount                              -- 当月立替金戻し金額
        ,aw_dst.M_OemTransferCount                                  -- 当月OEM移管件数
        ,aw_dst.M_OemTransferAmount                                 -- 当月OEM移管金額
        ,aw_dst.M_ReClaimFeeCount                                   -- 当日再発行手数料件数
        ,aw_dst.M_ReClaimFeeAmount                                  -- 当日再発行手数料額
        ,aw_dst.M_DamageCount                                       -- 当日遅延損害金件数
        ,aw_dst.M_DamageAmount                                      -- 当日遅延損害金額
        ,aw_dst.M_ReceiptCount                                      -- 当月入金件数
        ,aw_dst.M_ReceiptAmount                                     -- 当月入金額
        ,aw_dst.M_RepayCount                                        -- 当月返金件数
        ,aw_dst.M_RepayAmount                                       -- 当月返金額
        ,aw_dst.M_BadDebtCount                                      -- 当月貸倒件数
        ,aw_dst.M_BadDebtAmount                                     -- 当月貸倒金額
        ,aw_dst.M_OtherPaymentCount                                 -- 当月その他件数
        ,aw_dst.M_OtherPaymentAmount                                -- 当月その他金額
        ,aw_dst.MB__AccountsReceivableBalance +
         aw_dst.M_ChargeAmount +  aw_dst.M_CancelAmount +
         aw_dst.M_SettlementBackAmount + aw_dst.M_OemTransferAmount +
         aw_dst.M_ReClaimFeeAmount + aw_dst.M_DamageAmount +
         aw_dst.M_ReceiptAmount + aw_dst.M_RepayAmount +
         aw_dst.M_BadDebtAmount + aw_dst.M_OtherPaymentAmount       -- 当月時点未収金残高
        ,null                                                       -- OEM仮払金明細残高
        ,null                                                       -- 当月差額
        ,aw_dst.M_SettlementFee                                     -- 当月OEM決済手数料
        ,aw_dst.M_SettlementFeeTax                                  -- 当月OEM決済手数料消費税
        ,aw_dst.M_ClaimFee                                          -- 当月OEM請求手数料
        ,aw_dst.M_ClaimFeeTax                                       -- 当月OEM請求手数料消費税
        ,aw_dst.M_MonthlyFee                                        -- 当月OEM月額固定費
        ,aw_dst.M_MonthlyFeeTax                                     -- 当月OEM月額固定費消費税
        ,aw_dst.M_OemIncludeMonthlyFee                              -- 当月OEM同梱月額固定費
        ,aw_dst.M_OemIncludeMonthlyFeeTax                           -- 当月OEM同梱月額固定費消費税
        ,aw_dst.M_OemApiMonthlyFee                                  -- 当月OEMAPI月額固定費
        ,aw_dst.M_OemApiMonthlyFeeTax                               -- 当月OEMAPI月額固定費消費税
        ,aw_dst.M_OemCreditNoticeMonthlyFee                         -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.M_OemCreditNoticeMonthlyFeeTax                      -- 当月OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.M_OemNCreditNoticeMonthlyFee                        -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.M_OemNCreditNoticeMonthlyFeeTax                     -- 当月OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.M_OemReserveMonthlyFee                              -- 当月OEM月額固定費予備
        ,aw_dst.M_OemReserveMonthlyFeeTax                           -- 当月OEM月額固定費予備消費税
        ,aw_dst.M_AddClaimFee                                       -- 当月再発行手数料
        ,aw_dst.M_AddClaimFeeTax                                    -- 当月再発行手数料消費税
        ,aw_dst.M_DamageInterestAmount                              -- 当月遅延損害金
        ,aw_dst.M_CanSettlementFee                                  -- 当月ｷｬﾝｾﾙOEM決済手数料
        ,aw_dst.M_CanSettlementFeeTax                               -- 当月ｷｬﾝｾﾙOEM決済手数料消費税
        ,aw_dst.M_CanClaimFee                                       -- 当月ｷｬﾝｾﾙOEM請求手数料
        ,aw_dst.M_CanClaimFeeTax                                    -- 当月ｷｬﾝｾﾙOEM請求手数料消費税
        ,aw_dst.M_SettlementFee + aw_dst.M_CanSettlementFee         -- 当月合計OEM決済手数料
        ,aw_dst.M_SettlementFeeTax + aw_dst.M_CanSettlementFeeTax   -- 当月合計OEM決済手数料消費税
        ,aw_dst.M_ClaimFee + aw_dst.M_CanClaimFee                   -- 当月合計OEM請求手数料
        ,aw_dst.M_ClaimFeeTax + aw_dst.M_CanClaimFeeTax             -- 当月合計OEM請求手数料消費税
        ,aw_dst.M_MonthlyFee                                        -- 当月合計OEM月額固定費
        ,aw_dst.M_MonthlyFeeTax                                     -- 当月合計OEM月額固定費消費税
        ,aw_dst.M_OemIncludeMonthlyFee                              -- 当月合計OEM同梱月額固定費
        ,aw_dst.M_OemIncludeMonthlyFeeTax                           -- 当月合計OEM同梱月額固定費消費税
        ,aw_dst.M_OemApiMonthlyFee                                  -- 当月合計OEMAPI月額固定費
        ,aw_dst.M_OemApiMonthlyFeeTax                               -- 当月合計OEMAPI月額固定費消費税
        ,aw_dst.M_OemCreditNoticeMonthlyFee                         -- 当月合計OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.M_OemCreditNoticeMonthlyFeeTax                      -- 当月合計OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.M_OemNCreditNoticeMonthlyFee                        -- 当月合計OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.M_OemNCreditNoticeMonthlyFeeTax                     -- 当月合計OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.M_OemReserveMonthlyFee                              -- 当月合計OEM月額固定費予備
        ,aw_dst.M_OemReserveMonthlyFeeTax                           -- 当月合計OEM月額固定費予備消費税
        ,aw_dst.M_AddClaimFee                                       -- 当月合計再発行手数料
        ,aw_dst.M_AddClaimFeeTax                                    -- 当月合計再発行手数料消費税
        ,aw_dst.M_DamageInterestAmount                              -- 当月合計遅延損害金
        ,aw_dst.M_SettlementFee + aw_dst.M_CanSettlementFee +
         aw_dst.M_SettlementFeeTax + aw_dst.M_CanSettlementFeeTax +
         aw_dst.M_ClaimFee + aw_dst.M_CanClaimFee +
         aw_dst.M_ClaimFeeTax + aw_dst.M_CanClaimFeeTax +
         aw_dst.M_MonthlyFee + aw_dst.M_MonthlyFeeTax +
         aw_dst.M_OemIncludeMonthlyFee + aw_dst.M_OemIncludeMonthlyFeeTax +
         aw_dst.M_OemApiMonthlyFee + aw_dst.M_OemApiMonthlyFeeTax +
         aw_dst.M_OemCreditNoticeMonthlyFee + aw_dst.M_OemCreditNoticeMonthlyFeeTax +
         aw_dst.M_OemNCreditNoticeMonthlyFee + aw_dst.M_OemNCreditNoticeMonthlyFeeTax +
         aw_dst.M_OemReserveMonthlyFee + aw_dst.M_OemReserveMonthlyFeeTax +
         aw_dst.M_AddClaimFee + aw_dst.M_AddClaimFeeTax +
         aw_dst.M_DamageInterestAmount                              -- 当月総合計
        ,null                                                       -- 当月売上明細OEM決済手数料
        ,null                                                       -- 当月売上明細OEM決済手数料消費税
        ,null                                                       -- 当月売上明細OEM請求手数料
        ,null                                                       -- 当月売上明細OEM請求手数料消費税
        ,null                                                       -- 当月売上明細OEM月額固定費
        ,null                                                       -- 当月売上明細OEM月額固定費消費税
        ,null                                                       -- 当月売上明細OEM同梱月額固定費
        ,null                                                       -- 当月売上明細OEM同梱月額固定費消費税
        ,null                                                       -- 当月売上明細OEMAPI月額固定費
        ,null                                                       -- 当月売上明細OEMAPI月額固定費消費税
        ,null                                                       -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月売上明細OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月売上明細OEM月額固定費予備
        ,null                                                       -- 当月売上明細OEM月額固定費予備消費税
        ,null                                                       -- 当月手数料明細再発行手数料
        ,null                                                       -- 当月手数料明細再発行手数料消費税
        ,null                                                       -- 当月手数料明細遅延損害金
        ,null                                                       -- 当月差額OEM決済手数料
        ,null                                                       -- 当月差額OEM決済手数料消費税
        ,null                                                       -- 当月差額OEM請求手数料
        ,null                                                       -- 当月差額OEM請求手数料消費税
        ,null                                                       -- 当月差額OEM月額固定費
        ,null                                                       -- 当月差額OEM月額固定費消費税
        ,null                                                       -- 当月差額OEM同梱月額固定費
        ,null                                                       -- 当月差額OEM同梱月額固定費消費税
        ,null                                                       -- 当月差額OEMAPI月額固定費
        ,null                                                       -- 当月差額OEMAPI月額固定費消費税
        ,null                                                       -- 当月差額OEM与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月差額OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月差額OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当月差額OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当月差額OEM月額固定費予備
        ,null                                                       -- 当月差額OEM月額固定費予備消費税
        ,null                                                       -- 当月差額再発行手数料
        ,null                                                       -- 当月差額再発行手数料消費税
        ,null                                                       -- 当月差額遅延損害金
        ,NOW()                                                      -- 登録日時
        ,pi_user_id                                                 -- 登録者
        ,NOW()                                                      -- 更新日時
        ,pi_user_id                                                 -- 更新者
        ,1                                                          -- 有効ﾌﾗｸﾞ
    FROM (      SELECT
                     OemId                                                     AS OemId
                    ,EnterpriseId                                              AS EnterpriseId
                    ,SUM( IFNULL( DB__AccountsReceivableBalance, 0 ) )         AS DB__AccountsReceivableBalance
                    ,SUM( IFNULL( D_ChargeCount, 0 ) )                         AS D_ChargeCount
                    ,SUM( IFNULL( D_ChargeAmount, 0 ) )                        AS D_ChargeAmount
                    ,SUM( IFNULL( D_CancelCount, 0 ) )                         AS D_CancelCount
                    ,SUM( IFNULL( D_CancelAmount, 0 ) )                        AS D_CancelAmount
                    ,SUM( IFNULL( D_SettlementBackCount, 0 ) )                 AS D_SettlementBackCount
                    ,SUM( IFNULL( D_SettlementBackAmount, 0 ) )                AS D_SettlementBackAmount
                    ,SUM( IFNULL( D_OemTransferCount, 0 ) )                    AS D_OemTransferCount
                    ,SUM( IFNULL( D_OemTransferAmount, 0 ) )                   AS D_OemTransferAmount
                    ,SUM( IFNULL( D_ReClaimFeeCount, 0 ) )                     AS D_ReClaimFeeCount
                    ,SUM( IFNULL( D_ReClaimFeeAmount, 0 ) )                    AS D_ReClaimFeeAmount
                    ,SUM( IFNULL( D_DamageCount, 0 ) )                         AS D_DamageCount
                    ,SUM( IFNULL( D_DamageAmount, 0 ) )                        AS D_DamageAmount
                    ,SUM( IFNULL( D_ReceiptCount, 0 ) )                        AS D_ReceiptCount
                    ,SUM( IFNULL( D_ReceiptAmount, 0 ) )                       AS D_ReceiptAmount
                    ,SUM( IFNULL( D_RepayCount, 0 ) )                          AS D_RepayCount
                    ,SUM( IFNULL( D_RepayAmount, 0 ) )                         AS D_RepayAmount
                    ,SUM( IFNULL( D_BadDebtCount, 0 ) )                        AS D_BadDebtCount
                    ,SUM( IFNULL( D_BadDebtAmount, 0 ) )                       AS D_BadDebtAmount
                    ,SUM( IFNULL( D_OtherPaymentCount, 0 ) )                   AS D_OtherPaymentCount
                    ,SUM( IFNULL( D_OtherPaymentAmount, 0 ) )                  AS D_OtherPaymentAmount
                    ,SUM( IFNULL( D_AccountsReceivableBalance, 0 ) )           AS D_AccountsReceivableBalance
                    ,SUM( IFNULL( D_SettlementFee, 0 ) )                       AS D_SettlementFee
                    ,SUM( IFNULL( D_SettlementFeeTax, 0 ) )                    AS D_SettlementFeeTax
                    ,SUM( IFNULL( D_ClaimFee, 0 ) )                            AS D_ClaimFee
                    ,SUM( IFNULL( D_ClaimFeeTax, 0 ) )                         AS D_ClaimFeeTax
                    ,SUM( IFNULL( D_MonthlyFee, 0 ) )                          AS D_MonthlyFee
                    ,SUM( IFNULL( D_MonthlyFeeTax, 0 ) )                       AS D_MonthlyFeeTax
                    ,SUM( IFNULL( D_OemIncludeMonthlyFee, 0 ) )                AS D_OemIncludeMonthlyFee
                    ,SUM( IFNULL( D_OemIncludeMonthlyFeeTax, 0 ) )             AS D_OemIncludeMonthlyFeeTax
                    ,SUM( IFNULL( D_OemApiMonthlyFee, 0 ) )                    AS D_OemApiMonthlyFee
                    ,SUM( IFNULL( D_OemApiMonthlyFeeTax, 0 ) )                 AS D_OemApiMonthlyFeeTax
                    ,SUM( IFNULL( D_OemCreditNoticeMonthlyFee, 0 ) )           AS D_OemCreditNoticeMonthlyFee
                    ,SUM( IFNULL( D_OemCreditNoticeMonthlyFeeTax, 0 ) )        AS D_OemCreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( D_OemNCreditNoticeMonthlyFee, 0 ) )          AS D_OemNCreditNoticeMonthlyFee
                    ,SUM( IFNULL( D_OemNCreditNoticeMonthlyFeeTax, 0 ) )       AS D_OemNCreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( D_OemReserveMonthlyFee, 0 ) )                AS D_OemReserveMonthlyFee
                    ,SUM( IFNULL( D_OemReserveMonthlyFeeTax, 0 ) )             AS D_OemReserveMonthlyFeeTax
                    ,SUM( IFNULL( D_AddClaimFee, 0 ) )                         AS D_AddClaimFee
                    ,SUM( IFNULL( D_AddClaimFeeTax, 0 ) )                      AS D_AddClaimFeeTax
                    ,SUM( IFNULL( D_DamageInterestAmount, 0 ) )                AS D_DamageInterestAmount
                    ,SUM( IFNULL( D_CanSettlementFee, 0 ) )                    AS D_CanSettlementFee
                    ,SUM( IFNULL( D_CanSettlementFeeTax, 0 ) )                 AS D_CanSettlementFeeTax
                    ,SUM( IFNULL( D_CanClaimFee, 0 ) )                         AS D_CanClaimFee
                    ,SUM( IFNULL( D_CanClaimFeeTax, 0 ) )                      AS D_CanClaimFeeTax
                    ,SUM( IFNULL( D_SettlementFeeOther, 0 ) )                  AS D_SettlementFeeOther
                    ,SUM( IFNULL( D_SettlementFeeTaxOther, 0 ) )               AS D_SettlementFeeTaxOther
                    ,SUM( IFNULL( D_ClaimFeeOther, 0 ) )                       AS D_ClaimFeeOther
                    ,SUM( IFNULL( D_ClaimFeeTaxOther, 0 ) )                    AS D_ClaimFeeTaxOther
                    ,SUM( IFNULL( D_MonthlyFeeOther, 0 ) )                     AS D_MonthlyFeeOther
                    ,SUM( IFNULL( D_MonthlyFeeTaxOther, 0 ) )                  AS D_MonthlyFeeTaxOther
                    ,SUM( IFNULL( D_OemIncludeMonthlyFeeOther, 0 ) )           AS D_OemIncludeMonthlyFeeOther
                    ,SUM( IFNULL( D_OemIncludeMonthlyFeeTaxOther, 0 ) )        AS D_OemIncludeMonthlyFeeTaxOther
                    ,SUM( IFNULL( D_OemApiMonthlyFeeOther, 0 ) )               AS D_OemApiMonthlyFeeOther
                    ,SUM( IFNULL( D_OemApiMonthlyFeeTaxOther, 0 ) )            AS D_OemApiMonthlyFeeTaxOther
                    ,SUM( IFNULL( D_OemCreditNoticeMonthlyFeeOther, 0 ) )      AS D_OemCreditNoticeMonthlyFeeOther
                    ,SUM( IFNULL( D_OemCreditNoticeMonthlyFeeTaxOther, 0 ) )   AS D_OemCreditNoticeMonthlyFeeTaxOther
                    ,SUM( IFNULL( D_OemNCreditNoticeMonthlyFeeOther, 0 ) )     AS D_OemNCreditNoticeMonthlyFeeOther
                    ,SUM( IFNULL( D_OemNCreditNoticeMonthlyFeeTaxOther, 0 ) )  AS D_OemNCreditNoticeMonthlyFeeTaxOther
                    ,SUM( IFNULL( D_OemReserveMonthlyFeeOther, 0 ) )           AS D_OemReserveMonthlyFeeOther
                    ,SUM( IFNULL( D_OemReserveMonthlyFeeTaxOther, 0 ) )        AS D_OemReserveMonthlyFeeTaxOther
                    ,SUM( IFNULL( D_AddClaimFeeOther, 0 ) )                    AS D_AddClaimFeeOther
                    ,SUM( IFNULL( D_AddClaimFeeTaxOther, 0 ) )                 AS D_AddClaimFeeTaxOther
                    ,SUM( IFNULL( D_DamageInterestAmountOther, 0 ) )           AS D_DamageInterestAmountOther
                    ,SUM( IFNULL( MB__AccountsReceivableBalance, 0 ) )         AS MB__AccountsReceivableBalance
                    ,SUM( IFNULL( M_ChargeCount, 0 ) )                         AS M_ChargeCount
                    ,SUM( IFNULL( M_ChargeAmount, 0 ) )                        AS M_ChargeAmount
                    ,SUM( IFNULL( M_CancelCount, 0 ) )                         AS M_CancelCount
                    ,SUM( IFNULL( M_CancelAmount, 0 ) )                        AS M_CancelAmount
                    ,SUM( IFNULL( M_SettlementBackCount, 0 ) )                 AS M_SettlementBackCount
                    ,SUM( IFNULL( M_SettlementBackAmount, 0 ) )                AS M_SettlementBackAmount
                    ,SUM( IFNULL( M_OemTransferCount, 0 ) )                    AS M_OemTransferCount
                    ,SUM( IFNULL( M_OemTransferAmount, 0 ) )                   AS M_OemTransferAmount
                    ,SUM( IFNULL( M_ReClaimFeeCount, 0 ) )                     AS M_ReClaimFeeCount
                    ,SUM( IFNULL( M_ReClaimFeeAmount, 0 ) )                    AS M_ReClaimFeeAmount
                    ,SUM( IFNULL( M_DamageCount, 0 ) )                         AS M_DamageCount
                    ,SUM( IFNULL( M_DamageAmount, 0 ) )                        AS M_DamageAmount
                    ,SUM( IFNULL( M_ReceiptCount, 0 ) )                        AS M_ReceiptCount
                    ,SUM( IFNULL( M_ReceiptAmount, 0 ) )                       AS M_ReceiptAmount
                    ,SUM( IFNULL( M_RepayCount, 0 ) )                          AS M_RepayCount
                    ,SUM( IFNULL( M_RepayAmount, 0 ) )                         AS M_RepayAmount
                    ,SUM( IFNULL( M_BadDebtCount, 0 ) )                        AS M_BadDebtCount
                    ,SUM( IFNULL( M_BadDebtAmount, 0 ) )                       AS M_BadDebtAmount
                    ,SUM( IFNULL( M_OtherPaymentCount, 0 ) )                   AS M_OtherPaymentCount
                    ,SUM( IFNULL( M_OtherPaymentAmount, 0 ) )                  AS M_OtherPaymentAmount
                    ,SUM( IFNULL( M_AccountsReceivableBalance, 0 ) )           AS M_AccountsReceivableBalance
                    ,SUM( IFNULL( M_SuspensePaymentsAmount, 0 ) )              AS M_SuspensePaymentsAmount
                    ,SUM( IFNULL( M_AccountsReceivableBalanceDiff, 0 ) )       AS M_AccountsReceivableBalanceDiff
                    ,SUM( IFNULL( M_SettlementFee, 0 ) )                       AS M_SettlementFee
                    ,SUM( IFNULL( M_SettlementFeeTax, 0 ) )                    AS M_SettlementFeeTax
                    ,SUM( IFNULL( M_ClaimFee, 0 ) )                            AS M_ClaimFee
                    ,SUM( IFNULL( M_ClaimFeeTax, 0 ) )                         AS M_ClaimFeeTax
                    ,SUM( IFNULL( M_MonthlyFee, 0 ) )                          AS M_MonthlyFee
                    ,SUM( IFNULL( M_MonthlyFeeTax, 0 ) )                       AS M_MonthlyFeeTax
                    ,SUM( IFNULL( M_OemIncludeMonthlyFee, 0 ) )                AS M_OemIncludeMonthlyFee
                    ,SUM( IFNULL( M_OemIncludeMonthlyFeeTax, 0 ) )             AS M_OemIncludeMonthlyFeeTax
                    ,SUM( IFNULL( M_OemApiMonthlyFee, 0 ) )                    AS M_OemApiMonthlyFee
                    ,SUM( IFNULL( M_OemApiMonthlyFeeTax, 0 ) )                 AS M_OemApiMonthlyFeeTax
                    ,SUM( IFNULL( M_OemCreditNoticeMonthlyFee, 0 ) )           AS M_OemCreditNoticeMonthlyFee
                    ,SUM( IFNULL( M_OemCreditNoticeMonthlyFeeTax, 0 ) )        AS M_OemCreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( M_OemNCreditNoticeMonthlyFee, 0 ) )          AS M_OemNCreditNoticeMonthlyFee
                    ,SUM( IFNULL( M_OemNCreditNoticeMonthlyFeeTax, 0 ) )       AS M_OemNCreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( M_OemReserveMonthlyFee, 0 ) )                AS M_OemReserveMonthlyFee
                    ,SUM( IFNULL( M_OemReserveMonthlyFeeTax, 0 ) )             AS M_OemReserveMonthlyFeeTax
                    ,SUM( IFNULL( M_AddClaimFee, 0 ) )                         AS M_AddClaimFee
                    ,SUM( IFNULL( M_AddClaimFeeTax, 0 ) )                      AS M_AddClaimFeeTax
                    ,SUM( IFNULL( M_DamageInterestAmount, 0 ) )                AS M_DamageInterestAmount
                    ,SUM( IFNULL( M_CanSettlementFee, 0 ) )                    AS M_CanSettlementFee
                    ,SUM( IFNULL( M_CanSettlementFeeTax, 0 ) )                 AS M_CanSettlementFeeTax
                    ,SUM( IFNULL( M_CanClaimFee, 0 ) )                         AS M_CanClaimFee
                    ,SUM( IFNULL( M_CanClaimFeeTax, 0 ) )                      AS M_CanClaimFeeTax
                    ,SUM( IFNULL( M_SettlementFeeOther, 0 ) )                  AS M_SettlementFeeOther
                    ,SUM( IFNULL( M_SettlementFeeTaxOther, 0 ) )               AS M_SettlementFeeTaxOther
                    ,SUM( IFNULL( M_ClaimFeeOther, 0 ) )                       AS M_ClaimFeeOther
                    ,SUM( IFNULL( M_ClaimFeeTaxOther, 0 ) )                    AS M_ClaimFeeTaxOther
                    ,SUM( IFNULL( M_MonthlyFeeOther, 0 ) )                     AS M_MonthlyFeeOther
                    ,SUM( IFNULL( M_MonthlyFeeTaxOther, 0 ) )                  AS M_MonthlyFeeTaxOther
                    ,SUM( IFNULL( M_OemIncludeMonthlyFeeOther, 0 ) )           AS M_OemIncludeMonthlyFeeOther
                    ,SUM( IFNULL( M_OemIncludeMonthlyFeeTaxOther, 0 ) )        AS M_OemIncludeMonthlyFeeTaxOther
                    ,SUM( IFNULL( M_OemApiMonthlyFeeOther, 0 ) )               AS M_OemApiMonthlyFeeOther
                    ,SUM( IFNULL( M_OemApiMonthlyFeeTaxOther, 0 ) )            AS M_OemApiMonthlyFeeTaxOther
                    ,SUM( IFNULL( M_OemCreditNoticeMonthlyFeeOther, 0 ) )      AS M_OemCreditNoticeMonthlyFeeOther
                    ,SUM( IFNULL( M_OemCreditNoticeMonthlyFeeTaxOther, 0 ) )   AS M_OemCreditNoticeMonthlyFeeTaxOther
                    ,SUM( IFNULL( M_OemNCreditNoticeMonthlyFeeOther, 0 ) )     AS M_OemNCreditNoticeMonthlyFeeOther
                    ,SUM( IFNULL( M_OemNCreditNoticeMonthlyFeeTaxOther, 0 ) )  AS M_OemNCreditNoticeMonthlyFeeTaxOther
                    ,SUM( IFNULL( M_OemReserveMonthlyFeeOther, 0 ) )           AS M_OemReserveMonthlyFeeOther
                    ,SUM( IFNULL( M_OemReserveMonthlyFeeTaxOther, 0 ) )        AS M_OemReserveMonthlyFeeTaxOther
                    ,SUM( IFNULL( M_AddClaimFeeOther, 0 ) )                    AS M_AddClaimFeeOther
                    ,SUM( IFNULL( M_AddClaimFeeTaxOther, 0 ) )                 AS M_AddClaimFeeTaxOther
                    ,SUM( IFNULL( M_DamageInterestAmountOther, 0 ) )           AS M_DamageInterestAmountOther
                FROM  AW_Oem_DailyStatisticsTable
                GROUP BY OemId, EnterpriseId ) AS aw_dst
    LEFT JOIN( SELECT
                     OemId                                                     AS AT_DSD_OemId
                    ,EnterpriseId                                              AS AT_DSD_EnterpriseId
                    ,SUM( IFNULL( OemSettlementFee, 0 ) )                      AS OemSettlementFee
                    ,SUM( IFNULL( OemSettlementFeeTax, 0 ) )                   AS OemSettlementFeeTax
                    ,SUM( IFNULL( OemClaimFee, 0 ) )                           AS OemClaimFee
                    ,SUM( IFNULL( OemClaimFeeTax, 0 ) )                        AS OemClaimFeeTax
                    ,SUM( IFNULL( OemMonthlyFee, 0 ) )                         AS OemMonthlyFee
                    ,SUM( IFNULL( OemMonthlyFeeTax, 0 ) )                      AS OemMonthlyFeeTax
                    ,SUM( IFNULL( OemIncludeMonthlyFee, 0 ) )                  AS OemIncludeMonthlyFee
                    ,SUM( IFNULL( OemIncludeMonthlyFeeTax, 0 ) )               AS OemIncludeMonthlyFeeTax
                    ,SUM( IFNULL( OemApiMonthlyFee, 0 ) )                      AS OemApiMonthlyFee
                    ,SUM( IFNULL( OemApiMonthlyFeeTax, 0 ) )                   AS OemApiMonthlyFeeTax
                    ,SUM( IFNULL( OemCreditNoticeMonthlyFee, 0 ) )             AS OemCreditNoticeMonthlyFee
                    ,SUM( IFNULL( OemCreditNoticeMonthlyFeeTax, 0 ) )          AS OemCreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( OemNCreditNoticeMonthlyFee, 0 ) )            AS OemNCreditNoticeMonthlyFee
                    ,SUM( IFNULL( OemNCreditNoticeMonthlyFeeTax, 0 ) )         AS OemNCreditNoticeMonthlyFeeTax
                    ,SUM( IFNULL( OemReserveMonthlyFee, 0 ) )                  AS OemReserveMonthlyFee
                    ,SUM( IFNULL( OemReserveMonthlyFeeTax, 0 ) )               AS OemReserveMonthlyFeeTax
                FROM  AT_Daily_SalesDetails
                WHERE ProcessingDate = v_BusinessDate
                AND   AccountDate    = DATE_ADD( v_AccountingMonth, INTERVAL 1 MONTH )
                AND   SalesDefiniteConditions in ('着荷確認日', '入金日', '役務提供日', 'キャンセル日', 'キャンセル確定日', '月額固定費')
                AND      ValidFlg    = 1
                AND      OemId       <> 0
                GROUP BY OemId, EnterpriseId ) AS at_dsd
    ON  at_dsd.AT_DSD_OemId = aw_dst.OemId
    AND at_dsd.AT_DSD_EnterpriseId = aw_dst.EnterpriseId
    LEFT JOIN( SELECT
                     OemId                                                  AS AT_RFS_OemId
                    ,EnterpriseId                                           AS AT_RFS_EnterpriseId
                    ,SUM( IFNULL( Clm_L_ClaimFee, 0 ) )                     AS Clm_L_ClaimFee
                    ,SUM( IFNULL( Clm_L_ClaimFeeTax, 0 ) )                  AS Clm_L_ClaimFeeTax
                    ,SUM( IFNULL( Clm_L_DamageInterestAmount, 0 ) )         AS Clm_L_DamageInterestAmount
                FROM AT_ReissueFeeSpecification
                WHERE ProcessingDate = v_BusinessDate
                AND   AccountDate    = DATE_ADD( v_AccountingMonth, INTERVAL 1 MONTH )
                AND   ValidFlg       = 1
                AND   OemId          <> 0
                -- 債権移管されていないもの（債権移管日が未来日含む）が抽出対象 ↓↓↓
                AND   (OemTransferDate IS NULL OR (OemTransferDate IS NOT NULL AND OemTransferDate >= v_BusinessDate))
                GROUP BY OemId, EnterpriseId ) AS at_rfs
    ON  at_rfs.AT_RFS_OemId = aw_dst.OemId
    AND at_rfs.AT_RFS_EnterpriseId = aw_dst.EnterpriseId
    INNER JOIN  M_Code c1
    ON      c1.CodeId   = 160
    AND     c1.KeyCode  = IFNULL( aw_dst.OemId, 0 )
    AND     c1.Class1   <> 0
    LEFT JOIN  T_Enterprise ent
    ON  ent.EnterpriseId           = aw_dst.EnterpriseId;

END
$$

DELIMITER ;

