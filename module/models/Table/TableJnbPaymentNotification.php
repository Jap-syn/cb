<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_JnbPaymentNotification(JNB入金通知管理)テーブルへのアダプタ
 */
class TableJnbPaymentNotification {
	/** ステータス定数：受信済み @var int */
	const STATUS_RECEIVED = 1;

	/** ステータス定数：応答済み @var int */
	const STATUS_RESPONSED = 2;

	/** ステータス定数：入金保留中 @var int */
	const STATUS_PENDING_RECEIPT = 3;

	/** ステータス定数：入金処理済み @var int */
	const STATUS_RECEIPTED = 9;

	/** ステータス定数：入金不能確定 @var int */
	const STATUS_CANNOT_RECEIPT = -1;

	/** ステータス定数：処理対象外 @var int */
	const STATUS_NOT_AVAILABLE = -9;

	protected $_name = 'T_JnbPaymentNotification';
	protected $_primary = array('NotificationSeq');
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
	 * JNB入金通知管理データを取得する
	 *
	 * @param int $notificationSeq SEQ
	 * @return ResultInterface
	 */
	public function find($notificationSeq)
	{
	    $sql  = " SELECT * FROM T_JnbPaymentNotification WHERE NotificationSeq = :NotificationSeq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':NotificationSeq' => $notificationSeq,
	    );

