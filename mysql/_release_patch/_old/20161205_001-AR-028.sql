/* �^�M臒l��[�W���e�b�N�蓮�^�M���������ݒ�][�W���e�b�N�蓮�^�M���������ݒ�][�W���e�b�N�蓮�^�M���������ݒ�]��ǉ� */
ALTER TABLE `T_CreditJudgeThreshold`
ADD COLUMN `JintecManualJudgeUnpaidFlg` INT(11) NOT NULL DEFAULT '0' AFTER `CoreSystemHoldMIN`,
ADD COLUMN `JintecManualJudgeNonPaymentFlg` INT(11) NOT NULL DEFAULT '0' AFTER `JintecManualJudgeUnpaidFlg`,
ADD COLUMN `JintecManualJudgeSns` TEXT NULL AFTER `JintecManualJudgeNonPaymentFlg`;

/* �^�M���ʃ��O��[�蓮�^�M��┻�茋��]��ǉ� */
ALTER TABLE `T_CreditLog`
ADD COLUMN `JintecManualJudgeFlg` INT(11) DEFAULT '0' NULL AFTER `Jud_ManualStatus`;

/* �ڑ�[�W���e�b�N����.�蓮�^�M��┻�茋��]��[�^�M���ʃ��O.�蓮�^�M��┻�茋��] */
UPDATE T_CreditLog cl, T_JtcResult jr
SET cl.JintecManualJudgeFlg = jr.JintecManualJudgeFlg
WHERE cl.JtcSeq = jr.Seq;

/* [�^�M�V�X�e������CSV�o�^�E�C��]�ւR���ڒǉ� */
INSERT INTO M_TemplateField VALUES ( 47 , 168, 'ManualJudge-UnpaidFlg' ,'�^�M�����蓮��-������1' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 47 , 169, 'ManualJudge-NonPaymentFlg' ,'�^�M�����蓮��-�s����1' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 47 , 170, 'ManualJudge-JintecManualJudgeSns' ,'�^�M�����蓮��-�R���V�X�e���񓚏���' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
