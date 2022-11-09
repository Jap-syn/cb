ALTER TABLE T_Site ADD COLUMN `ReClaimFeeSetting` TINYINT NOT NULL DEFAULT 0 AFTER `ClaimFeeDK`;
ALTER TABLE T_Site ADD COLUMN `ReClaimFee1` INT  NULL AFTER `ReClaimFee`;
ALTER TABLE T_Site ADD COLUMN `ReClaimFee3` INT  NULL AFTER `ReClaimFee1`;
ALTER TABLE T_Site ADD COLUMN `ReClaimFee4` INT  NULL AFTER `ReClaimFee3`;
ALTER TABLE T_Site ADD COLUMN `ReClaimFee5` INT  NULL AFTER `ReClaimFee4`;
ALTER TABLE T_Site ADD COLUMN `ReClaimFee6` INT  NULL AFTER `ReClaimFee5`;
ALTER TABLE T_Site ADD COLUMN `ReClaimFee7` INT  NULL AFTER `ReClaimFee6`;
ALTER TABLE T_Site ADD COLUMN `ReClaimFeeStartRegistDate` DATE  NULL AFTER `ReClaimFee7`;
ALTER TABLE T_Site ADD COLUMN `ReClaimFeeStartDate` DATE  NULL AFTER `ReClaimFeeStartRegistDate`;

DROP VIEW IF EXISTS `MV_EnterpriseCampaign`;

CREATE VIEW `MV_EnterpriseCampaign` AS
    SELECT *
    FROM coraldb_new01.T_EnterpriseCampaign
;
