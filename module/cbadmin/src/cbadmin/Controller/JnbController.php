<?php
namespace cbadmin\Controller;

use Zend\Json\Json;
use Coral\Coral\Controller\CoralControllerAction;
use cbadmin\Application;
use models\Logic\Jnb\LogicJnbConfig;
use models\Table\TableOem;
use models\Table\TableJnb;
use models\Table\TableJnbBranch;
use models\Table\TableJnbAccount;
use models\Table\TableJnbAccountGroup;
use models\Table\TableJnbPaymentNotification;
use models\Logic\Jnb\LogicJnbAccount;

/**
 * JNB契約情報を管理するコントローラ
 */
class JnbController extends CoralControllerAction {
	protected $_componentRoot = './application/views/components';
	/**
	 * アプリケーションインスタンス
	 *
     * @access protected
	 * @var Application
	 */
	protected $app;

	/**
	 * JNB設定
	 *
	 * @access protected
	 * @var LogicJnbConfig
	 */
	protected $jnbConfig;

	/**
	 * フラッシュメッセンジャー
	 *
	 * @access protected
	 * @var FlashMessenger
	 */
	protected $messenger;

	/**
	 * コントローラ初期化
	 */
	protected function _init()
	{
	    $this->app = Application::getInstance();

        $this->addStyleSheet('../css/default02.css');
        $this->addStyleSheet('../css/cbadmin/jnb/main.css');
        $this->addJavaScript('../js/prototype.js');
        $this->addJavaScript('../js/json.js');
        $this->addJavaScript('../js/bytefx.js');
        $this->addJavaScript('../js/corelib.js');
        $this->addJavaScript('../js/base.ui.js');

        $this->jnbConfig = new LogicJnbConfig($this->app->dbAdapter);
        $this->messenger = $this->flashMessenger();
        $this->view->assign('current_action', $this->getActionName());
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());
	}

	/**
	 * indexAction
	 * listActionのエイリアス
	 */
	public function indexAction()
	{
	    return $this->_forward('list');
	}

    /**
     * listAction
     * JNB契約情報一覧
     */
    public function listAction()
    {
        $this->setPageTitle('後払い.com - JNB契約一覧');

        $tbl = new TableJnb($this->app->dbAdapter);
        $grpTable = new TableJnbAccountGroup($this->app->dbAdapter);

        $rows = ResultInterfaceToArray($tbl->fetchSummaries());
        // count関数対策
        $rowsCount = 0;
        if (!empty($rows)) {
            $rowsCount = count($rows);
        }
        for ($i=0; $i<$rowsCount; $i++) {
            $ttl = 0;
            $use = 0;
            $clm = 0;

            foreach($grpTable->getSummaryByJnbId($rows[$i]['JnbId']) as $grp) {
                $ttl += (int)($grp['TotalCount']);
                $use += (int)($grp['UsableCount']);
                $clm += (int)($grp['ClaimingCount']);
            }
            $rows[$i]['TotalCount'] = $ttl;
            $rows[$i]['UsableCount'] = $use;
            $rows[$i]['ClaimingCount'] = $clm;
        }
        $this->view->assign('list',$rows);

        return $this->view;
    }

    /**
     * detailAction
     * JNB契約情報詳細
     */
    public function detailAction()
    {
        $this->setPageTitle('後払い.com - JNB登録内容詳細');

        $params = $this->getParams();

        $oid = (isset($params['oid'])) ? $params['oid'] : -1;

        $jnbTable = new TableJnb($this->app->dbAdapter);
        $grpTable = new TableJnbAccountGroup($this->app->dbAdapter);

        $data = $jnbTable->findSummaryByOemId($oid);
        $groups = $grpTable->getSummaryByOemId($oid);

        $this->view->assign('oid', $oid);
        $this->view->assign('data', $data);
        $this->view->assign('groups', $groups);
        $this->view->assign('allowRestore', $this->jnbConfig->getAllowRestoreReturnedAccounts());

        return $this->view;
    }

    /**
     * newAction
     * JNB契約情報登録
     */
    public function newAction()
    {
        $this->setPageTitle('後払い.com - JNB新規登録');

        $params = $this->getParams();

        $oid = (isset($params['oid'])) ? $params['oid'] : -1;
        $formData = (isset($params['form'])) ? $params['form'] : array();
        if(isset($formData['OemId'])) $oid = $formData['OemId'];

        $jnbTable = new TableJnb($this->app->dbAdapter);
        $config = new LogicJnbConfig($this->app->dbAdapter);

        $defaultData = array_merge(array(
                'BankName' => $this->jnbConfig->getDefaultBankName(),
                'BankCode' => $this->jnbConfig->getDefaultBankCode(),
                'ValidFlg' => 1
        ), $formData);


        $this->view->assign('mode', 'add');
        $this->view->assign('oid', $oid);
        $this->view->assign('oemList', ResultInterfaceToArray($jnbTable->findNotBoundOemInfo()));
        $this->view->assign('data', $defaultData);

        $this->setTemplate('edit');
        return $this->view;
    }

    /**
     * editAction
     * JNB契約情報編集
     */
    public function editAction()
    {
        $this->setPageTitle('後払い.com - JNB登録内容編集');

        $params = $this->getParams();

        $oid = (isset($params['oid'])) ? $params['oid'] : -1;
        $formData = (isset($params['form'])) ? $params['form'] : array();

        $jnbTable = new TableJnb($this->app->dbAdapter);
        $oemTable = new TableOem($this->app->dbAdapter);

        $data = $jnbTable->findSummaryByOemId($oid);
        if(!$data) {
            throw new \Exception(sprintf("OEM ID '%d' は不正な指定です", $oid));
        }
        $data = array_merge($data, $formData);

        $this->view->assign('mode', 'edit');
        $this->view->assign('data', $data);

        return $this->view;
    }

	/**
	 * confirmAction
	 * JNB契約情報登録確認
	 */
    public function confirmAction()
    {
        $this->setPageTitle('後払い.com - JNB登録内容確認');

        $params = $this->getParams();

        $jnbTable = new TableJnb($this->app->dbAdapter);
        $oemTable = new TableOem($this->app->dbAdapter);

        $mode = (isset($params['mode'])) ? $params['mode'] : 'add';
        $data = (isset($params['form'])) ? $params['form'] : array();

        $oid = (int)($data['OemId']);

        $errors = $this->validate($data, $mode == 'add');
        $oemData = $oemTable->findOem($oid);
        if($oemData->count() > 0) {
            $data['OemNameKj'] = $oemData->current()['OemNameKj'];
        }

        $this->view->assign('oid', $oid);
        $this->view->assign('mode', $mode);
        $this->view->assign('oemList', ResultInterfaceToArray($jnbTable->findNotBoundOemInfo()));
        $this->view->assign('error', $errors);
        $this->view->assign('data', $data);
        // count関数対策
        $this->setTemplate(!empty($errors) ? 'edit' : 'confirm');
        return $this->view;
    }

    /**
     * saveAction
     * JNB契約情報永続化処理
     */
    public function saveAction()
    {
        $params = $this->getParams();

        $mode = (isset($params['mode'])) ? $params['mode'] : 'add';
        $data = (isset($params['form'])) ? $params['form'] : array();

        $oid = (int)($data['OemId']);

        $jnbTable = new TableJnb($this->app->dbAdapter);
        if($mode == 'add') {
            $jnbTable->saveNew($data);
        } else {
            $jnbTable->saveUpdateByOemId($data, $oid);
        }

        return $this->_redirect(sprintf('jnb/done/oid/%s', $oid));
    }

	/**
	 * doneAction
	 * JNB契約情報完了
	 */
	public function doneAction()
	{
        $this->setPageTitle('後払い.com - JNB登録完了');

        $params = $this->getParams();

        $oid = (isset($params['oid'])) ? $params['oid'] : -1;

        $jnbTable = new TableJnb($this->app->dbAdapter);
        $data = $jnbTable->findSummaryByOemId($oid);


        $this->view->assign('oid', $oid);
        $this->view->assign('data', $data);

        return $this->view;
	}

	/**
	 * brAction
	 * 支店マスターメンテナンス
	 */
	public function brAction()
	{
        $this->setPageTitle('後払い.com - JNB支店マスター管理');

        $master = new TableJnbBranch($this->app->dbAdapter);
        $this->view->assign('master', $master->getAllBranchInfo());

        $infos = array(
                'error' => array(),
                'info' => array()
        );

        try {
            // 更新情報などがFlashMessenger経由で搬送されてきたら復元する
            foreach($this->messenger->getMessages() as $data) {
                $infos = Json::decode($data, Json::TYPE_ARRAY);
                break;
            }
        } catch(\Exception $err) {}

        $this->view->assign('errors', $infos['error']);
        $this->view->assign('infos', $infos['info']);

        return $this->view;
	}

	/**
	 * brupAction
	 * 支店マスター更新
	 */
	public function brupAction()
	{
        $params = (isset($this->getParams()['br'])) ? $this->getParams()['br'] : array();

        $master = new TableJnbBranch($this->app->dbAdapter);
        $map = $master->getAllBranchInfo();

        $new_data = isset($params['new']) ? $params['new'] : null;
        unset($params['new']);

        $infos = array(
                'error' => array(),
                'info' => array()
        );

        // 新規登録データがあったら登録を試行
        if($new_data && isset($new_data['add']) && $new_data['add']) {
            $code = $new_data['code'];
            $name = $new_data['name'];
            try {
                $master->addBranchName($code, $name);
                // 更新情報を追加
                $infos['info']['新規登録'] = sprintf('支店コード = %s、支店名 = %sで新規登録しました', $code, $name);
            } catch(\Exception $err) {
                // エラー情報を追加
                $infos['error']['新規登録'] = sprintf('新規登録エラー：支店コード = %s、支店名 = %s、エラー = %s', $code, $name, $err->getMessage());
            }
        }

        // 更新または削除の試行
        foreach($params as $code => $data) {
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
            $is_del = isset($data['delete']);
            $mode = $is_del ? '削除' : '更新';
            $executed = false;
            try {
                if($is_del) {
                    // 削除を試行
                    $master->removeBranchName($code);
                    $executed = true;
                } else {
                    // 内容が変更されている場合のみ更新を試行
                    if(isset($map[$code]) && $map[$code]['name'] != $data['name']) {
                        $master->modifyBranchName($code, $data['name']);
                        $executed = true;
                    }
                }
                $this->app->dbAdapter->getDriver()->getConnection()->commit();
                if($executed) {
                    // 更新情報を追加
                    $infos['info'][sprintf('支店%s', $code)] = sprintf('%sしました', $mode);
                }
            } catch(\Exception $err) {
                $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
                // エラー情報を追加
                $infos['error'][sprintf('支店%s', $code)] = sprintf('%sエラー -> %s', $mode, $err->getMessage());
            }
        }

        // 更新情報・エラー情報をFlashMessenger経由でリダイレクト先に搬送
        $this->messenger->addMessage(Json::encode($infos));

        // メンテ画面へリダイレクト
        return $this->_redirect('jnb/br');
	}

	/**
	 * historyAction
	 * 口座履歴
	 */
	public function historyAction()
	{
        $params = $this->getParams();

        $accountNumber = isset($params['account']) ? $params['account'] : '000-0000000';
        $accSeq = isset($params['accseq']) ? $params['accseq'] : -1;

        $this->view->assign('accSeq', $accSeq);
        $this->view->assign('accNum', $accountNumber);

        $this->addJavaScript('../js/json_format.js');
        $this->setPageTitle('後払い.com - JNB口座利用履歴');

        $tbl = new TableJnbAccount($this->app->dbAdapter);
        $mst = new TableJnbBranch($this->app->dbAdapter);
        $this->view->assign('branchMap', $mst->getAllBranchInfo());

        if($accSeq > -1) {
            $this->view->assign('bySeq', true);
            $account = $tbl->find($accSeq)->current();
        }
        else {
            $this->view->assign('bySeq', false);
            list($br, $ac) = explode('-', $accountNumber);
            $account = $tbl->findAccount($br, $ac);
        }

        if($account) {
            $grpTable = new TableJnbAccountGroup($this->app->dbAdapter);
            $nfTable = new TableJnbPaymentNotification($this->app->dbAdapter);

            $this->view->assign('account', $account);
            $this->view->assign('group', $grpTable->getGroupDetail($account['AccountGroupId']));
            $this->view->assign('accNum', sprintf('%s-%s', $account['BranchCode'], $account['AccountNumber']));
            $this->view->assign('list', ResultInterfaceToArray($tbl->findUsageHistories($account['AccountSeq'])));
            $this->view->assign('nfHistory', ResultInterfaceToArray($nfTable->findByAccountSeq($account['AccountSeq'], 'desc')));
        }

        return $this->view;
	}

	/**
	 * erraccountsAction
	 * 複数注文に不当に割り当てられている可能性があるJNB口座一覧
	 */
	public function erraccountsAction()
	{
        $this->setPageTitle('後払い.com - JNB口座重複割り当て一覧');

        $logic = new LogicJnbAccount($this->app->dbAdapter);

        $this->view->assign('invalid_jnb_accounts', $logic->findMultiOpenedAccounts());

        return $this->view;
	}

	protected function validate($data = array(), $is_new = false)
	{
        $errors = array();

        $jnbTable = new TableJnb($this->app->dbAdapter);
        $validOemIds = array();
        if($is_new) {
            foreach($jnbTable->findNotBoundOemInfo() as $row) {
                $validOemIds[] = (int)($row['OemId']);
            }
        } else {
            $validOemIds[] = (int)($data['OemId']);
        }

        // OemId: OEM ID
        $key = 'OemId';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("'OEM先'は必須です");
        }
        if (!isset($errors[$key]) && !(in_array($data[$key], $validOemIds))) {
            $errors[$key] = array("'OEM先'の指定が不正です");
        }

        // DisplayName: 名称
        $key = 'DisplayName';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("'名称'は必須です");
        }

        // Memo: メモ (チェック不要の明示)

        // BankName: 銀行名
        $key = 'BankName';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("'銀行名'は必須です");
        }

        // BankCode: 銀行コード
        $key = 'BankCode';
        if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
            $errors[$key] = array("'銀行コード'は必須です");
        }
        if (!isset($errors[$key]) && !(strlen($data[$key]) == 4) && !(is_numeric($data[$key]))) {
            $errors[$key] = array("'銀行コード'は半角数字4文字で入力してください");
        }

        return $errors;
	}
}
