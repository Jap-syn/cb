DROP procedure IF EXISTS `procMigrateJnbAccountUsageHistory`;

DELIMITER $$
CREATE PROCEDURE `procMigrateJnbAccountUsageHistory` ()
BEGIN

    /* 移行処理：郵便番号 */

    INSERT INTO `T_JnbAccountUsageHistory`
        (`UsageHistorySeq`,
        `AccountSeq`,
        `UsedDate`,
        `MostRecent`,
        `Type`,
        `OrderSeq`,
        `CloseReason`,
        `CloseMemo`,
        `DeleteFlg`)
    SELECT 
        `T_JnbAccountUsageHistory`.`UsageHistorySeq`,
        `T_JnbAccountUsageHistory`.`AccountSeq`,
        `T_JnbAccountUsageHistory`.`UsedDate`,
        `T_JnbAccountUsageHistory`.`MostRecent`,
        `T_JnbAccountUsageHistory`.`Type`,
        `T_JnbAccountUsageHistory`.`OrderSeq`,
        `T_JnbAccountUsageHistory`.`CloseReason`,
        `T_JnbAccountUsageHistory`.`CloseMemo`,
        `T_JnbAccountUsageHistory`.`DeleteFlg`
    FROM `coraldb_ikou`.`T_JnbAccountUsageHistory`;
END
$$

DELIMITER ;

