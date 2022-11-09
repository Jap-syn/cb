DROP procedure IF EXISTS `procMigrateCjMailHistoryB`;

DELIMITER $$
CREATE PROCEDURE `procMigrateCjMailHistoryB`()
BEGIN

    /* 移行処置：与信結果メール履歴 の切替日移行*/

    DECLARE
        updDttm    datetime;

    SET updDttm = now();
    
    INSERT INTO `T_CjMailHistory`
        (`Seq`,
        `OrderSeq`,
        `RegistDate`,
        `OccReason`,
        `SendMailFlg`,
        `ProcessingDate`,
        `MailTo`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT 
        `T_CjMailHistory`.`Seq`,
        `T_CjMailHistory`.`OrderSeq`,
        `T_CjMailHistory`.`RegistDate`,
        `T_CjMailHistory`.`OccReason`,
        `T_CjMailHistory`.`SendMailFlg`,
        `T_CjMailHistory`.`ProcessingDate`,
        `T_CjMailHistory`.`MailTo`,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_CjMailHistory`
    WHERE  `T_CjMailHistory`.`Seq` > 2999999;

END$$

DELIMITER ;

