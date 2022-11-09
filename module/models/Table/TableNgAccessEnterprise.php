<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_NgAccessEnterprise(不正アクセス加盟店)テーブルへのアダプタ
 */
class TableNgAccessEnterprise {

    protected $_name = 'T_NgAccessEnterprise';
    protected $_primary = array('EnterpriseId');
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
     * 不正アクセス加盟店データを取得する
     *
     * @param int $enterpriseId 加盟店ID
     * @return ResultInterface
     */
    public function find($enterpriseId)
    {
        $sql  = " SELECT * FROM T_NgAccessEnterprise WHERE EnterpriseId = :EnterpriseId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
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
        $sql  = " INSERT INTO T_NgAccessEnterprise (EnterpriseId, NgAccessCount, NgAccessReferenceDate, UpdateDate) VALUES (";
        $sql .= "   :EnterpriseId ";
        $sql .= " , :NgAccessCount ";
        $sql .= " , :NgAccessReferenceDate ";
        $sql .= " , :UpdateDate ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $data['EnterpriseId'],
                ':NgAccessCount' => isset($data['NgAccessCount']) ? $data['NgAccessCount'] : 0,
                ':NgAccessReferenceDate' => $data['NgAccessReferenceDate'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $enterpriseId 加盟店ID
     * @return ResultInterface
     */
    public function saveUpdate($data, $enterpriseId)
    {
        $row = $this->find($enterpriseId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_NgAccessEnterprise ";
        $sql .= " SET ";
        $sql .= "     EnterpriseId = :EnterpriseId ";
        $sql .= " ,   NgAccessCount = :NgAccessCount ";
        $sql .= " ,   NgAccessReferenceDate = :NgAccessReferenceDate ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " WHERE EnterpriseId = :EnterpriseId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
                ':NgAccessCount' => isset($row['NgAccessCount']) ? $row['NgAccessCount'] : 0,
                ':NgAccessReferenceDate' => $row['NgAccessReferenceDate'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
        );

        return $stm->execute($prm);
    }
}
