 -- バッチ排他制御
INSERT INTO T_BatchLock (BatchId, ThreadNo, BatchName, BatchLock, UpdateDate) VALUES (3, 1, '立替確定処理', 0, NOW());