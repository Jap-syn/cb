DROP procedure IF EXISTS `procMigrateEnterpriseClaimed`;

DELIMITER $$
CREATE PROCEDURE `procMigrateEnterpriseClaimed` ()
BEGIN

    /* 移行処理：加盟店月別請求 */

    DECLARE
        updDttm    datetime;    -- 更新日時

    SET updDttm = now();

    INSERT INTO `T_EnterpriseClaimed`
        (`EnterpriseId`,
        `FixedMonth`,
        `ProcessDate`,
        `SpanFrom`,
        `SpanTo`,
        `OrderCount`,
        `OrderAmount`,
        `SettlementFee`,
        `ClaimFee`,
        `StampFee`,
        `MonthlyFee`,
        `CarryOverMonthlyFee`,
        `CancelRepaymentAmount`,
        `FfTransferFee`,
        `AdjustmentAmount`,
        `ClaimAmount`,
        `PaymentAmount`,
        `AdjustmentAmountOnMonthly`,
        `OemId`,
        `PayBackAmount`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT 
        `T_EnterpriseClaimed`.`EnterpriseId`,
        `T_EnterpriseClaimed`.`FixedMonth`,
        `T_EnterpriseClaimed`.`ProcessDate`,
        `T_EnterpriseClaimed`.`SpanFrom`,
        `T_EnterpriseClaimed`.`SpanTo`,
        `T_EnterpriseClaimed`.`OrderCount`,
        `T_EnterpriseClaimed`.`OrderAmount`,
        `T_EnterpriseClaimed`.`SettlementFee`,
        `T_EnterpriseClaimed`.`ClaimFee`,
        `T_EnterpriseClaimed`.`StampFee`,
        `T_EnterpriseClaimed`.`MonthlyFee`,
        `T_EnterpriseClaimed`.`CarryOverMonthlyFee`,
        `T_EnterpriseClaimed`.`CancelRepaymentAmount`,
        `T_EnterpriseClaimed`.`FfTransferFee`,
        `T_EnterpriseClaimed`.`AdjustmentAmount`,
        `T_EnterpriseClaimed`.`ClaimAmount`,
        `T_EnterpriseClaimed`.`PaymentAmount`,
        `T_EnterpriseClaimed`.`AdjustmentAmountOnMonthly`,
        `T_EnterpriseClaimed`.`OemId`,
        0,
        updDttm,
        9,
        updDttm,
        9,
        0
    FROM `coraldb_ikou`.`T_EnterpriseClaimed`;
END
$$

DELIMITER ;

