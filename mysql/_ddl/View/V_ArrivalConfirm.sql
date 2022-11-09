DROP VIEW IF EXISTS `V_ArrivalConfirm`;

CREATE VIEW `V_ArrivalConfirm`
AS
SELECT `ORDC`.`OrderSeq` AS `OrderSeq`
    ,`ORDC`.`DataStatus` AS `DataStatus`
    ,`ORDC`.`CloseReason` AS `CloseReason`
    ,`ORDC`.`UseAmount` AS `UseAmount`
    ,`ORDC`.`OutOfAmends` AS `OutOfAmends`
    ,`DELI`.`Deli_JournalIncDate` AS `Deli_JournalIncDate`
    ,`DELI`.`Deli_DeliveryMethod` AS `Deli_DeliveryMethod`
    ,`DELI`.`Deli_DeliveryMethodCaption` AS `Deli_DeliveryMethodCaption`
    ,`DELI`.`Deli_EnableCancelFlg` AS `Deli_EnableCancelFlg`
    ,`DELI`.`Deli_PayChgCondition` AS `Deli_PayChgCondition`
    ,`DELI`.`Deli_JournalNumber` AS `Deli_JournalNumber`
    ,`DELI`.`DestNameKj` AS `DestNameKj`
    ,`DELI`.`PostalCode` AS `PostalCode`
    ,`DELI`.`UnitingAddress` AS `UnitingAddress`
    ,`DELI`.`Phone` AS `Phone`
    ,`DELI`.`Deli_ConfirmArrivalFlg` AS `Deli_ConfirmArrivalFlg`
    ,`DELI`.`Deli_ConfirmNoArrivalReason` AS `Deli_ConfirmNoArrivalReason`
    ,`DELI`.`Deli_ConfirmArrivalDate` AS `Deli_ConfirmArrivalDate`
    ,`DELI`.`Deli_ConfirmArrivalOpId` AS `Deli_ConfirmArrivalOpId`
    ,`ENT`.`EnterpriseNameKj` AS `EnterpriseNameKj`
--  ,`ENT`.`FixPattern` AS `FixPattern`
    ,`MPC`.`FixPattern` AS `FixPattern`
    ,`ENT`.`PayingCycleId` AS `PayingCycleId`
FROM    `T_Order` `ORDC`
        INNER JOIN `V_Delivery` `DELI`
                ON `ORDC`.`OrderSeq` = `DELI`.`OrderSeq`
        INNER JOIN `T_Enterprise` `ENT`
                ON `ORDC`.`EnterpriseId` = `ENT`.`EnterpriseId`
        INNER JOIN `M_PayingCycle` `MPC`
                ON `ENT`.`PayingCycleId` = `MPC`.`PayingCycleId`

WHERE   `ORDC`.`Cnl_Status` = 0
        AND (
            `DELI`.`OrderItemId` = (
--              SELECT min(`T_OrderItems`.`OrderItemId`) AS `MIN(OrderItemId) `
                SELECT min(`T_OrderItems`.`OrderItemId`)
                FROM `T_OrderItems`
                WHERE (`T_OrderItems`.`OrderSeq` = `ORDC`.`OrderSeq`)
                )
            )
;
