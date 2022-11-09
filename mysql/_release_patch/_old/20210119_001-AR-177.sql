/* 立替精算仮締め バッチロック */
INSERT INTO T_BatchLock (BatchId, ThreadNo, BatchName, BatchLock, UpdateDate) VALUES (5, 1, 'Payeasy入金処理', 0, null);

/* ペイジー用カラム追加 */
ALTER TABLE T_OemClaimAccountInfo ADD COLUMN `ConfirmNumber` INT AFTER `ClaimLayoutMode`
, ADD COLUMN `CustomerNumber` VARCHAR(24) AFTER `ConfirmNumber`
, ADD INDEX Idx_T_OemClaimAccountInfo09(CustomerNumber);