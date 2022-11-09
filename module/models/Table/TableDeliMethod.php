<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_DeliveryMethodテーブルへのアダプタ
 */
class TableDeliMethod
{
    protected $_name = 'M_DeliveryMethod';
    protected $_primary = array('DeliMethodId');
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
	 * 配送方法データを取得する
	 *
	 * @param int $deliMethodId
	 * @return ResultInterface
	 */
	public function find($deliMethodId)
	{
        $sql  = " SELECT * FROM M_DeliveryMethod WHERE DeliMethodId = :DeliMethodId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':DeliMethodId' => $deliMethodId,
        );

        return $stm->execute($prm);
	}

    /**
	 * すべての配送方法データを取得する
	 *
	 * @return ResultInterface
	 */
	public function getAll()
	{
	    $sql = " SELECT * FROM M_DeliveryMethod ORDER BY DeliMethodId ";
        return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 有効フラッグが有効の配送方法データを取得する。
	 *
	 * @return ResultInterface
	 */
	public function getValidAll()
	{
	    $sql = " SELECT * FROM M_DeliveryMethod WHERE ValidFlg = 1 ORDER BY DeliMethodId ";
        return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO M_DeliveryMethod (DeliMethodName, DeliMethodNameB, EnableCancelFlg, PayChgCondition, ValidFlg, ArrivalConfirmUrl, ValidateRegex, ListNumber, JournalRegistClass, ProductServiceClass, SendMailRequestModifyJournalFlg, RegistDate, RegistId, UpdateDate, UpdateId) VALUES (";
        $sql .= "   :DeliMethodName ";
        $sql .= " , :DeliMethodNameB ";
        $sql .= " , :EnableCancelFlg ";
        $sql .= " , :PayChgCondition ";
        $sql .= " , :ValidFlg ";
        $sql .= " , :ArrivalConfirmUrl ";
        $sql .= " , :ValidateRegex ";
        $sql .= " , :ListNumber ";
        $sql .= " , :JournalRegistClass ";
        $sql .= " , :ProductServiceClass ";
        $sql .= " , :SendMailRequestModifyJournalFlg ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':DeliMethodName' => $data['DeliMethodName'],
                ':DeliMethodNameB' => $data['DeliMethodNameB'],
                ':EnableCancelFlg' => $data['EnableCancelFlg'],
                ':PayChgCondition' => $data['PayChgCondition'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
                ':ArrivalConfirmUrl' => $data['ArrivalConfirmUrl'],
                ':ValidateRegex' => $data['ValidateRegex'],
                ':ListNumber' => $data['ListNumber'],
                ':JournalRegistClass' => isset($data['JournalRegistClass']) ? $data['JournalRegistClass'] : 1,
                ':ProductServiceClass' => isset($data['ProductServiceClass']) ? $data['ProductServiceClass'] : 0,
                ':SendMailRequestModifyJournalFlg' => isset($data['SendMailRequestModifyJournalFlg']) ? $data['SendMailRequestModifyJournalFlg'] : 1,
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

	/**
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param unknown_type $deliMethodId 更新するDeliMethodId
	 */
	public function saveUpdate($data, $deliMethodId)
	{
        $sql = " SELECT * FROM M_DeliveryMethod WHERE DeliMethodId = :DeliMethodId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':DeliMethodId' => $deliMethodId,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE M_DeliveryMethod ";
        $sql .= " SET ";
        $sql .= "     DeliMethodName = :DeliMethodName ";
        $sql .= " ,   DeliMethodNameB = :DeliMethodNameB ";
        $sql .= " ,   EnableCancelFlg = :EnableCancelFlg ";
        $sql .= " ,   PayChgCondition = :PayChgCondition ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " ,   ArrivalConfirmUrl = :ArrivalConfirmUrl ";
        $sql .= " ,   ValidateRegex = :ValidateRegex ";
        $sql .= " ,   ListNumber = :ListNumber ";
        $sql .= " ,   JournalRegistClass = :JournalRegistClass ";
        $sql .= " ,   ProductServiceClass = :ProductServiceClass ";
        $sql .= " ,   SendMailRequestModifyJournalFlg = :SendMailRequestModifyJournalFlg ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " WHERE DeliMethodId = :DeliMethodId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':DeliMethodId' => $deliMethodId,
                ':DeliMethodName' => $row['DeliMethodName'],
                ':DeliMethodNameB' => $row['DeliMethodNameB'],
                ':EnableCancelFlg' => $row['EnableCancelFlg'],
                ':PayChgCondition' => $row['PayChgCondition'],
                ':ValidFlg' => $row['ValidFlg'],
                ':ArrivalConfirmUrl' => $row['ArrivalConfirmUrl'],
                ':ValidateRegex' => $row['ValidateRegex'],
                ':ListNumber' => $row['ListNumber'],
                ':JournalRegistClass' => $row['JournalRegistClass'],
                ':ProductServiceClass' => $row['ProductServiceClass'],
                ':SendMailRequestModifyJournalFlg' => $row['SendMailRequestModifyJournalFlg'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
        );

        return $stm->execute($prm);
    }

    /**
     * 対象の有効フラッグが有効のDeliveryMethodIdに紐づく情報を取得する.
     *
	 * @param int $deliMethodId
	 * @return ResultInterface
     */
    public function getValidDeliMethod($deliMethodId)
    {
        $sql  = " SELECT * FROM M_DeliveryMethod WHERE ValidFlg = 1 AND DeliMethodId = :DeliMethodId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':DeliMethodId' => $deliMethodId,
        );

        return $stm->execute($prm);
    }

    /**
     * 対象のDeliveryMethodIdに紐づく情報を取得する.
     *
     * @param string deliverymethodidのカンマ区切り文字列
	 * @return ResultInterface
     */
    public function getByDeliMethodIds($deliMethodIds)
    {
        $sql  = " SELECT * FROM M_DeliveryMethod WHERE DeliMethodId in ( :DeliMethodId ) ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':DeliMethodId' => $deliMethodIds,
        );

        return $stm->execute($prm);
    }

    /**
     * 指定配送方法IDのリストを元に配送方法データを取得する。
     * 戻り値の並び順はリストの指定順通りとなる
     *
     * @param array $deliMethodIds 配送方法IDのリスト
	 * @return ResultInterface
     */
    public function findByDeliMethodIds(array $deliMethodIds)
    {
        $implodestr = implode(",", $deliMethodIds);

        return $this->_adapter->query(sprintf(
            " SELECT * FROM M_DeliveryMethod WHERE DeliMethodId in ( %s ) ORDER BY FIELD(DeliMethodId, %s ) ", $implodestr, $implodestr))->execute(null);
    }
}
