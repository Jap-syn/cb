DROP VIEW IF EXISTS `MV_TmpImage`;

CREATE VIEW `MV_TmpImage` AS
    SELECT *
    FROM coraldb_new01.T_TmpImage
;
