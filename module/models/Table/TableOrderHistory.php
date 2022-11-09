<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_OrderHistory(注文履歴)テーブルへのアダプタ
 */
class TableOrderHistory
{
    protected $_name = 'T_OrderHistory';
    protected $_primary = array('HistorySeq');
    protected $_adapter = null;

    /**
     * コンストラクタ
     *
     * @param Adapter $adapter アダプタ
     */
    public function __construct(Adapter $adapter)
    {
        $this->_adapter = $adapter;
    }

    /**
     * 注文履歴データを取得する
     *
     * @param int $historySeq 履歴SEQ
     * @return ResultInterface
     */
    public function find($historySeq)
    {
        $sql = " SELECT * FROM T_OrderHistory WHERE HistorySeq = :HistorySeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':HistorySeq' => $historySeq,
        );

        return $stm->execute($prm);
    }

    /**
     * 新しいレコードをインサートする。
     *
     * @param array $data インサートする連想配列
     * @return プライマリキーのバリュー
     */
    public function saveNew($data)
    {
        $sql  = " INSERT INTO T_OrderHistory (OrderSeq, HistoryReasonCode, ORD_OrderId, ORD_OutOfAmends, ORD_Oem_OrderId, ORD_Ent_OrderId, ORD_RegistDate, ORD_ReceiptOrderDate, ORD_ServiceExpectedDate, ORD_DataStatus, CJM_ProcessingDate, ORD_UseAmount, StatusCaption, ORD_CombinedClaimTargetStatus, ORD_CombinedClaimParentFlg, ORD_CombinedClaimParentOrderId, CUS_NameKj, CUS_NameKn, CUS_PostalCode, CUS_UnitingAddress, CUS_AddressKn, CUS_EntCustId, CustomerStatus, CUS_Phone, CUS_Carrier, CUS_MailAddress, CUS_Occupation, CUS_CorporateName, CUS_DivisionName, CUS_CpNameKj, CreditInfo, ORD_Chg_NonChargeFlg, ORD_AnotherDeliFlg, DEL_DestNameKj, DEL_DestNameKn, DEL_PostalCode, DEL_UnitingAddress, DEL_Phone, OEM_OemNameKj, ENT_EnterpriseNameKj, SIT_SiteNameKj, ENT_CpNameKj, ENT_ContactPhoneNumber, OrderItemInfo, DeliveryFee, SettlementFee, ExTaxAmount, TotalSumMoney, ReclaimFee, OtherCombinedAmount, ORD_InstallmentPlanAmount, TotalClaimMoney, ORD_Incre_Note, JudgeSystemResult, PastDays, ORD_RemindClass, MAN_RemindStopFlg, ORD_FinalityRemindDate, ORD_FinalityRemindOpId, ORD_PromPayDate, CUS_ResidentCard, ORD_LonghandLetter, ORD_BriefNote, ORD_TouchHistoryFlg, ORD_VisitFlg, ReceiptPastDays, ORD_FinalityCollectionMean, ORD_ClaimStopReleaseDate, ORD_LetterClaimStopFlg, ORD_MailClaimStopFlg, ORD_Tel30DaysFlg, ORD_Tel90DaysFlg, CUS_Cinfo1, CUS_CinfoNote1, CUS_CinfoStatus1, CUS_Cinfo2, CUS_CinfoNote2, CUS_CinfoStatus2, CUS_Cinfo3, CUS_CinfoNote3, CUS_CinfoStatus3, ORD_OemClaimTransDate, ORD_Dmg_DecisionDate, ORD_Oem_Note, IncreArClass, ORD_Incre_ScoreTotal, CJR_TotalScore, ORD_Incre_Status, ORD_Dmi_Status, ORD_Dmi_DecisionDate, ORD_Dmi_ResponseNote, ITM_Deli_JournalIncDate, ITM_Deli_DeliveryMethod, ITM_Deli_JournalNumber, CLM_F_ClaimDate, CLM_F_LimitDate, CLM_ClaimDate, CLM_LimitDate, ORD_Deli_ConfirmArrivalDate, ORD_Deli_ConfirmArrivalFlg, PAS_ClearConditionForCharge, PAS_ClearConditionDate, ORD_Chg_FixedDate, PAC_ExecScheduleDate, REC_ReceiptDate, REC_ReceiptProcessDate, REC_ReceiptClass, CLM_ClaimedBalance, STF_StampFee, REC_DepositDate, MailInfo, ReClaimInfo, CNL_CancelDate, CNL_CancelReasonCode, ORD_MailPaymentSoonDate, ORD_MailLimitPassageCount, ORD_MailLimitPassageDate, ORD_Ent_Note, ORD_CombinedParentOrderSeq, ORD_CombinedOrderSeq, Reserve, RegistDate, RegistId) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :HistoryReasonCode ";
        $sql .= " , :ORD_OrderId ";
        $sql .= " , :ORD_OutOfAmends ";
        $sql .= " , :ORD_Oem_OrderId ";
        $sql .= " , :ORD_Ent_OrderId ";
        $sql .= " , :ORD_RegistDate ";
        $sql .= " , :ORD_ReceiptOrderDate ";
        $sql .= " , :ORD_ServiceExpectedDate ";
        $sql .= " , :ORD_DataStatus ";
        $sql .= " , :CJM_ProcessingDate ";
        $sql .= " , :ORD_UseAmount ";
        $sql .= " , :StatusCaption ";
        $sql .= " , :ORD_CombinedClaimTargetStatus ";
        $sql .= " , :ORD_CombinedClaimParentFlg ";
        $sql .= " , :ORD_CombinedClaimParentOrderId ";
        $sql .= " , :CUS_NameKj ";
        $sql .= " , :CUS_NameKn ";
        $sql .= " , :CUS_PostalCode ";
        $sql .= " , :CUS_UnitingAddress ";
        $sql .= " , :CUS_AddressKn ";
        $sql .= " , :CUS_EntCustId ";
        $sql .= " , :CustomerStatus ";
        $sql .= " , :CUS_Phone ";
        $sql .= " , :CUS_Carrier ";
        $sql .= " , :CUS_MailAddress ";
        $sql .= " , :CUS_Occupation ";
        $sql .= " , :CUS_CorporateName ";
        $sql .= " , :CUS_DivisionName ";
        $sql .= " , :CUS_CpNameKj ";
        $sql .= " , :CreditInfo ";
        $sql .= " , :ORD_Chg_NonChargeFlg ";
        $sql .= " , :ORD_AnotherDeliFlg ";
        $sql .= " , :DEL_DestNameKj ";
        $sql .= " , :DEL_DestNameKn ";
        $sql .= " , :DEL_PostalCode ";
        $sql .= " , :DEL_UnitingAddress ";
        $sql .= " , :DEL_Phone ";
        $sql .= " , :OEM_OemNameKj ";
        $sql .= " , :ENT_EnterpriseNameKj ";
        $sql .= " , :SIT_SiteNameKj ";
        $sql .= " , :ENT_CpNameKj ";
        $sql .= " , :ENT_ContactPhoneNumber ";
        $sql .= " , :OrderItemInfo ";
        $sql .= " , :DeliveryFee ";
        $sql .= " , :SettlementFee ";
        $sql .= " , :ExTaxAmount ";
        $sql .= " , :TotalSumMoney ";
        $sql .= " , :ReclaimFee ";
        $sql .= " , :OtherCombinedAmount ";
        $sql .= " , :ORD_InstallmentPlanAmount ";
        $sql .= " , :TotalClaimMoney ";
        $sql .= " , :ORD_Incre_Note ";
        $sql .= " , :JudgeSystemResult ";
        $sql .= " , :PastDays ";
        $sql .= " , :ORD_RemindClass ";
        $sql .= " , :MAN_RemindStopFlg ";
        $sql .= " , :ORD_FinalityRemindDate ";
        $sql .= " , :ORD_FinalityRemindOpId ";
        $sql .= " , :ORD_PromPayDate ";
        $sql .= " , :CUS_ResidentCard ";
        $sql .= " , :ORD_LonghandLetter ";
        $sql .= " , :ORD_BriefNote ";
        $sql .= " , :ORD_TouchHistoryFlg ";
        $sql .= " , :ORD_VisitFlg ";
        $sql .= " , :ReceiptPastDays ";
        $sql .= " , :ORD_FinalityCollectionMean ";
        $sql .= " , :ORD_ClaimStopReleaseDate ";
        $sql .= " , :ORD_LetterClaimStopFlg ";
        $sql .= " , :ORD_MailClaimStopFlg ";
        $sql .= " , :ORD_Tel30DaysFlg ";
        $sql .= " , :ORD_Tel90DaysFlg ";
        $sql .= " , :CUS_Cinfo1 ";
        $sql .= " , :CUS_CinfoNote1 ";
        $sql .= " , :CUS_CinfoStatus1 ";
        $sql .= " , :CUS_Cinfo2 ";
        $sql .= " , :CUS_CinfoNote2 ";
        $sql .= " , :CUS_CinfoStatus2 ";
        $sql .= " , :CUS_Cinfo3 ";
        $sql .= " , :CUS_CinfoNote3 ";
        $sql .= " , :CUS_CinfoStatus3 ";
        $sql .= " , :ORD_OemClaimTransDate ";
        $sql .= " , :ORD_Dmg_DecisionDate ";
        $sql .= " , :ORD_Oem_Note ";
        $sql .= " , :IncreArClass ";
        $sql .= " , :ORD_Incre_ScoreTotal ";
        $sql .= " , :CJR_TotalScore ";
        $sql .= " , :ORD_Incre_Status ";
        $sql .= " , :ORD_Dmi_Status ";
        $sql .= " , :ORD_Dmi_DecisionDate ";
        $sql .= " , :ORD_Dmi_ResponseNote ";
        $sql .= " , :ITM_Deli_JournalIncDate ";
        $sql .= " , :ITM_Deli_DeliveryMethod ";
        $sql .= " , :ITM_Deli_JournalNumber ";
        $sql .= " , :CLM_F_ClaimDate ";
        $sql .= " , :CLM_F_LimitDate ";
        $sql .= " , :CLM_ClaimDate ";
        $sql .= " , :CLM_LimitDate ";
        $sql .= " , :ORD_Deli_ConfirmArrivalDate ";
        $sql .= " , :ORD_Deli_ConfirmArrivalFlg ";
        $sql .= " , :PAS_ClearConditionForCharge ";
        $sql .= " , :PAS_ClearConditionDate ";
        $sql .= " , :ORD_Chg_FixedDate ";
        $sql .= " , :PAC_ExecScheduleDate ";
        $sql .= " , :REC_ReceiptDate ";
        $sql .= " , :REC_ReceiptProcessDate ";
        $sql .= " , :REC_ReceiptClass ";
        $sql .= " , :CLM_ClaimedBalance ";
        $sql .= " , :STF_StampFee ";
        $sql .= " , :REC_DepositDate ";
        $sql .= " , :MailInfo ";
        $sql .= " , :ReClaimInfo ";
        $sql .= " , :CNL_CancelDate ";
        $sql .= " , :CNL_CancelReasonCode ";
        $sql .= " , :ORD_MailPaymentSoonDate ";
        $sql .= " , :ORD_MailLimitPassageCount ";
        $sql .= " , :ORD_MailLimitPassageDate ";
        $sql .= " , :ORD_Ent_Note ";
        $sql .= " , :ORD_CombinedParentOrderSeq ";
        $sql .= " , :ORD_CombinedOrderSeq ";
        $sql .= " , :Reserve ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':HistoryReasonCode' => $data['HistoryReasonCode'],
                ':ORD_OrderId' => $data['ORD_OrderId'],
                ':ORD_OutOfAmends' => $data['ORD_OutOfAmends'],
                ':ORD_Oem_OrderId' => $data['ORD_Oem_OrderId'],
                ':ORD_Ent_OrderId' => $data['ORD_Ent_OrderId'],
                ':ORD_RegistDate' => $data['ORD_RegistDate'],
                ':ORD_ReceiptOrderDate' => $data['ORD_ReceiptOrderDate'],
                ':ORD_ServiceExpectedDate' => $data['ORD_ServiceExpectedDate'],
                ':ORD_DataStatus' => $data['ORD_DataStatus'],
                ':CJM_ProcessingDate' => $data['CJM_ProcessingDate'],
                ':ORD_UseAmount' => $data['ORD_UseAmount'],
                ':StatusCaption' => $data['StatusCaption'],
                ':ORD_CombinedClaimTargetStatus' => $data['ORD_CombinedClaimTargetStatus'],
                ':ORD_CombinedClaimParentFlg' => $data['ORD_CombinedClaimParentFlg'],
                ':ORD_CombinedClaimParentOrderId' => $data['ORD_CombinedClaimParentOrderId'],
                ':CUS_NameKj' => $data['CUS_NameKj'],
                ':CUS_NameKn' => $data['CUS_NameKn'],
                ':CUS_PostalCode' => $data['CUS_PostalCode'],
                ':CUS_UnitingAddress' => $data['CUS_UnitingAddress'],
                ':CUS_AddressKn' => $data['CUS_AddressKn'],
                ':CUS_EntCustId' => $data['CUS_EntCustId'],
                ':CustomerStatus' => $data['CustomerStatus'],
                ':CUS_Phone' => $data['CUS_Phone'],
                ':CUS_Carrier' => $data['CUS_Carrier'],
                ':CUS_MailAddress' => $data['CUS_MailAddress'],
                ':CUS_Occupation' => $data['CUS_Occupation'],
                ':CUS_CorporateName' => $data['CUS_CorporateName'],
                ':CUS_DivisionName' => $data['CUS_DivisionName'],
                ':CUS_CpNameKj' => $data['CUS_CpNameKj'],
                ':CreditInfo' => $data['CreditInfo'],
                ':ORD_Chg_NonChargeFlg' => $data['ORD_Chg_NonChargeFlg'],
                ':ORD_AnotherDeliFlg' => $data['ORD_AnotherDeliFlg'],
                ':DEL_DestNameKj' => $data['DEL_DestNameKj'],
                ':DEL_DestNameKn' => $data['DEL_DestNameKn'],
                ':DEL_PostalCode' => $data['DEL_PostalCode'],
                ':DEL_UnitingAddress' => $data['DEL_UnitingAddress'],
                ':DEL_Phone' => $data['DEL_Phone'],
                ':OEM_OemNameKj' => $data['OEM_OemNameKj'],
                ':ENT_EnterpriseNameKj' => $data['ENT_EnterpriseNameKj'],
                ':SIT_SiteNameKj' => $data['SIT_SiteNameKj'],
                ':ENT_CpNameKj' => $data['ENT_CpNameKj'],
                ':ENT_ContactPhoneNumber' => $data['ENT_ContactPhoneNumber'],
                ':OrderItemInfo' => $data['OrderItemInfo'],
                ':DeliveryFee' => $data['DeliveryFee'],
                ':SettlementFee' => $data['SettlementFee'],
                ':ExTaxAmount' => $data['ExTaxAmount'],
                ':TotalSumMoney' => $data['TotalSumMoney'],
                ':ReclaimFee' => $data['ReclaimFee'],
                ':OtherCombinedAmount' => $data['OtherCombinedAmount'],
                ':ORD_InstallmentPlanAmount' => $data['ORD_InstallmentPlanAmount'],
                ':TotalClaimMoney' => $data['TotalClaimMoney'],
                ':ORD_Incre_Note' => $data['ORD_Incre_Note'],
                ':JudgeSystemResult' => $data['JudgeSystemResult'],
                ':PastDays' => $data['PastDays'],
                ':ORD_RemindClass' => $data['ORD_RemindClass'],
                ':MAN_RemindStopFlg' => $data['MAN_RemindStopFlg'],
                ':ORD_FinalityRemindDate' => $data['ORD_FinalityRemindDate'],
                ':ORD_FinalityRemindOpId' => $data['ORD_FinalityRemindOpId'],
                ':ORD_PromPayDate' => $data['ORD_PromPayDate'],
                ':CUS_ResidentCard' => $data['CUS_ResidentCard'],
                ':ORD_LonghandLetter' => $data['ORD_LonghandLetter'],
                ':ORD_BriefNote' => $data['ORD_BriefNote'],
                ':ORD_TouchHistoryFlg' => $data['ORD_TouchHistoryFlg'],
                ':ORD_VisitFlg' => $data['ORD_VisitFlg'],
                ':ReceiptPastDays' => $data['ReceiptPastDays'],
                ':ORD_FinalityCollectionMean' => $data['ORD_FinalityCollectionMean'],
                ':ORD_ClaimStopReleaseDate' => $data['ORD_ClaimStopReleaseDate'],
                ':ORD_LetterClaimStopFlg' => $data['ORD_LetterClaimStopFlg'],
                ':ORD_MailClaimStopFlg' => $data['ORD_MailClaimStopFlg'],
                ':ORD_Tel30DaysFlg' => $data['ORD_Tel30DaysFlg'],
                ':ORD_Tel90DaysFlg' => $data['ORD_Tel90DaysFlg'],
                ':CUS_Cinfo1' => $data['CUS_Cinfo1'],
                ':CUS_CinfoNote1' => $data['CUS_CinfoNote1'],
                ':CUS_CinfoStatus1' => $data['CUS_CinfoStatus1'],
                ':CUS_Cinfo2' => $data['CUS_Cinfo2'],
                ':CUS_CinfoNote2' => $data['CUS_CinfoNote2'],
                ':CUS_CinfoStatus2' => $data['CUS_CinfoStatus2'],
                ':CUS_Cinfo3' => $data['CUS_Cinfo3'],
                ':CUS_CinfoNote3' => $data['CUS_CinfoNote3'],
                ':CUS_CinfoStatus3' => $data['CUS_CinfoStatus3'],
                ':ORD_OemClaimTransDate' => $data['ORD_OemClaimTransDate'],
                ':ORD_Dmg_DecisionDate' => $data['ORD_Dmg_DecisionDate'],
                ':ORD_Oem_Note' => $data['ORD_Oem_Note'],
                ':IncreArClass' => $data['IncreArClass'],
                ':ORD_Incre_ScoreTotal' => $data['ORD_Incre_ScoreTotal'],
                ':CJR_TotalScore' => $data['CJR_TotalScore'],
                ':ORD_Incre_Status' => $data['ORD_Incre_Status'],
                ':ORD_Dmi_Status' => $data['ORD_Dmi_Status'],
                ':ORD_Dmi_DecisionDate' => $data['ORD_Dmi_DecisionDate'],
                ':ORD_Dmi_ResponseNote' => $data['ORD_Dmi_ResponseNote'],
                ':ITM_Deli_JournalIncDate' => $data['ITM_Deli_JournalIncDate'],
                ':ITM_Deli_DeliveryMethod' => $data['ITM_Deli_DeliveryMethod'],
                ':ITM_Deli_JournalNumber' => $data['ITM_Deli_JournalNumber'],
                ':CLM_F_ClaimDate' => $data['CLM_F_ClaimDate'],
                ':CLM_F_LimitDate' => $data['CLM_F_LimitDate'],
                ':CLM_ClaimDate' => $data['CLM_ClaimDate'],
                ':CLM_LimitDate' => $data['CLM_LimitDate'],
                ':ORD_Deli_ConfirmArrivalDate' => $data['ORD_Deli_ConfirmArrivalDate'],
                ':ORD_Deli_ConfirmArrivalFlg' => $data['ORD_Deli_ConfirmArrivalFlg'],
                ':PAS_ClearConditionForCharge' => $data['PAS_ClearConditionForCharge'],
                ':PAS_ClearConditionDate' => $data['PAS_ClearConditionDate'],
                ':ORD_Chg_FixedDate' => $data['ORD_Chg_FixedDate'],
                ':PAC_ExecScheduleDate' => $data['PAC_ExecScheduleDate'],
                ':REC_ReceiptDate' => $data['REC_ReceiptDate'],
                ':REC_ReceiptProcessDate' => $data['REC_ReceiptProcessDate'],
                ':REC_ReceiptClass' => $data['REC_ReceiptClass'],
                ':CLM_ClaimedBalance' => $data['CLM_ClaimedBalance'],
                ':STF_StampFee' => $data['STF_StampFee'],
                ':REC_DepositDate' => $data['REC_DepositDate'],
                ':MailInfo' => $data['MailInfo'],
                ':ReClaimInfo' => $data['ReClaimInfo'],
                ':CNL_CancelDate' => $data['CNL_CancelDate'],
                ':CNL_CancelReasonCode' => $data['CNL_CancelReasonCode'],
                ':ORD_MailPaymentSoonDate' => $data['ORD_MailPaymentSoonDate'],
                ':ORD_MailLimitPassageCount' => $data['ORD_MailLimitPassageCount'],
                ':ORD_MailLimitPassageDate' => $data['ORD_MailLimitPassageDate'],
                ':ORD_Ent_Note' => $data['ORD_Ent_Note'],
                ':ORD_CombinedParentOrderSeq' => $data['ORD_CombinedParentOrderSeq'],
                ':ORD_CombinedOrderSeq' => $data['ORD_CombinedOrderSeq'],
                ':Reserve' => $data['Reserve'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $historySeq 履歴SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $historySeq)
    {
        $row = $this->find($historySeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_OrderHistory ";
        $sql .= " SET ";
        $sql .= "     OrderSeq = :OrderSeq ";
        $sql .= " ,   HistoryReasonCode = :HistoryReasonCode ";
        $sql .= " ,   ORD_OrderId = :ORD_OrderId ";
        $sql .= " ,   ORD_OutOfAmends = :ORD_OutOfAmends ";
        $sql .= " ,   ORD_Oem_OrderId = :ORD_Oem_OrderId ";
        $sql .= " ,   ORD_Ent_OrderId = :ORD_Ent_OrderId ";
        $sql .= " ,   ORD_RegistDate = :ORD_RegistDate ";
        $sql .= " ,   ORD_ReceiptOrderDate = :ORD_ReceiptOrderDate ";
        $sql .= " ,   ORD_ServiceExpectedDate = :ORD_ServiceExpectedDate ";
        $sql .= " ,   ORD_DataStatus = :ORD_DataStatus ";
        $sql .= " ,   CJM_ProcessingDate = :CJM_ProcessingDate ";
        $sql .= " ,   ORD_UseAmount = :ORD_UseAmount ";
        $sql .= " ,   StatusCaption = :StatusCaption ";
        $sql .= " ,   ORD_CombinedClaimTargetStatus = :ORD_CombinedClaimTargetStatus ";
        $sql .= " ,   ORD_CombinedClaimParentFlg = :ORD_CombinedClaimParentFlg ";
        $sql .= " ,   ORD_CombinedClaimParentOrderId = :ORD_CombinedClaimParentOrderId ";
        $sql .= " ,   CUS_NameKj = :CUS_NameKj ";
        $sql .= " ,   CUS_NameKn = :CUS_NameKn ";
        $sql .= " ,   CUS_PostalCode = :CUS_PostalCode ";
        $sql .= " ,   CUS_UnitingAddress = :CUS_UnitingAddress ";
        $sql .= " ,   CUS_AddressKn = :CUS_AddressKn ";
        $sql .= " ,   CUS_EntCustId = :CUS_EntCustId ";
        $sql .= " ,   CustomerStatus = :CustomerStatus ";
        $sql .= " ,   CUS_Phone = :CUS_Phone ";
        $sql .= " ,   CUS_Carrier = :CUS_Carrier ";
        $sql .= " ,   CUS_MailAddress = :CUS_MailAddress ";
        $sql .= " ,   CUS_Occupation = :CUS_Occupation ";
        $sql .= " ,   CUS_CorporateName = :CUS_CorporateName ";
        $sql .= " ,   CUS_DivisionName = :CUS_DivisionName ";
        $sql .= " ,   CUS_CpNameKj = :CUS_CpNameKj ";
        $sql .= " ,   CreditInfo = :CreditInfo ";
        $sql .= " ,   ORD_Chg_NonChargeFlg = :ORD_Chg_NonChargeFlg ";
        $sql .= " ,   ORD_AnotherDeliFlg = :ORD_AnotherDeliFlg ";
        $sql .= " ,   DEL_DestNameKj = :DEL_DestNameKj ";
        $sql .= " ,   DEL_DestNameKn = :DEL_DestNameKn ";
        $sql .= " ,   DEL_PostalCode = :DEL_PostalCode ";
        $sql .= " ,   DEL_UnitingAddress = :DEL_UnitingAddress ";
        $sql .= " ,   DEL_Phone = :DEL_Phone ";
        $sql .= " ,   OEM_OemNameKj = :OEM_OemNameKj ";
        $sql .= " ,   ENT_EnterpriseNameKj = :ENT_EnterpriseNameKj ";
        $sql .= " ,   SIT_SiteNameKj = :SIT_SiteNameKj ";
        $sql .= " ,   ENT_CpNameKj = :ENT_CpNameKj ";
        $sql .= " ,   ENT_ContactPhoneNumber = :ENT_ContactPhoneNumber ";
        $sql .= " ,   OrderItemInfo = :OrderItemInfo ";
        $sql .= " ,   DeliveryFee = :DeliveryFee ";
        $sql .= " ,   SettlementFee = :SettlementFee ";
        $sql .= " ,   ExTaxAmount = :ExTaxAmount ";
        $sql .= " ,   TotalSumMoney = :TotalSumMoney ";
        $sql .= " ,   ReclaimFee = :ReclaimFee ";
        $sql .= " ,   OtherCombinedAmount = :OtherCombinedAmount ";
        $sql .= " ,   ORD_InstallmentPlanAmount = :ORD_InstallmentPlanAmount ";
        $sql .= " ,   TotalClaimMoney = :TotalClaimMoney ";
        $sql .= " ,   ORD_Incre_Note = :ORD_Incre_Note ";
        $sql .= " ,   JudgeSystemResult = :JudgeSystemResult ";
        $sql .= " ,   PastDays = :PastDays ";
        $sql .= " ,   ORD_RemindClass = :ORD_RemindClass ";
        $sql .= " ,   MAN_RemindStopFlg = :MAN_RemindStopFlg ";
        $sql .= " ,   ORD_FinalityRemindDate = :ORD_FinalityRemindDate ";
        $sql .= " ,   ORD_FinalityRemindOpId = :ORD_FinalityRemindOpId ";
        $sql .= " ,   ORD_PromPayDate = :ORD_PromPayDate ";
        $sql .= " ,   CUS_ResidentCard = :CUS_ResidentCard ";
        $sql .= " ,   ORD_LonghandLetter = :ORD_LonghandLetter ";
        $sql .= " ,   ORD_BriefNote = :ORD_BriefNote ";
        $sql .= " ,   ORD_TouchHistoryFlg = :ORD_TouchHistoryFlg ";
        $sql .= " ,   ORD_VisitFlg = :ORD_VisitFlg ";
        $sql .= " ,   ReceiptPastDays = :ReceiptPastDays ";
        $sql .= " ,   ORD_FinalityCollectionMean = :ORD_FinalityCollectionMean ";
        $sql .= " ,   ORD_ClaimStopReleaseDate = :ORD_ClaimStopReleaseDate ";
        $sql .= " ,   ORD_LetterClaimStopFlg = :ORD_LetterClaimStopFlg ";
        $sql .= " ,   ORD_MailClaimStopFlg = :ORD_MailClaimStopFlg ";
        $sql .= " ,   ORD_Tel30DaysFlg = :ORD_Tel30DaysFlg ";
        $sql .= " ,   ORD_Tel90DaysFlg = :ORD_Tel90DaysFlg ";
        $sql .= " ,   CUS_Cinfo1 = :CUS_Cinfo1 ";
        $sql .= " ,   CUS_CinfoNote1 = :CUS_CinfoNote1 ";
        $sql .= " ,   CUS_CinfoStatus1 = :CUS_CinfoStatus1 ";
        $sql .= " ,   CUS_Cinfo2 = :CUS_Cinfo2 ";
        $sql .= " ,   CUS_CinfoNote2 = :CUS_CinfoNote2 ";
        $sql .= " ,   CUS_CinfoStatus2 = :CUS_CinfoStatus2 ";
        $sql .= " ,   CUS_Cinfo3 = :CUS_Cinfo3 ";
        $sql .= " ,   CUS_CinfoNote3 = :CUS_CinfoNote3 ";
        $sql .= " ,   CUS_CinfoStatus3 = :CUS_CinfoStatus3 ";
        $sql .= " ,   ORD_OemClaimTransDate = :ORD_OemClaimTransDate ";
        $sql .= " ,   ORD_Dmg_DecisionDate = :ORD_Dmg_DecisionDate ";
        $sql .= " ,   ORD_Oem_Note = :ORD_Oem_Note ";
        $sql .= " ,   IncreArClass = :IncreArClass ";
        $sql .= " ,   ORD_Incre_ScoreTotal = :ORD_Incre_ScoreTotal ";
        $sql .= " ,   CJR_TotalScore = :CJR_TotalScore ";
        $sql .= " ,   ORD_Incre_Status = :ORD_Incre_Status ";
        $sql .= " ,   ORD_Dmi_Status = :ORD_Dmi_Status ";
        $sql .= " ,   ORD_Dmi_DecisionDate = :ORD_Dmi_DecisionDate ";
        $sql .= " ,   ORD_Dmi_ResponseNote = :ORD_Dmi_ResponseNote ";
        $sql .= " ,   ITM_Deli_JournalIncDate = :ITM_Deli_JournalIncDate ";
        $sql .= " ,   ITM_Deli_DeliveryMethod = :ITM_Deli_DeliveryMethod ";
        $sql .= " ,   ITM_Deli_JournalNumber = :ITM_Deli_JournalNumber ";
        $sql .= " ,   CLM_F_ClaimDate = :CLM_F_ClaimDate ";
        $sql .= " ,   CLM_F_LimitDate = :CLM_F_LimitDate ";
        $sql .= " ,   CLM_ClaimDate = :CLM_ClaimDate ";
        $sql .= " ,   CLM_LimitDate = :CLM_LimitDate ";
        $sql .= " ,   ORD_Deli_ConfirmArrivalDate = :ORD_Deli_ConfirmArrivalDate ";
        $sql .= " ,   ORD_Deli_ConfirmArrivalFlg = :ORD_Deli_ConfirmArrivalFlg ";
        $sql .= " ,   PAS_ClearConditionForCharge = :PAS_ClearConditionForCharge ";
        $sql .= " ,   PAS_ClearConditionDate = :PAS_ClearConditionDate ";
        $sql .= " ,   ORD_Chg_FixedDate = :ORD_Chg_FixedDate ";
        $sql .= " ,   PAC_ExecScheduleDate = :PAC_ExecScheduleDate ";
        $sql .= " ,   REC_ReceiptDate = :REC_ReceiptDate ";
        $sql .= " ,   REC_ReceiptProcessDate = :REC_ReceiptProcessDate ";
        $sql .= " ,   REC_ReceiptClass = :REC_ReceiptClass ";
        $sql .= " ,   CLM_ClaimedBalance = :CLM_ClaimedBalance ";
        $sql .= " ,   STF_StampFee = :STF_StampFee ";
        $sql .= " ,   REC_DepositDate = :REC_DepositDate ";
        $sql .= " ,   MailInfo = :MailInfo ";
        $sql .= " ,   ReClaimInfo = :ReClaimInfo ";
        $sql .= " ,   CNL_CancelDate = :CNL_CancelDate ";
        $sql .= " ,   CNL_CancelReasonCode = :CNL_CancelReasonCode ";
        $sql .= " ,   ORD_MailPaymentSoonDate = :ORD_MailPaymentSoonDate ";
        $sql .= " ,   ORD_MailLimitPassageCount = :ORD_MailLimitPassageCount ";
        $sql .= " ,   ORD_MailLimitPassageDate = :ORD_MailLimitPassageDate ";
        $sql .= " ,   ORD_Ent_Note = :ORD_Ent_Note ";
        $sql .= " ,   ORD_CombinedParentOrderSeq = :ORD_CombinedParentOrderSeq ";
        $sql .= " ,   ORD_CombinedOrderSeq = :ORD_CombinedOrderSeq ";
        $sql .= " ,   Reserve = :Reserve ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':HistorySeq' => $historySeq,
                ':OrderSeq' => $row['OrderSeq'],
                ':HistoryReasonCode' => $row['HistoryReasonCode'],
                ':ORD_OrderId' => $row['ORD_OrderId'],
                ':ORD_OutOfAmends' => $row['ORD_OutOfAmends'],
                ':ORD_Oem_OrderId' => $row['ORD_Oem_OrderId'],
                ':ORD_Ent_OrderId' => $row['ORD_Ent_OrderId'],
                ':ORD_RegistDate' => $row['ORD_RegistDate'],
                ':ORD_ReceiptOrderDate' => $row['ORD_ReceiptOrderDate'],
                ':ORD_ServiceExpectedDate' => $row['ORD_ServiceExpectedDate'],
                ':ORD_DataStatus' => $row['ORD_DataStatus'],
                ':CJM_ProcessingDate' => $row['CJM_ProcessingDate'],
                ':ORD_UseAmount' => $row['ORD_UseAmount'],
                ':StatusCaption' => $row['StatusCaption'],
                ':ORD_CombinedClaimTargetStatus' => $row['ORD_CombinedClaimTargetStatus'],
                ':ORD_CombinedClaimParentFlg' => $row['ORD_CombinedClaimParentFlg'],
                ':ORD_CombinedClaimParentOrderId' => $row['ORD_CombinedClaimParentOrderId'],
                ':CUS_NameKj' => $row['CUS_NameKj'],
                ':CUS_NameKn' => $row['CUS_NameKn'],
                ':CUS_PostalCode' => $row['CUS_PostalCode'],
                ':CUS_UnitingAddress' => $row['CUS_UnitingAddress'],
                ':CUS_AddressKn' => $row['CUS_AddressKn'],
                ':CUS_EntCustId' => $row['CUS_EntCustId'],
                ':CustomerStatus' => $row['CustomerStatus'],
                ':CUS_Phone' => $row['CUS_Phone'],
                ':CUS_Carrier' => $row['CUS_Carrier'],
                ':CUS_MailAddress' => $row['CUS_MailAddress'],
                ':CUS_Occupation' => $row['CUS_Occupation'],
                ':CUS_CorporateName' => $row['CUS_CorporateName'],
                ':CUS_DivisionName' => $row['CUS_DivisionName'],
                ':CUS_CpNameKj' => $row['CUS_CpNameKj'],
                ':CreditInfo' => $row['CreditInfo'],
                ':ORD_Chg_NonChargeFlg' => $row['ORD_Chg_NonChargeFlg'],
                ':ORD_AnotherDeliFlg' => $row['ORD_AnotherDeliFlg'],
                ':DEL_DestNameKj' => $row['DEL_DestNameKj'],
                ':DEL_DestNameKn' => $row['DEL_DestNameKn'],
                ':DEL_PostalCode' => $row['DEL_PostalCode'],
                ':DEL_UnitingAddress' => $row['DEL_UnitingAddress'],
                ':DEL_Phone' => $row['DEL_Phone'],
                ':OEM_OemNameKj' => $row['OEM_OemNameKj'],
                ':ENT_EnterpriseNameKj' => $row['ENT_EnterpriseNameKj'],
                ':SIT_SiteNameKj' => $row['SIT_SiteNameKj'],
                ':ENT_CpNameKj' => $row['ENT_CpNameKj'],
                ':ENT_ContactPhoneNumber' => $row['ENT_ContactPhoneNumber'],
                ':OrderItemInfo' => $row['OrderItemInfo'],
                ':DeliveryFee' => $row['DeliveryFee'],
                ':SettlementFee' => $row['SettlementFee'],
                ':ExTaxAmount' => $row['ExTaxAmount'],
                ':TotalSumMoney' => $row['TotalSumMoney'],
                ':ReclaimFee' => $row['ReclaimFee'],
                ':OtherCombinedAmount' => $row['OtherCombinedAmount'],
                ':ORD_InstallmentPlanAmount' => $row['ORD_InstallmentPlanAmount'],
                ':TotalClaimMoney' => $row['TotalClaimMoney'],
                ':ORD_Incre_Note' => $row['ORD_Incre_Note'],
                ':JudgeSystemResult' => $row['JudgeSystemResult'],
                ':PastDays' => $row['PastDays'],
                ':ORD_RemindClass' => $row['ORD_RemindClass'],
                ':MAN_RemindStopFlg' => $row['MAN_RemindStopFlg'],
                ':ORD_FinalityRemindDate' => $row['ORD_FinalityRemindDate'],
                ':ORD_FinalityRemindOpId' => $row['ORD_FinalityRemindOpId'],
                ':ORD_PromPayDate' => $row['ORD_PromPayDate'],
                ':CUS_ResidentCard' => $row['CUS_ResidentCard'],
                ':ORD_LonghandLetter' => $row['ORD_LonghandLetter'],
                ':ORD_BriefNote' => $row['ORD_BriefNote'],
                ':ORD_TouchHistoryFlg' => $row['ORD_TouchHistoryFlg'],
                ':ORD_VisitFlg' => $row['ORD_VisitFlg'],
                ':ReceiptPastDays' => $row['ReceiptPastDays'],
                ':ORD_FinalityCollectionMean' => $row['ORD_FinalityCollectionMean'],
                ':ORD_ClaimStopReleaseDate' => $row['ORD_ClaimStopReleaseDate'],
                ':ORD_LetterClaimStopFlg' => $row['ORD_LetterClaimStopFlg'],
                ':ORD_MailClaimStopFlg' => $row['ORD_MailClaimStopFlg'],
                ':ORD_Tel30DaysFlg' => $row['ORD_Tel30DaysFlg'],
                ':ORD_Tel90DaysFlg' => $row['ORD_Tel90DaysFlg'],
                ':CUS_Cinfo1' => $row['CUS_Cinfo1'],
                ':CUS_CinfoNote1' => $row['CUS_CinfoNote1'],
                ':CUS_CinfoStatus1' => $row['CUS_CinfoStatus1'],
                ':CUS_Cinfo2' => $row['CUS_Cinfo2'],
                ':CUS_CinfoNote2' => $row['CUS_CinfoNote2'],
                ':CUS_CinfoStatus2' => $row['CUS_CinfoStatus2'],
                ':CUS_Cinfo3' => $row['CUS_Cinfo3'],
                ':CUS_CinfoNote3' => $row['CUS_CinfoNote3'],
                ':CUS_CinfoStatus3' => $row['CUS_CinfoStatus3'],
                ':ORD_OemClaimTransDate' => $row['ORD_OemClaimTransDate'],
                ':ORD_Dmg_DecisionDate' => $row['ORD_Dmg_DecisionDate'],
                ':ORD_Oem_Note' => $row['ORD_Oem_Note'],
                ':IncreArClass' => $row['IncreArClass'],
                ':ORD_Incre_ScoreTotal' => $row['ORD_Incre_ScoreTotal'],
                ':CJR_TotalScore' => $row['CJR_TotalScore'],
                ':ORD_Incre_Status' => $row['ORD_Incre_Status'],
                ':ORD_Dmi_Status' => $row['ORD_Dmi_Status'],
                ':ORD_Dmi_DecisionDate' => $row['ORD_Dmi_DecisionDate'],
                ':ORD_Dmi_ResponseNote' => $row['ORD_Dmi_ResponseNote'],
                ':ITM_Deli_JournalIncDate' => $row['ITM_Deli_JournalIncDate'],
                ':ITM_Deli_DeliveryMethod' => $row['ITM_Deli_DeliveryMethod'],
                ':ITM_Deli_JournalNumber' => $row['ITM_Deli_JournalNumber'],
                ':CLM_F_ClaimDate' => $row['CLM_F_ClaimDate'],
                ':CLM_F_LimitDate' => $row['CLM_F_LimitDate'],
                ':CLM_ClaimDate' => $row['CLM_ClaimDate'],
                ':CLM_LimitDate' => $row['CLM_LimitDate'],
                ':ORD_Deli_ConfirmArrivalDate' => $row['ORD_Deli_ConfirmArrivalDate'],
                ':ORD_Deli_ConfirmArrivalFlg' => $row['ORD_Deli_ConfirmArrivalFlg'],
                ':PAS_ClearConditionForCharge' => $row['PAS_ClearConditionForCharge'],
                ':PAS_ClearConditionDate' => $row['PAS_ClearConditionDate'],
                ':ORD_Chg_FixedDate' => $row['ORD_Chg_FixedDate'],
                ':PAC_ExecScheduleDate' => $row['PAC_ExecScheduleDate'],
                ':REC_ReceiptDate' => $row['REC_ReceiptDate'],
                ':REC_ReceiptProcessDate' => $row['REC_ReceiptProcessDate'],
                ':REC_ReceiptClass' => $row['REC_ReceiptClass'],
                ':CLM_ClaimedBalance' => $row['CLM_ClaimedBalance'],
                ':STF_StampFee' => $row['STF_StampFee'],
                ':REC_DepositDate' => $row['REC_DepositDate'],
                ':MailInfo' => $row['MailInfo'],
                ':ReClaimInfo' => $row['ReClaimInfo'],
                ':CNL_CancelDate' => $row['CNL_CancelDate'],
                ':CNL_CancelReasonCode' => $row['CNL_CancelReasonCode'],
                ':ORD_MailPaymentSoonDate' => $row['ORD_MailPaymentSoonDate'],
                ':ORD_MailLimitPassageCount' => $row['ORD_MailLimitPassageCount'],
                ':ORD_MailLimitPassageDate' => $row['ORD_MailLimitPassageDate'],
                ':ORD_Ent_Note' => $row['ORD_Ent_Note'],
                ':ORD_CombinedParentOrderSeq' => $row['ORD_CombinedParentOrderSeq'],
                ':ORD_CombinedOrderSeq' => $row['ORD_CombinedOrderSeq'],
                ':Reserve' => $row['Reserve'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
        );

        return $stm->execute($prm);
    }

    /**
     * 注文履歴データより、最新与信金額を取得
     *
     * @param int $oseq 注文番号
     * @return int|null 該当がない場合はnull
     */
    public function getLastCreditJudgeAmount($oseq)
    {
        // 自動与信（OK）、自動与信（保留）、社内与信（OK）、社内与信（保留）、与信NG復活のうち、最新の利用額を最新の与信金額とする
        $sql = " SELECT ORD_UseAmount FROM T_OrderHistory WHERE HistoryReasonCode IN (21, 23, 24, 26, 27) AND OrderSeq = :OrderSeq ORDER BY HistorySeq DESC LIMIT 1 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $oseq,
        );

        $ri = $stm->execute($prm)->current();

        if ($ri == false) {
            // 該当データがない場合はnull
            $result = null;
        }
        else {
            $result = (int)$ri['ORD_UseAmount'];
        }

        return $result;
    }
}
