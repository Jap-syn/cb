-- ==========================================================================
--     基幹側に反映
-- ==========================================================================

-- システムプロパティ設定
-- パスワード期限切れ日数
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg
) VALUES( '[DEFAULT]','systeminfo', 'PasswdLimitDay', '90', 'パスワード期限切れ日数(日)', NOW(), 9, NOW(), 9, '1');

-- パスワード期限警告日数
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg
) VALUES( '[DEFAULT]','systeminfo', 'PasswdLimitAlertDay', '20', 'パスワード期限警告日数(日)', NOW(), 9, NOW(), 9, '1');

-- 過去パスワード使用回数
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg
) VALUES( '[DEFAULT]','systeminfo', 'UsePasswdTimes', '4', '過去パスワード使用回数(回)', NOW(), 9, NOW(), 9, '1');

-- パスワード文字数
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg
) VALUES( '[DEFAULT]','systeminfo', 'PasswdCount', '7', 'パスワード文字数(桁)', NOW(), 9, NOW(), 9, '1');



-- パスワード履歴テーブル 新規作成
<<<<<<< HEAD
DROP TABLE IF EXISTS `T_PasswordHistory`;
=======
>>>>>>> 201803(pcidss)
CREATE TABLE `T_PasswordHistory` (
`Seq` bigint(20) NOT NULL AUTO_INCREMENT,
`Category` int(11) DEFAULT NULL,
`LoginId` varchar(20) DEFAULT NULL,
`LoginPasswd` varchar(100) DEFAULT NULL,
`PasswdStartDay` date DEFAULT NULL,
`PasswdLimitDay` date DEFAULT NULL,
`Hashed` int(11) NOT NULL DEFAULT '0',
`RegistDate` datetime DEFAULT NULL,
`RegistId` int(11) DEFAULT NULL,
`UpdateDate` datetime DEFAULT NULL,
`UpdateId` int(11) DEFAULT NULL,
`ValidFlg` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`Seq`),
  KEY `Idx_T_PasswordHistory01` (`Category` ASC, `LoginId` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- パスワード履歴テーブル 初期データ移行
-- オペレーター
INSERT INTO `T_PasswordHistory` (`Category`, `LoginId`, `LoginPasswd`, `PasswdStartDay`, `PasswdLimitDay`, `Hashed`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`)
SELECT 1, LoginId, LoginPasswd, DATE(NOW()), DATE_ADD(DATE(NOW()), INTERVAL 90 DAY), Hashed, NOW(), 9, NOW(), 9, 1  FROM T_Operator;

-- 加盟店オペレーター（直営）
INSERT INTO `T_PasswordHistory` (`Category`, `LoginId`, `LoginPasswd`, `PasswdStartDay`, `PasswdLimitDay`, `Hashed`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`)
SELECT 2, eo.LoginId, eo.LoginPasswd, DATE(NOW()), DATE_ADD(DATE(NOW()), INTERVAL 90 DAY), eo.Hashed, NOW(), 9, NOW(), 9, 1 FROM T_EnterpriseOperator eo INNER JOIN T_Enterprise e on (e.EnterpriseId = eo.EnterpriseId) WHERE IFNULL(e.OemId, 0) = 0;

-- 加盟店オペレーター（OEM）
INSERT INTO `T_PasswordHistory` (`Category`, `LoginId`, `LoginPasswd`, `PasswdStartDay`, `PasswdLimitDay`, `Hashed`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`)
SELECT 3, eo.LoginId, eo.LoginPasswd, DATE(NOW()), DATE_ADD(DATE(NOW()), INTERVAL 90 DAY), eo.Hashed, NOW(), 9, NOW(), 9, 1 FROM T_EnterpriseOperator eo INNER JOIN T_Enterprise e on (e.EnterpriseId = eo.EnterpriseId) WHERE IFNULL(e.OemId, 0) > 0;

-- OEMオペレーター
INSERT INTO `T_PasswordHistory` (`Category`, `LoginId`, `LoginPasswd`, `PasswdStartDay`, `PasswdLimitDay`, `Hashed`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`)
SELECT 4, LoginId, LoginPasswd, DATE(NOW()), DATE_ADD(DATE(NOW()), INTERVAL 90 DAY), Hashed, NOW(), 9, NOW(), 9, 1  FROM T_OemOperator;

-- 加盟店（直営）
INSERT INTO `T_PasswordHistory` (`Category`, `LoginId`, `LoginPasswd`, `PasswdStartDay`, `PasswdLimitDay`, `Hashed`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`)
SELECT 2, LoginId, LoginPasswd, DATE(NOW()), DATE_ADD(DATE(NOW()), INTERVAL 90 DAY), Hashed, NOW(), 9, NOW(), 9, 1  FROM T_Enterprise WHERE IFNULL(OemId, 0) = 0;

-- 加盟店（OEM）
INSERT INTO `T_PasswordHistory` (`Category`, `LoginId`, `LoginPasswd`, `PasswdStartDay`, `PasswdLimitDay`, `Hashed`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`)
SELECT 3, LoginId, LoginPasswd, DATE(NOW()), DATE_ADD(DATE(NOW()), INTERVAL 90 DAY), Hashed, NOW(), 9, NOW(), 9, 1  FROM T_Enterprise WHERE IFNULL(OemId, 0) > 0;




-- ==========================================================================
--     マイページ側に反映
-- ==========================================================================

-- マイページパスワード履歴テーブル 新規作成
<<<<<<< HEAD
DROP TABLE IF EXISTS `T_MypagePasswordHistory`;
=======
>>>>>>> 201803(pcidss)
CREATE TABLE `T_MypagePasswordHistory` (
`Seq` bigint(20) NOT NULL AUTO_INCREMENT,
`Category` int(11) DEFAULT NULL,
`LoginId` varchar(255) DEFAULT NULL,
`LoginPasswd` varchar(100) DEFAULT NULL,
`PasswdStartDay` date DEFAULT NULL,
`PasswdLimitDay` date DEFAULT NULL,
`Hashed` int(11) NOT NULL DEFAULT '0',
`RegistDate` datetime DEFAULT NULL,
`UpdateDate` datetime DEFAULT NULL,
`ValidFlg` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`Seq`),
  KEY `Idx_T_MypagePasswordHistory01` (`Category` ASC, `LoginId` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



-- マイページパスワード履歴テーブル 初期データ移行
-- マイページ顧客（直営）
INSERT INTO `T_MypagePasswordHistory` (`Category`, `LoginId`, `LoginPasswd`, `PasswdStartDay`, `PasswdLimitDay`, `Hashed`, `RegistDate`, `UpdateDate`, `ValidFlg`)
SELECT 5, LoginId, LoginPasswd, DATE(NOW()), DATE_ADD(DATE(NOW()), INTERVAL 90 DAY), Hashed, NOW(), NOW(), 1  FROM T_MypageCustomer WHERE IFNULL(OemId, 0) = 0;

-- マイページ顧客（OEM）
INSERT INTO `T_MypagePasswordHistory` (`Category`, `LoginId`, `LoginPasswd`, `PasswdStartDay`, `PasswdLimitDay`, `Hashed`, `RegistDate`, `UpdateDate`, `ValidFlg`)
SELECT 6, LoginId, LoginPasswd, DATE(NOW()), DATE_ADD(DATE(NOW()), INTERVAL 90 DAY), Hashed, NOW(), NOW(), 1  FROM T_MypageCustomer WHERE IFNULL(OemId, 0) > 0;



