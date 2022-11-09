DROP PROCEDURE IF EXISTS P_OrderHistory;

DELIMITER $$

CREATE PROCEDURE P_OrderHistory(    IN  pi_order_seq            BIGINT
                                ,   IN  pi_history_reason_cd    INT
                                ,   IN  pi_user_id              BIGINT
                                ,   OUT po_ret_sts              INT
                                ,   OUT po_ret_errcd            VARCHAR(100)
                                ,   OUT po_ret_sqlcd            INT
                                ,   OUT po_ret_msg              VARCHAR(255)
                               )
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名       ：  P_OrderHistory
 *
 * 概要             ：  注文履歴登録処理
 *
 * 引数             ：  [I/ ]pi_order_seq                       注文Seq
 *                  ：  [I/ ]pi_history_reason_cd               履歴理由ｺｰﾄﾞ
 *                  ：  [I/ ]pi_user_id                         ﾕｰｻﾞｰID
 *                  ：  [ /O]po_ret_sts                         ﾘﾀｰﾝｽﾃｰﾀｽ
 *                  ：  [ /O]po_ret_errcd                       ﾘﾀｰﾝｺｰﾄﾞ
 *                  ：  [ /O]po_ret_sqlcd                       ﾘﾀｰﾝSQLｺｰﾄﾞ
 *                  ：  [ /O]po_ret_msg                         ﾘﾀｰﾝﾒｯｾｰｼﾞ
 *
 * 履歴             ：  2015/05/27  NDC 新規作成
 *
 *****************************************************************************/
