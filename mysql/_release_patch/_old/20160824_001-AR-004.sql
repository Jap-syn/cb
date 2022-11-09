-- ジンテック結果テーブル
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

-- ジンテック結果詳細テーブル
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

-- サイトテーブルにジンテック手動与信強制カラム追加
ALTER TABLE `T_Site` 
ADD COLUMN `JintecManualReqFlg` TINYINT NOT NULL DEFAULT 0 AFTER `MultiOrderScore`;

-- 社内与信条件にジンテック手動与信強制カラム追加(約900秒)
ALTER TABLE `T_CreditCondition` 
ADD COLUMN `JintecManualReqFlg` TINYINT NOT NULL DEFAULT 0 AFTER `CreditCriterionId`;


-- ｺｰﾄﾞﾏｽﾀｰにデータ登録
INSERT INTO M_CodeManagement VALUES(187 ,'ジンテック結果詳細(注意事項)' ,NULL ,'注意事項ID' ,1 ,NULL ,0,NULL,0,NULL, NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(187,1 ,'該当データなし',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(187,2 ,'無効',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(187,3 ,'都合停止あり',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(187,4 ,'直近加入（3ヶ月以下）',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(187,5 ,'直近加入（4ヶ月以上6ヶ月以下）',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(187,6 ,'変更過多',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(187,7 ,'反復利用',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(187,8 ,'長期利用',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(187,9 ,'ＭＳＧなし',NULL ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
