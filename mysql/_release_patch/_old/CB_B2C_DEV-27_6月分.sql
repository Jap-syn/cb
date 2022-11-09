/*cbadminのデータベース*/

-- 105 --
UPDATE M_Code SET Class1 = '?spapp',Class2 = '?spapp2' WHERE CodeId = 105 AND KeyCode = 0;
UPDATE M_Code SET Class1 = '?spapp',Class2 = '?spapp2' WHERE CodeId = 105 AND KeyCode = 1;
UPDATE M_Code SET Class1 = '?spapp',Class2 = '?spapp2' WHERE CodeId = 105 AND KeyCode = 2;
UPDATE M_Code SET Class1 = '?spapp',Class2 = '?spapp2' WHERE CodeId = 105 AND KeyCode = 3;
UPDATE M_Code SET Class1 = '?spapp',Class2 = '?spapp2' WHERE CodeId = 105 AND KeyCode = 4;
UPDATE M_Code SET Class1 = '?spapp',Class2 = '?spapp2' WHERE CodeId = 105 AND KeyCode = 5;
UPDATE M_Code SET Class1 = '?spapp',Class2 = '?spapp2' WHERE CodeId = 105 AND KeyCode = 6;
-- 160 --
UPDATE M_Code SET Class4 = 1 WHERE CodeId = 160 AND KeyCode = 0;
UPDATE M_Code SET Class4 = 0 WHERE CodeId = 160 AND KeyCode = 1;
UPDATE M_Code SET Class4 = 0 WHERE CodeId = 160 AND KeyCode = 2;
UPDATE M_Code SET Class4 = 0 WHERE CodeId = 160 AND KeyCode = 3;
UPDATE M_Code SET Class4 = 0 WHERE CodeId = 160 AND KeyCode = 4;
UPDATE M_Code SET Class4 = 0 WHERE CodeId = 160 AND KeyCode = 5;
UPDATE M_Code SET Class4 = 0 WHERE CodeId = 160 AND KeyCode = 6;
-- 72 --
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '391', '購入者名', '{CustomerNameKj}', '104', '105', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '392', 'サイト名', '{SiteNameKj}', '104', '105', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '393', '注文ID', '{OrderId}', '104', '105', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '394', '注文日', '{OrderDate}', '104', '105', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '395', '利用額', '{UseAmount}', '104', '105', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '396', '決済手数料', '{SettlementFee}', '104', '105', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '397', '送料', '{DeliveryFee}', '104', '105', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '398', '支払期限', '{CreditLimitDate}', '104', '105', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '399', '注文ﾏｲﾍﾟｰｼﾞURL', '{OrderPageAccessUrl}', '104', '105', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '400', '事業者電話番号', '{Phone}', '104', '105', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '401', '利用可能な支払方法', '{PaymentMethod}', '104', '105', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '402', '購入者名', '{CustomerNameKj}', '108', '109', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '403', 'サイト名', '{SiteNameKj}', '108', '109', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '404', '注文ID', '{OrderId}', '108', '109', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '405', '注文日', '{OrderDate}', '108', '109', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '406', '利用額', '{UseAmount}', '108', '109', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '407', '決済手数料', '{SettlementFee}', '108', '109', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '408', '送料', '{DeliveryFee}', '108', '109', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '409', '支払期限', '{CreditLimitDate}', '108', '109', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '410', '注文ﾏｲﾍﾟｰｼﾞURL', '{OrderPageAccessUrl}', '108', '109', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '411', '事業者電話番号', '{Phone}', '108', '109', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '412', '利用可能な支払方法', '{PaymentMethod}', '108', '109', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '413', '支払方法', '{PaymentMethod}', '121', '122', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '414', 'サイト名', '{SiteNameKj}', '121', '122', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '415', '注文日', '{OrderDate}', '121', '122', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '416', '商品名', '{OrderItems}', '121', '122', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '417', '利用額', '{UseAmount}', '121', '122', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '418', '注文ﾏｲﾍﾟｰｼﾞURL', '{OrderPageUrl}', '121', '122', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '419', '事業者電話番号', '{Phone}', '121', '122', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '420', 'サイトURL', '{SiteUrl}', '121', '122', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '421', '支払方法', '{PaymentMethod}', '123', '124', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '422', 'サイト名', '{SiteNameKj}', '123', '124', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '423', '注文日', '{OrderDate}', '123', '124', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '424', '商品名', '{OrderItems}', '123', '124', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '425', '利用額', '{UseAmount}', '123', '124', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '426', '事業者電話番号', '{Phone}', '123', '124', '1', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('72', '427', 'サイトURL', '{SiteUrl}', '123', '124', '1', now(), '1', now(), '1', '1');
-- 163 --
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('163', '15', 'PayPay （オンライン決済）', 'PayPay （オンライン決済）', '', '0', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('163', '16', 'LINE Pay', 'LINE Pay', '', '0', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('163', '17', 'ソフトバンクまとめて支払い', 'ソフトバンクまとめて支払い', '', '0', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('163', '18', 'ドコモ払い', 'ドコモ払い', '', '0', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('163', '19', 'auかんたん決済', 'auかんたん決済', '', '0', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('163', '20', '楽天ペイ（オンライン決済）', '楽天ペイ（オンライン決済）', '0', now(), '1', now(), '1', '1');

-- 198 --
UPDATE M_Code
    SET KeyContent = 'LINE Pay請求書払い'
    WHERE CodeId = 198 AND KeyCode = 4;
UPDATE M_Code
    SET Class1 = 'credit'
    WHERE CodeId = 198 AND KeyCode = 5;
UPDATE M_Code
    SET KeyContent = 'PayPay請求書払い'
    WHERE CodeId = 198 AND KeyCode = 6;

INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (198, 15, 'PayPay （オンライン決済）', 'paypay', NULL, NULL, NULL, '', 0, now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (198, 16, 'LINE Pay', 'linepay', NULL, NULL, NULL, '', 0, now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (198, 17, 'ソフトバンクまとめて支払い', 'softbank2', NULL, NULL, NULL, '', 0, now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (198, 18, 'ドコモ払い', 'docomo',  NULL, NULL, NULL, '', 0, now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (198, 19, 'auかんたん決済', 'auone', NULL, NULL, NULL, '', 0, now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (198, 20, '楽天ペイ（オンライン決済）', 'rakuten', NULL, NULL, NULL, '', 0, now(), 1, now(), 1, 1);
-- 201 --
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','287','与信結果が存在しないため、売上処理を中止しました。','20','405',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','288','与信取消済みのため、売上処理を中止しました。','21','405',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','289','指定された金額が、与信時金額と違うため、売上処理を中止しました。','22','405',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','290','売上処理済みのため、売上処理を中止しました。','23','405',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','291','自動売上が設定されているため、売上処理は不要です。','24','405',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','292','与信結果が存在しないため、取消返金処理を中止しました。','25','405',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','293','与信取消済みのため、取消返金処理を中止しました。','26','405',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','294','返金処理済みのため、返金処理を中止しました。','30','405',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','295','取消返金可能期間を過ぎています。','31','405',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','296','売上確定可能期間を過ぎています。','33','405',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','297','ソフトバンクまとめて支払い B 決済センターにてエラーが発生したため、処理を中止しま した。','34','405',NULL,NULL,'不当な売上・取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','298','既に売上処理中のため、売上処理を中止しました。','36','405',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','299','既に売上処理中のため、取消処理を中止しました。','37','405',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','300','既に返金処理中のため、返金処理を中止しました。','38','405',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','301','自動売上の場合、返金処理を使用して下さい。','40','405',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','302','初回申込結果が存在しないため、定期購入処理を中止しました。','L0','405',NULL,NULL,'不当な定期購入要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','303','初回申込結果と加盟店顧客 ID が異なるため、定期購入処理を中止しました。','L1','405',NULL,NULL,'不当な定期購入要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','304','既に解約済みのため、定期購入処理を中止しました。','L2','405',NULL,NULL,'不当な定期購入要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','305','ソフトバンクまとめて支払い B 決済センターによりご指定の定期購入は取扱不可と判 定されました。','L3','405',NULL,NULL,'不当な定期購入要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','306','ソフトバンクまとめて支払い B 決済センターにて該当顧客の決済がエラーと判定されま した。','L4','405',NULL,NULL,'不当な定期購入要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','252','与信結果が存在しないため、売上処理を中止しました。','20','402',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','253','与信取消済みのため、売上処理を中止しました。','21','402',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','254','指定された金額が、与信時金額を越えているため、売上処理を中止しました。','22','402',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','255','売上処理が完了済みのため、処理を中止しました。','23','402',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','256','自動売上が設定されているため、売上要求は不要です。','24','402',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','257','与信結果が存在しないため、取消返金処理を中止しました。','25','402',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','258','与信取消済みのため、取消返金処理を中止しました。','26','402',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','259','指定された月の売上データが存在しないため、返金処理を中止しました。','27','402',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','260','部分返金処理は、売上確定日の翌月から有効です。','28','402',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','261','au かんたん決済センターにて請求処理が未実施のため、返金処理を中止しました。','29','402',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','262','返金処理済みのため、返金処理を中止しました。','30','402',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','263','返金処理は、売上確定日から翌々月末日まで有効です。','31','402',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','264','ご指定の継続課金は既に解約済みです。','32','402',NULL,NULL,'継続課金停止に限り発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','265','売上確定可能期間を過ぎています。','33','402',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','266','その他エラー','34','402',NULL,NULL,'決済会社から返却されるエラー','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','267','既に売上処理中のため、売上処理を中止しました。','36','402',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','268','既に売上処理中のため、取消処理を中止しました。','37','402',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','269','既に返金処理中のため、返金処理を中止しました。','38','402',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','270','自動売上の場合、返金処理を使用して下さい。','40','402',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','271','返金処理は、本決済では使用できません。','41','402',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','272','指定された金額が、与信時金額と違うため、売上処理を中止しました。','42','402',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','273','ご指定の決済は、お客様のご都合により与信取消されたため、売上処理を中止しま した。','43','402',NULL,NULL,'売上時に既にユーザが無効状態となっている場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','274','指定された金額が、売上時金額を越えているため、返金処理を中止しました。','75','402',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','275','初回申込結果が存在しないため、継続課金処理を中止しました。','L0','402',NULL,NULL,'不当な与信要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','276','初回申込結果と加盟店顧客 ID が異なるため、継続課金処理を中止しました。','L1','402',NULL,NULL,'不当な与信要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','277','既に解約済みのため、継続課金処理を中止しました。','L2','402',NULL,NULL,'不当な与信要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','278','ご指定の継続課金はご都合により利用できません。継続課金契約を解約しました。','L3','402',NULL,NULL,'与信時に既にユーザが無効状態となっている場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','279','ご利用可能額を超えているか、au かんたん決済センターでエラーが検知されました｡し ばらく経ってから操作して下さい｡','L4','402',NULL,NULL,'与信時に与信額オーバーなどのエラーとなった場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','280','初回申込結果が存在しないため、解約処理を中止しました。','L5','402',NULL,NULL,'不当な継続課金解約要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','281','初回申込結果と加盟店顧客 ID が異なるため、解約処理を中止しました。','L6','402',NULL,NULL,'不当な継続課金解約要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','282','既に解約済みのため、解約処理を中止しました。','L7','402',NULL,NULL,'不当な継続課金解約要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','283','有効なユーザでありません。','U0','402',NULL,NULL,'決済会社から返却されるエラー','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','284','ユーザ様事由によるエラーです。','U1','402',NULL,NULL,'決済会社から返却されるエラー','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','285','お客様の契約ISP(プロバイダ)事由によるエラーです。','U2','402',NULL,NULL,'決済会社から返却されるエラー','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','286','ユーザ設定により、定期購入の申込ができません。','U3','402',NULL,NULL,'決済会社から返却されるエラー','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','222','与信結果が存在しないため、売上処理を中止しました。','20','401',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','223','与信取消済みのため、売上処理を中止しました。','21','401',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','224','指定された金額が、与信時金額を越えているため、売上処理を中止しました。','22','401',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','225','売上処理が完了済みのため、処理を中止しました。','23','401',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','226','自動売上が設定されているため、売上要求は不要です。','24','401',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','227','与信結果が存在しないため、取消返金処理を中止しました。','25','401',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','228','与信取消済みのため、取消返金処理を中止しました。','26','401',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','229','指定された月の売上データが存在しないため、返金処理を中止しました。','27','401',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','230','使用していないエラーコード','28','401',NULL,NULL,'','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','231','使用していないエラーコード','29','401',NULL,NULL,'','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','232','返金処理済みのため、返金処理を中止しました。','30','401',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','233','取消返金可能期間を過ぎています。','31','401',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','234','ご指定の継続課金は既に解約済みです。','32','401',NULL,NULL,'継続課金停止に限り発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','235','売上確定可能期間を過ぎています。','33','401',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','236','その他エラー','34','401',NULL,NULL,'決済会社から返却されるエラー','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','237','決済会社夜間処理中、または、定期メンテナンス中です。','35','401',NULL,NULL,'決済会社から返却されるエラー','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','238','既に売上処理中のため、売上処理を中止しました。','36','401',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','239','既に売上処理中のため、取消処理を中止しました。','37','401',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','240','既に返金処理中のため、返金処理を中止しました。','38','401',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','241','決済会社側売上処理中のため、返金処理を中止しました。','39','401',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','242','自動売上の場合、返金処理を使用して下さい。','40','401',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','243','指定された金額が、売上時金額を超えるため、返金処理を中止しました。','44','401',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','244','初回申込結果が存在しないため、継続課金処理を中止しました。','L0','401',NULL,NULL,'不当な与信要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','245','初回申込結果と加盟店顧客 ID が異なるため、継続課金処理を中止しました。','L1','401',NULL,NULL,'不当な与信要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','246','既に解約済みのため、継続課金処理を中止しました。','L2','401',NULL,NULL,'不当な与信要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','247','ご指定の継続課金はご都合により利用できません。継続課金契約を解約しました。','L3','401',NULL,NULL,'与信時に既にユーザが無効状態となっている場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','248','ご利用可能額を超えているか、ドコモ払い決済センターでエラーが検知されました｡し ばらく経ってから操作して下さい｡','L4','401',NULL,NULL,'与信時に与信額オーバーなどのエラーとなった場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','249','初回申込結果が存在しないため、解約処理を中止しました。','L5','401',NULL,NULL,'不当な継続課金解約要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','250','初回申込結果と加盟店顧客 ID が異なるため、解約処理を中止しました。','L6','401',NULL,NULL,'不当な継続課金解約要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','251','既に解約済みのため、解約処理を中止しました。','L7','401',NULL,NULL,'不当な継続課金解約要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','185','PayPay（オンライン決済）はメンテナンスの為、只今、ご利用できません。','20','311',NULL,NULL,'決済会社から返却されるエラー','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','186','PayPay（オンライン決済）にてエラーが発生しました為、処理を中止しました。','21','311',NULL,NULL,'決済会社から返却されるエラー','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','187','既に取消処理中のため、取消返金処理を中止しました。','22','311',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','188','既に売上処理中のため、取消返金処理を中止しました。','23','311',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','189','既に売上依頼中のため、取消返金処理を中止しました。','24','311',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','190','売上可能期間を超過したため、売上処理を中止しました。','25','311',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','191','ご指定の継続課金は既に解約済みです。','26','311',NULL,NULL,'継続課金停止に限り発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','192','売上可能期間を超過したため、売上処理を中止しました。','27','311',NULL,NULL,'不当な売上要求をした場合に発生 ※与信有効期限 6 時間以内の増額売上要求にて発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','193','残高が不足しています。','28','311',NULL,NULL,'PayPay 残高が要求金額より不足している場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','194','返金金額の合計が、売上時金額を超えているため、返金処理を中止しました。','29','311',NULL,NULL,'不当な複数回部分返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','195','PayPay 決済にて返金処理中のため、返金処理を中止しました。','30','311',NULL,NULL,'不当な複数回部分返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','196','返金最大回数に達したため、返金処理を中止しました。','31','311',NULL,NULL,'不当な複数回部分返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','197','ユーザが設定している 1 日あたりの利用可能額設定の上限を超えているため、処理 できません。','32','311',NULL,NULL,'決済会社から返却されるエラー','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','198','ユーザが設定している1 ヶ月あたりの利用可能額設定の上限を超えているため、処理 できません。','33','311',NULL,NULL,'決済会社から返却されるエラー','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','199','その他上限を超えているため、処理できません。 例：加盟店の月上限を超えた','34','311',NULL,NULL,'決済会社から返却されるエラー','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','200','与信結果が存在しないため、売上処理を中止しました。','47','311',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','201','売上処理済みのため、売上処理を中止しました。','48','311',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','202','取消処理済みのため、売上処理を中止しました。','49','311',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','203','既に取消処理中のため、売上処理を中止しました。','50','311',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','204','既に売上処理中のため、売上処理を中止しました。','52','311',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','205','既に売上依頼中のため、売上処理を中止しました。','53','311',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','206','与信結果が存在しないため、返金処理を中止しました。','68','311',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','207','売上処理未実施のため、返金処理を中止しました。','69','311',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','208','取消処理済みのため、返金処理を中止しました。','70','311',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','209','返金処理済みのため、返金処理を中止しました。','71','311',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','210','既に返金処理中のため、返金処理を中止しました。','72','311',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','211','指定された金額が、売上時金額を越えているため、返金処理を中止しました。','75','311',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','212','与信結果が存在しないため、取消返金処理を中止しました。','76','311',NULL,NULL,'不当な取消・返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','213','初回申込結果が存在しないため、定期購入処理を中止しました。','L0','311',NULL,NULL,'不当な決済要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','214','初回申込結果と加盟店顧客 ID が異なるため、定期購入処理を中止しました。','L1','311',NULL,NULL,'不当な決済要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','215','既に解約済みのため、定期購入処理を中止しました。','L2','311',NULL,NULL,'不当な決済要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','216','ご指定の従量課金はご都合により利用できません。従量課金契約を解約しました。','L3','311',NULL,NULL,'決済時に既にユーザが無効状態となっている場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','217','初回申込結果が存在しないため、解約処理を中止しました。','L5','311',NULL,NULL,'不当な継続課金解約要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','218','初回申込結果と加盟店顧客 ID が異なるため、解約処理を中止しました。','L6','311',NULL,NULL,'不当な継続課金解約要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','219','既に解約済みのため、解約処理を中止しました。','L7','311',NULL,NULL,'不当な継続課金解約要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','220','初回申込結果とサービスタイプが異なるため、定期購入処理を中止しました。','L8','311',NULL,NULL,'不当な決済要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','221','処理中のため、取消処理を中止しました。','L9','311',NULL,NULL,'プッシュ課金（支払リクエスト）において、期限切れまたは売上済みの場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','163','LINE Pay にてエラーが発生したため、処理を中止しました。','20','310',NULL,NULL,'決済会社から返却されるエラー','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','164','一時的エラーです。暫く待って再度お手続きをお願いします。','21','310',NULL,NULL,'決済会社から返却されるエラー','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','165','返金可能期限を超過したため返金処理を中止しました。','22','310',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','166','取消可能期限を超過したため取消処理を中止しました。','23','310',NULL,NULL,'不当な取消要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','167','売上可能期限を超過したため売上処理を中止しました。','24','310',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','168','自動売上が設定されているため、売上処理は不要です。','46','310',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','169','与信結果が存在しないため、売上処理を中止しました。','47','310',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','170','売上処理済みのため、売上処理を中止しました。','48','310',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','171','取消処理済みのため、売上処理を中止しました。','49','310',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','172','既に取消処理中のため、売上処理を中止しました。','50','310',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','173','既に取消依頼中のため、売上処理を中止しました。','51','310',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','174','既に売上処理中のため、売上処理を中止しました。','52','310',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','175','指定された金額が、与信時金額を越えているため、売上処理を中止しました。','55','310',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','176','売上処理済みのため、取消処理を中止しました。','59','310',NULL,NULL,'不当な取消要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','177','取消処理済みのため、取消処理を中止しました。','60','310',NULL,NULL,'不当な取消要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','178','既に取消処理中のため、取消処理を中止しました。','61','310',NULL,NULL,'不当な取消要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','179','既に売上処理中のため、取消処理を中止しました。','63','310',NULL,NULL,'不当な取消要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','180','返金処理済みのため、返金処理を中止しました。','71','310',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','181','既に返金処理中のため、返金処理を中止しました。','72','310',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','182','既に返金依頼中のため、返金処理を中止しました。','73','310',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','183','指定された金額が、売上時金額を越えているため、返金処理を中止しました。','75','310',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','184','与信結果が存在しないため、取消返金処理を中止しました。','76','310',NULL,NULL,'不当な取消返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','105','サーバメンテナンス中エラー','20','305',NULL,NULL,'決済会社から返却されるエラー','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','106','楽天センターエラー','21','305',NULL,NULL,'決済会社から返却されるエラー','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','107','楽天センターエラー','22','305',NULL,NULL,'決済会社から返却されるエラー','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','108','楽天センターエラー','23','305',NULL,NULL,'決済会社から返却されるエラー','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','109','楽天センターエラー','24','305',NULL,NULL,'決済会社から返却されるエラー','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','110','使用していないエラーコード','25','305',NULL,NULL,'','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','111','使用していないエラーコード','26','305',NULL,NULL,'','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','112','返金可能期間を超過したため返金処理を中止しました。','27','305',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','113','取消可能期間を超過したため取消処理を中止しました。','28','305',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','114','金額変更処理は、本決済では使用できません。','29','305',NULL,NULL,'不当な金額変更要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','115','与信結果が存在しないため、金額変更処理を中止しました。','30','305',NULL,NULL,'不当な金額変更要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','116','取消処理済みのため、金額変更処理を中止しました。','31','305',NULL,NULL,'不当な金額変更要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','117','既に取消処理中のため、金額変更処理を中止しました。','32','305',NULL,NULL,'不当な金額変更要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','118','既に取消依頼中のため、金額変更処理を中止しました。','33','305',NULL,NULL,'不当な金額変更要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','119','既に売上処理中のため、金額変更処理を中止しました。','34','305',NULL,NULL,'不当な金額変更要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','120','既に売上依頼中のため、金額変更処理を中止しました。','35','305',NULL,NULL,'不当な金額変更要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','121','既に金額変更処理中のため、金額変更処理を中止しました。','36','305',NULL,NULL,'不当な金額変更要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','122','既に金額変更依頼中のため、金額変更処理を中止しました。','37','305',NULL,NULL,'不当な金額変更要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','123','既に金額変更処理中のため売上処理を中止しました。','38','305',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','124','既に金額変更処理中のため取消処理を中止しました。','39','305',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','125','返金処理済みのため、金額変更処理を中止しました。','40','305',NULL,NULL,'不当な金額変更要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','126','既に返金処理中のため、金額変更処理を中止しました。','41','305',NULL,NULL,'不当な金額変更要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','127','既に返金依頼中のため、金額変更処理を中止しました。','42','305',NULL,NULL,'不当な金額変更要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','128','既に金額変更処理中のため返金処理を中止しました。','43','305',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','129','与信金額が 0 円のため、売上処理を中止しました。','44','305',NULL,NULL,'不当な金額変更要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','130','売上処理は、本決済では使用できません。','45','305',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','131','自動売上が設定されているため、売上処理は不要です。','46','305',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','132','与信結果が存在しないため、売上処理を中止しました。','47','305',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','133','売上処理済みのため、売上処理を中止しました。','48','305',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','134','取消処理済みのため、売上処理を中止しました。','49','305',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','135','既に取消処理中のため、売上処理を中止しました。','50','305',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','136','既に取消依頼中のため、売上処理を中止しました。','51','305',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','137','既に売上処理中のため、売上処理を中止しました。','52','305',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','138','既に売上依頼中のため、売上処理を中止しました。','53','305',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','139','既に金額変更依頼中のため売上処理を中止しました。','55','305',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','140','既に金額変更依頼中のため取消処理を中止しました。','56','305',NULL,NULL,'不当な取消要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','141','自動売上の場合、返金処理を使用して下さい。','57','305',NULL,NULL,'不当な取消要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','142','取消処理済みのため、取消処理を中止しました。','60','305',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','143','既に取消処理中のため、取消処理を中止しました。','61','305',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','144','既に取消依頼中のため、取消処理を中止しました。','62','305',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','145','既に売上処理中のため、取消処理を中止しました。','63','305',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','146','既に売上依頼中のため、取消処理を中止しました。','64','305',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','147','既に金額変更依頼中のため返金処理を中止しました。','66','305',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','148','返金処理は、本決済では使用できません。','67','305',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','149','取消処理済みのため、返金処理を中止しました。','70','305',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','150','返金処理済みのため、返金処理を中止しました。','71','305',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','151','既に返金処理中のため、返金処理を中止しました。','72','305',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','152','既に返金依頼中のため、返金処理を中止しました。','73','305',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','153','与信結果が存在しないため、取消返金処理を中止しました。','76','305',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','154','楽天ペイ（オンライン決済）は、100 円以上のお支払いからご利用になれます。','77','305',NULL,NULL,'決済が許容する最低限の額を与信額が下回る場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','155','初回申込結果が存在しないため、継続課金処理を中止しました。','L0','305',NULL,NULL,'不当な与信要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','156','初回申込結果と加盟店顧客 ID が異なるため、継続課金処理を中止しました。','L1','305',NULL,NULL,'不当な与信要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','157','既に解約済みのため、継続課金処理を中止しました。','L2','305',NULL,NULL,'不当な与信要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','158','ご指定の継続課金はご都合により利用できません。継続課金契約を解約しました。','L3','305',NULL,NULL,'与信時に既にユーザが無効状態となっている場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','159','ご利用可能額を超えているか、楽天決済センターでエラーが検知されました｡しばらく 経ってから操作して下さい｡','L4','305',NULL,NULL,'与信時に与信額オーバーなどのエラーとなった場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','160','初回申込結果が存在しないため、解約処理を中止しました。','L5','305',NULL,NULL,'不当な継続課金解約要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','161','初回申込結果と加盟店顧客 ID が異なるため、解約処理を中止しました。','L6','305',NULL,NULL,'不当な継続課金解約要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','162','既に解約済みのため、解約処理を中止しました。','L7','305',NULL,NULL,'不当な継続課金解約要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','98','既に取消処理中のため、売上処理を中止しました。','W0','101',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','99','処理日時は、与信日以降の日付を指定してください。','W1','101',NULL,NULL,'不当な売上要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','100','売上処理未実施のため、返金処理を中止しました。','W2','101',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','101','既に返金処理中のため、返金処理を中止しました。','W3','101',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','102','指定された金額が、売上時金額を越えているため、返金処理を中止しました。','W4','101',NULL,NULL,'不当な部分返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','103','処理日時は、売上日以降の日付を指定してください。','W5','101',NULL,NULL,'不当な返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('201','104','既に取消処理中のため、取消処理を中止しました。','W6','101',NULL,NULL,'不当な取消返金要求をした場合に発生','0',NOW(),'9',NOW(),'9','1');

-- 212 --
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (212, 1, 'SBPS（紹介）', NULL, NULL, NULL, NULL, NULL, 0, now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (212, 2, 'SBPS（包括）', NULL, NULL, NULL, NULL, NULL, 0, now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (212, 3, 'CB直契約', NULL, NULL, NULL, NULL, NULL, 0, now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (212, 4, 'SBPS（ライフ）', NULL, NULL, NULL, NULL, NULL, 0, now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (212, 5, '加盟店直契約', NULL, NULL, NULL, NULL, NULL, 0, now(), 1, now(), 1, 1);
-- 211 --
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '1', 'システムエラー画面', '1101', '1299', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '2', '購入 NG 画面', '1301', '1399', '', '', '結果通知エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '3', 'システムエラー画面', '2101', '3399', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '4', 'マーチャントからのリクエストパラメータが不正です。', '5101', '5101', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '5', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7000', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '6', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7190', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '7', 'ご利用出来る限度額を超えています。↓\r\n
別のカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7202', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '8', 'ご利用出来る限度額を超えています。↓\r\n
別のカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7203', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '9', 'ご利用出来る限度額を超えています。↓\r\n
別のカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7204', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '10', 'ご利用出来る限度額を超えています。↓\r\n
別のカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7205', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '11', '本カードは使用不可となっています。↓\r\n
別のカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7212', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '12', '本カードは使用不可となっています。↓\r\n
別のカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7230', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '13', '暗証番号が不正です。', '7242', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '14', 'ご利用出来る回数の限度を超えています。', '7254', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '15', 'ご利用出来る限度額を超えています。', '7255', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '16', 'お取扱出来ないクレジットカードです。↓\r\n
別のカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7256', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '17', 'お取扱出来ないクレジットカードです。↓\r\n
別のカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7260', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '18', 'お取扱出来ないクレジットカードです。↓\r\n
別のカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7261', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '19', 'クレジットカード番号または有効期限に誤りがあります。↓\r\n
お持ちのカードをご確認の上、再入力して下さい。', '7265', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '20', 'お取扱できない取引内容です。
別のカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7267', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '21', 'お取扱できない取引内容です。
別のカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7268', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '22', 'お取扱できない取引内容です。
別のカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7269', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '23', '指定されたボーナス回数ではご利用出来ません。↓\r\n
お持ちのカードをご確認の上、再入力して下さい。', '7270', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '24', '指定されたボーナス月ではご利用出来ません。↓\r\n
お持ちのカードをご確認の上、再入力して下さい。', '7271', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '25', '指定されたボーナス金額ではご利用出来ません。↓\r\n
お持ちのカードをご確認の上、再入力して下さい。', '7272', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '26', '指定された支払開始月ではご利用出来ません。↓\r\n
お持ちのカードをご確認の上、再入力して下さい。', '7273', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '27', '指定された分割回数ではご利用出来ません。↓\r\n
お持ちのカードをご確認の上、再入力して下さい。', '7274', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '28', '指定された分割金額ではご利用出来ません。↓\r\n
お持ちのカードをご確認の上、再入力して下さい。', '7275', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '29', '指定された初回お支払い金額ではご利用出来ません。↓\r\n
お持ちのカードをご確認の上、再入力して下さい。', '7276', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '30', 'お取扱できない取引内容です。別のカードで再入力するか、
お持ちのカード会社へお問合せ下さい。', '7277', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '31', 'お取扱できない取引内容です。別のカードで再入力するか、
お持ちのカード会社へお問合せ下さい。', '7278', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '32', 'お取扱できない取引内容です。別のカードで再入力するか、
お持ちのカード会社へお問合せ下さい。', '7280', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '33', 'お取扱できない取引内容です。別のカードで再入力するか、
お持ちのカード会社へお問合せ下さい。', '7281', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '34', 'クレジットカード番号または有効期限に誤りがあります。↓\r\n
お持ちのカードをご確認の上、再入力して下さい。', '7283', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '35', 'お取扱できない取引内容です。
別のカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7284', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '36', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7285', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '37', 'お取扱できない取引内容です。
別のカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7292', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '38', 'お取扱できない取引内容です。
別のカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7294', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '39', 'お取扱できない取引内容です。
別のカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7295', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '40', 'お取扱できない取引内容です。
別のカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7297', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '41', 'お取扱できない取引内容です。
別のカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7298', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '42', 'お取扱できない取引内容です。
別のカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7299', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '43', 'お取扱出来ないクレジットカードです。↓\r\n別のカードで再入力して下さい。', '7103', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '44', 'お取扱出来ないクレジットカードです。↓\r\n別のカードで再入力して下さい。', '7115', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '45', 'お取扱出来ないクレジットカードです。↓\r\n別のカードで再入力して下さい。', '7220', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '46', 'お取扱出来ないクレジットカードです。↓\r\n別のカードで再入力して下さい。', '7221', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '47', 'クレジットカード番号または有効期限に誤りがあります。↓\r\n
お持ちのカードをご確認の上、再入力して下さい。', '7222', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '48', 'システムエラー画面', '7301', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '49', 'システムエラー画面', '7302', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '50', 'システムエラー画面', '7340', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '51', 'システムエラー画面', '7350', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '52', 'システムエラー画面', '7401', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '53', 'システムエラー画面', '7412', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '54', 'システムエラー画面', '7430', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '55', 'システムエラー画面', '7431', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '56', 'システムエラー画面', '7450', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '57', 'システムエラー画面', '7451', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '58', 'システムエラー画面', '7452', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '59', 'システムエラー画面', '7453', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '60', 'システムエラー画面', '7454', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '61', 'システムエラー画面', '7455', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '62', 'システムエラー画面', '7465', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '63', 'システムエラー画面', '7468', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '64', 'システムエラー画面', '7469', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '65', 'システムエラー画面', '7470', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '66', 'システムエラー画面', '7471', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '67', 'システムエラー画面', '7472', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '68', 'システムエラー画面', '7473', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '69', 'システムエラー画面', '7474', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '70', 'システムエラー画面', '7475', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '71', 'システムエラー画面', '7476', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '72', 'システムエラー画面', '7477', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '73', 'システムエラー画面', '7480', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '74', 'システムエラー画面', '7481', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '75', 'システムエラー画面', '7483', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '76', 'システムエラー画面', '7484', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '77', 'システムエラー画面', '7490', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '78', '外部接続エラーが発生しました。', '6302', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '79', '決済機関内部処理エラーが発生しました。', '7001', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '80', '※creditinfo.insert /creditinfo.update  が返却したエラー詳細', '7002', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '81', 'セキュリティコードに誤りがあります｡<BR>お持ちのカードをご確認の上､再入力して下さい｡', '7244', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '82', 'お取扱出来ないクレジットカードです｡<BR>別のカードで再入力するか､お持ちのカード会社へお問合せ下さい｡', '7245', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '83', 'お取扱出来ないクレジットカードです｡<BR>別のカードで再入力するか､お持ちのカード会社へお問合せ下さい｡', '7246', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '84', '（与信継続可能な為、メッセージを表示しない）', '7501', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '85', '（与信継続可能な為、メッセージを表示しない）', '7502', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '86', '（与信継続可能な為、メッセージを表示しない）', '7510', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '87', '（与信継続可能な為、メッセージを表示しない）', '7511', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '88', '（与信継続可能な為、メッセージを表示しない）', '7512', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '89', '（与信継続可能な為、メッセージを表示しない）', '7513', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '90', '（与信継続可能な為、メッセージを表示しない）', '7514', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '91', '（与信継続可能な為、メッセージを表示しない）', '7515', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '92', '（与信継続可能な為、メッセージを表示しない）', '7516', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '93', '（与信継続可能な為、メッセージを表示しない）', '7518', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '94', '（与信継続可能な為、メッセージを表示しない）', '7521', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '95', '（与信継続可能な為、メッセージを表示しない）', '7525', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '96', '（与信継続可能な為、メッセージを表示しない）', '7526', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '97', '（与信継続可能な為、メッセージを表示しない）', '7527', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '98', '（与信継続可能な為、メッセージを表示しない）', '7600', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '99', '（与信継続可能な為、メッセージを表示しない）', '7601', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '100', '（与信継続可能な為、メッセージを表示しない）', '7602', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '101', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7700', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '102', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7701', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '103', 'クレジットカード番号または有効期限に誤りがあります。↓\r\n
お持ちのカードをご確認の上、再入力して下さい。', '7702', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '104', 'クレジットカード番号または有効期限に誤りがあります。↓\r\n
お持ちのカードをご確認の上、再入力して下さい。', '7703', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '105', 'クレジットカード番号または有効期限に誤りがあります。↓\r\n
お持ちのカードをご確認の上、再入力して下さい。', '7704', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '106', 'クレジットカード番号または有効期限に誤りがあります。↓\r\n
お持ちのカードをご確認の上、再入力して下さい。', '7705', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '107', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7706', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '108', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7707', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '109', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7708', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '110', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7709', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '111', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7710', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '112', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7711', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '113', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7712', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '114', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7713', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '115', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7714', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '116', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7715', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '117', 'クレジットカード番号または有効期限に誤りがあります。↓\r\n
お持ちのカードをご確認の上、再入力して下さい。', '7716', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '118', '本人認証に失敗しました。↓\r\n
お持ちのカードをご確認の上、再入力して下さい。', '7717', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '119', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7718', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '120', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7719', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '121', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7720', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '122', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7721', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '123', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7722', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '124', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7723', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '125', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7724', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '126', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7725', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '127', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7726', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '128', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7727', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '129', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7799', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '130', '本人認証サービスに対応していないカードです。↓\r\n
別のカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7800', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '131', '本人認証サービスに未登録のカードです。↓\r\n
ご登録済みカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7801', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '132', '本人認証サービスに未登録のカードです。↓\r\n
ご登録済みカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7802', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '133', 'クレジットセンターでエラーが検知されました。↓\r\n
再度、または、しばらく時間をおいてから操作して下さい。', '7999', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '134', '（３D 認証は継続可能な為、メッセージを表示しない）', '7900', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '135', '（３D 認証は継続可能な為、メッセージを表示しない）', '7901', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '136', '（３D 認証は継続可能な為、メッセージを表示しない）', '7902', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '137', 'クレジットセンターでエラーが検知されました｡<BR>再度､または､しばらく時間をおいてから操作して下さい｡', '7903', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '138', '本人認証サービスに未登録のカードです。<BR>ご登録済みカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7904', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '139', '本人認証サービスに未登録のカードです。<BR>ご登録済みカードで再入力するか、お持ちのカード会社へお問合せ下さい。', '7905', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '140', '銀聯ネット決済にてエラーが発生したため、処理を中止しました。({0})', '8620', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '141', '銀聯ネット決済にてエラーが発生したため、処理を中止しました。({0})', '8621', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '142', '銀聯ネット決済はメンテナンスの為、只今、ご利用できません。', '8622', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '143', 'PayPay（オンライン決済）はメンテナンスの為、只今、ご利用できません。', '8778', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '144', 'PayPay（オンライン決済）にてエラーが発生しました為、処理を中止しました。({0})', '8779', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '145', '認証情報の妥当性の確認に失敗しました。', '8849', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '146', '認証情報の署名の確認に失敗しました。', '8850', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '147', '認証情報の有効期限が過ぎています。', '8851', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '148', '残高が不足しています。', '8852', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '149', '処理中のため、取消処理を中止しました。', '8857', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '150', 'メルペイ決済はメンテナンスの為、只今、ご利用できません。', '8840', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '151', 'メルペイ決済にてリクエスト認証が出来なかったため、処理を中止しました。', '8841', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '152', 'メルペイ決済にてエラーが発生しました為、処理を中止しました。', '8842', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '153', '指定された金額がメルペイ決済で利用可能な金額ではない為、処理を中止しました。', '8843', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '154', '指定された金額が、利用可能金額の範囲外であるため、購入要求処理を中止しました。', '8381', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '155', 'MySoftBank 決済センターにてエラーが発生しました為、処理を中止しました。', '8382', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '156', '一定時間操作されなかったため処理を中断しました。
再度MySoftbank ログインよりお手続きをお願いします。', '8383', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '157', '一定時間操作されなかったため処理を中断しました。
再度、ご購入手続きを最初からお願い致します。', '8384', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '158', '決済ができませんでした。詳細は、決済機関にお問合せください。', '8385', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '159', '決済ができませんでした。詳細は、決済機関にお問合せください。', '8386', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '160', '決済ができませんでした。詳細は、決済機関にお問合せください。', '8387', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '161', '決済ができませんでした。詳細は、決済機関にお問合せください。', '8388', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '162', '決済ができませんでした。詳細は、決済機関にお問合せください。', '8389', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '163', '決済ができませんでした。詳細は、決済機関にお問合せください。', '8390', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '164', 'ご利用出来る限度額を超えています。
他のお支払い方法にて、再度、お手続きをお願い致します。', '8391', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '165', '決済ができませんでした。詳細は、決済機関にお問合せください。', '8392', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '166', '決済ができませんでした。詳細は、決済機関にお問合せください。', '8393', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '167', '決済ができませんでした。詳細は、決済機関にお問合せください。', '8394', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '168', '決済ができませんでした。詳細は、決済機関にお問合せください。', '8395', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '169', '決済ができませんでした。詳細は、決済機関にお問合せください。', '8396', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '170', 'メンテナンス中のためソフトバンクまとめて支払いはご利用できません。
しばらく待って、再度お手続きをお願いします。', '8397', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '171', 'ソフトバンクケータイ払い決済センターにてエラーが発生したため、処理を中止しました。({0}:{1})', '8451', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '172', '売上確定可能期間を過ぎているため、売上処理を中止しました。', '8452', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '173', '取消可能期間を過ぎているため、取消処理を中止しました。', '8453', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '174', '返金可能期間を過ぎているため、返金処理を中止しました。', '8454', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '175', 'SoftBank 決済センターにてエラーが発生しました為、処理を中止しました。', '4701', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '176', 'SoftBank 決済センターにてエラーが発生しました為、処理を中止しました。', '4702', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '177', 'SoftBank 決済センターにてエラーが発生しました為、処理を中止しました。', '4703', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '178', 'SoftBank 決済センターにてエラーが発生しました為、処理を中止しました。', '4704', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '179', 'SoftBank 決済センターにてエラーが発生しました為、処理を中止しました。', '4705', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '180', 'SoftBank 決済センターにてエラーが発生しました為、処理を中止しました。', '4706', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '181', 'SoftBank 決済センターにてエラーが発生しました為、処理を中止しました。', '4707', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '182', 'SoftBank 決済センターにてエラーが発生しました為、処理を中止しました。', '4708', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '183', 'SoftBank 決済センターにてエラーが発生しました為、処理を中止しました。', '4709', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '184', 'SoftBank 決済センターにてエラーが発生しました為、処理を中止しました。', '4710', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '185', 'SoftBank 決済センターにてエラーが発生しました為、処理を中止しました。', '4711', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '186', 'SoftBank 決済センターにてエラーが発生しました為、処理を中止しました。', '4712', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '187', 'SoftBank 決済センターにてエラーが発生しました為、処理を中止しました。', '4713', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '188', 'SoftBank 決済センターにてエラーが発生しました為、処理を中止しました。', '4714', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '189', 'SoftBank 決済センターにてエラーが発生しました為、処理を中止しました。', '4715', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '190', 'SoftBank 決済センターにてエラーが発生しました為、処理を中止しました。', '4716', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '191', 'SoftBank 決済センターにてエラーが発生しました為、処理を中止しました。', '4717', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '192', '与信結果が存在しないため、売上処理を中止しました。', '8411', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '193', '与信取消済みのため、売上処理を中止しました。', '8412', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '194', '指定された金額が、与信時金額を越えているため、売上処理を中止しました。', '8413', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '195', '売上処理が完了済みのため、処理を中止しました。', '8414', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '196', '自動売上が設定されているため、売上要求は不要です。', '8415', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '197', '与信結果が存在しないため、取消返金処理を中止しました。', '8416', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '198', '与信取消済みのため、取消返金処理を中止しました。', '8417', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '199', '指定された月の売上データが存在しないため、返金処理を中止しました。', '8418', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '200', '返金処理済みのため、返金処理を中止しました。', '8421', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '201', 'ご指定の継続課金は既に解約済みです。', '8422', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '202', '決済機関夜間処理中、または、定期メンテナンス中です。', '8423', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '203', '売上確定可能期間を過ぎています。', '8424', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '204', '取消返金可能期間を過ぎています。', '8425', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '205', 'DoCoMo 決済センターにてエラーが発生しました為、処理を中止しました。', '8426', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '206', '決済機関夜間処理中、または、定期メンテナンス中です。', '8427', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '207', '既に売上処理中のため、売上処理を中止しました。', '8428', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '208', '既に売上処理中のため、取消処理を中止しました。', '8429', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '209', '既に返金処理中のため、返金処理を中止しました。', '8430', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '210', '決済機関側売上処理中のため、返金処理を中止しました。', '8471', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '211', '自動売上の場合、返金処理を使用して下さい。', '8472', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '212', '決済機関夜間処理中、または、定期メンテナンス中です。', '8473', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '213', '決済機関夜間処理中、または、定期メンテナンス中です。', '8474', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '214', 'メンテナンス中のためドコモケータイ払いはご利用できません。
しばらく待って、再度お手続きをお願いします。', '8475', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '215', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8252', '', '', '', '金額は１円以上を設定してください', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '216', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8253', '', '', '', '認証区分は 01（OpenID）を設定してください', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '217', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8254', '', '', '', '認証区分（OpenID）に該当するOpenID が
設定されていません', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '218', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8255', '', '', '', '認証区分（ワンタイムOpenID）に該当する
ワンタイム OpenID が設定されていません', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '219', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8256', '', '', '', '加盟店 ID あるいはセキュアキーに間違いがあります', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '220', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8257', '', '', '', '不正なトランザクション ID です', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '221', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8258', '', '', '', '実行許可のないトランザクションです', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '222', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8259', '', '', '', '契約が失効しています', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '223', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8260', '', '', '', '要求された機能を行う権限がありません', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '224', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8261', '', '', '', '決済PF の利用権限がありません', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '225', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8262', '', '', '', 'サービスが利用できない状態です', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '226', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8263', '', '', '', '他加盟店の決済情報になります', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '227', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8264', '', '', '', '決済PF の利用権限が停止されています', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '228', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8265', '', '', '', 'サービスがありません', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '229', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8266', '', '', '', '決済PF の利用権限が停止されています', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '230', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8267', '', '', '', 'タイムアウトになりました', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '231', 'au one-ID の ID 連携が無効となっているため、処理を中止しました。', '8268', '', '', '', 'タイムアウトになりました', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '232', 'au one-ID の ID 連携が無効となっているため、処理を中止しました。', '8269', '', '', '', 'タイムアウトになりました', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '233', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8270', '', '', '', '有効なワンタイム OpenID ではありません', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '234', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8271', '', '', '', '使用されたワンタイム OpenID です', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '235', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8272', '', '', '', 'ワンタイム OpenID の有効時間を過ぎてます', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '236', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8273', '', '', '', '課金データがありません', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '237', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8274', '', '', '', 'オーソリ取消が行われています', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '238', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8275', '', '', '', '売上確定が行われています', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '239', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8276', '', '', '', '売上取消が行われています', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '240', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8277', '', '', '', '返金が行われています', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '241', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8278', '', '', '', '請求が行われています', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '242', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8279', '', '', '', '継続課金情報はオーソリ取消できません', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '243', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8280', '', '', '', '設定された売上金額がオーソリ金額よりも
高くなっています', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '244', 'オーソリ額が限度額を超えています。', '8281', '', '', '', 'オーソリ額が限度額を超えています', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '245', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8282', '', '', '', '継続課金情報は売上確定できません', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '246', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8283', '', '', '', '実行許可のないデータです', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '247', '売上確定可能期間を過ぎています。', '8284', '', '', '', '売上確定可能期間を過ぎています', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '248', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8285', '', '', '', 'システムエラーが発生しています', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '249', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8286', '', '', '', '認証区分に該当するデータが設定されていません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '250', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。(エラーコード)', '8287', '', '', '', '認証区分に該当するデータが設定されていません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '251', '認証処理が正常に完了しませんでした。お手数ですが、登録の状態について KDDI へお問合せください。', '8288', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '252', '認証処理が正常に完了しませんでした。お手数ですが、登録の状態について KDDI へお問合せください。', '8289', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '253', '認証処理が正常に完了しませんでした。お手数ですが、登録の状態について KDDI へお問合せください。', '8290', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '254', '既に登録済みのため、処理を中止しました。', '8291', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '255', '認証処理が正常に完了しませんでした。お手数ですが、登録の状態について KDDI へお問合せください。', '8292', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '256', '認証処理が正常に完了しませんでした。お手数ですが、登録の状態について KDDI へお問合せください。', '8293', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '257', '認証処理が正常に完了しませんでした。お手数ですが、登録の状態について KDDI へお問合せください。', '8294', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '258', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。', '8295', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '259', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。', '8296', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '260', 'ご利用出来る限度額を超えています。
他のお支払い方法にて、再度、お手続きをお願い致します。', '8297', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '261', '返金処理は、本決済では使用できません。', '8346', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '262', '既に返金処理中のため、返金処理を中止しました。', '8347', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '263', '既に売上処理中のため、取消処理を中止しました。', '8348', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '264', '既に売上処理中のため、取消処理を中止しました。', '8349', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '265', '指定された金額が、与信時金額と違うため、売上処理を中止しました。', '8350', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '266', '与信結果が存在しないため、売上処理を中止しました。', '8351', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '267', '与信取消済みのため、売上処理を中止しました。', '8352', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '268', '指定された金額が、与信時金額を越えているため、売上処理を中止しました。', '8353', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '269', '売上処理が完了済みのため、処理を中止しました。', '8354', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '270', '自動売上が設定されているため、売上要求は不要です。', '8355', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '271', '与信結果が存在しないため、取消返金処理を中止しました。', '8356', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '272', '与信取消済みのため、取消返金処理処理を中止しました。', '8357', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '273', '指定された月の売上データが存在しないため、返金処理を中止しました。', '8358', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '274', '返金処理済みのため、返金処理を中止しました。', '8361', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '275', '返金処理は、売上確定日から 3 ヶ月目末日まで有効です。', '8362', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '276', 'ご指定の継続課金は既に解約済みです。', '8363', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '277', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。', '8364', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '278', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。', '8365', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '279', '日付を跨いだため、処理を中止しました。
お手数ですが、再度購入要求処理を実施してください。', '8366', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '280', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。', '8367', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '281', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。', '8368', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '282', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。', '8369', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '283', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。', '8370', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '284', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。', '8371', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '285', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。', '8372', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '286', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。', '8373', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '287', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。', '8374', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '288', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。', '8375', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '289', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。', '8376', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '290', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。', '8377', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '291', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。', '8378', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '292', 'au かんたん決済センターにてエラーが発生しました為、処理を中止しました。', '8379', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '293', '有効なユーザでありません。', '8670', '', '', '', '有効なユーザでありません。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '294', 'ユーザ様事由によるエラーです。', '8671', '', '', '', 'ユーザ様事由によるエラーです。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '295', 'お客様の契約ISP(プロバイダ)事由によるエラーです。', '8672', '', '', '', 'お客様の契約ISP(プロバイダ)事由によるエラーです。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '296', 'ユーザ設定により、定期購入の申込ができません。', '8673', '', '', '', 'ユーザ設定によるトラストクライアント利用不可です。', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '297', 'BitCash センターでエラーが発生しました。', '4411', '', '', '', '', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '298', 'ご利用のカードは使用できない状態です。↓\r\n
お手元のカードをご確認の上、別のカードで再度入力して下さい。', '4412', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '299', 'ご利用のカードは使用できない状態です。↓\r\n
お手元のカードをご確認の上、別のカードで再度入力して下さい。', '4413', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '300', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4414', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '301', '入力されたカードは、キャンペーン期間外です。↓\r\n
お手元のカードをご確認の上、別のカードで再度入力して下さい', '4415', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '302', '入力されたカードは、キャンペーン期間外です。↓\r\n
お手元のカードをご確認の上、別のカードで再度入力して下さい', '4416', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '303', 'ご利用のカードは残高が不足しています。
お手元のカードをご確認の上、別のカードで再度入力、
またはカードの残高調整をして下さい', '4417', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '304', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4418', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '305', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4419', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '306', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4420', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '307', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4421', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '308', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4422', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '309', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4423', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '310', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4424', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '311', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4425', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '312', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4426', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '313', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4427', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '314', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4428', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '315', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4429', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '316', '入力されたカード番号に誤りがあります。
お手元のカードをご確認の上、再度入力して下さい。', '4430', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '317', '入力されたカードは、本サービスでは使用できないカードです。
お手元のカードをご確認の上、別のカードで再度入力して下さい。', '4431', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '318', '入力されたカードは、本サービスでは使用できないカードです。
お手元のカードをご確認の上、別のカードで再度入力して下さい。', '4432', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '319', '入力されたカードは、ご使用できないカードです。
お手元のカードをご確認の上、別のカードで再度入力して下さい', '4433', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '320', '入力されたカードは、キャンペーン期間外です。
お手元のカードをご確認の上、別のカードで再度入力して下さい。', '4434', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '321', '入力されたカードは、ご使用できないカードです。
お手元のカードをご確認の上、別のカードで再度入力して下さい。', '4435', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '322', '入力されたカードは、キャンペーン期間外です。
お手元のカードをご確認の上、別のカードで再度入力して下さい。', '4436', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '323', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4437', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '324', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4438', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '325', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4439', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '326', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4440', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '327', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4441', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '328', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4442', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '329', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4443', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '330', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4444', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '331', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4445', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '332', 'BitCash センターにてエラーが発生しました為、減算処理を中止しました。', '4446', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '333', 'BitCash センターにてエラーが発生しました.', '4451', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '334', 'BitCash センターにてエラーが発生しました.', '4452', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '335', 'BitCash センターにてエラーが発生しました.', '4453', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '336', 'NETCASH 決済結果が NG です', '4201', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '337', 'メンテナンス中のため Yahoo!ウォレット決済はご利用できません。
しばらく待って、再度お手続きをお願いします。', '8481', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '338', 'ご指定の商品は既に解約済みです。', '8482', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '339', '決済機関夜間処理中、または、定期メンテナンス中です。', '8483', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '340', 'ご指定の商品は今月解約が行われています。
Yahoo!ウォレット決済では、解約当月に再度ご購入頂けません。', '8484', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '341', 'ご指定の商品は既に購入済みです。', '8485', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '342', 'ご指定の商品は既に購入済みです。', '8486', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '343', 'Yahoo!ウォレット決済センターにてエラーが発生したため、処理を中止しました。(エラー内容)', '8487', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '344', 'メンテナンス中のため Yahoo!ウォレット決済はご利用できません。
しばらく待って、再度お手続きをお願いします。', '8488', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '345', 'Yahoo!ウォレット決済より注文キャンセルが通知されました。', '8489', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '346', '楽天決済はメンテナンスの為、只今、ご利用できません。', '8231', '', '', '', '100：メンテナンス中', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '347', '楽天決済センターにてエラーが発生しました為、処理を中止しました。(エラー内容)', '8232', '', '', '', '200：システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '348', '楽天決済センターにてエラーが発生しました為、処理を中止しました。(エラー内容)', '8233', '', '', '', '300：入力値のフォーマットエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '349', '楽天決済センターにてエラーが発生しました為、処理を中止しました。(エラー内容)', '8234', '', '', '', '300：入力値のフォーマットエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '350', '楽天決済センターにてエラーが発生しました為、処理を中止しました。(エラー内容)', '8235', '', '', '', '300：入力値のフォーマットエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '351', '楽天決済センターにてエラーが発生しました為、処理を中止しました。(エラー内容)', '8236', '', '', '', '300：入力値のフォーマットエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '352', '楽天決済センターにてエラーが発生しました為、処理を中止しました。(エラー内容)', '8237', '', '', '', '300：入力値のフォーマットエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '353', '売上処理は、与信日から 60 日間有効です。', '8238', '', '', '', '300：入力値のフォーマットエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '354', '現在返金処理は受付不可となります。', '8239', '', '', '', '300：入力値のフォーマットエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '355', '返金処理は、売上確定日の翌月末日まで有効です。', '8240', '', '', '', '300：入力値のフォーマットエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '356', '楽天決済センターにてエラーが発生しました為、処理を中止しました。(エラー内容)', '8441', '', '', '', '300：入力値のフォーマットエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '357', '楽天決済センターにてエラーが発生しました為、処理を中止しました。(エラー内容)', '8442', '', '', '', '300：入力値のフォーマットエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '358', '楽天決済センターにてエラーが発生しました為、処理を中止しました。(エラー内容)', '8443', '', '', '', '300：入力値のフォーマットエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '359', '楽天決済センターにてエラーが発生しました為、処理を中止しました。(エラー内容)', '8444', '', '', '', '300：入力値のフォーマットエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '360', '楽天決済センターにてエラーが発生しました為、処理を中止しました。(エラー内容)', '8445', '', '', '', '300：入力値のフォーマットエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '361', '楽天決済センターにてエラーが発生しました為、処理を中止しました。(エラー内容)', '8446', '', '', '', '300：入力値のフォーマットエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '362', 'ご指定の商品は既に解約済みです。', '8541', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '363', 'リクルート決済センターにてエラーが発生しました為、処理を中止しました。({0})', '8542', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '364', 'リクルート決済はメンテナンスの為、只今、ご利用できません。', '8543', '', '', '', '業務エラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '365', '返金可能期限を超過したため返金処理を中止しました。', '8544', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '366', 'PayPal 決済センターにてエラーが発生しました為、処理を中止しました。', '4980', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '367', 'PayPal 決済センターにてエラーが発生しました為、処理を中止しました。', '4981', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '368', 'PayPal 決済センターにてエラーが発生しました為、処理を中止しました。', '4982', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '369', '決済結果が存在しないため、返金処理を中止しました。', '4983', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '370', '返金処理済みのため、返金処理を中止しました。', '4984', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '371', '返金処理は、決済日から 60 日後まで有効です。', '4985', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '372', 'PayPal 決済センターにてエラーが発生しました為、処理を中止しました。', '4986', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '373', 'PayPal 決済センターにてエラーが発生しました為、処理を中止しました。', '4987', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '374', 'PayPal 決済センターにてエラーが発生しました為、処理を中止しました。', '4988', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '375', 'PayPal 決済センターにてエラーが発生しました為、処理を中止しました。', '4989', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '376', 'PayPal 決済センターにてエラーが発生しました為、処理を中止しました。', '4990', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '377', 'PayPal 決済センターにてエラーが発生しました為、処理を中止しました。', '4991', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '378', 'PayPal 決済センターにてエラーが発生しました為、処理を中止しました。', '4992', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '379', 'PayPal 決済センターにてエラーが発生しました為、処理を中止しました。', '4993', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '380', 'PayPal 決済センターにてエラーが発生しました為、処理を中止しました。', '4994', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '381', 'PayPal 決済センターにてエラーが発生しました為、処理を中止しました。', '4995', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '382', 'PayPal 決済センターにてエラーが発生しました為、処理を中止しました。', '4996', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '383', 'PayPal 決済センターにてエラーが発生しました為、処理を中止しました。', '4997', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '384', 'PayPal 決済センターにてエラーが発生しました為、処理を中止しました。', '4998', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '385', 'PayPal 決済センターにてエラーが発生しました為、処理を中止しました。', '4999', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '386', 'PayPal 決済センターにてエラーが発生しました為、処理を中止しました。', '8001', '8223', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '387', 'PayPal 決済センターより返却された金額がマーチャントより送信された金額と異なります。', '8224', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '388', 'PayPal 決済センターより返却されたPayPal 顧客アカウント識別番号が GW が受信した値と異なります。', '8225', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '389', 'PayPal 決済センターにてエラーが発生しました為、処理を中止しました。', '8777', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '390', '既に処理が完了しているため、コミットを実施出来ません。', '8400', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '391', '既に処理が完了しているため、コミット（取消）を実施出来ません。', '8401', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '392', '指定された金額が、与信時金額と異なります。', '8402', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '393', 'NetMile 決済センターにてエラーが発生しました為、処理を中止しました。({0})', '8403', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '394', '一定時間操作されなかったため処理を中断しました。再度、ご購入手続きを最初からお願い致します。', '8404', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '395', 'NetMile 決済センターにてエラーが発生しました為、処理を中止しました。({0})', '8405', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '396', '決済ができませんでした。詳細は、決済機関にお問合せください。', '8406', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '397', '決済ができませんでした。詳細は、決済機関にお問合せください。', '8407', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '398', '決済ができませんでした。詳細は、決済機関にお問合せください。', '8408', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`,  `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('211', '399', '決済ができませんでした。詳細は、決済機関にお問合せください。', '8409', '', '', '', 'システムエラー', '0', now(), 1, now(), 1, 1);

-- 202 --
UPDATE `M_Code` SET `Note`='ST02 では売上、部分返金でパラメーターが正しく設定されていない場合に発生' WHERE `CodeId`='202' and`KeyCode`='9';
UPDATE `M_Code` SET `Note`='継続購入で、パラメーターが正しく設定されていない場合に発生' WHERE `CodeId`='202' and`KeyCode`='21';
UPDATE `M_Code` SET `Note`='継続購入で、パラメーターが正しく設定されていない場合に発生' WHERE `CodeId`='202' and`KeyCode`='22';
UPDATE `M_Code` SET `Note`='継続購入で、パラメーターが正しく設定されていない場合に発生' WHERE `CodeId`='202' and`KeyCode`='23';
UPDATE `M_Code` SET `Note`='不当な処理対象トラッキング ID を指定した場合に限り発生' WHERE `CodeId`='202' and`KeyCode`='28';
UPDATE `M_Code` SET `Note`='ST01 ではクレジットカード決済に限り発生' WHERE `CodeId`='202' and`KeyCode`='30';
UPDATE `M_Code` SET `Note`='MG02 では、登録・更新に限り発生' WHERE `CodeId`='202' and`KeyCode`='33';
UPDATE `M_Code` SET `Note`='MG02 では、登録・更新に限り発生' WHERE `CodeId`='202' and`KeyCode`='34';
UPDATE `M_Code` SET `Note`='ST01 では、都度購入・再与信に限り発生' WHERE `CodeId`='202' and`KeyCode`='35';
UPDATE `M_Code` SET `Note`='ST01 では、都度購入・再与信に限り発生' WHERE `CodeId`='202' and`KeyCode`='36';
UPDATE `M_Code` SET `Note`='ST01 では、都度購入・再与信に限り発生' WHERE `CodeId`='202' and`KeyCode`='37';
UPDATE `M_Code` SET `Note`='ST01 では、都度購入・再与信に限り発生' WHERE `CodeId`='202' and`KeyCode`='38';
UPDATE `M_Code` SET `Note`='MG02 で、登録・更新に限り発生' WHERE `CodeId`='202' and`KeyCode`='39';
UPDATE `M_Code` SET `Note`='MG02 で、登録・更新に限り発生' WHERE `CodeId`='202' and`KeyCode`='40';
UPDATE `M_Code` SET `Note`='MG02 で、登録・更新に限り発生' WHERE `CodeId`='202' and`KeyCode`='41';
UPDATE `M_Code` SET `Note`='MG02 では、登録・更新に限り発生' WHERE `CodeId`='202' and`KeyCode`='42';
UPDATE `M_Code` SET `Note`='再与信に限り発生' WHERE `CodeId`='202' and`KeyCode`='47';
UPDATE `M_Code` SET `Note`='再与信に限り発生' WHERE `CodeId`='202' and`KeyCode`='48';
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('202','64','注文詳細','202','311',NULL,NULL,'売上で、パラメーターが正しく設定されていない場合に発生','0',NOW(),'1',NOW(),'1','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('202','65','Pending 有効期限','203','311',NULL,NULL,'プッシュ課金購入要求で、パラメーターが正しく設定されていない場合に発生','0',NOW(),'1',NOW(),'1','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('202','66','返金枝番','204','311',NULL,NULL,'取消・返金で、パラメーターが正しく設定されていない場合に発生','0',NOW(),'1',NOW(),'1','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('202','67','決済番号返却フラグ','208','311',NULL,NULL,'プッシュ課金購入要求で、パラメーターが正しく設定されていない場合に発生','0',NOW(),'1',NOW(),'1','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('202','68','取消対象年月','200','401',NULL,NULL,'取消・返金で、パラメーターが正しく設定されていない場合に発生','0',NOW(),'1',NOW(),'1','1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Class4, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) values ('202','69','取消対象年月','200','402',NULL,NULL,'取消・返金で、パラメーターが正しく設定されていない場合に発生','0',NOW(),'1',NOW(),'1','1');

-- 199 --
UPDATE `M_Code` SET `Note`='https://atobarai-user.jp/todoitekara/#linepay' WHERE `CodeId`='199' and`KeyCode`='22';
UPDATE `M_Code` SET `Note`='https://atobarai-user.jp/todoitekara/#post-office' WHERE `CodeId`='199' and`KeyCode`='23';
UPDATE `M_Code` SET `Note`='https://atobarai-user.jp/todoitekara/#bank' WHERE `CodeId`='199' and`KeyCode`='24';

-- 163 --
UPDATE `M_Code` SET `Class2`='0' WHERE `CodeId`='163' and`KeyCode`='1';
UPDATE `M_Code` SET `Class2`='2' WHERE `CodeId`='163' and`KeyCode`='2';
UPDATE `M_Code` SET `Class2`='1' WHERE `CodeId`='163' and`KeyCode`='3';
UPDATE `M_Code` SET `Class2`='0' WHERE `CodeId`='163' and`KeyCode`='4';
UPDATE `M_Code` SET `Class2`='99' WHERE `CodeId`='163' and`KeyCode`='5';
UPDATE `M_Code` SET `Class2`='0' WHERE `CodeId`='163' and`KeyCode`='6';
UPDATE `M_Code` SET `Class2`='0' WHERE `CodeId`='163' and`KeyCode`='7';
UPDATE `M_Code` SET `Class2`='99' WHERE `CodeId`='163' and`KeyCode`='8';
UPDATE `M_Code` SET `Class2`='99' WHERE `CodeId`='163' and`KeyCode`='9';
UPDATE `M_Code` SET `Class2`='0' WHERE `CodeId`='163' and`KeyCode`='10';
UPDATE `M_Code` SET `Class2`='0' WHERE `CodeId`='163' and`KeyCode`='11';
UPDATE `M_Code` SET `Class2`='99' WHERE `CodeId`='163' and`KeyCode`='13';
UPDATE `M_Code` SET `Class2`='0' WHERE `CodeId`='163' and`KeyCode`='14';
UPDATE `M_Code` SET `Class2`='99' WHERE `CodeId`='163' and`KeyCode`='15';
UPDATE `M_Code` SET `Class2`='99' WHERE `CodeId`='163' and`KeyCode`='16';
UPDATE `M_Code` SET `Class2`='99' WHERE `CodeId`='163' and`KeyCode`='17';
UPDATE `M_Code` SET `Class2`='99' WHERE `CodeId`='163' and`KeyCode`='18';
UPDATE `M_Code` SET `Class2`='99' WHERE `CodeId`='163' and`KeyCode`='19';
UPDATE `M_Code` SET `Class2`='99' WHERE `CodeId`='163' and`KeyCode`='20';

-- 213 --
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('213', '0', '後払いドットコム', 'customer@ato-barai.com', 'doc/help/atobarai_help.html','0', now(), '34734', now(), '34734', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('213', '1', '届いてから払い', 'todoitekara@ato-barai.com', 'doc/help/todoitekara_help.html', '0', now(), '34734', now(), '34734', '1');

-- 72 --
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (72, 428, 'サービス名', '{ServiceName}', NULL, NULL, NULL, NULL, 1, NOW(), 34734, NOW(), 34734, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (72, 429, 'サービスメール', '{ServiceMail}', NULL, NULL, NULL, NULL, 1, NOW(), 34734, NOW(), 34734, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (72, 430, '注文ID', '{OrderId}', NULL, NULL, NULL, NULL, 1, NOW(), 34734, NOW(), 34734, 1);

-- 208 --
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('208', '2', 'spapp2', NULL, NULL, NULL, NULL, '', 0, NOW(), 34734, NOW(), 34734, 1);

-- 185 --
UPDATE `M_Code` SET `Note` = '<table><tr><td style=\"font-size: 14px ;font-weight: normal\"><B>注文情報確認ページの閲覧は一定期間で終了いたします。</td></tr><tr><td style=\"font-size: 14px ;font-weight: normal\"><B>注文情報のお問い合わせにつきましては弊社サポートセンターまでお問い合わせください。</td></tr><tr><td style=\"font-size: 14px ;font-weight: normal\"><B>サポートセンター電話番号： TEL: 0120-667-690（9:00 ～ 18:00）</td></tr><tr><td style=\"font-size: 14px ;font-weight: nrmal\"><B>または、請求書に記載されている情報を元に<a href=\"login/login\"><U>こちら</U></a>へログインしてください。</td></tr></table>' WHERE (`CodeId` = '185') and (`KeyCode` = '0');

-- 186 --
UPDATE `M_Code` SET `Note` = '<table><tr><td style=\"font-size: 14px ;font-weight: normal\"><B>注文情報確認ページの閲覧は一定期間で終了いたします。</td></tr><tr><td style=\"font-size: 14px ;font-weight: normal\"><B>注文情報のお問い合わせにつきましては弊社サポートセンターまでお問い合わせください。</td></tr><tr><td style=\"font-size: 14px ;font-weight: normal\"><B>サポートセンター電話番号： TEL: 0120-667-690（9:00 ～ 18:00）</td></tr><tr><td style=\"font-size: 14px ;font-weight: nrmal\"><B>または、請求書に記載されている情報を元に<a href=\"login/login\"><U>こちら</U></a>へログインしてください。</td></tr></table>' WHERE (`CodeId` = '186') and (`KeyCode` = '0');

-- M_CodeManagement
ALTER TABLE M_CodeManagement
    ADD COLUMN `Class4ValidFlg` tinyint(4) NOT NULL DEFAULT '0' AFTER Class3Name,
	ADD COLUMN `Class4Name` varchar(50) DEFAULT NULL AFTER Class4ValidFlg;
INSERT INTO `M_CodeManagement` (`CodeId`, `CodeName`, `KeyPhysicalName`, `KeyLogicName`, `Class1ValidFlg`, `Class1Name`, `Class2ValidFlg`, `Class2Name`, `Class3ValidFlg`, `Class3Name`, `Class4ValidFlg`, `Class4Name`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES
('212', 'SBPS契約先', NULL, 'SBPS契約先', '0', NULL, '0', NULL, '0', NULL, '0', NULL, now(), '1', now(), '1', '1');
INSERT INTO `M_CodeManagement` (`CodeId`, `CodeName`, `KeyPhysicalName`, `KeyLogicName`, `Class1ValidFlg`, `Class1Name`, `Class2ValidFlg`, `Class2Name`, `Class3ValidFlg`, `Class3Name`, `Class4ValidFlg`, `Class4Name`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES
('211', 'SBペイメントリンク型のエラー', NULL, 'SBPSエラー', '0', NULL, '0', NULL, '0', NULL, '0', NULL, now(), '1', now(), '1', '1');
UPDATE `M_CodeManagement` SET `CodeName`='届いてから払い利用', `KeyLogicName`='届いてから払い利用' WHERE `CodeId`='199';
INSERT INTO `M_CodeManagement` (`CodeId`, `CodeName`, `KeyLogicName`, `Class1ValidFlg`, `Class2ValidFlg`, `Class3ValidFlg`, `Class4ValidFlg`, `RegistId`, `UpdateId`, `ValidFlg`) VALUES ('213', '後払いと届いてから払いの設定', '後払いと届いてから払いの設定', '1', '1', '1', '1', '34734', '34734', '1');


-- T_Site
ALTER TABLE T_Site
    ADD COLUMN PaymentAfterArrivalName VARCHAR(255) COMMENT "届いてから決済名称" AFTER PaymentAfterArrivalFlg;
	
-- M_Payment
ALTER TABLE M_Payment ADD LogoUrl varchar(500) DEFAULT NULL;

UPDATE `M_Payment` SET `LogoUrl`='my_page/seikyu_PayB' WHERE `PaymentId`='1';
UPDATE `M_Payment` SET `LogoUrl`='my_page/seikyu_paypay' WHERE `PaymentId`='2';
UPDATE `M_Payment` SET `LogoUrl`='my_page/seikyu_LINEpay' WHERE `PaymentId`='3';
UPDATE `M_Payment` SET `LogoUrl`='my_page/seikyu_Rakuten' WHERE `PaymentId`='4';
UPDATE `M_Payment` SET `LogoUrl`='my_page/seikyu_FamiPay' WHERE `PaymentId`='5';
UPDATE `M_Payment` SET `LogoUrl`='my_page/seikyu_PostPay' WHERE `PaymentId`='6';	

-- M_TemplateField
INSERT INTO M_TemplateField VALUES ( 2, 46, 'ReceiptClass', '入金方法', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 1, NOW(), 1, 0);
INSERT INTO M_TemplateField VALUES ( 2, 47, 'ReceiptProcessDate', '入金確認日', 'DATE', 0, NULL, 0, NULL, NULL, NULL, NOW(), 1, NOW(), 1, 0);

-- T_SystemProperty
INSERT INTO `T_SystemProperty` (`Module`, `Category`, `Name`, `PropValue`, `Description`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('[DEFAULT]', 'sbpspayment', 'timeout', '600', 'タイムアウト（秒）', now(), '1', now(), '1', '1');
INSERT INTO `T_SystemProperty` (`Module`, `Category`, `Name`, `PropValue`, `Description`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('[DEFAULT]', 'sbpspayment', 'success_url', 'https://www1.atobarai-dev.jp/orderpage/sbpssettlement/success', '届いてから決済の成功URL', now(), '1', now(), '1', '1');
INSERT INTO `T_SystemProperty` (`Module`, `Category`, `Name`, `PropValue`, `Description`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('[DEFAULT]', 'sbpspayment', 'cancel_url', 'https://www1.atobarai-dev.jp/orderpage/sbpssettlement/cancel', '届いてから決済のキャンセルURL', now(), '1', now(), '1', '1');
INSERT INTO `T_SystemProperty` (`Module`, `Category`, `Name`, `PropValue`, `Description`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('[DEFAULT]', 'sbpspayment', 'error_url', 'https://www1.atobarai-dev.jp/orderpage/sbpssettlement/error', '届いてから決済のエラーURL', now(), '1', now(), '1', '1');
INSERT INTO `T_SystemProperty` (`Module`, `Category`, `Name`, `PropValue`, `Description`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('[DEFAULT]', 'sbpspayment', 'pagecon_url', 'https://www1.atobarai-dev.jp/orderpage/sbpssettlement/pagecon', '届いてから決済の結果CGIの返却URL', now(), '1', now(), '1', '1');
INSERT INTO `T_SystemProperty` (`Module`, `Category`, `Name`, `PropValue`, `Description`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('[DEFAULT]', 'sbpspayment', 'purchase_url', 'https://stbfep.sps-system.com/f01/FepBuyInfoReceive.do', '購入要求接続先', now(), '1', now(), '1', '1');


-- Add 4 template for T_MailTemplate
-- delete mail class 106 and 107
DELETE FROM `coraldb_new01`.`t_mailtemplate` WHERE (`Class` = '106');
DELETE FROM `coraldb_new01`.`t_mailtemplate` WHERE (`Class` = '107');
-- insert new mail template
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (121,'届いてから決済完了メール（PC）(領収書コメントあり)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【{ServiceName}】{PaymentMethod}決済への手続き完了のお知らせ (自動配信メール）','{ServiceName}','{CustomerNameKj}様                            
                            
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
商品名：{OrderItems}                            
ご利用金額：{UseAmount}                            
                            
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
                            
--------------------------------------------------------------                            
後払い請求代行サービス【後払いドットコム】                            
                            
  お問合せ先：03-4326-3600                            
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）                            
  mail: {ServiceMail}                            
                              
　運営会社：株式会社キャッチボール                            
　住所：〒140-0002　東京都品川区東品川2-2-24　天王洲セントラルタワー 12F                            
--------------------------------------------------------------                            
',null,now(),34734,now(),34734,1);
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (122,'届いてから決済完了メール（CEL）(領収書コメントあり)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【後払い.com】{PaymentMethod}決済への手続き完了のお知らせ (自動配信メール）','{ServiceName}','{CustomerNameKj}様                            
                            
この度は、{SiteNameKj}様で商品ご購入の際に、                            
後払いドットコムをご利用いただきまして                            
まことにありがとうございました。                            
                            
{PaymentMethod}決済の手続きが完了いたしましたので                            
ご報告いたします。                            
                            
以下が、今回ご注文の内容でございます。                            
                            
【ご注文内容】                            
ご注文日：{OrderDate}                            
ご注文店舗：{SiteNameKj}                            
商品名：{OrderItems}                            
ご利用金額：{UseAmount}                            
                            
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
                            
--------------------------------------------------------------                            
後払い請求代行サービス【後払いドットコム】                            
                            
  お問合せ先：03-4326-3600                            
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）                            
  mail: customer@ato-barai.com                            
                              
　運営会社：株式会社キャッチボール                            
　住所：〒140-0002　東京都品川区東品川2-2-24　天王洲セントラルタワー 12F                            
--------------------------------------------------------------                            
',null,now(),34734,now(),34734,1);
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (123,'届いてから決済完了メール（PC）(領収書コメントなし)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【後払いドットコム】{PaymentMethod}決済完了のお知らせ','{ServiceName}','{CustomerNameKj}様                            
                            
この度は、{SiteNameKj}様で商品ご購入の際に、                            
後払いドットコムをご利用いただきまして                            
まことにありがとうございました。                            
                            
{PaymentMethod}決済の手続きが完了いたしましたので                            
ご報告いたします。                            
                            
以下が、今回ご注文の内容でございます。                            
                            
【ご注文内容】                            
ご注文日：{OrderDate}                            
ご注文店舗：{SiteNameKj}                            
商品名：{OrderItems}                            
ご利用金額：{UseAmount}                            
                            
またのご利用を心よりお待ちしております。                            
                            
    
■商品の返品・未着など商品については                            
直接ご購入店様にお問い合わせください。                            
ご購入店様：{SiteNameKj}                            
電話：{Phone}                            
URL：{SiteUrl}                            
                            
--------------------------------------------------------------                            
後払い請求代行サービス【後払いドットコム】                            
                            
  お問合せ先：03-4326-3600                            
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）                            
  mail: customer@ato-barai.com                            
                              
　運営会社：株式会社キャッチボール                            
　住所：〒140-0002　東京都品川区東品川2-2-24　天王洲セントラルタワー 12F                            
--------------------------------------------------------------                            
',null,now(),34734,now(),34734,1);
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (124,'届いてから決済完了メール（CEL）(領収書コメントなし)','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'【後払い.com】{PaymentMethod}決済への手続き完了のお知らせ (自動配信メール）','{ServiceName}','{CustomerNameKj}様                            
                            
この度は、{SiteNameKj}様で商品ご購入の際に、                            
後払いドットコムをご利用いただきまして                            
まことにありがとうございました。                            
                            
{PaymentMethod}決済の手続きが完了いたしましたので                            
ご報告いたします。                            
                            
以下が、今回ご注文の内容でございます。                            
                            
【ご注文内容】                            
ご注文日：{OrderDate}                            
ご注文店舗：{SiteNameKj}                            
商品名：{OrderItems}                            
ご利用金額：{UseAmount}                            
                            
またのご利用を心よりお待ちしております。                                                  
                            
■商品の返品・未着など商品については                            
直接ご購入店様にお問い合わせください。                            
ご購入店様：{SiteNameKj}                            
電話：{Phone}                            
URL：{SiteUrl}                            
                            
--------------------------------------------------------------                            
後払い請求代行サービス【後払いドットコム】                            
                            
  お問合せ先：03-4326-3600                            
  営業時間：9:00～18:00　年中無休（年末・年始のぞく）                            
  mail: customer@ato-barai.com                            
                              
　運営会社：株式会社キャッチボール                            
　住所：〒140-0002　東京都品川区東品川2-2-24　天王洲セントラルタワー 12F                            
--------------------------------------------------------------                            
',null,now(),34734,now(),34734,1);

INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) values (126,'事業者宛テストメール ','{ServiceName}','{ServiceName}','{ServiceMail}',null,null,null,'{ServiceName}　送達テストメール','{ServiceName}','本メールは送達確認用のテストメールです。\r\n\r\n事業者：{EnterpriseNameKj} 様\r\nログインID：{LoginId}',null,now(),34734,now(),34734,1);

/*マイページのデータベース*/
-- Mypage.T_SbpsReceiptControl
CREATE TABLE IF NOT EXISTS `T_SbpsReceiptControl` (
  `OrderSeq` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '注文Seq',
  `PayType` int(1) NOT NULL COMMENT '追加支払い方法_区分1：届いてから決済',
  `PaymentName` varchar(30) NOT NULL COMMENT '支払方法（SBPSからの戻り値を設定。）',
  `ReceiptDate` datetime NOT NULL COMMENT '決済完了日時',
  `RegistDate` datetime NOT NULL COMMENT '登録日時',
  `RegistId` int(11) NOT NULL COMMENT '登録者',
  `UpdateDate` datetime NOT NULL COMMENT '更新日時',
  `UpdateId` int(11) NOT NULL COMMENT '更新者',
  PRIMARY KEY (`OrderSeq`),
  KEY `Idx_01` (`OrderSeq`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='届いてから決済管理';

-- Create View M_Payment
delimiter $$

CREATE ALGORITHM=UNDEFINED DEFINER=`coraluser`@`%` SQL SECURITY DEFINER VIEW `coraldb_mypage01`.`MV_Payment` AS select 
`coraldb_new01`.`M_Payment`.`PaymentId` AS `PaymentId`,
`coraldb_new01`.`M_Payment`.`OemId` AS `OemId`,
`coraldb_new01`.`M_Payment`.`PaymentGroupName` AS `PaymentGroupName`,
`coraldb_new01`.`M_Payment`.`PaymentName` AS `PaymentName`,
`coraldb_new01`.`M_Payment`.`SortId` AS `SortId`,
`coraldb_new01`.`M_Payment`.`UseFlg` AS `UseFlg`,
`coraldb_new01`.`M_Payment`.`FixedId` AS `FixedId`,
`coraldb_new01`.`M_Payment`.`ValidFlg` AS `ValidFlg`,
`coraldb_new01`.`M_Payment`.`LogoUrl` AS `LogoUrl`
from `coraldb_new01`.`M_Payment`$$

-- Create View MV_SitePayment
delimiter $$

CREATE ALGORITHM=UNDEFINED DEFINER=`coraluser`@`%` SQL SECURITY DEFINER VIEW `coraldb_mypage01`.`MV_SitePayment` AS select 
`coraldb_new01`.`T_SitePayment`.`SitePaymentId` AS `SitePaymentId`,
`coraldb_new01`.`T_SitePayment`.`SiteId` AS `SiteId`,
`coraldb_new01`.`T_SitePayment`.`PaymentId` AS `PaymentId`,
`coraldb_new01`.`T_SitePayment`.`UseFlg` AS `UseFlg`,
`coraldb_new01`.`T_SitePayment`.`ApplyDate` AS `ApplyDate`,
`coraldb_new01`.`T_SitePayment`.`UseStartDate` AS `UseStartDate`,
`coraldb_new01`.`T_SitePayment`.`UseEndDate` AS `UseEndDate`,
`coraldb_new01`.`T_SitePayment`.`ApplyFinishDate` AS `ApplyFinishDate`,
`coraldb_new01`.`T_SitePayment`.`UseStartFixFlg` AS `UseStartFixFlg`,
`coraldb_new01`.`T_SitePayment`.`ValidFlg` AS `ValidFlg`
from `coraldb_new01`.`T_SitePayment`$$

-- Create View MV_SbpsPayment
delimiter $$

CREATE ALGORITHM=UNDEFINED DEFINER=`coraluser`@`%` SQL SECURITY DEFINER VIEW `coraldb_mypage01`.`MV_SbpsPayment` AS select 
`coraldb_new01`.`M_SbpsPayment`.`SbpsPaymentId` AS `SbpsPaymentId`,
`coraldb_new01`.`M_SbpsPayment`.`OemId` AS `OemId`,
`coraldb_new01`.`M_SbpsPayment`.`PaymentName` AS `PaymentName`,
`coraldb_new01`.`M_SbpsPayment`.`PaymentNameKj` AS `PaymentNameKj` ,
`coraldb_new01`.`M_SbpsPayment`.`SortId` AS `SortId`,
`coraldb_new01`.`M_SbpsPayment`.`LogoUrl` AS `LogoUrl`
from `coraldb_new01`.`M_SbpsPayment`$$

-- Create View MV_SiteSbpsPayment
delimiter $$

CREATE ALGORITHM=UNDEFINED DEFINER=`coraluser`@`%` SQL SECURITY DEFINER VIEW `coraldb_mypage01`.`MV_SiteSbpsPayment` AS select `coraldb_new01`.`T_SiteSbpsPayment`.`PaymentId` AS `PaymentId`,`coraldb_new01`.`T_SiteSbpsPayment`.`NumUseDay` AS `NumUseDay`,`coraldb_new01`.`T_SiteSbpsPayment`.`UseStartDate` AS `UseStartDate`,`coraldb_new01`.`T_SiteSbpsPayment`.`ValidFlg` AS `ValidFlg`,`coraldb_new01`.`T_SiteSbpsPayment`.`SiteId` AS `SiteId` from `coraldb_new01`.`T_SiteSbpsPayment`$$

-- Alter View MV_Site
delimiter $$

ALTER VIEW  `coraldb_mypage01`.`MV_Site` AS select `coraldb_new01`.`T_Site`.`SiteId` AS `SiteId`,`coraldb_new01`.`T_Site`.`RegistDate` AS `RegistDate`,`coraldb_new01`.`T_Site`.`EnterpriseId` AS `EnterpriseId`,`coraldb_new01`.`T_Site`.`SiteNameKj` AS `SiteNameKj`,`coraldb_new01`.`T_Site`.`SiteNameKn` AS `SiteNameKn`,`coraldb_new01`.`T_Site`.`NickName` AS `NickName`,`coraldb_new01`.`T_Site`.`Url` AS `Url`,`coraldb_new01`.`T_Site`.`ReqMailAddrFlg` AS `ReqMailAddrFlg`,`coraldb_new01`.`T_Site`.`ValidFlg` AS `ValidFlg`,`coraldb_new01`.`T_Site`.`SiteForm` AS `SiteForm`,`coraldb_new01`.`T_Site`.`CombinedClaimFlg` AS `CombinedClaimFlg`,`coraldb_new01`.`T_Site`.`OutOfAmendsFlg` AS `OutOfAmendsFlg`,`coraldb_new01`.`T_Site`.`FirstClaimLayoutMode` AS `FirstClaimLayoutMode`,`coraldb_new01`.`T_Site`.`ServiceTargetClass` AS `ServiceTargetClass`,`coraldb_new01`.`T_Site`.`AutoCreditLimitAmount` AS `AutoCreditLimitAmount`,`coraldb_new01`.`T_Site`.`ClaimJournalClass` AS `ClaimJournalClass`,`coraldb_new01`.`T_Site`.`SettlementAmountLimit` AS `SettlementAmountLimit`,`coraldb_new01`.`T_Site`.`SettlementFeeRate` AS `SettlementFeeRate`,`coraldb_new01`.`T_Site`.`ClaimFeeBS` AS `ClaimFeeBS`,`coraldb_new01`.`T_Site`.`ClaimFeeDK` AS `ClaimFeeDK`,`coraldb_new01`.`T_Site`.`ReClaimFeeSetting` AS `ReClaimFeeSetting`,`coraldb_new01`.`T_Site`.`ReClaimFee` AS `ReClaimFee`,`coraldb_new01`.`T_Site`.`ReClaimFee1` AS `ReClaimFee1`,`coraldb_new01`.`T_Site`.`ReClaimFee3` AS `ReClaimFee3`,`coraldb_new01`.`T_Site`.`ReClaimFee4` AS `ReClaimFee4`,`coraldb_new01`.`T_Site`.`ReClaimFee5` AS `ReClaimFee5`,`coraldb_new01`.`T_Site`.`ReClaimFee6` AS `ReClaimFee6`,`coraldb_new01`.`T_Site`.`ReClaimFee7` AS `ReClaimFee7`,`coraldb_new01`.`T_Site`.`ReClaimFeeStartRegistDate` AS `ReClaimFeeStartRegistDate`,`coraldb_new01`.`T_Site`.`ReClaimFeeStartDate` AS `ReClaimFeeStartDate`,`coraldb_new01`.`T_Site`.`FirstCreditTransferClaimFee` AS `FirstCreditTransferClaimFee`,`coraldb_new01`.`T_Site`.`FirstCreditTransferClaimFeeWeb` AS `FirstCreditTransferClaimFeeWeb`,`coraldb_new01`.`T_Site`.`CreditTransferClaimFee` AS `CreditTransferClaimFee`,`coraldb_new01`.`T_Site`.`OemSettlementFeeRate` AS `OemSettlementFeeRate`,`coraldb_new01`.`T_Site`.`OemClaimFee` AS `OemClaimFee`,`coraldb_new01`.`T_Site`.`SystemFee` AS `SystemFee`,`coraldb_new01`.`T_Site`.`CreditCriterion` AS `CreditCriterion`,`coraldb_new01`.`T_Site`.`CreditOrderUseAmount` AS `CreditOrderUseAmount`,`coraldb_new01`.`T_Site`.`AutoCreditDateFrom` AS `AutoCreditDateFrom`,`coraldb_new01`.`T_Site`.`AutoCreditDateTo` AS `AutoCreditDateTo`,`coraldb_new01`.`T_Site`.`AutoCreditCriterion` AS `AutoCreditCriterion`,`coraldb_new01`.`T_Site`.`AutoClaimStopFlg` AS `AutoClaimStopFlg`,`coraldb_new01`.`T_Site`.`SelfBillingFlg` AS `SelfBillingFlg`,`coraldb_new01`.`T_Site`.`SelfBillingFixFlg` AS `SelfBillingFixFlg`,`coraldb_new01`.`T_Site`.`CombinedClaimDate` AS `CombinedClaimDate`,`coraldb_new01`.`T_Site`.`LimitDatePattern` AS `LimitDatePattern`,`coraldb_new01`.`T_Site`.`LimitDay` AS `LimitDay`,`coraldb_new01`.`T_Site`.`PayingBackFlg` AS `PayingBackFlg`,`coraldb_new01`.`T_Site`.`PayingBackDays` AS `PayingBackDays`,`coraldb_new01`.`T_Site`.`SiteConfDate` AS `SiteConfDate`,`coraldb_new01`.`T_Site`.`CreaditStartMail` AS `CreaditStartMail`,`coraldb_new01`.`T_Site`.`CreaditCompMail` AS `CreaditCompMail`,`coraldb_new01`.`T_Site`.`ClaimMail` AS `ClaimMail`,`coraldb_new01`.`T_Site`.`ReceiptMail` AS `ReceiptMail`,`coraldb_new01`.`T_Site`.`CancelMail` AS `CancelMail`,`coraldb_new01`.`T_Site`.`AddressMail` AS `AddressMail`,`coraldb_new01`.`T_Site`.`SoonPaymentMail` AS `SoonPaymentMail`,`coraldb_new01`.`T_Site`.`NotPaymentConfMail` AS `NotPaymentConfMail`,`coraldb_new01`.`T_Site`.`CreditResultMail` AS `CreditResultMail`,`coraldb_new01`.`T_Site`.`AutoJournalDeliMethodId` AS `AutoJournalDeliMethodId`,`coraldb_new01`.`T_Site`.`AutoJournalIncMode` AS `AutoJournalIncMode`,`coraldb_new01`.`T_Site`.`SitClass` AS `SitClass`,`coraldb_new01`.`T_Site`.`T_OrderClass` AS `T_OrderClass`,`coraldb_new01`.`T_Site`.`PrintFormDK` AS `PrintFormDK`,`coraldb_new01`.`T_Site`.`PrintFormBS` AS `PrintFormBS`,`coraldb_new01`.`T_Site`.`FirstClaimKisanbiDelayDays` AS `FirstClaimKisanbiDelayDays`,`coraldb_new01`.`T_Site`.`KisanbiDelayDays` AS `KisanbiDelayDays`,`coraldb_new01`.`T_Site`.`RemindStopClass` AS `RemindStopClass`,`coraldb_new01`.`T_Site`.`BarcodeLimitDays` AS `BarcodeLimitDays`,`coraldb_new01`.`T_Site`.`CombinedClaimChargeFeeFlg` AS `CombinedClaimChargeFeeFlg`,`coraldb_new01`.`T_Site`.`YuchoMT` AS `YuchoMT`,`coraldb_new01`.`T_Site`.`CreditJudgeMethod` AS `CreditJudgeMethod`,`coraldb_new01`.`T_Site`.`AverageUnitPriceRate` AS `AverageUnitPriceRate`,`coraldb_new01`.`T_Site`.`SelfBillingOemClaimFee` AS `SelfBillingOemClaimFee`,`coraldb_new01`.`T_Site`.`ClaimDisposeMail` AS `ClaimDisposeMail`,`coraldb_new01`.`T_Site`.`MultiOrderCount` AS `MultiOrderCount`,`coraldb_new01`.`T_Site`.`MultiOrderScore` AS `MultiOrderScore`,`coraldb_new01`.`T_Site`.`NgChangeFlg` AS `NgChangeFlg`,`coraldb_new01`.`T_Site`.`ShowNgReason` AS `ShowNgReason`,`coraldb_new01`.`T_Site`.`MuhoshoChangeDays` AS `MuhoshoChangeDays`,`coraldb_new01`.`T_Site`.`JintecManualReqFlg` AS `JintecManualReqFlg`,`coraldb_new01`.`T_Site`.`OutOfTermcheck` AS `OutOfTermcheck`,`coraldb_new01`.`T_Site`.`Telcheck` AS `Telcheck`,`coraldb_new01`.`T_Site`.`Addresscheck` AS `Addresscheck`,`coraldb_new01`.`T_Site`.`PostalCodecheck` AS `PostalCodecheck`,`coraldb_new01`.`T_Site`.`Ent_OrderIdcheck` AS `Ent_OrderIdcheck`,`coraldb_new01`.`T_Site`.`EtcAutoArrivalFlg` AS `EtcAutoArrivalFlg`,`coraldb_new01`.`T_Site`.`EtcAutoArrivalNumber` AS `EtcAutoArrivalNumber`,`coraldb_new01`.`T_Site`.`JintecJudge` AS `JintecJudge`,`coraldb_new01`.`T_Site`.`JintecJudge0` AS `JintecJudge0`,`coraldb_new01`.`T_Site`.`JintecJudge1` AS `JintecJudge1`,`coraldb_new01`.`T_Site`.`JintecJudge2` AS `JintecJudge2`,`coraldb_new01`.`T_Site`.`JintecJudge3` AS `JintecJudge3`,`coraldb_new01`.`T_Site`.`JintecJudge4` AS `JintecJudge4`,`coraldb_new01`.`T_Site`.`JintecJudge5` AS `JintecJudge5`,`coraldb_new01`.`T_Site`.`JintecJudge6` AS `JintecJudge6`,`coraldb_new01`.`T_Site`.`JintecJudge7` AS `JintecJudge7`,`coraldb_new01`.`T_Site`.`JintecJudge8` AS `JintecJudge8`,`coraldb_new01`.`T_Site`.`JintecJudge9` AS `JintecJudge9`,`coraldb_new01`.`T_Site`.`PaymentAfterArrivalFlg` AS `PaymentAfterArrivalFlg`,`coraldb_new01`.`T_Site`.`MerchantId` AS `MerchantId`,`coraldb_new01`.`T_Site`.`ServiceId` AS `ServiceId`,`coraldb_new01`.`T_Site`.`HashKey` AS `HashKey`,`coraldb_new01`.`T_Site`.`BasicId` AS `BasicId`,`coraldb_new01`.`T_Site`.`BasicPw` AS `BasicPw`,`coraldb_new01`.`T_Site`.`ReceiptIssueProviso` AS `ReceiptIssueProviso`,`coraldb_new01`.`T_Site`.`SmallLogo` AS `SmallLogo`,`coraldb_new01`.`T_Site`.`SpecificTransUrl` AS `SpecificTransUrl`,`coraldb_new01`.`T_Site`.`CSSettlementFeeRate` AS `CSSettlementFeeRate`,`coraldb_new01`.`T_Site`.`CSClaimFeeBS` AS `CSClaimFeeBS`,`coraldb_new01`.`T_Site`.`CSClaimFeeDK` AS `CSClaimFeeDK`,`coraldb_new01`.`T_Site`.`ReissueCount` AS `ReissueCount`,`coraldb_new01`.`T_Site`.`ClaimAutoJournalIncMode` AS `ClaimAutoJournalIncMode`,`coraldb_new01`.`T_Site`.`RegistId` AS `RegistId`,`coraldb_new01`.`T_Site`.`UpdateDate` AS `UpdateDate`,`coraldb_new01`.`T_Site`.`UpdateId` AS `UpdateId`,`coraldb_new01`.`T_Site`.`PaymentAfterArrivalName` AS `PaymentAfterArrivalName`,`coraldb_new01`.`T_Site`.`ReceiptUsedFlg` AS `ReceiptUsedFlg` from `coraldb_new01`.`T_Site`;

-- 20220420以降
-- cbadmin
-- T_ToBackMypageIF
CREATE TABLE `T_ToBackMypageIF` (
    `Seq` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'ｼｰｹﾝｽ',
    `Status` TINYINT(4) NOT NULL COMMENT 'ステータス 0：指示、1：クローズ、9：エラー',
    `IFClass` TINYINT(4) NOT NULL COMMENT '連携区分 1：入金 2：入金取消',
    `IFData` text NULL DEFAULT NULL COMMENT'連携内容 JSON形式で保存。内容は連携区分毎に異なる。 ※シート「基幹反映指示インタフェース項目一覧」参照',
    `OrderSeq` BIGINT(20) NOT NULL COMMENT '注文SEQ',
    `RegistDate` DATETIME NOT NULL COMMENT '登録日時',
    `UpdateDate` DATETIME NOT NULL COMMENT '更新日時',
    `ValidFlg` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '有効フラグ',
    PRIMARY KEY (`Seq`),
    KEY `i1` (`Status`),
    KEY `i2` (`OrderSeq`),
    KEY `i3` (`RegistDate`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- T_ReceiptControl
ALTER TABLE T_ReceiptControl ADD COLUMN `Receipt_Note` TEXT  DEFAULT NULL AFTER `MailRetryCount`;
UPDATE `coraldb_test03`.`M_Code` SET `KeyContent`='クレジット決済(VISA/MASTER）', `Class1`='credit_vm', `Class3`='VM', `Class4`='5' WHERE `CodeId`='198' and`KeyCode`='5';
INSERT INTO `coraldb_test03`.`M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('198', '21', 'クレジット決済(JCB/AMEX）', 'credit_ja', '1', 'JA', '6', '0', '2022-02-10 16:41:49', '1', '2022-02-10 16:41:49', '1', '1');
INSERT INTO `coraldb_test03`.`M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('198', '22', 'クレジット決済(Dinars）', 'credit_d', '1', 'D', '7', '0', '2022-02-10 16:41:49', '1', '2022-02-10 16:41:49', '1', '1');

-- M_Code
-- 198 --
UPDATE `coraldb_test03`.`M_Code` SET `Class4`='1' WHERE `CodeId`='198' and`KeyCode`='1';
UPDATE `coraldb_test03`.`M_Code` SET `Class4`='2' WHERE `CodeId`='198' and`KeyCode`='2';
UPDATE `coraldb_test03`.`M_Code` SET `Class4`='3' WHERE `CodeId`='198' and`KeyCode`='3';
UPDATE `coraldb_test03`.`M_Code` SET `Class4`='4' WHERE `CodeId`='198' and`KeyCode`='4';
UPDATE `coraldb_test03`.`M_Code` SET `Class4`='8' WHERE `CodeId`='198' and`KeyCode`='6';
UPDATE `coraldb_test03`.`M_Code` SET `Class4`='9' WHERE `CodeId`='198' and`KeyCode`='7';
UPDATE `coraldb_test03`.`M_Code` SET `Class4`='10' WHERE `CodeId`='198' and`KeyCode`='8';
UPDATE `coraldb_test03`.`M_Code` SET `Class4`='11' WHERE `CodeId`='198' and`KeyCode`='10';
UPDATE `coraldb_test03`.`M_Code` SET `Class4`='12' WHERE `CodeId`='198' and`KeyCode`='11';
UPDATE `coraldb_test03`.`M_Code` SET `Class4`='13' WHERE `CodeId`='198' and`KeyCode`='13';
UPDATE `coraldb_test03`.`M_Code` SET `Class4`='14' WHERE `CodeId`='198' and`KeyCode`='14';
UPDATE `coraldb_test03`.`M_Code` SET `Class4`='15' WHERE `CodeId`='198' and`KeyCode`='15';
UPDATE `coraldb_test03`.`M_Code` SET `Class4`='16' WHERE `CodeId`='198' and`KeyCode`='16';
UPDATE `coraldb_test03`.`M_Code` SET `Class4`='17' WHERE `CodeId`='198' and`KeyCode`='17';
UPDATE `coraldb_test03`.`M_Code` SET `Class4`='18' WHERE `CodeId`='198' and`KeyCode`='18';
UPDATE `coraldb_test03`.`M_Code` SET `Class4`='19' WHERE `CodeId`='198' and`KeyCode`='19';
UPDATE `coraldb_test03`.`M_Code` SET `Class4`='20' WHERE `CodeId`='198' and`KeyCode`='20';
-- 163 --
UPDATE `coraldb_test03`.`M_Code` SET `KeyContent`='クレジット決済(VISA/MASTER）', `Class1`='クレジット決済(VISA/MASTER）', `Class3`='1', `Class4`='5' WHERE `CodeId`='163' and`KeyCode`='5';
INSERT INTO `coraldb_test03`.`M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('163', '21', 'クレジット決済(JCB/AMEX）', 'クレジット決済(JCB/AMEX）', '99', '1', '7', '0', '2022-03-08 12:44:04', '18051', '2022-03-08 12:44:04', '18051', '1');
INSERT INTO `coraldb_test03`.`M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('163', '22', 'クレジット決済(Dinars）', 'クレジット決済(Dinars）', '99', '1', '0', '2022-03-08 12:44:04', '18051', '2022-03-08 12:44:04', '18051', '1');
UPDATE `coraldb_test03`.`M_Code` SET `Class3`='0', `Class4`='1' WHERE `CodeId`='163' and`KeyCode`='1';
UPDATE `coraldb_test03`.`M_Code` SET `Class3`='0', `Class4`='2' WHERE `CodeId`='163' and`KeyCode`='2';
UPDATE `coraldb_test03`.`M_Code` SET `Class3`='0', `Class4`='3' WHERE `CodeId`='163' and`KeyCode`='3';
UPDATE `coraldb_test03`.`M_Code` SET `Class3`='0', `Class4`='4' WHERE `CodeId`='163' and`KeyCode`='4';
UPDATE `coraldb_test03`.`M_Code` SET `Class3`='0', `Class4`='8' WHERE `CodeId`='163' and`KeyCode`='6';
UPDATE `coraldb_test03`.`M_Code` SET `Class3`='0', `Class4`='9' WHERE `CodeId`='163' and`KeyCode`='7';
UPDATE `coraldb_test03`.`M_Code` SET `Class3`='0', `Class4`='10' WHERE `CodeId`='163' and`KeyCode`='8';
UPDATE `coraldb_test03`.`M_Code` SET `Class3`='0', `Class4`='11' WHERE `CodeId`='163' and`KeyCode`='9';
UPDATE `coraldb_test03`.`M_Code` SET `Class3`='0', `Class4`='12' WHERE `CodeId`='163' and`KeyCode`='10';
UPDATE `coraldb_test03`.`M_Code` SET `Class3`='0', `Class4`='13' WHERE `CodeId`='163' and`KeyCode`='11';
UPDATE `coraldb_test03`.`M_Code` SET `Class3`='0', `Class4`='14' WHERE `CodeId`='163' and`KeyCode`='13';
UPDATE `coraldb_test03`.`M_Code` SET `Class3`='0', `Class4`='15' WHERE `CodeId`='163' and`KeyCode`='14';
UPDATE `coraldb_test03`.`M_Code` SET `Class3`='1', `Class4`='16' WHERE `CodeId`='163' and`KeyCode`='15';
UPDATE `coraldb_test03`.`M_Code` SET `Class3`='1', `Class4`='17' WHERE `CodeId`='163' and`KeyCode`='16';
UPDATE `coraldb_test03`.`M_Code` SET `Class3`='1', `Class4`='18' WHERE `CodeId`='163' and`KeyCode`='17';
UPDATE `coraldb_test03`.`M_Code` SET `Class3`='1', `Class4`='19' WHERE `CodeId`='163' and`KeyCode`='18';
UPDATE `coraldb_test03`.`M_Code` SET `Class3`='1', `Class4`='20' WHERE `CodeId`='163' and`KeyCode`='19';
UPDATE `coraldb_test03`.`M_Code` SET `Class3`='1', `Class4`='6' WHERE `CodeId`='163' and`KeyCode`='20';
UPDATE `coraldb_test03`.`m_code` SET `KeyContent` = 'PayPay請求書払い' WHERE (`CodeId` = '163') and (`KeyCode` = '6');
UPDATE `coraldb_test03`.`m_code` SET `KeyContent` = 'LINE Pay請求書払い' WHERE (`CodeId` = '163') and (`KeyCode` = '4');

-- M_SbpsPayment
UPDATE `coraldb_test03`.`M_SbpsPayment` SET `PaymentName`='credit_vm' WHERE `SbpsPaymentId`='1';
UPDATE `coraldb_test03`.`M_SbpsPayment` SET `PaymentName`='credit_ja' WHERE `SbpsPaymentId`='2';
UPDATE `coraldb_test03`.`M_SbpsPayment` SET `PaymentName`='credit_d' WHERE `SbpsPaymentId`='3';

-- T_BatchLock
INSERT INTO `coraldb_test03`.`T_BatchLock` (`Seq`, `BatchId`, `ThreadNo`, `BatchName`, `BatchLock`, `UpdateDate`) VALUES ('6', '6', '1', '基幹連携バッチ', '0', '2022-04-21 21:10:02');

-- mypage
-- T_SbpsReceiptControl
ALTER TABLE T_SbpsReceiptControl ADD COLUMN `ValidFlg` TINYINT NOT NULL DEFAULT 1 AFTER `UpdateId`;
-- T_ReceiptIssueHistory
ALTER TABLE `coraldb_mypage01`.`T_ReceiptIssueHistory` 
ADD COLUMN `ValidFlg` INT(11) NOT NULL DEFAULT 1 AFTER `RegistId`;

ALTER TABLE `coraldb_new01`.`t_sbpsreceiptcontrol` 
ADD COLUMN `Seq` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST,
CHANGE COLUMN `OrderSeq` `OrderSeq` BIGINT(20) NOT NULL COMMENT '注文Seq' ,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`Seq`);
-- Insert purchased order to T_SbpsReceiptControl
REPLACE INTO T_SbpsReceiptControl(
OrderSeq
,PayType
,PaymentName
,ReceiptDate
,RegistDate
,RegistId
,UpdateDate
,UpdateId
,ValidFlg
)
SELECT
OrderSeq
,1
,"credit_vm"
,RegistDate
,now()
,1
,now()
,1
,1
FROM T_MypageToBackIF
WHERE IFClass = 4

-- PROCEDURE
-- P_ReceiptControl 
delimiter $$

CREATE DEFINER=`skip-grants user`@`skip-grants host` PROCEDURE `P_ReceiptControl`(  IN  pi_receipt_amount   BIGINT(20)
                                ,   IN  pi_order_seq        BIGINT(20)
                                ,   IN  pi_receipt_date     DATE
                                ,   IN  pi_receipt_class    INT
                                ,   IN  pi_branch_bank_id   BIGINT
                                ,   IN  pi_receipt_agent_id BIGINT
                                ,   IN  pi_deposit_date     DATE
                                ,   IN  pi_user_id          VARCHAR(20)
                                , 	IN pi_receipt_note 	TEXT
                                ,   OUT po_ret_sts          INT
                                ,   OUT po_ret_errcd        VARCHAR(100)
                                ,   OUT po_ret_sqlcd        INT
                                ,   OUT po_ret_msg          VARCHAR(255)
                                 )
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名   ：  P_ReceiptControl
 *
 * 概要         ：  入金関連処理
 *
 * 引数         ：  [I/ ]pi_receipt_amount                      入金額
 *              ：  [I/ ]pi_order_seq                           親注文Seq
 *              ：  [I/ ]pi_receipt_date                        入金日
 *              ：  [I/ ]pi_receipt_class                       入金形態
 *              ：  [I/ ]pi_branch_bank_id                      銀行支店ID
 *              ：  [I/ ]pi_receipt_agent_id                    収納代行ID
 *              ：  [I/ ]pi_deposit_date                        口座入金日
 *              ：  [I/ ]pi_user_id                             ﾕｰｻﾞｰID
 *              ：  [I/ ]pi_receipt_note                       備考
 *              ：  [ /O]po_ret_sts                             ﾘﾀｰﾝｽﾃｰﾀｽ
 *              ：  [ /O]po_ret_errcd                           ﾘﾀｰﾝｺｰﾄﾞ
 *              ：  [ /O]po_ret_sqlcd                           ﾘﾀｰﾝSQLｺｰﾄﾞ
 *              ：  [ /O]po_ret_msg                             ﾘﾀｰﾝﾒｯｾｰｼﾞ
 *
 * 履歴         ：  2015/05/13  NDC 新規作成
 *                  2016/02/04  NDC 少額入金の場合にﾛｼﾞｯｸが崩れるため、今回入金額の考慮を追加。
 *
 *****************************************************************************/
BEGIN
    -- ---------------------
    -- 変数宣言
    -- ---------------------
    -- 注文ﾃﾞｰﾀ取得用
    DECLARE v_OrderId                           VARCHAR(50) DEFAULT '';
    DECLARE v_DataStatus                        INT DEFAULT 0;
    DECLARE v_Cnt                               INT;
    DECLARE v_P_OrderSeq                        BIGINT(20) DEFAULT 0;

    -- 残高情報更新用
    DECLARE v_BalanceClaimAmount                BIGINT(20) DEFAULT 0;
    DECLARE v_BalanceUseAmount                  BIGINT(20) DEFAULT 0;
    DECLARE v_BalanceDamageInterestAmount       BIGINT(20) DEFAULT 0;
    DECLARE v_BalanceClaimFee                   BIGINT(20) DEFAULT 0;
    DECLARE v_BalanceAdditionalClaimFee         BIGINT(20) DEFAULT 0;
    -- 消込情報更新用
    DECLARE v_CheckingClaimAmount               BIGINT(20) DEFAULT 0;
    DECLARE v_CheckingUseAmount                 BIGINT(20) DEFAULT 0;
    DECLARE v_CheckingClaimFee                  BIGINT(20) DEFAULT 0;
    DECLARE v_CheckingDamageInterestAmount      BIGINT(20) DEFAULT 0;
    DECLARE v_CheckingAdditionalClaimFee        BIGINT(20) DEFAULT 0;
    -- 雑収入・雑損失更新用
    DECLARE v_SundryAmount                      BIGINT(20) DEFAULT 0;
    DECLARE v_SundryUseAmount                   BIGINT(20) DEFAULT 0;
    DECLARE v_SundryClaimFee                    BIGINT(20) DEFAULT 0;
    DECLARE v_SundryDamageInterestAmount        BIGINT(20) DEFAULT 0;
    DECLARE v_SundryAdditionalClaimFee          BIGINT(20) DEFAULT 0;
    -- 残金取得用
    DECLARE v_CalculationAmount                 BIGINT(20) DEFAULT 0;
    -- 差額取得用
    DECLARE v_diffClaimFee                      BIGINT(20) DEFAULT 0;
    DECLARE v_diffDamageInterestAmount          BIGINT(20) DEFAULT 0;
    DECLARE v_diffAdditionalClaimFee            BIGINT(20) DEFAULT 0;
    -- 入金ｸﾛｰｽﾞ後入金ﾃﾞｰﾀ作成用
    DECLARE v_InsReceiptAmount                  BIGINT(20) DEFAULT 0;
    DECLARE v_InsReceiptUseAmount               BIGINT(20) DEFAULT 0;
    DECLARE v_InsReceiptClaimFee                BIGINT(20) DEFAULT 0;
    DECLARE v_InsReceiptDamageInterestAmount    BIGINT(20) DEFAULT 0;
    DECLARE v_InsReceiptAdditionalClaimFee      BIGINT(20) DEFAULT 0;

    -- 請求ﾃﾞｰﾀ取得用
    DECLARE v_ClaimId                           BIGINT(20) DEFAULT 0;
    DECLARE v_ClaimPattern                      INT;
    DECLARE v_UseAmountTotal                    BIGINT(20) DEFAULT 0;
    DECLARE v_ClaimFee                          BIGINT(20) DEFAULT 0;
    DECLARE v_DamageInterestAmount              BIGINT(20) DEFAULT 0;
    DECLARE v_AdditionalClaimFee                BIGINT(20) DEFAULT 0;
    DECLARE v_ClaimAmount                       BIGINT(20) DEFAULT 0;
    DECLARE v_ClaimedBalance                    BIGINT(20) DEFAULT 0;
    DECLARE v_MinClaimAmount                    BIGINT(20) DEFAULT 0;
    DECLARE v_MinUseAmount                      BIGINT(20) DEFAULT 0;
    DECLARE v_MinClaimFee                       BIGINT(20) DEFAULT 0;
    DECLARE v_MinDamageInterestAmount           BIGINT(20) DEFAULT 0;
    DECLARE v_MinAdditionalClaimFee             BIGINT(20) DEFAULT 0;

    -- 入金ﾃﾞｰﾀ取得用
    DECLARE v_ReceiptAmount                     BIGINT(20) DEFAULT 0;
    DECLARE v_ReceiptUseAmount                  BIGINT(20) DEFAULT 0;
    DECLARE v_ReceiptClaimFee                   BIGINT(20) DEFAULT 0;
    DECLARE v_ReceiptDamageInterestAmount       BIGINT(20) DEFAULT 0;
    DECLARE v_ReceiptAdditionalClaimFee         BIGINT(20) DEFAULT 0;
    DECLARE v_ReceiptSeq                        BIGINT(20) DEFAULT 0;
    DECLARE v_OrderSeq                          BIGINT(20) DEFAULT 0;

    -- その他
    DECLARE no_data_found INT DEFAULT 1;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_data_found = 0;

    /* ********************* *
     * 処理開始
     * ********************* */
    -- ------------------------------
    -- 1.戻り値初期化
    -- ------------------------------
    SET po_ret_sts      =   0;
    SET po_ret_errcd    =   '';
    SET po_ret_sqlcd    =   0;
    SET po_ret_msg      =   '正常終了';

    -- ------------------------------
    -- 2.注文ﾃﾞｰﾀ取得
    -- ------------------------------
    -- 取りまとめられている場合の考慮
    -- 1件でも有効な注文ﾃﾞｰﾀが存在すれば、入金可能
    SELECT  COUNT(*)
    INTO    v_Cnt
    FROM    T_Order
    -- 未キャンセル かつ 入金確認待ちまたは一部入金
    -- 未キャンセル かつ 入金済み正常クローズ
    -- キャンセルクローズかつマイナス入金（返金）
    WHERE   ((Cnl_Status = 0 AND DataStatus IN (51, 61))
          OR (Cnl_Status = 0 AND DataStatus = 91 AND CloseReason = 1)
          OR (Cnl_Status > 0 AND DataStatus = 91 AND CloseReason = 2 AND pi_receipt_amount < 0)
            )
    AND     P_OrderSeq  =   pi_order_seq
    ;

    IF  v_Cnt = 0  THEN
        SET po_ret_sts  =   -1;
        SET po_ret_msg  =   '入金対象のデータが存在しません。';
        LEAVE proc;
    END IF;

    -- 親の注文IDを取得
    -- 親注文がｷｬﾝｾﾙされている場合を考慮してDataStatus等の条件は含まない
    SELECT  OrderId
    INTO    v_OrderId
    FROM    T_Order
    WHERE   OrderSeq    =   P_OrderSeq
    AND     P_OrderSeq  =   pi_order_seq
    ;

    -- 最小のﾃﾞｰﾀｽﾃｰﾀｽを取得
    -- 取りまとめられている場合を考慮してP_OrderSeqでｸﾞﾙｰﾌﾟ化した最小のﾃﾞｰﾀｽﾃｰﾀｽを取得する
    SELECT  P_OrderSeq
        ,   MIN(DataStatus)
    INTO    v_P_OrderSeq
        ,   v_DataStatus
    FROM    T_Order
    WHERE   (DataStatus IN (51, 61) OR (DataStatus = 91 AND CloseReason = 1))
    AND     P_OrderSeq  =   pi_order_seq
    GROUP BY
            P_OrderSeq
    ;

    -- ------------------------------
    -- 3.入金確認待ちの場合
    -- ------------------------------
    IF  v_DataStatus = 51   THEN
        -- ------------------------------
        -- 3-1.請求ﾃﾞｰﾀ取得
        -- ------------------------------
        SELECT  ClaimId                     -- 請求ID
            ,   ClaimPattern                -- 請求ﾊﾟﾀｰﾝ
            ,   ClaimAmount                 -- 請求額
            ,   UseAmountTotal              -- 利用額合計
            ,   ClaimFee                    -- 請求手数料
            ,   DamageInterestAmount        -- 遅延損害金
            ,   AdditionalClaimFee          -- 請求追加手数料
            ,   ClaimedBalance              -- 請求残高
            ,   MinClaimAmount              -- 最低請求情報－請求金額
            ,   MinUseAmount                -- 最低請求情報－利用額
            ,   MinClaimFee                 -- 最低請求情報－請求手数料
            ,   MinDamageInterestAmount     -- 最低請求情報－遅延損害金
            ,   MinAdditionalClaimFee       -- 最低請求情報－請求追加手数料
        INTO    v_ClaimId
            ,   v_ClaimPattern
            ,   v_ClaimAmount
            ,   v_UseAmountTotal
            ,   v_ClaimFee
            ,   v_DamageInterestAmount
            ,   v_AdditionalClaimFee
            ,   v_ClaimedBalance
            ,   v_MinClaimAmount
            ,   v_MinUseAmount
            ,   v_MinClaimFee
            ,   v_MinDamageInterestAmount
            ,   v_MinAdditionalClaimFee
        FROM    T_ClaimControl
        WHERE   OrderSeq    =   pi_order_seq
        ;

        IF  no_data_found = 0   THEN
            SET po_ret_sts  =   -1;
            SET po_ret_msg  =   '請求対象のデータが存在しません。';
            LEAVE proc;
        END IF;

        -- ------------------------------
        -- 3-2.消込情報を計算
        -- ------------------------------
        /* -------------------------------------------------------------------------------------------
        -- 2015/08/03_メモ
        -- 金額の消込方法
        -- 後いくら入金したらｸﾛｰｽﾞするか が請求残高となるため、FROM（最低請求情報）から消し込む
        --  つまり・・・
        --   最低請求額（手数料等）消し込み → 利用額消し込み → 最終請求額（手数料等）消し込み
        --  1.最低請求情報－請求追加手数料（MinAdditionalClaimFee）消し込み
        --  2.最低請求情報－遅延損害金（MinDamageInterestAmount）消し込み
        --  3.最低請求情報－請求手数料（MinClaimFee）消し込み
        --  4.利用額（UseAmountTotal）消し込み
        --  5.請求追加手数料（AdditionalClaimFee）の差額分消し込み
        --  6.遅延損害金（DamageInterestAmount）の差額分消し込み
        --  7.請求手数料（ClaimFee）の差額分消し込み
        -- ------------------------------------------------------------------------------------------- */
        -- 最終請求額と最低請求額の差額を取得
        -- 請求手数料
        SET v_diffClaimFee = v_ClaimFee - v_MinClaimFee;
        -- 遅延損害金
        SET v_diffDamageInterestAmount = v_DamageInterestAmount - v_MinDamageInterestAmount;
        -- 請求追加手数料
        SET v_diffAdditionalClaimFee = v_AdditionalClaimFee - v_MinAdditionalClaimFee;

        -- +++++++++++++++++++++++++++++++++++++++++++++
        -- 1. 最低請求情報－請求追加手数料 消し込み
        -- +++++++++++++++++++++++++++++++++++++++++++++
        -- 入金額 が 最低請求情報－請求追加手数料 以上の場合
        IF  pi_receipt_amount >= v_MinAdditionalClaimFee    THEN
            -- 消込情報－請求追加手数料 = 最低請求情報－請求追加手数料
            SET v_CheckingAdditionalClaimFee = v_MinAdditionalClaimFee;
            -- 入金額から消込情報－請求追加手数料を減算して入金額の残金を取得
            SET v_CalculationAmount = pi_receipt_amount - v_CheckingAdditionalClaimFee;
        ELSE
            -- 消込情報－請求追加手数料 = 入金額
            SET v_CheckingAdditionalClaimFee = pi_receipt_amount;
            -- 入金額が全て 消込情報－請求追加手数料 なので、残金は 0（ｾﾞﾛ）
            SET v_CalculationAmount = 0;
        END IF;

        -- 1.で残金が存在する場合は以下処理を行う。
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 2. 最低請求情報－遅延損害金 消し込み
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 1.の残金 が 最低請求情報－遅延損害金 以上の場合
            IF  v_CalculationAmount >= v_MinDamageInterestAmount    THEN
                -- 消込情報－遅延損害金 = 最低請求情報－遅延損害金
                SET v_CheckingDamageInterestAmount = v_MinDamageInterestAmount;
                -- 1.の残金から消込情報－遅延損害金を減算して入金額の残金を取得
                SET v_CalculationAmount = v_CalculationAmount - v_CheckingDamageInterestAmount;
            ELSE
                -- 消込情報－遅延損害金 = 残金
                SET v_CheckingDamageInterestAmount = v_CalculationAmount;
                -- 1.の残金が全て 消込情報－遅延損害金 なので、残金は 0（ｾﾞﾛ）
                SET v_CalculationAmount = 0;
            END IF;
        END IF;

        -- 2.で残金が存在する場合は以下処理を行う。
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 3. 最低請求情報－請求手数料 消し込み
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 2.の残金 が 最低請求情報－請求手数料 以上の場合
            IF  v_CalculationAmount >= v_MinClaimFee    THEN
                -- 消込情報－請求手数料 = 最低請求情報－請求手数料
                SET v_CheckingClaimFee = v_MinClaimFee;
                -- 2.の残金から消込情報－請求手数料を減算して入金額の残金を取得
                SET v_CalculationAmount = v_CalculationAmount - v_CheckingClaimFee;
            ELSE
                -- 消込情報－請求手数料 = 残金
                SET v_CheckingClaimFee = v_CalculationAmount;
                -- 2.の残金が全て 消込情報－請求手数料 なので、残金は 0（ｾﾞﾛ）
                SET v_CalculationAmount = 0;
            END IF;
        END IF;

        -- 3.で残金が存在する場合は以下処理を行う。
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 4. 利用額 消し込み
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 3.の残金 が 利用合計額 以上の場合
            IF  v_CalculationAmount >= v_UseAmountTotal THEN
                -- 消込情報－利用額 = 利用合計額
                SET v_CheckingUseAmount = v_UseAmountTotal;
                -- 3.の残金から消込情報－利用額を減算して入金額の残金を取得
                SET v_CalculationAmount = v_CalculationAmount - v_CheckingUseAmount;
            -- それ以外の場合
            ELSE
                -- 消込情報－利用額 = 残金
                SET v_CheckingUseAmount = v_CalculationAmount;
                -- 3.の残金が全て 消込情報－利用額 なので、残金は 0（ｾﾞﾛ）
                SET v_CalculationAmount = 0;
            END IF;
        END IF;

        -- 4.で残金が存在する場合は以下処理を行う。
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 5. 請求追加手数料の差額分 消し込み
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 4.の残金 が 請求追加手数料の差額分 以上の場合
            IF  v_CalculationAmount >= v_diffAdditionalClaimFee THEN
                -- 消込情報－請求追加手数料 に 請求追加手数料の差額 を足しこむ
                SET v_CheckingAdditionalClaimFee = v_CheckingAdditionalClaimFee + v_diffAdditionalClaimFee;
                -- 4.の残金から請求追加手数料の差額を減算して入金額の残金を取得
                SET v_CalculationAmount = v_CalculationAmount - v_diffAdditionalClaimFee;
            -- それ以外の場合
            ELSE
                -- 消込情報－請求追加手数料 に 4.の残金を足しこむ
                SET v_CheckingAdditionalClaimFee = v_CheckingAdditionalClaimFee + v_CalculationAmount;
                -- 4.の残金が全て 消込情報－請求追加手数料 なので、残金は 0（ｾﾞﾛ）
                SET v_CalculationAmount = 0;
            END IF;
        END IF;

        -- 5.で残金が存在する場合は以下処理を行う。
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 6. 遅延損害金の差額分 消し込み
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 5.の残金 が 遅延損害金の差額分 以上の場合
            IF  v_CalculationAmount >= v_diffDamageInterestAmount   THEN
                -- 消込情報－遅延損害金 に 遅延損害金の差額 を足しこむ
                SET v_CheckingDamageInterestAmount = v_CheckingDamageInterestAmount + v_diffDamageInterestAmount;
                -- 5.の残金から遅延損害金の差額を減算して入金額の残金を取得
                SET v_CalculationAmount = v_CalculationAmount - v_diffDamageInterestAmount;
            -- それ以外の場合
            ELSE
                -- 消込情報－遅延損害金 に 5.の残金を足しこむ
                SET v_CheckingDamageInterestAmount = v_CheckingDamageInterestAmount + v_CalculationAmount;
                -- 5.の残金が全て 消込情報－遅延損害金 なので、残金は 0（ｾﾞﾛ）
                SET v_CalculationAmount = 0;
            END IF;
        END IF;

        -- 6.で残金が存在する場合は以下処理を行う。
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 7. 請求手数料の差額分 消し込み
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 6.の残金 が 請求手数料の差額 以上の場合
            IF  v_CalculationAmount >= v_diffClaimFee   THEN
                -- 消込情報－請求手数料 に 6.の残金を足しこむ
                SET v_CheckingClaimFee = v_CheckingClaimFee + v_diffClaimFee;
                -- 6.の残金から請求手数料の差額を減算して入金額の残金を取得
                SET v_CalculationAmount = v_CalculationAmount - v_diffClaimFee;
            ELSE
                -- 消込情報－請求手数料 に 6.の残金を足しこむ
                SET v_CheckingClaimFee = v_CheckingClaimFee + v_CalculationAmount;
                -- 6.の残金が全て 消込情報－請求手数料 なので、残金は 0（ｾﾞﾛ）
                SET v_CalculationAmount = 0;
            END IF;
        END IF;

        -- 7.で残金が存在する場合は以下処理を行う。
        IF  v_CalculationAmount > 0 THEN
            -- 過剰金として 消込情報－利用額 に 7.の残金 を足しこむ
            SET v_CheckingUseAmount = v_CheckingUseAmount + v_CalculationAmount;
        END IF;

        -- ++++++++++++++++++++++++++++++++++++++++
        -- 8. 消込情報－消込金額合計 を求める
        -- ++++++++++++++++++++++++++++++++++++++++
        SET v_CheckingClaimAmount = v_CheckingUseAmount + v_CheckingClaimFee + v_CheckingDamageInterestAmount + v_CheckingAdditionalClaimFee;

        -- ------------------------------
        -- 3-3.残高情報を計算
        -- ------------------------------
        -- 残高情報は請求額から消込額を減算して取得する
        -- 1) 残高情報－残高合計
        SET v_BalanceClaimAmount = v_ClaimAmount - v_CheckingClaimAmount;

        -- 2) 残高情報－利用額
        SET v_BalanceUseAmount = v_UseAmountTotal - v_CheckingUseAmount;

        -- 3) 残高情報－請求手数料
        SET v_BalanceClaimFee = v_ClaimFee - v_CheckingClaimFee;

        -- 4) 残高情報－遅延損害金
        SET v_BalanceDamageInterestAmount = v_DamageInterestAmount - v_CheckingDamageInterestAmount;

        -- 5) 残高情報－請求追加手数料
        SET v_BalanceAdditionalClaimFee = v_AdditionalClaimFee - v_CheckingAdditionalClaimFee;

        -- ------------------------------
        -- 3-4.入金ﾃﾞｰﾀの作成
        -- ------------------------------
        INSERT
        INTO    T_ReceiptControl(   ReceiptProcessDate              -- 入金処理日
                                ,   ReceiptDate                     -- 顧客入金日
                                ,   ReceiptClass                    -- 入金科目（入金方法）
                                ,   ReceiptAmount                   -- 金額
                                ,   ClaimId                         -- 請求ID
                                ,   OrderSeq                        -- 注文SEQ
                                ,   CheckingUseAmount               -- 消込情報－利用額
                                ,   CheckingClaimFee                -- 消込情報－請求手数料
                                ,   CheckingDamageInterestAmount    -- 消込情報－遅延損害金
                                ,   CheckingAdditionalClaimFee      -- 消込情報－請求追加手数料
                                ,   DailySummaryFlg                 -- 日次更新ﾌﾗｸﾞ
                                ,   BranchBankId                    -- 銀行支店ID
                                ,   DepositDate                     -- 入金予定日
                                ,   ReceiptAgentId                  -- 収納代行会社ID
                                ,   Receipt_Note                     -- 備考
                                ,   RegistDate                      -- 登録日時
                                ,   RegistId                        -- 登録者
                                ,   UpdateDate                      -- 更新日時
                                ,   UpdateId                        -- 更新者
                                ,   ValidFlg                        -- 有効ﾌﾗｸﾞ
                                )
                                VALUES
                                (   NOW()                           -- 入金処理日
                                ,   pi_receipt_date                 -- 顧客入金日
                                ,   pi_receipt_class                -- 入金科目（入金方法）
                                ,   pi_receipt_amount               -- 金額
                                ,   v_ClaimId                       -- 請求ID
                                ,   pi_order_seq                    -- 注文SEQ
                                ,   v_CheckingUseAmount             -- 消込情報－利用額
                                ,   v_CheckingClaimFee              -- 消込情報－請求手数料
                                ,   v_CheckingDamageInterestAmount  -- 消込情報－遅延損害金
                                ,   v_CheckingAdditionalClaimFee    -- 消込情報－請求追加手数料
                                ,   0                               -- 日次更新ﾌﾗｸﾞ
                                ,   pi_branch_bank_id               -- 銀行支店ID
                                ,   pi_deposit_date                 -- 入金予定日
                                ,   pi_receipt_agent_id             -- 収納代行会社ID
                                ,   pi_receipt_note                    -- 備考
                                ,   NOW()                           -- 登録日時
                                ,   pi_user_id                      -- 登録者
                                ,   NOW()                           -- 更新日時
                                ,   pi_user_id                      -- 更新者
                                ,   1                               -- 有効ﾌﾗｸﾞ
                                );

        -- ------------------------------
        -- 3-4'.入金Seqを取得
        -- ------------------------------
        SELECT  OrderSeq
            ,   MAX(ReceiptSeq)
        INTO    v_OrderSeq
            ,   v_ReceiptSeq
        FROM    T_ReceiptControl
        WHERE   OrderSeq = pi_order_seq
        GROUP BY
                OrderSeq
        ;

        -- ------------------------------
        -- 3-5.最低請求額 > 入金額 の場合
        -- ------------------------------
        IF  v_MinClaimAmount > pi_receipt_amount    THEN
            -- ------------------------------
            -- 3-5-1.請求ﾃﾞｰﾀの更新
            -- ------------------------------
            UPDATE  T_ClaimControl
            SET     ClaimedBalance                  =   v_ClaimedBalance - v_CheckingClaimAmount        -- 請求残高
                ,   LastProcessDate                 =   DATE(NOW())                                     -- 最終入金処理日
                ,   LastReceiptSeq                  =   v_ReceiptSeq                                    -- 最終入金SEQ
                ,   CheckingClaimAmount             =   v_CheckingClaimAmount                           -- 消込情報－消込金額合計
                ,   CheckingUseAmount               =   v_CheckingUseAmount                             -- 消込情報－利用額
                ,   CheckingClaimFee                =   v_CheckingClaimFee                              -- 消込情報－請求手数料
                ,   CheckingDamageInterestAmount    =   v_CheckingDamageInterestAmount                  -- 消込情報－遅延損害金
                ,   CheckingAdditionalClaimFee      =   v_CheckingAdditionalClaimFee                    -- 消込情報－請求追加手数料
                ,   BalanceClaimAmount              =   v_BalanceClaimAmount                            -- 残高情報－残高合計
                ,   BalanceUseAmount                =   v_BalanceUseAmount                              -- 残高情報－利用額
                ,   BalanceClaimFee                 =   v_BalanceClaimFee                               -- 残高情報－請求手数料
                ,   BalanceDamageInterestAmount     =   v_BalanceDamageInterestAmount                   -- 残高情報－遅延損害金
                ,   BalanceAdditionalClaimFee       =   v_BalanceAdditionalClaimFee                     -- 残高情報－請求追加手数料
                ,   ReceiptAmountTotal              =   ReceiptAmountTotal + pi_receipt_amount          -- 入金額合計
                ,   UpdateDate                      =   NOW()
                ,   UpdateId                        =   pi_user_id
            WHERE   ClaimId =   v_ClaimId
            ;

            -- ------------------------------
            -- 3-5-2.注文ﾃﾞｰﾀの更新
            -- ------------------------------
            UPDATE  T_Order
            SET     DataStatus  =   61              -- ﾃﾞｰﾀｽﾃｰﾀｽ（一部入金）
                ,   Rct_Status  =   1               -- 顧客入金ｽﾃｰﾀｽ（入金済み）
                ,   UpdateDate  =   NOW()
                ,   UpdateId    =   pi_user_id
            WHERE   P_OrderSeq  =   pi_order_seq
            AND     Cnl_Status  =   0
            ;
        -- ------------------------------
        -- 3-6.最低請求額 = 入金額 の場合
        -- ------------------------------
        ELSEIF  v_MinClaimAmount = pi_receipt_amount    THEN
            -- ------------------------------
            -- 3-6-1.最低請求額 = 最終請求額 の場合
            -- ------------------------------
            IF  v_MinClaimAmount = v_ClaimAmount    THEN
                -- ------------------------------
                -- 3-6-1-1.請求ﾃﾞｰﾀの更新
                -- ------------------------------
                UPDATE  T_ClaimControl
                SET     ClaimedBalance                  =   v_ClaimedBalance - v_CheckingClaimAmount        -- 請求残高
                    ,   LastProcessDate                 =   DATE(NOW())                                     -- 最終入金処理日
                    ,   LastReceiptSeq                  =   v_ReceiptSeq                                    -- 最終入金SEQ
                    ,   CheckingClaimAmount             =   v_CheckingClaimAmount                           -- 消込情報－消込金額合計
                    ,   CheckingUseAmount               =   v_CheckingUseAmount                             -- 消込情報－利用額
                    ,   CheckingClaimFee                =   v_CheckingClaimFee                              -- 消込情報－請求手数料
                    ,   CheckingDamageInterestAmount    =   v_CheckingDamageInterestAmount                  -- 消込情報－遅延損害金
                    ,   CheckingAdditionalClaimFee      =   v_CheckingAdditionalClaimFee                    -- 消込情報－請求追加手数料
                    ,   BalanceClaimAmount              =   v_BalanceClaimAmount                            -- 残高情報－残高合計
                    ,   BalanceUseAmount                =   v_BalanceUseAmount                              -- 残高情報－利用額
                    ,   BalanceClaimFee                 =   v_BalanceClaimFee                               -- 残高情報－請求手数料
                    ,   BalanceDamageInterestAmount     =   v_BalanceDamageInterestAmount                   -- 残高情報－遅延損害金
                    ,   BalanceAdditionalClaimFee       =   v_BalanceAdditionalClaimFee                     -- 残高情報－請求追加手数料
                    ,   ReceiptAmountTotal              =   ReceiptAmountTotal + pi_receipt_amount          -- 入金額合計
                    ,   UpdateDate                      =   NOW()
                    ,   UpdateId                        =   pi_user_id
                WHERE   ClaimId =   v_ClaimId
                ;

            -- ------------------------------
            -- 3-6-2.最終請求額 > 最低請求額 の場合
            -- ------------------------------
            ELSEIF  v_ClaimAmount > v_MinClaimAmount    THEN
                -- ------------------------------
                -- 3-6-2-1.雑損失情報の取得
                -- ------------------------------
                -- 最終請求額から消込金額を減算して差分を算出する
                -- 1) 利用額
                SET v_SundryUseAmount = v_UseAmountTotal - v_CheckingUseAmount;

                -- 2) 請求手数料
                SET v_SundryClaimFee = v_ClaimFee - v_CheckingClaimFee;

                -- 3) 遅延損害金
                SET v_SundryDamageInterestAmount = v_DamageInterestAmount - v_CheckingDamageInterestAmount;

                -- 4) 請求追加手数料
                SET v_SundryAdditionalClaimFee = v_AdditionalClaimFee - v_CheckingAdditionalClaimFee;

                -- 5) 金額
                SET v_SundryAmount = v_SundryUseAmount + v_SundryClaimFee + v_SundryDamageInterestAmount + v_SundryAdditionalClaimFee;

                -- ------------------------------
                -- 3-6-2-2.雑損失ﾃﾞｰﾀの作成
                -- ------------------------------
                INSERT
                INTO    T_SundryControl(    ProcessDate                     -- 発生日時
                                        ,   SundryType                      -- 種類（雑収入／雑損失）
                                        ,   SundryAmount                    -- 金額
                                        ,   SundryClass                     -- 雑収入・雑損失科目
                                        ,   OrderSeq                        -- 注文SEQ
                                        ,   OrderId                         -- 注文ID
                                        ,   ClaimId                         -- 請求ID
                                        ,   Note                            -- 備考
                                        ,   CheckingUseAmount               -- 消込情報－利用額
                                        ,   CheckingClaimFee                -- 消込情報－請求手数料
                                        ,   CheckingDamageInterestAmount    -- 消込情報－遅延損害金
                                        ,   CheckingAdditionalClaimFee      -- 消込情報－請求追加手数料
                                        ,   DailySummaryFlg                 -- 日次更新ﾌﾗｸﾞ
                                        ,   RegistDate                      -- 登録日時
                                        ,   RegistId                        -- 登録者
                                        ,   UpdateDate                      -- 更新日時
                                        ,   UpdateId                        -- 更新者
                                        ,   ValidFlg                        -- 有効ﾌﾗｸﾞ
                                       )
                                       VALUES
                                       (    DATE(NOW())                     -- 発生日時
                                        ,   1                               -- 種類（雑収入／雑損失）
                                        ,   v_SundryAmount                  -- 金額
                                        ,   99                              -- 雑収入・雑損失科目
                                        ,   pi_order_seq                    -- 注文SEQ
                                        ,   v_OrderId                       -- 注文ID
                                        ,   v_ClaimId                       -- 請求ID
                                        ,   NULL                            -- 備考
                                        ,   v_SundryUseAmount               -- 消込情報－利用額
                                        ,   v_SundryClaimFee                -- 消込情報－請求手数料
                                        ,   v_SundryDamageInterestAmount    -- 消込情報－遅延損害金
                                        ,   v_SundryAdditionalClaimFee      -- 消込情報－請求追加手数料
                                        ,   0                               -- 日次更新ﾌﾗｸﾞ
                                        ,   NOW()                           -- 登録日時
                                        ,   pi_user_id                      -- 登録者
                                        ,   NOW()                           -- 更新日時
                                        ,   pi_user_id                      -- 更新者
                                        ,   1                               -- 有効ﾌﾗｸﾞ
                                       );

                -- ------------------------------
                -- 3-6-2-3.請求ﾃﾞｰﾀの更新
                -- ------------------------------
                UPDATE  T_ClaimControl
                SET     ClaimedBalance                  =   v_ClaimedBalance - v_CheckingClaimAmount - v_SundryAmount           -- 請求残高
                    ,   LastProcessDate                 =   DATE(NOW())                                                         -- 最終入金処理日
                    ,   LastReceiptSeq                  =   v_ReceiptSeq                                                        -- 最終入金SEQ
                    ,   CheckingClaimAmount             =   v_CheckingClaimAmount + v_SundryAmount                              -- 消込情報－消込金額合計
                    ,   CheckingUseAmount               =   v_CheckingUseAmount + v_SundryUseAmount                             -- 消込情報－利用額
                    ,   CheckingClaimFee                =   v_CheckingClaimFee + v_SundryClaimFee                               -- 消込情報－請求手数料
                    ,   CheckingDamageInterestAmount    =   v_CheckingDamageInterestAmount + v_SundryDamageInterestAmount       -- 消込情報－遅延損害金
                    ,   CheckingAdditionalClaimFee      =   v_CheckingAdditionalClaimFee + v_SundryAdditionalClaimFee           -- 消込情報－請求追加手数料
                    ,   BalanceClaimAmount              =   v_BalanceClaimAmount - v_SundryAmount                               -- 残高情報－残高合計
                    ,   BalanceUseAmount                =   v_BalanceUseAmount - v_SundryUseAmount                              -- 残高情報－利用額
                    ,   BalanceClaimFee                 =   v_BalanceClaimFee - v_SundryClaimFee                                -- 残高情報－請求手数料
                    ,   BalanceDamageInterestAmount     =   v_BalanceDamageInterestAmount - v_SundryDamageInterestAmount        -- 残高情報－遅延損害金
                    ,   BalanceAdditionalClaimFee       =   v_BalanceAdditionalClaimFee - v_SundryAdditionalClaimFee            -- 残高情報－請求追加手数料
                    ,   ReceiptAmountTotal              =   ReceiptAmountTotal + pi_receipt_amount                              -- 入金額合計
                    ,   SundryLossTotal                 =   SundryLossTotal + v_SundryAmount                                    -- 雑損失合計
                    ,   UpdateDate                      =   NOW()
                    ,   UpdateId                        =   pi_user_id
                WHERE   ClaimId =   v_ClaimId
                ;
            END IF;

            -- ------------------------------
            -- 3-6-3.注文ﾃﾞｰﾀの更新
            -- ------------------------------
            UPDATE  T_Order
            SET     DataStatus  =   91      -- ﾃﾞｰﾀｽﾃｰﾀｽ（ｸﾛｰｽﾞ）
                ,   CloseReason =   1       -- ｸﾛｰｽﾞ理由（入金済み正常ｸﾛｰｽﾞ）
                ,   Rct_Status  =   1       -- 顧客入金ｽﾃｰﾀｽ（入金済み）
                ,   UpdateDate  =   NOW()
                ,   UpdateId    =   pi_user_id
            WHERE   P_OrderSeq  =   pi_order_seq
            AND     Cnl_Status  =   0
            ;
        -- ------------------------------
        -- 3-7.最終請求額 > 入金額 > 最低請求額 の場合
        -- ------------------------------
        ELSEIF  v_ClaimAmount > pi_receipt_amount AND pi_receipt_amount > v_MinClaimAmount  THEN
            -- ------------------------------
            -- 3-7-1.雑損失情報の取得
            -- ------------------------------
            -- 最終請求金額から消込金額を減算して差分を算出
            -- 1) 利用額
            SET v_SundryUseAmount = v_UseAmountTotal - v_CheckingUseAmount;

            -- 2) 請求手数料
            SET v_SundryClaimFee = v_ClaimFee - v_CheckingClaimFee;

            -- 3) 遅延損害金
            SET v_SundryDamageInterestAmount = v_DamageInterestAmount - v_CheckingDamageInterestAmount;

            -- 4) 請求追加手数料
            SET v_SundryAdditionalClaimFee = v_AdditionalClaimFee - v_CheckingAdditionalClaimFee;

            -- 5) 金額
            SET v_SundryAmount = v_SundryUseAmount + v_SundryClaimFee + v_SundryDamageInterestAmount + v_SundryAdditionalClaimFee;

            -- ------------------------------
            -- 3-7-2.雑損失ﾃﾞｰﾀの作成
            -- ------------------------------
            INSERT
            INTO    T_SundryControl(    ProcessDate                     -- 発生日時
                                    ,   SundryType                      -- 種類（雑収入／雑損失）
                                    ,   SundryAmount                    -- 金額
                                    ,   SundryClass                     -- 雑収入・雑損失科目
                                    ,   OrderSeq                        -- 注文SEQ
                                    ,   OrderId                         -- 注文ID
                                    ,   ClaimId                         -- 請求ID
                                    ,   Note                            -- 備考
                                    ,   CheckingUseAmount               -- 消込情報－利用額
                                    ,   CheckingClaimFee                -- 消込情報－請求手数料
                                    ,   CheckingDamageInterestAmount    -- 消込情報－遅延損害金
                                    ,   CheckingAdditionalClaimFee      -- 消込情報－請求追加手数料
                                    ,   DailySummaryFlg                 -- 日次更新ﾌﾗｸﾞ
                                    ,   RegistDate                      -- 登録日時
                                    ,   RegistId                        -- 登録者
                                    ,   UpdateDate                      -- 更新日時
                                    ,   UpdateId                        -- 更新者
                                    ,   ValidFlg                        -- 有効ﾌﾗｸﾞ
                                   )
                                   VALUES
                                   (    DATE(NOW())                     -- 発生日時
                                    ,   1                               -- 種類（雑収入／雑損失）
                                    ,   v_SundryAmount                  -- 金額
                                    ,   99                              -- 雑収入・雑損失科目
                                    ,   pi_order_seq                    -- 注文SEQ
                                    ,   v_OrderId                       -- 注文ID
                                    ,   v_ClaimId                       -- 請求ID
                                    ,   NULL                            -- 備考
                                    ,   v_SundryUseAmount               -- 消込情報－利用額
                                    ,   v_SundryClaimFee                -- 消込情報－請求手数料
                                    ,   v_SundryDamageInterestAmount    -- 消込情報－遅延損害金
                                    ,   v_SundryAdditionalClaimFee      -- 消込情報－請求追加手数料
                                    ,   0                               -- 日次更新ﾌﾗｸﾞ
                                    ,   NOW()                           -- 登録日時
                                    ,   pi_user_id                      -- 登録者
                                    ,   NOW()                           -- 更新日時
                                    ,   pi_user_id                      -- 更新者
                                    ,   1                               -- 有効ﾌﾗｸﾞ
                                   );

            -- ------------------------------
            -- 3-7-3.請求ﾃﾞｰﾀの更新
            -- ------------------------------
            UPDATE  T_ClaimControl
            SET     ClaimedBalance                  =   v_ClaimedBalance - v_CheckingClaimAmount - v_SundryAmount           -- 請求残高
                ,   LastProcessDate                 =   DATE(NOW())                                                         -- 最終入金処理日
                ,   LastReceiptSeq                  =   v_ReceiptSeq                                                        -- 最終入金SEQ
                ,   CheckingClaimAmount             =   v_CheckingClaimAmount + v_SundryAmount                              -- 消込情報－消込金額合計
                ,   CheckingUseAmount               =   v_CheckingUseAmount + v_SundryUseAmount                             -- 消込情報－利用額
                ,   CheckingClaimFee                =   v_CheckingClaimFee + v_SundryClaimFee                               -- 消込情報－請求手数料
                ,   CheckingDamageInterestAmount    =   v_CheckingDamageInterestAmount + v_SundryDamageInterestAmount       -- 消込情報－遅延損害金
                ,   CheckingAdditionalClaimFee      =   v_CheckingAdditionalClaimFee + v_SundryAdditionalClaimFee           -- 消込情報－請求追加手数料
                ,   BalanceClaimAmount              =   v_BalanceClaimAmount - v_SundryAmount                               -- 残高情報－残高合計
                ,   BalanceUseAmount                =   v_BalanceUseAmount - v_SundryUseAmount                              -- 残高情報－利用額
                ,   BalanceClaimFee                 =   v_BalanceClaimFee - v_SundryClaimFee                                -- 残高情報－請求手数料
                ,   BalanceDamageInterestAmount     =   v_BalanceDamageInterestAmount - v_SundryDamageInterestAmount        -- 残高情報－遅延損害金
                ,   BalanceAdditionalClaimFee       =   v_BalanceAdditionalClaimFee - v_SundryAdditionalClaimFee            -- 残高情報－請求追加手数料
                ,   ReceiptAmountTotal              =   ReceiptAmountTotal + pi_receipt_amount                              -- 入金額合計
                ,   SundryLossTotal                 =   SundryLossTotal + v_SundryAmount                                    -- 雑損失合計
                ,   UpdateDate                      =   NOW()
                ,   UpdateId                        =   pi_user_id
            WHERE   ClaimId =   v_ClaimId
            ;

            -- ------------------------------
            -- 3-7-4.注文ﾃﾞｰﾀの更新
            -- ------------------------------
            UPDATE  T_Order
            SET     DataStatus  =   91      -- ﾃﾞｰﾀｽﾃｰﾀｽ（ｸﾛｰｽﾞ）
                ,   CloseReason =   1       -- ｸﾛｰｽﾞ理由（入金済み正常ｸﾛｰｽﾞ）
                ,   Rct_Status  =   1       -- 顧客入金ｽﾃｰﾀｽ（入金済み）
                ,   UpdateDate  =   NOW()
                ,   UpdateId    =   pi_user_id
            WHERE   P_OrderSeq  =   pi_order_seq
            AND     Cnl_Status  =   0
            ;

        -- ------------------------------
        -- 3-8.入金額 >= 最終請求額 の場合
        -- ------------------------------
        ELSEIF  pi_receipt_amount >= v_ClaimAmount  THEN
            -- ------------------------------
            -- 3-8-1.請求ﾃﾞｰﾀの更新
            -- ------------------------------
            UPDATE  T_ClaimControl
            SET     ClaimedBalance                  =   v_ClaimedBalance - v_CheckingClaimAmount    -- 請求残高
                ,   LastProcessDate                 =   DATE(NOW())                                 -- 最終入金処理日
                ,   LastReceiptSeq                  =   v_ReceiptSeq                                -- 最終入金SEQ
                ,   CheckingClaimAmount             =   v_CheckingClaimAmount                       -- 消込情報－消込額合計
                ,   CheckingUseAmount               =   v_CheckingUseAmount                         -- 消込情報－利用額
                ,   CheckingClaimFee                =   v_CheckingClaimFee                          -- 消込情報－請求手数料
                ,   CheckingDamageInterestAmount    =   v_CheckingDamageInterestAmount              -- 消込情報－遅延損害金
                ,   CheckingAdditionalClaimFee      =   v_CheckingAdditionalClaimFee                -- 消込情報－請求追加手数料
                ,   BalanceClaimAmount              =   v_BalanceClaimAmount                        -- 残高情報－残高合計
                ,   BalanceUseAmount                =   v_BalanceUseAmount                          -- 残高情報－利用額
                ,   BalanceClaimFee                 =   v_BalanceClaimFee                           -- 残高情報－請求手数料
                ,   BalanceDamageInterestAmount     =   v_BalanceDamageInterestAmount               -- 残高情報－遅延損害金
                ,   BalanceAdditionalClaimFee       =   v_BalanceAdditionalClaimFee                 -- 残高情報－請求追加手数料
                ,   ReceiptAmountTotal              =   ReceiptAmountTotal + pi_receipt_amount      -- 入金済額
                ,   UpdateDate                      =   NOW()
                ,   UpdateId                        =   pi_user_id
            WHERE   ClaimId =   v_ClaimId
            ;

            -- --------------------------
            -- 3-8-2.注文ﾃﾞｰﾀの更新
            -- --------------------------
            UPDATE  T_Order
            SET     DataStatus  =   91      -- ﾃﾞｰﾀｽﾃｰﾀｽ（ｸﾛｰｽﾞ）
                ,   CloseReason =   1       -- ｸﾛｰｽﾞ理由（入金済み正常ｸﾛｰｽﾞ）
                ,   Rct_Status  =   1       -- 顧客入金ｽﾃｰﾀｽ（入金済み）
                ,   UpdateDate  =   NOW()
                ,   UpdateId    =   pi_user_id
            WHERE   P_OrderSeq  =   pi_order_seq
            AND     Cnl_Status  =   0
            ;
        END IF;

    -- ------------------------------
    -- 4.一部入金の場合
    -- ------------------------------
    ELSEIF  v_DataStatus = 61   THEN
        -- ------------------------------
        -- 4-1.入金済みのﾃﾞｰﾀを取得
        -- ------------------------------
        SELECT  SUM(ReceiptAmount)                   -- 金額
            ,   SUM(CheckingUseAmount)               -- 消込情報－利用額
            ,   SUM(CheckingClaimFee)                -- 消込情報－請求手数料
            ,   SUM(CheckingDamageInterestAmount)    -- 消込情報－遅延損害金
            ,   SUM(CheckingAdditionalClaimFee)      -- 消込情報－請求追加手数料
        INTO    v_ReceiptAmount
            ,   v_ReceiptUseAmount
            ,   v_ReceiptClaimFee
            ,   v_ReceiptDamageInterestAmount
            ,   v_ReceiptAdditionalClaimFee
        FROM    T_ReceiptControl
        WHERE   OrderSeq    =   pi_order_seq
        ;

        IF  no_data_found = 0   THEN
            SET po_ret_sts  =   -1;
            SET po_ret_msg  =   '入金済みのデータが存在しません。';
            LEAVE proc;
        END IF;

        -- ------------------------------
        -- 4-2.請求ﾃﾞｰﾀ取得
        -- ------------------------------
        SELECT  ClaimId                         -- 請求ID
            ,   ClaimAmount                     -- 請求額
            ,   UseAmountTotal                  -- 利用額合計
            ,   ClaimFee                        -- 請求手数料
            ,   DamageInterestAmount            -- 遅延損害金
            ,   AdditionalClaimFee              -- 請求追加手数料
            ,   ClaimedBalance                  -- 請求残高
            ,   MinClaimAmount                  -- 最低請求情報－請求金額
            ,   MinUseAmount                    -- 最低請求情報－利用額
            ,   MinClaimFee                     -- 最低請求情報－請求手数料
            ,   MinDamageInterestAmount         -- 最低請求情報－遅延損害金
            ,   MinAdditionalClaimFee           -- 最低請求情報－請求追加手数料
        INTO    v_ClaimId
            ,   v_ClaimAmount
            ,   v_UseAmountTotal
            ,   v_ClaimFee
            ,   v_DamageInterestAmount
            ,   v_AdditionalClaimFee
            ,   v_ClaimedBalance
            ,   v_MinClaimAmount
            ,   v_MinUseAmount
            ,   v_MinClaimFee
            ,   v_MinDamageInterestAmount
            ,   v_MinAdditionalClaimFee
        FROM    T_ClaimControl
        WHERE   OrderSeq    =   pi_order_seq
        ;

        IF  no_data_found = 0   THEN
            SET po_ret_sts  =   -1;
            SET po_ret_msg  =   '請求対象のデータが存在しません。';
            LEAVE proc;
        END IF;

        -- ------------------------------
        -- 4-3.消込情報を計算
        -- ------------------------------
        /* -------------------------------------------------------------------------------------------
        -- 2015/08/04_メモ
        -- 金額の消込方法
        --  一部入金の場合、入金済みの金額が存在するため、消込方法に注意が必要。
        --  後いくら入金したらｸﾛｰｽﾞするか の条件は入金確認待ちのときと変更はなし。
        --   つまり・・・入金済み額を考慮しつつ、FROM（最低請求情報）から消し込む、が正しい。
        --    最低請求額（手数料等）消し込み → 利用額消し込み → 最終請求額（手数料等）消し込み
        --  1.最低請求情報－請求追加手数料（MinAdditionalClaimFee）消し込み
        --  2.最低請求情報－遅延損害金（MinDamageInterestAmount）消し込み
        --  3.最低請求情報－請求手数料（MinClaimFee）消し込み
        --  4.利用額（UseAmountTotal）消し込み
        --  5.請求追加手数料（AdditionalClaimFee）の差額分消し込み
        --  6.遅延損害金（DamageInterestAmount）の差額分消し込み
        --  7.請求手数料（ClaimFee）の差額分消し込み
        -- 2016/02/04_追記
        --  少額入金（最低請求情報以下の入金）の場合、今のﾛｼﾞｯｸでは残高情報がおかしくなる。
        --   → 入金済みの金額以外に、今回入金額も消込金額の判定条件とする必要あり。
        -- ------------------------------------------------------------------------------------------- */
        -- 最終請求額と最低請求額の差額を取得
        -- 請求手数料
        SET v_diffClaimFee = v_ClaimFee - v_MinClaimFee;
        -- 遅延損害金
        SET v_diffDamageInterestAmount = v_DamageInterestAmount - v_MinDamageInterestAmount;
        -- 請求追加手数料
        SET v_diffAdditionalClaimFee = v_AdditionalClaimFee - v_MinAdditionalClaimFee;

        -- +++++++++++++++++++++++++++++++++++++++++++++
        -- 1. 最低請求情報－請求追加手数料 消し込み
        -- +++++++++++++++++++++++++++++++++++++++++++++
        -- 入金済みの請求追加手数料 が 最低請求情報－請求追加手数料 以上の場合
        IF  v_ReceiptAdditionalClaimFee >= v_MinAdditionalClaimFee  THEN
            -- 消込情報－請求追加手数料に対する追加消込はなし
            SET v_CheckingAdditionalClaimFee = 0;
            -- 残金は入金額
            SET v_CalculationAmount = pi_receipt_amount;
        -- それ以外の場合
        ELSE
            -- 入金額 が 最低請求情報－請求追加手数料 から 入金済みの請求追加手数料を減算した結果 以上の場合
            IF  pi_receipt_amount >= v_MinAdditionalClaimFee - v_ReceiptAdditionalClaimFee  THEN
                -- 最低請求情報－請求追加手数料 から 入金済みの請求追加手数料を減算して消込情報－請求追加手数料 を算出
                SET v_CheckingAdditionalClaimFee = v_MinAdditionalClaimFee - v_ReceiptAdditionalClaimFee;
            ELSE
                -- 消込情報－請求追加手数料 は 入金額
                SET v_CheckingAdditionalClaimFee = pi_receipt_amount;
            END IF;
            -- 入金額 から 消込情報－請求追加手数料を減算して残金を取得
            SET v_CalculationAmount = pi_receipt_amount - v_CheckingAdditionalClaimFee;
        END IF;

        -- 1.で残金が存在する場合は以下処理を行う。
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 2. 最低請求情報－遅延損害金 消し込み
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 入金済みの遅延損害金 が 最低請求情報－遅延損害金 以上の場合
            IF  v_ReceiptDamageInterestAmount >= v_MinDamageInterestAmount  THEN
                -- 消込情報－遅延損害金に対する追加消込はなし
                SET v_CheckingDamageInterestAmount = 0;
                -- 残金は1.の残金
                SET v_CalculationAmount = v_CalculationAmount;
            -- それ以外の場合
            ELSE
                -- 1.の残金 が 最低請求情報－遅延損害金 から 入金済みの遅延損害金を減算した結果 以上の場合
                IF  v_CalculationAmount >= v_MinDamageInterestAmount - v_ReceiptDamageInterestAmount    THEN
                    -- 最低請求情報－遅延損害金 から 入金済みの遅延損害金を減算して 消込情報－遅延損害金 を算出
                    SET v_CheckingDamageInterestAmount = v_MinDamageInterestAmount - v_ReceiptDamageInterestAmount;
                ELSE
                    -- 消込情報－遅延損害金 は 1.の残金
                    SET v_CheckingDamageInterestAmount = v_CalculationAmount;
                END IF;
                -- 1.の残金から消込情報－遅延損害金を減算して入金額の残金を取得
                SET v_CalculationAmount = v_CalculationAmount - v_CheckingDamageInterestAmount;
            END IF;
        END IF;

        -- 2.で残金が存在する場合は以下処理を行う。
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 3. 最低請求情報－請求手数料 消し込み
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 入金済みの請求手数料 が 最低請求情報－請求手数料 以上の場合
            IF  v_ReceiptClaimFee >= v_MinClaimFee  THEN
                -- 消込情報－請求手数料に対する追加消込はなし
                SET v_CheckingClaimFee = 0;
                -- 残金は2.の残金
                SET v_CalculationAmount = v_CalculationAmount;
            -- それ以外の場合
            ELSE
                -- 2.の残金 が 最低請求情報－請求手数料 から 入金済みの請求手数料を減算した結果 以上の場合
                IF  v_CalculationAmount >= v_MinClaimFee  - v_ReceiptClaimFee   THEN
                    -- 最低請求情報－請求手数料 から 入金済みの請求手数料を減算して 消込情報－請求手数料 を算出
                    SET v_CheckingClaimFee = v_MinClaimFee - v_ReceiptClaimFee;
                ELSE
                    -- 消込情報－請求手数料 は 2.の残金
                    SET v_CheckingClaimFee = v_CalculationAmount;
                END IF;
                -- 2.の残金 から消込情報－請求手数料を減算して入金額の残金を取得
                SET v_CalculationAmount = v_CalculationAmount - v_CheckingClaimFee;
            END IF;
        END IF;

        -- 3.で残金が存在する場合は以下処理を行う。
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 4. 利用額 消し込み
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 3.の残金 が 利用額合計と入金済みの利用額の差分 以上の場合
            IF  v_CalculationAmount >= v_UseAmountTotal - v_ReceiptUseAmount    THEN
                -- 消込情報－利用額 は 利用額合計と入金済みの利用額の差分
                SET v_CheckingUseAmount = v_UseAmountTotal - v_ReceiptUseAmount;
                -- 3.の残金 から消込情報－利用額を減算して入金額の残金を取得
                SET v_CalculationAmount = v_CalculationAmount - v_CheckingUseAmount;
            -- それ以外の場合
            ELSE
                -- 消込情報－利用額 は 3.の残金
                SET v_CheckingUseAmount = v_CalculationAmount;
                -- 3.の残金が全て 消込情報－利用額 なので、残金は 0（ｾﾞﾛ）
                SET v_CalculationAmount = 0;
            END IF;
        END IF;

        -- 4.で残金が存在する場合は以下処理を行う。
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 5. 請求追加手数料の差額分 消し込み
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 4.の残金 が 請求追加手数料の差額分 以上の場合
            IF  v_CalculationAmount >= v_diffAdditionalClaimFee THEN
                -- 消込情報－請求追加手数料 に 請求追加手数料の差額 を足しこむ
                SET v_CheckingAdditionalClaimFee = v_CheckingAdditionalClaimFee + v_diffAdditionalClaimFee;
                -- 4.の残金から請求追加手数料の差額を減算して入金額の残金を取得
                SET v_CalculationAmount = v_CalculationAmount - v_diffAdditionalClaimFee;
            -- それ以外の場合
            ELSE
                -- 消込情報－請求追加手数料 に 4.の残金を足しこむ
                SET v_CheckingAdditionalClaimFee = v_CheckingAdditionalClaimFee + v_CalculationAmount;
                -- 4.の残金が全て 消込情報－請求追加手数料 なので、残金は 0（ｾﾞﾛ）
                SET v_CalculationAmount = 0;
            END IF;
        END IF;

        -- 5.で残金が存在する場合は以下処理を行う。
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 6. 遅延損害金の差額分 消し込み
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 5.の残金 が 遅延損害金の差額分 以上の場合
            IF  v_CalculationAmount >= v_diffDamageInterestAmount   THEN
                -- 消込情報－遅延損害金 に 遅延損害金の差額 を足しこむ
                SET v_CheckingDamageInterestAmount = v_CheckingDamageInterestAmount + v_diffDamageInterestAmount;
                -- 5.の残金から遅延損害金の差額を減算して入金額の残金を取得
                SET v_CalculationAmount = v_CalculationAmount - v_diffDamageInterestAmount;
            -- それ以外の場合
            ELSE
                -- 消込情報－遅延損害金 に 5.の残金を足しこむ
                SET v_CheckingDamageInterestAmount = v_CheckingDamageInterestAmount + v_CalculationAmount;
                -- 5.の残金が全て 消込情報－遅延損害金 なので、残金は 0（ｾﾞﾛ）
                SET v_CalculationAmount = 0;
            END IF;
        END IF;

        -- 6.で残金が存在する場合は以下処理を行う。
        IF  v_CalculationAmount > 0 THEN
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 7. 請求手数料の差額分 消し込み
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 6.の残金 が 請求手数料の差額 以上の場合
            IF  v_CalculationAmount >= v_diffClaimFee   THEN
                -- 消込情報－請求手数料 に 6.の残金を足しこむ
                SET v_CheckingClaimFee = v_CheckingClaimFee + v_diffClaimFee;
                -- 6.の残金から請求手数料の差額を減算して入金額の残金を取得
                SET v_CalculationAmount = v_CalculationAmount - v_diffClaimFee;
            ELSE
                -- 消込情報－請求手数料 に 6.の残金を足しこむ
                SET v_CheckingClaimFee = v_CheckingClaimFee + v_CalculationAmount;
                -- 6.の残金が全て 消込情報－請求手数料 なので、残金は 0（ｾﾞﾛ）
                SET v_CalculationAmount = 0;
            END IF;
        END IF;

        -- 7.で残金が存在する場合は以下処理を行う。
        IF  v_CalculationAmount > 0 THEN
            -- 過剰金として 消込情報－利用額 に 7.の残金 を足しこむ
            SET v_CheckingUseAmount = v_CheckingUseAmount + v_CalculationAmount;
        END IF;

        -- ++++++++++++++++++++++++++++++++++++++++
        -- 8. 消込情報－消込金額合計 を求める
        -- ++++++++++++++++++++++++++++++++++++++++
        SET v_CheckingClaimAmount = v_CheckingUseAmount + v_CheckingClaimFee + v_CheckingDamageInterestAmount + v_CheckingAdditionalClaimFee;

        -- ------------------------------
        -- 4-4.入金ﾃﾞｰﾀの作成
        -- ------------------------------
        INSERT
        INTO    T_ReceiptControl(   ReceiptProcessDate              -- 入金処理日
                                ,   ReceiptDate                     -- 顧客入金日
                                ,   ReceiptClass                    -- 入金科目（入金方法）
                                ,   ReceiptAmount                   -- 金額
                                ,   ClaimId                         -- 請求ID
                                ,   OrderSeq                        -- 注文SEQ
                                ,   CheckingUseAmount               -- 消込情報－利用額
                                ,   CheckingClaimFee                -- 消込情報－請求手数料
                                ,   CheckingDamageInterestAmount    -- 消込情報－遅延損害金
                                ,   CheckingAdditionalClaimFee      -- 消込情報－請求追加手数料
                                ,   DailySummaryFlg                 -- 日次更新ﾌﾗｸﾞ
                                ,   BranchBankId                    -- 銀行支店ID
                                ,   DepositDate                     -- 入金予定日
                                ,   ReceiptAgentId                  -- 収納代行会社ID
                                ,   Receipt_Note                     -- 備考
                                ,   RegistDate                      -- 登録日時
                                ,   RegistId                        -- 登録者
                                ,   UpdateDate                      -- 更新日時
                                ,   UpdateId                        -- 更新者
                                ,   ValidFlg                        -- 有効ﾌﾗｸﾞ
                                )
                                VALUES
                                (   NOW()                           -- 入金処理日
                                ,   pi_receipt_date                 -- 顧客入金日
                                ,   pi_receipt_class                -- 入金科目（入金方法）
                                ,   pi_receipt_amount               -- 金額
                                ,   v_ClaimId                       -- 請求ID
                                ,   pi_order_seq                    -- 注文SEQ
                                ,   v_CheckingUseAmount             -- 消込情報－利用額
                                ,   v_CheckingClaimFee              -- 消込情報－請求手数料
                                ,   v_CheckingDamageInterestAmount  -- 消込情報－遅延損害金
                                ,   v_CheckingAdditionalClaimFee    -- 消込情報－請求追加手数料
                                ,   0                               -- 日次更新ﾌﾗｸﾞ
                                ,   pi_branch_bank_id               -- 銀行支店ID
                                ,   pi_deposit_date                 -- 入金予定日
                                ,   pi_receipt_agent_id             -- 収納代行会社ID
								, 	pi_receipt_note                  -- 備考
                                ,   NOW()                           -- 登録日時
                                ,   pi_user_id                      -- 登録者
                                ,   NOW()                           -- 更新日時
                                ,   pi_user_id                      -- 更新者
                                ,   1                               -- 有効ﾌﾗｸﾞ
                                );

        -- ------------------------------
        -- 4-4'.入金Seqを取得
        -- ------------------------------
        SELECT  OrderSeq
            ,   MAX(ReceiptSeq)
        INTO    v_OrderSeq
            ,   v_ReceiptSeq
        FROM    T_ReceiptControl
        WHERE   OrderSeq = pi_order_seq
        GROUP BY
                OrderSeq
        ;

        -- ------------------------------
        -- 4-5.最低請求額 > 入金額 の場合
        -- ------------------------------
        IF  v_MinClaimAmount > pi_receipt_amount + v_ReceiptAmount  THEN
            -- ------------------------------
            -- 4-5-1.請求ﾃﾞｰﾀの更新
            -- ------------------------------
            UPDATE  T_ClaimControl
            SET     ClaimedBalance                  =   ClaimedBalance - v_CheckingClaimAmount                              -- 請求残高
                ,   LastProcessDate                 =   DATE(NOW())                                                         -- 最終入金処理日
                ,   LastReceiptSeq                  =   v_ReceiptSeq                                                        -- 最終入金SEQ
                ,   CheckingClaimAmount             =   CheckingClaimAmount + v_CheckingClaimAmount                         -- 消込情報－消込額合計
                ,   CheckingUseAmount               =   CheckingUseAmount + v_CheckingUseAmount                             -- 消込情報－利用額
                ,   CheckingClaimFee                =   CheckingClaimFee + v_CheckingClaimFee                               -- 消込情報－請求手数料
                ,   CheckingDamageInterestAmount    =   CheckingDamageInterestAmount + v_CheckingDamageInterestAmount       -- 消込情報－遅延損害金
                ,   CheckingAdditionalClaimFee      =   CheckingAdditionalClaimFee + v_CheckingAdditionalClaimFee           -- 消込情報－請求追加手数料
                ,   BalanceClaimAmount              =   BalanceClaimAmount - v_CheckingClaimAmount                          -- 残高情報－残高合計
                ,   BalanceUseAmount                =   BalanceUseAmount - v_CheckingUseAmount                              -- 残高情報－利用額
                ,   BalanceClaimFee                 =   BalanceClaimFee - v_CheckingClaimFee                                -- 残高情報－請求手数料
                ,   BalanceDamageInterestAmount     =   BalanceDamageInterestAmount - v_CheckingDamageInterestAmount        -- 残高情報－遅延損害金
                ,   BalanceAdditionalClaimFee       =   BalanceAdditionalClaimFee - v_CheckingAdditionalClaimFee            -- 残高情報－請求追加手数料
                ,   ReceiptAmountTotal              =   ReceiptAmountTotal + pi_receipt_amount                              -- 入金額合計
                ,   UpdateDate                      =   NOW()
                ,   UpdateId                        =   pi_user_id
            WHERE   ClaimId =   v_ClaimId
            ;

        -- ------------------------------
        -- 4-6.最低請求額 = 入金額 の場合
        -- ------------------------------
        ELSEIF  v_MinClaimAmount = pi_receipt_amount + v_ReceiptAmount  THEN
            -- ------------------------------
            -- 4-6-1.最低請求額 = 最終請求額 の場合
            -- ------------------------------
            IF  v_MinClaimAmount = v_ClaimAmount    THEN
                -- ------------------------------
                -- 4-6-1-1.請求ﾃﾞｰﾀの更新
                -- ------------------------------
                UPDATE  T_ClaimControl
                SET     ClaimedBalance                  =   ClaimedBalance - v_CheckingClaimAmount                              -- 請求残高
                    ,   LastProcessDate                 =   DATE(NOW())                                                         -- 最終入金処理日
                    ,   LastReceiptSeq                  =   v_ReceiptSeq                                                        -- 最終入金SEQ
                    ,   CheckingClaimAmount             =   CheckingClaimAmount + v_CheckingClaimAmount                         -- 消込情報－消込額合計
                    ,   CheckingUseAmount               =   CheckingUseAmount + v_CheckingUseAmount                             -- 消込情報－利用額
                    ,   CheckingClaimFee                =   CheckingClaimFee + v_CheckingClaimFee                               -- 消込情報－請求手数料
                    ,   CheckingDamageInterestAmount    =   CheckingDamageInterestAmount + v_CheckingDamageInterestAmount       -- 消込情報－遅延損害金
                    ,   CheckingAdditionalClaimFee      =   CheckingAdditionalClaimFee + v_CheckingAdditionalClaimFee           -- 消込情報－請求追加手数料
                    ,   BalanceClaimAmount              =   BalanceClaimAmount - v_CheckingClaimAmount                          -- 残高情報－残高合計
                    ,   BalanceUseAmount                =   BalanceUseAmount - v_CheckingUseAmount                              -- 残高情報－利用額
                    ,   BalanceClaimFee                 =   BalanceClaimFee - v_CheckingClaimFee                                -- 残高情報－請求手数料
                    ,   BalanceDamageInterestAmount     =   BalanceDamageInterestAmount - v_CheckingDamageInterestAmount        -- 残高情報－遅延損害金
                    ,   BalanceAdditionalClaimFee       =   BalanceAdditionalClaimFee - v_CheckingAdditionalClaimFee            -- 残高情報－請求追加手数料
                    ,   ReceiptAmountTotal              =   ReceiptAmountTotal + pi_receipt_amount                              -- 入金額合計
                    ,   UpdateDate                      =   NOW()
                    ,   UpdateId                        =   pi_user_id
                WHERE   ClaimId =   v_ClaimId
                ;

            -- ------------------------------
            -- 4-6-2.最終請求額 > 最低請求額 の場合
            -- ------------------------------
            ELSEIF  v_ClaimAmount > v_MinClaimAmount    THEN
                -- ------------------------------
                -- 4-6-2-1.雑損失情報の取得
                -- ------------------------------
                -- 最終請求額 と 最低請求額 の差分を算出（雑損失額になる）
                -- 1) 利用額（最終請求額と最低請求額との差分 → 利用額の差分は存在しないので、ｾﾞﾛ）
                SET v_SundryUseAmount = 0;

                -- 2) 請求手数料
                SET v_SundryClaimFee = v_ClaimFee - v_MinClaimFee;

                -- 3) 遅延損害金
                SET v_SundryDamageInterestAmount = v_DamageInterestAmount - v_MinDamageInterestAmount;

                -- 4) 請求追加手数料
                SET v_SundryAdditionalClaimFee = v_AdditionalClaimFee - v_MinAdditionalClaimFee;

                -- 5) 金額
                SET v_SundryAmount = v_SundryUseAmount + v_SundryClaimFee + v_SundryDamageInterestAmount + v_SundryAdditionalClaimFee;

                -- ------------------------------
                -- 4-6-2-2.雑損失ﾃﾞｰﾀの作成
                -- ------------------------------
                INSERT
                INTO    T_SundryControl(    ProcessDate                     -- 発生日時
                                        ,   SundryType                      -- 種類（雑収入／雑損失）
                                        ,   SundryAmount                    -- 金額
                                        ,   SundryClass                     -- 雑収入・雑損失科目
                                        ,   OrderSeq                        -- 注文SEQ
                                        ,   OrderId                         -- 注文ID
                                        ,   ClaimId                         -- 請求ID
                                        ,   Note                            -- 備考
                                        ,   CheckingUseAmount               -- 消込情報－利用額
                                        ,   CheckingClaimFee                -- 消込情報－請求手数料
                                        ,   CheckingDamageInterestAmount    -- 消込情報－遅延損害金
                                        ,   CheckingAdditionalClaimFee      -- 消込情報－請求追加手数料
                                        ,   DailySummaryFlg                 -- 日次更新ﾌﾗｸﾞ
                                        ,   RegistDate                      -- 登録日時
                                        ,   RegistId                        -- 登録者
                                        ,   UpdateDate                      -- 更新日時
                                        ,   UpdateId                        -- 更新者
                                        ,   ValidFlg                        -- 有効ﾌﾗｸﾞ　（0：無効　1：有効）
                                       )
                                       VALUES
                                       (    DATE(NOW())                     -- 発生日時
                                        ,   1                               -- 種類（雑収入／雑損失）
                                        ,   v_SundryAmount                  -- 金額
                                        ,   99                              -- 雑収入・雑損失科目
                                        ,   pi_order_seq                    -- 注文SEQ
                                        ,   v_OrderId                       -- 注文ID
                                        ,   v_ClaimId                       -- 請求ID
                                        ,   NULL                            -- 備考
                                        ,   v_SundryUseAmount               -- 消込情報－利用額
                                        ,   v_SundryClaimFee                -- 消込情報－請求手数料
                                        ,   v_SundryDamageInterestAmount    -- 消込情報－遅延損害金
                                        ,   v_SundryAdditionalClaimFee      -- 消込情報－請求追加手数料
                                        ,   0                               -- 日次更新ﾌﾗｸﾞ
                                        ,   NOW()                           -- 登録日時
                                        ,   pi_user_id                      -- 登録者
                                        ,   NOW()                           -- 更新日時
                                        ,   pi_user_id                      -- 更新者
                                        ,   1                               -- 有効ﾌﾗｸﾞ
                                       );

                -- ------------------------------
                -- 4-6-2-3.請求ﾃﾞｰﾀの更新
                -- ------------------------------
                UPDATE  T_ClaimControl
                SET     ClaimedBalance                  =   ClaimedBalance - v_CheckingClaimAmount - v_SundryAmount                                             -- 請求残高
                    ,   LastProcessDate                 =   DATE(NOW())                                                                                         -- 最終入金処理日
                    ,   LastReceiptSeq                  =   v_ReceiptSeq                                                                                        -- 最終入金SEQ
                    ,   CheckingClaimAmount             =   CheckingClaimAmount + v_CheckingClaimAmount + v_SundryAmount                                        -- 消込情報－消込額合計
                    ,   CheckingUseAmount               =   CheckingUseAmount + v_CheckingUseAmount + v_SundryUseAmount                                         -- 消込情報－利用額
                    ,   CheckingClaimFee                =   CheckingClaimFee + v_CheckingClaimFee + v_SundryClaimFee                                            -- 消込情報－請求手数料
                    ,   CheckingDamageInterestAmount    =   CheckingDamageInterestAmount + v_CheckingDamageInterestAmount + v_SundryDamageInterestAmount        -- 消込情報－遅延損害金
                    ,   CheckingAdditionalClaimFee      =   CheckingAdditionalClaimFee + v_CheckingAdditionalClaimFee + v_SundryAdditionalClaimFee              -- 消込情報－請求追加手数料
                    ,   BalanceClaimAmount              =   BalanceClaimAmount - v_CheckingClaimAmount - v_SundryAmount                                         -- 残高情報－残高合計
                    ,   BalanceUseAmount                =   BalanceUseAmount - v_CheckingUseAmount - v_SundryUseAmount                                          -- 残高情報－利用額
                    ,   BalanceClaimFee                 =   BalanceClaimFee - v_CheckingClaimFee - v_SundryClaimFee                                             -- 残高情報－請求手数料
                    ,   BalanceDamageInterestAmount     =   BalanceDamageInterestAmount - v_CheckingDamageInterestAmount - v_SundryDamageInterestAmount         -- 残高情報－遅延損害金
                    ,   BalanceAdditionalClaimFee       =   BalanceAdditionalClaimFee - v_CheckingAdditionalClaimFee - v_SundryAdditionalClaimFee               -- 残高情報－請求追加手数料
                    ,   ReceiptAmountTotal              =   ReceiptAmountTotal + pi_receipt_amount                                                              -- 入金額合計
                    ,   SundryLossTotal                 =   SundryLossTotal + v_SundryAmount                                                                    -- 雑損失合計
                    ,   UpdateDate                      =   NOW()
                    ,   UpdateId                        =   pi_user_id
                WHERE   ClaimId =   v_ClaimId
                ;
            END IF;

            -- ------------------------------
            -- 4-6-3.注文ﾃﾞｰﾀの更新
            -- ------------------------------
            UPDATE  T_Order
            SET     DataStatus  =   91      -- ﾃﾞｰﾀｽﾃｰﾀｽ（ｸﾛｰｽﾞ）
                ,   CloseReason =   1       -- ｸﾛｰｽﾞ理由（入金済み正常ｸﾛｰｽﾞ）
                ,   Rct_Status  =   1       -- 顧客入金ｽﾃｰﾀｽ（入金済み）
                ,   UpdateDate  =   NOW()
                ,   UpdateId    =   pi_user_id
            WHERE   P_OrderSeq  =   pi_order_seq
            AND     Cnl_Status  =   0
            ;

        -- ------------------------------
        -- 4-7.最終請求額 > 入金額 > 最低請求額 の場合
        -- ------------------------------
        ELSEIF  v_ClaimAmount > pi_receipt_amount + v_ReceiptAmount AND pi_receipt_amount + v_ReceiptAmount > v_MinClaimAmount  THEN
            -- ------------------------------
            -- 4-7-1.雑損失情報の取得
            -- ------------------------------
            -- 最終の金額から消込金額と入金済み額を減算して差分を算出
            -- 1) 利用額
            SET v_SundryUseAmount = v_UseAmountTotal - (v_CheckingUseAmount + v_ReceiptUseAmount);

            -- 2) 請求手数料
            SET v_SundryClaimFee = v_ClaimFee - (v_CheckingClaimFee + v_ReceiptClaimFee);

            -- 3) 遅延損害金
            SET v_SundryDamageInterestAmount = v_DamageInterestAmount - (v_CheckingDamageInterestAmount + v_ReceiptDamageInterestAmount);

            -- 4) 請求追加手数料
            SET v_SundryAdditionalClaimFee = v_AdditionalClaimFee - (v_CheckingAdditionalClaimFee + v_ReceiptAdditionalClaimFee);

            -- 5) 金額
            SET v_SundryAmount = v_SundryUseAmount + v_SundryClaimFee + v_SundryDamageInterestAmount + v_SundryAdditionalClaimFee;

            -- ------------------------------
            -- 4-7-2.雑損失ﾃﾞｰﾀの作成
            -- ------------------------------
            INSERT
            INTO    T_SundryControl(    ProcessDate                     -- 発生日時
                                    ,   SundryType                      -- 種類（雑収入／雑損失）
                                    ,   SundryAmount                    -- 金額
                                    ,   SundryClass                     -- 雑収入・雑損失科目
                                    ,   OrderSeq                        -- 注文SEQ
                                    ,   OrderId                         -- 注文ID
                                    ,   ClaimId                         -- 請求ID
                                    ,   Note                            -- 備考
                                    ,   CheckingUseAmount               -- 消込情報－利用額
                                    ,   CheckingClaimFee                -- 消込情報－請求手数料
                                    ,   CheckingDamageInterestAmount    -- 消込情報－遅延損害金
                                    ,   CheckingAdditionalClaimFee      -- 消込情報－請求追加手数料
                                    ,   DailySummaryFlg                 -- 日次更新ﾌﾗｸﾞ
                                    ,   RegistDate                      -- 登録日時
                                    ,   RegistId                        -- 登録者
                                    ,   UpdateDate                      -- 更新日時
                                    ,   UpdateId                        -- 更新者
                                    ,   ValidFlg                        -- 有効ﾌﾗｸﾞ　（0：無効　1：有効）
                                   )
                                   VALUES
                                   (    DATE(NOW())                     -- 発生日時
                                    ,   1                               -- 種類（雑収入／雑損失）
                                    ,   v_SundryAmount                  -- 金額
                                    ,   99                              -- 雑収入・雑損失科目
                                    ,   pi_order_seq                    -- 注文SEQ
                                    ,   v_OrderId                       -- 注文ID
                                    ,   v_ClaimId                       -- 請求ID
                                    ,   NULL                            -- 備考
                                    ,   v_SundryUseAmount               -- 消込情報－利用額
                                    ,   v_SundryClaimFee                -- 消込情報－請求手数料
                                    ,   v_SundryDamageInterestAmount    -- 消込情報－遅延損害金
                                    ,   v_SundryAdditionalClaimFee      -- 消込情報－請求追加手数料
                                    ,   0                               -- 日次更新ﾌﾗｸﾞ
                                    ,   NOW()                           -- 登録日時
                                    ,   pi_user_id                      -- 登録者
                                    ,   NOW()                           -- 更新日時
                                    ,   pi_user_id                      -- 更新者
                                    ,   1                               -- 有効ﾌﾗｸﾞ
                                   );

            -- ------------------------------
            -- 4-7-3.請求ﾃﾞｰﾀの更新
            -- ------------------------------
            UPDATE  T_ClaimControl
            SET     ClaimedBalance                  =   v_ClaimedBalance - v_CheckingClaimAmount - v_SundryAmount                                           -- 請求残高
                ,   LastProcessDate                 =   DATE(NOW())                                                                                         -- 最終入金処理日
                ,   LastReceiptSeq                  =   v_ReceiptSeq                                                                                        -- 最終入金SEQ
                ,   CheckingClaimAmount             =   CheckingClaimAmount + v_CheckingClaimAmount + v_SundryAmount                                        -- 消込情報－消込額合計
                ,   CheckingUseAmount               =   CheckingUseAmount + v_CheckingUseAmount + v_SundryUseAmount                                         -- 消込情報－利用額
                ,   CheckingClaimFee                =   CheckingClaimFee + v_CheckingClaimFee + v_SundryClaimFee                                            -- 消込情報－請求手数料
                ,   CheckingDamageInterestAmount    =   CheckingDamageInterestAmount + v_CheckingDamageInterestAmount + v_SundryDamageInterestAmount        -- 消込情報－遅延損害金
                ,   CheckingAdditionalClaimFee      =   CheckingAdditionalClaimFee + v_CheckingAdditionalClaimFee + v_SundryAdditionalClaimFee              -- 消込情報－請求追加手数料
                ,   BalanceClaimAmount              =   BalanceClaimAmount - v_CheckingClaimAmount - v_SundryAmount                                         -- 残高情報－残高合計
                ,   BalanceUseAmount                =   BalanceUseAmount - v_CheckingUseAmount - v_SundryUseAmount                                          -- 残高情報－利用額
                ,   BalanceClaimFee                 =   BalanceClaimFee - v_CheckingClaimFee - v_SundryClaimFee                                             -- 残高情報－請求手数料
                ,   BalanceDamageInterestAmount     =   BalanceDamageInterestAmount - v_CheckingDamageInterestAmount - v_SundryDamageInterestAmount         -- 残高情報－遅延損害金
                ,   BalanceAdditionalClaimFee       =   BalanceAdditionalClaimFee - v_CheckingAdditionalClaimFee - v_SundryAdditionalClaimFee               -- 残高情報－請求追加手数料
                ,   ReceiptAmountTotal              =   ReceiptAmountTotal + pi_receipt_amount                                                              -- 入金額合計
                ,   SundryLossTotal                 =   SundryLossTotal + v_SundryAmount                                                                    -- 雑損失合計
                ,   UpdateDate                      =   NOW()
                ,   UpdateId                        =   pi_user_id
            WHERE   ClaimId =   v_ClaimId
            ;

            -- ------------------------------
            -- 4-7-4.注文ﾃﾞｰﾀの更新
            -- ------------------------------
            UPDATE  T_Order
            SET     DataStatus  =   91      -- ﾃﾞｰﾀｽﾃｰﾀｽ（ｸﾛｰｽﾞ）
                ,   CloseReason =   1       -- ｸﾛｰｽﾞ理由（入金済み正常ｸﾛｰｽﾞ）
                ,   Rct_Status  =   1       -- 顧客入金ｽﾃｰﾀｽ（入金済み）
                ,   UpdateDate  =   NOW()
                ,   UpdateId    =   pi_user_id
            WHERE   P_OrderSeq  =   pi_order_seq
            AND     Cnl_Status  =   0
            ;

        -- ------------------------------
        -- 4-8.最終請求額 <= 入金額 の場合
        -- ------------------------------
        ELSEIF  v_ClaimAmount <= pi_receipt_amount + v_ReceiptAmount    THEN
            -- ------------------------------
            -- 4-8-1.請求ﾃﾞｰﾀの更新
            -- ------------------------------
            UPDATE  T_ClaimControl
            SET     ClaimedBalance                  =   ClaimedBalance - v_CheckingClaimAmount                              -- 請求残高
                ,   LastProcessDate                 =   DATE(NOW())                                                         -- 最終入金処理日
                ,   LastReceiptSeq                  =   v_ReceiptSeq                                                        -- 最終入金SEQ
                ,   CheckingClaimAmount             =   CheckingClaimAmount + v_CheckingClaimAmount                         -- 消込情報－消込額合計
                ,   CheckingUseAmount               =   CheckingUseAmount + v_CheckingUseAmount                             -- 消込情報－利用額
                ,   CheckingClaimFee                =   CheckingClaimFee + v_CheckingClaimFee                               -- 消込情報－請求手数料
                ,   CheckingDamageInterestAmount    =   CheckingDamageInterestAmount + v_CheckingDamageInterestAmount       -- 消込情報－遅延損害金
                ,   CheckingAdditionalClaimFee      =   CheckingAdditionalClaimFee + v_CheckingAdditionalClaimFee           -- 消込情報－請求追加手数料
                ,   BalanceClaimAmount              =   BalanceClaimAmount - v_CheckingClaimAmount                          -- 残高情報－残高合計
                ,   BalanceUseAmount                =   BalanceUseAmount - v_CheckingUseAmount                              -- 残高情報－利用額
                ,   BalanceClaimFee                 =   BalanceClaimFee - v_CheckingClaimFee                                -- 残高情報－請求手数料
                ,   BalanceDamageInterestAmount     =   BalanceDamageInterestAmount - v_CheckingDamageInterestAmount        -- 残高情報－遅延損害金
                ,   BalanceAdditionalClaimFee       =   BalanceAdditionalClaimFee - v_CheckingAdditionalClaimFee            -- 残高情報－請求追加手数料
                ,   ReceiptAmountTotal              =   ReceiptAmountTotal + pi_receipt_amount                              -- 入金額合計
                ,   UpdateDate                      =   NOW()
                ,   UpdateId                        =   pi_user_id
            WHERE   ClaimId =   v_ClaimId
            ;

            -- ------------------------------
            -- 4-8-2.注文ﾃﾞｰﾀの更新
            -- ------------------------------
            UPDATE  T_Order
            SET     DataStatus  =   91      -- ﾃﾞｰﾀｽﾃｰﾀｽ（ｸﾛｰｽﾞ）
                ,   CloseReason =   1       -- ｸﾛｰｽﾞ理由（入金済み正常ｸﾛｰｽﾞ）
                ,   Rct_Status  =   1       -- 顧客入金ｽﾃｰﾀｽ（入金済み）
                ,   UpdateDate  =   NOW()
                ,   UpdateId    =   pi_user_id
            WHERE   P_OrderSeq  =   pi_order_seq
            AND     Cnl_Status  =   0
            ;
        END IF;

    -- ------------------------------
    -- 5.入金ｸﾛｰｽﾞ後の入金の場合
    -- ------------------------------
    ELSE
        -- ------------------------------
        -- 5-1.入金済みのﾃﾞｰﾀを取得
        -- ------------------------------
        SELECT  SUM(ReceiptAmount)                   -- 金額
            ,   SUM(CheckingUseAmount)               -- 消込情報－利用額
            ,   SUM(CheckingClaimFee)                -- 消込情報－請求手数料
            ,   SUM(CheckingDamageInterestAmount)    -- 消込情報－遅延損害金
            ,   SUM(CheckingAdditionalClaimFee)      -- 消込情報－請求追加手数料
            ,   COUNT(*)
        INTO    v_ReceiptAmount
            ,   v_ReceiptUseAmount
            ,   v_ReceiptClaimFee
            ,   v_ReceiptDamageInterestAmount
            ,   v_ReceiptAdditionalClaimFee
            ,   v_Cnt
        FROM    T_ReceiptControl
        WHERE   OrderSeq    =   pi_order_seq
        ;

        IF  v_Cnt = 0   THEN
            SET po_ret_sts  =   -1;
            SET po_ret_msg  =   '入金済みのデータが存在しません。';
            LEAVE proc;
        END IF;

        -- ------------------------------
        -- 5-2.請求ﾃﾞｰﾀ取得
        -- ------------------------------
        SELECT  ClaimId                         -- 請求ID
            ,   ClaimAmount                     -- 請求額
            ,   UseAmountTotal                  -- 利用額合計
            ,   ClaimFee                        -- 請求手数料
            ,   DamageInterestAmount            -- 遅延損害金
            ,   AdditionalClaimFee              -- 請求追加手数料
            ,   ClaimedBalance                  -- 請求残高
            ,   MinClaimAmount                  -- 最低請求情報－請求金額
            ,   MinUseAmount                    -- 最低請求情報－利用額
            ,   MinClaimFee                     -- 最低請求情報－請求手数料
            ,   MinDamageInterestAmount         -- 最低請求情報－遅延損害金
            ,   MinAdditionalClaimFee           -- 最低請求情報－請求追加手数料
        INTO    v_ClaimId
            ,   v_ClaimAmount
            ,   v_UseAmountTotal
            ,   v_ClaimFee
            ,   v_DamageInterestAmount
            ,   v_AdditionalClaimFee
            ,   v_ClaimedBalance
            ,   v_MinClaimAmount
            ,   v_MinUseAmount
            ,   v_MinClaimFee
            ,   v_MinDamageInterestAmount
            ,   v_MinAdditionalClaimFee
        FROM    T_ClaimControl
        WHERE   OrderSeq    =   pi_order_seq
        ;

        IF  no_data_found = 0   THEN
            SET po_ret_sts  =   -1;
            SET po_ret_msg  =   '請求対象のデータが存在しません。';
            LEAVE proc;
        END IF;

        -- ------------------------------
        -- 5-3.入金用消込情報の計算
        -- ------------------------------
        -- ++++++++++++++++++++++++++++++++++++++++
        -- 1. 消込情報－請求追加手数料 を求める
        -- ++++++++++++++++++++++++++++++++++++++++
        -- 入金済みの請求追加手数料 が 請求追加手数料 以上の場合
        IF  v_ReceiptAdditionalClaimFee >= v_AdditionalClaimFee THEN
            -- 消込情報－請求追加手数料 に対する消し込みはなし
            SET v_InsReceiptAdditionalClaimFee = 0;
            -- 残金は入金額
            SET v_CalculationAmount = pi_receipt_amount;
        -- それ以外の場合
        ELSE
            -- 消込情報－請求追加手数料 は 請求追加手数料 から 入金済みの請求追加手数料 を減算して取得
            SET v_InsReceiptAdditionalClaimFee = v_AdditionalClaimFee - v_ReceiptAdditionalClaimFee;
            -- 入金額 から 消込情報－請求追加手数料 を減算して残金を算出
            SET v_CalculationAmount = pi_receipt_amount - v_InsReceiptAdditionalClaimFee;
        END IF;

        -- 1.の残金が存在する場合以下処理を行う
        IF  v_CalculationAmount > 0 THEN
            -- ++++++++++++++++++++++++++++++++++++++++
            -- 2. 消込情報－遅延損害金 を求める
            -- ++++++++++++++++++++++++++++++++++++++++
            -- 入金済みの遅延損害金 が 遅延損害金 以上の場合
            IF  v_ReceiptDamageInterestAmount >= v_DamageInterestAmount THEN
                -- 消込情報－遅延損害金 に対する消し込みはなし
                SET v_InsReceiptDamageInterestAmount = 0;
                -- 残金は1.の残金
                SET v_CalculationAmount = v_CalculationAmount;
            -- それ以外の場合
            ELSE
                -- 消込情報－遅延損害金 は 遅延損害金 から 入金済みの遅延損害金 を減算して取得
                SET v_InsReceiptDamageInterestAmount = v_DamageInterestAmount - v_ReceiptDamageInterestAmount;
                -- 1.の残金 から 消込情報－遅延損害金 を減算して入金額の残金を算出
                SET v_CalculationAmount = v_CalculationAmount - v_InsReceiptDamageInterestAmount;
            END IF;
        END IF;

        -- 2.の残金が存在する場合以下処理を行う
        IF  v_CalculationAmount > 0 THEN
            -- ++++++++++++++++++++++++++++++++++++++++
            -- 3. 消込情報－請求手数料 を求める
            -- ++++++++++++++++++++++++++++++++++++++++
            -- 入金済みの請求手数料 が 請求手数料 以上の場合
            IF  v_ReceiptClaimFee >= v_ClaimFee THEN
                -- 消込情報－請求手数料 に対する消し込みはなし
                SET v_InsReceiptClaimFee = 0;
                -- 残金は2.の残金
                SET v_CalculationAmount = v_CalculationAmount;
            -- それ以外の場合
            ELSE
                -- 消込情報－請求手数料 は 請求手数料 から 入金済みの請求手数料 を減算して取得
                SET v_InsReceiptClaimFee = v_ClaimFee - v_ReceiptClaimFee;
                -- 2.の残金 から 消込情報－請求手数料 を減算して算出
                SET v_CalculationAmount = v_CalculationAmount - v_InsReceiptClaimFee;
            END IF;
        END IF;

        -- 3.の残金が存在する場合以下処理を行う
        IF  v_CalculationAmount > 0 THEN
            -- ++++++++++++++++++++++++++++++++++++++++
            -- 4. 消込情報－利用額 を求める
            -- ++++++++++++++++++++++++++++++++++++++++
            -- 過剰入金に当たるので、残金は全額 消込情報－利用額になる
            SET v_InsReceiptUseAmount = v_CalculationAmount;
        END IF;

        -- ++++++++++++++++++++++++++++++++++++++++
        -- 5. 消込情報－消込金額合計 を求める
        -- ++++++++++++++++++++++++++++++++++++++++
        SET v_InsReceiptAmount = v_InsReceiptUseAmount + v_InsReceiptClaimFee + v_InsReceiptDamageInterestAmount + v_InsReceiptAdditionalClaimFee;

        -- ------------------------------
        -- 5-4.入金ﾃﾞｰﾀの作成
        -- ------------------------------
        INSERT
        INTO    T_ReceiptControl(   ReceiptProcessDate                  -- 入金処理日
                                ,   ReceiptDate                         -- 顧客入金日
                                ,   ReceiptClass                        -- 入金科目（入金方法）
                                ,   ReceiptAmount                       -- 金額
                                ,   ClaimId                             -- 請求ID
                                ,   OrderSeq                            -- 注文SEQ
                                ,   CheckingUseAmount                   -- 消込情報－利用額
                                ,   CheckingClaimFee                    -- 消込情報－請求手数料
                                ,   CheckingDamageInterestAmount        -- 消込情報－遅延損害金
                                ,   CheckingAdditionalClaimFee          -- 消込情報－請求追加手数料
                                ,   DailySummaryFlg                     -- 日次更新ﾌﾗｸﾞ
                                ,   BranchBankId                        -- 銀行支店ID
                                ,   DepositDate                         -- 入金予定日
                                ,   ReceiptAgentId                      -- 収納代行会社ID
                                ,   Receipt_Note                         -- 備考
                                ,   RegistDate                          -- 登録日時
                                ,   RegistId                            -- 登録者
                                ,   UpdateDate                          -- 更新日時
                                ,   UpdateId                            -- 更新者
                                ,   ValidFlg                            -- 有効ﾌﾗｸﾞ
                                )
                                VALUES
                                (   NOW()                               -- 入金処理日
                                ,   pi_receipt_date                     -- 顧客入金日
                                ,   pi_receipt_class                    -- 入金科目（入金方法）
                                ,   pi_receipt_amount                   -- 金額
                                ,   v_ClaimId                           -- 請求ID
                                ,   pi_order_seq                        -- 注文SEQ
                                ,   v_InsReceiptUseAmount               -- 消込情報－利用額
                                ,   v_InsReceiptClaimFee                -- 消込情報－請求手数料
                                ,   v_InsReceiptDamageInterestAmount    -- 消込情報－遅延損害金
                                ,   v_InsReceiptAdditionalClaimFee      -- 消込情報－請求追加手数料
                                ,   0                                   -- 日次更新ﾌﾗｸﾞ
                                ,   pi_branch_bank_id                   -- 銀行支店ID
                                ,   pi_deposit_date                     -- 入金予定日
                                ,   pi_receipt_agent_id                 -- 収納代行会社ID
                                ,   pi_receipt_note                     -- 備考
                                ,   NOW()                               -- 登録日時
                                ,   pi_user_id                          -- 登録者
                                ,   NOW()                               -- 更新日時
                                ,   pi_user_id                          -- 更新者
                                ,   1                                   -- 有効ﾌﾗｸﾞ
                                );

        -- ------------------------------
        -- 5-4'.入金Seqを取得
        -- ------------------------------
        SELECT  OrderSeq
            ,   MAX(ReceiptSeq)
        INTO    v_OrderSeq
            ,   v_ReceiptSeq
        FROM    T_ReceiptControl
        WHERE   OrderSeq = pi_order_seq
        GROUP BY
                OrderSeq
        ;

        -- ------------------------------
        -- 5-5.入金済額 >= 最終請求額 の場合
        -- ------------------------------
        IF  v_ReceiptAmount >= v_ClaimAmount    THEN
            -- ------------------------------
            -- 5-5-1.請求ﾃﾞｰﾀの更新
            -- ------------------------------
            UPDATE  T_ClaimControl
            SET     ClaimedBalance      =   ClaimedBalance - pi_receipt_amount          -- 請求残高
                ,   LastProcessDate     =   DATE(NOW())                                 -- 最終入金処理日
                ,   LastReceiptSeq      =   v_ReceiptSeq                                -- 最終入金SEQ
                ,   CheckingClaimAmount =   CheckingClaimAmount + pi_receipt_amount     -- 消込情報－消込金額合計
                ,   CheckingUseAmount   =   CheckingUseAmount + pi_receipt_amount       -- 消込情報－利用額
                ,   BalanceClaimAmount  =   BalanceClaimAmount - pi_receipt_amount      -- 残高情報－残高合計
                ,   BalanceUseAmount    =   BalanceUseAmount - pi_receipt_amount        -- 残高情報－利用額
                ,   ReceiptAmountTotal  =   ReceiptAmountTotal + pi_receipt_amount      -- 入金額合計
                ,   UpdateDate          =   NOW()
                ,   UpdateId            =   pi_user_id
            WHERE   ClaimId =   v_ClaimId
            ;

        -- ------------------------------
        -- 5-6.入金済額 = 最低請求額 の場合
        -- ------------------------------
        ELSEIF  v_ReceiptAmount = v_MinClaimAmount  THEN
            -- ------------------------------
            -- 5-6-1.雑損失のﾃﾞｰﾀを取得
            -- ------------------------------
            -- 取得件数が1件とは限らないため、OrderSeq に紐づく雑損失ﾃﾞｰﾀのｻﾏﾘを取得する（ｺｺはｻﾏﾘしなくてもいいとは思うけど･･･念のため･･･）
            SELECT  SUM(SundryAmount)                   -- 金額
                ,   SUM(CheckingUseAmount)              -- 消込情報－利用額
                ,   SUM(CheckingClaimFee)               -- 消込情報－請求手数料
                ,   SUM(CheckingDamageInterestAmount)   -- 消込情報－遅延損害金
                ,   SUM(CheckingAdditionalClaimFee)     -- 消込情報－請求追加手数料
                ,   COUNT(*)
            INTO    v_SundryAmount
                ,   v_SundryUseAmount
                ,   v_SundryClaimFee
                ,   v_SundryDamageInterestAmount
                ,   v_SundryAdditionalClaimFee
                ,   v_Cnt
            FROM    T_SundryControl
            WHERE   OrderSeq    =   pi_order_seq
            ;

            IF  v_Cnt = 0   THEN
                SET po_ret_sts  =   -1;
                SET po_ret_msg  =   '雑損失データが存在しません。';
                LEAVE proc;
            END IF;

            -- ------------------------------
            -- 5-6-2.赤伝用消込情報を計算
            -- ------------------------------
            -- ++++++++++++++++++++++++++++++++++++++++
            -- 1. 消込情報－請求追加手数料 を求める
            -- ++++++++++++++++++++++++++++++++++++++++
            -- 1) 消込情報－請求追加手数料 が 入金額 以上の場合
            IF  v_SundryAdditionalClaimFee >= pi_receipt_amount THEN
                -- 消込情報－請求追加手数料 は 入金額
                SET v_CheckingAdditionalClaimFee = pi_receipt_amount;
                -- 残金は 0（ｾﾞﾛ）
                SET v_CalculationAmount = 0;
            -- それ以外
            ELSE
                SET v_CheckingAdditionalClaimFee = v_SundryAdditionalClaimFee;
                SET v_CalculationAmount = pi_receipt_amount - v_CheckingAdditionalClaimFee;
            END IF;

            -- 1.で残金が存在する場合
            IF  v_CalculationAmount > 0 THEN
                -- ++++++++++++++++++++++++++++++++++++++++
                -- 2. 消込情報－遅延損害金 を求める
                -- ++++++++++++++++++++++++++++++++++++++++
                -- 1) 消込情報－遅延損害金 が 残金 以上の場合
                IF  v_SundryDamageInterestAmount >= v_CalculationAmount THEN
                    -- 消込情報－遅延損害金 は 残金
                    SET v_CheckingDamageInterestAmount = v_CalculationAmount;
                    -- 残金は 0（ｾﾞﾛ）
                    SET v_CalculationAmount = 0;
                -- それ以外
                ELSE
                    SET v_CheckingDamageInterestAmount = v_SundryDamageInterestAmount;
                    SET v_CalculationAmount = v_CalculationAmount - v_CheckingDamageInterestAmount;
                END IF;
            END IF;

            -- 2.で残金が存在する場合
            IF  v_CalculationAmount > 0 THEN
                -- ++++++++++++++++++++++++++++++++++++++++
                -- 3. 消込情報－請求手数料 を求める
                -- ++++++++++++++++++++++++++++++++++++++++
                -- 1) 消込情報－請求手数料 が 残金 以上の場合
                IF  v_SundryClaimFee >= v_CalculationAmount THEN
                    -- 消込情報－請求手数料 は 残金
                    SET v_CheckingClaimFee = v_CalculationAmount;
                    -- 残金は 0（ｾﾞﾛ）
                    SET v_CalculationAmount = 0;
                -- それ以外
                ELSE
                    SET v_CheckingClaimFee = v_SundryClaimFee;
                    SET v_CalculationAmount = v_CalculationAmount - v_CheckingClaimFee;
                END IF;
            END IF;

            -- 3.で残金が存在する場合
            IF  v_CalculationAmount > 0 THEN
                -- ++++++++++++++++++++++++++++++++++++++++
                -- 4. 消込情報－利用額 を求める
                -- ++++++++++++++++++++++++++++++++++++++++
                -- 1) 消込情報－利用額 が 残金 以上の場合
                IF  v_SundryUseAmount >= v_CalculationAmount    THEN
                    -- 消込情報－利用額 は 残金
                    SET v_CheckingUseAmount = v_CalculationAmount;
                    -- 残金は 0（ｾﾞﾛ）
                    SET v_CalculationAmount = 0;
                -- それ以外
                ELSE
                    SET v_CheckingUseAmount = v_SundryUseAmount;
                    SET v_CalculationAmount = v_CalculationAmount - v_CheckingUseAmount;
                END IF;
            END IF;

            -- ++++++++++++++++++++++++++++++++++++++++
            -- 5. 消込情報－消込金額合計 を求める
            -- ++++++++++++++++++++++++++++++++++++++++
            SET v_CheckingClaimAmount = v_CheckingUseAmount + v_CheckingClaimFee + v_CheckingDamageInterestAmount + v_CheckingAdditionalClaimFee;

            -- ------------------------------
            -- 5-6-3.雑損失赤伝ﾃﾞｰﾀの作成
            -- ------------------------------
            -- 取得した消込情報に-1を掛ける
            INSERT
            INTO    T_SundryControl(    ProcessDate                             -- 発生日時
                                    ,   SundryType                              -- 種類（雑収入／雑損失）
                                    ,   SundryAmount                            -- 金額
                                    ,   SundryClass                             -- 雑収入・雑損失科目
                                    ,   OrderSeq                                -- 注文SEQ
                                    ,   OrderId                                 -- 注文ID
                                    ,   ClaimId                                 -- 請求ID
                                    ,   Note                                    -- 備考
                                    ,   CheckingUseAmount                       -- 消込情報－利用額
                                    ,   CheckingClaimFee                        -- 消込情報－請求手数料
                                    ,   CheckingDamageInterestAmount            -- 消込情報－遅延損害金
                                    ,   CheckingAdditionalClaimFee              -- 消込情報－請求追加手数料
                                    ,   DailySummaryFlg                         -- 日次更新ﾌﾗｸﾞ
                                    ,   RegistDate                              -- 登録日時
                                    ,   RegistId                                -- 登録者
                                    ,   UpdateDate                              -- 更新日時
                                    ,   UpdateId                                -- 更新者
                                    ,   ValidFlg                                -- 有効ﾌﾗｸﾞ
                                   )
                                   VALUES
                                   (    DATE(NOW())                             -- 発生日時
                                    ,   1                                       -- 種類（雑収入／雑損失）
                                    ,   v_CheckingClaimAmount * -1              -- 金額
                                    ,   99                                      -- 雑収入・雑損失科目
                                    ,   pi_order_seq                            -- 注文SEQ
                                    ,   v_OrderId                               -- 注文ID
                                    ,   v_ClaimId                               -- 請求ID
                                    ,   NULL                                    -- 備考
                                    ,   v_CheckingUseAmount * -1                -- 消込情報－利用額
                                    ,   v_CheckingClaimFee * -1                 -- 消込情報－請求手数料
                                    ,   v_CheckingDamageInterestAmount * -1     -- 消込情報－遅延損害金
                                    ,   v_CheckingAdditionalClaimFee * -1       -- 消込情報－請求追加手数料
                                    ,   0                                       -- 日次更新ﾌﾗｸﾞ
                                    ,   NOW()                                   -- 登録日時
                                    ,   pi_user_id                              -- 登録者
                                    ,   NOW()                                   -- 更新日時
                                    ,   pi_user_id                              -- 更新者
                                    ,   1                                       -- 有効ﾌﾗｸﾞ
                                   );

            -- ------------------------------
            -- 5-6-4.最終請求額 >= 入金額 の場合
            -- ------------------------------
            IF  v_ClaimAmount >= pi_receipt_amount + v_ReceiptAmount THEN
                -- ------------------------------
                -- 5-6-4-1.請求ﾃﾞｰﾀの更新
                -- ------------------------------
                UPDATE  T_ClaimControl
                SET     LastProcessDate     =   DATE(NOW())                                     -- 最終入金処理日
                    ,   LastReceiptSeq      =   v_ReceiptSeq                                    -- 最終入金SEQ
                    ,   ReceiptAmountTotal  =   ReceiptAmountTotal + pi_receipt_amount          -- 入金額合計
                    ,   SundryLossTotal     =   SundryLossTotal - v_CheckingClaimAmount         -- 雑損失合計
                    ,   UpdateDate          =   NOW()
                    ,   UpdateId            =   pi_user_id
                WHERE   ClaimId =   v_ClaimId
                ;

            -- ------------------------------
            -- 5-6-5.入金額 > 最終請求額 の場合
            -- ------------------------------
            ELSEIF  pi_receipt_amount + v_ReceiptAmount > v_ClaimAmount THEN
                -- ------------------------------
                -- 5-6-5-1.請求ﾃﾞｰﾀの更新
                -- ------------------------------
                -- 過剰入金分を足しこむ
                UPDATE  T_ClaimControl
                SET     ClaimedBalance      =   ClaimedBalance - v_InsReceiptUseAmount          -- 請求残高
                    ,   LastProcessDate     =   DATE(NOW())                                     -- 最終入金処理日
                    ,   LastReceiptSeq      =   v_ReceiptSeq                                    -- 最終入金SEQ
                    ,   CheckingClaimAmount =   CheckingClaimAmount + v_InsReceiptUseAmount     -- 消込情報－消込金額合計
                    ,   CheckingUseAmount   =   CheckingUseAmount + v_InsReceiptUseAmount       -- 消込情報－利用額
                    ,   BalanceClaimAmount  =   BalanceClaimAmount - v_InsReceiptUseAmount      -- 残高情報－残高合計
                    ,   BalanceUseAmount    =   BalanceUseAmount - v_InsReceiptUseAmount        -- 残高情報－利用額
                    ,   ReceiptAmountTotal  =   ReceiptAmountTotal + pi_receipt_amount          -- 入金額合計
                    ,   SundryLossTotal     =   SundryLossTotal - v_SundryAmount                -- 雑損失合計
                    ,   UpdateDate          =   NOW()
                    ,   UpdateId            =   pi_user_id
                WHERE   ClaimId =   v_ClaimId
                ;
            END IF;
        -- ------------------------------
        -- 5-7.最終請求額 > 入金済額 > 最低請求額 の場合
        -- ------------------------------
        ELSEIF  v_ClaimAmount > v_ReceiptAmount AND v_ReceiptAmount > v_MinClaimAmount  THEN
            -- ------------------------------
            -- 5-7-1.雑損失のﾃﾞｰﾀを取得
            -- ------------------------------
            -- 取得件数が1件とは限らないため、OrderSeq に紐づく雑損失ﾃﾞｰﾀのｻﾏﾘを取得する
            SELECT  SUM(SundryAmount)                   -- 金額
                ,   SUM(CheckingUseAmount)              -- 消込情報－利用額
                ,   SUM(CheckingClaimFee)               -- 消込情報－請求手数料
                ,   SUM(CheckingDamageInterestAmount)   -- 消込情報－遅延損害金
                ,   SUM(CheckingAdditionalClaimFee)     -- 消込情報－請求追加手数料
                ,   COUNT(*)
            INTO    v_SundryAmount
                ,   v_SundryUseAmount
                ,   v_SundryClaimFee
                ,   v_SundryDamageInterestAmount
                ,   v_SundryAdditionalClaimFee
                ,   v_Cnt
            FROM    T_SundryControl
            WHERE   OrderSeq    =   pi_order_seq
            ;

            IF  v_Cnt = 0   THEN
                SET po_ret_sts  =   -1;
                SET po_ret_msg  =   '雑損失データが存在しません。';
                LEAVE proc;
            END IF;

            -- ------------------------------
            -- 5-7-2.雑損失金額 > 入金額 の場合
            -- ------------------------------
            IF  v_SundryAmount > pi_receipt_amount  THEN
                -- ------------------------------
                -- 5-7-2-1.赤伝用消込情報を計算
                -- ------------------------------
                -- ++++++++++++++++++++++++++++++++++++++++
                -- 1. 消込情報－請求追加手数料 を求める
                -- ++++++++++++++++++++++++++++++++++++++++
                -- 1) 消込情報－請求追加手数料 が 入金額 以上の場合
                IF  v_SundryAdditionalClaimFee >= pi_receipt_amount THEN
                    -- 消込情報－請求追加手数料 は 入金額
                    SET v_CheckingAdditionalClaimFee = pi_receipt_amount;
                    -- 残金は 0（ｾﾞﾛ）
                    SET v_CalculationAmount = 0;
                -- それ以外
                ELSE
                    SET v_CheckingAdditionalClaimFee = v_SundryAdditionalClaimFee;
                    SET v_CalculationAmount = pi_receipt_amount - v_CheckingAdditionalClaimFee;
                END IF;

                -- 1.で残金が存在する場合
                IF  v_CalculationAmount > 0 THEN
                    -- ++++++++++++++++++++++++++++++++++++++++
                    -- 2. 消込情報－遅延損害金 を求める
                    -- ++++++++++++++++++++++++++++++++++++++++
                    -- 1) 消込情報－遅延損害金 が 残金 以上の場合
                    IF  v_SundryDamageInterestAmount >= v_CalculationAmount THEN
                        -- 消込情報－遅延損害金 は 残金
                        SET v_CheckingDamageInterestAmount = v_CalculationAmount;
                        -- 残金は 0（ｾﾞﾛ）
                        SET v_CalculationAmount = 0;
                    -- それ以外
                    ELSE
                        SET v_CheckingDamageInterestAmount = v_SundryDamageInterestAmount;
                        SET v_CalculationAmount = v_CalculationAmount - v_CheckingDamageInterestAmount;
                    END IF;
                END IF;

                -- 2.で残金が存在する場合
                IF  v_CalculationAmount > 0 THEN
                    -- ++++++++++++++++++++++++++++++++++++++++
                    -- 3. 消込情報－請求手数料 を求める
                    -- ++++++++++++++++++++++++++++++++++++++++
                    -- 1) 消込情報－請求手数料 が 残金 以上の場合
                    IF  v_SundryClaimFee >= v_CalculationAmount THEN
                        -- 消込情報－請求手数料 は 残金
                        SET v_CheckingClaimFee = v_CalculationAmount;
                        -- 残金は 0（ｾﾞﾛ）
                        SET v_CalculationAmount = 0;
                    -- それ以外
                    ELSE
                        SET v_CheckingClaimFee = v_SundryClaimFee;
                        SET v_CalculationAmount = v_CalculationAmount - v_CheckingClaimFee;
                    END IF;
                END IF;

                -- 3.で残金が存在する場合
                IF  v_CalculationAmount > 0 THEN
                    -- ++++++++++++++++++++++++++++++++++++++++
                    -- 4. 消込情報－利用額 を求める
                    -- ++++++++++++++++++++++++++++++++++++++++
                    -- 1) 消込情報－利用額 が 残金 以上の場合
                    IF  v_SundryUseAmount >= v_CalculationAmount    THEN
                        -- 消込情報－利用額 は 残金
                        SET v_CheckingUseAmount = v_CalculationAmount;
                        -- 残金は 0（ｾﾞﾛ）
                        SET v_CalculationAmount = 0;
                    -- それ以外
                    ELSE
                        SET v_CheckingUseAmount = v_SundryUseAmount;
                        SET v_CalculationAmount = v_CalculationAmount - v_CheckingUseAmount;
                    END IF;
                END IF;

                -- ++++++++++++++++++++++++++++++++++++++++
                -- 5. 消込情報－消込金額合計 を求める
                -- ++++++++++++++++++++++++++++++++++++++++
                SET v_CheckingClaimAmount = v_CheckingUseAmount + v_CheckingClaimFee + v_CheckingDamageInterestAmount + v_CheckingAdditionalClaimFee;

                -- ------------------------------
                -- 5-7-2-2.雑損失赤伝ﾃﾞｰﾀの作成
                -- ------------------------------
                -- 取得した消込情報に-1を掛ける
                INSERT
                INTO    T_SundryControl(    ProcessDate                             -- 発生日時
                                        ,   SundryType                              -- 種類（雑収入／雑損失）
                                        ,   SundryAmount                            -- 金額
                                        ,   SundryClass                             -- 雑収入・雑損失科目
                                        ,   OrderSeq                                -- 注文SEQ
                                        ,   OrderId                                 -- 注文ID
                                        ,   ClaimId                                 -- 請求ID
                                        ,   Note                                    -- 備考
                                        ,   CheckingUseAmount                       -- 消込情報－利用額
                                        ,   CheckingClaimFee                        -- 消込情報－請求手数料
                                        ,   CheckingDamageInterestAmount            -- 消込情報－遅延損害金
                                        ,   CheckingAdditionalClaimFee              -- 消込情報－請求追加手数料
                                        ,   DailySummaryFlg                         -- 日次更新ﾌﾗｸﾞ
                                        ,   RegistDate                              -- 登録日時
                                        ,   RegistId                                -- 登録者
                                        ,   UpdateDate                              -- 更新日時
                                        ,   UpdateId                                -- 更新者
                                        ,   ValidFlg                                -- 有効ﾌﾗｸﾞ
                                       )
                                       VALUES
                                       (    DATE(NOW())                             -- 発生日時
                                        ,   1                                       -- 種類（雑収入／雑損失）
                                        ,   v_CheckingClaimAmount * -1              -- 金額
                                        ,   99                                      -- 雑収入・雑損失科目
                                        ,   pi_order_seq                            -- 注文SEQ
                                        ,   v_OrderId                               -- 注文ID
                                        ,   v_ClaimId                               -- 請求ID
                                        ,   NULL                                    -- 備考
                                        ,   v_CheckingUseAmount * -1                -- 消込情報－利用額
                                        ,   v_CheckingClaimFee * -1                 -- 消込情報－請求手数料
                                        ,   v_CheckingDamageInterestAmount * -1     -- 消込情報－遅延損害金
                                        ,   v_CheckingAdditionalClaimFee * -1       -- 消込情報－請求追加手数料
                                        ,   0                                       -- 日次更新ﾌﾗｸﾞ
                                        ,   NOW()                                   -- 登録日時
                                        ,   pi_user_id                              -- 登録者
                                        ,   NOW()                                   -- 更新日時
                                        ,   pi_user_id                              -- 更新者
                                        ,   1                                       -- 有効ﾌﾗｸﾞ
                                       );

                -- ------------------------------
                -- 5-7-2-3.請求ﾃﾞｰﾀの更新
                -- ------------------------------
                UPDATE  T_ClaimControl
                SET     LastProcessDate     =   DATE(NOW())                                 -- 最終入金処理日
                    ,   LastReceiptSeq      =   v_ReceiptSeq                                -- 最終入金SEQ
                    ,   ReceiptAmountTotal  =   ReceiptAmountTotal + pi_receipt_amount      -- 入金額合計
                    ,   SundryLossTotal     =   SundryLossTotal - v_CheckingClaimAmount     -- 雑損失合計
                    ,   UpdateDate          =   NOW()
                    ,   UpdateId            =   pi_user_id
                WHERE   ClaimId =   v_ClaimId
                ;

            -- ------------------------------
            -- 5-7-3.入金額 >= 雑損失金額 の場合
            -- ------------------------------
            ELSEIF  pi_receipt_amount >= v_SundryAmount THEN
                -- ------------------------------
                -- 5-7-3-1.雑損失赤伝ﾃﾞｰﾀの作成
                -- ------------------------------
                -- 取得した雑損失額に-1を掛ける
                INSERT
                INTO    T_SundryControl(    ProcessDate                         -- 発生日時
                                        ,   SundryType                          -- 種類（雑収入／雑損失）
                                        ,   SundryAmount                        -- 金額
                                        ,   SundryClass                         -- 雑収入・雑損失科目
                                        ,   OrderSeq                            -- 注文SEQ
                                        ,   OrderId                             -- 注文ID
                                        ,   ClaimId                             -- 請求ID
                                        ,   Note                                -- 備考
                                        ,   CheckingUseAmount                   -- 消込情報－利用額
                                        ,   CheckingClaimFee                    -- 消込情報－請求手数料
                                        ,   CheckingDamageInterestAmount        -- 消込情報－遅延損害金
                                        ,   CheckingAdditionalClaimFee          -- 消込情報－請求追加手数料
                                        ,   DailySummaryFlg                     -- 日次更新ﾌﾗｸﾞ
                                        ,   RegistDate                          -- 登録日時
                                        ,   RegistId                            -- 登録者
                                        ,   UpdateDate                          -- 更新日時
                                        ,   UpdateId                            -- 更新者
                                        ,   ValidFlg                            -- 有効ﾌﾗｸﾞ
                                       )
                                       VALUES
                                       (    DATE(NOW())                         -- 発生日時
                                        ,   1                                   -- 種類（雑収入／雑損失）
                                        ,   v_SundryAmount * -1                 -- 金額
                                        ,   99                                  -- 雑収入・雑損失科目
                                        ,   pi_order_seq                        -- 注文SEQ
                                        ,   v_OrderId                           -- 注文ID
                                        ,   v_ClaimId                           -- 請求ID
                                        ,   NULL                                -- 備考
                                        ,   v_SundryUseAmount * -1              -- 消込情報－利用額
                                        ,   v_SundryClaimFee * -1               -- 消込情報－請求手数料
                                        ,   v_SundryDamageInterestAmount * -1   -- 消込情報－遅延損害金
                                        ,   v_SundryAdditionalClaimFee * -1     -- 消込情報－請求追加手数料
                                        ,   0                                   -- 日次更新ﾌﾗｸﾞ
                                        ,   NOW()                               -- 登録日時
                                        ,   pi_user_id                          -- 登録者
                                        ,   NOW()                               -- 更新日時
                                        ,   pi_user_id                          -- 更新者
                                        ,   1                                   -- 有効ﾌﾗｸﾞ
                                       );

                -- ------------------------------
                -- 5-7-3-2.入金額 = 雑損失金額 の場合
                -- ------------------------------
                IF  pi_receipt_amount = v_SundryAmount  THEN
                    -- ------------------------------
                    -- 5-7-3-2-1.請求ﾃﾞｰﾀの更新
                    -- ------------------------------
                    UPDATE  T_ClaimControl
                    SET     LastProcessDate     =   DATE(NOW())                                 -- 最終入金処理日
                        ,   LastReceiptSeq      =   v_ReceiptSeq                                -- 最終入金SEQ
                        ,   ReceiptAmountTotal  =   ReceiptAmountTotal + pi_receipt_amount      -- 入金額合計
                        ,   SundryLossTotal     =   SundryLossTotal - v_SundryAmount            -- 雑損失合計
                        ,   UpdateDate          =   NOW()
                        ,   UpdateId            =   pi_user_id
                    WHERE   ClaimId =   v_ClaimId
                    ;
                END IF;

                -- ------------------------------
                -- 5-7-3-3.入金額 > 雑損失金額 の場合
                -- ------------------------------
                IF  pi_receipt_amount > v_SundryAmount  THEN
                    -- ------------------------------
                    -- 5-7-2-3.請求ﾃﾞｰﾀの更新
                    -- ------------------------------
                    -- 過剰分を足しこむ
                    UPDATE  T_ClaimControl
                    SET     ClaimedBalance      =   ClaimedBalance - v_InsReceiptUseAmount          -- 請求残高
                        ,   LastProcessDate     =   DATE(NOW())                                     -- 最終入金処理日
                        ,   LastReceiptSeq      =   v_ReceiptSeq                                    -- 最終入金SEQ
                        ,   CheckingClaimAmount =   CheckingClaimAmount + v_InsReceiptUseAmount     -- 消込情報－消込金額合計
                        ,   CheckingUseAmount   =   CheckingUseAmount + v_InsReceiptUseAmount       -- 消込情報－利用額
                        ,   BalanceClaimAmount  =   BalanceClaimAmount - v_InsReceiptUseAmount      -- 残高情報－残高合計
                        ,   BalanceUseAmount    =   BalanceUseAmount - v_InsReceiptUseAmount        -- 残高情報－利用額
                        ,   ReceiptAmountTotal  =   ReceiptAmountTotal + pi_receipt_amount          -- 入金額合計
                        ,   SundryLossTotal     =   SundryLossTotal - v_SundryAmount                -- 雑損失合計
                        ,   UpdateDate          =   NOW()
                        ,   UpdateId            =   pi_user_id
                    WHERE   ClaimId =   v_ClaimId
                    ;
                END IF;
            END IF;
        END IF;
    END IF;
END$$

