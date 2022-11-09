<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use models\Table\TableBusinessCalendar;
use Coral\Base\BaseUtility;
use Coral\Coral\CoralValidate;
use Coral\Base\BaseGeneralUtils;

/**
 * T_Enterpriseテーブルへのアダプタ
 */
class TableEnterprise
{
	protected $_name = 'T_Enterprise';
	protected $_primary = array('EnterpriseId');
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
	 * すべての事業者データを取得する
	 *
	 * @param bool $asc 昇順ソートの場合はtrueを指定。デフォルトはfalse（＝降順ソート）
	 * @return ResultInterface
	 */
	public function getAllEnterprises($asc = false)
	{
	    $sql = " SELECT * FROM T_Enterprise ORDER BY EnterpriseId " . ($asc ? "asc" : "desc");
        return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 有効なすべての事業者データを取得する。
	 *
	 * @return ResultInterface
	 */
	public function getAllValidEnterprises()
	{
	    $sql = " SELECT * FROM T_Enterprise WHERE ValidFlg = 1 ORDER BY EnterpriseId ";
        return $this->_adapter->query($sql)->execute(null);
	}

	/**
     * OEMに紐づく事業者データ取得
     *
     * @return ResultInterface
     */
    public function getOemValidEnterprises($oem_id)
    {
        $sql = " SELECT * FROM T_Enterprise WHERE ValidFlg = 1 AND IFNULL(OemId, 0) = :OemId ORDER BY EnterpriseId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $oem_id,
        );

        return $stm->execute($prm);
    }

