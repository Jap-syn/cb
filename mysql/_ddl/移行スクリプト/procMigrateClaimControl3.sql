DROP procedure IF EXISTS `procMigrateClaimControl3`;

DELIMITER $$
CREATE PROCEDURE `procMigrateClaimControl3`()
BEGIN

    /* 移行処理：請求管理3（最終入金SEQのセット）
        ！！ 移行処理：入金管理 終了後、実行 ！！
    */
    DECLARE
        v_done int;

    DECLARE
        v_ReceiptSeq,       -- 入金SEQ
        v_OrderSeq          -- 注文SEQ
                        bigint(20);
    
    DECLARE
        cur2 cursor
    FOR SELECT
        `T_ReceiptControl`.`OrderSeq`
    FROM
        `T_ReceiptControl`
    LEFT OUTER JOIN `T_ClaimControl`
    ON `T_ReceiptControl`.`OrderSeq` = `T_ClaimControl`.`OrderSeq`
    GROUP BY `T_ReceiptControl`.`OrderSeq`
    ;
    
    DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' set v_done = 0;
    
    SET v_done = 1;
    
    OPEN cur2;
    read_loop : LOOP
        FETCH cur2 INTO
            v_OrderSeq;
        
        IF v_done = 0 THEN leave read_loop;
        END IF;
        
        SELECT
            `T_ReceiptControl`.`ReceiptSeq`
        INTO
            v_ReceiptSeq
        FROM
            `T_ReceiptControl`
        LEFT OUTER JOIN `T_ClaimControl`
        ON `T_ReceiptControl`.`OrderSeq` = `T_ClaimControl`.`OrderSeq`
        WHERE
            `T_ReceiptControl`.`ReceiptSeq` = (SELECT MAX(`ReceiptSeq`) 
                                                FROM `T_ReceiptControl` 
                                                WHERE `T_ReceiptControl`.`OrderSeq` = v_OrderSeq);
        
        -- 請求管理へ最終入金SEQのセット
        UPDATE `T_ClaimControl`
        SET `T_ClaimControl`.`LastReceiptSeq` = v_ReceiptSeq
        WHERE `T_ClaimControl`.`OrderSeq` = v_OrderSeq
            AND `T_ClaimControl`.`CheckingClaimAmount` > 0;
        
    END LOOP;

    CLOSE cur2;
END
$$

DELIMITER ;