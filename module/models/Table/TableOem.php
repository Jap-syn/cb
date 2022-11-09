<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_Oemテーブルへのアダプタ
 */
class TableOem
{
	protected $_name = 'T_Oem';
	protected $_primary = array('OemId');
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
	 * Oemデータを取得する
	 *
	 * @param int $oemId
	 * @return ResultInterface
	 */
	public function find($oemId)
	{
        $sql  = " SELECT * FROM T_Oem WHERE OemId = :OemId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $oemId,
        );

        return $stm->execute($prm);
	}

	/**
	 * すべてのOemデータを取得する
	 *
	 * @param bool $asc 昇順ソートの場合はtrueを指定。デフォルトはfalse（＝降順ソート）
	 * @return ResultInterface
	 */
	public function getAllOem($asc = false)
	{
	    $sql = " SELECT * FROM T_Oem ORDER BY OemId " . ($asc ? "asc" : "desc");
        return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 指定OEMIDのOEMデータを取得する。
	 *
	 * @param string $OemId OEMID
	 * @return ResultInterface
	 */
	public function findOem($OemId)
	{
        $sql = " SELECT * FROM T_Oem WHERE OemId = :OemId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $OemId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定OEMIDのOEMデータを取得する。
	 *
	 * @param string $oemId OEMID
	 * @return ResultInterface
	 */
	public function findOem2($oemId)
	{
        return $this->findOem($oemId);
	}

    /**
     * OEMIDと名前のリスト、もしくはOEMID指定で名前を取得する。
     *
     * @param string $oemId OEMID
     * @return array
     */
     public function getOemIdList($oemId = null)
     {
         $sql  = " SELECT 0 AS OemId, '-----' AS OemNameKj ";
         $sql .= " UNION ALL ";
         $sql .= " SELECT OemId, OemNameKj FROM T_Oem ";
         if(!is_null($oemId)) {
             $sql .= (" WHERE OemId = :OemId ");
         }
         $sql .= " ORDER BY OemId ";

         $stm = $this->_adapter->query($sql);

         $prm = array(
                 ':OemId' => $oemId,
         );

         $ri = $stm->execute($prm);

         foreach($ri as $oem) {
             $result[$oem['OemId']] = $oem['OemNameKj'];
         }
         ksort($result);

         return $result;
    }

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO T_Oem (ApplicationDate, ServiceInDate, RegistDate, OemNameKj, OemNameKn, PostalCode, PrefectureCode, PrefectureName, City, Town, Building, RepNameKj, RepNameKn, Phone, Fax, MonthlyFee, N_MonthlyFee, SettlementFeeRateRKF, SettlementFeeRateSTD, SettlementFeeRateEXP, SettlementFeeRateSPC, ClaimFeeBS, ClaimFeeDK, EntMonthlyFeeRKF, EntMonthlyFeeSTD, EntMonthlyFeeEXP, EntMonthlyFeeSPC, OpDkInitFeeRate, OpDkMonthlyFeeRate, OpApiRegOrdMonthlyFeeRate, OpApiAllInitFeeRate, OpApiAllMonthlyFeeRate, Salesman, FfName, FfCode, FfBranchName, FfBranchCode, FfAccountNumber, FfAccountClass, FfAccountName, TcClass, CpNameKj, CpNameKn, DivisionName, MailAddress, ContactPhoneNumber, ContactFaxNumber, Note, ValidFlg, InvalidatedDate, InvalidatedReason, KisanbiDelayDays, AccessId, EntLoginIdPrefix, OrderIdPrefix, Notice, ServiceName, ServicePhone, SupportTime, SupportMail, Copyright, LargeLogo, SmallLogo, Imprint, PayingMethod, HelpUrl, FixPattern, ReclaimAccountPolicy, FavIcon, FavIconType, EntAccountEditLimitation, EntAccountAdditionalMessage, CreditCriterion, AutoCreditDateFrom, AutoCreditDateTo, AutoCreditCriterion, PrintEntOrderIdOnClaimFlg, DamageInterestRate, OemClaimTransDays, OemClaimTransFlg, OemFixedPattern, OemFixedDay1, OemFixedDay2, OemFixedDay3, OemFixedDay_Week, SettlementDay1, SettlementDay2, SettlementDay3, SettlementDay_Week, AutoCreditLimitAmount, JapanPostPrintFlg, MembershipAgreement, B_OemFixedDate, B_SettlementDate, N_OemFixedDate, N_SettlementDate, SameFfTcFeeThirtyKAndOver, SameFfTcFeeUnderThirtyK, OtherFfTcFeeThirtyKAndOver, OtherFfTcFeeUnderThirtyK, TimemachineNgFlg, RecordClaimPrintedDateFlg, FixedLengthFlg, ConsignorCode, ConsignorName, RemittingBankCode, RemittingBankName, RemittingBranchCode, RemittingBranchName, AccountClass, AccountNumber, DspTaxFlg, AccOemClass, SettlementFeeRatePlan, EntMonthlyFeePlan, AddTcClass, StyleSheets, ChangeIssuerNameFlg, RegistId, FirstCreditTransferClaimFeeOem, FirstCreditTransferClaimFeeWebOem, CreditTransferClaimFeeOem, UpdateDate, UpdateId) VALUES (";
        $sql .= "   :ApplicationDate ";
        $sql .= " , :ServiceInDate ";
        $sql .= " , :RegistDate ";
        $sql .= " , :OemNameKj ";
        $sql .= " , :OemNameKn ";
        $sql .= " , :PostalCode ";
        $sql .= " , :PrefectureCode ";
        $sql .= " , :PrefectureName ";
        $sql .= " , :City ";
        $sql .= " , :Town ";
        $sql .= " , :Building ";
        $sql .= " , :RepNameKj ";
        $sql .= " , :RepNameKn ";
        $sql .= " , :Phone ";
        $sql .= " , :Fax ";
        $sql .= " , :MonthlyFee ";
        $sql .= " , :N_MonthlyFee ";
        $sql .= " , :SettlementFeeRateRKF ";
        $sql .= " , :SettlementFeeRateSTD ";
        $sql .= " , :SettlementFeeRateEXP ";
        $sql .= " , :SettlementFeeRateSPC ";
        $sql .= " , :ClaimFeeBS ";
        $sql .= " , :ClaimFeeDK ";
        $sql .= " , :EntMonthlyFeeRKF ";
        $sql .= " , :EntMonthlyFeeSTD ";
        $sql .= " , :EntMonthlyFeeEXP ";
        $sql .= " , :EntMonthlyFeeSPC ";
        $sql .= " , :OpDkInitFeeRate ";
        $sql .= " , :OpDkMonthlyFeeRate ";
        $sql .= " , :OpApiRegOrdMonthlyFeeRate ";
        $sql .= " , :OpApiAllInitFeeRate ";
        $sql .= " , :OpApiAllMonthlyFeeRate ";
        $sql .= " , :Salesman ";
        $sql .= " , :FfName ";
        $sql .= " , :FfCode ";
        $sql .= " , :FfBranchName ";
        $sql .= " , :FfBranchCode ";
        $sql .= " , :FfAccountNumber ";
        $sql .= " , :FfAccountClass ";
        $sql .= " , :FfAccountName ";
        $sql .= " , :TcClass ";
        $sql .= " , :CpNameKj ";
        $sql .= " , :CpNameKn ";
        $sql .= " , :DivisionName ";
        $sql .= " , :MailAddress ";
        $sql .= " , :ContactPhoneNumber ";
        $sql .= " , :ContactFaxNumber ";
        $sql .= " , :Note ";
        $sql .= " , :ValidFlg ";
        $sql .= " , :InvalidatedDate ";
        $sql .= " , :InvalidatedReason ";
        $sql .= " , :KisanbiDelayDays ";
        $sql .= " , :AccessId ";
        $sql .= " , :EntLoginIdPrefix ";
        $sql .= " , :OrderIdPrefix ";
        $sql .= " , :Notice ";
        $sql .= " , :ServiceName ";
        $sql .= " , :ServicePhone ";
        $sql .= " , :SupportTime ";
        $sql .= " , :SupportMail ";
        $sql .= " , :Copyright ";
        $sql .= " , :LargeLogo ";
        $sql .= " , :SmallLogo ";
        $sql .= " , :Imprint ";
        $sql .= " , :PayingMethod ";
        $sql .= " , :HelpUrl ";
        $sql .= " , :FixPattern ";
        $sql .= " , :ReclaimAccountPolicy ";
        $sql .= " , :FavIcon ";
        $sql .= " , :FavIconType ";
        $sql .= " , :EntAccountEditLimitation ";
        $sql .= " , :EntAccountAdditionalMessage ";
        $sql .= " , :CreditCriterion ";
        $sql .= " , :AutoCreditDateFrom ";
        $sql .= " , :AutoCreditDateTo ";
        $sql .= " , :AutoCreditCriterion ";
        $sql .= " , :PrintEntOrderIdOnClaimFlg ";
        $sql .= " , :DamageInterestRate ";
        $sql .= " , :OemClaimTransDays ";
        $sql .= " , :OemClaimTransFlg ";
        $sql .= " , :OemFixedPattern ";
        $sql .= " , :OemFixedDay1 ";
        $sql .= " , :OemFixedDay2 ";
        $sql .= " , :OemFixedDay3 ";
        $sql .= " , :OemFixedDay_Week ";
        $sql .= " , :SettlementDay1 ";
        $sql .= " , :SettlementDay2 ";
        $sql .= " , :SettlementDay3 ";
        $sql .= " , :SettlementDay_Week ";
        $sql .= " , :AutoCreditLimitAmount ";
        $sql .= " , :JapanPostPrintFlg ";
        $sql .= " , :MembershipAgreement ";
        $sql .= " , :B_OemFixedDate ";
        $sql .= " , :B_SettlementDate ";
        $sql .= " , :N_OemFixedDate ";
        $sql .= " , :N_SettlementDate ";
        $sql .= " , :SameFfTcFeeThirtyKAndOver ";
        $sql .= " , :SameFfTcFeeUnderThirtyK ";
        $sql .= " , :OtherFfTcFeeThirtyKAndOver ";
        $sql .= " , :OtherFfTcFeeUnderThirtyK ";
        $sql .= " , :TimemachineNgFlg ";
        $sql .= " , :RecordClaimPrintedDateFlg ";
        $sql .= " , :FixedLengthFlg ";
        $sql .= " , :ConsignorCode ";
        $sql .= " , :ConsignorName ";
        $sql .= " , :RemittingBankCode ";
        $sql .= " , :RemittingBankName ";
        $sql .= " , :RemittingBranchCode ";
        $sql .= " , :RemittingBranchName ";
        $sql .= " , :AccountClass ";
        $sql .= " , :AccountNumber ";
        $sql .= " , :DspTaxFlg ";
        $sql .= " , :AccOemClass ";
        $sql .= " , :SettlementFeeRatePlan ";
        $sql .= " , :EntMonthlyFeePlan ";
        $sql .= " , :AddTcClass ";
        $sql .= " , :StyleSheets ";
        $sql .= " , :ChangeIssuerNameFlg ";
        $sql .= " , :RegistId ";
        $sql .= " , :FirstCreditTransferClaimFeeOem ";
        $sql .= " , :FirstCreditTransferClaimFeeWebOem ";
        $sql .= " , :CreditTransferClaimFeeOem ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ApplicationDate' => $data['ApplicationDate'],
                ':ServiceInDate' => $data['ServiceInDate'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':OemNameKj' => $data['OemNameKj'],
                ':OemNameKn' => $data['OemNameKn'],
                ':PostalCode' => $data['PostalCode'],
                ':PrefectureCode' => $data['PrefectureCode'],
                ':PrefectureName' => $data['PrefectureName'],
                ':City' => $data['City'],
                ':Town' => $data['Town'],
                ':Building' => $data['Building'],
                ':RepNameKj' => $data['RepNameKj'],
                ':RepNameKn' => $data['RepNameKn'],
                ':Phone' => $data['Phone'],
                ':Fax' => $data['Fax'],
                ':MonthlyFee' => $data['MonthlyFee'],
                ':N_MonthlyFee' => $data['N_MonthlyFee'],
                ':SettlementFeeRateRKF' => $data['SettlementFeeRateRKF'],
                ':SettlementFeeRateSTD' => $data['SettlementFeeRateSTD'],
                ':SettlementFeeRateEXP' => $data['SettlementFeeRateEXP'],
                ':SettlementFeeRateSPC' => $data['SettlementFeeRateSPC'],
                ':ClaimFeeBS' => $data['ClaimFeeBS'],
                ':ClaimFeeDK' => $data['ClaimFeeDK'],
                ':EntMonthlyFeeRKF' => $data['EntMonthlyFeeRKF'],
                ':EntMonthlyFeeSTD' => $data['EntMonthlyFeeSTD'],
                ':EntMonthlyFeeEXP' => $data['EntMonthlyFeeEXP'],
                ':EntMonthlyFeeSPC' => $data['EntMonthlyFeeSPC'],
                ':OpDkInitFeeRate' => $data['OpDkInitFeeRate'],
                ':OpDkMonthlyFeeRate' => $data['OpDkMonthlyFeeRate'],
                ':OpApiRegOrdMonthlyFeeRate' => $data['OpApiRegOrdMonthlyFeeRate'],
                ':OpApiAllInitFeeRate' => $data['OpApiAllInitFeeRate'],
                ':OpApiAllMonthlyFeeRate' => $data['OpApiAllMonthlyFeeRate'],
                ':Salesman' => $data['Salesman'],
                ':FfName' => $data['FfName'],
                ':FfCode' => $data['FfCode'],
                ':FfBranchName' => $data['FfBranchName'],
                ':FfBranchCode' => $data['FfBranchCode'],
                ':FfAccountNumber' => $data['FfAccountNumber'],
                ':FfAccountClass' => $data['FfAccountClass'],
                ':FfAccountName' => $data['FfAccountName'],
                ':TcClass' => $data['TcClass'],
                ':CpNameKj' => $data['CpNameKj'],
                ':CpNameKn' => $data['CpNameKn'],
                ':DivisionName' => $data['DivisionName'],
                ':MailAddress' => $data['MailAddress'],
                ':ContactPhoneNumber' => $data['ContactPhoneNumber'],
                ':ContactFaxNumber' => $data['ContactFaxNumber'],
                ':Note' => $data['Note'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
                ':InvalidatedDate' => $data['InvalidatedDate'],
                ':InvalidatedReason' => $data['InvalidatedReason'],
                ':KisanbiDelayDays' => $data['KisanbiDelayDays'],
                ':AccessId' => $data['AccessId'],
                ':EntLoginIdPrefix' => $data['EntLoginIdPrefix'],
                ':OrderIdPrefix' => $data['OrderIdPrefix'],
                ':Notice' => $data['Notice'],
                ':ServiceName' => $data['ServiceName'],
                ':ServicePhone' => $data['ServicePhone'],
                ':SupportTime' => $data['SupportTime'],
                ':SupportMail' => $data['SupportMail'],
                ':Copyright' => $data['Copyright'],
                ':LargeLogo' => $data['LargeLogo'],
                ':SmallLogo' => $data['SmallLogo'],
                ':Imprint' => $data['Imprint'],
                ':PayingMethod' => isset($data['PayingMethod']) ? $data['PayingMethod'] : 0,
                ':HelpUrl' => $data['HelpUrl'],
                ':FixPattern' => $data['FixPattern'],
                ':ReclaimAccountPolicy' => isset($data['ReclaimAccountPolicy']) ? $data['ReclaimAccountPolicy'] : 0,
                ':FavIcon' => $data['FavIcon'],
                ':FavIconType' => $data['FavIconType'],
                ':EntAccountEditLimitation' => isset($data['EntAccountEditLimitation']) ? $data['EntAccountEditLimitation'] : 0,
                ':EntAccountAdditionalMessage' => $data['EntAccountAdditionalMessage'],
                ':CreditCriterion' => $data['CreditCriterion'],
                ':AutoCreditDateFrom' => $data['AutoCreditDateFrom'],
                ':AutoCreditDateTo' => $data['AutoCreditDateTo'],
                ':AutoCreditCriterion' => isset($data['AutoCreditCriterion']) ? $data['AutoCreditCriterion'] : 0,
                ':PrintEntOrderIdOnClaimFlg' => isset($data['PrintEntOrderIdOnClaimFlg']) ? $data['PrintEntOrderIdOnClaimFlg'] : 0,
                ':DamageInterestRate' => $data['DamageInterestRate'],
                ':OemClaimTransDays' => isset($data['OemClaimTransDays']) ? $data['OemClaimTransDays'] : 0,
                ':OemClaimTransFlg' => isset($data['OemClaimTransFlg']) ? $data['OemClaimTransFlg'] : 0,
                ':OemFixedPattern' => isset($data['OemFixedPattern']) ? $data['OemFixedPattern'] : 0,
                ':OemFixedDay1' => $data['OemFixedDay1'],
                ':OemFixedDay2' => $data['OemFixedDay2'],
                ':OemFixedDay3' => $data['OemFixedDay3'],
                ':OemFixedDay_Week' => $data['OemFixedDay_Week'],
                ':SettlementDay1' => $data['SettlementDay1'],
                ':SettlementDay2' => $data['SettlementDay2'],
                ':SettlementDay3' => $data['SettlementDay3'],
                ':SettlementDay_Week' => $data['SettlementDay_Week'],
                ':AutoCreditLimitAmount' => $data['AutoCreditLimitAmount'],
                ':JapanPostPrintFlg' => $data['JapanPostPrintFlg'],
                ':MembershipAgreement' => $data['MembershipAgreement'],
                ':B_OemFixedDate' => $data['B_OemFixedDate'],
                ':B_SettlementDate' => $data['B_SettlementDate'],
                ':N_OemFixedDate' => $data['N_OemFixedDate'],
                ':N_SettlementDate' => $data['N_SettlementDate'],
                ':SameFfTcFeeThirtyKAndOver' => $data['SameFfTcFeeThirtyKAndOver'],
                ':SameFfTcFeeUnderThirtyK' => $data['SameFfTcFeeUnderThirtyK'],
                ':OtherFfTcFeeThirtyKAndOver' => $data['OtherFfTcFeeThirtyKAndOver'],
                ':OtherFfTcFeeUnderThirtyK' => $data['OtherFfTcFeeUnderThirtyK'],
                ':TimemachineNgFlg' => $data['TimemachineNgFlg'],
                ':RecordClaimPrintedDateFlg' => isset($data['RecordClaimPrintedDateFlg']) ? $data['RecordClaimPrintedDateFlg'] : 0,
                ':FixedLengthFlg' => $data['FixedLengthFlg'],
                ':ConsignorCode' => $data['ConsignorCode'],
                ':ConsignorName' => $data['ConsignorName'],
                ':RemittingBankCode' => $data['RemittingBankCode'],
                ':RemittingBankName' => $data['RemittingBankName'],
                ':RemittingBranchCode' => $data['RemittingBranchCode'],
                ':RemittingBranchName' => $data['RemittingBranchName'],
                ':AccountClass' => $data['AccountClass'],
                ':AccountNumber' => $data['AccountNumber'],
                ':DspTaxFlg' => $data['DspTaxFlg'],
                ':AccOemClass' => isset($data['AccOemClass']) ? $data['AccOemClass'] : 0,
                ':SettlementFeeRatePlan' => $data['SettlementFeeRatePlan'],
                ':EntMonthlyFeePlan' => $data['EntMonthlyFeePlan'],
                ':AddTcClass' => $data['AddTcClass'],
                ':StyleSheets' => $data['StyleSheets'],
                ':ChangeIssuerNameFlg' => $data['ChangeIssuerNameFlg'],
                ':RegistId' => $data['RegistId'],
                ':FirstCreditTransferClaimFeeOem' => $data['FirstCreditTransferClaimFeeOem'],
                ':FirstCreditTransferClaimFeeWebOem' => $data['FirstCreditTransferClaimFeeWebOem'],
                ':CreditTransferClaimFeeOem' => $data['CreditTransferClaimFeeOem'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

	/**
	 * 指定のAccessIdの現在の件数をカウントする
	 *
	 * @param string $accessId 評価するAccessId
	 * @param int $excludeOemId カウントから除外するOEM ID。省略時は-1（＝除外なし）
	 * @return int
	 */
	public function countAccessId($accessId, $excludeOemId = -1)
	{
        $sql = " SELECT COUNT(1) AS cnt FROM T_Oem WHERE ValidFlg = 1 AND AccessId = :AccessId AND OemId <> :OemId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AccessId' => $accessId,
                ':OemId' => $excludeOemId,
        );

        return (int)$stm->execute($prm)->current()['cnt'];
	}

	/**
	 * AccessIdからT_Oem行を逆引きする
	 *
	 * @param string $accessId AccessId
	 * @return ResultInterface 行オブジェクト
	 */
	public function findByAccessId($accessId)
	{
        $sql = " SELECT * FROM T_Oem WHERE ValidFlg = 1 AND AccessId = :AccessId ORDER BY OemId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AccessId' => $accessId,
        );

        return $stm->execute($prm);
	}

	/**
	 * EntLoginIdPrefixからT_Oem行を逆引きする
	 *
	 * @param string $loginPrefix 事業者ログインIDプレフィックス
	 * @return ResultInterface 行オブジェクト
	 */
	public function findByLoginIdPrefix($loginPrefix)
	{
        $sql = " SELECT * FROM T_Oem WHERE ValidFlg = 1 AND EntLoginIdPrefix = :EntLoginIdPrefix ORDER BY OemId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EntLoginIdPrefix' => $loginPrefix,
        );

        return $stm->execute($prm);
	}

	/**
	 * OrderIdPrefixからT_Oem行を逆引きする
	 *
	 * @param string $orderPrefix 事業者注文IDプレフィックス
	 * @return ResultInterface 行オブジェクト
	 */
	public function findByOrderIdPrefix($orderPrefix)
	{
        $sql = " SELECT * FROM T_Oem WHERE ValidFlg = 1 AND OrderIdPrefix = :OrderIdPrefix ORDER BY OemId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderIdPrefix' => $orderPrefix,
        );

        return $stm->execute($prm);
	}

    /**
     * 指定のEntLoginIdPrefixの現在の件数をカウントする
     *
     * @param string $loginPrefix 評価するEntLoginIdPrefix
     * @param int $excludeOemId カウントから除外するOEM ID。省略時は-1（＝除外なし）
     * @return int
     */
    public function countEntLoginIdPrefix($loginPrefix, $excludeOemId = -1)
    {
        $sql = " SELECT COUNT(1) AS cnt FROM T_Oem WHERE ValidFlg = 1 AND EntLoginIdPrefix = :EntLoginIdPrefix AND OemId <> :OemId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EntLoginIdPrefix' => $loginPrefix,
                ':OemId' => $excludeOemId,
        );

        return (int)$stm->execute($prm)->current()['cnt'];
        return $stm->execute($prm);
    }

    /**
     * 指定のOrderIdPrefixの現在の件数をカウントする
     *
     * @param string $loginPrefix 評価するEntLoginIdPrefix
     * @param int $excludeOemId カウントから除外するOEM ID。省略時は-1（＝除外なし）
     * @return int
     */
    public function countOrderIdPrefix($orderPrefix, $excludeOemId = -1)
    {
        $sql = " SELECT COUNT(1) AS cnt FROM T_Oem WHERE ValidFlg = 1 AND OrderIdPrefix = :OrderIdPrefix AND OemId <> :OemId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderIdPrefix' => $orderPrefix,
                ':OemId' => $excludeOemId,
        );

        return (int)$stm->execute($prm)->current()['cnt'];
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
        $sql = " SELECT * FROM T_Oem WHERE OemId = :OemId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $eid,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_Oem ";
        $sql .= " SET ";
        $sql .= "     ApplicationDate = :ApplicationDate ";
        $sql .= " ,   ServiceInDate = :ServiceInDate ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   OemNameKj = :OemNameKj ";
        $sql .= " ,   OemNameKn = :OemNameKn ";
        $sql .= " ,   PostalCode = :PostalCode ";
        $sql .= " ,   PrefectureCode = :PrefectureCode ";
        $sql .= " ,   PrefectureName = :PrefectureName ";
        $sql .= " ,   City = :City ";
        $sql .= " ,   Town = :Town ";
        $sql .= " ,   Building = :Building ";
        $sql .= " ,   RepNameKj = :RepNameKj ";
        $sql .= " ,   RepNameKn = :RepNameKn ";
        $sql .= " ,   Phone = :Phone ";
        $sql .= " ,   Fax = :Fax ";
        $sql .= " ,   MonthlyFee = :MonthlyFee ";
        $sql .= " ,   N_MonthlyFee = :N_MonthlyFee ";
        $sql .= " ,   SettlementFeeRateRKF = :SettlementFeeRateRKF ";
        $sql .= " ,   SettlementFeeRateSTD = :SettlementFeeRateSTD ";
        $sql .= " ,   SettlementFeeRateEXP = :SettlementFeeRateEXP ";
        $sql .= " ,   SettlementFeeRateSPC = :SettlementFeeRateSPC ";
        $sql .= " ,   ClaimFeeBS = :ClaimFeeBS ";
        $sql .= " ,   ClaimFeeDK = :ClaimFeeDK ";
        $sql .= " ,   EntMonthlyFeeRKF = :EntMonthlyFeeRKF ";
        $sql .= " ,   EntMonthlyFeeSTD = :EntMonthlyFeeSTD ";
        $sql .= " ,   EntMonthlyFeeEXP = :EntMonthlyFeeEXP ";
        $sql .= " ,   EntMonthlyFeeSPC = :EntMonthlyFeeSPC ";
        $sql .= " ,   OpDkInitFeeRate = :OpDkInitFeeRate ";
        $sql .= " ,   OpDkMonthlyFeeRate = :OpDkMonthlyFeeRate ";
        $sql .= " ,   OpApiRegOrdMonthlyFeeRate = :OpApiRegOrdMonthlyFeeRate ";
        $sql .= " ,   OpApiAllInitFeeRate = :OpApiAllInitFeeRate ";
        $sql .= " ,   OpApiAllMonthlyFeeRate = :OpApiAllMonthlyFeeRate ";
        $sql .= " ,   Salesman = :Salesman ";
        $sql .= " ,   FfName = :FfName ";
        $sql .= " ,   FfCode = :FfCode ";
        $sql .= " ,   FfBranchName = :FfBranchName ";
        $sql .= " ,   FfBranchCode = :FfBranchCode ";
        $sql .= " ,   FfAccountNumber = :FfAccountNumber ";
        $sql .= " ,   FfAccountClass = :FfAccountClass ";
        $sql .= " ,   FfAccountName = :FfAccountName ";
        $sql .= " ,   TcClass = :TcClass ";
        $sql .= " ,   CpNameKj = :CpNameKj ";
        $sql .= " ,   CpNameKn = :CpNameKn ";
        $sql .= " ,   DivisionName = :DivisionName ";
        $sql .= " ,   MailAddress = :MailAddress ";
        $sql .= " ,   ContactPhoneNumber = :ContactPhoneNumber ";
        $sql .= " ,   ContactFaxNumber = :ContactFaxNumber ";
        $sql .= " ,   Note = :Note ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " ,   InvalidatedDate = :InvalidatedDate ";
        $sql .= " ,   InvalidatedReason = :InvalidatedReason ";
        $sql .= " ,   KisanbiDelayDays = :KisanbiDelayDays ";
        $sql .= " ,   AccessId = :AccessId ";
        $sql .= " ,   EntLoginIdPrefix = :EntLoginIdPrefix ";
        $sql .= " ,   OrderIdPrefix = :OrderIdPrefix ";
        $sql .= " ,   Notice = :Notice ";
        $sql .= " ,   ServiceName = :ServiceName ";
        $sql .= " ,   ServicePhone = :ServicePhone ";
        $sql .= " ,   SupportTime = :SupportTime ";
        $sql .= " ,   SupportMail = :SupportMail ";
        $sql .= " ,   Copyright = :Copyright ";
        $sql .= " ,   LargeLogo = :LargeLogo ";
        $sql .= " ,   SmallLogo = :SmallLogo ";
        $sql .= " ,   Imprint = :Imprint ";
        $sql .= " ,   PayingMethod = :PayingMethod ";
        $sql .= " ,   HelpUrl = :HelpUrl ";
        $sql .= " ,   FixPattern = :FixPattern ";
        $sql .= " ,   ReclaimAccountPolicy = :ReclaimAccountPolicy ";
        $sql .= " ,   FavIcon = :FavIcon ";
        $sql .= " ,   FavIconType = :FavIconType ";
        $sql .= " ,   EntAccountEditLimitation = :EntAccountEditLimitation ";
        $sql .= " ,   EntAccountAdditionalMessage = :EntAccountAdditionalMessage ";
        $sql .= " ,   CreditCriterion = :CreditCriterion ";
        $sql .= " ,   AutoCreditDateFrom = :AutoCreditDateFrom ";
        $sql .= " ,   AutoCreditDateTo = :AutoCreditDateTo ";
        $sql .= " ,   AutoCreditCriterion = :AutoCreditCriterion ";
        $sql .= " ,   PrintEntOrderIdOnClaimFlg = :PrintEntOrderIdOnClaimFlg ";
        $sql .= " ,   DamageInterestRate = :DamageInterestRate ";
        $sql .= " ,   OemClaimTransDays = :OemClaimTransDays ";
        $sql .= " ,   OemClaimTransFlg = :OemClaimTransFlg ";
        $sql .= " ,   OemFixedPattern = :OemFixedPattern ";
        $sql .= " ,   OemFixedDay1 = :OemFixedDay1 ";
        $sql .= " ,   OemFixedDay2 = :OemFixedDay2 ";
        $sql .= " ,   OemFixedDay3 = :OemFixedDay3 ";
        $sql .= " ,   OemFixedDay_Week = :OemFixedDay_Week ";
        $sql .= " ,   SettlementDay1 = :SettlementDay1 ";
        $sql .= " ,   SettlementDay2 = :SettlementDay2 ";
        $sql .= " ,   SettlementDay3 = :SettlementDay3 ";
        $sql .= " ,   SettlementDay_Week = :SettlementDay_Week ";
        $sql .= " ,   AutoCreditLimitAmount = :AutoCreditLimitAmount ";
        $sql .= " ,   JapanPostPrintFlg = :JapanPostPrintFlg ";
        $sql .= " ,   MembershipAgreement = :MembershipAgreement ";
        $sql .= " ,   B_OemFixedDate = :B_OemFixedDate ";
        $sql .= " ,   B_SettlementDate = :B_SettlementDate ";
        $sql .= " ,   N_OemFixedDate = :N_OemFixedDate ";
        $sql .= " ,   N_SettlementDate = :N_SettlementDate ";
        $sql .= " ,   SameFfTcFeeThirtyKAndOver = :SameFfTcFeeThirtyKAndOver ";
        $sql .= " ,   SameFfTcFeeUnderThirtyK = :SameFfTcFeeUnderThirtyK ";
        $sql .= " ,   OtherFfTcFeeThirtyKAndOver = :OtherFfTcFeeThirtyKAndOver ";
        $sql .= " ,   OtherFfTcFeeUnderThirtyK = :OtherFfTcFeeUnderThirtyK ";
        $sql .= " ,   TimemachineNgFlg = :TimemachineNgFlg ";
        $sql .= " ,   RecordClaimPrintedDateFlg = :RecordClaimPrintedDateFlg ";
        $sql .= " ,   FixedLengthFlg = :FixedLengthFlg ";
        $sql .= " ,   ConsignorCode = :ConsignorCode ";
        $sql .= " ,   ConsignorName = :ConsignorName ";
        $sql .= " ,   RemittingBankCode = :RemittingBankCode ";
        $sql .= " ,   RemittingBankName = :RemittingBankName ";
        $sql .= " ,   RemittingBranchCode = :RemittingBranchCode ";
        $sql .= " ,   RemittingBranchName = :RemittingBranchName ";
        $sql .= " ,   AccountClass = :AccountClass ";
        $sql .= " ,   AccountNumber = :AccountNumber ";
        $sql .= " ,   DspTaxFlg = :DspTaxFlg ";
        $sql .= " ,   AccOemClass = :AccOemClass ";
        $sql .= " ,   SettlementFeeRatePlan = :SettlementFeeRatePlan ";
        $sql .= " ,   EntMonthlyFeePlan = :EntMonthlyFeePlan ";
        $sql .= " ,   AddTcClass = :AddTcClass ";
        $sql .= " ,   StyleSheets = :StyleSheets ";
        $sql .= " ,   ChangeIssuerNameFlg = :ChangeIssuerNameFlg ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   FirstCreditTransferClaimFeeOem = :FirstCreditTransferClaimFeeOem ";
        $sql .= " ,   FirstCreditTransferClaimFeeWebOem = :FirstCreditTransferClaimFeeWebOem ";
        $sql .= " ,   CreditTransferClaimFeeOem = :CreditTransferClaimFeeOem ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " WHERE OemId  = :OemId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $eid,
                ':ApplicationDate' => $row['ApplicationDate'],
                ':ServiceInDate' => $row['ServiceInDate'],
                ':RegistDate' => $row['RegistDate'],
                ':OemNameKj' => $row['OemNameKj'],
                ':OemNameKn' => $row['OemNameKn'],
                ':PostalCode' => $row['PostalCode'],
                ':PrefectureCode' => $row['PrefectureCode'],
                ':PrefectureName' => $row['PrefectureName'],
                ':City' => $row['City'],
                ':Town' => $row['Town'],
                ':Building' => $row['Building'],
                ':RepNameKj' => $row['RepNameKj'],
                ':RepNameKn' => $row['RepNameKn'],
                ':Phone' => $row['Phone'],
                ':Fax' => $row['Fax'],
                ':MonthlyFee' => $row['MonthlyFee'],
                ':N_MonthlyFee' => $row['N_MonthlyFee'],
                ':SettlementFeeRateRKF' => $row['SettlementFeeRateRKF'],
                ':SettlementFeeRateSTD' => $row['SettlementFeeRateSTD'],
                ':SettlementFeeRateEXP' => $row['SettlementFeeRateEXP'],
                ':SettlementFeeRateSPC' => $row['SettlementFeeRateSPC'],
                ':ClaimFeeBS' => $row['ClaimFeeBS'],
                ':ClaimFeeDK' => $row['ClaimFeeDK'],
                ':EntMonthlyFeeRKF' => $row['EntMonthlyFeeRKF'],
                ':EntMonthlyFeeSTD' => $row['EntMonthlyFeeSTD'],
                ':EntMonthlyFeeEXP' => $row['EntMonthlyFeeEXP'],
                ':EntMonthlyFeeSPC' => $row['EntMonthlyFeeSPC'],
                ':OpDkInitFeeRate' => $row['OpDkInitFeeRate'],
                ':OpDkMonthlyFeeRate' => $row['OpDkMonthlyFeeRate'],
                ':OpApiRegOrdMonthlyFeeRate' => $row['OpApiRegOrdMonthlyFeeRate'],
                ':OpApiAllInitFeeRate' => $row['OpApiAllInitFeeRate'],
                ':OpApiAllMonthlyFeeRate' => $row['OpApiAllMonthlyFeeRate'],
                ':Salesman' => $row['Salesman'],
                ':FfName' => $row['FfName'],
                ':FfCode' => $row['FfCode'],
                ':FfBranchName' => $row['FfBranchName'],
                ':FfBranchCode' => $row['FfBranchCode'],
                ':FfAccountNumber' => $row['FfAccountNumber'],
                ':FfAccountClass' => $row['FfAccountClass'],
                ':FfAccountName' => $row['FfAccountName'],
                ':TcClass' => $row['TcClass'],
                ':CpNameKj' => $row['CpNameKj'],
                ':CpNameKn' => $row['CpNameKn'],
                ':DivisionName' => $row['DivisionName'],
                ':MailAddress' => $row['MailAddress'],
                ':ContactPhoneNumber' => $row['ContactPhoneNumber'],
                ':ContactFaxNumber' => $row['ContactFaxNumber'],
                ':Note' => $row['Note'],
                ':ValidFlg' => $row['ValidFlg'],
                ':InvalidatedDate' => $row['InvalidatedDate'],
                ':InvalidatedReason' => $row['InvalidatedReason'],
                ':KisanbiDelayDays' => $row['KisanbiDelayDays'],
                ':AccessId' => $row['AccessId'],
                ':EntLoginIdPrefix' => $row['EntLoginIdPrefix'],
                ':OrderIdPrefix' => $row['OrderIdPrefix'],
                ':Notice' => $row['Notice'],
                ':ServiceName' => $row['ServiceName'],
                ':ServicePhone' => $row['ServicePhone'],
                ':SupportTime' => $row['SupportTime'],
                ':SupportMail' => $row['SupportMail'],
                ':Copyright' => $row['Copyright'],
                ':LargeLogo' => $row['LargeLogo'],
                ':SmallLogo' => $row['SmallLogo'],
                ':Imprint' => $row['Imprint'],
                ':PayingMethod' => $row['PayingMethod'],
                ':HelpUrl' => $row['HelpUrl'],
                ':FixPattern' => $row['FixPattern'],
                ':ReclaimAccountPolicy' => $row['ReclaimAccountPolicy'],
                ':FavIcon' => $row['FavIcon'],
                ':FavIconType' => $row['FavIconType'],
                ':EntAccountEditLimitation' => $row['EntAccountEditLimitation'],
                ':EntAccountAdditionalMessage' => $row['EntAccountAdditionalMessage'],
                ':CreditCriterion' => $row['CreditCriterion'],
                ':AutoCreditDateFrom' => $row['AutoCreditDateFrom'],
                ':AutoCreditDateTo' => $row['AutoCreditDateTo'],
                ':AutoCreditCriterion' => $row['AutoCreditCriterion'],
                ':PrintEntOrderIdOnClaimFlg' => $row['PrintEntOrderIdOnClaimFlg'],
                ':DamageInterestRate' => $row['DamageInterestRate'],
                ':OemClaimTransDays' => $row['OemClaimTransDays'],
                ':OemClaimTransFlg' => $row['OemClaimTransFlg'],
                ':OemFixedPattern' => $row['OemFixedPattern'],
                ':OemFixedDay1' => $row['OemFixedDay1'],
                ':OemFixedDay2' => $row['OemFixedDay2'],
                ':OemFixedDay3' => $row['OemFixedDay3'],
                ':OemFixedDay_Week' => $row['OemFixedDay_Week'],
                ':SettlementDay1' => $row['SettlementDay1'],
                ':SettlementDay2' => $row['SettlementDay2'],
                ':SettlementDay3' => $row['SettlementDay3'],
                ':SettlementDay_Week' => $row['SettlementDay_Week'],
                ':AutoCreditLimitAmount' => $row['AutoCreditLimitAmount'],
                ':JapanPostPrintFlg' => $row['JapanPostPrintFlg'],
                ':MembershipAgreement' => $row['MembershipAgreement'],
                ':B_OemFixedDate' => $row['B_OemFixedDate'],
                ':B_SettlementDate' => $row['B_SettlementDate'],
                ':N_OemFixedDate' => $row['N_OemFixedDate'],
                ':N_SettlementDate' => $row['N_SettlementDate'],
                ':SameFfTcFeeThirtyKAndOver' => $row['SameFfTcFeeThirtyKAndOver'],
                ':SameFfTcFeeUnderThirtyK' => $row['SameFfTcFeeUnderThirtyK'],
                ':OtherFfTcFeeThirtyKAndOver' => $row['OtherFfTcFeeThirtyKAndOver'],
                ':OtherFfTcFeeUnderThirtyK' => $row['OtherFfTcFeeUnderThirtyK'],
                ':TimemachineNgFlg' => $row['TimemachineNgFlg'],
                ':RecordClaimPrintedDateFlg' => $row['RecordClaimPrintedDateFlg'],
                ':FixedLengthFlg' => $row['FixedLengthFlg'],
                ':ConsignorCode' => $row['ConsignorCode'],
                ':ConsignorName' => $row['ConsignorName'],
                ':RemittingBankCode' => $row['RemittingBankCode'],
                ':RemittingBankName' => $row['RemittingBankName'],
                ':RemittingBranchCode' => $row['RemittingBranchCode'],
                ':RemittingBranchName' => $row['RemittingBranchName'],
                ':AccountClass' => $row['AccountClass'],
                ':AccountNumber' => $row['AccountNumber'],
                ':DspTaxFlg' => $row['DspTaxFlg'],
                ':AccOemClass' => $row['AccOemClass'],
                ':SettlementFeeRatePlan' => $row['SettlementFeeRatePlan'],
                ':EntMonthlyFeePlan' => $row['EntMonthlyFeePlan'],
                ':AddTcClass' => $row['AddTcClass'],
                ':StyleSheets' => $row['StyleSheets'],
                ':ChangeIssuerNameFlg' => $row['ChangeIssuerNameFlg'],
                ':RegistId' => $row['RegistId'],
                ':FirstCreditTransferClaimFeeOem' => $row['FirstCreditTransferClaimFeeOem'],
                ':FirstCreditTransferClaimFeeWebOem' => $row['FirstCreditTransferClaimFeeWebOem'],
                ':CreditTransferClaimFeeOem' => $row['CreditTransferClaimFeeOem'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
        );

        return $stm->execute($prm);
	}

	/**
	 * すべての有効なOemデータを取得する
	 *
	 * @return ResultInterface
	 */
	public function getAllValidOem()
	{
	    $sql = " SELECT * FROM T_Oem WHERE ValidFlg = 1 ";
	    return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * OEM債権移管対象データを取得する
	 * @return ResultInterface
	 */
	public function getClaimTrans()
	{
	    $sql = " SELECT OemId, OemClaimTransDays FROM T_Oem WHERE OemClaimTransFlg = 1 and OemId <> 0 ";
	    return $this->_adapter->query($sql)->execute(null);
	}
}