BEGIN
    -- ---------------------------
    -- 変数宣言
    -- ---------------------------
    -- 1) 履歴登録用注文情報
    DECLARE l_OrderId                       VARCHAR(50);        -- 注文ID
    DECLARE l_OutOfAmends                   INT;                -- 補償対象外ﾌﾗｸﾞ
    DECLARE l_Oem_OrderId                   VARCHAR(255);       -- OEM任意番号
    DECLARE l_Ent_OrderId                   VARCHAR(255);       -- 任意注文番号
    DECLARE l_RegistDate                    DATETIME;           -- 注文登録日時
    DECLARE l_ReceiptOrderDate              DATE;               -- ご注文日
    DECLARE l_ServiceExpectedDate           DATE;               -- 役務提供予定日
    DECLARE l_DataStatus                    VARCHAR(20);        -- ｽﾃｰﾀｽ
    DECLARE l_UseAmount                     BIGINT;             -- 利用額
    DECLARE l_CombinedClaimTargetStatus     INT;                -- 取りまとめ対象注文ｽﾃｰﾀｽ
    DECLARE l_AnotherDeliFlg                INT;                -- 別配送先ﾌﾗｸﾞ
    DECLARE l_Incre_Note                    VARCHAR(4000);      -- 備考
    DECLARE l_RemindClass                   INT;                -- 不払い管理－督促分類
    DECLARE l_FinalityRemindDate            DATE;               -- 不払い管理－最終督促日
    DECLARE l_FinalityRemindOpId            INT;                -- 不払い管理－最終督促者
    DECLARE l_PromPayDate                   DATE;               -- 不払い管理－支払約束日
    DECLARE l_LonghandLetter                INT;                -- 不払い管理－手書き手紙
    DECLARE l_BriefNote                     VARCHAR(255);       -- 不払い管理－簡易備考
    DECLARE l_TouchHistoryFlg               INT;                -- 不払い管理－ﾀｯﾁ履歴
    DECLARE l_VisitFlg                      INT;                -- 不払い管理－訪問済
    DECLARE l_FinalityCollectionMean        INT;                -- 不払い管理－最終改修手段
    DECLARE l_ClaimStopReleaseDate          DATE;               -- 不払い管理－請求ｽﾄｯﾌﾟ解除日
    DECLARE l_LetterClaimStopFlg            INT;                -- 不払い管理－請求ｽﾄｯﾌﾟ紙
    DECLARE l_MailClaimStopFlg              INT;                -- 不払い管理－請求ｽﾄｯﾌﾟﾒｰﾙ
    DECLARE l_Tel30DaysFlg                  TINYINT;            -- 不払い管理－荷電30日ﾌﾗｸﾞ
    DECLARE l_Tel90DaysFlg                  TINYINT;            -- 不払い管理－荷電90日ﾌﾗｸﾞ
    DECLARE l_OemClaimTransDate             DATE;               -- 不払い管理－OEM債権移管日
    DECLARE l_Dmg_DecisionDate              DATE;               -- 不払い管理－貸倒確定日
    DECLARE l_Oem_Note                      TEXT;               -- OEM先備考
    DECLARE l_Incre_ScoreTotal              INT;                -- 与信－与信点数－ｽｺｱ合計
    DECLARE l_Incre_Status                  INT;                -- 与信－社内与信
    DECLARE l_Dmi_Status                    INT;                -- 与信－DMIｽﾃｰﾀｽ
    DECLARE l_Dmi_DecisionDate              DATE;               -- 与信－与信日
    DECLARE l_Dmi_ResponseNote              VARCHAR(255);       -- 与信－与信理由
    DECLARE l_Deli_ConfirmArrivalDate       DATETIME;           -- 着荷確認－確認日
    DECLARE l_Deli_ConfirmArrivalFlg        INT;                -- 着荷確認－確認結果
    DECLARE l_Chg_FixedDate                 DATE;               -- 着荷確認－立替締め日
    DECLARE l_MailPaymentSoonDate           DATE;               -- ﾒｰﾙ送信-直前ﾒｰﾙ
    DECLARE l_MailLimitPassageCount         INT;                -- ﾒｰﾙ送信-未確認ﾒｰﾙ-件数
    DECLARE l_MailLimitPassageDate          DATE;               -- ﾒｰﾙ送信-未確認ﾒｰﾙ-日付
    DECLARE l_Ent_Note                      VARCHAR(4000);      -- 事業者備考
    DECLARE l_P_OrderSeq                    BIGINT;             -- 親注文SEQ
    DECLARE l_CombinedClaimParentFlg        INT;                -- 取りまとめ後に親となったﾌﾗｸﾞ
    DECLARE l_Chg_NonChargeFlg              TINYINT;            -- 立替処理－立替対象外ﾌﾗｸﾞ
    -- 2) 履歴登録用購入者情報
    DECLARE l_NameKj                        VARCHAR(160);       -- 請求先－氏名
    DECLARE l_NameKn                        VARCHAR(160);       -- 請求先－氏名カナ
    DECLARE l_PostalCode                    VARCHAR(12);        -- 請求先－郵便番号
    DECLARE l_UnitingAddress                VARCHAR(4000);      -- 請求先－住所
    DECLARE l_AddressKn                     TEXT;               -- 請求先－住所カナ
    DECLARE l_EntCustId                     VARCHAR(255);       -- 請求先－加盟店顧客
    DECLARE l_Phone                         VARCHAR(50);        -- 請求先－電話番号
    DECLARE l_Carrier                       INT;                -- 請求先－電話番号種類
    DECLARE l_MailAddress                   VARCHAR(255);       -- 請求先－メールアドレス
    DECLARE l_Occupation                    VARCHAR(255);       -- 請求先－職業
    DECLARE l_CorporateName                 VARCHAR(255);       -- 請求先－法人名
    DECLARE l_DivisionName                  VARCHAR(255);       -- 請求先－部署名
    DECLARE l_CpNameKj                      VARCHAR(160);       -- 請求先－担当者名
    DECLARE l_ResidentCard                  INT;                -- 不払い管理－住民票
    DECLARE l_Cinfo1                        VARCHAR(255);       -- 不払い管理－追加連絡先TEL１
    DECLARE l_CinfoNote1                    VARCHAR(255);       -- 不払い管理－追加連絡先備考１
    DECLARE l_CinfoStatus1                  INT;                -- 不払い管理－追加連絡先状態１
    DECLARE l_Cinfo2                        VARCHAR(255);       -- 不払い管理－追加連絡先TEL２
    DECLARE l_CinfoNote2                    VARCHAR(255);       -- 不払い管理－追加連絡先備考２
    DECLARE l_CinfoStatus2                  INT;                -- 不払い管理－追加連絡先状態２
    DECLARE l_Cinfo3                        VARCHAR(255);       -- 不払い管理－追加連絡先TEL３
    DECLARE l_CinfoNote3                    VARCHAR(255);       -- 不払い管理－追加連絡先備考３
    DECLARE l_CinfoStatus3                  INT;                -- 不払い管理－追加連絡先状態３
    -- 3) 履歴登録用顧客情報
    DECLARE l_GoodFlg                       TINYINT;            -- 優良顧客ﾌﾗｸﾞ
    DECLARE l_BlackFlg                      TINYINT;            -- ﾌﾞﾗｯｸ顧客ﾌﾗｸﾞ
    DECLARE l_IdentityDocumentFlg           TINYINT;            -- 身分証ｱｯﾌﾟﾛｰﾄﾞﾌﾗｸﾞ
    DECLARE l_RemindStopFlg                 TINYINT;            -- 不払い管理－督促除外
    -- 4) 履歴登録用OEM情報
    DECLARE l_OemId                         BIGINT;             -- OEMID
    DECLARE l_OemNameKj                     VARCHAR(160);       -- OEM先名
    -- 5) 履歴登録用加盟店情報
    DECLARE l_EnterpriseNameKj              VARCHAR(160);       -- 加盟店名
    DECLARE l_EntCpNameKj                   VARCHAR(160);       -- 担当者名
    DECLARE l_ContactPhoneNumber            VARCHAR(50);        -- 連絡先電話番号
    -- 6) 履歴登録用ｻｲﾄ情報
    DECLARE l_SiteNameKj                    VARCHAR(160);       -- ｻｲﾄ名
    -- 7) 履歴登録用請求情報
    DECLARE l_F_ClaimDate                   DATE;               -- 請求－初回請求日
    DECLARE l_F_LimitDate                   DATE;               -- 請求－初回請求期限
    DECLARE l_ClaimDate                     DATE;               -- 請求－請求日
    DECLARE l_LimitDate                     DATE;               -- 請求－請求期限
    DECLARE l_ClaimedBalance                BIGINT;             -- 入金確認－金額差異
    DECLARE l_ReclaimFee                    BIGINT;             -- 再請求追加手数料
    DECLARE l_ReceiptAmountTotal            BIGINT;             -- 入金済額
    -- 8) 履歴登録用審査ｼｽﾃﾑ情報
    DECLARE l_JudgeSystemResult             TEXT;               -- 審査ｼｽﾃﾑ結果
    DECLARE l_JudgeSystemResultBef          TEXT;               -- 結合前審査ｼｽﾃﾑ結果
    DECLARE l_TotalScoreBef                 INT;                -- 与信－与信点数－ｽｺｱﾘﾝｸﾞ結果(判定用)
    DECLARE l_DataStatusBef                 INT;                -- ﾃﾞｰﾀｽﾃｰﾀｽ(判定用)
    DECLARE l_DataStatusBk                  INT;                -- ﾃﾞｰﾀｽﾃｰﾀｽ(判定用)
    DECLARE l_CjrSeq                        BIGINT;             -- 与信審査結果Seq(判定用)
    -- 9) 履歴登録用入金情報
    DECLARE l_ReceiptDate                   DATE;               -- 入金確認－入金日
    DECLARE l_ReceiptProcessDate            DATETIME;           -- 入金確認－処理日
    DECLARE l_ReceiptClass                  INT;                -- 入金確認－入金方法
    DECLARE l_DepositDate                   DATE;               -- 入金確認－CB入金日
    DECLARE l_CloseReceiptDate              DATE;               -- 入金確認－クローズ入金日
    -- 10) 履歴登録用立替売上情報
    DECLARE l_ClearConditionForCharge       INT;                -- 着荷確認－立替ｸﾘｱ-ﾌﾗｸﾞ
    DECLARE l_ClearConditionDate            DATE;               -- 着荷確認－立替ｸﾘｱ-日付
    -- 11) 履歴登録用立替振込情報
    DECLARE l_ExecScheduleDate              DATE;               -- 着荷確認－立替予定日
    -- 12) 履歴登録用印紙代情報
    DECLARE l_StampFee                      BIGINT;             -- 印紙代
    -- 13) 履歴登録用ｷｬﾝｾﾙ情報
    DECLARE l_CancelDate                    DATETIME;           -- ｷｬﾝｾﾙ処理-ｷｬﾝｾﾙ日
    DECLARE l_CancelReasonCode              TEXT;               -- ｷｬﾝｾﾙ処理-ｷｬﾝｾﾙ理由
    -- 14) 履歴登録用与信情報
    DECLARE l_TotalScore                    INT;                -- 与信－与信点数－ｽｺｱﾘﾝｸﾞ結果
    -- 15) 履歴登録用配送情報
    DECLARE l_Deli_DestNameKj               VARCHAR(160);       -- 配送先－氏名
    DECLARE l_Deli_DestNameKn               VARCHAR(160);       -- 配送先－氏名ｶﾅ
    DECLARE l_Deli_PostalCode               VARCHAR(12);        -- 配送先－郵便番号
    DECLARE l_Deli_UnitingAddress           VARCHAR(4000);      -- 配送先－住所
    DECLARE l_Deli_Phone                    VARCHAR(50);        -- 配送先－電話番号
    -- 16) 履歴登録用伝票情報
    DECLARE l_Deli_JournalIncDate           DATETIME;           -- 伝票番号－登録日
    DECLARE l_Deli_DeliveryMethod           INT;                -- 伝票番号－配送方法
    DECLARE l_Deli_JournalNumber            VARCHAR(255);       -- 伝票番号－伝票番号
    -- 17) 履歴登録用商品情報
    DECLARE l_OrderItemInfo                 TEXT;               -- 商品明細
    DECLARE l_DeliveryFee                   BIGINT;             -- 送料
    DECLARE l_SettlementFee                 BIGINT;             -- 手数料
    DECLARE l_TaxClass                      BIGINT;             -- 外税額
    DECLARE l_SubTotal                      BIGINT;             -- 利用額合計
    -- 18) 履歴登録用商品情報
    DECLARE l_ProcessingDate                DATETIME;           -- 与信返信日時
    -- 19) 履歴登録用その他情報
    DECLARE l_ParentOrderId                 VARCHAR(50);        -- 代表注文ID
    DECLARE l_CustomerStatus                VARCHAR(50);        -- 顧客ｽﾃｰﾀｽ
    DECLARE l_CombinedAmount                BIGINT;             -- 他取りまとめ金額
    DECLARE l_MailInfo                      TEXT;               -- ﾒｰﾙ送信履歴
    DECLARE l_ReClaimInfo                   TEXT;               -- 再請求情報
    DECLARE l_StatusCaption                 TEXT;               -- 注文状態
    DECLARE l_P_OrderId                     TEXT;               -- 取りまとめ情報ー親注文ID
    DECLARE l_CombinedOrderSeq              TEXT;               -- 取りまとめ情報ー子注文ID（ｶﾝﾏ区切りで取得）
    DECLARE l_OpNameKj                      VARCHAR(160);       -- 請求先－与信関連－与信確定担当者名
    DECLARE l_CreditInfo                    VARCHAR(50);        -- 請求先－与信関連
    DECLARE l_ClaimAmount                   BIGINT;             -- 請求合計
    DECLARE l_PastDays                      INT;                -- 不払い管理－超過日数(Clm_F_LimitDateから経過している日数)
    DECLARE l_ReceiptPastDays               INT;                -- 不払い管理－入金遅れ日数(Clm_F_LimitDateとRct_ReceiptDateの差)
    DECLARE l_IncreArClass                  INT;                -- 与信－ｸﾗｽ
    DECLARE l_Incre_ArTel                   INT;                -- 社内与信－氏名－自動与信結果ｸﾗｽ(与信ｸﾗｽ取得用)
    DECLARE l_Incre_ArAddr                  INT;                -- 社内与信－住所－自動与信結果ｸﾗｽ(与信ｸﾗｽ取得用)
    DECLARE l_Reserve                       TEXT;               -- 予約項目

    -- その他
    -- NO_DATA_FOUND ｲﾍﾞﾝﾄﾊﾝﾄﾞﾗ用変数
    DECLARE no_data_found INT DEFAULT 1;

    -- ｶｰｿﾙ宣言
    DECLARE l_cur   CURSOR  FOR
        SELECT  cr.TotalScore
            ,   o.DataStatus
            ,   CONCAT('「', IFNULL(crd.DetectionPatternName, ''), '」', IFNULL(crd.DetectionPatternScoreWeighting, crd.DetectionPatternScore)) AS JudgeSystemResult
        FROM    T_Order o
                LEFT OUTER JOIN T_CjResult cr ON (cr.OrderSeq = o.OrderSeq)
                LEFT OUTER JOIN T_CjResult_Detail crd ON (crd.CjrSeq = cr.Seq)
        WHERE   cr.Seq = l_CjrSeq
        ;

    -- NO_DATA_FOUND 用ｲﾍﾞﾝﾄﾊﾝﾄﾞﾗ宣言
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_data_found = 0;

    -- ---------------------------
    -- 1.戻り値初期化
    -- ---------------------------
    SET po_ret_sts      =   0;
    SET po_ret_errcd    =   '';
    SET po_ret_sqlcd    =   0;
    SET po_ret_msg      =   '正常終了';

    -- ---------------------------
    -- 2.履歴登録用情報取得
    -- ---------------------------
    SELECT  o.OrderId                           -- 注文ID
        ,   o.OutOfAmends                       -- 補償対象外ﾌﾗｸﾞ
        ,   o.Oem_OrderId                       -- OEM任意番号
        ,   o.Ent_OrderId                       -- 任意注文番号
        ,   o.RegistDate                        -- 注文登録日時
        ,   o.ReceiptOrderDate                  -- ご注文日
        ,   o.ServiceExpectedDate               -- 役務提供予定日
        -- ｽﾃｰﾀｽ
        ,   (SELECT CONCAT('[', o.DataStatus, ']', KeyContent)
             FROM   M_Code
             WHERE  CodeId = 9
             AND    KeyCode = o.DataStatus
            ) AS DataStatus
        ,   o.UseAmount                         -- 利用額
        ,   o.CombinedClaimTargetStatus         -- 取りまとめ対象注文ｽﾃｰﾀｽ
        ,   o.AnotherDeliFlg                    -- 別配送先ﾌﾗｸﾞ
        ,   o.Incre_Note                        -- 備考
        ,   o.RemindClass                       -- 不払い管理－督促分類
        ,   o.FinalityRemindDate                -- 不払い管理－最終督促日
        ,   o.FinalityRemindOpId                -- 不払い管理－最終督促者
        ,   o.PromPayDate                       -- 不払い管理－支払約束日
        ,   o.LonghandLetter                    -- 不払い管理－手書き手紙
        ,   o.BriefNote                         -- 不払い管理－簡易備考
        ,   o.TouchHistoryFlg                   -- 不払い管理－ﾀｯﾁ履歴
        ,   o.VisitFlg                          -- 不払い管理－訪問済
        ,   o.FinalityCollectionMean            -- 不払い管理－最終改修手段
        ,   o.ClaimStopReleaseDate              -- 不払い管理－請求ｽﾄｯﾌﾟ解除日
        ,   o.LetterClaimStopFlg                -- 不払い管理－請求ｽﾄｯﾌﾟ紙
        ,   o.MailClaimStopFlg                  -- 不払い管理－請求ｽﾄｯﾌﾟﾒｰﾙ
        ,   o.Tel30DaysFlg                      -- 不払い管理－荷電30日ﾌﾗｸﾞ
        ,   o.Tel90DaysFlg                      -- 不払い管理－荷電90日ﾌﾗｸﾞ
        ,   o.OemClaimTransDate                 -- 不払い管理－OEM債権移管日
        ,   o.Dmg_DecisionDate                  -- 不払い管理－貸倒確定日
        ,   o.Oem_Note                          -- OEM先備考
        ,   o.Incre_ScoreTotal                  -- 与信－与信点数－ｽｺｱ合計
        ,   CASE WHEN cr.TotalScoreWeighting IS NOT NULL THEN (cr.TotalScoreWeighting + IFNULL(o.Incre_JudgeScoreTotal,0))
                 WHEN cr.TotalScore IS NOT NULL THEN (cr.TotalScore + IFNULL(o.Incre_JudgeScoreTotal,0))
                 ELSE NULL
            END AS TotalScore                   -- 与信－与信点数－ｽｺｱﾘﾝｸﾞ結果
        ,   o.Incre_Status                      -- 与信－社内与信
        ,   o.Dmi_Status                        -- 与信－DMIｽﾃｰﾀｽ
        ,   o.Dmi_DecisionDate                  -- 与信－与信日
        ,   o.Dmi_ResponseNote                  -- 与信－与信理由
        ,   o.Deli_ConfirmArrivalDate           -- 着荷確認－確認日
        ,   o.Deli_ConfirmArrivalFlg            -- 着荷確認－確認結果
        ,   o.Chg_FixedDate                     -- 着荷確認－立替締め日
        ,   o.MailPaymentSoonDate               -- ﾒｰﾙ送信-直前ﾒｰﾙ
        ,   o.MailLimitPassageCount             -- ﾒｰﾙ送信-未確認ﾒｰﾙ-件数
        ,   o.MailLimitPassageDate              -- ﾒｰﾙ送信-未確認ﾒｰﾙ-日付
        ,   o.Ent_Note                          -- 事業者備考
        ,   o.P_OrderSeq                        -- 親注文SEQ
        ,   o.CombinedClaimParentFlg            -- 取りまとめ後に親となったﾌﾗｸﾞ
        ,   o.Chg_NonChargeFlg                  -- 立替処理－立替対象外ﾌﾗｸﾞ
        ,   c.NameKj                            -- 請求先－氏名
        ,   c.NameKn                            -- 請求先－氏名ｶﾅ
        ,   c.PostalCode                        -- 請求先－郵便番号
        ,   c.UnitingAddress                    -- 請求先－住所
        ,   c.AddressKn                         -- 請求先－住所ｶﾅ
        ,   c.EntCustId                         -- 請求先－加盟店顧客
        ,   c.Phone                             -- 請求先－電話番号
        ,   c.Carrier                           -- 請求先－電話番号種類
        ,   c.MailAddress                       -- 請求先－ﾒｰﾙｱﾄﾞﾚｽ
        ,   c.Occupation                        -- 請求先－職業
        ,   c.CorporateName                     -- 請求先－法人名
        ,   c.DivisionName                      -- 請求先－部署名
        ,   c.CpNameKj                          -- 請求先－担当者名
        ,   c.ResidentCard                      -- 不払い管理－住民票
        ,   c.Cinfo1                            -- 不払い管理－追加連絡先TEL１
        ,   c.CinfoNote1                        -- 不払い管理－追加連絡先備考１
        ,   c.CinfoStatus1                      -- 不払い管理－追加連絡先状態１
        ,   c.Cinfo2                            -- 不払い管理－追加連絡先TEL２
        ,   c.CinfoNote2                        -- 不払い管理－追加連絡先備考２
        ,   c.CinfoStatus2                      -- 不払い管理－追加連絡先状態２
        ,   c.Cinfo3                            -- 不払い管理－追加連絡先TEL３
        ,   c.CinfoNote3                        -- 不払い管理－追加連絡先備考３
        ,   c.CinfoStatus3                      -- 不払い管理－追加連絡先状態３
        ,   c.Incre_ArTel                       -- 社内与信－氏名－自動与信結果ｸﾗｽ(与信ｸﾗｽ取得用)
        ,   c.Incre_ArAddr                      -- 社内与信－住所－自動与信結果ｸﾗｽ(与信ｸﾗｽ取得用)
        ,   mc.GoodFlg                          -- 優良顧客ﾌﾗｸﾞ
        ,   mc.BlackFlg                         -- ﾌﾞﾗｯｸ顧客ﾌﾗｸﾞ
        ,   mc.IdentityDocumentFlg              -- 身分証ｱｯﾌﾟﾛｰﾄﾞﾌﾗｸﾞ
        ,   mc.RemindStopFlg                    -- 不払い管理－督促除外
        ,   oem.OemId                           -- OEMID
        ,   oem.OemNameKj                       -- OEM先名
        ,   e.EnterpriseNameKj                  -- 加盟店名
        ,   e.CpNameKj                          -- 担当者名
        ,   e.ContactPhoneNumber                -- 連絡先電話番号
        ,   s.SiteNameKj                        -- ｻｲﾄ名
        ,   cc.F_ClaimDate                      -- 請求－初回請求日
        ,   cc.F_LimitDate                      -- 請求－初回請求期限
        ,   cc.ClaimDate                        -- 請求－請求日
        ,   cc.LimitDate                        -- 請求－請求期限
        ,   cc.ClaimedBalance                   -- 入金確認－金額差異
        ,   IFNULL(cc.ReceiptAmountTotal, 0)    -- 入金済額
        -- 再請求追加手数料
        ,   IFNULL((cc.DamageInterestAmount + cc.ClaimFee), 0) AS ReclaimFee
        ,   rc.ReceiptDate                      -- 入金確認－入金日
        ,   rc.ReceiptProcessDate               -- 入金確認－処理日
        ,   rc.ReceiptClass                     -- 入金確認－入金方法
        ,   rc.DepositDate                      -- 入金確認－CB入金日
        ,   pas.ClearConditionForCharge         -- 着荷確認－立替ｸﾘｱ-ﾌﾗｸﾞ
        ,   pas.ClearConditionDate              -- 着荷確認－立替ｸﾘｱ-日付
        ,   pc.ExecScheduleDate                 -- 着荷確認－立替予定日
        -- 印紙代
        -- 入金がなければNULL、入金されていても印紙代が存在しなければ0、それ以外は印紙代表示
        ,   (CASE
                WHEN (SELECT COUNT(1) FROM T_ReceiptControl WHERE OrderSeq = o.OrderSeq) = 0 THEN NULL
                WHEN (SELECT COUNT(1) FROM T_ReceiptControl WHERE OrderSeq = o.OrderSeq) > 0 AND (SELECT COUNT(1) FROM T_StampFee WHERE OrderSeq = o.OrderSeq) = 0 THEN 0
                ELSE sf.StampFee
             END
            ) AS StampFee
        ,   cn.CancelDate                       -- ｷｬﾝｾﾙ処理-ｷｬﾝｾﾙ日
        -- ｷｬﾝｾﾙ処理-ｷｬﾝｾﾙ理由
        ,   (SELECT CASE
                        WHEN TRIM(IFNULL(cn.CancelReason, '')) = '' THEN KeyContent
                        ELSE CONCAT(KeyContent, '(', cn.CancelReason, ')')
                    END
             FROM   M_Code
             WHERE  CodeId = 90
             AND    KeyCode = cn.CancelReasonCode
            ) AS CancelReasonCode
        ,   vcr.ReceiptDate AS CloseReceiptDate -- クローズ入金日
    INTO    l_OrderId
        ,   l_OutOfAmends
        ,   l_Oem_OrderId
        ,   l_Ent_OrderId
        ,   l_RegistDate
        ,   l_ReceiptOrderDate
        ,   l_ServiceExpectedDate
        ,   l_DataStatus
        ,   l_UseAmount
        ,   l_CombinedClaimTargetStatus
        ,   l_AnotherDeliFlg
        ,   l_Incre_Note
        ,   l_RemindClass
        ,   l_FinalityRemindDate
        ,   l_FinalityRemindOpId
        ,   l_PromPayDate
        ,   l_LonghandLetter
        ,   l_BriefNote
        ,   l_TouchHistoryFlg
        ,   l_VisitFlg
        ,   l_FinalityCollectionMean
        ,   l_ClaimStopReleaseDate
        ,   l_LetterClaimStopFlg
        ,   l_MailClaimStopFlg
        ,   l_Tel30DaysFlg
        ,   l_Tel90DaysFlg
        ,   l_OemClaimTransDate
        ,   l_Dmg_DecisionDate
        ,   l_Oem_Note
        ,   l_Incre_ScoreTotal
        ,   l_TotalScore
        ,   l_Incre_Status
        ,   l_Dmi_Status
        ,   l_Dmi_DecisionDate
        ,   l_Dmi_ResponseNote
        ,   l_Deli_ConfirmArrivalDate
        ,   l_Deli_ConfirmArrivalFlg
        ,   l_Chg_FixedDate
        ,   l_MailPaymentSoonDate
        ,   l_MailLimitPassageCount
        ,   l_MailLimitPassageDate
        ,   l_Ent_Note
        ,   l_P_OrderSeq
        ,   l_CombinedClaimParentFlg
        ,   l_Chg_NonChargeFlg
        ,   l_NameKj
        ,   l_NameKn
        ,   l_PostalCode
        ,   l_UnitingAddress
        ,   l_AddressKn
        ,   l_EntCustId
        ,   l_Phone
        ,   l_Carrier
        ,   l_MailAddress
        ,   l_Occupation
        ,   l_CorporateName
        ,   l_DivisionName
        ,   l_CpNameKj
        ,   l_ResidentCard
        ,   l_Cinfo1
        ,   l_CinfoNote1
        ,   l_CinfoStatus1
        ,   l_Cinfo2
        ,   l_CinfoNote2
        ,   l_CinfoStatus2
        ,   l_Cinfo3
        ,   l_CinfoNote3
        ,   l_CinfoStatus3
        ,   l_Incre_ArTel
        ,   l_Incre_ArAddr
        ,   l_GoodFlg
        ,   l_BlackFlg
        ,   l_IdentityDocumentFlg
        ,   l_RemindStopFlg
        ,   l_OemId
        ,   l_OemNameKj
        ,   l_EnterpriseNameKj
        ,   l_EntCpNameKj
        ,   l_ContactPhoneNumber
        ,   l_SiteNameKj
        ,   l_F_ClaimDate
        ,   l_F_LimitDate
        ,   l_ClaimDate
        ,   l_LimitDate
        ,   l_ClaimedBalance
        ,   l_ReceiptAmountTotal
        ,   l_ReclaimFee
        ,   l_ReceiptDate
        ,   l_ReceiptProcessDate
        ,   l_ReceiptClass
        ,   l_DepositDate
        ,   l_ClearConditionForCharge
        ,   l_ClearConditionDate
        ,   l_ExecScheduleDate
        ,   l_StampFee
        ,   l_CancelDate
        ,   l_CancelReasonCode
        ,   l_CloseReceiptDate
    FROM    T_Order o
            INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
            INNER JOIN T_Site s ON (s.SiteId = o.SiteId)
            INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
            INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq)
            INNER JOIN T_ManagementCustomer mc ON (mc.ManCustId = ec.ManCustId)
            LEFT OUTER JOIN T_Oem oem ON (oem.OemId = o.OemId)
            LEFT OUTER JOIN T_CjResult cr ON (cr.OrderSeq = o.OrderSeq)
            LEFT OUTER JOIN T_CjResult_Detail crd ON (crd.CjrSeq = cr.Seq)
            LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
            LEFT OUTER JOIN T_ReceiptControl rc ON (rc.ReceiptSeq = cc.LastReceiptSeq)
            LEFT OUTER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
            LEFT OUTER JOIN T_PayingControl pc ON (pc.Seq = pas.PayingControlSeq)
            LEFT OUTER JOIN T_StampFee sf ON (sf.OrderSeq = o.OrderSeq)
            LEFT OUTER JOIN T_Cancel cn ON (cn.OrderSeq = o.OrderSeq AND cn.ValidFlg = 1)
            LEFT OUTER JOIN V_CloseReceiptControl vcr ON (vcr.OrderSeq = cc.OrderSeq)
    WHERE   o.OrderSeq  =   pi_order_seq
    LIMIT 1
    ;

    IF  no_data_found = 0   THEN
        SET po_ret_sts  =   -1;
        SET po_ret_msg  =   'データが存在しません。';
        LEAVE proc;
    END IF;

    -- ---------------------------
    -- 3.親注文ID取得
    -- ---------------------------
    -- 取りまとめ注文ｽﾃｰﾀｽが 91 or 92 場合
    IF  l_CombinedClaimTargetStatus = 91 OR l_CombinedClaimTargetStatus = 92    THEN
        -- 取りまとめ後に親となったﾌﾗｸﾞが 1 の場合
        IF  l_CombinedClaimParentFlg = 1    THEN
            -- 2.で取得した注文IDが代表注文ID
            SET l_ParentOrderId =   l_OrderId;
        -- それ以外の場合
        ELSE
            -- 2.で取得した親注文Seqを条件に代表注文IDを検索
            SELECT  OrderId
            INTO    l_ParentOrderId
            FROM    T_Order
            WHERE   OrderSeq    =   P_OrderSeq
            AND     P_OrderSeq  =   l_P_OrderSeq
            ;
        END IF;
    -- それ以外の場合
    ELSE
        SET l_ParentOrderId =   '---';
    END IF;

    -- ---------------------------
    -- 4.顧客ｽﾃｰﾀｽ取得
    -- ---------------------------
    -- 優良顧客ﾌﾗｸﾞ が 1 の場合
    IF  l_GoodFlg = 1   THEN
        IF  l_CustomerStatus IS NOT NULL    THEN
            SET l_CustomerStatus    =   CONCAT(l_CustomerStatus, '、優良顧客');
        ELSE
            SET l_CustomerStatus    =   '優良顧客';
        END IF;
    END IF;

    -- ﾌﾞﾗｯｸ顧客ﾌﾗｸﾞ が 1 の場合
    IF  l_BlackFlg = 1  THEN
        IF  l_CustomerStatus IS NOT NULL    THEN
            SET l_CustomerStatus    =   CONCAT(l_CustomerStatus, '、ブラック顧客');
        ELSE
            SET l_CustomerStatus    =   'ブラック顧客';
        END IF;
    ENd IF;

    -- 身分証ｱｯﾌﾟﾛｰﾄﾞﾌﾗｸﾞ が 1 の場合
    IF  l_IdentityDocumentFlg = 1   THEN
        IF  l_CustomerStatus IS NOT NULL    THEN
            SET l_CustomerStatus    =   CONCAT(l_CustomerStatus, '、身分証アップロード済');
        ELSE
            SET l_CustomerStatus    =   '身分証アップロード済';
        END IF;
    END IF;

    -- ---------------------------
    -- 5.商品情報取得
    -- ---------------------------
    -- --------------------メモ--------------------
    -- 以下項目は必ず取得出来る
    -- ・商品明細（ﾃﾞｰﾀｸﾗｽ = 1）
    -- ・送料（ﾃﾞｰﾀｸﾗｽ = 2）
    -- ・手数料（ﾃﾞｰﾀｸﾗｽ = 3）
    -- 以下項目は取得出来ない場合がある
    -- ・外税額（ﾃﾞｰﾀｸﾗｽ = 4）
    -- 　↑が取得出来ない場合、NULLを設定する
    -- 2015/06/25 追加メモ
    -- 以下項目は削除される場合がある
    -- ・商品明細（ﾃﾞｰﾀｸﾗｽ = 1）
    -- 　↑ValidFlg = 1 の明細のみ取得する
    -- --------------------------------------------
    -- 商品明細（ﾃﾞｰﾀｸﾗｽ = 1）をJSON形式で取得
    SELECT  CONCAT('[',
                        GROUP_CONCAT(CAST(CONCAT('{\"ItemNameKj\":\"', F_GetEscapeItemNameKj(ItemNameKj), '\"') AS CHAR),
                                     CAST(CONCAT(',\"UnitPrice\":\"', UnitPrice, '\"') AS CHAR),
                                     CAST(CONCAT(',\"ItemNum\":\"', ItemNum, '\"') AS CHAR),
                                     CAST(CONCAT(',\"SumMoney\":\"', SumMoney) AS CHAR), '\"}'),
                  ']') AS ItemInfo
    INTO    l_OrderItemInfo
    FROM    T_OrderItems
    WHERE   DataClass   =   1
    AND     ValidFlg    =   1           -- 注文修正時に削除された商品は取得しない
    AND     OrderSeq    =   pi_order_seq
    ;

    -- 送料（ﾃﾞｰﾀｸﾗｽ = 2）を取得
    SELECT  SumMoney
    INTO    l_DeliveryFee
    FROM    T_OrderItems
    WHERE   DataClass   =   2
    AND     OrderSeq    =   pi_order_seq
    ;

    -- 手数料（ﾃﾞｰﾀｸﾗｽ = 3）を取得
    SELECT  SumMoney
    INTO    l_SettlementFee
    FROM    T_OrderItems
    WHERE   DataClass   =   3
    AND     OrderSeq    =   pi_order_seq
    ;

    -- 外税額（ﾃﾞｰﾀｸﾗｽ = 4）を取得
    SELECT  SumMoney
    INTO    l_TaxClass
    FROM    T_OrderItems
    WHERE   DataClass   =   4
    AND     OrderSeq    =   pi_order_seq
    ;

    -- 取得出来ない場合、NULLを設定
    IF  no_data_found = 0   THEN
        SET l_TaxClass  =   NULL;
    END IF;

    -- 利用額合計を取得
    SELECT  SUM(SumMoney) AS SubTotal
    INTO    l_SubTotal
    FROM    T_OrderItems
    WHERE   ValidFlg    =   1           -- 注文修正時に削除された商品は取得しない
    AND     OrderSeq    =   pi_order_seq
    ;

    -- ---------------------------
    -- 6.他取りまとめ金額取得
    -- ---------------------------
    SELECT  IFNULL(SUM(o.UseAmount), 0) AS CombinedAmount
    INTO    l_CombinedAmount
    FROM    T_Order o
    WHERE   o.P_OrderSeq    =   pi_order_seq
    AND     o.CombinedClaimParentFlg <> 1
    ;

    -- ---------------------------
    -- 7.配送情報/伝票情報取得
    -- ---------------------------
    SELECT  DestNameKj                  -- 配送先-氏名
        ,   DestNameKn                  -- 配送先-氏名ｶﾅ
        ,   PostalCode                  -- 配送先-郵便番号
        ,   UnitingAddress              -- 配送先-住所
        ,   Phone                       -- 配送先-電話番号
        ,   Deli_JournalIncDate         -- 伝票番号-登録日
        ,   Deli_DeliveryMethod         -- 伝票番号-配送方法
        ,   Deli_JournalNumber          -- 伝票番号-伝票番号
    INTO    l_Deli_DestNameKj
        ,   l_Deli_DestNameKn
        ,   l_Deli_PostalCode
        ,   l_Deli_UnitingAddress
        ,   l_Deli_Phone
        ,   l_Deli_JournalIncDate
        ,   l_Deli_DeliveryMethod
        ,   l_Deli_JournalNumber
    FROM    V_Delivery vd
            INNER JOIN T_Order o ON (o.OrderSeq = vd.OrderSeq)
    WHERE   o.OrderSeq  =   pi_order_seq
    LIMIT 1
    ;

    -- ---------------------------
    -- 8.ﾒｰﾙ送信履歴取得
    -- ---------------------------
    -- 送信履歴情報をJSON形式で取得
    SELECT  CONCAT('[',
                        GROUP_CONCAT(CAST(CONCAT('{\"MailTemplateId\":\"', msh.MailTemplateId, '\"') AS CHAR),
                                     CAST(CONCAT(',\"SendDate\":\"', msh.MailSendDate) AS CHAR), '\"}'),
                  ']') AS MailInfo
    INTO    l_MailInfo
    FROM    T_MailSendHistory msh
            INNER JOIN T_Order o ON (o.OrderSeq = msh.OrderSeq)
    WHERE   o.OrderSeq  =   pi_order_seq
    ;

    -- ---------------------------
    -- 9.再請求情報取得
    -- ---------------------------
    -- 再請求情報をJSON形式で取得
    SELECT  CONCAT('[',
                        GROUP_CONCAT(CAST(CONCAT('{\"Seq\":\"', ch.Seq, '\"') AS CHAR),
                                     CAST(CONCAT(',\"ClaimPattern\":\"', ch.ClaimPattern, '\"') AS CHAR),
                                     CAST(CONCAT(',\"ClaimDate\":\"', ch.ClaimDate, '\"') AS CHAR),
                                     CAST(CONCAT(',\"LimitDate\":\"', ch.LimitDate, '\"') AS CHAR),
                                     CAST(CONCAT(',\"Additional\":\"', ch.DamageInterestAmount + ch.ClaimFee + ch.AdditionalClaimFee) AS CHAR), '\"}'),
                  ']') AS ReClaimInfo
    INTO    l_ReClaimInfo
    FROM    T_ClaimHistory ch
            INNER JOIN T_Order o ON (o.P_OrderSeq = ch.OrderSeq)
    WHERE   o.OrderSeq      =   pi_order_seq
    AND     ch.ClaimPattern <>  1
    AND     ch.ValidFlg     =   1
    ORDER BY
            FIELD(ch.ClaimPattern, '2, 3, 4, 6, 7, 8, 9, 5')
    ;

    -- ---------------------------
    -- 10.取りまとめ情報取得
    -- ---------------------------
    -- 取りまとめ注文ｽﾃｰﾀｽが 91 or 92 場合のみ、取りまとめ情報を取得する。
    IF  l_CombinedClaimTargetStatus = 91 OR l_CombinedClaimTargetStatus = 92    THEN
        -- 取りまとめ後に親となったﾌﾗｸﾞが1の場合（親注文の場合）
        IF  l_CombinedClaimParentFlg = 1    THEN
            -- 子注文の注文IDをｶﾝﾏ区切りで取得する
            SELECT  GROUP_CONCAT(o.OrderId separator ', ') AS CombinedOrderSeq
            INTO    l_CombinedOrderSeq
            FROM    T_Order o
            WHERE   o.OrderSeq <> o.P_OrderSeq
            AND     o.P_OrderSeq    =   pi_order_seq
            ;

            -- 念のため、親注文IDにはNULLを設定する
            SET l_P_OrderId =   NULL;
        ELSE
            -- 子注文の場合、親注文IDを取得する
            SELECT  (SELECT OrderId FROM T_Order WHERE OrderSeq = o.P_OrderSeq) AS OrderId
            INTO    l_P_OrderId
            FROM    T_Order o
            WHERE   o.OrderSeq  =   pi_order_seq
            ;

            -- 念のため、子注文IDにはNULLを設定する
            SET l_CombinedOrderSeq  =   NULL;
        END IF;
    END IF;

    -- ---------------------------
    -- 11.注文状態情報取得
    -- ---------------------------
    -- 注文状態情報をJSON形式で取得
    -- 複数入金に対応するため、T_ReceiptControl と結合 → 入金Seqの降順でｿｰﾄした1件目を取得 → CONCATでJSON形式で取得
    SELECT  CONCAT(GROUP_CONCAT(CAST(CONCAT('{\"DataStatus\":\"', t.DataStatus, '\"') AS CHAR),
                                CAST(CONCAT(',\"CloseReason\":\"', t.CloseReason, '\"') AS CHAR),
                                CAST(CONCAT(',\"Cnl_ReturnSaikenCancelFlg\":\"', t.Cnl_ReturnSaikenCancelFlg, '\"') AS CHAR),
                                CAST(CONCAT(',\"Clm_F_LimitDate\":\"', t.F_LimitDate, '\"') AS CHAR),
                                CAST(CONCAT(',\"Rct_Status\":\"', t.Rct_Status, '\"') AS CHAR),
                                CAST(CONCAT(',\"Cnl_Status\":\"', t.Cnl_Status, '\"') AS CHAR),
                                CAST(CONCAT(',\"ReceiptDate\":\"', t.ReceiptDate, '\"') AS CHAR),
                                CAST(CONCAT(',\"CloseReceiptDate\":\"', t.CloseReceiptDate) AS CHAR), '\"}')
                  ) AS StatusCaption
    INTO    l_StatusCaption
    FROM    (
             SELECT o.DataStatus AS DataStatus
                ,   IFNULL(o.CloseReason, -1) AS CloseReason
                ,   IFNULL(o.Cnl_ReturnSaikenCancelFlg, '') AS Cnl_ReturnSaikenCancelFlg
                ,   IFNULL(cc.F_LimitDate, '') AS F_LimitDate
                ,   IFNULL(o.Rct_Status, '') AS Rct_Status
                ,   IFNULL(o.Cnl_Status, '') AS Cnl_Status
                ,   IFNULL(rc.ReceiptDate, '') AS ReceiptDate
                ,   IFNULL(vcr.ReceiptDate, '') AS CloseReceiptDate
             FROM   T_Order o
                    LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = o.P_OrderSeq)
                    LEFT OUTER JOIN T_ReceiptControl rc ON (rc.OrderSeq = o.P_OrderSeq)
                    LEFT OUTER JOIN V_CloseReceiptControl vcr ON (vcr.OrderSeq = cc.OrderSeq)
             WHERE  o.OrderSeq = pi_order_seq
             ORDER BY
                    rc.ReceiptSeq DESC
             LIMIT 1
            ) t
    ;

    -- ---------------------------
    -- 12.与信関連情報取得
    -- ---------------------------
    SELECT  CONCAT(
                -- 電話履歴
                CASE c.PhoneHistory
                    WHEN 1 THEN '電話履歴：○　 '
                    WHEN 3 THEN '電話履歴：×　 '
                    ELSE '電話履歴：--　 '
                END,
                -- e電
                CASE c.eDen
                    WHEN 1 THEN 'ｅ電：○　 '
                    WHEN 2 THEN 'ｅ電：△　 '
                    WHEN 3 THEN 'ｅ電：×　 '
                    ELSE 'ｅ電：--　 '
                END,
                -- アドレス
                CASE c.RealSendMailResult
                    WHEN 1 THEN 'アドレス：OK　 '
                    WHEN 2 THEN 'アドレス：NG　 '
                    ELSE 'アドレス：--　 '
                END,
                '与信確定担当：',
                CASE
                    WHEN TRIM(IFNULL(o.Incre_DecisionOpId, '')) = '' THEN ''
                    WHEN IFNULL((SELECT NameKj FROM T_Operator WHERE OpId = o.Incre_DecisionOpId), '') = '' THEN ''
                    ELSE (SELECT NameKj FROM T_Operator WHERE OpId = o.Incre_DecisionOpId)
                END
            ) AS CreditInfo
    INTO    l_CreditInfo
    FROM    T_Customer c
            INNER JOIN T_Order o ON (o.OrderSeq = c.OrderSeq)
    WHERE   o.OrderSeq  =   pi_order_seq
    ;

    -- ---------------------------
    -- 13.請求合計を計算
    -- ---------------------------
    -- 利用額合計 + 再請求手数料
    SET l_ClaimAmount   =   l_SubTotal + l_ReclaimFee;

    -- ---------------------------
    -- 14.審査ｼｽﾃﾑ結果を生成
    -- ---------------------------
    -- 変数を初期化
    SET no_data_found   =   1;

    -- 最新の審査ｼｽﾃﾑ結果Seqを取得
    SELECT  MAX(o.DataStatus) AS DataStatus
        ,   IFNULL(MAX(Seq), 0)
    INTO    l_DataStatusBk
        ,   l_CjrSeq
    FROM    T_Order o
            LEFT OUTER JOIN T_CjResult cr ON (cr.OrderSeq = o.OrderSeq)
    WHERE   o.OrderSeq = pi_order_seq
    GROUP BY
            o.OrderSeq
    ;

    -- ﾃﾞｰﾀｽﾃｰﾀｽ：11 OR 12で最新の1件が取得できない場合にはNULLを設定する
    IF  l_CjrSeq = 0 AND (l_DataStatusBk = 11 OR l_DataStatusBk = 12)   THEN
        SET l_JudgeSystemResult =   NULL;
    -- ﾃﾞｰﾀｽﾃｰﾀｽ：11 OR 12以外で最新の1件が取得できない場合には「審査システム判定不可」を設定する
    ELSEIF  l_CjrSeq = 0 AND (l_DataStatusBk <> 11 OR l_DataStatusBk <> 12) THEN
        SET l_JudgeSystemResult =   '審査システム判定不可';
    ELSE
        -- ｶｰｿﾙｵｰﾌﾟﾝ
        OPEN    l_cur;
            -- 最初のｶｰｿﾙをFetch
            FETCH   l_cur   INTO l_TotalScoreBef, l_DataStatusBef, l_JudgeSystemResultBef;
            -- whileﾙｰﾌﾟで処理する
            WHILE   no_data_found != 0  DO
                -- ｽｺｱﾘﾝｸﾞ結果 が NULL かつ ﾃﾞｰﾀｽﾃｰﾀｽ：11 以外の場合には「審査システム判定不可」を設定する
                IF  l_TotalScoreBef IS NULL AND l_DataStatusBef <> 11   THEN
                    SET l_JudgeSystemResult =   '審査システム判定不可';
                ELSE
                    IF  l_JudgeSystemResult IS NULL THEN
                        SET l_JudgeSystemResult =   l_JudgeSystemResultBef;
                    ELSE
                        SET l_JudgeSystemResult =   CONCAT(l_JudgeSystemResult , '/', l_JudgeSystemResultBef);
                    END IF;
                END IF;
                -- 次のｶｰｿﾙをFetch
                FETCH   l_cur   INTO l_TotalScoreBef, l_DataStatusBef, l_JudgeSystemResultBef;
            END WHILE;
        -- ｶｰｿﾙｸﾛｰｽﾞ
        CLOSE   l_cur;
    END IF;

    -- ---------------------------
    -- 15.日数計算
    -- ---------------------------
    -- 不払い管理－超過日数
    -- 初回支払期限がNULL の場合は NULL
    IF  l_F_LimitDate IS NULL   THEN
        SET l_PastDays = NULL;
    -- 初回支払期限との日数差を取得する。
    ELSE
        SET l_PastDays  =   TIMESTAMPDIFF(DAY, l_F_LimitDate, CURDATE());
    END IF;
    -- 不払い管理－入金遅れ日数
    -- 請求がクローズした入金日がNULL の場合は NULL
    IF  l_CloseReceiptDate IS NULL   THEN
        SET l_ReceiptPastDays   =   NULL;
    -- 請求がクローズした入金日と初回支払期限の日数差を取得する。
    ELSE
        SET l_ReceiptPastDays   =   TIMESTAMPDIFF(DAY, l_F_LimitDate, l_CloseReceiptDate);
    END IF;

    -- ---------------------------
    -- 16.与信ｸﾗｽ設定
    -- ---------------------------
    IF  l_Incre_ArTel > l_Incre_ArAddr  THEN
        SET l_IncreArClass  =   l_Incre_ArTel;
    ELSE
        SET l_IncreArClass  =   l_Incre_ArAddr;
    END IF;

    -- ---------------------------
    -- 17.予約項目取得
    -- ---------------------------
    -- 予約項目をJSON形式で取得
    SELECT  CONCAT(GROUP_CONCAT(CAST(CONCAT('{\"DispDecimalPoint\":\"', e.DispDecimalPoint, '\"') AS CHAR),
                                CAST(CONCAT(',\"CreditTransferRequestFlg\":\"', ao.CreditTransferRequestFlg, '\"') AS CHAR),
                                CAST(CONCAT(',\"ExtraPayType\":\"', IFNULL(ao.ExtraPayType,''), '\"') AS CHAR),
                                CAST(CONCAT(',\"ExtraPayKey\":\"', IFNULL(ao.ExtraPayKey,''), '\"') AS CHAR),
                                '}')) AS Reserve
    INTO    l_Reserve
    FROM    T_Enterprise e
            INNER JOIN T_Order o ON (o.EnterpriseId = e.EnterpriseId)
            INNER JOIN AT_Order ao ON (o.OrderSeq = ao.OrderSeq)
    WHERE   o.OrderSeq  =   pi_order_seq
    ;

    -- ---------------------------
    -- 18.注文履歴登録
    -- ---------------------------
    INSERT
    INTO    T_OrderHistory( OrderSeq                            -- 注文SEQ
                        ,   HistoryReasonCode                   -- 履歴理由ｺｰﾄﾞ
                        ,   ORD_OrderId                         -- 注文ID (旧取引ID)
                        ,   ORD_OutOfAmends                     -- 補償対象外ﾌﾗｸﾞ
                        ,   ORD_Oem_OrderId                     -- OEM任意番号
                        ,   ORD_Ent_OrderId                     -- 任意注文番号
                        ,   ORD_RegistDate                      -- 注文登録日時
                        ,   ORD_ReceiptOrderDate                -- ご注文日
                        ,   ORD_ServiceExpectedDate             -- 役務提供予定日
                        ,   ORD_DataStatus                      -- ｽﾃｰﾀｽ
                        ,   CJM_ProcessingDate                  -- 与信返信日時
                        ,   ORD_UseAmount                       -- 利用額
                        ,   StatusCaption                       -- 注文状態
                        ,   ORD_CombinedClaimTargetStatus       -- 取りまとめ対象注文ｽﾃｰﾀｽ
                        ,   ORD_CombinedClaimParentFlg          -- 取りまとめ後に親となったﾌﾗｸﾞ
                        ,   ORD_CombinedClaimParentOrderId      -- 代表注文ID
                        ,   CUS_NameKj                          -- 請求先-氏名
                        ,   CUS_NameKn                          -- 請求先-氏名ｶﾅ
                        ,   CUS_PostalCode                      -- 請求先-郵便番号
                        ,   CUS_UnitingAddress                  -- 請求先-住所
                        ,   CUS_AddressKn                       -- 請求先-住所ｶﾅ
                        ,   CUS_EntCustId                       -- 請求先-加盟店顧客
                        ,   CustomerStatus                      -- 請求先-顧客ｽﾃｰﾀｽ
                        ,   CUS_Phone                           -- 請求先-電話番号
                        ,   CUS_Carrier                         -- 請求先-電話番号種類
                        ,   CUS_MailAddress                     -- 請求先-ﾒｰﾙｱﾄﾞﾚｽ
                        ,   CUS_Occupation                      -- 請求先-職業
                        ,   CUS_CorporateName                   -- 請求先-法人名
                        ,   CUS_DivisionName                    -- 請求先-部署名
                        ,   CUS_CpNameKj                        -- 請求先-担当者名
                        ,   CreditInfo                          -- 請求先-与信関連
                        ,   ORD_Chg_NonChargeFlg                -- 立替処理－立替対象外ﾌﾗｸﾞ
                        ,   ORD_AnotherDeliFlg                  -- 別配送先ﾌﾗｸﾞ
                        ,   DEL_DestNameKj                      -- 配送先-氏名
                        ,   DEL_DestNameKn                      -- 配送先-氏名ｶﾅ
                        ,   DEL_PostalCode                      -- 配送先-郵便番号
                        ,   DEL_UnitingAddress                  -- 配送先-住所
                        ,   DEL_Phone                           -- 配送先-電話番号
                        ,   OEM_OemNameKj                       -- 事業者情報-OEM先名
                        ,   ENT_EnterpriseNameKj                -- 事業者情報-事業者名
                        ,   SIT_SiteNameKj                      -- 事業者情報-購入ｻｲﾄ
                        ,   ENT_CpNameKj                        -- 事業者情報-担当者名
                        ,   ENT_ContactPhoneNumber              -- 事業者情報-電話番号
                        ,   OrderItemInfo                       -- 商品明細情報(JSON形式で保存)
                        ,   DeliveryFee                         -- 送料
                        ,   SettlementFee                       -- 手数料
                        ,   ExTaxAmount                         -- 外税額
                        ,   TotalSumMoney                       -- 利用額合計
                        ,   ReclaimFee                          -- 再請求追加手数料(Clm_L_DamageInterestAmount + Clm_L_ClaimFee)
                        ,   OtherCombinedAmount                 -- 他取りまとめ金額
                        ,   ORD_InstallmentPlanAmount           -- 入金済額
                        ,   TotalClaimMoney                     -- 請求合計(利用額合計 + 再請求追加手数料 - 入金済額)
                        ,   ORD_Incre_Note                      -- 備考
                        ,   JudgeSystemResult                   -- 審査ｼｽﾃﾑ結果
                        ,   PastDays                            -- 不払い管理-超過日数(Clm_F_LimitDateから経過している日数)
                        ,   ORD_RemindClass                     -- 不払い管理-督促分類
                        ,   MAN_RemindStopFlg                   -- 不払い管理-督促除外
                        ,   ORD_FinalityRemindDate              -- 不払い管理-最終督促日
                        ,   ORD_FinalityRemindOpId              -- 不払い管理-最終督促者
                        ,   ORD_PromPayDate                     -- 不払い管理-支払約束日
                        ,   CUS_ResidentCard                    -- 不払い管理-住民票
                        ,   ORD_LonghandLetter                  -- 不払い管理-手書き手紙
                        ,   ORD_BriefNote                       -- 不払い管理-簡易備考
                        ,   ORD_TouchHistoryFlg                 -- 不払い管理-ﾀｯﾁ履歴
                        ,   ORD_VisitFlg                        -- 不払い管理-訪問済
                        ,   ReceiptPastDays                     -- 不払い管理-入金遅れ日数(Clm_F_LimitDateとRct_ReceiptDateの差)
                        ,   ORD_FinalityCollectionMean          -- 不払い管理-最終改修手段
                        ,   ORD_ClaimStopReleaseDate            -- 不払い管理-請求ｽﾄｯﾌﾟ解除日
                        ,   ORD_LetterClaimStopFlg              -- 不払い管理-請求ｽﾄｯﾌﾟ紙
                        ,   ORD_MailClaimStopFlg                -- 不払い管理-請求ｽﾄｯﾌﾟﾒｰﾙ
                        ,   ORD_Tel30DaysFlg                    -- 不払い管理-荷電30日ﾌﾗｸﾞ
                        ,   ORD_Tel90DaysFlg                    -- 不払い管理-荷電90日ﾌﾗｸﾞ
                        ,   CUS_Cinfo1                          -- 不払い管理-追加連絡先TEL1
                        ,   CUS_CinfoNote1                      -- 不払い管理-追加連絡先備考1
                        ,   CUS_CinfoStatus1                    -- 不払い管理-追加連絡先状態1
                        ,   CUS_Cinfo2                          -- 不払い管理-追加連絡先TEL2
                        ,   CUS_CinfoNote2                      -- 不払い管理-追加連絡先備考2
                        ,   CUS_CinfoStatus2                    -- 不払い管理-追加連絡先状態2
                        ,   CUS_Cinfo3                          -- 不払い管理-追加連絡先TEL3
                        ,   CUS_CinfoNote3                      -- 不払い管理-追加連絡先備考3
                        ,   CUS_CinfoStatus3                    -- 不払い管理-追加連絡先状態3
                        ,   ORD_OemClaimTransDate               -- 不払い管理-OEM債権移管日
                        ,   ORD_Dmg_DecisionDate                -- 不払い管理-貸倒確定日
                        ,   ORD_Oem_Note                        -- OEM先備考
                        ,   IncreArClass                        -- 与信-ｸﾗｽ(Incre_ArTel､Incre_ArAddrの値が大きい方を設定する)
                        ,   ORD_Incre_ScoreTotal                -- 与信-与信点数-ｽｺｱ合計
                        ,   CJR_TotalScore                      -- 与信-与信点数-ｽｺｱﾘﾝｸﾞ結果
                        ,   ORD_Incre_Status                    -- 与信-社内与信
                        ,   ORD_Dmi_Status                      -- 与信-DMIｽﾃｰﾀｽ
                        ,   ORD_Dmi_DecisionDate                -- 与信-与信日
                        ,   ORD_Dmi_ResponseNote                -- 与信-与信理由
                        ,   ITM_Deli_JournalIncDate             -- 伝票番号-登録日
                        ,   ITM_Deli_DeliveryMethod             -- 伝票番号-配送方法
                        ,   ITM_Deli_JournalNumber              -- 伝票番号-伝票番号
                        ,   CLM_F_ClaimDate                     -- 請求-初回請求日
                        ,   CLM_F_LimitDate                     -- 請求-初回請求期限
                        ,   CLM_ClaimDate                       -- 請求-請求日
                        ,   CLM_LimitDate                       -- 請求-請求期限
                        ,   ORD_Deli_ConfirmArrivalDate         -- 着荷確認-確認日
                        ,   ORD_Deli_ConfirmArrivalFlg          -- 着荷確認-確認結果
                        ,   PAS_ClearConditionForCharge         -- 着荷確認-立替ｸﾘｱ-ﾌﾗｸﾞ
                        ,   PAS_ClearConditionDate              -- 着荷確認-立替ｸﾘｱ-日付
                        ,   ORD_Chg_FixedDate                   -- 着荷確認-立替締め日
                        ,   PAC_ExecScheduleDate                -- 着荷確認-立替予定日
                        ,   REC_ReceiptDate                     -- 入金確認-入金日
                        ,   REC_ReceiptProcessDate              -- 入金確認-処理日
                        ,   REC_ReceiptClass                    -- 入金確認-入金方法
                        ,   CLM_ClaimedBalance                  -- 入金確認-金額差異
                        ,   STF_StampFee                        -- 入金確認-印紙代
                        ,   REC_DepositDate                     -- 入金確認-CB入金日
                        ,   MailInfo                            -- ﾒｰﾙ履歴(JSON形式で保存)
                        ,   ReClaimInfo                         -- 再請求(JSON形式で保存)
                        ,   CNL_CancelDate                      -- ｷｬﾝｾﾙ処理-ｷｬﾝｾﾙ日
                        ,   CNL_CancelReasonCode                -- ｷｬﾝｾﾙ処理-ｷｬﾝｾﾙ理由
                        ,   ORD_MailPaymentSoonDate             -- ﾒｰﾙ送信-直前ﾒｰﾙ
                        ,   ORD_MailLimitPassageCount           -- ﾒｰﾙ送信-未確認ﾒｰﾙ-件数
                        ,   ORD_MailLimitPassageDate            -- ﾒｰﾙ送信-未確認ﾒｰﾙ-日付
                        ,   ORD_Ent_Note                        -- 事業者備考
                        ,   ORD_CombinedParentOrderSeq          -- 取りまとめ元注文SEQ
                        ,   ORD_CombinedOrderSeq                -- 取りまとめ先注文SEQ(ｶﾝﾏ区切りで保存)
                        ,   Reserve                             -- 予約項目
                        ,   RegistDate                          -- 登録日時
                        ,   RegistId                            -- 登録者
                          )
                        VALUES
                          ( pi_order_seq                        -- 注文SEQ
                        ,   pi_history_reason_cd                -- 履歴理由ｺｰﾄﾞ
                        ,   l_OrderId                           -- 注文ID (旧取引ID)
                        ,   l_OutOfAmends                       -- 補償対象外ﾌﾗｸﾞ
                        ,   l_Oem_OrderId                       -- OEM任意番号
                        ,   l_Ent_OrderId                       -- 任意注文番号
                        ,   l_RegistDate                        -- 注文登録日時
                        ,   l_ReceiptOrderDate                  -- ご注文日
                        ,   l_ServiceExpectedDate               -- 役務提供予定日
                        ,   l_DataStatus                        -- ｽﾃｰﾀｽ
                        ,   l_ProcessingDate                    -- 与信返信日時
                        ,   l_UseAmount                         -- 利用額
                        ,   l_StatusCaption                     -- 注文状態
                        ,   l_CombinedClaimTargetStatus         -- 取りまとめ対象注文ｽﾃｰﾀｽ
                        ,   l_CombinedClaimParentFlg            -- 取りまとめ後に親となったﾌﾗｸﾞ
                        ,   l_ParentOrderId                     -- 代表注文ID
                        ,   l_NameKj                            -- 請求先-氏名
                        ,   l_NameKn                            -- 請求先-氏名ｶﾅ
                        ,   l_PostalCode                        -- 請求先-郵便番号
                        ,   l_UnitingAddress                    -- 請求先-住所
                        ,   l_AddressKn                         -- 請求先-住所ｶﾅ
                        ,   l_EntCustId                         -- 請求先-加盟店顧客
                        ,   l_CustomerStatus                    -- 請求先-顧客ｽﾃｰﾀｽ
                        ,   l_Phone                             -- 請求先-電話番号
                        ,   l_Carrier                           -- 請求先-電話番号種類
                        ,   l_MailAddress                       -- 請求先-ﾒｰﾙｱﾄﾞﾚｽ
                        ,   l_Occupation                        -- 請求先-職業
                        ,   l_CorporateName                     -- 請求先-法人名
                        ,   l_DivisionName                      -- 請求先-部署名
                        ,   l_CpNameKj                          -- 請求先-担当者名
                        ,   l_CreditInfo                        -- 請求先-与信関連
                        ,   l_Chg_NonChargeFlg                  -- 立替処理－立替対象外ﾌﾗｸﾞ
                        ,   l_AnotherDeliFlg                    -- 別配送先ﾌﾗｸﾞ
                        ,   l_Deli_DestNameKj                   -- 配送先-氏名
                        ,   l_Deli_DestNameKn                   -- 配送先-氏名ｶﾅ
                        ,   l_Deli_PostalCode                   -- 配送先-郵便番号
                        ,   l_Deli_UnitingAddress               -- 配送先-住所
                        ,   l_Deli_Phone                        -- 配送先-電話番号
                        ,   l_OemNameKj                         -- 事業者情報-OEM先名
                        ,   l_EnterpriseNameKj                  -- 事業者情報-事業者名
                        ,   l_SiteNameKj                        -- 事業者情報-購入ｻｲﾄ
                        ,   l_EntCpNameKj                       -- 事業者情報-担当者名
                        ,   l_ContactPhoneNumber                -- 事業者情報-電話番号
                        ,   l_OrderItemInfo                     -- 商品明細情報(JSON形式で保存)
                        ,   l_DeliveryFee                       -- 送料
                        ,   l_SettlementFee                     -- 手数料
                        ,   l_TaxClass                          -- 外税額
                        ,   l_SubTotal                          -- 利用額合計
                        ,   l_ReclaimFee                        -- 再請求追加手数料(Clm_L_DamageInterestAmount + Clm_L_ClaimFee)
                        ,   l_CombinedAmount                    -- 他取りまとめ金額
                        ,   l_ReceiptAmountTotal                -- 入金済額
                        ,   l_ClaimAmount                       -- 請求合計(利用額合計 + 再請求追加手数料 - 入金済額)
                        ,   l_Incre_Note                        -- 備考
                        ,   l_JudgeSystemResult                 -- 審査ｼｽﾃﾑ結果
                        ,   l_PastDays                          -- 不払い管理-超過日数(Clm_F_LimitDateから経過している日数)
                        ,   l_RemindClass                       -- 不払い管理-督促分類
                        ,   l_RemindStopFlg                     -- 不払い管理-督促除外
                        ,   l_FinalityRemindDate                -- 不払い管理-最終督促日
                        ,   l_FinalityRemindOpId                -- 不払い管理-最終督促者
                        ,   l_PromPayDate                       -- 不払い管理-支払約束日
                        ,   l_ResidentCard                      -- 不払い管理-住民票
                        ,   l_LonghandLetter                    -- 不払い管理-手書き手紙
                        ,   l_BriefNote                         -- 不払い管理-簡易備考
                        ,   l_TouchHistoryFlg                   -- 不払い管理-ﾀｯﾁ履歴
                        ,   l_VisitFlg                          -- 不払い管理-訪問済
                        ,   l_ReceiptPastDays                   -- 不払い管理-入金遅れ日数(Clm_F_LimitDateとRct_ReceiptDateの差)
                        ,   l_FinalityCollectionMean            -- 不払い管理-最終改修手段
                        ,   l_ClaimStopReleaseDate              -- 不払い管理-請求ｽﾄｯﾌﾟ解除日
                        ,   l_LetterClaimStopFlg                -- 不払い管理-請求ｽﾄｯﾌﾟ紙
                        ,   l_MailClaimStopFlg                  -- 不払い管理-請求ｽﾄｯﾌﾟﾒｰﾙ
                        ,   l_Tel30DaysFlg                      -- 不払い管理-荷電30日ﾌﾗｸﾞ
                        ,   l_Tel90DaysFlg                      -- 不払い管理-荷電90日ﾌﾗｸﾞ
                        ,   l_Cinfo1                            -- 不払い管理-追加連絡先TEL1
                        ,   l_CinfoNote1                        -- 不払い管理-追加連絡先備考1
                        ,   l_CinfoStatus1                      -- 不払い管理-追加連絡先状態1
                        ,   l_Cinfo2                            -- 不払い管理-追加連絡先TEL2
                        ,   l_CinfoNote2                        -- 不払い管理-追加連絡先備考2
                        ,   l_CinfoStatus2                      -- 不払い管理-追加連絡先状態2
                        ,   l_Cinfo3                            -- 不払い管理-追加連絡先TEL3
                        ,   l_CinfoNote3                        -- 不払い管理-追加連絡先備考3
                        ,   l_CinfoStatus3                      -- 不払い管理-追加連絡先状態3
                        ,   l_OemClaimTransDate                 -- 不払い管理-OEM債権移管日
                        ,   l_Dmg_DecisionDate                  -- 不払い管理-貸倒確定日
                        ,   l_Oem_Note                          -- OEM先備考
                        ,   l_IncreArClass                      -- 与信-ｸﾗｽ(Incre_ArTel､Incre_ArAddrの値が大きい方を設定する)
                        ,   l_Incre_ScoreTotal                  -- 与信-与信点数-ｽｺｱ合計
                        ,   l_TotalScore                        -- 与信-与信点数-ｽｺｱﾘﾝｸﾞ結果
                        ,   l_Incre_Status                      -- 与信-社内与信
                        ,   l_Dmi_Status                        -- 与信-DMIｽﾃｰﾀｽ
                        ,   l_Dmi_DecisionDate                  -- 与信-与信日
                        ,   l_Dmi_ResponseNote                  -- 与信-与信理由
                        ,   l_Deli_JournalIncDate               -- 伝票番号-登録日
                        ,   l_Deli_DeliveryMethod               -- 伝票番号-配送方法
                        ,   l_Deli_JournalNumber                -- 伝票番号-伝票番号
                        ,   l_F_ClaimDate                       -- 請求-初回請求日
                        ,   l_F_LimitDate                       -- 請求-初回請求期限
                        ,   l_ClaimDate                         -- 請求-請求日
                        ,   l_LimitDate                         -- 請求-請求期限
                        ,   l_Deli_ConfirmArrivalDate           -- 着荷確認-確認日
                        ,   l_Deli_ConfirmArrivalFlg            -- 着荷確認-確認結果
                        ,   l_ClearConditionForCharge           -- 着荷確認-立替ｸﾘｱ-ﾌﾗｸﾞ
                        ,   l_ClearConditionDate                -- 着荷確認-立替ｸﾘｱ-日付
                        ,   l_Chg_FixedDate                     -- 着荷確認-立替締め日
                        ,   l_ExecScheduleDate                  -- 着荷確認-立替予定日
                        ,   l_ReceiptDate                       -- 入金確認-入金日
                        ,   l_ReceiptProcessDate                -- 入金確認-処理日
                        ,   l_ReceiptClass                      -- 入金確認-入金方法
                        ,   l_ClaimedBalance                    -- 入金確認-金額差異
                        ,   l_StampFee                          -- 入金確認-印紙代
                        ,   l_DepositDate                       -- 入金確認-CB入金日
                        ,   l_MailInfo                          -- ﾒｰﾙ履歴(JSON形式で保存)
                        ,   l_ReClaimInfo                       -- 再請求(JSON形式で保存)
                        ,   l_CancelDate                        -- ｷｬﾝｾﾙ処理-ｷｬﾝｾﾙ日
                        ,   l_CancelReasonCode                  -- ｷｬﾝｾﾙ処理-ｷｬﾝｾﾙ理由
                        ,   l_MailPaymentSoonDate               -- ﾒｰﾙ送信-直前ﾒｰﾙ
                        ,   l_MailLimitPassageCount             -- ﾒｰﾙ送信-未確認ﾒｰﾙ-件数
                        ,   l_MailLimitPassageDate              -- ﾒｰﾙ送信-未確認ﾒｰﾙ-日付
                        ,   l_Ent_Note                          -- 事業者備考
                        ,   l_P_OrderId                         -- 取りまとめ元注文SEQ
                        ,   l_CombinedOrderSeq                  -- 取りまとめ先注文SEQ(ｶﾝﾏ区切りで保存)
                        ,   l_Reserve                           -- 予約項目
                        ,   NOW()                               -- 登録日時
                        ,   pi_user_id                          -- 登録者
                          );
END
$$

DELIMITER ;

