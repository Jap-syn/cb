/* OEM¸Z_E[hÎ */

/* OEM¿[T_OemClaimed]ÖÌJÇÁ */
ALTER TABLE T_OemClaimed ADD COLUMN `N_MonthlyFeeWithoutTax` BIGINT NOT NULL DEFAULT 0 AFTER `OM_EntMonthlyFeePlan`;
ALTER TABLE T_OemClaimed ADD COLUMN `N_MonthlyFeeTax` BIGINT NOT NULL DEFAULT 0 AFTER `N_MonthlyFeeWithoutTax`;

/* R[h}X^Ö¿è¿p^[úo^ */
INSERT INTO M_CodeManagement VALUES(184 ,'¿è¿p^[' ,NULL ,'¿è¿Åz' ,1 ,'¿è¿Å²z' ,1,'¿è¿Åz',0,NULL, NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(184,172 ,'¿è¿Å172~','160' ,'12' , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(184,91 ,'¿è¿Å91~','85' ,'6' , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(184,216 ,'¿è¿Å216~','200' ,'16' , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);

/* ev[go^ */
INSERT INTO M_TemplateHeader VALUES( 84 , 'COEM030_1', 0, 0, 0, 'ã¥¢p_ñ', 1, ',', '\"' ,'*' ,0,'OEM030', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateHeader VALUES( 85 , 'COEM030_3', 0, 0, 0, 'ã¥¢p¾×ióãj', 1, ',', '\"' ,'*' ,0,'OEM030', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateHeader VALUES( 86 , 'COEM030_4', 0, 0, 0, 'LZÔà¾×', 1, ',', '\"' ,'*' ,0,'OEM030', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateHeader VALUES( 87 , 'COEM030_5', 0, 0, 0, '²®z¾×', 1, ',', '\"' ,'*' ,0,'OEM030', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateHeader VALUES( 88 , 'COEM030_6', 0, 0, 0, '¸ZÜÅÌLZÔà¾×', 1, ',', '\"' ,'*' ,0,'OEM030', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateField VALUES ( 84 , 1, 'ChargeMonth' ,'pN' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 84 , 2, 'EnterpriseId' ,'ÆÒhc' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 84 , 3, 'PlanId' ,'pv' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 84 , 4, 'ServiceInDate' ,'T[rXJnú' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 84 , 5, 'ChargeAmount' ,'OEMzÅèï' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 85 , 1, 'EnterpriseId' ,'ÆÒID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 85 , 2, 'SiteId' ,'TCgID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 85 , 3, 'OrderId' ,'¶ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 85 , 4, 'ReceiptProcessDate' ,'üàmFú' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 85 , 5, 'StampFee' ,'óã' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 85 , 6, 'UseAmount' ,'Úq¿àz' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 1, 'RowNumber' ,'No.' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 2, 'OrderId' ,'¶ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 3, 'Ent_OrderId' ,'CÓ¶Ô' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 4, 'NameKj' ,'wüÒ' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 5, 'OrderDate' ,'¶ú' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 6, 'FixedDate' ,'§Ö÷ßú' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 7, 'ApprovalDate' ,'LZú' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 8, 'ClaimAmount' ,'Úq¿z' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 9, 'RepayTotal' ,'LZÔàz' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 87 , 1, 'EnterpriseNameKj' ,'ÆÒ¼' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 87 , 2, 'OrderId' ,'æøID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 87 , 3, 'AdjustmentAmount' ,'²®z' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 87 , 4, 'Kamoku' ,'²®R' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 1, 'OrderDate' ,'¶ú' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 2, 'OrderId' ,'¶ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 3, 'Ent_OrderId' ,'CÓ¶Ô' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 4, 'NameKj' ,'wüÒ' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 5, 'UseAmount' ,'Úq¿z' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 6, 'SettlementFee' ,'Ïè¿' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 7, 'ClaimFee' ,'¿­sè¿' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 8, 'DiffSum' ,'·øv' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 9, 'EnterpriseNameKj' ,'ÆÒ¼' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 10, 'SiteNameKj' ,'ótTCg' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 11, 'SiteId' ,'TCgID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 12, 'ReceiptClass' ,'üàû@' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 13, 'EnterpriseId' ,'ÆÒID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 14, 'PlanId' ,'v' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 15, 'AppSettlementFeeRate' ,'¿¦' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
