<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_OemBadDebtテーブルへのアダプタ
 */
class TableOemBadDebt
{
	protected $_name = 'T_OemBadDebt';
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
     * 指定締め月および指定OEMの債権明細を取得する
     *
     * @param string $fixedDate 締め月
     * @param Int $oemId OEMID
     * @return ResultInterface
     */
    public function getOemBadDebt($fixedDate, $oemId)
    {
        $sql = " SELECT * FROM T_OemBadDebt WHERE FixedMonth = :FixedMonth AND OemId = :OemId ORDER BY Seq ASC ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':FixedMonth' => $fixedDate,
                ':OemId' => $oemId,
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
        $sql  = " INSERT INTO T_OemBadDebt (OemId, FixedMonth, ProcessDate, SpanFrom, SpanTo, FcSpanFrom, FcSpanTo, ClaimCount, ClaimAmount, ReceiptMoneyCount, ReceiptMoneyAmount, BadDebtCount, BadDebtAmount, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OemId ";
        $sql .= " , :FixedMonth ";
        $sql .= " , :ProcessDate ";
        $sql .= " , :SpanFrom ";
        $sql .= " , :SpanTo ";
        $sql .= " , :FcSpanFrom ";
        $sql .= " , :FcSpanTo ";
        $sql .= " , :ClaimCount ";
        $sql .= " , :ClaimAmount ";
        $sql .= " , :ReceiptMoneyCount ";
        $sql .= " , :ReceiptMoneyAmount ";
        $sql .= " , :BadDebtCount ";
        $sql .= " , :BadDebtAmount ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $data['OemId'],
                ':FixedMonth' => $data['FixedMonth'],
                ':ProcessDate' => $data['ProcessDate'],
                ':SpanFrom' => $data['SpanFrom'],
                ':SpanTo' => $data['SpanTo'],
                ':FcSpanFrom' => $data['FcSpanFrom'],
                ':FcSpanTo' => $data['FcSpanTo'],
                ':ClaimCount' => $data['ClaimCount'],
                ':ClaimAmount' => $data['ClaimAmount'],
                ':ReceiptMoneyCount' => $data['ReceiptMoneyCount'],
                ':ReceiptMoneyAmount' => $data['ReceiptMoneyAmount'],
                ':BadDebtCount' => $data['BadDebtCount'],
                ':BadDebtAmount' => $data['BadDebtAmount'],
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
        $sql = " SELECT * FROM T_OemBadDebt WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_OemBadDebt ";
        $sql .= " SET ";
        $sql .= "     OemId = :OemId ";
        $sql .= " ,   FixedMonth = :FixedMonth ";
        $sql .= " ,   ProcessDate = :ProcessDate ";
        $sql .= " ,   SpanFrom = :SpanFrom ";
        $sql .= " ,   SpanTo = :SpanTo ";
        $sql .= " ,   FcSpanFrom = :FcSpanFrom ";
        $sql .= " ,   FcSpanTo = :FcSpanTo ";
        $sql .= " ,   ClaimCount = :ClaimCount ";
        $sql .= " ,   ClaimAmount = :ClaimAmount ";
        $sql .= " ,   ReceiptMoneyCount = :ReceiptMoneyCount ";
        $sql .= " ,   ReceiptMoneyAmount = :ReceiptMoneyAmount ";
        $sql .= " ,   BadDebtCount = :BadDebtCount ";
        $sql .= " ,   BadDebtAmount = :BadDebtAmount ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':OemId' => $row['OemId'],
                ':FixedMonth' => $row['FixedMonth'],
                ':ProcessDate' => $row['ProcessDate'],
                ':SpanFrom' => $row['SpanFrom'],
                ':SpanTo' => $row['SpanTo'],
                ':FcSpanFrom' => $row['FcSpanFrom'],
                ':FcSpanTo' => $row['FcSpanTo'],
                ':ClaimCount' => $row['ClaimCount'],
                ':ClaimAmount' => $row['ClaimAmount'],
                ':ReceiptMoneyCount' => $row['ReceiptMoneyCount'],
                ':ReceiptMoneyAmount' => $row['ReceiptMoneyAmount'],
                ':BadDebtCount' => $row['BadDebtCount'],
                ':BadDebtAmount' => $row['BadDebtAmount'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定されたレコードを削除する。
	 * @param int $seq
	 */
	public function deleteBySeq($seq)
	{
	    $sql = " DELETE FROM T_OemBadDebt WHERE Seq = :Seq ";
	    $ri = $this->_adapter->query($sql)->execute(array(':Seq' => $seq));
	}

}
