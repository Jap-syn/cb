DROP procedure IF EXISTS `procMigrateCancel`;

DELIMITER $$
CREATE PROCEDURE `procMigrateCancel` ()
BEGIN
    /* 移行処理：キャンセル管理 */

    DECLARE
        updDttm    datetime;

    SET updDttm = now();

    INSERT INTO `T_Cancel`
        (`Seq`,
        `OrderSeq`,
        `CancelDate`,
        `CancelPhase`,
        `CancelReason`,
        `RepayChargeAmount`,
        `RepaySettlementFee`,
        `RepayClaimFee`,
        `RepayStampFee`,
        `RepayDamageInterest`,
        `RepayReClaimFee`,
        `RepayDifferentialAmount`,
        `RepayDepositAmount`,
        `RepayReceiptAmount`,
        `RepayTotal`,
        `ApproveFlg`,
        `ApprovalDate`,
        `ApproveOpId`,
        `KeepAnAccurateFlg`,
        `KeepAnAccurateDate`,
        `PayingControlSeq`,
        `CancelReasonCode`,
        `CancelRequestDate`,
        `PayingControlStatus`,
        `DailySummaryFlg`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `T_Cancel`.`Seq`,
        `T_Cancel`.`OrderSeq`,
        `T_Cancel`.`CancelDate`,
        `T_Cancel`.`CancelPhase`,
        `T_Cancel`.`CancelReason`,
        `T_Cancel`.`RepayChargeAmount`,
        `T_Cancel`.`RepaySettlementFee`,
        `T_Cancel`.`RepayClaimFee`,
        `T_Cancel`.`RepayStampFee`,
        `T_Cancel`.`RepayDamageInterest`,
        `T_Cancel`.`RepayReClaimFee`,
        `T_Cancel`.`RepayDifferentialAmount`,
        `T_Cancel`.`RepayDepositAmount`,
        `T_Cancel`.`RepayReceiptAmount`,
        `T_Cancel`.`RepayTotal`,
        `T_Cancel`.`ApproveFlg`,
        `T_Cancel`.`ApprovalDate`,
        `T_Cancel`.`ApproveOpId`,
        `T_Cancel`.`KeepAnAccurateFlg`,
        `T_Cancel`.`KeepAnAccurateDate`,
        `T_Cancel`.`PayingControlSeq`,
        null,
        null,
        CASE WHEN  `T_Cancel`.`ApproveFlg` = 1 AND `coraldb_ikou`.`T_Cancel`.`KeepAnAccurateFlg` = 1
            THEN 1
            ELSE 0
        END,
/**        CASE WHEN `T_Cancel`.`ApprovalDate` < current_date THEN 1
            ELSE 0
        END,
*/      CASE WHEN `T_Cancel`.`ApprovalDate` < updDttm THEN 1
            ELSE 0
        END,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_Cancel`;

END$$

DELIMITER ;

