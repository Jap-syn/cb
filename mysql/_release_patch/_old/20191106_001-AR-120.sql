/* �V�X�e���v���p�e�B�Ƀf�[�^�ǉ��F�����o�^API�^�C���A�E�g���ԁi�b�j */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( 'api','order', 'ApiOrderRest_TimeOut', '30', '�����o�^API�^�C���A�E�g���ԁi�b�j', NOW(), 9, NOW(), 9, '1');

/* �����X�ɃJ�����ǉ��F�����o�^API�^�C���A�E�g�t���O */
ALTER TABLE T_Enterprise ADD COLUMN `ApiOrderRestTimeOutFlg` TINYINT NOT NULL DEFAULT 0 AFTER `LinePayUseFlg`;
