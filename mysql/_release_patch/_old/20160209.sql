CREATE TABLE `AW_Receipt3` (
  `OrderSeq` bigint(20) NOT NULL,
  `Rct_CancelFlg` int NOT NULL,
  PRIMARY KEY (`OrderSeq`),
  KEY `Idx_AW_Receipt3` (`OrderSeq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE AT_ReceiptControl ADD INDEX Idx_AT_ReceiptControl01 (Rct_CancelFlg ASC);
ALTER TABLE T_PayingAndSales ADD INDEX Idx_T_PayingAndSales04 (ClearConditionDate ASC);
