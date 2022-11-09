-- =============================================================================
-- ■汎用シーケンスキュートリガ
-- =============================================================================
DROP TRIGGER IF EXISTS `INS_S_General_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_S_General_Que` AFTER INSERT ON `S_General` FOR EACH ROW
 INSERT
   INTO `S_General_Que` (
        `AccessType`
      , `SeqName`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'C'
      , new.SeqName
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;