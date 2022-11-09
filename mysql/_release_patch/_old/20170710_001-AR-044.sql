/* �R�[�h�}�X�^�[�Ɍ������@��ǉ� */
INSERT INTO M_CodeManagement VALUES(192, '�������@', NULL, '�������@', 0, NULL, 0, NULL, 0, NULL, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(192, 0, '����', NULL, '����', NULL, NULL, 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(192, 1, '�O��', NULL, '�O��', NULL, NULL, 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(192, 2, '���', NULL, '���', NULL, NULL, 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(192, 3, '���S', NULL, '���S', NULL, NULL, 0, NOW(), 1, NOW(), 1, 1);



/* �Г��^�M�����ɃJ�����ǉ� */ -- 2000�b 20170508�̃_���v�Ŏ��{
ALTER TABLE `T_CreditCondition` 
ADD COLUMN `SearchPattern` INT NOT NULL DEFAULT 0 AFTER `EnterpriseId`,
ADD COLUMN `AddConditionCount` INT NOT NULL DEFAULT 0 AFTER `SearchPattern`;


/* �Г��^�M�����̏����J�e�S���[�������XID�A�d�b�ԍ��ɂ��āA�������@�����S��v�ɍX�V */
UPDATE T_CreditCondition SET SearchPattern = 3 WHERE OrderSeq = -1 AND Category IN (5, 8);


/* ����ڰ�̨���ނ�UPDATE�iListNumber �ɑ΂���UPDATE�Ȃ̂ŁA������s���B�j */
UPDATE M_TemplateField SET ListNumber='7' WHERE TemplateSeq='48' and PhysicalName='ValidFlg';
UPDATE M_TemplateField SET ListNumber='6' WHERE TemplateSeq='48' and PhysicalName='Comment';
UPDATE M_TemplateField SET ListNumber='5' WHERE TemplateSeq='48' and PhysicalName='Point';
UPDATE M_TemplateField SET ListNumber='4' WHERE TemplateSeq='48' and PhysicalName='Cstring';


/* ����ڰ�̨���ނ�INSERT */
INSERT INTO M_TemplateField VALUES ( 48 , 3, 'SearchPattern' ,'�������@' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);



/* �ǉ��Г��^�M�����e�[�u���쐬 */
CREATE TABLE `T_AddCreditCondition` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `P_ConditionSeq` bigint(20) DEFAULT NULL,
  `Category` int(11) DEFAULT NULL,
  `Cstring` varchar(4000) DEFAULT NULL,
  `CstringHash` varchar(4000) DEFAULT NULL,
  `RegistDate` datetime DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT '1',
  `RegCstring` varchar(4000) DEFAULT NULL,
  `RegCstringHash` varchar(32) DEFAULT NULL,
  `ComboHash` varchar(32) DEFAULT NULL,
  `EnterpriseId` bigint(20) DEFAULT NULL,
  `SearchPattern` int(11) NOT NULL DEFAULT '0',
  `RegistId` int(11) DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `UpdateId` int(11) DEFAULT NULL,
  PRIMARY KEY (`Seq`),
  KEY `Idx_T_CreditCondition01` (`P_ConditionSeq`),
  KEY `Idx_T_CreditCondition02` (`Category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


