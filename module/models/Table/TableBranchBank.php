<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_BranchBank(銀行支店マスター)テーブルへのアダプタ
 */
class TableBranchBank
{
    protected $_name = 'T_BranchBank';
    protected $_primary = array('BranchBankId');
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
     * 銀行支店マスターデータを取得する(有効フラグ＝有効データに限る)
     *
     * @param int $branchBankId 銀行支店ID
     * @return ResultInterface
     */
    public function find($branchBankId)
    {
        $sql = " SELECT * FROM T_BranchBank WHERE ValidFlg = 1 AND BranchBankId = :BranchBankId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BranchBankId' => $branchBankId,
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
        $sql  = " INSERT INTO T_BranchBank (BankCode, BranchCode, BankName, BranchName, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :BankCode ";
        $sql .= " , :BranchCode ";
        $sql .= " , :BankName ";
        $sql .= " , :BranchName ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BankCode' => $data['BankCode'],
                ':BranchCode' => $data['BranchCode'],
                ':BankName' => $data['BankName'],
                ':BranchName' => $data['BranchName'],
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
     * @param int $branchBankId 銀行支店ID
     * @return ResultInterface
     */
    public function saveUpdate($data, $branchBankId)
    {
        $row = $this->find($branchBankId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_BranchBank ";
        $sql .= " SET ";
        $sql .= "     BankCode = :BankCode ";
        $sql .= " ,   BranchCode = :BranchCode ";
        $sql .= " ,   BankName = :BankName ";
        $sql .= " ,   BranchName = :BranchName ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE BranchBankId = :BranchBankId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BranchBankId' => $branchBankId,
                ':BankCode' => $row['BankCode'],
                ':BranchCode' => $row['BranchCode'],
                ':BankName' => $row['BankName'],
                ':BranchName' => $row['BranchName'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => $row['UpdateDate'],
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 銀行支店情報を取得する(ドロップダウンコンボ用)
     *
     * @param boolean $isDammyOn 先頭にダミーを挿入するか
     * @return array
     */
    public function getBranchBankList($isDammyOn = true)
    {
        $sql = " SELECT BranchBankId, CONCAT(BankName, ' (' , BranchName , ')') AS BankBranchName FROM M_BranchBank WHERE ValidFlg = 1 ORDER BY BranchBankId ";
        $ri = $this->_adapter->query($sql)->execute(null);

        $result = array();
        if ($isDammyOn) {
            $result[0] = '-----';
        }
        foreach($ri as $row) {
            $result[$row['BranchBankId']] = $row['BankBranchName'];
        }

        return $result;
    }
}
