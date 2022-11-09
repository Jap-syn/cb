-- AT_PayOff_DailyAccount.EnterpriseIdへインデックスを付与
ALTER TABLE `AT_PayOff_DailyAccount` ADD INDEX `Idx_AT_PayOff_DailyAccount02` (`EnterpriseId` ASC);
-- OEM請求へカラム[支払完了日]追加
ALTER TABLE `T_OemClaimed` ADD COLUMN `ExecDate` DATE NULL DEFAULT NULL AFTER `N_MonthlyFeeTax`;
-- OEM請求[支払完了日]へインデックス付与
ALTER TABLE `T_OemClaimed` ADD INDEX `Idx_T_OemClaimed04` (`ExecDate` ASC);


-- テンプレートマスター登録
INSERT INTO M_TemplateHeader VALUES( 89 , 'CKI24174_13_O', 0, 0, 0, '精算日計(OEM版)', 1, ',', '\"' ,'SJIS-win' ,0,'CKI24174', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateField VALUES ( 89 , 1, 'OemId' ,'OEMコード' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 2, 'OemNameKj' ,'OEM名' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 3, 'EnterpriseId' ,'加盟店コード' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 4, 'EnterpriseNameKj' ,'加盟店名' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 5, 'AdvancesDate' ,'OEMへの立替日' ,'DATE' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 6, 'AdvancesAmount' ,'OEM立替実行金額' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 7, 'EnterpriseAccountsDue' ,'OEM未収金' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 8, 'AccountsPayablePending' ,'OEM未払金保留' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 9, 'ClaimAndObligationsDecision' ,'債権債務判定' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 10, 'UseAmount' ,'利用額・OEM未払金' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 11, 'CancelAmount' ,'キャンセル金額' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 12, 'AccountsPayableTotal' ,'OEM未払金計' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 13, 'SettlementFee' ,'OEM決済手数料' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 14, 'ClaimFee' ,'OEM請求手数料' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 15, 'MonthlyFee' ,'OEM月額固定費' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 16, 'AccountsReceivableTotal' ,'OEM売掛金計' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 17, 'StampFee' ,'印紙代' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 18, 'TransferCommission' ,'OEM振込手数料' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 19, 'AdjustmentAmount' ,'OEM調整金額' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 20, 'EnterpriseRefund' ,'加盟店への返金加算' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 21, 'AccountsDueOffsetAmount' ,'OEM前精算時未収金相殺' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 89 , 22, 'AccountsPayablePendingAmount' ,'OEM前精算時未払金保留額' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);


-- ①.実績から支払日を設定
UPDATE T_PayingControl pc, (SELECT DecisionDate, ExecScheduleDate, IFNULL(OemId, 0) AS OemId, MAX(ExecDate) AS ExecDate, MAX(ExecCpId) AS ExecCpId FROM T_PayingControl WHERE ExecDate IS NOT NULL GROUP BY DecisionDate, ExecScheduleDate, IFNULL(OemId, 0)) tmp
SET pc.ExecDate = tmp.ExecDate
   ,pc.ExecCpId = tmp.ExecCpId
WHERE pc.DecisionDate = tmp.DecisionDate
AND pc.ExecScheduleDate = tmp.ExecScheduleDate
AND IFNULL(pc.OemId, 0) = tmp.OemId
AND pc.ExecDate IS NULL
AND pc.DecisionDate >= '2015-12-01'
AND pc.PayingControlStatus = 1;
-- ②.①で設定されないものは、算出された予定日から設定
UPDATE T_PayingControl pc
SET pc.ExecDate = pc.ExecScheduleDate
   ,pc.ExecCpId = 1
WHERE pc.ExecDate IS NULL
AND pc.ExecFlg IN (-1, 1, 11)
AND pc.PayingControlStatus = 1
AND pc.ExecScheduleDate < DATE(NOW());
-- ③.上記のOEM版
UPDATE T_OemClaimed SET ExecDate = SettlePlanDate WHERE SettlePlanDate <= NOW();
