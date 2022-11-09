DROP procedure IF EXISTS `procMigrateClearCondition`;

DELIMITER $$
CREATE PROCEDURE `procMigrateClearCondition` ()
BEGIN
    /* 移行処理：着荷済みで未立替のデータのクリアフラグをあげる */

    DECLARE
        updDttm    datetime;

    SET updDttm = now();
    
    UPDATE  T_PayingAndSales pas
    SET     ClearConditionForCharge = 1
           ,ClearConditionDate = ( SELECT MAX(Deli_ConfirmArrivalDate) FROM T_Order WHERE OrderSeq = pas.OrderSeq )
           ,UpdateDate = updDttm
           ,UpdateId = 9
    WHERE  EXISTS ( SELECT OrderSeq FROM T_Order WHERE Deli_ConfirmArrivalFlg = 1 AND Cnl_Status = 0 AND OrderSeq = pas.OrderSeq )
    AND    ClearConditionForCharge = 0
    ;
    
END
$$

DELIMITER ;

