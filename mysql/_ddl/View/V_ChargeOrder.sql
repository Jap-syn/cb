DROP VIEW IF EXISTS `V_ChargeOrder`;

CREATE VIEW `V_ChargeOrder`
AS
SELECT `PAS`.`Seq` AS `Seq`
	,`PAS`.`OrderSeq` AS `OrderSeq`
	,`PAS`.`OccDate` AS `OccDate`
	,`PAS`.`UseAmount` AS `UseAmount`
	,`PAS`.`AppSettlementFeeRate` AS `AppSettlementFeeRate`
	,`PAS`.`SettlementFee` AS `SettlementFee`
	,`PAS`.`ClaimFee` AS `ClaimFee`
	,`PAS`.`ChargeAmount` AS `ChargeAmount`
	,`PAS`.`ClearConditionDate` AS `ClearConditionDate`
	,`ODR`.`EnterpriseId` AS `EnterpriseId`
	,`ODR`.`RegistDate` AS `RegistDate`
	,`ODR`.`ReceiptOrderDate` AS `ReceiptOrderDate`
FROM (
	`T_PayingAndSales` `PAS` JOIN `T_Order` `ODR`
	)
WHERE (
		(`PAS`.`OrderSeq` = `ODR`.`OrderSeq`)
		AND (`PAS`.`ClearConditionForCharge` = 1)
		AND (`PAS`.`ChargeDecisionFlg` = 0)
		AND (`PAS`.`CancelFlg` = 0)
		)
;
