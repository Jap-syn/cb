DROP procedure IF EXISTS `procMigrateClaimControl5`;

DELIMITER $$
CREATE PROCEDURE `procMigrateClaimControl5`()
BEGIN

    /* 移行処理：請求管理5（一部入金処理）
        ！！ 移行処理：当日移行の最後に実施 ！！
    */
    DECLARE
        v_done int;

    DECLARE v_OrderSeq  bigint(20);           -- 注文SEQ
    DECLARE v_InstallmentPlanAmount  bigint(20);           -- 分割支払い済み額
    DECLARE v_DataStatus  int;           -- データステータス
    DECLARE v_Clm_L_ClaimDate DATE; -- 最終請求日
    DECLARE v_receipt_agent_id bigint(20);
    DECLARE v_Rct_ReceiptMethod int;
    DECLARE v_Rct_AccountPaymentDate DATE;
    
    DECLARE
        cur cursor
    FOR 
    SELECT o.OrderSeq
          ,o.InstallmentPlanAmount
          ,o.Clm_L_ClaimDate
          ,o.Rct_AccountPaymentDate
    FROM coraldb_ikou.T_Order o
    WHERE o.DataStatus = 51 AND o.InstallmentPlanAmount > 0
    ;
    
    DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' set v_done = 0;
    
    
    SET v_done = 1;
    
    OPEN cur;
    read_loop : LOOP
        FETCH cur INTO
            v_OrderSeq,
            v_InstallmentPlanAmount,
            v_Clm_L_ClaimDate,
            v_Rct_AccountPaymentDate;
        
        IF v_done = 0 THEN leave read_loop;
        END IF;
        
        
        -- 次期向けのデータステータスが51であること
        SELECT DataStatus
        INTO   v_DataStatus
        FROM   T_Order
        WHERE  OrderSeq = v_OrderSeq;
        
        IF v_DataStatus = 51 THEN
            
            SET v_receipt_agent_id = NULL;
            IF v_Rct_ReceiptMethod = 1 THEN
                SET v_receipt_agent_id = 4;
            END IF;
            
            -- -------------------
            -- 入金処理
            -- -------------------
            CALL P_ReceiptControl(
                    v_InstallmentPlanAmount
                ,   v_OrderSeq
                ,   v_Clm_L_ClaimDate
                ,   3
                ,   NULL
                ,   v_receipt_agent_id
                ,   v_Rct_AccountPaymentDate
                ,   9
                ,   @po_ret_sts
                ,   @po_ret_errcd
                ,   @po_ret_sqlcd
                ,   @po_ret_msg
            );
            
        END IF;
        
    END LOOP;

    CLOSE cur;

END
$$

DELIMITER ;