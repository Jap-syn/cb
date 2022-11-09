-- =============================================================================
-- ■与信審査結果詳細キュートリガ
-- =============================================================================
DROP TRIGGER IF EXISTS `DEL_T_CjResult_Detail_Que`;
DELIMITER ;;
CREATE TRIGGER `DEL_T_CjResult_Detail_Que` AFTER DELETE ON `T_CjResult_Detail` FOR EACH ROW
 INSERT
   INTO `T_CjResult_Detail_Que` (
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