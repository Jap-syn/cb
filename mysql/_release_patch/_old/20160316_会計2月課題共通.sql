/* 立替・売上管理_会計へ[売上確定条件][売上確定日付]を追加 */
ALTER TABLE `AT_PayingAndSales` 
ADD COLUMN `ATUriType` TINYINT(4) NULL DEFAULT 99 AFTER `Deli_ConfirmArrivalInputDate`,
ADD COLUMN `ATUriDay` VARCHAR(8) NULL DEFAULT '99999999' AFTER `ATUriType`;

/* 立替・売上管理_会計[売上確定条件][売上確定日付]へインデックス付与 */
ALTER TABLE `AT_PayingAndSales` 
ADD INDEX `Idx_AT_PayingAndSales02` (`ATUriType` ASC),
ADD INDEX `Idx_AT_PayingAndSales03` (`ATUriDay` ASC);
