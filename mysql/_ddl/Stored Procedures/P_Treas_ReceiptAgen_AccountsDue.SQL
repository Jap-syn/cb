DROP PROCEDURE IF EXISTS P_Treas_ReceiptAgen_AccountsDue;

DELIMITER $$

CREATE PROCEDURE P_Treas_ReceiptAgen_AccountsDue( IN pi_user_id INT )
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名       ：  P_Treas_ReceiptAgen_AccountsDue
 *
 * 概要             ：  収納代行委託先未収金明細
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
    SET v_BusinessDate = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'BusinessDate');         -- 業務日付

    -- ------------------------------
    -- 1.収納代行委託先未収金明細対象作成
    -- ------------------------------
    INSERT INTO AT_ReceiptAgen_AccountsDue(
         DailyMonthlyFlg                            -- 日次･月次区分
        ,ProcessingDate                             -- 処理日付
        ,ReceiptAgentId                             -- 収納代行会社I
        ,ReceiptAgentName                           -- 収納代行会社名
        ,PaymentMethod                              -- 入金方法
        ,ManCustId                                  -- 管理顧客番号
        ,ManCusNameKj                               -- 顧客名
        ,OrderSeq                                   -- 注文Seq
        ,OrderId                                    -- 注文ID-
        ,OutOfAmends                                -- 補償有無
        ,OemTransferDate                            -- OEM移管日
        ,OemId                                      -- OEMID
        ,OemNameKj                                  -- OEM先名
        ,EnterpriseId                               -- 加盟店ID
        ,EnterpriseNameKj                           -- 加盟店名
        ,ConsumerPayments                           -- 消費者支払額
        ,ConsumerPaymentDate                        -- 消費者支払日
        ,PaymentDueDate                             -- 口座入金予定日
        ,RegistDate                                 -- 登録日時
        ,RegistId                                   -- 登録者
        ,UpdateDate                                 -- 更新日時
        ,UpdateId                                   -- 更新者
        ,ValidFlg                                   -- 有効ﾌﾗｸﾞ
       )                                            --
    SELECT
        0                                           -- 日次･月次区分(0:日次)
       ,v_BusinessDate                              -- 処理日付
       ,rc.ReceiptAgentId                           -- 収納代行会社I
       ,c1.Class1                                   -- 収納代行会社名
       ,c2.Class1                                   -- 入金方法
       ,mc.ManCustId                                -- 管理顧客番号
       ,c.NameKj                                    -- 顧客名
       ,o.OrderSeq                                  -- 注文Seq
       ,o.OrderId                                   -- 注文ID-
       ,c3.Class1                                   -- 補償有無
       ,CASE WHEN o.OemClaimTransDate > v_BusinessDate
             THEN NULL
             ELSE o.OemClaimTransDate
        END                                         -- OEM移管日
       ,c4.Class3                                   -- OEMID
       ,c4.Class2                                   -- OEM先名
       ,o.EnterpriseId                              -- 加盟店ID
       ,e.EnterpriseNameKj                          -- 加盟店名
       ,rc.ReceiptAmount                            -- 消費者支払額
       ,rc.ReceiptDate                              -- 消費者支払日
       ,rc.DepositDate                              -- 口座入金予定日
       ,NOW()                                       -- 登録日時
       ,pi_user_id                                  -- 登録者
       ,NOW()                                       -- 更新日時
       ,pi_user_id                                  -- 更新者
       ,1                                           -- 有効ﾌﾗｸﾞ
    FROM  T_ReceiptControl rc
    INNER JOIN T_Order o
    ON    o.OrderSeq      = rc.OrderSeq
    INNER JOIN T_Customer c
    ON    o.OrderSeq        = c.OrderSeq
    LEFT  JOIN T_EnterpriseCustomer ec
    ON    c.EntCustSeq      = ec.EntCustSeq
    LEFT  JOIN T_ManagementCustomer mc
    ON    ec.ManCustId      = mc.ManCustId
    LEFT  JOIN T_Enterprise e
    ON    o.EnterpriseId    = e.EnterpriseId
    LEFT  JOIN M_Code c1
    ON    c1.CodeId         = 101
    AND   c1.KeyCode        = rc.ReceiptAgentId
    INNER JOIN M_Code c2
    ON    c2.CodeId         = 163
    AND   c2.KeyCode        = rc.ReceiptClass
    LEFT  JOIN M_Code c3
    ON    c3.CodeId         = 159
    AND   c3.KeyCode        = IFNULL(o.OutOfAmends, 0)
    LEFT  JOIN M_Code c4
    ON    c4.CodeId         = 160
    AND   c4.KeyCode        = IFNULL(o.OemId, 0)
    WHERE rc.ReceiptAgentId IS NOT NULL
    AND   rc.ReceiptDate        <= v_BusinessDate
    AND   ( rc.DepositDate IS NULL  OR  rc.DepositDate >= DATE_ADD(v_BusinessDate, INTERVAL -7 DAY) )
    AND   rc.ValidFlg           = 1;
END
$$

DELIMITER ;
