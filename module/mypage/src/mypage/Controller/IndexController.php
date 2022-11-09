<?php
namespace mypage\Controller;

use mypage\Application;
use Coral\Coral\Controller\CoralControllerAction;

class IndexController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';

    /**
     * @var Application
    */
    private $app;

    /**
     * 初期化処理
    */
    protected function _init() {
        $this->app = Application::getInstance();

        // ページタイトルとスタイルシート、JavaScriptを設定
        if ($this->is_mobile_request())
        {
            $this->addStyleSheet( './css_sp/mypage.css' )
                 ->addStyleSheet( './css_sp/mypage_index.css' );
        }
        else
        {
            $this->addStyleSheet( './css/mypage.css' )
                 ->addStyleSheet( './css/mypage_index.css' );
        }
        $this->addJavaScript( '../js/prototype.js' )
            ->addJavaScript( '../js/bytefx.js' )
            ->addJavaScript( '../js/json+.js' )
            ->addJavaScript( '../js/corelib.js' )
            ->addJavaScript( '../js/base.ui.js' );

        $this->userInfo = Application::getInstance()->authManager->getUserInfo();
        $this->altUserInfo = Application::getInstance()->authManager->getAlternativeUserInfo();
    }

    /**
     * 購入履歴表示
    */
    public function indexAction() {
        $userInfo = $this->altUserInfo ?
            sprintf( '%s %s(%s)　様', $this->userInfo->NameSeiKj, $this->userInfo->NameMeiKj, $this->altUserInfo->NameKj ) :
            sprintf( '%s%s　様', $this->userInfo->NameSeiKj , $this->userInfo->NameMeiKj );

        // 注文履歴
        $sql = <<<EOQ
SELECT o.*
,      cc.ClaimDate
,      cc.ClaimCpId
,      cc.ClaimPattern
,      cc.LimitDate
,      cc.UseAmountTotal
,      cc.DamageDays
,      cc.DamageBaseDate
,      cc.DamageInterestAmount
,      cc.ClaimFee
,      cc.AdditionalClaimFee
,      cc.PrintedDate
,      cc.ClaimAmount
,      cc.ReceiptAmountTotal
,      cc.ClaimedBalance
,      cc.Clm_Count
,      cc.F_ClaimDate
,      cc.F_LimitDate
,      cc.ReissueClass
,      cc.ReissueRequestDate
,      cc.MypageReissueClass
,      cc.MypageReissueRequestDate
,      s.SiteNameKj
,      s.Url
,      DATE_FORMAT(cnl.CancelDate, '%Y-%m-%d') AS CancelDate
,      (SELECT MAX(ReceiptDate) FROM MV_ReceiptControl WHERE OrderSeq = o.OrderSeq AND ReceiptDate IS NOT NULL) AS MAXReceiptDate
,      e.EnterpriseNameKj
,      (SELECT MAX(ClaimDate) FROM MV_ClaimHistory WHERE OrderSeq = o.OrderSeq AND ClaimPattern = 1) AS MAXClaimDate
FROM   MV_Order o INNER JOIN
       MV_Customer c ON ( c.OrderSeq = o.OrderSeq ) LEFT OUTER JOIN
       MV_ClaimControl cc ON ( cc.OrderSeq = o.OrderSeq ) LEFT OUTER JOIN
       MV_Site s ON ( s.SiteId = o.SiteId ) LEFT OUTER JOIN
       MV_Cancel cnl ON ( cnl.OrderSeq = o.OrderSeq AND cnl.ValidFlg = 1 ) LEFT OUTER JOIN
       MV_Enterprise e ON ( e.EnterpriseId = s.EnterpriseId ) LEFT OUTER JOIN
       MV_EnterpriseCustomer ec ON ( ec.EntCustSeq = c.EntCustSeq ) LEFT OUTER JOIN
       MV_ManagementCustomer mc ON ( mc.ManCustId = ec.ManCustId )
WHERE  mc.ManCustId = :ManCustId AND
       IFNULL( o.OemId, 0 ) = :OemId AND
       IFNULL( o.CloseReason, 0 ) <> 3 AND
       o.ValidFlg = 1 AND
       c.ValidFlg = 1
GROUP BY o.P_OrderSeq
ORDER BY o.OrderSeq
EOQ;

        if( empty( $this->userInfo->OemId ) ) {
            $oemId = 0;
        }
        else {
            $oemId = $this->userInfo->OemId;
        }
        $prm = array(
            ':ManCustId' => $this->userInfo->ManCustId,
            ':OemId' => $oemId,
        );
        $orderList = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( $prm ) );

        // 注文状態
        $sql = <<<EOQ
