DROP VIEW IF EXISTS `V_ChargeCancel`;

CREATE VIEW `V_ChargeCancel`
AS
SELECT `CNL`.`Seq` AS `Seq`
	,`CNL`.`OrderSeq` AS `OrderSeq`
	,`CNL`.`CancelDate` AS `CancelDate`
	,`CNL`.`CancelPhase` AS `CancelPhase`
	,`CNL`.`CancelReason` AS `CancelReason`
	,`CNL`.`RepayChargeAmount` AS `RepayChargeAmount`
	,`CNL`.`RepaySettlementFee` AS `RepaySettlementFee`
	,`CNL`.`RepayClaimFee` AS `RepayClaimFee`
	,`CNL`.`RepayStampFee` AS `RepayStampFee`
	,`CNL`.`RepayDamageInterest` AS `RepayDamageInterest`
	,`CNL`.`RepayReClaimFee` AS `RepayReClaimFee`
	,`CNL`.`RepayDifferentialAmount` AS `RepayDifferentialAmount`
	,`CNL`.`RepayDepositAmount` AS `RepayDepositAmount`
	,`CNL`.`RepayReceiptAmount` AS `RepayReceiptAmount`
	,`CNL`.`RepayTotal` AS `RepayTotal`
	,`CNL`.`ApprovalDate` AS `ApprovalDate`
	,`CNL`.`ApproveOpId` AS `ApproveOpId`
	,`ODR`.`EnterpriseId` AS `EnterpriseId`
	,`ODR`.`RegistDate` AS `RegistDate`
	,`ODR`.`ReceiptOrderDate` AS `ReceiptOrderDate`
FROM (
	`T_Cancel` `CNL` JOIN `T_Order` `ODR`
	)
WHERE (
		(`CNL`.`OrderSeq` = `ODR`.`OrderSeq`)
		AND (`CNL`.`ApproveFlg` = 1)
		AND (`CNL`.`KeepAnAccurateFlg` = 0)
		AND (`CNL`.`ValidFlg` = 1)
		)
;
