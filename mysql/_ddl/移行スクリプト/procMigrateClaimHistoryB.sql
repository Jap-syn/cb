DROP procedure IF EXISTS `procMigrateClaimHistoryB`;

DELIMITER $$

CREATE PROCEDURE `procMigrateClaimHistoryB` ()
BEGIN

    /* 移行処理：請求履歴 の切替日移行*/

    DECLARE
        updDttm    datetime;            -- 更新日時

    SET updDttm = now();

    INSERT INTO `T_ClaimHistory`
        (`Seq`,
        `OrderSeq`,
        `ClaimSeq`,
        `ClaimDate`,
        `ClaimCpId`,
        `ClaimPattern`,
        `LimitDate`,
        `DamageDays`,
        `DamageBaseDate`,
        `DamageInterestAmount`,
        `ClaimFee`,
        `AdditionalClaimFee`,
        `PrintedFlg`,
        `PrintedDate`,
        `MailFlg`,
        `EnterpriseBillingCode`,
        `ClaimAmount`,
        `ClaimId`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `T_ClaimHistory`.`Seq`,
        `T_ClaimHistory`.`OrderSeq`,
        `T_ClaimHistory`.`ClaimSeq`,
        `T_ClaimHistory`.`ClaimDate`,
        `T_ClaimHistory`.`ClaimCpId`,
        `T_ClaimHistory`.`ClaimPattern`,
        `T_ClaimHistory`.`LimitDate`,
        `T_ClaimHistory`.`DamageDays`,
        `T_ClaimHistory`.`DamageBaseDate`,
        `T_ClaimHistory`.`DamageInterestAmount`,
        `T_ClaimHistory`.`ClaimFee`,
        `T_ClaimHistory`.`AdditionalClaimFee`,
        `T_ClaimHistory`.`PrintedFlg`,
        `T_ClaimHistory`.`PrintedDate`,
        `T_ClaimHistory`.`MailFlg`,
        `T_ClaimHistory`.`EnterpriseBillingCode`,
        `T_Order`.`UseAmount`+ `T_ClaimHistory`.`DamageInterestAmount`+`T_ClaimHistory`.`ClaimFee`+`T_ClaimHistory`.`AdditionalClaimFee`,
        `T_ClaimHistory`.`OrderSeq`,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_ClaimHistory`
    INNER JOIN  `coraldb_ikou`.`T_Order`
    ON    `coraldb_ikou`.`T_ClaimHistory`.`OrderSeq` =  `coraldb_ikou`.`T_Order`.`OrderSeq`
    WHERE `T_ClaimHistory`.`Seq` > 3999999;

END
$$

DELIMITER ;