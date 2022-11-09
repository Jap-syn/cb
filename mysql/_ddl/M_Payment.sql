CREATE TABLE IF NOT EXISTS `M_Payment` (
`PaymentId` int(11) NOT NULL AUTO_INCREMENT COMMENT '支払方法ID',
`OemId` bigint(20) NOT NULL COMMENT 'OEMID',
`PaymentGroupName` varchar(50) NOT NULL COMMENT '支払方法グループ名称',
`PaymentName` varchar(50) NOT NULL COMMENT '支払方法名称',
`SortId` int(11) NOT NULL DEFAULT '0' COMMENT '表示順',
`UseFlg` tinyint(4) NOT NULL DEFAULT '0' COMMENT '使用可否区分',
`FixedId` tinyint(4) NOT NULL DEFAULT '0' COMMENT '固有ロジックID',
`ValidFlg` tinyint(4) NOT NULL DEFAULT '1' COMMENT '有効フラグ',
`LogoUrl` varchar(500) DEFAULT NULL,
PRIMARY KEY (`PaymentId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8