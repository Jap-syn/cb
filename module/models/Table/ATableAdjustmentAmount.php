<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * AT_AdjustmentAmount(調整額管理_会計)テーブルへのアダプタ
 */
class ATableAdjustmentAmount
{
    protected $_name = 'AT_AdjustmentAmount';
    protected $_primary = array ('PayingControlSeq', 'SerialNumber');
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
     * 調整額管理_会計データを取得する
     *
     * @param int $payingControlSeq 立替振込管理SEQ
     * @param int $serialNumber 連番
     * @return ResultInterface
     */
    public function find($payingControlSeq, $serialNumber)
    {
        $sql = " SELECT * FROM AT_AdjustmentAmount WHERE PayingControlSeq = :PayingControlSeq AND SerialNumber = :SerialNumber ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PayingControlSeq' => $payingControlSeq,
                ':SerialNumber' => $serialNumber,
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
        $sql  = " INSERT INTO AT_AdjustmentAmount (PayingControlSeq, SerialNumber, DailySummaryFlg) VALUES (";
        $sql .= "   :PayingControlSeq ";
        $sql .= " , :SerialNumber ";
        $sql .= " , :DailySummaryFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PayingControlSeq' => $data['PayingControlSeq'],
                ':SerialNumber' => $data['SerialNumber'],
                ':DailySummaryFlg' => isset($data['DailySummaryFlg']) ? $data['DailySummaryFlg'] : 0,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $payingControlSeq 立替振込管理SEQ
     * @param int $serialNumber 連番
     * @return ResultInterface
     */
    public function saveUpdate($data, $payingControlSeq, $serialNumber)
    {
        $row = $this->find($payingControlSeq, $serialNumber)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE AT_AdjustmentAmount ";
        $sql .= " SET ";
        $sql .= "     DailySummaryFlg = :DailySummaryFlg ";
        $sql .= " WHERE PayingControlSeq = :PayingControlSeq ";
        $sql .= " AND   SerialNumber = :SerialNumber ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PayingControlSeq' => $payingControlSeq,
                ':SerialNumber' => $serialNumber,
                ':DailySummaryFlg' => $row['DailySummaryFlg'],
        );

        return $stm->execute($prm);
    }
}
