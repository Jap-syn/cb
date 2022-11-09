DROP procedure IF EXISTS `procMigrateManagementCustomer2`;

DELIMITER $$
CREATE PROCEDURE `procMigrateManagementCustomer2`()
BEGIN

    /* 移行処理：管理顧客(T_ManagementCustomer) */

    DECLARE
        updDttm    datetime;

    SET updDttm = now();

    -- 移行：購入者　⇒　管理顧客

-- ↓↓↓速度対策（検索前に、インデックスを張って消したほうが早い）
ALTER TABLE `coraldb_ikou`.`T_CreditCondition` ADD INDEX `Idx_Ikou_T_CreditCondition` (`OrderSeq` ASC);
-- ↑↑↑速度対策（検索前に、インデックスを張って消したほうが早い）

-- ↓↓↓ブラック顧客フラグ、優良顧客フラグのUPDATE文が遅いので対策
    
    -- ブラックフラグ設定
    UPDATE `T_ManagementCustomer`, `coraldb_ikou`.`T_CreditCondition`, `coraldb_ikou`.`T_Customer`
    SET   `BlackFlg`          = 1
    WHERE `T_Customer`.`OrderSeq` = `T_CreditCondition`.`OrderSeq`
        AND (`T_ManagementCustomer`.`RegPhone`)          = (`T_Customer`.RegPhone)
        AND (`T_ManagementCustomer`.`RegNameKj`)         = (`T_Customer`.RegNameKj)
        AND (`T_ManagementCustomer`.`RegUnitingAddress`) = (`T_Customer`.RegUnitingAddress)
        AND `T_CreditCondition`.`Class` = 5
   ;

    -- 優良顧客の設定
    UPDATE `T_ManagementCustomer`, `coraldb_ikou`.`T_CreditCondition`, `coraldb_ikou`.`T_Customer`
    SET   `GoodFlg`           = 1
    WHERE `T_Customer`.`OrderSeq` = `T_CreditCondition`.`OrderSeq`
        AND (`T_ManagementCustomer`.`RegPhone`)          = (`T_Customer`.RegPhone)
        AND (`T_ManagementCustomer`.`RegNameKj`)         = (`T_Customer`.RegNameKj)
        AND (`T_ManagementCustomer`.`RegUnitingAddress`) = (`T_Customer`.RegUnitingAddress)
        AND `T_CreditCondition`.`Class` = 2;
-- ↑↑↑ブラック顧客フラグ、優良顧客フラグのUPDATE文が遅いので対策

-- ↓↓↓速度対策（検索前に、インデックスを張って消したほうが早い）
ALTER TABLE `coraldb_ikou`.`T_CreditCondition` DROP INDEX `Idx_Ikou_T_CreditCondition` ;
-- ↑↑↑速度対策（検索前に、インデックスを張って消したほうが早い）

END$$

DELIMITER ;