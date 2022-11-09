<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Coral\Base\BaseGeneralUtils;
use models\Table\TableBusinessCalendar;
use models\Table\TableEnterprise;

/**
 * V_ChargeConfirmビュー
 */
class ViewChargeConfirm
{
	protected $_name = 'V_ChargeConfirm';
	protected $_primary = 'Seq';
	protected $_adapter = null;

	/**
	 * ベースSQL
	 */
	const BASE_QUERY = "
SELECT MPC.FixPattern AS FixPattern
    ,ENT.EnterpriseNameKj AS EnterpriseNameKj
    ,F_GetCampaignVal(PC.EnterpriseId, (SELECT MIN(SiteId) FROM T_Site WHERE EnterpriseId = ENT.EnterpriseId), date(PC.FixedDate), 'AppPlan') AS Plan
    ,(SELECT MAX(SettlementFeeRate) AS SettlementFeeRate FROM T_Site WHERE EnterpriseId = ENT.EnterpriseId) AS SettlementFeeRate
    ,ENT.FfName AS FfName
    ,ENT.FfCode AS FfCode
    ,ENT.FfBranchName AS FfBranchName
    ,ENT.FfBranchCode AS FfBranchCode
    ,ENT.FfAccountNumber AS FfAccountNumber
    ,ENT.FfAccountClass AS FfAccountClass
    ,ENT.FfAccountName AS FfAccountName
    ,PC.Seq AS Seq
    ,PC.EnterpriseId AS EnterpriseId
    ,PC.FixedDate AS FixedDate
    ,PC.DecisionDate AS DecisionDate
    ,PC.ExecScheduleDate AS ExecScheduleDate
    ,PC.ExecDate AS ExecDate
    ,PC.ExecFlg AS ExecFlg
    ,PC.ExecCpId AS ExecCpId
    ,PC.CarryOver AS CarryOver
    ,PC.ChargeCount AS ChargeCount
    ,PC.ChargeAmount AS ChargeAmount
    ,PC.SettlementFee AS SettlementFee
    ,PC.ClaimFee AS ClaimFee
    ,((PC.ChargeAmount + PC.SettlementFee) + PC.ClaimFee) AS UseAmount
    ,(PC.SettlementFee + PC.ClaimFee) AS Uriage
    ,PC.CancelCount AS CancelCount
    ,PC.CalcelAmount AS CalcelAmount
    ,PC.StampFeeCount AS StampFeeCount
    ,PC.StampFeeTotal AS StampFeeTotal
    ,PC.MonthlyFee AS MonthlyFee
    ,PC.TransferCommission AS TransferCommission
    ,PC.DecisionPayment AS DecisionPayment
    ,(PC.DecisionPayment - PC.CarryOver) AS DecisionPaymentOrg
    ,PC.AddUpFlg AS AddUpFlg
    ,PC.AddUpFixedMonth AS AddUpFixedMonth
    ,PC.AdjustmentAmount AS AdjustmentAmount
    ,ENT.PayingCycleId AS PayingCycleId
    ,PC.ClaimPdfFilePath AS ClaimPdfFilePath
    ,PC.PayBackAmount AS PayBackAmount
    ,PC.PayingDataDownloadFlg AS PayingDataDownloadFlg
    ,PC.SpecialPayingFlg AS SpecialPayingFlg
    ,PC.PayingDataFilePath AS PayingDataFilePath
    ,PC.PayingControlStatus AS PayingControlStatus
FROM T_PayingControl PC
    INNER JOIN T_Enterprise ENT
            ON PC.EnterpriseId = ENT.EnterpriseId
    INNER JOIN M_PayingCycle MPC
        ON ENT.PayingCycleId = MPC.PayingCycleId
WHERE 1 = 1
";

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
	 * 指定条件（AND）の立替確認データを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @param string $order オーダー
	 * @return ResultInterface
	 */
	public function findChargeConfirm($conditionArray, $order, $oemId = null)
	{
        $prm = array();

        $sql  = self::BASE_QUERY;
        foreach ($conditionArray as $key => $value) {
            // V_ChargeConfirm使用の取りやめによる対応[ﾌｨｰﾙﾄﾞ名へのﾃｰﾌﾞﾙ名付加](ここから)
            $tableName = '';
            if ($key == 'Seq') {
                $tableName = 'PC.';
            }
            $sql .= (" AND " . $tableName . $key . " = :" . $key);
            // V_ChargeConfirm使用の取りやめによる対応[ﾌｨｰﾙﾄﾞ名へのﾃｰﾌﾞﾙ名付加](ここまで)
            $prm += array(':' . $key => $value);
        }

        // OEM
        if (nvl($oemId, 0)) {
            $sql .= " AND PC.OemId = :OemId ";
            $prm[':OemId'] = $oemId;
        }

        $sql .= " ORDER BY " . $order;

        return $this->_adapter->query($sql)->execute($prm);
	}

