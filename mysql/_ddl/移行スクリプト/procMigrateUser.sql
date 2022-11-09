DROP procedure IF EXISTS `procMigrateUser`;

DELIMITER $$
CREATE PROCEDURE `procMigrateUser` ()
BEGIN

    /* 移行処理：ユーザーマスター */
    /* 2015-08-19  APIユーザ　追加  */

    DECLARE updDttm     datetime;

    SET updDttm = now();

    -- バッチ用
    INSERT INTO `T_User`(`UserId`,`UserClass`,`Seq`,`LastLoginDate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (1, 99, 1, null, updDttm, 9, updDttm, 9, 1);

    -- 移行用
    INSERT INTO `T_User`(`UserId`,`UserClass`,`Seq`,`LastLoginDate`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`) VALUES (9, 99, 9, null, updDttm, 9, updDttm, 9, 1);

    -- 移行元：　オペレーター
    INSERT INTO `T_User`
        (`UserClass`,
        `Seq`,
        `LastLoginDate`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT 
        0,
        `T_Operator`.`OpId`,
        updDttm,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_Operator`;

    -- 移行元：　ORMオペレーター
    INSERT INTO `T_User`
        (`UserClass`,
        `Seq`,
        `LastLoginDate`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT 
        1,
        `T_OemOperator`.`OemOpId`,
        updDttm,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_OemOperator`;
    
    -- 移行元：　加盟店
    INSERT INTO `T_User`
        (`UserClass`,
        `Seq`,
        `LastLoginDate`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT 
        2,
        `T_Enterprise`.`EnterpriseId`,
        updDttm,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_Enterprise`;
-- --    2015-08-19   APIユーザー追加   START
    -- 移行元：　APIユーザー
    INSERT INTO `T_User`
        (`UserClass`,
        `Seq`,
        `LastLoginDate`,
        `RegistDate`,
        `RegistId`,
        `UpdateDate`,
        `UpdateId`,
        `ValidFlg`)
    SELECT 
        3,
        `T_ApiUser`.`ApiUserId`,
        updDttm,
        updDttm,
        9,
        updDttm,
        9,
        1
    FROM `coraldb_ikou`.`T_ApiUser`;
-- --    2015-08-19   APIユーザー追加   END

END
$$

DELIMITER ;

