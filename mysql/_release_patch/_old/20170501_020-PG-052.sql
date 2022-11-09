/* コードマスターに手動与信NG理由を追加 */
INSERT INTO M_CodeManagement VALUES(190 ,'手動与信NG理由' ,NULL ,'手動与信NG理由' ,1 ,'無保証変更判定' ,0,NULL,0,NULL, NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(190, 1, '通常NG', 0, NULL, NULL, '通常NG',     0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(190, 2, '期限切未払', 0, NULL, NULL, '期限切未払', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(190, 3, '長期遅延歴', 1, NULL, NULL, '長期遅延歴', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(190, 4, '高額保留', 1, NULL, NULL, '高額保留',   0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(190, 5, '無保証変更可能', 1, NULL, NULL, '無保証変更可能',   0, NOW(), 1, NOW(), 1, 1);

/* コードマスターに自動与信NG理由を追加 */
INSERT INTO M_CodeManagement VALUES(191 ,'自動与信NG理由' ,NULL ,'自動与信NG理由' ,1 ,'無保証変更判定' ,0,NULL,0,NULL, NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(191, 1, 'テスト注文', 0, NULL, NULL, '通常NG', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(191, 2, '与信可能金額判定（長期休暇Ver）', 0, NULL, NULL, '通常NG', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(191, 3, '債権返却キャンセル件数', 0, NULL, NULL, '期限切未払', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(191, 4, '不払い件数', 0, NULL, NULL, '期限切未払', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(191, 5, '未払い件数', 1, NULL, NULL, '長期遅延歴', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(191, 6, '基幹与信スコア判定', 0, NULL, NULL, '通常NG', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(191, 7, '審査システムスコア判定', 0, NULL, NULL, '通常NG', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(191, 8, 'ジンテック判定', 0, NULL, NULL, '通常NG', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(191, 9, '手動与信', 0, NULL, NULL, '通常NG', 0, NOW(), 1, NOW(), 1, 1);



/* サイトテーブルに項目を追加 */
ALTER TABLE `T_Site` 
ADD COLUMN `NgChangeFlg` TINYINT(4) NOT NULL DEFAULT 1 AFTER `MultiOrderScore`,
ADD COLUMN `ShowNgReason` TINYINT(4) NOT NULL DEFAULT 1 AFTER `NgChangeFlg`,
ADD COLUMN `MuhoshoChangeDays` INT(11) NOT NULL DEFAULT 7 AFTER `ShowNgReason`;

/* 会計_注文テーブルに項目を追加 */
ALTER TABLE `AT_Order` 
ADD COLUMN `AutoJudgeNgReasonCode` INT(11) DEFAULT NULL AFTER `StopSendMailConfirmJournalFlg`,
ADD COLUMN `ManualJudgeNgReasonCode` INT(11) DEFAULT NULL AFTER `AutoJudgeNgReasonCode`,
ADD COLUMN `NgNoGuaranteeChangeDate` DATETIME DEFAULT NULL AFTER `ManualJudgeNgReasonCode`,
ADD COLUMN `NgButtonFlg` TINYINT(4) DEFAULT NULL AFTER `NgNoGuaranteeChangeDate`,
ADD COLUMN `NoGuaranteeChangeLimitDay` DATE DEFAULT NULL AFTER `NgButtonFlg`;

/* NG無保証変更日にインデックス付与 */
ALTER TABLE `AT_Order` 
ADD INDEX `Idx_AT_Order03` (`NgNoGuaranteeChangeDate` ASC);

/* ﾃﾝﾌﾟﾚｰﾄﾌｨｰﾙﾄﾞのUPDATE（ListNumber に対するUPDATEなので、下から行う。） */
UPDATE M_TemplateField SET ListNumber='37' WHERE TemplateSeq='2' and PhysicalName='ReceiptDate';
UPDATE M_TemplateField SET ListNumber='36' WHERE TemplateSeq='2' and PhysicalName='IsWaitForReceipt';
UPDATE M_TemplateField SET ListNumber='35' WHERE TemplateSeq='2' and PhysicalName='UpdateName';
UPDATE M_TemplateField SET ListNumber='34' WHERE TemplateSeq='2' and PhysicalName='UpdateDate';
UPDATE M_TemplateField SET ListNumber='33' WHERE TemplateSeq='2' and PhysicalName='RegistName';
UPDATE M_TemplateField SET ListNumber='32' WHERE TemplateSeq='2' and PhysicalName='ArrivalConfirmAlert';
UPDATE M_TemplateField SET ListNumber='31' WHERE TemplateSeq='2' and PhysicalName='CancelReasonCode';
UPDATE M_TemplateField SET ListNumber='30' WHERE TemplateSeq='2' and PhysicalName='OutOfAmends';
UPDATE M_TemplateField SET ListNumber='29' WHERE TemplateSeq='2' and PhysicalName='RegistDate';
UPDATE M_TemplateField SET ListNumber='28' WHERE TemplateSeq='2' and PhysicalName='Deli_JournalNumberAlert';
UPDATE M_TemplateField SET ListNumber='27' WHERE TemplateSeq='2' and PhysicalName='ClaimSendingClass';
UPDATE M_TemplateField SET ListNumber='26' WHERE TemplateSeq='2' and PhysicalName='DestPhone';
UPDATE M_TemplateField SET ListNumber='25' WHERE TemplateSeq='2' and PhysicalName='ServiceExpectedDate';
UPDATE M_TemplateField SET ListNumber='24' WHERE TemplateSeq='2' and PhysicalName='EntCustId';
UPDATE M_TemplateField SET ListNumber='23' WHERE TemplateSeq='2' and PhysicalName='Phone';
UPDATE M_TemplateField SET ListNumber='22' WHERE TemplateSeq='2' and PhysicalName='RealCancelStatus';
UPDATE M_TemplateField SET ListNumber='21' WHERE TemplateSeq='2' and PhysicalName='ApprovalDate';
UPDATE M_TemplateField SET ListNumber='20' WHERE TemplateSeq='2' and PhysicalName='ExecScheduleDate';
UPDATE M_TemplateField SET ListNumber='19' WHERE TemplateSeq='2' and PhysicalName='Deli_JournalNumber';
UPDATE M_TemplateField SET ListNumber='18' WHERE TemplateSeq='2' and PhysicalName='Deli_DeliveryMethod';
UPDATE M_TemplateField SET ListNumber='17' WHERE TemplateSeq='2' and PhysicalName='Deli_JournalIncDate';
UPDATE M_TemplateField SET ListNumber='16' WHERE TemplateSeq='2' and PhysicalName='Ent_Note';
UPDATE M_TemplateField SET ListNumber='15' WHERE TemplateSeq='2' and PhysicalName='UseAmount';

/* デフォルトのﾃﾝﾌﾟﾚｰﾄﾌｨｰﾙﾄﾞのINSERT */
INSERT INTO M_TemplateField VALUES ( 2 , 14, 'NgNoGuaranteeChange' ,'NG無保証' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);

/* 各加盟店のﾃﾝﾌﾟﾚｰﾄﾌｨｰﾙﾄﾞのINSERT */
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'NgNoGuaranteeChange' ,'NG無保証' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1 from M_TemplateField where TemplateSeq IN (SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKA01005_1' AND Seq != 0) group by TemplateSeq;


/* メールテンプレートへ[CB向け無保証変更通知メール][加盟店向け無保証変更通知メール]追加 */
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (96,'CB向け無保証変更通知メール','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,'customer@ato-barai.com','無保証変更通知メール（{LoginId} {OrderId}）','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','以下の注文が無保証に変更されました。\r\n加盟店：{LoginId} {EnterpriseName}\r\n注文ID：{OrderId}\r\nNG理由：{NgReason}',0,NOW(),1,NOW(),1,1);
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (97,'加盟店向け無保証変更通知メール','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'【後払いカスタマーセンター】無保証処理受付','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}\r\nご担当者様\r\n\r\nいつも大変お世話になっております。\r\n後払いドットコムカスタマーセンターでございます。\r\n\r\n後払い決済管理システムの「無保証に変更」ボタンにてお申し込みをいただきました\r\n{OrderId} {CustomerNameKj}様の注文を無保証にて受付いたしました。\r\n\r\nご確認いただき、不備や不明点などございましたら\r\nお気軽にお問合せくださいませ。\r\n\r\n今後とも何卒、よろしくお願いいたします。\r\n\r\n\r\n--------------------------------------------------------------\r\n株式会社キャッチボール\r\n〒160-0023 東京都新宿区西新宿6-14-1 新宿グリーンタワービル14F\r\nTEL：03-5909-3490　FAX：03-5909-3939\r\nMAIL：customer@ato-barai.com\r\n営業時間：9:00～18:00　（年末・年始を除き、年中無休）\r\n--------------------------------------------------------------',0,NOW(),1,NOW(),1,1);


/* テンプレートマスタ登録 */
INSERT INTO M_TemplateField VALUES ( 18 , 154, 'NgNoGuaranteeChangeDate' ,'NG無保証変更日' ,'DATETIME' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 18 , 155, 'NgReason' ,'NG理由' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);


/* メールテンプレートの調整 */
UPDATE T_MailTemplate
SET Body = '{EnterpriseNameKj}　様\r\n\r\nいつも【後払いドットコム】をご利用いただき、まことにありがとうございます。\r\n\r\n与信件数：{CreditCount} 件\r\n\r\nの与信結果が出ましたのでご報告いたします。\r\n\r\n【管理画面ＵＲＬ】\r\nhttps://www.atobarai.jp/member/\r\n\r\n※与信がNGのご注文であっても、NG理由によっては、無保証にて「OK」に変更できる場合がございます。\r\n無保証で後払いサービスご希望の方は以下に記載の【NG理由による処理方法について】を参考にしてください。\r\n（無保証でも「OK」に変更できない場合もございますので、弊社からの\r\n返信メールをご確認いただいてから、商品発送などを行ってください。）\r\n\r\n{Orders}\r\n\r\n【OK案件の処理】\r\n与信が通過したお取引に関しましては、\r\n\r\n1.商品の発送\r\n2.配送伝票番号登録\r\n\r\nにお進み下さい。\r\n\r\n【NG理由による処理方法について】\r\n※ NG理由が「長期遅延歴」「高額保留」「無保証変更可能」の場合\r\n無保証での後払いサービスへ 切り替えて頂くことが可能です。\r\n無保証に変更する場合は、このメールより{OutOfAmendsDays}日以内に後払い決済管理システムに\r\nログイン後に操作を実施してください。\r\n\r\n※ 上記以外のNG理由の場合\r\nその他のNG理由のお取引に関しましては、お早めにご購入者様に他の決済方法のご選択を\r\nいただくなどのご対応をお願いいたします。\r\n\r\n--------------------------------------------------------------\r\n\r\n【後払いドットコム】～最も消費者に愛される決済サービス～\r\n\r\n  お問合せ先：0120-667-690\r\n  営業時間：9:00～18:00　年中無休（年末・年始のぞく）\r\n  mail: customer@ato-barai.com\r\n\r\n  運営会社：株式会社キャッチボール\r\n　住所：〒160-0023 東京都新宿区西新宿6-14-1　新宿グリーンタワー14階 \r\n\r\n--------------------------------------------------------------'
WHERE Class = 3 AND IFNULL(OemId, 0) = 0
;

UPDATE T_MailTemplate
SET Body = '{EnterpriseNameKj}　様\r\n\r\nいつも【後払いドットコム】をご利用いただき、まことにありがとうございます。\r\n\r\n与信件数：{CreditCount} 件\r\n\r\nの与信結果が出ましたのでご報告いたします。\r\n\r\n【管理画面ＵＲＬ】\r\nhttps://www.ato-barai.jp/smbcfs/member/\r\n\r\n※与信がNGのご注文であっても、NG理由によっては、無保証にて「OK」に変更できる場合がございます。\r\n無保証で後払いサービスご希望の方は以下に記載の【NG理由による処理方法について】を参考にしてください。\r\n（無保証でも「OK」に変更できない場合もございますので、弊社からの\r\n返信メールをご確認いただいてから、商品発送などを行ってください。）\r\n\r\n{Orders}\r\n\r\n【OK案件の処理】\r\n与信が通過したお取引に関しましては、\r\n\r\n1.商品の発送\r\n2.配送伝票番号登録\r\n\r\nにお進み下さい。\r\n\r\n【NG理由による処理方法について】\r\n※ NG理由が「長期遅延歴」「高額保留」「無保証変更可能」の場合\r\n無保証での後払いサービスへ 切り替えて頂くことが可能です。\r\n無保証に変更する場合は、このメールより{OutOfAmendsDays}日以内に後払い決済管理システムに\r\nログイン後に操作を実施してください。\r\n\r\n※ 上記以外のNG理由の場合\r\nその他のNG理由のお取引に関しましては、お早めにご購入者様に他の決済方法のご選択を\r\nいただくなどのご対応をお願いいたします。\r\n\r\n--------------------------------------------------------------\r\n\r\n【後払いドットコム】～最も消費者に愛される決済サービス～\r\n\r\n  お問合せ先：0120-667-690\r\n  営業時間：9:00～18:00　年中無休（年末・年始のぞく）\r\n  mail: customer@ato-barai.com\r\n\r\n  運営会社：株式会社キャッチボール\r\n　住所：〒160-0023 東京都新宿区西新宿6-14-1　新宿グリーンタワー14階 \r\n\r\n--------------------------------------------------------------'
WHERE Class = 3 AND OemId = 2
;

UPDATE T_MailTemplate
SET Body = '{EnterpriseNameKj}　様\r\n\r\nいつも【後払い決済サービス】をご利用いただき、まことにありがとうございます。\r\n\r\n与信件数：{CreditCount} 件\r\n\r\nの与信結果が出ましたのでご報告いたします。\r\n\r\n【管理画面ＵＲＬ】\r\nhttps://atobarai.seino.co.jp/seino-financial/member/\r\n\r\n※与信がNGのご注文であっても、NG理由によっては、無保証にて「OK」に変更できる場合がございます。\r\n無保証で後払いサービスご希望の方は以下に記載の【NG理由による処理方法について】を参考にしてください。\r\n（無保証でも「OK」に変更できない場合もございますので、弊社からの\r\n返信メールをご確認いただいてから、商品発送などを行ってください。）\r\n\r\n{Orders}\r\n\r\n【OK案件の処理】\r\n与信が通過したお取引に関しましては、\r\n\r\n1.商品の発送\r\n2.配送伝票番号登録\r\n\r\nにお進み下さい。\r\n\r\n【NG理由による処理方法について】\r\n※ NG理由が「長期遅延歴」「高額保留」「無保証変更可能」の場合\r\n無保証での後払いサービスへ 切り替えて頂くことが可能です。\r\n無保証に変更する場合は、このメールより{OutOfAmendsDays}日以内に後払い決済管理システムに\r\nログイン後に操作を実施してください。\r\n\r\n※ 上記以外のNG理由の場合\r\nその他のNG理由のお取引に関しましては、お早めにご購入者様に他の決済方法のご選択を\r\nいただくなどのご対応をお願いいたします。\r\n\r\n--------------------------------------------------------------\r\n\r\n【後払い決済サービス】～最も消費者に愛される決済サービス～\r\n\r\n  お問合せ先：03-5909-4500\r\n  営業時間：9:00～18:00　年中無休（年末・年始のぞく）\r\n  mail: sfc-atobarai@seino.co.jp\r\n\r\n  運営会社：セイノーフィナンシャル株式会社\r\n　住所：〒503-8501 岐阜県大垣市田口町１番地\r\n\r\n--------------------------------------------------------------'
WHERE Class = 3 AND OemId = 3
;


/* サイトテーブルの更新 */
UPDATE T_Site
SET NgChangeFlg = 0
WHERE OutOfAmendsFlg = 1;
