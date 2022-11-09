DROP procedure IF EXISTS `procMigrateCustomer1B`;

DELIMITER $$
CREATE PROCEDURE `procMigrateCustomer1B` ()
BEGIN

    /* 移行処理：購入者 の切替日移行*/
    /* 2015-08-18  ひらがな→カタカナ変換  */

    DECLARE
        updDttm    datetime;                            -- 更新日時

    SET updDttm = now();

    -- 購入者の移行
    INSERT INTO `T_Customer`
        (`CustomerId`,
        `OrderSeq`,
        `NameKj`,
        `NameKn`,
        `PostalCode`,
        `PrefectureCode`,
        `PrefectureName`,
        `City`,
        `Town`,
        `Building`,
        `UnitingAddress`,
        `Hash_Name`,
        `Hash_Address`,
        `Phone`,
        `RealCallStatus`,
        `RealCallResult`,
        `RealCallScore`,
        `MailAddress`,
        `RealSendMailStatus`,
        `RealSendMailResult`,
        `RealSendMailScore`,
        `Occupation`,
        `Incre_ArName`,
        `Incre_NameScore`,
        `Incre_NameNote`,
        `Incre_ArAddr`,
        `Incre_AddressScore`,
        `Incre_AddressNote`,
        `Incre_MailDomainScore`,
        `Incre_MailDomainNote`,
        `Incre_PostalCodeScore`,
        `Incre_PostalCodeNote`,
        `Incre_ScoreTotal`,
        `eDen`,
        `PhoneHistory`,
--        `ReturnClaimFlg`,
        `Carrier`,
        `ValidTel`,
        `ValidMail`,
        `ValidAddress`,
        `ResidentCard`,
        `Cinfo1`,
        `CinfoNote1`,
        `CinfoStatus1`,
        `Cinfo2`,
        `CinfoNote2`,
        `CinfoStatus2`,
        `Cinfo3`,
        `CinfoNote3`,
        `CinfoStatus3`,
        `SearchNameKj`,
        `SearchNameKn`,
        `SearchPhone`,
        `SearchUnitingAddress`,
        `RegNameKj`,
        `RegUnitingAddress`,
        `RegPhone`,
        `Incre_ArTel`,
        `Incre_TelScore`,
        `Incre_TelNote`,
        `CorporateName`,
        `DivisionName`,
        `CpNameKj`,
        `EntCustSeq`,
        `AddressKn`,
        `RemindResult`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT
        `T_Customer`.`CustomerId`,
        `T_Customer`.`OrderSeq`,
        `T_Customer`.`NameKj`,
        convert_kana(`T_Customer`.`NameKn`),
        `T_Customer`.`PostalCode`,
        `T_Customer`.`PrefectureCode`,
        `T_Customer`.`PrefectureName`,
        `T_Customer`.`City`,
        `T_Customer`.`Town`,
        `T_Customer`.`Building`,
        `T_Customer`.`UnitingAddress`,
        `T_Customer`.`Hash_Name`,
        `T_Customer`.`Hash_Address`,
        `T_Customer`.`Phone`,
        `T_Customer`.`RealCallStatus`,
        `T_Customer`.`RealCallResult`,
        `T_Customer`.`RealCallScore`,
        `T_Customer`.`MailAddress`,
        `T_Customer`.`RealSendMailStatus`,
        `T_Customer`.`RealSendMailResult`,
        `T_Customer`.`RealSendMailScore`,
        `T_Customer`.`Occupation`,
        `T_Customer`.`Incre_ArName`,
        `T_Customer`.`Incre_NameScore`,
        `T_Customer`.`Incre_NameNote`,
        `T_Customer`.`Incre_ArAddr`,
        `T_Customer`.`Incre_AddressScore`,
        `T_Customer`.`Incre_AddressNote`,
        `T_Customer`.`Incre_MailDomainScore`,
        `T_Customer`.`Incre_MailDomainNote`,
        `T_Customer`.`Incre_PostalCodeScore`,
        `T_Customer`.`Incre_PostalCodeNote`,
        `T_Customer`.`Incre_ScoreTotal`,
        `T_Customer`.`eDen`,
        `T_Customer`.`PhoneHistory`,
--        `T_Customer`.`ReturnClaimFlg`,
        `T_Customer`.`Carrier`,
        `T_Customer`.`ValidTel`,
        `T_Customer`.`ValidMail`,
        `T_Customer`.`ValidAddress`,
        `T_Customer`.`ResidentCard`,
        `T_Customer`.`Cinfo1`,
        `T_Customer`.`CinfoNote1`,
        `T_Customer`.`CinfoStatus1`,
        `T_Customer`.`Cinfo2`,
        `T_Customer`.`CinfoNote2`,
        `T_Customer`.`CinfoStatus2`,
        `T_Customer`.`Cinfo3`,
        `T_Customer`.`CinfoNote3`,
        `T_Customer`.`CinfoStatus3`,
        `T_Customer`.`SearchNameKj`,
        convert_kana(`T_Customer`.`SearchNameKn`),
        `T_Customer`.`SearchPhone`,
        `T_Customer`.`SearchUnitingAddress`,
        CASE
            WHEN IFNULL(`T_Customer`.`RegNameKj`, '') = '' THEN
                `T_Customer`.`NameKj`
            ELSE 
                `T_Customer`.`RegNameKj`
        END,
        CASE
            WHEN IFNULL(`T_Customer`.`RegUnitingAddress`, '') = '' THEN
                `T_Customer`.`UnitingAddress`
            ELSE
                `T_Customer`.`RegUnitingAddress`
        END,
        CASE
            WHEN IFNULL(`T_Customer`.`RegPhone`, '') = '' THEN
                `T_Customer`.`Phone`
            ELSE
                `T_Customer`.`RegPhone`
        END,
        `T_Customer`.`Incre_ArTel`,
        `T_Customer`.`Incre_TelScore`,
        `T_Customer`.`Incre_TelNote`,
        null,
        null,
        null,
        null,
        CASE
            WHEN T.CNT = 1 THEN
                CONCAT(TRIM(T.`PrefectureKana`), TRIM(T.`CityKana`), TRIM(T.`TownKana`))
            WHEN T.CNT > 1 THEN
                CONCAT(TRIM(T.`PrefectureKana`), TRIM(T.`CityKana`))
            ELSE
                null
        END,
        null,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_Customer`
    LEFT JOIN (
        SELECT  count(`M_PostalCode`.`Seq`) as CNT,
                `M_PostalCode`.`PostalCode7`,
                `M_PostalCode`.`PrefectureKana`,
                `M_PostalCode`.`CityKana`,
                `M_PostalCode`.`TownKana`
        FROM    `coraldb_ikou`.`M_PostalCode`
        GROUP BY
                `M_PostalCode`.`PostalCode7`
    ) T
    ON replace(`coraldb_ikou`.`T_Customer`.`PostalCode`, '-', '') = T.`PostalCode7`
    WHERE  `T_Customer`.`CustomerId` > 3899999;

END
$$

DELIMITER ;
