/* T_SystemProperty登録(値に対しては桁数考慮あり) */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]', 'cbadmin', 'RepayMailSendDays', '5', '未返金案件メール送付日数', NOW(), 9, NOW(), 9, '1');

/* 返金管理 */
ALTER TABLE T_RepaymentControl ADD COLUMN `RepayMailFlg` TINYINT NOT NULL DEFAULT 0 AFTER `MailRetryCount`;
ALTER TABLE T_RepaymentControl ADD COLUMN `RepayMailRetryCount` TINYINT NOT NULL DEFAULT 0 AFTER `RepayMailFlg`;

/* リリース前にハガキ出力済のデータは対象外とする */
UPDATE T_RepaymentControl SET RepayMailFlg = 1 WHERE NetStatus = 3;

/* 未返金案件メール */
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (102,'未返金案件メール（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'【後払い.com】ご返金のご連絡','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────\r\n◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇\r\n──────────────────────────────────\r\n\r\n{CustomerNameKj}様\r\n\r\nこの度は{SiteNameKj}で商品ご購入の際に、\r\n後払いドットコムをご利用いただきまして\r\nまことにありがとうございました。\r\n\r\n{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、\r\n多くお支払いいただいておりましたので\r\nご返金させていただきたくご連絡差し上げました。\r\n\r\n返金の方法のご案内を、注文者様ご住所宛にハガキにてお送りします。\r\n普通郵便での発送となりますので、お客様のお手元に届くまで\r\n一週間程度かかる場合がございます。\r\n一週間ほどお待ちいただいても届かない場合は、\r\n大変お手数ではございますが、このメールの末尾に記載しております\r\n後払いドットコムカスタマーセンターまでご一報くださいませ。\r\nなお、ご返金の際の手数料324円はお客様負担になる旨、 \r\nご理解賜りますようお願いいたします。 \r\n\r\n\r\n\r\n【ご注文内容】\r\nご注文ID：{OrderId}\r\nご注文日：{OrderDate}\r\nご注文店舗：{SiteNameKj}\r\n商品名（1品目のみ表示）：{OneOrderItem}\r\nご請求金額：{UseAmount}\r\n\r\n\r\n\r\n不明点などございましたら、お気軽にお問合せくださいませ。\r\n\r\n--------------------------------------------------------------\r\n後払い請求代行サービス【後払いドットコム】\r\n\r\n  お問合せ先：03－5909－3490\r\n  営業時間：9:00～18:00　年中無休（年末・年始のぞく）\r\n  mail: customer@ato-barai.com\r\n  \r\n　運営会社：株式会社キャッチボール\r\n　住所：〒160-0023 東京都新宿区西新宿6-14-1 新宿グリーンタワービル14F\r\n-------------------------------------------------------------- \r\n',NULL,'2016-02-23 14:00:00',1,'2017-12-29 15:31:00',66,1);
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (103,'未返金案件メール（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'【後払い.com】ご返金のご連絡','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────\r\n◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇\r\n──────────────────────────────────\r\n\r\n{CustomerNameKj}様\r\n\r\nこの度は{SiteNameKj}で商品ご購入の際に、\r\n後払いドットコムをご利用いただきまして\r\nまことにありがとうございました。\r\n\r\n{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、\r\n多くお支払いいただいておりましたので\r\nご返金させていただきたくご連絡差し上げました。\r\n\r\n返金の方法のご案内を、注文者様ご住所宛にハガキにてお送りします。\r\n普通郵便での発送となりますので、お客様のお手元に届くまで\r\n一週間程度かかる場合がございます。\r\n一週間ほどお待ちいただいても届かない場合は、\r\n大変お手数ではございますが、このメールの末尾に記載しております\r\n後払いドットコムカスタマーセンターまでご一報くださいませ。\r\nなお、ご返金の際の手数料324円はお客様負担になる旨、 \r\nご理解賜りますようお願いいたします。 \r\n\r\n\r\n\r\n【ご注文内容】\r\nご注文ID：{OrderId}\r\nご注文日：{OrderDate}\r\nご注文店舗：{SiteNameKj}\r\n商品名（1品目のみ表示）：{OneOrderItem}\r\nご請求金額：{UseAmount}\r\n\r\n\r\n不明点などございましたら、お気軽にお問合せくださいませ。\r\n\r\n--------------------------------------------------------------\r\n後払い請求代行サービス【後払いドットコム】\r\n\r\n  お問合せ先：03－5909－3490\r\n  営業時間：9:00～18:00　年中無休（年末・年始のぞく）\r\n  mail: customer@ato-barai.com\r\n \r\n　運営会社：株式会社キャッチボール\r\n　住所：〒160-0023 東京都新宿区西新宿6-14-1 新宿グリーンタワービル14F\r\n-------------------------------------------------------------- \r\n',NULL,'2016-02-23 14:00:00',1,'2017-12-29 15:32:13',66,1);
