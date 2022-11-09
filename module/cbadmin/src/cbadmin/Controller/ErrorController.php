<?php
namespace cbadmin\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use cbadmin\Application;

class ErrorController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

	/**
	 * ErrorController を初期化します。
	 */
	public function _init() {
		$this->addStyleSheet('../css/default02.css');
		$this->addJavaScript('../js/prototype.js');
		$this->setPageTitle("後払い.com - システムエラー");
		$this->view->assign('userInfo', Application::getInstance()->authManagerAdmin->getUserInfo());
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
