<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Coral\Base\BaseGeneralUtils;
use models\Sequence\SequenceGeneral;
use models\Table\TableSystemProperty;

/**
 * T_Orderテーブルへのアダプタ
 */
class TableOrder
{
	/**
	 * OrderIdに使用するシーケンス名
	 *
	 * @var string
	 */
	const ORDER_ID_SEQ_NAME = 'OrderIdSeq';

	/**
	 * OrderIdのプレフィックス文字列
	 *
	 * @var string
	 */
    const ORDER_ID_PREFIX = 'AK';

	/**
	 * OrderIdのシーケンス部分の桁数
	 *
	 * @var int
	 */
	const ORDER_ID_SEQ_LENGTH = 8;

	/**
	 * OrderIdのサフィックス文字列
	 */
	const ORDER_ID_SUFFIX = '';

    /**
     * 債権検索条件となる経過日
     *
     */
    const CLAIM_DAY = 120;

	protected $_name = 'T_Order';
	protected $_primary = array('OrderSeq');
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
	 * 注文データを取得する
	 *
	 * @param int $orderSeq
	 * @return ResultInterface
	 */
	public function find($orderSeq)
	{
	    $sql  = " SELECT * FROM T_Order WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定条件（AND）の注文データを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @param boolean $isAsc プライマリキーのオーダー
	 * @return ResultInterface
	 */
    public function findOrder($conditionArray, $isAsc = false)
	{
	    $prm = array();
        $sql  = " SELECT * FROM T_Order WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }
        $sql .= " ORDER BY OrderSeq " . ($isAsc ? "asc" : "desc");

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
	}

	/**
	 * 次の与信待ちデータの注文Seqを取得する。
	 *
	 * @param int $orderSeq 注文Seq
	 * @return 次の与信判断対象の注文Seq
	 */
	public function getNextOrderSeqForJudge($orderSeq)
	{
	    $sql = " SELECT OrderSeq FROM T_Order WHERE Incre_Status = 0 AND DataStatus = 15 AND OrderSeq > :OrderSeq ORDER BY OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
        );

        $ri = $stm->execute($prm);

        $nextSeq = ($ri->count() > 0) ? (int)$ri->current()['OrderSeq'] : 0;
        return $nextSeq;
	}

	/**
	 * 注文データの社内与信ステータスをNGに変更する。
	 */
	public function setIncreStatusNG($orderSeq)
	{
	    $data["Incre_Status"] = -1;
        $this->saveUpdate($data, $orderSeq);
	}

	/**
	 * 注文データの社内与信ステータスを未判断（確定待ち）に変更する。
	 */
	public function setIncreStatusNeutral($orderSeq)
	{
	    $data["Incre_Status"] = 0;
        $this->saveUpdate($data, $orderSeq);
	}

	/**
	 * 注文データの社内与信ステータスをOKに変更する。
	 */
	public function setIncreStatusOK($orderSeq)
	{
	    $data["Incre_Status"] = 1;
        $this->saveUpdate($data, $orderSeq);
	}

