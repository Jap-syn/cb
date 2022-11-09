DROP procedure IF EXISTS `procMigrateOemDeliveryMethodList`;

DELIMITER $$
CREATE PROCEDURE `procMigrateOemDeliveryMethodList` ()
BEGIN

    /* 移行処理： OEM配送先順序*/

    DECLARE
        updDttm    datetime;        -- 更新日時

    SET updDttm = now();

    INSERT INTO `T_OemDeliveryMethodList`
        (`DeliMethodId`,
        `OemId`,
        `ListNumber`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT 
        `T_OemDeliveryMethodList`.`DeliMethodId`,
        `T_OemDeliveryMethodList`.`OemId`,
        `T_OemDeliveryMethodList`.`ListNumber`,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_OemDeliveryMethodList`;
END
$$

DELIMITER ;

