<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_TmpImageテーブルへのアダプタ
 */
class TableTmpImage
{
	protected $_name = 'T_TmpImage';
	protected $_primary = array('Seq');
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
     * 新しいレコードをインサートする。
     *
     * @param array $data インサートする連想配列
     * @return プライマリキーのバリュー
     */
    public function saveNew($data)
    {
        $sql  = " INSERT INTO T_TmpImage (OemId, UseType, FileName, ImageData, ImageType, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OemId ";
        $sql .= " , :UseType ";
        $sql .= " , :FileName ";
        $sql .= " , :ImageData ";
        $sql .= " , :ImageType ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $data['OemId'],
                ':UseType' => $data['UseType'],
                ':FileName' => $data['FileName'],
                ':ImageData' => $data['ImageData'],
                ':ImageType' => $data['ImageType'],
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
     * 指定のOEM一時画像ファイル取得
     *
     * @param int $seq シーケンス番号
     * @return ResultInterface
     */
    public function findTmpImage($seq)
    {
        $sql = " SELECT * FROM T_TmpImage WHERE ValidFlg = 1 AND Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
        );

        return $stm->execute($prm);
    }

    /**
     * ＰＫ項目か？
     *
     * @param string $colName カラム名
     * @return Boolean
     */
    protected function isPrimaryKey($colName)
    {
        $primaries = $this->_primary;
        if(is_array($primaries)) {
            return in_array($colName, $primaries);
        } else {
            return $colName == $primaries;
        }
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $seq シーケンス番号
     */
    public function saveUpdate($data, $seq)
    {
        $sql = " SELECT * FROM T_TmpImage WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_TmpImage ";
        $sql .= " SET ";
        $sql .= "     OemId = :OemId ";
        $sql .= " ,   UseType = :UseType ";
        $sql .= " ,   FileName = :FileName ";
        $sql .= " ,   ImageData = :ImageData ";
        $sql .= " ,   ImageType = :ImageType ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq = :Seq";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':OemId' => $row['OemId'],
                ':UseType' => $row['UseType'],
                ':FileName' => $row['FileName'],
                ':ImageData' => $row['ImageData'],
                ':ImageType' => $row['ImageType'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }
}
