DROP procedure IF EXISTS `procMigrateOem`;

DELIMITER $$

CREATE PROCEDURE `procMigrateOem` ()
BEGIN

    /* 移行処理：OEM */

    DECLARE
        updDttm     datetime;
    
    SET
        updDttm = now();

    INSERT INTO `T_Oem`
        (`OemId`,
        `ApplicationDate`,
        `ServiceInDate`,
        `RegistDate`,
        `OemNameKj`,
        `OemNameKn`,
        `PostalCode`,
        `PrefectureCode`,
        `PrefectureName`,
        `City`,
        `Town`,
        `Building`,
        `RepNameKj`,
        `RepNameKn`,
        `Phone`,
        `Fax`,
        `MonthlyFee`,
        `N_MonthlyFee`,
        `SettlementFeeRateRKF`,
        `SettlementFeeRateSTD`,
        `SettlementFeeRateEXP`,
        `SettlementFeeRateSPC`,
        `ClaimFeeBS`,
        `ClaimFeeDK`,
        `EntMonthlyFeeRKF`,
        `EntMonthlyFeeSTD`,
        `EntMonthlyFeeEXP`,
        `EntMonthlyFeeSPC`,
        `OpDkInitFeeRate`,
        `OpDkMonthlyFeeRate`,
        `OpApiRegOrdMonthlyFeeRate`,
        `OpApiAllInitFeeRate`,
        `OpApiAllMonthlyFeeRate`,
        `Salesman`,
        `FfName`,
        `FfCode`,
        `FfBranchName`,
        `FfBranchCode`,
        `FfAccountNumber`,
        `FfAccountClass`,
        `FfAccountName`,
        `TcClass`,
        `CpNameKj`,
        `CpNameKn`,
        `DivisionName`,
        `MailAddress`,
        `ContactPhoneNumber`,
        `ContactFaxNumber`,
        `Note`,
        `ValidFlg`,
        `InvalidatedDate`,
        `InvalidatedReason`,
        `KisanbiDelayDays`,
        `AccessId`,
        `EntLoginIdPrefix`,
        `OrderIdPrefix`,
        `Notice`,
        `ServiceName`,
        `ServicePhone`,
        `SupportTime`,
        `SupportMail`,
        `Copyright`,
        `LargeLogo`,
        `SmallLogo`,
        `Imprint`,
        `PayingMethod`,
        `HelpUrl`,
        `FixPattern`,
        `ReclaimAccountPolicy`,
        `FavIcon`,
        `FavIconType`,
        `EntAccountEditLimitation`,
        `EntAccountAdditionalMessage`,
        `CreditCriterion`,
        `AutoCreditDateFrom`,
        `AutoCreditDateTo`,
        `AutoCreditCriterion`,
        `PrintEntOrderIdOnClaimFlg`,
        `DamageInterestRate`,
        `OemClaimTransDays`,
        `OemClaimTransFlg`,
        `OemFixedDay1`,
        `OemFixedDay2`,
        `OemFixedDay3`,
        `SettlementDay1`,
        `SettlementDay2`,
        `SettlementDay3`,
        `AutoCreditLimitAmount`,
        `JapanPostPrintFlg`,
        `MembershipAgreement`,
        `B_OemFixedDate`,
        `B_SettlementDate`,
        `N_OemFixedDate`,
        `N_SettlementDate`,
        `SameFfTcFeeThirtyKAndOver`,
        `SameFfTcFeeUnderThirtyK`,
        `OtherFfTcFeeThirtyKAndOver`,
        `OtherFfTcFeeUnderThirtyK`,
        `TimemachineNgFlg`,
        `RecordClaimPrintedDateFlg`,
        `FixedLengthFlg`,
        `ConsignorCode`,
        `ConsignorName`,
        `RemittingBankCode`,
        `RemittingBankName`,
        `RemittingBranchCode`,
        `RemittingBranchName`,
        `AccountClass`,
        `AccountNumber`,
        `DspTaxFlg`,
        `AccOemClass`,
        `SettlementFeeRatePlan`,
        `EntMonthlyFeePlan`,
        `AddTcClass`,
        `StyleSheets`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`)
    SELECT
        `T_Oem`.`OemId`,
        `T_Oem`.`ApplicationDate`,
        `T_Oem`.`ServiceInDate`,
        `T_Oem`.`RegistDate`,
        `T_Oem`.`OemNameKj`,
        `T_Oem`.`OemNameKn`,
        `T_Oem`.`PostalCode`,
        `T_Oem`.`PrefectureCode`,
        `T_Oem`.`PrefectureName`,
        `T_Oem`.`City`,
        `T_Oem`.`Town`,
        `T_Oem`.`Building`,
        `T_Oem`.`RepNameKj`,
        `T_Oem`.`RepNameKn`,
        `T_Oem`.`Phone`,
        `T_Oem`.`Fax`,
        TRUNCATE( (`T_Oem`.`MonthlyFee` / 1.08), 0),
        TRUNCATE( (`T_Oem`.`N_MonthlyFee` / 1.08), 0),
        `T_Oem`.`SettlementFeeRateRKF`,
        `T_Oem`.`SettlementFeeRateSTD`,
        `T_Oem`.`SettlementFeeRateEXP`,
        `T_Oem`.`SettlementFeeRateSPC`,
        TRUNCATE( (`T_Oem`.`ClaimFeeBS` / 1.08), 0),
        TRUNCATE( (`T_Oem`.`ClaimFeeDK` / 1.08), 0),
        TRUNCATE( (`T_Oem`.`EntMonthlyFeeRKF` / 1.08), 0),
        TRUNCATE( (`T_Oem`.`EntMonthlyFeeSTD` / 1.08), 0),
        TRUNCATE( (`T_Oem`.`EntMonthlyFeeEXP` / 1.08), 0),
        TRUNCATE( (`T_Oem`.`EntMonthlyFeeSPC` / 1.08), 0),
        `T_Oem`.`OpDkInitFeeRate`,
        `T_Oem`.`OpDkMonthlyFeeRate`,
        `T_Oem`.`OpApiRegOrdMonthlyFeeRate`,
        `T_Oem`.`OpApiAllInitFeeRate`,
        `T_Oem`.`OpApiAllMonthlyFeeRate`,
        `T_Oem`.`Salesman`,
        `T_Oem`.`FfName`,
        `T_Oem`.`FfCode`,
        `T_Oem`.`FfBranchName`,
        `T_Oem`.`FfBranchCode`,
        `T_Oem`.`FfAccountNumber`,
        `T_Oem`.`FfAccountClass`,
        `T_Oem`.`FfAccountName`,
        `T_Oem`.`TcClass`,
        `T_Oem`.`CpNameKj`,
        `T_Oem`.`CpNameKn`,
        `T_Oem`.`DivisionName`,
        `T_Oem`.`MailAddress`,
        `T_Oem`.`ContactPhoneNumber`,
        `T_Oem`.`ContactFaxNumber`,
        `T_Oem`.`Note`,
        IFNULL(`T_Oem`.`ValidFlg`, 1),
        `T_Oem`.`InvalidatedDate`,
        `T_Oem`.`InvalidatedReason`,
        `T_Oem`.`KisanbiDelayDays`,
        `T_Oem`.`AccessId`,
        `T_Oem`.`EntLoginIdPrefix`,
        `T_Oem`.`OrderIdPrefix`,
        `T_Oem`.`Notice`,
        `T_Oem`.`ServiceName`,
        `T_Oem`.`ServicePhone`,
        `T_Oem`.`SupportTime`,
        `T_Oem`.`SupportMail`,
        `T_Oem`.`Copyright`,
        `T_Oem`.`LargeLogo`,
        `T_Oem`.`SmallLogo`,
        `T_Oem`.`Imprint`,
        `T_Oem`.`PayingMethod`,
        `T_Oem`.`HelpUrl`,
        `T_Oem`.`FixPattern`,
        `T_Oem`.`ReclaimAccountPolicy`,
        `T_Oem`.`FavIcon`,
        `T_Oem`.`FavIconType`,
        `T_Oem`.`EntAccountEditLimitation`,
        `T_Oem`.`EntAccountAdditionalMessage`,
        NULL,
        NULL,
        NULL,
        NULL,
        1,
        0,
        CASE
            WHEN `T_Oem`.`OemId` = 1 THEN 14
            ELSE 90    -- 債権移行基準日
        END,
        CASE
            WHEN `T_Oem`.`OemId` = 1 THEN 1
            ELSE 0     -- 債権移行基準日
        END,
        15,
        31,
        NULL,
        31,
        15,
        NULL,
        NULL,
        0,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        `T_Oem`.`SameFfTcFeeThirtyKAndOver`,
        `T_Oem`.`SameFfTcFeeUnderThirtyK`,
        `T_Oem`.`OtherFfTcFeeThirtyKAndOver`,
        `T_Oem`.`OtherFfTcFeeUnderThirtyK`,
        `T_Oem`.`TimemachineNgFlg`,
        `T_Oem`.`RecordClaimPrintedDateFlg`,
        `T_Oem`.`FixedLengthFlg`,
        `T_Oem`.`ConsignorCode`,
        `T_Oem`.`ConsignorName`,
        `T_Oem`.`RemittingBankCode`,
        `T_Oem`.`RemittingBankName`,
        `T_Oem`.`RemittingBranchCode`,
        `T_Oem`.`RemittingBranchName`,
        `T_Oem`.`AccountClass`,
        `T_Oem`.`AccountNumber`,
        `T_Oem`.`DspTaxFlg`,
        CASE
            WHEN `T_Oem`.`OemId` = 2 THEN 1
            ELSE 0
        END,   -- 会計OEM区分(会計対応)
        CONCAT('{"11":"'  , TRUNCATE( (`T_Oem`.`SettlementFeeRateRKF` / (100000 * 1.08)), 0)
              ,'","21":"' , TRUNCATE( (`T_Oem`.`SettlementFeeRateSTD` / (100000 * 1.08)), 0)
              ,'","31":"' , TRUNCATE( (`T_Oem`.`SettlementFeeRateEXP` / (100000 * 1.08)), 0)
              ,'","41":"' , TRUNCATE( (`T_Oem`.`SettlementFeeRateSPC` / (100000 * 1.08)), 0)
              ,'"}'), -- プラン別決済手数料率
        CONCAT('{"11":"'  , TRUNCATE( (`T_Oem`.`EntMonthlyFeeRKF` / 1.08), 0)
              ,'","21":"' , TRUNCATE( (`T_Oem`.`EntMonthlyFeeSTD` / 1.08), 0)
              ,'","31":"' , TRUNCATE( (`T_Oem`.`EntMonthlyFeeEXP` / 1.08), 0)
              ,'","41":"' , TRUNCATE( (`T_Oem`.`EntMonthlyFeeSPC` / 1.08), 0) 
              ,'"}'), -- プラン別店舗月額固定費
        `T_Oem`.`AddTcClass`,
        CASE WHEN `T_Oem`.`OemId` = 2 THEN 2
             WHEN `T_Oem`.`OemId` = 3 THEN 1
             ELSE 0
        END ,  -- スタイルシート(0:ﾃﾞﾌｫﾙﾄ、1:青、2:緑)
        9,
        updDttm,
        9
    FROM `coraldb_ikou`.`T_Oem`;
END
$$

DELIMITER ;
