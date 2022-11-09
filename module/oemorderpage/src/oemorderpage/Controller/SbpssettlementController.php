<?php
namespace oemorderpage\Controller;

use orderpage\Application;
use Coral\Base\Auth\BaseAuthManager;
use Coral\Coral\Controller\CoralControllerMypageAction;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Zend\Json\Json;
use models\Table\TableMypageToBackIF;
use models\Table\TableSbpsReceiptControl;

class SbpssettlementController extends CoralControllerMypageAction {
    protected $_componentRoot = './application/views/components';

    /**
     * @var BaseAuthManager
    */
    private $authManager;

    /**
     * @var FlashMessenger
    */
    private $messenger;

    protected $_moduleName = 'orderpage'; // 必ず指定してください。

    /**
     * 初期化処理
    */
    protected function _init() {
        $this->app = Application::getInstance();
        $this->userInfo = Application::getInstance()->authManager->getUserInfo();

        // ページタイトルとスタイルシート、JavaScriptを設定
        $this->setPageTitle( 'クレジットカード支払い' )
            ->addJavaScript( '../../js/prototype.js' )
            ->addJavaScript( '../../js/bytefx.js' )
            ->addJavaScript( '../../js/pop_image.js' );

            if ($this->is_mobile_request()) {
                $this->addStyleSheet( '../../oemorderpage/css_sp/orderpage_index.css' );
            } else {
                $this->addStyleSheet( '../../oemorderpage/css/orderpage_index.css' );
            }

        // 認証マネージャとFlashMessengerをメンバに設定
        $this->authManager = Application::getInstance()->authManager;
        $this->messenger = $this->flashMessenger();
    }
    
