<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_ReclaimIndicateテーブルへのアダプタ
 */
class TableReclaimIndicate
{
	protected $_name = 'T_ReclaimIndicate';
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
	 * 再請求指示の指示中の数を取得する。
	 */
	public function getIndicateCount($oseq)
	{
        $sql = " SELECT COUNT(1) AS cnt FROM T_ReclaimIndicate WHERE IndicatedFlg = 0 AND OrderSeq = :OrderSeq AND ValidFlg = 1 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $oseq,
        );
        return (int)$stm->execute($prm)->current()['cnt'];
	}

	/**
	 * 再請求指示の指示中のデータを取得する。
	 *
	 * @param int $seq
	 * @return ResultInterface
	 */
	public function getIndicate($oseq)
	{
	    $sql  = " SELECT * FROM T_ReclaimIndicate WHERE IndicatedFlg = 0 AND OrderSeq = :OrderSeq AND ValidFlg = 1 ";

	    $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $oseq,
        );
	    return $stm->execute($prm);
	}



	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param int $orderSeq インサートする注文ID
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($orderSeq, $data)
	{

	    $data["OrderSeq"] = $orderSeq;

	    $sql  = " INSERT INTO T_ReclaimIndicate (OrderSeq, ClaimCpId, ClaimPattern, DamageDays, DamageBaseDate, DamageInterestAmount, ClaimFee, AdditionalClaimFee, ClaimAmount, ClaimId, IndicateDate, IndicatedFlg, ClaimHistorySeq, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
	    $sql .= "   :OrderSeq ";
	    $sql .= " , :ClaimCpId ";
	    $sql .= " , :ClaimPattern ";
	    $sql .= " , :DamageDays ";
	    $sql .= " , :DamageBaseDate ";
	    $sql .= " , :DamageInterestAmount ";
	    $sql .= " , :ClaimFee ";
	    $sql .= " , :AdditionalClaimFee ";
	    $sql .= " , :ClaimAmount ";
	    $sql .= " , :ClaimId ";
	    $sql .= " , :IndicateDate ";
	    $sql .= " , :IndicatedFlg ";
	    $sql .= " , :ClaimHistorySeq ";
	    $sql .= " , :RegistDate ";
	    $sql .= " , :RegistId ";
	    $sql .= " , :UpdateDate ";
	    $sql .= " , :UpdateId ";
	    $sql .= " , :ValidFlg ";
	    $sql .= " )";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':OrderSeq' => $data['OrderSeq'],
	            ':ClaimCpId' => $data['ClaimCpId'],
	            ':ClaimPattern' => $data['ClaimPattern'],
	            ':DamageDays' => $data['DamageDays'],
	            ':DamageBaseDate' => $data['DamageBaseDate'],
	            ':DamageInterestAmount' => $data['DamageInterestAmount'],
	            ':ClaimFee' => $data['ClaimFee'],
	            ':AdditionalClaimFee' => $data['AdditionalClaimFee'],
	            ':ClaimAmount' => $data['ClaimAmount'],
	            ':ClaimId' => $data['ClaimId'],
	            ':IndicateDate' => $data['IndicateDate'],
	            ':IndicatedFlg' => $data['IndicatedFlg'],
	            ':ClaimHistorySeq' => $data['ClaimHistorySeq'],
	            ':RegistDate' => date('Y-m-d H:i:s'),
	            ':RegistId' => $data['RegistId'],
	            ':UpdateDate' => date('Y-m-d H:i:s'),
	            ':UpdateId' => $data['UpdateId'],
	            ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
	    );

	    $ri = $stm->execute($prm);
	    $seq = $ri->getGeneratedValue();// 新規登録したPK値

	    return $seq;// 新規登録したPK値を戻す
	}

	/**
	 * 指定注文を印刷指示処理済に更新する
	 *
	 *
	 * @param $orderSeq 注文Seq
	 * @param $userId ﾕｰｻﾞｰID
	 * @param $historySeq 請求履歴SEQ
	 */
	public function updateIndicated($orderSeq, $userId, $historySeq)
	{
	    $sql  = " UPDATE T_ReclaimIndicate ";
	    $sql .= " SET    IndicatedFlg = 1 ";
	    $sql .= " ,      ClaimHistorySeq = :ClaimHistorySeq ";
	    $sql .= " ,      UpdateId = :UpdateId ";
	    $sql .= " ,      UpdateDate = :UpdateDate ";
	    $sql .= " WHERE  OrderSeq = :OrderSeq ";
        $sql .= " AND    IndicatedFlg = 0 ";
        $sql .= " AND    ValidFlg = 1 ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':OrderSeq' => $orderSeq,
	            ':ClaimHistorySeq' => $historySeq,
	            ':UpdateId' => $userId,
	            ':UpdateDate' => date('Y-m-d H:i:s')
	    );

	    return $stm->execute($prm);
	}

}
