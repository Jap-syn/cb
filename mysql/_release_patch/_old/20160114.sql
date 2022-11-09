CREATE TABLE `AW_Receipt1` (
  `OrderSeq` bigint(20) NOT NULL,
  `ReceiptSeq` bigint(20) NOT NULL,
  `ReceiptAmount` bigint(20) DEFAULT NULL,
  `ReceiptDate` date DEFAULT NULL,
  PRIMARY KEY (`OrderSeq`),
  KEY `Idx_AW_Receipt1` (`OrderSeq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `AW_Receipt2` (
  `OrderSeq` bigint(20) NOT NULL,
  `Sum_CheckingUseAmount` bigint(20) NOT NULL DEFAULT 0,
  `Max_ReceiptDate` date DEFAULT NULL,
  PRIMARY KEY (`OrderSeq`),
  KEY `Idx_AW_Receipt2` (`OrderSeq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
