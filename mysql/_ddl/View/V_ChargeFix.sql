DROP VIEW IF EXISTS `V_ChargeFix`;

CREATE VIEW `V_ChargeFix`
AS
SELECT `ORD`.`OrderSeq` AS `OrderSeq`
	,`ORD`.`Chg_Seq` AS `Chg_Seq`
	,`ORD`.`EnterpriseId` AS `EnterpriseId`
	,`ORD`.`OrderId` AS `OrderId`
	,`ORD`.`ReceiptOrderDate` AS `ReceiptOrderDate`
	,`CUS`.`NameKj` AS `NameKj`
    ,`CUS`.`CustomerId` AS `CustomerId`
	,`ORD`.`SiteId` AS `SiteId`
	,`ORD`.`UseAmount` AS `UseAmount`
	,`PAS`.`SettlementFee` AS `SettlementFee`
	,`PAS`.`ClaimFee` AS `ClaimFee`
	,(
-- 		SELECT sum(`T_StampFee`.`StampFee`) AS `SUM(StampFee) `
		SELECT sum(`T_StampFee`.`StampFee`)
		FROM `T_StampFee`
		WHERE (
				(`T_StampFee`.`OrderSeq` = `ORD`.`OrderSeq`)
				AND (`T_StampFee`.`ClearFlg` = 1)
				)
		) AS `StampFee`
-- 	,`ORD`.`Rct_ReceiptMethod` AS `Rct_ReceiptMethod`
	,`RCT`.`ReceiptClass` AS `ReceiptClass`
	,`ORD`.`Chg_ChargeAmount` AS `Chg_ChargeAmount`
FROM `T_Order` `ORD`
INNER JOIN `T_Customer` `CUS` ON `ORD`.`OrderSeq` = `CUS`.`OrderSeq`
INNER JOIN `T_PayingAndSales` `PAS` ON `ORD`.`OrderSeq` = `PAS`.`OrderSeq`
LEFT  JOIN `T_ClaimControl` `CLM` ON `ORD`.`P_OrderSeq` = `CLM`.`OrderSeq`
LEFT  JOIN `T_ReceiptControl` `RCT` ON `CLM`.`LastReceiptSeq` = `RCT`.`ReceiptSeq`
;
