<?php
namespace cbadmin\Controller;

use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\Auth\CoralAuthAttemptJudge;
use Coral\Coral\Auth\AttemptJudge\CoralAuthAttemptJudgeSetting;
use cbadmin\Application;

/**
 * 認証ロックアウトを管理する
 */
class IdlockController extends CoralControllerAction {
	protected $_componentRoot = './application/views/components';
	/**
	 * アプリケーションインスタンス
	 *
     * @access protected
	 * @var Application
	 */
	protected $app;

    /**
     * クライアントレベルロックアウト設定
     *
     * @access protected
     * @var CoralAuthAttemptJudgeSetting
     */
    protected $_clLockSetting;

    /**
     * IDレベルロックアウト設定
     *
     * @access protected
     * @var CoralAuthAttemptJudgeSetting
     */
    protected $_idLockSetting;

	/**
	 * コントローラ初期化
	 */
	protected function _init() {

        $this->app = Application::getInstance();

        $this->addStyleSheet('../css/default02.css');
        $this->addStyleSheet('../css/cbadmin/idlock/main.css');
        $this->addJavaScript('../js/prototype.js');
        $this->addJavaScript('../js/json.js');
        $this->addJavaScript('../js/corelib.js');

        $this->setPageTitle('後払い.com - 認証ロックアウト管理');

        $this->view->assign('current_action', $this->getActionName());
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());
        $this->view->assign('errors', $this->getFlashMessenger()->getMessages());

