<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class TablePayingAndSales
{
	/**
	 * 請求手数料を示す定数
	 *
	 */
	const CLAIM_FEE_DEFAULT = 200;

	/**
	 * テーブル名
	 *
	 * @var string
	 */
	protected $_name = 'T_PayingAndSales';

	/**
	 * プライマリキー
	 *
	 * @var string
	 */
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
	 * 新しいPayingAndSalesの行クラスを作成する。作成された行はまだテーブルに属さないため、
	 * 値を適切に設定して{@link Zend_Db_Table_Row_Abstract::save}を実行する必要がある
	 *
	 * @param int $orderSeq 注文シーケンス
	 * @param int $useAmount 注文金額
	 * @param int $settlementFeeRate 立替手数料率。パーセント値の数値
	 * @param int $claimFee 請求手数料
	 * @param array|null $data 初期データの連想配列
	 * @return array
	 */
	public function newRow($orderSeq, $useAmount, $settlementFeeRate, $claimFee, $data = array()) {
	    if( ! is_array( $data ) ) $data = array();

		// 注文シーケンスが不正の場合は例外をスロー
		if( ((int)$orderSeq < 1 ) ) {
			throw new \Exception( 'invalid order sequence' );
		}

		// 立替額と手数料を計算して連想配列を整形
		$data = array_merge( $data, self::calcFeeAndAmount( (int)$useAmount, $settlementFeeRate, (int)$claimFee ) );
		// 注文IDとデータ発生日、およびフラグフィールドの初期値を連想配列に適用
		$data = array_merge( $data, array(
			'OrderSeq' => $orderSeq,
			'OccDate' => date('Y-m-d'),
			'ClearConditionForCharge' => 0,
			'ChargeDecisionFlg' => 0,
			'CancelFlg' => 0
		) );

		// Mod By Takemasa(NDC) 20141226 Stt 関数戻り値の変更(row作成は行わない)
		//// 行を返す
		//return $this->createRow( $data );
		// arrayを返す
		return $data;
		// Mod By Takemasa(NDC) 20141226 End 関数戻り値の変更(row作成は行わない)
	}

	/**
	 * 注文金額と立替手数料率および請求手数料額を指定して、請求金額データを計算する。
	 * 戻り値はT_PayingAndSalesのUseAmount、AppSettlementFeeRate、ClaimFee、およびChargeAmount
	 * の各カラムに一致する連想配列として返される
	 *
	 * @param int $useAmount 注文金額
	 * @param int $settlementFeeRate 立替手数料率。パーセント値の数値
	 * @param int $claimFee 請求手数料額。省略時は{@link Table_PayingAndSales::CLAIM_FEE_DEFAULT}の値が採用される
	 * @return array
	 */
	public static function calcFeeAndAmount($useAmount, $settlementFeeRate, $claimFee = self::CLAIM_FEE_DEFAULT)
	{
	    if( ! is_int( $claimFee ) ) $claimFee = self::CLAIM_FEE_DEFAULT;

        $result = array(
        	'UseAmount' => ((int)$useAmount),
        	'AppSettlementFeeRate' => $settlementFeeRate,
        	'SettlementFee' => floor( strval( ( (int)$useAmount * $settlementFeeRate ) / 100 ) ),
        	'ClaimFee' => ((int)$claimFee)
        );
        $result['ChargeAmount'] =
        	$result['UseAmount'] - ( $result['SettlementFee'] + $result['ClaimFee'] );

        return $result;
	}

	/**
	 * 指定注文Seqデータの立替条件をクリアした状態にする。
	 *
	 * @param int $oseq 注文Seq
	 * @param int $cad 着荷確認日識別　（1：本日　2：昨日　3：一昨日）
	 * @param $opId 担当者
	 */
	public function clearConditionForCharge($oseq, $cad, $opId)
	{
        $sql = " SELECT * FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $oseq,
        );

        $cnt = (int)$stm->execute($prm)->count();

        if (!($cnt > 0)) { throw new \Exception(sprintf('paying-and-sales record not found !!! (oseq = %s)', $oseq)); }

        // 立替条件クリア済みの場合、更新しない
        $pas = $stm->execute($prm)->current();
        if ($pas['ClearConditionForCharge'] != 0) {
            return 0;
        }

        // 着荷確認日が何日前でも大丈夫にする。 2009.01.07 kashira
        $backDays = 1 - $cad;
        if ($backDays < 0) {
            $ClearConditionDate = date("Y-m-d", strtotime($backDays . " day"));
        }
        else {
            $ClearConditionDate = date("Y-m-d");
        }

        // UPDATE
        $sql  = " UPDATE T_PayingAndSales ";
        $sql .= " SET ";
        $sql .= "     ClearConditionForCharge = 1";
        $sql .= " ,   ClearConditionDate      = :ClearConditionDate ";
        $sql .= " ,   UpdateDate              = :UpdateDate ";
        $sql .= " ,   UpdateId                = :UpdateId ";
        $sql .= " WHERE OrderSeq              = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ClearConditionDate' => $ClearConditionDate,
                ':OrderSeq' => $oseq,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $opId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定注文Seqデータの立替条件をクリアした状態にする。
	 *
	 * @param int $oseq 注文Seq
	 * @param date $cadate 着荷確認日識別　（1：本日　2：昨日　3：一昨日）
	 * @param $opId 担当者
	 */
	public function clearConditionForCharge2($oseq, $cadate, $opId)
	{
	    $sql = " SELECT * FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':OrderSeq' => $oseq,
	    );

	    $cnt = (int)$stm->execute($prm)->count();

	    if (!($cnt > 0)) { throw new \Exception(sprintf('paying-and-sales record not found !!! (oseq = %s)', $oseq)); }

	    // 立替条件クリア済みの場合、更新しない
	    $pas = $stm->execute($prm)->current();
	    if ($pas['ClearConditionForCharge'] != 0) {
	        return 0;
	    }

	    // UPDATE
	    $sql  = " UPDATE T_PayingAndSales ";
	    $sql .= " SET ";
	    $sql .= "     ClearConditionForCharge = 1";
	    $sql .= " ,   ClearConditionDate      = :ClearConditionDate ";
	    $sql .= " ,   UpdateDate              = :UpdateDate ";
	    $sql .= " ,   UpdateId                = :UpdateId ";
	    $sql .= " WHERE OrderSeq              = :OrderSeq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':ClearConditionDate' => $cadate,
	            ':OrderSeq' => $oseq,
	            ':UpdateDate' => date('Y-m-d H:i:s'),
	            ':UpdateId' => $opId,
	    );

	    return $stm->execute($prm);
	}


	/**
	 * 指定条件（AND）の立替・売上管理データを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @param boolean $isAsc プライマリキーのオーダー
	 * @return ResultInterface
	 */
	public function findPayingAndSales($conditionArray, $isAsc = false)
	{
        $prm = array();
        $sql  = " SELECT * FROM T_PayingAndSales WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }
        $sql .= " ORDER BY Seq " . ($isAsc ? "asc" : "desc");

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
	}

	/**
	 * 指定注文SEQの立替・売上管理データをキャンセル中にする。
	 *
	 * @param int $oseq 注文SEQ
	 * @param $opId 担当者
	 */
	public function toCanceling($oseq, $opId)
	{
		$this->setCancelFlg($oseq, 1, $opId);
	}

	/**
	 * 指定注文SEQの立替・売上管理データをキャンセルにする。
	 *
	 * @param int $oseq 注文SEQ
	 * @param $opId 担当者
	 */
	public function toCanceled($oseq, $opId)
	{
		$this->setCancelFlg($oseq, 2, $opId);
	}

	/**
	 * 指定注文SEQの立替・売上管理データのキャンセルフラッグを設定する。
	 *
	 * @param int $oseq 注文SEQ
	 * @param int $flg キャンセルフラッグ
	 * @param $opId 担当者
	 */
	public function setCancelFlg($oseq, $flg, $opId)
	{
        $sql = " UPDATE T_PayingAndSales SET CancelFlg = :CancelFlg, UpdateDate = :UpdateDate, UpdateId = :UpdateId WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CancelFlg' => $flg,
                ':OrderSeq' => $oseq,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $opId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 立替確定済みにする
	 *
	 * @param int $seq 管理Seq
	 * @param string $date 立替確定日 'yyyy-MM-dd'書式で通知
	 * @param int $payingControlSeq 立替振込管理Seq
	 * @param $opId 担当者
	 */
	public function fixation($seq, $date, $payingControlSeq, $opId)
	{
        $sql  = " UPDATE T_PayingAndSales ";
        $sql .= " SET ";
        $sql .= "     ChargeDecisionFlg  = 1";
        $sql .= " ,   ChargeDecisionDate = :ChargeDecisionDate ";
        $sql .= " ,   PayingControlSeq   = :PayingControlSeq ";
        $sql .= " ,   UpdateDate         = :UpdateDate ";
        $sql .= " ,   UpdateId           = :UpdateId ";
        $sql .= " WHERE Seq              = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ChargeDecisionDate' => $date,
                ':PayingControlSeq' => $payingControlSeq,
                ':Seq' => $seq,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $opId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定日付の売上金額を取得する。
	 *
	 * @param string $date 日付 'yyyy-MM-dd'書式で通知
	 * @return int 売上金額
	 */
	public function getUriage($date)
	{
        $sql = " SELECT IFNULL(SUM(SettlementFee + ClaimFee), 0) AS Uriage FROM T_PayingAndSales WHERE OccDate = :OccDate AND CancelFlg = 0";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OccDate' => $date,
        );

        return (int)$stm->execute($prm)->current()['Uriage'];
	}

	/**
	 * 本日の売上金額を取得する。
	 *
	 * @return int 売上金額
	 */
	public function getUriageToday()
	{
        return $this->getUriage(date('Y-m-d'));
	}

	/**
	 * 昨日の売上金額を取得する。
	 *
	 * @return int 売上金額
	 */
	public function getUriageYesterday()
	{
        return $this->getUriage(date('Y-m-d', strtotime('-1 day')));
	}

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO T_PayingAndSales (OrderSeq, OccDate, UseAmount, AppSettlementFeeRate, SettlementFee, ClaimFee, ChargeAmount, ClearConditionForCharge, ClearConditionDate, ChargeDecisionFlg, ChargeDecisionDate, CancelFlg, PayingControlSeq, SpecialPayingDate, PayingControlStatus, AgencyFeeAddUpFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :OccDate ";
        $sql .= " , :UseAmount ";
        $sql .= " , :AppSettlementFeeRate ";
        $sql .= " , :SettlementFee ";
        $sql .= " , :ClaimFee ";
        $sql .= " , :ChargeAmount ";
        $sql .= " , :ClearConditionForCharge ";
        $sql .= " , :ClearConditionDate ";
        $sql .= " , :ChargeDecisionFlg ";
        $sql .= " , :ChargeDecisionDate ";
        $sql .= " , :CancelFlg ";
        $sql .= " , :PayingControlSeq ";
        $sql .= " , :SpecialPayingDate ";
        $sql .= " , :PayingControlStatus ";
        $sql .= " , :AgencyFeeAddUpFlg ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':OccDate' => $data['OccDate'],
                ':UseAmount' => $data['UseAmount'],
                ':AppSettlementFeeRate' => $data['AppSettlementFeeRate'],
                ':SettlementFee' => $data['SettlementFee'],
                ':ClaimFee' => $data['ClaimFee'],
                ':ChargeAmount' => $data['ChargeAmount'],
                ':ClearConditionForCharge' => $data['ClearConditionForCharge'],
                ':ClearConditionDate' => $data['ClearConditionDate'],
                ':ChargeDecisionFlg' => $data['ChargeDecisionFlg'],
                ':ChargeDecisionDate' => $data['ChargeDecisionDate'],
                ':CancelFlg' => $data['CancelFlg'],
                ':PayingControlSeq' => $data['PayingControlSeq'],
                ':SpecialPayingDate' => $data['SpecialPayingDate'],
                ':PayingControlStatus' => isset($data['PayingControlStatus']) ? $data['PayingControlStatus'] : 0,
                ':AgencyFeeAddUpFlg' => isset($data['AgencyFeeAddUpFlg']) ? $data['AgencyFeeAddUpFlg'] : 0,
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

	/**
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param int $seq 更新するSeq
	 */
	public function saveUpdate($data, $seq)
	{
	    $sql = " SELECT * FROM T_PayingAndSales WHERE Seq = :Seq ";

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

	    $sql  = " UPDATE T_PayingAndSales ";
	    $sql .= " SET ";
	    $sql .= "     OrderSeq = :OrderSeq ";
	    $sql .= " ,   OccDate = :OccDate ";
	    $sql .= " ,   UseAmount = :UseAmount ";
	    $sql .= " ,   AppSettlementFeeRate = :AppSettlementFeeRate ";
	    $sql .= " ,   SettlementFee = :SettlementFee ";
	    $sql .= " ,   ClaimFee = :ClaimFee ";
	    $sql .= " ,   ChargeAmount = :ChargeAmount ";
	    $sql .= " ,   ClearConditionForCharge = :ClearConditionForCharge ";
	    $sql .= " ,   ClearConditionDate = :ClearConditionDate ";
	    $sql .= " ,   ChargeDecisionFlg = :ChargeDecisionFlg ";
	    $sql .= " ,   ChargeDecisionDate = :ChargeDecisionDate ";
	    $sql .= " ,   CancelFlg = :CancelFlg ";
	    $sql .= " ,   PayingControlSeq = :PayingControlSeq ";
	    $sql .= " ,   SpecialPayingDate = :SpecialPayingDate ";
	    $sql .= " ,   PayingControlStatus = :PayingControlStatus ";
	    $sql .= " ,   AgencyFeeAddUpFlg = :AgencyFeeAddUpFlg ";
	    $sql .= " ,   RegistDate = :RegistDate ";
	    $sql .= " ,   RegistId = :RegistId ";
	    $sql .= " ,   UpdateDate = :UpdateDate ";
	    $sql .= " ,   UpdateId = :UpdateId ";
	    $sql .= " ,   ValidFlg = :ValidFlg ";
	    $sql .= " WHERE Seq = :Seq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':Seq' => $seq,
	            ':OrderSeq' => $row['OrderSeq'],
	            ':OccDate' => $row['OccDate'],
	            ':UseAmount' => $row['UseAmount'],
	            ':AppSettlementFeeRate' => $row['AppSettlementFeeRate'],
	            ':SettlementFee' => $row['SettlementFee'],
	            ':ClaimFee' => $row['ClaimFee'],
	            ':ChargeAmount' => $row['ChargeAmount'],
	            ':ClearConditionForCharge' => $row['ClearConditionForCharge'],
	            ':ClearConditionDate' => $row['ClearConditionDate'],
	            ':ChargeDecisionFlg' => $row['ChargeDecisionFlg'],
	            ':ChargeDecisionDate' => $row['ChargeDecisionDate'],
	            ':CancelFlg' => $row['CancelFlg'],
	            ':PayingControlSeq' => $row['PayingControlSeq'],
	            ':SpecialPayingDate' => $row['SpecialPayingDate'],
	            ':PayingControlStatus' => $row['PayingControlStatus'],
	            ':AgencyFeeAddUpFlg' => $row['AgencyFeeAddUpFlg'],
	            ':RegistDate' => $row['RegistDate'],
	            ':RegistId' => $row['RegistId'],
	            ':UpdateDate' => date('Y-m-d H:i:s'),
	            ':UpdateId' => $row['UpdateId'],
	            ':ValidFlg' => $row['ValidFlg'],
	    );

	    return $stm->execute($prm);
	}

	/**
	 * 指定された条件でレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param array $conditionArray
	 */
	public function saveUpdateWhere($data, $conditionArray)
	{
	    $prm = array();
	    $sql  = " SELECT * FROM T_PayingAndSales WHERE 1 = 1 ";
	    foreach ($conditionArray as $key => $value) {
	        $sql .= (" AND " . $key . " = :" . $key);
	        $prm += array(':' . $key => $value);
	    }

	    $stm = $this->_adapter->query($sql);

	    $ri = $stm->execute($prm);

	    foreach ($ri AS $row) {
	        foreach ($data as $key => $value) {
	            if (array_key_exists($key, $row)) {
	                $row[$key] = $value;
	            }
	        }

	        // 指定されたレコードを更新する
	        $this->saveUpdate($row, $row['Seq']);
	    }
	}

	/**
	 * 臨時加盟店立替確定したデータを本締めする
	 *
	 * @param int $payingControlSeq 立替振替管理Seq
	 * @param int $userId ユーザID
	 * @param int $enterpriseId 加盟店ID
	 * @param string $payingControlDate 立替締め日
	 */
	public function updateSpecialPaying($payingControlSeq, $userId, $enterpriseId, $payingControlDate)
	{
	    $sql  = " UPDATE T_PayingAndSales PAS ";
	    $sql .= " SET ";
	    $sql .= "     PAS.ChargeDecisionFlg = :ChargeDecisionFlg ";
	    $sql .= " ,   PAS.ChargeDecisionDate = :ChargeDecisionDate ";
	    $sql .= " ,   PAS.PayingControlStatus = :PayingControlStatus ";
	    $sql .= " ,   PAS.PayingControlSeq = :PayingControlSeq ";
	    $sql .= " ,   PAS.UpdateDate = :UpdateDate ";
	    $sql .= " ,   PAS.UpdateId = :UpdateId ";
	    $sql .= "  WHERE EXISTS (SELECT O.OrderSeq ";
	    $sql .= "                  FROM T_Order O ";
	    $sql .= "                 WHERE O.OrderSeq = PAS.OrderSeq ";
	    $sql .= "                   AND O.EnterpriseId = :EnterpriseId ";
	    $sql .= "               ) ";
	    $sql .= "    AND PAS.ClearConditionForCharge = 1 ";
	    $sql .= "    AND (   PAS.PayingControlStatus = 0 ";
	    $sql .= "         OR PAS.PayingControlStatus IS NULL ";
	    $sql .= "        ) ";
	    $sql .= "    AND PAS.CancelFlg = 0 ";
	    $sql .= "    AND PAS.SpecialPayingDate IS NOT NULL ";
	    $sql .= "    AND PAS.ClearConditionDate <= :ClearConditionDate ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':ChargeDecisionFlg' => 1,
	            ':ChargeDecisionDate' => date('Y-m-d'),
	            ':PayingControlStatus' => 1,
	            ':PayingControlSeq' => $payingControlSeq,
	            ':UpdateDate' => date('Y-m-d H:i:s'),
	            ':UpdateId' => $userId,
	            ':EnterpriseId' => $enterpriseId,
	            ':ClearConditionDate' => date("Y-m-d", strtotime($payingControlDate)),
	    );

	    return $stm->execute($prm);
	}

	/**
	 * 既に[立替条件クリアフラグ]が[1：条件をクリアしている]か？
	 *
	 * @param int $oseq 注文Seq
	 * @return boolean true:条件をクリアしている／false:条件をクリアしていない
	 * @see 該当のレコードが存在しないときはfalseを戻す
	 */
	public function IsAlreadyClearCondition($oseq)
	{
        $row = $this->_adapter->query(" SELECT ClearConditionForCharge FROM T_PayingAndSales WHERE OrderSeq = :OrderSeq "
	       )->execute(array(':OrderSeq' => $oseq))->current();

        if (!$row) { return false; }

        return ((int)$row['ClearConditionForCharge'] == 1) ? true : false;
	}
}
?>