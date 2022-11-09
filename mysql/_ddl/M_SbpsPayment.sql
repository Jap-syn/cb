CREATE TABLE IF NOT EXISTS `M_SbpsPayment` (
  `SbpsPaymentId` int(11) NOT NULL AUTO_INCREMENT COMMENT 'SBPS支払方法ID',
  `OemId` bigint(20) DEFAULT NULL COMMENT 'OEMID',
  `PaymentGroupName` varchar(50) DEFAULT NULL COMMENT '支払方法グループ名称\r\n例：届いてから',
  `PaymentName` varchar(50) DEFAULT NULL,
  `PaymentNameKj` varchar(50) DEFAULT NULL COMMENT '支払方法名称\r\n例：楽天Pay、LinePay',
  `SortId` int(11) NOT NULL DEFAULT '0' COMMENT '表示順',
  `LogoUrl` varchar(500) DEFAULT NULL,
  `CancelApiId` varchar(500) NOT NULL DEFAULT '1',
  `MailParameterNameKj` varchar(50) NOT NULL DEFAULT '1',
  `ValidFlg` tinyint(1) NOT NULL DEFAULT '1' COMMENT '有効フラグ　（0：無効　1：有効）',
  `RegistDate` datetime NOT NULL COMMENT '登録日時',
  `RegistId` int(11) NOT NULL COMMENT '登録者',
  `UpdateDate` datetime NOT NULL COMMENT '更新日時',
  `UpdateId` int(11) NOT NULL COMMENT '更新者',
  PRIMARY KEY (`SbpsPaymentId`),
  KEY `Idx_01` (`SbpsPaymentId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='届いてから決済の支払可能種類';