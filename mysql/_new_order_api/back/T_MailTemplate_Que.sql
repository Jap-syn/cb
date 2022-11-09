DROP TABLE IF EXISTS `T_MailTemplate_Que`;
CREATE TABLE T_MailTemplate_Que
(
	QueSeq bigint NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'キューSeq',
	AccessType char(1) NOT NULL COMMENT 'アクセス種別 C：登録 U：更新 D：削除',
	Id bigint NOT NULL COMMENT 'テンプレートID',
	RegistDate datetime COMMENT '登録日時',
	UpdateDate datetime COMMENT '更新日時',
	ValidFlg int DEFAULT 1 NOT NULL COMMENT '有効フラグ（0：無効　1：有効）'
) AUTO_INCREMENT = 1 ENGINE = InnoDB COMMENT = 'システムプロパティキュー' DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE INDEX Idx_T_MailTemplate_Que01            ON T_MailTemplate_Que            (ValidFlg);

DROP TRIGGER IF EXISTS `DEL_T_MailTemplate_Que`;
DELIMITER ;;
CREATE TRIGGER `DEL_T_MailTemplate_Que` AFTER DELETE ON `T_MailTemplate` FOR EACH ROW
 INSERT
   INTO `T_MailTemplate_Que` (
        `AccessType`
      , `Id`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'D'
      , old.Id
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `INS_T_MailTemplate_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_T_MailTemplate_Que` AFTER INSERT ON `T_MailTemplate` FOR EACH ROW
 INSERT
   INTO `T_MailTemplate_Que` (
        `AccessType`
      , `Id`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'C'
      , new.Id
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `UPD_T_MailTemplate_Que`;
DELIMITER ;;
CREATE TRIGGER `UPD_T_MailTemplate_Que` AFTER UPDATE ON `T_MailTemplate` FOR EACH ROW
 INSERT
   INTO `T_MailTemplate_Que` (
        `AccessType`
      , `Id`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'U'
      , new.Id
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;
