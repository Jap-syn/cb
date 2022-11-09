DROP VIEW IF EXISTS `MV_OrderItems`;

CREATE VIEW `MV_OrderItems` AS
    SELECT *
    FROM coraldb_new01.T_OrderItems
;
