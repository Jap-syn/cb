/*振込手数料負担区分*/
ALTER TABLE M_Agency
ADD COLUMN ChargeClass INT NOT NULL DEFAULT 1 AFTER TcClass;

/*振込先区分*/
ALTER TABLE M_Agency
ADD COLUMN TransferFeeClass INT AFTER ChargeClass;

/* 代理店手数料管理 */
ALTER TABLE `T_AgencyFee`
ADD COLUMN `CancelAddUpFlg` INT NOT NULL DEFAULT 0 AFTER `AddUpFlg`;

/* OEM代理店手数料管理 */
ALTER TABLE `T_OemAgencyFee`
ADD COLUMN `CancelAddUpFlg` INT NOT NULL DEFAULT 0 AFTER `AddUpFlg`;

/*※ListNumberの順序要チェック*/
UPDATE M_TemplateField SET LogicalName = '店舗名' WHERE TemplateSeq = 44 AND ListNumber = 7;

UPDATE M_TemplateField SET LogicalName = 'ご注文ID' WHERE TemplateSeq = 44 AND ListNumber = 9;

UPDATE M_TemplateField SET LogicalName = 'ご注文日' WHERE TemplateSeq = 44 AND ListNumber = 10;

UPDATE M_TemplateField SET LogicalName = 'ご入金日' WHERE TemplateSeq = 44 AND ListNumber = 11;

UPDATE M_TemplateField SET LogicalName = 'ご請求金額' WHERE TemplateSeq = 44 AND ListNumber = 12;

UPDATE M_TemplateField SET ValidFlg = 0 WHERE TemplateSeq = 44 AND ListNumber IN(1, 2, 3, 4, 5, 6, 8, 14);

/*初期設定(振込手数料負担、振込先)*/
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 1;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 2;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 3;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 4;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 5;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 6;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 7;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 8;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 9;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 10;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 11;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 12;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 13;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 14;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 15;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 16;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 17;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 18;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 19;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 20;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 21;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 22;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 23;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 24;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 25;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 26;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 27;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 28;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 29;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 30;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 31;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 32;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 33;
UPDATE M_Agency SET ChargeClass = 1, TransferFeeClass = NULL WHERE AgencyId = 34;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 35;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 36;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 37;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 38;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 39;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 40;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 41;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 42;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 43;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 44;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 45;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 46;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 47;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 48;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 49;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 50;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 51;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 52;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 53;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 54;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 55;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 56;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 57;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 58;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 59;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 60;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 61;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 62;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 63;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 64;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 65;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 66;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 67;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 68;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 69;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 70;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 71;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 72;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 73;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 74;
UPDATE M_Agency SET ChargeClass = 1, TransferFeeClass = NULL WHERE AgencyId = 75;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 76;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 77;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 78;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 79;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 80;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 81;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 82;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 83;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 84;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 85;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 86;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 87;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 88;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 89;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 90;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 91;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 92;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 93;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 94;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 95;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 96;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 97;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 98;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 99;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 100;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 101;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 102;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 103;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 104;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 105;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 106;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 107;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 108;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 109;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 110;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 111;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 112;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 113;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 114;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 115;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 116;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 117;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 118;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 119;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 120;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 121;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 122;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 123;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 2 WHERE AgencyId = 124;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 125;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 126;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 127;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 128;
UPDATE M_Agency SET ChargeClass = 2, TransferFeeClass = 1 WHERE AgencyId = 129;
