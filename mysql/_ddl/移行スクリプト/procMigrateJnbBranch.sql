DROP procedure IF EXISTS `procMigrateJnbBranch`;

DELIMITER $$
CREATE PROCEDURE `procMigrateJnbBranch` ()
BEGIN

    /* 移行処理：郵便番号 */

    INSERT INTO `M_JnbBranch`
        (`JnbBranchCode`,
        `JnbBranchName`)
    SELECT 
        `M_JnbBranch`.`JnbBranchCode`,
        `M_JnbBranch`.`JnbBranchName`
    FROM `coraldb_ikou`.`M_JnbBranch`;
END
$$

DELIMITER ;

