<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Zend\Db\Adapter\Adapter;
use models\Logic\LogicOemClaimAccount;
use models\Table\TableCvsReceiptAgent;

/**
 * コンビニ収納代行会社設定を管理するコントローラ
 */
class CvsagentController extends CoralControllerAction {
	protected $_componentRoot = './application/views/components';
	/**
	 * アプリケーションインスタンス
	 *
     * @access protected
	 * @var Application
	 */
	protected $app;

    /**
     * 有効なバーコードロジック名とロジッククラスを対応付ける連想配列
     *
     * @access protected
     * @var array
     */
    protected $bcClassMap;

    /**
     * 請求口座ロジック
     *
     * @access protected
     * @var LogicOemClaimAccount
     */
    protected $accountsLogic;

    /**
     * コンビニ収納代行会社マスターモデル
     *
     * @access protected
     * @var TableCvsReceiptAgent
     */
    protected $receiptAgents;

    /**
     * DBアダプタ
     *
     * @access protected
     * @var Adapter
     */
    protected $db;

	/**
	 * コントローラ初期化
	 */
	protected function _init() {
		$app = $this->app = Application::getInstance();
        $db = $this->db = $app->dbAdapter;
		$this->accountsLogic = new LogicOemClaimAccount($db);
		$this->accountsLogic->setLogger($app->logger);

		$this->bcClassMap = LogicOemClaimAccount::getBarcodeLogicClasses();
        $this->receiptAgents = $this->accountsLogic->getReceiptAgentMaster();

		$this->addStyleSheet('../css/default02.css');
		$this->addJavaScript('../js/prototype.js');
		$this->addJavaScript('../js/json+.js');
		$this->addJavaScript('../js/corelib.js');

		$this->setPageTitle('後払い.com - コンビニ収納代行会社管理');

		$this->view->assign('current_action', $this->getActionName());
        $this->view->assign('barcodeClasses', $this->bcClassMap);
		$this->view->assign('master_map', LogicOemClaimAccount::getCodeMap());
        $this->view->assign('mode', 'add');
		$this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

		return $this->view;
	}

    /**
     * listAction
     * コンビニ収納代行会社一覧を表示する
     */
    public function listAction() {
        $this->view->assign('agents', $this->receiptAgents->fetchAllAgents());
        return $this->view;
    }

    /**
     * addAction
     * 新規登録フォームを表示
     */
    public function addAction() {
        // ビュースクリプトはform.phtml
        $this->setTemplate('form');
        return $this->view;
    }

    /**
     * editAction
     * 収納代行会社情報更新フォームを表示
     */
    public function editAction() {

        $req = $this->getParams();
        $aid = (isset($req['aid'])) ? $req['aid'] : -9;
        $agent = $this->receiptAgents->findReceiptAgentId($aid)->current();
        if(!$agent) {
            throw new \Exception(sprintf("収納代行会社ID '%s' は無効な指定です", (isset($req['aid'])) ? $req['aid'] : '(null)'));
        }
        $this->view->assign('data', $agent);
        $this->view->assign('mode', 'edit');
        // ビュースクリプトはform.phtml
        $this->setTemplate('form');

        return $this->view;
    }

    /**
     * saveAction
     * 登録・更新フォームの内容を永続化する
     */
    public function saveAction() {

        $params = $this->getParams();
        $data = (isset($params['form'])) ? $params['form'] : array();

        // 無効フラグの整形
        $data['InvalidFlg'] = (isset($data['InvalidFlg']) && $data['InvalidFlg']) ? 1 : 0;

        // 対象の収納代行会社情報を検索し、動作モードを確定させる
        $aid = (int)$data['ReceiptAgentId'];
        $for_update = $this->receiptAgents->findReceiptAgentId($aid)->current() != null;

        // フォームデータをビューへ割り当てておく
        $this->view->assign('mode', $for_update ? 'edit' : 'add');
        $this->view->assign('data', $data);

        // 入力検証を実行
        $errors = $this->validate($data);
        // count関数対策
        if(!empty($errors)) {
            // 検証エラーあり
            $this->view->assign('error', $errors);
            $this->setTemplate('form');
            return $this->view;
        }

        // 不要カラムのデータを削除
        foreach(array('ReceiptAgentId', 'RegistDate', 'isNew') as $ignoreKey) {
            if(isset($data[$ignoreKey])) {
                unset($data[$ignoreKey]);
            }
        }
        // 有効フラグ　（0：無効　1：有効）
        $data['ValidFlg'] = ($data['InvalidFlg'] == 1) ? 0 : 1;
        unset($data['InvalidFlg']);

        if($for_update) {
            // 更新処理
            $aid = $this->receiptAgents->saveUpdate($data, $aid);
        } else {
            // 登録処理
            $aid = $this->receiptAgents->saveNew($data);
        }

        // 一覧へリダイレクト
        return $this->_redirect('cvsagent/list');
    }

	/**
	 * 入力検証処理
	 *
	 * @access protected
	 * @param array $data
	 * @return array
	 */
	protected function validate($data = array()) {

	    $errors = array();

	    //ReceiptAgentName: 収納代行会社名
	    $Key = 'ReceiptAgentName';
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
	        $errors[$Key] = "'収納代行会社名'は入力必須です";
	    }
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) == 1)){
	        $errors[$Key] = "'収納代行会社名'が短すぎます";
	    }
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) > 50)){
	        $errors[$Key] = "'収納代行会社名'は50文字以内で入力してください";
	    }

	    //ReceiptAgentCode: 収納代行会社固有コード
	    $Key = 'ReceiptAgentCode';
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
	        $errors[$Key] = "'収納代行会社固有コード'は入力必須です";
	    }
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) != 5)){
	        $errors[$Key] = "'収納代行会社固有コード'は半角数字5文字で入力してください";
	    }
	    if (!isset($errors[$Key]) && !IsValidFormatRecCode($data[$Key])) {
	        $errors[$Key] = "'収納代行会社固有コード'は半角数字5文字で入力してください";
	    }

	    //BarcodeLogicName: バーコード生成ロジック名
	    $Key = 'BarcodeLogicName';
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
	        $errors[$Key] = "'バーコード生成ロジック名'は入力必須です";
	    }
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) > 20)){
	        $errors[$Key] = "'バーコード生成ロジック名'は20文字以内で入力してください";
	    }
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) == 1)){
	        $errors[$Key] = "'バーコード生成ロジック名'が短すぎます";
	    }
	    if (!isset($errors[$Key]) && !IsValidFormatBarName($data[$Key])) {
	        $errors[$Key] = "'バーコード生成ロジック名'は半角英数字で入力してください";
	    }

	    //Note: 備考
	    $Key = 'Note';
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) > 20000)){
	        $errors[$Key] = "'備考'は20,000文字以内で入力してください";
	    }

        // BarcodeLogicNameにエラーがなければホワイトリストチェックを行う
        if(!isset($errors['BarcodeLogicName'])) {
            if(!isset($this->bcClassMap[$data['BarcodeLogicName']])) {
                $errors['BarcodeLogicName'] = "'バーコード生成ロジック名'に無効な値が指定されました";
            }
        }

		return $errors;
	}

}
