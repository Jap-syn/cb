/* コメント設定にペイジー関連項目追加
/* ドロップダウンバーに追加 */
INSERT INTO M_CodeManagement VALUES(205,'ペイジー決済',NULL,'ペイジー決済',0,NULL,0,NULL,0,NULL,NOW(),1,NOW(),1,1);

/* フィールドに追加*/
INSERT INTO M_Code VALUES(205,1,'対象OEM',NULL,NULL,NULL,'6',0,NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(205,2,'URL',NULL,NULL,NULL,'',0,NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(205,3,'タイムアウト',NULL,NULL,NULL,'10',0,NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(205,4,'加盟店コード',NULL,NULL,NULL,'000000',0,NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(205,5,'加盟店サブコード',NULL,NULL,NULL,'00000',0,NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(205,6,'ハッシュ用パスワード',NULL,NULL,NULL,'',0,NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(205,7,'収納機関番号',NULL,NULL,NULL,'58054',0,NOW(),1,NOW(),1,1);

/* ペイジー用テンプレート追加 */
INSERT INTO M_TemplateHeader VALUES('102', 'CKI04049_1', '0', '0', '0', 'ペイジー請求データCSV', '1', ',', '\"', '*', '0', 'KI04049', NULL, NOW(), '9', NOW(), '9', '1');

INSERT INTO M_TemplateField VALUES('102', '1', 'PostalCode', '顧客郵便番号', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '2', 'UnitingAddress', '顧客住所', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '3', 'NameKj', '顧客氏名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '4', 'OrderId', '注文ＩＤ', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '5', 'ReceiptOrderDate', '注文日', 'DATE', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '6', 'SiteNameKj', '購入店名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '7', 'Url', '購入店URL', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '8', 'ContactPhoneNumber', '購入店電話番号', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '9', 'ClaimAmount', '請求金額', 'BIGINT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '10', 'CarriageFee', '送料', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '11', 'ChargeFee', '決済手数料', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '12', 'ReIssueCount', '請求回数', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '13', 'LimitDate', '支払期限日', 'DATE', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '14', 'Cv_BarcodeData2', 'バーコードデータ', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '15', 'ItemNameKj_1', '商品名１', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '16', 'ItemNum_1', '数量１', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '17', 'UnitPrice_1', '単価１', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '18', 'ItemNameKj_2', '商品名２', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '19', 'ItemNum_2', '数量２', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '20', 'UnitPrice_2', '単価２', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '21', 'ItemNameKj_3', '商品名３', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '22', 'ItemNum_3', '数量３', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '23', 'UnitPrice_3', '単価３', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '24', 'ItemNameKj_4', '商品名４', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '25', 'ItemNum_4', '数量４', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '26', 'UnitPrice_4', '単価４', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '27', 'ItemNameKj_5', '商品名５', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '28', 'ItemNum_5', '数量５', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '29', 'UnitPrice_5', '単価５', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '30', 'ItemNameKj_6', '商品名６', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '31', 'ItemNum_6', '数量６', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '32', 'UnitPrice_6', '単価６', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '33', 'ItemNameKj_7', '商品名７', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '34', 'ItemNum_7', '数量７', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '35', 'UnitPrice_7', '単価７', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '36', 'ItemNameKj_8', '商品名８', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '37', 'ItemNum_8', '数量８', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '38', 'UnitPrice_8', '単価８', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '39', 'ItemNameKj_9', '商品名９', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '40', 'ItemNum_9', '数量９', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '41', 'UnitPrice_9', '単価９', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '42', 'ItemNameKj_10', '商品名１０', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '43', 'ItemNum_10', '数量１０', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '44', 'UnitPrice_10', '単価１０', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '45', 'ItemNameKj_11', '商品名１１', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '46', 'ItemNum_11', '数量１１', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '47', 'UnitPrice_11', '単価１１', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '48', 'ItemNameKj_12', '商品名１２', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '49', 'ItemNum_12', '数量１２', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '50', 'UnitPrice_12', '単価１２', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '51', 'ItemNameKj_13', '商品名１３', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '52', 'ItemNum_13', '数量１３', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '53', 'UnitPrice_13', '単価１３', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '54', 'ItemNameKj_14', '商品名１４', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '55', 'ItemNum_14', '数量１４', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '56', 'UnitPrice_14', '単価１４', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '57', 'ItemNameKj_15', '商品名１５', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '58', 'ItemNum_15', '数量１５', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '59', 'UnitPrice_15', '単価１５', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '60', 'ItemNameKj_16', '商品名１６', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '61', 'ItemNum_16', '数量１６', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '62', 'UnitPrice_16', '単価１６', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '63', 'ItemNameKj_17', '商品名１７', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '64', 'ItemNum_17', '数量１７', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '65', 'UnitPrice_17', '単価１７', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '66', 'ItemNameKj_18', '商品名１８', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '67', 'ItemNum_18', '数量１８', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '68', 'UnitPrice_18', '単価１８', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '69', 'ItemNameKj_19', '商品名１９', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '70', 'ItemNum_19', '数量１９', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '71', 'UnitPrice_19', '単価１９', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '72', 'ClaimFee', '再請求発行手数料', 'BIGINT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '73', 'DamageInterestAmount', '遅延損害金', 'BIGINT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '74', 'TotalItemPrice', '小計', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '75', 'Ent_OrderId', '任意注文番号', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '76', 'TaxAmount', '消費税額', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '77', 'Cv_ReceiptAgentName', 'CVS収納代行会社名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '78', 'Cv_SubscriberName', 'CVS収納代行加入者名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '79', 'Cv_BarcodeData', 'バーコードデータ(CD付)', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '80', 'Cv_BarcodeString1', 'バーコード文字1', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '81', 'Cv_BarcodeString2', 'バーコード文字2', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '82', 'Bk_BankCode', '銀行口座 - 銀行コード', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '83', 'Bk_BranchCode', '銀行口座 - 支店コード', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '84', 'Bk_BankName', '銀行口座 - 銀行名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '85', 'Bk_BranchName', '銀行口座 - 支店名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '86', 'Bk_DepositClass', '銀行口座 - 口座種別', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '87', 'Bk_AccountNumber', '銀行口座 - 口座番号', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '88', 'Bk_AccountHolder', '銀行口座 - 口座名義', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '89', 'Bk_AccountHolderKn', '銀行口座 - 口座名義カナ', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '90', 'Yu_SubscriberName', 'ゆうちょ口座 - 加入者名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '91', 'Yu_AccountNumber', 'ゆうちょ口座 - 口座番号', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '92', 'Yu_ChargeClass', 'ゆうちょ口座 - 払込負担区分', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '93', 'Yu_MtOcrCode1', 'ゆうちょ口座 - MT用OCRコード1', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '94', 'Yu_MtOcrCode2', 'ゆうちょ口座 - MT用OCRコード2', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '95', 'MypageToken', 'マイページログインパスワード', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '96', 'ItemsCount', '商品合計数', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '97', 'TaxClass', '消費税区分', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '98', 'CorporateName', '法人名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '99', 'DivisionName', '部署名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '100', 'CpNameKj', '担当者名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '101', 'CustomerNumber', 'お客様番号', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '102', 'ConfirmNumber', '確認番号', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '103', 'Bk_Number', '収納機関番号', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');

/*
-- CB請求書用紙にみずほファクターの項目追加
INSERT INTO M_Code VALUES(181,106 ,'MHF圧着ハガキ（初回）','MHF' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(181,206 ,'MHF封書（初回）','MHF_Fuusho' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(181,306 ,'MHF圧着ハガキ（再３～５）','MHF_S' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(181,406 ,'MHFＡ４用紙（再６～７）','MHF_Toku' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);

-- 請求パターン用紙にみずほファクターの項目追加
INSERT INTO M_Code VALUES(182,61 ,'MHF初回','106' ,'Shokai' , '1' ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(182,62 ,'MHF再１','106' ,'Sai1' , '1' ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(182,63 ,'MHF再２','106' ,'Sai2' , '1' ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(182,64 ,'MHF再３','306' ,'Sai3' , '1' ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(182,66 ,'MHF再４','306' ,'Sai4' , '1' ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(182,67 ,'MHF再５','306' ,'Sai5' , '2' ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(182,68 ,'MHF再６','406' ,'Sai6' , '2' ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(182,69 ,'MHF再７','406' ,'Sai7' , '2' ,NULL ,0, NOW(), 1, NOW(), 1, 1);
*/

/* ペイジー請求書印刷データCSV(請求書同梱) */
INSERT INTO M_TemplateHeader VALUES('103', 'CKA04016_2', '0', '0', '0', 'ペイジー請求書印刷データCSV(請求書同梱)', '0', ',', '\"', '*', '0', 'KA04016', '{"items": "19"}', NOW(), '9', NOW(), '9', '1');

INSERT INTO M_TemplateField VALUES('103', '1', 'PostalCode', '顧客郵便番号', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38','9', '1');
INSERT INTO M_TemplateField VALUES('103', '2', 'UnitingAddress', '顧客住所', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38','9', '1');
INSERT INTO M_TemplateField VALUES('103', '3', 'NameKj', '顧客氏名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '4', 'OrderId', '注文ID', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '5', 'Ent_OrderId', '任意注文番号', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '6', 'ReceiptOrderDate', '注文日', 'DATE', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '7', 'SiteNameKj', '購入店名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '8', 'Url', '購入店URL', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1' );
INSERT INTO M_TemplateField VALUES('103', '9', 'Phone', '購入店電話番号', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '10', 'ClaimAmount', '請求金額', 'BIGINT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '11', 'TotalItemPrice', '小計', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '12', 'CarriageFee', '送料', 'BIGINT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '13', 'ChargeFee', '決済手数料', 'BIGINT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '14', 'ReIssueCount', '請求回数', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9','1');
INSERT INTO M_TemplateField VALUES('103', '15', 'LimitDate', '支払期限日', 'DATE', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9','1');
INSERT INTO M_TemplateField VALUES('103', '16', 'Cv_BarcodeData', 'バーコードデータ', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '17', 'Cv_BarcodeString1', 'バーコード文字列1', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '18', 'Cv_BarcodeString2', 'バーコード文字列2', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '19', 'Yu_DtCode', 'ゆうちょDT用データ', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '20', 'OrderItems', '商品明細', '－', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '21', 'TotalItemPrice2', '小計', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '22', 'Ent_OrderId2', '任意注文番号', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '23', 'TaxAmount', 'うち消費税額', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '24', 'Cv_ReceiptAgentName', 'コンビニ収納代行会社名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '25', 'Cv_SubscriberName', 'コンビニ収納代行加入者名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '26', 'Bk_BankCode', '銀行コード', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38','9', '1');
INSERT INTO M_TemplateField VALUES('103', '27', 'Bk_BranchCode', '支店コード', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '28', 'Bk_BankName', '銀行名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '29', 'Bk_BranchName', '支店名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '30', 'Bk_DepositClass', '銀行口座種別', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '31', 'Bk_AccountNumber', '銀行口座番号', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '32', 'Bk_AccountHolder', '銀行口座名義', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '33', 'Bk_AccountHolderKn', '銀行口座名義カナ', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '34', 'Yu_SubscriberName', 'ゆうちょ加入者名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '35', 'Yu_AccountNumber', 'ゆうちょ口座番号', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '36', 'Yu_ChargeClass', 'ゆうちょ払込負担区分', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '37', 'Yu_MtOcrCode1', 'ゆうちょOCRコード1', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '38', 'Yu_MtOcrCode2', 'ゆうちょOCRコード2', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '39', 'PrintEntComment01', '店舗からのお知らせ０１', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '40', 'PrintEntComment02', '店舗からのお知らせ０２', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '41', 'PrintEntComment03', '店舗からのお知らせ０３', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '42', 'PrintEntComment04', '店舗からのお知らせ０４', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '43', 'PrintEntComment05', '店舗からのお知らせ０５', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '44', 'PrintEntComment06', '店舗からのお知らせ０６', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '45', 'PrintEntComment07', '店舗からのお知らせ０７', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '46', 'PrintEntComment08', '店舗からのお知らせ０８', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '47', 'PrintEntComment09', '店舗からのお知らせ０９', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '48', 'PrintEntComment10', '店舗からのお知らせ１０', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '49', 'MypageToken', 'マイページログインパスワード', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '50', 'OtherItemsCount', 'その他商品点数', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '51', 'OtherItemsSummary', 'その他合算金額', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '52', 'MypageUrl', 'マイページURL', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '53', 'CorporateName', '法人名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '54', 'DivisionName', '部署名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '55', 'CpNameKj', '担当者名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9','1');
INSERT INTO M_TemplateField VALUES('103', '56', 'TaxRate', '消費税率', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '0' );
INSERT INTO M_TemplateField VALUES('103', '57', 'SubUseAmount_1', '８％対象合計金額', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '0');
INSERT INTO M_TemplateField VALUES('103', '58', 'SubTaxAmount_1', '８％対象消費税額', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '0');
INSERT INTO M_TemplateField VALUES('103', '59', 'SubUseAmount_2', '１０％対象合計金額', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '0');
INSERT INTO M_TemplateField VALUES('103', '60', 'SubTaxAmount_2', '１０％対象消費税額', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '0');
INSERT INTO M_TemplateField VALUES('103', '61', 'CorporationNumber', '事業者登録番号', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '0');
INSERT INTO M_TemplateField VALUES('103', '62', 'CreditLimitDate', 'クレジット手続き期限日', 'DATE', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '83', '2020-12-2117:36:38', '83', '0');
INSERT INTO M_TemplateField VALUES('103', '63', 'CustomerNumber', 'お客様番号', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '83', '2020-12-21 17:36:38', '83', '1');
INSERT INTO M_TemplateField VALUES('103', '64', 'ConfirmNumber', '確認番号', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '83', '2020-12-21 17:36:38', '83', '1');
INSERT INTO M_TemplateField VALUES('103', '65', 'Bk_Number', '収納機関番号', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '83', '2020-12-21 17:36:38', '83', '1');


/* ペイジー項目を追加したためビューを更新する(mypage03で実行) */
DROP VIEW IF EXISTS `MV_OemClaimAccountInfo`;

CREATE VIEW `MV_OemClaimAccountInfo` AS
    SELECT *
    FROM coraldb_new01.T_OemClaimAccountInfo
;

/* ﾒｰﾙ挿入項目にペイジーの項目を追加 */
INSERT INTO M_Code VALUES('72', '295', 'お客様番号', '{CustomerNumber}', '39', '40', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '296', 'お客様番号', '{CustomerNumber}', '41', '42', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '297', 'お客様番号', '{CustomerNumber}', '43', '44', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '298', 'お客様番号', '{CustomerNumber}', '45', '46', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '299', '確認番号', '{ConfirmNumber}', '39', '40', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '300', '確認番号', '{ConfirmNumber}', '41', '42', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '301', '確認番号', '{ConfirmNumber}', '43', '44', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '302', '確認番号', '{ConfirmNumber}', '45', '46', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '303', '収納機関番号', '{Bk_Number}', '39', '40', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '304', '収納機関番号', '{Bk_Number}', '41', '42', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '305', '収納機関番号', '{Bk_Number}', '43', '44', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '306', '収納機関番号', '{Bk_Number}', '45', '46', NULL, '1', NOW(), '1', NOW(), '1', '1');

/* エラーメッセージの最大文字数を拡張 */
ALTER TABLE T_ClaimError MODIFY ErrorMsg varchar(1000);
