<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_Authority(権限マスター)テーブルへのアダプタ
 */
class TableAuthority
{
    protected $_name = 'M_Authority';
    protected $_primary = array('AuthorityId');
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
     * 権限マスターデータを取得する
     *
     * @param int $authorityId 権限ID
     * @return ResultInterface
     */
    public function find($authorityId)
    {
        $sql = " SELECT * FROM M_Authority WHERE AuthorityId = :AuthorityId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AuthorityId' => $authorityId,
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
        $sql  = " INSERT INTO M_Authority (AuthorityId, AuthorityName, AuthorityClass, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :AuthorityId ";
        $sql .= " , :AuthorityName ";
        $sql .= " , :AuthorityClass ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AuthorityId' => $data['AuthorityId'],
                ':AuthorityName' => $data['AuthorityName'],
                ':AuthorityClass' => $data['AuthorityClass'],
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
     * @param int $authorityId 権限ID
     * @return ResultInterface
     */
    public function saveUpdate($data, $authorityId)
    {
        $row = $this->find($authorityId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE M_Authority ";
        $sql .= " SET ";
        $sql .= "     AuthorityName = :AuthorityName ";
        $sql .= " ,   AuthorityClass = :AuthorityClass ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE AuthorityId = :AuthorityId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AuthorityId' => $authorityId,
                ':AuthorityName' => $row['AuthorityName'],
                ':AuthorityClass' => $row['AuthorityClass'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /*
     * 権限マスターデータを全取得する
     *
     * @return ResultInterface
     */
    public function findAll()
    {
        $sql = " SELECT * FROM M_Authority ";

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
    }
}
