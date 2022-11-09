DROP VIEW IF EXISTS `V_CloseReceiptControl`;

CREATE VIEW `V_CloseReceiptControl` 
AS
SELECT 
    `tbl`.`ReceiptSeq` AS `ReceiptSeq`,
    `tbl`.`ReceiptProcessDate` AS `ReceiptProcessDate`,
    `tbl`.`ReceiptDate` AS `ReceiptDate`,
    `tbl`.`ReceiptClass` AS `ReceiptClass`,
    `tbl`.`ReceiptAmount` AS `ReceiptAmount`,
    `tbl`.`ClaimId` AS `ClaimId`,
    `tbl`.`OrderSeq` AS `OrderSeq`,
    `tbl`.`CheckingUseAmount` AS `CheckingUseAmount`,
    `tbl`.`CheckingClaimFee` AS `CheckingClaimFee`,
    `tbl`.`CheckingDamageInterestAmount` AS `CheckingDamageInterestAmount`,
    `tbl`.`CheckingAdditionalClaimFee` AS `CheckingAdditionalClaimFee`,
    `tbl`.`DailySummaryFlg` AS `DailySummaryFlg`,
    `tbl`.`BranchBankId` AS `BranchBankId`,
    `tbl`.`DepositDate` AS `DepositDate`,
    `tbl`.`ReceiptAgentId` AS `ReceiptAgentId`,
    `tbl`.`RegistDate` AS `RegistDate`,
    `tbl`.`RegistId` AS `RegistId`,
    `tbl`.`UpdateDate` AS `UpdateDate`,
    `tbl`.`UpdateId` AS `UpdateId`,
    `tbl`.`ValidFlg` AS `ValidFlg`
FROM
    `T_ReceiptControl` `tbl`
WHERE
    (`tbl`.`ReceiptSeq` = (SELECT 
            MIN(`rc`.`ReceiptSeq`)
        FROM
            (`T_ClaimControl` `cc`
            JOIN `T_ReceiptControl` `rc`)
        WHERE
            ((`cc`.`OrderSeq` = `rc`.`OrderSeq`)
                AND (`cc`.`OrderSeq` = `tbl`.`OrderSeq`)
                AND ((`cc`.`MinClaimAmount` - (SELECT 
                    SUM(`T_ReceiptControl`.`ReceiptAmount`)
                FROM
                    `T_ReceiptControl`
                WHERE
                    ((`T_ReceiptControl`.`OrderSeq` = `cc`.`OrderSeq`)
                        AND (`T_ReceiptControl`.`ReceiptSeq` <= `rc`.`ReceiptSeq`)))) <= 0)
                AND (`rc`.`ReceiptSeq` > IFNULL((SELECT 
                            MAX(`sub1`.`ReceiptSeq`)
                        FROM
                            (`T_ReceiptControl` `sub1`
                            JOIN `AT_ReceiptControl` `sub2`)
                        WHERE
                            ((`sub1`.`ReceiptSeq` = `sub2`.`ReceiptSeq`)
                                AND (`sub1`.`OrderSeq` = `rc`.`OrderSeq`)
                                AND (`sub2`.`Rct_CancelFlg` = 1))),
                    0)))))
;
