CREATE TABLE IF NOT EXISTS `m_code` (
  `CodeId` int(11) NOT NULL,
  `KeyCode` int(11) NOT NULL,
  `KeyContent` varchar(100) DEFAULT NULL,
  `Class1` varchar(30) DEFAULT NULL,
  `Class2` varchar(30) DEFAULT NULL,
  `Class3` varchar(30) DEFAULT NULL,
  `Class4` varchar(30) DEFAULT NULL COMMENT '区分4',
  `Note` varchar(4000) DEFAULT NULL,
  `SystemFlg` tinyint(4) NOT NULL DEFAULT '0',
  `RegistDate` datetime DEFAULT NULL,
  `RegistId` int(11) DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `UpdateId` int(11) DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`CodeId`,`KeyCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
