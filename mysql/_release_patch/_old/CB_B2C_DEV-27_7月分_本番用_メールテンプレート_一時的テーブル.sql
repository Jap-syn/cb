-- Create temporary MailTemplate to insert data from www3
CREATE TABLE `T_MailTemplate_Tmp` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `Class` int(11) DEFAULT NULL,
  `ClassName` varchar(256) DEFAULT NULL,
  `FromTitle` varchar(1000) DEFAULT NULL,
  `FromTitleMime` varchar(1000) DEFAULT NULL,
  `FromAddress` varchar(1000) DEFAULT NULL,
  `ToTitle` varchar(1000) DEFAULT NULL,
  `ToTitleMime` varchar(1000) DEFAULT NULL,
  `ToAddress` varchar(1000) DEFAULT NULL,
  `Subject` varchar(1000) DEFAULT NULL,
  `SubjectMime` varchar(1000) DEFAULT NULL,
  `Body` varchar(4000) DEFAULT NULL,
  `OemId` bigint(20) DEFAULT NULL,
  `RegistDate` datetime DEFAULT NULL,
  `RegistId` int(11) DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `UpdateId` int(11) DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- insert data from w3
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (1,1,'事業者登録完了（サービス開始）メール','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】アカウント発行のお知らせ','{ServiceName}','{EnterpriseNameKj}　様

この度は弊社サービス【{ServiceName}】にお申込みいただき、
まことにありがとうございます。

アカウント発行が完了いたしましたので
サービス開始までに必要な最終手順のご案内をいたします。
最後までご覧ください。


決済管理システムのログイン情報をお知らせいたします。

【管理サイトＵＲＬ】
https://www.atobarai.jp/member/

【ログインＩＤ】
{LoginId}
　※　ログインパスワードは別メールにてお知らせいたします。


※！※！※！※！※！※！※！※！※！※！※！※！※！※！※

　サービス開始まで、以下STEP.1～4までのお手続きが必要です。
　必ずご確認ください。

※！※！※！※！※！※！※！※！※！※！※！※！※！※！※


　■■■　STEP.1　登録内容のご確認　■■■

決済管理システムにログインいただき、
「登録情報管理」のメニューより
登録されている店舗情報がお間違いないかをご確認ください。
（サイト名、ご利用プランなど）

　■■■　STEP.2　定型文のサイト掲載　■■■

サイト上に、消費者様へ向けたサイト掲載用定型文をご掲載ください。
詳細は同時配信の別メールにてご案内いたします。
（掲載箇所：特定商取引法ページや決済選択画面など）

　※　掲載いただいた時点からのサービス開始となります。

　■■■　STEP.3　サービス開始の当社へのご通知　■■■

サービスの開始（サイト掲載用定型文の掲載）が完了された旨を
弊社までメールにてお知らせください。
mail：{ServiceMail}

　■■■　STEP.4　弊社が決済画面を確認　■■■

弊社担当が各ページを拝見し、問題がなければ
そのまま弊社サービスを運用いただいて問題ございません。

　※　場合により修正のお願いをすることがございます。


以上でございます。

今後とも末永いお付き合いの程、よろしくお願いいたします。


株式会社キャッチボール
　{ServiceName}事業部　スタッフ一同

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/19 16:34:27',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (2,2,'注文登録（与信開始）メール','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】与信開始のお知らせ（{OrderCount}件）','{ServiceName}','{EnterpriseNameKj} 様

いつも【{ServiceName}】をご利用いただき、まことにありがとうございます。
以下のご注文を受け付けいたしました。
これより与信に入りますので、商品をまだ発送されないようご注意下さい。

受付注文件数：{OrderCount}件

ご注文者名（ご請求総額）
--------------------------------------------------------------
{OrderSummary}
--------------------------------------------------------------
上記お取引の与信完了後に、与信完了メールを送信いたします。


※18:00以降の与信は、通常翌日11:00までの回答となりますのでご注意下さい。
※注文により与信にかかる時間が異なる場合がございます。その場合、与信結果が
出たものから自動で与信完了メールが送信されますので、あらかじめご了承下さい。


■■■■■■■■■■■　キャンセルが発生した場合　■■■■■■■■■■■

ご登録された注文のキャンセルが入った場合は、お手数ですが「履歴検索」から
ご注文を検索し、該当のお取引をクリックしてキャンセル処理を行って下さい。

■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

【管理画面ＵＲＬ】
https://www.atobarai.jp/member/

ご不明な点などございましたら、お気軽にお問い合わせ下さい。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/19 16:35:43',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (3,3,'与信完了メール','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】与信完了のお知らせ：計{CreditCount}件（うちNG{NgCount}件）','{ServiceName}','{EnterpriseNameKj}　様

いつも【{ServiceName}】をご利用いただき、まことにありがとうございます。

与信件数：{CreditCount} 件

の与信結果が出ましたのでご報告いたします。

【管理画面ＵＲＬ】
https://www.atobarai.jp/member/

※「NG」のご注文は無保証であれば「OK」に変更できる場合がございます。
無保証で{ServiceName}ご希望の方はメールにてご連絡下さい。
（無保証でも「OK」に変更できない場合もございますので、弊社からの
返信メールをご確認いただいてから、商品発送などを行ってください。）

{Orders}

【OK案件の処理】
与信が通過したお取引に関しましては、

1.商品の発送
2.配送伝票番号登録

にお進み下さい。

【NG案件の処理】
与信結果がNGのお取引に関しましては、まことにお手数ですが、無保証での{ServiceName}
サービスに切り替えていただくか、お早めにご購入者様に他の決済方法のご選択を
いただくなどのご対応をお願いいたします。

ご不明な点などございましたら、メールもしくはお電話にてお問合せ下さい。

なお、与信結果理由につきましては恐れながら、ご回答することができませんので
あらかじめご了承くださいませ。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/19 16:36:30',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (4,4,'請求書発行メール（PC）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】請求書発行案内　（ハガキで届きます）　ご注文番号：{OrderId}','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください。

◇ホームページにて、「よくある質問」を掲載しておりますので、併せてご参照ください。
　ホームページ：https://atobarai-user.jp/
　請求書について：https://atobarai-user.jp/faq/invoice/
　請求書未着・紛失について：https://atobarai-user.jp/faq/non-arrival

◇ご利用状況は下記注文情報確認ページでご確認いただけます。
　簡易ログインページ　{OrderPageAccessUrl}
　(簡易ログイン有効期間は{LimitDate}より14日間となります。)

◇当メールを受信してから１週間経過しても請求書が届かない場合や、
　請求書の紛失などによる【再発行依頼】は
　上記のURLからお手続きいただけます。
──────────────────────────────────
 
{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【{ServiceName}】をご利用いただきまして、
まことにありがとうございます。

ご請求書を発行いたしました。
請求書到着後、期日までにお支払いくださいますよう、
お願い申し上げます。
お支払い期日は{LimitDate}でございます。

ご請求書は、普通郵便での発送となりますので、お客様のお手元に届くまで
一週間程度かかる場合がございます。また商品の発送状況により、
商品より先に請求書が届く可能性がございます。
その場合は、商品が到着してからお支払いくださいませ。

※一週間ほどお待ちいただいても請求書が届かない場合は、
大変お手数ではございますが、当メール冒頭の【簡易ログインページ】より
再発行のお手続きをいただくか、このメールの末尾に記載しております
{ServiceName}カスタマーセンターまでご一報くださいませ。


【ご注文内容】
ご注文番号：{OrderId}
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
ご請求金額：{UseAmount}


<よくある質問集>
Q1.商品について問い合わせをしたいです。
A1.商品に関するお問い合わせの場合は、
購入店に直接お問い合わせをお願いいたします。
購入店舗：{SiteNameKj}　
電話：{Phone}

Q2.支払い期限を過ぎてしまいましたが、どうしたらよいですか？
A2.ご状況のご確認をさせていただきますので至急弊社（03-4326-3600）
までご連絡をお願いいたします。
請求書に関するお問い合わせは、以下の順番でボタン操作をお願いします。
【音声ガイダンス】→【1】→【1】請求書に関するお問い合わせ

Q3.注文情報を確認する方法を教えてください。
A3.注文情報は、請求書に記載しております。より詳しい情報は、
下記ウェブページでもご確認いただけます。
・注文情報確認ページ　{OrderPageUrl}
　※ログインにはご注文時のお電話番号と、
　　請求書に記載されているパスワードをご利用ください。

すでに後払いドットコム・届いてから払い会員登録がお済のお客様は下記よりログインしてください。
・会員様用マイページ　https://www.atobarai.jp/mypage
　※ログインには会員登録時のメールアドレスと、
　　任意でご登録いただいたパスワードをご利用ください。

Q4.どこで支払いができますか？
A4.当メール冒頭の【簡易ログインページ】よりご確認くださいますようお願い申し上げます。

Q5.もう一度商品を注文したいです。
A5.お手数ですが、ご購入された店舗様で再度注文をお願いいたします。

下記ページでもご質問に関する答えを検索することができます。
https://atobarai-user.jp/

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 5:07:07',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (5,5,'請求書発行メール（CEL）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】請求書発行案内　（ハガキで届きます）　ご注文番号：{OrderId}','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください。

◇ホームページにて、「よくある質問」を掲載しておりますので、併せてご参照ください。
　ホームページ：https://atobarai-user.jp/
　請求書について：https://atobarai-user.jp/faq/invoice/
　請求書未着・紛失について：https://atobarai-user.jp/faq/non-arrival

◇ご利用状況は下記注文情報確認ページでご確認いただけます。
　簡易ログインページ　{OrderPageAccessUrl}
　(簡易ログイン有効期間は{LimitDate}より14日間となります。)

◇当メールを受信してから１週間経過しても請求書が届かない場合や、
　請求書の紛失などによる【再発行依頼】は
　上記のURLからお手続きいただけます。
──────────────────────────────────
 
{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【{ServiceName}】をご利用いただきまして、
まことにありがとうございます。

ご請求書を発行いたしました。
請求書到着後、期日までにお支払いくださいますよう、
お願い申し上げます。
お支払い期日は{LimitDate}でございます。

ご請求書は、普通郵便での発送となりますので、お客様のお手元に届くまで
一週間程度かかる場合がございます。また商品の発送状況により、
商品より先に請求書が届く可能性がございます。
その場合は、商品が到着してからお支払いくださいませ。

※一週間ほどお待ちいただいても請求書が届かない場合は、
大変お手数ではございますが、当メール冒頭の【簡易ログインページ】より
再発行のお手続きをいただくか、このメールの末尾に記載しております
{ServiceName}カスタマーセンターまでご一報くださいませ。


【ご注文内容】
ご注文番号：{OrderId}
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
ご請求金額：{UseAmount}


<よくある質問集>
Q1.商品について問い合わせをしたいです。
A1.商品に関するお問い合わせの場合は、
購入店に直接お問い合わせをお願いいたします。
購入店舗：{SiteNameKj}　
電話：{Phone}

Q2.支払い期限を過ぎてしまいましたが、どうしたらよいですか？
A2.ご状況のご確認をさせていただきますので至急弊社（03-4326-3600）
までご連絡をお願いいたします。
請求書に関するお問い合わせは、以下の順番でボタン操作をお願いします。
【音声ガイダンス】→【1】→【1】請求書に関するお問い合わせ

Q3.注文情報を確認する方法を教えてください。
A3.注文情報は、請求書に記載しております。より詳しい情報は、
下記ウェブページでもご確認いただけます。
・注文情報確認ページ　{OrderPageUrl}
　※ログインにはご注文時のお電話番号と、
　　請求書に記載されているパスワードをご利用ください。

すでに後払いドットコム・届いてから払い会員登録がお済のお客様は下記よりログインしてください。
・会員様用マイページ　https://www.atobarai.jp/mypage
　※ログインには会員登録時のメールアドレスと、
　　任意でご登録いただいたパスワードをご利用ください。

Q4.どこで支払いができますか？
A4.当メール冒頭の【簡易ログインページ】よりご確認くださいますようお願い申し上げます。

Q5.もう一度商品を注文したいです。
A5.お手数ですが、ご購入された店舗様で再度注文をお願いいたします。

下記ページでもご質問に関する答えを検索することができます。
https://atobarai-user.jp/

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 5:07:30',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (6,6,'入金確認メール（PC）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】ご入金を確認いたしました。ご注文番号： {OrderId} ','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}様

この度は、{SiteNameKj}様で商品ご購入の際に、
{ServiceName}をご利用いただきまして
まことにありがとうございました。

{ReceiptDate}に{CustomerNameKj}様からのご入金を
確認いたしましたのでご報告いたします。

以下が、今回ご入金いただいたご注文の内容でございます。

【領収済みご注文内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
ご入金金額：{UseAmount}

またのご利用を心よりお待ちしております。

なお、ご入金額とご請求金額に差異がある場合は、ご連絡、ご請求
をさせていただく場合がございます。

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
https://atobarai-user.jp/

■商品の返品・未着など商品については
直接ご購入店様にお問い合わせください。
ご購入店様：{SiteNameKj}
電話：{Phone}
URL：{SiteUrl}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------

',null,'2015/08/31 22:42:31',9,'2022/04/26 2:58:07',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (7,7,'入金確認メール（CEL）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】ご入金を確認いたしました。ご注文番号： {OrderId} ','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}様

この度は、{SiteNameKj}様で商品ご購入の際に、
{ServiceName}をご利用いただきまして
まことにありがとうございました。

{ReceiptDate}に{CustomerNameKj}様からのご入金を
確認いたしましたのでご報告いたします。

以下が、今回ご入金いただいたご注文の内容でございます。

【領収済みご注文内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
ご入金金額：{UseAmount}

またのご利用を心よりお待ちしております。

なお、ご入金額とご請求金額に差異がある場合は、ご連絡、ご請求
をさせていただく場合がございます。

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
https://atobarai-user.jp/

■商品の返品・未着など商品については
直接ご購入店様にお問い合わせください。
ご購入店様：{SiteNameKj}
電話：{Phone}
URL：{SiteUrl}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------

',null,'2015/08/31 22:42:31',9,'2022/04/26 2:58:17',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (8,8,'立替完了メール','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】立替金お支払いのご報告','{ServiceName}','{EnterpriseNameKj} 様

いつも【{ServiceName}】をご利用いただきまして、
まことにありがとうございます。

立替分のお支払いが完了いたしましたので、
報告申し上げます。

【管理画面ＵＲＬ】
https://www.atobarai.jp/member/

お支払サイト　　：　{FixedPattern}
立替締日　　　　：　{FixedDate}
振込実行日　　　：　{ExecDate}
お支払額　　　　：　{DecisionPayment}円
決済手数料　　　：　{SettlementFee}円
請求手数料　　　：　{ClaimFee}円
印紙代合計　　　：　{StampFee}円
キャンセル返金　：　{CancelAmount}円
月額固定費　　　：　{MonthlyFee}円
お振込み手数料　：　{TransferCommission}円

お支払に関しましてご不明な点などございましたら、
下記連絡先までお気軽にお問い合わせ下さいませ。

今後とも弊社サービス【{ServiceName}】を、よろしく
お願い申し上げます。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 5:49:29',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (9,9,'キャンセル確認メール','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】キャンセル確定のご報告({OrderId})','{ServiceName}','{EnterpriseNameKj}　様

いつも【{ServiceName}】をご利用いただきまして、まことにありがとうございます。

以下のご注文のキャンセルを承りましたので、ご確認下さい。

【管理画面ＵＲＬ】
https://www.atobarai.jp/member/

【キャンセル確定情報】
キャンセル区分：{CancelPhase}
ご注文ID：{OrderId}
購入者様氏名：{CustomerNameKj}　様
ご注文総額：{UseAmount}
ご注文日：{OrderDate}

※購入者様が店舗様の口座へ直接入金された場合や、店舗様が誤って代引きで
　発送された場合等の、購入者様と店舗様間でのお取引が成立している場合の
　キャンセル処理の際には、所定の手数料を次回立替時の調整額にて
　徴収させていただきます。

ご不明な点などございましたら、お気軽に当社までお問い合わせ下さい。


-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2023/01/04 13:21:02',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (10,10,'アドレス確認メール','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】のご選択ありがとうございます。ご注文番号： {OrderId} ','{ServiceName}','{CustomerNameKj}様

この度は、お支払方法に【{ServiceName}】をご選択いただき、
まことにありがとうございます。

ただいま、下記のご注文におきまして{ServiceName}をご利用いただけるか、
審査をいたしております。

【ご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料  {SettlementFee}円
送料        {DeliveryFee}円


結果につきましては、ご注文いただきました店舗様より、
後ほどご連絡が入りますので、もう少々お待ち下さいませ。

ご不明な点などございましたら、お気軽にお問い合わせ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/19 16:42:13',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (11,11,'もうすぐお支払メール（PC）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】請求書到着確認のお知らせ　ご注文番号：{OrderId}','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既に請求書がお手元に届いていらっしゃる、もしくはご入金のお手続きが
　お済のようであれば当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は、{SiteNameKj}様でのお買い物に、
【{ServiceName}】をご利用いただきまして、まことにありがとうございます。
{IssueDate}に請求書をお送りいたしましたが、お手元にお届きでしょうか。

【ご注文内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
ご請求金額：{UseAmount}

※請求書がまだ届いていない場合は大変お手数ですが、
早急に 03-4326-3600 にご一報ください。
営業時間：9:00～18:00

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担となります)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。


【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}
※ご注文ごとに口座番号が異なっております。
※一度ご入金頂きますと再度ご入金を受け付けることが
　できませんのでご注意ください。

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

※商品の返品・未着など商品に関するお問い合わせは、大変お手数ですが
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}
電話：{Phone}


ご不明な点などございましたら下記ＵＲＬの「よくあるご質問」をご覧ください。
https://atobarai-user.jp/faq/


-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 2:06:57',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (12,12,'もうすぐお支払メール（CEL）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】請求書到着確認のお知らせ　ご注文番号：{OrderId}','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既に請求書がお手元に届いていらっしゃる、もしくはご入金のお手続きが
　お済のようであれば当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は、{SiteNameKj}様でのお買い物に、
【{ServiceName}】をご利用いただきまして、まことにありがとうございます。
{IssueDate}に請求書をお送りいたしましたが、お手元にお届きでしょうか。

【ご注文内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
ご請求金額：{UseAmount}

※請求書がまだ届いていない場合は大変お手数ですが、
早急に 03-4326-3600 にご一報ください。
営業時間：9:00～18:00

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担となります)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。


【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}
※ご注文ごとに口座番号が異なっております。
※一度ご入金頂きますと再度ご入金を受け付けることが
　できませんのでご注意ください。

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

※商品の返品・未着など商品に関するお問い合わせは、大変お手数ですが
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}
電話：{Phone}

ご不明な点などございましたら下記ＵＲＬの「よくあるご質問」をご覧ください。
https://atobarai-user.jp/faq/


-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 2:07:14',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (13,13,'お支払い未確認メール（PC）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】{OrderDate}　{SiteNameKj}でのお買い物の件　ご注文番号：{OrderId}','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金済みの場合は当メールへの返信はご不要でございます。


{CustomerNameKj}様

{OrderDate}に{SiteNameKj}様でのお買い物に、
【{ServiceName}】ご利用いただきありがとうございます。

{IssueDate}に請求書をお送りいたしましたが、
本日現在ご入金の確認ができておりません。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまい、再度請求書が発行されますと
再請求手数料が加算される場合がございますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担となります)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}
※ご注文ごとに口座番号が異なっております。
※一度ご入金頂きますと再度ご入金を受け付けることが
　できませんのでご注意ください。

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。

https://atobarai-user.jp/

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 5:52:11',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (14,14,'お支払い未確認メール（CEL）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】{OrderDate}　{SiteNameKj}でのお買い物の件　ご注文番号：{OrderId}','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金済みの場合は当メールへの返信はご不要でございます。


{CustomerNameKj}様

{OrderDate}に{SiteNameKj}様でのお買い物に、
【{ServiceName}】ご利用いただきありがとうございます。

{IssueDate}に請求書をお送りいたしましたが、
本日現在ご入金の確認ができておりません。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまい、再度請求書が発行されますと
再請求手数料が加算される場合がございますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担となります)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}
※ご注文ごとに口座番号が異なっております。
※一度ご入金頂きますと再度ご入金を受け付けることが
　できませんのでご注意ください。

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。

https://atobarai-user.jp/

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 5:52:18',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (15,15,'伝票番号確認のお願い','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】未立替注文分についてご確認をお願いいたします','{ServiceName}','{EnterpriseNameKj}様

いつも【{ServiceName}】をご利用いただき、　
まことにありがとうございます。

下記のご注文について、
現在、(WEB上で)着荷確認が取れず、
お支払い期限を過ぎても入金の確認ができていないため、
立替ができていない状況でございますので
ご確認をいただきたくご連絡をいたしました。

まことにお手数をおかけいたしますが、
内容をご確認いただき、【2週間以内】に
弊社「決済管理システム」より修正、
またはご連絡をいただきますようお願いいたします。
 
※期限内にご変更またはご連絡をいただけず、
配送会社の追跡サービスにて着荷の確認が取れなくなった場合、
『無保証』扱いとなり、順次『債権返却』（ご注文のキャンセル）と
させていただきますのでご注意願います。

お取引ID ：{OrderId}
ご注文者様名 ：{CustomerNameKj} 様
伝票番号登録日 ：{Deli_JournalIncDate}
登録配送会社：{DeliMethodName}
登録伝票番号 ：{Deli_JournalNumber}

なお、着荷の確認が取れていない原因といたしましては、
下記のいずれかに該当する可能性がございます。

＊配送会社の選択間違い
＊配送伝票番号の入力間違い
＊お客様に商品が届いていない
＊キャンセル申請漏れ
 
また、受領書がございます場合には添付いただければ
弊社にて確認いたします。
 
 
ご不明な点などございましたら、お気軽にお問い合わせください。
 
今後ともよろしくお願いいたします。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 5:52:48',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (16,16,'戻り請求住所確認メール','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】【重要】ご住所確認の連絡です。{OrderId} ','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────  

{CustomerNameKj}様

{OrderDate}に{SiteNameKj}で、
【{ServiceName}】をご利用いただきありがとうございます。
{ClaimDate}にお送りいたしました請求書が弊社に戻ってきておりますので、
ご住所の確認をさせていただきたくご連絡させていただきました。

（お客様住所）　{UnitingAddress}

上記住所に不備がありましたら、再度請求書を発行させていただきますので
ご連絡の程、よろしくお願い致します。

住所に不備がない場合でも、表札氏名が違っていた場合などで、郵便物が届かないケースも
ありますので、ご了承下さい。

また、銀行・郵便局からのご入金も可能ですので
口座番号をお送りさせていただきます。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}
※ご注文ごとに口座番号が異なっております。
※一度ご入金頂きますと再度ご入金を受け付けることが
　できませんのでご注意ください。

【郵便振替口座】
口座記号：00120-7
口座番号：670031
カ）キャッチボール


【ご請求明細】
商品名　　：{ItemNameKj}
商品代金　：{ItemAmount}円
送料　　　：{DeliveryFee}円
手数料　　：{SettlementFee}円
{OptionFee}
合計　　　：{UseAmount}円
 （振込手数料はお客様ご負担となります。） 

その他ご不明な点、ご入金のご相談等は当社までお問い合わせください。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 2:08:29',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (17,17,'請求書発行メール（同梱ツール向け：PC）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】ご利用についてのご案内　ご注文番号：{OrderId}','{ServiceName}','──────────────────────────────────
◇お願い：お問い合わせ頂く際、必ず下記メール文面を残したままご返信ください◇
────────────────────────────────── 

※当メールは、【{ServiceName}】をご利用いただきましたお客様へお送りしております。
「商品発送のお知らせメール」ではございません。
商品の発送・到着予定日についてはご購入店様へのお問い合わせをお願い申し上げます。 

 
 {CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【{ServiceName}】をご利用いただきまして、
まことにありがとうございます。

■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□

ご請求書（払込用紙）は商品と一緒にお届けいたしますので、
商品到着後、請求書に記載のお支払い期限日までにお支払いいただきますよう、
お願い申し上げます。

■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□


【ご注文内容】
お支払者：{CustomerNameKj}　様
ご注文番号：{OrderId}
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料                              {SettlementFee}円
送料                                    {DeliveryFee}円

※商品と共に請求書が入っていない場合には、大変お手数ですが、
　【{ServiceName}】へご一報くださいますよう、お願い申し上げます。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、下記
　購入店舗様に直接お問合せください。

※※※その他ご不明な点は下記ＵＲＬをご確認ください。※※※
　　　　https://atobarai-user.jp/faq/

■商品・返品・配送に関するお問い合わせは
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは
株式会社キャッチボール
TEL:03-4326-3600 (平日土日9:00～18:00)
Mail: {ServiceMail}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 2:09:43',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (18,18,'請求書発行メール（同梱ツール向け：CEL）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】ご利用についてのご案内　ご注文番号：{OrderId}','{ServiceName}','──────────────────────────────────
◇お願い：お問い合わせ頂く際、必ず下記メール文面を残したままご返信ください◇
────────────────────────────────── 

※当メールは、【{ServiceName}】をご利用いただきましたお客様へお送りしております。
「商品発送のお知らせメール」ではございません。
商品の発送・到着予定日についてはご購入店様へのお問い合わせをお願い申し上げます。 

 
 {CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【{ServiceName}】をご利用いただきまして、
まことにありがとうございます。

■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□

ご請求書（払込用紙）は商品と一緒にお届けいたしますので、
商品到着後、請求書に記載のお支払い期限日までにお支払いいただきますよう、
お願い申し上げます。

■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□


【ご注文内容】
お支払者：{CustomerNameKj}　様
ご注文番号：{OrderId}
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料                              {SettlementFee}円
送料                                    {DeliveryFee}円

※商品と共に請求書が入っていない場合には、大変お手数ですが、
　【{ServiceName}】へご一報くださいますよう、お願い申し上げます。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、下記
　購入店舗様に直接お問合せください。

※※※その他ご不明な点は下記ＵＲＬをご確認ください。※※※
　　　　https://atobarai-user.jp/faq/

■商品・返品・配送に関するお問い合わせは
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは
株式会社キャッチボール
TEL:03-4326-3600 (平日土日9:00～18:00)
Mail: {ServiceMail}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 2:09:34',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (19,19,'与信結果メール(OK, PC)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【 {ServiceName} 】与信結果のお知らせ','{ServiceName}','──────────────────────────────────
◇お願い：お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────


{CustomerNameKj} 様

このたびは{SiteNameKj}で{OneOrderItem}をご注文された際のお支払い方法に、
【{ServiceName}】をご選択いただきまして、まことにありがとうございます。

このたびのご注文につきまして、【{ServiceName}】の与信審査が
通過いたしましたことをご報告申し上げます。
※請求書を発行の際は、改めて弊社よりメールをお送りいたしますのでご確認ください。


なお、ご注文いただきました商品につき、以下の内容に関しましては
{SiteNameKj}での対応となりますので、直接ご連絡いただきますようお願い申し上げます。

　・商品に関するお問い合わせ
　・ご注文内容の変更
　・ご注文のキャンセル

【{SiteNameKj}】
{ContactPhoneNumber}


また、{ServiceName}決済に関し、ご不明な点などございましたら、
下記、【{ServiceName}】カスタマーセンターへお問い合わせください。

【【{ServiceName}】カスタマーセンター】
運営会社：(株)キャッチボール
TEL:03-4326-3600
営業時間：9:00～18:00　年中無休(年末・年始をのぞく)

以上、今後とも、よろしくお願い申し上げます。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 2:28:00',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (20,20,'与信結果メール(OK, CEL)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【 {ServiceName} 】与信結果のお知らせ','{ServiceName}','──────────────────────────────────
◇お願い：お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────


{CustomerNameKj} 様

このたびは{SiteNameKj}で{OneOrderItem}をご注文された際のお支払い方法に、
【{ServiceName}】をご選択いただきまして、まことにありがとうございます。

このたびのご注文につきまして、【{ServiceName}】の与信審査が
通過いたしましたことをご報告申し上げます。
※請求書を発行の際は、改めて弊社よりメールをお送りいたしますのでご確認ください。


なお、ご注文いただきました商品につき、以下の内容に関しましては
{SiteNameKj}での対応となりますので、直接ご連絡いただきますようお願い申し上げます。

　・商品に関するお問い合わせ
　・ご注文内容の変更
　・ご注文のキャンセル

【{SiteNameKj}】
{ContactPhoneNumber}


また、{ServiceName}決済に関し、ご不明な点などございましたら、
下記、【{ServiceName}】カスタマーセンターへお問い合わせください。

【【{ServiceName}】カスタマーセンター】
運営会社：(株)キャッチボール
TEL:03-4326-3600
営業時間：9:00～18:00　年中無休(年末・年始をのぞく)

以上、今後とも、よろしくお願い申し上げます。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 2:28:08',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (21,21,'与信結果メール(NG, PC)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【 {ServiceName} 】与信結果のお知らせ','{ServiceName}','──────────────────────────────────
◇お願い：お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj} 様

このたびは{SiteNameKj}で{OneOrderItem}をご注文された際のお支払い方法に
【{ServiceName}】をご選択いただきまして、まことにありがとうございます。

このたびのご注文につき、【{ServiceName}】の与信審査の結果、
審査が通過いたしませんでしたのでお知らせいたします。

つきましては、まことにお手数ではございますが、 
{SiteNameKj}へご連絡のうえ、他のお支払い方法にご変更いただきたく存じます。

【{SiteNameKj}】
{ContactPhoneNumber}

※お支払い方法をご変更いただいた場合、
　【{ServiceName}】に関する手数料は一切発生いたしません。


なお、ご注文いただきました商品につき、以下の内容に関しましては
{SiteNameKj}での対応となりますので、直接ご連絡いただきますようお願い申し上げます。

　・商品に関するお問い合わせ
　・ご注文内容の変更
　・ご注文のキャンセル


また、【{ServiceName}】の与信審査につきましては、
【{ServiceName}】を運営しております(株)キャッチボールにて行っております。

与信審査結果に関する詳細の内容につきましては、個人情報を含む内容になりますため、
弊社から{SiteNameKj}へは一切開示しておりません。

与信審査結果に関するお問い合わせにつきましては、
直接お電話にて弊社へご連絡いただきますようお願い申し上げます。

【【{ServiceName}】カスタマーセンター】
運営会社：（株）キャッチボール
TEL:03-4326-3600
営業時間：9:00～18:00　年中無休(年末・年始をのぞく)

以上、よろしくお願い申し上げます。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 2:28:13',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (22,22,'与信結果メール(NG, CEL)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【 {ServiceName} 】与信結果のお知らせ','{ServiceName}','──────────────────────────────────
◇お願い：お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj} 様

このたびは{SiteNameKj}で{OneOrderItem}をご注文された際のお支払い方法に
【{ServiceName}】をご選択いただきまして、まことにありがとうございます。

このたびのご注文につき、【{ServiceName}】の与信審査の結果、
審査が通過いたしませんでしたのでお知らせいたします。

つきましては、まことにお手数ではございますが、 
{SiteNameKj}へご連絡のうえ、他のお支払い方法にご変更いただきたく存じます。

【{SiteNameKj}】
{ContactPhoneNumber}

※お支払い方法をご変更いただいた場合、
　【{ServiceName}】に関する手数料は一切発生いたしません。


なお、ご注文いただきました商品につき、以下の内容に関しましては
{SiteNameKj}での対応となりますので、直接ご連絡いただきますようお願い申し上げます。

　・商品に関するお問い合わせ
　・ご注文内容の変更
　・ご注文のキャンセル


また、【{ServiceName}】の与信審査につきましては、
【{ServiceName}】を運営しております(株)キャッチボールにて行っております。

与信審査結果に関する詳細の内容につきましては、個人情報を含む内容になりますため、
弊社から{SiteNameKj}へは一切開示しておりません。

与信審査結果に関するお問い合わせにつきましては、
直接お電話にて弊社へご連絡いただきますようお願い申し上げます。

【【{ServiceName}】カスタマーセンター】
運営会社：（株）キャッチボール
TEL:03-4326-3600
営業時間：9:00～18:00　年中無休(年末・年始をのぞく)

以上、よろしくお願い申し上げます。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2015/08/31 22:42:31',9,'2022/04/20 2:28:18',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (23,23,'パスワード情報お知らせメール','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】パスワード情報のお知らせ','{ServiceName}','{EnterpriseNameKj}　様

この度は弊社サービス【{ServiceName}】にお申込みいただき、
まことにありがとうございます。


決済管理システムのログインに必要な
パスワードをお知らせいたします。

PW：{GeneratedPassword}


以上でございます。


今後とも何卒、よろしくお願いいたします。

株式会社キャッチボール
　【{ServiceName}】　スタッフ一同

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------

',null,'2015/08/31 22:42:31',9,'2022/04/20 2:11:52',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (24,4,'請求書発行メール（PC）','株式会社Ｅストアー（後払い窓口）','=?UTF-8?B?GyRCM3Q8MDJxPFIjRSU5JUglIiE8IUo4ZUonJCRBazh9IUsbKEI=?=','ato-barai.sp@estore.co.jp',null,null,null,'【ご連絡】請求書を発行しました　（ハガキで届きます）','=?UTF-8?B?5qCq5byP5Lya56S+77yl44K544OI44Ki44O877yI5b6M5omV44GE56qT5Y+j?=
 =?UTF-8?B?77yJ?=','─────────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
─────────────────────────────────────

{CustomerNameKj}様


先日はご注文いただきまして、誠にありがとうございます。


下記のショッピングのご請求書を本日発行いたしますので、請求書到着後、
請求書に記載されているお支払期限日までにお支払いいただきますよう、
お願い申し上げます。


【ご注文内容】

お支払者：{CustomerNameKj}　様

ご購入店舗名：{SiteNameKj}　

ご購入日：{OrderDate}

お支払金額：{UseAmount}

ご購入商品明細：商品名／個数／購入品目計

{OrderItems}

決済手数料                              \{SettlementFee}

送料                                    \{DeliveryFee}


※郵送事故などにより、請求書が届かないことがございます。
一週間ほどお待ちいただいても請求書が届かない場合には、大変お手数ですが、
下記連絡先へご一報くださいますよう、お願い申し上げます。


※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
店舗様まで直接お問合せくださいませ。


※請求書並びに本メールが、商品よりも早く到着してしまうことがございますが、
何卒ご容赦下さいますようお願い申し上げます。
また、請求書内に明細が含まれておりますのでご確認くださいませ。


※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。


※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
配信されてしまう場合がございます。その際は大変お手数ですが、下記
購入店舗様に直接お問合せください。



■商品・返品・配送に関するお問い合わせは：

　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}



■お支払いに関するお問い合わせは：

  お問合せ先：03-6908-5100
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: ato-barai.sp@estore.co.jp
  運営会社：株式会社Ｅストアー　後払い窓口 
　住所：〒105-0003 東京都港区西新橋1-10-2　住友生命西新橋ビル9F
',1,'2015/08/31 22:42:31',9,'2015/12/01 11:47:58',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (25,5,'請求書発行メール（CEL）','株式会社Ｅストアー（後払い窓口）','=?UTF-8?B?GyRCM3Q8MDJxPFIjRSU5JUglIiE8IUo4ZUonJCRBazh9IUsbKEI=?=','ato-barai.sp@estore.co.jp',null,null,null,'【ご連絡】請求書（ハガキ）を発行しました','=?UTF-8?B?5qCq5byP5Lya56S+77yl44K544OI44Ki44O877yI5b6M5omV44GE56qT5Y+j?=
 =?UTF-8?B?77yJ?=','◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇


{CustomerNameKj}様


先日はご注文いただきまして、誠にありがとうございます。

下記のショッピングのご請求書を本日発行いたしますので、請求書到着後、
請求書に記載されているお支払期限日までにお支払いいただきますよう、
お願い申し上げます。
{OrderPageAccessUrl}

【ご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料  \{SettlementFee}
送料 \{DeliveryFee}

※郵送事故などにより、請求書が届かないことがございます。
一週間ほどお待ちいただいても請求書が届かない場合には、大変お手数ですが、
下記連絡先へご一報くださいますよう、お願い申し上げます。

※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
店舗様まで直接お問合せくださいませ。

※請求書並びに本メール、商品よりも早く到着してしまうことがございますが、
何卒ご容赦下さいますようお願い申し上げます。
また、請求書内に明細が含まれておりますのでご確認くださいませ。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
配信されてしまう場合がございます。その際は大変お手数ですが、下記
購入店舗様に直接お問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせください。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
03-6908-5100
 営業時間：9:00～18:00　年中無休（年末・年始のぞく）
 ato-barai.sp@estore.co.jp
 運営会社：株式会社Ｅストアー　後払い窓口
〒105-0003 東京都港区西新橋1-10-2　住友生命西新橋ビル9F',1,'2015/08/31 22:42:31',9,'2021/03/10 14:29:31',18008,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (26,6,'入金確認メール（PC）','株式会社Ｅストアー（後払い窓口）','=?UTF-8?B?GyRCM3Q8MDJxPFIjRSU5JUglIiE8IUo4ZUonJCRBazh9IUsbKEI=?=','ato-barai.sp@estore.co.jp',null,null,null,'【ご連絡】ご入金を確認しました','=?UTF-8?B?5qCq5byP5Lya56S+77yl44K544OI44Ki44O877yI5b6M5omV44GE56qT5Y+j?=
 =?UTF-8?B?77yJ?=','─────────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
─────────────────────────────────────

{CustomerNameKj}　様


先日はご注文いただきまして、誠にありがとうございます。

{ReceiptDate}に{CustomerNameKj}様からのご入金を
確認いたしましたのでご報告申し上げます。


以下が、今回ご入金いただいたご注文の内容となります。


【領収済みご注文内容】

お支払者：{CustomerNameKj}　様

ご購入店舗名：{SiteNameKj}　

ご購入日：{OrderDate}

お支払金額：{UseAmount}

ご購入商品明細：商品名／個数／購入品目計

{OrderItems}

決済手数料                              \{SettlementFee}

送料                                    \{DeliveryFee}



ご購入店舗名：{SiteNameKj}
ご連絡先：{Phone}
住所：{Address}


ご不明な点などございましたら、お気軽にお問い合わせください。
またのご利用を心より、お待ちしております。


※商品・返品・配送に関するお問い合わせは
直接購入店舗様にお問い合わせください。


※メールにてお問合せをいただく場合は、必ずご注文時のお名前
（フルネーム）を本文に入れてお問合せください。


■商品・返品・配送に関するお問い合わせは：

　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}


■お支払いに関するお問い合わせは：

  お問合せ先：03-6908-5100
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: ato-barai.sp@estore.co.jp
  運営会社：株式会社Ｅストアー　後払い窓口 
　住所：〒105-0003 東京都港区西新橋1-10-2　住友生命西新橋ビル9F',1,'2015/08/31 22:42:31',9,'2015/12/01 11:59:35',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (27,7,'入金確認メール（CEL）','株式会社Ｅストアー（後払い窓口）','=?UTF-8?B?GyRCM3Q8MDJxPFIjRSU5JUglIiE8IUo4ZUonJCRBazh9IUsbKEI=?=','ato-barai.sp@estore.co.jp',null,null,null,'【ご連絡】ご入金を確認しました','=?UTF-8?B?5qCq5byP5Lya56S+77yl44K544OI44Ki44O877yI5b6M5omV44GE56qT5Y+j?=
 =?UTF-8?B?77yJ?=','
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇


{CustomerNameKj}様
先日はご注文いただきまして、誠にありがとうございます。

{ReceiptDate}に{CustomerNameKj}様からのご入金を
確認いたしましたのでご報告申し上げます。

以下が、今回ご入金いただいたご注文の内容となります。

【領収済みご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料 \{SettlementFee}
送料       \{DeliveryFee}

ご購入店舗名：{SiteNameKj}
ご連絡先：{Phone}
住所：{Address}

ご不明な点などございましたら、お気軽にお問い合わせください。

※商品・返品・配送に関するお問い合わせは
直接購入店舗様にお問い合わせください。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前
（フルネーム）を本文に入れてお問合せください。

またのご利用を心より、お待ちしております。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせください。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
03-6908-5100
 営業時間：9:00～18:00　年中無休（年末・年始のぞく）
 ato-barai.sp@estore.co.jp
 運営会社：株式会社Ｅストアー　後払い窓口
〒105-0003 東京都港区西新橋1-10-2　住友生命西新橋ビル9F',1,'2015/08/31 22:42:31',9,'2015/12/01 12:55:11',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (28,11,'もうすぐお支払メール（PC）','株式会社Ｅストアー（後払い窓口）','=?UTF-8?B?GyRCM3Q8MDJxPFIjRSU5JUglIiE8IUo4ZUonJCRBazh9IUsbKEI=?=','ato-barai.sp@estore.co.jp',null,null,null,'【ご連絡】もうすぐお支払い期限です','=?UTF-8?B?5qCq5byP5Lya56S+77yl44K544OI44Ki44O877yI5b6M5omV44GE56qT5Y+j?=
 =?UTF-8?B?77yJ?=','─────────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
─────────────────────────────────────

{CustomerNameKj}様


先日はご注文いただきまして、誠にありがとうございます。


{IssueDate}に請求書をお送りいたしましたが、お手元にお届きでしょうか。
お送りした請求書のお支払期限日が近づいてまいりましたので、お知らせいたします。


※土日・祝祭日は入金の確認が取れない為、その間にお手続きいただいた場合、
入れ違いで当メールが送られてしまいます。
その場合は、まことに申し訳ございませんが、当メールを削除していただきますよう
お願い申し上げます。

（郵便局でお手続きいただいた場合、確認に最大4営業日かかる場合がございますので、
前日や前々日に御手続きいただいておりましても、同じように入れ違いで当メールが
届いてしまう場合がございます。）


お支払期限日：{LimitDate}

ご注文日：{OrderDate}

ご注文店舗：{SiteNameKj}

ご注文総額：{UseAmount}

商品名（1品目のみ表示）：{OneOrderItem}

請求日：{IssueDate}

 
まだお支払いいただいていない場合は、弊社よりお送りいたしました請求書を
ご確認のうえ、上記期限日までにお支払いいただきますよう、お願い申し上げます。


※期限を過ぎてしまいますと
再請求手数料が加算されますので、ご注意ください。


※下記口座へ直接お振込みいただきましても、ご入金の確認は取れます。
口座へお振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
ジャパンネット銀行
すずめ支店
普通預金　6291494
株式会社キャッチボール／Ｅストアー専用口座

【郵便振替口座】
口座記号：00140-5
口座番号：665145
株式会社　キャッチボール／Ｅストアー専用

※郵便局／銀行からお振込みいただく場合、振込手数料がお客様ご負担となります。

※郵便局の口座お持ちの場合は、郵便局のＡＴＭを利用して口座から
ご送金いただければ、郵便振込手数料はかかりません。(店舗決済手数料とは別です。)

万が一請求書がお手元に届いていない場合や、お支払に関しまして、ご不明な点等
ございましたら、下記までお気軽にお問い合わせくださいませ。

※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
店舗様まで直接お問合せくださいませ。


※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
配信されてしまう場合がございます。その際は大変お手数ですが、注文された
店舗様に直接お問合せください。


■商品・返品・配送に関するお問い合わせは：

　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：

  お問合せ先：03-6908-5100
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: ato-barai.sp@estore.co.jp
  運営会社：株式会社Ｅストアー　後払い窓口
  住所：〒105-0003　東京都港区西新橋1-10-2  住友生命西新橋ビル９F',1,'2015/08/31 22:42:31',9,'2015/12/01 12:03:09',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (29,12,'もうすぐお支払メール（CEL）','株式会社Ｅストアー（後払い窓口）','=?UTF-8?B?GyRCM3Q8MDJxPFIjRSU5JUglIiE8IUo4ZUonJCRBazh9IUsbKEI=?=','ato-barai.sp@estore.co.jp',null,null,null,'【ご連絡】もうすぐお支払い期限です','=?UTF-8?B?5qCq5byP5Lya56S+77yl44K544OI44Ki44O877yI5b6M5omV44GE56qT5Y+j?=
 =?UTF-8?B?77yJ?=','
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇


{CustomerNameKj}様


先日はご注文いただきまして、誠にありがとうございます。


{IssueDate}に請求書をお送りいたしましたが、お手元にお届きでしょうか。
お送りした請求書のお支払期限日が近づいてまいりましたので、お知らせいたします。


※土日・祝祭日は入金の確認が取れない為、その間にお手続きいただいた場合、
入れ違いで当メールが送られてしまいます。
その場合は、まことに申し訳ございませんが、当メールを削除していただきますよう
お願い申し上げます。

（郵便局でお手続きいただいた場合、確認に最大4営業日かかる場合がございますので、
前日や前々日に御手続きいただいておりましても、同じように入れ違いで当メールが
届いてしまう場合がございます。）


お支払期限日：{LimitDate}

ご注文日：{OrderDate}

ご注文店舗：{SiteNameKj}

ご注文総額：{UseAmount}

商品名（1品目のみ表示）：{OneOrderItem}

請求日：{IssueDate}

 
まだお支払いいただいていない場合は、弊社よりお送りいたしました請求書を
ご確認のうえ、上記期限日までにお支払いいただきますよう、お願い申し上げます。


※期限を過ぎてしまいますと
再請求手数料が加算されますので、ご注意ください。


※下記口座へ直接お振込みいただきましても、ご入金の確認は取れます。
口座へお振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
ジャパンネット銀行
すずめ支店
普通預金　6291494
株式会社キャッチボール／Ｅストアー専用口座

【郵便振替口座】
口座記号：00140-5
口座番号：665145
株式会社　キャッチボール／Ｅストアー専用

※郵便局／銀行からお振込みいただく場合、振込手数料がお客様ご負担となります。

※郵便局の口座お持ちの場合は、郵便局のＡＴＭを利用して口座から
ご送金いただければ、郵便振込手数料はかかりません。(店舗決済手数料とは別です。)

万が一請求書がお手元に届いていない場合や、お支払に関しまして、ご不明な点等
ございましたら、下記までお気軽にお問い合わせくださいませ。

※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
店舗様まで直接お問合せくださいませ。


※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
配信されてしまう場合がございます。その際は大変お手数ですが、注文された
店舗様に直接お問合せください。


■商品・返品・配送に関するお問い合わせは：

　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：

  お問合せ先：03-6908-5100
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: ato-barai.sp@estore.co.jp
  運営会社：株式会社Ｅストアー　後払い窓口
  住所：〒105-0003　東京都港区西新橋1-10-2  住友生命西新橋ビル９F',1,'2015/08/31 22:42:31',9,'2015/12/01 12:55:30',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (32,16,'戻り請求住所確認メール','株式会社Ｅストアー（後払い窓口）','=?UTF-8?B?GyRCM3Q8MDJxPFIjRSU5JUglIiE8IUo4ZUonJCRBazh9IUsbKEI=?=','ato-barai.sp@estore.co.jp',null,null,null,'【重要】ご住所確認のお願い（Ｅストアー後払い窓口）','=?UTF-8?B?5qCq5byP5Lya56S+77yl44K544OI44Ki44O877yI5b6M5omV44GE56qT5Y+j?=
 =?UTF-8?B?77yJ?=','─────────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
─────────────────────────────────────

{CustomerNameKj}様

先日はご注文いただきまして、誠にありがとうございます。

{ClaimDate}にお送りいたしました請求書が弊社に戻ってきておりますので、
ご住所の確認をさせていただきたくご連絡を差し上げました。


（お客様住所）　{UnitingAddress}


上記ご住所にお間違いはないでしょうか。

※住所に不備がなくても、表札氏名が違っていた場合は
郵便物が届かないケースがございます。
必ずお知らせくださいますようお願いいたします。

また、銀行・郵便局からのご入金も可能ですので
口座番号をお送りさせていただきます。

【銀行振込口座】
ジャパンネット銀行　すずめ支店
普通預金　6291494
株式会社キャッチボール／Ｅストアー専用口座

【郵便振替口座】
口座記号：00140-5
口座番号：665145
株式会社　キャッチボール／Ｅストアー専用

【ご請求明細】
商品名　　：{ItemNameKj}
商品代金　：{ItemAmount}円
送料　　　：{DeliveryFee}円
手数料　　：{SettlementFee}円
{OptionFee}

合計　　　：{UseAmount}円

その他ご不明な点、ご入金のご相談等は当社までお問い合わせください。

■商品・返品・配送に関するお問い合わせは：
　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
  お問合せ先：03-6908-5100
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: ato-barai.sp@estore.co.jp
  運営会社：株式会社Ｅストアー　後払い窓口
  住所：〒105-0003　東京都港区西新橋1-10-2  住友生命西新橋ビル９F',1,'2015/08/31 22:42:31',9,'2015/12/01 12:21:27',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (33,1,'事業者登録完了（サービス開始）メール','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'【後払い.com】 店舗審査通過のお知らせ','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}　様

この度は弊社サービス、【後払いドットコム】にお申込いただき、
まことにありがとうございます。

審査の結果、通過となりましたので、後払い決済管理システムを
ご利用いただくのに必要なIDを報告申し上げます。

重要なご案内となりますので、最後までお読みください。

【管理サイトＵＲＬ】

https://www.ato-barai.jp/smbcfs/member/

ID : {LoginId}


※パスワードは別途メールにてお送りさせていただきます。
※サイトＩＤは上記ＩＤとは異なりますのでご注意ください。
サイトＩＤの参照方法は以下の通りです。

【1】管理サイトにログイン
　　↓　↓　↓　↓
【2】「登録情報管理」をクリック
　　↓　↓　↓　↓
【3】「サイト情報」をクリック
　　↓　↓　↓　↓
【4】「サイトＩＤ」欄に表示されます。

 ■マニュアルのダウンロード（必須）
下記のURLより、【後払いドットコム】の運用マニュアルをダウンロード
してご使用下さい。
サービス開始に必要なマニュアルとなっておりますので、必ずご確認
いただきますようお願い申し上げます。

  https://www.ato-barai.jp/doc/help/Manual_SMBC.pdf

※閲覧にはAdobe PDF Reader が必要です。インストールされていない
方は、下記のURLより同ソフトのインストールをお願いいたします。

  http://www.adobe.com/jp/products/acrobat/readstep2.html

管理システムのご利用方法は、ダウンロードしていただいたマニュアル
をご確認ください。

サービスの開始まで、店舗様には以下のような作業をしていただきます。
開始のご連絡をお忘れなきよう、お願い申し上げます。

■■■　STEP 1　■■■登録内容のご確認

管理サイトにログイン、店舗情報を確認（プランその他の情報）

■■■　STEP 2　■■■定型文章のサイト掲載

マニュアルにしたがって、店舗様サイト上に当決済方法用の定型文章を掲載
（特定商取引法ページや決済選択画面など）

サイト掲載用定型文・画像提供ページ：

http://www.ato-barai.com/for_shops/s.tokuteishou.html

※この時点でサービス開始となります

■消費者様向け後払いドットコム動画＆販促バナーダウンロードページ
http://www.ato-barai.com/download/

消費者様向け動画は、初めて当決済をご利用になる消費者様にとって
分かり易くなり、お問合せを減らせる効果が期待できます。
さらに販促バナーは、後払い決済が出来るお店としてアピールできるため、
販促の効果にもつながりますので、こちらも併せてご活用ください。

■■■　STEP 3　■■■サービス開始の当社へのご通知

サービスを開始した旨を、当社までメールもしくはお電話にてご連絡下さい。
 mail: customer@ato-barai.com
 tel:  0120-667-690

■■■　STEP 4　■■■当社が決済画面を確認

当社担当が決済画面を確認させていただき、問題がなければそのまま運営、
問題があれば修正のお願いをさせていただくことがございます。

  ↑↑↑「流れ」はここまで

■消費者様への請求書のご案内用紙のダウンロード（任意）
下記のＵＲＬより請求書のご案内用紙をダウンロードして、商品に同梱
してください。
（ご案内用紙の同梱は店舗様のご判断による任意で行っていただいて
　おりますが、初めて当決済をご利用なる消費者様にとっては分かり易く
　なり、お問合せが減ることにも繋がりますので、同梱していただくこと
　を推奨しております。）

https://www.atobarai.jp/doc/download/doukonnyou.xls


サービス開始に当たって、また、運営に関するお問い合わせ等は、
メール末尾のご連絡先にお気軽にお問合せ下さい。

＊＊＊＊＊＊＊＊＊＊＊＊【注意事項】＊＊＊＊＊＊＊＊＊＊＊＊

１）以下に該当するご注文は、保証対象外となってしまいますので
　　ご注意ください。

※保証外とは、未払いの保証が付かず、消費者様からの入金が
　ない限りは店舗様へ入金させていただく事ができません。
　
・商品発送時に、メール便や定形外郵便等の、受領印又は
　受取りのサインが無い配送方法にて商品を発送されたご注文
・Web上にてお荷物の追跡ができない配送方法を使われたご注文
・伝票登録時に配送会社や伝票番号を誤った情報で登録されたご注文
・配達状況がキャンセル・持ち戻り等により配達完了の確認が
　とれないご注文
・実際に発送された配送方法に関わらず、伝票登録時の配送方法に
　【メール便】を選択して登録されたご注文
・紛争性があるご注文

２）配送伝票番号をご登録いただいた、当日又は、翌営業日に
　　ご注文者様に対して、ご請求書が発送されます。
※商品発送前に配送伝票番号をご登録いただきますと、請求書が商品
　より先に届いてしまう可能性が高くなりますので、商品発送後に
　配送伝票番号のご登録をお願いいたします。

３）締日までに弊社側で商品の着荷確認がとれたご注文分が
　　当該締日分の立替対象となります。
※伝票番号登録日や、配達完了日ではなく、弊社側で着荷確認が
　とれた日がベースとなりますのでご注意ください。

４）新たにウェブサイトまたはカタログ等で後払いドットコムのサービスを
    ご利用いただく場合、もしくは新たな商品を販売する場合、
    新たなサービスをご提供される場合は事前にご連絡くださいますよう
    お願い申しあげます。 
※未審査のものは、後払いドットコムのサービスはご利用いただけません。

＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊


今後とも末永いお付き合いの程、宜しくお願い申し上げます。

株式会社キャッチボール　後払いドットコム事業部　スタッフ一同

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：0120-667-690
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2022/07/03 15:54:24',63,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (34,2,'注文登録（与信開始）メール','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'【後払い.com】与信開始のお知らせ（{OrderCount}件）','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj} 様

いつも【後払いドットコム】をご利用いただき、まことにありがとうございます。
以下のご注文を受け付けいたしました。
これより与信に入りますので、商品をまだ発送されないようご注意下さい。

受付注文件数：{OrderCount}件

ご注文者名（ご請求総額）
--------------------------------------------------------------
{OrderSummary}
--------------------------------------------------------------
上記お取引の与信完了後に、与信完了メールを送信いたします。


※18:00以降の与信は、通常翌日11:00までの回答となりますのでご注意下さい。
※注文により与信にかかる時間が異なる場合がございます。その場合、与信結果が
出たものから自動で与信完了メールが送信されますので、あらかじめご了承下さい。

■■■■■■■■■■■　キャンセルが発生した場合　■■■■■■■■■■■

ご登録された注文のキャンセルが入った場合は、お手数ですが「履歴検索」から
ご注文を検索し、該当のお取引をクリックしてキャンセル処理を行って下さい。

■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

【管理画面ＵＲＬ】
https://www.ato-barai.jp/smbcfs/member/

ご不明な点などございましたら、お気軽にお問い合わせ下さい。

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：0120-667-690
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (35,3,'与信完了メール','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払い.com】与信完了のお知らせ：計{CreditCount}件（うちNG{NgCount}件）','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}　様

いつも【後払いドットコム】をご利用いただき、まことにありがとうございます。

与信件数：{CreditCount} 件

の与信結果が出ましたのでご報告いたします。

【管理画面ＵＲＬ】
https://www.ato-barai.jp/smbcfs/member/

※与信がNGのご注文であっても、NG理由によっては、無保証にて「OK」に変更できる場合がございます。
無保証で後払いサービスご希望の方は以下に記載の【NG理由による処理方法について】を参考にしてください。
（無保証でも「OK」に変更できない場合もございますので、弊社からの
返信メールをご確認いただいてから、商品発送などを行ってください。）

{Orders}

【OK案件の処理】
与信が通過したお取引に関しましては、

1.商品の発送
2.配送伝票番号登録

にお進み下さい。

【NG理由による処理方法について】
※ NG理由が「長期遅延歴」「高額保留」「無保証変更可能」の場合
無保証での後払いサービスへ 切り替えて頂くことが可能です。
無保証に変更する場合は、このメールより{OutOfAmendsDays}日以内に後払い決済管理システムに
ログイン後に操作を実施してください。

※ 上記以外のNG理由の場合
その他のNG理由のお取引に関しましては、お早めにご購入者様に他の決済方法のご選択を
いただくなどのご対応をお願いいたします。

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：0120-667-690
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿6-14-1　新宿グリーンタワー14階 

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (36,4,'請求書発行メール（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払い.com】請求書発行案内　（ハガキで届きます）','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払いドットコム】をご利用いただきまして、
まことにありがとうございます。

下記のショッピングのご請求書を本日発行いたしますので、請求書到着後、
請求書に記載されているお支払期限日までにお支払いいただきますよう、
お願い申し上げます。

{OrderPageAccessUrl}

【ご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料                              \{SettlementFee}
送料                                    \{DeliveryFee}

※郵送事故などにより、請求書が届かないことがございます。
　一週間ほどお待ちいただいても請求書が届かない場合には、大変お手数ですが、
　下記連絡先へご一報くださいますよう、お願い申し上げます。

※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
　届かない場合がございます。
　万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
　店舗様まで直接お問合せくださいませ。

※請求書並びに本メールが、商品よりも早く到着してしまうことがございますが、
何卒ご容赦下さいますようお願い申し上げます。
また、請求書内に明細が含まれておりますのでご確認くださいませ。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、下記
　購入店舗様に直接お問合せください。

※※※その他ご不明な点は下記ＵＲＬをご確認ください。※※※

　　　  http://www.ato-barai.com/guidance/faq.html

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
株式会社キャッチボール　後払いドットコム事業部
TEL:03-5332-3490(平日土日9:00～18:00)
Mail: customer@ato-barai.com

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2017/12/26 15:13:50',59,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (37,5,'請求書発行メール（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払い.com】請求書発行案内　（ハガキで届きます）','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払いドットコム】をご利用いただきまして、
まことにありがとうございます。

下記のショッピングのご請求書を本日発行いたしますので、請求書到着後、
請求書に記載されているお支払期限日までにお支払いいただきますよう、
お願い申し上げます。

【ご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料  \{SettlementFee}
送料 \{DeliveryFee}

※郵送事故などにより、請求書が届かないことがございます。
　一週間ほどお待ちいただいても請求書が届かない場合には、大変お手数ですが、
　下記連絡先へご一報くださいますよう、お願い申し上げます。

※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
　届かない場合がございます。
　万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
　店舗様まで直接お問合せくださいませ。

※請求書並びに本メール、商品よりも早く到着してしまうことがございますが、
何卒ご容赦下さいますようお願い申し上げます。
また、請求書内に明細が含まれておりますのでご確認くださいませ。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、下記
　購入店舗様に直接お問合せください。

※※※その他ご不明な点は下記ＵＲＬをご確認ください。※※※

　　　  http://www.ato-barai.com/guidance/faq.html


■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

■お支払いに関するお問い合わせは：
株式会社キャッチボール　後払いドットコム事業部
住所：〒160-0023 東京都新宿区西新宿7-8-2 福八ビル 4F
TEL:03-5332-3490(平日土日9:00～18:00)
Mail: customer@ato-barai.com
URL: http://www.ato-barai.com（パソコン専用）

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail:customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (38,6,'入金確認メール（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】ご入金確認のご報告','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 
{CustomerNameKj}　様

この度は、{SiteNameKj}様でのお買い物に、
【後払いドットコム】をご利用いただきまして、
まことにありがとうございます。

{ReceiptDate}に{CustomerNameKj}様からのご入金を
確認いたしましたのでご報告申し上げます。

以下が、今回ご入金いただいたご注文の内容となります。

【領収済みご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料                              \{SettlementFee}
送料                                    \{DeliveryFee}

ご購入店舗名：{SiteNameKj}
ご連絡先：{Phone}
住所：{Address}


※商品・返品・配送に関するお問い合わせは
直接購入店舗様にお問い合わせ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前
（フルネーム）を本文に入れてお問合せください。

※※※その他ご不明な点は下記ＵＲＬをご確認ください。※※※

　　　 http://www.ato-barai.com/guidance/faq.html


またのご利用を心より、お待ちしております。

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (39,7,'入金確認メール（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】ご入金確認のご報告','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}　様

この度は、{SiteNameKj}様でのお買い物に、
【後払いドットコム】をご利用いただきまして、
まことにありがとうございます。

{ReceiptDate}に{CustomerNameKj}様からのご入金を
確認いたしましたのでご報告申し上げます。

以下が、今回ご入金いただいたご注文の内容となります。

【領収済みご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料 \{SettlementFee}
送料       \{DeliveryFee}

ご購入店舗名：{SiteNameKj}
ご連絡先：{Phone}
住所：{Address}


※商品・返品・配送に関するお問い合わせは
直接購入店舗様にお問い合わせ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前
（フルネーム）を本文に入れてお問合せください。

※※※その他ご不明な点は下記ＵＲＬをご確認ください。※※※

　　　 http://www.ato-barai.com/guidance/faq.html


またのご利用を心より、お待ちしております。

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (40,8,'立替完了メール','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'【後払い.com】立替金お支払いのご報告','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj} 様

いつも【後払いドットコム】をご利用いただきまして、
まことにありがとうございます。

立替分のお支払いが完了いたしましたので、
報告申し上げます。

【管理画面ＵＲＬ】
https://www.ato-barai.jp/smbcfs/member/

お支払サイト　　：　{FixedPattern}
立替締日　　　　：　{FixedDate}
振込実行日　　　：　{ExecDate}
お支払額　　　　：　{DecisionPayment}円
決済手数料　　　：　{SettlementFee}円
請求手数料　　　：　{ClaimFee}円
印紙代合計　　　：　{StampFee}円
キャンセル返金　：　{CancelAmount}円
月額固定費　　　：　{MonthlyFee}円
お振込み手数料　：　{TransferCommission}円

お支払に関しましてご不明な点などございましたら、
下記連絡先までお気軽にお問い合わせ下さいませ。

今後とも弊社サービス【後払いドットコム】を、よろしく
お願い申し上げます。

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：0120-667-690
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (41,9,'キャンセル確認メール','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'【後払い.com】キャンセル確定のご報告({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}　様

いつも【後払いドットコム】をご利用いただきまして、まことにありがとうございます。

以下のご注文のキャンセルを承りましたので、ご確認下さい。
また、キャンセルのタイミングによって、その後の消費者様への対応が異なります
のでご注意下さい。（※以下の【1】～【4】をご参照下さい。）

【管理画面ＵＲＬ】
https://www.atobarai.jp/member/

【キャンセル確定情報】
キャンセル区分：{CancelPhase}
ご注文ID：{OrderId}
請求先氏名：{CustomerNameKj}　様
ご注文総額：{UseAmount}
ご注文日：{OrderDate}

【1】未立替案件のキャンセル
返金等は発生しません。請求書がすでに発送されている場合は、お客様
に請求書破棄のお願いをご連絡をお願い申し上げます。

【2】立替済み案件のキャンセル
次回立替時に、立替済みの金額を、相殺により返金させていただきます。
店舗様側での作業は必要ございません。また、決済手数料も発生いたしません。

【3】立替済み・お客様ご入金済み案件のキャンセル
後ほど当社より店舗様に連絡をさせていただきますので、その後にお客様へ、
商品代金を店舗様よりご返金いただくことになります。
決済手数料は発生いたしませんので、次回立替時に手数料を返金いたします。

【4】未立替え・お客様入金済み案件のキャンセル
後ほど当社より店舗様に連絡をさせていただきますので、その後にお客様へ、
商品代金を店舗様よりご返金いただくことになります。
また、お客様からのご入金分を次回立替時に当社より店舗様へ返金させていた
だきます。この場合も決済手数料は発生いたしません。

※お客様が店舗様の口座へ直接入金された場合や、店舗様が誤って代引きで
　発送された場合等の、お客様と店舗様間でのお取引が成立している場合の
　キャンセル処理の際には、上記【1】～【4】のいずれの場合も所定の手数料
　を次回立替時の調整額にて徴収させていただきます。

ご不明な点などございましたら、お気軽に当社までお問い合わせ下さい。

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：0120-667-690
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (42,10,'アドレス確認メール','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】のご選択ありがとうございます','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{CustomerNameKj}様

この度は、お支払方法に【後払いドットコム】をご選択いただき、
まことにありがとうございます。

ただいま、下記のご注文におきまして後払い.comをご利用いただけるか、
審査をいたしております。

【ご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料  \{SettlementFee}
送料        \{DeliveryFee}


結果につきましては、ご注文いただきました店舗様より、
後ほどご連絡が入りますので、もう少々お待ち下さいませ。

ご不明な点などございましたら、お気軽にお問い合わせ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (43,11,'もうすぐお支払メール（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】もうすぐお支払い期限です','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}様


この度は、{SiteNameKj}様でのお買い物に、
【後払いドットコム】をご利用いただきまして、まことにありがとうございます。
{IssueDate}に請求書をお送りいたしましたが、お手元にお届きでしょうか。

お送りした請求書のお支払期限日が近づいてまいりましたので、お知らせいたします。

※土日・祝祭日は入金の確認が取れない為、その間に御手続きいただいた場合、
入れ違いで当メールが送られてしまいます。
その場合は、まことに申し訳ございませんが、当メールを削除していただきますよう
お願い申し上げます。
（郵便局で御手続きいただいた場合、確認に最大4営業日かかる場合がございますので、
前日や前々日に御手続きいただいておりましても、同じように入れ違いで当メールが
届いてしまう場合がございます。）

お支払期限日：{LimitDate}
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
ご注文総額：{UseAmount}
商品名（1品目のみ表示）：{OneOrderItem}
請求日：{IssueDate}
 
まだお支払いいただいていない場合は、弊社よりお送りいたしました請求書を
ご確認のうえ、上記期限日までにお支払いいただきますよう、お願い申し上げます。

※期限を過ぎてしまいますと、再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましても、ご入金の確認は取れます。
口座へお振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}


【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

※郵便局／銀行からお振込みいただく場合、振込手数料がお客様ご負担となります。

※郵便局の口座お持ちの場合は、郵便局のＡＴＭを利用して口座からご送金いただ
ければ、郵便振込手数料はかかりません。(店舗決済手数料とは別です。)

※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
店舗様まで直接お問合せくださいませ。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、注文された
　店舗様に直接お問合せください。

※万が一請求書がお手元に届いていない場合や、お支払に関しまして、ご不明な点等
　ございましたら、下記ＵＲＬをご確認ください。

　http://www.ato-barai.com/guidance/faq.html

今後とも当社サービス【後払いドットコム】をよろしくお願い申し上げます。

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/12/01 14:00:41',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (44,12,'もうすぐお支払メール（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】もうすぐお支払い期限です','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}様


この度は、{SiteNameKj}様でのお買い物に、
【後払いドットコム】をご利用いただきまして、まことにありがとうございます。
{IssueDate}に請求書をお送りいたしましたが、お手元にお届きでしょうか。

お送りした請求書のお支払期限日が近づいてまいりましたので、お知らせいたします。

※土日・祝祭日は入金の確認が取れない為、その間に御手続きいただいた場合、
入れ違いで当メールが送られてしまいます。
その場合は、まことに申し訳ございませんが、当メールを削除していただきますよう
お願い申し上げます。
（郵便局で御手続きいただいた場合、確認に最大4営業日かかる場合がございますので、
前日や前々日に御手続きいただいておりましても、同じように入れ違いで当メールが
届いてしまう場合がございます。）

お支払期限日：{LimitDate}
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
ご注文総額：{UseAmount}
商品名（1品目のみ表示）：{OneOrderItem}
請求日：{IssueDate}
 
まだお支払いいただいていない場合は、弊社よりお送りいたしました請求書を
ご確認のうえ、上記期限日までにお支払いいただきますよう、お願い申し上げます。

※期限を過ぎてしまいますと、再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましても、ご入金の確認は取れます。
口座へお振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。


【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}


【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

※郵便局／銀行からお振込みいただく場合、振込手数料がお客様ご負担となります。

※郵便局の口座お持ちの場合は、郵便局のＡＴＭを利用して口座からご送金いただ
ければ、郵便振込手数料はかかりません。(店舗決済手数料とは別です。)

※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
店舗様まで直接お問合せくださいませ。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、注文された
　店舗様に直接お問合せください。

※万が一請求書がお手元に届いていない場合や、お支払に関しまして、ご不明な点等
　ございましたら、下記ＵＲＬをご確認ください。

　http://www.ato-barai.com/guidance/faq.html

今後とも当社サービス【後払いドットコム】をよろしくお願い申し上げます。

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/12/01 14:01:01',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (45,13,'お支払い未確認メール（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'後払い.com：{OrderDate}　{SiteNameKj}でのお買い物の件','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}様


{OrderDate}に{SiteNameKj}様でのお買い物に、
後払いドットコムをご利用いただきありがとうございます。

{IssueDate}に請求書をお送りいたしましたが、本日現在ご入金の確認ができて
おりません。

※土日・祝祭日は入金の確認が取れない為、その間に御手続きいただいた場合、
入れ違いで当メールが送られてしまいます。
その場合は、まことに申し訳ございませんが、当メールを削除していただきますよう
お願い申し上げます。
（郵便局で御手続きいただいた場合、確認に最大4営業日かかる場合がございますので、
前日や前々日に御手続きいただいておりましても、同じように入れ違いで当メールが
届いてしまう場合がございます。）

まだお支払いいただいていない場合は、コンビニエンスストア、郵便局または銀行
よりお支払いくださいませ。

※再請求手数料が加算されますので、お早めにご連絡または、
ご入金いただきますよう、お願い申し上げます。

【ご請求明細】
商品名（一品目のみ表示）：{OneOrderItem}　他
小計（送料・手数料含む）：{UseAmount}円
再請求手数料：{ReClaimFee}円
遅延損害金：{DamageInterest}円(前回請求書発行日時点)
その他：{InstPlanAmount}円
合計：{TotalAmount2}円

※下記口座へ直接お振込みいただきましても、ご入金の確認は取れます。
口座へお振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

※郵便局／銀行からお振込みいただく場合、振込手数料はお客様ご負担となります。

※郵便局の口座お持ちの場合は、郵便局のＡＴＭを利用して口座からご送金いただ
ければ、郵便振込手数料はかかりません。(店舗決済手数料とは別です。)

【銀行振込口座】
三井住友銀行　
新宿通支店　
普通口座　8047001
カ）キャッチボール

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール


※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
店舗様まで直接お問合せくださいませ。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、注文された
　店舗様に直接お問合せください。

※万が一請求書がお手元に届いていない場合や、お支払に関しまして、ご不明な点等
  ございましたら、下記ＵＲＬをご確認ください。

  http://www.ato-barai.com/guidance/faq.html

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/12/01 13:58:09',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (46,14,'お支払い未確認メール（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'後払い.com：{OrderDate}　{SiteNameKj}でのお買い物の件','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}様



{OrderDate}に{SiteNameKj}様でのお買い物に、
後払いドットコムをご利用いただきありがとうございます。

{IssueDate}に請求書をお送りいたしましたが、本日現在ご入金の確認ができて
おりません。

※土日・祝祭日は入金の確認が取れない為、その間に御手続きいただいた場合、
入れ違いで当メールが送られてしまいます。
その場合は、まことに申し訳ございませんが、当メールを削除していただきますよう
お願い申し上げます。
（郵便局で御手続きいただいた場合、確認に最大4営業日かかる場合がございますので、
前日や前々日に御手続きいただいておりましても、同じように入れ違いで当メールが
届いてしまう場合がございます。）

まだお支払いいただいていない場合は、コンビニエンスストア、郵便局または銀行
よりお支払いくださいませ。

※再請求手数料が加算されますので、お早めにご連絡または、
ご入金いただきますよう、お願い申し上げます。

【ご請求明細】
商品名（一品目のみ表示）：{OneOrderItem}　他
小計（送料・手数料含む）：{UseAmount}円
再請求手数料：{ReClaimFee}円
遅延損害金：{DamageInterest}円(前回請求書発行日時点)
その他：{InstPlanAmount}円
合計：{TotalAmount2}円

※下記口座へ直接お振込みいただきましても、ご入金の確認は取れます。
口座へお振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

※郵便局／銀行からお振込みいただく場合、振込手数料はお客様ご負担となります。

※郵便局の口座お持ちの場合は、郵便局のＡＴＭを利用して口座からご送金いただ
ければ、郵便振込手数料はかかりません。(店舗決済手数料とは別です。)

【銀行振込口座】
三井住友銀行　
新宿通支店　
普通口座　8047001
カ）キャッチボール

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール


※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
店舗様まで直接お問合せくださいませ。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、注文された
　店舗様に直接お問合せください。

※万が一請求書がお手元に届いていない場合や、お支払に関しまして、ご不明な点等
  ございましたら、下記ＵＲＬをご確認ください。

  http://www.ato-barai.com/guidance/faq.html

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/12/01 13:58:21',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (47,15,'伝票番号確認のお願い','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'【後払い.com】伝票番号のご確認をお願いします','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}
{CpNameKj} 様

お世話になっております。【後払いドットコム】カスタマーセンターです。

{ReceiptOrderDate}にご注文登録いただきました、下記お客様の着荷確認が
取れない為、現状立替をさせていただくことができておりません。

ご登録頂きました、配送伝票番号に入力ミスがあるか、
商品がお客様に届いていない可能性がございます。

商品の配送会社、配送伝票番号、並びに配送状況を
個人情報の兼ね合いもございますので
お手数ですが一度店舗様側でご確認いただき、
店舗様管理サイト上から修正していただきたく思います。
※編集方法は履歴検索から特定のお客様を絞り込んでいただき、
『登録内容の修正』から修正をおこなって下さい。

お取引ID ：{OrderId}
ご注文者様名 ：{CustomerNameKj} 様
伝票番号登録日 ：{Deli_JournalIncDate}
登録伝票番号 ：{Deli_JournalNumber}

尚、長期間に渡りご変更をいただけず、配送会社の追跡サービスにて
着荷の確認が取れなくなってしまった場合、無保証扱いとなりますので
ご注意ください。

ご不明点などございましたら、弊社フリーダイヤル（0120-667-690）まで
ご連絡いただければと思います。
何卒よろしくお願いいたします。


--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：0120-667-690
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (48,16,'戻り請求住所確認メール','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'後払い.com：【重要】ご住所確認の連絡です。','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
返信の際のお願い：お客様への正確なご案内提供のため、ご返信いただく
際は引用返信または、文頭にご注文者様の氏名のご記入をお願い致します。
────────────────────────────────── 

{CustomerNameKj}様

{ReceiptOrderDate}に{SiteNameKj}で、
後払いドットコム決済を選択していただきありがとうございます。
{ClaimDate}にお送りいたしました請求書が弊社に戻ってきておりますので、
ご住所の確認をさせていただきたくご連絡させていただきました。

（お客様住所）　{UnitingAddress}

上記住所に不備がありましたら、再度請求書を発行させていただきますので
ご連絡の程、よろしくお願い致します。

住所に不備がない場合でも、表札氏名が違っていた場合などで、郵便物が届かないケースも
ありますので、ご了承下さい。

また、銀行、郵便局からのご入金も可能ですので
口座番号をお送りさせていただきます。

【銀行振込口座】
三井住友銀行　新宿通支店　カ）キャッチボール
普通口座　8047001

【郵便振替口座】
口座番号：00120-7
口座番号：670031
カ）キャッチボール


【ご請求明細】
商品名　　：{ItemNameKj}
商品代金　：{ItemAmount}円
送料　　　：{DeliveryFee}円
手数料　　：{SettlementFee}円
{OptionFee}
合計　　　：{UseAmount}円

その他ご不明な点、ご入金のご相談等は当社までお問い合わせください。

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (49,17,'請求書発行メール（同梱ツール向け：PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払い.com】請求書発行案内','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

 {CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払いドットコム】をご利用いただきまして、
まことにありがとうございます。

下記のショッピングの請求書を本日発行いたしました。
商品に同梱されている請求書に記載のお支払期限日までに
お支払いいただきますよう、お願い申し上げます。

※当メールは、「商品発送のお知らせメール」ではございません。
　請求書を印刷した時点でメールをお送りしております関係で、
　請求書の発行日と、商品の発送日が異なる場合がございますので、
　予めご了承くださいませ。

【ご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料                              \{SettlementFee}
送料                                    \{DeliveryFee}

※商品と共に請求書が入っていない場合には、大変お手数ですが、
　下記連絡先へご一報くださいますよう、お願い申し上げます。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、下記
　購入店舗様に直接お問合せください。

※※※その他ご不明な点は下記ＵＲＬをご確認ください。※※※

       http://www.ato-barai.com/guidance/faq.html

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
株式会社キャッチボール　後払いドットコム事業部
TEL:03-5332-3490(平日土日9:00～18:00)
Mail: customer@ato-barai.com

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (50,18,'請求書発行メール（同梱ツール向け：CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払い.com】請求書発行案内','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払いドットコム】をご利用いただきまして、
まことにありがとうございます。

下記のショッピングの請求書を本日発行いたしました。
商品に同梱されている請求書に記載のお支払期限日までに
お支払いいただきますよう、お願い申し上げます。

※当メールは、「商品発送のお知らせメール」ではございません。
　請求書を印刷した時点でメールをお送りしております関係で、
　請求書の発行日と、商品の発送日が異なる場合がございますので、
　予めご了承くださいませ。

【ご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料  \{SettlementFee}
送料 \{DeliveryFee}

※商品と共に請求書が入っていない場合には、大変お手数ですが、
　下記連絡先へご一報くださいますよう、お願い申し上げます。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、下記
　購入店舗様に直接お問合せください。

※※※その他ご不明な点は下記ＵＲＬをご確認ください。※※※

       http://www.ato-barai.com/guidance/faq.html

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

■お支払いに関するお問い合わせは：
株式会社キャッチボール　後払いドットコム事業部
住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
TEL:03-5332-3490(平日土日9:00～18:00)
Mail: customer@ato-barai.com
URL: http://www.ato-barai.com（パソコン専用）

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (51,19,'与信結果メール(OK, PC)','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'与信結果のお知らせ','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
返信の際のお願い：お客様への正確なご案内提供のため、ご返信いただく
際は引用返信または、文頭にご注文者様の氏名のご記入をお願い致します。
────────────────────────────────── 

{CustomerNameKj}様

この度は{SiteNameKj}で{OneOrderItem}をご注文された際のお支払い方法に、後払いドットコムを御選択頂きまして、まことにありがとうございます。

後払い決済の与信審査が問題なく通過いたしましたのでご報告申し上げます。

ご注文頂きました商品についてのお問い合わせ・ご注文内容のご変更、ご注文のキャンセル等に関しましては、{SiteNameKj}での対応となりますので、直接ご連絡して頂きますようお願い申し上げます。

【{SiteNameKj}】
{ContactPhoneNumber}
{MailAddress}

尚、請求書の発行については弊社よりメールをお送り致しますので
ご確認ください。

後払い決済に関してご不明な点などございましたら、下記の後払いドットコムカスタマーセンターへお問い合わせください。

【後払いドットコムカスタマーセンター】
運営会社：（株）キャッチボール
TEL:03-5332-3490
',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (52,20,'与信結果メール(OK, CEL)','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'与信結果のお知らせ','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
返信の際のお願い：お客様への正確なご案内提供のため、ご返信いただく
際は引用返信または、文頭にご注文者様の氏名のご記入をお願い致します。
────────────────────────────────── 

{CustomerNameKj}様

この度は{SiteNameKj}で{OneOrderItem}をご注文された際のお支払い方法に、後払いドットコムを御選択頂きまして、まことにありがとうございます。

後払い決済の与信審査が問題なく通過いたしましたのでご報告申し上げます。

ご注文頂きました商品についてのお問い合わせ・ご注文内容のご変更、ご注文のキャンセル等に関しましては、{SiteNameKj}での対応となりますので、直接ご連絡して頂きますようお願い申し上げます。

【{SiteNameKj}】
{ContactPhoneNumber}
{MailAddress}

尚、請求書の発行については弊社よりメールをお送り致しますので
ご確認ください。

後払い決済に関してご不明な点などございましたら、下記の後払いドットコムカスタマーセンターへお問い合わせください。

【後払いドットコムカスタマーセンター】
運営会社：（株）キャッチボール
TEL:03-5332-3490
',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (53,21,'与信結果メール(NG, PC)','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'与信結果のお知らせ','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{CustomerNameKj}様

この度は{SiteNameKj}で{OneOrderItem}をご注文された際のお支払い方法に、後払いドットコムを御選択頂きまして、まことにありがとうございます。

与信審査の結果、今回の御注文につきまして、後払い決済の与信審査が通過いたしませんでした事をご報告申し上げます。

大変お手数ではございますが、{SiteNameKj}へご連絡の上、他のお支払い方法にご変更頂きたいと存じます。

【{SiteNameKj}】
{ContactPhoneNumber}
{MailAddress}

ご注文頂きました商品についてのお問い合わせ・ご注文内容のご変更、ご注文のキャンセル等に関しましても、{SiteNameKj}での対応となりますので、直接ご連絡して頂きますようお願い申し上げます。

お支払い方法をご変更頂いた場合、後払い決済に関する手数料は一切発生いたしません。

後払い決済の与信審査につきましては、後払いドットコムを運営しております（株）キャッチボールにて行っております。与信審査結果の理由などにつきましては、個人情報を含む内容になる為、当社から{SiteNameKj}へは一切開示いたしておりません。つきましては、与信審査結果の理由などのお問い合わせに関しましては、直接、お電話にて当社へ御連絡頂きますようお願い申し上げます。

【後払いドットコムカスタマーセンター】
運営会社：（株）キャッチボール
TEL:03-5332-3490
営業時間:10:00～18:00　年中無休(年末・年始のぞく)

与信審査に関しましては、ご本人様よりご連絡頂きますと再審査も可能でございます。
後払いの審査条件といたしまして、ご本人様確認が取れることが必須となっております。
そのため、ご住所やお電話番号の不備、もしくはお知り合いやご親戚の方とご同居で、ご住所やお電話番号のお名義がご注文者様と異なる場合(苗字が違う場合)など、与信審査が通過いたしません。
法人用・店舗用を個人名義にてご注文された場合も同様でございます。

もしお心当たりがあり修正が可能な場合には、再度与信審査をいたしますので、
上記の後払いドットコムカスタマーセンターまで御連絡いただきますよう、お願い申し上げます。

お手数をおかけいたしまして、まことに恐縮ではございますが、ご対応の程よろしくお願い申し上げます。
',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (54,22,'与信結果メール(NG, CEL)','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'与信結果のお知らせ','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{CustomerNameKj}様

この度は{SiteNameKj}で{OneOrderItem}をご注文された際のお支払い方法に、後払いドットコムを御選択頂きまして、まことにありがとうございます。

与信審査の結果、今回の御注文につきまして、後払い決済の与信審査が通過いたしませんでした事をご報告申し上げます。

大変お手数ではございますが、{SiteNameKj}へご連絡の上、他のお支払い方法にご変更頂きたいと存じます。

【{SiteNameKj}】
{ContactPhoneNumber}
{MailAddress}

ご注文頂きました商品についてのお問い合わせ・ご注文内容のご変更、ご注文のキャンセル等に関しましても、{SiteNameKj}での対応となりますので、直接ご連絡して頂きますようお願い申し上げます。

お支払い方法をご変更頂いた場合、後払い決済に関する手数料は一切発生いたしません。

後払い決済の与信審査につきましては、後払いドットコムを運営しております（株）キャッチボールにて行っております。与信審査結果の理由などにつきましては、個人情報を含む内容になる為、当社から{SiteNameKj}へは一切開示いたしておりません。つきましては、与信審査結果の理由などのお問い合わせに関しましては、直接、お電話にて当社へ御連絡頂きますようお願い申し上げます。

【後払いドットコムカスタマーセンター】
運営会社：（株）キャッチボール
TEL:03-5332-3490
営業時間:10:00～18:00　年中無休(年末・年始のぞく)

与信審査に関しましては、ご本人様よりご連絡頂きますと再審査も可能でございます。
後払いの審査条件といたしまして、ご本人様確認が取れることが必須となっております。
そのため、ご住所やお電話番号の不備、もしくはお知り合いやご親戚の方とご同居で、ご住所やお電話番号のお名義がご注文者様と異なる場合(苗字が違う場合)など、与信審査が通過いたしません。
法人用・店舗用を個人名義にてご注文された場合も同様でございます。

もしお心当たりがあり修正が可能な場合には、再度与信審査をいたしますので、
上記の後払いドットコムカスタマーセンターまで御連絡いただきますよう、お願い申し上げます。

お手数をおかけいたしまして、まことに恐縮ではございますが、ご対応の程よろしくお願い申し上げます。
',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (55,23,'パスワード情報お知らせメール','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'【後払い.com】 パスワード情報のお知らせ','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}　様

この度は弊社サービス、【後払いドットコム】にお申込いただき、
まことにありがとうございます。

後払い決済管理システムにログインしていただく為に必要な
パスワードをお送りさせていただきます。

PW :{GeneratedPassword}

サービス開始に当たって、また、運営に関するお問い合わせ等は、
メール末尾のご連絡先にお気軽にお問合せ下さい。


今後とも末永いお付き合いの程、宜しくお願い申し上げます。

株式会社キャッチボール　後払いドットコム事業部　スタッフ一同

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：0120-667-690
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (56,1,'事業者登録完了（サービス開始）メール','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】 店舗審査通過のお知らせ','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{EnterpriseNameKj}　様

この度は弊社サービス、【後払い決済サービス】にお申込いただき、
まことにありがとうございます。
 
審査の結果、通過となりましたので、後払い決済管理システムを
ご利用いただくのに必要なIDとパスワードを併せて報告申し上げます。

【管理サイトＵＲＬ】
https://atobarai.seino.co.jp/seino-financial/member/

ID : {LoginId}
※パスワードは別途メールにてお送りさせていただきます。
※サイトＩＤは上記ＩＤとは異なりますのでご注意ください。
サイトＩＤの参照方法は以下の通りです。

【1】管理サイトにログイン
　　↓　↓　↓　↓
【2】「登録情報管理」をクリック
　　↓　↓　↓　↓
【3】「サイト情報」をクリック
　　↓　↓　↓　↓
【4】「サイトＩＤ」欄に表示されます。

 ■マニュアルのダウンロード（必須）
下記のURLより、【後払い決済サービス】の運用マニュアルをダウンロード
してご使用下さい。
サービス開始に必要なマニュアルとなっておりますので、必ずご確認
いただきますようお願い申し上げます。

  http://www.seino.co.jp/financial/atobarai/Shop_Manual.pdf

※閲覧にはAdobe PDF Reader が必要です。インストールされていない
方は、下記のURLより同ソフトのインストールをお願いいたします。

  http://www.adobe.com/jp/products/acrobat/readstep2.html

管理システムのご利用方法は、ダウンロードしていただいたマニュアル
をご確認ください。

サービスの開始まで、店舗様には以下のような作業をしていただきます。
開始のご連絡をお忘れなきよう、お願い申し上げます。

■■■　STEP 1　■■■登録内容のご確認

管理サイトにログイン、店舗情報を確認（プランその他の情報）

■■■　STEP 2　■■■定型文章のサイト掲載

マニュアルにしたがって、店舗様サイト上に当決済方法用の定型文章を掲載
（特定商取引法ページや決済選択画面など）

サイト掲載用定型文・画像提供ページ：
http://www.seino.co.jp/financial/atobarai/tokushoho/
※この時点でサービス開始となります

■■■　STEP 3　■■■サービス開始の当社へのご通知

サービスを開始した旨を、当社までメールもしくはお電話にてご連絡下さい。
 mail: sfc-atobarai@seino.co.jp
 tel:  03-6908-7888

■■■　STEP 4　■■■当社が決済画面を確認

当社担当が決済画面を確認させていただき、問題がなければそのまま運営、
問題があれば修正のお願いをさせていただくことがございます。

  ↑↑↑「流れ」はここまで

■消費者様への請求書のご案内用紙のダウンロード（任意）
下記のＵＲＬより請求書のご案内用紙をダウンロードして、商品に同梱
してください。
（ご案内用紙の同梱は店舗様のご判断による任意で行っていただいて
　おりますが、初めて当決済をご利用なる消費者様にとっては分かり易く
　なり、お問合せが減ることにも繋がりますので、同梱していただくこと
　を推奨しております。）

http://www.seino.co.jp/financial/atobarai/dokon.xls

サービス開始に当たって、また、運営に関するお問い合わせ等は、
メール末尾のご連絡先にお気軽にお問合せ下さい。


今後とも末永いお付き合いの程、宜しくお願い申し上げます。

セイノーフィナンシャル株式会社　後払い決済サービス担当　スタッフ一同

--------------------------------------------------------------

【後払い決済サービス】～最も消費者に愛される決済サービス～

  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (57,2,'注文登録（与信開始）メール','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】与信開始のお知らせ（{OrderCount}件）','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{EnterpriseNameKj} 様

いつも【後払い決済サービス】をご利用いただき、まことにありがとうございます。
以下のご注文を受け付けいたしました。
これより与信に入りますので、商品をまだ発送されないようご注意下さい。

受付注文件数：{OrderCount}件

ご注文者名（ご請求総額）
--------------------------------------------------------------
{OrderSummary}
--------------------------------------------------------------
上記お取引の与信完了後に、与信完了メールを送信いたします。


※18:00以降の与信は、通常翌日11:00までの回答となりますのでご注意下さい。
※注文により与信にかかる時間が異なる場合がございます。その場合、与信結果が
出たものから自動で与信完了メールが送信されますので、あらかじめご了承下さい。

■■■■■■■■■■■　キャンセルが発生した場合　■■■■■■■■■■■

ご登録された注文のキャンセルが入った場合は、お手数ですが「履歴検索」から
ご注文を検索し、該当のお取引をクリックしてキャンセル処理を行って下さい。

■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

【管理画面ＵＲＬ】
https://atobarai.seino.co.jp/seino-financial/member/

ご不明な点などございましたら、お気軽にお問い合わせ下さい。

--------------------------------------------------------------

【後払い決済サービス】～最も消費者に愛される決済サービス～

  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (58,3,'与信完了メール','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】与信完了のお知らせ：計{CreditCount}件（うちNG{NgCount}件）','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{EnterpriseNameKj}　様

いつも【後払い決済サービス】をご利用いただき、まことにありがとうございます。

与信件数：{CreditCount} 件

の与信結果が出ましたのでご報告いたします。

【管理画面ＵＲＬ】
https://atobarai.seino.co.jp/seino-financial/member/

※与信がNGのご注文であっても、NG理由によっては、無保証にて「OK」に変更できる場合がございます。
無保証で後払いサービスご希望の方は以下に記載の【NG理由による処理方法について】を参考にしてください。
（無保証でも「OK」に変更できない場合もございますので、弊社からの
返信メールをご確認いただいてから、商品発送などを行ってください。）

{Orders}

【OK案件の処理】
与信が通過したお取引に関しましては、

1.商品の発送
2.配送伝票番号登録

にお進み下さい。

【NG理由による処理方法について】
※ NG理由が「長期遅延歴」「高額保留」「無保証変更可能」の場合
無保証での後払いサービスへ 切り替えて頂くことが可能です。
無保証に変更する場合は、このメールより{OutOfAmendsDays}日以内に後払い決済管理システムに
ログイン後に操作を実施してください。

※ 上記以外のNG理由の場合
その他のNG理由のお取引に関しましては、お早めにご購入者様に他の決済方法のご選択を
いただくなどのご対応をお願いいたします。

--------------------------------------------------------------

【後払い決済サービス】～最も消費者に愛される決済サービス～

  お問合せ先：03-5909-4500
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (59,4,'請求書発行メール（PC）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】請求書発行案内　（ハガキで届きます）','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','─────────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
─────────────────────────────────────

{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

下記のショッピングのご請求書を本日発行いたしますので、請求書到着後、
請求書に記載されているお支払期限日までにお支払いいただきますよう、
お願い申し上げます。

{OrderPageAccessUrl}

【ご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料                              \{SettlementFee}
送料                                    \{DeliveryFee}

※郵送事故などにより、請求書が届かないことがございます。
　一週間ほどお待ちいただいても請求書が届かない場合には、大変お手数ですが、
　下記連絡先へご一報くださいますよう、お願い申し上げます。

※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
　届かない場合がございます。
　万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
　店舗様まで直接お問合せくださいませ。

※請求書並びに本メールが、商品よりも早く到着してしまうことがございますが、
何卒ご容赦下さいますようお願い申し上げます。
また、請求書内に明細が含まれておりますのでご確認くださいませ。

詳しくは下記パソコン用URLをご覧下さい。

http://www.seino.co.jp/financial/atobarai/guidance/

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、下記
　購入店舗様に直接お問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp

--------------------------------------------------------------

【後払い決済サービス】～最も消費者に愛される決済サービス～

  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2017/12/26 15:14:00',59,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (60,5,'請求書発行メール（CEL）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】請求書発行案内　（ハガキで届きます）','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇


{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

下記のショッピングのご請求書を本日発行いたしますので、請求書到着後、
請求書に記載されているお支払期限日までにお支払いいただきますよう、
お願い申し上げます。

【ご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料  \{SettlementFee}
送料 \{DeliveryFee}

※郵送事故などにより、請求書が届かないことがございます。
　一週間ほどお待ちいただいても請求書が届かない場合には、大変お手数ですが、
　下記連絡先へご一報くださいますよう、お願い申し上げます。

※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
　届かない場合がございます。
　万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
　店舗様まで直接お問合せくださいませ。

※請求書並びに本メール、商品よりも早く到着してしまうことがございますが、
何卒ご容赦下さいますようお願い申し上げます。
また、請求書内に明細が含まれておりますのでご確認くださいませ。

詳しくは下記パソコン用URLをご覧下さい。

http://www.seino.co.jp/financial/atobarai/guidance/

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、下記
　購入店舗様に直接お問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
住所：〒503-8501 岐阜県大垣市田口町１番地
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp
URL: http://www.seino.co.jp/financial（パソコン専用）

--------------------------------------------------------------

【後払い決済サービス】～最も消費者に愛される決済サービス～

  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/12/01 12:58:08',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (61,6,'入金確認メール（PC）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】ご入金確認のご報告','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','─────────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
─────────────────────────────────────

{CustomerNameKj}　様

この度は、{SiteNameKj}様でのお買い物に、
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

{ReceiptDate}に{CustomerNameKj}様からのご入金を
確認いたしましたのでご報告申し上げます。

以下が、今回ご入金いただいたご注文の内容となります。

【領収済みご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料                              \{SettlementFee}
送料                                    \{DeliveryFee}

ご購入店舗名：{SiteNameKj}
ご連絡先：{Phone}
住所：{Address}

ご不明な点などございましたら、お気軽にお問い合わせ下さい。

※商品・返品・配送に関するお問い合わせは
直接購入店舗様にお問い合わせ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前
（フルネーム）を本文に入れてお問合せください。

またのご利用を心より、お待ちしております。

--------------------------------------------------------------

【後払い決済サービス】～最も消費者に愛される決済サービス～

  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (62,7,'入金確認メール（CEL）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】ご入金確認のご報告','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇


{CustomerNameKj}　様

この度は、{SiteNameKj}様でのお買い物に、
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

{ReceiptDate}に{CustomerNameKj}様からのご入金を
確認いたしましたのでご報告申し上げます。

以下が、今回ご入金いただいたご注文の内容となります。

【領収済みご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料 \{SettlementFee}
送料       \{DeliveryFee}

ご購入店舗名：{SiteNameKj}
ご連絡先：{Phone}
住所：{Address}

ご不明な点などございましたら、お気軽にお問い合わせ下さい。

※商品・返品・配送に関するお問い合わせは
直接購入店舗様にお問い合わせ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前
（フルネーム）を本文に入れてお問合せください。

またのご利用を心より、お待ちしております。

--------------------------------------------------------------

【後払い決済サービス】～最も消費者に愛される決済サービス～

  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/12/01 12:58:23',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (63,8,'立替完了メール','','','',null,null,null,'','','',3,'2015/08/31 22:42:31',9,'2015/12/01 13:01:10',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (64,9,'キャンセル確認メール','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】キャンセル確定のご報告({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{EnterpriseNameKj}　様

いつも【後払い決済サービス】をご利用いただきまして、まことにありがとうございます。

以下のご注文のキャンセルを承りましたので、ご確認下さい。
また、キャンセルのタイミングによって、その後の消費者様への対応が異なります
のでご注意下さい。（※以下の【1】～【4】をご参照下さい。）

【管理画面ＵＲＬ】
https://atobarai.seino.co.jp/seino-financial/member/

【キャンセル確定情報】
キャンセル区分：{CancelPhase}
ご注文ID：{OrderId}
請求先氏名：{CustomerNameKj}　様
ご注文総額：{UseAmount}
ご注文日：{OrderDate}

【1】未立替案件のキャンセル
返金等は発生しません。請求書がすでに発送されている場合は、お客様
に請求書破棄のお願いをご連絡をお願い申し上げます。

【2】立替済み案件のキャンセル
次回立替時に、立替済みの金額を、相殺により返金させていただきます。
店舗様側での作業は必要ございません。また、決済手数料も発生いたしません。

【3】立替済み・お客様ご入金済み案件のキャンセル
後ほど当社より店舗様に連絡をさせていただきますので、その後にお客様へ、
商品代金を店舗様よりご返金いただくことになります。
決済手数料は発生いたしませんので、次回立替時に手数料を返金いたします。

【4】未立替え・お客様入金済み案件のキャンセル
後ほど当社より店舗様に連絡をさせていただきますので、その後にお客様へ、
商品代金を店舗様よりご返金いただくことになります。
また、お客様からのご入金分を次回立替時に当社より店舗様へ返金させていた
だきます。この場合も決済手数料は発生いたしません。

※お客様が店舗様の口座へ直接入金された場合や、店舗様が誤って代引きで
　発送された場合等の、お客様と店舗様間でのお取引が成立している場合の
　キャンセル処理の際には、上記【1】～【4】のいずれの場合も所定の手数料
　を次回立替時の調整額にて徴収させていただきます。

ご不明な点などございましたら、お気軽に当社までお問い合わせ下さい。

--------------------------------------------------------------

【後払い決済サービス】～最も消費者に愛される決済サービス～

  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (65,10,'アドレス確認メール','後払い決済サービス','','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】のご選択ありがとうございます','','{CustomerNameKj}様

この度は、お支払方法に【後払い決済サービス】をご選択いただき、
まことにありがとうございます。

ただいま、下記のご注文におきまして後払い決済サービスをご利用いただけるか、
審査をいたしております。

【ご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料  \{SettlementFee}
送料        \{DeliveryFee}


結果につきましては、ご注文いただきました店舗様より、
後ほどご連絡が入りますので、もう少々お待ち下さいませ。

ご不明な点などございましたら、お気軽にお問い合わせ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

--------------------------------------------------------------

【後払い決済サービス】～最も消費者に愛される決済サービス～

  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (66,11,'もうすぐお支払メール（PC）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】もうすぐお支払い期限です','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','─────────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
─────────────────────────────────────

{CustomerNameKj}様


この度は、{SiteNameKj}様でのお買い物に、
【後払い決済サービス】をご利用いただきまして、まことにありがとうございます。
{IssueDate}に請求書をお送りいたしましたが、お手元にお届きでしょうか。

お送りした請求書のお支払期限日が近づいてまいりましたので、お知らせいたします。

※土日・祝祭日は入金の確認が取れない為、その間に御手続きいただいた場合、
入れ違いで当メールが送られてしまいます。
その場合は、まことに申し訳ございませんが、当メールを削除していただきますよう
お願い申し上げます。
（郵便局で御手続きいただいた場合、確認に最大4営業日かかる場合がございますので、
前日や前々日に御手続きいただいておりましても、同じように入れ違いで当メールが
届いてしまう場合がございます。）

お支払期限日：{LimitDate}
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
ご注文総額：{UseAmount}
商品名（1品目のみ表示）：{OneOrderItem}
請求日：{IssueDate}
 
まだお支払いいただいていない場合は、弊社よりお送りいたしました請求書を
ご確認のうえ、上記期限日までにお支払いいただきますよう、お願い申し上げます。

※万が一期限を過ぎてしまいますと、消費者契約法に基づく遅延損害金及び、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましても、ご入金の確認は取れます。
口座へお振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
ジャパンネット銀行　
モミジ支店　
普通口座　0015015
セイノーフィナンシャル（カ

【郵便振替口座】
口座記号：00100-7
口座番号：292043
株式会社キャッチボール　セイノーFC係

※郵便局／銀行からお振込みいただく場合、振込手数料がお客様ご負担となります。

※郵便局の口座お持ちの場合は、郵便局のＡＴＭを利用して口座からご送金いただ
ければ、郵便振込手数料はかかりません。(店舗決済手数料とは別です。)

万が一請求書がお手元に届いていない場合や、お支払に関しまして、ご不明な点等
ございましたら、下記までお気軽にお問い合わせ下さいませ。

※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
店舗様まで直接お問合せくださいませ。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、注文された
　店舗様に直接お問合せください。

今後とも当社サービス【後払い決済サービス】をよろしくお願い申し上げます。

--------------------------------------------------------------

【後払い決済サービス】～最も消費者に愛される決済サービス～

  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (67,12,'もうすぐお支払メール（CEL）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】もうすぐお支払い期限です','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇


{CustomerNameKj}様


この度は、{SiteNameKj}様でのお買い物に、
【後払い決済サービス】をご利用いただきまして、まことにありがとうございます。
{IssueDate}に請求書をお送りいたしましたが、お手元にお届きでしょうか。

お送りした請求書のお支払期限日が近づいてまいりましたので、お知らせいたします。

※土日・祝祭日は入金の確認が取れない為、その間に御手続きいただいた場合、
入れ違いで当メールが送られてしまいます。
その場合は、まことに申し訳ございませんが、当メールを削除していただきますよう
お願い申し上げます。
（郵便局で御手続きいただいた場合、確認に最大4営業日かかる場合がございますので、
前日や前々日に御手続きいただいておりましても、同じように入れ違いで当メールが
届いてしまう場合がございます。）

お支払期限日：{LimitDate}
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
ご注文総額：{UseAmount}
商品名（1品目のみ表示）：{OneOrderItem}
請求日：{IssueDate}
 
まだお支払いいただいていない場合は、弊社よりお送りいたしました請求書を
ご確認のうえ、上記期限日までにお支払いいただきますよう、お願い申し上げます。

※万が一期限を過ぎてしまいますと、消費者契約法に基づく遅延損害金及び、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましても、ご入金の確認は取れます。
口座へお振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。


【銀行振込口座】
ジャパンネット銀行　
モミジ支店　
普通口座　0015015
セイノーフィナンシャル（カ

【郵便振替口座】
口座記号：00100-7
口座番号：292043
株式会社キャッチボール　セイノーFC係

※郵便局／銀行からお振込みいただく場合、振込手数料がお客様ご負担となります。

※郵便局の口座お持ちの場合は、郵便局のＡＴＭを利用して口座からご送金いただ
ければ、郵便振込手数料はかかりません。(店舗決済手数料とは別です。)

万が一請求書がお手元に届いていない場合や、お支払に関しまして、ご不明な点等
ございましたら、下記までお気軽にお問い合わせ下さいませ。

※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
店舗様まで直接お問合せくださいませ。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、注文された
　店舗様に直接お問合せください。

今後とも当社サービス【後払い決済サービス】をよろしくお願い申し上げます。

--------------------------------------------------------------

【後払い決済サービス】～最も消費者に愛される決済サービス～

  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/12/01 13:06:36',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (70,15,'伝票番号確認のお願い','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】伝票番号のご確認をお願いします','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{EnterpriseNameKj}
{CpNameKj} 様

お世話になっております。【後払い決済サービス】カスタマーセンターです。

{ReceiptOrderDate}にご注文登録いただきました、
下記お客様の着荷確認が取れておりません。

ご登録頂きました、配送伝票番号に入力ミスがあるか、
商品がお客様に届いていない可能性がございます。

商品の配送会社、配送伝票番号、並びに配送状況を
個人情報の兼ね合いもございますので
お手数ですが一度店舗様側でご確認いただき、
店舗様管理サイト上から修正していただきたく思います。
※編集方法は履歴検索から特定のお客様を絞り込んでいただき、
『登録内容の修正』から修正をおこなって下さい。

お取引ID ：{OrderId}
ご注文者様名 ：{CustomerNameKj} 様
伝票番号登録日 ：{Deli_JournalIncDate}
登録伝票番号 ：{Deli_JournalNumber}

尚、長期間に渡りご変更をいただけず、配送会社の追跡サービスにて
着荷の確認が取れなくなってしまった場合、無保証扱いとなりますので
ご注意ください。

ご不明点などございましたら、弊社 03-6908-7888 まで
ご連絡いただければと思います。
何卒よろしくお願いいたします。


--------------------------------------------------------------

【後払い決済サービス】～最も消費者に愛される決済サービス～

  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/12/01 13:07:46',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (71,16,'戻り請求住所確認メール','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】【重要】ご住所確認の連絡です。','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','─────────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
─────────────────────────────────────

{CustomerNameKj}様

{ReceiptOrderDate}に{SiteNameKj}で、
後払い決済サービス決済を選択していただきありがとうございます。
{ClaimDate}にお送りいたしました請求書が弊社に戻ってきておりますので、
ご住所の確認をさせていただきたくご連絡させていただきました。

（お客様住所）　{UnitingAddress}

上記住所に不備がありましたら、再度請求書を発行させていただきますので
ご連絡の程、よろしくお願い致します。

住所に不備がない場合でも、表札氏名が違っていた場合などで、郵便物が届かないケースも
ありますので、ご了承下さい。

また、銀行、郵便局からのご入金も可能ですので
口座番号をお送りさせていただきます。

【銀行振込口座】
ジャパンネット銀行　
モミジ支店　
普通口座　0015015
セイノーフィナンシャル（カ

【郵便振替口座】
口座記号：00100-7
口座番号：292043
株式会社キャッチボール　セイノーFC係

【ご請求明細】
商品名　　：{ItemNameKj}
商品代金　：{ItemAmount}円
送料　　　：{DeliveryFee}円
手数料　　：{SettlementFee}円
{OptionFee}
合計　　　：{UseAmount}円

その他ご不明な点、ご入金のご相談等は当社までお問い合わせください。

--------------------------------------------------------------

【後払い決済サービス】～最も消費者に愛される決済サービス～

  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (72,17,'請求書発行メール（同梱ツール向け：PC）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】請求書発行案内','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','─────────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
─────────────────────────────────────

 {CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

下記のショッピングの請求書を本日発行いたしました。
商品に同梱されている請求書に記載のお支払期限日までに
お支払いいただきますよう、お願い申し上げます。

※請求書の発行日と商品の発送日は異なる場合がございます。
　予めご了承くださいませ。

【ご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料                              \{SettlementFee}
送料                                    \{DeliveryFee}

※商品と共に請求書が入っていない場合には、大変お手数ですが、
　下記連絡先へご一報くださいますよう、お願い申し上げます。

詳しくは下記パソコン用URLをご覧下さい。

http://www.seino.co.jp/financial/atobarai/guidance/

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、下記
　購入店舗様に直接お問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp

--------------------------------------------------------------

【後払い決済サービス】～最も消費者に愛される決済サービス～

  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (73,18,'請求書発行メール（同梱ツール向け：CEL）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】請求書発行案内','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','─────────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
─────────────────────────────────────

{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

下記のショッピングの請求書を本日発行いたしました。
商品に同梱されている請求書に記載のお支払期限日までに
お支払いいただきますよう、お願い申し上げます。

※請求書の発行日と商品の発送日は異なる場合がございます。
　予めご了承くださいませ。

【ご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料  \{SettlementFee}
送料 \{DeliveryFee}

※商品と共に請求書が入っていない場合には、大変お手数ですが、
　下記連絡先へご一報くださいますよう、お願い申し上げます。

詳しくは下記パソコン用URLをご覧下さい。

http://www.seino.co.jp/financial/atobarai/guidance/

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、下記
　購入店舗様に直接お問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
住所：〒503-8501 岐阜県大垣市田口町１番地
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp
URL: http://http://www.seino.co.jp/financial（パソコン専用）

--------------------------------------------------------------

【後払い決済サービス】～最も消費者に愛される決済サービス～

  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (74,19,'与信結果メール(OK, PC)','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】与信結果のお知らせ','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{CustomerNameKj}様

この度は{SiteNameKj}で{OneOrderItem}をご注文された際のお支払い方法に、後払い決済サービスを御選択頂きまして、まことにありがとうございます。

後払い決済の与信審査が問題なく通過いたしましたのでご報告申し上げます。

ご注文頂きました商品についてのお問い合わせ・ご注文内容のご変更、ご注文のキャンセル等に関しましては、{SiteNameKj}での対応となりますので、直接ご連絡して頂きますようお願い申し上げます。

【{SiteNameKj}】
{ContactPhoneNumber}
{MailAddress}

尚、請求書の発行については弊社よりメールをお送り致しますので
ご確認ください。

後払い決済に関してご不明な点などございましたら、下記の後払い決済サービスカスタマーセンターへお問い合わせください。

【後払い決済サービスカスタマーセンター】
運営会社：セイノーフィナンシャル株式会社
TEL:03-6908-7888',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (75,20,'与信結果メール(OK, CEL)','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】与信結果のお知らせ','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{CustomerNameKj}様

この度は{SiteNameKj}で{OneOrderItem}をご注文された際のお支払い方法に、後払い決済サービスを御選択頂きまして、まことにありがとうございます。

後払い決済の与信審査が問題なく通過いたしましたのでご報告申し上げます。

ご注文頂きました商品についてのお問い合わせ・ご注文内容のご変更、ご注文のキャンセル等に関しましては、{SiteNameKj}での対応となりますので、直接ご連絡して頂きますようお願い申し上げます。

【{SiteNameKj}】
{ContactPhoneNumber}
{MailAddress}

尚、請求書の発行については弊社よりメールをお送り致しますので
ご確認ください。

後払い決済に関してご不明な点などございましたら、下記の後払い決済サービスカスタマーセンターへお問い合わせください。

【後払い決済サービスカスタマーセンター】
運営会社：セイノーフィナンシャル株式会社
TEL:03-6908-7888',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (76,21,'与信結果メール(NG, PC)','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】与信結果のお知らせ','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{CustomerNameKj}様

この度は{SiteNameKj}で{OneOrderItem}をご注文された際のお支払い方法に、
後払い決済サービスを御選択頂きまして、まことにありがとうございます。

与信審査の結果、今回の御注文につきまして、
後払い決済の与信審査が通過いたしませんでした事をご報告申し上げます。

大変お手数ではございますが、{SiteNameKj}へご連絡の上、
他のお支払い方法にご変更頂きたいと存じます。

【{SiteNameKj}】
{ContactPhoneNumber}
{MailAddress}

ご注文頂きました商品についてのお問い合わせ・ご注文内容のご変更、
ご注文のキャンセル等に関しましても、{SiteNameKj}での対応となりますので、
直接ご連絡して頂きますようお願い申し上げます。

お支払い方法をご変更頂いた場合、
後払い決済に関する手数料は一切発生いたしません。

後払い決済の与信審査につきましては、
後払い決済サービスを運営しております
セイノーフィナンシャル株式会社にて行っております。
与信審査結果の理由などにつきましては、
個人情報を含む内容になる為、当社から{SiteNameKj}へは一切開示いたしておりません。
つきましては、与信審査結果の理由などのお問い合わせに関しましては、
直接、お電話にて当社へ御連絡頂きますようお願い申し上げます。

【後払い決済サービスカスタマーセンター】
運営会社：セイノーフィナンシャル株式会社
TEL:03-6908-7888
営業時間:10:00～18:00　年中無休(年末・年始のぞく)

与信審査に関しましては、ご本人様よりご連絡頂きますと再審査も可能でございます。
後払いの審査条件といたしまして、ご本人様確認が取れることが必須となっております。
そのため、ご住所やお電話番号の不備、
もしくはお知り合いやご親戚の方とご同居で、
ご住所やお電話番号のお名義がご注文者様と異なる場合(苗字が違う場合)など、
与信審査が通過いたしません。
法人用・店舗用を個人名義にてご注文された場合も同様でございます。

もしお心当たりがあり修正が可能な場合には、再度与信審査をいたしますので、
上記の後払い決済サービスカスタマーセンターまで御連絡いただきますよう、
お願い申し上げます。

お手数をおかけいたしまして、まことに恐縮ではございますが、
ご対応の程よろしくお願い申し上げます。
',3,'2015/08/31 22:42:31',9,'2015/12/01 13:17:24',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (77,22,'与信結果メール(NG, CEL)','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】与信結果のお知らせ','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{CustomerNameKj}様

この度は{SiteNameKj}で{OneOrderItem}をご注文された際のお支払い方法に、
後払い決済サービスを御選択頂きまして、まことにありがとうございます。

与信審査の結果、今回の御注文につきまして、
後払い決済の与信審査が通過いたしませんでした事をご報告申し上げます。

大変お手数ではございますが、{SiteNameKj}へご連絡の上、
他のお支払い方法にご変更頂きたいと存じます。

【{SiteNameKj}】
{ContactPhoneNumber}
{MailAddress}

ご注文頂きました商品についてのお問い合わせ・ご注文内容のご変更、
ご注文のキャンセル等に関しましても、{SiteNameKj}での対応となりますので、
直接ご連絡して頂きますようお願い申し上げます。

お支払い方法をご変更頂いた場合、後払い決済に関する手数料は一切発生いたしません。

後払い決済の与信審査につきましては、
後払い決済サービスを運営しております
セイノーフィナンシャル株式会社にて行っております。
与信審査結果の理由などにつきましては、個人情報を含む内容になる為、
当社から{SiteNameKj}へは一切開示いたしておりません。
つきましては、与信審査結果の理由などのお問い合わせに関しましては、
直接、お電話にて当社へ御連絡頂きますようお願い申し上げます。

【後払い決済サービスカスタマーセンター】
運営会社：セイノーフィナンシャル株式会社
TEL:03-6908-7888
営業時間:10:00～18:00　年中無休(年末・年始のぞく)

与信審査に関しましては、ご本人様よりご連絡頂きますと再審査も可能でございます。
後払いの審査条件といたしまして、ご本人様確認が取れることが必須となっております。
そのため、ご住所やお電話番号の不備、
もしくはお知り合いやご親戚の方とご同居で、
ご住所やお電話番号のお名義がご注文者様と異なる場合(苗字が違う場合)など、
与信審査が通過いたしません。
法人用・店舗用を個人名義にてご注文された場合も同様でございます。

もしお心当たりがあり修正が可能な場合には、再度与信審査をいたしますので、
上記の後払い決済サービスカスタマーセンターまで御連絡いただきますよう
、お願い申し上げます。

お手数をおかけいたしまして、まことに恐縮ではございますが、
ご対応の程よろしくお願い申し上げます。',3,'2015/08/31 22:42:31',9,'2015/12/01 13:18:12',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (78,23,'パスワード情報お知らせメール','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】 パスワード情報のお知らせ','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{EnterpriseNameKj}　様

この度は弊社サービス、【後払い決済サービス】にお申込いただき、
まことにありがとうございます。

後払い決済管理システムにログインしていただく為に必要な
パスワードをお送りさせていただきます。

PW :{GeneratedPassword}

サービス開始に当たって、また、運営に関するお問い合わせ等は、
メール末尾のご連絡先にお気軽にお問合せ下さい。


今後とも末永いお付き合いの程、宜しくお願い申し上げます。

セイノーフィナンシャル株式会社　後払い決済サービス担当　スタッフ一同

--------------------------------------------------------------

【後払い決済サービス】～最も消費者に愛される決済サービス～

  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------',3,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (79,2,'注文登録（与信開始）メール','','','',null,null,null,'','','',1,'2015/08/31 22:42:31',9,'2015/08/31 22:42:31',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (80,30,'キャンセル申請メール','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】キャンセル申請をお受付いたしました','{ServiceName}','{EnterpriseNameKj}　様

いつも【{ServiceName}】をご利用いただきまして、まことにありがとうございます。

以下のご注文のキャンセル申請のお受付いたしました。

【管理画面ＵＲＬ】
https://www.atobarai.jp/member/

【キャンセル受付情報】
ご注文ID：{OrderId}
請求先氏名：{CustomerNameKj}　様
ご注文総額：{UseAmount}
ご注文日：{OrderDate}

弊社での確認後、再度承認連絡をさせていただきます。

ご不明な点などございましたら、お気軽に当社までお問い合わせ下さい。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/09/17 15:27:09',9,'2022/04/20 2:12:09',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (81,31,'キャンセル申請取消メール','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】キャンセル申請取消をお受付いたしました','{ServiceName}','{EnterpriseNameKj}　様

いつも【{ServiceName}】をご利用いただきまして、まことにありがとうございます。

以下のご注文のキャンセル申請の取り消しを承りましたので、ご確認下さい。

【管理画面ＵＲＬ】
https://www.atobarai.jp/member/

【キャンセル取消情報】
ご注文ID：{OrderId}
請求先氏名：{CustomerNameKj}　様
ご注文総額：{UseAmount}
ご注文日：{OrderDate}
キャンセル申請日：{CancelDate} 

ご不明な点などございましたら、お気軽に当社までお問い合わせ下さい。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/09/17 15:27:09',9,'2022/04/20 5:55:56',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (116,1,'事業者登録完了（サービス開始）メール','株式会社Ｅストアー（後払い窓口）','=?UTF-8?B?GyRCM3Q8MDJxPFIjRSU5JUglIiE8IUo4ZUonJCRBazh9IUsbKEI=?=','ato-barai.sp@estore.co.jp',null,null,null,'テスト','=?UTF-8?B?5qCq5byP5Lya56S+77yl44K544OI44Ki44O877yI5b6M5omV44GE56qT5Y+j?=
 =?UTF-8?B?77yJ?=','{EnterpriseNameKj}　様

この度は弊社サービス、【後払いドットコム】にお申込いただき、
まことにありがとうございます。

審査の結果、通過となりましたので、後払い決済管理システムを
ご利用いただくのに必要なIDを報告申し上げます。

重要なご案内となりますので、最後までお読みください。

【管理サイトＵＲＬ】
https://www.atobarai.jp/member/




ID : {LoginId}




※パスワードは別途メールにてお送りさせていただきます。
※サイトＩＤは上記ＩＤとは異なりますのでご注意ください。
サイトＩＤの参照方法は以下の通りです。

【1】管理サイトにログイン
　　↓　↓　↓　↓
【2】「登録情報管理」をクリック
　　↓　↓　↓　↓
【3】「サイト情報」をクリック
　　↓　↓　↓　↓
【4】「サイトＩＤ」欄に表示されます。

 ■マニュアルのダウンロード（必須）
下記のURLより、【後払いドットコム】の運用マニュアルをダウンロード
してご使用下さい。
サービス開始に必要なマニュアルとなっておりますので、必ずご確認
いただきますようお願い申し上げます。

 https://www.atobarai.jp/doc/help/Atobarai.com_Manual.pdf

※閲覧にはAdobe PDF Reader が必要です。インストールされていない
方は、下記のURLより同ソフトのインストールをお願いいたします。

  http://www.adobe.com/jp/products/acrobat/readstep2.html

管理システムのご利用方法は、ダウンロードしていただいたマニュアル
をご確認ください。

サービスの開始まで、店舗様には以下のような作業をしていただきます。
開始のご連絡をお忘れなきよう、お願い申し上げます。

■■■　STEP 1　■■■登録内容のご確認

管理サイトにログイン、店舗情報を確認（プランその他の情報）

■■■　STEP 2　■■■定型文章のサイト掲載

マニュアルにしたがって、店舗様サイト上に当決済方法用の定型文章を掲載
（特定商取引法ページや決済選択画面など）

サイト掲載用定型文・画像提供ページ：

http://www.ato-barai.com/for_shops/tokuteishou.html

※この時点でサービス開始となります

■消費者様向け後払いドットコム動画＆販促バナーダウンロードページ
http://www.ato-barai.com/download/

消費者様向け動画は、初めて当決済をご利用になる消費者様にとって
分かり易くなり、お問合せを減らせる効果が期待できます。
さらに販促バナーは、後払い決済が出来るお店としてアピールできるため、
販促の効果にもつながりますので、こちらも併せてご活用ください。

■■■　STEP 3　■■■サービス開始の当社へのご通知

サービスを開始した旨を、当社までメールもしくはお電話にてご連絡下さい。
 mail: customer@ato-barai.com
 tel:  0120-667-690

■■■　STEP 4　■■■当社が決済画面を確認

当社担当が決済画面を確認させていただき、問題がなければそのまま運営、
問題があれば修正のお願いをさせていただくことがございます。

  ↑↑↑「流れ」はここまで

■消費者様への請求書のご案内用紙のダウンロード（任意）
下記のＵＲＬより請求書のご案内用紙をダウンロードして、商品に同梱
してください。
（ご案内用紙の同梱は店舗様のご判断による任意で行っていただいて
　おりますが、初めて当決済をご利用なる消費者様にとっては分かり易く
　なり、お問合せが減ることにも繋がりますので、同梱していただくこと
　を推奨しております。）

https://www.atobarai.jp/doc/download/doukonnyou.xls


サービス開始に当たって、また、運営に関するお問い合わせ等は、
メール末尾のご連絡先にお気軽にお問合せ下さい。

＊＊＊＊＊＊＊＊＊＊＊＊【注意事項】＊＊＊＊＊＊＊＊＊＊＊＊

１）以下に該当するご注文は、保証対象外となってしまいますので
　　ご注意ください。

※保証外とは、未払いの保証が付かず、消費者様からの入金が
　ない限りは店舗様へ入金させていただく事ができません。
　
・Web上にてお荷物の追跡ができない配送方法を使われたご注文
・伝票登録時に配送会社や伝票番号を誤った情報で登録されたご注文
・配達状況がキャンセル・持ち戻り等により配達完了の確認が
　とれないご注文
・実際に発送された配送方法に関わらず、伝票登録時の配送方法に
　【メール便】を選択して登録されたご注文
・紛争性があるご注文

２）配送伝票番号をご登録いただいた、当日又は、翌営業日に
　　ご注文者様に対して、ご請求書が発送されます。
※商品発送前に配送伝票番号をご登録いただきますと、請求書が商品
　より先に届いてしまう可能性が高くなりますので、商品発送後に
　配送伝票番号のご登録をお願いいたします。

３）締日までに弊社側で商品の着荷確認がとれたご注文分が
　　当該締日分の立替対象となります。
※伝票番号登録日や、配達完了日ではなく、弊社側で着荷確認が
　とれた日がベースとなりますのでご注意ください。

＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊


今後とも末永いお付き合いの程、宜しくお願い申し上げます。

株式会社キャッチボール　後払いドットコム事業部　スタッフ一同

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：0120-667-690
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',1,'2015/10/01 13:24:18',8394,'2016/01/19 18:28:25',43,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (188,32,'請求書破棄メール（PC）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】請求書破棄のお願い','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございました。

{CancelDate}に{SiteNameKj}より【{ServiceName}】へ
キャンセルのご連絡をいただきましたが、
既に請求書をお送りしてしまっておりますので、
大変お手数ではございますが破棄していただくようお願いいたします。

不備や不明点などございましたら、
お気軽にお問合せくださいませ。

この度は{SiteNameKj}と【{ServiceName}】をご利用いただき
まことにありがとうございました。

今後とも何卒、よろしくお願いいたします。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:56:46',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (189,33,'請求書破棄メール（CEL）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】請求書破棄のお願い','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございました。

{CancelDate}に{SiteNameKj}より【{ServiceName}】へ
キャンセルのご連絡をいただきましたが、
既に請求書をお送りしてしまっておりますので、
大変お手数ではございますが破棄していただくようお願いいたします。

不備や不明点などございましたら、
お気軽にお問合せくださいませ。

この度は{SiteNameKj}と【{ServiceName}】をご利用いただき
まことにありがとうございました。

今後とも何卒、よろしくお願いいたします。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:56:52',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (190,34,'過剰入金メール（PC）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】過剰入金のご連絡{OrderId} ','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}様で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございました。


{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
{OverReceiptAmount}円多くお支払いいただいておりましたので
ご返金させていただきたく、ご連絡をいたしました。

後日発送される、返金のご案内ハガキにて
お客様自身で返金のお手続きをお願いいたします。

※返金ハガキの発送については今月中旬、もしくは月末に
『【{ServiceName}】ご返金のご連絡』の件名のメールでご案内いたします。

また、当メールに口座情報を返信いただければ、
弊社にて振込のお手続きをさせていただく事も可能でございます。


お手数ではございますが、下記をご記入のうえ、当メールへご返信ください。
・銀行名：
・支店名：
・口座種目：
・口座番号：
・口座名義(カナ)：

※ご記入いただいた内容に不備がございますと返金処理が
いたしかねますのでご注意ください。

なお、ご返金の際の手数料330円はお客様負担になる旨、
ご理解賜りますようお願いいたします。

不明点などございましたら、お気軽にお問合せください。

何卒よろしくお願いいたします。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 2:20:06',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (191,35,'過剰入金メール（CEL）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】過剰入金のご連絡{OrderId} ','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}様で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございました。


{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
{OverReceiptAmount}円多くお支払いいただいておりましたので
ご返金させていただきたく、ご連絡をいたしました。

後日発送される、返金のご案内ハガキにて
お客様自身で返金のお手続きをお願いいたします。

※返金ハガキの発送については今月中旬、もしくは月末に
『【{ServiceName}】ご返金のご連絡』の件名のメールでご案内いたします。

また、当メールに口座情報を返信いただければ、
弊社にて振込のお手続きをさせていただく事も可能でございます。


お手数ではございますが、下記をご記入のうえ、当メールへご返信ください。
・銀行名：
・支店名：
・口座種目：
・口座番号：
・口座名義(カナ)：

※ご記入いただいた内容に不備がございますと返金処理が
いたしかねますのでご注意ください。

なお、ご返金の際の手数料330円はお客様負担になる旨、
ご理解賜りますようお願いいたします。

不明点などございましたら、お気軽にお問合せください。

何卒よろしくお願いいたします。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 2:20:00',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (192,38,'注文修正完了メール','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】ご注文内容の修正を承りました（{OrderCount}件）','{ServiceName}','{EnterpriseNameKj}様

いつも【{ServiceName}】をご利用いただき、
まことにありがとうございます。

以下のご注文内容の修正を受け付けいたしました。


修正完了件数：{OrderCount}件

ご注文者名：{OrderSummary}


【管理画面ＵＲＬ】
https://www.atobarai.jp/member/

ご不明な点などございましたら、お気軽にお問い合わせくださいませ。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 2:20:22',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (193,39,'支払期限超過メール（再１）（PC）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。


{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございます。

お支払い期限を過ぎてもご入金の確認が取れておりませんでしたので
再請求書を発行・発送いたしました。

※一週間ほどお待ちいただいても請求書が届かない場合は
大変お手数ですが、03-4326-3600にご一報ください。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまい、再度請求書が発行されますと
再請求手数料が加算される場合がございますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}
※ご注文ごとに口座番号が異なっております。
※一度ご入金頂きますと再度ご入金を受け付けることが
　できませんのでご注意ください。

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
https://atobarai-user.jp/

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:57:54',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (194,40,'支払期限超過メール（再１）（CEL）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。


{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございます。

お支払い期限を過ぎてもご入金の確認が取れておりませんでしたので
再請求書を発行・発送いたしました。

※一週間ほどお待ちいただいても請求書が届かない場合は
大変お手数ですが、03-4326-3600にご一報ください。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまい、再度請求書が発行されますと
再請求手数料が加算される場合がございますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}
※ご注文ごとに口座番号が異なっております。
※一度ご入金頂きますと再度ご入金を受け付けることが
　できませんのでご注意ください。

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
https://atobarai-user.jp/

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:58:01',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (195,41,'支払期限超過メール（再３）（PC）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。


{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございます。

お支払い期限を過ぎてもご入金の確認が取れておりませんでしたので
再請求書を発行・発送いたしました。
お手元に届き次第、期限までにお支払いをお願いいたします。

※一週間ほどお待ちいただいても請求書が届かない場合は
大変お手数ですが、03-4326-3600にご一報ください。


【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまい、再度請求書が発行されますと
再請求手数料が加算される場合がございますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}
※ご注文ごとに口座番号が異なっております。
※一度ご入金頂きますと再度ご入金を受け付けることが
　できませんのでご注意ください。

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
https://atobarai-user.jp/

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:58:23',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (196,42,'支払期限超過メール（再３）（CEL）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。


{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございます。

お支払い期限を過ぎてもご入金の確認が取れておりませんでしたので
再請求書を発行・発送いたしました。
お手元に届き次第、期限までにお支払いをお願いいたします。

※一週間ほどお待ちいただいても請求書が届かない場合は
大変お手数ですが、03-4326-3600にご一報ください。


【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまい、再度請求書が発行されますと
再請求手数料が加算される場合がございますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}
※ご注文ごとに口座番号が異なっております。
※一度ご入金頂きますと再度ご入金を受け付けることが
　できませんのでご注意ください。

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
https://atobarai-user.jp/

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:58:29',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (197,43,'支払期限超過メール（再４）（PC）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。


{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
再請求書を発行・発送いたしました。

未納分のお支払いにつき、お手元に届き次第
至急ご対応くださいますようお願いいたします。

※一週間ほどお待ちいただいても請求書が届かない場合は
大変お手数ですが、03-4326-3600にご一報ください。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまい、再度請求書が発行されますと
再請求手数料が加算される場合がございますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}
※ご注文ごとに口座番号が異なっております。
※一度ご入金頂きますと再度ご入金を受け付けることが
　できませんのでご注意ください。

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
https://atobarai-user.jp/

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:58:50',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (198,44,'支払期限超過メール（再４）（CEL）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【後払いドットコム】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。


{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
再請求書を発行・発送いたしました。

未納分のお支払いにつき、お手元に届き次第
至急ご対応くださいますようお願いいたします。

※一週間ほどお待ちいただいても請求書が届かない場合は
大変お手数ですが、03-4326-3600にご一報ください。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまい、再度請求書が発行されますと
再請求手数料が加算される場合がございますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}
※ご注文ごとに口座番号が異なっております。
※一度ご入金頂きますと再度ご入金を受け付けることが
　できませんのでご注意ください。

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
https://atobarai-user.jp/

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:58:56',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (199,45,'支払期限超過メール（再５）（PC）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
再請求書を発行・発送いたしました。

上記督促状でもお知らせしている通り、
お支払いが確認できない場合
お客様の信用取引など不利益が生じる可能性がございます。
つきましては速やかなご対応をお願いいたします。

※一週間ほどお待ちいただいても請求書が届かない場合は
大変お手数ですが、03-4326-3600にご一報ください。


【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまい、再度請求書が発行されますと
再請求手数料が加算される場合がございますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}
※ご注文ごとに口座番号が異なっております。
※一度ご入金頂きますと再度ご入金を受け付けることが
　できませんのでご注意ください。

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
https://atobarai-user.jp/

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:59:16',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (200,46,'支払期限超過メール（再５）（CEL）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
再請求書を発行・発送いたしました。

上記督促状でもお知らせしている通り、
お支払いが確認できない場合
お客様の信用取引など不利益が生じる可能性がございます。
つきましては速やかなご対応をお願いいたします。

※一週間ほどお待ちいただいても請求書が届かない場合は
大変お手数ですが、03-4326-3600にご一報ください。


【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまい、再度請求書が発行されますと
再請求手数料が加算される場合がございますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}
※ご注文ごとに口座番号が異なっております。
※一度ご入金頂きますと再度ご入金を受け付けることが
　できませんのでご注意ください。

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
https://atobarai-user.jp/

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:59:22',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (201,47,'支払期限超過メール（再６）（PC）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございます。

{ClaimDate}に督促状をお送りいたしましたが、
本日現在ご入金の確認ができておりません。

このまま未入金状態が継続されますと、
当社での対応が困難となり
通知記載の対応となる場合があります。
つきましては至急お支払いについて
ご対応くださいますようお願いいたします。

※一週間ほどお待ちいただいても請求書が届かない場合は
大変お手数ですが、03-4326-3600にご一報ください。


【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまい、再度請求書が発行されますと
再請求手数料が加算される場合がございますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}
※ご注文ごとに口座番号が異なっております。
※一度ご入金頂きますと再度ご入金を受け付けることが
　できませんのでご注意ください。

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
https://atobarai-user.jp/

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:59:43',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (202,48,'支払期限超過メール（再６）（CEL）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございます。

{ClaimDate}に督促状をお送りいたしましたが、
本日現在ご入金の確認ができておりません。

このまま未入金状態が継続されますと、
当社での対応が困難となり
通知記載の対応となる場合があります。
つきましては至急お支払いについて
ご対応くださいますようお願いいたします。

※一週間ほどお待ちいただいても請求書が届かない場合は
大変お手数ですが、03-4326-3600にご一報ください。


【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまい、再度請求書が発行されますと
再請求手数料が加算される場合がございますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}
※ご注文ごとに口座番号が異なっております。
※一度ご入金頂きますと再度ご入金を受け付けることが
　できませんのでご注意ください。

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
https://atobarai-user.jp/

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 5:59:51',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (203,49,'支払期限超過メール（再７）（PC）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。


{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
再請求書を発行・発送いたしました。

再三にわたり、ご返済に対し履行頂くようご通知いたしましたが
貴殿より誠意のあるご対応を頂いていない状況となっております。
今後につきましても、ご連絡・お支払いが確認できない場合は
弁護士への回収委任もしくは法的手続きに移行せざるをえません。
しかしながら、弊社債権管理部では
貴殿の債務履行に対し解決を図る為の相談窓口を設けており
相談による解決も可能な場合もございます。
つきましては、解決に向け至急ご連絡いただけますようお願いいたします。

※一週間ほどお待ちいただいても請求書が届かない場合は
大変お手数ですが、03-4326-3600にご一報ください。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまい、再度請求書が発行されますと
再請求手数料が加算される場合がございますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}
※ご注文ごとに口座番号が異なっております。
※一度ご入金頂きますと再度ご入金を受け付けることが
　できませんのでご注意ください。

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
https://atobarai-user.jp/

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 6:00:12',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (204,50,'支払期限超過メール（再７）（CEL）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。


{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
再請求書を発行・発送いたしました。

再三にわたり、ご返済に対し履行頂くようご通知いたしましたが
貴殿より誠意のあるご対応を頂いていない状況となっております。
今後につきましても、ご連絡・お支払いが確認できない場合は
弁護士への回収委任もしくは法的手続きに移行せざるをえません。
しかしながら、弊社債権管理部では
貴殿の債務履行に対し解決を図る為の相談窓口を設けており
相談による解決も可能な場合もございます。
つきましては、解決に向け至急ご連絡いただけますようお願いいたします。

※一週間ほどお待ちいただいても請求書が届かない場合は
大変お手数ですが、03-4326-3600にご一報ください。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまい、再度請求書が発行されますと
再請求手数料が加算される場合がございますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}
※ご注文ごとに口座番号が異なっております。
※一度ご入金頂きますと再度ご入金を受け付けることが
　できませんのでご注意ください。

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
https://atobarai-user.jp/

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 6:00:18',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (205,51,'CB向け請求取りまとめエラーメール','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,'daisuke-koie@scroll.co.jp','【{ServiceName}】請求取りまとめエラーメール','{ServiceName}','{EnterpriseNameKj} 様

いつも大変お世話になっております。
【{ServiceName}】カスタマーセンターでございます。

請求取りまとめ実行時にエラーが発生しました。 
下記のご注文を請求取りまとめ注文一覧から確認頂き、改めて御指示をお願い致します。
                      
取りまとめに失敗した取りまとめ指示グループの注文：
{OrderSummary}

理由：
{Error}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 6:00:40',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (206,52,'事業者向け請求取りまとめエラーメール','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】請求取りまとめエラーメール','{ServiceName}','{EnterpriseNameKj} 様

いつも大変お世話になっております。
【{ServiceName}】カスタマーセンターでございます。

請求取りまとめ実行時にエラーが発生しました。 
下記のご注文を請求取りまとめ注文一覧から確認頂き、改めて御指示をお願い致します。
                      
取りまとめに失敗した取りまとめ指示グループの注文：
{OrderSummary}

理由：
{Error}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 6:01:07',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (207,53,'マイページ仮登録完了メール（PC）','後払いドットコム / 届いてから払い','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQiAvIBskQkZPJCQkRiQrJGlKJyQkGyhC?=','customer@ato-barai.com',null,null,null,'【後払いドットコム / 届いてから払い】会員仮登録のご案内','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44OgIC8g5bGK44GE44Gm44GL44KJ5omV?=
 =?UTF-8?B?44GE?=','この度は【後払いドットコム / 届いてから払い】にお申込みいただき
まことにありがとうございます。

下記のURLをクリックして、【後払いドットコム / 届いてから払い】での
会員登録を進めてください。

{MypageRegistUrl}


＜ご注意事項＞
・本メールをお受け取り後、24時間以内に【後払いドットコム / 届いてから払い】会員登録を
完了していただきますようお願いいたします。
・24時間以内にご登録が完了されない場合は仮登録のお手続きが無効と
なりますのであらかじめご了承願います。
・24時間を過ぎてしまった場合は、恐れ入りますが再度仮登録のお手続きを
お願いいたします。


------------------------------------
ご登録の手順について
------------------------------------

１.上記URLにアクセスし、画面にしたがって必要事項をご入力ください。

２.ご入力内容をご確認のうえ、お客様情報を登録してください。

３.【後払いドットコム / 届いてから払い】会員登録完了のお知らせ”というメールが届きます。

以上で【後払いドットコム / 届いてから払い】会員登録完了となります。



ご登録がうまくいかない場合は、
大変恐れ入りますがcustomer@ato-barai.comまで
お問い合わせをお願いいたします。

※営業時間外のお問い合わせにつきましては
ご返信にお時間をいただく場合がございます。


この度はお申込みありがとうございました。


-----------------------------------------------------------
【後払いドットコム / 届いてから払い】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：customer@ato-barai.com
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/07/14 11:25:15',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (208,54,'マイページ仮登録完了メール（CEL）','後払いドットコム / 届いてから払い','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQiAvIBskQkZPJCQkRiQrJGlKJyQkGyhC?=','customer@ato-barai.com',null,null,null,'【後払いドットコム / 届いてから払い】会員仮登録のご案内','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44OgIC8g5bGK44GE44Gm44GL44KJ5omV?=
 =?UTF-8?B?44GE?=','この度は【後払いドットコム / 届いてから払い】にお申込みいただき
まことにありがとうございます。

下記のURLをクリックして、【後払いドットコム / 届いてから払い】での
会員登録を進めてください。

{MypageRegistUrl}


＜ご注意事項＞
・本メールをお受け取り後、24時間以内に【後払いドットコム / 届いてから払い】会員登録を
完了していただきますようお願いいたします。
・24時間以内にご登録が完了されない場合は仮登録のお手続きが無効と
なりますのであらかじめご了承願います。
・24時間を過ぎてしまった場合は、恐れ入りますが再度仮登録のお手続きを
お願いいたします。


------------------------------------
ご登録の手順について
------------------------------------

１.上記URLにアクセスし、画面にしたがって必要事項をご入力ください。

２.ご入力内容をご確認のうえ、お客様情報を登録してください。

３.【後払いドットコム / 届いてから払い】会員登録完了のお知らせ”というメールが届きます。

以上で【後払いドットコム / 届いてから払い】会員登録完了となります。



ご登録がうまくいかない場合は、
大変恐れ入りますがcustomer@ato-barai.comまで
お問い合わせをお願いいたします。

※営業時間外のお問い合わせにつきましては
ご返信にお時間をいただく場合がございます。


この度はお申込みありがとうございました。


-----------------------------------------------------------
【後払いドットコム / 届いてから払い】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：customer@ato-barai.com
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/07/14 11:25:36',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (209,55,'マイページ本登録完了メール（PC）','後払いドットコム / 届いてから払い','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQiAvIBskQkZPJCQkRiQrJGlKJyQkGyhC?=','customer@ato-barai.com',null,null,null,'【後払いドットコム / 届いてから払い】会員登録完了のお知らせ','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44OgIC8g5bGK44GE44Gm44GL44KJ5omV?=
 =?UTF-8?B?44GE?=','{MyPageNameKj}様

この度は【後払いドットコム / 届いてから払い】にお申込みいただき
まことにありがとうございます。

【後払いドットコム / 届いてから払い】での会員登録が完了いたしました。
下記ページよりログインしてご利用いただくことができます。
会員用マイページ　https://www.atobarai.jp/mypage
ID:ご登録のメールアドレス
パスワード：ご登録のパスワード




※当メールにお心当たりのない方は、
恐れ入りますが下記メールアドレスまでご連絡をお願いいたします。
また、当サービスに関するその他のお問い合わせも
下記アドレスにて承っております。

この度はお申込みありがとうございました。


-----------------------------------------------------------
【後払いドットコム / 届いてから払い】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：customer@ato-barai.com
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/07/14 11:27:37',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (210,56,'マイページ本登録完了メール（CEL）','後払いドットコム / 届いてから払い','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQiAvIBskQkZPJCQkRiQrJGlKJyQkGyhC?=','{ServiceMail}',null,null,null,'【後払いドットコム / 届いてから払い】会員登録完了のお知らせ','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44OgIC8g5bGK44GE44Gm44GL44KJ5omV?=
 =?UTF-8?B?44GE?=','{MyPageNameKj}様

この度は【後払いドットコム / 届いてから払い】にお申込みいただき
まことにありがとうございます。

【後払いドットコム / 届いてから払い】での会員登録が完了いたしました。
下記ページよりログインしてご利用いただくことができます。
会員用マイページ　https://www.atobarai.jp/mypage
ID:ご登録のメールアドレス
パスワード：ご登録のパスワード




※当メールにお心当たりのない方は、
恐れ入りますが下記メールアドレスまでご連絡をお願いいたします。
また、当サービスに関するその他のお問い合わせも
下記アドレスにて承っております。

この度はお申込みありがとうございました。


-----------------------------------------------------------
【後払いドットコム / 届いてから払い】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：customer@ato-barai.com
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/07/14 11:27:24',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (211,57,'マイページパスワード変更メール（PC）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】パスワード変更を承りました','{ServiceName}','{MyPageNameKj}様

いつも【{ServiceName}】をご利用いただき
まことにありがとうございます。

マイページのパスワード変更を承りましたのでご報告いたします。

下記URLへアクセスし、ログインを行ってください。

https://www.atobarai.jp/mypage

※当メールにお心当たりのない方は、
恐れ入りますが下記メールアドレスまでご連絡をお願いいたします。
また、当サービスに関するその他のお問い合わせも
下記アドレスにて承っております。

今後とも、当サービスをよろしくお願いいたします。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 6:02:18',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (212,58,'マイページパスワード変更メール（CEL）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】パスワード変更を承りました','{ServiceName}','{MyPageNameKj}様

いつも【{ServiceName}】をご利用いただき
まことにありがとうございます。

マイページのパスワード変更を承りましたのでご報告いたします。

下記URLへアクセスし、ログインを行ってください。

https://www.atobarai.jp/mypage

※当メールにお心当たりのない方は、
恐れ入りますが下記メールアドレスまでご連絡をお願いいたします。
また、当サービスに関するその他のお問い合わせも
下記アドレスにて承っております。

今後とも、当サービスをよろしくお願いいたします。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 6:02:27',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (213,59,'マイページ退会完了メール（PC）','後払いドットコム / 届いてから払い','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQiAvIBskQkZPJCQkRiQrJGlKJyQkGyhC?=','customer@ato-barai.com',null,null,null,'【後払いドットコム / 届いてから払い】退会完了のお知らせ','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44OgIC8g5bGK44GE44Gm44GL44KJ5omV?=
 =?UTF-8?B?44GE?=','{MyPageNameKj}様

この度は【後払いドットコム / 届いてから払い】をご利用いただき
まことにありがとうございました。

退会手続きが完了いたしましたのでご報告いたします。

またのご利用を心よりお待ちしております。



このメールは退会手続きをされたメールアドレスへ
自動で配信しております。
再度会員登録をされる際は、下記URLへアクセスくださいませ。

https://www.atobarai.jp/mypage

-----------------------------------------------------------
【後払いドットコム / 届いてから払い】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：customer@ato-barai.com
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/07/14 11:28:09',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (214,60,'マイページ退会完了メール（CEL）','後払いドットコム / 届いてから払い','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQiAvIBskQkZPJCQkRiQrJGlKJyQkGyhC?=','customer@ato-barai.com',null,null,null,'【後払いドットコム / 届いてから払い】退会完了のお知らせ','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44OgIC8g5bGK44GE44Gm44GL44KJ5omV?=
 =?UTF-8?B?44GE?=','{MyPageNameKj}様

この度は【後払いドットコム / 届いてから払い】をご利用いただき
まことにありがとうございました。

退会手続きが完了いたしましたのでご報告いたします。

またのご利用を心よりお待ちしております。



このメールは退会手続きをされたメールアドレスへ
自動で配信しております。
再度会員登録をされる際は、下記URLへアクセスくださいませ。

https://www.atobarai.jp/mypage

-----------------------------------------------------------
【後払いドットコム / 届いてから払い】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：customer@ato-barai.com
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/07/14 11:28:25',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (215,61,'社内与信保留メール','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】与信の件でご確認をお願いいたします','{ServiceName}','{EnterpriseNameKj}様

いつも大変お世話になっております。
【{ServiceName}】カスタマーセンターでございます。

本日与信をいただきました

{OrderId} {CustomerNameKj}様ですが

{PendingReason}

{PendingDate}まで与信保留とさせていただきますので
お手数ではございますが、正しい情報をご確認いただき
管理サイト上でご変更の処理をいただくか
弊社までご連絡をいただきますようお願いいたします。

■■■■■■■■■■■　ご注文修正をされる際の注意　■■■■■■■■■■■

修正内容をご入力いただいた後、「この内容で登録」をクリックすると
内容の確認画面に遷移します。内容をご確認のうえ、もう一度
「この内容で登録」をクリックすると修正が完了となります。
（※確認画面から別のページに移ってしまったり
閉じてしまったりすると、修正が反映されません。）

■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

【管理画面ＵＲＬ】
https://www.atobarai.jp/member/

ご不明な点などございましたら、お気軽にお問い合わせくださいませ。

何卒よろしくお願いいたします。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/10/30 21:58:02',9,'2022/04/20 6:03:08',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (216,81,'不足入金連絡メール（PC）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】ご入金額が不足しております','{ServiceName}','─────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございました。

{ReceiptDate}に{ReceiptClass}より{UseAmount}円のご入金をいただきましたが、
{ShortfallAmount}円が不足となっております。

大変お手数ですが不足分の{ShortfallAmount}円を本日より１週間以内に
下記口座までお振込みいただきますようお願いいたします。
(振込み手数料はお客様ご負担となります。)

【銀行振込口座】
三井住友銀行　新宿通支店
普通口座　8047001
カ）キャッチボール

【郵便振替口座】
口座記号：00120-7
口座番号：670031
株式会社キャッチボール

不明点などございましたら
お気軽にお問合せくださいませ。

何卒、よろしくお願いいたします。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 6:03:25',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (217,82,'不足入金連絡メール（CEL）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】ご入金額が不足しております','{ServiceName}','─────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございました。

{ReceiptDate}に{ReceiptClass}より{UseAmount}円のご入金をいただきましたが、
{ShortfallAmount}円が不足となっております。

大変お手数ですが不足分の{ShortfallAmount}円を本日より１週間以内に
下記口座までお振込みいただきますようお願いいたします。
(振込み手数料はお客様ご負担となります。)

【銀行振込口座】
三井住友銀行　新宿通支店
普通口座　8047001
カ）キャッチボール

【郵便振替口座】
口座記号：00120-7
口座番号：670031
株式会社キャッチボール

不明点などございましたら
お気軽にお問合せくださいませ。

何卒、よろしくお願いいたします。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2015/07/23 15:27:30',9,'2022/04/20 6:03:33',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (218,83,'マイページ身分証アップロードメール','後払いドットコム / 届いてから払い','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQiAvIBskQkZPJCQkRiQrJGlKJyQkGyhC?=','customer@ato-barai.com',null,null,null,'システムで自動的に設定されます','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44OgIC8g5bGK44GE44Gm44GL44KJ5omV?=
 =?UTF-8?B?44GE?=','システムで自動的に設定されます',0,'2015/07/23 15:27:30',9,'2022/07/14 11:28:53',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (219,86,'事業者メール登録バッチエラーメール','{ServiceName}','{ServiceName}','{ServiceMail}','後払いドットコム(オペレーター)',null,'cb-360resysmember@mb.scroll360.jp','【{ServiceName}】事業者メール登録バッチエラーメール','{ServiceName}','以下の事業者登録メールに対する処理に失敗しました。

------------------------------
{body}',0,'2015/10/05 18:15:52',9,'2022/04/20 5:38:37',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (220,88,'返金メール（キャンセル）(CEL)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】ご返金のご連絡','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}様で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございました。

{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
既に店舗様よりご注文キャンセルのご連絡をいただいておりましたので
ご返金させていただきたく、口座の確認のご連絡を差し上げました。

お手数ではございますが
・銀行名：
・支店名：
・口座種目：
・口座番号：
・口座名義(カナ)：
上記をご記入のうえ、当メールへご返信くださいませ。

不明点などございましたら、お気軽にお問合せくださいませ。

何卒よろしくお願いいたします。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------

',0,'2015/11/06 16:54:46',9,'2022/04/20 6:04:20',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (221,87,'返金メール（キャンセル）(PC)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】ご返金のご連絡','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}様で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございました。

{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
既に店舗様よりご注文キャンセルのご連絡をいただいておりましたので
ご返金させていただきたく、口座の確認のご連絡を差し上げました。

お手数ではございますが
・銀行名：
・支店名：
・口座種目：
・口座番号：
・口座名義(カナ)：
上記をご記入のうえ、当メールへご返信くださいませ。

不明点などございましたら、お気軽にお問合せくださいませ。

何卒よろしくお願いいたします。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------

',0,'2015/11/06 16:54:45',9,'2022/04/20 6:04:14',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (352,4,'請求書発行メール（PC）','【BASE後払い決済】','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'【BASE後払い決済】請求書発行案内　（ハガキで届きます）','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}様

先日は {SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

下記のショッピングのご請求書を本日発行いたしますので、請求書到着後、
請求書に記載されているお支払期限日までにお支払いいただきますよう、
お願い申し上げます。

【ご注文内容】

お支払者：{CustomerNameKj}　様

ご購入店舗名：{SiteNameKj}

ご購入日：{OrderDate}

お支払金額：{UseAmount}

ご購入商品明細：商品名／個数／購入品目計

{OrderItems}

決済手数料                              \{SettlementFee}

送料                                    \{DeliveryFee}

※郵送事故などにより、請求書が届かないことがございます。
　一週間ほどお待ちいただいても請求書が届かない場合には、大変お手数ですが、
　下記連絡先へご一報くださいますよう、お願い申し上げます。

※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
　届かない場合がございます。
　万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
　店舗様まで直接お問合せくださいませ。

※請求書並びに本メールが、商品よりも早く到着してしまうことがございますが、
　何卒ご容赦下さいますようお願い申し上げます。
　また、請求書内に明細が含まれておりますのでご確認くださいませ。


詳しくは下記URLをご覧下さい。
http://thebase.in/pages/help.html#category14_146

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、下記
　購入店舗様に直接お問合せください。


■商品・返品・配送に関するお問い合わせは：

直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

※商品到着から7日以降のキャンセルはできませんのでご注意ください。
店舗との同意の上キャンセルにより商品を返品される場合はその旨、
下記メールアドレスまで注文内容のご連絡をお願いします。

support@thebase.in

キャンセルを行なわないと
商品代金支払いの
ご請求届き続けますのでご注意ください。

{SiteNameKj} は BASE ( https://thebase.in ) で作成されています。 
BASEは誰でも簡単に無料でネットショップが開設できるサービスです。

■お支払いに関するお問い合わせは：

BASE 後払い決済　窓口
TEL:[03-6279-1149](平日土日9:00～18:00)
Mail: atobarai@thebase.in


───────────────────────────────────

BASE (ベイス)
https://thebase.in

 お問合せ先:[03-6279-1149]
 営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: atobarai@thebase.in

───────────────────────────────────',4,'2015/08/31 22:42:31',9,'2015/12/01 8:36:00',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (353,5,'請求書発行メール（CEL）','【BASE後払い決済】','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'【BASE後払い決済】請求書発行案内　（ハガキで届きます）','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}様

先日は {SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

下記のショッピングのご請求書を本日発行いたしますので、請求書到着後、
請求書に記載されているお支払期限日までにお支払いいただきますよう、
お願い申し上げます。

【ご注文内容】

お支払者：{CustomerNameKj}　様

ご購入店舗名：{SiteNameKj}

ご購入日：{OrderDate}

お支払金額：{UseAmount}

ご購入商品明細：商品名／個数／購入品目計

{OrderItems}

決済手数料                              \{SettlementFee}

送料                                    \{DeliveryFee}

※郵送事故などにより、請求書が届かないことがございます。
　一週間ほどお待ちいただいても請求書が届かない場合には、大変お手数ですが、
　下記連絡先へご一報くださいますよう、お願い申し上げます。

※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
　届かない場合がございます。
　万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
　店舗様まで直接お問合せくださいませ。

※請求書並びに本メールが、商品よりも早く到着してしまうことがございますが、
　何卒ご容赦下さいますようお願い申し上げます。
　また、請求書内に明細が含まれておりますのでご確認くださいませ。


詳しくは下記URLをご覧下さい。
http://thebase.in/pages/help.html#category14_146

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、下記
　購入店舗様に直接お問合せください。


■商品・返品・配送に関するお問い合わせは：

直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

※商品到着から7日以降のキャンセルはできませんのでご注意ください。
店舗との同意の上キャンセルにより商品を返品される場合はその旨、
下記メールアドレスまで注文内容のご連絡をお願いします。

support@thebase.in

キャンセルを行なわないと
商品代金支払いの
ご請求届き続けますのでご注意ください。

{SiteNameKj} は BASE ( https://thebase.in ) で作成されています。 
BASEは誰でも簡単に無料でネットショップが開設できるサービスです。

■お支払いに関するお問い合わせは：

BASE 後払い決済　窓口
TEL:[03-6279-1149](平日土日9:00～18:00)
Mail: atobarai@thebase.in


───────────────────────────────────

BASE (ベイス)
https://thebase.in

 お問合せ先:[03-6279-1149]
 営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: atobarai@thebase.in

───────────────────────────────────
',4,'2015/08/31 22:42:31',9,'2015/12/01 8:39:22',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (354,6,'入金確認メール（PC）','【BASE後払い決済】','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'【BASE後払い決済】ご入金確認のご報告','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}様

この度は、{SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

{ReceiptDate}に{CustomerNameKj}様からのご入金を
確認いたしましたのでご報告いたします。

以下が、今回ご入金いただいたご注文の内容でございます。

【領収済みご注文内容】

お支払者：{CustomerNameKj}　様

ご購入店舗名：{SiteNameKj}　

ご購入日：{OrderDate}

お支払金額：{UseAmount}

ご購入商品明細：商品名／個数／購入品目計

{OrderItems}

決済手数料                              \{SettlementFee}

送料                                    \{DeliveryFee}


ご購入店舗名：{SiteNameKj}

ご連絡先：{Phone}

住所：{Address}


ご不明な点などございましたら、お気軽にお問い合わせ下さい。


■商品・返品・配送に関するお問い合わせは：

直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}


{SiteNameKj} は BASE ( https://thebase.in ) で作成されています。 
BASEは誰でも簡単に無料でネットショップが開設できるサービスです。

■お支払いに関するお問い合わせは：

BASE 後払い決済　窓口
TEL:[03-6279-1149](平日土日9:00～18:00)
Mail: atobarai@thebase.in


───────────────────────────────────

BASE (ベイス)
https://thebase.in

 お問合せ先:[03-6279-1149]
 営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: atobarai@thebase.in

───────────────────────────────────',4,'2015/08/31 22:42:31',9,'2015/12/01 8:42:35',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (355,7,'入金確認メール（CEL）','【BASE後払い決済】','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'【BASE後払い決済】ご入金確認のご報告','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}様

この度は、{SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

{ReceiptDate}に{CustomerNameKj}様からのご入金を
確認いたしましたのでご報告いたします。

以下が、今回ご入金いただいたご注文の内容でございます。

【領収済みご注文内容】

お支払者：{CustomerNameKj}　様

ご購入店舗名：{SiteNameKj}　

ご購入日：{OrderDate}

お支払金額：{UseAmount}

ご購入商品明細：商品名／個数／購入品目計

{OrderItems}

決済手数料                              \{SettlementFee}

送料                                    \{DeliveryFee}


ご購入店舗名：{SiteNameKj}

ご連絡先：{Phone}

住所：{Address}


ご不明な点などございましたら、お気軽にお問い合わせ下さい。


■商品・返品・配送に関するお問い合わせは：

直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}


{SiteNameKj} は BASE ( https://thebase.in ) で作成されています。 
BASEは誰でも簡単に無料でネットショップが開設できるサービスです。

■お支払いに関するお問い合わせは：

BASE 後払い決済　窓口
TEL:[03-6279-1149](平日土日9:00～18:00)
Mail: atobarai@thebase.in


───────────────────────────────────

BASE (ベイス)
https://thebase.in

 お問合せ先:[03-6279-1149]
 営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: atobarai@thebase.in

───────────────────────────────────',4,'2015/08/31 22:42:31',9,'2015/12/01 11:07:32',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (359,11,'もうすぐお支払メール（PC）','【BASE後払い決済】','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'【BASE後払い決済】請求書到着確認のお知らせ','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は{SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

{IssueDate}に請求書をお送りいたしましたが、お手元にお届きでしょうか。

【ご注文内容】							
ご注文日：{OrderDate}							
ご注文店舗：{SiteNameKj} 
商品名（1品目のみ表示）：{OneOrderItem}						
ご請求金額：{UseAmount}	
お支払期限日（{LimitDate}）						
 							
※下記口座へ直接お振込みいただきましても、ご入金可能です。
(振込み手数料はお客様ご負担でございます)	
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

※請求書がまだ届いていない場合は大変お手数ですが、
早急に 03-6279-1149 にご一報ください。

※下記口座へ直接お振込みいただきましても、ご入金の確認は取れます。
  口座へお振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。


【銀行振込口座】
ジャパンネット銀行
モミジ支店
普通
3721018
ベイスカブシキガイシャ
ＢＡＳＥ株式会社

【郵便振込口座】
記号：001600-8
番号：450807
株式会社キャッチボール　ＢＡＳＥ専用口座
（カブシキガイシャキャッチボール　ベイスセンヨウコウザ）

※郵便局／銀行からお振込みいただく場合、振込手数料がお客様ご負担となります。

※郵便局の口座お持ちの場合は、郵便局のＡＴＭを利用して口座からご送金いただければ、
  郵便振込手数料はかかりません。(店舗決済手数料とは別です。)


万が一請求書がお手元に届いていない場合や、お支払に関しまして、ご不明な点等
ございましたら、下記までお気軽にお問い合わせ下さいませ。


※商品につきましても、メール便などの配送方法の場合には、
　配送事故などにより届かない場合がございます。
　万が一、商品が届いていない場合には大変お手数ではございますが、
　ご注文された店舗様まで直接お問合せくださいませ。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、注文された
　店舗様に直接お問合せください。


■商品・返品・配送に関するお問い合わせは：

直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}


{SiteNameKj}　 は BASE ( https://thebase.in ) で作成されています。 
BASEは誰でも簡単に無料でネットショップが開設できるサービスです。

■お支払いに関するお問い合わせは：

BASE 後払い決済　窓口
TEL:[03-6279-1149](平日土日9:00～18:00)
Mail: atobarai@thebase.in


───────────────────────────────────

BASE (ベイス)
https://thebase.in

 お問合せ先:[03-6279-1149]
 営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: atobarai@thebase.in

───────────────────────────────────',4,'2015/08/31 22:42:31',9,'2015/12/01 9:01:31',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (360,12,'もうすぐお支払メール（CEL）','【BASE後払い決済】','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'【BASE後払い決済】請求書到着確認のお知らせ','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は{SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

{IssueDate}に請求書をお送りいたしましたが、お手元にお届きでしょうか。

【ご注文内容】							
ご注文日：{OrderDate}							
ご注文店舗：{SiteNameKj} 
商品名（1品目のみ表示）：{OneOrderItem}						
ご請求金額：{UseAmount}	
お支払期限日（{LimitDate}）						
 							
※下記口座へ直接お振込みいただきましても、ご入金可能です。
(振込み手数料はお客様ご負担でございます)	
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

※請求書がまだ届いていない場合は大変お手数ですが、
早急に 03-6279-1149 にご一報ください。

※下記口座へ直接お振込みいただきましても、ご入金の確認は取れます。
  口座へお振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。


【銀行振込口座】
ジャパンネット銀行
モミジ支店
普通
3721018
ベイスカブシキガイシャ
ＢＡＳＥ株式会社

【郵便振込口座】
記号：001600-8
番号：450807
株式会社キャッチボール　ＢＡＳＥ専用口座
（カブシキガイシャキャッチボール　ベイスセンヨウコウザ）

※郵便局／銀行からお振込みいただく場合、振込手数料がお客様ご負担となります。

※郵便局の口座お持ちの場合は、郵便局のＡＴＭを利用して口座からご送金いただければ、
  郵便振込手数料はかかりません。(店舗決済手数料とは別です。)


万が一請求書がお手元に届いていない場合や、お支払に関しまして、ご不明な点等
ございましたら、下記までお気軽にお問い合わせ下さいませ。


※商品につきましても、メール便などの配送方法の場合には、
　配送事故などにより届かない場合がございます。
　万が一、商品が届いていない場合には大変お手数ではございますが、
　ご注文された店舗様まで直接お問合せくださいませ。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、注文された
　店舗様に直接お問合せください。


■商品・返品・配送に関するお問い合わせは：

直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}


{SiteNameKj}　 は BASE ( https://thebase.in ) で作成されています。 
BASEは誰でも簡単に無料でネットショップが開設できるサービスです。

■お支払いに関するお問い合わせは：

BASE 後払い決済　窓口
TEL:[03-6279-1149](平日土日9:00～18:00)
Mail: atobarai@thebase.in


───────────────────────────────────

BASE (ベイス)
https://thebase.in

 お問合せ先:[03-6279-1149]
 営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: atobarai@thebase.in

───────────────────────────────────',4,'2015/08/31 22:42:31',9,'2015/12/01 9:01:58',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (364,16,'戻り請求住所確認メール','【BASE後払い決済】','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'BASE【重要】ご住所確認の連絡です。','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────  

{CustomerNameKj}様

{ReceiptOrderDate}に{SiteNameKj}で、
後払い決済を選択していただきありがとうございます。

{ClaimDate}にお送りいたしました請求書が弊社に戻ってきておりますので、
ご住所の確認をさせていただきたくご連絡させていただきました。

（お客様住所）　{UnitingAddress}

上記住所に不備がありましたら、再度請求書を発行させていただきますので
ご連絡の程、よろしくお願い致します。

住所に不備がない場合でも、表札氏名が違っていた場合などで、郵便物が届かないケースも
ありますので、ご了承下さい。

また、銀行、郵便局からのご入金も可能ですので
口座番号をお送りさせていただきます。


【銀行振込口座】
ジャパンネット銀行
モミジ支店
普通
3721018
ベイスカブシキガイシャ
ＢＡＳＥ株式会社

【郵便振込口座】
記号：001600-8
番号：450807
株式会社キャッチボール　ＢＡＳＥ専用口座
（カブシキガイシャキャッチボール　ベイスセンヨウコウザ）


【ご請求明細】

商品名　　：{ItemNameKj}

商品代金　：{ItemAmount}円

送料　　　：{DeliveryFee}円

手数料　　：{SettlementFee}円

{OptionFee}

合計　　　：{UseAmount}円


■商品・返品・配送に関するお問い合わせは：

直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}


{SiteNameKj}　 は BASE ( https://thebase.in ) で作成されています。 
BASEは誰でも簡単に無料でネットショップが開設できるサービスです。

■お支払いに関するお問い合わせは：

BASE 後払い決済　窓口
TEL:[03-6279-1149](平日土日9:00～18:00)
Mail: atobarai@thebase.in


───────────────────────────────────

BASE (ベイス)
https://thebase.in

 お問合せ先:[03-6279-1149]
 営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: atobarai@thebase.in

───────────────────────────────────',4,'2015/08/31 22:42:31',9,'2015/12/01 10:14:50',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (374,32,'請求書破棄メール（PC）','【BASE後払い決済】','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'【BASE後払い決済】請求書破棄のお願い','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

先日は{SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

{CancelDate}に{SiteNameKj}よりBASE後払い窓口へ
キャンセルのご連絡をいただきましたが、
既に請求書をお送りしてしまっておりますので、
大変お手数ではございますが破棄していただくようお願いいたします。

この度は{SiteNameKj}とBASE後払い決済をご利用いただき
まことにありがとうございました。

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。


■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}


{SiteNameKj}　 は BASE ( https://thebase.in ) で作成されています。 
BASEは誰でも簡単に無料でネットショップが開設できるサービスです。


■お支払いに関するお問い合わせは：
BASE 後払い決済　窓口
TEL:[03-6279-1149](平日土日9:00～18:00)
Mail: atobarai@thebase.in

────────────────────────────────
BASE (ベイス)
https://thebase.in
 お問合せ先:[03-6279-1149]
 営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: atobarai@thebase.in
────────────────────────────────',4,'2015/07/23 15:27:30',9,'2015/12/01 11:03:22',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (375,33,'請求書破棄メール（CEL）','【BASE後払い決済】','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'【BASE後払い決済】請求書破棄のお願い','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

先日は{SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

{CancelDate}に{SiteNameKj}よりBASE後払い窓口へ
キャンセルのご連絡をいただきましたが、
既に請求書をお送りしてしまっておりますので、
大変お手数ではございますが破棄していただくようお願いいたします。

この度は{SiteNameKj}とBASE後払い決済をご利用いただき
まことにありがとうございました。

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。


■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}


{SiteNameKj}　 は BASE ( https://thebase.in ) で作成されています。 
BASEは誰でも簡単に無料でネットショップが開設できるサービスです。


■お支払いに関するお問い合わせは：
BASE 後払い決済　窓口
TEL:[03-6279-1149](平日土日9:00～18:00)
Mail: atobarai@thebase.in

────────────────────────────────
BASE (ベイス)
https://thebase.in
 お問合せ先:[03-6279-1149]
 営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: atobarai@thebase.in
────────────────────────────────',4,'2015/07/23 15:27:30',9,'2015/12/01 11:02:59',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (376,34,'過剰入金メール（PC）','【BASE後払い決済】','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'【BASE後払い決済】ご返金のご連絡','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
BASE後払い決済をご利用いただきまして
まことにありがとうございました。

{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
{OverReceiptAmount}円多くお支払いいただいておりましたので
ご返金させていただきたく、口座の確認のご連絡を差し上げました。

お手数ではございますが
・銀行名：
・支店名：
・口座種目：
・口座番号：
・口座名義(カナ)：
上記をご記入のうえ、当メールへご返信くださいませ。

何卒よろしくお願いいたします。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。


■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　


{SiteNameKj}　 は BASE ( https://thebase.in ) で作成されています。 
BASEは誰でも簡単に無料でネットショップが開設できるサービスです。


■お支払いに関するお問い合わせは：
BASE 後払い決済　窓口
TEL:[03-6279-1149](平日土日9:00～18:00)
Mail: atobarai@thebase.in

────────────────────────────────
BASE (ベイス)
https://thebase.in
 お問合せ先:[03-6279-1149]
 営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: atobarai@thebase.in
────────────────────────────────
',4,'2015/07/23 15:27:30',9,'2015/12/01 10:29:20',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (377,35,'過剰入金メール（CEL）','【BASE後払い決済】','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'【BASE後払い決済】ご返金のご連絡','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
BASE後払い決済をご利用いただきまして
まことにありがとうございました。

{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
{OverReceiptAmount}円多くお支払いいただいておりましたので
ご返金させていただきたく、口座の確認のご連絡を差し上げました。

お手数ではございますが
・銀行名：
・支店名：
・口座種目：
・口座番号：
・口座名義(カナ)：
上記をご記入のうえ、当メールへご返信くださいませ。

何卒よろしくお願いいたします。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。


■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　


{SiteNameKj}　 は BASE ( https://thebase.in ) で作成されています。 
BASEは誰でも簡単に無料でネットショップが開設できるサービスです。


■お支払いに関するお問い合わせは：
BASE 後払い決済　窓口
TEL:[03-6279-1149](平日土日9:00～18:00)
Mail: atobarai@thebase.in

────────────────────────────────
BASE (ベイス)
https://thebase.in
 お問合せ先:[03-6279-1149]
 営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: atobarai@thebase.in
────────────────────────────────
',4,'2015/07/23 15:27:30',9,'2015/12/01 10:30:21',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (379,39,'支払期限超過メール（再１）（PC）','株式会社キャッチボール','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'【ご確認ください】：{OrderDate}　{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は{SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

{IssueDate}に請求書をお送りいたしましたが、
本日現在ご入金の確認ができておりません。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
ご請求金額：{TotalAmount}

※万が一、お支払期限を過ぎてしまいますと
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】							
三井住友銀行　新宿通支店
普通口座　8047001
カ）キャッチボール				

【郵便振替口座】							
口座記号：00120‐7							
口座番号：670031							
カ）キャッチボール							

万が一、請求書がお手元に届いていない場合や
その他ご不明な点、ご入金のご相談等は
03-6908-6662(9：00～18：00)までお問い合わせ下さい。


※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、
ご注文された店舗様まで直接お問合せくださいませ。


※メールにてお問合せをいただく場合は、
必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。


※キャンセル（解約申請）されている場合でも、
行き違いにて当メールが配信されてしまう場合がございます。
その際は大変お手数ですが注文された店舗様に直接お問合せください。


■商品・返品・配送に関するお問い合わせは：
　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}


■お支払いに関するお問い合わせは：
  お問合せ先：03-6908-6662
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  運営会社：株式会社キャッチボール  
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:59:17',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (380,40,'支払期限超過メール（再１）（CEL）','株式会社キャッチボール','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'【ご確認ください】：{OrderDate}　{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は{SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

{IssueDate}に請求書をお送りいたしましたが、
本日現在ご入金の確認ができておりません。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
ご請求金額：{TotalAmount}

※万が一、お支払期限を過ぎてしまいますと
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】							
三井住友銀行　新宿通支店
普通口座　8047001
カ）キャッチボール				

【郵便振替口座】							
口座記号：00120‐7							
口座番号：670031							
カ）キャッチボール							

万が一、請求書がお手元に届いていない場合や
その他ご不明な点、ご入金のご相談等は
03-6908-6662(9：00～18：00)までお問い合わせ下さい。


※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、
ご注文された店舗様まで直接お問合せくださいませ。


※メールにてお問合せをいただく場合は、
必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。


※キャンセル（解約申請）されている場合でも、
行き違いにて当メールが配信されてしまう場合がございます。
その際は大変お手数ですが注文された店舗様に直接お問合せください。


■商品・返品・配送に関するお問い合わせは：
　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}


■お支払いに関するお問い合わせは：
  お問合せ先：03-6908-6662
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  運営会社：株式会社キャッチボール  
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:58:55',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (381,41,'支払期限超過メール（再３）（PC）','株式会社キャッチボール','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'【ご確認ください】：{OrderDate}　{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は{SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

{ClaimDate}に再請求書をお送りいたしましたが、
本日現在ご入金の確認ができておりません。

大変お手数ですが、上記再請求書をご確認いただき
お支払いくださいますようお願いいたします。


【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※万が一、お支払期限を過ぎてしまいますと
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】							
三井住友銀行　新宿通支店
普通口座　8047001
カ）キャッチボール				

【郵便振替口座】							
口座記号：00120‐7							
口座番号：670031							
カ）キャッチボール							

万が一、請求書がお手元に届いていない場合や
その他ご不明な点、ご入金のご相談等は
03-6908-6662(9：00～18：00)までお問い合わせ下さい。


※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、
ご注文された店舗様まで直接お問合せくださいませ。


※メールにてお問合せをいただく場合は、
必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。


※キャンセル（解約申請）されている場合でも、
行き違いにて当メールが配信されてしまう場合がございます。
その際は大変お手数ですが注文された店舗様に直接お問合せください。


■商品・返品・配送に関するお問い合わせは：
　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}


■お支払いに関するお問い合わせは：
  お問合せ先：03-6908-6662
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  運営会社：株式会社キャッチボール  
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:41:14',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (382,42,'支払期限超過メール（再３）（CEL）','株式会社キャッチボール','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'【ご確認ください】：{OrderDate}　{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は{SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

{ClaimDate}に再請求書をお送りいたしましたが、
本日現在ご入金の確認ができておりません。

大変お手数ですが、上記再請求書をご確認いただき
お支払いくださいますようお願いいたします。


【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※万が一、お支払期限を過ぎてしまいますと
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】							
三井住友銀行　新宿通支店
普通口座　8047001
カ）キャッチボール				

【郵便振替口座】							
口座記号：00120‐7							
口座番号：670031							
カ）キャッチボール							

万が一、請求書がお手元に届いていない場合や
その他ご不明な点、ご入金のご相談等は
03-6908-6662(9：00～18：00)までお問い合わせ下さい。


※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、
ご注文された店舗様まで直接お問合せくださいませ。


※メールにてお問合せをいただく場合は、
必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。


※キャンセル（解約申請）されている場合でも、
行き違いにて当メールが配信されてしまう場合がございます。
その際は大変お手数ですが注文された店舗様に直接お問合せください。


■商品・返品・配送に関するお問い合わせは：
　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}


■お支払いに関するお問い合わせは：
  お問合せ先：03-6908-6662
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  運営会社：株式会社キャッチボール  
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:58:31',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (383,43,'支払期限超過メール（再４）（PC）','株式会社キャッチボール','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'【ご確認ください】：{OrderDate}　{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は {SiteNameKj}でお買い物をして頂き
まことにありがとうございます。

{ClaimDate}に督促状をお送りいたしましたが、
本日現在ご入金の確認ができておりません。

つきましては、未納分のお支払いにつき
至急ご対応くださいますようお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※万が一、お支払期限を過ぎてしまいますと
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】							
三井住友銀行　新宿通支店
普通口座　8047001
カ）キャッチボール				

【郵便振替口座】							
口座記号：00120‐7							
口座番号：670031							
カ）キャッチボール							

万が一、請求書がお手元に届いていない場合や
その他ご不明な点、ご入金のご相談等は
03-6908-6662(9：00～18：00)までお問い合わせ下さい。


※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、
ご注文された店舗様まで直接お問合せくださいませ。


※メールにてお問合せをいただく場合は、
必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。


※キャンセル（解約申請）されている場合でも、
行き違いにて当メールが配信されてしまう場合がございます。
その際は大変お手数ですが注文された店舗様に直接お問合せください。


■商品・返品・配送に関するお問い合わせは：
　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}


■お支払いに関するお問い合わせは：
  お問合せ先：03-6908-6662
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  運営会社：株式会社キャッチボール  
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:58:03',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (384,44,'支払期限超過メール（再４）（CEL）','株式会社キャッチボール','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'【ご確認ください】：{OrderDate}　{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は {SiteNameKj}でお買い物をして頂き
まことにありがとうございます。

{ClaimDate}に督促状をお送りいたしましたが、
本日現在ご入金の確認ができておりません。

つきましては、未納分のお支払いにつき
至急ご対応くださいますようお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※万が一、お支払期限を過ぎてしまいますと
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】							
三井住友銀行　新宿通支店
普通口座　8047001
カ）キャッチボール				

【郵便振替口座】							
口座記号：00120‐7							
口座番号：670031							
カ）キャッチボール							

万が一、請求書がお手元に届いていない場合や
その他ご不明な点、ご入金のご相談等は
03-6908-6662(9：00～18：00)までお問い合わせ下さい。


※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、
ご注文された店舗様まで直接お問合せくださいませ。


※メールにてお問合せをいただく場合は、
必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。


※キャンセル（解約申請）されている場合でも、
行き違いにて当メールが配信されてしまう場合がございます。
その際は大変お手数ですが注文された店舗様に直接お問合せください。


■商品・返品・配送に関するお問い合わせは：
　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}


■お支払いに関するお問い合わせは：
  お問合せ先：03-6908-6662
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  運営会社：株式会社キャッチボール  
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:57:37',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (385,45,'支払期限超過メール（再５）（PC）','株式会社キャッチボール','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'【ご確認ください】：{OrderDate}　{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は{SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

{ClaimDate}に督促状をお送りいたしましたが、
本日現在ご入金の確認ができておりません。


上記督促状でもお知らせしている通り、
お支払いが確認できない場合
お客様の信用取引など不利益が生じる可能性がございます。
つきましては速やかなご対応をお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※万が一、お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】							
三井住友銀行　新宿通支店
普通口座　8047001
カ）キャッチボール				

【郵便振替口座】							
口座記号：00120‐7							
口座番号：670031							
カ）キャッチボール							

万が一、請求書がお手元に届いていない場合や
その他ご不明な点、ご入金のご相談等は
03-6908-6662(9：00～18：00)までお問い合わせ下さい。


※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、
ご注文された店舗様まで直接お問合せくださいませ。


※メールにてお問合せをいただく場合は、
必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。


※キャンセル（解約申請）されている場合でも、
行き違いにて当メールが配信されてしまう場合がございます。
その際は大変お手数ですが注文された店舗様に直接お問合せください。


■商品・返品・配送に関するお問い合わせは：
　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}


■お支払いに関するお問い合わせは：
  お問合せ先：03-6908-6662
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  運営会社：株式会社キャッチボール  
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:57:23',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (386,46,'支払期限超過メール（再５）（CEL）','株式会社キャッチボール','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'【ご確認ください】：{OrderDate}　{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は{SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

{ClaimDate}に督促状をお送りいたしましたが、
本日現在ご入金の確認ができておりません。


上記督促状でもお知らせしている通り、
お支払いが確認できない場合
お客様の信用取引など不利益が生じる可能性がございます。
つきましては速やかなご対応をお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※万が一、お支払期限を過ぎてしまいますと
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】							
三井住友銀行　新宿通支店
普通口座　8047001
カ）キャッチボール				

【郵便振替口座】							
口座記号：00120‐7							
口座番号：670031							
カ）キャッチボール							

万が一、請求書がお手元に届いていない場合や
その他ご不明な点、ご入金のご相談等は
03-6908-6662(9：00～18：00)までお問い合わせ下さい。


※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、
ご注文された店舗様まで直接お問合せくださいませ。


※メールにてお問合せをいただく場合は、
必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。


※キャンセル（解約申請）されている場合でも、
行き違いにて当メールが配信されてしまう場合がございます。
その際は大変お手数ですが注文された店舗様に直接お問合せください。


■商品・返品・配送に関するお問い合わせは：
　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}


■お支払いに関するお問い合わせは：
  お問合せ先：03-6908-6662
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  運営会社：株式会社キャッチボール  
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:57:03',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (387,47,'支払期限超過メール（再６）（PC）','株式会社キャッチボール','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'【ご確認ください】：{OrderDate}　{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は{SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

{ClaimDate}に督促状をお送りいたしましたが、
本日現在ご入金の確認ができておりません。

このまま未入金状態が継続されますと、
当社での対応が困難となり
通知記載の対応となる場合があります。
つきましては至急お支払いについて
ご対応くださいますようお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※万が一、お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】							
三井住友銀行　新宿通支店
普通口座　8047001
カ）キャッチボール				

【郵便振替口座】							
口座記号：00120‐7							
口座番号：670031							
カ）キャッチボール							

万が一、請求書がお手元に届いていない場合や
その他ご不明な点、ご入金のご相談等は
03-6908-6662(9：00～18：00)までお問い合わせ下さい。


※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、
ご注文された店舗様まで直接お問合せくださいませ。


※メールにてお問合せをいただく場合は、
必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。


※キャンセル（解約申請）されている場合でも、
行き違いにて当メールが配信されてしまう場合がございます。
その際は大変お手数ですが注文された店舗様に直接お問合せください。


■商品・返品・配送に関するお問い合わせは：
　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}


■お支払いに関するお問い合わせは：
  お問合せ先：03-6908-6662
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  運営会社：株式会社キャッチボール  
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:50:22',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (388,48,'支払期限超過メール（再６）（CEL）','株式会社キャッチボール','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'【ご確認ください】：{OrderDate}　{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は{SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

{ClaimDate}に督促状をお送りいたしましたが、
本日現在ご入金の確認ができておりません。

このまま未入金状態が継続されますと、
当社での対応が困難となり
通知記載の対応となる場合があります。
つきましては至急お支払いについて
ご対応くださいますようお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※万が一、お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】							
三井住友銀行　新宿通支店
普通口座　8047001
カ）キャッチボール				

【郵便振替口座】							
口座記号：00120‐7							
口座番号：670031							
カ）キャッチボール							

万が一、請求書がお手元に届いていない場合や
その他ご不明な点、ご入金のご相談等は
03-6908-6662(9：00～18：00)までお問い合わせ下さい。


※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、
ご注文された店舗様まで直接お問合せくださいませ。


※メールにてお問合せをいただく場合は、
必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。


※キャンセル（解約申請）されている場合でも、
行き違いにて当メールが配信されてしまう場合がございます。
その際は大変お手数ですが注文された店舗様に直接お問合せください。


■商品・返品・配送に関するお問い合わせは：
　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}


■お支払いに関するお問い合わせは：
  お問合せ先：03-6908-6662
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  運営会社：株式会社キャッチボール  
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:50:46',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (389,49,'支払期限超過メール（再７）（PC）','株式会社キャッチボール','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'【ご確認ください】：{OrderDate}　{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は{SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

{ClaimDate}に督促状をお送りいたしましたが、
本日現在ご入金の確認ができておりません。

再三にわたり、ご返済に対し履行頂くようご通知いたしましたが
貴殿より誠意のあるご対応を頂いていない状況となっております。
今後につきましても、ご連絡・お支払いが確認できない場合は
弁護士への回収委任もしくは法的手続きに移行せざるおえません。
しかしながら、弊社債権管理部では
貴殿の債務履行に対し解決を図る為の相談窓口を設けており
相談による解決も可能な場合もございます。
つきましては、解決に向け至急ご連絡いただけますようお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※万が一、お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】							
三井住友銀行　新宿通支店
普通口座　8047001
カ）キャッチボール				

【郵便振替口座】							
口座記号：00120‐7							
口座番号：670031							
カ）キャッチボール							

万が一、請求書がお手元に届いていない場合や
その他ご不明な点、ご入金のご相談等は
03-6908-6662(9：00～18：00)までお問い合わせ下さい。


※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、
ご注文された店舗様まで直接お問合せくださいませ。


※メールにてお問合せをいただく場合は、
必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。


※キャンセル（解約申請）されている場合でも、
行き違いにて当メールが配信されてしまう場合がございます。
その際は大変お手数ですが注文された店舗様に直接お問合せください。


■商品・返品・配送に関するお問い合わせは：
　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}


■お支払いに関するお問い合わせは：
  お問合せ先：03-6908-6662
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  運営会社：株式会社キャッチボール  
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:51:44',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (390,50,'支払期限超過メール（再７）（CEL）','株式会社キャッチボール','=?UTF-8?B?GyRCM3Q8MDJxPFIlLSVjJUMlQSVcITwlaxsoQg==?=','customer2@ato-barai.com',null,null,null,'【ご確認ください】：{OrderDate}　{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5qCq5byP5Lya56S+44Kt44Oj44OD44OB44Oc44O844Or?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は{SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

{ClaimDate}に督促状をお送りいたしましたが、
本日現在ご入金の確認ができておりません。

再三にわたり、ご返済に対し履行頂くようご通知いたしましたが
貴殿より誠意のあるご対応を頂いていない状況となっております。
今後につきましても、ご連絡・お支払いが確認できない場合は
弁護士への回収委任もしくは法的手続きに移行せざるおえません。
しかしながら、弊社債権管理部では
貴殿の債務履行に対し解決を図る為の相談窓口を設けており
相談による解決も可能な場合もございます。
つきましては、解決に向け至急ご連絡いただけますようお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※万が一、お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】							
三井住友銀行　新宿通支店
普通口座　8047001
カ）キャッチボール				

【郵便振替口座】							
口座記号：00120‐7							
口座番号：670031							
カ）キャッチボール							

万が一、請求書がお手元に届いていない場合や
その他ご不明な点、ご入金のご相談等は
03-6908-6662(9：00～18：00)までお問い合わせ下さい。


※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、
ご注文された店舗様まで直接お問合せくださいませ。


※メールにてお問合せをいただく場合は、
必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。


※キャンセル（解約申請）されている場合でも、
行き違いにて当メールが配信されてしまう場合がございます。
その際は大変お手数ですが注文された店舗様に直接お問合せください。


■商品・返品・配送に関するお問い合わせは：
　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}


■お支払いに関するお問い合わせは：
  お問合せ先：03-6908-6662
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  運営会社：株式会社キャッチボール  
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F',4,'2015/07/23 15:27:30',9,'2015/12/01 10:52:16',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (402,81,'不足入金連絡メール（PC）','【BASE後払い決済】','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'【BASE後払い決済】ご入金額が不足しております','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

先日は{SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

{ReceiptDate}に{ReceiptClass}より{UseAmount}円のご入金をいただきましたが、
{ShortfallAmount}円が不足となっております。

大変お手数ですが不足分の{ShortfallAmount}円を
下記口座までお振込みいただきますようお願いいたします。

【銀行振込口座】
ジャパンネット銀行　モミジ支店
普通　3721018
ＢＡＳＥ株式会社
（ベイスカブシキガイシャ）

【郵便振込口座】
記号：001600-8
番号：450807
株式会社キャッチボール　ＢＡＳＥ専用口座
（カブシキガイシャキャッチボール　ベイスセンヨウコウザ）

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。


■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}


{SiteNameKj}　 は BASE ( https://thebase.in ) で作成されています。 
BASEは誰でも簡単に無料でネットショップが開設できるサービスです。


■お支払いに関するお問い合わせは：
BASE 後払い決済　窓口
TEL:[03-6279-1149](平日土日9:00～18:00)
Mail: atobarai@thebase.in

────────────────────────────────
BASE (ベイス)
https://thebase.in
 お問合せ先:[03-6279-1149]
 営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: atobarai@thebase.in
────────────────────────────────',4,'2015/07/23 15:27:30',9,'2015/12/01 10:53:30',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (403,82,'不足入金連絡メール（CEL）','【BASE後払い決済】','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'【BASE後払い決済】ご入金額が不足しております','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

先日は{SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

{ReceiptDate}に{ReceiptClass}より{UseAmount}円のご入金をいただきましたが、
{ShortfallAmount}円が不足となっております。

大変お手数ですが不足分の{ShortfallAmount}円を
下記口座までお振込みいただきますようお願いいたします。

【銀行振込口座】
ジャパンネット銀行　モミジ支店
普通　3721018
ＢＡＳＥ株式会社
（ベイスカブシキガイシャ）

【郵便振込口座】
記号：001600-8
番号：450807
株式会社キャッチボール　ＢＡＳＥ専用口座
（カブシキガイシャキャッチボール　ベイスセンヨウコウザ）

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。


■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}


{SiteNameKj}は BASE ( https://thebase.in ) で作成されています。 
BASEは誰でも簡単に無料でネットショップが開設できるサービスです。


■お支払いに関するお問い合わせは：
BASE 後払い決済　窓口
TEL:[03-6279-1149](平日土日9:00～18:00)
Mail: atobarai@thebase.in

────────────────────────────────
BASE (ベイス)
https://thebase.in
 お問合せ先:[03-6279-1149]
 営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: atobarai@thebase.in
────────────────────────────────',4,'2015/07/23 15:27:30',9,'2015/12/01 10:54:10',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (406,88,'返金メール（キャンセル）(CEL)','【BASE後払い決済】','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'【BASE後払い決済】ご返金のご連絡','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

先日は{SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
既に店舗様よりご注文キャンセルのご連絡をいただいておりましたので
ご返金させていただきたく、口座の確認のご連絡を差し上げました。

お手数ではございますが
・銀行名：
・支店名：
・口座種目：
・口座番号：
・口座名義(カナ)：
上記をご記入のうえ、当メールへご返信くださいませ。

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。


■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}


{SiteNameKj}　 は BASE ( https://thebase.in ) で作成されています。 
BASEは誰でも簡単に無料でネットショップが開設できるサービスです。


■お支払いに関するお問い合わせは：
BASE 後払い決済　窓口
TEL:[03-6279-1149](平日土日9:00～18:00)
Mail: atobarai@thebase.in

────────────────────────────────
BASE (ベイス)
https://thebase.in
 お問合せ先:[03-6279-1149]
 営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: atobarai@thebase.in
────────────────────────────────',4,'2015/11/06 16:54:46',9,'2015/12/01 10:55:43',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (407,87,'返金メール（キャンセル）(PC)','【BASE後払い決済】','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','atobarai@thebase.in',null,null,null,'【BASE後払い決済】ご返金のご連絡','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

先日は{SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
既に店舗様よりご注文キャンセルのご連絡をいただいておりましたので
ご返金させていただきたく、口座の確認のご連絡を差し上げました。

お手数ではございますが
・銀行名：
・支店名：
・口座種目：
・口座番号：
・口座名義(カナ)：
上記をご記入のうえ、当メールへご返信くださいませ。

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。


■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}


{SiteNameKj}　 は BASE ( https://thebase.in ) で作成されています。 
BASEは誰でも簡単に無料でネットショップが開設できるサービスです。


■お支払いに関するお問い合わせは：
BASE 後払い決済　窓口
TEL:[03-6279-1149](平日土日9:00～18:00)
Mail: atobarai@thebase.in

────────────────────────────────
BASE (ベイス)
https://thebase.in
 お問合せ先:[03-6279-1149]
 営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: atobarai@thebase.in
────────────────────────────────',4,'2015/11/06 16:54:45',9,'2015/12/01 10:55:13',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (412,32,'請求書破棄メール（PC）','Ｅストアー　後払い窓口','=?UTF-8?B?GyRCI0UlOSVIJSIhPCEhOGVKJyQkQWs4fRsoQg==?=','ato-barai.sp@estore.co.jp',null,null,null,'【ご連絡】請求書破棄のお願い（{OrderId}）','=?UTF-8?B?77yl44K544OI44Ki44O844CA5b6M5omV44GE56qT5Y+j?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

先日はご注文いただきまして、誠にありがとうございます。

{CancelDate}に{SiteNameKj}より後払い窓口へ
キャンセルのご連絡をいただきましたが、
既に請求書をお送りしてしまっておりますので、
大変お手数ではございますが破棄していただくようお願いいたします。

この度は{SiteNameKj}と弊社後払いサービスをご利用いただき
まことにありがとうございました。

今後とも何卒、よろしくお願いいたします。


■商品・返品・配送に関するお問い合わせは：
　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}


■お支払いに関するお問い合わせは：
  お問合せ先：03-6908-5100
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: ato-barai.sp@estore.co.j
  運営会社：株式会社Ｅストアー　後払い窓口  
　住所：〒105-0003 東京都港区西新橋1-10-2　住友生命西新橋ビル9F
',1,'2015/12/01 12:22:35',32,'2015/12/01 12:23:44',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (413,33,'請求書破棄メール（CEL）','Ｅストアー　後払い窓口','=?UTF-8?B?GyRCI0UlOSVIJSIhPCEhOGVKJyQkQWs4fRsoQg==?=','ato-barai.sp@estore.co.jp',null,null,null,'【ご連絡】請求書破棄のお願い（{OrderId}）','=?UTF-8?B?77yl44K544OI44Ki44O844CA5b6M5omV44GE56qT5Y+j?=','
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇


{CustomerNameKj}様

先日はご注文いただきまして、誠にありがとうございます。

{CancelDate}に{SiteNameKj}より後払い窓口へ
キャンセルのご連絡をいただきましたが、
既に請求書をお送りしてしまっておりますので、
大変お手数ではございますが破棄していただくようお願いいたします。

この度は{SiteNameKj}と弊社後払いサービスをご利用いただき
まことにありがとうございました。

今後とも何卒、よろしくお願いいたします。


■商品・返品・配送に関するお問い合わせは：
　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}


■お支払いに関するお問い合わせは：
  お問合せ先：03-6908-5100
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: ato-barai.sp@estore.co.j
  運営会社：株式会社Ｅストアー　後払い窓口  
　住所：〒105-0003 東京都港区西新橋1-10-2　住友生命西新橋ビル9F
',1,'2015/12/01 12:24:08',32,'2015/12/01 12:50:25',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (414,34,'過剰入金メール（PC）','Ｅストアー　後払い窓口','=?UTF-8?B?GyRCI0UlOSVIJSIhPCEhOGVKJyQkQWs4fRsoQg==?=','ato-barai.sp@estore.co.jp',null,null,null,'【ご確認】ご返金のご連絡','=?UTF-8?B?77yl44K544OI44Ki44O844CA5b6M5omV44GE56qT5Y+j?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

先日はご注文いただきまして、誠にありがとうございます。

{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
{OverReceiptAmount}円多くお支払いいただいておりましたので
ご返金させていただきたく、口座の確認のご連絡を差し上げました。

お手数ではございますが
・銀行名：
・支店名：
・口座種目：
・口座番号：
・口座名義(カナ)：
上記をご記入のうえ、当メールへご返信くださいませ。

何卒よろしくお願いいたします。


■商品・返品・配送に関するお問い合わせは：
　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}


■お支払いに関するお問い合わせは：
  お問合せ先：03-6908-5100
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: ato-barai.sp@estore.co.j
  運営会社：株式会社Ｅストアー　後払い窓口  
　住所：〒105-0003 東京都港区西新橋1-10-2　住友生命西新橋ビル9F
',1,'2015/12/01 12:24:46',32,'2015/12/01 12:24:46',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (415,35,'過剰入金メール（CEL）','Ｅストアー　後払い窓口','=?UTF-8?B?GyRCI0UlOSVIJSIhPCEhOGVKJyQkQWs4fRsoQg==?=','ato-barai.sp@estore.co.jp',null,null,null,'【ご確認】ご返金のご連絡','=?UTF-8?B?77yl44K544OI44Ki44O844CA5b6M5omV44GE56qT5Y+j?=','
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇


{CustomerNameKj}様

先日はご注文いただきまして、誠にありがとうございます。

{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
{OverReceiptAmount}円多くお支払いいただいておりましたので
ご返金させていただきたく、口座の確認のご連絡を差し上げました。

お手数ではございますが
・銀行名：
・支店名：
・口座種目：
・口座番号：
・口座名義(カナ)：
上記をご記入のうえ、当メールへご返信くださいませ。

何卒よろしくお願いいたします。


■商品・返品・配送に関するお問い合わせは：
　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}


■お支払いに関するお問い合わせは：
  お問合せ先：03-6908-5100
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: ato-barai.sp@estore.co.j
  運営会社：株式会社Ｅストアー　後払い窓口  
　住所：〒105-0003 東京都港区西新橋1-10-2　住友生命西新橋ビル9F
',1,'2015/12/01 12:25:09',32,'2015/12/01 12:50:43',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (416,39,'支払期限超過メール（再１）（PC）','','','',null,null,null,'','','',1,'2015/12/01 12:31:18',32,'2015/12/01 12:44:14',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (417,40,'支払期限超過メール（再１）（CEL）','','','',null,null,null,'','','',1,'2015/12/01 12:31:53',32,'2015/12/01 12:44:00',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (418,41,'支払期限超過メール（再３）（PC）','','','',null,null,null,'','','',1,'2015/12/01 12:41:45',32,'2015/12/01 12:43:36',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (419,81,'不足入金連絡メール（PC）','Ｅストアー　後払い窓口','=?UTF-8?B?GyRCI0UlOSVIJSIhPCEhOGVKJyQkQWs4fRsoQg==?=','ato-barai.sp@estore.co.jp',null,null,null,'【ご連絡】ご入金額が不足しております','=?UTF-8?B?77yl44K544OI44Ki44O844CA5b6M5omV44GE56qT5Y+j?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

先日はご注文いただきまして、誠にありがとうございます。

{ReceiptDate}に{ReceiptClass}より{UseAmount}円のご入金をいただきましたが、
{ShortfallAmount}円が不足となっております。

大変お手数ですが不足分の{ShortfallAmount}円を
下記口座までお振込みいただきますようお願いいたします。

【銀行振込口座】
ジャパンネット銀行 すずめ支店
普通預金　6291494
株式会社キャッチボール／Ｅストアー専用口座

【郵便振替口座】
口座記号：00140-5
口座番号：665145
株式会社キャッチボール　Ｅストアー専用

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。


■商品・返品・配送に関するお問い合わせは：
　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}


■お支払いに関するお問い合わせは：
  お問合せ先：03-6908-5100
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: ato-barai.sp@estore.co.jp
  運営会社：株式会社Ｅストアー　後払い窓口 
  住所：〒105-0003　東京都港区西新橋1-10-2  住友生命西新橋ビル９F
',1,'2015/12/01 12:51:38',32,'2015/12/01 15:06:22',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (420,82,'不足入金連絡メール（CEL）','Ｅストアー　後払い窓口','=?UTF-8?B?GyRCI0UlOSVIJSIhPCEhOGVKJyQkQWs4fRsoQg==?=','ato-barai.sp@estore.co.jp',null,null,null,'【ご連絡】ご入金額が不足しております','=?UTF-8?B?77yl44K544OI44Ki44O844CA5b6M5omV44GE56qT5Y+j?=','◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇


{CustomerNameKj}様

先日はご注文いただきまして、誠にありがとうございます。

{ReceiptDate}に{ReceiptClass}より{UseAmount}円のご入金をいただきましたが、
{ShortfallAmount}円が不足となっております。

大変お手数ですが不足分の{ShortfallAmount}円を
下記口座までお振込みいただきますようお願いいたします。

【銀行振込口座】
ジャパンネット銀行 すずめ支店
普通預金　6291494
株式会社キャッチボール／Ｅストアー専用口座

【郵便振替口座】
口座記号：00140-5
口座番号：665145
株式会社キャッチボール　Ｅストアー専用

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。


■商品・返品・配送に関するお問い合わせは：
　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}


■お支払いに関するお問い合わせは：
  お問合せ先：03-6908-5100
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: ato-barai.sp@estore.co.jp
  運営会社：株式会社Ｅストアー　後払い窓口 
  住所：〒105-0003　東京都港区西新橋1-10-2  住友生命西新橋ビル９F
',1,'2015/12/01 12:52:10',32,'2015/12/01 15:07:04',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (421,87,'返金メール（キャンセル）(PC)','ato-barai.sp@estore.co.jp','ato-barai.sp@estore.co.jp','Ｅストアー　後払い窓口',null,null,null,'【ご連絡】ご返金のご連絡','ato-barai.sp@estore.co.jp','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

先日はご注文いただきまして、誠にありがとうございます。

{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
既に店舗様よりご注文キャンセルのご連絡をいただいておりましたので
ご返金させていただきたく、口座の確認のご連絡を差し上げました。

お手数ではございますが
・銀行名：
・支店名：
・口座種目：
・口座番号：
・口座名義(カナ)：
上記をご記入のうえ、当メールへご返信くださいませ。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。


■商品・返品・配送に関するお問い合わせは：
　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}


■お支払いに関するお問い合わせは：
  お問合せ先：03-6908-5100
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: ato-barai.sp@estore.co.jp
  運営会社：株式会社Ｅストアー　後払い窓口 
  住所：〒105-0003　東京都港区西新橋1-10-2  住友生命西新橋ビル９F
',1,'2015/12/01 12:53:37',32,'2015/12/01 12:53:37',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (422,88,'返金メール（キャンセル）(CEL)','Ｅストアー　後払い窓口','=?UTF-8?B?GyRCI0UlOSVIJSIhPCEhOGVKJyQkQWs4fRsoQg==?=','ato-barai.sp@estore.co.jp',null,null,null,'【ご連絡】ご返金のご連絡','=?UTF-8?B?77yl44K544OI44Ki44O844CA5b6M5omV44GE56qT5Y+j?=','
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇


{CustomerNameKj}様

先日はご注文いただきまして、誠にありがとうございます。

{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
既に店舗様よりご注文キャンセルのご連絡をいただいておりましたので
ご返金させていただきたく、口座の確認のご連絡を差し上げました。

お手数ではございますが
・銀行名：
・支店名：
・口座種目：
・口座番号：
・口座名義(カナ)：
上記をご記入のうえ、当メールへご返信くださいませ。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。


■商品・返品・配送に関するお問い合わせは：
　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}


■お支払いに関するお問い合わせは：
  お問合せ先：03-6908-5100
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: ato-barai.sp@estore.co.jp
  運営会社：株式会社Ｅストアー　後払い窓口 
  住所：〒105-0003　東京都港区西新橋1-10-2  住友生命西新橋ビル９F
',1,'2015/12/01 12:54:03',32,'2015/12/01 12:54:03',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (423,32,'請求書破棄メール（PC）','セイノーフィナンシャル後払い窓口','=?UTF-8?B?GyRCJTslJCVOITwlVSUjJUolcyU3JWMlazhlSickJEFrOH0bKEI=?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】請求書破棄のお願い','=?UTF-8?B?44K744Kk44OO44O844OV44Kj44OK44Oz44K344Oj44Or5b6M5omV44GE56qT?=
 =?UTF-8?B?5Y+j?=','─────────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
─────────────────────────────────────

 {CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

{CancelDate}に{SiteNameKj}より【後払い決済サービス】窓口へ
キャンセルのご連絡をいただきましたが、
既に請求書をお送りしてしまっておりますので、
大変お手数ではございますが破棄していただくようお願いいたします。

この度は{SiteNameKj}と【後払い決済サービス】をご利用いただき
まことにありがとうございました。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp

',3,'2015/12/01 13:21:31',32,'2015/12/01 13:21:31',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (424,33,'請求書破棄メール（CEL）','セイノーフィナンシャル後払い窓口','=?UTF-8?B?GyRCJTslJCVOITwlVSUjJUolcyU3JWMlazhlSickJEFrOH0bKEI=?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】請求書破棄のお願い','=?UTF-8?B?44K744Kk44OO44O844OV44Kj44OK44Oz44K344Oj44Or5b6M5omV44GE56qT?=
 =?UTF-8?B?5Y+j?=','
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇


 {CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

{CancelDate}に{SiteNameKj}より【後払い決済サービス】窓口へ
キャンセルのご連絡をいただきましたが、
既に請求書をお送りしてしまっておりますので、
大変お手数ではございますが破棄していただくようお願いいたします。

この度は{SiteNameKj}と【後払い決済サービス】をご利用いただき
まことにありがとうございました。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp

',3,'2015/12/01 13:22:00',32,'2015/12/01 13:22:00',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (425,34,'過剰入金メール（PC）','セイノーフィナンシャル後払い窓口','=?UTF-8?B?GyRCJTslJCVOITwlVSUjJUolcyU3JWMlazhlSickJEFrOH0bKEI=?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】ご返金のご連絡','=?UTF-8?B?44K744Kk44OO44O844OV44Kj44OK44Oz44K344Oj44Or5b6M5omV44GE56qT?=
 =?UTF-8?B?5Y+j?=','─────────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
─────────────────────────────────────

{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
{OverReceiptAmount}円多くお支払いいただいておりましたので
ご返金させていただきたく、口座の確認のご連絡を差し上げました。

お手数ではございますが
・銀行名：
・支店名：
・口座種目：
・口座番号：
・口座名義(カナ)：
上記をご記入のうえ、当メールへご返信くださいませ。

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp

',3,'2015/12/01 13:22:57',32,'2015/12/24 15:28:52',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (426,35,'過剰入金メール（CEL）','セイノーフィナンシャル後払い窓口 ','=?UTF-8?B?GyRCJTslJCVOITwlVSUjJUolcyU3JWMlazhlSickJEFrOH0bKEIg?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】ご返金のご連絡','=?UTF-8?B?44K744Kk44OO44O844OV44Kj44OK44Oz44K344Oj44Or5b6M5omV44GE56qT?=
 =?UTF-8?B?5Y+jIA==?=','
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇


 {CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
{OverReceiptAmount}円多くお支払いいただいておりましたので
ご返金させていただきたく、口座の確認のご連絡を差し上げました。

お手数ではございますが
・銀行名：
・支店名：
・口座種目：
・口座番号：
・口座名義(カナ)：
上記をご記入のうえ、当メールへご返信くださいませ。

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp

',3,'2015/12/01 13:28:13',32,'2015/12/01 13:28:13',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (427,81,'不足入金連絡メール（PC）','セイノーフィナンシャル後払い窓口','=?UTF-8?B?GyRCJTslJCVOITwlVSUjJUolcyU3JWMlazhlSickJEFrOH0bKEI=?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】ご入金額が不足しております','=?UTF-8?B?44K744Kk44OO44O844OV44Kj44OK44Oz44K344Oj44Or5b6M5omV44GE56qT?=
 =?UTF-8?B?5Y+j?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払い決済サービスをご利用いただきまして
まことにありがとうございます。

{ReceiptDate}に{ReceiptClass}より{UseAmount}円のご入金をいただきましたが、
{ShortfallAmount}円が不足となっております。

大変お手数ですが不足分の{ShortfallAmount}円を
下記口座までお振込みいただきますようお願いいたします。

【銀行振込口座】
ジャパンネット銀行　
モミジ支店　
普通口座　0015015
セイノーフィナンシャル（カ

【郵便振替口座】
口座記号：00100-7
口座番号：292043
株式会社キャッチボール　セイノーFC係

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/01 13:29:25',32,'2015/12/01 13:30:13',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (428,82,'不足入金連絡メール（CEL）','セイノーフィナンシャル後払い窓口','=?UTF-8?B?GyRCJTslJCVOITwlVSUjJUolcyU3JWMlazhlSickJEFrOH0bKEI=?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】ご入金額が不足しております','=?UTF-8?B?44K744Kk44OO44O844OV44Kj44OK44Oz44K344Oj44Or5b6M5omV44GE56qT?=
 =?UTF-8?B?5Y+j?=','
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇


{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払い決済サービスをご利用いただきまして
まことにありがとうございます。

{ReceiptDate}に{ReceiptClass}より{UseAmount}円のご入金をいただきましたが、
{ShortfallAmount}円が不足となっております。

大変お手数ですが不足分の{ShortfallAmount}円を
下記口座までお振込みいただきますようお願いいたします。

【銀行振込口座】
ジャパンネット銀行　
モミジ支店　
普通口座　0015015
セイノーフィナンシャル（カ

【郵便振替口座】
口座記号：00100-7
口座番号：292043
株式会社キャッチボール　セイノーFC係

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/01 13:30:57',32,'2015/12/01 13:30:57',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (429,87,'返金メール（キャンセル）(PC)','セイノーフィナンシャル後払い窓口','=?UTF-8?B?GyRCJTslJCVOITwlVSUjJUolcyU3JWMlazhlSickJEFrOH0bKEI=?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】ご返金のご連絡','=?UTF-8?B?44K744Kk44OO44O844OV44Kj44OK44Oz44K344Oj44Or5b6M5omV44GE56qT?=
 =?UTF-8?B?5Y+j?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払い決済サービスをご利用いただきまして
まことにありがとうございました。

{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
既に店舗様よりご注文キャンセルのご連絡をいただいておりましたので
ご返金させていただきたく、口座の確認のご連絡を差し上げました。

お手数ではございますが
・銀行名：
・支店名：
・口座種目：
・口座番号：
・口座名義(カナ)：
上記をご記入のうえ、当メールへご返信くださいませ。

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/01 13:36:11',32,'2015/12/01 13:36:34',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (430,88,'返金メール（キャンセル）(CEL)','セイノーフィナンシャル後払い窓口','=?UTF-8?B?GyRCJTslJCVOITwlVSUjJUolcyU3JWMlazhlSickJEFrOH0bKEI=?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】ご返金のご連絡','=?UTF-8?B?44K744Kk44OO44O844OV44Kj44OK44Oz44K344Oj44Or5b6M5omV44GE56qT?=
 =?UTF-8?B?5Y+j?=','
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇


{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払い決済サービスをご利用いただきまして
まことにありがとうございました。

{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
既に店舗様よりご注文キャンセルのご連絡をいただいておりましたので
ご返金させていただきたく、口座の確認のご連絡を差し上げました。

お手数ではございますが
・銀行名：
・支店名：
・口座種目：
・口座番号：
・口座名義(カナ)：
上記をご記入のうえ、当メールへご返信くださいませ。

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/01 13:36:59',32,'2015/12/01 13:36:59',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (431,1,'事業者登録完了（サービス開始）メール','','','customer@ato-barai.com',null,null,null,'','','',4,'2015/12/04 18:15:25',43,'2015/12/04 18:15:25',43,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (432,23,'パスワード情報お知らせメール','','','ato-barai.sp@estore.co.jp',null,null,null,'','','',1,'2015/12/04 18:16:52',43,'2015/12/04 18:16:52',43,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (433,30,'キャンセル申請メール','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'【後払いドットコム】キャンセル申請をお受付いたしました','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}　様

いつも【後払いドットコム】をご利用いただきまして、まことにありがとうございます。

以下のご注文のキャンセル申請のお受付いたしました。

【管理画面ＵＲＬ】
https://www.atobarai.jp/member/

【キャンセル受付情報】
ご注文ID：{OrderId}
請求先氏名：{CustomerNameKj}　様
ご注文総額：{UseAmount}
ご注文日：{OrderDate}

弊社での確認後、再度承認連絡をさせていただきます。

ご不明な点などございましたら、お気軽に当社までお問い合わせ下さい。

--------------------------------------------------------------

【後払いドットコム】
  お問合せ先：0120-667-690
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2015/12/22 16:34:14',32,'2015/12/22 16:34:14',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (434,31,'キャンセル申請取消メール','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'【後払いドットコム】キャンセル申請取消をお受付いたしました','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}　様

いつも【後払いドットコム】をご利用いただきまして、まことにありがとうございます。

以下のご注文のキャンセル申請の取り消しををを承りましたので、ご確認下さい。

【管理画面ＵＲＬ】
https://www.atobarai.jp/member/

【キャンセル取消情報】
ご注文ID：{OrderId}
請求先氏名：{CustomerNameKj}　様
ご注文総額：{UseAmount}
ご注文日：{OrderDate}
キャンセル申請日：{CancelDate} 

ご不明な点などございましたら、お気軽に当社までお問い合わせ下さい。

--------------------------------------------------------------

【後払いドットコム】
  お問合せ先：0120-667-690
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2015/12/22 16:35:01',32,'2015/12/22 16:35:01',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (435,32,'請求書破棄メール（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】請求書破棄のお願い','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございました。

{CancelDate}に{SiteNameKj}より後払いドットコムへ
キャンセルのご連絡をいただきましたが、
既に請求書をお送りしてしまっておりますので、
大変お手数ではございますが破棄していただくようお願いいたします。

不備や不明点などございましたら、
お気軽にお問合せくださいませ。

この度は{SiteNameKj}と後払いドットコムをご利用いただき
まことにありがとうございました。

今後とも何卒、よろしくお願いいたします。

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
-------------------------------------------------------------- 
',2,'2015/12/22 16:36:05',32,'2015/12/22 16:36:05',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (436,33,'請求書破棄メール（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】請求書破棄のお願い','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございました。

{CancelDate}に{SiteNameKj}より後払いドットコムへ
キャンセルのご連絡をいただきましたが、
既に請求書をお送りしてしまっておりますので、
大変お手数ではございますが破棄していただくようお願いいたします。

不備や不明点などございましたら、
お気軽にお問合せくださいませ。

この度は{SiteNameKj}と後払いドットコムをご利用いただき
まことにありがとうございました。

今後とも何卒、よろしくお願いいたします。

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
-------------------------------------------------------------- 
',2,'2015/12/22 16:36:27',32,'2015/12/22 16:36:27',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (437,34,'過剰入金メール（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】ご返金のご連絡','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございました。

{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
{OverReceiptAmount}円多くお支払いいただいておりましたので
ご返金させていただきたく、口座の確認のご連絡を差し上げました。

お手数ではございますが
・銀行名：
・支店名：
・口座種目：
・口座番号：
・口座名義(カナ)：
上記をご記入のうえ、当メールへご返信くださいませ。

不明点などございましたら、お気軽にお問合せくださいませ。

何卒よろしくお願いいたします。

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
-------------------------------------------------------------- 
',2,'2015/12/22 16:37:01',32,'2015/12/22 16:37:01',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (438,35,'過剰入金メール（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】ご返金のご連絡','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございました。

{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
{OverReceiptAmount}円多くお支払いいただいておりましたので
ご返金させていただきたく、口座の確認のご連絡を差し上げました。

お手数ではございますが
・銀行名：
・支店名：
・口座種目：
・口座番号：
・口座名義(カナ)：
上記をご記入のうえ、当メールへご返信くださいませ。

不明点などございましたら、お気軽にお問合せくださいませ。

何卒よろしくお願いいたします。

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
-------------------------------------------------------------- 
',2,'2015/12/22 16:37:22',32,'2015/12/22 16:37:22',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (439,38,'注文修正完了メール','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'【後払いドットコム】ご注文内容の修正を承りました（{OrderCount}件）','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}様

いつも後払いドットコムをご利用いただき、
まことにありがとうございます。

以下のご注文内容の修正を受け付けいたしました。


修正完了件数：{OrderCount}件

ご注文者名：{OrderSummary}



■■■■■■■■■■■　キャンセルが発生した場合　■■■■■■■■■■■

ご登録された注文のキャンセルが入った場合は、お手数ですが「履歴検索」から
ご注文を検索し、該当のお取引をクリックしてキャンセル処理を行ってください。

■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

【管理画面ＵＲＬ】
https://www.atobarai.jp/member/

ご不明な点などございましたら、お気軽にお問い合わせくださいませ。

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：0120-667-690
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/22 16:37:59',32,'2015/12/22 16:37:59',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (440,38,'注文修正完了メール','セイノーフィナンシャル後払い窓口','=?UTF-8?B?GyRCJTslJCVOITwlVSUjJUolcyU3JWMlazhlSickJEFrOH0bKEI=?=','sfc-atobarai@seino.co.jp',null,null,null,'ご注文内容の修正を承りました（{OrderCount}件）','=?UTF-8?B?44K744Kk44OO44O844OV44Kj44OK44Oz44K344Oj44Or5b6M5omV44GE56qT?=
 =?UTF-8?B?5Y+j?=','
{EnterpriseNameKj} 様

いつも【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

以下のご注文内容の修正を受け付けいたしました。


修正完了件数：{OrderCount}件

ご注文者名：{OrderSummary}



■■■■■■■■■■■　キャンセルが発生した場合　■■■■■■■■■■■

ご登録された注文のキャンセルが入った場合は、お手数ですが「履歴検索」から
ご注文を検索し、該当のお取引をクリックしてキャンセル処理を行ってください。

■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

【管理画面ＵＲＬ】
https://atobarai.seino.co.jp/seino-financial/member/

ご不明な点などございましたら、お気軽にお問い合わせくださいませ。


--------------------------------------------------------------

セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp

--------------------------------------------------------------',3,'2015/12/22 16:39:07',32,'2015/12/22 16:39:07',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (441,39,'支払期限超過メール（再１）（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございます。

お支払い期限を過ぎてもご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。
お手元に届き次第、期限までにお支払いをお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
http://www.ato-barai.com/guidance/faq.html

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/22 16:47:05',32,'2015/12/22 16:47:05',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (442,40,'支払期限超過メール（再１）（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございます。

お支払い期限を過ぎてもご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。
お手元に届き次第、期限までにお支払いをお願いいたします。
【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
http://www.ato-barai.com/guidance/faq.html

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/22 16:47:54',32,'2015/12/22 16:47:54',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (443,41,'支払期限超過メール（再３）（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。
お手元に届き次第、期限までにお支払いをお願いいたします。


【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
http://www.ato-barai.com/guidance/faq.html

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/22 16:48:42',32,'2015/12/22 16:51:04',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (444,42,'支払期限超過メール（再３）（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。
お手元に届き次第、期限までにお支払いをお願いいたします。


【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
http://www.ato-barai.com/guidance/faq.html

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/22 16:51:57',32,'2015/12/22 16:51:57',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (445,43,'支払期限超過メール（再４）（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。

未納分のお支払いにつき、お手元に届き次第
至急ご対応くださいますようお願いいたします。


【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
http://www.ato-barai.com/guidance/faq.html

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/22 16:53:51',32,'2015/12/22 16:54:16',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (446,44,'支払期限超過メール（再４）（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。

未納分のお支払いにつき、お手元に届き次第
至急ご対応くださいますようお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
http://www.ato-barai.com/guidance/faq.html

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/22 16:55:03',32,'2015/12/22 16:55:25',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (447,45,'支払期限超過メール（再５）（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。

上記督促状でもお知らせしている通り、
お支払いが確認できない場合
お客様の信用取引など不利益が生じる可能性がございます。
つきましては速やかなご対応をお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
http://www.ato-barai.com/guidance/faq.html

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/22 17:00:36',32,'2015/12/22 17:00:36',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (448,46,'支払期限超過メール（再５）（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。


上記督促状でもお知らせしている通り、
お支払いが確認できない場合
お客様の信用取引など不利益が生じる可能性がございます。
つきましては速やかなご対応をお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
http://www.ato-barai.com/guidance/faq.html

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/22 17:01:34',32,'2015/12/22 17:01:34',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (449,47,'支払期限超過メール（再６）（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。

このまま未入金状態が継続されますと、
当社での対応が困難となり
通知記載の対応となる場合があります。
つきましては至急お支払いについて
ご対応くださいますようお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
http://www.ato-barai.com/guidance/faq.html

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/22 17:02:34',32,'2015/12/22 17:02:34',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (450,48,'支払期限超過メール（再６）（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。

このまま未入金状態が継続されますと、
当社での対応が困難となり
通知記載の対応となる場合があります。
つきましては至急お支払いについて
ご対応くださいますようお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
http://www.ato-barai.com/guidance/faq.html

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/22 17:03:10',32,'2015/12/22 17:03:10',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (451,49,'支払期限超過メール（再７）（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。

再三にわたり、ご返済に対し履行頂くようご通知いたしましたが
貴殿より誠意のあるご対応を頂いていない状況となっております。
今後につきましても、ご連絡・お支払いが確認できない場合は
弁護士への回収委任もしくは法的手続きに移行せざるおえません。
しかしながら、弊社債権管理部では
貴殿の債務履行に対し解決を図る為の相談窓口を設けており
相談による解決も可能な場合もございます。
つきましては、解決に向け至急ご連絡いただけますようお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
http://www.ato-barai.com/guidance/faq.html

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/22 17:04:01',32,'2015/12/22 17:04:34',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (452,50,'支払期限超過メール（再７）（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。

再三にわたり、ご返済に対し履行頂くようご通知いたしましたが
貴殿より誠意のあるご対応を頂いていない状況となっております。
今後につきましても、ご連絡・お支払いが確認できない場合は
弁護士への回収委任もしくは法的手続きに移行せざるおえません。
しかしながら、弊社債権管理部では
貴殿の債務履行に対し解決を図る為の相談窓口を設けており
相談による解決も可能な場合もございます。
つきましては、解決に向け至急ご連絡いただけますようお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
http://www.ato-barai.com/guidance/faq.html

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/22 17:05:11',32,'2015/12/22 17:05:11',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (453,39,'支払期限超過メール（再１）（PC）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','customer2@ato-barai.com',null,null,null,'【後払い決済サービス】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

お支払い期限を過ぎてもご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。
お手元に届き次第、期限までにお支払いをお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
ジャパンネット銀行　
モミジ支店　
普通口座　0015015
セイノーフィナンシャル（カ

【郵便振替口座】
口座記号：00100-7
口座番号：292043
株式会社キャッチボール　セイノーFC係

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp

',3,'2015/12/24 8:08:57',32,'2015/12/24 8:08:57',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (454,40,'支払期限超過メール（再１）（CEL）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

お支払い期限を過ぎてもご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。
お手元に届き次第、期限までにお支払いをお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
ジャパンネット銀行　
モミジ支店　
普通口座　0015015
セイノーフィナンシャル（カ

【郵便振替口座】
口座記号：00100-7
口座番号：292043
株式会社キャッチボール　セイノーFC係

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp

',3,'2015/12/24 8:09:37',32,'2015/12/24 8:09:37',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (455,41,'支払期限超過メール（再３）（PC）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。
お手元に届き次第、期限までにお支払いをお願いいたします。


【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
ジャパンネット銀行　
モミジ支店　
普通口座　0015015
セイノーフィナンシャル（カ

【郵便振替口座】
口座記号：00100-7
口座番号：292043
株式会社キャッチボール　セイノーFC係

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/24 8:10:58',32,'2015/12/24 8:10:58',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (456,42,'支払期限超過メール（再３）（CEL）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。
お手元に届き次第、期限までにお支払いをお願いいたします。


【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
ジャパンネット銀行　
モミジ支店　
普通口座　0015015
セイノーフィナンシャル（カ

【郵便振替口座】
口座記号：00100-7
口座番号：292043
株式会社キャッチボール　セイノーFC係

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/24 8:11:48',32,'2015/12/24 8:11:48',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (457,43,'支払期限超過メール（再４）（PC）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。

未納分のお支払いにつき、お手元に届き次第
至急ご対応くださいますようお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
ジャパンネット銀行　
モミジ支店　
普通口座　0015015
セイノーフィナンシャル（カ

【郵便振替口座】
口座記号：00100-7
口座番号：292043
株式会社キャッチボール　セイノーFC係

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/24 8:13:36',32,'2015/12/24 8:13:36',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (458,44,'支払期限超過メール（再４）（CEL）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。

未納分のお支払いにつき、お手元に届き次第
至急ご対応くださいますようお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
ジャパンネット銀行　
モミジ支店　
普通口座　0015015
セイノーフィナンシャル（カ

【郵便振替口座】
口座記号：00100-7
口座番号：292043
株式会社キャッチボール　セイノーFC係

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/24 8:14:07',32,'2015/12/24 8:14:07',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (459,46,'支払期限超過メール（再５）（CEL）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。

上記督促状でもお知らせしている通り、
お支払いが確認できない場合
お客様の信用取引など不利益が生じる可能性がございます。
つきましては速やかなご対応をお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
ジャパンネット銀行　
モミジ支店　
普通口座　0015015
セイノーフィナンシャル（カ

【郵便振替口座】
口座記号：00100-7
口座番号：292043
株式会社キャッチボール　セイノーFC係

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/24 8:15:09',32,'2015/12/24 8:15:09',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (460,45,'支払期限超過メール（再５）（PC）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。

上記督促状でもお知らせしている通り、
お支払いが確認できない場合
お客様の信用取引など不利益が生じる可能性がございます。
つきましては速やかなご対応をお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
ジャパンネット銀行　
モミジ支店　
普通口座　0015015
セイノーフィナンシャル（カ

【郵便振替口座】
口座記号：00100-7
口座番号：292043
株式会社キャッチボール　セイノーFC係

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/24 8:15:43',32,'2015/12/24 8:15:43',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (461,47,'支払期限超過メール（再６）（PC）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。

このまま未入金状態が継続されますと、
当社での対応が困難となり
通知記載の対応となる場合があります。
つきましては至急お支払いについて
ご対応くださいますようお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
ジャパンネット銀行　
モミジ支店　
普通口座　0015015
セイノーフィナンシャル（カ

【郵便振替口座】
口座記号：00100-7
口座番号：292043
株式会社キャッチボール　セイノーFC係

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/24 8:17:15',32,'2015/12/24 8:17:15',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (462,48,'支払期限超過メール（再６）（CEL）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。

このまま未入金状態が継続されますと、
当社での対応が困難となり
通知記載の対応となる場合があります。
つきましては至急お支払いについて
ご対応くださいますようお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
ジャパンネット銀行　
モミジ支店　
普通口座　0015015
セイノーフィナンシャル（カ

【郵便振替口座】
口座記号：00100-7
口座番号：292043
株式会社キャッチボール　セイノーFC係

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/24 8:17:40',32,'2015/12/24 8:17:40',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (463,49,'支払期限超過メール（再７）（PC）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。

再三にわたり、ご返済に対し履行頂くようご通知いたしましたが
貴殿より誠意のあるご対応を頂いていない状況となっております。
今後につきましても、ご連絡・お支払いが確認できない場合は
弁護士への回収委任もしくは法的手続きに移行せざるをえません。
しかしながら、弊社債権管理部では
貴殿の債務履行に対し解決を図る為の相談窓口を設けており
相談による解決も可能な場合もございます。
つきましては、解決に向け至急ご連絡いただけますようお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
ジャパンネット銀行　
モミジ支店　
普通口座　0015015
セイノーフィナンシャル（カ

【郵便振替口座】
口座記号：00100-7
口座番号：292043
株式会社キャッチボール　セイノーFC係

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/24 8:18:28',32,'2015/12/24 8:20:46',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (464,50,'支払期限超過メール（再７）（CEL）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。

再三にわたり、ご返済に対し履行頂くようご通知いたしましたが
貴殿より誠意のあるご対応を頂いていない状況となっております。
今後につきましても、ご連絡・お支払いが確認できない場合は
弁護士への回収委任もしくは法的手続きに移行せざるをえません。
しかしながら、弊社債権管理部では
貴殿の債務履行に対し解決を図る為の相談窓口を設けており
相談による解決も可能な場合もございます。
つきましては、解決に向け至急ご連絡いただけますようお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
ジャパンネット銀行　
モミジ支店　
普通口座　0015015
セイノーフィナンシャル（カ

【郵便振替口座】
口座記号：00100-7
口座番号：292043
株式会社キャッチボール　セイノーFC係

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp
',3,'2015/12/24 8:21:19',32,'2015/12/24 8:21:19',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (465,51,'CB向け請求取りまとめエラーメール','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','以下の事業者で取りまとめに失敗しました。

事業者名：
{EnterpriseNameKj}

取りまとめに失敗した取りまとめ指示グループの注文：
{OrderSummary}

理由：
{Error}
',2,'2015/12/24 8:21:55',32,'2015/12/24 8:21:55',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (466,52,'事業者向け請求取りまとめエラーメール','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'【後払いドットコム】請求取りまとめエラーメール','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj} 様

いつも大変お世話になっております。
後払いドットコムカスタマーセンターでございます。

請求取りまとめ実行時にエラーが発生しました。 
下記のご注文を請求取りまとめ注文一覧から確認頂き、改めて御指示をお願い致します。
                      
取りまとめに失敗した取りまとめ指示グループの注文：
{OrderSummary}

理由：
{Error}
',2,'2015/12/24 8:22:59',32,'2015/12/24 8:23:04',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (467,53,'マイページ仮登録完了メール（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】会員仮登録のご案内','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','
この度は後払いドットコムにお申込みいただき
まことにありがとうございます。

下記のURLをクリックして後払いドットコムでの
会員登録を進めてください。

{MypageRegistUrl}


＜ご注意事項＞
・本メールをお受け取り後、２４時間以内に
後払いドットコム会員登録をいただきますようお願いいたします。
・２４時間以内にご登録が完了されない場合は
仮登録のお手続きが無効となりますのであらかじめご了承願います。
・２４時間を過ぎてしまった場合は、
恐れ入りますが再度仮登録のお手続きをお願いいたします。


------------------------------------
ご登録の手順について
------------------------------------

１.上記URLにアクセスし、画面にしたがって必要事項をご入力ください。

２.ご入力内容をご確認のうえ、お客様情報を登録してください。

３.“【後払いドットコム】会員登録完了のお知らせ”というメールが届きます。

以上で後払いドットコム会員登録完了となります。


------------------------------------
ご登録がうまくいかない場合は、
大変恐れ入りますがcustomer@ato-barai.comまで
お問い合わせをお願いいたします。

※営業時間外のお問い合わせにつきましては
ご返信にお時間をいただく場合がございます。


この度はお申込みありがとうございました。


--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/24 8:24:01',32,'2015/12/24 8:24:17',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (468,54,'マイページ仮登録完了メール（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】会員仮登録のご案内','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','この度は後払いドットコムにお申込みいただき
まことにありがとうございます。

下記のURLをクリックして後払いドットコムでの
会員登録を進めてください。

{MypageRegistUrl}


＜ご注意事項＞
・本メールをお受け取り後、２４時間以内に
後払いドットコム会員登録をいただきますようお願いいたします。
・２４時間以内にご登録が完了されない場合は
仮登録のお手続きが無効となりますのであらかじめご了承願います。
・２４時間を過ぎてしまった場合は、
恐れ入りますが再度仮登録のお手続きをお願いいたします。


------------------------------------
ご登録の手順について
------------------------------------

１.上記URLにアクセスし、画面にしたがって必要事項をご入力ください。

２.ご入力内容をご確認のうえ、お客様情報を登録してください。

３.“【後払いドットコム】会員登録完了のお知らせ”というメールが届きます。

以上で後払いドットコム会員登録完了となります。


------------------------------------
ご登録がうまくいかない場合は、
大変恐れ入りますがcustomer@ato-barai.comまで
お問い合わせをお願いいたします。

※営業時間外のお問い合わせにつきましては
ご返信にお時間をいただく場合がございます。


この度はお申込みありがとうございました。


--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/24 8:24:58',32,'2015/12/24 8:24:58',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (469,55,'マイページ本登録完了メール（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】会員登録完了のお知らせ','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{MyPageNameKj}様

この度は後払いドットコムにお申込みいただき
まことにありがとうございます。
後払いドットコムでの会員登録が完了いたしました。


------------------------------------


※当メールにお心当たりのない方は、
恐れ入りますが下記メールアドレスまでご連絡をお願いいたします。
また、当サービスに関するその他のお問い合わせも
下記アドレスにて承っております。

この度はお申込みありがとうございました。


--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/24 8:25:41',32,'2015/12/24 8:25:41',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (470,56,'マイページ本登録完了メール（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】会員登録完了のお知らせ','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{MyPageNameKj}様

この度は後払いドットコムにお申込みいただき
まことにありがとうございます。
後払いドットコムでの会員登録が完了いたしました。


------------------------------------


※当メールにお心当たりのない方は、
恐れ入りますが下記メールアドレスまでご連絡をお願いいたします。
また、当サービスに関するその他のお問い合わせも
下記アドレスにて承っております。

この度はお申込みありがとうございました。


--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/24 8:26:07',32,'2015/12/24 8:26:07',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (471,57,'マイページパスワード変更メール（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】パスワード変更を承りました','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{MyPageNameKj}様

いつも後払いドットコムをご利用いただき
まことにありがとうございます。

マイページのパスワード変更を承りましたのでご報告いたします。

下記URLへアクセスし、ログインを行ってください。

https://www.atobarai.jp/mypage

※当メールにお心当たりのない方は、
恐れ入りますが下記メールアドレスまでご連絡をお願いいたします。
また、当サービスに関するその他のお問い合わせも
下記アドレスにて承っております。

今後とも、当サービスをよろしくお願いいたします。

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/24 8:27:23',32,'2015/12/24 8:27:23',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (472,58,'マイページパスワード変更メール（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】パスワード変更を承りました','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{MyPageNameKj}様

いつも後払いドットコムをご利用いただき
まことにありがとうございます。

マイページのパスワード変更を承りましたのでご報告いたします。

下記URLへアクセスし、ログインを行ってください。

https://www.atobarai.jp/mypage

※当メールにお心当たりのない方は、
恐れ入りますが下記メールアドレスまでご連絡をお願いいたします。
また、当サービスに関するその他のお問い合わせも
下記アドレスにて承っております。

今後とも、当サービスをよろしくお願いいたします。

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/24 8:27:40',32,'2015/12/24 8:27:40',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (473,59,'マイページ退会完了メール（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】退会完了のお知らせ','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{MyPageNameKj}様

この度は後払いドットコムをご利用いただき
まことにありがとうございました。

退会手続きが完了いたしましたのでご報告いたします。

またのご利用を心よりお待ちしております。

--------------------------------------------------------------

このメールは退会手続きをされたメールアドレスへ
自動で配信しております。
再度会員登録をされる際は、下記URLへアクセスくださいませ。

https://www.atobarai.jp/mypage

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/24 8:28:04',32,'2015/12/24 8:28:04',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (474,60,'マイページ退会完了メール（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】退会完了のお知らせ','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{MyPageNameKj}様

この度は後払いドットコムをご利用いただき
まことにありがとうございました。

退会手続きが完了いたしましたのでご報告いたします。

またのご利用を心よりお待ちしております。

--------------------------------------------------------------

このメールは退会手続きをされたメールアドレスへ
自動で配信しております。
再度会員登録をされる際は、下記URLへアクセスくださいませ。

https://www.atobarai.jp/mypage

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/24 8:28:21',32,'2015/12/24 8:28:21',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (475,61,'社内与信保留メール','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'【後払いドットコム】与信の件でご確認をお願いいたします','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}様

いつも大変お世話になっております。
後払いドットコムカスタマーセンターでございます。

本日与信をいただきました

{OrderId} {CustomerNameKj}様ですが

{PendingReason}。

{PendingDate}まで与信保留とさせていただきますので
お手数ではございますが、正しい情報をご確認いただき
管理サイト上でご変更の処理をいただくか
弊社までご連絡をいただきますようお願いいたします。


■■■■■■■■■■■　ご注文修正をされる際の注意　■■■■■■■■■■■

修正内容をご入力いただいた後、「この内容で登録」をクリックすると
内容の確認画面に遷移します。内容をご確認のうえ、もう一度
「この内容で登録」をクリックすると修正が完了となります。
（※確認画面から別のページに移ってしまったり
閉じてしまったりすると、修正が反映されません。）

■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

【管理画面ＵＲＬ】
https://www.atobarai.jp/member/

ご不明な点などございましたら、お気軽にお問い合わせくださいませ。

何卒よろしくお願いいたします。

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：0120-667-690
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/24 8:28:57',32,'2015/12/24 8:28:57',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (476,81,'不足入金連絡メール（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】ご入金額が不足しております','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございます。

{ReceiptDate}に{ReceiptClass}より{UseAmount}円のご入金をいただきましたが、
{ShortfallAmount}円が不足となっております。

大変お手数ですが不足分の{ShortfallAmount}円を
下記口座までお振込みいただきますようお願いいたします。

【銀行振込口座】
三井住友銀行　新宿通支店
普通口座　8047001
カ）キャッチボール

【郵便振替口座】
口座記号：00120-7
口座番号：670031
株式会社キャッチボール

不明点などございましたら
お気軽にお問合せくださいませ。

何卒、よろしくお願いいたします。

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/24 8:29:41',32,'2015/12/24 8:31:27',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (477,82,'不足入金連絡メール（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】ご入金額が不足しております','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございます。

{ReceiptDate}に{ReceiptClass}より{UseAmount}円のご入金をいただきましたが、
{ShortfallAmount}円が不足となっております。

大変お手数ですが不足分の{ShortfallAmount}円を
下記口座までお振込みいただきますようお願いいたします。

【銀行振込口座】
三井住友銀行　新宿通支店
普通口座　8047001
カ）キャッチボール

【郵便振替口座】
口座記号：00120-7
口座番号：670031
株式会社キャッチボール

不明点などございましたら
お気軽にお問合せくださいませ。

何卒、よろしくお願いいたします。

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
--------------------------------------------------------------
',2,'2015/12/24 8:31:45',32,'2015/12/24 8:31:45',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (478,86,'事業者メール登録バッチエラーメール','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'【後払いドットコム】事業者メール登録バッチエラーメール','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','以下の事業者登録メールに対する処理に失敗しました。

------------------------------
{body}',2,'2015/12/24 8:32:38',32,'2015/12/24 8:32:38',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (479,87,'返金メール（キャンセル）(PC)','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'後払いドットコム】ご返金のご連絡','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございました。

{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
既に店舗様よりご注文キャンセルのご連絡をいただいておりましたので
ご返金させていただきたく、口座の確認のご連絡を差し上げました。

お手数ではございますが
・銀行名：
・支店名：
・口座種目：
・口座番号：
・口座名義(カナ)：
上記をご記入のうえ、当メールへご返信くださいませ。

不明点などございましたら、お気軽にお問合せくださいませ。

何卒よろしくお願いいたします。

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
-------------------------------------------------------------- 
',2,'2015/12/24 8:33:01',32,'2015/12/24 8:33:01',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (480,88,'返金メール（キャンセル）(CEL)','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】ご返金のご連絡','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございました。

{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
既に店舗様よりご注文キャンセルのご連絡をいただいておりましたので
ご返金させていただきたく、口座の確認のご連絡を差し上げました。

お手数ではございますが
・銀行名：
・支店名：
・口座種目：
・口座番号：
・口座名義(カナ)：
上記をご記入のうえ、当メールへご返信くださいませ。

不明点などございましたら、お気軽にお問合せくださいませ。

何卒よろしくお願いいたします。

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F
-------------------------------------------------------------- 
',2,'2015/12/24 8:33:24',32,'2015/12/24 8:33:24',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (481,51,'CB向け請求取りまとめエラーメール','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'請求取りまとめエラーメール','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','以下の事業者で取りまとめに失敗しました。

事業者名：
{EnterpriseNameKj}

取りまとめに失敗した取りまとめ指示グループの注文：
{OrderSummary}

理由：
{Error}
',3,'2015/12/24 8:39:08',32,'2015/12/24 8:39:08',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (482,52,'事業者向け請求取りまとめエラーメール','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】請求取りまとめエラーメール','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{EnterpriseNameKj} 様

いつも【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

請求取りまとめ実行時にエラーが発生しました。 
下記のご注文を請求取りまとめ注文一覧から確認頂き、改めて御指示をお願い致します。
                      
取りまとめに失敗した取りまとめ指示グループの注文：
{OrderSummary}

理由：
{Error}
',3,'2015/12/24 8:41:18',32,'2015/12/24 8:42:32',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (483,53,'マイページ仮登録完了メール（PC）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','customer2@ato-barai.com',null,null,null,'【後払い決済マイページ】会員仮登録のご案内','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','この度は【後払い決済マイページ】にお申込みいただき
まことにありがとうございます。

下記のURLをクリックして【後払い決済マイページ】での
会員登録を進めてください。

{MypageRegistUrl}


＜ご注意事項＞
・本メールをお受け取り後、２４時間以内に
  【後払い決済マイページ】会員登録をいただきますようお願いいたします。
・２４時間以内にご登録が完了されない場合は
  仮登録のお手続きが無効となりますのであらかじめご了承願います。
・２４時間を過ぎてしまった場合は、
  恐れ入りますが再度仮登録のお手続きをお願いいたします。


------------------------------------
ご登録の手順について
------------------------------------

１.上記URLにアクセスし、画面にしたがって必要事項をご入力ください。

２.ご入力内容をご確認のうえ、お客様情報を登録してください。

３.“【後払い決済マイページ】会員登録完了のお知らせ”というメールが届きます。

以上で【後払い決済マイページ】会員登録完了となります。


------------------------------------
ご登録がうまくいかない場合は、
大変恐れ入りますが sfc-atobarai@seino.co.jp まで
お問い合わせをお願いいたします。

※営業時間外のお問い合わせにつきましては
  ご返信にお時間をいただく場合がございます。


この度はお申込みありがとうございました。


--------------------------------------------------------------

【後払い決済サービス】
  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------
',3,'2015/12/24 8:44:34',32,'2015/12/24 15:54:44',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (484,54,'マイページ仮登録完了メール（CEL）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','customer2@ato-barai.com',null,null,null,'【後払い決済マイページ】会員仮登録のご案内','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','この度は【後払い決済マイページ】にお申込みいただき
まことにありがとうございます。

下記のURLをクリックして【後払い決済マイページ】での
会員登録を進めてください。

{MypageRegistUrl}


＜ご注意事項＞
・本メールをお受け取り後、２４時間以内に
  【後払い決済マイページ】会員登録をいただきますようお願いいたします。
・２４時間以内にご登録が完了されない場合は
  仮登録のお手続きが無効となりますのであらかじめご了承願います。
・２４時間を過ぎてしまった場合は、
  恐れ入りますが再度仮登録のお手続きをお願いいたします。


------------------------------------
ご登録の手順について
------------------------------------

１.上記URLにアクセスし、画面にしたがって必要事項をご入力ください。

２.ご入力内容をご確認のうえ、お客様情報を登録してください。

３.“【後払い決済マイページ】会員登録完了のお知らせ”というメールが届きます。

以上で【後払い決済マイページ】会員登録完了となります。


------------------------------------
ご登録がうまくいかない場合は、
大変恐れ入りますが sfc-atobarai@seino.co.jp まで
お問い合わせをお願いいたします。

※営業時間外のお問い合わせにつきましては
  ご返信にお時間をいただく場合がございます。


この度はお申込みありがとうございました。


--------------------------------------------------------------

【後払い決済サービス】
  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------
',3,'2015/12/24 11:26:28',32,'2015/12/24 15:54:51',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (485,55,'マイページ本登録完了メール（PC）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済マイページ】会員登録完了のお知らせ','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','この度は【後払い決済マイページ】にお申込みいただき
まことにありがとうございます。

【後払い決済マイページ】での会員登録が完了いたしました。


------------------------------------


※当メールにお心当たりのない方は、
恐れ入りますが下記メールアドレスまでご連絡をお願いいたします。
また、当サービスに関するその他のお問い合わせも
下記アドレスにて承っております。

この度はお申込みありがとうございました。


--------------------------------------------------------------

【後払い決済サービス】
  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------
',3,'2015/12/24 11:27:02',32,'2015/12/24 11:27:02',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (486,56,'マイページ本登録完了メール（CEL）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済マイページ】会員登録完了のお知らせ','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','この度は【後払い決済マイページ】にお申込みいただき
まことにありがとうございます。

【後払い決済マイページ】での会員登録が完了いたしました。


------------------------------------


※当メールにお心当たりのない方は、
恐れ入りますが下記メールアドレスまでご連絡をお願いいたします。
また、当サービスに関するその他のお問い合わせも
下記アドレスにて承っております。

この度はお申込みありがとうございました。


--------------------------------------------------------------

【後払い決済サービス】
  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------
',3,'2015/12/24 11:27:43',32,'2015/12/24 11:27:43',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (487,57,'マイページパスワード変更メール（PC）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','customer2@ato-barai.com',null,null,null,'【後払い決済マイページ】パスワード変更を承りました','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','いつも後払い決済マイページご利用いただき
まことにありがとうございます。

マイページのパスワード変更を承りましたのでご報告いたします。

下記URLへアクセスし、ログインを行ってください。


https://atobarai.seino.co.jp/seino-financial/mypage



※当メールにお心当たりのない方は、
恐れ入りますが下記メールアドレスまでご連絡をお願いいたします。
また、当サービスに関するその他のお問い合わせも
下記アドレスにて承っております。

今後とも、当サービスをよろしくお願いいたします。


--------------------------------------------------------------

【後払い決済サービス】
  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------
',3,'2015/12/24 11:28:23',32,'2015/12/24 11:28:23',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (488,58,'マイページパスワード変更メール（CEL）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','customer2@ato-barai.com',null,null,null,'【後払い決済マイページ】パスワード変更を承りました','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','いつも後払い決済マイページご利用いただき
まことにありがとうございます。

マイページのパスワード変更を承りましたのでご報告いたします。

下記URLへアクセスし、ログインを行ってください。


https://atobarai.seino.co.jp/seino-financial/mypage



※当メールにお心当たりのない方は、
恐れ入りますが下記メールアドレスまでご連絡をお願いいたします。
また、当サービスに関するその他のお問い合わせも
下記アドレスにて承っております。

今後とも、当サービスをよろしくお願いいたします。


--------------------------------------------------------------

【後払い決済サービス】
  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------
',3,'2015/12/24 11:28:56',32,'2015/12/24 11:28:56',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (489,59,'マイページ退会完了メール（PC）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済マイページ】退会完了のお知らせ','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','この度は【後払い決済マイページ】ご利用いただき
まことにありがとうございました。

退会手続きが完了いたしましたのでご報告いたします。

またのご利用を心よりお待ちしております。

--------------------------------------------------------------

このメールは退会手続きをされたメールアドレスへ
自動で配信しております。
再度会員登録をされる際は、下記URLへアクセスくださいませ。

https://atobarai.seino.co.jp/seino-financial/mypage

--------------------------------------------------------------

【後払い決済サービス】
  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------',3,'2015/12/24 11:29:43',32,'2015/12/24 11:29:43',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (490,60,'マイページ退会完了メール（CEL）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済マイページ】パスワード変更を承りました','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','いつも後払い決済マイページご利用いただき
まことにありがとうございます。

マイページのパスワード変更を承りましたのでご報告いたします。

下記URLへアクセスし、ログインを行ってください。


https://atobarai.seino.co.jp/seino-financial/mypage



※当メールにお心当たりのない方は、
恐れ入りますが下記メールアドレスまでご連絡をお願いいたします。
また、当サービスに関するその他のお問い合わせも
下記アドレスにて承っております。

今後とも、当サービスをよろしくお願いいたします。


--------------------------------------------------------------

【後払い決済サービス】
  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------
',3,'2015/12/24 11:41:38',32,'2015/12/24 11:41:38',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (491,61,'社内与信保留メール','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済マイページ】パスワード変更を承りました','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','{EnterpriseNameKj} 様

いつも【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

本日与信をいただきました

{OrderId} {CustomerNameKj}様ですが

{PendingReason}。

{PendingDate}まで与信保留とさせていただきますので
お手数ではございますが、正しい情報をご確認いただき
管理サイト上でご変更の処理をいただくか
弊社までご連絡をいただきますようお願いいたします。


■■■■■■■■■■■　ご注文修正をされる際の注意　■■■■■■■■■■■

修正内容をご入力いただいた後、「この内容で登録」をクリックすると
内容の確認画面に遷移します。内容をご確認のうえ、もう一度
「この内容で登録」をクリックすると修正が完了となります。
（※確認画面から別のページに移ってしまったり
閉じてしまったりすると、修正が反映されません。）

■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

【管理画面ＵＲＬ】
https://atobarai.seino.co.jp/seino-financial/member/

ご不明な点などございましたら、お気軽にお問い合わせくださいませ。

何卒よろしくお願いいたします。


--------------------------------------------------------------

セイノーフィナンシャル株式会社　後払い決済サービス担当

TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp

--------------------------------------------------------------
',3,'2015/12/24 11:47:40',32,'2015/12/24 11:47:40',32,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (492,89,'０円請求注文報告メール','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,'daisuke-koie@scroll.co.jp','【{ServiceName}】０円請求注文報告メール','{ServiceName}','以下の注文は請求額が０円になります。

------------------------------
{body}',null,'2016/02/23 14:00:00',1,'2022/04/20 5:39:24',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (493,90,'ネットDE受取メール（PC）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】ご返金のご連絡','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございました。

{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
多くお支払いいただいておりましたので
ご返金させていただきたくご連絡差し上げました。

返金の方法のご案内を、注文者様ご住所宛にハガキにてお送りします。
普通郵便での発送となりますので、お客様のお手元に届くまで
一週間程度かかる場合がございます。
一週間ほどお待ちいただいても届かない場合は、
大変お手数ではございますが、このメールの末尾に記載しております
【{ServiceName}】カスタマーセンターまでご一報くださいませ。
なお、ご返金の際の手数料330円はお客様負担になる旨、 
ご理解賜りますようお願いいたします。 


【ご注文内容】
ご注文ID：{OrderId}
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
ご請求金額：{UseAmount}



不明点などございましたら、お気軽にお問合せくださいませ。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2016/02/23 14:00:00',1,'2022/04/20 6:04:54',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (494,91,'ネットDE受取メール（CEL）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】ご返金のご連絡','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございました。

{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、
多くお支払いいただいておりましたので
ご返金させていただきたくご連絡差し上げました。

返金の方法のご案内を、注文者様ご住所宛にハガキにてお送りします。
普通郵便での発送となりますので、お客様のお手元に届くまで
一週間程度かかる場合がございます。
一週間ほどお待ちいただいても届かない場合は、
大変お手数ではございますが、このメールの末尾に記載しております
【{ServiceName}】カスタマーセンターまでご一報くださいませ。
なお、ご返金の際の手数料330円はお客様負担になる旨、 
ご理解賜りますようお願いいたします。 


【ご注文内容】
ご注文ID：{OrderId}
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
ご請求金額：{UseAmount}



不明点などございましたら、お気軽にお問合せくださいませ。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2016/02/23 14:00:00',1,'2022/04/20 6:05:02',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (495,92,'マイページパスワード再発行メール（PC）','後払いドットコム / 届いてから払い','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQiAvIBskQkZPJCQkRiQrJGlKJyQkGyhC?=','customer@ato-barai.com',null,null,null,'【後払いドットコム / 届いてから払い】パスワード再発行のご案内','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44OgIC8g5bGK44GE44Gm44GL44KJ5omV?=
 =?UTF-8?B?44GE?=','この度は【後払いドットコム / 届いてから払い】をご利用いただき
まことにありがとうございます。

下記のURLをクリックして【後払いドットコム / 届いてから払い】での
パスワード再設定を進めてください。

{MypagePasswordResetUrl}


＜ご注意事項＞
・本メールをお受け取り後、２４時間以内にパスワード再設定を
完了していただきますようお願いいたします。
・２４時間以内にパスワード再設定が完了されない場合はお手続きが無効と
なりますのであらかじめご了承願います。
・２４時間を過ぎてしまった場合は、恐れ入りますが再度再発行のお手続きを
お願いいたします。


------------------------------------
再設定の手順について
------------------------------------

１.上記URLにアクセスし、画面にしたがって必要事項をご入力ください。

２.ご入力内容をご確認のうえ、新しいパスワードを登録してください。

以上でパスワード再設定完了となります。


------------------------------------
再設定がうまくいかない場合は、
大変恐れ入りますがcustomer@ato-barai.comまで
お問い合わせをお願いいたします。

※営業時間外のお問い合わせにつきましては
ご返信にお時間をいただく場合がございます。


この度はご利用ありがとうございました。


-----------------------------------------------------------
【後払いドットコム / 届いてから払い】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：customer@ato-barai.com
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2017/03/02 16:00:00',1,'2022/07/14 11:29:45',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (496,93,'マイページパスワード再発行メール（CEL）','後払いドットコム / 届いてから払い','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQiAvIBskQkZPJCQkRiQrJGlKJyQkGyhC?=','customer@ato-barai.com',null,null,null,'【後払いドットコム / 届いてから払い】パスワード再発行のご案内','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44OgIC8g5bGK44GE44Gm44GL44KJ5omV?=
 =?UTF-8?B?44GE?=','この度は【後払いドットコム / 届いてから払い】をご利用いただき
まことにありがとうございます。

下記のURLをクリックして【後払いドットコム / 届いてから払い】での
パスワード再設定を進めてください。

{MypagePasswordResetUrl}


＜ご注意事項＞
・本メールをお受け取り後、２４時間以内にパスワード再設定を
完了していただきますようお願いいたします。
・２４時間以内にパスワード再設定が完了されない場合はお手続きが無効と
なりますのであらかじめご了承願います。
・２４時間を過ぎてしまった場合は、恐れ入りますが再度再発行のお手続きを
お願いいたします。


------------------------------------
再設定の手順について
------------------------------------

１.上記URLにアクセスし、画面にしたがって必要事項をご入力ください。

２.ご入力内容をご確認のうえ、新しいパスワードを登録してください。

以上でパスワード再設定完了となります。


------------------------------------
再設定がうまくいかない場合は、
大変恐れ入りますがcustomer@ato-barai.comまで
お問い合わせをお願いいたします。

※営業時間外のお問い合わせにつきましては
ご返信にお時間をいただく場合がございます。


この度はご利用ありがとうございました。


-----------------------------------------------------------
【後払いドットコム / 届いてから払い】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：customer@ato-barai.com
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2017/03/02 16:00:00',1,'2022/07/14 11:29:59',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (497,92,'マイページパスワード再発行メール（PC）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','customer2@ato-barai.com',null,null,null,'【後払い決済マイページ】パスワード再発行のご案内','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','この度は【後払い決済マイページ】をご利用いただき
まことにありがとうございます。

下記のURLをクリックして【後払い決済マイページ】での
パスワード再設定を進めてください。

{MypagePasswordResetUrl}


＜ご注意事項＞
・本メールをお受け取り後、２４時間以内にパスワード再設定を
完了していただきますようお願いいたします。
・２４時間以内にパスワード再設定が完了されない場合はお手続きが無効と
なりますのであらかじめご了承願います。
・２４時間を過ぎてしまった場合は、恐れ入りますが再度再発行のお手続きを
お願いいたします。


------------------------------------
再設定の手順について
------------------------------------

１.上記URLにアクセスし、画面にしたがって必要事項をご入力ください。

２.ご入力内容をご確認のうえ、新しいパスワードを登録してください。

以上でパスワード再設定完了となります。


------------------------------------
再設定がうまくいかない場合は、
大変恐れ入りますがsfc-atobarai@seino.co.jpまで
お問い合わせをお願いいたします。

※営業時間外のお問い合わせにつきましては
ご返信にお時間をいただく場合がございます。


この度はご利用ありがとうございました。


--------------------------------------------------------------

【後払い決済サービス】
  お問合せ先：03-5909-4500
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------',3,'2017/03/06 10:50:00',1,'2017/03/06 10:57:58',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (498,93,'マイページパスワード再発行メール（CEL）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','customer2@ato-barai.com',null,null,null,'【後払い決済マイページ】パスワード再発行のご案内','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','この度は【後払い決済マイページ】をご利用いただき
まことにありがとうございます。

下記のURLをクリックして【後払い決済マイページ】での
パスワード再設定を進めてください。

{MypagePasswordResetUrl}


＜ご注意事項＞
・本メールをお受け取り後、２４時間以内にパスワード再設定を
完了していただきますようお願いいたします。
・２４時間以内にパスワード再設定が完了されない場合はお手続きが無効と
なりますのであらかじめご了承願います。
・２４時間を過ぎてしまった場合は、恐れ入りますが再度再発行のお手続きを
お願いいたします。


------------------------------------
再設定の手順について
------------------------------------

１.上記URLにアクセスし、画面にしたがって必要事項をご入力ください。

２.ご入力内容をご確認のうえ、新しいパスワードを登録してください。

以上でパスワード再設定完了となります。


------------------------------------
再設定がうまくいかない場合は、
大変恐れ入りますがsfc-atobarai@seino.co.jpまで
お問い合わせをお願いいたします。

※営業時間外のお問い合わせにつきましては
ご返信にお時間をいただく場合がございます。


この度はご利用ありがとうございました。


--------------------------------------------------------------

【後払い決済サービス】
  お問合せ先：03-5909-4500
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------',3,'2017/03/06 10:50:00',1,'2017/03/06 10:58:20',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (499,94,'間違い伝票修正依頼メール','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】伝票番号のご確認をお願いいたします ','{ServiceName}','{EnterpriseNameKj}様 

いつも【{ServiceName}】をご利用いただき、　
まことにありがとうございます。

{ReceiptOrderDate}にご注文登録をいただきました、下記お客様でございますが、
現在、着荷確認が取れず、お支払い期限を過ぎても入金の確認が
できていないため、立替ができていない状況でございますので
ご確認をいただきたくご連絡をいたしました。

対象のお客様のデータを添付いたしますので、内容をご確認いただき、
2週間以内にご変更またはご連絡をいただきますようお願いいたします。

※期限内にご変更またはご連絡をいただけず、
配送会社の追跡サービスにて着荷の確認が取れなくなった場合、
無保証扱いとなり、順次債権返却をさせていただきますので
ご注意願います。
 
着荷の確認が取れていない原因といたしましては、
下記のいずれかに該当する可能性がございます。
・配送会社、配送伝票番号の間違い
・お客様に商品が届いていない
・キャンセル申請漏れ

お手数をおかけいたしますが
個人情報の兼ね合いもございますので
商品の配送会社、配送伝票番号、並びに配送状況を
一度店舗様側でご確認いただき、
店舗様管理サイト上から修正くださいますようお願いいたします。

お取引ID ：{OrderId} 

※ 詳細情報については、添付のファイル(解凍後はCSV形式)をご参照ください。
※ 添付ファイルは、個人情報保護の観点からパスワードを設定しております。
※ 添付ファイルのパスワードは別のメールにてお知らせいたします。
※ 添付ファイルは、「一括注文キャンセル（CSV）」にて、キャンセル申請用のCSVとして、そのままご利用いただけます。

また、受領書がございます場合には添付いただければ
弊社にて確認いたします。
 
※編集方法は履歴検索から特定のお客様を絞り込んでいただき、
『登録内容の修正』から修正をお願いします。
※また、返送・決済方法変更の場合でキャンセル申請漏れの場合も
店舗様管理サイト上から申請をお願いいたします。

ご不明な点などございましたら、お気軽にお問い合わせくださいませ。 

今後ともよろしくお願いいたします。 


-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2017/03/15 17:00:00',1,'2022/04/20 6:05:51',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (500,95,'解凍パスワード通知メール','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】 解凍パスワード通知メール','{ServiceName}','{EnterpriseNameKj}様 

いつも【{ServiceName}】をご利用いただき、　
まことにありがとうございます。

先ほど、「【{ServiceName}】伝票番号のご確認をお願いいたします 」の件名で、
お送りしたメールに添付されたファイルの開封パスワードをお知らせいたします。

添付ファイル名: {FileName} 
解凍パスワード: {Password} 

ご不明な点などございましたら、お気軽にお問い合わせくださいませ。 

今後ともよろしくお願いいたします。 

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2017/03/15 17:00:00',1,'2022/04/20 6:06:04',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (501,96,'CB向け無保証変更通知メール','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,'daisuke-koie@scroll.co.jp','無保証変更通知メール（{LoginId} {OrderId}）','{ServiceName}','以下の注文が無保証に変更されました。
加盟店：{LoginId} {EnterpriseName}
注文ID：{OrderId}
NG理由：{NgReason}
',0,'2019/05/29 21:13:36',1,'2022/04/20 6:07:05',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (502,97,'加盟店向け無保証変更通知メール','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【 {ServiceName} 】無保証処理受付','{ServiceName}','{EnterpriseNameKj}
ご担当者様

いつも大変お世話になっております。
【{ServiceName}】カスタマーセンターでございます。

決済管理システムの「無保証に変更」ボタンにてお申し込みをいただきました
{OrderId} {CustomerNameKj}様の注文を無保証にて受付いたしました。

ご確認いただき、不備や不明点などございましたら
お気軽にお問合せくださいませ。

今後とも何卒、よろしくお願いいたします。


-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',0,'2019/05/29 21:13:37',1,'2022/04/20 2:31:39',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (503,98,'請求データ送信バッチエラーメール','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,'daisuke-koie@scroll.co.jp','【{ServiceName}】請求データ送信バッチエラーメール','{ServiceName}','株式会社エクシード　ご担当者様

株式会社キャッチボール基幹システムのエラーメールです。

株式会社キャッチボール基幹システムより、東洋紙業株式会社宛の請求データ送信が失敗しました。

オペレーションによるデータ送信をお願い致します。
',0,'2020/04/13 21:24:08',1,'2022/04/20 5:41:41',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (505,53,'マイページ仮登録完了メール（PC）','マイページ仮登録','=?UTF-8?B?GyRCJV4lJCVaITwlODI+RVBPPxsoQg==?=','customer2@ato-barai.com',null,null,null,'会員仮登録のご案内','=?UTF-8?B?44Oe44Kk44Oa44O844K45Luu55m76Yyy?=','下記のURLをクリックして後払いドットコムでの
会員登録を進めてください。

{MypageRegistUrl}
',1,'2019/12/02 16:52:13',83,'2019/12/02 16:52:13',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (506,100,'口振WEB申込み案内メール（PC）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】口座引き落とし WEBお申し込みのご案内：{OrderId}','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────
 
{CustomerNameKj}様（{OrderId}）


{SiteNameKj}様での口座引き落としに関するご連絡です。

口座引き落としでの決済にあたり「WEB申込み」を選択されたお客様へ、
以下、お申し込み手順をご案内いたします。


【１】下記どちらかのURLより、マイページへログインしてください。

・簡易ログインURL
{OrderPageAccessUrl}

ログインにはご登録のお電話番号をご利用ください。
簡易ログインURLの有効期間は{LimitDate}より14日間となります。

{LimitDate}より14日間を経過されている場合は
下記、通常ログインURLよりログインください。


・通常ログインURL
{OrderPageUrl}

ログインにはご登録のお電話番号と、請求書・払込受領票に
記載されているパスワードをご利用ください。

※請求書・払込受領票をお手元にお持ちでなく
　パスワードが分からない場合は、このメールへご返信ください。


【２】『口座振替　情報の登録へ』のリンクへ進み、
表示される案内に従い、お手続きください。


以上でございます。

お手続きが完了するまでは、【{ServiceName}】よりお送りする
請求書でのお支払いとなります。



ご不明な点がございましたらご返信にてお問い合わせください。

よろしくお願いいたします。


-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2020/02/09 22:25:48',9,'2022/04/20 6:08:06',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (507,101,'口振WEB申込み案内メール（CEL）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】口座引き落とし WEBお申し込みのご案内：{OrderId}','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────
 
{CustomerNameKj}様（{OrderId}）


{SiteNameKj}様での口座引き落としに関するご連絡です。

口座引き落としでの決済にあたり「WEB申込み」を選択されたお客様へ、
以下、お申し込み手順をご案内いたします。


【１】下記どちらかのURLより、マイページへログインしてください。

・簡易ログインURL
{OrderPageAccessUrl}

ログインにはご登録のお電話番号をご利用ください。
簡易ログインURLの有効期間は{LimitDate}より14日間となります。

{LimitDate}より14日間を経過されている場合は
下記、通常ログインURLよりログインください。


・通常ログインURL
{OrderPageUrl}

ログインにはご登録のお電話番号と、請求書・払込受領票に
記載されているパスワードをご利用ください。

※請求書・払込受領票をお手元にお持ちでなく
　パスワードが分からない場合は、このメールへご返信ください。


【２】『口座振替　情報の登録へ』のリンクへ進み、
表示される案内に従い、お手続きください。


以上でございます。

お手続きが完了するまでは、【{ServiceName}】よりお送りする
請求書でのお支払いとなります。



ご不明な点がございましたらご返信にてお問い合わせください。

よろしくお願いいたします。


-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2020/02/09 22:25:48',9,'2022/04/20 6:08:15',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (508,100,'口振WEB申込み案内メール（PC）','株式会社Ｅストアー（後払い窓口）','=?UTF-8?B?GyRCM3Q8MDJxPFIjRSU5JUglIiE8IUo4ZUonJCRBazh9IUsbKEI=?=','ato-barai.sp@estore.co.jp',null,null,null,'【ご連絡】口振請求書を発行しました　（ハガキで届きます）','=?UTF-8?B?5qCq5byP5Lya56S+77yl44K544OI44Ki44O877yI5b6M5omV44GE56qT5Y+j?=
 =?UTF-8?B?77yJ?=','─────────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
─────────────────────────────────────

{CustomerNameKj}様


先日はご注文いただきまして、誠にありがとうございます。


下記のショッピングのご請求書を本日発行いたしますので、請求書到着後、
請求書に記載されているお支払期限日までにお支払いいただきますよう、
お願い申し上げます。


【ご注文内容】

お支払者：{CustomerNameKj}　様

ご購入店舗名：{SiteNameKj}　

ご購入日：{OrderDate}

お支払金額：{UseAmount}

ご購入商品明細：商品名／個数／購入品目計

{OrderItems}

決済手数料                              \{SettlementFee}

送料                                    \{DeliveryFee}


※郵送事故などにより、請求書が届かないことがございます。
一週間ほどお待ちいただいても請求書が届かない場合には、大変お手数ですが、
下記連絡先へご一報くださいますよう、お願い申し上げます。


※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
店舗様まで直接お問合せくださいませ。


※請求書並びに本メールが、商品よりも早く到着してしまうことがございますが、
何卒ご容赦下さいますようお願い申し上げます。
また、請求書内に明細が含まれておりますのでご確認くださいませ。


※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。


※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
配信されてしまう場合がございます。その際は大変お手数ですが、下記
購入店舗様に直接お問合せください。



■商品・返品・配送に関するお問い合わせは：

　直接購入店舗様にお問い合わせください。
　購入店舗：{SiteNameKj}　電話：{Phone}



■お支払いに関するお問い合わせは：

  お問合せ先：03-6908-5100
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: ato-barai.sp@estore.co.jp
  運営会社：株式会社Ｅストアー　後払い窓口 
　住所：〒105-0003 東京都港区西新橋1-10-2　住友生命西新橋ビル9F
',1,'2020/02/09 22:25:48',9,'2020/02/09 22:25:48',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (509,101,'口振WEB申込み案内メール（CEL）','株式会社Ｅストアー（後払い窓口）','=?UTF-8?B?GyRCM3Q8MDJxPFIjRSU5JUglIiE8IUo4ZUonJCRBazh9IUsbKEI=?=','ato-barai.sp@estore.co.jp',null,null,null,'【ご連絡】口振請求書（ハガキ）を発行しました','=?UTF-8?B?5qCq5byP5Lya56S+77yl44K544OI44Ki44O877yI5b6M5omV44GE56qT5Y+j?=
 =?UTF-8?B?77yJ?=','
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇


{CustomerNameKj}様


先日はご注文いただきまして、誠にありがとうございます。

下記のショッピングのご請求書を本日発行いたしますので、請求書到着後、
請求書に記載されているお支払期限日までにお支払いいただきますよう、
お願い申し上げます。

【ご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料  \{SettlementFee}
送料 \{DeliveryFee}

※郵送事故などにより、請求書が届かないことがございます。
一週間ほどお待ちいただいても請求書が届かない場合には、大変お手数ですが、
下記連絡先へご一報くださいますよう、お願い申し上げます。

※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
届かない場合がございます。
万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
店舗様まで直接お問合せくださいませ。

※請求書並びに本メール、商品よりも早く到着してしまうことがございますが、
何卒ご容赦下さいますようお願い申し上げます。
また、請求書内に明細が含まれておりますのでご確認くださいませ。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
配信されてしまう場合がございます。その際は大変お手数ですが、下記
購入店舗様に直接お問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせください。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
03-6908-5100
 営業時間：9:00～18:00　年中無休（年末・年始のぞく）
 ato-barai.sp@estore.co.jp
 運営会社：株式会社Ｅストアー　後払い窓口
〒105-0003 東京都港区西新橋1-10-2　住友生命西新橋ビル9F',1,'2020/02/09 22:25:48',9,'2020/02/09 22:25:48',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (510,100,'口振WEB申込み案内メール（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払い.com】SMBC口振請求書発行案内　（ハガキで届きます）','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払いドットコム】をご利用いただきまして、
まことにありがとうございます。

下記のショッピングのご請求書を本日発行いたしますので、請求書到着後、
請求書に記載されているお支払期限日までにお支払いいただきますよう、
お願い申し上げます。

{OrderPageAccessUrl}

【ご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料                              \{SettlementFee}
送料                                    \{DeliveryFee}

※郵送事故などにより、請求書が届かないことがございます。
　一週間ほどお待ちいただいても請求書が届かない場合には、大変お手数ですが、
　下記連絡先へご一報くださいますよう、お願い申し上げます。

※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
　届かない場合がございます。
　万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
　店舗様まで直接お問合せくださいませ。

※請求書並びに本メールが、商品よりも早く到着してしまうことがございますが、
何卒ご容赦下さいますようお願い申し上げます。
また、請求書内に明細が含まれておりますのでご確認くださいませ。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、下記
　購入店舗様に直接お問合せください。

※※※その他ご不明な点は下記ＵＲＬをご確認ください。※※※

　　　  http://www.ato-barai.com/guidance/faq.html

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
株式会社キャッチボール　後払いドットコム事業部
TEL:03-5332-3490(平日土日9:00～18:00)
Mail: customer@ato-barai.com

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2020/02/09 22:25:48',9,'2020/07/12 10:58:05',17202,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (511,101,'口振WEB申込み案内メール（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払い.com】口振請求書発行案内　（ハガキで届きます）','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払いドットコム】をご利用いただきまして、
まことにありがとうございます。

下記のショッピングのご請求書を本日発行いたしますので、請求書到着後、
請求書に記載されているお支払期限日までにお支払いいただきますよう、
お願い申し上げます。

【ご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料  \{SettlementFee}
送料 \{DeliveryFee}

※郵送事故などにより、請求書が届かないことがございます。
　一週間ほどお待ちいただいても請求書が届かない場合には、大変お手数ですが、
　下記連絡先へご一報くださいますよう、お願い申し上げます。

※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
　届かない場合がございます。
　万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
　店舗様まで直接お問合せくださいませ。

※請求書並びに本メール、商品よりも早く到着してしまうことがございますが、
何卒ご容赦下さいますようお願い申し上げます。
また、請求書内に明細が含まれておりますのでご確認くださいませ。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、下記
　購入店舗様に直接お問合せください。

※※※その他ご不明な点は下記ＵＲＬをご確認ください。※※※

　　　  http://www.ato-barai.com/guidance/faq.html


■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

■お支払いに関するお問い合わせは：
株式会社キャッチボール　後払いドットコム事業部
住所：〒160-0023 東京都新宿区西新宿7-8-2 福八ビル 4F
TEL:03-5332-3490(平日土日9:00～18:00)
Mail: customer@ato-barai.com
URL: http://www.ato-barai.com（パソコン専用）

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail:customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿7-7-30 小田急柏木ビル 8F

--------------------------------------------------------------',2,'2020/02/09 22:25:48',9,'2020/02/09 22:25:48',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (512,100,'口振WEB申込み案内メール（PC）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】口振請求書発行案内　（ハガキで届きます）','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','─────────────────────────────────────
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇
─────────────────────────────────────

{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

下記のショッピングのご請求書を本日発行いたしますので、請求書到着後、
請求書に記載されているお支払期限日までにお支払いいただきますよう、
お願い申し上げます。

{OrderPageAccessUrl}

【ご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料                              \{SettlementFee}
送料                                    \{DeliveryFee}

※郵送事故などにより、請求書が届かないことがございます。
　一週間ほどお待ちいただいても請求書が届かない場合には、大変お手数ですが、
　下記連絡先へご一報くださいますよう、お願い申し上げます。

※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
　届かない場合がございます。
　万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
　店舗様まで直接お問合せくださいませ。

※請求書並びに本メールが、商品よりも早く到着してしまうことがございますが、
何卒ご容赦下さいますようお願い申し上げます。
また、請求書内に明細が含まれておりますのでご確認くださいませ。

詳しくは下記パソコン用URLをご覧下さい。

http://www.seino.co.jp/financial/atobarai/guidance/

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、下記
　購入店舗様に直接お問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp

--------------------------------------------------------------

【後払い決済サービス】～最も消費者に愛される決済サービス～

  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------',3,'2020/02/09 22:25:49',9,'2020/02/09 22:25:49',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (513,101,'口振WEB申込み案内メール（CEL）','後払い決済サービス','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',null,null,null,'【後払い決済サービス】口振請求書発行案内　（ハガキで届きます）','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','
◇お願い：お問い合わせ頂く際、下記メール文面を残したままご返信ください◇


{CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【後払い決済サービス】をご利用いただきまして、
まことにありがとうございます。

下記のショッピングのご請求書を本日発行いたしますので、請求書到着後、
請求書に記載されているお支払期限日までにお支払いいただきますよう、
お願い申し上げます。

【ご注文内容】
お支払者：{CustomerNameKj}　様
ご購入店舗名：{SiteNameKj}　
ご購入日：{OrderDate}
お支払金額：{UseAmount}
ご購入商品明細：商品名／個数／購入品目計
{OrderItems}
決済手数料  \{SettlementFee}
送料 \{DeliveryFee}

※郵送事故などにより、請求書が届かないことがございます。
　一週間ほどお待ちいただいても請求書が届かない場合には、大変お手数ですが、
　下記連絡先へご一報くださいますよう、お願い申し上げます。

※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
　届かない場合がございます。
　万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
　店舗様まで直接お問合せくださいませ。

※請求書並びに本メール、商品よりも早く到着してしまうことがございますが、
何卒ご容赦下さいますようお願い申し上げます。
また、請求書内に明細が含まれておりますのでご確認くださいませ。

詳しくは下記パソコン用URLをご覧下さい。

http://www.seino.co.jp/financial/atobarai/guidance/

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、下記
　購入店舗様に直接お問合せください。

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

■お支払いに関するお問い合わせは：
セイノーフィナンシャル株式会社　後払い決済サービス担当
住所：〒503-8501 岐阜県大垣市田口町１番地
TEL:03-6908-7888 9:00～18:00　年中無休（年末・年始のぞく）
Mail: sfc-atobarai@seino.co.jp
URL: http://www.seino.co.jp/financial（パソコン専用）

--------------------------------------------------------------

【後払い決済サービス】～最も消費者に愛される決済サービス～

  お問合せ先：03-6908-7888
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: sfc-atobarai@seino.co.jp

  運営会社：セイノーフィナンシャル株式会社
　住所：〒503-8501 岐阜県大垣市田口町１番地

--------------------------------------------------------------',3,'2020/02/09 22:25:49',9,'2020/02/09 22:25:49',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (514,100,'口振WEB申込み案内メール（PC）','【BASE後払い決済】','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','customer2@ato-barai.com',null,null,null,'【BASE後払い決済】口振請求書発行案内　（ハガキで届きます）','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}様

先日は {SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

下記のショッピングのご請求書を本日発行いたしますので、請求書到着後、
請求書に記載されているお支払期限日までにお支払いいただきますよう、
お願い申し上げます。

【ご注文内容】

お支払者：{CustomerNameKj}　様

ご購入店舗名：{SiteNameKj}

ご購入日：{OrderDate}

お支払金額：{UseAmount}

ご購入商品明細：商品名／個数／購入品目計

{OrderItems}

決済手数料                              \{SettlementFee}

送料                                    \{DeliveryFee}

※郵送事故などにより、請求書が届かないことがございます。
　一週間ほどお待ちいただいても請求書が届かない場合には、大変お手数ですが、
　下記連絡先へご一報くださいますよう、お願い申し上げます。

※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
　届かない場合がございます。
　万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
　店舗様まで直接お問合せくださいませ。

※請求書並びに本メールが、商品よりも早く到着してしまうことがございますが、
　何卒ご容赦下さいますようお願い申し上げます。
　また、請求書内に明細が含まれておりますのでご確認くださいませ。


詳しくは下記URLをご覧下さい。
http://thebase.in/pages/help.html#category14_146

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、下記
　購入店舗様に直接お問合せください。


■商品・返品・配送に関するお問い合わせは：

直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

※商品到着から7日以降のキャンセルはできませんのでご注意ください。
店舗との同意の上キャンセルにより商品を返品される場合はその旨、
下記メールアドレスまで注文内容のご連絡をお願いします。

support@thebase.in

キャンセルを行なわないと
商品代金支払いの
ご請求届き続けますのでご注意ください。

{SiteNameKj} は BASE ( https://thebase.in ) で作成されています。 
BASEは誰でも簡単に無料でネットショップが開設できるサービスです。

■お支払いに関するお問い合わせは：

BASE 後払い決済　窓口
TEL:[03-6279-1149](平日土日9:00～18:00)
Mail: atobarai@thebase.in


───────────────────────────────────

BASE (ベイス)
https://thebase.in

 お問合せ先:[03-6279-1149]
 営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: atobarai@thebase.in

───────────────────────────────────',4,'2020/02/09 22:25:49',9,'2020/02/09 22:25:49',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (515,101,'口振WEB申込み案内メール（CEL）','【BASE後払い決済】','=?UTF-8?B?GyRCIVobKEJCQVNFGyRCOGVKJyQkN2g6USFbGyhC?=','customer2@ato-barai.com',null,null,null,'【BASE後払い決済】口振請求書発行案内　（ハガキで届きます）','=?UTF-8?B?44CQQkFTReW+jOaJleOBhOaxuua4iOOAkQ==?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}様

先日は {SiteNameKj}様でお買い物をして頂き
まことにありがとうございます。

下記のショッピングのご請求書を本日発行いたしますので、請求書到着後、
請求書に記載されているお支払期限日までにお支払いいただきますよう、
お願い申し上げます。

【ご注文内容】

お支払者：{CustomerNameKj}　様

ご購入店舗名：{SiteNameKj}

ご購入日：{OrderDate}

お支払金額：{UseAmount}

ご購入商品明細：商品名／個数／購入品目計

{OrderItems}

決済手数料                              \{SettlementFee}

送料                                    \{DeliveryFee}

※郵送事故などにより、請求書が届かないことがございます。
　一週間ほどお待ちいただいても請求書が届かない場合には、大変お手数ですが、
　下記連絡先へご一報くださいますよう、お願い申し上げます。

※商品につきましても、メール便などの配送方法の場合には、配送事故などにより
　届かない場合がございます。
　万が一、商品が届いていない場合には大変お手数ではございますが、ご注文された
　店舗様まで直接お問合せくださいませ。

※請求書並びに本メールが、商品よりも早く到着してしまうことがございますが、
　何卒ご容赦下さいますようお願い申し上げます。
　また、請求書内に明細が含まれておりますのでご確認くださいませ。


詳しくは下記URLをご覧下さい。
http://thebase.in/pages/help.html#category14_146

ご不明な点などございましたら、お気軽に下記までお問合せ下さい。

※メールにてお問合せをいただく場合は、必ずご注文時のお名前（フルネーム）を
　本文に入れてお問合せください。

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、下記
　購入店舗様に直接お問合せください。


■商品・返品・配送に関するお問い合わせは：

直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

※商品到着から7日以降のキャンセルはできませんのでご注意ください。
店舗との同意の上キャンセルにより商品を返品される場合はその旨、
下記メールアドレスまで注文内容のご連絡をお願いします。

support@thebase.in

キャンセルを行なわないと
商品代金支払いの
ご請求届き続けますのでご注意ください。

{SiteNameKj} は BASE ( https://thebase.in ) で作成されています。 
BASEは誰でも簡単に無料でネットショップが開設できるサービスです。

■お支払いに関するお問い合わせは：

BASE 後払い決済　窓口
TEL:[03-6279-1149](平日土日9:00～18:00)
Mail: atobarai@thebase.in


───────────────────────────────────

BASE (ベイス)
https://thebase.in

 お問合せ先:[03-6279-1149]
 営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: atobarai@thebase.in

───────────────────────────────────
',4,'2020/02/09 22:25:49',9,'2020/02/09 22:25:49',9,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (516,102,'未返金案件メール（PC）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】ご返金のご連絡','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────
{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございました。

{ReceiptDate}に{ReceiptClass}よりご入金をいただきましたが、
過剰でお支払いいただいておりましたので
以前、注文者様の住所宛に「返金のご案内」のハガキを
お送りしております。

本日現在、まだご返金の手続きが完了していない状況でございます。
手続期限がせまっておりますので、ご確認下さいますようお願いします。

【ご注文内容】
ご注文ID：{OrderId}
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
ご請求金額：{UseAmount}

なお、お手元に「返金のご案内」のハガキがない場合には
弊社にてお手続きをいたしますので、
下記をご記入のうえ、当メールへご返信ください。 　

・銀行名： 
・支店名： 
・口座種目： 
・口座番号： 
・口座名義(カナ)： 

不明点などございましたら、お気軽にお問合せください。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2016/02/23 14:00:00',1,'2022/04/20 6:08:34',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (517,103,'未返金案件メール（CEL）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】ご返金のご連絡','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────
{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございました。

{ReceiptDate}に{ReceiptClass}よりご入金をいただきましたが、
過剰でお支払いいただいておりましたので
以前、注文者様の住所宛に「返金のご案内」のハガキを
お送りしております。

本日現在、まだご返金の手続きが完了していない状況でございます。
手続期限がせまっておりますので、ご確認下さいますようお願いします。

【ご注文内容】
ご注文ID：{OrderId}
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
ご請求金額：{UseAmount}

なお、お手元に「返金のご案内」のハガキがない場合には
弊社にてお手続きをいたしますので、
下記をご記入のうえ、当メールへご返信ください。 　

・銀行名： 
・支店名： 
・口座種目： 
・口座番号： 
・口座名義(カナ)： 

不明点などございましたら、お気軽にお問合せください。

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2016/02/23 14:00:00',1,'2022/04/20 6:08:46',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (520,104,'届いてから決済請求書発行メール（PC）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】ご利用についてのご案内　 ～お好きなお支払い方法をお選びいただけます～　ご注文番号：{OrderId}','{ServiceName}','──────────────────────────────────                               
◇お願い：お問い合わせ頂く際、必ず下記メール文面を残したままご返信ください◇                               
────────────────────────────────── 
                             
※当メールは、【{ServiceName}】をご利用いただきましたお客様へ、                               
ご請求書のお届けについてご案内させていただくメールです。                               
                               
                                
 {CustomerNameKj}様                               
                               
先日は {SiteNameKj}様でのお買い物に                               
【{ServiceName}】をご利用いただきまして、                               
まことにありがとうございます。                               
                               
ご請求書を発行いたしました。
到着まで今しばらくお待ちくださいますようお願いいたします。                              
                               
※当メールは「商品発送のお知らせメール」ではございません。                               
商品の発送・到着予定日についてはご購入店様へのお問い合わせをお願い申し上げます。                               
                               
                               
【ご注文内容】                               
ご注文番号：{OrderId}                               
ご購入店舗名：{SiteNameKj}　                               
ご購入日：{OrderDate}                               
お支払金額：{UseAmount}                               
ご購入商品明細：商品名／個数／購入品目計                               
{OrderItems}                               
決済手数料                              {SettlementFee}円                               
送料                                    {DeliveryFee}円                               
                               
◇ {PaymentMethod}決済をご希望される方は下記URLにて【 {CreditLimitDate} 】までに
　お手続きをお願いいたします。
　簡易ログインページ　{OrderPageAccessUrl}  
 
※請求書の到着を待たず上記URLからお支払いいただけます。
　請求書は必ず郵送されますので、お支払い済の場合は、
　お手数ですが届いた請求書は破棄をお願いします。
                               
※キャンセル（解約申請）されている場合でも、行き違いにて当メールが                               
　配信されてしまう場合がございます。その際は大変お手数ですが、下記                               
　購入店舗様に直接お問合せください。                               
                               
※※※その他ご不明な点は下記ＵＲＬをご確認ください。※※※                               
　　　　https://atobarai-user.jp/                               
                               
■商品・返品・配送に関するお問い合わせは：                               
直接購入店舗様にお問い合わせ下さい。                               
購入店舗：{SiteNameKj}　電話：{Phone}                               
                               
■お支払いに関するお問い合わせは：                               
株式会社キャッチボール　【{ServiceName}】  
TEL:03-4326-3600 (平日土日9:00～18:00)                               
Mail: {ServiceMail}                              
                               
-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2024/01/10 0:54:17',1,'2022/06/08 18:41:19',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (521,105,'届いてから決済請求書発行メール（CEL）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】ご利用についてのご案内　 ～お好きなお支払い方法をお選びいただけます～　ご注文番号：{OrderId}','{ServiceName}','──────────────────────────────────                               
◇お願い：お問い合わせ頂く際、必ず下記メール文面を残したままご返信ください◇                               
────────────────────────────────── 
                             
※当メールは、【{ServiceName}】をご利用いただきましたお客様へ、                               
ご請求書のお届けについてご案内させていただくメールです。                               
                               
                                
 {CustomerNameKj}様                               
                               
先日は {SiteNameKj}様でのお買い物に                               
【{ServiceName}】をご利用いただきまして、                               
まことにありがとうございます。                               
                               
ご請求書を発行いたしました。
到着まで今しばらくお待ちくださいますようお願いいたします。                              
                               
※当メールは「商品発送のお知らせメール」ではございません。                               
商品の発送・到着予定日についてはご購入店様へのお問い合わせをお願い申し上げます。                               
                               
                               
【ご注文内容】                               
ご注文番号：{OrderId}                               
ご購入店舗名：{SiteNameKj}　                               
ご購入日：{OrderDate}                               
お支払金額：{UseAmount}                               
ご購入商品明細：商品名／個数／購入品目計                               
{OrderItems}                               
決済手数料                              {SettlementFee}円                               
送料                                    {DeliveryFee}円                               
                               
◇ {PaymentMethod}決済をご希望される方は下記URLにて【 {CreditLimitDate} 】までに
　お手続きをお願いいたします。
　簡易ログインページ　{OrderPageAccessUrl}  
 
※請求書の到着を待たず上記URLからお支払いいただけます。
　請求書は必ず郵送されますので、お支払い済の場合は、
　お手数ですが届いた請求書は破棄をお願いします。                 
                                                 
※キャンセル（解約申請）されている場合でも、行き違いにて当メールが                               
　配信されてしまう場合がございます。その際は大変お手数ですが、下記                               
　購入店舗様に直接お問合せください。                               
                               
※※※その他ご不明な点は下記ＵＲＬをご確認ください。※※※                               
　　　　https://atobarai-user.jp/                               
                               
■商品・返品・配送に関するお問い合わせは：                               
直接購入店舗様にお問い合わせ下さい。                               
購入店舗：{SiteNameKj}　電話：{Phone}                               
                               
■お支払いに関するお問い合わせは：                               
株式会社キャッチボール　【{ServiceName}】  
TEL:03-4326-3600 (平日土日9:00～18:00)                               
Mail: {ServiceMail}                              
                               
-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2024/01/10 0:54:17',1,'2022/06/08 18:41:27',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (522,108,'届いてから決済請求書発行メール(同梱)（PC）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】ご利用についてのご案内　 ～お好きなお支払い方法をお選びいただけます～　ご注文番号：{OrderId}','{ServiceName}','────────────────────────────────── 
◇お願い：お問い合わせ頂く際、必ず下記メール文面を残したままご返信ください◇
──────────────────────────────────

※当メールは、【{ServiceName}】をご利用いただきましたお客様へお送りしております。
「商品発送のお知らせメール」ではございません。
商品の発送・到着予定日についてはご購入店様へのお問い合わせをお願い申し上げます。

                                
 {CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【{ServiceName}】をご利用いただきまして、
まことにありがとうございます。

■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□

ご請求書（払込用紙）は商品と一緒にお届けいたしますので、
商品到着後、請求書に記載のお支払い期限日までにお支払いいただきますよう、
お願い申し上げます。 

■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□ 


【ご注文内容】
ご注文番号：{OrderId}
ご購入店舗名：{SiteNameKj}
ご購入日：{OrderDate}
お支払金額：{UseAmount} 
ご購入商品明細：商品名／個数／購入品目計 
{OrderItems} 
決済手数料                              {SettlementFee}円   
送料                                    {DeliveryFee}円 

◇ {PaymentMethod}決済をご希望される方は下記URLにて【 {CreditLimitDate} 】までに
　お手続きをお願いいたします。
　簡易ログインページ　{OrderPageAccessUrl}   

※請求書の到着を待たず上記URLからお支払いいただけます。
  請求書は必ず商品に同梱されておりますので、お支払い済の場合は、
  お手数ですが届いた請求書は破棄をお願いします。       

※商品と共に請求書が入っていない場合には、大変お手数ですが、
　【{ServiceName}】カスタマーセンターへご一報くださいますよう、お願い申し上げます。
 
※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、下記
　購入店舗様に直接お問合せください。
 
※※※その他ご不明な点は下記ＵＲＬをご確認ください。※※※
　　　　https://atobarai-user.jp/

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone} 

■お支払いに関するお問い合わせは： 
株式会社キャッチボール　【{ServiceName}】
TEL:03-4326-3600 (平日土日9:00～18:00) 
Mail: {ServiceMail} 

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2025/08/01 11:22:35',1,'2022/06/08 18:41:34',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (523,109,'届いてから決済請求書発行メール(同梱)（CEL）','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】ご利用についてのご案内　 ～お好きなお支払い方法をお選びいただけます～　ご注文番号：{OrderId}','{ServiceName}','────────────────────────────────── 
◇お願い：お問い合わせ頂く際、必ず下記メール文面を残したままご返信ください◇
──────────────────────────────────

※当メールは、【{ServiceName}】をご利用いただきましたお客様へお送りしております。
「商品発送のお知らせメール」ではございません。
商品の発送・到着予定日についてはご購入店様へのお問い合わせをお願い申し上げます。

                                
 {CustomerNameKj}様

先日は {SiteNameKj}様でのお買い物に
【{ServiceName}】をご利用いただきまして、
まことにありがとうございます。

■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□

ご請求書（払込用紙）は商品と一緒にお届けいたしますので、
商品到着後、請求書に記載のお支払い期限日までにお支払いいただきますよう、
お願い申し上げます。 

■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□■□ 


【ご注文内容】
ご注文番号：{OrderId}
ご購入店舗名：{SiteNameKj}
ご購入日：{OrderDate}
お支払金額：{UseAmount} 
ご購入商品明細：商品名／個数／購入品目計 
{OrderItems} 
決済手数料                              {SettlementFee}円   
送料                                    {DeliveryFee}円 

◇ {PaymentMethod}決済をご希望される方は下記URLにて【 {CreditLimitDate} 】までに
　お手続きをお願いいたします。
　簡易ログインページ　{OrderPageAccessUrl}   

※請求書の到着を待たず上記URLからお支払いいただけます。
  請求書は必ず商品に同梱されておりますので、お支払い済の場合は、
  お手数ですが届いた請求書は破棄をお願いします。        

※商品と共に請求書が入っていない場合には、大変お手数ですが、
　【{ServiceName}】カスタマーセンターへご一報くださいますよう、お願い申し上げます。
 
※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。その際は大変お手数ですが、下記
　購入店舗様に直接お問合せください。
 
※※※その他ご不明な点は下記ＵＲＬをご確認ください。※※※
　　　　https://atobarai-user.jp/

■商品・返品・配送に関するお問い合わせは：
直接購入店舗様にお問い合わせ下さい。
購入店舗：{SiteNameKj}　電話：{Phone} 

■お支払いに関するお問い合わせは： 
株式会社キャッチボール　【{ServiceName}】
TEL:03-4326-3600 (平日土日9:00～18:00) 
Mail: {ServiceMail} 

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2025/08/01 11:22:35',1,'2022/06/08 18:41:42',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (524,1,'事業者登録完了（サービス開始）メール','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'【後払い.com】 店舗審査通過のお知らせ','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}　様

この度は弊社サービス、【後払いドットコム】にお申込いただき、
まことにありがとうございます。

審査の結果、通過となりましたので、後払い決済管理システムを
ご利用いただくのに必要なIDを報告申し上げます。

重要なご案内となりますので、最後までお読みください。

【管理サイトＵＲＬ】
https://www.atobarai.jp/member/




ID : {LoginId}




※パスワードは別途メールにてお送りさせていただきます。
※サイトＩＤは上記ＩＤとは異なりますのでご注意ください。
サイトＩＤの参照方法は以下の通りです。

【1】管理サイトにログイン
　　↓　↓　↓　↓
【2】「登録情報管理」をクリック
　　↓　↓　↓　↓
【3】「サイト情報」をクリック
　　↓　↓　↓　↓
【4】「サイトＩＤ」欄に表示されます。

 ■マニュアルのダウンロード（必須）
下記のURLより、【後払いドットコム】の運用マニュアルをダウンロード
してご使用下さい。
サービス開始に必要なマニュアルとなっておりますので、必ずご確認
いただきますようお願い申し上げます。

 https://www.atobarai.jp/doc/help/Atobarai.com_Manual.pdf

※閲覧にはAdobe PDF Reader が必要です。インストールされていない
方は、下記のURLより同ソフトのインストールをお願いいたします。

  http://www.adobe.com/jp/products/acrobat/readstep2.html

管理システムのご利用方法は、ダウンロードしていただいたマニュアル
をご確認ください。

サービスの開始まで、店舗様には以下のような作業をしていただきます。
開始のご連絡をお忘れなきよう、お願い申し上げます。

■■■　STEP 1　■■■登録内容のご確認

管理サイトにログイン、店舗情報を確認（プランその他の情報）

■■■　STEP 2　■■■定型文章のサイト掲載

マニュアルにしたがって、店舗様サイト上に当決済方法用の定型文章を掲載
（特定商取引法ページや決済選択画面など）

サイト掲載用定型文・画像提供ページ：

http://www.ato-barai.com/for_shops/tokuteishou.html

※この時点でサービス開始となります

■消費者様向け後払いドットコム動画＆販促バナーダウンロードページ
http://www.ato-barai.com/download/

消費者様向け動画は、初めて当決済をご利用になる消費者様にとって
分かり易くなり、お問合せを減らせる効果が期待できます。
さらに販促バナーは、後払い決済が出来るお店としてアピールできるため、
販促の効果にもつながりますので、こちらも併せてご活用ください。

■■■　STEP 3　■■■サービス開始の当社へのご通知

サービスを開始した旨を、当社までメールもしくはお電話にてご連絡下さい。
 mail: customer@ato-barai.com
 tel:  0120-667-690

■■■　STEP 4　■■■当社が決済画面を確認

当社担当が決済画面を確認させていただき、問題がなければそのまま運営、
問題があれば修正のお願いをさせていただくことがございます。

  ↑↑↑「流れ」はここまで

■消費者様への請求書のご案内用紙のダウンロード（任意）
下記のＵＲＬより請求書のご案内用紙をダウンロードして、商品に同梱
してください。
（ご案内用紙の同梱は店舗様のご判断による任意で行っていただいて
　おりますが、初めて当決済をご利用なる消費者様にとっては分かり易く
　なり、お問合せが減ることにも繋がりますので、同梱していただくこと
　を推奨しております。）

https://www.atobarai.jp/doc/download/doukonnyou.xls


サービス開始に当たって、また、運営に関するお問い合わせ等は、
メール末尾のご連絡先にお気軽にお問合せ下さい。

＊＊＊＊＊＊＊＊＊＊＊＊【注意事項】＊＊＊＊＊＊＊＊＊＊＊＊

１）以下に該当するご注文は、保証対象外となってしまいますので
　　ご注意ください。

※保証外とは、未払いの保証が付かず、消費者様からの入金が
　ない限りは店舗様へ入金させていただく事ができません。
　
・Web上にてお荷物の追跡ができない配送方法を使われたご注文
・伝票登録時に配送会社や伝票番号を誤った情報で登録されたご注文
・配達状況がキャンセル・持ち戻り等により配達完了の確認が
　とれないご注文
・実際に発送された配送方法に関わらず、伝票登録時の配送方法に
　【メール便】を選択して登録されたご注文
・紛争性があるご注文

２）配送伝票番号をご登録いただいた、当日又は、翌営業日に
　　ご注文者様に対して、ご請求書が発送されます。
※商品発送前に配送伝票番号をご登録いただきますと、請求書が商品
　より先に届いてしまう可能性が高くなりますので、商品発送後に
　配送伝票番号のご登録をお願いいたします。

３）締日までに弊社側で商品の着荷確認がとれたご注文分が
　　当該締日分の立替対象となります。
※伝票番号登録日や、配達完了日ではなく、弊社側で着荷確認が
　とれた日がベースとなりますのでご注意ください。

４）新たにウェブサイトまたはカタログ等で後払いドットコムのサービスを
    ご利用いただく場合、もしくは新たな商品を販売する場合、
    新たなサービスをご提供される場合は事前にご連絡くださいますよう
    お願い申しあげます。 
※未審査のものは、後払いドットコムのサービスはご利用いただけません。

＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊


今後とも末永いお付き合いの程、宜しくお願い申し上げます。

株式会社キャッチボール　後払いドットコム事業部　スタッフ一同

--------------------------------------------------------------

【後払いドットコム】～最も消費者に愛される決済サービス～

  お問合せ先：0120-667-690
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com

  運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿6-14-1　新宿グリーンタワー14階 

--------------------------------------------------------------',6,'2026/04/09 12:59:17',83,'2026/04/09 12:59:49',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (525,1,'事業者登録完了（サービス開始）メール','','','customer@ato-barai.com',null,null,null,'','','',5,'2020/11/05 16:34:54',83,'2020/11/05 16:34:54',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (526,39,'支払期限超過メール（再１）（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',null,null,null,'再１【後払いドットコム】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございます。

お支払い期限を過ぎてもご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。
お手元に届き次第、期限までにお支払いをお願いいたします。

【ご請求内容】
ご注文番号：{OrderId}
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
http://www.ato-barai.com/guidance/faq.html

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿6-14-1　新宿グリーンタワー14階 
--------------------------------------------------------------
',6,'2021/08/16 18:39:52',83,'2022/05/08 13:51:24',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (527,110,'与信実行待ち注文通知','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【障害】30分以上与信実行待ちになっている注文があります','{ServiceName}','30分以上与信実行待ちになっている注文があります。
スレッドの一時的な変更をお願いします。
対象注文は以下
{OrderList}',null,'2021/03/03 16:17:23',1,'2022/04/07 16:36:50',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (528,100,'口振WEB申込み案内メール（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】口座引き落とし WEBお申し込みのご案内：{OrderId}','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}様（{OrderId}）


{SiteNameKj}様での口座引き落としに関するご連絡です。

口座引き落としでの決済にあたり「WEB申込み」を選択されたお客様へ、
以下、お申し込み手順をご案内いたします。


【１】下記どちらかのURLより、マイページへログインしてください。

・簡易ログインURL
{OrderPageAccessUrl}

ログインにはご注文時のお電話番号をご利用ください。
簡易ログインURLの有効期間は{LimitDate}より14日間となります。

{LimitDate}より14日間を経過されている場合は
下記、通常ログインURLよりログインください。


・通常ログインURL
https://www.atobarai.jp/orderpage

ログインにはご注文時のお電話番号と、請求書・払込受領票に
記載されているパスワードをご利用ください。

※請求書・払込受領票をお手元にお持ちでなく
　パスワードが分からない場合は、このメールへご返信ください。


【２】『口座振替　情報の登録へ』のリンクへ進み、
表示される案内に従い、お手続きください。


以上でございます。

お手続きが完了するまでは、後払いドットコムよりお送りする
請求書でのお支払いとなります。



ご不明な点がございましたらご返信にてお問い合わせください。

よろしくお願いいたします。


--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-4326-3600
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒140-0002　
　　　　東京都品川区東品川2-2-24　天王洲セントラルタワー 12F
--------------------------------------------------------------',6,'2021/10/05 19:40:26',18051,'2021/10/05 19:40:26',18051,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (529,93,'マイページパスワード再発行メール（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'【後払いドットコム】口座引き落とし WEBお申し込みのご案内：{OrderId}','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}様（{OrderId}）


{SiteNameKj}様での口座引き落としに関するご連絡です。

口座引き落としでの決済にあたり「WEB申込み」を選択されたお客様へ、
以下、お申し込み手順をご案内いたします。


【１】下記どちらかのURLより、マイページへログインしてください。

・簡易ログインURL
{OrderPageAccessUrl}

ログインにはご注文時のお電話番号をご利用ください。
簡易ログインURLの有効期間は{LimitDate}より14日間となります。

{LimitDate}より14日間を経過されている場合は
下記、通常ログインURLよりログインください。


・通常ログインURL
https://www.atobarai.jp/orderpage

ログインにはご注文時のお電話番号と、請求書・払込受領票に
記載されているパスワードをご利用ください。

※請求書・払込受領票をお手元にお持ちでなく
　パスワードが分からない場合は、このメールへご返信ください。


【２】『口座振替　情報の登録へ』のリンクへ進み、
表示される案内に従い、お手続きください。


以上でございます。

お手続きが完了するまでは、後払いドットコムよりお送りする
請求書でのお支払いとなります。



ご不明な点がございましたらご返信にてお問い合わせください。

よろしくお願いいたします。


--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-4326-3600
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒140-0002　
　　　　東京都品川区東品川2-2-24　天王洲セントラルタワー 12F
--------------------------------------------------------------',6,'2021/10/05 19:40:54',18051,'2021/10/05 19:40:54',18051,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (540,111,'口座振替成立メール（正常）(PC)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'口座振替成立メール（正常）','{ServiceName}','みずほファクター加盟店のみ利用可能の為、設定しない',null,'2021/12/30 21:03:08',0,'2022/04/07 16:36:45',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (541,112,'口座振替成立メール（正常）(CEL)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'口座振替成立メール（正常）','{ServiceName}','口座振替成立メール（正常）',null,'2021/12/30 21:03:08',0,'2022/04/07 16:36:37',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (542,113,'口座振替成立メール（エラー、中止、登録処理中）(PC)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'口座振替成立メール（エラー、中止、登録処理中）','{ServiceName}','みずほファクター加盟店のみ利用可能の為、設定しない',null,'2021/12/30 21:03:08',0,'2022/04/07 16:36:29',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (543,114,'口座振替成立メール（エラー、中止、登録処理中）(CEL)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'口座振替成立メール（エラー、中止、登録処理中）','{ServiceName}','',null,'2021/12/30 21:03:08',0,'2022/04/07 16:36:22',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (544,115,'もうすぐ口座振替メール(PC)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】口座振替のご案内','{ServiceName}','{CustomerNameKj}様


この度は、{SiteNameKj}での利用料金のお支払いに、
【(株)キャッチボール】立替払口座振替をご利用いただきまして、
まことにありがとうございます。

下記のご利用内容で承っていますので、口座振替の申込をいただいた
口座に、前日までに引落に必要な金額をご入金いただくようお願い申し上げます。

◆口座振替引落日：{CreditTransferDate} 
お支払金額：{UseAmount}　






【ご利用内容】
ご契約者：{CustomerNameKj}　様
ご利用店舗名：{SiteNameKj}　
ご利用申込日：{OrderDate}
ご利用の料金内容：{OneOrderItem}

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。
　その際は大変お手数ですが、下記ご利用店舗様に直接お問合せください。

※※その他ご不明な点は下記ＵＲＬをご確認ください。
　　　  　　　https://atobarai-user.jp/faq/

◆ご利用の料金内容：商品等に関するお問い合わせは：
直接ご利用店舗様にお問い合わせ下さい。
ご利用店舗：{SiteNameKj}　電話：{Phone}

◆お支払いに関するお問い合わせは：
株式会社キャッチボール　【{ServiceName}】
TEL:03-4326-3600(平日土日9:00～18:00)
Mail: {ServiceMail}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2021/12/30 21:03:08',0,'2022/06/08 18:42:42',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (545,116,'もうすぐ口座振替メール(CEL)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】口座振替のご案内','{ServiceName}','{CustomerNameKj}様


この度は、{SiteNameKj}での利用料金のお支払いに、
【(株)キャッチボール】立替払口座振替をご利用いただきまして、
まことにありがとうございます。

下記のご利用内容で承っていますので、口座振替の申込をいただいた
口座に、前日までに引落に必要な金額をご入金いただくようお願い申し上げます。

◆口座振替引落日：{CreditTransferDate} 
お支払金額：{UseAmount}　






【ご利用内容】
ご契約者：{CustomerNameKj}　様
ご利用店舗名：{SiteNameKj}　
ご利用申込日：{OrderDate}
ご利用の料金内容：{OneOrderItem}

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。
　その際は大変お手数ですが、下記ご利用店舗様に直接お問合せください。

※※その他ご不明な点は下記ＵＲＬをご確認ください。
　　　  　　　https://atobarai-user.jp/faq/

◆ご利用の料金内容：商品等に関するお問い合わせは：
直接ご利用店舗様にお問い合わせ下さい。
ご利用店舗：{SiteNameKj}　電話：{Phone}

◆お支払いに関するお問い合わせは：
株式会社キャッチボール　【{ServiceName}】
TEL:03-4326-3600(平日土日9:00～18:00)
Mail: {ServiceMail}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2021/12/30 21:03:08',0,'2022/06/08 18:46:08',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (546,117,'口座振替　引落完了メール(PC)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】ご入金を確認いたしました','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}様

この度は、{SiteNameKj}様で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございました。

{ReceiptDate}に{CustomerNameKj}様からのご入金を
確認いたしましたのでご報告いたします。

以下が、今回ご入金いただいたご注文の内容でございます。

【領収済みご注文内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名：{OrderItems}
ご入金金額：{UseAmount}
◆口座振替引落日：{CreditTransferDate} 

またのご利用を心よりお待ちしております。

なお、ご入金額とご請求金額に差異がある場合は、ご連絡、ご請求
をさせていただく場合がございます。

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
https://atobarai-user.jp/

■商品の返品・未着など商品については
直接ご購入店様にお問い合わせください。
ご購入店様：{SiteNameKj}
電話：{Phone}
URL：{SiteUrl}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2021/12/30 21:03:08',0,'2022/04/20 6:13:51',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (547,118,'口座振替　引落完了メール(CEL)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】ご入金を確認いたしました','{ServiceName}','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
────────────────────────────────── 

{CustomerNameKj}様

この度は、{SiteNameKj}様で商品ご購入の際に、
【{ServiceName}】をご利用いただきまして
まことにありがとうございました。

{ReceiptDate}に{CustomerNameKj}様からのご入金を
確認いたしましたのでご報告いたします。

以下が、今回ご入金いただいたご注文の内容でございます。

【領収済みご注文内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名：{OrderItems}
ご入金金額：{UseAmount}
◆口座振替引落日：{CreditTransferDate} 

またのご利用を心よりお待ちしております。

なお、ご入金額とご請求金額に差異がある場合は、ご連絡、ご請求
をさせていただく場合がございます。

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
https://atobarai-user.jp/

■商品の返品・未着など商品については
直接ご購入店様にお問い合わせください。
ご購入店様：{SiteNameKj}
電話：{Phone}
URL：{SiteUrl}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2021/12/30 21:03:08',0,'2022/04/20 6:13:59',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (548,119,'支払期限超過メール（口振 再1）(PC)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】ご利用料金お支払のお願い','{ServiceName}','{CustomerNameKj}様

この度は、{SiteNameKj}での利用料金のお支払いに、
【(株)キャッチボール】立替払口座振替をご利用いただきまして、
まことにありがとうございます。

{CustomerNameKj}様からのご入金を確認できませんでしたので
本日、再請求書を発行・発送いたしました。
（再請求手数料を加算させていただいています）
お手元に届き次第、期限までにお支払いをお願いいたします。

お支払金額：{TotalAmount}　

【ご利用内容】
ご契約者：{CustomerNameKj}　様
ご利用店舗名：{SiteNameKj}　
ご利用申込日：{OrderDate}
ご利用の料金内容：{OneOrderItem}


◆お支払期限を過ぎてしまいますと、更に再請求手数料が加算されますので、ご注意下さい。

※その他ご不明な点は下記ＵＲＬをご確認ください。
　　　  　　　https://atobarai-user.jp/faq/　

◆ご利用の料金内容：商品等に関するお問い合わせは：
直接ご利用店舗様にお問い合わせ下さい。
ご利用店舗：{SiteNameKj}　電話：{Phone}

◆お支払いに関するお問い合わせは：
株式会社キャッチボール　【{ServiceName}】
TEL:03-4326-3600(平日土日9:00～18:00)
Mail: {ServiceMail}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2021/12/30 21:03:08',0,'2022/04/20 6:14:16',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (549,120,'支払期限超過メール（口振 再1）(CEL)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】ご利用料金お支払のお願い','{ServiceName}','{CustomerNameKj}様

この度は、{SiteNameKj}での利用料金のお支払いに、
【(株)キャッチボール】立替払口座振替をご利用いただきまして、
まことにありがとうございます。

{CustomerNameKj}様からのご入金を確認できませんでしたので
本日、再請求書を発行・発送いたしました。
（再請求手数料を加算させていただいています）
お手元に届き次第、期限までにお支払いをお願いいたします。

お支払金額：{TotalAmount}　

【ご利用内容】
ご契約者：{CustomerNameKj}　様
ご利用店舗名：{SiteNameKj}　
ご利用申込日：{OrderDate}
ご利用の料金内容：{OneOrderItem}


◆お支払期限を過ぎてしまいますと、更に再請求手数料が加算されますので、ご注意下さい。

※その他ご不明な点は下記ＵＲＬをご確認ください。
　　　  　　　https://atobarai-user.jp/faq/　

◆ご利用の料金内容：商品等に関するお問い合わせは：
直接ご利用店舗様にお問い合わせ下さい。
ご利用店舗：{SiteNameKj}　電話：{Phone}

◆お支払いに関するお問い合わせは：
株式会社キャッチボール　【{ServiceName}】
TEL:03-4326-3600(平日土日9:00～18:00)
Mail: {ServiceMail}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
 お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
 営業時間： 9:00～18:00　年中無休（年末・年始のぞく）
 mail：{ServiceMail}
 運営会社：株式会社キャッチボール
 住所：〒140-0002
　　　 東京都品川区東品川2-2-24 天王洲セントラルタワー 12F
-----------------------------------------------------------
',null,'2021/12/30 21:03:09',0,'2022/04/20 6:14:25',18137,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (550,115,'もうすぐ口座振替メール(PC)','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'もうすぐ口座振替メール','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{CustomerNameKj}様


この度は、{SiteNameKj}での利用料金のお支払いに、
【(株)キャッチボール】立替払口座振替をご利用いただきまして、
まことにありがとうございます。

下記のご利用内容で承っていますので、口座振替の申込をいただいた
口座に、前日までに引落に必要な金額をご入金いただくようお願い申し上げます。

◆口座振替引落日：{CreditTransferDate}
お支払金額：{UseAmount}　






【ご利用内容】
ご契約者：{CustomerNameKj}　様
ご利用店舗名：{SiteNameKj}　
ご利用申込日：{OrderDate}
ご利用の料金内容：{OneOrderItem}

◆口座振替にご利用いただく口座の通帳やネットバンキングの入出金明細には、
以下のような摘要が記載されますので、ご留意下さい
　　　「MHF){MhfCreditTransferDisplayName}」

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。
　その際は大変お手数ですが、下記ご利用店舗様に直接お問合せください。

※※その他ご不明な点は下記ＵＲＬをご確認ください。
　　　  　　　https://atobarai-user.jp/faq/

◆ご利用の料金内容：商品等に関するお問い合わせは：
直接ご利用店舗様にお問い合わせ下さい。
ご利用店舗：{SiteNameKj}　電話：{Phone}

◆お支払いに関するお問い合わせは：
株式会社キャッチボール　後払いドットコム事業部
TEL:03-4326-3600(平日土日9:00～18:00)
Mail: customer@ato-barai.com

--------------------------------------------------------------
【後払いドットコム】～最も消費者に愛される決済サービス～
  お問合せ先：03-4326-3600
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
 
  運営会社：株式会社キャッチボール
　住所：〒140-0002　東京都品川区東品川2-2-24　天王洲セントラルタワー 12F
--------------------------------------------------------------
',6,'2022/01/13 19:37:04',83,'2022/03/08 8:41:17',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (551,116,'もうすぐ口座振替メール(CEL)','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'もうすぐ口座振替メール','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{CustomerNameKj}様


この度は、{SiteNameKj}での利用料金のお支払いに、
【(株)キャッチボール】立替払口座振替をご利用いただきまして、
まことにありがとうございます。

下記のご利用内容で承っていますので、口座振替の申込をいただいた
口座に、前日までに引落に必要な金額をご入金いただくようお願い申し上げます。

◆口座振替引落日：{CreditTransferDate} /毎月26日（銀行休業日は翌営業日）
お支払金額：{UseAmount}　






【ご利用内容】
お支払者：{CustomerNameKj}　様
ご利用店舗名：{SiteNameKj}　
ご利用申込日：{OrderDate}
ご利用の料金内容：{OneOrderItem}

◆口座振替にご利用いただく口座の通帳やネットバンキングの入出金明細には、以下のような摘要が記載されますので、ご留意下さい
　　　「MHF){MhfCreditTransferDisplayName}」

※キャンセル（解約申請）されている場合でも、行き違いにて当メールが
　配信されてしまう場合がございます。
　その際は大変お手数ですが、下記ご利用店舗様に直接お問合せください。

※※その他ご不明な点は下記ＵＲＬをご確認ください。
　　　  　　　https://atobarai-user.jp/faq/

◆ご利用の料金内容：商品等に関するお問い合わせは：
直接ご利用店舗様にお問い合わせ下さい。
ご利用店舗：{SiteNameKj}　電話：{Phone}

◆お支払いに関するお問い合わせは：
株式会社キャッチボール　後払いドットコム事業部
TEL:03-4326-3600(平日土日9:00～18:00)
Mail: customer@ato-barai.com

--------------------------------------------------------------
【後払いドットコム】～最も消費者に愛される決済サービス～
  お問合せ先：03-4326-3600
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
 
  運営会社：株式会社キャッチボール
　住所：〒140-0002　東京都品川区東品川2-2-24　天王洲セントラルタワー 12F
--------------------------------------------------------------
',6,'2022/01/13 19:37:25',83,'2022/01/23 22:35:51',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (552,117,'口座振替　引落完了メール(PC)','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'口座振替　引落完了メール','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{CustomerNameKj}　様

この度は、{SiteNameKj}での利用料金のお支払いに、
【(株)キャッチボール】立替払口座振替をご利用いただきまして、
まことにありがとうございます。

{CustomerNameKj}様からのご入金を
確認いたしましたのでご報告申し上げます。

以下が、今回ご入金いただいたご利用内容となります。

◆口座振替引落日：{CreditTransferDate}
 お支払金額：{UseAmount}　

【ご利用内容】
ご契約者：{CustomerNameKj}　様
ご利用店舗名：{SiteNameKj}　
ご利用申込日：{OrderDate}
ご利用の料金内容：{OneOrderItem}
　
◆口座振替にご利用いただく口座の通帳やネットバンキングの入出金明細には、
以下のような摘要が記載されますので、ご留意下さい　
　　　「MHF){MhfCreditTransferDisplayName}」

※その他ご不明な点は下記ＵＲＬをご確認ください。
　　　  　　　https://atobarai-user.jp/faq/　

◆ご利用の料金内容：商品等に関するお問い合わせは：
直接ご利用店舗様にお問い合わせ下さい。
ご利用店舗：{SiteNameKj}　電話：{Phone}

◆お支払いに関するお問い合わせは：
株式会社キャッチボール　後払いドットコム事業部
TEL:03-4326-3600(平日土日9:00～18:00)
Mail: customer@ato-barai.com
--------------------------------------------------------------
【後払いドットコム】～最も消費者に愛される決済サービス～
  お問合せ先：03-4326-3600
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
 
  運営会社：株式会社キャッチボール
　住所：〒140-0002　東京都品川区東品川2-2-24　天王洲セントラルタワー 12F
--------------------------------------------------------------
',6,'2022/01/13 19:37:46',83,'2022/03/08 8:41:31',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (553,118,'口座振替　引落完了メール(CEL)','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'口座振替　引落完了メール','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{CustomerNameKj}　様

この度は、{SiteNameKj}での利用料金のお支払いに、
【(株)キャッチボール】立替払口座振替をご利用いただきまして、
まことにありがとうございます。

{CustomerNameKj}様からのご入金を
確認いたしましたのでご報告申し上げます。

以下が、今回ご入金いただいたご利用内容となります。

◆口座振替引落日：{CreditTransferDate} /毎月26日（銀行休業日は翌営業日）
 お支払金額：{UseAmount}　

【ご利用内容】
お支払者：{CustomerNameKj}　様
ご利用店舗名：{SiteNameKj}　
ご利用申込日：{OrderDate}
ご利用の料金内容：商品名等　{OneOrderItem}
　
◆口座振替にご利用いただく口座の通帳やネットバンキングの入出金明細には、以下のような摘要が
　記載されますので、ご留意下さい　
　　　「MHF){MhfCreditTransferDisplayName}」

※その他ご不明な点は下記ＵＲＬをご確認ください。
　　　  　　　https://atobarai-user.jp/faq/　

◆ご利用の料金内容：商品等に関するお問い合わせは：
直接ご利用店舗様にお問い合わせ下さい。
ご利用店舗：{SiteNameKj}　電話：{Phone}

◆お支払いに関するお問い合わせは：
株式会社キャッチボール　後払いドットコム事業部
TEL:03-4326-3600(平日土日9:00～18:00)
Mail: customer@ato-barai.com
--------------------------------------------------------------
【後払いドットコム】～最も消費者に愛される決済サービス～
  お問合せ先：03-4326-3600
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
 
  運営会社：株式会社キャッチボール
　住所：〒140-0002　東京都品川区東品川2-2-24　天王洲セントラルタワー 12F
--------------------------------------------------------------
',6,'2022/01/13 19:38:11',83,'2022/01/23 22:33:49',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (554,111,'口座振替成立メール（正常）(PC)','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'口座振替成立メール（正常）','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','口座振替成立メール（正常）',6,'2022/01/13 19:38:41',83,'2022/01/13 19:38:41',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (555,112,'口座振替成立メール（正常）(CEL)','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'口座振替成立メール（正常）','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','口座振替成立メール（正常）',6,'2022/01/13 19:39:17',83,'2022/01/13 19:39:17',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (556,113,'口座振替成立メール（エラー、中止、登録処理中）(PC)','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'口座振替成立メール（エラー、中止、登録処理中）','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','口座振替成立メール（エラー、中止、登録処理中）',6,'2022/01/13 19:39:38',83,'2022/01/13 19:39:38',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (557,114,'口座振替成立メール（エラー、中止、登録処理中）(CEL)','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'口座振替成立メール（エラー、中止、登録処理中）','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','口座振替成立メール（エラー、中止、登録処理中）',6,'2022/01/13 19:39:58',83,'2022/01/13 19:39:58',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (558,119,'支払期限超過メール（口振 再1）(PC)','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'支払期限超過メール（口振 再1）','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{CustomerNameKj}様

この度は、{SiteNameKj}での利用料金のお支払いに、
【(株)キャッチボール】立替払口座振替をご利用いただきまして、
まことにありがとうございます。

{CustomerNameKj}様からのご入金を確認できませんでしたので
本日、再請求書を発行・発送いたしました。
（再請求手数料を加算させていただいています）
お手元に届き次第、期限までにお支払いをお願いいたします。

お支払金額：{TotalAmount}　

【ご利用内容】
ご契約者：{CustomerNameKj}　様
ご利用店舗名：{SiteNameKj}　
ご利用申込日：{OrderDate}
ご利用の料金内容：{OneOrderItem}


◆お支払期限を過ぎてしまいますと、更に再請求手数料が加算されますので、ご注意下さい。

※その他ご不明な点は下記ＵＲＬをご確認ください。
　　　  　　　https://atobarai-user.jp/faq/　

◆ご利用の料金内容：商品等に関するお問い合わせは：
直接ご利用店舗様にお問い合わせ下さい。
ご利用店舗：{SiteNameKj}　電話：{Phone}

◆お支払いに関するお問い合わせは：
株式会社キャッチボール　後払いドットコム事業部
TEL:03-4326-3600(平日土日9:00～18:00)
Mail: customer@ato-barai.com

--------------------------------------------------------------
【後払いドットコム】～最も消費者に愛される決済サービス～
  お問合せ先：03-4326-3600
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
 
  運営会社：株式会社キャッチボール
　住所：〒140-0002　東京都品川区東品川2-2-24　天王洲セントラルタワー 12F
--------------------------------------------------------------
',6,'2022/01/13 19:40:27',83,'2022/03/08 8:41:43',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (559,120,'支払期限超過メール（口振 再1）(CEL)','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'支払期限超過メール（口振 再1）','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{CustomerNameKj}様

この度は、{SiteNameKj}での利用料金のお支払いに、
【(株)キャッチボール】立替払口座振替をご利用いただきまして、
まことにありがとうございます。

{CustomerNameKj}様からのご入金を確認できませんでしたので
本日、再請求書を発行・発送いたしました。
（再請求手数料を加算させていただいています）
お手元に届き次第、期限までにお支払いをお願いいたします。

お支払金額：{UseAmount}　

【ご利用内容】
お支払者：{CustomerNameKj}　様
ご利用店舗名：{SiteNameKj}　
ご利用申込日：{OrderDate}
ご利用の料金内容：{OneOrderItem}


◆お支払期限を過ぎてしまいますと、更に再請求手数料が加算されますので、ご注意下さい。

※その他ご不明な点は下記ＵＲＬをご確認ください。
　　　  　　　https://atobarai-user.jp/faq/　

◆ご利用の料金内容：商品等に関するお問い合わせは：
直接ご利用店舗様にお問い合わせ下さい。
ご利用店舗：{SiteNameKj}　電話：{Phone}

◆お支払いに関するお問い合わせは：
株式会社キャッチボール　後払いドットコム事業部
TEL:03-4326-3600(平日土日9:00～18:00)
Mail: customer@ato-barai.com

--------------------------------------------------------------
【後払いドットコム】～最も消費者に愛される決済サービス～
  お問合せ先：03-4326-3600
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
 
  運営会社：株式会社キャッチボール
　住所：〒140-0002　東京都品川区東品川2-2-24　天王洲セントラルタワー 12F
--------------------------------------------------------------
',6,'2022/01/13 19:40:46',83,'2022/01/23 22:36:00',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (560,41,'支払期限超過メール（再３）（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'再３【後払いドットコム】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。
お手元に届き次第、期限までにお支払いをお願いいたします。


【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
http://www.ato-barai.com/guidance/faq.html

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿6-14-1　新宿グリーンタワー14階 
--------------------------------------------------------------
',6,'2022/01/18 13:41:49',83,'2022/05/08 13:51:31',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (561,43,'支払期限超過メール（再４）（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'再４【後払いドットコム】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。

未納分のお支払いにつき、お手元に届き次第
至急ご対応くださいますようお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
http://www.ato-barai.com/guidance/faq.html

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿6-14-1　新宿グリーンタワー14階 
--------------------------------------------------------------
',6,'2022/01/18 13:42:17',83,'2022/05/08 13:51:38',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (562,45,'支払期限超過メール（再５）（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'再５【後払いドットコム】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。

上記督促状でもお知らせしている通り、
お支払いが確認できない場合
お客様の信用取引など不利益が生じる可能性がございます。
つきましては速やかなご対応をお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
http://www.ato-barai.com/guidance/faq.html

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿6-14-1　新宿グリーンタワー14階 
--------------------------------------------------------------
',6,'2022/01/18 13:42:39',83,'2022/05/08 13:51:46',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (563,47,'支払期限超過メール（再６）（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'再６【後払いドットコム】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。

このまま未入金状態が継続されますと、
当社での対応が困難となり
通知記載の対応となる場合があります。
つきましては至急お支払いについて
ご対応くださいますようお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
http://www.ato-barai.com/guidance/faq.html

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿6-14-1　新宿グリーンタワー14階 
--------------------------------------------------------------
',6,'2022/01/18 13:43:05',83,'2022/05/08 13:51:55',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (564,49,'支払期限超過メール（再７）（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'再７【後払いドットコム】{OrderDate}{SiteNameKj}でのお買い物の件({OrderId})','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────
◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇
──────────────────────────────────

※ご入金確認に最大で4営業日お時間がかかる場合がございます。
　既にご入金のお手続きがお済のようであれば
　当メールへの返信はご不要でございます。

{CustomerNameKj}様

この度は{SiteNameKj}で商品ご購入の際に、
後払いドットコムをご利用いただきまして
まことにありがとうございます。

{ClaimDate}にお送りした請求書のお支払い期限を過ぎても
ご入金の確認が取れておりませんでしたので
本日、再請求書を発行・発送いたしました。

再三にわたり、ご返済に対し履行頂くようご通知いたしましたが
貴殿より誠意のあるご対応を頂いていない状況となっております。
今後につきましても、ご連絡・お支払いが確認できない場合は
弁護士への回収委任もしくは法的手続きに移行せざるをえません。
しかしながら、弊社債権管理部では
貴殿の債務履行に対し解決を図る為の相談窓口を設けており
相談による解決も可能な場合もございます。
つきましては、解決に向け至急ご連絡いただけますようお願いいたします。

【ご請求内容】
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
商品名（1品目のみ表示）：{OneOrderItem}
再請求追加手数料：{ReClaimFee}
遅延損害金：{DamageInterest}
ご請求金額：{TotalAmount}

※お支払期限を過ぎてしまいますと、
再請求手数料が加算されますので、ご注意下さい。

※下記口座へ直接お振込みいただきましてもご入金可能です。
(振込み手数料はお客様ご負担でございます)
お振込みいただく場合は、注文時のお名前と同一のお名前でお振込みください。

【銀行振込口座】
{Bk_BankName}　{Bk_BranchName}
普通口座　{Bk_AccountNumber}
{Bk_AccountHolderKn}

【郵便振替口座】
口座記号：00120‐7
口座番号：670031
カ）キャッチボール

その他、お支払に関してご不明な点は下記ＵＲＬをご確認ください。
http://www.ato-barai.com/guidance/faq.html

■商品の返品・未着など商品に関するお問い合わせは：
直接購入店にお問い合わせ下さい。
購入店舗：{SiteNameKj}　
電話：{Phone}

--------------------------------------------------------------
【後払いドットコム】

  お問合せ先：03-5332-3490
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）
  mail: customer@ato-barai.com
  
　運営会社：株式会社キャッチボール
　住所：〒160-0023 東京都新宿区西新宿6-14-1　新宿グリーンタワー14階 
--------------------------------------------------------------
',6,'2022/01/18 13:43:27',83,'2022/05/08 13:52:02',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (565,4,'請求書発行メール（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'請求書発行案内メール','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','請求書発行案内メール',6,'2022/01/18 13:32:06',83,'2022/01/18 13:32:08',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (566,121,'届いてから決済完了メール（PC）(領収書コメントあり)','{ServiceName} ','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】{PaymentMethod}決済への手続き完了のお知らせ (自動配信メール） {OrderId} ','{ServiceName}','{CustomerNameKj}様

この度は、{SiteNameKj}様で商品ご購入の際に、
{ServiceName}をご利用いただきまして
まことにありがとうございました。

{PaymentMethod}決済の手続きが完了いたしましたので
ご報告いたします。

以下が、今回ご注文の内容でございます。

【ご注文内容】
ご注文ID：{OrderId}
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
ご利用金額：{UseAmount}円

またのご利用を心よりお待ちしております。


領収書が必要な場合は下記URLよりご確認をお願いいたします。
・注文情報確認ページ　{OrderPageUrl}
　※ログインにはご注文時のお電話番号と、
　　請求書に記載されているパスワードをご利用ください。


■商品の返品・未着など商品については
直接ご購入店様にお問い合わせください。
ご購入店様：{SiteNameKj}
電話：{Phone}
URL：{SiteUrl}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
営業時間：9:00～18:00　年中無休（年末・年始のぞく）
mail：{ServiceMail}
運営会社：株式会社キャッチボール
住所：〒140-0002
　　　東京都品川区東品川2-2-24天王洲セントラルタワー12F
-----------------------------------------------------------
',null,'2022/03/06 4:47:05',1,'2023/01/04 13:26:36',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (567,122,'届いてから決済完了メール（CEL）(領収書コメントあり)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】{PaymentMethod}決済への手続き完了のお知らせ (自動配信メール） {OrderId} ','{ServiceName}','{CustomerNameKj}様

この度は、{SiteNameKj}様で商品ご購入の際に、
{ServiceName}をご利用いただきまして
まことにありがとうございました。

{PaymentMethod}決済の手続きが完了いたしましたので
ご報告いたします。

以下が、今回ご注文の内容でございます。

【ご注文内容】
ご注文ID：{OrderId}
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
ご利用金額：{UseAmount}円

またのご利用を心よりお待ちしております。


領収書が必要な場合は下記URLよりご確認をお願いいたします。
・注文情報確認ページ　{OrderPageUrl}
　※ログインにはご注文時のお電話番号と、
　　請求書に記載されているパスワードをご利用ください。


■商品の返品・未着など商品については
直接ご購入店様にお問い合わせください。
ご購入店様：{SiteNameKj}
電話：{Phone}
URL：{SiteUrl}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
営業時間：9:00～18:00　年中無休（年末・年始のぞく）
mail：{ServiceMail}
運営会社：株式会社キャッチボール
住所：〒140-0002
　　　東京都品川区東品川2-2-24天王洲セントラルタワー12F
-----------------------------------------------------------
',null,'2022/03/06 4:50:00',1,'2023/01/04 13:26:43',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (568,123,'届いてから決済完了メール（PC）(領収書コメントなし)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】{PaymentMethod}決済への手続き完了のお知らせ (自動配信メール） {OrderId} ','{ServiceName}','{CustomerNameKj}様

この度は、{SiteNameKj}様で商品ご購入の際に、
{ServiceName}をご利用いただきまして
まことにありがとうございました。

{PaymentMethod}決済の手続きが完了いたしましたので
ご報告いたします。

以下が、今回ご注文の内容でございます。

【ご注文内容】
ご注文ID：{OrderId}
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
ご利用金額：{UseAmount}円

またのご利用を心よりお待ちしております。



■商品の返品・未着など商品については
直接ご購入店様にお問い合わせください。
ご購入店様：{SiteNameKj}
電話：{Phone}
URL：{SiteUrl}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
営業時間：9:00～18:00　年中無休（年末・年始のぞく）
mail：{ServiceMail}
運営会社：株式会社キャッチボール
住所：〒140-0002
　　　東京都品川区東品川2-2-24天王洲セントラルタワー12F
-----------------------------------------------------------
',null,'2022/03/06 4:50:08',1,'2023/01/04 13:26:48',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (569,124,'届いてから決済完了メール（CEL）(領収書コメントなし)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】{PaymentMethod}決済への手続き完了のお知らせ (自動配信メール） {OrderId} ','{ServiceName}','{CustomerNameKj}様

この度は、{SiteNameKj}様で商品ご購入の際に、
{ServiceName}をご利用いただきまして
まことにありがとうございました。

{PaymentMethod}決済の手続きが完了いたしましたので
ご報告いたします。

以下が、今回ご注文の内容でございます。

【ご注文内容】
ご注文ID：{OrderId}
ご注文日：{OrderDate}
ご注文店舗：{SiteNameKj}
ご利用金額：{UseAmount}円

またのご利用を心よりお待ちしております。



■商品の返品・未着など商品については
直接ご購入店様にお問い合わせください。
ご購入店様：{SiteNameKj}
電話：{Phone}
URL：{SiteUrl}

-----------------------------------------------------------
【{ServiceName}】～最も消費者に愛される決済サービス～
お問い合わせ先　TEL：03-4326-3600　FAX：03-4326-3690
営業時間：9:00～18:00　年中無休（年末・年始のぞく）
mail：{ServiceMail}
運営会社：株式会社キャッチボール
住所：〒140-0002
　　　東京都品川区東品川2-2-24天王洲セントラルタワー12F
-----------------------------------------------------------
',null,'2022/03/06 4:50:16',1,'2023/01/04 13:26:52',21,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (570,125,'印刷パターンチェックエラー','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer2@ato-barai.com',null,null,null,'印刷パターンチェックエラー','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','印刷パターンチェックでエラーが発生しました。
サイトマスタの更新をしてください。

{body}',null,'2022/04/03 23:28:54',83,'2022/04/23 2:19:41',83,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (571,126,'事業者宛テストメール ','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'{ServiceName}　送達テストメール','{ServiceName}','本メールは送達確認用のテストメールです。

事業者：{EnterpriseNameKj} 様
ログインID：{LoginId}',null,'2022/07/06 2:16:26',1,'2022/07/06 2:16:26',1,1);
insert into coraldb_new01.`T_MailTemplate_Tmp`(`Id`,`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (572,127,'マイページ連携バッチエラー','届いてから払い','=?UTF-8?B?GyRCRk8kJCRGJCskaUonJCQbKEI=?=','todoitekara@ato-barai.com',null,null,null,'マイページ連携バッチエラー','=?UTF-8?B?5bGK44GE44Gm44GL44KJ5omV44GE?=','担当者様

マイページ → 基幹システムの連携バッチでエラーが発生しました。
基幹システムの入金情報が反映されていない可能性があります。

＜対象注文＞
{OrderId}

以上',null,'2022/07/06 2:19:30',1,'2022/07/20 18:10:48',18051,1);



-- Update data from Temporary table to main table with OemId IS NULL OR = 1
UPDATE T_MailTemplate a
LEFT JOIN T_MailTemplate_Tmp b
ON a.Id = b.Id
SET a.ClassName = b.ClassName
,a.FromTitle = b.FromTitle
,a.FromTitleMime = b.FromTitleMime
,a.FromAddress = b.FromAddress
,a.ToTitle = b.ToTitle
,a.ToTitleMime = b.ToTitleMime
,a.ToAddress = b.ToAddress
,a.Subject = b.Subject
,a.SubjectMime = b.SubjectMime
,a.Body = b.Body
,a.UpdateDate = DATE_ADD(NOW(), INTERVAL 9 HOUR)
,a.UpdateId = 1
WHERE a.OemId IS NULL OR a.OemId = 0;

-- Delete temp table
DROP TABLE `coraldb_new01`.`T_MailTemplate_Tmp`;