<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_OemEnterpriseClaimedテーブルへのアダプタ
 */
class TableOemDeliMethodList
{
	/**
	 * テーブル名
	 *
	 * @access protected
	 * @var string
	 */
	protected $_name = 'T_OemDeliveryMethodList';

	/**
	 * プライマリーキーのカラム名
	 *
	 * @access protected
	 * @var array
	 */
	protected $_primary = array('DeliMethodId', 'OemId');

	/**
	 * テーブルのシーケンス設定。このテーブルは自然キーを使用する
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $_sequence = false;

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
	 * 指定OEMID向けに定義されている配送方法IDのリストを取得する。
	 * 戻り値の配列は、定義順に並んだ配送方法IDが格納される
	 *
	 * @param int $oemId OEMID
	 * @return array
	 */
	public function findDeliMethodIds($oemId)
	{
		$sql = <<<EOQ
SELECT md.DeliMethodId
FROM   T_OemDeliveryMethodList ol INNER JOIN M_DeliveryMethod md ON (ol.DeliMethodId = md.DeliMethodId)
WHERE  ol.OemId = :OemId
ORDER BY ol.OemId, ol.ListNumber
EOQ;
        $result = array();
        $ri = $this->_adapter->query($sql)->execute(array(':OemId' => $oemId));
        foreach ($ri as $row) {
            $result[] = $row['DeliMethodId'];
        }
        return $result;
	}

	/**
	 * 指定OEMIDの情報を取得する
	 *
	 * @param int $oemId OEMID
	 * @param int $deliMethodId
	 * @return ResultInterface
	 */
	public function findDeliMethodId($oemId, $deliMethodId)
	{
        $sql =<<<EOQ
SELECT md.*
FROM   T_OemDeliveryMethodList ol INNER JOIN M_DeliveryMethod md ON (ol.DeliMethodId = md.DeliMethodId)
WHERE  ol.OemId = :OemId
AND    ol.DeliMethodId = :DeliMethodId
ORDER BY ol.OemId
EOQ;
        return $this->_adapter->query($sql)->execute(array(':OemId' => $oemId, ':DeliMethodId' => $deliMethodId));
	}

	/**
	 * 指定OEM IDの行をすべて削除する
	 *
	 * @param int $oemId OEM ID
	 * @return int 削除された行数
	 */
	public function deleteByOemId($oemId)
	{
        $sql = " DELETE FROM T_OemDeliveryMethodList WHERE OemId = :OemId ";
        $ri = $this->_adapter->query($sql)->execute(array(':OemId' => $oemId));
        return $ri->getAffectedRows();
	}

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO T_OemDeliveryMethodList (DeliMethodId, OemId, ListNumber, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :DeliMethodId ";
        $sql .= " , :OemId ";
        $sql .= " , :ListNumber ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':DeliMethodId' => isset($data['DeliMethodId']) ? $data['DeliMethodId'] : 0,
                ':OemId' => isset($data['OemId']) ? $data['OemId'] : 0,
                ':ListNumber' => $data['ListNumber'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

}
