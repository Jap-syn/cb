--------------------------------------------------------------------------------
-- 以下、マイページ側スキーマへ登録
--------------------------------------------------------------------------------
-- 注文マイページログイン情報テーブル作成
DROP TABLE IF EXISTS `T_MypageOrderLogin`;
CREATE TABLE `T_MypageOrderLogin` (
  `Seq` bigint(20) NOT NULL,
  `LastLoginDate` datetime NOT NULL,
  PRIMARY KEY (`Seq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



--------------------------------------------------------------------------------
-- 以下、本体側スキーマへ登録
--------------------------------------------------------------------------------
DROP VIEW IF EXISTS `MPV_MypageOrderLogin`;
CREATE VIEW `MPV_MypageOrderLogin` AS SELECT * FROM coraldb_mypage01.T_MypageOrderLogin;
