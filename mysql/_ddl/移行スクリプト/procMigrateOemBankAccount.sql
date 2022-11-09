DROP procedure IF EXISTS `procMigrateOemBankAccount`;

DELIMITER $$
CREATE PROCEDURE `procMigrateOemBankAccount` ()
BEGIN

    /* 移行処理：OEM銀行口座 */

    DECLARE
        updDttm    datetime;        -- 更新日時

    SET updDttm = now();

    INSERT INTO `T_OemBankAccount`
        (`BankAccountId`,
        `OemId`,
        `ServiceKind`,
        `BankCode`,
        `BranchCode`,
        `BankName`,
        `BranchName`,
        `DepositClass`,
        `AccountNumber`,
        `AccountHolder`,
        `AccountHolderKn`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `T_OemBankAccount`.`BankAccountId`,
        `T_OemBankAccount`.`OemId`,
        `T_OemBankAccount`.`ServiceKind`,
        `T_OemBankAccount`.`BankCode`,
        `T_OemBankAccount`.`BranchCode`,
        `T_OemBankAccount`.`BankName`,
        `T_OemBankAccount`.`BranchName`,
        `T_OemBankAccount`.`DepositClass`,
        `T_OemBankAccount`.`AccountNumber`,
        `T_OemBankAccount`.`AccountHolder`,
        `T_OemBankAccount`.`AccountHolderKn`,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_OemBankAccount`;
END
$$

DELIMITER ;

