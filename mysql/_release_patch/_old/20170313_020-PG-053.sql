/* 注文_会計テーブルに項目追加 */
ALTER TABLE `AT_Order` 
ADD COLUMN `DefectFlg` TINYINT(4) NOT NULL DEFAULT 0 AFTER `RepayPendingFlg`,
ADD COLUMN `DefectInvisibleFlg` TINYINT(4) NOT NULL DEFAULT 0 AFTER `DefectFlg`,
ADD COLUMN `DefectNote` TEXT NULL AFTER `DefectInvisibleFlg`,
ADD COLUMN `DefectCancelPlanDate` DATETIME NULL AFTER `DefectNote`;

/* 注文_会計テーブルの追加項目にインデックス付与 */
ALTER TABLE `AT_Order` 
ADD INDEX `Idx_AT_Order02` (`DefectFlg` ASC);

/* 与信結果ログテーブルに項目追加 */
ALTER TABLE `T_CreditLog` 
ADD COLUMN `Jud_DefectOrderYN` TINYINT NULL AFTER `Incre_SnapShot`;
UPDATE T_CreditLog SET Jud_DefectOrderYN = 0;    /* 既存データは0設定 */


/* 加盟店テーブルに保留ボックスフラグを追加 */
ALTER TABLE `T_Enterprise` 
ADD COLUMN `HoldBoxFlg` TINYINT(4) NOT NULL DEFAULT 1 AFTER `NgAccessReferenceDate`;

/* システム条件 */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'DefectCancelPlanDays', '2', '不備注文キャンセル予定日数', NOW(), 9, NOW(), 9, '1');


