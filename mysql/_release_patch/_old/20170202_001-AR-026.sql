/* ����_��v 20170110�_���v�ɂ�80�b */
ALTER TABLE `AT_Order` 
ADD COLUMN `RepayTCFlg` TINYINT(4) NOT NULL DEFAULT 0 AFTER `Dmg_DailySummaryFlg`,
ADD COLUMN `RepayPendingFlg` TINYINT(4) NOT NULL DEFAULT 0 AFTER `RepayTCFlg`;

/* �ԋ��Ǘ� 20170110�_���v�ɂ�1�b */
ALTER TABLE `T_RepaymentControl` 
ADD COLUMN `NetStatus` TINYINT NOT NULL DEFAULT 0 AFTER `OutputFileSeq`,
ADD COLUMN `CoRecvNum` VARCHAR(20) NULL AFTER `NetStatus`,
ADD COLUMN `CoYoyakuNum` VARCHAR(11) NULL AFTER `CoRecvNum`,
ADD COLUMN `CoTranLimit` VARCHAR(12) NULL AFTER `CoYoyakuNum`,
ADD COLUMN `CoWcosId` VARCHAR(20) NULL AFTER `CoTranLimit`,
ADD COLUMN `CoWcosPassword` VARCHAR(20) NULL AFTER `CoWcosId`,
ADD COLUMN `CoWcosUrl` VARCHAR(260) NULL AFTER `CoWcosPassword`,
ADD COLUMN `CoTranReqDate` VARCHAR(14) NULL AFTER `CoWcosUrl`,
ADD COLUMN `CoTranProcDate` VARCHAR(14) NULL AFTER `CoTranReqDate`,
ADD COLUMN `MailFlg` TINYINT NOT NULL DEFAULT 9 AFTER `CoTranProcDate`,
ADD COLUMN `MailRetryCount` TINYINT NOT NULL DEFAULT 0 AFTER `MailFlg`,
ADD INDEX `Idx_T_RepaymentControl03` (`CoRecvNum` ASC);

/* �����Ǘ� 20170110�_���v�ɂ�44�b */
ALTER TABLE `T_ClaimControl` 
ADD INDEX `Idx_T_ClaimControl05` (`ClaimedBalance` ASC);

