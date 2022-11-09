<?php
namespace models\Logic;

use Coral\Base\BaseLog;
use Coral\Base\IO\BaseIOUtility;
use models\Logic\Jnb\LogicJnbAccount;
use models\Logic\Smbcpa\LogicSmbcpaAccount;
use models\Table\ATableOrder;
use models\Table\TableSBPaymentSendResultHistory;
use models\Table\TableSite;
use Zend\Config\Reader\Ini;
use Zend\Db\Adapter\Adapter;
use models\Table\TableOrder;
use models\Table\TableCancel;
use models\Table\TablePayingAndSales;
use models\Table\TableStampFee;
use models\Table\TableOemSettlementFee;
use models\Table\TableOemClaimFee;
use models\Table\TableClaimHistory;
use models\Table\TableReclaimIndicate;
use Zend\Db\ResultSet\ResultSet;
use models\Table\TableClaimControl;
use Coral\Coral\History\CoralHistoryOrder;
use Zend\Http\Client;
use Zend\Json\Json;

/**
 * キャンセルクラス
 */
class LogicCancel
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
     * キャンセル申請
     *
     * @param int $oseq オーダーSEQ
     * @param string $reason キャンセル理由
     * @param int $reasonCode キャンセル理由コードKey
     * @param int $type
     * @param bool $transaction トランザクション処理を行うか否か
     * @param int $opId 担当者
     * @param int $isToDo
     * @param null $log
     * @return string '':成功 ''以外:失敗
     * @throws OrderCancelException
     */
	public function applies($oseq, $reason, $reasonCode = 0, $type = 0, $transaction = true, $opId, $isToDo = 0, $log = null)
	{
        $mdlo  = new TableOrder($this->_adapter);
        $mdlc  = new TableCancel($this->_adapter);
        $mdlps = new TablePayingAndSales($this->_adapter);
        $mdlsf = new TableStampFee($this->_adapter);
        $mdlcc = new TableClaimControl($this->_adapter);

        // 対象の注文データを取得
        // 子注文でも許可する(2015.08.03)
        $sql  = ' SELECT po.* ';
        $sql .= '   FROM T_Order o ';
        $sql .= '        INNER JOIN ( SELECT t.P_OrderSeq ';
        $sql .= '                           ,MAX(pas.PayingControlStatus) AS PayingControlStatus ';
        $sql .= '                           ,MAX(t.Rct_Status) AS Rct_Status ';
        $sql .= '                           ,MAX(t.Cnl_CantCancelFlg) AS Cnl_CantCancelFlg ';
        $sql .= '                           ,MIN(t.DataStatus) AS DataStatus ';
        $sql .= '                       FROM T_Order t ';
        $sql .= '                            LEFT OUTER JOIN T_PayingAndSales pas ';
        $sql .= '                                         ON t.OrderSeq = pas.OrderSeq ';
        $sql .= '                      WHERE t.Cnl_Status = 0 ';
        $sql .= '                        AND (t.DataStatus <= 61 OR (t.DataStatus = 91 AND t.CloseReason = 1) ) ';
        $sql .= '                        AND t.P_OrderSeq = (SELECT P_OrderSeq FROM T_Order WHERE OrderSeq = :OrderSeq) ';
        $sql .= '                      GROUP BY t.P_OrderSeq ';
        $sql .= '                   ) po ';
        $sql .= '                ON o.P_OrderSeq = po.P_OrderSeq ';
        $sql .= '  WHERE po.Cnl_CantCancelFlg != 1  ';
        $sql .= '    AND IFNULL(o.CombinedClaimTargetStatus,0) NOT IN (11, 12) ';
        $sql .= '    AND OrderSeq = :OrderSeq ';

        $prm = array(
                ':OrderSeq' => $oseq,
        );
        $stm = $this->_adapter->query($sql);
        $rs = new ResultSet();
        $ri = $stm->execute($prm);

        if (!($ri->count() > 0)) {
            throw new OrderCancelException('キャンセル申請できない注文Seqが指定されました。');
        }

        $result = '';
        if ($isToDo == 1) {
            $result = $this->processCancelTodo($oseq, $oseq, $opId, $log);
        }

        if (strlen($result) != 0) {
            return $result . '__sbps';
        }
        $orderData = $ri->current();

        try
        {
            // トランザクション開始
            if ($transaction) { $this->_adapter->getDriver()->getConnection()->beginTransaction(); }

            // 未印刷の請求書印刷予約データ存在確認を行う(2013.02.19)
            // 更新前に親注文で判定して保持しておく
            $mdlch = new TableClaimHistory($this->_adapter);
            $mdlri = new TableReclaimIndicate($this->_adapter);

            $printedTransFlg = 0;

            if($mdlch->getReservedCount($orderData['P_OrderSeq']) > 0) {
                $printedTransFlg = 1;
            }
            else {
                // 指示未処理の再請求指示データ存在確認を行う
                if($mdlri->getIndicateCount($orderData['P_OrderSeq']) > 0) {
                    $printedTransFlg = 2;
                }
            }



            // 印刷ステータス[2：CSV出力済み][3：PDF印刷済み]の状態である場合は、[印刷済みに更新]と同じ扱いとする
            $sql = " SELECT * FROM T_ClaimHistory WHERE PrintedFlg = 0 AND ValidFlg = 1 AND PrintedStatus IN (2, 3) AND OrderSeq = :OrderSeq ";
            $row_ch = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orderData['P_OrderSeq']))->current();
            if ($row_ch) {
                $this->cancelInJobtransfer($row_ch['OrderSeq'], $opId);
            }

            // 親・子注文レストを作成
            $cnlOrderSeqList = array();

