DROP VIEW IF EXISTS `MV_Customer`;

CREATE VIEW `MV_Customer` AS
    SELECT *
    FROM coraldb_new01.T_Customer
;
