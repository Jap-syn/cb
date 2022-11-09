DROP procedure IF EXISTS `procMigrateOemSettlementFee`;

DELIMITER $$
CREATE PROCEDURE `procMigrateOemSettlementFee` ()
BEGIN

    /* 移行処理：OEM決済手数料 */

    DECLARE
        updDttm    datetime;        -- 更新日時

    SET updDttm = now();

    INSERT INTO `T_OemSettlementFee`
        (`Seq`, 
        `OrderSeq`, 
        `OemId`, 
        `OccDate`, 
        `OccPlan`, 
        `UseAmount`, 
        `AppSettlementFeeRate`,
        `SettlementFee`,
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
        `T_OemSettlementFee`.`Seq`, -- Seq
        `T_OemSettlementFee`.`OrderSeq`, -- OrderSeq
        `T_OemSettlementFee`.`OemId`, -- OemId
        `T_OemSettlementFee`.`OccDate`, -- OccDate
        `T_OemSettlementFee`.`OccPlan`, -- OccPlan
        `T_OemSettlementFee`.`UseAmount`, -- UseAmount
        `T_OemSettlementFee`.`AppSettlementFeeRate` / 100000, -- AppSettlementFeeRate
        `T_OemSettlementFee`.`SettlementFee`, -- SettlementFee
        `T_OemSettlementFee`.`AddUpFlg`, -- AddUpFlg
        `T_OemSettlementFee`.`AddUpFixedMonth`, -- AddUpFixedMonth
        `T_OemSettlementFee`.`OemClaimedSeq`, -- OemClaimedSeq
        `T_OemSettlementFee`.`CancelFlg`, -- CancelFlg
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_OemSettlementFee`;
END
$$

DELIMITER ;