	    return $stm->execute($prm);
	}

	/**
	 * 取引IDを指定して入金通知管理データを検索する。
	 *
	 * @param string $tranId 取引ID
	 * @param array $target_status 検索対象のステータス。省略時は1：受信済み、2：応答済み、-9：処理対象外として扱われる
	 * @return array | null
	 */
	public function findByTransactionId($tranId, array $target_status = array(self::STATUS_RECEIVED, self::STATUS_RESPONSED, self::STATUS_NOT_AVAILABLE)) {

        $sql = " SELECT * FROM T_JnbPaymentNotification WHERE TransactionId = CAST(:TransactionId AS CHAR) AND IFNULL(DeleteFlg, 0) = 0 ";
        if(is_array($target_status)) {
            $status = array();
            foreach($target_status as $sts) {
                if(is_int($sts)) $status[] = $sts;
            }
            if(!empty($status)) {
                $sql .= (' AND Status IN (' . implode(',', $status) .')');
            }
        }
        $sql .= " ORDER BY NotificationSeq DESC ";

        return $this->_adapter->query($sql)->execute(array(':TransactionId' => $tranId))->current();
	}

	/**
	 * 指定ステータスの通知管理データを取得する
	 *
	 * @param int $status 検索対象のステータス
	 * @param null | int $limit 取得件数の上限。省略時は1000件で、1未満を指定することで無制限になる
	 * @return ResultInterface
	 */
	public function findByStatus($status, $limit = 1000) {
        $sql = " SELECT * FROM T_JnbPaymentNotification WHERE Status = :Status AND IFNULL(DeleteFlg, 0) = 0 ORDER BY NotificationSeq ";
        if ($limit >= 1) {
            $sql .= (" LIMIT " . $limit);
        }

        return $this->_adapter->query($sql)->execute(array(':Status' => $status));
	}

	/**
	 * 指定ステータスの通知管理データ件数を取得する
	 *
	 * @param int $status 検索対象のステータス
	 * @return int 指定ステータスのデータ件数
	 */
	public function countByStatus($status) {
		$q = <<<EOQ
SELECT COUNT(*) AS cnt
FROM T_JnbPaymentNotification
WHERE
	Status = :Status AND
	IFNULL(DeleteFlg, 0) = 0
EOQ;
        return (int)$this->_adapter->query($q)->execute(array(':Status' => $status))->current()['cnt'];
	}

	/**
	 * 自動入金処理対象の通知管理データを取得する
	 *
	 * @param null | int $limit 取得件数の上限。省略時は1000件で、1未満を指定することで無制限になる
	 * @return ResultInterface
	 */
	public function fetchReceiptTargets($limit = 1000) {
	    return $this->findByStatus(self::STATUS_RESPONSED, $limit);
	}

	/**
	 * 自動入金処理対象件数を取得する
	 *
	 * @return int
	 */
	public function countReceiptTargets() {
	    return $this->countByStatus(self::STATUS_RESPONSED);
	}

	/**
	 * 手動入金処理対象の通知管理データを取得する
	 *
	 * @param null | int $limit 取得件数の上限。省略時は1000件で、1未満を指定することで無制限になる
	 * @return ResultInterface
	 */
	public function fetchManualReceiptTargets($limit = 1000) {
	    return $this->findByStatus(self::STATUS_PENDING_RECEIPT, $limit);
	}

	/**
	 * 手動入金処理対象件数を取得する
	 *
	 * @return int
	 */
	public function countManualReceiptTargets() {
	    return $this->countByStatus(self::STATUS_PENDING_RECEIPT);
	}

	/**
	 * 指定注文・指定口座向けの入金保留中通知を削除する。
	 * このメソッドはDeleteFlgに1をセットする論理削除を実行する
	 *
	 * @param int $oseq 注文SEQ
	 * @param int $accSeq JNB口座SEQ
	 * @param string $reason 削除事由
	 */
	public function deletePendingNotifications($oseq, $accSeq, $reason = null) {

        $oseq = (int)$oseq;
        $accSeq = (int)$accSeq;
        $reason = trim(nvl($reason));
        if(!strlen($reason)) {
            $reason = 'delete by deletePendingNotifications method';
        }

        $sql = " SELECT * FROM T_JnbPaymentNotification WHERE OrderSeq = :OrderSeq AND AccountSeq = :AccountSeq AND IFNULL(DeleteFlg, 0) = 0 AND Status = :Status ";
        $ri = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $oseq, ':AccountSeq' => $accSeq, ':Status' => self::STATUS_PENDING_RECEIPT));
        foreach ($ri as $row) {
            $this->saveUpdate(array('DeleteFlg' => 1, 'RejectReason' => sprintf('%s %s', $reason, $row->RejectReason)), $row['NotificationSeq']);
        }
	}

	/**
	 * 指定JNB口座に対する通知履歴を取得する
	 *
	 * @param int $accSeq JNB口座SEQ
	 * @param null | string $order_by ソート順指定。'asc'または'desc'を指定可能で省略時や対象外指定時は'asc'と見なされる
	 * @return ResultInterface
	 */
	public function findByAccountSeq($accSeq, $order_by = 'asc') {
        $q = <<<EOQ
SELECT
	nf.*,
	ord.OrderId,
	sum.NameKj
FROM
	T_JnbPaymentNotification nf LEFT OUTER JOIN
	T_Order ord ON ord.OrderSeq = nf.OrderSeq LEFT OUTER JOIN
	T_OrderSummary sum ON sum.OrderSeq = ord.OrderSeq
WHERE
	nf.AccountSeq = :AccountSeq
ORDER BY
	nf.NotificationSeq
EOQ;
        return $this->_adapter->query($q . ' ' . $order_by)->execute(array(':AccountSeq' => $accSeq));
	}

	/**
	 * 指定日の通知履歴を取得する
	 *
	 * @param string $date 日付
	 * @param null | string $oder ソート順。'asc'または'desc'を指定可能で、省略時は'desc'
	 * @param null | int $limit 1度に取得する通知の上限。省略時は200
	 * @param null | int $offset 通知取得オフセット。省略時は0
	 * @return ResultInterface
	 */
	public function findByDate($date, $order = 'desc', $limit = 200, $offset = 0) {

        $date = date('Y-m-d', strtotime($date));
        $limit = (int)$limit;
        if($limit < 1) $limit = 1;
        $offset = (int)$offset;
        if($offset < 0) $offset = 0;

        $q = <<<EOQ
SELECT
	nf.*,
	ord.OrderSeq,
	ord.OrderId,
	sum.NameKj
FROM
	T_JnbPaymentNotification nf LEFT OUTER JOIN
	T_Order ord ON ord.OrderSeq = nf.OrderSeq LEFT OUTER JOIN
	T_OrderSummary sum ON sum.OrderSeq = ord.OrderSeq
WHERE ReceivedDate >= :ReceivedDate
AND   ReceivedDate < (:ReceivedDate + INTERVAL 1 DAY)
EOQ;
        $q .= (" ORDER BY NotificationSeq " . $order);
        $q .= (" LIMIT " . $limit);
        $q .= (" OFFSET " . $offset);

        return $this->_adapter->query($q)->execute(array(':ReceivedDate' => $date));
	}

	/**
	 * 指定日の通知履歴件数を取得する
	 *
	 * @param string $date 日付
	 * @return int
	 */
	public function countByDate($date) {
        $date = date('Y-m-d', strtotime($date));

        $sql = " SELECT COUNT(*) AS cnt FROM T_JnbPaymentNotification WHERE ReceivedDate >= :ReceivedDate AND ReceivedDate < (:ReceivedDate + INTERVAL 1 DAY) ";
        return (int)$this->_adapter->query($sql)->execute(array(':ReceivedDate' => $date))->current()['cnt'];
	}

	/**
	 * 指定年月における日別の通知受信件数統計を取得する
	 *
	 * @param int $year
	 * @param int $month
	 * @return array 日付をキー、その日付の受信件数を値とした連想配列。キー'year'、'month'に対象年・月も格納される
	 */
	public function countByDateInYearMonth($year, $month) {

		// 年の初期補正
		$year = (int)$year;
		if($year < 1970) $year = 1970;

		// 月を含めて最終補正
		$month = (int)$month;
		if($month < 1) {
			// n年0月 → (n - 1)年12月 になるように補正
			$year += floor($month / 12);
			if($month % 12 == 0) $year--;
			$month = 12 + ($month % 12);
		} else
		if($month > 12) {
			$year += floor($month / 12);
			$month = $month % 12;
			if($month == 0) {
				$month = 12;
				$year--;
			}
		}

        $q = <<<EOQ
SELECT
	DATE_FORMAT(ReceivedDate, '%Y-%m-%d') AS date,
	COUNT(*) AS cnt
FROM
	T_JnbPaymentNotification
WHERE
	ReceivedDate >= :ReceivedDate AND ReceivedDate < (:ReceivedDate + INTERVAL 1 MONTH)
GROUP BY
	DATE_FORMAT(ReceivedDate, '%Y-%m-%d')
ORDER BY
	DATE_FORMAT(ReceivedDate, '%Y-%m-%d')
EOQ;
        $ri = $this->_adapter->query($q)->execute(array(':ReceivedDate' => sprintf('%04d-%02d-01', $year, $month)));
        $results = array(
                'year' => $year,
                'month' => $month
        );
        foreach ($ri as $row) {
            $results[$row['date']] = (int)$row['cnt'];
        }
        return $results;
	}

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 * @see protected function LogicJnbNotificationHandler::createTransactionRow、が呼出しの参考箇所です(20150609_1830)
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO T_JnbPaymentNotification (TransactionId, ReceivedDate, Status, ResponseDate, ReceivedRawData, ResponseRawData, ReqBranchCode, ReqAccountNumber, AccountSeq, OrderSeq, ReceiptAmount, ReceiptProcessDate, ReceiptDate, LastProcessDate, RejectReason, DeleteFlg) VALUES (";
        $sql .= "   :TransactionId ";
        $sql .= " , :ReceivedDate ";
        $sql .= " , :Status ";
        $sql .= " , :ResponseDate ";
        $sql .= " , :ReceivedRawData ";
        $sql .= " , :ResponseRawData ";
        $sql .= " , :ReqBranchCode ";
        $sql .= " , :ReqAccountNumber ";
        $sql .= " , :AccountSeq ";
        $sql .= " , :OrderSeq ";
        $sql .= " , :ReceiptAmount ";
        $sql .= " , :ReceiptProcessDate ";
        $sql .= " , :ReceiptDate ";
        $sql .= " , :LastProcessDate ";
        $sql .= " , :RejectReason ";
        $sql .= " , :DeleteFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':TransactionId' => $data['TransactionId'],
                ':ReceivedDate' => $data['ReceivedDate'],
                ':Status' => isset($data['Status']) ? $data['Status'] : 1,
                ':ResponseDate' => $data['ResponseDate'],
                ':ReceivedRawData' => $data['ReceivedRawData'],
                ':ResponseRawData' => $data['ResponseRawData'],
                ':ReqBranchCode' => $data['ReqBranchCode'],
                ':ReqAccountNumber' => $data['ReqAccountNumber'],
                ':AccountSeq' => $data['AccountSeq'],
                ':OrderSeq' => $data['OrderSeq'],
                ':ReceiptAmount' => $data['ReceiptAmount'],
                ':ReceiptProcessDate' => $data['ReceiptProcessDate'],
                ':ReceiptDate' => $data['ReceiptDate'],
                ':LastProcessDate' => $data['LastProcessDate'],
                ':RejectReason' => $data['RejectReason'],
                ':DeleteFlg' => isset($data['DeleteFlg']) ? $data['DeleteFlg'] : 0,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

	/**
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param int $notificationSeq SEQ
	 * @return ResultInterface
	 */
	public function saveUpdate($data, $notificationSeq)
	{
        $row = $this->find($notificationSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_JnbPaymentNotification ";
        $sql .= " SET ";
        $sql .= "     TransactionId = :TransactionId ";
        $sql .= " ,   ReceivedDate = :ReceivedDate ";
        $sql .= " ,   Status = :Status ";
        $sql .= " ,   ResponseDate = :ResponseDate ";
        $sql .= " ,   ReceivedRawData = :ReceivedRawData ";
        $sql .= " ,   ResponseRawData = :ResponseRawData ";
        $sql .= " ,   ReqBranchCode = :ReqBranchCode ";
        $sql .= " ,   ReqAccountNumber = :ReqAccountNumber ";
        $sql .= " ,   AccountSeq = :AccountSeq ";
        $sql .= " ,   OrderSeq = :OrderSeq ";
        $sql .= " ,   ReceiptAmount = :ReceiptAmount ";
        $sql .= " ,   ReceiptProcessDate = :ReceiptProcessDate ";
        $sql .= " ,   ReceiptDate = :ReceiptDate ";
        $sql .= " ,   LastProcessDate = :LastProcessDate ";
        $sql .= " ,   RejectReason = :RejectReason ";
        $sql .= " ,   DeleteFlg = :DeleteFlg ";
        $sql .= " WHERE NotificationSeq = :NotificationSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':NotificationSeq' => $notificationSeq,
                ':TransactionId' => $row['TransactionId'],
                ':ReceivedDate' => $row['ReceivedDate'],
                ':Status' => $row['Status'],
                ':ResponseDate' => $row['ResponseDate'],
                ':ReceivedRawData' => $row['ReceivedRawData'],
                ':ResponseRawData' => $row['ResponseRawData'],
                ':ReqBranchCode' => $row['ReqBranchCode'],
                ':ReqAccountNumber' => $row['ReqAccountNumber'],
                ':AccountSeq' => $row['AccountSeq'],
                ':OrderSeq' => $row['OrderSeq'],
                ':ReceiptAmount' => $row['ReceiptAmount'],
                ':ReceiptProcessDate' => $row['ReceiptProcessDate'],
                ':ReceiptDate' => $row['ReceiptDate'],
                ':LastProcessDate' => $row['LastProcessDate'],
                ':RejectReason' => $row['RejectReason'],
                ':DeleteFlg' => $row['DeleteFlg'],
        );

        return $stm->execute($prm);
	}
}
