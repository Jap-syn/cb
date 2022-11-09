<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Coral\Coral\CoralValidate;
use Coral\Base\BaseGeneralUtils;

/**
 * T_Siteテーブルへのアダプタ
 */
class TableSite
{
	protected $_name = 'T_Site';
	protected $_primary = array('SiteId');
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
	 * 指定事業者のすべてのサイトデータを取得する
	 *
	 * @param int $enterpriseId 事業者ID
	 * @return ResultInterface
	 */
	public function getAll($enterpriseId)
	{
        $sql = " SELECT * FROM T_Site WHERE EnterpriseId = :EnterpriseId ORDER BY SiteId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定事業者のすべてのサイトデータを取得する
	 *
	 * @param int $enterpriseId 事業者ID
	 * @return ResultInterface
	 */
	public function getValidAll($enterpriseId)
	{
        $sql = " SELECT * FROM T_Site WHERE ValidFlg = 1 AND EnterpriseId = :EnterpriseId ORDER BY SiteId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
        );

        return $stm->execute($prm);
	}

    /**
     * Get Sites which has PaymentAfterArrivalFlg = 1
     */
    public function getValidAll4Copy($enterpriseId)
	{
        $sql = " SELECT * FROM T_Site WHERE ValidFlg = 1 AND PaymentAfterArrivalFlg = 1 AND EnterpriseId = :EnterpriseId ORDER BY SiteId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定サイトIDのサイトデータを取得する。
	 *
	 * @param string $siteId サイトID
	 * @return ResultInterface
	 */
	public function findSite($siteId)
	{
        $sql = " SELECT * FROM T_Site WHERE SiteId = :SiteId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SiteId' => $siteId,
        );

        return $stm->execute($prm);
	}

    /**
     * 指定サイトIDのOEMIDを含むサイトデータを取得する。
     *
     * @param string $siteId サイトID
     * @return ResultInterface
     */
    public function findSite2($siteId)
    {
        $sql = " SELECT e.OemId, s.* FROM T_Site s INNER JOIN T_Enterprise e ON (e.EnterpriseId = s.EnterpriseId) WHERE s.SiteId = :SiteId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SiteId' => $siteId,
        );

