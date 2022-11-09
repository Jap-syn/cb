--備考追加文言
INSERT INTO M_CodeManagement (`CodeId`,`CodeName`, `KeyLogicName`, `Class1ValidFlg`, `Class1Name`, `Class2ValidFlg`, `Class2Name`, `Class3ValidFlg`, `Class3Name`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`) 
VALUES ('219','口座振替不能結果の注文備考', '検索', '1', 'SMBC', '1', 'MUFJ', '3', 'みずほ', NOW(), 1, NOW(), 1);

INSERT INTO M_Code (`CodeId`,`KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Note`) VALUES ('219','1', '資金不足', '1資金不足 振替不能 20日頃再1発行 初回再発行押下禁止', '1資金不足 振替不能 15日頃再1発行 初回再発行押下禁止', '1資金不足 振替不能 15日頃再1発行 初回再発行押下禁止', '区分1:SMBC　区分2:MUFJ　区分3:みずほ');
INSERT INTO M_Code (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Note`) VALUES ('219', '2', '取引なし', '2取引なし 振替不能 20日頃再1発行 初回再発行押下禁止', '2取引なし 振替不能 15日頃再1発行 初回再発行押下禁止', '2取引なし 振替不能 15日頃再1発行 初回再発行押下禁止', '区分1:SMBC　区分2:MUFJ　区分3:みずほ');
INSERT INTO M_Code (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Note`) VALUES ('219', '3', '預金者都合', '3預金者都合 振替不能 20日頃再1発行 初回再発行押下禁止', '3預金者都合 振替不能 15日頃再1発行 初回再発行押下禁止', '3預金者都合 振替不能 15日頃再1発行 初回再発行押下禁止', '区分1:SMBC　区分2:MUFJ　区分3:みずほ');
INSERT INTO M_Code (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Note`) VALUES ('219', '4', '依頼書なし', '4依頼書なし 振替不能 20日頃再1発行 初回再発行押下禁止', '4依頼書なし 振替不能 15日頃再1発行 初回再発行押下禁止', '4依頼書なし 振替不能 15日頃再1発行 初回再発行押下禁止', '区分1:SMBC　区分2:MUFJ　区分3:みずほ');
INSERT INTO M_Code (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Note`) VALUES ('219', '9', 'その他', '9その他 振替不能 20日頃再1発行 初回再発行押下禁止', '9その他 振替不能 15日頃再1発行 初回再発行押下禁止', '9その他 振替不能 15日頃再1発行 初回再発行押下禁止', '区分1:SMBC　区分2:MUFJ　区分3:みずほ');
INSERT INTO M_Code (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Note`) VALUES ('219', '8', '委託者都合', '8委託者都合 振替不能 20日頃再1発行 初回再発行押下禁止', '8委託者都合 振替不能 15日頃再1発行 初回再発行押下禁止', '8委託者都合 振替不能 15日頃再1発行 初回再発行押下禁止', '区分1:SMBC　区分2:MUFJ　区分3:みずほ');


--振替結果CSVのヘッダー
INSERT INTO M_TemplateHeader VALUES( 12579 , 'FUR00000_1', 0, 0, 0, '振替結果一覧CSV', 1, ',', '\"' ,'*' ,0,NULL, NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateField VALUES ( 12579 , 1, 'ResCode' ,'振替結果' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 12579 , 2, 'OrderId' ,'注文ID' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 12579 , 3, 'EntCustSeq' ,'顧客番号' ,'INT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 12579 , 4, 'EnterpriseId' ,'事業者ID' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 12579 , 5, 'EnterpriseNameKj' ,'事業者名' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 12579 , 6, 'CustomerName' ,'加盟店顧客名' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 12579 , 7, 'ClaimAmount' ,'請求金額' ,'INT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);

--振替結果CSV保存場所
ALTER TABLE T_ImportedAccountTransferFile ADD csv LONGBLOB