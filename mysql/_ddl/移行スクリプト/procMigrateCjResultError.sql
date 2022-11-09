DROP procedure IF EXISTS `procMigrateCjResultError`;

DELIMITER $$
CREATE PROCEDURE `procMigrateCjResultError`()
BEGIN

    /* 移行処理：与信審査結果エラー */

    DECLARE
        updDttm    datetime;

    SET updDttm = now();

    INSERT INTO `T_CjResult_Error`
        (`Seq`,
        `CjrSeq`,
        `OrderSeq`,
        `ErrorCode`,
        `ErrorMsg`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `T_CjResult_Error`.`Seq`,
        `T_CjResult_Error`.`CjrSeq`,
        `T_CjResult_Error`.`OrderSeq`,
        `T_CjResult_Error`.`ErrorCode`,
        `T_CjResult_Error`.`ErrorMsg`,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_CjResult_Error`;
END$$

DELIMITER ;

