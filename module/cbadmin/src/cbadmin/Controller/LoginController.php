<?php
namespace cbadmin\Controller;

use Coral\Base\Auth\BaseAuthManager;
use Coral\Coral\Controller\CoralControllerAction;
use cbadmin\Application;
use Zend\Db\Adapter\Adapter;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Coral\Coral\Auth\Exception\CoralAuthExceptionClientLocked;
use Coral\Coral\Auth\Exception\CoralAuthExceptionIdLocked;
use models\Logic\AccountValidity\LogicAccountValidityException;
use models\Table\TableOperator;
use models\Table\TableSystemProperty;
use models\Table\TablePasswordHistory;
use models\Logic\AccountValidity\LogicAccountValidityPasswordValidator;

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
	private $_rowOperator;

	/**
	 * 初期化処理
	 */
	protected function _init()
	{
	    $this->app = Application::getInstance();
		$this->app->run();

		$this->addStyleSheet('../css/default02.css')
			->addJavaScript('../js/prototype.js');

		$this->setPageTitle("後払い.com - ログイン");

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
        $id = $this->getRequest()->getPost('op_loginid', '');
        $psw = $this->getRequest()->getPost('op_passwd', '');

        $mdlo = new \models\Table\TableOperator($this->app->dbAdapter);
        $this->_rowOperator = $mdlo->findLoginId(explode(':', $id)[0])->current();   // オペレーター情報の取得

        $clientInfo = sprintf('login_id = %s, password = *****, remote_host = %s, remote_addr = %s',
                              $id,
                              gethostbyaddr(f_get_client_address())/* $_SERVER['REMOTE_HOST'] */,      // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
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
            if ($this->_rowOperator && !$obj->isNotNgAccessOperator($this->_rowOperator)) {
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
            $this->view->assign('errMessage', $clAuthError->getMessage());
            $this->setTemplate('autherror');
            return $this->view;
        }
        catch(CoralAuthExceptionIdLocked $idAuthError)
        {
            $app->logger->crit('authentication locked (id-level)');
            sleep(3);	// 3秒sleep
            $this->view->assign('errMessage', $idAuthError->getMessage());
            $this->setTemplate('autherror');
            return $this->view;
        }
        catch(LogicAccountValidityException $validityError)
        {
            $app->logger->crit('account is already expired');
            sleep(3);	// 3秒sleep
            $this->view->assign('errMessage', $validityError->getMessage());
            $this->setTemplate('autherror');
            return $this->view;
        }

if (false) {
        if( empty($id) || empty($psw) || ! $this->authManager->login($id, $psw)->isValid())
        {
            $app->logger->crit(sprintf('authentication ERROR: %s', $clientInfo));
            $this->messenger->addMessage( 'IDとパスワードが一致しません' );
            return $this->_redirect('login/login');
        }
}
        // 認証成功時はNOTICEでログ出力（09.07.17 eda）
        $userInfo = $this->authManager->getUserInfo();
        $altUserInfo = $this->authManager->getAlternativeUserInfo();
        $clientInfo = sprintf('login_id = %s, password = *****, remote_host = %s, remote_addr = %s',
                              $altUserInfo ? sprintf('%s (as %s)', $altUserInfo->LoginId, $userInfo->LoginId) : $userInfo->LoginId,
                              gethostbyaddr(f_get_client_address())/* $_SERVER['REMOTE_HOST'] */,   // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
                              f_get_client_address()        // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
                              );
        $app->logger->notice(sprintf('authenticatated: %s', $clientInfo));

        // 認証成功後のリダイレクトURL
        $target = 'index/index';

        // アプリケーションクラスへのユーザーIDセット
        $obj = new \models\Table\TableUser($app->dbAdapter);
        $userId = $obj->getUserId(0, $app->authManagerAdmin->getUserInfo()->OpId);
        // 最終ログイン日時保存
        $data['LastLoginDate'] = date('Y-m-d H:i:s');
        $data['UpdateId'] = $userId;
        $obj->saveUpdate($data, $userId);

        // ======================= パスワード期限 間近チェック =======================
        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        $mdlph = new TablePasswordHistory($this->app->dbAdapter);

        $PassHist = ResultInterfaceToArray($mdlph->findnew(1, $userInfo->LoginId));

        $passAlert = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdLimitAlertDay');
		// count関数対策
        if(!empty($PassHist) && $PassHist[0]['PasswdLimitDay'] >= date('Y-m-d') && $PassHist[0]['PasswdLimitDay'] <= date('Y-m-d', strtotime($passAlert ." day"))){
            $target = 'login/chgpw';
        }
        // ===========================================================================

        // セッションIDを振りなおす
        session_regenerate_id(true);

if (false) {//zzz 認証プラグイン系どうすればよい？(20150618_1750)
        // 認証プラグインで設定されたセッション情報を復元
        $requestInfo = $this->app->authPlugin->getPreAuthRequest();

        // セッションIDを振りなおす
        session_regenerate_id(true);

        if($requestInfo)
        {
            // セッション情報にパラメータが存在しているので、Redirectorヘルパーでリダイレクト
            $this->_helper->Redirector->goto($requestInfo['action'],$requestInfo['controller'],null,$this->stripControllerKeys( $requestInfo ));
        }
        else
        {
            // 通常のリダイレクト
            return $this->_redirect($target);
        }
}
        return $this->_redirect($target);
	}

	/**
	 * 認証エラーメッセージをセットしてログインフォームにリダイレクトする
	 *
	 * @access protected
	 * @param string $clientInfo ログ出力向けのクライアント情報
	 */
	protected function authenticationFailed($clientInfo) {
        // 不正アクセスIP関連処理
        $obj = new \models\Logic\LogicNgAccessIp(Application::getInstance()->dbAdapter);
        $obj->registNgAccess($_SERVER['NGCHECK_REMOTE_ADDR'], $_SERVER['NGCHECK_FILE_PATH']);
        if ($this->_rowOperator) {
            // ログイン認証失敗によるオペレーターテーブルの更新
            $obj->updateOperatorNgAccess($this->_rowOperator);
        }

        Application::getInstance()->logger->crit(sprintf('authentication ERROR: %s', $clientInfo));
        sleep(3);	// 3秒sleep
        $this->messenger->addMessage( 'IDとパスワードが一致しません' );
        return $this->_redirect('login/login');
	}

	/**
	 * ログアウトアクション。無条件にログアウトし、ログインフォームへリダイレクト
	 *
	 */
	public function logoutAction()
	{
        // 不払いキャッシュをクリア（08.8.14 eda）
        \cbadmin\classes\SearchfCache::clearInstanceFromStorage();

        $this->authManager->logout();

        // セッションIDを振りなおす
        session_regenerate_id(true);

        // 念のためセッション全体を破棄
        session_destroy();

        return $this->_redirect('login/login');
	}

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
	    ->addJavaScript( '../js/prototype.js' )
	    ->addJavaScript( '../js/corelib.js' );

	    $authUtil = $this->app->getAuthUtility();

	    $mdlsp = new TableSystemProperty($this->app->dbAdapter);
	    $mdlph = new TablePasswordHistory($this->app->dbAdapter);

	    //$PassHistCnt = $mdlph->findnew(1, $userInfo->LoginId);
	    $PassHist = $mdlph->findnew(1, $userInfo->LoginId)->current();

	    $passCnt = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdCount');
	    $passAlert = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdLimitAlertDay');
	    $sysTimeLimit = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdLimitDay');
	    $loginmode = 0;
		// count関数対策
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

	    if ($cmd == 'e') {
	        $userInfo = $this->app->authManagerAdmin->getUserInfo();

	        $ar = $this->getRequest()->getPost()->toArray();

	        // パスワード変更実行
	        $oldPass = $ar['opw'];
	        $newPass1 = $ar['npw1'];
	        $newPass2 = $ar['npw2'];

	        $authUtil->setHashDisabled($userInfo->Hashed ? false : true);// 古いパスワードの突合用にハッシュ利用状況を設定
	        $oldPassHashed = $authUtil->generatePasswordHash($userInfo->LoginId, $oldPass);
	        $newPass1Hashed = $authUtil->generatePasswordHash($userInfo->LoginId, $newPass1);

	        //パスワード文字数
	        $pass_len = strlen($newPass1);
	        //過去パスワードの使用不可回数
	        $passTimeLimit = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'UsePasswdTimes');
	        //過去4回分のパスワードを取得
	        $sql = " SELECT LoginPasswd FROM T_PasswordHistory WHERE Category = 1 AND LoginId = :LoginId ORDER BY Seq DESC LIMIT " . $passTimeLimit;
	        $LastPass = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(array(':LoginId' => $userInfo->LoginId)));

	        $mdlop = new TableOperator($this->app->dbAdapter);

	        if (!$mdlop->isExists($userInfo->LoginId, $oldPassHashed))
	        {
	            $error['eopw'] = '<font color="red">現在のパスワードが一致しません。</font>';
	        }
	        if ($oldPass == $newPass1)
	        {
	            $error['enpw1'] = '<font color="red">パスワードを変更してください。</font>';
	        }

	        if( empty( $newPass1) )
	        {
	            $error['enpw1'] = '<font color="red">新しいパスワードを空にすることはできません。</font>';
	        }

	        if ($newPass1 != $newPass2)
	        {
	            $error['enpw2'] = '<font color="red">パスワードが一致しません。</font>';
	        }

	        if (!preg_match('/[a-z]/', $newPass1) || !preg_match('/[A-Z]/', $newPass1) || !preg_match('/[0-9]/', $newPass1)
	        || !preg_match('/[ -\/:-@\[-`\{-\~]/', $newPass1) || $pass_len < $passCnt)
	        {
	            $error['enpw1'] = '<font color="red">パスワードは英大文字、英小文字、数字、記号のすべてを含む' .$passCnt. '文字以上でご入力ください。</font>';
	        }

	        foreach ($LastPass as $pass)
	        {
	            if ($newPass1Hashed == $pass['LoginPasswd'])
	            {
	                $error['enpw1'] = '<font color="red">過去' .$passTimeLimit. '回に使用したパスワードは利用できません。</font>';
	            }
	        }

	        if (isset($error))
	        {
	            if (!array_key_exists('eopw' , $error)) { $error["eopw"]  = ""; }
	            if (!array_key_exists('enpw1', $error)) { $error["enpw1"] = ""; }
	            if (!array_key_exists('enpw2', $error)) { $error["enpw2"] = ""; }

	            $error['opw'] = $oldPass;
	            $error['npw1'] = $newPass1;
	            $error['npw2'] = $newPass2;

	            $this->view->assign('error', $error);
	            $this->setTemplate('chgpw_f');
	            return $this->view;
	        }
	        else
	        {
	            // ユーザーIDの取得
	            $obj = new \models\Table\TableUser($this->app->dbAdapter);
	            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

	            $updateInfo = array(
	                    'LoginPasswd' => $newPass1Hashed,
	                    'Hashed' => 1,
	                    'LastPasswordChanged' => date('Y-m-d H:i:s'),
	                    'UpdateId' => $userId,
	            );
	            $mdlop->saveUpdate($updateInfo, $userInfo->OpId);

	            $passChgData = array(
	                    'Category' => 1,
	                    'LoginId' => $userInfo->LoginId,
	                    'LoginPasswd' => $newPass1Hashed,
	                    'PasswdStartDay' => date('Y-m-d'),
	                    'PasswdLimitDay' => date('Y-m-d', strtotime("+$sysTimeLimit days")),
	                    'Hashed' => 1,
	                    'RegistDate' => date('Y-m-d H:i:s'),
	                    'RegistId' => $userId,
	                    'UpdateDate' => date('Y-m-d H:i:s'),
	                    'UpdateId' => $userId,
	                    'ValidFlg' => 1);

	            //パスワード履歴テーブルに1件追加する
	            $mdlph->saveNew($passChgData);

	            // ログイン状態を更新
	            $this->app->authManagerAdmin->resetLoginState();
	            $this->setTemplate('chgpw_e');
	            return $this->view;
	        }
	    }
	    else {
	        // パスワード変更フォーム表示
	        $this->setTemplate('chgpw_f');
	        return $this->view;
	    }
	}
}
