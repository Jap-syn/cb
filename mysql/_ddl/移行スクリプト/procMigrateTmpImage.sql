DROP procedure IF EXISTS `procMigrateTmpImage`;

DELIMITER $$
CREATE PROCEDURE `procMigrateTmpImage` ()
BEGIN
    /* 移行処理：OEM画像一時保存 */

    DECLARE
        updDttm    datetime;

    SET updDttm = now();

    INSERT INTO `T_TmpImage`
        (`Seq`,
        `OemId`,
        `UseType`,
        `FileName`,
        `ImageData`,
        `ImageType`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `T_TmpImage`.`Seq`,
        `T_TmpImage`.`OemId`,
        `T_TmpImage`.`UseType`,
        `T_TmpImage`.`FileName`,
        `T_TmpImage`.`ImageData`,
        `T_TmpImage`.`ImageType`,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_TmpImage`;

END
$$

DELIMITER ;

