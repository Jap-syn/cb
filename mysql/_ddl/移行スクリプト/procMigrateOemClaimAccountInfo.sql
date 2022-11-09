DROP procedure IF EXISTS `procMigrateOemClaimAccountInfo`;

DELIMITER $$
CREATE PROCEDURE `procMigrateOemClaimAccountInfo` ()
BEGIN

    /* 移行処理：OEM請求口座 */

    DECLARE
        updDttm    datetime;        -- 更新日時

    SET updDttm = now();

    INSERT INTO `T_OemClaimAccountInfo`
        (`ClaimAccountSeq`,
        `ClaimHistorySeq`,
        `OrderSeq`,
        `InnerSeq`,
        `Bk_ServiceKind`,
        `Bk_BankCode`,
        `Bk_BranchCode`,
        `Bk_BankName`,
        `Bk_BranchName`,
        `Bk_DepositClass`,
        `Bk_AccountNumber`,
        `Bk_AccountHolder`,
        `Yu_SubscriberName`,
        `Yu_AccountNumber`,
        `Yu_ChargeClass`,
        `Yu_SubscriberData`,
        `Yu_Option1`,
        `Yu_Option2`,
        `Yu_Option3`,
        `Yu_MtOcrCode1`,
        `Yu_MtOcrCode2`,
        `Yu_DtCode`,
        `Cv_ReceiptAgentName`,
        `Cv_ReceiptAgentCode`,
        `Cv_BarcodeLogicName`,
        `Cv_SubscriberCode`,
        `Cv_Option1`,
        `Cv_Option2`,
        `Cv_Option3`,
        `Cv_BarcodeData`,
        `Cv_BarcodeString1`,
        `Cv_BarcodeString2`,
        `RegistDate`,
        `Status`,
        `TaxAmount`,
        `Bk_AccountHolderKn`,
        `Cv_SubscriberName`,
        `ClaimLayoutMode`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `T_OemClaimAccountInfo`.`ClaimAccountSeq`,
        `T_OemClaimAccountInfo`.`ClaimHistorySeq`,
        `T_OemClaimAccountInfo`.`OrderSeq`,
        `T_OemClaimAccountInfo`.`InnerSeq`,
        `T_OemClaimAccountInfo`.`Bk_ServiceKind`,
        `T_OemClaimAccountInfo`.`Bk_BankCode`,
        `T_OemClaimAccountInfo`.`Bk_BranchCode`,
        `T_OemClaimAccountInfo`.`Bk_BankName`,
        `T_OemClaimAccountInfo`.`Bk_BranchName`,
        `T_OemClaimAccountInfo`.`Bk_DepositClass`,
        `T_OemClaimAccountInfo`.`Bk_AccountNumber`,
        `T_OemClaimAccountInfo`.`Bk_AccountHolder`,
        `T_OemClaimAccountInfo`.`Yu_SubscriberName`,
        `T_OemClaimAccountInfo`.`Yu_AccountNumber`,
        `T_OemClaimAccountInfo`.`Yu_ChargeClass`,
        `T_OemClaimAccountInfo`.`Yu_SubscriberData`,
        `T_OemClaimAccountInfo`.`Yu_Option1`,
        `T_OemClaimAccountInfo`.`Yu_Option2`,
        `T_OemClaimAccountInfo`.`Yu_Option3`,
        `T_OemClaimAccountInfo`.`Yu_MtOcrCode1`,
        `T_OemClaimAccountInfo`.`Yu_MtOcrCode2`,
        `T_OemClaimAccountInfo`.`Yu_DtCode`,
        `T_OemClaimAccountInfo`.`Cv_ReceiptAgentName`,
        `T_OemClaimAccountInfo`.`Cv_ReceiptAgentCode`,
        `T_OemClaimAccountInfo`.`Cv_BarcodeLogicName`,
        `T_OemClaimAccountInfo`.`Cv_SubscriberCode`,
        `T_OemClaimAccountInfo`.`Cv_Option1`,
        `T_OemClaimAccountInfo`.`Cv_Option2`,
        `T_OemClaimAccountInfo`.`Cv_Option3`,
        `T_OemClaimAccountInfo`.`Cv_BarcodeData`,
        `T_OemClaimAccountInfo`.`Cv_BarcodeString1`,
        `T_OemClaimAccountInfo`.`Cv_BarcodeString2`,
        `T_OemClaimAccountInfo`.`RegistDate`,
        `T_OemClaimAccountInfo`.`Status`,
        `T_OemClaimAccountInfo`.`TaxAmount`,
        `T_OemClaimAccountInfo`.`Bk_AccountHolderKn`,
        `T_OemClaimAccountInfo`.`Cv_SubscriberName`,
        `T_OemClaimAccountInfo`.`ClaimLayoutMode`,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_OemClaimAccountInfo`;
END
$$

DELIMITER ;

