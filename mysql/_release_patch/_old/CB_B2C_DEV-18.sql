-- カラム拡張
ALTER TABLE T_Enterprise ADD COLUMN `AutoCancelThreadNo` TINYINT NOT NULL DEFAULT 0 AFTER `IluCooperationFlg`;
ALTER TABLE T_Enterprise ADD COLUMN `CreditJudgeValidDays` INT NOT NULL DEFAULT 30 AFTER `AutoCancelThreadNo`;
