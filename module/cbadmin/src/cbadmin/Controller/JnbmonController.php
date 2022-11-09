<?php
namespace cbadmin\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use cbadmin\Application;
use models\Logic\Jnb\LogicJnbConfig;
use models\Logic\Jnb\Account\LogicJnbAccountReceipt;
use models\Table\TableThreadPool;
use models\Table\TableJnbPaymentNotification;

/**
 * JNB関連の各種機能をモニタリング／オペレーションする
 */
class JnbmonController extends CoralControllerAction {
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
        $this->addJavaScript('../js/bytefx_scroll_patch.js');
        $this->addJavaScript('../js/corelib.js');
        $this->addJavaScript('../js/base.ui.js');

        $this->setPageTitle('後払い.com - JNB自動入金実行状況');

        $this->view->assign('current_action', $this->getActionName());
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->jnbConfig = new LogicJnbConfig($this->app->dbAdapter);
	}

	/**
	 * indexAction
	 * jnbmon/autorcptへフォワード
	 */
	public function indexAction()
	{
        $this->_forward('autorcpt');
	}

    /**
     * autorcptAction
     * 自動入金プロセスモニタ
     */
    public function autorcptAction()
    {
        $thTable = new TableThreadPool($this->app->dbAdapter);

        $grp = LogicJnbAccountReceipt::BATCH_THREAD_GROUP_NAME;
        $ri = $thTable->fetchRunningItems($grp, 'desc');
        $runnings = ResultInterfaceToArray($ri);

        $ri = $thTable->fetchItemsByStatus($grp, TableThreadPool::STATUS_TERMINATED_NORMALLY, 'desc');
        $closed = ResultInterfaceToArray($ri);

        $abend_count = $thTable->countItemsByStatus($grp, TableThreadPool::STATUS_TERMINATED_ABNORMALLY);

        $this->view->assign('running_threads', $runnings);
        $this->view->assign('closed_threads', $closed);
        $this->view->assign('abend_count', $abend_count);

        return $this->view;
    }

	/**
	 * stopAction
	 * 自動入金プロセス停止要求
	 */
    public function stopAction()
    {
        $params = $this->getParams();

        $th = (isset($params['th'])) ? $params['th'] : -1;

        $sql = " UPDATE T_ThreadPool SET Status = :Status WHERE ThreadId = :ThreadId ";
        $this->app->dbAdapter->query($sql)->execute(array(':Status' => TableThreadPool::STATUS_TERMINATED_NORMALLY, ':ThreadId' => $th));

        return $this->_redirect('jnbmon/autorcpt');
    }

	/**
	 * clearAction
	 * 停止済み自動入金プロセスのスレッドログを削除
	 */
    public function clearAction()
    {
        $params = $this->getParams();

        $th = (isset($params['th'])) ? $params['th'] : -1;

        $thTable = new TableThreadPool($this->app->dbAdapter);
        if ($thTable->find($th)->count() > 0) {
            $sql = " DELETE FROM T_ThreadPool WHERE ThreadId = :ThreadId ";
            $this->app->dbAdapter->query($sql)->execute(array(':ThreadId' => $th));
        }

        return $this->_redirect('jnbmon/autorcpt');
    }

	/**
	 * notificationsAction
	 * 日付指定による全通知履歴を表示
	 */
	public function notificationsAction()
	{
        $params = $this->getParams();

        // 日付指定の整備
        $today = date('Y-m-d');
        $date = (isset($params['date'])) ? $params['date'] : $today;
        if(date('Ymd', strtotime($date)) != mb_ereg_replace('[^\d]', '', $date)) {
            $date = $today;
        }
        $date = date('Y-m-d', strtotime($date));

        // ページ指定の整備
        $ipp = 100;
        $page = (isset($params['page'])) ? $params['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $ipp;

        $tbl = new TableJnbPaymentNotification($this->app->dbAdapter);
        $total = $tbl->countByDate($date);
        $ri = $tbl->findByDate($date, 'desc', $ipp, $offset);
        $count = $ri->count();
        $list = ResultInterfaceToArray($ri);

        $this->view->assign('list', $list);
        $this->view->assign('count', $count);
        $this->view->assign('total', $total);
        $this->view->assign('page', $page);
        $this->view->assign('date', $date);
        $this->view->assign('start', $offset + 1);
        $this->view->assign('end', $offset + $count);

        return $this->view;
	}

	public function nfl3mAction()
	{
        $tbl = new TableJnbPaymentNotification($this->app->dbAdapter);

        $year = date('Y');
        $month = date('m');

        $list = array();
        for($i = 0; $i < 3; $i++) {
            $list[] = $tbl->countByDateInYearMonth($year, $month - $i);
        }

        $this->view->assign('list', $list);

        return $this->view;
	}
}
