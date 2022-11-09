DROP TABLE IF EXISTS `T_OrderNotClose_Que`;
CREATE TABLE T_OrderNotClose_Que
(
	QueSeq bigint NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '�L���[Seq',
	AccessType char(1) NOT NULL COMMENT '�A�N�Z�X��� C�F�o�^ U�F�X�V D�F�폜',
	OrderSeq bigint NOT NULL COMMENT '����Seq',
	RegistDate datetime COMMENT '�o�^����',
	UpdateDate datetime COMMENT '�X�V����',
	ValidFlg int DEFAULT 1 NOT NULL COMMENT '�L���t���O�i0�F�����@1�F�L���j'
) AUTO_INCREMENT = 1 ENGINE = InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE INDEX Idx_T_OrderNotClose_Que01             ON T_OrderNotClose_Que             (ValidFlg);

DROP TRIGGER IF EXISTS `DEL_T_OrderNotClose_Que`;
DELIMITER ;;
CREATE TRIGGER `DEL_T_OrderNotClose_Que` AFTER DELETE ON `T_OrderNotClose` FOR EACH ROW
 INSERT
   INTO `T_OrderNotClose_Que` (
        `AccessType`
      , `OrderSeq`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'D'
      , old.OrderSeq
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `INS_T_OrderNotClose_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_T_OrderNotClose_Que` AFTER INSERT ON `T_OrderNotClose` FOR EACH ROW
 INSERT
   INTO `T_OrderNotClose_Que` (
        `AccessType`
      , `OrderSeq`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'C'
      , new.OrderSeq
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `UPD_T_OrderNotClose_Que`;
DELIMITER ;;
CREATE TRIGGER `UPD_T_OrderNotClose_Que` AFTER UPDATE ON `T_OrderNotClose` FOR EACH ROW
 INSERT
   INTO `T_OrderNotClose_Que` (
        `AccessType`
      , `OrderSeq`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'U'
      , new.OrderSeq
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;
