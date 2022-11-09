<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_CreditPayment(コードマスター)テーブルへのアダプタ
 */
class TableCreditPayment
{
    protected $_name = 'T_CreditPayment';
    protected $_primary = 'OrderSeq';
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
     * コードマスターデータを取得する
     *
     * @param int $orderSeq
     * @return ResultInterface
     */
    public function find($orderSeq)
    {
        $sql = " SELECT * FROM T_CreditPayment WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $orderSeq,
        );

        return $stm->execute($prm);
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param int $orderSeq
     * @param array $data 更新内容
     * @return ResultInterface
     */
    public function saveUpdate($orderSeq, $data)
    {
        $row = $this->find($orderSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_CreditPayment ";
        $sql .= " SET ";
        $sql .= "     PaymentType = :PaymentType ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq'    => $orderSeq,
                ':PaymentType' => $row['PaymentType'],
                ':RegistDate'  => date('Y-m-d H:i:s'),
                ':UpdateDate'  => date('Y-m-d H:i:s'),
        );

        return $stm->execute($prm);
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param int $orderSeq
     * @param array $data 更新内容
     * @return ResultInterface
     */
    public function saveUpdateOrCreate($orderSeq, $data)
    {
        $row = $this->find($orderSeq)->current();

        if ($row) {
            foreach ($data as $key => $value)
            {
                if (array_key_exists($key, $row))
                {
                    $row[$key] = $value;
                }
            }
    
            $sql  = " UPDATE T_CreditPayment ";
            $sql .= " SET ";
            $sql .= "     PaymentType = :PaymentType ";
            $sql .= " ,   RegistDate = :RegistDate ";
            $sql .= " ,   UpdateDate = :UpdateDate ";
            $sql .= " WHERE OrderSeq = :OrderSeq ";
    
            $stm = $this->_adapter->query($sql);
    
            $prm = array(
                    ':OrderSeq'    => $orderSeq,
                    ':PaymentType' => $row['PaymentType'],
                    ':RegistDate'  => date('Y-m-d H:i:s'),
                    ':UpdateDate'  => date('Y-m-d H:i:s'),
            );
    
            return $stm->execute($prm);
        } else {
            $this->saveNew($data, $orderSeq);
        }
    }

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @param int $orderSeq
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data, $orderSeq)
	{
        $sql  = " INSERT INTO T_CreditPayment (OrderSeq, PaymentType, RegistDate, UpdateDate) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :PaymentType ";
        $sql .= " , :RegistDate ";
        $sql .= " , :UpdateDate ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq'    => $orderSeq,
                ':PaymentType' => $data['PaymentType'],
                ':RegistDate'  => date('Y-m-d H:i:s'),
                ':UpdateDate'  => date('Y-m-d H:i:s'),
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

}
