DROP procedure IF EXISTS `procMigrateReceiptControl`;

DELIMITER $$
CREATE PROCEDURE `procMigrateReceiptControl`()
BEGIN
    /* 移行処理：入金管理 */
    /* 2015-08-18 日次更新フラグ　セット */
    /* 2015-08-19 追加項目　セット */
    
    DECLARE updDttm     datetime;
    
    SET updDttm = now();
    
    -- （注文.データステータス＝91 AND  注文.クローズ理由<>3：与信NGクローズ）　AND　注文．顧客入金－入金額 ＞ 0 の場合
    INSERT INTO `T_ReceiptControl`(
        `ReceiptProcessDate`,               -- 入金処理日
        `ReceiptDate`,                      -- 顧客入金日
        `ReceiptClass`,                     -- 入金科目（入金方法）
        `ReceiptAmount`,                    -- 金額
        `ClaimId`,                          -- 請求ID
        `OrderSeq`,                         -- 注文SEQ
        `CheckingUseAmount`,                -- 消込情報－利用額
        `CheckingClaimFee`,                 -- 消込情報－請求手数料
        `CheckingDamageInterestAmount`,     -- 消込情報－遅延損害金
        `CheckingAdditionalClaimFee`,       -- 消込情報－請求追加手数料
        `DailySummaryFlg`,                  -- 日次更新フラグ　　2015-08-18
        `BranchBankId`,                     -- 銀行支店ID    　　2015-08-19
        `DepositDate`,                      -- 入金予定日    　　2015-08-19
        `ReceiptAgentId`,                   -- 収納代行会社ID　　2015-08-19
        `RegistDate`,                       -- 登録日時
        `RegistId`,                         -- 登録者
        `UpdateDate`,                       -- 更新日時
        `UpdateId`,                         -- 更新者
        `ValidFlg`                          -- 有効フラグ
    ) SELECT 
        `T_Order`.`Rct_ReceiptProcessDate`,     -- 入金処理日
        `T_Order`.`Rct_ReceiptDate`,            -- 顧客入金日
        `T_Order`.`Rct_ReceiptMethod`,          -- 入金科目（入金方法）
        `T_Order`.`Rct_ReceiptAmount`,          -- 金額
        `T_ClaimControl`.`ClaimId`,             -- 請求ID
        `T_Order`.`OrderSeq`,                   -- 注文SEQ
        `T_ClaimControl`.`CheckingUseAmount`,   -- 消込情報－利用額
        CASE `T_Order`.`Clm_L_ClaimFee` <= (`T_Order`.`Rct_ReceiptAmount` - `T_Order`.`UseAmount` - `T_ClaimControl`.`MinDamageInterestAmount`)
            WHEN true THEN `T_Order`.`Clm_L_ClaimFee`
            ELSE `T_Order`.`Rct_ReceiptAmount` - `T_Order`.`UseAmount` - `T_ClaimControl`.`MinDamageInterestAmount`
        END,                                    -- 消込情報－請求手数料
        CASE (`T_Order`.`Clm_L_ClaimFee` + `T_ClaimControl`.`MinDamageInterestAmount`) <= (`T_Order`.`Rct_ReceiptAmount` - `T_Order`.`UseAmount`)
            WHEN true THEN `T_ClaimControl`.`MinDamageInterestAmount`
            ELSE `T_Order`.`Rct_ReceiptAmount` - `T_Order`.`UseAmount` - `T_ClaimControl`.`MinClaimFee`
        END,                                    -- 消込情報－遅延損害金
        0,                                      -- 消込情報－請求追加手数料
        1,                                      -- 日次更新フラグ   2015-08-18
        NULL,                                   -- 銀行支店ID       2015-08-19
        `T_Order`.`Rct_AccountPaymentDate`,     -- 入金予定日       2015-08-19
        CASE WHEN `T_Order`.`Rct_ReceiptMethod` = 1   -- 収納代行会社ID   2015-08-19
             THEN  4
             ELSE  NULL
        END,  
        updDttm,                                -- 登録日時
        9,                                      -- 登録者
        updDttm,                                -- 更新日時
        9,                                      -- 更新者
        1                                       -- 有効フラグ
    FROM `coraldb_ikou`.`T_Order`
    INNER JOIN `T_ClaimControl`
    ON `T_Order`.`OrderSeq` = `T_ClaimControl`.`OrderSeq`
    WHERE `T_Order`.`DataStatus` = 91
    AND `T_Order`.`CloseReason` <> 3
    AND `T_Order`.`Rct_ReceiptAmount` > 0;
    
    
