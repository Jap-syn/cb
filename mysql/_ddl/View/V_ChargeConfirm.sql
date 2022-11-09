DROP VIEW IF EXISTS `V_ChargeConfirm`;

CREATE VIEW `V_ChargeConfirm`
AS
-- SELECT `ENT`.`FixPattern` AS `FixPattern`
SELECT `MPC`.`FixPattern` AS `FixPattern`
    ,`ENT`.`EnterpriseNameKj` AS `EnterpriseNameKj`
-- キャンペーン期間中はキャンペーン情報を表示する
    ,F_GetCampaignVal(`PC`.`EnterpriseId`, (SELECT MIN(`SiteId`) FROM `T_Site` WHERE `EnterpriseId` = `ENT`.`EnterpriseId`), date(`PC`.`FixedDate`), 'AppPlan') AS `Plan`
--  ,`ENT`.`Plan` AS `Plan`
-- サイト特定不可能なため、加盟店に紐づくサイトのMAXの決済手数料率を取得する
    ,(SELECT MAX(`SettlementFeeRate`) AS `SettlementFeeRate` FROM `T_Site` WHERE `EnterpriseId` = `ENT`.`EnterpriseId`) AS `SettlementFeeRate`
    ,`ENT`.`FfName` AS `FfName`
    ,`ENT`.`FfCode` AS `FfCode`
    ,`ENT`.`FfBranchName` AS `FfBranchName`
    ,`ENT`.`FfBranchCode` AS `FfBranchCode`
    ,`ENT`.`FfAccountNumber` AS `FfAccountNumber`
    ,`ENT`.`FfAccountClass` AS `FfAccountClass`
    ,`ENT`.`FfAccountName` AS `FfAccountName`
    ,`PC`.`Seq` AS `Seq`
    ,`PC`.`EnterpriseId` AS `EnterpriseId`
    ,`PC`.`FixedDate` AS `FixedDate`
    ,`PC`.`DecisionDate` AS `DecisionDate`
    ,`PC`.`ExecScheduleDate` AS `ExecScheduleDate`
    ,`PC`.`ExecDate` AS `ExecDate`
    ,`PC`.`ExecFlg` AS `ExecFlg`
    ,`PC`.`ExecCpId` AS `ExecCpId`
    ,`PC`.`CarryOver` AS `CarryOver`
    ,`PC`.`ChargeCount` AS `ChargeCount`
    ,`PC`.`ChargeAmount` AS `ChargeAmount`
    ,`PC`.`SettlementFee` AS `SettlementFee`
    ,`PC`.`ClaimFee` AS `ClaimFee`
    ,((`PC`.`ChargeAmount` + `PC`.`SettlementFee`) + `PC`.`ClaimFee`) AS `UseAmount`
    ,(`PC`.`SettlementFee` + `PC`.`ClaimFee`) AS `Uriage`
    ,`PC`.`CancelCount` AS `CancelCount`
    ,`PC`.`CalcelAmount` AS `CalcelAmount`
    ,`PC`.`StampFeeCount` AS `StampFeeCount`
    ,`PC`.`StampFeeTotal` AS `StampFeeTotal`
    ,`PC`.`MonthlyFee` AS `MonthlyFee`
    ,`PC`.`TransferCommission` AS `TransferCommission`
    ,`PC`.`DecisionPayment` AS `DecisionPayment`
    ,(`PC`.`DecisionPayment` - `PC`.`CarryOver`) AS `DecisionPaymentOrg`
    ,`PC`.`AddUpFlg` AS `AddUpFlg`
    ,`PC`.`AddUpFixedMonth` AS `AddUpFixedMonth`
    ,`PC`.`AdjustmentAmount` AS `AdjustmentAmount`
    ,`ENT`.`PayingCycleId` AS `PayingCycleId`
    ,`PC`.`ClaimPdfFilePath` AS `ClaimPdfFilePath`
    ,`PC`.`PayBackAmount` AS `PayBackAmount`
    ,`PC`.`PayingDataDownloadFlg` AS `PayingDataDownloadFlg`
    ,`PC`.`SpecialPayingFlg` AS `SpecialPayingFlg`          -- 2015/07/22 追加
    ,`PC`.`PayingDataFilePath` AS `PayingDataFilePath`      -- 2015/07/22 追加
    ,`PC`.`PayingControlStatus` AS `PayingControlStatus`    -- 2015/07/22 追加
FROM `T_PayingControl` `PC`
    INNER JOIN `T_Enterprise` `ENT`
            ON `PC`.`EnterpriseId` = `ENT`.`EnterpriseId`
    INNER JOIN `M_PayingCycle` `MPC`
        ON `ENT`.`PayingCycleId` = `MPC`.`PayingCycleId`
;