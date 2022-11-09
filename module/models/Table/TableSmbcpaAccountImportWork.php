<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_SmbcpaAccountImportWork(Smbcpa口座インポート作業)テーブルへのアダプタ
 */
class TableSmbcpaAccountImportWork {

    protected $_name = 'T_SmbcpaAccountImportWork';
    protected $_primary = array('Seq');
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
     * Smbcpa口座インポート作業データを取得する
     *
     * @param int $seq SEQ
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql  = " SELECT * FROM T_SmbcpaAccountImportWork WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
        );

        return $stm->execute($prm);
    }

    /**
     * 指定プロセスキーで登録されたインポート対象の一時データを取得する
     *
     * @param string $processKey 一時データグループを示すプロセスキー
     * @param int | null $limit 取得件数の上限。省略時は500
     * @return ResultInterface
     */
    public function findImportTargets($processKey, $limit = 500) {

        $sql = " SELECT * FROM T_SmbcpaAccountImportWork WHERE ProcessKey = :ProcessKey AND IFNULL(DeleteFlg, 0) = 0 AND CsvError IS NULL ORDER BY Seq LIMIT :Limit ";

        return $this->_adapter->query($sql)->execute(array(':ProcessKey' => $processKey, ':Limit' => $limit));
    }

    /**
     * 指定プロセスキーで登録されたインポート対象の一時データの残件数を取得する
     *
     * @param string $processKey 一時データグループを示すプロセスキー
     * @return int
     */
    public function countTargets($processKey) {
        $q = <<<EOQ
SELECT COUNT(*) AS cnt
FROM T_SmbcpaAccountImportWork
WHERE
    ProcessKey = :ProcessKey AND
    IFNULL(DeleteFlg, 0) = 0 AND
    CsvError IS NULL
EOQ;
        return (int)$this->_adapter->query($q)->execute(array(':ProcessKey' => $processKey))->current()['cnt'];
    }

    /**
     * 指定プロセスキーで登録されたデータでインポートエラーが発生した件数を取得する
     *
     * @param string $processKey 一時データ―グループを示すプロセスキー
     * @return int
     */
    public function countImportError($processKey) {
        $q = <<<EOQ
SELECT COUNT(*) AS cnt
FROM T_SmbcpaAccountImportWork
WHERE
    ProcessKey = :ProcessKey AND
    ImportError IS NOT NULL
EOQ;
        return (int)$this->_adapter->query($q)->execute(array(':ProcessKey' => $processKey))->current()['cnt'];
    }

    /**
     * 指定プロセスキーで最後に登録されたSmbcpa口座グループIDを取得する
     *
     * @param string $processKey 一時データグループを示すプロセスキー
     * @return int | null 指定プロセスキーを持つ一時データで最後に使用されたSmbcpa口座グループID。該当データがない場合はnull
     */
    public function getLastAccountGroupId($processKey) {
        $q = <<<EOQ
SELECT
    AccountGroupId
FROM
    T_SmbcpaAccountImportWork
WHERE
    ProcessKey = :ProcessKey AND
    AccountGroupId IS NOT NULL
ORDER BY
    Seq DESC
LIMIT 1
EOQ;
        $row = $this->_adapter->query($q)->execute(array(':ProcessKey' => $processKey))->current();
        return ($row) ? $row['AccountGroupId'] : null;
    }

    /**
     * 新しいレコードをインサートする。
     *
     * @param array $data インサートする連想配列
     * @return プライマリキーのバリュー
     */
    public function saveNew($data)
    {
        $sql  = " INSERT INTO T_SmbcpaAccountImportWork (SmbcpaId, AccountGroupId, ProcessKey, StartTime, EndTime, BranchCode, AccountNumber, AccountHolder, ManageKey, ManageKeyLabel, NumberingDate, EffectiveDate, ModifiedDate, SmbcpaStatus, ExpirationDate, LastReceiptDate, ReleasedDate, DeleteFlg, OpId, CsvError, ImportError) VALUES (";
        $sql .= "   :SmbcpaId ";
        $sql .= " , :AccountGroupId ";
        $sql .= " , :ProcessKey ";
        $sql .= " , :StartTime ";
        $sql .= " , :EndTime ";
        $sql .= " , :BranchCode ";
        $sql .= " , :AccountNumber ";
        $sql .= " , :AccountHolder ";
        $sql .= " , :ManageKey ";
        $sql .= " , :ManageKeyLabel ";
        $sql .= " , :NumberingDate ";
        $sql .= " , :EffectiveDate ";
        $sql .= " , :ModifiedDate ";
        $sql .= " , :SmbcpaStatus ";
        $sql .= " , :ExpirationDate ";
        $sql .= " , :LastReceiptDate ";
        $sql .= " , :ReleasedDate ";
        $sql .= " , :DeleteFlg ";
        $sql .= " , :OpId ";
        $sql .= " , :CsvError ";
        $sql .= " , :ImportError ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SmbcpaId' => $data['SmbcpaId'],
                ':AccountGroupId' => $data['AccountGroupId'],
                ':ProcessKey' => $data['ProcessKey'],
                ':StartTime' => $data['StartTime'],
                ':EndTime' => $data['EndTime'],
                ':BranchCode' => $data['BranchCode'],
                ':AccountNumber' => $data['AccountNumber'],
                ':AccountHolder' => $data['AccountHolder'],
                ':ManageKey' => $data['ManageKey'],
                ':ManageKeyLabel' => $data['ManageKeyLabel'],
                ':NumberingDate' => $data['NumberingDate'],
                ':EffectiveDate' => $data['EffectiveDate'],
                ':ModifiedDate' => $data['ModifiedDate'],
                ':SmbcpaStatus' => $data['SmbcpaStatus'],
                ':ExpirationDate' => $data['ExpirationDate'],
                ':LastReceiptDate' => $data['LastReceiptDate'],
                ':ReleasedDate' => $data['ReleasedDate'],
                ':DeleteFlg' => isset($data['DeleteFlg']) ? $data['DeleteFlg'] : 0,
                ':OpId' => $data['OpId'],
                ':CsvError' => $data['CsvError'],
                ':ImportError' => $data['ImportError'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param @param int $seq SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $seq)
    {
        $row = $this->find($seq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_SmbcpaAccountImportWork ";
        $sql .= " SET ";
        $sql .= "     SmbcpaId = :SmbcpaId ";
        $sql .= " ,   AccountGroupId = :AccountGroupId ";
        $sql .= " ,   ProcessKey = :ProcessKey ";
        $sql .= " ,   StartTime = :StartTime ";
        $sql .= " ,   EndTime = :EndTime ";
        $sql .= " ,   BranchCode = :BranchCode ";
        $sql .= " ,   AccountNumber = :AccountNumber ";
        $sql .= " ,   AccountHolder = :AccountHolder ";
        $sql .= " ,   ManageKey = :ManageKey ";
        $sql .= " ,   ManageKeyLabel = :ManageKeyLabel ";
        $sql .= " ,   NumberingDate = :NumberingDate ";
        $sql .= " ,   EffectiveDate = :EffectiveDate ";
        $sql .= " ,   ModifiedDate = :ModifiedDate ";
        $sql .= " ,   SmbcpaStatus = :SmbcpaStatus ";
        $sql .= " ,   ExpirationDate = :ExpirationDate ";
        $sql .= " ,   LastReceiptDate = :LastReceiptDate ";
        $sql .= " ,   ReleasedDate = :ReleasedDate ";
        $sql .= " ,   DeleteFlg = :DeleteFlg ";
        $sql .= " ,   OpId = :OpId ";
        $sql .= " ,   CsvError = :CsvError ";
        $sql .= " ,   ImportError = :ImportError ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':SmbcpaId' => $row['SmbcpaId'],
                ':AccountGroupId' => $row['AccountGroupId'],
                ':ProcessKey' => $row['ProcessKey'],
                ':StartTime' => $row['StartTime'],
                ':EndTime' => $row['EndTime'],
                ':BranchCode' => $row['BranchCode'],
                ':AccountNumber' => $row['AccountNumber'],
                ':AccountHolder' => $row['AccountHolder'],
                ':ManageKey' => $row['ManageKey'],
                ':ManageKeyLabel' => $row['ManageKeyLabel'],
                ':NumberingDate' => $row['NumberingDate'],
                ':EffectiveDate' => $row['EffectiveDate'],
                ':ModifiedDate' => $row['ModifiedDate'],
                ':SmbcpaStatus' => $row['SmbcpaStatus'],
                ':ExpirationDate' => $row['ExpirationDate'],
                ':LastReceiptDate' => $row['LastReceiptDate'],
                ':ReleasedDate' => $row['ReleasedDate'],
                ':DeleteFlg' => $row['DeleteFlg'],
                ':OpId' => $row['OpId'],
                ':CsvError' => $row['CsvError'],
                ':ImportError' => $row['ImportError'],
        );

        return $stm->execute($prm);
    }
}
