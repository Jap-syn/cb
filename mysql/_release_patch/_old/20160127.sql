-- 入金管理_会計（入金取消の対応分）
ALTER TABLE AT_ReceiptControl ADD COLUMN `Rct_CancelFlg` INT NOT NULL DEFAULT 0 AFTER `BankFlg`;
ALTER TABLE AT_ReceiptControl ADD COLUMN `Before_ClearConditionForCharge` INT  NULL AFTER `Rct_CancelFlg`;
ALTER TABLE AT_ReceiptControl ADD COLUMN `Before_ClearConditionDate` DATE  NULL AFTER `Before_ClearConditionForCharge`;
ALTER TABLE AT_ReceiptControl ADD COLUMN `Before_Cnl_Status` INT  NULL AFTER `Before_ClearConditionDate`;
ALTER TABLE AT_ReceiptControl ADD COLUMN `Before_Deli_ConfirmArrivalFlg` INT  NULL AFTER `Before_Cnl_Status`;

-- 未収金日計
ALTER TABLE AW_AccountsDue_DailyAccount ADD COLUMN `PaymentNumber` BIGINT  NULL AFTER `PaymentTargetAccountTitle`;
ALTER TABLE AT_AccountsDue_DailyAccountDetails ADD COLUMN `PaymentNumber` BIGINT  NULL AFTER `PaymentTargetAccountTitle`;

-- 仮払金日計
ALTER TABLE AW_SuspensePayments_DailyAccount ADD COLUMN `PaymentNumber` BIGINT  NULL AFTER `PaymentTargetAccountTitle`;
ALTER TABLE AT_SuspensePayments_DailyAccountDetails ADD COLUMN `PaymentNumber` BIGINT  NULL AFTER `PaymentTargetAccountTitle`;

-- ｺｰﾄﾞﾏｽﾀ
INSERT INTO M_Code VALUES(162,9 ,'加盟店未払金','加盟店未払金' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(161,8 ,'返金（OEM仮払金）','返金（OEM仮払金）' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(161,9 ,'OEM未払金','OEM未払金' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
