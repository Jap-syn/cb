DROP procedure IF EXISTS `procMigrateEnterpriseCustomer`;

DELIMITER $$
CREATE PROCEDURE `procMigrateEnterpriseCustomer`()
BEGIN

    /* 移行処理：加盟店顧客(T_EnterpriseCustomer)  */
    /* 2015-08-26  移行項目追加   */

    DECLARE
        updDttm    datetime;

    set updDttm = now();

    -- 移行：購入者　⇒　加盟店顧客
    -- 加盟店顧客情報登録
    INSERT INTO `T_EnterpriseCustomer`(
        `EnterpriseId`,
        `ManCustId`,
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
-- ----    2015-08-26  ADD  START
        `SearchNameKj`,
        `SearchNameKn`,
        `SearchPhone`,
        `SearchUnitingAddress`,
        `BtoBCreditLimitAmountFlg`,
        `BtoBCreditLimitAmount`,
-- ----    2015-08-26  ADD  END
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`
    )
    SELECT
        `T_Order`.`EnterpriseId`,
         null,
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
-- 2015-10-07 MOD START --------------------->
--          `T_Customer`.`RegNameKj`,
--          `T_Customer`.`RegUnitingAddress`,
--          `T_Customer`.`RegPhone`,
        `nc`.`RegNameKj`,
        `nc`.`RegUnitingAddress`,
        `nc`.`RegPhone`,
-- 2015-10-07 MOD END <---------------------
-- ----    2015-08-26  ADD  START
         `T_Customer`.`SearchNameKj`,
         `T_Customer`.`SearchNameKn`,
         `T_Customer`.`SearchPhone`,
         `T_Customer`.`SearchUnitingAddress`,
         0,
         0,
-- ----    2015-08-26  ADD  END
         updDttm,
         9,
         updDttm,
         9,
         1
    FROM `coraldb_ikou`.`T_Customer`
         INNER JOIN `coraldb_ikou`.`T_Order`
                 ON `T_Customer`.OrderSeq = `T_Order`.OrderSeq
         INNER JOIN `T_Customer` `nc`
                 ON `T_Customer`.`CustomerId` = `nc`.`CustomerId`
/*
    INNER JOIN (
                SELECT   max(`T_Customer`.`OrderSeq`) maxOrderSeq,
                         TRIM(`T_Customer`.`RegPhone`) RegPhone,
                         TRIM(`T_Customer`.`RegNameKj`) RegNameKj,
                         TRIM(`T_Customer`.`RegUnitingAddress`) RegUnitingAddress,
                         `T_Order`.`EnterpriseId` EnterpriseId
                FROM     `coraldb_ikou`.`T_Customer`
                LEFT JOIN `coraldb_ikou`.`T_Order`
                ON  `T_Order`.`OrderSeq` = `T_Customer`.`OrderSeq`
                GROUP BY RegNameKj,
                         RegPhone,
                         RegUnitingAddress,
                         EnterpriseId) AS tempOrder
    ON  tempOrder.maxOrderSeq = `coraldb_ikou`.`T_Customer`.`OrderSeq`;
*/
	WHERE `T_Customer`.CustomerId = ( SELECT MAX(`cus`.CustomerId)
                                        FROM `T_Customer` `cus`
                                             INNER JOIN `coraldb_ikou`.`T_Order` `ord`
                                                     ON `cus`.OrderSeq = `ord`.OrderSeq
                                       WHERE `nc`.RegNameKj         = `cus`.RegNameKj
                                         AND `nc`.RegPhone          = `cus`.RegPhone
                                         AND `nc`.RegUnitingAddress = `cus`.RegUnitingAddress
                                         AND `T_Order`.EnterpriseId = `ord`.EnterpriseId
                                    );

    -- 管理顧客番号設定
    UPDATE `T_EnterpriseCustomer`, `T_ManagementCustomer`
    SET    `T_EnterpriseCustomer`.`ManCustId`                  = `T_ManagementCustomer`.`ManCustId`
    WHERE  (`T_EnterpriseCustomer`.`RegUnitingAddress`) = (`T_ManagementCustomer`.`RegUnitingAddress`)
      AND  (`T_EnterpriseCustomer`.`RegPhone`)          = (`T_ManagementCustomer`.`RegPhone`)
      AND  (`T_EnterpriseCustomer`.`RegNameKj`)         = (`T_ManagementCustomer`.`RegNameKj`);

END$$

DELIMITER ;
