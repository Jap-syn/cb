DROP procedure IF EXISTS `procMigrateOrder`;

DELIMITER $$
CREATE PROCEDURE `procMigrateOrder`()
BEGIN
    /* 移行処理： 注文 */
    DECLARE updDttm     datetime;

    set updDttm = now();

    INSERT INTO `T_Order`
        (`OrderSeq`,
        `OrderId`,
        `ReceiptOrderDate`,
        `EnterpriseId`,
        `SiteId`,
        `UseAmount`,
        `AnotherDeliFlg`,
        `DataStatus`,
        `CloseReason`,
        `Incre_Status`,
        `Incre_AtnEnterpriseScore`,
        `Incre_AtnEnterpriseNote`,
        `Incre_BorderScore`,
        `Incre_BorderNote`,
        `Incre_LimitCheckScore`,
        `Incre_LimitCheckNote`,
        `Incre_ScoreTotal`,
        `Incre_DecisionDate`,
        `Incre_DecisionOpId`,
        `Dmi_Status`,
        `Dmi_ResponseCode`,
        `Dmi_DecisionDate`,
        `Chg_Status`,
        `Chg_FixedDate`,
        `Chg_DecisionDate`,
        `Chg_ExecDate`,
        `Chg_ChargeAmount`,
        `Rct_RejectFlg`,
        `Rct_RejectReason`,
        `Rct_Status`,
        `Cnl_CantCancelFlg`,
        `Cnl_Status`,
        `Dmg_DecisionFlg`,
        `Dmg_DecisionDate`,
        `Dmg_DecisionAmount`,
        `Dmg_DecisionReason`,
        `Ent_OrderId`,
        `Ent_Note`,
        `Incre_Note`,
        `Dmi_ResponseNote`,
        `RegistDate`,
        `Chg_Seq`,
        `Bekkan`,
        `Dmi_DecSeqId`,
        `Rct_MailFlg`,
        `StopClaimFlg`,
        `MailPaymentSoonDate`,
        `MailLimitPassageDate`,
        `MailLimitPassageCount`,
        `ReturnClaimFlg`,
        `RemindClass`,
        `TouchHistoryFlg`,
        `BriefNote`,
        `LonghandLetter`,
        `VisitFlg`,
        `FinalityCollectionMean`,
        `FinalityRemindDate`,
        `FinalityRemindOpId`,
        `PromPayDate`,
        `ClaimStopReleaseDate`,
        `LetterClaimStopFlg`,
        `MailClaimStopFlg`,
        `InstallmentPlanAmount`,
        `OutOfAmends`,
        `OrderRegisterMethod`,
        `ApiUserId`,
        `CreditConditionMatchData`,
        `Cnl_ReturnSaikenCancelFlg`,
        `PrintedTransBeforeCancelled`,
        `Deli_ConfirmArrivalFlg`,
        `Deli_ConfirmArrivalDate`,
        `CombinedClaimTargetStatus`,
        `CombinedClaimParentFlg`,
        `Jintec_Flags`,
        `OemId`,
        `Oem_OrderId`,
        `Oem_Note`,
        `OemBadDebtSeq`,
        `OemBadDebtType`,
        `T_OrderClass`,
        `T_OrderAutoCreditJudgeClass`,
        `ServiceTargetClass`,
        `ServiceExpectedDate`,
        `DailySummaryFlg`,
        `PendingReasonCode`,
        `ClaimSendingClass`,
        `P_OrderSeq`,
        `CancelBefDataStatus`,
        `Tel30DaysFlg`,
        `Tel90DaysFlg`,
        `NewSystemFlg`,
        `CreditReplyDate`,
        `OemClaimTransDate`,
        `OemClaimTransFlg`,
        `ConfirmWaitingFlg`,
        `CreditNgHiddenFlg`,
        `Incre_JudgeScoreTotal`,
        `Incre_CoreScoreTotal`,
        `Incre_ItemScoreTotal`,
        `Incre_NoteScore`,
        `Incre_PastOrderScore`,
        `Incre_UnpaidScore`,
        `Incre_NonPaymentScore`,
        `Incre_IdentityDocumentScore`,
        `Incre_MischiefCancelScore`,
        `Chg_NonChargeFlg`,
        `Dmg_DecisionUseAmount`,
        `Dmg_DecisionClaimFee`,
        `Dmg_DecisionDamageInterestAmount`,
        `Dmg_DecisionAdditionalClaimFee`,
        `ReverseOrderId`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `T_Order`.`OrderSeq`,
        `T_Order`.`OrderId`,
        `T_Order`.`ReceiptOrderDate`,
        `T_Order`.`EnterpriseId`,
        `T_Order`.`SiteId`,
        `T_Order`.`UseAmount`,
        `T_Order`.`AnotherDeliFlg`,
        CASE `T_Order`.`DataStatus` = 51 AND `T_Order`.`Clm_F_LimitDate` < '2013-12-01'
           WHEN true THEN 91
           ELSE `T_Order`.`DataStatus`
           END,
        CASE `T_Order`.`DataStatus` = 51 AND `T_Order`.`Clm_F_LimitDate` < '2013-12-01'
            WHEN true THEN 4
            ELSE `T_Order`.`CloseReason`
            END,
        `T_Order`.`Incre_Status`,
        `T_Order`.`Incre_AtnEnterpriseScore`,
        `T_Order`.`Incre_AtnEnterpriseNote`,
        `T_Order`.`Incre_BorderScore`,
        `T_Order`.`Incre_BorderNote`,
        `T_Order`.`Incre_LimitCheckScore`,
        `T_Order`.`Incre_LimitCheckNote`,
        `T_Order`.`Incre_ScoreTotal`,
        `T_Order`.`Incre_DecisionDate`,
        `T_Order`.`Incre_DecisionOpId`,
        `T_Order`.`Dmi_Status`,
        `T_Order`.`Dmi_ResponseCode`,
        `T_Order`.`Dmi_DecisionDate`,
        `T_Order`.`Chg_Status`,
        `T_Order`.`Chg_FixedDate`,
        `T_Order`.`Chg_DecisionDate`,
        `T_Order`.`Chg_ExecDate`,
        `T_Order`.`Chg_ChargeAmount`,
        `T_Order`.`Rct_RejectFlg`,
        `T_Order`.`Rct_RejectReason`,
        `T_Order`.`Rct_Status`,
        `T_Order`.`Cnl_CantCancelFlg`,
        `T_Order`.`Cnl_Status`,
        CASE
            WHEN `T_Order`.`DataStatus` = 51 AND `T_Order`.`Clm_F_LimitDate` < '2013-12-01'
                THEN  1
            ELSE
                NULL
        END,
        CASE
            WHEN `T_Order`.`DataStatus` = 51 AND `T_Order`.`Clm_F_LimitDate` < '2013-12-01'
                THEN LAST_DAY(DATE_ADD(`T_Order`.`Clm_F_LimitDate`, interval 730 day))
            ELSE
                NULL
         END,
         CASE
            WHEN `T_Order`.`DataStatus` = 51 AND `T_Order`.`Clm_F_LimitDate` < '2013-12-01'
                THEN    `T_Order`.`UseAmount` - `T_Order`.`Rct_DepositAmount`
            ELSE
                NULL
         END,
         CASE
            WHEN `T_Order`.`DataStatus` = 51 AND `T_Order`.`Clm_F_LimitDate` < '2013-12-01' AND `T_Order`.`Rct_DepositAmount` <> 0 THEN
                1
            WHEN `T_Order`.`DataStatus` = 51 AND `T_Order`.`Clm_F_LimitDate` < '2013-12-01' THEN
                2
            ELSE
                0
        END,
        `T_Order`.`Ent_OrderId`,
        `T_Order`.`Ent_Note`,
        `T_Order`.`Incre_Note`,
        `T_Order`.`Dmi_ResponseNote`,
        `T_Order`.`RegistDate`,
        `T_Order`.`Chg_Seq`,
        `T_Order`.`Bekkan`,
        `T_Order`.`Dmi_DecSeqId`,
        `T_Order`.`Rct_MailFlg`,
        `T_Order`.`StopClaimFlg`,
        `T_Order`.`MailPaymentSoonDate`,
        `T_Order`.`MailLimitPassageDate`,
        `T_Order`.`MailLimitPassageCount`,
        `T_Order`.`ReturnClaimFlg`,
        `T_Order`.`RemindClass`,
        `T_Order`.`TouchHistoryFlg`,
        `T_Order`.`BriefNote`,
        `T_Order`.`LonghandLetter`,
        `T_Order`.`VisitFlg`,
        `T_Order`.`FinalityCollectionMean`,
        `T_Order`.`FinalityRemindDate`,
        `T_Order`.`FinalityRemindOpId`,
        `T_Order`.`PromPayDate`,
        `T_Order`.`ClaimStopReleaseDate`,
        `T_Order`.`LetterClaimStopFlg`,
        `T_Order`.`MailClaimStopFlg`,
        `T_Order`.`InstallmentPlanAmount`,
        `T_Order`.`OutOfAmends`,
        `T_Order`.`OrderRegisterMethod`,
        `T_Order`.`ApiUserId`,
        `T_Order`.`CreditConditionMatchData`,
        `T_Order`.`Cnl_ReturnSaikenCancelFlg`,
        `T_Order`.`PrintedTransBeforeCancelled`,
        `T_Order`.`Deli_ConfirmArrivalFlg`,
        `T_Order`.`Deli_ConfirmArrivalDate`,
        `T_Order`.`CombinedClaimTargetStatus`,
        `T_Order`.`CombinedClaimParentFlg`,
        `T_Order`.`Jintec_Flags`,
        `T_Order`.`OemId`,
        `T_Order`.`Oem_OrderId`,
        `T_Order`.`Oem_Note`,
        `T_Order`.`OemBadDebtSeq`,
        `T_Order`.`OemBadDebtType`,
        0,
        0,
        0,
        NULL,
         CASE
            WHEN `T_PayingAndSales`.`ClearConditionForCharge` = 1 
                THEN   1
            ELSE
                       0
         END,
        NULL,
        CASE                         --  請求書送付区分
            WHEN `T_Enterprise`.`SelfBillingKey` IS NULL
                THEN 21
            ELSE
                CASE
                    WHEN `T_ClaimHistory`.`EnterpriseBillingCode` IS NULL
                        THEN 12
                    ELSE
                        11
                END
        END,
        `T_Order`.`OrderSeq`,         -- 親注文SEQ
        NULL,
        0,
        0,
        0,                            --  新旧システムフラグ
        NULL,                         --  与信返信日時
        CASE WHEN `T_Order`.`DataStatus` = 51 AND `T_PayingAndSales`.`ClearConditionForCharge` = 1 AND DATE_FORMAT(DATE_ADD(`T_Order`.`Clm_F_ClaimDate`, interval +14 day), '%Y-%m-%d')  <= '2015-11-30' THEN -- 債権移管日
                -- 債権移管が必要
                CASE WHEN IFNULL(`T_Enterprise`.`OemId`, 0) = 0 THEN NULL
                     WHEN IFNULL(`T_Enterprise`.`OemId`, 0) = 1 THEN '2015-11-30'
                     WHEN `T_Order`.`DataStatus` = 51 AND `T_Order`.`Clm_F_LimitDate` < '2013-12-01' THEN LAST_DAY(DATE_ADD(`T_Order`.`Clm_F_LimitDate`, interval 730 day))
                     ELSE NULL
                END
             ELSE
                -- 債権移管は不要
                NULL
        END,
        CASE WHEN `T_Order`.`DataStatus` = 51 AND `T_PayingAndSales`.`ClearConditionForCharge` = 1 AND DATE_FORMAT(DATE_ADD(`T_Order`.`Clm_F_ClaimDate`, interval +14 day), '%Y-%m-%d')  <= '2015-11-30' THEN -- 債権移管日
                -- 債権移管が必要
                CASE WHEN IFNULL(`T_Enterprise`.`OemId`, 0) = 0 THEN 0
                     WHEN IFNULL(`T_Enterprise`.`OemId`, 0) = 1 THEN 1
                     WHEN `T_Order`.`DataStatus` = 51 AND `T_Order`.`Clm_F_LimitDate` < '2013-12-01' THEN 1
                     ELSE 0
                END
             ELSE
                -- 債権移管は不要
                0
        END,
        CASE
            WHEN `T_ClaimHistory`.PrintedFlg = 0 THEN 1
            ELSE 0
        END , -- ジョブ転送中の場合は１
        0,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        0,
        CASE WHEN `T_Order`.`DataStatus` = 51 AND `T_Order`.`Clm_F_LimitDate` < '2013-12-01' THEN `T_Order`.`UseAmount`
             ELSE NULL
        END, -- Dmg_DecisionUseAmount
        CASE WHEN `T_Order`.`DataStatus` = 51 AND `T_Order`.`Clm_F_LimitDate` < '2013-12-01' THEN `T_Order`.`Clm_L_ClaimFee`
             ELSE NULL
        END, -- Dmg_DecisionClaimFee
        CASE WHEN `T_Order`.`DataStatus` = 51 AND `T_Order`.`Clm_F_LimitDate` < '2013-12-01' THEN `T_Order`.`Clm_L_DamageInterestAmount`
             ELSE NULL
        END, -- Dmg_DecisionDamageInterestAmount
        NULL,
        REVERSE(`T_Order`.`OrderId`), -- ReverseOrderId
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_Order`
LEFT OUTER JOIN `coraldb_ikou`.`T_ClaimHistory`
ON `T_Order`.`OrderSeq` = `T_ClaimHistory`.`OrderSeq`
AND (T_ClaimHistory.ClaimSeq IN 
(SELECT MAX(ClaimSeq) FROM `coraldb_ikou`.`T_ClaimHistory` hist
WHERE T_Order.OrderSeq = hist.OrderSeq
))
LEFT OUTER JOIN `coraldb_ikou`.`T_Enterprise`
    ON `T_Enterprise`.`EnterpriseId` = `T_Order`.`EnterpriseId`
LEFT OUTER JOIN `coraldb_ikou`.`T_PayingAndSales`
    ON `T_PayingAndSales`.`OrderSeq` = `T_Order`.`OrderSeq`
ORDER BY T_Order.OrderSeq ASC;


-- 何故か任意注文番号がおかしくなるので暫定対応。UPDATE文なら問題ないはず
UPDATE T_Order o
SET    Ent_OrderId = ( SELECT MAX( Ent_OrderId ) FROM coraldb_ikou.T_Order WHERE OrderSeq = o.OrderSeq );

END
$$

DELIMITER ;

