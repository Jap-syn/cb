<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * 銀行入金口座をCBまたはOEMへ関連付けて管理する
 * T_OemBankAccountテーブルへのアダプタ
 */
class TableOemBankAccount
{
	protected $_name = 'T_OemBankAccount';
	protected $_primary = array('BankAccountId');
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

// Del By Takemasa(NDC) 20141216 Stt 廃止(使用禁止)
//     /**
//      * 新しい行オブジェクトを作成する
//      *
//      * @param null | array $data 初期データの連想配列
//      * @return Zend_Db_Table_Row_Abstract このテーブルクラスのスキーマを持つ新しい行オブジェクト
//      */
//     public function createRow(array $data = array())
//     {
//          $data['ModifiedDate'] = date('Y-m-d H:i:s');
//          return parent::createRow($data);
//     }
// Del By Takemasa(NDC) 20141216 End 廃止(使用禁止)

    /**
	 * 新しいレコードをインサートする。
	 *
	 * @param int $oemId 関連付けるOEM ID
	 * @param array $data インサートする連想配列
	 * @return プライマリキー値
	 */
	public function saveNew($oemId, array $data = array())
	{
        $oemId = (int)$oemId;
        $data['OemId'] = $oemId;

        $sql  = " INSERT INTO T_OemBankAccount (OemId, ServiceKind, BankCode, BranchCode, BankName, BranchName, DepositClass, AccountNumber, AccountHolder, AccountHolderKn, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OemId ";
        $sql .= " , :ServiceKind ";
        $sql .= " , :BankCode ";
        $sql .= " , :BranchCode ";
        $sql .= " , :BankName ";
        $sql .= " , :BranchName ";
        $sql .= " , :DepositClass ";
        $sql .= " , :AccountNumber ";
        $sql .= " , :AccountHolder ";
        $sql .= " , :AccountHolderKn ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $data['OemId'],
                ':ServiceKind' => isset($data['ServiceKind']) ? $data['ServiceKind'] : 0,
                ':BankCode' => $data['BankCode'],
                ':BranchCode' => $data['BranchCode'],
                ':BankName' => $data['BankName'],
                ':BranchName' => $data['BranchName'],
                ':DepositClass' => isset($data['DepositClass']) ? $data['DepositClass'] : 0,
                ':AccountNumber' => $data['AccountNumber'],
                ':AccountHolder' => $data['AccountHolder'],
                ':AccountHolderKn' => $data['AccountHolderKn'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

	protected function isPrimaryKey($colName) {
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
	 * @param int $seq 更新するSeq
	 */
	public function saveUpdate($data, $seq)
	{
        $sql = " SELECT * FROM T_OemBankAccount WHERE BankAccountId = :BankAccountId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BankAccountId' => $seq,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_OemBankAccount ";
        $sql .= " SET ";
        $sql .= "     OemId = :OemId ";
        $sql .= " ,   ServiceKind = :ServiceKind ";
        $sql .= " ,   BankCode = :BankCode ";
        $sql .= " ,   BranchCode = :BranchCode ";
        $sql .= " ,   BankName = :BankName ";
        $sql .= " ,   BranchName = :BranchName ";
        $sql .= " ,   DepositClass = :DepositClass ";
        $sql .= " ,   AccountNumber = :AccountNumber ";
        $sql .= " ,   AccountHolder = :AccountHolder ";
        $sql .= " ,   AccountHolderKn = :AccountHolderKn ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE BankAccountId = :BankAccountId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':BankAccountId' => $seq,
                ':OemId' => $row['OemId'],
                ':ServiceKind' => $row['ServiceKind'],
                ':BankCode' => $row['BankCode'],
                ':BranchCode' => $row['BranchCode'],
                ':BankName' => $row['BankName'],
                ':BranchName' => $row['BranchName'],
                ':DepositClass' => $row['DepositClass'],
                ':AccountNumber' => $row['AccountNumber'],
                ':AccountHolder' => $row['AccountHolder'],
                ':AccountHolderKn' => $row['AccountHolderKn'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
	}

    /**
     * 指定OEM IDに関連付けられているレコードを検索する
     *
     * @param int $oemId OEM ID
     * @return ResultInterface
     */
    public function findByOemId($oemId)
    {
        $sql = " SELECT * FROM T_OemBankAccount WHERE OemId = :OemId ORDER BY BankAccountId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $oemId,
        );

        return $stm->execute($prm);
    }
}
