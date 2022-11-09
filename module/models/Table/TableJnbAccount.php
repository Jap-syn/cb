<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_JnbAccount(JNB口座)テーブルへのアダプタ
 */
class TableJnbAccount {
	/** 口座ステータス定数：空き @var int */
	const ACCOUNT_STATUS_BLANK = 0;

	/** 口座ステータス定数：請求中 @var int */
	const ACCOUNT_STATUS_CLAIMING = 1;

	/** 口座ステータス定数：開放待ち @var int */
	const ACCOUNT_STATUS_CLOSED = -1;

	/**
	 * 定義済みの口座ステータス定数をすべて取得する
	 *
	 * @static
	 * @return array
	 */
	public static function getAvailableStatuses() {
		return array_keys(self::getStatusMap());
	}

	/**
	 * 定義済みの口座ステータス定数とそれに対応する表示文字の連想配列を取得する
	 *
	 * @static
	 * @return array
	 */
	public static function getStatusMap() {
		return array(
			self::ACCOUNT_STATUS_BLANK => '空き',
			self::ACCOUNT_STATUS_CLAIMING => '請求中',
			self::ACCOUNT_STATUS_CLOSED => '開放待ち'
		);
	}

	protected $_name = 'T_JnbAccount';
	protected $_primary = array('AccountSeq');
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
	 * JNB口座データを取得する
	 *
	 * @param int $accountSeq SEQ
	 * @return ResultInterface
	 */
	public function find($accountSeq)
	{
	    $sql  = " SELECT * FROM T_JnbAccount WHERE AccountSeq = :AccountSeq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':AccountSeq' => $accountSeq,
	    );

