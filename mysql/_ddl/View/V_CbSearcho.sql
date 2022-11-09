DROP VIEW IF EXISTS `V_CbSearcho`;

CREATE VIEW `V_CbSearcho`
AS
SELECT DISTINCT `ORD`.`OrderSeq` AS `OrderSeq`
	,`ORD`.`OrderId` AS `OrderId`
	,`ORD`.`RegistDate` AS `RegistDate`
	,`ORD`.`ReceiptOrderDate` AS `ReceiptOrderDate`
	,`CUS`.`Incre_ArAddr` AS `Incre_ArAddr`
	,`ORD`.`Bekkan` AS `Bekkan`
	,`CUS`.`NameKj` AS `NameKj`
	,`CUS`.`NameKn` AS `NameKn`
	,`CUS`.`UnitingAddress` AS `UnitingAddress`
	,`CUS`.`Phone` AS `Phone`
	,`CUS`.`MailAddress` AS `MailAddress`
	,`DELI`.`DestNameKj` AS `DestNameKj`
	,`DELI`.`DestNameKn` AS `DestNameKn`
	,`DELI`.`UnitingAddress` AS `DeliUnitingAddress`
	,`DELI`.`Phone` AS `DeliPhone`
 	,`ENT`.`LoginId` AS `LoginId`
	,`ENT`.`EnterpriseNameKj` AS `EnterpriseNameKj`
	,`SITE`.`SiteNameKj` AS `SiteNameKj`
	,`ORD`.`Incre_Status` AS `Incre_Status`
	,`ORD`.`Incre_ScoreTotal` AS `Incre_ScoreTotal`
	,`ORD`.`Dmi_Status` AS `Dmi_Status`
	,(
-- 		SELECT count(0) AS `COUNT(*) `
		SELECT count(0)
		FROM `T_OrderItems`
		WHERE (
				(`T_OrderItems`.`OrderSeq` = `ORD`.`OrderSeq`)
				AND (`T_OrderItems`.`DataClass` = 1)
				AND isnull(`T_OrderItems`.`Deli_JournalIncDate`)
				)
		) AS `NotIncJournalCount`
	,(
-- 		SELECT count(0) AS `COUNT(*) `
		SELECT count(0)
		FROM `T_OrderItems`
		WHERE (
				(`T_OrderItems`.`OrderSeq` = `ORD`.`OrderSeq`)
				AND (`T_OrderItems`.`DataClass` = 1)
				AND (`T_OrderItems`.`Deli_ConfirmArrivalFlg` = 1)
				)
		) AS `ArrivalCount`
	,`ORD`.`DataStatus` AS `DataStatus`
-- 	,`ORD`.`Clm_F_ClaimDate` AS `Clm_F_ClaimDate`
-- 	,`ORD`.`Clm_F_LimitDate` AS `Clm_F_LimitDate`
	,`CLM`.`F_ClaimDate` AS `Clm_F_ClaimDate`
	,`CLM`.`F_LimitDate` AS `Clm_F_LimitDate`
	,`ORD`.`UseAmount` AS `UseAmount`
	,`ORD`.`Chg_Status` AS `Chg_Status`
	,`ORD`.`Chg_ExecDate` AS `Chg_ExecDate`
-- 	,`ORD`.`Rct_ReceiptProcessDate` AS `Rct_ReceiptProcessDate`
-- 	,`ORD`.`Rct_ReceiptDate` AS `Rct_ReceiptDate`
-- 	,`ORD`.`Rct_ReceiptAmount` AS `Rct_ReceiptAmount`
-- 	,`ORD`.`Rct_ReceiptMethod` AS `Rct_ReceiptMethod`
	,`RCT`.`ReceiptProcessDate` AS `Rct_ReceiptProcessDate`
    ,`RCT`.`ReceiptDate` AS `Rct_ReceiptDate`
    ,`RCT`.`ReceiptAmount` AS `Rct_ReceiptAmount`
    ,`RCT`.`ReceiptClass` AS `ReceiptClass`
