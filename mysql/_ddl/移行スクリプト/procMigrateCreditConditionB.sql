DROP procedure IF EXISTS `procMigrateCreditConditionB`;

DELIMITER $$
CREATE PROCEDURE `procMigrateCreditConditionB` ()
BEGIN
    /* 移行処理：社内与信条件 の切替日移行*/

    DECLARE
        updDttm    datetime;
        
	set updDttm = now();
    
	INSERT INTO `T_CreditCondition`
		(`Seq`,
		`OrderSeq`,
		`Category`,
		`Class`,
		`Cstring`,
		`CstringHash`,
		`RegistDate`,
		`ValidFlg`,
		`Point`,
		`RegCstring`,
		`Comment`,
		`RegCstringHash`,
		`ComboHash`,
		`CreditCriterionId`,
		`RegistId`,
		`UpdateDate`,
		`UpdateId`)
	SELECT 
		`T_CreditCondition`.`Seq`,
		`T_CreditCondition`.`OrderSeq`,
		`T_CreditCondition`.`Category`,
		`T_CreditCondition`.`Class`,
		.
		`T_CreditCondition`.`Cstring`,
		`T_CreditCondition`.`CstringHash`,
		updDttm,
		1,
		`T_CreditCondition`.`Point`,
		`T_CreditCondition`.`RegCstring`,
		`T_CreditCondition`.`Comment`,
		`T_CreditCondition`.`RegCstringHash`,
		`T_CreditCondition`.`ComboHash`,
        911,
        9,
        updDttm,
        9
	FROM `coraldb_ikou`.`T_CreditCondition`
	WHERE  `T_CreditCondition`.`Seq` > 8499999;
    
END$$

DELIMITER ;

