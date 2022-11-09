-- =============================================================================
-- ■与信審査結果キュートリガ
-- =============================================================================
DROP TRIGGER IF EXISTS `DEL_T_CjResult_Que`;
DELIMITER ;;
CREATE TRIGGER `DEL_T_CjResult_Que` AFTER DELETE ON `T_CjResult` FOR EACH ROW
 INSERT
   INTO `T_CjResult_Que` (
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