/*	,(
		CASE
			WHEN (`ORD`.`Rct_ReceiptMethod` = 1)
				THEN _utf8 'コンビニ'
			WHEN (`ORD`.`Rct_ReceiptMethod` = 2)
				THEN _utf8 '郵便局'
			WHEN (`ORD`.`Rct_ReceiptMethod` = 3)
				THEN _utf8 '銀行'
			ELSE _utf8 ''
			END
		) AS `Rct_ReceiptMethodLabel`
*/
-- 	,`ORD`.`Clm_L_ClaimDate` AS `Clm_L_ClaimDate`
-- 	,`ORD`.`Clm_L_LimitDate` AS `Clm_L_LimitDate`
 	,`CLM`.`ClaimDate` AS `Clm_L_ClaimDate`
 	,`CLM`.`LimitDate` AS `Clm_L_LimitDate`
	,(
-- 		SELECT count(0) AS `COUNT(*) `
		SELECT count(0)
		FROM `T_ClaimHistory`
		WHERE (
				(`T_ClaimHistory`.`OrderSeq` = `ORD`.`OrderSeq`)
				AND (`T_ClaimHistory`.`PrintedFlg` = 1)
				AND (`T_ClaimHistory`.`ClaimPattern` = 5)
				)
		) AS `NaiyoCount`
	,`ORD`.`Cnl_Status` AS `Cnl_Status`
	,(
-- 		SELECT max(`T_Cancel`.`ApprovalDate`) AS `MAX(ApprovalDate) `
		SELECT max(`T_Cancel`.`ApprovalDate`)
		FROM `T_Cancel`
		WHERE (
				(`T_Cancel`.`OrderSeq` = `ORD`.`OrderSeq`)
				AND (`T_Cancel`.`ApproveFlg` = 1)
				AND (`T_Cancel`.`ValidFlg`   = 1)
				)
		) AS `CancelConfirmDate`
	,`ORD`.`Incre_Note` AS `Incre_Note`
	,`ORD`.`Ent_OrderId` AS `Ent_OrderId`
	,`ITM`.`ItemNameKj` AS `ItemNameKj`
	,`ITM`.`UnitPrice` AS `UnitPrice`
	,`ITM`.`ItemNum` AS `ItemNum`
	,`ITM`.`Deli_JournalIncDate` AS `Deli_JournalIncDate`
	,`ITM`.`Deli_JournalNumber` AS `Deli_JournalNumber`
	,`MDM`.`DeliMethodName` AS `DeliMethodName`
FROM `T_Order` `ORD`
	INNER JOIN `T_Customer` `CUS` ON `ORD`.`OrderSeq` = `CUS`.`OrderSeq`
	INNER JOIN `T_OrderItems` `ITM` ON `ORD`.`OrderSeq` = `ITM`.`OrderSeq`
     LEFT JOIN `M_DeliveryMethod` `MDM` ON ((`ITM`.`Deli_DeliveryMethod` = `MDM`.`DeliMethodId`))
	INNER JOIN `T_DeliveryDestination` `DELI` ON `ITM`.`DeliDestId` = `DELI`.`DeliDestId`
	INNER JOIN `T_Enterprise` `ENT` ON `ORD`.`EnterpriseId` = `ENT`.`EnterpriseId`
	INNER JOIN `T_Site` `SITE` ON `ORD`.`SiteId` = `SITE`.`SiteId`
     LEFT JOIN `T_User` `USR` ON `USR`.`Seq` = `ENT`.`EnterpriseId` AND `USR`.`UserClass` = 2
     LEFT JOIN `T_ClaimControl` `CLM` ON `ORD`.`P_OrderSeq` = `CLM`.`OrderSeq`
     LEFT JOIN `T_ReceiptControl` `RCT` ON `ORD`.`P_OrderSeq` = `RCT`.`OrderSeq`
;
