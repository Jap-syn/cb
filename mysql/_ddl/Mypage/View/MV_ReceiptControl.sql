DROP VIEW IF EXISTS `MV_ReceiptControl`;

CREATE VIEW `MV_ReceiptControl` AS
    SELECT *
    FROM coraldb_new01.T_ReceiptControl
;
