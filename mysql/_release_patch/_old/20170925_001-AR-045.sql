/* 通知内容管理の登録 */
DROP TABLE IF EXISTS `T_NotificationManage`;
CREATE TABLE `T_NotificationManage` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `Token` varchar(30) NOT NULL,
  `ReceivedData` mediumtext,
  `ReceivedData2` longtext,
  PRIMARY KEY (`Seq`),
  UNIQUE KEY `Idx_T_NotificationManage01` (`Token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* コードマスターにAPIリダイレクトを追加 */
INSERT INTO M_CodeManagement VALUES(194, 'APIリダイレクト設定', NULL, NULL, 1, '有効フラグ(0:無効／1:有効)', 0, NULL, 0, NULL, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(194, 1, 'リダイレクト先(Eストアー)', '0', NULL, NULL, NULL, 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(194, 2, 'リダイレクト先(SMBC)'     , '1', NULL, NULL, 'https://www.ato-barai.jp/smbcfs/api', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(194, 3, 'リダイレクト先(セイノー)' , '1', NULL, NULL, 'https://atobarai.seino.co.jp/seino-financial/api', 0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(194, 4, 'リダイレクト先(BASE)'     , '0', NULL, NULL, NULL, 0, NOW(), 1, NOW(), 1, 1);