/* �R�[�h�}�X�^�[ ȯ�DE���ð�� */
INSERT INTO M_CodeManagement VALUES(188 ,'�l�b�g�c�d���X�e�[�^�X' ,NULL ,'�l�b�g�c�d���X�e�[�^�X' ,0 ,NULL ,0,NULL,0,NULL, NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(188,0 ,'���w��',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(188,1 ,'�w����',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(188,2 ,'���F��',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(188,3 ,'�n�K�L�o�͍�',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(188,4 ,'�ԋ���',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);

/* �R�[�h�}�X�^�[ �������� */
INSERT INTO M_Code VALUES(97,111 ,'�l�b�gDE���w����',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(97,112 ,'�l�b�gDE��揳�F��',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(97,113 ,'�l�b�gDE���n�K�L�o�͍�',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(97,114 ,'�l�b�gDE���ԋ���',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(97,115 ,'�ԋ��ۗ�',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(97,116 ,'�ԋ��ۗ�����',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);

/* �V�X�e������ */
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'NetTransferCommission', '324', '�l�b�gDE���U���萔��', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'NetCoTranLimitDays', '90', '�l�b�gDE��摗����������', NOW(), 9, NOW(), 9, '1');
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES( '[DEFAULT]','systeminfo', 'NetCoCorpCode', '9999999999', '�l�b�gDE��掖�Ǝ҃R�[�h', NOW(), 9, NOW(), 9, '1');

/* �e���v���[�g�}�X�^�[ �l�b�gDE���f�[�^ */
INSERT INTO M_TemplateHeader VALUES( 90 , 'CKI08070_2', 0, 0, 0, '�l�b�gDE���f�[�^', 0, ',', '' ,'SJIS-win' ,0,'KI08070', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateField VALUES ( 90 , 1, 'DataSyubetsu' ,'�f�[�^���' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 2, 'CoPayCode' ,'�x���R�[�h ' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 3, 'CoRecvNum' ,'���q�l�ԍ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 4, 'CoJigyosyaNo' ,'���ƎҔԍ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 5, 'CoAnkenNo' ,'�_��Č��ԍ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 6, 'CoWcosPassword' ,'WCOS�p�X���[�h' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 7, 'CoOpCode' ,'�f�[�^�敪' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 8, 'CoCorpCode' ,'���Ǝ҃R�[�h' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 9, 'CoTel' ,'�d�b�ԍ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 10, 'CoNameKanji' ,'���q�l����' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 11, 'CoTranLimit' ,'��������' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 12, 'CoTranAmount' ,'�������z' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 13, 'CoReserveNum' ,'�\��ԍ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 14, 'CoMemberNum' ,'����ԍ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 15, 'CoNameKana' ,'���q�l�����i�t���K�i�j' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 16, 'CoFree1' ,'WCOS ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 17, 'CoFree2' ,'���Z�@�փR�[�h' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 18, 'CoFree3' ,'�x�X�R�[�h' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 19, 'CoFree4' ,'�������' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 20, 'CoFree5' ,'�����ԍ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 21, 'CoFree6' ,'�������`�l��' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 22, 'CoFree7' ,'���[���A�h���X�P' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 23, 'CoFree8' ,'���[���A�h���X�Q' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 24, 'CoCFree1' ,'�t���[�X�y�[�X�P' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 25, 'CoCFree2' ,'�t���[�X�y�[�X�Q' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 26, 'CoCFree3' ,'�t���[�X�y�[�X�R' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 27, 'CoCFree4' ,'�t���[�X�y�[�X�S' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 28, 'CoCFree5' ,'�t���[�X�y�[�X�T' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 29, 'CoCFree6' ,'�t���[�X�y�[�X�U' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 30, 'CoCFree7' ,'�t���[�X�y�[�X�V' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 90 , 31, 'CoCFree8' ,'�t���[�X�y�[�X�W' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);

/* �e���v���[�g�}�X�^�[ �l�b�gDE���n�K�L�f�[�^ */
INSERT INTO M_TemplateHeader VALUES( 91 , 'CKI08070_3', 0, 0, 0, '�l�b�gDE���n�K�L�f�[�^', 1, ',', '\"' ,'*' ,0,'KI08070', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateField VALUES ( 91 , 1, 'PostalCode' ,'�ڋq�X�֔ԍ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 2, 'UnitingAddress' ,'�ڋq�Z��' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 3, 'NameKj' ,'�ڋq����' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 4, 'OrderId' ,'�����h�c' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 5, 'ReceiptOrderDate' ,'������' ,'DATE' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 6, 'SiteNameKj' ,'�w���X��' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 7, 'Url' ,'�w���XURL' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 8, 'ContactPhoneNumber' ,'�w���X�d�b�ԍ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 9, 'ClaimAmount' ,'�������z' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 10, 'CarriageFee' ,'����' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 11, 'ChargeFee' ,'���ώ萔��' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 12, 'ReIssueCount' ,'������' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 13, 'LimitDate' ,'�x��������' ,'DATE' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 14, 'Cv_BarcodeData2' ,'�o�[�R�[�h�f�[�^' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 15, 'ItemNameKj_1' ,'���i���P' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 16, 'ItemNum_1' ,'���ʂP' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 17, 'UnitPrice_1' ,'�P���P' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 18, 'ItemNameKj_2' ,'���i���Q' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 19, 'ItemNum_2' ,'���ʂQ' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 20, 'UnitPrice_2' ,'�P���Q' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 21, 'ItemNameKj_3' ,'���i���R' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 22, 'ItemNum_3' ,'���ʂR' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 23, 'UnitPrice_3' ,'�P���R' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 24, 'ItemNameKj_4' ,'���i���S' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 25, 'ItemNum_4' ,'���ʂS' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 26, 'UnitPrice_4' ,'�P���S' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 27, 'ItemNameKj_5' ,'���i���T' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 28, 'ItemNum_5' ,'���ʂT' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 29, 'UnitPrice_5' ,'�P���T' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 30, 'ItemNameKj_6' ,'���i���U' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 31, 'ItemNum_6' ,'���ʂU' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 32, 'UnitPrice_6' ,'�P���U' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 33, 'ItemNameKj_7' ,'���i���V' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 34, 'ItemNum_7' ,'���ʂV' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 35, 'UnitPrice_7' ,'�P���V' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 36, 'ItemNameKj_8' ,'���i���W' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 37, 'ItemNum_8' ,'���ʂW' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 38, 'UnitPrice_8' ,'�P���W' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 39, 'ItemNameKj_9' ,'���i���X' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 40, 'ItemNum_9' ,'���ʂX' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 41, 'UnitPrice_9' ,'�P���X' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 42, 'ItemNameKj_10' ,'���i���P�O' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 43, 'ItemNum_10' ,'���ʂP�O' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 44, 'UnitPrice_10' ,'�P���P�O' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 45, 'ItemNameKj_11' ,'���i���P�P' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 46, 'ItemNum_11' ,'���ʂP�P' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 47, 'UnitPrice_11' ,'�P���P�P' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 48, 'ItemNameKj_12' ,'���i���P�Q' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 49, 'ItemNum_12' ,'���ʂP�Q' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 50, 'UnitPrice_12' ,'�P���P�Q' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 51, 'ItemNameKj_13' ,'���i���P�R' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 52, 'ItemNum_13' ,'���ʂP�R' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 53, 'UnitPrice_13' ,'�P���P�R' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 54, 'ItemNameKj_14' ,'���i���P�S' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 55, 'ItemNum_14' ,'���ʂP�S' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 56, 'UnitPrice_14' ,'�P���P�S' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 57, 'ItemNameKj_15' ,'���i���P�T' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 58, 'ItemNum_15' ,'���ʂP�T' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 59, 'UnitPrice_15' ,'�P���P�T' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 60, 'ItemNameKj_16' ,'���i���P�U' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 61, 'ItemNum_16' ,'���ʂP�U' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 62, 'UnitPrice_16' ,'�P���P�U' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 63, 'ItemNameKj_17' ,'���i���P�V' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 64, 'ItemNum_17' ,'���ʂP�V' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 65, 'UnitPrice_17' ,'�P���P�V' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 66, 'ItemNameKj_18' ,'���i���P�W' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 67, 'ItemNum_18' ,'���ʂP�W' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 68, 'UnitPrice_18' ,'�P���P�W' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 69, 'ItemNameKj_19' ,'���i���P�X' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 70, 'ItemNum_19' ,'���ʂP�X' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 71, 'UnitPrice_19' ,'�P���P�X' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 72, 'ClaimFee' ,'�Đ������s�萔��' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 73, 'DamageInterestAmount' ,'�x�����Q��' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 74, 'TotalItemPrice' ,'���v' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 75, 'Ent_OrderId' ,'�C�Ӓ����ԍ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 76, 'TaxAmount' ,'����Ŋz' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 77, 'Cv_ReceiptAgentName' ,'CVS���[��s��Ж�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 78, 'Cv_SubscriberName' ,'CVS���[��s�����Җ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 79, 'Cv_BarcodeData' ,'�o�[�R�[�h�f�[�^(CD�t)' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 80, 'Cv_BarcodeString1' ,'�o�[�R�[�h����1' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 81, 'Cv_BarcodeString2' ,'�o�[�R�[�h����2' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 82, 'Bk_BankCode' ,'��s���� - ��s�R�[�h' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 83, 'Bk_BranchCode' ,'��s���� - �x�X�R�[�h' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 84, 'Bk_BankName' ,'��s���� - ��s��' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 85, 'Bk_BranchName' ,'��s���� - �x�X��' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 86, 'Bk_DepositClass' ,'��s���� - �������' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 87, 'Bk_AccountNumber' ,'��s���� - �����ԍ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 88, 'Bk_AccountHolder' ,'��s���� - �������`' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 89, 'Bk_AccountHolderKn' ,'��s���� - �������`�J�i' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 90, 'Yu_SubscriberName' ,'�䂤������� - �����Җ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 91, 'Yu_AccountNumber' ,'�䂤������� - �����ԍ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 92, 'Yu_ChargeClass' ,'�䂤������� - �������S�敪' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 93, 'Yu_MtOcrCode1' ,'�䂤������� - MT�pOCR�R�[�h1' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 94, 'Yu_MtOcrCode2' ,'�䂤������� - MT�pOCR�R�[�h2' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 95, 'MypageToken' ,'�}�C�y�[�W���O�C���p�X���[�h' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 96, 'ItemsCount' ,'���i���v��' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 97, 'TaxClass' ,'����ŋ敪' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 98, 'CorporateName' ,'�@�l��' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 99, 'DivisionName' ,'������' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 100, 'CpNameKj' ,'�S���Җ�' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 101, 'CoWcosId' ,'WCOS ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 102, 'CoWcosPassword' ,'WCOS �p�X���[�h' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 103, 'CoWcosUrl' ,'WCOS URL' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 104, 'RepayAmount' ,'�ԋ����z' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 105, 'TransferCommission' ,'�U���萔��' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 91 , 106, 'CoTranLimit' ,'�������͊���' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);


/* ���[���e���v���[�g */
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (90,'�l�b�gDE��惁�[���iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'�y�㕥���h�b�g�R���z���ԋ��̂��A��','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������\r\n�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������\r\n��������������������������������������������������������������������\r\n\r\n{CustomerNameKj}�l\r\n\r\n���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA\r\n�㕥���h�b�g�R���������p���������܂���\r\n�܂��Ƃɂ��肪�Ƃ��������܂����B\r\n\r\n{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A\r\n{OverReceiptAmount}�~�������x�������������Ă���܂����̂�\r\n���ԋ������Ă��������������A�������グ�܂����B\r\n\r\n�ԋ��̕��@�̂��ē����A�{�������җl���Z�����Ƀn�K�L�ɂĂ����肵�܂����B\r\n���ʗX�ւł̔����ƂȂ�܂��̂ŁA���q�l�̂��茳�ɓ͂��܂�\r\n�Q���`�T�����x������ꍇ���������܂��B\r\n��T�Ԃقǂ��҂����������Ă��͂��Ȃ��ꍇ�́A\r\n��ς��萔�ł͂������܂����A���̃��[���̖����ɋL�ڂ��Ă���܂�\r\n�㕥���h�b�g�R���J�X�^�}�[�Z���^�[�܂ł���񂭂������܂��B\r\n\r\n\r\n�y���������e�z\r\n�������ԍ��F{OrderId}\r\n���������F{OrderDate}\r\n�������X�܁F{SiteNameKj}\r\n���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}\r\n���������z�F{UseAmount}\r\n\r\n\r\n�s���_�Ȃǂ������܂�����A���C�y�ɂ��⍇�����������܂��B\r\n\r\n--------------------------------------------------------------\r\n�㕥��������s�T�[�r�X�y�㕥���h�b�g�R���z\r\n\r\n  ���⍇����F03�|5909�|3490\r\n  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j\r\n  mail: customer@ato-barai.com\r\n  \r\n�@�^�c��ЁF������ЃL���b�`�{�[��\r\n�@�Z���F��160-0023 �����s�V�h�搼�V�h6-14-1 �V�h�O���[���^���[�r��14F\r\n-------------------------------------------------------------- \r\n',NULL,'2016-02-23 14:00:00',1,'2017-02-14 09:49:26',83,1);
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (91,'�l�b�gDE��惁�[���iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'�y�㕥���h�b�g�R���z���ԋ��̂��A��','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��������������������������������������������������������������������\r\n�����₢���킹���������ہA���L���[�����ʂ��c�����܂܂��ԐM����������\r\n��������������������������������������������������������������������\r\n\r\n{CustomerNameKj}�l\r\n\r\n���̓x��{SiteNameKj}�ŏ��i���w���̍ۂɁA\r\n�㕥���h�b�g�R���������p���������܂���\r\n�܂��Ƃɂ��肪�Ƃ��������܂����B\r\n\r\n{ReceiptDate}��{ReceiptClass}��育�������m�F�������܂������A\r\n{OverReceiptAmount}�~�������x�������������Ă���܂����̂�\r\n���ԋ������Ă��������������A�������グ�܂����B\r\n\r\n�ԋ��̕��@�̂��ē����A�{�������җl���Z�����Ƀn�K�L�ɂĂ����肵�܂����B\r\n���ʗX�ւł̔����ƂȂ�܂��̂ŁA���q�l�̂��茳�ɓ͂��܂�\r\n�Q���`�T�����x������ꍇ���������܂��B\r\n��T�Ԃقǂ��҂����������Ă��͂��Ȃ��ꍇ�́A\r\n��ς��萔�ł͂������܂����A���̃��[���̖����ɋL�ڂ��Ă���܂�\r\n�㕥���h�b�g�R���J�X�^�}�[�Z���^�[�܂ł���񂭂������܂��B\r\n\r\n\r\n�y���������e�z\r\n�������ԍ��F{OrderId}\r\n���������F{OrderDate}\r\n�������X�܁F{SiteNameKj}\r\n���i���i1�i�ڂ̂ݕ\���j�F{OneOrderItem}\r\n���������z�F{UseAmount}\r\n\r\n\r\n�s���_�Ȃǂ������܂�����A���C�y�ɂ��⍇�����������܂��B\r\n\r\n--------------------------------------------------------------\r\n�㕥��������s�T�[�r�X�y�㕥���h�b�g�R���z\r\n\r\n  ���⍇����F03�|5909�|3490\r\n  �c�Ǝ��ԁF9:00�`18:00�@�N�����x�i�N���E�N�n�̂����j\r\n  mail: customer@ato-barai.com\r\n  \r\n�@�^�c��ЁF������ЃL���b�`�{�[��\r\n�@�Z���F��160-0023 �����s�V�h�搼�V�h6-14-1 �V�h�O���[���^���[�r��14F\r\n-------------------------------------------------------------- \r\n',NULL,'2016-02-23 14:00:00',1,'2017-02-14 09:49:26',83,1);

/* �R�[�h�}�X�^�[ ���[���p�����[�^�[ */
INSERT INTO M_Code VALUES(72,286 ,'�w���Җ�','{CustomerNameKj}' ,'90' , '91' ,NULL ,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(72,287 ,'�T�C�g��','{SiteNameKj}' ,'90' , '91' ,NULL ,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(72,288 ,'�����m�F��','{ReceiptDate}' ,'90' , '91' ,NULL ,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(72,289 ,'�������@','{ReceiptClass}' ,'90' , '91' ,NULL ,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(72,290 ,'�ߏ�����z','{OverReceiptAmount}' ,'90' , '91' ,NULL ,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(72,291 ,'����ID','{OrderId}' ,'90' , '91' ,NULL ,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(72,292 ,'������','{OrderDate}' ,'90' , '91' ,NULL ,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(72,293 ,'���i���i�擪�ЂƂj','{OneOrderItem}' ,'90' , '91' ,NULL ,1, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(72,294 ,'���v���z','{UseAmount}' ,'90' , '91' ,NULL ,1, NOW(), 1, NOW(), 1, 1);
