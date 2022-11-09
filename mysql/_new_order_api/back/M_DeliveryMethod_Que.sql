DROP TABLE IF EXISTS `M_DeliveryMethod_Que`;
CREATE TABLE M_DeliveryMethod_Que
(
	QueSeq bigint NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'キューSeq',
	AccessType char(1) NOT NULL COMMENT 'アクセス種別 C：登録 U：更新 D：削除',
	DeliMethodId bigint NOT NULL COMMENT '配送方法ID',
	RegistDate datetime COMMENT '登録日時',
	UpdateDate datetime COMMENT '更新日時',
	ValidFlg int DEFAULT 1 NOT NULL COMMENT '有効フラグ（0：無効　1：有効）'
) AUTO_INCREMENT = 1 ENGINE = InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE INDEX Idx_M_DeliveryMethod_Que_Que01             ON M_DeliveryMethod_Que             (ValidFlg);

DROP TRIGGER IF EXISTS `DEL_M_DeliveryMethod_Que`;
DELIMITER ;;
CREATE TRIGGER `DEL_M_DeliveryMethod_Que` AFTER DELETE ON `M_DeliveryMethod` FOR EACH ROW
 INSERT
   INTO `M_DeliveryMethod_Que` (
        `AccessType`
      , `DeliMethodId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'D'
      , old.DeliMethodId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `INS_M_DeliveryMethod_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_M_DeliveryMethod_Que` AFTER INSERT ON `M_DeliveryMethod` FOR EACH ROW
 INSERT
   INTO `M_DeliveryMethod_Que` (
        `AccessType`
      , `DeliMethodId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'C'
      , new.DeliMethodId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `UPD_M_DeliveryMethod_Que`;
DELIMITER ;;
CREATE TRIGGER `UPD_M_DeliveryMethod_Que` AFTER UPDATE ON `M_DeliveryMethod` FOR EACH ROW
 INSERT
   INTO `M_DeliveryMethod_Que` (
        `AccessType`
      , `DeliMethodId`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'U'
      , new.DeliMethodId
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;