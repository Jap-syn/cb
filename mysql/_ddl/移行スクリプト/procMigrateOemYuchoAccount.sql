DROP procedure IF EXISTS `procMigrateOemYuchoAccount`;

DELIMITER $$
CREATE PROCEDURE `procMigrateOemYuchoAccount` ()
BEGIN

    /* 移行処理：OEMゆうちょ口座 */

    DECLARE
        updDttm    datetime;        -- 更新日時

    SET updDttm = now();

    INSERT INTO `T_OemYuchoAccount`
        (`YuchoAccountId`,
        `OemId`,
        `SubscriberName`,
        `AccountNumber`,
        `ChargeClass`,
        `SubscriberData`,
        `Option1`,
        `Option2`,
        `Option3`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT 
        `T_OemYuchoAccount`.`YuchoAccountId`,
        `T_OemYuchoAccount`.`OemId`,
        `T_OemYuchoAccount`.`SubscriberName`,
        `T_OemYuchoAccount`.`AccountNumber`,
        CASE WHEN `T_OemYuchoAccount`.`OemId` = 2 THEN 2
             ELSE 0
        END ,
        `T_OemYuchoAccount`.`SubscriberData`,
        `T_OemYuchoAccount`.`Option1`,
        `T_OemYuchoAccount`.`Option2`,
        `T_OemYuchoAccount`.`Option3`,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_OemYuchoAccount`;
END
$$

DELIMITER ;

