<?php
namespace oemmember\Controller;

use oemmember\Application;
use models\Table\TablePasswordHistory;
use models\Table\TableSystemProperty;
use models\Table\TableEnterprise;
use models\Table\TableEnterpriseOperator;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Base\Auth\BaseAuthManager;
use Zend\Mvc\Controller\Plugin\FlashMessenger;

use Coral\Coral\Auth\Exception\CoralAuthExceptionClientLocked;
use Coral\Coral\Auth\Exception\CoralAuthExceptionIdLocked;
use models\Logic\AccountValidity\LogicAccountValidityException;
use models\Table\TableBatchLock;
use models\Table\TableLoginMemberLog;

class LoginController extends CoralControllerAction {
	protected $_componentRoot = './application/views/components';

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
	private $_rowEnterprise;

	/**
	 * バッチID
	 * @var int
	 */
	const EXECUTE_BATCH_ID = 3;

	/**
	 * 初期化処理
	 */
	protected function _init() {
		// ページタイトルとスタイルシート、JavaScriptを設定
		$this->setPageTitle( '後払い決済管理システム' )
			->addStyleSheet( '../../oemmember/css/members.css' )
			->addStyleSheet( '../../oemmember/css/login.css' )
			->addJavaScript( '../../js/prototype.js' )
			->addJavaScript( '../../js/bytefx.js' );

		// 認証マネージャとFlashMessengerをメンバに設定
		$this->authManager = Application::getInstance()->authManager;
		$this->messenger = $this->flashMessenger();

		$this->view->assign( 'cssName', "login" );
	}

	/**
	 * ログインアクション。ログインフォームを表示する
	 */
	public function loginAction() {
		if( $this->authManager->isAuthenticated() ) {
			// 認証済みの場合はデフォルトコントローラへリダイレクト
			return $this->_redirect( 'index/index' );
		}
		$this->view->assign('systemMessages', $this->messenger->getMessages() );
		return $this->view;
	}

