-- =============================================================================
-- ■ジンテック結果キュー
-- =============================================================================
DROP TRIGGER IF EXISTS `INS_T_JtcResult_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_T_JtcResult_Que` AFTER INSERT ON `T_JtcResult` FOR EACH ROW
 INSERT
   INTO `T_JtcResult_Que` (
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