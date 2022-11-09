DROP PROCEDURE IF EXISTS P_Treas_PayOff_DailyAccount;

DELIMITER $$

CREATE PROCEDURE P_Treas_PayOff_DailyAccount( IN pi_user_id INT )
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名       ：  P_Treas_PayOff_DailyAccount
 *
 * 概要             ：  精算日計
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

    -- ------------------------------
    -- 変数初期化
    -- ------------------------------
    SET v_BusinessDate = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'BusinessDate');        -- 業務日付

    -- ------------------------------
    -- ① 直営精算日計ﾜｰｸ（AW_PayOff_DailyAccount_Cb）をﾄﾗﾝｹｰﾄする
    -- ------------------------------
    TRUNCATE AW_PayOff_DailyAccount_Cb;

    -- ------------------------------
    -- ② OEM精算日計ﾜｰｸ（AW_PayOff_DailyAccount_Oem）をﾄﾗﾝｹｰﾄする
    -- ------------------------------
    TRUNCATE AW_PayOff_DailyAccount_Oem;

    -- ------------------------------
    -- ③ 会計未集計の直営の立替振込管理を集計して直営精算日計ﾜｰｸに出力する
    -- ------------------------------
    -- ③-1 立替金額がﾌﾟﾗｽの立替実行分を集計する
    INSERT INTO AW_PayOff_DailyAccount_Cb(
        PayingControlSeq
       ,OemId
       ,OemNameKj
       ,EnterpriseId
       ,AdvancesFixedDate
       ,AdvancesDate
       ,AdvancesAmount
       ,EnterpriseAccountsDue
       ,AccountsPayablePending
       ,ClaimAndObligationsDecision
       ,UseAmount
       ,CancelAmount
       ,UseSettlementBackOffse
       ,SettlementFee
       ,ClaimFee
       ,MonthlyFee
       ,IncludeMonthlyFee
       ,ApiMonthlyFee
       ,CreditNoticeMonthlyFee
       ,NextClaimCreditNoticeMonthlyFee
       ,StampFeeCount
       ,StampFee
       ,TransferCommissionCount
       ,TransferCommission
       ,AdjustmentAmountCount
       ,AdjustmentAmount
       ,EnterpriseRefundCount
       ,EnterpriseRefund
       ,AccountsDueOffsetAmount
       ,AccountsPayablePendingAmount
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        pc.Seq                                                              -- 立替振込管理Seq
       ,c.KeyCode                                                           -- OEMID
       ,c.KeyContent                                                        -- OEM先名
       ,pc.EnterpriseId                                                     -- 加盟店ID
       ,pc.DecisionDate                                                     -- 立替確定日
       ,pc.ExecDate                                                         -- 立替実行予定日
       ,pc.DecisionPayment                                                  -- 立替実行金額
       ,0                                                                   -- 加盟店未収金
       ,0                                                                   -- 未払金保留
       ,pc.DecisionPayment                                                  -- 債権債務判定
       ,0                                                                   -- 利用額･未払金
       ,0                                                                   -- ｷｬﾝｾﾙ金額
       ,pc.PayBackAmount * -1                                               -- 無保証立替金戻し金額
       ,pc.SettlementFee * -1                                               -- 決済手数料
       ,pc.ClaimFee * -1                                                    -- 請求手数料
       ,(apc.MonthlyFeeWithoutTax + apc.MonthlyFeeTax) * -1                 -- 月額固定費
       ,(apc.IncludeMonthlyFee + apc.IncludeMonthlyFeeTax) * -1             -- 同梱月額固定費
       ,(apc.ApiMonthlyFee + apc.ApiMonthlyFeeTax) * -1                     -- API月額固定費
       ,(apc.CreditNoticeMonthlyFee + apc.CreditNoticeMonthlyFeeTax) * -1   -- 与信結果通知ｻｰﾋﾞｽ月額固定費
       ,(apc.NCreditNoticeMonthlyFee + apc.NCreditNoticeMonthlyFeeTax) * -1 -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,pc.StampFeeCount                                                    -- 印紙代発生件数
       ,pc.StampFeeTotal * -1                                               -- 印紙代
       ,CASE WHEN IFNULL(pc.TransferCommission, 0) = 0 THEN 0 ELSE 1 END    -- 振込手数料件数
       ,pc.TransferCommission * -1                                          -- 振込手数料
       ,pc.AdjustmentCount                                                  -- 調整額件数
       ,pc.AdjustmentAmount                                                 -- 調整金額
       ,0                                                                   -- 加盟店への返金加算件数
       ,0                                                                   -- 加盟店への返金加算
       ,CASE WHEN IFNULL(pc.CarryOver, 0) < 0 THEN pc.CarryOver ELSE 0 END  -- 前精算時未収金相殺
       ,CASE WHEN IFNULL(pc.CarryOver, 0) > 0 THEN pc.CarryOver ELSE 0 END  -- 前精算時未払金保留額
       ,NOW()                                                               -- 登録日時
       ,pi_user_id                                                          -- 登録者
       ,NOW()                                                               -- 更新日時
       ,pi_user_id                                                          -- 更新者
       ,1                                                                   -- 有効ﾌﾗｸﾞ
    FROM
          T_PayingControl pc
    INNER JOIN AT_PayingControl apc
    ON    pc.Seq              = apc.Seq
    INNER JOIN M_Code c
    ON    c.CodeId            = 160
    AND   c.KeyCode           = IFNULL(pc.OemId, 0)
    WHERE apc.DailySummaryFlg = 0
    AND   pc.ValidFlg         = 1
    AND   pc.DecisionPayment  >= 0
    AND   pc.ExecDate         <= v_BusinessDate
    AND   c.Class1            = 0
    ;

    -- ③-2 立替金額がﾏｲﾅｽ且つ振込手数料を除いてもﾏｲﾅｽの加盟店未収金分を集計する
    INSERT INTO AW_PayOff_DailyAccount_Cb(
        PayingControlSeq
       ,OemId
       ,OemNameKj
       ,EnterpriseId
       ,AdvancesFixedDate
       ,AdvancesDate
       ,AdvancesAmount
       ,EnterpriseAccountsDue
       ,AccountsPayablePending
       ,ClaimAndObligationsDecision
       ,UseAmount
       ,CancelAmount
       ,UseSettlementBackOffse
       ,SettlementFee
       ,ClaimFee
       ,MonthlyFee
       ,IncludeMonthlyFee
       ,ApiMonthlyFee
       ,CreditNoticeMonthlyFee
       ,NextClaimCreditNoticeMonthlyFee
       ,StampFeeCount
       ,StampFee
       ,TransferCommissionCount
       ,TransferCommission
       ,AdjustmentAmountCount
       ,AdjustmentAmount
       ,EnterpriseRefundCount
       ,EnterpriseRefund
       ,AccountsDueOffsetAmount
       ,AccountsPayablePendingAmount
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        pc.Seq                                                              -- 立替振込管理Seq
       ,c.KeyCode                                                           -- OEMID
       ,c.KeyContent                                                        -- OEM先名
       ,pc.EnterpriseId                                                     -- 加盟店ID
       ,pc.DecisionDate                                                     -- 立替確定日
       ,pc.ExecDate                                                         -- 立替実行予定日
       ,0                                                                   -- 立替実行金額
       ,(pc.DecisionPayment + pc.TransferCommission) * -1                   -- 加盟店未収金
       ,0                                                                   -- 未払金保留
       ,pc.DecisionPayment - pc.TransferCommission                          -- 債権債務判定
       ,0                                                                   -- 利用額･未払金
       ,0                                                                   -- ｷｬﾝｾﾙ金額
       ,pc.PayBackAmount * -1                                               -- 無保証立替金戻し金額
       ,pc.SettlementFee * -1                                               -- 決済手数料
       ,pc.ClaimFee * -1                                                    -- 請求手数料
       ,(apc.MonthlyFeeWithoutTax + apc.MonthlyFeeTax) * -1                 -- 月額固定費
       ,(apc.IncludeMonthlyFee + apc.IncludeMonthlyFeeTax) * -1             -- 同梱月額固定費
       ,(apc.ApiMonthlyFee + apc.ApiMonthlyFeeTax) * -1                     -- API月額固定費
       ,(apc.CreditNoticeMonthlyFee + apc.CreditNoticeMonthlyFeeTax) * -1   -- 与信結果通知ｻｰﾋﾞｽ月額固定費
       ,(apc.NCreditNoticeMonthlyFee + apc.NCreditNoticeMonthlyFeeTax) * -1 -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,pc.StampFeeCount                                                    -- 印紙代発生件数
       ,pc.StampFeeTotal * -1                                               -- 印紙代
       ,CASE WHEN IFNULL(pc.TransferCommission, 0) = 0 THEN 0 ELSE 1 END    -- 振込手数料件数
       ,0                                                                   -- 振込手数料
       ,pc.AdjustmentCount                                                  -- 調整額件数
       ,pc.AdjustmentAmount                                                 -- 調整金額
       ,0                                                                   -- 加盟店への返金加算件数
       ,0                                                                   -- 加盟店への返金加算
       ,CASE WHEN IFNULL(pc.CarryOver, 0) < 0 THEN pc.CarryOver ELSE 0 END  -- 前精算時未収金相殺
       ,CASE WHEN IFNULL(pc.CarryOver, 0) > 0 THEN pc.CarryOver ELSE 0 END  -- 前精算時未払金保留額
       ,NOW()                                                               -- 登録日時
       ,pi_user_id                                                          -- 登録者
       ,NOW()                                                               -- 更新日時
       ,pi_user_id                                                          -- 更新者
       ,1                                                                   -- 有効ﾌﾗｸﾞ
    FROM
          T_PayingControl pc
    INNER JOIN AT_PayingControl apc
    ON    pc.Seq                = apc.Seq
    INNER JOIN M_Code c
    ON    c.CodeId              = 160
    AND   c.KeyCode             = IFNULL(pc.OemId, 0)
    WHERE apc.DailySummaryFlg   = 0
    AND   pc.ValidFlg           = 1
    AND   pc.DecisionPayment    < 0
    AND   IFNULL(pc.DecisionPayment, 0)
         + IFNULL(pc.TransferCommission, 0) < 0
    AND   pc.ExecDate           <= v_BusinessDate
    AND   c.Class1              = 0
    ;

    -- ③-3 立替金額がﾏｲﾅｽ且つ振込手数料を除くとﾏｲﾅｽでない未払金保留分を集計する(振込手数料以下の立替金額のため未立替)
    INSERT INTO AW_PayOff_DailyAccount_Cb(
        PayingControlSeq
       ,OemId
       ,OemNameKj
       ,EnterpriseId
       ,AdvancesFixedDate
       ,AdvancesDate
       ,AdvancesAmount
       ,EnterpriseAccountsDue
       ,AccountsPayablePending
       ,ClaimAndObligationsDecision
       ,UseAmount
       ,CancelAmount
       ,UseSettlementBackOffse
       ,SettlementFee
       ,ClaimFee
       ,MonthlyFee
       ,IncludeMonthlyFee
       ,ApiMonthlyFee
       ,CreditNoticeMonthlyFee
       ,NextClaimCreditNoticeMonthlyFee
       ,StampFeeCount
       ,StampFee
       ,TransferCommissionCount
       ,TransferCommission
       ,AdjustmentAmountCount
       ,AdjustmentAmount
       ,EnterpriseRefundCount
       ,EnterpriseRefund
       ,AccountsDueOffsetAmount
       ,AccountsPayablePendingAmount
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        pc.Seq                                                              -- 立替振込管理Seq
       ,c.KeyCode                                                           -- OEMID
       ,c.KeyContent                                                        -- OEM先名
       ,pc.EnterpriseId                                                     -- 加盟店ID
       ,pc.DecisionDate                                                     -- 立替確定日
       ,pc.ExecDate                                                         -- 立替実行予定日
       ,0                                                                   -- 立替実行金額
       ,0                                                                   -- 加盟店未収金
       ,(pc.DecisionPayment + pc.TransferCommission) * -1                   -- 未払金保留
       ,pc.DecisionPayment - pc.TransferCommission                          -- 債権債務判定
       ,0                                                                   -- 利用額･未払金
       ,0                                                                   -- ｷｬﾝｾﾙ金額
       ,pc.PayBackAmount * -1                                               -- 無保証立替金戻し金額
       ,pc.SettlementFee * -1                                               -- 決済手数料
       ,pc.ClaimFee * -1                                                    -- 請求手数料
       ,(apc.MonthlyFeeWithoutTax + apc.MonthlyFeeTax) * -1                 -- 月額固定費
       ,(apc.IncludeMonthlyFee + apc.IncludeMonthlyFeeTax) * -1             -- 同梱月額固定費
       ,(apc.ApiMonthlyFee + apc.ApiMonthlyFeeTax) * -1                     -- API月額固定費
       ,(apc.CreditNoticeMonthlyFee + apc.CreditNoticeMonthlyFeeTax) * -1   -- 与信結果通知ｻｰﾋﾞｽ月額固定費
       ,(apc.NCreditNoticeMonthlyFee + apc.NCreditNoticeMonthlyFeeTax) * -1 -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,pc.StampFeeCount                                                    -- 印紙代発生件数
       ,pc.StampFeeTotal * -1                                               -- 印紙代
       ,CASE WHEN IFNULL(pc.TransferCommission, 0) = 0 THEN 0 ELSE 1 END    -- 振込手数料件数
       ,0                                                                   -- 振込手数料
       ,pc.AdjustmentCount                                                  -- 調整額件数
       ,pc.AdjustmentAmount                                                 -- 調整金額
       ,0                                                                   -- 加盟店への返金加算件数
       ,0                                                                   -- 加盟店への返金加算
       ,CASE WHEN IFNULL(pc.CarryOver, 0) < 0 THEN pc.CarryOver ELSE 0 END  -- 前精算時未収金相殺
       ,CASE WHEN IFNULL(pc.CarryOver, 0) > 0 THEN pc.CarryOver ELSE 0 END  -- 前精算時未払金保留額
       ,NOW()                                                               -- 登録日時
       ,pi_user_id                                                          -- 登録者
       ,NOW()                                                               -- 更新日時
       ,pi_user_id                                                          -- 更新者
       ,1                                                                   -- 有効ﾌﾗｸﾞ
    FROM
          T_PayingControl pc
    INNER JOIN AT_PayingControl apc
    ON    pc.Seq                = apc.Seq
    INNER JOIN M_Code c
    ON    c.CodeId              = 160
    AND   c.KeyCode             = IFNULL(pc.OemId, 0)
    WHERE apc.DailySummaryFlg   = 0
    AND   pc.ValidFlg           = 1
    AND   pc.DecisionPayment       < 0
    AND   IFNULL(pc.DecisionPayment, 0)
         + IFNULL(pc.TransferCommission, 0) >= 0
    AND   pc.ExecDate           <= v_BusinessDate
    AND   c.Class1              = 0
    ;

    -- ------------------------------
    -- ④ 会計未集計の直営の立替振込管理に紐付く立替･売上管理より直営精算日計ﾜｰｸの｢利用額･未払金｣を出力する
    -- ------------------------------
    INSERT INTO AW_PayOff_DailyAccount_Cb(
        PayingControlSeq
       ,OemId
       ,OemNameKj
       ,EnterpriseId
       ,AdvancesFixedDate
       ,AdvancesDate
       ,AdvancesAmount
       ,EnterpriseAccountsDue
       ,AccountsPayablePending
       ,ClaimAndObligationsDecision
       ,UseAmount
       ,CancelAmount
       ,UseSettlementBackOffse
       ,SettlementFee
       ,ClaimFee
       ,MonthlyFee
       ,IncludeMonthlyFee
       ,ApiMonthlyFee
       ,CreditNoticeMonthlyFee
       ,NextClaimCreditNoticeMonthlyFee
       ,StampFeeCount
       ,StampFee
       ,TransferCommissionCount
       ,TransferCommission
       ,AdjustmentAmountCount
       ,AdjustmentAmount
       ,EnterpriseRefundCount
       ,EnterpriseRefund
       ,AccountsDueOffsetAmount
       ,AccountsPayablePendingAmount
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        pc.Seq                                                              -- 立替振込管理Seq
       ,c.KeyCode                                                           -- OEMID
       ,c.KeyContent                                                        -- OEM先名
       ,pc.EnterpriseId                                                     -- 加盟店ID
       ,pc.DecisionDate                                                     -- 立替確定日
       ,pc.ExecDate                                                         -- 立替実行予定日
       ,0                                                                   -- 立替実行金額
       ,0                                                                   -- 加盟店未収金
       ,0                                                                   -- 未払金保留
       ,0                                                                   -- 債権債務判定
       ,ps.UseAmount                                                        -- 利用額･未払金
       ,0                                                                   -- ｷｬﾝｾﾙ金額
       ,0                                                                   -- 無保証立替金戻し金額
       ,0                                                                   -- 決済手数料
       ,0                                                                   -- 請求手数料
       ,0                                                                   -- 月額固定費
       ,0                                                                   -- 同梱月額固定費
       ,0                                                                   -- API月額固定費
       ,0                                                                   -- 与信結果通知ｻｰﾋﾞｽ月額固定費
       ,0                                                                   -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,0                                                                   -- 印紙代件数
       ,0                                                                   -- 印紙代
       ,0                                                                   -- 振込手数料件数
       ,0                                                                   -- 振込手数料
       ,0                                                                   -- 調整金額件数
       ,0                                                                   -- 調整金額
       ,0                                                                   -- 加盟店への返金加算件数
       ,0                                                                   -- 加盟店への返金加算
       ,0                                                                   -- 前精算時未収金相殺
       ,0                                                                   -- 前精算時未払金保留額
       ,NOW()                                                               -- 登録日時
       ,pi_user_id                                                          -- 登録者
       ,NOW()                                                               -- 更新日時
       ,pi_user_id                                                          -- 更新者
       ,1                                                                   -- 有効ﾌﾗｸﾞ
    FROM
          T_PayingControl pc
    INNER JOIN AT_PayingControl apc
    ON    pc.Seq                = apc.Seq
    INNER JOIN M_Code c
    ON    c.CodeId              = 160
    AND   c.KeyCode             = IFNULL(pc.OemId, 0)
    INNER JOIN T_PayingAndSales ps
    ON    pc.Seq                = ps.PayingControlSeq
    AND   ps.ValidFlg           = 1
    WHERE apc.DailySummaryFlg   = 0
    AND   pc.ValidFlg           = 1
    AND   pc.ExecDate           <= v_BusinessDate
    AND   c.Class1              = 0
    ;

    -- ------------------------------
    -- ⑥ 会計未集計の直営の立替振込管理に紐付くｷｬﾝｾﾙ管理より直営精算日計ﾜｰｸの｢ｷｬﾝｾﾙ金額｣を出力する
    -- ------------------------------
    INSERT INTO AW_PayOff_DailyAccount_Cb(
        PayingControlSeq
       ,OemId
       ,OemNameKj
       ,EnterpriseId
       ,AdvancesFixedDate
       ,AdvancesDate
       ,AdvancesAmount
       ,EnterpriseAccountsDue
       ,AccountsPayablePending
       ,ClaimAndObligationsDecision
       ,UseAmount
       ,CancelAmount
       ,UseSettlementBackOffse
       ,SettlementFee
       ,ClaimFee
       ,MonthlyFee
       ,IncludeMonthlyFee
       ,ApiMonthlyFee
       ,CreditNoticeMonthlyFee
       ,NextClaimCreditNoticeMonthlyFee
       ,StampFeeCount
       ,StampFee
       ,TransferCommissionCount
       ,TransferCommission
       ,AdjustmentAmountCount
       ,AdjustmentAmount
       ,EnterpriseRefundCount
       ,EnterpriseRefund
       ,AccountsDueOffsetAmount
       ,AccountsPayablePendingAmount
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        pc.Seq                                                              -- 立替振込管理Seq
       ,c.KeyCode                                                           -- OEMID
       ,c.KeyContent                                                        -- OEM先名
       ,pc.EnterpriseId                                                     -- 加盟店ID
       ,pc.DecisionDate                                                     -- 立替確定日
       ,pc.ExecDate                                                         -- 立替実行予定日
       ,0                                                                   -- 立替実行金額
       ,0                                                                   -- 加盟店未収金
       ,0                                                                   -- 未払金保留
       ,0                                                                   -- 債権債務判定
       ,0                                                                   -- 利用額･未払金
       ,CASE
            WHEN tc.CancelPhase in (1, 4) THEN 0
            ELSE ps.UseAmount * -1
        END                                                                 -- ｷｬﾝｾﾙ金額：立替後入金前､立替後入金後のｷｬﾝｾﾙ（ｷｬﾝｾﾙﾌｪｰｽﾞ2、3）の場合のみ取得する（立替前入金前､立替前入金後のｷｬﾝｾﾙの場合は0）
       ,0                                                                   -- 無保証立替金戻し金額
       -- ｻﾏﾘしたときにｷｬﾝｾﾙ分の決済手数料と請求手数料が相殺されるようにﾌﾟﾗｽ金額で出力する。
       ,CASE
            WHEN tc.CancelPhase in (1, 4) THEN 0
            ELSE ps.SettlementFee
        END                                                                 -- 決済手数料：立替後入金前､立替後入金後ｷｬﾝｾﾙ（ｷｬﾝｾﾙﾌｪｰｽﾞ2、3）の場合のみ取得する（立替前入金前､立替前入金後のｷｬﾝｾﾙの場合は0）
       ,CASE
            WHEN tc.CancelPhase in (1, 4) THEN 0
            ELSE ps.ClaimFee
        END                                                                 -- 請求手数料：立替後入金前､立替後入金後ｷｬﾝｾﾙ（ｷｬﾝｾﾙﾌｪｰｽﾞ2、3）の場合のみ取得する（立替前入金前､立替前入金後のｷｬﾝｾﾙの場合は0）
       ,0                                                                   -- 月額固定費
       ,0                                                                   -- 同梱月額固定費
       ,0                                                                   -- API月額固定費
       ,0                                                                   -- 与信結果通知ｻｰﾋﾞｽ月額固定費
       ,0                                                                   -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,0                                                                   -- 印紙代件数
       ,0                                                                   -- 印紙代
       ,0                                                                   -- 振込手数料件数
       ,0                                                                   -- 振込手数料
       ,0                                                                   -- 調整金額件数
       ,0                                                                   -- 調整金額
       ,0                                                                   -- 加盟店への返金加算件数
       ,0                                                                   -- 加盟店への返金加算
       ,0                                                                   -- 前精算時未収金相殺
       ,0                                                                   -- 前精算時未払金保留額
       ,NOW()                                                               -- 登録日時
       ,pi_user_id                                                          -- 登録者
       ,NOW()                                                               -- 更新日時
       ,pi_user_id                                                          -- 更新者
       ,1                                                                   -- 有効ﾌﾗｸﾞ
    FROM
          T_PayingControl pc
    INNER JOIN AT_PayingControl apc
    ON    pc.Seq                = apc.Seq
    INNER JOIN M_Code c
    ON    c.CodeId              = 160
    AND   c.KeyCode             = IFNULL(pc.OemId, 0)
    INNER JOIN T_Cancel tc
    ON    pc.Seq                = tc.PayingControlSeq
    AND   tc.ValidFlg           = 1
    INNER JOIN T_PayingAndSales ps
    ON    tc.OrderSeq           = ps.OrderSeq
    WHERE apc.DailySummaryFlg   = 0
    AND   pc.ValidFlg           = 1
    AND   pc.ExecDate           <= v_BusinessDate
    AND   c.Class1              = 0
    ;

    -- ------------------------------
    -- ⑦ 会計未集計の直営の立替振込管理に紐付くｷｬﾝｾﾙ管理の入金済より直営精算日計ﾜｰｸの｢加盟店への返金加算｣を出力する
    -- ------------------------------
    INSERT INTO AW_PayOff_DailyAccount_Cb(
        PayingControlSeq
       ,OemId
       ,OemNameKj
       ,EnterpriseId
       ,AdvancesFixedDate
       ,AdvancesDate
       ,AdvancesAmount
       ,EnterpriseAccountsDue
       ,AccountsPayablePending
       ,ClaimAndObligationsDecision
       ,UseAmount
       ,CancelAmount
       ,UseSettlementBackOffse
       ,SettlementFee
       ,ClaimFee
       ,MonthlyFee
       ,IncludeMonthlyFee
       ,ApiMonthlyFee
       ,CreditNoticeMonthlyFee
       ,NextClaimCreditNoticeMonthlyFee
       ,StampFeeCount
       ,StampFee
       ,TransferCommissionCount
       ,TransferCommission
       ,AdjustmentAmountCount
       ,AdjustmentAmount
       ,EnterpriseRefundCount
       ,EnterpriseRefund
       ,AccountsDueOffsetAmount
       ,AccountsPayablePendingAmount
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        pc.Seq                                                              -- 立替振込管理Seq
       ,c.KeyCode                                                           -- OEMID
       ,c.KeyContent                                                        -- OEM先名
       ,pc.EnterpriseId                                                     -- 加盟店ID
       ,pc.DecisionDate                                                     -- 立替確定日
       ,pc.ExecDate                                                         -- 立替実行予定日
       ,0                                                                   -- 立替実行金額
       ,0                                                                   -- 加盟店未収金
       ,0                                                                   -- 未払金保留
       ,0                                                                   -- 債権債務判定
       ,0                                                                   -- 利用額･未払金
       ,0                                                                   -- ｷｬﾝｾﾙ金額
       ,0                                                                   -- 無保証立替金戻し金額
       ,0                                                                   -- 決済手数料
       ,0                                                                   -- 請求手数料
       ,0                                                                   -- 月額固定費
       ,0                                                                   -- 同梱月額固定費
       ,0                                                                   -- API月額固定費
       ,0                                                                   -- 与信結果通知ｻｰﾋﾞｽ月額固定費
       ,0                                                                   -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,0                                                                   -- 印紙代件数
       ,0                                                                   -- 印紙代
       ,0                                                                   -- 振込手数料件数
       ,0                                                                   -- 振込手数料
       ,0                                                                   -- 調整金額件数
       ,0                                                                   -- 調整金額
       ,1                                                                   -- 加盟店への返金加算件数
       ,cc.ReceiptAmountTotal                                               -- 加盟店への返金加算
       ,0                                                                   -- 前精算時未収金相殺
       ,0                                                                   -- 前精算時未払金保留額
       ,NOW()                                                               -- 登録日時
       ,pi_user_id                                                          -- 登録者
       ,NOW()                                                               -- 更新日時
       ,pi_user_id                                                          -- 更新者
       ,1                                                                   -- 有効ﾌﾗｸﾞ
    FROM
          T_PayingControl pc
    INNER JOIN AT_PayingControl apc
    ON    pc.Seq                = apc.Seq
    INNER JOIN M_Code c
    ON    c.CodeId              = 160
    AND   c.KeyCode             = IFNULL(pc.OemId, 0)
    INNER JOIN T_Cancel tc
    ON    pc.Seq                = tc.PayingControlSeq
    AND   tc.ValidFlg           = 1
    AND   tc.CancelPhase        IN (3, 4)
    INNER JOIN T_ClaimControl cc
    ON    cc.OrderSeq           = tc.OrderSeq
    WHERE apc.DailySummaryFlg   = 0
    AND   pc.ValidFlg           = 1
    AND   pc.ExecDate           <= v_BusinessDate
    AND   c.Class1              = 0
    ;

    -- ------------------------------
    -- ⑧ 直営精算日計ﾜｰｸをｻﾏﾘｰして直営分の精算日計を出力する
    -- ------------------------------
    INSERT INTO AT_PayOff_DailyAccount(
        DailyMonthlyFlg
       ,ProcessingDate
       ,OemId
       ,OemNameKj
       ,EnterpriseId
       ,EnterpriseNameKj
       ,MerchantNumber
       ,AdvancesDate
       ,AdvancesAmount
       ,EnterpriseAccountsDue
       ,AccountsPayablePending
       ,ClaimAndObligationsDecision
       ,UseAmount
       ,CancelAmount
       ,UseSettlementBackOffse
       ,AccountsPayableTotal
       ,SettlementFee
       ,ClaimFee
       ,MonthlyFee
       ,IncludeMonthlyFee
       ,ApiMonthlyFee
       ,CreditNoticeMonthlyFee
       ,NextClaimCreditNoticeMonthlyFee
       ,AccountsReceivableTotal
       ,StampFeeCount
       ,StampFee
       ,TransferCommissionCount
       ,TransferCommission
       ,AdjustmentAmountCount
       ,AdjustmentAmount
       ,EnterpriseRefundCount
       ,EnterpriseRefund
       ,AccountsDueOffsetAmount
       ,AccountsPayablePendingAmount
       ,AdvancesFixedDate
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        0                                                                   -- 日次･月次区分
       ,v_BusinessDate                                                      -- 処理日付
       ,wk.OemId                                                            -- OEMID
       ,wk.OemNameKj                                                        -- OEM先名
       ,wk.EnterpriseId                                                     -- 加盟店ID
       ,MAX(e.EnterpriseNameKj)                                             -- 加盟店名
       ,NULL                                                                -- 加盟店件数
       ,wk.AdvancesDate                                                     -- 立替日
       -- 「立替実行金額」は「加盟店未収金」と「未払金保留」が発生しない（ｻﾏﾘ値が 0）場合は「債権債務判定」の値を出力
       ,CASE WHEN SUM(wk.EnterpriseAccountsDue) = 0 AND SUM(wk.AccountsPayablePending) = 0
            THEN SUM(wk.UseAmount) + SUM(wk.CancelAmount) + SUM(wk.UseSettlementBackOffse) +
                 SUM(wk.SettlementFee) + SUM(wk.ClaimFee) + SUM(wk.MonthlyFee) +
                 SUM(wk.IncludeMonthlyFee) + SUM(wk.ApiMonthlyFee) + SUM(wk.CreditNoticeMonthlyFee) +
                 SUM(wk.NextClaimCreditNoticeMonthlyFee) +SUM(wk.StampFee) + SUM(wk.TransferCommission) +
                 SUM(wk.AdjustmentAmount) + SUM(wk.EnterpriseRefund) + SUM(wk.AccountsDueOffsetAmount) +
                 SUM(wk.AccountsPayablePendingAmount)
            ELSE SUM(wk.AdvancesAmount)
         END                                                                -- 立替実行金額
       ,SUM(wk.EnterpriseAccountsDue)                                       -- 加盟店未収金
       ,SUM(wk.AccountsPayablePending)                                      -- 未払金保留
       ,SUM(wk.UseAmount) + SUM(wk.CancelAmount) + SUM(wk.UseSettlementBackOffse) +
        SUM(wk.SettlementFee) + SUM(wk.ClaimFee) + SUM(wk.MonthlyFee) +
        SUM(wk.IncludeMonthlyFee) + SUM(wk.ApiMonthlyFee) + SUM(wk.CreditNoticeMonthlyFee) +
        SUM(wk.NextClaimCreditNoticeMonthlyFee) +SUM(wk.StampFee) + SUM(wk.TransferCommission) +
        SUM(wk.AdjustmentAmount) + SUM(wk.EnterpriseRefund) + SUM(wk.AccountsDueOffsetAmount) +
        SUM(wk.AccountsPayablePendingAmount)                                -- 債権債務判定
       ,SUM(wk.UseAmount)                                                   -- 利用額･未払金
       ,SUM(wk.CancelAmount)                                                -- ｷｬﾝｾﾙ金額
       ,SUM(wk.UseSettlementBackOffse)                                      -- 無保証立替金戻し金額
       ,SUM(wk.UseAmount) + SUM(wk.CancelAmount) +
        SUM(wk.UseSettlementBackOffse)                                      -- 未払金計
       ,SUM(wk.SettlementFee)                                               -- 決済手数料
       ,SUM(wk.ClaimFee)                                                    -- 請求手数料
       ,SUM(wk.MonthlyFee)                                                  -- 月額固定費
       ,SUM(wk.IncludeMonthlyFee)                                           -- 同梱月額固定費
       ,SUM(wk.ApiMonthlyFee)                                               -- API月額固定費
       ,SUM(wk.CreditNoticeMonthlyFee)                                      -- 与信結果通知ｻｰﾋﾞｽ月額固定費
       ,SUM(wk.NextClaimCreditNoticeMonthlyFee)                             -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,SUM(wk.SettlementFee) + SUM(wk.ClaimFee) +
        SUM(wk.MonthlyFee) + SUM(wk.IncludeMonthlyFee) +
        SUM(wk.ApiMonthlyFee) + SUM(wk.CreditNoticeMonthlyFee) +
        SUM(wk.NextClaimCreditNoticeMonthlyFee)                             -- 売掛金計
       ,SUM(wk.StampFeeCount)                                               -- 印紙代発生件数
       ,SUM(wk.StampFee)                                                    -- 印紙代
       ,SUM(wk.TransferCommissionCount)                                     -- 振込手数料件数
       ,SUM(wk.TransferCommission)                                          -- 振込手数料
       ,SUM(wk.AdjustmentAmountCount)                                       -- 調整額件数
       ,SUM(wk.AdjustmentAmount)                                            -- 調整金額
       ,SUM(wk.EnterpriseRefundCount)                                       -- 加盟店への返金加算件数
       ,SUM(wk.EnterpriseRefund)                                            -- 加盟店への返金加算
       ,SUM(wk.AccountsDueOffsetAmount)                                     -- 前精算時未収金相殺
       ,SUM(wk.AccountsPayablePendingAmount)                                -- 前精算時未払金保留額
       ,wk.AdvancesFixedDate                                                -- 立替確定日
       ,NOW()                                                               -- 登録日時
       ,pi_user_id                                                          -- 登録者
       ,NOW()                                                               -- 更新日時
       ,pi_user_id                                                          -- 更新者
       ,1                                                                   -- 有効ﾌﾗｸﾞ
    FROM
          AW_PayOff_DailyAccount_Cb wk
    LEFT  JOIN T_Enterprise e
    ON    wk.EnterpriseId       = e.EnterpriseId
    GROUP BY
        wk.PayingControlSeq
       ,wk.OemId
       ,wk.OemNameKj
       ,wk.EnterpriseId
       ,wk.AdvancesFixedDate
       ,wk.AdvancesDate
    ;

    -- ------------------------------
    -- ⑨ 会計未集計のOEM分のOEM請求を集計してOEM精算日計ﾜｰｸに出力する
    -- ------------------------------
    INSERT INTO AW_PayOff_DailyAccount_Oem(
        OemClaimedSeq
       ,OemId
       ,OemNameKj
       ,MerchantNumber
       ,AdvancesFixedDate
       ,AdvancesDate
       ,AdvancesAmount
       ,EnterpriseAccountsDue
       ,AccountsPayablePending
       ,ClaimAndObligationsDecision
       ,UseAmount
       ,CancelAmount
       ,UseSettlementBackOffse
       ,SettlementFee
       ,ClaimFee
       ,MonthlyFee
       ,IncludeMonthlyFee
       ,ApiMonthlyFee
       ,CreditNoticeMonthlyFee
       ,NextClaimCreditNoticeMonthlyFee
       ,StampFeeCount
       ,StampFee
       ,TransferCommissionCount
       ,TransferCommission
       ,AdjustmentAmountCount
       ,AdjustmentAmount
       ,EnterpriseRefundCount
       ,EnterpriseRefund
       ,AccountsDueOffsetAmount
       ,AccountsPayablePendingAmount
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        oc.OemClaimedSeq                                                    -- OEM請求ﾃﾞｰﾀｼｰｹﾝｽ
       ,c.Class3                                                            -- OEMID
       ,c.Class2                                                            -- OEM先名
       ,oc.OM_SettleShopTotal                                               -- 加盟店件数
       ,oc.ProcessDate                                                      -- 立替確定日
       ,oc.SettlePlanDate                                                   -- 立替実行予定日
       ,oc.FixedTransferAmount                                              -- 立替実行金額
       ,0                                                                   -- 加盟店未収金
       ,0                                                                   -- 未払金保留
       ,oc.FixedTransferAmount                                              -- 債権債務判定
       ,0                                                                   -- 利用額･未払金
       ,0                                                                   -- ｷｬﾝｾﾙ金額
       ,oc.PayBackAmount                                                    -- 無保証立替金戻し金額
       ,oc.CB_SettlementFee                                                 -- 決済手数料
       ,oc.CB_ClaimFeeBS + oc.CB_ClaimFeeDK                                 -- 請求手数料
       ,oc.CB_EntMonthlyFee - pctmp.IncludeMonthlyFee -
        pctmp.ApiMonthlyFee - pctmp.CreditNoticeMonthlyFee -
        pctmp.NCreditNoticeMonthlyFee                                       -- 月額固定費
       ,pctmp.IncludeMonthlyFee                                             -- 同梱月額固定費
       ,pctmp.ApiMonthlyFee                                                 -- API月額固定費
       ,pctmp.CreditNoticeMonthlyFee                                        -- 与信結果通知ｻｰﾋﾞｽ月額固定費
       ,pctmp.NCreditNoticeMonthlyFee                                       -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,pctmp.StampFeeCount                                                 -- 印紙代件数
       ,pctmp.StampFeeTotal                                                 -- 印紙代
       ,0                                                                   -- 0
       ,0                                                                   -- 振込手数料
       ,pctmp.AdjustmentCount                                               -- 調整金額件数
       ,oc.PC_AdjustmentAmount                                              -- 調整金額
       ,0                                                                   -- 加盟店への返金加算件数
       ,0                                                                   -- 加盟店への返金加算
       ,pctmp.CarryOver                                                     -- 前精算時未収金相殺
       ,0                                                                   -- 前精算時未払金保留額
       ,NOW()                                                               -- 登録日時
       ,pi_user_id                                                          -- 登録者
       ,NOW()                                                               -- 更新日時
       ,pi_user_id                                                          -- 更新者
       ,1                                                                   -- 有効ﾌﾗｸﾞ
    FROM
          T_OemClaimed oc
    INNER JOIN AT_OemClaimed aoc
    ON    oc.OemClaimedSeq    = aoc.OemClaimedSeq
    INNER JOIN M_Code c
    ON    c.CodeId            = 160
    AND   c.KeyCode           = IFNULL(oc.OemId, 0)
    INNER JOIN (SELECT
                    pc.OemClaimedSeq                                               -- OEM請求ﾃﾞｰﾀｼｰｹﾝｽ
                   ,SUM(apc.OemMonthlyFeeWithoutTax) + 
                    SUM(apc.OemMonthlyFeeTax)              MonthlyFee              -- 月額固定費
                   ,SUM(apc.OemIncludeMonthlyFee) + 
                    SUM(apc.OemIncludeMonthlyFeeTax)       IncludeMonthlyFee       -- 同梱月額固定費
                   ,SUM(apc.OemApiMonthlyFee) + 
                    SUM(apc.OemApiMonthlyFeeTax)           ApiMonthlyFee           -- API月額固定費
                   ,SUM(apc.OemCreditNoticeMonthlyFee) + 
                    SUM(apc.OemCreditNoticeMonthlyFeeTax)  CreditNoticeMonthlyFee  -- 与信結果通知ｻｰﾋﾞｽ月額固定費
                   ,SUM(apc.OemNCreditNoticeMonthlyFee) + 
                    SUM(apc.OemNCreditNoticeMonthlyFeeTax) NCreditNoticeMonthlyFee -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
                   ,SUM(pc.AdjustmentCount)                AdjustmentCount         -- 調整額件数
                   ,SUM(pc.CarryOver)                      CarryOver               -- 繰越
                   ,SUM(pc.StampFeeCount)                  StampFeeCount           -- 印紙代件数
                   ,SUM(pc.StampFeeTotal)                  StampFeeTotal           -- 印紙代
                FROM
                      T_PayingControl pc
                INNER JOIN AT_PayingControl apc
                ON    pc.Seq  = apc.Seq
                GROUP BY pc.OemClaimedSeq
               ) pctmp
    ON    oc.OemClaimedSeq    = pctmp.OemClaimedSeq
    WHERE aoc.DailySummaryFlg = 0
    AND   oc.ValidFlg         = 1
    AND   oc.ProcessDate     <= v_BusinessDate
    AND   c.Class1           <> 0
    ;

    -- ------------------------------
    -- ⑩ 会計未集計のOEM分のOEM請求に紐付く立替振込管理に紐付く立替･売上管理より直営精算日計ﾜｰｸの｢利用額･未払金｣を出力する
    -- ------------------------------
    INSERT INTO AW_PayOff_DailyAccount_Oem(
        OemClaimedSeq
       ,OemId
       ,OemNameKj
       ,MerchantNumber
       ,AdvancesFixedDate
       ,AdvancesDate
       ,AdvancesAmount
       ,EnterpriseAccountsDue
       ,AccountsPayablePending
       ,ClaimAndObligationsDecision
       ,UseAmount
       ,CancelAmount
       ,UseSettlementBackOffse
       ,SettlementFee
       ,ClaimFee
       ,MonthlyFee
       ,IncludeMonthlyFee
       ,ApiMonthlyFee
       ,CreditNoticeMonthlyFee
       ,NextClaimCreditNoticeMonthlyFee
       ,StampFeeCount
       ,StampFee
       ,TransferCommissionCount
       ,TransferCommission
       ,AdjustmentAmountCount
       ,AdjustmentAmount
       ,EnterpriseRefundCount
       ,EnterpriseRefund
       ,AccountsDueOffsetAmount
       ,AccountsPayablePendingAmount
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        oc.OemClaimedSeq                                                    -- OEM請求ﾃﾞｰﾀｼｰｹﾝｽ
       ,c.Class3                                                            -- OEMID
       ,c.Class2                                                            -- OEM先名
       ,0                                                                   -- 加盟店件数
       ,oc.ProcessDate                                                      -- 立替確定日
       ,oc.SettlePlanDate                                                   -- 立替実行予定日
       ,0                                                                   -- 立替実行金額
       ,0                                                                   -- 加盟店未収金
       ,0                                                                   -- 未払金保留
       ,0                                                                   -- 債権債務判定
       ,oc.UseAmount                                                        -- 利用額･未払金
       ,0                                                                   -- ｷｬﾝｾﾙ金額
       ,0                                                                   -- 無保証立替金戻し金額
       ,0                                                                   -- 決済手数料
       ,0                                                                   -- 請求手数料
       ,0                                                                   -- 月額固定費
       ,0                                                                   -- 同梱月額固定費
       ,0                                                                   -- API月額固定費
       ,0                                                                   -- 与信結果通知ｻｰﾋﾞｽ月額固定費
       ,0                                                                   -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,0                                                                   -- 印紙代件数
       ,0                                                                   -- 印紙代
       ,0                                                                   -- 振込手数料件数
       ,0                                                                   -- 振込手数料
       ,0                                                                   -- 調整金額件数
       ,0                                                                   -- 調整金額
       ,0                                                                   -- 加盟店への返金加算件数
       ,0                                                                   -- 加盟店への返金加算
       ,0                                                                   -- 前精算時未収金相殺
       ,0                                                                   -- 前精算時未払金保留額
       ,NOW()                                                               -- 登録日時
       ,pi_user_id                                                          -- 登録者
       ,NOW()                                                               -- 更新日時
       ,pi_user_id                                                          -- 更新者
       ,1                                                                   -- 有効ﾌﾗｸﾞ
    FROM
          T_OemClaimed oc
    INNER JOIN AT_OemClaimed aoc
    ON    oc.OemClaimedSeq    = aoc.OemClaimedSeq
    INNER JOIN M_Code c
    ON    c.CodeId            = 160
    AND   c.KeyCode           = IFNULL(oc.OemId, 0)
    INNER JOIN T_PayingControl pc
    ON    oc.OemClaimedSeq    = pc.OemClaimedSeq
    INNER JOIN T_PayingAndSales ps
    ON    pc.Seq  = ps.PayingControlSeq
    WHERE aoc.DailySummaryFlg = 0
    AND   oc.ValidFlg         = 1
    AND   oc.ProcessDate     <= v_BusinessDate
    AND   c.Class1           <> 0
    AND   ps.ValidFlg         = 1
    ;

    -- ------------------------------
    -- ⑪ 会計未集計のOEM分のOEM請求に紐付く立替振込管理に紐付くｷｬﾝｾﾙ管理で当月立替当月ｷｬﾝｾﾙﾃﾞｰﾀより直営精算日計ﾜｰｸの｢利用額･未払金｣を出力する
    -- ------------------------------
    INSERT INTO AW_PayOff_DailyAccount_Oem(
        OemClaimedSeq
       ,OemId
       ,OemNameKj
       ,MerchantNumber
       ,AdvancesFixedDate
       ,AdvancesDate
       ,AdvancesAmount
       ,EnterpriseAccountsDue
       ,AccountsPayablePending
       ,ClaimAndObligationsDecision
       ,UseAmount
       ,CancelAmount
       ,UseSettlementBackOffse
       ,SettlementFee
       ,ClaimFee
       ,MonthlyFee
       ,IncludeMonthlyFee
       ,ApiMonthlyFee
       ,CreditNoticeMonthlyFee
       ,NextClaimCreditNoticeMonthlyFee
       ,StampFeeCount
       ,StampFee
       ,TransferCommissionCount
       ,TransferCommission
       ,AdjustmentAmountCount
       ,AdjustmentAmount
       ,EnterpriseRefundCount
       ,EnterpriseRefund
       ,AccountsDueOffsetAmount
       ,AccountsPayablePendingAmount
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        oc.OemClaimedSeq                                                    -- OEM請求ﾃﾞｰﾀｼｰｹﾝｽ
       ,c.Class3                                                            -- OEMID
       ,c.Class2                                                            -- OEM先名
       ,0                                                                   -- 加盟店件数
       ,oc.ProcessDate                                                      -- 立替確定日
       ,oc.SettlePlanDate                                                   -- 立替実行予定日
       ,0                                                                   -- 立替実行金額
       ,0                                                                   -- 加盟店未収金
       ,0                                                                   -- 未払金保留
       ,0                                                                   -- 債権債務判定
       ,oc.UseAmount                                                        -- 利用額･未払金
       ,0                                                                   -- ｷｬﾝｾﾙ金額
       ,0                                                                   -- 無保証立替金戻し金額
       ,0                                                                   -- 決済手数料
       ,0                                                                   -- 請求手数料
       ,0                                                                   -- 月額固定費
       ,0                                                                   -- 同梱月額固定費
       ,0                                                                   -- API月額固定費
       ,0                                                                   -- 与信結果通知ｻｰﾋﾞｽ月額固定費
       ,0                                                                   -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,0                                                                   -- 印紙代件数
       ,0                                                                   -- 印紙代
       ,0                                                                   -- 振込手数料件数
       ,0                                                                   -- 振込手数料
       ,0                                                                   -- 調整金額件数
       ,0                                                                   -- 調整金額
       ,0                                                                   -- 加盟店への返金加算件数
       ,0                                                                   -- 加盟店への返金加算
       ,0                                                                   -- 前精算時未収金相殺
       ,0                                                                   -- 前精算時未払金保留額
       ,NOW()                                                               -- 登録日時
       ,pi_user_id                                                          -- 登録者
       ,NOW()                                                               -- 更新日時
       ,pi_user_id                                                          -- 更新者
       ,1                                                                   -- 有効ﾌﾗｸﾞ
    FROM
          T_OemClaimed oc
    INNER JOIN AT_OemClaimed aoc
    ON    oc.OemClaimedSeq    = aoc.OemClaimedSeq
    INNER JOIN M_Code c
    ON    c.CodeId            = 160
    AND   c.KeyCode           = IFNULL(oc.OemId, 0)
    INNER JOIN T_PayingControl pc
    ON    oc.OemClaimedSeq    = pc.OemClaimedSeq
    INNER JOIN T_Cancel tc
    ON    pc.Seq            = tc.PayingControlSeq
    INNER JOIN T_PayingAndSales ps
    ON    tc.OrderSeq       = ps.OrderSeq
    WHERE aoc.DailySummaryFlg = 0
    AND   oc.ValidFlg         = 1
    AND   oc.ProcessDate     <= v_BusinessDate
    AND   c.Class1           <> 0
    AND   tc.ValidFlg         = 1
    AND   tc.CancelPhase     IN (1, 4)
    ;

    -- ------------------------------
    -- ⑫ 会計未集計のOEM分のOEM請求に紐付く立替振込管理に紐付くｷｬﾝｾﾙ管理より直営精算日計ﾜｰｸの｢ｷｬﾝｾﾙ金額｣を出力する
    -- ------------------------------
    INSERT INTO AW_PayOff_DailyAccount_Oem(
        OemClaimedSeq
       ,OemId
       ,OemNameKj
       ,MerchantNumber
       ,AdvancesFixedDate
       ,AdvancesDate
       ,AdvancesAmount
       ,EnterpriseAccountsDue
       ,AccountsPayablePending
       ,ClaimAndObligationsDecision
       ,UseAmount
       ,CancelAmount
       ,UseSettlementBackOffse
       ,SettlementFee
       ,ClaimFee
       ,MonthlyFee
       ,IncludeMonthlyFee
       ,ApiMonthlyFee
       ,CreditNoticeMonthlyFee
       ,NextClaimCreditNoticeMonthlyFee
       ,StampFeeCount
       ,StampFee
       ,TransferCommissionCount
       ,TransferCommission
       ,AdjustmentAmountCount
       ,AdjustmentAmount
       ,EnterpriseRefundCount
       ,EnterpriseRefund
       ,AccountsDueOffsetAmount
       ,AccountsPayablePendingAmount
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        oc.OemClaimedSeq                                                    -- OEM請求ﾃﾞｰﾀｼｰｹﾝｽ
       ,c.Class3                                                            -- OEMID
       ,c.Class2                                                            -- OEM先名
       ,0                                                                   -- 加盟店件数
       ,oc.ProcessDate                                                      -- 立替確定日
       ,oc.SettlePlanDate                                                   -- 立替実行予定日
       ,0                                                                   -- 立替実行金額
       ,0                                                                   -- 加盟店未収金
       ,0                                                                   -- 未払金保留
       ,0                                                                   -- 債権債務判定
       ,0                                                                   -- 利用額･未払金
       ,oc.CR_TotalAmount * -1                                              -- ｷｬﾝｾﾙ金額
       ,0                                                                   -- 無保証立替金戻し金額
       ,0                                                                   -- 決済手数料
       ,0                                                                   -- 請求手数料
       ,0                                                                   -- 月額固定費
       ,0                                                                   -- 同梱月額固定費
       ,0                                                                   -- API月額固定費
       ,0                                                                   -- 与信結果通知ｻｰﾋﾞｽ月額固定費
       ,0                                                                   -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,0                                                                   -- 印紙代件数
       ,0                                                                   -- 印紙代
       ,0                                                                   -- 振込手数料件数
       ,0                                                                   -- 振込手数料
       ,0                                                                   -- 調整金額件数
       ,0                                                                   -- 調整金額
       ,0                                                                   -- 加盟店への返金加算件数
       ,0                                                                   -- 加盟店への返金加算
       ,0                                                                   -- 前精算時未収金相殺
       ,0                                                                   -- 前精算時未払金保留額
       ,NOW()                                                               -- 登録日時
       ,pi_user_id                                                          -- 登録者
       ,NOW()                                                               -- 更新日時
       ,pi_user_id                                                          -- 更新者
       ,1                                                                   -- 有効ﾌﾗｸﾞ
    FROM
          T_OemClaimed oc
    INNER JOIN AT_OemClaimed aoc
    ON    oc.OemClaimedSeq    = aoc.OemClaimedSeq
    INNER JOIN M_Code c
    ON    c.CodeId            = 160
    AND   c.KeyCode           = IFNULL(oc.OemId, 0)
    INNER JOIN T_PayingControl pc
    ON    oc.OemClaimedSeq    = pc.OemClaimedSeq
    INNER JOIN T_Cancel tc
    ON    pc.Seq            = tc.PayingControlSeq
    INNER JOIN T_PayingAndSales ps
    ON    tc.OrderSeq       = ps.OrderSeq
    WHERE aoc.DailySummaryFlg = 0
    AND   oc.ValidFlg         = 1
    AND   oc.ProcessDate     <= v_BusinessDate
    AND   c.Class1           <> 0
    AND   tc.ValidFlg         = 1
    ;

    -- ------------------------------
    -- ⑬ 会計未集計のOEM分のOEM請求に紐付く立替振込管理に紐付くｷｬﾝｾﾙ管理の入金済より直営精算日計ﾜｰｸの｢加盟店への返金加算｣を出力する
    -- ------------------------------
    INSERT INTO AW_PayOff_DailyAccount_Oem(
        OemClaimedSeq
       ,OemId
       ,OemNameKj
       ,MerchantNumber
       ,AdvancesFixedDate
       ,AdvancesDate
       ,AdvancesAmount
       ,EnterpriseAccountsDue
       ,AccountsPayablePending
       ,ClaimAndObligationsDecision
       ,UseAmount
       ,CancelAmount
       ,UseSettlementBackOffse
       ,SettlementFee
       ,ClaimFee
       ,MonthlyFee
       ,IncludeMonthlyFee
       ,ApiMonthlyFee
       ,CreditNoticeMonthlyFee
       ,NextClaimCreditNoticeMonthlyFee
       ,StampFeeCount
       ,StampFee
       ,TransferCommissionCount
       ,TransferCommission
       ,AdjustmentAmountCount
       ,AdjustmentAmount
       ,EnterpriseRefundCount
       ,EnterpriseRefund
       ,AccountsDueOffsetAmount
       ,AccountsPayablePendingAmount
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT
        oc.OemClaimedSeq                                                    -- OEM請求ﾃﾞｰﾀｼｰｹﾝｽ
       ,c.Class3                                                            -- OEMID
       ,c.Class2                                                            -- OEM先名
       ,0                                                                   -- 加盟店件数
       ,oc.ProcessDate                                                      -- 立替確定日
       ,oc.SettlePlanDate                                                   -- 立替実行予定日
       ,0                                                                   -- 立替実行金額
       ,0                                                                   -- 加盟店未収金
       ,0                                                                   -- 未払金保留
       ,0                                                                   -- 債権債務判定
       ,0                                                                   -- 利用額･未払金
       ,0                                                                   -- ｷｬﾝｾﾙ金額
       ,0                                                                   -- 無保証立替金戻し金額
       ,0                                                                   -- 決済手数料
       ,0                                                                   -- 請求手数料
       ,0                                                                   -- 月額固定費
       ,0                                                                   -- 同梱月額固定費
       ,0                                                                   -- API月額固定費
       ,0                                                                   -- 与信結果通知ｻｰﾋﾞｽ月額固定費
       ,0                                                                   -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
       ,0                                                                   -- 印紙代件数
       ,0                                                                   -- 印紙代
       ,0                                                                   -- 振込手数料件数
       ,0                                                                   -- 振込手数料
       ,0                                                                   -- 調整金額件数
       ,0                                                                   -- 調整金額
       ,1                                                                   -- 加盟店への返金加算件数
       ,pctmp.RepayTotal                                                    -- 加盟店への返金加算
       ,0                                                                   -- 前精算時未収金相殺
       ,0                                                                   -- 前精算時未払金保留額
       ,NOW()                                                               -- 登録日時
       ,pi_user_id                                                          -- 登録者
       ,NOW()                                                               -- 更新日時
       ,pi_user_id                                                          -- 更新者
       ,1                                                                   -- 有効ﾌﾗｸﾞ
    FROM
          T_OemClaimed oc
    INNER JOIN AT_OemClaimed aoc
    ON    oc.OemClaimedSeq    = aoc.OemClaimedSeq
    INNER JOIN M_Code c
    ON    c.CodeId            = 160
    AND   c.KeyCode           = IFNULL(oc.OemId, 0)
    INNER JOIN (SELECT
                    pc.OemClaimedSeq                        -- OEM請求ﾃﾞｰﾀｼｰｹﾝｽ
                   ,SUM(cc.ReceiptAmountTotal) RepayTotal   -- 返金－顧客－入金額
                FROM
                      T_PayingControl pc
                INNER JOIN T_Cancel tc
                ON    pc.Seq            = tc.PayingControlSeq
                AND   tc.ValidFlg       = 1
                AND   tc.CancelPhase    IN (3, 4)
                INNER JOIN T_ClaimControl cc
                ON    cc.OrderSeq       = tc.OrderSeq
                GROUP BY pc.OemClaimedSeq
               ) pctmp
    ON    oc.OemClaimedSeq    = pctmp.OemClaimedSeq
    WHERE aoc.DailySummaryFlg = 0
    AND   oc.ProcessDate     <= v_BusinessDate
    AND   oc.ValidFlg         = 1
    AND   c.Class1           <> 0
    AND   pctmp.RepayTotal   <> 0
    ;

    -- ------------------------------
    -- ⑭ OEM精算日計ﾜｰｸをｻﾏﾘｰしてOEM分の精算日計を出力する
    -- ------------------------------
    INSERT INTO AT_PayOff_DailyAccount(
        DailyMonthlyFlg
       ,ProcessingDate
       ,OemId
       ,OemNameKj
       ,EnterpriseId
       ,EnterpriseNameKj
       ,MerchantNumber
       ,AdvancesDate
       ,AdvancesAmount
       ,EnterpriseAccountsDue
       ,AccountsPayablePending
       ,ClaimAndObligationsDecision
       ,UseAmount
       ,CancelAmount
       ,UseSettlementBackOffse
       ,AccountsPayableTotal
       ,SettlementFee
       ,ClaimFee
       ,MonthlyFee
       ,IncludeMonthlyFee
       ,ApiMonthlyFee
       ,CreditNoticeMonthlyFee
       ,NextClaimCreditNoticeMonthlyFee
       ,AccountsReceivableTotal
       ,StampFeeCount
       ,StampFee
       ,TransferCommissionCount
       ,TransferCommission
       ,AdjustmentAmountCount
       ,AdjustmentAmount
       ,EnterpriseRefundCount
       ,EnterpriseRefund
       ,AccountsDueOffsetAmount
       ,AccountsPayablePendingAmount
       ,AdvancesFixedDate
       ,RegistDate
       ,RegistId
       ,UpdateDate
       ,UpdateId
       ,ValidFlg
       )
    SELECT 0
         , v_BusinessDate
         , OemId
         , OemNameKj
         , EnterpriseId
         , EnterpriseNameKj
         , CountEnterprise
         , SettlePlanDate
         , CASE WHEN (UseAmount + CancelAmount + OemSettlementFee + OemClaimFee + OemMonthlyFee + StampFee + OemTransferCommission + OemAdjustmentAmount + EnterpriseRefund + AccountsDueOffsetAmount + AccountsPayablePendingAmount) > 0 THEN (UseAmount + CancelAmount + OemSettlementFee + OemClaimFee + OemMonthlyFee + StampFee + OemTransferCommission + OemAdjustmentAmount + EnterpriseRefund + AccountsDueOffsetAmount + AccountsPayablePendingAmount)
                ELSE 0
           END - 
           CASE WHEN 0 < (UseAmount + CancelAmount + OemSettlementFee + OemClaimFee + OemMonthlyFee + StampFee + OemTransferCommission + OemAdjustmentAmount + EnterpriseRefund + AccountsDueOffsetAmount + AccountsPayablePendingAmount) AND (UseAmount + CancelAmount + OemSettlementFee + OemClaimFee + OemMonthlyFee + StampFee + OemTransferCommission + OemAdjustmentAmount + EnterpriseRefund + AccountsDueOffsetAmount + AccountsPayablePendingAmount) < 300 THEN (UseAmount + CancelAmount + OemSettlementFee + OemClaimFee + OemMonthlyFee + StampFee + OemTransferCommission + OemAdjustmentAmount + EnterpriseRefund + AccountsDueOffsetAmount + AccountsPayablePendingAmount)
                ELSE 0
           END AS OemAdvancesAmount -- OEM立替実行金額
         , CASE WHEN (UseAmount + CancelAmount + OemSettlementFee + OemClaimFee + OemMonthlyFee + StampFee + OemTransferCommission + OemAdjustmentAmount + EnterpriseRefund + AccountsDueOffsetAmount + AccountsPayablePendingAmount) < 0 THEN (UseAmount + CancelAmount + OemSettlementFee + OemClaimFee + OemMonthlyFee + StampFee + OemTransferCommission + OemAdjustmentAmount + EnterpriseRefund + AccountsDueOffsetAmount + AccountsPayablePendingAmount) - OemTransferCommission
                ELSE 0
           END AS OemClaimAmount -- OEM未収金
         , CASE WHEN 0 < (UseAmount + CancelAmount + OemSettlementFee + OemClaimFee + OemMonthlyFee + StampFee + OemTransferCommission + OemAdjustmentAmount + EnterpriseRefund + AccountsDueOffsetAmount + AccountsPayablePendingAmount) AND (UseAmount + CancelAmount + OemSettlementFee + OemClaimFee + OemMonthlyFee + StampFee + OemTransferCommission + OemAdjustmentAmount + EnterpriseRefund + AccountsDueOffsetAmount + AccountsPayablePendingAmount) < 300 THEN (UseAmount + CancelAmount + OemSettlementFee + OemClaimFee + OemMonthlyFee + StampFee + OemTransferCommission + OemAdjustmentAmount + EnterpriseRefund + AccountsDueOffsetAmount + AccountsPayablePendingAmount)
                ELSE 0
           END AS OemAccountsPayablePending -- OEM未払金保留
         , (UseAmount + CancelAmount + OemSettlementFee + OemClaimFee + OemMonthlyFee + StampFee + OemTransferCommission + OemAdjustmentAmount + EnterpriseRefund + AccountsDueOffsetAmount + AccountsPayablePendingAmount) AS ClaimAndObligationsDecision -- 債権債務判定
         , UseAmount
         , CancelAmount
         , UseSettlementBackOffse
         , (UseAmount + CancelAmount) AS OemAccountsPayableTotal -- OEM未払金計
         , OemSettlementFee
         , OemClaimFee
         , OemMonthlyFee
         , OemIncludeMonthlyFee
         , OemApiMonthlyFee
         , OemCreditNoticeMonthlyFee
         , OemNCreditNoticeMonthlyFee
         , OemSettlementFee + OemClaimFee + OemMonthlyFee AS OemAccountsReceivableTotal -- OEM売掛金計
         , 0 -- 印紙代件数
         , StampFee
         , 0 -- 振込手数料件数
         , OemTransferCommission
         , 0 -- 調整金額件数
         , OemAdjustmentAmount
         , 0 -- 加盟店への返金加算件数
         , EnterpriseRefund
         , AccountsDueOffsetAmount
         , AccountsPayablePendingAmount
         , AdvancesFixedDate
         , NOW()
         , pi_user_id
         , NOW()
         , pi_user_id
         , 1
    FROM (
    SELECT c.Class3 AS OemId -- OEMID
         , c.Class2 AS OemNameKj -- OEM先名
         , oec.EnterpriseId AS EnterpriseId -- 加盟店ID
         , e.EnterpriseNameKj AS EnterpriseNameKj -- 加盟店名
         , 1 AS CountEnterprise -- 加盟店件数
         , oc.ExecDate AS SettlePlanDate  -- 立替日
         , oec.UseAmount AS UseAmount -- 利用額・OEM未払金
         , IFNULL((SELECT SUM(o.UseAmount - osf.SettlementFee - ocf.ClaimFee)
              FROM T_Order o 
                   INNER JOIN T_Cancel can ON (o.OrderSeq = can.OrderSeq)
                   INNER JOIN T_PayingControl pc ON (can.PayingControlSeq = pc.Seq)
                   INNER JOIN T_OemSettlementFee osf ON (osf.OrderSeq = o.OrderSeq) -- 厳密にato_cancel_meisaiとあわせる為、内部結合を実施
                   INNER JOIN T_OemClaimFee ocf ON (ocf.OrderSeq = o.OrderSeq)      -- 厳密にato_cancel_meisaiとあわせる為、内部結合を実施
                   INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq)       -- 厳密にato_cancel_meisaiとあわせる為、内部結合を実施
             WHERE pc.OemClaimedSeq = oec.OemClaimedSeq
               AND pc.EnterpriseId = oec.EnterpriseId
               AND can.CancelPhase IN (2,3)
            ), 0) * -1 AS CancelAmount -- ｷｬﾝｾﾙ金額
         , NULL AS UseSettlementBackOffse -- 無保証立替金戻し金額(0固定でよい)
         , ((oec.CB_SettlementFee + oec.OM_SettlementFee) - (oec.OM_SettlementFee + oec.OM_ClaimFeeBS + oec.OM_ClaimFeeDK)) * -1 AS OemSettlementFee -- 決済手数料
         , (oec.CB_ClaimFeeBS + oec.CB_ClaimFeeDK + oec.OM_ClaimFeeBS + oec.OM_ClaimFeeDK) * -1 AS OemClaimFee -- 請求手数料
         , (oec.PC_MonthlyFee - oec.OM_EntMonthlyFee) * -1 AS OemMonthlyFee -- 月額固定費
         , NULL AS OemIncludeMonthlyFee -- 同梱月額固定費
         , NULL AS OemApiMonthlyFee -- API月額固定費
         , NULL AS OemCreditNoticeMonthlyFee -- 与信結果通知ｻｰﾋﾞｽ月額固定費
         , NULL AS OemNCreditNoticeMonthlyFee -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
         , oec.PC_StampFeeTotal * -1 AS StampFee -- 印紙代
         , CASE WHEN oem.PayingMethod = 0 THEN oec.PC_TransferCommission * -1
                ELSE 0
           END AS OemTransferCommission-- 振込手数料
         , oec.PC_AdjustmentAmount AS OemAdjustmentAmount-- 調整金額
         , IFNULL((SELECT SUM(o.UseAmount - osf.SettlementFee - ocf.ClaimFee)
              FROM T_Order o 
                   INNER JOIN T_Cancel can ON (o.OrderSeq = can.OrderSeq)
                   INNER JOIN T_PayingControl pc ON (can.PayingControlSeq = pc.Seq)
                   INNER JOIN T_OemSettlementFee osf ON (osf.OrderSeq = o.OrderSeq) -- 厳密にato_cancel_meisaiとあわせる為、内部結合を実施
                   INNER JOIN T_OemClaimFee ocf ON (ocf.OrderSeq = o.OrderSeq)      -- 厳密にato_cancel_meisaiとあわせる為、内部結合を実施
                   INNER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq)       -- 厳密にato_cancel_meisaiとあわせる為、内部結合を実施
             WHERE pc.OemClaimedSeq = oec.OemClaimedSeq
               AND pc.EnterpriseId = oec.EnterpriseId
               AND can.CancelPhase IN (3)
            ), 0) AS EnterpriseRefund -- 加盟店への返金加算
         , 0 AS AccountsDueOffsetAmount -- 前精算時未収金相殺
         , IFNULL((SELECT AccountsPayablePending FROM AT_PayOff_DailyAccount WHERE EnterpriseId = oec.EnterpriseId ORDER BY Seq DESC LIMIT 1), 0) AS AccountsPayablePendingAmount -- 前精算時未払金保留額
         , NULL AS AdvancesFixedDate -- 立替確定日
    FROM T_OemClaimed oc
         INNER JOIN AT_OemClaimed aoc
                 ON oc.OemClaimedSeq = aoc.OemClaimedSeq
         INNER JOIN T_OemEnterpriseClaimed oec
                 ON oc.OemClaimedSeq = oec.OemClaimedSeq
         INNER JOIN M_Code c
                 ON c.CodeId = 160
                AND c.KeyCode = IFNULL(oc.OemId, 0)
         INNER JOIN T_Enterprise e
                 ON oec.EnterpriseId = e.EnterpriseId
         INNER JOIN T_Oem oem
                 ON oc.OemId = oem.OemId
    WHERE aoc.DailySummaryFlg = 0
      AND oc.ExecDate <= v_BusinessDate
      AND c.Class1 <> 0
      ) tmp
    ;

END
$$

DELIMITER ;
