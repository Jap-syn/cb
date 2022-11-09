ALTER TABLE M_Code MODIFY Class1 VARCHAR(100);
ALTER TABLE M_Code MODIFY Class2 VARCHAR(100);
ALTER TABLE M_Code MODIFY Class3 VARCHAR(100);
ALTER TABLE M_Code MODIFY Class4 VARCHAR(100);
ALTER TABLE M_Code MODIFY Class5 VARCHAR(100);
ALTER TABLE M_Code MODIFY Class6 VARCHAR(100);
ALTER TABLE M_Code MODIFY Class7 VARCHAR(100);

ALTER TABLE M_SbpsPayment ADD COLUMN `FixedId` tinyint NULL AFTER `MailParameterNameKj`;

INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `SystemFlg`, `ValidFlg`) VALUES ('213', '2', '後払いドットコム', 'customer-dev@ato-barai.com', 'doc/help/atobarai_help.html', '0', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `SystemFlg`, `ValidFlg`) VALUES ('213', '3', '届いてから払い', 'todoitekara-dev@ato-barai.com', 'doc/help/todoitekara_help.html', '0', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `SystemFlg`, `ValidFlg`) VALUES ('213', '4', '後払いドットコム', 'customer-dev@ato-barai.com', 'doc/help/atobarai_help.html', '0', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `SystemFlg`, `ValidFlg`) VALUES ('213', '5', '届いてから払い', 'todoitekara-dev@ato-barai.com', 'doc/help/todoitekara_help.html', '0', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `SystemFlg`, `ValidFlg`) VALUES ('213', '6', '後払いドットコム', 'customer-dev@ato-barai.com', 'doc/help/atobarai_help.html', '0', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `SystemFlg`, `ValidFlg`) VALUES ('213', '7', '届いてから払い', 'todoitekara-dev@ato-barai.com', 'doc/help/todoitekara_help.html', '0', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `SystemFlg`, `ValidFlg`) VALUES ('213', '8', '後払いドットコム', 'customer-dev@ato-barai.com', 'doc/help/atobarai_help.html', '0', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `SystemFlg`, `ValidFlg`) VALUES ('213', '9', '届いてから払い', 'todoitekara-dev@ato-barai.com', 'doc/help/todoitekara_help.html', '0', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `SystemFlg`, `ValidFlg`) VALUES ('213', '10', '後払いドットコム', 'customer-dev@ato-barai.com', 'doc/help/atobarai_help.html', '0', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `SystemFlg`, `ValidFlg`) VALUES ('213', '11', '届いてから払い', 'todoitekara-dev@ato-barai.com', 'doc/help/todoitekara_help.html', '0', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `SystemFlg`, `ValidFlg`) VALUES ('213', '12', '後払いドットコム', 'customer-dev@ato-barai.com', 'doc/help/atobarai_help.html', '0', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `SystemFlg`, `ValidFlg`) VALUES ('213', '13', '届いてから払い', 'todoitekara-dev@ato-barai.com', 'doc/help/todoitekara_help.html', '0', '1');


INSERT INTO M_CodeManagement (`CodeId`,`CodeName`, `KeyLogicName`, `Class1ValidFlg`, `Class1Name`, `Class2ValidFlg`, `Class2Name`, `Class3ValidFlg`, `Class3Name`, `Class4ValidFlg`, `Class4Name`, `Class5ValidFlg`, `Class5Name`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`) 
VALUES ('220','SBPS OEMのリンク先', 'OEM先名', '1', 'SBPS URL', '1', 'PAGECON URL', '1', 'SUCCESS URL', '1', 'CANCEL URL', '1', 'ERROR URL', NOW(), 1, NOW(), 1);

INSERT INTO M_Code (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Class5`) VALUES ('220', '1', '株式会社Ｅストアー',               'https://fep.sps-system.com/f01/FepBuyInfoReceive.do', 'https://cb.ato-barai.jp/estore/orderpage/sbpssettlement/pagecon',          'https://cb.ato-barai.jp/estore/success.php',          'https://cb.ato-barai.jp/estore/cancel.php',          'https://cb.ato-barai.jp/estore/error.php'          );
INSERT INTO M_Code (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Class5`) VALUES ('220', '2', 'SMBCファイナンスサービス株式会社', 'https://fep.sps-system.com/f01/FepBuyInfoReceive.do', 'https://cb.ato-barai.jp/smbcfs/orderpage/sbpssettlement/pagecon',          'https://cb.ato-barai.jp/smbcfs/success.php',          'https://cb.ato-barai.jp/smbcfs/cancel.php',          'https://cb.ato-barai.jp/smbcfs/error.php'          );
INSERT INTO M_Code (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Class5`) VALUES ('220', '3', 'セイノーフィナンシャル株式会社',   'https://fep.sps-system.com/f01/FepBuyInfoReceive.do', 'https://cb.ato-barai.jp/seino-financial/orderpage/sbpssettlement/pagecon', 'https://cb.ato-barai.jp/seino-financial/success.php', 'https://cb.ato-barai.jp/seino-financial/cancel.php', 'https://cb.ato-barai.jp/seino-financial/error.php' );
INSERT INTO M_Code (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Class5`) VALUES ('220', '4', 'BASE株式会社',                     'https://fep.sps-system.com/f01/FepBuyInfoReceive.do', 'https://cb.ato-barai.jp/base/orderpage/sbpssettlement/pagecon',            'https://cb.ato-barai.jp/base/success.php',            'https://cb.ato-barai.jp/base/cancel.php',            'https://cb.ato-barai.jp/base/error.php'            );
INSERT INTO M_Code (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Class5`) VALUES ('220', '5', 'テモナ株式会社',                   'https://fep.sps-system.com/f01/FepBuyInfoReceive.do', 'https://cb.ato-barai.jp/temona/orderpage/sbpssettlement/pagecon',          'https://cb.ato-barai.jp/temona/success.php',          'https://cb.ato-barai.jp/temona/cancel.php',          'https://cb.ato-barai.jp/temona/error.php'          );
INSERT INTO M_Code (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Class5`) VALUES ('220', '6', 'みずほファクター株式会社',         'https://fep.sps-system.com/f01/FepBuyInfoReceive.do', 'https://cb.ato-barai.jp/mizuho/orderpage/sbpssettlement/pagecon',          'https://cb.ato-barai.jp/mizuho/success.php',          'https://cb.ato-barai.jp/mizuho/cancel.php',          'https://cb.ato-barai.jp/mizuho/error.php'          );
INSERT INTO M_Code (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Class5`) VALUES ('220', '7', 'トゥモロー総研株式会社',           'https://fep.sps-system.com/f01/FepBuyInfoReceive.do', 'https://cb.ato-barai.jp/tri-payment/orderpage/sbpssettlement/pagecon',     'https://cb.ato-barai.jp/tri-payment/success.php',     'https://cb.ato-barai.jp/tri-payment/cancel.php',     'https://cb.ato-barai.jp/tri-payment/error.php'     );

