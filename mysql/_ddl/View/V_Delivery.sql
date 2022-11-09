DROP VIEW IF EXISTS `V_Delivery`;

CREATE VIEW `V_Delivery` AS
    SELECT 
        `OITM`.`OrderItemId` AS `OrderItemId`,
        `OITM`.`OrderSeq` AS `OrderSeq`,
        `OITM`.`ItemNameKj` AS `ItemNameKj`,
        `OITM`.`ItemNameKn` AS `ItemNameKn`,
        `OITM`.`UnitPrice` AS `UnitPrice`,
        `OITM`.`ItemNum` AS `ItemNum`,
        `OITM`.`SumMoney` AS `SumMoney`,
        `OITM`.`DataClass` AS `DataClass`,
        `OITM`.`Incre_Score` AS `Incre_ItemScore`,
        `OITM`.`Incre_Note` AS `Incre_ItemNote`,
        `OITM`.`Deli_JournalIncDate` AS `Deli_JournalIncDate`,
        `OITM`.`Deli_DeliveryMethod` AS `Deli_DeliveryMethod`,
        `MDM`.`DeliMethodName` AS `Deli_DeliveryMethodCaption`,
        `MDM`.`EnableCancelFlg` AS `Deli_EnableCancelFlg`,
        `MDM`.`PayChgCondition` AS `Deli_PayChgCondition`,
        `OITM`.`Deli_JournalNumber` AS `Deli_JournalNumber`,
        `OITM`.`Deli_ShipDate` AS `Deli_ShipDate`,
        `OITM`.`Deli_ConfirmArrivalFlg` AS `Deli_ConfirmArrivalFlg`,
        `OITM`.`Deli_ConfirmNoArrivalReason` AS `Deli_ConfirmNoArrivalReason`,
        `OITM`.`Deli_ConfirmArrivalDate` AS `Deli_ConfirmArrivalDate`,
        `OITM`.`Deli_ConfirmArrivalOpId` AS `Deli_ConfirmArrivalOpId`,
        `DDST`.`DeliDestId` AS `DeliDestId`,
        `DDST`.`DestNameKj` AS `DestNameKj`,
        `DDST`.`DestNameKn` AS `DestNameKn`,
        `DDST`.`PostalCode` AS `PostalCode`,
        `DDST`.`PrefectureCode` AS `PrefectureCode`,
        `DDST`.`PrefectureName` AS `PrefectureName`,
        `DDST`.`City` AS `City`,
        `DDST`.`Town` AS `Town`,
        `DDST`.`Building` AS `Building`,
        `DDST`.`UnitingAddress` AS `UnitingAddress`,
        `DDST`.`Hash_Name` AS `Hash_Name`,
        `DDST`.`Hash_Address` AS `Hash_Address`,
        `DDST`.`Phone` AS `Phone`,
        `DDST`.`Incre_ArName` AS `Incre_ArName`,
        `DDST`.`Incre_NameScore` AS `Incre_NameScore`,
        `DDST`.`Incre_NameNote` AS `Incre_NameNote`,
        `DDST`.`Incre_ArAddr` AS `Incre_ArAddr`,
        `DDST`.`Incre_AddressScore` AS `Incre_AddressScore`,
        `DDST`.`Incre_AddressNote` AS `Incre_AddressNote`,
        `DDST`.`Incre_SameCnAndAddrScore` AS `Incre_SameCnAndAddrScore`,
        `DDST`.`Incre_SameCnAndAddrNote` AS `Incre_SameCnAndAddrNote`,
        `DDST`.`Incre_PostalCodeScore` AS `Incre_PostalCodeScore`,
        `DDST`.`Incre_PostalCodeNote` AS `Incre_PostalCodeNote`,
        `DDST`.`Incre_ScoreTotal` AS `Incre_ScoreTotal`,
        `DDST`.`Incre_ArTel` AS `Incre_ArTel`,
        `DDST`.`Incre_TelScore` AS `Incre_TelScore`,
        `DDST`.`Incre_TelNote` AS `Incre_TelNote`
    FROM
        ((`T_OrderItems` `OITM`
        INNER JOIN `T_DeliveryDestination` `DDST` ON ((`OITM`.`DeliDestId` = `DDST`.`DeliDestId`) AND (`DDST`.`ValidFlg` = 1)))
        LEFT JOIN `M_DeliveryMethod` `MDM` ON ((`OITM`.`Deli_DeliveryMethod` = `MDM`.`DeliMethodId`)))
   WHERE `OITM`.`ValidFlg` = 1
;
