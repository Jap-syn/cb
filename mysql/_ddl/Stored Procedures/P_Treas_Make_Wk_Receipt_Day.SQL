DROP PROCEDURE IF EXISTS P_Treas_Make_Wk_Receipt_Day;

DELIMITER $$

CREATE PROCEDURE P_Treas_Make_Wk_Receipt_Day ()
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名       ：  P_Treas_Make_Wk_Receipt_Day
 *
 * 概要             ：  会計日次用ﾜｰｸ入金ﾃﾞｰﾀ作成
 *
 * 引数             ：  なし
 *
 * 履歴             ：  2016/02/01  NDC 新規作成
 *
 *****************************************************************************/
BEGIN

    -- ------------------------------
    -- 変数宣言
    -- ------------------------------
    DECLARE v_BusinessDate  DATE;               -- 業務日付

    -- ------------------------------
    -- 変数初期化
    -- ------------------------------
    SET v_BusinessDate  = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'BusinessDate');   -- 業務日付

    -- AW_Receipt3全ﾃﾞｰﾀ削除
    TRUNCATE TABLE AW_Receipt3;

    -- AW_Receipt3作成
    INSERT
    INTO AW_Receipt3 (OrderSeq, Rct_CancelFlg)
    SELECT  rc.OrderSeq
        ,   MAX(arc.Rct_CancelFlg)
    FROM    T_ReceiptControl rc
            INNER JOIN AT_ReceiptControl arc ON (arc.ReceiptSeq = rc.ReceiptSeq)
    WHERE   DATE(rc.ReceiptProcessDate) <= v_BusinessDate    -- 業務日付以前
    GROUP BY
            rc.OrderSeq
    ;
END
$$

DELIMITER ;
