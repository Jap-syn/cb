<?php
namespace oemadmin\Controller;

use Coral\Base\BaseGeneralUtils;
use Coral\Base\BaseHtmlUtils;
use Coral\Coral\Controller\CoralControllerAction;
use models\Table\TableMailTemplate;
use models\Table\TableOem;
use oemadmin\Application;

class GpController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

	const SES_UPDATE = 'generalpurpose_notice';
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

		$this->setPageTitle($this->app->getOemServiceName()." - 各種マスター管理");
	}

	/**
	 * お知らせ設定フォームの表示
	 */
	public function noticeAction()
	{
		if (isset($_SESSION[self::SES_UPDATE]))
		{
			unset($_SESSION[self::SES_UPDATE]);
			$this->view->assign("updated", sprintf('<font color="red"><b>更新しました。　%s</b></font>', date("Y-m-d H:i:s")));
		}

		$oemMdl = new TableOem($this->app->dbAdapter);
		$info_row = $oemMdl->find( $this->app->authManagerAdmin->getUserInfo()->OemId )->current();
		$notice = $info_row['Notice'];

		$this->view->assign('notice', $notice);
		return $this->view;
	}

	/**
	 * お知らせ設定更新
	 */
	public function upAction()
	{
		// リファラーがなければリダイレクト
		if(!isset($_SERVER['HTTP_REFERER']))
		{
			$this->_redirect("gp/notice");
		}

		$notice = $this->params()->fromPost('notice', '未設定');
		$eData['Notice'] = $notice;
	    $eData['UpdateId'] = $this->app->authManagerAdmin->getUserInfo()->UserId;

		$mdl = new TableOem($this->app->dbAdapter);

		// 更新実行
		$mdl->saveUpdate($eData, $this->app->authManagerAdmin->getUserInfo()->OemId);

		$_SESSION[self::SES_UPDATE] = "updated";
		$this->_redirect("gp/notice");
	}

	/**
	 * メールテンプレート編集フォーム
	 */
	public function mailtfAction()
	{
		if (isset($_SESSION[self::SES_UPDATE]))
		{
			unset($_SESSION[self::SES_UPDATE]);
			$this->view->assign("updated", sprintf('<font color="red"><b>更新しました。　%s</b></font>', date("Y-m-d H:i:s")));
		}

		$prm_get = $this->params()->fromRoute();
		$class  = (isset($prm_get['cls'])) ? $prm_get['cls'] : '1';
		$mdlmt = new TableMailTemplate($this->app->dbAdapter);

		$clsTag = BaseHtmlUtils::SelectTag(
			'cls',
			$mdlmt->getTemplatesArray(),
			$class,
			' onChange="javascript:navi();"'
		);

        $oemClass = $this->app->authManagerAdmin->getUserInfo()->OemId;

        $data = $mdlmt->findMailTemplate($class, $oemClass)->current();

        $mode = $oemClass == 0 ? 'cb' : 'oem';

		$this->view->assign('clsTag', $clsTag);
        $this->view->assign('oemTag', $oemTag);
        $this->view->assign('mode', $mode);
		$this->view->assign('data', $data);

		return $this->view;
	}

	/**
	 * メールテンプレート設定
	 */
	public function mailupAction()
	{
		// リファラーがなければリダイレクト
		if(!isset($_SERVER['HTTP_REFERER']))
		{
			$this->_redirect("gp/mailtf");
		}

		$params = $this->getParams();

		$udata['FromTitle'] = $params['FromTitle'];
		//$udata['FromTitleMime'] = mb_encode_mimeheader($params['FromTitle'], 'UTF-8');
		$udata['FromTitleMime'] = BaseGeneralUtils::toMailCharMime($params['FromTitle']);
		$udata['FromAddress'] = $params['FromAddress'];
		$udata['Subject'] = $params['Subject'];
		$udata['SubjectMime'] = mb_encode_mimeheader($params['FromTitle'], 'UTF-8');
		$udata['Body'] = $params['Body'];
        $udata['Class'] = $params['cls'];
	    $udata['UpdateId'] = $this->app->authManagerAdmin->getUserInfo()->UserId;
	    $udata['ValidFlg'] = 1;

		$mdlmt = new TableMailTemplate($this->app->dbAdapter);

        // IDがなければ新規にレコード作成
        if(is_null($params['Id']) || $params['Id'] == ''){
            $udata['OemId'] = $this->app->authManagerAdmin->getUserInfo()->OemId;
		    $udata['RegistId'] = $this->app->authManagerAdmin->getUserInfo()->UserId;

            $mdlmt->saveNew($udata);

        } else {
            // IDがあれば更新
            $mdlmt->saveUpdate($udata, $params['Id']);
        }
		$_SESSION[self::SES_UPDATE] = "updated";
		$this->_redirect("gp/mailtf/cls/" . $params['cls']);
	}
}

