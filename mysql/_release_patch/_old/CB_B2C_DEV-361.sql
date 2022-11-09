-- 前日
CREATE TABLE `T_CreditTransfer` (
  `CreditTransferId` bigint(20) NOT NULL COMMENT '口座振替ID',
  `CreditTransferName` varchar(20) NOT NULL COMMENT '口座振替名称',
  `CreditTransferSpanFromMonth` tinyint(4) NOT NULL DEFAULT '-1' COMMENT '口座振替対象期間－From種別',
  `CreditTransferSpanFromDay` int(11) NOT NULL DEFAULT '1' COMMENT '口座振替対象期間－From日付',
  `CreditTransferSpanToTypeMonth` tinyint(4) NOT NULL DEFAULT '-1' COMMENT '口座振替対象期間－To種別',
  `CreditTransferSpanToDay` int(11) NOT NULL DEFAULT '1' COMMENT '口座振替対象期間－To日付',
  `CreditTransferLimitDayType` tinyint(4) NOT NULL DEFAULT '1' COMMENT '口座振替支払期限条件種別',
  `CreditTransferDay` int(11) NOT NULL DEFAULT '1' COMMENT '口座振替日',
  `CreditTransferAfterLimitDayType` tinyint(4) DEFAULT NULL COMMENT '口座振替支払期限種別',
  `CreditTransferAfterLimitDay` int(11) NOT NULL DEFAULT '1' COMMENT '口座振替支払期限日',
  `ValidFlg` tinyint(4) NOT NULL DEFAULT '1' COMMENT '有効フラグ',
  `RegistDate` datetime NOT NULL COMMENT '登録日時',
  `RegistId` int(11) NOT NULL COMMENT '登録者',
  `UpdateDate` datetime NOT NULL COMMENT '更新日時',
  `UpdateId` int(11) NOT NULL COMMENT '更新者',
  PRIMARY KEY (`CreditTransferId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `T_CreditTransferCalendar` (
  `BusinessDate` date NOT NULL DEFAULT '0000-00-00' COMMENT '日付',
  `CreditTransferFlg` int(11) NOT NULL COMMENT '口座振替サービス',
  `DataType` int(11) NOT NULL COMMENT 'データ種別',
  `ExecFlg` int(11) NOT NULL DEFAULT '0' COMMENT '実行対象フラグ',
  `RegistDate` datetime DEFAULT NULL COMMENT '登録日時',
  `RegistId` int(11) DEFAULT NULL COMMENT '登録者',
  `UpdateDate` datetime DEFAULT NULL COMMENT '更新日時',
  `UpdateId` int(11) DEFAULT NULL COMMENT '更新者',
  `ValidFlg` int(11) NOT NULL DEFAULT '1' COMMENT '有効フラグ',
  PRIMARY KEY (`BusinessDate`,`CreditTransferFlg`,`DataType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `T_MufjReceipt` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'SEQ',
  `ResponseData` varchar(120) NOT NULL COMMENT 'レスポンスデータ',
  `ProcessClass` tinyint(4) NOT NULL DEFAULT '0' COMMENT '処理区分',
  `Note` varchar(1000) DEFAULT NULL COMMENT 'メモ',
  `OrderSeq` bigint(20) DEFAULT NULL COMMENT '注文SEQ',
  `EnterpriseId` bigint(20) DEFAULT NULL COMMENT '加盟店ID',
  `PaymentAmount` bigint(20) DEFAULT NULL COMMENT '入金金額',
  `ErrorFlg` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'エラーフラグ',
  `ImportDate` datetime DEFAULT NULL COMMENT '入金取込日時',
  `RegistDate` datetime DEFAULT NULL COMMENT '登録日時',
  `RegistId` int(11) DEFAULT NULL COMMENT '登録者',
  `UpdateDate` datetime DEFAULT NULL COMMENT '更新日時',
  `UpdateId` int(11) DEFAULT NULL COMMENT '更新者',
  `ValidFlg` int(11) NOT NULL DEFAULT '1' COMMENT '有効フラグ',
  PRIMARY KEY (`Seq`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `T_CreditTransferAlert` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'SEQ',
  `OrderSeq` bigint(20) NOT NULL COMMENT '注文SEQ',
  `EnterpriseId` bigint(20) NOT NULL COMMENT '加盟店ID',
  `EntCustSeq` bigint(20) NOT NULL COMMENT '加盟店顧客SEQ',
  `RegistDate` datetime DEFAULT NULL COMMENT '登録日時',
  `RegistId` int(11) DEFAULT NULL COMMENT '登録者',
  `UpdateDate` datetime DEFAULT NULL COMMENT '更新日時',
  `UpdateId` int(11) DEFAULT NULL COMMENT '更新者',
  `ValidFlg` int(11) NOT NULL DEFAULT '1' COMMENT '有効フラグ',
  PRIMARY KEY (`Seq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO T_Menu VALUES ('205', 'cbadmin', 'kanriMenus', 'credittransfer', null, '***', '口座振替期間設定', '口座振替期間設定', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO T_Menu VALUES ('206', 'cbadmin', 'keiriMenus', 'creacctrnsmufj', null, '***', '振替請求データ（MUFJ）作成', '振替請求データ（MUFJ）作成', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO T_Menu VALUES ('207', 'cbadmin', 'keiriMenus', 'dlacctrnsmufj', null, '***', '振替請求データ（MUFJ）ダウンロード', '振替請求データ（MUFJ）ダウンロード', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO T_Menu VALUES ('208', 'cbadmin', 'normalProcessMenus', 'credittransferalert', null, '***', '通常処理－口座アラート', '通常処理－口座アラート', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO T_Menu VALUES ('209', 'cbadmin', 'keiriMenus', 'impacctrnsmufj', null, '***', '振替結果（MUFJ）インポート', '振替結果（MUFJ）インポート', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO T_Menu VALUES ('210', 'cbadmin', 'keiriMenus', 'acctrnslistmufj', null, '***', '振替結果（MUFJ）一覧', '振替結果（MUFJ）一覧', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO T_Menu VALUES ('211', 'cbadmin', 'normalProcessMenus', 'mufjerror', null, '***', '通常処理－MUFJ入金エラーリスト', '通常処理－MUFJ入金エラーリスト', null, null, null, NOW(), '0', NOW(), '0', '1');

INSERT INTO T_MenuAuthority VALUES ('205', '1', NOW(), '0', NOW(), '0', '0');
INSERT INTO T_MenuAuthority VALUES ('205', '11', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('205', '101', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('205', '110', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('205', '120', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('205', '130', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('205', '140', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('205', '150', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('205', '160', NOW(), '0', NOW(), '0', '1');

INSERT INTO T_MenuAuthority VALUES ('206', '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('206', '11', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('206', '101', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('206', '110', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('206', '120', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('206', '130', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('206', '140', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('206', '150', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('206', '160', NOW(), '0', NOW(), '0', '1');

INSERT INTO T_MenuAuthority VALUES ('207', '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('207', '11', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('207', '101', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('207', '110', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('207', '120', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('207', '130', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('207', '140', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('207', '150', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('207', '160', NOW(), '0', NOW(), '0', '1');

INSERT INTO T_MenuAuthority VALUES ('208', '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('208', '11', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('208', '101', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('208', '110', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('208', '120', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('208', '130', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('208', '140', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('208', '150', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('208', '160', NOW(), '0', NOW(), '0', '1');

INSERT INTO T_MenuAuthority VALUES ('209', '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('209', '11', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('209', '101', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('209', '110', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('209', '120', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('209', '130', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('209', '140', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('209', '150', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('209', '160', NOW(), '0', NOW(), '0', '1');

INSERT INTO T_MenuAuthority VALUES ('210', '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('210', '11', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('210', '101', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('210', '110', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('210', '120', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('210', '130', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('210', '140', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('210', '150', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('210', '160', NOW(), '0', NOW(), '0', '1');

INSERT INTO T_MenuAuthority VALUES ('211', '1', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('211', '11', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('211', '101', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('211', '110', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('211', '120', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('211', '130', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('211', '140', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('211', '150', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_MenuAuthority VALUES ('211', '160', NOW(), '0', NOW(), '0', '1');

INSERT INTO M_TemplateField VALUES ('18', '158', 'CorporateName', '法人名', 'VARCHAR', '0', null, '0', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO M_TemplateField VALUES ('18', '159', 'CreditTransferFlg', '口座振替サービス', 'VARCHAR', '0', null, '0', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO M_TemplateField VALUES ('18', '160', 'CreditTransferRequestFlg', '口座振替利用', 'VARCHAR', '0', null, '0', null, null, null, NOW(), '0', NOW(), '0', '1');

INSERT INTO T_SystemProperty VALUES ('310', '[DEFAULT]', 'systeminfo', 'CATSConsignorCodeMUFJ', '33493000', '委託者コード(口座振替MUFJ用　数字10桁入力)', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_SystemProperty VALUES ('311', '[DEFAULT]', 'systeminfo', 'CATSConsignorNameMUFJ', 'ｷﾔﾂﾁﾎﾞ-ﾙ                                ', '委託者名(口座振替MUFJ用　半角40桁入力)', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_SystemProperty VALUES ('316', '[DEFAULT]', 'systeminfo', 'CATSBankCodeMufj',      '0149',                                     '取引銀行番号(口座振替MUFJ用　数字4桁入力)', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_SystemProperty VALUES ('318', '[DEFAULT]', 'systeminfo', 'CATSBranchCodeMufj',    '361',                                      '取引支店番号(口座振替MUFJ用　数字3桁入力)', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_SystemProperty VALUES ('319', '[DEFAULT]', 'systeminfo', 'CATSDepositTypeMufj',   '2',                                        '預金種目(口座振替MUFJ用　数字1桁入力)', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_SystemProperty VALUES ('320', '[DEFAULT]', 'systeminfo', 'CATSAccountNumberMufj', '0331751',                                  '口座番号(口座振替MUFJ用　数字7桁入力)', NOW(), '0', NOW(), '0', '1');

INSERT INTO T_SystemProperty VALUES ('312', '[DEFAULT]', 'mufjpayment', 'company', '08167', 'MUFJ収納企業番号', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_SystemProperty VALUES ('313', '[DEFAULT]', 'mufjpayment', 'password', '08167', 'MUFJ API パスワード', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_SystemProperty VALUES ('314', '[DEFAULT]', 'mufjpayment', 'url', '08167', 'MUFJ API URL', NOW(), '0', NOW(), '0', '1');
INSERT INTO T_SystemProperty VALUES ('315', '[DEFAULT]', 'mufjpayment', 'timeout', '08167', 'MUFJ API タイムアウト', NOW(), '0', NOW(), '0', '1');


-- 当日

INSERT INTO M_CodeManagement VALUES ('210', '口座振替申込サブステータス', null, '申込サブステータス', '1', 'ステータス', '0', null, '0', null,  NOW(), '0', NOW(), '0', '1');

-- コメントコードテーブル
INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES 
('101', '5', '三菱UFJファクター', 'MUFJ', '収納代行業者（三菱UFJファクター）', '未収金(MUFJ)　', '', '0', NOW(), '0',NOW(), '0', '1'),
('163', '4', 'LINEPay請求書払い', 'LINEPay請求書払い', NULL, NULL, '', '0', NOW(), '0',NOW(), '0', '1'),
('163', '5', 'クレジット決済', 'クレジット決済', NULL, NULL, '', '0', NOW(), '0',NOW(), '0', '1'),
('163', '6', 'PayPay', 'PayPay', NULL, NULL, '', '0', NOW(), '0',NOW(), '0', '1'),
('163', '7', 'PayB', 'PayB', NULL, NULL, '', '0', NOW(), '0',NOW(), '0', '1'),
('163', '8', 'PayEasy', 'PayEasy', NULL, NULL, '', '0', NOW(), '0',NOW(), '0', '1'),
('163', '14', 'FamiPay', 'FamiPay', NULL, NULL, '', '0', NOW(), '0',NOW(), '0', '1'),
('163', '13', '口座振替', '口座振替', NULL, NULL, '', '0', NOW(), '0',NOW(), '0', '1'),
('198', '13', '口座振替', NULL, NULL, NULL, '', '0', NOW(), '0',NOW(), '0', '1'),
('198', '14', 'FamiPay', NULL, NULL, NULL, '', '0', NOW(), '0',NOW(), '0', '1'),
('210', '1', '返送待ち', NULL, NULL, NULL, '', '0', NOW(), '0',NOW(), '0', '1'),
('210', '2', '銀行手続き中', NULL, NULL, NULL, '', '0', NOW(), '0',NOW(), '0', '1'),
('210', '3', 'お客様問い合せ中', NULL, NULL, NULL, '', '0', NOW(), '0',NOW(), '0', '1')
;



ALTER TABLE T_Site
  ADD COLUMN `MufjBarcodeUsedFlg` TINYINT NOT NULL DEFAULT 0 COMMENT '三菱UFバーコード利用フラグ' AFTER `ReceiptIssueProviso`
, ADD COLUMN `MufjBarcodeSubscriberCode` VARCHAR(10)  NULL COMMENT '三菱UFバーコード加入者固有コード' AFTER `MufjBarcodeUsedFlg`
;

ALTER TABLE T_Enterprise
  ADD COLUMN `ClaimIssueStopFlg` TINYINT NOT NULL DEFAULT 0 COMMENT '請求書発行停止フラグ' AFTER `ForceCancelClaimPattern`
, ADD COLUMN `FirstClaimIssueCtlFlg` TINYINT NOT NULL DEFAULT 0 COMMENT '初回請求書発行制御フラグ' AFTER `ClaimIssueStopFlg`
, ADD COLUMN `ReClaimIssueCtlFlg` TINYINT NOT NULL DEFAULT 0 COMMENT '再請求書発行制御フラグ' AFTER `FirstClaimIssueCtlFlg`
, ADD COLUMN `FirstReClaimLmitDateFlg` TINYINT NOT NULL DEFAULT 0 COMMENT '初回再発行の支払期限フラグ' AFTER `ReClaimIssueCtlFlg`
;

ALTER TABLE T_ClaimAccountTransferFile ADD COLUMN `CreditTransferFlg` INT NOT NULL DEFAULT 1 COMMENT '口座振替サービス' AFTER `Seq`;
ALTER TABLE T_ImportedAccountTransferFile ADD COLUMN `CreditTransferFlg` INT NOT NULL DEFAULT 1 COMMENT '口座振替サービス' AFTER `Seq`;

-- 2637.924ms　43分
ALTER TABLE T_EnterpriseCustomer
  ADD COLUMN `RequestSubStatus` INT  NULL COMMENT '申込サブステータス' AFTER `RequestStatus`
, MODIFY COLUMN `FfCode` VARCHAR(4)  NULL COMMENT '金融機関－金融機関番号'
, MODIFY COLUMN `FfBranchCode` VARCHAR(3)  NULL COMMENT '金融機関－支店番号'
;

-- 時刻: 1077.534ms　17分
ALTER TABLE T_ClaimHistory
  ADD COLUMN `CreditTransferMethod` INT NOT NULL DEFAULT 0 COMMENT '口振請求方法' AFTER `CreditMailRetryCount`
, ADD COLUMN `ZeroAmountClaimMailFlg` TINYINT NOT NULL DEFAULT 9 COMMENT '0円請求口振WEB申し込み案内メールフラグ' AFTER `CreditTransferMethod`
;

ALTER TABLE T_ClaimControl
  ADD COLUMN `CancelNoticePrintDate` DATE  NULL COMMENT '強制解約通知出力日' AFTER `CreditSettlementDecisionDate`
, ADD COLUMN `CancelNoticePrintStopStatus` TINYINT NOT NULL DEFAULT 1 COMMENT '処理区分' AFTER `CancelNoticePrintDate`
, ADD COLUMN `CreditTransferFlg` TINYINT NOT NULL DEFAULT 0 COMMENT '口座振替サービス' AFTER `CancelNoticePrintStopStatus`
;

