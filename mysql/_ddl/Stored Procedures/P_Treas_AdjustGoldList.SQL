DROP PROCEDURE IF EXISTS P_Treas_AdjustGoldList;

DELIMITER $$

CREATE PROCEDURE P_Treas_AdjustGoldList ( IN pi_user_id INT )
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名       ：  P_Treas_AdjustGoldList
 *
 * 概要             ：  調整金一覧
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
    SET v_BusinessDate      = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'BusinessDate');       -- 業務日付

    -- ------------------------------
    -- 1.直営加盟店分の調整金一覧
    -- ------------------------------
    INSERT INTO AT_AdjustGoldList (
         DailyMonthlyFlg
        ,ProcessingDate
        ,AdjustGoldFixedDate
        ,OemId
        ,OemNameKj
        ,EnterpriseId
        ,EnterpriseNameKj
        ,AccountingCourses
        ,OrderSeq
        ,OrderId
        ,AdjustAmount
        ,AdvancesDate
        ,RegistDate
        ,RegistId
        ,UpdateDate
        ,UpdateId
        ,ValidFlg )
    SELECT 
         0                                                                      -- 日次･月次区分
        ,v_BusinessDate                                                         -- 処理日付
        ,pc.DecisionDate                                                        -- 調整金確定日付
        ,c2.Class3                                                              -- OEMID
        ,c2.Class2                                                              -- OEM先名
        ,pc.EnterpriseId                                                        -- 加盟店ID
        ,e.EnterpriseNameKj                                                     -- 加盟店名
        ,c1.Class2                                                              -- 会計科目
        ,aa.OrderSeq                                                            -- 注文Seq
        ,o.OrderId                                                              -- 注文ID
        ,aa.AdjustmentAmount                                                    -- 調整額
        ,pc.ExecScheduleDate                                                    -- 立替実行予定日
        ,NOW()                                                                  -- ｼｽﾃﾑ日時
        ,pi_user_id                                                             -- ﾕｰｻﾞｰID
        ,NOW()                                                                  -- ｼｽﾃﾑ日時
        ,pi_user_id                                                             -- ﾕｰｻﾞｰID
        ,1                                                                      -- 有効ﾌﾗｸﾞ:有効
    FROM        T_AdjustmentAmount aa
    INNER JOIN  AT_AdjustmentAmount at_aa 
    ON      at_aa.PayingControlSeq  = aa.PayingControlSeq
    AND     at_aa.SerialNumber      = aa.SerialNumber
    INNER JOIN  T_PayingControl pc 
    ON      pc.Seq                  = aa.PayingControlSeq
    AND     pc.PayingControlStatus  = 1
    AND     pc.ValidFlg             = 1
    INNER JOIN  AT_PayingControl at_pc 
    ON      pc.Seq                  = at_pc.Seq
    LEFT JOIN  T_Order o
    ON      o.OrderSeq              = aa.OrderSeq
    LEFT JOIN   M_Code c1
    ON      c1.CodeId               = 89
    AND     c1.KeyCode              = aa.ItemCode
    INNER JOIN  M_Code c2
    ON      c2.CodeId               = 160
    AND     c2.KeyCode              = IFNULL(pc.OemId, 0)
    LEFT JOIN   T_Enterprise e 
    ON      e.EnterpriseId          = pc.EnterpriseId
    WHERE   at_pc.DailySummaryFlg   = 0
    AND     aa.ValidFlg             = 1
    AND     c2.Class1               = 0
    AND     pc.ExecDate             <= v_BusinessDate;

    -- ------------------------------
    -- 2.OEM分の調整金一覧
    -- ------------------------------
    
    -- 2.1 OEM精算分の調整金一覧
    INSERT INTO AT_AdjustGoldList (
         DailyMonthlyFlg
        ,ProcessingDate
        ,AdjustGoldFixedDate
        ,OemId
        ,OemNameKj
        ,EnterpriseId
        ,EnterpriseNameKj
        ,AccountingCourses
        ,OrderSeq
        ,OrderId
        ,AdjustAmount
        ,AdvancesDate
        ,RegistDate
        ,RegistId
        ,UpdateDate
        ,UpdateId
        ,ValidFlg )
    SELECT 
         0                                                                      -- 日次･月次区分
        ,v_BusinessDate                                                         -- 処理日付
        ,oc.ProcessDate                                                         -- 調整金確定日付
        ,c2.Class3                                                              -- OEMID
        ,c2.Class2                                                              -- OEM先名
        ,o.EnterpriseId                                                         -- 加盟店ID
        ,e.EnterpriseNameKj                                                     -- 加盟店名
        ,c1.Class2                                                              -- 会計科目
        ,oaa.OrderSeq                                                           -- 注文Seq
        ,o.OrderId                                                              -- 注文ID
        ,oaa.AdjustmentAmount                                                   -- 調整額
        ,oc.SettlePlanDate                                                      -- 精算予定日
        ,NOW()                                                                  -- ｼｽﾃﾑ日時
        ,pi_user_id                                                             -- ﾕｰｻﾞｰID
        ,NOW()                                                                  -- ｼｽﾃﾑ日時
        ,pi_user_id                                                             -- ﾕｰｻﾞｰID
        ,1                                                                      -- 有効ﾌﾗｸﾞ:有効
    FROM        T_OemClaimed oc
    INNER JOIN  AT_OemClaimed at_oc
    ON      at_oc.OemClaimedSeq     = oc.OemClaimedSeq
    INNER JOIN  T_OemAdjustmentAmount oaa
    ON      oaa.OemClaimedSeq       = oc.OemClaimedSeq
    AND     oaa.ValidFlg            = 1
    LEFT JOIN   T_Order o
    ON      o.OrderSeq              = oaa.OrderSeq
    AND     o.ValidFlg              = 1
    LEFT JOIN   T_Enterprise e
    ON      e.EnterpriseId          = o.EnterpriseId
    LEFT JOIN   M_Code c1
    ON      c1.CodeId               = 89
    AND     c1.KeyCode              = oaa.ItemCode
    INNER JOIN  M_Code c2
    ON      c2.CodeId               = 160
    AND     c2.KeyCode              = IFNULL(oc.OemId, 0)
    WHERE   at_oc.DailySummaryFlg   = 0
    AND     oc.PayingControlStatus  = 1
    AND     oc.ValidFlg             = 1
    AND     oc.ExecDate             <= v_BusinessDate
    AND     c2.Class1               <> 0;

    -- 2.2 OEMの加盟店分の調整金一覧
    INSERT INTO AT_AdjustGoldList (
         DailyMonthlyFlg
        ,ProcessingDate
        ,AdjustGoldFixedDate
        ,OemId
        ,OemNameKj
        ,EnterpriseId
        ,EnterpriseNameKj
        ,AccountingCourses
        ,OrderSeq
        ,OrderId
        ,AdjustAmount
        ,AdvancesDate
        ,RegistDate
        ,RegistId
        ,UpdateDate
        ,UpdateId
        ,ValidFlg )
    SELECT 
         distinct
         0                                                                      -- 日次･月次区分
        ,v_BusinessDate                                                         -- 処理日付
        ,oc.ProcessDate                                                         -- 調整金確定日付
        ,c2.Class3                                                              -- OEMID
        ,c2.Class2                                                              -- OEM先名
        ,pc.EnterpriseId                                                        -- 加盟店ID
        ,e.EnterpriseNameKj                                                     -- 加盟店名
        ,c1.Class2                                                              -- 会計科目
        ,aa.OrderSeq                                                            -- 注文Seq
        ,o.OrderId                                                              -- 注文ID
        ,aa.AdjustmentAmount                                                    -- 調整額
        ,pc.ExecScheduleDate                                                    -- 立替実行予定日
        ,NOW()                                                                  -- ｼｽﾃﾑ日時
        ,pi_user_id                                                             -- ﾕｰｻﾞｰID
        ,NOW()                                                                  -- ｼｽﾃﾑ日時
        ,pi_user_id                                                             -- ﾕｰｻﾞｰID
        ,1                                                                      -- 有効ﾌﾗｸﾞ:有効
    FROM        T_OemClaimed oc
    INNER JOIN  AT_OemClaimed at_oc
    ON      at_oc.OemClaimedSeq     = oc.OemClaimedSeq
    INNER JOIN  T_PayingControl pc
    ON      pc.OemClaimedSeq        = oc.OemClaimedSeq
    AND     pc.ValidFlg             = 1
    INNER JOIN  T_AdjustmentAmount aa
    ON      aa.PayingControlSeq     = pc.Seq
    AND     aa.ValidFlg             = 1
    INNER JOIN  AT_AdjustmentAmount at_aa
    ON      at_aa.PayingControlSeq  = aa.PayingControlSeq
    LEFT JOIN   T_Order o
    ON      o.OrderSeq              = aa.OrderSeq
    AND     o.ValidFlg              = 1
    LEFT JOIN   T_Enterprise e
    ON      pc.EnterpriseId         = e.EnterpriseId
    LEFT JOIN   M_Code c1
    ON      c1.CodeId               = 89
    AND     c1.KeyCode              = aa.ItemCode
    INNER JOIN  M_Code c2
    ON      c2.CodeId               = 160
    AND     c2.KeyCode              = IFNULL(pc.OemId, 0)
    WHERE   at_oc.DailySummaryFlg   = 0
    AND     oc.PayingControlStatus  = 1
    AND     oc.ValidFlg             = 1
    AND     oc.ExecDate             <= v_BusinessDate
    AND     c2.Class1               <> 0;

END
$$

DELIMITER ;
