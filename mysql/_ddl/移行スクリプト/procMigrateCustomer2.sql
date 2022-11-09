DROP procedure IF EXISTS `procMigrateCustomer2`;

DELIMITER $$
CREATE PROCEDURE `procMigrateCustomer2` ()
BEGIN

    /* 移行処理：購入者2（加盟店顧客SEQのセット）
       ！！ 移行処理：加盟店顧客 終了後、実行！！
     */

    DECLARE
        updDttm    datetime;                            -- 更新日時

    SET updDttm = now();

    -- ------------------------------------------------------------------
    -- 20151006_2034
    --   400万件以上の一括UPDATEだとタイムアウト等が発生する可能性があるため、
    --   200万件単位でUPDATEを区切る
    -- ------------------------------------------------------------------
    
    --   購入者へ加盟店顧客SEQのセット
    UPDATE `T_Customer`, `T_Order`, `T_EnterpriseCustomer`
    SET
        `T_Customer`.`EntCustSeq` = `T_EnterpriseCustomer`.`EntCustSeq`,
        `T_Customer`.`UpdateDate` = updDttm,
        `T_Customer`.`UpdateId` = 9
    WHERE `T_Order`.`OrderSeq` = `T_Customer`.`OrderSeq`
    AND   (`T_Customer`.`RegNameKj`) = (`T_EnterpriseCustomer`.`RegNameKj`)
    AND   (`T_Customer`.`RegPhone`) = (`T_EnterpriseCustomer`.`RegPhone`)
    AND   (`T_Customer`.`RegUnitingAddress`) = (`T_EnterpriseCustomer`.`RegUnitingAddress`)
    AND   `T_EnterpriseCustomer`.`EnterpriseId` = `T_Order`.`EnterpriseId`
    AND   `T_Customer`.`CustomerId` <= 2000000;


    UPDATE `T_Customer`, `T_Order`, `T_EnterpriseCustomer`
    SET
        `T_Customer`.`EntCustSeq` = `T_EnterpriseCustomer`.`EntCustSeq`,
        `T_Customer`.`UpdateDate` = updDttm,
        `T_Customer`.`UpdateId` = 9
    WHERE `T_Order`.`OrderSeq` = `T_Customer`.`OrderSeq`
    AND   (`T_Customer`.`RegNameKj`) = (`T_EnterpriseCustomer`.`RegNameKj`)
    AND   (`T_Customer`.`RegPhone`) = (`T_EnterpriseCustomer`.`RegPhone`)
    AND   (`T_Customer`.`RegUnitingAddress`) = (`T_EnterpriseCustomer`.`RegUnitingAddress`)
    AND   `T_EnterpriseCustomer`.`EnterpriseId` = `T_Order`.`EnterpriseId`
    AND   `T_Customer`.`CustomerId` > 2000000;


END
$$

DELIMITER ;
