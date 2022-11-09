CREATE TABLE `T_SbpsReceiptControl` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '注文Seq',
  `OrderSeq` bigint(20) NOT NULL COMMENT '注文Seq',
  `PayType` int(1) NOT NULL COMMENT '追加支払い方法_区分1：届いてから決済',
  `PaymentName` varchar(30) NOT NULL COMMENT '支払方法（SBPSからの戻り値を設定。）',
  `ReceiptDate` datetime NOT NULL COMMENT '決済完了日時',
  `RegistDate` datetime NOT NULL COMMENT '登録日時',
  `RegistId` int(11) NOT NULL COMMENT '登録者',
  `UpdateDate` datetime NOT NULL COMMENT '更新日時',
  `UpdateId` int(11) NOT NULL COMMENT '更新者',
  `ValidFlg` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`Seq`),
  KEY `Idx_01` (`OrderSeq`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='届いてから決済管理';