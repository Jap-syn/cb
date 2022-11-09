<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * コンビニ収納情報をCBまたはOEMへ関連付けて管理する
 * T_OemCvsAccountテーブルへのアダプタ
 */
class TableOemCvsAccount
{
	protected $_name = 'T_OemCvsAccount';
	protected $_primary = array('CvsAccountId');
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
//         return parent::createRow($data);
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

        $sql  = " INSERT INTO T_OemCvsAccount (OemId, ReceiptAgentId, SubscriberCode, Option1, Option2, Option3, SubscriberName, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OemId ";
        $sql .= " , :ReceiptAgentId ";
        $sql .= " , :SubscriberCode ";
        $sql .= " , :Option1 ";
        $sql .= " , :Option2 ";
        $sql .= " , :Option3 ";
        $sql .= " , :SubscriberName ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $data['OemId'],
                ':ReceiptAgentId' => $data['ReceiptAgentId'],
                ':SubscriberCode' => $data['SubscriberCode'],
                ':Option1' => $data['Option1'],
                ':Option2' => $data['Option2'],
                ':Option3' => $data['Option3'],
                ':SubscriberName' => $data['SubscriberName'],
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
        $sql = " SELECT * FROM T_OemCvsAccount WHERE CvsAccountId = :CvsAccountId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CvsAccountId' => $seq,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_OemCvsAccount ";
        $sql .= " SET ";
        $sql .= "     OemId = :OemId ";
        $sql .= " ,   ReceiptAgentId = :ReceiptAgentId ";
        $sql .= " ,   SubscriberCode = :SubscriberCode ";
        $sql .= " ,   Option1 = :Option1 ";
        $sql .= " ,   Option2 = :Option2 ";
        $sql .= " ,   Option3 = :Option3 ";
        $sql .= " ,   SubscriberName = :SubscriberName ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE CvsAccountId = :CvsAccountId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CvsAccountId' => $seq,
                ':OemId' => $row['OemId'],
                ':ReceiptAgentId' => $row['ReceiptAgentId'],
                ':SubscriberCode' => $row['SubscriberCode'],
                ':Option1' => $row['Option1'],
                ':Option2' => $row['Option2'],
                ':Option3' => $row['Option3'],
                ':SubscriberName' => $row['SubscriberName'],
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
        $sql = " SELECT * FROM T_OemCvsAccount WHERE OemId = :OemId ORDER BY CvsAccountId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $oemId,
        );

        return $stm->execute($prm);
    }
}
