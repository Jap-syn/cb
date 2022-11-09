DROP procedure IF EXISTS `procMigrateAllTable_Kirikaebi`;

DELIMITER $$
CREATE PROCEDURE `procMigrateAllTable_Kirikaebi` ()
BEGIN
/* 2015-05-28 27日終了時点のソースから作成。5月28日以降に追加されたものがある場合は、要追加 */
/* 2015-08-13 */
--  S_General_汎用シーケンス
    CALL var_dump('procMigrateGeneral' , 'Start');
    CALL procMigrateGeneral();
    CALL var_dump('procMigrateGeneral' , 'End');
--  T_ApiUser_APIユーザー
    CALL var_dump('procMigrateApiUser' , 'Start');
    CALL procMigrateApiUser();
    CALL var_dump('procMigrateApiUser' , 'End');
--  T_ApiUserEnterprise_APIユーザー加盟店
    CALL var_dump('procMigrateApiUserEnterprise' , 'Start');
    CALL procMigrateApiUserEnterprise();
    CALL var_dump('procMigrateApiUserEnterprise' , 'End');
--  T_Cancel_キャンセル管理
    CALL var_dump('procMigrateCancel' , 'Start');
    CALL procMigrateCancel();
    CALL var_dump('procMigrateCancel' , 'End');
--  T_CjMailHistory_与信結果メール履歴
    CALL var_dump('procMigrateCjMailHistoryB' , 'Start');
    CALL procMigrateCjMailHistoryB();
    CALL var_dump('procMigrateCjMailHistoryB' , 'End');
--  T_CjResult_与信審査結果  
    CALL var_dump('procMigrateCjResultB' , 'Start');
    CALL procMigrateCjResultB();
    CALL var_dump('procMigrateCjResultB' , 'End');
--  T_CjResult_Detail_与信審査結果詳細
    CALL var_dump('procMigrateCjResultDetailB' , 'Start');
    CALL procMigrateCjResultDetailB();
    CALL var_dump('procMigrateCjResultDetailB' , 'End');
--  T_CjResult_Error_与信審査結果エラー
    CALL var_dump('procMigrateCjResultError' , 'Start');
    CALL procMigrateCjResultError();
    CALL var_dump('procMigrateCjResultError' , 'End');
--  T_ClaimControl_請求管理①  
    CALL var_dump('procMigrateClaimControl' , 'Start');
    CALL procMigrateClaimControl();
    CALL var_dump('procMigrateClaimControl' , 'End');
--  T_ClaimHistory_請求履歴
    CALL var_dump('procMigrateClaimHistoryB' , 'Start');
    CALL procMigrateClaimHistoryB();
    CALL var_dump('procMigrateClaimHistoryB' , 'End');
--  T_CreditCondition_社内与信条件
    CALL var_dump('procMigrateCreditConditionB' , 'Start');
    CALL procMigrateCreditConditionB();
    CALL var_dump('procMigrateCreditConditionB' , 'End');
--  T_CreditJudgeThreshold_与信閾値
    CALL var_dump('procMigrateCreditJudgeThreshold' , 'Start');
    CALL procMigrateCreditJudgeThreshold();
    CALL var_dump('procMigrateCreditJudgeThreshold' , 'End');
--  T_DeliveryDestination_配送先
    CALL var_dump('procMigrateDeliveryDestinationB' , 'Start');
    CALL procMigrateDeliveryDestinationB();
    CALL var_dump('procMigrateDeliveryDestinationB' , 'End');
--  T_Enterprise_加盟店 
    CALL var_dump('procMigrateEnterprise' , 'Start');
    CALL procMigrateEnterprise();
    CALL var_dump('procMigrateEnterprise' , 'End');
--  T_EnterpriseClaimed_加盟店月別請求
    CALL var_dump('procMigrateEnterpriseClaimed' , 'Start');
    CALL procMigrateEnterpriseClaimed();
    CALL var_dump('procMigrateEnterpriseClaimed' , 'End');
--  T_EnterpriseTotal_加盟店別集計
    CALL var_dump('procMigrateEnterpriseTotal' , 'Start');
    CALL procMigrateEnterpriseTotal();
    CALL var_dump('procMigrateEnterpriseTotal' , 'End');
--  T_OemClaimAccountInfo_OEM請求口座
    CALL var_dump('procMigrateOemClaimAccountInfo' , 'Start');
    CALL procMigrateOemClaimAccountInfo();
    CALL var_dump('procMigrateOemClaimAccountInfo' , 'End');
--  T_OemBadDebt_OEM債権明細
    CALL var_dump('procMigrateOemBadDebt' , 'Start');
    CALL procMigrateOemBadDebt();
    CALL var_dump('procMigrateOemBadDebt' , 'End');
