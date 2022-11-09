DROP procedure IF EXISTS `procMigratePrefecture`;

DELIMITER $$
CREATE PROCEDURE `procMigratePrefecture` ()
BEGIN

   /* 移行処理：都道府県 */

    INSERT INTO `M_Prefecture`(
        `PrefectureCode`,
        `PrefectureName`,
        `PrefectureShortName`,
        `ValidFlg`)
    SELECT 
        `M_Prefecture`.`PrefectureCode`,
        `M_Prefecture`.`PrefectureName`,
        `M_Prefecture`.`PrefectureShortName`,
        `M_Prefecture`.`ValidFlg`
    FROM `coraldb_ikou`.`M_Prefecture`;
END
$$

DELIMITER ;

