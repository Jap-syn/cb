/* 立替精算仮締め バッチロック */
INSERT INTO T_BatchLock (BatchId, ThreadNo, BatchName, BatchLock, UpdateDate) VALUES (4, 1, '立替精算仮締め', 0, null);

/* 退避_立替・売上管理テーブル作成 */
DROP TABLE IF EXISTS `T_PrePayingAndSales`;
CREATE TABLE IF NOT EXISTS `T_PrePayingAndSales` (
  `Seq` bigint(20) NOT NULL auto_increment,
  `OrderSeq` bigint(20) default NULL,
  `OccDate` date default NULL,
  `UseAmount` bigint(20) default NULL,
  `AppSettlementFeeRate` decimal(16, 5) default NULL,
  `SettlementFee` bigint(20) default NULL,
  `ClaimFee` bigint(20) default NULL,
  `ChargeFee` bigint(20) default NULL,
  `ChargeAmount` bigint(20) default NULL,
  `ClearConditionForCharge` int(11) default NULL,
  `ClearConditionDate` date default NULL,
  `ChargeDecisionFlg` int(11) default NULL,
  `ChargeDecisionDate` date default NULL,
  `CancelFlg` int(11) default NULL,
  `PayingControlSeq` bigint(20) default NULL,
  `RegistFlg` int(11) default NULL,
  PRIMARY KEY  (`Seq`),
  UNIQUE KEY `OrderSeq` (`OrderSeq`),
  KEY `idx_01` (`RegistFlg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=0;