	    return $stm->execute($prm);
	}

	/**
	 * 指定の支店コードと口座番号を持つJNB口座データを取得する
	 *
	 * @param string $branchCode 支店コード
	 * @param string $accountNumber 口座番号
	 * @return array | null
	 */
	public function findAccount($branchCode, $accountNumber) {

        if(strlen($accountNumber) < 7) $accountNumber = sprintf('%07s', $accountNumber);

        $sql = " SELECT * FROM T_JnbAccount WHERE BranchCode = CAST(:BranchCode AS CHAR) AND AccountNumber = :AccountNumber ";
        return $this->_adapter->query($sql)->execute(array(':BranchCode' => $branchCode, ':AccountNumber' => $accountNumber))->current();
	}

	/**
	 * 指定のJNB口座グループに集約されたJNB口座データを取得する。
	 *
	 * @param int $groupId JNB口座グループID
	 * @param null | array $targetStatus 検索対象のStatus。無効な値は検索時に除外され、
	 *                                   有効な値が1つもない場合は未指定として扱われる
	 * @param int | null $page ページ位置。省略時は1
	 * @param int | null $limit 1ページあたりの件数。省略時は500
	 * @return ResultInterface
	 */
	public function getAccountsByGroupId($groupId, array $targetStatus = null, $page = 1, $limit = 500) {

        $page = (int)$page;
        if($page < 1) $page = 1;

        $limit = (int)$limit;
        if($limit > 0) {
            $offset = $limit * ($page - 1);
        } else {
            $limit = $offset = null;
        }

        if(!is_array($targetStatus)) $targetStatus = array();
        $statusList = array();
        $validStatuses = self::getAvailableStatuses();
        foreach($targetStatus as $sts) {
            if(strlen($sts) && in_array($sts, $validStatuses)) $statusList[] = $sts;
        }

        $q = <<<EOQ
SELECT
	acc.*,
	his.OrderSeq,
	ord.OrderId,
	ord.OemId,
	sum.NameKj,
	his.UsageHistorySeq,
	ord.DataStatus,
	ord.Cnl_Status,
	SUM(CASE WHEN nf.NotificationSeq IS NOT NULL THEN 1 ELSE 0 END) AS NotifyCount,
	SUM(CASE WHEN IFNULL(nf.Status, 0) = 2 THEN 1 ELSE 0 END) AS WaitReceiptCount,
	SUM(CASE WHEN IFNULL(nf.Status, 0) = 3 THEN 1 ELSE 0 END) AS WaitManualReceiptCount,
	MAX(CASE WHEN IFNULL(nf.Status, 0) = 2 THEN nf.NotificationSeq ELSE 0 END) AS WaitReceiptNfSeq,
	MAX(CASE WHEN IFNULL(nf.Status, 0) = 3 THEN nf.NotificationSeq ELSE 0 END) AS WaitManualReceiptNfSeq
FROM
	T_JnbAccount acc LEFT OUTER JOIN
	T_JnbAccountUsageHistory his ON (
		his.AccountSeq = acc.AccountSeq AND
		his.MostRecent = 1 AND
		his.Type IN (1, 2) AND
		IFNULL(his.DeleteFlg, 0) = 0
	) LEFT OUTER JOIN
	T_Order ord ON ord.OrderSeq = his.OrderSeq LEFT OUTER JOIN
	T_OrderSummary sum ON sum.OrderSeq = ord.OrderSeq LEFT OUTER JOIN
	T_JnbPaymentNotification nf ON (
		nf.AccountSeq = acc.AccountSeq AND
		nf.OrderSeq = ord.OrderSeq AND
		IFNULL(nf.DeleteFlg, 0) = 0
	)
WHERE
    acc.AccountGroupId = :AccountGroupId
EOQ;
        if (!empty($statusList)) {
            $q .= ' AND acc.Status IN (' . implode(',', $statusList) .')';
        }
        $q .= " GROUP BY acc.AccountSeq ORDER BY acc.AccountSeq ";
        if ($limitCondition = $limit !== null) {
            $q .= (" LIMIT " . $limit . " OFFSET " . $offset);
        }

        return $this->_adapter->query($q)->execute(array(':AccountGroupId' => $groupId));
	}

	/**
	 * 指定のJNB口座グループに集約されたJNB口座データの件数を取得する
	 *
	 * @param int $groupId JNB口座グループID
	 * @param null | array $targetStatus 検索対象のStatus。無効な値は検索時に除外され、
	 *                                   有効な値が1つもない場合は未指定として扱われる
	 * @return int 指定条件のJNB口座データの件数
	 */
	public function countAccountsByGroupId($groupId, array $targetStatus = array()) {

        $statusList = array();
        if(!is_array($targetStatus)) $targetStatus = array();
        $validStatuses = self::getAvailableStatuses();
        foreach($targetStatus as $sts) {
            if(strlen($sts) && in_array($sts, $validStatuses)) $statusList[] = $sts;
        }

        $sql = " SELECT COUNT(*) AS cnt FROM T_JnbAccount WHERE AccountGroupId = :AccountGroupId ";
        if (!empty($statusList)) {
            $sql .= ' AND Status IN (' . implode(',', $statusList) .')';
        }

        return (int)$this->_adapter->query($sql)->execute(array(':AccountGroupId' => $groupId))->current()['cnt'];
	}

	/**
	 * 指定JNB口座グループ配下に登録されたJNB口座の利用状況統計を取得する。
	 * 戻り値は口座ステータスをキーとした連想配列で、値の各要素はstatus、label、countの各キーに
	 * それぞれ値を対応させた連想配列となる
	 *
	 * @param int $groupId JNB口座グループ
	 * @return array
	 */
	public function getAccountUsageByGroupId($groupId, $as_map = true) {
	    $q = <<<EOQ
SELECT
	Status,
	COUNT(*) AS Count
FROM
	T_JnbAccount
WHERE
	AccountGroupId = :AccountGroupId
GROUP BY
	Status
EOQ;
        $realMap = array();
        $results = array();
        $ri = $this->_adapter->query($q)->execute(array(':AccountGroupId' => $groupId));
        foreach($ri as $row) {
            $realMap[$row['Status']] = (int)$row['Count'];
        }
        foreach(self::getStatusMap() as $key => $label) {
            $results[$key] = array(
                    'status' => $key,
                    'label' => $label,
                    'count' => isset($realMap[$key]) ? $realMap[$key] : 0
            );
        }
        return $results;
	}

	/**
	 * 指定JNB契約配下で次の請求に使用できるJNB口座データを取得する
	 *
	 * @param int $jnbId JNB ID
	 * @return array | null
	 */
	public function fetchNextAccount($jnbId) {
		$q = <<<EOQ
SELECT
	acc.AccountSeq
FROM
	T_JnbAccount acc FORCE INDEX (Idx_T_JnbAccount03) INNER JOIN
	T_JnbAccountGroup grp ON grp.AccountGroupId = acc.AccountGroupId
WHERE
	IFNULL(grp.ReturnedFlg, 0) = 0 AND
	acc.Status = 0
ORDER BY
	acc.AccountSeq
LIMIT
	1
EOQ;
        $row = $this->_adapter->query($q)->execute()->current();
        return ($row) ? $this->find($row['AccountSeq'])->current() : null;

	}

	/**
	 * 指定OEM配下で次の請求に使用できるJNB口座データを取得する
	 *
	 * @param int $oemId OEM ID
	 * @return array | null
	 */
	public function fetchNextAccountByOemId($oemId) {

        $jnbTable = new \models\Table\TableJnb($this->_adapter);
        $jnbData = $jnbTable->findByOemId($oemId)->current();
        if(!$jnbData) {
            throw new \Exception('invalid JnbId specified.');
        }
        return $this->fetchNextAccount($jnbData['JnbId']);
	}

	/**
	 * 指定注文に関連付けられている請求中JNB口座データを取得する。
	 * このメソッドは、最後に関連付けられた注文が指定注文で且つ現在の状態が請求中の場合にのみ
	 * 有効なデータを返し、それ以外の場合はnullを返す
	 *
	 * @param int $oseq 注文SEQ
	 * @return array | null
	 */
	public function fetchClaimingAccountByOrderSeq($oseq) {
		// 最新履歴が当該注文且つ口座使用で、口座の状態が請求中のもののみ抽出
		$q = <<<EOQ
SELECT
	acc.*
FROM
	T_JnbAccount acc INNER JOIN
	T_JnbAccountUsageHistory his ON
		(his.AccountSeq = acc.AccountSeq AND his.MostRecent = 1)
WHERE
	acc.Status = 1 AND
	his.Type = 1 AND
	IFNULL(his.DeleteFlg, 0) = 0 AND
	his.OrderSeq = :OrderSeq
ORDER BY
	his.UsageHistorySeq DESC
EOQ;
        $row = $this->_adapter->query($q)->execute(array(':OrderSeq' => $oseq))->current();
        return ($row) ? $this->find($row['AccountSeq'])->current() : null;
	}

	/**
	 * 指定注文に関連付けられているクローズ状態のJNB口座データを取得する。
	 * このメソッドは、最後に関連付けられた注文が指定注文で且つ現在の状態がクローズの場合にのみ
	 * 有効なデータを返し、それ以外の場合はnullを返す
	 *
	 * @param int $oseq 注文SEQ
	 * @return array | null
	 */
	public function fetchClosedAccountByOrderSeq($oseq) {
		// 最新履歴が当該注文且つ口座クローズで、口座の状態がクローズのもののみ抽出
		$q = <<<EOQ
SELECT
	acc.*
FROM
	T_JnbAccount acc INNER JOIN
	T_JnbAccountUsageHistory his ON
		(his.AccountSeq = acc.AccountSeq AND his.MostRecent = 1)
WHERE
	acc.Status = -1 AND
	his.Type = 2 AND
	IFNULL(his.DeleteFlg, 0) = 0 AND
	his.OrderSeq = :OrderSeq
ORDER BY
	his.UsageHistorySeq DESC
EOQ;
        $row = $this->_adapter->query($q)->execute(array(':OrderSeq' => $oseq))->current();
        return ($row) ? $this->find($row['AccountSeq'])->current() : null;
	}

	/**
	 * 指定注文に関連付けられている、空き状態ではないJNB口座データを取得する。
	 * このメソッドは、最後に関連付けられた注文が指定注文で且つ現在の状態が空きでない場合にのみ
	 * 有効なデータを返し、それ以外の場合はnullを返す
	 *
	 * @param int $oseq 注文SEQ
	 * @return array | null
	 */
	public function fetchNonBlankAccountByOrderSeq($oseq) {
		// 最新履歴が当該注文且つ開放・強制解放のいずれでもなく、口座の状態が請求中またはクローズみの
		// もののみ抽出
		$q = <<<EOQ
SELECT
	acc.*
FROM
	T_JnbAccount acc INNER JOIN
	T_JnbAccountUsageHistory his ON
		(his.AccountSeq = acc.AccountSeq AND his.MostRecent = 1)
WHERE
	acc.Status IN (1, -1) AND
	his.Type NOT IN (3, 4) AND
	IFNULL(his.DeleteFlg, 0) = 0 AND
	his.OrderSeq = :OrderSeq
ORDER BY
	his.UsageHistorySeq DESC
EOQ;
        $row = $this->_adapter->query($q)->execute(array(':OrderSeq' => $oseq))->current();
        return ($row) ? $this->find($row['AccountSeq'])->current() : null;
	}

	/**
	 * 指定JNB口座の全利用履歴を取得する
	 *
	 * @param int $accSeq JNB口座SEQ
	 * @return ResultInterface 指定口座の全利用履歴。利用日時の降順でソートされる
	 */
	public function findUsageHistories($accSeq) {
		$q = <<<EOQ
SELECT
	*
FROM
	T_JnbAccount acc INNER JOIN
	T_JnbAccountUsageHistory his ON his.AccountSeq = acc.AccountSeq INNER JOIN
	T_Order ord ON ord.OrderSeq = his.OrderSeq INNER JOIN
	T_OrderSummary sum ON sum.OrderSeq = ord.OrderSeq LEFT OUTER JOIN
	T_ClaimHistory chis ON (
		chis.OrderSeq = ord.OrderSeq AND
		chis.Seq = (SELECT MAX(Seq) FROM T_ClaimHistory WHERE OrderSeq = ord.OrderSeq)
	) LEFT OUTER JOIN
	T_OemClaimAccountInfo oca ON oca.ClaimHistorySeq = chis.Seq
WHERE
	acc.AccountSeq = :AccountSeq
ORDER BY
	his.UsageHistorySeq DESC
EOQ;
		return $this->_adapter->query($q)->execute(array(':AccountSeq' => $accSeq));
	}

	/**
	 * 支店コード・口座番号を指定してJNB口座の全利用履歴を取得する
	 *
	 * @param string $brCode 支店コード
	 * @param string $accNumber 口座番号
	 * @return ResultInterface | null
	 */
	public function findUsageHistoriesByBranchAndAccountNumber($brCode, $accNumber) {
        $acc = $this->findAccount($brCode, $accNumber);
        if(!$acc) return null;
        return $this->findUsageHistories($acc->AccountSeq);
	}

    /**
     * 新しいレコードをインサートする。
     *
     * @param array $data インサートする連想配列
     * @return プライマリキーのバリュー
     */
    public function saveNew($data)
    {
        $sql  = " INSERT INTO T_JnbAccount (JnbId, AccountGroupId, RegistDate, BranchCode, AccountNumber, AccountHolder, Status, LastStatusChanged, NumberingDate, EffectiveDate, ModifiedDate, JnbStatus, ExpirationDate, LastReceiptDate, ReleasedDate) VALUES (";
        $sql .= "   :JnbId ";
        $sql .= " , :AccountGroupId ";
        $sql .= " , :RegistDate ";
        $sql .= " , :BranchCode ";
        $sql .= " , :AccountNumber ";
        $sql .= " , :AccountHolder ";
        $sql .= " , :Status ";
        $sql .= " , :LastStatusChanged ";
        $sql .= " , :NumberingDate ";
        $sql .= " , :EffectiveDate ";
        $sql .= " , :ModifiedDate ";
        $sql .= " , :JnbStatus ";
        $sql .= " , :ExpirationDate ";
        $sql .= " , :LastReceiptDate ";
        $sql .= " , :ReleasedDate ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':JnbId' => $data['JnbId'],
                ':AccountGroupId' => $data['AccountGroupId'],
                ':RegistDate' => $data['RegistDate'],
                ':BranchCode' => $data['BranchCode'],
                ':AccountNumber' => $data['AccountNumber'],
                ':AccountHolder' => $data['AccountHolder'],
                ':Status' => isset($data['Status']) ? $data['Status'] : 0,
                ':LastStatusChanged' => $data['LastStatusChanged'],
                ':NumberingDate' => $data['NumberingDate'],
                ':EffectiveDate' => $data['EffectiveDate'],
                ':ModifiedDate' => $data['ModifiedDate'],
                ':JnbStatus' => $data['JnbStatus'],
                ':ExpirationDate' => $data['ExpirationDate'],
                ':LastReceiptDate' => $data['LastReceiptDate'],
                ':ReleasedDate' => $data['ReleasedDate'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $accountSeq SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $accountSeq)
    {
        $row = $this->find($accountSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_JnbAccount ";
        $sql .= " SET ";
        $sql .= "     JnbId = :JnbId ";
        $sql .= " ,   AccountGroupId = :AccountGroupId ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   BranchCode = :BranchCode ";
        $sql .= " ,   AccountNumber = :AccountNumber ";
        $sql .= " ,   AccountHolder = :AccountHolder ";
        $sql .= " ,   Status = :Status ";
        $sql .= " ,   LastStatusChanged = :LastStatusChanged ";
        $sql .= " ,   NumberingDate = :NumberingDate ";
        $sql .= " ,   EffectiveDate = :EffectiveDate ";
        $sql .= " ,   ModifiedDate = :ModifiedDate ";
        $sql .= " ,   JnbStatus = :JnbStatus ";
        $sql .= " ,   ExpirationDate = :ExpirationDate ";
        $sql .= " ,   LastReceiptDate = :LastReceiptDate ";
        $sql .= " ,   ReleasedDate = :ReleasedDate ";
        $sql .= " WHERE AccountSeq = :AccountSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AccountSeq' => $accountSeq,
                ':JnbId' => $row['JnbId'],
                ':AccountGroupId' => $row['AccountGroupId'],
                ':RegistDate' => $row['RegistDate'],
                ':BranchCode' => $row['BranchCode'],
                ':AccountNumber' => $row['AccountNumber'],
                ':AccountHolder' => $row['AccountHolder'],
                ':Status' => $row['Status'],
                ':LastStatusChanged' => $row['LastStatusChanged'],
                ':NumberingDate' => $row['NumberingDate'],
                ':EffectiveDate' => $row['EffectiveDate'],
                ':ModifiedDate' => $row['ModifiedDate'],
                ':JnbStatus' => $row['JnbStatus'],
                ':ExpirationDate' => $row['ExpirationDate'],
                ':LastReceiptDate' => $row['LastReceiptDate'],
                ':ReleasedDate' => $row['ReleasedDate'],
        );

        return $stm->execute($prm);
    }
}
