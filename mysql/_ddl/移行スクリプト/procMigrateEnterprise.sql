DROP procedure IF EXISTS `procMigrateEnterprise`;

DELIMITER $$
CREATE PROCEDURE `procMigrateEnterprise`()
BEGIN

    /* 移行処理：加盟店 */
    /*  2015-08-18  カーソル宣言修正  */

    DECLARE v_done int;
    DECLARE updDttm datetime;   -- 登録日時

    -- FETCH用の変数宣言
    DECLARE c_EnterpriseId,
            c_Plan,
            c_NpMolecule3,
            c_NpDenominator3,
            c_NpMoleculeAll,
            c_NpDenominatorAll,
            c_NpGuaranteeMolecule3,
            c_NpNoGuaranteeMolecule3,
            c_NpGuaranteeMoleculeAll,
            c_NpNoGuaranteeMoleculeAll,
            c_NpNgMolecule3,
            c_NpNgDenominator3,
            c_NpNgMoleculeAll,
            c_NpNgDenominatorAll,
            c_NpAverageAmountTotal,
            c_NpAverageAmountOk,
            c_UseAmountLimitForCreditJudge,
            c_DkInitFee,
            c_DkMonthlyFee,
            c_ApiRegOrdMonthlyFee,
            c_ApiAllInitFee,
            c_ApiAllMonthlyFee,
            c_N_DkMonthlyFee,
            c_N_ApiRegOrdMonthlyFee,
            c_N_ApiAllMonthlyFee,
            c_OemId,
            c_N_OemEntMonthlyFee,
            c_N_OemDkMonthlyFee,
            c_N_OemApiRegOrdMonthlyFee,
            c_N_OemApiAllMonthlyFee,
            c_UserAmountOver,
            c_AutoJournalDeliMethodId
        BIGINT;
    
    DECLARE c_B_ChargeFixedDate,
            c_B_ChargeDecisionDate,
            c_B_ChargeExecDate,
            c_N_ChargeFixedDate,
            c_N_ChargeDecisionDate,
            c_N_ChargeExecDate,
            c_InvalidatedDate,
            c_ApplicationDate,
            c_PublishingConfirmDate,
            c_ServiceInDate,
            c_NpCalcDate
        date;
    
    DECLARE c_RegistDate,
            c_LastPasswordChanged
        DATETIME;
    
    DECLARE c_SettlementFeeRate,
            c_OemSettlementFeeRate
        DECIMAL(16, 5);
    
    DECLARE c_AverageUnitPriceRate  FLOAT;
    
    DECLARE c_PrefectureCode,
            c_Industry,
            c_MonthlyFee,
            c_SettlementAmountLimit,
            c_FixPattern,
            c_FfCode,
            c_FfBranchCode,
            c_FfAccountClass,
            c_ValidFlg,
            c_InvalidatedReason,
            c_PreSales,
            c_ClaimFee,
            c_TcClass,
            c_ReClaimFee,
            c_DocCollect,
            c_ExaminationResult,
            c_N_MonthlyFee,
            c_PaymentSchedule,
            c_LimitDatePattern,
            c_LimitDay,
            c_BkdFixPattern,
            c_BkdPaymentSchedule,
            c_NpRate3,
            c_NpRateAll,
            c_Special01Flg,
            c_NpGuaranteeRate3,
            c_NpNoGuaranteeRate3,
            c_NpGuaranteeRateAll,
            c_NpNoGuaranteeRateAll,
            c_NpNgRate3,
            c_NpNgRateAll,
            c_NpOrderCountTotal,
            c_NpOrderCountOk,
            c_SelfBillingClaimFee,
            c_SelfBillingExportAllow,
            c_CjMailMode,
            c_CombinedClaimMode,
            c_AutoClaimStopFlg,
            c_Hashed,
            c_OemMonthlyFee,
            c_OemClaimFee,
            c_N_OemMonthlyFee,
            c_PrintEntOrderIdOnClaimFlg,
            c_AutoJournalIncMode,
            c_HideToCbButton,
            c_OrderRevivalDisabled,
            c_SelfBillingOemClaimFee
        INT;
    
    DECLARE c_SelfBillingMode,
            c_AutoCreditJudgeMode,
            c_CreditJudgePendingRequest
        TINYINT;
    
    DECLARE c_PostalCode    VARCHAR(12);
    
    DECLARE c_LoginId   VARCHAR(20);
    
    DECLARE c_PrefectureName    VARCHAR(40);
    
    DECLARE c_Phone,
            c_Fax,
            c_ContactPhoneNumber,
            c_ContactFaxNumber
        VARCHAR(50);
    
    DECLARE c_FfAccountNumber   VARCHAR(80);
    
    DECLARE c_LoginPasswd   VARCHAR(100);
    
    DECLARE c_EnterpriseNameKj,
            c_EnterpriseNameKn,
            c_FfName,
            c_FfBranchName,
            c_CpNameKj,
            c_CpNameKn,
            c_RepNameKj,
            c_RepNameKn,
            c_Salesman
        VARCHAR(160);
    
    DECLARE c_City,
            c_Town,
            c_Building,
            c_FfAccountName,
            c_DivisionName,
            c_MailAddress,
            c_SelfBillingKey
        VARCHAR(255);
    
    DECLARE c_Note,
            c_Notice,
            c_UnitingAddress,
            c_Memo
        VARCHAR(4000);
    -- FETCH用の変数宣言

    DECLARE v_B_ChargeFixedDate,    -- 前回－立替締め日
            v_N_ChargeFixedDate    -- 次回－立替締め日
                            date;

    DECLARE v_B_ChargeExecDate,     -- 前回－立替実行日
            v_N_ChargeExecDate      -- 次回－立替実行日
                                date;
    
    DECLARE
        v_PayingCycleId,    -- 立替サイクルID
        v_N_PayingCycleId   -- 次回立替サイクルID
                 bigint(20);
    
    DECLARE
        v_NN_ChargeFixedDate,   -- 次々回－立替締め日
        v_NN_ChargeExecDate     -- 次々回－立替実行日
             date;
    
    -- カーソルの宣言
