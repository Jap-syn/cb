DROP procedure IF EXISTS procMigrateOrderHistory;

DELIMITER $$
CREATE PROCEDURE procMigrateOrderHistory()
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名   ：  procMigrateOrderHistory
 *
 * 概要         ：  移行時のP_OrderHistoryのｺｰﾙ用
 *
 * 引数         ：  なし
 *
 * 履歴         ：  2015/07/01  NDC 新規作成
 *
 *****************************************************************************/
BEGIN
    -- ---------------------------
    -- 変数宣言
    -- ---------------------------
    DECLARE l_OrderSeq  BIGINT(20);
    DECLARE no_data_found INT DEFAULT 1;
    -- FETCH用ｶｰｿﾙ宣言
    DECLARE l_cur   CURSOR  FOR
        SELECT  OrderSeq
        FROM    T_Order
--        WHERE   DataStatus <> 91
        ;
    -- ｶｰｿﾙのｲﾍﾞﾝﾄﾊﾝﾄﾞﾗ宣言
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_data_found = 0;

    -- ---------------------------
    -- 処理開始
    -- ---------------------------
    -- ｶｰｿﾙｵｰﾌﾟﾝ
    OPEN    l_cur;
        -- 最初のｶｰｿﾙをFETCH
        FETCH   l_cur   INTO    l_OrderSeq;
        -- whileﾙｰﾌﾟで処理する
        WHILE   no_data_found != 0  DO
            -- 履歴登録用ﾌﾟﾛｼｰｼﾞｬをｺｰﾙ
            CALL    P_OrderHistory( l_OrderSeq      -- 注文Seq
                                ,   999             -- 理由ｺｰﾄﾞ（移行）
                                ,   9
                                ,   @po_ret_sts
                                ,   @po_ret_errcd
                                ,   @po_ret_sqlcd
                                ,   @po_ret_msg
                                  );
            -- 次のｶｰｿﾙをFETCH
            FETCH   l_cur   INTO    l_OrderSeq;
        END WHILE;
    -- ｶｰｿﾙｸﾛｰｽﾞ
    CLOSE   l_cur;
END
$$

DELIMITER ;
