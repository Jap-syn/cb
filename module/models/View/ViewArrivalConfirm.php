<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

/**
 * V_ArraivalConfirmビュー
 */
class ViewArrivalConfirm
{
	protected $_name = 'V_ArrivalConfirm';
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

// Del By Takemasa(NDC) 20150708 Stt 未使用故コメントアウト化
// 	/**
// 	 * 指定条件（AND）の着荷確認対象データを取得する。
// 	 *
// 	 * @param array $conditionArray 検索条件を格納した連想配列
// 	 * @param string $order オーダー
// 	 * @return ResultInterface
// 	 */
// 	public function findArraivalConfirm($conditionArray, $order)
//
// 	/**
// 	 * 現在の着荷確認処理対象データを取得する
// 	 *
// 	 * @return ResultInterface
// 	 */
// 	public function getArrivalConfirmData()
//
// 	/**
// 	 * 現在の着荷確認処理対象データ件数を取得する
// 	 *
// 	 * @return int 件数
// 	 */
// 	public function getArrivalConfirmDataCount()
//
// 	/**
// 	 * 現在の着荷確認処理対象データを取得する
// 	 *
// 	 * @param int $deliveryMethod 配送方法
// 	 * @return ResultInterface
// 	 */
// 	public function getArrivalConfirmDataByMethod($deliveryMethod)
//
// 	/**
// 	 * 配送方法ごとの着荷確認待ち件数を取得する。
// 	 *
// 	 * @return ResultInterface
// 	 */
// 	public function getArrivalConfirmCount()
// Del By Takemasa(NDC) 20150708 End 未使用故コメントアウト化

// Del By Takemasa(NDC) 20150331 Stt 実装をRwarvlcfmControllerへ移送
// 	/**
// 	 * 現在の着荷確認処理対象データを取得する（Phase6 対応版）
// 	 *
// 	 * @param int $deliveryMethod 配送方法
// 	 * @return array
// 	 */
// 	public function getArrivalConfirmDataByMethod2($deliveryMethod, $limitDate)
// Del By Takemasa(NDC) 20150331 End 実装をRwarvlcfmControllerへ移送

// Del By Takemasa(NDC) 20150708 Stt 未使用故コメントアウト化
// 	/**
// 	 * 現在の着荷確認処理対象データを取得する（Phase6 対応版）
// 	 *
// 	 * @param int $deliveryMethod 配送方法
// 	 * @return ResultInterface
// 	 */
// 	public function getArrivalConfirmDataByMethod3($seq)
// 	}
//
// 	/**
// 	 * 配送方法ごとの着荷確認待ち件数を取得する。（Phase6 対応版）
// 	 *
// 	 * @return ResultInterface
// 	 */
// 	public function getArrivalConfirmCount2()
// 	}
// Del By Takemasa(NDC) 20150708 End 未使用故コメントアウト化

	/**
	 * 配送方法ごとの着荷確認待ち件数を取得する。（Phase9 対応版）
	 *
	 * @param string $limitDate 配送－伝票番号入力日
	 * @param int $oemId OEMID
	 * @param array $aryExCondition 追加検索条件
	 * @return array
	 */
	public function getArrivalConfirmCount3($limitDate, $limitDateT, $oemId, $aryExCondition)
	{
        // パフォーマンス改変版クエリを取得（2013.12.17 eda）
        $query = $this->_getQueryForArrivalConfirmCount3();

        $addWhere = "";
        if ($oemId != -1) {
            $addWhere .= " AND IFNULL(o.OemId, 0) = " . $oemId;
        }
        if ($aryExCondition['onlyetc']) {
            $addWhere .= " AND    (SIT.EtcAutoArrivalFlg = 1 OR (SIT.EtcAutoArrivalFlg = 2 AND SIT.EtcAutoArrivalNumber = OITM.Deli_JournalNumber)) ";
            $addWhere .= " AND    ENT.Special01Flg = 1 ";
        }
        if ($aryExCondition['fixPattern'] != 0) {
            $addWhere .= " AND    MPC.FixPattern = " . $aryExCondition['fixPattern'];
        }
        if ($aryExCondition['entid'] != '') {
            $addWhere .= " AND    ENT.LoginId LIKE '%" . $aryExCondition['entid'] . "' ";
        }
        if ($aryExCondition['onlyprnt']) {
            $addWhere .= " AND    IFNULL(o.CombinedClaimTargetStatus, 0) IN (91, 92) ";
        }
        $Date = array( ':limitDateT' => $limitDateT);
        if (($limitDate != '')) {
            $addWhere .= " AND    DATE(SUM.Deli_JournalIncDate) >= :limitDate ";
            $Date = $Date + array( ':limitDate' => $limitDate );
        }

        if ($aryExCondition['exceptPrintedBilling']) {
            $addWhere .= " AND  (( o.DataStatus >= 51 ";
            $addWhere .= "OR  o.ClaimSendingClass = 12 ";
            $addWhere .= "OR ENT.SelfBillingMode IS NULL ";
            $addWhere .= "OR ENT.SelfBillingMode = 0 ) ";
            $addWhere .= "OR SIT.SelfBillingFlg = 0 )";
        }

        $query = sprintf($query, $addWhere);

        // delete code which get claimIntervalDays

        $ri =  $this->_adapter->query($query)->execute($Date);
        $result = array();
        foreach($ri as $row) {
            $result[$row['METHOD']] = $row['CNT'];
        }
        return $result;
	}

