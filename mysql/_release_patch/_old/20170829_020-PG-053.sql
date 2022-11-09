/* 加盟店テーブルのデフォルト値変更 */
ALTER TABLE `T_Enterprise` 
CHANGE COLUMN `HoldBoxFlg` `HoldBoxFlg` TINYINT(4) NOT NULL DEFAULT 0 ;

