/* 請求エラーリストの登録 */
DROP TABLE IF EXISTS `T_ClaimError`;
CREATE TABLE `T_ClaimError` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `OrderSeq` bigint(20) NOT NULL,
  `RegistDate` datetime DEFAULT NULL,
  `ErrorCode` int(11) DEFAULT NULL,
  `ErrorMsg` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Seq`),
  KEY `Idx_T_ClaimError01` (`OrderSeq`),
  KEY `Idx_T_ClaimError02` (`RegistDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* カレンダーマスタの変更 */
ALTER TABLE `T_BusinessCalendar` ADD COLUMN `ToyoBusinessFlg` INT(11) NULL AFTER `Note`;
UPDATE T_BusinessCalendar SET ToyoBusinessFlg = BusinessFlg;

/* 決済ステーション管理の登録 */
DROP TABLE IF EXISTS `T_SmbcRelationControl`;
CREATE TABLE `T_SmbcRelationControl` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `ClaimHistorySeq` bigint(20) NOT NULL,
  `OrderSeq` bigint(20) NOT NULL,
  `OrderCnt` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`Seq`),
  KEY `Idx_T_SmbcRelationControl01` (`ClaimHistorySeq`,`OrderSeq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* 請求バッチ管理の登録 */
DROP TABLE IF EXISTS `T_ClaimBatchControl`;
CREATE TABLE `T_ClaimBatchControl` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `ClaimDate` date DEFAULT NULL,
  `MakeFlg` tinyint NOT NULL DEFAULT 0,
  `SendFlg` tinyint NOT NULL DEFAULT 0,
  `CompFlg` tinyint NOT NULL DEFAULT 0,
  PRIMARY KEY (`Seq`),
  KEY `Idx_T_ClaimBatchControl01` (`ClaimDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* NTTF加盟店IDの登録 */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'NTTFEnterpriseId', '16889', 'NTTF加盟店ID', NOW(), 9, NOW(), 9, '1');

/* 支払期限有効期限 */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'ValidLimitDays1', '14', '支払期限有効期限日数(初回)', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'ValidLimitDays2', '10', '支払期限有効期限日数(督促)', NOW(), 9, NOW(), 9, '1');

/* SFTP接続情報 */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'SFTPIP', '118.238.248.3', '東洋紙業連携(SFTP)で使用するIP', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'SFTPID', 'FTP-CTHBALL', '東洋紙業連携(SFTP)で使用するID', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'SFTPPW', 'Toyo2CTHBL#', '東洋紙業連携(SFTP)で使用するパスワード', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'SFTPKEY', '/var/www/html/htdocs_atobarai/data/sftp/key/TOYOCTHBLFTPSV', '東洋紙業連携(SFTP)で使用する秘密鍵', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'SFTPKEYPP', 'Toyo2CTHBL#', '東洋紙業連携(SFTP)で使用する秘密鍵パスフレーズ', NOW(), 9, NOW(), 9, '1');

/* 請求データ送信バッチエラーメール */
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (98,'請求データ送信バッチエラーメール','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,'xsupport@xseed.co.jp,cb-360resysmember@mb.scroll360.jp','【後払いドットコム】請求データ送信バッチエラーメール','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','株式会社エクシード　ご担当者様\r\n\r\n株式会社キャッチボール基幹システムのエラーメールです。\r\n\r\n株式会社キャッチボール基幹システムより、東洋紙業株式会社宛の請求データ送信が失敗しました。\r\n\r\nオペレーションによるデータ送信をお願い致します。\r\n',0,NOW(),1,NOW(),1,1);

/* 請求件数確認用CSV */
INSERT INTO M_TemplateHeader VALUES( 94 , 'CKI04040_1', 0, 0, 0, '確認用CSV', 1, ',', '\"' ,'*' ,0,'KI04040', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateField VALUES ( 94 , 1, 'ClaimFileName' ,'ファイル名' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 94 , 2, 'OrderCount' ,'レコード数' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);

/* 決済ステーション管理 */
INSERT INTO T_SmbcRelationControl (ClaimHistorySeq, OrderSeq, OrderCnt) 
SELECT DISTINCT 0, oca.OrderSeq, 100 FROM T_OemClaimAccountInfo oca INNER JOIN T_Order o ON (o.OrderSeq = oca.OrderSeq ) WHERE o.OemId = 2;
