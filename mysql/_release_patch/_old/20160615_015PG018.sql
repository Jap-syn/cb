/* 与信スレッドNo(加盟店) */
ALTER TABLE `T_Enterprise` ADD COLUMN `CreditThreadNo` TINYINT(4) NOT NULL DEFAULT 0 AFTER `DetailApiOrderStatusClass`;

/* 社内与信プロセス排他制御 */
CREATE TABLE `T_CreditJudgeLock` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `CreditThreadNo` tinyint(4) NOT NULL DEFAULT '0',
  `CreditThreadName` varchar(100) DEFAULT NULL,
  `CreditJudgeLock` bigint(20) NOT NULL DEFAULT '0',
  `UpdateDate` datetime DEFAULT NULL,
  PRIMARY KEY (`Seq`),
  UNIQUE KEY `CreditThreadNo_UNIQUE` (`CreditThreadNo`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO T_CreditJudgeLock (CreditThreadNo, CreditThreadName, CreditJudgeLock, UpdateDate) VALUES (0, '与信スレッドNo.1(標準)', 0, NOW());
INSERT INTO T_CreditJudgeLock (CreditThreadNo, CreditThreadName, CreditJudgeLock, UpdateDate) VALUES (1, '与信スレッドNo.2', 0, NOW());
INSERT INTO T_CreditJudgeLock (CreditThreadNo, CreditThreadName, CreditJudgeLock, UpdateDate) VALUES (2, '与信スレッドNo.3', 0, NOW());


-- 5077 シエル、5143 マキシムはｽﾚｯﾄﾞNo2を使用
UPDATE T_Enterprise SET CreditThreadNo = 1 WHERE EnterpriseId IN (5077, 5143);

-- 15898 16010 16011 アルテミスはｽﾚｯﾄﾞNo3を使用
UPDATE T_Enterprise SET CreditThreadNo = 2 WHERE EnterpriseId IN (15898,16010,16011);
