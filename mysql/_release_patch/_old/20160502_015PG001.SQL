/* OEM精算書ダウンロード対応 */

/* OEM請求[T_OemClaimed]へのカラム追加 */
ALTER TABLE T_OemClaimed ADD COLUMN `N_MonthlyFeeWithoutTax` BIGINT NOT NULL DEFAULT 0 AFTER `OM_EntMonthlyFeePlan`;
ALTER TABLE T_OemClaimed ADD COLUMN `N_MonthlyFeeTax` BIGINT NOT NULL DEFAULT 0 AFTER `N_MonthlyFeeWithoutTax`;

/* コードマスタへ請求手数料パターン初期登録 */
INSERT INTO M_CodeManagement VALUES(184 ,'請求手数料パターン' ,NULL ,'請求手数料税込額' ,1 ,'請求手数料税抜額' ,1,'請求手数料税額',0,NULL, NOW(),1,NOW(),1,1);
INSERT INTO M_Code VALUES(184,172 ,'請求手数料税込172円','160' ,'12' , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(184,91 ,'請求手数料税込91円','85' ,'6' , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);
INSERT INTO M_Code VALUES(184,216 ,'請求手数料税込216円','200' ,'16' , NULL ,NULL ,0, NOW(), 1, NOW(), 1, 1);

/* テンプレート登録 */
INSERT INTO M_TemplateHeader VALUES( 84 , 'COEM030_1', 0, 0, 0, '後払い利用契約', 1, ',', '\"' ,'*' ,0,'OEM030', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateHeader VALUES( 85 , 'COEM030_3', 0, 0, 0, '後払い利用明細（印紙代）', 1, ',', '\"' ,'*' ,0,'OEM030', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateHeader VALUES( 86 , 'COEM030_4', 0, 0, 0, 'キャンセル返金明細', 1, ',', '\"' ,'*' ,0,'OEM030', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateHeader VALUES( 87 , 'COEM030_5', 0, 0, 0, '調整額明細', 1, ',', '\"' ,'*' ,0,'OEM030', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateHeader VALUES( 88 , 'COEM030_6', 0, 0, 0, '精算までのキャンセル返金明細', 1, ',', '\"' ,'*' ,0,'OEM030', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateField VALUES ( 84 , 1, 'ChargeMonth' ,'利用年月' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 84 , 2, 'EnterpriseId' ,'事業者ＩＤ' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 84 , 3, 'PlanId' ,'利用プラン' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 84 , 4, 'ServiceInDate' ,'サービス開始日' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 84 , 5, 'ChargeAmount' ,'OEM月額固定費' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 85 , 1, 'EnterpriseId' ,'事業者ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 85 , 2, 'SiteId' ,'サイトID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 85 , 3, 'OrderId' ,'注文ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 85 , 4, 'ReceiptProcessDate' ,'入金確認日' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 85 , 5, 'StampFee' ,'印紙代' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 85 , 6, 'UseAmount' ,'顧客請求金額' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 1, 'RowNumber' ,'No.' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 2, 'OrderId' ,'注文ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 3, 'Ent_OrderId' ,'任意注文番号' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 4, 'NameKj' ,'購入者' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 5, 'OrderDate' ,'注文日' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 6, 'FixedDate' ,'立替締め日' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 7, 'ApprovalDate' ,'キャンセル日' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 8, 'ClaimAmount' ,'顧客請求額' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 86 , 9, 'RepayTotal' ,'キャンセル返金額' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 87 , 1, 'EnterpriseNameKj' ,'事業者名' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 87 , 2, 'OrderId' ,'取引ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 87 , 3, 'AdjustmentAmount' ,'調整額' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 87 , 4, 'Kamoku' ,'調整事由' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 1, 'OrderDate' ,'注文日' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 2, 'OrderId' ,'注文ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 3, 'Ent_OrderId' ,'任意注文番号' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 4, 'NameKj' ,'購入者' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 5, 'UseAmount' ,'顧客請求額' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 6, 'SettlementFee' ,'決済手数料' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 7, 'ClaimFee' ,'請求書発行手数料' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 8, 'DiffSum' ,'差引合計' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 9, 'EnterpriseNameKj' ,'事業者名' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 10, 'SiteNameKj' ,'受付サイト' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 11, 'SiteId' ,'サイトID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 12, 'ReceiptClass' ,'入金方法' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 13, 'EnterpriseId' ,'事業者ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 14, 'PlanId' ,'プラン' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 88 , 15, 'AppSettlementFeeRate' ,'料率' ,'DECIMAL' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
