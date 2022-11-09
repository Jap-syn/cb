DROP PROCEDURE IF EXISTS P_SundryControl;

DELIMITER $$

CREATE PROCEDURE P_SundryControl(   IN  pi_sundry_type      TINYINT
                                ,   IN  pi_sundry_amount    BIGINT
                                ,   IN  pi_process_date     DATE
                                ,   IN  pi_sundry_class     INT
                                ,   IN  pi_order_id         VARCHAR(50)
                                ,   IN  pi_user_id          VARCHAR(20)
                                ,   OUT po_ret_sts          INT
                                ,   OUT po_ret_errcd        VARCHAR(100)
                                ,   OUT po_ret_sqlcd        INT
                                ,   OUT po_ret_msg          VARCHAR(255)
                                )
proc:
/******************************************************************************
 *
 * ﾌﾟﾛｼｰｼﾞｬ名   ：  P_SundryControl
 *
 * 概要         ：  雑収入・雑損失関連処理
 *
 * 引数         ：  [I/ ]pi_sundry_type                         種類（0：雑収入／1：雑損失）
 *              ：  [I/ ]pi_sundry_amount                       金額
 *              ：  [I/ ]pi_process_date                        発生日
 *              ：  [I/ ]pi_sundry_class                        科目
 *              ：  [I/ ]pi_order_id                            注文ID
 *              ：  [I/ ]pi_user_id                             ﾕｰｻﾞｰID
 *              ：  [ /O]po_ret_sts                             ﾘﾀｰﾝｽﾃｰﾀｽ
 *              ：  [ /O]po_ret_errcd                           ﾘﾀｰﾝｺｰﾄﾞ
 *              ：  [ /O]po_ret_sqlcd                           ﾘﾀｰﾝSQLｺｰﾄﾞ
 *              ：  [ /O]po_ret_msg                             ﾘﾀｰﾝﾒｯｾｰｼﾞ
 *
 * 履歴         ：  2015/05/20  NDC 新規作成
 *
 *****************************************************************************/
