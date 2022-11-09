DROP PROCEDURE IF EXISTS P_ClaimControl;

DELIMITER $$

CREATE PROCEDURE P_ClaimControl(    IN  pi_history_seq      BIGINT(20)
                                ,   IN  pi_button_flg       INT(1)
                                ,   IN  pi_user_id          INT
                                ,   OUT po_ret_sts          INT
                                ,   OUT po_ret_errcd        VARCHAR(100)
                                ,   OUT po_ret_sqlcd        INT
                                ,   OUT po_ret_msg          VARCHAR(255)
                               )
proc:
/*******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名： P_ClaimControl
 *
 * 概要      ： 請求関連処理
 *
 * 引数      ： [I/ ]pi_history_seq                 請求履歴Seq
 *           ： [I/ ]pi_button_flg                  ｲﾝﾌﾟｯﾄﾎﾞﾀﾝﾌﾗｸﾞ（1：印刷済みに更新／2：CSV／3：印刷）
 *           ： [I/ ]pi_user_id                     ﾕｰｻﾞｰID
 *           ： [ /O]po_ret_sts                     ﾘﾀｰﾝｽﾃｰﾀｽ
 *           ： [ /O]po_ret_errcd                   ﾘﾀｰﾝｺｰﾄﾞ
 *           ： [ /O]po_ret_sqlcd                   ﾘﾀｰﾝSQLｺｰﾄﾞ
 *           ： [ /O]po_ret_msg                     ﾘﾀｰﾝﾒｯｾｰｼﾞ
 *
 * 履歴      ： 2015/05/11  NDC 新規作成
 *           ： 2015/08/07  NDC 当SPは、「印刷済みに更新」したときの処理に限定する
 *
 ******************************************************************************/
