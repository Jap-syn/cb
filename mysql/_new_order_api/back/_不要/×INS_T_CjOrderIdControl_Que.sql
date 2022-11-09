-- =============================================================================
-- ■与信注文ID管理キュートリガ
-- =============================================================================
DROP TRIGGER IF EXISTS `INS_T_CjOrderIdControl_Que`;
DELIMITER ;;
CREATE TRIGGER `INS_T_CjOrderIdControl_Que` AFTER INSERT ON `T_CjOrderIdControl` FOR EACH ROW
 INSERT
   INTO `T_CjOrderIdControl_Que` (
        `AccessType`
      , `OrderSeq`
      , `RegistDate`
      , `UpdateDate`
      , `ValidFlg`
   ) VALUES (
        'C'
      , new.OrderSeq
      , CURRENT_TIMESTAMP
      , CURRENT_TIMESTAMP
      , 1
   )
;;
DELIMITER ;