	/**
	 * 立替確定情報をセットする。
	 *
	 * @param int $oseq 注文SEQ
	 * @param string $fixedDate 締め日 'yyyy-MM-dd'書式で通知
	 * @param string $decisionDate 確定日 'yyyy-MM-dd'書式で通知
	 * @param int $chargeAmount 立替額
	 * @param int $payingControlSeq 立替振込管理Seq
	 */
	public function settleUp($oseq, $fixedDate, $decisionDate, $chargeAmount, $payingControlSeq)
	{
	    $sql  = " UPDATE T_Order ";
        $sql .= " SET ";
        $sql .= "     Chg_Status       = 1 ";// 立替確定時に「立替済」とする。
        $sql .= " ,   Chg_FixedDate    = :Chg_FixedDate ";
        $sql .= " ,   Chg_DecisionDate = :Chg_DecisionDate ";
        $sql .= " ,   Chg_ChargeAmount = :Chg_ChargeAmount ";
        $sql .= " ,   Chg_Seq          = :Chg_Seq ";
        $sql .= " WHERE OrderSeq       = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Chg_FixedDate' => $fixedDate,
                ':Chg_DecisionDate' => $decisionDate,
                ':Chg_ChargeAmount' => $chargeAmount,
                ':Chg_Seq' => $payingControlSeq,
                ':OrderSeq' => $oseq,
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
        $sql  = " INSERT INTO T_Order (OrderId, ReceiptOrderDate, EnterpriseId, SiteId, UseAmount, AnotherDeliFlg, DataStatus, CloseReason, Incre_Status, Incre_AtnEnterpriseScore, Incre_AtnEnterpriseNote, Incre_BorderScore, Incre_BorderNote, Incre_LimitCheckScore, Incre_LimitCheckNote, Incre_ScoreTotal, Incre_DecisionDate, Incre_DecisionOpId, Dmi_Status, Dmi_ResponseCode, Dmi_DecisionDate, Chg_Status, Chg_FixedDate, Chg_DecisionDate, Chg_ExecDate, Chg_ChargeAmount, Rct_RejectFlg, Rct_RejectReason, Rct_Status, Cnl_CantCancelFlg, Cnl_Status, Dmg_DecisionFlg, Dmg_DecisionDate, Dmg_DecisionAmount, Dmg_DecisionReason, Ent_OrderId, Ent_Note, Incre_Note, Dmi_ResponseNote, RegistDate, Chg_Seq, Bekkan, Dmi_DecSeqId, Rct_MailFlg, StopClaimFlg, MailPaymentSoonDate, MailLimitPassageDate, MailLimitPassageCount, ReturnClaimFlg, RemindClass, TouchHistoryFlg, BriefNote, LonghandLetter, VisitFlg, FinalityCollectionMean, FinalityRemindDate, FinalityRemindOpId, PromPayDate, ClaimStopReleaseDate, LetterClaimStopFlg, MailClaimStopFlg, InstallmentPlanAmount, OutOfAmends, OrderRegisterMethod, ApiUserId, CreditConditionMatchData, Cnl_ReturnSaikenCancelFlg, PrintedTransBeforeCancelled, Deli_ConfirmArrivalFlg, Deli_ConfirmArrivalDate, CombinedClaimTargetStatus, CombinedClaimParentFlg, Jintec_Flags, OemId, Oem_OrderId, Oem_Note, OemBadDebtSeq, OemBadDebtType, T_OrderClass, T_OrderAutoCreditJudgeClass, ServiceTargetClass, ServiceExpectedDate, DailySummaryFlg, PendingReasonCode, ClaimSendingClass, P_OrderSeq, CancelBefDataStatus, Tel30DaysFlg, Tel90DaysFlg, NewSystemFlg, CreditReplyDate, OemClaimTransDate, OemClaimTransFlg, ConfirmWaitingFlg, CreditNgHiddenFlg, Incre_JudgeScoreTotal, Incre_CoreScoreTotal, Incre_ItemScoreTotal, Incre_NoteScore, Incre_PastOrderScore, Incre_UnpaidScore, Incre_NonPaymentScore, Incre_IdentityDocumentScore, Incre_MischiefCancelScore, Chg_NonChargeFlg, Dmg_DecisionUseAmount, Dmg_DecisionClaimFee, Dmg_DecisionDamageInterestAmount, Dmg_DecisionAdditionalClaimFee, ReverseOrderId, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderId ";
        $sql .= " , :ReceiptOrderDate ";
        $sql .= " , :EnterpriseId ";
        $sql .= " , :SiteId ";
        $sql .= " , :UseAmount ";
        $sql .= " , :AnotherDeliFlg ";
        $sql .= " , :DataStatus ";
        $sql .= " , :CloseReason ";
        $sql .= " , :Incre_Status ";
        $sql .= " , :Incre_AtnEnterpriseScore ";
        $sql .= " , :Incre_AtnEnterpriseNote ";
        $sql .= " , :Incre_BorderScore ";
        $sql .= " , :Incre_BorderNote ";
        $sql .= " , :Incre_LimitCheckScore ";
        $sql .= " , :Incre_LimitCheckNote ";
        $sql .= " , :Incre_ScoreTotal ";
        $sql .= " , :Incre_DecisionDate ";
        $sql .= " , :Incre_DecisionOpId ";
        $sql .= " , :Dmi_Status ";
        $sql .= " , :Dmi_ResponseCode ";
        $sql .= " , :Dmi_DecisionDate ";
        $sql .= " , :Chg_Status ";
        $sql .= " , :Chg_FixedDate ";
        $sql .= " , :Chg_DecisionDate ";
        $sql .= " , :Chg_ExecDate ";
        $sql .= " , :Chg_ChargeAmount ";
        $sql .= " , :Rct_RejectFlg ";
        $sql .= " , :Rct_RejectReason ";
        $sql .= " , :Rct_Status ";
        $sql .= " , :Cnl_CantCancelFlg ";
        $sql .= " , :Cnl_Status ";
        $sql .= " , :Dmg_DecisionFlg ";
        $sql .= " , :Dmg_DecisionDate ";
        $sql .= " , :Dmg_DecisionAmount ";
        $sql .= " , :Dmg_DecisionReason ";
        $sql .= " , :Ent_OrderId ";
        $sql .= " , :Ent_Note ";
        $sql .= " , :Incre_Note ";
        $sql .= " , :Dmi_ResponseNote ";
        $sql .= " , :RegistDate ";
        $sql .= " , :Chg_Seq ";
        $sql .= " , :Bekkan ";
        $sql .= " , :Dmi_DecSeqId ";
        $sql .= " , :Rct_MailFlg ";
        $sql .= " , :StopClaimFlg ";
        $sql .= " , :MailPaymentSoonDate ";
        $sql .= " , :MailLimitPassageDate ";
        $sql .= " , :MailLimitPassageCount ";
        $sql .= " , :ReturnClaimFlg ";
        $sql .= " , :RemindClass ";
        $sql .= " , :TouchHistoryFlg ";
        $sql .= " , :BriefNote ";
        $sql .= " , :LonghandLetter ";
        $sql .= " , :VisitFlg ";
        $sql .= " , :FinalityCollectionMean ";
        $sql .= " , :FinalityRemindDate ";
        $sql .= " , :FinalityRemindOpId ";
        $sql .= " , :PromPayDate ";
        $sql .= " , :ClaimStopReleaseDate ";
        $sql .= " , :LetterClaimStopFlg ";
        $sql .= " , :MailClaimStopFlg ";
        $sql .= " , :InstallmentPlanAmount ";
        $sql .= " , :OutOfAmends ";
        $sql .= " , :OrderRegisterMethod ";
        $sql .= " , :ApiUserId ";
        $sql .= " , :CreditConditionMatchData ";
        $sql .= " , :Cnl_ReturnSaikenCancelFlg ";
        $sql .= " , :PrintedTransBeforeCancelled ";
        $sql .= " , :Deli_ConfirmArrivalFlg ";
        $sql .= " , :Deli_ConfirmArrivalDate ";
        $sql .= " , :CombinedClaimTargetStatus ";
        $sql .= " , :CombinedClaimParentFlg ";
        $sql .= " , :Jintec_Flags ";
        $sql .= " , :OemId ";
        $sql .= " , :Oem_OrderId ";
        $sql .= " , :Oem_Note ";
        $sql .= " , :OemBadDebtSeq ";
        $sql .= " , :OemBadDebtType ";
        $sql .= " , :T_OrderClass ";
        $sql .= " , :T_OrderAutoCreditJudgeClass ";
        $sql .= " , :ServiceTargetClass ";
        $sql .= " , :ServiceExpectedDate ";
        $sql .= " , :DailySummaryFlg ";
        $sql .= " , :PendingReasonCode ";
        $sql .= " , :ClaimSendingClass ";
        $sql .= " , :P_OrderSeq ";
        $sql .= " , :CancelBefDataStatus ";
        $sql .= " , :Tel30DaysFlg ";
        $sql .= " , :Tel90DaysFlg ";
        $sql .= " , :NewSystemFlg ";
        $sql .= " , :CreditReplyDate ";
        $sql .= " , :OemClaimTransDate ";
        $sql .= " , :OemClaimTransFlg ";
        $sql .= " , :ConfirmWaitingFlg ";
        $sql .= " , :CreditNgHiddenFlg ";
        $sql .= " , :Incre_JudgeScoreTotal ";
        $sql .= " , :Incre_CoreScoreTotal ";
        $sql .= " , :Incre_ItemScoreTotal ";
        $sql .= " , :Incre_NoteScore ";
        $sql .= " , :Incre_PastOrderScore ";
        $sql .= " , :Incre_UnpaidScore ";
        $sql .= " , :Incre_NonPaymentScore ";
        $sql .= " , :Incre_IdentityDocumentScore ";
        $sql .= " , :Incre_MischiefCancelScore ";
        $sql .= " , :Chg_NonChargeFlg ";
        $sql .= " , :Dmg_DecisionUseAmount ";
        $sql .= " , :Dmg_DecisionClaimFee ";
        $sql .= " , :Dmg_DecisionDamageInterestAmount ";
        $sql .= " , :Dmg_DecisionAdditionalClaimFee ";
        $sql .= " , :ReverseOrderId ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderId' => $data['OrderId'],
                ':ReceiptOrderDate' => $data['ReceiptOrderDate'],
                ':EnterpriseId' => $data['EnterpriseId'],
                ':SiteId' => $data['SiteId'],
                ':UseAmount' => $data['UseAmount'],
                ':AnotherDeliFlg' => $data['AnotherDeliFlg'],
                ':DataStatus' => $data['DataStatus'],
                ':CloseReason' => $data['CloseReason'],
                ':Incre_Status' => $data['Incre_Status'],
                ':Incre_AtnEnterpriseScore' => $data['Incre_AtnEnterpriseScore'],
                ':Incre_AtnEnterpriseNote' => $data['Incre_AtnEnterpriseNote'],
                ':Incre_BorderScore' => $data['Incre_BorderScore'],
                ':Incre_BorderNote' => $data['Incre_BorderNote'],
                ':Incre_LimitCheckScore' => $data['Incre_LimitCheckScore'],
                ':Incre_LimitCheckNote' => $data['Incre_LimitCheckNote'],
                ':Incre_ScoreTotal' => $data['Incre_ScoreTotal'],
                ':Incre_DecisionDate' => $data['Incre_DecisionDate'],
                ':Incre_DecisionOpId' => $data['Incre_DecisionOpId'],
                ':Dmi_Status' => $data['Dmi_Status'],
                ':Dmi_ResponseCode' => $data['Dmi_ResponseCode'],
                ':Dmi_DecisionDate' => $data['Dmi_DecisionDate'],
                ':Chg_Status' => $data['Chg_Status'],
                ':Chg_FixedDate' => $data['Chg_FixedDate'],
                ':Chg_DecisionDate' => $data['Chg_DecisionDate'],
                ':Chg_ExecDate' => $data['Chg_ExecDate'],
                ':Chg_ChargeAmount' => $data['Chg_ChargeAmount'],
                ':Rct_RejectFlg' => $data['Rct_RejectFlg'],
                ':Rct_RejectReason' => $data['Rct_RejectReason'],
                ':Rct_Status' => $data['Rct_Status'],
                ':Cnl_CantCancelFlg' => $data['Cnl_CantCancelFlg'],
                ':Cnl_Status' => $data['Cnl_Status'],
                ':Dmg_DecisionFlg' => $data['Dmg_DecisionFlg'],
                ':Dmg_DecisionDate' => $data['Dmg_DecisionDate'],
                ':Dmg_DecisionAmount' => $data['Dmg_DecisionAmount'],
                ':Dmg_DecisionReason' => $data['Dmg_DecisionReason'],
                ':Ent_OrderId' => $data['Ent_OrderId'],
                ':Ent_Note' => $data['Ent_Note'],
                ':Incre_Note' => $data['Incre_Note'],
                ':Dmi_ResponseNote' => $data['Dmi_ResponseNote'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':Chg_Seq' => $data['Chg_Seq'],
                ':Bekkan' => $data['Bekkan'],
                ':Dmi_DecSeqId' => $data['Dmi_DecSeqId'],
                ':Rct_MailFlg' => $data['Rct_MailFlg'],
                ':StopClaimFlg' => $data['StopClaimFlg'],
                ':MailPaymentSoonDate' => $data['MailPaymentSoonDate'],
                ':MailLimitPassageDate' => $data['MailLimitPassageDate'],
                ':MailLimitPassageCount' => $data['MailLimitPassageCount'],
                ':ReturnClaimFlg' => $data['ReturnClaimFlg'],
                ':RemindClass' => $data['RemindClass'],
                ':TouchHistoryFlg' => $data['TouchHistoryFlg'],
                ':BriefNote' => $data['BriefNote'],
                ':LonghandLetter' => $data['LonghandLetter'],
                ':VisitFlg' => $data['VisitFlg'],
                ':FinalityCollectionMean' => $data['FinalityCollectionMean'],
                ':FinalityRemindDate' => $data['FinalityRemindDate'],
                ':FinalityRemindOpId' => $data['FinalityRemindOpId'],
                ':PromPayDate' => $data['PromPayDate'],
                ':ClaimStopReleaseDate' => $data['ClaimStopReleaseDate'],
                ':LetterClaimStopFlg' => $data['LetterClaimStopFlg'],
                ':MailClaimStopFlg' => $data['MailClaimStopFlg'],
                ':InstallmentPlanAmount' => $data['InstallmentPlanAmount'],
                ':OutOfAmends' => $data['OutOfAmends'],
                ':OrderRegisterMethod' => $data['OrderRegisterMethod'],
                ':ApiUserId' => $data['ApiUserId'],
                ':CreditConditionMatchData' => $data['CreditConditionMatchData'],
                ':Cnl_ReturnSaikenCancelFlg' => $data['Cnl_ReturnSaikenCancelFlg'],
                ':PrintedTransBeforeCancelled' => $data['PrintedTransBeforeCancelled'],
                ':Deli_ConfirmArrivalFlg' => $data['Deli_ConfirmArrivalFlg'],
                ':Deli_ConfirmArrivalDate' => $data['Deli_ConfirmArrivalDate'],
                ':CombinedClaimTargetStatus' => $data['CombinedClaimTargetStatus'],
                ':CombinedClaimParentFlg' => $data['CombinedClaimParentFlg'],
                ':Jintec_Flags' => $data['Jintec_Flags'],
                ':OemId' => $data['OemId'],
                ':Oem_OrderId' => $data['Oem_OrderId'],
                ':Oem_Note' => $data['Oem_Note'],
                ':OemBadDebtSeq' => $data['OemBadDebtSeq'],
                ':OemBadDebtType' => $data['OemBadDebtType'],
                ':T_OrderClass' => isset($data['T_OrderClass']) ? $data['T_OrderClass'] : 0,
                ':T_OrderAutoCreditJudgeClass' => isset($data['T_OrderAutoCreditJudgeClass']) ? $data['T_OrderAutoCreditJudgeClass'] : 0,
                ':ServiceTargetClass' => isset($data['ServiceTargetClass']) ? $data['ServiceTargetClass'] : 0,
                ':ServiceExpectedDate' => $data['ServiceExpectedDate'],
                ':DailySummaryFlg' => isset($data['DailySummaryFlg']) ? $data['DailySummaryFlg'] : 0,
                ':PendingReasonCode' => $data['PendingReasonCode'],
                ':ClaimSendingClass' => isset($data['ClaimSendingClass']) ? $data['ClaimSendingClass'] : 0,
                ':P_OrderSeq' => $data['P_OrderSeq'],
                ':CancelBefDataStatus' => $data['CancelBefDataStatus'],
                ':Tel30DaysFlg' => isset($data['Tel30DaysFlg']) ? $data['Tel30DaysFlg'] : 0,
                ':Tel90DaysFlg' => isset($data['Tel90DaysFlg']) ? $data['Tel90DaysFlg'] : 0,
                ':NewSystemFlg' => isset($data['NewSystemFlg']) ? $data['NewSystemFlg'] : 0,
                ':CreditReplyDate' => $data['CreditReplyDate'],
                ':OemClaimTransDate' => $data['OemClaimTransDate'],
                ':OemClaimTransFlg' => isset($data['OemClaimTransFlg']) ? $data['OemClaimTransFlg'] : 0,
                ':ConfirmWaitingFlg' => isset($data['ConfirmWaitingFlg']) ? $data['ConfirmWaitingFlg'] : 0,
                ':CreditNgHiddenFlg' => isset($data['CreditNgHiddenFlg']) ? $data['CreditNgHiddenFlg'] : 0,
                ':Incre_JudgeScoreTotal' => $data['Incre_JudgeScoreTotal'],
                ':Incre_CoreScoreTotal' => $data['Incre_CoreScoreTotal'],
                ':Incre_ItemScoreTotal' => $data['Incre_ItemScoreTotal'],
                ':Incre_NoteScore' => $data['Incre_NoteScore'],
                ':Incre_PastOrderScore' => $data['Incre_PastOrderScore'],
                ':Incre_UnpaidScore' => $data['Incre_UnpaidScore'],
                ':Incre_NonPaymentScore' => $data['Incre_NonPaymentScore'],
                ':Incre_IdentityDocumentScore' => $data['Incre_IdentityDocumentScore'],
                ':Incre_MischiefCancelScore' => $data['Incre_MischiefCancelScore'],
                ':Chg_NonChargeFlg' => isset($data['Chg_NonChargeFlg']) ? $data['Chg_NonChargeFlg'] : 0,
                ':Dmg_DecisionUseAmount' => $data['Dmg_DecisionUseAmount'],
                ':Dmg_DecisionClaimFee' => $data['Dmg_DecisionClaimFee'],
                ':Dmg_DecisionDamageInterestAmount' => $data['Dmg_DecisionDamageInterestAmount'],
                ':Dmg_DecisionAdditionalClaimFee' => $data['Dmg_DecisionAdditionalClaimFee'],
                ':ReverseOrderId' => $data['ReverseOrderId'],
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
	 * @param int $orderSeq 更新するorderSeq
	 */
	public function saveUpdate($data, $orderSeq)
	{
	    $sql = " SELECT * FROM T_Order WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_Order ";
        $sql .= " SET ";
        $sql .= "     OrderId = :OrderId ";
        $sql .= " ,   ReceiptOrderDate = :ReceiptOrderDate ";
        $sql .= " ,   EnterpriseId = :EnterpriseId ";
        $sql .= " ,   SiteId = :SiteId ";
        $sql .= " ,   UseAmount = :UseAmount ";
        $sql .= " ,   AnotherDeliFlg = :AnotherDeliFlg ";
        $sql .= " ,   DataStatus = :DataStatus ";
        $sql .= " ,   CloseReason = :CloseReason ";
        $sql .= " ,   Incre_Status = :Incre_Status ";
        $sql .= " ,   Incre_AtnEnterpriseScore = :Incre_AtnEnterpriseScore ";
        $sql .= " ,   Incre_AtnEnterpriseNote = :Incre_AtnEnterpriseNote ";
        $sql .= " ,   Incre_BorderScore = :Incre_BorderScore ";
        $sql .= " ,   Incre_BorderNote = :Incre_BorderNote ";
        $sql .= " ,   Incre_LimitCheckScore = :Incre_LimitCheckScore ";
        $sql .= " ,   Incre_LimitCheckNote = :Incre_LimitCheckNote ";
        $sql .= " ,   Incre_ScoreTotal = :Incre_ScoreTotal ";
        $sql .= " ,   Incre_DecisionDate = :Incre_DecisionDate ";
        $sql .= " ,   Incre_DecisionOpId = :Incre_DecisionOpId ";
        $sql .= " ,   Dmi_Status = :Dmi_Status ";
        $sql .= " ,   Dmi_ResponseCode = :Dmi_ResponseCode ";
        $sql .= " ,   Dmi_DecisionDate = :Dmi_DecisionDate ";
        $sql .= " ,   Chg_Status = :Chg_Status ";
        $sql .= " ,   Chg_FixedDate = :Chg_FixedDate ";
        $sql .= " ,   Chg_DecisionDate = :Chg_DecisionDate ";
        $sql .= " ,   Chg_ExecDate = :Chg_ExecDate ";
        $sql .= " ,   Chg_ChargeAmount = :Chg_ChargeAmount ";
        $sql .= " ,   Rct_RejectFlg = :Rct_RejectFlg ";
        $sql .= " ,   Rct_RejectReason = :Rct_RejectReason ";
        $sql .= " ,   Rct_Status = :Rct_Status ";
        $sql .= " ,   Cnl_CantCancelFlg = :Cnl_CantCancelFlg ";
        $sql .= " ,   Cnl_Status = :Cnl_Status ";
        $sql .= " ,   Dmg_DecisionFlg = :Dmg_DecisionFlg ";
        $sql .= " ,   Dmg_DecisionDate = :Dmg_DecisionDate ";
        $sql .= " ,   Dmg_DecisionAmount = :Dmg_DecisionAmount ";
        $sql .= " ,   Dmg_DecisionReason = :Dmg_DecisionReason ";
        $sql .= " ,   Ent_OrderId = :Ent_OrderId ";
        $sql .= " ,   Ent_Note = :Ent_Note ";
        $sql .= " ,   Incre_Note = :Incre_Note ";
        $sql .= " ,   Dmi_ResponseNote = :Dmi_ResponseNote ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   Chg_Seq = :Chg_Seq ";
        $sql .= " ,   Bekkan = :Bekkan ";
        $sql .= " ,   Dmi_DecSeqId = :Dmi_DecSeqId ";
        $sql .= " ,   Rct_MailFlg = :Rct_MailFlg ";
        $sql .= " ,   StopClaimFlg = :StopClaimFlg ";
        $sql .= " ,   MailPaymentSoonDate = :MailPaymentSoonDate ";
        $sql .= " ,   MailLimitPassageDate = :MailLimitPassageDate ";
        $sql .= " ,   MailLimitPassageCount = :MailLimitPassageCount ";
        $sql .= " ,   ReturnClaimFlg = :ReturnClaimFlg ";
        $sql .= " ,   RemindClass = :RemindClass ";
        $sql .= " ,   TouchHistoryFlg = :TouchHistoryFlg ";
        $sql .= " ,   BriefNote = :BriefNote ";
        $sql .= " ,   LonghandLetter = :LonghandLetter ";
        $sql .= " ,   VisitFlg = :VisitFlg ";
        $sql .= " ,   FinalityCollectionMean = :FinalityCollectionMean ";
        $sql .= " ,   FinalityRemindDate = :FinalityRemindDate ";
        $sql .= " ,   FinalityRemindOpId = :FinalityRemindOpId ";
        $sql .= " ,   PromPayDate = :PromPayDate ";
        $sql .= " ,   ClaimStopReleaseDate = :ClaimStopReleaseDate ";
        $sql .= " ,   LetterClaimStopFlg = :LetterClaimStopFlg ";
        $sql .= " ,   MailClaimStopFlg = :MailClaimStopFlg ";
        $sql .= " ,   InstallmentPlanAmount = :InstallmentPlanAmount ";
        $sql .= " ,   OutOfAmends = :OutOfAmends ";
        $sql .= " ,   OrderRegisterMethod = :OrderRegisterMethod ";
        $sql .= " ,   ApiUserId = :ApiUserId ";
        $sql .= " ,   CreditConditionMatchData = :CreditConditionMatchData ";
        $sql .= " ,   Cnl_ReturnSaikenCancelFlg = :Cnl_ReturnSaikenCancelFlg ";
        $sql .= " ,   PrintedTransBeforeCancelled = :PrintedTransBeforeCancelled ";
        $sql .= " ,   Deli_ConfirmArrivalFlg = :Deli_ConfirmArrivalFlg ";
        $sql .= " ,   Deli_ConfirmArrivalDate = :Deli_ConfirmArrivalDate ";
        $sql .= " ,   CombinedClaimTargetStatus = :CombinedClaimTargetStatus ";
        $sql .= " ,   CombinedClaimParentFlg = :CombinedClaimParentFlg ";
        $sql .= " ,   Jintec_Flags = :Jintec_Flags ";
        $sql .= " ,   OemId = :OemId ";
        $sql .= " ,   Oem_OrderId = :Oem_OrderId ";
        $sql .= " ,   Oem_Note = :Oem_Note ";
        $sql .= " ,   OemBadDebtSeq = :OemBadDebtSeq ";
        $sql .= " ,   OemBadDebtType = :OemBadDebtType ";
        $sql .= " ,   T_OrderClass = :T_OrderClass ";
        $sql .= " ,   T_OrderAutoCreditJudgeClass = :T_OrderAutoCreditJudgeClass ";
        $sql .= " ,   ServiceTargetClass = :ServiceTargetClass ";
        $sql .= " ,   ServiceExpectedDate = :ServiceExpectedDate ";
        $sql .= " ,   DailySummaryFlg = :DailySummaryFlg ";
        $sql .= " ,   PendingReasonCode = :PendingReasonCode ";
        $sql .= " ,   ClaimSendingClass = :ClaimSendingClass ";
        $sql .= " ,   P_OrderSeq = :P_OrderSeq ";
        $sql .= " ,   CancelBefDataStatus = :CancelBefDataStatus ";
        $sql .= " ,   Tel30DaysFlg = :Tel30DaysFlg ";
        $sql .= " ,   Tel90DaysFlg = :Tel90DaysFlg ";
        $sql .= " ,   NewSystemFlg = :NewSystemFlg ";
        $sql .= " ,   CreditReplyDate = :CreditReplyDate ";
        $sql .= " ,   OemClaimTransDate = :OemClaimTransDate ";
        $sql .= " ,   OemClaimTransFlg = :OemClaimTransFlg ";
        $sql .= " ,   ConfirmWaitingFlg = :ConfirmWaitingFlg ";
        $sql .= " ,   CreditNgHiddenFlg = :CreditNgHiddenFlg ";
        $sql .= " ,   Incre_JudgeScoreTotal = :Incre_JudgeScoreTotal ";
        $sql .= " ,   Incre_CoreScoreTotal = :Incre_CoreScoreTotal ";
        $sql .= " ,   Incre_ItemScoreTotal = :Incre_ItemScoreTotal ";
        $sql .= " ,   Incre_NoteScore = :Incre_NoteScore ";
        $sql .= " ,   Incre_PastOrderScore = :Incre_PastOrderScore ";
        $sql .= " ,   Incre_UnpaidScore = :Incre_UnpaidScore ";
        $sql .= " ,   Incre_NonPaymentScore = :Incre_NonPaymentScore ";
        $sql .= " ,   Incre_IdentityDocumentScore = :Incre_IdentityDocumentScore ";
        $sql .= " ,   Incre_MischiefCancelScore = :Incre_MischiefCancelScore ";
        $sql .= " ,   Chg_NonChargeFlg = :Chg_NonChargeFlg ";
        $sql .= " ,   Dmg_DecisionUseAmount = :Dmg_DecisionUseAmount ";
        $sql .= " ,   Dmg_DecisionClaimFee = :Dmg_DecisionClaimFee ";
        $sql .= " ,   Dmg_DecisionDamageInterestAmount = :Dmg_DecisionDamageInterestAmount ";
        $sql .= " ,   Dmg_DecisionAdditionalClaimFee = :Dmg_DecisionAdditionalClaimFee ";
        $sql .= " ,   ReverseOrderId = :ReverseOrderId ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
                ':OrderId' => $row['OrderId'],
                ':ReceiptOrderDate' => $row['ReceiptOrderDate'],
                ':EnterpriseId' => $row['EnterpriseId'],
                ':SiteId' => $row['SiteId'],
                ':UseAmount' => $row['UseAmount'],
                ':AnotherDeliFlg' => $row['AnotherDeliFlg'],
                ':DataStatus' => $row['DataStatus'],
                ':CloseReason' => $row['CloseReason'],
                ':Incre_Status' => $row['Incre_Status'],
                ':Incre_AtnEnterpriseScore' => $row['Incre_AtnEnterpriseScore'],
                ':Incre_AtnEnterpriseNote' => $row['Incre_AtnEnterpriseNote'],
                ':Incre_BorderScore' => $row['Incre_BorderScore'],
                ':Incre_BorderNote' => $row['Incre_BorderNote'],
                ':Incre_LimitCheckScore' => $row['Incre_LimitCheckScore'],
                ':Incre_LimitCheckNote' => $row['Incre_LimitCheckNote'],
                ':Incre_ScoreTotal' => $row['Incre_ScoreTotal'],
                ':Incre_DecisionDate' => $row['Incre_DecisionDate'],
                ':Incre_DecisionOpId' => $row['Incre_DecisionOpId'],
                ':Dmi_Status' => $row['Dmi_Status'],
                ':Dmi_ResponseCode' => $row['Dmi_ResponseCode'],
                ':Dmi_DecisionDate' => $row['Dmi_DecisionDate'],
                ':Chg_Status' => $row['Chg_Status'],
                ':Chg_FixedDate' => $row['Chg_FixedDate'],
                ':Chg_DecisionDate' => $row['Chg_DecisionDate'],
                ':Chg_ExecDate' => $row['Chg_ExecDate'],
                ':Chg_ChargeAmount' => $row['Chg_ChargeAmount'],
                ':Rct_RejectFlg' => $row['Rct_RejectFlg'],
                ':Rct_RejectReason' => $row['Rct_RejectReason'],
                ':Rct_Status' => $row['Rct_Status'],
                ':Cnl_CantCancelFlg' => $row['Cnl_CantCancelFlg'],
                ':Cnl_Status' => $row['Cnl_Status'],
                ':Dmg_DecisionFlg' => $row['Dmg_DecisionFlg'],
                ':Dmg_DecisionDate' => $row['Dmg_DecisionDate'],
                ':Dmg_DecisionAmount' => $row['Dmg_DecisionAmount'],
                ':Dmg_DecisionReason' => $row['Dmg_DecisionReason'],
                ':Ent_OrderId' => $row['Ent_OrderId'],
                ':Ent_Note' => $row['Ent_Note'],
                ':Incre_Note' => $row['Incre_Note'],
                ':Dmi_ResponseNote' => $row['Dmi_ResponseNote'],
                ':RegistDate' => $row['RegistDate'],
                ':Chg_Seq' => $row['Chg_Seq'],
                ':Bekkan' => $row['Bekkan'],
                ':Dmi_DecSeqId' => $row['Dmi_DecSeqId'],
                ':Rct_MailFlg' => $row['Rct_MailFlg'],
                ':StopClaimFlg' => $row['StopClaimFlg'],
                ':MailPaymentSoonDate' => $row['MailPaymentSoonDate'],
                ':MailLimitPassageDate' => $row['MailLimitPassageDate'],
                ':MailLimitPassageCount' => $row['MailLimitPassageCount'],
                ':ReturnClaimFlg' => $row['ReturnClaimFlg'],
                ':RemindClass' => $row['RemindClass'],
                ':TouchHistoryFlg' => $row['TouchHistoryFlg'],
                ':BriefNote' => $row['BriefNote'],
                ':LonghandLetter' => $row['LonghandLetter'],
                ':VisitFlg' => $row['VisitFlg'],
                ':FinalityCollectionMean' => $row['FinalityCollectionMean'],
                ':FinalityRemindDate' => $row['FinalityRemindDate'],
                ':FinalityRemindOpId' => $row['FinalityRemindOpId'],
                ':PromPayDate' => $row['PromPayDate'],
                ':ClaimStopReleaseDate' => $row['ClaimStopReleaseDate'],
                ':LetterClaimStopFlg' => $row['LetterClaimStopFlg'],
                ':MailClaimStopFlg' => $row['MailClaimStopFlg'],
                ':InstallmentPlanAmount' => $row['InstallmentPlanAmount'],
                ':OutOfAmends' => $row['OutOfAmends'],
                ':OrderRegisterMethod' => $row['OrderRegisterMethod'],
                ':ApiUserId' => $row['ApiUserId'],
                ':CreditConditionMatchData' => $row['CreditConditionMatchData'],
                ':Cnl_ReturnSaikenCancelFlg' => $row['Cnl_ReturnSaikenCancelFlg'],
                ':PrintedTransBeforeCancelled' => $row['PrintedTransBeforeCancelled'],
                ':Deli_ConfirmArrivalFlg' => $row['Deli_ConfirmArrivalFlg'],
                ':Deli_ConfirmArrivalDate' => $row['Deli_ConfirmArrivalDate'],
                ':CombinedClaimTargetStatus' => $row['CombinedClaimTargetStatus'],
                ':CombinedClaimParentFlg' => $row['CombinedClaimParentFlg'],
                ':Jintec_Flags' => $row['Jintec_Flags'],
                ':OemId' => $row['OemId'],
                ':Oem_OrderId' => $row['Oem_OrderId'],
                ':Oem_Note' => $row['Oem_Note'],
                ':OemBadDebtSeq' => $row['OemBadDebtSeq'],
                ':OemBadDebtType' => $row['OemBadDebtType'],
                ':T_OrderClass' => $row['T_OrderClass'],
                ':T_OrderAutoCreditJudgeClass' => $row['T_OrderAutoCreditJudgeClass'],
                ':ServiceTargetClass' => $row['ServiceTargetClass'],
                ':ServiceExpectedDate' => $row['ServiceExpectedDate'],
                ':DailySummaryFlg' => $row['DailySummaryFlg'],
                ':PendingReasonCode' => $row['PendingReasonCode'],
                ':ClaimSendingClass' => $row['ClaimSendingClass'],
                ':P_OrderSeq' => $row['P_OrderSeq'],
                ':CancelBefDataStatus' => $row['CancelBefDataStatus'],
                ':Tel30DaysFlg' => $row['Tel30DaysFlg'],
                ':Tel90DaysFlg' => $row['Tel90DaysFlg'],
                ':NewSystemFlg' => $row['NewSystemFlg'],
                ':CreditReplyDate' => $row['CreditReplyDate'],
                ':OemClaimTransDate' => $row['OemClaimTransDate'],
                ':OemClaimTransFlg' => $row['OemClaimTransFlg'],
                ':ConfirmWaitingFlg' => $row['ConfirmWaitingFlg'],
                ':CreditNgHiddenFlg' => $row['CreditNgHiddenFlg'],
                ':Incre_JudgeScoreTotal' => $row['Incre_JudgeScoreTotal'],
                ':Incre_CoreScoreTotal' => $row['Incre_CoreScoreTotal'],
                ':Incre_ItemScoreTotal' => $row['Incre_ItemScoreTotal'],
                ':Incre_NoteScore' => $row['Incre_NoteScore'],
                ':Incre_PastOrderScore' => $row['Incre_PastOrderScore'],
                ':Incre_UnpaidScore' => $row['Incre_UnpaidScore'],
                ':Incre_NonPaymentScore' => $row['Incre_NonPaymentScore'],
                ':Incre_IdentityDocumentScore' => $row['Incre_IdentityDocumentScore'],
                ':Incre_MischiefCancelScore' => $row['Incre_MischiefCancelScore'],
                ':Chg_NonChargeFlg' => $row['Chg_NonChargeFlg'],
                ':Dmg_DecisionUseAmount' => $row['Dmg_DecisionUseAmount'],
                ':Dmg_DecisionClaimFee' => $row['Dmg_DecisionClaimFee'],
                ':Dmg_DecisionDamageInterestAmount' => $row['Dmg_DecisionDamageInterestAmount'],
                ':Dmg_DecisionAdditionalClaimFee' => $row['Dmg_DecisionAdditionalClaimFee'],
                ':ReverseOrderId' => $row['ReverseOrderId'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定された条件でレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param array $conditionArray
	 */
	public function saveUpdateWhere($data, $conditionArray)
	{
	    $prm = array();
        $sql  = " SELECT * FROM T_Order WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }

        $stm = $this->_adapter->query($sql);

        $ri = $stm->execute($prm);

        foreach ($ri AS $row) {
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $row)) {
                    $row[$key] = $value;
                }
            }

            // 指定されたレコードを更新する
            $this->saveUpdate($row, $row['OrderSeq']);
        }
	}

	/**
	 * 初期値として必要なパラメータを指定して、レコード挿入する
	 *
	 * @param string $receiptDate 注文登録日
	 * @param int $entId 事業者ID
	 * @param int $siteId サイトID
	 * @param data array $data その他のデータを示す連想配列
	 * @return プライマリキーのバリュー
	 */
	public function newRow($receiptDate, $entId, $siteId, $data = array(), $datastatus = 11)
	{
	    if( ! is_array( $data ) ) $data = array();

		if( empty($receiptDate) ) {
			throw new \Exception( 'need receipt order date' );
		}
		if( ((int)$entId) < 0 ) {
			throw new \Exception( 'invalid enterprise id' );
		}
		if( ((int)$siteId) < 0 ) {
			throw new \Exception( 'invalid site id' );
		}

		$data = array_merge( $data, array(
			'OrderId' => $this->generateOrderId($entId),
			'RegistDate' => date("Y-m-d H:i:s"),
			'ReceiptOrderDate' => $receiptDate,
			'EnterpriseId' => $entId,
			'SiteId' => $siteId,
			'DataStatus' => $datastatus,
			'Chg_Status' => 0,
			'Rct_Status' => 0,
			'Rct_MailFlg' => 0,
			'Cnl_CantCancelFlg' => 0,
			'Cnl_Status' => 0,
			'Dmg_DecisionFlg' => 0,
			'Deli_ConfirmArrivalFlg' => 0
		) );

		$data['ReverseOrderId'] = strrev($data['OrderId']);// 反転した注文ID、の設定

        // Mod By Takemasa(NDC) 20150105 Stt 関数saveNewを呼出すよう変更
		//return $this->createRow( $data );
		return $this->saveNew( $data );
        // Mod By Takemasa(NDC) 20150105 End 関数saveNewを呼出すよう変更
	}

	/**
	 * OrderIdに指定するID値を作成する
	 *
	 * @return string
	 */
	public function generateOrderId($entId = null)
	{
	    // プレフィックス確定にOEM設定を適用（2014.5.29 eda）
        // → ついでに有名無実のサフィックスも廃止
        $entTable = new TableEnterprise($this->_adapter);
        $prefix = nvl($entTable->getOrderIdPrefix($entId), self::ORDER_ID_PREFIX);

        // ID部に適用するシーケンス値を生成
        $seq_manager = new SequenceGeneral($this->_adapter);
        $seq = sprintf( '%08d', $seq_manager->nextValue( self::ORDER_ID_SEQ_NAME ) );

        return sprintf('%s%s', $prefix, $seq);
	}

	/**
	 * 指定された立替振込管理データSeqを持つ注文データを立替実行済みにする。
	 *
	 * @param int $payingControlSeq 立替振込管理データSeq
	 * @param string $execDate 実行日 'yyyy-MM-dd'書式で通知
	 * @param string $userId ユーザーID
	 * @return array 処理した注文Seqの配列
	 */
	public function execCharge($payingControlSeq, $execDate, $userId)
	{
	    $ri = $this->findOrder(array('Chg_Seq' => $payingControlSeq));

        foreach($ri as $data) {
            $result[] = $data['OrderSeq'];

            $sql = " UPDATE T_Order SET Chg_ExecDate = :Chg_ExecDate, UpdateDate = :UpdateDate, UpdateId = :UpdateId WHERE OrderSeq = :OrderSeq ";

            $stm = $this->_adapter->query($sql);

            $prm = array(
                    ':Chg_ExecDate' => $execDate,
                    ':UpdateDate' => date('Y-m-d H:i:s'),
                    ':UpdateId' => $userId,
                    ':OrderSeq' => $data['OrderSeq'],
            );

            $stm->execute($prm);
        }

        return $result;
	}

	/**
	 * 指定日付の注文登録数を取得する。
	 *
	 * @param string $date 日付 'yyyy-MM-dd'書式で通知
	 * @return int 注文登録数
	 */
	public function getOrderCount($date)
	{
	    $query = sprintf("SELECT COUNT(*) AS CNT FROM T_Order WHERE RegistDate BETWEEN '%s' AND '%s 23:59:59'", $date, $date);
        return (int)$this->_adapter->query($query)->execute(null)->current()['CNT'];
	}

	/**
	 * 本日の注文登録件数を取得する。
	 *
	 * @return int 注文登録数
	 */
	public function getOrderCountToday()
	{
	    return $this->getOrderCount(date('Y-m-d'));
	}

	/**
	 * 昨日の注文登録件数を取得する。
	 *
	 * @return int 注文登録数
	 */
	public function getOrderCountYesterday()
	{
	    return $this->getOrderCount(date('Y-m-d', strtotime('-1 day')));
	}

	/**
	 * 指定日付の社内与信確定件数を取得する。
	 *
	 * @param string $date 日付 'yyyy-MM-dd'書式で通知
	 * @return int 社内与信確定件数
	 */
	public function getCreditCount($date)
	{
	    $sql = " SELECT COUNT(*) AS CNT FROM T_Order WHERE Incre_Status != 0 AND Incre_DecisionDate = :Incre_DecisionDate ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Incre_DecisionDate' => $date,
        );

        return (int)$stm->execute($prm)->current()['CNT'];
	}

