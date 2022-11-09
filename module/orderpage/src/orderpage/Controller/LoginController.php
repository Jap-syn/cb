<?php
namespace orderpage\Controller;

use orderpage\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Base\Auth\BaseAuthManager;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Coral\Coral\Controller\CoralControllerMypageAction;
use models\Table\TableSystemProperty;
use models\View\MypageViewMypageOrder;
use models\View\MypageViewCode;
use Zend\Json\Json;

class LoginController extends CoralControllerMypageAction {
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
     * メッセージの種類
     * @var unknown
     */
    protected $_messageInfo = array(
        '1' => '・パスワードが未入力です。',
        '2' => '・お電話番号が未入力です。',
        '3' => '・お電話番号またはパスワードに間違いがあります。',
    );

    /**
     * 初期化処理
    */
    protected function _init() {
        $this->app = Application::getInstance();

        // ページタイトルとスタイルシート、JavaScriptを設定
        $this->setPageTitle( 'ログイン' )
            ->addJavaScript( '../js/prototype.js' )
            ->addJavaScript( '../js/bytefx.js' );

        if ($this->is_mobile_request()) {
            $this->addStyleSheet( './css_sp/orderpage.css')
                 ->addStyleSheet( './css_sp/orderpage_login.css' );
        } else {
            $this->addStyleSheet( './css/orderpage.css')
                 ->addStyleSheet( './css/orderpage_login.css' );
        }

        // 認証マネージャとFlashMessengerをメンバに設定
        $this->authManager = Application::getInstance()->authManager;
        $this->messenger = $this->flashMessenger();

        $oemId = 0;
        $this->view->assign( 'OemId', $oemId);
        $check = strpos($_SERVER['QUERY_STRING'], 'spapp2');
        if ($check === 0) {
            $this->view->assign( 'KochiraUrl', $this->app->dbAdapter->query(" SELECT CONCAT(KeyContent,Class2) as KeyContent FROM MV_Code WHERE CodeId = 105 AND KeyCode = :KeyCode "
                )->execute(array(':KeyCode' => $oemId))->current()['KeyContent']);
        }else{
            $this->view->assign( 'KochiraUrl', $this->app->dbAdapter->query(" SELECT KeyContent FROM MV_Code WHERE CodeId = 105 AND KeyCode = :KeyCode "
                )->execute(array(':KeyCode' => $oemId))->current()['KeyContent']);
        }
    }

