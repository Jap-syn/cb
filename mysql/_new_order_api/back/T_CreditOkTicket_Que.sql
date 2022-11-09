-- =============================================================================
-- ■連携DB　与信OKチケットキュー
-- =============================================================================
DROP TABLE IF EXISTS `T_CreditOkTicket_Que`;
CREATE TABLE T_CreditOkTicket_Que
(
	QueSeq bigint NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'キューSeq',
	AccessType char(1) NOT NULL COMMENT 'アクセス種別 C：登録 U：更新 D：削除',
	Seq bigint COMMENT 'シーケンス',
	RegistDate datetime COMMENT '登録日時',
	UpdateDate datetime COMMENT '更新日時',
	ValidFlg int DEFAULT 1 NOT NULL COMMENT '有効フラグ（0：無効　1：有効）'
) AUTO_INCREMENT = 1 ENGINE = InnoDB COMMENT = '与信OKチケットキュー' DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE INDEX Idx_T_CreditOkTicket_Que01            ON T_CreditOkTicket_Que            (ValidFlg);

-- =============================================================================
-- ■与信OKチケットキュー
-- =============================================================================
DROP TRIGGER IF EXISTS `DEL_T_CreditOkTicket_Que`;
DELIMITER ;;
CREATE TRIGGER `DEL_T_CreditOkTicket_Que` AFTER DELETE ON `T_CreditOkTicket` FOR EACH ROW
 INSERT
   INTO `T_CreditOkTicket_Que` (
        `AccessType`
      , `Seq`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'D'
      , old.Seq
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;

DROP TRIGGER IF EXISTS `INS_T_CreditOkTicket_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_T_CreditOkTicket_Que` AFTER INSERT ON `T_CreditOkTicket` FOR EACH ROW
 INSERT
   INTO `T_CreditOkTicket_Que` (
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

DROP TRIGGER IF EXISTS `UPD_T_CreditOkTicket_Que`;
DELIMITER ;;
CREATE TRIGGER `UPD_T_CreditOkTicket_Que` AFTER UPDATE ON `T_CreditOkTicket` FOR EACH ROW
 INSERT
   INTO `T_CreditOkTicket_Que` (
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
