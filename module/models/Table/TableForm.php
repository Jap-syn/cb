<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_Form(画面定義)テーブルへのアダプタ
 */
class TableForm
{
    protected $_name = 'M_Form';
    protected $_primary = array('FormId');
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
     * 画面定義データを取得する
     *
     * @param int $formId 画面ID
     * @return ResultInterface
     */
    public function find($formId)
    {
        $sql = " SELECT * FROM M_Form WHERE FormId = :FormId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':FormId' => $formId,
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
        $sql  = " INSERT INTO M_Form (FormId, FormName, Module, Controller, Action, Note, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :FormId ";
        $sql .= " , :FormName ";
        $sql .= " , :Module ";
        $sql .= " , :Controller ";
        $sql .= " , :Action ";
        $sql .= " , :Note ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':FormId' => $data['FormId'],
                ':FormName' => $data['FormName'],
                ':Module' => $data['Module'],
                ':Controller' => $data['Controller'],
                ':Action' => $data['Action'],
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
     * @param int $formId 画面ID
     * @return ResultInterface
     */
    public function saveUpdate($data, $formId)
    {
        $row = $this->find($formId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE M_Form ";
        $sql .= " SET ";
        $sql .= "     FormName = :FormName ";
        $sql .= " ,   Module = :Module ";
        $sql .= " ,   Controller = :Controller ";
        $sql .= " ,   Action = :Action ";
        $sql .= " ,   Note = :Note ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE FormId = :FormId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':FormId' => $formId,
                ':FormName' => $row['FormName'],
                ':Module' => $row['Module'],
                ':Controller' => $row['Controller'],
                ':Action' => $row['Action'],
                ':Note' => $row['Note'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * FORMIDと名前のリストを取得する。
     *
     * @return array
     */
     public function getFormIdList()
     {
        $sql = " SELECT FormId, FormName FROM M_Form WHERE ValidFlg = 1 ";

        $stm = $this->_adapter->query($sql);

        $ri = $stm->execute(null);

        $formIdList = ResultInterfaceToArray($ri);

        return $formIdList;
    }
}
