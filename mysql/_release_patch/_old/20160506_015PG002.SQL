/* T_ImportedReceiptFile登録 */
DROP TABLE IF EXISTS `T_ImportedReceiptFile`;
CREATE TABLE `T_ImportedReceiptFile` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `ReceiptProcessClass` int(11) DEFAULT NULL,
  `FileName` varchar(255) DEFAULT NULL,
  `Reserve` text,
  `RegistDate` datetime DEFAULT NULL,
  `RegistId` int(11) DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `UpdateId` int(11) DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`Seq`),
  UNIQUE KEY `ReceiptProcessClassFileName` (`ReceiptProcessClass`,`FileName`),
  KEY `Idx_T_ImportedReceiptFile01` (`ReceiptProcessClass`,`FileName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/* T_ReceiptContolへのカラム追加 */
ALTER TABLE `T_ReceiptControl` ADD COLUMN `MailFlg` INT NOT NULL DEFAULT 9 AFTER `ReceiptAgentId`;
ALTER TABLE `T_ReceiptControl` ADD COLUMN `MailRetryCount` TINYINT NOT NULL DEFAULT 0 AFTER `MailFlg`;

/* T_ReceiptContol.MailFlgへのインデックス付与 */
ALTER TABLE `T_ReceiptControl` ADD INDEX `Idx_T_ReceiptControl07` (`MailFlg` ASC);



/* ******************** 以下、確報取込関連 ******************** */
/* AT_ReceiptContolへのカラム追加(キー情報／速報データ取込日／確報データ取込日)  */
ALTER TABLE `AT_ReceiptControl` 
ADD COLUMN `KeyInfo` VARCHAR(100) NULL DEFAULT NULL AFTER `Before_Deli_ConfirmArrivalFlg`,
ADD COLUMN `SokuhoRegistDate` DATE NULL DEFAULT NULL AFTER `KeyInfo`,
ADD COLUMN `KakuhoRegistDate` DATE NULL DEFAULT NULL AFTER `SokuhoRegistDate`;

/* AT_ReceiptControl.KeyInfoへのインデックス付与 */
ALTER TABLE `AT_ReceiptControl` ADD INDEX `Idx_AT_ReceiptControl02` (`KeyInfo` ASC);



/* ******************** 以下、詳細入金インポート関連 ******************** */
INSERT INTO T_Menu VALUES (183, 'cbadmin', 'keiriMenus', 'impdtl', NULL, '***', '詳細入金インポート', '詳細入金インポート', '', '', '', NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (183, 1, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (183, 11, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (183, 101, NOW(), 9, NOW(), 9, 1);
