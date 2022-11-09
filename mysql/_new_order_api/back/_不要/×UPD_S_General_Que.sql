-- =============================================================================
-- ■汎用シーケンスキュートリガ
-- =============================================================================
DROP TRIGGER IF EXISTS `UPD_S_General_Que`;
DELIMITER ;;
CREATE TRIGGER `UPD_S_General_Que` AFTER UPDATE ON `S_General` FOR EACH ROW
 INSERT
   INTO `S_General_Que` (
        `AccessType`
      , `SeqName`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'U'
      , new.SeqName
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;