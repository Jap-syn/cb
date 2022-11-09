<?php

namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_SbpsPaymentテーブルへのアダプタ
 */
class TableSbpsPayment
{
    protected $_name = 'M_SbpsPayment';
    protected $_primary = array('SbpsPaymentId');
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
     * Get list payment methods
     */
    public function getList($oemId = 0, $validFlg = 1)
    {
        $sql = " SELECT * FROM " . $this->_name . " WHERE OemId = :OemId AND ValidFlg = :ValidFlg ORDER BY SortId ";
        $stm = $this->_adapter->query($sql);
        $prm = array(
            ':OemId' => $oemId,
            ':ValidFlg' => $validFlg,
        );
        return $stm->execute($prm);
    }

    public function findOemAll($oemId)
    {
        $sql = " SELECT * FROM " . $this->_name . " WHERE OemId = :OemId AND ValidFlg=1 ORDER BY SortId ASC ";
        return $this->_adapter->query($sql)->execute(array(':OemId' => $oemId));
    }
}
