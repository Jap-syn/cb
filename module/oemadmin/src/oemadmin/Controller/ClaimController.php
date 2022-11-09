<?php
namespace oemadmin\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use oemadmin\Application;
use models\Table\TableOemBadDebt;
use Coral\Base\BaseHtmlUtils;

class ClaimController extends CoralControllerAction
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
		$this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

		$this->addStyleSheet($this->app->getOemCss())
		->addStyleSheet('../../oemadmin/css/monthly.css')
		->addJavaScript('../../js/prototype.js');

		$this->setPageTitle($this->app->getOemServiceName()." - 債権明細");
	}

	/**
	 * 債権明細閲覧
	 */
	public function indexAction()
	{
        $userInfo = $this->app->authManagerAdmin->getUserInfo();

        $oem_id = $userInfo->OemId;

        //日付が指定されていない場合は先月のデータ取得
        $from_date = empty($this->getParams()['fd']) ? date ('Y-m-01', strtotime("-1 month")) : $this->getParams()['fd'];

        $mdlobd = new TableOemBadDebt($this->app->dbAdapter);

        $oemBadDebtData = $mdlobd->getOemBadDebt($from_date, $oem_id)->current();

        //空の場合初期化しておく
        if (!$oemBadDebtData) {
            $oemBadDebtData = array("FcSpanFrom"=>"", "FcSpanTo"=>"", "SpanFrom"=>"", "SpanTo"=>"", "ClaimCount"=>null,
                                    "ClaimAmount"=>null, "ReceiptMoneyCount"=>null, "ReceiptMoneyAmount"=>null, "BadDebtCount"=>null, "BadDebtAmount"=>null);
        }

        $this->_createMonthList();

        $this->view->assign('oem_bad_debit', $oemBadDebtData);

        return $this->view;
	}

	/**
	 * 年月選択リスト作成
	 */
	protected function _createMonthList() {

		$today = date('Y-m-d');

		$fixedDate = empty($this->getParams()['fd']) ? date ('Y-m-01', strtotime("-1 month")) : $this->getParams()['fd'];

		$fixedMonthTag = BaseHtmlUtils::SelectTag(
			'fd',
			array(
			    date ('Y-m-01') => date ('Y年m月度'),
			    date ('Y-m-01', strtotime("-1 month")) => date ('Y年m月度', strtotime("-1 month")),
		        date ('Y-m-01', strtotime("-2 month")) => date ('Y年m月度', strtotime("-2 month")),
		        date ('Y-m-01', strtotime("-3 month")) => date ('Y年m月度', strtotime("-3 month")),
		        date ('Y-m-01', strtotime("-4 month")) => date ('Y年m月度', strtotime("-4 month")),
		        date ('Y-m-01', strtotime("-5 month")) => date ('Y年m月度', strtotime("-5 month")),
		        date ('Y-m-01', strtotime("-6 month")) => date ('Y年m月度', strtotime("-6 month")),
		        date ('Y-m-01', strtotime("-7 month")) => date ('Y年m月度', strtotime("-7 month")),
		        date ('Y-m-01', strtotime("-8 month")) => date ('Y年m月度', strtotime("-8 month")),
		        date ('Y-m-01', strtotime("-9 month")) => date ('Y年m月度', strtotime("-9 month")),
		        date ('Y-m-01', strtotime("-10 month")) => date ('Y年m月度', strtotime("-10 month")),
		        date ('Y-m-01', strtotime("-11 month")) => date ('Y年m月度', strtotime("-11 month"))
			),
			$fixedDate
		);
		$this->view->assign('fixedMonthTag', $fixedMonthTag);
	}

}

