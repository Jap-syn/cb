<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_OutputFile(出力ファイル管理)テーブルへのアダプタ
 */
class TableOutputFile
{
    protected $_name = 'T_OutputFile';
    protected $_primary = array('OutputFileSeq');
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
     * 出力ファイル管理データを取得する
     *
     * @param int $outputFileSeq SEQ
     * @return ResultInterface
     */
    public function find($outputFileSeq)
    {
        $sql = " SELECT * FROM T_OutputFile WHERE OutputFileSeq = :OutputFileSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OutputFileSeq' => $outputFileSeq,
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
        $sql  = " INSERT INTO T_OutputFile (OutputFile, RegistDate, RegistId) VALUES (";
        $sql .= "   :OutputFile ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OutputFile' => $data['OutputFile'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param @param int $outputFileSeq SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $outputFileSeq)
    {
        $row = $this->find($outputFileSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_OutputFile ";
        $sql .= " SET ";
        $sql .= "     OutputFile = :OutputFile ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " WHERE OutputFileSeq = :OutputFileSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OutputFileSeq' => $outputFileSeq,
                ':OutputFile' => $row['OutputFile'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $row['RegistId'],
        );

        return $stm->execute($prm);
    }
}
