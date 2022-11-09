DROP VIEW IF EXISTS `MV_EnterpriseCustomer`;

CREATE VIEW `MV_EnterpriseCustomer` AS
    SELECT *
    FROM coraldb_new01.T_EnterpriseCustomer
;
