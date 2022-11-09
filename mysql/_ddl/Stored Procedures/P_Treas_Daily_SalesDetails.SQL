DROP PROCEDURE IF EXISTS P_Treas_Daily_SalesDetails;

DELIMITER $$

CREATE PROCEDURE P_Treas_Daily_SalesDetails( IN pi_user_id INT )
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名       ：  P_Treas_Daily_SalesDetails
 *
 * 概要             ：  日次売上明細
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
    DECLARE v_BusinessDate                          DATE;                           -- 業務日付
    DECLARE v_AccountingMonth                       DATE;                           -- 会計月

    -- ------------------------------
    -- 変数初期化
    -- ------------------------------
    SET v_BusinessDate = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'BusinessDate');       -- 業務日付
    SET v_AccountingMonth = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'AccountingMonth'); -- 会計月

    -- ------------------------------
    -- 日次売上明細の直営売上作成
    -- ------------------------------
    -- ① 直営(SMBC含む)加盟店の立替･売上管理のﾃﾞｰﾀより売上ﾃﾞｰﾀを出力する
    INSERT INTO AT_Daily_SalesDetails(
        DailyMonthlyFlg
       ,ProcessingDate
       ,AccountDate
       ,OemId
       ,OemNameKj
       ,EnterpriseId
       ,EnterpriseNameKj
       ,OrderSeq
       ,OrderId
       ,OutOfAmends
       ,SalesDefiniteConditions
       ,SalesDefiniteDate
       ,FixedDate
       ,ExecScheduleDate
       ,JournalNumber
       ,ManCustId
       ,ManCusNameKj
       ,UseAmountTotal
       ,SettlementFeeRate
       ,SettlementFee
       ,ClaimFee
       ,ClaimFeeTax
       ,MonthlyFee
       ,MonthlyFeeTax
       ,IncludeMonthlyFee
       ,IncludeMonthlyFeeTax
       ,ApiMonthlyFee
       ,ApiMonthlyFeeTax
       ,CreditNoticeMonthlyFee
       ,CreditNoticeMonthlyFeeTax
       ,NCreditNoticeMonthlyFee
       ,NCreditNoticeMonthlyFeeTax
       ,ReserveMonthlyFee
       ,ReserveMonthlyFeeTax
       ,TotalSales
       ,OemSettlementFeeRate
       ,OemSettlementFee
       ,OemSettlementFeeTax
       ,OemClaimFee
       ,OemClaimFeeTax
       ,OemMonthlyFee
       ,OemMonthlyFeeTax
       ,OemIncludeMonthlyFee
       ,OemIncludeMonthlyFeeTax
       ,OemApiMonthlyFee
       ,OemApiMonthlyFeeTax
       ,OemCreditNoticeMonthlyFee
       ,OemCreditNoticeMonthlyFeeTax
       ,OemNCreditNoticeMonthlyFee
       ,OemNCreditNoticeMonthlyFeeTax
       ,OemReserveMonthlyFee
       ,OemReserveMonthlyFeeTax
       ,OemTotalSales
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        2                                                      -- 日次･月次区分
       ,v_BusinessDate                                         -- 処理日付
       ,CASE
            WHEN EXTRACT(YEAR_MONTH FROM DATE(apas.ATUriDay)) < EXTRACT(YEAR_MONTH FROM v_AccountingMonth) THEN v_AccountingMonth
            ELSE DATE_FORMAT(DATE(apas.ATUriDay),'%Y-%m-01')
        END                                                    -- 会計月
       ,mc1.Class3                                             -- OEMID
       ,mc1.Class2                                             -- OEM先名
       ,o.EnterpriseId                                         -- 加盟店ID
       ,e.EnterpriseNameKj                                     -- 加盟店名
       ,o.OrderSeq                                             -- 注文Seq
       ,o.OrderId                                              -- 注文ID (旧取引ID)
       ,mc2.Class1                                             -- 補償有無
       ,F_GetSalesDefiniteConditions(o.OrderSeq)               -- 売上確定条件
       ,DATE(apas.ATUriDay)                                    -- 売上確定日付
       ,pc.FixedDate                                           -- 立替締め日
       ,pc.ExecScheduleDate                                    -- 立替予定日
       ,os.Deli_JournalNumber                                  -- 伝票番号
       ,mc.ManCustId                                           -- 管理顧客番号
       ,mc.NameKj                                              -- 顧客名
       ,pas.UseAmount                                          -- 商品等代金合計
       ,pas.AppSettlementFeeRate                               -- 決済手数料率
       ,pas.SettlementFee                                      -- 決済手数料
       -- ありえないとは思うけど念のため対応･･･請求手数料がﾏｲﾅｽの場合にはﾌﾟﾗｽ変換して計算 → ﾏｲﾅｽへ戻す。
       ,CASE
            WHEN pas.ClaimFee < 0 THEN ((pas.ClaimFee * -1) - FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
            ELSE pas.ClaimFee - FLOOR(IFNULL(pas.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
        END                                                    -- 請求手数料
       ,CASE
            WHEN pas.ClaimFee < 0 THEN (FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
            ELSE FLOOR(IFNULL(pas.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
        END                                                    -- 請求手数料消費税
       ,NULL                                                   -- 月額固定費
       ,NULL                                                   -- 月額固定費消費税
       ,NULL                                                   -- 同梱月額固定費
       ,NULL                                                   -- 同梱月額固定費費税
       ,NULL                                                   -- API月額固定費
       ,NULL                                                   -- API月額固定費費税
       ,NULL                                                   -- 与信結果通知ｻｰﾋﾞｽ月額固定費
       ,NULL                                                   -- 与信結果通知ｻｰﾋﾞｽ月額固定費費税
       ,NULL                                                   -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,NULL                                                   -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費費税
       ,NULL                                                   -- 月額固定費予備
       ,NULL                                                   -- 月額固定費予備費税
       -- ありえないとは思うけど念のため対応･･･請求手数料がﾏｲﾅｽの場合にはﾌﾟﾗｽ変換して計算 → ﾏｲﾅｽへ戻す。
       ,CASE WHEN pas.ClaimFee < 0
            THEN IFNULL(pas.SettlementFee, 0) +
                 (IFNULL(((pas.ClaimFee * -1) - FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))), 0)) * -1
            ELSE IFNULL(pas.SettlementFee, 0) + IFNULL(pas.ClaimFee - FLOOR(IFNULL(pas.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)
        END                                                    -- 売上合計
       ,NULL                                                   -- OEM決済手数料率
       ,NULL                                                   -- OEM決済手数料
       ,NULL                                                   -- OEM決済手数料消費税
       ,NULL                                                   -- OEM請求手数料
       ,NULL                                                   -- OEM請求手数料消費税
       ,NULL                                                   -- OEM月額固定費
       ,NULL                                                   -- OEM月額固定費消費税
       ,NULL                                                   -- OEM同梱月額固定費
       ,NULL                                                   -- OEM同梱月額固定費消費税
       ,NULL                                                   -- OEMAPI月額固定費
       ,NULL                                                   -- OEMAPI月額固定費消費税
       ,NULL                                                   -- OEM与信結果通知ｻｰﾋﾞｽ月額固定費
       ,NULL                                                   -- OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
       ,NULL                                                   -- OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,NULL                                                   -- OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
       ,NULL                                                   -- OEM月額固定費予備
       ,NULL                                                   -- OEM月額固定費予備消費税
       ,NULL                                                   -- OEM売上合計
       ,NOW()                                                  -- 登録日時
       ,pi_user_id                                             -- 登録者
       ,NOW()                                                  -- 更新日時
       ,pi_user_id                                             -- 更新者
       ,1                                                      -- 有効ﾌﾗｸﾞ
    FROM  T_PayingAndSales pas
          INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
          INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
          INNER JOIN M_Code mc1 ON (mc1.CodeId = 160 AND mc1.KeyCode = IFNULL(o.OemId, 0))
          INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
          INNER JOIN M_Code mc2 ON (mc2.CodeId = 159 AND mc2.KeyCode = IFNULL(o.OutOfAmends, 0))
          INNER JOIN T_OrderSummary os ON (os.OrderSeq = pas.OrderSeq)
          INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
          INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
          INNER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
          LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
          LEFT OUTER JOIN T_Cancel can ON (can.OrderSeq = o.OrderSeq)
          LEFT OUTER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq)
          LEFT OUTER JOIN T_PayingControl pc ON (pc.Seq = pas.PayingControlSeq)
    WHERE pas.ClearConditionForCharge   = 1
    AND   apas.ATUriDay                <= DATE_FORMAT(v_BusinessDate, '%Y%m%d')
    AND   apas.DailySummaryFlg          = 0
    AND   pas.ValidFlg                  = 1
    AND   mc1.Class1                    = 0
    AND   (can.ApprovalDate IS NULL OR DATE(apas.ATUriDay) <> DATE(can.ApprovalDate))        -- ｷｬﾝｾﾙ承認日がNULL もしくは 立替ｸﾘｱ日 <> ｷｬﾝｾﾙ承認日 のものが対象
    ;

    -- ------------------------------
    -- 日次売上明細のOEM売上作成
    -- ------------------------------
    -- ② OEM加盟店の立替･売上管理より売上ﾃﾞｰﾀを出力する
    INSERT INTO AT_Daily_SalesDetails(
        DailyMonthlyFlg
       ,ProcessingDate
       ,AccountDate
       ,OemId
       ,OemNameKj
       ,EnterpriseId
       ,EnterpriseNameKj
       ,OrderSeq
       ,OrderId
       ,OutOfAmends
       ,SalesDefiniteConditions
       ,SalesDefiniteDate
       ,FixedDate
       ,ExecScheduleDate
       ,JournalNumber
       ,ManCustId
       ,ManCusNameKj
       ,UseAmountTotal
       ,SettlementFeeRate
       ,SettlementFee
       ,ClaimFee
       ,ClaimFeeTax
       ,MonthlyFee
       ,MonthlyFeeTax
       ,IncludeMonthlyFee
       ,IncludeMonthlyFeeTax
       ,ApiMonthlyFee
       ,ApiMonthlyFeeTax
       ,CreditNoticeMonthlyFee
       ,CreditNoticeMonthlyFeeTax
       ,NCreditNoticeMonthlyFee
       ,NCreditNoticeMonthlyFeeTax
       ,ReserveMonthlyFee
       ,ReserveMonthlyFeeTax
       ,TotalSales
       ,OemSettlementFeeRate
       ,OemSettlementFee
       ,OemSettlementFeeTax
       ,OemClaimFee
       ,OemClaimFeeTax
       ,OemMonthlyFee
       ,OemMonthlyFeeTax
       ,OemIncludeMonthlyFee
       ,OemIncludeMonthlyFeeTax
       ,OemApiMonthlyFee
       ,OemApiMonthlyFeeTax
       ,OemCreditNoticeMonthlyFee
       ,OemCreditNoticeMonthlyFeeTax
       ,OemNCreditNoticeMonthlyFee
       ,OemNCreditNoticeMonthlyFeeTax
       ,OemReserveMonthlyFee
       ,OemReserveMonthlyFeeTax
       ,OemTotalSales
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        2                                                      -- 日次･月次区分
       ,v_BusinessDate                                         -- 処理日付
       ,CASE
            WHEN EXTRACT(YEAR_MONTH FROM DATE(apas.ATUriDay)) < EXTRACT(YEAR_MONTH FROM v_AccountingMonth) THEN v_AccountingMonth
            ELSE DATE_FORMAT(DATE(apas.ATUriDay),'%Y-%m-01')
        END                                                    -- 会計月
       ,mc1.Class3                                             -- OEMID
       ,mc1.Class2                                             -- OEM先名
       ,o.EnterpriseId                                         -- 加盟店ID
       ,e.EnterpriseNameKj                                     -- 加盟店名
       ,pas.OrderSeq                                           -- 注文Seq
       ,o.OrderId                                              -- 注文ID (旧取引ID)
       ,mc2.Class1                                             -- 補償有無
       ,F_GetSalesDefiniteConditions(o.OrderSeq)               -- 売上確定条件
       ,DATE(apas.ATUriDay)                                    -- 売上確定日付
       ,pc.FixedDate                                           -- 立替締め日
       ,pc.ExecScheduleDate                                    -- 立替予定日
       ,os.Deli_JournalNumber                                  -- 伝票番号
       ,mc.ManCustId                                           -- 管理顧客番号
       ,mc.NameKj                                              -- 顧客名
       ,pas.UseAmount                                          -- 商品等代金合計
       ,NULL                                                   -- 決済手数料率
       ,NULL                                                   -- 決済手数料
       ,NULL                                                   -- 請求手数料
       ,NULL                                                   -- 請求手数料消費税
       ,NULL                                                   -- 月額固定費
       ,NULL                                                   -- 月額固定費消費税
       ,NULL                                                   -- 同梱月額固定費
       ,NULL                                                   -- 同梱月額固定費費税
       ,NULL                                                   -- API月額固定費
       ,NULL                                                   -- API月額固定費費税
       ,NULL                                                   -- 与信結果通知ｻｰﾋﾞｽ月額固定費
       ,NULL                                                   -- 与信結果通知ｻｰﾋﾞｽ月額固定費費税
       ,NULL                                                   -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,NULL                                                   -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費費税
       ,NULL                                                   -- 月額固定費予備
       ,NULL                                                   -- 月額固定費予備費税
       ,NULL                                                   -- 売上合計
       ,TRUNCATE( osf.AppSettlementFeeRate * 100 / (100 + F_GetTaxRate(DATE(apas.ATUriDay))) , 5)    -- OEM決済手数料率
       -- ありえないとは思うけど念のため対応･･･請求手数料等がﾏｲﾅｽの場合にはﾌﾟﾗｽ変換して計算 → ﾏｲﾅｽへ戻す。
       ,CASE WHEN IFNULL(osf.SettlementFee, 0) < 0
            THEN ((osf.SettlementFee * -1) - FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
            ELSE osf.SettlementFee - FLOOR(IFNULL(osf.SettlementFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
        END                                                    -- OEM決済手数料
       ,CASE WHEN IFNULL(osf.SettlementFee, 0) < 0
            THEN (FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
            ELSE FLOOR(IFNULL(osf.SettlementFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
        END                                                    -- OEM決済手数料消費税
       ,CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0
            THEN ((ocf.ClaimFee * -1) - FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
            ELSE ocf.ClaimFee - FLOOR(IFNULL(ocf.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
        END                                                    -- OEM請求手数料
       ,CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0
            THEN (FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
            ELSE FLOOR(IFNULL(ocf.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))
        END                                                    -- OEM請求手数料消費税
       ,NULL                                                   -- OEM月額固定費
       ,NULL                                                   -- OEM月額固定費消費税
       ,NULL                                                   -- OEM同梱月額固定費
       ,NULL                                                   -- OEM同梱月額固定費消費税
       ,NULL                                                   -- OEMAPI月額固定費
       ,NULL                                                   -- OEMAPI月額固定費消費税
       ,NULL                                                   -- OEM与信結果通知ｻｰﾋﾞｽ月額固定費
       ,NULL                                                   -- OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
       ,NULL                                                   -- OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,NULL                                                   -- OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
       ,NULL                                                   -- OEM月額固定費予備
       ,NULL                                                   -- OEM月額固定費予備消費税
       -- ありえないとは思うけど念のため対応･･･請求手数料等がﾏｲﾅｽの場合にはﾌﾟﾗｽ変換して計算 → ﾏｲﾅｽへ戻す。
       ,(CASE WHEN IFNULL(osf.SettlementFee, 0) < 0
            THEN (IFNULL((osf.SettlementFee * -1) - FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)) * -1
            ELSE IFNULL(osf.SettlementFee - FLOOR(IFNULL(osf.SettlementFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)
        END
        +
        CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0
            THEN (IFNULL((ocf.ClaimFee * -1) - FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)) * -1
            ELSE IFNULL(ocf.ClaimFee - FLOOR(IFNULL(ocf.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)
        END)                                                   -- OEM売上合計
       ,NOW()                                                  -- 登録日時
       ,pi_user_id                                             -- 登録者
       ,NOW()                                                  -- 更新日時
       ,pi_user_id                                             -- 更新者
       ,1                                                      -- 有効ﾌﾗｸﾞ (0:無効 1:有効)
    FROM  T_PayingAndSales pas
          INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
          INNER JOIN T_Order o ON (o.OrderSeq = pas.OrderSeq)
          INNER JOIN M_Code mc1 ON (mc1.CodeId = 160 AND mc1.KeyCode = IFNULL(o.OemId, 0))
          INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
          INNER JOIN M_Code mc2 ON (mc2.CodeId = 159 AND mc2.KeyCode = IFNULL(o.OutOfAmends, 0))
          INNER JOIN M_Code mc3 ON (mc3.CodeId = 158 AND mc3.KeyCode = 5)
          INNER JOIN T_OrderSummary os ON (os.OrderSeq = pas.OrderSeq)
          INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
          INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
          INNER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
          LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
          LEFT OUTER JOIN T_OemSettlementFee osf ON (osf.OrderSeq = o.OrderSeq)
          LEFT OUTER JOIN T_OemClaimFee ocf ON (ocf.OrderSeq = o.OrderSeq)
          LEFT OUTER JOIN T_Cancel can ON (can.OrderSeq = o.OrderSeq)
          LEFT OUTER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq)
          LEFT OUTER JOIN T_PayingControl pc ON (pc.Seq = pas.PayingControlSeq)
    WHERE pas.ClearConditionForCharge   = 1
    AND   apas.ATUriDay                <= DATE_FORMAT(v_BusinessDate, '%Y%m%d')
    AND   apas.DailySummaryFlg          = 0
    AND   pas.ValidFlg                  = 1
    AND   mc1.Class1                   <> 0
    AND   (can.ApprovalDate IS NULL OR DATE(apas.ATUriDay) <> DATE(can.ApprovalDate))        -- ｷｬﾝｾﾙ承認日がNULL もしくは 立替ｸﾘｱ日 <> ｷｬﾝｾﾙ承認日 のものが対象
    ;

    -- ------------------------------
    -- 日次売上明細の直営ｷｬﾝｾﾙ作成
    -- ------------------------------
    -- ③ 直営加盟店のｷｬﾝｾﾙ管理(及び立替･売上管理)よりｷｬﾝｾﾙ分の売上ﾃﾞｰﾀを出力する
    INSERT INTO AT_Daily_SalesDetails(
        DailyMonthlyFlg
       ,ProcessingDate
       ,AccountDate
       ,OemId
       ,OemNameKj
       ,EnterpriseId
       ,EnterpriseNameKj
       ,OrderSeq
       ,OrderId
       ,OutOfAmends
       ,SalesDefiniteConditions
       ,SalesDefiniteDate
       ,FixedDate
       ,ExecScheduleDate
       ,JournalNumber
       ,ManCustId
       ,ManCusNameKj
       ,UseAmountTotal
       ,SettlementFeeRate
       ,SettlementFee
       ,ClaimFee
       ,ClaimFeeTax
       ,MonthlyFee
       ,MonthlyFeeTax
       ,IncludeMonthlyFee
       ,IncludeMonthlyFeeTax
       ,ApiMonthlyFee
       ,ApiMonthlyFeeTax
       ,CreditNoticeMonthlyFee
       ,CreditNoticeMonthlyFeeTax
       ,NCreditNoticeMonthlyFee
       ,NCreditNoticeMonthlyFeeTax
       ,ReserveMonthlyFee
       ,ReserveMonthlyFeeTax
       ,TotalSales
       ,OemSettlementFeeRate
       ,OemSettlementFee
       ,OemSettlementFeeTax
       ,OemClaimFee
       ,OemClaimFeeTax
       ,OemMonthlyFee
       ,OemMonthlyFeeTax
       ,OemIncludeMonthlyFee
       ,OemIncludeMonthlyFeeTax
       ,OemApiMonthlyFee
       ,OemApiMonthlyFeeTax
       ,OemCreditNoticeMonthlyFee
       ,OemCreditNoticeMonthlyFeeTax
       ,OemNCreditNoticeMonthlyFee
       ,OemNCreditNoticeMonthlyFeeTax
       ,OemReserveMonthlyFee
       ,OemReserveMonthlyFeeTax
       ,OemTotalSales
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        2                                                      -- 日次･月次区分
       ,v_BusinessDate                                         -- 処理日付
       ,CASE
            WHEN EXTRACT(YEAR_MONTH FROM can.ApprovalDate) < EXTRACT(YEAR_MONTH FROM v_AccountingMonth)
                THEN v_AccountingMonth
            ELSE DATE_FORMAT(can.ApprovalDate,'%Y-%m-01')
        END                                                    -- 会計月
       ,mc1.Class3                                             -- OEMID
       ,mc1.Class2                                             -- OEM先名
       ,o.EnterpriseId                                         -- 加盟店ID
       ,e.EnterpriseNameKj                                     -- 加盟店名
       ,can.OrderSeq                                           -- 注文Seq
       ,o.OrderId                                              -- 注文ID (旧取引ID)
       ,mc2.Class1                                             -- 補償有無
       ,mc3.Class1                                             -- 売上確定条件
       ,can.ApprovalDate                                       -- 売上確定日付
       ,pc.FixedDate                                           -- 立替締め日
       ,pc.ExecScheduleDate                                    -- 立替予定日
       ,os.Deli_JournalNumber                                  -- 伝票番号
       ,mc.ManCustId                                           -- 管理顧客番号
       ,mc.NameKj                                              -- 顧客名
       ,pas.UseAmount * -1                                     -- 商品等代金合計
       ,pas.AppSettlementFeeRate                               -- 決済手数料率
       ,IFNULL(pas.SettlementFee, 0) * -1                      -- 決済手数料
       -- ありえないとは思うけど念のため対応･･･請求手数料がﾏｲﾅｽの場合にはﾌﾟﾗｽ変換して計算 → ﾏｲﾅｽへ戻す。
       ,CASE WHEN pas.ClaimFee < 0
            THEN (((pas.ClaimFee * -1) - FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1) * -1
            ELSE (pas.ClaimFee - FLOOR(IFNULL(pas.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
        END                                                    -- 請求手数料
       ,CASE WHEN pas.ClaimFee < 0
            THEN (FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))) * -1) * -1
            ELSE FLOOR(IFNULL(pas.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))) * -1
        END                                                    -- 請求手数料消費税
       ,NULL                                                   -- 月額固定費
       ,NULL                                                   -- 月額固定費消費税
       ,NULL                                                   -- 同梱月額固定費
       ,NULL                                                   -- 同梱月額固定費費税
       ,NULL                                                   -- API月額固定費
       ,NULL                                                   -- API月額固定費費税
       ,NULL                                                   -- 与信結果通知ｻｰﾋﾞｽ月額固定費
       ,NULL                                                   -- 与信結果通知ｻｰﾋﾞｽ月額固定費費税
       ,NULL                                                   -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,NULL                                                   -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費費税
       ,NULL                                                   -- 月額固定費予備
       ,NULL                                                   -- 月額固定費予備費税
       ,(CASE WHEN pas.ClaimFee < 0
            THEN (IFNULL(pas.SettlementFee, 0) +
                  IFNULL((pas.ClaimFee * -1) - FLOOR((IFNULL(pas.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)) * -1
            ELSE IFNULL(pas.SettlementFee, 0) +
                 IFNULL(pas.ClaimFee - FLOOR(IFNULL(pas.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)
         END) * -1                                             -- 売上合計
       ,NULL                                                   -- OEM決済手数料率
       ,NULL                                                   -- OEM決済手数料
       ,NULL                                                   -- OEM決済手数料消費税
       ,NULL                                                   -- OEM請求手数料
       ,NULL                                                   -- OEM請求手数料消費税
       ,NULL                                                   -- OEM月額固定費
       ,NULL                                                   -- OEM月額固定費消費税
       ,NULL                                                   -- OEM同梱月額固定費
       ,NULL                                                   -- OEM同梱月額固定費消費税
       ,NULL                                                   -- OEMAPI月額固定費
       ,NULL                                                   -- OEMAPI月額固定費消費税
       ,NULL                                                   -- OEM与信結果通知ｻｰﾋﾞｽ月額固定費
       ,NULL                                                   -- OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
       ,NULL                                                   -- OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,NULL                                                   -- OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
       ,NULL                                                   -- OEM月額固定費予備
       ,NULL                                                   -- OEM月額固定費予備消費税
       ,NULL                                                   -- OEM売上合計
       ,NOW()                                                  -- 登録日時
       ,pi_user_id                                             -- 登録者
       ,NOW()                                                  -- 更新日時
       ,pi_user_id                                             -- 更新者
       ,1                                                      -- 有効ﾌﾗｸﾞ (0:無効 1:有効)
    FROM  T_Cancel can
          INNER JOIN T_Order o ON (o.OrderSeq = can.OrderSeq)
          INNER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
          INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
          INNER JOIN M_Code mc1 ON (mc1.CodeId = 160 AND mc1.KeyCode = IFNULL(o.OemId, 0))
          INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
          INNER JOIN M_Code mc2 ON (mc2.CodeId = 159 AND mc2.KeyCode = IFNULL(o.OutOfAmends, 0))
          INNER JOIN M_Code mc3 ON (mc3.CodeId = 158 AND mc3.KeyCode = 4)
          INNER JOIN T_OrderSummary os ON (os.OrderSeq = o.OrderSeq)
          INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
          INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
          INNER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
          LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
          LEFT OUTER JOIN T_PayingControl pc ON (pc.Seq = pas.PayingControlSeq)
    WHERE
          can.ApproveFlg                = 1
    AND   DATE_FORMAT( can.ApprovalDate, '%Y-%m-%d' )   <= v_BusinessDate
    AND   can.DailySummaryFlg           = 0
    AND   can.ValidFlg                  = 1
    AND   pas.ClearConditionForCharge   = 1
    AND   mc1.Class1                    = 0
    AND   DATE(apas.ATUriDay) <> DATE(can.ApprovalDate)      -- 立替ｸﾘｱ日 <> ｷｬﾝｾﾙ承認日 のものが対象
    ;

    -- ------------------------------
    -- 日次売上明細のOEMｷｬﾝｾﾙ作成
    -- ------------------------------
    -- ④ OEM加盟店のｷｬﾝｾﾙ管理(及び立替･売上管理)よりｷｬﾝｾﾙ分の売上ﾃﾞｰﾀを出力する
    INSERT INTO AT_Daily_SalesDetails(
        DailyMonthlyFlg
       ,ProcessingDate
       ,AccountDate
       ,OemId
       ,OemNameKj
       ,EnterpriseId
       ,EnterpriseNameKj
       ,OrderSeq
       ,OrderId
       ,OutOfAmends
       ,SalesDefiniteConditions
       ,SalesDefiniteDate
       ,FixedDate
       ,ExecScheduleDate
       ,JournalNumber
       ,ManCustId
       ,ManCusNameKj
       ,UseAmountTotal
       ,SettlementFeeRate
       ,SettlementFee
       ,ClaimFee
       ,ClaimFeeTax
       ,MonthlyFee
       ,MonthlyFeeTax
       ,IncludeMonthlyFee
       ,IncludeMonthlyFeeTax
       ,ApiMonthlyFee
       ,ApiMonthlyFeeTax
       ,CreditNoticeMonthlyFee
       ,CreditNoticeMonthlyFeeTax
       ,NCreditNoticeMonthlyFee
       ,NCreditNoticeMonthlyFeeTax
       ,ReserveMonthlyFee
       ,ReserveMonthlyFeeTax
       ,TotalSales
       ,OemSettlementFeeRate
       ,OemSettlementFee
       ,OemSettlementFeeTax
       ,OemClaimFee
       ,OemClaimFeeTax
       ,OemMonthlyFee
       ,OemMonthlyFeeTax
       ,OemIncludeMonthlyFee
       ,OemIncludeMonthlyFeeTax
       ,OemApiMonthlyFee
       ,OemApiMonthlyFeeTax
       ,OemCreditNoticeMonthlyFee
       ,OemCreditNoticeMonthlyFeeTax
       ,OemNCreditNoticeMonthlyFee
       ,OemNCreditNoticeMonthlyFeeTax
       ,OemReserveMonthlyFee
       ,OemReserveMonthlyFeeTax
       ,OemTotalSales
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        2                                                      -- 日次･月次区分
       ,v_BusinessDate                                         -- 処理日付
       ,CASE
            WHEN EXTRACT(YEAR_MONTH FROM can.ApprovalDate) < EXTRACT(YEAR_MONTH FROM v_AccountingMonth)
                THEN v_AccountingMonth
            ELSE DATE_FORMAT(can.ApprovalDate,'%Y-%m-01')
        END                                                    -- 会計月
       ,mc1.Class3                                             -- OEMID
       ,mc1.Class2                                             -- OEM先名
       ,o.EnterpriseId                                         -- 加盟店ID
       ,e.EnterpriseNameKj                                     -- 加盟店名
       ,can.OrderSeq                                           -- 注文Seq
       ,o.OrderId                                              -- 注文ID (旧取引ID)
       ,mc2.Class1                                             -- 補償有無
       ,mc3.Class1                                             -- 売上確定条件
       ,can.ApprovalDate                                       -- 売上確定日付
       ,pc.FixedDate                                           -- 立替締め日
       ,pc.ExecScheduleDate                                    -- 立替予定日
       ,os.Deli_JournalNumber                                  -- 伝票番号
       ,mc.ManCustId                                           -- 管理顧客番号
       ,mc.NameKj                                              -- 顧客名
       ,pas.UseAmount * -1                                     -- 商品等代金合計
       ,NULL                                                   -- 決済手数料率
       ,NULL                                                   -- 決済手数料
       ,NULL                                                   -- 請求手数料
       ,NULL                                                   -- 請求手数料消費税
       ,NULL                                                   -- 月額固定費
       ,NULL                                                   -- 月額固定費消費税
       ,NULL                                                   -- 同梱月額固定費
       ,NULL                                                   -- 同梱月額固定費費税
       ,NULL                                                   -- API月額固定費
       ,NULL                                                   -- API月額固定費費税
       ,NULL                                                   -- 与信結果通知ｻｰﾋﾞｽ月額固定費
       ,NULL                                                   -- 与信結果通知ｻｰﾋﾞｽ月額固定費費税
       ,NULL                                                   -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,NULL                                                   -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費費税
       ,NULL                                                   -- 月額固定費予備
       ,NULL                                                   -- 月額固定費予備費税
       ,NULL                                                   -- 売上合計
       ,TRUNCATE( osf.AppSettlementFeeRate * 100 / (100 + F_GetTaxRate(DATE(apas.ATUriDay))) , 5) -- OEM決済手数料率
       -- ありえないとは思うけど念のため対応･･･請求手数料がﾏｲﾅｽの場合にはﾌﾟﾗｽ変換して計算 → ﾏｲﾅｽへ戻す。
       ,CASE WHEN IFNULL(osf.SettlementFee, 0) < 0
            THEN (((osf.SettlementFee * -1) - FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1) * -1
            ELSE (osf.SettlementFee - FLOOR(IFNULL(osf.SettlementFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
        END                                                    -- OEM決済手数料
       ,CASE WHEN IFNULL(osf.SettlementFee, 0) < 0
            THEN (FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))) * -1) * -1
            ELSE FLOOR(IFNULL(osf.SettlementFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))) * -1
        END                                                    -- OEM決済手数料消費税
       ,CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0
            THEN (((ocf.ClaimFee * -1) - FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1) * -1
            ELSE (ocf.ClaimFee - FLOOR(IFNULL(ocf.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay))))) * -1
        END                                                    -- OEM請求手数料
       ,CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0
            THEN (FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))) * -1) * -1
            ELSE FLOOR(IFNULL(ocf.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))) * -1
        END                                                    -- OEM請求手数料消費税
       ,NULL                                                   -- OEM月額固定費
       ,NULL                                                   -- OEM月額固定費消費税
       ,NULL                                                   -- OEM同梱月額固定費
       ,NULL                                                   -- OEM同梱月額固定費消費税
       ,NULL                                                   -- OEMAPI月額固定費
       ,NULL                                                   -- OEMAPI月額固定費消費税
       ,NULL                                                   -- OEM与信結果通知ｻｰﾋﾞｽ月額固定費
       ,NULL                                                   -- OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
       ,NULL                                                   -- OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,NULL                                                   -- OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
       ,NULL                                                   -- OEM月額固定費予備
       ,NULL                                                   -- OEM月額固定費予備消費税
       ,(CASE WHEN IFNULL(osf.SettlementFee, 0) < 0
            THEN (IFNULL((osf.SettlementFee * -1) - FLOOR((IFNULL(osf.SettlementFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)) * -1
            ELSE IFNULL(osf.SettlementFee - FLOOR(IFNULL(osf.SettlementFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)
         END
         +
         CASE WHEN IFNULL(ocf.ClaimFee, 0) < 0
            THEN (IFNULL((ocf.ClaimFee * -1) - FLOOR((IFNULL(ocf.ClaimFee, 0) * -1) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)) * -1
            ELSE IFNULL(ocf.ClaimFee - FLOOR(IFNULL(ocf.ClaimFee, 0) * F_GetTaxRate(DATE(apas.ATUriDay)) / (100 + F_GetTaxRate(DATE(apas.ATUriDay)))), 0)
         END) * -1                                             -- OEM売上合計
       ,NOW()                                                  -- 登録日時
       ,pi_user_id                                             -- 登録者
       ,NOW()                                                  -- 更新日時
       ,pi_user_id                                             -- 更新者
       ,1                                                      -- 有効ﾌﾗｸﾞ (0:無効 1:有効)
    FROM  T_Cancel can
          INNER JOIN T_Order o ON (o.OrderSeq = can.OrderSeq)
          INNER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
          INNER JOIN AT_PayingAndSales apas ON (apas.Seq = pas.Seq)
          INNER JOIN M_Code mc1 ON (mc1.CodeId = 160 AND mc1.KeyCode = IFNULL(o.OemId, 0))
          INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
          INNER JOIN M_Code mc2 ON (mc2.CodeId = 159 AND mc2.KeyCode = IFNULL(o.OutOfAmends, 0))
          INNER JOIN M_Code mc3 ON (mc3.CodeId = 158 AND mc3.KeyCode = 4)
          INNER JOIN T_OrderSummary os ON (os.OrderSeq = o.OrderSeq)
          INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
          INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
          INNER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
          LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
          LEFT  JOIN T_OemSettlementFee osf ON (osf.OrderSeq = o.OrderSeq)
          LEFT  JOIN T_OemClaimFee ocf ON (ocf.OrderSeq = o.OrderSeq)
          LEFT OUTER JOIN T_PayingControl pc ON (pc.Seq = pas.PayingControlSeq)
    WHERE
          can.ApproveFlg                = 1
    AND   DATE_FORMAT( can.ApprovalDate, '%Y-%m-%d' )   <= v_BusinessDate
    AND   can.DailySummaryFlg           = 0
    AND   can.ValidFlg                  = 1
    AND   pas.ClearConditionForCharge   = 1
    AND   mc1.Class1                   <> 0
    AND   DATE(apas.ATUriDay) <> DATE(can.ApprovalDate)      -- 立替ｸﾘｱ日 <> ｷｬﾝｾﾙ承認日 のものが対象
    ;

    -- ------------------------------
    -- 日次売上明細の直営月額固定費作成
    -- ------------------------------
    -- ⑤ 直営加盟店の加盟店月締め情報より月額固定費の売上ﾃﾞｰﾀを作成する
    INSERT INTO AT_Daily_SalesDetails(
        DailyMonthlyFlg
       ,ProcessingDate
       ,AccountDate
       ,OemId
       ,OemNameKj
       ,EnterpriseId
       ,EnterpriseNameKj
       ,OrderSeq
       ,OrderId
       ,OutOfAmends
       ,SalesDefiniteConditions
       ,SalesDefiniteDate
       ,FixedDate
       ,ExecScheduleDate
       ,JournalNumber
       ,ManCustId
       ,ManCusNameKj
       ,UseAmountTotal
       ,SettlementFeeRate
       ,SettlementFee
       ,ClaimFee
       ,ClaimFeeTax
       ,MonthlyFee
       ,MonthlyFeeTax
       ,IncludeMonthlyFee
       ,IncludeMonthlyFeeTax
       ,ApiMonthlyFee
       ,ApiMonthlyFeeTax
       ,CreditNoticeMonthlyFee
       ,CreditNoticeMonthlyFeeTax
       ,NCreditNoticeMonthlyFee
       ,NCreditNoticeMonthlyFeeTax
       ,ReserveMonthlyFee
       ,ReserveMonthlyFeeTax
       ,TotalSales
       ,OemSettlementFeeRate
       ,OemSettlementFee
       ,OemSettlementFeeTax
       ,OemClaimFee
       ,OemClaimFeeTax
       ,OemMonthlyFee
       ,OemMonthlyFeeTax
       ,OemIncludeMonthlyFee
       ,OemIncludeMonthlyFeeTax
       ,OemApiMonthlyFee
       ,OemApiMonthlyFeeTax
       ,OemCreditNoticeMonthlyFee
       ,OemCreditNoticeMonthlyFeeTax
       ,OemNCreditNoticeMonthlyFee
       ,OemNCreditNoticeMonthlyFeeTax
       ,OemReserveMonthlyFee
       ,OemReserveMonthlyFeeTax
       ,OemTotalSales
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT DISTINCT
        2                                                      -- 日次･月次区分
       ,v_BusinessDate                                         -- 処理日付
       ,CASE
            WHEN EXTRACT(YEAR_MONTH FROM v_BusinessDate) < EXTRACT(YEAR_MONTH FROM v_AccountingMonth)
                THEN v_AccountingMonth
            ELSE DATE_FORMAT(v_BusinessDate,'%Y-%m-01')
        END                                                    -- 会計月
       ,mc1.Class3                                             -- OEMID
       ,mc1.Class2                                             -- OEM先名
       ,e.EnterpriseId                                         -- 加盟店ID
       ,e.EnterpriseNameKj                                     -- 加盟店名
       ,NULL                                                   -- 注文Seq
       ,NULL                                                   -- 注文ID (旧取引ID)
       ,NULL                                                   -- 補償有無
       ,mc2.Class1                                             -- 売上確定条件
       ,v_BusinessDate                                         -- 売上確定日付
       ,NULL                                                   -- 立替締め日
       ,NULL                                                   -- 立替予定日
       ,NULL                                                   -- 伝票番号
       ,NULL                                                   -- 管理顧客番号 
       ,NULL                                                   -- 顧客名
       ,NULL                                                   -- 商品等代金合計
       ,NULL                                                   -- 決済手数料率
       ,NULL                                                   -- 決済手数料
       ,NULL                                                   -- 請求手数料
       ,NULL                                                   -- 請求手数料消費税
       ,ec.MonthlyFee                                          -- 月額固定費
       ,ec.MonthlyFeeTax                                       -- 月額固定費消費税
       ,ec.IncludeMonthlyFee                                   -- 同梱月額固定費
       ,ec.IncludeMonthlyFeeTax                                -- 同梱月額固定費費税
       ,ec.ApiMonthlyFee                                       -- API月額固定費
       ,ec.ApiMonthlyFeeTax                                    -- API月額固定費費税
       ,ec.CreditNoticeMonthlyFee                              -- 与信結果通知ｻｰﾋﾞｽ月額固定費
       ,ec.CreditNoticeMonthlyFeeTax                           -- 与信結果通知ｻｰﾋﾞｽ月額固定費費税
       ,ec.NCreditNoticeMonthlyFee                             -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,ec.NCreditNoticeMonthlyFeeTax                          -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費費税
       ,ec.ReserveMonthlyFee                                   -- 月額固定費予備
       ,ec.ReserveMonthlyFeeTax                                -- 月額固定費予備費税
       ,ec.MonthlyFee + ec.IncludeMonthlyFee + ec.ApiMonthlyFee
       + ec.CreditNoticeMonthlyFee + ec.NCreditNoticeMonthlyFee
       + ec.ReserveMonthlyFee                                  -- 売上合計
       ,NULL                                                   -- OEM決済手数料率
       ,NULL                                                   -- OEM決済手数料
       ,NULL                                                   -- OEM決済手数料消費税
       ,NULL                                                   -- OEM請求手数料
       ,NULL                                                   -- OEM請求手数料消費税
       ,NULL                                                   -- OEM月額固定費
       ,NULL                                                   -- OEM月額固定費消費税
       ,NULL                                                   -- OEM同梱月額固定費
       ,NULL                                                   -- OEM同梱月額固定費消費税
       ,NULL                                                   -- OEMAPI月額固定費
       ,NULL                                                   -- OEMAPI月額固定費消費税
       ,NULL                                                   -- OEM与信結果通知ｻｰﾋﾞｽ月額固定費
       ,NULL                                                   -- OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
       ,NULL                                                   -- OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,NULL                                                   -- OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
       ,NULL                                                   -- OEM月額固定費予備
       ,NULL                                                   -- OEM月額固定費予備消費税
       ,NULL                                                   -- OEM売上合計
       ,NOW()                                                  -- 登録日時
       ,pi_user_id                                             -- 登録者
       ,NOW()                                                  -- 更新日時
       ,pi_user_id                                             -- 更新者
       ,1                                                      -- 有効ﾌﾗｸﾞ (0:無効 1:有効)
    FROM  AT_EnterpriseMonthlyClosingInfo ec
    INNER JOIN M_Code mc1
    ON    mc1.CodeId            = 160
    AND   mc1.KeyCode           = IFNULL(ec.OemId, 0)
    LEFT  JOIN M_Code mc2
    ON    mc2.CodeId            = 158
    AND   mc2.KeyCode           = 8
    INNER JOIN T_Enterprise e
    ON    e.EnterpriseId        = ec.EnterpriseId
    WHERE
          ec.ValidFlg                   = 1
    AND   ec.DailySummaryFlg            = 0
    AND   ec.ClosingMonthly             <= v_BusinessDate
    AND   ( ec.MonthlyFee + ec.IncludeMonthlyFee
          + ec.ApiMonthlyFee + ec.CreditNoticeMonthlyFee
          + ec.NCreditNoticeMonthlyFee + ec.ReserveMonthlyFee
          )                             > 0
    AND   mc1.Class1                    = 0
    ;

    -- ------------------------------
    -- 日次売上明細のOEM月額固定費作成
    -- ------------------------------
    -- ⑥ OEM加盟店の加盟店月締め情報よりOEM月額固定費の売上ﾃﾞｰﾀを作成する
    INSERT INTO AT_Daily_SalesDetails(
        DailyMonthlyFlg
       ,ProcessingDate
       ,AccountDate
       ,OemId
       ,OemNameKj
       ,EnterpriseId
       ,EnterpriseNameKj
       ,OrderSeq
       ,OrderId
       ,OutOfAmends
       ,SalesDefiniteConditions
       ,SalesDefiniteDate
       ,FixedDate
       ,ExecScheduleDate
       ,JournalNumber
       ,ManCustId
       ,ManCusNameKj
       ,UseAmountTotal
       ,SettlementFeeRate
       ,SettlementFee
       ,ClaimFee
       ,ClaimFeeTax
       ,MonthlyFee
       ,MonthlyFeeTax
       ,IncludeMonthlyFee
       ,IncludeMonthlyFeeTax
       ,ApiMonthlyFee
       ,ApiMonthlyFeeTax
       ,CreditNoticeMonthlyFee
       ,CreditNoticeMonthlyFeeTax
       ,NCreditNoticeMonthlyFee
       ,NCreditNoticeMonthlyFeeTax
       ,ReserveMonthlyFee
       ,ReserveMonthlyFeeTax
       ,TotalSales
       ,OemSettlementFeeRate
       ,OemSettlementFee
       ,OemSettlementFeeTax
       ,OemClaimFee
       ,OemClaimFeeTax
       ,OemMonthlyFee
       ,OemMonthlyFeeTax
       ,OemIncludeMonthlyFee
       ,OemIncludeMonthlyFeeTax
       ,OemApiMonthlyFee
       ,OemApiMonthlyFeeTax
       ,OemCreditNoticeMonthlyFee
       ,OemCreditNoticeMonthlyFeeTax
       ,OemNCreditNoticeMonthlyFee
       ,OemNCreditNoticeMonthlyFeeTax
       ,OemReserveMonthlyFee
       ,OemReserveMonthlyFeeTax
       ,OemTotalSales
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT DISTINCT
        2                                                      -- 日次･月次区分
       ,v_BusinessDate                                         -- 処理日付
       ,CASE
            WHEN EXTRACT(YEAR_MONTH FROM v_BusinessDate) < EXTRACT(YEAR_MONTH FROM v_AccountingMonth)
                THEN v_AccountingMonth
            ELSE DATE_FORMAT(v_BusinessDate,'%Y-%m-01')
        END                                                    -- 会計月
       ,mc1.Class3                                             -- OEMID
       ,mc1.Class2                                             -- OEM先名
       ,e.EnterpriseId                                         -- 加盟店ID
       ,e.EnterpriseNameKj                                     -- 加盟店名
       ,NULL                                                   -- 注文Seq
       ,NULL                                                   -- 注文ID (旧取引ID)
       ,NULL                                                   -- 補償有無
       ,mc2.Class1                                             -- 売上確定条件
       ,v_BusinessDate                                         -- 売上確定日付
       ,NULL                                                   -- 立替締め日
       ,NULL                                                   -- 立替予定日
       ,NULL                                                   -- 伝票番号
       ,NULL                                                   -- 管理顧客番号 
       ,NULL                                                   -- 顧客名
       ,NULL                                                   -- 商品等代金合計
       ,NULL                                                   -- 決済手数料率
       ,NULL                                                   -- 決済手数料
       ,NULL                                                   -- 請求手数料
       ,NULL                                                   -- 請求手数料消費税
       ,NULL                                                   -- 月額固定費
       ,NULL                                                   -- 月額固定費消費税
       ,NULL                                                   -- 同梱月額固定費
       ,NULL                                                   -- 同梱月額固定費費税
       ,NULL                                                   -- API月額固定費
       ,NULL                                                   -- API月額固定費費税
       ,NULL                                                   -- 与信結果通知ｻｰﾋﾞｽ月額固定費
       ,NULL                                                   -- 与信結果通知ｻｰﾋﾞｽ月額固定費費税
       ,NULL                                                   -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,NULL                                                   -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費費税
       ,NULL                                                   -- 月額固定費予備
       ,NULL                                                   -- 月額固定費予備費税
       ,NULL                                                   -- 売上合計
       ,NULL                                                   -- OEM決済手数料率
       ,NULL                                                   -- OEM決済手数料
       ,NULL                                                   -- OEM決済手数料消費税
       ,NULL                                                   -- OEM請求手数料
       ,NULL                                                   -- OEM請求手数料消費税
       ,ec.OemMonthlyFee                                       -- OEM月額固定費
       ,ec.OemMonthlyFeeTax                                    -- OEM月額固定費消費税
       ,ec.OemIncludeMonthlyFee                                -- OEM同梱月額固定費
       ,ec.OemIncludeMonthlyFeeTax                             -- OEM同梱月額固定費消費税
       ,ec.OemApiMonthlyFee                                    -- OEMAPI月額固定費
       ,ec.OemApiMonthlyFeeTax                                 -- OEMAPI月額固定費消費税
       ,ec.OemCreditNoticeMonthlyFee                           -- OEM与信結果通知ｻｰﾋﾞｽ月額固定費
       ,ec.OemCreditNoticeMonthlyFeeTax                        -- OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
       ,ec.OemNCreditNoticeMonthlyFee                          -- OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,ec.OemNCreditNoticeMonthlyFeeTax                       -- OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
       ,ec.OemReserveMonthlyFee                                -- OEM月額固定費予備
       ,ec.OemReserveMonthlyFeeTax                             -- OEM月額固定費予備消費税
       ,ec.OemMonthlyFee + ec.OemIncludeMonthlyFee + ec.OemApiMonthlyFee
       + ec.OemCreditNoticeMonthlyFee + ec.OemNCreditNoticeMonthlyFee
       + ec.OemReserveMonthlyFee                               -- OEM売上合計
       ,NOW()                                                  -- 登録日時
       ,pi_user_id                                             -- 登録者
       ,NOW()                                                  -- 更新日時
       ,pi_user_id                                             -- 更新者
       ,1                                                      -- 有効ﾌﾗｸﾞ (0:無効 1:有効)
    FROM  AT_EnterpriseMonthlyClosingInfo ec
    INNER JOIN M_Code mc1
    ON    mc1.CodeId            = 160
    AND   mc1.KeyCode           = IFNULL(ec.OemId, 0)
    LEFT  JOIN M_Code mc2
    ON    mc2.CodeId            = 158
    AND   mc2.KeyCode           = 8
    INNER JOIN T_Enterprise e
    ON    e.EnterpriseId        = ec.EnterpriseId
    WHERE
          ec.ValidFlg                   = 1
    AND   ec.DailySummaryFlg            = 0
    AND   ec.ClosingMonthly             <= v_BusinessDate
    AND   ( ec.OemMonthlyFee + ec.OemIncludeMonthlyFee
          + ec.OemApiMonthlyFee + ec.OemCreditNoticeMonthlyFee
          + ec.OemNCreditNoticeMonthlyFee + ec.OemReserveMonthlyFee
          )                             > 0
    AND   mc1.Class1                   <> 0
    ;

END
$$

DELIMITER ;
