/* 入金方法追加 */
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (198, 8, 'PayEasy', null, null, null, '仮', 0, NOW(), 83, NOW(), 83,1 );

/* Payeasy連携エラーメッセージ追加 */
INSERT INTO M_CodeManagement VALUES(206, 'Payeasy連携エラーメッセージ', NULL, 'Payeasy連携エラーメッセージ', 1, NULL, 0, NULL, 0, NULL, NOW(), 9, NOW(), 9, 1);
/* Payeasy連携エラーメッセージ（中間）追加 */
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES ('206', '00001', '正常終了(収納機関側画面で金額変更)', null, null, null, '収納機関からの返却コード', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES ('206', '10001', 'キャンセル(収納機関選択画面)', null, null, null, '収納機関からの返却コード', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES ('206', '10002', '有効期限切れ', null, null, null, '収納機関からの返却コード', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES ('206', '10003', 'パラメタエラー', null, null, null, '収納機関からの返却コード', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES ('206', '10004', '決済金額エラー', null, null, null, '収納機関からの返却コード', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES ('206', '10005', 'プロトコルバージョン未サポート', null, null, null, '収納機関からの返却コード', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES ('206', '20001', 'お客さまキャンセルに起因する収納機関からのエラー', null, null, null, '収納機関からの返却コード', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES ('206', '20002', '加盟店に起因する収納機関からのエラー', null, null, null, '収納機関からの返却コード', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES ('206', '20003', '収納機関システムに起因するエラー', null, null, null, '収納機関からの返却コード', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES ('206', '20004', 'サービス時間外に起因する収納機関からのエラー', null, null, null, '収納機関からの返却コード', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES ('206', '20005', '残高不足など、お客さまに起因する収納機関からのエラー', null, null, null, '収納機関からの返却コード', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
/* Payeasy連携エラーメッセージ（下位）追加 */
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES ('206', '50001', 'ステータス不正', null, null, null, '決済ナビでのエラーコード', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES ('206', '50002', 'ハッシュ値不正', null, null, null, '決済ナビでのエラーコード', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES ('206', '50003', '収納機関から返却されるパラメタのチェックで問題が発生', null, null, null, '決済ナビでのエラーコード', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES ('206', '50004', '指定された取引は既に受付済', null, null, null, '決済ナビでのエラーコード', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES ('206', '60001', '加盟店のご都合によりサービス停止状態', null, null, null, '決済ナビでのエラーコード', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES ('206', '80001', '決済ナビシステム障害', null, null, null, '決済ナビでのエラーコード', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES ('206', '90001', 'その他エラー', null, null, null, '決済ナビでのエラーコード', 0, NOW(), 83, NOW(), 83,1 );

/* Payeasy入金データの一時登録 */
DROP TABLE IF EXISTS `T_PayeasyReceived`;
CREATE TABLE `T_PayeasyReceived` (
  `Seq`          bigint(20)   NOT NULL AUTO_INCREMENT
, `p_ver`        varchar(14)  DEFAULT NULL
, `stdate`       varchar(18)  DEFAULT NULL
, `stran`        varchar(16)  DEFAULT NULL
, `bkcode`       varchar(14)  DEFAULT NULL
, `shopid`       varchar(16)  DEFAULT NULL
, `cshopid`      varchar(15)  DEFAULT NULL
, `amount`       varchar(20)  DEFAULT NULL
, `mbtran`       varchar(35)  DEFAULT NULL
, `bktrans`      varchar(34)  DEFAULT NULL
, `tranid`       varchar(120) DEFAULT NULL
, `ddate`        varchar(18)  DEFAULT NULL
, `tdate`        varchar(18)  DEFAULT NULL
, `rsltcd`       varchar(23)  DEFAULT NULL
, `rchksum`      varchar(42)  DEFAULT NULL
, `ProcessedFlg` varchar(1)   DEFAULT '0'
,  PRIMARY KEY (`Seq`)
,  KEY `Idx_T_PayeasyReceived01` (`ProcessedFlg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* Payeasy入金エラーリストの登録 */
DROP TABLE IF EXISTS `T_PayeasyError`;
CREATE TABLE `T_PayeasyError` (
  `Seq`           bigint(20)   NOT NULL AUTO_INCREMENT
, `OrderSeq`      bigint(20)
, `PaymentAmount` bigint(20)
, `RegistDate`    datetime     DEFAULT NULL
, `ErrorCode`     int(11)      DEFAULT NULL
, `ErrorMsg`      varchar(255) DEFAULT NULL
, PRIMARY KEY (`Seq`)
, KEY `Idx_T_PayeasyError01` (`OrderSeq`)
, KEY `Idx_T_PayeasyError02` (`RegistDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
