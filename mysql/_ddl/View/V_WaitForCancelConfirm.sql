DROP VIEW IF EXISTS `V_WaitForCancelConfirm`;

CREATE VIEW `V_WaitForCancelConfirm`
AS
SELECT `VOC`.`OrderSeq` AS `OrderSeq`
	,`VOC`.`OrderId` AS `OrderId`
	,`VOC`.`RegistDate` AS `RegistDate`
	,`VOC`.`ReceiptOrderDate` AS `ReceiptOrderDate`
	,`VOC`.`SiteId` AS `SiteId`
	,`VOC`.`UseAmount` AS `UseAmount`
	,`VOC`.`AnotherDeliFlg` AS `AnotherDeliFlg`
	,`VOC`.`DataStatus` AS `DataStatus`
	,`VOC`.`CloseReason` AS `CloseReason`
	,`VOC`.`IncreArCaption` AS `IncreArCaption`
	,`VOC`.`IncreArLongCaption` AS `IncreArLongCaption`
	,`VOC`.`Incre_Status` AS `Incre_Status`
	,`VOC`.`Incre_AtnEnterpriseScore` AS `Incre_AtnEnterpriseScore`
	,`VOC`.`Incre_AtnEnterpriseNote` AS `Incre_AtnEnterpriseNote`
	,`VOC`.`Incre_BorderScore` AS `Incre_BorderScore`
	,`VOC`.`Incre_BorderNote` AS `Incre_BorderNote`
	,`VOC`.`Incre_LimitCheckScore` AS `Incre_LimitCheckScore`
	,`VOC`.`Incre_LimitCheckNote` AS `Incre_LimitCheckNote`
	,`VOC`.`Incre_ScoreTotal` AS `Incre_ScoreTotal`
	,`VOC`.`Incre_DecisionDate` AS `Incre_DecisionDate`
	,`VOC`.`Incre_DecisionOpId` AS `Incre_DecisionOpId`
	,`VOC`.`Incre_Note` AS `Incre_Note`
	,`VOC`.`Dmi_Status` AS `Dmi_Status`
	,`VOC`.`Dmi_ResponseCode` AS `Dmi_ResponseCode`
	,`VOC`.`Dmi_ResponseNote` AS `Dmi_ResponseNote`
	,`VOC`.`Dmi_DecisionDate` AS `Dmi_DecisionDate`
	,`VOC`.`Dmi_DecSeqId` AS `Dmi_DecSeqId`
	,`VOC`.`Clm_Count` AS `Clm_Count`
	,`VOC`.`Clm_F_ClaimDate` AS `Clm_F_ClaimDate`
	,`VOC`.`Clm_F_OpId` AS `Clm_F_OpId`
	,`VOC`.`Clm_F_LimitDate` AS `Clm_F_LimitDate`
	,`VOC`.`Clm_L_ClaimDate` AS `Clm_L_ClaimDate`
	,`VOC`.`Clm_L_OpId` AS `Clm_L_OpId`
	,`VOC`.`Clm_L_ClaimPattern` AS `Clm_L_ClaimPattern`
	,`VOC`.`Clm_L_LimitDate` AS `Clm_L_LimitDate`
	,`VOC`.`Clm_L_DamageDays` AS `Clm_L_DamageDays`
	,`VOC`.`Clm_L_DamageBaseDate` AS `Clm_L_DamageBaseDate`
	,`VOC`.`Clm_L_DamageInterestAmount` AS `Clm_L_DamageInterestAmount`
	,`VOC`.`Clm_L_ClaimFee` AS `Clm_L_ClaimFee`
	,`VOC`.`Clm_L_AdditionalClaimFee` AS `Clm_L_AdditionalClaimFee`
	,`VOC`.`Chg_Status` AS `Chg_Status`
	,`VOC`.`Chg_FixedDate` AS `Chg_FixedDate`
	,`VOC`.`Chg_DecisionDate` AS `Chg_DecisionDate`
	,`VOC`.`Chg_ExecDate` AS `Chg_ExecDate`
	,`VOC`.`Chg_ChargeAmount` AS `Chg_ChargeAmount`
	,`VOC`.`Rct_RejectFlg` AS `Rct_RejectFlg`
	,`VOC`.`Rct_RejectReason` AS `Rct_RejectReason`
	,`VOC`.`Rct_Status` AS `Rct_Status`
