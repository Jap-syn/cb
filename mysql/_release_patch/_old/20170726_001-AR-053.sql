/* 注文_会計にカラム追加 */
ALTER TABLE `AT_Order` 
ADD COLUMN `ResumeFlg` TINYINT NOT NULL DEFAULT 0 AFTER `NoGuaranteeChangeLimitDay`;

