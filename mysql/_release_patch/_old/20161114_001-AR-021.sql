-- カラム[加盟店ID]追加
ALTER TABLE `T_CreditCondition` 
ADD COLUMN `EnterpriseId` BIGINT(20) NULL AFTER `JintecManualReqFlg`;

-- [加盟店ID]へインデックス付与
ALTER TABLE `T_CreditCondition` 
ADD INDEX `Idx_T_CreditCondition10` (`EnterpriseId` ASC);
