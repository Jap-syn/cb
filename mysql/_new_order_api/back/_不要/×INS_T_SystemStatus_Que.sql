-- =============================================================================
-- ■システムステータスキュー
-- =============================================================================
DROP TRIGGER IF EXISTS `INS_T_SystemStatus_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_T_SystemStatus_Que` AFTER INSERT ON `T_SystemStatus` FOR EACH ROW
 INSERT
   INTO `T_SystemStatus_Que` (
        `AccessType`
      , `CodeId`
      , `KeyCode`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'C'  
      , new.CodeId
      , new.KeyCode
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , new.ValidFlg
   )
;;
DELIMITER ;