	/**
	 * 立替確認データを取得する
	 *
	 * @param int $isExec 0：未立替え　1：立替済み
	 * @param string $fromDecision 開始確定日
	 * @param string $toDecision 終了確定日
	 * @param int $numSimePtn 有効締め日パターン数
	 * @param boolean $isOnlyTudoSeikyu 都度請求のみ表示
	 * @param int $eid 加盟店ID
	 * @param boolean $updateFlg 更新フラグ
	 * @param number $specialPayingFlg 臨時加盟店立替フラグ
	 * @return ResultInterface
	 */
	public function getConfirmList($isExec = 0, $fromDecision = '', $toDecision = '', $isOnlyTudoSeikyu, &$numSimePtn, $oemId = null, $eid = -1, $updateFlg, $specialPayingFlg = null)
	{
        // 締め日パターン取得
        $sql = " SELECT KeyCode, KeyContent FROM M_Code WHERE CodeId = 2 AND ValidFlg = 1 ORDER BY KeyCode ";

        $ri = $this->_adapter->query($sql)->execute(null);
        $numSimePtn = $ri->count();// 戻り引数[有効締め日パターン数]設定

        // SQL文字列パーツ
        $sqlA = "";
        $sqlB = "";
        $sqlC = "";
        $sqlD = "";

        $i = 1;
        foreach ($ri as $row) {
            // sqlA考慮
            $sqlA .= (" ,      " . CoatStr($row['KeyContent']) . " AS P" . $i . "NM ");
            $sqlA .= (" ,      SUM(T.P" . $i . "CNT) AS P" . $i . "CNT ");
            $sqlA .= (" ,      SUM(T.P" . $i . "PAY) AS P" . $i . "PAY ");
            $sqlA .= (" ,      MAX(T.P" . $i . "FD ) AS P" . $i . "FD  ");
            // sqlB考慮
            $sqlB .= (($i > 1) ? (" + T.P" . $i . "CNT") : ("T.P" . $i . "CNT"));
            // sqlC考慮
            $sqlC .= (($i > 1) ? (" + T.P" . $i . "PAY") : ("T.P" . $i . "PAY"));
            // sqlD考慮
            $sqlD .= ("      ,      CASE WHEN V_ChargeConfirm.FixPattern = " . $row['KeyCode'] . " THEN 1 ELSE 0 END AS P" . $i . "CNT ");
            $sqlD .= ("      ,      CASE WHEN V_ChargeConfirm.FixPattern = " . $row['KeyCode'] . " THEN DecisionPayment ELSE 0 END AS P" . $i . "PAY ");
            $sqlD .= ("      ,      CASE WHEN V_ChargeConfirm.FixPattern = " . $row['KeyCode'] . " THEN FixedDate ELSE NULL END AS P" . $i . "FD ");

            // $iインクリメント
            $i++;
        }

        // SQL組立て
        $sql  = " SELECT IFNULL(T.OemId,0) as OemId ";
        $sql .= " ,      T.DecisionDate ";
        $sql .= " ,      T.ExecScheduleDate ";
        $sql .= " ,      MAX(T.Seq) AS Seq ";
        $sql .= " ,      MAX(CASE WHEN T.ExecStopFlg = 1 THEN 0 ELSE IFNULL(T.PayingDataDownloadFlg, 0) END) as PayingDataDownloadFlg ";
        $sql .= " ,      MAX(LENGTH(IFNULL(T.PayingDataFilePath,''))) AS PayingDataFilePath ";
        $sql .= " ,      MAX(LENGTH(IFNULL(ClaimPdfFilePath,''))) AS ClaimPdfFilePath ";
        $sql .= " ,      GROUP_CONCAT(T.Seq SEPARATOR ',') AS SeqList ";
        $sql .= $sqlA;
        $sql .= " ,      SUM(" . $sqlB . ") AS CTOTAL ";
        $sql .= " ,      SUM(" . $sqlC . ") AS PTOTAL ";
        $sql .= " FROM ";
        $sql .= "     (SELECT V_ChargeConfirm.Seq ";
        $sql .= "      ,      V_ChargeConfirm.DecisionDate ";
        $sql .= "      ,      V_ChargeConfirm.ExecScheduleDate ";
        $sql .= "      ,      V_ChargeConfirm.FixedDate ";
        $sql .= "      ,      V_ChargeConfirm.OemId ";
        $sql .= "      ,      V_ChargeConfirm.ClaimPdfFilePath ";
        $sql .= "      ,      V_ChargeConfirm.PayingDataDownloadFlg ";
        $sql .= "      ,      V_ChargeConfirm.PayingDataFilePath ";
        $sql .= "      ,      V_ChargeConfirm.ExecStopFlg ";
        $sql .= $sqlD;
        $sql .= "      FROM   ( ";
        $sql .= "               SELECT MPC.FixPattern AS FixPattern ";
        $sql .= "               ,      PC.Seq AS Seq ";
        $sql .= "               ,      PC.EnterpriseId AS EnterpriseId ";
        $sql .= "               ,      PC.FixedDate AS FixedDate ";
        $sql .= "               ,      PC.DecisionDate AS DecisionDate ";
        $sql .= "               ,      PC.ExecScheduleDate AS ExecScheduleDate ";
        $sql .= "               ,      PC.DecisionPayment AS DecisionPayment ";
        $sql .= "               ,      PC.ClaimPdfFilePath AS ClaimPdfFilePath ";
        $sql .= "               ,      PC.PayingDataDownloadFlg AS PayingDataDownloadFlg ";
        $sql .= "               ,      PC.PayingDataFilePath AS PayingDataFilePath ";
        $sql .= "               ,      PC.OemId AS OemId ";
        $sql .= "               ,      ENT.ExecStopFlg AS ExecStopFlg ";
        $sql .= "               FROM   T_PayingControl PC ";
        $sql .= "                      INNER JOIN T_Enterprise ENT ON PC.EnterpriseId = ENT.EnterpriseId ";
        $sql .= "                      INNER JOIN M_PayingCycle MPC ON ENT.PayingCycleId = MPC.PayingCycleId ";
        $sql .= "               WHERE  1 = 1 ";
        // 臨時加盟店立替フラグ（0：通常／1：臨時加盟店立替精算）
        // ※ 引数によって抽出条件切り替え。デフォルトは「通常」のみ
        if (!is_null($specialPayingFlg)) {
            $sql .= "           AND    PC.SpecialPayingFlg = " . $specialPayingFlg;
        }
        // 0：未立替え／1：立替済み
        $sql .= "               AND    PC.ExecFlg = " . $isExec;
        // 立替確定日範囲
        $where = BaseGeneralUtils::makeWhereDate('PC.DecisionDate', $fromDecision, $toDecision);
        if ($where != '') {
            $sql .= ("          AND    " . $where);
        }
        // OemId
        if (!is_null($oemId)) {
            $sql .= ("          AND    IFNULL(PC.OemId, 0) = ".$oemId);
        }
        // 都度請求のみ表示
        if ($isOnlyTudoSeikyu) {
            $sql .= ("          AND    PC.ExecFlg = 1 ");
            $sql .= ("          AND    PC.DecisionPayment < 0 ");
        }
        // 加盟店ID
        if ($eid != -1) {
            $sql .= ("          AND    PC.EnterpriseId = " . $eid);
        }
        // 更新対象フラグ
        // 締め日の翌土曜日（締め日が土曜日の場合は当日） がシステム日付以前 かつ 仮締めの立替データが更新対象
        if ($updateFlg) {
            $sql .= ("          AND    ( SELECT MIN(BusinessDate) FROM T_BusinessCalendar WHERE WeekDay = 6 AND BusinessDate >= PC.FixedDate ) <= DATE(NOW()) ");
            $sql .= ("          AND    PC.PayingControlStatus = 0 ");
        }
        $sql .= "             ) V_ChargeConfirm ";
        $sql .= "     ) T ";
        $sql .= " GROUP BY ";
        $sql .= "     IFNULL(T.OemId,0) ";
        $sql .= " ,   T.DecisionDate ";
        $sql .= " ,   T.ExecScheduleDate ";
        $sql .= " ORDER BY ";
        $sql .= "     IFNULL(T.OemId,0) ASC ";
        $sql .= " ,   T.DecisionDate DESC ";
        $sql .= " ,   T.ExecScheduleDate DESC ";
        $sql .= " ,   T.Seq ASC ";

        return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 立替確認データを取得する(振込確定金額が0より大きいものに限定する)
	 *
	 * @param int $isExec 0：未立替え　1：立替済み
	 * @param string $fromDecision 開始確定日
	 * @param string $toDecision 終了確定日
	 * @param int $numSimePtn 有効締め日パターン数
	 * @param boolean $isOnlyTudoSeikyu 都度請求のみ表示
	 * @param int $eid 加盟店ID
	 * @param boolean $updateFlg 更新フラグ
	 * @param number $specialPayingFlg 臨時加盟店立替フラグ
	 * @return ResultInterface
	 */
	public function getConfirmList2($isExec = 0, $fromDecision = '', $toDecision = '', $isOnlyTudoSeikyu, &$numSimePtn, $oemId = null, $eid = -1, $updateFlg, $specialPayingFlg = null)
	{
	    // 締め日パターン取得
	    $sql = " SELECT KeyCode, KeyContent FROM M_Code WHERE CodeId = 2 AND ValidFlg = 1 ORDER BY KeyCode ";

	    $ri = $this->_adapter->query($sql)->execute(null);
	    $numSimePtn = $ri->count();// 戻り引数[有効締め日パターン数]設定

	    // SQL文字列パーツ
	    $sqlA = "";
	    $sqlB = "";
	    $sqlC = "";
	    $sqlD = "";

	    $i = 1;
	    foreach ($ri as $row) {
	        // sqlA考慮
	        $sqlA .= (" ,      " . CoatStr($row['KeyContent']) . " AS P" . $i . "NM ");
	        $sqlA .= (" ,      SUM(T.P" . $i . "CNT) AS P" . $i . "CNT ");
	        $sqlA .= (" ,      SUM(T.P" . $i . "PAY) AS P" . $i . "PAY ");
	        $sqlA .= (" ,      MAX(T.P" . $i . "FD ) AS P" . $i . "FD  ");
	        // sqlB考慮
	        $sqlB .= (($i > 1) ? (" + T.P" . $i . "CNT") : ("T.P" . $i . "CNT"));
	        // sqlC考慮
	        $sqlC .= (($i > 1) ? (" + T.P" . $i . "PAY") : ("T.P" . $i . "PAY"));
	        // sqlD考慮
	        $sqlD .= ("      ,      CASE WHEN V_ChargeConfirm.FixPattern = " . $row['KeyCode'] . " AND ((DecisionPayment > 0 AND IFNULL(oem.PayingMethod, 0) = 1) OR (IFNULL(oem.PayingMethod, 0) = 0)) " . " THEN 1 ELSE 0 END AS P" . $i . "CNT ");
	        $sqlD .= ("      ,      CASE WHEN V_ChargeConfirm.FixPattern = " . $row['KeyCode'] . " AND ((DecisionPayment > 0 AND IFNULL(oem.PayingMethod, 0) = 1) OR (IFNULL(oem.PayingMethod, 0) = 0)) " . " THEN DecisionPayment ELSE 0 END AS P" . $i . "PAY ");
	        $sqlD .= ("      ,      CASE WHEN V_ChargeConfirm.FixPattern = " . $row['KeyCode'] . " AND ((DecisionPayment > 0 AND IFNULL(oem.PayingMethod, 0) = 1) OR (IFNULL(oem.PayingMethod, 0) = 0)) " . " THEN FixedDate ELSE NULL END AS P" . $i . "FD ");

	        // $iインクリメント
	        $i++;
	    }

	    // SQL組立て
	    $sql  = " SELECT IFNULL(T.OemId,0) as OemId ";
	    $sql .= " ,      T.DecisionDate ";
	    $sql .= " ,      T.ExecScheduleDate ";
	    $sql .= " ,      MAX(T.Seq) AS Seq ";
	    $sql .= " ,      MAX(CASE WHEN T.ExecStopFlg = 1 THEN 0 ELSE IFNULL(T.PayingDataDownloadFlg, 0) END) as PayingDataDownloadFlg ";
	    $sql .= " ,      MAX(LENGTH(IFNULL(T.PayingDataFilePath,''))) AS PayingDataFilePath ";
	    $sql .= " ,      MAX(LENGTH(IFNULL(ClaimPdfFilePath,''))) AS ClaimPdfFilePath ";
	    $sql .= " ,      GROUP_CONCAT(T.Seq SEPARATOR ',') AS SeqList ";
	    $sql .= $sqlA;
	    $sql .= " ,      SUM(" . $sqlB . ") AS CTOTAL ";
	    $sql .= " ,      SUM(" . $sqlC . ") AS PTOTAL ";
	    $sql .= " FROM ";
	    $sql .= "     (SELECT V_ChargeConfirm.Seq ";
	    $sql .= "      ,      V_ChargeConfirm.DecisionDate ";
	    $sql .= "      ,      V_ChargeConfirm.ExecScheduleDate ";
	    $sql .= "      ,      V_ChargeConfirm.FixedDate ";
	    $sql .= "      ,      V_ChargeConfirm.OemId ";
	    $sql .= "      ,      V_ChargeConfirm.ClaimPdfFilePath ";
	    $sql .= "      ,      V_ChargeConfirm.PayingDataDownloadFlg ";
	    $sql .= "      ,      V_ChargeConfirm.PayingDataFilePath ";
	    $sql .= "      ,      V_ChargeConfirm.ExecStopFlg ";
	    $sql .= $sqlD;
	    $sql .= "      FROM   ( ";
	    $sql .= "               SELECT MPC.FixPattern AS FixPattern ";
	    $sql .= "               ,      PC.Seq AS Seq ";
	    $sql .= "               ,      PC.EnterpriseId AS EnterpriseId ";
	    $sql .= "               ,      PC.FixedDate AS FixedDate ";
	    $sql .= "               ,      PC.DecisionDate AS DecisionDate ";
	    $sql .= "               ,      PC.ExecScheduleDate AS ExecScheduleDate ";
	    $sql .= "               ,      PC.DecisionPayment AS DecisionPayment ";
	    $sql .= "               ,      PC.ClaimPdfFilePath AS ClaimPdfFilePath ";
	    $sql .= "               ,      PC.PayingDataDownloadFlg AS PayingDataDownloadFlg ";
	    $sql .= "               ,      PC.PayingDataFilePath AS PayingDataFilePath ";
	    $sql .= "               ,      PC.OemId AS OemId ";
	    $sql .= "               ,      ENT.ExecStopFlg AS ExecStopFlg ";
	    $sql .= "               FROM   T_PayingControl PC ";
	    $sql .= "                      INNER JOIN T_Enterprise ENT ON PC.EnterpriseId = ENT.EnterpriseId ";
	    $sql .= "                      INNER JOIN M_PayingCycle MPC ON ENT.PayingCycleId = MPC.PayingCycleId ";
	    $sql .= "               WHERE  1 = 1 ";
	    // 臨時加盟店立替フラグ（0：通常／1：臨時加盟店立替精算）
	    // ※ 引数によって抽出条件切り替え。デフォルトは「通常」のみ
	    if (!is_null($specialPayingFlg)) {
	        $sql .= "           AND    PC.SpecialPayingFlg = " . $specialPayingFlg;
	    }
	    // 0：未立替え／1：立替済み
	    $sql .= "               AND    PC.ExecFlg = " . $isExec;
	    // 立替確定日範囲
	    $where = BaseGeneralUtils::makeWhereDate('PC.DecisionDate', $fromDecision, $toDecision);
	    if ($where != '') {
	        $sql .= ("          AND    " . $where);
	    }
	    // OemId
	    if (!is_null($oemId)) {
	        $sql .= ("          AND    IFNULL(PC.OemId, 0) = ".$oemId);
	    }
	    // 都度請求のみ表示
	    if ($isOnlyTudoSeikyu) {
	        $sql .= ("          AND    PC.ExecFlg = 1 ");
	        $sql .= ("          AND    PC.DecisionPayment < 0 ");
	    }
	    // 加盟店ID
	    if ($eid != -1) {
	        $sql .= ("          AND    PC.EnterpriseId = " . $eid);
	    }
	    // 更新対象フラグ
	    // 締め日の翌土曜日（締め日が土曜日の場合は当日） がシステム日付以前 かつ 仮締めの立替データが更新対象
	    if ($updateFlg) {
	        $sql .= ("          AND    ( SELECT MIN(BusinessDate) FROM T_BusinessCalendar WHERE WeekDay = 6 AND BusinessDate >= PC.FixedDate ) <= DATE(NOW()) ");
	        $sql .= ("          AND    PC.PayingControlStatus = 0 ");
	    }
	    $sql .= "             ) V_ChargeConfirm ";
	    $sql .= "             LEFT OUTER JOIN T_Oem oem ";
	    $sql .= "                          ON V_ChargeConfirm.OemId = oem.OemId ";
	    $sql .= "     ) T ";
	    $sql .= " GROUP BY ";
	    $sql .= "     IFNULL(T.OemId,0) ";
	    $sql .= " ,   T.DecisionDate ";
	    $sql .= " ,   T.ExecScheduleDate ";
	    $sql .= " ORDER BY ";
	    $sql .= "     IFNULL(T.OemId,0) ASC ";
	    $sql .= " ,   T.DecisionDate DESC ";
	    $sql .= " ,   T.ExecScheduleDate DESC ";
	    $sql .= " ,   T.Seq ASC ";

	    return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 指定確定日、指定締めパターンの立替詳細データを取得する。
	 *
	 * @param string $decisionDate 立替確定日 'yyyy-MM-dd'書式で通知
	 * @param string $execScheduleDate 立替予定日 'yyyy-MM-dd'書式で通知
	 * @param int $fixPattern 締めパターン
	 * @param int $oemId OEMID
	 * @param int $isOnlyTudoSeikyu 都度請求のみ表示（1:のみ / 0:全て）
	 * @param int $execFlg 立替済フラグ(0：未立替、1：立替済)
	 *
	 * @return ResultInterface
	 */
	public function getConfirmDetailList($decisionDate, $execScheduleDate, $fixPattern, $oemId = null, $isOnlyTudoSeikyu = 0, $execFlg = 0)
	{
        $prm = array();

        $query  = self::BASE_QUERY;
        $query .= " AND IFNULL(PC.DecisionDate, 0) = :DecisionDate ";
        $query .= " AND PC.ExecScheduleDate = :ExecScheduleDate ";
        $query .= " AND MPC.FixPattern = :FixPattern ";
        $prm[':DecisionDate'] = $decisionDate;
        $prm[':ExecScheduleDate'] = $execScheduleDate;
        $prm[':FixPattern'] = $fixPattern;

        // 都度請求のみ表示
        if ($isOnlyTudoSeikyu) {
            $query .= (" AND PC.ExecFlg = 1 ");
            $query .= (" AND PC.DecisionPayment < 0 ");
        }
        else if (nvl($oemId, 0) > 0) {
            // OEM立替の場合は、立替額が１円以上のみ対象
            $payingMethod = $this->_adapter->query(" SELECT PayingMethod FROM T_Oem WHERE OemId = :OemId "
                )->execute(array(':OemId' => nvl($oemId, 0)))->current()['PayingMethod'];
        }

        //OEM
        $oemId = nvl($oemId, 0);
        $query .= " AND IFNULL(PC.OemId, 0) = :OemId ";
        $prm[':OemId'] = $oemId;

        // 立替済フラグ
        $query .= " AND PC.ExecFlg = :ExecFlg ";
        $prm[':ExecFlg'] = $execFlg;

        return $this->_adapter->query($query)->execute($prm);
	}

	/**
	 * 月次明細データ作成対象を取得する。
	 *
	 * @param string $fixMonth 対象年月 'yyyy-MM-dd'書式で通知
	 * @return ResultInterface 月次データ作成ネタデータ
	 */
	public function getMonthlyClaimedConfirm($fixMonth/*,$oem_id = null*/)
	{
	    $query = "
			SELECT
			    ENT.EnterpriseId,
			    ENT.LoginId,
			    ENT.EnterpriseNameKj,
			    IFNULL(ENT.OemId,0) as OemId,
			    SUM(PC.ChargeCount) AS ChargeCount,
			    SUM(PC.ChargeAmount + PC.SettlementFee + PC.ClaimFee) AS UseAmount,
			    SUM(PC.SettlementFee) AS SettlementFee,
			    SUM(PC.ClaimFee) AS ClaimFee,
			    SUM(PC.CalcelAmount) AS CalcelAmount,
			    SUM(PC.StampFeeTotal) AS StampFeeTotal,
			    SUM(PC.MonthlyFee) AS MonthlyFee,
				SUM((PC.ChargeAmount + PC.SettlementFee + PC.ClaimFee) - (PC.DecisionPayment - PC.CarryOver) + PC.AdjustmentAmount) AS ClaimTotal,
				SUM(PC.TransferCommission) AS TransferCommission,
			    SUM(PC.DecisionPayment) AS DecisionPayment,
			    SUM(PC.DecisionPayment - PC.CarryOver) AS DecisionPaymentOrg,
				SUM(PC.AdjustmentAmount) AS AdjustmentAmountOnCharge
			FROM
			    T_Enterprise ENT,
			    T_PayingControl PC
			WHERE
			    PC.EnterpriseId = ENT.EnterpriseId AND
			    PC.AddUpFlg = 0 AND
			    PC.AddUpFixedMonth = '%s'
			    %s
			GROUP BY
			    ENT.EnterpriseId,
			    ENT.LoginId,
			    ENT.EnterpriseNameKj
		";

		$oem_query = "";

		/*
		 * 事業者向けの月次明細作成はOemIdを考慮する必要は無い。 2014.9.11 kashira
		 *
		 * if(!is_null($oem_id)){
		    $oem_query = "AND ENT.OemId = ".$oem_id;
		    //$oem_query = "AND ENT.OemId is null";

		}*/

        return $this->_adapter->query(sprintf($query, $fixMonth, $oem_query))->execute(null);
	}

    /**
     * 指定確定日、指定締めパターンの立替詳細データを取得する。
     *
     * @param string $decisionDate 立替確定日 'yyyy-MM-dd'書式で通知
     * @param string $execScheduleDate 立替予定日 'yyyy-MM-dd'書式で通知
     * @param int $fixPattern 締めパターン
     * @param int $enterpiseid 加盟店ID
     *
     * @return ResultInterface
     */
    public function getConfirmDetailEnt($decisionDate, $execScheduleDate, $fixPattern, $enterpiseid)
    {
        $query  = self::BASE_QUERY;
        $query .= " AND IFNULL(PC.DecisionDate, 0) = :DecisionDate ";
        $query .= " AND PC.ExecScheduleDate = :ExecScheduleDate ";
        $query .= " AND MPC.FixPattern = :FixPattern ";
        $query .= " AND PC.EnterpriseId = :EnterpriseId ";
        $query .= " AND PC.ExecFlg IN (0,1) ";

        $prm = array(
                ':DecisionDate' => $decisionDate,
                ':ExecScheduleDate' => $execScheduleDate,
                ':FixPattern' => $fixPattern,
                ':EnterpriseId' => $enterpiseid,
        );
        return $this->_adapter->query($query)->execute($prm);
    }

    /**
     * 立替予測データ取得
     *
     * @param int OemId
     * @return ResultInterface 立替予測データ
     */
    public function getForecastList2($oemId, &$numSimePtn)
    {
        // 締め日パターン取得
        $sqlFix = " SELECT KeyCode, KeyContent FROM M_Code WHERE CodeId = 2 AND ValidFlg = 1 ORDER BY KeyCode ";

        $ri = $this->_adapter->query($sqlFix)->execute(null);
        $numSimePtn = $ri->count();// 戻り引数[有効締め日パターン数]設定

        // SQL文字列パーツ
        $sqlA = "";
        $sqlB = "";
        $sqlC = "";
        $sqlD = "";

        $i = 1;
        foreach ($ri as $row) {
            // sqlA考慮
            $sqlA .= (" ,      " . CoatStr($row['KeyContent']) . " AS P" . $i . "NM ");
            $sqlA .= (" ,      SUM(T.P" . $i . "CNT) AS P" . $i . "CNT ");
            $sqlA .= (" ,      SUM(T.P" . $i . "PAY) AS P" . $i . "PAY ");
            $sqlA .= (" ,      MAX(T.P" . $i . "FD ) AS P" . $i . "FD  ");
            // sqlB考慮
            $sqlB .= (($i > 1) ? (" + T.P" . $i . "CNT") : ("T.P" . $i . "CNT"));
            // sqlC考慮
            $sqlC .= (($i > 1) ? (" + T.P" . $i . "PAY") : ("T.P" . $i . "PAY"));
            // sqlD考慮
            $sqlD .= ("      ,      CASE WHEN V_ChargeConfirm.FixPattern = " . $row['KeyCode'] . " THEN 1 ELSE 0 END AS P" . $i . "CNT ");
            $sqlD .= ("      ,      CASE WHEN V_ChargeConfirm.FixPattern = " . $row['KeyCode'] . " THEN ChargeAmount ELSE 0 END AS P" . $i . "PAY ");
            $sqlD .= ("      ,      CASE WHEN V_ChargeConfirm.FixPattern = " . $row['KeyCode'] . " THEN FixedDate ELSE NULL END AS P" . $i . "FD ");

            // $iインクリメント
            $i++;
        }

        // SQL組立て
        $sql  = " SELECT T.OemId ";
        $sql .= " ,      T.ExecScheduleDate ";
        $sql .= " ,      GROUP_CONCAT(T.Seq SEPARATOR ',') AS SeqList ";
        $sql .= $sqlA;
        $sql .= " ,      SUM(" . $sqlB . ") AS CTOTAL ";
        $sql .= " ,      SUM(" . $sqlC . ") AS PTOTAL ";
        $sql .= " FROM ";
        $sql .= "     (SELECT V_ChargeConfirm.Seq ";
        $sql .= "      ,      V_ChargeConfirm.ExecScheduleDate ";
        $sql .= "      ,      V_ChargeConfirm.FixedDate ";
        $sql .= "      ,      V_ChargeConfirm.OemId ";
        $sql .= $sqlD;
        $sql .= "     FROM ";
        $sql .= "         ( ";
        $sql .= "             SELECT MPC.FixPattern AS FixPattern ";
        $sql .= "             ,      PC.Seq AS Seq ";
        $sql .= "             ,      PC.FixedDate AS FixedDate ";
        $sql .= "             ,      PC.ExecScheduleDate AS ExecScheduleDate ";
        $sql .= "             ,      PC.ChargeAmount AS ChargeAmount ";
        $sql .= "             ,      PC.OemId AS OemId ";
        $sql .= "             FROM T_PayingControl PC ";
        $sql .= "                  INNER JOIN T_Enterprise ENT ON PC.EnterpriseId = ENT.EnterpriseId ";
        $sql .= "                  INNER JOIN M_PayingCycle MPC ON ENT.PayingCycleId = MPC.PayingCycleId ";
        $sql .= "             WHERE 1 = 1 ";
        // 臨時加盟店立替フラグ（0：通常／1：臨時加盟店立替精算）※ 通常の立替のみ表示する
        $sql .= "             AND   PC.SpecialPayingFlg = 0 ";
        // 0：未立替え／1：立替済み
        $sql .= "             AND   PC.ExecFlg = 0 ";
        // 締め日 がシステム日付以前 かつ 仮締めの立替データが更新対象
        $sql .= ("            AND   PC.PayingControlStatus = 0 ");
        // OemId
        if (!is_null($oemId)) {
            $sql .= ("        AND   IFNULL(PC.OemId, 0) = ".$oemId);
        }
        $sql .= "         ) V_ChargeConfirm ";
        $sql .= "     ) T ";
        $sql .= " GROUP BY ";
        $sql .= "     IFNULL(T.OemId,0) ";
        $sql .= " ,   T.ExecScheduleDate ";
        $sql .= " ORDER BY ";
        $sql .= "     IFNULL(T.OemId,0) ASC ";
        $sql .= " ,   T.ExecScheduleDate DESC ";
        $sql .= " ,   T.Seq ASC ";

        return $this->_adapter->query($sql)->execute(null);
    }


    /**
     * 振込ファイルがダウンロード可能なデータが存在するか否か判断します。
     * @param string $prmSeqs カンマ区切りのSEQのリスト
     * @return boolean true:ダウンロードファイルあり false:ダウンロードファイルなし
     */
    public function isPayingDataDownLoad($prmSeqs) {
        $booRet = false;

        $sql  = "";
        $sql .= " SELECT COUNT(1) AS CNT ";
        $sql .= "   FROM T_PayingControl pc ";
        $sql .= "        INNER JOIN T_Enterprise e ";
        $sql .= "                ON pc.EnterpriseId = e.EnterpriseId ";
        $sql .= "  WHERE 1 = 1 ";
        $sql .= "    AND pc.Seq IN ($prmSeqs) ";
        $sql .= "    AND pc.PayingControlStatus = 1 ";
        $sql .= "    AND pc.DecisionPayment > 0 ";
        $sql .= "    AND e.ExecStopFlg = 0 ";

        $row = $this->_adapter->query($sql)->execute(null)->current();

        if ($row['CNT'] > 0) {
            $booRet = true;
        }

        return $booRet;
    }

    /**
     * 都度請求ファイルがダウンロード可能なデータが存在するか否か判断します。
     * @param string $prmSeqs カンマ区切りのSEQのリスト
     * @return boolean true:ダウンロードファイルあり false:ダウンロードファイルなし
     */
    public function isClaimPdfDownLoad($prmSeqs) {
        $booRet = false;

        $sql  = "";
        $sql .= " SELECT COUNT(1) AS CNT ";
        $sql .= "   FROM T_PayingControl pc ";
        $sql .= "        INNER JOIN T_EnterpriseClaimHistory ech ";
        $sql .= "                ON pc.Seq = ech.PayingControlSeq ";
        $sql .= "  WHERE 1 = 1 ";
        $sql .= "    AND pc.Seq IN ($prmSeqs) ";

        $row = $this->_adapter->query($sql)->execute(null)->current();

        if ($row['CNT'] > 0) {
            $booRet = true;
        }

        return $booRet;
    }
}
