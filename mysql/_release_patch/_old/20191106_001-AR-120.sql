/* システムプロパティにデータ追加：注文登録APIタイムアウト時間（秒） */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'api','order', 'ApiOrderRest_TimeOut', '30', '注文登録APIタイムアウト時間（秒）', NOW(), 9, NOW(), 9, '1');

/* 加盟店にカラム追加：注文登録APIタイムアウトフラグ */
ALTER TABLE T_Enterprise ADD COLUMN `ApiOrderRestTimeOutFlg` TINYINT NOT NULL DEFAULT 0 AFTER `LinePayUseFlg`;
