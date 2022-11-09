<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_EnterpriseDelivMethod(加盟店別配送方法マスター)テーブルへのアダプタ
 */
class TableEnterpriseDelivMethod
{
    protected $_name = 'T_EnterpriseDelivMethod';
    protected $_primary = array ('EnterpriseId', 'DeliMethodId');
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
     * 加盟店別配送方法マスターデータを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $enterpriseId 加盟店ID
     * @return ResultInterface
     */
    public function find($enterpriseId)
    {
        $sql = " SELECT * FROM T_EnterpriseDelivMethod WHERE ValidFlg = 1 AND EnterpriseId = :EnterpriseId ORDER BY ListNumber ";

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
        $sql  = " INSERT INTO T_EnterpriseDelivMethod (EnterpriseId, DeliMethodId, ListNumber, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :EnterpriseId ";
        $sql .= " , :DeliMethodId ";
        $sql .= " , :ListNumber ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $data['EnterpriseId'],
                ':DeliMethodId' => $data['DeliMethodId'],
                ':ListNumber' => $data['ListNumber'],
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
     * @param int $enterpriseId 加盟店ID
     * @param int $deliMethodId 配送方法ID
     * @return ResultInterface
     */
    public function saveUpdate($data, $enterpriseId, $deliMethodId)
    {
        $row = $this->find($enterpriseId, $deliMethodId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_EnterpriseDelivMethod ";
        $sql .= " SET ";
        $sql .= "     ListNumber = :ListNumber ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE EnterpriseId = :EnterpriseId ";
        $sql .= " AND   DeliMethodId = :DeliMethodId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
                ':DeliMethodId' => $deliMethodId,
                ':ListNumber' => $row['ListNumber'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 指定された加盟店IDのすべての加盟店別配送方法設定を削除する。
     *
     * @param int $enterpriseId 加盟店ID
     */
    public function deleteByEnterpriseId($enterpriseId)
    {
        $sql = " DELETE FROM T_EnterpriseDelivMethod WHERE EnterpriseId = :EnterpriseId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
        );

        return $stm->execute($prm);
    }
}
