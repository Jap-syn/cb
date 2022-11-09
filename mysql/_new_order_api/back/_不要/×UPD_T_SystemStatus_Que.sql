-- =============================================================================
-- ■システムステータスキュー
-- =============================================================================
DROP TRIGGER IF EXISTS `UPD_T_SystemStatus_Que`;
DELIMITER ;;
CREATE TRIGGER `UPD_T_SystemStatus_Que` AFTER UPDATE ON `T_SystemStatus` FOR EACH ROW
 INSERT
   INTO `T_SystemStatus_Que` (
        `AccessType`
      , `CodeId`
      , `KeyCode`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'U'
      , new.CodeId
      , new.KeyCode
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , new.ValidFlg
   )
;;
DELIMITER ;