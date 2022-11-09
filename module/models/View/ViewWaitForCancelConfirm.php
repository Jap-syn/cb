<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * V_WaitForCancelConfirmビュー
 *
 */
class ViewWaitForCancelConfirm
{
	protected $_name = 'V_WaitForCancelConfirm';
	protected $_primary = 'OrderSeq';
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
	 * 全てのキャンセル確認待ち注文データを取得する。
	 *
	 * @return ResultInterface
	 */
	public function getAll()
	{
	    $sql = " SELECT * FROM V_WaitForCancelConfirm ORDER BY CancelPhase, OrderSeq ";
        return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 全てのキャンセル確認待ち注文データ件数を取得する。
	 *
	 * @return int 件数
	 */
	public function getAllCount()
	{
	    $query = "
			SELECT
			    COUNT(*) AS CNT
			FROM
			    T_Order ORD
				INNER JOIN
					(SELECT DISTINCT DataStatus FROM T_Order) V
					ON V.DataStatus = ORD.DataStatus
			WHERE
			    ORD.Cnl_Status = 1
		";

        return (int)$this->_adapter->query($query)->execute(null)->current()['CNT'];
	}

	/**
	 * 指定条件（AND）のデータを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @return ResultInterface
	 */
	public function findWfcc($conditionArray)
	{
        $prm = array();
        $sql  = " SELECT * FROM V_WaitForCancelConfirm WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
	}

	/**
	 * 指定キャンセルフェイズのデータを取得する。
	 * @param int $phase キャンセルフェイズ
	 * @return ResultInterface
	 * @see MySQLのViewはなんだか異常に遅いので、ベタにクエリーを書いて発行する。
	 */
	public function getPhaseByQuery($phase)
	{
	    $query = "
			SELECT
				ORD.OrderSeq,
				ORD.OrderId,
				ORD.RegistDate,
				ORD.ReceiptOrderDate,
				ORD.SiteId,
				ORD.UseAmount,
				ORD.AnotherDeliFlg,
				ORD.DataStatus,
				ORD.CloseReason,
				(SELECT
					Class2
				FROM
					M_Code
				WHERE
					CodeId = 4 AND
					KeyCode = (CASE WHEN Incre_ArTel = 5 OR Incre_ArAddr = 5 THEN  5
                          WHEN Incre_ArTel = 4 OR Incre_ArAddr = 4 THEN  4
                          WHEN Incre_ArTel = 3 OR Incre_ArAddr = 3 THEN  3
                          WHEN Incre_ArTel = 2 OR Incre_ArAddr = 2 THEN  2
                          WHEN Incre_ArTel = 1 OR Incre_ArAddr = 1 THEN  1
                          ELSE -1 END)
				) AS IncreArCaption,
				(SELECT
					Class2
				FROM
					M_Code
				WHERE
					CodeId = 4 AND
					KeyCode = (CASE WHEN Incre_ArTel = 5 OR Incre_ArAddr = 5 THEN  5
                          WHEN Incre_ArTel = 4 OR Incre_ArAddr = 4 THEN  4
                          WHEN Incre_ArTel = 3 OR Incre_ArAddr = 3 THEN  3
                          WHEN Incre_ArTel = 2 OR Incre_ArAddr = 2 THEN  2
                          WHEN Incre_ArTel = 1 OR Incre_ArAddr = 1 THEN  1
                          ELSE -1 END)
				) AS IncreArLongCaption,
				ORD.Incre_Status,
				ORD.Incre_AtnEnterpriseScore,
				ORD.Incre_AtnEnterpriseNote,
				ORD.Incre_BorderScore,
				ORD.Incre_BorderNote,
				ORD.Incre_LimitCheckScore,
				ORD.Incre_LimitCheckNote,
				ORD.Incre_ScoreTotal,
				ORD.Incre_DecisionDate,
				ORD.Incre_DecisionOpId,
				ORD.Incre_Note,
				ORD.Dmi_Status,
				ORD.Dmi_ResponseCode,
				ORD.Dmi_ResponseNote,
				ORD.Dmi_DecisionDate,
				ORD.Dmi_DecSeqId,
				CCNT.Clm_Count,
				CCNT.F_ClaimDate,
				CCNT.F_OpId,
				CCNT.F_LimitDate,
				CCNT.ClaimDate,
				CCNT.ClaimCpId,
				CCNT.ClaimPattern,
				CCNT.LimitDate,
				CCNT.DamageDays,
				CCNT.DamageBaseDate,
				CCNT.DamageInterestAmount,
				CCNT.ClaimFee,
				CCNT.AdditionalClaimFee,
	            CCNT.ClaimAmount,
				ORD.Chg_Status,
				ORD.Chg_FixedDate,
				ORD.Chg_DecisionDate,
				ORD.Chg_ExecDate,
				ORD.Chg_ChargeAmount,
				ORD.Rct_RejectFlg,
				ORD.Rct_RejectReason,
				ORD.Rct_Status,
				CNL.RepayDifferentialAmount,
				CNL.RepayDepositAmount,
				CNL.KeepAnAccurateFlg,
				ORD.Cnl_CantCancelFlg,
				ORD.Cnl_Status,
				ORD.Dmg_DecisionFlg,
				ORD.Dmg_DecisionDate,
				ORD.Dmg_DecisionAmount,
				ORD.Dmg_DecisionReason,
				ORD.Ent_OrderId,
				ORD.Ent_Note,
				ORD.Bekkan,
				CUS.CustomerId,
				CUS.NameKj,
				CUS.NameKn,
				CUS.PostalCode,
				CUS.PrefectureCode,
				CUS.PrefectureName,
				CUS.City,
				CUS.Town,
				CUS.Building,
				CUS.UnitingAddress,
				CUS.Hash_Name,
				CUS.Hash_Address,
				CUS.Phone,
				CUS.RealCallStatus,
				CUS.RealCallResult,
				CUS.RealCallScore,
				CUS.eDen,
				CUS.MailAddress,
				CUS.RealSendMailStatus,
				CUS.RealSendMailResult,
				CUS.RealSendMailScore,
				CUS.Occupation,
				CUS.Incre_ArName,
				CUS.Incre_NameScore,
				CUS.Incre_NameNote,
				CUS.Incre_ArAddr,
				CUS.Incre_AddressScore,
				CUS.Incre_AddressNote,
				CUS.Incre_MailDomainScore,
				CUS.Incre_MailDomainNote,
				CUS.Incre_PostalCodeScore,
				CUS.Incre_PostalCodeNote,
				CUS.Incre_ScoreTotal AS Incre_CusScoreTotal,
				ETP.EnterpriseId,
				ETP.LoginId AS EnterpriseLoginId,
				ETP.EnterpriseNameKj,
				ETP.CpNameKj,
				ETP.ContactPhoneNumber,
				ETP.MailAddress AS EntMailAddress,
				SIT.SiteNameKj,
			    CNL.Seq,
			    CNL.CancelDate,
			    CNL.CancelPhase,
			    CDC.KeyContent AS SelectCancelReason,
			    CNL.CancelReason AS InputCancelReason,
			    CNL.RepayTotal,
	            ORD.P_OrderSeq
			FROM
				T_Order ORD JOIN
				T_Customer CUS JOIN
				T_Enterprise ETP JOIN
				T_Site SIT JOIN
			    T_Cancel CNL LEFT OUTER JOIN
	            T_ClaimControl CCNT ON ORD.P_OrderSeq = CCNT.OrderSeq LEFT OUTER JOIN
	            M_Code CDC ON CDC.CodeId = 90 AND CDC.KeyCode = CNL.CancelReasonCode
	    WHERE
				ORD.OrderSeq = CUS.OrderSeq AND
				ORD.OrderSeq = CNL.OrderSeq AND
	            ORD.Cnl_Status = 1 AND
				ORD.EnterpriseId = ETP.EnterpriseId AND
				ORD.SiteId = SIT.SiteId AND
				CNL.ApproveFlg = 0 AND
	            CNL.ValidFlg = 1 AND
				CNL.CancelPhase = :CancelPhase AND
	            ORD.OrderSeq = ( SELECT MIN(OrderSeq) FROM T_Order t WHERE t.P_OrderSeq = ORD.P_OrderSeq AND t.Cnl_Status = 1 )
		";

	    $prm = array(
	        ':CancelPhase' => $phase,
	    );

        return $this->_adapter->query($query)->execute($prm);
	}

	/**
	 * キャンセルフェイズ１のデータを取得する。
	 *
	 * @return ResultInterface
	 */
	public function getPhase1()
	{
		return $this->getPhase(1);
	}

	/**
	 * キャンセルフェイズ２のデータを取得する。
	 *
	 * @return ResultInterface
	 */
	public function getPhase2()
	{
		return $this->getPhase(2);
	}

	/**
	 * キャンセルフェイズ３のデータを取得する。
	 *
	 * @return ResultInterface
	 */
	public function getPhase3()
	{
		return $this->getPhase(3);
	}

	/**
	 * キャンセルフェイズ４のデータを取得する。
	 *
	 * @return ResultInterface
	 */
	public function getPhase4()
	{
		return $this->getPhase(4);
	}

	/**
	 * 指定キャンセルフェイズのデータを取得する。
	 *
	 * @param int $phase
	 * @return ResultInterface
	 */
	public function getPhase($phase)
	{
		return $this->findWfcc(array('CancelPhase' => $phase));
	}

	/**
	 * 指定条件文字列（WHERE句）によるデータを取得する。
	 *
	 * @param string $whereStr WHERE句
	 * @param string $orderStr ORDER句
	 * @return ResultInterface
	 */
	public function findWfccByWhereStr($whereStr, $orderStr)
	{
	    $sql = " SELECT * FROM V_WaitForCancelConfirm " . $whereStr . " " . $orderStr;
        return $this->_adapter->query($sql)->execute(null);
	}
}
