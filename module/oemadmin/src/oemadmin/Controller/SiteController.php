<?php
namespace oemadmin\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use models\Table\TableEnterprise;
use models\Table\TableSite;
use oemadmin\Application;

class SiteController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

	const SES_EID        = "site_EidForSiteMainte";
	const SES_ERROR      = "site_ErrorMessage";

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
		$this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

		$this->addStyleSheet($this->app->getOemCss())
			->addJavaScript( '../../js/prototype.js' );

		$this->setPageTitle($this->app->getOemServiceName()." - 事業者管理");
	}

	/**
	 * サイト一覧を表示
	 */
	public function listAction()
	{
        $enterpriseId = $this->params()->fromRoute("eid", -1);

        $this->view->assign("eid", $enterpriseId);

		$_SESSION[self::SES_EID] = $enterpriseId;		// 処理対象のEnterpriseIdをセット

		$mdlSite = new TableSite($this->app->dbAdapter);
		$mdlEnterprise = new TableEnterprise($this->app->dbAdapter);
		$codeMaster = new CoralCodeMaster($this->app->dbAdapter);

		// 事業者名の取得・アサイン
		$enterpriseData = $mdlEnterprise->findEnterprise($enterpriseId)->current();
		$this->view->assign("EnterpriseNameKj", $enterpriseData["EnterpriseNameKj"]);

        if($enterpriseData["OemId"] != $this->app->authManagerAdmin->getUserInfo()->OemId){
            $this->_redirect("error/nop");
        }

        // 事業者の請求取りまとめモードの取得・アサイン
		$this->view->assign("CombinedClaimMode", $enterpriseData["CombinedClaimMode"]);

		// サイトデータの取得
		$datas = $mdlSite->getAll($enterpriseId);

		$this->view->assign('list', $datas);
		$this->view->assign('codeMaster', $codeMaster);
		$this->view->assign('error', $_SESSION[self::SES_ERROR]);

		unset($_SESSION[self::SES_ERROR]);

		return $this->view;
	}
}

