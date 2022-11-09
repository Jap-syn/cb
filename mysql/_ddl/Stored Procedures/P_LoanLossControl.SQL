DROP PROCEDURE IF EXISTS P_LoanLossControl;

DELIMITER $$

CREATE PROCEDURE P_LoanLossControl( IN  pi_order_seq    BIGINT
                                ,   IN  pi_sundry_class INT
                                ,   IN  pi_note         VARCHAR(4000)
                                ,   IN  pi_user_id      VARCHAR(20)
                                ,   OUT po_ret_sts      INT
                                ,   OUT po_ret_errcd    VARCHAR(100)
                                ,   OUT po_ret_sqlcd    INT
                                ,   OUT po_ret_msg      VARCHAR(255)
                                  )
proc:
/******************************************************************************
 *
 * プロシージャ名   ：  P_LoanLossControl
 *
 * 概要             ：  貸し倒れ関連処理
 *
 * 引数             ：  [I/ ]pi_order_seq                           注文Seq
 *                  ：  [I/ ]pi_sundry_class                        科目
 *                  ：  [I/ ]pi_note                                備考
 *                  ：  [I/ ]pi_user_id                             ﾕｰｻﾞｰID
 *                  ：  [ /O]po_ret_sts                             ﾘﾀｰﾝｽﾃｰﾀｽ
 *                  ：  [ /O]po_ret_errcd                           ﾘﾀｰﾝｺｰﾄﾞ
 *                  ：  [ /O]po_ret_sqlcd                           ﾘﾀｰﾝSQLｺｰﾄﾞ
 *                  ：  [ /O]po_ret_msg                             ﾘﾀｰﾝﾒｯｾｰｼﾞ
 *
 * 履歴             ：  2015/05/22  NDC 新規作成
 *
 *****************************************************************************/
