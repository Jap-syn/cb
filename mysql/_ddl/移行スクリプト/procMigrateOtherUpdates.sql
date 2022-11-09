DROP procedure IF EXISTS `procMigrateOtherUpdates`;

DELIMITER $$
CREATE PROCEDURE `procMigrateOtherUpdates` ()
BEGIN

    /* 移行処理：当日移行の汎用UPDATEプロシージャー  */

    DECLARE
        updDttm    datetime;                            -- 更新日時

    SET updDttm = now();
    
    -- -------------------------------------------
    -- 請求履歴のPrintedStatusの更新
    -- インデックスをうまく効かすため、一度一括UPDATEした後、インデックスが効率的に効くPrintedFlg=0のものをUPDATEする
    -- -------------------------------------------
    -- 一度、全体をUPDATE
    UPDATE T_ClaimHistory ch
    SET PrintedStatus = 9;

    -- ジョブ転送中のデータのみ、2(CSV出力済)に更新
    UPDATE T_ClaimHistory ch
    SET PrintedStatus = 2
    WHERE PrintedFlg = 0;
    
    
    -- -------------------------------------------
    -- 会計用　入金管理の入金予定日を更新
    -- （収納代行会社IDがNOTNULLかつ、入金予定日がNULLの場合、'2015-11-30'とする
    -- -------------------------------------------
    UPDATE T_ReceiptControl rc
    SET    rc.DepositDate = '2015-11-30'
    WHERE  rc.DepositDate IS NULL
    AND    rc.ReceiptAgentId IS NOT NULL;
    
END
$$

DELIMITER ;