-- --------------------  2015-08-18  START
-- -    DECLARE cur1 CURSOR FOR SELECT * FROM `coraldb_ikou`.`T_Enterprise` ORDER BY `EnterpriseId`;
    DECLARE cur1 CURSOR FOR 
                 SELECT 
                   EnterpriseId,
                   RegistDate,
                   LoginId,
                   LoginPasswd,
                   EnterpriseNameKj,
                   EnterpriseNameKn,
                   PostalCode,
                   PrefectureCode,
                   PrefectureName,
                   City,
                   Town,
                   Building,
                   Phone,
                   Fax,
                   Industry,
                   Plan,
                   MonthlyFee,
                   SettlementAmountLimit,
                   SettlementFeeRate,
                   FixPattern,
                   FfName,
                   FfCode,
                   FfBranchName,
                   FfBranchCode,
                   FfAccountNumber,
                   FfAccountClass,
                   FfAccountName,
                   CpNameKj,
                   CpNameKn,
                   DivisionName,
                   MailAddress,
                   ContactPhoneNumber,
                   Note,
                   B_ChargeFixedDate,
                   B_ChargeDecisionDate,
                   B_ChargeExecDate,
                   N_ChargeFixedDate,
                   N_ChargeDecisionDate,
                   N_ChargeExecDate,
                   ValidFlg,
                   InvalidatedDate,
                   InvalidatedReason,
                   RepNameKj,
                   RepNameKn,
                   PreSales,
                   ClaimFee,
                   Salesman,
                   TcClass,
                   ContactFaxNumber,
                   ReClaimFee,
                   ApplicationDate,
                   PublishingConfirmDate,
                   ServiceInDate,
                   DocCollect,
                   ExaminationResult,
                   N_MonthlyFee,
                   Notice,
                   IFNULL(PaymentSchedule, 0),
                   LimitDatePattern,
                   LimitDay,
                   BkdFixPattern,
                   IFNULL(BkdPaymentSchedule, 0),
                   UnitingAddress,
                   Memo,
                   NpCalcDate,
                   NpMolecule3,
                   NpDenominator3,
                   NpRate3,
                   NpMoleculeAll,
                   NpDenominatorAll,
                   NpRateAll,
                   Special01Flg,
                   NpGuaranteeMolecule3,
                   NpNoGuaranteeMolecule3,
                   NpGuaranteeRate3,
                   NpNoGuaranteeRate3,
                   NpGuaranteeMoleculeAll,
                   NpNoGuaranteeMoleculeAll,
                   NpGuaranteeRateAll,
                   NpNoGuaranteeRateAll,
                   NpNgMolecule3,
                   NpNgDenominator3,
                   NpNgRate3,
                   NpNgMoleculeAll,
                   NpNgDenominatorAll,
                   NpNgRateAll,
                   NpOrderCountTotal,
                   NpOrderCountOk,
                   NpAverageAmountTotal,
                   NpAverageAmountOk,
                   SelfBillingMode,
                   SelfBillingKey,
                   AutoCreditJudgeMode,
                   SelfBillingClaimFee,
                   SelfBillingExportAllow,
                   CjMailMode,
                   CombinedClaimMode,
                   AutoClaimStopFlg,
                   UseAmountLimitForCreditJudge,
                   AverageUnitPriceRate,
                   DkInitFee,
                   DkMonthlyFee,
                   ApiRegOrdMonthlyFee,
                   ApiAllInitFee,
                   ApiAllMonthlyFee,
                   N_DkMonthlyFee,
                   N_ApiRegOrdMonthlyFee,
                   N_ApiAllMonthlyFee,
                   OemId,
                   N_OemEntMonthlyFee,
                   N_OemDkMonthlyFee,
                   N_OemApiRegOrdMonthlyFee,
                   N_OemApiAllMonthlyFee,
                   Hashed,
                   OemMonthlyFee,
                   OemSettlementFeeRate,
                   OemClaimFee,
                   N_OemMonthlyFee,
                   SelfBillingOemClaimFee,
                   PrintEntOrderIdOnClaimFlg,
                   UserAmountOver,
                   AutoJournalIncMode,
                   AutoJournalDeliMethodId,
                   CreditJudgePendingRequest,
                   HideToCbButton,
                   LastPasswordChanged,
                   OrderRevivalDisabled
                 FROM `coraldb_ikou`.`T_Enterprise` ORDER BY `EnterpriseId`;
