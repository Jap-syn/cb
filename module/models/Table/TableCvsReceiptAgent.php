<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * コンビニ収納代行会社情報を管理する
 * M_CvsReceiptAgentテーブルへのアダプタ
 */
class TableCvsReceiptAgent
{
	protected $_name = 'M_CvsReceiptAgent';
	protected $_primary = array('ReceiptAgentId');
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
//         $data['ModifiedDate'] = date('Y-m-d H:i:s');
//         $data['InvalidFlg'] = 0;
//         return parent::createRow($data);
//     }
// Del By Takemasa(NDC) 20141216 End 廃止(使用禁止)

    /**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキー値
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO M_CvsReceiptAgent (ReceiptAgentName, ReceiptAgentCode, BarcodeLogicName, Note, AccountingSimpleName, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :ReceiptAgentName ";
        $sql .= " , :ReceiptAgentCode ";
        $sql .= " , :BarcodeLogicName ";
        $sql .= " , :Note ";
        $sql .= " , :AccountingSimpleName ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ReceiptAgentName' => $data['ReceiptAgentName'],
                ':ReceiptAgentCode' => $data['ReceiptAgentCode'],
                ':BarcodeLogicName' => $data['BarcodeLogicName'],
                ':Note' => $data['Note'],
                ':AccountingSimpleName' => $data['AccountingSimpleName'],
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
        $sql = " SELECT * FROM M_CvsReceiptAgent WHERE ReceiptAgentId = :ReceiptAgentId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ReceiptAgentId' => $seq,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE M_CvsReceiptAgent ";
        $sql .= " SET ";
        $sql .= "     ReceiptAgentName = :ReceiptAgentName ";
        $sql .= " ,   ReceiptAgentCode = :ReceiptAgentCode ";
        $sql .= " ,   BarcodeLogicName = :BarcodeLogicName ";
        $sql .= " ,   Note = :Note ";
        $sql .= " ,   AccountingSimpleName = :AccountingSimpleName ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE ReceiptAgentId = :ReceiptAgentId ";

        $stm = $this->_adapter->query($sql);
        $prm = array(
                ':ReceiptAgentId' => $seq,
                ':ReceiptAgentName' => $row['ReceiptAgentName'],
                ':ReceiptAgentCode' => $row['ReceiptAgentCode'],
                ':BarcodeLogicName' => $row['BarcodeLogicName'],
                ':Note' => $row['Note'],
                ':AccountingSimpleName' => $row['AccountingSimpleName'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
	}

    /**
     * すべてのコンビニ収納代行会社データを取得する
     *
     * @param null | string $order ソート順指定で、'asc'または'desc'の指定が可能。省略時は'asc'
     * @return ResultInterface
     */
    public function fetchAllAgents($order = 'asc')
    {
        $sql = " SELECT * FROM M_CvsReceiptAgent ORDER BY ReceiptAgentId " . $order;
        return $this->_adapter->query($sql)->execute(null);
    }
    /**
     * 対象の収納代行会社情報を取得する
     *
     * @param int $ReceiptAgentId
     * @return ResultInterface
     */
    public function findReceiptAgentId($ReceiptAgentId)
    {
        $sql = " SELECT * FROM M_CvsReceiptAgent WHERE ReceiptAgentId = :ReceiptAgentId ";
        return $this->_adapter->query($sql)->execute(array(':ReceiptAgentId' => $ReceiptAgentId));
    }

    /**
     * 収納代行会社情報を取得する(ドロップダウンコンボ用)
     *
     * @param boolean $isDammyOn 先頭にダミーを挿入するか
     * @return array
     */
    public function getCvsReceiptAgentList($isDammyOn = true)
    {
        $sql = " SELECT ReceiptAgentId, AccountingSimpleName FROM M_CvsReceiptAgent WHERE ValidFlg = 1 ORDER BY ReceiptAgentId ";
        $ri = $this->_adapter->query($sql)->execute(null);

        $result = array();
        if ($isDammyOn) {
            $result[0] = '-----';
        }
        foreach($ri as $row) {
            $result[$row['ReceiptAgentId']] = $row['AccountingSimpleName'];
        }

        return $result;
    }

    /**
     * CB_B2C_DEV-14
     * 事業者に紐づく収納代行会社情報を取得する(ドロップダウンコンボ用)
     *
     * @param boolean $isDammyOn 先頭にダミーを挿入するか
     * @return array
     */
    public function getEnterpriseCvsReceiptAgentList($EnterpriseId,$isDammyOn = true)
    {
    	$sql = " SELECT ReceiptAgentId,ReceiptAgentName ";
    	$sql.= " FROM M_CvsReceiptAgent ";
    	$sql.= " WHERE ValidFlg = 1  ";
    	$ri = $this->_adapter->query($sql)->execute(NULL);

    	$result = array();
    	if ($isDammyOn) {
    		$result[0] = '-----';
    	}
    	foreach($ri as $row) {
    		$result[$row['ReceiptAgentId']] = $row['ReceiptAgentName'];
    	}

    	return $result;
    }
}
