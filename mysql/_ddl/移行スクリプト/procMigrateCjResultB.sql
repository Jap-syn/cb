DROP procedure IF EXISTS `procMigrateCjResultB`;

DELIMITER $$
CREATE PROCEDURE `procMigrateCjResultB`()
BEGIN

    /* 移行処理：与信審査結果 の切替日移行*/

    DECLARE
        updDttm    datetime;

    SET updDttm = now();

    INSERT INTO `T_CjResult`
        (`Seq`,
        `OrderSeq`,
        `OrderId`,
        `SendDate`,
        `ReceiveDate`,
        `TotalScore`,
        `Status`,
        `Result`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT 
        `T_CjResult`.`Seq`,
        `T_CjResult`.`OrderSeq`,
        `T_CjResult`.`OrderId`,
        `T_CjResult`.`SendDate`,
        `T_CjResult`.`ReceiveDate`,
        `T_CjResult`.`TotalScore`,
        `T_CjResult`.`Status`,
        `T_CjResult`.`Result`,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_CjResult`
    WHERE  `T_CjResult`.`Seq` > 1999999;

END$$

DELIMITER ;