	/**
	 * すべてのOEM事業者データを取得する。
	 *
	 * @param bool $asc 昇順ソートの場合はtrueを指定。デフォルトはfalse（＝降順ソート）
	 * @return ResultInterface
	 */
	public function getAllOemEnterprises($asc = false)
	{
	    $sql = " SELECT * FROM T_Enterprise WHERE IFNULL(OemId, 0) <> 0 ORDER BY EnterpriseId " . ($asc ? "asc" : "desc");
        return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 指定事業者IDの事業者データを取得する。
	 *
	 * @param string $enterpriseId 事業者ID
	 * @return ResultInterface
	 */
	public function findEnterprise($enterpriseId)
	{
        $sql = " SELECT * FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定事業者IDの事業者データを取得する。
	 *
	 * @param string $enterpriseId 事業者ID
	 * @return ResultInterface
	 */
	public function findEnterprise2($enterpriseId)
	{
	    return $this->findEnterprise($enterpriseId);
	}

	/**
	 * 指定OEMIDの事業者データを取得する。
	 *
	 * @param string $oemId OEMID
	 * @return ResultInterface
	 */
	public function findOemEnterprise($oemId, $orderCondition )
	{
	    $sql = " SELECT * FROM T_Enterprise WHERE IFNULL(OemId, 0) = :OemId ";
        if (!is_null($orderCondition)) {
            $sql .= (" ORDER BY " . $orderCondition);
        }

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $oemId,
        );

        return $stm->execute($prm);
	}

// Del By Takemasa(NDC) 20150612 Stt 本実装はSQLを直接発行しT_Siteより取得すること
//     /**
//      * 指定シーケンスのOEM決済手数料（別送）を取得する
//      *
//      * @param int $orderSeq 注文シーケンス
//      * @param int 指定データのOemId
//      */
//     public function getOemClaimFee($eid)
//     {
//         $sql = " SELECT OemClaimFee FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ";
//
//         $stm = $this->_adapter->query($sql);
//
//         $prm = array(
//                 ':EnterpriseId' => $eid,
//         );
//
//         $ri = $stm->execute($prm);
//
//         if (!($ri->count() > 0)) { return null; }
//
//         return (int)$ri->current()['OemClaimFee'];
//     }
// Del By Takemasa(NDC) 20150612 End 本実装はSQLを直接発行しT_Siteより取得すること
    /**
     * 事業者データを取得する
     *
     * @return ResultInterface
     */
    public function fetchAll($ValComb, $orderCondition)
    {
        $sql  = " SELECT * FROM T_Enterprise WHERE " . $ValComb;
        if (!is_null($orderCondition)) {
            $sql .= (" ORDER BY " . $orderCondition);
        }
        return $this->_adapter->query($sql)->execute(null);
    }

    /**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
	    $sql  = " INSERT INTO T_Enterprise (RegistDate, LoginId, LoginPasswd, EnterpriseNameKj, EnterpriseNameKn, PostalCode, PrefectureCode, PrefectureName, City, Town, Building, Phone, Fax, Industry, Plan, MonthlyFee, FfName, FfCode, FfBranchName, FfBranchCode, FfAccountNumber, FfAccountClass, FfAccountName, CpNameKj, CpNameKn, DivisionName, MailAddress, ContactPhoneNumber, Note, B_ChargeFixedDate, B_ChargeDecisionDate, B_ChargeExecDate, N_ChargeFixedDate, N_ChargeDecisionDate, N_ChargeExecDate, ValidFlg, InvalidatedDate, InvalidatedReason, RepNameKj, RepNameKn, PreSales, Salesman, TcClass, ContactFaxNumber, ApplicationDate, PublishingConfirmDate, ServiceInDate, DocCollect, ExaminationResult, N_MonthlyFee, Notice, UnitingAddress, Memo, Special01Flg, SelfBillingMode, SelfBillingKey, AutoCreditJudgeMode, SelfBillingExportAllow, CjMailMode, CombinedClaimMode, AutoClaimStopFlg, UseAmountLimitForCreditJudge, DkInitFee, DkMonthlyFee, ApiRegOrdMonthlyFee, ApiAllInitFee, ApiAllMonthlyFee, N_DkMonthlyFee, N_ApiRegOrdMonthlyFee, N_ApiAllMonthlyFee, OemId, N_OemEntMonthlyFee, N_OemDkMonthlyFee, N_OemApiRegOrdMonthlyFee, N_OemApiAllMonthlyFee, Hashed, OemMonthlyFee, N_OemMonthlyFee, PrintEntOrderIdOnClaimFlg, ClaimIndividualOutputFlg, UserAmountOver, TaxClass, ClaimClass, SystemClass, JudgeSystemFlg, AutoJudgeFlg, JintecFlg, ManualJudgeFlg, CreditNgDispDays, CombinedClaimFlg, AutoCombinedClaimDay, PayBackFlg, JournalRegistDispClass, DispOrder1, DispOrder2, DispOrder3, ClaimOrder1, ClaimOrder2, RegUnitingAddress, RegEnterpriseNameKj, RegCpNameKj, PrintAdjustmentX, PrintAdjustmentY, PayingCycleId, N_PayingCycleId, DamageInterestRate, AutoNoGuaranteeFlg, DispDecimalPoint, UseAmountFractionClass, PrintEntComment, CombinedClaimChargeFeeFlg, CsvRegistClass, CsvRegistErrorClass, ReceiptStatusSearchClass, NN_ChargeFixedDate, NN_ChargeExecDate, B_MonthlyFee, B_OemMonthlyFee, CreditJudgePendingRequest, HideToCbButton, LastPasswordChanged, OrderRevivalDisabled, PayingMail, DetailApiOrderStatusClass, CreditThreadNo, NgAccessCount, NgAccessReferenceDate, HoldBoxFlg, SendMailRequestModifyJournalFlg, ExecStopFlg, LinePayUseFlg, ApiOrderRestTimeOutFlg, CreditTransferFlg, NTTSmartTradeFlg, ChargeClass, TargetListLimit, IndividualSubscriberCodeFlg, SubscriberCode, BillingAgentFlg, IluCooperationFlg, CreditJudgeValidDays, DisplayCount, OrderpageUseFlg, AppFormIssueCond, ForceCancelDatePrintFlg, ForceCancelClaimPattern, ClaimIssueStopFlg, FirstClaimIssueCtlFlg, ReClaimIssueCtlFlg, FirstReClaimLmitDateFlg, SelfBillingPrintedAutoUpdateFlg, MhfCreditTransferDisplayName, ClaimEntCustIdDisplayName, ClaimOrderDateFormat, ClaimPamphletPut, RegistId, UpdateDate, UpdateId) VALUES (";
        $sql .= "   :RegistDate ";
        $sql .= " , :LoginId ";
        $sql .= " , :LoginPasswd ";
        $sql .= " , :EnterpriseNameKj ";
        $sql .= " , :EnterpriseNameKn ";
        $sql .= " , :PostalCode ";
        $sql .= " , :PrefectureCode ";
        $sql .= " , :PrefectureName ";
        $sql .= " , :City ";
        $sql .= " , :Town ";
        $sql .= " , :Building ";
        $sql .= " , :Phone ";
        $sql .= " , :Fax ";
        $sql .= " , :Industry ";
        $sql .= " , :Plan ";
        $sql .= " , :MonthlyFee ";
        $sql .= " , :FfName ";
        $sql .= " , :FfCode ";
        $sql .= " , :FfBranchName ";
        $sql .= " , :FfBranchCode ";
        $sql .= " , :FfAccountNumber ";
        $sql .= " , :FfAccountClass ";
        $sql .= " , :FfAccountName ";
        $sql .= " , :CpNameKj ";
        $sql .= " , :CpNameKn ";
        $sql .= " , :DivisionName ";
        $sql .= " , :MailAddress ";
        $sql .= " , :ContactPhoneNumber ";
        $sql .= " , :Note ";
        $sql .= " , :B_ChargeFixedDate ";
        $sql .= " , :B_ChargeDecisionDate ";
        $sql .= " , :B_ChargeExecDate ";
        $sql .= " , :N_ChargeFixedDate ";
        $sql .= " , :N_ChargeDecisionDate ";
        $sql .= " , :N_ChargeExecDate ";
        $sql .= " , :ValidFlg ";
        $sql .= " , :InvalidatedDate ";
        $sql .= " , :InvalidatedReason ";
        $sql .= " , :RepNameKj ";
        $sql .= " , :RepNameKn ";
        $sql .= " , :PreSales ";
        $sql .= " , :Salesman ";
        $sql .= " , :TcClass ";
        $sql .= " , :ContactFaxNumber ";
        $sql .= " , :ApplicationDate ";
        $sql .= " , :PublishingConfirmDate ";
        $sql .= " , :ServiceInDate ";
        $sql .= " , :DocCollect ";
        $sql .= " , :ExaminationResult ";
        $sql .= " , :N_MonthlyFee ";
        $sql .= " , :Notice ";
        $sql .= " , :UnitingAddress ";
        $sql .= " , :Memo ";
        $sql .= " , :Special01Flg ";
        $sql .= " , :SelfBillingMode ";
        $sql .= " , :SelfBillingKey ";
        $sql .= " , :AutoCreditJudgeMode ";
        $sql .= " , :SelfBillingExportAllow ";
        $sql .= " , :CjMailMode ";
        $sql .= " , :CombinedClaimMode ";
        $sql .= " , :AutoClaimStopFlg ";
        $sql .= " , :UseAmountLimitForCreditJudge ";
        $sql .= " , :DkInitFee ";
        $sql .= " , :DkMonthlyFee ";
        $sql .= " , :ApiRegOrdMonthlyFee ";
        $sql .= " , :ApiAllInitFee ";
        $sql .= " , :ApiAllMonthlyFee ";
        $sql .= " , :N_DkMonthlyFee ";
        $sql .= " , :N_ApiRegOrdMonthlyFee ";
        $sql .= " , :N_ApiAllMonthlyFee ";
        $sql .= " , :OemId ";
        $sql .= " , :N_OemEntMonthlyFee ";
        $sql .= " , :N_OemDkMonthlyFee ";
        $sql .= " , :N_OemApiRegOrdMonthlyFee ";
        $sql .= " , :N_OemApiAllMonthlyFee ";
        $sql .= " , :Hashed ";
        $sql .= " , :OemMonthlyFee ";
        $sql .= " , :N_OemMonthlyFee ";
        $sql .= " , :PrintEntOrderIdOnClaimFlg ";
        $sql .= " , :ClaimIndividualOutputFlg ";
        $sql .= " , :UserAmountOver ";
        $sql .= " , :TaxClass ";
        $sql .= " , :ClaimClass ";
        $sql .= " , :SystemClass ";
        $sql .= " , :JudgeSystemFlg ";
        $sql .= " , :AutoJudgeFlg ";
        $sql .= " , :JintecFlg ";
        $sql .= " , :ManualJudgeFlg ";
        $sql .= " , :CreditNgDispDays ";
        $sql .= " , :CombinedClaimFlg ";
        $sql .= " , :AutoCombinedClaimDay ";
        $sql .= " , :PayBackFlg ";
        $sql .= " , :JournalRegistDispClass ";
        $sql .= " , :DispOrder1 ";
        $sql .= " , :DispOrder2 ";
        $sql .= " , :DispOrder3 ";
        $sql .= " , :ClaimOrder1 ";
        $sql .= " , :ClaimOrder2 ";
        $sql .= " , :RegUnitingAddress ";
        $sql .= " , :RegEnterpriseNameKj ";
        $sql .= " , :RegCpNameKj ";
        $sql .= " , :PrintAdjustmentX ";
        $sql .= " , :PrintAdjustmentY ";
        $sql .= " , :PayingCycleId ";
        $sql .= " , :N_PayingCycleId ";
        $sql .= " , :DamageInterestRate ";
        $sql .= " , :AutoNoGuaranteeFlg ";
        $sql .= " , :DispDecimalPoint ";
        $sql .= " , :UseAmountFractionClass ";
        $sql .= " , :PrintEntComment ";
        $sql .= " , :CombinedClaimChargeFeeFlg ";
        $sql .= " , :CsvRegistClass ";
        $sql .= " , :CsvRegistErrorClass ";
        $sql .= " , :ReceiptStatusSearchClass ";
        $sql .= " , :NN_ChargeFixedDate ";
        $sql .= " , :NN_ChargeExecDate ";
        $sql .= " , :B_MonthlyFee ";
        $sql .= " , :B_OemMonthlyFee ";
        $sql .= " , :CreditJudgePendingRequest ";
        $sql .= " , :HideToCbButton ";
        $sql .= " , :LastPasswordChanged ";
        $sql .= " , :OrderRevivalDisabled ";
        $sql .= " , :PayingMail ";
        $sql .= " , :DetailApiOrderStatusClass ";
        $sql .= " , :CreditThreadNo ";
        $sql .= " , :NgAccessCount ";
        $sql .= " , :NgAccessReferenceDate ";
        $sql .= " , :HoldBoxFlg ";
        $sql .= " , :SendMailRequestModifyJournalFlg ";
        $sql .= " , :ExecStopFlg ";
        $sql .= " , :LinePayUseFlg ";
        $sql .= " , :ApiOrderRestTimeOutFlg ";
        $sql .= " , :CreditTransferFlg ";
        $sql .= " , :NTTSmartTradeFlg ";
        $sql .= " , :ChargeClass ";
        $sql .= " , :TargetListLimit ";
        $sql .= " , :IndividualSubscriberCodeFlg ";
        $sql .= " , :SubscriberCode ";
        $sql .= " , :BillingAgentFlg ";
        $sql .= " , :IluCooperationFlg ";
        $sql .= " , :CreditJudgeValidDays ";
        $sql .= " , :DisplayCount ";
        $sql .= " , :OrderpageUseFlg ";
        $sql .= " , :AppFormIssueCond ";
        $sql .= " , :ForceCancelDatePrintFlg ";
        $sql .= " , :ForceCancelClaimPattern ";
        $sql .= " , :ClaimIssueStopFlg ";
        $sql .= " , :FirstClaimIssueCtlFlg ";
        $sql .= " , :ReClaimIssueCtlFlg ";
        $sql .= " , :FirstReClaimLmitDateFlg ";
        $sql .= " , :SelfBillingPrintedAutoUpdateFlg ";
        $sql .= " , :MhfCreditTransferDisplayName ";
        $sql .= " , :ClaimEntCustIdDisplayName ";
        $sql .= " , :ClaimOrderDateFormat ";
        $sql .= " , :ClaimPamphletPut ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':LoginId' => $data['LoginId'],
                ':LoginPasswd' => $data['LoginPasswd'],
                ':EnterpriseNameKj' => $data['EnterpriseNameKj'],
                ':EnterpriseNameKn' => $data['EnterpriseNameKn'],
                ':PostalCode' => $data['PostalCode'],
                ':PrefectureCode' => $data['PrefectureCode'],
                ':PrefectureName' => $data['PrefectureName'],
                ':City' => $data['City'],
                ':Town' => $data['Town'],
                ':Building' => $data['Building'],
                ':Phone' => $data['Phone'],
                ':Fax' => $data['Fax'],
                ':Industry' => $data['Industry'],
                ':Plan' => $data['Plan'],
                ':MonthlyFee' => $data['MonthlyFee'],
                ':FfName' => $data['FfName'],
                ':FfCode' => $data['FfCode'],
                ':FfBranchName' => $data['FfBranchName'],
                ':FfBranchCode' => $data['FfBranchCode'],
                ':FfAccountNumber' => $data['FfAccountNumber'],
                ':FfAccountClass' => $data['FfAccountClass'],
                ':FfAccountName' => $data['FfAccountName'],
                ':CpNameKj' => $data['CpNameKj'],
                ':CpNameKn' => $data['CpNameKn'],
                ':DivisionName' => $data['DivisionName'],
                ':MailAddress' => $data['MailAddress'],
                ':ContactPhoneNumber' => $data['ContactPhoneNumber'],
                ':Note' => $data['Note'],
                ':B_ChargeFixedDate' => $data['B_ChargeFixedDate'],
                ':B_ChargeDecisionDate' => $data['B_ChargeDecisionDate'],
                ':B_ChargeExecDate' => $data['B_ChargeExecDate'],
                ':N_ChargeFixedDate' => $data['N_ChargeFixedDate'],
                ':N_ChargeDecisionDate' => $data['N_ChargeDecisionDate'],
                ':N_ChargeExecDate' => $data['N_ChargeExecDate'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
                ':InvalidatedDate' => $data['InvalidatedDate'],
                ':InvalidatedReason' => $data['InvalidatedReason'],
                ':RepNameKj' => $data['RepNameKj'],
                ':RepNameKn' => $data['RepNameKn'],
                ':PreSales' => $data['PreSales'],
                ':Salesman' => $data['Salesman'],
                ':TcClass' => $data['TcClass'],
                ':ContactFaxNumber' => $data['ContactFaxNumber'],
                ':ApplicationDate' => $data['ApplicationDate'],
                ':PublishingConfirmDate' => $data['PublishingConfirmDate'],
                ':ServiceInDate' => $data['ServiceInDate'],
                ':DocCollect' => $data['DocCollect'],
                ':ExaminationResult' => $data['ExaminationResult'],
                ':N_MonthlyFee' => $data['N_MonthlyFee'],
                ':Notice' => $data['Notice'],
                ':UnitingAddress' => $data['UnitingAddress'],
                ':Memo' => $data['Memo'],
                ':Special01Flg' => $data['Special01Flg'],
                ':SelfBillingMode' => $data['SelfBillingMode'],
                ':SelfBillingKey' => $data['SelfBillingKey'],
                ':AutoCreditJudgeMode' => $data['AutoCreditJudgeMode'],
                ':SelfBillingExportAllow' => $data['SelfBillingExportAllow'],
                ':CjMailMode' => isset($data['CjMailMode']) ? $data['CjMailMode'] : 0,
                ':CombinedClaimMode' => $data['CombinedClaimMode'],
                ':AutoClaimStopFlg' => $data['AutoClaimStopFlg'],
                ':UseAmountLimitForCreditJudge' => $data['UseAmountLimitForCreditJudge'],
                ':DkInitFee' => $data['DkInitFee'],
                ':DkMonthlyFee' => $data['DkMonthlyFee'],
                ':ApiRegOrdMonthlyFee' => $data['ApiRegOrdMonthlyFee'],
                ':ApiAllInitFee' => $data['ApiAllInitFee'],
                ':ApiAllMonthlyFee' => $data['ApiAllMonthlyFee'],
                ':N_DkMonthlyFee' => $data['N_DkMonthlyFee'],
                ':N_ApiRegOrdMonthlyFee' => $data['N_ApiRegOrdMonthlyFee'],
                ':N_ApiAllMonthlyFee' => $data['N_ApiAllMonthlyFee'],
                ':OemId' => $data['OemId'],
                ':N_OemEntMonthlyFee' => $data['N_OemEntMonthlyFee'],
                ':N_OemDkMonthlyFee' => $data['N_OemDkMonthlyFee'],
                ':N_OemApiRegOrdMonthlyFee' => $data['N_OemApiRegOrdMonthlyFee'],
                ':N_OemApiAllMonthlyFee' => $data['N_OemApiAllMonthlyFee'],
                ':Hashed' => $data['Hashed'],
                ':OemMonthlyFee' => $data['OemMonthlyFee'],
                ':N_OemMonthlyFee' => $data['N_OemMonthlyFee'],
                ':PrintEntOrderIdOnClaimFlg' => isset($data['PrintEntOrderIdOnClaimFlg']) ? $data['PrintEntOrderIdOnClaimFlg'] : 0,
                ':ClaimIndividualOutputFlg' => isset($data['ClaimIndividualOutputFlg']) ? $data['ClaimIndividualOutputFlg'] : 0,
                ':UserAmountOver' => $data['UserAmountOver'],
                ':TaxClass' => isset($data['TaxClass']) ? $data['TaxClass'] : 0,
                ':ClaimClass' => isset($data['ClaimClass']) ? $data['ClaimClass'] : 0,
                ':SystemClass' => isset($data['SystemClass']) ? $data['SystemClass'] : 1,
                ':JudgeSystemFlg' => isset($data['JudgeSystemFlg']) ? $data['JudgeSystemFlg'] : 1,
                ':AutoJudgeFlg' => isset($data['AutoJudgeFlg']) ? $data['AutoJudgeFlg'] : 1,
                ':JintecFlg' => isset($data['JintecFlg']) ? $data['JintecFlg'] : 1,
                ':ManualJudgeFlg' => isset($data['ManualJudgeFlg']) ? $data['ManualJudgeFlg'] : 1,
                ':CreditNgDispDays' => isset($data['CreditNgDispDays']) ? $data['CreditNgDispDays'] : 0,
                ':CombinedClaimFlg' => isset($data['CombinedClaimFlg']) ? $data['CombinedClaimFlg'] : 0,
                ':AutoCombinedClaimDay' => isset($data['AutoCombinedClaimDay']) ? $data['AutoCombinedClaimDay'] : 1,
                ':PayBackFlg' => isset($data['PayBackFlg']) ? $data['PayBackFlg'] : 0,
                ':JournalRegistDispClass' => isset($data['JournalRegistDispClass']) ? $data['JournalRegistDispClass'] : 0,
                ':DispOrder1' => isset($data['DispOrder1']) ? $data['DispOrder1'] : 0,
                ':DispOrder2' => isset($data['DispOrder2']) ? $data['DispOrder2'] : 1,
                ':DispOrder3' => isset($data['DispOrder3']) ? $data['DispOrder3'] : 2,
                ':ClaimOrder1' => isset($data['ClaimOrder1']) ? $data['ClaimOrder1'] : 1,
                ':ClaimOrder2' => isset($data['ClaimOrder2']) ? $data['ClaimOrder2'] : 1,
                ':RegUnitingAddress' => $data['RegUnitingAddress'],
                ':RegEnterpriseNameKj' => $data['RegEnterpriseNameKj'],
                ':RegCpNameKj' => $data['RegCpNameKj'],
                ':PrintAdjustmentX' => isset($data['PrintAdjustmentX']) ? $data['PrintAdjustmentX'] : 0,
                ':PrintAdjustmentY' => isset($data['PrintAdjustmentY']) ? $data['PrintAdjustmentY'] : 0,
                ':PayingCycleId' => $data['PayingCycleId'],
                ':N_PayingCycleId' => $data['N_PayingCycleId'],
                ':DamageInterestRate' => $data['DamageInterestRate'],
                ':AutoNoGuaranteeFlg' => isset($data['AutoNoGuaranteeFlg']) ? $data['AutoNoGuaranteeFlg'] : 0,
                ':DispDecimalPoint' => isset($data['DispDecimalPoint']) ? $data['DispDecimalPoint'] : 0,
                ':UseAmountFractionClass' => isset($data['UseAmountFractionClass']) ? $data['UseAmountFractionClass'] : 0,
                ':PrintEntComment' => $data['PrintEntComment'],
                ':CombinedClaimChargeFeeFlg' => isset($data['CombinedClaimChargeFeeFlg']) ? $data['CombinedClaimChargeFeeFlg'] : 0,
                ':CsvRegistClass' => isset($data['CsvRegistClass']) ? $data['CsvRegistClass'] : 0,
                ':CsvRegistErrorClass' => isset($data['CsvRegistErrorClass']) ? $data['CsvRegistErrorClass'] : 0,
                ':ReceiptStatusSearchClass' => isset($data['ReceiptStatusSearchClass']) ? $data['ReceiptStatusSearchClass'] : 0,
                ':NN_ChargeFixedDate' => $data['NN_ChargeFixedDate'],
                ':NN_ChargeExecDate' => $data['NN_ChargeExecDate'],
                ':B_MonthlyFee' => $data['B_MonthlyFee'],
                ':B_OemMonthlyFee' => $data['B_OemMonthlyFee'],
                ':CreditJudgePendingRequest' => isset($data['CreditJudgePendingRequest']) ? $data['CreditJudgePendingRequest'] : 0,
                ':HideToCbButton' => isset($data['HideToCbButton']) ? $data['HideToCbButton'] : 0,
                ':LastPasswordChanged' => $data['LastPasswordChanged'],
                ':OrderRevivalDisabled' => isset($data['OrderRevivalDisabled']) ? $data['OrderRevivalDisabled'] : 0,
                ':PayingMail' => isset($data['PayingMail']) ? $data['PayingMail'] : 0,
                ':DetailApiOrderStatusClass' => isset($data['DetailApiOrderStatusClass']) ? $data['DetailApiOrderStatusClass'] : 0,
                ':CreditThreadNo' => isset($data['CreditThreadNo']) ? $data['CreditThreadNo'] : 3,
                ':NgAccessCount' => isset($data['NgAccessCount']) ? $data['NgAccessCount'] : 0,
                ':NgAccessReferenceDate' => $data['NgAccessReferenceDate'],
                ':HoldBoxFlg' => isset($data['HoldBoxFlg']) ? $data['HoldBoxFlg'] : 0,
                ':SendMailRequestModifyJournalFlg' => isset($data['SendMailRequestModifyJournalFlg']) ? $data['SendMailRequestModifyJournalFlg'] : 1,
                ':ExecStopFlg' => isset($data['ExecStopFlg']) ? $data['ExecStopFlg'] : 0,
                ':LinePayUseFlg' => isset($data['LinePayUseFlg']) ? $data['LinePayUseFlg'] : 0,
                ':ApiOrderRestTimeOutFlg' => isset($data['ApiOrderRestTimeOutFlg']) ? $data['ApiOrderRestTimeOutFlg'] : 0,
                ':CreditTransferFlg' => isset($data['CreditTransferFlg']) ? $data['CreditTransferFlg'] : 0,
                ':NTTSmartTradeFlg' => isset($data['NTTSmartTradeFlg']) ? $data['NTTSmartTradeFlg'] : 0,
                ':ChargeClass' => isset($data['ChargeClass']) ? $data['ChargeClass'] : 0,
                ':TargetListLimit' => isset($data['TargetListLimit']) ? $data['TargetListLimit'] : 0,
                ':IndividualSubscriberCodeFlg' => isset($data['IndividualSubscriberCodeFlg']) ? $data['IndividualSubscriberCodeFlg'] : 0,
                ':SubscriberCode' => $data['SubscriberCode'],
                ':BillingAgentFlg' => isset($data['BillingAgentFlg']) ? $data['BillingAgentFlg'] : 0,
                ':IluCooperationFlg' => isset($data['IluCooperationFlg']) ? $data['IluCooperationFlg'] : 1,
                ':CreditJudgeValidDays' => isset($data['CreditJudgeValidDays']) ? $data['CreditJudgeValidDays'] : 30,
                ':DisplayCount' => isset($data['DisplayCount']) ? $data['DisplayCount'] : 0,
                ':OrderpageUseFlg' => isset($data['OrderpageUseFlg']) ? $data['OrderpageUseFlg'] : 1,
                ':AppFormIssueCond' => isset($data['AppFormIssueCond']) ? $data['AppFormIssueCond'] : 2,
                ':ForceCancelDatePrintFlg' => isset($data['ForceCancelDatePrintFlg']) ? $data['ForceCancelDatePrintFlg'] : 0,
                ':ForceCancelClaimPattern' => isset($data['ForceCancelClaimPattern']) ? $data['ForceCancelClaimPattern'] : null,
                ':ClaimIssueStopFlg' => isset($data['ClaimIssueStopFlg']) ? $data['ClaimIssueStopFlg'] : 0,
                ':FirstClaimIssueCtlFlg' => isset($data['FirstClaimIssueCtlFlg']) ? $data['FirstClaimIssueCtlFlg'] : 0,
                ':ReClaimIssueCtlFlg' => isset($data['ReClaimIssueCtlFlg']) ? $data['ReClaimIssueCtlFlg'] : 0,
                ':FirstReClaimLmitDateFlg' => isset($data['FirstReClaimLmitDateFlg']) ? $data['FirstReClaimLmitDateFlg'] : 0,
                ':SelfBillingPrintedAutoUpdateFlg' => isset($data['SelfBillingPrintedAutoUpdateFlg']) ? $data['SelfBillingPrintedAutoUpdateFlg'] : 0,
                ':MhfCreditTransferDisplayName' => isset($data['MhfCreditTransferDisplayName']) ? $data['MhfCreditTransferDisplayName'] : null,
                ':ClaimEntCustIdDisplayName' => isset($data['ClaimEntCustIdDisplayName']) ? $data['ClaimEntCustIdDisplayName'] : null,
                ':ClaimOrderDateFormat' => isset($data['ClaimOrderDateFormat']) ? $data['ClaimOrderDateFormat'] : 0,
                ':ClaimPamphletPut' => isset($data['ClaimPamphletPut']) ? $data['ClaimPamphletPut'] : 0,
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
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
	 * @param int $eid 更新するEnterpriseId
	 */
	public function saveUpdate($data, $eid)
	{
	    $sql = " SELECT * FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $eid,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_Enterprise ";
        $sql .= " SET ";
        $sql .= "     RegistDate = :RegistDate ";
        $sql .= " ,   LoginId = :LoginId ";
        $sql .= " ,   LoginPasswd = :LoginPasswd ";
        $sql .= " ,   EnterpriseNameKj = :EnterpriseNameKj ";
        $sql .= " ,   EnterpriseNameKn = :EnterpriseNameKn ";
        $sql .= " ,   PostalCode = :PostalCode ";
        $sql .= " ,   PrefectureCode = :PrefectureCode ";
        $sql .= " ,   PrefectureName = :PrefectureName ";
        $sql .= " ,   City = :City ";
        $sql .= " ,   Town = :Town ";
        $sql .= " ,   Building = :Building ";
        $sql .= " ,   Phone = :Phone ";
        $sql .= " ,   Fax = :Fax ";
        $sql .= " ,   Industry = :Industry ";
        $sql .= " ,   Plan = :Plan ";
        $sql .= " ,   MonthlyFee = :MonthlyFee ";
        $sql .= " ,   FfName = :FfName ";
        $sql .= " ,   FfCode = :FfCode ";
        $sql .= " ,   FfBranchName = :FfBranchName ";
        $sql .= " ,   FfBranchCode = :FfBranchCode ";
        $sql .= " ,   FfAccountNumber = :FfAccountNumber ";
        $sql .= " ,   FfAccountClass = :FfAccountClass ";
        $sql .= " ,   FfAccountName = :FfAccountName ";
        $sql .= " ,   CpNameKj = :CpNameKj ";
        $sql .= " ,   CpNameKn = :CpNameKn ";
        $sql .= " ,   DivisionName = :DivisionName ";
        $sql .= " ,   MailAddress = :MailAddress ";
        $sql .= " ,   ContactPhoneNumber = :ContactPhoneNumber ";
        $sql .= " ,   Note = :Note ";
        $sql .= " ,   B_ChargeFixedDate = :B_ChargeFixedDate ";
        $sql .= " ,   B_ChargeDecisionDate = :B_ChargeDecisionDate ";
        $sql .= " ,   B_ChargeExecDate = :B_ChargeExecDate ";
        $sql .= " ,   N_ChargeFixedDate = :N_ChargeFixedDate ";
        $sql .= " ,   N_ChargeDecisionDate = :N_ChargeDecisionDate ";
        $sql .= " ,   N_ChargeExecDate = :N_ChargeExecDate ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " ,   InvalidatedDate = :InvalidatedDate ";
        $sql .= " ,   InvalidatedReason = :InvalidatedReason ";
        $sql .= " ,   RepNameKj = :RepNameKj ";
        $sql .= " ,   RepNameKn = :RepNameKn ";
        $sql .= " ,   PreSales = :PreSales ";
        $sql .= " ,   Salesman = :Salesman ";
        $sql .= " ,   TcClass = :TcClass ";
        $sql .= " ,   ContactFaxNumber = :ContactFaxNumber ";
        $sql .= " ,   ApplicationDate = :ApplicationDate ";
        $sql .= " ,   PublishingConfirmDate = :PublishingConfirmDate ";
        $sql .= " ,   ServiceInDate = :ServiceInDate ";
        $sql .= " ,   DocCollect = :DocCollect ";
        $sql .= " ,   ExaminationResult = :ExaminationResult ";
        $sql .= " ,   N_MonthlyFee = :N_MonthlyFee ";
        $sql .= " ,   Notice = :Notice ";
        $sql .= " ,   UnitingAddress = :UnitingAddress ";
        $sql .= " ,   Memo = :Memo ";
        $sql .= " ,   Special01Flg = :Special01Flg ";
        $sql .= " ,   SelfBillingMode = :SelfBillingMode ";
        $sql .= " ,   SelfBillingKey = :SelfBillingKey ";
        $sql .= " ,   AutoCreditJudgeMode = :AutoCreditJudgeMode ";
        $sql .= " ,   SelfBillingExportAllow = :SelfBillingExportAllow ";
        $sql .= " ,   CjMailMode = :CjMailMode ";
        $sql .= " ,   CombinedClaimMode = :CombinedClaimMode ";
        $sql .= " ,   AutoClaimStopFlg = :AutoClaimStopFlg ";
        $sql .= " ,   UseAmountLimitForCreditJudge = :UseAmountLimitForCreditJudge ";
        $sql .= " ,   DkInitFee = :DkInitFee ";
        $sql .= " ,   DkMonthlyFee = :DkMonthlyFee ";
        $sql .= " ,   ApiRegOrdMonthlyFee = :ApiRegOrdMonthlyFee ";
        $sql .= " ,   ApiAllInitFee = :ApiAllInitFee ";
        $sql .= " ,   ApiAllMonthlyFee = :ApiAllMonthlyFee ";
        $sql .= " ,   N_DkMonthlyFee = :N_DkMonthlyFee ";
        $sql .= " ,   N_ApiRegOrdMonthlyFee = :N_ApiRegOrdMonthlyFee ";
        $sql .= " ,   N_ApiAllMonthlyFee = :N_ApiAllMonthlyFee ";
        $sql .= " ,   OemId = :OemId ";
        $sql .= " ,   N_OemEntMonthlyFee = :N_OemEntMonthlyFee ";
        $sql .= " ,   N_OemDkMonthlyFee = :N_OemDkMonthlyFee ";
        $sql .= " ,   N_OemApiRegOrdMonthlyFee = :N_OemApiRegOrdMonthlyFee ";
        $sql .= " ,   N_OemApiAllMonthlyFee = :N_OemApiAllMonthlyFee ";
        $sql .= " ,   Hashed = :Hashed ";
        $sql .= " ,   OemMonthlyFee = :OemMonthlyFee ";
        $sql .= " ,   N_OemMonthlyFee = :N_OemMonthlyFee ";
        $sql .= " ,   PrintEntOrderIdOnClaimFlg = :PrintEntOrderIdOnClaimFlg ";
        $sql .= " ,   ClaimIndividualOutputFlg = :ClaimIndividualOutputFlg ";
        $sql .= " ,   UserAmountOver = :UserAmountOver ";
        $sql .= " ,   TaxClass = :TaxClass ";
        $sql .= " ,   ClaimClass = :ClaimClass ";
        $sql .= " ,   SystemClass = :SystemClass ";
        $sql .= " ,   JudgeSystemFlg = :JudgeSystemFlg ";
        $sql .= " ,   AutoJudgeFlg = :AutoJudgeFlg ";
        $sql .= " ,   JintecFlg = :JintecFlg ";
        $sql .= " ,   ManualJudgeFlg = :ManualJudgeFlg ";
        $sql .= " ,   CreditNgDispDays = :CreditNgDispDays ";
        $sql .= " ,   CombinedClaimFlg = :CombinedClaimFlg ";
        $sql .= " ,   AutoCombinedClaimDay = :AutoCombinedClaimDay ";
        $sql .= " ,   PayBackFlg = :PayBackFlg ";
        $sql .= " ,   JournalRegistDispClass = :JournalRegistDispClass ";
        $sql .= " ,   DispOrder1 = :DispOrder1 ";
        $sql .= " ,   DispOrder2 = :DispOrder2 ";
        $sql .= " ,   DispOrder3 = :DispOrder3 ";
        $sql .= " ,   ClaimOrder1 = :ClaimOrder1 ";
        $sql .= " ,   ClaimOrder2 = :ClaimOrder2 ";
        $sql .= " ,   RegUnitingAddress = :RegUnitingAddress ";
        $sql .= " ,   RegEnterpriseNameKj = :RegEnterpriseNameKj ";
        $sql .= " ,   RegCpNameKj = :RegCpNameKj ";
        $sql .= " ,   PrintAdjustmentX = :PrintAdjustmentX ";
        $sql .= " ,   PrintAdjustmentY = :PrintAdjustmentY ";
        $sql .= " ,   PayingCycleId = :PayingCycleId ";
        $sql .= " ,   N_PayingCycleId = :N_PayingCycleId ";
        $sql .= " ,   DamageInterestRate = :DamageInterestRate ";
        $sql .= " ,   AutoNoGuaranteeFlg = :AutoNoGuaranteeFlg ";
        $sql .= " ,   DispDecimalPoint = :DispDecimalPoint ";
        $sql .= " ,   UseAmountFractionClass = :UseAmountFractionClass ";
        $sql .= " ,   PrintEntComment = :PrintEntComment ";
        $sql .= " ,   CombinedClaimChargeFeeFlg = :CombinedClaimChargeFeeFlg ";
        $sql .= " ,   CsvRegistClass = :CsvRegistClass ";
        $sql .= " ,   CsvRegistErrorClass = :CsvRegistErrorClass ";
        $sql .= " ,   ReceiptStatusSearchClass = :ReceiptStatusSearchClass ";
        $sql .= " ,   NN_ChargeFixedDate = :NN_ChargeFixedDate ";
        $sql .= " ,   NN_ChargeExecDate = :NN_ChargeExecDate ";
        $sql .= " ,   B_MonthlyFee = :B_MonthlyFee ";
        $sql .= " ,   B_OemMonthlyFee = :B_OemMonthlyFee ";
        $sql .= " ,   CreditJudgePendingRequest = :CreditJudgePendingRequest ";
        $sql .= " ,   HideToCbButton = :HideToCbButton ";
        $sql .= " ,   LastPasswordChanged = :LastPasswordChanged ";
        $sql .= " ,   OrderRevivalDisabled = :OrderRevivalDisabled ";
        $sql .= " ,   PayingMail = :PayingMail ";
        $sql .= " ,   DetailApiOrderStatusClass = :DetailApiOrderStatusClass ";
        $sql .= " ,   CreditThreadNo = :CreditThreadNo ";
        $sql .= " ,   NgAccessCount = :NgAccessCount ";
        $sql .= " ,   NgAccessReferenceDate = :NgAccessReferenceDate ";
        $sql .= " ,   HoldBoxFlg = :HoldBoxFlg ";
        $sql .= " ,   SendMailRequestModifyJournalFlg = :SendMailRequestModifyJournalFlg ";
        $sql .= " ,   ExecStopFlg = :ExecStopFlg ";
        $sql .= " ,   LinePayUseFlg = :LinePayUseFlg ";
        $sql .= " ,   ApiOrderRestTimeOutFlg = :ApiOrderRestTimeOutFlg ";
        $sql .= " ,   CreditTransferFlg = :CreditTransferFlg ";
        $sql .= " ,   NTTSmartTradeFlg = :NTTSmartTradeFlg ";
        $sql .= " ,   ChargeClass = :ChargeClass ";
        $sql .= " ,   TargetListLimit = :TargetListLimit ";
        $sql .= " ,   IndividualSubscriberCodeFlg = :IndividualSubscriberCodeFlg ";
        $sql .= " ,   SubscriberCode = :SubscriberCode ";
        $sql .= " ,   BillingAgentFlg = :BillingAgentFlg ";
        $sql .= " ,   IluCooperationFlg = :IluCooperationFlg ";
        $sql .= " ,   CreditJudgeValidDays = :CreditJudgeValidDays ";
        $sql .= " ,   DisplayCount = :DisplayCount ";
        $sql .= " ,   OrderpageUseFlg = :OrderpageUseFlg ";
        $sql .= " ,   AppFormIssueCond = :AppFormIssueCond ";
        $sql .= " ,   ForceCancelDatePrintFlg = :ForceCancelDatePrintFlg ";
        $sql .= " ,   ForceCancelClaimPattern = :ForceCancelClaimPattern ";
        $sql .= " ,   ClaimIssueStopFlg = :ClaimIssueStopFlg ";
        $sql .= " ,   FirstClaimIssueCtlFlg = :FirstClaimIssueCtlFlg ";
        $sql .= " ,   ReClaimIssueCtlFlg = :ReClaimIssueCtlFlg ";
        $sql .= " ,   FirstReClaimLmitDateFlg = :FirstReClaimLmitDateFlg ";
        $sql .= " ,   SelfBillingPrintedAutoUpdateFlg = :SelfBillingPrintedAutoUpdateFlg ";
        $sql .= " ,   MhfCreditTransferDisplayName = :MhfCreditTransferDisplayName ";
        $sql .= " ,   ClaimEntCustIdDisplayName = :ClaimEntCustIdDisplayName ";
        $sql .= " ,   ClaimOrderDateFormat = :ClaimOrderDateFormat ";
        $sql .= " ,   ClaimPamphletPut = :ClaimPamphletPut ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " WHERE EnterpriseId = :EnterpriseId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $eid,
                ':RegistDate' => $row['RegistDate'],
                ':LoginId' => $row['LoginId'],
                ':LoginPasswd' => $row['LoginPasswd'],
                ':EnterpriseNameKj' => $row['EnterpriseNameKj'],
                ':EnterpriseNameKn' => $row['EnterpriseNameKn'],
                ':PostalCode' => $row['PostalCode'],
                ':PrefectureCode' => $row['PrefectureCode'],
                ':PrefectureName' => $row['PrefectureName'],
                ':City' => $row['City'],
                ':Town' => $row['Town'],
                ':Building' => $row['Building'],
                ':Phone' => $row['Phone'],
                ':Fax' => $row['Fax'],
                ':Industry' => $row['Industry'],
                ':Plan' => $row['Plan'],
                ':MonthlyFee' => $row['MonthlyFee'],
                ':FfName' => $row['FfName'],
                ':FfCode' => $row['FfCode'],
                ':FfBranchName' => $row['FfBranchName'],
                ':FfBranchCode' => $row['FfBranchCode'],
                ':FfAccountNumber' => $row['FfAccountNumber'],
                ':FfAccountClass' => $row['FfAccountClass'],
                ':FfAccountName' => $row['FfAccountName'],
                ':CpNameKj' => $row['CpNameKj'],
                ':CpNameKn' => $row['CpNameKn'],
                ':DivisionName' => $row['DivisionName'],
                ':MailAddress' => $row['MailAddress'],
                ':ContactPhoneNumber' => $row['ContactPhoneNumber'],
                ':Note' => $row['Note'],
                ':B_ChargeFixedDate' => $row['B_ChargeFixedDate'],
                ':B_ChargeDecisionDate' => $row['B_ChargeDecisionDate'],
                ':B_ChargeExecDate' => $row['B_ChargeExecDate'],
                ':N_ChargeFixedDate' => $row['N_ChargeFixedDate'],
                ':N_ChargeDecisionDate' => $row['N_ChargeDecisionDate'],
                ':N_ChargeExecDate' => $row['N_ChargeExecDate'],
                ':ValidFlg' => $row['ValidFlg'],
                ':InvalidatedDate' => $row['InvalidatedDate'],
                ':InvalidatedReason' => $row['InvalidatedReason'],
                ':RepNameKj' => $row['RepNameKj'],
                ':RepNameKn' => $row['RepNameKn'],
                ':PreSales' => $row['PreSales'],
                ':Salesman' => $row['Salesman'],
                ':TcClass' => $row['TcClass'],
                ':ContactFaxNumber' => $row['ContactFaxNumber'],
                ':ApplicationDate' => $row['ApplicationDate'],
                ':PublishingConfirmDate' => $row['PublishingConfirmDate'],
                ':ServiceInDate' => $row['ServiceInDate'],
                ':DocCollect' => $row['DocCollect'],
                ':ExaminationResult' => $row['ExaminationResult'],
                ':N_MonthlyFee' => $row['N_MonthlyFee'],
                ':Notice' => $row['Notice'],
                ':UnitingAddress' => $row['UnitingAddress'],
                ':Memo' => $row['Memo'],
                ':Special01Flg' => $row['Special01Flg'],
                ':SelfBillingMode' => $row['SelfBillingMode'],
                ':SelfBillingKey' => $row['SelfBillingKey'],
                ':AutoCreditJudgeMode' => $row['AutoCreditJudgeMode'],
                ':SelfBillingExportAllow' => $row['SelfBillingExportAllow'],
                ':CjMailMode' => $row['CjMailMode'],
                ':CombinedClaimMode' => $row['CombinedClaimMode'],
                ':AutoClaimStopFlg' => $row['AutoClaimStopFlg'],
                ':UseAmountLimitForCreditJudge' => $row['UseAmountLimitForCreditJudge'],
                ':DkInitFee' => $row['DkInitFee'],
                ':DkMonthlyFee' => $row['DkMonthlyFee'],
                ':ApiRegOrdMonthlyFee' => $row['ApiRegOrdMonthlyFee'],
                ':ApiAllInitFee' => $row['ApiAllInitFee'],
                ':ApiAllMonthlyFee' => $row['ApiAllMonthlyFee'],
                ':N_DkMonthlyFee' => $row['N_DkMonthlyFee'],
                ':N_ApiRegOrdMonthlyFee' => $row['N_ApiRegOrdMonthlyFee'],
                ':N_ApiAllMonthlyFee' => $row['N_ApiAllMonthlyFee'],
                ':OemId' => $row['OemId'],
                ':N_OemEntMonthlyFee' => $row['N_OemEntMonthlyFee'],
                ':N_OemDkMonthlyFee' => $row['N_OemDkMonthlyFee'],
                ':N_OemApiRegOrdMonthlyFee' => $row['N_OemApiRegOrdMonthlyFee'],
                ':N_OemApiAllMonthlyFee' => $row['N_OemApiAllMonthlyFee'],
                ':Hashed' => $row['Hashed'],
                ':OemMonthlyFee' => $row['OemMonthlyFee'],
                ':N_OemMonthlyFee' => $row['N_OemMonthlyFee'],
                ':PrintEntOrderIdOnClaimFlg' => $row['PrintEntOrderIdOnClaimFlg'],
                ':ClaimIndividualOutputFlg' => $row['ClaimIndividualOutputFlg'],
                ':UserAmountOver' => $row['UserAmountOver'],
                ':TaxClass' => $row['TaxClass'],
                ':ClaimClass' => $row['ClaimClass'],
                ':SystemClass' => $row['SystemClass'],
                ':JudgeSystemFlg' => $row['JudgeSystemFlg'],
                ':AutoJudgeFlg' => $row['AutoJudgeFlg'],
                ':JintecFlg' => $row['JintecFlg'],
                ':ManualJudgeFlg' => $row['ManualJudgeFlg'],
                ':CreditNgDispDays' => $row['CreditNgDispDays'],
                ':CombinedClaimFlg' => $row['CombinedClaimFlg'],
                ':AutoCombinedClaimDay' => $row['AutoCombinedClaimDay'],
                ':PayBackFlg' => $row['PayBackFlg'],
                ':JournalRegistDispClass' => $row['JournalRegistDispClass'],
                ':DispOrder1' => $row['DispOrder1'],
                ':DispOrder2' => $row['DispOrder2'],
                ':DispOrder3' => $row['DispOrder3'],
                ':ClaimOrder1' => $row['ClaimOrder1'],
                ':ClaimOrder2' => $row['ClaimOrder2'],
                ':RegUnitingAddress' => $row['RegUnitingAddress'],
                ':RegEnterpriseNameKj' => $row['RegEnterpriseNameKj'],
                ':RegCpNameKj' => $row['RegCpNameKj'],
                ':PrintAdjustmentX' => $row['PrintAdjustmentX'],
                ':PrintAdjustmentY' => $row['PrintAdjustmentY'],
                ':PayingCycleId' => $row['PayingCycleId'],
                ':N_PayingCycleId' => $row['N_PayingCycleId'],
                ':DamageInterestRate' => $row['DamageInterestRate'],
                ':AutoNoGuaranteeFlg' => $row['AutoNoGuaranteeFlg'],
                ':DispDecimalPoint' => $row['DispDecimalPoint'],
                ':UseAmountFractionClass' => $row['UseAmountFractionClass'],
                ':PrintEntComment' => $row['PrintEntComment'],
                ':CombinedClaimChargeFeeFlg' => $row['CombinedClaimChargeFeeFlg'],
                ':CsvRegistClass' => $row['CsvRegistClass'],
                ':CsvRegistErrorClass' => $row['CsvRegistErrorClass'],
                ':ReceiptStatusSearchClass' => $row['ReceiptStatusSearchClass'],
                ':NN_ChargeFixedDate' => $row['NN_ChargeFixedDate'],
                ':NN_ChargeExecDate' => $row['NN_ChargeExecDate'],
                ':B_MonthlyFee' => $row['B_MonthlyFee'],
                ':B_OemMonthlyFee' => $row['B_OemMonthlyFee'],
                ':CreditJudgePendingRequest' => $row['CreditJudgePendingRequest'],
                ':HideToCbButton' => $row['HideToCbButton'],
                ':LastPasswordChanged' => $row['LastPasswordChanged'],
                ':OrderRevivalDisabled' => $row['OrderRevivalDisabled'],
                ':PayingMail' => $row['PayingMail'],
                ':DetailApiOrderStatusClass' => $row['DetailApiOrderStatusClass'],
                ':CreditThreadNo' => $row['CreditThreadNo'],
                ':NgAccessCount' => $row['NgAccessCount'],
                ':NgAccessReferenceDate' => $row['NgAccessReferenceDate'],
                ':HoldBoxFlg' => $row['HoldBoxFlg'],
                ':SendMailRequestModifyJournalFlg' => $row['SendMailRequestModifyJournalFlg'],
                ':ExecStopFlg' => $row['ExecStopFlg'],
                ':LinePayUseFlg' => $row['LinePayUseFlg'],
                ':ApiOrderRestTimeOutFlg' => $row['ApiOrderRestTimeOutFlg'],
                ':CreditTransferFlg' => $row['CreditTransferFlg'],
                ':NTTSmartTradeFlg' => $row['NTTSmartTradeFlg'],
                ':ChargeClass' => $row['ChargeClass'],
                ':TargetListLimit' => $row['TargetListLimit'],
                ':IndividualSubscriberCodeFlg' => $row['IndividualSubscriberCodeFlg'],
                ':SubscriberCode' => $row['SubscriberCode'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':BillingAgentFlg' => $row['BillingAgentFlg'],
                ':IluCooperationFlg' => $row['IluCooperationFlg'],
                ':CreditJudgeValidDays' => $row['CreditJudgeValidDays'],
                ':DisplayCount' => $row['DisplayCount'],
                ':OrderpageUseFlg' => $row['OrderpageUseFlg'],
                ':AppFormIssueCond' => $row['AppFormIssueCond'],
                ':ForceCancelDatePrintFlg' => $row['ForceCancelDatePrintFlg'],
                ':ForceCancelClaimPattern' => $row['ForceCancelClaimPattern'],
                ':ClaimIssueStopFlg' => $row['ClaimIssueStopFlg'],
                ':FirstClaimIssueCtlFlg' => $row['FirstClaimIssueCtlFlg'],
                ':ReClaimIssueCtlFlg' => $row['ReClaimIssueCtlFlg'],
                ':FirstReClaimLmitDateFlg' => $row['FirstReClaimLmitDateFlg'],
                ':SelfBillingPrintedAutoUpdateFlg' => $row['SelfBillingPrintedAutoUpdateFlg'],
                ':MhfCreditTransferDisplayName' => $row['MhfCreditTransferDisplayName'],
                ':ClaimEntCustIdDisplayName' => $row['ClaimEntCustIdDisplayName'],
                ':ClaimOrderDateFormat' => $row['ClaimOrderDateFormat'],
                ':ClaimPamphletPut' => $row['ClaimPamphletPut'],
        );

        return $stm->execute($prm);
	}

	/**
	 * サービス開始日を記録する。
	 */
	public function serviceIn($eid, $userId)
	{
	    $mdlpc = new TablePayingCycle($this->_adapter);
        $mdlsp = new TableSystemProperty($this->_adapter);
        $mdlat = new ATableEnterprise($this->_adapter);

        $sql = " SELECT * FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ";

        $row = $this->_adapter->query($sql)->execute( array(':EnterpriseId' => $eid) )->current();

        $row2 = $mdlat->find($row['EnterpriseId'])->current();
        $row = array_merge($row, $row2);

        // 今月の日数を算出
        $today = date('Y-m-d');
        $daysOfMonth = date('t');

        $sql  = " UPDATE T_Enterprise e , AT_Enterprise at ";
        $sql .= " SET ";
        $sql .= "     e.ServiceInDate     = :ServiceInDate ";     //サービス開始日
        $sql .= " ,   e.N_ChargeFixedDate = :N_ChargeFixedDate "; // 次回立替締め日
        // 月額固定費の日割りを算出
        $days = BaseGeneralUtils::CalcSpanDays(date('Y-m-d'), $this->getLastDateOfMonth(date('Y-m-d')));
        $monthlyFee                 = (int)($row['MonthlyFee'] / $daysOfMonth * $days);
        $includeMonthlyFee          = (int)($row['IncludeMonthlyFee'] / $daysOfMonth * $days);
        $apiMonthlyFee              = (int)($row['ApiMonthlyFee'] / $daysOfMonth * $days);
        $creditNoticeMonthlyFee     = (int)($row['CreditNoticeMonthlyFee'] / $daysOfMonth * $days);
        $ncreditNoticeMonthlyFee    = (int)($row['NCreditNoticeMonthlyFee'] / $daysOfMonth * $days);
        $reserveMonthlyFee          = (int)($row['ReserveMonthlyFee'] / $daysOfMonth * $days);

        $sql .= " ,   e.N_MonthlyFee      = :N_MonthlyFee ";
        $sql .= " ,   at.N_IncludeMonthlyFee = :N_IncludeMonthlyFee ";
        $sql .= " ,   at.N_ApiMonthlyFee = :N_ApiMonthlyFee ";
        $sql .= " ,   at.N_CreditNoticeMonthlyFee = :N_CreditNoticeMonthlyFee ";
        $sql .= " ,   at.N_NCreditNoticeMonthlyFee = :N_NCreditNoticeMonthlyFee ";
        $sql .= " ,   at.N_ReserveMonthlyFee = :N_ReserveMonthlyFee ";

        // OEMの場合OEM月額固定費の次回OEM請求月額固定費を設定する
        if (!is_null($row['OemId']) && $row['OemId'] != 0) {
            $oemMonthlyFee              = (int)($row['OemMonthlyFee'] / $daysOfMonth * $days);
            $oemIncludeMonthlyFee       = (int)($row['OemIncludeMonthlyFee'] / $daysOfMonth * $days);
            $oemApiMonthlyFee           = (int)($row['OemApiMonthlyFee'] / $daysOfMonth * $days);
            $oemCreditNoticeMonthlyFee  = (int)($row['OemCreditNoticeMonthlyFee'] / $daysOfMonth * $days);
            $oemNCreditNoticeMonthlyFee = (int)($row['OemNCreditNoticeMonthlyFee'] / $daysOfMonth * $days);
            $oemReserveMonthlyFee       = (int)($row['OemReserveMonthlyFee'] / $daysOfMonth * $days);

            $sql .= " ,   e.N_OemMonthlyFee = :N_OemMonthlyFee ";
            $sql .= " ,   at.N_OemIncludeMonthlyFee = :N_OemIncludeMonthlyFee ";
            $sql .= " ,   at.N_OemApiMonthlyFee = :N_OemApiMonthlyFee ";
            $sql .= " ,   at.N_OemCreditNoticeMonthlyFee = :N_OemCreditNoticeMonthlyFee ";
            $sql .= " ,   at.N_OemNCreditNoticeMonthlyFee = :N_OemNCreditNoticeMonthlyFee ";
            $sql .= " ,   at.N_OemReserveMonthlyFee = :N_OemReserveMonthlyFee ";
        }
        $sql .= " ,   e.UpdateDate = :UpdateDate ";
        $sql .= " ,   e.UpdateId = :UpdateId ";
        $sql .= " WHERE e.EnterpriseId    = at.EnterpriseId ";
        $sql .= "   AND e.EnterpriseId    = :EnterpriseId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ServiceInDate' => date('Y-m-d'),
                ':N_ChargeFixedDate' => $mdlpc->getNextFixedDate($eid, date('Y-m-d')),
                ':N_MonthlyFee' => $monthlyFee,
                ':N_IncludeMonthlyFee' => $includeMonthlyFee,
                ':N_ApiMonthlyFee' => $apiMonthlyFee,
                ':N_CreditNoticeMonthlyFee' => $creditNoticeMonthlyFee,
                ':N_NCreditNoticeMonthlyFee' => $ncreditNoticeMonthlyFee,
                ':N_ReserveMonthlyFee' => $reserveMonthlyFee,
                ':EnterpriseId' => $eid,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $userId,
        );
        if (!is_null($row['OemId']) && $row['OemId'] != 0) {
            $prm = array_merge($prm, array(':N_OemMonthlyFee' => $oemMonthlyFee,
                                           ':N_OemIncludeMonthlyFee' => $oemIncludeMonthlyFee,
                                           ':N_OemApiMonthlyFee' => $oemApiMonthlyFee,
                                           ':N_OemCreditNoticeMonthlyFee' => $oemCreditNoticeMonthlyFee,
                                           ':N_OemNCreditNoticeMonthlyFee' => $oemNCreditNoticeMonthlyFee,
                                           ':N_OemReserveMonthlyFee' => $oemReserveMonthlyFee,
                                     ));
        }

        return $stm->execute($prm);
	}

