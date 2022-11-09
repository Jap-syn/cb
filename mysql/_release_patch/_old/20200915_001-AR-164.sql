/* 01. テーブル作成 */
DROP TABLE IF EXISTS `T_Smbcpa`;
DROP TABLE IF EXISTS `T_SmbcpaAccount`;
DROP TABLE IF EXISTS `T_SmbcpaAccountGroup`;
DROP TABLE IF EXISTS `T_SmbcpaAccountImportWork`;
DROP TABLE IF EXISTS `T_SmbcpaAccountUsageHistory`;
DROP TABLE IF EXISTS `T_SmbcpaPaymentNotification`;
DROP TABLE IF EXISTS `M_SmbcpaBranch`;

CREATE TABLE `T_Smbcpa` (
  `SmbcpaId` bigint(20) NOT NULL AUTO_INCREMENT,
  `OemId` bigint(20) NOT NULL,
  `RegistDate` datetime DEFAULT NULL,
  `DisplayName` varchar(100) DEFAULT NULL,
  `Memo` text,
  `BankName` varchar(255) DEFAULT NULL,
  `BankCode` varchar(4) DEFAULT NULL,
  `ValidFlg` int(11) DEFAULT '1',
  PRIMARY KEY (`SmbcpaId`),
  UNIQUE KEY `Idx_T_Smbcpa01` (`OemId`),
  KEY `Idx_T_Smbcpa02` (`ValidFlg`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `T_SmbcpaAccount` (
  `AccountSeq` bigint(20) NOT NULL AUTO_INCREMENT,
  `SmbcpaId` bigint(20) NOT NULL,
  `AccountGroupId` bigint(20) NOT NULL,
  `RegistDate` datetime DEFAULT NULL,
  `BranchCode` varchar(3) NOT NULL,
  `AccountNumber` varchar(20) NOT NULL,
  `AccountHolder` varchar(255) DEFAULT NULL,
  `Status` int(11) NOT NULL DEFAULT '0',
  `LastStatusChanged` datetime NOT NULL,
  `NumberingDate` date DEFAULT NULL,
  `EffectiveDate` date DEFAULT NULL,
  `ModifiedDate` date DEFAULT NULL,
  `SmbcpaStatus` varchar(255) DEFAULT NULL,
  `ExpirationDate` datetime DEFAULT NULL,
  `LastReceiptDate` datetime DEFAULT NULL,
  `ReleasedDate` datetime DEFAULT NULL,
  PRIMARY KEY (`AccountSeq`),
  UNIQUE KEY `Idx_T_SmbcpaAccount02` (`BranchCode`,`AccountNumber`),
  KEY `Idx_T_SmbcpaAccount01` (`AccountGroupId`,`SmbcpaId`),
  KEY `Idx_T_SmbcpaAccount03` (`Status`),
  KEY `Idx_T_SmbcpaAccount04` (`LastStatusChanged`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `T_SmbcpaAccountGroup` (
  `AccountGroupId` bigint(20) NOT NULL AUTO_INCREMENT,
  `SmbcpaId` bigint(20) NOT NULL,
  `RegistDate` datetime DEFAULT NULL,
  `TotalAccounts` int(11) DEFAULT NULL,
  `ManageKey` varchar(20) DEFAULT NULL,
  `ManageKeyLabel` varchar(255) DEFAULT NULL,
  `DepositClass` int(11) NOT NULL DEFAULT '0',
  `ReturnedFlg` int(11) NOT NULL DEFAULT '0',
  `ReturnedDate` datetime DEFAULT NULL,
  PRIMARY KEY (`AccountGroupId`),
  KEY `Idx_T_SmbcpaAccountGroup01` (`SmbcpaId`),
  KEY `Idx_T_SmbcpaAccountGroup02` (`ManageKey`),
  KEY `Idx_T_SmbcpaAccountGroup03` (`ReturnedFlg`),
  KEY `Idx_T_SmbcpaAccountGroup04` (`ManageKeyLabel`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `T_SmbcpaAccountImportWork` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `SmbcpaId` bigint(20) NOT NULL,
  `AccountGroupId` bigint(20) DEFAULT NULL,
  `ProcessKey` varchar(100) NOT NULL,
  `StartTime` datetime NOT NULL,
  `EndTime` datetime DEFAULT NULL,
  `BranchCode` varchar(3) DEFAULT NULL,
  `AccountNumber` varchar(20) DEFAULT NULL,
  `AccountHolder` varchar(255) DEFAULT NULL,
  `ManageKey` varchar(20) DEFAULT NULL,
  `ManageKeyLabel` varchar(255) DEFAULT NULL,
  `NumberingDate` varchar(255) DEFAULT NULL,
  `EffectiveDate` varchar(255) DEFAULT NULL,
  `ModifiedDate` varchar(255) DEFAULT NULL,
  `SmbcpaStatus` varchar(255) DEFAULT NULL,
  `ExpirationDate` varchar(255) DEFAULT NULL,
  `LastReceiptDate` varchar(255) DEFAULT NULL,
  `ReleasedDate` varchar(255) DEFAULT NULL,
  `DeleteFlg` int(11) DEFAULT '0',
  `OpId` bigint(20) DEFAULT NULL,
  `CsvError` varchar(255) DEFAULT NULL,
  `ImportError` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Seq`),
  KEY `Idx_T_SmbcpaAccountImportWork01` (`ProcessKey`),
  KEY `Idx_T_SmbcpaAccountImportWork02` (`StartTime`),
  KEY `Idx_T_SmbcpaAccountImportWork03` (`BranchCode`),
  KEY `Idx_T_SmbcpaAccountImportWork04` (`AccountGroupId`),
  KEY `Idx_T_SmbcpaAccountImportWork05` (`SmbcpaId`),
  KEY `Idx_T_SmbcpaAccountImportWork06` (`DeleteFlg`),
  KEY `Idx_T_SmbcpaAccountImportWork07` (`OpId`),
  KEY `Idx_T_SmbcpaAccountImportWork08` (`CsvError`),
  KEY `Idx_T_SmbcpaAccountImportWork09` (`ImportError`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `T_SmbcpaAccountUsageHistory` (
  `UsageHistorySeq` bigint(20) NOT NULL AUTO_INCREMENT,
  `AccountSeq` bigint(20) NOT NULL,
  `UsedDate` datetime DEFAULT NULL,
  `MostRecent` int(11) NOT NULL DEFAULT '0',
  `Type` int(11) NOT NULL,
  `OrderSeq` bigint(20) NOT NULL,
  `CloseReason` int(11) DEFAULT NULL,
  `CloseMemo` text,
  `DeleteFlg` int(11) DEFAULT '0',
  PRIMARY KEY (`UsageHistorySeq`),
  KEY `Idx_T_SmbcpaAccountUsageHistory01` (`AccountSeq`),
  KEY `Idx_T_SmbcpaAccountUsageHistory02` (`Type`),
  KEY `Idx_T_SmbcpaAccountUsageHistory03` (`OrderSeq`),
  KEY `Idx_T_SmbcpaAccountUsageHistory04` (`MostRecent`),
  KEY `Idx_T_SmbcpaAccountUsageHistory05` (`CloseReason`),
  KEY `Idx_T_SmbcpaAccountUsageHistory06` (`DeleteFlg`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `T_SmbcpaPaymentNotification` (
  `NotificationSeq` bigint(20) NOT NULL AUTO_INCREMENT,
  `TransactionId` varchar(30) NOT NULL,
  `ReceivedDate` datetime NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `ResponseDate` datetime DEFAULT NULL,
  `ReceivedRawData` text,
  `ResponseRawData` text,
  `ReqBranchCode` varchar(3) NOT NULL,
  `ReqAccountNumber` varchar(20) NOT NULL,
  `AccountSeq` bigint(20) DEFAULT NULL,
  `OrderSeq` bigint(20) DEFAULT NULL,
  `ReceiptAmount` bigint(20) NOT NULL,
  `ReceiptProcessDate` datetime DEFAULT NULL,
  `ReceiptDate` date DEFAULT NULL,
  `LastProcessDate` datetime DEFAULT NULL,
  `RejectReason` varchar(100) DEFAULT NULL,
  `DeleteFlg` int(11) DEFAULT '0',
  PRIMARY KEY (`NotificationSeq`),
  KEY `Idx_T_SmbcpaPaymentNotification01` (`TransactionId`),
  KEY `Idx_T_SmbcpaPaymentNotification02` (`ReceivedDate`),
  KEY `Idx_T_SmbcpaPaymentNotification03` (`Status`),
  KEY `Idx_T_SmbcpaPaymentNotification04` (`ResponseDate`),
  KEY `Idx_T_SmbcpaPaymentNotification05` (`AccountSeq`),
  KEY `Idx_T_SmbcpaPaymentNotification06` (`OrderSeq`),
  KEY `Idx_T_SmbcpaPaymentNotification07` (`Status`,`ReceiptProcessDate`,`DeleteFlg`)
) ENGINE=InnoDB AUTO_INCREMENT=148 DEFAULT CHARSET=utf8;

CREATE TABLE `M_SmbcpaBranch` (
  `SmbcpaBranchCode` varchar(3) NOT NULL DEFAULT '',
  `SmbcpaBranchName` varchar(255) NOT NULL,
  PRIMARY KEY (`SmbcpaBranchCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* 02. T_SystemProperty登録 */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','smbcpaconf', 'DefaultBankName', '三井住友銀行', 'SMBCパーフェクト口座設定：デフォルトのSMBCパーフェクト口座銀行名', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','smbcpaconf', 'DefaultBankCode', '0009', 'SMBCパーフェクト口座設定：デフォルトのSMBCパーフェクト口座金融機関コード', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','smbcpaconf', 'ReleaseAfterReceiptInterval', '30', 'SMBCパーフェクト口座設定：入金済み口座の開放待ち日数', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','smbcpaconf', 'ForceReleaseOverReclaim7LimitInterval', '65', 'SMBCパーフェクト口座設定：再請求7期限超過後の強制口座解放までの猶予日数', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','smbcpaconf', 'AllowRestoreReturnedAccounts', '', 'SMBCパーフェクト口座設定：返却済み口座復活機能の利用許可', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','smbcpaconf', 'AllowDebugReceiptForm', '', 'SMBCパーフェクト口座設定：デバッグ用入金通知シミュレーターの利用許可', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','smbcpaconf', 'AllowFullUrlToDebugReceiptForm', '', 'SMBCパーフェクト口座設定：入金通知シミュレーターの送信先URLにフルURLを許容するかの設定', NOW(), 9, NOW(), 9, '1');

/* 03. T_Menu登録 */
INSERT INTO T_Menu VALUES (196, 'cbadmin', 'keiriMenus', 'smbcparcpt', NULL, '***', 'SMBCバーチャル口座手動入金確認', 'SMBCバーチャル口座手動入金確認', '', '', '', NOW(), 9, NOW(), 9, 1);
INSERT INTO T_Menu VALUES (197, 'cbadmin', 'kanriMenus', 'smbcpabr', NULL, '***', 'SMBCバーチャル口座支店マスター管理', 'SMBCバーチャル口座支店マスター管理', '', '', '', NOW(), 9, NOW(), 9, 1);
INSERT INTO T_Menu VALUES (198, 'cbadmin', 'kanriMenus', 'smbcpalist', NULL, '***', 'SMBCバーチャル口座契約一覧', 'SMBCバーチャル口座契約一覧', '', '', '', NOW(), 9, NOW(), 9, 1);

/* 04. T_MenuAuthority登録 */
INSERT INTO T_MenuAuthority VALUES (196, 1, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (196, 11, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (196, 101, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (197, 1, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (197, 11, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (197, 101, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (198, 1, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (198, 11, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (198, 101, NOW(), 9, NOW(), 9, 1);
