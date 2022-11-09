DROP VIEW IF EXISTS `MV_ClaimControl`;

CREATE VIEW `MV_ClaimControl` AS
    SELECT *
    FROM coraldb_new01.T_ClaimControl
;
