DROP procedure IF EXISTS `procMigrateBusinessCalendar`;

DELIMITER $$

CREATE PROCEDURE `procMigrateBusinessCalendar` ()
BEGIN

    /* 移行処理：カレンダー */
    /* 20150724 note追加  */
    DECLARE
        updDttm    datetime;

    SET updDttm = now();

    INSERT INTO `T_BusinessCalendar`
        (`BusinessDate`,
        `BusinessFlg`,
        `WeekDay`,
        `Label`,
        `Note`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `T_BusinessCalendar`.`BusinessDate`,
        `T_BusinessCalendar`.`BusinessFlg`,
        `T_BusinessCalendar`.`WeekDay`,
        `T_BusinessCalendar`.`Label`,
        NULL,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_BusinessCalendar`;
END
$$

DELIMITER ;
