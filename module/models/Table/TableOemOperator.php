<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * Table_OemOperatorテーブルへのアダプタ
 */
class TableOemOperator
{
	protected $_name = 'T_OemOperator';
	protected $_primary = array('OemOpId');
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
	 * すべてのOEMオペレーターデータを取得する
	 *
	 * @param bool $asc 昇順ソートの場合はtrueを指定。デフォルトはfalse（＝降順ソート）
	 * @return ResultInterface
	 */
	public function getAll($oemId = null, $asc = false)
	{
        $prm = array();
        $sql  = " SELECT * FROM T_OemOperator ";
        if (!is_null($oemId)) {
            $sql .= " WHERE OemId = :OemId";
            $prm += array(':OemId' => $oemId);
        }
        $sql .= " ORDER BY OemOpId " . ($asc ? "asc" : "desc");

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
	}

	/**
	 * 指定OEMオペレーターIDのオペレーターデータを取得する。
	 *
	 * @param string $OemOpId OEMオペレーターID
	 * @return ResultInterface
	 */
	public function findOperator($OemOpId)
	{
        $sql = " SELECT * FROM T_OemOperator WHERE OemOpId = :OemOpId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemOpId' => $OemOpId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定OEMオペレーターIDのオペレーターデータを取得する。
	 *
	 * @param string $OemOpId OEMオペレーターID
	 * @return ResultInterface
	 */
	public function findOperator2($OemOpId)
	{
        return $this->findOperator($opId);
	}

	/**
	 * 指定OEM IDに関連付けられているオペレータデータをすべて取得する
	 *
	 * @param int $oemId OEM ID
	 * @param null | boolean $validOnly 有効アカウントのみに絞り込むかを指定。省略時はtrue（＝有効アカウントのみ）
	 * @return ResultInterface
	 */
	public function findOperatorByOemId($oemId, $validOnly = true)
	{
        $sql = " SELECT * FROM T_OemOperator WHERE OemId = :OemId ";
        if ($validOnly) {
            $sql .= " AND   ValidFlg = 1 ";
        }

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $oemId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定LoginIdが新しいIDか（重複していないか）をチェックする。
	 *
	 * @param string $id ログインID
	 * @return boolean
	 */
	public function isNewLoginId($id)
	{
        $sql = " SELECT COUNT(1) AS cnt FROM T_OemOperator WHERE LoginId = :LoginId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':LoginId' => $id,
        );

        $row = $stm->execute($prm)->current();

        return (intval($row['cnt']) > 0) ? false : true;
	}

	/**
	 * 指定したID、Passwdの組み合わせのオペレーターが存在するか。
	 *
	 * @param string $loginId ログインID
	 * @param string $loginPasswd ログインパスワード
	 * @return boolean
	 */
	public function isExists($loginId, $loginPasswd)
	{
        $sql  = " SELECT COUNT(1) AS cnt FROM T_OemOperator WHERE LoginId = :LoginId AND LoginPasswd = :LoginPasswd ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':LoginId' => $loginId,
                ':LoginPasswd' => $loginPasswd,
        );

        $row = $stm->execute($prm)->current();

        return (intval($row['cnt']) > 0) ? true : false;
	}

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー？
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO T_OemOperator (OemId, LoginId, LoginPasswd, NameKj, NameKn, Division, ValidFlg, RoleCode, Hashed, LastPasswordChanged, NgAccessCount, NgAccessReferenceDate, RegistDate, RegistId, UpdateDate, UpdateId) VALUES (";
        $sql .= "   :OemId ";
        $sql .= " , :LoginId ";
        $sql .= " , :LoginPasswd ";
        $sql .= " , :NameKj ";
        $sql .= " , :NameKn ";
        $sql .= " , :Division ";
        $sql .= " , :ValidFlg ";
        $sql .= " , :RoleCode ";
        $sql .= " , :Hashed ";
        $sql .= " , :LastPasswordChanged ";
        $sql .= " , :NgAccessCount ";
        $sql .= " , :NgAccessReferenceDate ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $data['OemId'],
                ':LoginId' => $data['LoginId'],
                ':LoginPasswd' => $data['LoginPasswd'],
                ':NameKj' => $data['NameKj'],
                ':NameKn' => $data['NameKn'],
                ':Division' => $data['Division'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
                ':RoleCode' => $data['RoleCode'],
                ':Hashed' => isset($data['Hashed']) ? $data['Hashed'] : 0,
                ':LastPasswordChanged' => $data['LastPasswordChanged'],
                ':NgAccessCount' => isset($data['NgAccessCount']) ? $data['NgAccessCount'] : 0,
                ':NgAccessReferenceDate' => $data['NgAccessReferenceDate'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

	/**
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param unknown_type $oid 更新するOemId
	 * @return ResultInterface
	 */
	public function saveUpdate($data, $oid)
	{
        $row = $this->findOperator($oid)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_OemOperator ";
        $sql .= " SET ";
        $sql .= "     OemId = :OemId ";
        $sql .= " ,   LoginId = :LoginId ";
        $sql .= " ,   LoginPasswd = :LoginPasswd ";
        $sql .= " ,   NameKj = :NameKj ";
        $sql .= " ,   NameKn = :NameKn ";
        $sql .= " ,   Division = :Division ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " ,   RoleCode = :RoleCode ";
        $sql .= " ,   Hashed = :Hashed ";
        $sql .= " ,   LastPasswordChanged = :LastPasswordChanged ";
        $sql .= " ,   NgAccessCount = :NgAccessCount ";
        $sql .= " ,   NgAccessReferenceDate = :NgAccessReferenceDate ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " WHERE OemOpId = :OemOpId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemOpId' => $oid,
                ':OemId' => $row['OemId'],
                ':LoginId' => $row['LoginId'],
                ':LoginPasswd' => $row['LoginPasswd'],
                ':NameKj' => $row['NameKj'],
                ':NameKn' => $row['NameKn'],
                ':Division' => $row['Division'],
                ':ValidFlg' => $row['ValidFlg'],
                ':RoleCode' => $row['RoleCode'],
                ':Hashed' => $row['Hashed'],
                ':LastPasswordChanged' => $row['LastPasswordChanged'],
                ':NgAccessCount' => $row['NgAccessCount'],
                ':NgAccessReferenceDate' => $row['NgAccessReferenceDate'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定OEMID／ログインIDのOEMオペレーターデータを取得する。
	 *
	 * @param string $oemId OEMID
	 * @param string $loginId ログインID
	 * @return ResultInterface
	 */
	public function findLoginId($oemId, $loginId)
	{
	    return $this->_adapter->query(" SELECT * FROM T_OemOperator WHERE OemId = :OemId AND LoginId = :LoginId "
            )->execute(array(':OemId' => $oemId, ':LoginId' => $loginId));
	}
}
