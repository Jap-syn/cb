/* 注文検索結果CSV(検索パターン1)のﾃﾝﾌﾟﾚｰﾄﾍｯﾀﾞｰのINSERT */
INSERT INTO M_TemplateHeader VALUES( 99 , 'CKI01033_2', 0, 0, 0, '注文検索結果CSV(検索パターン1)', 1, ',', '\"' ,'*' ,0,'CKI01033', NULL, NOW(), 9, NOW(), 9,1);
/* 注文検索結果CSV(検索パターン2)のﾃﾝﾌﾟﾚｰﾄﾍｯﾀﾞｰのINSERT */
INSERT INTO M_TemplateHeader VALUES( 100 , 'CKI01033_3', 0, 0, 0, '注文検索結果CSV(検索パターン2)', 1, ',', '\"' ,'*' ,0,'CKI01033', NULL, NOW(), 9, NOW(), 9,1);
/* 注文検索結果CSV(検索パターン3)のﾃﾝﾌﾟﾚｰﾄﾍｯﾀﾞｰのINSERT */
INSERT INTO M_TemplateHeader VALUES( 101 , 'CKI01033_4', 0, 0, 0, '注文検索結果CSV(検索パターン3)', 1, ',', '\"' ,'*' ,0,'CKI01033', NULL, NOW(), 9, NOW(), 9,1);
/* 注文検索結果CSV(検索パターン1)のﾃﾝﾌﾟﾚｰﾄﾌｨｰﾙドのINSERT */
INSERT INTO M_TemplateField VALUES ( 99 , 1, 'OrderId', '注文ID', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 99 , 2, 'ReceiptAmount', '入金額', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 99 , 3, 'UseAmount', '注文金額', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
/* 注文検索結果CSV(検索パターン2)のﾃﾝﾌﾟﾚｰﾄﾌｨｰﾙドのINSERT */
INSERT INTO M_TemplateField VALUES ( 100 , 1, 'OrderId', '注文ID', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 100 , 2, 'RegistDate', '注文登録日', 'DATE', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 100 , 3, 'ReceiptOrderDate', '注文日', 'DATE', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 100 , 4, 'SiteId', 'サイトID', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 100 , 5, 'EnterpriseNameKj', '会社名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 100 , 6, 'NameKj', '注文者名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 100 , 7, 'Incre_Note', '備考', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 100 , 8, 'Phone', '注文者TEL', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 100 , 9, 'MailAddress', '注文者メアド', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 100 , 10, 'UnitingAddress', '注文者住所', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 100 , 11, 'DestUnitingAddress', '配送先住所', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 100 , 12, 'F_LimitDate', '初回支払期限', 'DATE', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 100 , 13, 'Incre_DecisionOpId', '与信担当者', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 100 , 14, 'Incre_ScoreTotal', '社内与信スコア', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 100 , 15, 'TotalScore', '審査システムスコア', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 100 , 16, 'ReceiptDate', '入金日', 'DATE', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 100 , 17, 'ReceiptAmountTotal', '入金額', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 100 , 18, 'Cnl_Status', 'キャンセル状態', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 100 , 19, 'ItemNameKj', '商品名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 100 , 20, 'UnitPrice', '商品単価', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 100 , 21, 'UseAmount', '利用額', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 100 , 22, 'Incre_Status', '審査結果', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
/* 注文検索結果CSV(検索パターン3)のﾃﾝﾌﾟﾚｰﾄﾌｨｰﾙドのINSERT */
INSERT INTO M_TemplateField VALUES ( 101 , 1, 'OrderId', '注文ID', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 101 , 2, 'ReceiptOrderDate', '注文日', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 101 , 3, 'EnterpriseNameKj', '会社名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 101 , 4, 'NameKj', '注文者名', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 101 , 5, 'Phone', '注文者TEL', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 101 , 6, 'MailAddress', '注文者メアド', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 101 , 7, 'PostalCode', '注文者郵便番号', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 101 , 8, 'UnitingAddress', '注文者住所', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 101 , 9, 'Deli_ConfirmArrivalDate', '着荷確認日', 'DATE', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 101 , 10, 'ExecScheduleDate', '立替予定日', 'DATE', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 101 , 11, 'UseAmount', '利用額', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 101 , 12, 'ItemNameKj', '商品１名前', 'VARCHAR', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 101 , 13, 'Incre_Status', '審査結果', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 101 , 14, 'PromPayDate', '支払約束日', 'DATE', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 101 , 15, 'RemindClass', '督促分類', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 101 , 16, 'CombinedClaimTargetStatus', '取りまとめ', 'INT', '0', NULL, '0', NULL, NULL, NULL, NOW(), 9, NOW(), 9, 1);