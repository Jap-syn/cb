/* ����_��v�e�[�u���ɓ`�[�m�F���[�����M�X�g�b�v�ݒ��ǉ� */
ALTER TABLE `AT_Order` 
ADD COLUMN `StopSendMailConfirmJournalFlg` TINYINT(4) NOT NULL DEFAULT 0 AFTER `DefectCancelPlanDate`;

/* �����X�e�[�u���ɊԈႢ�`�ԏC���˗����[����ǉ� */
ALTER TABLE `T_Enterprise` 
ADD COLUMN `SendMailRequestModifyJournalFlg` TINYINT(4) NOT NULL DEFAULT 1 AFTER `HoldBoxFlg`;

/* �z�����@�e�[�u���ɏC���˗����[��(�̑��M�L���`�F�b�N)��ǉ� */
ALTER TABLE `M_DeliveryMethod` 
ADD COLUMN `SendMailRequestModifyJournalFlg` TINYINT(4) NOT NULL DEFAULT 1 AFTER `ProductServiceClass`;

/* ���[�����M�����e�[�u���ɃC���f�b�N�X��ǉ�(03�Ԃ͉^�p���ɂĈꕔ�ݒ肳��Ă���̂�04����Ƃ���) */
ALTER TABLE `T_MailSendHistory`
ADD INDEX `Idx_T_MailSendHistory04` (`EnterpriseId` ASC),
ADD INDEX `Idx_T_MailSendHistory05` (`MailSendDate` ASC);


/* �R�[�h�}�X�^�[�ɓ`�[�m�F���[�����M��ǉ� */
INSERT INTO M_Code VALUES(97,35 ,'�`�[�m�F���[���i�ĂP�j���M',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(97,36 ,'�`�[�m�F���[���i�ĂR�j���M',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(97,37 ,'�`�[�m�F���[���i�蓮�j���M',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);

/* ���[���e���v���[�g��[�ԈႢ�`�[�C���˗����[��][�𓀃p�X���[�h�ʒm���[��]�ǉ� */
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (94,'�ԈႢ�`�[�C���˗����[��','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'�y�㕥���h�b�g�R���z�`�[�ԍ��̂��m�F�����肢�������܂� ','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}�l \r\n\r\n�����b�ɂȂ��Ă���܂��B \r\n�㕥���h�b�g�R���J�X�^�}�[�Z���^�[�ł������܂��B \r\n\r\n{ReceiptOrderDate}�ɂ������o�^�����������܂����A���L���q�l�̒��׊m�F�����Ȃ��ׁA���󗧑ւ������Ă����������Ƃ��ł��Ă���܂���B \r\n\r\n���o�^�����������z���`�[�ԍ��ɓ��̓~�X�����邩�A ���i�����q�l�ɓ͂��Ă��Ȃ��\�����������܂��B \r\n���萔���������������܂��� �l���̌��ˍ������������܂��̂ŁA���i�̔z����ЁA�z���`�[�ԍ��A���тɔz���󋵂���x�X�ܗl���ł��m�F���������A�X�ܗl�Ǘ��T�C�g�ォ�炲�C�����������܂��悤���肢�������܂��B\r\n ���ҏW���@�͗��������������̂��������i�荞��ł��������A�w�o�^���e�̏C���x���炲�C�����������B \r\n\r\n�܂��A���������L�����Z�������ꍇ�͓X�ܗl�Ǘ��T�C�g���L�����Z���̐\�������肢�������܂��B \r\n\r\n�����ID �F{OrderId} \r\n\r\n�� �ڍ׏��ɂ��ẮA�Y�t�̃t�@�C��(�𓀌��CSV�`��)�����Q�Ƃ��������B\r\n�� �Y�t�t�@�C���́A�l���ی�̊ϓ_����p�X���[�h��ݒ肵�Ă���܂��B\r\n�� �Y�t�t�@�C���̃p�X���[�h�͕ʂ̃��[���ɂĂ��m�点�������܂��B\r\n�� �Y�t�t�@�C���́A�u�ꊇ�����L�����Z���iCSV�j�v�ɂāA�L�����Z���\���p��CSV�Ƃ��āA���̂܂܂����p���������܂��B\r\n\r\n���A�{������2�T�Ԉȓ��ɂ��ύX�܂��͂��A���������������A �`�[�ԍ��o�^������񔼔N���o�߂��A�z����Ђ̒ǐՃT�[�r�X�ɂ� ���ׂ̊m�F�����Ȃ��Ȃ��Ă��܂����ꍇ�A ���ۏ؈����ƂȂ菇�����ԋp�������Ă��������܂��̂ł����ӊ肢�܂��B \r\n\r\n���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹���������܂��B \r\n\r\n����Ƃ���낵�����肢�������܂��B \r\n\r\n\r\n-------------------------------------------------------------- \r\n�㕥��������s�T�[�r�X�y�㕥���h�b�g�R���z   \r\n���⍇����F0120-667-690   �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j   \r\nmail: customer@ato-barai.com   \r\n�^�c��ЁF������ЃL���b�`�{�[�� �@\r\n�Z���F��160-0023 �����s�V�h�搼�V�h6-14-1 �V�h�O���[���^���[�r��14F \r\n--------------------------------------------------------------\r\n',0,'2017-03-15 17:00:00',1,'2017-04-28 18:26:41',83,1);
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (95,'�𓀃p�X���[�h�ʒm���[��','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'�y�㕥���h�b�g�R���z �𓀃p�X���[�h�ʒm���[��','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}�l \r\n\r\n�����b�ɂȂ��Ă���܂��B  \r\n�㕥���h�b�g�R���J�X�^�}�[�Z���^�[�ł������܂��B  \r\n\r\n��قǁA�u�y�㕥���h�b�g�R���z�`�[�ԍ��̂��m�F�����肢�������܂� �v�̌����ŁA\r\n�����肵�����[���ɓY�t���ꂽ�t�@�C���̊J���p�X���[�h�����m�点�������܂��B\r\n\r\n�Y�t�t�@�C����: {FileName} \r\n�𓀃p�X���[�h: {Password} \r\n\r\n���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹���������܂��B \r\n\r\n����Ƃ���낵�����肢�������܂��B \r\n\r\n-------------------------------------------------------------- \r\n�㕥��������s�T�[�r�X�y�㕥���h�b�g�R���z   \r\n���⍇����F0120-667-690   �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j   \r\nmail: customer@ato-barai.com   \r\n�^�c��ЁF������ЃL���b�`�{�[�� �@\r\n�Z���F��160-0023 �����s�V�h�搼�V�h6-14-1 �V�h�O���[���^���[�r��14F \r\n--------------------------------------------------------------',0,'2017-03-15 17:00:00',1,'2017-03-15 17:00:00',1,1);

