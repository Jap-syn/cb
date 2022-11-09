DROP procedure IF EXISTS `procMigrateStampFee`;

DELIMITER $$
CREATE PROCEDURE `procMigrateStampFee` ()
BEGIN

    /* 移行処理：印紙代管理 */
    /* 2015-08-18  本締仮締フラグ設定変更  */

    DECLARE
        updDttm    datetime;        -- 更新日時

    SET updDttm = now();

    INSERT INTO `T_StampFee`
        (`Seq`,
        `OrderSeq`,
        `DecisionDate`,
        `StampFee`,
        `ClearFlg`,
        `ClearDate`,
        `CancelFlg`,
        `PayingControlSeq`,
        `PayingControlStatus`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT 
        `T_StampFee`.`Seq`,
        `T_StampFee`.`OrderSeq`,
        `T_StampFee`.`DecisionDate`,
        `T_StampFee`.`StampFee`,
        `T_StampFee`.`ClearFlg`,
        `T_StampFee`.`ClearDate`,
        `T_StampFee`.`CancelFlg`,
        `T_StampFee`.`PayingControlSeq`,
        -- `T_StampFee`.`ClearFlg`,
        CASE WHEN `T_StampFee`.`ClearFlg` = 1 THEN 1
             ELSE 0
        END,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_StampFee`;
END
$$

DELIMITER ;

