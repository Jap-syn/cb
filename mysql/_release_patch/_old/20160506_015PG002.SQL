/* T_ImportedReceiptFile�o�^ */
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


/* T_ReceiptContol�ւ̃J�����ǉ� */
ALTER TABLE `T_ReceiptControl` ADD COLUMN `MailFlg` INT NOT NULL DEFAULT 9 AFTER `ReceiptAgentId`;
ALTER TABLE `T_ReceiptControl` ADD COLUMN `MailRetryCount` TINYINT NOT NULL DEFAULT 0 AFTER `MailFlg`;

/* T_ReceiptContol.MailFlg�ւ̃C���f�b�N�X�t�^ */
ALTER TABLE `T_ReceiptControl` ADD INDEX `Idx_T_ReceiptControl07` (`MailFlg` ASC);



/* ******************** �ȉ��A�m��捞�֘A ******************** */
/* AT_ReceiptContol�ւ̃J�����ǉ�(�L�[���^����f�[�^�捞���^�m��f�[�^�捞��)  */
ALTER TABLE `AT_ReceiptControl` 
ADD COLUMN `KeyInfo` VARCHAR(100) NULL DEFAULT NULL AFTER `Before_Deli_ConfirmArrivalFlg`,
ADD COLUMN `SokuhoRegistDate` DATE NULL DEFAULT NULL AFTER `KeyInfo`,
ADD COLUMN `KakuhoRegistDate` DATE NULL DEFAULT NULL AFTER `SokuhoRegistDate`;

/* AT_ReceiptControl.KeyInfo�ւ̃C���f�b�N�X�t�^ */
ALTER TABLE `AT_ReceiptControl` ADD INDEX `Idx_AT_ReceiptControl02` (`KeyInfo` ASC);



/* ******************** �ȉ��A�ڍד����C���|�[�g�֘A ******************** */
INSERT INTO T_Menu VALUES (183, 'cbadmin', 'keiriMenus', 'impdtl', NULL, '***', '�ڍד����C���|�[�g', '�ڍד����C���|�[�g', '', '', '', NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (183, 1, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (183, 11, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (183, 101, NOW(), 9, NOW(), 9, 1);