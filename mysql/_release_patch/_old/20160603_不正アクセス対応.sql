/* T_NgAccessIp�o�^ */
DROP TABLE IF EXISTS `T_NgAccessIp`;
CREATE TABLE `T_NgAccessIp` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `IpAddress` varchar(40) DEFAULT NULL,
  `Count` int(11) NOT NULL DEFAULT '0',
  `UpdateDate` datetime NOT NULL,
  PRIMARY KEY (`Seq`),
  UNIQUE KEY `Idx_T_NgAccessIp01` (`IpAddress`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* �s���A�N�Z�X���~�b�g�o�^ */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'NgAccessIpLimit', '5', '�s���A�N�Z�X���~�b�g', NOW(), 9, NOW(), 9, '1');
