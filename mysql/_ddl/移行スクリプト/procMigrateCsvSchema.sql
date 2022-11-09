DROP procedure IF EXISTS `procMigrateCsvSchema`;

DELIMITER $$

CREATE PROCEDURE `procMigrateCsvSchema` ()
BEGIN

    /* 移行処理：APIユーザー */

    DECLARE 
        updDttm    datetime;

    SET updDttm = now();
    
    
    -- テーブルの作成
    DROP TABLE IF EXISTS T_CsvSchema;
    CREATE TABLE `T_CsvSchema` (
      `EnterpriseId` bigint(20) NOT NULL DEFAULT '0',
      `CsvClass` int(11) NOT NULL DEFAULT '0',
      `Ordinal` int(11) NOT NULL DEFAULT '0',
      `TableName` varchar(80) DEFAULT NULL,
      `ColumnName` varchar(80) DEFAULT NULL,
      `PrimaryFlg` int(11) DEFAULT NULL,
      `ValidationRegex` varchar(255) DEFAULT NULL,
      `Caption` varchar(255) DEFAULT NULL,
      `ApplicationData` varchar(4000) DEFAULT NULL,
      PRIMARY KEY (`EnterpriseId`,`CsvClass`,`Ordinal`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
    -- 移行
    INSERT INTO T_CsvSchema
    (   EnterpriseId
    ,   CsvClass
    ,   Ordinal
    ,   TableName
    ,   ColumnName
    ,   PrimaryFlg
    ,   ValidationRegex
    ,   Caption
    ,   ApplicationData
    ) 
    SELECT
        ikou.EnterpriseId
    ,   ikou.CsvClass
    ,   ikou.Ordinal
    ,   ikou.TableName
    ,   ikou.ColumnName
    ,   ikou.PrimaryFlg
    ,   ikou.ValidationRegex
    ,   ikou.Caption
    ,   ikou.ApplicationData
    FROM coraldb_ikou.T_CsvSchema ikou;
    
END
$$

DELIMITER ;