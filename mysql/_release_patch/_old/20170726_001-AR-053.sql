/* ����_��v�ɃJ�����ǉ� */
ALTER TABLE `AT_Order` 
ADD COLUMN `ResumeFlg` TINYINT NOT NULL DEFAULT 0 AFTER `NoGuaranteeChangeLimitDay`;