    /**
     * ログインアクション。ログインフォームを表示する
    */
    public function loginAction() {

        $params = $this->getParams();

        // accessidによる遷移判定
        $accessKeyValidToDate = '1970-01-01'; // URL用有効期限

        $accessid = $params['accessid'];
        $messages = $params['messages'];

        // アプリケーションロードバランサ
        $lb = $this->app->config['app_load_balancer'];
        if ($lb['app_redirect']) {
            $sever_list = explode(',', $lb['redirect_server']);
            $rand = mt_rand(0, sizeof($sever_list)-1);
            if ($sever_list[$rand] == 1) {
                $replace_str = '';
            } else {
                $replace_str = $sever_list[$rand];
            }
            $url = 'https://' . str_replace('?', $replace_str, $lb['redirect_domain_cb']) . '/orderpage/login/login';

            if (strpos($_SERVER['REQUEST_URI'], 'accountlock') !== false) {
                $url .= '/accountlock/on';
            }
            if (isset($accessid)) {
                $url .= '/accessid/' . $accessid;
            }
            if (isset($messages)) {
                $url .= '/messages/' . $messages;
            }

            return $this->redirect()->toUrl($url);
        }

        if (strlen($accessid) > 0) {
            $this->setPageTitle( '簡易ログイン' );

            // 注文ﾏｲﾍﾟｰｼﾞによる判定がOKか否か
            $mdlvmo = new MypageViewMypageOrder($this->app->dbAdapter);
            $row = $mdlvmo->findByAccessKey($accessid)->current();
            if ($row) {
                $accessKeyValidToDate = $row['AccessKeyValidToDate'];
            }

            if ( strtotime( date( 'Y-m-d H:i:s' ) ) > strtotime( $accessKeyValidToDate )) {
                // ﾊﾟｽﾜｰﾄﾞが取得できない もしくは 有効期限が切れている場合
                // 期限切れ画面を表示
                $this->setTemplate('invalid');

                // 有効期限切れの文言を設定
                $sql = ' SELECT * FROM MV_Code WHERE CodeId = :CodeId AND KeyCode = :KeyCode ';
                $prm = array(
                    ':CodeId' => $this->is_mobile_request() ? 186 : 185 ,
                    ':KeyCode' => 0,
                );
                $row = $this->app->dbAdapter->query($sql)->execute($prm)->current();
                $note = $row ? $row['Note'] : '';
                $check = strpos($_SERVER['QUERY_STRING'], 'spapp2');
                $check_spapp = strpos($_SERVER['QUERY_STRING'], 'spapp');
                if ($check === 0) {
                    $note = str_replace("login/login", "login/login?spapp2", $note);
                }elseif($check_spapp === 0) {
                    $note = str_replace("login/login", "login/login?spapp", $note);
                }
                
                $this->view->assign( 'Note', $note );
                return $this->view;
            }
        }
        $this->view->assign( 'accessid', $accessid );

        // メッセージ情報の構築
        $messages = explode(',', $params['messages']);
        $loginMessages = array();
        foreach($messages as $key => $value) {
            $msg = $this->_messageInfo[$value];
            if (isset($msg)) {
                $loginMessages[] =(isset($accessid)) ? str_replace('またはパスワード', '', $msg) : $msg;
            }
        }
        $this->view->assign( 'loginMessages', $loginMessages );

//        //年末年始文言取得
//        $mdlCode = new MypageViewCode($this->app->dbAdapter);
//        $row = $mdlCode->find(203, 1)->current();
//        if(isset($row) && $row['ValidFlg'] == 1){
//            $this->view->assign("yearMessage", $row['Note']);
//        }

        // CB_B2C_DEV-10
        $mvCodeMsgs= array();
        foreach($params as $keyContent => $paramMsg){
        	switch($keyContent){
        		case 'messages':
        		case 'accessid':
        			break;
        		default:
        			$row = $this->app->dbAdapter->query(" SELECT Note,ValidFlg FROM MV_Code WHERE CodeId = 208 AND KeyContent = :KeyContent "
        					)->execute(array(':KeyContent' => $keyContent))->current();
        			if(isset($row) && $row['ValidFlg'] == 1){
        				$mvCodeMsgs[] = $row['Note'];
        			}
        			break;
        	}
        }
        $this->view->assign("mvCodeMsgs", $mvCodeMsgs);

        return $this->view;
    }