BEGIN
    -- --------------------
    -- 変数宣言
    -- --------------------
    -- 請求履歴ﾃﾞｰﾀ取得用
    DECLARE v_OrderSeq                  BIGINT;             -- 注文Seq
    DECLARE v_ClaimSeq                  INT;                -- 請求Seq（請求ごとの通番）
    DECLARE v_ClaimDate                 DATE;               -- 請求日
    DECLARE v_ClaimCpId                 INT;                -- 請求担当者
    DECLARE v_ClaimPattern              INT;                -- 請求ﾊﾟﾀｰﾝ
    DECLARE v_LimitDate                 DATE;               -- 支払期限
    DECLARE v_DamageDays                INT;                -- 遅延日数
    DECLARE v_DamageBaseDate            DATE;               -- 遅延日数算出基準日
    DECLARE v_DamageInterestAmount      BIGINT;             -- 遅延損害金
    DECLARE v_ClaimFee                  BIGINT;             -- 請求手数料
    DECLARE v_AdditionalClaimFee        BIGINT;             -- 請求追加手数料
    DECLARE v_PrintedFlg                INT;                -- 印刷－処理ﾌﾗｸﾞ
    DECLARE v_PrintedDate               DATETIME;           -- 印刷－印刷日時
    DECLARE v_MailFlg                   INT;                -- 請求書発行通知ﾒｰﾙ
    DECLARE v_EnterpriseBillingCode     VARCHAR(255);       -- 同梱ﾂｰﾙｱｸｾｽｷｰ
    DECLARE v_ClaimAmount               BIGINT;             -- 請求金額

    -- 更新対象ﾃﾞｰﾀ取得用
    DECLARE v_OrderId                   VARCHAR(50);        -- 注文ID
    DECLARE v_OemId                     BIGINT;             -- OEMID
    DECLARE v_AutoClaimStopFlg          TINYINT;            -- 請求自動ｽﾄｯﾌﾟ
    DECLARE v_ClaimId                   BIGINT;             -- 請求ID
    DECLARE v_EntCustSeq                BIGINT;             -- 加盟店顧客Seq
    DECLARE v_OutOfAmends               INT;                -- 補償外案件
    DECLARE v_Re1_ClaimAmount           BIGINT DEFAULT 0;   -- 再１請求金額
    DECLARE v_Re3_ClaimAmount           BIGINT DEFAULT 0;   -- 再３請求金額
    DECLARE v_DataStatus                INT;                -- データステータス
    DECLARE v_SelfBillingFlg            INT;                -- 同梱フラグ
    DECLARE v_SelfBillingMode           INT;                -- 同梱モード

    -- 最低請求情報取得用
    DECLARE v_MinClaimPattern           INT;                -- 請求ﾊﾟﾀｰﾝ
    DECLARE v_Seq                       BIGINT;             -- 履歴Seq
    DECLARE v_DamageInterestAmountHis   BIGINT;             -- 遅延損害金
    DECLARE v_ClaimFeeHis               BIGINT;             -- 請求手数料
    DECLARE v_ClaimAmountHis            BIGINT;             -- 請求金額
    DECLARE v_AdditionalClaimFeeHis     BIGINT;             -- 請求追加手数料
    
    -- 請求管理
    DECLARE v_MypageReissueClass        INT;                -- マイページ請求書再発行区分
    DECLARE v_MypageReissueDate         DATETIME;           -- マイページ請求書再発行日
    DECLARE v_ReceiptAmountTotal        BIGINT;             -- 入金額合計
    DECLARE v_RepayAmountTotal          BIGINT;             -- 返金額合計
    DECLARE v_SundryLossTotal           BIGINT;             -- 雑損失合計
    DECLARE v_SundryIncomeTotal         BIGINT;             -- 雑収入合計
    DECLARE v_CheckingClaimAmount       BIGINT;             -- 消込情報－消込金額合計
    DECLARE v_CheckingUseAmount         BIGINT;             -- 消込情報－利用額
    DECLARE v_CheckingClaimFee          BIGINT;             -- 消込情報－請求手数料
    DECLARE v_CheckingDamageInterestAmount BIGINT;          -- 消込情報－遅延損害金
    DECLARE v_CheckingAdditionalClaimFee BIGINT;            -- 消込情報－請求追加手数料
    DECLARE v_UseAmountTotal            BIGINT;             -- 利用額合計
    DECLARE v_MinClaimAmount            BIGINT;             -- 最低請求情報－請求金額
    DECLARE v_MinClaimFee               BIGINT;             -- 最低請求情報－請求手数料
    DECLARE v_MinDamageInterestAmount   BIGINT;             -- 最低請求情報－遅延損害金
    DECLARE v_MinAdditionalClaimFee     BIGINT;             -- 最低請求情報－請求追加手数料
    DECLARE v_MinUseAmount              BIGINT;             -- 最低請求情報－利用額
    DECLARE v_BalanceClaimAmount        BIGINT;             -- 残高情報－残高合計
    DECLARE v_BalanceUseAmount          BIGINT;             -- 残高情報－利用額
    DECLARE v_BalanceClaimFee           BIGINT;             -- 残高情報－請求手数料
    DECLARE v_BalanceDamageInterestAmount BIGINT;           -- 残高情報－遅延損害金
    DECLARE v_BalanceAdditionalClaimFee BIGINT;             -- 残高情報－請求追加手数料
    DECLARE v_ClaimedBalance            BIGINT;             -- 請求残高
    
    -- 印刷指示
    DECLARE v_CntReclaimIndicate        BIGINT DEFAULT 0;             -- 印刷指示件数
    
    -- その他
    DECLARE v_cnt                       INT;                -- 請求書発行待ち件数
    DECLARE no_data_found INT DEFAULT 1;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_data_found = 0;

    /* ******************* *
     * 処理開始
     * ******************* */
    -- ------------------------------
    -- 1.戻り値初期化
    -- ------------------------------
    SET po_ret_sts      =   0;
    SET po_ret_errcd    =   '';
    SET po_ret_sqlcd    =   0;
    SET po_ret_msg      =   '正常終了';

    -- ------------------------------
    -- 2.請求履歴ﾃﾞｰﾀの取得
    -- ------------------------------
    SELECT  OrderSeq                    -- 注文Seq
        ,   ClaimSeq                    -- 請求Seq
        ,   ClaimDate                   -- 請求日
        ,   ClaimCpId                   -- 請求担当者
        ,   ClaimPattern                -- 請求ﾊﾟﾀｰﾝ
        ,   LimitDate                   -- 支払期限
        ,   DamageDays                  -- 遅延日数
        ,   DamageBaseDate              -- 遅延日数算出基準日
        ,   DamageInterestAmount        -- 遅延損害金
        ,   ClaimFee                    -- 請求手数料
        ,   AdditionalClaimFee          -- 請求追加手数料
        ,   PrintedFlg                  -- 印刷－処理ﾌﾗｸﾞ
        ,   PrintedDate                 -- 印刷－印刷日時
        ,   MailFlg                     -- 請求書発行通知ﾒｰﾙ
        ,   EnterpriseBillingCode       -- 同梱ﾂｰﾙｱｸｾｽｷｰ
        ,   ClaimAmount                 -- 請求金額
    INTO    v_OrderSeq
        ,   v_ClaimSeq
        ,   v_ClaimDate
        ,   v_ClaimCpId
        ,   v_ClaimPattern
        ,   v_LimitDate
        ,   v_DamageDays
        ,   v_DamageBaseDate
        ,   v_DamageInterestAmount
        ,   v_ClaimFee
        ,   v_AdditionalClaimFee
        ,   v_PrintedFlg
        ,   v_PrintedDate
        ,   v_MailFlg
        ,   v_EnterpriseBillingCode
        ,   v_ClaimAmount
    FROM    T_ClaimHistory
    WHERE   ValidFlg    =   1
    AND     Seq         =   pi_history_seq
    ;

    IF  no_data_found = 0   THEN
        SET po_ret_sts  =   -1;
        SET po_ret_msg  =   '請求履歴が存在しません。';
        LEAVE proc;
    END IF;

    -- ------------------------------
    -- 3.初回請求の場合
    -- ------------------------------
    IF  v_ClaimPattern = 1  THEN
        -- ------------------------------
        -- 3-1.更新対象ﾃﾞｰﾀの取得
        -- ------------------------------
        -- 存在判定
        -- 取りまとめられた注文で1つでも請求書発行待ちがあればOK
        SELECT  COUNT(*)
        INTO    v_cnt
        FROM    T_Order o
        WHERE   EXISTS(SELECT * FROM T_Order t WHERE t.P_OrderSeq = o.OrderSeq AND t.Cnl_Status = 0)
        AND     o.OrderSeq = v_OrderSeq
        ;

        IF  v_cnt = 0   THEN
            SET po_ret_sts  =   -1;
            SET po_ret_msg  =   '請求書発行待ちのデータが存在しません。';
            LEAVE proc;
        END IF;

        -- 更新対象ﾃﾞｰﾀの取得
        -- 親注文のデータを取得する
        SELECT  o.OrderId               -- 注文ID
            ,   o.OemId                 -- OEMID
            ,   s.AutoClaimStopFlg      -- 請求自動ｽﾄｯﾌﾟ
            ,   c.EntCustSeq            -- 加盟店顧客Seq
            ,   o.DataStatus            -- データステータス
            ,   s.SelfBillingFlg        -- 同梱フラグ
            ,   e.SelfBillingMode       -- 同梱モード
        INTO    v_OrderId
            ,   v_OemId
            ,   v_AutoClaimStopFlg
            ,   v_EntCustSeq
            ,   v_DataStatus
            ,   v_SelfBillingFlg
            ,   v_SelfBillingMode
        FROM    T_Order o
                INNER JOIN T_Customer c ON (c.OrderSeq = o.OrderSeq)
                INNER JOIN T_Site s ON (s.SiteId = o.SiteId)
                INNER JOIN T_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
        WHERE   o.OrderSeq      =   v_OrderSeq
        ;

        -- ------------------------------
        -- 3-3-1.注文ﾃﾞｰﾀの更新
        -- ------------------------------
        UPDATE  T_Order
        SET     DataStatus          =   CASE WHEN DataStatus = 41        THEN 51 ELSE DataStatus END -- ﾃﾞｰﾀｽﾃｰﾀｽ（51：入金確認待ち）
            ,   LetterClaimStopFlg  =   CASE WHEN v_AutoClaimStopFlg = 1 THEN 1 ELSE 0 END      -- 紙請求ｽﾄｯﾌﾟﾌﾗｸﾞ
            ,   MailClaimStopFlg    =   CASE WHEN v_AutoClaimStopFlg = 1 THEN 1 ELSE 0 END      -- ﾒｰﾙ請求ｽﾄｯﾌﾟﾌﾗｸﾞ
            ,   ConfirmWaitingFlg   =   0                                                       -- 確定待ちﾌﾗｸﾞ
            ,   ClaimSendingClass   =   CASE WHEN v_EnterpriseBillingCode IS NOT NULL THEN 11   -- 同梱であることが確定
                                             WHEN v_EnterpriseBillingCode IS NULL AND v_SelfBillingMode > 0 AND v_SelfBillingFlg = 1 THEN 12  -- 同梱（別送に送る）であることが確定
                                             ELSE 21 -- 別送であることが確定
                                        END
            ,   UpdateDate          =   NOW()                                                   -- 更新日時
            ,   UpdateId            =   pi_user_id                                              -- 更新者
        WHERE   P_OrderSeq          =   v_OrderSeq
        AND     Cnl_Status          =   0
        ;

        -- ------------------------------
        -- 3-3-2.請求ﾃﾞｰﾀの作成
        -- ------------------------------
        SELECT COUNT(*)
        INTO   v_cnt
        FROM   T_ClaimControl
        WHERE  OrderSeq = v_OrderSeq;
        
        IF v_cnt = 0 THEN
            
            -- 請求データがない＝初回の場合
            
            INSERT
            INTO    T_ClaimControl( OrderSeq                        -- 注文Seq
                                ,   EntCustSeq                      -- 加盟店顧客Seq
                                ,   ClaimDate                       -- 請求日
                                ,   ClaimCpId                       -- 請求担当者
                                ,   ClaimPattern                    -- 請求ﾊﾟﾀｰﾝ
                                ,   LimitDate                       -- 支払期限
                                ,   UseAmountTotal                  -- 利用額合計
                                ,   DamageDays                      -- 遅延日数
                                ,   DamageBaseDate                  -- 遅延日数算出基準日
                                ,   DamageInterestAmount            -- 遅延損害金
                                ,   ClaimFee                        -- 請求手数料
                                ,   AdditionalClaimFee              -- 請求追加手数料
                                ,   PrintedDate                     -- 印刷-印刷日時
                                ,   ClaimAmount                     -- 請求金額
                                ,   ReceiptAmountTotal              -- 入金額合計
                                ,   RepayAmountTotal                -- 返金額合計
                                ,   SundryLossTotal                 -- 雑損失合計
                                ,   SundryIncomeTotal               -- 雑収入合計
                                ,   ClaimedBalance                  -- 請求残高
                                ,   SundryLossTarget                -- 雑損失対象区分
                                ,   SundryIncomeTarget              -- 雑収入対象区分
                                ,   Clm_Count                       -- 請求回数（初回含む）
                                ,   F_ClaimDate                     -- 初回－請求日
                                ,   F_OpId                          -- 初回－請求担当者
                                ,   F_LimitDate                     -- 初回－支払期限
                                ,   F_ClaimAmount                   -- 初回請求金額
                                ,   Re1_ClaimAmount                 -- 再１請求金額
                                ,   Re3_ClaimAmount                 -- 再３請求金額
                                ,   AutoSundryStatus                -- 自動雑損失計上ｽﾃｰﾀｽ
                                ,   ReissueClass                    -- 初回請求再発行区分
                                ,   ReissueRequestDate              -- 初回請求書再発行申請日
                                ,   LastProcessDate                 -- 最終入金処理日
                                ,   LastReceiptSeq                  -- 最終入金SEQ
                                ,   MinClaimAmount                  -- 最低請求情報－請求金額
                                ,   MinUseAmount                    -- 最低請求情報－利用額
                                ,   MinClaimFee                     -- 最低請求情報－請求手数料
                                ,   MinDamageInterestAmount         -- 最低請求情報－遅延損害金
                                ,   MinAdditionalClaimFee           -- 最低請求情報－請求追加手数料
                                ,   CheckingClaimAmount             -- 消込情報－消込金額合計
                                ,   CheckingUseAmount               -- 消込情報－利用額
                                ,   CheckingClaimFee                -- 消込情報－請求手数料
                                ,   CheckingDamageInterestAmount    -- 消込情報－遅延損害金
                                ,   CheckingAdditionalClaimFee      -- 消込情報－請求追加手数料
                                ,   BalanceClaimAmount              -- 残高情報－残高合計
                                ,   BalanceUseAmount                -- 残高情報－利用額
                                ,   BalanceClaimFee                 -- 残高情報－請求手数料
                                ,   BalanceDamageInterestAmount     -- 残高情報－遅延損害金
                                ,   BalanceAdditionalClaimFee       -- 残高情報－請求追加手数料
                                ,   RegistDate                      -- 登録日時
                                ,   RegistId                        -- 登録者
                                ,   UpdateDate                      -- 更新日時
                                ,   UpdateId                        -- 更新者
                                ,   ValidFlg                        -- 有効ﾌﾗｸﾞ
                                  )
                                VALUES
                                  ( v_OrderSeq                      -- 注文Seq
                                ,   v_EntCustSeq                    -- 加盟店顧客Seq
                                ,   v_ClaimDate                     -- 請求日
                                ,   v_ClaimCpId                     -- 請求担当者
                                ,   v_ClaimPattern                  -- 請求ﾊﾟﾀｰﾝ（初回請求）
                                ,   v_LimitDate                     -- 支払期限
                                ,   v_ClaimAmount                   -- 利用額合計
                                ,   v_DamageDays                    -- 遅延日数
                                ,   NULL                            -- 遅延日数算出基準日
                                ,   v_DamageInterestAmount          -- 遅延損害金
                                ,   v_ClaimFee                      -- 請求手数料
                                ,   v_AdditionalClaimFee            -- 請求追加手数料
                                ,   NOW()                           -- 印刷-印刷日時
                                ,   v_ClaimAmount                   -- 請求金額
                                ,   0                               -- 入金額合計
                                ,   0                               -- 返金額合計
                                ,   0                               -- 雑損失合計
                                ,   0                               -- 雑収入合計
                                ,   v_ClaimAmount                   -- 請求残高
                                ,   0                               -- 雑損失対象区分
                                ,   0                               -- 雑収入対象区分
                                ,   1                               -- 請求回数（初回含む）
                                ,   v_ClaimDate                     -- 初回－請求日
                                ,   v_ClaimCpId                     -- 初回－請求担当者
                                ,   v_LimitDate                     -- 初回－支払期限
                                ,   v_ClaimAmount                   -- 初回請求金額
                                ,   0                               -- 再１請求金額
                                ,   0                               -- 再３請求金額
                                ,   0                               -- 自動雑損失計上ｽﾃｰﾀｽ
                                ,   0                               -- 初回請求再発行区分
                                ,   NULL                            -- 初回請求書再発行申請日
                                ,   NULL                            -- 最終入金処理日
                                ,   0                               -- 最終入金SEQ
                                ,   v_ClaimAmount                   -- 最低請求情報－請求金額
                                ,   v_ClaimAmount                   -- 最低請求情報－利用額
                                ,   v_ClaimFee                      -- 最低請求情報－請求手数料
                                ,   v_DamageInterestAmount          -- 最低請求情報－遅延損害金
                                ,   v_AdditionalClaimFee            -- 最低請求情報－請求追加手数料
                                ,   0                               -- 消込情報－消込金額合計
                                ,   0                               -- 消込情報－利用額
                                ,   0                               -- 消込情報－請求手数料
                                ,   0                               -- 消込情報－遅延損害金
                                ,   0                               -- 消込情報－請求追加手数料
                                ,   v_ClaimAmount                   -- 残高情報－残高合計
                                ,   v_ClaimAmount                   -- 残高情報－利用額
                                ,   v_ClaimFee                      -- 残高情報－請求手数料
                                ,   v_DamageInterestAmount          -- 残高情報－遅延損害金
                                ,   v_AdditionalClaimFee            -- 残高情報－請求追加手数料
                                ,   NOW()                           -- 登録日時
                                ,   pi_user_id                      -- 登録者
                                ,   NOW()                           -- 更新日時
                                ,   pi_user_id                      -- 更新者
                                ,   1                               -- 有効ﾌﾗｸﾞ
                                );
        ELSE
            
            -- 請求データがある＝再発行の場合
            UPDATE  T_ClaimControl
            SET     ClaimDate                       = v_ClaimDate               -- 請求日
            ,       ClaimCpId                       = v_ClaimCpId               -- 請求担当者
            ,       ClaimPattern                    = v_ClaimPattern            -- 請求ﾊﾟﾀｰﾝ
            ,       LimitDate                       = v_LimitDate               -- 支払期限
            ,       UseAmountTotal                  = v_ClaimAmount             -- 利用額合計
            ,       DamageDays                      = v_DamageDays              -- 遅延日数
            ,       DamageBaseDate                  = NULL                      -- 遅延日数算出基準日
            ,       DamageInterestAmount            = v_DamageInterestAmount    -- 遅延損害金
            ,       ClaimFee                        = v_ClaimFee                -- 請求手数料
            ,       AdditionalClaimFee              = v_AdditionalClaimFee      -- 請求追加手数料
            ,       PrintedDate                     = NOW()                     -- 印刷-印刷日時
            ,       ClaimAmount                     = v_ClaimAmount             -- 請求金額
            ,       ReceiptAmountTotal              = 0                         -- 入金額合計
            ,       RepayAmountTotal                = 0                         -- 返金額合計
            ,       SundryLossTotal                 = 0                         -- 雑損失合計
            ,       SundryIncomeTotal               = 0                         -- 雑収入合計
            ,       ClaimedBalance                  = v_ClaimAmount             -- 請求残高
            ,       SundryLossTarget                = 0                         -- 雑損失対象区分
            ,       SundryIncomeTarget              = 0                         -- 雑収入対象区分
            ,       Clm_Count                       = 1                         -- 請求回数（初回含む）
            ,       F_ClaimDate                     = v_ClaimDate               -- 初回－請求日
            ,       F_OpId                          = v_ClaimCpId               -- 初回－請求担当者
            ,       F_LimitDate                     = v_LimitDate               -- 初回－支払期限
            ,       F_ClaimAmount                   = v_ClaimAmount             -- 初回請求金額
            ,       Re1_ClaimAmount                 = 0                         -- 再１請求金額
            ,       Re3_ClaimAmount                 = 0                         -- 再３請求金額
            ,       AutoSundryStatus                = 0                         -- 自動雑損失計上ｽﾃｰﾀｽ
            ,       ReissueClass                    = 0                         -- 初回請求再発行区分
            -- ,       ReissueRequestDate              = -- 初回請求書再発行申請日
            ,       LastProcessDate                 = NULL                      -- 最終入金処理日
            ,       LastReceiptSeq                  = 0                         -- 最終入金SEQ
            ,       MinClaimAmount                  = v_ClaimAmount             -- 最低請求情報－請求金額
            ,       MinUseAmount                    = v_ClaimAmount             -- 最低請求情報－利用額
            ,       MinClaimFee                     = v_ClaimFee                -- 最低請求情報－請求手数料
            ,       MinDamageInterestAmount         = v_DamageInterestAmount    -- 最低請求情報－遅延損害金
            ,       MinAdditionalClaimFee           = v_AdditionalClaimFee      -- 最低請求情報－請求追加手数料
            ,       CheckingClaimAmount             = 0                         -- 消込情報－消込金額合計
            ,       CheckingUseAmount               = 0                         -- 消込情報－利用額
            ,       CheckingClaimFee                = 0                         -- 消込情報－請求手数料
            ,       CheckingDamageInterestAmount    = 0                         -- 消込情報－遅延損害金
            ,       CheckingAdditionalClaimFee      = 0                         -- 消込情報－請求追加手数料
            ,       BalanceClaimAmount              = v_ClaimAmount             -- 残高情報－残高合計
            ,       BalanceUseAmount                = v_ClaimAmount             -- 残高情報－利用額
            ,       BalanceClaimFee                 = v_ClaimFee                -- 残高情報－請求手数料
            ,       BalanceDamageInterestAmount     = v_DamageInterestAmount    -- 残高情報－遅延損害金
            ,       BalanceAdditionalClaimFee       = v_AdditionalClaimFee      -- 残高情報－請求追加手数料
            ,       UpdateDate                      = NOW()                     -- 更新日時
            ,       UpdateId                        = pi_user_id                -- 更新者
            WHERE   OrderSeq = v_OrderSeq;
            
        END IF;
        
        -- ------------------------------
        -- 3-3-3.請求履歴ﾃﾞｰﾀの更新
        -- ------------------------------
        -- 請求IDを取得
        SELECT  ClaimId
        INTO    v_ClaimId
        FROM    T_ClaimControl
        WHERE   OrderSeq    =   v_OrderSeq
        ;
        
        UPDATE  T_ClaimHistory
        SET     PrintedFlg  =   1                   -- 印刷済みﾌﾗｸﾞ
            ,   PrintedDate =   NOW()               -- 印刷日時
            ,   ClaimId     =   v_ClaimId           -- 請求ID
            ,   UpdateDate  =   NOW()               -- 更新日時
            ,   UpdateId    =   pi_user_id          -- 更新者
        WHERE   Seq         =   pi_history_seq
        ;
    -- ------------------------------
    -- 4.再請求の場合
    -- ------------------------------
    ELSE

        -- ------------------------------
        -- 4-3-1.再１、再３の場合
        -- ------------------------------
        IF  v_ClaimPattern = 2 OR v_ClaimPattern = 4    THEN
            -- 最低請求情報は初回請求
            SET v_MinClaimPattern = 1;
        -- ------------------------------
        -- 4-3-2.再４の場合
        -- ------------------------------
        ELSEIF  v_ClaimPattern = 6  THEN
            -- 最低請求情報は再１
            SET v_MinClaimPattern = 2;
        -- ------------------------------
        -- 4-3-3.再５、再６、再７の場合
        -- ------------------------------
        ELSE
            -- 最低請求情報は再３
            SET v_MinClaimPattern = 4;
        END IF;

        -- ------------------------------
        -- 印刷指示データの件数取得
        -- ------------------------------
        SELECT COUNT(1) 
        INTO   v_CntReclaimIndicate
        FROM   T_ReclaimIndicate 
        WHERE  ClaimHistorySeq = pi_history_seq;
        
        -- ------------------------------
        -- 4-3-6.最低請求情報取得
        -- ------------------------------
        -- 請求履歴から履歴Seqでｿｰﾄして最初の1件を取得する。
        SELECT  Seq
            ,   DamageInterestAmount
            ,   ClaimFee
            ,   ClaimAmount
            ,   AdditionalClaimFee
        INTO    v_Seq
            ,   v_DamageInterestAmountHis
            ,   v_ClaimFeeHis
            ,   v_ClaimAmountHis
            ,   v_AdditionalClaimFeeHis
        FROM    T_ClaimHistory
        WHERE   OrderSeq        =   v_OrderSeq
        AND     ClaimPattern    <=   v_MinClaimPattern
        AND     ValidFlg        =   1
        AND     (SELECT COUNT(1) FROM T_ReclaimIndicate WHERE ClaimHistorySeq = T_ClaimHistory.Seq) = 0
        ORDER BY
                ClaimPattern DESC, Seq DESC
        LIMIT 1
        ;
        
        -- 請求データ取得
        SELECT  MypageReissueClass
             ,  MypageReissueDate
             ,  Re1_ClaimAmount
             ,  Re3_ClaimAmount
             ,  ReceiptAmountTotal
             ,  RepayAmountTotal
             ,  SundryLossTotal
             ,  SundryIncomeTotal
             ,  CheckingClaimAmount
             ,  CheckingUseAmount
             ,  CheckingClaimFee
             ,  CheckingDamageInterestAmount
             ,  CheckingAdditionalClaimFee
             ,  ClaimId
             ,  MinClaimAmount
             ,  MinUseAmount
             ,  MinClaimFee
             ,  MinDamageInterestAmount
             ,  MinAdditionalClaimFee
          INTO  v_MypageReissueClass
             ,  v_MypageReissueDate
             ,  v_Re1_ClaimAmount
             ,  v_Re3_ClaimAmount
             ,  v_ReceiptAmountTotal     -- 入金額合計
             ,  v_RepayAmountTotal       -- 返金額合計
             ,  v_SundryLossTotal        -- 雑損失合計
             ,  v_SundryIncomeTotal      -- 雑収入合計
             ,  v_CheckingClaimAmount    -- 消込情報－消込金額合計
             ,  v_CheckingUseAmount      -- 消込情報－利用額
             ,  v_CheckingClaimFee       -- 消込情報－請求手数料
             ,  v_CheckingDamageInterestAmount -- 消込情報－遅延損害金
             ,  v_CheckingAdditionalClaimFee   -- 消込情報－請求追加手数料
             ,  v_ClaimId
             ,  v_MinClaimAmount         -- 最低請求情報－請求金額
             ,  v_MinUseAmount           -- 最低請求情報－利用額
             ,  v_MinClaimFee            -- 最低請求情報－請求手数料
             ,  v_MinDamageInterestAmount -- 最低請求情報－遅延損害金
             ,  v_MinAdditionalClaimFee  -- 最低請求情報－請求追加手数料
        FROM    T_ClaimControl
        WHERE   OrderSeq        =   v_OrderSeq
        ;

        -- ------------------------------
        -- 4-3-4.再１の場合
        -- ------------------------------
        IF  v_ClaimPattern = 2  THEN
            SET v_Re1_ClaimAmount   =   v_ClaimAmount;
        -- ------------------------------
        -- 4-3-5.再３の場合
        -- ------------------------------
        ELSEIF  v_ClaimPattern = 4  THEN
            SET v_Re3_ClaimAmount   =   v_ClaimAmount;
        END IF;
        
        -- ------------------------------
        -- 最終請求情報の調整
        -- ------------------------------
        SET v_UseAmountTotal = v_ClaimAmount - v_ClaimFee - v_DamageInterestAmount - v_AdditionalClaimFee; 
        
        -- ------------------------------
        -- 最低請求情報の調整
        -- 最低請求情報 > 最終請求情報の場合（取りまとめ一部キャンセル時に発動する可能性あり）、最低請求情報 ← 最終請求情報とする
        -- ------------------------------
        -- 印刷指示の場合においては最低請求額は上書きしない
        -- 但し、今回請求額 < 最低請求額の場合のみ上書きする
        IF v_CntReclaimIndicate = 0 THEN
            SET v_MinClaimAmount = v_ClaimAmountHis;
            SET v_MinClaimFee = v_ClaimFeeHis;
            SET v_MinDamageInterestAmount = v_DamageInterestAmountHis;
            SET v_MinAdditionalClaimFee = v_AdditionalClaimFeeHis;
        END IF;
        
        IF v_MinClaimAmount > v_ClaimAmount THEN
            -- 請求金額
            SET v_MinClaimAmount = v_ClaimAmount;
        END IF;
        IF v_MinClaimFee > v_ClaimFee THEN
            -- 請求手数料
            SET v_MinClaimFee = v_ClaimFee;
        END IF;
        IF v_MinDamageInterestAmount > v_DamageInterestAmount THEN
            -- 遅延損害金
            SET v_MinDamageInterestAmount = v_DamageInterestAmount;
        END IF;
        IF v_MinAdditionalClaimFee > v_AdditionalClaimFee THEN
            -- 請求追加手数料
            SET v_MinAdditionalClaimFee = v_AdditionalClaimFee;
        END IF;
        -- 最低利用額は逆算して算出（請求金額－請求手数料－遅延損害金－請求追加手数料）
        SET v_MinUseAmount = v_MinClaimAmount - v_MinClaimFee - v_MinDamageInterestAmount - v_MinAdditionalClaimFee;
        
        -- ------------------------------
        -- 残高情報の再計算
        -- ------------------------------
        -- 残高合計（請求金額－消込額合計）
        SET v_BalanceClaimAmount = v_ClaimAmount - v_CheckingClaimAmount;
        -- 利用額（利用額－消込利用額）
        SET v_BalanceUseAmount   = v_UseAmountTotal - v_CheckingUseAmount;
        -- 請求手数料（請求手数料－消込請求手数料）
        SET v_BalanceClaimFee    = v_ClaimFee - v_CheckingClaimFee;
        -- 遅延損害金（遅延損害金－消込遅延損害金）
        SET v_BalanceDamageInterestAmount = v_DamageInterestAmount - v_CheckingDamageInterestAmount;
        -- 請求追加手数料（請求追加手数料－消込請求追加手数料）
        SET v_BalanceAdditionalClaimFee   = v_AdditionalClaimFee - v_CheckingAdditionalClaimFee;
        
        -- 請求残の再計算＝請求金額－(入金額－返金額＋雑損失－雑収入)
        SET v_ClaimedBalance = v_ClaimAmount - (v_ReceiptAmountTotal - v_RepayAmountTotal + v_SundryLossTotal - v_SundryIncomeTotal);
        
        -- ------------------------------
        -- 4-3-7.請求ﾃﾞｰﾀ更新
        -- ------------------------------
        UPDATE  T_ClaimControl
        SET     Clm_Count                   =   Clm_Count + 1                           -- 請求回数（初回含む）
            ,   ClaimPattern                =   v_ClaimPattern                          -- 請求ﾊﾟﾀｰﾝ
            ,   ClaimDate                   =   v_ClaimDate                             -- 請求日
            ,   ClaimCpId                   =   v_ClaimCpId                             -- 請求担当者
            ,   LimitDate                   =   v_LimitDate                             -- 支払期限
            ,   UseAmountTotal              =   v_UseAmountTotal                        -- 利用額合計
            ,   DamageDays                  =   v_DamageDays                            -- 遅延日数
            -- ,   DamageBaseDate              =   v_DamageBaseDate                        -- 遅延日数算出基準日（初めて再発行したときのジョブ転送時に設定する→以降は初回請求書再発行をするまで再設定しない）
            ,   DamageInterestAmount        =   v_DamageInterestAmount                  -- 遅延損害金
            ,   ClaimFee                    =   v_ClaimFee                              -- 請求手数料
            ,   AdditionalClaimFee          =   v_AdditionalClaimFee                    -- 請求追加手数料
            ,   ClaimAmount                 =   v_ClaimAmount                           -- 請求金額（請求金額 + 遅延損害金 + 請求手数料）
            ,   PrintedDate                 =   NOW()                                   -- 印刷－印刷日時
            ,   ClaimedBalance              =   v_ClaimedBalance                        -- 請求残高
            ,   Re1_ClaimAmount             =   v_Re1_ClaimAmount                       -- 再１請求金額
            ,   Re3_ClaimAmount             =   v_Re3_ClaimAmount                       -- 再３請求金額
            ,   MinClaimAmount              =   v_MinClaimAmount                        -- 最低請求情報－請求金額
            ,   MinUseAmount                =   v_MinUseAmount                          -- 最低請求情報－利用額
            ,   MinClaimFee                 =   v_MinClaimFee                           -- 最低請求情報－請求手数料
            ,   MinDamageInterestAmount     =   v_MinDamageInterestAmount               -- 最低請求情報－遅延損害金
            ,   MinAdditionalClaimFee       =   v_MinAdditionalClaimFee                 -- 最低請求情報－請求追加手数料
            ,   BalanceClaimAmount          =   v_BalanceClaimAmount                    -- 残高情報－残高合計（請求金額－消込金額合計）
            ,   BalanceUseAmount            =   v_BalanceUseAmount                      -- 残高情報－利用額（利用額－消込利用額）
            ,   BalanceClaimFee             =   v_BalanceClaimFee                       -- 残高情報－請求手数料（請求手数料－消込請求手数料）
            ,   BalanceDamageInterestAmount =   v_BalanceDamageInterestAmount           -- 残高情報－遅延損害金（遅延損害金－消込遅延損害金）
            ,   BalanceAdditionalClaimFee   =   v_BalanceAdditionalClaimFee             -- 残高情報－請求追加手数料（請求追加手数料－消込請求追加手数料）
            ,   MypageReissueClass          =   CASE WHEN v_MypageReissueClass = 1 THEN 91
                                                     WHEN v_MypageReissueClass = 2 THEN 92
                                                     ELSE v_MypageReissueClass
                                                END                                     -- マイページ請求書再発行区分
            ,   MypageReissueDate           =   CASE WHEN v_MypageReissueClass = 1 OR v_MypageReissueClass = 2 THEN NOW()
                                                     ELSE v_MypageReissueDate
                                                END                                     -- マイページ請求書再発行日
            ,   UpdateDate                  =   NOW()                                   -- 更新日時
            ,   UpdateId                    =   pi_user_id                              -- 更新者
        WHERE   ClaimId                     =   v_ClaimId
        ;

        -- ------------------------------
        -- 4-3-8.請求履歴更新
        -- ------------------------------
        UPDATE  T_ClaimHistory
        SET     PrintedFlg  =   1                   -- 印刷済みﾌﾗｸﾞ
            ,   PrintedDate =   NOW()               -- 印刷日時
            ,   UpdateDate  =   NOW()               -- 更新日時
            ,   UpdateId    =   pi_user_id          -- 更新者
        WHERE   Seq         =   pi_history_seq
        ;

        -- ------------------------------
        -- 4-3-9.注文ﾃﾞｰﾀ更新
        -- ------------------------------
        -- 注文ﾃﾞｰﾀの更新
        UPDATE  T_Order
        SET     ConfirmWaitingFlg   =   0
            ,   UpdateDate          =   NOW()
            ,   UpdateId            =   pi_user_id
        WHERE   P_OrderSeq          =   v_OrderSeq
        AND     Cnl_Status          =   0
        ;
    END IF;
END
$$

DELIMITER ;

