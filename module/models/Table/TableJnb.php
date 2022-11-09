<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_Jnb(JNB情報)テーブルへのアダプタ
 */
class TableJnb {

	protected $_name = 'T_Jnb';
	protected $_primary = array('JnbId');
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
	 * JNB情報データを取得する
	 *
	 * @param int $jnbId SEQ
	 * @return ResultInterface
	 */
	public function find($jnbId)
	{
        $sql  = " SELECT * FROM T_Jnb WHERE JnbId = :JnbId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':JnbId' => $jnbId,
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

        return $this->saveUpdate($data, $row['JnbId']);
	}

	/**
	 * OEM IDを指定してJNB契約情報を取得する
	 *
	 * @param int $oemId OEM ID
	 * @return ResultInterface
	 */
	public function findByOemId($oemId) {

        $sql  = " SELECT * FROM T_Jnb WHERE OemId = :OemId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $oemId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 登録済みのすべてのJNB契約情報サマリーを取得する。
	 * 戻りリストの要素は、T_Jnbの情報とそれに関連付けられたOEM名、
	 * 登録グループ数が付与される
	 *
	 * @return ResultInterface
	 */
	public function fetchSummaries() {
	    $q = <<<EOQ
SELECT
	oem.OemNameKj,
	jnb.*,
	(SELECT COUNT(*) FROM
	 T_JnbAccountGroup grp
	 WHERE
		grp.JnbId = jnb.JnbId AND
		IFNULL(grp.ReturnedFlg, 0) = 0
	) AS GroupCount
FROM
	T_Jnb jnb INNER JOIN
	(
		SELECT 0 AS OemId, 'キャッチボール' AS OemNameKj
		UNION ALL
		SELECT OemId, OemNameKj FROM T_Oem
	) oem ON oem.OemId = jnb.OemId
ORDER BY
	oem.OemId
EOQ;
	    return $this->_adapter->query($q)->execute(null);
	}

	/**
	 * JNB契約情報が関連付けられていないOEMのOEM情報を取得する
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
	T_Jnb jnb ON jnb.OemId = oem.OemId
WHERE
	jnb.JnbId IS NULL
ORDER BY
	oem.OemId
EOQ;
	    return $this->_adapter->query($q)->execute(null);
	}

	/**
	 * 指定OEMに関連付けられた、JNB契約情報サマリーを取得する。
	 * 戻り値はfetchSummaries()の戻りリストの要素と同じ構造の連想配列で、
	 * 指定OEMにJNB契約情報が関連付けられていない場合はnullを返す
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
        $sql  = " INSERT INTO T_Jnb (OemId, RegistDate, DisplayName, Memo, BankName, BankCode, ValidFlg) VALUES (";
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
	 * @param int $jnbId SEQ
	 * @return ResultInterface
	 */
	public function saveUpdate($data, $jnbId)
	{
        $row = $this->find($jnbId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_Jnb ";
        $sql .= " SET ";
        $sql .= "     OemId = :OemId ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   DisplayName = :DisplayName ";
        $sql .= " ,   Memo = :Memo ";
        $sql .= " ,   BankName = :BankName ";
        $sql .= " ,   BankCode = :BankCode ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE JnbId = :JnbId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':JnbId' => $jnbId,
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
