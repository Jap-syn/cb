DROP procedure IF EXISTS `procMigrateOrderSummaryA`;

DELIMITER $$
CREATE PROCEDURE `procMigrateOrderSummaryA` ()
BEGIN

    /* 移行処理：注文サマリー の事前移行*/
    /* 2015-08-18  ひらがな→カタカナ変換  */

    DECLARE
        updDttm    datetime;        -- 更新日時

    SET updDttm = now();

    INSERT INTO `T_OrderSummary`
        (`SummaryId`,
        `OrderSeq`,
        `CarriageFee`,
        `ChargeFee`,
        `DestNameKj`,
        `DestNameKn`,
        `DestPostalCode`,
        `DestUnitingAddress`,
        `DestPhone`,
        `OrderItemId`,
        `OrderItemNames`,
        `ItemCount`,
        `ItemNameKj`,
        `Deli_JournalIncDate`,
        `Deli_DeliveryMethod`,
        `Deli_DeliveryMethodName`,
        `Deli_JournalNumber`,
        `NameKj`,
        `NameKn`,
        `PostalCode`,
        `UnitingAddress`,
        `Phone`,
        `MailAddress`,
        `RegDestNameKj`,
        `RegDestUnitingAddress`,
        `RegDestPhone`,
        `RegNameKj`,
        `RegUnitingAddress`,
        `RegPhone`,
        `OemId`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT 
        `T_OrderSummary`.`SummaryId`,
        `T_OrderSummary`.`OrderSeq`,
        `T_OrderSummary`.`CarriageFee`,
        `T_OrderSummary`.`ChargeFee`,
        `T_OrderSummary`.`DestNameKj`,
        convert_kana(`T_OrderSummary`.`DestNameKn`),
        `T_OrderSummary`.`DestPostalCode`,
        `T_OrderSummary`.`DestUnitingAddress`,
        `T_OrderSummary`.`DestPhone`,
        `T_OrderSummary`.`OrderItemId`,
        `T_OrderSummary`.`OrderItemNames`,
        `T_OrderSummary`.`ItemCount`,
        `T_OrderSummary`.`ItemNameKj`,
        `T_OrderSummary`.`Deli_JournalIncDate`,
        `T_OrderSummary`.`Deli_DeliveryMethod`,
        `T_OrderSummary`.`Deli_DeliveryMethodName`,
        `T_OrderSummary`.`Deli_JournalNumber`,
        `T_OrderSummary`.`NameKj`,
        convert_kana(`T_OrderSummary`.`NameKn`),
        `T_OrderSummary`.`PostalCode`,
        `T_OrderSummary`.`UnitingAddress`,
        `T_OrderSummary`.`Phone`,
        `T_OrderSummary`.`MailAddress`,
        `T_OrderSummary`.`RegDestNameKj`,
        `T_OrderSummary`.`RegDestUnitingAddress`,
        `T_OrderSummary`.`RegDestPhone`,
        `T_OrderSummary`.`RegNameKj`,
        `T_OrderSummary`.`RegUnitingAddress`,
        `T_OrderSummary`.`RegPhone`,
        `T_OrderSummary`.`OemId`,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_OrderSummary`
    WHERE  `T_OrderSummary`.`SummaryId` < 4200000;
END
$$

DELIMITER ;

