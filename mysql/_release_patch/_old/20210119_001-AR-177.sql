/* ���֐��Z������ �o�b�`���b�N */
INSERT INTO T_BatchLock (BatchId, ThreadNo, BatchName, BatchLock, UpdateDate) VALUES (5, 1, 'Payeasy��������', 0, null);

/* �y�C�W�[�p�J�����ǉ� */
ALTER TABLE T_OemClaimAccountInfo ADD COLUMN `ConfirmNumber` INT AFTER `ClaimLayoutMode`
, ADD COLUMN `CustomerNumber` VARCHAR(24) AFTER `ConfirmNumber`
, ADD INDEX Idx_T_OemClaimAccountInfo09(CustomerNumber);