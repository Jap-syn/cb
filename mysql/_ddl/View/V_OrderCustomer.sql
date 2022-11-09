DROP VIEW IF EXISTS `V_OrderCustomer`;

CREATE VIEW `V_OrderCustomer`
AS
SELECT `ORD`.`OrderSeq` AS `OrderSeq`
    ,`ORD`.`OrderId` AS `OrderId`
    ,`ORD`.`RegistDate` AS `RegistDate`
    ,`ORD`.`ReceiptOrderDate` AS `ReceiptOrderDate`
    ,`ORD`.`SiteId` AS `SiteId`
    ,`ORD`.`UseAmount` AS `UseAmount`
    ,`ORD`.`AnotherDeliFlg` AS `AnotherDeliFlg`
    ,`ORD`.`DataStatus` AS `DataStatus`
    ,`ORD`.`CloseReason` AS `CloseReason`
--  ,`MGP`.`ShortCaption` AS `IncreArCaption`
--  ,`MGP`.`Caption` AS `IncreArLongCaption`
    ,`MCD`.`Class2` AS `IncreArCaption`
    ,`MCD`.`KeyContent` AS `IncreArLongCaption`
    ,`ORD`.`Incre_Status` AS `Incre_Status`
    ,`ORD`.`Incre_AtnEnterpriseScore` AS `Incre_AtnEnterpriseScore`
    ,`ORD`.`Incre_AtnEnterpriseNote` AS `Incre_AtnEnterpriseNote`
    ,`ORD`.`Incre_BorderScore` AS `Incre_BorderScore`
    ,`ORD`.`Incre_BorderNote` AS `Incre_BorderNote`
    ,`ORD`.`Incre_LimitCheckScore` AS `Incre_LimitCheckScore`
    ,`ORD`.`Incre_LimitCheckNote` AS `Incre_LimitCheckNote`
    ,`ORD`.`Incre_ScoreTotal` AS `Incre_ScoreTotal`
    ,`ORD`.`Incre_DecisionDate` AS `Incre_DecisionDate`
    ,`ORD`.`Incre_DecisionOpId` AS `Incre_DecisionOpId`
    ,`ORD`.`Incre_Note` AS `Incre_Note`
    ,`ORD`.`Dmi_Status` AS `Dmi_Status`
    ,`ORD`.`Dmi_ResponseCode` AS `Dmi_ResponseCode`
    ,`ORD`.`Dmi_ResponseNote` AS `Dmi_ResponseNote`
    ,`ORD`.`Dmi_DecisionDate` AS `Dmi_DecisionDate`
    ,`ORD`.`Dmi_DecSeqId` AS `Dmi_DecSeqId`
--  ,`ORD`.`Clm_Count` AS `Clm_Count`
    ,`CLM`.`Clm_Count` AS `Clm_Count`
--  ,`ORD`.`Clm_F_ClaimDate` AS `Clm_F_ClaimDate`
--  ,`ORD`.`Clm_F_OpId` AS `Clm_F_OpId`
--  ,`ORD`.`Clm_F_LimitDate` AS `Clm_F_LimitDate`
    ,`CLM`.`F_ClaimDate` AS `Clm_F_ClaimDate`
    ,`CLM`.`F_OpId` AS `Clm_F_OpId`
    ,`CLM`.`F_LimitDate` AS `Clm_F_LimitDate`
--  ,`ORD`.`Clm_L_ClaimDate` AS `Clm_L_ClaimDate`
--  ,`ORD`.`Clm_L_OpId` AS `Clm_L_OpId`
--  ,`ORD`.`Clm_L_ClaimPattern` AS `Clm_L_ClaimPattern`
--  ,`ORD`.`Clm_L_LimitDate` AS `Clm_L_LimitDate`
--  ,`ORD`.`Clm_L_DamageDays` AS `Clm_L_DamageDays`
--  ,`ORD`.`Clm_L_DamageBaseDate` AS `Clm_L_DamageBaseDate`
--  ,`ORD`.`Clm_L_DamageInterestAmount` AS `Clm_L_DamageInterestAmount`
--  ,`ORD`.`Clm_L_ClaimFee` AS `Clm_L_ClaimFee`
--  ,`ORD`.`Clm_L_AdditionalClaimFee` AS `Clm_L_AdditionalClaimFee`
    ,`CLM`.`ClaimDate` AS `Clm_L_ClaimDate`
    ,`CLM`.`ClaimCpId` as `Clm_L_OpId`
    ,`CLM`.`ClaimPattern` AS `Clm_L_ClaimPattern`
    ,`CLM`.`LimitDate` AS `Clm_L_LimitDate`
    ,`CLM`.`DamageDays` AS `Clm_L_DamageDays`
    ,`CLM`.`DamageBaseDate` AS `Clm_L_DamageBaseDate`
    ,`CLM`.`DamageInterestAmount` AS `Clm_L_DamageInterestAmount`
    ,`CLM`.`ClaimFee` AS `Clm_L_ClaimFee`
    ,`CLM`.`AdditionalClaimFee` AS `Clm_L_AdditionalClaimFee`
    ,`ORD`.`Chg_Status` AS `Chg_Status`
    ,`ORD`.`Chg_FixedDate` AS `Chg_FixedDate`
    ,`ORD`.`Chg_DecisionDate` AS `Chg_DecisionDate`
    ,`ORD`.`Chg_ExecDate` AS `Chg_ExecDate`
    ,`ORD`.`Chg_ChargeAmount` AS `Chg_ChargeAmount`
    ,`ORD`.`Chg_Seq` AS `Chg_Seq`
    ,`ORD`.`Rct_RejectFlg` AS `Rct_RejectFlg`
    ,`ORD`.`Rct_RejectReason` AS `Rct_RejectReason`
    ,`ORD`.`Rct_Status` AS `Rct_Status`
