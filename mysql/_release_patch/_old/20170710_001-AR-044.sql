/* コードマスターに検索方法を追加 */
INSERT INTO M_CodeManagement VALUES(192, '検索方法', NULL, '検索方法', 0, NULL, 0, NULL, 0, NULL, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(192, 0, '部分', NULL, '部分', NULL, NULL, 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(192, 1, '前方', NULL, '前方', NULL, NULL, 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(192, 2, '後方', NULL, '後方', NULL, NULL, 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(192, 3, '完全', NULL, '完全', NULL, NULL, 0, NOW(), 1, NOW(), 1, 1);



/* 社内与信条件にカラム追加 */ -- 2000秒 20170508のダンプで実施
ALTER TABLE `T_CreditCondition` 
ADD COLUMN `SearchPattern` INT NOT NULL DEFAULT 0 AFTER `EnterpriseId`,
ADD COLUMN `AddConditionCount` INT NOT NULL DEFAULT 0 AFTER `SearchPattern`;


/* 社内与信条件の条件カテゴリーが加盟店ID、電話番号について、検索方法を完全一致に更新 */
UPDATE T_CreditCondition SET SearchPattern = 3 WHERE OrderSeq = -1 AND Category IN (5, 8);


/* ﾃﾝﾌﾟﾚｰﾄﾌｨｰﾙﾄﾞのUPDATE（ListNumber に対するUPDATEなので、下から行う。） */
UPDATE M_TemplateField SET ListNumber='7' WHERE TemplateSeq='48' and PhysicalName='ValidFlg';
UPDATE M_TemplateField SET ListNumber='6' WHERE TemplateSeq='48' and PhysicalName='Comment';
UPDATE M_TemplateField SET ListNumber='5' WHERE TemplateSeq='48' and PhysicalName='Point';
UPDATE M_TemplateField SET ListNumber='4' WHERE TemplateSeq='48' and PhysicalName='Cstring';


/* ﾃﾝﾌﾟﾚｰﾄﾌｨｰﾙﾄﾞのINSERT */
INSERT INTO M_TemplateField VALUES ( 48 , 3, 'SearchPattern' ,'検索方法' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);



/* 追加社内与信条件テーブル作成 */
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


