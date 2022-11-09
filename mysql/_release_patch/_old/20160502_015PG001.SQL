/* OEM���Z���_�E�����[�h�Ή� */

/* OEM����[T_OemClaimed]�ւ̃J�����ǉ� */
ALTER TABLE T_OemClaimed ADD COLUMN `N_MonthlyFeeWithoutTax` BIGINT NOT NULL DEFAULT 0 AFTER `OM_EntMonthlyFeePlan`;
ALTER TABLE T_OemClaimed ADD COLUMN `N_MonthlyFeeTax` BIGINT NOT NULL DEFAULT 0 AFTER `N_MonthlyFeeWithoutTax`;

/* �R�[�h�}�X�^�֐����萔���p�^�[�������o�^ */
INSERT INTO M_CodeManagement VALUES(184 ,'�����萔���p�^�[��' ,NULL ,'�����萔���ō��z' ,1 ,'�����萔���Ŕ��z' ,1,'�����萔���Ŋz',0,NULL, NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(184,172 ,'�����萔���ō�172�~','160' ,'12' , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(184,91 ,'�����萔���ō�91�~','85' ,'6' , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(184,216 ,'�����萔���ō�216�~','200' ,'16' , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);

/* �e���v���[�g�o�^ */
INSERT INTO M_TemplateHeader VALUES( 84 , 'COEM030_1', 0, 0, 0, '�㕥�����p�_��', 1, ',', '\"' ,'*' ,0,'OEM030', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateHeader VALUES( 85 , 'COEM030_3', 0, 0, 0, '�㕥�����p���ׁi�󎆑�j', 1, ',', '\"' ,'*' ,0,'OEM030', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateHeader VALUES( 86 , 'COEM030_4', 0, 0, 0, '�L�����Z���ԋ�����', 1, ',', '\"' ,'*' ,0,'OEM030', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateHeader VALUES( 87 , 'COEM030_5', 0, 0, 0, '�����z����', 1, ',', '\"' ,'*' ,0,'OEM030', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateHeader VALUES( 88 , 'COEM030_6', 0, 0, 0, '���Z�܂ł̃L�����Z���ԋ�����', 1, ',', '\"' ,'*' ,0,'OEM030', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateField VALUES ( 84 , 1, 'ChargeMonth' ,'���p�N��' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 84 , 2, 'EnterpriseId' ,'���Ǝ҂h�c' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 84 , 3, 'PlanId' ,'���p�v����' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 84 , 4, 'ServiceInDate' ,'�T�[�r�X�J�n��' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 84 , 5, 'ChargeAmount' ,'OEM���z�Œ��' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 85 , 1, 'EnterpriseId' ,'���Ǝ�ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 85 , 2, 'SiteId' ,'�T�C�gID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 85 , 3, 'OrderId' ,'����ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 85 , 4, 'ReceiptProcessDate' ,'�����m�F��' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 85 , 5, 'StampFee' ,'�󎆑�' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 85 , 6, 'UseAmount' ,'�ڋq�������z' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 1, 'RowNumber' ,'No.' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 2, 'OrderId' ,'����ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 3, 'Ent_OrderId' ,'�C�Ӓ����ԍ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 4, 'NameKj' ,'�w����' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 5, 'OrderDate' ,'������' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 6, 'FixedDate' ,'���֒��ߓ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 7, 'ApprovalDate' ,'�L�����Z����' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 8, 'ClaimAmount' ,'�ڋq�����z' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 9, 'RepayTotal' ,'�L�����Z���ԋ��z' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 87 , 1, 'EnterpriseNameKj' ,'���ƎҖ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 87 , 2, 'OrderId' ,'���ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 87 , 3, 'AdjustmentAmount' ,'�����z' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 87 , 4, 'Kamoku' ,'�������R' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 1, 'OrderDate' ,'������' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 2, 'OrderId' ,'����ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 3, 'Ent_OrderId' ,'�C�Ӓ����ԍ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 4, 'NameKj' ,'�w����' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 5, 'UseAmount' ,'�ڋq�����z' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 6, 'SettlementFee' ,'���ώ萔��' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 7, 'ClaimFee' ,'���������s�萔��' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 8, 'DiffSum' ,'�������v' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 9, 'EnterpriseNameKj' ,'���ƎҖ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 10, 'SiteNameKj' ,'��t�T�C�g' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 11, 'SiteId' ,'�T�C�gID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 12, 'ReceiptClass' ,'�������@' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 13, 'EnterpriseId' ,'���Ǝ�ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 14, 'PlanId' ,'�v����' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 15, 'AppSettlementFeeRate' ,'����' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);