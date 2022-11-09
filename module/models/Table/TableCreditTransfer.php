<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_CreditTransfer(口座振替マスタ)テーブルへのアダプタ
 */
class TableCreditTransfer
{
    protected $_name = 'T_CreditTransfer';
    protected $_primary = array ('CreditTransferId');
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
     * 口座振替マスタデータを取得する
     *
     * @param int $creditTransferId 口座振替ID
     * @return ResultInterface
     */
    public function find($creditTransferId)
    {
        $sql = " SELECT * FROM T_CreditTransfer WHERE CreditTransferId = :CreditTransferId AND ValidFlg=1 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CreditTransferId' => $creditTransferId,
        );

        return $stm->execute($prm);
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $creditTransferId 口座振替ID
     * @return ResultInterface
     */
    public function saveUpdate($data, $creditTransferId)
    {
        $row = $this->find($creditTransferId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_CreditTransfer ";
        $sql .= " SET ";
        $sql .= "     CreditTransferSpanFromMonth = :CreditTransferSpanFromMonth ";
        $sql .= " ,   CreditTransferSpanFromDay = :CreditTransferSpanFromDay ";
        $sql .= " ,   CreditTransferSpanToTypeMonth = :CreditTransferSpanToTypeMonth ";
        $sql .= " ,   CreditTransferSpanToDay = :CreditTransferSpanToDay ";
        $sql .= " ,   CreditTransferLimitDayType = :CreditTransferLimitDayType ";
        $sql .= " ,   CreditTransferDay = :CreditTransferDay ";
        $sql .= " ,   CreditTransferAfterLimitDayType = :CreditTransferAfterLimitDayType ";
        $sql .= " ,   CreditTransferAfterLimitDay = :CreditTransferAfterLimitDay ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " WHERE CreditTransferId = :CreditTransferId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CreditTransferId' => $creditTransferId,
                ':CreditTransferSpanFromMonth' => $row['CreditTransferSpanFromMonth'],
                ':CreditTransferSpanFromDay' => $row['CreditTransferSpanFromDay'],
                ':CreditTransferSpanToTypeMonth' => $row['CreditTransferSpanToTypeMonth'],
                ':CreditTransferSpanToDay' => $row['CreditTransferSpanToDay'],
                ':CreditTransferLimitDayType' => $row['CreditTransferLimitDayType'],
                ':CreditTransferDay' => $row['CreditTransferDay'],
                ':CreditTransferAfterLimitDayType' => $row['CreditTransferAfterLimitDayType'],
                ':CreditTransferAfterLimitDay' => $row['CreditTransferAfterLimitDay'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
        );

        return $stm->execute($prm);
    }

    /**
     * すべてのマスターデータを取得する
     *
     * @return ResultInterface
     */
    public function getAll()
    {
        $sql = " SELECT * FROM T_CreditTransfer WHERE ValidFlg=1 ORDER BY CreditTransferId ";
        return $this->_adapter->query($sql)->execute(null);
    }

    public function getSelectData()
    {
        $result = array();
        $sql = " SELECT CreditTransferId,CreditTransferName FROM T_CreditTransfer WHERE ValidFlg=1 ORDER BY CreditTransferId ";
        $datas = $this->_adapter->query($sql)->execute(null);
        $result[-1] = '-----';
        foreach ($datas as $data) {
            $result[$data['CreditTransferId']] = $data['CreditTransferName'];
        }
        return $result;
    }

    /**
     * クラスIDと名称のアレイを取得する。
     *
     * @return array
     */
    public function getTemplatesArray()
    {
        $datas = $this->getAll();

        foreach ($datas as $data)
        {
            if($data['CreditTransferName'] != null) {
                $d[$data['CreditTransferId']] = $data['CreditTransferName'];
            }
        }

        return $d;
    }
}
