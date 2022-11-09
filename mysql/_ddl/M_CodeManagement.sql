CREATE TABLE IF NOT EXISTS `M_CodeManagement` (
  `CodeId` int(11) NOT NULL,
  `CodeName` varchar(50) DEFAULT NULL,
  `KeyPhysicalName` varchar(50) DEFAULT NULL,
  `KeyLogicName` varchar(50) DEFAULT NULL,
  `Class1ValidFlg` tinyint(4) NOT NULL DEFAULT '0',
  `Class1Name` varchar(50) DEFAULT NULL,
  `Class2ValidFlg` tinyint(4) NOT NULL DEFAULT '0',
  `Class2Name` varchar(50) DEFAULT NULL,
  `Class3ValidFlg` tinyint(4) NOT NULL DEFAULT '0',
  `Class3Name` varchar(50) DEFAULT NULL,
  `Class4ValidFlg` tinyint(4) NOT NULL DEFAULT '0',
  `Class4Name` varchar(50) DEFAULT NULL,
  `RegistDate` datetime DEFAULT NULL,
  `RegistId` int(11) DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `UpdateId` int(11) DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`CodeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8$$

SELECT * FROM coraldb_mypage01.T_CreditPayment;