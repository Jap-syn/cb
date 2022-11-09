ALTER TABLE `T_ReclaimIndicate` 
ADD COLUMN `ClaimHistorySeq` BIGINT(20) NULL AFTER `IndicatedFlg`;

ALTER TABLE `T_ReclaimIndicate` 
ADD INDEX `Idx_T_ReclaimIndicate04` (`ClaimHistorySeq` ASC);

-- �X�V�O�̈���w���f�[�^�o�b�N�A�b�v
SELECT * FROM T_ReclaimIndicate LIMIT 99999;

-- ����w���f�[�^�ƈ�������̕R�t�����s��
UPDATE T_ReclaimIndicate ri
SET ClaimHistorySeq = ((SELECT MIN(Seq) FROM T_ClaimHistory WHERE OrderSeq = ri.OrderSeq AND ClaimAmount = ri.ClaimAmount /* AND ValidFlg = 1 */ AND DATE(ri.UpdateDate) = DATE(ClaimDate)))
WHERE ri.IndicatedFlg = 1;