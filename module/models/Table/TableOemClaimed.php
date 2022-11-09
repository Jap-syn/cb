<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_OemClaimedテーブルへのアダプタ
 */
class TableOemClaimed
{
	protected $_name = 'T_OemClaimed';
	protected $_primary = array('OemClaimedSeq');
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
	 * 指定OEMIDのOEM請求データを取得する。
	 *
	 * @param string $OemId OEMID
	 * @return ResultInterface
	 */
	public function findOem($OemId)
	{
        $sql = " SELECT * FROM T_OemClaimed WHERE OemId = :OemId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $OemId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定OEMIDのOEM請求データを取得する。
	 *
	 * @param string $oemId OEMID
	 * @return ResultInterface
	 */
	public function findOem2($oemId)
	{
        return $this->findOem($oemId);
	}

    /**
     * 指定締め月のOEM請求データを取得する
     *
     * @param string $fixedDate 締め月 'yyyy-MM-dd'書式で通知
     * @param Int $oemId OEMID
     * @return ResultInterface
     */
    public function getOemClaimed($fixedDate,$oemId = -1)
    {
        $prm = array();
        $sql = <<<EOQ
SELECT
    OM.OemNameKj,
    OC.*
FROM
    T_Oem OM,
    T_OemClaimed OC
WHERE
    OM.OemId = OC.OemId AND
    OC.FixedMonth = :FixedMonth
EOQ;
        $prm += array(':FixedMonth' => $fixedDate);

        //OEMIDが指定されていればOEMIDも検索条件に追加
        if($oemId != -1){
            $sql .= "AND OM.OemId = :OemId ";
            $prm += array(':OemId' => $oemId);
        }

        $sql .= "ORDER BY OC.OemId, OC.FixedMonth, Ordinal";

        $stm = $this->_adapter->query($sql);

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
        $sql  = " INSERT INTO T_OemClaimed (OemId, FixedMonth, Ordinal, ProcessDate, SettlePlanDate, SpanFrom, SpanTo, OrderCount, UseAmount, CB_MonthlyFee, CB_SettlementCountRKF, CB_SettlementFeeRKF, CB_SettlementCountSTD, CB_SettlementFeeSTD, CB_SettlementCountEXP, CB_SettlementFeeEXP, CB_SettlementCountSPC, CB_SettlementFeeSPC, CB_SettlementCount, CB_SettlementFee, CB_ClaimCountBS, CB_ClaimFeeBS, CB_ClaimCountDK, CB_ClaimFeeDK, CB_EntMonthlyCountRKF, CB_EntMonthlyFeeRKF, CB_EntMonthlyCountSTD, CB_EntMonthlyFeeSTD, CB_EntMonthlyCountEXP, CB_EntMonthlyFeeEXP, CB_EntMonthlyCountSPC, CB_EntMonthlyFeeSPC, CB_EntMonthlyCount, CB_EntMonthlyFee, OpDkInitCount, OpDkInitFee, OpDkMonthlyCount, OpDkMonthlyFee, OpApiRegOrdMonthlyCount, OpApiRegOrdMonthlyFee, OpApiAllInitCount, OpApiAllInitFee, OpApiAllMonthlyCount, OpApiAllMonthlyFee, TfReclaimCount, TfReclaimFee, TfDamageInterestCount, TfDamageInterestAmount, TfMissedReceiptCount, TfMissedReceiptAmount, TfDoubleReceiptCount, TfDoubleReceiptAmount, TfCancelCount, TfCancelAmount, TfDevideReceiptCount, TfDevideReceiptAmount, CB_AdjustmentAmount, CB_ClaimTotal, OM_ShopTotal, OM_SettleShopTotal, OM_SettlementCountRKF, OM_SettlementFeeRKF, OM_SettlementCountSTD, OM_SettlementFeeSTD, OM_SettlementCountEXP, OM_SettlementFeeEXP, OM_SettlementCountSPC, OM_SettlementFeeSPC, OM_SettlementCount, OM_SettlementFee, OM_ClaimCountBS, OM_ClaimFeeBS, OM_ClaimCountDK, OM_ClaimFeeDK, OM_EntMonthlyCountRKF, OM_EntMonthlyFeeRKF, OM_EntMonthlyCountSTD, OM_EntMonthlyFeeSTD, OM_EntMonthlyCountEXP, OM_EntMonthlyFeeEXP, OM_EntMonthlyCountSPC, OM_EntMonthlyFeeSPC, OM_EntMonthlyCount, OM_EntMonthlyFee, OM_AdjustmentAmount, OM_TotalProfit, CR_TotalAmount, CR_OemAmount, CR_EntAmount, PayingMethod, PC_CarryOver, PC_ChargeCount, PC_ChargeAmount, PC_SettlementFee, PC_ClaimFee, PC_CancelCount, PC_CalcelAmount, PC_StampFeeCount, PC_StampFeeTotal, PC_MonthlyFee, PC_TransferCommission, PC_DecisionPayment, PC_AdjustmentAmount, FixedTransferAmount, PayBackCount, PayBackAmount, PayingControlStatus, AgencyFee, CB_SettlementCountPlan, CB_SettlementFeePlan, CB_EntMonthlyCountPlan, CB_EntMonthlyFeePlan, OM_SettlementCountPlan, OM_SettlementFeePlan, OM_EntMonthlyCountPlan, OM_EntMonthlyFeePlan, N_MonthlyFeeWithoutTax, N_MonthlyFeeTax, ExecDate, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OemId ";
        $sql .= " , :FixedMonth ";
        $sql .= " , :Ordinal ";
        $sql .= " , :ProcessDate ";
        $sql .= " , :SettlePlanDate ";
        $sql .= " , :SpanFrom ";
        $sql .= " , :SpanTo ";
        $sql .= " , :OrderCount ";
        $sql .= " , :UseAmount ";
        $sql .= " , :CB_MonthlyFee ";
        $sql .= " , :CB_SettlementCountRKF ";
        $sql .= " , :CB_SettlementFeeRKF ";
        $sql .= " , :CB_SettlementCountSTD ";
        $sql .= " , :CB_SettlementFeeSTD ";
        $sql .= " , :CB_SettlementCountEXP ";
        $sql .= " , :CB_SettlementFeeEXP ";
        $sql .= " , :CB_SettlementCountSPC ";
        $sql .= " , :CB_SettlementFeeSPC ";
        $sql .= " , :CB_SettlementCount ";
        $sql .= " , :CB_SettlementFee ";
        $sql .= " , :CB_ClaimCountBS ";
        $sql .= " , :CB_ClaimFeeBS ";
        $sql .= " , :CB_ClaimCountDK ";
        $sql .= " , :CB_ClaimFeeDK ";
        $sql .= " , :CB_EntMonthlyCountRKF ";
        $sql .= " , :CB_EntMonthlyFeeRKF ";
        $sql .= " , :CB_EntMonthlyCountSTD ";
        $sql .= " , :CB_EntMonthlyFeeSTD ";
        $sql .= " , :CB_EntMonthlyCountEXP ";
        $sql .= " , :CB_EntMonthlyFeeEXP ";
        $sql .= " , :CB_EntMonthlyCountSPC ";
        $sql .= " , :CB_EntMonthlyFeeSPC ";
        $sql .= " , :CB_EntMonthlyCount ";
        $sql .= " , :CB_EntMonthlyFee ";
        $sql .= " , :OpDkInitCount ";
        $sql .= " , :OpDkInitFee ";
        $sql .= " , :OpDkMonthlyCount ";
        $sql .= " , :OpDkMonthlyFee ";
        $sql .= " , :OpApiRegOrdMonthlyCount ";
        $sql .= " , :OpApiRegOrdMonthlyFee ";
        $sql .= " , :OpApiAllInitCount ";
        $sql .= " , :OpApiAllInitFee ";
        $sql .= " , :OpApiAllMonthlyCount ";
        $sql .= " , :OpApiAllMonthlyFee ";
        $sql .= " , :TfReclaimCount ";
        $sql .= " , :TfReclaimFee ";
        $sql .= " , :TfDamageInterestCount ";
        $sql .= " , :TfDamageInterestAmount ";
        $sql .= " , :TfMissedReceiptCount ";
        $sql .= " , :TfMissedReceiptAmount ";
        $sql .= " , :TfDoubleReceiptCount ";
        $sql .= " , :TfDoubleReceiptAmount ";
        $sql .= " , :TfCancelCount ";
        $sql .= " , :TfCancelAmount ";
        $sql .= " , :TfDevideReceiptCount ";
        $sql .= " , :TfDevideReceiptAmount ";
        $sql .= " , :CB_AdjustmentAmount ";
        $sql .= " , :CB_ClaimTotal ";
        $sql .= " , :OM_ShopTotal ";
        $sql .= " , :OM_SettleShopTotal ";
        $sql .= " , :OM_SettlementCountRKF ";
        $sql .= " , :OM_SettlementFeeRKF ";
        $sql .= " , :OM_SettlementCountSTD ";
        $sql .= " , :OM_SettlementFeeSTD ";
        $sql .= " , :OM_SettlementCountEXP ";
        $sql .= " , :OM_SettlementFeeEXP ";
        $sql .= " , :OM_SettlementCountSPC ";
        $sql .= " , :OM_SettlementFeeSPC ";
        $sql .= " , :OM_SettlementCount ";
        $sql .= " , :OM_SettlementFee ";
        $sql .= " , :OM_ClaimCountBS ";
        $sql .= " , :OM_ClaimFeeBS ";
        $sql .= " , :OM_ClaimCountDK ";
        $sql .= " , :OM_ClaimFeeDK ";
        $sql .= " , :OM_EntMonthlyCountRKF ";
        $sql .= " , :OM_EntMonthlyFeeRKF ";
        $sql .= " , :OM_EntMonthlyCountSTD ";
        $sql .= " , :OM_EntMonthlyFeeSTD ";
        $sql .= " , :OM_EntMonthlyCountEXP ";
        $sql .= " , :OM_EntMonthlyFeeEXP ";
        $sql .= " , :OM_EntMonthlyCountSPC ";
        $sql .= " , :OM_EntMonthlyFeeSPC ";
        $sql .= " , :OM_EntMonthlyCount ";
        $sql .= " , :OM_EntMonthlyFee ";
        $sql .= " , :OM_AdjustmentAmount ";
        $sql .= " , :OM_TotalProfit ";
        $sql .= " , :CR_TotalAmount ";
        $sql .= " , :CR_OemAmount ";
        $sql .= " , :CR_EntAmount ";
        $sql .= " , :PayingMethod ";
        $sql .= " , :PC_CarryOver ";
        $sql .= " , :PC_ChargeCount ";
        $sql .= " , :PC_ChargeAmount ";
        $sql .= " , :PC_SettlementFee ";
        $sql .= " , :PC_ClaimFee ";
        $sql .= " , :PC_CancelCount ";
        $sql .= " , :PC_CalcelAmount ";
        $sql .= " , :PC_StampFeeCount ";
        $sql .= " , :PC_StampFeeTotal ";
        $sql .= " , :PC_MonthlyFee ";
        $sql .= " , :PC_TransferCommission ";
        $sql .= " , :PC_DecisionPayment ";
        $sql .= " , :PC_AdjustmentAmount ";
        $sql .= " , :FixedTransferAmount ";
        $sql .= " , :PayBackCount ";
        $sql .= " , :PayBackAmount ";
        $sql .= " , :PayingControlStatus ";
        $sql .= " , :AgencyFee ";
        $sql .= " , :CB_SettlementCountPlan ";
        $sql .= " , :CB_SettlementFeePlan ";
        $sql .= " , :CB_EntMonthlyCountPlan ";
        $sql .= " , :CB_EntMonthlyFeePlan ";
        $sql .= " , :OM_SettlementCountPlan ";
        $sql .= " , :OM_SettlementFeePlan ";
        $sql .= " , :OM_EntMonthlyCountPlan ";
        $sql .= " , :OM_EntMonthlyFeePlan ";
        $sql .= " , :N_MonthlyFeeWithoutTax ";
        $sql .= " , :N_MonthlyFeeTax ";
        $sql .= " , :ExecDate ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $data['OemId'],
                ':FixedMonth' => $data['FixedMonth'],
                ':Ordinal' => $data['Ordinal'],
                ':ProcessDate' => $data['ProcessDate'],
                ':SettlePlanDate' => $data['SettlePlanDate'],
                ':SpanFrom' => $data['SpanFrom'],
                ':SpanTo' => $data['SpanTo'],
                ':OrderCount' => $data['OrderCount'],
                ':UseAmount' => $data['UseAmount'],
                ':CB_MonthlyFee' => $data['CB_MonthlyFee'],
                ':CB_SettlementCountRKF' => $data['CB_SettlementCountRKF'],
                ':CB_SettlementFeeRKF' => $data['CB_SettlementFeeRKF'],
                ':CB_SettlementCountSTD' => $data['CB_SettlementCountSTD'],
                ':CB_SettlementFeeSTD' => $data['CB_SettlementFeeSTD'],
                ':CB_SettlementCountEXP' => $data['CB_SettlementCountEXP'],
                ':CB_SettlementFeeEXP' => $data['CB_SettlementFeeEXP'],
                ':CB_SettlementCountSPC' => $data['CB_SettlementCountSPC'],
                ':CB_SettlementFeeSPC' => $data['CB_SettlementFeeSPC'],
                ':CB_SettlementCount' => $data['CB_SettlementCount'],
                ':CB_SettlementFee' => $data['CB_SettlementFee'],
                ':CB_ClaimCountBS' => $data['CB_ClaimCountBS'],
                ':CB_ClaimFeeBS' => $data['CB_ClaimFeeBS'],
                ':CB_ClaimCountDK' => $data['CB_ClaimCountDK'],
                ':CB_ClaimFeeDK' => $data['CB_ClaimFeeDK'],
                ':CB_EntMonthlyCountRKF' => $data['CB_EntMonthlyCountRKF'],
                ':CB_EntMonthlyFeeRKF' => $data['CB_EntMonthlyFeeRKF'],
                ':CB_EntMonthlyCountSTD' => $data['CB_EntMonthlyCountSTD'],
                ':CB_EntMonthlyFeeSTD' => $data['CB_EntMonthlyFeeSTD'],
                ':CB_EntMonthlyCountEXP' => $data['CB_EntMonthlyCountEXP'],
                ':CB_EntMonthlyFeeEXP' => $data['CB_EntMonthlyFeeEXP'],
                ':CB_EntMonthlyCountSPC' => $data['CB_EntMonthlyCountSPC'],
                ':CB_EntMonthlyFeeSPC' => $data['CB_EntMonthlyFeeSPC'],
                ':CB_EntMonthlyCount' => $data['CB_EntMonthlyCount'],
                ':CB_EntMonthlyFee' => $data['CB_EntMonthlyFee'],
                ':OpDkInitCount' => $data['OpDkInitCount'],
                ':OpDkInitFee' => $data['OpDkInitFee'],
                ':OpDkMonthlyCount' => $data['OpDkMonthlyCount'],
                ':OpDkMonthlyFee' => $data['OpDkMonthlyFee'],
                ':OpApiRegOrdMonthlyCount' => $data['OpApiRegOrdMonthlyCount'],
                ':OpApiRegOrdMonthlyFee' => $data['OpApiRegOrdMonthlyFee'],
                ':OpApiAllInitCount' => $data['OpApiAllInitCount'],
                ':OpApiAllInitFee' => $data['OpApiAllInitFee'],
                ':OpApiAllMonthlyCount' => $data['OpApiAllMonthlyCount'],
                ':OpApiAllMonthlyFee' => $data['OpApiAllMonthlyFee'],
                ':TfReclaimCount' => $data['TfReclaimCount'],
                ':TfReclaimFee' => $data['TfReclaimFee'],
                ':TfDamageInterestCount' => $data['TfDamageInterestCount'],
                ':TfDamageInterestAmount' => $data['TfDamageInterestAmount'],
                ':TfMissedReceiptCount' => $data['TfMissedReceiptCount'],
                ':TfMissedReceiptAmount' => $data['TfMissedReceiptAmount'],
                ':TfDoubleReceiptCount' => $data['TfDoubleReceiptCount'],
                ':TfDoubleReceiptAmount' => $data['TfDoubleReceiptAmount'],
                ':TfCancelCount' => $data['TfCancelCount'],
                ':TfCancelAmount' => $data['TfCancelAmount'],
                ':TfDevideReceiptCount' => $data['TfDevideReceiptCount'],
                ':TfDevideReceiptAmount' => $data['TfDevideReceiptAmount'],
                ':CB_AdjustmentAmount' => $data['CB_AdjustmentAmount'],
                ':CB_ClaimTotal' => $data['CB_ClaimTotal'],
                ':OM_ShopTotal' => $data['OM_ShopTotal'],
                ':OM_SettleShopTotal' => $data['OM_SettleShopTotal'],
                ':OM_SettlementCountRKF' => $data['OM_SettlementCountRKF'],
                ':OM_SettlementFeeRKF' => $data['OM_SettlementFeeRKF'],
                ':OM_SettlementCountSTD' => $data['OM_SettlementCountSTD'],
                ':OM_SettlementFeeSTD' => $data['OM_SettlementFeeSTD'],
                ':OM_SettlementCountEXP' => $data['OM_SettlementCountEXP'],
                ':OM_SettlementFeeEXP' => $data['OM_SettlementFeeEXP'],
                ':OM_SettlementCountSPC' => $data['OM_SettlementCountSPC'],
                ':OM_SettlementFeeSPC' => $data['OM_SettlementFeeSPC'],
                ':OM_SettlementCount' => $data['OM_SettlementCount'],
                ':OM_SettlementFee' => $data['OM_SettlementFee'],
                ':OM_ClaimCountBS' => $data['OM_ClaimCountBS'],
                ':OM_ClaimFeeBS' => $data['OM_ClaimFeeBS'],
                ':OM_ClaimCountDK' => $data['OM_ClaimCountDK'],
                ':OM_ClaimFeeDK' => $data['OM_ClaimFeeDK'],
                ':OM_EntMonthlyCountRKF' => $data['OM_EntMonthlyCountRKF'],
                ':OM_EntMonthlyFeeRKF' => $data['OM_EntMonthlyFeeRKF'],
                ':OM_EntMonthlyCountSTD' => $data['OM_EntMonthlyCountSTD'],
                ':OM_EntMonthlyFeeSTD' => $data['OM_EntMonthlyFeeSTD'],
                ':OM_EntMonthlyCountEXP' => $data['OM_EntMonthlyCountEXP'],
                ':OM_EntMonthlyFeeEXP' => $data['OM_EntMonthlyFeeEXP'],
                ':OM_EntMonthlyCountSPC' => $data['OM_EntMonthlyCountSPC'],
                ':OM_EntMonthlyFeeSPC' => $data['OM_EntMonthlyFeeSPC'],
                ':OM_EntMonthlyCount' => $data['OM_EntMonthlyCount'],
                ':OM_EntMonthlyFee' => $data['OM_EntMonthlyFee'],
                ':OM_AdjustmentAmount' => $data['OM_AdjustmentAmount'],
                ':OM_TotalProfit' => $data['OM_TotalProfit'],
                ':CR_TotalAmount' => $data['CR_TotalAmount'],
                ':CR_OemAmount' => $data['CR_OemAmount'],
                ':CR_EntAmount' => $data['CR_EntAmount'],
                ':PayingMethod' => $data['PayingMethod'],
                ':PC_CarryOver' => $data['PC_CarryOver'],
                ':PC_ChargeCount' => $data['PC_ChargeCount'],
                ':PC_ChargeAmount' => $data['PC_ChargeAmount'],
                ':PC_SettlementFee' => $data['PC_SettlementFee'],
                ':PC_ClaimFee' => $data['PC_ClaimFee'],
                ':PC_CancelCount' => $data['PC_CancelCount'],
                ':PC_CalcelAmount' => $data['PC_CalcelAmount'],
                ':PC_StampFeeCount' => $data['PC_StampFeeCount'],
                ':PC_StampFeeTotal' => $data['PC_StampFeeTotal'],
                ':PC_MonthlyFee' => $data['PC_MonthlyFee'],
                ':PC_TransferCommission' => $data['PC_TransferCommission'],
                ':PC_DecisionPayment' => $data['PC_DecisionPayment'],
                ':PC_AdjustmentAmount' => $data['PC_AdjustmentAmount'],
                ':FixedTransferAmount' => $data['FixedTransferAmount'],
                ':PayBackCount' => isset($data['PayBackCount']) ? $data['PayBackCount'] : 0,
                ':PayBackAmount' => isset($data['PayBackAmount']) ? $data['PayBackAmount'] : 0,
                ':PayingControlStatus' => isset($data['PayingControlStatus']) ? $data['PayingControlStatus'] : 0,
                ':AgencyFee' => isset($data['AgencyFee']) ? $data['AgencyFee'] : 0,
                ':CB_SettlementCountPlan' => $data['CB_SettlementCountPlan'],
                ':CB_SettlementFeePlan' => $data['CB_SettlementFeePlan'],
                ':CB_EntMonthlyCountPlan' => $data['CB_EntMonthlyCountPlan'],
                ':CB_EntMonthlyFeePlan' => $data['CB_EntMonthlyFeePlan'],
                ':OM_SettlementCountPlan' => $data['OM_SettlementCountPlan'],
                ':OM_SettlementFeePlan' => $data['OM_SettlementFeePlan'],
                ':OM_EntMonthlyCountPlan' => $data['OM_EntMonthlyCountPlan'],
                ':OM_EntMonthlyFeePlan' => $data['OM_EntMonthlyFeePlan'],
                ':N_MonthlyFeeWithoutTax' => isset($data['N_MonthlyFeeWithoutTax']) ? $data['N_MonthlyFeeWithoutTax'] : 0,
                ':N_MonthlyFeeTax' => isset($data['N_MonthlyFeeTax']) ? $data['N_MonthlyFeeTax'] : 0,
                ':ExecDate' => $data['ExecDate'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

	protected function isPrimaryKey($colName) {
		$primaries = $this->_primary;
		if(is_array($primaries)) {
			return in_array($colName, $primaries);
		} else {
			return $colName == $primaries;
		}
	}

	/**
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param int $eid 更新するOemId
	 */
	public function saveUpdate($data, $eid)
	{
        $sql = " SELECT * FROM T_OemClaimed WHERE OemClaimedSeq = :OemClaimedSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemClaimedSeq' => $eid,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_OemClaimed ";
        $sql .= " SET ";
        $sql .= "     OemId = :OemId ";
        $sql .= " ,   FixedMonth = :FixedMonth ";
        $sql .= " ,   Ordinal = :Ordinal ";
        $sql .= " ,   ProcessDate = :ProcessDate ";
        $sql .= " ,   SettlePlanDate = :SettlePlanDate ";
        $sql .= " ,   SpanFrom = :SpanFrom ";
        $sql .= " ,   SpanTo = :SpanTo ";
        $sql .= " ,   OrderCount = :OrderCount ";
        $sql .= " ,   UseAmount = :UseAmount ";
        $sql .= " ,   CB_MonthlyFee = :CB_MonthlyFee ";
        $sql .= " ,   CB_SettlementCountRKF = :CB_SettlementCountRKF ";
        $sql .= " ,   CB_SettlementFeeRKF = :CB_SettlementFeeRKF ";
        $sql .= " ,   CB_SettlementCountSTD = :CB_SettlementCountSTD ";
        $sql .= " ,   CB_SettlementFeeSTD = :CB_SettlementFeeSTD ";
        $sql .= " ,   CB_SettlementCountEXP = :CB_SettlementCountEXP ";
        $sql .= " ,   CB_SettlementFeeEXP = :CB_SettlementFeeEXP ";
        $sql .= " ,   CB_SettlementCountSPC = :CB_SettlementCountSPC ";
        $sql .= " ,   CB_SettlementFeeSPC = :CB_SettlementFeeSPC ";
        $sql .= " ,   CB_SettlementCount = :CB_SettlementCount ";
        $sql .= " ,   CB_SettlementFee = :CB_SettlementFee ";
        $sql .= " ,   CB_ClaimCountBS = :CB_ClaimCountBS ";
        $sql .= " ,   CB_ClaimFeeBS = :CB_ClaimFeeBS ";
        $sql .= " ,   CB_ClaimCountDK = :CB_ClaimCountDK ";
        $sql .= " ,   CB_ClaimFeeDK = :CB_ClaimFeeDK ";
        $sql .= " ,   CB_EntMonthlyCountRKF = :CB_EntMonthlyCountRKF ";
        $sql .= " ,   CB_EntMonthlyFeeRKF = :CB_EntMonthlyFeeRKF ";
        $sql .= " ,   CB_EntMonthlyCountSTD = :CB_EntMonthlyCountSTD ";
        $sql .= " ,   CB_EntMonthlyFeeSTD = :CB_EntMonthlyFeeSTD ";
        $sql .= " ,   CB_EntMonthlyCountEXP = :CB_EntMonthlyCountEXP ";
        $sql .= " ,   CB_EntMonthlyFeeEXP = :CB_EntMonthlyFeeEXP ";
        $sql .= " ,   CB_EntMonthlyCountSPC = :CB_EntMonthlyCountSPC ";
        $sql .= " ,   CB_EntMonthlyFeeSPC = :CB_EntMonthlyFeeSPC ";
        $sql .= " ,   CB_EntMonthlyCount = :CB_EntMonthlyCount ";
        $sql .= " ,   CB_EntMonthlyFee = :CB_EntMonthlyFee ";
        $sql .= " ,   OpDkInitCount = :OpDkInitCount ";
        $sql .= " ,   OpDkInitFee = :OpDkInitFee ";
        $sql .= " ,   OpDkMonthlyCount = :OpDkMonthlyCount ";
        $sql .= " ,   OpDkMonthlyFee = :OpDkMonthlyFee ";
        $sql .= " ,   OpApiRegOrdMonthlyCount = :OpApiRegOrdMonthlyCount ";
        $sql .= " ,   OpApiRegOrdMonthlyFee = :OpApiRegOrdMonthlyFee ";
        $sql .= " ,   OpApiAllInitCount = :OpApiAllInitCount ";
        $sql .= " ,   OpApiAllInitFee = :OpApiAllInitFee ";
        $sql .= " ,   OpApiAllMonthlyCount = :OpApiAllMonthlyCount ";
        $sql .= " ,   OpApiAllMonthlyFee = :OpApiAllMonthlyFee ";
        $sql .= " ,   TfReclaimCount = :TfReclaimCount ";
        $sql .= " ,   TfReclaimFee = :TfReclaimFee ";
        $sql .= " ,   TfDamageInterestCount = :TfDamageInterestCount ";
        $sql .= " ,   TfDamageInterestAmount = :TfDamageInterestAmount ";
        $sql .= " ,   TfMissedReceiptCount = :TfMissedReceiptCount ";
        $sql .= " ,   TfMissedReceiptAmount = :TfMissedReceiptAmount ";
        $sql .= " ,   TfDoubleReceiptCount = :TfDoubleReceiptCount ";
        $sql .= " ,   TfDoubleReceiptAmount = :TfDoubleReceiptAmount ";
        $sql .= " ,   TfCancelCount = :TfCancelCount ";
        $sql .= " ,   TfCancelAmount = :TfCancelAmount ";
        $sql .= " ,   TfDevideReceiptCount = :TfDevideReceiptCount ";
        $sql .= " ,   TfDevideReceiptAmount = :TfDevideReceiptAmount ";
        $sql .= " ,   CB_AdjustmentAmount = :CB_AdjustmentAmount ";
        $sql .= " ,   CB_ClaimTotal = :CB_ClaimTotal ";
        $sql .= " ,   OM_ShopTotal = :OM_ShopTotal ";
        $sql .= " ,   OM_SettleShopTotal = :OM_SettleShopTotal ";
        $sql .= " ,   OM_SettlementCountRKF = :OM_SettlementCountRKF ";
        $sql .= " ,   OM_SettlementFeeRKF = :OM_SettlementFeeRKF ";
        $sql .= " ,   OM_SettlementCountSTD = :OM_SettlementCountSTD ";
        $sql .= " ,   OM_SettlementFeeSTD = :OM_SettlementFeeSTD ";
        $sql .= " ,   OM_SettlementCountEXP = :OM_SettlementCountEXP ";
        $sql .= " ,   OM_SettlementFeeEXP = :OM_SettlementFeeEXP ";
        $sql .= " ,   OM_SettlementCountSPC = :OM_SettlementCountSPC ";
        $sql .= " ,   OM_SettlementFeeSPC = :OM_SettlementFeeSPC ";
        $sql .= " ,   OM_SettlementCount = :OM_SettlementCount ";
        $sql .= " ,   OM_SettlementFee = :OM_SettlementFee ";
        $sql .= " ,   OM_ClaimCountBS = :OM_ClaimCountBS ";
        $sql .= " ,   OM_ClaimFeeBS = :OM_ClaimFeeBS ";
        $sql .= " ,   OM_ClaimCountDK = :OM_ClaimCountDK ";
        $sql .= " ,   OM_ClaimFeeDK = :OM_ClaimFeeDK ";
        $sql .= " ,   OM_EntMonthlyCountRKF = :OM_EntMonthlyCountRKF ";
        $sql .= " ,   OM_EntMonthlyFeeRKF = :OM_EntMonthlyFeeRKF ";
        $sql .= " ,   OM_EntMonthlyCountSTD = :OM_EntMonthlyCountSTD ";
        $sql .= " ,   OM_EntMonthlyFeeSTD = :OM_EntMonthlyFeeSTD ";
        $sql .= " ,   OM_EntMonthlyCountEXP = :OM_EntMonthlyCountEXP ";
        $sql .= " ,   OM_EntMonthlyFeeEXP = :OM_EntMonthlyFeeEXP ";
        $sql .= " ,   OM_EntMonthlyCountSPC = :OM_EntMonthlyCountSPC ";
        $sql .= " ,   OM_EntMonthlyFeeSPC = :OM_EntMonthlyFeeSPC ";
        $sql .= " ,   OM_EntMonthlyCount = :OM_EntMonthlyCount ";
        $sql .= " ,   OM_EntMonthlyFee = :OM_EntMonthlyFee ";
        $sql .= " ,   OM_AdjustmentAmount = :OM_AdjustmentAmount ";
        $sql .= " ,   OM_TotalProfit = :OM_TotalProfit ";
        $sql .= " ,   CR_TotalAmount = :CR_TotalAmount ";
        $sql .= " ,   CR_OemAmount = :CR_OemAmount ";
        $sql .= " ,   CR_EntAmount = :CR_EntAmount ";
        $sql .= " ,   PayingMethod = :PayingMethod ";
        $sql .= " ,   PC_CarryOver = :PC_CarryOver ";
        $sql .= " ,   PC_ChargeCount = :PC_ChargeCount ";
        $sql .= " ,   PC_ChargeAmount = :PC_ChargeAmount ";
        $sql .= " ,   PC_SettlementFee = :PC_SettlementFee ";
        $sql .= " ,   PC_ClaimFee = :PC_ClaimFee ";
        $sql .= " ,   PC_CancelCount = :PC_CancelCount ";
        $sql .= " ,   PC_CalcelAmount = :PC_CalcelAmount ";
        $sql .= " ,   PC_StampFeeCount = :PC_StampFeeCount ";
        $sql .= " ,   PC_StampFeeTotal = :PC_StampFeeTotal ";
        $sql .= " ,   PC_MonthlyFee = :PC_MonthlyFee ";
        $sql .= " ,   PC_TransferCommission = :PC_TransferCommission ";
        $sql .= " ,   PC_DecisionPayment = :PC_DecisionPayment ";
        $sql .= " ,   PC_AdjustmentAmount = :PC_AdjustmentAmount ";
        $sql .= " ,   FixedTransferAmount = :FixedTransferAmount ";
        $sql .= " ,   PayBackCount = :PayBackCount ";
        $sql .= " ,   PayBackAmount = :PayBackAmount ";
        $sql .= " ,   PayingControlStatus = :PayingControlStatus ";
        $sql .= " ,   AgencyFee = :AgencyFee ";
        $sql .= " ,   CB_SettlementCountPlan = :CB_SettlementCountPlan ";
        $sql .= " ,   CB_SettlementFeePlan = :CB_SettlementFeePlan ";
        $sql .= " ,   CB_EntMonthlyCountPlan = :CB_EntMonthlyCountPlan ";
        $sql .= " ,   CB_EntMonthlyFeePlan = :CB_EntMonthlyFeePlan ";
        $sql .= " ,   OM_SettlementCountPlan = :OM_SettlementCountPlan ";
        $sql .= " ,   OM_SettlementFeePlan = :OM_SettlementFeePlan ";
        $sql .= " ,   OM_EntMonthlyCountPlan = :OM_EntMonthlyCountPlan ";
        $sql .= " ,   OM_EntMonthlyFeePlan = :OM_EntMonthlyFeePlan ";
        $sql .= " ,   N_MonthlyFeeWithoutTax = :N_MonthlyFeeWithoutTax ";
        $sql .= " ,   N_MonthlyFeeTax = :N_MonthlyFeeTax ";
        $sql .= " ,   ExecDate = :ExecDate ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE OemClaimedSeq = :OemClaimedSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemClaimedSeq' => $eid,
                ':OemId' => $row['OemId'],
                ':FixedMonth' => $row['FixedMonth'],
                ':Ordinal' => $row['Ordinal'],
                ':ProcessDate' => $row['ProcessDate'],
                ':SettlePlanDate' => $row['SettlePlanDate'],
                ':SpanFrom' => $row['SpanFrom'],
                ':SpanTo' => $row['SpanTo'],
                ':OrderCount' => $row['OrderCount'],
                ':UseAmount' => $row['UseAmount'],
                ':CB_MonthlyFee' => $row['CB_MonthlyFee'],
                ':CB_SettlementCountRKF' => $row['CB_SettlementCountRKF'],
                ':CB_SettlementFeeRKF' => $row['CB_SettlementFeeRKF'],
                ':CB_SettlementCountSTD' => $row['CB_SettlementCountSTD'],
                ':CB_SettlementFeeSTD' => $row['CB_SettlementFeeSTD'],
                ':CB_SettlementCountEXP' => $row['CB_SettlementCountEXP'],
                ':CB_SettlementFeeEXP' => $row['CB_SettlementFeeEXP'],
                ':CB_SettlementCountSPC' => $row['CB_SettlementCountSPC'],
                ':CB_SettlementFeeSPC' => $row['CB_SettlementFeeSPC'],
                ':CB_SettlementCount' => $row['CB_SettlementCount'],
                ':CB_SettlementFee' => $row['CB_SettlementFee'],
                ':CB_ClaimCountBS' => $row['CB_ClaimCountBS'],
                ':CB_ClaimFeeBS' => $row['CB_ClaimFeeBS'],
                ':CB_ClaimCountDK' => $row['CB_ClaimCountDK'],
                ':CB_ClaimFeeDK' => $row['CB_ClaimFeeDK'],
                ':CB_EntMonthlyCountRKF' => $row['CB_EntMonthlyCountRKF'],
                ':CB_EntMonthlyFeeRKF' => $row['CB_EntMonthlyFeeRKF'],
                ':CB_EntMonthlyCountSTD' => $row['CB_EntMonthlyCountSTD'],
                ':CB_EntMonthlyFeeSTD' => $row['CB_EntMonthlyFeeSTD'],
                ':CB_EntMonthlyCountEXP' => $row['CB_EntMonthlyCountEXP'],
                ':CB_EntMonthlyFeeEXP' => $row['CB_EntMonthlyFeeEXP'],
                ':CB_EntMonthlyCountSPC' => $row['CB_EntMonthlyCountSPC'],
                ':CB_EntMonthlyFeeSPC' => $row['CB_EntMonthlyFeeSPC'],
                ':CB_EntMonthlyCount' => $row['CB_EntMonthlyCount'],
                ':CB_EntMonthlyFee' => $row['CB_EntMonthlyFee'],
                ':OpDkInitCount' => $row['OpDkInitCount'],
                ':OpDkInitFee' => $row['OpDkInitFee'],
                ':OpDkMonthlyCount' => $row['OpDkMonthlyCount'],
                ':OpDkMonthlyFee' => $row['OpDkMonthlyFee'],
                ':OpApiRegOrdMonthlyCount' => $row['OpApiRegOrdMonthlyCount'],
                ':OpApiRegOrdMonthlyFee' => $row['OpApiRegOrdMonthlyFee'],
                ':OpApiAllInitCount' => $row['OpApiAllInitCount'],
                ':OpApiAllInitFee' => $row['OpApiAllInitFee'],
                ':OpApiAllMonthlyCount' => $row['OpApiAllMonthlyCount'],
                ':OpApiAllMonthlyFee' => $row['OpApiAllMonthlyFee'],
                ':TfReclaimCount' => $row['TfReclaimCount'],
                ':TfReclaimFee' => $row['TfReclaimFee'],
                ':TfDamageInterestCount' => $row['TfDamageInterestCount'],
                ':TfDamageInterestAmount' => $row['TfDamageInterestAmount'],
                ':TfMissedReceiptCount' => $row['TfMissedReceiptCount'],
                ':TfMissedReceiptAmount' => $row['TfMissedReceiptAmount'],
                ':TfDoubleReceiptCount' => $row['TfDoubleReceiptCount'],
                ':TfDoubleReceiptAmount' => $row['TfDoubleReceiptAmount'],
                ':TfCancelCount' => $row['TfCancelCount'],
                ':TfCancelAmount' => $row['TfCancelAmount'],
                ':TfDevideReceiptCount' => $row['TfDevideReceiptCount'],
                ':TfDevideReceiptAmount' => $row['TfDevideReceiptAmount'],
                ':CB_AdjustmentAmount' => $row['CB_AdjustmentAmount'],
                ':CB_ClaimTotal' => $row['CB_ClaimTotal'],
                ':OM_ShopTotal' => $row['OM_ShopTotal'],
                ':OM_SettleShopTotal' => $row['OM_SettleShopTotal'],
                ':OM_SettlementCountRKF' => $row['OM_SettlementCountRKF'],
                ':OM_SettlementFeeRKF' => $row['OM_SettlementFeeRKF'],
                ':OM_SettlementCountSTD' => $row['OM_SettlementCountSTD'],
                ':OM_SettlementFeeSTD' => $row['OM_SettlementFeeSTD'],
                ':OM_SettlementCountEXP' => $row['OM_SettlementCountEXP'],
                ':OM_SettlementFeeEXP' => $row['OM_SettlementFeeEXP'],
                ':OM_SettlementCountSPC' => $row['OM_SettlementCountSPC'],
                ':OM_SettlementFeeSPC' => $row['OM_SettlementFeeSPC'],
                ':OM_SettlementCount' => $row['OM_SettlementCount'],
                ':OM_SettlementFee' => $row['OM_SettlementFee'],
                ':OM_ClaimCountBS' => $row['OM_ClaimCountBS'],
                ':OM_ClaimFeeBS' => $row['OM_ClaimFeeBS'],
                ':OM_ClaimCountDK' => $row['OM_ClaimCountDK'],
                ':OM_ClaimFeeDK' => $row['OM_ClaimFeeDK'],
                ':OM_EntMonthlyCountRKF' => $row['OM_EntMonthlyCountRKF'],
                ':OM_EntMonthlyFeeRKF' => $row['OM_EntMonthlyFeeRKF'],
                ':OM_EntMonthlyCountSTD' => $row['OM_EntMonthlyCountSTD'],
                ':OM_EntMonthlyFeeSTD' => $row['OM_EntMonthlyFeeSTD'],
                ':OM_EntMonthlyCountEXP' => $row['OM_EntMonthlyCountEXP'],
                ':OM_EntMonthlyFeeEXP' => $row['OM_EntMonthlyFeeEXP'],
                ':OM_EntMonthlyCountSPC' => $row['OM_EntMonthlyCountSPC'],
                ':OM_EntMonthlyFeeSPC' => $row['OM_EntMonthlyFeeSPC'],
                ':OM_EntMonthlyCount' => $row['OM_EntMonthlyCount'],
                ':OM_EntMonthlyFee' => $row['OM_EntMonthlyFee'],
                ':OM_AdjustmentAmount' => $row['OM_AdjustmentAmount'],
                ':OM_TotalProfit' => $row['OM_TotalProfit'],
                ':CR_TotalAmount' => $row['CR_TotalAmount'],
                ':CR_OemAmount' => $row['CR_OemAmount'],
                ':CR_EntAmount' => $row['CR_EntAmount'],
                ':PayingMethod' => $row['PayingMethod'],
                ':PC_CarryOver' => $row['PC_CarryOver'],
                ':PC_ChargeCount' => $row['PC_ChargeCount'],
                ':PC_ChargeAmount' => $row['PC_ChargeAmount'],
                ':PC_SettlementFee' => $row['PC_SettlementFee'],
                ':PC_ClaimFee' => $row['PC_ClaimFee'],
                ':PC_CancelCount' => $row['PC_CancelCount'],
                ':PC_CalcelAmount' => $row['PC_CalcelAmount'],
                ':PC_StampFeeCount' => $row['PC_StampFeeCount'],
                ':PC_StampFeeTotal' => $row['PC_StampFeeTotal'],
                ':PC_MonthlyFee' => $row['PC_MonthlyFee'],
                ':PC_TransferCommission' => $row['PC_TransferCommission'],
                ':PC_DecisionPayment' => $row['PC_DecisionPayment'],
                ':PC_AdjustmentAmount' => $row['PC_AdjustmentAmount'],
                ':FixedTransferAmount' => $row['FixedTransferAmount'],
                ':PayBackCount' => $row['PayBackCount'],
                ':PayBackAmount' => $row['PayBackAmount'],
                ':PayingControlStatus' => $row['PayingControlStatus'],
                ':AgencyFee' => $row['AgencyFee'],
                ':CB_SettlementCountPlan' => $row['CB_SettlementCountPlan'],
                ':CB_SettlementFeePlan' => $row['CB_SettlementFeePlan'],
                ':CB_EntMonthlyCountPlan' => $row['CB_EntMonthlyCountPlan'],
                ':CB_EntMonthlyFeePlan' => $row['CB_EntMonthlyFeePlan'],
                ':OM_SettlementCountPlan' => $row['OM_SettlementCountPlan'],
                ':OM_SettlementFeePlan' => $row['OM_SettlementFeePlan'],
                ':OM_EntMonthlyCountPlan' => $row['OM_EntMonthlyCountPlan'],
                ':OM_EntMonthlyFeePlan' => $row['OM_EntMonthlyFeePlan'],
                ':N_MonthlyFeeWithoutTax' => $row['N_MonthlyFeeWithoutTax'],
                ':N_MonthlyFeeTax' => $row['N_MonthlyFeeTax'],
                ':ExecDate' => $row['ExecDate'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
	}

    /**
     * 指定のOEM請求データを年度＞対象期間FROM＞対象期間TOで降順ソートしたもの取得
     *
     * @param string $OemId OEMID
     * @param string $From 対象期間From 'yyyy-MM-dd'書式で通知
     * @param string $To 対象期間To 'yyyy-MM-dd'書式で通知
     * @return ResultInterface
     */
    public function findOemClaimed($OemId = null,$From = null,$To = null)
    {
        $prm = array();
        $sql = " SELECT * FROM T_OemClaimed WHERE 1 = 1 AND ValidFlg = 1 ";

        // OEMIDが指定されている
        if (!is_null($OemId)) {
            $sql .= " AND OemId = :OemId ";
            $prm += array(':OemId' => $OemId);
        }

        // 対象期間FROMが指定されている
        if (!is_null($From)) {
            $sql .= " AND SpanFrom = :SpanFrom ";
            $prm += array(':SpanFrom' => $From);
        }

        // 対象期間TOが指定されている
        if (!is_null($To)) {
            $sql .= " AND SpanTo = :SpanTo ";
            $prm += array(':SpanTo' => $To);
        }

        $sql .= " ORDER BY FixedMonth DESC, SpanFrom DESC, SpanTo DESC ";

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
    }

    /**
     * 精算調整額更新
     *
     * @param $oemClaimedId int T_OemClaimedのOemClaimedSeq
     * @param $amount int 精算調整額
	 * @param $opId 担当者
     */
    public function updateAdjustmentAmount($oemClaimedId,$amount, $opId)
    {
        $sql = " SELECT * FROM T_OemClaimed WHERE OemClaimedSeq = :OemClaimedSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemClaimedSeq' => $oemClaimedId,
        );

        $ri = $stm->execute($prm);

        // 既存データが取得できなかったら例外
        if (!($ri->count() > 0)){
            throw new \Exception('精算調整額更新失敗');
        }

        $row = $ri->current();
        // 本来の確定振込額
        $transferAmount= (($row['PayingMethod'] == 1) ? $row['PC_DecisionPayment'] : 0) + $row['OM_TotalProfit'] + (($row['PayingMethod'] == 1) ? $row['PC_TransferCommission'] : 0);

        // 調整額計算
        $transferAmount += $amount;

        $uData['OM_AdjustmentAmount'] = $amount;
        $uData['FixedTransferAmount'] = $transferAmount;
        $uData['UpdateId'] = $opId;

        return $this->saveUpdate($uData, $oemClaimedId);
    }

    /**
     * 指定されたレコードを削除する。
     * @param int $seq
     */
    public function deleteByPayingControlStatus()
    {
        $sql = " DELETE FROM T_OemClaimed WHERE PayingControlStatus = 0 ";
        $ri = $this->_adapter->query($sql)->execute();
    }

}
