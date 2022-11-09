<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_CombinedDictateDetail(取りまとめ指示明細)テーブルへのアダプタ
 */
class TableCombinedDictateDetail
{
    protected $_name = 'T_CombinedDictateDetail';
    protected $_primary = array ('CombinedDictateSeq', 'OrderSeq');
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
     * 取りまとめ指示明細データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $combinedDictateSeq 取りまとめ指示SEQ
     * @param int $orderSeq 注文SEQ
     * @return ResultInterface
     */
    public function find($combinedDictateSeq, $orderSeq)
    {
        $sql = " SELECT * FROM T_CombinedDictateDetail WHERE ValidFlg = 1 AND CombinedDictateSeq = :CombinedDictateSeq AND OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CombinedDictateSeq' => $combinedDictateSeq,
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
        $sql  = " INSERT INTO T_CombinedDictateDetail (CombinedDictateSeq, OrderSeq, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :CombinedDictateSeq ";
        $sql .= " , :OrderSeq ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CombinedDictateSeq' => $data['CombinedDictateSeq'],
                ':OrderSeq' => $data['OrderSeq'],
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
     * @param int $combinedDictateSeq 取りまとめ指示SEQ
     * @param int $orderSeq 注文SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $combinedDictateSeq, $orderSeq)
    {
        $row = $this->find($combinedDictateSeq, $orderSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_CombinedDictateDetail ";
        $sql .= " SET ";
        $sql .= "     RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE CombinedDictateSeq = :CombinedDictateSeq ";
        $sql .= " AND   OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CombinedDictateSeq' => $combinedDictateSeq,
                ':OrderSeq' => $orderSeq,
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }
}
