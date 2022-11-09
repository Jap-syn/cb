DROP procedure IF EXISTS `procMigrateJnbAccount`;

DELIMITER $$
CREATE PROCEDURE `procMigrateJnbAccount` ()
BEGIN

    /* 移行処理：郵便番号 */

    INSERT INTO `T_JnbAccount`
        (`AccountSeq`,
        `JnbId`,
        `AccountGroupId`,
        `RegistDate`,
        `BranchCode`,
        `AccountNumber`,
        `AccountHolder`,
        `Status`,
        `LastStatusChanged`,
        `NumberingDate`,
        `EffectiveDate`,
        `ModifiedDate`,
        `JnbStatus`,
        `ExpirationDate`,
        `LastReceiptDate`,
        `ReleasedDate`)
    SELECT 
        `T_JnbAccount`.`AccountSeq`,
        `T_JnbAccount`.`JnbId`,
        `T_JnbAccount`.`AccountGroupId`,
        `T_JnbAccount`.`RegistDate`,
        `T_JnbAccount`.`BranchCode`,
        `T_JnbAccount`.`AccountNumber`,
        `T_JnbAccount`.`AccountHolder`,
        `T_JnbAccount`.`Status`,
        `T_JnbAccount`.`LastStatusChanged`,
        `T_JnbAccount`.`NumberingDate`,
        `T_JnbAccount`.`EffectiveDate`,
        `T_JnbAccount`.`ModifiedDate`,
        `T_JnbAccount`.`JnbStatus`,
        `T_JnbAccount`.`ExpirationDate`,
        `T_JnbAccount`.`LastReceiptDate`,
        `T_JnbAccount`.`ReleasedDate`
    FROM `coraldb_ikou`.`T_JnbAccount`;
END
$$

DELIMITER ;

