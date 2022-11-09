ALTER TABLE `T_ReclaimIndicate` 
ADD COLUMN `ClaimHistorySeq` BIGINT(20) NULL AFTER `IndicatedFlg`;

ALTER TABLE `T_ReclaimIndicate` 
ADD INDEX `Idx_T_ReclaimIndicate04` (`ClaimHistorySeq` ASC);

-- 更新前の印刷指示データバックアップ
SELECT * FROM T_ReclaimIndicate LIMIT 99999;

-- 印刷指示データと印刷履歴の紐付けを行う
UPDATE T_ReclaimIndicate ri
SET ClaimHistorySeq = ((SELECT MIN(Seq) FROM T_ClaimHistory WHERE OrderSeq = ri.OrderSeq AND ClaimAmount = ri.ClaimAmount /* AND ValidFlg = 1 */ AND DATE(ri.UpdateDate) = DATE(ClaimDate)))
WHERE ri.IndicatedFlg = 1;
