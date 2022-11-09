/* 加盟店 */
ALTER TABLE T_Enterprise ADD COLUMN `IndividualSubscriberCodeFlg` TINYINT NOT NULL DEFAULT 0 AFTER `HashKey`;
ALTER TABLE T_Enterprise ADD COLUMN `SubscriberCode` VARCHAR(10)  NULL AFTER `IndividualSubscriberCodeFlg`;
