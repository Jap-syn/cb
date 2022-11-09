<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use Zend\Text\Table\Table;
use models\Table\TableSystemProperty;
use models\Table\TableDeliMethod;
use Zend\Db\ResultSet\ResultSet;
use models\Table\TableOrder;
use models\Table\TableOrderItems;
use models\Table\TablePayingAndSales;
use models\Table\TableOrderSummary;
use Coral\Coral\History\CoralHistoryOrder;
use models\Table\TableOemSettlementFee;
use models\Table\ATablePayingAndSales;

/**
 * 役務提供クラス
 */
class LogicServiceTarget
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
	 * 役務提供経過処理
	 */

	/**
	 * 役務提供予定日経過処理
	 * @param int $userId
	 * @throws Exception
	 */
	public function provided($userId) {

	    $mdlsp = new TableSystemProperty($this->_adapter);
        $mdldm = new TableDeliMethod($this->_adapter);
        $mdlo = new TableOrder($this->_adapter);
        $mdloi = new TableOrderItems($this->_adapter);
        $mdlpas = new TablePayingAndSales($this->_adapter);
        $mdlsum = new TableOrderSummary($this->_adapter);
        $mdlfee = new TableOemSettlementFee($this->_adapter);
        $logicCam = new LogicCampaign($this->_adapter);
        $logicShip = new LogicShipping($this->_adapter, $userId);
        $mdlatpas = new ATablePayingAndSales($this->_adapter);      // 2015/11/17 Y.Suzuki 会計対応 Add

        $history = new CoralHistoryOrder($this->_adapter);

        try {
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // 業務日付を取得
            //$business_date = $mdlsp->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'BusinessDate');

            // 役務の配送方法を取得
            $service_delimethod = $this->_adapter->query('SELECT * FROM M_DeliveryMethod WHERE ProductServiceClass = 1 AND ValidFlg = 1')->execute(null)->current();

            // ダミー伝票番号を取得
            $dummy_number = $mdlsp->getValue( '[DEFAULT]', 'systeminfo', 'DummyJournalNumber' );

            // 対象を抽出
            // 1. 役務対象注文であること
            // 2. 役務提供予定日 <= 業務日付であること
            // 3. 未キャンセルであること
            // 4. 伝票登録待ちであること
            // 5. 伝票未登録であること
$sql = <<<EOQ
SELECT *
FROM T_Order o
WHERE 1 = 1
AND   o.ServiceTargetClass = 1
AND   o.ServiceExpectedDate <= :ServiceExpectedDate
AND   o.Cnl_Status = 0
AND   o.DataStatus IN (31,41,51)
AND   o.Deli_ConfirmArrivalFlg <> 1
EOQ;

            $prm = array(
                ':ServiceExpectedDate' => date('Y-m-d'), // システム日付以前が提供日の場合に対象とする
            );
            $ri = $this->_adapter->query($sql)->execute($prm);
            // 対象データを全件ループ
            foreach ($ri as $row) {
                $order = $row;
                $oseq = $order['OrderSeq'];
                $outofamends = nvl($order['OutOfAmends'], 0);
                $orderStatusBefore = $order['DataStatus'];
                
                $sqlTodo = <<<EOQ
SELECT count(*) AS CNT
FROM T_Order o
INNER JOIN T_Site s ON (s.SiteId=o.SiteId)
WHERE o.OrderSeq=:OrderSeq
AND
(
s.PaymentAfterArrivalFlg = 0
OR
    (
        (s.PaymentAfterArrivalFlg = 1 AND o.DataStatus IN (51) AND 
        DATE_ADD((SELECT MIN(ch.ClaimDate) FROM T_ClaimHistory AS ch WHERE ch.ClaimPattern = 1 AND ch.OrderSeq = o.OrderSeq), 
        INTERVAL (SELECT MAX(sbps.NumUseDay) AS MaxNumUseDay FROM T_SiteSbpsPayment sbps WHERE sbps.SiteId = o.SiteId AND sbps.ValidFlg = 1 
        AND sbps.UseStartDate < :CurrentDate)DAY) < :CurrentDate)    
    )
)
EOQ;
                $prmTodo = array(
                    ':OrderSeq' => $oseq,
                    ':CurrentDate' => date('Y-m-d'),
                );
                $todo = $this->_adapter->query($sqlTodo)->execute($prmTodo)->current()['CNT'];
                
                // --------------------
                // 注文の更新
                // --------------------
                if ( $orderStatusBefore != 51 ) {
                    $order['DataStatus'] = 41;
                }
                $order['Cnl_CantCancelFlg'] = $service_delimethod['EnableCancelFlg'] == 1 ? 0 : 1;
                
                //todo
                if ( $todo > 0 ) {
                    if ($outofamends == 0) {
                        // 保証内の場合のみ更新する
                        $order['Deli_ConfirmArrivalFlg'] = 1;
                        $order['Deli_ConfirmArrivalDate'] = $order['ServiceExpectedDate'];
                    }
                }
                $order['UpdateId'] = $userId;
                $mdlo->saveUpdate( $order, $oseq );

                // --------------------
                // 注文商品データの更新
                // --------------------
                $order_items = $mdloi->findByOrderSeq($oseq);
                foreach( $order_items as $order_item ) {
                    if ( !$order_item['Deli_DeliveryMethod'] || $order_item['Deli_DeliveryMethod'] == '' ) {
                        $order_item['Deli_DeliveryMethod'] = $service_delimethod['DeliMethodId'];
                        // ダミー伝票番号入力
                        $order_item['Deli_JournalNumber'] = $dummy_number;
                        $order_item['Deli_JournalIncDate'] = date('Y-m-d H:i:s');
                    }
                    
                    //todo
                    if ( $todo > 0 ) {
                        if ($outofamends == 0) {
                            // 保証内の場合のみ更新する
                            // 着荷確認済みとする
                            $order_item['Deli_ConfirmArrivalFlg'] = 1;
                            $order_item['Deli_ConfirmArrivalDate'] = $order['ServiceExpectedDate'];
                            $order_item['Deli_ConfirmArrivalOpId'] = -1; // バッチのため、-1
                        }
                    }

                    $order_item['UpdateId'] = $userId;

                    $mdloi->saveUpdate( $order_item, $order_item['OrderItemId'] );
                }

                // --------------------
                // 立替・売上管理データの作成
                // --------------------
                // キャンペーン期間中はキャンペーン情報で更新/登録する
                $campaign = $logicCam->getCampaignInfo($order['EnterpriseId'], $order['SiteId']);
                // 請求手数料(別送)を税込み金額に変換
                $campaign['ClaimFeeBS'] = $mdlsp->getIncludeTaxAmount(date('Y-m-d'), $campaign['ClaimFeeBS']);
                
                $pas = $mdlpas->findPayingAndSales(array('OrderSeq' => $oseq))->current();
                if (!$pas){
                    // 立替・売上データの作成
                    $payRow = $mdlpas->newRow(
                        $oseq,
                        $order['UseAmount'],
                        $campaign['SettlementFeeRate'],
                        $campaign['ClaimFeeBS']
                    );
                    
                    //todo
                    if ( $todo > 0 ) {
                        // テスト注文の場合は売上をあげない
                        if ( $order['T_OrderClass'] != 1 ) {
                            if ($outofamends == 0) {
                                // 保証内の場合のみ更新する
                                $payRow['ClearConditionForCharge'] = 1;   // 立替条件をクリアさせる
                                $payRow['ClearConditionDate'] = $order['ServiceExpectedDate']; // 立替条件クリア日は役務提供予定日とする
                            }
                        }
                    }
    
                    $payRow['RegistId'] = $userId;
                    $payRow['UpdateId'] = $userId;
    
                    $seq = $mdlpas->saveNew( $payRow );     // 2015/11/17 Y.Suzuki 会計対応 Mod
                } else {
                    $seq = $pas['Seq'];
                    //todo
                    if ( $todo > 0 ) {
                        // テスト注文の場合は売上をあげない
                        if ( $order['T_OrderClass'] != 1 ) {
                            if ($outofamends == 0) {
                                // 保証内の場合のみ更新する
                                // 立替条件をクリアさせる
                                $mdlpas->saveUpdate(array('ClearConditionForCharge' => 1, 'ClearConditionDate' => $order['ServiceExpectedDate'], 'UpdateId' => $userId), $seq);
                            }
                        }
                    }
                }
                
                //todo
                if ( $todo > 0 ) {
                    // 2015/11/17 Y.Suzuki Add 会計対応 Stt
                    // 会計用項目のINSERT
                    $Deli_ConfirmArrivalInputDate = null;
                    if ($outofamends == 0) {
                        $Deli_ConfirmArrivalInputDate = date('Y-m-d H:i:s');
                    }
                    $mdlatpas->saveNew(array('Seq' => $seq, 'Deli_ConfirmArrivalInputDate' => $Deli_ConfirmArrivalInputDate));
                    // 2015/11/17 Y.Suzuki Add 会計対応 End
    
                    if ( $order['T_OrderClass'] != 1 ) {
                        if ($outofamends == 0) {
                            // 保証内の場合のみ更新する
                            // 役務着荷により立替条件クリアフラグが１化されるとき => '3:役務'として更新
                            $mdlatpas->saveUpdate(array('ATUriType' => 3, 'ATUriDay' => date('Ymd', strtotime($order['ServiceExpectedDate']))), $seq);
                        }
                    }
                }

                // --------------------
                // 注文サマリを更新
                // --------------------
                $mdlsum->updateSummary( $oseq, $userId );

                // --------------------
                // OEM決済手数料を更新
                // --------------------
                // OEM対応(OEM決済手数料登録)
                if(!is_null($order['OemId']) && $order['OemId'] != 0) {
                    $mdlfee->saveOemSettlementFee( $order );
                }
                
                $sqlForOrderHistory = <<<EOQ
SELECT count(*) AS CNT
FROM T_OrderHistory o
WHERE 1 = 1
AND   o.HistoryReasonCode = 31
AND   o.OrderSeq = :OrderSeq
EOQ;
                $prmForOrderHistory = array(
                    ':OrderSeq' => $oseq,
                );
                $todoOrderHistory = $this->_adapter->query($sqlForOrderHistory)->execute($prmForOrderHistory)->current()['CNT'];
                if($todoOrderHistory < 1){
                    // 注文履歴へ登録
                    $reasonCode = 31;       // 伝票登録
                    $history->InsOrderHistory($oseq, $reasonCode, $userId);
                }

                // テスト注文時のクローズ処理
                $logicShip->closeIfTestOrder($oseq);

            }

            // コミット
            $this->_adapter->getDriver()->getConnection()->commit();

        } catch(\Exception $e) {
            // ロールバック
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e; // エラーは上位へ委任
        }


	}

}
