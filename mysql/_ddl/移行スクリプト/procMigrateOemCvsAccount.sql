DROP procedure IF EXISTS `procMigrateOemCvsAccount`;

DELIMITER $$
CREATE PROCEDURE `procMigrateOemCvsAccount` ()
BEGIN

    /* 移行処理：OEMコンビニ収納情報 */

    DECLARE
        updDttm    datetime;        -- 更新日時

    SET updDttm = now();

    INSERT INTO `T_OemCvsAccount`
        (`CvsAccountId`,
        `OemId`,
        `ReceiptAgentId`,
        `SubscriberCode`,
        `Option1`,
        `Option2`,
        `Option3`,
        `SubscriberName`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `T_OemCvsAccount`.`CvsAccountId`,
        `T_OemCvsAccount`.`OemId`,
        `T_OemCvsAccount`.`ReceiptAgentId`,
        `T_OemCvsAccount`.`SubscriberCode`,
        `T_OemCvsAccount`.`Option1`,
        `T_OemCvsAccount`.`Option2`,
        `T_OemCvsAccount`.`Option3`,
        `T_OemCvsAccount`.`SubscriberName`,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_OemCvsAccount`;
END
$$

DELIMITER ;

