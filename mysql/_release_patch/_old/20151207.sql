/***************************************************************************************************/
/** ��������ׂ̃��C�A�E�g�����V�X�e�����l�ɖ߂��Ή�
/***************************************************************************************************/
SELECT * FROM M_TemplateHeader WHERE TemplateId = 'CKA11019_2';

-- ��������ׁi���ߓ��ʁj
DELETE FROM M_TemplateField WHERE TemplateSeq = (SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKA11019_2'); -- 7

-- ��������ׁi���ߓ��ʁj
INSERT INTO M_TemplateField VALUES ( 7 , 1, 'ReceiptOrderDate' ,'������' ,'DATE' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 7 , 2, 'OrderId' ,'����ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 7 , 3, 'Ent_OrderId' ,'�C�Ӓ����ԍ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 7 , 4, 'NameKj' ,'�w����' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 7 , 5, 'UseAmount' ,'�ڋq�������z' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 7 , 6, 'SettlementFee' ,'���ώ萔��' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 7 , 7, 'ClaimFee' ,'���������s�萔��' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 7 , 8, 'ChargeAmount' ,'���������v' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 7 , 9, 'SiteNameKj' ,'��t�T�C�g' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 7 , 10, 'SiteId' ,'�T�C�gID' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 7 , 11, 'EnterpriseId' ,'�����XID' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0);
INSERT INTO M_TemplateField VALUES ( 7 , 12, 'EnterpriseNameKj' ,'�����X��' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0);
INSERT INTO M_TemplateField VALUES ( 7 , 13, 'FixedDate' ,'���֒��ߓ�' ,'DATE' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0);
INSERT INTO M_TemplateField VALUES ( 7 , 14, 'ExecScheduleDate' ,'���֗\���' ,'DATE' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0);
INSERT INTO M_TemplateField VALUES ( 7 , 15, 'ChargeCount' ,'���������' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0);
INSERT INTO M_TemplateField VALUES ( 7 , 16, 'Deli_JournalIncDate' ,'�`�o��' ,'DATE' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0);
INSERT INTO M_TemplateField VALUES ( 7 , 17, 'FixedDate2' ,'���֒�' ,'DATE' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0);
INSERT INTO M_TemplateField VALUES ( 7 , 18, 'StampFee' ,'�󎆑��' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0);





/***************************************************************************************************/
/** �`�[�C���̃��C�A�E�g�����V�X�e�����l�ɖ߂��Ή�
/***************************************************************************************************/
INSERT INTO M_TemplateHeader VALUES( 82 , 'CKA03011_3', 0, 0, 0, '�z���`�[�C��CSV�i10���ڔŁj', 1, ',', '\"' ,'*' ,0,'KA03011', NULL, NOW(), 9, NOW(), 9,1);


INSERT INTO M_TemplateField VALUES ( 82 , 1, 'OrderId' ,'����ID' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 82 , 2, 'Ent_OrderId' ,'�C�Ӓ����ԍ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 82 , 3, 'Deli_DeliveryMethod' ,'�z�����' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 82 , 4, 'Deli_JournalNumber' ,'�z���`�[�ԍ�' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 82 , 5, 'ReceiptOrderDate' ,'������' ,'DATE' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 82 , 6, 'NameKj' ,'�w����' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 82 , 7, 'Phone' ,'�d�b�ԍ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 82 , 8, 'UseAmount' ,'�w�����z' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 82 , 9, 'UnitingAddress' ,'�z����Z��' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 82 , 10, 'IsSelfBilling' ,'���Ј��' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);



INSERT INTO M_TemplateHeader VALUES( 83 , 'CKA03011_4', 0, 0, 0, '�z���`�[�C��CSV�i9���ڔŁj', 1, ',', '\"' ,'*' ,0,'KA03011', NULL, NOW(), 9, NOW(), 9,1);

INSERT INTO M_TemplateField VALUES ( 83 , 1, 'OrderId' ,'����ID' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 83 , 2, 'Ent_OrderId' ,'�C�Ӓ����ԍ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 83 , 3, 'Deli_DeliveryMethod' ,'�z�����' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 83 , 4, 'Deli_JournalNumber' ,'�z���`�[�ԍ�' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 83 , 5, 'ReceiptOrderDate' ,'������' ,'DATE' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 83 , 6, 'NameKj' ,'�w����' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 83 , 7, 'Phone' ,'�d�b�ԍ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 83 , 8, 'UseAmount' ,'�w�����z' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 83 , 9, 'UnitingAddress' ,'�z����Z��' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);

