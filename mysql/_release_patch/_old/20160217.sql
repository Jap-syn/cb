ALTER TABLE T_Site ADD COLUMN `MultiOrderCount` INT  NULL AFTER `ClaimDisposeMail`;
ALTER TABLE T_Site ADD COLUMN `MultiOrderScore` INT  NULL AFTER `MultiOrderCount`;
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'MultiOrderDays', '3', '�A���������ԁi�^�M�j', NOW(), 9, NOW(), 9, '1');
