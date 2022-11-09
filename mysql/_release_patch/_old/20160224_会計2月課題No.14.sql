/***** No.14 [調整金一覧]「調整金確定日」と「注文番号」の間に「立替日」を追加する対応 *****/
ALTER TABLE `AT_AdjustGoldList` ADD COLUMN `AdvancesDate` DATE AFTER `AdjustAmount`;

UPDATE `M_TemplateField` SET `ListNumber`='10' WHERE `TemplateSeq`='74' and`ListNumber`='9';
UPDATE `M_TemplateField` SET `ListNumber`='9' WHERE `TemplateSeq`='74' and`ListNumber`='8';
INSERT INTO `M_TemplateField` (`TemplateSeq`, `ListNumber`, `PhysicalName`, `LogicalName`, `FieldClass`, `RequiredFlg`, `DispWidth`, `RegistDate`, `RegistId`, `UpdateDate`, `UpdateId`, `ValidFlg`) VALUES ('74', '8', 'AdvancesDate', '立替日', 'DATE', '1', '0', '2016-02-15 00:00:00', '9', '2016-02-15 00:00:00', '9', '1');
