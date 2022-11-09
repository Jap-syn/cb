-- 請求管理
ALTER TABLE T_ClaimControl ADD COLUMN `CreditSettlementDecisionDate` DATE  NULL AFTER `MypageReissueReClaimFee`;

-- 注文_会計
ALTER TABLE AT_Order ADD COLUMN `ExtraPayType`  VARCHAR(30) NULL AFTER `CreditTransferRequestFlg`;
ALTER TABLE AT_Order ADD COLUMN `ExtraPayKey`   VARCHAR(50) NULL AFTER `ExtraPayType`;
ALTER TABLE AT_Order ADD COLUMN `ExtraPayNote`  TEXT        NULL AFTER `ExtraPayKey`;

-- 注文_会計【追加支払い方法区分】【追加支払い方法鍵】にインデックス付与
ALTER TABLE `AT_Order` ADD INDEX `Idx_AT_Order04` (
  `ExtraPayType` ASC
, `ExtraPayKey`  ASC
);

-- サイト
ALTER TABLE T_Site ADD COLUMN `PaymentAfterArrivalFlg` TINYINT NOT NULL DEFAULT 0 AFTER `JintecJudge9`;
ALTER TABLE T_Site ADD COLUMN `ReceiptIssueProviso` VARCHAR(255)  NULL AFTER `PaymentAfterArrivalFlg`;

-- バッチ排他制御
ALTER TABLE `T_BatchLock` DROP INDEX `ThreadNo_UNIQUE` ;
ALTER TABLE `T_BatchLock` DROP INDEX `BatchId_UNIQUE` ;
ALTER TABLE `T_BatchLock` ADD UNIQUE INDEX `Idx_T_BatchLock01` (`BatchId` ASC, `ThreadNo` ASC);

INSERT INTO T_BatchLock (BatchId, ThreadNo, BatchName, BatchLock, UpdateDate) VALUES (2, 1, 'マイページ連携バッチ', 0, NOW());

