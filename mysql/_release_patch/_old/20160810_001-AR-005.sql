-- OKチケットテーブルの作成
CREATE TABLE `T_CreditOkTicket` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `Status` tinyint(4) DEFAULT NULL,
  `EnterpriseId` bigint(20) DEFAULT NULL,
  `OrderSeq` bigint(20) DEFAULT NULL,
  `RegistDate` datetime DEFAULT NULL,
  `RegistOpId` int(11) DEFAULT NULL,
  `ValidToDate` datetime DEFAULT NULL,
  `ReleaseDate` datetime DEFAULT NULL,
  `ReleaseOpId` int(11) DEFAULT NULL,
  `UseOrderSeq` bigint(20) DEFAULT NULL,
  `UseDate` datetime DEFAULT NULL,
  PRIMARY KEY (`Seq`),
  KEY `Idx_T_CreditOkTicket01` (`OrderSeq`),
  KEY `Idx_T_CreditOkTicket02` (`UseOrderSeq`),
  KEY `Idx_T_CreditOkTicket03` (`EnterpriseId`,`ValidToDate`,`Status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
