-- �����Ǘ�
ALTER TABLE T_ClaimControl ADD COLUMN `CreditSettlementDecisionDate` DATE  NULL AFTER `MypageReissueReClaimFee`;

-- ����_��v
ALTER TABLE AT_Order ADD COLUMN `ExtraPayType`  VARCHAR(30) NULL AFTER `CreditTransferRequestFlg`;
ALTER TABLE AT_Order ADD COLUMN `ExtraPayKey`   VARCHAR(50) NULL AFTER `ExtraPayType`;
ALTER TABLE AT_Order ADD COLUMN `ExtraPayNote`  TEXT        NULL AFTER `ExtraPayKey`;

-- ����_��v�y�ǉ��x�������@�敪�z�y�ǉ��x�������@���z�ɃC���f�b�N�X�t�^
ALTER TABLE `AT_Order` ADD INDEX `Idx_AT_Order04` (
  `ExtraPayType` ASC
, `ExtraPayKey`  ASC
);

-- �T�C�g
ALTER TABLE T_Site ADD COLUMN `PaymentAfterArrivalFlg` TINYINT NOT NULL DEFAULT 0 AFTER `JintecJudge9`;
ALTER TABLE T_Site ADD COLUMN `ReceiptIssueProviso` VARCHAR(255)  NULL AFTER `PaymentAfterArrivalFlg`;

-- �o�b�`�r������
ALTER TABLE `T_BatchLock` DROP INDEX `ThreadNo_UNIQUE` ;
ALTER TABLE `T_BatchLock` DROP INDEX `BatchId_UNIQUE` ;
ALTER TABLE `T_BatchLock` ADD UNIQUE INDEX `Idx_T_BatchLock01` (`BatchId` ASC, `ThreadNo` ASC);

INSERT INTO T_BatchLock (BatchId, ThreadNo, BatchName, BatchLock, UpdateDate) VALUES (2, 1, '�}�C�y�[�W�A�g�o�b�`', 0, NOW());

