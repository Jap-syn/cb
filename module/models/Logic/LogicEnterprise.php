<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use Zend\Text\Table\Table;
use models\Table\TableEnterpriseClaimed;
use models\Table\TablePayingControl;
use models\Table\TableEnterpriseClaimHistory;
use models\Table\TableUser;

/**
 * 加盟店クラス
 */
class LogicEnterprise
{
	/**
	 * アダプタ
	 *
	 * @var Adapter
	 */
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
     * 月次明細データ作成
	 * @throws Exception
	 */
	public function createMonthDedailData(){

	    $mdlec = new TableEnterpriseClaimed($this->_adapter);                           // 加盟店月別請求
	    $mdlech = new TableEnterpriseClaimHistory($this->_adapter);                     // 加盟店月別請求履歴
	    $mdlpc = new TablePayingControl($this->_adapter);                               // 立替振込管理
	    $mdlu = new TableUser($this->_adapter);                                         // ユーザー

        // 対象月度の決定
        $fromDate = date('Y-m-01',strtotime(date('Y-m-d').' -1 month'));                // From日
        $toDate = date('Y-m-t', strtotime($fromDate));                                  // TO日
        $objDate = $fromDate;                                                           // 対象月度
        $befDate = date('Y-m-d', strtotime($objDate.' -1 month'));                      // 前月

        try {

            // --------------------------------------------------------------------------------------------------------
            // 処理の開始判定
            // --------------------------------------------------------------------------------------------------------
            // 前月の立替データが存在しない場合は処理をスキップ
            $sql = ' SELECT COUNT(1) AS cnt FROM T_PayingControl pc WHERE pc.AddUpFixedMonth = :AddUpFixedMonth ';
            $prm = array(
                ':AddUpFixedMonth' => $objDate,
            );
            $cnt = (int)$this->_adapter->query($sql)->execute($prm)->current()['cnt'];
            if ( $cnt <= 0 ) {
                return;
            }

            // 前月の仮締めデータが存在する場合は処理をスキップ
            $sql = ' SELECT COUNT(1) AS cnt FROM T_PayingControl pc WHERE pc.AddUpFixedMonth = :AddUpFixedMonth AND PayingControlStatus = 0 ';
            $prm = array(
                    ':AddUpFixedMonth' => $objDate,
            );
            $cnt = (int)$this->_adapter->query($sql)->execute($prm)->current()['cnt'];
            if ( $cnt > 0 ) {
                return;
            }

            // 前月の締め情報が存在する場合は処理をスキップ
            $sql = ' SELECT COUNT(1) AS cnt FROM T_EnterpriseClaimed ec WHERE ec.FixedMonth = :FixedMonth ';
            $prm = array(
                    ':FixedMonth' => $objDate,
            );
            $cnt = (int)$this->_adapter->query($sql)->execute($prm)->current()['cnt'];
            if ( $cnt > 0 ) {
                return;
            }


            // --------------------------------------------------------------------------------------------------------
            // メイン処理
            // --------------------------------------------------------------------------------------------------------
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            $opId = $mdlu->getUserId(99, 1);                                            // ユーザーID

            ////////////////////////初期化/////////////////////////////

            // 加盟店月別請求の初期化
            $mdlec->deleteEnterpriseClaimed(
                array(
                        'FixedMonth' => $objDate,                                       // 月度
                )
            );

            // 立替振込管理の初期化
            $data = array(
                    'AddUpFlg' => 0,                                                    // 月次計上フラグ(0:未計上)
                    'UpdateDate' => date('Y-m-d H:i:s'),
                    'UpdateId' => $opId,
            );

            $condition = array(
                    'AddUpFixedMonth' => $objDate,                                       // 月次計上月度
            );

            $mdlpc->saveUpdateWhere($data, $condition);

            //////////////////////月次計算処理/////////////////////////////

            // 月次計算対象データ抽出
            $monthObjDatas = $this->getMonthCaluObjData($objDate);

            foreach ($monthObjDatas as $monthObjData){

                // 前回繰越データの取得
                $befCarryOver = $this->getBefCarryOverData($monthObjData['EnterpriseId'], $befDate);

                // 加盟店月別請求の作成
                $mdlec->saveNew(
                    array(
                            'EnterpriseId' => $monthObjData['EnterpriseId'],                    // 加盟店ID
                            'FixedMonth' => $objDate,                                           // 月度
                            'ProcessDate' => date('Y-m-d H:i:s'),                               // 月次処理日
                            'SpanFrom' => $fromDate,                                            // 対象期間－From
                            'SpanTo' => $toDate,                                                // 対象期間－To
                            'OrderCount' => $monthObjData['Sum_ChargeCount'],                   // 注文件数
                            'OrderAmount' => $monthObjData['Sum_ChargeAmountOfFee'],            // 注文総額
                            'SettlementFee' => $monthObjData['Sum_SettlementFee'] ,             // 決済手数料
                            'ClaimFee' => $monthObjData['Sum_ClaimFee'],                        // 請求手数料
                            'StampFee' => $monthObjData['Sum_StampFeeTotal'],                   // 印紙代
                            'MonthlyFee' => $monthObjData['Sum_MonthlyFee'],                    // 月額固定費
                            'CarryOverMonthlyFee' => $befCarryOver,                             // 前月繰越（固定費）
                            'CancelRepaymentAmount' => $monthObjData['Sum_CalcelAmount'],       // キャンセル返金
                            'FfTransferFee' => $monthObjData['TransferCommission'],             // 口座振込手数料
                            'AdjustmentAmount' => $monthObjData['Sum_AdjustmentAmount'],        // 精算調整額
                            'ClaimAmount' => $monthObjData['Sum_ClaimAmount'],                  // 請求金額
                            'PaymentAmount' =>  $monthObjData['Sum_ChargeAmountOfFee']
                                        - $monthObjData['Sum_ClaimAmount'],                     // 支払総額
                            'AdjustmentAmountOnMonthly' => 0,                                   // 月次データ作成時に指定された精算調整額
                            'OemId' => $monthObjData['OemId'],                                  // OEMID
                            'PayBackAmount' => $monthObjData['Sum_PayBackAmount'],              // 立替精算戻し金額
                            'RegistId' => $opId,                                                // 登録者
                            'UpdateId' => $opId,                                                // 更新者
                            'ValidFlg' =>1 ,                                                    // 有効フラグ
                    )
                );

                // 加立替振込管理のﾌﾗｸﾞ更新
                $data = array(
                        'AddUpFlg' => 1,                                             // 月次計上フラグ(1:計上済)
                        'UpdateDate' => date('Y-m-d H:i:s'),
                        'UpdateId' => $opId,
                );

                $condition = array(
                        'AddUpFixedMonth' => $objDate,                              // 月次計上月度
                        'EnterpriseId' => $monthObjData['EnterpriseId'],            // 加盟店ID
                );

                $mdlpc->saveUpdateWhere($data, $condition);
            }

            $this->_adapter->getDriver()->getConnection()->commit();

        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

	/**
	 * 月次計算対象データの取得
	 *
	 * @param int $objDate 対象月度
	 */
	private function getMonthCaluObjData($objDate)
	{
	    $sql  = " SELECT ";
        $sql .= "       T_Enterprise.EnterpriseId ";                                            // 加盟店ID
        $sql .= " ,     T_Enterprise.LoginId ";                                                 // ﾛｸﾞｲﾝID
        $sql .= " ,     T_Enterprise.EnterpriseNameKj ";                                        // 加盟店名
        $sql .= " ,     T_Enterprise.OemId ";                                                   // OEMID
        $sql .= " ,     T_Enterprise.ClaimClass ";                                              // 加盟店請求区分
        $sql .= " ,     SUM(T_PayingControl.ChargeCount)        as Sum_ChargeCount ";           // 立替注文件数計
        $sql .= " ,     SUM(T_PayingControl.ChargeAmount + T_PayingControl.SettlementFee + T_PayingControl.ClaimFee) ";
        $sql .= "                                               as Sum_ChargeAmountOfFee ";  // 手数料加えた立替金額
        $sql .= " ,     SUM(T_PayingControl.SettlementFee)      as Sum_SettlementFee ";         // 決済手数料
        $sql .= " ,     SUM(T_PayingControl.ClaimFee)           as Sum_ClaimFee ";              // 請求手数料
        $sql .= " ,     SUM(T_PayingControl.CalcelAmount)       as Sum_CalcelAmount ";          // キャンセル精算金額
        $sql .= " ,     SUM(T_PayingControl.StampFeeTotal)      as Sum_StampFeeTotal ";         // 印紙代精算金額
        $sql .= " ,     SUM(T_PayingControl.PayBackAmount)      as Sum_PayBackAmount ";         // 立替精算戻し金額
        $sql .= " ,     SUM(T_PayingControl.MonthlyFee)         as Sum_MonthlyFee ";            // 月額固定費
        $sql .= " ,     SUM((T_PayingControl.ChargeAmount + T_PayingControl.SettlementFee + T_PayingControl.ClaimFee) ";
        $sql .= "        - (T_PayingControl.DecisionPayment - T_PayingControl.CarryOver)) ";
        $sql .= "                                               as Sum_ClaimAmount ";           // 請求金額
        $sql .= " ,     SUM(T_PayingControl.TransferCommission) as TransferCommission ";        // 振込手数料
        $sql .= " ,     SUM(T_PayingControl.DecisionPayment)    as DecisionPayment ";           // 振込確定金額
        $sql .= " ,     SUM(T_PayingControl.DecisionPayment - T_PayingControl.CarryOver) ";
        $sql .= "                                               as Sum_DecisionPaymentOfCarr "; //(振込確定金額-WK.繰越)
        $sql .= " ,     SUM(T_PayingControl.AdjustmentAmount)   as Sum_AdjustmentAmount ";      // 調整額
        $sql .= " ,     MAX(T_PayingControl.Seq)                as Max_Seq ";                   // 立替振込管理SEQ
        $sql .= " FROM  T_PayingControl, T_Enterprise ";
        $sql .= " WHERE T_PayingControl.EnterpriseId = T_Enterprise.EnterpriseId ";
        $sql .= " AND   T_PayingControl.AddUpFlg = 0 ";
        $sql .= " AND   T_PayingControl.AddUpFixedMonth = :AddUpFixedMonth ";
        $sql .= " AND   T_PayingControl.ValidFlg = 1 ";
        $sql .= " GROUP BY T_Enterprise.EnterpriseId ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':AddUpFixedMonth' => $objDate,
	    );

	    return $stm->execute($prm);
	}

	/**
	 * 前回繰越データの取得
	 *
	 * @param int $befDate 前月
	 * @param int $enterpriseId 加盟店
	 */
	private function getBefCarryOverData($enterpriseId, $befDate)
	{
	    $rtnBefCarryOver = 0;                                              // 前回繰越

	    $sql  = " SELECT ";
	    $sql .= "       EnterpriseId ";
	    $sql .= " ,     AddUpFixedMonth ";
	    $sql .= " ,     SUM(PayBackTC)  as Sum_PayBackTC ";
	    $sql .= " FROM  T_PayingControl ";
	    $sql .= " WHERE EnterpriseId = :EnterpriseId ";
	    $sql .= " AND   AddUpFixedMonth = :AddUpFixedMonth ";
	    $sql .= " GROUP BY EnterpriseId, AddUpFixedMonth ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            'EnterpriseId' => $enterpriseId,           // 加盟店
	            'AddUpFixedMonth' => $befDate,
	    );

	    $row = $stm->execute($prm)->current();

	    $rtnBefCarryOver = ($row) ? $row['Sum_PayBackTC'] : 0;

	    return $rtnBefCarryOver;
	}
}
