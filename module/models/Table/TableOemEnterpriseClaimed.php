<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_OemEnterpriseClaimedテーブルへのアダプタ
 */
class TableOemEnterpriseClaimed
{
	protected $_name = 'T_OemEnterpriseClaimed';
	protected $_primary = array('OemEnterpriseClaimedSeq');
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
        $sql = " SELECT * FROM T_OemEnterpriseClaimed WHERE OemId = :OemId ";

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
     * 指定締め月の店舗別精算明細データを取得する
     *
     * @param string $fixedDate 締め月 'yyyy-MM-dd'書式で通知
     * @param Int $oemId OEMID
     * @param Int $enterpriseId 事業者ID
     * @return ResultInterface
     */
    public function getOemEnterpriseClaimed($fixedDate,$oemId = -1,$enterpriseId = -1)
    {
        $prm = array();

        $sql = <<<EOQ
SELECT
    EP.EnterpriseNameKj,
    EP.EnterpriseId,
    OEC.*
FROM
    T_OemEnterpriseClaimed OEC,
    T_Enterprise EP
WHERE
    OEC.EnterpriseId = EP.EnterpriseId AND
    OEC.FixedMonth = :FixedMonth
EOQ;
        $prm += array(':FixedMonth' => $fixedDate);

        //OEMIDが指定されていればOEMIDも検索条件に追加
        if($oemId != -1){
            $sql .= " AND OEC.OemId = :OemId ";
            $prm += array(':OemId' => $oemId);
        }

        //EnterPriseIDが指定されていればOEMIDも検索条件に追加
        if($enterpriseId != -1){
            $sql .= " AND EP.EnterpriseId = :EnterpriseId ";
            $prm += array(':EnterpriseId' => $enterpriseId);
        }

        $sql .= "ORDER BY OEC.OemId, OEC.FixedMonth";

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
        $sql  = " INSERT INTO T_OemEnterpriseClaimed (OemId, EnterpriseId, FixedMonth, ProcessDate, SpanFrom, SpanTo, OrderCount, UseAmount, CB_MonthlyFee, AppPlan, SettlementFeeRate, OemSettlementFeeRate, CB_SettlementCount, CB_SettlementFee, CB_ClaimCountBS, CB_ClaimFeeBS, CB_ClaimCountDK, CB_ClaimFeeDK, CB_EntMonthlyFee, CB_AdjustmentAmount, CB_ClaimTotal, OM_SettlementCount, OM_SettlementFee, OM_ClaimCountBS, OM_ClaimFeeBS, OM_ClaimCountDK, OM_ClaimFeeDK, OM_EntMonthlyFee, OM_AdjustmentAmount, OM_TotalProfit, CR_TotalAmount, CR_OemAmount, CR_EntAmount, PayingMethod, PC_CarryOver, PC_ChargeCount, PC_ChargeAmount, PC_SettlementFee, PC_ClaimFee, PC_CancelCount, PC_CalcelAmount, PC_StampFeeCount, PC_StampFeeTotal, PC_MonthlyFee, PC_TransferCommission, PC_DecisionPayment, PC_AdjustmentAmount, FixedTransferAmount, OemClaimedSeq, PayBackCount, PayBackAmount, AgencyFee, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OemId ";
        $sql .= " , :EnterpriseId ";
        $sql .= " , :FixedMonth ";
        $sql .= " , :ProcessDate ";
        $sql .= " , :SpanFrom ";
        $sql .= " , :SpanTo ";
        $sql .= " , :OrderCount ";
        $sql .= " , :UseAmount ";
        $sql .= " , :CB_MonthlyFee ";
        $sql .= " , :AppPlan ";
        $sql .= " , :SettlementFeeRate ";
        $sql .= " , :OemSettlementFeeRate ";
        $sql .= " , :CB_SettlementCount ";
        $sql .= " , :CB_SettlementFee ";
        $sql .= " , :CB_ClaimCountBS ";
        $sql .= " , :CB_ClaimFeeBS ";
        $sql .= " , :CB_ClaimCountDK ";
        $sql .= " , :CB_ClaimFeeDK ";
        $sql .= " , :CB_EntMonthlyFee ";
        $sql .= " , :CB_AdjustmentAmount ";
        $sql .= " , :CB_ClaimTotal ";
        $sql .= " , :OM_SettlementCount ";
        $sql .= " , :OM_SettlementFee ";
        $sql .= " , :OM_ClaimCountBS ";
        $sql .= " , :OM_ClaimFeeBS ";
        $sql .= " , :OM_ClaimCountDK ";
        $sql .= " , :OM_ClaimFeeDK ";
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
        $sql .= " , :OemClaimedSeq ";
        $sql .= " , :PayBackCount ";
        $sql .= " , :PayBackAmount ";
        $sql .= " , :AgencyFee ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $data['OemId'],
                ':EnterpriseId' => $data['EnterpriseId'],
                ':FixedMonth' => $data['FixedMonth'],
                ':ProcessDate' => $data['ProcessDate'],
                ':SpanFrom' => $data['SpanFrom'],
                ':SpanTo' => $data['SpanTo'],
                ':OrderCount' => $data['OrderCount'],
                ':UseAmount' => $data['UseAmount'],
                ':CB_MonthlyFee' => $data['CB_MonthlyFee'],
                ':AppPlan' => $data['AppPlan'],
                ':SettlementFeeRate' => $data['SettlementFeeRate'],
                ':OemSettlementFeeRate' => $data['OemSettlementFeeRate'],
                ':CB_SettlementCount' => $data['CB_SettlementCount'],
                ':CB_SettlementFee' => $data['CB_SettlementFee'],
                ':CB_ClaimCountBS' => $data['CB_ClaimCountBS'],
                ':CB_ClaimFeeBS' => $data['CB_ClaimFeeBS'],
                ':CB_ClaimCountDK' => $data['CB_ClaimCountDK'],
                ':CB_ClaimFeeDK' => $data['CB_ClaimFeeDK'],
                ':CB_EntMonthlyFee' => $data['CB_EntMonthlyFee'],
                ':CB_AdjustmentAmount' => $data['CB_AdjustmentAmount'],
                ':CB_ClaimTotal' => $data['CB_ClaimTotal'],
                ':OM_SettlementCount' => $data['OM_SettlementCount'],
                ':OM_SettlementFee' => $data['OM_SettlementFee'],
                ':OM_ClaimCountBS' => $data['OM_ClaimCountBS'],
                ':OM_ClaimFeeBS' => $data['OM_ClaimFeeBS'],
                ':OM_ClaimCountDK' => $data['OM_ClaimCountDK'],
                ':OM_ClaimFeeDK' => $data['OM_ClaimFeeDK'],
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
                ':OemClaimedSeq' => $data['OemClaimedSeq'],
                ':PayBackCount' => isset($data['PayBackCount']) ? $data['PayBackCount'] : 0,
                ':PayBackAmount' => isset($data['PayBackAmount']) ? $data['PayBackAmount'] : 0,
                ':AgencyFee' => $data['AgencyFee'],
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
	 * @param int $eid 更新するOemEnterpriseClaimedSeq
	 */
	public function saveUpdate($data, $eid)
	{
        $sql = " SELECT * FROM T_OemEnterpriseClaimed WHERE OemEnterpriseClaimedSeq = :OemEnterpriseClaimedSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemEnterpriseClaimedSeq' => $eid,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_OemEnterpriseClaimed ";
        $sql .= " SET ";
        $sql .= "     OemId = :OemId ";
        $sql .= " ,   EnterpriseId = :EnterpriseId ";
        $sql .= " ,   FixedMonth = :FixedMonth ";
        $sql .= " ,   ProcessDate = :ProcessDate ";
        $sql .= " ,   SpanFrom = :SpanFrom ";
        $sql .= " ,   SpanTo = :SpanTo ";
        $sql .= " ,   OrderCount = :OrderCount ";
        $sql .= " ,   UseAmount = :UseAmount ";
        $sql .= " ,   CB_MonthlyFee = :CB_MonthlyFee ";
        $sql .= " ,   AppPlan = :AppPlan ";
        $sql .= " ,   SettlementFeeRate = :SettlementFeeRate ";
        $sql .= " ,   OemSettlementFeeRate = :OemSettlementFeeRate ";
        $sql .= " ,   CB_SettlementCount = :CB_SettlementCount ";
        $sql .= " ,   CB_SettlementFee = :CB_SettlementFee ";
        $sql .= " ,   CB_ClaimCountBS = :CB_ClaimCountBS ";
        $sql .= " ,   CB_ClaimFeeBS = :CB_ClaimFeeBS ";
        $sql .= " ,   CB_ClaimCountDK = :CB_ClaimCountDK ";
        $sql .= " ,   CB_ClaimFeeDK = :CB_ClaimFeeDK ";
        $sql .= " ,   CB_EntMonthlyFee = :CB_EntMonthlyFee ";
        $sql .= " ,   CB_AdjustmentAmount = :CB_AdjustmentAmount ";
        $sql .= " ,   CB_ClaimTotal = :CB_ClaimTotal ";
        $sql .= " ,   OM_SettlementCount = :OM_SettlementCount ";
        $sql .= " ,   OM_SettlementFee = :OM_SettlementFee ";
        $sql .= " ,   OM_ClaimCountBS = :OM_ClaimCountBS ";
        $sql .= " ,   OM_ClaimFeeBS = :OM_ClaimFeeBS ";
        $sql .= " ,   OM_ClaimCountDK = :OM_ClaimCountDK ";
        $sql .= " ,   OM_ClaimFeeDK = :OM_ClaimFeeDK ";
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
        $sql .= " ,   OemClaimedSeq = :OemClaimedSeq ";
        $sql .= " ,   PayBackCount = :PayBackCount ";
        $sql .= " ,   PayBackAmount = :PayBackAmount ";
        $sql .= " ,   AgencyFee = :AgencyFee ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE OemEnterpriseClaimedSeq  = :OemEnterpriseClaimedSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemEnterpriseClaimedSeq' => $eid,
                ':OemId' => $row['OemId'],
                ':EnterpriseId' => $row['EnterpriseId'],
                ':FixedMonth' => $row['FixedMonth'],
                ':ProcessDate' => $row['ProcessDate'],
                ':SpanFrom' => $row['SpanFrom'],
                ':SpanTo' => $row['SpanTo'],
                ':OrderCount' => $row['OrderCount'],
                ':UseAmount' => $row['UseAmount'],
                ':CB_MonthlyFee' => $row['CB_MonthlyFee'],
                ':AppPlan' => $row['AppPlan'],
                ':SettlementFeeRate' => $row['SettlementFeeRate'],
                ':OemSettlementFeeRate' => $row['OemSettlementFeeRate'],
                ':CB_SettlementCount' => $row['CB_SettlementCount'],
                ':CB_SettlementFee' => $row['CB_SettlementFee'],
                ':CB_ClaimCountBS' => $row['CB_ClaimCountBS'],
                ':CB_ClaimFeeBS' => $row['CB_ClaimFeeBS'],
                ':CB_ClaimCountDK' => $row['CB_ClaimCountDK'],
                ':CB_ClaimFeeDK' => $row['CB_ClaimFeeDK'],
                ':CB_EntMonthlyFee' => $row['CB_EntMonthlyFee'],
                ':CB_AdjustmentAmount' => $row['CB_AdjustmentAmount'],
                ':CB_ClaimTotal' => $row['CB_ClaimTotal'],
                ':OM_SettlementCount' => $row['OM_SettlementCount'],
                ':OM_SettlementFee' => $row['OM_SettlementFee'],
                ':OM_ClaimCountBS' => $row['OM_ClaimCountBS'],
                ':OM_ClaimFeeBS' => $row['OM_ClaimFeeBS'],
                ':OM_ClaimCountDK' => $row['OM_ClaimCountDK'],
                ':OM_ClaimFeeDK' => $row['OM_ClaimFeeDK'],
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
                ':OemClaimedSeq' => $row['OemClaimedSeq'],
                ':PayBackCount' => $row['PayBackCount'],
                ':PayBackAmount' => $row['PayBackAmount'],
                ':AgencyFee' => $row['AgencyFee'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
	}

	/**
     * 指定のOEM事業者データを対象期間FROM＞対象期間TOで降順ソートしたもの取得
     *
     * @param string $OemId OEMID
     * @param string $From 対象期間From 'yyyy-MM-dd'書式で通知
     * @param string $To 対象期間To 'yyyy-MM-dd'書式で通知
     * @param int $EnterpriseId 事業者ID
     * @return ResultInterface
     */
    public function findOemEnterpriseClaimed($OemId = null,$From = null,$To = null,$EnterpriseId = null)
    {
        $prm = array();
        $sql  = " SELECT OEC.SettlementFeeRate as OecSettlementFeeRate ";
        $sql .= " ,      OEC.OemSettlementFeeRate as OecOemSettlementFeeRate ";
        $sql .= " ,      EP.EnterpriseNameKj ";
        $sql .= " ,      EP.LoginId ";
        $sql .= " ,      OEC.* ";
        $sql .= " FROM   T_OemEnterpriseClaimed OEC ";
        $sql .= " ,      T_Enterprise EP ";
        $sql .= " WHERE  OEC.EnterpriseId = EP.EnterpriseId ";
        $sql .= " AND    1 = 1 ";

        //OEMIDが指定されている
        if(!is_null($OemId)){
            $sql .= " AND OEC.OemId = :OemId ";
            $prm += array(':OemId' => $OemId);
        }
        //対象期間FROMが指定されている
        if(!is_null($From)){
            $sql .= " AND OEC.SpanFrom = :SpanFrom ";
            $prm += array(':SpanFrom' => $From);
        }
        //対象期間TOが指定されている
        if(!is_null($To)){
            $sql .= " AND OEC.SpanTo = :SpanTo ";
            $prm += array(':SpanTo' => $To);
        }
        //事業者IDが指定されている
        if(!is_null($EnterpriseId)){
            $sql .= " AND OEC.EnterpriseId = :EnterpriseId ";
            $prm += array(':EnterpriseId' => $EnterpriseId);
        }
        $sql .= " ORDER BY SpanFrom DESC, SpanTo DESC ";

        // 事業者ログインID昇順にソート
        $sql .= " , EP.LoginId ASC ";

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
    }

    /**
     * 指定されているレコードを削除する
     * @param int $seq
     */
    public function deleteBySeq($seq)
    {
        $sql = " DELETE FROM T_OemEnterpriseClaimed WHERE OemClaimedSeq = :Seq";
        $ri = $this->_adapter->query($sql)->execute(array(':Seq' => $seq));
    }
}
