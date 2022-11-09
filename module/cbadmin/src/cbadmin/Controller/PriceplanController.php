<?php
namespace cbadmin\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use cbadmin\Application;
use models\Table\TablePricePlan;

class PriceplanController extends CoralControllerAction
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

        // ログイン中アカウントの権限を確認して、利用可能なロールで無い場合はエラーにする
        $this->isAdmin = $userInfo->RoleCode > 1;

        $this->view->assign('userInfo', $userInfo);

        $this->addStyleSheet('../css/default02.css');
        $this->addJavaScript('../js/prototype.js');

        $this->setPageTitle("後払い.com - 加盟店料金プラン管理");
	}

	/**
	 * 加盟店料金プラン登録フォームの表示
	 */
	public function formAction()
	{
        // POSTされたデータを取得（※確認画面から「戻る」の場合のみデータがある）
        $ar = $this->getRequest()->getPost()->toArray();
        $eData = (array_key_exists('data', $ar)) ? $ar['data'] : null;
        $rolecode = ($eData != null) ? $eData['RoleCode'] : null;

        $this->view->assign('edit', false);// editモードはfalse

        // 初期値の考慮
        $eData['PricePlanName'] = isset($eData['PricePlanName']) ? $eData['PricePlanName'] : '';
        $eData['MonthlyFee'] = isset($eData['MonthlyFee']) ? $eData['MonthlyFee'] : '';
        $eData['SettlementAmountLimit'] = isset($eData['SettlementAmountLimit']) ? $eData['SettlementAmountLimit'] : '';
        $eData['SettlementFeeRate'] = isset($eData['SettlementFeeRate']) ? $eData['SettlementFeeRate'] : '';
        $eData['ClaimFeeBS'] = isset($eData['ClaimFeeBS']) ? $eData['ClaimFeeBS'] : '';
        $eData['ClaimFeeDK'] = isset($eData['ClaimFeeDK']) ? $eData['ClaimFeeDK'] : '';
        $eData['ReClaimFee'] = isset($eData['ReClaimFee']) ? $eData['ReClaimFee'] : '';
        $eData['ValidFlg'] = isset($eData['ValidFlg']) ? $eData['ValidFlg'] : 1;

        $this->view->assign('fd', $eData); // POSTされたデータを割り当て

        return $this->view;
	}

	/**
	 * 加盟店料金プラン編集画面を表示
	 */
	public function editAction()
	{
	    // POSTされたデータを取得（※確認画面から「戻る」の場合のみデータがある）
        $ppid = $this->params()->fromRoute( 'ppid', '-1' );
        $ar = $this->getRequest()->getPost()->toArray();
        $eData = (array_key_exists('data', $ar)) ? $ar['data'] : null;

        if ($eData == null) {
            // データがPOSTされていない場合はDBから読み出す
            $mdl = new TablePricePlan($this->app->dbAdapter);
            $eData = $mdl->find($ppid)->current();
        }
        $rolecode = ($eData != null) ? $eData['RoleCode'] : null;

        $this->view->assign('edit', true); // editモードはtrue
        $this->view->assign('fd', $eData); // POSTまたはDBから読み出したデータを割り当て

        $this->setTemplate('form');

        return $this->view;
	}

	/**
	 * 加盟店料金プラン登録内容の確認
	 */
	public function confirmAction()
	{
        $ar = $this->getRequest()->getPost()->toArray();
        $eData = (array_key_exists('data', $ar)) ? $ar['data'] : array();

        // 入力チェック
        $errors = $this->validate($eData);

        // 新規登録or更新
        $this->view->assign('edit', (!isset($eData['PricePlanId'])) ? false : true );

        // count関数対策
        if (!empty($errors)) {
            // エラーがあればエラーメッセージをセット。
            $this->view->assign('error', $errors);
            $this->view->assign('fd', $eData); // form.phtml なのでアサインする変数名は fd
            $this->setTemplate('form');
        }
        else {
            $this->view->assign('data', $eData);// confirm.phtml なのでアサインする変数は data
        }

        return $this->view;
	}

	/**
	 * 加盟店料金プラン登録を実行
	 */
	public function saveAction()
	{
        $mdl = new TablePricePlan($this->app->dbAdapter);

        $ar = $this->getRequest()->getPost()->toArray();
        $eData = (array_key_exists('data', $ar)) ? $ar['data'] : null;
        if ($eData == null) {
            return $this->_redirect("priceplan/list");// 登録データが存在しなければリストへリダイレクト
        }

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        if (isset($eData['PricePlanId'])) {
            // 編集
            $eData['UpdateId'] = $userId;
            $mdl->saveUpdate($eData, $eData['PricePlanId']);
        }
        else {
            // 新規保存
            $eData['RegistId'] = $userId;
            $eData['UpdateId'] = $userId;
            $mdl->saveNew($eData);
        }

        // 完了画面へリダイレクトするため、セッションへデータを退避する
        $_SESSION[self::SES_EDATA] = $eData;

        return $this->_redirect("priceplan/completion");
	}

	/**
	 * 登録完了画面の表示
	 */
	public function completionAction()
	{
        // 登録データのセッションデータが存在しなければリストへリダイレクト。
        if (!isset($_SESSION[self::SES_EDATA])) {
            return $this->_redirect("priceplan/list");
        }

        $this->view->assign('data', $_SESSION[self::SES_EDATA]);

        unset($_SESSION[self::SES_EDATA]);

        return $this->view;
	}

    /**
     * 加盟店料金プラン一覧を表示
     */
    public function listAction()
    {
        $mdl = new TablePricePlan($this->app->dbAdapter);
        $this->view->assign('list', $mdl->getAll());

        return $this->view;
    }

	/**
	 * 入力検証処理
	 *
	 * @access protected
	 * @param array $data
	 * @return array
	 */
	protected function validate($data = array())
	{
        $errors = array();

        $errorTmp = '<font color="red">%s</font>';

        // PricePlanName: 料金プラン名
        $key = 'PricePlanName';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = sprintf($errorTmp, "<br />料金プラン名は必須です");
        }
        if (!isset($errors[$key]) && !(strlen($data[$key]) <= 160)) {
            $errors[$key] = sprintf($errorTmp, "<br />料金プラン名は160文字以内で入力してください");
        }

        // MonthlyFee: 月額固定費
        $key = 'MonthlyFee';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = sprintf($errorTmp, "<br />月額固定費は必須です");
        }
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = sprintf($errorTmp, "<br />月額固定費が数値ではありません");
        }

        // SettlementAmountLimit: 決済上限額
        $key = 'SettlementAmountLimit';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = sprintf($errorTmp, "<br />決済上限額は必須です");
        }
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = sprintf($errorTmp, "<br />決済上限額が数値ではありません");
        }

        // SettlementFeeRate: 決済手数料率
        $key = 'SettlementFeeRate';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = sprintf($errorTmp, "<br />決済手数料率は必須です");
        }
        if (!isset($errors[$key]) && !((float)$data[$key] > 0.0)) {
            $errors[$key] = sprintf($errorTmp, "<br />決済手数料率の指定が不正です");
        }

        // ClaimFeeBS: 請求手数料（別送）
        $key = 'ClaimFeeBS';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = sprintf($errorTmp, "<br />請求手数料（別送）は必須です");
        }
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = sprintf($errorTmp, "<br />請求手数料（別送）が数値ではありません");
        }

        // ClaimFeeDK: 請求手数料（同梱）
        $key = 'ClaimFeeDK';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = sprintf($errorTmp, "<br />請求手数料（同梱）は必須です");
        }
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = sprintf($errorTmp, "<br />請求手数料（同梱）が数値ではありません");
        }

        // ReClaimFee: 再請求手数料
        $key = 'ReClaimFee';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = sprintf($errorTmp, "<br />再請求手数料は必須です");
        }
        if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
            $errors[$key] = sprintf($errorTmp, "<br />再請求手数料が数値ではありません");
        }

        return $errors;
	}
}

