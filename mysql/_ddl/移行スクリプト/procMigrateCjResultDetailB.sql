DROP procedure IF EXISTS `procMigrateCjResultDetailB`;

DELIMITER $$
CREATE PROCEDURE `procMigrateCjResultDetailB`()
BEGIN

    /* 移行処理：与信審査結果詳細 の切替日移行*/

    DECLARE
        updDttm    datetime;

    SET updDttm = now();

    INSERT INTO `T_CjResult_Detail`
        (`Seq`,
        `CjrSeq`,
        `OrderSeq`,
        `DetectionPatternNo`,
        `DetectionPatternName`,
        `DetectionPatternScore`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT 
        `T_CjResult_Detail`.`Seq`,
        `T_CjResult_Detail`.`CjrSeq`,
        `T_CjResult_Detail`.`OrderSeq`,
        `T_CjResult_Detail`.`DetectionPatternNo`,
        `T_CjResult_Detail`.`DetectionPatternName`,
        `T_CjResult_Detail`.`DetectionPatternScore`,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_CjResult_Detail`
    WHERE  `T_CjResult_Detail`.`Seq` > 2999999;
END$$

DELIMITER ;

