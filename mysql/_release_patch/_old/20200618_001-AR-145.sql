/* 加盟店 */
ALTER TABLE T_Enterprise ADD COLUMN NTTSmartTradeFlg TINYINT NOT NULL DEFAULT 0 AFTER CreditTransferFlg;

/* T_SystemProperty登録(値に対しては桁数考慮あり) */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]', 'systeminfo', 'ReceiptAgentCode', '12345', '収納代行会社固有コード', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]', 'systeminfo', 'SubscriberCode'  , '12345', '加入者固有コード'      , NOW(), 9, NOW(), 9, '1');