--  T_OemClaimed_OEM請求
    CALL var_dump('procMigrateOemClaimed' , 'Start');
    CALL procMigrateOemClaimed();
    CALL var_dump('procMigrateOemClaimed' , 'End');
--  T_OemClaimFee_OEM請求手数料
    CALL var_dump('procMigrateOemClaimFee' , 'Start');
    CALL procMigrateOemClaimFee();
    CALL var_dump('procMigrateOemClaimFee' , 'End');
--  T_OemEnterpriseClaimed_OEM加盟店請求
    CALL var_dump('procMigrateOemEnterpriseClaimed' , 'Start');
    CALL procMigrateOemEnterpriseClaimed();
    CALL var_dump('procMigrateOemEnterpriseClaimed' , 'End');
--  T_OemOperator_OEMオペレーター
    CALL var_dump('procMigrateOmeOperator' , 'Start');
    CALL procMigrateOmeOperator();
    CALL var_dump('procMigrateOmeOperator' , 'End');
--  T_OemSettlementFee_OEM決済手数料
    CALL var_dump('procMigrateOemSettlementFee' , 'Start');
    CALL procMigrateOemSettlementFee();
    CALL var_dump('procMigrateOemSettlementFee' , 'End');
--  T_Operator_オペレーター
    CALL var_dump('procMigrateOperator' , 'Start');
    CALL procMigrateOperator();
    CALL var_dump('procMigrateOperator' , 'End');
--  T_OrderItems_注文商品
    CALL var_dump('procMigrateOrderItemsB' , 'Start');
    CALL procMigrateOrderItemsB();
    CALL var_dump('procMigrateOrderItemsB' , 'End');
--  T_OrderSummary_注文サマリー
    CALL var_dump('procMigrateOrderSummaryB' , 'Start');
    CALL procMigrateOrderSummaryB();
    CALL var_dump('procMigrateOrderSummaryB' , 'End');
--  T_PayingAndSales_立替・売上管理
    CALL var_dump('procMigratePayingAndSales' , 'Start');
    CALL procMigratePayingAndSales();
    CALL var_dump('procMigratePayingAndSales' , 'End');
--  T_PayingControl_立替振込管理
    CALL var_dump('procMigratePayingControl' , 'Start');
    CALL procMigratePayingControl();
    CALL var_dump('procMigratePayingControl' , 'End');
--  T_Site_サイト
    CALL var_dump('procMigrateSite' , 'Start');
    CALL procMigrateSite();
    CALL var_dump('procMigrateSite' , 'End');
--  T_SmbcRelationAccount_決済ステーション連携アカウント
    CALL var_dump('procMigrateSmbcRelationAccount' , 'Start');
    CALL procMigrateSmbcRelationAccount();
    CALL var_dump('procMigrateSmbcRelationAccount' , 'End');
--  T_SmbcRelationLog_決済ステーション送受信ログ
    CALL var_dump('procMigrateSmbcRelationLog' , 'Start');
    CALL procMigrateSmbcRelationLog();
    CALL var_dump('procMigrateSmbcRelationLog' , 'End');
--  T_StampFee_ 印紙代管理
    CALL var_dump('procMigrateStampFee' , 'Start');
    CALL procMigrateStampFee();
    CALL var_dump('procMigrateStampFee' , 'End');
--  T_SystemStatus_システムステータス
    CALL var_dump('procMigrateSystemStatus' , 'Start');
    CALL procMigrateSystemStatus();
    CALL var_dump('procMigrateSystemStatus' , 'End');
--  T_ThreadPool_スレッドプール
    CALL var_dump('procMigrateThreadPool' , 'Start');
    CALL procMigrateThreadPool();
    CALL var_dump('procMigrateThreadPool' , 'End');
--  T_User_ユーザー
    CALL var_dump('procMigrateUser' , 'Start');
    CALL procMigrateUser();
    CALL var_dump('procMigrateUser' , 'End');
--  T_Jnb_JNB情報
    CALL var_dump('procMigrateJnb' , 'Start');
    CALL procMigrateJnb();
    CALL var_dump('procMigrateJnb' , 'End');
--  T_JnbAccountGroup_JNB口座グループ
    CALL var_dump('procMigrateJnbAccountGroup' , 'Start');
    CALL procMigrateJnbAccountGroup();
    CALL var_dump('procMigrateJnbAccountGroup' , 'End');
--  T_JnbAccount_JNB口座
    CALL var_dump('procMigrateJnbAccount' , 'Start');
    CALL procMigrateJnbAccount();
    CALL var_dump('procMigrateJnbAccount' , 'End');
