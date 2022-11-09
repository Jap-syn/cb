<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_CreditLock(与信排他制御)テーブルへのアダプタ
 */
class TableCreditLock
{
    protected $_name = 'T_CreditLock';
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
     * 与信排他制御データを取得する
     *
     * @param int $orderSeq 注文SEQ
     * @param int $opId オペレーターＩＤ
     * @return ResultInterface
     */
    public function find($orderSeq, $opId)
    {
        $sql = " SELECT * FROM T_CreditLock WHERE OrderSeq = :OrderSeq AND OpId = :OpId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
                ':OpId' => $opId,
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
        $sql  = " INSERT INTO T_CreditLock (OrderSeq, OpId) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :OpId ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':OpId' => $data['OpId'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();
    }

    /**
     * 指定されたレコードを削除する。
     *
     * @param int $orderSeq 注文SEQ
     * @param int $opId オペレーターＩＤ
     * @return ResultInterface
     */
    public function delete($orderSeq, $opId)
    {
        $sql  = " DELETE FROM T_CreditLock WHERE OrderSeq = :OrderSeq AND OpId = :OpId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
                ':OpId' => $opId,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();
    }
}
