DROP procedure IF EXISTS `procMigratePayingAndSales`;

DELIMITER $$
CREATE PROCEDURE `procMigratePayingAndSales` ()
BEGIN

    /* 移行処理：立替・売上管理 */

    INSERT INTO `T_PayingAndSales`
        (`Seq`,
        `OrderSeq`,
        `OccDate`,
        `UseAmount`,
        `AppSettlementFeeRate`,
        `SettlementFee`,
        `ClaimFee`,
        `ChargeAmount`,
        `ClearConditionForCharge`,
        `ClearConditionDate`,
        `ChargeDecisionFlg`,
        `ChargeDecisionDate`,
        `CancelFlg`,
        `PayingControlSeq`,
        `SpecialPayingDate`,
        `PayingControlStatus`,
        `AgencyFeeAddUpFlg`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT 
        `T_PayingAndSales`.`Seq`,
        `T_PayingAndSales`.`OrderSeq`,
        `T_PayingAndSales`.`OccDate`,
        `T_PayingAndSales`.`UseAmount`,
        `T_PayingAndSales`.`AppSettlementFeeRate` / 100000,
        `T_PayingAndSales`.`SettlementFee`,
        `T_PayingAndSales`.`ClaimFee`,
        `T_PayingAndSales`.`ChargeAmount`,
        `T_PayingAndSales`.`ClearConditionForCharge`,
        `T_PayingAndSales`.`ClearConditionDate`,
        `T_PayingAndSales`.`ChargeDecisionFlg`,
        `T_PayingAndSales`.`ChargeDecisionDate`,
        `T_PayingAndSales`.`CancelFlg`,
        `T_PayingAndSales`.`PayingControlSeq`,
        NULL , --  SpecialPayingDate
        CASE
            WHEN ChargeDecisionFlg = 1 THEN 1
            ELSE 0
        END, -- `PayingControlStatus`,
        CASE
            WHEN IFNULL(ClearConditionDate, '2100-12-31') < '2015-12-01' THEN 1
            ELSE 0
        END, -- `AgencyFeeAddUpFlg`,
        NOW(), -- `RegistDate`,
        9, -- `RegistId`,
        NOW(), -- `UpdateDate`,
        9, -- `UpdateId`,
        1 -- `ValidFlg`)
    FROM `coraldb_ikou`.`T_PayingAndSales`;
END
$$

DELIMITER ;

