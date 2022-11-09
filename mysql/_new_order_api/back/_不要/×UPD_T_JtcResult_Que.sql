-- =============================================================================
-- ■ジンテック結果キュー
-- =============================================================================
DROP TRIGGER IF EXISTS `UPD_T_JtcResult_Que`;
DELIMITER ;;
CREATE TRIGGER `UPD_T_JtcResult_Que` AFTER UPDATE ON `T_JtcResult` FOR EACH ROW
 INSERT
   INTO `T_JtcResult_Que` (
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