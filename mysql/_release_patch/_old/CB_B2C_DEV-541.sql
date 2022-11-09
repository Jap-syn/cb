INSERT INTO T_Menu VALUES ('213', 'cbadmin', 'keiriMenus', 'creacctrnsmhf', null, '***', '振替請求データ（MHF）作成', '振替請求データ（MHF）作成', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO T_Menu VALUES ('214', 'cbadmin', 'keiriMenus', 'dlacctrnsmhf', null, '***', '振替請求データ（MHF）ダウンロード', '振替請求データ（MHF）ダウンロード', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO T_Menu VALUES ('215', 'cbadmin', 'keiriMenus', 'impacctrnsmhf', null, '***', '振替結果（MHF）インポート', '振替結果（MHF）インポート', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO T_Menu VALUES ('216', 'cbadmin', 'keiriMenus', 'acctrnslistmhf', null, '***', '振替結果（MHF）一覧', '振替結果（MHF）一覧', null, null, null, NOW(), '0', NOW(), '0', '1');

INSERT INTO T_MenuAuthority VALUES ('213', '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('213', '11', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('213', '101', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('213', '110', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('213', '120', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('213', '130', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('213', '140', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('213', '150', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('213', '160', NOW(), '0', NOW(), '0', '1');

INSERT INTO T_MenuAuthority VALUES ('214', '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('214', '11', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('214', '101', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('214', '110', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('214', '120', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('214', '130', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('214', '140', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('214', '150', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('214', '160', NOW(), '0', NOW(), '0', '1');

INSERT INTO T_MenuAuthority VALUES ('215', '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('215', '11', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('215', '101', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('215', '110', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('215', '120', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('215', '130', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('215', '140', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('215', '150', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('215', '160', NOW(), '0', NOW(), '0', '1');

INSERT INTO T_MenuAuthority VALUES ('216', '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('216', '11', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('216', '101', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('216', '110', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('216', '120', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('216', '130', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('216', '140', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('216', '150', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('216', '160', NOW(), '0', NOW(), '0', '1');



INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'CATSConsignorCodeMHF'     , '334930'                                  , '委託者コード(口座振替MHF用　数字6桁入力)', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'CATSConsignorNameMHF'     , 'ｷﾔﾂﾁﾎﾞ-ﾙ                                ', '委託者名(口座振替MUFJ用　半角40桁入力)'  , NOW(), '0', NOW(), '0', '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'CreditTransferSoonDays'   , 10                                        , 'もうすぐ口座振替メール送信タイミング'    , NOW(), '0', NOW(), '0', '1');


INSERT INTO T_MailTemplate(Class, ClassName, FromTitle, FromTitleMime, FromAddress, ToTitle, ToTitleMime, ToAddress, Subject, SubjectMime, Body, OemId, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (111, '口座振替成立メール（正常）(PC)', '後払いドットコム', '=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=', 'customer@ato-barai.com', null, null, null, '口座振替成立メール（正常）', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MailTemplate(Class, ClassName, FromTitle, FromTitleMime, FromAddress, ToTitle, ToTitleMime, ToAddress, Subject, SubjectMime, Body, OemId, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (112, '口座振替成立メール（正常）(CEL)', '後払いドットコム', '=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=', 'customer@ato-barai.com', null, null, null, '口座振替成立メール（正常）', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MailTemplate(Class, ClassName, FromTitle, FromTitleMime, FromAddress, ToTitle, ToTitleMime, ToAddress, Subject, SubjectMime, Body, OemId, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (113, '口座振替成立メール（エラー、中止、登録処理中）(PC)', '後払いドットコム', '=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=', 'customer@ato-barai.com', null, null, null, '口座振替成立メール（エラー、中止、登録処理中）', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MailTemplate(Class, ClassName, FromTitle, FromTitleMime, FromAddress, ToTitle, ToTitleMime, ToAddress, Subject, SubjectMime, Body, OemId, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (114, '口座振替成立メール（エラー、中止、登録処理中）(CEL)', '後払いドットコム', '=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=', 'customer@ato-barai.com', null, null, null, '口座振替成立メール（エラー、中止、登録処理中）', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MailTemplate(Class, ClassName, FromTitle, FromTitleMime, FromAddress, ToTitle, ToTitleMime, ToAddress, Subject, SubjectMime, Body, OemId, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (115, 'もうすぐ口座振替メール(PC)', '後払いドットコム', '=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=', 'customer@ato-barai.com', null, null, null, 'もうすぐ口座振替メール', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MailTemplate(Class, ClassName, FromTitle, FromTitleMime, FromAddress, ToTitle, ToTitleMime, ToAddress, Subject, SubjectMime, Body, OemId, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (116, 'もうすぐ口座振替メール(CEL)', '後払いドットコム', '=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=', 'customer@ato-barai.com', null, null, null, 'もうすぐ口座振替メール', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MailTemplate(Class, ClassName, FromTitle, FromTitleMime, FromAddress, ToTitle, ToTitleMime, ToAddress, Subject, SubjectMime, Body, OemId, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (117, '口座振替　引落完了メール(PC)', '後払いドットコム', '=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=', 'customer@ato-barai.com', null, null, null, '口座振替　引落完了メール', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MailTemplate(Class, ClassName, FromTitle, FromTitleMime, FromAddress, ToTitle, ToTitleMime, ToAddress, Subject, SubjectMime, Body, OemId, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (118, '口座振替　引落完了メール(CEL)', '後払いドットコム', '=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=', 'customer@ato-barai.com', null, null, null, '口座振替　引落完了メール', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MailTemplate(Class, ClassName, FromTitle, FromTitleMime, FromAddress, ToTitle, ToTitleMime, ToAddress, Subject, SubjectMime, Body, OemId, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (119, '支払期限超過メール（口振 再1）(PC)', '後払いドットコム', '=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=', 'customer@ato-barai.com', null, null, null, '支払期限超過メール（口振 再1）', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MailTemplate(Class, ClassName, FromTitle, FromTitleMime, FromAddress, ToTitle, ToTitleMime, ToAddress, Subject, SubjectMime, Body, OemId, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (120, '支払期限超過メール（口振 再1）(CEL)', '後払いドットコム', '=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=', 'customer@ato-barai.com', null, null, null, '支払期限超過メール（口振 再1）', null, null, null, NOW(), '0', NOW(), '0', '1');


INSERT INTO M_Code VALUES (72, 308, '注文ID(件名)'        , '{OrderId}'           , '111', '112', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 309, '購入者名'            , '{CustomerNameKj}'    , '111', '112', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 310, '送料'                , '{DeliveryFee}'       , '111', '112', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 311, '事業者名'            , '{EnterpriseNameKj}'  , '111', '112', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 312, '支払期限'            , '{LimitDate}'         , '111', '112', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 313, '商品名（先頭ひとつ）', '{OneOrderItem}'      , '111', '112', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 314, '注文日'              , '{OrderDate}'         , '111', '112', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 315, '注文ID'              , '{OrderId}'           , '111', '112', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 316, '注文商品リスト'      , '{OrderItems}'        , '111', '112', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 317, '事業者電話番号'      , '{Phone}'             , '111', '112', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 318, '決済手数料'          , '{SettlementFee}'     , '111', '112', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 319, 'サイト名'            , '{SiteNameKj}'        , '111', '112', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 320, '利用額'              , '{UseAmount}'         , '111', '112', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 321, '注文ﾏｲﾍﾟｰｼﾞURL'      , '{OrderPageAccessUrl}', '111', '112', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 322, '通帳表示名'          , '{}'                  , '111', '112', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 323, '引落日'              , '{}'                  , '111', '112', null, '1', NOW(), '0', NOW(), '0', '1');

INSERT INTO M_Code VALUES (72, 324, '注文ID(件名)'        , '{OrderId}'           , '113', '114', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 325, '購入者名'            , '{CustomerNameKj}'    , '113', '114', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 326, '送料'                , '{DeliveryFee}'       , '113', '114', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 327, '事業者名'            , '{EnterpriseNameKj}'  , '113', '114', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 328, '支払期限'            , '{LimitDate}'         , '113', '114', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 329, '商品名（先頭ひとつ）', '{OneOrderItem}'      , '113', '114', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 330, '注文日'              , '{OrderDate}'         , '113', '114', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 331, '注文ID'              , '{OrderId}'           , '113', '114', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 332, '注文商品リスト'      , '{OrderItems}'        , '113', '114', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 333, '事業者電話番号'      , '{Phone}'             , '113', '114', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 334, '決済手数料'          , '{SettlementFee}'     , '113', '114', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 335, 'サイト名'            , '{SiteNameKj}'        , '113', '114', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 336, '利用額'              , '{UseAmount}'         , '113', '114', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 337, '注文ﾏｲﾍﾟｰｼﾞURL'      , '{OrderPageAccessUrl}', '113', '114', null, '1', NOW(), '0', NOW(), '0', '1');

INSERT INTO M_Code VALUES (72, 338, '注文ID(件名)'        , '{OrderId}'           , '115', '116', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 339, '購入者名'            , '{CustomerNameKj}'    , '115', '116', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 340, '初回請求日'          , '{IssueDate}'         , '115', '116', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 341, '初回請求期限'        , '{LimitDate}'         , '115', '116', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 342, '商品名（先頭ひとつ）', '{OneOrderItem}'      , '115', '116', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 343, '注文日'              , '{OrderDate}'         , '115', '116', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 344, '事業者電話番号'      , '{Phone}'             , '115', '116', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 345, 'サイト名'            , '{SiteNameKj}'        , '115', '116', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 346, '利用額'              , '{UseAmount}'         , '115', '116', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 347, '銀行コード'          , '{Bk_BankCode}'       , '115', '116', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 348, '支店コード'          , '{Bk_BranchCode}'     , '115', '116', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 349, '銀行名'              , '{Bk_BankName}'       , '115', '116', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 350, '支店名'              , '{Bk_BranchName}'     , '115', '116', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 351, '口座預金種別'        , '{Bk_DepositClass}'   , '115', '116', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 352, '口座番号'            , '{Bk_AccountNumber}'  , '115', '116', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 353, '口座名義ｶﾅ'          , '{Bk_AccountHolder}'  , '115', '116', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 354, '口座名義ｶﾅ'          , '{Bk_AccountHolderKn}', '115', '116', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 355, '加入者名'            , '{Yu_SubscriberName}' , '115', '116', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 356, '口座番号'            , '{Yu_AccountNumber}'  , '115', '116', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 357, '通帳表示名'          , '{MhfCreditTransferDisplayName}', '115', '116', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 358, '引落日'              , '{CreditTransferDate}', '115', '116', null, '1', NOW(), '0', NOW(), '0', '1');

INSERT INTO M_Code VALUES (72, 359, '事業者住所'          , '{Address}'           , '117', '118', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 360, '購入者名'            , '{CustomerNameKj}'    , '117', '118', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 361, '送料'                , '{DeliveryFee}'       , '117', '118', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 362, '事業者名'            , '{EnterpriseNameKj}'  , '117', '118', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 363, '商品名（先頭ひとつ）', '{OneOrderItem}'      , '117', '118', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 364, '注文日'              , '{OrderDate}'         , '117', '118', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 365, '注文商品リスト'      , '{OrderItems}'        , '117', '118', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 366, '事業者電話番号'      , '{Phone}'             , '117', '118', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 367, '入金確認日'          , '{ReceiptDate}'       , '117', '118', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 368, '決済手数料'          , '{SettlementFee}'     , '117', '118', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 369, 'サイト名'            , '{SiteNameKj}'        , '117', '118', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 370, 'サイトURL'           , '{SiteUrl}'           , '117', '118', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 371, '入金額'              , '{UseAmount}'         , '117', '118', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 372, '通帳表示名'          , '{MhfCreditTransferDisplayName}', '117', '118', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 373, '引落日'              , '{CreditTransferDate}', '117', '118', null, '1', NOW(), '0', NOW(), '0', '1');

INSERT INTO M_Code VALUES (72, 374, '注文日(件名)'        , '{OrderDate}'         , '119', '120', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 375, '注文ID(件名)'        , '{OrderId}'           , '119', '120', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 376, 'サイト名(件名)'      , '{SiteNameKj}'        , '119', '120', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 377, '購入者名'            , '{CustomerNameKj}'    , '119', '120', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 378, '初回請求日'          , '{IssueDate}'         , '119', '120', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 379, '商品名（先頭ひとつ）', '{OneOrderItem}'      , '119', '120', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 380, '注文日'              , '{OrderDate}'         , '119', '120', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 381, '事業者電話番号'      , '{Phone}'             , '119', '120', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 382, 'サイト名'            , '{SiteNameKj}'        , '119', '120', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 383, '合計'                , '{TotalAmount}'       , '119', '120', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 384, '支店名'              , '{Bk_BranchName}'     , '119', '120', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 385, '口座番号'            , '{Bk_AccountNumber}'  , '119', '120', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 386, '口座名義ｶﾅ'          , '{Bk_AccountHolderKn}', '119', '120', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 387, 'お客様番号'          , '{CustomerNumber}'    , '119', '120', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 388, '確認番号'            , '{ConfirmNumber}'     , '119', '120', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 389, '収納機関番号'        , '{Bk_Number}'         , '119', '120', null, '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO M_Code VALUES (72, 390, '引落日'              , '{CreditTransferDate}', '119', '120', null, '1', NOW(), '0', NOW(), '0', '1');

-- ALTER TABLE T_ClaimControl ADD COLUMN `CreditTransferFlg` TINYINT NOT NULL DEFAULT 0 AFTER `CancelNoticePrintStopStatus`
ALTER TABLE T_ClaimControl ADD COLUMN `F_CreditTransferDate` DATE NULL AFTER `CreditTransferFlg`;
ALTER TABLE T_Enterprise ADD COLUMN `MhfCreditTransferDisplayName` VARCHAR(12)  NULL AFTER `ClaimIssueStopFlg`;
