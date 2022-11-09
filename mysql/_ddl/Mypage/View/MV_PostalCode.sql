DROP VIEW IF EXISTS `MV_PostalCode`;

CREATE VIEW `MV_PostalCode` AS
    SELECT *
    FROM coraldb_new01.M_PostalCode
;
