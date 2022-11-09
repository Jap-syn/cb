<?php
namespace cbadmin\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use cbadmin\Application;
use Zend\Db\Adapter\Adapter;
use models\Table\TableCode;

class OpAuthorityController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

	const SES_UPDATE = 'authority_update';

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

        $this->setPageTitle("後払い.com - 権限マスタ");
	}

	/**
	 * 権限マスタ一覧を表示
	 */
	public function listAction()
	{
	    if (isset($_SESSION[self::SES_UPDATE]))
	    {
	       unset($_SESSION[self::SES_UPDATE]);
	       $msg = $this->params()->fromRoute('msg', 'none');

    	    if ($msg == "u")
    	    {
    	        $this->view->assign("message", "既に存在する権限名です。");
    	    }
    	    if ($msg == "e")
    	    {
    	        $this->view->assign("message", "登録内容を更新しました。" . date("Y/m/d H:i:s") );
    	    }
	    }

        // 権限マスタデータの取得
$sql = <<<EOQ
SELECT  KeyCode     AS AuthorityId
      , KeyContent  AS AuthorityName
      , Class1      AS AuthorityClass
      , ValidFlg
FROM   M_Code
WHERE  CodeId = 52
EOQ;

        $datas = $this->app->dbAdapter->query($sql)->execute();
	    $array = ResultInterfaceToArray($datas);

	    $this->view->assign('list',$array);
	    $this->view->assign("msg","");
	    return $this->view;
	}

	/**
	 * 権限マスタを保存
	 */
	public function saveAction(){
	    //POSTデータ取得
	    $eData = $this->getRequest()->getPost()->toArray();

        $code = new TableCode($this->app->dbAdapter);
	    // 権限マスタデータの取得
$sql = <<<EOQ
SELECT  KeyCode
      , KeyContent
      , Class1
FROM   M_Code
WHERE  CodeId = 52
EOQ;
	    $datas = $this->app->dbAdapter->query($sql)->execute();
	    $array = ResultInterfaceToArray($datas);

	    $_SESSION[self::SES_UPDATE] = "updated";

	    $i = 0;
	    //
	    while (isset($eData["AuthorityId" . $i]))
	    {
	        $j = 0;
	        while (isset($eData["AuthorityId" . $j]))
	        {
	            //同じ権限名
	            if($eData["AuthorityName" . $i] == $array[$j]["KeyContent"])
	            {
	                //IDが違う場合
	                if( $eData["AuthorityId" . $i] != $array[$j]["KeyCode"])
	                {
	                    return $this->_redirect("opauthority/list/msg/u");
	                }
	            }

	            $j++;
	        }

	        $i++;
	    }

	    $i = 0;
	    //
	    while (isset($eData["AuthorityId" . $i]))
	    {
	        unset($deliData);
	        $deliData["KeyContent"] = $eData["AuthorityName" . $i];
	        $deliData["Class1"] = $eData["AuthorityClass" . $i];

	        if (array_key_exists("ValidFlg" . $i, $eData) && $eData["ValidFlg" . $i] == "on")
	        {
	            $deliData["ValidFlg"] = 1;
	        }
	        else
	        {
	            $deliData["ValidFlg"] = 0;
	        }

	        // ユーザーIDの取得
	        $mdluser = new \models\Table\TableUser($this->app->dbAdapter);
	        $deliData['UpdateId'] = $mdluser->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

	        //データ更新
	        $code->saveUpdate($deliData, 52, $eData["AuthorityId" . $i]);

	        $i++;
	    }

	    return $this->_redirect("opauthority/list/msg/e");

	}

}

