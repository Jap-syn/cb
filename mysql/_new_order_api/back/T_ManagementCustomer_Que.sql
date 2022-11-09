-- =============================================================================
-- ■連携DB　管理顧客キュー
-- =============================================================================
DROP TABLE IF EXISTS `T_ManagementCustomer_Que`;
CREATE TABLE T_ManagementCustomer_Que
(
	QueSeq bigint NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'キューSeq',
	AccessType char(1) NOT NULL COMMENT 'アクセス種別 C：登録 U：更新 D：削除',
	ManCustId bigint NOT NULL COMMENT '管理顧客番号',
	RegistDate datetime COMMENT '登録日時',
	UpdateDate datetime COMMENT '更新日時',
	ValidFlg int DEFAULT 1 NOT NULL COMMENT '有効フラグ（0：無効　1：有効）'
) AUTO_INCREMENT = 1 ENGINE = InnoDB COMMENT = '管理顧客キュー' DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE INDEX Idx_T_ManagementCustomer_Que01        ON T_ManagementCustomer_Que        (ValidFlg);

-- =============================================================================
-- ■管理顧客キュー
-- =============================================================================
DROP TRIGGER IF EXISTS `DEL_T_ManagementCustomer_Que`;
DELIMITER ;;
CREATE TRIGGER `DEL_T_ManagementCustomer_Que` AFTER DELETE ON `T_ManagementCustomer` FOR EACH ROW
 INSERT
   INTO `T_ManagementCustomer_Que` (
        `AccessType`
      , `ManCustId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'D'
      , old.ManCustId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `INS_T_ManagementCustomer_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_T_ManagementCustomer_Que` AFTER INSERT ON `T_ManagementCustomer` FOR EACH ROW
 INSERT
   INTO `T_ManagementCustomer_Que` (
        `AccessType`
      , `ManCustId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'C'
      , new.ManCustId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `UPD_T_ManagementCustomer_Que`;
DELIMITER ;;
CREATE TRIGGER `UPD_T_ManagementCustomer_Que` AFTER UPDATE ON `T_ManagementCustomer` FOR EACH ROW
 INSERT
   INTO `T_ManagementCustomer_Que` (
        `AccessType`
      , `ManCustId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'U'
      , new.ManCustId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;
