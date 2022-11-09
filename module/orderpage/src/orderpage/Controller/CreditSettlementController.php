<?php
namespace orderpage\Controller;

use models\View\MypageViewReceiptControl;
use orderpage\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Base\Auth\BaseAuthManager;
use Coral\Coral\Controller\CoralControllerMypageAction;
use Coral\Base\IO\BaseIOUtility;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Zend\Http\Client;
use Zend\Json\Json;
use models\Table\TableSystemProperty;
use models\View\MypageViewMypageOrder;
use models\Table\TableMypageToBackIF;
use DateTime;
use models\View\MypageViewCode;
use models\Table\TableSbpsReceiptControl;

class CreditSettlementController extends CoralControllerMypageAction {
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

    private $session_key = 'cb_token';

    /**
     * 初期化処理
    */
    protected function _init() {
        $this->app = Application::getInstance();
        $this->userInfo = Application::getInstance()->authManager->getUserInfo();

        // ページタイトルとスタイルシート、JavaScriptを設定
        $this->setPageTitle( 'クレジットカード支払い' )
            ->addJavaScript( '../js/prototype.js' )
            ->addJavaScript( '../js/bytefx.js' )
            ->addJavaScript( '../js/pop_image.js' );

        if ($this->is_mobile_request()) {
            $this->addStyleSheet( './css_sp/orderpage.css')
                 ->addStyleSheet( './css_sp/orderpage_creditsettlement.css' );
        } else {
            $this->addStyleSheet( './css/orderpage.css')
                 ->addStyleSheet( './css/orderpage_creditsettlement.css' );
        }

        // 認証マネージャとFlashMessengerをメンバに設定
        $this->authManager = Application::getInstance()->authManager;
        $this->messenger = $this->flashMessenger();
    }

    /**
     * カード支払い(入力)
     *
    */
    public function inputAction() {
        $_SESSION[$this->session_key] = uniqid();
        $params = $this->getParams();

        $orderSeqA = $params['oseq'];
        $orderSeqB = $this->userInfo->OrderSeq;

        if ( empty( $orderSeqA )
          || empty( $orderSeqB )
          || ( $orderSeqA <> $orderSeqB )
        ) {
            $this->_redirect( 'login/login' );
            return;
        }

        if (!$this->judgeCreditCardPayment()) {
            $errMsg = array();
            $errMsg[] = '既に決済済みまたはキャンセル済みです。購入履歴画面にお戻りください。';
            $this->view->assign('errorMessages', $errMsg );
            $this->view->assign('url', 'index/index' );
            $this->setTemplate('error');
            return $this->view;
        }


        // システムプロパティ取得
        $tokenurl = $this->app->dbAdapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'sbpayment' AND Name = :Name "
            )->execute(array(':Name' => 'tokenurl'))->current()['PropValue'];

        // 注文情報取得
        $odrInf = $this->_getOrderInfo($this->userInfo->OrderSeq);

        /* クレジット入力対象外の場合、注文マイページのトップにリダイレクトさせる */
        if ( ( $odrInf['PaymentAfterArrivalFlg'] != 1 || $odrInf['CreditTransferRequestFlg'] != 0 )
          || ( $odrInf['ToBackIfClass_4_Count'] > 0 ) || ( $odrInf['ExtraPayType'] == 1 )
          || ( $odrInf['ClaimPattern'] != 1 )
          || ( ( $odrInf['Rct_Status'] == 1 ) && ( $odrInf['ClaimedBalance'] <= 0 ) )
        ) {
            // クレジット払い不可または処理済の場合
            return $this->_redirect('index/index');

        } else {
            // システム日付を取得する
            $dateTime = new DateTime();
            $sysDate = $dateTime->format('Ymd');
            // 最初回の請求日を取得する
            $minClaimDate = $odrInf['MinClaimDate'];
            // DateTimeメソッドの再宣言
            if ( ! empty( $minClaimDate ) ) {
                $dateTime = new DateTime( $minClaimDate. ' 00:00:00' );
            }
            // 請求日にクレジット払い可能期限日数を加算
            $dateTime->modify('+'. $odrInf['CreditMaxNumUseDay']. ' Day');
            // 結果を抽出
            $limitDate = $dateTime->format('Ymd');
            // システム日付と比較
            if ($limitDate < $sysDate) {
                // クレジット払い可能期限切れの場合
                return $this->_redirect('index/index');
            }
        }

