-- =============================================================================
-- ■連携DB　OEMキュー
-- =============================================================================
DROP TABLE IF EXISTS `T_Oem_Que`;
CREATE TABLE T_Oem_Que
(
	QueSeq bigint NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'キューSeq',
	AccessType char(1) NOT NULL COMMENT 'アクセス種別 C：登録 U：更新 D：削除',
	OemId bigint NOT NULL COMMENT 'OEMID',
	RegistDate datetime COMMENT '登録日時',
	UpdateDate datetime COMMENT '更新日時',
	ValidFlg int DEFAULT 1 NOT NULL COMMENT '有効フラグ（0：無効　1：有効）'
) AUTO_INCREMENT = 1 ENGINE = InnoDB COMMENT = 'OEMキュー' DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE INDEX Idx_T_Oem_Que01                       ON T_Oem_Que                       (ValidFlg);

-- =============================================================================
-- ■OEMキュー
-- =============================================================================
DROP TRIGGER IF EXISTS `DEL_T_Oem_Que`;
DELIMITER ;;
CREATE TRIGGER `DEL_T_Oem_Que` AFTER DELETE ON `T_Oem` FOR EACH ROW
 INSERT
   INTO `T_Oem_Que` (
        `AccessType`
      , `OemId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'D'
      , old.OemId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `INS_T_Oem_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_T_Oem_Que` AFTER INSERT ON `T_Oem` FOR EACH ROW
 INSERT
   INTO `T_Oem_Que` (
        `AccessType`
      , `OemId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'C'
      , new.OemId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `UPD_T_Oem_Que`;
DELIMITER ;;
CREATE TRIGGER `UPD_T_Oem_Que` AFTER UPDATE ON `T_Oem` FOR EACH ROW
 INSERT
   INTO `T_Oem_Que` (
        `AccessType`
      , `OemId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'U'
      , new.OemId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;
