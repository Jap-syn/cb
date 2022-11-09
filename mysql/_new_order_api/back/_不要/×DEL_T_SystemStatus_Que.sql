-- =============================================================================
-- ■システムステータスキュー
-- =============================================================================
DROP TRIGGER IF EXISTS `DEL_T_SystemStatus_Que`;
DELIMITER ;;
CREATE TRIGGER `DEL_T_SystemStatus_Que` AFTER DELETE ON `T_SystemStatus` FOR EACH ROW
 INSERT
   INTO `T_SystemStatus_Que` (
        `AccessType`
      , `CodeId`
      , `KeyCode`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'D'
      , old.CodeId
      , old.KeyCode
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , old.ValidFlg
   )
;;
DELIMITER ;