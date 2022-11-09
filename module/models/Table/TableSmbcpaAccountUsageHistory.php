<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_SmbcpaAccountUsageHistory(Smbcpa口座利用履歴)テーブルへのアダプタ
 */
class TableSmbcpaAccountUsageHistory {
    /** 利用履歴タイプ定数：口座使用 */
    const HISTORY_TYPE_OPEN = 1;

    /** 利用履歴タイプ定数：入金確定 */
    const HISTORY_TYPE_PAY = 2;

    /** 利用履歴タイプ定数：口座解放 */
    const HISTORY_TYPE_RELEASE = 3;

    /** 利用履歴タイプ定数：強制口座解放 */
    const HISTORY_TYPE_FORCE_RELEASE = 4;

    protected $_name = 'T_SmbcpaAccountUsageHistory';
    protected $_primary = array('UsageHistorySeq');
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
     * Smbcpa口座利用履歴データを取得する
     *
     * @param int $usageHistorySeq SEQ
     * @return ResultInterface
     */
    public function find($usageHistorySeq)
    {
        $sql  = " SELECT * FROM T_SmbcpaAccountUsageHistory WHERE UsageHistorySeq = :UsageHistorySeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':UsageHistorySeq' => $usageHistorySeq,
        );

        return $stm->execute($prm);
    }

    /**
     * 指定Smbcpa口座の履歴件数を取得する
     *
     * @param int $accSeq Smbcpa口座SEQ
     * @return int
     */
    public function countHistoriesByAccountSeq($accSeq) {
        $q = <<<EOQ
SELECT COUNT(*) AS cnt
FROM T_SmbcpaAccountUsageHistory
WHERE AccountSeq = :AccountSeq
EOQ;
        return (int)$this->_adapter->query($q)->execute(array(':AccountSeq' => $accSeq))->current()['cnt'];
    }

    /**
     * 指定Smbcpa口座のすべての履歴を取得する
     *
     * @param int $accSeq Smbcpa口座SEQ
     * @param null | string $orderBy ソート順序指定で'ASC'または'DESC'を指定可能。省略時は'DESC'
     * @return ResultInterface
     */
    public function findByAccountSeq($accSeq, $orderBy = 'DESC') {
        $sql = " SELECT * FROM T_SmbcpaAccountUsageHistory WHERE AccountSeq = :AccountSeq ORDER BY UsageHistorySeq " . $orderBy;

        return $this->_adapter->query($sql)->execute(array(':AccountSeq' => $accSeq));
    }

    /**
     * 利用履歴タイプとSmbcpa口座SEQ、関連する注文SEQを指定して
     * 新しい利用履歴を追加する
     *
     * @param int $type 利用履歴タイプ
     * @param int $accSeq Smbcpa口座SEQ
     * @param int $oseq 注文SEQ
     * @param null | int $reason クローズ事由コード。$typeにHISTORY_TYPE_PAYが指定されている場合のみ利用される
     * @param null | string $memo クローズ時メモ。$typeにHISTORY_TYPE_PAYが指定されている場合のみ利用される
     * @return プライマリキーのバリュー
     */
    public function addHistory($type, $accSeq, $oseq, $reason = null, $memo = null) {

        $types = array(
                self::HISTORY_TYPE_OPEN,
                self::HISTORY_TYPE_PAY,
                self::HISTORY_TYPE_RELEASE,
                self::HISTORY_TYPE_FORCE_RELEASE
        );
        if(!in_array($type, $types)) {
            throw new \Exception('invalid type specified on Table_SmbcpaAccountUsageHistory');
        }

        if($type != self::HISTORY_TYPE_PAY) {
            $reason = $memo = null;
        }

        // 当該Smbcpa口座の既存履歴の、MostRecentをすべて0に
        $sql = " UPDATE T_SmbcpaAccountUsageHistory SET MostRecent = 0 WHERE AccountSeq = :AccountSeq ";
        $this->_adapter->query($sql)->execute(array(':AccountSeq' => $accSeq));

        // 新規履歴をインサート
        $data = array(
                'AccountSeq' => $accSeq,
                'UsedDate' => date('Y-m-d H:i:s'),
                'MostRecent' => 1,
                'Type' => $type,
                'OrderSeq' => $oseq,
                'CloseReason' => $reason,
                'CloseMemo' => $memo
        );
        return $this->saveNew($data);
    }

    /**
     * 指定のSmbcpa口座の利用履歴をひとつ前の状態にロールバックする。
     * このメソッドは、履歴挿入と同時に実行した他のDB操作でトラブルがあった場合の救済措置なので
     * 単独で利用してはならない
     *
     * @param int $accSeq Smbcpa口座SEQ
     * @return int ロールバック後の最新履歴SEQ。履歴が存在しない場合は-1を返す
     */
    public function rollbackHistory($accSeq) {

        // 最新履歴を取得
        $recent = $this->findRecentHistoryByAccountSeq($accSeq);
        if(!$recent) {
            // 最新履歴がない場合は-1を返して終了
            return -1;
        }

        // 最新履歴の1つ前の履歴を取得しておく
        $prev = $this->findPrevHistoryByAccountSeq($accSeq);

        // 最新履歴を削除(論理削除)
        $sql = " UPDATE T_SmbcpaAccountUsageHistory SET MostRecent = 0, DeleteFlg = 1 WHERE UsageHistorySeq = :UsageHistorySeq ";
        $this->_adapter->query($sql)->execute(array(':UsageHistorySeq' => $recent['UsageHistorySeq']));

        if($prev) {
            // 最新前が存在していたらそちらを最新扱いとして更新
            $sql = " UPDATE T_SmbcpaAccountUsageHistory SET MostRecent = 1 WHERE UsageHistorySeq = :UsageHistorySeq ";
            $this->_adapter->query($sql)->execute(array(':UsageHistorySeq' => $prev['UsageHistorySeq']));
            return $prev['UsageHistorySeq'];
        } else {
            // 最新前がない場合は-1を返す
            return -1;
        }
    }

    /**
     * 指定注文に関連付けられた口座利用履歴を取得する
     *
     * @param int $oseq 注文SEQ
     * @return ResultInterface
     */
    public function findHistoriesByOrderSeq($oseq) {
        $sql = " SELECT * FROM T_SmbcpaAccountUsageHistory WHERE OrderSeq = :OrderSeq AND IFNULL(DeleteFlg, 0) = 0 ORDER BY UsageHistorySeq ";

        return $this->_adapter->query($sql)->execute(array(':OrderSeq' => $oseq));
    }

    /**
     * 指定Smbcpa口座に関連付けられた口座利用履歴を取得する
     *
     * @param int $accSeq Smbcpa口座SEQ
     * @return ResultInterface
     */
    public function findHistoriesByAccountSeq($accSeq) {
        $sql = " SELECT * FROM T_SmbcpaAccountUsageHistory WHERE AccountSeq = :AccountSeq AND IFNULL(DeleteFlg, 0) = 0 ORDER BY UsageHistorySeq ";

        return $this->_adapter->query($sql)->execute(array(':AccountSeq' => $accSeq));
    }

    /**
     * 指定Smbcpa口座に関連付けられた最新の口座利用履歴を取得する
     *
     * @param int $accSeq Smbcpa口座SEQ
     * @return array | null
     */
    public function findRecentHistoryByAccountSeq($accSeq) {
        $sql = " SELECT * FROM T_SmbcpaAccountUsageHistory WHERE AccountSeq = :AccountSeq AND MostRecent = 1 AND IFNULL(DeleteFlg, 0) = 0 ORDER BY UsageHistorySeq DESC ";
        $row = $this->_adapter->query($sql)->execute(array(':AccountSeq' => $accSeq))->current();

        return ($row) ? $row : null;
    }

    /**
     * 指定Smbcpa口座に関連付けられた、最新のひとつ前の口座利用履歴を取得する
     *
     * @param int $accSeq Smbcpa口座SEQ
     * @return array | null
     */
    public function findPrevHistoryByAccountSeq($accSeq) {
        $sql = " SELECT * FROM T_SmbcpaAccountUsageHistory WHERE AccountSeq = :AccountSeq AND MostRecent = 0 AND IFNULL(DeleteFlg, 0) = 0 ORDER BY UsageHistorySeq DESC ";
        $row = $this->_adapter->query($sql)->execute(array(':AccountSeq' => $accSeq))->current();

        return ($row) ? $row : null;
    }

    /**
     * 指定注文に関連付けられた、強制口座解放の利用履歴件数を取得する
     *
     * @param int $oseq 注文SEQ
     * @return int
     */
    public function countForceReleasedHistoryByOrderSeq($oseq) {
        $q = <<<EOQ
SELECT
    COUNT(*) AS cnt
FROM
    T_SmbcpaAccountUsageHistory
WHERE
    IFNULL(DeleteFlg, 0) = 0 AND
    OrderSeq = :oseq AND
    Type = :type
EOQ;
        return (int)$this->_adapter->query($q)->execute(array('oseq' => $oseq, 'type' => self::HISTORY_TYPE_FORCE_RELEASE))->current()['cnt'];
    }

    /**
     * 新しいレコードをインサートする。
     *
     * @param array $data インサートする連想配列
     * @return プライマリキーのバリュー
     */
    public function saveNew($data)
    {
        $sql  = " INSERT INTO T_SmbcpaAccountUsageHistory (AccountSeq, UsedDate, MostRecent, Type, OrderSeq, CloseReason, CloseMemo, DeleteFlg) VALUES (";
        $sql .= "   :AccountSeq ";
        $sql .= " , :UsedDate ";
        $sql .= " , :MostRecent ";
        $sql .= " , :Type ";
        $sql .= " , :OrderSeq ";
        $sql .= " , :CloseReason ";
        $sql .= " , :CloseMemo ";
        $sql .= " , :DeleteFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AccountSeq' => $data['AccountSeq'],
                ':UsedDate' => $data['UsedDate'],
                ':MostRecent' => isset($data['MostRecent']) ? $data['MostRecent'] : 0,
                ':Type' => $data['Type'],
                ':OrderSeq' => $data['OrderSeq'],
                ':CloseReason' => $data['CloseReason'],
                ':CloseMemo' => $data['CloseMemo'],
                ':DeleteFlg' => isset($data['DeleteFlg']) ? $data['DeleteFlg'] : 0,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $usageHistorySeq SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $usageHistorySeq)
    {
        $row = $this->find($usageHistorySeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_SmbcpaAccountUsageHistory ";
        $sql .= " SET ";
        $sql .= "     AccountSeq = :AccountSeq ";
        $sql .= " ,   UsedDate = :UsedDate ";
        $sql .= " ,   MostRecent = :MostRecent ";
        $sql .= " ,   Type = :Type ";
        $sql .= " ,   OrderSeq = :OrderSeq ";
        $sql .= " ,   CloseReason = :CloseReason ";
        $sql .= " ,   CloseMemo = :CloseMemo ";
        $sql .= " ,   DeleteFlg = :DeleteFlg ";
        $sql .= " WHERE UsageHistorySeq = :UsageHistorySeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':UsageHistorySeq' => $usageHistorySeq,
                ':AccountSeq' => $row['AccountSeq'],
                ':UsedDate' => $row['UsedDate'],
                ':MostRecent' => $row['MostRecent'],
                ':Type' => $row['Type'],
                ':OrderSeq' => $row['OrderSeq'],
                ':CloseReason' => $row['CloseReason'],
                ':CloseMemo' => $row['CloseMemo'],
                ':DeleteFlg' => $row['DeleteFlg'],
        );

        return $stm->execute($prm);
    }
}
