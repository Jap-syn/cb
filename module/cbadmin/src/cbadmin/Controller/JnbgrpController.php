<?php
namespace cbadmin\Controller;

use Zend\Json\Json;
use Coral\Coral\Controller\CoralControllerAction;
use cbadmin\Application;
use models\Logic\Jnb\LogicJnbConfig;
use models\Table\TableJnb;
use models\Table\TableJnbAccount;
use models\Table\TableJnbAccountGroup;
use models\Table\TableJnbAccountImportWork;
use models\Logic\Jnb\Account\LogicJnbAccountImporter;

/**
 * JNB口座グループおよび口座を管理するコントローラ
 */
class JnbgrpController extends CoralControllerAction {
	protected $_componentRoot = './application/views/components';
	/**
	 * アプリケーションインスタンス
	 *
     * @access protected
	 * @var Application
	 */
	protected $app;

	/**
	 * インポート一時テーブル
	 *
	 * @access protected
	 * @var TableJnbAccountImportWork
	 */
	protected $_workTable;

	/**
	 * JNB口座テーブル
	 *
	 * @access protected
	 * @var TableJnbAccount
	 */
	protected $_accTable;

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

        $this->view->assign('current_action', $this->getActionName());
        $this->jnbConfig = new LogicJnbConfig($this->app->dbAdapter);
	}

	/**
	 * indexAction
	 * jnb/listへリダイレクト
	 */
	public function indexAction()
	{
        return $this->_redirect('jnb/list');
	}

    /**
     * impAction
     * JNB口座インポート画面
     */
    public function impAction()
    {
        $this->setPageTitle('後払い.com - JNB口座インポート');

        $params = $this->getParams();

        $oid = (isset($params['oid'])) ? $params['oid'] : -1;

        // 指定OEMのサマリを取得
        $data = $this->_getJnbSummary($oid);
        if(!$data) {
            // OEM指定が不正な場合はnoOemをセットしておく
            $this->view->assign('noOem', true);
        } else {
            $this->view->assign('noOem', false);
            $this->view->assign('data', $data);
        }

        return $this->view;
    }

	/**
	 * confirmAction
	 * JNB口座インポート確認
	 */
    public function confirmAction()
    {
        $this->setPageTitle('後払い.com - JNB口座インポート');

        ini_set( 'max_execution_time', 0 );		// 処理タイムアウトはしない

        $params = $this->getParams();

        // 指定OEMのサマリを取得
        $oid = (isset($params['oid'])) ? $params['oid'] : -1;
        $data = $this->_getJnbSummary($oid);
        if(!$data) {
            // OEM指定が不正な場合はインポート画面へ戻す
            $this->view->assign('noOem', true);
            $this->setTemplate('imp');
            return $this->view;
        }
        $this->view->assign('data', $data);

        /** インポートロジック @var LogicJnbAccountImporter */
        $logic = new LogicJnbAccountImporter($this->app->dbAdapter, $oid);
        $logic->setOperatorId($this->app->authManagerAdmin->getUserInfo()->OpId);	// CSV読み込み時に必要なのでオペレータIDを付与
        // CSV読み込み実行
        try {
            $result = $logic->loadCsvFile($_FILES['jnbcsv']['tmp_name']);
            $this->view->assign('menuHide', true);
            $this->view->assign('tranId', $result['key']);
            $this->view->assign('count', array(
                    'ok' => $result['success'],
                    'ng' => $result['error'],
                    'skip' => $result['skip']
            ));
        } catch(\Exception $err) {
            $this->view->assign('error', sprintf('以下のエラーが発生しました：　%s', $err->getMessage()));
        }

        return $this->view;
    }

	/**
	 * continueAction
	 * CSV読み込み後に処理を中断した、インポート実行の継続画面を表示
	 */
	public function continueAction()
	{
        $this->setPageTitle('後払い.com - JNB口座インポート');

        $params = $this->getParams();

        // 指定OEMのサマリを取得
        $oid = (isset($params['oid'])) ? $params['oid'] : -1;
        $data = $this->_getJnbSummary($oid);
        if(!$data) {
            // OEM指定が不正な場合はインポート画面へ戻す
            $this->view->assign('noOem', true);
            $this->setTemplate('imp');
            return $this->view;
        }
        $this->view->assign('data', $data);

        $tranId = (isset($params['tid'])) ? $params['tid'] : 'INVALID-TRANSACTION-KEY';

        /** インポートロジック @var LogicJnbAccountImporter */
        $logic = new LogicJnbAccountImporter($this->app->dbAdapter, $oid);
        try {
            $result = $logic->reportPreImportSummary($tranId);
            $this->view->assign('menuHide', true);
            $this->view->assign('tranId', $result['ProcessKey']);
            $this->view->assign('count', array(
                    'ok' => $result['SuccessCount'],
                    'ng' => $result['ErrorCount'],
                    'skip' => 0
            ));
        } catch(\Exception $err) {
            $this->view->assign('error', sprintf('以下のエラーが発生しました：　%s', $err->getMessage()));
        }

        $this->setTemplate('confirm');
        return $this->view;
	}

    /**
     * saveAction
     * JNB口座インポート処理
     */
    public function saveAction()
    {
        $this->setPageTitle('後払い.com - JNB口座インポート');

        $params = $this->getParams();

        // トランザクション情報を復元
        $tranProp = null;
        $tranHash = $params['transaction'];
        if(strlen($tranHash)) {
            $tranProp = Json::decode(base64_decode($tranHash), Json::TYPE_ARRAY);
        }
        if(!$tranProp) {
            // リクエストにトランザクション情報が設定されていない場合は
            // パラメータから初期化する
            $tranProp = array(
                    'oemId'         => isset($params['oid']) ? $params['oid'] : -1,
                    'transactionId' => $params['tid'],
                    'groupId'       => $params['gid'],
                    'depositClass'  => isset($params['depo']) ? $params['depo'] : 0,
                    'startTime'     => isset($params['st']) ? $params['st'] : strtotime(date('Y-m-d H:i:s')),
                    'processed'     => isset($params['processed']) ? $params['processed'] : 0
            );
        }

        $oid = $tranProp['oemId'];
        $tranId = $tranProp['transactionId'];

        // JNB契約サマリーを取得
        $data = $this->_getJnbSummary($oid);
        if(!$data) {
            throw new \Exception(sprintf("OEM ID '%d' は不正な指定です", $oid));
        }

        /** インポートロジック @var LogicJnbAccountImporter */
        $logic = new LogicJnbAccountImporter($this->app->dbAdapter, $oid);
        $logic->setDepositClass($tranProp['depositClass']);		// 口座種別はここで設定
        $result = $logic->execImport($tranId, 2500, $tranProp['groupId']);

        // 口座グループID、処理件数を書き戻す
        $tranProp['groupId'] = $result['groupId'];
        $tranProp['processed'] += $result['processed'];

        if($result['remain'] > 0) {
            // 残があるので進行画面を表示
            $this->view->assign('data', $data);
            $this->view->assign('tranProp', $tranProp);
            $this->view->assign('remain', $result['remain']);
            $this->view->assign('totalError', $result['totalError']);
            $this->view->assign('menuHide', true);

            $this->setTemplate('progress');
            return $this->view;
        }
        else {
            $grpRow = $logic->getJnbGroupTable()->find($tranProp['groupId'])->current();
            // インポートが完了したのでリダイレクト
            $stateParams = array_merge($tranProp, array(
                    'processed' => $tranProp['processed'],
                    'imported'  => $grpRow['TotalAccounts'],
                    'errors'    => $result['totalError'],
                    'elapsed'   => strtotime(date('Y-m-d H:i:s')) - $tranProp['startTime']
            ));
            $_SESSION['JNBGRP_SAVE_STATE'] = $stateParams;  // 完了画面へリダイレクトするため、セッションへデータを退避する
            return $this->_redirect('jnbgrp/done');
        }

        return $this->view;
    }

	/**
	 * doneAction
	 * JNB口座インポート完了
	 */
	public function doneAction()
	{
        $this->setPageTitle('後払い.com - JNB口座インポート');

        $params = array();
        if (isset($_SESSION['JNBGRP_SAVE_STATE'])) {
            $params = $_SESSION['JNBGRP_SAVE_STATE'];
            unset($_SESSION['JNBGRP_SAVE_STATE']);
        }
        $oid = isset($params['oemId']) ? $params['oemId'] : -1;
        $gid = isset($params['groupId']) ? $params['groupId'] : -1;
        $data = $this->_getJnbSummary($oid);
        $this->view->assign('data', $data);
        $this->view->assign('state', $params);

        $grpTable = new TableJnbAccountGroup($this->app->dbAdapter);
        $ri = $grpTable->find($gid);
        foreach($ri as $grpRow) {
            if ($grpRow['TotalAccounts'] == 0) {
                // インポート件数が0の場合はそのグループを削除する
                $sql = " DELETE FROM T_JnbAccountGroup WHERE AccountGroupId = :AccountGroupId ";
                $this->app->dbAdapter->query($sql)->execute(array(':AccountGroupId' => $grpRow['AccountGroupId']));
            }
        }

        // インポートに成功した一時データを削除
        // count関数対策
        if(!empty($params)) {
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
            try {
                $sql = " DELETE FROM T_JnbAccountImportWork WHERE ProcessKey = :ProcessKey AND IFNULL(DeleteFlg, 0) = 1 AND ImportError IS NULL ";
                $this->app->dbAdapter->query($sql)->execute(array(':ProcessKey' => $params['transactionId']));
                $this->app->dbAdapter->getDriver()->getConnection()->commit();
            }
            catch(\Exception $err) {
                $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            }
        }

        return $this->view;
	}

    /**
     * cancelAction
     * JNB口座インポートキャンセル
     */
    public function cancelAction()
    {
        $this->setPageTitle('後払い.com - JNB口座インポート');

        $params = $this->getParams();

        $oid = isset($params['oid']) ? $params['oid'] : -1;
        $tranId = isset($params['tid']) ? $params['tid'] : 'INVALID-TRANSACTION-KEY';

        /** インポートロジック @var LogicJnbAccountImporter */
        $logic = new LogicJnbAccountImporter($this->app->dbAdapter, $oid);
        $logic->cancelImport($tranId);

        return $this->_redirect(sprintf('jnbgrp/canceldone/oid/%d', $oid));
    }

    /**
     * canceldoneAction
     * JNB口座インポートキャンセル完了
     */
    public function canceldoneAction()
    {
        $this->setPageTitle('後払い.com - JNB口座インポート');

        $params = $this->getParams();
        $oid = isset($params['oid']) ? $params['oid'] : -1;

        $this->view->assign('data', $this->_getJnbSummary($oid));

        return $this->view;
    }

	/**
	 * csverrAction
	 * JNB口座インポート CSVエラー詳細
	 */
	public function csverrAction()
	{
        $this->setPageTitle('後払い.com - JNB口座インポート');

        $params = $this->getParams();

        $oid = isset($params['oid']) ? $params['oid'] : -1;
        $tranId = isset($params['tid']) ? $params['tid'] : 'INVALID-TRANSACTION-KEY';

        $page = isset($params['page']) ? $params['page'] : 1;
        if($page < 1) $page = 1;

        /** インポートロジック @var LogicJnbAccountImporter */
        $logic = new LogicJnbAccountImporter($this->app->dbAdapter, $oid);
        $ipp = 500;

        $list = $logic->reportCsvErrors($tranId, $page, $ipp);
        $totalCount = $logic->countCsvErrors($tranId);
        $maxPage = ceil($totalCount / $ipp);
        if(!$maxPage) $maxPage = 1;
        if($page > $maxPage) $page = $maxPage;

        $this->view->assign('oid', $oid);
        $this->view->assign('tranId', $tranId);
        $this->view->assign('page', $page);
        $this->view->assign('list', ResultInterfaceToArray($list));
        $this->view->assign('totalCount', $totalCount);
        $this->view->assign('maxPage', $maxPage);
        $this->view->assign('ipp', $ipp);
        $this->view->assign('colInfo', $logic->getImportCsvSchema());

        return $this->view;
	}

	/**
	 * csverrclearAction
	 * JNB口座インポート CSVエラークリア
	 */
	public function csverrclearAction()
	{
        $this->setPageTitle('後払い.com - JNB口座インポート');

        $params = $this->getParams();

        $oid = isset($params['oid']) ? $params['oid'] : -1;
        $data = $this->_getJnbSummary($oid);
        if(!$data) {
            // OEM指定が不正な場合はnoOemをセットしておく
            $this->view->assign('noOem', true);
        } else {
            $this->view->assign('noOem', false);
            $this->view->assign('data', $data);
        }
        $tranId = isset($params['tid']) ? $params['tid'] : 'INVALID-TRANSACTION-KEY';

        /** インポートロジック @var LogicJnbAccountImporter */
        $logic = new LogicJnbAccountImporter($this->app->dbAdapter, $oid);
        $preCount = $logic->countCsvErrors($tranId);
        $logic->clearCsvErrors($tranId);
        $postCount = $logic->countCsvErrors($tranId);

        $this->view->assign('oid', $oid);
        $this->view->assign('tranId', $tranId);
        $this->view->assign('preCount', $preCount);
        $this->view->assign('postCount', $postCount);

        return $this->view;
	}

	/**
	 * errdetailAction
	 * JNB口座インポートエラー詳細
	 */
	public function errdetailAction()
	{
        $this->setPageTitle('後払い.com - JNB口座インポート');

        $params = $this->getParams();

        $oid = isset($params['oid']) ? $params['oid'] : -1;
        $this->view->assign('data', $this->_getJnbSummary($oid));

        $tid = $params['tid'];
        if($tid) {
            /** インポートロジック @var LogicJnbAccountImporter */
            $logic = new LogicJnbAccountImporter($this->app->dbAdapter, $oid);
            $this->view->assign('transactionId', $tid);
            $this->view->assign('errors', ResultInterfaceToArray($logic->reportErrors($tid)));
        }

        return $this->view;
	}

	/**
	 * errclearAction
	 * JNB口座インポートエラークリア
	 */
	public function errclearAction()
	{
        $params = $this->getParams();

        $oid = isset($params['oid']) ? $params['oid'] : -1;
        $tid = $params['tid'];
        $data = $this->_getJnbSummary($oid);
        if($tid) {
            /** インポートロジック @var LogicJnbAccountImporter */
            $logic = new LogicJnbAccountImporter($this->app->dbAdapter, $oid);
            $logic->clearImportErrors($tid);
        }

        $url = 'jnb/index';
        if($data) {
            $url = sprintf('jnb/detail/oid/%s', $data['OemId']);
        }
        return $this->_redirect($url);
	}

	/**
	 * detailAction
	 * JNB口座グループ詳細
	 */
	public function detailAction()
	{
        $this->setPageTitle('後払い.com - JNB口座グループ詳細');

        $params = $this->getParams();

        $ipp = 500;

        $grpTable = new TableJnbAccountGroup($this->app->dbAdapter);
        $accTable = new TableJnbAccount($this->app->dbAdapter);
        $jnbConfig = new LogicJnbConfig($this->app->dbAdapter);

        $groupId = isset($params['gid']) ? $params['gid'] : -1;
        $grp = $grpTable->getGroupDetail($groupId);
        $validStatuses = TableJnbAccount::getAvailableStatuses();
        if($grp) {

            $page = isset($params['page']) ? $params['page'] : 1;
            $page = is_numeric($page) ? $page : 1;
            if($page < 1) $page = 1;
            if($page < 1) $page = 1;
            $filters = array();

            $prm_filter = isset($params['filter']) ? $params['filter'] : '';
            foreach(explode(',', $prm_filter) as $v) {
                if(strlen($v) && is_numeric($v) && in_array($v, $validStatuses)) $filters[] = $v;
            }

            $totalCount = $accTable->countAccountsByGroupId($groupId, $filters);
            $maxPage = ceil($totalCount / $ipp);
            if(!$maxPage) $maxPage = 1;
            if($page > $maxPage) $page = $maxPage;
            $list = $accTable->getAccountsByGroupId($groupId, $filters, $page, $ipp);

            $prevGroup = null;
            $nextGroup = null;
            foreach($grpTable->getSummaryByJnbId($grp['JnbId']) as $grpInfo) {
                if($grpInfo['ReturnedFlg']) continue;
                if($grpInfo['AccountGroupId'] < $grp['AccountGroupId']) $prevGroup = $grpInfo;
                if($nextGroup == null && $grpInfo['AccountGroupId'] > $grp['AccountGroupId']) $nextGroup = $grpInfo;
            }

            $this->view->assign('page', $page);
            $this->view->assign('maxPage', $maxPage);
            $this->view->assign('ipp', $ipp);
            $this->view->assign('group', $grp);
            $this->view->assign('total', $totalCount);
            $this->view->assign('list', ResultInterfaceToArray($list));
            $this->view->assign('filters', $filters);
            $this->view->assign('releaseInterval', $jnbConfig->getReleaseAfterReceiptInterval());
            $this->view->assign('nextAccount', $accTable->fetchNextAccountByOemId($grp['OemId']));
            if(!$grp['ReturnedFlg']) {
                $this->view->assign('prevGroup', $prevGroup);
                $this->view->assign('nextGroup', $nextGroup);
            }
        }
        else {
            $this->view->assign('error', '口座グループの指定が不正です');
        }

        return $this->view;
	}

    /**
     * retAction
     * JNB口座返却画面
     */
    public function retAction()
    {
        $this->setPageTitle('後払い.com - JNB口座返却');

        $params = $this->getParams();

        $gid = isset($params['gid']) ? $params['gid'] : -1;

        $jnbTable = new TableJnb($this->app->dbAdapter);
        $grpTable = new TableJnbAccountGroup($this->app->dbAdapter);
        $accTable = new TableJnbAccount($this->app->dbAdapter);
        $map = TableJnbAccount::getStatusMap();
        $sts_claiming = TableJnbAccount::ACCOUNT_STATUS_CLAIMING;
        $sts_closed = TableJnbAccount::ACCOUNT_STATUS_CLOSED;
        $this->view->assign('map', $map);

        try {
            $grp = $grpTable->find($gid)->current();
            if(!$grp) {
                // グループ指定不正
                $this->view->assign('msg_only', true);
                throw new \Exception('指定の口座グループは存在しません');
            }

            $usage = $accTable->getAccountUsageByGroupId($gid);
            $total = 0;
            foreach($usage as $sts => $data) {
                $total += $data['count'];
            }
            $oid = $jnbTable->find($grp['JnbId'])->current()['OemId'];

            $this->view->assign('group', $grp);
            $this->view->assign('summary', $this->_getJnbSummary($oid));
            $this->view->assign('usage', $usage);
            $this->view->assign('total', $total);

            if($usage[$sts_claiming]['count'] > 0 || $usage[$sts_closed]['count'] > 0) {
                // 返却不可状態
                $this->view->assign('msg_only');
                throw new \Exception(sprintf('%s または %sの状態の口座が残っているので現在返却できません', $map[$sts_claiming], $map[$sts_closed]));
            }

            if($grp['ReturnedFlg']) {
                // 返却済み
                $this->view->assign('mgs_only', true);
                throw new \Exception('指定の口座グループはすでに返却済みです');
            }
        }
        catch(\Exception $err) {
            $this->view->assign('error', $err->getMessage());
        }

        return $this->view;
    }

    /**
     * doretAction
     * JNB口座返却処理
     */
    public function doretAction()
    {
        $params = $this->getParams();

        $gid = isset($params['gid']) ? $params['gid'] : -1;

        $jnbTable = new TableJnb($this->app->dbAdapter);
        $grpTable = new TableJnbAccountGroup($this->app->dbAdapter);
        $accTable = new TableJnbAccount($this->app->dbAdapter);
        $map = TableJnbAccount::getStatusMap();
        $sts_claiming = TableJnbAccount::ACCOUNT_STATUS_CLAIMING;
        $sts_closed = TableJnbAccount::ACCOUNT_STATUS_CLOSED;


        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            $grp = $grpTable->find($gid)->current();
            if(!$grp) {
                // グループ指定不正
                $this->view->assign('msg_only', true);
                throw new \Exception('指定の口座グループは存在しません');
            }

            $usage = $accTable->getAccountUsageByGroupId($gid);
            $total = 0;
            foreach($usage as $sts => $data) {
                $total += $data['count'];
            }
            $oid = $jnbTable->find($grp['JnbId'])->current()['OemId'];

            $this->view->assign('group', $grp);
            $this->view->assign('summary', $this->_getJnbSummary($oid));
            $this->view->assign('usage', $usage);

            if($usage[$sts_claiming]['count'] > 0 || $usage[$sts_closed]['count'] > 0) {
                // 返却不可状態
                $this->view->assign('msg_only');
                throw new \Exception(sprintf('%s または %sの状態の口座が残っているので現在返却できません', $map[$sts_claiming], $map[$sts_closed]));
            }

            if($grp['ReturnedFlg']) {
                // 返却済み
                $this->view->assign('mgs_only', true);
                throw new \Exception('指定の口座グループはすでに返却済みです');
            }

            // 返却実行
            $grpTable->saveUpdate(array('ReturnedFlg' => 1, 'ReturnedDate' => date('Y-m-d H:i:s')), $gid);

            $this->app->dbAdapter->getDriver()->getConnection()->commit();

            // 登録内容詳細へリダイレクト
            return $this->_redirect(sprintf('jnb/detail/oid/%d', $oid));
        }
        catch(\Exception $err) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            $this->view->assign('error', $err->getMessage());
        }

        return $this->view;
    }

	/**
	 * restoreAction
	 * 返却済み口座グループを復活
	 */
	public function restoreAction()
	{
        $params = $this->getParams();

        $gid = isset($params['gid']) ? $params['gid'] : -1;

        $jnbTable = new TableJnb($this->app->dbAdapter);
        $grpTable = new TableJnbAccountGroup($this->app->dbAdapter);
        $accTable = new TableJnbAccount($this->app->dbAdapter);
        $map = TableJnbAccount::getStatusMap();
        $sts_claiming = TableJnbAccount::ACCOUNT_STATUS_CLAIMING;
        $sts_closed = TableJnbAccount::ACCOUNT_STATUS_CLOSED;

        $allow_restore = $this->jnbConfig->getAllowRestoreReturnedAccounts();
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            $grp = $grpTable->find($gid)->current();
            if(!$grp) {
                // 復活機能が許可されていない場合はJNB契約一覧へリダイレクト
                if(!$allow_restore) {
                    return $this->_redirect('jnb/list');
                }
                // グループ指定不正
                $this->view->assign('msg_only', true);
                throw new \Exception('指定の口座グループは存在しません');
            }

            $oid = $jnbTable->find($grp['JnbId'])->current()['OemId'];
            $redirect_to = sprintf('jnb/detail/oid/%d', $oid);

            // 復活機能が許可されていない場合は登録内容詳細へリダイレクト
            if(!$allow_restore) {
                return $this->_redirect($redirect_to);
            }

            if($grp['ReturnedFlg']) {
                // 返却実行
                $grpTable->saveUpdate(array('ReturnedFlg' => 0, 'ReturnedDate' => null), $gid);
            }
            $this->app->dbAdapter->getDriver()->getConnection()->commit();

            // 登録内容詳細へリダイレクト
            return $this->_redirect($redirect_to);
        }
        catch(\Exception $err) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            throw $err;
        }
	}

	/**
	 * 指定OEMに関連付けられた、JNB契約情報サマリーを取得する。
	 * @param int $oemId OEM ID
	 * @return array
	 */
	protected function _getJnbSummary($oid)
	{
        $jnbTable = new TableJnb($this->app->dbAdapter);
        return $jnbTable->findSummaryByOemId($oid);
	}
}