	/**
	 * 本日の社内与信確定件数を取得する。
	 *
	 * @return int 社内与信確定件数
	 */
	public function getCreditCountToday()
	{
	    return $this->getCreditCount(date('Y-m-d'));
	}

	/**
	 * 昨日の社内与信確定件数を取得する。
	 *
	 * @return int 社内与信確定件数
	 */
	public function getCreditCountYesterday()
	{
	    return $this->getCreditCount(date('Y-m-d', strtotime('-1 day')));
	}


// Del By Takemasa(NDC) 20150721 Stt 未使用故コメントアウト化
// 	/**
// 	 * 未入金（入金待ち）額を取得する。
// 	 *
// 	 * @return int 未入金額
// 	 */
// 	public function getUnpayment()
// Del By Takemasa(NDC) 20150721 End 未使用故コメントアウト化

	/**
	 * 指定された注文の請求額を取得する。
	 *
	 * @param int $orderSeq 注文Seq
	 * @return int 請求額
	 */
	public function getUnpaymentForOrder($orderSeq)
	{
//zzz [請求関連]は廃止されます(20150410_1310)
	    $query = "
			SELECT
			    UseAmount + Clm_L_DamageInterestAmount + Clm_L_ClaimFee + Clm_L_AdditionalClaimFee AS ClaimAmountTotal
			FROM
			    T_Order
			WHERE
				OrderSeq = :OrderSeq
		";

        return (int)$this->_adapter->query($query)->execute(array(':OrderSeq' => $orderSeq))->current()['ClaimAmountTotal'];
	}