	/**
	 * 配送方法別要着荷確認件数集計のベースSQLを取得する
	 *
	 * @access private
	 * @return string
	 */
	private function _getQueryForArrivalConfirmCount3() {
	    return <<<EOQ
SELECT
	SUM.Deli_DeliveryMethod AS METHOD,
	COUNT(*) AS CNT
FROM
	T_Order o STRAIGHT_JOIN
	T_OrderSummary SUM ON SUM.OrderSeq = o.OrderSeq STRAIGHT_JOIN
    M_DeliveryMethod DM ON DM.DeliMethodId = SUM.Deli_DeliveryMethod
    INNER JOIN T_OrderItems OITM ON OITM.OrderItemId = SUM.OrderItemId
    INNER JOIN T_Enterprise ENT ON ENT.EnterpriseId = o.EnterpriseId
    INNER JOIN M_PayingCycle MPC ON MPC.PayingCycleId = ENT.PayingCycleId
    INNER JOIN T_Site SIT ON SIT.SiteId = o.SiteId
    INNER JOIN AT_Order AS ao ON ao.OrderSeq = o.OrderSeq
WHERE
    (o.Deli_ConfirmArrivalFlg IS NULL OR o.Deli_ConfirmArrivalFlg IN (-1, 0)) AND
	o.Cnl_Status = 0 AND
	o.DataStatus > 31 AND
	(o.CloseReason IS NULL OR o.CloseReason IN (0, 1))
    AND DATE(SUM.Deli_JournalIncDate) <= :limitDateT
    AND ( ao.CreditTransferRequestFlg <> '0' OR (
    IFNULL(ao.ExtraPayType, '0') <> '1'
    AND NOT (SIT.PaymentAfterArrivalFlg = 1
         AND DATE_ADD( (SELECT MIN(ch.ClaimDate) FROM T_ClaimHistory AS ch WHERE ch.ClaimPattern = 1 AND ch.OrderSeq = o.OrderSeq), INTERVAL (SELECT MAX(sbps.NumUseDay) AS MaxNumUseDay FROM T_SiteSbpsPayment sbps WHERE sbps.SiteId = o.SiteId AND sbps.ValidFlg = 1 AND sbps.UseStartDate < CURRENT_DATE()) DAY ) >= CURRENT_DATE()
         AND NOT EXISTS (SELECT 1 FROM T_ReceiptControl AS rc WHERE rc.OrderSeq = o.P_OrderSeq)
    )))
    AND
	(o.CombinedClaimTargetStatus IS NULL OR o.CombinedClaimTargetStatus IN (0, 91, 92))
AND    (o.OutOfAmends IS NULL OR o.OutOfAmends = 0)      /* 2015/10/29 条件を追加 補償対象外は表示しない */
    %s
GROUP BY
	SUM.Deli_DeliveryMethod
EOQ;
	}

// Del By Takemasa(NDC) 20150331 Stt 実装をRwarvlcfmControllerへ移送
// 	/**
// 	 * 指定条件の一括着荷確認処理対象データを取得する
// 	 *
// 	 * @param string $journalIncDate 指定伝票登録日 'yyyy-MM-dd'書式で通知
// 	 * @param string $receiptDate 指定入金日 'yyyy-MM-dd'書式で通知
// 	 * @param int $fixPattern 締めパターン
// 	 * @return ResultInterface
// 	 */
// 	public function findArrivalConfirmForLumpProcess($journalIncDate, $receiptDate, $fixPattern = 0)
// Del By Takemasa(NDC) 20150331 End 実装をRwarvlcfmControllerへ移送

// Del By Takemasa(NDC) 20150708 Stt 未使用故コメントアウト化
// 	/**
// 	 * 指定条件の一括着荷確認処理対象データの件数を取得する
// 	 *
// 	 * @param string $journalIncDate 指定伝票登録日 'yyyy-MM-dd'書式で通知
// 	 * @param string $receiptDate 指定入金日 'yyyy-MM-dd'書式で通知
// 	 * @param int $fixPattern 締めパターン
// 	 * @return int
// 	 */
// 	public function countArrivalConfirmForLumpProcess($journalIncDate, $receiptDate, $fixPattern = 0)
// 	}
// Del By Takemasa(NDC) 20150708 End 未使用故コメントアウト化

