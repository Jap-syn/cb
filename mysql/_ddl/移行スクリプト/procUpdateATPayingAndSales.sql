DROP procedure IF EXISTS `procUpdateATPayingAndSales`;

DELIMITER $$
CREATE PROCEDURE `procUpdateATPayingAndSales` ()
BEGIN
    /* 1. AT_PayingAndSales調整(Deli_ConfirmArrivalInputDate) */

    DECLARE l_Seq                       BIGINT;     -- SEQ
    DECLARE l_Deli_ConfirmArrivalDate   DATETIME;   -- 配送－確認日

    -- NO_DATA_FOUND ｲﾍﾞﾝﾄﾊﾝﾄﾞﾗ用変数
    DECLARE no_data_found INT DEFAULT 1;

    -- ｶｰｿﾙ宣言
    DECLARE l_cur CURSOR FOR
        SELECT apas.Seq
        ,      o.Deli_ConfirmArrivalDate
        FROM   AT_PayingAndSales apas
               INNER JOIN T_PayingAndSales pas ON (apas.Seq = pas.Seq)
               INNER JOIN T_Order o ON (pas.OrderSeq = o.OrderSeq)
        WHERE  apas.Deli_ConfirmArrivalInputDate IS NULL
        AND    o.Deli_ConfirmArrivalFlg = 1
        AND    o.Deli_ConfirmArrivalDate IS NOT NULL
        ;

    -- NO_DATA_FOUND 用ｲﾍﾞﾝﾄﾊﾝﾄﾞﾗ宣言
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_data_found = 0;

    -- ｶｰｿﾙｵｰﾌﾟﾝ
    OPEN    l_cur;
        -- 最初のｶｰｿﾙをFetch
        FETCH l_cur INTO l_Seq, l_Deli_ConfirmArrivalDate;
        -- whileﾙｰﾌﾟで処理する
        WHILE no_data_found != 0  DO

            -- AT_PayingAndSales更新
            UPDATE AT_PayingAndSales
            SET    Deli_ConfirmArrivalInputDate = l_Deli_ConfirmArrivalDate
            WHERE  Seq = l_Seq
            ;

            -- 次のｶｰｿﾙをFetch
            FETCH l_cur INTO l_Seq, l_Deli_ConfirmArrivalDate;
        END WHILE;
    -- ｶｰｿﾙｸﾛｰｽﾞ
    CLOSE   l_cur;

END
$$

DELIMITER ;

