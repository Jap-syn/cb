DROP procedure IF EXISTS `procMigrateAdjustmentAmount`;

DELIMITER $$
CREATE PROCEDURE `procMigrateAdjustmentAmount` ()
BEGIN
    /* 移行処理：調整額管理　*/

    DECLARE 
        updDttm    datetime;

    SET updDttm = now();

    INSERT INTO `T_AdjustmentAmount`
        (`PayingControlSeq`,
        `SerialNumber`,
        `OrderId`,
        `OrderSeq`,
        `ItemCode`,
        `AdjustmentAmount`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `T_PayingControl`.`Seq`,
        1,
        null,
        null,
        null,
        `T_PayingControl`.`AdjustmentAmount`,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_PayingControl`
    WHERE `T_PayingControl`.`AdjustmentAmount` <> 0;

END
$$

DELIMITER ;

