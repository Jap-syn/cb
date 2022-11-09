<?php

namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_PaymentCheckテーブルへのアダプタ
 */
class TablePaymentCheck
{
    protected $_name = 'M_PaymentCheck';
    protected $_primary = array('PaymentCheckSeq');
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

    public function find($printPatternCd)
    {
        $sql = " SELECT * FROM M_PaymentCheck WHERE PrintPatternCd = :PrintPatternCd AND ValidFlg = 1 ORDER BY PaymentCheckSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':PrintPatternCd' => $printPatternCd,
        );

        return $stm->execute($prm);
    }

    public function primary($paymentCheckSeq)
    {
        $sql = " SELECT * FROM M_PaymentCheck WHERE PaymentCheckSeq = :PaymentCheckSeq AND ValidFlg = 1 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':PaymentCheckSeq' => $paymentCheckSeq,
        );

        return $stm->execute($prm);
    }
}
