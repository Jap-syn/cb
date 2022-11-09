<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_EnterpriseReceiptHistory(加盟店入金履歴)テーブルへのアダプタ
 */
class TableEnterpriseReceiptHistory
{
    protected $_name = 'T_EnterpriseReceiptHistory';
    protected $_primary = array('EntRcptSeq');
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
     * 加盟店入金履歴データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $entRcptSeq 加盟店入金SEQ
     * @return ResultInterface
     */
    public function find($entRcptSeq)
    {
        $sql = " SELECT * FROM T_EnterpriseReceiptHistory WHERE ValidFlg = 1 AND EntRcptSeq = :EntRcptSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EntRcptSeq' => $entRcptSeq,
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
        $sql  = " INSERT INTO T_EnterpriseReceiptHistory (EnterpriseId, ReceiptDate, ReceiptProcessDate, ReceiptAmount, ReceiptClass, Note, RegistDate, RegistId, UpdateDate, UpdateId ,ValidFlg) VALUES (";
        $sql .= "   :EnterpriseId ";
        $sql .= " , :ReceiptDate ";
        $sql .= " , :ReceiptProcessDate ";
        $sql .= " , :ReceiptAmount ";
        $sql .= " , :ReceiptClass ";
        $sql .= " , :Note ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $data['EnterpriseId'],
                ':ReceiptDate' => $data['ReceiptDate'],
                ':ReceiptProcessDate' => $data['ReceiptProcessDate'],
                ':ReceiptAmount' => $data['ReceiptAmount'],
                ':ReceiptClass' => $data['ReceiptClass'],
                ':Note' => $data['Note'],
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
     * @param int $entRcptSeq 加盟店入金SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $entRcptSeq)
    {
        $row = $this->find($entRcptSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_EnterpriseReceiptHistory ";
        $sql .= " SET ";
        $sql .= "     EnterpriseId = :EnterpriseId ";
        $sql .= " ,   ReceiptDate = :ReceiptDate ";
        $sql .= " ,   ReceiptProcessDate = :ReceiptProcessDate ";
        $sql .= " ,   ReceiptAmount = :ReceiptAmount ";
        $sql .= " ,   ReceiptClass = :ReceiptClass ";
        $sql .= " ,   Note = :Note ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE EntRcptSeq = :EntRcptSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EntRcptSeq' => $entRcptSeq,
                ':EnterpriseId' => $row['EnterpriseId'],
                ':ReceiptDate' => $row['ReceiptDate'],
                ':ReceiptProcessDate' => $row['ReceiptProcessDate'],
                ':ReceiptAmount' => $row['ReceiptAmount'],
                ':ReceiptClass' => $row['ReceiptClass'],
                ':Note' => $row['Note'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }
}
