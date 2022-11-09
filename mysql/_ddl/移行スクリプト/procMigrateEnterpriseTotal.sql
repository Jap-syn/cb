DROP procedure IF EXISTS `procMigrateEnterpriseTotal`;

DELIMITER $$
CREATE PROCEDURE `procMigrateEnterpriseTotal`()
BEGIN
    /* 移行処理：加盟店集計 */

    DECLARE updDttm     datetime;

    SET updDttm = now();

    INSERT INTO `T_EnterpriseTotal`
        (`EnterpriseId`,
        `NpCalcDate`,
        `NpMolecule3`,
        `NpDenominator3`,
        `NpRate3`,
        `NpMoleculeAll`,
        `NpDenominatorAll`,
        `NpRateAll`,
        `NpGuaranteeMolecule3`,
        `NpNoGuaranteeMolecule3`,
        `NpGuaranteeRate3`,
        `NpNoGuaranteeRate3`,
        `NpGuaranteeMoleculeAll`,
        `NpNoGuaranteeMoleculeAll`,
        `NpGuaranteeRateAll`,
        `NpNoGuaranteeRateAll`,
        `NpNgMolecule3`,
        `NpNgDenominator3`,
        `NpNgRate3`,
        `NpNgMoleculeAll`,
        `NpNgDenominatorAll`,
        `NpNgRateAll`,
        `NpOrderCountTotal`,
        `NpOrderCountOk`,
        `NpAverageAmountTotal`,
        `NpAverageAmountOk`,
        `Profitability`,
        `ArrivalConfirmCount`,
        `CancelRate`,
        `TransAmount`,
        `ClaimAmountTotal`,
        `ReceiptAmountTotal`,
        `ClaimedBalance`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `T_Enterprise`.`EnterpriseId`,
        `T_Enterprise`.`NpCalcDate`,
        IFNULL(`T_Enterprise`.`NpMolecule3`, 0),
        IFNULL(`T_Enterprise`.`NpDenominator3`, 0),
        IFNULL(`T_Enterprise`.`NpRate3` / 100, 0),
        IFNULL(`T_Enterprise`.`NpMoleculeAll`, 0),
        IFNULL(`T_Enterprise`.`NpDenominatorAll`, 0),
        IFNULL(`T_Enterprise`.`NpRateAll` / 100, 0),
        IFNULL(`T_Enterprise`.`NpGuaranteeMolecule3`, 0),
        IFNULL(`T_Enterprise`.`NpNoGuaranteeMolecule3`, 0),
        IFNULL(`T_Enterprise`.`NpGuaranteeRate3` / 100, 0),
        IFNULL(`T_Enterprise`.`NpNoGuaranteeRate3` / 100, 0),
        IFNULL(`T_Enterprise`.`NpGuaranteeMoleculeAll`, 0),
        IFNULL(`T_Enterprise`.`NpNoGuaranteeMoleculeAll`, 0),
        IFNULL(`T_Enterprise`.`NpGuaranteeRateAll` / 100, 0),
        IFNULL(`T_Enterprise`.`NpNoGuaranteeRateAll` / 100, 0),
        IFNULL(`T_Enterprise`.`NpNgMolecule3`, 0),
        IFNULL(`T_Enterprise`.`NpNgDenominator3`, 0),
        IFNULL(`T_Enterprise`.`NpNgRate3` / 100, 0),
        IFNULL(`T_Enterprise`.`NpNgMoleculeAll`, 0),
        IFNULL(`T_Enterprise`.`NpNgDenominatorAll`, 0),
        IFNULL(`T_Enterprise`.`NpNgRateAll` / 100, 0),
        IFNULL(`T_Enterprise`.`NpOrderCountTotal`, 0),
        IFNULL(`T_Enterprise`.`NpOrderCountOk`, 0),
        IFNULL(`T_Enterprise`.`NpAverageAmountTotal`, 0),
        IFNULL(`T_Enterprise`.`NpAverageAmountOk`, 0),
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_Enterprise`;

END$$

DELIMITER ;

