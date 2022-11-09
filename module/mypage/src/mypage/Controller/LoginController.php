<?php
namespace mypage\Controller;

use mypage\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Base\Auth\BaseAuthManager;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Zend\Json\Json;
use models\Logic\AccountValidity\LogicAccountValidityPasswordValidator;
use models\Logic\LogicNormalizer;
use models\Logic\LogicMail;
use models\Table\TableMypageCustomer;
use models\Table\TableMypagePasswordHistory;
use models\Table\TableSystemProperty;
use Coral\Base\BaseGeneralUtils;
use models\Table\TableCode;
use models\View\MypageViewCode;

class LoginController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';

    /**
     * @var Application
     */
    private $app;

    /**
     * @var BaseAuthManager
    */
    private $authManager;

    /**
     * @var FlashMessenger
    */
    private $messenger;

    /**
     * @var CookieName
     */
    private $cookieName;

    /**
     * 初期化処理
    */
    protected function _init() {
        $this->app = Application::getInstance();
        // ページタイトルとスタイルシート、JavaScriptを設定
        $this->setPageTitle( 'ログイン' )
            ->addJavaScript( '../js/prototype.js' )
            ->addJavaScript( '../js/bytefx.js' );

        if ($this->is_mobile_request())
        {
            $this->addStyleSheet( './css_sp/mypage.css' )
                 ->addStyleSheet( './css_sp/mypage_login.css' );
        }
        else
        {
            $this->addStyleSheet( './css/mypage.css' )
                 ->addStyleSheet( './css/mypage_login.css' );
        }
        // 認証マネージャとFlashMessengerをメンバに設定
        $this->authManager = Application::getInstance()->authManager;
        $this->messenger = $this->flashMessenger();
        $this->cookieName = "0". "@". "autologin";
    }

    /**
     * ログインアクション。ログインフォームを表示する
    */
    public function loginAction() {
        // 自動ログイン用クッキー情報が空でない場合
        if( isset($_COOKIE[$this->cookieName]) && ! empty($_COOKIE[$this->cookieName]) ) {
            $this->view->assign( 'autologin', true );

            // 認証アクションへ
            $this->_redirect( 'login/auth' );
            return;
        }

        // とりあえず OemId は 0 とする (2015/06/08)
        $this->view->assign( 'oemid', 0 );
        $this->view->assign('loginMessages', $this->messenger->getMessages() );
        // 会員IDを忘れた方用URL
        $faqlink = $this->app->dbAdapter->query(
            " SELECT PropValue FROM T_SystemProperty WHERE Module = 'mypage' AND Category = 'application_global' AND Name = 'faq_link' ")->execute(null)->current()['PropValue'];
        $this->view->assign( 'faqlink', $faqlink );

        //年末年始文言取得
        $mdlCode = new MypageViewCode($this->app->dbAdapter);
        $row = $mdlCode->find(203, 2)->current();
        if(isset($row) && $row['ValidFlg'] == 1){
            $this->view->assign("yearMessage", $row['Note']);
        }
        return $this->view;
    }

    /**
     * 認証アクション
    */
    public function authAction() {
        $param = $this->getParams();

        // 自動ログイン用クッキー情報が空の場合、手動ログインと判断する。
        // 自動ログイン処理
        if (! empty($_COOKIE[$this->cookieName])) {
            $autoLoginKey = $_COOKIE[$this->cookieName];

            // 自動ログイン用テーブルを検索
            $sql = "SELECT * FROM T_MypageAutoLogin WHERE AutoLoginKey = :AutoLoginKey";
            $autoData = $this->app->dbAdapter->query($sql)->execute(array(':AutoLoginKey' => $autoLoginKey))->current();
            if ($autoData) {
                if (! empty($autoLoginKey)) {
                    // 存在していれば、DELETEする
                    $this->deleteAutoLogin($autoLoginKey);
                }
                // 自動ログイン用クッキー情報をセット
                $this->setupAutoLogin($autoData['MailAddress']);
            }

            // 自動ログイン用パースワードを検索
            $sql = ' SELECT * FROM T_MypageCustomer WHERE MailAddress = :MailAddress AND OemId = :OemId AND ValidFlg = 1 ';
            $prm = array(
                    ':MailAddress' => $autoData['MailAddress'],
                    ':OemId' => 0
            );
            $loginInfo = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

            // 認証マネージャとFlashMessengerをメンバに設定
            $this->authManager = Application::getInstance()->authManager;
            $this->authManager->login( $loginInfo['LoginId'], $loginInfo['LoginPasswd'] );
        } else {
            $id = $param['login_id'];
            $psw = $param['password'];
            $loginId = "0". "@". $id;

            $mdlmc = new \models\Table\TableMypageCustomer($this->app->dbAdapter);
            $rowMypageCustomer = $mdlmc->findLoginId($loginId)->current();   // マイページ顧客情報の取得

            $clientInfo = sprintf( 'login_id = %s, password = *****, remote_host = %s, remote_addr = %s',
                                  $loginId,
                                  gethostbyaddr( f_get_client_address() ),      // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
                                  f_get_client_address()        // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
                                 );

            // 不正アクセスIP判定処理
            $obj = new \models\Logic\LogicNgAccessIp(Application::getInstance()->dbAdapter);
            $result = $obj->isNgAccessMypage($_SERVER['NGCHECK_REMOTE_ADDR'], $_SERVER['NGCHECK_FILE_PATH']);
            if($result){
                $this->_redirect("login/login");
                return;
            }

            // チェック処理
            if( empty( $id ) || empty( $psw )) {
                if( empty( $psw )) {
                    $this->messenger->addMessage( '・パスワードが未入力です。' );
                }
                if( empty( $id )) {
                    $this->messenger->addMessage( '・会員ID（メールアドレス）が未入力です。' );
                }
                $this->_redirect( 'login/login' );
                return;
            }
            $obj = new \models\Logic\LogicNgAccessIp($this->app->dbAdapter);
            if( !$this->authManager->login( $loginId, $psw )->isValid() ) {
                // 不正アクセス処理
                $obj->registNgAccessMypage($_SERVER['NGCHECK_REMOTE_ADDR'], $_SERVER['NGCHECK_FILE_PATH']);
                $obj->updateMypageCustomerNgAccess($rowMypageCustomer);

                $this->messenger->addMessage( '・会員ID（メールアドレス）またはパスワードに間違いがあります。' );
                $this->_redirect( 'login/login' );
                return;
            }

            // 不正アクセスIP関連処理
            $obj->resetNgAccess($_SERVER['NGCHECK_REMOTE_ADDR']);

            // 認証成功も連続不正アクセスと判断される時の処理
            if (!$obj->isNotNgAccessMypageCustomer($rowMypageCustomer)) {
                $this->authManager->logout();
                session_regenerate_id(true);
                session_destroy();
                $this->view->assign('isAccountLock', true);
                $this->setTemplate('login');
                return $this->view;
            }

            // 「次回から自動ログイン」にチェックが入っている場合
            if (! empty($param['autologin'])) {
                // 該当データ削除
                $sql = "DELETE FROM T_MypageAutoLogin WHERE MailAddress = :MailAddress AND OemId = :OemId";
                $this->app->dbAdapter->query($sql)->execute(array(':MailAddress' => $param['login_id'], ':OemId' => 0));

                // 新たに自動ログイン用クッキー情報をセット
                $this->setupAutoLogin($param['login_id']);
            }
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

        // 最終ログイン日時保存
        if (!$altUserInfo) {
            $sql = ' UPDATE T_MypageCustomer SET LastLoginDate = :LastLoginDate WHERE CustomerId = :CustomerId ';
            $prm = array(
                    ':CustomerId' => $userInfo->CustomerId,
                    ':LastLoginDate' => date( 'Y-m-d H:i:s' ),
            );
            $this->app->dbAdapter->query( $sql )->execute( $prm );
        }

        // ======================= パスワード期限 間近チェック =======================
        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        $mdlph = new TableMypagePasswordHistory($this->app->dbAdapter);

        $PassHist = ResultInterfaceToArray($mdlph->findnew(5, $userInfo->LoginId));

        $passAlert = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdLimitAlertDay');

        if(isset($PassHist) && (($PassHist[0]['PasswdLimitDay'] >= date('Y-m-d') && $PassHist[0]['PasswdLimitDay'] <= date('Y-m-d', strtotime($passAlert ." day")))) || $PassHist[0]['PasswdLimitDay'] < date('Y-m-d')){
            $target = 'edit/chgpw';
        }
        // ===========================================================================

        $this->_redirect( $target );
        // セッションIDを振りなおす
        session_regenerate_id(true);

        return $this->_redirect( $target );
    }

    /**
     * 自動ログイン情報の設定
     */
    public function setupAutoLogin($mailAddress)
    {
        /* **************************************************************************************
         * 2015/09/17 メモ
         * $cookieName：クッキー名
         * $autoLoginKey：自動ログイン用キー値
         * 　→ 一意になるキー + ランダムな数値 + 固定文字列（salt）を暗号化して作成
         * $cookieExpire：有効期間
         * 　→ 設定しない場合、ブラウザを立ち上げている間のみ有効。
         * 　　 ※ 適宜変更すること。
         * $cookiePath：パス
         * ************************************************************************************** */
        $autoLoginKey = md5(uniqid() . mt_rand(1, 999999999) . '_autoLogin');
        $cookieExpire = time() + 3600 * 24 * 7;     // 有効期間は7日間
        $cookiePath = "/";

        // 自動ログイン用テーブルにログイン情報をINSERT
        $sql = "INSERT INTO T_MypageAutoLogin (MailAddress, AutoLoginKey, BrowserInfo, RegistDate, UpdateDate, OemId) VALUES (:MailAddress, :AutoLoginKey, :BrowserInfo, :RegistDate, :UpdateDate, :OemId)";
        $prm = array(':MailAddress' => $mailAddress, ':AutoLoginKey' => $autoLoginKey, ':BrowserInfo' => $_SERVER['HTTP_USER_AGENT'], ':RegistDate' => date('Y-m-d H:i:s'), ':UpdateDate' => date('Y-m-d H:i:s'), ':OemId' => 0);
        $this->app->dbAdapter->query($sql)->execute($prm);

        // クッキーに情報をセット
        setcookie($this->cookieName, $autoLoginKey, $cookieExpire, $cookiePath, '', 1);

        return $this;
    }

    /**
     * 自動ログイン情報の削除
     */
    public function deleteAutoLogin($autoLoginKey = '')
    {
        // 該当データ削除
        $sql = "DELETE FROM T_MypageAutoLogin WHERE AutoLoginKey = :AutoLoginKey";
        $this->app->dbAdapter->query($sql)->execute(array(':AutoLoginKey' => $autoLoginKey));

        $cookieExpire = time() - 1800;  // 削除する場合は現在のタイムスタンプから減算する。 → 有効期間が「今日」以前になる。
        $cookiePath = "/";

        // クッキー情報を削除
        setcookie($this->cookieName, "", $cookieExpire, $cookiePath, '', 1);

        return $this;
    }


    /**
     * ログアウトアクション。無条件にログアウトし、ログインフォームへリダイレクト
     *
    */
    public function logoutAction() {
        // 自動ログイン時の情報は削除
        if (! empty($_COOKIE[$this->cookieName])) {
            $this->deleteAutoLogin($_COOKIE[$this->cookieName]);
        }

        $this->authManager->logout();

        // セッションIDを振りなおす
        session_regenerate_id(true);

        // セッション全体を破棄
        session_destroy();

        $this->_redirect( 'login/login' );
    }

    /**
     * パスワード再発行画面
     */
    public function reissueAction() {
        $this->setPageTitle( 'パスワード再発行' );
        $params = $this->getParams();

        if (isset($params['form'])) {
            $this->view->assign( 'formdata', $params['form'] );
        }
        return $this->view;
    }

    /**
     * パスワード再発行確認画面
     */
    public function reissueconfirmAction() {
        $this->setPageTitle( 'パスワード再発行' );
        $params = $this->getParams();
        $formdata = $params['form'];

        // 検証
        $errors = array();
        if (empty($formdata['MailAddress'])) {
            $errors[] = 'メールアドレスは、必ず入力してください。';
        }
        if (empty($formdata['NameSeiKj']) || empty($formdata['NameMeiKj'])) {
            $errors[] = '名前は、必ず入力してください。';
        }
        if (empty($formdata['Phone_1']) || empty($formdata['Phone_2']) || empty($formdata['Phone_3'])) {
            $errors[] = '電話番号は、必ず入力してください。';
        }
        if (empty($errors)) {
            // 入力が全てある場合は、会員情報確認
            $regPhone = LogicNormalizer::create(LogicNormalizer::FILTER_FOR_TEL)->normalize($formdata['Phone_1'] . $formdata['Phone_2'] . $formdata['Phone_3']);
            $sql = " SELECT CustomerId FROM T_MypageCustomer WHERE OemId = :OemId AND MailAddress = :MailAddress AND NameSeiKj = :NameSeiKj AND NameMeiKj = :NameMeiKj AND (RegPhone = :Phone OR RegMobilePhone = :Phone) ";
            $prm = array(
                    ':OemId' => 0,
                    ':MailAddress' => $formdata['MailAddress'],
                    ':NameSeiKj' => $formdata['NameSeiKj'],
                    ':NameMeiKj' => $formdata['NameMeiKj'],
                    ':Phone' => $regPhone,
            );
            $row = $this->app->dbAdapter->query($sql)->execute($prm)->current();
            if (!$row) {
                $errors[] = '会員情報が確認できませんでした。';
            }
        }

        // 問題がある場合はエラー情報を積み自身へ遷移
        if (!empty($errors)) {
            $this->view->assign( 'errors', $errors );
            $this->view->assign( 'formdata', $formdata );
            $this->setTemplate( 'reissue' );
            return $this->view;
        }

        $this->view->assign( 'customerid', $row['CustomerId'] );
        $this->view->assign( 'formdata', $formdata );
        return $this->view;
    }

    /**
     * パスワード再発行完了画面
     */
    public function reissuecompleteAction() {
        $this->setPageTitle( 'パスワード再発行' );
        $params = $this->getParams();

        $sql = " SELECT * FROM T_MypageCustomer WHERE CustomerId = :CustomerId ";
        $row = $this->app->dbAdapter->query($sql)->execute(array(':CustomerId' => $params['customerid']))->current();

        // アクセス用URLの生成
        $retryNum = 5;
        $mdlmc = new \models\Table\TableMypageCustomer($this->app->dbAdapter);
        $accessKey = null;
        for ( $i = 0; $i < $retryNum; $i++ ) {
            $accessKey = BaseGeneralUtils::makeRandStr(50);  // 50文字
            // すでに存在するかチェック
            $cnt = $mdlmc->countByAccessKey($accessKey);
            if ($cnt == 0 ) break; // 重複していなければ確定
            if ($i >= $retryNum - 1) throw new \Exception('パスワード再発行に失敗しました'); // 指定回数試行してもNGだった場合はエラー
        }

        // マイページ顧客更新(アクセス用URLキー及び有効期限)
        $mdlmc->saveUpdate(array('AccessKey' => $accessKey, 'AccessKeyValidToDate' => date('Y-m-d H:i:s', strtotime('+1 day'))), $params['customerid']);

        // マイページパスワード再発行メールの送信
        $baseUrl = $this->app->dbAdapter->query(" SELECT KeyContent FROM MV_Code WHERE CodeId = 107 AND KeyCode = :OemId ")->execute(array(':OemId' => 0))->current()['KeyContent'];
        $lcmail = new LogicMail($this->app->dbAdapter, $this->app->smtpServer);
        $lcmail->SendPasswordResetMail(array('OemId' => 0, 'MailAddress' => $row['MailAddress'], 'UrlParameter' => $accessKey), $baseUrl);

        // よくある質問リンク
        $linkfaq = $this->app->dbAdapter->query(" SELECT KeyContent FROM MV_Code WHERE CodeId = 103 AND KeyCode = :OemId ")->execute(array(':OemId' => 0))->current()['KeyContent'];
        $this->view->assign( 'linkfaq', $linkfaq );
        $this->view->assign( 'MailAddress', $row['MailAddress'] );
        return $this->view;
    }

    /**
     * パスワード入力画面
     */
    public function resetAction() {
        $this->setPageTitle( 'パスワード再設定' );
        $params = $this->getParams();

        if (!isset($params['accessid'])) {
            $this->_redirect( 'login/expired' );
        }
        if (strlen($params['accessid']) != 50) {
            $this->_redirect( 'login/expired' );
        }
        $sql = " SELECT CustomerId, IFNULL(AccessKeyValidToDate, '1970-01-01') AS AccessKeyValidToDate FROM T_MypageCustomer WHERE AccessKey = :AccessKey ";
        $row = $this->app->dbAdapter->query($sql)->execute(array(':AccessKey' => $params['accessid']))->current();
        if (!$row) {
            $this->_redirect( 'login/expired' );
        }
        if (strtotime(date( 'Y-m-d H:i:s' )) > strtotime($row['AccessKeyValidToDate'])) {
            $this->_redirect( 'login/expired' );
        }

        // パスワード文字数(桁)の取得
        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        $passwdCount = $mdlsp->getValue( '[DEFAULT]', 'systeminfo', 'PasswdCount' );

        $this->view->assign( 'customerid', $row['CustomerId'] );
        $this->view->assign( 'passwdCount', $passwdCount );
        return $this->view;
    }

    /**
     * パスワード入力完了画面
     */
    public function resetcompleteAction() {
        $this->setPageTitle( 'パスワード再設定' );
        $params = $this->getParams();
        $formdata = $params['form'];

        $sql = " SELECT * FROM T_MypageCustomer WHERE CustomerId = :CustomerId ";
        $row = $this->app->dbAdapter->query($sql)->execute(array(':CustomerId' => $params['customerid']))->current();

        $authUtil = $this->app->getAuthUtility();
        $newPassHashed = $authUtil->generatePasswordHash( $row['LoginId'], $formdata['pwd'] );

        // パスワード文字数(桁)の取得
        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        $passwdCount = $mdlsp->getValue( '[DEFAULT]', 'systeminfo', 'PasswdCount' );
        // 過去パスワード使用回数(回)の取得
        $usePasswdTimes = $mdlsp->getValue( '[DEFAULT]', 'systeminfo', 'UsePasswdTimes' );
        // パスワード期限切れ日数(日)の取得
        $PasswdLimitDay = $mdlsp->getValue( '[DEFAULT]', 'systeminfo', 'PasswdLimitDay' );
        //パスワード文字数
        $pass_len = strlen($formdata['pwd']);
        //過去4回分のパスワードを取得
        $sql = " SELECT LoginPasswd FROM T_MypagePasswordHistory WHERE Category = 5 AND LoginId = :LoginId ORDER BY Seq DESC LIMIT " . $usePasswdTimes;
        $LastPass = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(array(':LoginId' => $row['LoginId'])));

        // 検証
        $errors = array();
        if (empty($formdata['pwd'])) {
            $errors[] = '新しいパスワードが未入力です。';
        }
        if (empty($formdata['pwd2'])) {
            $errors[] = '新しいパスワード（確認）が未入力です。';
        }
        if ($formdata['pwd'] != $formdata['pwd2']) {
            $errors[] = '新しいパスワードと新しいパスワード（確認）が一致しません。';
        }

        if (empty($errors) && (!preg_match('/[a-z]/', $formdata['pwd']) || !preg_match('/[A-Z]/', $formdata['pwd']) || !preg_match('/[0-9]/', $formdata['pwd'])
        || !preg_match('/[ -\/:-@\[-`\{-\~]/', $formdata['pwd']) || $pass_len < $passwdCount))
        {
            $errors[] = 'パスワードは英大文字、英小文字、数字、記号のすべてを含む' . $passwdCount . '文字以上でご入力ください。';
        }
        foreach ($LastPass as $pass)
        {
            if ($newPassHashed == $pass['LoginPasswd'])
            {
                $errors[] = '過去' .$usePasswdTimes. '回に使用したパスワードは利用できません。';
            }
        }

        // 問題がある場合はエラー情報を積み自身へ遷移
        if (!empty($errors)) {
            $this->view->assign( 'errors', $errors );
            $this->view->assign( 'formdata', $formdata );
            $this->view->assign( 'customerid', $params['customerid'] );
            $this->view->assign( 'passwdCount', $passwdCount );
            $this->setTemplate( 'reset' );
            return $this->view;
        }

        // ハッシュ済みのログインパスワードを設定
        $saveData = array(
                'LoginPasswd' => $authUtil->generatePasswordHash( $row['LoginId'], $formdata['pwd'] ),
                'Hashed' => 1,
        );

        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // パスワード再発行履歴
            $reserve = array();
            if (nvl($row['Reserve'],'') != '') {
                $reserve = Json::decode($row['Reserve'], Json::TYPE_ARRAY);
            }
            $reserve[] = array('date' => date('Y-m-d H:i:s'), 'reason' => '再発行完了');
            $saveData['Reserve'] = Json::encode($reserve);

            // アクセス用URLキー有効期限更新
            $saveData['AccessKeyValidToDate'] = date('Y-m-d H:i:s');

            // パスワード更新処理
            $mypageCustomer = new \models\Table\TableMypageCustomer($this->app->dbAdapter);
            $mypageCustomer->saveUpdate( $saveData, $params['customerid'] );

            // パスワード履歴テーブルに１件追加
            $historyData = array(
                     'Category'       => 5
                    ,'LoginId'        => $row['LoginId']
                    ,'LoginPasswd'    => $saveData['LoginPasswd']
                    ,'PasswdStartDay' => date('Y-m-d')
                    ,'PasswdLimitDay' => date('Y-m-d', strtotime("$PasswdLimitDay day"))
                    ,'Hashed'         => 1
                    ,'ValidFlg'       => 1
            );
            $mdlmph = new TableMypagePasswordHistory($this->app->dbAdapter);
            $mdlmph->saveNew($historyData);

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch( \Exception $err ) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollback();
            throw $err;
        }

        return $this->view;
    }

    /**
     * 有効期限切れページ
     */
    public function expiredAction() {
        $this->setPageTitle( 'パスワード再設定' );
        return $this->view;
    }
}
