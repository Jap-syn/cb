DROP procedure IF EXISTS `procMigrateClaimControl`;

DELIMITER $$
CREATE PROCEDURE `procMigrateClaimControl`()
BEGIN

    DECLARE updDttm datetime;
    
    SET updDttm = now();
CALL var_dump('insert' , 'Start');
    -- T_ClaimControlへのINSERT　！！共通部 詳細値は一旦デフォルト値　後ろでUPDATE処理により値を上書き！！
    INSERT INTO `T_ClaimControl`(
            `ClaimId`,              -- 請求ID
            `OrderSeq`, -- 注文SEQ
            `EntCustSeq`, -- 加盟店顧客SEQ
            `ClaimDate`, -- 請求日
            `ClaimCpId`, -- 請求担当者
            `ClaimPattern`, -- 請求パターン
            `LimitDate`, -- 支払期限
            `UseAmountTotal`, -- 利用額合計
            `DamageDays`, -- 遅延日数
            `DamageBaseDate`, -- 遅延日数算出基準日
            `DamageInterestAmount`, -- 遅延損害金
            `ClaimFee`, -- 請求手数料（正確には再請求手数料）
            `AdditionalClaimFee`, -- 請求追加手数料
            `PrintedDate`, -- 印刷－印刷日時
            `ClaimAmount`, -- 請求金額
            `ReceiptAmountTotal`, -- 入金額合計
            `RepayAmountTotal`, -- 返金額合計
            `SundryLossTotal`, -- 雑損失合計
            `SundryIncomeTotal`, -- 雑収入合計
            `ClaimedBalance`, -- 請求残高
            `SundryLossTarget`, -- 雑損失対象区分
            `SundryIncomeTarget`, -- 雑収入対象区分
            `Clm_Count`, -- 請求回数（初回含む）
            `F_ClaimDate`,  -- 初回－請求日
            `F_OpId`, -- 初回－請求担当者
            `F_LimitDate`, -- 初回－支払期限
            `F_ClaimAmount`, -- 初回請求金額
            `Re1_ClaimAmount`, -- 再１請求金額
            `Re3_ClaimAmount`, -- 再３請求金額
            `AutoSundryStatus`, -- 自動雑損失計上ステータス
            `ReissueClass`, -- 初回請求再発行区分
            `ReissueRequestDate`, -- 初回請求書再発行申請日
            `LastProcessDate`, -- 最終入金処理日
            `LastReceiptSeq`, -- 最終入金SEQ
            `MinClaimAmount`, -- 最低請求情報－請求金額
            `MinUseAmount`, -- 最低請求情報－利用額
            `MinClaimFee`, -- 最低請求情報－請求手数料
            `MinDamageInterestAmount`, -- 最低請求情報－遅延損害金
            `MinAdditionalClaimFee`, -- 最低請求情報－請求追加手数料
            `CheckingClaimAmount`, -- 消込情報－消込金額合計
            `CheckingUseAmount`, -- 消込情報－利用額
            `CheckingClaimFee`, -- 消込情報－請求手数料
            `CheckingDamageInterestAmount`, -- 消込情報－遅延損害金
            `CheckingAdditionalClaimFee`, -- 消込情報－請求追加手数料
            `BalanceClaimAmount`, -- 残高情報－残高合計
            `BalanceUseAmount`, -- 残高情報－利用額
            `BalanceClaimFee`, -- 残高情報－請求手数料
            `BalanceDamageInterestAmount`, -- 残高情報－遅延損害金
            `BalanceAdditionalClaimFee`, -- 残高情報－請求追加手数料
            `MypageReissueClass`, -- マイページ請求書再発行区分
            `MypageReissueRequestDate`, -- マイページ請求書再発行申請日
            `MypageReissueDate`, -- マイページ請求書再発行日
            `RegistDate`, -- 登録日時
            `RegistId`, -- 登録者
            `UpdateDate`, -- 更新日時
            `UpdateId`, -- 更新者
            `ValidFlg` -- 有効フラグ
        ) 
            SELECT
                `T_Order`.`OrderSeq`, -- 請求ID
                `T_Order`.`OrderSeq`, -- 注文SEQ
                NULL, -- 加盟店顧客SEQ
                `T_Order`.`Clm_L_ClaimDate`, -- 請求日
                `T_Order`.`Clm_L_OpId`, -- 請求担当者
                IFNULL(`T_Order`.`Clm_L_ClaimPattern`, 1), -- 請求パターン
                `T_Order`.`Clm_L_LimitDate`, -- 支払期限
                IFNULL(`T_Order`.`UseAmount`, 0), -- 利用額合計
                `T_Order`.`Clm_L_DamageDays`, -- 遅延日数
                `T_Order`.`Clm_L_DamageBaseDate`, -- 遅延日数算出基準日
                IFNULL(`T_Order`.`Clm_L_DamageInterestAmount`, 0), -- 遅延損害金
                IFNULL(`T_Order`.`Clm_L_ClaimFee`, 0), -- 請求手数料（正確には再請求手数料）
                IFNULL(`T_Order`.`Clm_L_AdditionalClaimFee`, 0), -- 請求追加手数料
                T_ClaimHistory.PrintedDate, -- 印刷－印刷日時
                0, -- 請求金額 UPDATE対象!!
                IFNULL(`T_Order`.`Rct_ReceiptAmount`, 0), -- 入金額合計
                0, -- 返金額合計
                CASE `T_Order`.`DataStatus` = 91 AND (`T_Order`.`UseAmount` + `T_Order`.`Clm_L_ClaimFee` + `T_Order`.`Clm_L_DamageInterestAmount`) > `T_Order`.`Rct_ReceiptAmount`
                    WHEN true THEN `T_Order`.`UseAmount` + `T_Order`.`Clm_L_ClaimFee` + `T_Order`.`Clm_L_DamageInterestAmount` - `T_Order`.`Rct_ReceiptAmount`
                    ELSE 0
                END, -- 雑損失合計
                CASE `T_Order`.`DataStatus` = 91 AND (`T_Order`.`UseAmount` + `T_Order`.`Clm_L_ClaimFee` + `T_Order`.`Clm_L_DamageInterestAmount`) < `T_Order`.`Rct_ReceiptAmount`
                    WHEN TRUE THEN `T_Order`.`Rct_ReceiptAmount` - `T_Order`.`UseAmount` + `T_Order`.`Clm_L_ClaimFee` + `T_Order`.`Clm_L_DamageInterestAmount`
                    ELSE 0
                END, -- 雑収入合計
                CASE `T_Order`.`DataStatus` = 91
                    WHEN FALSE THEN IFNULL(`T_Order`.`UseAmount`, 0) + IFNULL(`T_Order`.`Clm_L_ClaimFee`, 0) + IFNULL(`T_Order`.`Clm_L_DamageInterestAmount`, 0)
                    ELSE 0
                END, -- 請求残高
                0, -- 雑損失対象区分
                0, -- 雑収入対象区分
                `T_Order`.`Clm_Count`, -- 請求回数（初回含む）
                `T_Order`.`Clm_F_ClaimDate`, -- 初回－請求日
                `T_Order`.`Clm_F_OpId`, -- 初回－請求担当者
                `T_Order`.`Clm_F_LimitDate`, -- 初回－支払期限
                NULL, -- 初回請求金額 UPDATE対象!!
                NULL, -- 再１請求金額 UPDATE対象!!
                NULL, -- 再３請求金額 UPDATE対象!!
                0, -- 自動雑損失計上ステータス
                0, -- 初回請求再発行区分
                NULL, -- 初回請求書再発行申請日
                `T_Order`.`Rct_ReceiptProcessDate`, -- 最終入金処理日
                NULL, -- 最終入金SEQ
                0, -- 最低請求情報－請求金額 UPDATE対象!!
                0, -- 最低請求情報－利用額 UPDATE対象!!
                0, -- 最低請求情報－請求手数料 UPDATE対象!!
                0, -- 最低請求情報－遅延損害金 UPDATE対象!!
                0, -- 最低請求情報－請求追加手数料 UPDATE対象!!
                0, -- 消込情報－消込金額合計 UPDATE対象!!
                0, -- 消込情報－利用額 UPDATE対象!!
                0, -- 消込情報－請求手数料 UPDATE対象!!
                0, -- 消込情報－遅延損害金 UPDATE対象!!
                0, -- 消込情報－請求追加手数料 UPDATE対象!!
                0, -- 残高情報－残高合計 UPDATE対象!!
                0, -- 残高情報－利用額 UPDATE対象!!
                0, -- 残高情報－請求手数料 UPDATE対象!!
                0, -- 残高情報－遅延損害金 UPDATE対象!!
                0, -- 残高情報－請求追加手数料 UPDATE対象!!
                0, -- マイページ請求書再発行区分
                NULL, -- マイページ請求書再発行申請日
                NULL, -- マイページ請求書再発行日
                updDttm, -- 登録日時
                9, -- 登録者
                updDttm,-- 更新日時
                9, -- 更新者
                1 -- 有効フラグ
