-- �����Ǘ�_��v�i��������̑Ή����j
ALTER TABLE AT_ReceiptControl ADD COLUMN `Rct_CancelFlg` INT NOT NULL DEFAULT 0 AFTER `BankFlg`;
ALTER TABLE AT_ReceiptControl ADD COLUMN `Before_ClearConditionForCharge` INT  NULL AFTER `Rct_CancelFlg`;
ALTER TABLE AT_ReceiptControl ADD COLUMN `Before_ClearConditionDate` DATE  NULL AFTER `Before_ClearConditionForCharge`;
ALTER TABLE AT_ReceiptControl ADD COLUMN `Before_Cnl_Status` INT  NULL AFTER `Before_ClearConditionDate`;
ALTER TABLE AT_ReceiptControl ADD COLUMN `Before_Deli_ConfirmArrivalFlg` INT  NULL AFTER `Before_Cnl_Status`;

-- ���������v
ALTER TABLE AW_AccountsDue_DailyAccount ADD COLUMN `PaymentNumber` BIGINT  NULL AFTER `PaymentTargetAccountTitle`;
ALTER TABLE AT_AccountsDue_DailyAccountDetails ADD COLUMN `PaymentNumber` BIGINT  NULL AFTER `PaymentTargetAccountTitle`;

-- ���������v
ALTER TABLE AW_SuspensePayments_DailyAccount ADD COLUMN `PaymentNumber` BIGINT  NULL AFTER `PaymentTargetAccountTitle`;
ALTER TABLE AT_SuspensePayments_DailyAccountDetails ADD COLUMN `PaymentNumber` BIGINT  NULL AFTER `PaymentTargetAccountTitle`;

-- ����Ͻ�
INSERT INTO M_Code VALUES(162,9 ,'�����X������','�����X������' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(161,8 ,'�ԋ��iOEM�������j','�ԋ��iOEM�������j' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(161,9 ,'OEM������','OEM������' ,NULL , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
