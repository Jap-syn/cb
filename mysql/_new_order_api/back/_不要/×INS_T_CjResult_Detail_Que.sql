-- =============================================================================
-- ■与信審査結果詳細キュートリガ
-- =============================================================================
DROP TRIGGER IF EXISTS `INS_T_CjResult_Detail_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_T_CjResult_Detail_Que` AFTER INSERT ON `T_CjResult_Detail` FOR EACH ROW
 INSERT
   INTO `T_CjResult_Detail_Que` (
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