<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_User(ユーザーマスター)テーブルへのアダプタ
 */
class TableUser
{

    const USERCLASS_SYSTEM = 99;

    const SEQ_BATCH_USER = 1;

    protected $_name = 'T_User';
    protected $_primary = array('UserId');
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
     * ユーザーマスターデータを取得する
     *
     * @param int $userId ユーザーID
     * @return ResultInterface
     */
    public function find($userId)
    {
        $sql = " SELECT * FROM T_User WHERE UserId = :UserId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':UserId' => $userId,
        );

        return $stm->execute($prm);
    }

    /**
     * 新しいレコードをインサートする。
     *
     * @param array $data インサートする連想配列
     * @return プライマリキーのバリュー
     */
    public function saveNew($data)
    {
        $sql  = " INSERT INTO T_User (UserClass, Seq, LastLoginDate, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :UserClass ";
        $sql .= " , :Seq ";
        $sql .= " , :LastLoginDate ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':UserClass' => $data['UserClass'],
                ':Seq' => $data['Seq'],
                ':LastLoginDate' => $data['LastLoginDate'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $userId ユーザーID
     * @return ResultInterface
     */
    public function saveUpdate($data, $userId)
    {
        $row = $this->find($userId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_User ";
        $sql .= " SET ";
        $sql .= "     UserClass = :UserClass ";
        $sql .= " ,   Seq = :Seq ";
        $sql .= " ,   LastLoginDate = :LastLoginDate ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE UserId = :UserId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':UserId' => $userId,
                ':UserClass' => $row['UserClass'],
                ':Seq' => $row['Seq'],
                ':LastLoginDate' => $row['LastLoginDate'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 区分とシーケンスより、ユーザーIDを取得する
     *
     * @param int $userClass 区分(0：CBオペレーター,1：OEMオペレーター,2：加盟店)
     * @param int $seq シーケンス(区分が0の場合：T_Operator.OpId,区分が1の場合：T_OemOperator.OemOpId,区分が2の場合：T_Enterprise.EnterpriseId)
     * @return int ユーザーID 該当無し時は-1を戻す
     */
    public function getUserId($userClass, $seq)
    {
        $sql = " SELECT UserId FROM T_User WHERE UserClass = :UserClass AND Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':UserClass' => $userClass,
                ':Seq' => $seq,
        );

        $row = $stm->execute($prm)->current();

        return ($row) ? (int)$row['UserId'] : -1;
    }

    /**
     * ユーザーIDをもとにユーザー名を取得する
     * @param string $userId ユーザーID
     * @return Ambigous <NULL, string>
     */
    public function getUserName($userId)
    {
        $sql = ' SELECT F_GetLoginUserName(:UserId) AS UserName ';

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':UserId' => $userId,
        );

        $row = $stm->execute($prm)->current();

        return ($row) ? $row['UserName'] : null;

    }
}
