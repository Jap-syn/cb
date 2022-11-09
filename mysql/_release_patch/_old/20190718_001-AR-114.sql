-- システムプロパティ設定
-- 消費税率
INSERT INTO T_SystemProperty(Module, Category, Name, PropValue, Description, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg
) VALUES( '[DEFAULT]','taxconf', '2019-10-01', '10:54000', '2019年10月1日移行の消費税率と印紙代適用金額', NOW(), 9, NOW(), 9, '1');


-- 注文商品の項目追加
ALTER TABLE `T_OrderItems` 
ADD COLUMN `TaxRate` INT DEFAULT NULL AFTER `SumMoney`,
ADD COLUMN `TaxrateNotsetFlg` INT NOT NULL DEFAULT 1 AFTER `TaxRate`;


-- OEM請求口座項目追加
ALTER TABLE `T_OemClaimAccountInfo` 
ADD COLUMN `SubUseAmount_1` INT NOT NULL DEFAULT 0 AFTER `TaxAmount`,
ADD COLUMN `SubTaxAmount_1` INT NOT NULL DEFAULT 0 AFTER `SubUseAmount_1`,
ADD COLUMN `SubUseAmount_2` INT NOT NULL DEFAULT 0 AFTER `SubTaxAmount_1`,
ADD COLUMN `SubTaxAmount_2` INT NOT NULL DEFAULT 0 AFTER `SubUseAmount_2`;



-- 各加盟店のﾃﾝﾌﾟﾚｰﾄﾌｨｰﾙﾄﾞのINSERT
-- 注文登録CSV
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'TaxRate', '消費税率', 'INT', 0, NULL, 0, 'T_OrderItems', '/^8|10$/', '{"group":"order_items"}', NOW(), 9, NOW(), 9, 0 from M_TemplateField where TemplateSeq IN (SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKA01001_1' ) group by TemplateSeq;


-- 注文修正CSV
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'TaxRate', '消費税率', 'INT', 0, NULL, 0, 'T_OrderItems', '/^8|10$/', '{"group":"order_items"}', NOW(), 9, NOW(), 9, 0 from M_TemplateField where TemplateSeq IN (SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKA01001_2' ) group by TemplateSeq;


-- 注文登録API
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'TaxRate', '消費税率', 'INT', 0, NULL, 20, NULL, '/^8|10$/', NULL, NOW(), 9, NOW(), 9, 0 from M_TemplateField where TemplateSeq IN (SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'API005' ) group by TemplateSeq;


-- 同梱請求書
INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'TaxRate', '消費税率', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0 from M_TemplateField where TemplateSeq IN (SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKA04016_1' ) group by TemplateSeq;

INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'SubUseAmount_1', '８％対象合計金額', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0 from M_TemplateField where TemplateSeq IN (SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKA04016_1' ) group by TemplateSeq;

INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'SubTaxAmount_1', '８％対象消費税額', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0 from M_TemplateField where TemplateSeq IN (SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKA04016_1' ) group by TemplateSeq;

INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'SubUseAmount_2', '１０％対象合計金額', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0 from M_TemplateField where TemplateSeq IN (SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKA04016_1' ) group by TemplateSeq;

INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'SubTaxAmount_2', '１０％対象消費税額', 'INT', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0 from M_TemplateField where TemplateSeq IN (SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKA04016_1' ) group by TemplateSeq;

INSERT INTO `M_TemplateField` (`TemplateSeq`,`ListNumber`,`PhysicalName`,`LogicalName`,`FieldClass`,`RequiredFlg`,`DefaultValue`,`DispWidth`,`TableName`,`ValidationRegex`,`ApplicationData`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
SELECT TemplateSeq, MAX(ListNumber)+1, 'CorporationNumber', '事業者登録番号', 'VARCHAR', 0, NULL, 0, NULL, NULL, NULL, NOW(), 9, NOW(), 9, 0 from M_TemplateField where TemplateSeq IN (SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKA04016_1' ) group by TemplateSeq;



-- 注文商品の更新
UPDATE T_OrderItems SET TaxrateNotsetFlg = 1;

UPDATE T_OrderItems SET TaxRate = 5, TaxrateNotsetFlg = 0 WHERE OrderSeq IN 
(SELECT DISTINCT OrderSeq FROM T_ClaimHistory WHERE ClaimDate <= '2014-03-31' AND ClaimPattern = 1);

UPDATE T_OrderItems SET TaxRate = 8, TaxrateNotsetFlg = 0 WHERE OrderSeq IN 
(SELECT DISTINCT OrderSeq FROM T_ClaimHistory WHERE ClaimDate > '2014-03-31' AND ClaimPattern = 1);


