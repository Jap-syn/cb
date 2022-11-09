-- =============================================================================
-- ■連携DB　配送先キュー
-- =============================================================================
DROP TABLE IF EXISTS `T_DeliveryDestination_Que`;
CREATE TABLE T_DeliveryDestination_Que
(
	QueSeq bigint NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'キューSeq',
	AccessType char(1) NOT NULL COMMENT 'アクセス種別 C：登録 U：更新 D：削除',
	DeliDestId bigint NOT NULL COMMENT '配送先ID',
	RegistDate datetime COMMENT '登録日時',
	UpdateDate datetime COMMENT '更新日時',
	ValidFlg int DEFAULT 1 NOT NULL COMMENT '有効フラグ（0：無効　1：有効）'
) AUTO_INCREMENT = 1 ENGINE = InnoDB COMMENT = '配送先キュー' DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE INDEX Idx_T_DeliveryDestination_Que01       ON T_DeliveryDestination_Que       (ValidFlg);

-- =============================================================================
-- ■配送先キュー
-- =============================================================================
DROP TRIGGER IF EXISTS `DEL_T_DeliveryDestination_Que`;
DELIMITER ;;
CREATE TRIGGER `DEL_T_DeliveryDestination_Que` AFTER DELETE ON `T_DeliveryDestination` FOR EACH ROW
 INSERT
   INTO `T_DeliveryDestination_Que` (
        `AccessType`
      , `DeliDestId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'D'
      , old.DeliDestId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `INS_T_DeliveryDestination_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_T_DeliveryDestination_Que` AFTER INSERT ON `T_DeliveryDestination` FOR EACH ROW
 INSERT
   INTO `T_DeliveryDestination_Que` (
        `AccessType`
      , `DeliDestId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'C'
      , new.DeliDestId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `UPD_T_DeliveryDestination_Que`;
DELIMITER ;;
CREATE TRIGGER `UPD_T_DeliveryDestination_Que` AFTER UPDATE ON `T_DeliveryDestination` FOR EACH ROW
 INSERT
   INTO `T_DeliveryDestination_Que` (
        `AccessType`
      , `DeliDestId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'U'
      , new.DeliDestId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;
