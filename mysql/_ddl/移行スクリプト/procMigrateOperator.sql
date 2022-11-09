DROP procedure IF EXISTS `procMigrateOperator`;

DELIMITER $$
CREATE PROCEDURE `procMigrateOperator` ()
BEGIN

    /* 移行処理：オペレーター */

    DECLARE
        updDttm    datetime;        -- 更新日時

    SET updDttm = now();

    INSERT INTO `T_Operator`
        (`OpId`,
        `LoginId`,
        `LoginPasswd`,
        `NameKj`,
        `NameKn`,
        `Division`,
        `RoleCode`,
        `ValidFlg`,
        `Hashed`,
        `LastPasswordChanged`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`)
    SELECT
        `T_Operator`.`OpId`,
        `T_Operator`.`LoginId`,
        `T_Operator`.`LoginPasswd`,
        `T_Operator`.`NameKj`,
        `T_Operator`.`NameKn`,
        `T_Operator`.`Division`,
        `T_Operator`.`RoleCode`,
        IFNULL(`T_Operator`.`ValidFlg`, 1),
        `T_Operator`.`Hashed`,
        `T_Operator`.`LastPasswordChanged`,
        updDttm,
        9,
        updDttm,
        9
    FROM `coraldb_ikou`.`T_Operator`;

END
$$

DELIMITER ;
;
