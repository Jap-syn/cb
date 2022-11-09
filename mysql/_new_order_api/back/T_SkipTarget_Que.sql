DROP TABLE IF EXISTS `T_SkipTarget_Que`;
CREATE TABLE T_SkipTarget_Que
(
	QueSeq bigint NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '�L���[Seq',
	AccessType char(1) NOT NULL COMMENT '�A�N�Z�X��� C�F�o�^ U�F�X�V D�F�폜',
	ManCustId bigint NOT NULL COMMENT '�Ǘ��ڋq�ԍ�',
	RegistDate datetime COMMENT '�o�^����',
	UpdateDate datetime COMMENT '�X�V����',
	ValidFlg int DEFAULT 1 NOT NULL COMMENT '�L���t���O�i0�F�����@1�F�L���j'
) AUTO_INCREMENT = 1 ENGINE = InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE INDEX Idx_T_SkipTarget_Que01                ON T_SkipTarget_Que                (ValidFlg);

DROP TRIGGER IF EXISTS `DEL_T_SkipTarget_Que`;
DELIMITER ;;
CREATE TRIGGER `DEL_T_SkipTarget_Que` AFTER DELETE ON `T_SkipTarget` FOR EACH ROW
 INSERT
   INTO `T_SkipTarget_Que` (
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

DROP TRIGGER IF EXISTS `INS_T_SkipTarget_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_T_SkipTarget_Que` AFTER INSERT ON `T_SkipTarget` FOR EACH ROW
 INSERT
   INTO `T_SkipTarget_Que` (
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

DROP TRIGGER IF EXISTS `UPD_T_SkipTarget_Que`;
DELIMITER ;;
CREATE TRIGGER `UPD_T_SkipTarget_Que` AFTER UPDATE ON `T_SkipTarget` FOR EACH ROW
 INSERT
   INTO `T_SkipTarget_Que` (
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
