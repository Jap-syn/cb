INSERT INTO M_TemplateField VALUES ('18', '161', 'RequestStatus', '申込ステータス', 'VARCHAR', '0', null, '0', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO M_TemplateField VALUES ('18', '162', 'RequestSubStatus', '申込サブステータス', 'VARCHAR', '0', null, '0', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO M_TemplateField VALUES ('18', '163', 'RequestCompDate', '申込完了日', 'VARCHAR', '0', null, '0', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO M_TemplateField VALUES ('18', '164', 'CreditTransferMethod1', '口座振替', 'VARCHAR', '0', null, '0', null, null, null, NOW(), '0', NOW(), '0', '1');
INSERT INTO M_TemplateField VALUES ('18', '165', 'CreditTransferMethod2', '初回申込用紙発行', 'VARCHAR', '0', null, '0', null, null, null, NOW(), '0', NOW(), '0', '1');

INSERT INTO `M_Code` (`CodeId`, `KeyCode`, `KeyContent`, `Class1`, `Class2`, `Class3`, `Note`, `SystemFlg`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES 
('101', '6', '三菱UFJファクター', 'MUFJ', '収納代行業者（三菱UFJファクター）', '未収金(MUFJ)　', '', '0', NOW(), '0',NOW(), '0', '1');


/* デフォルトのﾃﾝﾌﾟﾚｰﾄﾌｨｰﾙﾄﾞのINSERT */
INSERT INTO M_TemplateField VALUES ( 2 , 41, 'CreditTransferRequestFlg' ,'口座振替申込区分' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1) ;
INSERT INTO M_TemplateField VALUES ( 2 , 42, 'RequestStatus' ,'申込ステータス' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1) ;
INSERT INTO M_TemplateField VALUES ( 2 , 43, 'RequestSubStatus' ,'申込サブステータス' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1) ;
INSERT INTO M_TemplateField VALUES ( 2 , 44, 'RequestCompDate' ,'申込完了日' ,'DATETIME' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1) ;
INSERT INTO M_TemplateField VALUES ( 2 , 45, 'CreditTransferMethod1' ,'口座振替' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1) ;
INSERT INTO M_TemplateField VALUES ( 2 , 46, 'CreditTransferMethod2' ,'初回申込用紙発行' ,'INT' ,0 ,NULL ,0,NULL ,NULL ,NULL , NOW(), 9, NOW(), 9, 1) ;

/* 各加盟店のﾃﾝﾌﾟﾚｰﾄﾌｨｰﾙﾄﾞのINSERT */
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'CreditTransferRequestFlg', '口座振替申込区分', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0 FROM M_TemplateField WHERE TemplateSeq IN( SELECT TemplateSeq FROM M_TemplateHeader INNER JOIN T_Enterprise ON M_TemplateHeader.Seq=T_Enterprise.EnterpriseId AND T_Enterprise.CreditTransferFlg IN (1,2,3) WHERE TemplateId = 'CKA01005_1' AND TemplateClass = 2 AND Seq > 0 ) GROUP BY TemplateSeq;
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'RequestStatus', '申込ステータス', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0 FROM M_TemplateField WHERE TemplateSeq IN( SELECT TemplateSeq FROM M_TemplateHeader INNER JOIN T_Enterprise ON M_TemplateHeader.Seq=T_Enterprise.EnterpriseId AND T_Enterprise.CreditTransferFlg IN (1,2,3) WHERE TemplateId = 'CKA01005_1' AND TemplateClass = 2 AND Seq > 0 ) GROUP BY TemplateSeq;
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'RequestSubStatus', '申込サブステータス', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0 FROM M_TemplateField WHERE TemplateSeq IN( SELECT TemplateSeq FROM M_TemplateHeader INNER JOIN T_Enterprise ON M_TemplateHeader.Seq=T_Enterprise.EnterpriseId AND T_Enterprise.CreditTransferFlg IN (1,2,3) WHERE TemplateId = 'CKA01005_1' AND TemplateClass = 2 AND Seq > 0 ) GROUP BY TemplateSeq;
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'RequestCompDate', '申込完了日', 'DATETIME', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0 FROM M_TemplateField WHERE TemplateSeq IN( SELECT TemplateSeq FROM M_TemplateHeader INNER JOIN T_Enterprise ON M_TemplateHeader.Seq=T_Enterprise.EnterpriseId AND T_Enterprise.CreditTransferFlg IN (1,2,3) WHERE TemplateId = 'CKA01005_1' AND TemplateClass = 2 AND Seq > 0 ) GROUP BY TemplateSeq;
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'CreditTransferMethod1', '口座振替', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0 FROM M_TemplateField WHERE TemplateSeq IN( SELECT TemplateSeq FROM M_TemplateHeader INNER JOIN T_Enterprise ON M_TemplateHeader.Seq=T_Enterprise.EnterpriseId AND T_Enterprise.CreditTransferFlg IN (1,2,3) WHERE TemplateId = 'CKA01005_1' AND TemplateClass = 2 AND Seq > 0 ) GROUP BY TemplateSeq;
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'CreditTransferMethod2', '初回申込用紙発行', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0 FROM M_TemplateField WHERE TemplateSeq IN( SELECT TemplateSeq FROM M_TemplateHeader INNER JOIN T_Enterprise ON M_TemplateHeader.Seq=T_Enterprise.EnterpriseId AND T_Enterprise.CreditTransferFlg IN (1,2,3) WHERE TemplateId = 'CKA01005_1' AND TemplateClass = 2 AND Seq > 0 ) GROUP BY TemplateSeq;


create index Idx_T_EnterpriseCustomer03 on T_EnterpriseCustomer(EnterpriseId);
create index Idx_T_Customer10 on T_Customer(EntCustSeq, OrderSeq, EntCustId);