	public function getArrivalConfirmDetailForDiffMail($order_seq)
	{
	    $q = <<<EOQ
SELECT STRAIGHT_JOIN
	O.OrderSeq,
	O.OrderId,
	O.ReceiptOrderDate,
	E.EnterpriseId,
	E.EnterpriseNameKj,
	E.CpNameKj,
	E.MailAddress,
	C.NameKj AS CustomerNameKj,
	DATE_FORMAT(S.Deli_JournalIncDate, '%Y-%m-%d') AS Deli_JournalIncDate,
	S.Deli_JournalNumber,
	DM.DeliMethodName
FROM
	T_Order O INNER JOIN
	T_Customer C ON C.OrderSeq = O.OrderSeq INNER JOIN
	T_OrderSummary S ON S.OrderSeq = O.OrderSeq INNER JOIN
	T_Enterprise E ON E.EnterpriseId = O.EnterpriseId INNER JOIN
    M_DeliveryMethod DM ON DM.DeliMethodId = S.Deli_DeliveryMethod
WHERE
	O.OrderSeq = ?
EOQ;

		return $this->_adapter->query($q)->execute( array( $order_seq ) )->current();
	}

	/**
	 * OEMごとの着荷確認待ち件数を取得する。
	 *
	 * @param string $limitDate 配送－伝票番号入力日
	 * @param array $aryExCondition 追加検索条件
	 * @return array
	 */
	public function getArrivalConfirmCountByOem($limitDate, $limitDateT,  $aryExCondition)
	{
        $query = $this->_getQueryForArrivalConfirmCountByOem();

        $addWhere = "";
        if ($aryExCondition['onlyetc']) {
            $addWhere .= " AND    (SIT.EtcAutoArrivalFlg = 1 OR (SIT.EtcAutoArrivalFlg = 2 AND SIT.EtcAutoArrivalNumber = OITM.Deli_JournalNumber)) ";
            $addWhere .= " AND    ENT.Special01Flg = 1 ";
        }
        if ($aryExCondition['fixPattern'] != 0) {
            $addWhere .= " AND    MPC.FixPattern = " . $aryExCondition['fixPattern'];
        }
        if ($aryExCondition['entid'] != '') {
            $addWhere .= " AND    ENT.LoginId LIKE '%" . $aryExCondition['entid'] . "' ";
        }
        if ($aryExCondition['onlyprnt']) {
            $addWhere .= " AND    IFNULL(o2.CombinedClaimTargetStatus, 0) IN (91, 92) ";
        }
        $Date = array( ':limitDateT' => $limitDateT);
        if (($limitDate != '')) {
            $addWhere .= " AND    DATE(SUM.Deli_JournalIncDate) >= :limitDate ";
            $Date = $Date + array( ':limitDate' => $limitDate );
        }

        if ($aryExCondition['exceptPrintedBilling']) {
            $addWhere .= " AND  (( o2.DataStatus >= 51 ";
            $addWhere .= "OR  o2.ClaimSendingClass = 12 ";
            $addWhere .= "OR ENT.SelfBillingMode IS NULL ";
            $addWhere .= "OR ENT.SelfBillingMode = 0 ) ";
            $addWhere .= "OR SIT.SelfBillingFlg = 0 )";
        }

        $query = sprintf($query, $addWhere);

        // delete code which get claimIntervalDays

        $ri =  $this->_adapter->query($query)->execute($Date);

        // 各OEM件数を登録
        $result = array();
        foreach($ri as $row) {
            $result[$row['OEMID']] = (int)$row['CNT'];
        }

        // 全件数を[OEMID=-1]で登録
        $total = 0;
        foreach($result as $key => $value) {
            $total += $value;
        }
        $result['-1'] = $total;

        ksort($result);

        return $result;
	}

