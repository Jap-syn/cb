/* AT_EnterpriseMonthlyClosingInfo���ڒǉ�(��10�b) */
ALTER TABLE `AT_EnterpriseMonthlyClosingInfo` 
ADD COLUMN `AppPlan` INT NULL AFTER `PayingControlSeq`;

/* OEM����AppPlan�ڍs(���m�ɐݒ�o������̂͐ݒ�)(��10�b) */
UPDATE AT_EnterpriseMonthlyClosingInfo emc
SET AppPlan = (SELECT MAX(AppPlan) 
                 FROM T_PayingControl pc 
                      INNER JOIN T_OemEnterpriseClaimed oec 
                              ON pc.OemClaimedSeq = oec.OemClaimedSeq 
                             AND pc.EnterpriseId = oec.EnterpriseId 
                WHERE pc.Seq = emc.PayingControlSeq)
;

/* AppPlan�ڍs(�}�X�^�ݒ�l�ł݂Ȃ�)(��3�b) */
UPDATE AT_EnterpriseMonthlyClosingInfo emc
SET AppPlan = (SELECT e.Plan FROM T_Enterprise e WHERE emc.EnterpriseId = e.EnterpriseId)
WHERE AppPlan IS NULL
;
