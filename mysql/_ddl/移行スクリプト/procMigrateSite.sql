DROP procedure IF EXISTS `procMigrateSite`;

DELIMITER $$
CREATE PROCEDURE `procMigrateSite` ()
BEGIN
    /* 移行処理：サイト */
    
    DECLARE
        updDttm     datetime;

    SET
        updDttm = now();
        
        
        INSERT INTO `T_Site`(
            `SiteId`,                   -- サイトID
            `RegistDate`,               -- 登録日
            `EnterpriseId`,             -- 加盟店ID
            `SiteNameKj`,               -- サイト名
            `SiteNameKn`,               -- サイト名かな
            `NickName`,                 -- 略称
            `Url`,                      -- URL
            `ReqMailAddrFlg`,           -- メールアドレス必須フラグ
            `ValidFlg`,                 -- 有効フラグ
            `SiteForm`,                 -- サイト形態
            `CombinedClaimFlg`,         -- 取りまとめ請求フラグ
            `OutOfAmendsFlg`,           -- 全案件補償外対象フラグ
            `FirstClaimLayoutMode`,     -- 初回請求用紙モード
            `ServiceTargetClass`,       -- 役務対象区分
            `AutoCreditLimitAmount`,    -- 自動与信限度額
            `ClaimJournalClass`,        -- 請求書発行ルール
            `SettlementAmountLimit`,    -- 決済上限額
            `SettlementFeeRate`,        -- 決済手数料率
            `ClaimFeeBS`,               -- 請求手数料（別送）
            `ClaimFeeDK`,               -- 請求手数料（同梱）
            `ReClaimFee`,               -- 再請求手数料
            `OemSettlementFeeRate`,     -- OEM決済手数料率
            `OemClaimFee`,              -- OEM請求手数料
            `SystemFee`,                -- システム手数料
            `CreditCriterion`,          -- 与信判定基準
            `CreditOrderUseAmount`,     -- 与信時注文利用額
            `AutoCreditDateFrom`,       -- 与信自動化有効期間FROM
            `AutoCreditDateTo`,         -- 与信自動化有効期間TO
            `AutoCreditCriterion`,      -- 自動化用与信判定基準
            `AutoClaimStopFlg`,         -- 請求自動ストップ
            `SelfBillingFlg`,           -- 請求書同梱
            `CombinedClaimDate`,        -- 自動請求取りまとめ指定日
            `LimitDatePattern`,         -- 初回請求支払期限算出方法
            `LimitDay`,                 -- 支払期限算出基準日
            `PayingBackFlg`,            -- 立替精算戻し可否
            `PayingBackDays`,           -- 立替精算戻し判定日数
            `SiteConfDate`,             -- 掲載確認日
            `CreaditStartMail`,         -- 与信開始メール
            `CreaditCompMail`,          -- 与信完了メール
            `ClaimMail`,                -- 請求書発行メール
            `ReceiptMail`,              -- 入金確認メール
            `CancelMail`,               -- キャンセル確認メール
            `AddressMail`,              -- アドレス確認メール
            `SoonPaymentMail`,          -- もうすぐお支払メール
            `NotPaymentConfMail`,       -- お支払未確認メール
            `CreditResultMail`,         -- 与信結果メール
            `AutoJournalDeliMethodId`,  -- 自動伝票番号登録時配送先ID
            `AutoJournalIncMode`,       -- 自動伝票番号登録フラグ
            `SitClass`,                 -- サイト区分
            `T_OrderClass`,             -- テスト注文可否区分
            `PrintFormDK`,              -- 請求書用用紙設定（同梱）
            `PrintFormBS`,              -- 請求書用用紙設定（別送）
            `KisanbiDelayDays`,         -- 延滞起算猶予
            `BarcodeLimitDays`,         -- バーコード使用期限
            `CombinedClaimChargeFeeFlg`,-- 請求とりまとめ店舗手数料フラグ
            `YuchoMT`,                  -- 郵貯MT
            `CreditJudgeMethod`,        -- 与信判定方法
            `AverageUnitPriceRate`,     -- バーコード使用期限
            `SelfBillingOemClaimFee`,   -- OEM同梱請求手数料
            `ClaimDisposeMail`,         -- 請求書破棄メール
            `RegistId`,                 -- 登録者
            `UpdateDate`,               -- 更新日時
            `UpdateId`                  -- 更新者
        ) SELECT 
            `T_Site`.`SiteId`,                                                  -- サイトID
            `T_Site`.`RegistDate`,                                              -- 登録日
            `T_Site`.`EnterpriseId`,                                            -- 加盟店ID
            `T_Site`.`SiteNameKj`,                                              -- サイト名
            convert_kana(`T_Site`.`SiteNameKn`),                                -- サイト名かな
            `T_Site`.`NickName`,                                                -- 略称
            `T_Site`.`Url`,                                                     -- URL
            `T_Site`.`ReqMailAddrFlg`,                                          -- メールアドレス必須フラグ
            IFNULL(`T_Site`.`ValidFlg`,1),                                      -- 有効フラグ
            `T_Site`.`SiteForm`,                                                -- サイト形態
            `T_Site`.`CombinedClaimFlg`,                                        -- 取りまとめ請求フラグ
            `T_Site`.`OutOfAmendsFlg`,                                          -- 全案件補償外対象フラグ
            IFNULL(`T_Site`.`FirstClaimLayoutMode`,0),                          -- 初回請求用紙モード
            0,                                                                  -- 役務対象区分
            NULL,                                                               -- 自動与信限度額
            0,                                                                  -- 請求書発行ルール
            `T_Enterprise`.`SettlementAmountLimit`,                             -- 決済上限額
            CASE WHEN `T_Enterprise`.`SettlementFeeRate` IS NULL THEN NULL
                ELSE TRUNCATE(`T_Enterprise`.`SettlementFeeRate` / 100000 , 5)
            END,                                                                -- 決済手数料率
            CASE WHEN `T_Enterprise`.`ClaimFee` IS NULL THEN NULL
                ELSE TRUNCATE( (`T_Enterprise`.`ClaimFee` / 1.08), 0)
            END,                                                                -- 請求手数料（別送）
            CASE WHEN `T_Enterprise`.`SelfBillingClaimFee`  IS NULL THEN NULL
                ELSE TRUNCATE( (`T_Enterprise`.`SelfBillingClaimFee` / 1.08), 0)
            END,                                                                -- 請求手数料（同梱）
            CASE WHEN `T_Enterprise`.`ReClaimFee` IS NULL THEN NULL
                ELSE TRUNCATE( (`T_Enterprise`.`ReClaimFee` / 1.08), 0)
            END,                                                                -- 再請求手数料
            CASE WHEN `T_Enterprise`.`OemSettlementFeeRate` IS NULL THEN NULL
                ELSE TRUNCATE( (`T_Enterprise`.`OemSettlementFeeRate` / (1.08 * 100000)) , 5)
            END,                                                                -- OEM決済手数料率
            CASE WHEN `T_Enterprise`.`OemClaimFee` IS NULL THEN NULL
                ELSE TRUNCATE( (`T_Enterprise`.`OemClaimFee` / 1.08), 0)
            END,                                                                -- OEM請求手数料
            NULL,                                                               -- システム手数料
            0,                                                                  -- 与信判定基準
            NULL,                                                               -- 与信時注文利用額
            NULL,                                                               -- 与信自動化有効期間FROM
            NULL,                                                               -- 与信自動化有効期間TO
            0,                                                                  -- 自動化用与信判定基準
            IFNULL(`T_Enterprise`.`AutoClaimStopFlg`, 0),                       -- 請求自動ストップ
            CASE 
                WHEN `T_Enterprise`.`SelfBillingMode` > 0
                    THEN 1
                ELSE 0
            END,                                                                -- 請求書同梱
            31,                                                                 -- 自動請求取りまとめ指定日
            NULL,                                                               -- 初回請求支払期限算出方法
            NULL,                                                               -- 支払期限算出基準日
            1,                                                                  -- 立替精算戻し可否
            120,                                                                -- 立替精算戻し判定日数
            `T_Enterprise`.`PublishingConfirmDate`,                             -- 掲載確認日
            1,                                                                  -- 与信開始メール
            1,                                                                  -- 与信完了メール
            1,                                                                  -- 請求書発行メール
            1,                                                                  -- 入金確認メール
            1,                                                                  -- キャンセル確認メール
            1,                                                                  -- アドレス確認メール
            1,                                                                  -- もうすぐお支払メール
            1,                                                                  -- お支払未確認メール
            IFNULL(`T_Enterprise`.`CjMailMode`,0),                              -- 与信結果メール
            IFNULL(`T_Enterprise`.`AutoJournalDeliMethodId`,0),                 -- 自動伝票番号登録時配送先ID
            IFNULL(`T_Enterprise`.`AutoJournalIncMode`,0),                      -- 自動伝票番号登録フラグ
            0,                                                                  -- サイト区分
            0,                                                                  -- テスト注文可否区分
            1,                                                                  -- 請求書用用紙設定（同梱）
            CASE WHEN `T_Enterprise`.`OemId` IS NULL OR `T_Enterprise`.`OemId` = 0
                    THEN 1
                WHEN `T_Enterprise`.`OemId`= 1
                    THEN 2
                WHEN `T_Enterprise`.`OemId` = 2
                    THEN 3
                WHEN `T_Enterprise`.`OemId` = 3
                    THEN 2
            END,                                                                -- 請求書用用紙設定（別送）
            IFNULL( `T_Oem`.`KisanbiDelayDays`, 0) ,                            -- 延滞起算猶予
            
            0,                                                                  -- バーコード使用期限
            0,                                                                  -- 請求とりまとめ店舗手数料フラグ
            0,                                                                  -- 郵貯MT
            0,                                                                  -- 与信判定方法
            `T_Enterprise`.`AverageUnitPriceRate`,                              -- 与信平均単価倍率
            TRUNCATE( (`T_Enterprise`.`SelfBillingOemClaimFee` / 1.08) + 0.9, 0), -- OEM同梱請求手数料
            1,                                                                  -- 請求書破棄メール
            9,                                                                  -- 登録者
            updDttm,                                                            -- 更新日時
            9                                                                   -- 更新者

        FROM `coraldb_ikou`.`T_Site`
        LEFT OUTER JOIN `coraldb_ikou`.`T_Enterprise`
        ON `T_Site`.`EnterpriseId` = `T_Enterprise`.`EnterpriseId`
        LEFT OUTER JOIN `coraldb_ikou`.`T_Oem`
        ON `T_Enterprise`.`OemId` = `T_Oem`.`OemId`;
    
END
$$

DELIMITER ;

