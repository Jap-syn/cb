<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_StagnationAlert(停滞アラート)テーブルへのアダプタ
 */
class TableStagnationAlert
{
    protected $_name = 'T_StagnationAlert';
    protected $_primary = array('AlertSeq');
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
     * 停滞アラートデータを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $alertSeq アラートSEQ
     * @return ResultInterface
     */
    public function find($alertSeq)
    {
        $sql = " SELECT * FROM T_StagnationAlert WHERE ValidFlg = 1 AND AlertSeq = :AlertSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AlertSeq' => $alertSeq,
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
        $sql  = " INSERT INTO T_StagnationAlert (AlertClass, AlertSign, OrderSeq, StagnationDays, EnterpriseId, AlertJudgDate, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :AlertClass ";
        $sql .= " , :AlertSign ";
        $sql .= " , :OrderSeq ";
        $sql .= " , :StagnationDays ";
        $sql .= " , :EnterpriseId ";
        $sql .= " , :AlertJudgDate ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AlertClass' => $data['AlertClass'],
                ':AlertSign' => $data['AlertSign'],
                ':OrderSeq' => $data['OrderSeq'],
                ':StagnationDays' => $data['StagnationDays'],
                ':EnterpriseId' => $data['EnterpriseId'],
                ':AlertJudgDate' => $data['AlertJudgDate'],
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
     * @param int $alertSeq アラートSEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $alertSeq)
    {
        $row = $this->find($alertSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_StagnationAlert ";
        $sql .= " SET ";
        $sql .= "     AlertClass = :AlertClass ";
        $sql .= " ,   AlertSign = :AlertSign ";
        $sql .= " ,   OrderSeq = :OrderSeq ";
        $sql .= " ,   StagnationDays = :StagnationDays ";
        $sql .= " ,   EnterpriseId = :EnterpriseId ";
        $sql .= " ,   AlertJudgDate = :AlertJudgDate ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE AlertSeq = :AlertSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AlertSeq' => $alertSeq,
                ':AlertClass' => $row['AlertClass'],
                ':AlertSign' => $row['AlertSign'],
                ':OrderSeq' => $row['OrderSeq'],
                ':StagnationDays' => $row['StagnationDays'],
                ':EnterpriseId' => $row['EnterpriseId'],
                ':AlertJudgDate' => $row['AlertJudgDate'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 指定された条件でレコードを更新する。
     *
     * @param array $data 更新内容
     * @param array $conditionArray
     */
    public function saveUpdateWhere($data, $conditionArray)
    {
        $prm = array();
        $sql  = " SELECT * FROM T_StagnationAlert WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }

        $stm = $this->_adapter->query($sql);

        $ri = $stm->execute($prm);

        foreach ($ri AS $row) {
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $row)) {
                    $row[$key] = $value;
                }
            }

            // 指定されたレコードを更新する
            $this->saveUpdate($row, $row['AlertSeq']);
        }
    }
}
