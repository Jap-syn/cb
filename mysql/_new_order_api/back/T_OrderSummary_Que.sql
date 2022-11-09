-- =============================================================================
-- ■連携DB　注文サマリーキュー
-- =============================================================================
DROP TABLE IF EXISTS `T_OrderSummary_Que`;
CREATE TABLE T_OrderSummary_Que
(
	QueSeq bigint NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'キューSeq',
	AccessType char(1) NOT NULL COMMENT 'アクセス種別 C：登録 U：更新 D：削除',
	SummaryId bigint NOT NULL COMMENT '注文サマリーID',
	RegistDate datetime COMMENT '登録日時',
	UpdateDate datetime COMMENT '更新日時',
	ValidFlg int DEFAULT 1 NOT NULL COMMENT '有効フラグ（0：無効　1：有効）'
) AUTO_INCREMENT = 1 ENGINE = InnoDB COMMENT = '注文サマリーキュー' DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE INDEX Idx_T_OrderSummary_Que01              ON T_OrderSummary_Que              (ValidFlg);

-- =============================================================================
-- ■注文サマリーキュー
-- =============================================================================
DROP TRIGGER IF EXISTS `DEL_T_OrderSummary_Que`;
DELIMITER ;;
CREATE TRIGGER `DEL_T_OrderSummary_Que` AFTER DELETE ON `T_OrderSummary` FOR EACH ROW
 INSERT
   INTO `T_OrderSummary_Que` (
        `AccessType`
      , `SummaryId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'D'
      , old.SummaryId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `INS_T_OrderSummary_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_T_OrderSummary_Que` AFTER INSERT ON `T_OrderSummary` FOR EACH ROW
 INSERT
   INTO `T_OrderSummary_Que` (
        `AccessType`
      , `SummaryId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'C'
      , new.SummaryId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `UPD_T_OrderSummary_Que`;
DELIMITER ;;
CREATE TRIGGER `UPD_T_OrderSummary_Que` AFTER UPDATE ON `T_OrderSummary` FOR EACH ROW
 INSERT
   INTO `T_OrderSummary_Que` (
        `AccessType`
      , `SummaryId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'U'
      , new.SummaryId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;
