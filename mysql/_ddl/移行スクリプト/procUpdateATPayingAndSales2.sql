DROP procedure IF EXISTS `procUpdateATPayingAndSales2`;

DELIMITER $$
CREATE PROCEDURE `procUpdateATPayingAndSales2` ()
BEGIN
    /* 2. AT_PayingAndSales調整(ATUriType／ATUriDay) */

    DECLARE l_Seq                           BIGINT;     -- SEQ
    DECLARE l_OrderSeq                      BIGINT;     -- 注文SEQ
    DECLARE l_P_OrderSeq                    BIGINT;     -- 親注文SEQ
    DECLARE l_ClearConditionDate            DATE;       -- 立替条件クリア日
    DECLARE l_Deli_ConfirmArrivalFlg        INT;        -- 配送－着荷確認
    DECLARE l_Deli_ConfirmArrivalDate       DATETIME;   -- 配送－確認日
    DECLARE l_Deli_ConfirmArrivalInputDate  DATETIME;   -- 着荷確認入力日
    DECLARE l_ServiceTargetClass            INT;        -- 役務対象区分

    DECLARE l_rc_ReceiptSeq                 BIGINT;     -- 入金SEQ
    DECLARE l_rc_cnt                        INT;        -- 入金件数
    DECLARE l_rc_ReceiptDate                DATE;       -- 顧客入金日
    DECLARE l_rc_ReceiptProcessDate         DATETIME;   -- 入金処理日

    -- NO_DATA_FOUND ｲﾍﾞﾝﾄﾊﾝﾄﾞﾗ用変数
    DECLARE no_data_found INT DEFAULT 1;

    -- ｶｰｿﾙ宣言
    DECLARE l_cur CURSOR FOR
        SELECT apas.Seq
        ,      pas.OrderSeq
        ,      o.P_OrderSeq
        ,      pas.ClearConditionDate
        ,      o.Deli_ConfirmArrivalFlg
        ,      o.Deli_ConfirmArrivalDate
        ,      apas.Deli_ConfirmArrivalInputDate
        ,      o.ServiceTargetClass
        FROM   AT_PayingAndSales apas
               INNER JOIN T_PayingAndSales pas ON (apas.Seq = pas.Seq)
               INNER JOIN T_Order o ON (pas.OrderSeq = o.OrderSeq)
        WHERE  apas.ATUriType = 99
        AND    apas.ATUriDay = '99999999'
        AND    pas.ClearConditionForCharge = 1
        AND    pas.ClearConditionDate IS NOT NULL
        ;

    -- NO_DATA_FOUND 用ｲﾍﾞﾝﾄﾊﾝﾄﾞﾗ宣言
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_data_found = 0;

    -- ｶｰｿﾙｵｰﾌﾟﾝ
    OPEN    l_cur;
        -- 最初のｶｰｿﾙをFetch
        FETCH l_cur INTO l_Seq, l_OrderSeq, l_P_OrderSeq, l_ClearConditionDate, l_Deli_ConfirmArrivalFlg, l_Deli_ConfirmArrivalDate, l_Deli_ConfirmArrivalInputDate, l_ServiceTargetClass;
        -- whileﾙｰﾌﾟで処理する
        WHILE no_data_found != 0  DO

            IF l_ServiceTargetClass = 0 THEN
                -- 通常注文

                -- 立替条件クリア条件に該当する入金のチェック
                SELECT MIN(rc.ReceiptSeq)
                ,      COUNT(rc.ReceiptSeq)
                INTO   l_rc_ReceiptSeq
                ,      l_rc_cnt
                FROM   T_ClaimControl cc
                ,      T_ReceiptControl rc
                WHERE  cc.OrderSeq = rc.OrderSeq
                AND    cc.OrderSeq = l_P_OrderSeq
                AND    cc.MinClaimAmount - (SELECT SUM(ReceiptAmount) FROM T_ReceiptControl WHERE OrderSeq = cc.OrderSeq AND ReceiptSeq <= rc.ReceiptSeq) <= 0
                AND    rc.ReceiptSeq > IFNULL((SELECT MAX(sub1.ReceiptSeq) FROM T_ReceiptControl sub1,AT_ReceiptControl sub2 WHERE sub1.ReceiptSeq = sub2.ReceiptSeq AND sub1.OrderSeq = rc.OrderSeq AND Rct_CancelFlg = 1), 0);

                IF l_rc_cnt > 0 THEN
                    -- 入金あり

                    -- 顧客入金日／入金処理日の取得
                    SELECT ReceiptDate
                    ,      ReceiptProcessDate
                    INTO   l_rc_ReceiptDate
                    ,      l_rc_ReceiptProcessDate
                    FROM   T_ReceiptControl
                    WHERE  ReceiptSeq = l_rc_ReceiptSeq;

                    IF l_Deli_ConfirmArrivalFlg = 1 AND (l_Deli_ConfirmArrivalInputDate  < l_rc_ReceiptProcessDate) THEN
                        -- (着荷あり且つ、着荷確認入力日が入金処理日より小さい時)
                        UPDATE AT_PayingAndSales
                        SET    ATUriType = 1                                                    -- 1:着荷
                        ,      ATUriDay = DATE_FORMAT(l_Deli_ConfirmArrivalDate, '%Y%m%d')      -- 着荷確認日
                        WHERE  Seq = l_Seq;
                        
                    ELSE
                        -- (上記が成り立たない時)
                        UPDATE AT_PayingAndSales
                        SET    ATUriType = 2                                                    -- 2:入金
                        ,      ATUriDay = DATE_FORMAT(l_rc_ReceiptDate, '%Y%m%d')               -- 顧客入金日
                        WHERE  Seq = l_Seq;
                        
                    END IF;
                    
                ELSE
                    -- 入金なし
                    IF l_Deli_ConfirmArrivalDate IS NULL THEN
                        -- (ただし、着荷にての立替条件クリアとならないケース [雑損失])
                        UPDATE AT_PayingAndSales
                        SET    ATUriType = 2                                                    -- 2:入金
                        ,      ATUriDay = DATE_FORMAT(l_ClearConditionDate, '%Y%m%d')           -- 立替条件クリア日
                        WHERE  Seq = l_Seq;
                        
                    ELSE
                        UPDATE AT_PayingAndSales
                        SET    ATUriType = 1                                                    -- 1:着荷
                        ,      ATUriDay = DATE_FORMAT(l_Deli_ConfirmArrivalDate, '%Y%m%d')      -- 着荷確認日
                        WHERE  Seq = l_Seq;
                        
                    END IF;
                    
                END IF;

            ELSE
                -- 役務
                UPDATE AT_PayingAndSales
                SET    ATUriType = 3                                                            -- 3:役務
                ,      ATUriDay = DATE_FORMAT(l_ClearConditionDate, '%Y%m%d')                   -- 立替条件クリア日
                WHERE  Seq = l_Seq;
                
            END IF;

            -- 次のｶｰｿﾙをFetch
            FETCH l_cur INTO l_Seq, l_OrderSeq, l_P_OrderSeq, l_ClearConditionDate, l_Deli_ConfirmArrivalFlg, l_Deli_ConfirmArrivalDate, l_Deli_ConfirmArrivalInputDate, l_ServiceTargetClass;
        END WHILE;
    -- ｶｰｿﾙｸﾛｰｽﾞ
    CLOSE   l_cur;

END
$$

DELIMITER ;

