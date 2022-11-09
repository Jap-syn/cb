/* メールテンプレート追加*/
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) 
VALUES (110,'与信実行待ち注文通知','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'【障害】30分以上与信実行待ちになっている注文があります','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','30分以上与信実行待ちになっている注文があります。\r\nスレッドの一時的な変更をお願いします。\r\n対象注文は以下\r\n{OrderList}',NULL,NOW(),1,NOW(),83,1);

/* 埋め込み文字追加 */
INSERT INTO `M_Code` (`CodeId`,`KeyCode`,`KeyContent`,`Class1`,`Class2`,`Class3`,`Note`,`SystemFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) 
VALUES (72,307,'注文リスト','{OrderList}',110,null,null,1,NOW(),1,NOW(),1,1);

/*コメント設定管理*/
INSERT INTO M_CodeManagement VALUES(209,'与信実行待ち注文データ送信対象',NULL,'与信実行待ち注文データ送信対象',0,NULL,0,NULL,0,NULL,NOW(),1,NOW(),1,1);

/*コメント設定のフィールドに追加*/
INSERT INTO M_Code VALUES(209,1,'送信先メールアドレス',NULL,NULL,NULL,'system@ato-barai.com,cb-360resysmember@mb.scroll360.jp',0,NOW(),1,NOW(),1,1);