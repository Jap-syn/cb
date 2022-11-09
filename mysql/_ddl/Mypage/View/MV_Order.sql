DROP VIEW IF EXISTS `MV_Order`;

CREATE VIEW `MV_Order` AS
    SELECT *
    FROM coraldb_new01.T_Order
;