	/**
	 * 指定されたデータステータスの件数を取得する（キャンセルは除外）
	 *
	 * @param int $dataStatus データステータス
	 * @return int 件数
	 */
	public function getCountDs($dataStatus)
	{
	    $sql = " SELECT COUNT(*) AS CNT FROM T_Order WHERE DataStatus = :DataStatus AND Cnl_Status = 0 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':DataStatus' => $dataStatus,
        );

        return (int)$stm->execute($prm)->current()['CNT'];
	}

	/**
	 * cbadminトップページ向けにデータステータスごとの未キャンセル件数を集計する
	 *
	 * @param null|array $ds_list 取得対象のデータステータスを格納した配列。省略時は11, 15, 21
	 * @return array キーにデータステータス、値に対応件数が格納された連想配列
	 */
	public function getCountDsForTop($ds_list = array(11, 15, 21), $oemId = 0)
	{
	    if(!is_array($ds_list)) $ds_list = array((int)$ds_list);
        $result = array();
        foreach($ds_list as $ds) $result[$ds] = 0;

        $prm = array();
        $sql  = " SELECT DataStatus, COUNT(*) AS Cnt FROM T_Order ";
        $sql .= " WHERE  DataStatus IN (" . implode(",", $ds_list) . ") ";
        $sql .= " AND    Cnl_Status = 0 ";
        if($oemId !== 0) {
            $sql .= ' AND OemId = :OemId ';
            $prm += array(':OemId' => $oemId);
        }
        $sql .= ' GROUP BY DataStatus';

        $ri = $this->_adapter->query($sql)->execute($prm);
        foreach($ri as $row) {
            $ds = $row['DataStatus'];
            $result[$ds] = (int)$row['Cnt'];
        }

        return $result;
	}

	/**
	 * 社内与信NGになっている注文の与信確定識別シーケンスを指定された値で更新する。
	 *
	 * @param string $dsi 与信確定識別シーケンス
	 * @return int 更新したレコード数
	 */
	public function setDecSeqId($dsi)
	{
	    $sql = " UPDATE T_Order SET Dmi_DecSeqId = :Dmi_DecSeqId WHERE Dmi_DecSeqId = '_CREDIT_NG_' ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Dmi_DecSeqId' => $dsi,
        );

        $ri = $stm->execute($prm);

        return $ri->getAffectedRows();
	}

	/**
	 * 「もうすぐお支払」メール送信対象注文Seqを取得する。
	 *
	 * @return ResultInterface
	 * @see 2008/04/28に請求ストップの条件を追加。
	 * @see 2008/07/14 請求ストップをメール請求ストップに変更。
	 */
	public function getPaymentSoonMailTarget()
	{
	    $mdlsp = new TableSystemProperty($this->_adapter);
	    $days = intval($mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PaymentSoonDays'));
	    $days = -1 * $days;

	    $query = "
			SELECT
			    T_Order.P_OrderSeq AS OrderSeq
			FROM
			    T_Order
            INNER JOIN T_ClaimControl ON T_ClaimControl.OrderSeq = T_Order.P_OrderSeq
            INNER JOIN T_Customer C ON C.OrderSeq = T_Order.OrderSeq
            INNER JOIN T_EnterpriseCustomer EC ON EC.EntCustSeq = C.EntCustSeq
            INNER JOIN T_ManagementCustomer MC ON MC.ManCustId = EC.ManCustId
			WHERE
			    DATEDIFF(T_ClaimControl.F_LimitDate, CURDATE()) = " . $days . " AND
			    T_Order.MailPaymentSoonDate is null AND
				((T_Order.MailClaimStopFlg is null OR T_Order.MailClaimStopFlg = 0) AND IFNULL(MC.RemindStopFlg, 0) = 0) AND
			    T_Order.DataStatus = 51 AND
				T_Order.Cnl_Status = 0 AND
				T_ClaimControl.CreditTransferFlg = 0
	        GROUP BY T_Order.P_OrderSeq
		";

        return $this->_adapter->query($query)->execute(null);
	}

    /**
     * 「もうすぐお支払」メール送信対象注文Seqを取得する。
     *
     * @return ResultInterface
     * @see 2008/04/28に請求ストップの条件を追加。
     * @see 2008/07/14 請求ストップをメール請求ストップに変更。
     */
    public function getCreditTransferSoonMailTarget()
    {
        $mdlsp = new TableSystemProperty($this->_adapter);
        $days = intval($mdlsp->getValue('[DEFAULT]', 'systeminfo', 'CreditTransferSoonDays'));
//        $days = -1 * $days;

        $query = "
			SELECT
			    T_Order.P_OrderSeq AS OrderSeq
			FROM
			    T_Order
            INNER JOIN T_ClaimControl ON T_ClaimControl.OrderSeq = T_Order.P_OrderSeq
            INNER JOIN T_Customer C ON C.OrderSeq = T_Order.OrderSeq
            INNER JOIN T_EnterpriseCustomer EC ON EC.EntCustSeq = C.EntCustSeq
            INNER JOIN T_ManagementCustomer MC ON MC.ManCustId = EC.ManCustId
			WHERE
			    DATEDIFF(T_ClaimControl.F_CreditTransferDate, CURDATE()) = " . $days . " AND
			    T_ClaimControl.F_CreditTransferDate is not null AND
			    T_Order.MailPaymentSoonDate is null AND
				((T_Order.MailClaimStopFlg is null OR T_Order.MailClaimStopFlg = 0) AND IFNULL(MC.RemindStopFlg, 0) = 0) AND
			    T_Order.DataStatus = 51 AND
				T_Order.Cnl_Status = 0 AND
				T_ClaimControl.CreditTransferFlg != 0
	        GROUP BY T_Order.P_OrderSeq
		";

        return $this->_adapter->query($query)->execute(null);
    }

    /**
	 * 「支払期限経過」メール送信対象注文Seqを取得する。
	 *
	 * @return ResultInterface
	 */
	public function getLimitPassageMailTarget()
	{
        $sql = <<<EOQ
SELECT T_Order.P_OrderSeq
,      MAX(IFNULL(MailLimitPassageCount, 0)) AS MailLimitPassageCount
FROM   T_Order
	   INNER JOIN T_ClaimControl ON (T_ClaimControl.OrderSeq = T_Order.OrderSeq)
WHERE  1 = 1
AND	   DATEDIFF(F_LimitDate, CURDATE()) <= -9
AND	   (DATEDIFF(CURDATE(), MailLimitPassageDate) >= 7 OR MailLimitPassageDate is null)
AND	   (MailClaimStopFlg is null OR MailClaimStopFlg = 0)
AND	   DataStatus = 51
AND	   Cnl_Status = 0
AND    IFNULL((SELECT COUNT(1) FROM T_Order O WHERE O.P_OrderSeq = T_Order.P_OrderSeq AND O.MailClaimStopFlg = 1 GROUP BY O.P_OrderSeq), 0) = 0
GROUP BY T_Order.P_OrderSeq
EOQ;
        return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 指定された注文がキャンセルされているか否かをチェックする。
	 *
	 * @param int $orderSeq 注文Seq
	 * @return boolean true:キャンセルされている　false:キャンセルされていない
	 */
	public function isCanceled($orderSeq)
	{
	    $sql = " SELECT Cnl_Status FROM T_Order WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
        );

        $ri = $stm->execute($prm);

        return ((int)$ri->current()['Cnl_Status'] > 0) ? true : false;
	}

	/**
	 * 請求ストップ解除日を経過した注文の請求ストップ解除
	 *
	 */
	public function turnOffClaimFlg($userId)
	{
	    $sql = "
			UPDATE
			    T_Order
			SET
			    LetterClaimStopFlg = 0,
			    MailClaimStopFlg = 0,
	            UpdateDate = :UpdateDate,
	            UpdateId = :UpdateId
			WHERE
			    ClaimStopReleaseDate <= CURRENT_DATE() AND
			    (LetterClaimStopFlg = 1 OR MailClaimStopFlg = 1)
		";

        return $this->_adapter->query($sql)->execute(array(
            ':UpdateDate' => date('Y-m-d H:i:s'),
            ':UpdateId' => $userId
        ));
	}

	/**
	 * 指定範囲の有効なOrderSeq配列を取得する。
	 *
	 * @param int $startSeq 開始番号
	 * @param int $endSeq 終了番号
	 * @return ResultInterface
	 */
	public function getOrderSeqArray($startSeq, $endSeq)
	{
	    $query = "
			SELECT
			    OrderSeq
			FROM
			    T_Order
			WHERE
				OrderSeq BETWEEN $startSeq AND $endSeq
		";

        return $this->_adapter->query($query)->execute(null);
	}

	/**
	 * 指定されたカラムの値にNULLをセットする。
	 *
	 * @param array $columns
	 * @param int $orderSeq
	 */
	public function setNullValue($columns, $orderSeq)
	{
	    $sql = "UPDATE T_Order SET ";

	    $isFirstTimeOn = true;// 初回か？フラグ
		foreach($columns as $column)
		{
		    if ($isFirstTimeOn) {
		        $sql .= ($column . ' = NULL ');
		        $isFirstTimeOn = false;// フラグを落とす
		    }
		    else {
		        $sql .= (' , ' . $column . ' = NULL ');
		    }
		}

        $sql .= " WHERE OrderSeq = :OrderSeq ";

        return $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orderSeq));
	}

	/**
	 * 指定シーケンスの注文データのステータスを取得する
	 *
	 * @param int $orderSeq 注文シーケンス
	 * @param int 指定データのDataStatus
	 */
	public function getDataStatus($orderSeq) {
        $sql = " SELECT DataStatus FROM T_Order WHERE OrderSeq = :OrderSeq ";
        $ri = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orderSeq));
        return ($ri->count() > 0) ? (int)$ri->current()['DataStatus'] : null;
	}

    /**
     * 指定シーケンスのOEMIDを取得する
     *
     * @param int $orderSeq 注文シーケンス
     * @param int 指定データのOemId
     */
    public function getOemId($orderSeq) {
        $sql = " SELECT OemId FROM T_Order WHERE OrderSeq = :OrderSeq ";
        $ri = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orderSeq));
        return ($ri->count() > 0) ? (int)$ri->current()['OemId'] : null;
    }

    /**
     * 指定シーケンスの事業者IDを取得する
     *
     * @param int $orderSeq 注文シーケンス
     * @param int 指定データのEnterpriseId
     */
    public function getEnterpriseId($orderSeq) {
        $sql = " SELECT EnterpriseId FROM T_Order WHERE OrderSeq = :OrderSeq ";
        $ri = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orderSeq));
        return ($ri->count() > 0) ? (int)$ri->current()['EnterpriseId'] : null;
    }

	/**
	 * 指定された任意注文番号を取得する
	 *
	 * @param string $entId  任意注文番号
	 * @return $entId
	 */
	public function searchEntId($entOrderId, $entId) {

        $sql  = " SELECT Ent_OrderId FROM T_Order ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    EnterpriseId = :EnterpriseId ";
        $sql .= " AND    Ent_OrderId = :Ent_OrderId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $entId,
                ':Ent_OrderId' => $entOrderId,
        );

        $ri = $stm->execute($prm);
        if ($ri->count() > 0) {
            return $ri->current()['Ent_OrderId'];
        }
        return '';
	}

	/**
	 * 指定された事業者IDの返却キャンセル数を取得する
	 *
	 * @param string $entId  任意注文番号
	 * @return 返却キャンセル数
	 */
	public function searchSaikenCount($entid) {
	    $sql = " SELECT COUNT(*) AS saikenCount FROM T_Order WHERE EnterpriseId = :EnterpriseId AND Cnl_ReturnSaikenCancelFlg = 1";
	    return $this->_adapter->query($sql)->execute(array(':EnterpriseId' => $entid))->current()['saikenCount'];
	}

	/**
	 * 与信完了メールを送信する
	 *
	 * @param string $decSeqId 送信対象となる与信確定識別ID
	 * @return ResultInterface
	 */
	public function getEnterpriseSiteByDmiSeqId($decSeqId)
	{
	    $query = "
				SELECT
				    EnterpriseId,
				    SiteId,
				    COUNT(*) AS CNT
				FROM
				    T_Order
				WHERE
				    Dmi_DecSeqId = :Dmi_DecSeqId
				GROUP BY
				    EnterpriseId,
				    SiteId
				ORDER BY
				    EnterpriseId,
				    SiteId
	 		";

        return $this->_adapter->query($query)->execute(array(':Dmi_DecSeqId' => $decSeqId));
	}

	// 以下与信自動に伴う判定
	/**
	 * 条件に従って件数を取得する
	 * @param $where
	 * @return int
	 */
	public function findOrderCustomerCnt($where) {
	    $query = "SELECT COUNT(*) AS Cnt FROM T_Order WHERE " . $where;
        return (int)$this->_adapter->query($query)->execute(null)->current()['Cnt'];
	}

	/*
	 * 過去二年間で注文があったかどうかのデータ件数を取得する
	 * @param $pastOrders 対象注文以外の注文Seq配列
	 * @return int 件数
	 */
	public function findOrderCustomerByTwoYearsOrderCnt($pastOrders) {
	    $where = sprintf(" RegistDate >= '%s' AND OrderSeq IN (%s)", date("Y-m-d",strtotime("-2 year")), $pastOrders);

		return $this->findOrderCustomerCnt($where);
	}

	/*
	 * 過去二年間で正規化された住所もしくは電話番号に一致する債権返却キャンセルのデータ件数を取得する
	 * @param $pastOrders 過去2年間の対象注文以外の注文配列
	 * @return int 件数
	 */
	public function findOrderCustomerBySaikenCancelCnt($pastOrders) {
	    $where = sprintf(" Cnl_ReturnSaikenCancelFlg = 1 AND RegistDate >= '%s' AND OrderSeq IN (%s)", date("Y-m-d",strtotime("-2 year")), $pastOrders);

		return $this->findOrderCustomerCnt($where);
	}

	/*
	 * 過去二年間で正規化された住所もしくは電話番号に一致する不払データ件数を取得する
	 * @param $pastOrders 過去2年間の対象注文以外の注文配列
	 * @return int 件数
	 */
	public function findOrderCustomerByNoRecDamagedCnt($pastOrders) {
//zzz [請求関連]は廃止されます(20150410_1310)
	    $where = sprintf(" DataStatus <> 91 AND Cnl_Status = 0 AND Rct_Status = 0 AND Clm_F_LimitDate < '%s' AND RegistDate >= '%s' AND OrderSeq IN (%s)",
													date("Y-m-d"), date("Y-m-d",strtotime("-2 year")), $pastOrders);

		return $this->findOrderCustomerCnt($where);
	}

	/* 過去二年間で正規化された住所もしくは電話番号に一致する与信NG件数を取得する
	 * @param $pastOrders 過去2年間の対象注文以外の注文配列
	 * @return int 件数
	 */
	public function findOrderCustomerByNgCnt($pastOrders) {
	    $where = sprintf(" CloseReason = 3 AND RegistDate >= '%s' AND OrderSeq IN (%s)", date("Y-m-d",strtotime("-2 year")), $pastOrders);

		return $this->findOrderCustomerCnt($where);
	}

	/* 過去二年間で正規化された住所もしくは電話番号に一致する未クローズ件数を取得する
	 * @param $pastOrders 過去2年間の対象注文以外の注文配列
	 * @return int 件数
	 */
	public function findOrderCustomerByNoCloseCnt($pastOrders) {
	    $where = sprintf(" DataStatus <> 91 AND RegistDate >= '%s' AND OrderSeq IN (%s)", date("Y-m-d",strtotime("-2 year")), $pastOrders);

		return $this->findOrderCustomerCnt($where);
	}

	/* 過去二年間で正規化された住所もしくは電話番号に一致する支払遅れ5日以上件数を取得する
	 * @param $pastOrders 過去2年間の対象注文以外の注文配列
	 * @return int 件数
	 */
	public function findOrderCustomerByLateRecCnt($pastOrders) {
//zzz [請求受注関連]は廃止されます(20150410_1310)
	    $where = sprintf(" Rct_Status = 1 AND Rct_ReceiptDate >= DATE_ADD(Clm_F_LimitDate, INTERVAL 5 DAY) AND RegistDate >= '%s' AND OrderSeq IN (%s)"
											, date("Y-m-d",strtotime("-2 year")), $pastOrders);

		return $this->findOrderCustomerCnt($where);
	}

	/* 過去二年間で正規化された住所もしくは電話番号に一致する合計額の最大値を取得する
	 * @param $pastOrders 過去2年間の対象注文以外の注文配列
	 * @return int 最大値
	 */
	public function findOrderCustomerByMaxUseAmountCnt($pastOrders) {
	    $where = sprintf(" RegistDate >= '%s' AND OrderSeq IN (%s)", date("Y-m-d",strtotime("-2 year")), $pastOrders);

		$query = "SELECT max(UseAmount) AS maxuseamount FROM T_Order WHERE " . $where;

        return (int)$this->_adapter->query($query)->execute(null)->current()['maxuseamount'];
	}

	/* 過去二年間で正規化された住所もしくは電話番号に一致する直前のキャンセル状態を取得する
	 * @param $pastOrders 過去2年間の対象注文以外の注文配列
	 * @return int 件数
	 */
	public function findOrderCustomerByOneBeforeCnlCnt($pastOrders) {
	    $where = sprintf("OrderSeq = (SELECT max(OrderSeq) FROM T_Order WHERE OrderSeq IN (%s)) AND CloseReason = 2 AND DataStatus = 91 "
							,$pastOrders);

		return $this->findOrderCustomerCnt($where);
	}

	/**
	 * Deli_ConfirmArrivalFlgがNULLの一覧を返す
	 * @return ResultInterface
	 */
	public function getDeliConfirmArrivalFlgIsNull($limit = 1) {
	    $query = sprintf("SELECT OrderSeq from T_Order where Deli_ConfirmArrivalFlg is Null ORDER BY OrderSeq  LIMIT %s", $limit);

        return $this->_adapter->query($query)->execute(null);
	}

	/**
	 * 複数の注文Seqから最も古い注文Seqを取得する
	 *
	 * @param array $arrOrderSeqs 注文Seqの配列
	 * @return 最も古い注文Seq
	 */
	public function getOldestOrder($arrOrderSeqs)
	{
	    $query = sprintf("SELECT MIN(OrderSeq) AS OrderSeq FROM T_Order WHERE OrderSeq IN (%s)", BaseGeneralUtils::ArrayToCsv($arrOrderSeqs));

        return $this->_adapter->query($query)->execute(null)->current()['OrderSeq'];
	}

	/**
	 * 複数の注文Seqから複数の（特定の）注文情報を一括して取得する
	 * 取得する情報　：　注文Seq、注文ID、事業者ID、データステータス
	 *
	 * @param ResultInterface 注文Seqの配列
	 */
	public function getMultiOrders($arrOrderSeqs)
	{
	    $query = sprintf("SELECT OrderSeq, OrderId, EnterpriseId, DataStatus FROM T_Order WHERE OrderSeq IN (%s)", BaseGeneralUtils::ArrayToCsv($arrOrderSeqs));

        return $this->_adapter->query($query)->execute(null);
	}

	/**
	 * 請求取りまとめステータスを更新する
	 *
	 *
	 * @param $entId 事業者ID
	 */
	public function updateCombinedClaimStatus($entId)
	{
	    $sql = " UPDATE T_Order SET CombinedClaimTargetStatus = 0 WHERE EnterpriseId = :EnterpriseId and CombinedClaimTargetStatus in (1,2) ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $entId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定された請求取りまとめ対象かの判定を行う
	 *
	 * @param int $orderSeq 注文Seq
	 * @return boolean true:取りまとめ対象　false:取りまとめ対象ではない
	 */
	public function isClaimCombinedTarget($orderSeq)
	{
	    $sql = " SELECT CombinedClaimTargetStatus FROM T_Order WHERE OrderSeq = :OrderSeq ";
        $ri = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orderSeq));
        $row = $ri->current();
        return ($row['CombinedClaimTargetStatus'] == 1 || $row['CombinedClaimTargetStatus'] == 2) ? true : false;
	}

	/* 過去二年間で正規化された住所もしくは電話番号に一致する支払遅れ45日以上件数を取得する
	 * @param $pastOrders 過去2年間の対象注文以外の注文配列
	 * @return int 件数
	 */
	public function findOrderCustomerByLateRecCnt45($pastOrders) {
//zzz [請求受注関連]は廃止されます(20150410_1310)
	    $where = sprintf(" Rct_Status = 1 AND Rct_ReceiptDate >= DATE_ADD(Clm_F_LimitDate, INTERVAL 45 DAY) AND RegistDate >= '%s' AND OrderSeq IN (%s)"
											, date("Y-m-d",strtotime("-2 year")), $pastOrders);

		return $this->findOrderCustomerCnt($where);
	}

	/**
	 * 指定の注文が入金待ちかを判断する
	 * 取りまとめ注文の場合、入金待ち状態の注文が含まれていればOK
	 *
	 * @param int $orderSeq 注文SEQ
	 * @return boolean 指定注文が入金待ち状態ならtrue、それ以外はfalse
	 */
	public function isReceiptReady($orderSeq) {
        $sql = " SELECT DataStatus, Cnl_Status FROM T_Order WHERE P_OrderSeq = :OrderSeq ";
        $ri = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orderSeq));
        if (!($ri->count() > 0)) {
            return false;
        }
        $result = false;
        $datas = ResultInterfaceToArray($ri);
        foreach($datas as $row) {
            if ($row['DataStatus'] == 51 && $row['Cnl_Status'] == 0) {
                $result = true;
                break;
            }
        }
        return $result;
	}

	/**
	 * oemadminトップページ向けに請求書発行中件数を集計する
	 *
	 * @param int $oemId OEMID
	 * @return 請求書発行中件数
	 */
	public function getToPrintCountOem($oemId)
	{
	    if ($oemId == null || $oemId < 1) {
	        return 0;
	    }

	    $query = <<<EOQ
SELECT COUNT(DISTINCT o.OrderSeq) AS Cnt
FROM   T_Order o
       INNER JOIN T_ClaimHistory ch ON (ch.OrderSeq = o.OrderSeq)
WHERE  o.OemId = :OemId
AND    o.Cnl_Status = 0
AND    o.P_OrderSeq = o.OrderSeq
AND    ch.ValidFlg = 1
AND    ch.PrintedFlg = 0
EOQ;

	    return $this->_adapter->query($query)->execute(array(':OemId' => $oemId))->current()['Cnt'];
	}

	/**
	 * oemadminトップページ向けに着荷確認待ち件数を集計する
	 *
	 * @param int $oemId OEMID
	 * @return 着荷確認待ち件数
	 */
	public function getArrivalCountOem($oemId)
	{
	    if ($oemId == null || $oemId < 1) {
	        return 0;
	    }

	    $query = <<<EOQ
SELECT COUNT(*) AS Cnt
FROM T_Order
WHERE OemId = :OemId
AND Cnl_Status = 0
AND DataStatus IN (51, 61)
AND Deli_ConfirmArrivalFlg <> 1
EOQ;

	    return $this->_adapter->query($query)->execute(array(':OemId' => $oemId))->current()['Cnt'];
	}

    /**
	 * oemadminトップページ向けに入金確認待ち件数を集計する
	 *
	 * @param int $oemId OEMID
	 * @return 入金確認待ち件数
	 */
	public function getDepositCountOem($oemId)
	{
//zzz [請求関連]は廃止されます(20150410_1310)
	    if ($oemId == null || $oemId < 1) {
            return 0;
        }

        $query = <<<EOQ
SELECT Rct_DepositClearFlg, COUNT(*) AS Cnt
FROM T_Order
WHERE Rct_DepositClearFlg = 0
AND DataStatus = 51
EOQ;
        $query .= ' AND OemId = :OemId ';
        $query .= ' GROUP BY Rct_DepositClearFlg';

        return $this->_adapter->query($query)->execute(array(':OemId' => $oemId))->current()['Cnt'];
	}

	/**
	 * 指定の注文が、着荷確認によって立替条件をクリアするかを判断する。
	 * 判断には配送方法と補償外フラグが勘案される
	 *
	 * @param int $orderSeq 注文SEQ
	 * @return boolean 対象注文が着荷確認によって立替条件をクリアする場合はtrue、入金確認でクリアする場合はfalse
	 */
	public function judgePayChargeByArrivalConfirm($orderSeq) {
	    $q = <<<EOQ
SELECT
	o.OrderSeq,
	o.OrderId,
	s.Deli_DeliveryMethod,
	s.Deli_DeliveryMethodName,
	d.PayChgCondition,
	IFNULL(o.OutOfAmends, 0) AS OutOfAmends,
	(CASE
		WHEN d.PayChgCondition = 1 AND IFNULL(o.OutOfAmends, 0) = 0 THEN 1
		ELSE 0
	END) AS PayChargeCondition
FROM
	T_Order o INNER JOIN
	T_OrderSummary s ON s.OrderSeq = o.OrderSeq LEFT OUTER JOIN
	M_DeliveryMethod d ON d.DeliMethodId = s.Deli_DeliveryMethod
WHERE
	o.OrderSeq = :OrderSeq
ORDER BY
	o.OrderSeq
EOQ;
        $ri = $this->_adapter->query($q)->execute(array(':OrderSeq' => $orderSeq));
        if ($ri->count() > 0) {
            return ($ri->current()['PayChargeCondition'] == 1) ? true : false;
        }
        return false;
	}

	/**
	 * 指定注文のOEM固有注文IDを更新する
	 *
	 * @param int $seq 注文SEQ
	 * @param int $oemOrderId
	 */
	public function updateOemOrderId($seq, $oemOrderId)
	{
	    $oemArray = array('Oem_OrderId' => $oemOrderId);

		$this->saveUpdate($oemArray, $seq);
	}

