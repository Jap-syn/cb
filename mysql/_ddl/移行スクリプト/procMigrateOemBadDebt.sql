DROP procedure IF EXISTS `procMigrateOemBadDebt`;

DELIMITER $$
CREATE PROCEDURE `procMigrateOemBadDebt` ()
BEGIN

    /* 移行処理：OEM債権明細 */

    DECLARE
        updDttm    datetime;    -- 更新日時

    SET updDttm = now();

    INSERT INTO `T_OemBadDebt`
        (`Seq`,
        `OemId`,
        `FixedMonth`,
        `ProcessDate`,
        `SpanFrom`,
        `SpanTo`,
        `FcSpanFrom`,
        `FcSpanTo`,
        `ClaimCount`,
        `ClaimAmount`,
        `ReceiptMoneyCount`,
        `ReceiptMoneyAmount`,
        `BadDebtCount`,
        `BadDebtAmount`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `T_OemBadDebt`.`Seq`,
        `T_OemBadDebt`.`OemId`,
        `T_OemBadDebt`.`FixedMonth`,
        `T_OemBadDebt`.`ProcessDate`,
        `T_OemBadDebt`.`SpanFrom`,
        `T_OemBadDebt`.`SpanTo`,
        `T_OemBadDebt`.`FcSpanFrom`,
        `T_OemBadDebt`.`FcSpanTo`,
        `T_OemBadDebt`.`ClaimCount`,
        `T_OemBadDebt`.`ClaimAmount`,
        `T_OemBadDebt`.`ReceiptMoneyCount`,
        `T_OemBadDebt`.`ReceiptMoneyAmount`,
        `T_OemBadDebt`.`BadDebtCount`,
        `T_OemBadDebt`.`BadDebtAmount`,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_OemBadDebt`;
END
$$

DELIMITER ;


