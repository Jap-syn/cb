CREATE TABLE `T_CreditPayment`  (
  `OrderSeq` bigint(20) NOT NULL,
  `PaymentType` int(11) NOT NULL DEFAULT 0,
  `RegistDate` datetime NULL DEFAULT NULL,
  `UpdateDate` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`OrderSeq`) USING BTREE
);
