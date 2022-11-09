<?php
namespace oemadmin\Controller;

use oemadmin\Application;
use models\Table\TablePasswordHistory;
use models\Table\TableSystemProperty;
use models\Table\TableOemOperator;
use Coral\Base\Auth\BaseAuthManager;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\Auth\Exception\CoralAuthExceptionClientLocked;
use Coral\Coral\Auth\Exception\CoralAuthExceptionIdLocked;
use Zend\Db\Adapter\Adapter;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use models\Logic\AccountValidity\LogicAccountValidityException;

class LoginController extends CoralControllerAction
{
    protected $_componentRoot = './application/views/components';

    /**
     * アプリケーションオブジェクト
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
     * @var array
     */
    private $_rowOemOperator;

    /**
     * 初期化処理
     */
    protected function _init()
    {
        $this->app = Application::getInstance();
//        $this->app->run();

        $cssDir = 'public/oemadmin/css/vender/'.$this->app->getOemAccessId().'/';
        if(is_dir($cssDir)) {
            $cssPath = '../../oemadmin/css/vender/'.$this->app->getOemAccessId().'/login.css';
        } else {
            $cssPath = '../../oemadmin/css/vender/default/login.css';
        }

        $this->addStyleSheet($cssPath)
            ->addJavaScript('../../js/prototype.js');

        $this->setPageTitle($this->app->getOemServiceName()." - ログイン");

        // 認証マネージャとFlashMessengerをメンバに設定
        $this->authManager = Application::getInstance()->authManagerAdmin;
        $this->messenger = $this->flashMessenger();
    }

    /**
     * ログインアクション。ログインフォームを表示する
     */
    public function loginAction()
    {
        if($this->authManager->isAuthenticated())
        {
            // 認証済みの場合はデフォルトコントローラへリダイレクト
            return $this->_redirect('index/index');
        }
        $this->view->assign('oemInfo', $this->app->getCurrentOemData());
        $this->view->assign('systemMessages', $this->messenger->getMessages());

        return $this->view;
    }

