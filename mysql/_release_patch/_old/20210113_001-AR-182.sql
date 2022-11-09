-- é–ì‡ó^êMèåèÅiã‡äzÅj
DROP TABLE IF EXISTS `T_CreditConditionMoney`;
CREATE TABLE `T_CreditConditionMoney` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `OrderSeq` bigint(20) DEFAULT NULL,
  `Category` int(11) DEFAULT NULL,
  `Class` int(11) DEFAULT NULL,
  `Cstring` varchar(4000) DEFAULT NULL,
  `CstringHash` varchar(4000) DEFAULT NULL,
  `RegistDate` datetime DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT '1',
  `Point` int(11) DEFAULT NULL,
  `RegCstring` varchar(4000) DEFAULT NULL,
  `Comment` varchar(255) DEFAULT NULL,
  `RegCstringHash` varchar(32) DEFAULT NULL,
  `ComboHash` varchar(32) DEFAULT NULL,
  `CreditCriterionId` int(11) DEFAULT NULL,
  `JintecManualReqFlg` tinyint(4) NOT NULL DEFAULT '0',
  `EnterpriseId` bigint(20) DEFAULT NULL,
  `SearchPattern` int(11) NOT NULL DEFAULT '0',
  `AddConditionCount` int(11) NOT NULL DEFAULT '0',
  `RegistId` int(11) DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `UpdateId` int(11) DEFAULT NULL,
  PRIMARY KEY (`Seq`),
  KEY `Idx_T_CreditConditionMoney01` (`Cstring`(255)),
  KEY `Idx_T_CreditConditionMoney02` (`Category`),
  KEY `IDX_T_CreditConditionMoney03` (`RegCstring`(255)),
  KEY `IDX_T_CreditConditionMoney04` (`RegCstringHash`),
  KEY `IDX_T_CreditConditionMoney05` (`ComboHash`),
  KEY `Idx_T_CreditConditionMoney06` (`Class`),
  KEY `Idx_T_CreditConditionMoney07` (`ValidFlg`),
  KEY `Idx_T_CreditConditionMoney08` (`Category`,`ValidFlg`,`OrderSeq`),
  KEY `Idx_T_CreditConditionMoney09` (`RegCstring`(255),`Category`,`Class`,`ValidFlg`),
  KEY `Idx_T_CreditConditionMoney10` (`EnterpriseId`),
  KEY `Idx_T_CreditConditionMoney11` (`UpdateDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- çwì¸é“
ALTER TABLE T_Customer ADD COLUMN `Incre_MoneyScore` INT AFTER `Incre_PostalCodeNote`;
ALTER TABLE T_Customer ADD COLUMN `Incre_MoneyNote` TEXT NULL AFTER `Incre_MoneyScore`;

-- åüçıèåè
UPDATE M_Code SET KeyContent = 'ïîï™(ÅÅ)' , Class2 = 'ïîï™(ÅÅ)' WHERE CodeId = '192' and KeyCode = '0';
UPDATE M_Code SET KeyContent = 'ëOï˚(ÅÖ)' , Class2 = 'ëOï˚(ÅÖ)' WHERE CodeId = '192' and KeyCode = '1';
UPDATE M_Code SET KeyContent = 'äÆëS(ÅÅ)' , Class2 = 'äÆëS(ÅÅ)' WHERE CodeId = '192' and KeyCode = '3';
UPDATE M_Code SET KeyContent = 'å„ï˚(ÅÜ)' , Class2 = 'å„ï˚(ÅÜ)' WHERE CodeId = '192' and KeyCode = '2';

-- ã‡äzçÄñ⁄ÇÃí«â¡
INSERT INTO `M_Code` (`CodeId`,`KeyCode`,`KeyContent`,`Class1`,`Class2`,`Class3`,`Note`,`SystemFlg`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (3,9,'ã‡äz',NULL,'ã‡äz',NULL,NULL,0,now(),1,now(),1,1);
