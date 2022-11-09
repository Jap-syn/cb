/* 注文_会計テーブルに伝票確認メール送信ストップ設定を追加 */
ALTER TABLE `AT_Order` 
ADD COLUMN `StopSendMailConfirmJournalFlg` TINYINT(4) NOT NULL DEFAULT 0 AFTER `DefectCancelPlanDate`;

/* 加盟店テーブルに間違い伝番修正依頼メールを追加 */
ALTER TABLE `T_Enterprise` 
ADD COLUMN `SendMailRequestModifyJournalFlg` TINYINT(4) NOT NULL DEFAULT 1 AFTER `HoldBoxFlg`;

/* 配送方法テーブルに修正依頼メール(の送信有無チェック)を追加 */
ALTER TABLE `M_DeliveryMethod` 
ADD COLUMN `SendMailRequestModifyJournalFlg` TINYINT(4) NOT NULL DEFAULT 1 AFTER `ProductServiceClass`;

/* メール送信履歴テーブルにインデックスを追加(03番は運用環境にて一部設定されているので04からとした) */
ALTER TABLE `T_MailSendHistory`
ADD INDEX `Idx_T_MailSendHistory04` (`EnterpriseId` ASC),
ADD INDEX `Idx_T_MailSendHistory05` (`MailSendDate` ASC);


/* コードマスターに伝票確認メール送信を追加 */
INSERT INTO M_Code VALUES(97,35 ,'伝票確認メール（再１）送信',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(97,36 ,'伝票確認メール（再３）送信',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(97,37 ,'伝票確認メール（手動）送信',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);

/* メールテンプレートへ[間違い伝票修正依頼メール][解凍パスワード通知メール]追加 */
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (94,'間違い伝票修正依頼メール','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'【後払いドットコム】伝票番号のご確認をお願いいたします ','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}様 \r\n\r\nお世話になっております。 \r\n後払いドットコムカスタマーセンターでございます。 \r\n\r\n{ReceiptOrderDate}にご注文登録をいただきました、下記お客様の着荷確認が取れない為、現状立替をさせていただくことができておりません。 \r\n\r\nご登録いただいた配送伝票番号に入力ミスがあるか、 商品がお客様に届いていない可能性がございます。 \r\nお手数をおかけいたしますが 個人情報の兼ね合いもございますので、商品の配送会社、配送伝票番号、並びに配送状況を一度店舗様側でご確認いただき、店舗様管理サイト上からご修正くださいますようお願いいたします。\r\n ※編集方法は履歴検索から特定のご注文を絞り込んでいただき、『登録内容の修正』からご修正ください。 \r\n\r\nまた、ご注文をキャンセルされる場合は店舗様管理サイトよりキャンセルの申請をお願いいたします。 \r\n\r\nお取引ID ：{OrderId} \r\n\r\n※ 詳細情報については、添付のファイル(解凍後はCSV形式)をご参照ください。\r\n※ 添付ファイルは、個人情報保護の観点からパスワードを設定しております。\r\n※ 添付ファイルのパスワードは別のメールにてお知らせいたします。\r\n※ 添付ファイルは、「一括注文キャンセル（CSV）」にて、キャンセル申請用のCSVとして、そのままご利用いただけます。\r\n\r\n尚、本日から2週間以内にご変更またはご連絡をいただけず、 伝票番号登録日から約半年が経過し、配送会社の追跡サービスにて 着荷の確認が取れなくなってしまった場合、 無保証扱いとなり順次債権返却をさせていただきますのでご注意願います。 \r\n\r\nご不明な点などございましたら、お気軽にお問い合わせくださいませ。 \r\n\r\n今後ともよろしくお願いいたします。 \r\n\r\n\r\n-------------------------------------------------------------- \r\n後払い請求代行サービス【後払いドットコム】   \r\nお問合せ先：0120-667-690   営業時間：9:00～18:00　年中無休（年末・年始のぞく）   \r\nmail: customer@ato-barai.com   \r\n運営会社：株式会社キャッチボール 　\r\n住所：〒160-0023 東京都新宿区西新宿6-14-1 新宿グリーンタワービル14F \r\n--------------------------------------------------------------\r\n',0,'2017-03-15 17:00:00',1,'2017-04-28 18:26:41',83,1);
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (95,'解凍パスワード通知メール','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'【後払いドットコム】 解凍パスワード通知メール','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}様 \r\n\r\nお世話になっております。  \r\n後払いドットコムカスタマーセンターでございます。  \r\n\r\n先ほど、「【後払いドットコム】伝票番号のご確認をお願いいたします 」の件名で、\r\nお送りしたメールに添付されたファイルの開封パスワードをお知らせいたします。\r\n\r\n添付ファイル名: {FileName} \r\n解凍パスワード: {Password} \r\n\r\nご不明な点などございましたら、お気軽にお問い合わせくださいませ。 \r\n\r\n今後ともよろしくお願いいたします。 \r\n\r\n-------------------------------------------------------------- \r\n後払い請求代行サービス【後払いドットコム】   \r\nお問合せ先：0120-667-690   営業時間：9:00～18:00　年中無休（年末・年始のぞく）   \r\nmail: customer@ato-barai.com   \r\n運営会社：株式会社キャッチボール 　\r\n住所：〒160-0023 東京都新宿区西新宿6-14-1 新宿グリーンタワービル14F \r\n--------------------------------------------------------------',0,'2017-03-15 17:00:00',1,'2017-03-15 17:00:00',1,1);

