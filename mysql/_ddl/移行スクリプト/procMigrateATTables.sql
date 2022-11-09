DROP procedure IF EXISTS `procMigrateATTables`;

DELIMITER $$
CREATE PROCEDURE `procMigrateATTables` ()
BEGIN

    /* 移行処理：会計用追加テーブル移送処理 */

    DECLARE updDttm     datetime;

    SET updDttm = now();

    -- ----------------------------------------
    -- 会計用加盟店マスターの登録(設計確認済)
    -- ----------------------------------------
    INSERT INTO AT_Enterprise (
        EnterpriseId,
        IncludeMonthlyFee,
        ApiMonthlyFee,
        CreditNoticeMonthlyFee,
        NCreditNoticeMonthlyFee,
        ReserveMonthlyFee,
        N_IncludeMonthlyFee,
        N_ApiMonthlyFee,
        N_CreditNoticeMonthlyFee,
        N_NCreditNoticeMonthlyFee,
        N_ReserveMonthlyFee,
        OemIncludeMonthlyFee,
        OemApiMonthlyFee,
        OemCreditNoticeMonthlyFee,
        OemNCreditNoticeMonthlyFee,
        OemReserveMonthlyFee,
        N_OemIncludeMonthlyFee,
        N_OemApiMonthlyFee,
        N_OemCreditNoticeMonthlyFee,
        N_OemNCreditNoticeMonthlyFee,
        N_OemReserveMonthlyFee
    )
    SELECT 
        EnterpriseId,   -- EnterpriseId,
        0,              -- IncludeMonthlyFee,
        0,              -- ApiMonthlyFee,
        0,              -- CreditNoticeMonthlyFee,
        0,              -- NCreditNoticeMonthlyFee,
        0,              -- ReserveMonthlyFee,
        0,              -- N_IncludeMonthlyFee,
        0,              -- N_ApiMonthlyFee,
        0,              -- N_CreditNoticeMonthlyFee,
        0,              -- N_NCreditNoticeMonthlyFee,
        0,              -- N_ReserveMonthlyFee,
        0,              -- OemIncludeMonthlyFee,
        0,              -- OemApiMonthlyFee,
        0,              -- OemCreditNoticeMonthlyFee,
        0,              -- OemNCreditNoticeMonthlyFee,
        0,              -- OemReserveMonthlyFee,
        0,              -- N_OemIncludeMonthlyFee,
        0,              -- N_OemApiMonthlyFee,
        0,              -- N_OemCreditNoticeMonthlyFee,
        0,              -- N_OemNCreditNoticeMonthlyFee,
        0               -- N_OemReserveMonthlyFee
    FROM T_Enterprise;
    
    -- ------------------------------
    -- 会計用立替振込管理の作成(設計確認済)
    -- ------------------------------
    INSERT INTO AT_PayingControl (
        Seq,
        MonthlyFeeWithoutTax,
        MonthlyFeeTax,
        IncludeMonthlyFee,
        IncludeMonthlyFeeTax,
        ApiMonthlyFee,
        ApiMonthlyFeeTax,
        CreditNoticeMonthlyFee,
        CreditNoticeMonthlyFeeTax,
        NCreditNoticeMonthlyFee,
        NCreditNoticeMonthlyFeeTax,
        ReserveMonthlyFee,
        ReserveMonthlyFeeTax,
        OemMonthlyFeeWithoutTax,
        OemMonthlyFeeTax,
        OemIncludeMonthlyFee,
        OemIncludeMonthlyFeeTax,
        OemApiMonthlyFee,
        OemApiMonthlyFeeTax,
        OemCreditNoticeMonthlyFee,
        OemCreditNoticeMonthlyFeeTax,
        OemNCreditNoticeMonthlyFee,
        OemNCreditNoticeMonthlyFeeTax,
        OemReserveMonthlyFee,
        OemReserveMonthlyFeeTax,
        DailySummaryFlg
    ) 
    SELECT 
        Seq,        -- Seq,
        MonthlyFee - TRUNCATE(MonthlyFee / 1.08, 0), -- MonthlyFeeWithoutTax,
        TRUNCATE(MonthlyFee / 1.08, 0), -- MonthlyFeeTax,
        0,          -- IncludeMonthlyFee,
        0,          -- IncludeMonthlyFeeTax,
        0,          -- ApiMonthlyFee,
        0,          -- ApiMonthlyFeeTax,
        0,          -- CreditNoticeMonthlyFee,
        0,          -- CreditNoticeMonthlyFeeTax,
        0,          -- NCreditNoticeMonthlyFee,
        0,          -- NCreditNoticeMonthlyFeeTax,
        0,          -- ReserveMonthlyFee,
        0,          -- ReserveMonthlyFeeTax,
        CASE WHEN MonthlyFee > 0 AND IFNULL(OemMonthlyFee, 0) > 0 THEN OemMonthlyFee - TRUNCATE(OemMonthlyFee / 1.08, 0) -- OemMonthlyFeeWithoutTax,
             ELSE 0
        END,
        CASE WHEN MonthlyFee > 0 AND IFNULL(OemMonthlyFee, 0) > 0 THEN TRUNCATE( OemMonthlyFee / 1.08, 0) -- OemMonthlyFeeTax,
             ELSE 0
        END,
        0,          -- OemIncludeMonthlyFee,
        0,          -- OemIncludeMonthlyFeeTax,
        0,          -- OemApiMonthlyFee,
        0,          -- OemApiMonthlyFeeTax,
        0,          -- OemCreditNoticeMonthlyFee,
        0,          -- OemCreditNoticeMonthlyFeeTax,
        0,          -- OemNCreditNoticeMonthlyFee,
        0,          -- OemNCreditNoticeMonthlyFeeTax,
        0,          -- OemReserveMonthlyFee,
        0,          -- OemReserveMonthlyFeeTax,
        1           -- DailySummaryFlg
    FROM T_PayingControl pc 
         LEFT OUTER JOIN ( SELECT OemEnterpriseClaimedSeq
                                 ,OemClaimedSeq
                                 ,EnterpriseId
                                 ,PC_MonthlyFee - OM_EntMonthlyFee AS OemMonthlyFee
                             FROM T_OemEnterpriseClaimed
                         ) oec
                     ON pc.OemClaimedSeq = oec.OemClaimedSeq
                    AND pc.EnterpriseId = oec.EnterpriseId
    ;
    

    -- ------------------------------------
    -- 会計用調整額管理の作成(設計確認済)
    -- ------------------------------------
    INSERT INTO AT_AdjustmentAmount (
        PayingControlSeq,
        SerialNumber,
        DailySummaryFlg
    )
    SELECT 
        PayingControlSeq,   -- PayingControlSeq,
        SerialNumber,       -- SerialNumber,
        1                   -- DailySummaryFlg
    FROM T_AdjustmentAmount;
    
    
    -- -------------------------------------
    -- 会計用のOEM請求データ作成(設計確認済)
    -- -------------------------------------
    INSERT INTO AT_OemClaimed (
        OemClaimedSeq,
        DailySummaryFlg
    ) 
    SELECT 
        OemClaimedSeq,  -- OemClaimedSeq,
        1               -- DailySummaryFlg
    FROM T_OemClaimed;
    
    -- ----------------------------------------
    -- 会計注文データの作成(設計確認済)
    -- ----------------------------------------
    INSERT INTO AT_Order
    (
        OrderSeq,
        Dmg_DailySummaryFlg
    )
    SELECT
        OrderSeq,   -- OrderSeq,
        CASE WHEN `T_Order`.`DataStatus` = 51 AND `T_Order`.`Clm_F_LimitDate` < '2013-12-01' THEN 1
             ELSE 0
        END         -- Dmg_DailySummaryFlg
    FROM coraldb_ikou.T_Order;
    
    -- ----------------------------------------
    -- 会計用立替・売上管理データの作成(設計確認済)
    -- ----------------------------------------
    INSERT INTO AT_PayingAndSales
    (
        Seq,
        DailySummaryFlg
    )
    SELECT 
        Seq,        -- Seq,
        CASE WHEN (IFNULL(ClearConditionForCharge, 0) = 1 AND ClearConditionDate < '2015-12-01') THEN 1
             ELSE 0
        END  -- DailySummaryFlg
    FROM T_PayingAndSales;
    
    -- ----------------------------------------
    -- 会計用返金管理データの作成(返金データは作られないため空振り)
    -- ----------------------------------------
    INSERT INTO AT_RepaymentControl
    (
        RepaySeq,
        DailySummaryFlg
    )
    SELECT 
        RepaySeq,       -- RepaySeq,
        1               -- DailySummaryFlg
    FROM T_RepaymentControl;
    
    -- ----------------------------------------
    -- 会計用入金管理データの作成(設計確認済)
    -- ----------------------------------------
    INSERT INTO AT_ReceiptControl
    (
        ReceiptSeq,
        AccountNumber,
        ClassDetails,
        BankFlg
    )
    SELECT 
        ReceiptSeq,       -- ReceiptSeq,
        NULL, -- AccountNumber,
        NULL, -- ClassDetails,
        8 -- BankFlg
    FROM T_ReceiptControl;
    
END
$$

DELIMITER ;

