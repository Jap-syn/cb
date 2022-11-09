CREATE TABLE `T_MailSubject` (
  `MailSubject` varchar(255) NOT NULL,
  `FailAttfileFlg` tinyint(4) NOT NULL DEFAULT '0',
  `FailExtfileFlg` tinyint(4) NOT NULL DEFAULT '0',
  `FailNameFlg` tinyint(4) NOT NULL DEFAULT '0',
  `FailAddressFlg` tinyint(4) NOT NULL DEFAULT '0',
  `FailBirthFlg` tinyint(4) NOT NULL DEFAULT '0',
  `ChkFlg` tinyint(4) NOT NULL DEFAULT '0',
  `ListPrintFlg` tinyint(4) NOT NULL DEFAULT '0',
  `RegistDate` datetime DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `AttfileName` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`MailSubject`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