	/**
	 * OEM別要着荷確認件数集計のベースSQLを取得する
	 *
	 * @access private
	 * @return string
	 */
	private function _getQueryForArrivalConfirmCountByOem() {
	    return <<<EOQ
SELECT
	IFNULL(o2.OemId,0) AS OEMID,
	COUNT(IFNULL(o2.OemId,0)) AS CNT
FROM
	T_Order o2 STRAIGHT_JOIN
	T_OrderSummary SUM ON SUM.OrderSeq = o2.OrderSeq STRAIGHT_JOIN
    M_DeliveryMethod DM ON DM.DeliMethodId = SUM.Deli_DeliveryMethod
    INNER JOIN T_OrderItems OITM ON OITM.OrderItemId = SUM.OrderItemId
    INNER JOIN T_Enterprise ENT ON ENT.EnterpriseId = o2.EnterpriseId
    INNER JOIN M_PayingCycle MPC ON MPC.PayingCycleId = ENT.PayingCycleId
    INNER JOIN T_Site SIT ON SIT.SiteId = o2.SiteId
    INNER JOIN AT_Order AS ao ON ao.OrderSeq = o2.OrderSeq
WHERE
	o2.Cnl_Status = 0 AND
	o2.DataStatus > 31 AND
	(
		o2.CloseReason IS NULL OR
		o2.CloseReason IN (0, 1)
	)
 AND DATE(SUM.Deli_JournalIncDate) <= :limitDateT
 AND ( ao.CreditTransferRequestFlg <> '0' OR (
 IFNULL(ao.ExtraPayType, '0') <> '1'
 AND NOT (SIT.PaymentAfterArrivalFlg = 1
      AND DATE_ADD( (SELECT MIN(ch.ClaimDate) FROM T_ClaimHistory AS ch WHERE ch.ClaimPattern = 1 AND ch.OrderSeq = o2.OrderSeq), INTERVAL (SELECT MAX(sbps.NumUseDay) AS MaxNumUseDay FROM T_SiteSbpsPayment sbps WHERE sbps.SiteId = o2.SiteId AND sbps.ValidFlg = 1 AND sbps.UseStartDate < CURRENT_DATE()) DAY ) >= CURRENT_DATE()
      AND NOT EXISTS (SELECT 1 FROM T_ReceiptControl AS rc WHERE rc.OrderSeq = o2.P_OrderSeq)
     )))
	AND
	(
		o2.CombinedClaimTargetStatus IS NULL OR
		o2.CombinedClaimTargetStatus IN (0, 91, 92)
	)
    AND (o2.OutOfAmends IS NULL OR o2.OutOfAmends = 0)
    AND (o2.Deli_ConfirmArrivalFlg IS NULL OR o2.Deli_ConfirmArrivalFlg IN (-1, 0))
    %s
GROUP BY
    IFNULL(o2.OemId,0)
EOQ;
	}

