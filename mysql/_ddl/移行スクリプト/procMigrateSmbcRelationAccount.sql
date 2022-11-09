DROP procedure IF EXISTS `procMigrateSmbcRelationAccount`;

DELIMITER $$
CREATE PROCEDURE `procMigrateSmbcRelationAccount` ()
BEGIN

    /* 移行処理：決済ステーション連携アカウント */

    DECLARE
        updDttm    datetime;        -- 更新日時

    SET updDttm = now();

    INSERT INTO `T_SmbcRelationAccount`
        (`SmbcAccountId`,
        `OemId`,
        `DisplayName`,
        `ApiVersion`,
        `BillMethod`,
        `KessaiId`,
        `ShopCd`,
        `SyunoCoCd1`,
        `SyunoCoCd2`,
        `SyunoCoCd3`,
        `ShopPwd1`,
        `ShopPwd2`,
        `ShopPwd3`,
        `SeikyuuName`,
        `SeikyuuKana`,
        `HakkouKbn`,
        `YuusousakiKbn`,
        `Yu_SubscriberName`,
        `Yu_AccountNumber`,
        `Yu_ChargeClass`,
        `Yu_SubscriberData`,
        `Cv_ReceiptAgentName`,
        `Cv_SubscriberName`,
        `Cv_ReceiptAgentCode`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT 
        `T_SmbcRelationAccount`.`SmbcAccountId`,
        `T_SmbcRelationAccount`.`OemId`,
        `T_SmbcRelationAccount`.`DisplayName`,
        `T_SmbcRelationAccount`.`ApiVersion`,
        `T_SmbcRelationAccount`.`BillMethod`,
        `T_SmbcRelationAccount`.`KessaiId`,
        `T_SmbcRelationAccount`.`ShopCd`,
        `T_SmbcRelationAccount`.`SyunoCoCd1`,
        `T_SmbcRelationAccount`.`SyunoCoCd2`,
        `T_SmbcRelationAccount`.`SyunoCoCd3`,
        `T_SmbcRelationAccount`.`ShopPwd1`,
        `T_SmbcRelationAccount`.`ShopPwd2`,
        `T_SmbcRelationAccount`.`ShopPwd3`,
        `T_SmbcRelationAccount`.`SeikyuuName`,
        `T_SmbcRelationAccount`.`SeikyuuKana`,
        `T_SmbcRelationAccount`.`HakkouKbn`,
        `T_SmbcRelationAccount`.`YuusousakiKbn`,
        `T_SmbcRelationAccount`.`Yu_SubscriberName`,
        `T_SmbcRelationAccount`.`Yu_AccountNumber`,
        `T_SmbcRelationAccount`.`Yu_ChargeClass`,
        `T_SmbcRelationAccount`.`Yu_SubscriberData`,
        `T_SmbcRelationAccount`.`Cv_ReceiptAgentName`,
        `T_SmbcRelationAccount`.`Cv_SubscriberName`,
        `T_SmbcRelationAccount`.`Cv_ReceiptAgentCode`,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_SmbcRelationAccount`;
END
$$

DELIMITER ;

