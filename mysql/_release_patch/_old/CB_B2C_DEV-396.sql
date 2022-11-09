-- =================================================================================================
-- 事前登録
-- =================================================================================================
CREATE TABLE `M_ClaimPrintCheck`  (
  `ClaimPrintCheckSeq` bigint(20) NOT NULL AUTO_INCREMENT,
  `ClaimPrintCheckName` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `PrintFormCd` varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `PrintTypeCd` varchar(7) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `PrintIssueCd` varchar(7) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `PrintIssueCountCd` varchar(2) NULL DEFAULT NULL,
  `PrintNote` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `RegistDate` datetime NULL DEFAULT NULL,
  `RegistId` int(11) NULL DEFAULT NULL,
  `UpdateDate` datetime NULL DEFAULT NULL,
  `UpdateId` int(11) NULL DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`ClaimPrintCheckSeq`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci;
CREATE TABLE `M_PaymentCheck`  (
  `PaymentCheckSeq` bigint(20) NOT NULL AUTO_INCREMENT,
  `ImageName` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `PrintPatternCd` varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `SpPaymentCd` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `RegistDate` datetime NULL DEFAULT NULL,
  `RegistId` int(11) NULL DEFAULT NULL,
  `UpdateDate` datetime NULL DEFAULT NULL,
  `UpdateId` int(11) NULL DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`PaymentCheckSeq`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci;
CREATE TABLE `T_ClaimPrintPattern`  (
  `ClaimPrintPatternSeq` bigint(20) NOT NULL AUTO_INCREMENT,
  `EnterpriseId` bigint(20) NOT NULL,
  `SiteId` bigint(20) NOT NULL,
  `PrintIssueCountCd` varchar(2) NULL DEFAULT NULL,
  `PrintPatternCd` varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `PrintFormCd` varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `PrintTypeCd` varchar(7) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `EnclosedSpecCd` varchar(5) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `PrintIssueCd` varchar(7) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `SpPaymentCd` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `AdCd` varchar(5) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `EnclosedAdCd` varchar(5) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `RegistDate` datetime NULL DEFAULT NULL,
  `RegistId` int(11) NULL DEFAULT NULL,
  `UpdateDate` datetime NULL DEFAULT NULL,
  `UpdateId` int(11) NULL DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`ClaimPrintPatternSeq`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci;
create index Idx_T_ClaimPrintPattern01 on T_ClaimPrintPattern(EnterpriseId, SiteId, PrintIssueCountCd);

-- M_SbpsPayment
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

INSERT INTO `M_SbpsPayment` (`SbpsPaymentId`, `OemId`, `PaymentGroupName`, `PaymentName`, `PaymentNameKj`, `SortId`, `LogoUrl`, `CancelApiId`, `MailParameterNameKj`, `ValidFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`) VALUES
(1, 0, '届いてから', 'credit_vm', 'クレジット(VISA/MASTER）', 1, 'my_page/credit_VISA-Master.png', 'ST02-00303-101', 'クレジット', 1, now(), 1, now(), 1),
(2, 0, '届いてから', 'credit_ja', 'クレジット(JCB/AMEX）', 2, 'my_page/credit_JCB-Amex.png', 'ST02-00303-101', 'クレジット', 1, now(), 1, now(), 1),
(3, 0, '届いてから', 'credit_d', 'クレジット(Dinars）', 3, 'my_page/credit_DinersClub.png', 'ST02-00303-101', 'クレジット', 1, now(), 1, now(), 1),
(4, 0, '届いてから', 'paypay', 'PayPay（オンライン決済）', 4, 'my_page/todo_paypay.png', 'ST02-00306-311', 'PayPay（オンライン決済）', 1, now(), 1, now(), 1),
(5, 0, '届いてから', 'linepay', 'LINEPay', 5, 'my_page/todo_LINEpay.png', 'ST02-00306-310', 'LINEPay', 1, now(), 1, now(), 1),
(6, 0, '届いてから', 'softbank2', 'ソフトバンクまとめて支払い', 6, 'my_page/todo_softbank.png', 'ST02-00303-405', 'ソフトバンクまとめて支払い,ワイモバイルまとめて支払い', 1, now(), 1, now(), 1),
(7, 0, '届いてから', 'docomo', 'ドコモ払い', 7, 'my_page/todo_docomo.png', 'ST02-00303-401', 'ドコモ払い', 1, now(), 1, now(), 1),
(8, 0, '届いてから', 'auone', 'auかんたん決済', 8, 'my_page/todo_au.png', 'ST02-00303-402', 'auかんたん決済', 1, now(), 1, now(), 1),
(9, 0, '届いてから', 'rakuten', '楽天ペイ（オンライン決済）', 9, 'my_page/todo_Rakuten.png', 'ST02-00306-305', '楽天ペイ（オンライン決済）', 1, now(), 1, now(), 1);
	
-- T_SiteSbpsPayment
CREATE TABLE IF NOT EXISTS `T_SiteSbpsPayment` (
  `SiteSbpsPaymentId` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'シーケンス',
  `SiteId` bigint(20) NOT NULL COMMENT 'サイトID',
  `ValidFlg` int(1) NOT NULL DEFAULT '1' COMMENT '利用可否（0：利用不可　1：利用可能）',
  `PaymentId` int(1) NOT NULL COMMENT '支払方法(M_SbpsPayment.SbpsPaymentId)',
  `ContractorId` int(11) NOT NULL COMMENT '契約先(M_Code.CodeId=212)',
  `SettlementFeeRate` decimal(16,5) DEFAULT NULL COMMENT '決済手数料率',
  `ClaimFeeBS` int(11) DEFAULT NULL COMMENT '請求手数料(別送)',
  `ClaimFeeDK` int(11) DEFAULT NULL COMMENT '請求手数料(同梱)',
  `NumUseDay` int(3) DEFAULT NULL COMMENT '利用期間',
  `UseStartDate` datetime DEFAULT NULL COMMENT '利用開始日時',
  `RegistDate` datetime NOT NULL COMMENT '登録日時',
  `RegistId` int(11) NOT NULL COMMENT '登録者',
  `UpdateDate` datetime NOT NULL COMMENT '更新日時',
  `UpdateId` int(11) NOT NULL COMMENT '更新者',
  PRIMARY KEY (`SiteSbpsPaymentId`),
  KEY `Idx_01` (`SiteSbpsPaymentId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='届いてから決済のサイト別の支払可能種類';

--現行の届いてからクレジットを新テーブルに移行
REPLACE INTO T_SiteSbpsPayment(
SiteId
,ValidFlg
,ContractorId
,PaymentId
,SettlementFeeRate
,ClaimFeeBS
,ClaimFeeDK
,NumUseDay
,UseStartDate
,RegistDate
,RegistId
,UpdateDate
,UpdateId
)
SELECT
SiteId
,PaymentAfterArrivalFlg
,2
,1
,CSSettlementFeeRate
,CSClaimFeeBS
,CSClaimFeeDK
,10
,now()
,now()
,1
,now()
,1
FROM T_Site
WHERE CSSettlementFeeRate IS NOT NULL AND CSClaimFeeBS IS NOT NULL AND CSClaimFeeDK IS NOT NULL
;

-- 後は手動で画面から登録する
INSERT INTO T_MailTemplate(Class, ClassName, FromTitle, FromTitleMime, FromAddress, ToTitle, ToTitleMime, ToAddress, Subject, SubjectMime, Body, OemId, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (125, '印刷パターンチェックエラー', '後払いドットコム', '=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=', 'customer2@ato-barai.com', NULL, NULL, NULL, '印刷パターンチェックエラー', '=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=', '印刷パターンチェックでエラーが発生しました。\r\nサイトマスタの更新をしてください。\r\n\r\n{body}', NULL, NOW(), '1', NOW(), 1, 1);

INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','ClaimPrintErrorMail', 'MailTo', 'k-minatoya@alahaka.co.jp', '印刷パターンチェックエラーメール宛先', NOW(), 0, NOW(), 0, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','kyodoinfo', 'SFTPPORT', '10022', '共同印刷連携(SFTP)で使用するPORT', NOW(), 0, NOW(), 0, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','kyodoinfo', 'SFTPIP', '210.136.16.88', '共同印刷連携(SFTP)で使用するIP', NOW(), 0, NOW(), 0, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','kyodoinfo', 'SFTPID', 'ATO-CATCHBALL-T', '共同印刷連携(SFTP)で使用するID', NOW(), 0, NOW(), 0, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','kyodoinfo', 'SFTPKEY', '/var/www/html/htdocs_www4/data/sftp/key/KYODOCTHBLFTPSV', '共同印刷連携(SFTP)で使用する秘密鍵', NOW(), 0, NOW(), 0, '1');

INSERT INTO M_TemplateHeader VALUES ('106', 'CKI04050_1', '0', '0', '0', '初回請求書印刷データCSV', '1', ',', '\"', 'UTF-8', '0', 'KI04042', null, NOW(), '1', NOW(), '1', '1');

INSERT INTO M_TemplateField VALUES ('106', '1', 'OemId', 'ビジネス区分', 'INT', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '2', 'ClaimPrintCheckSeq', '印刷帳票＋版下＋発行元＋発行回数　コード', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '3', 'ClaimPrintCheckName', '印刷帳票＋版下＋発行元＋発行回数　名称', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '4', 'PaymentCheckSeq', '印字パターン＋スマホ決済　コード', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '5', 'ImageName', '印字パターン＋スマホ決済　名称', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '6', 'PrintPatternCd', '印字パターン', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '7', 'PrintFormCd', '印刷帳票', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '8', 'PrintTypeCd', '版下', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '9', 'EnclosedSpecCd', '請求種別', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '10', 'PrintIssueCd', '発行元', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '11', 'SpPaymentCd', 'スマホ決済', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '12', 'PrintIssueCountCd', '発行回数', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '13', 'AdCd', 'はがき広告', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '14', 'EnclosedAdCd', '封書広告', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '15', 'PostalCode', '顧客郵便番号', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '16', 'UnitingAddress', '顧客住所', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '17', 'NameKj', '顧客氏名', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '18', 'OrderId', '注文ＩＤ', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '19', 'ReceiptOrderDate', '注文日', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '20', 'SiteNameKj', '購入店名', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '21', 'Url', '購入店URL', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '22', 'SiteContactPhoneNumber', '購入店電話番号', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '23', 'ClaimAmount', '請求金額', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '24', 'CarriageFee', '送料', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '25', 'ChargeFee', '決済手数料', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '26', 'Clm_Count', '請求回数', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '27', 'LimitDate', '支払期限日', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '28', 'Cv_BarcodeData2', 'バーコードデータ', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '29', 'ItemNameKj_1', '商品名１', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '30', 'ItemNum_1', '数量１', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '31', 'UnitPrice_1', '単価１', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '32', 'ItemNameKj_2', '商品名２', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '33', 'ItemNum_2', '数量２', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '34', 'UnitPrice_2', '単価２', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '35', 'ItemNameKj_3', '商品名３', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '36', 'ItemNum_3', '数量３', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '37', 'UnitPrice_3', '単価３', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '38', 'ItemNameKj_4', '商品名４', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '39', 'ItemNum_4', '数量４', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '40', 'UnitPrice_4', '単価４', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '41', 'ItemNameKj_5', '商品名５', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '42', 'ItemNum_5', '数量５', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '43', 'UnitPrice_5', '単価５', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '44', 'ItemNameKj_6', '商品名６', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '45', 'ItemNum_6', '数量６', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '46', 'UnitPrice_6', '単価６', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '47', 'ItemNameKj_7', '商品名７', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '48', 'ItemNum_7', '数量７', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '49', 'UnitPrice_7', '単価７', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '50', 'ItemNameKj_8', '商品名８', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '51', 'ItemNum_8', '数量８', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '52', 'UnitPrice_8', '単価８', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '53', 'ItemNameKj_9', '商品名９', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '54', 'ItemNum_9', '数量９', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '55', 'UnitPrice_9', '単価９', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '56', 'ItemNameKj_10', '商品名１０', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '57', 'ItemNum_10', '数量１０', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '58', 'UnitPrice_10', '単価１０', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '59', 'ItemNameKj_11', '商品名１１', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '60', 'ItemNum_11', '数量１１', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '61', 'UnitPrice_11', '単価１１', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '62', 'ItemNameKj_12', '商品名１２', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '63', 'ItemNum_12', '数量１２', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '64', 'UnitPrice_12', '単価１２', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '65', 'ItemNameKj_13', '商品名１３', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '66', 'ItemNum_13', '数量１３', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '67', 'UnitPrice_13', '単価１３', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '68', 'ItemNameKj_14', '商品名１４', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '69', 'ItemNum_14', '数量１４', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '70', 'UnitPrice_14', '単価１４', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '71', 'ItemNameKj_15', '商品名１５', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '72', 'ItemNum_15', '数量１５', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '73', 'UnitPrice_15', '単価１５', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '74', 'ItemNameKj_16', '商品名１６', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '75', 'ItemNum_16', '数量１６', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '76', 'UnitPrice_16', '単価１６', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '77', 'ItemNameKj_17', '商品名１７', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '78', 'ItemNum_17', '数量１７', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '79', 'UnitPrice_17', '単価１７', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '80', 'ItemNameKj_18', '商品名１８', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '81', 'ItemNum_18', '数量１８', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '82', 'UnitPrice_18', '単価１８', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '83', 'ItemNameKj_19', '商品名１９', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '84', 'ItemNum_19', '数量１９', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '85', 'UnitPrice_19', '単価１９', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '86', 'ClaimFee', '再請求発行手数料', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '87', 'DamageInterestAmount', '遅延損害金', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '88', 'ReceiptAmountTotal', '入金済額', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '89', 'TotalItemPrice', '小計', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '90', 'Ent_OrderId', '任意注文番号', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '91', 'TaxAmount', '消費税額', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '92', 'Cv_ReceiptAgentName', 'CVS収納代行会社名', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '93', 'Cv_SubscriberName', 'CVS収納代行加入者名', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '94', 'Cv_BarcodeData', 'バーコードデータ(CD付)', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '95', 'Cv_BarcodeString1', 'バーコード文字1', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '96', 'Cv_BarcodeString2', 'バーコード文字2', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '97', 'Bk_BankCode', '銀行口座 - 銀行コード', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '98', 'Bk_BranchCode', '銀行口座 - 支店コード', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '99', 'Bk_BankName', '銀行口座 - 銀行名', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '100', 'Bk_BranchName', '銀行口座 - 支店名', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '101', 'Bk_DepositClass', '銀行口座 - 口座種別', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '102', 'Bk_AccountNumber', '銀行口座 - 口座番号', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '103', 'Bk_AccountHolder', '銀行口座 - 口座名義', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '104', 'Bk_AccountHolderKn', '銀行口座 - 口座名義カナ', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '105', 'AccountNumber', '固定ゆうちょ銀行　記号・番号', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '106', 'SubscriberName', '固定ゆうちょ銀行　口座名義', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '107', 'Yu_SubscriberName', 'ゆうちょ口座 - 加入者名', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '108', 'Yu_AccountNumber', 'ゆうちょ口座 - 口座番号', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '109', 'Yu_ChargeClass', 'ゆうちょ口座 - 払込負担区分', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '110', 'Yu_MtOcrCode1', 'ゆうちょ口座 - MT用OCRコード1', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '111', 'Yu_MtOcrCode2', 'ゆうちょ口座 - MT用OCRコード2', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '112', 'MyPageUrl', 'マイページURL', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '113', 'MypageToken', 'マイページログインパスワード', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '114', 'ItemsCount', '商品合計数', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '115', 'TaxClass', '消費税区分', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '116', 'SubUseAmount_2', '10％対象　小計', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '117', 'SubTaxAmount_2', '10%消費税額', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '118', 'SubUseAmount_1', '8％対象　小計', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '119', 'SubTaxAmount_1', '8%消費税額', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '120', 'ClaimDate', '請求書発行日', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '121', 'CorporateName', '法人名', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '122', 'DivisionName', '部署名', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '123', 'CpNameKj', '担当者名', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '124', 'CreditSettlementDecisionDate', 'クレジット決済利用期限日', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '125', 'ClaimEntCustIdDisplayName', '加盟店顧客番号項目名', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '126', 'EntCustId', '加盟店顧客番号', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '127', 'Ent_Note', '強制解約期日', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '128', 'Free1', '自由項目１', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '129', 'Free2', '自由項目２', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '130', 'Free3', '自由項目３', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '131', 'Free4', '自由項目４', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '132', 'Free5', '自由項目５', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '133', 'Free6', '自由項目６', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '134', 'Free7', '自由項目７', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '135', 'Free8', '自由項目８', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '136', 'Free9', '自由項目９', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '137', 'Free10', '自由項目１０', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '138', 'Free11', '自由項目１１', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '139', 'Free12', '自由項目１２', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '140', 'Free13', '自由項目１３', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '141', 'Free14', '自由項目１４', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '142', 'Free15', '自由項目１５', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '143', 'Free16', '自由項目１６', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '144', 'Free17', '自由項目１７', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '145', 'Free18', '自由項目１８', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '146', 'Free19', '自由項目１９', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '147', 'Free20', '自由項目２０', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '148', 'PublicPhoneNumber', '発行元電話番号', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '149', 'InvoicePhoneNumber', '発行元登録番号', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '150', 'EntNameKj', '発行元名（請求代行）', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '151', 'EntPostalCode', '発行元郵便番号（請求代行）', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '152', 'EntAddress1', '発行元住所１（請求代行）', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '153', 'EntAddress2', '発行元住所２（請求代行）', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '154', 'EntContactPhoneNumber', '発行元電話番号（請求代行）', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '155', 'InvoiceContactPhoneNumber', '発行元登録番号（請求代行）', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '156', 'CustomerNumber', 'ペイジー - お客様番号', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '157', 'ConfirmNumber', 'ペイジー - 確認番号', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '158', 'PayeasyNote', 'ペイジー - 収納機関番号', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '159', 'ItemNameKj_20', '商品名２０', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '160', 'ItemNum_20', '数量２０', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '161', 'UnitPrice_20', '単価２０', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '162', 'ItemNameKj_21', '商品名２１', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '163', 'ItemNum_21', '数量２１', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '164', 'UnitPrice_21', '単価２１', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '165', 'ItemNameKj_22', '商品名２２', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '166', 'ItemNum_22', '数量２２', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '167', 'UnitPrice_22', '単価２２', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '168', 'ItemNameKj_23', '商品名２３', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '169', 'ItemNum_23', '数量２３', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '170', 'UnitPrice_23', '単価２３', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '171', 'ItemNameKj_24', '商品名２４', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '172', 'ItemNum_24', '数量２４', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '173', 'UnitPrice_24', '単価２４', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '174', 'ItemNameKj_25', '商品名２５', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '175', 'ItemNum_25', '数量２５', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '176', 'UnitPrice_25', '単価２５', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '177', 'ItemNameKj_26', '商品名２６', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '178', 'ItemNum_26', '数量２６', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '179', 'UnitPrice_26', '単価２６', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '180', 'ItemNameKj_27', '商品名２７', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '181', 'ItemNum_27', '数量２７', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '182', 'UnitPrice_27', '単価２７', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '183', 'ItemNameKj_28', '商品名２８', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '184', 'ItemNum_28', '数量２８', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '185', 'UnitPrice_28', '単価２８', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '186', 'ItemNameKj_29', '商品名２９', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '187', 'ItemNum_29', '数量２９', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '188', 'UnitPrice_29', '単価２９', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '189', 'ItemNameKj_30', '商品名３０', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '190', 'ItemNum_30', '数量３０', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');
INSERT INTO M_TemplateField VALUES ('106', '191', 'UnitPrice_30', '単価３０', 'DATETIME', '0', null, '0', null, null, null, NOW(), '1', NOW(), 1, '1');


INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_緑ハガキ_初回','001','0010010','0001001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_緑ハガキ_再１','001','0010010','0001001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_緑ハガキ_再３','001','0010010','0001001','03',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_緑ハガキ_再４','001','0010010','0001001','04',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_緑ハガキ_再５','001','0010010','0001001','05',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（三菱）_緑ハガキ_初回','001','0010010','0004001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（SMBC）_緑ハガキ_初回','001','0010011','0202001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（SMBC）_緑ハガキ_再１','001','0010011','0202001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（eストアー）_緑ハガキ_再３','001','0010010','0101001','03',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（eストアー）_緑ハガキ_再４','001','0010010','0101001','04',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（eストアー）_緑ハガキ_再５','001','0010010','0101001','05',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（セイノー）_緑ハガキ_再３','001','0010010','0301001','03',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（セイノー）_緑ハガキ_再４','001','0010010','0301001','04',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（セイノー）_緑ハガキ_再５','001','0010010','0301001','05',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（テモナ）_緑ハガキ_再３','001','0010010','0501001','03',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（テモナ）_緑ハガキ_再４','001','0010010','0501001','04',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（テモナ）_緑ハガキ_再５','001','0010010','0501001','05',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_緑ハガキ（強督促）_再３','001','0010020','0001001','03',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_緑ハガキ（強督促）_再４','001','0010020','0001001','04',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_緑ハガキ（強督促）_再５','001','0010020','0001001','05',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_緑ハガキ（役務文言）_初回','001','0010030','0001001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_緑ハガキ（役務文言）_再１','001','0010030','0001001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_緑ハガキ（役務文言）_再３','001','0010030','0001001','03',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_緑ハガキ（役務文言）_再４','001','0010030','0001001','04',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_緑ハガキ（役務文言）_再５','001','0010030','0001001','05',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（三菱）_緑ハガキ（役務文言）_初回','001','0010030','0004001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（SMBC）_緑ハガキ（役務文言）_初回','001','0010031','0202001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（SMBC）_緑ハガキ（役務文言）_再１','001','0010031','0202001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（eストアー）_緑ハガキ（役務文言）_再３','001','0010030','0101001','03',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（eストアー）_緑ハガキ（役務文言）_再４','001','0010030','0101001','04',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（eストアー）_緑ハガキ（役務文言）_再５','001','0010030','0101001','05',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（セイノー）_緑ハガキ（役務文言）_再３','001','0010030','0301001','03',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（セイノー）_緑ハガキ（役務文言）_再４','001','0010030','0301001','04',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（セイノー）_緑ハガキ（役務文言）_再５','001','0010030','0301001','05',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（テモナ）_緑ハガキ（役務文言）_再３','001','0010030','0501001','03',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（テモナ）_緑ハガキ（役務文言）_再４','001','0010030','0501001','04',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（テモナ）_緑ハガキ（役務文言）_再５','001','0010030','0501001','05',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_紫ハガキ_初回','001','0020010','0001001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_紫ハガキ_再１','001','0020010','0001001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（三菱）_紫ハガキ_初回','001','0020010','0004001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（eストアー））_紫ハガキ_初回','001','0020010','0101001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（eストアー））_紫ハガキ_再１','001','0020010','0101001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（セイノー））_紫ハガキ_初回','001','0020010','0301001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（セイノー））_紫ハガキ_再１','001','0020010','0301001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（テモナ））_紫ハガキ_初回','001','0020010','0501001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（テモナ））_紫ハガキ_再１','001','0020010','0501001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_紫ハガキ（役務文言）_初回','001','0020030','0001001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_紫ハガキ（役務文言）_再１','001','0020030','0001001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（三菱）_紫ハガキ（役務文言）_初回','001','0020030','0004001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（eストアー）_紫ハガキ（役務文言）_初回','001','0020030','0101001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（eストアー）_紫ハガキ（役務文言）_再１','001','0020030','0101001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（セイノー）_紫ハガキ（役務文言）_初回','001','0020030','0301001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（セイノー）_紫ハガキ（役務文言）_再１','001','0020030','0301001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（テモナ）_紫ハガキ（役務文言）_初回','001','0020030','0501001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（テモナ）_紫ハガキ（役務文言）_再１','001','0020030','0501001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_ペイジーハガキ_初回','001','0030010','0001001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_ペイジーハガキ_再１','001','0030010','0001001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_ペイジーハガキ_再３','001','0030010','0001001','03',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_ペイジーハガキ_再４','001','0030010','0001001','04',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_ペイジーハガキ_再５','001','0030010','0001001','05',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（みずほ）_ペイジーハガキ_初回','001','0030010','0603001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_ペイジーハガキ（強督促）_再３','001','0030020','0001001','03',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_ペイジーハガキ（強督促）_再４','001','0030020','0001001','04',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_ペイジーハガキ（強督促）_再５','001','0030020','0001001','05',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_ペイジーハガキ（役務文言）_初回','001','0030030','0001001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_ペイジーハガキ（役務文言）_再１','001','0030030','0001001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_ペイジーハガキ（役務文言）_再３','001','0030030','0001001','03',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_ペイジーハガキ（役務文言）_再４','001','0030030','0001001','04',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_ペイジーハガキ（役務文言）_再５','001','0030030','0001001','05',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（みずほ）_ペイジーハガキ（役務文言）_初回','001','0030030','0603001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_ペイジーハガキ（役務②（再１強制解約通知））_初回','001','0030040','0001001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_ペイジーハガキ（役務②（再１強制解約通知））_再１','001','0030040','0001001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（みずほ）_ペイジーハガキ（役務②（再１強制解約通知））_初回','001','0030040','0603001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_ペイジーハガキ（役務③（再３強制解約通知））_再１','001','0030050','0001001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_ペイジーハガキ（役務③（再３強制解約通知））_再３','001','0030050','0001001','03',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_水色ハガキ_初回','001','0040010','0001001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_水色ハガキ_再１','001','0040010','0001001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（三菱）_水色ハガキ_初回','001','0040010','0004001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（eストアー）_水色ハガキ_初回','001','0040010','0101001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（eストアー）_水色ハガキ_再１','001','0040010','0101001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（セイノー）_水色ハガキ_初回','001','0040010','0301001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（セイノー）_水色ハガキ_再１','001','0040010','0301001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（テモナ）_水色ハガキ_初回','001','0040010','0501001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（テモナ）_水色ハガキ_再１','001','0040010','0501001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_水色ハガキ（役務文言）_初回','001','0040030','0001001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_水色ハガキ（役務文言）_再１','001','0040030','0001001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（三菱）_水色ハガキ（役務文言）_初回','001','0040030','0004001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（eストアー）_水色ハガキ（役務文言）_初回','001','0040030','0101001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（eストアー）_水色ハガキ（役務文言）_再１','001','0040030','0101001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（セイノー）_水色ハガキ（役務文言）_初回','001','0040030','0301001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（セイノー）_水色ハガキ（役務文言）_再１','001','0040030','0301001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（テモナ）_水色ハガキ（役務文言）_初回','001','0040030','0501001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（テモナ）_水色ハガキ（役務文言）_再１','001','0040030','0501001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_封書督促_再６','102','0050010','0001001','06',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_封書督促_再７','102','0050010','0001001','07',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（eストアー）_封書督促_再６','102','0050010','0101001','06',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（eストアー）_封書督促_再７','102','0050010','0101001','07',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（セイノー）_封書督促_再６','102','0050010','0301001','06',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（セイノー）_封書督促_再７','102','0050010','0301001','07',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（テモナ）_封書督促_再６','102','0050010','0501001','06',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('OEM（テモナ）_封書督促_再７','102','0050010','0501001','07',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_封書NTT督促_再3','102','0060010','0001001','03',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_封書NTT督促_再4','102','0060010','0001001','04',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_封書NTT督促_再5','102','0060010','0001001','05',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（SMBC）_封書NTT（加入者負担）_初回','103','0060011','0202001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（SMBC）_封書NTT（加入者負担）_再１','103','0060011','0202001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_封書払込票なし_0円','101','0070010','0001001','00',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('みずほ_封書払込票なし_0円','101','0070010','0603001','00',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（三菱UFJファクター）三菱_封書払込票なし_0円','101','0080010','0004001','00',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_封書明細_初回','102','0090010','0001001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（SMBC）_封書明細（加入者負担）_初回','103','0090011','0202001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（SMBC）_封書明細（加入者負担）_再１','103','0090011','0202001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）請求代行_初回','001','0110010','0001003','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）届いてから決済_初回','001','0120010','0001002','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）届いてから決済_再１','001','0010010','0001002','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）届いてから決済_再３','001','0010010','0001002','03',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）届いてから決済_再４','001','0010010','0001002','04',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）届いてから決済_再５','001','0010010','0001002','05',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_役務ハガキ（明細10行）_初回','001','0130010','0001001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_役務ハガキ（明細10行）_再１','001','0130010','0001001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_役務ハガキ（明細10行）_再３','001','0130010','0001001','03',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_役務ハガキ（明細10行）_再４','001','0130010','0001001','04',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_役務ハガキ（明細10行）_再５','001','0130010','0001001','05',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（三菱）_役務ハガキ（明細10行）_初回','001','0130010','0004001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_役務ハガキ（明細10行）（強督促）_再３','001','0130020','0001001','03',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_役務ハガキ（明細10行）（強督促）_再４','001','0130020','0001001','04',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_役務ハガキ（明細10行）（強督促）_再５','001','0130020','0001001','05',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_役務ハガキ（明細10行）（再１強制解約）_初回','001','0130040','0001001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_役務ハガキ（明細10行）（再１強制解約）_再１','001','0130040','0001001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（三菱）_役務ハガキ（明細10行）（再１強制解約）_初回','001','0130040','0004001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_役務ハガキ（明細10行）（再３強制解約）_再１','001','0130050','0001001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_役務ハガキ（明細10行）（再３強制解約）_再３','001','0130050','0001001','03',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（三菱）_Looop役務ハガキ（明細10行）（再１強制解約）_初回','001','0130060','0004001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_Looop役務ハガキ（明細10行）（再１強制解約）_再１','001','0130060','0001001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（三菱）_Looop役務ハガキ（明細10行）（再３強制解約）_初回','001','0130070','0004001','01',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_Looop役務ハガキ（明細10行）（再３強制解約）_再１','001','0130070','0001001','02',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_Looop役務ハガキ（明細10行）（再３強制解約）_再３','001','0130070','0001001','03',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_Looop役務ハガキ（明細10行）（再３強制解約）_再４','001','0130070','0001001','04',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_Looop役務ハガキ（明細10行）（再３強制解約）_再５','001','0130070','0001001','05',1);
INSERT INTO M_ClaimPrintCheck(ClaimPrintCheckName,PrintFormCd,PrintTypeCd,PrintIssueCd,PrintIssueCountCd,ValidFlg) VALUES('CB（MICS）_封書明細_初回口振','102','0090010','0001001','00',1);


INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・コンビニのみ','101','100001109999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・LINE・PayB他','101','102011129999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・広告あり・コンビニのみ','101','110001109999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・広告あり・LINE・PayB他','101','112011129999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ無・コンビニのみ','101','000001109999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ無・LINE・PayB他','101','002011129999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ無・広告あり','101','010001109999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ無・広告あり・LINE・PayB他','101','012011129999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ無・コンビニのみ','102','000001109999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ無・LINE・PayB他','102','002011129999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ無・LINE・PayPay','102','002111129999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・LINE・クレカ','103','102011129999122222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・広告あり・LINE・クレカ','103','112011129999122222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・コンビニのみ','104','100001109999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・LINE・PayB他','104','102011129999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・広告あり・コンビニのみ','104','110001109999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・広告あり・LINE・PayB他','104','112011129999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・コンビニのみ','105','100001109999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・LINE・PayB他','105','102011129999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・広告あり・コンビニのみ','105','110001109999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・広告あり・LINE・PayB他','105','112011129999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ無・コンビニのみ','105','000001109999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ無・LINE・PayB他','105','002011129999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ無・広告あり','105','010001109999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・コンビニのみ','206','100001109999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・LINE・PayB他','206','102011129999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・LINE・PayB他','207','102011129999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・コンビニのみ','208','200001109999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・LINE・PayB他','208','202011129999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・コンビニのみ','209','100001109999222222222222222222',0);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・LINE・PayB他','209','102011129999222222222222222222',0);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('フラグ無し','210','222222222222222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('','','',0);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('フラグ無し・広告あり','210','010001109999222222222222222222',1);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ無・広告あり・LINE・PayB他','105','012011129999222222222222222222',0);
INSERT INTO M_PaymentCheck(ImageName,PrintPatternCd,SpPaymentCd,ValidFlg) VALUES('マイページ有・コンビニのみ','211','100001109999222222222222222222',1);

-- 株式会社ＮＴＴファシリティーズ
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (16889, 19483, '01', '103', '206', '0060011', '00000', '0202001', '100011109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (16889, 19483, '02', '103', '206', '0060011', '00000', '0202001', '100011109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (16889, 19483, '03', '102', '207', '0060010', '00000', '0001001', '100011109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (16889, 19483, '04', '102', '207', '0060010', '00000', '0001001', '100011109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (16889, 19483, '05', '102', '207', '0060010', '00000', '0001001', '100011109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (16889, 19483, '06', '102', '208', '0050010', '00000', '0001001', '100011109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (16889, 19483, '07', '102', '208', '0050010', '00000', '0001001', '100011109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);

-- 【のんびり後払い】株式会社マキシム
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (19205, 22346, '01', '001', '101', '0010010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (19205, 22346, '02', '001', '101', '0010010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (19205, 22346, '03', '001', '101', '0010020', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (19205, 22346, '04', '001', '101', '0010020', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (19205, 22346, '05', '001', '101', '0010020', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (19205, 22346, '06', '102', '208', '0050010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (19205, 22346, '07', '102', '208', '0050010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);


-- 【口座振替】株式会社Looop
-- (【口座振替】株式会社Looop １)
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34288, '00', '101', '210', '0080010', '00003', '0004001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34288, '01', '001', '105', '0130070', '00000', '0004001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34288, '02', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34288, '03', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34288, '04', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34288, '05', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34288, '06', '102', '208', '0050010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34288, '07', '102', '208', '0050010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
-- (【口座振替】株式会社Looop ２)
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34289, '00', '101', '210', '0080010', '00003', '0004001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34289, '01', '001', '105', '0130070', '00000', '0004001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34289, '02', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34289, '03', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34289, '04', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34289, '05', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34289, '06', '102', '208', '0050010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34289, '07', '102', '208', '0050010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
-- (【口座振替】株式会社Looop ３)
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34290, '00', '101', '210', '0080010', '00003', '0004001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34290, '01', '001', '105', '0130070', '00000', '0004001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34290, '02', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34290, '03', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34290, '04', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34290, '05', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34290, '06', '102', '208', '0050010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34290, '07', '102', '208', '0050010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
-- (【クレカ落ち】株式会社Looop　１)
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34291, '00', '101', '210', '0080010', '00003', '0004001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34291, '01', '001', '105', '0130070', '00000', '0004001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34291, '02', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34291, '03', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34291, '04', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34291, '05', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34291, '06', '102', '208', '0050010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34291, '07', '102', '208', '0050010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
-- (【クレカ落ち】株式会社Looop　２)
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34292, '00', '101', '210', '0080010', '00003', '0004001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34292, '01', '001', '105', '0130070', '00000', '0004001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34292, '02', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34292, '03', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34292, '04', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34292, '05', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34292, '06', '102', '208', '0050010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34292, '07', '102', '208', '0050010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
-- ( 	【クレカ落ち】株式会社Looop　３)
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34293, '00', '101', '210', '0080010', '00003', '0004001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34293, '01', '001', '105', '0130070', '00000', '0004001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34293, '02', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34293, '03', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34293, '04', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34293, '05', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34293, '06', '102', '208', '0050010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34293, '07', '102', '208', '0050010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
-- (【クレカ落ち】株式会社Looop　４)
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34294, '00', '101', '210', '0080010', '00003', '0004001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34294, '01', '001', '105', '0130070', '00000', '0004001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34294, '02', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34294, '03', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34294, '04', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34294, '05', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34294, '06', '102', '208', '0050010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34294, '07', '102', '208', '0050010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
-- (【クレカ落ち】株式会社Looop　５)
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34295, '00', '101', '210', '0080010', '00003', '0004001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34295, '01', '001', '105', '0130070', '00000', '0004001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34295, '02', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34295, '03', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34295, '04', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34295, '05', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34295, '06', '102', '208', '0050010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34295, '07', '102', '208', '0050010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
-- ( 	【強制解約出さない設定】株式会社Looop )
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34677, '00', '101', '210', '0080010', '00003', '0004001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34677, '01', '001', '105', '0130070', '00000', '0004001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34677, '02', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34677, '03', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34677, '04', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34677, '05', '001', '105', '0130070', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34677, '06', '102', '208', '0050010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28840, 34677, '07', '102', '208', '0050010', '00000', '0001001', '100001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);

-- 【全件保証外】株式会社Looop
-- (【無保証】株式会社Looop)
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28986, 34466, '01', '001', '105', '0130060', '00000', '0004001', '000001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28986, 34466, '02', '001', '105', '0130060', '00000', '0001001', '000001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28986, 34466, '03', '001', '101', '0010010', '00000', '0001001', '000001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28986, 34466, '04', '001', '101', '0010010', '00000', '0001001', '000001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28986, 34466, '05', '001', '101', '0010010', '00000', '0001001', '000001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28986, 34466, '06', '102', '208', '0050010', '00000', '0001001', '000001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28986, 34466, '07', '102', '208', '0050010', '00000', '0001001', '000001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
-- ( 	【強制解約出さない設定】【無保証】株式会社Looop)
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28986, 34678, '01', '001', '105', '0130060', '00000', '0004001', '000001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28986, 34678, '02', '001', '105', '0130060', '00000', '0001001', '000001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28986, 34678, '03', '001', '101', '0010010', '00000', '0001001', '000001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28986, 34678, '04', '001', '101', '0010010', '00000', '0001001', '000001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28986, 34678, '05', '001', '101', '0010010', '00000', '0001001', '000001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28986, 34678, '06', '102', '208', '0050010', '00000', '0001001', '000001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);
INSERT INTO `T_ClaimPrintPattern` (`EnterpriseId`, `SiteId`, `PrintIssueCountCd`, `PrintFormCd`, `PrintPatternCd`, `PrintTypeCd`, `EnclosedSpecCd`, `PrintIssueCd`, `SpPaymentCd`, `AdCd`, `EnclosedAdCd`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (28986, 34678, '07', '102', '208', '0050010', '00000', '0001001', '000001109999000000000999999999', '00000', '00000', NOW(), 1, NOW(), 1, 1);







-- =================================================================================================
-- リリース当日
-- =================================================================================================

ALTER TABLE T_Enterprise 
ADD COLUMN `ClaimEntCustIdDisplayName` VARCHAR(20)  NULL AFTER `MhfCreditTransferDisplayName`,
ADD COLUMN `ClaimOrderDateFormat` TINYINT NOT NULL DEFAULT 0 AFTER `ClaimEntCustIdDisplayName`,
ADD COLUMN `ClaimPamphletPut` TINYINT NOT NULL DEFAULT 0 AFTER `ClaimOrderDateFormat`;

ALTER TABLE T_Site 
ADD COLUMN `ClaimOriginalFormat` TINYINT NOT NULL DEFAULT 0 AFTER `MufjBarcodeSubscriberCode`,
ADD COLUMN `ClaimMypagePrint` TINYINT NOT NULL DEFAULT 0 AFTER `ClaimOriginalFormat`;

ALTER TABLE M_Code 
ADD COLUMN `Class4` VARCHAR(30)  NULL AFTER `Class3`,
ADD COLUMN `Class5` VARCHAR(30)  NULL AFTER `Class4`,
ADD COLUMN `Class6` VARCHAR(30)  NULL AFTER `Class5`,
ADD COLUMN `Class7` VARCHAR(30)  NULL AFTER `Class6`;

ALTER TABLE M_CodeManagement 
ADD COLUMN `Class4ValidFlg` TINYINT NOT NULL DEFAULT 0 AFTER `Class3Name`,
ADD COLUMN `Class4Name` VARCHAR(50) NULL AFTER `Class4ValidFlg`,
ADD COLUMN `Class5ValidFlg` TINYINT NOT NULL DEFAULT 0 AFTER `Class4Name`,
ADD COLUMN `Class5Name` VARCHAR(50) NULL AFTER `Class5ValidFlg`,
ADD COLUMN `Class6ValidFlg` TINYINT NOT NULL DEFAULT 0 AFTER `Class5Name`,
ADD COLUMN `Class6Name` VARCHAR(50) NULL AFTER `Class6ValidFlg`,
ADD COLUMN `Class7ValidFlg` TINYINT NOT NULL DEFAULT 0 AFTER `Class6Name`,
ADD COLUMN `Class7Name` VARCHAR(50) NULL AFTER `Class7ValidFlg`;

UPDATE M_CodeManagement set
Class1ValidFlg=1,Class1Name='初回（発行回数：01）',
Class2ValidFlg=1,Class2Name='再１（発行回数：02）',
Class3ValidFlg=1,Class3Name='再３（発行回数：03）',
Class4ValidFlg=1,Class4Name='再４（発行回数：04）',
Class5ValidFlg=1,Class5Name='再５（発行回数：05）',
Class6ValidFlg=1,Class6Name='再６（発行回数：06）',
Class7ValidFlg=1,Class7Name='再７（発行回数：07）',
UpdateDate=NOW(),UpdateId='0'
where CodeId=108;

UPDATE M_Code set KeyContent='03-4326-3600',Class1='03-4326-3600',Class2='03-4326-3600',Class3='03-4326-3600',Class4='03-4326-3600',Class5='03-4326-3600',Class6='03-4326-3600',Class7='03-4326-3600',UpdateDate=NOW(),UpdateId='0' where CodeId=108 and KeyCode=0;
UPDATE M_Code set KeyContent='03-4326-3574',Class1='03-4326-3575',Class2='03-4326-3575',Class3='03-4326-3575',Class4='03-4326-3575',Class5='03-4326-3575',Class6='03-4326-3575',Class7='03-4326-3575',UpdateDate=NOW(),UpdateId='0' where CodeId=108 and KeyCode=1;
UPDATE M_Code set KeyContent='03-4326-3600',Class1='03-4326-3600',Class2='03-4326-3600',Class3='03-4326-3600',Class4='03-4326-3600',Class5='03-4326-3600',Class6='03-4326-3600',Class7='03-4326-3600',UpdateDate=NOW(),UpdateId='0' where CodeId=108 and KeyCode=2;
UPDATE M_Code set KeyContent='03-4326-3610',Class1='03-4326-3610',Class2='03-4326-3610',Class3='03-4326-3610',Class4='03-4326-3610',Class5='03-4326-3610',Class6='03-4326-3610',Class7='03-4326-3610',UpdateDate=NOW(),UpdateId='0' where CodeId=108 and KeyCode=3;
UPDATE M_Code set KeyContent='03-4326-3600',Class1='03-4326-3600',Class2='03-4326-3600',Class3='03-4326-3600',Class4='03-4326-3600',Class5='03-4326-3600',Class6='03-4326-3600',Class7='03-4326-3600',UpdateDate=NOW(),UpdateId='0' where CodeId=108 and KeyCode=4;
UPDATE M_Code set KeyContent='03-4326-3539',Class1='03-4326-3623',Class2='03-4326-3623',Class3='03-4326-3623',Class4='03-4326-3623',Class5='03-4326-3623',Class6='03-4326-3623',Class7='03-4326-3623',UpdateDate=NOW(),UpdateId='0' where CodeId=108 and KeyCode=5;
UPDATE M_Code set KeyContent='03-4326-3600',Class1='03-4326-3600',Class2='03-4326-3600',Class3='03-4326-3600',Class4='03-4326-3600',Class5='03-4326-3600',Class6='03-4326-3600',Class7='03-4326-3600',UpdateDate=NOW(),UpdateId='0' where CodeId=108 and KeyCode=6;


UPDATE M_CodeManagement set
Class5ValidFlg=1,Class5Name='サイトマスタの請求書マイページ印字の初期表示（0：しない　1：する）',
Class6ValidFlg=1,Class6Name='印刷パターンマスタ登録時の版下の設定（001：通常・緑ハガキ　002：通常・紫ハガキ）',
Class7ValidFlg=1,Class7Name='サイトマスタの初回請求用紙モードの入力制御（0：非活性　1：活性）',
UpdateDate=NOW(),UpdateId='0'
where CodeId=160;
UPDATE M_Code set Class5='1',Class6='001',Class7='0',UpdateDate=NOW(),UpdateId='0' where CodeId=160 and KeyCode=0;
UPDATE M_Code set Class5='0',Class6='002',Class7='0',UpdateDate=NOW(),UpdateId='0' where CodeId=160 and KeyCode=1;
UPDATE M_Code set Class5='1',Class6='001',Class7='1',UpdateDate=NOW(),UpdateId='0' where CodeId=160 and KeyCode=2;
UPDATE M_Code set Class5='1',Class6='002',Class7='0',UpdateDate=NOW(),UpdateId='0' where CodeId=160 and KeyCode=3;
UPDATE M_Code set Class5='0',Class6='002',Class7='0',UpdateDate=NOW(),UpdateId='0' where CodeId=160 and KeyCode=4;
UPDATE M_Code set Class5='0',Class6='002',Class7='0',UpdateDate=NOW(),UpdateId='0' where CodeId=160 and KeyCode=5;
UPDATE M_Code set Class5='1',Class6='001',Class7='1',UpdateDate=NOW(),UpdateId='0' where CodeId=160 and KeyCode=6;
UPDATE M_Code SET Class4 = 1 WHERE CodeId = 160 AND KeyCode = 0;
UPDATE M_Code SET Class4 = 0 WHERE CodeId = 160 AND KeyCode = 1;
UPDATE M_Code SET Class4 = 1 WHERE CodeId = 160 AND KeyCode = 2;
UPDATE M_Code SET Class4 = 0 WHERE CodeId = 160 AND KeyCode = 3;
UPDATE M_Code SET Class4 = 0 WHERE CodeId = 160 AND KeyCode = 4;
UPDATE M_Code SET Class4 = 0 WHERE CodeId = 160 AND KeyCode = 5;
UPDATE M_Code SET Class4 = 1 WHERE CodeId = 160 AND KeyCode = 6;

INSERT INTO M_CodeManagement(CodeId,CodeName,KeyPhysicalName,KeyLogicName,Class1ValidFlg,Class1Name,Class2ValidFlg,Class2Name,Class3ValidFlg,Class3Name,Class4ValidFlg,Class4Name,Class5ValidFlg,Class5Name,Class6ValidFlg,Class6Name,Class7ValidFlg,Class7Name,RegistDate,RegistId,UpdateDate,UpdateId,ValidFlg)
VALUES ('214', '印刷パターン更新除外特定加盟店', null, '加盟店', '0', null, '0', null, '0', null, '0', null, '0', null, '0', null, '0', null, NOW(), '0', NOW(), '0', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Class5`, `Class6`, `Class7`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES
('214', '12345', '○○加盟店', '', '', '', '', '', '', '', '', '0', NOW(), '0',NOW(), '0', '1')
;

INSERT INTO M_CodeManagement(CodeId,CodeName,KeyPhysicalName,KeyLogicName,Class1ValidFlg,Class1Name,Class2ValidFlg,Class2Name,Class3ValidFlg,Class3Name,Class4ValidFlg,Class4Name,Class5ValidFlg,Class5Name,Class6ValidFlg,Class6Name,Class7ValidFlg,Class7Name,RegistDate,RegistId,UpdateDate,UpdateId,ValidFlg)
VALUES ('215', '請求書CSVの発行元（請求代行）', null, 'OEMID', '1', '初回（発行回数：01）', '1', '再１（発行回数：02）', '1', '再３（発行回数：03）', '1', '再４（発行回数：04）', '1', '再５（発行回数：05）', '1', '再６（発行回数：06）', '1', '再７（発行回数：07）', NOW(), '0', NOW(), '0', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Class5`, `Class6`, `Class7`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES
('215', '0', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '', '0', NOW(), '0',NOW(), '0', '1'),
('215', '1', '株式会社Ｅストアー', '株式会社Ｅストアー', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '', '0', NOW(), '0',NOW(), '0', '1'),
('215', '2', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '', '0', NOW(), '0',NOW(), '0', '1'),
('215', '3', 'セイノーフィナンシャル株式会社', 'セイノーフィナンシャル株式会社', 'セイノーフィナンシャル株式会社', 'セイノーフィナンシャル株式会社', 'セイノーフィナンシャル株式会社', 'セイノーフィナンシャル株式会社', 'セイノーフィナンシャル株式会社', 'セイノーフィナンシャル株式会社', '', '0', NOW(), '0',NOW(), '0', '1'),
('215', '5', 'テモナ株式会社', 'テモナ株式会社', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '', '0', NOW(), '0',NOW(), '0', '1'),
('215', '6', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '', '0', NOW(), '0',NOW(), '0', '1')
;
INSERT INTO M_CodeManagement(CodeId,CodeName,KeyPhysicalName,KeyLogicName,Class1ValidFlg,Class1Name,Class2ValidFlg,Class2Name,Class3ValidFlg,Class3Name,Class4ValidFlg,Class4Name,Class5ValidFlg,Class5Name,Class6ValidFlg,Class6Name,Class7ValidFlg,Class7Name,RegistDate,RegistId,UpdateDate,UpdateId,ValidFlg)
VALUES ('216', '印刷パターンマスタ発行元2桁', null, 'OEMID', '1', '請求回数01', '1', '請求回数02', '1', '請求回数03', '1', '請求回数04', '1', '請求回数05', '1', '請求回数06', '1', '請求回数07', NOW(), '0', NOW(), '0', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Class5`, `Class6`, `Class7`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES
('216', '0', '00', '00', '00', '00', '00', '00', '00', '00', 'キャッチボール', '1', NOW(), '0',NOW(), '0', '1'),
('216', '1', '01', '01', '01', '01', '01', '01', '01', '01', 'Eストアー', '1', NOW(), '0',NOW(), '0', '1'),
('216', '2', '02', '02', '02', '00', '00', '00', '00', '00', 'SMBCファイナンスサービス', '1', NOW(), '0',NOW(), '0', '1'),
('216', '3', '03', '03', '00', '00', '00', '00', '00', '00', 'セイノーフィナンシャル', '1', NOW(), '0',NOW(), '0', '1'),
('216', '5', '05', '05', '05', '05', '05', '05', '05', '05', 'テモナ', '1', NOW(), '0',NOW(), '0', '1'),
('216', '6', '06', '06', '00', '00', '00', '00', '00', '00', 'みずほファクター', '1', NOW(), '0',NOW(), '0', '1')
;
INSERT INTO M_CodeManagement(CodeId,CodeName,KeyPhysicalName,KeyLogicName,Class1ValidFlg,Class1Name,Class2ValidFlg,Class2Name,Class3ValidFlg,Class3Name,Class4ValidFlg,Class4Name,Class5ValidFlg,Class5Name,Class6ValidFlg,Class6Name,Class7ValidFlg,Class7Name,RegistDate,RegistId,UpdateDate,UpdateId,ValidFlg)
VALUES ('217', '請求書CSVの固定ゆうちょ銀行　記号・番号', null, 'OEMID', '1', '請求回数01', '1', '請求回数02', '1', '請求回数03', '1', '請求回数04', '1', '請求回数05', '1', '請求回数06', '1', '請求回数07', NOW(), '0', NOW(), '0', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Class5`, `Class6`, `Class7`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES
('217', '0', '001207670031', '001207670031', '001207670031', '001207670031', '001207670031', '001207670031', '001207670031', '001207670031', 'キャッチボール', '1', NOW(), '0',NOW(), '0', '1'),
('217', '1', '001405665145', '001405665145', '001207670031', '001207670031', '001207670031', '001207670031', '001207670031', '001207670031', 'Eストアー', '1', NOW(), '0',NOW(), '0', '1'),
('217', '2', '001207670031', '001207670031', '001207670031', '001207670031', '001207670031', '001207670031', '001207670031', '001207670031', 'SMBCファイナンスサービス', '1', NOW(), '0',NOW(), '0', '1'),
('217', '3', '001007292043', '001007292043', '001007292043', '001007292043', '001007292043', '001007292043', '001007292043', '001007292043', 'セイノーフィナンシャル', '1', NOW(), '0',NOW(), '0', '1'),
('217', '5', '001408697448', '001408697448', '001207670031', '001207670031', '001207670031', '001207670031', '001207670031', '001207670031', 'テモナ', '1', NOW(), '0',NOW(), '0', '1'),
('217', '6', '001207670031', '001207670031', '001207670031', '001207670031', '001207670031', '001207670031', '001207670031', '001207670031', 'みずほファクター', '1', NOW(), '0',NOW(), '0', '1')
;
INSERT INTO M_CodeManagement(CodeId,CodeName,KeyPhysicalName,KeyLogicName,Class1ValidFlg,Class1Name,Class2ValidFlg,Class2Name,Class3ValidFlg,Class3Name,Class4ValidFlg,Class4Name,Class5ValidFlg,Class5Name,Class6ValidFlg,Class6Name,Class7ValidFlg,Class7Name,RegistDate,RegistId,UpdateDate,UpdateId,ValidFlg)
VALUES ('218', '請求書CSVの固定ゆうちょ銀行　口座名義', null, 'OEMID', '1', '請求回数01', '1', '請求回数02', '1', '請求回数03', '1', '請求回数04', '1', '請求回数05', '1', '請求回数06', '1', '請求回数07', NOW(), '0', NOW(), '0', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Class5`, `Class6`, `Class7`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES
('218', '0', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', 'キャッチボール', '1', NOW(), '0',NOW(), '0', '1'),
('218', '1', '株式会社キャッチボール Eストア－専用', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', 'Eストアー', '1', NOW(), '0',NOW(), '0', '1'),
('218', '2', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', 'SMBCファイナンスサービス', '1', NOW(), '0',NOW(), '0', '1'),
('218', '3', '株式会社キャッチボール セイノーFC係', '株式会社キャッチボール セイノーFC係', '株式会社キャッチボール セイノーFC係', '株式会社キャッチボール セイノーFC係', '株式会社キャッチボール セイノーFC係', '株式会社キャッチボール セイノーFC係', '株式会社キャッチボール セイノーFC係', '株式会社キャッチボール セイノーFC係', 'セイノーフィナンシャル', '1', NOW(), '0',NOW(), '0', '1'),
('218', '5', 'カ）キャッチボール テモナ専用口座', '株式会社キャッチボール セイノーFC係', '株式会社キャッチボール セイノーFC係', '株式会社キャッチボール セイノーFC係', '株式会社キャッチボール セイノーFC係', '株式会社キャッチボール セイノーFC係', '株式会社キャッチボール セイノーFC係', '株式会社キャッチボール セイノーFC係', 'テモナ', '1', NOW(), '0',NOW(), '0', '1'),
('218', '6', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', '株式会社キャッチボール', 'みずほファクター', '1', NOW(), '0',NOW(), '0', '1')
;

-- 105 --
UPDATE M_Code SET Class1 = '?spapp',Class2 = '?spapp2' WHERE CodeId = 105 AND KeyCode = 0;
UPDATE M_Code SET Class1 = '?spapp',Class2 = '?spapp2' WHERE CodeId = 105 AND KeyCode = 1;
UPDATE M_Code SET Class1 = '?spapp',Class2 = '?spapp2' WHERE CodeId = 105 AND KeyCode = 2;
UPDATE M_Code SET Class1 = '?spapp',Class2 = '?spapp2' WHERE CodeId = 105 AND KeyCode = 3;
UPDATE M_Code SET Class1 = '?spapp',Class2 = '?spapp2' WHERE CodeId = 105 AND KeyCode = 4;
UPDATE M_Code SET Class1 = '?spapp',Class2 = '?spapp2' WHERE CodeId = 105 AND KeyCode = 5;
UPDATE M_Code SET Class1 = '?spapp',Class2 = '?spapp2' WHERE CodeId = 105 AND KeyCode = 6;

-- 208 --
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('208', '2', 'spapp2', NULL, NULL, NULL, NULL, '<a href=\"https://www.ato-barai.com/for_user/sp_notes.html\" target=\"_blank\"><img src=\"/images/smartphone_payment_app.jpg\"></a>', 0, NOW(), 34734, NOW(), 34734, 1);


/*
請求書の加盟店顧客番号の印字名(ClaimEntCustIdDisplayName)
　　Looop契約番号
　　　<対象>
　　　　事業者ID：28840　【口座振替】株式会社Looop
　　　　事業者ID：28986　【全件保証外】株式会社Looop
*/

/*
請求書の注文日の印字形式チェックON(ClaimOrderDateFormat)
　　　<対象>　
　　　　事業者ID：16889　株式会社ＮＴＴファシリティーズ
　　　　事業者ID：28840　【口座振替】株式会社Looop
　　　　事業者ID：28986　【全件保証外】株式会社Looop
*/

update T_Enterprise set ClaimEntCustIdDisplayName='Looop契約番号' where EnterpriseId=28840;
update T_Enterprise set ClaimEntCustIdDisplayName='Looop契約番号' where EnterpriseId=28986;

update T_Enterprise set ClaimOrderDateFormat=1 where EnterpriseId=16889;
update T_Enterprise set ClaimOrderDateFormat=1 where EnterpriseId=28840;
update T_Enterprise set ClaimOrderDateFormat=1 where EnterpriseId=28986;


/*
オリジナル帳票利用する(ClaimOriginalFormat)
　　　<対象>　
　　　　事業者ID：16889　株式会社ＮＴＴファシリティーズ
　　　　　サイトID：16889
　　　　　
　　　　事業者ID：28840　【口座振替】株式会社Looop
　　　　　サイトID：34288/34289/34290/34291/34292/34293/34294/34295/34677
　　　　
　　　　事業者ID：28986　【全件保証外】株式会社Looop
　　　　　サイトID：34466/34678
　　　　　
*/
/*
請求書マイページ印字(ClaimMypagePrint)
　　M_Code(160)に設定した内容にしたがう
　　　<する>
　　　　0：キャッチボール
　　　　2：SMBCファイナンスサービス株式会社
　　　　3：セイノーフィナンシャル株式会社
　　　　6：みずほファクター株式会社
　　　<しない>
　　　　1：株式会社Ｅストアー
　　　　5：テモナ株式会社
　　　　
　　イレギュラー
　　　<しない>
　　　　事業者ID：28986　【全件保証外】株式会社Looop
　　　　　サイトID：34466/34678
　　　　
　　　　請求代行利用する設定の加盟店
　　　　
　　　　
　　　　
*/

-- update T_Site set ClaimOriginalFormat=0 where SiteId=16889;
update T_Site set ClaimOriginalFormat=1 where SiteId=19483;
update T_Site set ClaimOriginalFormat=1 where SiteId=34288;
update T_Site set ClaimOriginalFormat=1 where SiteId=34289;
update T_Site set ClaimOriginalFormat=1 where SiteId=34290;
update T_Site set ClaimOriginalFormat=1 where SiteId=34291;
update T_Site set ClaimOriginalFormat=1 where SiteId=34292;
update T_Site set ClaimOriginalFormat=1 where SiteId=34293;
update T_Site set ClaimOriginalFormat=1 where SiteId=34294;
update T_Site set ClaimOriginalFormat=1 where SiteId=34295;
update T_Site set ClaimOriginalFormat=1 where SiteId=34677;
update T_Site set ClaimOriginalFormat=1 where SiteId=34466;
update T_Site set ClaimOriginalFormat=1 where SiteId=34678;


update T_Site set ClaimMypagePrint=1 where EnterpriseId in (select EnterpriseId from T_Enterprise where IFNULL(OemId, 0) in (0, 2, 3, 6));
update T_Site set ClaimMypagePrint=0 where EnterpriseId in (select EnterpriseId from T_Enterprise where IFNULL(OemId, 0) in (1, 5));
update T_Site set ClaimMypagePrint=0 where SiteId=34466;
update T_Site set ClaimMypagePrint=0 where SiteId=34678;
update T_Site set ClaimMypagePrint=0 where EnterpriseId in (select EnterpriseId from T_Enterprise where BillingAgentFlg = 1);