    /**
     * 認証アクション
     */
    public function authAction()
    {
        $app = Application::getInstance();

        if ($this->authManager->isAuthenticated())
        {
            // 認証済みの場合はデフォルトコントローラへリダイレクト
            return $this->_redirect('index/index');
        }

        // 認証処理
        $id = $this->params()->fromPost('op_loginid', '');
        $psw = $this->params()->fromPost('op_passwd', '');

        $agencyflg = 0;

        // 代理ログイン パスワード期限チェック
        if(preg_match('/^([^:]+)(::?)(.+)$/', $id, $userIdInfo)) {
            $agencyflg = 1;
            // 代理認証
            $altAuthKey = $userIdInfo[2] == '::' ? 1 : 4;
            $altLoginId = $userIdInfo[3];

            $mdlph = new TablePasswordHistory(Application::getInstance()->dbAdapter);
            $PassHistCnt = $mdlph->findnew($altAuthKey, $altLoginId);
            $PassHist = $mdlph->findnew($altAuthKey, $altLoginId)->current();

            if ($PassHistCnt > 0 && $PassHist['PasswdLimitDay'] < date('Y-m-d')) {
                $this->messenger->addMessage( '代理ログインユーザーのパスワード有効期限切れです。CB管理画面でパスワードを変更してください。' );
                return $this->_redirect('login/login');
            }
        }
        $mdloo = new \models\Table\TableOemOperator($this->app->dbAdapter);
        $this->_rowOemOperator = $mdloo->findLoginId($this->app->getCurrentOemData()['OemId'], explode(':', $id)[0])->current();   // OEMオペレーター情報の取得

        $clientInfo = sprintf('login_id = %s, password = *****, remote_host = %s, remote_addr = %s',
                              $id,
                              gethostbyaddr(f_get_client_address())/* $_SERVER['REMOTE_HOST'] */,       // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
                              f_get_client_address()        // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
                             );

        // 不正アクセスIP判定処理
        $obj = new \models\Logic\LogicNgAccessIp(Application::getInstance()->dbAdapter);
        $result = $obj->isNgAccess($_SERVER['NGCHECK_REMOTE_ADDR'], $_SERVER['NGCHECK_FILE_PATH']);
        if($result){
            return $this->_redirect("login/login");
        }

        if(empty($id) || empty($psw))
        {
            $this->authenticationFailed($clientInfo);
            return;
        }
        try
        {
            if(!$this->authManager->login($id, $psw)->isValid())
            {
                $this->authenticationFailed($clientInfo);
                return;
            }
            // 不正アクセスIP関連処理
            $obj = new \models\Logic\LogicNgAccessIp($this->app->dbAdapter);
            $obj->resetNgAccess($_SERVER['NGCHECK_REMOTE_ADDR']);

            // 認証成功も連続不正アクセスと判断される時の処理
            if ($this->_rowOemOperator && !$obj->isNotNgAccessOemOperator($this->_rowOemOperator)) {
                $this->authManager->logout();
                session_regenerate_id(true);
                session_destroy();
                sleep(3);	// 3秒sleep
                $this->view->assign('errMessage', '');
                $this->view->assign('isAccountLock', true);
                $this->setTemplate('autherror');
                return $this->view;
            }
        }
        catch(CoralAuthExceptionClientLocked $clAuthError)
        {
            $app->logger->crit('authentication locked (client-level)');
            sleep(3);	// 3秒sleep
            $this->view->assign('oemInfo', $this->app->getCurrentOemData());
            $this->view->assign('errMessage', $clAuthError->getMessage());
            $this->setTemplate('autherror');
            return $this->view;
        }
        catch(CoralAuthExceptionIdLocked $idAuthError)
        {
            $app->logger->crit('authentication locked (id-level)');
            sleep(3);	// 3秒sleep
            $this->view->assign('oemInfo', $this->app->getCurrentOemData());
            $this->view->assign('errMessage', $idAuthError->getMessage());
            $this->setTemplate('autherror');
            return $this->view;
        }
        catch(LogicAccountValidityException $validityError)
        {
            $app->logger->crit('account is already expired');
            sleep(3);	// 3秒sleep
            $this->view->assign('oemInfo', $this->app->getCurrentOemData());
            $this->view->assign('errMessage', $validityError->getMessage());
            $this->setTemplate('autherror');
            return $this->view;
        }
        //if( empty($id) || empty($psw) || ! $this->authManager->login($id, $psw)->isValid())
        //{
        //	$app->logger->crit(sprintf('authentication ERROR: %s', $clientInfo));
        //	$this->messenger->addMessage( 'IDとパスワードが一致しません' );
        //		$this->_redirect('login/login');
        //		return;
        //}

        // 認証成功時はNOTICEでログ出力
        $userInfo = $this->authManager->getUserInfo();
        $altUserInfo = $this->authManager->getAlternativeUserInfo();
        $clientInfo = sprintf('login_id = %s, password = *****, remote_host = %s, remote_addr = %s',
                              $altUserInfo ? sprintf('%s (as %s)', $altUserInfo->LoginId, $userInfo->LoginId) : $userInfo->LoginId,
                              gethostbyaddr(f_get_client_address())/* $_SERVER['REMOTE_HOST'] */,       // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
                              f_get_client_address()        // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
                             );
        $app->logger->notice(sprintf('authenticatated: %s', $clientInfo));

        // 認証成功後のリダイレクトURL
        $target = 'index/index';

        // アプリケーションクラスへのユーザーIDセット
        $obj = new \models\Table\TableUser($app->dbAdapter);
        $altUserInfo = $app->authManagerAdmin->getAlternativeUserInfo();
        if (!is_null($altUserInfo)) {
            // 代理認証の場合
            $userClass = 0;
            $seq = $altUserInfo->OpId;
        } else {
            // 通常認証の場合
            $userClass = 1;
            $seq = $app->authManagerAdmin->getUserInfo()->OemOpId;
        }
        $userId = $obj->getUserId($userClass, $seq);
        $app->authManagerAdmin->getUserInfo()->UserId = $userId;

        // 最終ログイン日時保存
        $data['LastLoginDate'] = date('Y-m-d H:i:s');
        $data['UpdateId'] = $userId;
        $obj->saveUpdate($data, $userId);

        // ======================= パスワード期限 チェック =======================
        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        $mdlph = new TablePasswordHistory($this->app->dbAdapter);

        // 代理ログイン時は以下の処理を行わない
        if ($agencyflg == 0)
        {
            // 最新の有効なパスワード履歴データ
            $PassHist = ResultInterfaceToArray($mdlph->findnew(1, $userInfo->LoginId));
            // パスワード期限警告日数
            $passAlert = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdLimitAlertDay');

            // パスワード有効期限間近チェック
            if(!empty($PassHist) && $PassHist[0]['PasswdLimitDay'] >= date('Y-m-d') && $PassHist[0]['PasswdLimitDay'] <= date('Y-m-d', strtotime($passAlert ." day"))){
                $target = 'operator/changepw';
            }

            // パスワード有効期限超過チェック
            if ($PassHist[0]['PasswdLimitDay'] <= date('Y-m-d'))
            {
                $target = 'operator/changepw';
            }
        }
        // ===========================================================================

if (false) {
        // 認証プラグインで設定されたセッション情報を復元
        $requestInfo = $this->app->authPlugin->getPreAuthRequest();

        // セッションIDを振りなおす
        session_regenerate_id(true);

        if($requestInfo)
        {
            // セッション情報にパラメータが存在しているので、Redirectorヘルパーでリダイレクト
            $this->_helper->Redirector->goto(
                $requestInfo['action'],
                $requestInfo['controller'],
                null,
                $this->stripControllerKeys( $requestInfo )
            );
        }
        else
        {
            // 通常のリダイレクト
            $this->_redirect($target);
        }
}
        $this->_redirect($target);
    }

