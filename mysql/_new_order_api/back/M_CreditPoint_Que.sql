-- =============================================================================
-- ■連携DB　社内与信ポイントマスターキュー
-- =============================================================================
DROP TABLE IF EXISTS `M_CreditPoint_Que`;
CREATE TABLE M_CreditPoint_Que
(
	QueSeq bigint NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'キューSeq',
	AccessType char(1) NOT NULL COMMENT 'アクセス種別 C：登録 U：更新 D：削除',
	CreditCriterionId int NOT NULL COMMENT '与信判定基準ID ※コードマスターにて管理',
	CpId int DEFAULT 0 NOT NULL COMMENT '与信ポイントID',
	RegistDate datetime COMMENT '登録日時',
	UpdateDate datetime COMMENT '更新日時',
	ValidFlg int DEFAULT 1 NOT NULL COMMENT '有効フラグ（0：無効　1：有効）'
) AUTO_INCREMENT = 1 ENGINE = InnoDB COMMENT = '社内与信ポイントマスターキュー' DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE INDEX Idx_M_CreditPoint_Que01               ON M_CreditPoint_Que               (ValidFlg);

-- =============================================================================
-- ■社内与信ポイントマスターキュートリガ
-- =============================================================================
DROP TRIGGER IF EXISTS `DEL_M_CreditPoint_Que`;
DELIMITER ;;
CREATE TRIGGER `DEL_M_CreditPoint_Que` AFTER DELETE ON `M_CreditPoint` FOR EACH ROW
 INSERT
   INTO `M_CreditPoint_Que` (
        `AccessType`
      , `CreditCriterionId`
      , `CpId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'D'
      , old.CreditCriterionId
      , old.CpId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `INS_M_CreditPoint_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_M_CreditPoint_Que` AFTER INSERT ON `M_CreditPoint` FOR EACH ROW
 INSERT
   INTO `M_CreditPoint_Que` (
        `AccessType`
      , `CreditCriterionId`
      , `CpId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'C'
      , new.CreditCriterionId
      , new.CpId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `UPD_M_CreditPoint_Que`;
DELIMITER ;;
CREATE TRIGGER `UPD_M_CreditPoint_Que` AFTER UPDATE ON `M_CreditPoint` FOR EACH ROW
 INSERT
   INTO `M_CreditPoint_Que` (
        `AccessType`
      , `CreditCriterionId`
      , `CpId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'U'
      , new.CreditCriterionId
      , new.CpId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;
