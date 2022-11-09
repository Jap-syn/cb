DROP procedure IF EXISTS `procMigrateThreadPool`;

DELIMITER $$
CREATE PROCEDURE `procMigrateThreadPool` ()
BEGIN
    /* 移行処理：スレッドプール */

    DECLARE
        updDttm    datetime;

    SET updDttm = now();

    INSERT INTO `T_ThreadPool`
        (`ThreadId`,
        `ThreadGroup`,
        `CreateDate`,
        `LastAccessDate`,
        `Status`,
        `UserData`,
        `TerminateReason`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `T_ThreadPool`.`ThreadId`,
        `T_ThreadPool`.`ThreadGroup`,
        `T_ThreadPool`.`CreateDate`,
        `T_ThreadPool`.`LastAccessDate`,
        `T_ThreadPool`.`Status`,
        `T_ThreadPool`.`UserData`,
        `T_ThreadPool`.`TerminateReason`,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_ThreadPool`;
END
$$

DELIMITER ;

