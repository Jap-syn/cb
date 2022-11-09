ALTER TABLE `T_Oem` ADD COLUMN `OemFixedPattern`    TINYINT(4) NOT NULL DEFAULT 0    AFTER `OemClaimTransFlg`;
ALTER TABLE `T_Oem` ADD COLUMN `OemFixedDay_Week`   INT(11)        NULL DEFAULT NULL AFTER `OemFixedDay3`;
ALTER TABLE `T_Oem` ADD COLUMN `SettlementDay_Week` INT(11)        NULL DEFAULT NULL AFTER `SettlementDay3`;

/* OEM事業者一覧CSVのﾃﾝﾌﾟﾚｰﾄﾌｨｰﾙﾄﾞのINSERT */
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq
     , MAX(ListNumber) + 1
     , 'OemFixedPattern'
     , 'OEM締めパターン　0：日付指定　1：週締め'
     , 'TINYINT'
     , 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0
  FROM M_TemplateField
 WHERE TemplateSeq IN (SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKI16142_1')
 GROUP BY
       TemplateSeq
;
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq
     , MAX(ListNumber) + 1
     , 'OemFixedDay_Week'
     , 'OEM締め日（週締め）'
     , 'INT'
     , 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0
  FROM M_TemplateField
 WHERE TemplateSeq IN (SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKI16142_1')
 GROUP BY
       TemplateSeq
;
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq
     , MAX(ListNumber) + 1
     , 'SettlementDay_Week'
     , '精算予定日（週締め）'
     , 'INT'
     , 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0
  FROM M_TemplateField
 WHERE TemplateSeq IN (SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKI16142_1')
 GROUP BY
       TemplateSeq
;
