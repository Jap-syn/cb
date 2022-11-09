<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_OemAdjustmentAmount(OEM調整額管理)テーブルへのアダプタ
 */
class TableOemAdjustmentAmount
{
    protected $_name = 'T_OemAdjustmentAmount';
    protected $_primary = array ('OemClaimedSeq', 'SerialNumber');
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
     * OEM調整額管理データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $oemClaimedSeq OEM請求データシーケンス
     * @param int $serialNumber 連番
     * @return ResultInterface
     */
    public function find($oemClaimedSeq, $serialNumber)
    {
        $sql = " SELECT * FROM T_OemAdjustmentAmount WHERE ValidFlg = 1 AND OemClaimedSeq = :OemClaimedSeq AND SerialNumber = :SerialNumber ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemClaimedSeq' => $oemClaimedSeq,
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
        $sql  = " INSERT INTO T_OemAdjustmentAmount (OemClaimedSeq, SerialNumber, OrderId, OrderSeq, ItemCode, AdjustmentAmount, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OemClaimedSeq ";
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
                ':OemClaimedSeq' => $data['OemClaimedSeq'],
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
     * @param int $oemClaimedSeq OEM請求データシーケンス
     * @param int $serialNumber 連番
     * @return ResultInterface
     */
    public function saveUpdate($data, $oemClaimedSeq, $serialNumber)
    {
        $row = $this->find($oemClaimedSeq, $serialNumber)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_OemAdjustmentAmount ";
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
        $sql .= " WHERE OemClaimedSeq = :OemClaimedSeq ";
        $sql .= " AND   SerialNumber = :SerialNumber ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemClaimedSeq' => $oemClaimedSeq,
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
     * OEM調整額管理データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $oemClaimedSeq OEM請求データシーケンス
     * @return ResultInterface
     */
    public function findBySeq($oemClaimedSeq)
    {
        $sql = " SELECT * FROM T_OemAdjustmentAmount WHERE ValidFlg = 1 AND OemClaimedSeq = :OemClaimedSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemClaimedSeq' => $oemClaimedSeq
        );

        return $stm->execute($prm);
    }

    /**
     * 指定されているレコードを削除する
     *
     * @param int $oemClaimedSeq OEM請求データシーケンス
     */
    public function deleteBySeq($seq)
    {
        $sql = " DELETE FROM T_OemAdjustmentAmount WHERE OemClaimedSeq = :Seq";
        $ri = $this->_adapter->query($sql)->execute(array(':Seq' => $seq));
    }

}
