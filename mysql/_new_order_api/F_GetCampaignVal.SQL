DROP FUNCTION IF EXISTS F_GetCampaignVal;

DELIMITER $$

CREATE FUNCTION F_GetCampaignVal(   pi_enterprise_id    INT
                                ,   pi_site_id          INT
                                ,   pi_today            DATE
                                ,   pi_type             VARCHAR(255)
                                )
RETURNS DECIMAL(16,5)
/****************************************************************************************
    *
    * ファンクション名 ：  F_GetCampaignVal
    *
    * 概要             ：  加盟店ID、ｻｲﾄID、期間からｷｬﾝﾍﾟｰﾝかﾏｽﾀのどちらかの情報を取得する
    *
    * 引数             ：  [I/ ]pi_enterprise_id               加盟店ID
    *                  ：  [I/ ]pi_site_id                     ｻｲﾄID
    *                  ：  [I/ ]pi_today                       日付
    *                  ：  [I/ ]pi_type                        取得項目
    *
    * 戻り値           ：  取得ﾃﾞｰﾀ
    *
    * 履歴             ：  2015/07/09  NDC 新規作成
    *                   ：  2015/10/05  NDC 取得対象の項目にSelfBillingOemClaimFee(OEM同梱請求手数料)を追加
    *
    ***************************************************************************************/
