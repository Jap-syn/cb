<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_SmbcRelationControl(決済ステーション管理)テーブルへのアダプタ
 */
class TableSmbcRelationControl
{
    protected $_name = 'T_SmbcRelationControl';
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
     * 決済ステーション管理を取得する
     *
     * @param int $seq シーケンス
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM T_SmbcRelationControl WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
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
        $sql  = " INSERT INTO T_SmbcRelationControl (ClaimHistorySeq, OrderSeq, OrderCnt) VALUES (";
        $sql .= "   :ClaimHistorySeq ";
        $sql .= " , :OrderSeq ";
        $sql .= " , :OrderCnt ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ClaimHistorySeq' => $data['ClaimHistorySeq'],
                ':OrderSeq' => $data['OrderSeq'],
                ':OrderCnt' => isset($data['OrderCnt']) ? $data['OrderCnt'] : 0,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $siteId サイトID
     * @return ResultInterface
     */
    public function saveUpdate($data, $seq)
    {
        $row = $this->find($seq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_SmbcRelationControl ";
        $sql .= " SET ";
        $sql .= "     ClaimHistorySeq = :ClaimHistorySeq ";
        $sql .= " ,   OrderSeq = :OrderSeq ";
        $sql .= " ,   OrderCnt = :OrderCnt ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':ClaimHistorySeq' => $row['ClaimHistorySeq'],
                ':OrderSeq' => $row['OrderSeq'],
                ':OrderCnt' => $row['OrderCnt'],
        );

        return $stm->execute($prm);
    }
}