/* ↓移行の最後のSPで作成する(20150905_1136_suzuki_h)
    -- （注文.データステータス＝51 ）　AND　注文．分割支払済み金額 ＞ 0 の場合
    INSERT INTO `T_ReceiptControl`(
        `ReceiptProcessDate`,               -- 入金処理日
        `ReceiptDate`,                      -- 顧客入金日
        `ReceiptClass`,                     -- 入金科目（入金方法）
        `ReceiptAmount`,                    -- 金額
        `ClaimId`,                          -- 請求ID
        `OrderSeq`,                         -- 注文SEQ
        `CheckingUseAmount`,                -- 消込情報－利用額
        `CheckingClaimFee`,                 -- 消込情報－請求手数料
        `CheckingDamageInterestAmount`,     -- 消込情報－遅延損害金
        `CheckingAdditionalClaimFee`,       -- 消込情報－請求追加手数料
        `DailySummaryFlg`,                  -- 日次更新フラグ　　2015-08-18
        `BranchBankId`,                     -- 銀行支店ID    　　2015-08-19
        `DepositDate`,                      -- 入金予定日    　　2015-08-19
        `ReceiptAgentId`,                   -- 収納代行会社ID　　2015-08-19
        `RegistDate`,                       -- 登録日時
        `RegistId`,                         -- 登録者
        `UpdateDate`,                       -- 更新日時
        `UpdateId`,                         -- 更新者
        `ValidFlg`                          -- 有効フラグ
    ) SELECT
        `T_Order`.`Clm_L_ClaimDate`,        -- 入金処理日
        `T_Order`.`Clm_L_ClaimDate`,        -- 顧客入金日
        3,                                  -- 入金科目（入金方法）
        `T_Order`.`InstallmentPlanAmount`,  -- 金額
        `T_ClaimControl`.`ClaimId`,         -- 請求ID
        `T_Order`.`OrderSeq`,               -- 注文SEQ
        CASE  `T_Order`.`InstallmentPlanAmount` <= (`T_ClaimControl`.`MinClaimFee` + `T_ClaimControl`.`MinDamageInterestAmount`)
            WHEN true THEN 0
            ELSE `T_Order`.`InstallmentPlanAmount` - (`T_ClaimControl`.`MinClaimFee` + `T_ClaimControl`.`CheckingDamageInterestAmount`)
        END,                                -- 消込情報－利用額
        CASE `T_Order`.`InstallmentPlanAmount` <= `T_ClaimControl`.`MinClaimFee`
            WHEN true THEN `T_Order`.`InstallmentPlanAmount`
            ELSE `T_ClaimControl`.`MinClaimFee`
        END,                                -- 消込情報－請求手数料
        CASE WHEN `T_Order`.`InstallmentPlanAmount` <= `T_ClaimControl`.`MinClaimFee`
                THEN 0
            WHEN `T_Order`.`InstallmentPlanAmount` <= (`T_ClaimControl`.`MinClaimFee` + `T_ClaimControl`.`MinDamageInterestAmount`)
                THEN `T_Order`.`InstallmentPlanAmount` - `T_ClaimControl`.`MinClaimFee`
            ELSE `T_ClaimControl`.`MinDamageInterestAmount`
        END,                                -- 消込情報－遅延損害金
        0,                                  -- 消込情報－請求追加手数料
        1,                                      -- 日次更新フラグ   2015-08-18
        NULL,                                   -- 銀行支店ID       2015-08-19
        `T_Order`.`Rct_AccountPaymentDate`,     -- 入金予定日       2015-08-19
        CASE WHEN `T_Order`.`Rct_ReceiptMethod` = 1   -- 収納代行会社ID   2015-08-19
             THEN  4
             ELSE  NULL
        END,  
        updDttm,                            -- 登録日時
        9,                                  -- 登録者
        updDttm,                            -- 更新日時
        9,                                  -- 更新者
        1                                   -- 有効フラグ
    FROM `coraldb_ikou`.`T_Order`
    INNER JOIN `T_ClaimControl`
    ON `T_Order`.`OrderSeq` = `T_ClaimControl`.`OrderSeq`
    WHERE `T_Order`.`DataStatus` = 51
    AND `T_Order`.`InstallmentPlanAmount` > 0;
*/

END$$

DELIMITER ;
