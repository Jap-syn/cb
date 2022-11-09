INSERT INTO M_TemplateField VALUES ( 2 , 41, 'CreditTransferRequestFlg' ,'口座振替申込区分' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0) ;
INSERT INTO M_TemplateField VALUES ( 2 , 42, 'RequestStatus' ,'申込ステータス' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0) ;
INSERT INTO M_TemplateField VALUES ( 2 , 43, 'RequestSubStatus' ,'申込サブステータス' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0) ;
INSERT INTO M_TemplateField VALUES ( 2 , 44, 'RequestCompDate' ,'申込完了日' ,'DATETIME' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0) ;
INSERT INTO M_TemplateField VALUES ( 2 , 45, 'CreditTransferMethod1' ,'口座振替' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0) ;
INSERT INTO M_TemplateField VALUES ( 2 , 46, 'CreditTransferMethod2' ,'初回申込用紙発行' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 0) ;

INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'CreditTransferRequestFlg', '口座振替申込区分',   'INT',      0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0 FROM M_TemplateField WHERE TemplateSeq IN(select distinct(TemplateSeq) from M_TemplateHeader where TemplateId = 'CKA01005_1' and TemplateSeq not in ( select TemplateSeq from M_TemplateField where PhysicalName='CreditTransferRequestFlg')) GROUP BY TemplateSeq;
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'RequestStatus',            '申込ステータス',     'INT',      0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0 FROM M_TemplateField WHERE TemplateSeq IN(select distinct(TemplateSeq) from M_TemplateHeader where TemplateId = 'CKA01005_1' and TemplateSeq not in ( select TemplateSeq from M_TemplateField where PhysicalName='RequestStatus')) GROUP BY TemplateSeq;
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'RequestSubStatus',         '申込サブステータス', 'INT',      0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0 FROM M_TemplateField WHERE TemplateSeq IN(select distinct(TemplateSeq) from M_TemplateHeader where TemplateId = 'CKA01005_1' and TemplateSeq not in ( select TemplateSeq from M_TemplateField where PhysicalName='RequestSubStatus')) GROUP BY TemplateSeq;
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'RequestCompDate',          '申込完了日',         'DATETIME', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0 FROM M_TemplateField WHERE TemplateSeq IN(select distinct(TemplateSeq) from M_TemplateHeader where TemplateId = 'CKA01005_1' and TemplateSeq not in ( select TemplateSeq from M_TemplateField where PhysicalName='RequestCompDate')) GROUP BY TemplateSeq;
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'CreditTransferMethod1',    '口座振替',           'INT',      0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0 FROM M_TemplateField WHERE TemplateSeq IN(select distinct(TemplateSeq) from M_TemplateHeader where TemplateId = 'CKA01005_1' and TemplateSeq not in ( select TemplateSeq from M_TemplateField where PhysicalName='CreditTransferMethod1')) GROUP BY TemplateSeq;
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'CreditTransferMethod2',    '初回申込用紙発行',   'INT',      0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0 FROM M_TemplateField WHERE TemplateSeq IN(select distinct(TemplateSeq) from M_TemplateHeader where TemplateId = 'CKA01005_1' and TemplateSeq not in ( select TemplateSeq from M_TemplateField where PhysicalName='CreditTransferMethod2')) GROUP BY TemplateSeq;
