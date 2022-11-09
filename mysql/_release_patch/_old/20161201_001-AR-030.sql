-- AT_PayOff_DailyAccount.EnterpriseId�փC���f�b�N�X��t�^
ALTER TABLE `AT_PayOff_DailyAccount` ADD INDEX `Idx_AT_PayOff_DailyAccount02` (`EnterpriseId` ASC);
-- OEM�����փJ����[�x��������]�ǉ�
ALTER TABLE `T_OemClaimed` ADD COLUMN `ExecDate` DATE NULL DEFAULT NULL AFTER `N_MonthlyFeeTax`;
-- OEM����[�x��������]�փC���f�b�N�X�t�^
ALTER TABLE `T_OemClaimed` ADD INDEX `Idx_T_OemClaimed04` (`ExecDate` ASC);


-- �e���v���[�g�}�X�^�[�o�^
INSERT INTO M_TemplateHeader VALUES( 89 , 'CKI24174_13_O', 0, 0, 0, '���Z���v(OEM��)', 1, ',', '\"' ,'SJIS-win' ,0,'CKI24174', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateField VALUES ( 89 , 1, 'OemId' ,'OEM�R�[�h' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 2, 'OemNameKj' ,'OEM��' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 3, 'EnterpriseId' ,'�����X�R�[�h' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 4, 'EnterpriseNameKj' ,'�����X��' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 5, 'AdvancesDate' ,'OEM�ւ̗��֓�' ,'DATE' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 6, 'AdvancesAmount' ,'OEM���֎��s���z' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 7, 'EnterpriseAccountsDue' ,'OEM������' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 8, 'AccountsPayablePending' ,'OEM�������ۗ�' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 9, 'ClaimAndObligationsDecision' ,'��������' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 10, 'UseAmount' ,'���p�z�EOEM������' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 11, 'CancelAmount' ,'�L�����Z�����z' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 12, 'AccountsPayableTotal' ,'OEM�������v' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 13, 'SettlementFee' ,'OEM���ώ萔��' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 14, 'ClaimFee' ,'OEM�����萔��' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 15, 'MonthlyFee' ,'OEM���z�Œ��' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 16, 'AccountsReceivableTotal' ,'OEM���|���v' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 17, 'StampFee' ,'�󎆑�' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 18, 'TransferCommission' ,'OEM�U���萔��' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 19, 'AdjustmentAmount' ,'OEM�������z' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 20, 'EnterpriseRefund' ,'�����X�ւ̕ԋ����Z' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 21, 'AccountsDueOffsetAmount' ,'OEM�O���Z�����������E' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 22, 'AccountsPayablePendingAmount' ,'OEM�O���Z���������ۗ��z' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);


-- �@.���т���x������ݒ�
UPDATE T_PayingControl pc, (SELECT DecisionDate, ExecScheduleDate, IFNULL(OemId, 0) AS OemId, MAX(ExecDate) AS ExecDate, MAX(ExecCpId) AS ExecCpId FROM T_PayingControl WHERE ExecDate IS NOT NULL GROUP BY DecisionDate, ExecScheduleDate, IFNULL(OemId, 0)) tmp
SET pc.ExecDate = tmp.ExecDate
   ,pc.ExecCpId = tmp.ExecCpId
WHERE pc.DecisionDate = tmp.DecisionDate
AND pc.ExecScheduleDate = tmp.ExecScheduleDate
AND IFNULL(pc.OemId, 0) = tmp.OemId
AND pc.ExecDate IS NULL
AND pc.DecisionDate >= '2015-12-01'
AND pc.PayingControlStatus = 1;
-- �A.�@�Őݒ肳��Ȃ����̂́A�Z�o���ꂽ�\�������ݒ�
UPDATE T_PayingControl pc
SET pc.ExecDate = pc.ExecScheduleDate
   ,pc.ExecCpId = 1
WHERE pc.ExecDate IS NULL
AND pc.ExecFlg IN (-1, 1, 11)
AND pc.PayingControlStatus = 1
AND pc.ExecScheduleDate < DATE(NOW());
-- �B.��L��OEM��
UPDATE T_OemClaimed SET ExecDate = SettlePlanDate WHERE SettlePlanDate <= NOW();
