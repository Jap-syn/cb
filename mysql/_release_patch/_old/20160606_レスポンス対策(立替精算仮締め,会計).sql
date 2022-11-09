/* âÔåvä÷òAçÇë¨âªëŒâû */
ALTER TABLE `AT_Daily2StatisticsTable` ADD INDEX `Idx_AT_Daily2StatisticsTable01` (`ProcessingDate` ASC);
ALTER TABLE `AT_Daily2StatisticsTable` ADD INDEX `Idx_AT_Daily2StatisticsTable02` (`AccountDate` ASC);
ALTER TABLE `AT_DailyStatisticsTable` ADD INDEX `Idx_AT_DailyStatisticsTable02` (`ProcessingDate` ASC);
ALTER TABLE `AT_Oem_Daily2StatisticsTable` ADD INDEX `Idx_AT_Oem_Daily2StatisticsTable01` (`ProcessingDate` ASC);
ALTER TABLE `AT_Oem_Daily2StatisticsTable` ADD INDEX `Idx_AT_Oem_Daily2StatisticsTable02` (`AccountDate` ASC);
ALTER TABLE `AT_Oem_DailyStatisticsTable` ADD INDEX `Idx_AT_Oem_DailyStatisticsTable02` (`ProcessingDate` ASC);
ALTER TABLE `AT_Daily_SalesDetails` ADD INDEX `Idx_AT_Daily_SalesDetails01` (`ProcessingDate` ASC);
ALTER TABLE `AT_Daily_SalesDetails` ADD INDEX `Idx_AT_Daily_SalesDetails02` (`AccountDate` ASC);
ALTER TABLE `AT_ReissueFeeSpecification` ADD INDEX `Idx_AT_ReissueFeeSpecification01` (`ProcessingDate` ASC);
ALTER TABLE `AT_ReissueFeeSpecification` ADD INDEX `Idx_AT_ReissueFeeSpecification02` (`AccountDate` ASC);
ALTER TABLE `AT_PayOff_DailyAccount` ADD INDEX `Idx_AT_PayOff_DailyAccount01` (`ProcessingDate` ASC);
ALTER TABLE `T_Order` ADD INDEX `Idx_T_Order23` (`Dmg_DecisionFlg` ASC);

/* óßë÷ê∏éZâºí˜Çﬂ(LogicChargeDecision.payTempFixed)çÇë¨âªëŒâû */
ALTER TABLE `T_Cancel` ADD INDEX `Idx_T_Cancel08` (`ApproveFlg` ASC, `KeepAnAccurateFlg` ASC);
ALTER TABLE `T_StampFee` ADD INDEX `Idx_T_StampFee02` (`ClearFlg` ASC);
ALTER TABLE `T_StampFee` ADD INDEX `Idx_T_StampFee03` (`PayingControlSeq` ASC);
