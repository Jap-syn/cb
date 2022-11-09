DROP TABLE IF EXISTS `M_DeliveryMethod_Que`;
CREATE TABLE M_DeliveryMethod_Que
(
	QueSeq bigint NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '�L���[Seq',
	AccessType char(1) NOT NULL COMMENT '�A�N�Z�X��� C�F�o�^ U�F�X�V D�F�폜',
	DeliMethodId bigint NOT NULL COMMENT '�z�����@ID',
	RegistDate datetime COMMENT '�o�^����',
	UpdateDate datetime COMMENT '�X�V����',
	ValidFlg int DEFAULT 1 NOT NULL COMMENT '�L���t���O�i0�F�����@1�F�L���j'
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