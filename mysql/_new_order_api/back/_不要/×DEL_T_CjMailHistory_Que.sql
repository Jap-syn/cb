-- =============================================================================
-- ■与信結果メール履歴キュートリガ
-- =============================================================================
DROP TRIGGER IF EXISTS `DEL_T_CjMailHistory_Que`;
DELIMITER ;;
CREATE TRIGGER `DEL_T_CjMailHistory_Que` AFTER DELETE ON `T_CjMailHistory` FOR EACH ROW
 INSERT
   INTO `T_CjMailHistory_Que` (
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