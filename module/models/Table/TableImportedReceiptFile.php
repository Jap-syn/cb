<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_ImportedReceiptFile(取込済み入金ファイル)テーブルへのアダプタ
 */
class TableImportedReceiptFile
{
    protected $_name = 'T_ImportedReceiptFile';
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
     * 取込済み入金ファイルデータを取得する
     *
     * @param int $seq SEQ
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM T_ImportedReceiptFile WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
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
        $sql  = " INSERT INTO T_ImportedReceiptFile (ReceiptProcessClass, FileName, Reserve, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :ReceiptProcessClass ";
        $sql .= " , :FileName ";
        $sql .= " , :Reserve ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ReceiptProcessClass' => $data['ReceiptProcessClass'],
                ':FileName' => $data['FileName'],
                ':Reserve' => $data['Reserve'],
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
     * @param int $seq SEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $seq)
    {
        $row = $this->find($seq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_ImportedReceiptFile ";
        $sql .= " SET ";
        $sql .= "     ReceiptProcessClass = :ReceiptProcessClass ";
        $sql .= " ,   FileName = :FileName ";
        $sql .= " ,   Reserve = :Reserve ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':ReceiptProcessClass' => $row['ReceiptProcessClass'],
                ':FileName' => $row['FileName'],
                ':Reserve' => $row['Reserve'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 取込み可能ファイルか？
     *
     * @param int $receiptProcessClass 入金処理分類(0：＠Payment、1：＠ペイメント、2：詳細入金ﾌｧｲﾙ)
     * @param string $fileName 取込ファイル名
     * @return boolean true:取込み可能／false:取込み不可(既に取込み済み)
     */
    public function isCanImport($receiptProcessClass, $fileName)
    {
        $sql  = " SELECT COUNT(1) AS cnt FROM T_ImportedReceiptFile WHERE ReceiptProcessClass = :ReceiptProcessClass AND FileName = :FileName ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ReceiptProcessClass' => $receiptProcessClass,
                ':FileName' => $fileName,
        );

        return ((int)$stm->execute($prm)->current()['cnt'] == 0) ? true : false;
    }
}
