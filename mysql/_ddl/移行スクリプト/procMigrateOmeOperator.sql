DROP procedure IF EXISTS `procMigrateOmeOperator`;

DELIMITER $$
CREATE PROCEDURE `procMigrateOmeOperator` ()
BEGIN

    /* 移行処理：OEMオペレーター */

    DECLARE
        updDttm    datetime;        -- 更新日時

    SET updDttm = now();

    INSERT INTO `T_OemOperator`
        (`OemOpId`,
        `OemId`,
        `LoginId`,
        `LoginPasswd`,
        `NameKj`,
        `NameKn`,
        `Division`,
        `ValidFlg`,
        `RoleCode`,
        `Hashed`,
        `LastPasswordChanged`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`)
    SELECT
         `T_OemOperator`.`OemOpId`,
        `T_OemOperator`.`OemId`,
        `T_OemOperator`.`LoginId`,
        `T_OemOperator`.`LoginPasswd`,
        `T_OemOperator`.`NameKj`,
        `T_OemOperator`.`NameKn`,
        `T_OemOperator`.`Division`,
        IFNULL(`T_OemOperator`.`ValidFlg`, 1),
        1,
        `T_OemOperator`.`Hashed`,
        `T_OemOperator`.`LastPasswordChanged`,
        updDttm,
        9,
        updDttm,
        9
    FROM `coraldb_ikou`.`T_OemOperator`;

END
$$

DELIMITER ;

