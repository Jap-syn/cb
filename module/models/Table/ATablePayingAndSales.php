<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * AT_PayingAndSales(立替・売上管理_会計)テーブルへのアダプタ
 */
class ATablePayingAndSales
{
    protected $_name = 'AT_PayingAndSales';
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
     * 立替・売上管理_会計データを取得する
     *
     * @param int $seq 管理SEQ
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM AT_PayingAndSales WHERE Seq = :Seq ";

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
        $sql  = " INSERT INTO AT_PayingAndSales (Seq, DailySummaryFlg, Deli_ConfirmArrivalInputDate, ATUriType, ATUriDay) VALUES (";
        $sql .= "   :Seq ";
        $sql .= " , :DailySummaryFlg ";
        $sql .= " , :Deli_ConfirmArrivalInputDate ";
        $sql .= " , :ATUriType ";
        $sql .= " , :ATUriDay ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $data['Seq'],
                ':DailySummaryFlg' => isset($data['DailySummaryFlg']) ? $data['DailySummaryFlg'] : 0,
                ':Deli_ConfirmArrivalInputDate' => $data['Deli_ConfirmArrivalInputDate'],
                ':ATUriType' => isset($data['ATUriType']) ? $data['ATUriType'] : 99,
                ':ATUriDay' => isset($data['ATUriDay']) ? $data['ATUriDay'] : '999999999',
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $seq 管理SEQ
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

        $sql  = " UPDATE AT_PayingAndSales ";
        $sql .= " SET ";
        $sql .= "     DailySummaryFlg = :DailySummaryFlg ";
        $sql .= " ,   Deli_ConfirmArrivalInputDate = :Deli_ConfirmArrivalInputDate ";
        $sql .= " ,   ATUriType = :ATUriType ";
        $sql .= " ,   ATUriDay = :ATUriDay ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':DailySummaryFlg' => $row['DailySummaryFlg'],
                ':Deli_ConfirmArrivalInputDate' => $row['Deli_ConfirmArrivalInputDate'],
                ':ATUriType' => $row['ATUriType'],
                ':ATUriDay' => $row['ATUriDay'],
        );

        return $stm->execute($prm);
    }
}