// 2015/10/29 取りまとめ注文の一部キャンセルは認めない → 無条件で全キャンセルとする → 不要なﾛｼﾞｯｸはｺﾒﾝﾄｱｳﾄ（suzuki_y）
//             if ($orderData['Rct_Status'] == 1 || $orderData['PayingControlStatus'] == 1) {
//                 // 入金済み もしくは 立替済みの注文が存在する場合は、全キャンセル

            // キャンセル申請中のデータがいる場合は、取り消し→全キャンセル
            try {
                $this->cancelApplies($oseq, false, $opId);
            } catch(OrderCancelException $e) {
                // 取消失敗の場合はスルー
            } catch(\Exception $e) {
                // 例外の場合は上位へ投げる
                throw $e;
            }

            $sql = " SELECT * FROM T_Order o WHERE o.P_OrderSeq = :P_OrderSeq AND o.Cnl_Status IN ( 0, 1 ) ";
            $prm = array(
                'P_OrderSeq' => $orderData['P_OrderSeq'],
            );
            $orderList = $this->_adapter->query($sql)->execute($prm);
            //$orderList = $mdlo->findOrder(array('P_OrderSeq' => $orderData['P_OrderSeq'], 'Cnl_Status' => 0));

//             } else {
//                 $orderList = $mdlo->findOrder(array('OrderSeq' => $oseq, 'Cnl_Status' => 0));
//             }
// 2015/10/29 取りまとめ注文の一部キャンセルは認めない → 無条件で全キャンセルとする → 不要なﾛｼﾞｯｸはｺﾒﾝﾄｱｳﾄ（suzuki_y）
            foreach ($orderList as $order) {
                $cnlOrderSeqList[$order['OrderSeq']] = $order;
            }

            // キャンセルフェイズ判断用変数の設定
            $chgStatus = $orderData['PayingControlStatus']; // 立替済みか否か
            $rctStatus = 0;   // 0：未入金 or 一部入金、1：全入金
            if ($orderData['Rct_Status'] == 1) {
                // 全入金済み
                $rctStatus = 1;
            }

            // 代表注文SEQ（現時点で未キャンセル、申請中の最小注文SEQ）
            $sql = " SELECT MIN(OrderSeq) OrderSeq FROM T_Order WHERE P_OrderSeq = :P_OrderSeq AND Cnl_Status IN ( 0, 1 ) ";
            $dhyOrderSeq = $this->_adapter->query($sql)->execute(array(':P_OrderSeq' => $orderData['P_OrderSeq']))->current()['OrderSeq'];

            foreach ($cnlOrderSeqList as $row) {

                $oseq = $row['OrderSeq'];

                // 代表注文か否か
                $isDhyoOrder = $dhyOrderSeq == $row['OrderSeq'];

                if ($isToDo == 1) {
                    $mdlps->toCanceled( $oseq, $opId );
                    $mdlsf->toCanceled( $oseq, $opId );
                } else {
                    // 立替・売上データをキャンセル中にする。
                    $mdlps->toCanceling($oseq, $opId);
                    // 印紙代データをキャンセル中にする。
                    $mdlsf->toCanceling($oseq, $opId);
                }


                /*
                 * キャンセル管理データの作成
                */
                $cancelData['OrderSeq'] = $oseq;                    // 注文Seq
                $cancelData['CancelDate'] = date('Y-m-d H:i:s');    // キャンセル日

                // 各種返金額の初期値をセット
                $cancelData['RepayChargeAmount'] = 0;
                $cancelData['RepaySettlementFee'] = 0;
                $cancelData['RepayClaimFee'] = 0;
                $cancelData['RepayStampFee'] = 0;
                $cancelData['RepayDamageInterest'] = 0;
                $cancelData['RepayReClaimFee'] = 0;
                $cancelData['RepayDifferentialAmount'] = 0;
                $cancelData['RepayDepositAmount'] = 0;
                $cancelData['RepayReceiptAmount'] = 0;

                $cancelData['ApproveFlg'] = 0;
                $cancelData['ApprovalDate'] = null;
                $cancelData['ApproveOpId'] = null;

                // 請求管理データの取得
                $claimData = $mdlcc->findClaim(array('OrderSeq' => $oseq))->current();

                $extraPayType = $this->_adapter->query(" SELECT IFNULL(ExtraPayType,'0') AS ExtraPayType FROM AT_Order WHERE OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $oseq))->current()['ExtraPayType'];
                $isExtraPay = ($extraPayType == '1') ? true : false;

                // キャンセルフェイズと各返金額の判断
                if ($chgStatus == 1 && $isExtraPay) {
                    $cancelData['CancelPhase'] = 3;
                    if ($isToDo == 1) {
                        $cancelData['RepayChargeAmount'] = 0;
                    } else {
                        $cancelData['RepayChargeAmount'] = $row['Chg_ChargeAmount'];                    // 立替金額プラス値
                    }
                }
                else if ($chgStatus == 0 && $isExtraPay) {
                    $cancelData['CancelPhase'] = 4;
                    if ($isDhyoOrder) {
                        // 代表注文の場合
                        if ($isToDo == 1) {
                            $cancelData['RepayReceiptAmount'] = 0;
                        } else {
                            $cancelData['RepayReceiptAmount'] = nvl($claimData['ReceiptAmountTotal'], 0) * -1;   // 顧客入金額マイナス値
                        }
                    }
                }
                else if ($chgStatus == 1 && $rctStatus == 0)
                {
                    // Phase2 : 立替実行後・顧客入金前
                    $cancelData['CancelPhase'] = 2;

                    $cancelData['RepayChargeAmount'] = $row['Chg_ChargeAmount'];                    // 立替金額プラス値
                    if ($isDhyoOrder) {
                        // 代表注文の場合
                        $cancelData['RepayReceiptAmount'] = nvl($claimData['ReceiptAmountTotal'], 0) * -1 ;  // 顧客入金額マイナス値
                    }
                }
                else if ($chgStatus == 1 && $rctStatus == 1)
                {
                    // Phase3 : 立替実行後・顧客入金後
                    $cancelData['CancelPhase'] = 3;

                    if ($isToDo == 1) {
                        $cancelData['RepaySettlementFee'] = 0;
                        $cancelData['RepayClaimFee'] = 0;
                    } else {
                        // 立替・売上管理データの取得
                        $pas = $mdlps->findPayingAndSales(array('OrderSeq' => $oseq))->current();
                        $cancelData['RepaySettlementFee'] = $pas['SettlementFee'] * -1;                     // 決済手数料マイナス値
                        $cancelData['RepayClaimFee'] = $pas['ClaimFee'] * -1;                               // 請求手数料マイナス値
                    }

                    if ($isDhyoOrder) {
                        // 代表注文の場合
                        $cancelData['RepayDamageInterest'] = nvl($claimData['DamageInterestAmount'], 0) * -1;       // 遅延損害金マイナス値
                        $cancelData['RepayReClaimFee'] = (nvl($claimData['ClaimFee'], 0)  + nvl($claimData['AdditionalClaimFee'], 0) ) * -1;     // 再請求手数料マイナス値
                        $cancelData['RepayDifferentialAmount'] = ( $row['UseAmount']
                                                                   + nvl($claimData['DamageInterestAmount'], 0)
                                                                   + nvl($claimData['ClaimFee'], 0)
                                                                   + nvl($claimData['AdditionalClaimFee'], 0)
                                                                   - nvl($claimData['ReceiptAmountTotal'], 0) ) ;     // 入金差額プラス値
                    } else {
                        // 子注文の場合
                        $cancelData['RepayDifferentialAmount'] = $row['UseAmount'];                                           // 入金差額プラス値
                    }

// ↓↓↓過剰入金の場合にRepayDifferentialAmountと金額を打ち消しあって、計算が狂うので不要(20150813_1609_suzuki_h)
//                     if(nvl($claimData['ClaimedBalance'], 0) < 0) {
//                         $cancelData['RepayDepositAmount'] = nvl($claimData['ClaimedBalance'], 0) ;              // 預り金マイナス値
//                     }
// ↑↑↑過剰入金の場合にRepayDifferentialAmountと金額を打ち消しあって、計算が狂うので不要(20150813_1609_suzuki_h)
                }
                else if ($chgStatus == 0 && $rctStatus == 1)
                {
                    // Phase4 : 立替実行前・顧客入金後
                    $cancelData['CancelPhase'] = 4;

                    if ($isDhyoOrder) {
                        // 代表注文の場合
                        if ($isToDo == 1) {
                            $cancelData['RepayReceiptAmount'] = 0;
                        } else {
                            $cancelData['RepayReceiptAmount'] = nvl($claimData['ReceiptAmountTotal'], 0) * -1;         // 顧客入金額マイナス値
                        }
                    }
                }
                else
                {
                    // Phase1 : 立替実行前・顧客入金前
                    $cancelData['CancelPhase'] = 1;

                    if ($isDhyoOrder) {
                        // 代表注文の場合
                        $cancelData['RepayReceiptAmount'] = nvl($claimData['ReceiptAmountTotal'], 0) * -1;              // 顧客入金額マイナス値
                    }
                }

                if ($isToDo == 1) {
                    $cancelData['ApproveFlg'] = 1;
                    $cancelData['ApprovalDate'] = date('Y-m-d H:i:s');
                    $cancelData['ApproveOpId'] = $opId;
                }

                // 返金額の合計
                $cancelData['RepayTotal'] = $cancelData['RepayChargeAmount']
                + $cancelData['RepaySettlementFee']
                + $cancelData['RepayClaimFee']
                + $cancelData['RepayStampFee']
                + $cancelData['RepayDamageInterest']
                + $cancelData['RepayReClaimFee']
                + $cancelData['RepayDifferentialAmount']
                + $cancelData['RepayDepositAmount']
                + $cancelData['RepayReceiptAmount'];

                $cancelData['CancelReason'] = $reason;  // キャンセル理由
                //$cancelData['ApproveFlg'] = 0;          // キャンセル承認フラッグ
                $cancelData['KeepAnAccurateFlg'] = 0;   // 精算フラッグ
                if( $reasonCode > 0 ) {                 // キャンセル理由コードKey
                    $cancelData['CancelReasonCode'] = $reasonCode;
                }
                $cancelData['RegistId'] = $opId;
                $cancelData['UpdateId'] = $opId;

                // キャンセルデータがあった場合、キャンセル管理データを削除後追加処理を行う。キャンセル申請→取消しの想定 (2015/06/03)
                $this->_adapter->query( 'DELETE FROM T_Cancel WHERE OrderSeq = :OrderSeq' )->execute( array( ':OrderSeq' => $oseq ) );
                // キャンセル管理データをインサートする。
                $mdlc->saveNew($cancelData);

                //OEMID判定
                if($row['OemId'] != 0){

                    //OEM決済手数料データをキャンセル済みにする
                    $mdlosf = new TableOemSettlementFee($this->_adapter);

                    //OEM請求手数料データをキャンセル済みにする
                    $mdlocf = new TableOemClaimFee($this->_adapter);

                    if ($isToDo == 1) {
                        $mdlosf->toCanceled($oseq, $opId);
                        $mdlocf->toCanceled($oseq, $opId);
                    } else {
                        $mdlosf->toCanceling($oseq, $opId);
                        $mdlocf->toCanceling($oseq, $opId);
                    }
                }

                // 注文データのキャンセル関連のステータスを更新する。
                if ($isToDo == 1) {
                    $mdlo->saveUpdate(array('Cnl_Status' => 2,'Cnl_ReturnSaikenCancelFlg' => $type, 'PrintedTransBeforeCancelled' => $printedTransFlg, 'UpdateId' => $opId, 'DataStatus' => '91', 'CloseReason' => 2), $oseq);
                } else {
                    $mdlo->saveUpdate(array('Cnl_Status' => 1,'Cnl_ReturnSaikenCancelFlg' => $type, 'PrintedTransBeforeCancelled' => $printedTransFlg, 'UpdateId' => $opId), $oseq);
                }

                // 未印刷の請求書印刷予約データを削除
                $mdlch->deleteReserved($oseq, $opId);

                // 注文履歴へ登録
                $history = new CoralHistoryOrder($this->_adapter);
                $history->InsOrderHistory($oseq, 71, $opId);
                if ($isToDo == 1) {
                    // 注文履歴へ登録
                    $history->InsOrderHistory($oseq, 72, $opId);


                    // 口座振替アラートを論理削除する
                    $sql = ' UPDATE T_CreditTransferAlert SET UpdateDate=:UpdateDate, UpdateId=:UpdateId, ValidFlg=0 WHERE OrderSeq = :OrderSeq ';
                    $data = array(
                        ':UpdateDate' => date('Y-m-d H:i:s'),
                        ':UpdateId' => $opId,
                        ':OrderSeq' => $oseq
                    );
                    $this->_adapter->query($sql)->execute($data);
                }
            }

            if ($transaction) { $this->_adapter->getDriver()->getConnection()->commit(); }

        }
        catch(\Exception $e)
        {
            if ($transaction)
            {
                $this->_adapter->getDriver()->getConnection()->rollBack();
                $result = $e->getMessage();
            }
            else
            {
                throw $e;
            }
        }

        return $result;
	}

	/**
	 * キャンセル取消
	 *
	 * @param int $oseq オーダーSEQ
	 * @param int $opId 担当者
	 * @param bool $transaction トランザクション処理を行うか否か
	 * @return string '':成功 ''以外:失敗
	 */
	public function cancelApplies($oseq, $transaction = true, $opId)
	{
	    $mdlo  = new TableOrder($this->_adapter);
	    $mdlc  = new TableCancel($this->_adapter);
	    $mdlps = new TablePayingAndSales($this->_adapter);
	    $mdlsf = new TableStampFee($this->_adapter);

	    // 対象の注文データを取得
	    $sql  = " SELECT * FROM T_Order ";
	    $sql .= " WHERE  OrderSeq IN (:OrderSeq) AND Cnl_Status = 1 ";
	    $prm = array(
	            ':OrderSeq' => $oseq,
	    );
	    $ri = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $oseq));
	    if (!($ri->count() > 0)) {
	        throw new OrderCancelException('この注文は、他の端末からキャンセル申請取り消しされているか、または、キャンセル申請されていない注文です。');
	    }

	    $orderData = $ri->current();

