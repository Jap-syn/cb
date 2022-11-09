DROP procedure IF EXISTS `procMigrateSystemProperty`;

DELIMITER $$
CREATE PROCEDURE `procMigrateSystemProperty` ()
BEGIN
    /* 移行処置：システムプロパティ */

    DECLARE
        updDttm    datetime;

    SET updDttm = now();

    INSERT INTO `T_SystemProperty`
        (`PropId`,
        `Module`,
        `Category`,
        `Name`,
        `PropValue`,
        `Description`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `T_SystemProperty`.`PropId`,
        '[DEFAULT]',
        `T_SystemProperty`.`Category`,
        `T_SystemProperty`.`Name`,
        `T_SystemProperty`.`PropValue`,
        `T_SystemProperty`.`Description`,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_SystemProperty`;

END
$$

DELIMITER ;