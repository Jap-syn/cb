<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_FixedNoteRelateテーブルへのアダプタ
 */
class TableFixedNoteRelate
{
    protected $_name = 'T_FixedNoteRelate';
    protected $_primary = array('HeaderSeq', 'DetailSeq');
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
     * 定型備考関連付けデータを取得する
     *
     * @param int $headerSeq ヘッダー項目シーケンス
     * @param int $detailSeq 明細項目シーケンス
     * @return ResultInterface
     */
    public function find($headerSeq, $detailSeq)
    {
        $sql = " SELECT * FROM T_FixedNoteRelate WHERE HeaderSeq = :HeaderSeq AND DetailSeq = :DetailSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':HeaderSeq' => $headerSeq,
                ':DetailSeq' => $detailSeq,
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
        $sql  = " INSERT INTO T_FixedNoteRelate (HeaderSeq, DetailSeq, ListNumber, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :HeaderSeq ";
        $sql .= " , :DetailSeq ";
        $sql .= " , :ListNumber ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':HeaderSeq' => $data['HeaderSeq'],
                ':DetailSeq' => $data['DetailSeq'],
                ':ListNumber' => $data['ListNumber'],
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
     * @param int $headerSeq ヘッダー項目シーケンス
     * @param int $detailSeq 明細項目シーケンス
     */
    public function saveUpdate($data, $headerSeq, $detailSeq)
    {
        $sql = " SELECT * FROM T_FixedNoteRelate WHERE HeaderSeq = :HeaderSeq AND DetailSeq = :DetailSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':HeaderSeq' => $headerSeq,
                ':DetailSeq' => $detailSeq,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_FixedNoteRelate ";
        $sql .= " SET ";
        $sql .= "     ListNumber = :ListNumber ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE HeaderSeq = :HeaderSeq ";
        $sql .= " AND   DetailSeq = :DetailSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':HeaderSeq' => $headerSeq,
                ':DetailSeq' => $detailSeq,
                ':ListNumber' => $row['ListNumber'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 指定されたヘッダー項目シーケンスの全設定を削除する。
     *
     * @param int $headerSeq ヘッダー項目シーケンス
     */
    public function deleteByHeaderSeq($headerSeq)
    {
        $sql = " DELETE FROM T_FixedNoteRelate WHERE HeaderSeq = :HeaderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':HeaderSeq' => $headerSeq,
        );

        return $stm->execute($prm);
    }
}
