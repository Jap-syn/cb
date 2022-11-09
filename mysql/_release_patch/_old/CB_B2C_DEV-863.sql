-- OEM口振紙初回登録手数料（税抜）, OEM口振WEB初回登録手数料（税抜）, OEM口振引落手数料（税抜）--
ALTER TABLE `T_Site` 
ADD COLUMN `OemFirstCreditTransferClaimFee` INT(11) DEFAULT NULL AFTER `RegistId`,
ADD COLUMN `OemFirstCreditTransferClaimFeeWeb` INT(11) DEFAULT NULL AFTER `RegistId`,
ADD COLUMN `OemCreditTransferClaimFee` INT(11) DEFAULT NULL AFTER `RegistId`;

ALTER TABLE `T_Oem` 
ADD COLUMN `FirstCreditTransferClaimFeeOem` INT(11) DEFAULT NULL AFTER `RegistId`,
ADD COLUMN `FirstCreditTransferClaimFeeWebOem` INT(11) DEFAULT NULL AFTER `RegistId`,
ADD COLUMN `CreditTransferClaimFeeOem` INT(11) DEFAULT NULL AFTER `RegistId`;