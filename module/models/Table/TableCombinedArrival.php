<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_CombinedArrivalテーブルへのアダプタ
 */
class TableCombinedArrival
{
	protected $_name = 'T_CombinedArrival';
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
	 * 取りまとめ着荷確認データを取得する
	 *
	 * @param int $Seq シーケンス
	 * @return ResultInterface
	 */
	public function find($Seq)
	{
	    $sql = " SELECT * FROM T_CombinedArrival WHERE Seq = :Seq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':Seq' => $Seq,
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
        $sql  = " INSERT INTO T_CombinedArrival (OrderSeq, Deli_JournalNumber, Deli_ConfirmArrivalDate) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :DeliJournalNumber ";
        $sql .= " , :DeliConfirmArrivalDate ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':DeliJournalNumber' => $data['DeliJournalNumber'],
                ':DeliConfirmArrivalDate' => date('Y-m-d H:i:s'),
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}
}
