<?php
namespace cbadmin\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralValidate;
use cbadmin\Application;
use Zend\Db\Adapter\Adapter;
use models\Table\TableOperator;
use models\Table\TableGeneralPurpose;
use models\Table\TablePasswordHistory;
use models\Table\TableSystemProperty;
use Coral\Coral\CoralCodeMaster;
use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseGeneralUtils;
use Zend\Http\Header\Vary;
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
	 * 権限区分（CB＝2）
	 * @var int
	 */
	const ROLE_CODE_CLASS = 0;

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

        // ログイン中アカウントの権限を確認して、利用可能なロールで無い場合はエラーにする
        $this->isAdmin = $userInfo->RoleCode > 1;

        $this->view->assign('userInfo', $userInfo);

        $this
            ->addStyleSheet('../css/default02.css')
            ->addJavaScript('../js/prototype.js')
            ->addJavaScript('../js/corelib.js');

        $this->setPageTitle("後払い.com - オペレーター管理");
	}

	/**
	 * オペレーター登録フォームの表示
	 */
	public function formAction()
	{
	    // 権限チェック
        $this->checkAdminPermission();

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        // POSTされたデータを取得（※確認画面から「戻る」の場合のみデータがある）
        $ar = $this->getRequest()->getPost()->toArray();
        $eData = (array_key_exists('data', $ar)) ? $ar['data'] : null;
        $rolecode = ($eData != null) ? $eData['RoleCode'] : null;

        $this->view->assign('roleCodeTag', BaseHtmlUtils::SelectTag("data[RoleCode]", $codeMaster->getRoleCodeMaster(self::ROLE_CODE_CLASS), $rolecode));
        $this->view->assign('edit', false);// editモードはfalse
        $this->view->assign('fd', $eData); // POSTされたデータを割り当て

        return $this->view;
	}

	/**
	 * オペレーター編集画面を表示
	 */
	public function editAction()
	{
        // 権限チェック
        $this->checkAdminPermission();

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        // POSTされたデータを取得（※確認画面から「戻る」の場合のみデータがある）
        $opid = $this->params()->fromRoute( 'opid', '-1' );
        $ar = $this->getRequest()->getPost()->toArray();
        $eData = (array_key_exists('data', $ar)) ? $ar['data'] : null;

        if ($eData == null) {
            // データがPOSTされていない場合はDBから読み出す
            $operators = new TableOperator($this->app->dbAdapter);
            $eData = $operators->findOperator($opid)->current();
            $eData['InvalidFlg'] = ($eData['ValidFlg'] == 1) ? 0 : 1;
        }
        $rolecode = ($eData != null) ? $eData['RoleCode'] : null;

        $this->view->assign('roleCodeTag', BaseHtmlUtils::SelectTag("data[RoleCode]", $codeMaster->getRoleCodeMaster(self::ROLE_CODE_CLASS), $rolecode));
        $this->view->assign('edit', true); // editモードはtrue
        $this->view->assign('fd', $eData); // POSTまたはDBから読み出したデータを割り当て

        $this->setTemplate('form');

        return $this->view;
	}

	/**
	 * オペレーター登録内容の確認
	 */
	public function confirmAction()
	{
        // 権限チェック
        $this->checkAdminPermission();

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        $ar = $this->getRequest()->getPost()->toArray();
        $eData = (array_key_exists('data', $ar)) ? $ar['data'] : array();
        $eData['InvalidFlg'] = (isset($eData['InvalidFlg'])) ? 1 : 0;

        // 入力チェック
        $errorTmp = '<font color="red">%s</font>';
        $vali = new CoralValidate();
        $mdlOperator = new TableOperator($this->app->dbAdapter);

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
        if (strlen($eData["NameKn"]) > 0 && !preg_match( '/^[ァ-ヾ]+$/u', preg_replace( '/(\s|　)/', '', $eData["NameKn"] ) ))
        {
            $error["NameKn"] = sprintf($errorTmp, "<br />氏名カナがカタカナでないです。");
        }
        // チェック：権限
        if ($eData["RoleCode"] == "0")
        {
            $error["RoleCode"] = sprintf($errorTmp, "<br />権限を選択してください。");
        }

        if (isset($error))
        {
            if (!array_key_exists('LoginId' , $error)) { $error["LoginId"]  = ""; }
            if (!array_key_exists('NameKj'  , $error)) { $error["NameKj"]   = ""; }
            if (!array_key_exists('RoleCode', $error)) { $error["RoleCode"] = ""; }

            // エラーがあればエラーメッセージをセット。
            $this->view->assign('error', $error);

            // ビューに入力を割り当てる
            $this->view->assign('roleCodeTag', BaseHtmlUtils::SelectTag("data[RoleCode]", $codeMaster->getRoleCodeMaster(self::ROLE_CODE_CLASS), $eData['RoleCode']));
            $this->view->assign('fd', $eData);// form.phtml なのでアサインする変数名は fd

            // フォームを再表示
            $this->setTemplate('form');
        }
        else
        {
            // マスターがらみの項目については、キャプションを求めてセットする。
            $eData["RoleCodeName"] = $codeMaster->getRoleCodeCaption((int)$eData["RoleCode"]);

            // ビューに入力を割り当てる
            $this->view->assign('data', $eData);// confirm.phtml なのでアサインする変数は data
        }

        return $this->view;
	}

	/**
	 * オペレーター登録を実行
	 */
	public function saveAction()
	{
        // 権限チェック
        $this->checkAdminPermission();

        $mdlOperator = new TableOperator($this->app->dbAdapter);

        $ar = $this->getRequest()->getPost()->toArray();
        $eData = (array_key_exists('data', $ar)) ? $ar['data'] : null;
        if ($eData == null) {
            return $this->_redirect("operator/list");// 登録データが存在しなければリストへリダイレクト
        }
        if (isset($eData['InvalidFlg'])) {
            $eData['ValidFlg'] = ($eData['InvalidFlg'] == 1) ? 0 : 1;
            unset($eData['InvalidFlg']);
        }

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        if (isset($eData['OpId'])) {
            // 編集
            unset($eData['LoginPasswd']);
            $eData['UpdateId'] = $userId;
            $mdlOperator->saveUpdate($eData, $eData['OpId']);
        }
        else {

            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
            try {
                // 新規保存
                $eData['RegistId'] = $userId;
                $newId = $mdlOperator->saveNew($eData);
                $newPassword = $this->generateNewPassword($eData['LoginId']);// パスワードをランダム設定

                $userInfo = $this->app->authManagerAdmin->getUserInfo();
                $authUtil = $this->app->getAuthUtility();
                $eData['LoginPasswd'] = $authUtil->generatePasswordHash($eData['LoginId'], $newPassword);
                $eData['Hashed'] = 1;
                $eData['LastPasswordChanged'] = date('Y-m-d H:i:s');// パスワード更新日時を設定
                $eData['UpdateId'] = $userId;

                $mdlOperator->saveUpdate($eData, $newId);// 更新保存
                $eData['OpId'] = $newId;
                $eData['GeneratedPassword'] = $newPassword;

                // T_User新規登録
                $obj->saveNew(array('UserClass' => 0, 'Seq' => $newId, 'RegistId' => $userId, 'UpdateId' => $userId,));
                $this->app->dbAdapter->getDriver()->getConnection()->commit();
            }
            catch(\Exception $err) {
                // 例外はエラーコントローラにお任せ
                $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
                throw $err;
            }
        }

        // 権限の表示ラベルを展開
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $eData['RoleCodeName'] = $codeMaster->getRoleCodeCaption((int)$eData['RoleCode']);

        // 完了画面へリダイレクトするため、セッションへデータを退避する
        $_SESSION[self::SES_EDATA] = $eData;

        return $this->_redirect("operator/completion");
	}

	/**
	 * 登録完了画面の表示
	 */
	public function completionAction()
	{
        // 登録データのセッションデータが存在しなければリストへリダイレクト。
        if (!isset($_SESSION[self::SES_EDATA]))
        {
            return $this->_redirect("operator/list");
        }

        $this->view->assign('data', $_SESSION[self::SES_EDATA]);

        unset($_SESSION[self::SES_EDATA]);

        return $this->view;
	}

    /**
     * オペレーター一覧を表示
     */
    public function listAction()
    {
        // 権限チェック
        $this->checkAdminPermission();

        $mdlOperator = new TableOperator($this->app->dbAdapter);
        $this->view->assign('codeMaster', new CoralCodeMaster($this->app->dbAdapter));
        $this->view->assign('list', $mdlOperator->getAll());

        return $this->view;
    }

	/**
	 * パスワード変更
	 */
	public function chgpwAction()
	{
        unset($error);

        $cmd = $this->params()->fromRoute('cmd', 'none');

        $this
            ->addJavaScript( '../js/prototype.js' )
            ->addJavaScript( '../js/corelib.js' );

        $authUtil = $this->app->getAuthUtility();

        $mdlph = new TablePasswordHistory($this->app->dbAdapter);
        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        //パスワード期限切れ日数
        $sysTimeLimit = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'NetCoTranLimitDays');

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

            $mdlop = new TableOperator($this->app->dbAdapter);

            //パスワード最小桁数
            $passMin = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'PasswdCount');
            //パスワード文字数
            $pass_len = strlen($newPass1);
            //過去パスワードの使用不可回数
            $passTimeLimit = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'UsePasswdTimes');
            //過去4回分のパスワードを取得
            $sql  = " SELECT LoginPasswd FROM T_PasswordHistory WHERE Category = :Category AND LoginId = :LoginId ORDER BY Seq DESC LIMIT " . $passTimeLimit;
            $lastPass = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(array(':Category' => 1, ':LoginId' => $userInfo->LoginId)));

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
                || !preg_match('/[ -\/:-@\[-`\{-\~]/', $newPass1) || $pass_len < $passMin)
            {
                $error['enpw1'] = '<font color="red">パスワードは英大文字、英小文字、数字、記号のすべてを含む' .$passMin. '文字以上でご入力ください。</font>';
            }

            foreach ($lastPass as $pastPass['LoginPasswd'] => $pass)
            {
                if ($newPass1Hashed == $pass['LoginPasswd'])
                {
                    $error['enpw1'] = '<font color="red">過去' .$passTimeLimit. '回に使用したパスワードは利用できません。</font>';
                }
            }

            // ここまでエラーがなければパスワードルール検証を実施
            // count関数対策
            if (!isset($error) || empty($error))
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

                // パスワード履歴テーブルに1件追加する
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

	private function checkAdminPermission() {
		// 権限により機能制約を設けるなら以下のコメントアウトを解除する
		// TODO: 抽象アクションクラスをもう1層設けるなりして、もう少し共通化したい（09.07.17 eda）
		//if( ! $this->isAdmin ) throw new Exception('権限がありません');
	}

    public function resetpswAction() {
        if(!$this->isAdmin) throw new \Exception('権限がありません');

        $operators = new TableOperator($this->app->dbAdapter);
        $mdlph = new TablePasswordHistory($this->app->dbAdapter);
        $mdlsp = new TableSystemProperty($this->app->dbAdapter);

        $params = $this->getParams();
        $opId = isset($params['opid']) ? $params['opid'] : -1;

        //パスワード期限切れ日数
        $sysTimeLimit = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'NetCoTranLimitDays');

        // 自分自身のパスワードリセット要求はパスワード変更フォームに付け替える
        if($this->app->authManagerAdmin->getUserInfo()->OpId == $opId) {
            return $this->_redirect('operator/chgpw');
        }

        $data = $operators->findOperator($opId)->current();
        if(!$data) {
            // 対象オペレータが存在しない場合は一覧へリダイレクト
            return $this->_redirect('operator/list');
        }

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // ランダムパスワードを生成
            $newPassword = $this->generateNewPassword($data['LoginId']);
            $authUtil = $this->app->getAuthUtility();

            // ハッシュ済みパスワードで更新
            $eData = array(
                    'LoginPasswd' => $authUtil->generatePasswordHash($data['LoginId'], $newPassword),
                    'Hashed' => 1,
                    'LastPasswordChanged' => date('Y-m-d H:i:s'),
                    'UpdateId' => $userId,
            );
            // パスワード更新実行
            $operators->saveUpdate($eData, $data['OpId']);

            // 生成されたパスワードや基本情報を表示データに反映
            $eData['GeneratedPassword'] = $newPassword;
            $eData = array_merge($eData, $data);

            $loginData = array(
                    'Category' => 1,
                    'LoginId' => $data['LoginId'],
                    'LoginPasswd' => $authUtil->generatePasswordHash($data['LoginId'], $newPassword),
                    'PasswdStartDay' => date('Y-m-d'),
                    'PasswdLimitDay' => date('Y-m-d', strtotime("+$sysTimeLimit days")),
                    'Hashed' => 1,
                    'RegistDate' => date('Y-m-d H:i:s'),
                    'RegistId' => $userId,
                    'UpdateDate' => date('Y-m-d H:i:s'),
                    'UpdateId' => $userId,
                    'ValidFlg' => 0);

            //パスワード履歴テーブルに1件追加する
            $mdlph->saveNew($loginData);

            //パスワード履歴テーブルの有効フラグを更新する
            $mdlph->validflgUpdate(1, $data['LoginId'], $userId);

            // DB変更をコミット
            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        } catch(\Exception $err) {
            // 例外はエラーコントローラにお任せ
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            throw $err;
        }

        // 権限の表示ラベルを展開
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $eData['RoleCodeName'] = $codeMaster->getRoleCodeCaption((int)$data['RoleCode']);

        // 完了画面へリダイレクトするため、セッションへデータを退避する
        $_SESSION[self::SES_EDATA] = $eData;

        return $this->_redirect("operator/completion");
    }

    /**
     * 新しいランダムパスワードを生成する
     *
     * @access protected
     * @param null | string $loginId ログインID
     * @return string
     */
    protected function generateNewPassword($loginId = null)
    {
        $validator = LogicAccountValidityPasswordValidator::getDefaultValidator();
        $i = 0;
        while (true) {
            $this->app->logger->debug(sprintf('[OperatorController::generateNewPassword] generating new password for %s (total %d times)', $loginId, ++$i));
            $newPassword = BaseGeneralUtils::MakePassword(8);			// パスワードをランダム設定
            if ($validator->isValid($newPassword, $loginId))
            {
                return $newPassword;
            }
        }
    }
}

