/* OEM仮締め処理の高速化対応 */
ALTER TABLE `T_OemSettlementFee` ADD INDEX `Idx_T_OemSettlementFee07` (`AddUpFlg` ASC, `OemId` ASC);
ALTER TABLE `T_OemClaimFee` ADD INDEX `Idx_T_OemClaimFee07` (`AddUpFlg` ASC, `OemId` ASC);