--  ,`ORD`.`Rct_ReceiptProcessDate` AS `Rct_ReceiptProcessDate`
--  ,`ORD`.`Rct_ReceiptDate` AS `Rct_ReceiptDate`
--  ,`ORD`.`Rct_ReceiptAmount` AS `Rct_ReceiptAmount`
--  ,`ORD`.`Rct_ReceiptMethod` AS `Rct_ReceiptMethod`
--  ,`ORD`.`Rct_DifferentialAmount` AS `Rct_DifferentialAmount`
    ,`CLM`.`ClaimedBalance` AS `Rct_DifferentialAmount`
--  ,`ORD`.`Rct_DepositOccDate` AS `Rct_DepositOccDate`
--  ,`ORD`.`Rct_DepositAmount` AS `Rct_DepositAmount`
--  ,`ORD`.`Rct_DepositClearFlg` AS `Rct_DepositClearFlg`
    ,`ORD`.`Cnl_CantCancelFlg` AS `Cnl_CantCancelFlg`
    ,`ORD`.`Cnl_Status` AS `Cnl_Status`
    ,`ORD`.`Dmg_DecisionFlg` AS `Dmg_DecisionFlg`
    ,`ORD`.`Dmg_DecisionDate` AS `Dmg_DecisionDate`
    ,`ORD`.`Dmg_DecisionAmount` AS `Dmg_DecisionAmount`
    ,`ORD`.`Dmg_DecisionReason` AS `Dmg_DecisionReason`
    ,`ORD`.`Ent_OrderId` AS `Ent_OrderId`
    ,`ORD`.`Ent_Note` AS `Ent_Note`
    ,`ORD`.`Bekkan` AS `Bekkan`
    ,`ORD`.`StopClaimFlg` AS `StopClaimFlg`
    ,`ORD`.`MailPaymentSoonDate` AS `MailPaymentSoonDate`
    ,`ORD`.`MailLimitPassageDate` AS `MailLimitPassageDate`
    ,`ORD`.`MailLimitPassageCount` AS `MailLimitPassageCount`
    ,`ORD`.`ReturnClaimFlg` AS `ReturnClaimFlg`
    ,`ORD`.`RemindClass` AS `RemindClass`
    ,`ORD`.`TouchHistoryFlg` AS `TouchHistoryFlg`
    ,`ORD`.`BriefNote` AS `BriefNote`
    ,`ORD`.`LonghandLetter` AS `LonghandLetter`
    ,`ORD`.`VisitFlg` AS `VisitFlg`
    ,`ORD`.`FinalityCollectionMean` AS `FinalityCollectionMean`
    ,`ORD`.`FinalityRemindDate` AS `FinalityRemindDate`
    ,`ORD`.`FinalityRemindOpId` AS `FinalityRemindOpId`
    ,`ORD`.`PromPayDate` AS `PromPayDate`
    ,`ORD`.`ClaimStopReleaseDate` AS `ClaimStopReleaseDate`
    ,`ORD`.`LetterClaimStopFlg` AS `LetterClaimStopFlg`
    ,`ORD`.`MailClaimStopFlg` AS `MailClaimStopFlg`
    ,`CLM`.`ReceiptAmountTotal` AS `InstallmentPlanAmount`
    ,`ORD`.`OutOfAmends` AS `OutOfAmends`
    ,`ORD`.`Cnl_ReturnSaikenCancelFlg` AS `Cnl_ReturnSaikenCancelFlg`
    ,`ORD`.`CombinedClaimTargetStatus` AS `CombinedClaimTargetStatus`
    ,`ORD`.`OemId` AS `OemId` -- 2015/03/26 OEMID 追加
