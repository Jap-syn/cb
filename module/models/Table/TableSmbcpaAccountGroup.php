<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_SmbcpaAccountGroup(Smbcpa口座グループ)テーブルへのアダプタ
 */
class TableSmbcpaAccountGroup {

    protected $_name = 'T_SmbcpaAccountGroup';
    protected $_primary = array('AccountGroupId');
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
     * Smbcpa情報データを取得する
     *
     * @param int $accountGroupId SEQ
     * @return ResultInterface
     */
    public function find($accountGroupId)
    {
        $sql  = " SELECT * FROM T_SmbcpaAccountGroup WHERE AccountGroupId = :AccountGroupId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AccountGroupId' => $accountGroupId,
        );

        return $stm->execute($prm);
    }


    /**
     * 指定Smbcpaの管理グループサマリーを取得する。
     * 結果データはT_SmbcpaAccountGroupのスキーマにOEM IDとOEM名、および下記統計情報が追加された情報となる。
     *     TotalCount - 登録口座合計
     *     UsableCount - 利用可能口座数
     *     ClaimingCount - 請求中口座数
     *     LooseUsableCount - 実質利用可能口座数（空き＋クローズ済み）
     *     UsablePercentage - 利用可能口座率
     *     ClaimingPercentage - 請求中口座率
     *     LooseUsablePercentage - 実質利用可能口座率
     *
     * @param int $smbcpaId Smbcpa ID
     * @return ResultInterface
     */
    public function getSummaryBySmbcpaId($smbcpaId) {
        $q = <<<EOQ
SELECT
    oem.OemId,
    oem.OemNameKj,
    grp.*,
    CASE grp.ReturnedFlg WHEN 1 THEN 0 ELSE IFNULL(acc.TotalCount, 0) END AS TotalCount,
    CASE grp.ReturnedFlg WHEN 1 THEN 0 ELSE IFNULL(acc.UsableCount, 0) END AS UsableCount,
    CASE grp.ReturnedFlg WHEN 1 THEN 0 ELSE IFNULL(acc.ClaimingCount, 0) END AS ClaimingCount,
    CASE grp.ReturnedFlg WHEN 1 THEN 0 ELSE IFNULL(acc.LooseUsableCount, 0) END AS LooseUsableCount,
    CASE grp.ReturnedFlg WHEN 1 THEN 0 ELSE IFNULL(acc.UsedCount, 0) END AS UsedCount,
    ROUND(CASE
        WHEN grp.ReturnedFlg = 1 THEN 0
        WHEN IFNULL(acc.TotalCount, 0) = 0 THEN 0
        ELSE IFNULL(acc.UsableCount, 0) / IFNULL(acc.TotalCount, 0)
    END * 100, 2) AS UsablePercentage,
    ROUND(CASE
        WHEN grp.ReturnedFlg = 1 THEN 0
        WHEN IFNULL(acc.TotalCount, 0) = 0 THEN 0
        ELSE IFNULL(acc.ClaimingCount, 0) / IFNULL(acc.TotalCount, 0)
    END * 100, 2) AS ClaimingPercentage,
    ROUND(CASE
        WHEN grp.ReturnedFlg = 1 THEN 0
        WHEN IFNULL(acc.TotalCount, 0) = 0 THEN 0
        ELSE IFNULL(acc.LooseUsableCount, 0) / IFNULL(acc.TotalCount, 0)
    END * 100, 2) AS LooseUsablePercentage,
    ROUND(CASE
        WHEN grp.ReturnedFlg = 1 THEN 0
        WHEN IFNULL(acc.TotalCount, 0) = 0 THEN 0
        ELSE IFNULL(acc.UsedCount, 0) / IFNULL(acc.TotalCount, 0)
    END * 100, 2) AS UsedPercentage
FROM
    T_SmbcpaAccountGroup grp LEFT OUTER JOIN
    (
        SELECT
            AccountGroupId,
            COUNT(*) AS TotalCount,
            SUM(CASE IFNULL(Status, 0) WHEN 0 THEN 1 ELSE 0 END) AS UsableCount,
            SUM(CASE IFNULL(Status, 0) WHEN 1 THEN 1 ELSE 0 END) AS ClaimingCount,
            SUM(CASE IFNULL(Status, 0) WHEN 1 THEN 0 ELSE 1 END) AS LooseUsableCount,
            SUM(CASE IFNULL(Status, 0) WHEN 0 THEN 0 ELSE 1 END) AS UsedCount
        FROM
            T_SmbcpaAccount
        WHERE
            SmbcpaId = :SmbcpaId
        GROUP BY
            AccountGroupId
    ) acc ON acc.AccountGroupId = grp.AccountGroupId INNER JOIN
    T_Smbcpa smbcpa ON smbcpa.SmbcpaId = grp.SmbcpaId INNER JOIN
    (
        SELECT 0 AS OemId, 'キャッチボール' AS OemNameKj, 1 AS ValidFlg
        UNION ALL
        SELECT OemId, OemNameKj, ValidFlg FROM T_Oem
    ) oem ON oem.OemId = smbcpa.OemId
WHERE
    grp.SmbcpaId = :SmbcpaId
ORDER BY
    grp.AccountGroupId
EOQ;
        return $this->_adapter->query($q)->execute(array(':SmbcpaId' => $smbcpaId));
    }

    /**
     * 指定OEM先の管理グループサマリーを取得する。
     * 結果データはT_SmbcpaAccountGroupのスキーマにOEM IDとOEM名、および下記統計情報が追加された情報となる。
     *     TotalCount - 登録口座合計
     *     UsableCount - 利用可能口座数
     *     ClaimingCount - 請求中口座数
     *     LooseUsableCount - 実質利用可能口座数（空き＋クローズ済み）
     *     UsablePercentage - 利用可能口座率
     *     ClaimingPercentage - 請求中口座率
     *     LooseUsablePercentage - 実質利用可能口座率
     *
     * @param int $oemId OEM ID
     * @return array
     */
    public function getSummaryByOemId($oemId) {

        $smbcpaTable = new \models\Table\TableSmbcpa($this->_adapter);
        $oem = $smbcpaTable->findByOemId($oemId)->current();
        return ($oem) ? ResultInterfaceToArray($this->getSummaryBySmbcpaId($oem['SmbcpaId'])) : array();
    }

    /**
     * 指定のSmbcpa口座グループの詳細を取得する。
     * 戻り値はSmbcpa口座グループの情報を基本に、Smbcpa契約の情報＋OEM名を付与した連想配列となる。
     *
     * @param int $groupId Smbcpa口座グループID
     * @return array | null
     */
    public function getGroupDetail($groupId) {
        $q = <<<EOQ
SELECT
    oem.OemId,
    oem.OemNameKj,
    smbcpa.RegistDate AS SmbcpaRegistDate,
    smbcpa.DisplayName,
    smbcpa.Memo,
    smbcpa.BankName,
    smbcpa.BankCode,
    smbcpa.ValidFlg,
    grp.*
FROM
    (SELECT 0 AS OemId, 'キャッチボール' AS OemNameKj, 1 AS ValidFlg UNION ALL
     SELECT OemId, OemNameKj, ValidFlg FROM T_Oem) oem INNER JOIN
    T_Smbcpa smbcpa ON smbcpa.OemId = oem.OemId INNER JOIN
    T_SmbcpaAccountGroup grp ON grp.SmbcpaId = smbcpa.SmbcpaId
WHERE
    grp.AccountGroupId = :AccountGroupId
EOQ;
        $row = $this->_adapter->query($q)->execute(array(':AccountGroupId' => $groupId))->current();
        return ($row) ? $row : null;
    }

    /**
     * 新しいレコードをインサートする。
     *
     * @param array $data インサートする連想配列
     * @return プライマリキーのバリュー
     */
    public function saveNew($data)
    {
        $sql  = " INSERT INTO T_SmbcpaAccountGroup (SmbcpaId, RegistDate, TotalAccounts, ManageKey, ManageKeyLabel, DepositClass, ReturnedFlg, ReturnedDate) VALUES (";
        $sql .= "   :SmbcpaId ";
        $sql .= " , :RegistDate ";
        $sql .= " , :TotalAccounts ";
        $sql .= " , :ManageKey ";
        $sql .= " , :ManageKeyLabel ";
        $sql .= " , :DepositClass ";
        $sql .= " , :ReturnedFlg ";
        $sql .= " , :ReturnedDate ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SmbcpaId' => $data['SmbcpaId'],
                ':RegistDate' => $data['RegistDate'],
                ':TotalAccounts' => $data['TotalAccounts'],
                ':ManageKey' => $data['ManageKey'],
                ':ManageKeyLabel' => $data['ManageKeyLabel'],
                ':DepositClass' => isset($data['DepositClass']) ? $data['DepositClass'] : 0,
                ':ReturnedFlg' => isset($data['ReturnedFlg']) ? $data['ReturnedFlg'] : 0,
                ':ReturnedDate' => $data['ReturnedDate'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param @param int $accountGroupId SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $accountGroupId)
    {
        $row = $this->find($accountGroupId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_SmbcpaAccountGroup ";
        $sql .= " SET ";
        $sql .= "     SmbcpaId = :SmbcpaId ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   TotalAccounts = :TotalAccounts ";
        $sql .= " ,   ManageKey = :ManageKey ";
        $sql .= " ,   ManageKeyLabel = :ManageKeyLabel ";
        $sql .= " ,   DepositClass = :DepositClass ";
        $sql .= " ,   ReturnedFlg = :ReturnedFlg ";
        $sql .= " ,   ReturnedDate = :ReturnedDate ";
        $sql .= " WHERE AccountGroupId = :AccountGroupId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AccountGroupId' => $accountGroupId,
                ':SmbcpaId' => $row['SmbcpaId'],
                ':RegistDate' => $row['RegistDate'],
                ':TotalAccounts' => $row['TotalAccounts'],
                ':ManageKey' => $row['ManageKey'],
                ':ManageKeyLabel' => $row['ManageKeyLabel'],
                ':DepositClass' => $row['DepositClass'],
                ':ReturnedFlg' => $row['ReturnedFlg'],
                ':ReturnedDate' => $row['ReturnedDate'],
        );

        return $stm->execute($prm);
    }
}
