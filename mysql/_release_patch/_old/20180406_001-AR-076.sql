/* サイトマスタの変更 */
ALTER TABLE `T_Site` ADD COLUMN `SelfBillingFixFlg` TINYINT(4) NOT NULL DEFAULT 0 AFTER `SelfBillingFlg`;