    /**
     * 指定日付の月末日付を取得する
     *
     * @param string $date 基準日付 'yyyy-MM-dd'書式で通知
     * @return string 月末日付 'yyyy-MM-dd'書式で通知
     */
    private function getLastDateOfMonth($date)
    {
        $gessyo = date('Y-m-01', strtotime($date . " +1 month"));
        $result = date('Y-m-d', strtotime($gessyo . " -1 day"));
        return $result;
    }

    /**
     * 立替確定に伴う記録を行う。
     * @param int $eid 事業者ID
     * @param int $chargeMonthlyFeeFlg 月額固定費が課金されたかどうか
     * @param int $userId ユーザID
     */
    public function ChargeFixed($eid, $chargeMonthlyFeeFlg, $userId)
    {
        // 立替サイクルマスタの関数を呼び出して直近の締め日等を取得する。
        $mdlpc = new TablePayingCycle($this->_adapter);
        $mdlat = new ATableEnterprise($this->_adapter);

        // 直近の締め日
        $nextFixedDate = $mdlpc->getNextFixedDate($eid, date('Y-m-d'));

        // 直近の立替日
        $nextTransferDate = $mdlpc->getNextTransferDate($eid, date('Y-m-d'));

        // 直近の締め日の次の締め日
        $nextNextFixedDate = $mdlpc->getNextFixedDate($eid, $nextFixedDate);

        // 直近の立替日の次の立替日
        $nextNextTransferDate = $mdlpc->getNextTransferDate($eid, $nextTransferDate);

        // 加盟店情報を取得
        $sql = " SELECT * FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ";
        $row = $this->_adapter->query($sql)->execute(array( ':EnterpriseId' => $eid ))->current();

        $row2 = $mdlat->find($eid)->current();
        $row = array_merge($row, $row2);

        // T_Enterpriseを更新
        $sql =  " UPDATE T_Enterprise e, AT_Enterprise at ";
        $sql .= " SET ";
        $sql .= "     e.B_ChargeFixedDate     = :B_ChargeFixedDate ";     // 前回立替締め日
        $sql .= " ,   e.B_ChargeDecisionDate  = :B_ChargeDecisionDate ";  // 前回立替確定日
        $sql .= " ,   e.B_ChargeExecDate      = :B_ChargeExecDate ";      // 前回立替実行日
        $sql .= " ,   e.N_ChargeFixedDate     = :N_ChargeFixedDate ";     // 次回立替締め日
        $sql .= " ,   e.N_ChargeExecDate      = :N_ChargeExecDate ";      // 次回立替実行日
        $sql .= " ,   e.NN_ChargeFixedDate    = :NN_ChargeFixedDate ";    // 次々回立替締め日
        $sql .= " ,   e.NN_ChargeExecDate     = :NN_ChargeExecDate ";     // 次々回立替実行日
        $sql .= " ,   e.B_MonthlyFee          = :B_MonthlyFee ";          // 前回月額固定費
        $sql .= " ,   e.B_OemMonthlyFee       = :B_OemMonthlyFee ";       // 前回OEM月額固定費
        // 月額固定費（MonthlyFee）の参照 → 月額固定費が課金されたかどうか（ChargeMonthlyFeeFlg）へ変更
        if ($chargeMonthlyFeeFlg > 0) {
            $sql .= " ,   e.N_MonthlyFee               = :N_MonthlyFee ";      // 次回月額固定費
            $sql .= " ,   at.N_IncludeMonthlyFee       = :N_IncludeMonthlyFee ";
            $sql .= " ,   at.N_ApiMonthlyFee           = :N_ApiMonthlyFee ";
            $sql .= " ,   at.N_CreditNoticeMonthlyFee  = :N_CreditNoticeMonthlyFee ";
            $sql .= " ,   at.N_NCreditNoticeMonthlyFee = :N_NCreditNoticeMonthlyFee ";
            $sql .= " ,   at.N_ReserveMonthlyFee       = :N_ReserveMonthlyFee ";
        }
        $sql .= " ,   e.UpdateDate            = :UpdateDate ";            // 更新日
        $sql .= " ,   e.UpdateId              = :UpdateId ";              // 更新者
        $sql .= " WHERE e.EnterpriseId        = at.EnterpriseId ";
        $sql .= "   AND e.EnterpriseId        = :EnterpriseId ";

        $stm = $this->_adapter->query($sql);

        // パラメータ設定
        $prm = array(
                ':B_ChargeFixedDate' => $row['N_ChargeFixedDate'],
                ':B_ChargeDecisionDate' => date('Y-m-d'),
                ':B_ChargeExecDate' => $row['N_ChargeExecDate'],
                ':N_ChargeFixedDate' => $nextFixedDate,
                ':N_ChargeExecDate' => $nextTransferDate,
                ':NN_ChargeFixedDate' => $nextNextFixedDate,
                ':NN_ChargeExecDate' => $nextNextTransferDate,
                ':B_MonthlyFee' => $row['MonthlyFee'],
                ':B_OemMonthlyFee' => $row['OemMonthlyFee'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $userId,
                ':EnterpriseId' => $eid,
        );
        if ($chargeMonthlyFeeFlg > 0) {
            $prm[':N_MonthlyFee'] = $row['MonthlyFee'];
            $prm[':N_IncludeMonthlyFee'] = $row['IncludeMonthlyFee'];
            $prm[':N_ApiMonthlyFee'] = $row['ApiMonthlyFee'];
            $prm[':N_CreditNoticeMonthlyFee'] = $row['CreditNoticeMonthlyFee'];
            $prm[':N_NCreditNoticeMonthlyFee'] = $row['NCreditNoticeMonthlyFee'];
            $prm[':N_ReserveMonthlyFee'] = $row['ReserveMonthlyFee'];
        }

        return $stm->execute($prm);
	}

// Del By Takemasa(NDC) 20150630 Stt 廃止(未使用化)
// 	/**
// 	 * 立替確定処理が可能な事業所を取得する。
// 	 *
// 	 * @return ResultInterface
// 	 */
// 	public function getChargeTarget()
// 	{
// 	    $sql = " SELECT * FROM T_Enterprise WHERE N_ChargeFixedDate < CURDATE() AND (FixPattern = 5 OR (FixPattern IN (11,101) AND DATEDIFF((SELECT MIN(BusinessDate) FROM T_BusinessCalendar WHERE WeekDay = 5 AND BusinessDate > CURDATE()), N_ChargeFixedDate) >= 6)) AND ValidFlg = 1 ";
//         return $this->_adapter->query($sql)->execute(null);
// 	}
// Del By Takemasa(NDC) 20150630 End 廃止(未使用化)

// Del By Takemasa(NDC) 20150714 Stt 廃止(未使用化)
// 	/**
// 	 * 指定締めパターンの次回締め日の最小値を取得する。
// 	 *
// 	 * @param int $fixPattern 締めパターン
// 	 * @return string 次回締め日 'yyyy-MM-dd'書式で通知
// 	 * @see 最小値を取る意味は、直近でサービス開始された事業者の締め日が混在する可能性があるため。
// 	 */
// 	public function getNextChargeFixedDateMin($fixPattern)
// Del By Takemasa(NDC) 20150714 Stt 廃止(未使用化)

// Del By Takemasa(NDC) 20150514 Stt TableSiteへ移動
// 	/**
// 	 * 今日現在を基準とした初回請求支払期限を取得する。
// 	 *
// 	 * @param int $enterpriseId 事業者ID
// 	 * @param int $normalLimitDays 通常支払期限日数
// 	 * @return string 支払期限日 'yyyy-MM-dd'書式で通知
// 	 */
// 	public function getLimitDate($enterpriseId, $normalLimitDays)
// 	{
// 	    $sql = " SELECT * FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ";
//
//         $stm = $this->_adapter->query($sql);
//
//         $prm = array(
//                 ':EnterpriseId' => $enterpriseId,
//         );
//
//         $enterpriseData = $stm->execute($prm)->current();
//
//         if ($enterpriseData['LimitDatePattern'] == 1) {
//             // 支払期限日は翌月指定日
//             $year = (int)date('Y');
//             $month = (int)date('m');
//             $day = $enterpriseData['LimitDay'];
//
//             $cnt = 5;
//
//             // 翌月
//             $month++;
//             if ($month > 12) {
//                 $year++;
//                 $month = 1;
//             }
//
//             while(!CoralValidate::isDate(sprintf("%04d-%02d-%02d", $year, $month, $day)) && $cnt > 0) {
//                 $day--;
//                 $cnt--;
//             }
//
//             if ($cnt > 0) {
//                 $today = sprintf("%04d-%02d-%02d", $year, $month, $day);
//             }
//             else {
//                 throw new \Exception("支払期限算出エラー：翌月指定日");
//             }
//         }
//         else if ($enterpriseData['LimitDatePattern'] == 2) {
//             // 支払い期限日は当月指定日
//             $year = (int)date('Y');
//             $month = (int)date('m');
//             $day = $enterpriseData['LimitDay'];
//
//             $cnt = 5;
//
//             while(!CoralValidate::isDate(sprintf("%04d-%02d-%02d", $year, $month, $day)) && $cnt > 0) {
//                 $day--;
//                 $cnt--;
//             }
//
//             if ($cnt > 0) {
//                 $today = sprintf("%04d-%02d-%02d", $year, $month, $day);
//             }
//             else {
//                 throw new \Exception("支払期限算出エラー：当月指定日");
//             }
//
//             if (BaseGeneralUtils::CalcSpanDays($today, date('Y-m-d')) > 0) {
//                 // 過去日であればエラーとする。
//                 throw new \Exception("支払期限算出エラー（過去日）：当月指定日");
//             }
//         }
//         else {
//             // 支払期限日は通常
//             $limitDays = $normalLimitDays;
//             // 通常 x 1.5 or 2のバターン追加（2013.10.18 eda）
//             if($enterpriseData['LimitDatePattern'] == 3) {
//                 // 通常 x 1.5
//                 $limitDays = ceil($limitDays * 1.5);
//             }
//             else if($enterpriseData['LimitDatePattern'] == 4) {
//                 // 通常 x 2
//                 $limitDays = ceil($limitDays * 2);
//             }
//             $today = date('Y-m-d', strtotime("+" . $limitDays . " day"));
//         }
//
//         return $today;
// 	}
// Del By Takemasa(NDC) 20150514 End TableSiteへ移動

// 	/**
// 	 * 不払い率を0リセットする。
// 	 */
// 	public function resetNp()
// 	{
// 	    $sql = "
// 			UPDATE
// 			    T_Enterprise
// 			SET
// 			    NpCalcDate = CURDATE(),
// 			    NpMolecule3 = 0,
// 				NpDenominator3 = 0,
// 				NpRate3 = 0,
// 			    NpMoleculeAll = 0,
// 				NpDenominatorAll = 0,
// 				NpRateAll = 0
// 		";

//         return $this->_adapter->query($sql)->execute(null);
// 	}

	/**
	 * 指定事業者の与信通過案件における平均利用額を算出する。
	 * 算出は(NpAverageAmountOK / NpOrderCountOk)によって行われるが、
	 * NoOrderCountOkが0の場合は0と見なされる
	 *
	 * @param int $enterpriseId 事業者ID
	 * @return float 当該事業者の平均利用額
	 */
	public function getUseAmountAverage($enterpriseId) {
	    $ri = $this->findEnterprise($enterpriseId);
        if (!($ri->count() > 0)) {
            throw new \Exception(sprintf('enterprise data not found. EnterpriseId = %s', $enterpriseId));
        }
        $row = $ri->current();
        $avgAmount = (int)$row['NpAverageAmountOk'];
        $count = (int)$row['NpOrderCountOk'];
        $result = ($count != 0) ? ($avgAmount / $count) : 0;

        return $result;
	}

	/**
	 * 指定事業者における、平均利用額から導出される与信自動判定可能基準金額を取得する。
	 *
	 * @param int $enterpriseId 事業者ID
	 * @param null | float $default_rate デフォルトの倍率
	 * @return float 平均利用額由来の与信自動判定可能基準金額
	 */
	public function getCreditJudgePendingThresholdAmount($enterpriseId, $default_rate = null) {
	    // 指定のデフォルト倍率を一時確定
        $rate = ($default_rate !== null) ? (float)$default_rate : 0;

        // 当該事業者の平均利用額を取得
        $base_amount = $this->getUseAmountAverage($enterpriseId);

        // 当該事業者固有の倍率があったらそちらで倍率確定
        $ri = $this->findEnterprise($enterpriseId);
        $row = $ri->current();
        if(isset($row['AverageUnitPriceRate'])) {   //zzz [AverageUnitPriceRate]はT_Siteへ移動されてた(20150422_1050)
            $rate = (float)($row['AverageUnitPriceRate']);
        }

        // 倍率を補正
        if($rate <= 0) $rate = 1;

        // 平均利用額に倍率を掛けて結果を算出
        $result = $base_amount * $rate;

        return $result;
	}

	/**
	 * 指定事業者が使用する事業者ログインID向けのプレフィックスを取得する。
	 * 指定事業者が特定OEMに関連付けられていない場合、このメソッドはnullを返す
	 *
	 * @param int $entId 事業者ID
	 * @return string | null ログインID向けプレフィックス、またはnull
	 */
	public function getLoginIdPrefix($entId)
	{
	    $q = <<<EOQ
SELECT
	o.EntLoginIdPrefix AS prefix
FROM
	T_Enterprise e LEFT OUTER JOIN
	T_Oem o ON o.OemId = IFNULL(e.OemId, 0)
WHERE
	e.EnterpriseId = :EnterpriseId
EOQ;

        $stm = $this->_adapter->query($q);

        $prm = array(
                ':EnterpriseId' => $entId,
        );

        $ri = $stm->execute($prm);

        if ($ri->count() > 0) {
            $row = $ri->current();
            return (strlen($row['prefix']) > 0) ? strtoupper($row['prefix']) : null;
        }
        return null;
	}

	/**
	 * 指定事業者の注文に付与する、注文ID向けのプレフィックスを取得する。
	 * 指定事業者が特定OEMに関連付けられていない場合、このメソッドはnullを返す
	 *
	 * @param int $entId 事業者ID
	 * @return string | null ログインID向けプレフィックス、またはnull
	 */
	public function getOrderIdPrefix($entId)
	{
	    $q = <<<EOQ
SELECT
	o.OrderIdPrefix AS prefix
FROM
	T_Enterprise e LEFT OUTER JOIN
	T_Oem o ON o.OemId = IFNULL(e.OemId, 0)
WHERE
	e.EnterpriseId = :EnterpriseId
EOQ;

        $stm = $this->_adapter->query($q);

        $prm = array(
                ':EnterpriseId' => $entId,
        );

        $ri = $stm->execute($prm);

        if ($ri->count() > 0) {
            $row = $ri->current();
            return (strlen($row['prefix']) > 0) ? strtoupper($row['prefix']) : null;
        }
        return null;
	}

	/**
	 * 指定事業者が所属するOEMの立替方法がOEM立替か否かを取得する。
	 *
	 * @param int $entId 事業者ID
	 * @return bool
	 */
	public function isOemCharge($entId)
	{
	    $q = <<<EOQ
SELECT
    OEM.PayingMethod,
    ENT.EnterpriseId
FROM
    T_Oem OEM INNER JOIN T_Enterprise ENT ON (OEM.OemId = ENT.OemId)
WHERE
    ENT.EnterpriseId = :EnterpriseId
EOQ;

        $stm = $this->_adapter->query($q);

        $prm = array(
                ':EnterpriseId' => $entId,
        );

        $ri = $stm->execute($prm);

        if ($ri->count() > 0) {
            return ($ri->current()['PayingMethod'] == 1) ? true : false;
        }
        return false;
	}

	/**
	 * 指定事業者のオプション利用状況を取得する。
	 * 戻り値は連想配列で、EnterpriseId以外のキーの内容は以下の通り
	 * - UseApi: APIを利用するか否か
	 * - UseSelfBilling: 請求同梱を利用するか否か
	 * - AutoCreditJudgeMode: 自動与信モード（0：自動与信対象／1：与信全部OK／2：通常与信（＝自動与信対象外）／3：APIリアルタイム与信
	 * - CjMailMode: 与信結果メール送信モード（0：送信しない／1：OKのみ送信／2：NGのみ送信／3：OK・NGとも送信）
	 * - CombinedClaimMode: 取りまとめ請求モード（0：取りまとめ対象外／1：事業者単位／2：サイト単位）
	 * - UseAutoClaimStop: 請求自動ストップを利用するか否か
	 * - UsePrintEntOrderIdOnClaim: 請求書に任意注文番号を出力するか否か
	 * - UseAutoJournalInc: 配送伝票自動仮登録を利用するか否か
	 * - UseFirstClaimLayout: 初回請求封書レイアウトを利用するか否か
	 *
	 * @param int $entId 事業者ID
	 * @return array
	 */
	public function getEnterpriseOptionInfo($entId)
	{
	    $entId = (int)$entId;
	    $infos = $this->getAllEnterpriseOptionInfo();
	    return isset($infos[$entId]) ? $infos[$entId] : array();
	}

	/**
	 * 事業者のオプション利用状況を、事業者IDをキーとした一覧で取得する。
	 * 戻り値の配列の各要素は連想配列で、EnterpriseId以外のキーの内容は以下の通り
	 * - UseApi: APIを利用するか否か
	 * - UseSelfBilling: 請求同梱を利用するか否か
	 * - AutoCreditJudgeMode: 自動与信モード（0：自動与信対象／1：与信全部OK／2：通常与信（＝自動与信対象外）／3：APIリアルタイム与信
	 * - CjMailMode: 与信結果メール送信モード（0：送信しない／1：OKのみ送信／2：NGのみ送信／3：OK・NGとも送信）
	 * - CombinedClaimMode: 取りまとめ請求モード（0：取りまとめ対象外／1：事業者単位／2：サイト単位）
	 * - UseAutoClaimStop: 請求自動ストップを利用するか否か
	 * - UsePrintEntOrderIdOnClaim: 請求書に任意注文番号を出力するか否か
	 * - UseAutoJournalInc: 配送伝票自動仮登録を利用するか否か
	 * - UseFirstClaimLayout: 初回請求封書レイアウトを利用するか否か
	 *
	 * @param int $entId 事業者ID
	 * @return array
	 */
	public function getAllEnterpriseOptionInfo()
	{
	    $q = <<<EOQ
SELECT
	ent.EnterpriseId,
	(CASE
	 WHEN (
	 	SELECT COUNT(*)
	 	FROM
	 		T_ApiUser au INNER JOIN
	 		T_ApiUserEnterprise ae ON ae.ApiUserId = au.ApiUserId
	        INNER JOIN T_Site s ON ae.SiteId = s.SiteId
	 	WHERE s.EnterpriseId = ent.EnterpriseId) > 0 THEN 1
	 ELSE 0
	END) AS UseApi,
	(CASE IFNULL(ent.SelfBillingMode, 0)
	 WHEN 1 THEN 1
	 WHEN 11 THEN 1
	 ELSE 0
	END) AS UseSelfBilling,
 	IFNULL(ent.AutoCreditJudgeMode, 0) AS AutoCreditJudgeMode,
 	IFNULL(ent.CjMailMode, 0) AS CjMailMode,
 	IFNULL(ent.CombinedClaimMode, 0) AS CombinedClaimMode,
 	IFNULL(ent.AutoClaimStopFlg, 0) AS UseAutoClaimStop,
 	IFNULL(ent.PrintEntOrderIdOnClaimFlg, 0) AS UsePrintEntOrderIdOnClaim,
 	IFNULL(ent.ClaimIndividualOutputFlg, 0) AS UseClaimIndividualOutputFlg,
 	    IFNULL(sit.AutoJournalIncMode, 0) AS UseAutoJournalInc,
	(CASE
	 WHEN (
	 	SELECT COUNT(*)
	 	FROM T_Site st
	 	WHERE
	 		st.EnterpriseId = ent.EnterpriseId AND
	 		IFNULL(st.FirstClaimLayoutMode, 0) = 1) > 0 THEN 1
	 ELSE 0
	END) AS UseFirstClaimLayout,
	(CASE
		WHEN IFNULL(ent.SelfBillingMode, 0) > 10 THEN 1
		WHEN IFNULL(sit.AutoJournalIncMode, 0) = 1 THEN 1
		ELSE 0
	END) AS CanJournalUpdate
FROM
	T_Enterprise ent LEFT OUTER JOIN (SELECT EnterpriseId, MAX(AutoJournalIncMode) AS AutoJournalIncMode FROM T_Site GROUP BY EnterpriseId) sit ON ent.EnterpriseId = sit.EnterpriseId

ORDER BY
	ent.EnterpriseId
EOQ;

        $result = array();
        $ri = $this->_adapter->query($q)->execute(null);
        foreach ($ri as $row) {
            $result[$row['EnterpriseId']] = $row;
        }

        return $result;
	}

	/**
	 * 加盟店データを取得する
	 *
	 * @param int $enterpriseId 加盟店ID
	 * @return ResultInterface
	 */
	public function find($enterpriseId)
	{
        $sql = " SELECT * FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
        );

        return $stm->execute($prm);
	}

// Del By suzuki_h(NDC) 20150806 Stt サイトに移動しました
// 	/**
// 	 * 指定シーケンスのOEM決済手数料（同梱）を取得する
// 	 *
// 	 * @param int $enterpriseId 加盟店ID
// 	 * @return int | null OEM同梱請求手数料
// 	 */
// 	public function getSelfBillingOemClaimFee($enterpriseId)
// 	{
//         $row = $this->find($enterpriseId)->current();
//         return ($row) ? $row['SelfBillingOemClaimFee'] : null;
// 	}
// Del By suzuki_h(NDC) 20150806 Stt サイトに移動しました

// Del By Takemasa(NDC) 20150612 Stt 適切にパラメタを設定の上[saveNew]を呼出すこと
//     public function createRow(array $data = array())
//     {
// 		$default = array(
// 			'Hashed' => 0,
// 			'PrintEntOrderIdOnClaimFlg' => 0,
// 			'AutoJournalIncMode' => 0,
// 			'AutoJournalDeliMethodId' => 0,
// 			'HideToCbButton' => 0
// 		);
// 		$data = array_merge($default, $data);
// 		return parent::createRow($data);
// 	}
// Del By Takemasa(NDC) 20150612 End 適切にパラメタを設定の上[saveNew]を呼出すこと

