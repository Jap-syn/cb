ALTER TABLE `T_OrderSummary` DROP INDEX `Idx_T_OrderSummary14`;
ALTER TABLE `T_OrderSummary` ADD INDEX `Idx_T_OrderSummary14` (`RegDestUnitingAddress`(255) ASC);
