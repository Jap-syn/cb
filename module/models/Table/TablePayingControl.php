<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Coral\Base\BaseGeneralUtils;

/**
 * T_PayingControlテーブルへのアダプタ
 */
class TablePayingControl
{
	protected $_name = 'T_PayingControl';
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
	 * 指定条件（AND）の立替振込管理データを取得する。
	 *
	 * @param array $conditionArray 検索条件を格納した連想配列
	 * @param boolean $isAsc プライマリキーのオーダー
	 * @return ResultInterface
	 */
	public function findPayingControl($conditionArray, $isAsc = false)
	{
        $prm = array();
        $sql  = " SELECT * FROM T_PayingControl WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }
        $sql .= " ORDER BY Seq " . ($isAsc ? "asc" : "desc");

        $stm = $this->_adapter->query($sql);

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
        $sql  = " INSERT INTO T_PayingControl (EnterpriseId, FixedDate, DecisionDate, ExecDate, ExecFlg, ExecCpId, ChargeCount, ChargeAmount, CancelCount, CalcelAmount, StampFeeCount, StampFeeTotal, MonthlyFee, DecisionPayment, AddUpFlg, AddUpFixedMonth, SettlementFee, ClaimFee, CarryOver, TransferCommission, ExecScheduleDate, AdjustmentAmount, PayBackTC, CarryOverTC, OemId, OemClaimedSeq, OemClaimedAddUpFlg, ChargeMonthlyFeeFlg, PayBackCount, PayBackAmount, PayingControlStatus, SpecialPayingFlg, PayingDataDownloadFlg, PayingDataFilePath, ClaimPdfFilePath, AdjustmentDecisionFlg, AdjustmentDecisionDate, AdjustmentCount, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :EnterpriseId ";
        $sql .= " , :FixedDate ";
        $sql .= " , :DecisionDate ";
        $sql .= " , :ExecDate ";
        $sql .= " , :ExecFlg ";
        $sql .= " , :ExecCpId ";
        $sql .= " , :ChargeCount ";
        $sql .= " , :ChargeAmount ";
        $sql .= " , :CancelCount ";
        $sql .= " , :CalcelAmount ";
        $sql .= " , :StampFeeCount ";
        $sql .= " , :StampFeeTotal ";
        $sql .= " , :MonthlyFee ";
        $sql .= " , :DecisionPayment ";
        $sql .= " , :AddUpFlg ";
        $sql .= " , :AddUpFixedMonth ";
        $sql .= " , :SettlementFee ";
        $sql .= " , :ClaimFee ";
        $sql .= " , :CarryOver ";
        $sql .= " , :TransferCommission ";
        $sql .= " , :ExecScheduleDate ";
        $sql .= " , :AdjustmentAmount ";
        $sql .= " , :PayBackTC ";
        $sql .= " , :CarryOverTC ";
        $sql .= " , :OemId ";
        $sql .= " , :OemClaimedSeq ";
        $sql .= " , :OemClaimedAddUpFlg ";
        $sql .= " , :ChargeMonthlyFeeFlg ";
        $sql .= " , :PayBackCount ";
        $sql .= " , :PayBackAmount ";
        $sql .= " , :PayingControlStatus ";
        $sql .= " , :SpecialPayingFlg ";
        $sql .= " , :PayingDataDownloadFlg ";
        $sql .= " , :PayingDataFilePath ";
        $sql .= " , :ClaimPdfFilePath ";
        $sql .= " , :AdjustmentDecisionFlg ";
        $sql .= " , :AdjustmentDecisionDate ";
        $sql .= " , :AdjustmentCount ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $data['EnterpriseId'],
                ':FixedDate' => $data['FixedDate'],
                ':DecisionDate' => $data['DecisionDate'],
                ':ExecDate' => $data['ExecDate'],
                ':ExecFlg' => $data['ExecFlg'],
                ':ExecCpId' => $data['ExecCpId'],
                ':ChargeCount' => $data['ChargeCount'],
                ':ChargeAmount' => $data['ChargeAmount'],
                ':CancelCount' => $data['CancelCount'],
                ':CalcelAmount' => $data['CalcelAmount'],
                ':StampFeeCount' => $data['StampFeeCount'],
                ':StampFeeTotal' => $data['StampFeeTotal'],
                ':MonthlyFee' => $data['MonthlyFee'],
                ':DecisionPayment' => $data['DecisionPayment'],
                ':AddUpFlg' => $data['AddUpFlg'],
                ':AddUpFixedMonth' => $data['AddUpFixedMonth'],
                ':SettlementFee' => $data['SettlementFee'],
                ':ClaimFee' => $data['ClaimFee'],
                ':CarryOver' => $data['CarryOver'],
                ':TransferCommission' => $data['TransferCommission'],
                ':ExecScheduleDate' => $data['ExecScheduleDate'],
                ':AdjustmentAmount' => $data['AdjustmentAmount'],
                ':PayBackTC' => isset($data['PayBackTC']) ? $data['PayBackTC'] : 0,
                ':CarryOverTC' => isset($data['CarryOverTC']) ? $data['CarryOverTC'] : 0,
                ':OemId' => $data['OemId'],
                ':OemClaimedSeq' => $data['OemClaimedSeq'],
                ':OemClaimedAddUpFlg' => $data['OemClaimedAddUpFlg'],
                ':ChargeMonthlyFeeFlg' => $data['ChargeMonthlyFeeFlg'],
                ':PayBackCount' => isset($data['PayBackCount']) ? $data['PayBackCount'] : 0,
                ':PayBackAmount' => isset($data['PayBackAmount']) ? $data['PayBackAmount'] : 0,
                ':PayingControlStatus' => isset($data['PayingControlStatus']) ? $data['PayingControlStatus'] : 0,
                ':SpecialPayingFlg' => isset($data['SpecialPayingFlg']) ? $data['SpecialPayingFlg'] : 0,
                ':PayingDataDownloadFlg' => isset($data['PayingDataDownloadFlg']) ? $data['PayingDataDownloadFlg'] : 0,
                ':PayingDataFilePath' => $data['PayingDataFilePath'],
                ':ClaimPdfFilePath' => $data['ClaimPdfFilePath'],
                ':AdjustmentDecisionFlg' => isset($data['AdjustmentDecisionFlg']) ? $data['AdjustmentDecisionFlg'] : 0,
                ':AdjustmentDecisionDate' => $data['AdjustmentDecisionDate'],
                ':AdjustmentCount' => isset($data['AdjustmentCount']) ? $data['AdjustmentCount'] : 0,
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
        $sql = " SELECT * FROM T_PayingControl WHERE Seq = :Seq ";

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

        $sql  = " UPDATE T_PayingControl ";
        $sql .= " SET ";
        $sql .= "     EnterpriseId = :EnterpriseId ";
        $sql .= " ,   FixedDate = :FixedDate ";
        $sql .= " ,   DecisionDate = :DecisionDate ";
        $sql .= " ,   ExecDate = :ExecDate ";
        $sql .= " ,   ExecFlg = :ExecFlg ";
        $sql .= " ,   ExecCpId = :ExecCpId ";
        $sql .= " ,   ChargeCount = :ChargeCount ";
        $sql .= " ,   ChargeAmount = :ChargeAmount ";
        $sql .= " ,   CancelCount = :CancelCount ";
        $sql .= " ,   CalcelAmount = :CalcelAmount ";
        $sql .= " ,   StampFeeCount = :StampFeeCount ";
        $sql .= " ,   StampFeeTotal = :StampFeeTotal ";
        $sql .= " ,   MonthlyFee = :MonthlyFee ";
        $sql .= " ,   DecisionPayment = :DecisionPayment ";
        $sql .= " ,   AddUpFlg = :AddUpFlg ";
        $sql .= " ,   AddUpFixedMonth = :AddUpFixedMonth ";
        $sql .= " ,   SettlementFee = :SettlementFee ";
        $sql .= " ,   ClaimFee = :ClaimFee ";
        $sql .= " ,   CarryOver = :CarryOver ";
        $sql .= " ,   TransferCommission = :TransferCommission ";
        $sql .= " ,   ExecScheduleDate = :ExecScheduleDate ";
        $sql .= " ,   AdjustmentAmount = :AdjustmentAmount ";
        $sql .= " ,   PayBackTC = :PayBackTC ";
        $sql .= " ,   CarryOverTC = :CarryOverTC ";
        $sql .= " ,   OemId = :OemId ";
        $sql .= " ,   OemClaimedSeq = :OemClaimedSeq ";
        $sql .= " ,   OemClaimedAddUpFlg = :OemClaimedAddUpFlg ";
        $sql .= " ,   ChargeMonthlyFeeFlg = :ChargeMonthlyFeeFlg ";
        $sql .= " ,   PayBackCount = :PayBackCount ";
        $sql .= " ,   PayBackAmount = :PayBackAmount ";
        $sql .= " ,   PayingControlStatus = :PayingControlStatus ";
        $sql .= " ,   SpecialPayingFlg = :SpecialPayingFlg ";
        $sql .= " ,   PayingDataDownloadFlg = :PayingDataDownloadFlg ";
        $sql .= " ,   PayingDataFilePath = :PayingDataFilePath ";
        $sql .= " ,   ClaimPdfFilePath = :ClaimPdfFilePath ";
        $sql .= " ,   AdjustmentDecisionFlg = :AdjustmentDecisionFlg ";
        $sql .= " ,   AdjustmentDecisionDate = :AdjustmentDecisionDate ";
        $sql .= " ,   AdjustmentCount = :AdjustmentCount ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':EnterpriseId' => $row['EnterpriseId'],
                ':FixedDate' => $row['FixedDate'],
                ':DecisionDate' => $row['DecisionDate'],
                ':ExecDate' => $row['ExecDate'],
                ':ExecFlg' => $row['ExecFlg'],
                ':ExecCpId' => $row['ExecCpId'],
                ':ChargeCount' => $row['ChargeCount'],
                ':ChargeAmount' => $row['ChargeAmount'],
                ':CancelCount' => $row['CancelCount'],
                ':CalcelAmount' => $row['CalcelAmount'],
                ':StampFeeCount' => $row['StampFeeCount'],
                ':StampFeeTotal' => $row['StampFeeTotal'],
                ':MonthlyFee' => $row['MonthlyFee'],
                ':DecisionPayment' => $row['DecisionPayment'],
                ':AddUpFlg' => $row['AddUpFlg'],
                ':AddUpFixedMonth' => $row['AddUpFixedMonth'],
                ':SettlementFee' => $row['SettlementFee'],
                ':ClaimFee' => $row['ClaimFee'],
                ':CarryOver' => $row['CarryOver'],
                ':TransferCommission' => $row['TransferCommission'],
                ':ExecScheduleDate' => $row['ExecScheduleDate'],
                ':AdjustmentAmount' => $row['AdjustmentAmount'],
                ':PayBackTC' => $row['PayBackTC'],
                ':CarryOverTC' => $row['CarryOverTC'],
                ':OemId' => $row['OemId'],
                ':OemClaimedSeq' => $row['OemClaimedSeq'],
                ':OemClaimedAddUpFlg' => $row['OemClaimedAddUpFlg'],
                ':ChargeMonthlyFeeFlg' => $row['ChargeMonthlyFeeFlg'],
                ':PayBackCount' => $row['PayBackCount'],
                ':PayBackAmount' => $row['PayBackAmount'],
                ':PayingControlStatus' => $row['PayingControlStatus'],
                ':SpecialPayingFlg' => $row['SpecialPayingFlg'],
                ':PayingDataDownloadFlg' => $row['PayingDataDownloadFlg'],
                ':PayingDataFilePath' => $row['PayingDataFilePath'],
                ':ClaimPdfFilePath' => $row['ClaimPdfFilePath'],
                ':AdjustmentDecisionFlg' => $row['AdjustmentDecisionFlg'],
                ':AdjustmentDecisionDate' => $row['AdjustmentDecisionDate'],
                ':AdjustmentCount' => $row['AdjustmentCount'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定日付を含む月の最初の立替か否かを取得する。
	 *
	 * @param int $enterpriseId 事業者ID
	 * @param string $date 日付（立替締め日等） 'yyyy-MM-dd'書式で通知
	 * @return boolean
	 */
	public function isFirstOfMonth($enterpriseId, $date)
	{
	    /*
		 * 引数で渡される日付は立替締め日。
		 * したがってその立替締め日が計上されるべき月度の最初の立替か否かを判断する。
		 */

		$cnt = $this->findPayingControl(
			array(
				'EnterpriseId' => $enterpriseId,
				'AddUpFixedMonth' => date('Y-m-01', $date)
			)
		)->count();

		if ($cnt > 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * 繰越すべき立替金額（マイナス）を取得する。
	 *
	 * @param int $enterpriseId 事業者ID
	 * @return int 繰越金額
	 */
	public function getCarryOverAmount($enterpriseId)
	{
        $sql = " SELECT SUM(DecisionPayment) AS CarryOverAmount FROM T_PayingControl WHERE EnterpriseId = :EnterpriseId AND ExecFlg = 10 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
        );

        return (int)$stm->execute($prm)->current()['CarryOverAmount'];
	}

	/**
	 * 指定日付の前月度の繰越額（マイナス）を取得する。
	 *
	 * @param int $enterpriseId 事業者ID
	 * @param string $date 日付 'yyyy-MM-dd'書式で通知
	 * @return int 繰越金額
	 */
	public function getCarryOverLastMonth($enterpriseId, $date)
	{
        $d = date('Y-m-01', $date);

        $sql = "
        	SELECT
        	    Seq,
        	    EnterpriseId,
        	    DecisionPayment
        	FROM
        	    T_PayingControl
        	WHERE
        	    EnterpriseId = :EnterpriseId AND
        	    AddUpFixedMonth = :AddUpFixedMonth
        	ORDER BY
        	    Seq DESC
        ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
                ':AddUpFixedMonth' => date('Y-m-d', strtotime($d . " -1 month")),
        );

        $ri = $stm->execute($prm);

        if (!($ri->count() > 0)) { return 0; }

        $carryOver = (int)$ri->current()['DecisionPayment'];

        return ($carryOver > 0) ? 0 : $carryOver;
	}

	/**
	 * 繰越すべき立替振込管理データをすべて繰越済みにする。
	 * @param int $enterpriseId 事業者ID
	 * @param $opId 担当者
	 */
	public function carryOverSummingUp($enterpriseId, $opId)
	{
        $sql = " UPDATE T_PayingControl SET ExecFlg = 11, UpdateDate = :UpdateDate, UpdateId = :UpdateId WHERE ExecFlg = 10 AND EnterpriseId = :EnterpriseId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $opId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定年月度の立替振込管理データを月次計上済みにする。
	 *
	 * @param int $enterpriseId 事業者ID
	 * @param string $fixedMonth 年月度 'yyyy-MM-01'書式で通知
	 * @param $opId 担当者
	 */
	public function monthlyAddUp($enterpriseId, $fixedMonth, $opId)
	{
        $sql = " UPDATE T_PayingControl SET AddUpFlg = 1, UpdateDate = :UpdateDate, UpdateId = :UpdateId WHERE EnterpriseId = :EnterpriseId AND AddUpFixedMonth = :AddUpFixedMonth ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
                ':AddUpFixedMonth' => $fixedMonth,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $opId,
        );

        return $stm->execute($prm);
	}

    /**
     * 指定確定日付の立替振込管理データを立替実行済みにする。
     *
     * @param string $decisionDate 対象確定日付 'yyyy-MM-dd'書式で通知
     * @param string $execScheduleDate 対象予定日付 'yyyy-MM-dd'書式で通知
     * @param int $oemId OEMID
     * @param int $opId 実行担当者
     * @return array 処理した管理データのSeq配列
     */
    public function execCharge($decisionDate, $execScheduleDate, $oemId, $opId)
    {
        $mdloem = new TableOem($this->_adapter);
        $mdle = new TableEnterprise($this->_adapter);

        $sql =<<<EOQ
SELECT *
FROM   T_PayingControl
WHERE  DecisionDate = :DecisionDate
AND    ExecScheduleDate = :ExecScheduleDate
AND    IFNULL(OemId,0) = :OemId
AND    ExecFlg = 0
EOQ;
        $pcDatas = $this->_adapter->query($sql)->execute(
            array(  ':DecisionDate' => $decisionDate,
                    ':ExecScheduleDate' => $execScheduleDate,
                    ':OemId' => $oemId,
            )
        );

        foreach($pcDatas as $pcData) {

            // 加盟店の精算停止区分がストップの場合は、更新処理をスキップ
            $rowe = $mdle->find($pcData['EnterpriseId'])->current();
            if ($rowe['ExecStopFlg'] == 1) {
                continue;
            }

            $sql =  " SELECT COUNT(*) AS CNT ";
            $sql .= "   FROM T_EnterpriseClaimHistory ";
            $sql .= "  WHERE PayingControlSeq = :PayingControlSeq ";

            $cnt = $this->_adapter->query($sql)->execute(array(':PayingControlSeq' => $pcData['Seq']))->current()['CNT'];

            $payingMethod = 0; // ﾃﾞﾌｫﾙﾄはCB立替
            if ($pcData['OemId'] > 0) {
                $payingMethod = $mdloem->find($pcData['OemId'])->current()['PayingMethod']; // 0:CB立替、1:OEM立替
            }

            if ($pcData['DecisionPayment'] < 0 && $cnt <= 0 && $payingMethod == 0) {
                $execFlg = 10;
            } else {
                $execFlg = 1;

                // 今回実行対象のみ抽出に変更(20150910_1840_suzuki_h)
                $result[] = $pcData['Seq'];
            }

            // 更新処理
            $sql  = " UPDATE T_PayingControl ";
            $sql .= " SET ";
            $sql .= "     ExecFlg            = :ExecFlg ";      // 立替実行済み
            $sql .= " ,   ExecDate           = :ExecDate ";     // 立替実行日
            $sql .= " ,   ExecCpId           = :ExecCpId ";     // 立替実行担当者
            $sql .= " ,   UpdateDate         = :UpdateDate ";
            $sql .= " ,   UpdateId           = :UpdateId ";
            $sql .= " WHERE Seq              = :Seq ";

            $stm = $this->_adapter->query($sql);

            $prm = array(
                    ':Seq' => $pcData['Seq'],
                    ':ExecFlg' => $execFlg,
                    ':ExecDate' => date('Y-m-d'),
                    ':ExecCpId' => $opId,
                    ':UpdateDate' => date('Y-m-d H:i:s'),
                    ':UpdateId' => $opId,
            );

            $stm->execute($prm);

        }

        return $result;
    }

	/**
	 * 指定確定日付期間の月額固定費合計を取得する。
	 *
	 * @param string $from 開始日
	 * @param string $to 終了日
	 * @return int 月額固定費合計
	 */
	public function getMonthlyFee($from, $to)
	{
	    $query = "SELECT SUM(MonthlyFee) AS MonthlyFee FROM T_PayingControl ";
		$where = BaseGeneralUtils::makeWhereDate('DecisionDate', $from, $to);
		if ($where != '')
		{
			$query = $query . ' WHERE ' . $where;
		}

        return (int)$this->_adapter->query($query)->execute(null)->current()['MonthlyFee'];
	}

	public function getMonthlyFeeOem($from, $to, $oemId)
	{
	    $query = "SELECT SUM(MonthlyFee) AS MonthlyFee FROM T_PayingControl WHERE OemId = :OemId ";
	    $where = BaseGeneralUtils::makeWhereDate('DecisionDate', $from, $to);
	    if ($where != '')
	    {
	        $query = $query . ' AND ' . $where;
	    }

	    return (int)$this->_adapter->query($query)->execute(array(':OemId' => $oemId))->current()['MonthlyFee'];
	}

	/**
	 * 指定事業者・指定確定日付期間の月額固定費合計を取得する。
	 *
	 * @param int $enterpriseId 事業者ID
	 * @param string $from 開始日
	 * @param string $to 終了日
	 * @return int 月額固定費合計
	 */
	public function getMonthlyFeeEachEnt($enterpriseId, $from, $to)
	{
	    $query = "SELECT SUM(MonthlyFee) AS MonthlyFee FROM T_PayingControl WHERE EnterpriseId = :EnterpriseId ";
	    $where = BaseGeneralUtils::makeWhereDate('DecisionDate', $from, $to);
	    if ($where != '')
	    {
	        $query = $query . ' AND ' . $where;
	    }

	    return  (int)$this->_adapter->query($query)->execute(array(':EnterpriseId' => $enterpriseId))->current()['MonthlyFee'];
	}

	/**
	 * 指定確定日付期間の振込手数料合計を取得する。
	 *
	 * @param string $from 開始日
	 * @param string $to 終了日
	 * @return int 振込手数料合計
	 */
	public function getTransferCommission($from, $to)
	{
	    $query = "SELECT SUM(TransferCommission) AS TransferCommission FROM T_PayingControl ";
		$where = BaseGeneralUtils::makeWhereDate('DecisionDate', $from, $to);
		if ($where != '')
		{
			$query = $query . ' WHERE ' . $where;
		}

        return  (int)$this->_adapter->query($query)->execute(null)->current()['TransferCommission'];
	}

	public function getTransferCommissionOem($from, $to, $oemId)
	{
        $query = "SELECT SUM(TransferCommission) AS TransferCommission FROM T_PayingControl WHERE OemId = :OemId ";
        $where = BaseGeneralUtils::makeWhereDate('DecisionDate', $from, $to);
        if ($where != '')
        {
            $query = $query . ' AND ' . $where;
        }

        return  (int)$this->_adapter->query($query)->execute(array(':OemId' => $oemId))->current()['TransferCommission'];
	}

	/**
	 * 調整額を反映する
	 * @param int $seq 振込管理シーケンス番号
	 * @param int $adjustmentAmount 調整額
	 * @param $opId 担当者
	 */
	public function adjustAmount($seq, $adjustmentAmount, $opId)
	{
        $sql = "UPDATE T_PayingControl SET DecisionPayment = CarryOver + ChargeAmount + CalcelAmount + StampFeeTotal - MonthlyFee - TransferCommission + (:AdjustmentAmount), AdjustmentAmount = :AdjustmentAmount, UpdateDate = :UpdateDate, UpdateId = :UpdateId WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AdjustmentAmount' => $adjustmentAmount,
                ':Seq' => $seq,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $opId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 前回振込手数料を取得する
	 * @param $enterpriseId
	 * @return int 前回振込手数料
	 */
	public function getPayBackTcAmount($enterpriseId)
	{
        $sql = " SELECT SUM(PayBackTC) AS PayBackTC FROM T_PayingControl WHERE ExecFlg = 10 AND EnterpriseId = :EnterpriseId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $enterpriseId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 精算すべきOEMIDのデータを取得する
	 * @param ResultInterface
	 */
	public function getClosingTargetOemIds()
	{
	    $sql = " SELECT OemId, COUNT(*) AS CNT FROM T_PayingControl WHERE OemClaimedAddUpFlg = 0 AND OemId > 0 GROUP BY OemId ";
        return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 指定されたOEMIDの精算すべきデータを取得する
	 * @param $oemId
	 * @param ResultInterface
	 */
	public function getClosingTargetDatas($oemId)
	{
        $sql = " SELECT * FROM T_PayingControl WHERE OemClaimedAddUpFlg = 0 AND OemId = :OemId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $oemId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定されたOEMIDの精算すべきデータのSeqのCSVを取得する
	 * @param $oemId
	 * @param $fixedDateLimit 立替え締め日（この日付以前の立替え締め日を対象とする）
	 * @param array
	 */
	public function getClosingTargetSeqsCSV($oemId, $fixedDateLimit) {
        $cquery = <<<EOQ
				SELECT
				    COUNT(*) AS CNT
				FROM
				    T_PayingControl PC
				    INNER JOIN T_PayingAndSales PAS ON (PC.Seq = PAS.PayingControlSeq)
				    INNER JOIN T_Order ORD ON (PAS.OrderSeq = ORD.OrderSeq)
				WHERE
				    PC.OemId = :OemId AND
                    PC.FixedDate <= :FixedDate AND
				    PC.OemClaimedAddUpFlg = 0 AND
				    ORD.Clm_F_ClaimDate IS NULL AND
				    ORD.Cnl_Status = 0
EOQ;

        $ri = $this->_adapter->query($cquery)->execute(array(':OemId' => $oemId, ':FixedDate' => $fixedDateLimit));
        if ((int)$ri->current()['CNT'] > 0) {
            throw new \Exception(sprintf("論理エラー：立替確定データのうち、初回請求未印刷の注文データがあります。,OemId=%d", $oemId));
        }

        $query = "SELECT Seq FROM T_PayingControl WHERE OemId = :OemId AND FixedDate <= :FixedDate AND OemClaimedAddUpFlg = 0";
        $ri = $this->_adapter->query($query)->execute(array(':OemId' => $oemId, ':FixedDate' => $fixedDateLimit));
        if (!($ri->count() > 0)) {
            ;// データが無いなら無いで例外を投げる必要は無い
             // throw new \Exception(sprintf("論理エラー：精算すべき立替管理データがない。,OemId=%d", $oemId));
        }

        $resultCSV = "X";
        foreach ($ri as $data) {
            $resultCSV = sprintf("%s,%d", $resultCSV, $data["Seq"]);
        }

        $resultCSV = str_replace("X,", "", $resultCSV);

        return $resultCSV;
	}

	/**
	 * 立替振込管理データを取得する
	 * @param int $seq
	 * @return ResultInterface
	 */
	public function find($seq)
	{
        $sql = " SELECT * FROM T_PayingControl WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
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
	    $sql  = " SELECT * FROM T_PayingControl WHERE 1 = 1 ";
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
	 * 0：仮締めのデータを削除する。
	 *
	 * @return ResultInterface
	 */
	public function deletePayingControl()
	{
	    $sql  = " DELETE FROM T_PayingControl WHERE PayingControlStatus = 0 ";

	    $stm = $this->_adapter->query($sql);

	    return $stm->execute();
	}

}