--	,`VOC`.`Rct_ReceiptProcessDate` AS `Rct_ReceiptProcessDate`
--	,`VOC`.`Rct_ReceiptDate` AS `Rct_ReceiptDate`
--	,`VOC`.`Rct_ReceiptAmount` AS `Rct_ReceiptAmount`
-- 	,`VOC`.`Rct_ReceiptMethod` AS `Rct_ReceiptMethod`
-- 	,`VOC`.`Rct_ReceiptClass` AS `Rct_ReceiptClass`
-- 	,`VOC`.`Rct_DifferentialAmount` AS `Rct_DifferentialAmount`
--	,`VOC`.`ClaimedBalance` AS `ClaimedBalance`
-- 	,`VOC`.`Rct_DepositOccDate` AS `Rct_DepositOccDate`
-- 	,`VOC`.`Rct_DepositAmount` AS `Rct_DepositAmount`
-- 	,`VOC`.`Rct_DepositClearFlg` AS `Rct_DepositClearFlg`
	,`VOC`.`Cnl_CantCancelFlg` AS `Cnl_CantCancelFlg`
	,`VOC`.`Cnl_Status` AS `Cnl_Status`
	,`VOC`.`Dmg_DecisionFlg` AS `Dmg_DecisionFlg`
	,`VOC`.`Dmg_DecisionDate` AS `Dmg_DecisionDate`
	,`VOC`.`Dmg_DecisionAmount` AS `Dmg_DecisionAmount`
	,`VOC`.`Dmg_DecisionReason` AS `Dmg_DecisionReason`
	,`VOC`.`Ent_OrderId` AS `Ent_OrderId`
	,`VOC`.`Ent_Note` AS `Ent_Note`
	,`VOC`.`Bekkan` AS `Bekkan`
	,`VOC`.`StopClaimFlg` AS `StopClaimFlg`
	,`VOC`.`MailPaymentSoonDate` AS `MailPaymentSoonDate`
	,`VOC`.`MailLimitPassageDate` AS `MailLimitPassageDate`
	,`VOC`.`MailLimitPassageCount` AS `MailLimitPassageCount`
	,`VOC`.`ReturnClaimFlg` AS `ReturnClaimFlg`
	,`VOC`.`RemindClass` AS `RemindClass`
	,`VOC`.`TouchHistoryFlg` AS `TouchHistoryFlg`
	,`VOC`.`BriefNote` AS `BriefNote`
	,`VOC`.`LonghandLetter` AS `LonghandLetter`
	,`VOC`.`VisitFlg` AS `VisitFlg`
	,`VOC`.`FinalityCollectionMean` AS `FinalityCollectionMean`
	,`VOC`.`FinalityRemindDate` AS `FinalityRemindDate`
	,`VOC`.`FinalityRemindOpId` AS `FinalityRemindOpId`
	,`VOC`.`PromPayDate` AS `PromPayDate`
	,`VOC`.`ClaimStopReleaseDate` AS `ClaimStopReleaseDate`
	,`VOC`.`LetterClaimStopFlg` AS `LetterClaimStopFlg`
	,`VOC`.`MailClaimStopFlg` AS `MailClaimStopFlg`
	,`VOC`.`InstallmentPlanAmount` AS `InstallmentPlanAmount`
	,`VOC`.`CustomerId` AS `CustomerId`
	,`VOC`.`NameKj` AS `NameKj`
	,`VOC`.`NameKn` AS `NameKn`
	,`VOC`.`PostalCode` AS `PostalCode`
	,`VOC`.`PrefectureCode` AS `PrefectureCode`
	,`VOC`.`PrefectureName` AS `PrefectureName`
	,`VOC`.`City` AS `City`
	,`VOC`.`Town` AS `Town`
	,`VOC`.`Building` AS `Building`
	,`VOC`.`UnitingAddress` AS `UnitingAddress`
	,`VOC`.`Hash_Name` AS `Hash_Name`
	,`VOC`.`Hash_Address` AS `Hash_Address`
	,`VOC`.`Phone` AS `Phone`
	,`VOC`.`RealCallStatus` AS `RealCallStatus`
	,`VOC`.`RealCallResult` AS `RealCallResult`
	,`VOC`.`RealCallScore` AS `RealCallScore`
	,`VOC`.`eDen` AS `eDen`
	,`VOC`.`MailAddress` AS `MailAddress`
	,`VOC`.`RealSendMailStatus` AS `RealSendMailStatus`
	,`VOC`.`RealSendMailResult` AS `RealSendMailResult`
	,`VOC`.`RealSendMailScore` AS `RealSendMailScore`
	,`VOC`.`Occupation` AS `Occupation`
	,`VOC`.`Incre_ArName` AS `Incre_ArName`
	,`VOC`.`Incre_NameScore` AS `Incre_NameScore`
	,`VOC`.`Incre_NameNote` AS `Incre_NameNote`
	,`VOC`.`Incre_ArAddr` AS `Incre_ArAddr`
	,`VOC`.`Incre_AddressScore` AS `Incre_AddressScore`
	,`VOC`.`Incre_AddressNote` AS `Incre_AddressNote`
	,`VOC`.`Incre_MailDomainScore` AS `Incre_MailDomainScore`
	,`VOC`.`Incre_MailDomainNote` AS `Incre_MailDomainNote`
	,`VOC`.`Incre_PostalCodeScore` AS `Incre_PostalCodeScore`
	,`VOC`.`Incre_PostalCodeNote` AS `Incre_PostalCodeNote`
	,`VOC`.`Incre_CusScoreTotal` AS `Incre_CusScoreTotal`
	,`VOC`.`PhoneHistory` AS `PhoneHistory`
	,`VOC`.`Carrier` AS `Carrier`
	,`VOC`.`ValidTel` AS `ValidTel`
	,`VOC`.`ValidMail` AS `ValidMail`
	,`VOC`.`ValidAddress` AS `ValidAddress`
	,`VOC`.`ResidentCard` AS `ResidentCard`
	,`VOC`.`Cinfo1` AS `Cinfo1`
	,`VOC`.`CinfoNote1` AS `CinfoNote1`
	,`VOC`.`CinfoStatus1` AS `CinfoStatus1`
	,`VOC`.`Cinfo2` AS `Cinfo2`
	,`VOC`.`CinfoNote2` AS `CinfoNote2`
	,`VOC`.`CinfoStatus2` AS `CinfoStatus2`
	,`VOC`.`Cinfo3` AS `Cinfo3`
	,`VOC`.`CinfoNote3` AS `CinfoNote3`
	,`VOC`.`CinfoStatus3` AS `CinfoStatus3`
	,`VOC`.`SearchNameKj` AS `SearchNameKj`
	,`VOC`.`SearchNameKn` AS `SearchNameKn`
	,`VOC`.`SearchPhone` AS `SearchPhone`
	,`VOC`.`EnterpriseId` AS `EnterpriseId`
	,`VOC`.`EnterpriseLoginId` AS `EnterpriseLoginId`
	,`VOC`.`EnterpriseNameKj` AS `EnterpriseNameKj`
	,`VOC`.`CpNameKj` AS `CpNameKj`
	,`VOC`.`ContactPhoneNumber` AS `ContactPhoneNumber`
	,`VOC`.`EntMailAddress` AS `EntMailAddress`
-- 	,`VOC`.`ClaimFee` AS `ClaimFee`
    ,`VOC`.`ClaimFeeDK` AS `ClaimFeeDK`
    ,`VOC`.`ClaimFeeBS` AS `ClaimFeeBS`
	,`VOC`.`ReClaimFee` AS `ReClaimFee`
	,`VOC`.`SiteNameKj` AS `SiteNameKj`
	,`CNL`.`Seq` AS `Seq`
	,`CNL`.`CancelDate` AS `CancelDate`
	,`CNL`.`CancelPhase` AS `CancelPhase`
	,`CNL`.`CancelReason` AS `CancelReason`
	,`CNL`.`RepayTotal` AS `RepayTotal`
FROM (
	`V_OrderCustomer` `VOC` JOIN `T_Cancel` `CNL`
	)
WHERE (
		(`VOC`.`OrderSeq` = `CNL`.`OrderSeq`)
		AND (`VOC`.`Cnl_Status` = 1)
		AND (`CNL`.`ValidFlg`   = 1)
		)
;