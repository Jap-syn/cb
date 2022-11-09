/* サイト */
ALTER TABLE  T_Site ADD COLUMN FirstCreditTransferClaimFeeWeb INT NULL AFTER FirstCreditTransferClaimFee;
/* 立替・売上管理 */
ALTER TABLE T_PayingAndSales ADD COLUMN `CreditTransferUpdFlg` TINYINT NOT NULL DEFAULT 0 AFTER `AgencyFeeAddUpFlg`;