-- --------------------  2015-08-18  END
    -- ハンドラーの宣言
    DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' set v_done = 1;

    SET v_done = 0;
    SET updDttm = now();

    open cur1;

    l_loop : LOOP
        FETCH cur1
            INTO c_EnterpriseId,
                c_RegistDate,
                c_LoginId,
                c_LoginPasswd,
                c_EnterpriseNameKj,
                c_EnterpriseNameKn,
                c_PostalCode,
                c_PrefectureCode,
                c_PrefectureName,
                c_City,
                c_Town,
                c_Building,
                c_Phone,
                c_Fax,
                c_Industry,
                c_Plan,
                c_MonthlyFee,
                c_SettlementAmountLimit,
                c_SettlementFeeRate,
                c_FixPattern,
                c_FfName,
                c_FfCode,
                c_FfBranchName,
                c_FfBranchCode,
                c_FfAccountNumber,
                c_FfAccountClass,
                c_FfAccountName,
                c_CpNameKj,
                c_CpNameKn,
                c_DivisionName,
                c_MailAddress,
                c_ContactPhoneNumber,
                c_Note,
                c_B_ChargeFixedDate,
                c_B_ChargeDecisionDate,
                c_B_ChargeExecDate,
                c_N_ChargeFixedDate,
                c_N_ChargeDecisionDate,
                c_N_ChargeExecDate,
                c_ValidFlg,
                c_InvalidatedDate,
                c_InvalidatedReason,
                c_RepNameKj,
                c_RepNameKn,
                c_PreSales,
                c_ClaimFee,
                c_Salesman,
                c_TcClass,
                c_ContactFaxNumber,
                c_ReClaimFee,
                c_ApplicationDate,
                c_PublishingConfirmDate,
                c_ServiceInDate,
                c_DocCollect,
                c_ExaminationResult,
                c_N_MonthlyFee,
                c_Notice,
                c_PaymentSchedule,
                c_LimitDatePattern,
                c_LimitDay,
                c_BkdFixPattern,
                c_BkdPaymentSchedule,
                c_UnitingAddress,
                c_Memo,
                c_NpCalcDate,
                c_NpMolecule3,
                c_NpDenominator3,
                c_NpRate3,
                c_NpMoleculeAll,
                c_NpDenominatorAll,
                c_NpRateAll,
                c_Special01Flg,
                c_NpGuaranteeMolecule3,
                c_NpNoGuaranteeMolecule3,
                c_NpGuaranteeRate3,
                c_NpNoGuaranteeRate3,
                c_NpGuaranteeMoleculeAll,
                c_NpNoGuaranteeMoleculeAll,
                c_NpGuaranteeRateAll,
                c_NpNoGuaranteeRateAll,
                c_NpNgMolecule3,
                c_NpNgDenominator3,
                c_NpNgRate3,
                c_NpNgMoleculeAll,
                c_NpNgDenominatorAll,
                c_NpNgRateAll,
                c_NpOrderCountTotal,
                c_NpOrderCountOk,
                c_NpAverageAmountTotal,
                c_NpAverageAmountOk,
                c_SelfBillingMode,
                c_SelfBillingKey,
                c_AutoCreditJudgeMode,
                c_SelfBillingClaimFee,
                c_SelfBillingExportAllow,
                c_CjMailMode,
                c_CombinedClaimMode,
                c_AutoClaimStopFlg,
                c_UseAmountLimitForCreditJudge,
                c_AverageUnitPriceRate,
                c_DkInitFee,
                c_DkMonthlyFee,
                c_ApiRegOrdMonthlyFee,
                c_ApiAllInitFee,
                c_ApiAllMonthlyFee,
                c_N_DkMonthlyFee,
                c_N_ApiRegOrdMonthlyFee,
                c_N_ApiAllMonthlyFee,
                c_OemId,
                c_N_OemEntMonthlyFee,
                c_N_OemDkMonthlyFee,
                c_N_OemApiRegOrdMonthlyFee,
                c_N_OemApiAllMonthlyFee,
                c_Hashed,
                c_OemMonthlyFee,
                c_OemSettlementFeeRate,
                c_OemClaimFee,
                c_N_OemMonthlyFee,
                c_SelfBillingOemClaimFee,
                c_PrintEntOrderIdOnClaimFlg,
                c_UserAmountOver,
                c_AutoJournalIncMode,
                c_AutoJournalDeliMethodId,
                c_CreditJudgePendingRequest,
                c_HideToCbButton,
                c_LastPasswordChanged,
                c_OrderRevivalDisabled;

        -- FETCHできなければループを抜ける
        IF v_done THEN LEAVE l_loop;
        END IF;

        -- 前回－立替実行日
        IF c_FixPattern = 5
            THEN set v_B_ChargeExecDate = DATE_ADD(c_B_ChargeFixedDate, INTERVAL 7 DAY);
        ELSEIF c_FixPattern <> 5 AND c_PaymentSchedule = 1
            -- 前回－立替締め日の翌月15日
            THEN set v_B_ChargeExecDate = DATE_ADD(LAST_DAY(c_B_ChargeFixedDate), INTERVAL 15 DAY);
        ELSEIF DATE_FORMAT(c_B_ChargeFixedDate, '%w') = 1
            -- 月曜日
            THEN set v_B_ChargeExecDate = DATE_ADD(c_B_ChargeFixedDate, INTERVAL 11 DAY);
        ELSEIF DATE_FORMAT(c_B_ChargeFixedDate, '%w') = 2
            -- 火曜日
            THEN set v_B_ChargeExecDate = DATE_ADD(c_B_ChargeFixedDate, INTERVAL 10 DAY);
        ELSEIF DATE_FORMAT(c_B_ChargeFixedDate, '%w') = 3
            -- 水曜日
            THEN set v_B_ChargeExecDate = DATE_ADD(c_B_ChargeFixedDate, INTERVAL 9 DAY);
        ELSEIF DATE_FORMAT(c_B_ChargeFixedDate, '%w') = 4
            -- 木曜日
            THEN set v_B_ChargeExecDate = DATE_ADD(c_B_ChargeFixedDate, INTERVAL 8 DAY);
        ELSEIF DATE_FORMAT(c_B_ChargeFixedDate, '%w') = 5
            -- 金曜日
            THEN set v_B_ChargeExecDate = DATE_ADD(c_B_ChargeFixedDate, INTERVAL 7 DAY);
        ELSEIF DATE_FORMAT(c_B_ChargeFixedDate, '%w') = 6
            -- 土曜日
            THEN set v_B_ChargeExecDate = DATE_ADD(c_B_ChargeFixedDate, INTERVAL 6 DAY);
        ELSEIF DATE_FORMAT(c_B_ChargeFixedDate, '%w') = 0
            -- 日曜日
            THEN set v_B_ChargeExecDate = DATE_ADD(c_B_ChargeFixedDate, INTERVAL 12 DAY);
        END IF;
        
        -- 次回－立替実行日
        IF c_FixPattern = 5
            THEN set v_N_ChargeExecDate = DATE_ADD(c_N_ChargeFixedDate, INTERVAL 7 DAY);
        ELSEIF c_FixPattern <> 5 AND c_PaymentSchedule = 1
            -- 次回－立替締め日の翌月15日
            THEN set v_N_ChargeExecDate = DATE_ADD(LAST_DAY(c_N_ChargeFixedDate), INTERVAL 15 DAY);
        ELSEIF DATE_FORMAT(c_N_ChargeFixedDate, '%w') = 1
            -- 月曜日
            THEN set v_N_ChargeExecDate = DATE_ADD(c_N_ChargeFixedDate, INTERVAL 11 DAY);
        ELSEIF DATE_FORMAT(c_N_ChargeFixedDate, '%w') = 2
            -- 火曜日
            THEN set v_N_ChargeExecDate = DATE_ADD(c_N_ChargeFixedDate, INTERVAL 10 DAY);
        ELSEIF DATE_FORMAT(c_N_ChargeFixedDate, '%w') = 3
            -- 水曜日
            THEN set v_N_ChargeExecDate = DATE_ADD(c_N_ChargeFixedDate, INTERVAL 9 DAY);
        ELSEIF DATE_FORMAT(c_N_ChargeFixedDate, '%w') = 4
            -- 木曜日
            THEN set v_N_ChargeExecDate = DATE_ADD(c_N_ChargeFixedDate, INTERVAL 8 DAY);
        ELSEIF DATE_FORMAT(c_N_ChargeFixedDate, '%w') = 5
            -- 金曜日
            THEN set v_N_ChargeExecDate = DATE_ADD(c_N_ChargeFixedDate, INTERVAL 7 DAY);
        ELSEIF DATE_FORMAT(c_N_ChargeFixedDate, '%w') = 6
            -- 土曜日
            THEN set v_N_ChargeExecDate = DATE_ADD(c_N_ChargeFixedDate, INTERVAL 6 DAY);
        ELSEIF DATE_FORMAT(c_N_ChargeFixedDate, '%w') = 0
            -- 日曜日
            THEN set v_N_ChargeExecDate = DATE_ADD(c_N_ChargeFixedDate, INTERVAL 12 DAY);
        END IF;
        
        -- 立替サイクルID
        IF c_FixPattern <> 101
            THEN set v_PayingCycleId = c_FixPattern;
        ELSE
            set v_PayingCycleId = concat(c_FixPattern, c_PaymentSchedule);
        END IF;
        
        -- 次回立替サイクルID
        IF c_FixPattern <> 101
            THEN set v_N_PayingCycleId = c_FixPattern;
        ELSE
            set v_N_PayingCycleId = concat(c_FixPattern, c_PaymentSchedule);
        END IF;
        
        -- 次々回－立替締め日
        set v_NN_ChargeFixedDate = DATE_ADD(c_N_ChargeFixedDate, INTERVAL DATEDIFF(c_N_ChargeFixedDate, c_B_ChargeFixedDate) DAY);
        
        -- 次々回－立替実行日
        IF c_FixPattern = 5
            THEN set v_NN_ChargeExecDate = DATE_ADD(v_NN_ChargeFixedDate, INTERVAL 7 DAY);
        ELSEIF c_FixPattern <> 5 AND c_PaymentSchedule = 1
            -- 次々回－立替締め日の翌月15日
            THEN set v_N_ChargeExecDate = DATE_ADD(LAST_DAY(c_N_ChargeFixedDate), INTERVAL 15 DAY);
        ELSEIF DATE_FORMAT(v_NN_ChargeFixedDate, '%w') = 1
            -- 月曜日
            THEN set v_NN_ChargeFixedDate = DATE_ADD(v_NN_ChargeFixedDate, INTERVAL 11 DAY);
        ELSEIF DATE_FORMAT(v_NN_ChargeFixedDate, '%w') = 2
            -- 火曜日
            THEN set v_NN_ChargeFixedDate = DATE_ADD(v_NN_ChargeFixedDate, INTERVAL 10 DAY);
        ELSEIF DATE_FORMAT(v_NN_ChargeFixedDate, '%w') = 3
            -- 水曜日
            THEN set v_NN_ChargeFixedDate = DATE_ADD(v_NN_ChargeFixedDate, INTERVAL 9 DAY);
        ELSEIF DATE_FORMAT(v_NN_ChargeFixedDate, '%w') = 4
            -- 木曜日
            THEN set v_NN_ChargeFixedDate = DATE_ADD(v_NN_ChargeFixedDate, INTERVAL 8 DAY);
        ELSEIF DATE_FORMAT(v_NN_ChargeFixedDate, '%w') = 5
            -- 金曜日
            THEN set v_NN_ChargeFixedDate = DATE_ADD(v_NN_ChargeFixedDate, INTERVAL 7 DAY);
        ELSEIF DATE_FORMAT(v_NN_ChargeFixedDate, '%w') = 6
            -- 土曜日
            THEN set v_NN_ChargeFixedDate = DATE_ADD(v_NN_ChargeFixedDate, INTERVAL 6 DAY);
        ELSEIF DATE_FORMAT(v_NN_ChargeFixedDate, '%w') = 0
            -- 日曜日
            THEN set v_NN_ChargeFixedDate = DATE_ADD(v_NN_ChargeFixedDate, INTERVAL 12 DAY);
        END IF;
        
        INSERT INTO `T_Enterprise`(
            `EnterpriseId`,                     -- 加盟店ID
            `RegistDate`,                       -- 登録日
            `LoginId`,                          -- ログインID
            `LoginPasswd`,                      -- パスワード
            `EnterpriseNameKj`,                 -- 加盟店名
            `EnterpriseNameKn`,                 -- 加盟店名かな
            `PostalCode`,                       -- 所在地－郵便番号
            `PrefectureCode`,                   -- 所在地－都道府県コード
            `PrefectureName`,                   -- 所在地－都道府県
            `City`,                             -- 所在地－市区郡
            `Town`,                             -- 所在地－町域
            `Building`,                         -- 所在地－建物
            `Phone`,                            -- 代表電話番号
            `Fax`,                              -- 代表FAX番号
            `Industry`,                         -- 業種
            `Plan`,                             -- 利用プラン
            `MonthlyFee`,                       -- 月額固定費
            `FfName`,                           -- 金融機関－金融機関名
            `FfCode`,                           -- 金融機関－金融機関番号
            `FfBranchName`,                     -- 金融機関－支店名
            `FfBranchCode`,                     -- 金融機関－支店番号
            `FfAccountNumber`,                  -- 金融機関－口座番号
            `FfAccountClass`,                   -- 金融機関－口座種別
            `FfAccountName`,                    -- 金融機関－口座名義
            `CpNameKj`,                         -- 担当者名
            `CpNameKn`,                         -- 担当者名かな
            `DivisionName`,                     -- 部署名
            `MailAddress`,                      -- メールアドレス
            `ContactPhoneNumber`,               -- 連絡先電話
            `Note`,                             -- 備考
            `B_ChargeFixedDate`,                -- 前回－立替締め日
            `B_ChargeDecisionDate`,             -- 前回－立替確定日
            `B_ChargeExecDate`,                 -- 前回－立替実行日
            `N_ChargeFixedDate`,                -- 次回－立替締め日
            `N_ChargeDecisionDate`,             -- 次回－立替確定日
            `N_ChargeExecDate`,                 -- 次回－立替実行日
            `ValidFlg`,                         -- 有効フラグ
            `InvalidatedDate`,                  -- 無効年月日
            `InvalidatedReason`,                -- 無効理由
            `RepNameKj`,                        -- 代表者氏名
            `RepNameKn`,                        -- 代表者氏名かな
            `PreSales`,                         -- 推定月商
            `Salesman`,                         -- 担当営業
            `TcClass`,                          -- 金融機関－振込手数料
            `ContactFaxNumber`,                 -- 連絡先FAX
            `ApplicationDate`,                  -- 申込日
            `PublishingConfirmDate`,            -- 掲載確認日
            `ServiceInDate`,                    -- サービス開始日（メール送信日）
            `DocCollect`,                       -- 書類回収
            `ExaminationResult`,                -- 審査結果
            `N_MonthlyFee`,                     -- 次回－課金月額固定費
            `Notice`,                           -- お知らせ
            `UnitingAddress`,                   -- 住所－結合住所
            `Memo`,                             -- 簡易備考
            `Special01Flg`,                     -- 特殊店舗フラグ
            `SelfBillingMode`,                  -- 請求書同梱ツール利用フラグ
            `SelfBillingKey`,                   -- 請求書同梱ツールアクセスキー
            `AutoCreditJudgeMode`,              -- 自動与信モード
            `SelfBillingExportAllow`,           -- 同梱ツールからのCSV出力可否
            `CjMailMode`,                       -- 与信結果メール送信モード
            `CombinedClaimMode`,                -- 取りまとめ請求モード
            `AutoClaimStopFlg`,                 -- 請求自動ストップフラグ
            `UseAmountLimitForCreditJudge`,     -- APIリアルタイム与信加盟店の与信OK最大限度額
            `DkInitFee`,                        -- 同梱サービス－初期費用
            `DkMonthlyFee`,                     -- 同梱サービス－月額固定費
            `ApiRegOrdMonthlyFee`,              -- API－注文登録利用月額
            `ApiAllInitFee`,                    -- API－全API利用初期費用
            `ApiAllMonthlyFee`,                 -- API－全API利用月額
            `N_DkMonthlyFee`,                   -- 次回請求－同梱月額（日割り対応）
            `N_ApiRegOrdMonthlyFee`,            -- 次回請求－注文登録API利用月額（日割り対応）
            `N_ApiAllMonthlyFee`,               -- 次回請求－全API利用月額（日割り対応）
            `OemId`,                            -- OEMID
            `N_OemEntMonthlyFee`,               -- OEM次回請求－店舗月額（日割り対応）
            `N_OemDkMonthlyFee`,                -- OEM次回請求－同梱月額（日割り対応）
            `N_OemApiRegOrdMonthlyFee`,         -- OEM次回請求－注文登録API利用月額（日割り対応）
            `N_OemApiAllMonthlyFee`,            -- OEM次回請求－全API利用月額（日割り対応）
            `Hashed`,                           -- LoginPasswdにハッシュ済みパスワードを格納しているかのフラグ
            `OemMonthlyFee`,                    -- OEM月額固定費
            `N_OemMonthlyFee`,                  -- 次回請求OEM月額固定費
            `PrintEntOrderIdOnClaimFlg`,        -- 請求書任意注文番号印刷フラグ
            `UserAmountOver`,                   -- 注文利用額
            `TaxClass`,                         -- 税区分
            `ClaimClass`,                       -- 加盟店請求区分
            `SystemClass`,                      -- 新旧システム版設定
            `JudgeSystemFlg`,                   -- 与信審査システム連携
            `AutoJudgeFlg`,                     -- 社内自動与信
            `JintecFlg`,                        -- ジンテック与信
            `ManualJudgeFlg`,                   -- 手動与信
            `CreditNgDispDays`,                 -- 与信NG表示期間日数
            `CombinedClaimFlg`,                 -- 請求取りまとめ（定期購入）
            `AutoCombinedClaimDay`,             -- 自動請求取りまとめ指定日
            `PayBackFlg`,                       -- 立替精算戻し
            `JournalRegistDispClass`,           -- 配送伝票入力初期表示
            `DispOrder1`,                       -- 表示順１
            `DispOrder2`,                       -- 表示順２
            `DispOrder3`,                       -- 表示順３
            `RegUnitingAddress`,                -- 所在地－結合住所（正規化）
            `RegEnterpriseNameKj`,              -- 事業者名（正規化）
            `RegCpNameKj`,                      -- 担当者氏名（正規化）
            `PrintAdjustmentX`,                 -- 印字位置調整X座標
            `PrintAdjustmentY`,                 -- 印字位置調整Y座標
            `PayingCycleId`,                    -- 立替サイクルID
            `N_PayingCycleId`,                  -- 次回立替サイクルID
            `DamageInterestRate`,               -- 遅延損害率
            `AutoNoGuaranteeFlg`,               -- 自動無保証対象フラグ
            `DispDecimalPoint`,                 -- 表示用小数点桁数
            `UseAmountFractionClass`,           -- 利用額端数計算設定
            `PrintEntComment`,                  -- 請求書コメント
            `CombinedClaimChargeFeeFlg`,        -- 請求とりまとめ店舗手数料フラグ
            `CsvRegistClass`,                   -- CSV一括登録区分
            `CsvRegistErrorClass`,              -- 一括登録エラー修正機能区分
            `ReceiptStatusSearchClass`,         -- 入金ステータス検索条件区分
            `NN_ChargeFixedDate`,               -- 次々回－立替締め日
            `NN_ChargeExecDate`,                -- 次々回－立替実行日
            `B_MonthlyFee`,                     -- 前回月額固定費
            `B_OemMonthlyFee`,                  -- 前回OEM月額固定費
            `CreditJudgePendingRequest`,        -- 与信保留要求
            `HideToCbButton`,                   -- 同梱ツール別送ボタン非表示フラグ
            `LastPasswordChanged`,              -- パスワード最終更新日時
            `OrderRevivalDisabled`,             -- 「与信NG復活」機能禁止フラグ
            `PayingMail`,                       -- 立替完了メール
            `DetailApiOrderStatusClass`,        -- 注文状況問合せAPI返却区分
            `RegistId`,                         -- 登録者
            `UpdateDate`,                       -- 更新日時
            `UpdateId`                          -- 更新者
        ) VALUES (
            c_EnterpriseId,
            c_RegistDate,
            c_LoginId,
            c_LoginPasswd,
            c_EnterpriseNameKj,
            c_EnterpriseNameKn,
            c_PostalCode,
            c_PrefectureCode,
            c_PrefectureName,
            c_City,
            c_Town,
            c_Building,
            c_Phone,
            c_Fax,
            c_Industry,
            c_Plan,
            TRUNCATE(c_MonthlyFee / 1.08, 0), -- 月額固定費(会計対応)
            c_FfName,
            c_FfCode,
            c_FfBranchName,
            c_FfBranchCode,
            c_FfAccountNumber,
            c_FfAccountClass,
            c_FfAccountName,
            c_CpNameKj,
            c_CpNameKn,
            c_DivisionName,
            c_MailAddress,
            c_ContactPhoneNumber,
            c_Note,
            v_B_ChargeFixedDate,
            c_B_ChargeDecisionDate,
            v_B_ChargeExecDate, -- B_ChargeExecDate
            c_N_ChargeFixedDate,
            c_N_ChargeDecisionDate,
            v_N_ChargeExecDate, -- N_ChargeExecDate
            IFNULL(c_ValidFlg,1),
            c_InvalidatedDate,
            c_InvalidatedReason,
            c_RepNameKj,
            c_RepNameKn,
            c_PreSales,
            c_Salesman,
            c_TcClass,
            c_ContactFaxNumber,
            c_ApplicationDate,
            c_PublishingConfirmDate,
            c_ServiceInDate,
            c_DocCollect,
            c_ExaminationResult,
            TRUNCATE(c_N_MonthlyFee / 1.08, 0),  -- 次回月額固定費(会計対応)
            c_Notice,
            c_UnitingAddress,
            c_Memo,
            c_Special01Flg,
            c_SelfBillingMode,
            c_SelfBillingKey,
            c_AutoCreditJudgeMode,
            c_SelfBillingExportAllow,
            c_CjMailMode,
            c_CombinedClaimMode,
            c_AutoClaimStopFlg,
            c_UseAmountLimitForCreditJudge,
            c_DkInitFee,
            c_DkMonthlyFee,
            c_ApiRegOrdMonthlyFee,
            c_ApiAllInitFee,
            c_ApiAllMonthlyFee,
            c_N_DkMonthlyFee,
            c_N_ApiRegOrdMonthlyFee,
            c_N_ApiAllMonthlyFee,
            c_OemId,
            c_N_OemEntMonthlyFee,
            c_N_OemDkMonthlyFee,
            c_N_OemApiRegOrdMonthlyFee,
            c_N_OemApiAllMonthlyFee,
            c_Hashed,
            TRUNCATE(c_OemMonthlyFee / 1.08, 0), -- OEM月額固定費(会計対応)
            TRUNCATE(c_N_OemMonthlyFee / 1.08, 0), -- 次回OEM月額固定費(会計対応)
            1,  -- PrintEntOrderIdOnClaimFlg(任意注文番号印刷フラグ 1(印刷する)固定とする)
            c_UserAmountOver,
            0,
            1,
            0,
            1,
            1,
            1,
            1,
            7,
            0,
            31,
            0,
            1,
            0,
            1,
            2,
            NULL,
            NULL,
            NULL,
            0,
            0,
            v_PayingCycleId, -- PayingCycleId
            v_N_PayingCycleId, -- N_PayingCycleId
            0,
            0,
            0,
            0,
            NULL,
            0,
            0,
            0,
            0,
            v_NN_ChargeFixedDate, -- NN_ChargeFixedDate,
            v_NN_ChargeExecDate, -- NN_ChargeExecDate,
            TRUNCATE(c_MonthlyFee / 1.08, 0), -- 前回月額固定費
            TRUNCATE(c_OemMonthlyFee / 1.08, 0), -- 前回OEM月額固定費
            0,
            0,
            NULL,
            0,
            1,                            -- 立替完了メール
            0,                            -- 注文状況問合せAPI返却区分
            9,
            updDttm,
            9);
    END LOOP;

    close cur1;
END
$$

DELIMITER ;
