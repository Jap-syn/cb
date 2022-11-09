DROP VIEW IF EXISTS `MV_OemClaimAccountInfo`;

CREATE VIEW `MV_OemClaimAccountInfo` AS
    SELECT *
    FROM coraldb_new01.T_OemClaimAccountInfo
;
