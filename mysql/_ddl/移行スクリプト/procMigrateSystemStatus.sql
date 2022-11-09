DROP procedure IF EXISTS `procMigrateSystemStatus`;

DELIMITER $$
CREATE PROCEDURE `procMigrateSystemStatus` ()
BEGIN
    /* 移行処理：システムステータス */

    DECLARE
        updDttm    datetime;

    SET updDttm = now();

    INSERT INTO `T_SystemStatus`
        (`CreditJudgeLock`,
        `CjMailLock`)
    SELECT
        `T_SystemStatus`.`CreditJudgeLock`,
        `T_SystemStatus`.`CjMailLock`
    FROM `coraldb_ikou`.`T_SystemStatus`;
END
$$

DELIMITER ;