        // ビューアサイン
        $this->view->assign('entsiteName', ($odrInf['EnterpriseNameKj'] . '　' . $odrInf['SiteNameKj']));
        $this->view->assign('specificTransUrl', $odrInf['SpecificTransUrl']);
        $this->view->assign('siteLogo', $odrInf['SiteLogo']);
        $this->view->assign('oseq', $this->userInfo->OrderSeq);
        $this->view->assign('orderId', $odrInf['OrderId']);
        $this->view->assign('claimedBalance', $odrInf['ClaimedBalance']);
        $this->view->assign('merchantid', $odrInf['MerchantId']);
        $this->view->assign('serviceid', $odrInf['ServiceId']);
        $this->view->assign('creditLogoUrl', $odrInf['CreditLogoUrl']);
        $this->view->assign('tokenurl', $tokenurl);
        $this->view->assign('data', array ('card_no' => $params['card_no'], 'period_month' => $params['period_month'], 'period_year' => $params['period_year'], 'cvc' => $params['cvc']));
        $this->view->assign('cb_token', $_SESSION[$this->session_key]);

        return $this->view;
    }

    /**
     * カード支払い(確認)
     *
    */
    public function confirmAction() {
        $params = $this->getParams();

        $orderSeqA = $params['oseq'];
        $orderSeqB = $this->userInfo->OrderSeq;

        if ( empty( $orderSeqA )
          || empty( $orderSeqB )
          || ( $orderSeqA <> $orderSeqB )
        ) {
            $this->_redirect( 'login/login' );
            return;
        }

        // トークンチェック
        if ($params['cb_token'] != $_SESSION[$this->session_key]) {
            $errMsg = array();
            $errMsg[] = '予期せぬエラーが発生しましたので、申し訳ございませんが、もう一度入力画面から処理をやり直してください。';
            $this->view->assign('errorMessages', $errMsg );
            $this->view->assign('url', 'creditsettlement/input' );
            $this->setTemplate('error');
            return $this->view;
        }

        if (!$this->judgeCreditCardPayment()) {
            $errMsg = array();
            $errMsg[] = '既に決済済みまたはキャンセル済みです。購入履歴画面にお戻りください。';
            $this->view->assign('errorMessages', $errMsg );
            $this->view->assign('url', 'index/index' );
            $this->setTemplate('error');
            return $this->view;
        }


        // 注文情報取得
        $odrInf = $this->_getOrderInfo($this->userInfo->OrderSeq);

        // 入力チェックはビューJavaScriptにて実施している(本箇所ではチェック不要)

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
                $this->setTemplate('error');
                return $this->view;
            }
        }

        // ビューアサイン
        $this->view->assign('entsiteName', ($odrInf['EnterpriseNameKj'] . '　' . $odrInf['SiteNameKj']));
        $this->view->assign('siteLogo', $odrInf['SiteLogo']);
        $this->view->assign('oseq', $this->userInfo->OrderSeq);
        $this->view->assign('orderId', $odrInf['OrderId']);
        $this->view->assign('claimedBalance', $odrInf['ClaimedBalance']);
        $this->view->assign('data', array ('card_no' => $params['card_no'], 'period_month' => $params['period_month'], 'period_year' => $params['period_year'], 'cvc' => $params['cvc'],
                'token' => $params['token'], 'tokenKey' => $params['tokenKey']));
        // (以下、表示用)
        $this->view->assign('card_no_view', (strlen($params['card_no']) > 4) ? str_repeat('*', strlen($params['card_no']) - 4) . substr($params['card_no'], -4) : $params['card_no']);
        $this->view->assign('period_view', $params['period_year'] . '年 ' . (int)$params['period_month'] . '月');
        $this->view->assign('cvc_view', str_repeat('*', strlen($params['cvc'])));
        $this->view->assign('cb_token', $_SESSION[$this->session_key]);

        return $this->view;
    }

    /**
     * カード支払い(手続き完了)
     *
    */
    public function completeAction() {
        $params = $this->getParams();

        $orderSeqA = $params['oseq'];
        $orderSeqB = $this->userInfo->OrderSeq;

        if ( empty( $orderSeqA )
          || empty( $orderSeqB )
          || ( $orderSeqA <> $orderSeqB )
        ) {
            $this->_redirect( 'login/login' );
            return;
        }

        // トークンチェック
        if ($params['cb_token'] != $_SESSION[$this->session_key]) {
            $errMsg = array();
            $errMsg[] = '予期せぬエラーが発生しましたので、申し訳ございませんが、もう一度入力画面から処理をやり直してください。';
            $this->view->assign('errorMessages', $errMsg );
            $this->view->assign('url', 'creditsettlement/input' );
            $this->setTemplate('error');
            return $this->view;
        }
        $_SESSION[$this->session_key] = uniqid();

        if (!$this->judgeCreditCardPayment()) {
            $errMsg = array();
            $errMsg[] = '既に決済済みまたはキャンセル済みです。購入履歴画面にお戻りください。';
            $this->view->assign('errorMessages', $errMsg );
            $this->view->assign('url', 'index/index' );
            $this->setTemplate('error');
            return $this->view;
        }

        // 注文情報取得
        $odrInf = $this->_getOrderInfo($this->userInfo->OrderSeq);

        // ビューアサイン
        $this->view->assign('entsiteName', ($odrInf['EnterpriseNameKj'] . '　' . $odrInf['SiteNameKj']));
        $this->view->assign('siteLogo', $odrInf['SiteLogo']);
        $this->view->assign('orderId', $odrInf['OrderId']);
        $this->view->assign('orderSeq', $this->userInfo->OrderSeq);
        $this->view->assign('claimedBalance', $odrInf['ClaimedBalance']);
        $this->view->assign('data', array ('card_no' => $params['card_no'], 'period_month' => $params['period_month'], 'period_year' => $params['period_year'], 'cvc' => $params['cvc']));

        // 悲観ロック
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        $work = $this->app->dbAdapter->query('SELECT COUNT(*) AS Cnt FROM T_CreditPayment WHERE OrderSeq=:OrderSeq AND PaymentType=0 FOR UPDATE')->execute(array(':OrderSeq' => $this->userInfo->OrderSeq))->current();
        if ($work['Cnt'] == 0) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            $errMsg = array();
            $errMsg[] = '既に決済済みまたはキャンセル済みです。購入履歴画面にお戻りください。';
            $this->view->assign('errorMessages', $errMsg );
            $this->view->assign('url', 'index/index' );
            $this->setTemplate('error');
            return $this->view;
        }

        // クレジットカード決済：決済要求（ワンタイムトークン利用）
        $sps_transaction_id = '';
        $tracking_id = '';
        $err_code = '';
        $cardbrand_code = '';
        $errorMessages = array();
        $params['merchantid'] = $odrInf['MerchantId'];
        $params['serviceid']  = $odrInf['ServiceId'];
        $isSuccess = $this->_SBPaymentSettlementRequest(array_merge($params, array('orderId' => $odrInf['OrderId'], 'claimedBalance' => $odrInf['ClaimedBalance'], 'hashkey' => $odrInf['HashKey'])), $sps_transaction_id, $tracking_id, $err_code, $errorMessages, $cardbrand_code);
        if ($isSuccess == false) {
            $errMsg = array();
            $errMsg[] = '現在、カードでのお支払いができません。申し訳ございませんが、他のお支払い方法をお選びください。'. ( (count($errorMessages) > 0) ? '' : ('（'. substr($err_code, 3, 2). '）') );
            $this->view->assign('errorMessages', $errMsg );
            $this->view->assign('url', 'index/index' );
            $this->setTemplate('error');
            return $this->view;
        }

        // クレジットカード決済：確定要求
        $isSuccess = $this->_SBPaymentConfirmRequest(array_merge($params, array('hashkey' => $odrInf['HashKey'])), $sps_transaction_id, $err_code, $errorMessages);
        if ($isSuccess == false) {
            $errMsg = array();
            $errMsg[] = '現在、カードでのお支払いができません。申し訳ございませんが、他のお支払い方法をお選びください。'. ( (count($errorMessages) > 0) ? '' : ('（'. substr($err_code, 3, 2). '）') );
            $this->view->assign('errorMessages', $errMsg );
            $this->view->assign('url', 'index/index' );
            $this->setTemplate('error');
            return $this->view;
        }

        // 注文状態
        $mvcd = new MypageViewCode( $this->app->dbAdapter );
        $class1 = $mvcd->getClass1ByClass3('198', $cardbrand_code)->current()['Class1'];
        if(!$class1) {
            $class1 = 5;
        }
        // 基幹反映指示インタフェース(T_MypageToBackIF)登録
        $mdlMyToBack = new TableMypageToBackIF($this->app->dbAdapter);
        $ifDataArray = array(
            'sps_transaction_id' => $sps_transaction_id, // API型での決済時に返却された[処理SBPSトランザクションID(32桁)]
            'tracking_id' => $tracking_id, // 処理対象トラッキングID(14桁)
            'cardbrand_code' => $cardbrand_code,
            'payment_method' => $class1,
            'amount' => $odrInf['ClaimedBalance'],
        );
        $ifData = Json::encode($ifDataArray);
        $data = array (
            'Status'        => '0', // 0：指示
            'Reason'        => NULL,
            'IFClass'       => '4', // 連携区分(4：クレジット決済手続き)
            'IFData'        => $ifData,
            'OrderSeq'      => $this->userInfo->OrderSeq,
            'ManCustId'     => NULL,
            'CustomerId'    => NULL,
            'ValidFlg'      => '1'
        );
        $mdlMyToBack->saveNew($data);
        
        // T_SbpsReceiptControl登録
        $prm = array(
            'OrderSeq'     => $this->userInfo->OrderSeq,
            'PayType'      => 1,
            'PaymentName'  => $class1,
            'ReceiptDate'  => date('Y-m-d H:i:s'),
            'RegistId'      => 1,
            'UpdateId'      => 1,
            'ValidFlg'      => '1'
        );
        $mdlSbpsReceiptControl = new TableSbpsReceiptControl($this->app->dbAdapter);
        $mdlSbpsReceiptControl->saveNew($prm);

        // 悲観ロック解除
        $this->app->dbAdapter
            ->query('UPDATE T_CreditPayment SET PaymentType=1,UpdateDate=:UpdateDate WHERE OrderSeq=:OrderSeq')
            ->execute(array(
                          ':OrderSeq' => $this->userInfo->OrderSeq,
                          ':UpdateDate' => date('Y-m-d H:i:s')
                      ));
        $this->app->dbAdapter->getDriver()->getConnection()->commit();

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

