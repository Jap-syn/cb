/***************************************************************************************************/
/** お取引明細のレイアウトを旧システム同様に戻す対応
/***************************************************************************************************/
SELECT * FROM M_TemplateHeader WHERE TemplateId = 'CKA11019_2';

-- お取引明細（締め日別）
DELETE FROM M_TemplateField WHERE TemplateSeq = (SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKA11019_2'); -- 7

-- お取引明細（締め日別）
INSERT INTO M_TemplateField VALUES ( 7 , 1, 'ReceiptOrderDate' ,'注文日' ,'DATE' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 7 , 2, 'OrderId' ,'注文ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 7 , 3, 'Ent_OrderId' ,'任意注文番号' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 7 , 4, 'NameKj' ,'購入者' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 7 , 5, 'UseAmount' ,'顧客請求金額' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 7 , 6, 'SettlementFee' ,'決済手数料' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 7 , 7, 'ClaimFee' ,'請求書発行手数料' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 7 , 8, 'ChargeAmount' ,'差引き合計' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 7 , 9, 'SiteNameKj' ,'受付サイト' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 7 , 10, 'SiteId' ,'サイトID' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 7 , 11, 'EnterpriseId' ,'加盟店ID' ,'BIGINT' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0);
INSERT INTO M_TemplateField VALUES ( 7 , 12, 'EnterpriseNameKj' ,'加盟店名' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0);
INSERT INTO M_TemplateField VALUES ( 7 , 13, 'FixedDate' ,'立替締め日' ,'DATE' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0);
INSERT INTO M_TemplateField VALUES ( 7 , 14, 'ExecScheduleDate' ,'立替予定日' ,'DATE' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0);
INSERT INTO M_TemplateField VALUES ( 7 , 15, 'ChargeCount' ,'お取引件数' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0);
INSERT INTO M_TemplateField VALUES ( 7 , 16, 'Deli_JournalIncDate' ,'伝登日' ,'DATE' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0);
INSERT INTO M_TemplateField VALUES ( 7 , 17, 'FixedDate2' ,'立替締' ,'DATE' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0);
INSERT INTO M_TemplateField VALUES ( 7 , 18, 'StampFee' ,'印紙代金' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0);





/***************************************************************************************************/
/** 伝票修正のレイアウトを旧システム同様に戻す対応
/***************************************************************************************************/
INSERT INTO M_TemplateHeader VALUES( 82 , 'CKA03011_3', 0, 0, 0, '配送伝票修正CSV（10項目版）', 1, ',', '\"' ,'*' ,0,'KA03011', NULL, NOW(), 9, NOW(), 9,1);


INSERT INTO M_TemplateField VALUES ( 82 , 1, 'OrderId' ,'注文ID' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 82 , 2, 'Ent_OrderId' ,'任意注文番号' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 82 , 3, 'Deli_DeliveryMethod' ,'配送会社' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 82 , 4, 'Deli_JournalNumber' ,'配送伝票番号' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 82 , 5, 'ReceiptOrderDate' ,'注文日' ,'DATE' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 82 , 6, 'NameKj' ,'購入者' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 82 , 7, 'Phone' ,'電話番号' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 82 , 8, 'UseAmount' ,'購入金額' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 82 , 9, 'UnitingAddress' ,'配送先住所' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 82 , 10, 'IsSelfBilling' ,'自社印刷' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);



INSERT INTO M_TemplateHeader VALUES( 83 , 'CKA03011_4', 0, 0, 0, '配送伝票修正CSV（9項目版）', 1, ',', '\"' ,'*' ,0,'KA03011', NULL, NOW(), 9, NOW(), 9,1);

INSERT INTO M_TemplateField VALUES ( 83 , 1, 'OrderId' ,'注文ID' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 83 , 2, 'Ent_OrderId' ,'任意注文番号' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 83 , 3, 'Deli_DeliveryMethod' ,'配送会社' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 83 , 4, 'Deli_JournalNumber' ,'配送伝票番号' ,'VARCHAR' ,1 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 83 , 5, 'ReceiptOrderDate' ,'注文日' ,'DATE' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 83 , 6, 'NameKj' ,'購入者' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 83 , 7, 'Phone' ,'電話番号' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 83 , 8, 'UseAmount' ,'購入金額' ,'BIGINT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 83 , 9, 'UnitingAddress' ,'配送先住所' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);

