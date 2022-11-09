<?php
namespace oemadmin\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use models\Table\TableOemOperator;
use models\Table\TablePasswordHistory;
use models\Table\TableSystemProperty;
use oemadmin\Application;
use models\Logic\AccountValidity\LogicAccountValidityPasswordValidator;

class OperatorController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

	/**
	 * saveAction → complationAction の間でデータを持ちまわる際のキー
	 * @var string
	 */
	const SES_EDATA      = "oData";

	/**
	 * アプリケーションオブジェクト
	 * @var Application
	 */
	private $app;

	/**
	 * 管理者権限フラグ
	 * @var boolean
	 */
	private $isAdmin;

	/**
	 * Controllerを初期化する
	 */
	public function _init()
	{
		$this->app = Application::getInstance();
		$userInfo = $this->app->authManagerAdmin->getUserInfo();

		// TODO: ログイン中アカウントの権限を確認して、利用可能なロールで無い場合はエラーにする
		$this->isAdmin = $userInfo->RoleCode > 1;

		$this->view->assign('userInfo', $userInfo);

		$this
			->addStyleSheet($this->app->getOemCss())
			->addJavaScript('../../js/prototype.js');

		$this->setPageTitle($this->app->getOemServiceName()." - パスワード変更");

	}

	/**
	 * オペレーター登録フォームの表示
	 */
	/*
	public function formAction()
	{
		$this->_redirect('operator');
		return;
		// 権限チェック
		$this->checkAdminPermission();

		$codeMaster = new Coral_CodeMaster();

		// POSTされたデータを取得（※確認画面から「戻る」の場合のみデータがある）
		$eData = $this->getRequest()->getParam('data', array());

		// 権限SELECTタグ
		$this
			->view
				->assign('roleCodeTag',
					NetB_HtmlUtils::SelectTag("data[RoleCode]",
						$codeMaster->getRoleCodeMaster(),
						$eData['RoleCode']
					)
				)
				->assign('edit', false)		// editモードはfalse
				->assign('fd', $eData);		// POSTされたデータを割り当て
	}
	 */

	/**
	 * オペレーター編集画面を表示
	 */
	/*
	public function editAction()
	{
		$this->_redirect('operator');
		return;
		// 権限チェック
		$this->checkAdminPermission();

		$codeMaster = new Coral_CodeMaster();

		$req = $this->getRequest();
		$opid = $req->getParam('opid', -1);

		// POSTされたデータを取得（※確認画面から「戻る」の場合のみデータがある）
		$eData = $req->getPost('data', false);
		if( !$req->getParam('data') ) {
			// データがPOSTされていない場合はDBから読み出す
			$operators = new Table_Operator($this->app->dbAdapter);
			$eData = $operators->findOperator2($opid);
		}

		// 権限SELECTタグ
		$this
			->view
				->assign('roleCodeTag',
					NetB_HtmlUtils::SelectTag("data[RoleCode]",
						$codeMaster->getRoleCodeMaster(),
						$eData['RoleCode']
					)
				)
				->assign('edit', true)		// editモードはtrue
				->assign('fd', $eData);		// POSTまたはDBから読み出したデータを割り当て

		// form.phtmlを表示
		$this->_helper->viewRenderer('form');
	}
	 */

	/**
	 * オペレーター登録内容の確認
	 */
	/*
	public function confirmAction()
	{
		$this->_redirect('operator');
		return;
		// 権限チェック
		$this->checkAdminPermission();

		$codeMaster = new Coral_CodeMaster();

		$req = $this->getRequest();
		$eData = $req->getParam('data', array());

		// 入力チェック
		$errorTmp = '<font color="red">%s</font>';
		$vali = new Coral_Validate();
		$mdlOperator = new Table_Operator($this->app->dbAdapter);

		// チェック：ログインID
		if (!$vali->isNotEmpty($eData["LoginId"]))
		{
			$error["LoginId"] = sprintf($errorTmp, "<br />ログインIDは必須です。");
		}
		// 新規登録時
		if (!isset($eData['OpId']))
		{
			$this->view->assign('edit', false);
			if (!$mdlOperator->isNewLoginId($eData["LoginId"]))
			{
				$error["LoginId"] = sprintf($errorTmp, "<br />指定されたログインIDは他のユーザーが使用しています。");
			}
		} else {
			$this->view->assign('edit', true);
		}
		// チェック：氏名
		if (!$vali->isNotEmpty($eData["NameKj"]))
		{
			$error["NameKj"] = sprintf($errorTmp, "<br />氏名は必須です。");
		}
		// チェック：氏名カナ
		/*
		if (!$vali->isNotEmpty($eData["NameKn"]))
		{
			$error["NameKn"] = sprintf($errorTmp, "<br />氏名カナは必須です。");
		}
		// チェック：権限
		if ($eData["RoleCode"] == "0")
		{
			$error["RoleCode"] = sprintf($errorTmp, "<br />権限を選択してください。");
		}

		if (isset($error))
		{
			// エラーがあればエラーメッセージをセット。
			$this->view->assign('error', $error);

			// ビューに入力を割り当てる
			$this
				->view
					->assign('roleCodeTag',
						NetB_HtmlUtils::SelectTag("data[RoleCode]",
							$codeMaster->getRoleCodeMaster(),
							$eData['RoleCode']
						)
					)
					->assign('fd', $eData);		// form.phtml なのでアサインする変数名は fd

			// フォームを再表示
			$this->_helper->viewRenderer('form');
		}
		else
		{
			// マスターがらみの項目については、キャプションを求めてセットする。
			$eData["RoleCodeName"] = $codeMaster->getRoleCodeCaption((int)$eData["RoleCode"]);

			// ビューに入力を割り当てる
			$this->view->assign('data', $eData);	// confirm.phtml なのでアサインする変数は data

		}
	}
	 */

	/**
	 * オペレーター登録を実行
	 */
	/*
	public function saveAction()
	{
		$this->_redirect('operator');
		return;
		// 権限チェック
		$this->checkAdminPermission();

		$mdlOperator = new Table_Operator($this->app->dbAdapter);
		$eData = $this->getRequest()->getParam('data', false);

		// 登録データが存在しなければリストへリダイレクト。
		if ( ! $eData )
		{
			$this->_redirect("operator/list");
		}


		if (isset($eData['OpId']))
		{
			// 編集
			$mdlOperator->saveUpdate($eData, $eData['OpId']);
		}
		else
		{
			// 新規保存
			$newId = $mdlOperator->saveNew($eData);

			$eData["LoginPasswd"] = NetB_GeneralUtils::MakePassword(8);				// パスワードをランダム設定

			$mdlOperator->saveUpdate($eData, $newId);							// 更新保存
			$eData['OpId'] = $newId;
		}

		// 権限の表示ラベルを展開
		$codeMaster = new Coral_CodeMaster();
		$eData['RoleCodeName'] = $codeMaster->getRoleCodeCaption((int)$eData['RoleCode']);

		// 完了画面へリダイレクトするため、セッションへデータを退避する
		$_SESSION[self::SES_EDATA] = $eData;

		$this->_redirect("operator/completion");
	}
	*/

	/**
	 * 登録完了画面の表示
	 */
	/*
	public function completionAction()
	{
		$this->_redirect('operator');
		return;
		// 登録データのセッションデータが存在しなければリストへリダイレクト。
		if (!isset($_SESSION[self::SES_EDATA]))
		{
			$this->_redirect("operator/list");
		}

		$this->view->assign('data', $_SESSION[self::SES_EDATA]);

		unset($_SESSION[self::SES_EDATA]);
	}
	*/

	/**
	 * オペレーター一覧を表示
	 */
        /*
	public function listAction()
	{
		// 権限チェック
		$this->checkAdminPermission();

		$mdlOperator = new Table_OemOperator($this->app->dbAdapter);
		$this->view->assign('codeMaster', new Coral_CodeMaster());
		$this->view->assign('list', $mdlOperator->getAll());
	}
        */

	/**
	 * パスワード変更
	 */
	public function chgpwAction()
	{
		// ここは実験的に伝統的なコマンド制御で実装してみる。
		// コマンドはGET、データはPOST
		unset($error);

		$params = $this->getParams();
		$cmd = isset($params['cmd']) ? $params['cmd'] : 'none';

		$mdlph = new TablePasswordHistory($this->app->dbAdapter);
		$mdlsp = new TableSystemProperty($this->app->dbAdapter);
		//パスワード期限切れ日数
		$sysTimeLimit = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdLimitDay');

		$this
			->addJavaScript( '../../js/prototype.js' )
			->addJavaScript( '../../js/corelib.js' );

		$this->view
			->assign('altLogin', $this->app->authManagerAdmin->getAlternativeUserInfo() != null);

		/** @var NetB_Auth_Utility */
		$authUtil = $this->app->getAuthUtility();
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

			//パスワード最小桁数
			$passMin = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdCount');
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
			}/* else
			if( strlen($newPass1) < 4)
			{
				$error['enpw1'] = '<font color="red">パスワードは4文字以上で入力してください。</font>';
			}*/	// 文字数チェックはパスワードルール内の定義となったためここでのチェックを廃止（2015.4.20 eda）

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
			    $this->setTemplate('chgpw_f');
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
			    $this->setTemplate('chgpw_e');
			    return $this->view;
			}
		}
		else
		{
			// パスワード変更フォーム表示
			$this->setTemplate('chgpw_f');
			return $this->view;
		}
	}

	private function checkAdminPermission() {
		// 権限により機能制約を設けるなら以下のコメントアウトを解除する
		// TODO: 抽象アクションクラスをもう1層設けるなりして、もう少し共通化したい（09.07.17 eda）
		//if( ! $this->isAdmin ) throw new Exception('権限がありません');
	}


	    /**
	     * 初回ログイン、パスワード期限間近、パスワード期限切れ時パスワード変更
	     */
	    public function changepwAction()
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

//  	        $PassHistCnt = $mdlph->findnew(4, $userInfo->LoginId);
 	        $PassHist = $mdlph->findnew(4, $userInfo->LoginId)->current();

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


	            //パスワード最小桁数
	            $passMin = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdCount');
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
	                if ($oldPass != $newPass1)
	                {
	                    if ($newPass1Hashed == $pass['LoginPasswd'])
	                    {
	                        $error['enpw1'] = '<font color="red">過去' .$passTimeLimit. '回に使用したパスワードは利用できません。</font>';
	                    }
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
	                $this->setTemplate('changepw_f2');
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
	                $this->setTemplate('changepw_e2');
	                return $this->view;
	            }
	        }
	        else
	        {
	            // パスワード変更フォーム表示
	            $this->setTemplate('changepw_f2');
	            return $this->view;
	        }
	    }
}

