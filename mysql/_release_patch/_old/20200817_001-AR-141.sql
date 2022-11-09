/* T_ImportedPaymentAfterArrival登録 */
DROP TABLE IF EXISTS `T_ImportedPaymentAfterArrival`;
CREATE TABLE `T_ImportedPaymentAfterArrival` (
   `Seq`             bigint(20)   NOT NULL AUTO_INCREMENT
,  `FileName`        varchar(255) DEFAULT NULL
,  `Status`          int(11)      NOT NULL DEFAULT 0
,  `ReceiptResult`   longtext
,  `RegistDate`      datetime     DEFAULT NULL
,  `RegistId`        int(11)      DEFAULT NULL
,  `UpdateDate`      datetime     DEFAULT NULL
,  `UpdateId`        int(11)      DEFAULT NULL
,  `ValidFlg`        int(11)      NOT NULL DEFAULT '1'
,  PRIMARY KEY (`Seq`)
,  UNIQUE KEY `PaymentAfterArrivalName` (`FileName`)
,  KEY `Idx_T_ImportedPaymentAfterArrival01` (`FileName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* T_Menu/MenuAuthority登録(届いてから決済入金結果関連) */
INSERT INTO T_Menu VALUES (193, 'cbadmin', 'keiriMenus', 'imppaymentafter', NULL, '***', '届いてから決済インポート', '届いてから決済インポート', '', '', '', NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (193,   1, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (193,  11, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (193, 101, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_Menu VALUES (194, 'cbadmin', 'keiriMenus', 'paymentafterlist', NULL, '***', '届いてから決済結果一覧', '届いてから決済結果一覧', '', '', '', NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (194,   1, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (194,  11, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (194, 101, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_Menu VALUES (195, 'cbadmin', 'normalProcessMenus', 'jnbsberraccounts', NULL, '***', 'SoftBankPaymentServiceCancelApi連携エラー確認', 'SoftBankPaymentServiceCancelApi連携エラー確認', '', '', '', NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (195,   1, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (195,  11, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (195, 101, NOW(), 9, NOW(), 9, 1);

/* ﾃﾝﾌﾟﾚｰﾄﾌｨｰﾙﾄﾞのUPDATE（ListNumber に対するUPDATEなので、下から行う。） */
UPDATE M_TemplateField SET ListNumber='40' WHERE TemplateSeq='2' and PhysicalName='ClaimDate';
UPDATE M_TemplateField SET ListNumber='39' WHERE TemplateSeq='2' and PhysicalName='ReceiptDate';
UPDATE M_TemplateField SET ListNumber='38' WHERE TemplateSeq='2' and PhysicalName='IsWaitForReceipt';
/* デフォルトのﾃﾝﾌﾟﾚｰﾄﾌｨｰﾙﾄﾞのINSERT */
INSERT INTO M_TemplateField VALUES ( 2 , 37, 'ExtraPayKey' ,'トラッキングID' ,'CHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1) ;
/* 各加盟店のﾃﾝﾌﾟﾚｰﾄﾌｨｰﾙﾄﾞのINSERT */
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'ExtraPayKey', 'トラッキングID', 'CHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0 from M_TemplateField where TemplateSeq IN (SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKA01005_1' AND Seq > 0) group by TemplateSeq;

-- メールテンプレート【届いてから決済請求書発行メール】のUPDATE
UPDATE T_MailTemplate SET Body = '■{EnterpriseNameKj}■{OrderId}■{SiteNameKj}■{Phone}■{CustomerNameKj}■{OrderDate}■{UseAmount}■{LimitDate}■{SettlementFee}■{OrderItems}■{OneOrderItem}■{DeliveryFee}■{Tax}■{PassWord}■{CreditLimitDate}■{OrderPageAccessUrl}' WHERE Class= 104;
UPDATE T_MailTemplate SET Body = '■{EnterpriseNameKj}■{OrderId}■{SiteNameKj}■{Phone}■{CustomerNameKj}■{OrderDate}■{UseAmount}■{LimitDate}■{SettlementFee}■{OrderItems}■{OneOrderItem}■{DeliveryFee}■{Tax}■{PassWord}■{CreditLimitDate}■{OrderPageAccessUrl}' WHERE Class= 105;

/* 注文検索結果CSVのﾃﾝﾌﾟﾚｰﾄﾌｨｰﾙﾄﾞのINSERT */
INSERT INTO M_TemplateField VALUES ( 18 , 156, 'ExtraPayKey' ,'トラッキングID' ,'CHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1) ;

/* ｸﾚｼﾞｯﾄ決済利用時のﾃﾝﾌﾟﾚｰﾄﾌｨｰﾙﾄのINSERT */
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'CreditLimitDate','クレジット手続き期限日', 'DATE', 0, null, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 from M_TemplateField where TemplateSeq IN (SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKI04047_1') group by TemplateSeq;

/* 同梱請求書発行（CSV設定変更）のﾃﾝﾌﾟﾚｰﾄﾌｨｰﾙドのINSERT */
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'CreditLimitDate', 'クレジット手続き期限日', 'DATE', 0, null, 0, null, null, null, NOW(), 83, NOW(), 83, 0 from M_TemplateField where TemplateSeq IN (SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKA04016_1' ) group by TemplateSeq;

/* SB連携履歴（クレジット返金API履歴） */
DROP TABLE IF EXISTS T_SBPaymentSendResultHistory;
CREATE TABLE T_SBPaymentSendResultHistory(
   `Seq`                   bigint(20)   NOT NULL AUTO_INCREMENT
,  `OrderSeq`              bigint(20)   DEFAULT NULL
,  `OrderId`               varchar(50)  DEFAULT NULL
,  `ResResult`             varchar(2)   DEFAULT NULL
,  `ResSpsTransactionId`   varchar(32)  DEFAULT NULL
,  `ResProcessDate`        varchar(14)  DEFAULT NULL
,  `ResErrCode`            varchar(8)   DEFAULT NULL
,  `ResDate`               varchar(14)  DEFAULT NULL
,  `ErrorMessage`          longtext
,  `RegistDate`            datetime     DEFAULT NULL
,  `RegistId`              int(11)      DEFAULT NULL
,  `UpdateDate`            datetime     DEFAULT NULL
,  `UpdateId`              int(11)      DEFAULT NULL
,  `ValidFlg`              int(11)      NOT NULL DEFAULT '1'
,  PRIMARY KEY (`Seq`)
,  KEY `Idx_T_SBPaymentSendResultHistory01` (`Seq`)
,  KEY `Idx_T_SBPaymentSendResultHistory02` (`OrderSeq`)
,  KEY `Idx_T_SBPaymentSendResultHistory03` (`ResResult`)
);