UPDATE M_Payment SET LogoUrl='my_page/seikyu_PayB' WHERE FixedId=1;
UPDATE M_Payment SET LogoUrl='my_page/seikyu_paypay' WHERE FixedId=2;
UPDATE M_Payment SET LogoUrl='my_page/seikyu_LINEpay' WHERE FixedId=3;
UPDATE M_Payment SET LogoUrl='my_page/seikyu_Rakuten' WHERE FixedId=4;
UPDATE M_Payment SET LogoUrl='my_page/seikyu_FamiPay' WHERE FixedId=5;
UPDATE M_Payment SET LogoUrl='my_page/seikyu_PostPay' WHERE FixedId=6;

UPDATE M_SbpsPayment SET FixedId=SbpsPaymentId;
INSERT INTO `M_SbpsPayment` VALUES (10, 2, '届いてから', 'credit_vm', 'クレジット(VISA/MASTER）', 1, 'my_page/credit_VISA-Master.png', 'ST02-00303-101', 'クレジット', 1, 1, NOW(), 1, NOW(), 1);
INSERT INTO `M_SbpsPayment` VALUES (11, 2, '届いてから', 'credit_ja', 'クレジット(JCB/AMEX）', 2, 'my_page/credit_JCB-Amex.png', 'ST02-00303-101', 'クレジット', 2, 1, NOW(), 1, NOW(), 1);
INSERT INTO `M_SbpsPayment` VALUES (12, 2, '届いてから', 'credit_d', 'クレジット(Dinars）', 3, 'my_page/credit_DinersClub.png', 'ST02-00303-101', 'クレジット', 3, 1, NOW(), 1, NOW(), 1);
INSERT INTO `M_SbpsPayment` VALUES (13, 2, '届いてから', 'paypay', 'PayPay（オンライン決済）', 4, 'my_page/todo_paypay.png', 'ST02-00306-311', 'PayPay（オンライン決済）', 4, 1, NOW(), 1, NOW(), 1);
INSERT INTO `M_SbpsPayment` VALUES (14, 2, '届いてから', 'linepay', 'LINEPay', 5, 'my_page/todo_LINEpay.png', 'ST02-00306-310', 'LINEPay', 5, 1, NOW(), 1, NOW(), 1);
INSERT INTO `M_SbpsPayment` VALUES (15, 2, '届いてから', 'softbank2', 'ソフトバンクまとめて支払い', 6, 'my_page/todo_softbank.png', 'ST02-00303-405', 'ソフトバンクまとめて支払い,ワイモバイルまとめて支払い', 6, 1, NOW(), 1, NOW(), 1);
INSERT INTO `M_SbpsPayment` VALUES (16, 2, '届いてから', 'docomo', 'ドコモ払い', 7, 'my_page/todo_docomo.jpg', 'ST02-00303-401', 'ドコモ払い', 7, 1, NOW(), 1, NOW(), 1);
INSERT INTO `M_SbpsPayment` VALUES (17, 2, '届いてから', 'auone', 'auかんたん決済', 8, 'my_page/todo_au.jpg', 'ST02-00303-402', 'auかんたん決済 (au / UQ mobile)', 8, 1, NOW(), 1, NOW(), 1);
INSERT INTO `M_SbpsPayment` VALUES (18, 2, '届いてから', 'rakuten', '楽天ペイ（オンライン決済）', 9, 'my_page/todo_Rakuten.png', 'ST02-00306-305', '楽天ペイ（オンライン決済）', 9, 1, NOW(), 1, NOW(), 1);

UPDATE T_SystemProperty SET PropValue='https://cb.ato-barai.jp/sf/orderpage' WHERE Module='[DEFAULT]' AND Category='systeminfo' AND Name='smbc_orderpage_url'

UPDATE M_Code Set Class4=1 WHERE CodeId = 160 AND KeyCode = 2;


-- mypageユーザで
DROP VIEW IF EXISTS `MV_Code`;

CREATE VIEW `MV_Code` AS
    SELECT *
    FROM coraldb_new01.M_Code
;

DROP VIEW IF EXISTS `MV_SbpsPayment`;

CREATE VIEW `MV_SbpsPayment` AS
    SELECT *
    FROM coraldb_new01.M_SbpsPayment
;

