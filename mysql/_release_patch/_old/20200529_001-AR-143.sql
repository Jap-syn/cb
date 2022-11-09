/* バッチ排他制御 */
DROP TABLE IF EXISTS `T_BatchLock`;
CREATE TABLE `T_BatchLock` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `BatchId` tinyint(4) NOT NULL DEFAULT '0',
  `ThreadNo` tinyint(4) NOT NULL DEFAULT '0',
  `BatchName` varchar(100) DEFAULT NULL,
  `BatchLock` bigint(20) NOT NULL DEFAULT '0',
  `UpdateDate` datetime DEFAULT NULL,
  PRIMARY KEY (`Seq`),
  UNIQUE KEY `BatchId_UNIQUE` (`BatchId`),
  UNIQUE KEY `ThreadNo_UNIQUE` (`ThreadNo`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO T_BatchLock (BatchId, ThreadNo, BatchName, BatchLock, UpdateDate) VALUES (1, 1, '請求書発行案内メール', 0, NOW());
