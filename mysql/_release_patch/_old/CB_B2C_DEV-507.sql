ALTER TABLE T_Site ADD COLUMN `ReceiptUsedFlg` TINYINT NOT NULL DEFAULT 0 AFTER `ReceiptIssueProviso`;

DROP VIEW IF EXISTS `MV_Site`;
CREATE VIEW `MV_Site` AS
    SELECT *
    FROM coraldb_new01.T_Site
;
