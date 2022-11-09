<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_Smbcpa(Smbcpa情報)テーブルへのアダプタ
 */
class TableSmbcpa {

    protected $_name = 'T_Smbcpa';
    protected $_primary = array('SmbcpaId');
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
     * @param int $smbcpaId SEQ
     * @return ResultInterface
     */
    public function find($smbcpaId)
    {
        $sql  = " SELECT * FROM T_Smbcpa WHERE SmbcpaId = :SmbcpaId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SmbcpaId' => $smbcpaId,
        );

        return $stm->execute($prm);
    }

    /**
     * 更新キーとしてOEM IDを指定してレコードの更新を実行する
     *
     * @param array $data 更新内容
     * @param int $oemId OEM ID
     */
    public function saveUpdateByOemId($data, $oid) {

        $row = $this->findByOemId($oid)->current();
        if (!$row) {
            throw new \Exception('invalid oem id');
        }

        return $this->saveUpdate($data, $row['SmbcpaId']);
    }

    /**
     * OEM IDを指定してSmbcpa契約情報を取得する
     *
     * @param int $oemId OEM ID
     * @return ResultInterface
     */
    public function findByOemId($oemId) {

        $sql  = " SELECT * FROM T_Smbcpa WHERE OemId = :OemId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $oemId,
        );

        return $stm->execute($prm);
    }

    /**
     * 登録済みのすべてのSmbcpa契約情報サマリーを取得する。
     * 戻りリストの要素は、T_Smbcpaの情報とそれに関連付けられたOEM名、
     * 登録グループ数が付与される
     *
     * @return ResultInterface
     */
    public function fetchSummaries() {
        $q = <<<EOQ
SELECT
    oem.OemNameKj,
    smbcpa.*,
    (SELECT COUNT(*) FROM
     T_SmbcpaAccountGroup grp
     WHERE
        grp.SmbcpaId = smbcpa.SmbcpaId AND
        IFNULL(grp.ReturnedFlg, 0) = 0
    ) AS GroupCount
FROM
    T_Smbcpa smbcpa INNER JOIN
    (
        SELECT 0 AS OemId, 'キャッチボール' AS OemNameKj
        UNION ALL
        SELECT OemId, OemNameKj FROM T_Oem
    ) oem ON oem.OemId = smbcpa.OemId
ORDER BY
    oem.OemId
EOQ;
        return $this->_adapter->query($q)->execute(null);
    }

    /**
     * Smbcpa契約情報が関連付けられていないOEMのOEM情報を取得する
     *
     * @return ResultInterface
     */
    public function findNotBoundOemInfo() {
        $q = <<<EOQ
SELECT
    oem.*
FROM
    (
        SELECT 0 AS OemId, 'キャッチボール' AS OemNameKj
        UNION ALL
        SELECT OemId, OemNameKj FROM T_Oem
    ) oem LEFT OUTER JOIN
    T_Smbcpa smbcpa ON smbcpa.OemId = oem.OemId
WHERE
    smbcpa.SmbcpaId IS NULL
ORDER BY
    oem.OemId
EOQ;
        return $this->_adapter->query($q)->execute(null);
    }

    /**
     * 指定OEMに関連付けられた、Smbcpa契約情報サマリーを取得する。
     * 戻り値はfetchSummaries()の戻りリストの要素と同じ構造の連想配列で、
     * 指定OEMにSmbcpa契約情報が関連付けられていない場合はnullを返す
     *
     * @param int $oemId OEM ID
     * @return array
     */
    public function findSummaryByOemId($oid) {
        foreach($this->fetchSummaries() as $row) {
            if($row['OemId'] == $oid) return $row;
        }
        return null;
    }

    /**
     * 新しいレコードをインサートする。
     *
     * @param array $data インサートする連想配列
     * @return プライマリキーのバリュー
     */
    public function saveNew($data)
    {
        $sql  = " INSERT INTO T_Smbcpa (OemId, RegistDate, DisplayName, Memo, BankName, BankCode, ValidFlg) VALUES (";
        $sql .= "   :OemId ";
        $sql .= " , :RegistDate ";
        $sql .= " , :DisplayName ";
        $sql .= " , :Memo ";
        $sql .= " , :BankName ";
        $sql .= " , :BankCode ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $data['OemId'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':DisplayName' => $data['DisplayName'],
                ':Memo' => $data['Memo'],
                ':BankName' => $data['BankName'],
                ':BankCode' => $data['BankCode'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $smbcpaId SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $smbcpaId)
    {
        $row = $this->find($smbcpaId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_Smbcpa ";
        $sql .= " SET ";
        $sql .= "     OemId = :OemId ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   DisplayName = :DisplayName ";
        $sql .= " ,   Memo = :Memo ";
        $sql .= " ,   BankName = :BankName ";
        $sql .= " ,   BankCode = :BankCode ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE SmbcpaId = :SmbcpaId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SmbcpaId' => $smbcpaId,
                ':OemId' => $row['OemId'],
                ':RegistDate' => $row['RegistDate'],
                ':DisplayName' => $row['DisplayName'],
                ':Memo' => $row['Memo'],
                ':BankName' => $row['BankName'],
                ':BankCode' => $row['BankCode'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }
}