BEGIN
    -- ---------------------
    -- 変数宣言
    -- ---------------------
    -- 注文ﾃﾞｰﾀ取得用
    DECLARE v_OrderId                       VARCHAR(50) DEFAULT '';
    DECLARE v_Cnt                           INT;
    DECLARE v_OemClaimTransDate             DATE;

    -- 請求ﾃﾞｰﾀ取得用
    DECLARE v_ClaimId                       BIGINT(20);
    DECLARE v_ClaimAmount                   BIGINT(20) DEFAULT 0;
    DECLARE v_ClaimedBalance                BIGINT(20) DEFAULT 0;
    DECLARE v_UseAmountTotal                BIGINT(20) DEFAULT 0;
    DECLARE v_ClaimFee                      BIGINT(20) DEFAULT 0;
    DECLARE v_DamageInterestAmount          BIGINT(20) DEFAULT 0;
    DECLARE v_AdditionalClaimFee            BIGINT(20) DEFAULT 0;
    DECLARE v_MinClaimAmount                BIGINT(20) DEFAULT 0;
    DECLARE v_MinUseAmount                  BIGINT(20) DEFAULT 0;
    DECLARE v_MinClaimFee                   BIGINT(20) DEFAULT 0;
    DECLARE v_MinDamageInterestAmount       BIGINT(20) DEFAULT 0;
    DECLARE v_MinAdditionalClaimFee         BIGINT(20) DEFAULT 0;

    -- 入金ﾃﾞｰﾀ取得用
    DECLARE v_ReceiptAmount                 BIGINT(20) DEFAULT 0;
    DECLARE v_ReceiptUseAmount              BIGINT(20) DEFAULT 0;
    DECLARE v_ReceiptClaimFee               BIGINT(20) DEFAULT 0;
    DECLARE v_ReceiptDamageInterestAmount   BIGINT(20) DEFAULT 0;
    DECLARE v_ReceiptAdditionalClaimFee     BIGINT(20) DEFAULT 0;

    -- 雑収入・雑損失更新用
    DECLARE v_SundryAmount                      BIGINT(20) DEFAULT 0;
    DECLARE v_SundryUseAmount                   BIGINT(20) DEFAULT 0;
    DECLARE v_SundryClaimFee                    BIGINT(20) DEFAULT 0;
    DECLARE v_SundryDamageInterestAmount        BIGINT(20) DEFAULT 0;
    DECLARE v_SundryAdditionalClaimFee          BIGINT(20) DEFAULT 0;

    -- 差額取得用
    DECLARE v_DiffAmountTotal               BIGINT(20) DEFAULT 0;
    DECLARE v_DiffClaimFee                  BIGINT(20) DEFAULT 0;
    DECLARE v_DiffDamageInterestAmount      BIGINT(20) DEFAULT 0;
    DECLARE v_DiffAdditionalClaimFee        BIGINT(20) DEFAULT 0;

    -- 貸し倒れ金額更新用
    DECLARE v_LoanLossClaimAmount           BIGINT(20) DEFAULT 0;
    DECLARE v_LoanLossUseAmount             BIGINT(20) DEFAULT 0;
    DECLARE v_LoanLossClaimFee              BIGINT(20) DEFAULT 0;
    DECLARE v_LoanLossDamageInterestAmount  BIGINT(20) DEFAULT 0;
    DECLARE v_LoanLossAdditionalClaimFee    BIGINT(20) DEFAULT 0;

    -- 残金取得用
    DECLARE v_CalculationAmount             BIGINT(20) DEFAULT 0;

    -- その他
    DECLARE no_data_found INT DEFAULT 1;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_data_found = 0;

    -- ------------------------------
    -- 1.戻り値初期化
    -- ------------------------------
    SET po_ret_sts      =   0;
    SET po_ret_errcd    =   '';
    SET po_ret_sqlcd    =   0;
    SET po_ret_msg      =   '正常終了';

    -- ------------------------------
    -- 2.注文ﾃﾞｰﾀ取得
    -- ------------------------------
    -- 取りまとめられている場合の考慮
    -- 1件でも有効な注文ﾃﾞｰﾀが存在すれば、入金可能
    SELECT  COUNT(*)
    INTO    v_Cnt
    FROM    T_Order
    WHERE   DataStatus IN (51, 61)
    AND     Cnl_Status  =   0
    AND     P_OrderSeq  =   pi_order_seq
    ;

    IF  v_Cnt = 0   THEN
        SET po_ret_sts  =   -1;
        SET po_ret_msg  =   '対象の注文データが存在しません。';
        LEAVE proc;
    END IF;

    -- 親の注文IDを取得
    -- 親注文がｷｬﾝｾﾙされている場合を考慮してDataStatus等の条件は含まない
    SELECT  OrderId
    INTO    v_OrderId
    FROM    T_Order
    WHERE   OrderSeq    =   P_OrderSeq
    AND     P_OrderSeq  =   pi_order_seq
    ;

    -- ------------------------------
    -- 3.請求ﾃﾞｰﾀ取得
    -- ------------------------------
    SELECT  ClaimId                         -- 請求ID
        ,   ClaimAmount                     -- 請求金額
        ,   UseAmountTotal                  -- 利用額合計
        ,   ClaimFee                        -- 請求手数料（正確には再請求手数料）
        ,   DamageInterestAmount            -- 遅延損害金
        ,   AdditionalClaimFee              -- 請求追加手数料
        ,   ClaimedBalance                  -- 請求残高
        ,   MinClaimAmount                  -- 最低請求情報－請求金額
        ,   MinUseAmount                    -- 最低請求情報－利用額
        ,   MinClaimFee                     -- 最低請求情報－請求手数料
        ,   MinDamageInterestAmount         -- 最低請求情報－遅延損害金
        ,   MinAdditionalClaimFee           -- 最低請求情報－請求追加手数料
    INTO    v_ClaimId
        ,   v_ClaimAmount
        ,   v_UseAmountTotal
        ,   v_ClaimFee
        ,   v_DamageInterestAmount
        ,   v_AdditionalClaimFee
        ,   v_ClaimedBalance
        ,   v_MinClaimAmount
        ,   v_MinUseAmount
        ,   v_MinClaimFee
        ,   v_MinDamageInterestAmount
        ,   v_MinAdditionalClaimFee
    FROM    T_ClaimControl
    WHERE   OrderSeq    =   pi_order_seq
    ;

    -- ﾃﾞｰﾀが存在しない場合
    IF  no_data_found = 0   THEN
        SET po_ret_sts  =   -1;
        SET po_ret_msg  =   '対象の請求データが存在しません。';
        LEAVE proc;
    END IF;

    -- ------------------------------
    -- 4.入金ﾃﾞｰﾀ取得
    -- ------------------------------
    SELECT  IFNULL(SUM(ReceiptAmount), 0)                   -- 金額
        ,   IFNULL(SUM(CheckingUseAmount), 0)               -- 消込情報－利用額
        ,   IFNULL(SUM(CheckingClaimFee), 0)                -- 消込情報－請求手数料
        ,   IFNULL(SUM(CheckingDamageInterestAmount), 0)    -- 消込情報－遅延損害金
        ,   IFNULL(SUM(CheckingAdditionalClaimFee), 0)      -- 消込情報－請求追加手数料
    INTO    v_ReceiptAmount
        ,   v_ReceiptUseAmount
        ,   v_ReceiptClaimFee
        ,   v_ReceiptDamageInterestAmount
        ,   v_ReceiptAdditionalClaimFee
    FROM    T_ReceiptControl
    WHERE   OrderSeq    =   pi_order_seq
    ;

    -- ------------------------------
    -- 5.雑損失ﾃﾞｰﾀ取得
    -- ------------------------------
    -- 雑損失は注文単位で出来るため、取りまとめの考慮をする。
    SELECT  IFNULL(SUM(SundryAmount), 0)                    -- 金額
        ,   IFNULL(SUM(CheckingUseAmount), 0)               -- 消込情報－利用額
        ,   IFNULL(SUM(CheckingClaimFee), 0)                -- 消込情報－請求手数料
        ,   IFNULL(SUM(CheckingDamageInterestAmount), 0)    -- 消込情報－遅延損害金
        ,   IFNULL(SUM(CheckingAdditionalClaimFee), 0)      -- 消込情報－請求追加手数料
    INTO    v_SundryAmount
        ,   v_SundryUseAmount
        ,   v_SundryClaimFee
        ,   v_SundryDamageInterestAmount
        ,   v_SundryAdditionalClaimFee
    FROM    T_SundryControl
    WHERE   OrderSeq    IN   (SELECT OrderSeq FROM T_Order WHERE P_OrderSeq = pi_order_seq)     -- 取りまとめの対応
    AND     SundryType  =   1           -- 雑損失
    AND     SundryClass <>  99          -- 会計対象外以外
    ;

    -- ------------------------------
    -- 6.貸し倒れ情報を算出
    -- ------------------------------
    -- ++++++++++++++++++++++++++++++++++++++++
    -- 1. 貸し倒れ金－請求追加手数料 を求める
    -- ++++++++++++++++++++++++++++++++++++++++
    -- 入金済みの請求追加手数料 + 雑損失計上済みの請求追加手数料 が 最低請求情報－請求追加手数料 以上場合
    IF  v_ReceiptAdditionalClaimFee + v_SundryAdditionalClaimFee >= v_MinAdditionalClaimFee THEN
        -- 貸し倒れ金額に対する 請求追加手数料の消し込みはなし
        SET v_LoanLossAdditionalClaimFee = 0;
        -- 残金 は 請求残高
        SET v_CalculationAmount = v_ClaimedBalance;
    -- それ以外
    ELSE
        -- 最低請求情報－請求追加手数料 から 入金済みの請求追加手数料と雑損失計上済みの請求追加手数料 を減算して 貸し倒れ金－請求追加手数料 を算出
        SET v_LoanLossAdditionalClaimFee = v_MinAdditionalClaimFee - (v_ReceiptAdditionalClaimFee + v_SundryAdditionalClaimFee);
        -- 請求残高 から 貸し倒れ金－請求追加手数料 を減算して残金を取得
        SET v_CalculationAmount = v_ClaimedBalance - v_LoanLossAdditionalClaimFee;
    END IF;

    -- 1.で残金が存在する場合、以下処理を行う
    IF  v_CalculationAmount > 0 THEN
        -- ++++++++++++++++++++++++++++++++++++++++
        -- 2. 貸し倒れ金－遅延損害金 を求める
        -- ++++++++++++++++++++++++++++++++++++++++
        -- 入金済みの遅延損害金 + 雑損失計上済みの遅延損害金 が 最低請求情報－遅延損害金 以上の場合
        IF  v_ReceiptDamageInterestAmount + v_SundryDamageInterestAmount >= v_MinDamageInterestAmount  THEN
            -- 貸し倒れ金額に対する 遅延損害金の消し込みはなし
            SET v_LoanLossDamageInterestAmount = 0;
            -- 残金は 1.の残金
            SET v_CalculationAmount = v_CalculationAmount;
        -- それ以外
        ELSE
            -- 最低請求情報－遅延損害金 から 入金済みの遅延損害金と雑損失計上済みの遅延損害金 を減算して 貸し倒れ金－遅延損害金 を算出
            SET v_LoanLossDamageInterestAmount = v_MinDamageInterestAmount - (v_ReceiptDamageInterestAmount + v_SundryDamageInterestAmount);
            -- 1.の残金 から 貸し倒れ金－遅延損害金 を減算して残金を取得
            SET v_CalculationAmount = v_CalculationAmount - v_LoanLossDamageInterestAmount;
        END IF;
    END IF;

    -- 2.で残金が存在する場合、以下処理を行う
    IF  v_CalculationAmount > 0 THEN
        -- ++++++++++++++++++++++++++++++++++++++++
        -- 3. 貸し倒れ金－請求手数料 を求める
        -- ++++++++++++++++++++++++++++++++++++++++
        -- 入金済みの請求手数料 + 雑損失計上済みの請求手数料 が 最低請求情報－請求手数料 以上の場合
        IF  v_ReceiptClaimFee + v_SundryClaimFee >= v_MinClaimFee  THEN
            -- 貸し倒れ金額に対する 請求手数料の消し込みはなし
            SET v_LoanLossClaimFee = 0;
            -- 残金は 2.の残金
            SET v_CalculationAmount = v_CalculationAmount;
        -- それ以外
        ELSE
            -- 最低請求情報－請求手数料 から 入金済みの請求手数料と雑損失計上済みの請求手数料 を減算して 貸し倒れ金－請求手数料 を算出
            SET v_LoanLossClaimFee = v_MinClaimFee - (v_ReceiptClaimFee + v_SundryClaimFee);
            -- 2.の残金 から 貸し倒れ金－請求手数料を減算して残金を取得
            SET v_CalculationAmount = v_CalculationAmount - v_LoanLossClaimFee;
        END IF;
    END IF;

    -- ++++++++++++++++++++++++++++++++++++++++
    -- 4. 貸し倒れ金－合計金額 を求める
    -- ++++++++++++++++++++++++++++++++++++++++
    -- 会計対象外雑損失ﾃﾞｰﾀ作成用に合計金額を取得
    SET v_LoanLossClaimAmount = v_LoanLossClaimFee + v_LoanLossDamageInterestAmount + v_LoanLossAdditionalClaimFee;

    -- ------------------------------
    -- 7.貸し倒れ金額取得
    -- ------------------------------
    -- 5.貸し倒れ金額算出 で残金が発生している場合、以下処理を行う
    IF  v_CalculationAmount > 0 THEN
        -- ++++++++++++++++++++++++++++++++++++++++
        -- 5. 貸し倒れ金額 を求める
        -- ++++++++++++++++++++++++++++++++++++++++
        -- 入金済みの利用額 + 雑損失計上済みの利用額 が 利用額合計 以上の場合
        IF  v_ReceiptUseAmount + v_SundryUseAmount >= v_UseAmountTotal  THEN
            -- 貸し倒れ金額に対する 利用額の消し込みはなし
            SET v_LoanLossUseAmount = 0;
        -- それ以外
        ELSE
            -- 利用額合計 から 入金済みの利用額と雑損失計上済みの利用額 を減算して 貸し倒れ金－利用額 を算出
            SET v_LoanLossUseAmount = v_UseAmountTotal - (v_ReceiptUseAmount + v_SundryUseAmount);
        END IF;
    END IF;

    -- ++++++++++++++++++++++++++++++++++++++++
    -- 6. 最終請求額との差額を取得
    -- ++++++++++++++++++++++++++++++++++++++++
    -- 最終請求額との差額を取得し、貸し倒れ情報に足しこむ
    -- 請求手数料
    SET v_LoanLossClaimFee = v_LoanLossClaimFee + (v_ClaimFee - v_MinClaimFee);
    -- 遅延損害金
    SET v_LoanLossDamageInterestAmount = v_LoanLossDamageInterestAmount + (v_DamageInterestAmount - v_MinDamageInterestAmount);
    -- 請求追加手数料
    SET v_LoanLossAdditionalClaimFee = v_LoanLossAdditionalClaimFee + (v_AdditionalClaimFee - v_MinAdditionalClaimFee);
    -- 合計
    SET v_LoanLossClaimAmount = v_LoanLossClaimFee + v_LoanLossDamageInterestAmount + v_LoanLossAdditionalClaimFee;

    -- ------------------------------
    -- 8.貸し倒れ損失ﾃﾞｰﾀの作成
    -- ------------------------------
    -- 会計対象の雑損失ﾃﾞｰﾀのため、手数料等は0（ｾﾞﾛ）で固定とする。
    INSERT
    INTO    T_SundryControl(    ProcessDate                     -- 発生日時
                            ,   SundryType                      -- 種類（雑収入／雑損失）
                            ,   SundryAmount                    -- 金額
                            ,   SundryClass                     -- 雑収入・雑損失科目
                            ,   OrderSeq                        -- 注文SEQ
                            ,   OrderId                         -- 注文ID
                            ,   ClaimId                         -- 請求ID
                            ,   Note                            -- 備考
                            ,   CheckingUseAmount               -- 消込情報－利用額
                            ,   CheckingClaimFee                -- 消込情報－請求手数料
                            ,   CheckingDamageInterestAmount    -- 消込情報－遅延損害金
                            ,   CheckingAdditionalClaimFee      -- 消込情報－請求追加手数料
                            ,   DailySummaryFlg                 -- 日次更新フラグ
                            ,   RegistDate                      -- 登録日時
                            ,   RegistId                        -- 登録者
                            ,   UpdateDate                      -- 更新日時
                            ,   UpdateId                        -- 更新者
                            ,   ValidFlg                        -- 有効フラグ
                           )
                           VALUES
                           (    DATE(NOW())                     -- 発生日時
                            ,   1                               -- 種類（雑収入／雑損失）
                            ,   v_LoanLossUseAmount             -- 金額
                            ,   pi_sundry_class                 -- 雑収入・雑損失科目
                            ,   pi_order_seq                    -- 注文SEQ
                            ,   v_OrderId                       -- 注文ID
                            ,   v_ClaimId                       -- 請求ID
                            ,   pi_note                         -- 備考
                            ,   v_LoanLossUseAmount             -- 消込情報－利用額
                            ,   0                               -- 消込情報－請求手数料
                            ,   0                               -- 消込情報－遅延損害金
                            ,   0                               -- 消込情報－請求追加手数料
                            ,   0                               -- 日次更新フラグ
                            ,   NOW()                           -- 登録日時
                            ,   pi_user_id                      -- 登録者
                            ,   NOW()                           -- 更新日時
                            ,   pi_user_id                      -- 更新者
                            ,   1                               -- 有効フラグ
                           );

    -- ------------------------------
    -- 9.会計対象外雑損失ﾃﾞｰﾀの作成
    -- ------------------------------
    -- 貸し倒れ金－合計金額 が存在する場合、会計対象外雑損失ﾃﾞｰﾀを作成する。
    IF  v_LoanLossClaimAmount > 0   THEN
        -- 利用額分は貸し倒れとして雑損失で計上するため、利用額は 0（ｾﾞﾛ） で固定とし、手数料分を会計対象外の雑損失として作成する。
        INSERT
        INTO    T_SundryControl(    ProcessDate                     -- 発生日時
                                ,   SundryType                      -- 種類（雑収入／雑損失）
                                ,   SundryAmount                    -- 金額
                                ,   SundryClass                     -- 雑収入・雑損失科目
                                ,   OrderSeq                        -- 注文SEQ
                                ,   OrderId                         -- 注文ID
                                ,   ClaimId                         -- 請求ID
                                ,   Note                            -- 備考
                                ,   CheckingUseAmount               -- 消込情報－利用額
                                ,   CheckingClaimFee                -- 消込情報－請求手数料
                                ,   CheckingDamageInterestAmount    -- 消込情報－遅延損害金
                                ,   CheckingAdditionalClaimFee      -- 消込情報－請求追加手数料
                                ,   DailySummaryFlg                 -- 日次更新フラグ
                                ,   RegistDate                      -- 登録日時
                                ,   RegistId                        -- 登録者
                                ,   UpdateDate                      -- 更新日時
                                ,   UpdateId                        -- 更新者
                                ,   ValidFlg                        -- 有効フラグ
                               )
                               VALUES
                               (    DATE(NOW())                     -- 発生日時
                                ,   1                               -- 種類（雑収入／雑損失）
                                ,   v_LoanLossClaimAmount           -- 金額
                                ,   99                              -- 雑収入・雑損失科目
                                ,   pi_order_seq                    -- 注文SEQ
                                ,   v_OrderId                       -- 注文ID
                                ,   v_ClaimId                       -- 請求ID
                                ,   NULL                            -- 備考
                                ,   0                               -- 消込情報－利用額
                                ,   v_LoanLossClaimFee              -- 消込情報－請求手数料
                                ,   v_LoanLossDamageInterestAmount  -- 消込情報－遅延損害金
                                ,   v_LoanLossAdditionalClaimFee    -- 消込情報－請求追加手数料
                                ,   0                               -- 日次更新フラグ
                                ,   NOW()                           -- 登録日時
                                ,   pi_user_id                      -- 登録者
                                ,   NOW()                           -- 更新日時
                                ,   pi_user_id                      -- 更新者
                                ,   1                               -- 有効フラグ
                               );
    END IF;

    -- ------------------------------
    -- 9.請求ﾃﾞｰﾀの更新
    -- ------------------------------
    UPDATE  T_ClaimControl
    SET     ClaimedBalance                  =   ClaimedBalance - v_LoanLossUseAmount - v_LoanLossClaimAmount
        ,   CheckingClaimAmount             =   CheckingClaimAmount + v_LoanLossUseAmount + v_LoanLossClaimAmount
        ,   CheckingUseAmount               =   CheckingUseAmount + v_LoanLossUseAmount
        ,   CheckingClaimFee                =   CheckingClaimFee  + v_LoanLossClaimFee
        ,   CheckingDamageInterestAmount    =   CheckingDamageInterestAmount + v_LoanLossDamageInterestAmount
        ,   CheckingAdditionalClaimFee      =   CheckingAdditionalClaimFee + v_LoanLossAdditionalClaimFee
        ,   BalanceClaimAmount              =   BalanceClaimAmount - v_LoanLossUseAmount - v_LoanLossClaimAmount
        ,   BalanceUseAmount                =   BalanceUseAmount - v_LoanLossUseAmount
        ,   BalanceClaimFee                 =   BalanceClaimFee - v_LoanLossClaimFee
        ,   BalanceDamageInterestAmount     =   BalanceDamageInterestAmount - v_LoanLossDamageInterestAmount
        ,   BalanceAdditionalClaimFee       =   BalanceAdditionalClaimFee - v_LoanLossAdditionalClaimFee
        ,   SundryLossTotal                 =   SundryLossTotal + v_LoanLossUseAmount + v_LoanLossClaimAmount            -- 雑損失合計
        ,   UpdateDate                      =   NOW()
        ,   UpdateId                        =   pi_user_id
    WHERE   ClaimId                         =   v_ClaimId
    ;

    -- ------------------------------
    -- 10.注文ﾃﾞｰﾀ更新
    -- ------------------------------
    -- 債権移管日
    SELECT CASE WHEN OemClaimTransDate IS NULL AND (SELECT Class1 FROM M_Code WHERE CodeId = 160 AND KeyCode = IFNULL(OemId, 0)) <> 0 THEN DATE(NOW())
           ELSE OemClaimTransDate
           END
    INTO   v_OemClaimTransDate
    FROM   T_Order
    WHERE  P_OrderSeq = pi_order_seq
    LIMIT  1;

    UPDATE  T_Order
    SET     DataStatus          =   91                      -- ﾃﾞｰﾀｽﾃｰﾀｽ（91：ｸﾛｰｽﾞ）
        ,   CloseReason         =   4                       -- ｸﾛｰｽﾞ理由（4：損害確定ｸﾛｰｽﾞ）
        ,   Dmg_DecisionFlg     =   1                       -- 損害確定ﾌﾗｸﾞ（0：損害未確定　1：損害確定）
        ,   Dmg_DecisionDate    =   DATE(NOW())             -- 損害確定日
        ,   Dmg_DecisionAmount  =   v_LoanLossUseAmount     -- 損害確定額
        ,   Dmg_DecisionReason  =   2                       -- 損害確定理由（1：差額放棄　2：全額放棄）
        ,   OemClaimTransDate   =   v_OemClaimTransDate     -- 債権移管日
        ,   UpdateDate          =   NOW()                   -- 更新日時
        ,   UpdateId            =   pi_user_id              -- 更新者
    WHERE   P_OrderSeq          =   pi_order_seq
    ;
END
$$

DELIMITER ;
