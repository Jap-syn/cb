DROP procedure IF EXISTS `procMigratePricePlan`;

DELIMITER $$
CREATE PROCEDURE `procMigratePricePlan` ()
BEGIN

    /* 移行処理：料金プランマスター */

    DECLARE
        updDttm    datetime;        -- 更新日時

    SET updDttm = now();

    INSERT INTO `M_PricePlan`(`PricePlanId`,`PricePlanName`,`MonthlyFee`,`SettlementAmountLimit`,`SettlementFeeRate`,`ClaimFeeBS`,`ClaimFeeDK`,`ReClaimFee`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
                       VALUES( 11          , 'リスクフリー',     0      , 100000000             , 4.8               , 160        , 85         , 278        , updDttm    , 9        , updDttm    , 9        , 1);
    INSERT INTO `M_PricePlan`(`PricePlanId`,`PricePlanName`,`MonthlyFee`,`SettlementAmountLimit`,`SettlementFeeRate`,`ClaimFeeBS`,`ClaimFeeDK`,`ReClaimFee`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
                       VALUES( 21          , 'スタンダード',  4500      , 100000000             , 4.2               , 160        , 85         , 278        , updDttm    , 9        , updDttm    , 9        , 1);
    INSERT INTO `M_PricePlan`(`PricePlanId`,`PricePlanName`,`MonthlyFee`,`SettlementAmountLimit`,`SettlementFeeRate`,`ClaimFeeBS`,`ClaimFeeDK`,`ReClaimFee`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
                       VALUES( 31          , 'エキスパート', 16667      , 100000000             , 3.5               , 160        , 85         , 278        , updDttm    , 9        , updDttm    , 9        , 1);
    INSERT INTO `M_PricePlan`(`PricePlanId`,`PricePlanName`,`MonthlyFee`,`SettlementAmountLimit`,`SettlementFeeRate`,`ClaimFeeBS`,`ClaimFeeDK`,`ReClaimFee`,`RegistDate`,`RegistId`,`UpdateDate`,`UpdateId`,`ValidFlg`)
                       VALUES( 41          , 'スペシャル'  , 41667      , 100000000             , 2.8               , 160        , 85         , 278        , updDttm    , 9        , updDttm    , 9        , 1);
END
$$

DELIMITER ;

