DROP TABLE IF EXISTS `M_CreditSystemInfo_Que`;
CREATE TABLE M_CreditSystemInfo_Que
(
	QueSeq bigint NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'キューSeq',
	AccessType char(1) NOT NULL COMMENT 'アクセス種別 C：登録 U：更新 D：削除',
	RegistDate datetime COMMENT '登録日時',
	UpdateDate datetime COMMENT '更新日時',
	ValidFlg int DEFAULT 1 NOT NULL COMMENT '有効フラグ（0：無効　1：有効）'
) AUTO_INCREMENT = 1 ENGINE = InnoDB COMMENT = '社内与信ポイントマスターキュー' DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE INDEX Idx_M_CreditSystemInfo_Que01               ON M_CreditSystemInfo_Que               (ValidFlg);

DROP TRIGGER IF EXISTS `DEL_M_CreditSystemInfo_Que`;
DELIMITER ;;
CREATE TRIGGER `DEL_M_CreditSystemInfo_Que` AFTER DELETE ON `M_CreditSystemInfo` FOR EACH ROW
 INSERT
   INTO `M_CreditSystemInfo_Que` (
        `AccessType`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'D'
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `INS_M_CreditSystemInfo_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_M_CreditSystemInfo_Que` AFTER INSERT ON `M_CreditSystemInfo` FOR EACH ROW
 INSERT
   INTO `M_CreditSystemInfo_Que` (
        `AccessType`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'C'
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `UPD_M_CreditSystemInfo_Que`;
DELIMITER ;;
CREATE TRIGGER `UPD_M_CreditSystemInfo_Que` AFTER UPDATE ON `M_CreditSystemInfo` FOR EACH ROW
 INSERT
   INTO `M_CreditSystemInfo_Que` (
        `AccessType`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'U'
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;