        $this->_clLockSetting = CoralAuthAttemptJudgeSetting::createClientLevelSetting($this->app->dbAdapter);
        $this->_idLockSetting = CoralAuthAttemptJudgeSetting::createIdLevelSetting($this->app->dbAdapter);
	}

	/**
	 * indexAction
	 * idlistへフォワード
	 */
	public function indexAction() {
        $this->_forward('idlist');
	}

    /**
     * idlistAction
     * IDレベルロックアウト一覧を表示
     */
    public function idlistAction() {

        $config = $this->getAppInfoConfig();
        $list = array();
        foreach($config as $key => $conf) {
            $list[$key] = $this->_idLockSetting->getEnabled() ?
                $this->getAttemptJudgeLogic($conf['app'])->findIdLevelLockoutInfo() :
                array();
        }

        $this->view->assign('level', 'id');
        $this->view->assign('list', $list);
        $this->view->assign('apps', $config);
        $this->view->assign('id_lockout_enabled', $this->_idLockSetting->getEnabled());
        $this->view->assign('cl_lockout_enabled', $this->_clLockSetting->getEnabled());
        $this->view->assign('lockout_time_enabled', $this->_idLockSetting->isLockoutTimeAvailable());

        $this->setTemplate('list');
        return $this->view;
    }

    /**
     * cllistAction
     * クライアントレベルロックアウト一覧を表示
     */
    public function cllistAction() {

        $config = $this->getAppInfoConfig();
        $list = array();
        foreach($config as $key => $conf) {
            $list[$key] = $this->_clLockSetting->getEnabled() ?
                $this->getAttemptJudgeLogic($conf['app'])->findClientLevelLockoutInfo() :
                array();
        }

        $this->view->assign('level', 'client');
        $this->view->assign('list', $list);
        $this->view->assign('apps', $config);
        $this->view->assign('id_lockout_enabled', $this->_idLockSetting->getEnabled());
        $this->view->assign('cl_lockout_enabled', $this->_clLockSetting->getEnabled());
        $this->view->assign('lockout_time_enabled', $this->_clLockSetting->isLockoutTimeAvailable());

        $this->setTemplate('list');
        return $this->view;
    }

    /**
     * idlockreleaseAction
     * IDレベルロックアウトを解除
     */
    public function idlockreleaseAction() {

        $params = $this->getParams();
        $seq = (isset($params['seq'])) ? $params['seq'] : -1;

        try {
            $log_row = $this->fetchAuthenticationLog($seq, true);
            if(!$log_row) {
                throw new \Exception('ロックアウト情報が見つかりません。すでに解除済みかもしれません');
            }
            $logic = $this->getAttemptJudgeLogic($log_row['TargetApp']);
            $logic->releaseIdLevelLockInfo($log_row['LoginId']);
        }
        catch(\Exception $err) {
            $this->onLockoutReleaseError($err, 'idlockreleaseAction', $seq);
        }

        return $this->_redirect('idlock/idlist');
    }

    /**
     * cllockreleaseAction
     * クライアントレベルロックアウトを解除
     */
    public function cllockreleaseAction() {

        $params = $this->getParams();
        $seq = (isset($params['seq'])) ? $params['seq'] : -1;

        try {
            $log_row = $this->fetchAuthenticationLog($seq, false);
            if(!$log_row) {
                throw new \Exception('ロックアウト情報が見つかりません。すでに解除済みかもしれません');
            }
            $logic = $this->getAttemptJudgeLogic($log_row['TargetApp']);
            $logic->releaseClientLevelLockByClientHash($log_row['LoginId'], $log_row['AltLoginId'], $log_row['ClientHash']);
        }
        catch(\Exception $err) {
            $this->onLockoutReleaseError($err, 'cllockreleaseAction', $seq);
        }

        return $this->_redirect('idlock/cllist');
    }

    /**
     * 指定SEQのロックアウト情報を取得する
     *
     * @access protected
     * @param int $seq 認証ログSEQ
     * @param boolean $is_id_level IDレベルロックアウト情報を取得する場合はtrue、クライアントレベルの場合はfalseを指定
     * @return array | null
     */
    protected function fetchAuthenticationLog($seq, $is_id_level) {

        $logType = CoralAuthAttemptJudge::LOGTYPE_LOCKED;
        $resultCode = $is_id_level ? CoralAuthAttemptJudge::RESULT_LOCK_ID  : CoralAuthAttemptJudge::RESULT_LOCK_CLIENT;

        $sql = " SELECT * FROM T_AuthenticationLog WHERE Seq = :Seq AND LogType = :LogType AND Result = :Result AND DeleteFlg = 0 ";
        return $this->app->dbAdapter->query($sql)->execute(array(':Seq' => (int)$seq, ':LogType' => $logType, ':Result' => $resultCode))->current();
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
     * 指定アプリケーション向けの認証試行管理ロジックを生成する
     *
     * @access protected
     * @param int $app アプリケーション種別。0：cbadmin、1：member、2：oemadmin、3：oemmember
     * @return CoralAuthAttemptJudge
     */
    protected function getAttemptJudgeLogic($app) {
        $logic = new CoralAuthAttemptJudge($this->app->dbAdapter, $app);

        $logic->setClientLevelLockoutSetting($this->_clLockSetting);
        $logic->setIdLevelLockoutSetting($this->_idLockSetting);

        return $logic;
    }

    /**
     * ロックアウト情報に関する、アプリケーション別の定義設定一覧を取得する
     *
     * @access protected
     * return array
     */
    protected function getAppInfoConfig() {
        return array(
            'cbadmin' => array(
                               'app' => CoralAuthAttemptJudge::APP_CBADMIN,
                               'label' => 'キャッチボールオペレータ'),

            'oemadmin' => array(
                                'app' => CoralAuthAttemptJudge::APP_OEMADMIN,
                                'label' => 'OEM先オペレータ'),

            'member' => array(
                              'app' => CoralAuthAttemptJudge::APP_MEMBER,
                              'label' => '事業者（直収）'),

            'oemmember' => array(
                                 'app' => CoralAuthAttemptJudge::APP_OEMMEMBER,
                                 'label' => '事業者（OEM先）')
        );
    }

    /**
     * ロックアウト解放処理時に発生したエラーを処理する
     *
     * @access protected
     * @param \Exception $err 発生した例外
     */
    protected function onLockoutReleaseError(\Exception $err, $methodName, $seq) {
        $this->getFlashMessenger()->addMessage(sprintf('以下の理由でロックを解除できませんでした：　%s', $err->getMessage()));
        $this->app->logger->info(sprintf('[IdlockController::%s] lockout release error: seq = %s, error = %s',
                                         $methodName, $seq, $err->getMessage()));
    }
}
