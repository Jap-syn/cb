<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_MenuAuthority(メニュー権限制御)テーブルへのアダプタ
 */
class TableMenuAuthority
{
    protected $_name = 'T_MenuAuthority';
    protected $_primary = array ('MenuSeq', 'RoleCode');
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
     * メニュー権限制御データを取得する
     *
     * @param int $menuSeq メニューSEQ
     * @param int $roleCode 権限ID
     * @return ResultInterface
     */
    public function find($menuSeq, $roleCode)
    {
        $sql = " SELECT * FROM T_MenuAuthority WHERE MenuSeq = :MenuSeq AND RoleCode = :RoleCode ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':MenuSeq' => $menuSeq,
                ':RoleCode' => $roleCode,
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
        $sql  = " INSERT INTO T_MenuAuthority (MenuSeq, RoleCode, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :MenuSeq ";
        $sql .= " , :RoleCode ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':MenuSeq' => $data['MenuSeq'],
                ':RoleCode' => $data['RoleCode'],
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
     * @param int $menuSeq メニューSEQ
     * @param int $roleCode 権限ID
     * @return ResultInterface
     */
    public function saveUpdate($data, $menuSeq, $roleCode)
    {
        $row = $this->find($menuSeq, $roleCode)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_MenuAuthority ";
        $sql .= " SET ";
        $sql .= "     RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE MenuSeq = :MenuSeq ";
        $sql .= " AND   RoleCode = :RoleCode ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':MenuSeq' => $menuSeq,
                ':RoleCode' => $roleCode,
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }
}
