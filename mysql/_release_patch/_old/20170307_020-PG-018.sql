-- �T�C�g�ʏW�v�e�[�u���쐬
CREATE TABLE `T_SiteTotal` (
  `SiteId` bigint(20) NOT NULL,
  `NpTotal` TEXT DEFAULT NULL,
  `RegistDate` datetime DEFAULT NULL,
  `RegistId` int(11) DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `UpdateId` int(11) DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`SiteId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ���Ǝ҈ꗗ�f�[�^CSV���ڒǉ�
INSERT INTO M_TemplateField VALUES ( 45 , 74, 'NpRateCount1' ,'�s�������i�����j��T��' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 75, 'NpRateCount2' ,'�s�������i�����j�ꃖ��' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 76, 'NpRateCount3' ,'�s�������i�����j�ꃖ��' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 77, 'NpRateCount4' ,'�s�������i�����j�Z����' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 78, 'NpRateCount5' ,'�s�������i�����j��N' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 79, 'NpRateCount6' ,'�s�������i�����j�S��' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 80, 'NpRateMoney1' ,'�s�������i���z�j�P�T��' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 81, 'NpRateMoney2' ,'�s�������i���z�j�U�O��' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 82, 'NpRateMoney3' ,'�s�������i���z�j�U�O��' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 83, 'NpRateMoney4' ,'�s�������i���z�j�U�O��' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 84, 'NpRateMoney5' ,'�s�������i���z�j�R�X�O��' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 85, 'NpRateMoney6' ,'�s�������i���z�j�S��' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 86, 'SiteProfitFeeRate' ,'���ƎҎ��v�i�R�P���j�萔����' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 87, 'SiteProfitRate' ,'���ƎҎ��v�i�R�P���j���v��' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 88, 'SiteProfitAndLoss' ,'���ƎҎ��v�i�R�P���j���v�z' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);


-- �s�������w�i�F�������l
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg
) VALUES( '[DEFAULT]','systeminfo', 'NpRateColorThreshold', '2.0', '�s�������w�i�F�������l(��)', NOW(), 9, NOW(), 9, '1');
