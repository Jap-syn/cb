/* ����_��v�e�[�u���ɍ��ڒǉ� */
ALTER TABLE `AT_Order` 
ADD COLUMN `DefectFlg` TINYINT(4) NOT NULL DEFAULT 0 AFTER `RepayPendingFlg`,
ADD COLUMN `DefectInvisibleFlg` TINYINT(4) NOT NULL DEFAULT 0 AFTER `DefectFlg`,
ADD COLUMN `DefectNote` TEXT NULL AFTER `DefectInvisibleFlg`,
ADD COLUMN `DefectCancelPlanDate` DATETIME NULL AFTER `DefectNote`;

/* ����_��v�e�[�u���̒ǉ����ڂɃC���f�b�N�X�t�^ */
ALTER TABLE `AT_Order` 
ADD INDEX `Idx_AT_Order02` (`DefectFlg` ASC);

/* �^�M���ʃ��O�e�[�u���ɍ��ڒǉ� */
ALTER TABLE `T_CreditLog` 
ADD COLUMN `Jud_DefectOrderYN` TINYINT NULL AFTER `Incre_SnapShot`;
UPDATE T_CreditLog SET Jud_DefectOrderYN = 0;    /* �����f�[�^��0�ݒ� */


/* �����X�e�[�u���ɕۗ��{�b�N�X�t���O��ǉ� */
ALTER TABLE `T_Enterprise` 
ADD COLUMN `HoldBoxFlg` TINYINT(4) NOT NULL DEFAULT 1 AFTER `NgAccessReferenceDate`;

/* �V�X�e������ */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'DefectCancelPlanDays', '2', '�s�������L�����Z���\�����', NOW(), 9, NOW(), 9, '1');


