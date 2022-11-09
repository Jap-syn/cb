<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_JtcResult_Detailテーブルへのアダプタ
 */
class TableJtcResultDetail
{
    protected $_name = 'T_JtcResult_Detail';
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
     * ジンテック結果データを取得する
     *
     * @param int $seq SEQ
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM T_JtcResult_Detail WHERE Seq = :Seq ";

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
        $sql  = " INSERT INTO T_JtcResult_Detail (JtcSeq, OrderSeq, ClassId, ItemId, Value) VALUES (";
        $sql .= "   :JtcSeq ";
        $sql .= " , :OrderSeq ";
        $sql .= " , :ClassId ";
        $sql .= " , :ItemId ";
        $sql .= " , :Value ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':JtcSeq' => $data['JtcSeq'],
            ':OrderSeq' => $data['OrderSeq'],
            ':ClassId' => $data['ClassId'],
            ':ItemId' => $data['ItemId'],
            ':Value' => $data['Value'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $seq SEQ
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

        $sql  = " UPDATE T_JtcResult_Detail ";
        $sql .= " SET ";
        $sql .= "     JtcSeq = :JtcSeq ";
        $sql .= " ,   OrderSeq = :OrderSeq ";
        $sql .= " ,   ClassId = :ClassId ";
        $sql .= " ,   ItemId = :ItemId ";
        $sql .= " ,   Value = :Value ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':JtcSeq' => $row['JtcSeq'],
            ':OrderSeq' => $row['OrderSeq'],
            ':ClassId' => $row['ClassId'],
            ':ItemId' => $row['ItemId'],
            ':Value' => $row['Value'],
            ':Seq' => $row['Seq'],
        );

        return $stm->execute($prm);
    }

    /**
     * ジンテック結果SEQをもとに、ジンテック結果データを取得する
     *
     * @param int $jtcSeq ジンテック結果SEQ
     * @return ResultInterface
     */
    public function findByJtcSeq($jtcSeq)
    {
        $sql = " SELECT * FROM T_JtcResult_Detail WHERE JtcSeq = :JtcSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':JtcSeq' => $jtcSeq,
        );

        return $stm->execute($prm);
    }
}