        return $stm->execute($prm);
        }

	/**
	 * 指定事業者の指定サイト名の情報を取得する
	 *
	 * @param int $enterpriseId 事業者ID
	 * @param string $siteNameKj サイト名
	 * @return ResultInterface サイト情報
	 */
	public function findSiteBySiteName($enterpriseId, $siteNameKj)
	{
        $sql = " SELECT * FROM T_Site WHERE EnterpriseId = :EnterpriseId AND SiteNameKj = :SiteNameKj ORDER BY SiteId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
                ':SiteNameKj' => $siteNameKj,
        );

        return $stm->execute($prm);
	}

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー？
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO T_Site (RegistDate, EnterpriseId, SiteNameKj, SiteNameKn, NickName, Url, ReqMailAddrFlg, ValidFlg, SiteForm, CombinedClaimFlg, OutOfAmendsFlg, FirstClaimLayoutMode, ServiceTargetClass, ClaimOriginalFormat, ClaimMypagePrint, AutoCreditLimitAmount, ClaimJournalClass, SettlementAmountLimit, SettlementFeeRate, ClaimFeeBS, ClaimFeeDK, ReClaimFeeSetting, ReClaimFee, ReClaimFee1, ReClaimFee3, ReClaimFee4, ReClaimFee5, ReClaimFee6, ReClaimFee7, ReClaimFeeStartRegistDate, ReClaimFeeStartDate, FirstCreditTransferClaimFee, FirstCreditTransferClaimFeeWeb, CreditTransferClaimFee, OemSettlementFeeRate, OemClaimFee, SystemFee, CreditCriterion, CreditOrderUseAmount, AutoCreditDateFrom, AutoCreditDateTo, AutoCreditCriterion, AutoClaimStopFlg, SelfBillingFlg, SelfBillingFixFlg, CombinedClaimDate, LimitDatePattern, LimitDay, PayingBackFlg, PayingBackDays, SiteConfDate, CreaditStartMail, CreaditCompMail, ClaimMail, ReceiptMail, CancelMail, AddressMail, SoonPaymentMail, NotPaymentConfMail, CreditResultMail, AutoJournalDeliMethodId, AutoJournalIncMode, SitClass, T_OrderClass, PrintFormDK, PrintFormBS, FirstClaimKisanbiDelayDays, KisanbiDelayDays, RemindStopClass, BarcodeLimitDays, ReceiptAgentId, SubscriberCode, CombinedClaimChargeFeeFlg, YuchoMT, CreditJudgeMethod, AverageUnitPriceRate, SelfBillingOemClaimFee, ClaimDisposeMail, MultiOrderCount, MultiOrderScore, NgChangeFlg, ShowNgReason, MuhoshoChangeDays, JintecManualReqFlg, OutOfTermcheck, Telcheck, Addresscheck, PostalCodecheck, Ent_OrderIdcheck, EtcAutoArrivalFlg, EtcAutoArrivalNumber, JintecJudge, JintecJudge0, JintecJudge1, JintecJudge2, JintecJudge3, JintecJudge4, JintecJudge5, JintecJudge6, JintecJudge7, JintecJudge8, JintecJudge9, JintecJudge10, PaymentAfterArrivalFlg, MerchantId, ServiceId, HashKey, BasicId, BasicPw, ReceiptUsedFlg, ReceiptIssueProviso, SmallLogo, SpecificTransUrl, CSSettlementFeeRate, CSClaimFeeBS, CSClaimFeeDK, ReissueCount, ClaimAutoJournalIncMode, ChatBotFlg, OtherSitesAuthCheckFlg, MufjBarcodeUsedFlg, MufjBarcodeSubscriberCode, RegistId, OemFirstCreditTransferClaimFee, OemFirstCreditTransferClaimFeeWeb, OemCreditTransferClaimFee, UpdateDate, UpdateId) VALUES (";
        $sql .= "   :RegistDate ";
        $sql .= " , :EnterpriseId ";
        $sql .= " , :SiteNameKj ";
        $sql .= " , :SiteNameKn ";
        $sql .= " , :NickName ";
        $sql .= " , :Url ";
        $sql .= " , :ReqMailAddrFlg ";
        $sql .= " , :ValidFlg ";
        $sql .= " , :SiteForm ";
        $sql .= " , :CombinedClaimFlg ";
        $sql .= " , :OutOfAmendsFlg ";
        $sql .= " , :FirstClaimLayoutMode ";
        $sql .= " , :ServiceTargetClass ";
        $sql .= " , :ClaimOriginalFormat ";
        $sql .= " , :ClaimMypagePrint ";
        $sql .= " , :AutoCreditLimitAmount ";
        $sql .= " , :ClaimJournalClass ";
        $sql .= " , :SettlementAmountLimit ";
        $sql .= " , :SettlementFeeRate ";
        $sql .= " , :ClaimFeeBS ";
        $sql .= " , :ClaimFeeDK ";
        $sql .= " , :ReClaimFeeSetting ";
        $sql .= " , :ReClaimFee ";
        $sql .= " , :ReClaimFee1 ";
        $sql .= " , :ReClaimFee3 ";
        $sql .= " , :ReClaimFee4 ";
        $sql .= " , :ReClaimFee5 ";
        $sql .= " , :ReClaimFee6 ";
        $sql .= " , :ReClaimFee7 ";
        $sql .= " , :ReClaimFeeStartRegistDate ";
        $sql .= " , :ReClaimFeeStartDate ";
        $sql .= " , :FirstCreditTransferClaimFee ";
        $sql .= " , :FirstCreditTransferClaimFeeWeb ";
        $sql .= " , :CreditTransferClaimFee ";
        $sql .= " , :OemSettlementFeeRate ";
        $sql .= " , :OemClaimFee ";
        $sql .= " , :SystemFee ";
        $sql .= " , :CreditCriterion ";
        $sql .= " , :CreditOrderUseAmount ";
        $sql .= " , :AutoCreditDateFrom ";
        $sql .= " , :AutoCreditDateTo ";
        $sql .= " , :AutoCreditCriterion ";
        $sql .= " , :AutoClaimStopFlg ";
        $sql .= " , :SelfBillingFlg ";
        $sql .= " , :SelfBillingFixFlg ";
        $sql .= " , :CombinedClaimDate ";
        $sql .= " , :LimitDatePattern ";
        $sql .= " , :LimitDay ";
        $sql .= " , :PayingBackFlg ";
        $sql .= " , :PayingBackDays ";
        $sql .= " , :SiteConfDate ";
        $sql .= " , :CreaditStartMail ";
        $sql .= " , :CreaditCompMail ";
        $sql .= " , :ClaimMail ";
        $sql .= " , :ReceiptMail ";
        $sql .= " , :CancelMail ";
        $sql .= " , :AddressMail ";
        $sql .= " , :SoonPaymentMail ";
        $sql .= " , :NotPaymentConfMail ";
        $sql .= " , :CreditResultMail ";
        $sql .= " , :AutoJournalDeliMethodId ";
        $sql .= " , :AutoJournalIncMode ";
        $sql .= " , :SitClass ";
        $sql .= " , :T_OrderClass ";
        $sql .= " , :PrintFormDK ";
        $sql .= " , :PrintFormBS ";
        $sql .= " , :FirstClaimKisanbiDelayDays ";
        $sql .= " , :KisanbiDelayDays ";
        $sql .= " , :RemindStopClass ";
        $sql .= " , :BarcodeLimitDays ";
        $sql .= " , :ReceiptAgentId ";
        $sql .= " , :SubscriberCode ";
        $sql .= " , :CombinedClaimChargeFeeFlg ";
        $sql .= " , :YuchoMT ";
        $sql .= " , :CreditJudgeMethod ";
        $sql .= " , :AverageUnitPriceRate ";
        $sql .= " , :SelfBillingOemClaimFee ";
        $sql .= " , :ClaimDisposeMail ";
        $sql .= " , :MultiOrderCount ";
        $sql .= " , :MultiOrderScore ";
        $sql .= " , :NgChangeFlg ";
        $sql .= " , :ShowNgReason ";
        $sql .= " , :MuhoshoChangeDays ";
        $sql .= " , :JintecManualReqFlg ";
        $sql .= " , :OutOfTermcheck ";
        $sql .= " , :Telcheck ";
        $sql .= " , :Addresscheck ";
        $sql .= " , :PostalCodecheck ";
        $sql .= " , :Ent_OrderIdcheck ";
        $sql .= " , :EtcAutoArrivalFlg ";
        $sql .= " , :EtcAutoArrivalNumber ";
        $sql .= " , :JintecJudge ";
        $sql .= " , :JintecJudge0 ";
        $sql .= " , :JintecJudge1 ";
        $sql .= " , :JintecJudge2 ";
        $sql .= " , :JintecJudge3 ";
        $sql .= " , :JintecJudge4 ";
        $sql .= " , :JintecJudge5 ";
        $sql .= " , :JintecJudge6 ";
        $sql .= " , :JintecJudge7 ";
        $sql .= " , :JintecJudge8 ";
        $sql .= " , :JintecJudge9 ";
        $sql .= " , :JintecJudge10 ";
        $sql .= " , :PaymentAfterArrivalFlg ";
        $sql .= " , :MerchantId ";
        $sql .= " , :ServiceId ";
        $sql .= " , :HashKey ";
        $sql .= " , :BasicId ";
        $sql .= " , :BasicPw ";
        $sql .= " , :ReceiptUsedFlg ";
        $sql .= " , :ReceiptIssueProviso ";
        $sql .= " , :SmallLogo ";
        $sql .= " , :SpecificTransUrl ";
        $sql .= " , :CSSettlementFeeRate ";
        $sql .= " , :CSClaimFeeBS ";
        $sql .= " , :CSClaimFeeDK ";
        $sql .= " , :ReissueCount ";
        $sql .= " , :ClaimAutoJournalIncMode ";
        $sql .= " , :ChatBotFlg ";
        $sql .= " , :OtherSitesAuthCheckFlg ";
        $sql .= " , :MufjBarcodeUsedFlg ";
        $sql .= " , :MufjBarcodeSubscriberCode ";
        $sql .= " , :RegistId ";
        $sql .= " , :OemFirstCreditTransferClaimFee ";
        $sql .= " , :OemFirstCreditTransferClaimFeeWeb ";
        $sql .= " , :OemCreditTransferClaimFee ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':EnterpriseId' => $data['EnterpriseId'],
                ':SiteNameKj' => $data['SiteNameKj'],
                ':SiteNameKn' => $data['SiteNameKn'],
                ':NickName' => $data['NickName'],
                ':Url' => $data['Url'],
                ':ReqMailAddrFlg' => $data['ReqMailAddrFlg'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
                ':SiteForm' => $data['SiteForm'],
                ':CombinedClaimFlg' => $data['CombinedClaimFlg'],
                ':OutOfAmendsFlg' => $data['OutOfAmendsFlg'],
                ':FirstClaimLayoutMode' => isset($data['FirstClaimLayoutMode']) ? $data['FirstClaimLayoutMode'] : 0,
                ':ServiceTargetClass' => isset($data['ServiceTargetClass']) ? $data['ServiceTargetClass'] : 0,
                ':ClaimOriginalFormat' => isset($data['ClaimOriginalFormat']) ? $data['ClaimOriginalFormat'] : 0,
                ':ClaimMypagePrint' => isset($data['ClaimMypagePrint']) ? $data['ClaimMypagePrint'] : 0,
                ':AutoCreditLimitAmount' => $data['AutoCreditLimitAmount'],
                ':ClaimJournalClass' => isset($data['ClaimJournalClass']) ? $data['ClaimJournalClass'] : 0,
                ':SettlementAmountLimit' => $data['SettlementAmountLimit'],
                ':SettlementFeeRate' => $data['SettlementFeeRate'],
                ':ClaimFeeBS' => $data['ClaimFeeBS'],
                ':ClaimFeeDK' => $data['ClaimFeeDK'],
                ':ReClaimFeeSetting' => isset($data['ReClaimFeeSetting']) ? $data['ReClaimFeeSetting'] : 0,
                ':ReClaimFee' => $data['ReClaimFee'],
                ':ReClaimFee1' => $data['ReClaimFee1'],
                ':ReClaimFee3' => $data['ReClaimFee3'],
                ':ReClaimFee4' => $data['ReClaimFee4'],
                ':ReClaimFee5' => $data['ReClaimFee5'],
                ':ReClaimFee6' => $data['ReClaimFee6'],
                ':ReClaimFee7' => $data['ReClaimFee7'],
                ':ReClaimFeeStartRegistDate' => $data['ReClaimFeeStartRegistDate'],
                ':ReClaimFeeStartDate' => $data['ReClaimFeeStartDate'],
                ':FirstCreditTransferClaimFee' => $data['FirstCreditTransferClaimFee'],
                ':FirstCreditTransferClaimFeeWeb' => $data['FirstCreditTransferClaimFeeWeb'],
                ':CreditTransferClaimFee' => $data['CreditTransferClaimFee'],
                ':OemSettlementFeeRate' => $data['OemSettlementFeeRate'],
                ':OemClaimFee' => $data['OemClaimFee'],
                ':SystemFee' => $data['SystemFee'],
                ':CreditCriterion' => $data['CreditCriterion'],
                ':CreditOrderUseAmount' => $data['CreditOrderUseAmount'],
                ':AutoCreditDateFrom' => $data['AutoCreditDateFrom'],
                ':AutoCreditDateTo' => $data['AutoCreditDateTo'],
                ':AutoCreditCriterion' => isset($data['AutoCreditCriterion']) ? $data['AutoCreditCriterion'] : 0,
                ':AutoClaimStopFlg' => isset($data['AutoClaimStopFlg']) ? $data['AutoClaimStopFlg'] : 0,
                ':SelfBillingFlg' => isset($data['SelfBillingFlg']) ? $data['SelfBillingFlg'] : 0,
                ':SelfBillingFixFlg' => isset($data['SelfBillingFixFlg']) ? $data['SelfBillingFixFlg'] : 0,
                ':CombinedClaimDate' => $data['CombinedClaimDate'],
                ':LimitDatePattern' => $data['LimitDatePattern'],
                ':LimitDay' => $data['LimitDay'],
                ':PayingBackFlg' => isset($data['PayingBackFlg']) ? $data['PayingBackFlg'] : 0,
                ':PayingBackDays' => $data['PayingBackDays'],
                ':SiteConfDate' => $data['SiteConfDate'],
                ':CreaditStartMail' => isset($data['CreaditStartMail']) ? $data['CreaditStartMail'] : 0,
                ':CreaditCompMail' => isset($data['CreaditCompMail']) ? $data['CreaditCompMail'] : 0,
                ':ClaimMail' => isset($data['ClaimMail']) ? $data['ClaimMail'] : 0,
                ':ReceiptMail' => isset($data['ReceiptMail']) ? $data['ReceiptMail'] : 0,
                ':CancelMail' => isset($data['CancelMail']) ? $data['CancelMail'] : 0,
                ':AddressMail' => isset($data['AddressMail']) ? $data['AddressMail'] : 0,
                ':SoonPaymentMail' => isset($data['SoonPaymentMail']) ? $data['SoonPaymentMail'] : 0,
                ':NotPaymentConfMail' => isset($data['NotPaymentConfMail']) ? $data['NotPaymentConfMail'] : 0,
                ':CreditResultMail' => isset($data['CreditResultMail']) ? $data['CreditResultMail'] : 0,
                ':AutoJournalDeliMethodId' => isset($data['AutoJournalDeliMethodId']) ? $data['AutoJournalDeliMethodId'] : 0,
                ':AutoJournalIncMode' => isset($data['AutoJournalIncMode']) ? $data['AutoJournalIncMode'] : 0,
                ':SitClass' => isset($data['SitClass']) ? $data['SitClass'] : 0,
                ':T_OrderClass' => isset($data['T_OrderClass']) ? $data['T_OrderClass'] : 0,
                ':PrintFormDK' => $data['PrintFormDK'],
                ':PrintFormBS' => $data['PrintFormBS'],
                ':FirstClaimKisanbiDelayDays' => isset($data['FirstClaimKisanbiDelayDays']) ? $data['FirstClaimKisanbiDelayDays'] : 0,
                ':KisanbiDelayDays' => isset($data['KisanbiDelayDays']) ? $data['KisanbiDelayDays'] : 0,
                ':RemindStopClass' => isset($data['RemindStopClass']) ? $data['RemindStopClass'] : 0,
                ':BarcodeLimitDays' => isset($data['BarcodeLimitDays']) ? $data['BarcodeLimitDays'] : 0,
        		':ReceiptAgentId' => isset($data['ReceiptAgentId']) ? $data['ReceiptAgentId'] : 0,
        		':SubscriberCode' => $data['SubscriberCode'],
                ':CombinedClaimChargeFeeFlg' => isset($data['CombinedClaimChargeFeeFlg']) ? $data['CombinedClaimChargeFeeFlg'] : 0,
                ':YuchoMT' => isset($data['YuchoMT']) ? $data['YuchoMT'] : 0,
                ':CreditJudgeMethod' => isset($data['CreditJudgeMethod']) ? $data['CreditJudgeMethod'] : 0,
                ':AverageUnitPriceRate' => $data['AverageUnitPriceRate'],
                ':SelfBillingOemClaimFee' => $data['SelfBillingOemClaimFee'],
                ':ClaimDisposeMail' => $data['ClaimDisposeMail'],
                ':MultiOrderCount' => $data['MultiOrderCount'],
                ':MultiOrderScore' => $data['MultiOrderScore'],
                ':NgChangeFlg' => isset($data['NgChangeFlg']) ? $data['NgChangeFlg'] : 0,
                ':ShowNgReason' => isset($data['ShowNgReason']) ? $data['ShowNgReason'] : 0,
                ':MuhoshoChangeDays' => isset($data['MuhoshoChangeDays']) ? $data['MuhoshoChangeDays'] : 7,
                ':JintecManualReqFlg' => isset($data['JintecManualReqFlg']) ? $data['JintecManualReqFlg'] : 0,
                ':OutOfTermcheck' => isset($data['OutOfTermcheck']) ? $data['OutOfTermcheck'] : 0,
                ':Telcheck' => isset($data['Telcheck']) ? $data['Telcheck'] : 0,
                ':Addresscheck' => isset($data['Addresscheck']) ? $data['Addresscheck'] : 0,
                ':PostalCodecheck' => isset($data['PostalCodecheck']) ? $data['PostalCodecheck'] : 0,
                ':Ent_OrderIdcheck' => isset($data['Ent_OrderIdcheck']) ? $data['Ent_OrderIdcheck'] : 0,
                ':EtcAutoArrivalFlg' => isset($data['EtcAutoArrivalFlg']) ? $data['EtcAutoArrivalFlg'] : 0,
                ':EtcAutoArrivalNumber' => $data['EtcAutoArrivalNumber'],
                ':JintecJudge' => isset($data['JintecJudge']) ? $data['JintecJudge'] : 0,
                ':JintecJudge0' => isset($data['JintecJudge0']) ? $data['JintecJudge0'] : 2,
                ':JintecJudge1' => isset($data['JintecJudge1']) ? $data['JintecJudge1'] : 2,
                ':JintecJudge2' => isset($data['JintecJudge2']) ? $data['JintecJudge2'] : 1,
                ':JintecJudge3' => isset($data['JintecJudge3']) ? $data['JintecJudge3'] : 1,
                ':JintecJudge4' => isset($data['JintecJudge4']) ? $data['JintecJudge4'] : 1,
                ':JintecJudge5' => isset($data['JintecJudge5']) ? $data['JintecJudge5'] : 0,
                ':JintecJudge6' => isset($data['JintecJudge6']) ? $data['JintecJudge6'] : 1,
                ':JintecJudge7' => isset($data['JintecJudge7']) ? $data['JintecJudge7'] : 1,
                ':JintecJudge8' => isset($data['JintecJudge8']) ? $data['JintecJudge8'] : 0,
                ':JintecJudge9' => isset($data['JintecJudge9']) ? $data['JintecJudge9'] : 2,
                ':JintecJudge10' => isset($data['JintecJudge10']) ? $data['JintecJudge10'] : 0,
                ':PaymentAfterArrivalFlg' => isset($data['PaymentAfterArrivalFlg']) ? $data['PaymentAfterArrivalFlg'] : 0,
                ':MerchantId' => $data['MerchantId'],
                ':ServiceId' => $data['ServiceId'],
                ':HashKey' => $data['HashKey'],
                ':BasicId' => $data['BasicId'],
                ':BasicPw' => $data['BasicPw'],
                ':ReceiptUsedFlg' => isset($data['ReceiptUsedFlg']) ? $data['ReceiptUsedFlg'] : 0,
                ':ReceiptIssueProviso' => $data['ReceiptIssueProviso'],
                ':SmallLogo' => $data['SmallLogo'],
                ':SpecificTransUrl' => $data['SpecificTransUrl'],
                ':CSSettlementFeeRate' => $data['CSSettlementFeeRate'],
                ':CSClaimFeeBS' => $data['CSClaimFeeBS'],
                ':CSClaimFeeDK' => $data['CSClaimFeeDK'],
                ':ReissueCount' => $data['ReissueCount'],
                ':ClaimAutoJournalIncMode' => isset($data['ClaimAutoJournalIncMode']) ? $data['ClaimAutoJournalIncMode'] : 0,
                ':ChatBotFlg' => isset($data['ChatBotFlg']) ? $data['ChatBotFlg'] : 0,
                ':OtherSitesAuthCheckFlg' => isset($data['OtherSitesAuthCheckFlg']) ? $data['OtherSitesAuthCheckFlg'] : 0 ,
                ':MufjBarcodeUsedFlg' => isset($data['MufjBarcodeUsedFlg']) ? $data['MufjBarcodeUsedFlg'] : 0 ,
                ':MufjBarcodeSubscriberCode' => isset($data['MufjBarcodeSubscriberCode']) ? $data['MufjBarcodeSubscriberCode'] : null ,
                ':RegistId' => $data['RegistId'],
                ':OemFirstCreditTransferClaimFee' => $data['OemFirstCreditTransferClaimFee'],
                ':OemFirstCreditTransferClaimFeeWeb' => $data['OemFirstCreditTransferClaimFeeWeb'],
                ':OemCreditTransferClaimFee' => $data['OemCreditTransferClaimFee'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

	/**
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param unknown_type $eid 更新するEnterpriseId
	 */
	public function saveUpdate($data, $eid)
	{
        $row = $this->findSite($eid)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_Site ";
        $sql .= " SET ";
        $sql .= "     RegistDate = :RegistDate ";
        $sql .= " ,   EnterpriseId = :EnterpriseId ";
        $sql .= " ,   SiteNameKj = :SiteNameKj ";
        $sql .= " ,   SiteNameKn = :SiteNameKn ";
        $sql .= " ,   NickName = :NickName ";
        $sql .= " ,   Url = :Url ";
        $sql .= " ,   ReqMailAddrFlg = :ReqMailAddrFlg ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " ,   SiteForm = :SiteForm ";
        $sql .= " ,   CombinedClaimFlg = :CombinedClaimFlg ";
        $sql .= " ,   OutOfAmendsFlg = :OutOfAmendsFlg ";
        $sql .= " ,   FirstClaimLayoutMode = :FirstClaimLayoutMode ";
        $sql .= " ,   ServiceTargetClass = :ServiceTargetClass ";
        $sql .= " ,   ClaimOriginalFormat = :ClaimOriginalFormat ";
        $sql .= " ,   ClaimMypagePrint = :ClaimMypagePrint ";
        $sql .= " ,   AutoCreditLimitAmount = :AutoCreditLimitAmount ";
        $sql .= " ,   ClaimJournalClass = :ClaimJournalClass ";
        $sql .= " ,   SettlementAmountLimit = :SettlementAmountLimit ";
        $sql .= " ,   SettlementFeeRate = :SettlementFeeRate ";
        $sql .= " ,   ClaimFeeBS = :ClaimFeeBS ";
        $sql .= " ,   ClaimFeeDK = :ClaimFeeDK ";
        $sql .= " ,   ReClaimFeeSetting = :ReClaimFeeSetting ";
        $sql .= " ,   ReClaimFee = :ReClaimFee ";
        $sql .= " ,   ReClaimFee1 = :ReClaimFee1 ";
        $sql .= " ,   ReClaimFee3 = :ReClaimFee3 ";
        $sql .= " ,   ReClaimFee4 = :ReClaimFee4 ";
        $sql .= " ,   ReClaimFee5 = :ReClaimFee5 ";
        $sql .= " ,   ReClaimFee6 = :ReClaimFee6 ";
        $sql .= " ,   ReClaimFee7 = :ReClaimFee7 ";
        $sql .= " ,   ReClaimFeeStartRegistDate = :ReClaimFeeStartRegistDate ";
        $sql .= " ,   ReClaimFeeStartDate = :ReClaimFeeStartDate ";
        $sql .= " ,   FirstCreditTransferClaimFee = :FirstCreditTransferClaimFee ";
        $sql .= " ,   FirstCreditTransferClaimFeeWeb = :FirstCreditTransferClaimFeeWeb ";
        $sql .= " ,   CreditTransferClaimFee = :CreditTransferClaimFee ";
        $sql .= " ,   OemSettlementFeeRate = :OemSettlementFeeRate ";
        $sql .= " ,   OemClaimFee = :OemClaimFee ";
        $sql .= " ,   SystemFee = :SystemFee ";
        $sql .= " ,   CreditCriterion = :CreditCriterion ";
        $sql .= " ,   CreditOrderUseAmount = :CreditOrderUseAmount ";
        $sql .= " ,   AutoCreditDateFrom = :AutoCreditDateFrom ";
        $sql .= " ,   AutoCreditDateTo = :AutoCreditDateTo ";
        $sql .= " ,   AutoCreditCriterion = :AutoCreditCriterion ";
        $sql .= " ,   AutoClaimStopFlg = :AutoClaimStopFlg ";
        $sql .= " ,   SelfBillingFlg = :SelfBillingFlg ";
        $sql .= " ,   SelfBillingFixFlg = :SelfBillingFixFlg ";
        $sql .= " ,   CombinedClaimDate = :CombinedClaimDate ";
        $sql .= " ,   LimitDatePattern = :LimitDatePattern ";
        $sql .= " ,   LimitDay = :LimitDay ";
        $sql .= " ,   PayingBackFlg = :PayingBackFlg ";
        $sql .= " ,   PayingBackDays = :PayingBackDays ";
        $sql .= " ,   SiteConfDate = :SiteConfDate ";
        $sql .= " ,   CreaditStartMail = :CreaditStartMail ";
        $sql .= " ,   CreaditCompMail = :CreaditCompMail ";
        $sql .= " ,   ClaimMail = :ClaimMail ";
        $sql .= " ,   ReceiptMail = :ReceiptMail ";
        $sql .= " ,   CancelMail = :CancelMail ";
        $sql .= " ,   AddressMail = :AddressMail ";
        $sql .= " ,   SoonPaymentMail = :SoonPaymentMail ";
        $sql .= " ,   NotPaymentConfMail = :NotPaymentConfMail ";
        $sql .= " ,   CreditResultMail = :CreditResultMail ";
        $sql .= " ,   AutoJournalDeliMethodId = :AutoJournalDeliMethodId ";
        $sql .= " ,   AutoJournalIncMode = :AutoJournalIncMode ";
        $sql .= " ,   SitClass = :SitClass ";
        $sql .= " ,   T_OrderClass = :T_OrderClass ";
        $sql .= " ,   PrintFormDK = :PrintFormDK ";
        $sql .= " ,   PrintFormBS = :PrintFormBS ";
        $sql .= " ,   FirstClaimKisanbiDelayDays = :FirstClaimKisanbiDelayDays ";
        $sql .= " ,   KisanbiDelayDays = :KisanbiDelayDays ";
        $sql .= " ,   RemindStopClass = :RemindStopClass ";
        $sql .= " ,   BarcodeLimitDays = :BarcodeLimitDays ";
        $sql .= " ,   ReceiptAgentId = :ReceiptAgentId ";
        $sql .= " ,   SubscriberCode = :SubscriberCode ";
        $sql .= " ,   CombinedClaimChargeFeeFlg = :CombinedClaimChargeFeeFlg ";
        $sql .= " ,   YuchoMT = :YuchoMT ";
        $sql .= " ,   CreditJudgeMethod = :CreditJudgeMethod ";
        $sql .= " ,   AverageUnitPriceRate = :AverageUnitPriceRate ";
        $sql .= " ,   SelfBillingOemClaimFee = :SelfBillingOemClaimFee ";
        $sql .= " ,   ClaimDisposeMail = :ClaimDisposeMail ";
        $sql .= " ,   MultiOrderCount = :MultiOrderCount ";
        $sql .= " ,   MultiOrderScore = :MultiOrderScore ";
        $sql .= " ,   NgChangeFlg = :NgChangeFlg ";
        $sql .= " ,   ShowNgReason = :ShowNgReason ";
        $sql .= " ,   MuhoshoChangeDays = :MuhoshoChangeDays ";
        $sql .= " ,   JintecManualReqFlg = :JintecManualReqFlg ";
        $sql .= " ,   OutOfTermcheck = :OutOfTermcheck ";
        $sql .= " ,   Telcheck = :Telcheck ";
        $sql .= " ,   Addresscheck = :Addresscheck ";
        $sql .= " ,   PostalCodecheck = :PostalCodecheck ";
        $sql .= " ,   Ent_OrderIdcheck = :Ent_OrderIdcheck ";
        $sql .= " ,   EtcAutoArrivalFlg = :EtcAutoArrivalFlg ";
        $sql .= " ,   EtcAutoArrivalNumber = :EtcAutoArrivalNumber ";
        $sql .= " ,   JintecJudge = :JintecJudge ";
        $sql .= " ,   JintecJudge0 = :JintecJudge0 ";
        $sql .= " ,   JintecJudge1 = :JintecJudge1 ";
        $sql .= " ,   JintecJudge2 = :JintecJudge2 ";
        $sql .= " ,   JintecJudge3 = :JintecJudge3 ";
        $sql .= " ,   JintecJudge4 = :JintecJudge4 ";
        $sql .= " ,   JintecJudge5 = :JintecJudge5 ";
        $sql .= " ,   JintecJudge6 = :JintecJudge6 ";
        $sql .= " ,   JintecJudge7 = :JintecJudge7 ";
        $sql .= " ,   JintecJudge8 = :JintecJudge8 ";
        $sql .= " ,   JintecJudge9 = :JintecJudge9 ";
        $sql .= " ,   JintecJudge10 = :JintecJudge10 ";
        if (isset($data['PaymentAfterArrivalFlg'])) {
            $sql .= " ,   PaymentAfterArrivalFlg = :PaymentAfterArrivalFlg ";
        }
        $sql .= " ,   MerchantId = :MerchantId ";
        $sql .= " ,   ServiceId = :ServiceId ";
        $sql .= " ,   HashKey = :HashKey ";
        $sql .= " ,   BasicId = :BasicId ";
        $sql .= " ,   BasicPw = :BasicPw ";
        $sql .= " ,   ReceiptUsedFlg = :ReceiptUsedFlg ";
        $sql .= " ,   ReceiptIssueProviso = :ReceiptIssueProviso ";
        $sql .= " ,   SmallLogo = :SmallLogo ";
        $sql .= " ,   SpecificTransUrl = :SpecificTransUrl ";
        $sql .= " ,   CSSettlementFeeRate = :CSSettlementFeeRate ";
        $sql .= " ,   CSClaimFeeBS = :CSClaimFeeBS ";
        $sql .= " ,   CSClaimFeeDK = :CSClaimFeeDK ";
        $sql .= " ,   ReissueCount = :ReissueCount ";
        $sql .= " ,   ClaimAutoJournalIncMode = :ClaimAutoJournalIncMode ";
        $sql .= " ,   ChatBotFlg = :ChatBotFlg ";
        $sql .= " ,   OtherSitesAuthCheckFlg  = :OtherSitesAuthCheckFlg ";
        $sql .= " ,   MufjBarcodeUsedFlg  = :MufjBarcodeUsedFlg ";
        $sql .= " ,   MufjBarcodeSubscriberCode  = :MufjBarcodeSubscriberCode ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   OemFirstCreditTransferClaimFee = :OemFirstCreditTransferClaimFee ";
        $sql .= " ,   OemFirstCreditTransferClaimFeeWeb = :OemFirstCreditTransferClaimFeeWeb ";
        $sql .= " ,   OemCreditTransferClaimFee = :OemCreditTransferClaimFee ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " WHERE SiteId = :SiteId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SiteId' => $eid,
                ':RegistDate' => $row['RegistDate'],
                ':EnterpriseId' => $row['EnterpriseId'],
                ':SiteNameKj' => $row['SiteNameKj'],
                ':SiteNameKn' => $row['SiteNameKn'],
                ':NickName' => $row['NickName'],
                ':Url' => $row['Url'],
                ':ReqMailAddrFlg' => $row['ReqMailAddrFlg'],
                ':ValidFlg' => $row['ValidFlg'],
                ':SiteForm' => $row['SiteForm'],
                ':CombinedClaimFlg' => $row['CombinedClaimFlg'],
                ':OutOfAmendsFlg' => $row['OutOfAmendsFlg'],
                ':FirstClaimLayoutMode' => $row['FirstClaimLayoutMode'],
                ':ServiceTargetClass' => $row['ServiceTargetClass'],
                ':ClaimOriginalFormat' => $row['ClaimOriginalFormat'],
                ':ClaimMypagePrint' => $row['ClaimMypagePrint'],
                ':AutoCreditLimitAmount' => $row['AutoCreditLimitAmount'],
                ':ClaimJournalClass' => $row['ClaimJournalClass'],
                ':SettlementAmountLimit' => $row['SettlementAmountLimit'],
                ':SettlementFeeRate' => $row['SettlementFeeRate'],
                ':ClaimFeeBS' => $row['ClaimFeeBS'],
                ':ClaimFeeDK' => $row['ClaimFeeDK'],
                ':ReClaimFeeSetting' => isset($row['ReClaimFeeSetting']) ? $row['ReClaimFeeSetting'] : 0,
                ':ReClaimFee' => $row['ReClaimFee'],
                ':ReClaimFee1' => $row['ReClaimFee1'],
                ':ReClaimFee3' => $row['ReClaimFee3'],
                ':ReClaimFee4' => $row['ReClaimFee4'],
                ':ReClaimFee5' => $row['ReClaimFee5'],
                ':ReClaimFee6' => $row['ReClaimFee6'],
                ':ReClaimFee7' => $row['ReClaimFee7'],
                ':ReClaimFeeStartRegistDate' => $row['ReClaimFeeStartRegistDate'],
                ':ReClaimFeeStartDate' => $row['ReClaimFeeStartDate'],
                ':FirstCreditTransferClaimFee' => $row['FirstCreditTransferClaimFee'],
                ':FirstCreditTransferClaimFeeWeb' => $row['FirstCreditTransferClaimFeeWeb'],
                ':CreditTransferClaimFee' => $row['CreditTransferClaimFee'],
                ':OemSettlementFeeRate' => $row['OemSettlementFeeRate'],
                ':OemClaimFee' => $row['OemClaimFee'],
                ':SystemFee' => $row['SystemFee'],
                ':CreditCriterion' => $row['CreditCriterion'],
                ':CreditOrderUseAmount' => $row['CreditOrderUseAmount'],
                ':AutoCreditDateFrom' => $row['AutoCreditDateFrom'],
                ':AutoCreditDateTo' => $row['AutoCreditDateTo'],
                ':AutoCreditCriterion' => $row['AutoCreditCriterion'],
                ':AutoClaimStopFlg' => $row['AutoClaimStopFlg'],
                ':SelfBillingFlg' => $row['SelfBillingFlg'],
                ':SelfBillingFixFlg' => $row['SelfBillingFixFlg'],
                ':CombinedClaimDate' => $row['CombinedClaimDate'],
                ':LimitDatePattern' => $row['LimitDatePattern'],
                ':LimitDay' => $row['LimitDay'],
                ':PayingBackFlg' => $row['PayingBackFlg'],
                ':PayingBackDays' => $row['PayingBackDays'],
                ':SiteConfDate' => $row['SiteConfDate'],
                ':CreaditStartMail' => $row['CreaditStartMail'],
                ':CreaditCompMail' => $row['CreaditCompMail'],
                ':ClaimMail' => $row['ClaimMail'],
                ':ReceiptMail' => $row['ReceiptMail'],
                ':CancelMail' => $row['CancelMail'],
                ':AddressMail' => $row['AddressMail'],
                ':SoonPaymentMail' => $row['SoonPaymentMail'],
                ':NotPaymentConfMail' => $row['NotPaymentConfMail'],
                ':CreditResultMail' => $row['CreditResultMail'],
                ':AutoJournalDeliMethodId' => $row['AutoJournalDeliMethodId'],
                ':AutoJournalIncMode' => $row['AutoJournalIncMode'],
                ':SitClass' => $row['SitClass'],
                ':T_OrderClass' => $row['T_OrderClass'],
                ':PrintFormDK' => $row['PrintFormDK'],
                ':PrintFormBS' => $row['PrintFormBS'],
                ':FirstClaimKisanbiDelayDays' => $row['FirstClaimKisanbiDelayDays'],
                ':KisanbiDelayDays' => $row['KisanbiDelayDays'],
                ':RemindStopClass' => $row['RemindStopClass'],
                ':BarcodeLimitDays' => $row['BarcodeLimitDays'],
        		':ReceiptAgentId' => $row['ReceiptAgentId'],
        		':SubscriberCode' => $row['SubscriberCode'],
                ':CombinedClaimChargeFeeFlg' => $row['CombinedClaimChargeFeeFlg'],
                ':YuchoMT' => $row['YuchoMT'],
                ':CreditJudgeMethod' => $row['CreditJudgeMethod'],
                ':AverageUnitPriceRate' => $row['AverageUnitPriceRate'],
                ':SelfBillingOemClaimFee' => $row['SelfBillingOemClaimFee'],
                ':ClaimDisposeMail' => $row['ClaimDisposeMail'],
                ':MultiOrderCount' => $row['MultiOrderCount'],
                ':MultiOrderScore' => $row['MultiOrderScore'],
                ':NgChangeFlg' => $row['NgChangeFlg'],
                ':ShowNgReason' => $row['ShowNgReason'],
                ':MuhoshoChangeDays' => $row['MuhoshoChangeDays'],
                ':JintecManualReqFlg' => $row['JintecManualReqFlg'],
                ':OutOfTermcheck' => $row['OutOfTermcheck'],
                ':Telcheck' => $row['Telcheck'],
                ':Addresscheck' => $row['Addresscheck'],
                ':PostalCodecheck' => $row['PostalCodecheck'],
                ':Ent_OrderIdcheck' => $row['Ent_OrderIdcheck'],
                ':EtcAutoArrivalFlg' => $row['EtcAutoArrivalFlg'],
                ':EtcAutoArrivalNumber' => $row['EtcAutoArrivalNumber'],
                ':JintecJudge' => $data['JintecJudge'],
                ':JintecJudge0' => $data['JintecJudge0'],
                ':JintecJudge1' => $data['JintecJudge1'],
                ':JintecJudge2' => $data['JintecJudge2'],
                ':JintecJudge3' => $data['JintecJudge3'],
                ':JintecJudge4' => $data['JintecJudge4'],
                ':JintecJudge5' => $data['JintecJudge5'],
                ':JintecJudge6' => $data['JintecJudge6'],
                ':JintecJudge7' => $data['JintecJudge7'],
                ':JintecJudge8' => $data['JintecJudge8'],
                ':JintecJudge9' => $data['JintecJudge9'],
                ':JintecJudge10' => $data['JintecJudge10'],
                ':MerchantId' => $row['MerchantId'],
                ':ServiceId' => $row['ServiceId'],
                ':HashKey' => $row['HashKey'],
                ':BasicId' => $row['BasicId'],
                ':BasicPw' => $row['BasicPw'],
                ':ReceiptUsedFlg' => $row['ReceiptUsedFlg'],
                ':ReceiptIssueProviso' => $row['ReceiptIssueProviso'],
                ':SmallLogo' => $row['SmallLogo'],
                ':SpecificTransUrl' => $row['SpecificTransUrl'],
                ':CSSettlementFeeRate' => $row['CSSettlementFeeRate'],
                ':CSClaimFeeBS' => $row['CSClaimFeeBS'],
                ':CSClaimFeeDK' => $row['CSClaimFeeDK'],
                ':ReissueCount' => $row['ReissueCount'],
                ':ClaimAutoJournalIncMode' => $row['ClaimAutoJournalIncMode'],
                ':ChatBotFlg' => $row['ChatBotFlg'],
                ':OtherSitesAuthCheckFlg'  => $row['OtherSitesAuthCheckFlg'],
                ':MufjBarcodeUsedFlg'  => $row['MufjBarcodeUsedFlg'],
                ':MufjBarcodeSubscriberCode'  => $row['MufjBarcodeSubscriberCode'],
                ':RegistId' => $row['RegistId'],
                ':OemFirstCreditTransferClaimFee' => $row['OemFirstCreditTransferClaimFee'],
                ':OemFirstCreditTransferClaimFeeWeb' => $row['OemFirstCreditTransferClaimFeeWeb'],
                ':OemCreditTransferClaimFee' => $row['OemCreditTransferClaimFee'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
        );

        if (isset($data['PaymentAfterArrivalFlg'])) {
            $prm[':PaymentAfterArrivalFlg'] = $data['PaymentAfterArrivalFlg'];
        }

        return $stm->execute($prm);
	}

	/**
	 * 取りまとめ対象のサイト情報を取得する
	 *
	 * @param int $eid
	 * @return ResultInterface
	 */
	public function getSiteListByCombinedClaim($eid)
	{
        $sql = " SELECT * FROM T_Site WHERE ValidFlg = 1 AND CombinedClaimFlg = 1 AND EnterpriseId = :EnterpriseId ORDER BY SiteId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $eid,
        );

        return $stm->execute($prm);
	}

// Add By Takemasa(NDC) 20150514 Stt TableEnterpriseから移動
	/**
	 * 今日現在を基準とした初回請求支払期限を取得する。
	 *
	 * @param int $siteId サイトID
	 * @param int $normalLimitDays 通常支払期限日数
	 * @return string 支払期限日 'yyyy-MM-dd'書式で通知
	 */
	public function getLimitDate($siteId, $normalLimitDays)
	{
	    $sql = " SELECT * FROM T_Site WHERE SiteId = :SiteId ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':SiteId' => $siteId,
	    );

	    $siteData = $stm->execute($prm)->current();

	    if ($siteData['LimitDatePattern'] == 1) {
	        // 支払期限日は翌月指定日
	        $year = (int)date('Y');
	        $month = (int)date('m');
	        $day = $siteData['LimitDay'];

	        $cnt = 5;

	        // 翌月
	        $month++;
	        if ($month > 12) {
	            $year++;
	            $month = 1;
	        }

	        while(!CoralValidate::isDate(sprintf("%04d-%02d-%02d", $year, $month, $day)) && $cnt > 0) {
	            $day--;
	            $cnt--;
	        }

	        if ($cnt > 0) {
	            $today = sprintf("%04d-%02d-%02d", $year, $month, $day);
	        }
	        else {
	            throw new \Exception("支払期限算出エラー：翌月指定日");
	        }
	    }
	    else if ($siteData['LimitDatePattern'] == 2) {
	        // 支払い期限日は当月指定日
	        $year = (int)date('Y');
	        $month = (int)date('m');
	        $day = $siteData['LimitDay'];

	        $cnt = 5;

	        while(!CoralValidate::isDate(sprintf("%04d-%02d-%02d", $year, $month, $day)) && $cnt > 0) {
	            $day--;
	            $cnt--;
	        }

	        if ($cnt > 0) {
	            $today = sprintf("%04d-%02d-%02d", $year, $month, $day);
	        }
	        else {
	            throw new \Exception("支払期限算出エラー：当月指定日");
	        }

	        if (BaseGeneralUtils::CalcSpanDays($today, date('Y-m-d')) > 0) {
	            // 過去日であればエラーとする。
	            throw new \Exception("支払期限算出エラー（過去日）：当月指定日");
	        }
	    }
	    else {
	        // 支払期限日は通常
	        $limitDays = $normalLimitDays;
	        // 通常 x 1.5 or 2のバターン追加
	        if($siteData['LimitDatePattern'] == 3) {
	            // 通常 x 1.5
	            $limitDays = ceil($limitDays * 1.5);
	        }
	        else if($siteData['LimitDatePattern'] == 4) {
	            // 通常 x 2
	            $limitDays = ceil($limitDays * 2);
	        }
	        $today = date('Y-m-d', strtotime("+" . $limitDays . " day"));
	    }

	    if ($siteData['FirstClaimKisanbiDelayDays'] <> 0) {
	        $today = date('Y-m-d', strtotime($today . " +" . $siteData['FirstClaimKisanbiDelayDays'] . " day"));
	    }

	    return $today;
	}
// Add By Takemasa(NDC) 20150514 End TableEnterpriseから移動

	/**
	 * 請求日を基準とした初回請求支払期限を取得する。
	 *
	 * @param int $siteId サイトID
	 * @param int $normalLimitDays 通常支払期限日数
	 * @param date $claimDate 請求日（基準日）
	 * @return string 支払期限日 'yyyy-MM-dd'書式で通知
	 */
	public function getLimitDateForBatch($siteId, $normalLimitDays, $claimDate)
	{
	    $sql = " SELECT * FROM T_Site WHERE SiteId = :SiteId ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':SiteId' => $siteId,
	    );

	    $siteData = $stm->execute($prm)->current();

	    if ($siteData['LimitDatePattern'] == 1) {
	        // 支払期限日は翌月指定日
	        $year = (int)date('Y', strtotime($claimDate));
	        $month = (int)date('m', strtotime($claimDate));
	        $day = $siteData['LimitDay'];

	        $cnt = 5;

	        // 翌月
	        $month++;
	        if ($month > 12) {
	            $year++;
	            $month = 1;
	        }

	        while(!CoralValidate::isDate(sprintf("%04d-%02d-%02d", $year, $month, $day)) && $cnt > 0) {
	            $day--;
	            $cnt--;
	        }

	        if ($cnt > 0) {
	            $today = sprintf("%04d-%02d-%02d", $year, $month, $day);
	        }
	        else {
	            throw new \Exception("支払期限算出エラー：翌月指定日");
	        }
	    }
	    else if ($siteData['LimitDatePattern'] == 2) {
	        // 支払い期限日は当月指定日
	        $year = (int)date('Y', strtotime($claimDate));
	        $month = (int)date('m', strtotime($claimDate));
	        $day = $siteData['LimitDay'];

	        $cnt = 5;

	        while(!CoralValidate::isDate(sprintf("%04d-%02d-%02d", $year, $month, $day)) && $cnt > 0) {
	            $day--;
	            $cnt--;
	        }

	        if ($cnt > 0) {
	            $today = sprintf("%04d-%02d-%02d", $year, $month, $day);
	        }
	        else {
	            throw new \Exception("支払期限算出エラー：当月指定日");
	        }

	        if (BaseGeneralUtils::CalcSpanDays($today, $claimDate) > 0) {
	            // 過去日であればエラーとする。
	            throw new \Exception("支払期限算出エラー（過去日）：当月指定日");
	        }
	    }
	    else {
	        // 支払期限日は通常
	        $limitDays = $normalLimitDays;
	        // 通常 x 1.5 or 2のバターン追加
	        if($siteData['LimitDatePattern'] == 3) {
	            // 通常 x 1.5
	            $limitDays = ceil($limitDays * 1.5);
	        }
	        else if($siteData['LimitDatePattern'] == 4) {
	            // 通常 x 2
	            $limitDays = ceil($limitDays * 2);
	        }
	        $today = date('Y-m-d', strtotime($claimDate . " +" . $limitDays . " day"));
	    }

	    if ($siteData['FirstClaimKisanbiDelayDays'] <> 0) {
	        $today = date('Y-m-d', strtotime($today . " +" . $siteData['FirstClaimKisanbiDelayDays'] . " day"));
	    }

	    return $today;
	}

// Add By suzuki_h(NDC) 20150806 Stt TableEnterpriseから移動
		/**
		 * 指定シーケンスのOEM決済手数料（同梱）を取得する
		 *
		 * @param int $siteId サイトID
		 * @return int | null OEM同梱請求手数料
		 */
		public function getSelfBillingOemClaimFee($siteId)
		{
	        $row = $this->findSite($siteId)->current();
	        return ($row) ? $row['SelfBillingOemClaimFee'] : null;
		}
// Add By suzuki_h(NDC) 20150806 Stt TableEnterpriseから移動

}
