DROP procedure IF EXISTS `procMigratePayingCycle`;

DELIMITER $$
CREATE PROCEDURE `procMigratePayingCycle`()
BEGIN

    /* 移行処理：立替サイクルマスター */

    DECLARE updDttm     datetime;

    SET updDttm = now();

    INSERT INTO `M_PayingCycle`(`PayingCycleId`,`PayingCycleName`,`ListNumber`,`PayingDecisionClass`,`FixPattern`,`PayingDecisionDay`,`PayingDecisionDate1`,`PayingDecisionDate2`,`PayingDecisionDate3`,`PayingDecisionDate4`,`PayingClass`,`PayingDay`,`PayingMonth`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (5, "毎週", 1, 0, 5, 5, null, null, null, null, 0, 5, null, updDttm, 9, updDttm, 9, 1);
    INSERT INTO `M_PayingCycle`(`PayingCycleId`,`PayingCycleName`,`ListNumber`,`PayingDecisionClass`,`FixPattern`,`PayingDecisionDay`,`PayingDecisionDate1`,`PayingDecisionDate2`,`PayingDecisionDate3`,`PayingDecisionDate4`,`PayingClass`,`PayingDay`,`PayingMonth`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (11, "月２回", 2, 1, 11, null, 15, 31, null, null, 0, 5, null, updDttm, 9, updDttm, 9, 1);
    INSERT INTO `M_PayingCycle`(`PayingCycleId`,`PayingCycleName`,`ListNumber`,`PayingDecisionClass`,`FixPattern`,`PayingDecisionDay`,`PayingDecisionDate1`,`PayingDecisionDate2`,`PayingDecisionDate3`,`PayingDecisionDate4`,`PayingClass`,`PayingDay`,`PayingMonth`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (1010, "月末（翌週）", 3, 1, 101, null, 31, null, null, null, 0, 5, null, updDttm, 9, updDttm, 9, 1);
    INSERT INTO `M_PayingCycle`(`PayingCycleId`,`PayingCycleName`,`ListNumber`,`PayingDecisionClass`,`FixPattern`,`PayingDecisionDay`,`PayingDecisionDate1`,`PayingDecisionDate2`,`PayingDecisionDate3`,`PayingDecisionDate4`,`PayingClass`,`PayingDay`,`PayingMonth`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (1011, "月末（翌月１５日）", 4, 1, 101, null, 31, null, null, null, 1, null, 15, updDttm, 9, updDttm, 9, 1);

END$$

DELIMITER ;


