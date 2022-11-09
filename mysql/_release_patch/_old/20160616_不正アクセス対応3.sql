--------------------------------------------------------------------------------
-- 以下、本体側スキーマへ登録
--------------------------------------------------------------------------------
/* 不正アクセスIPホワイトリスト */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'NgAccessIpWhiteList', '121.117.167.6', '不正アクセスIPホワイトリスト', NOW(), 9, NOW(), 9, '1');

ALTER TABLE `T_OemOperator` 
ADD COLUMN `NgAccessCount` INT(11) NOT NULL DEFAULT '0' AFTER `LastPasswordChanged`,
ADD COLUMN `NgAccessReferenceDate` DATETIME NULL AFTER `NgAccessCount`;

ALTER TABLE `T_Operator` 
ADD COLUMN `NgAccessCount` INT(11) NOT NULL DEFAULT '0' AFTER `LastPasswordChanged`,
ADD COLUMN `NgAccessReferenceDate` DATETIME NULL AFTER `NgAccessCount`;

/* 不正アクセスリミット(マイページ用) */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'MypageNgAccessIpLimit', '5', '(マイページ)不正アクセスリミット', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'MypageNgAccessReferenceTerm', '600', '(マイページ)連続不正アクセス判定基準間隔(秒)', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'MypageNgAccessLoginLimit', '5', '(マイページ)不正アクセスログインリミット', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'MypageNgAccessLoginReferenceTerm', '600', '(マイページ)連続不正アクセスログイン判定基準間隔(秒)', NOW(), 9, NOW(), 9, '1');

ALTER TABLE `T_MypageOrder` ADD INDEX `Idx_T_MypageOrder04` (`Phone` ASC);



--------------------------------------------------------------------------------
-- 以下、マイページ側スキーマへ登録
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