--  ,`ORD`.`ClmExt_DamageBaseDate` AS `ClmExt_DamageBaseDate`
    ,`CLM`.`DamageBaseDate` AS `ClmExt_DamageBaseDate`
    ,`CUS`.`CustomerId` AS `CustomerId`
    ,`CUS`.`NameKj` AS `NameKj`
    ,`CUS`.`NameKn` AS `NameKn`
    ,`CUS`.`PostalCode` AS `PostalCode`
    ,`CUS`.`PrefectureCode` AS `PrefectureCode`
    ,`CUS`.`PrefectureName` AS `PrefectureName`
    ,`CUS`.`City` AS `City`
    ,`CUS`.`Town` AS `Town`
    ,`CUS`.`Building` AS `Building`
    ,`CUS`.`UnitingAddress` AS `UnitingAddress`
    ,`CUS`.`Hash_Name` AS `Hash_Name`
    ,`CUS`.`Hash_Address` AS `Hash_Address`
    ,`CUS`.`Phone` AS `Phone`
    ,`CUS`.`RealCallStatus` AS `RealCallStatus`
    ,`CUS`.`RealCallResult` AS `RealCallResult`
    ,`CUS`.`RealCallScore` AS `RealCallScore`
    ,`CUS`.`eDen` AS `eDen`
    ,`CUS`.`MailAddress` AS `MailAddress`
    ,`CUS`.`RealSendMailStatus` AS `RealSendMailStatus`
    ,`CUS`.`RealSendMailResult` AS `RealSendMailResult`
    ,`CUS`.`RealSendMailScore` AS `RealSendMailScore`
    ,`CUS`.`Occupation` AS `Occupation`
    ,`CUS`.`Incre_ArName` AS `Incre_ArName`
    ,`CUS`.`Incre_NameScore` AS `Incre_NameScore`
    ,`CUS`.`Incre_NameNote` AS `Incre_NameNote`
    ,`CUS`.`Incre_ArAddr` AS `Incre_ArAddr`
    ,`CUS`.`Incre_AddressScore` AS `Incre_AddressScore`
    ,`CUS`.`Incre_AddressNote` AS `Incre_AddressNote`
    ,`CUS`.`Incre_MailDomainScore` AS `Incre_MailDomainScore`
    ,`CUS`.`Incre_MailDomainNote` AS `Incre_MailDomainNote`
    ,`CUS`.`Incre_PostalCodeScore` AS `Incre_PostalCodeScore`
    ,`CUS`.`Incre_PostalCodeNote` AS `Incre_PostalCodeNote`
    ,`CUS`.`Incre_ScoreTotal` AS `Incre_CusScoreTotal`
    ,`CUS`.`Incre_ArTel` AS `Incre_ArTel`
    ,`CUS`.`Incre_TelScore` AS `Incre_TelScore`
    ,`CUS`.`Incre_TelNote` AS `Incre_TelNote`
    ,`CUS`.`PhoneHistory` AS `PhoneHistory`
    ,`CUS`.`Carrier` AS `Carrier`
    ,`CUS`.`ValidTel` AS `ValidTel`
    ,`CUS`.`ValidMail` AS `ValidMail`
    ,`CUS`.`ValidAddress` AS `ValidAddress`
    ,`CUS`.`ResidentCard` AS `ResidentCard`
    ,`CUS`.`Cinfo1` AS `Cinfo1`
    ,`CUS`.`CinfoNote1` AS `CinfoNote1`
    ,`CUS`.`CinfoStatus1` AS `CinfoStatus1`
    ,`CUS`.`Cinfo2` AS `Cinfo2`
    ,`CUS`.`CinfoNote2` AS `CinfoNote2`
    ,`CUS`.`CinfoStatus2` AS `CinfoStatus2`
    ,`CUS`.`Cinfo3` AS `Cinfo3`
    ,`CUS`.`CinfoNote3` AS `CinfoNote3`
    ,`CUS`.`CinfoStatus3` AS `CinfoStatus3`
    ,`CUS`.`SearchNameKj` AS `SearchNameKj`
    ,`CUS`.`SearchNameKn` AS `SearchNameKn`
    ,`CUS`.`SearchPhone` AS `SearchPhone`
    ,`CUS`.`RegNameKj` AS `RegNameKj`
    ,`CUS`.`RegUnitingAddress` AS `RegUnitingAddress`
    ,`CUS`.`RegPhone` AS `RegPhone`
    ,`ETP`.`EnterpriseId` AS `EnterpriseId`
    ,`ETP`.`LoginId` AS `EnterpriseLoginId`
    ,`ETP`.`EnterpriseNameKj` AS `EnterpriseNameKj`
    ,`ETP`.`CpNameKj` AS `CpNameKj`
    ,`ETP`.`ContactPhoneNumber` AS `ContactPhoneNumber`
    ,`ETP`.`MailAddress` AS `EntMailAddress`
