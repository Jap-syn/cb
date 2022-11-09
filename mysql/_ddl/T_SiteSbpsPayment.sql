CREATE TABLE IF NOT EXISTS `T_SiteSbpsPayment` (
  `SiteSbpsPaymentId` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'シーケンス',
  `SiteId` bigint(20) NOT NULL COMMENT 'サイトID',
  `ValidFlg` int(1) NOT NULL DEFAULT '1' COMMENT '利用可否（0：利用不可　1：利用可能）',
  `PaymentId` int(1) NOT NULL COMMENT '支払方法(M_SbpsPayment.SbpsPaymentId)',
  `ContractorId` int(11) NOT NULL COMMENT '契約先(M_Code.CodeId=212)',
  `SettlementFeeRate` decimal(16,5) DEFAULT NULL COMMENT '決済手数料率',
  `ClaimFeeBS` int(11) DEFAULT NULL COMMENT '請求手数料(別送)',
  `ClaimFeeDK` int(11) DEFAULT NULL COMMENT '請求手数料(同梱)',
  `NumUseDay` int(3) DEFAULT NULL COMMENT '利用期間',
  `UseStartDate` datetime DEFAULT NULL COMMENT '利用開始日時',
  `RegistDate` datetime NOT NULL COMMENT '登録日時',
  `RegistId` int(11) NOT NULL COMMENT '登録者',
  `UpdateDate` datetime NOT NULL COMMENT '更新日時',
  `UpdateId` int(11) NOT NULL COMMENT '更新者',
  PRIMARY KEY (`SiteSbpsPaymentId`),
  KEY `Idx_01` (`SiteSbpsPaymentId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='届いてから決済のサイト別の支払可能種類';