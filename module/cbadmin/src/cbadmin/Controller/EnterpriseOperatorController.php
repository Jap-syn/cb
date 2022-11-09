<?php
namespace cbadmin\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralValidate;
use cbadmin\Application;
use Zend\Db\Adapter\Adapter;
use models\Table\TableOperator;
use models\Table\TableGeneralPurpose;
use Coral\Coral\CoralCodeMaster;
use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseGeneralUtils;
use Zend\Http\Header\Vary;
use models\Table\TableEnterpriseOperator;

class EnterpriseOperatorController extends CoralControllerAction
{
    protected $_componentRoot = './application/views/components';

    /**
     * saveAction → complationAction の間でデータを持ちまわる際のキー
     * @var string
     */
    const SES_EDATA      = "oData";

    /**
     * 権限区分（加盟店＝2）
     * @var int
     */
    const ROLE_CODE_CLASS = 2;

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
            ->addJavaScript('../js/prototype.js');

        $this->setPageTitle("後払い.com - 加盟店オペレーター管理");
    }

    /**
     * 加盟店オペレーター登録フォームの表示
     */
    public function formAction()
    {
        // 権限チェック
        $this->checkAdminPermission();

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        // パラメータ取得
        $params = $this->getParams();

        // POSTされたデータを取得（※確認画面から「戻る」の場合のみデータがある）
        $ar = $this->getRequest()->getPost()->toArray();
        $eData = (array_key_exists('data', $ar)) ? $ar['data'] : null;
        $rolecode = ($eData != null) ? $eData['RoleCode'] : null;

        $eData['EnterpriseId'] = $params['eid'];

        $this->view->assign('roleCodeTag', BaseHtmlUtils::SelectTag("data[RoleCode]", $codeMaster->getRoleCodeMaster(self::ROLE_CODE_CLASS), $rolecode));
        $this->view->assign('edit', false);// editモードはfalse
        $this->view->assign('eid', $eData['EnterpriseId']);
        $this->view->assign('fd', $eData); // POSTされたデータを割り当て

        return $this->view;
    }

    /**
     * 加盟店オペレーター編集画面を表示
     */
    public function editAction()
    {
        // 権限チェック
        $this->checkAdminPermission();

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        // POSTされたデータを取得（※確認画面から「戻る」の場合のみデータがある）
        $enterpriseOpId = $this->params()->fromRoute( 'enterpriseopid', '-1' );
        $ar = $this->getRequest()->getPost()->toArray();
        $eData = (array_key_exists('data', $ar)) ? $ar['data'] : null;

        if ($eData == null) {
            // データがPOSTされていない場合はDBから読み出す
            $entoperators = new TableEnterpriseOperator($this->app->dbAdapter);
            $eData = $entoperators->find($enterpriseOpId)->current();
            $eData['InvalidFlg'] = ($eData['ValidFlg'] == 1) ? 0 : 1;
        }

        $rolecode = ($eData != null) ? $eData['RoleCode'] : null;

        $this->view->assign('roleCodeTag', BaseHtmlUtils::SelectTag("data[RoleCode]", $codeMaster->getRoleCodeMaster(self::ROLE_CODE_CLASS), $rolecode));
        $this->view->assign('edit', true); // editモードはtrue
        $this->view->assign('eid', $eData['EnterpriseId']);
        $this->view->assign('fd', $eData); // POSTまたはDBから読み出したデータを割り当て

        $this->setTemplate('form');

        return $this->view;
	}

	/**
	 * 加盟店オペレーター登録内容の確認
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

        $mdlEntOp = new TableEnterpriseOperator($this->app->dbAdapter);

        // チェック：ログインID
        if (!$vali->isNotEmpty($eData["LoginId"]))
        {
            $error["LoginId"] = sprintf($errorTmp, "<br />ログインIDは必須です。");
        }
        // 新規登録時
        if (!isset($eData['EnterpriseOpId']))
        {
            $this->view->assign('edit', false);
            if (!$mdlEntOp->isNewLoginId($eData["LoginId"]))
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
            $this->view->assign('eid', $eData['EnterpriseId']);

            // フォームを再表示
            $this->setTemplate('form');
        }
        else
        {
            // マスターがらみの項目については、キャプションを求めてセットする。
            $eData["RoleCodeName"] = $codeMaster->getRoleCodeCaption((int)$eData["RoleCode"]);

            // ビューに入力を割り当てる
            $this->view->assign('data', $eData);// confirm.phtml なのでアサインする変数は data
            $this->view->assign('eid', $eData['EnterpriseId']);
        }

        return $this->view;
    }

    /**
     * 加盟店オペレーター登録を実行
     */
    public function saveAction()
    {
        // 権限チェック
        $this->checkAdminPermission();

        $mdlEntOp = new TableEnterpriseOperator($this->app->dbAdapter);

        $ar = $this->getRequest()->getPost()->toArray();
        $eData = (array_key_exists('data', $ar)) ? $ar['data'] : null;
        if ($eData == null) {
            return $this->_redirect("enterpriseoperator/list/eid/" . $eData['EnterpriseId']);// 登録データが存在しなければリストへリダイレクト
        }
        if (isset($eData['InvalidFlg'])) {
            $eData['ValidFlg'] = ($eData['InvalidFlg'] == 1) ? 0 : 1;
            unset($eData['InvalidFlg']);
        }

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        if (isset($eData['EnterpriseOpId'])) {
            // 編集
            unset($eData['LoginPasswd']);
            $eData['UpdateId'] = $userId;
            $mdlEntOp->saveUpdate($eData, $eData['EnterpriseOpId']);
        }
        else {
            // 新規保存
            $eData['RegistId'] = $userId;
            $newId = $mdlEntOp->saveNew($eData);
            $newPassword = BaseGeneralUtils::MakePassword(8);// パスワードをランダム設定

            $userInfo = $this->app->authManagerAdmin->getUserInfo();
            $authUtil = $this->app->getAuthUtility();
            $eData['LoginPasswd'] = $authUtil->generatePasswordHash($eData['LoginId'], $newPassword);
            $eData['Hashed'] = 1;
            $eData['UpdateId'] = $userId;

            $mdlEntOp->saveUpdate($eData, $newId);// 更新保存
            $eData['EnterpriseOpId'] = $newId;
            $eData['GeneratedPassword'] = $newPassword;

        }

        // 権限の表示ラベルを展開
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $eData['RoleCodeName'] = $codeMaster->getRoleCodeCaption((int)$eData['RoleCode']);

        // 完了画面へリダイレクトするため、セッションへデータを退避する
        $_SESSION[self::SES_EDATA] = $eData;

        return $this->_redirect("enterpriseoperator/completion/eid/" . $eData['EnterpriseId']);
    }

    /**
     * 登録完了画面の表示
     */
    public function completionAction()
    {
        // パラメータ取得
        $params = $this->getParams();

        // 登録データのセッションデータが存在しなければリストへリダイレクト。
        if (!isset($_SESSION[self::SES_EDATA]))
        {
            return $this->_redirect("enterpriseoperator/list/eid/" . $params['eid']);
        }

        $this->view->assign('data', $_SESSION[self::SES_EDATA]);
        $this->view->assign('eid', $params['eid']);

        unset($_SESSION[self::SES_EDATA]);

        return $this->view;
    }

    /**
     * 加盟店オペレーター一覧を表示
     */
    public function listAction()
    {
        // 権限チェック
        $this->checkAdminPermission();

        // パラメータ取得
        $params = $this->getParams();

        $ar = $this->getRequest()->getPost()->toArray();
        $eData = (array_key_exists('data', $ar)) ? $ar['data'] : array();

        $eData['EnterpriseId'] = $params['eid'];

        $mdlEntOp = new TableEnterpriseOperator($this->app->dbAdapter);
        $this->view->assign('codeMaster', new CoralCodeMaster($this->app->dbAdapter));
        $this->view->assign('eid', $eData['EnterpriseId']);
        $this->view->assign('list', $mdlEntOp->findEnterprise($eData['EnterpriseId']));

        return $this->view;
    }

    /**
     * パスワード変更
     */
    public function chgpwAction()
    {
        unset($error);

        $cmd = $this->params()->fromRoute('cmd', 'none');

        $this->addJavaScript( '../js/prototype.js' );
        $this->addJavaScript( '../js/corelib.js' );

        $authUtil = $this->app->getAuthUtility();
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

            $mdlEntOp = new TableEnterpriseOperator($this->app->dbAdapter);

            if (!$mdlEntOp->isExists($userInfo->LoginId, $oldPassHashed))
            {
                $error['eopw'] = '<font color="red">現在のパスワードが一致しません。</font>';
            }

            if( empty( $newPass1) )
            {
                $error['enpw1'] = '<font color="red">新しいパスワードを空にすることはできません。</font>';
            }

            if ($newPass1 != $newPass2)
            {
                $error['enpw2'] = '<font color="red">パスワードが一致しません。</font>';
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
                        'UpdateId' => $userId,
                );
                $mdlEntOp->saveUpdate($updateInfo, $userInfo->OpId);
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
}