    /**
     * 認証アクション
    */
    public function authAction() {
        $app = Application::getInstance();
        $messages = array();
        // check if has spapp2 in URL
        $check = strpos($_SERVER['HTTP_REFERER'], 'spapp2');

        if( $this->authManager->isAuthenticated() ) {
            //// 認証済みの場合はデフォルトコントローラへリダイレクト
            //$this->_redirect( 'index/index' );
            // 認証済みの場合はログアウト
            $this->_LogOut(true, false);
            //return;
        }

        // 認証処理
        $id = $this->params()->fromPost( 'login_id', '' );
        $psw = $this->params()->fromPost( 'password', '' );
        $accessid = $this->params()->fromPost( 'accessid', null );
        if (isset($accessid)) {
            // ｱｸｾｽ用IDが設定されている場合、ﾊﾟｽﾜｰﾄﾞを探す
            $mdlvmo = new MypageViewMypageOrder($this->app->dbAdapter);
            $row = $mdlvmo->findByAccessKey($accessid)->current();
            $psw = $row['Token'];
        }

        // 不正アクセスIP判定処理
        $obj = new \models\Logic\LogicNgAccessIp(Application::getInstance()->dbAdapter);
        $result = $obj->isNgAccessMypage($_SERVER['NGCHECK_REMOTE_ADDR'], $_SERVER['NGCHECK_FILE_PATH']);
        if($result){
            $url = "https://" . $_SERVER['SERVER_NAME'] . '/orderpage/login/login';
            if ($check !== false) {
                $url .= '?spapp2';
            }
            header("Location: $url");
            die;
//             $this->_redirect("login/login");
//             return;
        }

        if( empty( $id ) || empty( $psw )) {
            if( empty( $psw )) {
                // ・パスワードが未入力です。
                $messages[] = '1';
            }
            if( empty( $id )) {
                // 会員ID（電話番号）が未入力です。
                $messages[] = '2';
            }
//             $url = 'login/login';
            $url = "https://" . $_SERVER['SERVER_NAME'] . '/orderpage/login/login';
            if ($check !== false) {
                $url .= '?spapp2';
            }
            if (isset($accessid)) {
                $url .= '/accessid/' . $accessid;
            }

            if (!empty($messages)) {
                $url .= '/messages/' . implode(',', $messages);
            }
            
            header("Location: $url");
            die;
//             $this->_redirect( $url );
//             return;
        }

        $phone = $id;
        $mdlnamo = new \models\Table\TableNgAccessMypageOrder($app->dbAdapter);
        $rowNgAccessMypageOrder = $mdlnamo->findOemPhone(0, $phone)->current(); // 不正アクセス注文マイページ情報の取得

        $obj = new \models\Logic\LogicNgAccessIp($this->app->dbAdapter);
        $isExistMypageOrder = $obj->isExistMypageOrder(0, $phone);              // 注文マイページに存在するか？

        // LoginId に パスワード を追加
        $id = $psw . $id;

        $clientInfo = sprintf( 'login_id = %s, password = *****, remote_host = %s, remote_addr = %s',
                              $id,
                              gethostbyaddr( f_get_client_address() ),      // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
                              f_get_client_address()        // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
                             );

        if( !$this->authManager->login( $id, $psw )->isValid() ) {

            //SMBCの考慮を行う為、SMBCログイン認証への強制遷移
//            // SMBC認証要のURLを取得
//            $mdlsysp = new TableSystemProperty($this->app->dbAdapter);
//            $url = $mdlsysp->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'smbc_orderpage_url');

            // アプリケーションロードバランサ
            $lb = $this->app->config['app_load_balancer'];
            $sever_list = explode(',', $lb['redirect_server']);
            $rand = mt_rand(0, sizeof($sever_list)-1);
            if ($sever_list[$rand] == 1) {
                $replace_str = '';
            } else {
                $replace_str = $sever_list[$rand];
            }
            $url = 'https://' . str_replace('?', $replace_str, $lb['redirect_domain_oem']) . '/sf/orderpage';

            $url .= '/login/auth';
            if ($check !== false) {
                $url .= '?spapp2';
            }
            $strhtml = "";
            $strhtml .= "<!DOCTYPE html>";
            $strhtml .= "<head>";
            $strhtml .= "<meta charset='utf-8'>";
            $strhtml .= "</head>";
            $strhtml .= "<html lang='ja'>";
            $strhtml .= "<body onload='document.returnForm.submit();'>";
            $strhtml .= "<form name='returnForm' method='post' action='$url'>";
            $strhtml .= "<input type='hidden' id='login_id' name='login_id' value='".htmlspecialchars($this->params()->fromPost( 'login_id', '' ),ENT_QUOTES,"UTF-8")."''>";
            $strhtml .= "<input type='hidden' id='password' name='password' value='".htmlspecialchars($psw,ENT_QUOTES,"UTF-8")."''>";
            if (isset($accessid)) {
                $strhtml .= "<input type='hidden' id='accessid' name='accessid' value='".htmlspecialchars($accessid,ENT_QUOTES,"UTF-8")."''>";
            }
            $strhtml .= "</form>";
            $strhtml .= "</body>";
            $strhtml .= "</html>";
            echo $strhtml;
            exit();
            // 以降の処理は行わない

            $this->messenger->addMessage( '・会員ID（電話番号）またはパスワードに間違いがあります。' );
            $this->_redirect( 'login/login' );
            return;
        }

        // 不正アクセスIP関連処理
        $obj->resetNgAccess($_SERVER['NGCHECK_REMOTE_ADDR']);

        // 認証成功も連続不正アクセスと判断される時の処理
        if (!$obj->isNotNgAccessMypageOrder($rowNgAccessMypageOrder)) {
            $this->authManager->logout();
            session_regenerate_id(true);
            session_destroy();
            $url = "https://" . $_SERVER['SERVER_NAME'] . 'login/login/accountlock/on';
            if ($check !== false) {
                $url .= '?spapp2';
            }
            header("Location: $url");
            die;
//             $this->_redirect( 'login/login/accountlock/on' );
//             return;
        }

        // 認証成功時はNOTICEでログ出力（09.07.17 eda）
        $userInfo = $this->authManager->getUserInfo();
        $altUserInfo = $this->authManager->getAlternativeUserInfo();
        $clientInfo = sprintf( 'login_id = %s, password = *****, remote_host = %s, remote_addr = %s',
                              $altUserInfo ? sprintf( '%s (as %s)', $altUserInfo->LoginId, $userInfo->LoginId ) : $userInfo->LoginId,
                              gethostbyaddr( f_get_client_address() ),      // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
                              f_get_client_address()        // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
                             );

        // 認証成功後のリダイレクトURL
        $target = 'index/index';

        $_SESSION['loginAccessId'] = isset($accessid) ? $accessid : null;

        // 最終ログイン日時登録or更新
        $rowmol = $this->app->dbAdapter->query(" SELECT Seq FROM T_MypageOrderLogin WHERE Seq = :Seq ")->execute(array(':Seq' => $userInfo->Seq))->current();
        $prm = array(':Seq' => $userInfo->Seq, ':LastLoginDate' => date('Y-m-d H:i:s'));
        if ($rowmol) {
            $this->app->dbAdapter->query(" UPDATE T_MypageOrderLogin SET LastLoginDate = :LastLoginDate WHERE Seq = :Seq ")->execute($prm);
        }
        else {
            $this->app->dbAdapter->query(" INSERT INTO T_MypageOrderLogin (Seq, LastLoginDate) VALUES (:Seq, :LastLoginDate) ")->execute($prm);
        }

        // セッションIDを振りなおす
        session_regenerate_id(true);

        $this->_redirect( $target );
    }