	/**
	 * 認証アクション
	 */
	public function authAction() {
		$app = Application::getInstance();

		if( $this->authManager->isAuthenticated() ) {
			// 認証済みの場合はデフォルトコントローラへリダイレクト
			return $this->_redirect( 'index/index' );
		}

		// 認証処理
		// $id = $this->getRequest()->getPost('login_id', '');
		// $psw = $this->getRequest()->getPost('password', '');
		$id = $this->params()->fromPost('login_id', '');
		$psw = $this->params()->fromPost('password', '');

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

		$mdlent = new \models\Table\TableEnterprise(Application::getInstance()->dbAdapter);
		$this->_rowEnterprise = $mdlent->findLoginId(explode(':', $id)[0])->current();   // 加盟店情報の取得

		$clientInfo = sprintf('login_id = %s, password = *****, remote_host = %s, remote_addr = %s',
							  $id,
							  gethostbyaddr(f_get_client_address()), /*$_SERVER['REMOTE_HOST'],*/    // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
							  f_get_client_address()     // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
							 );

		// 不正アクセスIP判定処理
		$obj = new \models\Logic\LogicNgAccessIp(Application::getInstance()->dbAdapter);
		$result = $obj->isNgAccess($_SERVER['NGCHECK_REMOTE_ADDR'], $_SERVER['NGCHECK_FILE_PATH']);
		if($result){
		    return $this->_redirect("login/login");
		}

		if(empty($id) || empty($psw)) {
			$this->authenticationFailed($clientInfo);
			return;
		}

		//パスワード期限間近
		$passwdLimitDayFlg = false;
		//パスワード期限切れ
		$passwdLimitDayExpiredFlg = false;
		//初回ログイン
		$loginFlg = false;

        // 代理ログイン時は以下の処理を行わない
        if ($agencyflg == 0) {
            $mdlsp = new TableSystemProperty($app->dbAdapter);
            $mdlph = new TablePasswordHistory($app->dbAdapter);

            // 最新の有効なパスワード履歴データ
            $PassHist = ResultInterfaceToArray($mdlph->findnew(3, $id));
            // パスワード期限警告日数
            $passAlert = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdLimitAlertDay');
            // バッチ排他制御
            $mdlbl = new TableBatchLock(Application::getInstance ()->dbAdapter);
            $BatchLockData = $mdlbl->findBatchId($this::EXECUTE_BATCH_ID);

            if ($BatchLockData ['BatchLock'] > 0) {
                if (!empty($PassHist)) {
                    if ($PassHist[0]['PasswdLimitDay'] >= date('Y-m-d') && $PassHist[0]['PasswdLimitDay'] <= date('Y-m-d', strtotime($passAlert . " day"))) {
                        $passwdLimitDayFlg = true;
                    } elseif ($PassHist[0]['PasswdLimitDay'] < date('Y-m-d')) {
                        $passwdLimitDayExpiredFlg = true;
                    }
                } else {
                    $loginFlg = true;
                }
            }
        }

		try {
			if(!$this->authManager->login($id, $psw)->isValid()) {
				$this->authenticationFailed($clientInfo);
				return $this->view;
			}
			if($passwdLimitDayExpiredFlg){
			    //ログアウト
			    $this->authManager->logout();
			    $this->messenger->addMessage('パスワードの期限が切れておりますが、現在パスワード変更処理を行うことができません。しばらくたってから再度実行をお願い致します。');
		        return $this->_redirect('login/login');
			}

			if($loginFlg){
			    //ログアウト
			    $this->authManager->logout();
			    $this->messenger->addMessage('初回ログインのためパスワードの変更が必要ですが、現在パスワード変更処理を行うことができません。しばらくたってから再度実行をお願い致します。');
			    return $this->_redirect('login/login');
			}

			// 不正アクセスIP関連処理
			$obj = new \models\Logic\LogicNgAccessIp(Application::getInstance()->dbAdapter);
			$obj->resetNgAccess($_SERVER['NGCHECK_REMOTE_ADDR']);

			// 認証成功も連続不正アクセスと判断される時の処理
			if ($this->_rowEnterprise && !$obj->isNotNgAccessEnterprise($this->_rowEnterprise)) {
			    $this->authManager->logout();
			    session_regenerate_id(true);
			    session_destroy();
			    sleep(3);	// 3秒sleep
			    $this->view->assign('errMessage', '');
			    $this->view->assign('isAccountLock', true);
			    $this->setTemplate('autherror');
			    return $this->view;
			}
		} catch(CoralAuthExceptionClientLocked $clAuthError) {
			$app->logger->crit('authentication locked (client-level)');
			sleep(3);	// 3秒sleep
			$this->view->assign('errMessage', $clAuthError->getMessage());
			$this->setTemplate('autherror');
			return $this->view;
		} catch(CoralAuthExceptionIdLocked $idAuthError) {
			$app->logger->crit('authentication locked (id-level)');
			sleep(3);	// 3秒sleep
			$this->view->assign('errMessage', $idAuthError->getMessage());
			$this->setTemplate('autherror');
			return $this->view;
		} catch(LogicAccountValidityException $validityError) {
			$app->logger->crit('account is already expired');
			sleep(3);	// 3秒sleep
			$this->view->assign('errMessage', $validityError->getMessage());
			$this->setTemplate('autherror');
			return $this->view;
		}

		// 認証成功時はNOTICEでログ出力（09.07.17 eda）
		$userInfo = $this->authManager->getUserInfo();
		$altUserInfo = $this->authManager->getAlternativeUserInfo();
		$clientInfo = sprintf('login_id = %s, password = *****, remote_host = %s, remote_addr = %s',
							  $altUserInfo ? sprintf('%s (as %s)', $altUserInfo->LoginId, $userInfo->LoginId) : $userInfo->LoginId,
							  gethostbyaddr(f_get_client_address()), /*$_SERVER['REMOTE_HOST'],*/    // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
		                      f_get_client_address()      // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
							 );

		// 認証成功後のリダイレクトURL
		$target = 'index/index';

		// アプリケーションクラスへのユーザーIDセット
 		$obj = new \models\Table\TableUser($app->dbAdapter);
 		getUserInfoForMember($app, $userClass, $seq);
 		$userId = $obj->getUserId($userClass, $seq);

		// 最終ログイン日時保存
		$data['LastLoginDate'] = date('Y-m-d H:i:s');
		$data['UpdateId'] = $userId;
		$obj->saveUpdate($data, $userId);

        // ログインログ情報登録
		$mdllml = new TableLoginMemberLog($app->dbAdapter);
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		$mdllml->saveNew($userInfo,$altUserInfo,$userAgent);

		// ======================= パスワード期限 チェック =======================
		// 代理ログイン時は以下の処理を行わない
		if ($agencyflg == 0){
		    // パスワード有効期限間近チェック
		    if(!empty($PassHist) && $PassHist[0]['PasswdLimitDay'] >= date('Y-m-d') && $PassHist[0]['PasswdLimitDay'] <= date('Y-m-d', strtotime($passAlert ." day"))){
		        //パスワード期限 間近 かつ 立替確定処理がロック中の場合
		        if(!$passwdLimitDayFlg){
		            $target = 'login/chgpw';
		        }
		    }

		    // パスワード有効期限超過チェック
		    if ($PassHist[0]['PasswdLimitDay'] < date('Y-m-d')){
		        $target = 'login/chgpw';
		    }
		}
		// ===========================================================================

        // セッションIDを振りなおす
        session_regenerate_id(true);

        // メニュー画面に、ログイン直後であることを通知する
        $_SESSION['SESS_AFTER_LOGIN'] = 1;

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
        if ($this->_rowEnterprise) {
            // ログイン認証失敗による加盟店テーブルの更新
            $obj->updateEnterpriseNgAccess($this->_rowEnterprise);
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
	public function logoutAction() {
		$this->authManager->logout();

		// セッションIDを振りなおす
		session_regenerate_id(true);

		// セッション全体を破棄
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
	    $userInfo = $this->authManager->getUserInfo();

	    $this->view->assign('userInfo', $userInfo);

	    $cmd = $this->params()->fromRoute('cmd', 'none');

	    $this
	    ->addJavaScript( '../../js/prototype.js' )
	    ->addJavaScript( '../../js/corelib.js' );

	    $authUtil = Application::getInstance()->getAuthUtility();

	    $mdlph = new TablePasswordHistory(Application::getInstance()->dbAdapter);
	    $mdlsp = new TableSystemProperty(Application::getInstance()->dbAdapter);

	    //$PassHistCnt = $mdlph->findnew(3, $userInfo->LoginId);
	    $PassHist = $mdlph->findnew(3, $userInfo->LoginId)->current();

	    $passCnt = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdCount');
	    $passAlert = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdLimitAlertDay');
	    $sysTimeLimit = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdLimitDay');
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
	        $userInfo = $this->authManager->getUserInfo();

	        $ar = $this->getRequest()->getPost()->toArray();

	        // パスワード変更実行
	        $oldPass = $ar['opw'];
	        $newPass1 = $ar['npw1'];
	        $newPass2 = $ar['npw2'];

 	        $authUtil->setHashDisabled($userInfo->Hashed ? false : true);// 古いパスワードの突合用にハッシュ利用状況を設定
 	        $oldPassHashed = $authUtil->generatePasswordHash($userInfo->LoginId, $oldPass);
 	        $newPass1Hashed = $authUtil->generatePasswordHash($userInfo->LoginId, $newPass1);

 	        $mdlph = new TablePasswordHistory(Application::getInstance()->dbAdapter);
 	        $mdlsp = new TableSystemProperty(Application::getInstance()->dbAdapter);
 	        $mdle = new TableEnterprise(Application::getInstance()->dbAdapter);
 	        $mdleo = new TableEnterpriseOperator(Application::getInstance()->dbAdapter);

	        //パスワード最小桁数
	        $passMin = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdCount');
	        //過去パスワードの使用不可回数
	        $passTimeLimit = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'UsePasswdTimes');
	        //過去4回分のパスワードを取得
 	        $sql  = " SELECT LoginPasswd FROM T_PasswordHistory WHERE Category = 3 AND LoginId = :LoginId ORDER BY Seq DESC LIMIT " . $passTimeLimit;
 	        $lastPass = ResultInterfaceToArray(Application::getInstance()->dbAdapter->query($sql)->execute(array(':LoginId' => $userInfo->LoginId)));

 	        // パスワードの正常性
 	        if(!$this->isMatchCurrentPassword(trim($oldPass))) {
 	            $errors['eopw'] = '<font color="red">現在のパスワードに誤りがあります</font>';
 	        }
 	        if(!strlen(trim($oldPass))) {
 	            $errors['eopw'] = '<font color="red">現在のパスワードが未入力です</font>';
 	        }
 	        // 新パスワードと現行パスワードの一致(レターケース区別なし）の検出
 	        if(strcasecmp(trim($oldPass), trim($newPass1)) == 0) {
 	            $errors['enpw1'] = '<font color="red">パスワードを変更してください</font>';
 	        }

	        // 空パスワードのチェック
 	        if(!strlen(trim($newPass1))) {
 	            $errors['enpw1'] = '<font color="red">新しいパスワードが未入力です</font>';
 	        }
 	        if(!strlen(trim($newPass2))) {
 	            $errors['enpw2'] = '<font color="red">新しいパスワード（確認）が未入力です</font>';
 	        }
 	        // 確認パスワードの不一致（レターケース区別あり）の検出
 	        if(trim($newPass1) != trim($newPass2)) {
 	            $errors['enpw2'] = '<font color="red">新しいパスワードと新しいパスワード（確認）が一致しません</font>';
 	        }
 	        //英大文字、英小文字、数字、記号、桁数のチェック
 	        //パスワード文字数
 	        $pass_len = strlen(trim($newPass1));
 	        if ((!preg_match('/[a-z]/', trim($newPass1)) || !preg_match('/[A-Z]/', trim($newPass1)) || !preg_match('/[0-9]/', trim($newPass1))
 	            || !preg_match('/[ -\/:-@\[-`\{-\~]/', trim($newPass1)) || $pass_len < $passMin) && (!empty($newPass1)))
 	        {
 	            $errors['enpw1'] = '<font color="red">パスワードは英大文字、英小文字、数字、記号のすべてを含む' .$passMin. '文字以上でご入力ください。</font>';
 	        }

 	        $authUtil = Application::getInstance()->getAuthUtility();
 	        $newPassHashed = $authUtil->generatePasswordHash($userInfo->LoginId, trim($newPass1));
 	        //過去4回分のパスワードとの重複チェック
 	        foreach ($lastPass as $pass)
 	        {
 	            if ($oldPass != $newPass1)
 	            {
 	                if ($newPassHashed == $pass['LoginPasswd'])
 	                {
 	                    $errors['enpw1'] = '<font color="red">過去' .$passTimeLimit. '回に使用したパスワードは利用できません。</font>';
 	                }
 	            }
 	        }

	        if (isset($errors))
	        {
	            if (!array_key_exists('eopw' , $errors)) { $errors["eopw"]  = ""; }
	            if (!array_key_exists('enpw1', $errors)) { $errors["enpw1"] = ""; }
	            if (!array_key_exists('enpw2', $errors)) { $errors["enpw2"] = ""; }

	            $errors['opw'] = $oldPass;
	            $errors['npw1'] = $newPass1;
	            $errors['npw2'] = $newPass2;

	            $this->view->assign('error', $errors);
	            $this->setTemplate('chgpw_f');
	            return $this->view;
	        }
	        else
	        {
	            // バッチ排他制御実行
	            $mdlbl = new TableBatchLock (Application::getInstance()->dbAdapter);
	            $BatchLockData = $mdlbl->findBatchId($this::EXECUTE_BATCH_ID);

	            if ($BatchLockData['BatchLock']> 0) {
	                //入力値を設定
	                $errors['opw'] = $oldPass;
	                $errors['npw1'] = $newPass1;
	                $errors['npw2'] = $newPass2;
	                $this->view->assign('error', $errors);

	                $msg = '';
	                switch ($loginmode)
	                {
	                    //初回
	                    case '1':
	                        $msg = '初回ログインのためパスワードの変更が必要ですが、現在パスワード変更処理を行うことができません。しばらくたってから再度実行をお願い致します。';
	                        break;
	                        //期限間近
	                    case '2':
	                        $msg = '現在パスワード変更処理を行うことができません。しばらくたってから再度実行をお願い致します。';
	                        break;
	                        //期限切れ
	                    case '3':
	                        $msg = 'パスワードの期限が切れておりますが、現在パスワード変更処理を行うことができません。しばらくたってから再度実行をお願い致します。';
	                        break;
	                    default:
	                        break;
	                }
	                $this->messenger->addMessage($msg);
	                $this->view->assign('systemMessages', $this->messenger->getCurrentMessages());
	                $this->setTemplate('chgpw_f');
	                return $this->view;
	            }

	            // ユーザーIDの取得
	            $obj = new \models\Table\TableUser(Application::getInstance()->dbAdapter);
	            getUserInfoForMember(Application::getInstance(), $userClass, $seq);
	            $userId = $obj->getUserId($userClass, $seq);
	            $entRow = $mdle->findEnterprise( $userInfo->EnterpriseId )->current();

	            $updateInfo = array(
	                    'LoginPasswd' => $newPass1Hashed,
	                    'Hashed' => 1,
	                    'LastPasswordChanged' => date('Y-m-d H:i:s'),
	                    'UpdateId' => $userId,
	            );

	            if($entRow['LoginId'] == $userInfo->LoginId) {
	                $mdle->saveUpdate($updateInfo, $userInfo->EnterpriseId);
	            } else {
	                $entOpId = $mdleo->findEnterpriseOpId($userInfo->LoginId);
	                $mdleo->saveUpdate($updateInfo, $entOpId['EnterpriseOpId']);
	            }

	            $passChgData = array(
	                    'Category' => 3,
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

	            Application::getInstance()->authManager->resetLoginState();
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

	/**
	 * 指定のログインパスワードが現在の設定に一致するかを判断する
	 *
	 * @access private
	 * @param string $password 確認するパスワード
	 * @return boolean
	 */
	private function isMatchCurrentPassword($password) {
	    $mdle = new TableEnterprise(Application::getInstance()->dbAdapter);
	    $entData = $mdle->findEnterprise( $this->authManager->getUserInfo()->EnterpriseId )->current();
	    $authUtility =
	    Application::getInstance()
	    ->getAuthUtility()
	    ->setHashDisabled($entData['Hashed'] ? false : true);

	    // ログイン中のIDが加盟店IDかオペレータIDか判断する
	    if ($this->authManager->getUserInfo()->LoginId == $entData['LoginId'] )
	    {
	        $password = $authUtility->generatePasswordHash($entData['LoginId'], $password);
	        $loginPassword = $entData['LoginPasswd'];
	    }
	    else
	    {
	        // 追加分のオペレーターでログインしている場合、ログインしているオペレーターのパスワードを変更する。
	        $mdleo = new TableEnterpriseOperator(Application::getInstance()->dbAdapter);
	        // 加盟店オペレータIDを取得
	        $enterpriseOpId = $mdleo->findEnterpriseOpId($this->authManager->getUserInfo()->LoginId);
	        $entOpData = $mdleo->find($enterpriseOpId['EnterpriseOpId'])->current();

	        $password = $authUtility->generatePasswordHash($entOpData['LoginId'], $password);
	        $loginPassword = $entOpData['LoginPasswd'];
	    }

	    return strcasecmp($password, $loginPassword) == 0;
	}

}
