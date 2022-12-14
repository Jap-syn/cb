-- TCgÊWve[uì¬
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


-- ÆÒêf[^CSVÚÇÁ
INSERT INTO M_TemplateField VALUES ( 45 , 74, 'NpRateCount1' ,'s¥¢¦ijêTÔ' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 75, 'NpRateCount2' ,'s¥¢¦ijê' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 76, 'NpRateCount3' ,'s¥¢¦ijê' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 77, 'NpRateCount4' ,'s¥¢¦ijZ' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 78, 'NpRateCount5' ,'s¥¢¦ijêN' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 79, 'NpRateCount6' ,'s¥¢¦ijSÌ' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 80, 'NpRateMoney1' ,'s¥¢¦iàzjPTú' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 81, 'NpRateMoney2' ,'s¥¢¦iàzjUOú' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 82, 'NpRateMoney3' ,'s¥¢¦iàzjUOú' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 83, 'NpRateMoney4' ,'s¥¢¦iàzjUOú' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 84, 'NpRateMoney5' ,'s¥¢¦iàzjRXOú' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 85, 'NpRateMoney6' ,'s¥¢¦iàzjSÌ' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 86, 'SiteProfitFeeRate' ,'ÆÒûviRPjè¿¦' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 87, 'SiteProfitRate' ,'ÆÒûviRPjûv¦' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 88, 'SiteProfitAndLoss' ,'ÆÒûviRPj¹vz' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);


-- s¥¢¦wiFµ«¢l
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg
) VALUES( '[DEFAULT]','systeminfo', 'NpRateColorThreshold', '2.0', 's¥¢¦wiFµ«¢l()', NOW(), 9, NOW(), 9, '1');
