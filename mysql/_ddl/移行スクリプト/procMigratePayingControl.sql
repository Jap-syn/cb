DROP procedure IF EXISTS `procMigratePayingControl`;

DELIMITER $$
CREATE PROCEDURE `procMigratePayingControl` ()
BEGIN

    /* 移行処理：立替振込管理 */

    DECLARE
        updDttm    datetime;        -- 更新日時

    SET updDttm = now();

    INSERT INTO `T_PayingControl`
        (`Seq`,
        `EnterpriseId`,
        `FixedDate`,
        `DecisionDate`,
        `ExecDate`,
        `ExecFlg`,
        `ExecCpId`,
        `ChargeCount`,
        `ChargeAmount`,
        `CancelCount`,
        `CalcelAmount`,
        `StampFeeCount`,
        `StampFeeTotal`,
        `MonthlyFee`,
        `DecisionPayment`,
        `AddUpFlg`,
        `AddUpFixedMonth`,
        `SettlementFee`,
        `ClaimFee`,
        `CarryOver`,
        `TransferCommission`,
        `ExecScheduleDate`,
        `AdjustmentAmount`,
        `PayBackTC`,
        `CarryOverTC`,
        `OemId`,
        `OemClaimedSeq`,
        `OemClaimedAddUpFlg`,
        `ChargeMonthlyFeeFlg`,
        `PayBackCount`,
        `PayBackAmount`,
        `PayingControlStatus`,
        `SpecialPayingFlg`,
        `PayingDataDownloadFlg`,
        `PayingDataFilePath`,
        `ClaimPdfFilePath`,
        `AdjustmentDecisionFlg`,
        `AdjustmentDecisionDate`,
        `AdjustmentCount`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `T_PayingControl`.`Seq`,
        `T_PayingControl`.`EnterpriseId`,
        `T_PayingControl`.`FixedDate`,
        `T_PayingControl`.`DecisionDate`,
        `T_PayingControl`.`ExecDate`,
        `T_PayingControl`.`ExecFlg`,
        `T_PayingControl`.`ExecCpId`,
        `T_PayingControl`.`ChargeCount`,
        `T_PayingControl`.`ChargeAmount`,
        `T_PayingControl`.`CancelCount`,
        `T_PayingControl`.`CalcelAmount`,
        `T_PayingControl`.`StampFeeCount`,
        `T_PayingControl`.`StampFeeTotal`,
        `T_PayingControl`.`MonthlyFee`,
        `T_PayingControl`.`DecisionPayment`,
        `T_PayingControl`.`AddUpFlg`,
        `T_PayingControl`.`AddUpFixedMonth`,
        `T_PayingControl`.`SettlementFee`,
        `T_PayingControl`.`ClaimFee`,
        `T_PayingControl`.`CarryOver`,
        `T_PayingControl`.`TransferCommission`,
        `T_PayingControl`.`ExecScheduleDate`,
        `T_PayingControl`.`AdjustmentAmount`,
        `T_PayingControl`.`PayBackTC`,
        `T_PayingControl`.`CarryOverTC`,
        `T_PayingControl`.`OemId`,
        `T_PayingControl`.`OemClaimedSeq`,
        `T_PayingControl`.`OemClaimedAddUpFlg`,
        `T_PayingControl`.`ChargeMonthlyFeeFlg`,
        0,
        0,
        1,
        0,
        1,
        null,
        null,
        1,
        `T_PayingControl`.`DecisionDate`,
        CASE `T_PayingControl`.`AdjustmentAmount` > 0
            WHEN TRUE THEN 1
            ELSE 0
            END,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_PayingControl`;
END
$$

DELIMITER ;
;
