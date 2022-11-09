/* �^�M�X���b�hNo(�����X) */
ALTER TABLE `T_Enterprise` ADD COLUMN `CreditThreadNo` TINYINT(4) NOT NULL DEFAULT 0 AFTER `DetailApiOrderStatusClass`;

/* �Г��^�M�v���Z�X�r������ */
CREATE TABLE `T_CreditJudgeLock` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `CreditThreadNo` tinyint(4) NOT NULL DEFAULT '0',
  `CreditThreadName` varchar(100) DEFAULT NULL,
  `CreditJudgeLock` bigint(20) NOT NULL DEFAULT '0',
  `UpdateDate` datetime DEFAULT NULL,
  PRIMARY KEY (`Seq`),
  UNIQUE KEY `CreditThreadNo_UNIQUE` (`CreditThreadNo`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO T_CreditJudgeLock (CreditThreadNo, CreditThreadName, CreditJudgeLock, UpdateDate) VALUES (0, '�^�M�X���b�hNo.1(�W��)', 0, NOW());
INSERT INTO T_CreditJudgeLock (CreditThreadNo, CreditThreadName, CreditJudgeLock, UpdateDate) VALUES (1, '�^�M�X���b�hNo.2', 0, NOW());
INSERT INTO T_CreditJudgeLock (CreditThreadNo, CreditThreadName, CreditJudgeLock, UpdateDate) VALUES (2, '�^�M�X���b�hNo.3', 0, NOW());


-- 5077 �V�G���A5143 �}�L�V���ͽگ��No2���g�p
UPDATE T_Enterprise SET CreditThreadNo = 1 WHERE EnterpriseId IN (5077, 5143);

-- 15898 16010 16011 �A���e�~�X�ͽگ��No3���g�p
UPDATE T_Enterprise SET CreditThreadNo = 2 WHERE EnterpriseId IN (15898,16010,16011);
