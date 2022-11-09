DROP PROCEDURE IF EXISTS P_Treas_DailyStatisticsTable_Month;

DELIMITER $$

CREATE PROCEDURE P_Treas_DailyStatisticsTable_Month ( IN pi_user_id INT )
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名       ：  P_Treas_DailyStatisticsTable_Month
 *
 * 概要             ：  直営日次統計表（月次）
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
    -- 4.1.直営日次統計表ﾜｰｸをﾄﾗﾝｹｰﾄする
    -- ------------------------------
    TRUNCATE AW_DailyStatisticsTable;

    -- ------------------------------
    -- 4.3.会計月 = 日別直営日次統計表.集計月のﾃﾞｰﾀを集計出力し月次の直営日次統計表ﾜｰｸを出力する
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
        ,D_ReClaimFeeCount                                          -- 当日再請求手数料件数
        ,D_ReClaimFeeAmount                                         -- 当日再請求手数料額
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
        ,D_AddClaimFee                                              -- 当日再請求手数料
        ,D_AddClaimFeeTax                                           -- 当日再請求手数料消費税
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
        ,D_AddClaimFeeOther                                         -- 当日手数料明細再請求手数料
        ,D_AddClaimFeeTaxOther                                      -- 当日手数料明細再請求手数料消費税
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
        ,M_ReClaimFeeCount                                          -- 当月再請求手数料件数
        ,M_ReClaimFeeAmount                                         -- 当月再請求手数料額
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
        ,M_AddClaimFee                                              -- 当月再請求手数料
        ,M_AddClaimFeeTax                                           -- 当月再請求手数料消費税
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
        ,M_AddClaimFeeOther                                         -- 当月手数料明細再請求手数料
        ,M_AddClaimFeeTaxOther                                      -- 当月手数料明細再請求手数料消費税
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
        ,null                                                       -- 当日再請求手数料件数
        ,null                                                       -- 当日再請求手数料額
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
        ,null                                                       -- 当日再請求手数料
        ,null                                                       -- 当日再請求手数料消費税
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
        ,null                                                       -- 当日手数料明細再請求手数料
        ,null                                                       -- 当日手数料明細再請求手数料消費税
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
        ,SUM( IFNULL ( D_ReClaimFeeCount, 0 ) )                     -- 当月再請求手数料件数
        ,SUM( IFNULL ( D_ReClaimFeeAmount, 0 ) )                    -- 当月再請求手数料額
        ,SUM( IFNULL ( D_DamageCount, 0 ) )                         -- 当月遅延損害金件数
        ,SUM( IFNULL ( D_DamageAmount, 0 ) )                        -- 当月遅延損害金額
        ,SUM( IFNULL ( D_ReceiptCount, 0 ) )                        -- 当月入金件数
        ,SUM( IFNULL ( D_ReceiptAmount, 0 ) )                       -- 当月入金額
        ,SUM( IFNULL ( D_RepayCount, 0 ) )                          -- 当月返金件数
        ,SUM( IFNULL ( D_RepayAmount, 0 ) )                         -- 当月返金額
        ,SUM( IFNULL ( D_BadDebtCount, 0 ) )                        -- 当月貸倒件数
        ,SUM( IFNULL ( D_BadDebtAmount, 0 ) )                       -- 当月貸倒金額
        ,SUM( IFNULL ( D_OtherPaymentCount, 0 ) )                   -- 当月その他件数
        ,SUM( IFNULL ( D_OtherPaymentAmount, 0 ) )                  -- 当月その他金額
        ,null                                                       -- 当月時点未収金残高
        ,null                                                       -- 消費者未収金明細残高
        ,null                                                       -- 当月差額
        ,SUM( IFNULL ( D_SettlementFee, 0 ) )                       -- 当月決済手数料
        ,SUM( IFNULL ( D_ClaimFee, 0 ) )                            -- 当月請求手数料
        ,SUM( IFNULL ( D_ClaimFeeTax, 0 ) )                         -- 当月請求手数料消費税
        ,SUM( IFNULL ( D_MonthlyFee, 0 ) )                          -- 当月月額固定費
        ,SUM( IFNULL ( D_MonthlyFeeTax, 0 ) )                       -- 当月月額固定費消費税
        ,SUM( IFNULL ( D_IncludeMonthlyFee, 0 ) )                   -- 当月同梱月額固定費
        ,SUM( IFNULL ( D_IncludeMonthlyFeeTax, 0 ) )                -- 当月同梱月額固定費消費税
        ,SUM( IFNULL ( D_ApiMonthlyFee, 0 ) )                       -- 当月API月額固定費
        ,SUM( IFNULL ( D_ApiMonthlyFeeTax, 0 ) )                    -- 当月API月額固定費消費税
        ,SUM( IFNULL ( D_CreditNoticeMonthlyFee, 0 ) )              -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM( IFNULL ( D_CreditNoticeMonthlyFeeTax, 0 ) )           -- 当月与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM( IFNULL ( D_NCreditNoticeMonthlyFee, 0 ) )             -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,SUM( IFNULL ( D_NCreditNoticeMonthlyFeeTax, 0 ) )          -- 当月次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,SUM( IFNULL ( D_ReserveMonthlyFee, 0 ) )                   -- 当月月額固定費予備
        ,SUM( IFNULL ( D_ReserveMonthlyFeeTax, 0 ) )                -- 当月月額固定費予備消費税
        ,SUM( IFNULL ( D_AddClaimFee, 0 ) )                         -- 当月再請求手数料
        ,SUM( IFNULL ( D_AddClaimFeeTax, 0 ) )                      -- 当月再請求手数料消費税
        ,SUM( IFNULL ( D_DamageInterestAmount, 0 ) )                -- 当月遅延損害金
        ,SUM( IFNULL ( D_CanSettlementFee, 0 ) )                    -- 当月ｷｬﾝｾﾙ決済手数料
        ,SUM( IFNULL ( D_CanClaimFee, 0 ) )                         -- 当月ｷｬﾝｾﾙ請求手数料
        ,SUM( IFNULL ( D_CanClaimFeeTax, 0 ) )                      -- 当月ｷｬﾝｾﾙ請求手数料消費税
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
        ,null                                                       -- 当月手数料明細再請求手数料
        ,null                                                       -- 当月手数料明細再請求手数料消費税
        ,null                                                       -- 当月手数料明細遅延損害金
        ,NOW()                                                      -- 登録日時
        ,pi_user_id                                                 -- 登録者
        ,NOW()                                                      -- 更新日時
        ,pi_user_id                                                 -- 更新者
        ,1                                                          -- 有効ﾌﾗｸﾞ
    FROM    AT_Daily2StatisticsTable
    WHERE   AccountDate = v_AccountingMonth
    GROUP BY AccountDate
            ,EnterpriseId;

    -- ------------------------------
    -- 4.5.加盟店別に会計月の前月 = 直営日次統計表.会計月､且つ､MAX(直営日次統計表.集計日)【前月末】の
    --     ﾃﾞｰﾀを出力し月次用の直営日次統計表ﾜｰｸを出力する
    --       → 月初未収金残高を取得
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
        ,D_ReClaimFeeCount                                          -- 当日再請求手数料件数
        ,D_ReClaimFeeAmount                                         -- 当日再請求手数料額
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
        ,D_AddClaimFee                                              -- 当日再請求手数料
        ,D_AddClaimFeeTax                                           -- 当日再請求手数料消費税
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
        ,D_AddClaimFeeOther                                         -- 当日手数料明細再請求手数料
        ,D_AddClaimFeeTaxOther                                      -- 当日手数料明細再請求手数料消費税
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
        ,M_ReClaimFeeCount                                          -- 当月再請求手数料件数
        ,M_ReClaimFeeAmount                                         -- 当月再請求手数料額
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
        ,M_AddClaimFee                                              -- 当月再請求手数料
        ,M_AddClaimFeeTax                                           -- 当月再請求手数料消費税
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
        ,M_AddClaimFeeOther                                         -- 当月手数料明細再請求手数料
        ,M_AddClaimFeeTaxOther                                      -- 当月手数料明細再請求手数料消費税
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
        ,null                                                       -- 前日時点未収金残高
        ,null                                                       -- 当日立替件数
        ,null                                                       -- 当日立替金額
        ,null                                                       -- 当日ｷｬﾝｾﾙ件数
        ,null                                                       -- 当日ｷｬﾝｾﾙ立替金額
        ,null                                                       -- 当日立替金戻し件数
        ,null                                                       -- 当日立替金戻し金額
        ,null                                                       -- 当日OEM移管件数
        ,null                                                       -- 当日OEM移管金額
        ,null                                                       -- 当日再請求手数料件数
        ,null                                                       -- 当日再請求手数料額
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
        ,null                                                       -- 当日再請求手数料
        ,null                                                       -- 当日再請求手数料消費税
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
        ,null                                                       -- 当日手数料明細再請求手数料
        ,null                                                       -- 当日手数料明細再請求手数料消費税
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
        ,null                                                       -- 当月再請求手数料件数
        ,null                                                       -- 当月再請求手数料額
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
        ,null                                                       -- 当月再請求手数料
        ,null                                                       -- 当月再請求手数料消費税
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
        ,null                                                       -- 当月手数料明細再請求手数料
        ,null                                                       -- 当月手数料明細再請求手数料消費税
        ,null                                                       -- 当月手数料明細遅延損害金
        ,NOW()                                                      -- 登録日時
        ,pi_user_id                                                 -- 登録者
        ,NOW()                                                      -- 更新日時
        ,pi_user_id                                                 -- 更新者
        ,1                                                          -- 有効ﾌﾗｸﾞ
    FROM  AT_DailyStatisticsTable dst
    INNER JOIN ( SELECT EnterpriseId, MAX( ProcessingDate ) ProcessingDate FROM AT_DailyStatisticsTable
                 WHERE AccountDate         = DATE_SUB( v_AccountingMonth, INTERVAL 1 MONTH )
                 AND   DailyMonthlyFlg     = 1                      -- 1:月次
                 GROUP BY EnterpriseId ) dstw
    ON    dst.EnterpriseId = dstw.EnterpriseId
    AND   dst.ProcessingDate = dstw.ProcessingDate
    AND   dst.AccountDate = DATE_SUB( v_AccountingMonth, INTERVAL 1 MONTH )
    AND   dst.DailyMonthlyFlg = 1;

    -- ------------------------------
    -- 4.6.直営日次統計表ﾜｰｸのﾃﾞｰﾀを加盟店別に集計出力し月次用の直営日次統計表を出力する
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
        ,D_ReClaimFeeCount                                          -- 当日再請求手数料件数
        ,D_ReClaimFeeAmount                                         -- 当日再請求手数料額
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
        ,D_AddClaimFee                                              -- 当日再請求手数料
        ,D_AddClaimFeeTax                                           -- 当日再請求手数料消費税
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
        ,D_AddClaimFeeTotal                                         -- 当日合計再請求手数料
        ,D_AddClaimFeeTaxTotal                                      -- 当日合計再請求手数料消費税
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
        ,D_AddClaimFeeOther                                         -- 当日手数料明細再請求手数料
        ,D_AddClaimFeeTaxOther                                      -- 当日手数料明細再請求手数料消費税
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
        ,D_AddClaimFeeDiff                                          -- 当日差額再請求手数料
        ,D_AddClaimFeeTaxDiff                                       -- 当日差額再請求手数料消費税
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
        ,M_ReClaimFeeCount                                          -- 当月再請求手数料件数
        ,M_ReClaimFeeAmount                                         -- 当月再請求手数料額
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
        ,M_AddClaimFee                                              -- 当月再請求手数料
        ,M_AddClaimFeeTax                                           -- 当月再請求手数料消費税
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
        ,M_AddClaimFeeTotal                                         -- 当月合計再請求手数料
        ,M_AddClaimFeeTaxTotal                                      -- 当月合計再請求手数料消費税
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
        ,M_AddClaimFeeOther                                         -- 当月手数料明細再請求手数料
        ,M_AddClaimFeeTaxOther                                      -- 当月手数料明細再請求手数料消費税
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
        ,M_AddClaimFeeDiff                                          -- 当月差額再請求手数料
        ,M_AddClaimFeeTaxDiff                                       -- 当月差額再請求手数料消費税
        ,M_DamageInterestAmountDiff                                 -- 当月差額遅延損害金
        ,RegistDate                                                 -- 登録日時
        ,RegistId                                                   -- 登録者
        ,UpdateDate                                                 -- 更新日時
        ,UpdateId                                                   -- 更新者
        ,ValidFlg                                                   -- 有効ﾌﾗｸﾞ
        )
    SELECT
         1                                                          -- 日次･月次区分
        ,v_BusinessDate                                             -- 集計日
        ,v_AccountingMonth                                          -- 会計月
        ,aw_dst.EnterpriseId                                        -- 加盟店ID
        ,CASE WHEN aw_dst.EnterpriseId = v_EnterpriseId
            THEN 'OEM移管分'
            ELSE ent.EnterpriseNameKj
         END                                                        -- 加盟店名
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
        ,null                                                       -- 当日再請求手数料
        ,null                                                       -- 当日再請求手数料消費税
        ,null                                                       -- 当日遅延損害金
        ,null                                                       -- 当日ｷｬﾝｾﾙ決済手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙ請求手数料
        ,null                                                       -- 当日ｷｬﾝｾﾙ請求手数料消費税
        ,null                                                       -- 当日合計決済手数料
        ,null                                                       -- 当日合計請求手数料
        ,null                                                       -- 当日合計請求手数料消費税
        ,null                                                       -- 当日合計月額固定費
        ,null                                                       -- 当日合計月額固定費消費税
        ,null                                                       -- 当日合計同梱月額固定費
        ,null                                                       -- 当日合計同梱月額固定費消費税
        ,null                                                       -- 当日合計API月額固定費
        ,null                                                       -- 当日合計API月額固定費消費税
        ,null                                                       -- 当日合計与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日合計与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日合計次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日合計次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日合計月額固定費予備
        ,null                                                       -- 当日合計月額固定費予備消費税
        ,null                                                       -- 当日合計再請求手数料
        ,null                                                       -- 当日合計再請求手数料消費税
        ,null                                                       -- 当日合計遅延損害金
        ,null                                                       -- 当日総合計
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
        ,null                                                       -- 当日手数料明細再請求手数料
        ,null                                                       -- 当日手数料明細再請求手数料消費税
        ,null                                                       -- 当日手数料明細遅延損害金
        ,null                                                       -- 当日差額決済手数料
        ,null                                                       -- 当日差額請求手数料
        ,null                                                       -- 当日差額請求手数料消費税
        ,null                                                       -- 当日差額月額固定費
        ,null                                                       -- 当日差額月額固定費消費税
        ,null                                                       -- 当日差額同梱月額固定費
        ,null                                                       -- 当日差額同梱月額固定費消費税
        ,null                                                       -- 当日差額API月額固定費
        ,null                                                       -- 当日差額API月額固定費消費税
        ,null                                                       -- 当日差額与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日差額与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日差額次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,null                                                       -- 当日差額次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,null                                                       -- 当日差額月額固定費予備
        ,null                                                       -- 当日差額月額固定費予備消費税
        ,null                                                       -- 当日差額再請求手数料
        ,null                                                       -- 当日差額再請求手数料消費税
        ,null                                                       -- 当日差額遅延損害金
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
        ,CASE WHEN aw_dst.EnterpriseId = v_EnterpriseId
            THEN IFNULL( at_cad2.ReceivablesRemainingAmount, 0)
            ELSE IFNULL( at_cad1.ReceivablesRemainingAmount, 0)
         END                                                        -- 消費者未収金明細残高
        ,CASE WHEN aw_dst.EnterpriseId = v_EnterpriseId
            THEN aw_dst.M_ChargeAmount + aw_dst.M_CancelAmount +
                 aw_dst.M_SettlementBackAmount + aw_dst.M_TransferAmount +
                 aw_dst.M_ReceiptAmount + aw_dst.M_RepayAmount +
                 aw_dst.M_BadDebtAmount + aw_dst.M_OtherPaymentAmount -
                 IFNULL( at_cad2.ReceivablesRemainingAmount, 0)
            ELSE aw_dst.M_ChargeAmount + aw_dst.M_CancelAmount +
                 aw_dst.M_SettlementBackAmount + aw_dst.M_TransferAmount +
                 aw_dst.M_ReceiptAmount + aw_dst.M_RepayAmount +
                 aw_dst.M_BadDebtAmount + aw_dst.M_OtherPaymentAmount -
                 IFNULL(at_cad1.ReceivablesRemainingAmount, 0)
         END                                                        -- 当月差額
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
        ,aw_dst.M_AddClaimFee                                       -- 当月再請求手数料
        ,aw_dst.M_AddClaimFeeTax                                    -- 当月再請求手数料消費税
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
        ,aw_dst.M_AddClaimFee                                       -- 当月合計再請求手数料
        ,aw_dst.M_AddClaimFeeTax                                    -- 当月合計再請求手数料消費税
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
        ,IFNULL( at_dsd.SettlementFee, 0)                           -- 当月売上明細決済手数料
        ,IFNULL( at_dsd.ClaimFee, 0)                                -- 当月売上明細請求手数料
        ,IFNULL( at_dsd.ClaimFeeTax, 0)                             -- 当月売上明細請求手数料消費税
        ,IFNULL( at_dsd.MonthlyFee, 0)                              -- 当月売上明細月額固定費
        ,IFNULL( at_dsd.MonthlyFeeTax, 0)                           -- 当月売上明細月額固定費消費税
        ,IFNULL( at_dsd.IncludeMonthlyFee, 0)                       -- 当月売上明細同梱月額固定費
        ,IFNULL( at_dsd.IncludeMonthlyFeeTax , 0)                   -- 当月売上明細同梱月額固定費消費税
        ,IFNULL( at_dsd.ApiMonthlyFee, 0)                           -- 当月売上明細API月額固定費
        ,IFNULL( at_dsd.ApiMonthlyFeeTax, 0)                        -- 当月売上明細API月額固定費消費税
        ,IFNULL( at_dsd.CreditNoticeMonthlyFee, 0)                  -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費
        ,IFNULL( at_dsd.CreditNoticeMonthlyFeeTax, 0)               -- 当月売上明細与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,IFNULL( at_dsd.NCreditNoticeMonthlyFee, 0)                 -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,IFNULL( at_dsd.NCreditNoticeMonthlyFeeTax, 0)              -- 当月売上明細次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,IFNULL( at_dsd.ReserveMonthlyFee, 0)                       -- 当月売上明細月額固定費予備
        ,IFNULL( at_dsd.ReserveMonthlyFeeTax, 0)                    -- 当月売上明細月額固定費予備消費税
        ,IFNULL( at_rfs.Clm_L_ClaimFee, 0)                          -- 当月手数料明細再請求手数料
        ,IFNULL( at_rfs.Clm_L_ClaimFeeTax, 0)                       -- 当月手数料明細再請求手数料消費税
        ,IFNULL( at_rfs.Clm_L_DamageInterestAmount, 0)              -- 当月手数料明細遅延損害金
        ,aw_dst.M_SettlementFee + aw_dst.M_CanSettlementFee - IFNULL( at_dsd.SettlementFee, 0)      -- 当月差額決済手数料
        ,aw_dst.M_ClaimFee + aw_dst.M_CanClaimFee - IFNULL( at_dsd.ClaimFee, 0)                     -- 当月差額請求手数料
        ,aw_dst.M_ClaimFeeTax + aw_dst.M_CanClaimFeeTax - IFNULL( at_dsd.ClaimFeeTax, 0)            -- 当月差額請求手数料消費税
        ,aw_dst.M_MonthlyFee - IFNULL( at_dsd.MonthlyFee, 0)                                        -- 当月差額月額固定費
        ,aw_dst.M_MonthlyFeeTax - IFNULL( at_dsd.MonthlyFeeTax, 0)                                  -- 当月差額月額固定費消費税
        ,aw_dst.M_IncludeMonthlyFee - IFNULL( at_dsd.IncludeMonthlyFee, 0)                          -- 当月差額同梱月額固定費
        ,aw_dst.M_IncludeMonthlyFeeTax - IFNULL( at_dsd.IncludeMonthlyFeeTax, 0)                    -- 当月差額同梱月額固定費消費税
        ,aw_dst.M_ApiMonthlyFee - IFNULL( at_dsd.ApiMonthlyFee, 0)                                  -- 当月差額API月額固定費
        ,aw_dst.M_ApiMonthlyFeeTax - IFNULL( at_dsd.ApiMonthlyFeeTax, 0)                            -- 当月差額API月額固定費消費税
        ,aw_dst.M_CreditNoticeMonthlyFee - IFNULL( at_dsd.CreditNoticeMonthlyFee, 0)                -- 当月差額与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.M_CreditNoticeMonthlyFeeTax - IFNULL( at_dsd.CreditNoticeMonthlyFeeTax, 0)          -- 当月差額与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.M_NCreditNoticeMonthlyFee - IFNULL( at_dsd.NCreditNoticeMonthlyFee, 0)              -- 当月差額次回請求与信結果通知ｻｰﾋﾞｽ月額固定費
        ,aw_dst.M_NCreditNoticeMonthlyFeeTax - IFNULL( at_dsd.NCreditNoticeMonthlyFeeTax, 0)        -- 当月差額次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,aw_dst.M_ReserveMonthlyFee - IFNULL( at_dsd.ReserveMonthlyFee, 0)                          -- 当月差額月額固定費予備
        ,aw_dst.M_ReserveMonthlyFeeTax - IFNULL( at_dsd.ReserveMonthlyFeeTax, 0)                    -- 当月差額月額固定費予備消費税
        ,aw_dst.M_AddClaimFee - IFNULL(at_rfs.Clm_L_ClaimFee, 0)                                    -- 当月差額再請求手数料
        ,aw_dst.M_AddClaimFeeTax - IFNULL(at_rfs.Clm_L_ClaimFeeTax, 0)                              -- 当月差額再請求手数料消費税
        ,aw_dst.M_DamageInterestAmount - IFNULL(at_rfs.Clm_L_DamageInterestAmount, 0)               -- 当月差額遅延損害金
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
                WHERE AccountDate   = v_AccountingMonth
                AND   SalesDefiniteConditions in ('着荷確認日', '入金日', '役務提供日', 'キャンセル日', 'キャンセル確定日', '月額固定費')
                AND      ValidFlg   = 1
                AND      OemId      = 0
                GROUP BY EnterpriseId ) AS at_dsd
    ON  at_dsd.AT_DSD_EnterpriseId = aw_dst.EnterpriseId
    LEFT JOIN( SELECT
                     EnterpriseId                                           AS AT_RFS_EnterpriseId
                    ,SUM( IFNULL( Clm_L_ClaimFee, 0 ) )                     AS Clm_L_ClaimFee
                    ,SUM( IFNULL( Clm_L_ClaimFeeTax, 0 ) )                  AS Clm_L_ClaimFeeTax
                    ,SUM( IFNULL( Clm_L_DamageInterestAmount, 0 ) )         AS Clm_L_DamageInterestAmount
                FROM AT_ReissueFeeSpecification
                WHERE AccountDate   = v_AccountingMonth
                AND   ValidFlg      = 1
                AND   OemId         = 0
                GROUP BY EnterpriseId ) AS at_rfs
    ON  at_rfs.AT_RFS_EnterpriseId = aw_dst.EnterpriseId
    LEFT JOIN( SELECT
                     EnterpriseId                                           AS AT_CAD1_EnterpriseId
                    ,SUM( IFNULL( ReceivablesRemainingAmount, 0 ) )         AS ReceivablesRemainingAmount
                FROM AT_Consumer_AccountsDue
                WHERE AccountDate   =  v_AccountingMonth
                AND   OemId         =  0
                AND   ValidFlg      =  1
                GROUP BY EnterpriseId ) AS at_cad1
    ON  at_cad1.AT_CAD1_EnterpriseId =  aw_dst.EnterpriseId
    AND at_cad1.AT_CAD1_EnterpriseId <> v_EnterpriseId
    LEFT JOIN( SELECT
                     v_EnterpriseId                                         AS AT_CAD2_EnterpriseId
                    ,SUM( IFNULL( ReceivablesRemainingAmount, 0 ) )         AS ReceivablesRemainingAmount
                FROM AT_Consumer_AccountsDue
                WHERE AccountDate   =  v_AccountingMonth
                AND   OemId         <> 0
                AND   ValidFlg      =  1 ) AS at_cad2
    ON  at_cad2.AT_CAD2_EnterpriseId =  aw_dst.EnterpriseId
    AND at_cad2.AT_CAD2_EnterpriseId =  v_EnterpriseId
    LEFT JOIN  T_Enterprise ent
    ON  ent.EnterpriseId             =  aw_dst.EnterpriseId;

    -- ------------------------------
    -- 4.7.会計月の翌月で作成済の直営日次統計表の残高情報を月次確定ﾃﾞｰﾀの残高を反映させる
    -- ------------------------------
    BEGIN

        -- ｶｰｿﾙ用
        DECLARE v_Seq                           BIGINT;             -- ｼｰｹﾝｽ
        DECLARE v_EnterpriseId                  BIGINT;             -- 加盟店ID

        DECLARE v_D_AccountsReceivableBalance   BIGINT;             -- 当日時点未収金残高
        DECLARE v_M_AccountsReceivableBalance   BIGINT;             -- 当月時点未収金残高

        -- その他
        DECLARE v_done                          INT DEFAULT 0;

        -- ｶｰｿﾙ宣言
        DECLARE cur1 CURSOR FOR
            SELECT
                 Seq
                ,EnterpriseId
            FROM AT_DailyStatisticsTable
            WHERE AccountDate = DATE_ADD( v_AccountingMonth, INTERVAL 1 MONTH )
            AND   ValidFlg    = 1;

        -- ﾊﾝﾄﾞﾗｰ
        DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET v_done = 1;   -- cursor読み取り終了

        -- ｶｰｿﾙｵｰﾌﾟﾝ
        OPEN cur1;

        read_loop1: LOOP

            -- ｶｰｿﾙﾌｪｯﾁ
            FETCH cur1 INTO
                v_Seq
               ,v_EnterpriseId;

            IF v_done = 1 THEN leave read_loop1;
            END IF;

            SELECT
                 IFNULL( D_AccountsReceivableBalance, 0)
                ,IFNULL( M_AccountsReceivableBalance, 0)
            INTO
                 v_D_AccountsReceivableBalance
                ,v_M_AccountsReceivableBalance
            FROM  AT_DailyStatisticsTable dst
            INNER JOIN ( SELECT EnterpriseId, MAX( ProcessingDate ) ProcessingDate FROM AT_DailyStatisticsTable
                         WHERE v_AccountingMonth   = AccountDate
                         AND   ValidFlg            = 1
                         AND   EnterpriseId        = v_EnterpriseId
                         AND   DailyMonthlyFlg     = 1
                         GROUP BY EnterpriseId ) dstw
            ON    dst.EnterpriseId = dstw.EnterpriseId
            AND   dst.ProcessingDate = dstw.ProcessingDate
            AND   dst.AccountDate = v_AccountingMonth
            AND   dst.DailyMonthlyFlg = 1;

            IF v_done = 1 THEN 
                SET v_done = 0;
                SET v_D_AccountsReceivableBalance = 0;
                SET v_M_AccountsReceivableBalance = 0;
            END IF;

            UPDATE  AT_DailyStatisticsTable
            SET  DB__AccountsReceivableBalance = DB__AccountsReceivableBalance + v_D_AccountsReceivableBalance  -- 前日時点未収金残高
                ,D_AccountsReceivableBalance   = D_AccountsReceivableBalance   + v_D_AccountsReceivableBalance  -- 当日時点未収金残高
                ,MB__AccountsReceivableBalance = MB__AccountsReceivableBalance + v_M_AccountsReceivableBalance  -- 月初未収金残高
                ,M_AccountsReceivableBalance   = M_AccountsReceivableBalance   + v_M_AccountsReceivableBalance  -- 当月時点未収金残高
                ,UpdateDate                    = NOW()                                                          -- 更新日時
                ,UpdateId                      = pi_user_id                                                     -- 更新者
            WHERE Seq = v_Seq;

        END LOOP;

        -- ｶｰｿﾙｸﾛｰｽﾞ
        CLOSE cur1;

    END;
END
$$

DELIMITER ;

