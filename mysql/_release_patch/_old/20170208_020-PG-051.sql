/* サイトへカラム追加 */
ALTER TABLE `T_Site` ADD COLUMN `OutOfTermcheck` TINYINT(4) NOT NULL DEFAULT '0' AFTER `JintecManualReqFlg`;
ALTER TABLE `T_Site` ADD COLUMN `Telcheck` TINYINT(4) NOT NULL DEFAULT '0' AFTER `OutOfTermcheck`;
ALTER TABLE `T_Site` ADD COLUMN `Addresscheck` TINYINT(4) NOT NULL DEFAULT '0' AFTER `Telcheck`;
ALTER TABLE `T_Site` ADD COLUMN `PostalCodecheck` TINYINT(4) NOT NULL DEFAULT '0' AFTER `Addresscheck`;
ALTER TABLE `T_Site` ADD COLUMN `Ent_OrderIdcheck` TINYINT(4) NOT NULL DEFAULT '0' AFTER `PostalCodecheck`;

