DROP procedure IF EXISTS `procMigrateJnb`;

DELIMITER $$
CREATE PROCEDURE `procMigrateJnb` ()
BEGIN

    /* 移行処理：郵便番号 */

    INSERT INTO `T_Jnb`
        (`JnbId`,
        `OemId`,
        `RegistDate`,
        `DisplayName`,
        `Memo`,
        `BankName`,
        `BankCode`,
        `ValidFlg`)
    SELECT 
        `T_Jnb`.`JnbId`,
        `T_Jnb`.`OemId`,
        `T_Jnb`.`RegistDate`,
        `T_Jnb`.`DisplayName`,
        `T_Jnb`.`Memo`,
        `T_Jnb`.`BankName`,
        `T_Jnb`.`BankCode`,
        `T_Jnb`.`ValidFlg`
    FROM `coraldb_ikou`.`T_Jnb`;
END
$$

DELIMITER ;

