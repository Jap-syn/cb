<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * V_WaitForFirstClaimビュー
 */
class ViewWaitForFirstClaim
{
	protected $_name = 'V_WaitForFirstClaim';
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
	 * 全ての初回請求待ち注文データを取得する。
	 *
	 * @return ResultInterface
	 */
	public function getAll()
	{
        $sql = " SELECT * FROM V_WaitForFirstClaim ORDER BY OrderSeq ";
        return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 印刷ジョブ転送待ち注文データを取得する（同梱ツール利用事業者分を除く）。
	 *
	 * @return ResultInterface
	 * @see 請求ストップの注文は初回請求も行わない。
	 */
	public function getToPrint()
	{
		// 直接クエリ方式に変更（2014.8.21 eda）
		$q = <<<EOQ
SELECT
	o.OrderSeq,
	o.OrderId,
	o.ReceiptOrderDate,
	o.RegistDate,
	o.EnterpriseId,
	(SELECT IncreArCaption
	 FROM V_OrderCustomer
	 WHERE OrderSeq = o.OrderSeq
	) AS IncreArCaption,
	s.NameKj,
	s.UnitingAddress,
	o.UseAmount,
	o.Ent_OrderId,
	s.DestNameKj,
	s.DestPostalCode,
	s.DestUnitingAddress,
	s.DestPhone,
	CASE
		WHEN s.RegNameKj <> s.RegDestNameKj THEN 1
		ELSE 0
	END AS IsAnotherDeli,
	e.EnterpriseNameKj,
	o.SiteId
FROM
	T_Order o INNER JOIN
	T_OrderSummary s ON s.OrderSeq = o.OrderSeq INNER JOIN
	T_Enterprise e ON e.EnterpriseId = o.EnterpriseId
WHERE
	o.DataStatus = 41 AND
	o.Cnl_Status = 0 AND
	-- BookedCount = 0
	(
		SELECT COUNT(*)
		FROM T_ClaimHistory
		WHERE OrderSeq = o.OrderSeq AND PrintedFlg = 0
	) = 0 AND
	(LetterClaimStopFlg is null OR LetterClaimStopFlg = 0) AND
	(CombinedClaimTargetStatus NOT IN (1,2) OR CombinedClaimTargetStatus IS null) AND
	o.EnterpriseId IN (
		SELECT EnterpriseId
		FROM T_Enterprise
		WHERE SelfBillingMode IS NULL OR SelfBillingMode <= 0
	)
ORDER BY
	o.OrderSeq ASC
EOQ;

        return $this->_adapter->query($q)->execute(null);
	}

	/**
	 * （旧）印刷ジョブ転送待ち注文データを取得する（同梱ツール利用事業者分を除く）。
	 * @access private
	 * @return ResultInterface
	 * @see 請求ストップの注文は初回請求も行わない。
	 */
	private function getToPrint_()
	{
		$where = 'BookedCount = 0 AND (LetterClaimStopFlg is null OR LetterClaimStopFlg = 0) AND (CombinedClaimTargetStatus NOT IN (1,2) OR CombinedClaimTargetStatus IS null)' .
			' AND ' .
			'EnterpriseId IN (SELECT EnterpriseId FROM T_Enterprise WHERE SelfBillingMode IS NULL OR SelfBillingMode <= 0)';

        $sql = " SELECT * FROM V_WaitForFirstClaim WHERE " . $where . " ORDER BY OrderSeq ";
        return $this->_adapter->query($sql)->execute(null);
	}

    /**
     * 同梱ツール利用事業者向けの印刷ジョブ転送待ち注文データ（配送先情報付き）を取得する。
     *
     * @param null|int $ent_id 事業者ID。省略可能
     * @return ResultInterface
     */
    public function getToPrintSB($ent_id = null)
    {
        $prm = array();
// ↓↓↓現行と比較してレスポンスが不安ではあるが、20秒→4秒程度になったのでOKとする
        // 2013-07-10
        // V_WaitForFirstClaimの利用を廃止し、最小限の結合/カラム指定の
        // クエリに変更
        $query = <<<EOQ
SELECT  o.OrderSeq
    ,   o.OrderId
    ,   o.ReceiptOrderDate
    ,   o.RegistDate
    ,   s.NameKj
    ,   s.UnitingAddress
    ,   o.UseAmount
    ,   o.Ent_OrderId
    ,   s.DestNameKj
    ,   s.DestPostalCode
    ,   s.DestUnitingAddress
    ,   s.DestPhone
    ,   CASE
            WHEN s.RegNameKj <> s.RegDestNameKj THEN 1
            ELSE 0
        END AS IsAnotherDeli
FROM    T_Order o
        INNER JOIN T_OrderSummary s ON (s.OrderSeq = o.OrderSeq)
        INNER JOIN T_Enterprise e ON (o.EnterpriseId = e.EnterpriseId)
        INNER JOIN T_Site sit ON (o.SiteId = sit.SiteId)
WHERE   o.OrderSeq = o.P_OrderSeq
AND     (SELECT MIN(DataStatus) FROM T_Order WHERE P_OrderSeq = o.OrderSeq AND Cnl_Status = 0) = 41
AND     o.ConfirmWaitingFlg = 0
AND     IFNULL(o.CombinedClaimTargetStatus, 0) IN (0, 91, 92) -- 請求取りまとめ済みのもの
AND     e.SelfBillingMode > 0
AND     sit.SelfBillingFlg = 1
AND     o.ClaimSendingClass <> 12  -- 別送指定されていないもの(現行との辻褄あわせ)
%s
ORDER BY
        o.OrderSeq ASC
EOQ;
// ↑↑↑現行と比較してレスポンスが不安ではあるが、20秒→4秒程度になったのでOKとする
        // 補足説明：LetterStopFlgの条件指定がないのは「同梱請求時は紙請求ストップ設定を無視する」という
        // 仕様に基づいている（[Staff-all 1302]）

        $where = '';
        if ($ent_id) {
            $where = " AND o.EnterpriseId = :EnterpriseId ";
            $prm += array(':EnterpriseId' => $ent_id);
        }

        $query = sprintf($query, $where);
        return $this->_adapter->query($query)->execute($prm);
    }

    /**
     * 同梱ツール利用事業者向けの印刷ジョブ転送待ち注文データの件数を取得する。
     *
     * @param int $order_id 注文ID
     * @return array
     */
    public function getToPrintSBCount($order_id)
    {
        $query = <<<EOQ
SELECT  o.OrderId
FROM    T_Order o
        INNER JOIN T_OrderSummary s ON (s.OrderSeq = o.OrderSeq)
        INNER JOIN T_Enterprise e ON (o.EnterpriseId = e.EnterpriseId)
        INNER JOIN T_Site sit ON (o.SiteId = sit.SiteId)
WHERE   o.OrderSeq = o.P_OrderSeq
AND     (SELECT MIN(DataStatus) FROM T_Order WHERE P_OrderSeq = o.OrderSeq AND Cnl_Status = 0) = 41
AND     o.ConfirmWaitingFlg = 0
AND     IFNULL(o.CombinedClaimTargetStatus, 0) IN (0, 91, 92) -- 請求取りまとめ済みのもの
AND     e.SelfBillingMode > 0
AND     sit.SelfBillingFlg = 1
AND     o.ClaimSendingClass <> 12  -- 別送指定されていないもの(現行との辻褄あわせ)
AND     o.OrderId = :OrderId
EOQ;

        $prm = array(
            ':OrderId' => $order_id,
        );
        // 補足説明：LetterStopFlgの条件指定がないのは「同梱請求時は紙請求ストップ設定を無視する」という
        // 仕様に基づいている（[Staff-all 1302]）
        $ri = $this->_adapter->query($query)->execute($prm);
        return ($ri->count() > 0) ? ResultInterfaceToArray($ri) : array();
    }

	/**
	 * すべての印刷ジョブ転送待ち注文データを取得する（同梱ツール利用事業者分を除く）。
	 *
	 * @return ResultInterface
	 */
	public function getToPrintAll()
	{
		// TODO: 2013.7.10現在どこからも呼び出されていないが、紙請求ストップフラグの扱いを検討する必要アリ
		// → CB印刷分は考慮する必要があるが、同梱分は考慮外にする必要があるため
		$sql .= " SELECT * FROM V_WaitForFirstClaim WHERE BookedCount = 0 AND (LetterClaimStopFlg is null OR LetterClaimStopFlg = 0) ORDER BY OrderSeq ";
		return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 印刷ジョブ転送待ち注文データ件数を取得する（同梱ツール利用事業者分を除く）。
	 *
	 * @return int 件数
	 */
	public function getToPrintCount()
	{
		$query = "
			SELECT
			    COUNT(DISTINCT ORD.P_OrderSeq) AS CNT
			FROM
			    T_Order ORD INNER JOIN
				T_Enterprise ENT ON ORD.EnterpriseId = ENT.EnterpriseId INNER JOIN
		        T_Site SIT ON ORD.SiteId = SIT.SiteId INNER JOIN
		        T_PayingAndSales PAS ON PAS.OrderSeq = ORD.OrderSeq LEFT OUTER JOIN
		        T_ClaimControl CLM ON ORD.P_OrderSeq = CLM.OrderSeq
			WHERE
			    ORD.Cnl_Status = 0 AND
		        ORD.ConfirmWaitingFlg = 0 AND
		        IFNULL(CombinedClaimTargetStatus, 0) IN (0, 91, 92) AND
		        (ORD.LetterClaimStopFlg is null OR ORD.LetterClaimStopFlg = 0) AND
				(
		          ( ORD.DataStatus = 41 AND ( SIT.SelfBillingFlg = 0 OR ( SIT.SelfBillingFlg = 1 AND ORD.ClaimSendingClass = 12)))  -- 通常の初回請求書発行条件
                  OR
		          ( ORD.DataStatus >= 51 AND ORD.DataStatus < 91 AND CLM.ReissueClass <> 0 )  -- 初回請求書再発行指示の対象検索条件（初回再発行は必ず別送）
                )
			";

        return (int)$this->_adapter->query($query)->execute(null)->current()['CNT'];
	}

    /**
     * 同梱ツール利用事業者向けの印刷ジョブ転送待ち注文データ件数を取得する。
     *
     * @param int $ent_id 事業者ID。省略可能
     * @return int 件数
     */
    public function getToPrintCountSB($ent_id)
    {
        $prm = array();
// ↓↓↓現行と比較してレスポンスが不安ではあるが、20秒→4秒程度になったのでOKとする
        $query = "
SELECT  COUNT(*)
FROM    T_Order o
        INNER JOIN T_OrderSummary s ON (s.OrderSeq = o.OrderSeq)
        INNER JOIN T_Enterprise e ON (o.EnterpriseId = e.EnterpriseId)
        INNER JOIN T_Site sit ON (o.SiteId = sit.SiteId)
WHERE   o.OrderSeq = o.P_OrderSeq
AND     (SELECT MIN(DataStatus) FROM T_Order WHERE P_OrderSeq = o.OrderSeq AND Cnl_Status = 0) = 41
AND     o.ConfirmWaitingFlg = 0
AND     IFNULL(o.CombinedClaimTargetStatus, 0) IN (0, 91, 92) -- 請求取りまとめ済みのもの
AND     e.SelfBillingMode > 0
AND     sit.SelfBillingFlg = 1
AND     o.ClaimSendingClass <> 12  -- 別送指定されていないもの(現行との辻褄あわせ)
AND     e.EnterpriseId = :EnterpriseId
";
// ↑↑↑現行と比較してレスポンスが不安ではあるが、20秒→4秒程度になったのでOKとする
        $prm += array(':EnterpriseId' => $ent_id);

        return (int)$this->_adapter->query($query)->execute($prm)->current()['CNT'];
    }

	/**
	 * すべての印刷ジョブ転送待ち注文データ件数を取得する。
	 *
	 * @return int 件数
	 */
	public function getToPrintCountAll()
	{
		// TODO: 2013.7.10現在未使用メソッドだが、紙請求ストップフラグの扱いを検討する必要アリ
		// → CB印刷分は考慮する必要があるが、同梱分は考慮外にする必要があるため
		$query = "
			SELECT
			    COUNT(*) AS CNT
			FROM
			    T_Order ORD
			WHERE
			    ORD.DataStatus = 41 AND
			    ORD.Cnl_Status = 0 AND
			    (SELECT
			        COUNT(*)
			    FROM
			        T_ClaimHistory
			    WHERE
			        OrderSeq = ORD.OrderSeq AND
			        PrintedFlg = 0
			    ) = 0 AND
				(ORD.LetterClaimStopFlg IS NULL OR ORD.LetterClaimStopFlg = 0)
			";

        return (int)$this->_adapter->query($query)->execute(null)->current()['CNT'];
	}

	/**
	 * 印刷完了待ち注文データを取得する（同梱ツール利用事業者分を除く）。
	 *
	 * @return ResultInterface
	 */
	public function getPrinted()
	{
		$where = <<<EOQ
OrderSeq IN (
	SELECT o.OrderSeq FROM T_Order o
	WHERE
		o.DataStatus = 41 AND
		o.Cnl_Status = 0 AND
		(SELECT COUNT(*)
		 FROM T_ClaimHistory h
		 WHERE
			h.OrderSeq = o.OrderSeq AND
			h.PrintedFlg = 0 AND
			h.EnterpriseBillingCode IS NULL
		) > 0 AND
		(CombinedClaimTargetStatus NOT IN (1,2) OR CombinedClaimTargetStatus IS null)
)
EOQ;

        $sql = " SELECT * FROM V_WaitForFirstClaim WHERE " . $where . " ORDER BY OrderSeq ";
        return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 同梱ツール利用事業者向けの印刷完了待ち注文データを取得する。
	 *
	 * @return ResultInterface
	 */
	public function getPrintedSB()
	{
		$where = <<<EOQ
OrderSeq IN (
	SELECT o.OrderSeq FROM T_Order o
	WHERE
		o.DataStatus = 41 AND
		o.Cnl_Status = 0 AND
		(SELECT COUNT(*)
		 FROM T_ClaimHistory h
		 WHERE
			h.OrderSeq = o.OrderSeq AND
			h.PrintedFlg = 0 AND
			h.EnterpriseBillingCode IS NOT NULL
		) > 0
)
EOQ;

		$sql = " SELECT * FROM V_WaitForFirstClaim WHERE " . $where . " ORDER BY OrderSeq ";
		return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * すべての印刷完了待ち注文データを取得する。
	 *
	 * @return ResultInterface
	 */
	public function getPrintedAll()
	{
        $sql = " SELECT * FROM V_WaitForFirstClaim WHERE BookedCount > 0 ORDER BY OrderSeq ";
        return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 印刷完了待ち注文データ件数を取得する（同梱ツール利用事業者分を除く）。
	 *
	 * @return int 件数
	 */
	public function getPrintedCount()
	{
		$query = "
			SELECT
			    COUNT(*) AS CNT
			FROM
			    T_Order ORD INNER JOIN
				T_Enterprise ENT ON ORD.EnterpriseId = ENT.EnterpriseId INNER JOIN
		        T_Site SIT ON ORD.SiteId = SIT.SiteId LEFT OUTER JOIN
		        T_ClaimControl CLM ON ORD.OrderSeq = CLM.OrderSeq
			WHERE
		        ORD.OrderSeq = ORD.P_OrderSeq AND
			    ORD.DataStatus = 41 AND
			    ORD.Cnl_Status = 0 AND
		        ORD.ConfirmWaitingFlg = 1 AND
		        IFNULL(CombinedClaimTargetStatus, 0) IN (0, 91, 92) AND
		        (ORD.LetterClaimStopFlg is null OR ORD.LetterClaimStopFlg = 0) AND
				(
		          ( ORD.DataStatus = 41 AND ( IFNULL(ENT.SelfBillingMode, 0) <= 0 OR SIT.SelfBillingFlg = 0 OR ( ENT.SelfBillingMode > 0 AND SIT.SelfBillingFlg = 1 AND ORD.ClaimSendingClass = 12)))  -- 通常の初回請求書発行条件
                  OR
		          ( ORD.DataStatus >= 51 AND ORD.DataStatus < 91 AND CLM.ReissueClass <> 0 )  -- 初回請求書再発行指示の対象検索条件（初回再発行は必ず別送）.DataStatus >= 51 AND o.DataStatus < 91 AND clm.ReissueClass <> 0 )  -- 初回請求書再発行指示の対象検索条件（初回再発行は必ず別送）
                )
		";

        return (int)$this->_adapter->query($query)->execute(null)->current()['CNT'];
	}

	/**
	 * 同梱ツール利用事業者向けの印刷完了待ち注文データ件数を取得する。
	 *
	 * @return int 件数
	 */
	public function getPrintedCountSB()
	{
		$query = "
			SELECT
			    COUNT(*) AS CNT
			FROM
			    T_Order ORD
			WHERE
			    ORD.DataStatus = 41 AND
			    ORD.Cnl_Status = 0 AND
			    (SELECT
			        COUNT(*)
			    FROM
			        T_ClaimHistory
			    WHERE
			        OrderSeq = ORD.OrderSeq AND
					EnterpriseBillingCode IS NOT NULL AND
			        PrintedFlg = 0
			    ) > 0
			";

        return (int)$this->_adapter->query($query)->execute(null)->current()['CNT'];
	}

	/**
	 * すべての印刷完了待ち注文データ件数を取得する。
	 *
	 * @return int 件数
	 */
	public function getPrintedCountAll()
	{
		$query = "
			SELECT
			    COUNT(*) AS CNT
			FROM
			    T_Order ORD
			WHERE
			    ORD.DataStatus = 41 AND
			    ORD.Cnl_Status = 0 AND
			    (SELECT
			        COUNT(*)
			    FROM
			        T_ClaimHistory
			    WHERE
			        OrderSeq = ORD.OrderSeq AND
			        PrintedFlg = 0
			    ) > 0
			";

        return (int)$this->_adapter->query($query)->execute(null)->current()['CNT'];
	}

	/**
	 * 指定条件（AND）のデータを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @return ResultInterface
	 */
	public function findWffc($conditionArray)
	{
        $prm = array();
        $sql  = " SELECT * FROM V_WaitForFirstClaim WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
	}

	/**
	 * 指定条件文字列（WHERE句）によるデータを取得する。
	 *
	 * @param string $whereStr WHERE句
	 * @param string $orderStr ORDER句
	 * @return ResultInterface
	 */
	public function findWffcByWhereStr($whereStr, $orderStr)
	{
        $sql = " SELECT * FROM V_WaitForFirstClaim " . $whereStr . " " . $orderStr;
        return $this->_adapter->query($sql)->execute(null);
	}
}
