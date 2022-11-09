/* 連続不正アクセス判定基準時刻(連続不正を判定する為の１回目のアクセス時刻保管) */
ALTER TABLE `T_NgAccessIp` ADD COLUMN `NgAccessReferenceDate` DATETIME NULL AFTER `UpdateDate`;

/* 連続不正アクセス判定基準間隔(秒) */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'NgAccessReferenceTerm', '600', '連続不正アクセス判定基準間隔(秒)', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'NgAccessLoginReferenceTerm', '600', '連続不正アクセスログイン判定基準間隔(秒)', NOW(), 9, NOW(), 9, '1');

/* 不正アクセスログインリミット登録 */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'NgAccessLoginLimit', '5', '不正アクセスログインリミット', NOW(), 9, NOW(), 9, '1');

/* 連続不正アクセス関連 */
ALTER TABLE `T_Enterprise` 
ADD COLUMN `NgAccessCount` INT(11) NOT NULL DEFAULT '0' AFTER `CreditThreadNo`,
ADD COLUMN `NgAccessReferenceDate` DATETIME NULL AFTER `NgAccessCount`;
