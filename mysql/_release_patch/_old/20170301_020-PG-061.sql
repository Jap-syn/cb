--------------------------------------------------------------------------------
-- �ȉ��A�{�̑��X�L�[�}�֓o�^
--------------------------------------------------------------------------------
-- �}�C�y�[�W���X�L�[�}��{�̃X�L�[�}�̃r���[��(�����O��[�}�C�y�[�W���X�L�[�}�֓o�^]���{�̂���)
DROP VIEW IF EXISTS `MPV_MypageCustomer`;
CREATE VIEW `MPV_MypageCustomer` AS SELECT * FROM coraldb_mypage01.T_MypageCustomer;

INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (92,'�}�C�y�[�W�p�X���[�h�Ĕ��s���[���iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'�y�㕥���h�b�g�R���z�p�X���[�h�Ĕ��s�̂��ē�','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','���̓x�͌㕥���h�b�g�R���������p��������\r\n�܂��Ƃɂ��肪�Ƃ��������܂��B\r\n\r\n���L��URL���N���b�N���Č㕥���h�b�g�R���ł�\r\n�p�X���[�h�Đݒ��i�߂Ă��������B\r\n\r\n{MypagePasswordResetUrl}\r\n\r\n\r\n�������ӎ�����\r\n�E�{���[�������󂯎���A�Q�S���Ԉȓ��Ƀp�X���[�h�Đݒ��\r\n�������Ă��������܂��悤���肢�������܂��B\r\n�E�Q�S���Ԉȓ��Ƀp�X���[�h�Đݒ肪��������Ȃ��ꍇ�͂��葱����������\r\n�Ȃ�܂��̂ł��炩���߂������肢�܂��B\r\n�E�Q�S���Ԃ��߂��Ă��܂����ꍇ�́A�������܂����ēx�Ĕ��s�̂��葱����\r\n���肢�������܂��B\r\n\r\n\r\n------------------------------------\r\n�Đݒ�̎菇�ɂ���\r\n------------------------------------\r\n\r\n�P.��LURL�ɃA�N�Z�X���A��ʂɂ��������ĕK�v�����������͂��������B\r\n\r\n�Q.�����͓��e�����m�F�̂����A�V�����p�X���[�h��o�^���Ă��������B\r\n\r\n�ȏ�Ńp�X���[�h�Đݒ芮���ƂȂ�܂��B\r\n\r\n\r\n------------------------------------\r\n�Đݒ肪���܂������Ȃ��ꍇ�́A\r\n��ϋ������܂���customer@ato-barai.com�܂�\r\n���₢���킹�����肢�������܂��B\r\n\r\n���c�Ǝ��ԊO�̂��₢���킹�ɂ��܂��Ă�\r\n���ԐM�ɂ����Ԃ����������ꍇ���������܂��B\r\n\r\n\r\n���̓x�͂����p���肪�Ƃ��������܂����B\r\n\r\n\r\n--------------------------------------------------------------\r\n�㕥��������s�T�[�r�X�y�㕥���h�b�g�R���z\r\n\r\n  ���⍇����F03-5909-3490\r\n  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j\r\n  mail: customer@ato-barai.com\r\n  \r\n�@�^�c��ЁF������ЃL���b�`�{�[��\r\n�@�Z���F��160-0023 �����s�V�h�搼�V�h6-14-1 �V�h�O���[���^���[�r��14F\r\n--------------------------------------------------------------',0,'2017-03-02 16:00:00',1,'2017-03-02 16:17:24',83,1);
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (93,'�}�C�y�[�W�p�X���[�h�Ĕ��s���[���iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'�y�㕥���h�b�g�R���z�p�X���[�h�Ĕ��s�̂��ē�','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','���̓x�͌㕥���h�b�g�R���������p��������\r\n�܂��Ƃɂ��肪�Ƃ��������܂��B\r\n\r\n���L��URL���N���b�N���Č㕥���h�b�g�R���ł�\r\n�p�X���[�h�Đݒ��i�߂Ă��������B\r\n\r\n{MypagePasswordResetUrl}\r\n\r\n\r\n�������ӎ�����\r\n�E�{���[�������󂯎���A�Q�S���Ԉȓ��Ƀp�X���[�h�Đݒ��\r\n�������Ă��������܂��悤���肢�������܂��B\r\n�E�Q�S���Ԉȓ��Ƀp�X���[�h�Đݒ肪��������Ȃ��ꍇ�͂��葱����������\r\n�Ȃ�܂��̂ł��炩���߂������肢�܂��B\r\n�E�Q�S���Ԃ��߂��Ă��܂����ꍇ�́A�������܂����ēx�Ĕ��s�̂��葱����\r\n���肢�������܂��B\r\n\r\n\r\n------------------------------------\r\n�Đݒ�̎菇�ɂ���\r\n------------------------------------\r\n\r\n�P.��LURL�ɃA�N�Z�X���A��ʂɂ��������ĕK�v�����������͂��������B\r\n\r\n�Q.�����͓��e�����m�F�̂����A�V�����p�X���[�h��o�^���Ă��������B\r\n\r\n�ȏ�Ńp�X���[�h�Đݒ芮���ƂȂ�܂��B\r\n\r\n\r\n------------------------------------\r\n�Đݒ肪���܂������Ȃ��ꍇ�́A\r\n��ϋ������܂���customer@ato-barai.com�܂�\r\n���₢���킹�����肢�������܂��B\r\n\r\n���c�Ǝ��ԊO�̂��₢���킹�ɂ��܂��Ă�\r\n���ԐM�ɂ����Ԃ����������ꍇ���������܂��B\r\n\r\n\r\n���̓x�͂����p���肪�Ƃ��������܂����B\r\n\r\n\r\n--------------------------------------------------------------\r\n�㕥��������s�T�[�r�X�y�㕥���h�b�g�R���z\r\n\r\n  ���⍇����F03-5909-3490\r\n  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j\r\n  mail: customer@ato-barai.com\r\n  \r\n�@�^�c��ЁF������ЃL���b�`�{�[��\r\n�@�Z���F��160-0023 �����s�V�h�搼�V�h6-14-1 �V�h�O���[���^���[�r��14F\r\n--------------------------------------------------------------',0,'2017-03-02 16:00:00',1,'2017-03-02 16:17:41',83,1);
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (92,'�}�C�y�[�W�p�X���[�h�Ĕ��s���[���iPC�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',NULL,NULL,NULL,'�y�㕥�����σ}�C�y�[�W�z�p�X���[�h�Ĕ��s�̂��ē�','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','���̓x�́y�㕥�����σ}�C�y�[�W�z�������p��������\r\n�܂��Ƃɂ��肪�Ƃ��������܂��B\r\n\r\n���L��URL���N���b�N���āy�㕥�����σ}�C�y�[�W�z�ł�\r\n�p�X���[�h�Đݒ��i�߂Ă��������B\r\n\r\n{MypagePasswordResetUrl}\r\n\r\n\r\n�������ӎ�����\r\n�E�{���[�������󂯎���A�Q�S���Ԉȓ��Ƀp�X���[�h�Đݒ��\r\n�������Ă��������܂��悤���肢�������܂��B\r\n�E�Q�S���Ԉȓ��Ƀp�X���[�h�Đݒ肪��������Ȃ��ꍇ�͂��葱����������\r\n�Ȃ�܂��̂ł��炩���߂������肢�܂��B\r\n�E�Q�S���Ԃ��߂��Ă��܂����ꍇ�́A�������܂����ēx�Ĕ��s�̂��葱����\r\n���肢�������܂��B\r\n\r\n\r\n------------------------------------\r\n�Đݒ�̎菇�ɂ���\r\n------------------------------------\r\n\r\n�P.��LURL�ɃA�N�Z�X���A��ʂɂ��������ĕK�v�����������͂��������B\r\n\r\n�Q.�����͓��e�����m�F�̂����A�V�����p�X���[�h��o�^���Ă��������B\r\n\r\n�ȏ�Ńp�X���[�h�Đݒ芮���ƂȂ�܂��B\r\n\r\n\r\n------------------------------------\r\n�Đݒ肪���܂������Ȃ��ꍇ�́A\r\n��ϋ������܂���sfc-atobarai@seino.co.jp�܂�\r\n���₢���킹�����肢�������܂��B\r\n\r\n���c�Ǝ��ԊO�̂��₢���킹�ɂ��܂��Ă�\r\n���ԐM�ɂ����Ԃ����������ꍇ���������܂��B\r\n\r\n\r\n���̓x�͂����p���肪�Ƃ��������܂����B\r\n\r\n\r\n--------------------------------------------------------------\r\n\r\n�y�㕥�����σT�[�r�X�z\r\n  ���⍇����F03-5909-4500\r\n  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j\r\n  mail: sfc-atobarai@seino.co.jp\r\n\r\n  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������\r\n�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn\r\n\r\n--------------------------------------------------------------',3,'2017-03-06 10:50:00',1,'2017-03-06 10:57:58',83,1);
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (93,'�}�C�y�[�W�p�X���[�h�Ĕ��s���[���iCEL�j','�㕥�����σT�[�r�X','=?UTF-8?B?GyRCOGVKJyQkN2g6USU1ITwlUyU5GyhC?=','sfc-atobarai@seino.co.jp',NULL,NULL,NULL,'�y�㕥�����σ}�C�y�[�W�z�p�X���[�h�Ĕ��s�̂��ē�','=?UTF-8?B?5b6M5omV44GE5rG65riI44K144O844OT44K5?=','���̓x�́y�㕥�����σ}�C�y�[�W�z�������p��������\r\n�܂��Ƃɂ��肪�Ƃ��������܂��B\r\n\r\n���L��URL���N���b�N���āy�㕥�����σ}�C�y�[�W�z�ł�\r\n�p�X���[�h�Đݒ��i�߂Ă��������B\r\n\r\n{MypagePasswordResetUrl}\r\n\r\n\r\n�������ӎ�����\r\n�E�{���[�������󂯎���A�Q�S���Ԉȓ��Ƀp�X���[�h�Đݒ��\r\n�������Ă��������܂��悤���肢�������܂��B\r\n�E�Q�S���Ԉȓ��Ƀp�X���[�h�Đݒ肪��������Ȃ��ꍇ�͂��葱����������\r\n�Ȃ�܂��̂ł��炩���߂������肢�܂��B\r\n�E�Q�S���Ԃ��߂��Ă��܂����ꍇ�́A�������܂����ēx�Ĕ��s�̂��葱����\r\n���肢�������܂��B\r\n\r\n\r\n------------------------------------\r\n�Đݒ�̎菇�ɂ���\r\n------------------------------------\r\n\r\n�P.��LURL�ɃA�N�Z�X���A��ʂɂ��������ĕK�v�����������͂��������B\r\n\r\n�Q.�����͓��e�����m�F�̂����A�V�����p�X���[�h��o�^���Ă��������B\r\n\r\n�ȏ�Ńp�X���[�h�Đݒ芮���ƂȂ�܂��B\r\n\r\n\r\n------------------------------------\r\n�Đݒ肪���܂������Ȃ��ꍇ�́A\r\n��ϋ������܂���sfc-atobarai@seino.co.jp�܂�\r\n���₢���킹�����肢�������܂��B\r\n\r\n���c�Ǝ��ԊO�̂��₢���킹�ɂ��܂��Ă�\r\n���ԐM�ɂ����Ԃ����������ꍇ���������܂��B\r\n\r\n\r\n���̓x�͂����p���肪�Ƃ��������܂����B\r\n\r\n\r\n--------------------------------------------------------------\r\n\r\n�y�㕥�����σT�[�r�X�z\r\n  ���⍇����F03-5909-4500\r\n  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j\r\n  mail: sfc-atobarai@seino.co.jp\r\n\r\n  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������\r\n�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn\r\n\r\n--------------------------------------------------------------',3,'2017-03-06 10:50:00',1,'2017-03-06 10:58:20',83,1);

-- ���ID��Y�ꂽ���pURL
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg
) VALUES( 'mypage','application_global', 'faq_link', 'https://atobarai-user.jp/faq/atobarai/193/', '���ID��Y�ꂽ���pURL', NOW(), 9, NOW(), 9, '1');


--------------------------------------------------------------------------------
-- �ȉ��A�}�C�y�[�W���X�L�[�}�֓o�^
--------------------------------------------------------------------------------
ALTER TABLE `T_MypageCustomer` 
ADD COLUMN `AccessKey` VARCHAR(100) NULL AFTER `NgAccessReferenceDate`,
ADD COLUMN `AccessKeyValidToDate` DATETIME NULL AFTER `AccessKey`,
ADD COLUMN `Reserve` TEXT NULL DEFAULT NULL AFTER `AccessKeyValidToDate`;

ALTER TABLE `T_MypageCustomer` ADD INDEX `idx_02` (`AccessKey` ASC);

UPDATE T_MypageCustomer t
SET t.ManCustId = (SELECT MAX(ManCustId) FROM MV_ManagementCustomer WHERE MailAddress = t.MailAddress AND RegNameKj = t.RegNameKj AND RegUnitingAddress = t.RegUnitingAddress AND RegPhone IN (t.RegPhone, t.RegMobilePhone))
WHERE t.ManCustId IS NULL;