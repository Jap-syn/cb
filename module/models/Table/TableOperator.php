<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_Operatorテーブルへのアダプタ
 */
class TableOperator
{
    protected $_name = 'T_Operator';
    protected $_primary = array('OpId');
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
     * すべてのオペレーターデータを取得する
     *
     * @param bool $asc 昇順ソートの場合はtrueを指定。デフォルトはfalse（＝降順ソート）
     * @return ResultInterface
     */
    public function getAll($asc = false)
    {
        $sql = " SELECT * FROM T_Operator ORDER BY OpId " . ($asc ? "asc" : "desc");
        return $this->_adapter->query($sql)->execute(null);
    }

	/**
	 * 指定オペレーターIDのオペレーターデータを取得する。
	 *
	 * @param string $opId オペレーターID
	 * @return ResultInterface
	 */
	public function findOperator($opId)
	{
        $sql = " SELECT * FROM T_Operator WHERE OpId = :OpId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OpId' => $opId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定オペレーターIDのオペレーターデータを取得する。
	 *
	 * @param string $opId オペレーターID
	 * @return ResultInterface
	 */
	public function findOperator2($opId)
	{
        return $this->findOperator($opId);
	}

	/**
	 * 指定LoginIdが新しいIDか（重複していないか）をチェックする。
	 *
	 * @param string $id ログインID
	 * @return boolean
	 */
	public function isNewLoginId($id)
	{
        $sql = " SELECT COUNT(1) AS cnt FROM T_Operator WHERE LoginId = :LoginId ";

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
        $sql  = " SELECT COUNT(1) AS cnt FROM T_Operator WHERE LoginId = :LoginId AND LoginPasswd = :LoginPasswd ";

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
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO T_Operator (LoginId, LoginPasswd, NameKj, NameKn, Division, RoleCode, ValidFlg, Hashed, LastPasswordChanged, NgAccessCount, NgAccessReferenceDate, RegistDate, RegistId, UpdateDate, UpdateId) VALUES (";
        $sql .= "   :LoginId ";
        $sql .= " , :LoginPasswd ";
        $sql .= " , :NameKj ";
        $sql .= " , :NameKn ";
        $sql .= " , :Division ";
        $sql .= " , :RoleCode ";
        $sql .= " , :ValidFlg ";
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
                ':LoginId' => $data['LoginId'],
                ':LoginPasswd' => $data['LoginPasswd'],
                ':NameKj' => $data['NameKj'],
                ':NameKn' => $data['NameKn'],
                ':Division' => $data['Division'],
                ':RoleCode' => $data['RoleCode'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
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
	 * @param string $eid ログインID
	 * @return ResultInterface
	 */
	public function saveUpdate($data, $eid)
	{
        $row = $this->findOperator($eid)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_Operator ";
        $sql .= " SET ";
        $sql .= "     LoginId = :LoginId ";
        $sql .= " ,   LoginPasswd = :LoginPasswd ";
        $sql .= " ,   NameKj = :NameKj ";
        $sql .= " ,   NameKn = :NameKn ";
        $sql .= " ,   Division = :Division ";
        $sql .= " ,   RoleCode = :RoleCode ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " ,   Hashed = :Hashed ";
        $sql .= " ,   LastPasswordChanged = :LastPasswordChanged ";
        $sql .= " ,   NgAccessCount = :NgAccessCount ";
        $sql .= " ,   NgAccessReferenceDate = :NgAccessReferenceDate ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " WHERE OpId = :OpId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OpId' => $eid,
                ':LoginId' => $row['LoginId'],
                ':LoginPasswd' => $row['LoginPasswd'],
                ':NameKj' => $row['NameKj'],
                ':NameKn' => $row['NameKn'],
                ':Division' => $row['Division'],
                ':RoleCode' => $row['RoleCode'],
                ':ValidFlg' => $row['ValidFlg'],
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
	 * 指定ログインIDのオペレーターデータを取得する。
	 *
	 * @param string $loginId ログインID
	 * @return ResultInterface
	 */
	public function findLoginId($loginId)
	{
	    return $this->_adapter->query(" SELECT * FROM T_Operator WHERE LoginId = :LoginId "
	    )->execute(array(':LoginId' => $loginId));
	}
}
