<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use models\Logic\LogicTemplate;

/**
 * T_ImportedAccountTransferFile(取込済み口座振替入金ファイル)テーブルへのアダプタ
 */
class TableImportedAccountTransferFile
{
    protected $_name = 'T_ImportedAccountTransferFile';
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
     * 取込済み口座振替入金ファイルデータを取得する
     *
     * @param int $seq SEQ
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM T_ImportedAccountTransferFile WHERE Seq = :Seq ";

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
        $sql  = " INSERT INTO T_ImportedAccountTransferFile (CreditTransferFlg, FileName, Status, ReceiptResult, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :CreditTransferFlg ";
        $sql .= " , :FileName ";
        $sql .= " , :Status ";
        $sql .= " , :ReceiptResult ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CreditTransferFlg' => isset($data['CreditTransferFlg']) ? $data['CreditTransferFlg'] : 1,
                ':FileName' => $data['FileName'],
                ':Status' => isset($data['Status']) ? $data['Status'] : 0,
                ':ReceiptResult' => $data['ReceiptResult'],
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

        $sql  = " UPDATE T_ImportedAccountTransferFile ";
        $sql .= " SET ";
        $sql .= "     CreditTransferFlg = :CreditTransferFlg ";
        $sql .= " ,   FileName = :FileName ";
        $sql .= " ,   Status = :Status ";
        $sql .= " ,   ReceiptResult = :ReceiptResult ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " ,   csv      = :csv ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':CreditTransferFlg' => $row['CreditTransferFlg'],
                ':FileName' => $row['FileName'],
                ':Status' => $row['Status'],
                ':ReceiptResult' => $row['ReceiptResult'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
                ':csv'      => $row['csv'],
        );

        return $stm->execute($prm);
    }

    /**
     * 取込み可能ファイルか？
     *
     * @param string $fileName 取込ファイル名
     * @return boolean true:取込み可能／false:取込み不可(処理中、又は、取込み済み)
     */
    public function isCanImport($fileName, $CreditTransferFlg)
    {
        $sql  = " SELECT COUNT(1) AS cnt FROM T_ImportedAccountTransferFile WHERE FileName = :FileName AND Status IN (0, 1) AND CreditTransferFlg=:CreditTransferFlg ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':FileName' => $fileName,
                ':CreditTransferFlg' => $CreditTransferFlg,
        );

        return ((int)$stm->execute($prm)->current()['cnt'] == 0) ? true : false;
    }
}
