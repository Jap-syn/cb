DROP procedure IF EXISTS `procMigrateClaimControl4`;

DELIMITER $$
CREATE PROCEDURE `procMigrateClaimControl4`()
BEGIN

    /* 移行処理：請求管理4（最終入金SEQのセット）
        ！！ 移行処理：入金管理 終了後、実行 ！！
    */

    -- 請求管理へ最終入金SEQのセット
--    UPDATE `T_ClaimControl`
--    LEFT OUTER JOIN `T_ReceiptControl`
--    ON `T_ClaimControl`.`OrderSeq` = `T_ReceiptControl`.`OrderSeq`
--    SET `T_ClaimControl`.`LastReceiptSeq` = `T_ReceiptControl`.`ReceiptSeq`
--    WHERE `T_ClaimControl`.`OrderSeq` = `T_ReceiptControl`.`OrderSeq`;


    UPDATE `T_ClaimControl`
    SET `T_ClaimControl`.`LastReceiptSeq` = ( SELECT MAX(`T_ReceiptControl`.`ReceiptSeq`) 
                                                FROM `T_ReceiptControl` 
                                               WHERE `T_ClaimControl`.`OrderSeq` = `T_ReceiptControl`.`OrderSeq` ) ;

END
$$

DELIMITER ;