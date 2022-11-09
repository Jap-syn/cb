/* T_ImportedMizuhoFactor登録 */
DROP TABLE IF EXISTS `T_ImportedMizuhoFactor`;
CREATE TABLE `T_ImportedMizuhoFactor` (
   `Seq`             bigint(20)   NOT NULL AUTO_INCREMENT
,  `FileName`        varchar(255) DEFAULT NULL
,  `Status`          int(11)      NOT NULL DEFAULT 0
,  `ReceiptResult`   longtext
,  `RegistDate`      datetime     DEFAULT NULL
,  `RegistId`        int(11)      DEFAULT NULL
,  `UpdateDate`      datetime     DEFAULT NULL
,  `UpdateId`        int(11)      DEFAULT NULL
,  `ValidFlg`        int(11)      NOT NULL DEFAULT '1'
,  PRIMARY KEY (`Seq`)
,  UNIQUE KEY `MizuhoFactorName` (`FileName`)
,  KEY `Idx_T_ImportedMizuhoFactor01` (`FileName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* T_Menu/MenuAuthority登録(みずほファクター入金結果関連) */
INSERT INTO T_Menu VALUES (200, 'cbadmin', 'keiriMenus', 'impmizuhofactor', NULL, '***', 'みずほファクターインポート', 'みずほファクターインポート', '', '', '', NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (200,   1, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (200,  11, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (200, 101, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_Menu VALUES (201, 'cbadmin', 'keiriMenus', 'mizuhofactorlist', NULL, '***', 'みずほファクター結果一覧', 'みずほファクター結果一覧', '', '', '', NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (201,   1, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (201,  11, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (201, 101, NOW(), 9, NOW(), 9, 1);
