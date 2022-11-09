<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_ClaimControl(請求管理)テーブルへのアダプタ
 */
class TableClaimControl
{
    protected $_name = 'T_ClaimControl';
    protected $_primary = array('ClaimId');
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
     * 請求管理データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $claimId 請求ID
     * @return ResultInterface
     */
    public function find($claimId)
    {
        $sql = " SELECT * FROM T_ClaimControl WHERE ValidFlg = 1 AND ClaimId = :ClaimId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ClaimId' => $claimId,
        );

        return $stm->execute($prm);
    }

	/**
	 * 指定条件（AND）の請求管理データを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @param boolean $isAsc プライマリキーのオーダー
	 * @return ResultInterface
	 */
	public function findClaim($conditionArray, $isAsc = false)
	{
        $prm = array();
        $sql  = " SELECT * FROM T_ClaimControl WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }
        $sql .= " ORDER BY ClaimId " . ($isAsc ? "asc" : "desc");

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
        $sql  = " INSERT INTO T_ClaimControl (OrderSeq, EntCustSeq, ClaimDate, ClaimCpId, ClaimPattern, LimitDate, UseAmountTotal, DamageDays, DamageBaseDate, DamageInterestAmount, ClaimFee, AdditionalClaimFee, PrintedDate, ClaimAmount, ReceiptAmountTotal, RepayAmountTotal, SundryLossTotal, SundryIncomeTotal, ClaimedBalance, SundryLossTarget, SundryIncomeTarget, Clm_Count, F_ClaimDate, F_OpId, F_LimitDate, F_ClaimAmount, Re1_ClaimAmount, Re3_ClaimAmount, AutoSundryStatus, ReissueClass, ReissueRequestDate, LastProcessDate, LastReceiptSeq, MinClaimAmount, MinUseAmount, MinClaimFee, MinDamageInterestAmount, MinAdditionalClaimFee, CheckingClaimAmount, CheckingUseAmount, CheckingClaimFee, CheckingDamageInterestAmount, CheckingAdditionalClaimFee, BalanceClaimAmount, BalanceUseAmount, BalanceClaimFee, BalanceDamageInterestAmount, BalanceAdditionalClaimFee, MypageReissueClass, MypageReissueRequestDate, MypageReissueDate, MypageReissueReClaimFee, CreditSettlementDecisionDate, ReissueCount, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :EntCustSeq ";
        $sql .= " , :ClaimDate ";
        $sql .= " , :ClaimCpId ";
        $sql .= " , :ClaimPattern ";
        $sql .= " , :LimitDate ";
        $sql .= " , :UseAmountTotal ";
        $sql .= " , :DamageDays ";
        $sql .= " , :DamageBaseDate ";
        $sql .= " , :DamageInterestAmount ";
        $sql .= " , :ClaimFee ";
        $sql .= " , :AdditionalClaimFee ";
        $sql .= " , :PrintedDate ";
        $sql .= " , :ClaimAmount ";
        $sql .= " , :ReceiptAmountTotal ";
        $sql .= " , :RepayAmountTotal ";
        $sql .= " , :SundryLossTotal ";
        $sql .= " , :SundryIncomeTotal ";
        $sql .= " , :ClaimedBalance ";
        $sql .= " , :SundryLossTarget ";
        $sql .= " , :SundryIncomeTarget ";
        $sql .= " , :Clm_Count ";
        $sql .= " , :F_ClaimDate ";
        $sql .= " , :F_OpId ";
        $sql .= " , :F_LimitDate ";
        $sql .= " , :F_ClaimAmount ";
        $sql .= " , :Re1_ClaimAmount ";
        $sql .= " , :Re3_ClaimAmount ";
        $sql .= " , :AutoSundryStatus ";
        $sql .= " , :ReissueClass ";
        $sql .= " , :ReissueRequestDate ";
        $sql .= " , :LastProcessDate ";
        $sql .= " , :LastReceiptSeq ";
        $sql .= " , :MinClaimAmount ";
        $sql .= " , :MinUseAmount ";
        $sql .= " , :MinClaimFee ";
        $sql .= " , :MinDamageInterestAmount ";
        $sql .= " , :MinAdditionalClaimFee ";
        $sql .= " , :CheckingClaimAmount ";
        $sql .= " , :CheckingUseAmount ";
        $sql .= " , :CheckingClaimFee ";
        $sql .= " , :CheckingDamageInterestAmount ";
        $sql .= " , :CheckingAdditionalClaimFee ";
        $sql .= " , :BalanceClaimAmount ";
        $sql .= " , :BalanceUseAmount ";
        $sql .= " , :BalanceClaimFee ";
        $sql .= " , :BalanceDamageInterestAmount ";
        $sql .= " , :BalanceAdditionalClaimFee ";
        $sql .= " , :MypageReissueClass ";
        $sql .= " , :MypageReissueRequestDate ";
        $sql .= " , :MypageReissueDate ";
        $sql .= " , :MypageReissueReClaimFee ";
        $sql .= " , :CreditSettlementDecisionDate ";
        $sql .= " , :ReissueCount ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':EntCustSeq' => $data['EntCustSeq'],
                ':ClaimDate' => $data['ClaimDate'],
                ':ClaimCpId' => $data['ClaimCpId'],
                ':ClaimPattern' => isset($data['ClaimPattern']) ? $data['ClaimPattern'] : 1,
                ':LimitDate' => $data['LimitDate'],
                ':UseAmountTotal' => isset($data['UseAmountTotal']) ? $data['UseAmountTotal'] : 0,
                ':DamageDays' => $data['DamageDays'],
                ':DamageBaseDate' => $data['DamageBaseDate'],
                ':DamageInterestAmount' => isset($data['DamageInterestAmount']) ? $data['DamageInterestAmount'] : 0,
                ':ClaimFee' => isset($data['ClaimFee']) ? $data['ClaimFee'] : 0,
                ':AdditionalClaimFee' => isset($data['AdditionalClaimFee']) ? $data['AdditionalClaimFee'] : 0,
                ':PrintedDate' => $data['PrintedDate'],
                ':ClaimAmount' => isset($data['ClaimAmount']) ? $data['ClaimAmount'] : 0,
                ':ReceiptAmountTotal' => isset($data['ReceiptAmountTotal']) ? $data['ReceiptAmountTotal'] : 0,
                ':RepayAmountTotal' => isset($data['RepayAmountTotal']) ? $data['RepayAmountTotal'] : 0,
                ':SundryLossTotal' => isset($data['SundryLossTotal']) ? $data['SundryLossTotal'] : 0,
                ':SundryIncomeTotal' => isset($data['SundryIncomeTotal']) ? $data['SundryIncomeTotal'] : 0,
                ':ClaimedBalance' => isset($data['ClaimedBalance']) ? $data['ClaimedBalance'] : 0,
                ':SundryLossTarget' => isset($data['SundryLossTarget']) ? $data['SundryLossTarget'] : 0,
                ':SundryIncomeTarget' => isset($data['SundryIncomeTarget']) ? $data['SundryIncomeTarget'] : 0,
                ':Clm_Count' => $data['Clm_Count'],
                ':F_ClaimDate' => $data['F_ClaimDate'],
                ':F_OpId' => $data['F_OpId'],
                ':F_LimitDate' => $data['F_LimitDate'],
                ':F_ClaimAmount' => $data['F_ClaimAmount'],
                ':Re1_ClaimAmount' => $data['Re1_ClaimAmount'],
                ':Re3_ClaimAmount' => $data['Re3_ClaimAmount'],
                ':AutoSundryStatus' => isset($data['AutoSundryStatus']) ? $data['AutoSundryStatus'] : 0,
                ':ReissueClass' => isset($data['ReissueClass']) ? $data['ReissueClass'] : 0,
                ':ReissueRequestDate' => $data['ReissueRequestDate'],
                ':LastProcessDate' => $data['LastProcessDate'],
                ':LastReceiptSeq' => $data['LastReceiptSeq'],
                ':MinClaimAmount' => isset($data['MinClaimAmount']) ? $data['MinClaimAmount'] : 0,
                ':MinUseAmount' => isset($data['MinUseAmount']) ? $data['MinUseAmount'] : 0,
                ':MinClaimFee' => isset($data['MinClaimFee']) ? $data['MinClaimFee'] : 0,
                ':MinDamageInterestAmount' => isset($data['MinDamageInterestAmount']) ? $data['MinDamageInterestAmount'] : 0,
                ':MinAdditionalClaimFee' => isset($data['MinAdditionalClaimFee']) ? $data['MinAdditionalClaimFee'] : 0,
                ':CheckingClaimAmount' => isset($data['CheckingClaimAmount']) ? $data['CheckingClaimAmount'] : 0,
                ':CheckingUseAmount' => isset($data['CheckingUseAmount']) ? $data['CheckingUseAmount'] : 0,
                ':CheckingClaimFee' => isset($data['CheckingClaimFee']) ? $data['CheckingClaimFee'] : 0,
                ':CheckingDamageInterestAmount' => isset($data['CheckingDamageInterestAmount']) ? $data['CheckingDamageInterestAmount'] : 0,
                ':CheckingAdditionalClaimFee' => isset($data['CheckingAdditionalClaimFee']) ? $data['CheckingAdditionalClaimFee'] : 0,
                ':BalanceClaimAmount' => isset($data['BalanceClaimAmount']) ? $data['BalanceClaimAmount'] : 0,
                ':BalanceUseAmount' => isset($data['BalanceUseAmount']) ? $data['BalanceUseAmount'] : 0,
                ':BalanceClaimFee' => isset($data['BalanceClaimFee']) ? $data['BalanceClaimFee'] : 0,
                ':BalanceDamageInterestAmount' => isset($data['BalanceDamageInterestAmount']) ? $data['BalanceDamageInterestAmount'] : 0,
                ':BalanceAdditionalClaimFee' => isset($data['BalanceAdditionalClaimFee']) ? $data['BalanceAdditionalClaimFee'] : 0,
                ':MypageReissueClass' => isset($data['MypageReissueClass']) ? $data['MypageReissueClass'] : 0,
                ':MypageReissueRequestDate' => $data['MypageReissueRequestDate'],
                ':MypageReissueDate' => $data['MypageReissueDate'],
                ':MypageReissueReClaimFee' => $data['MypageReissueReClaimFee'],
                ':CreditSettlementDecisionDate' => $data['CreditSettlementDecisionDate'],
                ':ReissueCount' => isset($data['ReissueCount']) ? $data['ReissueCount'] : 0,
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $claimId 請求ID
     * @return ResultInterface
     */
    public function saveUpdate($data, $claimId)
    {
        $row = $this->find($claimId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_ClaimControl ";
        $sql .= " SET ";
        $sql .= "     OrderSeq = :OrderSeq ";
        $sql .= " ,   EntCustSeq = :EntCustSeq ";
        $sql .= " ,   ClaimDate = :ClaimDate ";
        $sql .= " ,   ClaimCpId = :ClaimCpId ";
        $sql .= " ,   ClaimPattern = :ClaimPattern ";
        $sql .= " ,   LimitDate = :LimitDate ";
        $sql .= " ,   UseAmountTotal = :UseAmountTotal ";
        $sql .= " ,   DamageDays = :DamageDays ";
        $sql .= " ,   DamageBaseDate = :DamageBaseDate ";
        $sql .= " ,   DamageInterestAmount = :DamageInterestAmount ";
        $sql .= " ,   ClaimFee = :ClaimFee ";
        $sql .= " ,   AdditionalClaimFee = :AdditionalClaimFee ";
        $sql .= " ,   PrintedDate = :PrintedDate ";
        $sql .= " ,   ClaimAmount = :ClaimAmount ";
        $sql .= " ,   ReceiptAmountTotal = :ReceiptAmountTotal ";
        $sql .= " ,   RepayAmountTotal = :RepayAmountTotal ";
        $sql .= " ,   SundryLossTotal = :SundryLossTotal ";
        $sql .= " ,   SundryIncomeTotal = :SundryIncomeTotal ";
        $sql .= " ,   ClaimedBalance = :ClaimedBalance ";
        $sql .= " ,   SundryLossTarget = :SundryLossTarget ";
        $sql .= " ,   SundryIncomeTarget = :SundryIncomeTarget ";
        $sql .= " ,   Clm_Count = :Clm_Count ";
        $sql .= " ,   F_ClaimDate = :F_ClaimDate ";
        $sql .= " ,   F_OpId = :F_OpId ";
        $sql .= " ,   F_LimitDate = :F_LimitDate ";
        $sql .= " ,   F_ClaimAmount = :F_ClaimAmount ";
        $sql .= " ,   Re1_ClaimAmount = :Re1_ClaimAmount ";
        $sql .= " ,   Re3_ClaimAmount = :Re3_ClaimAmount ";
        $sql .= " ,   AutoSundryStatus = :AutoSundryStatus ";
        $sql .= " ,   ReissueClass = :ReissueClass ";
        $sql .= " ,   ReissueRequestDate = :ReissueRequestDate ";
        $sql .= " ,   LastProcessDate = :LastProcessDate ";
        $sql .= " ,   LastReceiptSeq = :LastReceiptSeq ";
        $sql .= " ,   MinClaimAmount = :MinClaimAmount ";
        $sql .= " ,   MinUseAmount = :MinUseAmount ";
        $sql .= " ,   MinClaimFee = :MinClaimFee ";
        $sql .= " ,   MinDamageInterestAmount = :MinDamageInterestAmount ";
        $sql .= " ,   MinAdditionalClaimFee = :MinAdditionalClaimFee ";
        $sql .= " ,   CheckingClaimAmount = :CheckingClaimAmount ";
        $sql .= " ,   CheckingUseAmount = :CheckingUseAmount ";
        $sql .= " ,   CheckingClaimFee = :CheckingClaimFee ";
        $sql .= " ,   CheckingDamageInterestAmount = :CheckingDamageInterestAmount ";
        $sql .= " ,   CheckingAdditionalClaimFee = :CheckingAdditionalClaimFee ";
        $sql .= " ,   BalanceClaimAmount = :BalanceClaimAmount ";
        $sql .= " ,   BalanceUseAmount = :BalanceUseAmount ";
        $sql .= " ,   BalanceClaimFee = :BalanceClaimFee ";
        $sql .= " ,   BalanceDamageInterestAmount = :BalanceDamageInterestAmount ";
        $sql .= " ,   BalanceAdditionalClaimFee = :BalanceAdditionalClaimFee ";
        $sql .= " ,   MypageReissueClass = :MypageReissueClass ";
        $sql .= " ,   MypageReissueRequestDate = :MypageReissueRequestDate ";
        $sql .= " ,   MypageReissueDate = :MypageReissueDate ";
        $sql .= " ,   MypageReissueReClaimFee = :MypageReissueReClaimFee ";
        $sql .= " ,   CreditSettlementDecisionDate = :CreditSettlementDecisionDate ";
        $sql .= " ,   ReissueCount = :ReissueCount ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE ClaimId = :ClaimId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ClaimId' => $claimId,
                ':OrderSeq' => $row['OrderSeq'],
                ':EntCustSeq' => $row['EntCustSeq'],
                ':ClaimDate' => $row['ClaimDate'],
                ':ClaimCpId' => $row['ClaimCpId'],
                ':ClaimPattern' => $row['ClaimPattern'],
                ':LimitDate' => $row['LimitDate'],
                ':UseAmountTotal' => $row['UseAmountTotal'],
                ':DamageDays' => $row['DamageDays'],
                ':DamageBaseDate' => $row['DamageBaseDate'],
                ':DamageInterestAmount' => $row['DamageInterestAmount'],
                ':ClaimFee' => $row['ClaimFee'],
                ':AdditionalClaimFee' => $row['AdditionalClaimFee'],
                ':PrintedDate' => $row['PrintedDate'],
                ':ClaimAmount' => $row['ClaimAmount'],
                ':ReceiptAmountTotal' => $row['ReceiptAmountTotal'],
                ':RepayAmountTotal' => $row['RepayAmountTotal'],
                ':SundryLossTotal' => $row['SundryLossTotal'],
                ':SundryIncomeTotal' => $row['SundryIncomeTotal'],
                ':ClaimedBalance' => $row['ClaimedBalance'],
                ':SundryLossTarget' => $row['SundryLossTarget'],
                ':SundryIncomeTarget' => $row['SundryIncomeTarget'],
                ':Clm_Count' => $row['Clm_Count'],
                ':F_ClaimDate' => $row['F_ClaimDate'],
                ':F_OpId' => $row['F_OpId'],
                ':F_LimitDate' => $row['F_LimitDate'],
                ':F_ClaimAmount' => $row['F_ClaimAmount'],
                ':Re1_ClaimAmount' => $row['Re1_ClaimAmount'],
                ':Re3_ClaimAmount' => $row['Re3_ClaimAmount'],
                ':AutoSundryStatus' => $row['AutoSundryStatus'],
                ':ReissueClass' => $row['ReissueClass'],
                ':ReissueRequestDate' => $row['ReissueRequestDate'],
                ':LastProcessDate' => $row['LastProcessDate'],
                ':LastReceiptSeq' => $row['LastReceiptSeq'],
                ':MinClaimAmount' => $row['MinClaimAmount'],
                ':MinUseAmount' => $row['MinUseAmount'],
                ':MinClaimFee' => $row['MinClaimFee'],
                ':MinDamageInterestAmount' => $row['MinDamageInterestAmount'],
                ':MinAdditionalClaimFee' => $row['MinAdditionalClaimFee'],
                ':CheckingClaimAmount' => $row['CheckingClaimAmount'],
                ':CheckingUseAmount' => $row['CheckingUseAmount'],
                ':CheckingClaimFee' => $row['CheckingClaimFee'],
                ':CheckingDamageInterestAmount' => $row['CheckingDamageInterestAmount'],
                ':CheckingAdditionalClaimFee' => $row['CheckingAdditionalClaimFee'],
                ':BalanceClaimAmount' => $row['BalanceClaimAmount'],
                ':BalanceUseAmount' => $row['BalanceUseAmount'],
                ':BalanceClaimFee' => $row['BalanceClaimFee'],
                ':BalanceDamageInterestAmount' => $row['BalanceDamageInterestAmount'],
                ':BalanceAdditionalClaimFee' => $row['BalanceAdditionalClaimFee'],
                ':MypageReissueClass' => $row['MypageReissueClass'],
                ':MypageReissueRequestDate' => $row['MypageReissueRequestDate'],
                ':MypageReissueDate' => $row['MypageReissueDate'],
                ':MypageReissueReClaimFee' => $row['MypageReissueReClaimFee'],
                ':CreditSettlementDecisionDate' => $row['CreditSettlementDecisionDate'],
                ':ReissueCount' => $row['ReissueCount'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

	/**
	 * マイページ請求書再発行情報を更新する。
	 *
	 * @param int $oseq
	 */
	public function updateMypageReissue($data, $orderSeq)
	{
        $sql  = " UPDATE T_ClaimControl ";
        $sql .= "    SET MypageReissueClass       = :MypageReissueClass ";
        $sql .= "       ,MypageReissueRequestDate = :MypageReissueRequestDate ";
        $sql .= "       ,MypageReissueReClaimFee  = :MypageReissueReClaimFee ";
        $sql .= "       ,UpdateId                 = :UpdateId ";
        $sql .= "       ,UpdateDate               = NOW() ";
        $sql .= "  WHERE OrderSeq                 = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':MypageReissueClass' => $data['MypageReissueClass'],
                ':MypageReissueRequestDate' => $data['MypageReissueRequestDate'],
                ':MypageReissueReClaimFee' => $data['MypageReissueReClaimFee'],
                ':UpdateId' => $data['UpdateId'],
                ':OrderSeq' => $orderSeq,
        );

        return $stm->execute($prm);
	}

}
