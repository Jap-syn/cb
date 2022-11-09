DROP procedure IF EXISTS `procMigratePostalCode`;

DELIMITER $$
CREATE PROCEDURE `procMigratePostalCode` ()
BEGIN

    /* 移行処理：郵便番号 */

    INSERT INTO `M_PostalCode`
        (`Seq`,
        `LocalGroupCode`,
        `PostalCode5`,
        `PostalCode7`,
        `PrefectureKana`,
        `CityKana`,
        `TownKana`,
        `PrefectureKanji`,
        `CityKanji`,
        `TownKanji`,
        `OneTownPluralNumberFlg`,
        `NumberingEachKoazaFlg`,
        `TownIncludeChoumeFlg`,
        `OneNumberPluralTownFlg`,
        `UpdateFlg`,
        `ModifiedReasonCode`)
    SELECT 
        `M_PostalCode`.`Seq`,
        `M_PostalCode`.`LocalGroupCode`,
        `M_PostalCode`.`PostalCode5`,
        `M_PostalCode`.`PostalCode7`,
        `M_PostalCode`.`PrefectureKana`,
        `M_PostalCode`.`CityKana`,
        `M_PostalCode`.`TownKana`,
        `M_PostalCode`.`PrefectureKanji`,
        `M_PostalCode`.`CityKanji`,
        `M_PostalCode`.`TownKanji`,
        `M_PostalCode`.`OneTownPluralNumberFlg`,
        `M_PostalCode`.`NumberingEachKoazaFlg`,
        `M_PostalCode`.`TownIncludeChoumeFlg`,
        `M_PostalCode`.`OneNumberPluralTownFlg`,
        `M_PostalCode`.`UpdateFlg`,
        `M_PostalCode`.`ModifiedReasonCode`
    FROM `coraldb_ikou`.`M_PostalCode`;
END
$$

DELIMITER ;

