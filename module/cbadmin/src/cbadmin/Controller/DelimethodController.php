<?php
namespace cbadmin\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use models\Table\TableDeliMethod;
use cbadmin\Application;

class DelimethodController extends CoralControllerAction
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

        $this->addStyleSheet('../css/default02.css');

        $this->setPageTitle("後払い.com - 配送方法設定");
	}

// Del By Takemasa(NDC) 20141210 Stt マジックメソッド廃止
// 	/**
// 	 * 未定義のアクションがコールされた
// 	 */
// 	public function __call($method, $args)
// 	{
// 		// 無条件にlistへinvoke
// 		$this->_forward('list');
// 	}
// Del By Takemasa(NDC) 20141210 End マジックメソッド廃止

	/**
	 * サイト情報を更新
	 */
	public function upAction()
	{
        $eData = $this->getRequest()->getPost()->toArray();

        $mdl = new TableDeliMethod($this->app->dbAdapter);

        $i = 0;

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        // 更新部分
        while (isset($eData["DeliMethodId" . $i]))
        {
            unset($deliData);

            $deliData["DeliMethodName"] = $eData["DeliMethodName" . $i];
            $deliData["DeliMethodNameB"] = $eData["DeliMethodNameB" . $i];
            $deliData["ArrivalConfirmUrl"] = $eData["ArrivalConfirmUrl" . $i];
            $deliData["ValidateRegex"] = $eData["ValidateRegex" . $i];

            if (array_key_exists("EnableCancelFlg" . $i, $eData) && $eData["EnableCancelFlg" . $i] == "on")
            {
                $deliData["EnableCancelFlg"] = 1;
            }
            else
            {
                $deliData["EnableCancelFlg"] = 0;
            }

            if (array_key_exists("ValidFlg" . $i, $eData) && $eData["ValidFlg" . $i] == "on")
            {
                $deliData["ValidFlg"] = 1;
            }
            else
            {
                $deliData["ValidFlg"] = 0;
            }

            // 追加項目(20150318)
            // (表示順)
            $deliData["ListNumber"] = $eData["ListNumber" . $i];
            // (修正依頼メール)
            $deliData["SendMailRequestModifyJournalFlg"] = (array_key_exists("SendMailRequestModifyJournalFlg" . $i, $eData) && $eData["SendMailRequestModifyJournalFlg" . $i] == "on") ? 1 : 0;
            // (伝票番号登録区分)
            $deliData["JournalRegistClass"] = (array_key_exists("JournalRegistClass" . $i, $eData) && $eData["JournalRegistClass" . $i] == "on") ? 1 : 0;
            // (その他)
            $deliData['UpdateId'] = $userId;

            // 更新実行
             $mdl->saveUpdate($deliData, $eData["DeliMethodId" . $i]);

            $i++;
        }

        // 新規登録部分
        // サイト名が指定されていれば新規登録と見なす。
        if ($eData["DeliMethodName"] != "")
        {
            unset($deliData);

            $deliData["DeliMethodName"] = $eData["DeliMethodName"];
            $deliData["DeliMethodNameB"] = $eData["DeliMethodNameB"];
            $deliData["ArrivalConfirmUrl"] = $eData["ArrivalConfirmUrl"];
            $deliData["ValidateRegex"] = $eData["ValidateRegex"];

            if (array_key_exists("EnableCancelFlg", $eData) && $eData["EnableCancelFlg"] == "on")
            {
                $deliData["EnableCancelFlg"] = 1;
            }
            else
            {
                $deliData["EnableCancelFlg"] = 0;
            }

            if (array_key_exists("ValidFlg", $eData) && $eData["ValidFlg"] == "on")
            {
                $deliData["ValidFlg"] = 1;
            }
            else
            {
                $deliData["ValidFlg"] = 0;
            }

            // 追加項目(20150318)
            // (表示順)
            $deliData["ListNumber"] = $eData["ListNumber"];
            // (修正依頼メール)
            $deliData["SendMailRequestModifyJournalFlg"] = (array_key_exists("SendMailRequestModifyJournalFlg", $eData) && $eData["SendMailRequestModifyJournalFlg"] == "on") ? 1 : 0;
            // (伝票番号登録区分)
            $deliData["JournalRegistClass"] = (array_key_exists("JournalRegistClass", $eData) && $eData["JournalRegistClass"] == "on") ? 1 : 0;
            // (その他)
            $deliData['RegistId'] = $userId;
            $deliData['UpdateId'] = $userId;

             $mdl->saveNew($deliData);
        }

        return $this->_redirect("delimethod/list/msg/u");
	}

	/**
	 * 配送方法一覧を表示
	 */
	public function listAction()
	{
        $msg = $this->params()->fromRoute('msg', 'none');

        if ($msg == "u")
        {
            $this->view->assign("message", sprintf('<font color="red"><b>更新しました。　%s</b></font>', date("Y-m-d H:i:s")));
        }

        $mdl = new TableDeliMethod($this->app->dbAdapter);
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

        // 配送方法データの取得
        $datas = $mdl->getAll();

        $this->view->assign('codeMaster', $codeMaster);
        $this->view->assign('list', $datas);

        return $this->view;
	}
}

