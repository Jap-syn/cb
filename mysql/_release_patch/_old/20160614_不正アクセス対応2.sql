/* �A���s���A�N�Z�X��������(�A���s���𔻒肷��ׂ̂P��ڂ̃A�N�Z�X�����ۊ�) */
ALTER TABLE `T_NgAccessIp` ADD COLUMN `NgAccessReferenceDate` DATETIME NULL AFTER `UpdateDate`;

/* �A���s���A�N�Z�X�����Ԋu(�b) */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'NgAccessReferenceTerm', '600', '�A���s���A�N�Z�X�����Ԋu(�b)', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'NgAccessLoginReferenceTerm', '600', '�A���s���A�N�Z�X���O�C�������Ԋu(�b)', NOW(), 9, NOW(), 9, '1');

/* �s���A�N�Z�X���O�C�����~�b�g�o�^ */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'NgAccessLoginLimit', '5', '�s���A�N�Z�X���O�C�����~�b�g', NOW(), 9, NOW(), 9, '1');

/* �A���s���A�N�Z�X�֘A */
ALTER TABLE `T_Enterprise` 
ADD COLUMN `NgAccessCount` INT(11) NOT NULL DEFAULT '0' AFTER `CreditThreadNo`,
ADD COLUMN `NgAccessReferenceDate` DATETIME NULL AFTER `NgAccessCount`;
