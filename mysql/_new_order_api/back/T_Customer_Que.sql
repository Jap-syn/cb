-- =============================================================================
-- ■連携DB　購入者キュー
-- =============================================================================
DROP TABLE IF EXISTS `T_Customer_Que`;
CREATE TABLE T_Customer_Que
(
	QueSeq bigint NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'キューSeq',
	AccessType char(1) NOT NULL COMMENT 'アクセス種別 C：登録 U：更新 D：削除',
	CustomerId bigint NOT NULL COMMENT '購入者ID',
	RegistDate datetime COMMENT '登録日時',
	UpdateDate datetime COMMENT '更新日時',
	ValidFlg int DEFAULT 1 NOT NULL COMMENT '有効フラグ（0：無効　1：有効）'
) AUTO_INCREMENT = 1 ENGINE = InnoDB COMMENT = '購入者キュー' DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE INDEX Idx_T_Customer_Que01                  ON T_Customer_Que                  (ValidFlg);

-- =============================================================================
-- ■購入者キュー
-- =============================================================================
DROP TRIGGER IF EXISTS `DEL_T_Customer_Que`;
DELIMITER ;;
CREATE TRIGGER `DEL_T_Customer_Que` AFTER DELETE ON `T_Customer` FOR EACH ROW
 INSERT
   INTO `T_Customer_Que` (
        `AccessType`
      , `CustomerId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'D'
      , old.CustomerId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `INS_T_Customer_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_T_Customer_Que` AFTER INSERT ON `T_Customer` FOR EACH ROW
 INSERT
   INTO `T_Customer_Que` (
        `AccessType`
      , `CustomerId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'C'
      , new.CustomerId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `UPD_T_Customer_Que`;
DELIMITER ;;
CREATE TRIGGER `UPD_T_Customer_Que` AFTER UPDATE ON `T_Customer` FOR EACH ROW
 INSERT
   INTO `T_Customer_Que` (
        `AccessType`
      , `CustomerId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'U'
      , new.CustomerId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;
