/* T_ImportedNttSmartTrade登録 */
DROP TABLE IF EXISTS `T_ImportedNttSmartTrade`;
CREATE TABLE `T_ImportedNttSmartTrade` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `FileName` varchar(255) DEFAULT NULL,
  `Status` int(11) NOT NULL DEFAULT 0,
  `ReceiptResult` longtext,
  `RegistDate` datetime DEFAULT NULL,
  `RegistId` int(11) DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `UpdateId` int(11) DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`Seq`),
  UNIQUE KEY `NttSmartTradeName` (`FileName`),
  KEY `Idx_T_ImportedNttSmartTrade01` (`FileName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* T_Menu/MenuAuthority登録(NTTスマートトレード結果関連) */
INSERT INTO T_Menu VALUES (191, 'cbadmin', 'keiriMenus', 'impnttst', NULL, '***', 'NTTスマートトレードインポート', 'NTTスマートトレードインポート', '', '', '', NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (191, 1, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (191, 11, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (191, 101, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_Menu VALUES (192, 'cbadmin', 'keiriMenus', 'nttstlist', NULL, '***', 'NTTスマートトレード結果一覧', 'NTTスマートトレード結果一覧', '', '', '', NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (192, 1, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (192, 11, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (192, 101, NOW(), 9, NOW(), 9, 1);
/* 振込手数料額 */
INSERT INTO M_Code VALUES(93, 5 ,'0円(CB負担)','3' ,'0' , '999999999' ,'0' ,1, NOW(), 1, NOW(), 1, 1);
/* 振込手数料 */
INSERT INTO M_Code VALUES(56, 3 ,'0円(CB負担)',NULL ,NULL ,NULL ,'' ,0, NOW(), 1, NOW(), 1, 1);