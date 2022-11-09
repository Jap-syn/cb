-- ---------------------------
-- 社内与信ポイントマスター
-- ---------------------------
-- 不払い回数(購入店舗)
INSERT INTO `M_CreditPoint` (`CreditCriterionId`,`CpId`,`Caption`                     ,`Point`,`Message`,`Description`                                               ,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`   ,`RegistDate`, `RegistId`, `UpdateDate`,`UpdateId` , `ValidFlg`) 
SELECT                        KeyCode           ,  204 , '不払い回数（購入店舗のみ）' , 0     , NULL    , 'NGとする不払い回数（購入サイトでの不払い数のみカウント）' , 8          , NULL        , NULL        , KeyContent          , 1.00000 , NOW()      , 12        , NOW()       , 83        , 1
FROM   M_Code
WHERE  CodeId = 91;

-- 不払い回数(他店舗)
INSERT INTO `M_CreditPoint` (`CreditCriterionId`,`CpId`,`Caption`                     ,`Point`,`Message`,`Description`                                               ,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`   ,`RegistDate`, `RegistId`, `UpdateDate`,`UpdateId` , `ValidFlg`) 
SELECT                        KeyCode           ,  205 , '不払い回数（他店舗のみ）'   , 0     , NULL    , 'NGとする不払い回数（他サイトでの不払い数のみカウント）'   , 8          , NULL        , NULL        , KeyContent          , 1.00000 , NOW()      , 12        , NOW()       , 83        , 1
FROM   M_Code
WHERE  CodeId = 91;

-- ﾃﾝﾌﾟﾚｰﾄﾏｽﾀｰ
INSERT INTO M_TemplateField VALUES ( 47 , 166, 'NonPaymentCount_Site' ,'不払い回数(購入店舗のみ)' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 47 , 167, 'NonPaymentCount_OtherSite' ,'不払い回数(他店舗のみ)' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