-- コード識別管理マスター
INSERT INTO M_CodeManagement(CodeId, CodeName, KeyPhysicalName, KeyLogicName, Class1ValidFlg, Class1Name, Class2ValidFlg, Class2Name, Class3ValidFlg, Class3Name, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (198, '入金方法', null, '届いてから決済用', 0, null, 0, null, 0, null, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_CodeManagement(CodeId, CodeName, KeyPhysicalName, KeyLogicName, Class1ValidFlg, Class1Name, Class2ValidFlg, Class2Name, Class3ValidFlg, Class3Name, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199, '届いてから決済利用', null, '届いてから決済利用', 0, null, 0, null, 0, null, NOW(), 1, NOW(), 1, 1);

-- コードマスター【入金方法】
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (198, 1, 'コンビニ', null, null, null, '仮', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (198, 2, '郵便局', null, null, null, '仮', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (198, 3, '銀行', null, null, null, '仮', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (198, 4, 'LINE Pay', null, null, null, '仮', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (198, 5, 'クレジット決済', null, null, null, '仮', 0, NOW(), 83, NOW(), 83,1 );

-- コードマスター【届いてから決済利用】
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199, 0, 'クレジット決済利用期間', null, null, null, '90日', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199, 1, 'クレジット決済利用不可コメント', null, null, null, 'クレジット決済利用不可コメント', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199, 2, 'クレジット決済申請中コメント', null, null, null, 'クレジット決済申請中コメント', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199, 3, 'クレジット決済完了済コメント', null, null, null, 'クレジット決済完了済コメント', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199, 4, 'クレジット決済可能コメント', null, null, null, 'クレジット決済可能コメント', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199, 10, '請求書発行(初回)クレジット決済利用可能時CSV挿入コメント', null, null, null, '請求書発行(初回)', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199,20, 'LIEN-Pay用URL', null, null, null, 'http://www.ato-barai.com/guidance/faq.html', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199,21, 'PayPay用URL', null, null, null, 'http://www.ato-barai.com/guidance/faq.html', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199,22, 'コンビニ払い用URL', null, null, null, 'http://www.ato-barai.com/guidance/faq.html', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199,23, '郵便局用URL', null, null, null, 'http://www.ato-barai.com/guidance/faq.html', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199,24, '銀行用URL', null, null, null, 'http://www.ato-barai.com/guidance/faq.html', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199,99, 'ヘルプ用URL', null, null, null, 'http://www.ato-barai.com/guidance/faq.html', 0, NOW(), 1, NOW(), 1, 1);

-- メールテンプレート【届いてから決済請求書発行メール】
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES (104,'届いてから決済請求書発行メール（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'【後払い.com】届いてから決済請求書発行案内（PC）','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','■{EnterpriseNameKj}■{OrderId}■{SiteNameKj}■{Phone}■{CustomerNameKj}■{OrderDate}■{UseAmount}■{LimitDate}■{SettlementFee}■{OrderItems}■{OneOrderItem}■{DeliveryFee}■{Tax}■{PassWord}■{OrderPageAccessUrl}',NULL,NOW(),1,NOW(),66,1);
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES (105,'届いてから決済請求書発行メール（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'【後払い.com】届いてから決済請求書発行案内（CEL）','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','■{EnterpriseNameKj}■{OrderId}■{SiteNameKj}■{Phone}■{CustomerNameKj}■{OrderDate}■{UseAmount}■{LimitDate}■{SettlementFee}■{OrderItems}■{OneOrderItem}■{DeliveryFee}■{Tax}■{PassWord}■{OrderPageAccessUrl}',NULL,NOW(),1,NOW(),66,1);
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT '106' AS Class, 'クレジット決済完了メール（PC）' AS ClassName, mt.FromTitle, mt.FromTitleMime, mt.FromAddress, mt.ToTitle, mt.ToTitleMime, mt.ToAddress, mt.Subject, mt.SubjectMime, mt.Body, mt.OemId, NOW() AS RegistDate, 1 AS RegistId, NOW() AS UpdateDate, 1 AS UpdateId, 1 AS ValidFlg FROM T_MailTemplate AS mt WHERE mt.Class = 6 AND IFNULL(mt.OemId, 0) = 0;
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT '107' AS Class, 'クレジット決済完了メール（CEL）' AS ClassName, mt.FromTitle, mt.FromTitleMime, mt.FromAddress, mt.ToTitle, mt.ToTitleMime, mt.ToAddress, mt.Subject, mt.SubjectMime, mt.Body, mt.OemId, NOW() AS RegistDate, 1 AS RegistId, NOW() AS UpdateDate, 1 AS UpdateId, 1 AS ValidFlg FROM T_MailTemplate AS mt WHERE mt.Class = 7 AND IFNULL(mt.OemId, 0) = 0;

-- メールテンプレート【届いてから決済用】
INSERT INTO M_TemplateHeader VALUES( 97 , 'CKI04047_1', 0, 0, 0, '届いてから決済用', 1, ',', '\"' ,'*' ,0,'KI04047', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 1, 'PostalCode', '顧客郵便番号', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 2, 'UnitingAddress', '顧客住所', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 3, 'NameKj', '顧客氏名', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 4, 'OrderId', '注文ＩＤ', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 5, 'ReceiptOrderDate', '注文日', 'DATE', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 6, 'SiteNameKj', '購入店名', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 7, 'Url', '購入店URL', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 8, 'ContactPhoneNumber', '購入店電話番号', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 9, 'ClaimAmount', '請求金額', 'BIGINT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 10, 'CarriageFee', '送料', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 11, 'ChargeFee', '決済手数料', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 12, 'ReIssueCount', '請求回数', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 13, 'LimitDate', '支払期限日', 'DATE', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 14, 'Cv_BarcodeData2', 'バーコードデータ', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 15, 'ItemNameKj_1', '商品名１', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 16, 'ItemNum_1', '数量１', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 17, 'UnitPrice_1', '単価１', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 18, 'ItemNameKj_2', '商品名２', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 19, 'ItemNum_2', '数量２', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 20, 'UnitPrice_2', '単価２', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 21, 'ItemNameKj_3', '商品名３', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 22, 'ItemNum_3', '数量３', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 23, 'UnitPrice_3', '単価３', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 24, 'ItemNameKj_4', '商品名４', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 25, 'ItemNum_4', '数量４', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 26, 'UnitPrice_4', '単価４', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 27, 'ItemNameKj_5', '商品名５', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 28, 'ItemNum_5', '数量５', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 29, 'UnitPrice_5', '単価５', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 30, 'ItemNameKj_6', '商品名６', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 31, 'ItemNum_6', '数量６', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 32, 'UnitPrice_6', '単価６', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 33, 'ItemNameKj_7', '商品名７', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 34, 'ItemNum_7', '数量７', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 35, 'UnitPrice_7', '単価７', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 36, 'ItemNameKj_8', '商品名８', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 37, 'ItemNum_8', '数量８', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 38, 'UnitPrice_8', '単価８', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 39, 'ItemNameKj_9', '商品名９', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 40, 'ItemNum_9', '数量９', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 41, 'UnitPrice_9', '単価９', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 42, 'ItemNameKj_10', '商品名１０', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 43, 'ItemNum_10', '数量１０', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 44, 'UnitPrice_10', '単価１０', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 45, 'ItemNameKj_11', '商品名１１', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 46, 'ItemNum_11', '数量１１', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 47, 'UnitPrice_11', '単価１１', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 48, 'ItemNameKj_12', '商品名１２', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 49, 'ItemNum_12', '数量１２', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 50, 'UnitPrice_12', '単価１２', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 51, 'ItemNameKj_13', '商品名１３', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 52, 'ItemNum_13', '数量１３', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 53, 'UnitPrice_13', '単価１３', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 54, 'ItemNameKj_14', '商品名１４', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 55, 'ItemNum_14', '数量１４', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 56, 'UnitPrice_14', '単価１４', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 57, 'ItemNameKj_15', '商品名１５', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 58, 'ItemNum_15', '数量１５', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 59, 'UnitPrice_15', '単価１５', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 60, 'ItemNameKj_16', '商品名１６', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 61, 'ItemNum_16', '数量１６', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 62, 'UnitPrice_16', '単価１６', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 63, 'ItemNameKj_17', '商品名１７', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 64, 'ItemNum_17', '数量１７', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 65, 'UnitPrice_17', '単価１７', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 66, 'ItemNameKj_18', '商品名１８', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 67, 'ItemNum_18', '数量１８', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 68, 'UnitPrice_18', '単価１８', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 69, 'ItemNameKj_19', '商品名１９', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 70, 'ItemNum_19', '数量１９', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 71, 'UnitPrice_19', '単価１９', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 72, 'ClaimFee', '再請求発行手数料', 'BIGINT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 73, 'DamageInterestAmount', '遅延損害金', 'BIGINT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 74, 'TotalItemPrice', '小計', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 75, 'Ent_OrderId', '任意注文番号', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 76, 'TaxAmount', '消費税額', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 77, 'Cv_ReceiptAgentName', 'CVS収納代行会社名', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 78, 'Cv_SubscriberName', 'CVS収納代行加入者名', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 79, 'Cv_BarcodeData', 'バーコードデータ(CD付);', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 80, 'Cv_BarcodeString1', 'バーコード文字1', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 81, 'Cv_BarcodeString2', 'バーコード文字2', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 82, 'Bk_BankCode', '銀行口座 - 銀行コード', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 83, 'Bk_BranchCode', '銀行口座 - 支店コード', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 84, 'Bk_BankName', '銀行口座 - 銀行名', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 85, 'Bk_BranchName', '銀行口座 - 支店名', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 86, 'Bk_DepositClass', '銀行口座 - 口座種別', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 87, 'Bk_AccountNumber', '銀行口座 - 口座番号', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 88, 'Bk_AccountHolder', '銀行口座 - 口座名義', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 89, 'Bk_AccountHolderKn', '銀行口座 - 口座名義カナ', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 90, 'Yu_SubscriberName', 'ゆうちょ口座 - 加入者名', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 91, 'Yu_AccountNumber', 'ゆうちょ口座 - 口座番号', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 92, 'Yu_ChargeClass', 'ゆうちょ口座 - 払込負担区分', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 93, 'Yu_MtOcrCode1', 'ゆうちょ口座 - MT用OCRコード1', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 94, 'Yu_MtOcrCode2', 'ゆうちょ口座 - MT用OCRコード2', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 95, 'MypageToken', 'マイページログインパスワード', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 96, 'ItemsCount', '商品合計数', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 97, 'TaxClass', '消費税区分', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 98, 'CorporateName', '法人名', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 99, 'DivisionName', '部署名', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 100, 'CpNameKj', '担当者名', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 101, 'Comment', 'コメント', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );

-- ---------------------------
-- マイページスキーマ向け
-- ---------------------------
DROP VIEW IF EXISTS `T_ReceiptIssueHistory`;
CREATE TABLE T_ReceiptIssueHistory
( Seq               BIGINT      NOT NULL AUTO_INCREMENT
, OrderSeq          BIGINT      DEFAULT NULL
, ReceiptIssueDate  DATETIME    DEFAULT NULL
, RegistDate        DATETIME    DEFAULT NULL
, RegistId          INT         DEFAULT NULL
, PRIMARY KEY(Seq)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------
-- 基幹スキーマ向け
-- ---------------------------
-- Viewの再構成 ※運用環境、公開環境はスキーマが異なるので注意
DROP VIEW IF EXISTS `MPV_ReceiptIssueHistory`;

CREATE VIEW `MPV_ReceiptIssueHistory` AS
  SELECT *
    FROM coraldb_mypage01.T_ReceiptIssueHistory
;