    /**
     * ログアウトアクション。無条件にログアウトし、ログインフォームへリダイレクト
     *
    */
    public function logoutAction() {
        $params = $this->getParams();
        $orderSeqA = $params['oseq'];
        $this->userInfo = Application::getInstance()->authManager->getUserInfo();
        $orderSeqB = $this->userInfo->OrderSeq;
        
        $flagHasSiteTodo = Application::getInstance()->getFlagPaymentAfterArrivalFlg();
        
        if ( $flagHasSiteTodo > 0 ) {
            if ( $orderSeqA <> $orderSeqB ) {
                $this->_LogOut(false, true, true);
            } else {
                $this->_LogOut(true, true, true);
            }
        } else {
            if ( $orderSeqA <> $orderSeqB ) {
                $this->_LogOut(false, true, false);
            } else {
                $this->_LogOut(true, true, false);
            }
        }
    }

    protected function _LogOut($outFlg = true, $sendFlg = true, $paymentAfterArrivalFlg = true) {
        //
        if ($outFlg) {
            $accessid = $_SESSION['loginAccessId'];

            $this->authManager->logout();

            // セッションIDを振りなおす
            session_regenerate_id(true);

            // セッション全体を破棄
            session_destroy();
        }

        //
        if ($sendFlg) {
            if ($paymentAfterArrivalFlg) {
                if ( isset($accessid) ) {
                    $urlRedirect = "https://" . $_SERVER['SERVER_NAME'] . '/orderpage/login/login/accessid/' . $accessid . '?spapp2';
                    header("Location: $urlRedirect");
                    die;
//                     $this->_redirect( 'login/login/accessid/' . $accessid . '?spapp2' );
                } else {
                    $urlRedirect = "https://" . $_SERVER['SERVER_NAME'] . '/orderpage/login/login?spapp2';
                    header("Location: $urlRedirect");
                    die;
//                     $this->_redirect( 'login/login?spapp2' );
                }
            } else {
                if ( isset($accessid) ) {
                    $this->_redirect( 'login/login/accessid/' . $accessid );
                } else {
                    $this->_redirect( 'login/login' );
                }
            }
        }
    }
}
