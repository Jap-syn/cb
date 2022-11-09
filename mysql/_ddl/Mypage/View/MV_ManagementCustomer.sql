DROP VIEW IF EXISTS `MV_ManagementCustomer`;

CREATE VIEW `MV_ManagementCustomer` AS
    SELECT *
    FROM coraldb_new01.T_ManagementCustomer
;
