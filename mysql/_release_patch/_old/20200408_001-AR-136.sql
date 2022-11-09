/* Še‰Á–¿“X‚ÌÃİÌßÚ°ÄÌ¨°ÙÄŞ‚ÌINSERT */
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq
     , MAX(ListNumber) + 1
     , 'ClaimDate'
     , '¿‹“ú'
     , 'DATE'
     , 0
     , NULL
     , 0
     , NULL
     , NULL
     , NULL
     , NOW()
     , 9
     , NOW()
     , 9
     , 0
  FROM M_TemplateField
 WHERE TemplateSeq IN (SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKA01005_1')
 GROUP BY
       TemplateSeq
;