    /**
     * 認証エラーメッセージをセットしてログインフォームにリダイレクトする
     *
     * @access protected
     * @param string $clientInfo ログ出力向けのクライアント情報
     */
    protected function authenticationFailed($clientInfo)
    {
        // 不正アクセスIP関連処理
        $obj = new \models\Logic\LogicNgAccessIp(Application::getInstance()->dbAdapter);
        $obj->registNgAccess($_SERVER['NGCHECK_REMOTE_ADDR'], $_SERVER['NGCHECK_FILE_PATH']);
        if ($this->_rowOemOperator) {
            // ログイン認証失敗によるOEMオペレーターテーブルの更新
            $obj->updateOemOperatorNgAccess($this->_rowOemOperator);
        }

        Application::getInstance()->logger->crit(sprintf('authentication ERROR: %s', $clientInfo));
        sleep(3);	// 3秒sleep
        $this->messenger->addMessage( 'IDとパスワードが一致しません' );
        $this->_redirect('login/login');
    }

    /**
     * ログアウトアクション。無条件にログアウトし、ログインフォームへリダイレクト
     *
     */
    public function logoutAction()
    {
        // 不払いキャッシュをクリア（08.8.14 eda）
        //require_once 'SearchfCache.php';
        //SearchfCache::clearInstanceFromStorage();

        $this->authManager->logout();

        // セッションIDを振りなおす
        session_regenerate_id(true);

        // セッション全体を破棄
        session_destroy();

        $this->_redirect('login/login');
    }

//    /**
//     * __callマジックメソッド。ログインフォームへフォワード
//     */
//    public function __call($method, $args)
//    {
//        return $this->_forward('login', 'login');
//    }

