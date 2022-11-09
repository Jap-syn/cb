<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_CjOrderIdControl(与信注文ID管理)テーブルへのアダプタ
 */
class TableCjOrderIdControl
{
    protected $_name = 'T_CjOrderIdControl';
    protected $_primary = array('OrderSeq');
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
     * 与信注文ID管理データを取得する
     *
     * @param int $OrderSeq 注文Seq
     * @return ResultInterface
     */
    public function find($orderSeq)
    {
        $sql = " SELECT * FROM T_CjOrderIdControl WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
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
        $sql  = " INSERT INTO T_CjOrderIdControl (OrderSeq, IluOrderId, RegistDate, RegistId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :IluOrderId ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':IluOrderId' => $data['IluOrderId'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
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
    public function saveUpdate($data, $orderSeq)
    {
        $row = $this->find($orderSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_CjOrderIdControl ";
        $sql .= " SET ";
        $sql .= "     IluOrderId = :IluOrderId ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
                ':IluOrderId' => $row['IluOrderId'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }
}
