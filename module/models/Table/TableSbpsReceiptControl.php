<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class TableSbpsReceiptControl
{
    protected $_name = 'T_SbpsReceiptControl';
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
     * ユーザーマスターデータを取得する
     *
     * @param int $OrderSeq ユーザーID
     * @return ResultInterface
     */
    public function find($OrderSeq)
    {
        $sql = " SELECT * FROM T_SbpsReceiptControl WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $OrderSeq,
        );

        return $stm->execute($prm);
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $OrderSeq ユーザーID
     * @return ResultInterface
     */
    public function saveUpdate($data, $OrderSeq)
    {
        $row = $this->find($OrderSeq)->current();

        foreach ($data as $key => $value)
        {
            if (is_array($row) && array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_SbpsReceiptControl ";
        $sql .= " SET ";
        $sql .= "     PayType = :PayType ";
        $sql .= " ,   PaymentName = :PaymentName ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $OrderSeq,
                ':PayType' => $row['PayType'],
                ':PaymentName' => $row['PaymentName'],
                ':RegistDate' => $row['RegistDate'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
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
        $sql  = " INSERT INTO T_SbpsReceiptControl (OrderSeq, PayType, PaymentName, ReceiptDate, RegistDate, UpdateDate) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :PayType ";
        $sql .= " , :PaymentName ";
        $sql .= " , :ReceiptDate ";
        $sql .= " , :RegistDate ";
        $sql .= " , :UpdateDate ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq'    => $data['OrderSeq'],
                ':PayType'     => $data['PayType'],
                ':PaymentName' => $data['PaymentName'],
                ':ReceiptDate' => $data['ReceiptDate'],
                ':RegistDate'  => date('Y-m-d H:i:s'),
                ':UpdateDate'  => date('Y-m-d H:i:s'),
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $OrderSeq ユーザーID
     * @return ResultInterface
     */
    public function saveUpdateOrCreate($data, $OrderSeq)
    {
        $row = $this->find($OrderSeq)->current();

        if ($row) {
            foreach ($data as $key => $value)
            {
                if (array_key_exists($key, $row))
                {
                    $row[$key] = $value;
                }
            }
    
            $sql  = " UPDATE T_SbpsReceiptControl ";
            $sql .= " SET ";
            $sql .= "     PayType = :PayType ";
            $sql .= " ,   PaymentName = :PaymentName ";
            $sql .= " ,   RegistDate = :RegistDate ";
            $sql .= " ,   UpdateDate = :UpdateDate ";
            $sql .= " ,   UpdateId = :UpdateId ";
            $sql .= " ,   ValidFlg = :ValidFlg ";
            $sql .= " WHERE OrderSeq = :OrderSeq ";
    
            $stm = $this->_adapter->query($sql);
    
            $prm = array(
                    ':OrderSeq' => $OrderSeq,
                    ':PayType' => $row['PayType'],
                    ':PaymentName' => $row['PaymentName'],
                    ':RegistDate' => $row['RegistDate'],
                    ':UpdateDate' => date('Y-m-d H:i:s'),
                    ':UpdateId' => $row['UpdateId'],
                    ':ValidFlg' => $row['ValidFlg'],
            );
    
            return $stm->execute($prm);
        } else {
            $this->saveNew($data);
        }
    }

}