	/**
	 * 指定OEMID OR 加盟店IDの事業者データ(締めパタンを含む)を取得する。
	 *
	 * @param string $oemId OEMID
	 * @return ResultInterface
	 */
	public function findOemEnterpriseAndFixPattern($xid, $isEnterpriseId = false, $orderCondition = null)
	{
	    $sql = <<<EOQ
SELECT
	ent.*,
	pc.FixPattern,
    pp.PricePlanName AS PricePlanStr,
    (SELECT KeyContent FROM M_Code WHERE CodeId = 2 AND KeyCode = pc.FixPattern) AS FixPatternStr
FROM
	T_Enterprise ent LEFT OUTER JOIN
    M_PayingCycle pc on pc.PayingCycleId = ent.PayingCycleId
    LEFT OUTER JOIN M_PricePlan pp ON pp.PricePlanId = ent.Plan
WHERE
    1 = 1
EOQ;
	    if ($isEnterpriseId)
	    {
	        $sql .= " AND ent.EnterpriseId = :XId ";
	    } else {
	        $sql .= " AND IFNULL(ent.OemId, 0) = :XId ";
	    }

	    if (!is_null($orderCondition)) {
	        $sql .= " ORDER BY " . $orderCondition;
	    }

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':XId' => $xid,
	    );

	    return $stm->execute($prm);
	}

    /**
     * 立替サイクル変更処理
     */
    public function changePayingCycle($enterpriseId)
    {
        $sql = " SELECT * FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
        );

        $row = $stm->execute($prm)->current();

        $uEntData = array();
        if ($row['N_PayingCycleId'] > 0) {
            // 変更予約状態なので変更する
            $uEntData['PayingCycleId'] = $row['N_PayingCycleId'];
        }

        $this->saveUpdate($uEntData, $enterpriseId);
    }

    /**
     * 指定ログインIDの事業者データを取得する。
     *
     * @param string $loginId ログインID
     * @return ResultInterface
     */
    public function findLoginId($loginId)
    {
        $sql = <<<EOQ
SELECT e.*
FROM   T_Enterprise e
       LEFT OUTER JOIN T_EnterpriseOperator eo ON (eo.EnterpriseId = e.EnterpriseId)
WHERE  e.LoginId = :LoginId
OR     eo.LoginId = :LoginId
EOQ;
        return $this->_adapter->query($sql)->execute(array(':LoginId' => $loginId));
    }
}