,      (SELECT MAX(ss.NumUseDay) FROM MV_SiteSbpsPayment ss
		INNER JOIN MV_SbpsPayment sp on (sp.SbpsPaymentId=ss.PaymentId)
		WHERE ss.SiteId=o.SiteId AND ss.ValidFlg=1 AND ss.UseStartDate <= :Now AND sp.PaymentName LIKE '%credit%'
		AND DATE_ADD((SELECT MIN(ch.ClaimDate) FROM MV_ClaimHistory AS ch WHERE ch.OrderSeq = o.OrderSeq AND ch.ClaimPattern = 1), 
		INTERVAL ss.NumUseDay DAY) >= CURRENT_DATE()) AS  CreditMaxNumUseDay

,      (SELECT GROUP_CONCAT(sp.LogoUrl ORDER BY sp.SortId ASC) FROM MV_SbpsPayment sp
		INNER JOIN MV_SiteSbpsPayment ss on (ss.PaymentId=sp.SbpsPaymentId)
		WHERE ss.SiteId=o.SiteId AND ss.ValidFlg=1 AND ss.UseStartDate <= :Now AND sp.PaymentName LIKE '%credit%'
        AND DATE_ADD((SELECT MIN(ch.ClaimDate) FROM MV_ClaimHistory AS ch WHERE ch.OrderSeq = o.OrderSeq AND ch.ClaimPattern = 1) ,
		INTERVAL ss.NumUseDay DAY) >= CURRENT_DATE()) AS CreditLogoUrl
