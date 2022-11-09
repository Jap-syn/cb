<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_PasswordHistory(パスワード履歴)テーブルへのアダプタ
 */
class TablePasswordHistory
{
    protected $_name = 'T_PasswordHistory';
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
     * パスワード履歴データを取得する
     *
     * @param int $Seq シーケンス
     * @return ResultInterface
     */
    public function find($Seq)
    {
        $sql = " SELECT * FROM T_PasswordHistory WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $Seq,
        );

        return $stm->execute($prm);
    }

    /**
     * パスワード履歴データを取得する(インデックス検索)
     *
     * @param int $Category 管理画面カテゴリー
     * @param string $LoginId ログインID
     * @return ResultInterface
     */
    public function findIndex($Category, $LoginId)
    {
        $sql = " SELECT * FROM T_PasswordHistory WHERE Category = :Category AND LoginId = :LoginId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Category' => $Category,
                ':LoginId' => $LoginId,
        );

        return $stm->execute($prm);
    }

    /**
     * 最新の有効なパスワード履歴データを取得する
     *
     * @param int $Category 管理画面カテゴリー
     * @param string $LoginId ログインID
     * @return ResultInterface
     */
    public function findnew($Category, $LoginId)
    {
        $sql = " SELECT * FROM T_PasswordHistory WHERE Category = :Category AND LoginId = :LoginId AND ValidFlg = 1 ORDER BY Seq DESC Limit 1 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Category' => $Category,
                ':LoginId' => $LoginId,
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
        $sql  = " INSERT INTO T_PasswordHistory (Category, LoginId, LoginPasswd, PasswdStartDay, PasswdLimitDay, Hashed, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :Category ";
        $sql .= " , :LoginId ";
        $sql .= " , :LoginPasswd ";
        $sql .= " , :PasswdStartDay ";
        $sql .= " , :PasswdLimitDay ";
        $sql .= " , :Hashed ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Category' => $data['Category'],
                ':LoginId' => $data['LoginId'],
                ':LoginPasswd' => $data['LoginPasswd'],
                ':PasswdStartDay' => $data['PasswdStartDay'],
                ':PasswdLimitDay' => $data['PasswdLimitDay'],
                ':Hashed' => isset($data['Hashed']) ? $data['Hashed'] : 0,
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
     * @param int $Seq シーケンス
     * @return ResultInterface
     */
    public function saveUpdate($data, $Seq)
    {
        $row = $this->find($Seq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_PasswordHistory ";
        $sql .= " SET ";
        $sql .= "     Category = :Category ";
        $sql .= " ,   LoginId = :LoginId ";
        $sql .= " ,   LoginPasswd = :LoginPasswd ";
        $sql .= " ,   PasswdStartDay = :PasswdStartDay ";
        $sql .= " ,   PasswdLimitDay = :PasswdLimitDay ";
        $sql .= " ,   Hashed = :Hashed ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Category' => $row['Category'],
                ':LoginId' => $row['LoginId'],
                ':LoginPasswd' => $row['LoginPasswd'],
                ':PasswdStartDay' => $row['PasswdStartDay'],
                ':PasswdLimitDay' => $row['PasswdLimitDay'],
                ':Hashed' => $row['Hashed'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 有効フラグを無効に更新する。
     *
     * @param int $Category 管理画面カテゴリー
     * @param string $LoginId ログインID
     * @param int $userId ユーザーID
     */
    public function validflgUpdate($Category, $LoginId, $userId)
    {
        $sql  = " UPDATE T_PasswordHistory ";
        $sql .= "    SET UpdateId = :UpdateId ";
        $sql .= "       ,UpdateDate = :UpdateDate ";
        $sql .= "       ,ValidFlg = 0 ";
        $sql .= "  WHERE Seq = :Seq ";
        $stm = $this->_adapter->query($sql);

        $ri = $this->_adapter->query(" SELECT Seq FROM T_PasswordHistory WHERE Category = :Category AND LoginId = :LoginId AND ValidFlg = 1 "
            )->execute(array(':Category' => $Category, ':LoginId' => $LoginId));
        foreach ($ri as $row) {
            $prm = array(
                    ':Seq' => $row['Seq'],
                    ':UpdateId' => $userId,
                    ':UpdateDate' => date('Y-m-d H:i:s'),
            );
            $stm->execute($prm);
        }
    return;
    }
}
