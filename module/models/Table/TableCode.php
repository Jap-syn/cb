<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_Code(コードマスター)テーブルへのアダプタ
 */
class TableCode
{
    protected $_name = 'M_Code';
    protected $_primary = array ('CodeId', 'KeyCode');
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
     * @param int $codeId コード識別ID
     * @param int $keyCode KEYコード
     * @return ResultInterface
     */
    public function find($codeId, $keyCode)
    {
        $sql = " SELECT * FROM M_Code WHERE CodeId = :CodeId AND KeyCode = :KeyCode ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CodeId' => $codeId,
                ':KeyCode' => $keyCode,
        );

        return $stm->execute($prm);
    }

    public function find2($codeId, $keyCode)
    {
        $sql = " SELECT * FROM M_Code WHERE CodeId = :CodeId AND KeyCode = :KeyCode AND ValidFlg = 1 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':CodeId' => $codeId,
            ':KeyCode' => $keyCode,
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
        $sql  = " INSERT INTO M_Code (CodeId, KeyCode, KeyContent, Class1, Class2, Class3, Note, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :CodeId ";
        $sql .= " , :KeyCode ";
        $sql .= " , :KeyContent ";
        $sql .= " , :Class1 ";
        $sql .= " , :Class2 ";
        $sql .= " , :Class3 ";
        $sql .= " , :Note ";
        $sql .= " , :SystemFlg ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CodeId' => $data['CodeId'],
                ':KeyCode' => $data['KeyCode'],
                ':KeyContent' => $data['KeyContent'],
                ':Class1' => $data['Class1'],
                ':Class2' => $data['Class2'],
                ':Class3' => $data['Class3'],
                ':Note' => $data['Note'],
                ':SystemFlg' => isset($data['SystemFlg']) ? $data['SystemFlg'] : 0,
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
     * @param int $keyCode KEYコード
     * @return ResultInterface
     */
    public function saveUpdate($data, $codeId, $keyCode)
    {
        $row = $this->find($codeId, $keyCode)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE M_Code ";
        $sql .= " SET ";
        $sql .= "     KeyContent = :KeyContent ";
        $sql .= " ,   Class1 = :Class1 ";
        $sql .= " ,   Class2 = :Class2 ";
        $sql .= " ,   Class3 = :Class3 ";
        $sql .= " ,   Note = :Note ";
        $sql .= " ,   SystemFlg = :SystemFlg ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE CodeId = :CodeId ";
        $sql .= " AND   KeyCode = :KeyCode ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CodeId' => $codeId,
                ':KeyCode' => $keyCode,
                ':KeyContent' => $row['KeyContent'],
                ':Class1' => $row['Class1'],
                ':Class2' => $row['Class2'],
                ':Class3' => $row['Class3'],
                ':Note' => $row['Note'],
                ':SystemFlg' => $row['SystemFlg'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }


    /**
     * すべてのマスターデータを取得する
     *
     * @return ResultInterface
     */
    public function getAll()
    {
        $sql = " SELECT * FROM M_Code ORDER BY CodeId, KeyCode ";
        return $this->_adapter->query($sql)->execute(null);
    }

    /**
     * 指定コード識別IDのマスターデータを取得する。
     *
     * @param string $codeId コード識別ID
     * @return ResultInterface
     */
    public function getMasterByClass($codeId)
    {
        $sql = " SELECT * FROM M_Code WHERE ValidFlg = 1 AND CodeId = :CodeId ORDER BY KeyCode ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CodeId' => $codeId,
        );

        return $stm->execute($prm);
    }

    /**
     * 指定コード識別IDのマスターデータを取得する。(取引履歴検索の入金方法用)
     *
     * @param string $codeId コード識別ID
     * @return ResultInterface
     */
    public function getMasterByClass5($codeId)
    {
        $sql = " SELECT KeyCode FROM M_Code WHERE ValidFlg = 1 AND CodeId = :CodeId AND Class5 = 1 ORDER BY KeyCode ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CodeId' => $codeId,
        );

        return $stm->execute($prm);
    }

    /**
     * 指定コード識別IDのマスターデータを取得する。(無効レコード含む）
     *
     * @param string $codeId コード識別ID
     * @return ResultInterface
     */
    public function getMasterByClassAll($codeId)
    {
        $sql = " SELECT * FROM M_Code WHERE CodeId = :CodeId ORDER BY KeyCode ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CodeId' => $codeId,
        );

        return $stm->execute($prm);
    }

    /**
     * 指定コード識別ID・KEYコードの説明(Note)を取得する。
     *
     * @param int $codeId コード識別ID
     * @param int $keyCode KEYコード
     * @return string 説明
     */
    public function getMasterDescription($codeId, $keyCode)
    {
        $sql = " SELECT Note FROM M_Code WHERE CodeId = :CodeId AND KeyCode = :KeyCode ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CodeId' => $codeId,
                ':KeyCode' => $keyCode,
        );

        return $stm->execute($prm)->current()['Note'];
    }

    /**
     * 指定コード識別ID・KEYコードのキャプション(KeyContent)を取得する。
     *
     * @param int $codeId コード識別ID
     * @param int $keyCode KEYコード
     * @return string キャプション
     */
    public function getMasterCaption($codeId, $keyCode)
    {
        $sql = " SELECT KeyContent FROM M_Code WHERE CodeId = :CodeId AND KeyCode = :KeyCode ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CodeId' => $codeId,
                ':KeyCode' => $keyCode,
        );

        return $stm->execute($prm)->current()['KeyContent'];
    }

    /**
     * 指定コード識別ID・KEYコードのショートキャプション(Class2)を取得する。
     *
     * @param int $codeId コード識別ID
     * @param int $keyCode KEYコード
     * @return string ショートキャプション
     */
    public function getMasterShortCaption($codeId, $keyCode)
    {
        $sql = " SELECT Class2 FROM M_Code WHERE CodeId = :CodeId AND KeyCode = :KeyCode ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CodeId' => $codeId,
                ':KeyCode' => $keyCode,
        );

        return $stm->execute($prm)->current()['Class2'];
    }

    /**
     * 指定コード識別ID・KEYコードの補助コード(Class1)を取得する。
     *
     * @param int $codeId コード識別ID
     * @param int $keyCode KEYコード
     * @return int 補助コード
     */
    public function getMasterAssCode($codeId, $keyCode)
    {
        $sql = " SELECT Class1 FROM M_Code WHERE CodeId = :CodeId AND KeyCode = :KeyCode ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CodeId' => $codeId,
                ':KeyCode' => $keyCode,
        );

        return $stm->execute($prm)->current()['Class1'];
    }


    public function getMasterAssCodeTodo($codeId, $keyCode)
    {
        $sql = " SELECT Class2 FROM M_Code WHERE CodeId = :CodeId AND KeyCode = :KeyCode AND ValidFlg=1";

        $stm = $this->_adapter->query($sql);

        $prm = array(
            ':CodeId' => $codeId,
            ':KeyCode' => $keyCode,
        );

        return $stm->execute($prm)->current()['Class2'];
    }

}
