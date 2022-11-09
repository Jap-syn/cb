<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_AdjustmentAmount(調整額管理)テーブルへのアダプタ
 */
class TableAdjustmentAmount
{
    protected $_name = 'T_AdjustmentAmount';
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
     * 調整額管理データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $payingControlSeq 立替振込管理SEQ
     * @param int $serialNumber 連番
     * @return ResultInterface
     */
    public function find($payingControlSeq, $serialNumber)
    {
        $sql = " SELECT * FROM T_AdjustmentAmount WHERE ValidFlg = 1 AND PayingControlSeq = :PayingControlSeq AND SerialNumber = :SerialNumber ";

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
        $sql  = " INSERT INTO T_AdjustmentAmount (PayingControlSeq, SerialNumber, OrderId, OrderSeq, ItemCode, AdjustmentAmount, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :PayingControlSeq ";
        $sql .= " , :SerialNumber ";
        $sql .= " , :OrderId ";
        $sql .= " , :OrderSeq ";
        $sql .= " , :ItemCode ";
        $sql .= " , :AdjustmentAmount ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PayingControlSeq' => $data['PayingControlSeq'],
                ':SerialNumber' => $data['SerialNumber'],
                ':OrderId' => $data['OrderId'],
                ':OrderSeq' => $data['OrderSeq'],
                ':ItemCode' => $data['ItemCode'],
                ':AdjustmentAmount' => $data['AdjustmentAmount'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
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

        $sql  = " UPDATE T_AdjustmentAmount ";
        $sql .= " SET ";
        $sql .= "     OrderId = :OrderId ";
        $sql .= " ,   OrderSeq = :OrderSeq ";
        $sql .= " ,   ItemCode = :ItemCode ";
        $sql .= " ,   AdjustmentAmount = :AdjustmentAmount ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE PayingControlSeq = :PayingControlSeq ";
        $sql .= " AND   SerialNumber = :SerialNumber ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PayingControlSeq' => $payingControlSeq,
                ':SerialNumber' => $serialNumber,
                ':OrderId' => $row['OrderId'],
                ':OrderSeq' => $row['OrderSeq'],
                ':ItemCode' => $row['ItemCode'],
                ':AdjustmentAmount' => $row['AdjustmentAmount'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 指定条件（AND）の調整額管理データを取得する。
     *
     * @param array $conditionArray 検索条件を格納した連想配列
     * @param boolean $isAsc プライマリキーのオーダー
     * @return ResultInterface
     */
    public function findAdjustmentAmount($conditionArray, $isAsc = false)
    {
        $prm = array();
        $sql  = " SELECT * FROM T_AdjustmentAmount WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }
        $sql .= " ORDER BY PayingControlSeq " . ($isAsc ? "asc" : "desc");

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
    }

    /**
     * 指定条件（AND）の調整額管理データを削除する。
     *
     * @param array $conditionArray 削除条件を格納した連想配列
     * @return ResultInterface
     */
    public function deleteAdjustmentAmount($conditionArray)
    {
        $prm = array();
        $sql  = " DELETE FROM T_AdjustmentAmount WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
    }
}