/*            FROM `coraldb_ikou`.`T_Order`
            LEFT OUTER JOIN `coraldb_ikou`.`T_ClaimHistory` */
            FROM `coraldb_ikou`.`T_ClaimHistory`
            INNER JOIN `coraldb_ikou`.`T_Order`
            ON `T_Order`.OrderSeq = `T_ClaimHistory`.OrderSeq
            AND `T_ClaimHistory`.ClaimSeq IN (SELECT MAX(ClaimSeq) FROM `coraldb_ikou`.`T_ClaimHistory` `ch` WHERE `T_Order`.OrderSeq = OrderSeq AND PrintedFlg = 1) -- ジョブ転送中の場合は除外する
            ;


    -- ！！UPDATE！！
    
    -- 請求金額　v_useAmount（利用額合計） + v_clm_L_ClaimFee(請求手数料（正確には再請求手数料）) + v_clm_L_DamageInterestAmount(遅延損害金) + v_clm_L_AdditionalClaimFee(請求追加手数料)
    -- 最低請求情報についても、一旦は最終をセットしておく(20150905_0944_suzuki_h)
    UPDATE T_ClaimControl cc
    SET ClaimAmount                 =  UseAmountTotal + ClaimFee + DamageInterestAmount + AdditionalClaimFee
        ,MinUseAmount               =  UseAmountTotal                                                           -- 最低請求情報－利用額
        ,MinClaimFee                =  ClaimFee                                                                 -- 最低請求情報－請求手数料
        ,MinDamageInterestAmount    =  DamageInterestAmount                                                     -- 最低請求情報－遅延損害金
        ,MinAdditionalClaimFee      =  AdditionalClaimFee                                                       -- 最低請求情報－請求追加手数料
        ,F_ClaimAmount = cc.UseAmountTotal + (SELECT (ch.DamageInterestAmount + ch.ClaimFee) FROM coraldb_ikou.T_ClaimHistory ch WHERE cc.OrderSeq = ch.OrderSeq AND ch.ClaimPattern = 1 AND PrintedFlg = 1 ORDER BY ch.ClaimSeq DESC LIMIT 1)  -- 初回請求金額
        ,Re1_ClaimAmount = cc.UseAmountTotal + (SELECT (ch.DamageInterestAmount + ch.ClaimFee) FROM coraldb_ikou.T_ClaimHistory ch WHERE cc.OrderSeq = ch.OrderSeq AND ch.ClaimPattern = 2 AND PrintedFlg = 1 ORDER BY ch.ClaimSeq DESC LIMIT 1) -- 再１請求金額
        ,Re3_ClaimAmount = cc.UseAmountTotal + (SELECT (ch.DamageInterestAmount + ch.ClaimFee) FROM coraldb_ikou.T_ClaimHistory ch WHERE cc.OrderSeq = ch.OrderSeq AND ch.ClaimPattern = 4 AND PrintedFlg = 1 ORDER BY ch.ClaimSeq DESC LIMIT 1) -- 再３請求金額
    ;
    
    -- 再１～２の場合は、初回をセットする
    UPDATE T_ClaimControl cc
    SET -- cc.MinUseAmount = cc.UseAmountTotal, -- これは初期セットしているので更新不要
        cc.MinClaimFee = (SELECT (ch.ClaimFee) FROM coraldb_ikou.T_ClaimHistory ch WHERE cc.OrderSeq = ch.OrderSeq AND ch.ClaimPattern = 1 AND PrintedFlg = 1 ORDER BY ch.ClaimSeq DESC LIMIT 1),
        cc.MinDamageInterestAmount = (SELECT (ch.DamageInterestAmount) FROM coraldb_ikou.T_ClaimHistory ch WHERE cc.OrderSeq = ch.OrderSeq AND ch.ClaimPattern = 1 AND PrintedFlg = 1 ORDER BY ch.ClaimSeq DESC LIMIT 1),
        cc.MinAdditionalClaimFee = 0  -- 0しかありえないのでみなす
    WHERE cc.ClaimPattern BETWEEN 1 AND 3;
    
    -- 再３～４の場合は、再１をセットする
    UPDATE T_ClaimControl cc
    SET -- cc.MinUseAmount = cc.UseAmountTotal, -- これは初期セットしているので更新不要
        cc.MinClaimFee = (SELECT (ch.ClaimFee) FROM coraldb_ikou.T_ClaimHistory ch WHERE cc.OrderSeq = ch.OrderSeq AND ch.ClaimPattern = 2 AND PrintedFlg = 1 ORDER BY ch.ClaimSeq DESC LIMIT 1),
        cc.MinDamageInterestAmount = (SELECT (ch.DamageInterestAmount) FROM coraldb_ikou.T_ClaimHistory ch WHERE cc.OrderSeq = ch.OrderSeq AND ch.ClaimPattern = 2 AND PrintedFlg = 1 ORDER BY ch.ClaimSeq DESC LIMIT 1),
        cc.MinAdditionalClaimFee = 0  -- 0しかありえないのでみなす
    WHERE cc.ClaimPattern BETWEEN 4 AND 6;
    
    -- 請求パターン５以降の場合は、再３をセットする
    UPDATE T_ClaimControl cc
    SET -- cc.MinUseAmount = cc.UseAmountTotal, -- これは初期セットしているので更新不要
        cc.MinClaimFee = (SELECT (ch.ClaimFee) FROM coraldb_ikou.T_ClaimHistory ch WHERE cc.OrderSeq = ch.OrderSeq AND ch.ClaimPattern = 4 AND PrintedFlg = 1 ORDER BY ch.ClaimSeq DESC LIMIT 1),
        cc.MinDamageInterestAmount = (SELECT (ch.DamageInterestAmount) FROM coraldb_ikou.T_ClaimHistory ch WHERE cc.OrderSeq = ch.OrderSeq AND ch.ClaimPattern = 4 AND PrintedFlg = 1 ORDER BY ch.ClaimSeq DESC LIMIT 1),
        cc.MinAdditionalClaimFee = 0  -- 0しかありえないのでみなす
    WHERE cc.ClaimPattern >= 7;
    
    -- 最低請求金額を再セットする
    UPDATE T_ClaimControl cc
    SET cc.MinClaimAmount             =  MinUseAmount + MinClaimFee + MinDamageInterestAmount + MinAdditionalClaimFee    -- 最低請求情報－請求金額
    ;
    -- ---------------------------------
    -- 一部入金は最後の移行プログラムにて行う。入金用PLSQLをコールして厳密に作成する
    -- ---------------------------------
    -- DataStatus = 91:クローズ
    -- 消込情報－利用額, 消込情報－請求手数料, 消込情報－遅延損害金, 消込情報－請求追加手数料
    UPDATE `T_ClaimControl`
    LEFT OUTER JOIN `coraldb_ikou`.`T_Order`
    ON `T_ClaimControl`.OrderSeq = `T_Order`.OrderSeq
    SET `T_ClaimControl`.`CheckingUseAmount` = IFNULL(`T_Order`.`UseAmount`, 0),
        `T_ClaimControl`.CheckingClaimFee = IFNULL(`T_Order`.Clm_L_ClaimFee, 0),
        `T_ClaimControl`.CheckingDamageInterestAmount = IFNULL(`T_Order`.Clm_L_DamageInterestAmount, 0),
        `T_ClaimControl`.CheckingAdditionalClaimFee = IFNULL(`T_Order`.Clm_L_AdditionalClaimFee, 0)
    WHERE `T_Order`.DataStatus = 91;

    -- 消込情報－消込金額合計
    UPDATE `T_ClaimControl`
    LEFT OUTER JOIN `coraldb_ikou`.`T_Order`
    ON `T_ClaimControl`.OrderSeq = `T_Order`.OrderSeq
    SET `T_ClaimControl`.CheckingClaimAmount = CheckingUseAmount + CheckingClaimFee + CheckingDamageInterestAmount + CheckingAdditionalClaimFee
    WHERE `T_Order`.DataStatus = 91;
    
    -- 残高情報－残高合計, 残高情報－利用額, 残高情報－請求手数料, 残高情報－遅延損害金, 残高情報－請求追加手数料
    UPDATE `T_ClaimControl`
    LEFT OUTER JOIN `coraldb_ikou`.`T_Order`
    ON `T_ClaimControl`.OrderSeq = `T_Order`.OrderSeq
    SET BalanceClaimAmount = ClaimAmount - CheckingClaimAmount,
        BalanceUseAmount = UseAmountTotal - CheckingUseAmount,
        BalanceClaimFee = ClaimFee - CheckingClaimFee,
        BalanceDamageInterestAmount = DamageInterestAmount - CheckingDamageInterestAmount,
        BalanceAdditionalClaimFee = AdditionalClaimFee - CheckingAdditionalClaimFee
    WHERE `T_Order`.DataStatus = 91;

