-- =============================================================================
-- ■連携DB　郵便番号キュー
-- =============================================================================
DROP TABLE IF EXISTS `M_PostalCode_Que`;
CREATE TABLE M_PostalCode_Que
(
	QueSeq bigint NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'キューSeq',
	AccessType char(1) NOT NULL COMMENT 'アクセス種別 C：登録 U：更新 D：削除',
	Seq bigint NOT NULL COMMENT '通番',
	RegistDate datetime COMMENT '登録日時',
	UpdateDate datetime COMMENT '更新日時',
	ValidFlg int DEFAULT 1 NOT NULL COMMENT '有効フラグ（0：無効　1：有効）'
) AUTO_INCREMENT = 1 ENGINE = InnoDB COMMENT = '郵便番号キュー' DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE INDEX Idx_M_PostalCode_Que01                ON M_PostalCode_Que                (ValidFlg);

-- =============================================================================
-- ■郵便番号キュートリガ
-- =============================================================================
DROP TRIGGER IF EXISTS `DEL_M_PostalCode_Que`;
DELIMITER ;;
CREATE TRIGGER `DEL_M_PostalCode_Que` AFTER DELETE ON `M_PostalCode` FOR EACH ROW
 INSERT
   INTO `M_PostalCode_Que` (
        `AccessType`
      , `Seq`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'D'
      , pld.Seq
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `INS_M_PostalCode_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_M_PostalCode_Que` AFTER INSERT ON `M_PostalCode` FOR EACH ROW
 INSERT
   INTO `M_PostalCode_Que` (
        `AccessType`
      , `Seq`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'C'
      , new.Seq
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `UPD_M_PostalCode_Que`;
DELIMITER ;;
CREATE TRIGGER `UPD_M_PostalCode_Que` AFTER UPDATE ON `M_PostalCode` FOR EACH ROW
 INSERT
   INTO `M_PostalCode_Que` (
        `AccessType`
      , `Seq`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'U'
      , new.Seq
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;
