-- üàÇ_ïviüàæÁÌÎªj
ALTER TABLE AT_ReceiptControl ADD COLUMN `Rct_CancelFlg` INT NOT NULL DEFAULT 0 AFTER `BankFlg`;
ALTER TABLE AT_ReceiptControl ADD COLUMN `Before_ClearConditionForCharge` INT  NULL AFTER `Rct_CancelFlg`;
ALTER TABLE AT_ReceiptControl ADD COLUMN `Before_ClearConditionDate` DATE  NULL AFTER `Before_ClearConditionForCharge`;
ALTER TABLE AT_ReceiptControl ADD COLUMN `Before_Cnl_Status` INT  NULL AFTER `Before_ClearConditionDate`;
ALTER TABLE AT_ReceiptControl ADD COLUMN `Before_Deli_ConfirmArrivalFlg` INT  NULL AFTER `Before_Cnl_Status`;

-- ¢ûàúv
ALTER TABLE AW_AccountsDue_DailyAccount ADD COLUMN `PaymentNumber` BIGINT  NULL AFTER `PaymentTargetAccountTitle`;
ALTER TABLE AT_AccountsDue_DailyAccountDetails ADD COLUMN `PaymentNumber` BIGINT  NULL AFTER `PaymentTargetAccountTitle`;

-- ¼¥àúv
ALTER TABLE AW_SuspensePayments_DailyAccount ADD COLUMN `PaymentNumber` BIGINT  NULL AFTER `PaymentTargetAccountTitle`;
ALTER TABLE AT_SuspensePayments_DailyAccountDetails ADD COLUMN `PaymentNumber` BIGINT  NULL AFTER `PaymentTargetAccountTitle`;

-- º°ÄÞÏ½À
INSERT INTO M_Code VALUES(162,9 ,'Á¿X¢¥à','Á¿X¢¥à' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(161,8 ,'ÔàiOEM¼¥àj','ÔàiOEM¼¥àj' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(161,9 ,'OEM¢¥à','OEM¢¥à' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
