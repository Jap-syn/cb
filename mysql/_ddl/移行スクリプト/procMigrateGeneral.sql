DROP procedure IF EXISTS `procMigrateGeneral`;

DELIMITER $$
CREATE PROCEDURE `procMigrateGeneral` ()

BEGIN

    /* 移行処理：汎用シーケンス */

    INSERT INTO `S_General` (`SeqName`,`Value`) SELECT `S_General`.`SeqName`, `S_General`.`Value` FROM `coraldb_ikou`.`S_General`;
    INSERT INTO `S_General` (`SeqName`,`Value`) VALUES ('CombinedHistorySeq', 1);
    INSERT INTO `S_General` (`SeqName`,`Value`) VALUES ('CombinedListId', 1);
END
$$

DELIMITER ;
