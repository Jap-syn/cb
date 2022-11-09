<?php
namespace oemadmin\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use oemadmin\Application;

class ErrorController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

	/**
	 * ErrorController を初期化します。
	 */
	public function _init() {
		$this->app = Application::getInstance();
		$this->addStyleSheet(Application::getInstance()->getOemCss());
		$this->setPageTitle($this->app->getOemServiceName()." - システムエラー");
		$this->addJavaScript('../../js/prototype.js');
		$this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());
	}

	/**
	 * ErrorController の既定のアクションです
	 */
	public function errorAction() {
		// TODO: Errorアクションを実装してください。
		// ヒント：デフォルトでは application/views/scripts/error/error.phtml を
		//         利用してレスポンスがレンダリングされます
	}

	/**
	 * Permission Denied によるエラー
	 */
	public function nopAction()
	{
        return $this->view;
	}
}
