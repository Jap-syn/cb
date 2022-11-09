<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_CodeManagement(コード識別管理マスター)テーブルへのアダプタ
 */
class TableCodeManagement
{
    protected $_name = 'M_CodeManagement';
    protected $_primary = array('CodeId');
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
     * コード識別管理マスターデータを取得する
     *
     * @param int $codeId コード識別ID
     * @return ResultInterface
     */
    public function find($codeId)
    {
        $sql = " SELECT * FROM M_CodeManagement WHERE CodeId = :CodeId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CodeId' => $codeId,
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
        $sql  = " INSERT INTO M_CodeManagement (CodeId, CodeName, KeyPhysicalName, KeyLogicName, Class1ValidFlg, Class1Name, Class2ValidFlg, Class2Name, Class3ValidFlg, Class3Name, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :CodeId ";
        $sql .= " , :CodeName ";
        $sql .= " , :KeyPhysicalName ";
        $sql .= " , :KeyLogicName ";
        $sql .= " , :Class1ValidFlg ";
        $sql .= " , :Class1Name ";
        $sql .= " , :Class2ValidFlg ";
        $sql .= " , :Class2Name ";
        $sql .= " , :Class3ValidFlg ";
        $sql .= " , :Class3Name ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CodeId' => $data['CodeId'],
                ':CodeName' => $data['CodeName'],
                ':KeyPhysicalName' => $data['KeyPhysicalName'],
                ':KeyLogicName' => $data['KeyLogicName'],
                ':Class1ValidFlg' => isset($data['Class1ValidFlg']) ? $data['Class1ValidFlg'] : 0,
                ':Class1Name' => $data['Class1Name'],
                ':Class2ValidFlg' => isset($data['Class2ValidFlg']) ? $data['Class2ValidFlg'] : 0,
                ':Class2Name' => $data['Class2Name'],
                ':Class3ValidFlg' => isset($data['Class3ValidFlg']) ? $data['Class3ValidFlg'] : 0,
                ':Class3Name' => $data['Class3Name'],
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
     * @param int $codeId コード識別ID
     * @return ResultInterface
     */
    public function saveUpdate($data, $codeId)
    {
        $row = $this->find($codeId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE M_CodeManagement ";
        $sql .= " SET ";
        $sql .= "     CodeName = :CodeName ";
        $sql .= " ,   KeyPhysicalName = :KeyPhysicalName ";
        $sql .= " ,   KeyLogicName = :KeyLogicName ";
        $sql .= " ,   Class1ValidFlg = :Class1ValidFlg ";
        $sql .= " ,   Class1Name = :Class1Name ";
        $sql .= " ,   Class2ValidFlg = :Class2ValidFlg ";
        $sql .= " ,   Class2Name = :Class2Name ";
        $sql .= " ,   Class3ValidFlg = :Class3ValidFlg ";
        $sql .= " ,   Class3Name = :Class3Name ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE CodeId = :CodeId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CodeId' => $codeId,
                ':CodeName' => $row['CodeName'],
                ':KeyPhysicalName' => $row['KeyPhysicalName'],
                ':KeyLogicName' => $row['KeyLogicName'],
                ':Class1ValidFlg' => $row['Class1ValidFlg'],
                ':Class1Name' => $row['Class1Name'],
                ':Class2ValidFlg' => $row['Class2ValidFlg'],
                ':Class2Name' => $row['Class2Name'],
                ':Class3ValidFlg' => $row['Class3ValidFlg'],
                ':Class3Name' => $row['Class3Name'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * コード識別管理マスターデータを全て取得する
     *
     * @return ResultInterface
     */
    public function getAll()
    {
        $sql = " SELECT * FROM M_CodeManagement WHERE ValidFlg = 1 ";

        $stm = $this->_adapter->query($sql);

        return $stm->execute(null);
    }
}
