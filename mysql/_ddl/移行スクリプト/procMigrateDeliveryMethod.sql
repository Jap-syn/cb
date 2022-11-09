DROP procedure IF EXISTS `procMigrateDeliveryMethod`;

DELIMITER $$
CREATE PROCEDURE `procMigrateDeliveryMethod` ()
BEGIN

    /* 移行処理：　配送方法 */

    DECLARE updDttm     datetime;

    SET updDttm = now();

    INSERT INTO `M_DeliveryMethod`
        (`DeliMethodId`,
        `DeliMethodName`,
        `DeliMethodNameB`,
        `EnableCancelFlg`,
        `PayChgCondition`,
        `ValidFlg`,
        `ArrivalConfirmUrl`,
        `ValidateRegex`,
        `ListNumber`,
        `JournalRegistClass`,
        `ProductServiceClass`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`)
    SELECT
        `M_DeliveryMethod`.`DeliMethodId`,
        `M_DeliveryMethod`.`DeliMethodName`,
        `M_DeliveryMethod`.`DeliMethodNameB`,
        `M_DeliveryMethod`.`EnableCancelFlg`,
        `M_DeliveryMethod`.`PayChgCondition`,
        IFNULL(`M_DeliveryMethod`.`ValidFlg`, 1),
        `M_DeliveryMethod`.`ArrivalConfirmUrl`,
        `M_DeliveryMethod`.`ValidateRegex`,
        `M_DeliveryMethod`.`DeliMethodId`,
        1,
        0,
        updDttm,
        9,
        updDttm,
        9
    FROM `coraldb_ikou`.`M_DeliveryMethod`;

    -- メール便の場合、伝票番号登録区分　＝　0へ設定
    UPDATE  `M_DeliveryMethod`
    SET     `JournalRegistClass` = 0
    WHERE   `DeliMethodId` = 13;
END
$$

DELIMITER ;

