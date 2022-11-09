CREATE TABLE `T_SitePayment` (
  `SitePaymentId` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '支払方法ID',
  `SiteId` bigint(20) NOT NULL COMMENT 'サイトID',
  `PaymentId` int(11) NOT NULL COMMENT '支払方法ID',
  `UseFlg` tinyint(4) NOT NULL COMMENT '利用可否',
  `ApplyDate` date DEFAULT NULL COMMENT '申請日',
  `UseStartDate` date DEFAULT NULL COMMENT '利用開始日',
  `UseEndDate` date DEFAULT NULL COMMENT '利用終了日',
  `ApplyFinishDate` date DEFAULT NULL COMMENT '申請完了日',
  `UseStartFixFlg` tinyint(4) NOT NULL DEFAULT '0' COMMENT '利用開始確定フラグ',
  `ValidFlg` tinyint(4) NOT NULL DEFAULT '1' COMMENT '有効フラグ',
  `RegistDate` datetime NOT NULL COMMENT '登録日時',
  `RegistId` int(11) NOT NULL COMMENT '登録者',
  `UpdateDate` datetime NOT NULL COMMENT '更新日時',
  `UpdateId` int(11) NOT NULL COMMENT '更新者',
  PRIMARY KEY (`SitePaymentId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='サイト支払方法';

CREATE TABLE `M_Payment` (
  `PaymentId` int(11) NOT NULL AUTO_INCREMENT COMMENT '支払方法ID',
  `OemId` bigint(20) NOT NULL COMMENT 'OEMID',
  `PaymentGroupName` varchar(50) NOT NULL COMMENT '支払方法グループ名称',
  `PaymentName` varchar(50) NOT NULL COMMENT '支払方法名称',
  `SortId` int(11) NOT NULL DEFAULT '0' COMMENT '表示順',
  `UseFlg` tinyint(4) NOT NULL DEFAULT '0' COMMENT '使用可否区分',
  `FixedId` tinyint(4) NOT NULL DEFAULT '0' COMMENT '固有ロジックID',
  `ValidFlg` tinyint(4) NOT NULL DEFAULT '1' COMMENT '有効フラグ',
  PRIMARY KEY (`PaymentId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='支払方法マスタ';

ALTER TABLE `M_SubscriberCode` ADD COLUMN `LineApplyDate` date DEFAULT NULL COMMENT 'LINE申込日' AFTER `LinePayUseFlg`,
                               ADD COLUMN `LineUseStartDate` date DEFAULT NULL COMMENT 'LINE利用開始日' AFTER `LineApplyDate`,
                               ADD COLUMN `RakutenBankUseFlg` tinyint(50) DEFAULT NULL COMMENT '楽天銀行利用可否区分' AFTER `LineUseStartDate`,
                               ADD COLUMN `FamiPayUseFlg` tinyint(50) DEFAULT NULL COMMENT 'FamiPay利用可否区分' AFTER `RakutenBankUseFlg`;

INSERT INTO M_Payment VALUES ('1', '0', '請求書払い', 'PayB', '1', '1', '1', '1');
INSERT INTO M_Payment VALUES ('2', '0', '請求書払い', 'PayPay', '2', '1', '2', '1');
INSERT INTO M_Payment VALUES ('3', '0', '請求書払い', 'LINEPay請求書払い', '3', '0', '3', '1');
INSERT INTO M_Payment VALUES ('4', '0', '請求書払い', '楽天銀行', '4', '0', '4', '1');
INSERT INTO M_Payment VALUES ('5', '0', '請求書払い', 'FamiPay', '5', '0', '5', '1');
INSERT INTO M_Payment VALUES ('6', '0', '請求書払い', 'ゆうちょPay', '6', '0', '6', '1');
INSERT INTO M_Payment VALUES ('7', '1', '請求書払い', 'PayB', '1', '1', '1', '0');
INSERT INTO M_Payment VALUES ('8', '1', '請求書払い', 'PayPay', '2', '1', '2', '0');
INSERT INTO M_Payment VALUES ('9', '1', '請求書払い', 'LINEPay請求書払い', '3', '0', '3', '0');
INSERT INTO M_Payment VALUES ('10', '1', '請求書払い', '楽天銀行', '4', '0', '4', '1');
INSERT INTO M_Payment VALUES ('11', '1', '請求書払い', 'FamiPay', '5', '0', '5', '1');
INSERT INTO M_Payment VALUES ('12', '1', '請求書払い', 'ゆうちょPay', '6', '0', '6', '0');
INSERT INTO M_Payment VALUES ('13', '2', '請求書払い', 'PayB', '1', '1', '1', '0');
INSERT INTO M_Payment VALUES ('14', '2', '請求書払い', 'PayPay', '2', '1', '2', '0');
INSERT INTO M_Payment VALUES ('15', '2', '請求書払い', 'LINEPay請求書払い', '3', '0', '3', '0');
INSERT INTO M_Payment VALUES ('16', '2', '請求書払い', '楽天銀行', '4', '0', '4', '1');
INSERT INTO M_Payment VALUES ('17', '2', '請求書払い', 'FamiPay', '5', '0', '5', '1');
INSERT INTO M_Payment VALUES ('18', '2', '請求書払い', 'ゆうちょPay', '6', '0', '6', '0');
INSERT INTO M_Payment VALUES ('19', '3', '請求書払い', 'PayB', '1', '1', '1', '0');
INSERT INTO M_Payment VALUES ('20', '3', '請求書払い', 'PayPay', '2', '1', '2', '0');
INSERT INTO M_Payment VALUES ('21', '3', '請求書払い', 'LINEPay請求書払い', '3', '0', '3', '0');
INSERT INTO M_Payment VALUES ('22', '3', '請求書払い', '楽天銀行', '4', '0', '4', '1');
INSERT INTO M_Payment VALUES ('23', '3', '請求書払い', 'FamiPay', '5', '0', '5', '1');
INSERT INTO M_Payment VALUES ('24', '3', '請求書払い', 'ゆうちょPay', '6', '0', '6', '0');
INSERT INTO M_Payment VALUES ('25', '4', '請求書払い', 'PayB', '1', '1', '1', '0');
INSERT INTO M_Payment VALUES ('26', '4', '請求書払い', 'PayPay', '2', '1', '2', '0');
INSERT INTO M_Payment VALUES ('27', '4', '請求書払い', 'LINEPay請求書払い', '3', '0', '3', '0');
INSERT INTO M_Payment VALUES ('28', '4', '請求書払い', '楽天銀行', '4', '0', '4', '1');
INSERT INTO M_Payment VALUES ('29', '4', '請求書払い', 'FamiPay', '5', '0', '5', '1');
INSERT INTO M_Payment VALUES ('30', '4', '請求書払い', 'ゆうちょPay', '6', '0', '6', '0');
INSERT INTO M_Payment VALUES ('31', '5', '請求書払い', 'PayB', '1', '1', '1', '0');
INSERT INTO M_Payment VALUES ('32', '5', '請求書払い', 'PayPay', '2', '1', '2', '0');
INSERT INTO M_Payment VALUES ('33', '5', '請求書払い', 'LINEPay請求書払い', '3', '0', '3', '0');
INSERT INTO M_Payment VALUES ('34', '5', '請求書払い', '楽天銀行', '4', '0', '4', '1');
INSERT INTO M_Payment VALUES ('35', '5', '請求書払い', 'FamiPay', '5', '0', '5', '1');
INSERT INTO M_Payment VALUES ('36', '5', '請求書払い', 'ゆうちょPay', '6', '0', '6', '0');
INSERT INTO M_Payment VALUES ('37', '6', '請求書払い', 'PayB', '1', '1', '1', '0');
INSERT INTO M_Payment VALUES ('38', '6', '請求書払い', 'PayPay', '2', '1', '2', '0');
INSERT INTO M_Payment VALUES ('39', '6', '請求書払い', 'LINEPay請求書払い', '3', '0', '3', '0');
INSERT INTO M_Payment VALUES ('40', '6', '請求書払い', '楽天銀行', '4', '0', '4', '1');
INSERT INTO M_Payment VALUES ('41', '6', '請求書払い', 'FamiPay', '5', '0', '5', '1');
INSERT INTO M_Payment VALUES ('42', '6', '請求書払い', 'ゆうちょPay', '6', '0', '6', '0');



UPDATE T_Site SET BarcodeLimitDays = 15;
UPDATE T_Site SET BarcodeLimitDays = 0 WHERE EnterpriseId IN (19205,25791,27385,27386,28432);

