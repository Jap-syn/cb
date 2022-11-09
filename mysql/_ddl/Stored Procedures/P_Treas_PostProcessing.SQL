DROP PROCEDURE IF EXISTS P_Treas_PostProcessing;

DELIMITER $$

CREATE PROCEDURE P_Treas_PostProcessing ( IN pi_user_id INT )
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名       ：  P_Treas_PostProcessing
 *
 * 概要             ：  会計後処理
 *
 * 引数             ：  [I/ ]pi_user_id        ﾕｰｻﾞｰID
 *
 * 履歴             ：  2015/10/01  NDC 新規作成
 *
 *****************************************************************************/
BEGIN
    -- ------------------------------
    -- 変数宣言
    -- ------------------------------
    DECLARE v_BusinessDate      DATE;                   -- 業務日付

    -- ------------------------------
    -- 変数初期化
    -- ------------------------------
    SET v_BusinessDate      = F_GetSystemProperty( '[DEFAULT]', 'systeminfo', 'BusinessDate' );       -- 業務日付

    -- ①貸倒債権一覧
    UPDATE  AT_Order, T_Order
    SET     AT_Order.Dmg_DailySummaryFlg    = 1                         -- 損害確定日次更新ﾌﾗｸﾞ
        ,   T_Order.UpdateDate              = now()                     -- 更新日時
        ,   T_Order.UpdateId                = pi_user_id                -- 更新者
    WHERE   T_Order.OrderSeq = AT_Order.OrderSeq
    AND     T_Order.Dmg_DecisionDate IS NOT NULL                        -- 損害確定日
    AND     T_Order.Dmg_DecisionDate <= v_BusinessDate                  -- 損害確定日=<業務日付
    AND     T_Order.Dmg_DecisionDate > (v_BusinessDate - INTERVAL 7 DAY)
    AND     T_Order.Dmg_DecisionFlg = 1                                 -- 損害確定ﾌﾗｸﾞ
    AND     AT_Order.Dmg_DailySummaryFlg = 0
    AND     T_Order.ValidFlg = 1;

    -- ②OEM債権移管
    UPDATE  T_Order
    SET     OemClaimTransFlg    = 1                                     -- 債権移管日次更新ﾌﾗｸﾞ
        ,   UpdateDate          = now()                                 -- 更新日時
        ,   UpdateId            = pi_user_id                            -- 更新者
    WHERE   OemClaimTransDate IS NOT NULL                               -- 債権移管日
    AND     OemClaimTransDate  <= v_BusinessDate
    AND     OemClaimTransDate  > (v_BusinessDate - INTERVAL 7 DAY)
    AND     OemClaimTransFlg    = 0                                     -- 債権移管日次更新ﾌﾗｸﾞ
    AND     ValidFlg = 1;
    
    -- 債権移管後にｷｬﾝｾﾙされた注文は債権移管もなかったことにする。
    UPDATE  T_Order, T_Cancel
    SET     T_Order.OemClaimTransFlg    = 0                             -- 債権移管日次更新ﾌﾗｸﾞ
        ,   T_Order.OemClaimTransDate   = NULL                          -- 債権移管日
        ,   T_Order.UpdateDate          = now()                         -- 更新日時
        ,   T_Order.UpdateId            = pi_user_id                    -- 更新者
    WHERE   T_Order.OrderSeq            = T_Cancel.OrderSeq
    AND     T_Order.OemClaimTransDate IS NOT NULL
    AND     T_Order.OemClaimTransDate  <= v_BusinessDate
    AND     T_Order.ValidFlg            = 1
    AND     T_Cancel.ApprovalDate >= v_BusinessDate AND T_Cancel.ApprovalDate < (v_BusinessDate + INTERVAL 1 DAY)
    AND     T_Cancel.ApproveFlg         = 1
    AND     T_Cancel.ValidFlg           = 1;

    -- ③売上明細一覧
    UPDATE  T_Cancel
    SET     DailySummaryFlg = 1                                         -- 日次更新ﾌﾗｸﾞ
        ,   UpdateDate      = now()                                     -- 更新日時
        ,   UpdateId        = pi_user_id                                -- 更新者
    WHERE   DATE_FORMAT(ApprovalDate, '%Y-%m-%d') <= v_BusinessDate      -- ｷｬﾝｾﾙ承認日
    AND     ApproveFlg       = 1                                         -- ｷｬﾝｾﾙ承認ﾌﾗｸﾞ（0：ｷｬﾝｾﾙ未承認 1：ｷｬﾝｾﾙ承認）
    AND     DailySummaryFlg = 0
    AND     ValidFlg = 1;

    -- ④売上明細一覧
    UPDATE  T_PayingAndSales, AT_PayingAndSales
    SET     AT_PayingAndSales.DailySummaryFlg   = 1                     -- 日別更新ﾌﾗｸﾞ
        ,   T_PayingAndSales.UpdateDate         = now()                 -- 更新日時
        ,   T_PayingAndSales.UpdateId           = pi_user_id            -- 更新者
    WHERE   T_PayingAndSales.Seq = AT_PayingAndSales.Seq
    AND     T_PayingAndSales.ClearConditionForCharge = 1                 -- 立替条件ｸﾘｱﾌﾗｸﾞ
    AND     T_PayingAndSales.ClearConditionDate <= v_BusinessDate        -- 立替条件ｸﾘｱ日
    AND     AT_PayingAndSales.DailySummaryFlg = 0
    AND     AT_PayingAndSales.ATUriType <> 99
    AND     T_PayingAndSales.ValidFlg = 1;

    -- ⑤未収金日計/仮払金日計/入金先ﾄｰﾀﾙ表
    UPDATE  T_RepaymentControl, AT_RepaymentControl
    SET     AT_RepaymentControl.DailySummaryFlg     = 1                 -- 日次更新ﾌﾗｸﾞ
        ,   T_RepaymentControl.UpdateDate           = now()             -- 更新日時
        ,   T_RepaymentControl.UpdateId             = pi_user_id        -- 更新者
    WHERE   T_RepaymentControl.RepaySeq = AT_RepaymentControl.RepaySeq
    AND     AT_RepaymentControl.DailySummaryFlg = 0
    AND     T_RepaymentControl.ValidFlg = 1
    AND     DATE_FORMAT( T_RepaymentControl.DecisionDate, '%Y-%m-%d' ) <= v_BusinessDate;

    -- ⑥未収金日計/仮払金日計/入金先ﾄｰﾀﾙ表
    UPDATE  T_ReceiptControl
    SET     DailySummaryFlg             = 1                             -- 日次更新ﾌﾗｸﾞ
        ,   UpdateDate                  = now()                         -- 更新日時
        ,   UpdateId                    = pi_user_id                    -- 更新者
    WHERE   DailySummaryFlg = 0
    AND     ValidFlg = 1
    AND     DATE_FORMAT( ReceiptProcessDate, '%Y-%m-%d' ) <= v_BusinessDate;

    -- ⑦未収金日計/仮払金日計/入金先ﾄｰﾀﾙ表/再発行手数料明細
    UPDATE  T_SundryControl
    SET     DailySummaryFlg = 1
        ,   UpdateDate                  = now()                         -- 更新日時
        ,   UpdateId                    = pi_user_id                    -- 更新者
    WHERE   DailySummaryFlg = 0
    AND     ValidFlg = 1
    AND     ProcessDate <= v_BusinessDate;

    -- ⑧未収金日計/仮払金日計
    UPDATE  T_EnterpriseReceiptHistory, AT_EnterpriseReceiptHistory
    SET     AT_EnterpriseReceiptHistory.DailySummaryFlg = 1
        ,   T_EnterpriseReceiptHistory.UpdateDate       = now()         -- 更新日時
        ,   T_EnterpriseReceiptHistory.UpdateId         = pi_user_id    -- 更新者
    WHERE   T_EnterpriseReceiptHistory.EntRcptSeq = AT_EnterpriseReceiptHistory.EntRcptSeq
    AND     T_EnterpriseReceiptHistory.ValidFlg = 1
    AND     AT_EnterpriseReceiptHistory.DailySummaryFlg = 0
    AND     DATE_FORMAT( ReceiptProcessDate, '%Y-%m-%d' ) <= v_BusinessDate;

    -- ⑨調整金一覧
    UPDATE  T_AdjustmentAmount, AT_AdjustmentAmount, T_PayingControl
    SET     AT_AdjustmentAmount.DailySummaryFlg         = 1
        ,   T_AdjustmentAmount.UpdateDate               = now()         -- 更新日時
        ,   T_AdjustmentAmount.UpdateId                 = pi_user_id    -- 更新者
    WHERE   T_AdjustmentAmount.PayingControlSeq = AT_AdjustmentAmount.PayingControlSeq
    AND     T_AdjustmentAmount.PayingControlSeq = T_PayingControl.Seq
    AND     T_AdjustmentAmount.SerialNumber     = AT_AdjustmentAmount.SerialNumber
    AND     AT_AdjustmentAmount.DailySummaryFlg = 0
    AND     T_AdjustmentAmount.ValidFlg = 1
    AND     T_PayingControl.PayingControlStatus = 1
    AND     T_PayingControl.ExecDate <= v_BusinessDate;

    -- ⑩無保証立替金戻し明細/精算日計
    UPDATE  T_PayingControl, AT_PayingControl
    SET     AT_PayingControl.DailySummaryFlg        = 1
        ,   T_PayingControl.UpdateDate              = now()             -- 更新日時
        ,   T_PayingControl.UpdateId                = pi_user_id        -- 更新者
    WHERE   AT_PayingControl.Seq = T_PayingControl.Seq
    AND     T_PayingControl.PayingControlStatus = 1
    AND     AT_PayingControl.DailySummaryFlg = 0
    AND     T_PayingControl.ValidFlg = 1
    AND     T_PayingControl.ExecDate <= v_BusinessDate;

    -- ⑪精算日計
    UPDATE  T_OemClaimed, AT_OemClaimed
    SET     AT_OemClaimed.DailySummaryFlg           = 1
        ,   T_OemClaimed.UpdateDate                 = now()             -- 更新日時
        ,   T_OemClaimed.UpdateId                   = pi_user_id        -- 更新者
    WHERE   T_OemClaimed.OemClaimedSeq = AT_OemClaimed.OemClaimedSeq
    AND     T_OemClaimed.ValidFlg = 1
    AND     AT_OemClaimed.DailySummaryFlg = 0
    AND     T_OemClaimed.PayingControlStatus = 1
    AND     T_OemClaimed.ExecDate <= v_BusinessDate;

    -- ⑫売上明細一覧
    UPDATE  AT_EnterpriseMonthlyClosingInfo
    SET     DailySummaryFlg             = 1
        ,   UpdateDate                  = now()                         -- 更新日時
        ,   UpdateId                    = pi_user_id                    -- 更新者
    WHERE   ValidFlg = 1
    AND     DailySummaryFlg =  0
    AND     ClosingMonthly  <= v_BusinessDate;

END
$$

DELIMITER ;
