<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_PayingBackControl(立替精算戻し管理)テーブルへのアダプタ
 */
class TablePayingBackControl
{
    protected $_name = 'T_PayingBackControl';
    protected $_primary = array('PayingBackSeq');
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
     * 立替精算戻し管理データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $payingBackSeq 立替精算戻しSEQ
     * @return ResultInterface
     */
    public function find($payingBackSeq)
    {
        $sql = " SELECT * FROM T_PayingBackControl WHERE ValidFlg = 1 AND PayingBackSeq = :PayingBackSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PayingBackSeq' => $payingBackSeq,
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
        $sql  = " INSERT INTO T_PayingBackControl (OrderSeq, PayBackAmount, PayBackIndicationDate, PayDecisionFlg, PayDecisionDate, EnterpriseId, PayingControlSeq, PayingControlStatus, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :PayBackAmount ";
        $sql .= " , :PayBackIndicationDate ";
        $sql .= " , :PayDecisionFlg ";
        $sql .= " , :PayDecisionDate ";
        $sql .= " , :EnterpriseId ";
        $sql .= " , :PayingControlSeq ";
        $sql .= " , :PayingControlStatus ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':PayBackAmount' => $data['PayBackAmount'],
                ':PayBackIndicationDate' => $data['PayBackIndicationDate'],
                ':PayDecisionFlg' => isset($data['PayDecisionFlg']) ? $data['PayDecisionFlg'] : 0,
                ':PayDecisionDate' => $data['PayDecisionDate'],
                ':EnterpriseId' => $data['EnterpriseId'],
                ':PayingControlSeq' => $data['PayingControlSeq'],
                ':PayingControlStatus' => isset($data['PayingControlStatus']) ? $data['PayingControlStatus'] : 0,
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
     * @param int $payingBackSeq 立替精算戻しSEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $payingBackSeq)
    {
        $row = $this->find($payingBackSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_PayingBackControl ";
        $sql .= " SET ";
        $sql .= "     OrderSeq = :OrderSeq ";
        $sql .= " ,   PayBackAmount = :PayBackAmount ";
        $sql .= " ,   PayBackIndicationDate = :PayBackIndicationDate ";
        $sql .= " ,   PayDecisionFlg = :PayDecisionFlg ";
        $sql .= " ,   PayDecisionDate = :PayDecisionDate ";
        $sql .= " ,   EnterpriseId = :EnterpriseId ";
        $sql .= " ,   PayingControlSeq = :PayingControlSeq ";
        $sql .= " ,   PayingControlStatus = :PayingControlStatus ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE PayingBackSeq = :PayingBackSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PayingBackSeq' => $payingBackSeq,
                ':OrderSeq' => $row['OrderSeq'],
                ':PayBackAmount' => $row['PayBackAmount'],
                ':PayBackIndicationDate' => $row['PayBackIndicationDate'],
                ':PayDecisionFlg' => $row['PayDecisionFlg'],
                ':PayDecisionDate' => $row['PayDecisionDate'],
                ':EnterpriseId' => $row['EnterpriseId'],
                ':PayingControlSeq' => $row['PayingControlSeq'],
                ':PayingControlStatus' => $row['PayingControlStatus'],
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
        $sql  = " SELECT * FROM T_PayingBackControl WHERE 1 = 1 ";
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
            $this->saveUpdate($row, $row['PayingBackSeq']);
        }
    }

    /**
     * 立替精算戻し管理データを取得する。(削除含む)
     *
     * @param int $payingBackSeq 立替精算戻しSEQ
     * @return ResultInterface
     */
    public function find2($payingBackSeq)
    {
        $sql = " SELECT * FROM T_PayingBackControl WHERE PayingBackSeq = :PayingBackSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PayingBackSeq' => $payingBackSeq,
        );

        return $stm->execute($prm);
    }

    /**
     * 指定されたレコードを更新する。(削除も更新可能)
     *
     * @param array $data 更新内容
     * @param int $payingBackSeq 立替精算戻しSEQ
     * @return ResultInterface
     */
    public function saveUpdate2($data, $payingBackSeq)
    {
        $row = $this->find2($payingBackSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_PayingBackControl ";
        $sql .= " SET ";
        $sql .= "     OrderSeq = :OrderSeq ";
        $sql .= " ,   PayBackAmount = :PayBackAmount ";
        $sql .= " ,   PayBackIndicationDate = :PayBackIndicationDate ";
        $sql .= " ,   PayDecisionFlg = :PayDecisionFlg ";
        $sql .= " ,   PayDecisionDate = :PayDecisionDate ";
        $sql .= " ,   EnterpriseId = :EnterpriseId ";
        $sql .= " ,   PayingControlSeq = :PayingControlSeq ";
        $sql .= " ,   PayingControlStatus = :PayingControlStatus ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE PayingBackSeq = :PayingBackSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':PayingBackSeq' => $payingBackSeq,
                ':OrderSeq' => $row['OrderSeq'],
                ':PayBackAmount' => $row['PayBackAmount'],
                ':PayBackIndicationDate' => $row['PayBackIndicationDate'],
                ':PayDecisionFlg' => $row['PayDecisionFlg'],
                ':PayDecisionDate' => $row['PayDecisionDate'],
                ':EnterpriseId' => $row['EnterpriseId'],
                ':PayingControlSeq' => $row['PayingControlSeq'],
                ':PayingControlStatus' => $row['PayingControlStatus'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 指定条件（AND）の立替精算戻しデータを取得する。
     *
     * @param array $conditionArray 検索条件を格納した連想配列
     * @param boolean $isAsc プライマリキーのオーダー
     * @return ResultInterface
     */
    public function findPayingBackControl($conditionArray, $isAsc = false)
    {
        $prm = array();
        $sql  = " SELECT * FROM T_PayingBackControl WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }
        $sql .= " ORDER BY PayingBackSeq " . ($isAsc ? "asc" : "desc");

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
    }
}
