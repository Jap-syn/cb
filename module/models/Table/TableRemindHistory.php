<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_RemindHistory(督促履歴)テーブルへのアダプタ
 */
class TableRemindHistory
{
    protected $_name = 'T_RemindHistory';
    protected $_primary = array('RemindSeq');
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
     * 督促履歴データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $remindSeq 督促SEQ
     * @return ResultInterface
     */
    public function find($remindSeq)
    {
        $sql = " SELECT * FROM T_RemindHistory WHERE ValidFlg = 1 AND RemindSeq = :RemindSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':RemindSeq' => $remindSeq,
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
        $sql  = " INSERT INTO T_RemindHistory (OrderSeq, OutputDate, InputDate, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :OutputDate ";
        $sql .= " , :InputDate ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':OutputDate' => $data['OutputDate'],
                ':InputDate' => $data['InputDate'],
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
     * @param int $remindSeq 督促SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $remindSeq)
    {
        $row = $this->find($remindSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_RemindHistory ";
        $sql .= " SET ";
        $sql .= "     OrderSeq = :OrderSeq ";
        $sql .= " ,   OutputDate = :OutputDate ";
        $sql .= " ,   InputDate = :InputDate ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE RemindSeq = :RemindSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':RemindSeq' => $remindSeq,
                ':OrderSeq' => $row['OrderSeq'],
                ':OutputDate' => $row['OutputDate'],
                ':InputDate' => $row['InputDate'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }
}