-- 2015-04-22 ClaimFeeは同梱、別送に分別されました
--  ,`ETP`.`ClaimFee` AS `ClaimFee`
    ,`SIT`.`ClaimFeeDK` AS `ClaimFeeDK`
    ,`SIT`.`ClaimFeeBS` AS `ClaimFeeBS`
--  ,`ETP`.`ReClaimFee` AS `ReClaimFee`
-- 2015/07/13 キャンペーン期間中はキャンペーン情報を取得する
    ,F_GetCampaignVal(`ETP`.`EnterpriseId`, `SIT`.`SiteId`, DATE(NOW()), 'ReClaimFee') AS `ReClaimFee`
--    ,`SIT`.`ReClaimFee` AS `ReClaimFee`
    ,`SIT`.`SiteNameKj` AS `SiteNameKj`
    ,`SIT`.`PrintFormBS` AS `PrintFormBS` -- 2015/03/26 追加
    ,`ORD`.`ConfirmWaitingFlg` AS `ConfirmWaitingFlg` -- 2015/03/27 追加
    ,`CLM`.`LastProcessDate` AS `LastProcessDate` -- 2015/03/27 追加
    ,`SIT`.`PayingBackFlg` AS `PayingBackFlg` -- 2015/05/01 追加
    ,`SIT`.`PayingBackDays` AS `PayingBackDays` -- 2015/05/01 追加
    ,`ETP`.`TaxClass` AS `TaxClass` -- 2015/05/11 追加
-- 2015/07/13 キャンペーン期間中はキャンペーン情報を取得する
    ,F_GetCampaignVal(`ETP`.`EnterpriseId`, `SIT`.`SiteId`, DATE(NOW()), 'SettlementFeeRate') AS `SettlementFeeRate`
--  ,`SIT`.`SettlementFeeRate` AS `SettlementFeeRate` -- 2015/05/11 追加
    ,`SIT`.`CreditCriterion` AS `CreditCriterion` -- 2015/05/13 追加
FROM `T_Order` `ORD`
    straight_join `T_Enterprise` `ETP`
               ON `ORD`.`EnterpriseId` = `ETP`.`EnterpriseId`
    straight_join `T_Site` `SIT`
               ON `ORD`.`SiteId` = `SIT`.`SiteId`
    straight_join `T_Customer` `CUS`
               ON `ORD`.`OrderSeq` = `CUS`.`OrderSeq`
        LEFT JOIN `T_ClaimControl` `CLM`
               ON `ORD`.`P_OrderSeq` = `CLM`.`OrderSeq`
/*
        LEFT JOIN `T_ReceiptControl` `RCP`
               ON `CLM`.`ClaimId` = `RCP`.`ClaimId`
*/
/*      LEFT JOIN `T_User` `EUS`
               ON `EUS`.`UserClass` = 2
              AND `EUS`.`Seq` = `ETP`.`EnterpriseId`
*/
        LEFT JOIN `M_Code` `MCD`
               ON `MCD`.`CodeId` = 4
              AND `MCD`.`KeyCode` =  IF(`CUS`.`Incre_ArTel` > `CUS`.`Incre_ArAddr`, `CUS`.`Incre_ArTel`, `CUS`.`Incre_ArAddr`)
/*
        LEFT JOIN `M_GeneralPurpose` `MGP`
               ON `MGP`.`Code` = (
                        CASE
                            WHEN (
                                    (`CUS`.`Incre_ArTel` = 5)
                                    OR (`CUS`.`Incre_ArAddr` = 5)
                                    )
                                THEN 5
                            WHEN (
                                    (`CUS`.`Incre_ArTel` = 4)
                                    OR (`CUS`.`Incre_ArAddr` = 4)
                                    )
                                THEN 4
                            WHEN (
                                    (`CUS`.`Incre_ArTel` = 3)
                                    OR (`CUS`.`Incre_ArAddr` = 3)
                                    )
                                THEN 3
                            WHEN (
                                    (`CUS`.`Incre_ArTel` = 2)
                                    OR (`CUS`.`Incre_ArAddr` = 2)
                                    )
                                THEN 2
                            WHEN (
                                    (`CUS`.`Incre_ArTel` = 1)
                                    OR (`CUS`.`Incre_ArAddr` = 1)
                                    )
                                THEN 1
                            ELSE - (1)
                            END
                        )
                AND (`MGP`.`Class` = 4)
*/
;