SELECT o.OrderSeq
,      o.Cnl_Status
,      o.DataStatus
,      o.Rct_Status
FROM   MV_Order o
WHERE  o.P_OrderSeq = :OrderSeq
ORDER BY o.OrderSeq
EOQ;
        $stm = $this->app->dbAdapter->query( $sql );
        foreach( $orderList as $order ) {
            $prm = array( ':OrderSeq' => $order['OrderSeq'] );
            $orderStatuses = ResultInterfaceToArray( $stm->execute( $prm ) );
            $cnlStatusJudge = array(0 => 0, 1 => 0, 2 => 0);
            $DataStatusJudge = 0;
            $RctStatusJudge = 0;
            foreach( $orderStatuses as $orderSt ) {
                // キャンセル状態
                $cnlStatusJudge[$orderSt['Cnl_Status']]++;

                // データステータス
                if ($orderSt['Cnl_Status'] == 0 && $DataStatusJudge < $orderSt['DataStatus']) {
                    $DataStatusJudge = $orderSt['DataStatus'];
                }

                // 入金済み
                if ($orderSt['Rct_Status'] == 1) {
                    $RctStatusJudge++;
                }
            }

            // キャンセル状態
            // 全明細がキャンセル済み→キャンセル済み
            $orderStatusesCount = 0;
            if(!empty($orderStatuses)) {
                $orderStatusesCount = count($orderStatuses);
            }

            if ($cnlStatusJudge[2] == $orderStatusesCount) {
                $orderStatus[$order['OrderSeq']]['Cnl_Status'] = 2;
            }
            // 全明細がキャンセル申請以降→キャンセル申請中
            elseif (($cnlStatusJudge[2] + $cnlStatusJudge[1]) == $orderStatusesCount) {
                $orderStatus[$order['OrderSeq']]['Cnl_Status'] = 1;
            }
            else {
                $orderStatus[$order['OrderSeq']]['Cnl_Status'] = 0;
            }

            // データステータス
            // 有効な注文で最大のステータスを使用。全てキャンセルされている場合は先頭を使用
            $orderStatus[$order['OrderSeq']]['DataStatus'] = $DataStatusJudge > 0 ? $DataStatusJudge : $orderStatuses[0]['DataStatus'];

            // 1レコードでも入金済みがあれば入金済み
            $orderStatus[$order['OrderSeq']]['Rct_Status'] = $RctStatusJudge > 0 ? 1 : 0;
        }

        // 注文を注文状態を参照してソート
        $orderList1 = array();  // 支払い前
        $orderList2 = array();  // 支払い済み・キャンセル済み
        foreach ($orderList as $order ) {
            $cnlStatus = $orderStatus[$order['OrderSeq']]['Cnl_Status'];
            $dataStatus = $orderStatus[$order['OrderSeq']]['DataStatus'];
            $rctStatus = $orderStatus[$order['OrderSeq']]['Rct_Status'];
            $claimedBalance = $order['ClaimedBalance'];

            // キャンセル済み、支払い済み
            if (   $cnlStatus >= 1
                || ($dataStatus >= 51 && $rctStatus == 1 && $claimedBalance <= 0)
               )
            {
                $orderList2[] = $order;
            }
            // 支払い前
            else {
                $orderList1[] = $order;
            }
        }
        // 支払い前は注文日で昇順
        array_multisort(array_map(function ($i) { return $i['ReceiptOrderDate']; }, $orderList1), SORT_ASC
                      , array_map(function ($i) { return $i['OrderSeq']; }, $orderList1), SORT_ASC
                      , $orderList1
        );

        // 支払い済み・キャンセル済みは注文日で降順
        array_multisort(array_map(function ($i) { return $i['ReceiptOrderDate']; }, $orderList2), SORT_DESC
                      , array_map(function ($i) { return $i['OrderSeq']; }, $orderList2), SORT_DESC
                      , $orderList2
        );
        // 1つにまとめる
        $orderList = array_merge($orderList1, $orderList2);

        // 注文商品と配送先取得
        $sql = <<<EOQ
