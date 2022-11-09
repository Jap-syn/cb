DROP procedure IF EXISTS `procMigrateSmbcRelationLog`;

DELIMITER $$
CREATE PROCEDURE `procMigrateSmbcRelationLog` ()
BEGIN
    DECLARE
        updDttm    datetime;

    SET updDttm = now();

    INSERT INTO `T_SmbcRelationLog`
        (`Seq`,
        `ClaimAccountSeq`,
        `TargetFunction`,
        `SentTime`,
        `OrderSeq`,
        `Status`,
        `SentRawData`,
        `ReceivedTime`,
        `ReceivedRawData`,
        `ErrorReason`,
        `AcceptTime`,
        `AcceptNumber`,
        `ResponseCode`,
        `ResponseMessage`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT 
        `T_SmbcRelationLog`.`Seq`,
        `T_SmbcRelationLog`.`ClaimAccountSeq`,
        `T_SmbcRelationLog`.`TargetFunction`,
        `T_SmbcRelationLog`.`SentTime`,
        `T_SmbcRelationLog`.`OrderSeq`,
        `T_SmbcRelationLog`.`Status`,
        `T_SmbcRelationLog`.`SentRawData`,
        `T_SmbcRelationLog`.`ReceivedTime`,
        `T_SmbcRelationLog`.`ReceivedRawData`,
        `T_SmbcRelationLog`.`ErrorReason`,
        `T_SmbcRelationLog`.`AcceptTime`,
        `T_SmbcRelationLog`.`AcceptNumber`,
        `T_SmbcRelationLog`.`ResponseCode`,
        `T_SmbcRelationLog`.`ResponseMessage`,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_SmbcRelationLog`;
END
$$

DELIMITER ;

