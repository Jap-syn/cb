-- =============================================================================
-- ■汎用シーケンスキュートリガ
-- =============================================================================
DROP TRIGGER IF EXISTS `DEL_S_General_Que`;
DELIMITER ;;
CREATE TRIGGER `DEL_S_General_Que` AFTER DELETE ON `S_General` FOR EACH ROW
 INSERT
   INTO `S_General_Que` (
        `AccessType`
      , `SeqName`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'D'
      , old.SeqName
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;