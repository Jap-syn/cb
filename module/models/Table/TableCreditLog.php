<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * TableCreditLogテーブルへのアダプタ
 */
class TableCreditLog
{
    protected $_name = 'T_CreditLog';
    protected $_primary = array ('Seq');
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
     * 新しいT_CreditLogの行クラスを作成する。作成された行はまだテーブルに属さないため、
     * 値を適切に設定してsaveNew()を実行する必要がある
     *
     * @param array|null $data 初期データの連想配列
     * @return array
     */
    public function newRow($data = array()) {
        if( ! is_array( $data ) ) $data = array();

        // 初期化の明示が必要な項目のみ
        $data = array_merge( $data, array(
                'Jud_judgeTOrderClass' => 0,
                'Jud_MultiOrderScore' => 0,
                'Jud_MultiOrderYN' => 0,
                'Jud_Cust_IncreArName' => -1,
                'Jud_Cust_IncreArAddr' => -1,
                'Jud_Cust_IncreArTel' => -1,
                'Jud_Deli_IncreArName' => -1,
                'Jud_Deli_IncreArAddr' => -1,
                'Jud_Deli_IncreArTel' => -1,
                'Jud_CreditCriterion' => 0,
                'Jud_AutoUseAmountOverYN' => 0,
                'Jud_SaikenCancelScore' => 0,
                'Jud_SaikenCancelYN' => 0,
                'Jud_NonPaymentDaysScore' => 0,
                'Jud_NonPaymentDaysYN' => 0,
                'Jud_NonPaymentCntScore' => 0,
                'Jud_NonPaymentCntYN' => 0,
                'Jud_NonPaymentAmtScore' => 0,
                'Jud_UnpaidCntScore' => 0,
                'Jud_UnpaidCntYN' => 0,
                'Jud_UnpaidAmtScore' => 0,
                'Jud_PastOrdersScore' => 0,
                'Jud_EntNoteScore' => 0,
                'Jud_IdentityDocumentFlgScore' => 0,
                'Jud_MischiefCancelScore' => 0,
                'Jud_CustomerScore' => 0,
                'Jud_CustomerSeqs' => null,
                'Jud_OrderItemsScore' => 0,
                'Jud_OrderItemsSeqs' => null,
                'Jud_DeliveryDestinationScore' => 0,
                'Jud_DeliveryDestinationSeqs' => null,
                'Jud_EnterpriseScore' => 0,
                'Jud_EnterpriseSeqs' => null,
                'Jud_UseAmountScore' => 0,
                'Jud_UseAmountOverYN' => 0,
                'Jud_CoreStatus' => 0,
                'Jud_IluStatus' => 0,
                'Jud_JintecStatus' => 0,
                'Jud_ManualStatus' => 0,
                'JintecManualJudgeFlg' => 0,
                'Jud_DefectOrderYN' => 0,
        ) );

        return $data;
    }

