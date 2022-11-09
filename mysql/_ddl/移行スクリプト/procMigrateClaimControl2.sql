DROP procedure IF EXISTS `procMigrateClaimControl2`;

DELIMITER $$
CREATE PROCEDURE `procMigrateClaimControl2`()
BEGIN

    /* 移行処理：請求管理2（加盟店顧客SEQのセット）
        ！！ 移行処理：購入者 終了後、実行 ！！
    */

    -- 請求管理へ加盟店顧客SEQのセット
    UPDATE `T_ClaimControl`
    LEFT OUTER JOIN `T_Customer`
    ON `T_ClaimControl`.`OrderSeq` = `T_Customer`.`OrderSeq`
    SET `T_ClaimControl`.`EntCustSeq` = `T_Customer`.`EntCustSeq`
    WHERE `T_ClaimControl`.`OrderSeq` = `T_Customer`.`OrderSeq`;

END
$$

DELIMITER ;