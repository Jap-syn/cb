DROP procedure IF EXISTS `procMigrateAllTable_Jizen`;

DELIMITER $$
CREATE PROCEDURE `procMigrateAllTable_Jizen` ()
BEGIN
/* 2015-05-28 27日終了時点のソースから作成。5月28日以降に追加されたものがある場合は、要追加 */
/* 2015-08-13 本番切替の数日前に実行し切替日の処理時間を短縮するためのデータ移行 */
/* 2015-08-25 procMigrateCreditPoint　他　追加 */
-- ----------
-- procMigrateAdjustmentAmount
-- ----------
--  M_CvsReceiptAgent_コンビニ収納代行会社
    CALL var_dump('procMigrateCvsReceiptAgent' , 'Start');
    CALL procMigrateCvsReceiptAgent();
    CALL var_dump('procMigrateCvsReceiptAgent' , 'End');
--  M_DeliveryMethod_配送方法
    CALL var_dump('procMigrateDeliveryMethod' , 'Start');
    CALL procMigrateDeliveryMethod();
    CALL var_dump('procMigrateDeliveryMethod' , 'End');
--  M_NationalHoliday_ 祝日マスター
    CALL var_dump('procMigrateNationalHoliday' , 'Start');
    CALL procMigrateNationalHoliday();
    CALL var_dump('procMigrateNationalHoliday' , 'End');
--  M_PayingCycle_ 立替サイクルマスター
    CALL var_dump('procMigratePayingCycle' , 'Start');
    CALL procMigratePayingCycle();
    CALL var_dump('procMigratePayingCycle' , 'End');
--  M_PostalCode_郵便番号
    CALL var_dump('procMigratePostalCode' , 'Start');
    CALL procMigratePostalCode();
    CALL var_dump('procMigratePostalCode' , 'End');
--  M_Prefecture_都道府県
    CALL var_dump('procMigratePrefecture' , 'Start');
    CALL procMigratePrefecture();
    CALL var_dump('procMigratePrefecture' , 'End');
--  M_PricePlan_ 料金プランマスター
    CALL var_dump('procMigratePricePlan' , 'Start');
    CALL procMigratePricePlan();
    CALL var_dump('procMigratePricePlan' , 'End');
--  T_BusinessCalendar_カレンダー
    CALL var_dump('procMigrateBusinessCalendar' , 'Start');
    CALL procMigrateBusinessCalendar();
    CALL var_dump('procMigrateBusinessCalendar' , 'End');
--  T_MailTemplate_メールテンプレート
    CALL var_dump('procMigrateMailTemplate' , 'Start');
    CALL procMigrateMailTemplate();
    CALL var_dump('procMigrateMailTemplate' , 'End');
--  T_Oem_OEM
    CALL var_dump('procMigrateOem' , 'Start');
    CALL procMigrateOem();
    CALL var_dump('procMigrateOem' , 'End');
--  T_OemBankAccount_OEM銀行口座
    CALL var_dump('procMigrateOemBankAccount' , 'Start');
    CALL procMigrateOemBankAccount();
    CALL var_dump('procMigrateOemBankAccount' , 'End');
--  T_OemCvsAccount_OEMコンビニ収納情報
    CALL var_dump('procMigrateOemCvsAccount' , 'Start');
    CALL procMigrateOemCvsAccount();
    CALL var_dump('procMigrateOemCvsAccount' , 'End');
--  T_OemDeliveryMethodList_OEM配送先順序
    CALL var_dump('procMigrateOemDeliveryMethodList' , 'Start');
    CALL procMigrateOemDeliveryMethodList();
    CALL var_dump('procMigrateOemDeliveryMethodList' , 'End');
--  T_OemYuchoAccount_OEMゆうちょ口座
    CALL var_dump('procMigrateOemYuchoAccount' , 'Start');
    CALL procMigrateOemYuchoAccount();
    CALL var_dump('procMigrateOemYuchoAccount' , 'End');
--  T_SystemProperty_システムプロパティ
    CALL var_dump('procMigrateSystemProperty' , 'Start');
    CALL procMigrateSystemProperty();
    CALL var_dump('procMigrateSystemProperty' , 'End');
--  T_TmpImage_OEM画像一時保存
    CALL var_dump('procMigrateTmpImage' , 'Start');
    CALL procMigrateTmpImage();
    CALL var_dump('procMigrateTmpImage' , 'End');
--  M_JnbBranch_JNB支店マスター
    CALL var_dump('procMigrateJnbBranch' , 'Start');
    CALL procMigrateJnbBranch();
    CALL var_dump('procMigrateJnbBranch' , 'End');
--  T_CjMailHistory_与信結果メール履歴
    CALL var_dump('procMigrateCjMailHistoryA' , 'Start');
    CALL procMigrateCjMailHistoryA();
    CALL var_dump('procMigrateCjMailHistoryA' , 'End');
--  T_CjResult_与信審査結果   
    CALL var_dump('procMigrateCjResultA' , 'Start');
    CALL procMigrateCjResultA();
    CALL var_dump('procMigrateCjResultA' , 'End');
--  T_CjResult_Detail_与信審査結果詳細
    CALL var_dump('procMigrateCjResultDetailA' , 'Start');
    CALL procMigrateCjResultDetailA();
    CALL var_dump('procMigrateCjResultDetailA' , 'End');
--  T_ClaimHistory_請求履歴
    CALL var_dump('procMigrateClaimHistoryA' , 'Start');
    CALL procMigrateClaimHistoryA();
    CALL var_dump('procMigrateClaimHistoryA' , 'End');
--  T_CreditCondition_社内与信条件
    CALL var_dump('procMigrateCreditConditionA' , 'Start');
    CALL procMigrateCreditConditionA();
    CALL var_dump('procMigrateCreditConditionA' , 'End');
--  T_DeliveryDestination_配送先
    CALL var_dump('procMigrateDeliveryDestinationA' , 'Start');
    CALL procMigrateDeliveryDestinationA();
    CALL var_dump('procMigrateDeliveryDestinationA' , 'End');
--  T_OrderItems_注文商品
    CALL var_dump('procMigrateOrderItemsA' , 'Start');
    CALL procMigrateOrderItemsA();
    CALL var_dump('procMigrateOrderItemsA' , 'End');
--  T_OrderSummary_注文サマリー
    CALL var_dump('procMigrateOrderSummaryA' , 'Start');
    CALL procMigrateOrderSummaryA();
    CALL var_dump('procMigrateOrderSummaryA' , 'End');
--  T_Customer_購入者
    CALL var_dump('procMigrateCustomer1A' , 'Start');
    CALL procMigrateCustomer1A();
    CALL var_dump('procMigrateCustomer1A' , 'End');
--  M_CreditPoint  社内与信ポイントマスタ  2015-08-25
    CALL var_dump('procMigrateCreditPoint' , 'Start');
    CALL procMigrateCreditPoint();
    CALL var_dump('procMigrateCreditPoint' , 'End');
--  M_CreditPoint  銀行支店マスタ   2015-08-25
    CALL var_dump('procMigrateBranchBank' , 'Start');
    CALL procMigrateBranchBank();
    CALL var_dump('procMigrateBranchBank' , 'End');

END
$$

DELIMITER ;