/* �R���V�X�e���񓚏��� */
INSERT INTO M_CodeManagement VALUES(189 ,'�R���V�X�e���񓚏���' ,NULL ,'�L�[�l' ,1 ,'�ۗ��{�b�N�X����敪' ,0,NULL,0,NULL, NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(189,11 ,'�u���b�N�F�Z��','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,12 ,'�u���b�N�F����','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,13 ,'�u���b�N�F�d�b�ԍ�','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,14 ,'�u���b�N�F���[���A�h���X','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,101 ,'�Z���F�Z���^�[�E�c�Ə�����','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,102 ,'�Z���F�c�n','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,103 ,'�Z���F�z�e��','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,104 ,'�Z���F��','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,105 ,'�Z���F�l��','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,106 ,'�Z���F�Ԓn�Ȃ�','1' ,NULL , NULL ,'�Z���E�Ԓn�����m�F���������B' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,107 ,'�Z���F�����ԍ��Ȃ�','1' ,NULL , NULL ,'�Z���E�����Ԃ����m�F���������B' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,108 ,'�Z���F��͕s�\�Z��','1' ,NULL , NULL ,'�Z���S�̂����m�F���������B' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,109 ,'�Z���F���\�E�N���[�}�[','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,111 ,'�Z���F���I�t�B�X','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,114 ,'�Z���F������','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,115 ,'�Z���F�R���r�j','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,116 ,'�Z���F�X�֔ԍ��s��v','1' ,NULL , NULL ,'�X�֔ԍ������m�F���������B' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,117 ,'�Z���F�����{��','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,118 ,'�Z���F�ŏI�����s��','1' ,NULL , NULL ,'�Z�������m�F���������B' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,201 ,'�����F�Ђ炪��','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,202 ,'�����F�J�^�J�i','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,203 ,'�����F�Z���^�[�E�c�Ə�����','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,204 ,'�����F�����E�������','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,205 ,'�����F�O�l','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,206 ,'�����F���[�}��','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,207 ,'�����F���\�E�N���[�}�[','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,208 ,'�����F����������','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,301 ,'�d�b�F050/070','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,302 ,'�d�b�F�����A�i�E���X','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,303 ,'�d�b�F���\�E�N���[�}�[','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,304 ,'�d�b�F�Z��-�s�O�ǔԕs��v','1' ,NULL , NULL ,'�d�b�ԍ������m�F���������B' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,305 ,'�d�b�F���I�t�B�X','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,401 ,'Ұٱ��ڽ�F�o��n','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,402 ,'Ұٱ��ڽ�F���t�I�N�����[�U�[','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,403 ,'Ұٱ��ڽ�F�̂ăA�h','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,404 ,'Ұٱ��ڽ�F�A���S��������','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,405 ,'Ұٱ��ڽ�F�當����','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,406 ,'Ұٱ��ڽ�F���\�E�N���[�}�[','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,407 ,'Ұٱ��ڽ�F���I�t�B�X','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,501 ,'�����F�A������','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,502 ,'�����F���ʃI�[�o�[','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,503 ,'�����F���i�������','1' ,NULL , NULL ,'���i��������m�F���������B' ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,504 ,'�����F���������z','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,551 ,'�����F�v���ӏ��i01','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,552 ,'�����F�v���ӏ��i02','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,553 ,'�����F�v���ӏ��i03','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,554 ,'�����F�v���ӏ��i04','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,555 ,'�����F�v���ӏ��i05','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,556 ,'�����F�v���ӏ��i06','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,557 ,'�����F�v���ӏ��i07','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,558 ,'�����F�v���ӏ��i08','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,559 ,'�����F�v���ӏ��i09','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,560 ,'�����F�v���ӏ��i10','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,601 ,'���ƎҁF�␳','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,602 ,'���ƎҁF�w���z�I�[�o�[','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,1001 ,'�C�x���g�F�o�����^�C���f�[','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,1002 ,'�C�x���g�F�z���C�g�f�[','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,1003 ,'�C�x���g�F��̓�','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,1004 ,'�C�x���g�F���̓�','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(189,1005 ,'�C�x���g�F�h�V�̓�','0' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);

/* �ۗ����R */
UPDATE `M_Code` SET `Note`='���Z���̔Ԓn�����m�F���������B' WHERE `CodeId`='92' and`KeyCode`='1';
UPDATE `M_Code` SET `Note`='���Z���̂������ԍ������m�F���������B' WHERE `CodeId`='92' and`KeyCode`='2';
UPDATE `M_Code` SET `Note`='���o�^�̂��d�b�ԍ������m�F���������B' WHERE `CodeId`='92' and`KeyCode`='3';
UPDATE `M_Code` SET `Note`='���d�b�ԍ��̌����������悤�ł������܂��B' WHERE `CodeId`='92' and`KeyCode`='4';
UPDATE `M_Code` SET `Note`='���d�b�ԍ��̌��������Ȃ��悤�ł������܂��B' WHERE `CodeId`='92' and`KeyCode`='5';
UPDATE `M_Code` SET `Note`='���o�^�̂��Z���ɕs�����������܂��B' WHERE `CodeId`='92' and`KeyCode`='6';
UPDATE `M_Code` SET `Note`='���Z���̔Ԓn�Ƃ��d�b�ԍ������m�F���������B' WHERE `CodeId`='92' and`KeyCode`='7';
UPDATE `M_Code` SET `Note`='���m�F�����Ă��������������Ƃ��������܂��B' WHERE `CodeId`='92' and`KeyCode`='8';


/* ���j���[�}�X�^�̒��� */
INSERT INTO T_Menu VALUES (29, 'member', 'submenus', 'header_menu_1', 5, 'order/defectlist', '�ۗ��������X�g', '�ۗ��������X�g', '�ۗ����̒������ꗗ�\��', '', '', NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (29, 301, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (29, 302, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (29, 399, NOW(), 9, NOW(), 9, 1);


/* ���[���e���v���[�g�̒��� */
UPDATE T_MailTemplate
SET Body = '{EnterpriseNameKj}�l\r\n\r\n������ς����b�ɂȂ��Ă���܂��B\r\n�㕥���h�b�g�R���J�X�^�}�[�Z���^�[�ł������܂��B\r\n\r\n�{���^�M�����������܂����ȉ��̓o�^�ɂ����āA\r\n���͏��ɕs��������\�������m���܂����B\r\n\r\n\r\n{OrderId}   {CustomerNameKj}�l   �ۗ����R�F {PendingReason}\r\n\r\n\r\n{PendingDate}�܂ŗ^�M�ۗ��Ƃ����Ă��������܂��̂�\r\n���萔�ł͂������܂����A�������������m�F��������\r\n�Ǘ��T�C�g��ł��ύX�̏���������������\r\n���Ђ܂ł��A�������������܂��悤���肢�������܂��B\r\n\r\n������  �d�v   ������ \r\n�ēx�A�^�M�����{�������ʂ��u�^�MNG�v�ƂȂ�ꍇ���������܂��B  �C���A�ēo�^��́A\r\n�K���u �y�㕥��.com�z�^�M�����̂��m�点�v ���[�������m�F���������܂��悤�A���肢��\���グ�܂��B\r\n�� �C���E�ēo�^�����{����Ȃ��ꍇ�́A{PendingDate}�ȍ~�ɑΏۂ̒����͎����I�ɃL�����Z������܂��B\r\n\r\n�����������������������@�������C���������ۂ̒��Ӂ@����������������������\r\n\r\n�ۗ������̏C���́A�Ǘ��T�C�g�́u�����o�^�v���j���[����u�ۗ��������X�g�v\r\n�I�v�V������I����ɕ\��������ʂɂđ�������{���Ă��������B\r\n\r\n�C�����e�������͂�����������A�u���̓��e�œo�^�v���N���b�N�����\r\n���e�̊m�F��ʂɑJ�ڂ��܂��B���e�����m�F�̂����A������x\r\n�u���̓��e�œo�^�v���N���b�N����ƏC���������ƂȂ�܂��B\r\n�i���m�F��ʂ���ʂ̃y�[�W�Ɉڂ��Ă��܂�����\r\n���Ă��܂����肷��ƁA�C�������f����܂���B�j\r\n\r\n������������������������������������������������������������������������\r\n\r\n�y�Ǘ���ʂt�q�k�z\r\nhttps://www.atobarai.jp/member/\r\n\r\n���s���ȓ_�Ȃǂ������܂�����A���C�y�ɂ��₢���킹���������܂��B\r\n\r\n������낵�����肢�������܂��B\r\n\r\n--------------------------------------------------------------\r\n�㕥��������s�T�[�r�X�y�㕥���h�b�g�R���z\r\n\r\n  ���⍇����F0120-667-690\r\n  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j\r\n  mail: customer@ato-barai.com\r\n\r\n  �^�c��ЁF������ЃL���b�`�{�[��\r\n�@�Z���F��160-0023 �����s�V�h�搼�V�h6-14-1 �V�h�O���[���^���[�r��14F\r\n--------------------------------------------------------------\r\n'
WHERE Class = 61 AND OemId = 0
;

