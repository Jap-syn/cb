-- ---------------------------
-- �Г��^�M�|�C���g�}�X�^�[
-- ---------------------------
-- �s������(�w���X��)
INSERT INTO `M_CreditPoint` (`CreditCriterionId`,`CpId`,`Caption`                     ,`Point`,`Message`,`Description`                                               ,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`   ,`RegistDate`, `RegistId`, `UpdateDate`,`UpdateId` , `ValidFlg`) 
SELECT                        KeyCode           ,  204 , '�s�����񐔁i�w���X�܂̂݁j' , 0     , NULL    , 'NG�Ƃ���s�����񐔁i�w���T�C�g�ł̕s�������̂݃J�E���g�j' , 8          , NULL        , NULL        , KeyContent          , 1.00000 , NOW()      , 12        , NOW()       , 83        , 1
FROM   M_Code
WHERE  CodeId = 91;

-- �s������(���X��)
INSERT INTO `M_CreditPoint` (`CreditCriterionId`,`CpId`,`Caption`                     ,`Point`,`Message`,`Description`                                               ,`Dependence`,`GeneralProp`,`SetCategory`,`CreditCriterionName`,`Rate`   ,`RegistDate`, `RegistId`, `UpdateDate`,`UpdateId` , `ValidFlg`) 
SELECT                        KeyCode           ,  205 , '�s�����񐔁i���X�܂̂݁j'   , 0     , NULL    , 'NG�Ƃ���s�����񐔁i���T�C�g�ł̕s�������̂݃J�E���g�j'   , 8          , NULL        , NULL        , KeyContent          , 1.00000 , NOW()      , 12        , NOW()       , 83        , 1
FROM   M_Code
WHERE  CodeId = 91;

-- ����ڰ�Ͻ��
INSERT INTO M_TemplateField VALUES ( 47 , 166, 'NonPaymentCount_Site' ,'�s������(�w���X�܂̂�)' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
INSERT INTO M_TemplateField VALUES ( 47 , 167, 'NonPaymentCount_OtherSite' ,'�s������(���X�܂̂�)' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1);
