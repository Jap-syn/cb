DROP procedure IF EXISTS `procMigrateCvsReceiptAgent`;

DELIMITER $$
CREATE PROCEDURE `procMigrateCvsReceiptAgent` ()
BEGIN

    /* 移行処理：コンビニ収納代行会社 */

    DECLARE
        updDttm    datetime;

    SET updDttm = now();

    INSERT INTO `M_CvsReceiptAgent`
        (`ReceiptAgentId`,
        `ReceiptAgentName`,
        `ReceiptAgentCode`,
        `BarcodeLogicName`,
        `Note`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `M_CvsReceiptAgent`.`ReceiptAgentId`,
        `M_CvsReceiptAgent`.`ReceiptAgentName`,
        `M_CvsReceiptAgent`.`ReceiptAgentCode`,
        `M_CvsReceiptAgent`.`BarcodeLogicName`,
        `M_CvsReceiptAgent`.`Note`,
        updDttm,
        9,
        updDttm,
        9,
        CASE `M_CvsReceiptAgent`.`InvalidFlg`
            WHEN 0 THEN 1
            WHEN 1 THEN 0
            ELSE 1
        END
    FROM `coraldb_ikou`.`M_CvsReceiptAgent`;
/**
    -- 会計略称を追加
    UPDATE  `M_CvsReceiptAgent`
    SET     `AccountingSimpleName` =
            CASE `M_CvsReceiptAgent`.`ReceiptAgentId`
                WHEN 1 THEN 'アプラス'
                WHEN 2 THEN '@payment'
                WHEN 3 THEN '決済ステーション'
                ELSE ''
            END;
*/
END
$$

DELIMITER ;

