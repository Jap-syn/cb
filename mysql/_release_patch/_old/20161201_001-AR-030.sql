-- AT_PayOff_DailyAccount.EnterpriseIdÖCfbNXðt^
ALTER TABLE `AT_PayOff_DailyAccount` ADD INDEX `Idx_AT_PayOff_DailyAccount02` (`EnterpriseId` ASC);
-- OEM¿ÖJ[x¥®¹ú]ÇÁ
ALTER TABLE `T_OemClaimed` ADD COLUMN `ExecDate` DATE NULL DEFAULT NULL AFTER `N_MonthlyFeeTax`;
-- OEM¿[x¥®¹ú]ÖCfbNXt^
ALTER TABLE `T_OemClaimed` ADD INDEX `Idx_T_OemClaimed04` (`ExecDate` ASC);


-- ev[g}X^[o^
INSERT INTO M_TemplateHeader VALUES( 89 , 'CKI24174_13_O', 0, 0, 0, '¸Zúv(OEMÅ)', 1, ',', '\"' ,'SJIS-win' ,0,'CKI24174', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateField VALUES ( 89 , 1, 'OemId' ,'OEMR[h' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 2, 'OemNameKj' ,'OEM¼' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 3, 'EnterpriseId' ,'Á¿XR[h' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 4, 'EnterpriseNameKj' ,'Á¿X¼' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 5, 'AdvancesDate' ,'OEMÖÌ§Öú' ,'DATE' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 6, 'AdvancesAmount' ,'OEM§ÖÀsàz' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 7, 'EnterpriseAccountsDue' ,'OEM¢ûà' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 8, 'AccountsPayablePending' ,'OEM¢¥àÛ¯' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 9, 'ClaimAndObligationsDecision' ,'Â Â±»è' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 10, 'UseAmount' ,'pzEOEM¢¥à' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 11, 'CancelAmount' ,'LZàz' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 12, 'AccountsPayableTotal' ,'OEM¢¥àv' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 13, 'SettlementFee' ,'OEMÏè¿' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 14, 'ClaimFee' ,'OEM¿è¿' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 15, 'MonthlyFee' ,'OEMzÅèï' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 16, 'AccountsReceivableTotal' ,'OEM|àv' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 17, 'StampFee' ,'óã' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 18, 'TransferCommission' ,'OEMUè¿' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 19, 'AdjustmentAmount' ,'OEM²®àz' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 20, 'EnterpriseRefund' ,'Á¿XÖÌÔàÁZ' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 21, 'AccountsDueOffsetAmount' ,'OEMO¸Z¢ûàE' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 22, 'AccountsPayablePendingAmount' ,'OEMO¸Z¢¥àÛ¯z' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);


-- @.ÀÑ©çx¥úðÝè
UPDATE T_PayingControl pc, (SELECT DecisionDate, ExecScheduleDate, IFNULL(OemId, 0) AS OemId, MAX(ExecDate) AS ExecDate, MAX(ExecCpId) AS ExecCpId FROM T_PayingControl WHERE ExecDate IS NOT NULL GROUP BY DecisionDate, ExecScheduleDate, IFNULL(OemId, 0)) tmp
SET pc.ExecDate = tmp.ExecDate
   ,pc.ExecCpId = tmp.ExecCpId
WHERE pc.DecisionDate = tmp.DecisionDate
AND pc.ExecScheduleDate = tmp.ExecScheduleDate
AND IFNULL(pc.OemId, 0) = tmp.OemId
AND pc.ExecDate IS NULL
AND pc.DecisionDate >= '2015-12-01'
AND pc.PayingControlStatus = 1;
-- A.@ÅÝè³êÈ¢àÌÍAZo³ê½\èú©çÝè
UPDATE T_PayingControl pc
SET pc.ExecDate = pc.ExecScheduleDate
   ,pc.ExecCpId = 1
WHERE pc.ExecDate IS NULL
AND pc.ExecFlg IN (-1, 1, 11)
AND pc.PayingControlStatus = 1
AND pc.ExecScheduleDate < DATE(NOW());
-- B.ãLÌOEMÅ
UPDATE T_OemClaimed SET ExecDate = SettlePlanDate WHERE SettlePlanDate <= NOW();
