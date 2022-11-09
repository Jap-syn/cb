-- =============================================================================
-- ■与信審査結果キュートリガ
-- =============================================================================
DROP TRIGGER IF EXISTS `INS_T_CjResult_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_T_CjResult_Que` AFTER INSERT ON `T_CjResult` FOR EACH ROW
 INSERT
   INTO `T_CjResult_Que` (
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