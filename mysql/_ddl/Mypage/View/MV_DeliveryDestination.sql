DROP VIEW IF EXISTS `MV_DeliveryDestination`;

CREATE VIEW `MV_DeliveryDestination` AS
    SELECT *
    FROM coraldb_new01.T_DeliveryDestination
;