/* ↓初期セットで０にしているので不要
    -- 一部入金無し（データステータス=<51(入金確認待ち) AND　顧客入金－入金額が0）
    -- 消込情報－利用額, 消込情報－請求手数料, 消込情報－遅延損害金, 消込情報－請求追加手数料
    UPDATE `T_ClaimControl`
    LEFT OUTER JOIN `coraldb_ikou`.`T_Order`
    ON `T_ClaimControl`.OrderSeq = `T_Order`.OrderSeq
    SET `T_ClaimControl`.`CheckingUseAmount` = 0,
        `T_ClaimControl`.CheckingClaimFee = 0,
        `T_ClaimControl`.CheckingDamageInterestAmount = 0,
        `T_ClaimControl`.CheckingAdditionalClaimFee = 0
    WHERE `T_Order`.DataStatus <= 51 AND `T_Order`.Rct_ReceiptAmount = 0;

CALL var_dump('Update15' , 'Start');
    -- 消込情報－消込金額合計
    UPDATE `T_ClaimControl`
    LEFT OUTER JOIN `coraldb_ikou`.`T_Order`
    ON `T_ClaimControl`.OrderSeq = `T_Order`.OrderSeq
    SET `T_ClaimControl`.CheckingClaimAmount = 0
    WHERE `T_Order`.DataStatus <= 51 AND `T_Order`.Rct_ReceiptAmount = 0;
*/

    -- 残高情報－残高合計, 残高情報－利用額, 残高情報－請求手数料, 残高情報－遅延損害金, 残高情報－請求追加手数料
    UPDATE `T_ClaimControl`
    LEFT OUTER JOIN `coraldb_ikou`.`T_Order`
    ON `T_ClaimControl`.OrderSeq = `T_Order`.OrderSeq
    SET BalanceClaimAmount = ClaimAmount - CheckingClaimAmount,
        BalanceUseAmount = UseAmountTotal - CheckingUseAmount,
        BalanceClaimFee = ClaimFee - CheckingClaimFee,
        BalanceDamageInterestAmount = DamageInterestAmount - CheckingDamageInterestAmount,
        BalanceAdditionalClaimFee = AdditionalClaimFee - CheckingAdditionalClaimFee
    WHERE `T_Order`.DataStatus <= 51 AND `T_Order`.Rct_ReceiptAmount = 0;

