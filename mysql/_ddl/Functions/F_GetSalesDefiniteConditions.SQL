DROP FUNCTION IF EXISTS F_GetSalesDefiniteConditions;

DELIMITER $$

CREATE FUNCTION F_GetSalesDefiniteConditions(pi_order_seq BIGINT)
RETURNS TEXT
BEGIN
/****************************************************************************************
 *
 * ファンクション名 ：  F_GetSalesDefiniteConditions
 *
 * 概要             ：  【共通】注文の売上確定条件取得
 *
 * 引数             ：  [I/ ]pi_order_seq           注文Seq
 *
 * 戻り値           ：  取得ﾃﾞｰﾀ
 *
 * 履歴             ：  2015/10/01  NDC 新規作成
 *                      2016/02/18  NDC 仕様変更
 *                      2016/03/01  NDC 仕様変更(AT_PayingAndSales.ATUriTypeによる決定化)
 *
 ***************************************************************************************/

    -- 変数宣言
    DECLARE v_retval VARCHAR(30) DEFAULT '';

    -- 売上確定条件取得
    SELECT CASE WHEN apas.ATUriType = 1 THEN '着荷確認日'
                WHEN apas.ATUriType = 2 THEN '入金日'
                WHEN apas.ATUriType = 3 THEN '役務提供日'
           ELSE ''
           END
    INTO   v_retval
    FROM   AT_PayingAndSales apas
           INNER JOIN T_PayingAndSales pas ON (apas.Seq = pas.Seq)
    WHERE  pas.OrderSeq = pi_order_seq;

    return v_retval;

END
$$

DELIMITER ;
