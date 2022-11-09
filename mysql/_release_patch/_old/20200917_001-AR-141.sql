/* �T�C�g�e�[�u���ɃJ�����ǉ� */
ALTER TABLE T_Site ADD COLUMN `MerchantId` VARCHAR(10)  NULL AFTER `PaymentAfterArrivalFlg`;
ALTER TABLE T_Site ADD COLUMN `ServiceId` VARCHAR(10)  NULL AFTER `MerchantId`;
ALTER TABLE T_Site ADD COLUMN `HashKey` VARCHAR(100)  NULL AFTER `ServiceId`;
ALTER TABLE T_Site ADD COLUMN `BasicId` VARCHAR(10)  NULL AFTER `HashKey`;
ALTER TABLE T_Site ADD COLUMN `BasicPw` VARCHAR(100)  NULL AFTER `BasicId`;

ALTER TABLE T_Site ADD COLUMN `ClaimAutoJournalIncMode` int(11) NOT NULL DEFAULT 0 AFTER `ReissueCount`;

-- T_Site�̕ύX��K�p���邽��MV_Site���X�V
DROP VIEW IF EXISTS MV_Site;
CREATE VIEW `MV_Site` AS
    SELECT *
    FROM coraldb_new01.T_Site
;

-- ���[���e���v���[�g�y�͂��Ă��猈�ϐ��������s���[��(����)�z
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES (108,'�͂��Ă��猈�ϐ��������s���[��(����)�iPC�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'�y�㕥��.com�z�͂��Ă��猈�ϐ��������s�ē�(����)�iPC�j','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��{EnterpriseNameKj}��{OrderId}��{SiteNameKj}��{Phone}��{CustomerNameKj}��{OrderDate}��{UseAmount}��{LimitDate}��{SettlementFee}��{OrderItems}��{OneOrderItem}��{DeliveryFee}��{Tax}��{PassWord}��{CreditLimitDate}��{OrderPageAccessUrl}',NULL,NOW(),1,NOW(),66,1);
INSERT INTO `T_MailTemplate` (`Class`,`ClassName`,`FromTitle`,`FromTitleMime`,`FromAddress`,`ToTitle`,`ToTitleMime`,`ToAddress`,`Subject`,`SubjectMime`,`Body`,`OemId`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
VALUES (109,'�͂��Ă��猈�ϐ��������s���[��(����)�iCEL�j','�㕥���h�b�g�R��','=?UTF-8?B?GyRCOGVKJyQkJUklQyVIJTMlYBsoQg==?=','customer@ato-barai.com',NULL,NULL,NULL,'�y�㕥��.com�z�͂��Ă��猈�ϐ��������s�ē�(����)�iCEL�j','=?UTF-8?B?5b6M5omV44GE44OJ44OD44OI44Kz44Og?=','��{EnterpriseNameKj}��{OrderId}��{SiteNameKj}��{Phone}��{CustomerNameKj}��{OrderDate}��{UseAmount}��{LimitDate}��{SettlementFee}��{OrderItems}��{OneOrderItem}��{DeliveryFee}��{Tax}��{PassWord}��{CreditLimitDate}��{OrderPageAccessUrl}',NULL,NOW(),1,NOW(),66,1);