SELECT i.OrderSeq
,      i.ItemNameKj
,      i.UnitPrice
,      i.ItemNum
,      i.SumMoney
,      i.DataClass
,      dd.DestNameKj
,      dd.PostalCode
,      dd.UnitingAddress
,      dd.Phone
,      o.Cnl_Status
FROM   MV_OrderItems i INNER JOIN
       MV_Order o ON o.OrderSeq = i.OrderSeq INNER JOIN
       MV_DeliveryDestination dd ON dd.DeliDestId = ( SELECT DeliDestId FROM MV_OrderItems WHERE OrderSeq = o.OrderSeq AND DataClass = 1 AND ValidFlg = 1 LIMIT 1 )
WHERE  o.P_OrderSeq = :OrderSeq AND
       i.ValidFlg = 1 AND
       o.ValidFlg = 1 AND
       dd.ValidFlg = 1
ORDER BY i.OrderSeq, i.DataClass, i.OrderItemId
EOQ;
        $stm = $this->app->dbAdapter->query( $sql );
        foreach( $orderList as $order ) {
            $prm = array( ':OrderSeq' => $order['OrderSeq'] );
            $orderItems[$order['OrderSeq']] = ResultInterfaceToArray( $stm->execute( $prm ) );
            // 送料 と 決済手数料 と 税額 算出
            foreach( $orderItems[$order['OrderSeq']] as $orderItem ) {
                if( $orderItem['DataClass'] == 1 ) {
                    $deliList[$order['OrderSeq']][$orderItem['OrderSeq']][] = $orderItem;
                }
                else if( $orderItem['DataClass'] == 2 ) {
                    if ($orderStatus[$order['OrderSeq']]['Cnl_Status'] == 0) {
                        $sumCarriage[$order['OrderSeq']] += $orderItem['Cnl_Status'] == 0 ? $orderItem['SumMoney'] : 0;
                    }
                    else {
                        $sumCarriage[$order['OrderSeq']] += $orderItem['SumMoney'];
                    }
                }
                else if( $orderItem['DataClass'] == 3 ) {
                    if ($orderStatus[$order['OrderSeq']]['Cnl_Status'] == 0) {
                        $sumSettlementFee[$order['OrderSeq']] += $orderItem['Cnl_Status'] == 0 ? $orderItem['SumMoney'] : 0;
                    }
                    else {
                        $sumSettlementFee[$order['OrderSeq']] += $orderItem['SumMoney'];
                    }
                }
                else if( $orderItem['DataClass'] == 4 ) {
                    if ($orderStatus[$order['OrderSeq']]['Cnl_Status'] == 0) {
                        $sumTax[$order['OrderSeq']] += $orderItem['Cnl_Status'] == 0 ? $orderItem['SumMoney'] : 0;
                    }
                    else {
                        $sumTax[$order['OrderSeq']] += $orderItem['SumMoney'];
                    }
                }
                if ($orderStatus[$order['OrderSeq']]['Cnl_Status'] == 0) {
                    $orderClaimAmount[$order['OrderSeq']] += $orderItem['Cnl_Status'] == 0 ? $orderItem['SumMoney'] : 0;
                }
                else {
                    $orderClaimAmount[$order['OrderSeq']] += $orderItem['SumMoney'];
                }
            }

            // 請求金額に再請求手数料、遅延損害金、追加請求手数料を含める
            $orderClaimAmount[$order['OrderSeq']] += nvl($order['ClaimFee'], 0) + nvl($order['DamageInterestAmount'], 0) + nvl($order['AdditionalClaimFee'], 0);
        }

        $this->setPageTitle( 'ご購入履歴' );
        $this->view->assign( 'userInfo', $userInfo );
        $this->view->assign( 'orderList', $orderList );
        $this->view->assign( 'orderItems', $orderItems );
        $this->view->assign( 'sumCarriage', $sumCarriage );
        $this->view->assign( 'sumSettlementFee', $sumSettlementFee );
        $this->view->assign( 'sumTax', $sumTax );
        $this->view->assign( 'deliList', $deliList );
        $this->view->assign( 'orderStatus', $orderStatus);
        $this->view->assign( 'orderClaimAmount', $orderClaimAmount);
        return $this->view;
    }
}
