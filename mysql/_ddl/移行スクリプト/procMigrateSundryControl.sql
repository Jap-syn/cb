DROP procedure IF EXISTS `procMigrateSundryControl`;

DELIMITER $$
CREATE PROCEDURE `procMigrateSundryControl`()
BEGIN
    /* 移行処理：雑収入・雑損失管理(T_SundryControl) */

    DECLARE
        updDttm    datetime;

    SET updDttm = now();

    INSERT INTO `T_SundryControl`
        (`ProcessDate`,
        `SundryType`,
        `SundryAmount`,
        `SundryClass`,
        `OrderSeq`,
        `OrderId`,
        `ClaimId`,
        `Note`,
        `CheckingUseAmount`,
        `CheckingClaimFee`,
        `CheckingDamageInterestAmount`,
        `CheckingAdditionalClaimFee`,
        `DailySummaryFlg`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `T_ReceiptControl`.`ReceiptProcessDate`,
        1,
        0,                                       -- 設計書（移行設計書_T_SundryControl_雑収入・雑損失管理【最新】.xlsx）の移行元設定しない
        99,
        `T_Order`.`OrderSeq`,
        `T_Order`.`OrderId`,
        `T_ClaimControl`.`ClaimId`,
        null,
        `T_ClaimControl`.`UseAmountTotal` - `T_ReceiptControl`.`CheckingUseAmount`,
        `T_ClaimControl`.`ClaimFee` - `T_ReceiptControl`.`CheckingClaimFee`,
        `T_ClaimControl`.`DamageInterestAmount` - `T_ReceiptControl`.`CheckingDamageInterestAmount`,
        0,
        1,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_Order`
    INNER JOIN `T_ReceiptControl`
    ON `T_ReceiptControl`.`OrderSeq` = `T_Order`.`OrderSeq`
    INNER JOIN `T_ClaimControl`
    ON `T_ClaimControl`.`OrderSeq` = `T_Order`.`OrderSeq`
    WHERE `T_Order`.`DataStatus`   = 91
      AND `T_Order`.`CloseReason`  <> 3
      AND `T_Order`.`Rct_ReceiptAmount` BETWEEN 0 AND `T_ClaimControl`.`ClaimAmount`;

END$$

DELIMITER ;