    /**
     * 指定の連想配列からモジュール名キー、コントローラ名キー、アクション名キーに
     * 一致するキーと値を除外する
     *
     * @param array $params 処理対象の連想配列
     * @return array $paramsからモジュール名・コントローラ名・アクション名のキーを除外した連想配列
     */
    private function stripControllerKeys(array $params) {
        $keys = array(
            $this->getModuleName(),
            $this->getControllerName(),
            $this->getActionName()
        );
        $result = array();
        foreach( $params as $key => $value ) {
            if( in_array( $key, $keys ) ) continue;
            $result[ $key ] = $value;
        }

        return $result;
    }

    /**
     * パスワード変更
     */
    public function chgpwAction()
    {
        unset($error);
        $userInfo = $this->app->authManagerAdmin->getUserInfo();

        $this->view->assign('userInfo', $userInfo);

        $cmd = $this->params()->fromRoute('cmd', 'none');

        $this
        ->addJavaScript( '../../js/prototype.js' )
        ->addJavaScript( '../../js/corelib.js' );

        $authUtil = $this->app->getAuthUtility();

        $mdlph = new TablePasswordHistory($this->app->dbAdapter);
        $mdlsp = new TableSystemProperty($this->app->dbAdapter);

//         $PassHistCnt = $mdlph->findnew(1, $userInfo->LoginId);
        $PassHist = $mdlph->findnew(1, $userInfo->LoginId)->current();

        //パスワード最小桁数
        $passCnt = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdCount');
        //パスワード期限切れ日数
        $sysTimeLimit = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdLimitDay');
        // パスワード期限警告日数
        $passAlert = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdLimitAlertDay');
        // パスワード有効期限取得
        $sql = "select PasswdLimitDay from T_PasswordHistory WHERE Category = '4' AND LoginId = :LoginId ORDER BY Seq DESC  LIMIT 1";
        $passLimit = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(array(':LoginId' => $userInfo->LoginId)));
        $loginmode = 0;

        if(!empty($PassHist)){
            if ($PassHist['PasswdLimitDay'] >= date('Y-m-d') && $PassHist['PasswdLimitDay'] <= date('Y-m-d', strtotime($passAlert ." day")) ) {
                $msg = 'パスワードの有効期限が近づいています。新しいパスワードを設定してください。';
                $loginmode = 2;
            } elseif ($PassHist['PasswdLimitDay'] < date('Y-m-d')) {
                $msg = 'パスワードの有効期限が切れています。新しいパスワードを設定してください。';
                $loginmode = 3;
            }
        } else {
            $msg = '初回ログインのため、パスワードを変更してください。パスワードは、英数記号混在で' . $passCnt . '文字以上のパスワードを設定してください。';
            $loginmode = 1;
        }

        if($loginmode == 0) {
            return $this->_redirect('index/index');
        }

        $this->view->assign('mode', $loginmode);
        $this->view->assign('msg', $msg);

