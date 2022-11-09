/* ‰ïŒvŒŸˆ—‚‘¬‰»‘Î‰ */
ALTER TABLE `AT_DailyStatisticsTable` ADD INDEX `Idx_AT_DailyStatisticsTable01` (`AccountDate` ASC, `EnterpriseId` ASC);
ALTER TABLE `AT_Oem_DailyStatisticsTable` ADD INDEX `Idx_AT_Oem_DailyStatisticsTable01` (`AccountDate` ASC, `EnterpriseId` ASC);
