-- =============================================================================
-- ■ジンテック結果キュー
-- =============================================================================
DROP TRIGGER IF EXISTS `DEL_T_JtcResult_Que`;
DELIMITER ;;
CREATE TRIGGER `DEL_T_JtcResult_Que` AFTER DELETE ON `T_JtcResult` FOR EACH ROW
 INSERT
   INTO `T_JtcResult_Que` (
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