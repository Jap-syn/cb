/* �R�[�h�}�X�^�[�Ɏ蓮�^�MNG���R��ǉ� */
INSERT INTO M_CodeManagement VALUES(190 ,'�蓮�^�MNG���R' ,NULL ,'�蓮�^�MNG���R' ,1 ,'���ۏؕύX����' ,0,NULL,0,NULL, NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(190, 1, '�ʏ�NG', 0, NULL, NULL, '�ʏ�NG',     0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(190, 2, '�����ؖ���', 0, NULL, NULL, '�����ؖ���', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(190, 3, '�����x����', 1, NULL, NULL, '�����x����', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(190, 4, '���z�ۗ�', 1, NULL, NULL, '���z�ۗ�',   0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(190, 5, '���ۏؕύX�\', 1, NULL, NULL, '���ۏؕύX�\',   0, NOW(), 1, NOW(), 1, 1);

/* �R�[�h�}�X�^�[�Ɏ����^�MNG���R��ǉ� */
INSERT INTO M_CodeManagement VALUES(191 ,'�����^�MNG���R' ,NULL ,'�����^�MNG���R' ,1 ,'���ۏؕύX����' ,0,NULL,0,NULL, NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(191, 1, '�e�X�g����', 0, NULL, NULL, '�ʏ�NG', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(191, 2, '�^�M�\���z����i�����x��Ver�j', 0, NULL, NULL, '�ʏ�NG', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(191, 3, '���ԋp�L�����Z������', 0, NULL, NULL, '�����ؖ���', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(191, 4, '�s��������', 0, NULL, NULL, '�����ؖ���', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(191, 5, '����������', 1, NULL, NULL, '�����x����', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(191, 6, '��^�M�X�R�A����', 0, NULL, NULL, '�ʏ�NG', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(191, 7, '�R���V�X�e���X�R�A����', 0, NULL, NULL, '�ʏ�NG', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(191, 8, '�W���e�b�N����', 0, NULL, NULL, '�ʏ�NG', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(191, 9, '�蓮�^�M', 0, NULL, NULL, '�ʏ�NG', 0, NOW(), 1, NOW(), 1, 1);



/* �T�C�g�e�[�u���ɍ��ڂ�ǉ� */
ALTER TABLE `T_Site` 
ADD COLUMN `NgChangeFlg` TINYINT(4) NOT NULL DEFAULT 1 AFTER `MultiOrderScore`,
ADD COLUMN `ShowNgReason` TINYINT(4) NOT NULL DEFAULT 1 AFTER `NgChangeFlg`,
ADD COLUMN `MuhoshoChangeDays` INT(11) NOT NULL DEFAULT 7 AFTER `ShowNgReason`;

/* ��v_�����e�[�u���ɍ��ڂ�ǉ� */
ALTER TABLE `AT_Order` 
ADD COLUMN `AutoJudgeNgReasonCode` INT(11) DEFAULT NULL AFTER `StopSendMailConfirmJournalFlg`,
ADD COLUMN `ManualJudgeNgReasonCode` INT(11) DEFAULT NULL AFTER `AutoJudgeNgReasonCode`,
ADD COLUMN `NgNoGuaranteeChangeDate` DATETIME DEFAULT NULL AFTER `ManualJudgeNgReasonCode`,
ADD COLUMN `NgButtonFlg` TINYINT(4) DEFAULT NULL AFTER `NgNoGuaranteeChangeDate`,
ADD COLUMN `NoGuaranteeChangeLimitDay` DATE DEFAULT NULL AFTER `NgButtonFlg`;

/* NG���ۏؕύX���ɃC���f�b�N�X�t�^ */
ALTER TABLE `AT_Order` 
ADD INDEX `Idx_AT_Order03` (`NgNoGuaranteeChangeDate` ASC);

/* ����ڰ�̨���ނ�UPDATE�iListNumber �ɑ΂���UPDATE�Ȃ̂ŁA������s���B�j */
UPDATE M_TemplateField SET ListNumber='37' WHERE TemplateSeq='2' and PhysicalName='ReceiptDate';
UPDATE M_TemplateField SET ListNumber='36' WHERE TemplateSeq='2' and PhysicalName='IsWaitForReceipt';
UPDATE M_TemplateField SET ListNumber='35' WHERE TemplateSeq='2' and PhysicalName='UpdateName';
UPDATE M_TemplateField SET ListNumber='34' WHERE TemplateSeq='2' and PhysicalName='UpdateDate';
UPDATE M_TemplateField SET ListNumber='33' WHERE TemplateSeq='2' and PhysicalName='RegistName';
UPDATE M_TemplateField SET ListNumber='32' WHERE TemplateSeq='2' and PhysicalName='ArrivalConfirmAlert';
UPDATE M_TemplateField SET ListNumber='31' WHERE TemplateSeq='2' and PhysicalName='CancelReasonCode';
UPDATE M_TemplateField SET ListNumber='30' WHERE TemplateSeq='2' and PhysicalName='OutOfAmends';
UPDATE M_TemplateField SET ListNumber='29' WHERE TemplateSeq='2' and PhysicalName='RegistDate';
UPDATE M_TemplateField SET ListNumber='28' WHERE TemplateSeq='2' and PhysicalName='Deli_JournalNumberAlert';
UPDATE M_TemplateField SET ListNumber='27' WHERE TemplateSeq='2' and PhysicalName='ClaimSendingClass';
UPDATE M_TemplateField SET ListNumber='26' WHERE TemplateSeq='2' and PhysicalName='DestPhone';
UPDATE M_TemplateField SET ListNumber='25' WHERE TemplateSeq='2' and PhysicalName='ServiceExpectedDate';
UPDATE M_TemplateField SET ListNumber='24' WHERE TemplateSeq='2' and PhysicalName='EntCustId';
UPDATE M_TemplateField SET ListNumber='23' WHERE TemplateSeq='2' and PhysicalName='Phone';
UPDATE M_TemplateField SET ListNumber='22' WHERE TemplateSeq='2' and PhysicalName='RealCancelStatus';
UPDATE M_TemplateField SET ListNumber='21' WHERE TemplateSeq='2' and PhysicalName='ApprovalDate';
UPDATE M_TemplateField SET ListNumber='20' WHERE TemplateSeq='2' and PhysicalName='ExecScheduleDate';
UPDATE M_TemplateField SET ListNumber='19' WHERE TemplateSeq='2' and PhysicalName='Deli_JournalNumber';
UPDATE M_TemplateField SET ListNumber='18' WHERE TemplateSeq='2' and PhysicalName='Deli_DeliveryMethod';
UPDATE M_TemplateField SET ListNumber='17' WHERE TemplateSeq='2' and PhysicalName='Deli_JournalIncDate';
UPDATE M_TemplateField SET ListNumber='16' WHERE TemplateSeq='2' and PhysicalName='Ent_Note';
UPDATE M_TemplateField SET ListNumber='15' WHERE TemplateSeq='2' and PhysicalName='UseAmount';

/* �f�t�H���g������ڰ�̨���ނ�INSERT */
INSERT INTO M_TemplateField VALUES ( 2 , 14, 'NgNoGuaranteeChange' ,'NG���ۏ�' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);

/* �e�����X������ڰ�̨���ނ�INSERT */
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'NgNoGuaranteeChange' ,'NG���ۏ�' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1 from M_TemplateField where TemplateSeq IN (SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKA01005_1' AND Seq != 0) group by TemplateSeq;


/* ���[���e���v���[�g��[CB�������ۏؕύX�ʒm���[��][�����X�������ۏؕύX�ʒm���[��]�ǉ� */
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (96,'CB�������ۏؕύX�ʒm���[��','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,'customer@ato-barai.com','���ۏؕύX�ʒm���[���i{LoginId} {OrderId}�j','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','�ȉ��̒��������ۏ؂ɕύX����܂����B\r\n�����X�F{LoginId} {EnterpriseName}\r\n����ID�F{OrderId}\r\nNG���R�F{NgReason}',0,NOW(),1,NOW(),1,1);
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (97,'�����X�������ۏؕύX�ʒm���[��','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'�y�㕥���J�X�^�}�[�Z���^�[�z���ۏ؏�����t','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','{EnterpriseNameKj}\r\n���S���җl\r\n\r\n������ς����b�ɂȂ��Ă���܂��B\r\n�㕥���h�b�g�R���J�X�^�}�[�Z���^�[�ł������܂��B\r\n\r\n�㕥�����ϊǗ��V�X�e���́u���ۏ؂ɕύX�v�{�^���ɂĂ��\�����݂����������܂���\r\n{OrderId} {CustomerNameKj}�l�̒����𖳕ۏ؂ɂĎ�t�������܂����B\r\n\r\n���m�F���������A�s����s���_�Ȃǂ������܂�����\r\n���C�y�ɂ��⍇�����������܂��B\r\n\r\n����Ƃ������A��낵�����肢�������܂��B\r\n\r\n\r\n--------------------------------------------------------------\r\n������ЃL���b�`�{�[��\r\n��160-0023 �����s�V�h�搼�V�h6-14-1 �V�h�O���[���^���[�r��14F\r\nTEL�F03-5909-3490�@FAX�F03-5909-3939\r\nMAIL�Fcustomer@ato-barai.com\r\n�c�Ǝ��ԁF9:00�`18:00�@�i�N���E�N�n�������A�N�����x�j\r\n--------------------------------------------------------------',0,NOW(),1,NOW(),1,1);


/* �e���v���[�g�}�X�^�o�^ */
INSERT INTO M_TemplateField VALUES ( 18 , 154, 'NgNoGuaranteeChangeDate' ,'NG���ۏؕύX��' ,'DATETIME' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 18 , 155, 'NgReason' ,'NG���R' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);


/* ���[���e���v���[�g�̒��� */
UPDATE T_MailTemplate
SET Body = '{EnterpriseNameKj}�@�l\r\n\r\n�����y�㕥���h�b�g�R���z�������p���������A�܂��Ƃɂ��肪�Ƃ��������܂��B\r\n\r\n�^�M�����F{CreditCount} ��\r\n\r\n�̗^�M���ʂ��o�܂����̂ł��񍐂������܂��B\r\n\r\n�y�Ǘ���ʂt�q�k�z\r\nhttps://www.atobarai.jp/member/\r\n\r\n���^�M��NG�̂������ł����Ă��ANG���R�ɂ���ẮA���ۏ؂ɂāuOK�v�ɕύX�ł���ꍇ���������܂��B\r\n���ۏ؂Ō㕥���T�[�r�X����]�̕��͈ȉ��ɋL�ڂ́yNG���R�ɂ�鏈�����@�ɂ��āz���Q�l�ɂ��Ă��������B\r\n�i���ۏ؂ł��uOK�v�ɕύX�ł��Ȃ��ꍇ���������܂��̂ŁA���Ђ����\r\n�ԐM���[�������m�F���������Ă���A���i�����Ȃǂ��s���Ă��������B�j\r\n\r\n{Orders}\r\n\r\n�yOK�Č��̏����z\r\n�^�M���ʉ߂���������Ɋւ��܂��ẮA\r\n\r\n1.���i�̔���\r\n2.�z���`�[�ԍ��o�^\r\n\r\n�ɂ��i�݉������B\r\n\r\n�yNG���R�ɂ�鏈�����@�ɂ��āz\r\n�� NG���R���u�����x�����v�u���z�ۗ��v�u���ۏؕύX�\�v�̏ꍇ\r\n���ۏ؂ł̌㕥���T�[�r�X�� �؂�ւ��Ē������Ƃ��\�ł��B\r\n���ۏ؂ɕύX����ꍇ�́A���̃��[�����{OutOfAmendsDays}���ȓ��Ɍ㕥�����ϊǗ��V�X�e����\r\n���O�C����ɑ�������{���Ă��������B\r\n\r\n�� ��L�ȊO��NG���R�̏ꍇ\r\n���̑���NG���R�̂�����Ɋւ��܂��ẮA�����߂ɂ��w���җl�ɑ��̌��ϕ��@�̂��I����\r\n���������Ȃǂ̂��Ή������肢�������܂��B\r\n\r\n--------------------------------------------------------------\r\n\r\n�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`\r\n\r\n  ���⍇����F0120-667-690\r\n  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j\r\n  mail: customer@ato-barai.com\r\n\r\n  �^�c��ЁF������ЃL���b�`�{�[��\r\n�@�Z���F��160-0023 �����s�V�h�搼�V�h6-14-1�@�V�h�O���[���^���[14�K \r\n\r\n--------------------------------------------------------------'
WHERE Class = 3 AND IFNULL(OemId, 0) = 0
;

UPDATE T_MailTemplate
SET Body = '{EnterpriseNameKj}�@�l\r\n\r\n�����y�㕥���h�b�g�R���z�������p���������A�܂��Ƃɂ��肪�Ƃ��������܂��B\r\n\r\n�^�M�����F{CreditCount} ��\r\n\r\n�̗^�M���ʂ��o�܂����̂ł��񍐂������܂��B\r\n\r\n�y�Ǘ���ʂt�q�k�z\r\nhttps://www.ato-barai.jp/smbcfs/member/\r\n\r\n���^�M��NG�̂������ł����Ă��ANG���R�ɂ���ẮA���ۏ؂ɂāuOK�v�ɕύX�ł���ꍇ���������܂��B\r\n���ۏ؂Ō㕥���T�[�r�X����]�̕��͈ȉ��ɋL�ڂ́yNG���R�ɂ�鏈�����@�ɂ��āz���Q�l�ɂ��Ă��������B\r\n�i���ۏ؂ł��uOK�v�ɕύX�ł��Ȃ��ꍇ���������܂��̂ŁA���Ђ����\r\n�ԐM���[�������m�F���������Ă���A���i�����Ȃǂ��s���Ă��������B�j\r\n\r\n{Orders}\r\n\r\n�yOK�Č��̏����z\r\n�^�M���ʉ߂���������Ɋւ��܂��ẮA\r\n\r\n1.���i�̔���\r\n2.�z���`�[�ԍ��o�^\r\n\r\n�ɂ��i�݉������B\r\n\r\n�yNG���R�ɂ�鏈�����@�ɂ��āz\r\n�� NG���R���u�����x�����v�u���z�ۗ��v�u���ۏؕύX�\�v�̏ꍇ\r\n���ۏ؂ł̌㕥���T�[�r�X�� �؂�ւ��Ē������Ƃ��\�ł��B\r\n���ۏ؂ɕύX����ꍇ�́A���̃��[�����{OutOfAmendsDays}���ȓ��Ɍ㕥�����ϊǗ��V�X�e����\r\n���O�C����ɑ�������{���Ă��������B\r\n\r\n�� ��L�ȊO��NG���R�̏ꍇ\r\n���̑���NG���R�̂�����Ɋւ��܂��ẮA�����߂ɂ��w���җl�ɑ��̌��ϕ��@�̂��I����\r\n���������Ȃǂ̂��Ή������肢�������܂��B\r\n\r\n--------------------------------------------------------------\r\n\r\n�y�㕥���h�b�g�R���z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`\r\n\r\n  ���⍇����F0120-667-690\r\n  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j\r\n  mail: customer@ato-barai.com\r\n\r\n  �^�c��ЁF������ЃL���b�`�{�[��\r\n�@�Z���F��160-0023 �����s�V�h�搼�V�h6-14-1�@�V�h�O���[���^���[14�K \r\n\r\n--------------------------------------------------------------'
WHERE Class = 3 AND OemId = 2
;

UPDATE T_MailTemplate
SET Body = '{EnterpriseNameKj}�@�l\r\n\r\n�����y�㕥�����σT�[�r�X�z�������p���������A�܂��Ƃɂ��肪�Ƃ��������܂��B\r\n\r\n�^�M�����F{CreditCount} ��\r\n\r\n�̗^�M���ʂ��o�܂����̂ł��񍐂������܂��B\r\n\r\n�y�Ǘ���ʂt�q�k�z\r\nhttps://atobarai.seino.co.jp/seino-financial/member/\r\n\r\n���^�M��NG�̂������ł����Ă��ANG���R�ɂ���ẮA���ۏ؂ɂāuOK�v�ɕύX�ł���ꍇ���������܂��B\r\n���ۏ؂Ō㕥���T�[�r�X����]�̕��͈ȉ��ɋL�ڂ́yNG���R�ɂ�鏈�����@�ɂ��āz���Q�l�ɂ��Ă��������B\r\n�i���ۏ؂ł��uOK�v�ɕύX�ł��Ȃ��ꍇ���������܂��̂ŁA���Ђ����\r\n�ԐM���[�������m�F���������Ă���A���i�����Ȃǂ��s���Ă��������B�j\r\n\r\n{Orders}\r\n\r\n�yOK�Č��̏����z\r\n�^�M���ʉ߂���������Ɋւ��܂��ẮA\r\n\r\n1.���i�̔���\r\n2.�z���`�[�ԍ��o�^\r\n\r\n�ɂ��i�݉������B\r\n\r\n�yNG���R�ɂ�鏈�����@�ɂ��āz\r\n�� NG���R���u�����x�����v�u���z�ۗ��v�u���ۏؕύX�\�v�̏ꍇ\r\n���ۏ؂ł̌㕥���T�[�r�X�� �؂�ւ��Ē������Ƃ��\�ł��B\r\n���ۏ؂ɕύX����ꍇ�́A���̃��[�����{OutOfAmendsDays}���ȓ��Ɍ㕥�����ϊǗ��V�X�e����\r\n���O�C����ɑ�������{���Ă��������B\r\n\r\n�� ��L�ȊO��NG���R�̏ꍇ\r\n���̑���NG���R�̂�����Ɋւ��܂��ẮA�����߂ɂ��w���җl�ɑ��̌��ϕ��@�̂��I����\r\n���������Ȃǂ̂��Ή������肢�������܂��B\r\n\r\n--------------------------------------------------------------\r\n\r\n�y�㕥�����σT�[�r�X�z�`�ł�����҂Ɉ�����錈�σT�[�r�X�`\r\n\r\n  ���⍇����F03-5909-4500\r\n  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j\r\n  mail: sfc-atobarai@seino.co.jp\r\n\r\n  �^�c��ЁF�Z�C�m�[�t�B�i���V�����������\r\n�@�Z���F��503-8501 �򕌌���_�s�c�����P�Ԓn\r\n\r\n--------------------------------------------------------------'
WHERE Class = 3 AND OemId = 3
;


/* �T�C�g�e�[�u���̍X�V */
UPDATE T_Site
SET NgChangeFlg = 0
WHERE OutOfAmendsFlg = 1;
