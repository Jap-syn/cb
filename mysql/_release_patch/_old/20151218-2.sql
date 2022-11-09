ALTER TABLE `AT_Order` ADD INDEX `Idx_AT_Order01` (`Dmg_DailySummaryFlg` ASC);
ALTER TABLE `T_Order` ADD INDEX `Idx_T_Order22` (`OemClaimTransDate` ASC, `OemClaimTransFlg` ASC);
ALTER TABLE `T_Cancel` ADD INDEX `Idx_T_Cancel07` (`DailySummaryFlg` ASC);
ALTER TABLE `AT_PayingAndSales` ADD INDEX `Idx_AT_PayingAndSales01` (`DailySummaryFlg` ASC);
ALTER TABLE `AT_RepaymentControl` ADD INDEX `Idx_AT_RepaymentControl01` (`DailySummaryFlg` ASC);
ALTER TABLE `T_ReceiptControl` ADD INDEX `Idx_T_ReceiptControl03` (`DailySummaryFlg` ASC);
ALTER TABLE `T_SundryControl` ADD INDEX `Idx_T_SundryControl03` (`DailySummaryFlg` ASC);
ALTER TABLE `AT_EnterpriseReceiptHistory` ADD INDEX `Idx_AT_EnterpriseReceiptHistory01` (`DailySummaryFlg` ASC);
ALTER TABLE `AT_AdjustmentAmount` ADD INDEX `Idx_AT_AdjustmentAmount01` (`DailySummaryFlg` ASC);
ALTER TABLE `AT_PayingControl` ADD INDEX `Idx_AT_PayingControl01` (`DailySummaryFlg` ASC);
ALTER TABLE `AT_OemClaimed` ADD INDEX `Idx_AT_OemClaimed01` (`DailySummaryFlg` ASC);
ALTER TABLE `AT_EnterpriseMonthlyClosingInfo` ADD INDEX `Idx_AT_EnterpriseMonthlyClosingInfo01` (`DailySummaryFlg` ASC);
