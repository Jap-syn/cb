DROP PROCEDURE IF EXISTS P_Treas_Make_Wk_Receipt;

DELIMITER $$

CREATE PROCEDURE P_Treas_Make_Wk_Receipt ()
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名       ：  P_Treas_Make_Wk_Receipt
 *
 * 概要             ：  会計月次用ワーク請求データ作成
 *
 * 引数             ：  なし
 *
 * 履歴             ：  2016/01/14  NDC 新規作成
 *
 *****************************************************************************/
BEGIN

    -- ------------------------------
    -- 変数宣言
    -- ------------------------------
    DECLARE v_AccountingMonth   DATE;                   -- 会計月

    -- ------------------------------
    -- 変数初期化
    -- ------------------------------
    SET v_AccountingMonth = F_GetSystemProperty('[DEFAULT]', 'systeminfo', 'AccountingMonth');   -- 会計月

    -- AW_Receipt1全データ削除
    TRUNCATE TABLE AW_Receipt1;

    -- AW_Receipt2全データ削除
    TRUNCATE TABLE AW_Receipt2;

    -- AW_Receipt1作成
    INSERT INTO AW_Receipt1 (OrderSeq, ReceiptSeq, ReceiptAmount, ReceiptDate)
    SELECT OrderSeq
    ,      MAX(ReceiptSeq)
    ,      SUM(ReceiptAmount)
    ,      MAX(ReceiptDate)
    FROM   T_ReceiptControl
    WHERE  DATE_FORMAT(ReceiptDate, '%Y-%m') <= DATE_FORMAT(v_AccountingMonth, '%Y-%m')
    GROUP BY OrderSeq;

    -- AW_Receipt2作成
    INSERT INTO AW_Receipt2 (OrderSeq, Sum_CheckingUseAmount, Max_ReceiptDate)
    SELECT OrderSeq
    ,      SUM(IFNULL(CheckingUseAmount, 0))
    ,      MAX(ReceiptDate)
    FROM   T_ReceiptControl
    WHERE  DATE_FORMAT(ReceiptDate, '%Y-%m') > DATE_FORMAT(v_AccountingMonth, '%Y-%m')
    GROUP BY OrderSeq;

END
$$

DELIMITER ;