// Del By Takemasa(NDC) 20150721 Stt 未使用故コメントアウト化
//     /**
//      * 該当のOEMの債権明細請求データ取得
//      * @param int $oem_id　OemID
//      * @param string $search_from 検索開始日 'yyyy-MM-dd'書式で通知
//      * @param string $search_to 検索終了日 'yyyy-MM-dd'書式で通知
//      * @return ResultInterface
//      */
//     public function getOrderOemClaim($oem_id = 0,$search_from,$search_to)
//
//     /**
//      * 該当のOEMの入金情報取得
//      * @param int $oem_id　OemID
//      * @param string $search_from 検索開始日 'yyyy-MM-dd'書式で通知
//      * @param string $search_to 検索終了日 'yyyy-MM-dd'書式で通知
//      * @return ResultInterface
//      *
//      */
//     public function getOrderOemPayment($oem_id,$search_from,$search_to)
//
//     /**
//      * 該当のOEMの債権情報取得
//      * @param int $oem_id　OemID
//      * @param string $search_from 検索開始日 'yyyy-MM-dd'書式で通知
//      * @param string $search_to 検索終了日 'yyyy-MM-dd'書式で通知
//      * @return ResultInterface
//      *
//      */
//     public function getOrderOemClaimDetail($oem_id,$search_from,$search_to)
// Del By Takemasa(NDC) 20150721 End 未使用故コメントアウト化

    /**
     * 該当注文の取りまとめ処理を実施
     * @param string $orderSeqs 注文Seq
     * @param unknown $updateId　更新者
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function updateCombinedClaimTargetStatus($orderSeqs, $updateId)
    {
        $sql = <<<EOQ
UPDATE 	T_Order
SET		CombinedClaimTargetStatus = CombinedClaimTargetStatus + 10,
		UpdateDate = '%s',
        UpdateId = %d
WHERE	OrderSeq IN (%s)
EOQ;
        return $this->_adapter->query(sprintf($sql, date('Y-m-d H:i:s'), $updateId, $orderSeqs))->execute();
    }

    /**
     * 指定している注文データの内紙請求ストップしたデータの件数を取得
     * @param string $orderSeqs 注文Seq
     * @return number 該当データ件数
     */
    public function getLetterClaimStopCnt($orderSeqs)
    {
        $sql = <<<EOQ
SELECT	T_Order.OrderSeq
FROM 	T_Order
WHERE	T_Order.OrderSeq IN ( %s )
AND		T_Order.LetterClaimStopFlg = 1
EOQ;
        return $this->_adapter->query(sprintf($sql, $orderSeqs))->execute() ->count();
    }

    /**
     * 管理顧客番号より過去2年間の注文を取得する
     *
     * @param string $regUnitingAddress 購入者.正規化された住所
     * @param string $regPhone 購入者.正規化された電話番号
     */
    public function getPastOrderSeqs($regUnitingAddress, $regPhone) {
        $sql  = " SELECT O.OrderSeq ";
        $sql .= " FROM T_Order O ";
        $sql .= " INNER JOIN T_Customer C ON C.OrderSeq = O.OrderSeq ";
        $sql .= " INNER JOIN T_EnterpriseCustomer EC ON EC.EntCustSeq = C.EntCustSeq ";
        $sql .= " INNER JOIN T_ManagementCustomer MC ON MC.ManCustId = EC.ManCustId ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND (MC.RegUnitingAddress = :RegUnitingAddress OR MC.RegPhone = :RegPhone) ";
        $sql .= " AND O.RegistDate >= :RegistDate ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':RegUnitingAddress' => $regUnitingAddress,
                ':RegPhone' => $regPhone,
                ':RegistDate' => date("Y-m-d",strtotime("-2 year")),
        );

        return $stm->execute($prm);
    }

    /**
     * 過去二年間の注文で債権返却キャンセルのデータ件数を取得する
     * @param $pastOrders 過去2年間の対象注文以外の注文配列
     * @return int 件数
     */
    public function findOrderCustomerBySaikenCancelCnt2($pastOrders) {
        $where = sprintf(" Cnl_ReturnSaikenCancelFlg = 1 AND OrderSeq IN (%s)", $pastOrders);

        return $this->findOrderCustomerCnt($where);
    }

    /**
     * 過去二年間の注文の不払日数を取得する
     * @param $pastOrders 過去2年間の対象注文以外の注文配列
     * @return ResultInterface
     */
    public function findOrderCustomerByNonPaymentDays($pastOrders) {
        $query  = sprintf(" SELECT MAX(DATEDIFF('%s', CC.F_LimitDate)) AS Days ",
        date("Y-m-d"));
        $query .= " FROM T_Order O ";
        $query .= " INNER JOIN T_ClaimControl CC ON CC.OrderSeq = O.P_OrderSeq ";
        $query .= " WHERE ";
        $query .= sprintf(" O.DataStatus <> 91 AND O.Cnl_Status = 0 AND CC.F_LimitDate < '%s' AND CC.OrderSeq IN (%s)",
        date("Y-m-d"), $pastOrders);

        return (int)$this->_adapter->query($query)->execute(null)->current()['Days'];
    }

    /**
     * 過去二年間の注文の不払データ件数を取得する
     * @param $pastOrders 過去2年間の対象注文以外の注文配列
     * @return ResultInterface
     */
    public function findOrderCustomerByNonPaymentCnt($pastOrders) {
        $query  = " SELECT COUNT(*) AS Cnt ";
        $query .= " FROM T_Order O ";
        $query .= " INNER JOIN T_ClaimControl CC ON CC.OrderSeq = O.P_OrderSeq ";
        $query .= " WHERE ";
        $query .= sprintf(" O.DataStatus <> 91 AND O.Cnl_Status = 0 AND CC.F_LimitDate < '%s' AND CC.OrderSeq IN (%s)",
        date("Y-m-d"), $pastOrders);

        return (int)$this->_adapter->query($query)->execute(null)->current()['Cnt'];
    }

    /**
     * 過去二年間の注文の不払データ総額を取得する
     * @param $pastOrders 過去2年間の対象注文以外の注文配列
     * @return ResultInterface
     */
    public function findOrderCustomerByNonPaymentAmount($pastOrders) {
        $query  = " SELECT IFNULL(SUM(IFNULL(ClaimedBalance, 0)), 0) AS Amount ";
        $query .= " FROM T_ClaimControl ";
        $query .= " WHERE OrderSeq IN ( ";
        $query .= " SELECT CC.OrderSeq ";
        $query .= " FROM T_Order O ";
        $query .= " INNER JOIN T_ClaimControl CC ON CC.OrderSeq = O.P_OrderSeq ";
        $query .= " WHERE ";
        $query .= sprintf(" O.DataStatus <> 91 AND O.Cnl_Status = 0 AND CC.F_LimitDate < '%s' AND O.OrderSeq IN (%s)",
        date("Y-m-d"), $pastOrders);
        $query .= " ) ";

        return (int)$this->_adapter->query($query)->execute(null)->current()['Amount'];
    }

    /**
     * 過去2年間の購入店舗（サイト）のみでの不払い回数を取得する
     * @param $pastOrders 過去2年間の対象注文以外の注文配列
     * @param $siteId ｻｲﾄID
     * @return ResultInterface
     */
    public function findOrderCustomerByNonPaymentCntForSite($pastOrders, $siteId) {
        $sql = <<<EOQ
SELECT COUNT(1) AS cnt
FROM   T_Order o
       INNER JOIN T_ClaimControl cc
               ON o.P_OrderSeq = cc.OrderSeq
WHERE  cc.OrderSeq IN ($pastOrders)
AND    o.SiteId = :SiteId
AND    o.DataStatus <> 91
AND    o.Cnl_Status = 0
AND    cc.F_LimitDate < :F_LimitDate
EOQ;

        $prm = array(
                ':SiteId' => $siteId,
                ':F_LimitDate' => date("Y-m-d"),
        );

        return (int)$this->_adapter->query($sql)->execute($prm)->current()['cnt'];
    }

    /**
     * 過去2年間の他店舗（サイト）での不払い回数を取得する
     * @param $pastOrders 過去2年間の対象注文以外の注文配列
     * @param $siteId ｻｲﾄID
     * @return ResultInterface
     */
    public function findOrderCustomerByNonPaymentCntForOtherSite($pastOrders, $siteId) {
        $sql = <<<EOQ
SELECT COUNT(1) AS cnt
FROM   T_Order o
       INNER JOIN T_ClaimControl cc
               ON o.P_OrderSeq = cc.OrderSeq
WHERE  cc.OrderSeq IN ($pastOrders)
AND    o.SiteId <> :SiteId
AND    o.DataStatus <> 91
AND    o.Cnl_Status = 0
AND    cc.F_LimitDate < :F_LimitDate
EOQ;

        $prm = array(
                ':SiteId' => $siteId,
                ':F_LimitDate' => date("Y-m-d"),
        );

        return (int)$this->_adapter->query($sql)->execute($prm)->current()['cnt'];
    }

    /**
     * 過去2年間の同じ店舗での購入履歴（NG、キャンセル除く）を取得する
     * @param $pastOrders 過去2年間の対象注文以外の注文配列
     * @param $siteId ｻｲﾄID
     * @return ResultInterface
     */
    public function findOrderCustomerByNonPaymentSiteCnt($pastOrders, $siteId) {
$sql = <<<EOQ
SELECT COUNT(1) AS cnt
FROM   T_Order
WHERE  OrderSeq IN ($pastOrders)
AND    SiteId = :SiteId
AND    IFNULL(CloseReason, 0) <> 3
AND    Cnl_Status = 0
EOQ;

        $prm = array(
                ':SiteId' => $siteId,
        );

        return (int)$this->_adapter->query($sql)->execute($prm)->current()['cnt'];
    }

    /**
     * 過去二年間の注文の未払データ件数を取得する
     * @param $pastOrders 過去2年間の対象注文以外の注文配列
     * @return ResultInterface
     */
    public function findOrderCustomerByUnpaidCnt($pastOrders) {
        $query  = " SELECT COUNT(*) AS Cnt ";
        $query .= " FROM T_Order O ";
        $query .= " WHERE ";
        $query .= sprintf(" O.DataStatus < 91 AND O.DataStatus >= 31 AND O.Cnl_Status = 0 AND O.OrderSeq IN (%s)",
        $pastOrders);

        return (int)$this->_adapter->query($query)->execute(null)->current()['Cnt'];
    }

    /**
     * 過去二年間の注文の未払データ総額を取得する
     * @param $pastOrders 過去2年間の対象注文以外の注文配列
     * @return ResultInterface
     */
    public function findOrderCustomerByUnpaidAmount($pastOrders) {
        $query  = " SELECT IFNULL(SUM(IFNULL(ClaimedBalance, 0)), 0) AS Amount ";
        $query .= " FROM T_ClaimControl ";
        $query .= " WHERE OrderSeq IN ( ";
        $query .= " SELECT CC.OrderSeq ";
        $query .= " FROM T_Order O ";
        $query .= " INNER JOIN T_ClaimControl CC ON CC.OrderSeq = O.P_OrderSeq ";
        $query .= " WHERE ";
        $query .= sprintf(" O.DataStatus IN (51, 61) AND O.Cnl_Status = 0 AND O.OrderSeq IN (%s)",
        $pastOrders);
        $query .= " ) ";

        $amount = (int)$this->_adapter->query($query)->execute(null)->current()['Amount'];  // 請求済み分

        $query  = " SELECT IFNULL(SUM(UseAmount), 0) AS UseAmount ";
        $query .= " FROM   T_Order ";
        $query .= " WHERE  DataStatus >= 31 AND DataStatus < 51 ";
        $query .= " AND    Cnl_Status = 0 ";
        $query .= sprintf(" AND OrderSeq IN (%s) ", $pastOrders);
        $useAmount = (int)$this->_adapter->query($query)->execute(null)->current()['UseAmount'];  // 未請求分

        return ($amount + $useAmount);
    }

    /**
     *  過去二年間の注文で与信NG件数を取得する
     * @param $pastOrders 過去2年間の対象注文以外の注文配列
     * @return int 件数
     */
    public function findOrderCustomerByNgCnt2($pastOrders) {
        $where = sprintf(" CloseReason = 3 AND OrderSeq IN (%s)", $pastOrders);

        return $this->findOrderCustomerCnt($where);
    }

    /**
     *  過去二年間の注文で未クローズ件数を取得する
     * @param $pastOrders 過去2年間の対象注文以外の注文配列
     * @return int 件数
     */
    public function findOrderCustomerByNoCloseCnt2($pastOrders) {
        $where = sprintf(" DataStatus <> 91 AND OrderSeq IN (%s)", $pastOrders);

        return $this->findOrderCustomerCnt($where);
    }

    /**
     *  過去二年間の注文の合計額の最大値を取得する
     * @param $pastOrders 過去2年間の対象注文以外の注文配列
     * @return int 最大値
     */
    public function findOrderCustomerByMaxUseAmountCnt2($pastOrders) {
        $where = sprintf(" OrderSeq IN (%s)", $pastOrders);

        $query = "SELECT max(UseAmount) AS maxuseamount FROM T_Order WHERE DataStatus = 91 AND CloseReason = 1 AND Cnl_Status = 0 AND " . $where;

        return (int)$this->_adapter->query($query)->execute(null)->current()['maxuseamount'];
    }

    /**
     *  過去二年間の注文で支払遅れ10日以上件数を取得する
     * @param $pastOrders 過去2年間の対象注文以外の注文配列
     * @return int 件数
     */
    public function findOrderCustomerByLateRecCnt2($pastOrders) {
        $query  = " SELECT COUNT(*) AS Cnt ";
        $query .= " FROM T_Order O ";
        $query .= " LEFT JOIN T_ClaimControl CC ON CC.OrderSeq = O.OrderSeq ";
        $query .= " LEFT JOIN T_ReceiptControl RC ON RC.OrderSeq = O.OrderSeq ";
        $query .= " LEFT JOIN T_SystemProperty SP ON SP.Module = '[DEFAULT]' AND SP.Category = 'systeminfo' AND SP.Name = 'PaymentDelayDays' "; 
        $query .= " WHERE ";
        $query .= sprintf(" O.DataStatus = 91 AND O.Cnl_Status = 0 AND CC.OrderSeq IN (%s) ", $pastOrders);
        $query .= " AND DATE_ADD(CC.F_LimitDate, INTERVAL SP.PropValue DAY) <= RC.ReceiptDate ";

        return $this->_adapter->query($query)->execute(null)->current()['Cnt'];
    }

    /**
     * 指定注文のOEM先備考欄に請求書発行情報を追記する。
     * このメソッドは対象注文を所有するOEM先で「請求書発行履歴」が有効になっている場合のみ
     * 有効な動作を行い、それ以外の場合はなにもしない
     *
     * @param int $oseq 注文SEQ
     */
    public function appendPrintedInfoToOemNote($oseq) {
        // ----------------------------- 注文データ取得
        $order = $this->find($oseq)->current();

        // 指定注文が存在しない場合はなにもしない
        if(!$order) return;

        // ----------------------------- OEM先データ取得
        $oemTable = new TableOem($this->_adapter);
        $oem = $oemTable->find((int)$order['OemId'])->current();

        // OEM先が見つからないか、請求書発行履歴を使用しない設定の場合は何もしない
        if(!$oem || !$oem['RecordClaimPrintedDateFlg']) return;

        // ----------------------------- 最新の印刷済み請求履歴取得
        $q = <<<EOQ
SELECT
	*
FROM
	T_ClaimHistory
WHERE
	Seq = (
		SELECT MAX(Seq)
		FROM T_ClaimHistory
		WHERE
			OrderSeq = :oseq AND
			PrintedFlg = 1
	)
EOQ;
        $hisRow = $this->_adapter->query($q)->execute(array('oseq' => $order['P_OrderSeq']))->current();
        // 請求履歴が見つからない場合は何もしない
        if(!$hisRow) return ;

        $map = array(
                '1' => '初回',
                '2' => '再１',
                '3' => '再２',
                '4' => '再３',
                '5' => '内容証明',
                '6' => '再４',
                '7' => '再５',
                '8' => '再６',
                '9' => '再７'
        );

        $msgbuf = array();
        if(strlen(nvl($order['Oem_Note']))) $msgbuf[] = $order['Oem_Note'];
        $msgbuf[] =
        sprintf('%s：%s 発行', date('Y-m-d', strtotime($hisRow['PrintedDate'])), $map[$hisRow['ClaimPattern']]);
        $msgbuf[] = '----';
        $udata = array(
                'Oem_Note' => join("\r\n", $msgbuf)
        );
        $this->saveUpdate($udata, $order['OrderSeq']);
    }

    /**
     * 臨時加盟店立替確定したデータを本締めする
     *
     * @param int $payingControlSeq 立替振替管理Seq
     * @param int $userId ユーザID
     * @param int $enterpriseId 加盟店ID
     * @param string $payingControlDate 立替締め日
     */
    public function updateSpecialPayingOrder($payingControlSeq, $userId, $enterpriseId, $payingControlDate)
    {
$sql= <<<EOQ
UPDATE T_Order o
SET    o.Chg_Status             = 1
      ,o.Chg_FixedDate          = :Chg_FixedDate
      ,o.Chg_DecisionDate       = :Chg_DecisionDate
      ,o.Chg_ChargeAmount       = ( SELECT MAX(t.ChargeAmount) FROM T_PayingAndSales t WHERE t.OrderSeq = o.OrderSeq )
      ,o.Chg_Seq                = :Chg_Seq
      ,o.UpdateDate             = :UpdateDate
      ,o.UpdateId               = :UpdateId
 WHERE o.EnterpriseId = :EnterpriseId
   AND EXISTS( SELECT *
                 FROM T_PayingAndSales pas
                WHERE pas.OrderSeq = o.OrderSeq
                  AND pas.ClearConditionForCharge = 1
                  AND (   pas.PayingControlStatus = 0
                       OR pas.PayingControlStatus IS NULL
                      )
                  AND pas.CancelFlg = 0
                  AND pas.SpecialPayingDate IS NOT NULL
                  AND pas.ClearConditionDate <= :ClearConditionDate
             )
EOQ;



        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Chg_FixedDate' => date('Y-m-d', strtotime($payingControlDate)),
                ':Chg_DecisionDate' => date('Y-m-d'),
                ':Chg_Seq' => $payingControlSeq,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $userId,
                ':EnterpriseId' => $enterpriseId,
                ':ClearConditionDate' => date("Y-m-d", strtotime($payingControlDate)),
        );

        return $stm->execute($prm);
    }

    /**
     * 指定注文の確定待ちフラグをOFFにする
     * @param int $oseq 注文SEQ
     * @param int $userId ユーザーID
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function updateClaimUnissued($oseq, $userId) {

        // 親注文SEQを取得し、指定の注文に紐づく注文は更新する
        $poseq = $this->find($oseq)->current()['P_OrderSeq'];

$sql= <<<EOQ
UPDATE T_Order o
SET    o.ConfirmWaitingFlg = 0
     , o.UpdateId          = :UpdateId
     , o.UpdateDate        = :UpdateDate
WHERE  o.P_OrderSeq = :OrderSeq
EOQ;

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $poseq,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $userId,
        );

        return $stm->execute($prm);

    }

    /**
     * 指定されたレコードを更新する。(対象項目のみ版)
     *
     * @param array $data 更新内容
     * @param int $orderSeq 更新するorderSeq
     */
    public function saveUpdateParts($data, $orderSeq)
    {
        if (empty($data)) { return; }

        $prm = array();
        $sql  = " UPDATE T_Order SET ";

        $isFirst = true;
        foreach ($data as $key => $value) {
            if ($isFirst) {
                $sql .= ($key . " = :" . $key);
                $isFirst = false;// フラグを落とす
            }
            else {
                $sql .= (" , " . $key . " = :" . $key);
            }

            $prm += array(':' . $key => $value);
        }

        $sql .= " WHERE OrderSeq = :OrderSeq ";
        $prm += array(':OrderSeq' => $orderSeq);

        return $this->_adapter->query($sql)->execute($prm);
    }
}
