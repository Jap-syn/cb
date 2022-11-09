-- �W���e�b�N���ʃe�[�u��
CREATE TABLE `T_JtcResult` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `OrderSeq` bigint(20) DEFAULT NULL,
  `SendDate` datetime DEFAULT NULL,
  `ReceiveDate` datetime DEFAULT NULL,
  `Status` int(11) DEFAULT NULL,
  `Result` int(11) DEFAULT NULL,
  `JintecManualJudgeFlg` int(11) DEFAULT NULL,
  PRIMARY KEY (`Seq`),
  KEY `Idx_T_JtcResult01` (`OrderSeq`),
  KEY `Idx_T_JtcResult02` (`SendDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- �W���e�b�N���ʏڍ׃e�[�u��
CREATE TABLE `T_JtcResult_Detail` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `JtcSeq` bigint(20) DEFAULT NULL,
  `OrderSeq` bigint(20) DEFAULT NULL,
  `ClassId` varchar(50) DEFAULT NULL,
  `ItemId` varchar(50) DEFAULT NULL,
  `Value` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`Seq`),
  KEY `Idx_T_JtcResult_Detail01` (`JtcSeq`),
  KEY `Idx_T_JtcResult_Detail02` (`OrderSeq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- �T�C�g�e�[�u���ɃW���e�b�N�蓮�^�M�����J�����ǉ�
ALTER TABLE `T_Site` 
ADD COLUMN `JintecManualReqFlg` TINYINT NOT NULL DEFAULT 0 AFTER `MultiOrderScore`;

-- �Г��^�M�����ɃW���e�b�N�蓮�^�M�����J�����ǉ�(��900�b)
ALTER TABLE `T_CreditCondition` 
ADD COLUMN `JintecManualReqFlg` TINYINT NOT NULL DEFAULT 0 AFTER `CreditCriterionId`;


-- ����Ͻ���Ƀf�[�^�o�^
INSERT INTO M_CodeManagement VALUES(187 ,'�W���e�b�N���ʏڍ�(���ӎ���)' ,NULL ,'���ӎ���ID' ,1 ,NULL ,0,NULL,0,NULL, NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(187,1 ,'�Y���f�[�^�Ȃ�',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(187,2 ,'����',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(187,3 ,'�s����~����',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(187,4 ,'���߉����i3�����ȉ��j',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(187,5 ,'���߉����i4�����ȏ�6�����ȉ��j',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(187,6 ,'�ύX�ߑ�',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(187,7 ,'�������p',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(187,8 ,'�������p',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(187,9 ,'�l�r�f�Ȃ�',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
