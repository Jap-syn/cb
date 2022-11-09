-- =============================================================================
-- ■与信審査結果キュートリガ
-- =============================================================================
DROP TRIGGER IF EXISTS `UPD_T_CjResult_Que`;
DELIMITER ;;
CREATE TRIGGER `UPD_T_CjResult_Que` AFTER UPDATE ON `T_CjResult` FOR EACH ROW
 INSERT
   INTO `T_CjResult_Que` (
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