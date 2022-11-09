DROP procedure IF EXISTS `procMigrateApiUser`;

DELIMITER $$

CREATE PROCEDURE `procMigrateApiUser` ()
BEGIN

    /* 移行処理：APIユーザー */

    DECLARE 
        updDttm    datetime;

    SET updDttm = now();

    INSERT INTO `T_ApiUser`
        (`ApiUserId`,
        `ApiUserNameKj`,
        `ApiUserNameKn`,
        `RegistDate`,
        `ServiceInDate`,
        `AuthenticationKey`,
        `PostalCode`,
        `PrefectureCode`,
        `PrefectureName`,
        `City`,
        `Town`,
        `Building`,
        `RepNameKj`,
        `RepNameKn`,
        `Phone`,
        `Fax`,
        `CpNameKj`,
        `CpNameKn`,
        `DivisionName`,
        `MailAddress`,
        `ContactPhoneNumber`,
        `ContactFaxNumber`,
        `ValidFlg`,
        `InvalidatedDate`,
        `InvalidatedReason`,
        `ConnectIpAddressList`,
        `Note`,
        `OemId`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`)
    SELECT
        `T_ApiUser`.`ApiUserId`,
        `T_ApiUser`.`ApiUserNameKj`,
        `T_ApiUser`.`ApiUserNameKn`,
        `T_ApiUser`.`RegistDate`,
        `T_ApiUser`.`ServiceInDate`,
        `T_ApiUser`.`AuthenticationKey`,
        `T_ApiUser`.`PostalCode`,
        `T_ApiUser`.`PrefectureCode`,
        `T_ApiUser`.`PrefectureName`,
        `T_ApiUser`.`City`,
        `T_ApiUser`.`Town`,
        `T_ApiUser`.`Building`,
        `T_ApiUser`.`RepNameKj`,
        `T_ApiUser`.`RepNameKn`,
        `T_ApiUser`.`Phone`,
        `T_ApiUser`.`Fax`,
        `T_ApiUser`.`CpNameKj`,
        `T_ApiUser`.`CpNameKn`,
        `T_ApiUser`.`DivisionName`,
        `T_ApiUser`.`MailAddress`,
        `T_ApiUser`.`ContactPhoneNumber`,
        `T_ApiUser`.`ContactFaxNumber`,
        IFNULL(`T_ApiUser`.`ValidFlg`, 1),
        `T_ApiUser`.`InvalidatedDate`,
        `T_ApiUser`.`InvalidatedReason`,
        `T_ApiUser`.`ConnectIpAddressList`,
        `T_ApiUser`.`Note`,
        `T_ApiUser`.`OemId`,
        9,
        updDttm,
        9
    FROM `coraldb_ikou`.`T_ApiUser`;
END
$$

DELIMITER ;