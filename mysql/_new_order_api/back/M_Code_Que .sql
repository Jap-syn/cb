-- =============================================================================
-- ■連携DB　コードマスターキュー
-- =============================================================================
DROP TABLE IF EXISTS `M_Code_Que`;
CREATE TABLE M_Code_Que
(
	QueSeq bigint NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'キューSeq',
	AccessType char(1) NOT NULL COMMENT 'アクセス種別 C：登録 U：更新 D：削除',
	CodeId int NOT NULL COMMENT 'コード識別ID 旧：マスタークラス',
	KeyCode int NOT NULL COMMENT 'KEYコード 旧：マスターコード',
	RegistDate datetime COMMENT '登録日時',
	UpdateDate datetime COMMENT '更新日時',
	ValidFlg int DEFAULT 1 NOT NULL COMMENT '有効フラグ（0：無効　1：有効）'
) AUTO_INCREMENT = 1 ENGINE = InnoDB COMMENT = 'コードマスターキュー' DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE INDEX Idx_M_Code_Que01                      ON M_Code_Que                      (ValidFlg);

-- =============================================================================
-- ■コードマスターキュートリガ
-- =============================================================================
DROP TRIGGER IF EXISTS `DEL_M_Code_Que`;
DELIMITER ;;
CREATE TRIGGER `DEL_M_Code_Que` AFTER DELETE ON `M_Code` FOR EACH ROW
 INSERT
   INTO `M_Code_Que` (
        `AccessType`
      , `CodeId`
      , `KeyCode`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'D'
      , old.CodeId
      , old.KeyCode
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `INS_M_Code_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_M_Code_Que` AFTER INSERT ON `M_Code` FOR EACH ROW
 INSERT
   INTO `M_Code_Que` (
        `AccessType`
      , `CodeId`
      , `KeyCode`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'C'  
      , new.CodeId
      , new.KeyCode
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `UPD_M_Code_Que`;
DELIMITER ;;
CREATE TRIGGER `UPD_M_Code_Que` AFTER UPDATE ON `M_Code` FOR EACH ROW
 INSERT
   INTO `M_Code_Que` (
        `AccessType`
      , `CodeId`
      , `KeyCode`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'U'
      , new.CodeId
      , new.KeyCode
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;
