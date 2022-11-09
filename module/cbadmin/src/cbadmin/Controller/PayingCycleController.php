<?php
namespace cbadmin\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use models\Table\TablePayingCycle;
use cbadmin\Application;
use Zend\Db\Adapter\Adapter;
use Coral\Base\BaseHtmlUtils;
use Coral\Coral\CoralCodeMaster;

class PayingCycleController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

	/**
	 * アプリケーションオブジェクト
	 * @var Application
	 */
	private $app;

	/**
	 * Controllerを初期化する
	 */
	public function _init()
	{
        $this->app = Application::getInstance();
        $userInfo = $this->app->authManagerAdmin->getUserInfo();
        $this->view->assign('userInfo', $userInfo );

        $this->addStyleSheet('../css/default02.css')
        ->addJavaScript( '../js/prototype.js' );

        $this->setPageTitle("後払い.com - 立替サイクル管理");
	}

	/**
	 * 加盟店立替サイクル一覧を表示
	 */
	public function listAction()
	{
	    $pay = new TablePayingCycle($this->app->dbAdapter);

	    //加盟店立替サイクルデータを取得する。
	    $datas = $pay->findAll();
	    $array = ResultInterfaceToArray($datas);
	    $code = new \models\Table\TableCode($this->app->dbAdapter);
	    $this->view->assign('list',$array);
	    $this->view->assign('code',$code);
	    return $this->view;
	}

	/**
	 * registAction
	 * 立替サイクルデータ登録フォーム
	 */
	public function registAction() {
	    $rels = $this->params()->fromPost('rels', array());
	    if(!isset($rels['PayingCycleName'])){
	        $rels['FixPattern'] = -1;
	        $rels['PayingDecisionClass'] = 0;
	        $rels['PayingDecisionDay'] = -1;
	        $rels['PayingDay'] = -1;
	        $rels['PayingDecisionDate1'] = 0;
	        $rels['PayingDecisionDate2'] = 0;
	        $rels['PayingDecisionDate3'] = 0;
	        $rels['PayingDecisionDate4'] = 0;
	        $rels['PayingMonth'] = 0;
	    }
		$this->setTemplate('edit');

        // 締めパターン
        $obj = new CoralCodeMaster($this->app->dbAdapter);
        $this->view->assign('fixPatternTag', BaseHtmlUtils::SelectTag("rels[FixPattern]", $obj->getMasterCodes(2, array(-1 => '－')), $rels['FixPattern']));

        // 立替確定日日付指定
        $this->view->assign('payingDecisionDate1', BaseHtmlUtils::SelectTag("rels[PayingDecisionDate1]", $obj->getMasterCodes(76, array(0 => '--')), $rels['PayingDecisionDate1']));
        $this->view->assign('payingDecisionDate2', BaseHtmlUtils::SelectTag("rels[PayingDecisionDate2]", $obj->getMasterCodes(76, array(0 => '--')), $rels['PayingDecisionDate2']));
        $this->view->assign('payingDecisionDate3', BaseHtmlUtils::SelectTag("rels[PayingDecisionDate3]", $obj->getMasterCodes(76, array(0 => '--')), $rels['PayingDecisionDate3']));
        $this->view->assign('payingDecisionDate4', BaseHtmlUtils::SelectTag("rels[PayingDecisionDate4]", $obj->getMasterCodes(76, array(0 => '--')), $rels['PayingDecisionDate4']));

        // 立替日翌月
        $this->view->assign('payingMonth', BaseHtmlUtils::SelectTag("rels[PayingMonth]", $obj->getMasterCodes(77, array(0 => '--')), $rels['PayingMonth']));

		$this->view->assign('rels', $rels);
		return $this->view;
	}

	/**
	 * editAction
	 * 立替サイクルデータ編集フォーム
	 */
	public function editAction() {

	    $params = $this->getParams();

	    $rels = isset($params['rels']) ? $params['rels'] : array();
	    $payingcycle = isset($params['id']) ? $params['id'] : -1;

	    $relations = new TablePayingCycle($this->app->dbAdapter);
	    if (empty($rels) && ($payingcycle > 0)) {
    	    $rels = $relations->find($payingcycle)->current();
	    }
	    $code = new \models\Table\TableCode($this->app->dbAdapter);

	    $this->view->assign('payingcycle', $payingcycle);

        // 締めパターン
        $obj = new CoralCodeMaster($this->app->dbAdapter);
        $this->view->assign('fixPatternTag', BaseHtmlUtils::SelectTag("rels[FixPattern]", $obj->getMasterCodes(2, array(-1 => '－')), $rels['FixPattern']));

        // 立替確定日日付指定
        $this->view->assign('payingDecisionDate1', BaseHtmlUtils::SelectTag("rels[PayingDecisionDate1]", $obj->getMasterCodes(76, array(0 => '--')), $rels['PayingDecisionDate1']));
        $this->view->assign('payingDecisionDate2', BaseHtmlUtils::SelectTag("rels[PayingDecisionDate2]", $obj->getMasterCodes(76, array(0 => '--')), $rels['PayingDecisionDate2']));
        $this->view->assign('payingDecisionDate3', BaseHtmlUtils::SelectTag("rels[PayingDecisionDate3]", $obj->getMasterCodes(76, array(0 => '--')), $rels['PayingDecisionDate3']));
        $this->view->assign('payingDecisionDate4', BaseHtmlUtils::SelectTag("rels[PayingDecisionDate4]", $obj->getMasterCodes(76, array(0 => '--')), $rels['PayingDecisionDate4']));

        // 立替日翌月
        $this->view->assign('payingMonth', BaseHtmlUtils::SelectTag("rels[PayingMonth]", $obj->getMasterCodes(77, array(0 => '--')), $rels['PayingMonth']));

	    $this->view->assign('rels', $rels);
	    $this->view->assign('code',$code);

	    return $this->view;
	}

	/**
	 * confirmAction
	 * 立替サイクルデータ登録確認
	 */
	public function confirmAction() {

	    $rels = $this->params()->fromPost('rels', array());

	    $code = new \models\Table\TableCode($this->app->dbAdapter);

	    // 締めパターン
	    $obj = new CoralCodeMaster($this->app->dbAdapter);
	    $this->view->assign('fixPatternTag', BaseHtmlUtils::SelectTag("rels[FixPattern]", $obj->getMasterCodes(2, array(-1 => '－')), $rels['FixPattern']));

	    // 立替確定日日付指定
	    $this->view->assign('payingDecisionDate1', BaseHtmlUtils::SelectTag("rels[PayingDecisionDate1]", $obj->getMasterCodes(76, array(0 => '--')), $rels['PayingDecisionDate1']));
	    $this->view->assign('payingDecisionDate2', BaseHtmlUtils::SelectTag("rels[PayingDecisionDate2]", $obj->getMasterCodes(76, array(0 => '--')), $rels['PayingDecisionDate2']));
	    $this->view->assign('payingDecisionDate3', BaseHtmlUtils::SelectTag("rels[PayingDecisionDate3]", $obj->getMasterCodes(76, array(0 => '--')), $rels['PayingDecisionDate3']));
	    $this->view->assign('payingDecisionDate4', BaseHtmlUtils::SelectTag("rels[PayingDecisionDate4]", $obj->getMasterCodes(76, array(0 => '--')), $rels['PayingDecisionDate4']));

	    // 立替日翌月
	    $this->view->assign('payingMonth', BaseHtmlUtils::SelectTag("rels[PayingMonth]", $obj->getMasterCodes(77, array(0 => '--')), $rels['PayingMonth']));

	    // 先にビューへアサインしておく
	    $this->view->assign('rels', $rels);
	    $this->view->assign('code',$code);
	    // 検証実行
	    $errors = $this->validate($rels);
	    $this->view->assign('errorMessages', $errors);

		// count関数対策
	    if(!empty($errors)) {
	        $this->setTemplate('edit');
	    }
	    return $this->view;
	}

	/**
	 * saveAction
	 * 新規・編集の永続化処理
	 */
	public function saveAction() {
	    $pay = new TablePayingCycle($this->app->dbAdapter);

	    $rels = $this->params()->fromPost('rels', array());
	    $row = array();

	    // 登録日を設定
	    if( empty($rels['RegistDate']) ) $rels['RegistDate'] = date('Y-m-d H:i:s');
	    // サービス開始日も設定
	    if( empty($rels['ServiceInDate']) ) $rels['ServiceInDate'] = date('Y-m-d H:i:s');

	    // ユーザーIDの取得
	    $userInfo = $this->app->authManagerAdmin->getUserInfo();
	    $userId = $userInfo->OpId;

	    $rels['UserId'] = $userId;
	    $id = $rels['PayingCycleId'];

	    // PayingDecisionDate1～4について、[0]は[null]に置き換えて登録
        if ($rels['PayingDecisionDate1'] == '0') { $rels['PayingDecisionDate1'] = null; }
        if ($rels['PayingDecisionDate2'] == '0') { $rels['PayingDecisionDate2'] = null; }
        if ($rels['PayingDecisionDate3'] == '0') { $rels['PayingDecisionDate3'] = null; }
        if ($rels['PayingDecisionDate4'] == '0') { $rels['PayingDecisionDate4'] = null; }

	    // 永続化実行
	    if($id > 0){
	        $ri = $pay->saveUpdate($rels, $id);
	    }else{
	        $id = $pay->saveNew($rels);
	    }
	    // 詳細ページへリダイレクト
	    return $this->_redirect( "payingcycle/detail/id/$id" );
	}

	/**
	 * detailAction
	 * 立替サイクルデータ情報詳細表示
	 */
	public function detailAction() {
	    $relations = new TablePayingCycle($this->app->dbAdapter);

	    $payingcycle = $this->params()->fromRoute('id', -1);
	    $rels = $relations->find($payingcycle)->current();
	    $code = new \models\Table\TableCode($this->app->dbAdapter);
	    // 先にビューへアサインしておく
	    $this->view->assign('rels', $rels);
	    $this->view->assign('code',$code);
	    return $this->view;
	}

	/**
	 * 立替サイクルデータ登録/変更フォームの内容を検証する
	 * @param array $data 登録フォームデータ
	 * @return array エラーメッセージの配列
	 */
	protected function validate($data = array()) {

	    $errors = array();

	    //PayingCycleName: 立替サイクル/名称
	    $Key = 'PayingCycleName';

	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
	        $errors[$Key] = "立替サイクル/名称は必須です";
	    }

	    //ListNumber: 表示順
	    $Key = 'ListNumber';
	    if(!isset($errors[$Key]) && (strlen ($data[$Key]) <= 0)){
	        $errors[$Key] = "表示順は必須です";
	    }
	    if (!isset($errors[$Key]) && !(is_numeric($data[$Key]))) {
	        $errors[$Key] = "表示順の指定が不正です";
	    }

	    //FixPattern: 締めP
	    $Key = 'FixPattern';
	    if(!isset($errors[$Key]) && ($data[$Key] == "-1")){
	        $errors[$Key] = "締めＰは必須です";
	    }

	    //PayingDecisionClass: 立替サイクル種別
	    //PayingDecisionDay: 立替確定日/毎週
	    $Key1 = 'PayingDecisionClass';
	    $Key2 = 'PayingDecisionDay';

	    if(($data[$Key1] == 0) && ($data[$Key2] == "") ){
	        $errors[$Key] = "立替サイクルが毎週指定の場合、立替確定日/毎週は必須です";
	    }

	    //PayingDecisionClass: 立替サイクル種別
	    //PayingDecisionDate1: 立替確定日－日付指定１
	    //PayingDecisionDate2: 立替確定日－日付指定２
	    //PayingDecisionDate3: 立替確定日－日付指定３
	    //PayingDecisionDate4: 立替確定日－日付指定４
	    $Key1 = 'PayingDecisionClass';
	    $Key2 = 'PayingDecisionDate1';
	    $Key3 = 'PayingDecisionDate2';
	    $Key4 = 'PayingDecisionDate3';
	    $Key5 = 'PayingDecisionDate4';
	    if(($data[$Key1] == "1") && ($data[$Key2] == "0") && ($data[$Key3] == "0") && ($data[$Key4] == "0") && ($data[$Key5] == "0")){
	        $errors[$Key] = "立替サイクルが日付指定の場合、立替確定日/日付指定は必須です";
	    }
	    //PayingClass: 立替日－種別
	    //PayingDay: 立替日/毎週
	    $Key1 = 'PayingClass';
	    $Key2 = 'PayingDay';
	    if(($data[$Key1] == "0") && ($data[$Key2] == "")){
	        $errors[$Key] = "立替日が翌週指定の場合、立替確定日/翌週は必須です";
	    }
	    //PayingClass: 立替日－種別
	    //PayingMonth: 立替日/翌月－翌々月
	    $Key1 = 'PayingClass';
	    $Key2 = 'PayingMonth';
	    if(($data[$Key1] == "1")  && ($data[$Key2] == "0")){
	        $errors[$Key] = "立替日が翌月指定の場合、立替確定日/翌月－翌々月は必須です";
	    }

	    //PayingClass: 立替日－種別
	    //PayingMonth: 立替日/翌月－翌々月
	    $Key1 = 'PayingClass';
	    $Key2 = 'PayingMonth';
	    if(($data[$Key1] == "2")  && ($data[$Key2] == "0")){
	        $errors[$Key] = "立替日が翌々月指定の場合、立替確定日/翌月－翌々月は必須です";
	    }

	    // 加盟店に紐づいている場合は変更不可
	    $Key = 'PayingCycleId';
	    if (strlen($data[$Key]) > 0) {
	    $sql = <<<EOQ
SELECT COUNT(*) AS cnt
FROM   T_Enterprise
WHERE  (   PayingCycleId = :PayingCycleId
        OR N_PayingCycleId = :PayingCycleId
	   )
EOQ;
	       $ri = $this->app->dbAdapter->query($sql)->execute(array(':PayingCycleId' => $data[$Key]))->current();

	       if ($ri['cnt'] > 0) {
	           $errors[$Key] = "加盟店で使用済みの立替サイクルは変更できません";
	       }
	    }

	    return $errors;
	}

}