-- �R�[�h���ʊǗ��}�X�^�[
INSERT INTO M_CodeManagement(CodeId, CodeName, KeyPhysicalName, KeyLogicName, Class1ValidFlg, Class1Name, Class2ValidFlg, Class2Name, Class3ValidFlg, Class3Name, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (198, '�������@', null, '�͂��Ă��猈�ϗp', 0, null, 0, null, 0, null, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_CodeManagement(CodeId, CodeName, KeyPhysicalName, KeyLogicName, Class1ValidFlg, Class1Name, Class2ValidFlg, Class2Name, Class3ValidFlg, Class3Name, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199, '�͂��Ă��猈�ϗ��p', null, '�͂��Ă��猈�ϗ��p', 0, null, 0, null, 0, null, NOW(), 1, NOW(), 1, 1);

-- �R�[�h�}�X�^�[�y�������@�z
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (198, 1, '�R���r�j', null, null, null, '��', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (198, 2, '�X�֋�', null, null, null, '��', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (198, 3, '��s', null, null, null, '��', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (198, 4, 'LINE Pay', null, null, null, '��', 0, NOW(), 83, NOW(), 83,1 );
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (198, 5, '�N���W�b�g����', null, null, null, '��', 0, NOW(), 83, NOW(), 83,1 );

-- �R�[�h�}�X�^�[�y�͂��Ă��猈�ϗ��p�z
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199, 0, '�N���W�b�g���ϗ��p����', null, null, null, '90��', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199, 1, '�N���W�b�g���ϗ��p�s�R�����g', null, null, null, '�N���W�b�g���ϗ��p�s�R�����g', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199, 2, '�N���W�b�g���ϐ\�����R�����g', null, null, null, '�N���W�b�g���ϐ\�����R�����g', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199, 3, '�N���W�b�g���ϊ����σR�����g', null, null, null, '�N���W�b�g���ϊ����σR�����g', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199, 4, '�N���W�b�g���ω\�R�����g', null, null, null, '�N���W�b�g���ω\�R�����g', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199, 10, '���������s(����)�N���W�b�g���ϗ��p�\��CSV�}���R�����g', null, null, null, '���������s(����)', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199,20, 'LIEN-Pay�pURL', null, null, null, 'http://www.ato-barai.com/guidance/faq.html', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199,21, 'PayPay�pURL', null, null, null, 'http://www.ato-barai.com/guidance/faq.html', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199,22, '�R���r�j�����pURL', null, null, null, 'http://www.ato-barai.com/guidance/faq.html', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199,23, '�X�֋ǗpURL', null, null, null, 'http://www.ato-barai.com/guidance/faq.html', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199,24, '��s�pURL', null, null, null, 'http://www.ato-barai.com/guidance/faq.html', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code(CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (199,99, '�w���v�pURL', null, null, null, 'http://www.ato-barai.com/guidance/faq.html', 0, NOW(), 1, NOW(), 1, 1);

-- ���[���e���v���[�g�y�͂��Ă��猈�ϐ��������s���[���z
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES (104,'�͂��Ă��猈�ϐ��������s���[���iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'�y�㕥��.com�z�͂��Ă��猈�ϐ��������s�ē��iPC�j','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��{EnterpriseNameKj}��{OrderId}��{SiteNameKj}��{Phone}��{CustomerNameKj}��{OrderDate}��{UseAmount}��{LimitDate}��{SettlementFee}��{OrderItems}��{OneOrderItem}��{DeliveryFee}��{Tax}��{PassWord}��{OrderPageAccessUrl}',NULL,NOW(),1,NOW(),66,1);
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES (105,'�͂��Ă��猈�ϐ��������s���[���iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'�y�㕥��.com�z�͂��Ă��猈�ϐ��������s�ē��iCEL�j','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��{EnterpriseNameKj}��{OrderId}��{SiteNameKj}��{Phone}��{CustomerNameKj}��{OrderDate}��{UseAmount}��{LimitDate}��{SettlementFee}��{OrderItems}��{OneOrderItem}��{DeliveryFee}��{Tax}��{PassWord}��{OrderPageAccessUrl}',NULL,NOW(),1,NOW(),66,1);
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT '106' AS Class, '�N���W�b�g���ϊ������[���iPC�j' AS ClassName, mt.FromTitle, mt.FromTitleMime, mt.FromAddress, mt.ToTitle, mt.ToTitleMime, mt.ToAddress, mt.Subject, mt.SubjectMime, mt.Body, mt.OemId, NOW() AS RegistDate, 1 AS RegistId, NOW() AS UpdateDate, 1 AS UpdateId, 1 AS ValidFlg FROM T_MailTemplate AS mt WHERE mt.Class = 6 AND IFNULL(mt.OemId, 0) = 0;
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT '107' AS Class, '�N���W�b�g���ϊ������[���iCEL�j' AS ClassName, mt.FromTitle, mt.FromTitleMime, mt.FromAddress, mt.ToTitle, mt.ToTitleMime, mt.ToAddress, mt.Subject, mt.SubjectMime, mt.Body, mt.OemId, NOW() AS RegistDate, 1 AS RegistId, NOW() AS UpdateDate, 1 AS UpdateId, 1 AS ValidFlg FROM T_MailTemplate AS mt WHERE mt.Class = 7 AND IFNULL(mt.OemId, 0) = 0;

-- ���[���e���v���[�g�y�͂��Ă��猈�ϗp�z
INSERT INTO M_TemplateHeader VALUES( 97 , 'CKI04047_1', 0, 0, 0, '�͂��Ă��猈�ϗp', 1, ',', '\"' ,'*' ,0,'KI04047', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 1, 'PostalCode', '�ڋq�X�֔ԍ�', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 2, 'UnitingAddress', '�ڋq�Z��', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 3, 'NameKj', '�ڋq����', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 4, 'OrderId', '�����h�c', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 5, 'ReceiptOrderDate', '������', 'DATE', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 6, 'SiteNameKj', '�w���X��', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 7, 'Url', '�w���XURL', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 8, 'ContactPhoneNumber', '�w���X�d�b�ԍ�', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 9, 'ClaimAmount', '�������z', 'BIGINT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 10, 'CarriageFee', '����', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 11, 'ChargeFee', '���ώ萔��', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 12, 'ReIssueCount', '������', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 13, 'LimitDate', '�x��������', 'DATE', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 14, 'Cv_BarcodeData2', '�o�[�R�[�h�f�[�^', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 15, 'ItemNameKj_1', '���i���P', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 16, 'ItemNum_1', '���ʂP', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 17, 'UnitPrice_1', '�P���P', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 18, 'ItemNameKj_2', '���i���Q', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 19, 'ItemNum_2', '���ʂQ', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 20, 'UnitPrice_2', '�P���Q', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 21, 'ItemNameKj_3', '���i���R', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 22, 'ItemNum_3', '���ʂR', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 23, 'UnitPrice_3', '�P���R', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 24, 'ItemNameKj_4', '���i���S', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 25, 'ItemNum_4', '���ʂS', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 26, 'UnitPrice_4', '�P���S', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 27, 'ItemNameKj_5', '���i���T', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 28, 'ItemNum_5', '���ʂT', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 29, 'UnitPrice_5', '�P���T', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 30, 'ItemNameKj_6', '���i���U', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 31, 'ItemNum_6', '���ʂU', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 32, 'UnitPrice_6', '�P���U', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 33, 'ItemNameKj_7', '���i���V', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 34, 'ItemNum_7', '���ʂV', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 35, 'UnitPrice_7', '�P���V', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 36, 'ItemNameKj_8', '���i���W', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 37, 'ItemNum_8', '���ʂW', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 38, 'UnitPrice_8', '�P���W', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 39, 'ItemNameKj_9', '���i���X', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 40, 'ItemNum_9', '���ʂX', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 41, 'UnitPrice_9', '�P���X', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 42, 'ItemNameKj_10', '���i���P�O', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 43, 'ItemNum_10', '���ʂP�O', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 44, 'UnitPrice_10', '�P���P�O', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 45, 'ItemNameKj_11', '���i���P�P', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 46, 'ItemNum_11', '���ʂP�P', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 47, 'UnitPrice_11', '�P���P�P', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 48, 'ItemNameKj_12', '���i���P�Q', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 49, 'ItemNum_12', '���ʂP�Q', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 50, 'UnitPrice_12', '�P���P�Q', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 51, 'ItemNameKj_13', '���i���P�R', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 52, 'ItemNum_13', '���ʂP�R', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 53, 'UnitPrice_13', '�P���P�R', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 54, 'ItemNameKj_14', '���i���P�S', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 55, 'ItemNum_14', '���ʂP�S', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 56, 'UnitPrice_14', '�P���P�S', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 57, 'ItemNameKj_15', '���i���P�T', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 58, 'ItemNum_15', '���ʂP�T', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 59, 'UnitPrice_15', '�P���P�T', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 60, 'ItemNameKj_16', '���i���P�U', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 61, 'ItemNum_16', '���ʂP�U', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 62, 'UnitPrice_16', '�P���P�U', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 63, 'ItemNameKj_17', '���i���P�V', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 64, 'ItemNum_17', '���ʂP�V', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 65, 'UnitPrice_17', '�P���P�V', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 66, 'ItemNameKj_18', '���i���P�W', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 67, 'ItemNum_18', '���ʂP�W', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 68, 'UnitPrice_18', '�P���P�W', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 69, 'ItemNameKj_19', '���i���P�X', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 70, 'ItemNum_19', '���ʂP�X', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 71, 'UnitPrice_19', '�P���P�X', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 72, 'ClaimFee', '�Đ������s�萔��', 'BIGINT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 73, 'DamageInterestAmount', '�x�����Q��', 'BIGINT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 74, 'TotalItemPrice', '���v', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 75, 'Ent_OrderId', '�C�Ӓ����ԍ�', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 76, 'TaxAmount', '����Ŋz', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 77, 'Cv_ReceiptAgentName', 'CVS���[��s��Ж�', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 78, 'Cv_SubscriberName', 'CVS���[��s�����Җ�', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 79, 'Cv_BarcodeData', '�o�[�R�[�h�f�[�^(CD�t);', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 80, 'Cv_BarcodeString1', '�o�[�R�[�h����1', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 81, 'Cv_BarcodeString2', '�o�[�R�[�h����2', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 82, 'Bk_BankCode', '��s���� - ��s�R�[�h', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 83, 'Bk_BranchCode', '��s���� - �x�X�R�[�h', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 84, 'Bk_BankName', '��s���� - ��s��', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 85, 'Bk_BranchName', '��s���� - �x�X��', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 86, 'Bk_DepositClass', '��s���� - �������', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 87, 'Bk_AccountNumber', '��s���� - �����ԍ�', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 88, 'Bk_AccountHolder', '��s���� - �������`', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 89, 'Bk_AccountHolderKn', '��s���� - �������`�J�i', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 90, 'Yu_SubscriberName', '�䂤������� - �����Җ�', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 91, 'Yu_AccountNumber', '�䂤������� - �����ԍ�', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 92, 'Yu_ChargeClass', '�䂤������� - �������S�敪', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 93, 'Yu_MtOcrCode1', '�䂤������� - MT�pOCR�R�[�h1', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 94, 'Yu_MtOcrCode2', '�䂤������� - MT�pOCR�R�[�h2', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 95, 'MypageToken', '�}�C�y�[�W���O�C���p�X���[�h', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 96, 'ItemsCount', '���i���v��', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 97, 'TaxClass', '����ŋ敪', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 98, 'CorporateName', '�@�l��', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 99, 'DivisionName', '������', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 100, 'CpNameKj', '�S���Җ�', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES ( 97, 101, 'Comment', '�R�����g', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1 );

-- ---------------------------
-- �}�C�y�[�W�X�L�[�}����
-- ---------------------------
DROP VIEW IF EXISTS `T_ReceiptIssueHistory`;
CREATE TABLE T_ReceiptIssueHistory
( Seq               BIGINT      NOT NULL AUTO_INCREMENT
, OrderSeq          BIGINT      DEFAULT NULL
, ReceiptIssueDate  DATETIME    DEFAULT NULL
, RegistDate        DATETIME    DEFAULT NULL
, RegistId          INT         DEFAULT NULL
, PRIMARY KEY(Seq)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------
-- ��X�L�[�}����
-- ---------------------------
-- View�̍č\�� ���^�p���A���J���̓X�L�[�}���قȂ�̂Œ���
DROP VIEW IF EXISTS `MPV_ReceiptIssueHistory`;

CREATE VIEW `MPV_ReceiptIssueHistory` AS
  SELECT *
    FROM coraldb_mypage01.T_ReceiptIssueHistory
;
