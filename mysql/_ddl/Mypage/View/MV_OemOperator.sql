DROP VIEW IF EXISTS `MV_OemOperator`;

CREATE VIEW `MV_OemOperator` AS
    SELECT *
    FROM coraldb_new01.T_OemOperator
;