        if ($cmd == 'e')
        {
            $userInfo = $this->app->authManagerAdmin->getUserInfo();
            $altUserInfo = $this->app->authManagerAdmin->getAlternativeUserInfo();
            $authUtil->setHashDisabled($userInfo->Hashed ? false : true);

            // パスワード変更実行
            $oldPass = $this->params()->fromPost('opw', '');
            $newPass1 = $this->params()->fromPost('npw1', '');
            $newPass2 = $this->params()->fromPost('npw2', '');

            $oldPassHashed = $authUtil->generatePasswordHash($userInfo->LoginId, $oldPass);
            $newPass1Hashed = $authUtil->generatePasswordHash($userInfo->LoginId, $newPass1);

            $mdlop = new TableOemOperator($this->app->dbAdapter);


            //パスワード文字数
            $pass_len = strlen($newPass1);
            //過去パスワードの使用不可回数
            $passTimeLimit = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'UsePasswdTimes');
            //過去規定回数分のパスワードを取得
            $sql  = " SELECT LoginPasswd FROM T_PasswordHistory WHERE Category = :Category AND LoginId = :LoginId ORDER BY Seq DESC LIMIT " . $passTimeLimit;
            $lastPass = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(array(':Category' => 4, ':LoginId' => $userInfo->LoginId)));

            if(!$altUserInfo)
            {
                // 現在パスワードのチェックは非代理ログイン時のみ
                if (!$mdlop->isExists($userInfo->LoginId, $oldPassHashed))
                {
                    $error['eopw'] = '<font color="red">現在のパスワードが一致しません。</font>';
                }
                if($oldPass == $newPass1)
                {
                    $error['enpw1'] = '<font color="red">パスワードを変更してください。</font>';
                }
            }

            if( empty( $newPass1) )
            {
                $error['enpw1'] = '<font color="red">新しいパスワードを空にすることはできません。</font>';
            }

            if ($newPass1 != $newPass2)
            {
                $error['enpw2'] = '<font color="red">パスワードが一致しません。</font>';
            }

            //英大文字、英小文字、数字、記号、桁数のチェック
            if (!preg_match('/[a-z]/', $newPass1) || !preg_match('/[A-Z]/', $newPass1) || !preg_match('/[0-9]/', $newPass1)
            || !preg_match('/[ -\/:-@\[-`\{-\~]/', $newPass1) || $pass_len < $passMin)
            {
                $error['enpw1'] = '<font color="red">パスワードは英大文字、英小文字、数字、記号のすべてを含む' .$passMin. '文字以上でご入力ください。</font>';
            }

            //過去規定回数分のパスワードとの重複チェック
            foreach ($lastPass as $pastPass['LoginPasswd'] => $pass)
            {
                if ($newPass1Hashed == $pass['LoginPasswd'])
                {
                    $error['enpw1'] = '<font color="red">過去' .$passTimeLimit. '回に使用したパスワードは利用できません。</font>';
                }
            }

            // ここまでエラーがなければパスワードルール検証を実施
            if (empty($error))
            {
                $psw_validator = LogicAccountValidityPasswordValidator::getDefaultValidator();
                if (!$psw_validator->isValid($newPass1, $userInfo->LoginId))
                {
                    $npw1_errors = array();
                    foreach ($psw_validator->getLastErrors() as $psw_err)
                    {
                        $npw1_errors[] = sprintf('<div style="color:red">%s</div>', f_e($psw_err));
                    }
                    $error['enpw1'] = join('', $npw1_errors);
                }
            }

            if (isset($error))
            {
                $error['opw'] = $oldPass;
                $error['npw1'] = $newPass1;
                $error['npw2'] = $newPass2;

                $this->view->assign('error', $error);
                $this->setTemplate('chgpw_f2');
                return $this->view;
            }
            else
            {
                $updateInfo = array(
                        'LoginPasswd' => $newPass1Hashed,
                        'Hashed' => 1,
                        'LastPasswordChanged' => date('Y-m-d H:i:s'),
                        'UpdateId' => $this->app->authManagerAdmin->getUserInfo()->UserId
                );
                $mdlop->saveUpdate($updateInfo, $this->app->authManagerAdmin->getUserInfo()->OemOpId);


                $passChgData = array(
                        'Category' => 4,
                        'LoginId' => $userInfo->LoginId,
                        'LoginPasswd' => $newPass1Hashed,
                        'PasswdStartDay' => date('Y-m-d'),
                        'PasswdLimitDay' => date('Y-m-d', strtotime("+$sysTimeLimit days")),
                        'Hashed' => 1,
                        'RegistDate' => date('Y-m-d H:i:s'),
                        'RegistId' => $this->app->authManagerAdmin->getUserInfo()->UserId,
                        'UpdateDate' => date('Y-m-d H:i:s'),
                        'UpdateId' => $this->app->authManagerAdmin->getUserInfo()->UserId,
                        'ValidFlg' => 1);

                // パスワード履歴テーブルに1件追加する
                $mdlph->saveNew($passChgData);

                // ログイン状態を更新
                $this->app->authManagerAdmin->resetLoginState();
                $this->setTemplate('chgpw_e2');
                return $this->view;
            }
        }
        else
        {
            // パスワード変更フォーム表示
            $this->setTemplate('chgpw_f2');
            return $this->view;
        }
    }
}