BEGIN
    -- 変数宣言
    DECLARE l_cnt   INT;            -- 件数ｶｳﾝﾄ用
    DECLARE l_type  DECIMAL(16,5);  -- 取得項目

    -- 加盟店ID、ｻｲﾄID、期間から加盟店ｷｬﾝﾍﾟｰﾝにﾃﾞｰﾀが存在するか確認する。
    SELECT  COUNT(*)
    INTO    l_cnt
    FROM    T_EnterpriseCampaign
    WHERE   EnterpriseId    =   pi_enterprise_id
    AND     SiteId          =   pi_site_id
    AND     pi_today BETWEEN DateFrom AND DateTo
    ;

    -- 存在しない場合は、ﾏｽﾀから該当項目のﾃﾞｰﾀを取得する。
    IF  l_cnt = 0   THEN
        -- 取得項目が月額固定費（MonthlyFee）、利用ﾌﾟﾗﾝ（AppPlan）、OEM月額固定費（OemMonthlyFee）の場合は加盟店から取得する。
        IF  pi_type = 'AppPlan' OR
            pi_type = 'MonthlyFee' OR 
            pi_type = 'IncludeMonthlyFee' OR 
            pi_type = 'ApiMonthlyFee' OR 
            pi_type = 'CreditNoticeMonthlyFee' OR 
            pi_type = 'NCreditNoticeMonthlyFee' OR 
            pi_type = 'ReserveMonthlyFee' OR 
            pi_type = 'OemMonthlyFee' OR 
            pi_type = 'OemIncludeMonthlyFee' OR 
            pi_type = 'OemApiMonthlyFee' OR 
            pi_type = 'OemCreditNoticeMonthlyFee' OR 
            pi_type = 'OemNCreditNoticeMonthlyFee' OR 
            pi_type = 'OemReserveMonthlyFee' 
        THEN
            SELECT  CASE    pi_type
                        WHEN 'MonthlyFee' THEN MonthlyFee
                        WHEN 'AppPlan' THEN Plan
                        WHEN 'IncludeMonthlyFee' THEN IncludeMonthlyFee
                        WHEN 'ApiMonthlyFee' THEN ApiMonthlyFee
                        WHEN 'CreditNoticeMonthlyFee' THEN CreditNoticeMonthlyFee
                        WHEN 'NCreditNoticeMonthlyFee' THEN NCreditNoticeMonthlyFee
                        WHEN 'ReserveMonthlyFee' THEN ReserveMonthlyFee
                        WHEN 'OemMonthlyFee' THEN OemMonthlyFee
                        WHEN 'OemIncludeMonthlyFee' THEN OemIncludeMonthlyFee
                        WHEN 'OemApiMonthlyFee' THEN OemApiMonthlyFee
                        WHEN 'OemCreditNoticeMonthlyFee' THEN OemCreditNoticeMonthlyFee
                        WHEN 'OemNCreditNoticeMonthlyFee' THEN OemNCreditNoticeMonthlyFee
                        WHEN 'OemReserveMonthlyFee' THEN OemReserveMonthlyFee
                        ELSE 0
                    END
            INTO    l_type
            FROM    T_Enterprise e
                    INNER JOIN AT_Enterprise ae
                            ON e.EnterpriseId = ae.EnterpriseId
            WHERE   e.EnterpriseId    =   pi_enterprise_id
            ;
        -- 上記以外の場合、ｻｲﾄから取得する
        ELSE
            SELECT  CASE    pi_type
                        WHEN 'SettlementAmountLimit' THEN SettlementAmountLimit
                        WHEN 'SettlementFeeRate' THEN SettlementFeeRate
                        WHEN 'ClaimFeeDK' THEN ClaimFeeDK
                        WHEN 'ClaimFeeBS' THEN ClaimFeeBS
                        WHEN 'ReClaimFee' THEN ReClaimFee
                        WHEN 'OemSettlementFeeRate' THEN OemSettlementFeeRate
                        WHEN 'OemClaimFee' THEN OemClaimFee
                        WHEN 'SelfBillingOemClaimFee' THEN SelfBillingOemClaimFee
                        ELSE SystemFee
                    END
            INTO    l_type
            FROM    T_Site
            WHERE   SiteId  =   pi_site_id
            ;
        END IF;
    -- 存在する場合は、加盟店ｷｬﾝﾍﾟｰﾝから該当項目のﾃﾞｰﾀを取得する。
    ELSE
        SELECT  CASE    pi_type
                    WHEN 'AppPlan' THEN AppPlan
                    WHEN 'MonthlyFee' THEN MonthlyFee
                    WHEN 'IncludeMonthlyFee' THEN IncludeMonthlyFee
                    WHEN 'ApiMonthlyFee' THEN ApiMonthlyFee
                    WHEN 'CreditNoticeMonthlyFee' THEN CreditNoticeMonthlyFee
                    WHEN 'NCreditNoticeMonthlyFee' THEN NCreditNoticeMonthlyFee
                    WHEN 'ReserveMonthlyFee' THEN ReserveMonthlyFee
                    WHEN 'OemMonthlyFee' THEN OemMonthlyFee
                    WHEN 'OemIncludeMonthlyFee' THEN OemIncludeMonthlyFee
                    WHEN 'OemApiMonthlyFee' THEN OemApiMonthlyFee
                    WHEN 'OemCreditNoticeMonthlyFee' THEN OemCreditNoticeMonthlyFee
                    WHEN 'OemNCreditNoticeMonthlyFee' THEN OemNCreditNoticeMonthlyFee
                    WHEN 'OemReserveMonthlyFee' THEN OemReserveMonthlyFee
                    WHEN 'SettlementAmountLimit' THEN SettlementAmountLimit
                    WHEN 'SettlementFeeRate' THEN SettlementFeeRate
                    WHEN 'ClaimFeeDK' THEN ClaimFeeDK
                    WHEN 'ClaimFeeBS' THEN ClaimFeeBS
                    WHEN 'ReClaimFee' THEN ReClaimFee
                    WHEN 'OemSettlementFeeRate' THEN OemSettlementFeeRate
                    WHEN 'OemClaimFee' THEN OemClaimFee
                    WHEN 'SelfBillingOemClaimFee' THEN SelfBillingOemClaimFee
                    ELSE SystemFee
                END
        INTO    l_type
        FROM    T_EnterpriseCampaign ec
                INNER JOIN AT_EnterpriseCampaign aec
                        ON ec.Seq = aec.Seq
        WHERE   EnterpriseId    =   pi_enterprise_id
        AND     SiteId          =   pi_site_id
        AND     pi_today BETWEEN DateFrom AND DateTo
        ;
    END IF;

    -- 取得したﾃﾞｰﾀを返す。
    RETURN  l_type;
END
$$

DELIMITER ;
