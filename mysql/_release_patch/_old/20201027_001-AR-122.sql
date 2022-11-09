/* システムプロパティ登録(申込完了予定日のデフォルト設定(口座振替用)) */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES('[DEFAULT]','systeminfo', 'CATSAppCompDate', '3', '申込完了予定日のデフォルト設定(口座振替用)', NOW(), 9, NOW(), 9, '1');