--  T_JnbAccountUsageHistory_JNB口座利用履歴
    CALL var_dump('procMigrateJnbAccountUsageHistory' , 'Start');
    CALL procMigrateJnbAccountUsageHistory();
    CALL var_dump('procMigrateJnbAccountUsageHistory' , 'End');
--  T_JnbPaymentNotification_JNB入金通知管理
    CALL var_dump('procMigrateJnbPaymentNotification' , 'Start');
    CALL procMigrateJnbPaymentNotification();
    CALL var_dump('procMigrateJnbPaymentNotification' , 'End');
--  T_AuthenticationLog_認証ログ
    CALL var_dump('procMigrateAuthenticationLog' , 'Start');
    CALL procMigrateAuthenticationLog();
    CALL var_dump('procMigrateAuthenticationLog' , 'End');
--  T_Order_注文
    CALL var_dump('procMigrateOrder' , 'Start');
    CALL procMigrateOrder();
    CALL var_dump('procMigrateOrder' , 'End');
--  T_Customer_購入者
    CALL var_dump('procMigrateCustomer1B' , 'Start');
    CALL procMigrateCustomer1B();
    CALL var_dump('procMigrateCustomer1B' , 'End');
--  T_ManagementCustomer_管理顧客①
    CALL var_dump('procMigrateManagementCustomer' , 'Start');
    CALL procMigrateManagementCustomer();
    CALL var_dump('procMigrateManagementCustomer' , 'End');
--  T_EnterpriseCustomer_加盟店顧客
    CALL var_dump('procMigrateEnterpriseCustomer' , 'Start');
    CALL procMigrateEnterpriseCustomer();
    CALL var_dump('procMigrateEnterpriseCustomer' , 'End');
--  T_Customer_購入者②
    CALL var_dump('procMigrateCustomer2' , 'Start');
    CALL procMigrateCustomer2();
    CALL var_dump('procMigrateCustomer2' , 'End');
--  T_ClaimControl_請求管理②
    CALL var_dump('procMigrateClaimControl2' , 'Start');
    CALL procMigrateClaimControl2();
    CALL var_dump('procMigrateClaimControl2' , 'End');
--  T_ReceiptControl_入金管理
    CALL var_dump('procMigrateReceiptControl' , 'Start');
    CALL procMigrateReceiptControl();
    CALL var_dump('procMigrateReceiptControl' , 'End');
--  T_ClaimControl_請求管理③→④
    CALL var_dump('procMigrateClaimControl4' , 'Start');
    CALL procMigrateClaimControl4();
    CALL var_dump('procMigrateClaimControl4' , 'End');
--  T_SundryControl_雑収入・雑損失管理
    CALL var_dump('procMigrateSundryControl' , 'Start');
    CALL procMigrateSundryControl();
    CALL var_dump('procMigrateSundryControl' , 'End');
--  T_AdjustmentAmount_調整額管理
    CALL var_dump('procMigrateAdjustmentAmount' , 'Start');
    CALL procMigrateAdjustmentAmount();
    CALL var_dump('procMigrateAdjustmentAmount' , 'End');
--  T_ClaimControl_請求管理⑤(20150905_1139_suzuki_h)
    CALL var_dump('procMigrateClaimControl5' , 'Start');
    CALL procMigrateClaimControl5();
    CALL var_dump('procMigrateClaimControl5' , 'End');
--  移行後に着荷済みのデータは立替クリアフラグを計上する
    CALL var_dump('procMigrateClearCondition' , 'Start');
    CALL procMigrateClearCondition();
    CALL var_dump('procMigrateClearCondition' , 'End');

--  会計関連テーブルの追加(20150917_1440_suzuki_h)
    CALL var_dump('procMigrateATTables(会計テーブルの追加)' , 'Start');
    CALL procMigrateATTables();
    CALL var_dump('procMigrateATTables(会計テーブルの追加)' , 'End');
    
--  直営未払金兼売掛金明細(AT_Cb_Accounts_PayableReceivable)
    CALL var_dump('procMigrateAT_Cb_Accounts_PayableReceivable' , 'Start');
    CALL procMigrateAT_Cb_Accounts_PayableReceivable();
    CALL var_dump('procMigrateAT_Cb_Accounts_PayableReceivable' , 'End');
    
-- CSVSchemaの作成
    CALL var_dump('procMigrateCsvSchema' , 'Start');
    CALL procMigrateCsvSchema();
    CALL var_dump('procMigrateCsvSchema' , 'End');
    
-- 汎用UPDATE(最後に特定のカラムを一括で更新したい場合など)
    CALL var_dump('procMigrateOtherUpdates' , 'Start');
    CALL procMigrateOtherUpdates();
    CALL var_dump('procMigrateOtherUpdates' , 'End');

END
$$

DELIMITER ;

 