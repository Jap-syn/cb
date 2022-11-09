DROP VIEW IF EXISTS `MV_MypageOrder`;

CREATE VIEW `MV_MypageOrder` AS
    SELECT *
    FROM coraldb_new01.T_MypageOrder
;
