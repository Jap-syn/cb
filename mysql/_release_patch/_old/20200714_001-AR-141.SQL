/* コード識別管理マスター登録(SBペイメントサービス用設定) */
INSERT INTO M_CodeManagement (CodeId, CodeName, KeyPhysicalName, KeyLogicName, Class1ValidFlg, Class1Name, Class2ValidFlg, Class2Name, Class3ValidFlg, Class3Name, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 'SBペイメントエラー(決済手段)', NULL, 'SBペイメントエラー(決済手段)', 0, '決済手段コード', 0, NULL, 0, NULL, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_CodeManagement (CodeId, CodeName, KeyPhysicalName, KeyLogicName, Class1ValidFlg, Class1Name, Class2ValidFlg, Class2Name, Class3ValidFlg, Class3Name, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 'SBペイメントエラー(種別)', NULL, 'SBペイメントエラー(種別)', 0, '種別コード', 0, '決済手段コード', 0, NULL, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_CodeManagement (CodeId, CodeName, KeyPhysicalName, KeyLogicName, Class1ValidFlg, Class1Name, Class2ValidFlg, Class2Name, Class3ValidFlg, Class3Name, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 'SBペイメントエラー(項目)', NULL, 'SBペイメントエラー(項目)', 0, '項目コード', 0, '決済手段コード', 0, NULL, NOW(), 9, NOW(), 9, '1');

/* コードマスター登録(SBペイメントサービス用設定) */
/* (決済手段) */
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 1, 'クレジットカード決済', '101', NULL, NULL, 'クレジットカード決済', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 2, 'Yahoo!ウォレット決済', '304', NULL, NULL, 'Yahoo!ウォレット決済', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 3, '楽天ペイ（オンライン決済）', '305', NULL, NULL, '楽天ペイ（オンライン決済）', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 4, 'ペイパル決済', '306', NULL, NULL, 'ペイパル決済', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 5, 'Yahoo!ウォレット決済デジコン版', '307', NULL, NULL, 'Yahoo!ウォレット決済デジコン版', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 6, 'リクルートかんたん支払い', '309', NULL, NULL, 'リクルートかんたん支払い', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 7, 'LINE Pay', '310', NULL, NULL, 'LINE Pay', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 8, 'PayPay（オンライン決済）', '311', NULL, NULL, 'PayPay（オンライン決済）', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 9, 'ドコモ払い', '401', NULL, NULL, 'ドコモ払い', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 10, 'au かんたん決済', '402', NULL, NULL, 'au かんたん決済', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 11, 'ソフトバンクまとめて支払い（B）', '405', NULL, NULL, 'ソフトバンクまとめて支払い（B）', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 12, 'Alipay 国際決済', '510', NULL, NULL, 'Alipay 国際決済', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 13, 'JCB PREMO 決済・ハウスプリペイド決済', '513', NULL, NULL, 'JCB PREMO 決済・ハウスプリペイド決済', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 14, '銀聯ネット決済', '514', NULL, NULL, '銀聯ネット決済', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 15, 'Apple Pay', '601', NULL, NULL, 'Apple Pay', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 16, 'Google Pay', '604', NULL, NULL, 'Google Pay', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 17, 'コンビニ決済', '701', NULL, NULL, 'コンビニ決済', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 18, '総合振込決済', '702', NULL, NULL, '総合振込決済', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 19, 'Pay-easy 決済', '703', NULL, NULL, 'Pay-easy 決済', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 20, 'NP 後払い', '710', NULL, NULL, 'NP 後払い', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 21, '永久不滅ポイント決済', '805', NULL, NULL, '永久不滅ポイント決済', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 22, 'T ポイントプログラム（オンライン決済）', '806', NULL, NULL, 'T ポイントプログラム（オンライン決済）', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 23, 'メールリンク型', '912', NULL, NULL, 'メールリンク型', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (200, 24, '共通', '999', NULL, NULL, '共通', 0, NOW(), 9, NOW(), 9, '1');
/* (種別) */
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 1, '支払方法有効性エラー', '01', NULL, NULL, '支払方法有効性エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 2, 'API リクエストID 存在エラー', '02', NULL, NULL, 'API リクエストID 存在エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 3, '必須エラー', '03', NULL, NULL, '必須エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 4, '属性エラー', '04', NULL, NULL, '属性エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 5, '桁数エラー', '05', NULL, NULL, '桁数エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 6, '形式エラー', '06', NULL, NULL, '形式エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 7, '定義値エラー', '07', NULL, NULL, '定義値エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 8, '(使用していないエラーコード)', '08', NULL, NULL, '(使用していないエラーコード)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 9, 'ハッシュ値エラー', '09', NULL, NULL, 'ハッシュ値エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 10, 'リクエスト有効期限エラー', '10', NULL, NULL, 'リクエスト有効期限エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 11, '処理対象SBPS トランザクションID エラー', '11', NULL, NULL, '処理対象SBPS トランザクションID エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 12, '処理対象トラッキングID エラー', '12', NULL, NULL, '処理対象トラッキングID エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 13, '指定不可エラー', '13', NULL, NULL, '指定不可エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 14, 'マーチャントID・サービスID 不正エラー', '14', NULL, NULL, 'マーチャントID・サービスID 不正エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 15, 'ワンタイムトークンの復号化に失敗しました', 'T1', NULL, NULL, 'ワンタイムトークンの復号化に失敗しました', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 16, 'ご指定のワンタイムトークンは有効期限切れのため使用できません', 'T2', NULL, NULL, 'ご指定のワンタイムトークンは有効期限切れのため使用できません。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 17, 'GW システムエラー', '80', NULL, NULL, 'GW システムエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 18, 'API リクエストパラメータエラー', '81', NULL, NULL, 'API リクエストパラメータエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 19, '(使用していないエラーコード)', '82', NULL, NULL, '(使用していないエラーコード)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 20, 'GW レコード検索エラー', '83', NULL, NULL, 'GW レコード検索エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 21, '決済会社レスポンスパラメータエラー', '84', NULL, NULL, '決済会社レスポンスパラメータエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 22, '決済会社接続エラー', '85', NULL, NULL, '決済会社接続エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 23, '決済会社システムエラー', '86', NULL, NULL, '決済会社システムエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 24, 'API システムエラー', '90', NULL, NULL, 'API システムエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 25, '(使用していないエラーコード)', '91', NULL, NULL, '(使用していないエラーコード)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 26, 'GW 接続エラー', '92', NULL, NULL, 'GW 接続エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 27, '再入力上限回数制限エラー', '93', NULL, NULL, '再入力上限回数制限エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 28, '決済未完了エラー', '94', NULL, NULL, '決済未完了エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 29, '顧客情報整合性エラー', '95', NULL, NULL, '顧客情報整合性エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 30, '2 重リクエストエラー', '96', NULL, NULL, '2 重リクエストエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 31, '永久トークン整合性エラー', '98', NULL, NULL, '永久トークン整合性エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 32, 'クレジットカード不正カードフィルタエラー', '20', '101', NULL, 'クレジットカード不正カードフィルタエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 33, 'クレジットセンターエラー', '21', '101', NULL, 'クレジットセンターエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 34, 'クレジットカード利用限度額超過', '22', '101', NULL, 'クレジットカード利用限度額超過', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 35, 'クレジットカード使用不可', '23', '101', NULL, 'クレジットカード使用不可', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 36, '暗証番号不正', '24', '101', NULL, '暗証番号不正', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 37, 'クレジットカード利用限度回数超過', '25', '101', NULL, 'クレジットカード利用限度回数超過', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 38, 'クレジットカード取扱不可', '26', '101', NULL, 'クレジットカード取扱不可', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 39, 'クレジットカード番号・有効期限誤り', '27', '101', NULL, 'クレジットカード番号・有効期限誤り', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 40, '取引内容取扱不可', '28', '101', NULL, '取引内容取扱不可', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 41, '指定ボーナス回数利用不可', '29', '101', NULL, '指定ボーナス回数利用不可', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 42, '指定ボーナス月利用不可', '30', '101', NULL, '指定ボーナス月利用不可', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 43, '指定ボーナス金額利用不可', '31', '101', NULL, '指定ボーナス金額利用不可', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 44, '指定支払開始月利用不可', '32', '101', NULL, '指定支払開始月利用不可', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 45, '指定分割回数利用不可', '33', '101', NULL, '指定分割回数利用不可', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 46, '指定分割金額利用不可', '34', '101', NULL, '指定分割金額利用不可', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 47, '指定初回お支払い金額利用不可', '35', '101', NULL, '指定初回お支払い金額利用不可', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 48, 'その他与信エラー', '36', '101', NULL, 'その他与信エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 49, '自動売上が設定されているため、売上要求は不要です。', '37', '101', NULL, '自動売上が設定されているため、売上要求は不要です。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 50, 'G 式の場合、ファイルにて売上処理を行ってください。', '38', '101', NULL, 'G 式の場合、ファイルにて売上処理を行ってください。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 51, '与信結果が存在しないため、売上処理を中止しました。', '39', '101', NULL, '与信結果が存在しないため、売上処理を中止しました。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 52, '与信取消済みのため、売上処理を中止しました。', '40', '101', NULL, '与信取消済みのため、売上処理を中止しました。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 53, '売上処理が完了済みのため、処理を中止しました。', '41', '101', NULL, '売上処理が完了済みのため、処理を中止しました。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 54, '売上処理の処理日時は、与信日から3 ヶ月目末日まで有効です。', '42', '101', NULL, '売上処理の処理日時は、与信日から3 ヶ月目末日まで有効です。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 55, 'G 式の場合、ファイルにて返金処理を行ってください。', '43', '101', NULL, 'G 式の場合、ファイルにて返金処理を行ってください。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 56, '与信結果が存在しないため、返金処理を中止しました。', '44', '101', NULL, '与信結果が存在しないため、返金処理を中止しました。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 57, '与信取消済みのため、返金処理を中止しました。', '45', '101', NULL, '与信取消済みのため、返金処理を中止しました。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 58, '返金処理済みのため、返金処理を中止しました。', '46', '101', NULL, '返金処理済みのため、返金処理を中止しました。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 59, '継続課金中のため、返金処理を中止しました。', '47', '101', NULL, '継続課金中のため、返金処理を中止しました。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 60, '(使用していないエラーコード)', '48', '101', NULL, '(使用していないエラーコード)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 61, '自動売上の場合、返金要求API を使用して下さい。', '49', '101', NULL, '自動売上の場合、返金要求API を使用して下さい。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 62, 'P 式の場合、返金要求API を使用して下さい。', '50', '101', NULL, 'P 式の場合、返金要求API を使用して下さい。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 63, '与信結果が存在しないため、与信取消処理を中止しました。', '51', '101', NULL, '与信結果が存在しないため、与信取消処理を中止しました。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 64, '与信取消済みのため、与信取消処理を中止しました。', '52', '101', NULL, '与信取消済みのため、与信取消処理を中止しました。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 65, '(使用していないエラーコード)', '53', '101', NULL, '(使用していないエラーコード)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 66, '継続課金中のため、与信取消処理を中止しました。', '54', '101', NULL, '継続課金中のため、与信取消処理を中止しました。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 67, '(使用していないエラーコード)', '55', '101', NULL, '(使用していないエラーコード)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 68, '返金処理は、売上日以降かつ、与信取消日から3 ヶ月目末日まで有効です。', '56', '101', NULL, '返金処理は、売上日以降かつ、与信取消日から3 ヶ月目末日まで有効です。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 69, 'ご指定の継続課金は既に解約済みです。', '57', '101', NULL, 'ご指定の継続課金は既に解約済みです。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 70, '継続課金使用中エラー', '58', '101', NULL, '継続課金使用中エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 71, '自動売上(コミットフラグ適用)の場合、コミット実施後、返金処理を実施して下さい。', '59', '101', NULL, '自動売上(コミットフラグ適用)の場合、コミット実施後、返金処理を実施して下さい。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 72, '既に処理が完了しているため、コミット(取消)を実施出来ません。', '60', '101', NULL, '既に処理が完了しているため、コミット(取消)を実施出来ません。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 73, 'セキュリティコード誤り', '61', '101', NULL, 'セキュリティコード誤り', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 74, '(使用していないエラーコード)', '62', '101', NULL, '(使用していないエラーコード)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 75, '認証アシスト情報必須エラー （SmartLink 利用時対象）', '63', '101', NULL, '認証アシスト情報必須エラー （SmartLink 利用時対象）', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 76, 'SmartLink センターエラー （SmartLink 利用時対象）', '64', '101', NULL, 'SmartLink センターエラー （SmartLink 利用時対象）', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 77, '決済会社判定エラー （SmartLink 利用時対象）', '65', '101', NULL, '決済会社判定エラー （SmartLink 利用時対象）', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 78, '決済会社判定エラー （SmartLink 利用時対象）', '66', '101', NULL, '決済会社判定エラー （SmartLink 利用時対象）', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 79, '決済会社判定エラー （SmartLink 利用時対象）', '67', '101', NULL, '決済会社判定エラー （SmartLink 利用時対象）', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 80, '決済会社判定エラー （SmartLink 利用時対象）', '68', '101', NULL, '決済会社判定エラー （SmartLink 利用時対象）', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 81, '決済会社判定エラー （SmartLink 利用時対象）', '69', '101', NULL, '決済会社判定エラー （SmartLink 利用時対象）', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 82, '決済会社判定エラー （SmartLink 利用時対象）', '70', '101', NULL, '決済会社判定エラー （SmartLink 利用時対象）', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 83, '決済会社判定エラー （SmartLink 利用時対象）', '71', '101', NULL, '決済会社判定エラー （SmartLink 利用時対象）', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 84, '決済会社判定エラー （SmartLink 利用時対象）', '72', '101', NULL, '決済会社判定エラー （SmartLink 利用時対象）', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 85, '決済会社判定エラー （SmartLink 利用時対象）', '73', '101', NULL, '決済会社判定エラー （SmartLink 利用時対象）', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 86, '決済会社判定エラー （SmartLink 利用時対象）', '74', '101', NULL, '決済会社判定エラー （SmartLink 利用時対象）', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 87, '決済会社判定エラー （SmartLink 利用時対象）', '75', '101', NULL, '決済会社判定エラー （SmartLink 利用時対象）', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 88, '決済会社判定エラー （SmartLink 利用時対象）', '76', '101', NULL, '決済会社判定エラー （SmartLink 利用時対象）', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 89, '決済会社判定エラー （SmartLink 利用時対象）', '77', '101', NULL, '決済会社判定エラー （SmartLink 利用時対象）', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 90, 'クレジットカード情報保存フラグ指定エラー', '78', '101', NULL, 'クレジットカード情報保存フラグ指定エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 91, '指定された金額が、与信時金額を越えているため、売上処理を中止しました。', '79', '101', NULL, '指定された金額が、与信時金額を越えているため、売上処理を中止しました。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 92, '売上処理未実施のため、部分返金処理を中止しました。', 'K0', '101', NULL, '売上処理未実施のため、部分返金処理を中止しました。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 93, '返金金額の合計が、売上時金額を超えているため、部分返金処理を中止しました。', 'K1', '101', NULL, '返金金額の合計が、売上時金額を超えているため、部分返金処理を中止しました。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 94, '決済会社判定エラー', 'K2', '101', NULL, '決済会社判定エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 95, 'クレジットカードブランド返却フラグ指定エラー', 'K3', '101', NULL, 'クレジットカードブランド返却フラグ指定エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 96, '与信結果が存在しないため、NG 与信情報変更処理を中止しました。', 'K4', '101', NULL, '与信結果が存在しないため、NG 与信情報変更処理を中止しました。', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (201, 97, '正常与信済みのため、NG 与信情報変更処理を中止しました。', 'K5', '101', NULL, '正常与信済みのため、NG 与信情報変更処理を中止しました。', 0, NOW(), 9, NOW(), 9, '1');
/* (項目) */
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 1, 'API リクエストID', '000', NULL, NULL, 'API リクエストID', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 2, 'マーチャントID', '001', NULL, NULL, 'マーチャントID', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 3, 'サービスID', '002', NULL, NULL, 'サービスID', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 4, '顧客ID', '003', NULL, NULL, '顧客ID', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 5, '購入ID', '004', NULL, NULL, '購入ID', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 6, '商品ID', '005', NULL, NULL, '商品ID', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 7, '商品名称', '006', NULL, NULL, '商品名称', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 8, '税額', '007', NULL, NULL, '税額', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 9, '金額(税込)', '008', NULL, NULL, '金額(税込)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 10, '結果通知先CGI', '009', NULL, NULL, '結果通知先CGI', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 11, '自由欄1', '010', NULL, NULL, '自由欄1', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 12, '自由欄2', '011', NULL, NULL, '自由欄2', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 13, '自由欄3', '012', NULL, NULL, '自由欄3', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 14, '請求番号枝番', '013', NULL, NULL, '請求番号枝番', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 15, '明細行番号', '014', NULL, NULL, '明細行番号', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 16, '明細商品ID', '015', NULL, NULL, '明細商品ID', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 17, '明細商品名称', '016', NULL, NULL, '明細商品名称', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 18, '明細数量', '017', NULL, NULL, '明細数量', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 19, '明細税額', '018', NULL, NULL, '明細税額', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 20, '明細金額(税込)', '019', NULL, NULL, '明細金額(税込)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 21, '決済区分', '020', NULL, NULL, '決済区分', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 22, '最終課金月', '021', NULL, NULL, '最終課金月', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 23, 'キャンペーンタイプ', '022', NULL, NULL, 'キャンペーンタイプ', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 24, 'リクエスト日時', '023', NULL, NULL, 'リクエスト日時', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 25, 'リクエスト許容時間', '024', NULL, NULL, 'リクエスト許容時間', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 26, 'チェックサム', '025', NULL, NULL, 'チェックサム', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 27, '処理対象SBPS トランザクションID', '026', NULL, NULL, '処理対象SBPS トランザクションID', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 28, '処理対象トラッキングID', '027', NULL, NULL, '処理対象トラッキングID', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 29, '3DES 暗号化フラグ', '028', NULL, NULL, '3DES 暗号化フラグ', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 30, '処理日時', '029', NULL, NULL, '処理日時', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 31, 'SBPS 顧客情報返却フラグ', '030', NULL, NULL, 'SBPS 顧客情報返却フラグ', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 32, '該当項目なし', '999', NULL, NULL, '該当項目なし', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 33, 'クレジットカード番号', '200', '101', NULL, 'クレジットカード番号', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 34, 'クレジットカード有効期限', '201', '101', NULL, 'クレジットカード有効期限', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 35, '取引区分', '202', '101', NULL, '取引区分', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 36, '分割回数', '203', '101', NULL, '分割回数', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 37, 'ボーナス併用回数', '204', '101', NULL, 'ボーナス併用回数', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 38, 'クレジットカード情報保存フラグ', '205', '101', NULL, 'クレジットカード情報保存フラグ', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 39, '備考欄1', '206', '101', NULL, '備考欄1', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 40, '備考欄2', '207', '101', NULL, '備考欄2', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 41, '備考欄3', '208', '101', NULL, '備考欄3', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 42, 'セキュリティコード', '209', '101', NULL, 'セキュリティコード', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 43, '(使用していないエラーコード)', '210', '101', NULL, '(使用していないエラーコード)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 44, '(使用していないエラーコード)', '211', '101', NULL, '(使用していないエラーコード)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 45, '(使用していないエラーコード)', '212', '101', NULL, '(使用していないエラーコード)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 46, '(使用していないエラーコード)', '213', '101', NULL, '(使用していないエラーコード)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 47, '決済情報使用特定タイプ', '214', '101', NULL, '決済情報使用特定タイプ', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 48, '決済支払方法使用特定タイプ', '215', '101', NULL, '決済支払方法使用特定タイプ', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 49, '(使用していないエラーコード)', '216', '101', NULL, '(使用していないエラーコード)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 50, 'クレジットカードブランド返却フラグ', '217', '101', NULL, 'クレジットカードブランド返却フラグ', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 51, '(使用していないエラーコード)', '218', '101', NULL, '(使用していないエラーコード)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 52, '(使用していないエラーコード)', '219', '101', NULL, '(使用していないエラーコード)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 53, '(使用していないエラーコード)', '220', '101', NULL, '(使用していないエラーコード)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 54, '(使用していないエラーコード)', '221', '101', NULL, '(使用していないエラーコード)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 55, '(使用していないエラーコード)', '222', '101', NULL, '(使用していないエラーコード)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 56, '(使用していないエラーコード)', '223', '101', NULL, '(使用していないエラーコード)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 57, '(使用していないエラーコード)', '224', '101', NULL, '(使用していないエラーコード)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 58, '返金枝番', '225', '101', NULL, '返金枝番', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 59, '(使用していないエラーコード)', '226', '101', NULL, '(使用していないエラーコード)', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 60, 'トークン', '227', '101', NULL, 'トークン', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 61, 'トークンキー', '228', '101', NULL, 'トークンキー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 62, '永久トークン', '229', '101', NULL, '永久トークン', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (202, 63, '(使用していないエラーコード)', '900', '101', NULL, '(使用していないエラーコード)', 0, NOW(), 9, NOW(), 9, '1');

/* システムプロパティ登録(SBペイメントサービス用設定) */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','sbpayment', 'tokenurl', 'http://localhost/orderpage/../js/orderpage/com_sbps_system_token_ex.js', 'トークン生成URL', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','sbpayment', 'url', 'http://localhost/settele/', 'オンライン決済URL', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','sbpayment', 'timeout', '10', 'タイムアウト', NOW(), 9, NOW(), 9, '1');

/* 加盟店テーブルに[マーチャントID][サービスID][ハッシュキー]を追加 */
ALTER TABLE `T_Enterprise` ADD COLUMN `MerchantId` varchar(10) DEFAULT NULL AFTER `TargetListLimit`;
ALTER TABLE `T_Enterprise` ADD COLUMN `ServiceId` varchar(10) DEFAULT NULL AFTER `MerchantId`;
ALTER TABLE `T_Enterprise` ADD COLUMN `HashKey` varchar(100) DEFAULT NULL AFTER `ServiceId`;
ALTER TABLE `T_Enterprise` ADD COLUMN `BasicId` varchar(10) DEFAULT NULL AFTER `HashKey`;
ALTER TABLE `T_Enterprise` ADD COLUMN `BasicPw` varchar(100) DEFAULT NULL AFTER `BasicId`;

/* サイトテーブルに[ロゴ2][特定取引URL][クレジット決済専用決済手数料率][クレジット決済専用請求手数料(別送)][クレジット決済専用請求手数料(同梱)]を追加 */
ALTER TABLE `T_Site` ADD COLUMN `SmallLogo` text AFTER `ReceiptIssueProviso`;
ALTER TABLE `T_Site` ADD COLUMN `SpecificTransUrl` varchar(255) DEFAULT NULL AFTER `SmallLogo`;
ALTER TABLE `T_Site` ADD COLUMN `CSSettlementFeeRate` decimal(16,5) DEFAULT NULL AFTER `SpecificTransUrl`;
ALTER TABLE `T_Site` ADD COLUMN `CSClaimFeeBS` int(11) DEFAULT NULL AFTER `CSSettlementFeeRate`;
ALTER TABLE `T_Site` ADD COLUMN `CSClaimFeeDK` int(11) DEFAULT NULL AFTER `CSClaimFeeBS`;

-- ---------------------------
-- マイページスキーマ向け
-- ---------------------------
-- 基幹反映指示インタフェース
ALTER TABLE T_MypageToBackIF ADD COLUMN `MailFlg` INT  DEFAULT 0 AFTER `CustomerId`;
ALTER TABLE T_MypageToBackIF ADD COLUMN `MailRetryCount` INT  DEFAULT 0 AFTER `MailFlg`;

-- Viewの再構成 ※運用環境、公開環境はスキーマが異なるので注意
DROP VIEW IF EXISTS `MAV_Order`;

CREATE VIEW `MAV_Order` AS
  SELECT *
    FROM coraldb_new01.AT_Order
;

DROP VIEW IF EXISTS `MV_ClaimHistory`;

CREATE VIEW `MV_ClaimHistory` AS
    SELECT *
    FROM coraldb_new01.T_ClaimHistory
;

DROP VIEW IF EXISTS `MV_Site`;

CREATE VIEW `MV_Site` AS
    SELECT *
    FROM coraldb_new01.T_Site
;

DROP VIEW IF EXISTS `MV_ClaimControl`;

CREATE VIEW `MV_ClaimControl` AS
    SELECT *
    FROM coraldb_new01.T_ClaimControl
;

DROP VIEW IF EXISTS `MV_Enterprise`;

CREATE VIEW `MV_Enterprise` AS
    SELECT *
    FROM coraldb_new01.T_Enterprise
;
