DROP VIEW IF EXISTS `V_ArrivalConfirm2`;

CREATE VIEW `V_ArrivalConfirm2`
AS
SELECT `ORDC`.`OrderSeq` AS `OrderSeq`
	,`ORDC`.`DataStatus` AS `DataStatus`
	,`ORDC`.`CloseReason` AS `CloseReason`
	,`ORDC`.`UseAmount` AS `UseAmount`
	,`ORDC`.`OutOfAmends` AS `OutOfAmends`
	,`OITM`.`Deli_JournalIncDate` AS `Deli_JournalIncDate`
	,`OITM`.`Deli_DeliveryMethod` AS `Deli_DeliveryMethod`
	,`MDM`.`DeliMethodName` AS `Deli_DeliveryMethodCaption`
	,`MDM`.`EnableCancelFlg` AS `Deli_EnableCancelFlg`
	,`MDM`.`PayChgCondition` AS `Deli_PayChgCondition`
	,`OITM`.`Deli_JournalNumber` AS `Deli_JournalNumber`
	,`DDST`.`DestNameKj` AS `DestNameKj`
	,`DDST`.`PostalCode` AS `PostalCode`
	,`DDST`.`UnitingAddress` AS `UnitingAddress`
	,`DDST`.`Phone` AS `Phone`
	,`OITM`.`Deli_ConfirmArrivalFlg` AS `Deli_ConfirmArrivalFlg`
	,`OITM`.`Deli_ConfirmNoArrivalReason` AS `Deli_ConfirmNoArrivalReason`
	,`OITM`.`Deli_ConfirmArrivalDate` AS `Deli_ConfirmArrivalDate`
	,`OITM`.`Deli_ConfirmArrivalOpId` AS `Deli_ConfirmArrivalOpId`
	,`ENT`.`EnterpriseNameKj` AS `EnterpriseNameKj`
-- 	,`ENT`.`FixPattern` AS `FixPattern`
	,`ENT`.`PayingCycleId` AS `PayingCycleId`
FROM (
	(
		(
			(
				`T_Order` `ORDC` JOIN `T_Enterprise` `ENT` ON ((`ORDC`.`EnterpriseId` = `ENT`.`EnterpriseId`))
				) JOIN `T_OrderItems` `OITM` ON ((`ORDC`.`OrderSeq` = `OITM`.`OrderSeq`))
			) JOIN `T_DeliveryDestination` `DDST` ON ((`OITM`.`DeliDestId` = `DDST`.`DeliDestId`))
		) JOIN `M_DeliveryMethod` `MDM` ON ((`OITM`.`Deli_DeliveryMethod` = `MDM`.`DeliMethodId`))
	)
WHERE (
		(`ORDC`.`Cnl_Status` = 0)
		AND (`ORDC`.`DataStatus` > 31)
		AND (
			isnull(`ORDC`.`CloseReason`)
			OR (
				`ORDC`.`CloseReason` IN (
					0
					,1
					)
				)
			)
		AND (
			`OITM`.`Deli_ConfirmArrivalFlg` IN (
				- (1)
				,0
				)
			)
		AND (
			isnull(`ORDC`.`OutOfAmends`)
			OR (`ORDC`.`OutOfAmends` = 0)
			)
		AND (
			`OITM`.`OrderItemId` = (
-- 				SELECT min(`T_OrderItems`.`OrderItemId`) AS `MIN(OrderItemId) `
				SELECT min(`T_OrderItems`.`OrderItemId`) 
				FROM `T_OrderItems`
				WHERE (`T_OrderItems`.`OrderSeq` = `ORDC`.`OrderSeq`)
				)
			)
		)
;
