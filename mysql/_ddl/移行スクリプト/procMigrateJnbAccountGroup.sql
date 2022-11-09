DROP procedure IF EXISTS `procMigrateJnbAccountGroup`;

DELIMITER $$
CREATE PROCEDURE `procMigrateJnbAccountGroup` ()
BEGIN

    /* 移行処理：郵便番号 */

    INSERT INTO `T_JnbAccountGroup`
        (`AccountGroupId`,
        `JnbId`,
        `RegistDate`,
        `TotalAccounts`,
        `ManageKey`,
        `ManageKeyLabel`,
        `DepositClass`,
        `ReturnedFlg`,
        `ReturnedDate`)
    SELECT 
        `T_JnbAccountGroup`.`AccountGroupId`,
        `T_JnbAccountGroup`.`JnbId`,
        `T_JnbAccountGroup`.`RegistDate`,
        `T_JnbAccountGroup`.`TotalAccounts`,
        `T_JnbAccountGroup`.`ManageKey`,
        `T_JnbAccountGroup`.`ManageKeyLabel`,
        `T_JnbAccountGroup`.`DepositClass`,
        `T_JnbAccountGroup`.`ReturnedFlg`,
        `T_JnbAccountGroup`.`ReturnedDate`
    FROM `coraldb_ikou`.`T_JnbAccountGroup`;
END
$$

DELIMITER ;

