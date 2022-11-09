/* T_ImportedSmbcPerfect�o�^ */
DROP TABLE IF EXISTS `T_ImportedSmbcPerfect`;
CREATE TABLE `T_ImportedSmbcPerfect` (
   `Seq`             BIGINT(20)   NOT NULL AUTO_INCREMENT
,  `FileName`        VARCHAR(255) DEFAULT NULL
,  `Status`          INT(11)      NOT NULL DEFAULT 0
,  `ReceiptResult`   LONGTEXT
,  `RegistDate`      DATETIME     DEFAULT NULL
,  `RegistId`        INT(11)      DEFAULT NULL
,  `UpdateDate`      DATETIME     DEFAULT NULL
,  `UpdateId`        INT(11)      DEFAULT NULL
,  `ValidFlg`        INT(11)      NOT NULL DEFAULT '1'
,  PRIMARY KEY (`Seq`)
,  UNIQUE KEY `SmbcPerfectName` (`FileName`)
,  KEY `Idx_T_ImportedSmbcPerfect01` (`FileName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* T_Menu/MenuAuthority�o�^(SMBC�p�[�t�F�N�g�����������ʊ֘A) */
INSERT INTO T_Menu VALUES (202, 'cbadmin', 'keiriMenus', 'impsmbcperfect' , NULL, '***', 'SMBC�p�[�t�F�N�g�����C���|�[�g', 'SMBC�p�[�t�F�N�g�����C���|�[�g', '', '', '', NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (202,   1, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (202,  11, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (202, 101, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_Menu VALUES (203, 'cbadmin', 'keiriMenus', 'smbcperfectlist', NULL, '***', 'SMBC�p�[�t�F�N�g�������ʈꗗ'  , 'SMBC�p�[�t�F�N�g�������ʈꗗ'  , '', '', '', NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (203,   1, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (203,  11, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (203, 101, NOW(), 9, NOW(), 9, 1);
