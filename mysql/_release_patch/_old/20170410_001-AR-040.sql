/* AT_EnterpriseMonthlyClosingInfo項目追加(約10秒) */
ALTER TABLE `AT_EnterpriseMonthlyClosingInfo` 
ADD COLUMN `AppPlan` INT NULL AFTER `PayingControlSeq`;

/* OEM分のAppPlan移行(正確に設定出来るものは設定)(約10秒) */
UPDATE AT_EnterpriseMonthlyClosingInfo emc
SET AppPlan = (SELECT MAX(AppPlan) 
                 FROM T_PayingControl pc 
                      INNER JOIN T_OemEnterpriseClaimed oec 
                              ON pc.OemClaimedSeq = oec.OemClaimedSeq 
                             AND pc.EnterpriseId = oec.EnterpriseId 
                WHERE pc.Seq = emc.PayingControlSeq)
;

/* AppPlan移行(マスタ設定値でみなし)(約3秒) */
UPDATE AT_EnterpriseMonthlyClosingInfo emc
SET AppPlan = (SELECT e.Plan FROM T_Enterprise e WHERE emc.EnterpriseId = e.EnterpriseId)
WHERE AppPlan IS NULL
;
