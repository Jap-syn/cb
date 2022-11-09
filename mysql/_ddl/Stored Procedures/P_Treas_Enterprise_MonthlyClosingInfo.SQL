DROP PROCEDURE IF EXISTS P_Treas_Enterprise_MonthlyClosingInfo;

DELIMITER $$

CREATE PROCEDURE P_Treas_Enterprise_MonthlyClosingInfo ( IN pi_user_id INT )
proc:
/******************************************************************************
 *
 * プロシージャ名   ：  P_Treas_Enterprise_MonthlyClosingInfo
 *
 * 概要             ：  加盟店月締め情報
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
 *
 *****************************************************************************/
BEGIN
    -- ------------------------------
    -- 変数宣言
    -- ------------------------------
    DECLARE v_BusinessDate      DATE;                   -- 業務日付
    DECLARE v_AccountingMonth   DATE;                   -- 会計月
    DECLARE v_TaxRate           DECIMAL(11, 2);         -- 消費税率

    -- ------------------------------
    -- 変数初期化
    -- ------------------------------
    SET v_BusinessDate      = F_GetSystemProperty( '[DEFAULT]', 'systeminfo', 'BusinessDate' );     -- 業務日付
    SET v_AccountingMonth   = F_GetSystemProperty( '[DEFAULT]', 'systeminfo', 'AccountingMonth' );  -- 会計月
    SET v_TaxRate           = F_GetTaxRate( v_BusinessDate ) / 100 ;                                -- 消費税率（計算用）

    -- ------------------------------
    -- 加盟店月締め情報
    -- ------------------------------
    INSERT INTO AT_EnterpriseMonthlyClosingInfo (
         EnterpriseId
        ,ClosingMonthly
        ,OemId
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
        ,TaxRate
        ,DailySummaryFlg
        ,AppPlan
        ,RegistDate
        ,RegistId
        ,UpdateDate
        ,UpdateId
        ,ValidFlg )
    SELECT
         e.EnterpriseId                                                             -- 加盟店ID
        ,v_AccountingMonth                                                          -- 会計月
        ,IFNULL( e.OemId, 0 )                                                       -- OEMID
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( e.N_MonthlyFee, 0 )
            ELSE IFNULL( ec.MonthlyFee, 0 )
         END                                                                        -- 月額固定費(税抜）
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( FLOOR( e.N_MonthlyFee * v_TaxRate ), 0 )
            ELSE IFNULL( FLOOR( ec.MonthlyFee * v_TaxRate ), 0 )
         END                                                                        -- 月額固定費消費税
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( at_e.N_IncludeMonthlyFee, 0 )
            ELSE IFNULL( at_ec.IncludeMonthlyFee, 0 )
         END                                                                        -- 同梱月額固定費(税抜）
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( FLOOR( at_e.N_IncludeMonthlyFee * v_TaxRate ), 0 )
            ELSE IFNULL( FLOOR( at_ec.IncludeMonthlyFee * v_TaxRate ), 0 )
         END                                                                        -- 同梱月額固定費消費税
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( at_e.N_ApiMonthlyFee, 0 )
            ELSE IFNULL( at_ec.ApiMonthlyFee, 0 )
         END                                                                        -- API月額固定費(税抜）
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( FLOOR( at_e.N_ApiMonthlyFee * v_TaxRate ), 0 )
            ELSE IFNULL( FLOOR( at_ec.ApiMonthlyFee * v_TaxRate ), 0 )
         END                                                                        -- API月額固定費消費税
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( at_e.N_CreditNoticeMonthlyFee, 0 )
            ELSE IFNULL( at_ec.CreditNoticeMonthlyFee, 0 )
         END                                                                        -- 与信結果通知ｻｰﾋﾞｽ月額固定費(税抜）
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( FLOOR( at_e.N_CreditNoticeMonthlyFee * v_TaxRate ), 0 )
            ELSE IFNULL( FLOOR( at_ec.CreditNoticeMonthlyFee * v_TaxRate ), 0 )
         END                                                                        -- 与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( at_e.N_NCreditNoticeMonthlyFee, 0 )
            ELSE IFNULL( at_ec.NCreditNoticeMonthlyFee, 0 )
         END                                                                        -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費(税抜）
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( FLOOR( at_e.N_NCreditNoticeMonthlyFee * v_TaxRate ), 0 )
            ELSE IFNULL( FLOOR( at_ec.NCreditNoticeMonthlyFee * v_TaxRate ), 0 )
         END                                                                        -- 次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( at_e.N_ReserveMonthlyFee, 0 )
            ELSE IFNULL( at_ec.ReserveMonthlyFee, 0 )
         END                                                                        -- 月額固定費予備(税抜）
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( FLOOR( at_e.N_ReserveMonthlyFee * v_TaxRate ), 0 )
            ELSE IFNULL( FLOOR( at_ec.ReserveMonthlyFee * v_TaxRate ), 0 )
         END                                                                        -- 月額固定費予備消費税
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( e.N_OemMonthlyFee, 0 )
            ELSE IFNULL( ec.OemMonthlyFee, 0 )
         END                                                                        -- OEM月額固定費(税抜）
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( FLOOR( e.N_OemMonthlyFee * v_TaxRate ), 0 )
            ELSE IFNULL( FLOOR( ec.OemMonthlyFee * v_TaxRate ), 0 )
         END                                                                        -- OEM月額固定費消費税
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( at_e.N_OemIncludeMonthlyFee, 0 )
            ELSE IFNULL( at_ec.OemIncludeMonthlyFee, 0 )
         END                                                                        -- OEM同梱月額固定費(税抜）
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( FLOOR( at_e.N_OemIncludeMonthlyFee * v_TaxRate ), 0 )
            ELSE IFNULL( FLOOR( at_ec.OemIncludeMonthlyFee * v_TaxRate ), 0 )
         END                                                                        -- OEM同梱月額固定費消費税
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( at_e.N_OemApiMonthlyFee, 0 )
            ELSE IFNULL( at_ec.OemApiMonthlyFee, 0 )
         END                                                                        -- OEMAPI月額固定費(税抜）
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( FLOOR( at_e.N_OemApiMonthlyFee * v_TaxRate ), 0 )
            ELSE IFNULL( FLOOR( at_ec.OemApiMonthlyFee * v_TaxRate ), 0 )
         END                                                                        -- OEMAPI月額固定費消費税
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( at_e.N_OemCreditNoticeMonthlyFee, 0 )
            ELSE IFNULL( at_ec.OemCreditNoticeMonthlyFee, 0 )
         END                                                                        -- OEM与信結果通知ｻｰﾋﾞｽ月額固定費(税抜）
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( FLOOR( at_e.N_OemCreditNoticeMonthlyFee * v_TaxRate ), 0 )
            ELSE IFNULL( FLOOR( at_ec.OemCreditNoticeMonthlyFee * v_TaxRate ), 0 )
         END                                                                        -- OEM与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( at_e.N_OemNCreditNoticeMonthlyFee, 0 )
            ELSE IFNULL( at_ec.OemNCreditNoticeMonthlyFee, 0 )
         END                                                                        -- OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費(税抜）
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( FLOOR( at_e.N_OemNCreditNoticeMonthlyFee * v_TaxRate ), 0 )
            ELSE IFNULL( FLOOR( at_ec.OemNCreditNoticeMonthlyFee * v_TaxRate ), 0 )
         END                                                                        -- OEM次回請求与信結果通知ｻｰﾋﾞｽ月額固定費消費税
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( at_e.N_OemReserveMonthlyFee, 0 )
            ELSE IFNULL( at_ec.OemReserveMonthlyFee, 0 )
         END                                                                        -- OEM月額固定費予備(税抜）
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN IFNULL( FLOOR( at_e.N_OemReserveMonthlyFee * v_TaxRate ), 0 )
            ELSE IFNULL( FLOOR( at_ec.OemReserveMonthlyFee * v_TaxRate ), 0 )
         END                                                                        -- OEM月額固定費予備消費税
        ,v_TaxRate * 100                                                            -- 消費税率
        ,0                                                                          -- 日別集計フラグ
        ,CASE WHEN ISNULL( ec.EnterpriseId ) 
            THEN e.Plan
            ELSE ec.AppPlan
         END                                                                        -- プラン
        ,NOW()                                                                      -- 登録日時
        ,pi_user_id                                                                 -- 登録者
        ,NOW()                                                                      -- 更新日時
        ,pi_user_id                                                                 -- 更新者
        ,1                                                                          -- 有効フラグ：有効
    FROM        T_Enterprise e
    INNER JOIN  AT_Enterprise at_e
    ON      at_e.EnterpriseId   =   e.EnterpriseId
    LEFT JOIN   (SELECT MIN(Seq) AS Seq
                    ,   EnterpriseId
                    ,   MIN(MonthlyFee) AS MonthlyFee
                    ,   MIN(OemMonthlyFee) AS OemMonthlyFee
                    ,   MIN(AppPlan) AS AppPlan
                 FROM   T_EnterpriseCampaign
                 WHERE  DateFrom <= v_BusinessDate AND v_BusinessDate <= DateTo
                 GROUP BY
                        EnterpriseId) ec
    ON      ec.EnterpriseId     =   e.EnterpriseId
    LEFT JOIN   AT_EnterpriseCampaign at_ec
    ON      at_ec.Seq           =   ec.Seq
    WHERE   e.ServiceInDate IS NOT NULL
    AND     e.ValidFlg          =   1;

END
$$

DELIMITER ;