     /**
     * Purchase request action
    */
    public function indexAction() {
        $params = $this->getParams();

        // check if session lost and login into another order
        $orderSeqA = $params['oseq'];
        $orderSeqB = $this->userInfo->OrderSeq;
        
        if ( empty( $orderSeqA )
            || empty( $orderSeqB )
            || ( $orderSeqA <> $orderSeqB )
            ) {
                $this->_redirect( 'login/login' );
                return;
            }
            
        // check if order has been already purschased
        if (!$this->judgeCreditCardPayment($orderSeqA)) {
            $errMsg = array();
            $errMsg[] = '既に決済済みまたはキャンセル済みです。購入履歴画面にお戻りください。';
            $this->view->assign('errorMessages', $errMsg );
            $odrInf = $this->_getOrderInfo($this->userInfo->OrderSeq);
            $this->view->assign('orderId', $odrInf['OrderId']);
            $this->view->assign('claimedBalance', $odrInf['ClaimedBalance']);
            $this->view->assign('siteLogo', $odrInf['SiteLogo']);
            $this->view->assign('entsiteName', ($odrInf['EnterpriseNameKj'] . '　' . $odrInf['SiteNameKj']));
            $this->setTemplate('error');
            return $this->view;
        }

        // 悲観ロック用にクレジット支払管理にデータ登録
        $work = $this->app->dbAdapter->query('SELECT COUNT(*) AS Cnt, PaymentType FROM T_CreditPayment WHERE OrderSeq=:OrderSeq')->execute(array(':OrderSeq' => $this->userInfo->OrderSeq))->current();
        if ($work['Cnt'] == 0) {
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
            $this->app->dbAdapter
            ->query('INSERT INTO T_CreditPayment(OrderSeq,PaymentType,RegistDate,UpdateDate) VALUES (:OrderSeq,0,:RegistDate,:RegistDate)')
            ->execute(array(
                ':OrderSeq' => $this->userInfo->OrderSeq,
                ':RegistDate' => date('Y-m-d H:i:s')
            ));
            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        } else {
            if ($work['PaymentType'] == 1) {
                $errMsg = array();
                $errMsg[] = '既に決済済みまたはキャンセル済みです。購入履歴画面にお戻りください。';
                $this->view->assign('errorMessages', $errMsg );
                $this->view->assign('url', 'index/index' );
                $odrInf = $this->_getOrderInfo($this->userInfo->OrderSeq);
                $this->view->assign('orderId', $odrInf['OrderId']);
                $this->view->assign('claimedBalance', $odrInf['ClaimedBalance']);
                $this->view->assign('siteLogo', $odrInf['SiteLogo']);
                $this->view->assign('entsiteName', ($odrInf['EnterpriseNameKj'] . '　' . $odrInf['SiteNameKj']));
                $this->setTemplate('error');
                return $this->view;
            }
        }
        
        $orderSeq = $this->userInfo->OrderSeq;

        // 注文情報
        $sql = <<<EOQ
SELECT o.OrderId
,      cc.ClaimedBalance
,      (SELECT GROUP_CONCAT(sp.PaymentName) FROM MV_SbpsPayment sp
		INNER JOIN MV_SiteSbpsPayment ss on (ss.PaymentId=sp.SbpsPaymentId)
		WHERE ss.SiteId=o.SiteId AND ss.ValidFlg=1 AND ss.UseStartDate <= :Now AND sp.PaymentName NOT LIKE "%credit%" 
		AND DATE_ADD((SELECT MIN(ch.ClaimDate) FROM MV_ClaimHistory AS ch WHERE ch.OrderSeq = o.OrderSeq AND ch.ClaimPattern = 1) ,
		INTERVAL ss.NumUseDay DAY) >= CURRENT_DATE()) AS PaymentMethod	
,      s.MerchantId
,      s.ServiceId
,      IF(c.EntCustId IS NULL OR c.EntCustId = '', c.EntCustSeq, c.EntCustId) AS CustCode
,      s.PaymentAfterArrivalName
,      o.Ent_OrderId
,      SUBSTRING(o.Incre_Note, 1, 20) AS Incre_Note
,      s.HashKey
,      o.OemId
FROM   MV_Order o INNER JOIN
       MV_Customer c ON ( c.OrderSeq = o.OrderSeq ) LEFT OUTER JOIN
       MV_ClaimControl cc ON ( cc.OrderSeq = o.OrderSeq ) LEFT OUTER JOIN
       MV_Site s ON ( s.SiteId = o.SiteId ) 
WHERE  o.ValidFlg = 1 AND
       c.ValidFlg = 1 AND
       o.OrderSeq = :OrderSeq
EOQ;

        $prm = array( ':OrderSeq' => $orderSeq, ':Now' => date('Y-m-d H:i:s'));
        $order = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();
        
        //Data to send to SBPS
        $mdlsp = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        $sql = "SELECT * FROM MV_Code WHERE CodeId = 220 AND KeyCode = :KeyCode";
        $prm = array(
            ':KeyCode' => $order['OemId'],
        );
        $mvCode = $this->app->dbAdapter->query($sql)->execute($prm)->current();
        $success_url = $mvCode['Class3'];
        $success_url = str_replace('oe.atobarai-dev.jp', $_SERVER['SERVER_NAME'], $success_url);
        $success_url = str_replace('www4.atobarai-dev.jp', $_SERVER['SERVER_NAME'], $success_url);
        $success_url = str_replace('cb.ato-barai.jp', $_SERVER['SERVER_NAME'], $success_url);
        $cancel_url = $mvCode['Class4'];
        $cancel_url = str_replace('oe.atobarai-dev.jp', $_SERVER['SERVER_NAME'], $cancel_url);
        $cancel_url = str_replace('www4.atobarai-dev.jp', $_SERVER['SERVER_NAME'], $cancel_url);
        $cancel_url = str_replace('cb.ato-barai.jp', $_SERVER['SERVER_NAME'], $cancel_url);
        $error_url = $mvCode['Class5'];
        $error_url = str_replace('oe.atobarai-dev.jp', $_SERVER['SERVER_NAME'], $error_url);
        $error_url = str_replace('www4.atobarai-dev.jp', $_SERVER['SERVER_NAME'], $error_url);
        $error_url = str_replace('cb.ato-barai.jp', $_SERVER['SERVER_NAME'], $error_url);
        $pagecon_url = $mvCode['Class2'];
        $pagecon_url = str_replace('oe.atobarai-dev.jp', $_SERVER['SERVER_NAME'], $pagecon_url);
        $pagecon_url = str_replace('www4.atobarai-dev.jp', $_SERVER['SERVER_NAME'], $pagecon_url);
        $pagecon_url = str_replace('cb.ato-barai.jp', $_SERVER['SERVER_NAME'], $pagecon_url);

        $request_date = date('YmdHis');
        //Create sbps_hashcode
        $sha1str_temp =  $order['PaymentMethod'] .
        $order['MerchantId'] .
        $order['ServiceId'] .
        $order['CustCode'] .
        $order['OrderId'] . '_' . $request_date .
        $order['OrderId'] .
        $order['PaymentAfterArrivalName'] .
        $order['ClaimedBalance'] .
        0 .
        0 .
        $success_url .
        $cancel_url .
        $error_url .
        $pagecon_url .
        $order['Ent_OrderId'].
        //$order['Incre_Note'].
        //1.
        $request_date .
        $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'sbpspayment', 'timeout') .
        $order['HashKey']
        ;
        //Create request array
        $sbps_send_data = [
            'pay_method'              => $order['PaymentMethod'],
            'merchant_id'               => $order['MerchantId'],
            'service_id'              => $order['ServiceId'],
            'cust_code'               => $order['CustCode'],
            'order_id'          => $order['OrderId'] . '_' . $request_date,
            'item_id'                 => $order['OrderId'],
            'item_name' => mb_convert_encoding($order['PaymentAfterArrivalName'], "SJIS", "UTF-8"),
            'amount'              => $order['ClaimedBalance'],
            'pay_type'               => 0,
            'service_type'                   => 0,
            'success_url'               => $success_url,
            'cancel_url'                 => $cancel_url,
            'error_url'                => $error_url,
            'pagecon_url'          => $pagecon_url,
            'free1'              => $order['Ent_OrderId'],
            //'free2'              => mb_convert_encoding($order['Incre_Note'], "SJIS", "UTF-8"),
            //'dtl_rowno'              => 0,           
            'request_date'               => $request_date,
            'limit_second'                 => $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'sbpspayment', 'timeout')
        ];
        $sha1str =  array('sps_hashcode'                => mb_convert_encoding(sha1($sha1str_temp), "SJIS", "UTF-8"));
        $sbps_send_data = array_merge((array)$sbps_send_data, (array)$sha1str);
        header("Content-Type: text/html; charset=Shift_JIS");
        $this->view->assign( 'sbps_send_data', $sbps_send_data );
        //convert from shift-jis to utf-8 to save log
        $data = [
            'pay_method'              => $order['PaymentMethod'],
            'merchant_id'               => $order['MerchantId'],
            'service_id'              => $order['ServiceId'],
            'cust_code'               => $order['CustCode'],
            'order_id'          => $order['OrderId'] . '_' . $request_date,
            'item_id'                 => $order['OrderId'],
            'item_name' => $order['PaymentAfterArrivalName'],
            'amount'              => $order['ClaimedBalance'],
            'pay_type'               => 0,
            'service_type'                   => 0,
            'success_url'               => $success_url,
            'cancel_url'                 => $cancel_url,
            'error_url'                => $error_url,
            'pagecon_url'          => $pagecon_url,
            'free1'              => $order['Ent_OrderId'],
            'free2'              => $order['Incre_Note'],
            //'dtl_rowno'              => 0,
            'request_date'               => $request_date,
            'limit_second'                 => $mdlsp->getValue(\models\Table\TableSystemProperty::DEFAULT_MODULE, 'sbpspayment', 'timeout'),
            'sps_hashcode'                => sha1($sha1str_temp)
        ];
        $this->app->sbpsLogger->info('購入要求:' . Json::encode($data));
        $this->view->assign( 'purchase_url', $mvCode['Class1'] );
        return $this->view;
    }
    
    /**
     * Purchase result CGI receive action
     */
    
    public function pageconAction()
    {
        $for_log = mb_convert_encoding($_REQUEST, mb_internal_encoding(), "SJIS");
        $this->app->sbpsLogger->info('購入結果 CGI:' . Json::encode($for_log));
        $params   = $this->getParams();
        $orderId  = $params['item_id'];
        $orderSeq = $this->getOrderByOrderId($orderId)['OrderSeq'];
        
        if ($params['res_result'] == 'OK' && !$this->judgeCreditCardPayment($orderSeq)) {
            $response = 'NG,既に決済済みです';
            $this->app->sbpsLogger->info('購入結果 CGI レスポンス(OKの場合):'.$response);
            exit($response);
        }
        
        if($params['res_result'] == 'NG'){
            $response = 'OK';
            $this->app->sbpsLogger->info('購入結果 CGI レスポンス(NGの場合):'.$response);
            exit($response);
        }
        try {
            // 悲観ロック
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
            $work = $this->app->dbAdapter->query('SELECT COUNT(*) AS Cnt FROM T_CreditPayment WHERE OrderSeq=:OrderSeq AND PaymentType=0 FOR UPDATE')->execute(array(':OrderSeq' => $orderSeq))->current();
            if ($work['Cnt'] == 0) {
                $this->app->dbAdapter->getDriver()->getConnection()->rollback();
                $response = 'NG,既に決済済みです';
                $this->app->sbpsLogger->info('購入結果 CGI レスポンス:'.$response);
                exit($response);
            }
            
            // 基幹反映指示インタフェース(T_MypageToBackIF)登録
            $mdlMyToBack = new TableMypageToBackIF($this->app->dbAdapter);
            $ifDataArray = array(
                'res_tracking_id'    => $params['res_tracking_id'],
                'res_pay_method'     => $params['res_pay_method'],
                'res_result'         => $params['res_result'],
                'res_sps_cust_no'    => $params['res_sps_cust_no'],
                'res_sps_payment_no' => $params['res_sps_payment_no'],
                'res_payinfo_key'    => $params['res_payinfo_key'],
                'res_payment_date'   => $params['res_payment_date'],
                'res_err_code'       => $params['res_err_code'],
                'terminal_type'           => $params['terminal_type'],           
                'merchant_id'           => $params['merchant_id'],              
                'service_id'           => $params['service_id'],              
                'amount'           => $params['amount'],
                'item_id'           => $params['item_id'],               
                'pay_method'           => $params['pay_method'],              
                'limit_second'           => $params['limit_second'],              
                'request_date'           => $params['request_date'],
            );
            
            $ifData = Json::encode($ifDataArray);
            
            $data = array (
                'Status'        => '0', // 0：指示
                'Reason'        => NULL,
                'IFClass'       => '4', // 連携区分(4：届いてから払い手続き)
                'IFData'        => $ifData,
                'OrderSeq'      => $orderSeq,
                'ManCustId'     => NULL,
                'CustomerId'    => NULL,
                'ValidFlg'      => '1'
            );
            $mdlMyToBack->saveNew($data);
            
            // T_SbpsReceiptControl登録
            $prm = array(
                'OrderSeq'     => $orderSeq,
                'PayType'      => 1,
                'PaymentName'  => $params['res_pay_method'],
                'ReceiptDate'  => $params['res_payment_date'],
                'RegistId'      => 1,
                'UpdateId'      => 1,
                'ValidFlg'      => 1,
            );
            $mdlSbpsReceiptControl = new TableSbpsReceiptControl($this->app->dbAdapter);
            $mdlSbpsReceiptControl->saveNew($prm);
            
            // 悲観ロック解除
            $this->app->dbAdapter
            ->query('UPDATE T_CreditPayment SET PaymentType=1,UpdateDate=:UpdateDate WHERE OrderSeq=:OrderSeq')
            ->execute(array(
                ':OrderSeq' => $orderSeq,
                ':UpdateDate' => date('Y-m-d H:i:s')
            ));
            $this->app->dbAdapter->getDriver()->getConnection()->commit();
            $response = 'OK';
            $this->app->sbpsLogger->info('購入結果 CGI レスポンス:'.$response);
            exit($response);
        } catch (\Exception $e) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            $response = 'NG,システムでエラーを発生するため処理できません';
            $this->app->sbpsLogger->info('購入結果 CGI レスポンス(NGの場合):'.$response);
            exit($response);
        }
    }
    
    /**
     * 届いてから払いが可能かの判定
     * ※購入履歴表示を流用
     */
    private function judgeCreditCardPayment($orderSeq) {
        // 注文情報
        $sql = <<<EOQ
SELECT o.*
,      c.EntCustSeq
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
,      c.PostalCode
,      c.UnitingAddress
,      ec.RequestStatus
,      ao.CreditTransferRequestFlg
,      ec.FfName
,      ec.FfBranchName
,      ec.FfAccountClass
,      ec.FfAccountNumber
,      ao.ExtraPayType
,      ao.ExtraPayKey
,      ao.ExtraPayNote
,      s.PaymentAfterArrivalFlg
,      (SELECT MAX(rc.ReceiptSeq)
          FROM MV_ReceiptControl AS rc
         WHERE rc.OrderSeq = o.OrderSeq
           AND ReceiptDate IS NOT NULL
       ) AS MaxReceiptSeq
,      (SELECT MIN(ch.ClaimDate) FROM MV_ClaimHistory AS ch WHERE ch.OrderSeq = o.OrderSeq AND ch.ClaimPattern = 1) AS MinClaimDate
FROM   MV_Order o INNER JOIN
       MV_Customer c ON ( c.OrderSeq = o.OrderSeq ) LEFT OUTER JOIN
       MV_ClaimControl cc ON ( cc.OrderSeq = o.OrderSeq ) LEFT OUTER JOIN
       MV_Site s ON ( s.SiteId = o.SiteId ) LEFT OUTER JOIN
       MV_Cancel cnl ON ( cnl.OrderSeq = o.OrderSeq AND cnl.ValidFlg = 1 ) LEFT OUTER JOIN
       MV_Enterprise e ON ( e.EnterpriseId = s.EnterpriseId )
       LEFT JOIN MV_EnterpriseCustomer AS ec ON c.EntCustSeq = ec.EntCustSeq
       LEFT JOIN MAV_Order AS ao ON c.OrderSeq = ao.OrderSeq
WHERE  o.ValidFlg = 1 AND
       c.ValidFlg = 1 AND
       o.OrderSeq = :OrderSeq
EOQ;

        $prm = array( ':OrderSeq' => $orderSeq );
        $order = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

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
        $prm = array( ':OrderSeq' => $order['OrderSeq'] );
        $orderStatuses = ResultInterfaceToArray( $stm->execute( $prm ) );

        $RctStatusJudge = 0;
        $CnlStatusJudge = 0;
        foreach( $orderStatuses as $orderSt ) {
            // 入金済み
            if ($orderSt['Rct_Status'] == 1) {
                $RctStatusJudge++;
            }
            // 未キャンセルかどうかチェック
            if ($orderSt['Cnl_Status'] != 0) {
                $CnlStatusJudge++;
            }
        }

        // 1レコードでも入金済みがあれば入金済み
        $orderStatus['Rct_Status'] = $RctStatusJudge > 0 ? 1 : 0;
        // 1レコードでもキャンセル済みがあれば入金済み
        $orderStatus['Cnl_Status'] = $CnlStatusJudge > 0 ? 1 : 0;

        // クレジット決済ステータス
        $mdlMyToBack = new TableMypageToBackIF($this->app->dbAdapter);
        $prmMTB = array(
            'OrderSeq' => $order['OrderSeq'],
            'IFClass' => '4',
            'ValidFlg' => '1',
        );
        if ( empty($order['ExtraPayNote']) ) {
            // 追加支払い方法備考が空欄（null）の場合、連携テーブルを確認する
            $myToBackCount = $mdlMyToBack->findMypageToBackIF($prmMTB, false)->count();
            if ($myToBackCount > 0) {
                $order['CreditSettlementStatus'] = '2' ;
            } else {
                $order['CreditSettlementStatus'] = '0' ;
            }

        } else {
            // 追加支払い方法区分が 1:クレジット の場合
            if ( $order['ExtraPayType'] == '1' ) {
                $order['CreditSettlementStatus'] = '2' ;
            } else {
                $order['CreditSettlementStatus'] = '0' ;
            }

        }
        // スマホ用入金方法等
        if ( $order['CreditSettlementStatus'] == 2 ) {
            // クレジットカード処理済
            return false;
        } else if ( $orderStatus['Rct_Status'] == 1 && $order['ClaimedBalance'] <= 0 ) {
            // 入金済みかつ残金無し
            return false;
        } else if ( $orderStatus['Cnl_Status'] != 0 ) {
            // キャンセル済み
            return false;
        }

        return true;
    }
    
    /**
     * Callback success funtion
     */
    public function successAction() {
        $for_log = mb_convert_encoding($_REQUEST, mb_internal_encoding(), "SJIS");
        $this->app->sbpsLogger->info('購入結果（画面返却） 成功:' . Json::encode($for_log));
        try {
            $this->_redirect( 'index/index' );
            return;
        } catch (\Exception $e) {
            $errMsg = array();
            $errMsg[] = 'システムでエラーを発生するため、処理できません';
            $this->view->assign('errorMessages', $errMsg );
            $this->setTemplate('error');
            return $this->view;
        }
    }

    /**
     * Callback cancel funtion
     */
    public function cancelAction()
    {
        $for_log = mb_convert_encoding($_REQUEST, mb_internal_encoding(), "SJIS");
        $this->app->sbpsLogger->info('購入結果（画面返却） キャンセル:' . Json::encode($for_log));
        try {
            $this->_redirect( 'index/index' );
            return;
        } catch (\Exception $e) {
            $errMsg = array();
            $errMsg[] = 'システムでエラーを発生するため、処理できません';
            $this->view->assign('errorMessages', $errMsg );
            $this->setTemplate('error');
            return $this->view;
        }
    }

    /**
     * Callback error funtion
     */
    public function errorAction()
    {
        $for_log = mb_convert_encoding($_REQUEST, mb_internal_encoding(), "SJIS");
        $this->app->sbpsLogger->info('購入結果（画面返却） エラー:' . Json::encode($for_log));
        $params   = $this->getParams();
        $params['res_err_code'] = $_GET['res_err_code'];
        if ($params['res_err_code'] >= 1101 && $params['res_err_code'] <= 1299) {
            $res_err_code = 1101;
        } elseif (($params['res_err_code'] >= 1301 && $params['res_err_code'] <= 1399)) {
            $res_err_code = 1301;
        } elseif (($params['res_err_code'] >= 2101 && $params['res_err_code'] <= 3399)) {
            $res_err_code = 2101;
        } elseif (($params['res_err_code'] >= 8001 && $params['res_err_code'] <= 8223)) {
            $res_err_code = 8001;
        } else {
            $res_err_code = $params['res_err_code'];
        }
        $sql = " SELECT KeyContent FROM MV_Code WHERE CodeId = :CodeId AND Class1 = :Class1 AND ValidFlg=1";
        $row = $this->app->dbAdapter->query($sql)->execute(array(':CodeId' => 211, ':Class1' => $res_err_code))->current();
        if($row){
            $errMsg = array();
            $errMsg[] = $this->sanitize_br($row['KeyContent']);
            $this->view->assign('errorMessages', $errMsg);
        }
        $odrInf = $this->_getOrderInfo($this->userInfo->OrderSeq);
        $this->view->assign('orderId', $odrInf['OrderId']);
        $this->view->assign('claimedBalance', $odrInf['ClaimedBalance']);
        $this->view->assign('siteLogo', $odrInf['SiteLogo']);
        $this->view->assign('entsiteName', ($odrInf['EnterpriseNameKj'] . '　' . $odrInf['SiteNameKj']));
        $this->setTemplate('error');
        return $this->view;
    }

    /**
     * 注文情報取得
     *
     * @param int $oseq 注文SEQ
     * @return array
     */
    protected function _getOrderInfo($oseq) {
        $sql = "
SELECT o.OrderId
,      cc.ClaimedBalance
,      e.EnterpriseId
,      e.EnterpriseNameKj
,      s.MerchantId
,      s.ServiceId
,      s.HashKey
,      s.BasicId
,      s.BasicPw
,      s.SiteId
,      s.SiteNameKj
,      s.SpecificTransUrl
,      s.SmallLogo AS SiteLogo
,      cc.ClaimPattern
,      o.Rct_Status
,      s.PaymentAfterArrivalFlg
,      ao.CreditTransferRequestFlg
,      IFNULL(ao.ExtraPayType, 0) AS ExtraPayType
,      (SELECT MIN(ch.ClaimDate) FROM MV_ClaimHistory AS ch WHERE ch.OrderSeq = o.OrderSeq AND ch.ClaimPattern = 1) AS MinClaimDate
,      (SELECT COUNT(mtbi.Seq) FROM T_MypageToBackIF AS mtbi WHERE mtbi.ValidFlg = 1 AND mtbi.IFClass = 4 AND mtbi.OrderSeq = o.OrderSeq) AS ToBackIfClass_4_Count
FROM   MV_Order o
       INNER JOIN MV_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq)
       INNER JOIN MV_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
       INNER JOIN MV_Site s ON (s.SiteId = o.SiteId)
       INNER JOIN MAV_Order AS ao ON (ao.OrderSeq = o.OrderSeq)
WHERE  o.OrderSeq = :OrderSeq
        ";
        
        return $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();
    }
    
    protected function sanitize_br($str){
        
        return nl2br(htmlspecialchars($str, ENT_QUOTES, 'UTF-8'));
        
    }
    
    protected function getOrderByOrderId($orderId)
    {
        // 注文情報
        $sql = <<<EOQ
SELECT o.OrderSeq
FROM   MV_Order o
WHERE  o.ValidFlg = 1 AND
       o.OrderId = :OrderId
EOQ;
        
        $prm = array( ':OrderId' => $orderId );
        $order = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();
        
        return $order;
    }
}