    /**
     * 与信結果ログを取得する
     *
     * @param int $seq シーケンス
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM T_CreditLog WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
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
        $sql  = " INSERT INTO T_CreditLog (StartTime, EndTime, OrderSeq, OrderId, CjrSeq, JtcSeq, CotSeq, Oem_CreditCriterion, Oem_AutoCreditDateFrom, Oem_AutoCreditDateTo, Ent_JintecFlg, Ent_AutoJudgeFlg, Ent_ManualJudgeFlg, Ent_CreditThreadNo, Ent_UseAmountLimitForCreditJudge, Ent_AutoCreditJudgeMode, Ent_JudgeSystemFlg, Ent_CreditJudgePendingRequest, Sit_T_OrderClass, Sit_AutoCreditLimitAmount, Sit_CreditOrderUseAmount, Sit_AverageUnitPriceRate, Sit_AutoCreditDateFrom, Sit_AutoCreditDateTo, Sit_AutoCreditCriterion, Sit_CreditJudgeMethod, Sit_MultiOrderScore, Sit_MultiOrderCount, Sit_SitClass, Def_CpId1_Point, Def_CpId2_Point, Def_CpId105_Point, Def_CpId106_Point, Def_CpId107_Point, Def_CpId108_Point, Def_CpId109_Point, Def_CpId201_Point, Def_CpId202_Point, Def_CpId203_Point, Def_CpId206_Point, Def_CpId301_Point, Def_CpId301_GeneralProp, Def_CpId302_Point, Def_CpId302_GeneralProp, Def_CpId303_Point, Def_CpId303_GeneralProp, Def_CpId304_Point, Def_CpId304_GeneralProp, Def_CpId401_Rate, Def_CpId402_Rate, Def_CpId403_Rate, Def_CoreSystemHoldMIN, Def_CoreSystemHoldMAX, Def_CpId501_Description, Def_JudgeSystemHoldMIN, Def_JudgeSystemHoldMAX, Org_CpId105_Point, Org_CpId106_Point, Org_CpId107_Point, Org_CpId108_Point, Org_CpId109_Point, Org_CpId201_Point, Org_CpId202_Point, Org_CpId203_Point, Org_CpId206_Point, Org_CpId301_Point, Org_CpId301_GeneralProp, Org_CpId302_Point, Org_CpId302_GeneralProp, Org_CpId303_Point, Org_CpId303_GeneralProp, Org_CpId304_Point, Org_CpId304_GeneralProp, Org_CpId401_Rate, Org_CpId402_Rate, Org_CpId403_Rate, Org_CoreSystemHoldMIN, Org_CoreSystemHoldMAX, Org_CpId501_Description, Org_JudgeSystemHoldMIN, Org_JudgeSystemHoldMAX, Sys_enterpriseid, Sys_AutoCreditDateFrom, Sys_AutoCreditDateTo, Sys_AutoCreditLimitAmount, Sys_BtoBCreditLimitAmount, Sys_CreditCriterion, Sys_CreditOrderUseAmount, Sys_MultiOrderDays, Sys_default_average_unit_price_rate, Jud_judgeTOrderClass, Jud_MultiOrderScore, Jud_MultiOrderYN, Jud_Cust_IncreArName, Jud_Cust_IncreArAddr, Jud_Cust_IncreArTel, Jud_Deli_IncreArName, Jud_Deli_IncreArAddr, Jud_Deli_IncreArTel, Jud_CreditCriterion, Jud_AutoUseAmountOverYN, Jud_SaikenCancelScore, Jud_SaikenCancelYN, Jud_NonPaymentDaysScore, Jud_NonPaymentDaysYN, Jud_NonPaymentCntScore, Jud_NonPaymentCntYN, Jud_NonPaymentAmtScore, Jud_UnpaidCntScore, Jud_UnpaidCntYN, Jud_UnpaidAmtScore, Jud_PastOrdersScore, Jud_EntNoteScore, Jud_IdentityDocumentFlgScore, Jud_MischiefCancelScore, Jud_CustomerScore, Jud_CustomerSeqs, Jud_OrderItemsScore, Jud_OrderItemsSeqs, Jud_DeliveryDestinationScore, Jud_DeliveryDestinationSeqs, Jud_EnterpriseScore, Jud_EnterpriseSeqs, Jud_UseAmountScore, Jud_UseAmountOverYN, Jud_CoreStatus, Jud_IluStatus, Jud_JintecStatus, Jud_ManualStatus, JintecManualJudgeFlg, Incre_SnapShot, Jud_DefectOrderYN) VALUES (";
        $sql .= "   :StartTime ";
        $sql .= " , :EndTime ";
        $sql .= " , :OrderSeq ";
        $sql .= " , :OrderId ";
        $sql .= " , :CjrSeq ";
        $sql .= " , :JtcSeq ";
        $sql .= " , :CotSeq ";
        $sql .= " , :Oem_CreditCriterion ";
        $sql .= " , :Oem_AutoCreditDateFrom ";
        $sql .= " , :Oem_AutoCreditDateTo ";
        $sql .= " , :Ent_JintecFlg ";
        $sql .= " , :Ent_AutoJudgeFlg ";
        $sql .= " , :Ent_ManualJudgeFlg ";
        $sql .= " , :Ent_CreditThreadNo ";
        $sql .= " , :Ent_UseAmountLimitForCreditJudge ";
        $sql .= " , :Ent_AutoCreditJudgeMode ";
        $sql .= " , :Ent_JudgeSystemFlg ";
        $sql .= " , :Ent_CreditJudgePendingRequest ";
        $sql .= " , :Sit_T_OrderClass ";
        $sql .= " , :Sit_AutoCreditLimitAmount ";
        $sql .= " , :Sit_CreditOrderUseAmount ";
        $sql .= " , :Sit_AverageUnitPriceRate ";
        $sql .= " , :Sit_AutoCreditDateFrom ";
        $sql .= " , :Sit_AutoCreditDateTo ";
        $sql .= " , :Sit_AutoCreditCriterion ";
        $sql .= " , :Sit_CreditJudgeMethod ";
        $sql .= " , :Sit_MultiOrderScore ";
        $sql .= " , :Sit_MultiOrderCount ";
        $sql .= " , :Sit_SitClass ";
        $sql .= " , :Def_CpId1_Point ";
        $sql .= " , :Def_CpId2_Point ";
        $sql .= " , :Def_CpId105_Point ";
        $sql .= " , :Def_CpId106_Point ";
        $sql .= " , :Def_CpId107_Point ";
        $sql .= " , :Def_CpId108_Point ";
        $sql .= " , :Def_CpId109_Point ";
        $sql .= " , :Def_CpId201_Point ";
        $sql .= " , :Def_CpId202_Point ";
        $sql .= " , :Def_CpId203_Point ";
        $sql .= " , :Def_CpId206_Point ";
        $sql .= " , :Def_CpId301_Point ";
        $sql .= " , :Def_CpId301_GeneralProp ";
        $sql .= " , :Def_CpId302_Point ";
        $sql .= " , :Def_CpId302_GeneralProp ";
        $sql .= " , :Def_CpId303_Point ";
        $sql .= " , :Def_CpId303_GeneralProp ";
        $sql .= " , :Def_CpId304_Point ";
        $sql .= " , :Def_CpId304_GeneralProp ";
        $sql .= " , :Def_CpId401_Rate ";
        $sql .= " , :Def_CpId402_Rate ";
        $sql .= " , :Def_CpId403_Rate ";
        $sql .= " , :Def_CoreSystemHoldMIN ";
        $sql .= " , :Def_CoreSystemHoldMAX ";
        $sql .= " , :Def_CpId501_Description ";
        $sql .= " , :Def_JudgeSystemHoldMIN ";
        $sql .= " , :Def_JudgeSystemHoldMAX ";
        $sql .= " , :Org_CpId105_Point ";
        $sql .= " , :Org_CpId106_Point ";
        $sql .= " , :Org_CpId107_Point ";
        $sql .= " , :Org_CpId108_Point ";
        $sql .= " , :Org_CpId109_Point ";
        $sql .= " , :Org_CpId201_Point ";
        $sql .= " , :Org_CpId202_Point ";
        $sql .= " , :Org_CpId203_Point ";
        $sql .= " , :Org_CpId206_Point ";
        $sql .= " , :Org_CpId301_Point ";
        $sql .= " , :Org_CpId301_GeneralProp ";
        $sql .= " , :Org_CpId302_Point ";
        $sql .= " , :Org_CpId302_GeneralProp ";
        $sql .= " , :Org_CpId303_Point ";
        $sql .= " , :Org_CpId303_GeneralProp ";
        $sql .= " , :Org_CpId304_Point ";
        $sql .= " , :Org_CpId304_GeneralProp ";
        $sql .= " , :Org_CpId401_Rate ";
        $sql .= " , :Org_CpId402_Rate ";
        $sql .= " , :Org_CpId403_Rate ";
        $sql .= " , :Org_CoreSystemHoldMIN ";
        $sql .= " , :Org_CoreSystemHoldMAX ";
        $sql .= " , :Org_CpId501_Description ";
        $sql .= " , :Org_JudgeSystemHoldMIN ";
        $sql .= " , :Org_JudgeSystemHoldMAX ";
        $sql .= " , :Sys_enterpriseid ";
        $sql .= " , :Sys_AutoCreditDateFrom ";
        $sql .= " , :Sys_AutoCreditDateTo ";
        $sql .= " , :Sys_AutoCreditLimitAmount ";
        $sql .= " , :Sys_BtoBCreditLimitAmount ";
        $sql .= " , :Sys_CreditCriterion ";
        $sql .= " , :Sys_CreditOrderUseAmount ";
        $sql .= " , :Sys_MultiOrderDays ";
        $sql .= " , :Sys_default_average_unit_price_rate ";
        $sql .= " , :Jud_judgeTOrderClass ";
        $sql .= " , :Jud_MultiOrderScore ";
        $sql .= " , :Jud_MultiOrderYN ";
        $sql .= " , :Jud_Cust_IncreArName ";
        $sql .= " , :Jud_Cust_IncreArAddr ";
        $sql .= " , :Jud_Cust_IncreArTel ";
        $sql .= " , :Jud_Deli_IncreArName ";
        $sql .= " , :Jud_Deli_IncreArAddr ";
        $sql .= " , :Jud_Deli_IncreArTel ";
        $sql .= " , :Jud_CreditCriterion ";
        $sql .= " , :Jud_AutoUseAmountOverYN ";
        $sql .= " , :Jud_SaikenCancelScore ";
        $sql .= " , :Jud_SaikenCancelYN ";
        $sql .= " , :Jud_NonPaymentDaysScore ";
        $sql .= " , :Jud_NonPaymentDaysYN ";
        $sql .= " , :Jud_NonPaymentCntScore ";
        $sql .= " , :Jud_NonPaymentCntYN ";
        $sql .= " , :Jud_NonPaymentAmtScore ";
        $sql .= " , :Jud_UnpaidCntScore ";
        $sql .= " , :Jud_UnpaidCntYN ";
        $sql .= " , :Jud_UnpaidAmtScore ";
        $sql .= " , :Jud_PastOrdersScore ";
        $sql .= " , :Jud_EntNoteScore ";
        $sql .= " , :Jud_IdentityDocumentFlgScore ";
        $sql .= " , :Jud_MischiefCancelScore ";
        $sql .= " , :Jud_CustomerScore ";
        $sql .= " , :Jud_CustomerSeqs ";
        $sql .= " , :Jud_OrderItemsScore ";
        $sql .= " , :Jud_OrderItemsSeqs ";
        $sql .= " , :Jud_DeliveryDestinationScore ";
        $sql .= " , :Jud_DeliveryDestinationSeqs ";
        $sql .= " , :Jud_EnterpriseScore ";
        $sql .= " , :Jud_EnterpriseSeqs ";
        $sql .= " , :Jud_UseAmountScore ";
        $sql .= " , :Jud_UseAmountOverYN ";
        $sql .= " , :Jud_CoreStatus ";
        $sql .= " , :Jud_IluStatus ";
        $sql .= " , :Jud_JintecStatus ";
        $sql .= " , :Jud_ManualStatus ";
        $sql .= " , :JintecManualJudgeFlg ";
        $sql .= " , :Incre_SnapShot ";
        $sql .= " , :Jud_DefectOrderYN ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':StartTime' => $data['StartTime'],
                ':EndTime' => $data['EndTime'],
                ':OrderSeq' => $data['OrderSeq'],
                ':OrderId' => $data['OrderId'],
                ':CjrSeq' => $data['CjrSeq'],
                ':JtcSeq' => $data['JtcSeq'],
                ':CotSeq' => $data['CotSeq'],
                ':Oem_CreditCriterion' => $data['Oem_CreditCriterion'],
                ':Oem_AutoCreditDateFrom' => $data['Oem_AutoCreditDateFrom'],
                ':Oem_AutoCreditDateTo' => $data['Oem_AutoCreditDateTo'],
                ':Ent_JintecFlg' => $data['Ent_JintecFlg'],
                ':Ent_AutoJudgeFlg' => $data['Ent_AutoJudgeFlg'],
                ':Ent_ManualJudgeFlg' => $data['Ent_ManualJudgeFlg'],
                ':Ent_CreditThreadNo' => $data['Ent_CreditThreadNo'],
                ':Ent_UseAmountLimitForCreditJudge' => $data['Ent_UseAmountLimitForCreditJudge'],
                ':Ent_AutoCreditJudgeMode' => $data['Ent_AutoCreditJudgeMode'],
                ':Ent_JudgeSystemFlg' => $data['Ent_JudgeSystemFlg'],
                ':Ent_CreditJudgePendingRequest' => $data['Ent_CreditJudgePendingRequest'],
                ':Sit_T_OrderClass' => $data['Sit_T_OrderClass'],
                ':Sit_AutoCreditLimitAmount' => $data['Sit_AutoCreditLimitAmount'],
                ':Sit_CreditOrderUseAmount' => $data['Sit_CreditOrderUseAmount'],
                ':Sit_AverageUnitPriceRate' => $data['Sit_AverageUnitPriceRate'],
                ':Sit_AutoCreditDateFrom' => $data['Sit_AutoCreditDateFrom'],
                ':Sit_AutoCreditDateTo' => $data['Sit_AutoCreditDateTo'],
                ':Sit_AutoCreditCriterion' => $data['Sit_AutoCreditCriterion'],
                ':Sit_CreditJudgeMethod' => $data['Sit_CreditJudgeMethod'],
                ':Sit_MultiOrderScore' => $data['Sit_MultiOrderScore'],
                ':Sit_MultiOrderCount' => $data['Sit_MultiOrderCount'],
                ':Sit_SitClass' => $data['Sit_SitClass'],
                ':Def_CpId1_Point' => $data['Def_CpId1_Point'],
                ':Def_CpId2_Point' => $data['Def_CpId2_Point'],
                ':Def_CpId105_Point' => $data['Def_CpId105_Point'],
                ':Def_CpId106_Point' => $data['Def_CpId106_Point'],
                ':Def_CpId107_Point' => $data['Def_CpId107_Point'],
                ':Def_CpId108_Point' => $data['Def_CpId108_Point'],
                ':Def_CpId109_Point' => $data['Def_CpId109_Point'],
                ':Def_CpId201_Point' => $data['Def_CpId201_Point'],
                ':Def_CpId202_Point' => $data['Def_CpId202_Point'],
                ':Def_CpId203_Point' => $data['Def_CpId203_Point'],
                ':Def_CpId206_Point' => $data['Def_CpId206_Point'],
                ':Def_CpId301_Point' => $data['Def_CpId301_Point'],
                ':Def_CpId301_GeneralProp' => $data['Def_CpId301_GeneralProp'],
                ':Def_CpId302_Point' => $data['Def_CpId302_Point'],
                ':Def_CpId302_GeneralProp' => $data['Def_CpId302_GeneralProp'],
                ':Def_CpId303_Point' => $data['Def_CpId303_Point'],
                ':Def_CpId303_GeneralProp' => $data['Def_CpId303_GeneralProp'],
                ':Def_CpId304_Point' => $data['Def_CpId304_Point'],
                ':Def_CpId304_GeneralProp' => $data['Def_CpId304_GeneralProp'],
                ':Def_CpId401_Rate' => $data['Def_CpId401_Rate'],
                ':Def_CpId402_Rate' => $data['Def_CpId402_Rate'],
                ':Def_CpId403_Rate' => $data['Def_CpId403_Rate'],
                ':Def_CoreSystemHoldMIN' => $data['Def_CoreSystemHoldMIN'],
                ':Def_CoreSystemHoldMAX' => $data['Def_CoreSystemHoldMAX'],
                ':Def_CpId501_Description' => $data['Def_CpId501_Description'],
                ':Def_JudgeSystemHoldMIN' => $data['Def_JudgeSystemHoldMIN'],
                ':Def_JudgeSystemHoldMAX' => $data['Def_JudgeSystemHoldMAX'],
                ':Org_CpId105_Point' => $data['Org_CpId105_Point'],
                ':Org_CpId106_Point' => $data['Org_CpId106_Point'],
                ':Org_CpId107_Point' => $data['Org_CpId107_Point'],
                ':Org_CpId108_Point' => $data['Org_CpId108_Point'],
                ':Org_CpId109_Point' => $data['Org_CpId109_Point'],
                ':Org_CpId201_Point' => $data['Org_CpId201_Point'],
                ':Org_CpId202_Point' => $data['Org_CpId202_Point'],
                ':Org_CpId203_Point' => $data['Org_CpId203_Point'],
                ':Org_CpId206_Point' => $data['Org_CpId206_Point'],
                ':Org_CpId301_Point' => $data['Org_CpId301_Point'],
                ':Org_CpId301_GeneralProp' => $data['Org_CpId301_GeneralProp'],
                ':Org_CpId302_Point' => $data['Org_CpId302_Point'],
                ':Org_CpId302_GeneralProp' => $data['Org_CpId302_GeneralProp'],
                ':Org_CpId303_Point' => $data['Org_CpId303_Point'],
                ':Org_CpId303_GeneralProp' => $data['Org_CpId303_GeneralProp'],
                ':Org_CpId304_Point' => $data['Org_CpId304_Point'],
                ':Org_CpId304_GeneralProp' => $data['Org_CpId304_GeneralProp'],
                ':Org_CpId401_Rate' => $data['Org_CpId401_Rate'],
                ':Org_CpId402_Rate' => $data['Org_CpId402_Rate'],
                ':Org_CpId403_Rate' => $data['Org_CpId403_Rate'],
                ':Org_CoreSystemHoldMIN' => $data['Org_CoreSystemHoldMIN'],
                ':Org_CoreSystemHoldMAX' => $data['Org_CoreSystemHoldMAX'],
                ':Org_CpId501_Description' => $data['Org_CpId501_Description'],
                ':Org_JudgeSystemHoldMIN' => $data['Org_JudgeSystemHoldMIN'],
                ':Org_JudgeSystemHoldMAX' => $data['Org_JudgeSystemHoldMAX'],
                ':Sys_enterpriseid' => $data['Sys_enterpriseid'],
                ':Sys_AutoCreditDateFrom' => $data['Sys_AutoCreditDateFrom'],
                ':Sys_AutoCreditDateTo' => $data['Sys_AutoCreditDateTo'],
                ':Sys_AutoCreditLimitAmount' => $data['Sys_AutoCreditLimitAmount'],
                ':Sys_BtoBCreditLimitAmount' => $data['Sys_BtoBCreditLimitAmount'],
                ':Sys_CreditCriterion' => $data['Sys_CreditCriterion'],
                ':Sys_CreditOrderUseAmount' => $data['Sys_CreditOrderUseAmount'],
                ':Sys_MultiOrderDays' => $data['Sys_MultiOrderDays'],
                ':Sys_default_average_unit_price_rate' => $data['Sys_default_average_unit_price_rate'],
                ':Jud_judgeTOrderClass' => $data['Jud_judgeTOrderClass'],
                ':Jud_MultiOrderScore' => $data['Jud_MultiOrderScore'],
                ':Jud_MultiOrderYN' => $data['Jud_MultiOrderYN'],
                ':Jud_Cust_IncreArName' => $data['Jud_Cust_IncreArName'],
                ':Jud_Cust_IncreArAddr' => $data['Jud_Cust_IncreArAddr'],
                ':Jud_Cust_IncreArTel' => $data['Jud_Cust_IncreArTel'],
                ':Jud_Deli_IncreArName' => $data['Jud_Deli_IncreArName'],
                ':Jud_Deli_IncreArAddr' => $data['Jud_Deli_IncreArAddr'],
                ':Jud_Deli_IncreArTel' => $data['Jud_Deli_IncreArTel'],
                ':Jud_CreditCriterion' => $data['Jud_CreditCriterion'],
                ':Jud_AutoUseAmountOverYN' => $data['Jud_AutoUseAmountOverYN'],
                ':Jud_SaikenCancelScore' => $data['Jud_SaikenCancelScore'],
                ':Jud_SaikenCancelYN' => $data['Jud_SaikenCancelYN'],
                ':Jud_NonPaymentDaysScore' => $data['Jud_NonPaymentDaysScore'],
                ':Jud_NonPaymentDaysYN' => $data['Jud_NonPaymentDaysYN'],
                ':Jud_NonPaymentCntScore' => $data['Jud_NonPaymentCntScore'],
                ':Jud_NonPaymentCntYN' => $data['Jud_NonPaymentCntYN'],
                ':Jud_NonPaymentAmtScore' => $data['Jud_NonPaymentAmtScore'],
                ':Jud_UnpaidCntScore' => $data['Jud_UnpaidCntScore'],
                ':Jud_UnpaidCntYN' => $data['Jud_UnpaidCntYN'],
                ':Jud_UnpaidAmtScore' => $data['Jud_UnpaidAmtScore'],
                ':Jud_PastOrdersScore' => $data['Jud_PastOrdersScore'],
                ':Jud_EntNoteScore' => $data['Jud_EntNoteScore'],
                ':Jud_IdentityDocumentFlgScore' => $data['Jud_IdentityDocumentFlgScore'],
                ':Jud_MischiefCancelScore' => $data['Jud_MischiefCancelScore'],
                ':Jud_CustomerScore' => $data['Jud_CustomerScore'],
                ':Jud_CustomerSeqs' => $data['Jud_CustomerSeqs'],
                ':Jud_OrderItemsScore' => $data['Jud_OrderItemsScore'],
                ':Jud_OrderItemsSeqs' => $data['Jud_OrderItemsSeqs'],
                ':Jud_DeliveryDestinationScore' => $data['Jud_DeliveryDestinationScore'],
                ':Jud_DeliveryDestinationSeqs' => $data['Jud_DeliveryDestinationSeqs'],
                ':Jud_EnterpriseScore' => $data['Jud_EnterpriseScore'],
                ':Jud_EnterpriseSeqs' => $data['Jud_EnterpriseSeqs'],
                ':Jud_UseAmountScore' => $data['Jud_UseAmountScore'],
                ':Jud_UseAmountOverYN' => $data['Jud_UseAmountOverYN'],
                ':Jud_CoreStatus' => isset($data['Jud_CoreStatus']) ? $data['Jud_CoreStatus'] : 0,
                ':Jud_IluStatus' => isset($data['Jud_IluStatus']) ? $data['Jud_IluStatus'] : 0,
                ':Jud_JintecStatus' => isset($data['Jud_JintecStatus']) ? $data['Jud_JintecStatus'] : 0,
                ':Jud_ManualStatus' => isset($data['Jud_ManualStatus']) ? $data['Jud_ManualStatus'] : 0,
                ':JintecManualJudgeFlg' => isset($data['JintecManualJudgeFlg']) ? $data['JintecManualJudgeFlg'] : 0,
                ':Incre_SnapShot' => $data['Incre_SnapShot'],
                ':Jud_DefectOrderYN' => $data['Jud_DefectOrderYN'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $seq シーケンス
     * @return ResultInterface
     */
    public function saveUpdate($data, $seq)
    {
        $row = $this->find($seq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_CreditLog ";
        $sql .= " SET ";
        $sql .= "     StartTime = :StartTime ";
        $sql .= " ,   EndTime = :EndTime ";
        $sql .= " ,   OrderSeq = :OrderSeq ";
        $sql .= " ,   OrderId = :OrderId ";
        $sql .= " ,   CjrSeq = :CjrSeq ";
        $sql .= " ,   JtcSeq = :JtcSeq ";
        $sql .= " ,   CotSeq = :CotSeq ";
        $sql .= " ,   Oem_CreditCriterion = :Oem_CreditCriterion ";
        $sql .= " ,   Oem_AutoCreditDateFrom = :Oem_AutoCreditDateFrom ";
        $sql .= " ,   Oem_AutoCreditDateTo = :Oem_AutoCreditDateTo ";
        $sql .= " ,   Ent_JintecFlg = :Ent_JintecFlg ";
        $sql .= " ,   Ent_AutoJudgeFlg = :Ent_AutoJudgeFlg ";
        $sql .= " ,   Ent_ManualJudgeFlg = :Ent_ManualJudgeFlg ";
        $sql .= " ,   Ent_CreditThreadNo = :Ent_CreditThreadNo ";
        $sql .= " ,   Ent_UseAmountLimitForCreditJudge = :Ent_UseAmountLimitForCreditJudge ";
        $sql .= " ,   Ent_AutoCreditJudgeMode = :Ent_AutoCreditJudgeMode ";
        $sql .= " ,   Ent_JudgeSystemFlg = :Ent_JudgeSystemFlg ";
        $sql .= " ,   Ent_CreditJudgePendingRequest = :Ent_CreditJudgePendingRequest ";
        $sql .= " ,   Sit_T_OrderClass = :Sit_T_OrderClass ";
        $sql .= " ,   Sit_AutoCreditLimitAmount = :Sit_AutoCreditLimitAmount ";
        $sql .= " ,   Sit_CreditOrderUseAmount = :Sit_CreditOrderUseAmount ";
        $sql .= " ,   Sit_AverageUnitPriceRate = :Sit_AverageUnitPriceRate ";
        $sql .= " ,   Sit_AutoCreditDateFrom = :Sit_AutoCreditDateFrom ";
        $sql .= " ,   Sit_AutoCreditDateTo = :Sit_AutoCreditDateTo ";
        $sql .= " ,   Sit_AutoCreditCriterion = :Sit_AutoCreditCriterion ";
        $sql .= " ,   Sit_CreditJudgeMethod = :Sit_CreditJudgeMethod ";
        $sql .= " ,   Sit_MultiOrderScore = :Sit_MultiOrderScore ";
        $sql .= " ,   Sit_MultiOrderCount = :Sit_MultiOrderCount ";
        $sql .= " ,   Sit_SitClass = :Sit_SitClass ";
        $sql .= " ,   Def_CpId1_Point = :Def_CpId1_Point ";
        $sql .= " ,   Def_CpId2_Point = :Def_CpId2_Point ";
        $sql .= " ,   Def_CpId105_Point = :Def_CpId105_Point ";
        $sql .= " ,   Def_CpId106_Point = :Def_CpId106_Point ";
        $sql .= " ,   Def_CpId107_Point = :Def_CpId107_Point ";
        $sql .= " ,   Def_CpId108_Point = :Def_CpId108_Point ";
        $sql .= " ,   Def_CpId109_Point = :Def_CpId109_Point ";
        $sql .= " ,   Def_CpId201_Point = :Def_CpId201_Point ";
        $sql .= " ,   Def_CpId202_Point = :Def_CpId202_Point ";
        $sql .= " ,   Def_CpId203_Point = :Def_CpId203_Point ";
        $sql .= " ,   Def_CpId206_Point = :Def_CpId206_Point ";
        $sql .= " ,   Def_CpId301_Point = :Def_CpId301_Point ";
        $sql .= " ,   Def_CpId301_GeneralProp = :Def_CpId301_GeneralProp ";
        $sql .= " ,   Def_CpId302_Point = :Def_CpId302_Point ";
        $sql .= " ,   Def_CpId302_GeneralProp = :Def_CpId302_GeneralProp ";
        $sql .= " ,   Def_CpId303_Point = :Def_CpId303_Point ";
        $sql .= " ,   Def_CpId303_GeneralProp = :Def_CpId303_GeneralProp ";
        $sql .= " ,   Def_CpId304_Point = :Def_CpId304_Point ";
        $sql .= " ,   Def_CpId304_GeneralProp = :Def_CpId304_GeneralProp ";
        $sql .= " ,   Def_CpId401_Rate = :Def_CpId401_Rate ";
        $sql .= " ,   Def_CpId402_Rate = :Def_CpId402_Rate ";
        $sql .= " ,   Def_CpId403_Rate = :Def_CpId403_Rate ";
        $sql .= " ,   Def_CoreSystemHoldMIN = :Def_CoreSystemHoldMIN ";
        $sql .= " ,   Def_CoreSystemHoldMAX = :Def_CoreSystemHoldMAX ";
        $sql .= " ,   Def_CpId501_Description = :Def_CpId501_Description ";
        $sql .= " ,   Def_JudgeSystemHoldMIN = :Def_JudgeSystemHoldMIN ";
        $sql .= " ,   Def_JudgeSystemHoldMAX = :Def_JudgeSystemHoldMAX ";
        $sql .= " ,   Org_CpId105_Point = :Org_CpId105_Point ";
        $sql .= " ,   Org_CpId106_Point = :Org_CpId106_Point ";
        $sql .= " ,   Org_CpId107_Point = :Org_CpId107_Point ";
        $sql .= " ,   Org_CpId108_Point = :Org_CpId108_Point ";
        $sql .= " ,   Org_CpId109_Point = :Org_CpId109_Point ";
        $sql .= " ,   Org_CpId201_Point = :Org_CpId201_Point ";
        $sql .= " ,   Org_CpId202_Point = :Org_CpId202_Point ";
        $sql .= " ,   Org_CpId203_Point = :Org_CpId203_Point ";
        $sql .= " ,   Org_CpId206_Point = :Org_CpId206_Point ";
        $sql .= " ,   Org_CpId301_Point = :Org_CpId301_Point ";
        $sql .= " ,   Org_CpId301_GeneralProp = :Org_CpId301_GeneralProp ";
        $sql .= " ,   Org_CpId302_Point = :Org_CpId302_Point ";
        $sql .= " ,   Org_CpId302_GeneralProp = :Org_CpId302_GeneralProp ";
        $sql .= " ,   Org_CpId303_Point = :Org_CpId303_Point ";
        $sql .= " ,   Org_CpId303_GeneralProp = :Org_CpId303_GeneralProp ";
        $sql .= " ,   Org_CpId304_Point = :Org_CpId304_Point ";
        $sql .= " ,   Org_CpId304_GeneralProp = :Org_CpId304_GeneralProp ";
        $sql .= " ,   Org_CpId401_Rate = :Org_CpId401_Rate ";
        $sql .= " ,   Org_CpId402_Rate = :Org_CpId402_Rate ";
        $sql .= " ,   Org_CpId403_Rate = :Org_CpId403_Rate ";
        $sql .= " ,   Org_CoreSystemHoldMIN = :Org_CoreSystemHoldMIN ";
        $sql .= " ,   Org_CoreSystemHoldMAX = :Org_CoreSystemHoldMAX ";
        $sql .= " ,   Org_CpId501_Description = :Org_CpId501_Description ";
        $sql .= " ,   Org_JudgeSystemHoldMIN = :Org_JudgeSystemHoldMIN ";
        $sql .= " ,   Org_JudgeSystemHoldMAX = :Org_JudgeSystemHoldMAX ";
        $sql .= " ,   Sys_enterpriseid = :Sys_enterpriseid ";
        $sql .= " ,   Sys_AutoCreditDateFrom = :Sys_AutoCreditDateFrom ";
        $sql .= " ,   Sys_AutoCreditDateTo = :Sys_AutoCreditDateTo ";
        $sql .= " ,   Sys_AutoCreditLimitAmount = :Sys_AutoCreditLimitAmount ";
        $sql .= " ,   Sys_BtoBCreditLimitAmount = :Sys_BtoBCreditLimitAmount ";
        $sql .= " ,   Sys_CreditCriterion = :Sys_CreditCriterion ";
        $sql .= " ,   Sys_CreditOrderUseAmount = :Sys_CreditOrderUseAmount ";
        $sql .= " ,   Sys_MultiOrderDays = :Sys_MultiOrderDays ";
        $sql .= " ,   Sys_default_average_unit_price_rate = :Sys_default_average_unit_price_rate ";
        $sql .= " ,   Jud_judgeTOrderClass = :Jud_judgeTOrderClass ";
        $sql .= " ,   Jud_MultiOrderScore = :Jud_MultiOrderScore ";
        $sql .= " ,   Jud_MultiOrderYN = :Jud_MultiOrderYN ";
        $sql .= " ,   Jud_Cust_IncreArName = :Jud_Cust_IncreArName ";
        $sql .= " ,   Jud_Cust_IncreArAddr = :Jud_Cust_IncreArAddr ";
        $sql .= " ,   Jud_Cust_IncreArTel = :Jud_Cust_IncreArTel ";
        $sql .= " ,   Jud_Deli_IncreArName = :Jud_Deli_IncreArName ";
        $sql .= " ,   Jud_Deli_IncreArAddr = :Jud_Deli_IncreArAddr ";
        $sql .= " ,   Jud_Deli_IncreArTel = :Jud_Deli_IncreArTel ";
        $sql .= " ,   Jud_CreditCriterion = :Jud_CreditCriterion ";
        $sql .= " ,   Jud_AutoUseAmountOverYN = :Jud_AutoUseAmountOverYN ";
        $sql .= " ,   Jud_SaikenCancelScore = :Jud_SaikenCancelScore ";
        $sql .= " ,   Jud_SaikenCancelYN = :Jud_SaikenCancelYN ";
        $sql .= " ,   Jud_NonPaymentDaysScore = :Jud_NonPaymentDaysScore ";
        $sql .= " ,   Jud_NonPaymentDaysYN = :Jud_NonPaymentDaysYN ";
        $sql .= " ,   Jud_NonPaymentCntScore = :Jud_NonPaymentCntScore ";
        $sql .= " ,   Jud_NonPaymentCntYN = :Jud_NonPaymentCntYN ";
        $sql .= " ,   Jud_NonPaymentAmtScore = :Jud_NonPaymentAmtScore ";
        $sql .= " ,   Jud_UnpaidCntScore = :Jud_UnpaidCntScore ";
        $sql .= " ,   Jud_UnpaidCntYN = :Jud_UnpaidCntYN ";
        $sql .= " ,   Jud_UnpaidAmtScore = :Jud_UnpaidAmtScore ";
        $sql .= " ,   Jud_PastOrdersScore = :Jud_PastOrdersScore ";
        $sql .= " ,   Jud_EntNoteScore = :Jud_EntNoteScore ";
        $sql .= " ,   Jud_IdentityDocumentFlgScore = :Jud_IdentityDocumentFlgScore ";
        $sql .= " ,   Jud_MischiefCancelScore = :Jud_MischiefCancelScore ";
        $sql .= " ,   Jud_CustomerScore = :Jud_CustomerScore ";
        $sql .= " ,   Jud_CustomerSeqs = :Jud_CustomerSeqs ";
        $sql .= " ,   Jud_OrderItemsScore = :Jud_OrderItemsScore ";
        $sql .= " ,   Jud_OrderItemsSeqs = :Jud_OrderItemsSeqs ";
        $sql .= " ,   Jud_DeliveryDestinationScore = :Jud_DeliveryDestinationScore ";
        $sql .= " ,   Jud_DeliveryDestinationSeqs = :Jud_DeliveryDestinationSeqs ";
        $sql .= " ,   Jud_EnterpriseScore = :Jud_EnterpriseScore ";
        $sql .= " ,   Jud_EnterpriseSeqs = :Jud_EnterpriseSeqs ";
        $sql .= " ,   Jud_UseAmountScore = :Jud_UseAmountScore ";
        $sql .= " ,   Jud_UseAmountOverYN = :Jud_UseAmountOverYN ";
        $sql .= " ,   Jud_CoreStatus = :Jud_CoreStatus ";
        $sql .= " ,   Jud_IluStatus = :Jud_IluStatus ";
        $sql .= " ,   Jud_JintecStatus = :Jud_JintecStatus ";
        $sql .= " ,   Jud_ManualStatus = :Jud_ManualStatus ";
        $sql .= " ,   JintecManualJudgeFlg = :JintecManualJudgeFlg ";
        $sql .= " ,   Incre_SnapShot = :Incre_SnapShot ";
        $sql .= " ,   Jud_DefectOrderYN = :Jud_DefectOrderYN ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $row['Seq'],
                ':StartTime' => $row['StartTime'],
                ':EndTime' => $row['EndTime'],
                ':OrderSeq' => $row['OrderSeq'],
                ':OrderId' => $row['OrderId'],
                ':CjrSeq' => $row['CjrSeq'],
                ':JtcSeq' => $row['JtcSeq'],
                ':CotSeq' => $row['CotSeq'],
                ':Oem_CreditCriterion' => $row['Oem_CreditCriterion'],
                ':Oem_AutoCreditDateFrom' => $row['Oem_AutoCreditDateFrom'],
                ':Oem_AutoCreditDateTo' => $row['Oem_AutoCreditDateTo'],
                ':Ent_JintecFlg' => $row['Ent_JintecFlg'],
                ':Ent_AutoJudgeFlg' => $row['Ent_AutoJudgeFlg'],
                ':Ent_ManualJudgeFlg' => $row['Ent_ManualJudgeFlg'],
                ':Ent_CreditThreadNo' => $row['Ent_CreditThreadNo'],
                ':Ent_UseAmountLimitForCreditJudge' => $row['Ent_UseAmountLimitForCreditJudge'],
                ':Ent_AutoCreditJudgeMode' => $row['Ent_AutoCreditJudgeMode'],
                ':Ent_JudgeSystemFlg' => $row['Ent_JudgeSystemFlg'],
                ':Ent_CreditJudgePendingRequest' => $row['Ent_CreditJudgePendingRequest'],
                ':Sit_T_OrderClass' => $row['Sit_T_OrderClass'],
                ':Sit_AutoCreditLimitAmount' => $row['Sit_AutoCreditLimitAmount'],
                ':Sit_CreditOrderUseAmount' => $row['Sit_CreditOrderUseAmount'],
                ':Sit_AverageUnitPriceRate' => $row['Sit_AverageUnitPriceRate'],
                ':Sit_AutoCreditDateFrom' => $row['Sit_AutoCreditDateFrom'],
                ':Sit_AutoCreditDateTo' => $row['Sit_AutoCreditDateTo'],
                ':Sit_AutoCreditCriterion' => $row['Sit_AutoCreditCriterion'],
                ':Sit_CreditJudgeMethod' => $row['Sit_CreditJudgeMethod'],
                ':Sit_MultiOrderScore' => $row['Sit_MultiOrderScore'],
                ':Sit_MultiOrderCount' => $row['Sit_MultiOrderCount'],
                ':Sit_SitClass' => $row['Sit_SitClass'],
                ':Def_CpId1_Point' => $row['Def_CpId1_Point'],
                ':Def_CpId2_Point' => $row['Def_CpId2_Point'],
                ':Def_CpId105_Point' => $row['Def_CpId105_Point'],
                ':Def_CpId106_Point' => $row['Def_CpId106_Point'],
                ':Def_CpId107_Point' => $row['Def_CpId107_Point'],
                ':Def_CpId108_Point' => $row['Def_CpId108_Point'],
                ':Def_CpId109_Point' => $row['Def_CpId109_Point'],
                ':Def_CpId201_Point' => $row['Def_CpId201_Point'],
                ':Def_CpId202_Point' => $row['Def_CpId202_Point'],
                ':Def_CpId203_Point' => $row['Def_CpId203_Point'],
                ':Def_CpId206_Point' => $row['Def_CpId206_Point'],
                ':Def_CpId301_Point' => $row['Def_CpId301_Point'],
                ':Def_CpId301_GeneralProp' => $row['Def_CpId301_GeneralProp'],
                ':Def_CpId302_Point' => $row['Def_CpId302_Point'],
                ':Def_CpId302_GeneralProp' => $row['Def_CpId302_GeneralProp'],
                ':Def_CpId303_Point' => $row['Def_CpId303_Point'],
                ':Def_CpId303_GeneralProp' => $row['Def_CpId303_GeneralProp'],
                ':Def_CpId304_Point' => $row['Def_CpId304_Point'],
                ':Def_CpId304_GeneralProp' => $row['Def_CpId304_GeneralProp'],
                ':Def_CpId401_Rate' => $row['Def_CpId401_Rate'],
                ':Def_CpId402_Rate' => $row['Def_CpId402_Rate'],
                ':Def_CpId403_Rate' => $row['Def_CpId403_Rate'],
                ':Def_CoreSystemHoldMIN' => $row['Def_CoreSystemHoldMIN'],
                ':Def_CoreSystemHoldMAX' => $row['Def_CoreSystemHoldMAX'],
                ':Def_CpId501_Description' => $row['Def_CpId501_Description'],
                ':Def_JudgeSystemHoldMIN' => $row['Def_JudgeSystemHoldMIN'],
                ':Def_JudgeSystemHoldMAX' => $row['Def_JudgeSystemHoldMAX'],
                ':Org_CpId105_Point' => $row['Org_CpId105_Point'],
                ':Org_CpId106_Point' => $row['Org_CpId106_Point'],
                ':Org_CpId107_Point' => $row['Org_CpId107_Point'],
                ':Org_CpId108_Point' => $row['Org_CpId108_Point'],
                ':Org_CpId109_Point' => $row['Org_CpId109_Point'],
                ':Org_CpId201_Point' => $row['Org_CpId201_Point'],
                ':Org_CpId202_Point' => $row['Org_CpId202_Point'],
                ':Org_CpId203_Point' => $row['Org_CpId203_Point'],
                ':Org_CpId206_Point' => $row['Org_CpId206_Point'],
                ':Org_CpId301_Point' => $row['Org_CpId301_Point'],
                ':Org_CpId301_GeneralProp' => $row['Org_CpId301_GeneralProp'],
                ':Org_CpId302_Point' => $row['Org_CpId302_Point'],
                ':Org_CpId302_GeneralProp' => $row['Org_CpId302_GeneralProp'],
                ':Org_CpId303_Point' => $row['Org_CpId303_Point'],
                ':Org_CpId303_GeneralProp' => $row['Org_CpId303_GeneralProp'],
                ':Org_CpId304_Point' => $row['Org_CpId304_Point'],
                ':Org_CpId304_GeneralProp' => $row['Org_CpId304_GeneralProp'],
                ':Org_CpId401_Rate' => $row['Org_CpId401_Rate'],
                ':Org_CpId402_Rate' => $row['Org_CpId402_Rate'],
                ':Org_CpId403_Rate' => $row['Org_CpId403_Rate'],
                ':Org_CoreSystemHoldMIN' => $row['Org_CoreSystemHoldMIN'],
                ':Org_CoreSystemHoldMAX' => $row['Org_CoreSystemHoldMAX'],
                ':Org_CpId501_Description' => $row['Org_CpId501_Description'],
                ':Org_JudgeSystemHoldMIN' => $row['Org_JudgeSystemHoldMIN'],
                ':Org_JudgeSystemHoldMAX' => $row['Org_JudgeSystemHoldMAX'],
                ':Sys_enterpriseid' => $row['Sys_enterpriseid'],
                ':Sys_AutoCreditDateFrom' => $row['Sys_AutoCreditDateFrom'],
                ':Sys_AutoCreditDateTo' => $row['Sys_AutoCreditDateTo'],
                ':Sys_AutoCreditLimitAmount' => $row['Sys_AutoCreditLimitAmount'],
                ':Sys_BtoBCreditLimitAmount' => $row['Sys_BtoBCreditLimitAmount'],
                ':Sys_CreditCriterion' => $row['Sys_CreditCriterion'],
                ':Sys_CreditOrderUseAmount' => $row['Sys_CreditOrderUseAmount'],
                ':Sys_MultiOrderDays' => $row['Sys_MultiOrderDays'],
                ':Sys_default_average_unit_price_rate' => $row['Sys_default_average_unit_price_rate'],
                ':Jud_judgeTOrderClass' => $row['Jud_judgeTOrderClass'],
                ':Jud_MultiOrderScore' => $row['Jud_MultiOrderScore'],
                ':Jud_MultiOrderYN' => $row['Jud_MultiOrderYN'],
                ':Jud_Cust_IncreArName' => $row['Jud_Cust_IncreArName'],
                ':Jud_Cust_IncreArAddr' => $row['Jud_Cust_IncreArAddr'],
                ':Jud_Cust_IncreArTel' => $row['Jud_Cust_IncreArTel'],
                ':Jud_Deli_IncreArName' => $row['Jud_Deli_IncreArName'],
                ':Jud_Deli_IncreArAddr' => $row['Jud_Deli_IncreArAddr'],
                ':Jud_Deli_IncreArTel' => $row['Jud_Deli_IncreArTel'],
                ':Jud_CreditCriterion' => $row['Jud_CreditCriterion'],
                ':Jud_AutoUseAmountOverYN' => $row['Jud_AutoUseAmountOverYN'],
                ':Jud_SaikenCancelScore' => $row['Jud_SaikenCancelScore'],
                ':Jud_SaikenCancelYN' => $row['Jud_SaikenCancelYN'],
                ':Jud_NonPaymentDaysScore' => $row['Jud_NonPaymentDaysScore'],
                ':Jud_NonPaymentDaysYN' => $row['Jud_NonPaymentDaysYN'],
                ':Jud_NonPaymentCntScore' => $row['Jud_NonPaymentCntScore'],
                ':Jud_NonPaymentCntYN' => $row['Jud_NonPaymentCntYN'],
                ':Jud_NonPaymentAmtScore' => $row['Jud_NonPaymentAmtScore'],
                ':Jud_UnpaidCntScore' => $row['Jud_UnpaidCntScore'],
                ':Jud_UnpaidCntYN' => $row['Jud_UnpaidCntYN'],
                ':Jud_UnpaidAmtScore' => $row['Jud_UnpaidAmtScore'],
                ':Jud_PastOrdersScore' => $row['Jud_PastOrdersScore'],
                ':Jud_EntNoteScore' => $row['Jud_EntNoteScore'],
                ':Jud_IdentityDocumentFlgScore' => $row['Jud_IdentityDocumentFlgScore'],
                ':Jud_MischiefCancelScore' => $row['Jud_MischiefCancelScore'],
                ':Jud_CustomerScore' => $row['Jud_CustomerScore'],
                ':Jud_CustomerSeqs' => $row['Jud_CustomerSeqs'],
                ':Jud_OrderItemsScore' => $row['Jud_OrderItemsScore'],
                ':Jud_OrderItemsSeqs' => $row['Jud_OrderItemsSeqs'],
                ':Jud_DeliveryDestinationScore' => $row['Jud_DeliveryDestinationScore'],
                ':Jud_DeliveryDestinationSeqs' => $row['Jud_DeliveryDestinationSeqs'],
                ':Jud_EnterpriseScore' => $row['Jud_EnterpriseScore'],
                ':Jud_EnterpriseSeqs' => $row['Jud_EnterpriseSeqs'],
                ':Jud_UseAmountScore' => $row['Jud_UseAmountScore'],
                ':Jud_UseAmountOverYN' => $row['Jud_UseAmountOverYN'],
                ':Jud_CoreStatus' => $row['Jud_CoreStatus'],
                ':Jud_IluStatus' => $row['Jud_IluStatus'],
                ':Jud_JintecStatus' => $row['Jud_JintecStatus'],
                ':Jud_ManualStatus' => $row['Jud_ManualStatus'],
                ':JintecManualJudgeFlg' => $row['JintecManualJudgeFlg'],
                ':Incre_SnapShot' => $row['Incre_SnapShot'],
                ':Jud_DefectOrderYN' => $row['Jud_DefectOrderYN'],
        );

        return $stm->execute($prm);
    }

