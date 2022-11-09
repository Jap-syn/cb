-- サイト別集計テーブル作成
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


-- 事業者一覧データCSV項目追加
INSERT INTO M_TemplateField VALUES ( 45 , 74, 'NpRateCount1' ,'不払い率（件数）一週間' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 75, 'NpRateCount2' ,'不払い率（件数）一ヶ月' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 76, 'NpRateCount3' ,'不払い率（件数）一ヶ月' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 77, 'NpRateCount4' ,'不払い率（件数）六ヶ月' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 78, 'NpRateCount5' ,'不払い率（件数）一年' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 79, 'NpRateCount6' ,'不払い率（件数）全体' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 80, 'NpRateMoney1' ,'不払い率（金額）１５日' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 81, 'NpRateMoney2' ,'不払い率（金額）６０日' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 82, 'NpRateMoney3' ,'不払い率（金額）６０日' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 83, 'NpRateMoney4' ,'不払い率（金額）６０日' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 84, 'NpRateMoney5' ,'不払い率（金額）３９０日' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 85, 'NpRateMoney6' ,'不払い率（金額）全体' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 86, 'SiteProfitFeeRate' ,'事業者収益（３ケ月）手数料率' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 87, 'SiteProfitRate' ,'事業者収益（３ケ月）収益率' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 45 , 88, 'SiteProfitAndLoss' ,'事業者収益（３ケ月）損益額' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);


-- 不払い率背景色しきい値
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg
) VALUES( '[DEFAULT]','systeminfo', 'NpRateColorThreshold', '2.0', '不払い率背景色しきい値(％)', NOW(), 9, NOW(), 9, '1');
