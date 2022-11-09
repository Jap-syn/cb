DROP procedure IF EXISTS `procMigrateOrderItemsB`;

DELIMITER $$
CREATE PROCEDURE `procMigrateOrderItemsB` ()
BEGIN

    /* 移行処理：注文商品 の事前移行*/

    DECLARE
        updDttm    datetime;        -- 更新日時

    SET updDttm = now();

    INSERT INTO `T_OrderItems`
        (`OrderItemId`,
        `OrderSeq`,
        `DeliDestId`,
        `ItemNameKj`,
        `ItemNameKn`,
        `UnitPrice`,
        `ItemNum`,
        `SumMoney`,
        `DataClass`,
        `Incre_Score`,
        `Incre_Note`,
        `Deli_JournalIncDate`,
        `Deli_DeliveryMethod`,
        `Deli_JournalNumber`,
        `Deli_ShipDate`,
        `Deli_ConfirmArrivalFlg`,
        `Deli_ConfirmArrivalDate`,
        `Deli_ConfirmArrivalOpId`,
        `Deli_ConfirmNoArrivalReason`,
        `CombinedTargetFlg`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT 
        `T_OrderItems`.`OrderItemId`,
        `T_OrderItems`.`OrderSeq`,
        `T_OrderItems`.`DeliDestId`,
        `T_OrderItems`.`ItemNameKj`,
        `T_OrderItems`.`ItemNameKn`,
        `T_OrderItems`.`UnitPrice`,
        `T_OrderItems`.`ItemNum`,
        `T_OrderItems`.`SumMoney`,
        `T_OrderItems`.`DataClass`,
        `T_OrderItems`.`Incre_Score`,
        `T_OrderItems`.`Incre_Note`,
        `T_OrderItems`.`Deli_JournalIncDate`,
        `T_OrderItems`.`Deli_DeliveryMethod`,
        `T_OrderItems`.`Deli_JournalNumber`,
        `T_OrderItems`.`Deli_ShipDate`,
        `T_OrderItems`.`Deli_ConfirmArrivalFlg`,
        `T_OrderItems`.`Deli_ConfirmArrivalDate`,
        `T_OrderItems`.`Deli_ConfirmArrivalOpId`,
        `T_OrderItems`.`Deli_ConfirmNoArrivalReason`,
        1,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_OrderItems`
    WHERE  `T_OrderItems`.`OrderItemId` > 17999999;
END
$$

DELIMITER ;

