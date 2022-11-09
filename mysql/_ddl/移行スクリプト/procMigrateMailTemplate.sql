DROP procedure IF EXISTS `procMigrateMailTemplate`;

DELIMITER $$
CREATE PROCEDURE `procMigrateMailTemplate` ()
BEGIN

    /* 移行処理：メールテンプレート */

    DECLARE
        updDttm    datetime;        -- 更新日時

    SET updDttm = now();
    /*
    INSERT INTO `T_MailTemplate`
        (`Id`,
        `Class`,
        `ClassName`,
        `FromTitle`,
        `FromTitleMime`,
        `FromAddress`,
        `ToTitle`,
        `ToTitleMime`,
        `ToAddress`,
        `Subject`,
        `SubjectMime`,
        `Body`,
        `OemId`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `T_MailTemplate`.`Id`,
        `T_MailTemplate`.`Class`,
        `T_MailTemplate`.`ClassName`,
        `T_MailTemplate`.`FromTitle`,
        `T_MailTemplate`.`FromTitleMime`,
        `T_MailTemplate`.`FromAddress`,
        `T_MailTemplate`.`ToTitle`,
        `T_MailTemplate`.`ToTitleMime`,
        `T_MailTemplate`.`ToAddress`,
        `T_MailTemplate`.`Subject`,
        `T_MailTemplate`.`SubjectMime`,
        `T_MailTemplate`.`Body`,
        `T_MailTemplate`.`OemId`,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_MailTemplate`;
    */
END
$$

DELIMITER ;

