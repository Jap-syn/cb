DROP procedure IF EXISTS `procMigrateJnbPaymentNotification`;

DELIMITER $$
CREATE PROCEDURE `procMigrateJnbPaymentNotification` ()
BEGIN

    /* 移行処理：郵便番号 */

    INSERT INTO `T_JnbPaymentNotification`
        (`NotificationSeq`,
        `TransactionId`,
        `ReceivedDate`,
        `Status`,
        `ResponseDate`,
        `ReceivedRawData`,
        `ResponseRawData`,
        `ReqBranchCode`,
        `ReqAccountNumber`,
        `AccountSeq`,
        `OrderSeq`,
        `ReceiptAmount`,
        `ReceiptProcessDate`,
        `ReceiptDate`,
        `LastProcessDate`,
        `RejectReason`,
        `DeleteFlg`)
    SELECT 
        `T_JnbPaymentNotification`.`NotificationSeq`,
        `T_JnbPaymentNotification`.`TransactionId`,
        `T_JnbPaymentNotification`.`ReceivedDate`,
        `T_JnbPaymentNotification`.`Status`,
        `T_JnbPaymentNotification`.`ResponseDate`,
        `T_JnbPaymentNotification`.`ReceivedRawData`,
        `T_JnbPaymentNotification`.`ResponseRawData`,
        `T_JnbPaymentNotification`.`ReqBranchCode`,
        `T_JnbPaymentNotification`.`ReqAccountNumber`,
        `T_JnbPaymentNotification`.`AccountSeq`,
        `T_JnbPaymentNotification`.`OrderSeq`,
        `T_JnbPaymentNotification`.`ReceiptAmount`,
        `T_JnbPaymentNotification`.`ReceiptProcessDate`,
        `T_JnbPaymentNotification`.`ReceiptDate`,
        `T_JnbPaymentNotification`.`LastProcessDate`,
        `T_JnbPaymentNotification`.`RejectReason`,
        `T_JnbPaymentNotification`.`DeleteFlg`
    FROM `coraldb_ikou`.`T_JnbPaymentNotification`;
END
$$

DELIMITER ;

