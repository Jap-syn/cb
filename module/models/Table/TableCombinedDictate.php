<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_CombinedDictate(取りまとめ指示)テーブルへのアダプタ
 */
class TableCombinedDictate
{
    protected $_name = 'T_CombinedDictate';
    protected $_primary = array('CombinedDictateSeq');
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
     * 取りまとめ指示データを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $combinedDictateSeq 取りまとめ指示SEQ
     * @return ResultInterface
     */
    public function find($combinedDictateSeq)
    {
        $sql = " SELECT * FROM T_CombinedDictate WHERE ValidFlg = 1 AND CombinedDictateSeq = :CombinedDictateSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CombinedDictateSeq' => $combinedDictateSeq,
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
        $sql  = " INSERT INTO T_CombinedDictate (CombinedDictateSeq, CombinedDictateGroup, OrderSeq, CombinedStatus, IndicationDate, ExecDate, CancelDate, EnterpriseId, ErrorMsg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :CombinedDictateSeq ";
        $sql .= " , :CombinedDictateGroup ";
        $sql .= " , :OrderSeq ";
        $sql .= " , :CombinedStatus ";
        $sql .= " , :IndicationDate ";
        $sql .= " , :ExecDate ";
        $sql .= " , :CancelDate ";
        $sql .= " , :EnterpriseId ";
        $sql .= " , :ErrorMsg ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CombinedDictateSeq' => $data['CombinedDictateSeq'],
                ':CombinedDictateGroup' => $data['CombinedDictateGroup'],
                ':OrderSeq' => $data['OrderSeq'],
                ':CombinedStatus' => isset($data['CombinedStatus']) ? $data['CombinedStatus'] : 0,
                ':IndicationDate' => $data['IndicationDate'],
                ':ExecDate' => $data['ExecDate'],
                ':CancelDate' => $data['CancelDate'],
                ':EnterpriseId' => $data['EnterpriseId'],
                ':ErrorMsg' => $data['ErrorMsg'],
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
     * @param int $combinedDictateSeq 取りまとめ指示SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $combinedDictateSeq)
    {
        $row = $this->find($combinedDictateSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_CombinedDictate ";
        $sql .= " SET ";
        $sql .= "     CombinedStatus = :CombinedStatus ";
        $sql .= " ,   CombinedDictateGroup = :CombinedDictateGroup ";
        $sql .= " ,   OrderSeq = :OrderSeq ";
        $sql .= " ,   IndicationDate = :IndicationDate ";
        $sql .= " ,   ExecDate = :ExecDate ";
        $sql .= " ,   CancelDate = :CancelDate ";
        $sql .= " ,   EnterpriseId = :EnterpriseId ";
        $sql .= " ,   ErrorMsg = :ErrorMsg ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE CombinedDictateSeq = :CombinedDictateSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CombinedDictateSeq' => $combinedDictateSeq,
                ':CombinedDictateGroup' => $row['CombinedDictateGroup'],
                ':OrderSeq' => $row['OrderSeq'],
                ':CombinedStatus' => $row['CombinedStatus'],
                ':IndicationDate' => $row['IndicationDate'],
                ':ExecDate' => $row['ExecDate'],
                ':CancelDate' => $row['CancelDate'],
                ':EnterpriseId' => $row['EnterpriseId'],
                ':ErrorMsg' => $row['ErrorMsg'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 取りまとめ指示中の取りまとめ指示グループを取得
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getCombinedDictateGroups()
    {
        $sql = <<<EOQ
SELECT	CombinedDictateGroup
FROM	T_CombinedDictate
WHERE	CombinedStatus = 0
AND		ValidFlg = 1
GROUP BY
		CombinedDictateGroup
EOQ;
        return $this->_adapter->query($sql)->execute();
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
        $sql  = " SELECT * FROM T_CombinedDictate WHERE 1 = 1 ";
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
            $this->saveUpdate($row, $row['CombinedDictateSeq']);
        }
    }
}
