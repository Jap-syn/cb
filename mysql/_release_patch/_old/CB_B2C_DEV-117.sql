ALTER TABLE T_Enterprise ADD COLUMN `AppFormIssueCond` TINYINT NOT NULL DEFAULT 0 AFTER `IluCooperationFlg`;
ALTER TABLE T_Enterprise ADD COLUMN `ForceCancelDatePrintFlg` TINYINT NOT NULL DEFAULT 0 AFTER `AppFormIssueCond`;
ALTER TABLE T_Enterprise ADD COLUMN `ForceCancelClaimPattern` INT  NULL AFTER `ForceCancelDatePrintFlg`;
ALTER TABLE T_EnterpriseCustomer ADD COLUMN `RequestCompDate` DATE  NULL AFTER `RequestCompScheduleDate`;