    /**
     * 社内与信結果スナップショットを書式化(文字列化)して戻す
     *
     * @param int $oseq 注文SEQ
     * @return string 社内審査結果文字列
     * @see 社内与信結果スナップショットがない場合は空文字を戻す
     */
    public function getIncreSnapShotString($oseq)
    {
        // 対象注文の社内与信結果スナップショット取得
        $sql = " SELECT Incre_SnapShot FROM T_CreditLog WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC LIMIT 1 ";
        $row = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();
        if (!$row) {
            return '';
        }

        // 値なし時は空文字を戻す
        if (empty($row['Incre_SnapShot'])) {
            return '';
        }

        // 社内与信項目のキーバリューペア生成
        $aryCode3 = array();
        $mdlcode = new TableCode($this->_adapter);
        $ri = $mdlcode->getMasterByClass(3);
        foreach ($ri as $rowCode) {
            $aryCode3[$rowCode['KeyCode']] = $rowCode['KeyContent'];
        }

        // JSON展開
        $arys = \Zend\Json\Json::decode($row['Incre_SnapShot']);

        // 書式化(文字列化)
        $retval = '';
        $point = null;
        $comment = null;
        $arycount = 0;
        if(!empty($arys)) {
            $arycount = count($arys);
        }
        $i = 1;
        foreach ($arys as $ary) {
            if ($i == 1) {
                $retval .= ('「' . $aryCode3[$ary->Category] . ':' . $ary->Cstring);
            }
            if ($ary->Point != null) {
                if ($point != null) {
                    $retval .= ('」' . $point . ' ' . $comment . ' /「');
                }
                $point = $ary->Point;
                $comment = $ary->Comment;
            }
            if ($retval != '' && $i != 1) {
                if ($ary->Point == null) {
                    $retval .= ('、');
                }
                $retval .= ($aryCode3[$ary->Category] . ':' . $ary->Cstring);
            }
            if ($i == $arycount) {
                $retval .= ('」' . $point . ' ' . $comment);
            }
            $i += 1;
        }

        return $retval;
    }
}
