-- =============================================================================
-- ■与信結果メール履歴キュートリガ
-- =============================================================================
DROP TRIGGER IF EXISTS `INS_T_CjMailHistory_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_T_CjMailHistory_Que` AFTER INSERT ON `T_CjMailHistory` FOR EACH ROW
 INSERT
   INTO `T_CjMailHistory_Que` (
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