	/**
	 * OEMごとの着荷確認待ち"最小配送方法"を取得する。
	 *
	 * @param string $limitDate 配送－伝票番号入力日
	 * @param int $oemId OEMID
	 * @param array $aryExCondition 追加検索条件
	 * @return int 最小配送方法
	 */
	public function getArrivalConfirmMinDeliMethodByOem($limitDate, $limitDateT, $oemId, $aryExCondition)
	{
        $query = $this->_getQueryForArrivalConfirmMinDeliMethodByOem();

        $addWhere = "";
        if ($oemId != -1) {
            $addWhere .= " AND IFNULL(o2.OemId, 0) = " . $oemId;
        }
        if ($aryExCondition['onlyetc']) {
            $addWhere .= " AND    (SIT.EtcAutoArrivalFlg = 1 OR (SIT.EtcAutoArrivalFlg = 2 AND SIT.EtcAutoArrivalNumber = OITM.Deli_JournalNumber)) ";
            $addWhere .= " AND    ENT.Special01Flg = 1 ";
        }
        if ($aryExCondition['fixPattern'] != 0) {
            $addWhere .= " AND    MPC.FixPattern = " . $aryExCondition['fixPattern'];
        }
        if ($aryExCondition['entid'] != '') {
            $addWhere .= " AND    ENT.LoginId LIKE '%" . $aryExCondition['entid'] . "' ";
        }
        if ($aryExCondition['onlyprnt']) {
            $addWhere .= " AND    IFNULL(o2.CombinedClaimTargetStatus, 0) IN (91, 92) ";
        }

        $Date = array( ':limitDateT' => $limitDateT);
        if (($limitDate != '')) {
            $addWhere .= " AND    DATE(SUM.Deli_JournalIncDate) >= :limitDate ";
            $Date = $Date + array( ':limitDate' => $limitDate );
        }

        if ($aryExCondition['exceptPrintedBilling']) {
            $addWhere .= " AND  (( o2.DataStatus >= 51 ";
            $addWhere .= "OR  o2.ClaimSendingClass = 12 ";
            $addWhere .= "OR ENT.SelfBillingMode IS NULL ";
            $addWhere .= "OR ENT.SelfBillingMode = 0 ) ";
            $addWhere .= "OR SIT.SelfBillingFlg = 0 )";
        }

        $query = sprintf($query, $addWhere);

        // delete code which get claimIntervalDays

        return  $this->_adapter->query($query)->execute($Date)->current()['MinDeliveryMethod'];
	}

	/**
	 * OEM別要着荷確認"最小配送方法"集計のベースSQLを取得する
	 *
	 * @access private
	 * @return string
	 */
	private function _getQueryForArrivalConfirmMinDeliMethodByOem() {
	    return <<<EOQ
SELECT
	IFNULL(MIN(SUM.Deli_DeliveryMethod),-1) AS MinDeliveryMethod
FROM
	T_Order o2 STRAIGHT_JOIN
	T_OrderSummary SUM ON SUM.OrderSeq = o2.OrderSeq STRAIGHT_JOIN
    M_DeliveryMethod DM ON DM.DeliMethodId = SUM.Deli_DeliveryMethod
    INNER JOIN T_OrderItems OITM ON OITM.OrderItemId = SUM.OrderItemId
    INNER JOIN T_Enterprise ENT ON ENT.EnterpriseId = o2.EnterpriseId
    INNER JOIN M_PayingCycle MPC ON MPC.PayingCycleId = ENT.PayingCycleId
    INNER JOIN T_Site SIT ON SIT.SiteId = o2.SiteId
    INNER JOIN AT_Order AS ao ON ao.OrderSeq = o2.OrderSeq
WHERE
	o2.Cnl_Status = 0 AND
	o2.DataStatus > 31 AND
	(
		o2.CloseReason IS NULL OR
		o2.CloseReason IN (0, 1)
	)
    AND DATE(SUM.Deli_JournalIncDate) <= :limitDateT
    AND ( ao.CreditTransferRequestFlg <> '0' OR (
    IFNULL(ao.ExtraPayType, '0') <> '1'
    AND NOT (SIT.PaymentAfterArrivalFlg = 1
         AND DATE_ADD( (SELECT MIN(ch.ClaimDate) FROM T_ClaimHistory AS ch WHERE ch.ClaimPattern = 1 AND ch.OrderSeq = o2.OrderSeq), INTERVAL (SELECT MAX(sbps.NumUseDay) AS MaxNumUseDay FROM T_SiteSbpsPayment sbps WHERE sbps.SiteId = o2.SiteId AND sbps.ValidFlg = 1 AND sbps.UseStartDate < CURRENT_DATE()) DAY ) >= CURRENT_DATE()
         AND NOT EXISTS (SELECT 1 FROM T_ReceiptControl AS rc WHERE rc.OrderSeq = o2.P_OrderSeq)
        )))
	AND
	(
		o2.CombinedClaimTargetStatus IS NULL OR
		o2.CombinedClaimTargetStatus IN (0, 91, 92)
	)
    AND (o2.OutOfAmends IS NULL OR o2.OutOfAmends = 0)
    AND (o2.Deli_ConfirmArrivalFlg IS NULL OR o2.Deli_ConfirmArrivalFlg IN (-1, 0))
%s
EOQ;
	}
}
