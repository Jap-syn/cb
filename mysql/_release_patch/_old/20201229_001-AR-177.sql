/* �R�����g�ݒ�Ƀy�C�W�[�֘A���ڒǉ�
/* �h���b�v�_�E���o�[�ɒǉ� */
INSERT INTO M_CodeManagement VALUES(205,'�y�C�W�[����',NULL,'�y�C�W�[����',0,NULL,0,NULL,0,NULL,NOW(),1,NOW(),1,1);

/* �t�B�[���h�ɒǉ�*/
INSERT INTO M_Code VALUES(205,1,'�Ώ�OEM',NULL,NULL,NULL,'6',0,NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(205,2,'URL',NULL,NULL,NULL,'',0,NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(205,3,'�^�C���A�E�g',NULL,NULL,NULL,'10',0,NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(205,4,'�����X�R�[�h',NULL,NULL,NULL,'000000',0,NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(205,5,'�����X�T�u�R�[�h',NULL,NULL,NULL,'00000',0,NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(205,6,'�n�b�V���p�p�X���[�h',NULL,NULL,NULL,'',0,NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(205,7,'���[�@�֔ԍ�',NULL,NULL,NULL,'58054',0,NOW(),1,NOW(),1,1);

/* �y�C�W�[�p�e���v���[�g�ǉ� */
INSERT INTO M_TemplateHeader VALUES('102', 'CKI04049_1', '0', '0', '0', '�y�C�W�[�����f�[�^CSV', '1', ',', '\"', '*', '0', 'KI04049', NULL, NOW(), '9', NOW(), '9', '1');

INSERT INTO M_TemplateField VALUES('102', '1', 'PostalCode', '�ڋq�X�֔ԍ�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '2', 'UnitingAddress', '�ڋq�Z��', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '3', 'NameKj', '�ڋq����', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '4', 'OrderId', '�����h�c', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '5', 'ReceiptOrderDate', '������', 'DATE', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '6', 'SiteNameKj', '�w���X��', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '7', 'Url', '�w���XURL', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '8', 'ContactPhoneNumber', '�w���X�d�b�ԍ�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '9', 'ClaimAmount', '�������z', 'BIGINT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '10', 'CarriageFee', '����', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '11', 'ChargeFee', '���ώ萔��', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '12', 'ReIssueCount', '������', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '13', 'LimitDate', '�x��������', 'DATE', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '14', 'Cv_BarcodeData2', '�o�[�R�[�h�f�[�^', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '15', 'ItemNameKj_1', '���i���P', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '16', 'ItemNum_1', '���ʂP', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '17', 'UnitPrice_1', '�P���P', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '18', 'ItemNameKj_2', '���i���Q', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '19', 'ItemNum_2', '���ʂQ', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '20', 'UnitPrice_2', '�P���Q', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '21', 'ItemNameKj_3', '���i���R', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '22', 'ItemNum_3', '���ʂR', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '23', 'UnitPrice_3', '�P���R', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '24', 'ItemNameKj_4', '���i���S', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '25', 'ItemNum_4', '���ʂS', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '26', 'UnitPrice_4', '�P���S', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '27', 'ItemNameKj_5', '���i���T', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '28', 'ItemNum_5', '���ʂT', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '29', 'UnitPrice_5', '�P���T', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '30', 'ItemNameKj_6', '���i���U', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '31', 'ItemNum_6', '���ʂU', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '32', 'UnitPrice_6', '�P���U', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '33', 'ItemNameKj_7', '���i���V', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '34', 'ItemNum_7', '���ʂV', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '35', 'UnitPrice_7', '�P���V', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '36', 'ItemNameKj_8', '���i���W', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '37', 'ItemNum_8', '���ʂW', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '38', 'UnitPrice_8', '�P���W', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '39', 'ItemNameKj_9', '���i���X', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '40', 'ItemNum_9', '���ʂX', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '41', 'UnitPrice_9', '�P���X', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '42', 'ItemNameKj_10', '���i���P�O', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '43', 'ItemNum_10', '���ʂP�O', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '44', 'UnitPrice_10', '�P���P�O', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '45', 'ItemNameKj_11', '���i���P�P', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '46', 'ItemNum_11', '���ʂP�P', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '47', 'UnitPrice_11', '�P���P�P', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '48', 'ItemNameKj_12', '���i���P�Q', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '49', 'ItemNum_12', '���ʂP�Q', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '50', 'UnitPrice_12', '�P���P�Q', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '51', 'ItemNameKj_13', '���i���P�R', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '52', 'ItemNum_13', '���ʂP�R', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '53', 'UnitPrice_13', '�P���P�R', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '54', 'ItemNameKj_14', '���i���P�S', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '55', 'ItemNum_14', '���ʂP�S', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '56', 'UnitPrice_14', '�P���P�S', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '57', 'ItemNameKj_15', '���i���P�T', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '58', 'ItemNum_15', '���ʂP�T', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '59', 'UnitPrice_15', '�P���P�T', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '60', 'ItemNameKj_16', '���i���P�U', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '61', 'ItemNum_16', '���ʂP�U', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '62', 'UnitPrice_16', '�P���P�U', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '63', 'ItemNameKj_17', '���i���P�V', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '64', 'ItemNum_17', '���ʂP�V', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '65', 'UnitPrice_17', '�P���P�V', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '66', 'ItemNameKj_18', '���i���P�W', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '67', 'ItemNum_18', '���ʂP�W', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '68', 'UnitPrice_18', '�P���P�W', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '69', 'ItemNameKj_19', '���i���P�X', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '70', 'ItemNum_19', '���ʂP�X', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '71', 'UnitPrice_19', '�P���P�X', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '72', 'ClaimFee', '�Đ������s�萔��', 'BIGINT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '73', 'DamageInterestAmount', '�x�����Q��', 'BIGINT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '74', 'TotalItemPrice', '���v', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '75', 'Ent_OrderId', '�C�Ӓ����ԍ�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '76', 'TaxAmount', '����Ŋz', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '77', 'Cv_ReceiptAgentName', 'CVS���[��s��Ж�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '78', 'Cv_SubscriberName', 'CVS���[��s�����Җ�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '79', 'Cv_BarcodeData', '�o�[�R�[�h�f�[�^(CD�t)', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '80', 'Cv_BarcodeString1', '�o�[�R�[�h����1', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '81', 'Cv_BarcodeString2', '�o�[�R�[�h����2', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '82', 'Bk_BankCode', '��s���� - ��s�R�[�h', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '83', 'Bk_BranchCode', '��s���� - �x�X�R�[�h', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '84', 'Bk_BankName', '��s���� - ��s��', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '85', 'Bk_BranchName', '��s���� - �x�X��', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '86', 'Bk_DepositClass', '��s���� - �������', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '87', 'Bk_AccountNumber', '��s���� - �����ԍ�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '88', 'Bk_AccountHolder', '��s���� - �������`', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '89', 'Bk_AccountHolderKn', '��s���� - �������`�J�i', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '90', 'Yu_SubscriberName', '�䂤������� - �����Җ�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '91', 'Yu_AccountNumber', '�䂤������� - �����ԍ�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '92', 'Yu_ChargeClass', '�䂤������� - �������S�敪', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '93', 'Yu_MtOcrCode1', '�䂤������� - MT�pOCR�R�[�h1', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '94', 'Yu_MtOcrCode2', '�䂤������� - MT�pOCR�R�[�h2', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '95', 'MypageToken', '�}�C�y�[�W���O�C���p�X���[�h', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '96', 'ItemsCount', '���i���v��', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '97', 'TaxClass', '����ŋ敪', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '98', 'CorporateName', '�@�l��', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '99', 'DivisionName', '������', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '100', 'CpNameKj', '�S���Җ�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '101', 'CustomerNumber', '���q�l�ԍ�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '102', 'ConfirmNumber', '�m�F�ԍ�', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');
INSERT INTO M_TemplateField VALUES('102', '103', 'Bk_Number', '���[�@�֔ԍ�', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), '9', NOW(), '9', '1');

/*
-- CB�������p���ɂ݂��كt�@�N�^�[�̍��ڒǉ�
INSERT INTO M_Code VALUES(181,106 ,'MHF�����n�K�L�i����j','MHF' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(181,206 ,'MHF�����i����j','MHF_Fuusho' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(181,306 ,'MHF�����n�K�L�i�ĂR�`�T�j','MHF_S' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(181,406 ,'MHF�`�S�p���i�ĂU�`�V�j','MHF_Toku' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);

-- �����p�^�[���p���ɂ݂��كt�@�N�^�[�̍��ڒǉ�
INSERT INTO M_Code VALUES(182,61 ,'MHF����','106' ,'Shokai' , '1' ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(182,62 ,'MHF�ĂP','106' ,'Sai1' , '1' ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(182,63 ,'MHF�ĂQ','106' ,'Sai2' , '1' ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(182,64 ,'MHF�ĂR','306' ,'Sai3' , '1' ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(182,66 ,'MHF�ĂS','306' ,'Sai4' , '1' ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(182,67 ,'MHF�ĂT','306' ,'Sai5' , '2' ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(182,68 ,'MHF�ĂU','406' ,'Sai6' , '2' ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(182,69 ,'MHF�ĂV','406' ,'Sai7' , '2' ,NULL ,0, NOW(), 1, NOW(), 1, 1);
*/

/* �y�C�W�[����������f�[�^CSV(����������) */
INSERT INTO M_TemplateHeader VALUES('103', 'CKA04016_2', '0', '0', '0', '�y�C�W�[����������f�[�^CSV(����������)', '0', ',', '\"', '*', '0', 'KA04016', '{"items": "19"}', NOW(), '9', NOW(), '9', '1');

INSERT INTO M_TemplateField VALUES('103', '1', 'PostalCode', '�ڋq�X�֔ԍ�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38','9', '1');
INSERT INTO M_TemplateField VALUES('103', '2', 'UnitingAddress', '�ڋq�Z��', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38','9', '1');
INSERT INTO M_TemplateField VALUES('103', '3', 'NameKj', '�ڋq����', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '4', 'OrderId', '����ID', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '5', 'Ent_OrderId', '�C�Ӓ����ԍ�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '6', 'ReceiptOrderDate', '������', 'DATE', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '7', 'SiteNameKj', '�w���X��', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '8', 'Url', '�w���XURL', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1' );
INSERT INTO M_TemplateField VALUES('103', '9', 'Phone', '�w���X�d�b�ԍ�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '10', 'ClaimAmount', '�������z', 'BIGINT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '11', 'TotalItemPrice', '���v', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '12', 'CarriageFee', '����', 'BIGINT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '13', 'ChargeFee', '���ώ萔��', 'BIGINT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '14', 'ReIssueCount', '������', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9','1');
INSERT INTO M_TemplateField VALUES('103', '15', 'LimitDate', '�x��������', 'DATE', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9','1');
INSERT INTO M_TemplateField VALUES('103', '16', 'Cv_BarcodeData', '�o�[�R�[�h�f�[�^', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '17', 'Cv_BarcodeString1', '�o�[�R�[�h������1', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '18', 'Cv_BarcodeString2', '�o�[�R�[�h������2', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '19', 'Yu_DtCode', '�䂤����DT�p�f�[�^', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '20', 'OrderItems', '���i����', '�|', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '21', 'TotalItemPrice2', '���v', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '22', 'Ent_OrderId2', '�C�Ӓ����ԍ�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '23', 'TaxAmount', '��������Ŋz', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '24', 'Cv_ReceiptAgentName', '�R���r�j���[��s��Ж�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '25', 'Cv_SubscriberName', '�R���r�j���[��s�����Җ�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '26', 'Bk_BankCode', '��s�R�[�h', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38','9', '1');
INSERT INTO M_TemplateField VALUES('103', '27', 'Bk_BranchCode', '�x�X�R�[�h', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '28', 'Bk_BankName', '��s��', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '29', 'Bk_BranchName', '�x�X��', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '30', 'Bk_DepositClass', '��s�������', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '31', 'Bk_AccountNumber', '��s�����ԍ�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '32', 'Bk_AccountHolder', '��s�������`', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '33', 'Bk_AccountHolderKn', '��s�������`�J�i', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '34', 'Yu_SubscriberName', '�䂤��������Җ�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '35', 'Yu_AccountNumber', '�䂤��������ԍ�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '36', 'Yu_ChargeClass', '�䂤���啥�����S�敪', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '37', 'Yu_MtOcrCode1', '�䂤����OCR�R�[�h1', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '38', 'Yu_MtOcrCode2', '�䂤����OCR�R�[�h2', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '39', 'PrintEntComment01', '�X�܂���̂��m�点�O�P', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '40', 'PrintEntComment02', '�X�܂���̂��m�点�O�Q', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '41', 'PrintEntComment03', '�X�܂���̂��m�点�O�R', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '42', 'PrintEntComment04', '�X�܂���̂��m�点�O�S', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '43', 'PrintEntComment05', '�X�܂���̂��m�点�O�T', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '44', 'PrintEntComment06', '�X�܂���̂��m�点�O�U', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '45', 'PrintEntComment07', '�X�܂���̂��m�点�O�V', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '46', 'PrintEntComment08', '�X�܂���̂��m�点�O�W', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '47', 'PrintEntComment09', '�X�܂���̂��m�点�O�X', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '48', 'PrintEntComment10', '�X�܂���̂��m�点�P�O', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '49', 'MypageToken', '�}�C�y�[�W���O�C���p�X���[�h', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-2117:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '50', 'OtherItemsCount', '���̑����i�_��', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '51', 'OtherItemsSummary', '���̑����Z���z', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '52', 'MypageUrl', '�}�C�y�[�WURL', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '53', 'CorporateName', '�@�l��', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '54', 'DivisionName', '������', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '1');
INSERT INTO M_TemplateField VALUES('103', '55', 'CpNameKj', '�S���Җ�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9','1');
INSERT INTO M_TemplateField VALUES('103', '56', 'TaxRate', '����ŗ�', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '0' );
INSERT INTO M_TemplateField VALUES('103', '57', 'SubUseAmount_1', '�W���Ώۍ��v���z', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '0');
INSERT INTO M_TemplateField VALUES('103', '58', 'SubTaxAmount_1', '�W���Ώۏ���Ŋz', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '0');
INSERT INTO M_TemplateField VALUES('103', '59', 'SubUseAmount_2', '�P�O���Ώۍ��v���z', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '0');
INSERT INTO M_TemplateField VALUES('103', '60', 'SubTaxAmount_2', '�P�O���Ώۏ���Ŋz', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '0');
INSERT INTO M_TemplateField VALUES('103', '61', 'CorporationNumber', '���Ǝғo�^�ԍ�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '9', '2020-12-21 17:36:38', '9', '0');
INSERT INTO M_TemplateField VALUES('103', '62', 'CreditLimitDate', '�N���W�b�g�葱��������', 'DATE', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '83', '2020-12-2117:36:38', '83', '0');
INSERT INTO M_TemplateField VALUES('103', '63', 'CustomerNumber', '���q�l�ԍ�', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '83', '2020-12-21 17:36:38', '83', '1');
INSERT INTO M_TemplateField VALUES('103', '64', 'ConfirmNumber', '�m�F�ԍ�', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '83', '2020-12-21 17:36:38', '83', '1');
INSERT INTO M_TemplateField VALUES('103', '65', 'Bk_Number', '���[�@�֔ԍ�', 'INT', '0', NULL, '0', NULL, NULL, NULL, '2020-12-21 17:36:38', '83', '2020-12-21 17:36:38', '83', '1');


/* �y�C�W�[���ڂ�ǉ��������߃r���[���X�V����(mypage03�Ŏ��s) */
DROP VIEW IF EXISTS `MV_OemClaimAccountInfo`;

CREATE VIEW `MV_OemClaimAccountInfo` AS
    SELECT *
    FROM coraldb_new01.T_OemClaimAccountInfo
;

/* Ұّ}�����ڂɃy�C�W�[�̍��ڂ�ǉ� */
INSERT INTO M_Code VALUES('72', '295', '���q�l�ԍ�', '{CustomerNumber}', '39', '40', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '296', '���q�l�ԍ�', '{CustomerNumber}', '41', '42', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '297', '���q�l�ԍ�', '{CustomerNumber}', '43', '44', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '298', '���q�l�ԍ�', '{CustomerNumber}', '45', '46', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '299', '�m�F�ԍ�', '{ConfirmNumber}', '39', '40', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '300', '�m�F�ԍ�', '{ConfirmNumber}', '41', '42', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '301', '�m�F�ԍ�', '{ConfirmNumber}', '43', '44', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '302', '�m�F�ԍ�', '{ConfirmNumber}', '45', '46', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '303', '���[�@�֔ԍ�', '{Bk_Number}', '39', '40', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '304', '���[�@�֔ԍ�', '{Bk_Number}', '41', '42', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '305', '���[�@�֔ԍ�', '{Bk_Number}', '43', '44', NULL, '1', NOW(), '1', NOW(), '1', '1');
INSERT INTO M_Code VALUES('72', '306', '���[�@�֔ԍ�', '{Bk_Number}', '45', '46', NULL, '1', NOW(), '1', NOW(), '1', '1');

/* �G���[���b�Z�[�W�̍ő啶�������g�� */
ALTER TABLE T_ClaimError MODIFY ErrorMsg varchar(1000);
