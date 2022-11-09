CREATE VIEW `V_CbSearche`
AS
SELECT `ENT`.`EnterpriseId` AS `EnterpriseId`
	,`ENT`.`ApplicationDate` AS `ApplicationDate`
	,`ENT`.`PublishingConfirmDate` AS `PublishingConfirmDate`
	,`ENT`.`ServiceInDate` AS `ServiceInDate`
	,`ENT`.`DocCollect` AS `DocCollect`
	,`ENT`.`ExaminationResult` AS `ExaminationResult`
	,`ENT`.`RegistDate` AS `RegistDate`
	,`ENT`.`LoginId` AS `LoginId`
	,`ENT`.`LoginPasswd` AS `LoginPasswd`
	,`ENT`.`EnterpriseNameKj` AS `EnterpriseNameKj`
	,`ENT`.`EnterpriseNameKn` AS `EnterpriseNameKn`
	,`ENT`.`PostalCode` AS `PostalCode`
	,`ENT`.`PrefectureCode` AS `PrefectureCode`
	,`ENT`.`PrefectureName` AS `PrefectureName`
	,`ENT`.`City` AS `City`
	,`ENT`.`Town` AS `Town`
	,`ENT`.`Building` AS `Building`
	,`ENT`.`UnitingAddress` AS `UnitingAddress`
	,`ENT`.`RepNameKj` AS `RepNameKj`
	,`ENT`.`RepNameKn` AS `RepNameKn`
	,`ENT`.`Phone` AS `Phone`
	,`ENT`.`Fax` AS `Fax`
	,`ENT`.`PreSales` AS `PreSales`
	,`ENT`.`Industry` AS `Industry`
	,`ENT`.`Plan` AS `Plan`
	,`ENT`.`MonthlyFee` AS `MonthlyFee`
	,`ENT`.`SettlementAmountLimit` AS `SettlementAmountLimit`
	,`ENT`.`SettlementFeeRate` AS `SettlementFeeRate`
	,`ENT`.`ClaimFee` AS `ClaimFee`
	,`ENT`.`ReClaimFee` AS `ReClaimFee`
-- 	,`ENT`.`FixPattern` AS `FixPattern`
	,`ENT`.`PayingCycleId` AS `PayingCycleId`
	,`ENT`.`Salesman` AS `Salesman`
	,`ENT`.`FfName` AS `FfName`
	,`ENT`.`FfCode` AS `FfCode`
	,`ENT`.`FfBranchName` AS `FfBranchName`
	,`ENT`.`FfBranchCode` AS `FfBranchCode`
	,`ENT`.`FfAccountNumber` AS `FfAccountNumber`
	,`ENT`.`FfAccountClass` AS `FfAccountClass`
	,`ENT`.`FfAccountName` AS `FfAccountName`
	,`ENT`.`TcClass` AS `TcClass`
	,`ENT`.`CpNameKj` AS `CpNameKj`
	,`ENT`.`CpNameKn` AS `CpNameKn`
	,`ENT`.`DivisionName` AS `DivisionName`
	,`ENT`.`MailAddress` AS `MailAddress`
	,`ENT`.`ContactPhoneNumber` AS `ContactPhoneNumber`
	,`ENT`.`ContactFaxNumber` AS `ContactFaxNumber`
	,`ENT`.`Note` AS `Note`
	,`ENT`.`B_ChargeFixedDate` AS `B_ChargeFixedDate`
	,`ENT`.`B_ChargeDecisionDate` AS `B_ChargeDecisionDate`
	,`ENT`.`B_ChargeExecDate` AS `B_ChargeExecDate`
	,`ENT`.`N_ChargeFixedDate` AS `N_ChargeFixedDate`
	,`ENT`.`N_ChargeDecisionDate` AS `N_ChargeDecisionDate`
	,`ENT`.`N_ChargeExecDate` AS `N_ChargeExecDate`
	,`ENT`.`N_MonthlyFee` AS `N_MonthlyFee`
	,`ENT`.`ValidFlg` AS `ValidFlg`
	,`ENT`.`InvalidatedDate` AS `InvalidatedDate`
	,`ENT`.`InvalidatedReason` AS `InvalidatedReason`
	,`ENT`.`AutoCreditJudgeMode` AS `AutoCreditJudgeMode`
	,`SIT`.`SiteId` AS `SiteId`
	,`SIT`.`SiteNameKj` AS `SiteNameKj`
	,`SIT`.`SiteNameKn` AS `SiteNameKn`
	,`SIT`.`NickName` AS `NickName`
	,`SIT`.`Url` AS `Url`
	,`SIT`.`ReqMailAddrFlg` AS `ReqMailAddrFlg`
	,`SIT`.`SiteForm` AS `SiteForm`
FROM (
	`T_Enterprise` `ENT` JOIN `T_Site` `SIT`
	)
WHERE (`ENT`.`EnterpriseId` = `SIT`.`EnterpriseId`)