/* 代理店手数料管理 */
ALTER TABLE `T_AgencyFee`
ADD COLUMN `CancelAddUpFlg` INT NOT NULL DEFAULT 0 AFTER `AddUpFlg`;

/* OEM代理店手数料管理 */
ALTER TABLE `T_OemAgencyFee`
ADD COLUMN `CancelAddUpFlg` INT NOT NULL DEFAULT 0 AFTER `AddUpFlg`;