// 取りまとめ注文の一部キャンセルは認めない → 無条件で全キャンセルとする → キャンセル取消も全注文を対象とする → 不要なﾛｼﾞｯｸはｺﾒﾝﾄｱｳﾄ
//	    // 親注文チェック：クローズしているとりまとめ注文は、代表注文以外キャンセルできません。
//	    if (( $orderData['DataStatus'] == 91 || $orderData['DataStatus'] == 61 ) && !( $oseq == $orderData['P_OrderSeq'] )){
//	        throw new OrderCancelException('クローズしているとりまとめ注文は、代表注文以外キャンセルできません。');
//	    }

	    try
	    {
	        // トランザクション開始
	        if ($transaction) { $this->_adapter->getDriver()->getConnection()->beginTransaction(); }

	        // 親・子注文レストを作成
	        $cnlOrderSeqList = array();

// 取りまとめ注文の一部キャンセルは認めない → 無条件で全キャンセルとする → キャンセル取消も全注文を対象とする → 不要なﾛｼﾞｯｸはｺﾒﾝﾄｱｳﾄ
//	        if ($orderData['DataStatus'] == 91 || $orderData['DataStatus'] == 61) {
	            $orderList = $mdlo->findOrder(array('P_OrderSeq' => $orderData['P_OrderSeq'], 'Cnl_Status' => 1));
	            foreach ($orderList as $order) {
	                $cnlOrderSeqList[$order['OrderSeq']] = $order;
	            }
//	        } else {
//	            $cnlOrderSeqList[$oseq] = $orderData;
//	        }

	        foreach ($cnlOrderSeqList as $cnlOrder) {
	            $oseq = $cnlOrder['OrderSeq'];

    	        // 立替・売上データを未キャンセルにする。
    	        $mdlps->setCancelFlg($oseq, 0, $opId);
    	        // 印紙代データを未キャンセルにする。
    	        $mdlsf->setCancelFlg($oseq, 0, $opId);

    	        // キャンセル管理データを論理削除する。 (2015/06/02)
    	        $mdlc->saveUpdateWhere( array( 'ValidFlg' => 0, 'UpdateId' => $opId, 'UpdateDate' => date('Y-m-d H:i:s') ), array( 'OrderSeq' => $oseq ) );

    	        //OEMID判定
    	        if($orderData['OemId'] != 0){

    	            //OEM決済手数料データをキャンセル済みにする
    	            $mdlosf = new TableOemSettlementFee($this->_adapter);
    	            $mdlosf->setCancelFlg($oseq, 0, $opId);

    	            //OEM請求手数料データをキャンセル済みにする
    	            $mdlosf = new TableOemClaimFee($this->_adapter);
    	            $mdlosf->setCancelFlg($oseq, 0, $opId);
    	        }

    	        // 未印刷の請求書印刷予約データ削除を行う(2013.02.19)
    	        $mdlch = new TableClaimHistory($this->_adapter);
    	        $mdlch->deleteReserved($oseq, $opId);

    	        // 注文データのキャンセル関連のステータスを更新する。
    	        $mdlo->saveUpdate(array('Cnl_Status' => 0, 'PrintedTransBeforeCancelled' => 0, 'UpdateId' => $opId), $oseq);

                // 注文履歴へ登録
                $history = new CoralHistoryOrder($this->_adapter);
                $history->InsOrderHistory($oseq, 73, $opId);
	        }

	        if ($transaction) { $this->_adapter->getDriver()->getConnection()->commit(); }

	    }
	    catch(\Exception $e)
	    {
	        if ($transaction)
	        {
	            $this->_adapter->getDriver()->getConnection()->rollBack();
	            $result = $e->getMessage();
	        }
	        else
	        {
	            throw $e;
	        }
	    }

	    return $result;
	}

	/**
	 * Job転送中のキャンセル処理
	 *
	 * @param int $oseq オーダーSEQ
	 * @param int $opId 担当者
	 */
	protected function cancelInJobtransfer($oseq, $opId)
	{
        // 請求関連処理SQL
        $stm = $this->_adapter->query($this->getBaseP_ClaimControl());

        // SQL実行結果取得用のSQL
        $getretvalsql = " SELECT @po_ret_sts AS po_ret_sts, @po_ret_errcd AS po_ret_errcd, @po_ret_sqlcd AS po_ret_sqlcd, @po_ret_msg AS po_ret_msg ";

        // 請求履歴データを取得
        $mdlch = new TableClaimHistory($this->_adapter);
        $data = $mdlch->findClaimHistory(array( 'PrintedFlg' => 0, 'ValidFlg' => 1, 'OrderSeq' => $oseq ))->current();

        // 請求関連処理呼び出し用パラメータの設定
        $prm = array(
            ':pi_history_seq'   => $data['Seq'],
            ':pi_button_flg'    => 1,
            ':pi_user_id'       => $opId,
        );

        $ri = $stm->execute($prm);

        // SQL実行例外なしもエラー戻り値の時は例外をｽﾛｰ
        $retval = $this->_adapter->query($getretvalsql)->execute(null)->current();
        if ($retval['po_ret_sts'] != 0) {
            throw new \Exception($retval['po_ret_msg']);
        }

        if ($data['ClaimPattern'] > 1) {
            // 再請求時

            // (紙請求ストップフラグの判定)
            $letterClaimStopFlg = 0;
            if (($data['OutOfAmends'] == 0 && $data['ClaimPattern'] >= 9) ||
                ($data['OutOfAmends'] == 1 && ($data['ClaimPattern'] >= 3 && $data['ClaimPattern'] < 9))) {
                $letterClaimStopFlg = 1;
            }

            // 注文データの更新
            $sql  = " UPDATE T_Order ";
            $sql .= " SET    LetterClaimStopFlg = :LetterClaimStopFlg ";
            $sql .= " ,      MailClaimStopFlg = 0 ";
            $sql .= " ,      UpdateId = :UpdateId ";
            $sql .= " ,      UpdateDate = :UpdateDate ";
            $sql .= " WHERE  P_OrderSeq = :OrderSeq ";

            $prm = array(
                ':LetterClaimStopFlg'   => $letterClaimStopFlg,
                ':UpdateId'             => $opId,
                ':UpdateDate'           => date('Y-m-d H:i:s'),
                ':OrderSeq'             => $oseq,
            );
            $this->_adapter->query($sql)->execute($prm);
        }

        // 備考に保存
        $mdlo = new TableOrder( $this->_adapter);
        $mdlo->appendPrintedInfoToOemNote($oseq);

        // 請求履歴．印刷ステータス(PrintedStatus)を"9"(印刷済み)に更新する
        $this->_adapter->query(" UPDATE T_ClaimHistory SET PrintedStatus = 9 WHERE Seq = :Seq ")->execute(array(':Seq' => $data['Seq']));
    }

    /**
     * 請求関連処理ファンクションの基礎SQL取得。
     *
     * @return 請求関連処理ファンクションの基礎SQL
     */
    protected function getBaseP_ClaimControl() {
        return <<<EOQ
CALL P_ClaimControl(
    :pi_history_seq
,   :pi_button_flg
,   :pi_user_id
,   @po_ret_sts
,   @po_ret_errcd
,   @po_ret_sqlcd
,   @po_ret_msg
    )
EOQ;
    }

    /**
     * (SB Payment Service)クレジットカード決済：取消返金要求
     *
     * @param array $params パラメタ
     * @param $resSBP
     * @param string $err_code エラーコード(8桁)
     * @param array $errorMessages エラーメッセージ文字列の配列(※通信障害系の内容)
     * @param $sbpsCancelApiId
     * @param $amount
     * @param null $log
     * @return boolean true;成功／false:失敗
     */
    protected function _SBPaymentCancelRequest($params, &$resSBP, &$err_code, &$errorMessages, $sbpsCancelApiId, $amount, $log = null) {

        $request_date = date('YmdHis');

        // チェックサム（項目順に結合＋ハッシュキー）
        $sha1str =  $params['merchantid']. $params['serviceid']. $params['sps_transaction_id']. $params['tracking_id']. $request_date. $request_date. '600'. $params['hashkey'];

        $req  = '<?xml version="1.0" encoding="Shift_JIS"?>';
        $req .= '<sps-api-request id="' . $sbpsCancelApiId . '">';
        $req .= '<merchant_id>'        . $params['merchantid']        . '</merchant_id>';
        $req .= '<service_id>'         . $params['serviceid']         . '</service_id>';
        $req .= '<sps_transaction_id>' . $params['sps_transaction_id']. '</sps_transaction_id>';
        $req .= '<tracking_id>'        . $params['tracking_id']       . '</tracking_id>';
        $req .= '<processing_datetime>'. $request_date                . '</processing_datetime>';
        $req .= '<request_date>'       . $request_date                . '</request_date>';
        //$req .= '<amount>'             . $amount                      . '</amount>';
        $req .= '<limit_second>600</limit_second>';
        $req .= '<sps_hashcode>'       . sha1($sha1str)               . '</sps_hashcode>'; // 40文字の16進数
        $req .= '</sps-api-request>';
        if (!is_null($log)) {
            $log->info('キャンセルAPIのリクエストXML:' . $req);
        }
        // リクエスト送信
        $xmlstr = '';
        $orderSeq = $params['OrderSeq'];
        $basicId = $params['BasicId'];
        $basicPw = $params['BasicPw'];
        $isSuccess = $this->_SBPaymentSendRequest($orderSeq, $basicId, $basicPw, $req, $xmlstr, $errorMessages, $log);
        if ($isSuccess == false) {
            return false;
        }
        if (!is_null($log)) {
            $log->info('キャンセルAPIのレスポンスXML:' . $xmlstr);
            $log->info('isSuccess:' . $isSuccess);
        }
        $xml = simplexml_load_string($xmlstr);
        $json = json_encode($xml);
        $resSBP = json_decode($json, true);

        if ($resSBP['res_result'] == 'NG') {
            $err_code = $resSBP['res_err_code'];
            return false;
        }

        return true;
    }

    /**
     * (SB Payment Service)リクエスト送信
     *
     * @param $orderSeq
     * @param $basicId
     * @param $basicPw
     * @param string $params オンライン決済ASPに渡すパラメータ
     * @param string $responseBody レスポンスデータ
     * @param $errorMessages
     * @param null $log
     * @return boolean true:成功／false:失敗
     */
    protected function _SBPaymentSendRequest($orderSeq, $basicId, $basicPw, $params, &$responseBody, &$errorMessages, $log = null) {

        // オンライン決済URL取得
        $url = $this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'sbpayment' AND Name = 'url' ")->execute(null)->current()['PropValue'];
        $timeout = $this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'sbpayment' AND Name = 'timeout' ")->execute(null)->current()['PropValue'];

        // (オンライン決済URL書式化 : T_Enterpriseに設定される[Basic認証ID][Basic認証PW]でﾌﾟﾚｰｽﾌｫﾙﾀﾞを置き換える)
        $url = sprintf($url, $basicId, $basicPw );

        $option = array(
            'adapter'=> 'Zend\Http\Client\Adapter\Curl', // SSL通信用に差し替え
            'ssltransport' => 'tls',
            'maxredirects' => 1,                         // 試行回数(maxredirects) を 1 に設定
        );

        $client = new Client($url, $option);
        $client->setOptions(array('timeout' => (int)$timeout, 'keepalive' => true, 'maxredirects' => 1));

        try {
            if (!is_null($log)) {
                $log->info('params : '.$params);
            }
            // データ送信を実行する
            $response = $client
                ->setRawBody($params)
                ->setEncType('application/xml; charset=UTF-8', ';')
                ->setMethod('Post')
                ->send();

            // 結果を取得する
            $status = $response->getStatusCode();
            $res_msg = $response->getReasonPhrase();
            $res_msg = mb_convert_encoding($res_msg, mb_internal_encoding(), BaseIOUtility::detectEncoding($res_msg));

            if (!is_null($log)) {
                $log->info('response : '.$response);
            }

            if ($status == 200) {
                $responseBody =  $response->getBody();
                return true;
            }

            $errorMessages = 'オンライン決済通信エラー（';
            $errorMessages .= 'ステイタス : ' . $status;
            $errorMessages .= '、メッセージ : ' . $res_msg;
            $errorMessages .= '）';
            return false;
        }
        catch (\Exception $err) {
            $errorMessages = 'オンライン決済通信エラー（データ送信に失敗しました）';
            if (!is_null($log)) {
                $log->info($err->getMessage());
            }
            return false;
        }
    }


    /**
     * SBPS-29: api request cancel
     * @param $oseq
     * @param $p_oseq
     * @param $userId
     * @param null $log
     * @return string
     * @throws \Exception
     */
    private function processCancelTodo($oseq, $p_oseq, $userId, $log = null) {
        $result = '';
        $mdlo  = new TableOrder($this->_adapter);
        $mdlc  = new TableCancel($this->_adapter);
        $mdlSBPsrh = new TableSBPaymentSendResultHistory( $this->_adapter );
        $history = new CoralHistoryOrder($this->_adapter);

        // すべての注文がキャンセルされているか確認する
        $sql = ' SELECT COUNT(1) CNT FROM T_Order WHERE P_OrderSeq = :P_OrderSeq AND Cnl_Status <> 2 ';
        $orderCnt = $this->_adapter->query($sql)->execute(array(':P_OrderSeq' => $p_oseq))->current()['CNT'];

        // SMBC決済ステーションへ取り消し依頼をする（20150813_1704_suzuki_h）
        // すべての注文がキャンセルされている場合
        if ( $orderCnt == 0 ) {
            // 請求中のJNB口座もクローズする
            $reason = $mdlc->findCancel(array('OrderSeq' => $oseq))->current()['CancelReason'];
            $jnbAccLogic = new LogicJnbAccount($this->_adapter);
            $jnbAccLogic->closeAccount($p_oseq, LogicJnbAccount::CLOSE_BY_CANCEL, sprintf("キャンセル理由 '%s' によってキャンセル", $reason));

            // 請求中のSMBCバーチャル口座もクローズする
            $reason = $mdlc->findCancel(array('OrderSeq' => $oseq))->current()['CancelReason'];
            $smbcpaAccLogic = new LogicSmbcpaAccount($this->_adapter);
            $smbcpaAccLogic->closeAccount($p_oseq, LogicSmbcpaAccount::CLOSE_BY_CANCEL, sprintf("キャンセル理由 '%s' によってキャンセル", $reason));
        }

        // 注文_会計の取得
        $mdlao = new ATableOrder( $this->_adapter );
        $aoInfo = $mdlao->find( $oseq )->current();
        // 届いてから払いのクレジット払いトラッキングIDの取得
        $trackingId = '';
        if ( !empty($aoInfo['ExtraPayType']) && $aoInfo['ExtraPayType'] == '1' ) {
            $trackingId = $aoInfo['ExtraPayKey'];
            $extraPayNote = Json::decode( $aoInfo['ExtraPayNote'] );
            $spsTransactionId = $extraPayNote->sps_transaction_id;
        }

        // クレジットカード決済【取消返金要求】を行う
        if ( !empty($trackingId) ) {
            // トラッキングIDを保持している場合
            // 注文情報の取得
            $orderInfo = $mdlo->find( $oseq )->current();

            // サイト情報の取得
            $mdlsit = new TableSite($this->_adapter);
            $siteInfo = $mdlsit->findSite($orderInfo['SiteId'])->current();

            $params['OrderSeq']    = $oseq;
            $params['tracking_id'] = $trackingId;
            $params['BasicId']     = $siteInfo['BasicId'];
            $params['BasicPw']     = $siteInfo['BasicPw'];
            $params['sps_transaction_id'] = $spsTransactionId;
            $params['merchantid']  = $siteInfo['MerchantId']; // マーチャントID
            $params['serviceid']   = $siteInfo['ServiceId'];  // サービスID
            $params['hashkey']     = $siteInfo['HashKey'];    // ハッシュキー
            if (!is_null($log)) {
                $log->info("キャンセルAPIのパラメーター:" . Json::encode($params));
            }

            $sbpsCancelApiId = $this->getCancelApiId($oseq);
            $amount = $this->getClaimedBalance($oseq);
            $rtn = $this->_SBPaymentCancelRequest($params, $resSBP, $err_code, $errorMessages, $sbpsCancelApiId, $amount, $log);
            if (!is_null($log)) {
                $log->info("キャンセルAPIのレスポンス" . Json::encode($resSBP));
            }

            // 連携履歴の取得
            $sbpsrHistory = $mdlSBPsrh->findOrderSeq( $oseq )->current();
            $sbpsrHistoryCnt = $mdlSBPsrh->findOrderSeq( $oseq )->count();
            // 連携履歴の登録
            if ( !empty($resSBP) ) {
                $sbpsrHistory['ResSpsTransactionId'] = (empty($resSBP['res_sps_transaction_id']) ? null : $resSBP['res_sps_transaction_id']);
                $sbpsrHistory['ResProcessDate'] = (empty($resSBP['res_process_date']) ? null : $resSBP['res_process_date']);
                $sbpsrHistory['ResErrCode'] = (empty($resSBP['res_err_code']) ? null : $resSBP['res_err_code']);
                $sbpsrHistory['ResDate']    = $resSBP['res_date'];
                $sbpsrHistory['ResResult'] = $resSBP['res_result'];
            } else {
                $sbpsrHistory['ResSpsTransactionId'] = null;
                $sbpsrHistory['ResProcessDate'] = null;
                $sbpsrHistory['ResErrCode'] = null;
                $sbpsrHistory['ResDate']    = null;
                $sbpsrHistory['ResResult'] = 'NG';
            }

            $sbpsrHistory['ErrorMessage']  = $errorMessages;

            if (!is_null($log)) {
                $log->info("Error code: " . $err_code);
                $log->info("Error Mess: " . $sbpsrHistory['ErrorMessage'] );
            }

            $sbpsrHistory['UpdateId']  = $userId;

            if ( $sbpsrHistoryCnt <= 0 ) {
                $sbpsrHistory['OrderSeq']  = $oseq;
                $sbpsrHistory['OrderId']   = $orderInfo['OrderId'];
                $sbpsrHistory['RegistId']  = $userId;
                $mdlSBPsrh->saveNew( $sbpsrHistory );
            } else {
                $sbpsrSeq = $sbpsrHistory['Seq'];
                $mdlSBPsrh->saveUpdate( $sbpsrHistory, $sbpsrSeq );
            }
        }
        if (!empty($err_code)) {
            $result = $err_code;
        } else {
            if (!empty($errorMessages)) {
                return '99999999';
            }
        }

        return $result;
    }

    /**
     * Check order if use todoItekara to pay
     * @param $orderSeq
     * @return bool
     */
    public function _usedTodo2Pay($orderSeq) {
        $sql = "SELECT ExtraPayType FROM AT_Order WHERE OrderSeq = :OrderSeq";
        $row = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orderSeq))->current();
        if ($row['ExtraPayType']) {
            return true;
        }
        return false;
    }

    private function getCancelApiId($orderSeq) {
        $sql = "SELECT ExtraPayNote FROM AT_Order WHERE OrderSeq = :OrderSeq AND ExtraPayType = 1";
        $row = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orderSeq))->current();
        $extraPayNote = Json::decode( $row['ExtraPayNote'], true);
        if(isset($extraPayNote['res_pay_method'])) {
            $payMethod = $extraPayNote['res_pay_method'];
        } elseif (isset($extraPayNote['payment_method'])) {
            $payMethod = $extraPayNote['payment_method'];
        } else {
            $payMethod  = null;
        }
        $cancelApiId = null;
        if (!empty($payMethod)) {
            $sql = "SELECT DISTINCT CancelApiId FROM M_SbpsPayment WHERE PaymentName = :PaymentName";
            $row = $this->_adapter->query($sql)->execute(array(':PaymentName' => $payMethod))->current();
            $cancelApiId = $row['CancelApiId'];
        }

        return $cancelApiId;
    }

    private function getClaimedBalance($orderSeq) {
        $sql = "SELECT ClaimedBalance FROM T_ClaimControl WHERE OrderSeq = :OrderSeq";
        $row = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orderSeq))->current();
        return $row['ClaimedBalance'];
    }


    /**
     * (SB Payment Service)エラー情報生成
     *
     * @param string $err_code エラーコード(8桁)
     * @return string
     */
    public function _SBPaymentMakeErrorInfo($err_code) {

        $messages = "";
        $sql     = " SELECT KeyContent FROM M_Code WHERE CodeId = :CodeId AND Class1 = :Class1 ";
        $sql_knd = " SELECT KeyContent FROM M_Code WHERE CodeId = :CodeId AND Class1 = :Class1 AND Class2 = :Class2 ";

        // 決済手段
        $knd = substr($err_code, 0, 3);
        $message = '決済手段 : ';
        $row = $this->_adapter->query($sql)->execute(array(':CodeId' => 200, ':Class1' => $knd))->current();
        $message .= (($row) ? $row['KeyContent'] : '');
        $messages .= $message .PHP_EOL;

        // 種別
        $key = substr($err_code, 3, 2);
        $message = '種別 : ';
        $row = $this->_adapter->query($sql_knd)->execute(array(':CodeId' => 201, ':Class1' => $key, ':Class2' => $knd))->current();
        if ($row) {
            $message .= $row['KeyContent'];// (決済手段固有の情報)
        }
        else {
            $row = $this->_adapter->query($sql)->execute(array(':CodeId' => 201, ':Class1' => $key))->current();
            $message .= (($row) ? $row['KeyContent'] : '');
        }
        $messages .= $message .PHP_EOL;

        // 項目
        $key = substr($err_code, 5, 3);
        $message = '項目 : ';
        $row = $this->_adapter->query($sql_knd)->execute(array(':CodeId' => 202, ':Class1' => $key, ':Class2' => $knd))->current();
        if ($row) {
            $message .= $row['KeyContent'];// (決済手段固有の情報)
        }
        else {
            $row = $this->_adapter->query($sql)->execute(array(':CodeId' => 202, ':Class1' => $key))->current();
            $message .= (($row) ? $row['KeyContent'] : '');
        }

        $messages .= $message;

        return $messages;
    }

    /**
     * @param $err_code
     * @return string
     */
    public function _SBPaymentMakeErrorInfoForAjax($err_code) {

        $messages = "";
        $sql     = " SELECT KeyContent FROM M_Code WHERE CodeId = :CodeId AND Class1 = :Class1 ";
        $sql_knd = " SELECT KeyContent FROM M_Code WHERE CodeId = :CodeId AND Class1 = :Class1 AND Class2 = :Class2 ";
        $knd = substr($err_code, 0, 3);
        $key = substr($err_code, 3, 2);
        $message = '';
        $row = $this->_adapter->query($sql_knd)->execute(array(':CodeId' => 201, ':Class1' => $key, ':Class2' => $knd))->current();
        if ($row) {
            $message .= $row['KeyContent'];// (決済手段固有の情報)
        }
        else {
            $row = $this->_adapter->query($sql)->execute(array(':CodeId' => 201, ':Class1' => $key))->current();
            $message .= (($row) ? $row['KeyContent'] : '');
        }
        $messages .= $message;

        return $messages;
    }
}

class OrderCancelException extends \Exception
{

}
