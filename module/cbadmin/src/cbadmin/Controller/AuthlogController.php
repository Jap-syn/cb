<?php
namespace cbadmin\Controller;

use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Coral\Coral\Controller\CoralControllerAction;
use cbadmin\Application;

/**
 * 認証試行ログ閲覧コントローラ
 */
class AuthlogController extends CoralControllerAction {
    /**
     * ログ閲覧時の1ページあたりのログ数
     * @var int
     */
    const LOGBROWSE_ITEMS_PER_PAGE = 100;

	protected $_componentRoot = './application/views/components';
    /**
     * アプリケーションインスタンス
     *
     * @access protected
     * @var Application
     */
    protected $app;

	/**
	 * コントローラ初期化
	 */
	protected function _init() {

        $this->app = Application::getInstance();

        $this->addStyleSheet('../css/default02.css');
        $this->addStyleSheet('../css/cbadmin/authlog/main.css');
        $this->addJavaScript('../js/prototype.js');
        $this->addJavaScript('../js/json.js');
        $this->addJavaScript('../js/corelib.js');
        $this->addJavaScript('../js/bytefx.js');
        $this->addJavaScript('../js/bytefx_scroll_patch.js');
        $this->addJavaScript('../js/base.ui.js');

        $this->setPageTitle('後払い.com - 認証試行ログ');

        $this->view->assign('current_action', $this->getActionName());
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());
        $this->view->assign('errors', $this->getFlashMessenger()->getMessages());
	}

	/**
	 * indexAction
	 * rawへフォワード
	 */
	public function indexAction() {
	    return $this->_redirect('authlog/raw');
	}

    /**
     * リクエストパラメータからページ指定を抽出する
     *
     * @access protected
     * @return int
     */
    protected function getPageFromParam() {
        $params = $this->getParams();
        $page = (isset($params['page'])) ? $params['page'] : 1;
        if($page < 1) $page = 1;
        return $page;
    }

    /**
     * リクエストパラメータから対象日付指定を抽出する
     *
     * @access protected
     * @return null | string 指定されていた場合はY-m-d形式の日付文字列。未指定時はnull
     */
    protected function getDateFromParam() {
        $params = $this->getParams();
        $date = (isset($params['date'])) ? $params['date'] : '';
        if(!strlen($date)) return null;
        if(preg_replace('/[^\d]/', '', $date) == f_df($date, 'Ymd')) {
            return f_df($date, 'Y-m-d');
        } else {
            return null;
        }
    }

    /**
     * 指定日付によるログ抽出フィルタを生成する
     *
     * @access protected
     * @package null | string $date 日付指定。省略時はgetDateFromParam()でリクエストパラメータからの抽出を試みる
     * @param null | boolean $nest $dateがnullだった場合にgetDateFromParam()を経由した再帰呼び出しを行うかのフラグ。省略時はtrue
     * @return string | null ログの抽出対象を絞り込む日付ベースのフィルタ
     */
    protected function getDateFilter($date = null, $nest = true) {
        if(!$date) {
            return $nest ?
                $this->getDateFilter($this->getDateFromParam(), false) : null;
        }

        //Ex:" LogTime >= '2015-06-15' AND LogTime <= '2015-06-15 23:59:59' "
        return " LogTime >= '" . $date . "' AND LogTime <= '" . f_df($date, 'Y-m-d 23:59:59') . "' ";
    }

    /**
     * rawAction
     * 認証ログを閲覧する（1ページ最大200件）
     *
     * パラメータ：
     * ・page → 1～、省略時は1
     * ・date → yyyy-MM-dd、省略時は日付による絞り込みなし
     *
     * 出力：
     * （T_AuthenticationLogのすべてのカラム）
     */
    public function rawAction() {

        $page = $this->getPageFromParam();
        $date = $this->getDateFromParam();

        if($date) {
            $filter = $this->getDateFilter($date);
            $this->view->assign('date', $date);
        } else {
            $filter = null;
        }

        $list = $this->fetchLogs($filter, $page);
        $count_info = $this->countLogs($filter);
        $start = (($page - 1) * self::LOGBROWSE_ITEMS_PER_PAGE) + 1;

        // count関数対策
        $list_count = 0;
        if(!empty($list)){
            $list_count = count($list);
        }
        $end = $start + $list_count - 1;

        $this->view->assign('mode', 'raw');
        $this->view->assign('page', $page);
        $this->view->assign('total', $count_info['count']);
        $this->view->assign('start', $start);
        $this->view->assign('end', $end);
        $this->view->assign('max_page', $count_info['max_page']);
        $this->view->assign('logs', $list);

        return $this->view;
    }

    /**
     * 指定ログインIDの認証ログを閲覧する（1ページ最大200件）
     *
     * パラメータ：
     * ・loginid → ログインID。指定必須
     * ・page → 1～、省略時は1
     * ・date → yyyy-MM-dd、省略時は日付による絞り込みなし
     *
     * 出力：
     * （T_AuthenticationLogのすべてのカラム）
     */
    public function byidAction() {

        $params = $this->getParams();

        $page = $this->getPageFromParam();
        $date = $this->getDateFromParam();

        $this->view->assign('mode', 'id');
        $this->view->assign('page', $page);

        $id = $params['loginid'];
        if(!$id) {
            $this->view->assign('error', 'ログインIDが指定されていません');
        }
        else {
            $filter = (" LoginId = '" . $id . "' ");
            if($date) {
                $filter = join(' AND ', array($filter, $this->getDateFilter($date)));
                $this->view->assign('date', $date);
            }
            $list = $this->fetchLogs($filter, $page);
            $count_info = $this->countLogs($filter);
            $start = (($page - 1) * self::LOGBROWSE_ITEMS_PER_PAGE) + 1;

            // count関数対策
            $list_count = 0;
            if(!empty($list)){
                $list_count = count($list);
            }
            $end = $start + $list_count - 1;

            $this->view->assign('loginid', $id);
            $this->view->assign('total', $count_info['count']);
            $this->view->assign('start', $start);
            $this->view->assign('end', $end);
            $this->view->assign('max_page', $count_info['max_page']);
            $this->view->assign('logs', $list);
        }

        $this->setTemplate('raw');
        return $this->view;
    }

    /**
     * 指定IPアドレスの認証ログを閲覧する（1ページ最大200件）
     *
     * パラメータ：
     * ・ip → IPアドレス。指定必須
     * ・page → 1～、省略時は1
     * ・date → yyyy-MM-dd、省略時は日付による絞り込みなし
     *
     * 出力：
     * （T_AuthenticationLogのすべてのカラム）
     */
    public function byipAction() {

        $params = $this->getParams();

        $page = $this->getPageFromParam();
        $date = $this->getDateFromParam();

        $this->view->assign('mode', 'ip');
        $this->view->assign('page', $page);

        $ip = $params['ip'];
        if(!$ip) {
            $this->view->assign('error', 'IPアドレスが指定されていません');
        }
        else {
            $filter = (" IpAddress = '" . $ip . "' ");
            if($date) {
                $filter = join(' AND ', array($filter, $this->getDateFilter($date)));
                $this->view->assign('date', $date);
            }
            $list = $this->fetchLogs($filter, $page);
            $count_info = $this->countLogs($filter);
            $start = (($page - 1) * self::LOGBROWSE_ITEMS_PER_PAGE) + 1;

            // count関数対策
            $list_count = 0;
            if(!empty($list)){
                $list_count = count($list);
            }
            $end = $start + $list_count - 1;

            $this->view->assign('ip', $ip);
            $this->view->assign('total', $count_info['count']);
            $this->view->assign('start', $start);
            $this->view->assign('end', $end);
            $this->view->assign('max_page', $count_info['max_page']);
            $this->view->assign('logs', $list);
        }

        $this->setTemplate('raw');
        return $this->view;
    }

    /**
     * 指定クライアントハッシュの認証ログを閲覧する（1ページ最大200件）
     *
     * パラメータ：
     * ・hash → クライアントハッシュ。指定必須
     * ・page → 1～、省略時は1
     * ・date → yyyy-MM-dd、省略時は日付による絞り込みなし
     *
     * 出力：
     * （T_AuthenticationLogのすべてのカラム）
     */
    public function byhashAction() {

        $params = $this->getParams();

        $page = $this->getPageFromParam();
        $date = $this->getDateFromParam();

        $this->view->assign('mode', 'hash');
        $this->view->assign('page', $page);

        $hash = $params['hash'];
        if(!$hash) {
            $this->view->assign('error', 'クライアント識別子が指定されていません');
        }
        else {
            $filter = (" ClientHash = '" . $hash . "' ");
            if($date) {
                $filter = join(' AND ', array($filter, $this->getDateFilter($date)));
                $this->view->assign('date', $date);
            }
            $list = $this->fetchLogs($filter, $page);
            $count_info = $this->countLogs($filter);
            $start = (($page - 1) * self::LOGBROWSE_ITEMS_PER_PAGE) + 1;

            // count関数対策
            $list_count = 0;
            if(!empty($list)){
                $list_count = count($list);
            }
            $end = $start + $list_count - 1;

            $this->view->assign('hash', $hash);
            $this->view->assign('total', $count_info['count']);
            $this->view->assign('start', $start);
            $this->view->assign('end', $end);
            $this->view->assign('max_page', $count_info['max_page']);
            $this->view->assign('logs', $list);
        }

        $this->setTemplate('raw');
        return $this->view;
    }

    /**
     * 指定条件に一致する認証ログを抽出する
     *
     * @access protected
     * @param string | null $filter 抽出条件。T_AuthenticationLogに対する完全なWHEREを指定する
     * @param null | int $page 抽出対象ページ
     * @return array
     */
    protected function fetchLogs($filter = null, $page = 1) {
        if(!$filter) $filter = '1 = 1';

        $ipp = self::LOGBROWSE_ITEMS_PER_PAGE;
        $offset = ($page - 1) * $ipp;

        $sql = " SELECT * FROM T_AuthenticationLog WHERE %s ORDER BY Seq DESC LIMIT %d OFFSET %d ";
        $sql = sprintf($sql, $filter, $ipp, $offset);

        return ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(null));
    }

    /**
     * 指定条件に一致する認証ログの件数を取得する
     *
     * @access protected
     * @param string | null $filter 抽出条件。T_AuthenticationLogに対する完全なWHEREを指定する
     * @return int
     */
    protected function countLogs($filter = null) {
        if(!$filter) $filter = '1 = 1';

        $ipp = self::LOGBROWSE_ITEMS_PER_PAGE;

        $sql = sprintf(" SELECT COUNT(*) AS cnt FROM T_AuthenticationLog WHERE %s ", $filter);
        $count = (int)$this->app->dbAdapter->query($sql)->execute(null)->current()['cnt'];

        return array(
                'count' => $count,
                'max_page' => floor($count / $ipp) + (($count % $ipp) == 0 ? 0 : 1)
        );
    }

    /**
     * rankAction
     * 指定期間中のログインID別認証試行回数ランキング（トップ30）
     *
     * パラメータ：
     * ・span → 期間指定：three（過去3日間） | seven（過去7日間） | thirty（過去30日間）
     *
     * 出力：
     * ・対象期間
     * ・ログインID
     * ・代理認証ID
     * ・IPアドレス
     * ・クライアントハッシュ
     * ・試行回数
     * ・NG回数
     */
    public function rankAction() {
        return $this->_redirect('authlog/raw');
    }

    /**
     * rankbyipAction
     * 指定期間中のIPアドレス別認証試行回数ランキング（トップ30）
     *
     * パラメータ：
     * ・span → 期間指定：three（過去3日間） | seven（過去7日間） | thirty（過去30日間）
     *
     * 出力：
     * ・対象期間
     * ・IPアドレス
     * ・クライアントハッシュ
     * ・ログインID
     * ・代理認証ID
     * ・試行回数
     * ・NG回数
     */
    public function rankbyipAction() {
        return $this->_redirect('authlog/raw');
    }

    /**
     * rankbyhashAction
     * 指定期間中のクライアントハッシュ別認証試行回数ランキング（トップ30）
     *
     * パラメータ：
     * ・span → 期間指定：three（過去3日間） | seven（過去7日間） | thirty（過去30日間）
     *
     * 出力：
     * ・対象期間
     * ・クライアントハッシュ
     * ・IPアドレス
     * ・ログインID
     * ・代理認証ID
     * ・試行回数
     * ・NG回数
     */
    public function rankbyhashAction() {
        return $this->_redirect('authlog/raw');
    }

    /**
     * フラッシュメッセンジャーを取得する
     *
     * @access protected
     * @return FlashMessenger
     */
    protected function getFlashMessenger() {
        return $this->flashMessenger();
    }

    /**
     * 処理時に発生したエラーを処理する
     *
     * @access protected
     * @param \Exception $err 発生した例外
     */
    protected function onActionError(\Exception $err, $methodName) {
        $this->getFlashMessenger()->addMessage(sprintf('以下のエラーが発生しました：　%s', $err->getMessage()));
        $this->app->logger->info(sprintf('[AuthlogController::%s] an error has occured: seq = %s, error = %s',
                                         $methodName, $err->getMessage()));
    }
}
