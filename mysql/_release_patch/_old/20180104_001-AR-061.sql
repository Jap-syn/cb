/* ����ڰ�̨���ނ�UPDATE�iListNumber �ɑ΂���UPDATE�Ȃ̂ŁA������s���B�j */
UPDATE M_TemplateField SET ListNumber='38' WHERE TemplateSeq='2' and PhysicalName='ReceiptDate';
UPDATE M_TemplateField SET ListNumber='37' WHERE TemplateSeq='2' and PhysicalName='IsWaitForReceipt';
UPDATE M_TemplateField SET ListNumber='36' WHERE TemplateSeq='2' and PhysicalName='UpdateName';
UPDATE M_TemplateField SET ListNumber='35' WHERE TemplateSeq='2' and PhysicalName='UpdateDate';
UPDATE M_TemplateField SET ListNumber='34' WHERE TemplateSeq='2' and PhysicalName='RegistName';
UPDATE M_TemplateField SET ListNumber='33' WHERE TemplateSeq='2' and PhysicalName='ArrivalConfirmAlert';

/* �f�t�H���g������ڰ�̨���ނ�INSERT */
INSERT INTO M_TemplateField VALUES ( 2, 32, 'CancelReason', '�L�����Z�����l', 'CHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0);


/* �e�����X������ڰ�̨���ނ�INSERT */
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'CancelReason', '�L�����Z�����l', 'CHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0 from M_TemplateField where TemplateSeq IN (SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKA01005_1' AND Seq > 0) group by TemplateSeq;

