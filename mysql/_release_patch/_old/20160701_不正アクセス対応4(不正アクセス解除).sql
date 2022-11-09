/* マイページ側スキーマを本体スキーマのビューへ */
DROP VIEW IF EXISTS `MPV_MypageCustomer`;
CREATE VIEW `MPV_MypageCustomer` AS SELECT * FROM coraldb_mypage01.T_MypageCustomer;

DROP VIEW IF EXISTS `MPV_NgAccessMypageOrder`;
CREATE VIEW `MPV_NgAccessMypageOrder` AS SELECT * FROM coraldb_mypage01.T_NgAccessMypageOrder;

DROP VIEW IF EXISTS `MPV_NgAccessIp`;
CREATE VIEW `MPV_NgAccessIp` AS SELECT * FROM coraldb_mypage01.T_NgAccessIp;

/* T_NgAccessClear登録 */
DROP TABLE IF EXISTS `T_NgAccessClear`;
CREATE TABLE `T_NgAccessClear` (
  `Seq` bigint(20) NOT NULL AUTO_INCREMENT,
  `LoginId` varchar(40) DEFAULT NULL,
  `Type` tinyint(4) DEFAULT NULL,
  `Status` tinyint(4) NOT NULL DEFAULT '1',
  `ServerStatus` varchar(20) DEFAULT NULL,
  `IndicateDate` datetime DEFAULT NULL,
  PRIMARY KEY (`Seq`),
  UNIQUE KEY `Idx_T_NgAccessClear01` (`LoginId`, `Type`),
  INDEX `Idx_T_NgAccessClear02` (`Status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
