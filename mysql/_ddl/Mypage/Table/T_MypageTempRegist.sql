CREATE TABLE `T_MypageTempRegist` (
  `TempRegistId` bigint(20) NOT NULL AUTO_INCREMENT,
  `OemId` bigint(20) DEFAULT NULL,
  `MailAddress` varchar(255) DEFAULT NULL,
  `UrlParameter` varchar(255) DEFAULT NULL,
  `CreateDate` datetime DEFAULT NULL,
  `ValidDate` datetime DEFAULT NULL,
  `OrderSeq` bigint(20) DEFAULT NULL,
  `RegistDate` datetime DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`TempRegistId`),
  KEY `idx_01` (`OemId`,`MailAddress`),
  KEY `idx_02` (`UrlParameter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
