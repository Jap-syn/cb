<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_FormAuthority(画面権限制御)テーブルへのアダプタ
 */
class TableFormAuthority
{
    protected $_name = 'M_FormAuthority';
    protected $_primary = array ('FormId', 'AuthorityId');
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
     * 画面権限制御データを取得する
     *
     * @param int $formId 画面ID
     * @param int $authorityId 権限ID
     * @return ResultInterface
     */
    public function find($formId, $authorityId)
    {
        $sql = " SELECT * FROM M_FormAuthority WHERE FormId = :FormId AND AuthorityId = :AuthorityId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':FormId' => $formId,
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
        $sql  = " INSERT INTO M_FormAuthority (FormId, AuthorityId, AuthorityClass, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :FormId ";
        $sql .= " , :AuthorityId ";
        $sql .= " , :AuthorityClass ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':FormId' => $data['FormId'],
                ':AuthorityId' => $data['AuthorityId'],
                ':AuthorityClass' => isset($data['AuthorityClass']) ? $data['AuthorityClass'] : 0,
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
     * @param int $formId 画面ID
     * @param int $authorityId 権限ID
     * @return ResultInterface
     */
    public function saveUpdate($data, $formId, $authorityId)
    {
        $row = $this->find($formId, $authorityId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE M_FormAuthority ";
        $sql .= " SET ";
        $sql .= "     AuthorityClass = :AuthorityClass ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE FormId = :FormId ";
        $sql .= " AND   AuthorityId = :AuthorityId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':FormId' => $formId,
                ':AuthorityId' => $authorityId,
                ':AuthorityClass' => $row['AuthorityClass'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }
}
