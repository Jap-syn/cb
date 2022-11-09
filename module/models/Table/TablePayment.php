<?php

namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * 支払方法マスタ
 * M_Paymentテーブルへのアダプタ
 */
class TablePayment
{
    protected $_name = 'M_Payment';
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
     * OEMに紐づくすべての支払方法を取得する
     *
     * @param null | string $oemId OEMID
     * @return ResultInterface
     */
    public function fetchAllSubscriberCode($oemId)
    {
        $sql = " SELECT * FROM M_Payment WHERE OemId = :OemId AND ValidFlg=1 ORDER BY SortId ASC ";
        return $this->_adapter->query($sql)->execute(array(':OemId' => $oemId));
    }

    public function fetchAllSubscriberCodeAll($oemId)
    {
        $sql = " SELECT * FROM M_Payment WHERE OemId = :OemId ORDER BY SortId ASC ";
        return $this->_adapter->query($sql)->execute(array(':OemId' => $oemId));
    }

    public function findOemAll($oemId)
    {
        $sql = " SELECT * FROM M_Payment WHERE OemId = :OemId AND ValidFlg=1 ORDER BY PaymentId ASC ";
        return $this->_adapter->query($sql)->execute(array(':OemId' => $oemId));
    }
}
