INSERT INTO M_TemplateHeader VALUES( 92 , 'CKI01039_1', 0, 0, 0, '備考コメントダウンロードCSV（備考のみ）', 1, ',', '\"' ,'*' ,0,'KI01039', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateHeader VALUES( 93 , 'CKI01039_2', 0, 0, 0, '備考コメントダウンロードCSV（一括）', 1, ',', '\"' ,'*' ,0,'KI01039', NULL, NOW(), 9, NOW(), 9,1);
INSERT INTO M_TemplateField VALUES ( 92 , 1, 'OrderId' ,'注文ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 92 , 2, 'Incre_Note' ,'備考' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 93 , 1, 'OrderId' ,'注文ID' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 93 , 2, 'Incre_Note' ,'備考' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 93 , 3, 'LetterClaimStopFlg' ,'紙ストップ区分' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 93 , 4, 'MailClaimStopFlg' ,'メールストップ区分' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 93 , 5, 'ClaimStopReleaseDate' ,'請求ストップ解除日' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 93 , 6, 'PromPayDate' ,'支払約束日' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 93 , 7, 'VisitFlg' ,'訪問済処理区分' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 93 , 8, 'FinalityCollectionMean' ,'最終回収手段' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 93 , 9, 'FinalityRemindDate' ,'最終督促日' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 93 , 10, 'RemindClass' ,'督促分類' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 93 , 11, 'ValidTel' ,'TEL有効' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 93 , 12, 'ValidAddress' ,'住所有効' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 93 , 13, 'ValidMail' ,'メール有効' ,'VARCHAR' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);


INSERT INTO T_Menu VALUES (185, 'cbadmin', 'searchMenus', 'rwordercsv', NULL, '***', '備考コメント一括登録', '備考コメント一括登録', '', '', '', NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (185, 1, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (185, 11, NOW(), 9, NOW(), 9, 1);
INSERT INTO T_MenuAuthority VALUES (185, 101, NOW(), 9, NOW(), 9, 1);

