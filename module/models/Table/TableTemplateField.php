<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_TemplateField(テンプレートフィールドマスター)テーブルへのアダプタ
 */
class TableTemplateField
{
    protected $_name = 'M_TemplateField';
    protected $_primary = array ('TemplateSeq', 'ListNumber');
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
     * コードマスターデータを取得する
     *
     * @param int $templateSeq テンプレートSEQ
     * @param int $listNumber 順序
     * @return ResultInterface
     */
    public function find($templateSeq, $listNumber)
    {
        $sql = " SELECT * FROM M_TemplateField WHERE TemplateSeq = :TemplateSeq AND ListNumber = :ListNumber ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':TemplateSeq' => $templateSeq,
                ':ListNumber' => $listNumber,
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
        $sql  = " INSERT INTO M_TemplateField (TemplateSeq, ListNumber, PhysicalName, LogicalName, FieldClass, RequiredFlg, DefaultValue, DispWidth, TableName, ValidationRegex, ApplicationData, RegistDate, RegistId, UpdateDate ,UpdateId, ValidFlg) VALUES (";
        $sql .= "   :TemplateSeq ";
        $sql .= " , :ListNumber ";
        $sql .= " , :PhysicalName ";
        $sql .= " , :LogicalName ";
        $sql .= " , :FieldClass ";
        $sql .= " , :RequiredFlg ";
        $sql .= " , :DefaultValue ";
        $sql .= " , :DispWidth ";
        $sql .= " , :TableName ";
        $sql .= " , :ValidationRegex ";
        $sql .= " , :ApplicationData ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':TemplateSeq' => $data['TemplateSeq'],
                ':ListNumber' => $data['ListNumber'],
                ':PhysicalName' => $data['PhysicalName'],
                ':LogicalName' => $data['LogicalName'],
                ':FieldClass' => isset($data['FieldClass']) ? $data['FieldClass'] : 'CHAR',
                ':RequiredFlg' => isset($data['RequiredFlg']) ? $data['RequiredFlg'] : 0,
                ':DefaultValue' => $data['DefaultValue'],
                ':DispWidth' => $data['DispWidth'],
                ':TableName' => $data['TableName'],
                ':ValidationRegex' => $data['ValidationRegex'],
                ':ApplicationData' => $data['ApplicationData'],
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
     * @param int $templateSeq テンプレートSEQ
     * @param int $listNumber 順序
     * @return ResultInterface
     */
    public function saveUpdate($data, $templateSeq, $listNumber)
    {
        $row = $this->find($templateSeq, $listNumber)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE M_TemplateField ";
        $sql .= " SET ";
        $sql .= "     PhysicalName = :PhysicalName ";
        $sql .= " ,   LogicalName = :LogicalName ";
        $sql .= " ,   FieldClass = :FieldClass ";
        $sql .= " ,   RequiredFlg = :RequiredFlg ";
        $sql .= " ,   DefaultValue = :DefaultValue ";
        $sql .= " ,   DispWidth = :DispWidth ";
        $sql .= " ,   TableName = :TableName ";
        $sql .= " ,   ValidationRegex = :ValidationRegex ";
        $sql .= " ,   ApplicationData = :ApplicationData ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE TemplateSeq = :TemplateSeq ";
        $sql .= " AND   ListNumber = :ListNumber ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':TemplateSeq' => $templateSeq,
                ':ListNumber' => $listNumber,
                ':PhysicalName' => $row['PhysicalName'],
                ':LogicalName' => $row['LogicalName'],
                ':FieldClass' => $row['FieldClass'],
                ':RequiredFlg' => $row['RequiredFlg'],
                ':DefaultValue' => $row['DefaultValue'],
                ':DispWidth' => $row['DispWidth'],
                ':TableName' => $row['TableName'],
                ':ValidationRegex' => $row['ValidationRegex'],
                ':ApplicationData' => $row['ApplicationData'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 指定されたレコードを削除する。
     *
     * @param int $templateSeq テンプレートSEQ
     */
    public function delete($templateSeq)
    {
        $sql = " DELETE FROM M_TemplateField WHERE TemplateSeq = :TemplateSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':TemplateSeq' => $templateSeq,
        );

        return $stm->execute($prm);
    }

    /**
     * 指定テンプレートSEQのコードマスターデータを取得する
     *
     * @param int $templateSeq テンプレートSEQ
     * @return ResultInterface
     */
    public function get($templateSeq)
    {
        $sql = " SELECT * FROM M_TemplateField WHERE TemplateSeq = :TemplateSeq ORDER BY ListNumber ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':TemplateSeq' => $templateSeq,
        );

        return $stm->execute($prm);
    }

    /**
     * 指定条件（AND）のテンプレートフィールドマスターを取得する。
     *
     * @param array $conditionArray 検索条件を格納した連想配列
     * @param boolean $isAsc プライマリキーのオーダー
     * @return ResultInterface
     */
    public function findTemplateField($conditionArray, $isAsc = false)
    {
        $prm = array();
        $sql  = " SELECT * FROM M_TemplateField WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }
        $sql .= " ORDER BY TemplateSeq " . ($isAsc ? "asc" : "desc");
        $sql .= "        , ListNumber " . ($isAsc ? "asc" : "desc");

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
    }
}
