-- 追加社内与信条件の項目追加
ALTER TABLE `T_AddCreditCondition` 
ADD COLUMN `P_Category` INT DEFAULT NULL AFTER `P_ConditionSeq`;

-- 追加社内与信条件の移行
UPDATE T_AddCreditCondition SET T_AddCreditCondition.P_Category = (SELECT Category FROM T_CreditCondition WHERE Seq = T_AddCreditCondition.P_ConditionSeq);


-- スキップ対象者リスト
CREATE TABLE `T_SkipTarget` (
  `ManCustId` bigint(20) NOT NULL,
  `RegNameKj` varchar(160) DEFAULT NULL,
  `RegUnitingAddress` text,
  `RegPhone` varchar(50) DEFAULT NULL,
  `MailAddress` varchar(255) DEFAULT NULL,
  `LastReceiptDate` date DEFAULT NULL,
  `LastClaimDate` date DEFAULT NULL,
  `RegistDate` datetime DEFAULT NULL,
  `RegistId` int(11) DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `UpdateId` int(11) DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ManCustId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- スキップ対象者削除リスト
CREATE TABLE `T_SkipDeleteList` (
  `ManCustId` bigint(20) NOT NULL,
  `RegNameKj` varchar(160) DEFAULT NULL,
  `RegUnitingAddress` text,
  `RegPhone` varchar(50) DEFAULT NULL,
  `MailAddress` varchar(255) DEFAULT NULL,
  `LastReceiptDate` date DEFAULT NULL,
  `LastClaimDate` date DEFAULT NULL,
  `RegistDate` datetime DEFAULT NULL,
  `RegistId` int(11) DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `UpdateId` int(11) DEFAULT NULL,
  `ValidFlg` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ManCustId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- スキップ対象者リスト作成バッチ管理
CREATE TABLE `T_SkipBatchControl` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `ExecDate` date DEFAULT NULL,
  `TargetYears` int(11) DEFAULT NULL,
  PRIMARY KEY (`Seq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- システムプロパティ設定

-- スキップ対象者リスト作成対象年数
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg
) VALUES( '[DEFAULT]','systeminfo', 'targetyear', '5', 'スキップ対象者リスト作成対象年数', NOW(), 9, NOW(), 9, '1');

-- 社内与信全スキップフラグ
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg
) VALUES( '[DEFAULT]','systeminfo', 'skipallflg', '0', '社内与信全スキップフラグ', NOW(), 9, NOW(), 9, '1');

-- 全スキップ対象加盟店
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg
) VALUES( '[DEFAULT]','systeminfo', 'SkipAllEnterprise', '7066', '全スキップ対象加盟店', NOW(), 9, NOW(), 9, '1');

-- スキップ対象者リスト利用年数
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg
) VALUES( '[DEFAULT]','systeminfo', 'useyear', '2', 'スキップ対象者リスト利用年数', NOW(), 9, NOW(), 9, '1');

-- ホワイトリストスキップ実施フラグ
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg
) VALUES( '[DEFAULT]','systeminfo', 'ExecSkipFlg', '1', 'ホワイトリストスキップ実施フラグ', NOW(), 9, NOW(), 9, '1');

-- ホワイトリストスキップ対象加盟店
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg
) VALUES( '[DEFAULT]','systeminfo', 'SkipTargetEnterprise', '7066', 'ホワイトリストスキップ対象加盟店', NOW(), 9, NOW(), 9, '1');






-- 社内与信条件（住所）
CREATE TABLE `T_CreditConditionAddress` (
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
  KEY `Idx_T_CreditConditionAddress01` (`Cstring`(255)),
  KEY `Idx_T_CreditConditionAddress02` (`Category`),
  KEY `IDX_T_CreditConditionAddress03` (`RegCstring`(255)),
  KEY `IDX_T_CreditConditionAddress04` (`RegCstringHash`),
  KEY `IDX_T_CreditConditionAddress05` (`ComboHash`),
  KEY `Idx_T_CreditConditionAddress06` (`Class`),
  KEY `Idx_T_CreditConditionAddress07` (`ValidFlg`),
  KEY `Idx_T_CreditConditionAddress08` (`Category`,`ValidFlg`,`OrderSeq`),
  KEY `Idx_T_CreditConditionAddress09` (`RegCstring`(255),`Category`,`Class`,`ValidFlg`),
  KEY `Idx_T_CreditConditionAddress10` (`EnterpriseId`),
  KEY `Idx_T_CreditConditionAddress11` (`UpdateDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 社内与信条件（ドメイン）
CREATE TABLE `T_CreditConditionDomain` (
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
  KEY `Idx_T_CreditConditionDomain01` (`Cstring`(255)),
  KEY `Idx_T_CreditConditionDomain02` (`Category`),
  KEY `IDX_T_CreditConditionDomain03` (`RegCstring`(255)),
  KEY `IDX_T_CreditConditionDomain04` (`RegCstringHash`),
  KEY `IDX_T_CreditConditionDomain05` (`ComboHash`),
  KEY `Idx_T_CreditConditionDomain06` (`Class`),
  KEY `Idx_T_CreditConditionDomain07` (`ValidFlg`),
  KEY `Idx_T_CreditConditionDomain08` (`Category`,`ValidFlg`,`OrderSeq`),
  KEY `Idx_T_CreditConditionDomain09` (`RegCstring`(255),`Category`,`Class`,`ValidFlg`),
  KEY `Idx_T_CreditConditionDomain10` (`EnterpriseId`),
  KEY `Idx_T_CreditConditionDomain11` (`UpdateDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 社内与信条件（加盟店）
CREATE TABLE `T_CreditConditionEnterprise` (
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
  KEY `Idx_T_CreditConditionEnterprise01` (`Cstring`(255)),
  KEY `Idx_T_CreditConditionEnterprise02` (`Category`),
  KEY `IDX_T_CreditConditionEnterprise03` (`RegCstring`(255)),
  KEY `IDX_T_CreditConditionEnterprise04` (`RegCstringHash`),
  KEY `IDX_T_CreditConditionEnterprise05` (`ComboHash`),
  KEY `Idx_T_CreditConditionEnterprise06` (`Class`),
  KEY `Idx_T_CreditConditionEnterprise07` (`ValidFlg`),
  KEY `Idx_T_CreditConditionEnterprise08` (`Category`,`ValidFlg`,`OrderSeq`),
  KEY `Idx_T_CreditConditionEnterprise09` (`RegCstring`(255),`Category`,`Class`,`ValidFlg`),
  KEY `Idx_T_CreditConditionEnterprise10` (`EnterpriseId`),
  KEY `Idx_T_CreditConditionEnterprise11` (`UpdateDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 社内与信条件（商品）
CREATE TABLE `T_CreditConditionItem` (
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
  KEY `Idx_T_CreditConditionItem01` (`Cstring`(255)),
  KEY `Idx_T_CreditConditionItem02` (`Category`),
  KEY `IDX_T_CreditConditionItem03` (`RegCstring`(255)),
  KEY `IDX_T_CreditConditionItem04` (`RegCstringHash`),
  KEY `IDX_T_CreditConditionItem05` (`ComboHash`),
  KEY `Idx_T_CreditConditionItem06` (`Class`),
  KEY `Idx_T_CreditConditionItem07` (`ValidFlg`),
  KEY `Idx_T_CreditConditionItem08` (`Category`,`ValidFlg`,`OrderSeq`),
  KEY `Idx_T_CreditConditionItem09` (`RegCstring`(255),`Category`,`Class`,`ValidFlg`),
  KEY `Idx_T_CreditConditionItem10` (`EnterpriseId`),
  KEY `Idx_T_CreditConditionItem11` (`UpdateDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 社内与信条件（氏名）
CREATE TABLE `T_CreditConditionName` (
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
  KEY `Idx_T_CreditConditionName01` (`Cstring`(255)),
  KEY `Idx_T_CreditConditionName02` (`Category`),
  KEY `IDX_T_CreditConditionName03` (`RegCstring`(255)),
  KEY `IDX_T_CreditConditionName04` (`RegCstringHash`),
  KEY `IDX_T_CreditConditionName05` (`ComboHash`),
  KEY `Idx_T_CreditConditionName06` (`Class`),
  KEY `Idx_T_CreditConditionName07` (`ValidFlg`),
  KEY `Idx_T_CreditConditionName08` (`Category`,`ValidFlg`,`OrderSeq`),
  KEY `Idx_T_CreditConditionName09` (`RegCstring`(255),`Category`,`Class`,`ValidFlg`),
  KEY `Idx_T_CreditConditionName10` (`EnterpriseId`),
  KEY `Idx_T_CreditConditionName11` (`UpdateDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 社内与信条件（電話番号）
CREATE TABLE `T_CreditConditionPhone` (
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
  KEY `Idx_T_CreditConditionPhone01` (`Cstring`(255)),
  KEY `Idx_T_CreditConditionPhone02` (`Category`),
  KEY `IDX_T_CreditConditionPhone03` (`RegCstring`(255)),
  KEY `IDX_T_CreditConditionPhone04` (`RegCstringHash`),
  KEY `IDX_T_CreditConditionPhone05` (`ComboHash`),
  KEY `Idx_T_CreditConditionPhone06` (`Class`),
  KEY `Idx_T_CreditConditionPhone07` (`ValidFlg`),
  KEY `Idx_T_CreditConditionPhone08` (`Category`,`ValidFlg`,`OrderSeq`),
  KEY `Idx_T_CreditConditionPhone09` (`RegCstring`(255),`Category`,`Class`,`ValidFlg`),
  KEY `Idx_T_CreditConditionPhone10` (`EnterpriseId`),
  KEY `Idx_T_CreditConditionPhone11` (`UpdateDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 社内与信条件（住所）登録
insert into T_CreditConditionAddress
select *
from T_CreditCondition as b 
where b.OrderSeq ='-1' and b.Category = '1';


-- 社内与信条件（ドメイン）登録
insert into T_CreditConditionDomain
select *
from T_CreditCondition as b 
where b.OrderSeq ='-1' and b.Category = '4';


-- 社内与信条件（加盟店）登録
insert into T_CreditConditionEnterprise
select *
from T_CreditCondition as b 
where b.OrderSeq ='-1' and b.Category = '5';


-- 社内与信条件（商品）登録
insert into T_CreditConditionItem
select *
from T_CreditCondition as b 
where b.OrderSeq ='-1' and b.Category = '3';


-- 社内与信条件（氏名）登録
insert into T_CreditConditionName
select *
from T_CreditCondition as b 
where b.OrderSeq ='-1' and b.Category = '2';


-- 社内与信条件（電話番号）登録
insert into T_CreditConditionPhone
select *
from T_CreditCondition as b 
where b.OrderSeq ='-1' and b.Category = '8';


-- 社内与信条件削除
delete from T_CreditCondition where  Category in ('1','2','3','4','5','8') and OrderSeq = -1;

