DROP procedure IF EXISTS `procMigrateApiUserEnterprise`;

DELIMITER $$
CREATE PROCEDURE `procMigrateApiUserEnterprise` ()
BEGIN
    /* 移行処理：APIユーザー加盟店 */

    DECLARE
        updDttm    datetime;

    SET updDttm = now();

    INSERT INTO `T_ApiUserEnterprise`
        (`ApiUserId`,
        `SiteId`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `T_ApiUserEnterprise`.`ApiUserId`,
        `T_Site`.`SiteId`,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_ApiUserEnterprise`
    INNER JOIN `coraldb_ikou`.`T_Site`
    ON `T_ApiUserEnterprise`.`EnterpriseId` = `T_Site`.`EnterpriseId`;

END
$$

DELIMITER ;