FROM   MV_Order o
       INNER JOIN MV_ClaimControl cc ON (cc.OrderSeq = o.OrderSeq)
       INNER JOIN MV_Enterprise e ON (e.EnterpriseId = o.EnterpriseId)
       INNER JOIN MV_Site s ON (s.SiteId = o.SiteId)
       INNER JOIN MAV_Order AS ao ON (ao.OrderSeq = o.OrderSeq)
WHERE  o.OrderSeq = :OrderSeq
        ";

        return $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq, ':Now' => date('Y-m-d H:i:s')))->current();
    }

    /**
     * (SB Payment Service)クレジットカード決済：決済要求（ワンタイムトークン利用）
     *
     * @param array $params パラメタ
     * @param string $sps_transaction_id 処理SBPS トランザクションID
     * @param string $tracking_id 処理対象トラッキングID
     * @param string $err_code エラーコード(8桁)
     * @param array $errorMessages エラーメッセージ文字列の配列(※通信障害系の内容)
     * @param string $cardbrand_code
     * @return boolean true;成功／false:失敗
     */
    protected function _SBPaymentSettlementRequest($params, &$sps_transaction_id, &$tracking_id, &$err_code, &$errorMessages, &$cardbrand_code) {
        $request_date = date('YmdHis');

        $mdlc = new \models\View\MypageViewCustomer($this->app->dbAdapter);
        $customerInfo = $mdlc->findCustomer(array('OrderSeq' => $this->userInfo->OrderSeq))->current();

        $sha1str =  $params['merchantid'] .
            $params['serviceid'] .
            $customerInfo['EntCustSeq'] .
            $params['orderId'] . '_' . $request_date .
            $this->userInfo->OrderSeq .
            $params['claimedBalance'] .
            $params['token'] .
            $params['tokenKey'] .
            '1' .
            '0' .
            $request_date .
            $params['hashkey']
        ;

        $req  = '<?xml version="1.0" encoding="Shift_JIS"?>';
        $req .= '<sps-api-request id="ST01-00131-101">';
        $req .= '<merchant_id>' . $params['merchantid'] . '</merchant_id>';
        $req .= '<service_id>' . $params['serviceid'] . '</service_id>';
        $req .= '<cust_code>' . $customerInfo['EntCustSeq'] . '</cust_code>';
        $req .= '<order_id>' . $params['orderId'] . '_' . $request_date . '</order_id>';
        $req .= '<item_id>' . $this->userInfo->OrderSeq . '</item_id>';
        $req .= '<amount>' . $params['claimedBalance'] . '</amount>';
        $req .= '<pay_option_manage>';
        $req .= '<token>' . $params['token'] . '</token>';
        $req .= '<token_key>' . $params['tokenKey'] . '</token_key>';
        $req .= '<cardbrand_return_flg>1</cardbrand_return_flg>';
        $req .= '</pay_option_manage>';
        $req .= '<encrypted_flg>0</encrypted_flg>';
        $req .= '<request_date>' . $request_date . '</request_date>';
        $req .= '<sps_hashcode>' . sha1($sha1str) . '</sps_hashcode>'; // 40文字の16進数
        $req .= '</sps-api-request>';
        $this->app->sbpsLogger->info("Credit SettlementRequest1:" . $req);

        // リクエスト送信
        $xmlstr = '';
        $isSuccess = $this->_SBPaymentSendRequest($req, $xmlstr, $errorMessages);
        if ($isSuccess == false) {
            return false;
        }

        $this->app->logger->info('SettlementRequest:' . $xmlstr);
        $this->app->sbpsLogger->info("Credit SettlementRequest2:" . $xmlstr);

        $xml = simplexml_load_string($xmlstr);
        $json = json_encode($xml);
        $array = json_decode($json, true);

        if ($array['res_result'] == 'NG') {
            $err_code = $array['res_err_code'];
            return false;
        }

        $sps_transaction_id = $array['res_sps_transaction_id'];
        $tracking_id = $array['res_tracking_id'];
        $cardbrand_code = $array['res_pay_method_info']['cardbrand_code'];
        
        return true;
    }

    /**
     * (SB Payment Service)クレジットカード決済：確定要求
     *
     * @param array $params パラメタ
     * @param string $sps_transaction_id 処理SBPS トランザクションID
     * @param string $err_code エラーコード(8桁)
     * @param array $errorMessages エラーメッセージ文字列の配列(※通信障害系の内容)
     * @return boolean true;成功／false:失敗
     */
    protected function _SBPaymentConfirmRequest($params, $sps_transaction_id, &$err_code, &$errorMessages) {
        $request_date = date('YmdHis');

        $sha1str =  $params['merchantid'] .
            $params['serviceid'] .
            $sps_transaction_id .
            $request_date .
            $params['hashkey']
        ;

        $req  = '<?xml version="1.0" encoding="Shift_JIS"?>';
        $req .= '<sps-api-request id="ST02-00101-101">';
        $req .= '<merchant_id>' . $params['merchantid'] . '</merchant_id>';
        $req .= '<service_id>' . $params['serviceid'] . '</service_id>';
        $req .= '<sps_transaction_id>' . $sps_transaction_id . '</sps_transaction_id>';
        $req .= '<request_date>' . $request_date . '</request_date>';
        $req .= '<sps_hashcode>' . sha1($sha1str) . '</sps_hashcode>'; // 40文字の16進数
        $req .= '</sps-api-request>';
        $this->app->sbpsLogger->info("Credit ConfirmRequest1: " . $req);

        // リクエスト送信
        $xmlstr = '';
        $isSuccess = $this->_SBPaymentSendRequest($req, $xmlstr, $errorMessages);
        if ($isSuccess == false) {
            return false;
        }

        $this->app->logger->info('ConfirmRequest:' . $xmlstr);
        $this->app->sbpsLogger->info("Credit ConfirmRequest2: " . $xmlstr);

        $xml = simplexml_load_string($xmlstr);
        $json = json_encode($xml);
        $array = json_decode($json, true);

        if ($array['res_result'] == 'NG') {
            $err_code = $array['res_err_code'];
            return false;
        }

        return true;
    }

    /**
     * (SB Payment Service)リクエスト送信
     *
     * @param string $params オンライン決済ASPに渡すパラメータ
     * @param string $responseBody レスポンスデータ
     * @param array $errorMessage エラーメッセージ文字列の配列
     * @return boolean true:成功／false:失敗
     */
    protected function _SBPaymentSendRequest($params, &$responseBody, &$errorMessages) {

        // オンライン決済URL取得
        $url = $this->app->dbAdapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'sbpayment' AND Name = 'url' ")->execute(null)->current()['PropValue'];
        $timeout = $this->app->dbAdapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'sbpayment' AND Name = 'timeout' ")->execute(null)->current()['PropValue'];

        // (オンライン決済URL書式化 : T_Enterpriseに設定される[Basic認証ID][Basic認証PW]でﾌﾟﾚｰｽﾌｫﾙﾀﾞを置き換える)
        $odrInf = $this->_getOrderInfo($this->userInfo->OrderSeq);
        $url = sprintf($url, $odrInf['BasicId'], $odrInf['BasicPw']);

        $option = array(
                'adapter'=> 'Zend\Http\Client\Adapter\Curl', // SSL通信用に差し替え
                'ssltransport' => 'tls',
                'maxredirects' => 1,                         // 試行回数(maxredirects) を 1 に設定
        );
        $client = new Client($url, $option);
        $client->setOptions(array('timeout' => (int)$timeout, 'keepalive' => true, 'maxredirects' => 1));

        try {
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

            $this->app->logger->info('Receive:' . $res_msg);
            $this->app->sbpsLogger->info("Credit Receive:" . $res_msg);

            if ($status == 200) {
                $responseBody =  $response->getBody();
                return true;
            }

            $errorMessages[] = 'オンライン決済通信エラー';
            $errorMessages[] = 'ステイタス : ' . $status;
            $errorMessages[] = 'メッセージ : ' . $res_msg;
            return false;
        }
        catch (\Exception $err) {
            $errorMessages[] = 'オンライン決済通信エラー';
            $errorMessages[] = '(データ送信に失敗しました)';
            $this->app->logger->info($err->getMessage());
            $this->app->sbpsLogger->info("Credit Error:" . $err->getMessage());
            return false;
        }
    }

    /**
     * (SB Payment Service)エラー情報生成
     *
     * @param string $err_code エラーコード(8桁)
     * @return array エラーメッセージ文字列の配列
     */
    protected function _SBPaymentMakeErrorInfo($err_code) {
        $messages = array();

        $sql     = " SELECT Note FROM MV_Code WHERE CodeId = :CodeId AND Class1 = :Class1 AND Class2 IS NULL ";
        $sql_knd = " SELECT Note FROM MV_Code WHERE CodeId = :CodeId AND Class1 = :Class1 AND Class2 = :Class2 ";

        // 決済手段
        $knd = substr($err_code, 0, 3);
        $message = '・決済手段 : ';
        $row = $this->app->dbAdapter->query($sql)->execute(array(':CodeId' => 200, ':Class1' => $knd))->current();
        $message .= (($row) ? $row['Note'] : '-');
        $messages[] = $message . '(' . $knd . ')';

        // 種別
        $key = substr($err_code, 3, 2);
        $message = '・種別 : ';
        $row = $this->app->dbAdapter->query($sql_knd)->execute(array(':CodeId' => 201, ':Class1' => $key, ':Class2' => $knd))->current();
        if ($row) {
            $message .= $row['Note'];// (決済手段固有の情報)
        }
        else {
            $row = $this->app->dbAdapter->query($sql)->execute(array(':CodeId' => 201, ':Class1' => $key))->current();
            $message .= (($row) ? $row['Note'] : '-');
        }
        $messages[] = $message . '(' . $key . ')';

        // 項目
        $key = substr($err_code, 5, 3);
        $message = '・項目 : ';
        $row = $this->app->dbAdapter->query($sql_knd)->execute(array(':CodeId' => 202, ':Class1' => $key, ':Class2' => $knd))->current();
        if ($row) {
            $message .= $row['Note'];// (決済手段固有の情報)
        }
        else {
            $row = $this->app->dbAdapter->query($sql)->execute(array(':CodeId' => 202, ':Class1' => $key))->current();
            $message .= (($row) ? $row['Note'] : '-');
        }
        $messages[] = $message . '(' . $key . ')';

        return $messages;
    }

    /**
     * クレジットカード払いか可能かの判定
     * ※購入履歴表示を流用
     */
    private function judgeCreditCardPayment() {
        $orderSeq = $this->userInfo->OrderSeq;

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
}
