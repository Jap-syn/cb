DROP procedure IF EXISTS `procMigrateAuthenticationLog`;

DELIMITER $$
CREATE PROCEDURE `procMigrateAuthenticationLog` ()
BEGIN

    /* 移行処理：認証ログ */

    INSERT INTO `T_AuthenticationLog`
        (`Seq`,
        `LogType`,
        `TargetApp`,
        `LoginId`,
        `AltLoginId`,
        `IpAddress`,
        `ClientHash`,
        `Result`,
        `LogTime`,
        `DeleteFlg`,
        `OemAccessId`)
    SELECT 
        `T_AuthenticationLog`.`Seq`,
        `T_AuthenticationLog`.`LogType`,
        `T_AuthenticationLog`.`TargetApp`,
        `T_AuthenticationLog`.`LoginId`,
        `T_AuthenticationLog`.`AltLoginId`,
        `T_AuthenticationLog`.`IpAddress`,
        `T_AuthenticationLog`.`ClientHash`,
        `T_AuthenticationLog`.`Result`,
        `T_AuthenticationLog`.`LogTime`,
        `T_AuthenticationLog`.`DeleteFlg`,
        `T_AuthenticationLog`.`OemAccessId`
    FROM `coraldb_ikou`.`T_AuthenticationLog`;
END
$$

DELIMITER ;

