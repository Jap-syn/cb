--------------------------------------------------------------------------------
-- �ȉ��A�{�̑��X�L�[�}�֓o�^
--------------------------------------------------------------------------------
/* �s���A�N�Z�XIP�z���C�g���X�g */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'NgAccessIpWhiteList', '121.117.167.6', '�s���A�N�Z�XIP�z���C�g���X�g', NOW(), 9, NOW(), 9, '1');

ALTER TABLE `T_OemOperator` 
ADD COLUMN `NgAccessCount` INT(11) NOT NULL DEFAULT '0' AFTER `LastPasswordChanged`,
ADD COLUMN `NgAccessReferenceDate` DATETIME NULL AFTER `NgAccessCount`;

ALTER TABLE `T_Operator` 
ADD COLUMN `NgAccessCount` INT(11) NOT NULL DEFAULT '0' AFTER `LastPasswordChanged`,
ADD COLUMN `NgAccessReferenceDate` DATETIME NULL AFTER `NgAccessCount`;

/* �s���A�N�Z�X���~�b�g(�}�C�y�[�W�p) */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'MypageNgAccessIpLimit', '5', '(�}�C�y�[�W)�s���A�N�Z�X���~�b�g', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'MypageNgAccessReferenceTerm', '600', '(�}�C�y�[�W)�A���s���A�N�Z�X�����Ԋu(�b)', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'MypageNgAccessLoginLimit', '5', '(�}�C�y�[�W)�s���A�N�Z�X���O�C�����~�b�g', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'MypageNgAccessLoginReferenceTerm', '600', '(�}�C�y�[�W)�A���s���A�N�Z�X���O�C�������Ԋu(�b)', NOW(), 9, NOW(), 9, '1');

ALTER TABLE `T_MypageOrder` ADD INDEX `Idx_T_MypageOrder04` (`Phone` ASC);



--------------------------------------------------------------------------------
-- �ȉ��A�}�C�y�[�W���X�L�[�}�֓o�^
--------------------------------------------------------------------------------
ALTER TABLE `T_MypageCustomer` 
ADD COLUMN `NgAccessCount` INT(11) NOT NULL DEFAULT '0' AFTER `MailSubject`,
ADD COLUMN `NgAccessReferenceDate` DATETIME NULL AFTER `NgAccessCount`;

DROP TABLE IF EXISTS `T_NgAccessMypageOrder`;
CREATE TABLE `T_NgAccessMypageOrder` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `Phone` varchar(50) DEFAULT NULL,
  `OemId` bigint(20) DEFAULT NULL,
  `NgAccessCount` INT(11) NOT NULL DEFAULT '0',
  `NgAccessReferenceDate` datetime DEFAULT NULL,
  `UpdateDate` datetime NOT NULL,
  PRIMARY KEY (`Seq`),
  UNIQUE KEY `Idx_T_NgAccessMypageOrder01` (`Phone`, `OemId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `T_NgAccessIp`;
CREATE TABLE `T_NgAccessIp` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `IpAddress` varchar(40) DEFAULT NULL,
  `Count` int(11) NOT NULL DEFAULT '0',
  `UpdateDate` datetime NOT NULL,
  `NgAccessReferenceDate` datetime DEFAULT NULL,
  PRIMARY KEY (`Seq`),
  UNIQUE KEY `Idx_T_NgAccessIp01` (`IpAddress`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
