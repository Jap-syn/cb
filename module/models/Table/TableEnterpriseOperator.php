<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_EnterpriseOperator(加盟店オペレーター)テーブルへのアダプタ
 */
class TableEnterpriseOperator {

    protected $_name = 'T_EnterpriseOperator';
    protected $_primary = array('EnterpriseOpId');
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
     * すべての加盟店オペレーターデータを取得する
     *
     * @param bool $asc 昇順ソートの場合はtrueを指定。デフォルトはfalse（＝降順ソート）
     * @return ResultInterface
     */
    public function getAll($asc = false)
    {
        $sql = " SELECT * FROM T_EnterpriseOperator ORDER BY EnterpriseOpId " . ($asc ? "asc" : "desc");
        return $this->_adapter->query($sql)->execute(null);
    }

    /**
     * 指定した加盟店IDの加盟店オペレーターデータを取得する
     *
     * @param int $enterpriseId 加盟店ID
     * @return ResultInterface
     */
    public function findEnterprise($enterpriseId)
    {
        $sql  = " SELECT * FROM T_EnterpriseOperator WHERE EnterpriseId = :EnterpriseId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
        );

        return $stm->execute($prm);
    }

    /**
     * 加盟店オペレーターデータを取得する
     *
     * @param int $enterpriseOpId 加盟店オペレーターID
     * @return ResultInterface
     */
    public function find($enterpriseOpId)
    {
        $sql  = " SELECT * FROM T_EnterpriseOperator WHERE EnterpriseOpId = :EnterpriseOpId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseOpId' => $enterpriseOpId,
        );

        return $stm->execute($prm);
    }

    /**
     * 指定のログインIDから加盟店オペレーターIDを取得する
     *
     * @param string $entOpLoginId 加盟店オペレーターログインID
     * @return array
     */
    public function findEnterpriseOpId($entOpLoginId)
    {
        $sql = "SELECT EnterpriseOpId FROM T_EnterpriseOperator WHERE LoginId = :LoginId";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':LoginId' => $entOpLoginId,
        );

        return $row = $stm->execute($prm)->current();
    }

    /**
     * 指定LoginIdが新しいIDか（重複していないか）をチェックする。
     *
     * @param string $id ログインID
     * @return boolean
     */
    public function isNewLoginId($id)
    {
        $sql = " SELECT COUNT(1) AS cnt FROM T_EnterpriseOperator WHERE LoginId = :LoginId";

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
        $sql  = " SELECT COUNT(1) AS cnt FROM T_EnterpriseOperator WHERE LoginId = :LoginId AND LoginPasswd = :LoginPasswd ";

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
        $sql  = " INSERT INTO T_EnterpriseOperator (EnterpriseId, LoginId, LoginPasswd, NameKj, NameKn, Division, ValidFlg, RoleCode, Hashed, LastPasswordChanged, RegistDate, RegistId, UpdateDate, UpdateId) VALUES (";
        $sql .= "   :EnterpriseId ";
        $sql .= " , :LoginId ";
        $sql .= " , :LoginPasswd ";
        $sql .= " , :NameKj ";
        $sql .= " , :NameKn ";
        $sql .= " , :Division ";
        $sql .= " , :ValidFlg ";
        $sql .= " , :RoleCode ";
        $sql .= " , :Hashed ";
        $sql .= " , :LastPasswordChanged ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $data['EnterpriseId'],
                ':LoginId' => $data['LoginId'],
                ':LoginPasswd' => $data['LoginPasswd'],
                ':NameKj' => $data['NameKj'],
                ':NameKn' => $data['NameKn'],
                ':Division' => $data['Division'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
                ':RoleCode' => $data['RoleCode'],
                ':Hashed' => isset($data['Hashed']) ? $data['Hashed'] : 0,
                ':LastPasswordChanged' => $data['LastPasswordChanged'],
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
     * @param int $enterpriseOpId 加盟店オペレーターID
     * @return ResultInterface
     */
    public function saveUpdate($data, $enterpriseOpId)
    {
        $row = $this->find($enterpriseOpId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_EnterpriseOperator ";
        $sql .= " SET ";
        $sql .= "     EnterpriseId = :EnterpriseId ";
        $sql .= " ,   LoginId = :LoginId ";
        $sql .= " ,   LoginPasswd = :LoginPasswd ";
        $sql .= " ,   NameKj = :NameKj ";
        $sql .= " ,   NameKn = :NameKn ";
        $sql .= " ,   Division = :Division ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " ,   RoleCode = :RoleCode ";
        $sql .= " ,   Hashed = :Hashed ";
        $sql .= " ,   LastPasswordChanged = :LastPasswordChanged ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " WHERE EnterpriseOpId = :EnterpriseOpId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseOpId' => $enterpriseOpId,
                ':EnterpriseId' => $row['EnterpriseId'],
                ':LoginId' => $row['LoginId'],
                ':LoginPasswd' => $row['LoginPasswd'],
                ':NameKj' => $row['NameKj'],
                ':NameKn' => $row['NameKn'],
                ':Division' => $row['Division'],
                ':ValidFlg' => $row['ValidFlg'],
                ':RoleCode' => $row['RoleCode'],
                ':Hashed' => $row['Hashed'],
                ':LastPasswordChanged' => $row['LastPasswordChanged'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
        );

        return $stm->execute($prm);
    }
}
