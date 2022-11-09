<?php
namespace orderpage\Controller;

use orderpage\Application;
use Coral\Coral\Controller\CoralControllerAction;
use models\View\MypageViewOemClaimAccountInfo;
use models\View\MypageViewOrder;
use models\View\MypageViewCustomer;
use models\View\MypageViewSystemProperty;
use models\Table\TableMypageToBackIF;
use Zend\Json\Json;

class BillReIssController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';

    /**
     * @var Application
    */
    private $app;

    /**
     * @var string
    */
    private $title;

    /**
     * @var string
     * お届け先住所に変更の場合,true
     */
    private $AnotherDeliUpdFlg = '1';

    /**
     * 初期化処理
    */
    protected function _init() {
        $this->app = Application::getInstance();

        $billreissFlg = 'true';
        $this->view->assign( 'billreissFlg', $billreissFlg );

        $this->userInfo = Application::getInstance()->authManager->getUserInfo();
        $this->altUserInfo = Application::getInstance()->authManager->getAlternativeUserInfo();

        // 購入者情報取得
        $mdlc = new MypageViewCustomer( $this->app->dbAdapter );
        $customerInfo = $mdlc->findCustomer( array( 'OrderSeq' => $this->userInfo->OrderSeq ) )->current();

        // OemId取得
        $mdlo = new MypageViewOrder( $this->app->dbAdapter );
        $oemId = $mdlo->find( $this->userInfo->OrderSeq )->current()['OemId'];

        // ページタイトルとスタイルシート、JavaScriptを設定
        if ($this->is_mobile_request()) {
            $this->addStyleSheet( './css_sp/orderpage.css' )
                 ->addStyleSheet( './css_sp/orderpage_billreiss.css')
                 ->addStyleSheet( './css_sp/orderpage_index.css' );
        } else {
            $this->addStyleSheet( './css/orderpage.css' )
                 ->addStyleSheet( './css/orderpage_billreiss.css')
                 ->addStyleSheet( './css/orderpage_index.css' );
        }
        $this->addJavaScript( '../js/prototype.js' )
             ->addJavaScript( '../js/bytefx.js' )
             ->addJavaScript( '../js/json+.js' )
             ->addJavaScript( '../js/corelib.js' )
             ->addJavaScript( '../js/base.ui.js' );

        // タイトル文字取得
        $this->title = $this->altUserInfo ?
           sprintf( '%s (%s)　様', $customerInfo['NameKj'], $this->altUserInfo->NameKj ) :
           sprintf( '%s　様', $customerInfo['NameKj'] );
    }

    /**
     * 請求書再発行画面の表示
    */
    public function indexAction() {
        $params = $this->getParams();

        $orderSeqA = $params['oseq'];
        $orderSeqB = $this->userInfo->OrderSeq;

        if ( empty( $orderSeqA ) ) {
            $this->setTemplate( 'error' );
            return $this->view;

        } else if ( empty( $orderSeqB )
                 || ( $orderSeqA <> $orderSeqB )
        ) {
            $this->_redirect( 'login/login' );
            return;

        }

        $orderSeq = $this->userInfo->OrderSeq;

        $errors = array();
        // 注文SEQが指定されていなかった場合
        if( !isset( $orderSeq ) ) {
            $this->setTemplate( 'error' );

            return $this->view;
        }

        // 請求情報取得
        $sql = <<<EOQ
SELECT o.OrderSeq
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
,      cc.MypageReissueRequestDate
,      cc.MypageReissueDate
,      cc.MypageReissueClass
FROM   MV_Order o INNER JOIN
       MV_Customer c ON ( c.OrderSeq = o.OrderSeq ) LEFT OUTER JOIN
       MV_ClaimControl cc ON ( cc.OrderSeq = o.P_OrderSeq )
WHERE  o.OrderSeq = :OrderSeq AND
       o.ValidFlg = 1 AND
       c.ValidFlg = 1
EOQ;

        $prm = array( ':OrderSeq' => $orderSeq );
        $claimInfo = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

        // 指定された注文SEQがユーザーの注文SEQと一致しなかった場合
        if( empty( $claimInfo ) ) {
            $this->setTemplate( 'error' );

            return $this->view;
        }

        // OEM請求口座取得　最新を表示
        $mdlocai = new MypageViewOemClaimAccountInfo( $this->app->dbAdapter );
        $accountInfos = $mdlocai->findByOrderSeq( $orderSeq )->current();
        $accountInfos['Yu_AccountNumber'] = '00120-7-670031';
        $accountInfos['Yu_SubscriberName'] = '株式会社キャッチボール';

        $sysProps = new MypageViewSystemProperty( $this->app->dbAdapter );
        $reClaimCautionMessageA = $sysProps->getValue('[DEFAULT]', 'systeminfo', 'ReClaimCautionMessageA');
        $reClaimCautionMessageB = $sysProps->getValue('[DEFAULT]', 'systeminfo', 'ReClaimCautionMessageB');
        $reIssobj = $sysProps->getValue('[DEFAULT]', 'systeminfo', 'MypageReissueClaimPattern');

        // 請求書再発行を続けるボタンを表示するフラグ
        $displayFlg = 1;            // 1:表示
        if ( (isset( $claimInfo['MypageReissueRequestDate'] ) && ( $claimInfo['MypageReissueClass'] != 91 && $claimInfo['MypageReissueClass'] != 92 ))
        || (isset( $claimInfo['MypageReissueRequestDate'] ) && ( $claimInfo['MypageReissueClass'] == 91 || $claimInfo['MypageReissueClass'] == 92 ))
        || ($claimInfo['ClaimPattern'] > $reIssobj))
        {
            $displayFlg = 0;            // 1:表示しない
        }

        $this->setPageTitle( '請求書再発行' );
        $this->view->assign( 'userInfo', $this->title );
        $this->view->assign( 'OrderSeq', $this->userInfo->OrderSeq );
        $this->view->assign( 'claimInfo', $claimInfo );
        $this->view->assign( 'accountInfos', $accountInfos );
        $this->view->assign( 'displayFlg', $displayFlg );
        $this->view->assign( 'ReClaimCautionMessageA', $reClaimCautionMessageA );
        $this->view->assign( 'ReClaimCautionMessageB', $reClaimCautionMessageB );
        $this->view->assign( 'ReIssobj', $reIssobj );

        return $this->view;
    }


    /**
     * 請求書再発行内容の確認
    */
    public function confirmAction() {
        $params = $this->getParams();
        $orderSeq = $params['orderseq'];
        $reason = $params['reason'];
        $change = $claim['claim'];
        $anotherDeliUpdFlg = $params['anotherDeliUpdFlg'];

        $orderSeqA = $params['orderseq'];
        $orderSeqB = $this->userInfo->OrderSeq;

        if ( empty( $orderSeqA )
          || empty( $orderSeqB )
          || ( $orderSeqA <> $orderSeqB )
        ) {
            $this->_redirect( 'login/login' );
            return;
        }

        if( !isset( $reason ) ) {
            $this->setTemplate( 'error' );

            return $this->view;
        }

        $sql = <<<EOQ
SELECT o.*
,      c.NameKj
,      c.PostalCode
,      c.UnitingAddress
,      c.Phone
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
,      s.SiteNameKj
,      s.Url
/* 2015/07/14 ｷｬﾝﾍﾟｰﾝ期間中はｷｬﾝﾍﾟｰﾝ情報を表示 */
,      (CASE WHEN IFNULL(ECampaign.cnt, 0) >= 1
             THEN IFNULL(ECampaign.ReClaimFee, 0)
             WHEN s.ReClaimFeeSetting = '1' AND s.ReClaimFeeStartRegistDate <= o.RegistDate AND s.ReClaimFeeStartDate <= o.ReceiptOrderDate
             THEN IFNULL(s.ReClaimFee1, 0)
             WHEN s.ReClaimFeeSetting = '1' AND s.ReClaimFeeStartRegistDate IS NULL AND s.ReClaimFeeStartDate <= o.ReceiptOrderDate
             THEN IFNULL(s.ReClaimFee1, 0)
             WHEN s.ReClaimFeeSetting = '1' AND s.ReClaimFeeStartRegistDate <= o.RegistDate AND s.ReClaimFeeStartDate IS NULL
             THEN IFNULL(s.ReClaimFee1, 0)
             WHEN s.ReClaimFeeSetting = '1' AND s.ReClaimFeeStartRegistDate IS NULL AND s.ReClaimFeeStartDate IS NULL
             THEN IFNULL(s.ReClaimFee1, 0)
             ELSE s.ReClaimFee
        END) AS ReClaimFee
,      cnl.CancelDate
,      (SELECT MAX(ReceiptDate) FROM MV_ReceiptControl WHERE OrderSeq = o.P_OrderSeq) AS MAXReceiptDate
,      e.EnterpriseNameKj
,      (SELECT MAX(ClaimDate) FROM MV_ClaimHistory WHERE ClaimPattern = 1 AND OrderSeq = o.P_OrderSeq) AS MAXClaimDate
FROM   MV_Order o INNER JOIN
       MV_Customer c ON ( c.OrderSeq = o.OrderSeq ) LEFT OUTER JOIN
       MV_ClaimControl cc ON ( cc.OrderSeq = o.P_OrderSeq ) LEFT OUTER JOIN
       MV_Site s ON ( s.SiteId = o.SiteId ) LEFT OUTER JOIN
       MV_Cancel cnl ON ( cnl.OrderSeq = o.OrderSeq AND cnl.ValidFlg = 1 ) LEFT OUTER JOIN
       MV_Enterprise e ON ( e.EnterpriseId = s.EnterpriseId ) LEFT OUTER JOIN
       (SELECT EnterpriseId
             , SiteId
             , COUNT(*) AS cnt
             , MAX(ReClaimFee) AS ReClaimFee
          FROM MV_EnterpriseCampaign
         WHERE DATE(NOW()) BETWEEN DateFrom AND DateTo
         GROUP BY
               EnterpriseId
             , SiteId
       ) AS ECampaign ON ( ECampaign.EnterpriseId = o.EnterpriseId AND ECampaign.SiteId = o.SiteId )
WHERE  o.OrderSeq = :OrderSeq AND
       o.ValidFlg = 1 AND
       c.ValidFlg = 1
EOQ;

        $prm = array( ':OrderSeq' => $orderSeq );
        $order = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

        // 再請求手数料を税込み金額に変換
        $sysProps = new MypageViewSystemProperty($this->app->dbAdapter);
        $order['ReClaimFee'] = $sysProps->getIncludeTaxAmount(date('Y-m-d'), $order['ReClaimFee']);

        // 子注文IDリストを取得
        $orderIdList = $this->app->dbAdapter->query( 'select OrderId from MV_Order where P_OrderSeq = :OrderSeq ' )->execute( array(':OrderSeq' => $orderSeq) );
        foreach ($orderIdList as $orderId) {
            $order['OrderIds'] = $order['OrderIds'] . $orderId['OrderId'] . '　';
        }

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
FROM   MV_OrderItems i INNER JOIN
       MV_Order o ON o.OrderSeq = i.OrderSeq INNER JOIN
       MV_DeliveryDestination dd ON dd.DeliDestId = ( SELECT DeliDestId FROM MV_OrderItems WHERE OrderSeq = :OrderSeq AND DataClass = 1 AND ValidFlg = 1 LIMIT 1 )
WHERE  o.P_OrderSeq = :OrderSeq AND
       i.ValidFlg = 1 AND
       o.ValidFlg = 1 AND
       dd.ValidFlg = 1 AND
       o.Cnl_Status = 0
ORDER BY i.OrderSeq, i.DataClass, i.OrderItemId
EOQ;
        $stm = $this->app->dbAdapter->query( $sql );
        $prm = array( ':OrderSeq' => $orderSeq );
        $orderItems = ResultInterfaceToArray( $stm->execute( $prm ) );
        // 送料 と 決済手数料 と 税額 算出
        foreach( $orderItems as $orderItem ) {
            if( $orderItem['DataClass'] == 2 ) {
                $sumCarriage[$order['OrderSeq']] += $orderItem['SumMoney'];
            }
            else if( $orderItem['DataClass'] == 3 ) {
                $sumSettlementFee[$order['OrderSeq']] += $orderItem['SumMoney'];
            }
            else if( $orderItem['DataClass'] == 4 ) {
                $sumTax[$order['OrderSeq']] += $orderItem['SumMoney'];
            }
            $orderClaimAmount += $orderItem['SumMoney'];
        }
        $deliDest = $orderItems[0];
        if( isset( $params['claim'] ) && $anotherDeliUpdFlg != '2' ) {
            $this->AnotherDeliUpdFlg = '2';
            $order['NameKj'] = $deliDest['DestNameKj'];
            $order['PostalCode'] = $deliDest['PostalCode'];
            $order['UnitingAddress'] = $deliDest['UnitingAddress'];
            $order['Phone'] = $deliDest['Phone'];
        }

        $this->setPageTitle( '請求書再発行申請' );
        $this->view->assign( 'userInfo', $this->title );
        $this->view->assign( 'OrderSeq', $this->userInfo->OrderSeq );
        $this->view->assign( 'reason', $reason );
        $this->view->assign( 'order', $order );
        $this->view->assign( 'orderItems', $orderItems );
        $this->view->assign( 'sumCarriage', $sumCarriage );
        $this->view->assign( 'sumSettlementFee', $sumSettlementFee );
        $this->view->assign( 'sumTax', $sumTax );
        $this->view->assign( 'anotherDeliUpdFlg', $this->AnotherDeliUpdFlg );
        $this->view->assign( 'orderClaimAmount', $orderClaimAmount);

        return $this->view;
    }

    /**
     * 請求書再発行処理
    */
    public function reissAction() {
        $params = $this->getParams();
        $orderSeq = $params['orderseq'];
        $reason = $params['reason'];
        $anotherDeliUpdFlg = $params['anotherDeliUpdFlg'];
        $reclaimfee = $params['reclaimfee'];

        $orderSeqA = $params['orderseq'];
        $orderSeqB = $this->userInfo->OrderSeq;

        if ( empty( $orderSeqA )
          || empty( $orderSeqB )
          || ( $orderSeqA <> $orderSeqB )
        ) {
            $this->_redirect( 'login/login' );
            return;
        }

        // トランザクション開始
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

        try {
            $mdlMyToBack = new TableMypageToBackIF($this->app->dbAdapter);

            // 購入者情報取得
            $mdlc = new MypageViewCustomer( $this->app->dbAdapter );
            $customerInfo = $mdlc->findCustomer( array( 'OrderSeq' => $orderSeq ) )->current();

            $ifDataArray = array(
                'ReissueClass' => $anotherDeliUpdFlg,
                'ReissueRequestDate' => date('Y-m-d H:i:s'),
                'ReissueReClaimFee' => isset($reclaimfee) ? $reclaimfee : 0,
            );
            $IfData = Json::encode($ifDataArray);
            $data = array (
                    'Status' => '0',            // 0：指示
                    'Reason' => NULL,
                    'IFClass' => '1',           // 1：請求書再発行指示
                    'IFData' => $IfData,
                    'OrderSeq' => $orderSeq,
                    'ManCustId' => $customerInfo['EntCustSeq'],
                    'CustomerId' => NULL,
                    'ValidFlg' => '1'
            );
            $mdlMyToBack->saveNew($data);

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        } catch( \Exception $err ) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            throw $err;
        }

        $this->_redirect( 'billreiss/completion' );
    }

    /**
     * 請求書再発行申請完了画面の表示
    */
    public function completionAction() {
        $this->setPageTitle( '再発行申請完了' );
        $this->view->assign( 'userInfo', $this->title );

        return $this->view;
    }

    /**
     * エラー画面表示
    */
    public function errorAction() {
        $this->setPageTitle( 'エラー' );
        $this->view->assign( 'userInfo', $this->title );

        return $this->view;
    }
}
