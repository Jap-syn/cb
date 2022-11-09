/*cbadminのデータベース*/

-- 160 --
UPDATE M_Code SET Class4 = 1, UpdateDate = now(), UpdateId = 1  WHERE CodeId = 160 AND KeyCode = 0;
UPDATE M_Code SET Class4 = 0, UpdateDate = now(), UpdateId = 1  WHERE CodeId = 160 AND KeyCode = 1;
UPDATE M_Code SET Class4 = 0, UpdateDate = now(), UpdateId = 1  WHERE CodeId = 160 AND KeyCode = 2;
UPDATE M_Code SET Class4 = 0, UpdateDate = now(), UpdateId = 1  WHERE CodeId = 160 AND KeyCode = 3;
UPDATE M_Code SET Class4 = 0, UpdateDate = now(), UpdateId = 1  WHERE CodeId = 160 AND KeyCode = 4;
UPDATE M_Code SET Class4 = 0, UpdateDate = now(), UpdateId = 1  WHERE CodeId = 160 AND KeyCode = 5;
UPDATE M_Code SET Class4 = 0, UpdateDate = now(), UpdateId = 1  WHERE CodeId = 160 AND KeyCode = 6;

-- 163 --
UPDATE `M_Code` SET `Class2`='0', `Class3`='0', `Class4`='1', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='163' and`KeyCode`='1';
UPDATE `M_Code` SET `Class2`='2', `Class3`='0', `Class4`='2', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='163' and`KeyCode`='2';
UPDATE `M_Code` SET `Class2`='1', `Class3`='0', `Class4`='3', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='163' and`KeyCode`='3';
UPDATE `M_Code` SET `Class2`='0', `Class3`='0', `Class4`='4', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='163' and`KeyCode`='4';
UPDATE `M_Code` SET `Class2`='99', `KeyContent`='クレジット決済(VISA/MASTER）', `Class1`='クレジット決済(VISA/MASTER）', `Class3`='1', `Class4`='21', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='163' and`KeyCode`='5';
UPDATE `M_Code` SET `Class2`='0', `KeyContent` = 'PayPay請求書払い', `Class1`='PayPay請求書払い', `Class3`='0', `Class4`='8', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='163' and`KeyCode`='6';
UPDATE `M_Code` SET `Class2`='0', `KeyContent` = 'LINE Pay請求書払い', `Class1`='LINE Pay請求書払い', `Class3`='0', `Class4`='9', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='163' and`KeyCode`='7';
UPDATE `M_Code` SET `Class2`='99', `Class3`='0', `Class4`='10', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='163' and`KeyCode`='8';
UPDATE `M_Code` SET `Class2`='99', `Class3`='0', `Class4`='11', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='163' and`KeyCode`='9';
UPDATE `M_Code` SET `Class2`='0', `Class3`='0', `Class4`='12', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='163' and`KeyCode`='10';
UPDATE `M_Code` SET `Class2`='0', `Class3`='0', `Class4`='13', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='163' and`KeyCode`='11';
UPDATE `M_Code` SET `Class2`='99', `Class3`='0', `Class4`='14', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='163' and`KeyCode`='13';
UPDATE `M_Code` SET `Class2`='0', `Class3`='0', `Class4`='15', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='163' and`KeyCode`='14';
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('163', '15', 'PayPay （オンライン決済）', 'PayPay （オンライン決済）', '99', '1', '24', '', '0', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('163', '16', 'LINE Pay', 'LINE Pay', '99', '1', '25', '', '0', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('163', '17', 'ソフトバンクまとめて支払い', 'ソフトバンクまとめて支払い', '99', '1', '26', '', '0', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('163', '18', 'ドコモ払い', 'ドコモ払い', '99', '1', '27', '', '0', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('163', '19', 'auかんたん決済', 'auかんたん決済', '99', '1', '28', '', '0', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('163', '20', '楽天ペイ（オンライン決済）', '楽天ペイ（オンライン決済）', '99', '1', '29', '', '0', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('163', '21', 'クレジット決済(JCB/AMEX）', 'クレジット決済(JCB/AMEX）', '99', '1', '22', '', '0', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('163', '22', 'クレジット決済(Diners）', 'クレジット決済(Diners）', '99', '1', '23', '', '0', now(), '1', now(), '1', '1');

-- 185 --
UPDATE `M_Code` SET `Note` = '<table><tr><td style=\"font-size: 14px ;font-weight: normal\"><B>注文情報確認ページの閲覧は一定期間で終了いたします。</td></tr><tr><td style=\"font-size: 14px ;font-weight: normal\"><B>注文情報のお問い合わせにつきましては弊社サポートセンターまでお問い合わせください。</td></tr><tr><td style=\"font-size: 14px ;font-weight: normal\"><B>サポートセンター電話番号： TEL: 0120-667-690（9:00 ～ 18:00）</td></tr><tr><td style=\"font-size: 14px ;font-weight: nrmal\"><B>または、請求書に記載されている情報を元に<a href=\"login/login\"><U>こちら</U></a>へログインしてください。</td></tr></table>', UpdateDate = now(), UpdateId = 1 WHERE (`CodeId` = '185') and (`KeyCode` = '0');

-- 186 --
UPDATE `M_Code` SET `Note` = '<table><tr><td style=\"font-size: 14px ;font-weight: normal\"><B>注文情報確認ページの閲覧は一定期間で終了いたします。</td></tr><tr><td style=\"font-size: 14px ;font-weight: normal\"><B>注文情報のお問い合わせにつきましては弊社サポートセンターまでお問い合わせください。</td></tr><tr><td style=\"font-size: 14px ;font-weight: normal\"><B>サポートセンター電話番号： TEL: 0120-667-690（9:00 ～ 18:00）</td></tr><tr><td style=\"font-size: 14px ;font-weight: nrmal\"><B>または、請求書に記載されている情報を元に<a href=\"login/login\"><U>こちら</U></a>へログインしてください。</td></tr></table>', UpdateDate = now(), UpdateId = 1 WHERE (`CodeId` = '186') and (`KeyCode` = '0');

-- 198 --
UPDATE `M_Code` SET `Class4`='1', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='198' and`KeyCode`='1';
UPDATE `M_Code` SET `Class4`='2', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='198' and`KeyCode`='2';
UPDATE `M_Code` SET `Class4`='3', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='198' and`KeyCode`='3';
UPDATE `M_Code` SET `KeyContent` = 'LINE Pay請求書払い', `Class4`='4', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='198' and`KeyCode`='4';
UPDATE `M_Code` SET `KeyContent` = 'クレジット決済(VISA/MASTER）', `Class1`='credit_vm', `Class3`='VM', `Class4`='21', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='198' and`KeyCode`='5';
UPDATE `M_Code` SET `KeyContent` = 'PayPay請求書払い', `Class4`='8', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='198' and`KeyCode`='6';
UPDATE `M_Code` SET `Class4`='9', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='198' and`KeyCode`='7';
UPDATE `M_Code` SET `Class4`='10', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='198' and`KeyCode`='8';
UPDATE `M_Code` SET `Class4`='11', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='198' and`KeyCode`='10';
UPDATE `M_Code` SET `Class4`='12', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='198' and`KeyCode`='11';
UPDATE `M_Code` SET `Class4`='13', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='198' and`KeyCode`='13';
UPDATE `M_Code` SET `Class4`='14', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='198' and`KeyCode`='14';
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (198, 15, 'PayPay （オンライン決済）', 'paypay', '1', NULL, '24', '', 0, now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (198, 16, 'LINE Pay', 'linepay', '1', NULL, '25', '', 0, now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (198, 17, 'ソフトバンクまとめて支払い', 'softbank2', '1', NULL, '26', '', 0, now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (198, 18, 'ドコモ払い', 'docomo',  '1', NULL, '27', '', 0, now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (198, 19, 'auかんたん決済', 'auone', '1', NULL, '28', '', 0, now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES (198, 20, '楽天ペイ（オンライン決済）', 'rakuten', '1', NULL, '29', '', 0, now(), 1, now(), 1, 1);
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('198', '21', 'クレジット決済(JCB/AMEX）', 'credit_ja', '1', 'JA', '22', '0', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('198', '22', 'クレジット決済(Diners）', 'credit_d', '1', 'D', '23', '0', now(), '1', now(), '1', '1');

-- 199 --
UPDATE `M_Code` SET `Note`='https://atobarai-user.jp/todoitekara/#linepay', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='199' and`KeyCode`='22';
UPDATE `M_Code` SET `Note`='https://atobarai-user.jp/todoitekara/#post-office', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='199' and`KeyCode`='23';
UPDATE `M_Code` SET `Note`='https://atobarai-user.jp/todoitekara/#bank', UpdateDate = now(), UpdateId = 1 WHERE `CodeId`='199' and`KeyCode`='24';

-- 208 --
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Class4`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('208', '2', 'spapp2', NULL, NULL, NULL, NULL, '', 0, NOW(), 1, NOW(), 1, 1);

-- 213 --
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('213', '0', '後払いドットコム', 'customer@ato-barai.com', 'doc/help/atobarai_help.html','0', now(), '1', now(), '1', '1');
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('213', '1', '届いてから払い', 'todoitekara@ato-barai.com', 'doc/help/todoitekara_help.html', '0', now(), '1', now(), '1', '1');


-- T_SystemProperty
INSERT INTO `T_SystemProperty` (`Module`, `Category`, `Name`, `PropValue`, `Description`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('[DEFAULT]', 'sbpspayment', 'timeout', '600', 'タイムアウト（秒）', now(), '1', now(), '1', '1');
INSERT INTO `T_SystemProperty` (`Module`, `Category`, `Name`, `PropValue`, `Description`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('[DEFAULT]', 'sbpspayment', 'success_url', 'https://www1.atobarai-dev.jp/orderpage/success.php', '届いてから決済の成功URL', now(), '1', now(), '1', '1');
INSERT INTO `T_SystemProperty` (`Module`, `Category`, `Name`, `PropValue`, `Description`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('[DEFAULT]', 'sbpspayment', 'cancel_url', 'https://www1.atobarai-dev.jp/orderpage/cancel.php', '届いてから決済のキャンセルURL', now(), '1', now(), '1', '1');
INSERT INTO `T_SystemProperty` (`Module`, `Category`, `Name`, `PropValue`, `Description`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('[DEFAULT]', 'sbpspayment', 'error_url', 'https://www1.atobarai-dev.jp/orderpage/error.php', '届いてから決済のエラーURL', now(), '1', now(), '1', '1');
INSERT INTO `T_SystemProperty` (`Module`, `Category`, `Name`, `PropValue`, `Description`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('[DEFAULT]', 'sbpspayment', 'pagecon_url', 'https://www1.atobarai-dev.jp/orderpage/sbpssettlement/pagecon', '届いてから決済の結果CGIの返却URL', now(), '1', now(), '1', '1');
INSERT INTO `T_SystemProperty` (`Module`, `Category`, `Name`, `PropValue`, `Description`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('[DEFAULT]', 'sbpspayment', 'purchase_url', 'https://stbfep.sps-system.com/f01/FepBuyInfoReceive.do', '購入要求接続先', now(), '1', now(), '1', '1');


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

-- T_BatchLock
INSERT INTO `coraldb_test03`.`T_BatchLock` (`Seq`, `BatchId`, `ThreadNo`, `BatchName`, `BatchLock`, `UpdateDate`) VALUES ('6', '6', '1', '基幹連携バッチ', '0', now());

-- M_SbpsPayment
UPDATE `M_SbpsPayment` SET `LogoUrl`='my_page/todo_docomo.jpg' WHERE `SbpsPaymentId`='7';


/*マイページのデータベース*/
-- Create T_SbpsReceiptControl
ALTER TABLE T_SbpsReceiptControl ADD COLUMN `ValidFlg` TINYINT NOT NULL DEFAULT 1 AFTER `UpdateId`;
ALTER TABLE `coraldb_new01`.`t_sbpsreceiptcontrol` 
ADD COLUMN `Seq` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST,
CHANGE COLUMN `OrderSeq` `OrderSeq` BIGINT(20) NOT NULL COMMENT '注文Seq' ,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`Seq`);

-- T_ReceiptIssueHistory
ALTER TABLE `coraldb_mypage01`.`T_ReceiptIssueHistory` 
ADD COLUMN `ValidFlg` INT(11) NOT NULL DEFAULT 1 AFTER `RegistId`;

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
 *                  2022/06/07  OMINEXT 詳細入金画面の改修で備考を追加。

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

