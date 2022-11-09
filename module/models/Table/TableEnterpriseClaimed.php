<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_EnterpriseClaimedテーブルへのアダプタ
 */
class TableEnterpriseClaimed
{
	protected $_name = 'T_EnterpriseClaimed';
	protected $_primary = array('EnterpriseId', 'FixedMonth');
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
	 * 指定条件（AND）の事業者請求データを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @param boolean $isAsc プライマリキーのオーダー
	 * @return ResultInterface
	 */
	public function findEnterpriseClaimed($conditionArray, $isAsc = false)
	{
        $prm = array();
        $sql  = " SELECT * FROM T_EnterpriseClaimed WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }
        $sql .= " ORDER BY EnterpriseId " . ($isAsc ? "asc" : "desc");

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
	}

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param int $orderSeq インサートする注文ID
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO T_EnterpriseClaimed (EnterpriseId, FixedMonth, ProcessDate, SpanFrom, SpanTo, OrderCount, OrderAmount, SettlementFee, ClaimFee, StampFee, MonthlyFee, CarryOverMonthlyFee, CancelRepaymentAmount, FfTransferFee, AdjustmentAmount, ClaimAmount, PaymentAmount, AdjustmentAmountOnMonthly, OemId, PayBackAmount, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :EnterpriseId ";
        $sql .= " , :FixedMonth ";
        $sql .= " , :ProcessDate ";
        $sql .= " , :SpanFrom ";
        $sql .= " , :SpanTo ";
        $sql .= " , :OrderCount ";
        $sql .= " , :OrderAmount ";
        $sql .= " , :SettlementFee ";
        $sql .= " , :ClaimFee ";
        $sql .= " , :StampFee ";
        $sql .= " , :MonthlyFee ";
        $sql .= " , :CarryOverMonthlyFee ";
        $sql .= " , :CancelRepaymentAmount ";
        $sql .= " , :FfTransferFee ";
        $sql .= " , :AdjustmentAmount ";
        $sql .= " , :ClaimAmount ";
        $sql .= " , :PaymentAmount ";
        $sql .= " , :AdjustmentAmountOnMonthly ";
        $sql .= " , :OemId ";
        $sql .= " , :PayBackAmount ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $data['EnterpriseId'],
                ':FixedMonth' => $data['FixedMonth'],
                ':ProcessDate' => $data['ProcessDate'],
                ':SpanFrom' => $data['SpanFrom'],
                ':SpanTo' => $data['SpanTo'],
                ':OrderCount' => $data['OrderCount'],
                ':OrderAmount' => $data['OrderAmount'],
                ':SettlementFee' => $data['SettlementFee'],
                ':ClaimFee' => $data['ClaimFee'],
                ':StampFee' => $data['StampFee'],
                ':MonthlyFee' => $data['MonthlyFee'],
                ':CarryOverMonthlyFee' => $data['CarryOverMonthlyFee'],
                ':CancelRepaymentAmount' => $data['CancelRepaymentAmount'],
                ':FfTransferFee' => $data['FfTransferFee'],
                ':AdjustmentAmount' => $data['AdjustmentAmount'],
                ':ClaimAmount' => $data['ClaimAmount'],
                ':PaymentAmount' => $data['PaymentAmount'],
                ':AdjustmentAmountOnMonthly' => $data['AdjustmentAmountOnMonthly'],
                ':OemId' => $data['OemId'],
                ':PayBackAmount' => $data['PayBackAmount'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

// Del By Takemasa(NDC) 20141216 Stt 未使用故コメントアウト化
// 	/**
// 	 * 指定されたレコードを更新する。
// 	 *
// 	 * @param array $data 更新内容
// 	 * @param int $seq 更新するSeq
// 	 */
// 	public function saveUpdate($data, $seq)
// 	{
// 		$row = $this->find($seq)->current();
//
// 		foreach ($data as $key => $value)
// 		{
// 			if (isset($row->$key))
// 			{
// 				$row->$key = $value;
// 			}
// 		}
//
// 		$row->save();
// 	}
// Del By Takemasa(NDC) 20141216 End 未使用故コメントアウト化

	/**
	 * 指定締め月の事業者請求データを取得する
	 *
	 * @param string $fixedDate 締め月 'yyyy-MM-dd'書式で通知
	 * @return ResultInterface
	 */
	public function getEnterpriseClaimed($fixedDate)
	{
        $sql  = " SELECT ENT.EnterpriseId, ENT.LoginId, ENT.EnterpriseNameKj, EC.* ";
        $sql .= " FROM   T_EnterpriseClaimed EC ";
        $sql .= " ,      T_Enterprise ENT ";
        $sql .= " WHERE  EC.EnterpriseId = ENT.EnterpriseId ";
        $sql .= " AND    EC.FixedMonth = :fixedDate ";
        $sql .= " ORDER BY EC.EnterpriseId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':fixedDate' => $fixedDate,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定条件（AND）の事業者請求データを削除する。
	 *
	 * @param array $conditionArray 削除条件を格納した連想配列
	 * @param boolean $isAsc プライマリキーのオーダー
	 * @return ResultInterface
	 */
	public function deleteEnterpriseClaimed($conditionArray)
	{
	    $prm = array();
	    $sql  = " DELETE FROM T_EnterpriseClaimed WHERE 1 = 1 ";
	    foreach ($conditionArray as $key => $value) {
	        $sql .= (" AND " . $key . " = :" . $key);
	        $prm += array(':' . $key => $value);
	    }

	    $stm = $this->_adapter->query($sql);

	    return $stm->execute($prm);
	}
}
