DROP procedure IF EXISTS `procMigrateDeliveryDestinationA`;

DELIMITER $$
CREATE PROCEDURE `procMigrateDeliveryDestinationA` ()
BEGIN

    /* 移行処理：配送先 の事前移行*/
    /* 2015-08-18  ひらがな→カタカナ変換  */

    DECLARE
        updDttm    datetime;        -- 更新日時

    SET updDttm = now();

    INSERT INTO `T_DeliveryDestination`
        (`DeliDestId`,
        `DestNameKj`,
        `DestNameKn`,
        `PostalCode`,
        `PrefectureCode`,
        `PrefectureName`,
        `City`,
        `Town`,
        `Building`,
        `UnitingAddress`,
        `Hash_Name`,
        `Hash_Address`,
        `Phone`,
        `Incre_ArName`,
        `Incre_NameScore`,
        `Incre_NameNote`,
        `Incre_ArAddr`,
        `Incre_AddressScore`,
        `Incre_AddressNote`,
        `Incre_SameCnAndAddrScore`,
        `Incre_SameCnAndAddrNote`,
        `Incre_PostalCodeScore`,
        `Incre_PostalCodeNote`,
        `Incre_ScoreTotal`,
        `SearchDestNameKj`,
        `SearchDestNameKn`,
        `SearchPhone`,
        `SearchUnitingAddress`,
        `Incre_ArTel`,
        `Incre_TelScore`,
        `Incre_TelNote`,
        `RegDestNameKj`,
        `RegUnitingAddress`,
        `RegPhone`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `T_DeliveryDestination`.`DeliDestId`,
        `T_DeliveryDestination`.`DestNameKj`,
        convert_kana(`T_DeliveryDestination`.`DestNameKn`),
        `T_DeliveryDestination`.`PostalCode`,
        `T_DeliveryDestination`.`PrefectureCode`,
        `T_DeliveryDestination`.`PrefectureName`,
        `T_DeliveryDestination`.`City`,
        `T_DeliveryDestination`.`Town`,
        `T_DeliveryDestination`.`Building`,
        `T_DeliveryDestination`.`UnitingAddress`,
        `T_DeliveryDestination`.`Hash_Name`,
        `T_DeliveryDestination`.`Hash_Address`,
        `T_DeliveryDestination`.`Phone`,
        `T_DeliveryDestination`.`Incre_ArName`,
        `T_DeliveryDestination`.`Incre_NameScore`,
        `T_DeliveryDestination`.`Incre_NameNote`,
        `T_DeliveryDestination`.`Incre_ArAddr`,
        `T_DeliveryDestination`.`Incre_AddressScore`,
        `T_DeliveryDestination`.`Incre_AddressNote`,
        `T_DeliveryDestination`.`Incre_SameCnAndAddrScore`,
        `T_DeliveryDestination`.`Incre_SameCnAndAddrNote`,
        `T_DeliveryDestination`.`Incre_PostalCodeScore`,
        `T_DeliveryDestination`.`Incre_PostalCodeNote`,
        `T_DeliveryDestination`.`Incre_ScoreTotal`,
        `T_DeliveryDestination`.`SearchDestNameKj`,
        convert_kana(`T_DeliveryDestination`.`SearchDestNameKn`),
        `T_DeliveryDestination`.`SearchPhone`,
        `T_DeliveryDestination`.`SearchUnitingAddress`,
        `T_DeliveryDestination`.`Incre_ArTel`,
        `T_DeliveryDestination`.`Incre_TelScore`,
        `T_DeliveryDestination`.`Incre_TelNote`,
        `T_DeliveryDestination`.`RegDestNameKj`,
        `T_DeliveryDestination`.`RegUnitingAddress`,
        `T_DeliveryDestination`.`RegPhone`,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_DeliveryDestination`
    WHERE  `T_DeliveryDestination`.`DeliDestId` < 9000000;
END
$$

DELIMITER ;

