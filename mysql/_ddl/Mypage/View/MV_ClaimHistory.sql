DROP VIEW IF EXISTS `MV_ClaimHistory`;

CREATE VIEW `MV_ClaimHistory` AS
    SELECT *
    FROM coraldb_new01.T_ClaimHistory
;
