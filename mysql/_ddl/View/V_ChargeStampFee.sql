DROP VIEW IF EXISTS `V_ChargeStampFee`;

CREATE VIEW `V_ChargeStampFee`
AS
SELECT `STF`.`Seq` AS `Seq`
	,`STF`.`OrderSeq` AS `OrderSeq`
	,`STF`.`DecisionDate` AS `DecisionDate`
	,`STF`.`StampFee` AS `StampFee`
	,`ODR`.`EnterpriseId` AS `EnterpriseId`
	,`ODR`.`RegistDate` AS `RegistDate`
	,`ODR`.`ReceiptOrderDate` AS `ReceiptOrderDate`
FROM (
	`T_StampFee` `STF` JOIN `T_Order` `ODR`
	)
WHERE (
		(`STF`.`OrderSeq` = `ODR`.`OrderSeq`)
		AND (`STF`.`ClearFlg` = 0)
		AND (`STF`.`CancelFlg` = 0)
		)
;
