DELIMITER $$

CREATE DEFINER=`root`@`%` FUNCTION `F_GetCampaignVal`(   pi_enterprise_id    INT
                                ,   pi_site_id          INT
                                ,   pi_today            DATE
                                ,   pi_type             VARCHAR(255)
                                ) RETURNS decimal(16,5)
BEGIN
    -- 変数宣言
    DECLARE l_type  DECIMAL(16,5);  -- 取得項目

    select coraldb_new01.F_GetCampaignVal(pi_enterprise_id, pi_site_id, pi_today, pi_type) into l_type;

    return l_type;
END$$

DELIMITER ;
