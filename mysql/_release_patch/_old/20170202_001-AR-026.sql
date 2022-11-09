/* 注文_会計 20170110ダンプにて80秒 */
ALTER TABLE `AT_Order` 
ADD COLUMN `RepayTCFlg` TINYINT(4) NOT NULL DEFAULT 0 AFTER `Dmg_DailySummaryFlg`,
ADD COLUMN `RepayPendingFlg` TINYINT(4) NOT NULL DEFAULT 0 AFTER `RepayTCFlg`;

/* 返金管理 20170110ダンプにて1秒 */
ALTER TABLE `T_RepaymentControl` 
ADD COLUMN `NetStatus` TINYINT NOT NULL DEFAULT 0 AFTER `OutputFileSeq`,
ADD COLUMN `CoRecvNum` VARCHAR(20) NULL AFTER `NetStatus`,
ADD COLUMN `CoYoyakuNum` VARCHAR(11) NULL AFTER `CoRecvNum`,
ADD COLUMN `CoTranLimit` VARCHAR(12) NULL AFTER `CoYoyakuNum`,
ADD COLUMN `CoWcosId` VARCHAR(20) NULL AFTER `CoTranLimit`,
ADD COLUMN `CoWcosPassword` VARCHAR(20) NULL AFTER `CoWcosId`,
ADD COLUMN `CoWcosUrl` VARCHAR(260) NULL AFTER `CoWcosPassword`,
ADD COLUMN `CoTranReqDate` VARCHAR(14) NULL AFTER `CoWcosUrl`,
ADD COLUMN `CoTranProcDate` VARCHAR(14) NULL AFTER `CoTranReqDate`,
ADD COLUMN `MailFlg` TINYINT NOT NULL DEFAULT 9 AFTER `CoTranProcDate`,
ADD COLUMN `MailRetryCount` TINYINT NOT NULL DEFAULT 0 AFTER `MailFlg`,
ADD INDEX `Idx_T_RepaymentControl03` (`CoRecvNum` ASC);

/* 請求管理 20170110ダンプにて44秒 */
ALTER TABLE `T_ClaimControl` 
ADD INDEX `Idx_T_ClaimControl05` (`ClaimedBalance` ASC);

