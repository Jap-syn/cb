DROP VIEW IF EXISTS `V_StatSales`;

CREATE VIEW `V_StatSales`
AS
SELECT `ENT`.`EnterpriseId` AS `EnterpriseId`
	,`ENT`.`EnterpriseNameKj` AS `EnterpriseNameKj`
	,`PAS`.`OccDate` AS `OccDate`
	,`PAS`.`ChargeDecisionFlg` AS `ChargeDecisionFlg`
	,`PAS`.`ChargeDecisionDate` AS `ChargeDecisionDate`
	,`PAS`.`ChargeAmount` AS `ChargeAmount`
	,`PAS`.`SettlementFee` AS `SettlementFee`
	,`PAS`.`ClaimFee` AS `ClaimFee`
	,(`PAS`.`SettlementFee` + `PAS`.`ClaimFee`) AS `Uriage`
	,`PAS`.`ChargeDecisionFlg` AS `ChargeCount`
	,`PAS`.`UseAmount` AS `UseAmount`
	,`ORD`.`OrderSeq` AS `OrderSeq`
-- 	,`ORD`.`Clm_L_DamageInterestAmount` AS `Clm_L_DamageInterestAmount`
	,`CLM`.`DamageInterestAmount` AS `Clm_L_DamageInterestAmount`
-- 	,`ORD`.`Clm_L_ClaimFee` AS `Clm_L_ClaimFee`
	,`CLM`.`ClaimFee` AS `Clm_L_ClaimFee`
-- 	,`ORD`.`Clm_L_AdditionalClaimFee` AS `Clm_L_AdditionalClaimFee`
	,`CLM`.`AdditionalClaimFee` AS `Clm_L_AdditionalClaimFee`
-- 	,(((`ORD`.`UseAmount` + `ORD`.`Clm_L_DamageInterestAmount`) + `ORD`.`Clm_L_ClaimFee`) + `ORD`.`Clm_L_AdditionalClaimFee`) AS `ClaimAmount`
 	,(((`ORD`.`UseAmount` + `CLM`.`DamageInterestAmount`) + `CLM`.`ClaimFee`) + `CLM`.`AdditionalClaimFee`) AS `ClaimAmount`
	,(
		CASE 
			WHEN (`ORD`.`DataStatus` = 51)
				THEN 1
			ELSE 0
			END
		) AS `MisyuCnt`
	,(
		CASE 
			WHEN (`ORD`.`DataStatus` = 51)
-- 				THEN (((`ORD`.`UseAmount` + `ORD`.`Clm_L_DamageInterestAmount`) + `ORD`.`Clm_L_ClaimFee`) + `ORD`.`Clm_L_AdditionalClaimFee`)
				THEN (((`ORD`.`UseAmount` + `CLM`.`DamageInterestAmount`) + `CLM`.`ClaimFee`) + `CLM`.`AdditionalClaimFee`)
			ELSE 0
			END
		) AS `MisyuAmount`
	,`ORD`.`DataStatus` AS `DataStatus`
	,`ORD`.`CloseReason` AS `CloseReason`
	,`ORD`.`Rct_Status` AS `Rct_Status`
	,`ORD`.`Cnl_Status` AS `Cnl_Status`
--  	,`ORD`.`Clm_L_ClaimPattern` AS `Clm_L_ClaimPattern`
	,`CLM`.`ClaimPattern` AS `Clm_L_ClaimPattern`
FROM 
		`T_Enterprise` `ENT` 
        INNER JOIN `T_Order` `ORD` ON `ENT`.`EnterpriseId` = `ORD`.`EnterpriseId`
		INNER JOIN `T_PayingAndSales` `PAS` ON `ORD`.`OrderSeq` = `PAS`.`OrderSeq`
        LEFT JOIN `T_ClaimControl` `CLM` ON `ORD`.`P_OrderSeq` = `CLM`.`OrderSeq`	
;
