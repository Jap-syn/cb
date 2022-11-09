CREATE TABLE `T_MypageToBackIF` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `Status` tinyint(4) NOT NULL DEFAULT '0',
  `Reason` varchar(255) DEFAULT NULL,
  `IFClass` tinyint(4) NOT NULL,
  `IFData` text,
  `OrderSeq` bigint(20) DEFAULT NULL,
  `ManCustId` bigint(20) DEFAULT NULL,
  `CustomerId` bigint(20) DEFAULT NULL,
  `RegistDate` datetime DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`Seq`),
  KEY `idx_01` (`Status`),
  KEY `idx_02` (`OrderSeq`),
  KEY `idx_03` (`RegistDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
