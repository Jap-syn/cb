-- =============================================================================
-- ■与信注文ID管理キュートリガ
-- =============================================================================
DROP TRIGGER IF EXISTS `UPD_T_CjOrderIdControl_Que`;
DELIMITER ;;
CREATE TRIGGER `UPD_T_CjOrderIdControl_Que` AFTER UPDATE ON `T_CjOrderIdControl` FOR EACH ROW
 INSERT
   INTO `T_CjOrderIdControl_Que` (
        `AccessType`
      , `OrderSeq`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'U'
      , new.OrderSeq
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;