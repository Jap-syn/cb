CREATE TABLE `W_AdjustmentAmount` (
  `EnterpriseId` bigint(20) NOT NULL,
  `SerialNumber` int(11) NOT NULL,
  `OrderId` varchar(50) DEFAULT NULL,
  `OrderSeq` bigint(20) DEFAULT NULL,
  `ItemCode` int(11) DEFAULT NULL,
  `AdjustmentAmount` bigint(20) DEFAULT NULL,
  `RegistDate` datetime DEFAULT NULL,
  `RegistId` int(11) DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `UpdateId` int(11) DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`EnterpriseId`,`SerialNumber`),
  KEY `Idx_W_AdjustmentAmount01` (`OrderSeq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
