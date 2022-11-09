DROP procedure IF EXISTS `procMigrateManagementCustomer`;

DELIMITER $$
CREATE PROCEDURE `procMigrateManagementCustomer`()
BEGIN

    /* 移行処理：管理顧客(T_ManagementCustomer) */

    DECLARE
        updDttm    datetime;

    SET updDttm = now();

    -- 移行：購入者　⇒　管理顧客

    -- 管理顧客情報登録処理
    INSERT INTO `T_ManagementCustomer`(
        `GoodFlg`,
        `BlackFlg`,
        `ClaimerFlg`,
        `RemindStopFlg`,
        `IdentityDocumentFlg`,
        `NameKj`,
        `NameKn`,
        `PostalCode`,
        `PrefectureCode`,
        `PrefectureName`,
        `City`,
        `Town`,
        `Building`,
        `UnitingAddress`,
        `Phone`,
        `MailAddress`,
        `Note`,
        `RegNameKj`,
        `RegUnitingAddress`,
        `RegPhone`,
        `SearchNameKj`,
        `SearchNameKn`,
        `SearchPhone`,
        `SearchUnitingAddress`,
        `IluCustomerId`,
        `IluCustomerListFlg`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`
    )
    SELECT
-- ↓↓↓ブラック顧客フラグ、優良顧客フラグのUPDATE文が遅いので対策
--        IF( (SELECT COUNT(`T_CreditCondition`.`Seq`) 
--               FROM `coraldb_ikou`.`T_CreditCondition` 
--              WHERE `T_Customer`.`OrderSeq` = `T_CreditCondition`.`OrderSeq` 
--                AND `T_CreditCondition`.`Class` = 2) > 0
--            , 1
--            , 0
--          ), -- GoodFlg
--        IF( (SELECT COUNT(`T_CreditCondition`.`Seq`) 
--               FROM `coraldb_ikou`.`T_CreditCondition` 
--              WHERE `T_Customer`.`OrderSeq` = `T_CreditCondition`.`OrderSeq` 
--                AND `T_CreditCondition`.`Class` = 5 ) > 0
--            , 1
--            , 0
--          ), -- BlackFlg 
        0,
        0,
--       2015-08-06  フラグは本番切り替え後に設定
-- ↑↑↑ブラック顧客フラグ、優良顧客フラグのUPDATE文が遅いので対策
        0,
        0,
        0,
        `T_Customer`.`NameKj`,
        convert_kana(`T_Customer`.`NameKn`),
        `T_Customer`.`PostalCode`,
        `T_Customer`.`PrefectureCode`,
        `T_Customer`.`PrefectureName`,
        `T_Customer`.`City`,
        `T_Customer`.`Town`,
        `T_Customer`.`Building`,
        `T_Customer`.`UnitingAddress`,
        `T_Customer`.`Phone`,
        `T_Customer`.`MailAddress`,
        null,
        `nc`.`RegNameKj`,
        `nc`.`RegUnitingAddress`,
        `nc`.`RegPhone`,
        `T_Customer`.`SearchNameKj`,
        convert_kana(`T_Customer`.`SearchNameKn`),
        `T_Customer`.`SearchPhone`,
        `T_Customer`.`SearchUnitingAddress`,
        NULL,
        0,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_Customer`
         INNER JOIN `T_Customer` `nc`
                 ON `T_Customer`.`CustomerId` = `nc`.`CustomerId`
                 
    WHERE `nc`.CustomerId = ( SELECT MAX(CustomerId)
                                        FROM `T_Customer` `cus`
                                             INNER JOIN `coraldb_ikou`.`T_Order` `ord`
                                                     ON `cus`.OrderSeq = `ord`.OrderSeq
                                       WHERE `cus`.RegNameKj         = `nc`.RegNameKj
                                         AND `cus`.RegPhone          = `nc`.RegPhone
                                         AND `cus`.RegUnitingAddress = `nc`.RegUnitingAddress
                                    );


END$$

DELIMITER ;