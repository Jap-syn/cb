ALTER TABLE AT_EnterpriseMonthlyClosingInfo ADD COLUMN `PayingControlAddUpFlg` TINYINT NOT NULL DEFAULT 0 AFTER `DailySummaryFlg`;
ALTER TABLE AT_EnterpriseMonthlyClosingInfo ADD COLUMN `PayingControlSeq` BIGINT NOT NULL DEFAULT 0 AFTER `PayingControlAddUpFlg`;
