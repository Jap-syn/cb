/* 取りまとめ着荷確認の登録 */
DROP TABLE IF EXISTS `T_CombinedArrival`;
CREATE TABLE `T_CombinedArrival` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `OrderSeq` bigint(20) NOT NULL,
  `Deli_JournalNumber` varchar(255) DEFAULT NULL,
  `Deli_ConfirmArrivalDate` datetime DEFAULT NULL,
  PRIMARY KEY (`Seq`),
  KEY `Idx_T_CombinedArrival01` (`OrderSeq`,`Deli_JournalNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;