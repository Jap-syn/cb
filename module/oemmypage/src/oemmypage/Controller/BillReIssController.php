<?php
namespace oemmypage\Controller;

use oemmypage\Application;
use Coral\Coral\Controller\CoralControllerAction;
use models\View\MypageViewOemClaimAccountInfo;
use models\View\MypageViewSystemProperty;
use models\Table\TableMypageToBackIF;
use models\View\MypageViewOrder;
use Zend\Json\Json;
use models\Logic\LogicPayeasy;

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

        // ページタイトルとスタイルシート、JavaScriptを設定
        if ($this->is_mobile_request())
        {
            $this->addStyleSheet( '../../oemmypage/css_sp/mypage_billreiss.css' )
                 ->addStyleSheet( '../../oemmypage/css_sp/mypage_index.css' );
        }
        else
        {
            $this->addStyleSheet( '../../oemmypage/css/mypage_billreiss.css' )
                 ->addStyleSheet( '../../oemmypage/css/mypage_index.css' );
        }
        $this->addJavaScript( '../../js/prototype.js' )
            ->addJavaScript( '../../js/bytefx.js' )
            ->addJavaScript( '../../js/json+.js' )
            ->addJavaScript( '../../js/corelib.js' )
            ->addJavaScript( '../../js/base.ui.js' );

        $this->userInfo = Application::getInstance()->authManager->getUserInfo();
        $this->altUserInfo = Application::getInstance()->authManager->getAlternativeUserInfo();

        // タイトル文字取得
        $this->title = $this->altUserInfo ?
           sprintf( '%s %s(%s)　様', $this->userInfo->NameSeiKj, $this->userInfo->NameMeiKj, $this->altUserInfo->NameKj ) :
           sprintf( '%s%s　様', $this->userInfo->NameSeiKj , $this->userInfo->NameMeiKj );
    }

    /**
     * 購入履歴表示
    */
    public function indexAction() {
        $orderSeq = $this->getParams()['orderseq'];

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
       MV_ClaimControl cc ON ( cc.OrderSeq = o.P_OrderSeq ) LEFT OUTER JOIN
       MV_EnterpriseCustomer ec ON ( ec.EntCustSeq = c.EntCustSeq ) LEFT OUTER JOIN
       MV_ManagementCustomer mc ON ( mc.ManCustId = ec.ManCustId )
WHERE  o.OrderSeq = :OrderSeq AND
       mc.ManCustId = :ManCustId AND
       IFNULL( o.OemId, 0 ) = :OemId AND
       o.ValidFlg = 1 AND
       c.ValidFlg = 1
EOQ;

        if( empty( $this->userInfo->OemId ) ) {
            $oemId = 0;
        }
        else {
            $oemId = $this->userInfo->OemId;
        }
        $prm = array(
            ':OrderSeq' => $orderSeq,
            ':ManCustId' => $this->userInfo->ManCustId,
            ':OemId' => $oemId,
        );
        $claimInfo = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

        // 指定された注文SEQがユーザーの注文SEQと一致しなかった場合
        if( empty( $claimInfo ) ) {
            $this->setTemplate( 'error' );

            return $this->view;
        }

        // OEM請求口座取得　最新を表示
        $mdlocai = new MypageViewOemClaimAccountInfo( $this->app->dbAdapter );
        $accountInfos = $mdlocai->findByOrderSeq( $orderSeq )->current();
        if      ($oemId == 2) { $accountInfos['Yu_AccountNumber'] = '00120-7-670031'; $accountInfos['Yu_SubscriberName'] = '株式会社キャッチボール'; }
        else if ($oemId == 3) { $accountInfos['Yu_AccountNumber'] = '00100-7-292043'; $accountInfos['Yu_SubscriberName'] = '株式会社キャッチボール　セイノーＦＣ係'; }

        $sysProps = new MypageViewSystemProperty($this->app->dbAdapter);
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

        //ペイジー収納機関番号取得
        $sql = ' SELECT Note FROM MV_Code WHERE CodeId = :CodeId AND KeyCode = :KeyCode';
        $codeId = LogicPayeasy::PAYEASY_CODEID;
        $keyCode = LogicPayeasy::BK_NUMBER_KEYCODE;
        $bk_number = $this->app->dbAdapter->query($sql)->execute(array(':CodeId'=>$codeId, ':KeyCode'=>$keyCode))->current()['Note'];

        $this->setPageTitle( '請求書再発行' );
        $this->view->assign( 'userInfo', $this->title );
        $this->view->assign( 'claimInfo', $claimInfo );
        $this->view->assign( 'accountInfos', $accountInfos );
        $this->view->assign( 'displayFlg', $displayFlg );
        $this->view->assign( 'ReClaimCautionMessageA', $reClaimCautionMessageA );
        $this->view->assign( 'ReClaimCautionMessageB', $reClaimCautionMessageB );
        $this->view->assign( 'ReIssobj', $reIssobj );
        $this->view->assign( 'bk_number', $bk_number );

        return $this->view;
    }


    /**
     * 請求書発行内容の確認
    */
    public function confirmAction() {
        $params = $this->getParams();
        $orderSeq = $params['orderseq'];
        $reason = $params['reason'];
        $change = $claim['claim'];
        $anotherDeliUpdFlg = $params['anotherDeliUpdFlg'];

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
,      F_GetCampaignVal(e.EnterpriseId, s.SiteId, DATE(NOW()), 'ReClaimFee') AS ReClaimFee
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
       MV_EnterpriseCustomer ec ON ( ec.EntCustSeq = c.EntCustSeq ) LEFT OUTER JOIN
       MV_ManagementCustomer mc ON ( mc.ManCustId = ec.ManCustId )
WHERE  o.OrderSeq = :OrderSeq AND
       mc.ManCustId = :ManCustId AND
       IFNULL( o.OemId, 0 ) = :OemId AND
       o.ValidFlg = 1 AND
       c.ValidFlg = 1
EOQ;

        if( empty( $this->userInfo->OemId ) ) {
            $oemId = 0;
        }
        else {
            $oemId = $this->userInfo->OemId;
        }
        $prm = array(
            ':OrderSeq' => $orderSeq,
            ':ManCustId' => $this->userInfo->ManCustId,
            ':OemId' => $oemId,
        );
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
        $prm = array( ':OrderSeq' => $order['OrderSeq'] );
        $orderItems[$order['OrderSeq']] = ResultInterfaceToArray( $stm->execute( $prm ) );
        // 送料 と 決済手数料 と 税額 算出
        foreach( $orderItems[$order['OrderSeq']] as $orderItem ) {
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
        $deliDest = $orderItems[$order['OrderSeq']][0];
        if( isset( $params['claim'] ) && $anotherDeliUpdFlg != '2') {
            $this->AnotherDeliUpdFlg = '2';
            $order['NameKj'] = $deliDest['DestNameKj'];
            $order['PostalCode'] = $deliDest['PostalCode'];
            $order['UnitingAddress'] = $deliDest['UnitingAddress'];
            $order['Phone'] = $deliDest['Phone'];
        }

        $this->setPageTitle( '請求書再発行申請' );
        $this->view->assign( 'userInfo', $this->title );
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

        // トランザクション開始
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

        try {
            $mdlMyToBack = new TableMypageToBackIF($this->app->dbAdapter);

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
                    'ManCustId' => $this->userInfo->ManCustId,
                    'CustomerId' => $this->userInfo->CustomerId,
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
