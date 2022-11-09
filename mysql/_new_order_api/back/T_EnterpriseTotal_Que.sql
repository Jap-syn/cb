-- =============================================================================
-- ■連携DB　加盟店集計キュー
-- =============================================================================
DROP TABLE IF EXISTS `T_EnterpriseTotal_Que`;
CREATE TABLE T_EnterpriseTotal_Que
(
	QueSeq bigint NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'キューSeq',
	AccessType char(1) NOT NULL COMMENT 'アクセス種別 C：登録 U：更新 D：削除',
	EnterpriseId bigint NOT NULL COMMENT '加盟店ID',
	RegistDate datetime COMMENT '登録日時',
	UpdateDate datetime COMMENT '更新日時',
	ValidFlg int DEFAULT 1 NOT NULL COMMENT '有効フラグ（0：無効　1：有効）'
) AUTO_INCREMENT = 1 ENGINE = InnoDB COMMENT = '加盟店集計キュー' DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE INDEX Idx_T_EnterpriseTotal_Que01           ON T_EnterpriseTotal_Que           (ValidFlg);

-- =============================================================================
-- ■加盟店集計キュー
-- =============================================================================
DROP TRIGGER IF EXISTS `DEL_T_EnterpriseTotal_Que`;
DELIMITER ;;
CREATE TRIGGER `DEL_T_EnterpriseTotal_Que` AFTER DELETE ON `T_EnterpriseTotal` FOR EACH ROW
 INSERT
   INTO `T_EnterpriseTotal_Que` (
        `AccessType`
      , `EnterpriseId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'D'
      , old.EnterpriseId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `INS_T_EnterpriseTotal_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_T_EnterpriseTotal_Que` AFTER INSERT ON `T_EnterpriseTotal` FOR EACH ROW
 INSERT
   INTO `T_EnterpriseTotal_Que` (
        `AccessType`
      , `EnterpriseId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'C'
      , new.EnterpriseId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `UPD_T_EnterpriseTotal_Que`;
DELIMITER ;;
CREATE TRIGGER `UPD_T_EnterpriseTotal_Que` AFTER UPDATE ON `T_EnterpriseTotal` FOR EACH ROW
 INSERT
   INTO `T_EnterpriseTotal_Que` (
        `AccessType`
      , `EnterpriseId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'U'
      , new.EnterpriseId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;
