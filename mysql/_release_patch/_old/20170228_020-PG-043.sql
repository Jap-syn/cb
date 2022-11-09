-- サイトテーブルに「その他」着荷を自動で取る／「その他」着荷を自動で取る指定文字、カラム追加
ALTER TABLE `T_Site` 
ADD COLUMN `EtcAutoArrivalFlg` TINYINT NOT NULL DEFAULT 0 AFTER `Ent_OrderIdcheck`,
ADD COLUMN `EtcAutoArrivalNumber` VARCHAR(255) NULL AFTER `EtcAutoArrivalFlg`;

-- サイトテーブルに「その他」着荷を自動で取るにインデックス付与
ALTER TABLE `T_Site` 
ADD INDEX `Idx_T_Site02` (`EtcAutoArrivalFlg` ASC);