/* コードマスター ﾈｯﾄDE受取ｽﾃｰﾀｽ */
INSERT INTO M_CodeManagement VALUES(188 ,'ネットＤＥ受取ステータス' ,NULL ,'ネットＤＥ受取ステータス' ,0 ,NULL ,0,NULL,0,NULL, NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(188,0 ,'未指示',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(188,1 ,'指示済',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(188,2 ,'承認済',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(188,3 ,'ハガキ出力済',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(188,4 ,'返金済',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);

/* コードマスター 注文履歴 */
INSERT INTO M_Code VALUES(97,111 ,'ネットDE受取指示済',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(97,112 ,'ネットDE受取承認済',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(97,113 ,'ネットDE受取ハガキ出力済',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(97,114 ,'ネットDE受取返金済',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(97,115 ,'返金保留',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(97,116 ,'返金保留解除',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);

/* システム条件 */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'NetTransferCommission', '324', 'ネットDE受取振込手数料', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'NetCoTranLimitDays', '90', 'ネットDE受取送金期限日数', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'NetCoCorpCode', '9999999999', 'ネットDE受取事業者コード', NOW(), 9, NOW(), 9, '1');

/* テンプレートマスター ネットDE受取データ */
INSERT INTO M_TemplateHeader VALUES( 90 , 'CKI08070_2', 0, 0, 0, 'ネットDE受取データ', 0, ',', '' ,'SJIS-win' ,0,'KI08070', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateField VALUES ( 90 , 1, 'DataSyubetsu' ,'データ種別' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 2, 'CoPayCode' ,'支払コード ' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 3, 'CoRecvNum' ,'お客様番号' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 4, 'CoJigyosyaNo' ,'事業者番号' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 5, 'CoAnkenNo' ,'契約案件番号' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 6, 'CoWcosPassword' ,'WCOSパスワード' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 7, 'CoOpCode' ,'データ区分' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 8, 'CoCorpCode' ,'事業者コード' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 9, 'CoTel' ,'電話番号' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 10, 'CoNameKanji' ,'お客様氏名' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 11, 'CoTranLimit' ,'送金期限' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 12, 'CoTranAmount' ,'送金金額' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 13, 'CoReserveNum' ,'予約番号' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 14, 'CoMemberNum' ,'会員番号' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 15, 'CoNameKana' ,'お客様氏名（フリガナ）' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 16, 'CoFree1' ,'WCOS ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 17, 'CoFree2' ,'金融機関コード' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 18, 'CoFree3' ,'支店コード' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 19, 'CoFree4' ,'口座種別' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 20, 'CoFree5' ,'口座番号' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 21, 'CoFree6' ,'口座名義人名' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 22, 'CoFree7' ,'メールアドレス１' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 23, 'CoFree8' ,'メールアドレス２' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 24, 'CoCFree1' ,'フリースペース１' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 25, 'CoCFree2' ,'フリースペース２' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 26, 'CoCFree3' ,'フリースペース３' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 27, 'CoCFree4' ,'フリースペース４' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 28, 'CoCFree5' ,'フリースペース５' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 29, 'CoCFree6' ,'フリースペース６' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 30, 'CoCFree7' ,'フリースペース７' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 31, 'CoCFree8' ,'フリースペース８' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);

/* テンプレートマスター ネットDE受取ハガキデータ */
INSERT INTO M_TemplateHeader VALUES( 91 , 'CKI08070_3', 0, 0, 0, 'ネットDE受取ハガキデータ', 1, ',', '\"' ,'*' ,0,'KI08070', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateField VALUES ( 91 , 1, 'PostalCode' ,'顧客郵便番号' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 2, 'UnitingAddress' ,'顧客住所' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 3, 'NameKj' ,'顧客氏名' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 4, 'OrderId' ,'注文ＩＤ' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 5, 'ReceiptOrderDate' ,'注文日' ,'DATE' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 6, 'SiteNameKj' ,'購入店名' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 7, 'Url' ,'購入店URL' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 8, 'ContactPhoneNumber' ,'購入店電話番号' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 9, 'ClaimAmount' ,'請求金額' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 10, 'CarriageFee' ,'送料' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 11, 'ChargeFee' ,'決済手数料' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 12, 'ReIssueCount' ,'請求回数' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 13, 'LimitDate' ,'支払期限日' ,'DATE' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 14, 'Cv_BarcodeData2' ,'バーコードデータ' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 15, 'ItemNameKj_1' ,'商品名１' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 16, 'ItemNum_1' ,'数量１' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 17, 'UnitPrice_1' ,'単価１' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 18, 'ItemNameKj_2' ,'商品名２' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 19, 'ItemNum_2' ,'数量２' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 20, 'UnitPrice_2' ,'単価２' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 21, 'ItemNameKj_3' ,'商品名３' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 22, 'ItemNum_3' ,'数量３' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 23, 'UnitPrice_3' ,'単価３' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 24, 'ItemNameKj_4' ,'商品名４' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 25, 'ItemNum_4' ,'数量４' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 26, 'UnitPrice_4' ,'単価４' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 27, 'ItemNameKj_5' ,'商品名５' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 28, 'ItemNum_5' ,'数量５' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 29, 'UnitPrice_5' ,'単価５' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 30, 'ItemNameKj_6' ,'商品名６' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 31, 'ItemNum_6' ,'数量６' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 32, 'UnitPrice_6' ,'単価６' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 33, 'ItemNameKj_7' ,'商品名７' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 34, 'ItemNum_7' ,'数量７' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 35, 'UnitPrice_7' ,'単価７' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 36, 'ItemNameKj_8' ,'商品名８' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 37, 'ItemNum_8' ,'数量８' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 38, 'UnitPrice_8' ,'単価８' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 39, 'ItemNameKj_9' ,'商品名９' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 40, 'ItemNum_9' ,'数量９' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 41, 'UnitPrice_9' ,'単価９' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 42, 'ItemNameKj_10' ,'商品名１０' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 43, 'ItemNum_10' ,'数量１０' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 44, 'UnitPrice_10' ,'単価１０' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 45, 'ItemNameKj_11' ,'商品名１１' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 46, 'ItemNum_11' ,'数量１１' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 47, 'UnitPrice_11' ,'単価１１' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 48, 'ItemNameKj_12' ,'商品名１２' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 49, 'ItemNum_12' ,'数量１２' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 50, 'UnitPrice_12' ,'単価１２' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 51, 'ItemNameKj_13' ,'商品名１３' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 52, 'ItemNum_13' ,'数量１３' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 53, 'UnitPrice_13' ,'単価１３' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 54, 'ItemNameKj_14' ,'商品名１４' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 55, 'ItemNum_14' ,'数量１４' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 56, 'UnitPrice_14' ,'単価１４' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 57, 'ItemNameKj_15' ,'商品名１５' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 58, 'ItemNum_15' ,'数量１５' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 59, 'UnitPrice_15' ,'単価１５' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 60, 'ItemNameKj_16' ,'商品名１６' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 61, 'ItemNum_16' ,'数量１６' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 62, 'UnitPrice_16' ,'単価１６' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 63, 'ItemNameKj_17' ,'商品名１７' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 64, 'ItemNum_17' ,'数量１７' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 65, 'UnitPrice_17' ,'単価１７' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 66, 'ItemNameKj_18' ,'商品名１８' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 67, 'ItemNum_18' ,'数量１８' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 68, 'UnitPrice_18' ,'単価１８' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 69, 'ItemNameKj_19' ,'商品名１９' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 70, 'ItemNum_19' ,'数量１９' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 71, 'UnitPrice_19' ,'単価１９' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 72, 'ClaimFee' ,'再請求発行手数料' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 73, 'DamageInterestAmount' ,'遅延損害金' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 74, 'TotalItemPrice' ,'小計' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 75, 'Ent_OrderId' ,'任意注文番号' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 76, 'TaxAmount' ,'消費税額' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 77, 'Cv_ReceiptAgentName' ,'CVS収納代行会社名' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 78, 'Cv_SubscriberName' ,'CVS収納代行加入者名' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 79, 'Cv_BarcodeData' ,'バーコードデータ(CD付)' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 80, 'Cv_BarcodeString1' ,'バーコード文字1' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 81, 'Cv_BarcodeString2' ,'バーコード文字2' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 82, 'Bk_BankCode' ,'銀行口座 - 銀行コード' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 83, 'Bk_BranchCode' ,'銀行口座 - 支店コード' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 84, 'Bk_BankName' ,'銀行口座 - 銀行名' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 85, 'Bk_BranchName' ,'銀行口座 - 支店名' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 86, 'Bk_DepositClass' ,'銀行口座 - 口座種別' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 87, 'Bk_AccountNumber' ,'銀行口座 - 口座番号' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 88, 'Bk_AccountHolder' ,'銀行口座 - 口座名義' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 89, 'Bk_AccountHolderKn' ,'銀行口座 - 口座名義カナ' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 90, 'Yu_SubscriberName' ,'ゆうちょ口座 - 加入者名' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 91, 'Yu_AccountNumber' ,'ゆうちょ口座 - 口座番号' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 92, 'Yu_ChargeClass' ,'ゆうちょ口座 - 払込負担区分' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 93, 'Yu_MtOcrCode1' ,'ゆうちょ口座 - MT用OCRコード1' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 94, 'Yu_MtOcrCode2' ,'ゆうちょ口座 - MT用OCRコード2' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 95, 'MypageToken' ,'マイページログインパスワード' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 96, 'ItemsCount' ,'商品合計数' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 97, 'TaxClass' ,'消費税区分' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 98, 'CorporateName' ,'法人名' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 99, 'DivisionName' ,'部署名' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 100, 'CpNameKj' ,'担当者名' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 101, 'CoWcosId' ,'WCOS ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 102, 'CoWcosPassword' ,'WCOS パスワード' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 103, 'CoWcosUrl' ,'WCOS URL' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 104, 'RepayAmount' ,'返金金額' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 105, 'TransferCommission' ,'振込手数料' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 106, 'CoTranLimit' ,'口座入力期限' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);


/* メールテンプレート */
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (90,'ネットDE受取メール（PC）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'【後払いドットコム】ご返金のご連絡','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────\r\n◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇\r\n──────────────────────────────────\r\n\r\n{CustomerNameKj}様\r\n\r\nこの度は{SiteNameKj}で商品ご購入の際に、\r\n後払いドットコムをご利用いただきまして\r\nまことにありがとうございました。\r\n\r\n{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、\r\n{OverReceiptAmount}円多くお支払いいただいておりましたので\r\nご返金させていただきたくご連絡差し上げました。\r\n\r\n返金の方法のご案内を、本日注文者様ご住所宛にハガキにてお送りしました。\r\n普通郵便での発送となりますので、お客様のお手元に届くまで\r\n２日～５日程度かかる場合がございます。\r\n一週間ほどお待ちいただいても届かない場合は、\r\n大変お手数ではございますが、このメールの末尾に記載しております\r\n後払いドットコムカスタマーセンターまでご一報くださいませ。\r\n\r\n\r\n【ご注文内容】\r\nご注文番号：{OrderId}\r\nご注文日：{OrderDate}\r\nご注文店舗：{SiteNameKj}\r\n商品名（1品目のみ表示）：{OneOrderItem}\r\nご請求金額：{UseAmount}\r\n\r\n\r\n不明点などございましたら、お気軽にお問合せくださいませ。\r\n\r\n--------------------------------------------------------------\r\n後払い請求代行サービス【後払いドットコム】\r\n\r\n  お問合せ先：03－5909－3490\r\n  営業時間：9:00～18:00　年中無休（年末・年始のぞく）\r\n  mail: customer@ato-barai.com\r\n  \r\n　運営会社：株式会社キャッチボール\r\n　住所：〒160-0023 東京都新宿区西新宿6-14-1 新宿グリーンタワービル14F\r\n-------------------------------------------------------------- \r\n',NULL,'2016-02-23 14:00:00',1,'2017-02-14 09:49:26',83,1);
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (91,'ネットDE受取メール（CEL）','後払いドットコム','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'【後払いドットコム】ご返金のご連絡','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','──────────────────────────────────\r\n◇お問い合わせいただく際、下記メール文面を残したままご返信ください◇\r\n──────────────────────────────────\r\n\r\n{CustomerNameKj}様\r\n\r\nこの度は{SiteNameKj}で商品ご購入の際に、\r\n後払いドットコムをご利用いただきまして\r\nまことにありがとうございました。\r\n\r\n{ReceiptDate}に{ReceiptClass}よりご入金を確認いたしましたが、\r\n{OverReceiptAmount}円多くお支払いいただいておりましたので\r\nご返金させていただきたくご連絡差し上げました。\r\n\r\n返金の方法のご案内を、本日注文者様ご住所宛にハガキにてお送りしました。\r\n普通郵便での発送となりますので、お客様のお手元に届くまで\r\n２日～５日程度かかる場合がございます。\r\n一週間ほどお待ちいただいても届かない場合は、\r\n大変お手数ではございますが、このメールの末尾に記載しております\r\n後払いドットコムカスタマーセンターまでご一報くださいませ。\r\n\r\n\r\n【ご注文内容】\r\nご注文番号：{OrderId}\r\nご注文日：{OrderDate}\r\nご注文店舗：{SiteNameKj}\r\n商品名（1品目のみ表示）：{OneOrderItem}\r\nご請求金額：{UseAmount}\r\n\r\n\r\n不明点などございましたら、お気軽にお問合せくださいませ。\r\n\r\n--------------------------------------------------------------\r\n後払い請求代行サービス【後払いドットコム】\r\n\r\n  お問合せ先：03－5909－3490\r\n  営業時間：9:00～18:00　年中無休（年末・年始のぞく）\r\n  mail: customer@ato-barai.com\r\n  \r\n　運営会社：株式会社キャッチボール\r\n　住所：〒160-0023 東京都新宿区西新宿6-14-1 新宿グリーンタワービル14F\r\n-------------------------------------------------------------- \r\n',NULL,'2016-02-23 14:00:00',1,'2017-02-14 09:49:26',83,1);

/* コードマスター メールパラメーター */
INSERT INTO M_Code VALUES(72,286 ,'購入者名','{CustomerNameKj}' ,'90' , '91' ,NULL ,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(72,287 ,'サイト名','{SiteNameKj}' ,'90' , '91' ,NULL ,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(72,288 ,'入金確認日','{ReceiptDate}' ,'90' , '91' ,NULL ,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(72,289 ,'入金方法','{ReceiptClass}' ,'90' , '91' ,NULL ,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(72,290 ,'過剰入金額','{OverReceiptAmount}' ,'90' , '91' ,NULL ,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(72,291 ,'注文ID','{OrderId}' ,'90' , '91' ,NULL ,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(72,292 ,'注文日','{OrderDate}' ,'90' , '91' ,NULL ,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(72,293 ,'商品名（先頭ひとつ）','{OneOrderItem}' ,'90' , '91' ,NULL ,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(72,294 ,'合計金額','{UseAmount}' ,'90' , '91' ,NULL ,1, NOW(), 1, NOW(), 1, 1);
