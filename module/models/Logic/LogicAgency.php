<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use Zend\Text\Table\Table;
use models\Table\TableAgency;
use models\Table\TableAgencyFee;
use models\Table\TableOemAgencyFee;
use models\Table\TableUser;
use models\Table\TableAgencyFeeSummary;
use models\Table\TablePayingAndSales;
use models\Table\TableCancel;
use models\Table\TableCode;

/**
 * 代理店クラス
 */
class LogicAgency
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
     * 代理店締め集計(日次)
     *
     * @return bool
     */
    public function clacuAgencyDayFix(){

        $mdlaf = new TableAgencyFee($this->_adapter);                   // 代理店手数料管理
        $mdloaf = new TableOemAgencyFee($this->_adapter);               // OEM代理店手数料
        $mdlu = new TableUser($this->_adapter);                         // ユーザー
        $mdlpas = new TablePayingAndSales($this->_adapter);             // 立替・売上管理

        try {

            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            $opId = $mdlu->getUserId(99, 1);                           // ユーザーID

            ////////////////////////日次代理店手数料計算処理/////////////////////////////

            // 業務日付を取得
            $mdlsp = new \models\Table\TableSystemProperty($this->_adapter);
            $business_date = $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'BusinessDate');

            // 対象データ取得
            $agencyDayDatas = $this->getAgencyDayData();

            foreach ($agencyDayDatas as $agencyDayData){

                if ($agencyDayData['OemId'] == 0) {// 0:CB

                    // 代理店手数料管理
                    $mdlaf->saveNew(
                    array(
                            'OrderSeq' => $agencyDayData['OrderSeq'],                           // オーダーID
                            'EnterpriseId' => $agencyDayData['EnterpriseId'],                   // 加盟店ID
                            'SiteId' =>  $agencyDayData['SiteId'],                              // サイトID
                            'AgencyId' => $agencyDayData['AgencyId'] ,                          // 代理店ID
                            'OccDate' =>  $agencyDayData['Deli_JournalIncDate'],                // データ発生日（伝票番号入力日）
                            'UseAmount' =>  $agencyDayData['UseAmount'],                        // 利用額
                            'AgencyFeeRate' => $agencyDayData['AgencyFeeRate'],                 // 代理店手数料率
                            'AgencyDivideFeeRate' => $agencyDayData['AgencyDivideFeeRate'],     // 代理店手数料按分比率
                            'AgencyFee' => $agencyDayData['AgencyFee'],                         // 代理店手数料
                            'AddUpFlg' => 0,                                                    // 月次計上フラグ
                            'CancelAddUpFlg' => 0,                                              // キャンセル月次計上フラグ
                            'AddUpFixedMonth' => date('Y-m-01', strtotime($business_date)),     // 月次計上月度
                            'PayingControlSeq' => NULL,                                         // 月次計上先立替振込管理Seq
                            'CancelFlg' => 0,                                                   // キャンセルフラグ
                            'RegistId' => $opId,                                                // 登録者
                            'UpdateId' => $opId,                                                // 更新者
                            'ValidFlg' => 1,                                                    // 有効フラグ
                        )
                    );
                } else {

                    // OEM代理店手数料追加
                    $mdloaf->saveNew(
                        array(
                                'OrderSeq' => $agencyDayData['OrderSeq'],                       // オーダーID
                                'OemId' => $agencyDayData['OemId'],                             // OEMID
                                'EnterpriseId' => $agencyDayData['EnterpriseId'],               // 加盟店ID
                                'SiteId' => $agencyDayData['SiteId'],                           // サイトID
                                'AgencyId' => $agencyDayData['AgencyId'],                       // 代理店ID
                                'OccDate' => $agencyDayData['Deli_JournalIncDate'],             // データ発生日（伝票番号入力日）
                                'UseAmount' => $agencyDayData['UseAmount'],                     // 利用額
                                'AgencyFeeRate' => $agencyDayData['AgencyFeeRate'],             // 代理店手数料率
                                'AgencyDivideFeeRate' => $agencyDayData['AgencyDivideFeeRate'], // 代理店手数料按分比率
                                'AgencyFee' => $agencyDayData['AgencyFee'],                     // 代理店手数料
                                'AddUpFlg' => 0,                                                // 月次計上フラグ
                                'CancelAddUpFlg' => 0,                                          // キャンセル月次計上フラグ
                                'AddUpFixedMonth' => date('Y-m-01', strtotime($business_date)), // 月次計上月度
                                'OemClaimedSeq' => NULL,                                        // 月次計上先OEM請求Seq
                                'CancelFlg' => 0,                                               // キャンセルフラグ
                                'RegistId' => $opId,                                            // 登録者
                                'UpdateId' => $opId,                                            // 更新者
                                'ValidFlg' => 1,                                                // 有効フラグ
                        )
                    );
                }

                // 立替・売上管理の更新
                $mdlpas->saveUpdateWhere(array('AgencyFeeAddUpFlg' => 1, 'UpdateId' => $opId), array('OrderSeq' => $agencyDayData['OrderSeq']));
            }

            // 20180726 Add
            // キャンセル処理
            $cancelDatas = $this->getCancelData($business_date);

            foreach ($cancelDatas as $cancelData){
                if ($cancelData['OemId'] == 0) {// 0:CB

                    // 代理店手数料管理
                    $mdlaf->saveNew(
                    array(
                            'OrderSeq' => $cancelData['OrderSeq'],                              // オーダーID
                            'EnterpriseId' => $cancelData['EnterpriseId'],                      // 加盟店ID
                            'SiteId' =>  $cancelData['SiteId'],                                 // サイトID
                            'AgencyId' => $cancelData['AgencyId'] ,                             // 代理店ID
                            'OccDate' =>  $cancelData['OccDate'],                               // データ発生日（伝票番号入力日）
                            'UseAmount' =>  $cancelData['UseAmount'],                           // 利用額
                            'AgencyFeeRate' => $cancelData['AgencyFeeRate'],                    // 代理店手数料率
                            'AgencyDivideFeeRate' => $cancelData['AgencyDivideFeeRate'],        // 代理店手数料按分比率
                            'AgencyFee' => -1 * $cancelData['AgencyFee'],                       // 代理店手数料
                            'AddUpFlg' => $cancelData['AddUpFlg'],                              // 月次計上フラグ
                            'CancelAddUpFlg' => 1,                                              // キャンセル月次計上フラグ
                            'AddUpFixedMonth' => date('Y-m-01', strtotime($business_date)),     // 月次計上月度
                            'PayingControlSeq' => $cancelData['PayingControlSeq'],              // 月次計上先立替振込管理Seq
                            'CancelFlg' => $cancelData['CancelFlg'],                            // キャンセルフラグ
                            'RegistId' => $opId,                                                // 登録者
                            'UpdateId' => $opId,                                                // 更新者
                            'ValidFlg' => 1,                                                    // 有効フラグ
                        )
                    );
                    // 代理店手数料管理の更新
                    $mdlaf->saveUpdate(array('CancelAddUpFlg' => 1, 'UpdateId' => $opId), $cancelData['Seq']);
                } else {

                    // OEM代理店手数料追加
                    $mdloaf->saveNew(
                    array(
                            'OrderSeq' => $cancelData['OrderSeq'],                          // オーダーID
                            'OemId' => $cancelData['OemId'],                                // OEMID
                            'EnterpriseId' => $cancelData['EnterpriseId'],                  // 加盟店ID
                            'SiteId' => $cancelData['SiteId'],                              // サイトID
                            'AgencyId' => $cancelData['AgencyId'],                          // 代理店ID
                            'OccDate' => $cancelData['OccDate'],                            // データ発生日（伝票番号入力日）
                            'UseAmount' => $cancelData['UseAmount'],                        // 利用額
                            'AgencyFeeRate' => $cancelData['AgencyFeeRate'],                // 代理店手数料率
                            'AgencyDivideFeeRate' => $cancelData['AgencyDivideFeeRate'],    // 代理店手数料按分比率
                            'AgencyFee' => -1 * $cancelData['AgencyFee'],                   // 代理店手数料
                            'AddUpFlg' => $cancelData['AddUpFlg'],                          // 月次計上フラグ
                            'CancelAddUpFlg' => 1,                                              // キャンセル月次計上フラグ
                            'AddUpFixedMonth' => date('Y-m-01', strtotime($business_date)), // 月次計上月度
                            'OemClaimedSeq' => $cancelData['PayingControlSeq'],             // 月次計上先OEM請求Seq
                            'CancelFlg' => $cancelData['CancelFlg'],                        // キャンセルフラグ
                            'RegistId' => $opId,                                            // 登録者
                            'UpdateId' => $opId,                                            // 更新者
                            'ValidFlg' => 1,                                                // 有効フラグ
                        )
                    );
                    // OEM代理店手数料管理の更新
                    $mdloaf->saveUpdate(array('CancelAddUpFlg' => 1, 'UpdateId' => $opId), $cancelData['Seq']);
                }
            }

            $this->_adapter->getDriver()->getConnection()->commit();

        } catch (\Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
	}

    /**
     * 代理店締め集計(月次)
     *
     * @return bool 処理結果
     * */
    public function clacuAgencyMonthFix(){

        $mdlaf = new TableAgencyFee($this->_adapter);                               // 代理店手数料管理
        $mdloaf = new TableOemAgencyFee($this->_adapter);                           // OEM代理店手数料管理
        $mdlafs = new TableAgencyFeeSummary($this->_adapter);                       // 代理店手数料ｻﾏﾘｰ
        $mdla = new TableAgency($this->_adapter);                                   // 代理店マスター
        $mdu = new TableUser($this->_adapter);                                      // ユーザーマスター
        $mdc = new TableCode($this->_adapter);                                      // コードマスタ

        $targetMonth = date('Y-m-01', strtotime(date('Y-m-01').' -1 month'));       // 対象年月

        try {

            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            $opId = $mdu->getUserId(99, 1);                                         // ユーザーＩＤ

            // コードマスタから代理店負担金取得
            $tfDatas = ResultInterfaceToArray($mdc->getMasterByClass(93));
            $master = array();
            foreach ($tfDatas as $tfData){
                $master[$tfData['KeyCode']] = $tfData['Note'];
            }

            // 対象年月の代理店手数料ｻﾏﾘｰ削除
            $mdlafs->deleteAgencyFeeSummary(
               array(
                       'TargetMonth' => $targetMonth
               )
            );

            // 対象年月の代理店手数料管理フラグオフ
            $mdlaf->saveUpdateWhere(
                array(
                    'AddUpFlg' => 0,
                    'UpdateId' => $opId,
                )
                ,
                array(
                    'ValidFlg' => 1,
                    'CancelFlg' => 0,
                    'AddUpFlg' => 1,
                    'AddUpFixedMonth' => $targetMonth,
                )
            );

            // 対象年月のOEM代理店手数料管理フラグオフ
            $mdloaf->saveUpdateWhere(
                array(
                    'AddUpFlg' => 0,
                    'UpdateId' => $opId,
                )
                ,
                array(
                    'ValidFlg' => 1,
                    'CancelFlg' => 0,
                    'AddUpFlg' => 1,
                    'AddUpFixedMonth' => $targetMonth,
                )
            );

            // 月次代理店手数料計算対象抽出
            $targetDatas = $this->getTargetData($targetMonth);

            foreach ($targetDatas as $targetData){

                // 小計の計算
                $subTotal = $targetData['AgencyFee'] + $targetData['FeeUnpaidBalance'];

                // 手数料の計算
                if ($subTotal <= 0){
                    // 小計が0以下の場合
                    $tcClass = 0;
                } elseif ($targetData['ChargeClass'] == 1){
                    // CB負担の場合
                    $tcClass = 0;
                } elseif ($targetData['TransferFeeClass'] == 1) {
                    // 代理店負担・同行の場合
                    if ($subTotal < 30000){
                        $tcClass = $master[1];
                    } else {
                        $tcClass = $master[2];
                    }
                } else {
                    // 代理店負担・他行の場合
                    if ($subTotal < 30000){
                        $tcClass = $master[3];
                    } else {
                        $tcClass = $master[4];
                    }
                }

                // 代理店手数料ｻﾏﾘｰ情報追加
                $data = array(
                        'AgencyId' => $targetData['AgencyId'],                             // 代理店ID
                        'TargetMonth' => $targetMonth,                                     // 対象年月
                        'EnterpriseCount' => $targetData['EnterpriseCount'],               // 対象加盟店数
                        'EnterpriseSalesAmount' => $targetData['EnterpriseSalesAmount'],   // 加盟店売上額
                        'AgencyFee' => $targetData['AgencyFee'],                           // 手数料額
                        'TransferCommission' => $tcClass,                                  // 振込手数料
                        'CarryOverTC' => $targetData['FeeUnpaidBalance'],                  // 前回からの繰越手数料額
                        'SubTotal' => $subTotal,                                           // 小計
                        'PaymentAmount' => $subTotal - $tcClass,                           // 支払額
                        'PaymentTargetClass' => $subTotal < $targetData['FeePaymentThreshold'] ? 0 : 1, // 支払対象区分
                        'RegistId' => $opId,
                        'UpdateId' => $opId,
                        'ValidFlg' => 1,
                        'MonthlyFee' => 0,
                );

                $mdlafs->saveNew($data);

                // 代理店手数料管理の更新
                $agencyFeedata = array(
                        'AddUpFlg' => 1,                                                   // 月次計上フラグ
                        'UpdateDate' => date('Y-m-d H:i:s'),
                        'UpdateId' => $opId,
                );

                $conditionArray = array(
                        'ValidFlg' => 1,                                                   // 有効フラグ
                        'CancelFlg' => 0,                                                  // キャンセルフラグ
                        'AddUpFlg' => 0,                                                   // 月次計上フラグ
                        'AddUpFixedMonth' => $targetMonth,                                 // 月次計上月度
                        'AgencyId' => $targetData['AgencyId'],                             // 代理店ID
                );

                $mdlaf->saveUpdateWhere($agencyFeedata, $conditionArray);

                // OEM代理店手数料管理の更新
                $oemAgencyFeedata = array(
                        'AddUpFlg' => 1,                                                   // 月次計上フラグ
                        'UpdateDate' => date('Y-m-d H:i:s'),
                        'UpdateId' => $opId,
                );

                $conditionArray = array(
                        'ValidFlg' => 1,                                                   // 有効フラグ
                        'CancelFlg' => 0,                                                  // キャンセルフラグ
                        'AddUpFlg' => 0,                                                   // 月次計上フラグ
                        'AddUpFixedMonth' => $targetMonth,                                 // 月次計上月度
                        'AgencyId' => $targetData['AgencyId'],                             // 代理店ID
                );

                $mdloaf->saveUpdateWhere($oemAgencyFeedata, $conditionArray);

                // 代理店マスター手数料未払残高の更新
                $agencyData = array(
                        'FeeUnpaidBalance' => $subTotal < $targetData['FeePaymentThreshold'] ? $subTotal : 0,   // 手数料未払残高
                        'UpdateDate' => date('Y-m-d H:i:s'),
                        'UpdateId' => $opId,
                );

                $mdla->saveUpdate($agencyData, $targetData['AgencyId']);
            }

            // 3.代理店月額固定費計算処理
            // 3.1　対象抽出
            $ri = $this->getAgencyMonthlyFeeBase();

            foreach ($ri as $row) {
                // 3.2　対象件数分の繰り返し

                // 3.2.1　代理店手数料ｻﾏﾘｰの存在ﾁｪｯｸ]
                $row_afs = $this->_adapter->query(" SELECT AgencyFee FROM T_AgencyFeeSummary WHERE AgencyId = :AgencyId AND TargetMonth = :TargetMonth "
                    )->execute(array(':AgencyId' => $row['AgencyId'], ':TargetMonth' => $targetMonth))->current();

                if (!$row_afs) {

                    // 手数料の計算
                    if ((0 + $row['FeeUnpaidBalance'] + $row['MonthlyFee']) <= 0){
                        // 小計が0以下の場合
                        $tcClass = 0;
                    } elseif ($row['ChargeClass'] == 1){
                        // CB負担の場合
                        $tcClass = 0;
                    } elseif ($row['TransferFeeClass'] == 1) {
                        // 代理店負担・同行の場合
                        if ((0 + $row['FeeUnpaidBalance'] + $row['MonthlyFee']) < 30000){
                            $tcClass = $master[1];
                        } else {
                            $tcClass = $master[2];
                        }
                    } else {
                        // 代理店負担・他行の場合
                        if ((0 + $row['FeeUnpaidBalance'] + $row['MonthlyFee']) < 30000){
                            $tcClass = $master[3];
                        } else {
                            $tcClass = $master[4];
                        }
                    }

                    // 3.2.3　(代理店ID=WK.代理店IDかつ代理店手数料ｻﾏﾘｰ.対象年月=WK.対象年月)の代理店手数料ｻﾏﾘｰが存在しない場合、追加出力する
                    $data = array(
                        'AgencyId' => $row['AgencyId'],
                        'TargetMonth' => $targetMonth,
                        'EnterpriseCount' => $row['EnterpriseCount'],
                        'EnterpriseSalesAmount' => 0,
                        'AgencyFee' => 0,
                        'TransferCommission' => $tcClass,
                        'MonthlyFee' => $row['MonthlyFee'],
                        'CarryOverTC' => $row['FeeUnpaidBalance'],
                        'SubTotal' => (0 + $row['FeeUnpaidBalance'] + $row['MonthlyFee']),
                        'PaymentAmount' => (0 + $row['FeeUnpaidBalance'] + $row['MonthlyFee'] - $tcClass),
                        'PaymentTargetClass' => ((0 + $row['FeeUnpaidBalance'] + $row['MonthlyFee']) < $row['FeePaymentThreshold']) ? 0 : 1,
                        'RegistDate' => date('Y-m-d H:i:s'),
                        'RegistId' => $opId,
                        'UpdateDate' => date('Y-m-d H:i:s'),
                        'UpdateId' => $opId,
                        'ValidFlg' => 1,
                    );

                    $mdlafs->saveNew($data);
                }
                else {
                    // 手数料の計算
                    if (($row_afs['AgencyFee'] + $row['FeeUnpaidBalance'] + $row['MonthlyFee']) <= 0){
                        // 小計が0以下の場合
                        $tcClass = 0;
                    } elseif ($row['ChargeClass'] == 1){
                        // CB負担の場合
                        $tcClass = 0;
                    } elseif ($row['TransferFeeClass'] == 1) {
                        // 代理店負担・同行の場合
                        if (($row_afs['AgencyFee'] + $row['FeeUnpaidBalance'] + $row['MonthlyFee']) < 30000){
                            $tcClass = $master[1];
                        } else {
                            $tcClass = $master[2];
                        }
                    } else {
                        // 代理店負担・他行の場合
                        if (($row_afs['AgencyFee'] + $row['FeeUnpaidBalance'] + $row['MonthlyFee']) < 30000){
                            $tcClass = $master[3];
                        } else {
                            $tcClass = $master[4];
                        }
                    }

                    // 3.2.4　(代理店ID=WK.代理店IDかつ代理店手数料ｻﾏﾘｰ.対象年月=WK.対象年月)の代理店手数料ｻﾏﾘｰが存在する場合、代理店ID=WK.代理店IDの代理店
                    $data = array(
                        'AgencyFee' => $row_afs['AgencyFee'],
                        'TransferCommission' => $tcClass,
                        'MonthlyFee' => $row['MonthlyFee'],
                        'CarryOverTC' => $row['FeeUnpaidBalance'],
                        'SubTotal' => ($row_afs['AgencyFee'] + $row['FeeUnpaidBalance'] + $row['MonthlyFee']),
                        'PaymentAmount' => ($row_afs['AgencyFee'] + $row['FeeUnpaidBalance'] + $row['MonthlyFee'] - $tcClass),
                        'PaymentTargetClass' => (($row_afs['AgencyFee'] + $row['FeeUnpaidBalance'] + $row['MonthlyFee']) < $row['FeePaymentThreshold']) ? 0 : 1,
                        'UpdateDate' => date('Y-m-d H:i:s'),
                        'UpdateId' => $opId,
                        'ValidFlg' => 1,
                    );

                    $mdlafs->saveUpdate($data, $row['AgencyId'], $targetMonth);
                }
            }

            $this->_adapter->getDriver()->getConnection()->commit();

        } catch (Exception $e) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * 代理店締め日次集計対象データの取得
     *
     */
    private function getAgencyDayData()
    {
        $sql = <<<EOQ
SELECT  o.OrderSeq                      /* 注文Seq */
    ,   o.OemId                         /* OEMID */
    ,   o.EnterpriseId                  /* 加盟店ID */
    ,   o.SiteId                        /* サイトID */
    ,   mas.AgencyId                    /* 代理店ID */
    /* 伝票番号入力日 */
    ,   MAX(oi.Deli_JournalIncDate) AS Deli_JournalIncDate
    ,   o.UseAmount                     /* 利用額 */
    ,   mas.AgencyFeeRate               /* 代理店手数料率 */
    /* 代理店手数料按分比率 */
    ,   IFNULL(mas.AgencyDivideFeeRate, 1) AS AgencyDivideFeeRate
    /* 代理店手数料 */
    ,   FLOOR(o.UseAmount * mas.AgencyFeeRate / 100 * IFNULL(mas.AgencyDivideFeeRate, 100) / 100) AS AgencyFee
FROM    T_Order o
        INNER JOIN T_OrderItems oi ON (oi.OrderSeq = o.OrderSeq)
        INNER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
        INNER JOIN M_AgencySite mas ON (mas.SiteId = o.SiteId)
        INNER JOIN M_Agency ma ON (ma.AgencyId = mas.AgencyId)
WHERE   o.ValidFlg = 1                              /* 注文.有効フラグ */
AND     (o.DataStatus = 91 AND o.CloseReason = 1)   /* 入金済み正常クローズ */
AND     pas.AgencyFeeAddUpFlg = 0                   /* 代理店手数料計上フラグ */
AND     mas.ValidFlg = 1                            /* 代理店サイト関連マスタ.有効フラグ */
GROUP BY
        mas.AgencyId
    ,   o.OrderSeq
;
EOQ;

        $stm = $this->_adapter->query($sql);

        $prm = array(
        );

	    return $stm->execute($prm);
	}

    /**
     * 月次代理店手数料計算対象抽出
     *
     * @param date 対象年月
     * @return array
     */
    private function getTargetData($targetMonth){

        // 加盟店側
$sql = <<<EOQ
SELECT 	  T.AgencyId
		, SUM(T.EnterpriseCount) 			AS EnterpriseCount
        , SUM(T.EnterpriseSalesAmount) 		AS EnterpriseSalesAmount
        , SUM(T.AgencyFee)					AS AgencyFee
		, MAX(MA.ChargeClass)               AS ChargeClass                    -- 手数料負担
		, MAX(MA.TransferFeeClass)          AS TransferFeeClass               -- 代理店負担
        , MAX(MA.FeePaymentThreshold)       AS FeePaymentThreshold            -- 手数料支払閾値
		, MAX(MA.FeeUnpaidBalance)          AS FeeUnpaidBalance               -- 手数料未払残高
FROM (
		(
			 SELECT
				   AgencyId                      AS AgencyId                       -- 代理店ID
			 ,     COUNT(DISTINCT EnterpriseId)  AS EnterpriseCount                -- 対象加盟店数
			 ,     SUM(UseAmount)                AS EnterpriseSalesAmount          -- 加盟店売上額
			 ,     SUM(AgencyFee)                AS AgencyFee                      -- 手数料額
			 FROM  T_AgencyFee
			 WHERE 1 = 1
             AND   ValidFlg   = 1
			 AND   CancelFlg  = 0
			 AND   AddUpFlg   = 0
		     AND   T_AgencyFee.AddUpFixedMonth = :AddUpFixedMonth
             GROUP BY AgencyId
		)
        UNION ALL
		-- OEM側
        (
			 SELECT
				   AgencyId                      AS AgencyId                       -- 代理店ID
			 ,     COUNT(DISTINCT EnterpriseId)  AS EnterpriseCount                -- 対象加盟店数
			 ,     SUM(UseAmount)                AS EnterpriseSalesAmount          -- 加盟店売上額
			 ,     SUM(AgencyFee)                AS AgencyFee                      -- 手数料額
			 FROM  T_OemAgencyFee
			 WHERE 1 = 1
			 AND   ValidFlg   = 1
			 AND   CancelFlg  = 0
			 AND   AddUpFlg   = 0
			 AND   AddUpFixedMonth = :AddUpFixedMonth
			 GROUP BY AgencyId
         )
	) T
    INNER JOIN M_Agency MA
			ON T.AgencyId = MA.AgencyId
GROUP BY T.AgencyId
EOQ;

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':AddUpFixedMonth' => $targetMonth,
        );

        return $stm->execute($prm);
    }

    /**
     * 代理店月額固定費計算処理(対象抽出)
     *
     */
    private function getAgencyMonthlyFeeBase()
    {
        $sql = <<<EOQ
SELECT mas.AgencyId
,      COUNT(DISTINCT mas.EnterpriseId) AS EnterpriseCount
,      SUM(mas.MonthlyFee) AS MonthlyFee
,      MAX(ma.ChargeClass) AS ChargeClass
,      MAX(ma.TransferFeeClass) AS TransferFeeClass
,      MAX(ma.FeePaymentThreshold) AS FeePaymentThreshold
,      MAX(ma.FeeUnpaidBalance) AS FeeUnpaidBalance
FROM   M_AgencySite mas
       INNER JOIN M_Agency ma ON (ma.AgencyId = mas.AgencyId)
WHERE  mas.MonthlyFee > 0
GROUP BY mas.AgencyId
EOQ;
	    return $this->_adapter->query($sql)->execute(null);
	}

	// 20180726 Add
	/**
	 * キャンセル管理対象取得
	 * 処理日当日にキャンセル承認されたものを対象
     * @param date 処理日
     * @return array
     */
    private function getCancelData($targetDate){
        $sql = <<<EOQ
SELECT o.OrderSeq
      ,o.OemId
      ,af.EnterpriseId
      ,af.SiteId
      ,af.AgencyId
      ,af.OccDate
      ,af.UseAmount
      ,af.AgencyFeeRate
      ,af.AgencyDivideFeeRate
      ,af.AgencyFee
      ,af.AddUpFlg
      ,af.AddUpFixedMonth
      ,af.PayingControlSeq
      ,af.CancelFlg
      ,af.Seq
  FROM T_Cancel c
 INNER JOIN T_Order o ON (c.OrderSeq = o.OrderSeq)
 INNER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
 INNER JOIN T_AgencyFee af ON (af.OrderSeq = o.OrderSeq)
 WHERE c.ApproveFlg = 1
   AND CAST(c.ApprovalDate AS DATE) = :ApprovalDate
   AND af.CancelAddUpFlg = 0
   AND pas.AgencyFeeAddUpFlg = 1
   AND o.OemId = 0
UNION ALL
SELECT o.OrderSeq
      ,o.OemId
      ,oaf.EnterpriseId
      ,oaf.SiteId
      ,oaf.AgencyId
      ,oaf.OccDate
      ,oaf.UseAmount
      ,oaf.AgencyFeeRate
      ,oaf.AgencyDivideFeeRate
      ,oaf.AgencyFee
      ,oaf.AddUpFlg
      ,oaf.AddUpFixedMonth
      ,oaf.OemClaimedSeq as PayingControlSeq
      ,oaf.CancelFlg
      ,oaf.Seq
  FROM T_Cancel c
 INNER JOIN T_Order o ON (c.OrderSeq = o.OrderSeq)
 INNER JOIN T_PayingAndSales pas ON (pas.OrderSeq = o.OrderSeq)
 INNER JOIN T_OemAgencyFee oaf ON (oaf.OrderSeq = o.OrderSeq)
 WHERE c.ApproveFlg = 1
   AND CAST(c.ApprovalDate AS DATE) = :ApprovalDate
   AND oaf.CancelAddUpFlg = 0
   AND pas.AgencyFeeAddUpFlg = 1
   AND o.OemId <> 0
EOQ;
        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ApprovalDate' => $targetDate,
        );

        return $stm->execute($prm);
	}
}
