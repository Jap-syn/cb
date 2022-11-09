/* メール送信日登録 */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'PaymentSoonDays', '3', 'もうすぐお支払いメール送信タイミング', NOW(), 9, NOW(), 9, '1');
