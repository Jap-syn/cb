DROP procedure IF EXISTS `procMigrateCreditJudgeThreshold`;

DELIMITER $$
CREATE PROCEDURE `procMigrateCreditJudgeThreshold` ()
BEGIN
    /* 移行処理：与信閾値 */

    DECLARE
        updDttm    datetime;

	set updDttm = now();

	INSERT INTO `T_CreditJudgeThreshold`
		(`Seq`,
		`CreditCriterionId`,
-- 		`UserAmountOver`,
		`JudgeSystemHoldMAX`,
		`JudgeSystemHoldMIN`,
		`CoreSystemHoldMAX`,
		`CoreSystemHoldMIN`,
		`RegistDate`,
		`RegistId`,
		`UpdateDate`,
		`UpdateId`,
		`ValidFlg`)
	SELECT `T_CreditJudgeThreshold`.`Seq`,
		0,
-- 		`T_CreditJudgeThreshold`.`UserAmountOver`,
		`T_CreditJudgeThreshold`.`JudgeSystemHoldMAX`,
		`T_CreditJudgeThreshold`.`JudgeSystemHoldMIN`,
		1000,
		-1000,
        updDttm,
        9,
        updDttm,
        9,
        1
	FROM `coraldb_ikou`.`T_CreditJudgeThreshold`;

END$$

DELIMITER ;

