<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * 加入者固有コード管理マスタ
 * M_SubscriberCodeテーブルへのアダプタ
 */
class TableSubscriberCode
{
	protected $_name = 'M_SubscriberCode';
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
     * すべての加入者固有コードを取得する
     *
     * @param null | string $order ソート順指定で、'asc'または'desc'の指定が可能。省略時は'asc'
     * @return ResultInterface
     */
    public function fetchAllSubscriberCode($order = 'asc')
    {
        $sql = " SELECT * FROM M_SubscriberCode ORDER BY ReceiptAgentId " . $order;
        return $this->_adapter->query($sql)->execute(null);
    }

    /**
     * 対象の収納代行会社に紐づく加入者固有コード情報を取得する
     *
     * @param int $ReceiptAgentId
     * @return ResultInterface
     */
    public function findReceiptAgentId($ReceiptAgentId)
    {
        $sql = " SELECT * FROM M_SubscriberCode WHERE ReceiptAgentId = :ReceiptAgentId ";
        return $this->_adapter->query($sql)->execute(array(':ReceiptAgentId' => $ReceiptAgentId));
    }

    /**
     * 対象の収納代行会社に紐づく加入者固有コード情報を取得する
     *
     * @param int $ReceiptAgentId
     * @return ResultInterface
     */
    public function findReceiptAgentIdSiteCntList($ReceiptAgentId)
    {
    	$sql = "  SELECT msc.*, ";
    	$sql.= "         ( SELECT COUNT(*)  ";
    	$sql.= "             FROM T_Site as ts  ";
    	$sql.= "            WHERE ts.ReceiptAgentId = msc.ReceiptAgentId  ";
    	$sql.= "              AND ts.SubscriberCode = msc.SubscriberCode ) AS siteCnt ";
    	$sql.= "    FROM M_SubscriberCode as msc  ";
    	$sql.= "   WHERE msc.ValidFlg=1  ";
    	$sql.= "     AND msc.ReceiptAgentId = :ReceiptAgentId ";
        $sql.= "   ORDER BY msc.SubscriberName ASC, msc.SubscriberCode DESC";
        return $this->_adapter->query($sql)->execute(array(':ReceiptAgentId' => $ReceiptAgentId));
    }

    /**
     * 対象の収納代行会社、加入者固有コードに紐づくデータを取得する
     *
     * @param int $ReceiptAgentId
     * @return ResultInterface
     */
    public function findReceiptAgentIdSubscriberCode($ReceiptAgentId,$SubscriberCode)
    {
    	$sql = " SELECT * FROM M_SubscriberCode WHERE ValidFlg=1 AND ReceiptAgentId = :ReceiptAgentId AND SubscriberCode = :SubscriberCode ";
    	return $this->_adapter->query($sql)->execute(array(':ReceiptAgentId' => $ReceiptAgentId,':SubscriberCode' => $SubscriberCode))->current();
    }

    /**
     * 対象の収納代行会社、加入者固有コードに紐づくデータ数を取得する
     *
     * @param int $ReceiptAgentId
     * @return ResultInterface
     */
    public function cntReceiptAgentIdSubscriberCode($ReceiptAgentId,$SubscriberCode)
    {
    	$sql = " SELECT count(*) as codeCnt FROM M_SubscriberCode WHERE ValidFlg=1 AND ReceiptAgentId = :ReceiptAgentId AND SubscriberCode = :SubscriberCode ";
    	return $this->_adapter->query($sql)->execute(array(':ReceiptAgentId' => $ReceiptAgentId,':SubscriberCode' => $SubscriberCode))->current()['codeCnt'];
    }

    public function saveUnappliedSubscriberCode($data, $updateId, $receiptAgentId, $subscriberCode)
    {
        $sql = " UPDATE M_SubscriberCode SET ";
        $sql .= "   SubscriberName=:SubscriberName ";
        $sql .= " , LinePayUseFlg=:LinePayUseFlg ";
        $sql .= " , LineApplyDate=:LineApplyDate ";
        $sql .= " , UpdateDate=:UpdateDate ";
        $sql .= " , UpdateId=:UpdateId ";
        $sql .= " WHERE ReceiptAgentId=:ReceiptAgentId";
        $sql .= " AND SubscriberCode=:SubscriberCode ";
        $sql .= " AND LinePayUseFlg=9 ";
        $stm = $this->_adapter->query($sql);
        $prm = array(
            ':ReceiptAgentId' => $receiptAgentId,
            ':SubscriberCode' => $subscriberCode,
            ':SubscriberName' => $data['SubscriberName'],
            ':UpdateId' => $updateId,
            ':UpdateDate' => date('Y-m-d H:i:s'),
            ':LinePayUseFlg' => is_null($data['LinePayUseFlg']) ? 0 : $data['LinePayUseFlg'],
            ':LineApplyDate' => ($data['LineApplyDate'] == '') ? null : $data['LineApplyDate'],
        );
        return $stm->execute($prm);
    }

    public function saveLineUseStartDate($data, $updateId, $receiptAgentId, $subscriberCode)
    {
        $sql = " UPDATE M_SubscriberCode SET ";
        $sql .= "   LineUseStartDate=:LineUseStartDate ";
        $sql .= " , UpdateDate=:UpdateDate ";
        $sql .= " , UpdateId=:UpdateId ";
        $sql .= " WHERE ReceiptAgentId=:ReceiptAgentId";
        $sql .= " AND SubscriberCode=:SubscriberCode ";
        $sql .= " AND LineUseStartDate IS NULL ";
        $stm = $this->_adapter->query($sql);
        $prm = array(
            ':ReceiptAgentId' => $receiptAgentId,
            ':SubscriberCode' => $subscriberCode,
            ':LineUseStartDate' => ($data['LineUseStartDate'] == '') ? null : $data['LineUseStartDate'],
            ':UpdateId' => $updateId,
            ':UpdateDate' => date('Y-m-d H:i:s'),
        );
        return $stm->execute($prm);
    }
}