/* 審査システム回答条件 */
INSERT INTO M_CodeManagement VALUES(189 ,'審査システム回答条件' ,NULL ,'キー値' ,1 ,'保留ボックス判定区分' ,0,NULL,0,NULL, NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(189,11 ,'ブラック：住所','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,12 ,'ブラック：氏名','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,13 ,'ブラック：電話番号','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,14 ,'ブラック：メールアドレス','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,101 ,'住所：センター・営業所留め','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,102 ,'住所：団地','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,103 ,'住所：ホテル','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,104 ,'住所：荘','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,105 ,'住所：様方','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,106 ,'住所：番地なし','1' ,NULL , NULL ,'住所・番地をご確認ください。' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,107 ,'住所：部屋番号なし','1' ,NULL , NULL ,'住所・部屋番をご確認ください。' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,108 ,'住所：解析不能住所','1' ,NULL , NULL ,'住所全体をご確認ください。' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,109 ,'住所：詐欺・クレーマー','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,111 ,'住所：仮オフィス','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,114 ,'住所：私書箱','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,115 ,'住所：コンビニ','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,116 ,'住所：郵便番号不一致','1' ,NULL , NULL ,'郵便番号をご確認ください。' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,117 ,'住所：公共施設','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,118 ,'住所：最終文字不正','1' ,NULL , NULL ,'住所をご確認ください。' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,201 ,'氏名：ひらがな','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,202 ,'氏名：カタカナ','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,203 ,'氏名：センター・営業所留め','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,204 ,'氏名：合同・合資会社','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,205 ,'氏名：外人','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,206 ,'氏名：ローマ字','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,207 ,'氏名：詐欺・クレーマー','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,208 ,'氏名：珍しい名字','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,301 ,'電話：050/070','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,302 ,'電話：自動アナウンス','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,303 ,'電話：詐欺・クレーマー','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,304 ,'電話：住所-市外局番不一致','1' ,NULL , NULL ,'電話番号をご確認ください。' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,305 ,'電話：仮オフィス','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,401 ,'ﾒｰﾙｱﾄﾞﾚｽ：出会い系','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,402 ,'ﾒｰﾙｱﾄﾞﾚｽ：ヤフオク悪ユーザー','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,403 ,'ﾒｰﾙｱﾄﾞﾚｽ：捨てアド','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,404 ,'ﾒｰﾙｱﾄﾞﾚｽ：連続４文字数字','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,405 ,'ﾒｰﾙｱﾄﾞﾚｽ：顔文字含','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,406 ,'ﾒｰﾙｱﾄﾞﾚｽ：詐欺・クレーマー','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,407 ,'ﾒｰﾙｱﾄﾞﾚｽ：仮オフィス','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,501 ,'注文：連続注文','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,502 ,'注文：数量オーバー','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,503 ,'注文：商品代金無し','1' ,NULL , NULL ,'商品代金をご確認ください。' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,504 ,'注文：未払い債権額','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,551 ,'注文：要注意商品01','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,552 ,'注文：要注意商品02','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,553 ,'注文：要注意商品03','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,554 ,'注文：要注意商品04','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,555 ,'注文：要注意商品05','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,556 ,'注文：要注意商品06','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,557 ,'注文：要注意商品07','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,558 ,'注文：要注意商品08','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,559 ,'注文：要注意商品09','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,560 ,'注文：要注意商品10','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,601 ,'事業者：補正','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,602 ,'事業者：購入額オーバー','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,1001 ,'イベント：バレンタインデー','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,1002 ,'イベント：ホワイトデー','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,1003 ,'イベント：母の日','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,1004 ,'イベント：父の日','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,1005 ,'イベント：敬老の日','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);

/* 保留理由 */
UPDATE `M_Code` SET `Note`='ご住所の番地をご確認ください。' WHERE `CodeId`='92' and`KeyCode`='1';
UPDATE `M_Code` SET `Note`='ご住所のお部屋番号をご確認ください。' WHERE `CodeId`='92' and`KeyCode`='2';
UPDATE `M_Code` SET `Note`='ご登録のお電話番号をご確認ください。' WHERE `CodeId`='92' and`KeyCode`='3';
UPDATE `M_Code` SET `Note`='お電話番号の桁数が多いようでございます。' WHERE `CodeId`='92' and`KeyCode`='4';
UPDATE `M_Code` SET `Note`='お電話番号の桁数が少ないようでございます。' WHERE `CodeId`='92' and`KeyCode`='5';
UPDATE `M_Code` SET `Note`='ご登録のご住所に不備がございます。' WHERE `CodeId`='92' and`KeyCode`='6';
UPDATE `M_Code` SET `Note`='ご住所の番地とお電話番号をご確認ください。' WHERE `CodeId`='92' and`KeyCode`='7';
UPDATE `M_Code` SET `Note`='ご確認させていただきたいことがございます。' WHERE `CodeId`='92' and`KeyCode`='8';


/* メニューマスタの調整 */
INSERT INTO T_Menu VALUES (29, 'member', 'submenus', 'header_menu_1', 5, 'order/defectlist', '保留注文リスト', '保留注文リスト', '保留中の注文を一覧表示', '', '', NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (29, 301, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (29, 302, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (29, 399, NOW(), 9, NOW(), 9, 1);


/* メールテンプレートの調整 */
UPDATE T_MailTemplate
SET Body = '{EnterpriseNameKj}様\r\n\r\nいつも大変お世話になっております。\r\n後払いドットコムカスタマーセンターでございます。\r\n\r\n本日与信をいただきました以下の登録において、\r\n入力情報に不備がある可能性を検知しました。\r\n\r\n\r\n{OrderId}   {CustomerNameKj}様   保留理由： {PendingReason}\r\n\r\n\r\n{PendingDate}まで与信保留とさせていただきますので\r\nお手数ではございますが、正しい情報をご確認いただき\r\n管理サイト上でご変更の処理をいただくか\r\n弊社までご連絡をいただきますようお願いいたします。\r\n\r\n※※※  重要   ※※※ \r\n再度、与信を実施した結果が「与信NG」となる場合もございます。  修正、再登録後は、\r\n必ず「 【後払い.com】与信完了のお知らせ」 メールをご確認くださいますよう、お願いを申し上げます。\r\n※ 修正・再登録を実施されない場合は、{PendingDate}以降に対象の注文は自動的にキャンセルされます。\r\n\r\n■■■■■■■■■■■　ご注文修正をされる際の注意　■■■■■■■■■■■\r\n\r\n保留注文の修正は、管理サイトの「注文登録」メニューから「保留注文リスト」\r\nオプションを選択後に表示される画面にて操作を実施してください。\r\n\r\n修正内容をご入力いただいた後、「この内容で登録」をクリックすると\r\n内容の確認画面に遷移します。内容をご確認のうえ、もう一度\r\n「この内容で登録」をクリックすると修正が完了となります。\r\n（※確認画面から別のページに移ってしまったり\r\n閉じてしまったりすると、修正が反映されません。）\r\n\r\n■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■\r\n\r\n【管理画面ＵＲＬ】\r\nhttps://www.atobarai.jp/member/\r\n\r\nご不明な点などございましたら、お気軽にお問い合わせくださいませ。\r\n\r\n何卒よろしくお願いいたします。\r\n\r\n--------------------------------------------------------------\r\n後払い請求代行サービス【後払いドットコム】\r\n\r\n  お問合せ先：0120-667-690\r\n  営業時間：9:00～18:00　年中無休（年末・年始のぞく）\r\n  mail: customer@ato-barai.com\r\n\r\n  運営会社：株式会社キャッチボール\r\n　住所：〒160-0023 東京都新宿区西新宿6-14-1 新宿グリーンタワービル14F\r\n--------------------------------------------------------------\r\n'
WHERE Class = 61 AND OemId = 0
;

