DROP procedure IF EXISTS `procMigrateOemClaimFee`;

DELIMITER $$
CREATE PROCEDURE `procMigrateOemClaimFee` ()
BEGIN

    /* 移行処理：OEM請求手数料 */

    DECLARE
        updDttm    datetime;        -- 更新日時

    SET updDttm = now();

    INSERT INTO `T_OemClaimFee`
        (`Seq`,
        `OrderSeq`,
        `OemId`,
        `OccDate`,
        `ClaimFeeType`,
        `ClaimFee`,
        `AddUpFlg`,
        `AddUpFixedMonth`,
        `OemClaimedSeq`,
        `CancelFlg`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `T_OemClaimFee`.`Seq`,
        `T_OemClaimFee`.`OrderSeq`,
        `T_OemClaimFee`.`OemId`,
        `T_OemClaimFee`.`OccDate`,
        `T_OemClaimFee`.`ClaimFeeType`,
        `T_OemClaimFee`.`ClaimFee`,
        `T_OemClaimFee`.`AddUpFlg`,
        `T_OemClaimFee`.`AddUpFixedMonth`,
        `T_OemClaimFee`.`OemClaimedSeq`,
        `T_OemClaimFee`.`CancelFlg`,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_OemClaimFee`;
END
$$

DELIMITER ;


