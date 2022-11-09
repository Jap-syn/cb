CREATE TABLE `M_CreditSystemInfo` (
  `AutoCreditLimitAmount1` TINYINT NOT NULL DEFAULT 1,
  `AutoCreditLimitAmount2` TINYINT NOT NULL DEFAULT 1,
  `AutoCreditLimitAmount3` TINYINT NOT NULL DEFAULT 1,
  `AutoCreditLimitAmount4` TINYINT NOT NULL DEFAULT 1,
  `ClaimPastDays` INT NOT NULL DEFAULT 0,
  `DeliveryPastDays` INT NOT NULL DEFAULT 0,
  `JintecManualJudgeSns` TEXT NULL);

INSERT INTO M_CreditSystemInfo VALUES( 1, 1, 1, 1, 730, 45, NULL);

ALTER TABLE `T_OrderSummary` 
ADD INDEX `Idx_T_OrderSummary15` (`RegDestNameKj` ASC);