/*
    -- 一部入金有（データステータス＝＜51 AND　顧客入金－入金額＜＞0　の場合）
    -- かつ 顧客入金－入金額＜最低請求情報－請求金額
    -- 消込情報－消込金額合計, 消込情報－利用額, 消込情報－請求手数料, 消込情報－遅延損害金, 消込情報－請求追加手数料
    UPDATE `T_ClaimControl`
    LEFT OUTER JOIN `coraldb_ikou`.`T_Order`
    ON `T_ClaimControl`.OrderSeq = `T_Order`.OrderSeq
    SET CheckingClaimAmount = `T_Order`.Rct_ReceiptAmount,
        CheckingUseAmount = 
            (CASE WHEN (MinClaimAmount + MinDamageInterestAmount) >= `T_Order`.Rct_ReceiptAmount THEN 0
                ELSE `T_Order`.Rct_ReceiptAmount - (MinClaimFee + MinDamageInterestAmount)
            END),
        CheckingClaimFee = 
            (CASE WHEN MinClaimFee <= `T_Order`.Rct_ReceiptAmount THEN `T_Order`.Rct_ReceiptAmount
                ELSE MinClaimFee
            END),
        CheckingDamageInterestAmount = 
            (CASE WHEN MinClaimFee >= `T_Order`.Rct_ReceiptAmount THEN 0
                WHEN MinClaimFee + MinDamageInterestAmount <= `T_Order`.Rct_ReceiptAmount THEN `T_Order`.Rct_ReceiptAmount - CheckingClaimFee
                ELSE MinDamageInterestAmount
            END),
        CheckingAdditionalClaimFee = 0
        WHERE `T_Order`.DataStatus <= 51 AND `T_Order`.Rct_ReceiptAmount <> 0 AND `T_Order`.Rct_ReceiptAmount < `T_ClaimControl`.MinClaimAmount;


    -- 一部入金有（データステータス＝＜51 AND　顧客入金－入金額＜＞0　の場合）
    -- かつ 顧客入金－入金額＞＝最低請求情報－請求金額
    -- 消込情報－消込金額合計, 消込情報－利用額, 消込情報－請求手数料, 消込情報－遅延損害金, 消込情報－請求追加手数料
    UPDATE `T_ClaimControl`
    LEFT OUTER JOIN `coraldb_ikou`.`T_Order`
    ON `T_ClaimControl`.OrderSeq = `T_Order`.OrderSeq
    SET CheckingClaimAmount = `T_Order`.Rct_ReceiptAmount,
        CheckingUseAmount = `T_Order`.UseAmount,
        CheckingClaimFee = 
            (CASE WHEN ClaimFee <= (`T_Order`.Rct_ReceiptAmount - MinClaimAmount) THEN ClaimFee
                ELSE `T_Order`.Rct_ReceiptAmount - MinClaimAmount
            END),
        CheckingDamageInterestAmount = 
            (CASE WHEN ClaimFee <= (`T_Order`.Rct_ReceiptAmount - MinClaimAmount) THEN `T_Order`.Rct_ReceiptAmount - MinClaimAmount
                ELSE 0
            END),
        CheckingAdditionalClaimFee = 0
        WHERE `T_Order`.DataStatus <= 51 AND `T_Order`.Rct_ReceiptAmount <> 0 AND `T_Order`.Rct_ReceiptAmount >= `T_ClaimControl`.MinClaimAmount;


CALL var_dump('Update19' , 'Start');
    -- 一部入金有（データステータス＝＜51 AND　顧客入金－入金額＜＞0　の場合）
    -- 残高情報－残高合計, 残高情報－利用額, 残高情報－請求手数料, 残高情報－遅延損害金, 残高情報－請求追加手数料
    UPDATE `T_ClaimControl`
    LEFT OUTER JOIN `coraldb_ikou`.`T_Order`
    ON `T_ClaimControl`.OrderSeq = `T_Order`.OrderSeq
    SET BalanceClaimAmount = ClaimAmount - CheckingClaimAmount,
        BalanceUseAmount = UseAmountTotal - CheckingUseAmount,
        BalanceClaimFee = ClaimFee - CheckingClaimFee,
        BalanceDamageInterestAmount = DamageInterestAmount - CheckingDamageInterestAmount,
        BalanceAdditionalClaimFee = AdditionalClaimFee - CheckingClaimFee
    WHERE `T_Order`.DataStatus <= 51 AND `T_Order`.Rct_ReceiptAmount <> 0 AND `T_Order`.Rct_ReceiptAmount < `T_ClaimControl`.MinClaimAmount;
*/

END
$$

DELIMITER ;
