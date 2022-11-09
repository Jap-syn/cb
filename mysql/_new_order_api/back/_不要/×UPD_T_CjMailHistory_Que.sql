-- =============================================================================
-- ■与信結果メール履歴キュートリガ
-- =============================================================================
DROP TRIGGER IF EXISTS `UPD_T_CjMailHistory_Que`;
DELIMITER ;;
CREATE TRIGGER `UPD_T_CjMailHistory_Que` AFTER UPDATE ON `T_CjMailHistory` FOR EACH ROW
 INSERT
   INTO `T_CjMailHistory_Que` (
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