BEGIN
    -- ---------------------
    -- 変数宣言
    -- ---------------------
    -- 注文ﾃﾞｰﾀ取得用
    DECLARE v_OrderSeq                      BIGINT(20);
    DECLARE v_P_OrderSeq                    BIGINT(20);

    -- 消込情報更新用
    DECLARE v_CheckingClaimAmount           BIGINT(20) DEFAULT 0;
    DECLARE v_CheckingUseAmount             BIGINT(20) DEFAULT 0;
    DECLARE v_CheckingClaimFee              BIGINT(20) DEFAULT 0;
    DECLARE v_CheckingDamageInterestAmount  BIGINT(20) DEFAULT 0;
    DECLARE v_CheckingAdditionalClaimFee    BIGINT(20) DEFAULT 0;
    -- 会計対象外雑損失作成用
    DECLARE v_NaSundryAmount                BIGINT(20) DEFAULT 0;
    DECLARE v_NaSundryUseAmount             BIGINT(20) DEFAULT 0;
    DECLARE v_NaSundryClaimFee              BIGINT(20) DEFAULT 0;
    DECLARE v_NaSundryDamageInterestAmount  BIGINT(20) DEFAULT 0;
    DECLARE v_NaSundryAdditionalClaimFee    BIGINT(20) DEFAULT 0;
    -- 残金取得用
    DECLARE v_CalculationAmount             BIGINT(20) DEFAULT 0;
    -- 差額取得用
    DECLARE v_diffClaimFee                  BIGINT(20) DEFAULT 0;
    DECLARE v_diffDamageInterestAmount      BIGINT(20) DEFAULT 0;
    DECLARE v_diffAdditionalClaimFee        BIGINT(20) DEFAULT 0;

    -- 請求ﾃﾞｰﾀ取得用
    DECLARE v_ClaimId                       BIGINT(20);
    DECLARE v_ClaimAmount                   BIGINT(20) DEFAULT 0;
    DECLARE v_UseAmountTotal                BIGINT(20) DEFAULT 0;
    DECLARE v_ClaimFee                      BIGINT(20) DEFAULT 0;
    DECLARE v_DamageInterestAmount          BIGINT(20) DEFAULT 0;
    DECLARE v_AdditionalClaimFee            BIGINT(20) DEFAULT 0;
    DECLARE v_ClaimedBalance                BIGINT(20) DEFAULT 0;
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

    -- 雑収入・雑損失ﾃﾞｰﾀ取得用
    DECLARE v_SundryAmount                  BIGINT(20) DEFAULT 0;
    DECLARE v_SundryUseAmount               BIGINT(20) DEFAULT 0;
    DECLARE v_SundryClaimFee                BIGINT(20) DEFAULT 0;
    DECLARE v_SundryDamageInterestAmount    BIGINT(20) DEFAULT 0;
    DECLARE v_SundryAdditionalClaimFee      BIGINT(20) DEFAULT 0;

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
    -- 2.雑収入の場合
    -- ------------------------------
    IF  pi_sundry_type = 0  THEN
        -- ------------------------------
        -- 2-1.注文／請求ﾃﾞｰﾀの取得
        -- ------------------------------
        SELECT  o.OrderSeq
            ,   o.P_OrderSeq
            ,   cc.ClaimId
        INTO    v_OrderSeq
            ,   v_P_OrderSeq
            ,   v_ClaimId
        FROM    T_Order o
                INNER JOIN T_ClaimControl cc ON (o.P_OrderSeq = cc.OrderSeq)
        WHERE   o.OrderId =	pi_order_id
        ;

        -- pi_order_id が NULL の場合、ﾃﾞｰﾀは取得できない･･･ﾊｽﾞ･･･
        IF  no_data_found = 0   THEN
            -- ﾃﾞｰﾀが取得出来なかった場合、NULL値を設定する
            SET v_OrderSeq  =   NULL;
            SET v_ClaimId   =   NULL;
        END IF;

        -- ------------------------------
        -- 2-2.消込金額の設定
        -- ------------------------------
        -- 過剰入金分は全額利用額へ乗せられるので、入金額を全額消込情報－利用額とする。
        SET v_CheckingUseAmount = pi_sundry_amount;
        -- 利用額以外は全て0（ｾﾞﾛ）
        SET v_CheckingClaimFee = 0;
        SET v_CheckingDamageInterestAmount = 0;
        SET v_CheckingAdditionalClaimFee = 0;

        -- ------------------------------
        -- 2-3.雑収入ﾃﾞｰﾀの作成
        -- ------------------------------
        INSERT
        INTO    T_SundryControl(    ProcessDate                         -- 発生日時
                                ,   SundryType                          -- 種類（雑収入／雑損失）
                                ,   SundryAmount                        -- 金額
                                ,   SundryClass                         -- 雑収入・雑損失科目
                                ,   OrderSeq                            -- 注文SEQ
                                ,   OrderId                             -- 注文ID
                                ,   ClaimId                             -- 請求ID
                                ,   Note                                -- 備考
                                ,   CheckingUseAmount                   -- 消込情報－利用額
                                ,   CheckingClaimFee                    -- 消込情報－請求手数料
                                ,   CheckingDamageInterestAmount        -- 消込情報－遅延損害金
                                ,   CheckingAdditionalClaimFee          -- 消込情報－請求追加手数料
                                ,   DailySummaryFlg                     -- 日次更新ﾌﾗｸﾞ
                                ,   RegistDate                          -- 登録日時
                                ,   RegistId                            -- 登録者
                                ,   UpdateDate                          -- 更新日時
                                ,   UpdateId                            -- 更新者
                                ,   ValidFlg                            -- 有効ﾌﾗｸﾞ
                               )
                               VALUES
                               (    pi_process_date                     -- 発生日時
                                ,   pi_sundry_type                      -- 種類（雑収入／雑損失）
                                ,   pi_sundry_amount                    -- 金額
                                ,   pi_sundry_class                     -- 雑収入・雑損失科目
                                ,   v_OrderSeq                          -- 注文SEQ
                                ,   pi_order_id                         -- 注文ID
                                ,   v_ClaimId                           -- 請求ID
                                ,   NULL                                -- 備考
                                ,   pi_sundry_amount                    -- 消込情報－利用額
                                ,   0                                   -- 消込情報－請求手数料
                                ,   0                                   -- 消込情報－遅延損害金
                                ,   0                                   -- 消込情報－請求追加手数料
                                ,   0                                   -- 日次更新ﾌﾗｸﾞ
                                ,   NOW()                               -- 登録日時
                                ,   pi_user_id                          -- 登録者
                                ,   NOW()                               -- 更新日時
                                ,   pi_user_id                          -- 更新者
                                ,   1                                   -- 有効ﾌﾗｸﾞ
                               );

        -- ------------------------------
        -- 2-4.請求ﾃﾞｰﾀの更新
        -- ------------------------------
        -- 請求ﾃﾞｰﾀが取得出来た場合、更新する
        IF  v_ClaimId IS NOT NULL   THEN
            UPDATE  T_ClaimControl
            SET     ClaimedBalance      =   ClaimedBalance + pi_sundry_amount
                ,   CheckingClaimAmount =   CheckingClaimAmount - pi_sundry_amount
                ,   CheckingUseAmount   =   CheckingUseAmount - pi_sundry_amount
                ,   BalanceClaimAmount  =   BalanceClaimAmount + pi_sundry_amount
                ,   BalanceUseAmount    =   BalanceUseAmount + pi_sundry_amount
                ,   SundryIncomeTotal   =   SundryIncomeTotal + pi_sundry_amount    -- 雑収入合計
                ,   UpdateDate          =   NOW()
                ,   UpdateId            =   pi_user_id
            WHERE   ClaimId             =   v_ClaimId
            ;
        END IF;

    -- ------------------------------
    -- 3.雑損失の場合
    -- ------------------------------
    ELSEIF  pi_sundry_type = 1  THEN
        -- ------------------------------
        -- 3-1.親注文Seqを取得
        -- ------------------------------
        SELECT  OrderSeq
            ,   P_OrderSeq
        INTO    v_OrderSeq
            ,   v_P_OrderSeq
        FROM    T_Order
        WHERE   OrderId =   pi_order_id
        ;

        IF  no_data_found = 0   THEN
            SET po_ret_sts  =   -1;
            SET po_ret_msg  =   '対象の注文データが存在しません。';
            LEAVE proc;
        END IF;

        -- ------------------------------
        -- 3-2.入金済みのﾃﾞｰﾀを取得
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
        WHERE   OrderSeq    =   v_P_OrderSeq
        ;

        -- ------------------------------
        -- 3-3.請求ﾃﾞｰﾀ取得
        -- ------------------------------
        SELECT  ClaimId                         -- 請求ID
            ,   ClaimAmount                     -- 請求額
            ,   UseAmountTotal                  -- 利用額合計
            ,   ClaimFee                        -- 請求手数料
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
        WHERE   OrderSeq    =   v_P_OrderSeq
        ;

        IF  no_data_found = 0   THEN
            SET po_ret_sts  =   -1;
            SET po_ret_msg  =   '請求対象のデータが存在しません。';
            LEAVE proc;
        END IF;

        -- ------------------------------
        -- 3-4.雑損失ﾃﾞｰﾀの取得
        -- ------------------------------
        SELECT  IFNULL(SUM(SundryAmount), 0)
            ,   IFNULL(SUM(CheckingUseAmount), 0)
            ,   IFNULL(SUM(CheckingClaimFee), 0)
            ,   IFNULL(SUM(CheckingDamageInterestAmount), 0)
            ,   IFNULL(SUM(CheckingAdditionalClaimFee), 0)
        INTO    v_SundryAmount
            ,   v_SundryUseAmount
            ,   v_SundryClaimFee
            ,   v_SundryDamageInterestAmount
            ,   v_SundryAdditionalClaimFee
        FROM    T_SundryControl
        WHERE   OrderSeq    =   v_OrderSeq
        AND     SundryClass <>  99      -- 会計対象ﾃﾞｰﾀを取得する。
        ;

        -- ------------------------------
        -- 3-5.ﾌﾟﾗｽ入金の場合
        -- ------------------------------
        IF  pi_sundry_amount > 0    THEN
            -- ------------------------------
            -- 3-5-1.消込情報を計算
            -- ------------------------------
            /* -------------------------------------------------------------------------------------------
            -- 2015/08/27 メモ
            -- 金額の消込方法
            -- 基本的には入金時と同じ。ただし、諸々の考慮が必要。
            --  つまり・・・
            --   最低請求額（手数料等）消し込み → 利用額消し込み → 最終請求額（手数料等）消し込み
            --  1.最低請求情報－請求追加手数料（MinAdditionalClaimFee）消し込み
            --  2.最低請求情報－遅延損害金（MinDamageInterestAmount）消し込み
            --  3.最低請求情報－請求手数料（MinClaimFee）消し込み
            --  4.利用額（UseAmountTotal）消し込み
            --  5.請求追加手数料（AdditionalClaimFee）の差額分消し込み
            --  6.遅延損害金（DamageInterestAmount）の差額分消し込み
            --  7.請求手数料（ClaimFee）の差額分消し込み
            -- ------------------------------------------------------------------------------------------- */
            -- 最終請求額と最低請求額の差額を取得
            -- 請求手数料
            SET v_diffClaimFee = v_ClaimFee - v_MinClaimFee;
            -- 遅延損害金
            SET v_diffDamageInterestAmount = v_DamageInterestAmount - v_MinDamageInterestAmount;
            -- 請求追加手数料
            SET v_diffAdditionalClaimFee = v_AdditionalClaimFee - v_MinAdditionalClaimFee;

            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 1. 最低請求情報－請求追加手数料 消し込み
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 入金済みの請求追加手数料 が 最低請求情報－請求追加手数料 以上の場合
            IF  v_ReceiptAdditionalClaimFee >= v_MinAdditionalClaimFee  THEN
                -- 消込情報－請求追加手数料に対する追加消込はなし
                SET v_CheckingAdditionalClaimFee = 0;
                -- 残金は入金額
                SET v_CalculationAmount = pi_sundry_amount;
            -- それ以外の場合
            ELSE
                -- 最低請求情報－請求追加手数料 から 入金済みの請求追加手数料を減算して消込情報－請求追加手数料 を算出
                SET v_CheckingAdditionalClaimFee = v_MinAdditionalClaimFee - v_ReceiptAdditionalClaimFee;
                -- 入金額 から 消込情報－請求追加手数料を減算して残金を取得
                SET v_CalculationAmount = pi_sundry_amount - v_CheckingAdditionalClaimFee;
            END IF;

            -- 1.で残金が存在する場合は以下処理を行う。
            IF  v_CalculationAmount > 0 THEN
                -- +++++++++++++++++++++++++++++++++++++++++++++
                -- 2. 最低請求情報－遅延損害金 消し込み
                -- +++++++++++++++++++++++++++++++++++++++++++++
                -- 入金済みの遅延損害金 が 最低請求情報－遅延損害金 以上の場合
                IF  v_ReceiptDamageInterestAmount >= v_MinDamageInterestAmount  THEN
                    -- 消込情報－遅延損害金に対する追加消込はなし
                    SET v_CheckingDamageInterestAmount = 0;
                    -- 残金は1.の残金
                    SET v_CalculationAmount = v_CalculationAmount;
                -- それ以外の場合
                ELSE
                    -- 最低請求情報－遅延損害金 から 入金済みの遅延損害金を減算して 消込情報－遅延損害金 を算出
                    SET v_CheckingDamageInterestAmount = v_MinDamageInterestAmount - v_ReceiptDamageInterestAmount;
                    -- 1.の残金から消込情報－遅延損害金を減算して入金額の残金を取得
                    SET v_CalculationAmount = v_CalculationAmount - v_CheckingDamageInterestAmount;
                END IF;
            END IF;

                -- 2.で残金が存在する場合は以下処理を行う。
            IF  v_CalculationAmount > 0 THEN
                -- +++++++++++++++++++++++++++++++++++++++++++++
                -- 3. 最低請求情報－請求手数料 消し込み
                -- +++++++++++++++++++++++++++++++++++++++++++++
                -- 入金済みの請求手数料 が 最低請求情報－請求手数料 以上の場合
                IF  v_ReceiptClaimFee >= v_MinClaimFee  THEN
                    -- 消込情報－請求手数料に対する追加消込はなし
                    SET v_CheckingClaimFee = 0;
                    -- 残金は2.の残金
                    SET v_CalculationAmount = v_CalculationAmount;
                -- それ以外の場合
                ELSE
                    -- 最低請求情報－請求手数料 から 入金済みの請求手数料を減算して 消込情報－請求手数料 を算出
                    SET v_CheckingClaimFee = v_MinClaimFee - v_ReceiptClaimFee;
                    -- 2.の残金 から消込情報－請求手数料を減算して入金額の残金を取得
                    SET v_CalculationAmount = v_CalculationAmount - v_CheckingClaimFee;
                END IF;
            END IF;

            -- 3.で残金が存在する場合は以下処理を行う。
            IF  v_CalculationAmount > 0 THEN
                -- +++++++++++++++++++++++++++++++++++++++++++++
                -- 4. 利用額 消し込み
                -- +++++++++++++++++++++++++++++++++++++++++++++
                -- 入金済みの利用額 が 利用額合計 以上の場合
                IF  v_ReceiptUseAmount >= v_UseAmountTotal THEN
                    -- 消込情報－利用額に対する追加消込はなし
                    SET v_CheckingUseAmount = 0;
                    -- 残金は3.の残金
                    SET v_CalculationAmount = v_CalculationAmount;
                -- それ以外の場合
                ELSE
                    -- 消込情報－利用額 は 3.の残金
                    SET v_CheckingUseAmount = v_CalculationAmount;
                    -- 3.の残金 が全て消込情報－利用額 なので、残金は 0（ｾﾞﾛ）
                    SET v_CalculationAmount = 0;
                END IF;
            END IF;

            -- 4.で残金が存在する場合は以下処理を行う。
            IF  v_CalculationAmount > 0 THEN
                -- +++++++++++++++++++++++++++++++++++++++++++++
                -- 5. 請求追加手数料の差額分 消し込み
                -- +++++++++++++++++++++++++++++++++++++++++++++
                -- 4.の残金 が 請求追加手数料の差額分 以上の場合
                IF  v_CalculationAmount >= v_diffAdditionalClaimFee THEN
                    -- 消込情報－請求追加手数料 に 請求追加手数料の差額 を足しこむ
                    SET v_CheckingAdditionalClaimFee = v_CheckingAdditionalClaimFee + v_diffAdditionalClaimFee;
                    -- 4.の残金から請求追加手数料の差額を減算して入金額の残金を取得
                    SET v_CalculationAmount = v_CalculationAmount - v_diffAdditionalClaimFee;
                -- それ以外の場合
                ELSE
                    -- 消込情報－請求追加手数料 に 4.の残金を足しこむ
                    SET v_CheckingAdditionalClaimFee = v_CheckingAdditionalClaimFee + v_CalculationAmount;
                    -- 4.の残金が全て 消込情報－請求追加手数料 なので、残金は 0（ｾﾞﾛ）
                    SET v_CalculationAmount = 0;
                END IF;
            END IF;

            -- 5.で残金が存在する場合は以下処理を行う。
            IF  v_CalculationAmount > 0 THEN
                -- +++++++++++++++++++++++++++++++++++++++++++++
                -- 6. 遅延損害金の差額分 消し込み
                -- +++++++++++++++++++++++++++++++++++++++++++++
                -- 5.の残金 が 遅延損害金の差額分 以上の場合
                IF  v_CalculationAmount >= v_diffDamageInterestAmount   THEN
                    -- 消込情報－遅延損害金 に 遅延損害金の差額 を足しこむ
                    SET v_CheckingDamageInterestAmount = v_CheckingDamageInterestAmount + v_diffDamageInterestAmount;
                    -- 5.の残金から遅延損害金の差額を減算して入金額の残金を取得
                    SET v_CalculationAmount = v_CalculationAmount - v_diffDamageInterestAmount;
                -- それ以外の場合
                ELSE
                    -- 消込情報－遅延損害金 に 5.の残金を足しこむ
                    SET v_CheckingDamageInterestAmount = v_CheckingDamageInterestAmount + v_CalculationAmount;
                    -- 5.の残金が全て 消込情報－遅延損害金 なので、残金は 0（ｾﾞﾛ）
                    SET v_CalculationAmount = 0;
                END IF;
            END IF;

            -- 6.で残金が存在する場合は以下処理を行う。
            IF  v_CalculationAmount > 0 THEN
                -- +++++++++++++++++++++++++++++++++++++++++++++
                -- 7. 請求手数料の差額分 消し込み
                -- +++++++++++++++++++++++++++++++++++++++++++++
                -- 6.の残金 が 請求手数料の差額 以上の場合
                IF  v_CalculationAmount >= v_diffClaimFee   THEN
                    -- 消込情報－請求手数料 に 6.の残金を足しこむ
                    SET v_CheckingClaimFee = v_CheckingClaimFee + v_diffClaimFee;
                    -- 6.の残金から請求手数料の差額を減算して入金額の残金を取得
                    SET v_CalculationAmount = v_CalculationAmount - v_diffClaimFee;
                ELSE
                    -- 消込情報－請求手数料 に 6.の残金を足しこむ
                    SET v_CheckingClaimFee = v_CheckingClaimFee + v_CalculationAmount;
                    -- 6.の残金が全て 消込情報－請求手数料 なので、残金は 0（ｾﾞﾛ）
                    SET v_CalculationAmount = 0;
                END IF;
            END IF;

            -- 7.で残金が存在する場合は以下処理を行う。
            IF  v_CalculationAmount > 0 THEN
                -- 過剰金として 消込情報－利用額 に 7.の残金 を足しこむ
                SET v_CheckingUseAmount = v_CheckingUseAmount + v_CalculationAmount;
            END IF;

            -- ++++++++++++++++++++++++++++++++++++++++
            -- 8. 消込情報－消込金額合計 を求める
            -- ++++++++++++++++++++++++++++++++++++++++
            SET v_CheckingClaimAmount = v_CheckingUseAmount + v_CheckingClaimFee + v_CheckingDamageInterestAmount + v_CheckingAdditionalClaimFee;

            -- ------------------------------
            -- 3-5-2.雑損失ﾃﾞｰﾀの作成
            -- ------------------------------
            INSERT
            INTO    T_SundryControl(    ProcessDate                         -- 発生日時
                                    ,   SundryType                          -- 種類（雑収入／雑損失）
                                    ,   SundryAmount                        -- 金額
                                    ,   SundryClass                         -- 雑収入・雑損失科目
                                    ,   OrderSeq                            -- 注文SEQ
                                    ,   OrderId                             -- 注文ID
                                    ,   ClaimId                             -- 請求ID
                                    ,   Note                                -- 備考
                                    ,   CheckingUseAmount                   -- 消込情報－利用額
                                    ,   CheckingClaimFee                    -- 消込情報－請求手数料
                                    ,   CheckingDamageInterestAmount        -- 消込情報－遅延損害金
                                    ,   CheckingAdditionalClaimFee          -- 消込情報－請求追加手数料
                                    ,   DailySummaryFlg                     -- 日次更新ﾌﾗｸﾞ
                                    ,   RegistDate                          -- 登録日時
                                    ,   RegistId                            -- 登録者
                                    ,   UpdateDate                          -- 更新日時
                                    ,   UpdateId                            -- 更新者
                                    ,   ValidFlg                            -- 有効ﾌﾗｸﾞ
                                   )
                                   VALUES
                                   (    pi_process_date                     -- 発生日時
                                    ,   pi_sundry_type                      -- 種類（雑収入／雑損失）
                                    ,   pi_sundry_amount                    -- 金額
                                    ,   pi_sundry_class                     -- 雑収入・雑損失科目
                                    ,   v_OrderSeq                          -- 注文SEQ
                                    ,   pi_order_id                         -- 注文ID
                                    ,   v_ClaimId                           -- 請求ID
                                    ,   NULL                                -- 備考
                                    ,   v_CheckingUseAmount                 -- 消込情報－利用額
                                    ,   v_CheckingClaimFee                  -- 消込情報－請求手数料
                                    ,   v_CheckingDamageInterestAmount      -- 消込情報－遅延損害金
                                    ,   v_CheckingAdditionalClaimFee        -- 消込情報－請求追加手数料
                                    ,   0                                   -- 日次更新ﾌﾗｸﾞ
                                    ,   NOW()                               -- 登録日時
                                    ,   pi_user_id                          -- 登録者
                                    ,   NOW()                               -- 更新日時
                                    ,   pi_user_id                          -- 更新者
                                    ,   1                                   -- 有効ﾌﾗｸﾞ
                                   );

            -- ------------------------------
            -- 3-5-3.最低請求額 = 入金額 + 雑損失額 の場合
            -- ------------------------------
            IF  v_MinClaimAmount = pi_sundry_amount + v_ReceiptAmount + v_SundryAmount  THEN
                -- ------------------------------
                -- 3-5-3-1.最低請求額 = 最終請求額 の場合
                -- ------------------------------
                IF  v_MinClaimAmount = v_ClaimAmount    THEN
                    -- ------------------------------
                    -- 3-5-3-1-1.請求ﾃﾞｰﾀの更新
                    -- ------------------------------
                    UPDATE  T_ClaimControl
                    SET     ClaimedBalance                  =   ClaimedBalance - v_CheckingClaimAmount                          -- 請求残高
                        ,   CheckingClaimAmount             =   CheckingClaimAmount + v_CheckingClaimAmount                     -- 消込情報－消込額合計
                        ,   CheckingUseAmount               =   CheckingUseAmount + v_CheckingUseAmount                         -- 消込情報－利用額
                        ,   CheckingClaimFee                =   CheckingClaimFee + v_CheckingClaimFee                           -- 消込情報－請求手数料
                        ,   CheckingDamageInterestAmount    =   CheckingDamageInterestAmount + v_CheckingDamageInterestAmount   -- 消込情報－遅延損害金
                        ,   CheckingAdditionalClaimFee      =   CheckingAdditionalClaimFee + v_CheckingAdditionalClaimFee       -- 消込情報－請求追加手数料
                        ,   BalanceClaimAmount              =   BalanceClaimAmount - v_CheckingClaimAmount                      -- 残高情報－残高合計
                        ,   BalanceUseAmount                =   BalanceUseAmount - v_CheckingUseAmount                          -- 残高情報－利用額
                        ,   BalanceClaimFee                 =   BalanceClaimFee - v_CheckingClaimFee                            -- 残高情報－請求手数料
                        ,   BalanceDamageInterestAmount     =   BalanceDamageInterestAmount - v_CheckingDamageInterestAmount    -- 残高情報－遅延損害金
                        ,   BalanceAdditionalClaimFee       =   BalanceAdditionalClaimFee - v_CheckingAdditionalClaimFee        -- 残高情報－請求追加手数料
                        ,   SundryLossTotal                 =   SundryLossTotal + pi_sundry_amount                              -- 雑損失合計
                        ,   UpdateDate                      =   NOW()
                        ,   UpdateId                        =   pi_user_id
                    WHERE   ClaimId =   v_ClaimId
                    ;

                -- ------------------------------
                -- 3-5-3-2.最終請求額 > 最低請求額 の場合
                -- ------------------------------
                ELSEIF  v_ClaimAmount > v_MinClaimAmount    THEN
                    -- ------------------------------
                    -- 3-5-3-2-1.雑損失情報の取得
                    -- ------------------------------
                    -- 1) 利用額（利用額合計 から 入金済みの利用額と消込情報－利用額 を減算する）
                    SET v_NaSundryUseAmount = v_UseAmountTotal - v_ReceiptUseAmount - v_CheckingUseAmount;

                    -- 2) 請求手数料（請求手数料 から 入金済みの請求手数料と消込情報－請求手数料 を減算する）
                    SET v_NaSundryClaimFee = v_ClaimFee - v_ReceiptClaimFee - v_CheckingClaimFee;

                    -- 3) 遅延損害金（遅延損害金 から 入金済みの遅延損害金と消込情報－遅延損害金 を減算する）
                    SET v_NaSundryDamageInterestAmount = v_DamageInterestAmount - v_ReceiptDamageInterestAmount - v_CheckingDamageInterestAmount;

                    -- 4) 請求追加手数料（請求追加手数料 から 入金済みの請求追加手数料と消込情報－請求追加手数料 を減算する）
                    SET v_NaSundryAdditionalClaimFee = v_AdditionalClaimFee - v_ReceiptAdditionalClaimFee - v_CheckingAdditionalClaimFee;

                    -- 5) 金額
                    SET v_NaSundryAmount = v_NaSundryUseAmount + v_NaSundryClaimFee + v_NaSundryDamageInterestAmount + v_NaSundryAdditionalClaimFee;

                    -- ------------------------------
                    -- 3-5-3-2-2.会計対象外雑損失ﾃﾞｰﾀの作成
                    -- ------------------------------
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
                                            ,   DailySummaryFlg                 -- 日次更新ﾌﾗｸﾞ
                                            ,   RegistDate                      -- 登録日時
                                            ,   RegistId                        -- 登録者
                                            ,   UpdateDate                      -- 更新日時
                                            ,   UpdateId                        -- 更新者
                                            ,   ValidFlg                        -- 有効ﾌﾗｸﾞ
                                           )
                                           VALUES
                                           (    DATE(NOW())                     -- 発生日時
                                            ,   1                               -- 種類（雑収入／雑損失）
                                            ,   v_NaSundryAmount                -- 金額
                                            ,   99                              -- 雑収入・雑損失科目
                                            ,   v_OrderSeq                      -- 注文SEQ
                                            ,   pi_order_id                     -- 注文ID
                                            ,   v_ClaimId                       -- 請求ID
                                            ,   NULL                            -- 備考
                                            ,   v_NaSundryUseAmount             -- 消込情報－利用額
                                            ,   v_NaSundryClaimFee              -- 消込情報－請求手数料
                                            ,   v_NaSundryDamageInterestAmount  -- 消込情報－遅延損害金
                                            ,   v_NaSundryAdditionalClaimFee    -- 消込情報－請求追加手数料
                                            ,   0                               -- 日次更新ﾌﾗｸﾞ
                                            ,   NOW()                           -- 登録日時
                                            ,   pi_user_id                      -- 登録者
                                            ,   NOW()                           -- 更新日時
                                            ,   pi_user_id                      -- 更新者
                                            ,   1                               -- 有効ﾌﾗｸﾞ
                                           );

                    -- ------------------------------
                    -- 3-5-3-2-3.請求ﾃﾞｰﾀの更新
                    -- ------------------------------
                    UPDATE  T_ClaimControl
                    SET     ClaimedBalance                  =   ClaimedBalance - v_CheckingClaimAmount - v_NaSundryAmount                                       -- 請求残高
                        ,   CheckingClaimAmount             =   CheckingClaimAmount + v_CheckingClaimAmount + v_NaSundryAmount                                  -- 消込情報－消込額合計
                        ,   CheckingUseAmount               =   CheckingUseAmount + v_CheckingUseAmount + v_NaSundryUseAmount                                   -- 消込情報－利用額
                        ,   CheckingClaimFee                =   CheckingClaimFee + v_CheckingClaimFee + v_NaSundryClaimFee                                      -- 消込情報－請求手数料
                        ,   CheckingDamageInterestAmount    =   CheckingDamageInterestAmount + v_CheckingDamageInterestAmount + v_NaSundryDamageInterestAmount  -- 消込情報－遅延損害金
                        ,   CheckingAdditionalClaimFee      =   CheckingAdditionalClaimFee + v_CheckingAdditionalClaimFee + v_NaSundryAdditionalClaimFee        -- 消込情報－請求追加手数料
                        ,   BalanceClaimAmount              =   BalanceClaimAmount - v_CheckingClaimAmount - v_NaSundryAmount                                   -- 残高情報－残高合計
                        ,   BalanceUseAmount                =   BalanceUseAmount - v_CheckingUseAmount - v_NaSundryUseAmount                                    -- 残高情報－利用額
                        ,   BalanceClaimFee                 =   BalanceClaimFee - v_CheckingClaimFee - v_NaSundryClaimFee                                       -- 残高情報－請求手数料
                        ,   BalanceDamageInterestAmount     =   BalanceDamageInterestAmount - v_CheckingDamageInterestAmount - v_NaSundryDamageInterestAmount   -- 残高情報－遅延損害金
                        ,   BalanceAdditionalClaimFee       =   BalanceAdditionalClaimFee - v_CheckingAdditionalClaimFee - v_NaSundryAdditionalClaimFee         -- 残高情報－請求追加手数料
                        ,   SundryLossTotal                 =   SundryLossTotal + pi_sundry_amount + v_NaSundryAmount                                           -- 雑損失合計
                        ,   UpdateDate                      =   NOW()
                        ,   UpdateId                        =   pi_user_id
                    WHERE   ClaimId =   v_ClaimId
                    ;
                END IF;

                -- ------------------------------
                -- 3-5-3-3.注文ﾃﾞｰﾀの更新
                -- ------------------------------
                UPDATE  T_Order
                SET     DataStatus  =   91      -- ﾃﾞｰﾀｽﾃｰﾀｽ（ｸﾛｰｽﾞ）
                    ,   CloseReason =   1       -- ｸﾛｰｽﾞ理由（入金済み正常ｸﾛｰｽﾞ）
                    ,   Rct_Status  =   1       -- 顧客入金ｽﾃｰﾀｽ（入金済み）
                    ,   UpdateDate  =   NOW()
                    ,   UpdateId    =   pi_user_id
                WHERE   P_OrderSeq  =   v_P_OrderSeq
                AND     Cnl_Status  =   0       -- ｷｬﾝｾﾙされていないもの
                ;

            -- ------------------------------
            -- 3-5-4.最低請求額 > 入金額 + 雑損失額 の場合
            -- ------------------------------
            ELSEIF  v_MinClaimAmount > pi_sundry_amount + v_ReceiptAmount + v_SundryAmount  THEN
                -- ------------------------------
                -- 3-5-4-1.請求ﾃﾞｰﾀの更新
                -- ------------------------------
                UPDATE  T_ClaimControl
                SET     ClaimedBalance      =   ClaimedBalance - pi_sundry_amount
                    ,   CheckingClaimAmount =   CheckingClaimAmount + pi_sundry_amount
                    ,   CheckingUseAmount   =   CheckingUseAmount + pi_sundry_amount
                    ,   BalanceClaimAmount  =   BalanceClaimAmount - pi_sundry_amount
                    ,   BalanceUseAmount    =   BalanceUseAmount - pi_sundry_amount
                    ,   SundryLossTotal     =   SundryLossTotal + pi_sundry_amount      -- 雑損失合計
                    ,   UpdateDate          =   NOW()
                    ,   UpdateId            =   pi_user_id
                WHERE   ClaimId =   v_ClaimId
                ;

                -- ------------------------------
                -- 3-5-4-2.注文ﾃﾞｰﾀの更新
                -- ------------------------------
                UPDATE  T_Order
                SET     DataStatus  =   61      -- ﾃﾞｰﾀｽﾃｰﾀｽ（一部入金）
                    ,   Rct_Status  =   1       -- 顧客入金ｽﾃｰﾀｽ（入金済み）
                    ,   UpdateDate  =   NOW()
                    ,   UpdateId    =   pi_user_id
                WHERE   P_OrderSeq  =   v_P_OrderSeq
                AND     Cnl_Status  =   0       -- ｷｬﾝｾﾙされていないもの
                ;

            -- ------------------------------
            -- 3-5-5.最終請求額 > 入金額 + 雑損失額 > 最低請求額 の場合
            -- ------------------------------
            ELSEIF  v_ClaimAmount > pi_sundry_amount + v_ReceiptAmount + v_SundryAmount AND
                    pi_sundry_amount + v_ReceiptAmount + v_SundryAmount > v_MinClaimAmount  THEN
                -- ------------------------------
                -- 3-5-5-1.雑損失情報の取得
                -- ------------------------------
                -- 1) 利用額（利用額合計 から 入金済みの利用額と消込情報－利用額 を減算する）
                SET v_NaSundryUseAmount = v_UseAmountTotal - v_ReceiptUseAmount - v_CheckingUseAmount;

                -- 2) 請求手数料（請求手数料 から 入金済みの請求手数料と消込情報－請求手数料 を減算する）
                SET v_NaSundryClaimFee = v_ClaimFee - v_ReceiptClaimFee - v_CheckingClaimFee;

                -- 3) 遅延損害金（遅延損害金 から 入金済みの遅延損害金と消込情報－遅延損害金 を減算する）
                SET v_NaSundryDamageInterestAmount = v_DamageInterestAmount - v_ReceiptDamageInterestAmount - v_CheckingDamageInterestAmount;

                -- 4) 請求追加手数料（請求追加手数料 から 入金済みの請求追加手数料と消込情報－請求追加手数料 を減算する）
                SET v_NaSundryAdditionalClaimFee = v_AdditionalClaimFee - v_ReceiptAdditionalClaimFee - v_CheckingAdditionalClaimFee;

                -- 5) 金額
                SET v_NaSundryAmount = v_NaSundryUseAmount + v_NaSundryClaimFee + v_NaSundryDamageInterestAmount + v_NaSundryAdditionalClaimFee;

                -- ------------------------------
                -- 3-5-5-2.会計対象外雑損失ﾃﾞｰﾀの作成
                -- ------------------------------
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
                                        ,   DailySummaryFlg                 -- 日次更新ﾌﾗｸﾞ
                                        ,   RegistDate                      -- 登録日時
                                        ,   RegistId                        -- 登録者
                                        ,   UpdateDate                      -- 更新日時
                                        ,   UpdateId                        -- 更新者
                                        ,   ValidFlg                        -- 有効ﾌﾗｸﾞ
                                       )
                                       VALUES
                                       (    DATE(NOW())                     -- 発生日時
                                        ,   1                               -- 種類（雑収入／雑損失）
                                        ,   v_NaSundryAmount                -- 金額
                                        ,   99                              -- 雑収入・雑損失科目
                                        ,   v_OrderSeq                      -- 注文SEQ
                                        ,   pi_order_id                     -- 注文ID
                                        ,   v_ClaimId                       -- 請求ID
                                        ,   NULL                            -- 備考
                                        ,   v_NaSundryUseAmount             -- 消込情報－利用額
                                        ,   v_NaSundryClaimFee              -- 消込情報－請求手数料
                                        ,   v_NaSundryDamageInterestAmount  -- 消込情報－遅延損害金
                                        ,   v_NaSundryAdditionalClaimFee    -- 消込情報－請求追加手数料
                                        ,   0                               -- 日次更新ﾌﾗｸﾞ
                                        ,   NOW()                           -- 登録日時
                                        ,   pi_user_id                      -- 登録者
                                        ,   NOW()                           -- 更新日時
                                        ,   pi_user_id                      -- 更新者
                                        ,   1                               -- 有効ﾌﾗｸﾞ
                                       );

                -- ------------------------------
                -- 3-5-5-3.請求ﾃﾞｰﾀの更新
                -- ------------------------------
                UPDATE  T_ClaimControl
                SET     ClaimedBalance                  =   ClaimedBalance - v_CheckingClaimAmount - v_NaSundryAmount                                       -- 請求残高
                    ,   CheckingClaimAmount             =   CheckingClaimAmount + v_CheckingClaimAmount + v_NaSundryAmount                                  -- 消込情報－消込額合計
                    ,   CheckingUseAmount               =   CheckingUseAmount + v_CheckingUseAmount + v_NaSundryUseAmount                                   -- 消込情報－利用額
                    ,   CheckingClaimFee                =   CheckingClaimFee + v_CheckingClaimFee + v_NaSundryClaimFee                                      -- 消込情報－請求手数料
                    ,   CheckingDamageInterestAmount    =   CheckingDamageInterestAmount + v_CheckingDamageInterestAmount + v_NaSundryDamageInterestAmount  -- 消込情報－遅延損害金
                    ,   CheckingAdditionalClaimFee      =   CheckingAdditionalClaimFee + v_CheckingAdditionalClaimFee + v_NaSundryAdditionalClaimFee        -- 消込情報－請求追加手数料
                    ,   BalanceClaimAmount              =   BalanceClaimAmount - v_CheckingClaimAmount - v_NaSundryAmount                                   -- 残高情報－残高合計
                    ,   BalanceUseAmount                =   BalanceUseAmount - v_CheckingUseAmount - v_NaSundryUseAmount                                    -- 残高情報－利用額
                    ,   BalanceClaimFee                 =   BalanceClaimFee - v_CheckingClaimFee - v_NaSundryClaimFee                                       -- 残高情報－請求手数料
                    ,   BalanceDamageInterestAmount     =   BalanceDamageInterestAmount - v_CheckingDamageInterestAmount - v_NaSundryDamageInterestAmount   -- 残高情報－遅延損害金
                    ,   BalanceAdditionalClaimFee       =   BalanceAdditionalClaimFee - v_CheckingAdditionalClaimFee - v_NaSundryAdditionalClaimFee         -- 残高情報－請求追加手数料
                    ,   SundryLossTotal                 =   SundryLossTotal + pi_sundry_amount + v_NaSundryAmount                                           -- 雑損失合計
                    ,   UpdateDate                      =   NOW()
                    ,   UpdateId                        =   pi_user_id
                WHERE   ClaimId =   v_ClaimId
                ;

                -- ------------------------------
                -- 3-5-5-4.注文ﾃﾞｰﾀの更新
                -- ------------------------------
                UPDATE  T_Order
                SET     DataStatus  =   91      -- ﾃﾞｰﾀｽﾃｰﾀｽ（ｸﾛｰｽﾞ）
                    ,   CloseReason =   1       -- ｸﾛｰｽﾞ理由（入金済み正常ｸﾛｰｽﾞ）
                    ,   Rct_Status  =   1       -- 顧客入金ｽﾃｰﾀｽ（入金済み）
                    ,   UpdateDate  =   NOW()
                    ,   UpdateId    =   pi_user_id
                WHERE   P_OrderSeq  =   v_P_OrderSeq
                AND     Cnl_Status  =   0       -- ｷｬﾝｾﾙされていないもの
                ;

            -- ------------------------------
            -- 3-5-6.最終請求額 = 入金額 + 雑損失額 の場合
            -- ------------------------------
            ELSEIF  v_ClaimAmount = pi_sundry_amount  + v_ReceiptAmount + v_SundryAmount  THEN
                -- ------------------------------
                -- 3-5-6-1.請求ﾃﾞｰﾀの更新
                -- ------------------------------
                UPDATE  T_ClaimControl
                SET     ClaimedBalance                  =   ClaimedBalance - v_CheckingClaimAmount                          -- 請求残高
                    ,   CheckingClaimAmount             =   CheckingClaimAmount + v_CheckingClaimAmount                     -- 消込情報－消込額合計
                    ,   CheckingUseAmount               =   CheckingUseAmount + v_CheckingUseAmount                         -- 消込情報－利用額
                    ,   CheckingClaimFee                =   CheckingClaimFee + v_CheckingClaimFee                           -- 消込情報－請求手数料
                    ,   CheckingDamageInterestAmount    =   CheckingDamageInterestAmount + v_CheckingDamageInterestAmount   -- 消込情報－遅延損害金
                    ,   CheckingAdditionalClaimFee      =   CheckingAdditionalClaimFee + v_CheckingAdditionalClaimFee       -- 消込情報－請求追加手数料
                    ,   BalanceClaimAmount              =   BalanceClaimAmount - v_CheckingClaimAmount                      -- 残高情報－残高合計
                    ,   BalanceUseAmount                =   BalanceUseAmount - v_CheckingUseAmount                          -- 残高情報－利用額
                    ,   BalanceClaimFee                 =   BalanceClaimFee - v_CheckingClaimFee                            -- 残高情報－請求手数料
                    ,   BalanceDamageInterestAmount     =   BalanceDamageInterestAmount - v_CheckingDamageInterestAmount    -- 残高情報－遅延損害金
                    ,   BalanceAdditionalClaimFee       =   BalanceAdditionalClaimFee - v_CheckingAdditionalClaimFee        -- 残高情報－請求追加手数料
                    ,   SundryLossTotal                 =   SundryLossTotal + pi_sundry_amount                              -- 雑損失合計
                    ,   UpdateDate                      =   NOW()
                    ,   UpdateId                        =   pi_user_id
                WHERE   ClaimId =   v_ClaimId
                ;

                -- ------------------------------
                -- 3-5-6-2.注文ﾃﾞｰﾀの更新
                -- ------------------------------
                UPDATE  T_Order
                SET     DataStatus  =   91      -- ﾃﾞｰﾀｽﾃｰﾀｽ（ｸﾛｰｽﾞ）
                    ,   CloseReason =   1       -- ｸﾛｰｽﾞ理由（入金済み正常ｸﾛｰｽﾞ）
                    ,   Rct_Status  =   1       -- 顧客入金ｽﾃｰﾀｽ（入金済み）
                    ,   UpdateDate  =   NOW()
                    ,   UpdateId    =   pi_user_id
                WHERE   P_OrderSeq  =   v_P_OrderSeq
                AND     Cnl_Status  =   0       -- ｷｬﾝｾﾙされていないもの
                ;
            END IF;

        -- ------------------------------
        -- 3-6.ﾏｲﾅｽ入金の場合
        -- ------------------------------
        ELSEIF  pi_sundry_amount < 0    THEN
            -- ------------------------------
            -- 3-6-1.会計対象外雑損失情報の取得
            -- ------------------------------
            SELECT  IFNULL(SUM(SundryAmount), 0)
                ,   IFNULL(SUM(CheckingUseAmount), 0)
                ,   IFNULL(SUM(CheckingClaimFee), 0)
                ,   IFNULL(SUM(CheckingDamageInterestAmount), 0)
                ,   IFNULL(SUM(CheckingAdditionalClaimFee), 0)
            INTO    v_NaSundryAmount
                ,   v_NaSundryUseAmount
                ,   v_NaSundryClaimFee
                ,   v_NaSundryDamageInterestAmount
                ,   v_NaSundryAdditionalClaimFee
            FROM    T_SundryControl
            WHERE   OrderSeq    =   v_OrderSeq
            AND     SundryClass =   99
            ;

            -- ------------------------------
            -- 3-6-2.会計対象外雑損失取り消しﾃﾞｰﾀの作成
            -- ------------------------------
            -- 会計対象外雑損失の金額が存在する場合
            IF  v_NaSundryAmount > 0    THEN
                -- 取り消しﾃﾞｰﾀなので、取得した雑損失情報に -1 を掛ける
                SET v_NaSundryAmount = v_NaSundryAmount * (-1);
                SET v_NaSundryUseAmount = v_NaSundryUseAmount * (-1);
                SET v_NaSundryClaimFee = v_NaSundryClaimFee * (-1);
                SET v_NaSundryDamageInterestAmount = v_NaSundryDamageInterestAmount * (-1);
                SET v_NaSundryAdditionalClaimFee = v_NaSundryAdditionalClaimFee * (-1);

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
                                        ,   DailySummaryFlg                 -- 日次更新ﾌﾗｸﾞ
                                        ,   RegistDate                      -- 登録日時
                                        ,   RegistId                        -- 登録者
                                        ,   UpdateDate                      -- 更新日時
                                        ,   UpdateId                        -- 更新者
                                        ,   ValidFlg                        -- 有効ﾌﾗｸﾞ
                                       )
                                       VALUES
                                       (    DATE(NOW())                     -- 発生日時
                                        ,   1                               -- 種類（雑収入／雑損失）
                                        ,   v_NaSundryAmount                -- 金額
                                        ,   99                              -- 雑収入・雑損失科目
                                        ,   v_OrderSeq                      -- 注文SEQ
                                        ,   pi_order_id                     -- 注文ID
                                        ,   v_ClaimId                       -- 請求ID
                                        ,   NULL                            -- 備考
                                        ,   v_NaSundryUseAmount             -- 消込情報－利用額
                                        ,   v_NaSundryClaimFee              -- 消込情報－請求手数料
                                        ,   v_NaSundryDamageInterestAmount  -- 消込情報－遅延損害金
                                        ,   v_NaSundryAdditionalClaimFee    -- 消込情報－請求追加手数料
                                        ,   0                               -- 日次更新ﾌﾗｸﾞ
                                        ,   NOW()                           -- 登録日時
                                        ,   pi_user_id                      -- 登録者
                                        ,   NOW()                           -- 更新日時
                                        ,   pi_user_id                      -- 更新者
                                        ,   1                               -- 有効ﾌﾗｸﾞ
                                       );
            END IF;

            -- ------------------------------
            -- 3-6-3.消込情報を計算
            -- ------------------------------
            -- ﾏｲﾅｽ入金の場合は入力した雑損失に対する消し込みをする！！！
            -- 金額計算の都合上、ﾏｲﾅｽ値はﾌﾟﾗｽ値に直して計算する。
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 1. 請求追加手数料の消し込み
            -- +++++++++++++++++++++++++++++++++++++++++++++
            -- 計上済みの雑損失－請求追加手数料 が 入金額（ﾌﾟﾗｽ値に変換） 以上の場合
            IF  v_SundryAdditionalClaimFee >= pi_sundry_amount * (-1)   THEN
                -- 消込情報－請求追加手数料 は 入金額（-1を掛ける）
                SET v_CheckingAdditionalClaimFee = pi_sundry_amount * (-1);
                -- 入金額が全て消込情報－請求追加手数料 になるので、残金は0（ｾﾞﾛ）
                SET v_CalculationAmount = 0;
            -- それ以外の場合
            ELSE
                -- 消込情報－請求追加手数料 は 計上済みの雑損失－請求追加手数料
                SET v_CheckingAdditionalClaimFee = v_SundryAdditionalClaimFee;
                -- 入金額（-1を掛ける） から 消込情報－請求追加手数料 を減算して残金を取得
                SET v_CalculationAmount = pi_sundry_amount * (-1) - v_CheckingAdditionalClaimFee;
            END IF;

            -- 1.で残金が存在する場合、以下処理を行う
            IF  v_CalculationAmount > 0 THEN
                -- +++++++++++++++++++++++++++++++++++++++++++++
                -- 2. 遅延損害金の消し込み
                -- +++++++++++++++++++++++++++++++++++++++++++++
                -- 計上済みの雑損失－遅延損害金 が 1.の残金 以上の場合
                IF  v_SundryDamageInterestAmount >= v_CalculationAmount THEN
                    -- 消込情報－遅延損害金 は 1.の残金
                    SET v_CheckingDamageInterestAmount = v_CalculationAmount;
                    -- 1.の残金が全て消込情報－遅延損害金 になるので、残金は0（ｾﾞﾛ）
                    SET v_CalculationAmount = 0;
                -- それ以外の場合
                ELSE
                    -- 消込情報－遅延損害金 は 計上済みの雑損失－遅延損害金
                    SET v_CheckingDamageInterestAmount = v_SundryDamageInterestAmount;
                    -- 1.の残金 から 消込情報－遅延損害金 を減算して入金額の残金を取得
                    SET v_CalculationAmount = v_CalculationAmount - v_CheckingDamageInterestAmount;
                END IF;
            END IF;

            -- 2.で残金が存在する場合、以下処理を行う
            IF  v_CalculationAmount > 0 THEN
                -- +++++++++++++++++++++++++++++++++++++++++++++
                -- 3. 請求手数料の消し込み
                -- +++++++++++++++++++++++++++++++++++++++++++++
                -- 計上済みの雑損失－請求手数料 が 2.の残金 以上の場合
                IF  v_SundryClaimFee >= v_CalculationAmount THEN
                    -- 消込情報－請求手数料 は 2.の残金
                    SET v_CheckingClaimFee = v_CalculationAmount;
                    -- 1.の残金が全て消込情報－請求手数料 になるので、残金は0（ｾﾞﾛ）
                    SET v_CalculationAmount = 0;
                -- それ以外の場合
                ELSE
                    -- 消込情報－請求手数料 は 計上済みの雑損失－請求手数料
                    SET v_CheckingClaimFee = v_SundryClaimFee;
                    -- 1.の残金 から 消込情報－請求手数料 を減算して入金額の残金を取得
                    SET v_CalculationAmount = v_CalculationAmount - v_CheckingClaimFee;
                END IF;
            END IF;

            -- 3.で残金が存在する場合、以下処理を行う
            IF  v_CalculationAmount > 0 THEN
                -- +++++++++++++++++++++++++++++++++++++++++++++
                -- 4. 利用額の消し込み
                -- +++++++++++++++++++++++++++++++++++++++++++++
                -- 3.の残金は全額利用額となる。
                SET v_CheckingUseAmount = v_CalculationAmount;
            END IF;

            -- ﾏｲﾅｽ入金の消し込みなので、取得した消込情報に -1 を掛けて ﾏｲﾅｽ値に戻す。
            SET v_CheckingUseAmount = v_CheckingUseAmount * -1;
            SET v_CheckingClaimFee = v_CheckingClaimFee * -1;
            SET v_CheckingDamageInterestAmount = v_CheckingDamageInterestAmount * -1;
            SET v_CheckingAdditionalClaimFee = v_CheckingAdditionalClaimFee * -1;

            -- ++++++++++++++++++++++++++++++++++++++++
            -- 8. 消込情報－消込金額合計 を求める
            -- ++++++++++++++++++++++++++++++++++++++++
            SET v_CheckingClaimAmount = v_CheckingUseAmount + v_CheckingClaimFee + v_CheckingDamageInterestAmount + v_CheckingAdditionalClaimFee;

            -- ------------------------------
            -- 3-6-4.雑損失取り消しﾃﾞｰﾀの作成
            -- ------------------------------
            INSERT
            INTO    T_SundryControl(    ProcessDate                         -- 発生日時
                                    ,   SundryType                          -- 種類（雑収入／雑損失）
                                    ,   SundryAmount                        -- 金額
                                    ,   SundryClass                         -- 雑収入・雑損失科目
                                    ,   OrderSeq                            -- 注文SEQ
                                    ,   OrderId                             -- 注文ID
                                    ,   ClaimId                             -- 請求ID
                                    ,   Note                                -- 備考
                                    ,   CheckingUseAmount                   -- 消込情報－利用額
                                    ,   CheckingClaimFee                    -- 消込情報－請求手数料
                                    ,   CheckingDamageInterestAmount        -- 消込情報－遅延損害金
                                    ,   CheckingAdditionalClaimFee          -- 消込情報－請求追加手数料
                                    ,   DailySummaryFlg                     -- 日次更新ﾌﾗｸﾞ
                                    ,   RegistDate                          -- 登録日時
                                    ,   RegistId                            -- 登録者
                                    ,   UpdateDate                          -- 更新日時
                                    ,   UpdateId                            -- 更新者
                                    ,   ValidFlg                            -- 有効ﾌﾗｸﾞ
                                   )
                                   VALUES
                                   (    pi_process_date                     -- 発生日時
                                    ,   pi_sundry_type                      -- 種類（雑収入／雑損失）
                                    ,   pi_sundry_amount                    -- 金額
                                    ,   pi_sundry_class                     -- 雑収入・雑損失科目
                                    ,   v_OrderSeq                          -- 注文SEQ
                                    ,   pi_order_id                         -- 注文ID
                                    ,   v_ClaimId                           -- 請求ID
                                    ,   NULL                                -- 備考
                                    ,   v_CheckingUseAmount                 -- 消込情報－利用額
                                    ,   v_CheckingClaimFee                  -- 消込情報－請求手数料
                                    ,   v_CheckingDamageInterestAmount      -- 消込情報－遅延損害金
                                    ,   v_CheckingAdditionalClaimFee        -- 消込情報－請求追加手数料
                                    ,   0                                   -- 日次更新ﾌﾗｸﾞ
                                    ,   NOW()                               -- 登録日時
                                    ,   pi_user_id                          -- 登録者
                                    ,   NOW()                               -- 更新日時
                                    ,   pi_user_id                          -- 更新者
                                    ,   1                                   -- 有効ﾌﾗｸﾞ
                                   );

            -- ------------------------------
            -- 3-6-5.請求ﾃﾞｰﾀの更新
            -- ------------------------------
            -- この時点でChecking系、NaSundry系ともにﾏｲﾅｽ値が入っている → 加算する項目は ﾏｲﾅｽ する。減算する項目は ﾌﾟﾗｽ する。
            UPDATE  T_ClaimControl
            SET     ClaimedBalance                  =   ClaimedBalance - v_CheckingClaimAmount - v_NaSundryAmount                                       -- 請求残高
                ,   CheckingClaimAmount             =   CheckingClaimAmount + v_CheckingClaimAmount + v_NaSundryAmount                                  -- 消込情報－消込額合計
                ,   CheckingUseAmount               =   CheckingUseAmount + v_CheckingUseAmount + v_NaSundryUseAmount                                   -- 消込情報－利用額
                ,   CheckingClaimFee                =   CheckingClaimFee + v_CheckingClaimFee + v_NaSundryClaimFee                                      -- 消込情報－請求手数料
                ,   CheckingDamageInterestAmount    =   CheckingDamageInterestAmount + v_CheckingDamageInterestAmount + v_NaSundryDamageInterestAmount  -- 消込情報－遅延損害金
                ,   CheckingAdditionalClaimFee      =   CheckingAdditionalClaimFee + v_CheckingAdditionalClaimFee + v_NaSundryAdditionalClaimFee        -- 消込情報－請求追加手数料
                ,   BalanceClaimAmount              =   BalanceClaimAmount - v_CheckingClaimAmount - v_NaSundryAmount                                   -- 残高情報－残高合計
                ,   BalanceUseAmount                =   BalanceUseAmount - v_CheckingUseAmount - v_NaSundryUseAmount                                    -- 残高情報－利用額
                ,   BalanceClaimFee                 =   BalanceClaimFee - v_CheckingClaimFee - v_NaSundryClaimFee                                       -- 残高情報－請求手数料
                ,   BalanceDamageInterestAmount     =   BalanceDamageInterestAmount - v_CheckingDamageInterestAmount - v_NaSundryDamageInterestAmount   -- 残高情報－遅延損害金
                ,   BalanceAdditionalClaimFee       =   BalanceAdditionalClaimFee - v_CheckingAdditionalClaimFee - v_NaSundryAdditionalClaimFee         -- 残高情報－請求追加手数料
                ,   SundryLossTotal                 =   SundryLossTotal + pi_sundry_amount + v_NaSundryAmount                                           -- 雑損失合計
                ,   UpdateDate                      =   NOW()
                ,   UpdateId                        =   pi_user_id
            WHERE   ClaimId =   v_ClaimId
            ;

            -- ------------------------------
            -- 3-6-6.計上済み雑損失額の相殺 の場合
            -- ------------------------------
            IF  pi_sundry_amount * -1 = v_SundryAmount  THEN
                -- ------------------------------
                -- 3-6-1.注文ﾃﾞｰﾀの更新
                -- ------------------------------
                -- 雑損失の全額相殺の場合、請求残が残る（ﾌﾟﾗｽ値になる）ので、ﾃﾞｰﾀｽﾃｰﾀｽは61（一部入金）へ落とし、ｸﾛｰｽﾞ理由もなかったことにする。
                UPDATE  T_Order
                SET     DataStatus  =   61      -- ﾃﾞｰﾀｽﾃｰﾀｽ（一部入金）
                    ,   CloseReason =   0       -- ｸﾛｰｽﾞ理由（なかったことにする）
                    ,   UpdateDate  =   NOW()
                    ,   UpdateId    =   pi_user_id
                WHERE   P_OrderSeq  =   v_P_OrderSeq
                AND     Cnl_Status  =   0       -- ｷｬﾝｾﾙされていないもの
                ;

            -- ------------------------------
            -- 3-6-7.計上済み雑損失額の一部相殺 の場合
            -- ------------------------------
            ELSEIF  v_SundryAmount > pi_sundry_amount * -1  THEN
                -- ------------------------------
                -- 3-6-7-1.最低請求額 > 入金済み額 + 雑損失額 + 入金額 の場合
                -- ------------------------------
                IF  v_MinClaimAmount > v_ReceiptAmount + v_SundryAmount + pi_sundry_amount  THEN
                    -- ------------------------------
                    -- 3-6-7-1-1.注文ﾃﾞｰﾀの更新
                    -- ------------------------------
                    -- 請求残が残る（最低請求額を満たさない）ので、ﾃﾞｰﾀｽﾃｰﾀｽは61（一部入金）へ落とし、ｸﾛｰｽﾞ理由もなかったことにする。
                    UPDATE  T_Order
                    SET     DataStatus  =   61      -- ﾃﾞｰﾀｽﾃｰﾀｽ（一部入金）
                        ,   CloseReason =   0       -- ｸﾛｰｽﾞ理由（なかったことにする）
                        ,   UpdateDate  =   NOW()
                        ,   UpdateId    =   pi_user_id
                    WHERE   P_OrderSeq  =   v_P_OrderSeq
                    AND     Cnl_Status  =   0       -- ｷｬﾝｾﾙされていないもの
                    ;

                -- ------------------------------
                -- 3-6-7-2.最低請求額 = 入金額 + 雑損失額 の場合
                -- ------------------------------
                ELSEIF  v_MinClaimAmount = v_ReceiptAmount + pi_sundry_amount + v_SundryAmount  THEN
                    -- ------------------------------
                    -- 3-6-7-2-1.最終請求額 > 最低請求額 の場合
                    -- ------------------------------
                    IF  v_ClaimAmount > v_MinClaimAmount    THEN
                        -- この時点でChecking系はﾏｲﾅｽ値が入っている → 加算する項目は ﾏｲﾅｽ する。減算する項目は ﾌﾟﾗｽ する。
                        -- ------------------------------
                        -- 3-6-7-2-1-1.会計対象外雑損失情報の取得
                        -- ------------------------------
                        -- 最低請求額と最終請求額の差分が家計対象外雑損失になる。
                        -- 1) 利用額
                        SET v_NaSundryUseAmount = v_UseAmountTotal - v_MinUseAmount;
                        -- 2) 請求手数料
                        SET v_NaSundryClaimFee = v_ClaimFee - v_MinClaimFee;
                        -- 3) 遅延損害金
                        SET v_NaSundryDamageInterestAmount = v_DamageInterestAmount - v_MinDamageInterestAmount;
                        -- 4) 請求追加手数料
                        SET v_NaSundryAdditionalClaimFee = v_AdditionalClaimFee - v_MinAdditionalClaimFee;
                        -- 5) 合計
                        SET v_NaSundryAmount = v_NaSundryUseAmount + v_NaSundryClaimFee + v_NaSundryDamageInterestAmount + v_NaSundryAdditionalClaimFee;
                        
                        -- ------------------------------
                        -- 3-6-7-2-1-2.会計対象外雑損失ﾃﾞｰﾀの作成
                        -- ------------------------------
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
                                                ,   DailySummaryFlg                 -- 日次更新ﾌﾗｸﾞ
                                                ,   RegistDate                      -- 登録日時
                                                ,   RegistId                        -- 登録者
                                                ,   UpdateDate                      -- 更新日時
                                                ,   UpdateId                        -- 更新者
                                                ,   ValidFlg                        -- 有効ﾌﾗｸﾞ
                                               )
                                               VALUES
                                               (    DATE(NOW())                     -- 発生日時
                                                ,   1                               -- 種類（雑収入／雑損失）
                                                ,   v_NaSundryAmount                -- 金額
                                                ,   99                              -- 雑収入・雑損失科目
                                                ,   v_OrderSeq                      -- 注文SEQ
                                                ,   pi_order_id                     -- 注文ID
                                                ,   v_ClaimId                       -- 請求ID
                                                ,   NULL                            -- 備考
                                                ,   v_NaSundryUseAmount             -- 消込情報－利用額
                                                ,   v_NaSundryClaimFee              -- 消込情報－請求手数料
                                                ,   v_NaSundryDamageInterestAmount  -- 消込情報－遅延損害金
                                                ,   v_NaSundryAdditionalClaimFee    -- 消込情報－請求追加手数料
                                                ,   0                               -- 日次更新ﾌﾗｸﾞ
                                                ,   NOW()                           -- 登録日時
                                                ,   pi_user_id                      -- 登録者
                                                ,   NOW()                           -- 更新日時
                                                ,   pi_user_id                      -- 更新者
                                                ,   1                               -- 有効ﾌﾗｸﾞ
                                               );
                    END IF;

                    -- ------------------------------
                    -- 3-6-7-2-1-3.請求ﾃﾞｰﾀの更新
                    -- ------------------------------
                    -- 会計対象外雑損失として計上した分を再計算する。
                    UPDATE  T_ClaimControl
                    SET     ClaimedBalance                  =   ClaimedBalance - v_NaSundryAmount                               -- 請求残高
                        ,   CheckingClaimAmount             =   CheckingClaimAmount + v_NaSundryAmount                          -- 消込情報－消込額合計
                        ,   CheckingUseAmount               =   CheckingUseAmount + v_NaSundryUseAmount                         -- 消込情報－利用額
                        ,   CheckingClaimFee                =   CheckingClaimFee + v_NaSundryClaimFee                           -- 消込情報－請求手数料
                        ,   CheckingDamageInterestAmount    =   CheckingDamageInterestAmount + v_NaSundryDamageInterestAmount   -- 消込情報－遅延損害金
                        ,   CheckingAdditionalClaimFee      =   CheckingAdditionalClaimFee + v_NaSundryAdditionalClaimFee       -- 消込情報－請求追加手数料
                        ,   BalanceClaimAmount              =   BalanceClaimAmount - v_NaSundryAmount                           -- 残高情報－残高合計
                        ,   BalanceUseAmount                =   BalanceUseAmount - v_NaSundryUseAmount                          -- 残高情報－利用額
                        ,   BalanceClaimFee                 =   BalanceClaimFee - v_NaSundryClaimFee                            -- 残高情報－請求手数料
                        ,   BalanceDamageInterestAmount     =   BalanceDamageInterestAmount - v_NaSundryDamageInterestAmount    -- 残高情報－遅延損害金
                        ,   BalanceAdditionalClaimFee       =   BalanceAdditionalClaimFee - v_NaSundryAdditionalClaimFee        -- 残高情報－請求追加手数料
                        ,   SundryLossTotal                 =   SundryLossTotal + v_NaSundryAmount                              -- 雑損失合計
                        ,   UpdateDate                      =   NOW()
                        ,   UpdateId                        =   pi_user_id
                    WHERE   ClaimId =   v_ClaimId
                    ;

                -- ------------------------------
                -- 3-6-4.最終請求額 > 入金額 + 雑損失額 > 最低請求額 の場合
                -- ------------------------------
                ELSEIF	v_ClaimAmount > v_ReceiptAmount + pi_sundry_amount + v_SundryAmount AND
                        v_ReceiptAmount + pi_sundry_amount + v_SundryAmount > v_MinClaimAmount   THEN
                    -- ------------------------------
                    -- 3-6-4-1.会計対象外雑損失情報の取得
                    -- ------------------------------
                    -- 1) 利用額
                    SET v_NaSundryUseAmount = v_UseAmountTotal - (v_ReceiptUseAmount + v_CheckingUseAmount + v_SundryUseAmount);
                    -- 2) 請求手数料
                    SET v_NaSundryClaimFee = v_ClaimFee - (v_ReceiptClaimFee + v_CheckingClaimFee + v_SundryClaimFee);
                    -- 3) 遅延損害金
                    SET v_NaSundryDamageInterestAmount = v_DamageInterestAmount - (v_ReceiptDamageInterestAmount + v_CheckingDamageInterestAmount + v_SundryDamageInterestAmount);
                    -- 4) 請求追加手数料
                    SET v_NaSundryAdditionalClaimFee = v_AdditionalClaimFee - (v_ReceiptAdditionalClaimFee + v_CheckingAdditionalClaimFee + v_SundryAdditionalClaimFee);
                    -- 5) 合計
                    SET v_NaSundryAmount = v_NaSundryUseAmount + v_NaSundryClaimFee + v_NaSundryDamageInterestAmount + v_NaSundryAdditionalClaimFee;

                    -- ------------------------------
                    -- 3-6-4-2.会計対象外雑損失ﾃﾞｰﾀの作成
                    -- ------------------------------
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
                                            ,   DailySummaryFlg                 -- 日次更新ﾌﾗｸﾞ
                                            ,   RegistDate                      -- 登録日時
                                            ,   RegistId                        -- 登録者
                                            ,   UpdateDate                      -- 更新日時
                                            ,   UpdateId                        -- 更新者
                                            ,   ValidFlg                        -- 有効ﾌﾗｸﾞ
                                           )
                                           VALUES
                                           (    DATE(NOW())                     -- 発生日時
                                            ,   1                               -- 種類（雑収入／雑損失）
                                            ,   v_NaSundryAmount                -- 金額
                                            ,   99                              -- 雑収入・雑損失科目
                                            ,   v_OrderSeq                      -- 注文SEQ
                                            ,   pi_order_id                     -- 注文ID
                                            ,   v_ClaimId                       -- 請求ID
                                            ,   NULL                            -- 備考
                                            ,   v_NaSundryUseAmount             -- 消込情報－利用額
                                            ,   v_NaSundryClaimFee              -- 消込情報－請求手数料
                                            ,   v_NaSundryDamageInterestAmount  -- 消込情報－遅延損害金
                                            ,   v_NaSundryAdditionalClaimFee    -- 消込情報－請求追加手数料
                                            ,   0                               -- 日次更新ﾌﾗｸﾞ
                                            ,   NOW()                           -- 登録日時
                                            ,   pi_user_id                      -- 登録者
                                            ,   NOW()                           -- 更新日時
                                            ,   pi_user_id                      -- 更新者
                                            ,   1                               -- 有効ﾌﾗｸﾞ
                                           );

                    -- ------------------------------
                    -- 3-6-4-3.請求ﾃﾞｰﾀの更新
                    -- ------------------------------
                    -- この時点でChecking系、NaSundry系ともにﾏｲﾅｽ値が入っている → 加算する項目は ﾏｲﾅｽ する。減算する項目は ﾌﾟﾗｽ する。
                    UPDATE  T_ClaimControl
                    SET     ClaimedBalance                  =   ClaimedBalance - v_NaSundryAmount                               -- 請求残高
                        ,   CheckingClaimAmount             =   CheckingClaimAmount + v_NaSundryAmount                          -- 消込情報－消込額合計
                        ,   CheckingUseAmount               =   CheckingUseAmount + v_NaSundryUseAmount                         -- 消込情報－利用額
                        ,   CheckingClaimFee                =   CheckingClaimFee + v_NaSundryClaimFee                           -- 消込情報－請求手数料
                        ,   CheckingDamageInterestAmount    =   CheckingDamageInterestAmount + v_NaSundryDamageInterestAmount   -- 消込情報－遅延損害金
                        ,   CheckingAdditionalClaimFee      =   CheckingAdditionalClaimFee + v_NaSundryAdditionalClaimFee       -- 消込情報－請求追加手数料
                        ,   BalanceClaimAmount              =   BalanceClaimAmount - v_NaSundryAmount                           -- 残高情報－残高合計
                        ,   BalanceUseAmount                =   BalanceUseAmount - v_NaSundryUseAmount                          -- 残高情報－利用額
                        ,   BalanceClaimFee                 =   BalanceClaimFee - v_NaSundryClaimFee                            -- 残高情報－請求手数料
                        ,   BalanceDamageInterestAmount     =   BalanceDamageInterestAmount - v_NaSundryDamageInterestAmount    -- 残高情報－遅延損害金
                        ,   BalanceAdditionalClaimFee       =   BalanceAdditionalClaimFee - v_NaSundryAdditionalClaimFee        -- 残高情報－請求追加手数料
                        ,   SundryLossTotal                 =   SundryLossTotal + v_NaSundryAmount                              -- 雑損失合計
                        ,   UpdateDate                      =   NOW()
                        ,   UpdateId                        =   pi_user_id
                    WHERE   ClaimId =   v_ClaimId
                    ;
                END IF;
            END IF;
        END IF;
    END IF;
END
$$

DELIMITER ;

