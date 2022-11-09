/* RENI重複登録ブロック */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]', 'systeminfo', 'RENIEnterpriseId', '17393', 'RENI【同梱】加盟店ID', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]', 'systeminfo', 'ApiDuplicateFlg', '0', '同梱API重複登録ブロックフラグ', NOW(), 9, NOW(), 9, '1');
